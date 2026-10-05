<?php

/*--------------------------------------------------------------------------------------------------------
    * ControladorCargaMasiva
    *
    * Esta clase permite cargar informacion de clc's y afectaciones a la base de datos
    * unicamente Recursos Humanos tiene permisos para carga de clc´s
    * y la carga de afectaciones esta abierta a las dependencias que tengan el permiso
    *
    * @author        L.I. Anuar López Morgan <anuar.lopez@finanzasoaxaca.gob.mx>
    * @version:        1.6
    * Modificó 2015 : Marcos
    * Vamos a poner en éste único archivo lo que antes estaba en cargaMasiva
    y cargaMasivaConsolidada
    * la única diferencia identificada es que la consolidada calendariza automáticamente
    y otros detalles
    * que se controlan dentro del proceso (como el hecho de generar una CLC por cada UR
    que venga dentro de
    * la afectación en carga Consolidada)

    Modifico 2016 : @Japhet
    @version 1.8
    * Se crea una tabla para guardar las cargas creadas
    * Se crean los estados de las cargas para determinar hasta que punto llegan
    * Se crea una bitacora para registrar casi todos los momentos de la carga
    * Se crea un log de texto donde se guardan las consultas cuando hay         inconsistencias en la carga
    * No se permiten mas de una carga por sesion, si algo falla lo mas prudente es cerrar
    sesion y volver a iniciarla para destruir $_SESSION[_S] y evitar duplicidad, tedioso pero prudente
    * Medio que se estructura el codigo para que se parezca al PSR2
    * Dos lineas de comentarios por funcion ( en algunos casos se dejaron los que ya estaban

    Modifico 2016 : @Japhet
    @version 1.9
    * Se agregan validaciones durante la carga a temporales
    * Se agregan validaciones para celdas vacias o invalidas
    * Se agregan validaciones basicas de contendio
    * Se validan columnas numericas
    * Se validan valores basicos de las columnas
    * Se valida que los importes no traigan formulas
    * Todo lo anterior ya se le muestra de manera detallada al usuario para que      sepa  donde esta mal
    Modificaciones 2017 : @Japhet
    @version 1.9.2
    * Se agregan control de consultas por bloqueo de TABLAS
    si se muere la conexxion en espera del desbloqueo de tablas, se reinicia esa consulta ( parcial no todas las consultas )
    Modificaciones 2018 : @Japhet,@HectorMorales
    @version 1.9.2
    * Se intentará  pasar lo mas que se pueda ( lo mas seguro) a un procedimiento almacenando

    Modificaciones 2019 : @rvargas
    * Se implementa clase AnalyzerData para validar los datos del archivo xls, xlsx
    --------------------------------------------------------------------------------------------------------*/

require_once dirname(__FILE__) . '/../../lib/sessionSafeStart.php';
sessionSafeStart();

ini_set('max_execution_time', 600); //300 seconds = 5 minutes
ini_set('post_max_size', '14000M');
ini_set('memory_limit', '14000M');
ini_set('upload_max_filesize', '14000M');
set_time_limit(0);

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/scp/modelCargaMasiva.php';
require_once dirname(__FILE__) . '/../../lib/scp/vistaCargaMasiva.php';
require_once dirname(__FILE__) . '/../../ent/scp/Clc.php';
require_once dirname(__FILE__) . '/../../ent/scp/Afectaciones.php';
require_once dirname(__FILE__) . '/../../lib/funciones.php';
require_once dirname(__FILE__) . '/../../lib/FicheroSefip.php';
require_once dirname(__FILE__) . '/../../lib/bootstrap_spreadsheet.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ControladorCargaMasiva extends WebResponse
{
    private $hoy;
    private $cargaMasiva = true;
    public $fecha_actual;
    public $usr_id;
    public $adm_grupo;
    public $adm_ur;
    public $tipoCarga;
    public $typeCargaMasiva;
    public $usr_ip;
    public modelCargaMasiva $model;
    public vistaCargaMasiva $vista;
    public $escritura;

    public function __construct()
    {
        parent::__construct();
        $this->escritura = tienePermiso('s.2.1.2');
        $this->hoy = date('Y-m-d H:i:s');
        $this->fecha_actual = date('Y-m-d');
        $opcion = (isset($this->prm->claveOpcion)) ? $this->prm->claveOpcion : $this->prm->opcion->claveOpcion;
        #si se cumple alguna de las condiciones $this->cargaMasiva es TRUE si no se cumple ninguno es FALSE
        $this->cargaMasiva = (
            #valida que si la peticion viene del modulo de afectacion tenga el permiso de carga masiva de afectaciones
            ($opcion == 'S_2_1' && isset($_SESSION[_S]['prm']['S']['2']['1']['23'])) ||
            #valida que si la peticion viene del modulo de carga masiva tenga el permiso de carga masiva
            ($opcion == 'S_9_1' && isset($_SESSION[_S]['prm']['S']['9']['1']['23']))
        );

        #También controlamos que sólo la UR 318 puede hacer cargas masivas de CLC Recursos Humanos y CLC Consolidadas
        #Cualquier otra UR será sólo afectaciones
        $ur = $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'];
        $this->usr_id = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : false;
        $this->adm_grupo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : false;
        $this->adm_ur = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : false;

        # Si NO es la ur 318 o la ur 317 y que ademas tenga el permiso de carga, no lo permitimos
        if (($ur == '118' or $ur == '117') and isset($_SESSION[_S]['prm']['S']['9']['1']['23']) and $opcion == 'S_9_1') {
            $this->tipoCarga = 2;
            $this->typeCargaMasiva = 'CLC';
        } else {
            $this->tipoCarga = 1;
            $this->typeCargaMasiva = 'AFECTACION';
        }

        $this->pantalla = 'scp/admon/ControladorCargaMasiva.php';
        $this->usr_ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $this->model = new modelCargaMasiva();
        $this->vista = new vistaCargaMasiva();
        $this->model->setUrAdministracion();

        switch ($this->metodo) {
            case 'resCarga':
                $this->resCarga();
                break;
            case 'copiarArchivo':
                $this->copiarArchivo();
                break;
            case 'dibujaPantalla':
                $this->dibujaPantalla();
                break;
            case 'limpiaCarga':
                $this->limpiaCarga();
                break;
            case 'resultadoCarga':
                $this->resultadoCarga();
                break;
            case 'prepararDatos':
                $this->preparar();
                break;
            case 'revisarDatos':
                $this->revisar();
                break;
            case 'cargarDatos':
                $this->cargar();
                break;
            case 'muestraCarga':
                $this->muestraCarga();
                break;
            case 'enviaClc':
                $this->enviaClc();
                break;
            case 'prevCarga':
                $this->prevCarga();
                break;
            case 'stepTransfer':
                $this->stepTransfer();
                break;
            case 'in_process':
                $this->in_process();
                break;
            default:
                $this->main();
                break;
        }

        $this->returnJson();
    }
    // FUNCION INICIAL ( PANTALLA DE INICIO DE LA CARGA MASIVA )
    private function main()
    {
        unset($_SESSION[_S]['tem_carga']);
        unset($_SESSION[_S]['estadocarga']);
        $this->cierre_inesperado();

        $boton = new stdClass();

        if ($this->tipoCarga == 1) {
            $boton->id = 'btnRegresar';
            $boton->title = 'Regresar a Afectaciones';
            $boton->value = ' Regresar';
            $boton->icon = 'fa solid fa-circle-left me-2';
            $boton->color = 'gris';
            $boton->click = 'comunica("scp/afectaciones/ControladorAfectacion.php")';
            $this->agregaTools($this->ponBoton($boton) . ' ');

            $this->mixHTML('
                <div id="fondorotatorio" class="row">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" id="afe_fondoRotatorio" value="N">
                        <label class="custom-control-label">Marcar esta casilla si la Carga Masiva será usada para recuperación de Fondo Rotatorio</label>
                    </div>
                </div>
            ');
        } else {
            $boton->id = 'btnImprimir';
            $boton->color = 'gris';
            $boton->title = 'Imprimir Clc';
            $boton->value = ' Imprimir';
            $boton->icon = 'print';
            $boton->click = '
                alertify.prompt( "Escribe el id de Carga Masiva a imprimir", "", "", function(evt, idCarga) { 
                if (idCarga !== null) {
                    var form = $("<form>")
                        .attr("action", "rep/doc/rep_clc.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "tem_carga").attr("value", idCarga ))
                        .append($("<input>").attr("type", "hidden").attr("name", "claveOpcion").attr("value", "' . $this->prm->opcion->claveOpcion . '" ));
                    $("body").append(form);
                    form.submit();
                }}, function() {}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            ';

            $this->agregaTools($this->ponBoton($boton) . ' ');
            $this->mixHTML('
                <div class="row" style="display:none;">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" id="afe_fondoRotatorio" value="N">
                        <label class="custom-control-label">Marcar esta casilla si la Carga Masiva será usada para recuperación de Fondo Rotatorio</label>
                    </div>
                </div>
            ');
        }
        $boton = new stdClass();
        $boton->id = 'btnFormato';
        $boton->title = 'Descargar Formato para Carga Masiva';
        $boton->icon = 'fa-solid fa-folder me-2';
        $boton->style = 'font-size: 15px;';
        $boton->color = 'amarillo';
        $boton->value = 'Formato';

        switch ($this->tipoCarga) {
            case 1:
                $this->agregaTools('<a href="archivos/formatos/FormatoAfectaciones.zip" target="_blank">' . $this->ponBoton($boton) . '</a>');
                break;
            case 2:
                $this->agregaTools('<a href="archivos/formatos/FormatoCLCs.zip" target="_blank">' . $this->ponBoton($boton) . '</a>');
                break;
        }

        $boton = new stdClass();
        $boton->id = 'btnReglas';
        $boton->title = 'Ver Reglas Generales';
        $boton->color = 'amarillo';
        $boton->icon = 'th-list';

        $this->mixHtml('
            <ul class="list-group" id="listaCarga">
                <li class="list-group-item warning" style="background:#ffffff">
                    <h4>
                        <!-- START CARGA MASIVA -->
                        <dd class="file-js">
                        <div class="box">
                            <form method="post" id="export_excel"> 
                                <input type="hidden" name="type_carga" id="type_carga" value="' . $this->typeCargaMasiva . '" />
                                <input type="file" name="file-1" id="file-1" class="inputCargaMasiva inputCargaMasiva-1"/>
                                    <label for="file-1" style="font-size: 1rem !important;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17">
                                            <path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/>
                                        </svg><span>Seleccionar archivo&hellip;</span>
                                    </label> 
                            </form>
                        </div>
                        <div id="name_xls"> </div>
                        <div id="button_txt"> </div>
                        <div id="loading_file" class="parent_loading"> 
                            <img  src="img/loading_xls.gif" class="child_loading" alt="loading" />
                        </div>
                        </dd>
                        <!-- END CARGA MASIVA -->
                    </h4>
                </li>
                <li class="list-group-item" id="botonesCarga"></li>
                    <table class="table table-bordered">
                        <tbody id="indicaciones">
                            <tr>
                                <td class="warning" colspan="4">INDICACIONES</td>
                            </tr>
                            <tr>
                                <td>
                                    1. Antes de realizar la carga masiva asegurese de descargar el formato del ejercicio actual. </br> 
                                    2. En caso de generar el excel mediante un sistema, es recomendable abrir el documento y guardarlo nuevamente con el fomato xls a fin de evitar futuros fallos.
                                </td>
                            </tr>
                        </tbody>
                    </table>
            </ul>
            <div class="row" id="tablaCarga"></div>
            <div class="row" id="resCarga"></div>
        ');

        $this->mixScript('
        $("#afe_fondoRotatorio").on("click",function(){
            if ($(this).is(":checked")) {
                // Hacer algo si el checkbox ha sido seleccionado
                $(this).attr("value", "S");
            } else {
                $(this).attr("value", "N");
            }
        });

        // START CARGA MASIVA 
        (function(e,t,n){var r=e.querySelectorAll("dd")[0];r.className=r.className.replace(/(^|\s)file-js(\s|$)/,"$1js$2")})(document,window,0);

        $("#loading_file").hide();
        $("#file-1").change(function(){
            comunica("' . $this->pantalla . '",{
                metodo:"prevCarga"
            });
        });
        $(\'#export_excel\').on(\'submit\', function(event){
            $("#loading_file").show();
            event.preventDefault();
            $.ajax({
                url:"lib/_class_carga_masiva/analyzer.php",
                method:"POST",
                data:new FormData(this),
                contentType:false, processData:false,
                success:function(data){ 
                    var message = "";
                    var type = "error";
                    var parsed = JSON.parse(data);
                    if(parsed.success != undefined && parsed.success != ""){
                        type = "success";
                        message = parsed.success;
                        comunica("' . $this->pantalla . '",{metodo:"stepTransfer",file_tmp:parsed.xls,tem_last: parsed.tem_last});
                    }else if(parsed.errores != undefined && parsed.errores != ""){
                        message = parsed.errores;
                        if(parsed.txt != undefined){
                            $(\'#button_txt\').html("<a class=\'btn btn-danger\' href=\'archivos/failedCargaMasiva/afectaciones/' . date('m') . '/' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '/"+parsed.txt+".txt\' id=\'download_file\' download=\'archivos/failedCargaMasiva/afectaciones/' . date('m') . '/' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '/"+parsed.txt+".txt\'>Descargar archivo</a>"); 
                        }
                    } else {
                        message = "Ocurrio un error al retornar respuesta, intente de nuevo.";
                    }
                    $("#loading_file").hide();
                    notify(message,type);
                    $(\'#file-1\').val(""); 
                    $(\'#file-1\').removeAttr(\'disabled\');
                }
            });
        });

        $("a#download_file").click(function(){
            var now = new Date().toString();
            this.href = "data:text/plain;charset=UTF-8,"  + encodeURIComponent(now);
        });
        // END CARGA MASIVA 
    ');
    }

    private function cierre_inesperado()
    {
        $this->conectar();
        $sql = '
            SELECT crm_id,tem_carga
            FROM cat_CargaMasiva
            WHERE tcr_id = ?
            AND ecr_id NOT IN (91,92,93,94,95,99,100) 
            AND usr_id = ?
            ORDER BY crm_id DESC
            LIMIT 1
        ';
        $this->setSql($sql);
        $this->consultar([$this->tipoCarga, $this->usr_id]);
        $this->desconectar();

        while ($row = $this->extraer()) {
            $this->mixScript('
                $("#export_excel").hide();
                $(\'#button_txt\').html("<div style=\'background:#ffb1af; padding: 12px; font-size: 15px; border-radius: 5px; color: #000; \'>La última carga masiva con id ' . $row['tem_carga'] . ' está en proceso favor de terminar el proceso. <br /><button class=\'btn btn-outline-danger\'  id=\'stop_carga\'>Finalizar</button></div>"); 
                $("#stop_carga").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"in_process",
                        crm_id:' . $row['crm_id'] . '
                    });
                });
            ');
        }
    }

    private function in_process()
    {
        $this->conectar();
        $observacion = 'Error inesperado';
        $sql = '
            SET 
            @usr_id = ?,
            @usr_ip = INET_ATON("127.0.0.1"),
            @bit_observacion = ?;
        ';
        $this->setSql($sql);
        $this->consultar([$this->usr_id, $observacion]);

        $sql = '
            UPDATE cat_CargaMasiva 
            SET ecr_id = 99
            WHERE 
            tcr_id = ?
            AND usr_id = ?
            AND crm_id = ?;
        ';
        $this->setSql($sql);
        $this->consultar([$this->tipoCarga, $this->usr_id, $this->prm->crm_id]);
        $this->desconectar();

        $this->mixScript('
            $("#export_excel").show();
            $(\'#button_txt\').html(""); 
        ');
    }

    private function prevCarga()
    {
        if (isset($_SESSION[_S]['tem_carga']) && $_SESSION[_S]['tem_carga'] != '') {
            $this->mixScript(' notify("Hay una carga masiva en proceso","error");');
            return false;
        }
        $this->mixScript('
            $("#button_txt").html("");
            $("#export_excel").submit();
            $(\'#file-1\').attr("disabled", "disabled"); 
            $("#indicaciones, #fondorotatorio").hide();
        ');
    }

    private function setterBitacora($datetime1, $estadoCarga, $observacion)
    {
        $datetime2 = new DateTime('now');
        $interval = date_diff($datetime1, $datetime2);
        $tiempo = ($interval->format('%i minutos %s segundos'));
        $this->bitacora($estadoCarga, $observacion . ' ' . $tiempo);
    }

    // --------------------------------------------------------------------------------------------
    // Obtenemos json para manipular la data
    // --------------------------------------------------------------------------------------------

    private function stepTransfer()
    {
        $file_name = explode('.', $this->prm->file_tmp);
        $this->prm->tipo = $file_name[1];

        $_SESSION[_S]['tem_carga'] = $file_name[0];
        $_SESSION[_S]['tem_usr_ip'] = ($_SERVER['REMOTE_ADDR'] == '::1' ? '127.0.0.1' : $_SERVER['REMOTE_ADDR']);
        $_SESSION[_S]['tem_carga_id'] = $this->prm->tem_last;

        $this->mixScript(' 
            $("#export_excel").hide();
            $(\'#name_xls\').html("<h4 style=\'color: #44658a;\'><label>Archivo: </label>  <b>' . $_SESSION[_S]['tem_carga'] . '.' . $this->prm->tipo . '</b></h4>"); 
        ');

        $boton = new stdClass();
        $boton->id = 'btnSeleccionArchivo';
        $boton->title = 'Buscar Archivo';
        $boton->color = 'gris';
        $boton->value = 'copiar';
        $boton->icon = 'exchange me-2';
        $boton->click = '
            $(this).attr("disabled","disabled").css("background-color","#348fe2")
                .css("border-color","#858585")
                .append($("<img/>").attr("src","img/loader2.gif"));
            comunica("' . $this->pantalla . '",{
                metodo:"copiarArchivo",
                tipo:"' . $this->prm->tipo . '",
                afe_fr:$("#afe_fondoRotatorio").attr("value")
            });
        ';
        $this->setContenedor('botonesCarga');
        $this->mixHtml($this->ponBoton($boton));
        $this->mixScript('$(".datoCarga").remove();');

        $datetime1 = new DateTime('now');

        $opcion = (isset($this->prm->claveOpcion)) ? $this->prm->claveOpcion : $this->prm->opcion->claveOpcion;
        $ur = $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'];
        if (($ur == '118' or $ur == '117') and isset($_SESSION[_S]['prm']['S']['9']['1']['23']) and $opcion == 'S_9_1') {
            $tipoCarga = 2;
        } else {
            $tipoCarga = 1;
        }
        $this->actualizaCarga(7, 'CARGANDO LOS DATOS', $tipoCarga);
        $this->setterBitacora($datetime1, 4, 'La Carga del Archivo tardo');
    }

    //OBTIENE EL ESTADO DE LA CARGA MASIVA
    private function getEstadoCarga($carga)
    {
        $c = new AccesoDatos();
        $c->conectar();
        $sql = '
            SELECT ecr_id FROM cat_CargaMasiva
            WHERE gpo_id = ?
            AND ur_id = ?
            AND tem_Carga = ?
        ';
        $c->setSql($sql);
        $c->consultar([$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $carga]);
        $data = $c->extraer();
        $c->desconectar();
        if ($c->getNumeroRegistros() == 0) {
            return false;
        } else {
            return $data['ecr_id'];
        }
    }
    //  ACTUALIZA EL ESTADO DE LA CARGA PARA NO REPETIR PASOS ( SEGÚN )
    private function actualizaCarga($estado, $observacion, $tipoCarga = '')
    {
        $tcr_id = $tipoCarga ? ',tcr_id = ' . $tipoCarga . '' : '';
        $c = new AccesoDatos();
        $c->conectar();
        $sql = '
            SET
            @usr_id = ?,
            @usr_ip = INET_ATON(?),
            @bit_observacion = ?;
        ';
        $c->setSql($sql);
        $c->consultar([$_SESSION[_S]['usr_id'], $_SESSION[_S]['tem_usr_ip'], $observacion]);
        $sql = '
            UPDATE cat_CargaMasiva 
            SET ecr_id = ' . $estado . '' . $tcr_id . '
            WHERE 
            gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id =' . $_SESSION[_S]['clasAdm-ur'] . ' 
            AND tem_Carga ="' . $_SESSION[_S]['tem_carga'] . '" 
        ';
        $c->setSql($sql);
        $c->consultar();
        $c->desconectar();
    }
    //GUARDA EN UN FICHERO LO EJECUTADO POR CARGA
    private function logtxt($linea, $sql, $mensaje = '')
    {
        if (!file_exists('log')) {
            mkdir('log', 0777, true);
        }
        if (!file_exists('log/carga')) {
            mkdir('log/carga', 0777, true);
        }
        $rutalog = 'log/carga/' . $_SESSION[_S]['tem_carga'] . '.log';
        $file = fopen($rutalog, 'a');
        $tempdata = date('Y-m-d-H-i-s') . '|IPADDRESS=' . $_SESSION[_S]['ses_ip'] . '|USRID=' . $_SESSION[_S]['usr_id'];
        fwrite($file, 'L:' . $linea . ':' . $mensaje . ':[' . $tempdata . ']:' . $sql . PHP_EOL);
        fclose($file);
    }
    // INSERTA UN REGISTRO EN LA BITACORA DE CARGA MASIVA ESTO ES PARA EVITAR EL BLOQUE DE CODIGO
    private function bitacora($edo, $observacion)
    {
        $c = new AccesoDatos();
        $c->conectar();
        $sql = '
            INSERT INTO ' . BD_BITACORA . '.bit_CargaMasiva
            SET bit_observacion = ?,
            crm_id = ?,
            tem_carga = ?,
            ecr_id = ?,
            tcr_id = ?,
            usr_id = ?,
            usr_ip = INET_ATON(?);
        ';
        $paramsBit = [
            $observacion,
            $_SESSION[_S]['tem_carga_id'],
            $_SESSION[_S]['tem_carga'],
            $edo,
            $this->tipoCarga,
            $_SESSION[_S]['usr_id'],
            $_SESSION[_S]['tem_usr_ip'],
        ];
        $c->setSql($sql);
        $c->consultar($paramsBit);
        $c->desconectar();
    }

    // ------------------------------------------------------------------------------
    // Copiamos el archivo al servidor + cargamos su información en la tabla temporal
    private function copiarArchivo()
    {
        if ($this->getEstadoCarga($_SESSION[_S]['tem_carga']) != 7) {
            $this->mixScript('notify("Ya se inicio el copiado","warning");');
            return false;
        }
        $datetime1 = new DateTime('now');

        $this->actualizaCarga(10, 'CARGA EN TEMPORAL');

        if ($this->prm->tipo == 'xls') { // Extension excel 97
            $ext = 'Xls';
        } elseif ($this->prm->tipo == 'xlsx') { // Extension excel 2007 y 2010
            $ext = 'Xlsx';
        }

        $fichero = '';
        $base = '';
        $this->prm->lnk = 'afectaciones/' . date('m') . '/Carga_Masiva/' . $_SESSION[_S]['tem_carga'] . '.' . $this->prm->tipo;

        if (isset($this->prm->lnk)) {
            $tmp = explode('/', $this->prm->lnk);
            $base = $tmp[0] . '/' . $tmp[1] . '/' . $tmp[2];
            $fichero = $tmp[3];
        }

        $r = new RutaArchivoSefip();
        $r->setBase('SCP');
        $r->setPath($base);
        $r->setAnioFichero(APP_EJERCICIO);
        $r->setUrFichero($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur']);
        $routeXLS = $r->getRutaMod();
        $routeXLS .= $fichero;
        $contenido = file_get_contents($routeXLS);

        if ($contenido === false) {
            // Por hacer: cambiar el guardado de bitacora de errores (a la tabla bit_CargaMasivaError )una bitacora formal para la carga masiva
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql('
                INSERT INTO ' . BD_TEMPORAL . ".tem_Clc( gpo_id, ur_id, clc_referencia, clc_concepto, tem_carga, usr_id ) 
                VALUES( ?, ?, 9999, 'NO FUÉ POSIBLE LEER EL ARCHIVO', ?, ?);
            ");
            $paramsTem = [
                $_SESSION[_S]['clasAdm-gpo'],
                $_SESSION[_S]['clasAdm-ur'],
                $_SESSION[_S]['tem_carga'],
                $_SESSION[_S]['usr_id'],
            ];
            $conexion->consultar($paramsTem);
            $conexion->desconectar();
            $this->mixScript('
                $("#listaCarga").append(\'<li class="list-group-item datoCarga">
                <label class="alert alert-danger">ERROR:</label> NO FUÉ POSIBLE LEER EL ARCHIVO</li>\');
            ');
            return false;
        }

        $reader = IOFactory::createReader($ext);
        $spreadsheet = $reader->load($routeXLS);
        $sheetNames = $spreadsheet->getSheetNames();

        $nombreSheetCorrectos = true;
        if (($this->tipoCarga == '2' || $this->tipoCarga == '3') && !in_array('CLC', $sheetNames)) {
            $this->mixScript('
                $("#listaCarga").append(\'<li class="list-group-item datoCarga">El archivo no contiene la pestaña CLC</li>\');
            ');
            $nombreSheetCorrectos = false;
        }
        if (!in_array('AFECTACION', $sheetNames)) {
            $this->mixScript('
                $("#listaCarga").append(\'<li class="list-group-item datoCarga">El archivo no contiene la pestaña AFECTACION</li>\');
            ');
            $nombreSheetCorrectos = false;
        }
        if ($nombreSheetCorrectos) {
            $inconsistencias = false;
            // Revisamos que tenga las nuevas columnas que pediremos en el 2015 y que no estaban en el 2014
            // (porque será el mismo formato para todas las cargas masivas de afectaciones y clcs)
            // Siempre y cuando sea carga de CLCs ...
            if ($this->tipoCarga == '2' || $this->tipoCarga == '3') {
                $gpo = $spreadsheet->getSheetByName('CLC')->getCell('D1')->getValue();
                $ur = $spreadsheet->getSheetByName('CLC')->getCell('E1')->getValue();
                // Revisamos la pestaña CLC
                if (trim(strtoupper($gpo)) != 'GPO_ID' || trim(strtoupper($ur)) != 'UR_ID') {
                    // Mensaje de error "patito"
                    $conexion = new AccesoDatos();
                    $conexion->setSql('
                        INSERT INTO ' . BD_TEMPORAL . ".tem_Clc( gpo_id, ur_id, clc_referencia, clc_concepto, tem_carga, usr_id ) 
                        VALUES( ?, ?, 9999, 'Faltan las columnas D = GPO_ID y E = UR_ID en la pestaña CLC.', ?, ?);
                    ");
                    $paramsTem = [
                        $_SESSION[_S]['clasAdm-gpo'],
                        $_SESSION[_S]['clasAdm-ur'],
                        $_SESSION[_S]['tem_carga'],
                        $_SESSION[_S]['usr_id'],
                    ];
                    $conexion->consultar($paramsTem);
                    $conexion->desconectar();
                    $this->mixScript('
                        $("#listaCarga").append(\'<li class="list-group-item datoCarga"><label class="alert alert-danger">ERROR:</label> Faltan las columnas D = GPO_ID y E = UR_ID en la pestaña CLC.</li>\');
                    ');
                    return false;
                }
            }
            // Si NO es carga masiva de COMPROMISOS, sí necesitamos afectaciones
            if ($this->tipoCarga <> '4') { // si es diferente de
                $tcp = $spreadsheet->getSheetByName('AFECTACION')->getCell('T1')->getValue();
                $tcp = (empty($tcp)) ? $spreadsheet->getSheetByName('AFECTACION')->getCell('T2')->getValue() : $tcp;
                $iva = $spreadsheet->getSheetByName('AFECTACION')->getCell('AM1')->getValue();
                $iva = (empty($iva)) ? $spreadsheet->getSheetByName('AFECTACION')->getCell('AM2')->getValue() : $iva;
                $neto = $spreadsheet->getSheetByName('AFECTACION')->getCell('AN1')->getValue();
                $neto = (empty($neto)) ? $spreadsheet->getSheetByName('AFECTACION')->getCell('AN2')->getValue() : $neto;
                $neto = str_replace(' ', '', $neto);

                // Revisamos la pestaña AFECTACION
                if (trim(strtoupper($tcp)) != 'TCP_ID' || trim(strtoupper($iva)) != 'IMPORTE_IVA' || trim(strtoupper($neto)) != 'IMPORTENETO') {
                    // Mensaje de error "patito"
                    $conexion = new AccesoDatos();
                    $conexion->conectar();
                    $conexion->setSql('
                        INSERT INTO ' . BD_TEMPORAL . ".tem_Clc( gpo_id, ur_id, clc_referencia, clc_concepto, tem_carga, usr_id )
                        VALUES(?, ?, 9999, 'Faltan las columnas T = tcp_id, AM = IMPORTE_IVA y/o AN = IMPORTENETO en la pestaña AFECTACION.', ?, ?);
                    ");
                    $params = [
                        $_SESSION[_S]['clasAdm-gpo'],
                        $_SESSION[_S]['clasAdm-ur'],
                        $_SESSION[_S]['tem_carga'],
                        $_SESSION[_S]['usr_id'],
                    ];
                    $conexion->consultar($params);
                    $conexion->desconectar();
                    $this->mixScript('
                        $("#listaCarga").append(\'<li class="list-group-item datoCarga"><label class="alert alert-danger">ERROR:</label> Faltan las columnas T = tcp_id, AM = IMPORTE_IVA y/o AN = IMPORTENETO en la pestaña AFECTACION.</li>\');
                    ');
                    return false;
                }
            }

            $resultadoAfectaciones2 = true;
            if ($inconsistencias == false) {
                // 1. Cargamos la pestaña AFECTACION ( números de control presupuestales y contables )
                $resultadoCLCs = true;   // Por default true para que cuando sólo se carguen afectaciones no marque error de inconsistencias
                $resultadoAfectaciones = $this->cargaAfectaciones($spreadsheet->getSheetByName('AFECTACION'), $this->tipoCarga);
                // Como se puso en un sólo código todas las variantes (sólo afectaciones, clc Recuros Humanos, clc consolidada )
                // validamos después de cargar las afectaciones que se cumplan ciertas reglas
                if ($resultadoAfectaciones != false) {
                    $resultadoAfectaciones2 = $this->validaReglasAfectaciones($this->tipoCarga);
                    // Solo cargamos CLCs cuando se trate de CLC para Recursos Humanos o CLC Consolidada
                    // ( en código, cuando el valor de la variable soloAfectaciones sea igual a false )
                    if ($this->tipoCarga != '1') {
                        // Siempre y cuando no haya habido ningún problema al cargar las afectaciones
                        if ($resultadoAfectaciones2 == true) {
                            $resultadoCLCs = $this->cargaClcs($spreadsheet->getSheetByName('CLC'), $this->tipoCarga);
                        }
                    }
                }
                // Si hubo algun problema al cargar afectaciones y - en su caso - CLCs ...
                if ($resultadoAfectaciones == false || $resultadoCLCs == false || $resultadoAfectaciones2 == false) {
                    $inconsistencias = true;
                } else {
                    $inconsistencias = false;
                }
            }
            if ($inconsistencias) {
                $datetime2 = new DateTime('now');
                $interval = date_diff($datetime1, $datetime2);
                $tiempo = ($interval->format('%i minutos %s segundos'));
                $this->bitacora(99, 'El proceso de copiar archivo tardo ' . $tiempo);
                $this->actualizaCarga(99, 'HAY INCONSISTENCIAS EN LA CARGA');
                if ($resultadoAfectaciones2 == false) {
                    $this->mixScript('
                        $form = $("<form />");
                        $form.attr("method","post").attr("target","_blank").attr("action","rep/doc/scp/afectaciones/rep_inconsistenciasCarga.php");
                        $form.html( $("<input />").attr("name","tem_Carga").val("' . $_SESSION[_S]['tem_carga'] . '") ).append( $("<input />").attr("name","archivo").val("' . $_SESSION[_S]['tem_carga']/*$this->nombreArchivo*/ . '") );
                        $("body").append($form);
                        $form.submit();
                        $form.remove();
                    ');
                } elseif (!$resultadoCLCs) {
                    $this->conectar();
                    $this->setSql('
                        SELECT * FROM ' . BD_TEMPORAL . '.tem_Clc
                        WHERE tem_Carga = ? 
                        LIMIT 1
                    ');
                    $this->consultar([$_SESSION[_S]['tem_carga']]);
                    $res = $this->extraer();
                    $this->mixScript('
                        $("#listaCarga").append(\'<li class="list-group-item datoCarga"><label class="alert alert-danger">ERROR:</label>' . htmlspecialchars($res['clc_concepto']) . '</li>\');
                    ');
                }
            } else {
                $this->actualizaCarga(35, 'FINALIZA EL COPIADO DEL FICHERO');
                /*$this->conectar();
                $this->setSql('
                    INSERT INTO '.BD_TEMPORAL.'.tem_Avance (tem_carga,actual,mensaje)
                    VALUES(?, 2, "Datos Copiados")
                ');
                $this->consultar([$_SESSION[_S]['tem_carga']]);
                $this->desconectar(); La tabla no existe, validar, de ser así eliminar esto*/
                $boton = new stdClass();
                $boton->id = 'btnPrepararArchivo';
                $boton->title = 'Buscar Archivo';
                $boton->value = ' Preparar';
                $boton->color = 'amarillo';
                $boton->icon = 'certificate';
                $boton->click = '
                    $(this).attr("disabled","disabled").css("background-color","#348fe2")
                        .css("border-color","#858585")
                        .append($("<img/>").attr("src","img/loader2.gif"));
                    comunica("' . $this->pantalla . '",{
                        metodo:"prepararDatos", 
                        afe_fr:$("#afe_fondoRotatorio").attr("value")
                    });
                ';
                $this->setContenedor('botonesCarga');
                $this->mixHtml($this->ponBoton($boton));
                $this->mixScript('
                    $("#listaCarga").append(\'<li class="list-group-item datoCarga"><label class="alert-success">Paso 1 completo:</label> Se copiaron los datos de su archivo con el id de carga : <b><h4>' . $_SESSION[_S]['tem_carga'] . '</h4></b></li>\');
                    $("#botonesCarga").prepend(\'<button type="button" value=" copiar"class="btn btn-outline-secondary me-1 disabled" <span class="glyphicon glyphicon-upload"></span> copiar</button>\');
                ');
                $datetime2 = new DateTime('now');
                $interval = date_diff($datetime1, $datetime2);
                $tiempo = ($interval->format('%i minutos %s segundos'));
                $this->bitacora(35, 'El proceso de copiar archivo tardo ' . $tiempo);
            }
        }
    }
    //  Cargamos en tablas temporales el contenido de la pestaña AFECTACION del archivo Excel
    //  Básicamente estamos ingresando registros en las tablas de números de control Presupuestal y Contables
    //  valores para $tipoCarga: 1 = sólo Afectaciones      2 = CLC Recursos Humanos      3 = CLC Consolidadas
    // -----------------------------------------------------------------------------------------------

    private function cargaAfectaciones($afes, $tipoCarga)
    {
        if ($this->getEstadoCarga($_SESSION[_S]['tem_carga']) != 10) {
            $this->mixScript('notify("Ya se inicio el copiado de afectaciones","warning");');
            return false;
        }

        $datetime1 = new DateTime('now');

        $this->actualizaCarga(15, 'CARGA AFECTACIONES-INICIO LINEA 767');

        $array = ['541', '124', '117', '118'];
        $minRegistrosPorCarga = (in_array($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'], $array)) ? 1 : 10;
        $registrosDeLaCarga = 0;
        $maxRegistrosPorInsert = 500;
        $tclPermitido = true;
        //Campos que se van a considerar para la consulta que insertara los registros a los numero de control presupuestal
        $sqlInsertP = '
            INSERT INTO ' . BD_TEMPORAL . '.tem_Presupuestal(clc_id,afe_id,ecf_id,tcl_id,
            stc_id,gpo_id,ur_id,ue_id, prg_id,spr_id,py_id,oa_id, pg_id,pe_id,
            cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,
            tcp_id,tno_id,mno_id,smn_id,rfc_id,clc_referencia,ncp_fechaReferencia,
            ncp_percepcionBruto,iva_id,ncp_percepcionIva,ncp_percepcionNeto,ncp_concepto,
            afe_fechaInicio,afe_fechaFin,ncp_numeroOrden,ncp_fechaOrden,tem_carga,usr_id) VALUES ';

        $valuesP = ''; // almacena los registros a insertar
        $numRegP = 0; // cuenta los registros que se van almacenando en values
        //Campos que se van a considerar para la consulta que insertara los registros a los numero de control contable
        $sqlInsertC = '
            INSERT INTO ' . BD_TEMPORAL . '.tem_Contable(clc_id,afe_id,ecf_id,tcl_id,
            stc_id,gpo_id,ur_id,ue_id, prg_id,spr_id,py_id,oa_id,
            cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,
            tcp_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,
            cta_id,cta_nombre,tno_id,mno_id,smn_id,rfc_id,clc_referencia,ncc_fechaReferencia,
            ncc_tipoMovimiento,ncc_percepcion,ncc_retencion,ncc_concepto,afe_fechaInicio,afe_fechaFin,
            ncc_amortiza,ncc_numeroOrden,ncc_fechaOrden,tem_carga,usr_id) VALUES';
        $valuesC = '';
        $numRegC = 0;
        $fila = 3; // es la fila que va leyendo en el archivo excel, inicia en 3 ya que por el encabezado los registros inician en esta fila
        $this->conectar();

        $subtipos = ['B', 'F', 'G', 'H'];   // LOS TIPOS DE CLC QUE ADMITEN SUBTIPO 0

        $while = true;

        while ($afes->getCell('A' . $fila)->getValue() != '') {
            $error = '';

            /* TIPO DE CLC */
            $tcl_id = $afes->getCell('A' . $fila)->getValue(); //tcl_id,
            $stc_id = $afes->getCell('B' . $fila)->getValue(); //stc_id,
            $stc_id = (empty($stc_id)) ? '0,' : $stc_id . ','; //stc_id,

            if ($tcl_id == '' || empty($tcl_id)) {
                $error .= '||ERROR FILA' . $fila . ': el tipo de clc esta vacio';
            }
            if (!in_array($tcl_id, $subtipos) && $stc_id == '0,') {
                $error .= '||ERROR FILA' . $fila . ': el tipo de clc ' . $tcl_id . ' no tiene Concepto de pago 0 ';
            }
            if ($tcl_id == 'B' && $stc_id != '0,') {
                $error .= '||ERROR FILA' . $fila . ': el tipo de clc ' . $tcl_id . ' no tiene Concepto de pago diferente de 0 ';
            }
            if ($this->prm->afe_fr == 'S' && $tcl_id != 'B') {
                $error .= '||ERROR FILA' . $fila . ': el tipo de clc ' . $tcl_id . ' no es valida para recuperación de Fondo Rotatorio ';
            }

            /* UNIDA RESPONSABLE */
            $gpo_id = intval($afes->getCell('D' . $fila)->getValue()); //gpo_id,
            $ur_id = intval($afes->getCell('E' . $fila)->getValue()); //ur_id,
            $ue_id = intval($afes->getCell('F' . $fila)->getValue()); //ue_id,

            if (empty($gpo_id) || $gpo_id == 0 || $gpo_id > 9) {
                $error .= '||ERROR FILA ' . $fila . ': la columna <strong>gpo_id</strong> no puede ir vacia,ser 0 o diferente de ' . intval($_SESSION[_S]['clasAdm-gpo']) . ' ';
            } else {
                $gpo_id = $gpo_id . ',';
            }
            if (empty($ur_id) || $ur_id == 0 || $ur_id > 99) {
                $error .= '||ERROR FILA ' . $fila . ': la columna <strong>ur_id</strong> no puede ir vacia,ser 0 o diferente de ' . intval($_SESSION[_S]['clasAdm-ur']) . ' ';
            } else {
                $ur_id = $ur_id . ',';
            }
            if (empty($ue_id) || $ue_id == 0 || $ue_id > 999) {
                $error .= '||ERROR FILA ' . $fila . ': la columna <strong>ue_id</strong> no puede ir vacia,ser 0 o mayor a 999 ';
            } else {
                $ue_id = $ue_id . ',';
            }

            /* OBRA */
            $prg_id = intval($afes->getCell('G' . $fila)->getValue()); //prg_id,
            $spr_id = intval($afes->getCell('H' . $fila)->getValue()); //spr_id,
            $py_id = intval($afes->getCell('I' . $fila)->getValue()); //py_id,
            $oa_id = intval($afes->getCell('J' . $fila)->getValue()); //oa_id,
            $obra = $prg_id . '-' . $spr_id . '-' . $py_id . '-' . $oa_id;

            if ($prg_id === '' || $spr_id === '' || $py_id === '' || $oa_id === '') {
                $error .= '||ERROR FILA' . $fila . ': verifique los datos de la obra en esta fila:' . $obra;
            } else {
                if ($prg_id > 999) {
                    $error .= '||ERROR CELDA G' . $fila . ': el <strong>Programa</strong> no puede ser mayor a 999';
                }
                if ($spr_id > 99) {
                    $error .= '||ERROR FILA H' . $fila . ': el <strong>Subprograma</strong> no puede ser mayor a 99';
                }
                if ($py_id > 999) {
                    $error .= '||ERROR FILA I' . $fila . ': el <strong>Proyecto</strong> no puede ser mayor a 999';
                }
                if ($oa_id > 999) {
                    $error .= '||ERROR FILA J' . $fila . ': la <strong>Obra</strong> no puede ser mayor a 999';
                }
            }

            $prg_id = $prg_id . ',';
            $spr_id = $spr_id . ',';
            $py_id = $py_id . ',';
            $oa_id = $oa_id . ',';

            /* CLAVE DE FINANCIAMIENTO */
            $cff_id = '"' . $afes->getCell('M' . $fila)->getValue() . '",'; //fte_id,
            $fte_id = '"' . $afes->getCell('N' . $fila)->getValue() . '",'; //fte_id,
            $sft_id = '"' . $afes->getCell('O' . $fila)->getValue() . '",'; //sft_id,
            $ori_id = '"' . $afes->getCell('P' . $fila)->getValue() . '",'; //ori_id,
            $sor_id = '"' . $afes->getCell('Q' . $fila)->getValue() . '",'; //sor_id,
            $cfi_id = $afes->getCell('R' . $fila)->getValue() . ','; //cfi_id,
            $cfi_anio = $afes->getCell('S' . $fila)->getValue() . ','; //cfi_anio,

            $cvefin = htmlspecialchars($cff_id . $fte_id . $sft_id . $ori_id . $sor_id . $cfi_id . $cfi_anio);

            /**
             * Evaluación de la clave de financiamiento y si el año correponde al 2020
             * Se debe de cambiar dependiendo del ejercicio en al que corresponda
             */

            $anio = substr(APP_EJERCICIO, -2);
            if (
                empty($cff_id) ||
                empty($fte_id) ||
                empty($sft_id) ||
                empty($ori_id) ||
                empty($sor_id) ||
                empty($cfi_id) || !is_numeric(intval($cfi_id)) || intval($cfi_id) == 0 ||
                empty($cfi_anio) || !is_numeric(intval($cfi_anio)) || intval($cfi_anio) > $anio || intval($cfi_anio) == 0
            ) {
                $error .= '||ERROR FILA ' . $fila . ':CVEFIN ' . $cvefin . ': hay columnas vacias, las ultimas dos columnas '
                    . 'deben ser numericas y/o el año no puede ser mayor a ' . $anio . ' o estos dos ultimos no pueden ser 0';
            }

            $modalidad = 'AF' . $fila;
            $tno_id = $afes->getCell('AG' . $fila)->getValue();
            $tno_id = (empty($tno_id)) ? '0,' : $tno_id . ','; //tno_id,

            /* DOCUMENTO */
            $rfc_id = '"' . $afes->getCell('AH' . $fila)->getValue() . '",'; //rfc_id,
            $referencia = '"' . $afes->getCell('AI' . $fila)->getValue() . '",'; //ncp_referencia,
            if ($this->prm->afe_fr == 'S') {
                //Buscamos que el documento no exista
                $this->conectar();
                $this->setSql('
                    SELECT count(*) veces,
                        gpo_id, ur_id, bdo_referencia, rfc_id
                    FROM cat_BancoDocumentos
                    WHERE rfc_id = ?
                        AND bdo_referencia = ?
                    GROUP BY gpo_id,ur_id
                ');
                $params = [$afes->getCell('AH' . $fila)->getValue(), $afes->getCell('AI' . $fila)->getValue()];
                $this->consultar($params);
                $r = $this->extraer();
                $this->desconectar();
            }
            if (empty($referencia) || empty($rfc_id)) {
                $error .= '||ERROR FILA' . $fila . ':La referencia o el RFC_BENEFICIARIO no pueden estar vacios';
            }
            if ($this->prm->afe_fr == 'S' && (($r) && $r['veces'] > 0)) {
                $error .= '||ERROR FILA' . $fila . ':El documento al que hace referencia ya existe';
            }

            if ($error != '') {
                $errores = explode('||', $error);
                foreach ($errores as $val) {
                    if ($val != '') {
                        $this->mixScript('
                            m = $("<div/>").addClass("alert alert-danger").html("' . $val . '");
                            $("#tablaCarga").append(m) 
                        ');
                    }
                }
                $while = false;
                break;
            }
            $fechaReferencia = str_replace("'", '', '"' . $afes->getCell('AJ' . $fila)->getValue() . '",'); //ncp_fechaReferencia,
            $fechaReferencia = $afes->getCell('AJ' . $fila)->getValue(); //ncp_fechaReferencia,

            if (is_string($fechaReferencia)) {
                $fechaReferencia = '"' . $this->date_to_mysqldate(str_replace("'", '', $fechaReferencia)) . '",'; //ncp_fechaReferencia,
                //valida formato
            } elseif (is_numeric($fechaReferencia)) {
                $fechaReferencia = '"' . NumberFormat::toFormattedString($fechaReferencia, 'YYYY-MM-DD') . '",';
            }
            $importeBruto = $afes->getCell('AL' . $fila)->getValue();
            if ($importeBruto == '' || empty($importeBruto)) {
                $error .= '||ERROR CELDA AL' . $fila . ':el importe no puede ser vacio o 0';
            }

            // Tanto las de $cargaRH como las que no lo son siguen ahora éstas reglas
            // por eso lo sacamos del IF que sigue en el código
            // ----------------------------------------------------------------------------------
            $concepto = '"' . htmlspecialchars($afes->getCell('AO' . $fila)->getValue()) . '",'; //ncp_concepto,
            $fechaInicio = $afes->getCell('AP' . $fila)->getValue(); //afe_fechaInicio,

            if (is_string($fechaInicio)) {
                if (trim($fechaInicio) != '') {
                    $fechaInicio = '"' . $this->date_to_mysqldate(str_replace("'", '', $fechaInicio)) . '",'; //ncp_fechaReferencia,
                } else {
                    $fechaInicio = 'NULL, ';
                }
                //valida formato
            } elseif (is_numeric($fechaInicio)) {
                $fechaInicio = '"' . NumberFormat::toFormattedString($fechaInicio, 'YYYY-MM-DD') . '",';
                // Si la fecha viene vacía, tenemos que llenar de todos modos su espacio en el INSERT
            } else {
                $fechaInicio = 'NULL, ';
            }

            $fechaFin = $afes->getCell('AQ' . $fila)->getValue(); //afe_fechaFin,

            if (is_string($fechaFin)) {
                if (trim($fechaFin) != '') {
                    $fechaFin = '"' . $this->date_to_mysqldate(str_replace("'", '', $fechaFin)) . '",'; //ncp_fechaReferencia,
                } else {
                    $fechaFin = 'NULL, ';
                }
            } elseif (is_numeric($fechaFin)) {
                $fechaFin = '"' . NumberFormat::toFormattedString($fechaFin, 'YYYY-MM-DD') . '",';
            } else {
                $fechaFin = 'NULL, ';
            }

            $nccamortiza = $afes->getCell('AS' . $fila)->getValue();
            if (!is_numeric($nccamortiza) && !empty($nccamortiza)) {
                $error .= '||ERROR CELDA AS' . $fila . ':Si va a amortizar el valor debe ser numerico, no texto o formula de excel[' . $nccamortiza . ']';
            }
            $nccamortiza = (empty($nccamortiza)) ? '0,' : $nccamortiza . ','; //nccamortiza,

            // Si es una carga de Recursos Humanos ...
            if ($tipoCarga == '2') {
                $numOrden = '"",';
                $fechaOrden = 'NULL,';

            } elseif ($tipoCarga == '1') {

                $numOrden = '"' . $afes->getCell('AT' . $fila)->getValue() . '",';
                $fechaOrden = $afes->getCell('AU' . $fila)->getValue();

                if (is_string($fechaOrden)) {
                    $fechaOrden = $this->date_to_mysqldate(str_replace("'", '', $fechaOrden)); //ncp_fechaReferencia,
                } elseif (is_numeric($fechaOrden)) {
                    $fechaOrden = '"' . NumberFormat::toFormattedString($fechaOrden, 'YYYY-MM-DD') . '",';
                }
                $fechaOrden = (empty($fechaOrden)) ? 'NULL,' : str_replace("'", '', '"' . $fechaOrden . '",');
            } elseif ($tipoCarga == '3') {
                $numOrden = '"",';
                $fechaOrden = 'NULL,';
            }

            $mno_id = '';
            $smn_id = '';

            // Si en el archivo de excel la modalidad de nómina es entre 61 y 69 entonces la convertimos
            // a los valores que maneja la base de datos
            switch ($afes->getCell($modalidad)->getValue()) {
                case 61:
                    $smn_id = '1,';
                    // no break
                case 62:
                    $smn_id = ($smn_id == '') ? '2,' : $smn_id;
                    // no break
                case 63:
                    $smn_id = ($smn_id == '') ? '3,' : $smn_id;
                    // no break
                case 64:
                    $smn_id = ($smn_id == '') ? '4,' : $smn_id;
                    // no break
                case 65:
                    $smn_id = ($smn_id == '') ? '5,' : $smn_id;
                    // no break
                case 66:
                    $smn_id = ($smn_id == '') ? '6,' : $smn_id;
                    // no break
                case 67:
                    $smn_id = ($smn_id == '') ? '7,' : $smn_id;
                    // no break
                case 68:
                    $smn_id = ($smn_id == '') ? '8,' : $smn_id;
                    // no break
                case 69:
                    $smn_id = ($smn_id == '') ? '9,' : $smn_id;
                    $mno_id = '6,';
                    break;
                default:
                    $mno_id = $afes->getCell($modalidad)->getValue(); // Si no, le ponemos la que traiga ( si no trae le ponemos 0 )
                    $mno_id = (empty($mno_id)) ? 0 : $mno_id;  #si la modalidad no es numero o no es mayor a 9(ultimo valor en el catalogo) parar la carga
                    if (!is_numeric($mno_id) || $mno_id > 9) {
                        $error .= '||ERROR FILA' . $fila . ':Modalidad de nomina erronea';
                    }
                    $mno_id = $mno_id . ',';
                    $smn_id = '0,';
                    break;
            }

            if ($error != '') {
                $errores = explode('||', $error);
                foreach ($errores as $val) {
                    if ($val != '') {
                        $this->mixScript('
                            m = $("<div/>").addClass("alert alert-danger").html("' . $val . '");
                            $("#tablaCarga").append(m)
                        ');
                    }
                }
                $while = false;
                break;
            }
            // REGLA: Cuando se trate de afectaciones o carga de recursos humanos, NO PERMITIREMOS
            // cargar CLCs de Pagos a Terceros.
            // Cuando se trate de CLC Consolidadas sí
            if ($tipoCarga == '3') {
                // En éste sí se permite ... asi que siempre vale true
                $permitirPagosTerceros = true;
            } else {
                // En éstos casos, si es una CLC de pagos a terceros NO cargamos los números de control presupuestales
                $permitirPagosTerceros = strtoupper($tcl_id) !== 'C';
            }

            $tclPermitido = $tcl_id != 'F' && $tcl_id != 'G' && $tclPermitido;
            $tcl_id = '"' . $tcl_id . '",';
            $movimiento = strtoupper($afes->getCell('C' . $fila)->getValue()); // TIPO DE NUMERO DE CONTROL RESUPUESTAL O CONTABLE

            switch ($movimiento) {
                case 'P':
                    /* Si es un número de control Presupuestal
                    Sólo las CLCs consolidadas permiten carga de números de control PRESUPUESTALES cuando
                       se trate de CLCs de pagos a terceros */
                    if ($permitirPagosTerceros) {
                        $valuesP .= ($valuesP != '') ? ', (0,0,0,' : '(0,0,0,';
                        $valuesP .= $tcl_id;
                        $valuesP .= $stc_id;
                        $valuesP .= $gpo_id;
                        $valuesP .= $ur_id;
                        $valuesP .= $ue_id;
                        $valuesP .= $prg_id;
                        $valuesP .= $spr_id;
                        $valuesP .= $py_id;
                        $valuesP .= $oa_id;
                        /* OBJETO DEL GASTO */
                        $pg_id = intval($afes->getCell('K' . $fila)->getValue());
                        $pe_id = intval($afes->getCell('L' . $fila)->getValue());

                        if (!is_numeric($pg_id) || !is_numeric($pe_id) || $pg_id < 1 || $pe_id < 1) {
                            $error .= '||ERROR FILA ' . $fila . ':Objeto del gasto erroneo [pg_id:' . $pg_id . ' - pe_id:' . $pe_id . ']';
                        } else {
                            if ($pg_id > 999 || $pe_id > 999) {
                                $error .= '||ERROR FILA' . $fila . ':Objeto del gasto erroneo, pg_id  y pe_id debe ser inferior a 999';
                            }
                        }

                        $valuesP .= $pg_id . ','; //pg_id,
                        $valuesP .= $pe_id . ','; //pe_id,
                        $valuesP .= $cff_id;
                        $valuesP .= $fte_id;
                        $valuesP .= $sft_id;
                        $valuesP .= $ori_id;
                        $valuesP .= $sor_id;
                        $valuesP .= $cfi_id;
                        $valuesP .= $cfi_anio;
                        $valuesP .= ($tipoCarga == '2') ? '"D",' : '"' . $afes->getCell('T' . $fila)->getValue() . '",'; //tcp_id,
                        $valuesP .= $tno_id;
                        $valuesP .= $mno_id;
                        $valuesP .= $smn_id;
                        $valuesP .= $rfc_id;
                        $valuesP .= $referencia;
                        $valuesP .= $fechaReferencia;
                        $valuesP .= $importeBruto . ','; //ncp_percepcionBruto,

                        if ($tipoCarga == '2' || $afes->getCell('A' . $fila)->getValue() == 'A') {
                            $valuesP .= '0,0,'; //iva_id,ncp_percepcionIva,
                            $valuesP .= $importeBruto . ','; //ncp_percepcionNeto
                        } else {
                            $importeIva = str_replace("'", '', $afes->getCell('AM' . $fila)->getValue());
                            if (strpos($importeIva, '=') === true || !is_numeric($importeIva)) {
                                if (!empty($importeIva)) {
                                    $error .= '||ERROR EN CELDA AM' . $fila . ':No se aceptan formulas o texto en esta celda: ' . htmlspecialchars($importeIva);
                                }
                            }
                            $importeIva = (float)$importeIva * 1;
                            $valuesP .= '0,' . $importeIva . ','; //ncp_percepcionIva,
                            $valuesP .= $importeBruto + $importeIva . ','; //ncp_percepcionNeto,
                        }

                        $valuesP .= $concepto;
                        $valuesP .= $fechaInicio;
                        $valuesP .= $fechaFin;
                        $valuesP .= $numOrden;
                        $valuesP .= $fechaReferencia;
                        $valuesP .= '"' . $_SESSION[_S]['tem_carga'] . '",' . $_SESSION[_S]['usr_id'] . ' )'; //tem_carga,
                        $numRegP++;

                        if ($error != '') {
                            $errores = explode('||', $error);
                            foreach ($errores as $val) {
                                if ($val != '') {
                                    $this->mixScript('
                                        m = $("<div/>").addClass("alert alert-danger").html("' . $val . '");
                                        $("#tablaCarga").append(m)
                                    ');
                                }
                            }
                            $while = false;
                            break;
                        }

                        if ($numRegP > $maxRegistrosPorInsert) {
                            $this->setSql($sqlInsertP . $valuesP);
                            reboot1112:
                            if (!$this->consultar([], false)) {

                                if ($this->getErrno() == 1213) {
                                    $this->bitacora(15, 'deadlock found se reinicia la consulta ');
                                    sleep(2);
                                    goto reboot1112;
                                }

                                /******************* LOG DE CONSULTAS FALLIDAS************************************/
                                if (!file_exists('log')) {
                                    mkdir('log', 0777, true);
                                }
                                if (!file_exists('log/carga')) {
                                    mkdir('log/carga', 0777, true);
                                }
                                $tem_carga = '';
                                $rutalog = 'log/carga/' . $tem_carga . '.log';
                                $file = fopen($rutalog, 'a');
                                $tempdata = '';
                                $hoy = date('Y-m-d-H-i-s');
                                $tempdata .= '|IPADDRESS=' . $_SESSION[_S]['ses_ip'];
                                $tempdata .= '|USRID=' . $_SESSION[_S]['usr_id'];

                                fwrite($file, 'Error de carga masiva en esta consulta:' . $hoy . $tempdata . ':' . $sqlInsertP . $valuesP . PHP_EOL);
                                fclose($file);
                                /******************* LOG DE CONSULTAS FALLIDAS************************************/
                                $this->mixScript('notify("Ocurrio un error en su carga masiva (1): Error ' . $this->getErrno() . '","error",false);');
                                return false;
                            }
                            $valuesP = '';
                            $numRegP = 0;
                        }
                    }
                    break;
                case 'C':
                    $valuesC .= ($valuesC != '') ? ',(0,0,0,' : '(0,0,0,';
                    $valuesC .= $tcl_id;
                    $valuesC .= $stc_id;
                    $valuesC .= $gpo_id;
                    $valuesC .= $ur_id;
                    $valuesC .= $afes->getCell('F' . $fila)->getValue() . ','; //ue_id,
                    $valuesC .= $prg_id;
                    $valuesC .= $spr_id;
                    $valuesC .= $py_id;
                    $valuesC .= $oa_id;
                    $valuesC .= $cff_id;
                    $valuesC .= $fte_id;
                    $valuesC .= $sft_id;
                    $valuesC .= $ori_id;
                    $valuesC .= $sor_id;
                    $valuesC .= $cfi_id;
                    $valuesC .= $cfi_anio;
                    $valuesC .= ($tipoCarga == '2') ? '"D",' : '"' . $afes->getCell('T' . $fila)->getValue() . '",';
                    /* LA CUENTA CONTABLE */

                    $ger_id = intval($afes->getCell('U' . $fila)->getValue());
                    $cgp_id = intval($afes->getCell('V' . $fila)->getValue());
                    $rub_id = intval($afes->getCell('W' . $fila)->getValue());
                    $cgcu_id = intval($afes->getCell('X' . $fila)->getValue());
                    $cgsc_id = intval($afes->getCell('Y' . $fila)->getValue());

                    $cta_gpo_id = intval($afes->getCell('Z' . $fila)->getValue());
                    $cta_ur_id = intval($afes->getCell('AA' . $fila)->getValue());
                    $cta_ue_id = intval($afes->getCell('AB' . $fila)->getValue());

                    $cgc_id = $afes->getCell('AC' . $fila)->getValue();
                    $cta_id = $afes->getCell('AD' . $fila)->getValue();
                    $cta_nombre = $afes->getCell('AE' . $fila)->getValue();

                    /* validaciones basicas de la informacion */
                    if ($ger_id == 0 || !is_numeric($ger_id) || $ger_id > 9 || $ger_id == '') {
                        $error .= '||ERROR EN CELDA U' . $fila . ':Error en el <strong>ger_id</strong>, No puede estar vacio, ser 0 o mayor a 9';
                    }
                    if ($cgp_id == 0 || !is_numeric($cgp_id) || $cgp_id > 9 || $cgp_id == '') {
                        $error .= '||ERROR EN CELDA V' . $fila . ':Error en el <strong>cgp_id</strong>, No puede estar vacio, ser 0 o mayor a 9';
                    }
                    if ($rub_id == 0 || !is_numeric($rub_id) || $rub_id > 9 || $rub_id == '') {
                        $error .= '||ERROR EN CELDA W' . $fila . ':Error en el <strong>rub_id</strong>, No puede estar vacio, ser 0 o mayor a 9';
                    }
                    if ($cgcu_id == 0 || !is_numeric($cgcu_id) || $cgcu_id > 9 || $cgcu_id == '') {
                        $error .= '||ERROR EN CELDA X' . $fila . ':Error en el <strong>cgcu_id</strong>, No puede estar vacio, ser 0 o mayor a 9';
                    }
                    if ($cgsc_id == 0 || !is_numeric($cgsc_id) || $cgsc_id > 9 || $cgsc_id == '') {
                        $error .= '||ERROR EN CELDA Y' . $fila . ':Error en el <strong>cgsc_id</strong>, No puede estar vacio, ser 0 o mayor a 9';
                    }

                    if ($cta_gpo_id == 0 || !is_numeric($cta_gpo_id) || $cta_gpo_id > 9 || $cta_gpo_id == '') {
                        $error .= '||ERROR EN CELDA Z' . $fila . ':Error en el <strong>cta_gpo_id</strong>, No puede estar vacio, ser 0 o mayor a 9';
                    }
                    if ($cta_ur_id == 0 || !is_numeric($cta_ur_id) || $cta_ur_id > 99 || $cta_ur_id == '') {
                        $error .= '||ERROR EN CELDA AA' . $fila . ':Error en el <strong>cta_ur_id</strong>, No puede estar vacio, ser 0 o mayor a 99';
                    }
                    if ($cta_ue_id == 0 || !is_numeric($cta_ue_id) || $cta_ue_id > 999 || $cta_ue_id == '') {
                        $error .= '||ERROR EN CELDA AB' . $fila . ':Error en el <strong>ue_id</strong>, No puede estar vacio, ser 0 o mayor a 999';
                    }

                    if ($cgc_id === '') {
                        $error .= '||ERROR EN CELDA AC' . $fila . ':Error en el <strong>cgc_id</strong>, No puede estar vacio';
                    }
                    if ($cta_id === '') {
                        $error .= '||ERROR FILA AD' . $fila . ':Error en el <strong>cta_id</strong>, No puede estar vacio, ser 0 o mayor a 999';
                    }
                    /* FIN DE LA CUENTA CONTABLE */

                    $valuesC .= $ger_id . ',';
                    $valuesC .= $cgp_id . ',';
                    $valuesC .= $rub_id . ',';
                    $valuesC .= $cgcu_id . ',';
                    $valuesC .= $cgsc_id . ',';
                    $valuesC .= $cta_gpo_id . ',';
                    $valuesC .= $cta_ur_id . ',';
                    $valuesC .= $cta_ue_id . ',';
                    $valuesC .= '"' . str_pad($cgc_id, 3, '0', STR_PAD_LEFT) . '",'; //cgc_id
                    $valuesC .= '"' . str_pad($cta_id, 4, '0', STR_PAD_LEFT) . '",'; //cta_id
                    $valuesC .= ($tipoCarga == '2') ? '"' . $cta_nombre . '",' : '"",'; //cta_nombre

                    $valuesC .= $tno_id;
                    $valuesC .= $mno_id;
                    $valuesC .= $smn_id;
                    $valuesC .= $rfc_id;
                    $valuesC .= $referencia;
                    $valuesC .= $fechaReferencia;
                    switch ($afes->getCell('AK' . $fila)->getValue()) {
                        case '+':
                            $valuesC .= '"+",' . $importeBruto . ',0,';
                            break; //ncc_percepcion,
                        case '-':
                            $valuesC .= '"-",' . '0,' . $importeBruto . ',';
                            break; //ncc_retencion,
                        default:
                            $error .= '||ERROR FILA AK' . $fila . ':Error en el <strong>TIPO MOVIMIENTO (+/-)</strong>, No puede estar vacio,  o debe ser + o -';
                            break;
                    }
                    $valuesC .= $concepto;
                    $valuesC .= $fechaInicio;
                    $valuesC .= $fechaFin;
                    $valuesC .= $nccamortiza;
                    $valuesC .= $numOrden;
                    $valuesC .= $fechaReferencia;
                    $valuesC .= '"' . $_SESSION[_S]['tem_carga'] . '",' . $_SESSION[_S]['usr_id'] . ' )'; //tem_carga,
                    $numRegC++;

                    if ($error != '') {
                        $errores = explode('||', $error);
                        foreach ($errores as $val) {
                            if ($val != '') {
                                $this->mixScript('
                                        m = $("<div/>").addClass("alert alert-danger").html("' . $val . '");
                                        $("#tablaCarga").append(m) ');
                            }
                        }
                        $while = false;
                        break;
                    }

                    if ($numRegC > $maxRegistrosPorInsert) {
                        $this->setSql($sqlInsertC . $valuesC);

                        reboot1261:
                        if (!$this->consultar([], false)) {
                            if ($this->getErrno() == 1213) {
                                $this->bitacora(15, 'deadlock foun se reinicia la consulta ');
                                sleep(2);
                                goto reboot1261;
                            }

                            /******************* LOG DE CONSULTAS FALLIDAS************************************/
                            $tem_carga = '';
                            $rutalog = 'log/carga/' . $tem_carga . '.log';
                            $file = fopen($rutalog, 'a');
                            $tempdata = '';
                            $hoy = date('Y-m-d-H-i-s');
                            $tempdata .= '|IPADDRESS=' . $_SESSION[_S]['ses_ip'];
                            $tempdata .= '|USRID=' . $_SESSION[_S]['usr_id'];

                            fwrite($file, 'Error de carga masiva en esta consulta:' . $hoy . $tempdata . ':' . $sqlInsertC . $valuesC . PHP_EOL);
                            fclose($file);
                            $this->mixScript('notify("Ocurrio un error en su carga masiva (2) : Error ' . $this->getErrno() . '","error",false);');
                            return false;
                            /******************* LOG DE CONSULTAS FALLIDAS************************************/
                        }
                        $valuesC = '';
                        $numRegC = 0;
                    }
                    break;
            }
            $registrosDeLaCarga++;
            $fila++;
        }

        if (!$while) {
            return false;
        }

        if ($registrosDeLaCarga >= $minRegistrosPorCarga) {
            if ($tclPermitido) {
                if ($valuesC != '') {
                    $this->conectar();
                    $this->setSql($sqlInsertC . $valuesC);
                    $this->consultar();
                    $this->desconectar();
                }
                if ($valuesP != '') {
                    $this->conectar();
                    $this->setSql($sqlInsertP . $valuesP);
                    $this->consultar();
                    $this->desconectar();
                }
                $this->actualizaCarga(16, 'TERMINA FUNCION cargaAfectaciones y regresa true');
                $datetime2 = new DateTime('now');
                $interval = date_diff($datetime1, $datetime2);
                $tiempo = ($interval->format('%i minutos %s segundos'));
                $this->bitacora(16, 'El proceso de cargar afectaciones tardo ' . $tiempo);
                return true;
            } else {
                $this->actualizaCarga(99, 'FALLA LA CARGA LINEA 1019');
                $this->mixScript('
                    var dataCarga = [];
                    dataCarga.push({
                        "a": "ERROR", 
                        "i"  : "No es posible realizar cargas de Administracion Directa y/o Contrato ."
                    });
                    gridCarga.updateRowCount();   gridCarga.render();   gridCarga.scrollRowIntoView( dataCarga.length, false );
                ');
                $datetime2 = new DateTime('now');
                $interval = date_diff($datetime1, $datetime2);
                $tiempo = ($interval->format('%i minutos %s segundos'));
                $this->bitacora(99, 'El proceso de cargar afectaciones tardo ' . $tiempo);
                return false;
            }
        } else {
            $datetime2 = new DateTime('now');
            $interval = date_diff($datetime1, $datetime2);
            $tiempo = ($interval->format('%i minutos %s segundos'));
            $this->bitacora(99, 'El proceso de cargar afectaciones tardo ' . $tiempo);
            $this->actualizaCarga(99, 'FALLA LA CARGA LINEA 1031');
            $this->mixScript('
                notify("Para poder realizar una CARGA MASIVA minimo debe contener 10 registros.","error");
            ');
            return false;
        }
    }
    //VALIDAMOS LAS AFECTACIONES:
    // 1. SI ES CARGA DE AFECTACIONES, SOLO DEBEN VENIR DE UNA SOLA UR
    // 2. SI ES DE CARGA MASIVA DE CLCs, UNA UR POR CLC
    // 3. SI ES DE CARGA MASIVA DE CLCs CONSOLIDADAS, PUEDEN VENIR VARIAS URs POR CADA CLC
    // valores para $tipoCarga: 1 = sólo Afectaciones      2 = CLC Recursos Humanos      3 = CLC Consolidadas
    private function validaReglasAfectaciones($tipoCarga)
    {
        if ($this->getEstadoCarga($_SESSION[_S]['tem_carga']) != 16) {
            $this->mixScript('notify("Ya se inicio LA VALIDACION","warning");');
            return false;
        }

        $datetime1 = new DateTime('now');
        $this->actualizaCarga(36, 'INICIA VALIDACION DE REGLAS DE AFECTACION');

        $sql = '';
        // Si solo están cargando afectaciones, no debe venir más de una UR en los números de control
        if ($tipoCarga == '1') {
            $sql .= '
                SELECT DISTINCT CONCAT( "TODAS las afectaciones deben corresponder a la misma UR ( afe=", clc_referencia, ", ur=", ur ) AS error 
                    FROM (
                        SELECT clc_referencia, CONCAT( gpo_id, ur_id ) AS ur
                        FROM ' . BD_TEMPORAL . '.tem_Presupuestal
                        WHERE tem_carga = "' . $_SESSION[_S]['tem_carga'] . '"
                        GROUP BY clc_referencia, gpo_id, ur_id
                    ) AS X
                    GROUP BY clc_referencia, ur
                    HAVING COUNT(*) > 1
            ';

            $sql .= ' UNION ';

            // Y todos los registros deben corresponder a la UR actual Sólo puedes cargar información de la UR
            $sql .= '
                SELECT DISTINCT CONCAT( "Sólo puedes cargar información de la UR ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '") AS error 
                FROM (
                    SELECT clc_referencia, CONCAT( gpo_id, ur_id ) AS ur
                    FROM ' . BD_TEMPORAL . '.tem_Presupuestal
                    WHERE tem_carga = "' . $_SESSION[_S]['tem_carga'] . '" AND CONCAT( gpo_id, ur_id ) <> "' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '"
                    GROUP BY clc_referencia, gpo_id, ur_id
                ) AS X
                GROUP BY clc_referencia, ur
            ';
        }

        // Si es carga masiva de CLC de Recursos Humanos, sólo se permite una UR por Afectación
        if ($tipoCarga == '2') {
            $sql .= '
                SELECT DISTINCT CONCAT( "NO puedes usar más de una UR por afectación ( afe=", clc_referencia, ", ur=", ur ) AS error 
                FROM ( SELECT clc_referencia, CONCAT( gpo_id, ur_id ) AS ur
                    FROM ' . BD_TEMPORAL . '.tem_Presupuestal
                    WHERE tem_carga = "' . $_SESSION[_S]['tem_carga'] . '"
                    GROUP BY clc_referencia, gpo_id, ur_id
                ) AS X
                GROUP BY clc_referencia, ur
                HAVING COUNT(*) > 1
            ';
        }

        // Aunque no hay validación, en CLC consolidada pueden venir varias URs por afectación
        // Y le generaremos una CLC por cada UR distinta que traiga
        // Nada que validar ...
        // Validamos ...
        $this->setSql($sql);
        $this->conectar();
        $this->consultar();
        $this->desconectar();

        $numErrores = $this->getNumeroRegistros();
        // Si hubo errores ... los mostramos
        if ($numErrores > 0) {
            $this->actualizaCarga(95, 'ERRORES DE VALIDACION DE AFECTACION');
            $conexion = new AccesoDatos();
            $conexion->conectar();

            $this->mixScript('notify("Ocurrio un error en la afectacion ","error");');

            while ($row = $this->extraer()) {
                $this->mixScript('
                    $("#listaCarga").append(\'<li class="list-group-item datoCarga"><label class="alert alert-danger">ERROR: ' . $row['error'] . '   </label></li>\');
                ');
            }

            $conexion->desconectar();
            $datetime2 = new DateTime('now');
            $interval = date_diff($datetime1, $datetime2);
            $tiempo = ($interval->format('%i minutos %s segundos'));
            $this->bitacora(99, 'El proceso de validacion de reglas de afectacion  tardo ' . $tiempo);
            return false;
            // Si hubo errores les avisamos ...
        } else {
            $datetime2 = new DateTime('now');
            $interval = date_diff($datetime1, $datetime2);
            $tiempo = ($interval->format('%i minutos %s segundos'));
            $this->bitacora(61, 'El proceso de validacion de reglas de afectacion  tardo ' . $tiempo);
            $this->actualizaCarga(61, 'FIN DE VALIDACION DE REGLAS DE AFECTACION');
            return true;
        }
    }

    private function dibujaPantalla()
    {
        $this->mixScript('$("#tituloPantalla").hide();');
        // Si no tiene permisos de cargaMasiva para cargas masivas, no dibujamos el grid
        if (!$this->cargaMasiva) {
            return false;
        }

        $this->mixScript('
            $(".input-group-addon").css("padding","2px 12px");
            // Para que el GRID se ajuste al espacio disponible en pantalla
            $("#myGridCarga").height( 
                $(document).height() -
                $("header").outerHeight() -
                $("#sticky").outerHeight() -
                $("#buscarMaestro").outerHeight() -
                $("footer").outerHeight() - 80
            );

            setTimeout( function() { $( "#ctaMayor" ).focus() }, 100 );

            function HTMLFormatter(row, cell, value, columnDef, dataContext) {
                return value;
            }
            var gridCarga;
            var dataCarga = [];
            var options = {
                enableCellNavigation: true, /* Para permitir que se seleccione una celda */
                enableColumnReorder: false,
                forceFitColumns: true,
                explicitInitialization: true
            };
            var columnsCarga = [
                {id:"accion", name:"ACCION",      field:"a", minWidth: 200, sortable: false, formatter: HTMLFormatter },
                {id:"info",   name:"INFORMACION", field:"i", minWidth: 400, sortable: false, formatter: HTMLFormatter }
            ];
            gridCarga = new Slick.Grid($("#myGridCarga"), dataCarga, columnsCarga, options); 
            // Para que se seleccione toda la línea
            gridCarga.setSelectionModel(new Slick.RowSelectionModel());
            gridCarga.render();
            gridCarga.init();
        ');
    }

    // Checamos en la tabla ctrl2020Bitacora.tem_Clc que se hayan cargado cuentas realmente ... y buscamos mensajes de error "patito"
    private function resultadoCarga()
    {
        // Obtenemos el número de carga temporal asignado...
        $tem_carga = $this->prm->tem;
        // Revisamos que la cuenta exista a nivel de mayor

        $sql = '
            SELECT clc_concepto AS error, "---> ERROR DE CARGA" AS cuenta 
            FROM ' . BD_TEMPORAL . '.tem_Clc 
            WHERE tem_carga = ? AND clc_referencia = 9999;';
        $this->setSql($sql);
        $this->conectar();
        $this->consultar([$tem_carga]);
        $this->desconectar();

        $numErrores = $this->getNumeroRegistros();

        // Si hubo errores ... avisamos ...
        if ($numErrores > 0) {
            while ($row = $this->extraer()) {
                $this->mixScript('
                    dataCarga.push({ "a": "' . $row['cuenta'] . '", "i": "' . $row['error'] . '" } );
                ');
            }
            $this->mixScript('
                gridCarga.updateRowCount();
                gridCarga.render();
                gridCarga.scrollRowIntoView( dataCarga.length, false );
                $("#copiar").attr("style", "visibility: visible");
                $("#preparar").attr("style", "display: none");
                try { window.espera.stop(); } catch(err) { /* nada */ };
            ');
            // Si NO hubo errores, checamos que realmente exista algo para cargar ...
        } else {
            // Revisamos que realmente se haya subido algo (que no sea error patito) a la temporal
            $sql = '
                SELECT SUM( total ) AS total FROM (
                SELECT COUNT(*) AS total FROM ' . BD_TEMPORAL . '.tem_Clc 
                WHERE tem_carga = :tem_carga
                UNION
                SELECT COUNT(*) AS total FROM ' . BD_TEMPORAL . '.tem_Presupuestal
                WHERE tem_carga = :tem_carga
                UNION
                SELECT COUNT(*) AS total FROM ' . BD_TEMPORAL . '.tem_Contable
                WHERE tem_carga = :tem_carga
                ) AS x;
            ';

            $this->setSql($sql);
            $this->conectar();
            $this->consultar([':tem_carga' => $tem_carga]);
            $this->desconectar();
            $row = $this->extraer();

            if ($row['total'] > 0) {
                // Si el proceso dejó al menos una cuenta lista para revisar ...

                $this->mixScript('
                    dataCarga.push( {
                        "a": "COPIADO !!!", 
                        "i"  : "Cargado, puedes Preparar la información"
                    } );
                    gridCarga.updateRowCount();
                    gridCarga.render();
                    gridCarga.scrollRowIntoView( dataCarga.length, false );
                    
                    $("#preparar").attr("style", "visibility: visible");
                    $("#copiar").attr("style", "display: none");
                    try { window.espera.stop(); } catch(err) { /* nada */ };
                ');

                // Si no ... algo pasó ...
            } else {
                $this->mixScript('
                    dataCarga.push( {
                        "a": "ERROR", 
                        "i"  : "<b>NINGUNA Información se procesó ... revisa tu archivo O REVISA QUE LA UR ACTUAL SEA LA CORRECTA.<b>"
                    } );
                    dataCarga.push( {
                        "a": "-", 
                        "i"  : "<b style=\'color:#FF0000;font-size: 11px;\'>Para realizar una carga masiva mínimo debe contener 10 registros en el archivo a cargar.</b>"
                    } );
                    gridCarga.updateRowCount();   gridCarga.render();   gridCarga.scrollRowIntoView( dataCarga.length, false );
                    $("#copiar").attr("style", "visibility: visible");
                    $("#preparar").attr("style", "display: none");
                    try { window.espera.stop(); } catch(err) { /* nada */ };
                ');
            }
        }
    }

    // Una vez cargada la información del archivo de Excel, ponemos la información faltante ( encuadres por ejemplo ) para poder continuar con su revisión formal
    private function preparar()
    {
        // Obtenemos el número de carga temporal asignado...
        // Valores para tc ( Tipo de Carga ) : 1 = sólo Afectaciones    2 = CLC Recursos Humanos      3 = CLC Consolidada
        // En carga clc RH y afectaciones, limitamos a que NO sean de Pagos a Terceros (C)
        // Pero en afectaciones limitamos a que NO sean de Pagos a Terceros (C) y además NO de Gastos de Operación (B)
        // PENDIENTE
        // MARCOS: PREGUNTA ¿ porqué si es consolidada los Gastos de Operación (B) sí incluyen los campos de tno_id, mno_id smn_id ?
        if ($this->getEstadoCarga($_SESSION[_S]['tem_carga']) != 35) {
            $this->mixScript('notify("ya se inicio esta funcion","warning");');
            return false;
        }
        $datetime1 = new DateTime('now');
        $this->actualizaCarga(69, 'INICIA LA PREPARACION DE DATOS- LINEA 1643');

        if ($this->tipoCarga == '3') {
            $filtraTipoCLC = 'tcl_id NOT IN("B","C") ';
        } else {
            $filtraTipoCLC = 'tcl_id NOT IN("C") ';
        }
        //Vincula las afectaciones con las CLCS correspondientes
        $this->conectar();
        //Se actualiza el campo ecf_id de acuerdo con la clave de financiamiento cargada
        // Siempre y cuando estén cargando CLC, y no solamente afectaciones
        if ($this->tipoCarga == '2' || $this->tipoCarga == '3') {
            $this->setSql('
                UPDATE ' . BD_TEMPORAL . '.tem_Clc a
                JOIN enc_ClaveFinanciamiento c
                USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                SET a.ecf_id = c.ecf_id
                WHERE tem_carga = ?
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);
        }

        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Presupuestal a
            JOIN enc_Programatico b
            USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            JOIN enc_ClaveFinanciamiento c
            USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            SET a.epg_id = b.epg_id, a.ecf_id = c.ecf_id
            WHERE tem_carga = ?
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->logtxt(1780, $this->getSql(), ' SE ACTUALIZA EPG Y ECF');


        // En COMPROMISOS no usamos ctrl2020Temporal.tem_Contable
        if ($this->tipoCarga <> '3') {
            //Se actualiza el campo epg_id y ecf_id en ctrl2020Temporal.tem_Contable
            $this->setSql('
                UPDATE ' . BD_TEMPORAL . '.tem_Contable a
                JOIN enc_Programatico b
                USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                JOIN enc_ClaveFinanciamiento c
                USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                SET a.epg_id = b.epg_id, a.ecf_id = c.ecf_id
                WHERE tem_carga = ?
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);
        }
        // Cuando sea una CLC de pagos a terceros, buscamos el ncc_id que venga en el excel en las CLCs de
        // servicios personales y le ponemos afe_fechaInicio y afe_fechaFin a la temporal
        // PENDIENTE:
        // Averiguar porqué no se hace ésto en la carga de CLCs consolidadas
        if ($this->tipoCarga != '3' && $this->tipoCarga != '4') {
            $this->setSql('
                UPDATE ' . BD_TEMPORAL . '.tem_Contable a
                JOIN(
                    SELECT ncc_id ncc_amortiza,afe_fechaInicio,afe_fechaFin
                    FROM scp_Contable 
                    JOIN scp_Afectacion USING(gpo_id,ur_id,clc_id,afe_id)
                    WHERE tcl_id = "A"
                )fechas USING(ncc_amortiza)
                SET a.tno_id = 0,a.mno_id= 0,a.smn_id= 0,a.afe_fechaInicio = fechas.afe_fechaInicio, a.afe_fechaFin = fechas.afe_fechaFin
                WHERE tcl_id = "C" AND stc_id = 1 AND tem_carga = ?;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);
        }
        // Cuando sólo se carguen CLCs de Recursos Humanos y Consolidadas, agrupamos primero por clc_referencia ...
        $referencia = ($this->tipoCarga != '1') ? ' clc_referencia, ' : '';
        // En COMPROMISOS no usamos ésta parte
        if ($this->tipoCarga <> '3') {
            //se generan las afectaciones de SERVICIOS PERSONALES en la tabla temporal
            $this->setSql('
                INSERT INTO ' . BD_TEMPORAL . '.tem_Afectacion (gpo_id,ur_id,clc_id,afe_id,epg_id,ecf_id,ue_id,tcp_id,tcl_id,stc_id,tno_id,mno_id,smn_id,afe_fechaInicio,afe_fechaFin,clc_referencia,tem_carga,usr_id)
                SELECT DISTINCT gpo_id,ur_id,clc_id,afe_id,epg_id,ecf_id,ue_id,tcp_id,tcl_id,stc_id,tno_id,mno_id,smn_id,afe_fechaInicio,afe_fechaFin,clc_referencia,tem_carga ,' . $_SESSION[_S]['usr_id'] . '
                FROM (
                    SELECT gpo_id,ur_id,clc_id,afe_id,epg_id,ecf_id,ue_id,tcp_id,tcl_id,stc_id,tno_id,mno_id,smn_id,afe_fechaInicio,afe_fechaFin,MAX(clc_referencia) AS clc_referencia,tem_carga
                    FROM ' . BD_TEMPORAL . '.tem_Presupuestal
                    WHERE ( tcl_id = "A" OR (tcl_id = "C" AND stc_id = 1) ) 
                    AND tem_carga = :tem_carga
                    GROUP BY ' . $referencia . 'tem_carga,gpo_id,ur_id,clc_id,afe_id,epg_id,ecf_id,ue_id,tcp_id,tcl_id,stc_id,tno_id,mno_id,smn_id,afe_fechaInicio,afe_fechaFin
                    UNION ' . ($this->tipoCarga == '3' ? 'ALL' : '') . ' 
                    SELECT gpo_id,ur_id,clc_id,afe_id,epg_id,ecf_id,ue_id,tcp_id,tcl_id,stc_id,tno_id,mno_id,smn_id,afe_fechaInicio,afe_fechaFin,MAX(clc_referencia) AS clc_referencia,tem_carga
                    FROM ' . BD_TEMPORAL . '.tem_Contable
                    WHERE ( tcl_id = "A" OR (tcl_id = "C" AND stc_id = 1) ) 
                    AND  tem_carga = :tem_carga
                GROUP BY ' . $referencia . 'tem_carga,gpo_id,ur_id,clc_id,afe_id,epg_id,ecf_id,ue_id,tcp_id,tcl_id,stc_id,tno_id,mno_id,smn_id,afe_fechaInicio,afe_fechaFin
                ) afectaciones
            ');
            $this->consultar([':tem_carga' => $_SESSION[_S]['tem_carga']]);
            // Nota Personal: En el caso de CLCs consolidadas, usaremos UNION ALL,
            // y en el caso de CLCs de RH y Afectaciones, únicamente usaremos UNION
            // Lo anterior para asegurarnos en las consolidadas que todos los registros son incluídos (checado con Anuar)
            //se generan las afectaciones (los demas tipos) en la tabla temporal
            $this->setSql('
                INSERT INTO ' . BD_TEMPORAL . '.tem_Afectacion (gpo_id,ur_id,clc_id,afe_id,epg_id,ecf_id,ue_id,tcp_id,tcl_id,stc_id,tno_id,mno_id,smn_id,clc_referencia,tem_carga,afe_fondoRotatorio,usr_id) 
                SELECT DISTINCT gpo_id,ur_id,clc_id,afe_id,epg_id,ecf_id,ue_id,tcp_id,tcl_id,stc_id,tno_id,mno_id,smn_id,clc_referencia,tem_carga,"' . $this->prm->afe_fr . '",' . $_SESSION[_S]['usr_id'] . '
                FROM (
                    SELECT gpo_id,ur_id,clc_id,afe_id,epg_id,ecf_id,ue_id,tcp_id,tcl_id,stc_id,tno_id,mno_id,smn_id,MAX(clc_referencia) AS clc_referencia,tem_carga
                    FROM ' . BD_TEMPORAL . '.tem_Presupuestal
                    WHERE  tcl_id ' . (($this->tipoCarga == '3') ? "!= 'A' " : "NOT IN ('A','C') ") . '
                    AND tem_carga = :tem_carga
                    GROUP BY ' . $referencia . 'tem_carga,gpo_id,ur_id,clc_id,afe_id,epg_id,ecf_id,ue_id,tcp_id,tcl_id,stc_id,tno_id,mno_id,smn_id,afe_fechaInicio,afe_fechaFin
                    UNION ' . ($this->tipoCarga == '3' ? 'ALL' : '') . ' 
                    SELECT gpo_id,ur_id,clc_id,afe_id,epg_id,ecf_id,ue_id,tcp_id,tcl_id,stc_id,tno_id,mno_id,smn_id,MAX(clc_referencia) AS clc_referencia,tem_carga
                    FROM ' . BD_TEMPORAL . '.tem_Contable
                    WHERE  tcl_id != "A" AND NOT (tcl_id = "C" AND stc_id = 1) 
                    AND tem_carga = :tem_carga
                    GROUP BY ' . $referencia . 'tem_carga,gpo_id,ur_id,clc_id,afe_id,epg_id,ecf_id,ue_id,tcp_id,tcl_id,stc_id,tno_id,mno_id,smn_id,afe_fechaInicio,afe_fechaFin
                ) afectaciones
            ');
            $this->consultar([':tem_carga' => $_SESSION[_S]['tem_carga']]);

            /*
                Nota personal: Aunque las dos consultas siguientes son iguales en lo que hacen, el detalle está en que determinados
                tipos de CLC traen en NULL los campos de tno_id, mno_id y smn_id.   Y si son nulos no funciona el inner join.
                Por lo tanto se hace una consulta para aquellos tipos de CLC que sí tengan esos campos, y luego otra
                para los que NO sean ese tipo, y entre ambas consultas sólo varian los campos que se usan en USING()
                */

            $this->setSql('
                UPDATE ' . BD_TEMPORAL . '.tem_Afectacion a
                LEFT JOIN ' . BD_TEMPORAL . '.tem_Clc c USING(clc_referencia,gpo_id,ur_id,ecf_id,tcl_id,stc_id,tno_id,mno_id,smn_id,tem_carga)
                SET a.tem_Clc = c.tem_Clc
                WHERE tem_carga = ?
                AND ' . $filtraTipoCLC . ';
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);

            // Le ponemos a las tablas el campo tem_Clc que permite vincular con la tabla ctrl2020Temporal.tem_Afectacion
            $this->setSql('
                UPDATE ' . BD_TEMPORAL . '.tem_Afectacion a
                LEFT JOIN ' . BD_TEMPORAL . '.tem_Clc c USING(clc_referencia,gpo_id,ur_id,ecf_id,tcl_id,stc_id,tem_carga)
                SET a.tem_Clc = c.tem_Clc
                WHERE tem_carga = ?
                AND ' . str_replace('NOT ', '', $filtraTipoCLC) . ';
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);

            // Nota Personal: En la carga de CLC de RH se usaba USING(`tem_afe`,`tem_carga`) solamente para la vinculación
            // y era para todos los tipos de CLC.   Sin embargo en la carga de CLC Consolidadas era más específica la vinculación
            // ya que seguía la regla de separar las CLCs de Servicios Personales (A) de las demás, por el mayor número de
            // campos que se usan en la vinculación.   Decidí usar la segunda por ser más completa
            // Se vinculan las afectaciones creadas con los numeros de control
            // PARA CLCs DE SERVICIOS PERSONALES
            $this->setSql('
                UPDATE ' . BD_TEMPORAL . '.tem_Presupuestal p
                LEFT JOIN ' . BD_TEMPORAL . '.tem_Afectacion a USING(' . $referencia . ' tem_carga,gpo_id,ur_id,ue_id,epg_id,ecf_id,tcp_id,tcl_id,stc_id,tno_id,mno_id,smn_id,afe_fechaInicio,afe_fechaFin)
                SET p.tem_afe = a.tem_afe, p.tem_Clc = a.tem_Clc
                WHERE ( tcl_id = "A" OR (tcl_id = "C" AND stc_id = 1) )
                AND tem_carga = ?;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);

            $this->setSql('
                UPDATE ' . BD_TEMPORAL . '.tem_Contable c
                LEFT JOIN ' . BD_TEMPORAL . '.tem_Afectacion a USING(' . $referencia . ' tem_carga, gpo_id, ur_id, ue_id, epg_id, ecf_id, tcp_id, tcl_id, stc_id, tno_id, mno_id, smn_id, afe_fechaInicio, afe_fechaFin)
                SET c.tem_afe = a.tem_afe, c.tem_Clc = a.tem_Clc
                WHERE  tcl_id = "A"
                AND tem_carga = ?;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);

            $this->setSql('
                UPDATE ' . BD_TEMPORAL . '.tem_Contable c
                LEFT JOIN ' . BD_TEMPORAL . '.tem_Afectacion a USING(' . $referencia . ' tem_carga , gpo_id, ur_id, ue_id, epg_id, ecf_id, tcl_id, stc_id, afe_fechaInicio, afe_fechaFin)
                SET c.tem_afe = a.tem_afe, c.tem_Clc = a.tem_Clc
                WHERE tcl_id = "C" AND stc_id = 1 
                AND tem_carga = ?;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);

            $this->setSql('
                UPDATE ' . BD_TEMPORAL . '.tem_Contable a
                SET a.tno_id = null,a.mno_id= null,a.smn_id= null
                WHERE tcl_id = "C" AND stc_id = 1
                AND tem_carga = ?;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);

            $this->setSql('
                UPDATE ' . BD_TEMPORAL . '.tem_Afectacion a
                SET a.tno_id = null,a.mno_id= null,a.smn_id= null
                WHERE tcl_id = "C" AND stc_id = 1
                AND tem_carga = ?;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);
            // Se vinculan las afectaciones creadas con los numeros de control
            // PARA LOS DEMÁS TIPOS DE CLCs
            $this->setSql('
                UPDATE ' . BD_TEMPORAL . '.tem_Presupuestal p
                LEFT JOIN ' . BD_TEMPORAL . '.tem_Afectacion a USING(' . $referencia . ' tem_carga, gpo_id, ur_id, ue_id, epg_id, ecf_id, tcp_id, tcl_id, stc_id)
                SET p.tem_afe = a.tem_afe, p.tem_Clc = a.tem_Clc
                WHERE  tcl_id != "A"
                AND NOT (tcl_id = "C" AND stc_id = 1)
                AND tem_carga = ?;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);

            $this->setSql('
                UPDATE ' . BD_TEMPORAL . '.tem_Contable c
                LEFT JOIN ' . BD_TEMPORAL . '.tem_Afectacion a USING(' . $referencia . ' tem_carga, gpo_id, ur_id, ue_id, epg_id, ecf_id, tcp_id, tcl_id, stc_id)
                SET c.tem_afe = a.tem_afe,c.tem_Clc = a.tem_Clc
                WHERE  tcl_id != "A" 
                AND NOT (tcl_id = "C" AND stc_id = 1)
                AND tem_carga = ?;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);

            $this->setSql('
                UPDATE ' . BD_TEMPORAL . '.tem_Afectacion
                SET tcp_id = NULL
                WHERE  tcl_id = "C" 
                AND tem_carga = ?;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);
            // Si es de recursos humanos, insertamos cuentas faltantes,
            // Si no, DEBEN existir en el catálogo de la UR que haga cargas de afectaciones
            if ($this->tipoCarga == '2') {
                // inserta las cuentas contables que existan en cuentaCuarto
                $this->setSql('
                    SET 
                    @bit_observacion= "DE CARGA MASIVA",
                    @usr_id = ?,
                    @usr_ip = INET_ATON(?);
                ');
                $this->consultar([$_SESSION[_S]['usr_id'], $_SESSION[_S]['tem_usr_ip']]);

                $this->setSql('
                    INSERT INTO cg_PlanCuentas (ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id,cta_nombre,usr_id,usr_fecha)
                    SELECT DISTINCT ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id,t.cta_nombre,' . $_SESSION[_S]['usr_id'] . ' usr_id,"' . $this->hoy . '" usr_fecha
                    FROM ' . BD_TEMPORAL . '.tem_Contable t
                    JOIN cg_CuentaCuarto USING (ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id)
                    LEFT JOIN cg_PlanCuentas c USING (ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id)
                    WHERE isnull(cta_cuenta) AND t.tem_carga = ?;
                ');
                $this->consultar([$_SESSION[_S]['tem_carga']]);
            }
        }
        $this->desconectar();

        // Ya que preparamos la info, les mostramos el botón de revisar
        $this->actualizaCarga(70, 'FINALIZA LA PREPARACION DE DATOS SIN CONTRATIEMPOS');
        $boton = new stdClass();
        $boton->id = 'btnRevisarArchivo';
        $boton->title = 'Buscar Archivo';
        $boton->color = 'gris';
        $boton->value = ' Revisar';
        $boton->icon = 'check';
        $boton->click = '
            $(this).attr("disabled","disabled").css("background-color","#348fe2")
                .css("border-color","#858585")
                .append($("<img/>").attr("src","img/loader2.gif"));
            comunica("' . $this->pantalla . '",{
                metodo:"revisarDatos",
                afe_fr:$("#afe_fondoRotatorio").attr("value")
            });
        ';

        $this->setContenedor('botonesCarga');
        $this->mixHtml($this->ponBoton($boton));
        $this->mixScript('
            $("#listaCarga").append(\'<li class="list-group-item datoCarga"><label class="alert-success">Paso 2 completo:</label><b>INFORMACIÓN PREPARADA</b> Puedes proceder a Revisar la información.</li>\');
            $("#botonesCarga").prepend(\'<button type="button" value=" copiar"class="btn btn-outline-secondary me-1  disabled" <span class="fa fa-exchange"></span> copiar</button><button type="button" value=" copiar"class="btn btn-outline-secondary me-1 disabled" <span class="fa fa-certificate"></span> preparar</button>\');
        ');
        $datetime2 = new DateTime('now');
        $interval = date_diff($datetime1, $datetime2);
        $tiempo = ($interval->format('%i minutos %s segundos'));
        $this->bitacora(70, 'La preparacion de datos tardo ' . $tiempo);
    }

    // Revisamos las cuentas cargadas en la tabla temporal ...
    private function revisar()
    {
        // Obtenemos el número de carga temporal asignado...
        $tem_carga = $_SESSION[_S]['tem_carga'];

        if ($this->getEstadoCarga($tem_carga) != 70) {
            $this->mixScript('notify("Ya se inicio el copiado","warning");');
            return false;
        }

        $datetime1 = new DateTime('now');
        $this->actualizaCarga(5, 'INICIA LA REVISION DE DATOS');
        /* REVISAR LAS CLAVES DE FINANCIAMIENTO CARGADAS  */

        $ecf = $this->model->fn_RevisaCveFin($_SESSION[_S]['tem_carga']);

        if ($ecf !== true) {
            $this->setContenedor('tablaCarga');
            $this->mixHtml($ecf['html']);
            $this->logtxt(__LINE__, $ecf['sql'], 'ERROR EN CLAVES DE FINANCIAMIENTO');
            return;
        }
        // ENCUADRES PROGRAMATICOS

        $epg = $this->model->fn_RevisaObra($_SESSION[_S]['tem_carga']);

        if ($epg !== true) {
            $this->setContenedor('tablaCarga');
            $this->logtxt(__LINE__, $this->getSql(), 'EXISTEN ERRORES EN LOS ENCUADRES PROGRAMATICOS');
            $this->mixHtml($epg['html']);
            return;
        }
        /*     VERIFICAR SI HAY BLOQUEOS DE ALGUN MOMENTO CONTABLE COMPROMETIDO,DEVENGADO EJERCIDO
                EN ESTE CASO CUALQUIER MOMENTO QUE ESTE BLOQUEADO IMPLICA EL BLOQUEO DE LA TOTALIDAD
                DE LOS MOMENTOS CONTABLES */

        $opc = new stdClass();
        /**
         *  1 afectacion
         *  2 clc recursos humanos
         *  3 clc consolidada
         *  4 compromiso
         */
        if ($this->tipoCarga == 1) {
            $opc->sis_id = 'S';
            $opc->men_id = '2';
            $opc->opc_id = '1';
        } elseif ($this->tipoCarga == 2 || $this->tipoCarga == 3) {
            $opc->sis_id = 'S';
            $opc->men_id = '2';
            $opc->opc_id = '2';
        } else {
            $opc->sis_id = 'S';
            $opc->men_id = '2';
            $opc->opc_id = '3';
        }

        $blq = $this->model->fn_RevisaBloqueos($_SESSION[_S]['tem_carga'], $opc);

        if ($blq !== true) {
            $this->setContenedor('tablaCarga');
            $this->mixHtml($blq['html']);
            $this->mixScript('
                $("#btnRevisarArchivo").remove();
                $("#botonesCarga").append(\'<button type="button" value=" copiar"class="btn btn-outline-secondary  disabled" <span class="glyphicon glyphicon-check"></span> revisar</button>\');
            ');
            $this->logtxt(__LINE__, $this->getSql(), 'EXISTEN BLOQUEOS DE CLC');
            return;
        }

        /**METODO PARA VERIFICAR LOS FOLIOS DE ADECUACIÓN EN PROCESO DE CARGA MASIVA Ticket #550148*/
        $extisenFoliosAdecuaciones = $this->model->fn_RevisaFoliosAdecuaciones($_SESSION[_S]['tem_carga']);

        /**SI SOLO EL METODO ENVÍA UN ARREGLO MOSTRARÁ LAS CLAVES PRESUPUESTALES */
        if ($extisenFoliosAdecuaciones !== true) {
            $this->setContenedor('tablaCarga');
            $this->mixHtml($extisenFoliosAdecuaciones['html']);
            $this->mixScript('
                $("#btnRevisarArchivo").remove();
                $("#botonesCarga").append(\'<button type="button" value=" copiar"class="btn btn-outline-secondary  disabled" <span class="glyphicon glyphicon-check"></span> revisar</button>\');
            ');
            $this->logtxt(__LINE__, $extisenFoliosAdecuaciones['sql'], 'EXISTEN FOLIOS DE ADECUACIONES EN TRANSITO');
            return false;
        }

        #se bloque las claves presupuestales para que no genere ningun movimiento(modificacion,compromiso,afectacion o clc)
        $this->conectar();
        $this->setSql('
            SET
            @bit_observacion ="SE MARCA LAS CLAVES PARA CARGA MASIVA",
            @usr_id = ?,
            @usr_ip = INET_ATON(?);
        ');
        $this->consultar([$this->usr_id, $this->usr_ip]);

        $this->setSql('
            UPDATE presupuesto 
            JOIN ' . BD_TEMPORAL . '.tem_Presupuestal USING(gpo_id,ur_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            SET pre_cargaMasiva = "1"
            WHERE tem_carga = ?
            AND pre_cargaMasiva = 0; 
        ');
        $this->consultar([$tem_carga]);

        $this->setSql(
            '
            UPDATE presupuestoVigente
            JOIN ' . BD_TEMPORAL . '.tem_Presupuestal USING(gpo_id,ur_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            SET pre_mov = "M"
            WHERE tem_carga = ?
            AND pre_mov = "";'
        );

        $this->consultar([$tem_carga]);

        $this->bitacora(5, 'SE MARCAN LAS CLAVES PRESUPUESTALES PARA CARGA MASIVA PRE_MOV = M ');
        $this->desconectar();

        // Valores para tc ( Tipo de Carga ) : 1 = sólo Afectaciones    2 = CLC Recursos Humanos      3 = CLC Consolidada
        // Inicializamos a vacías las consultas

        $sql0 = '';
        $sql1 = '';
        $sql2 = '';
        $sql3 = '';
        $sql4 = '';
        $sql5 = '';
        $sql6 = '';
        $sql7 = '';
        $sql8 = '';
        $sql9 = '';
        $sql10 = '';
        $sql11 = '';
        $sql12 = '';
        $sql13 = '';
        $sql14 = '';
        $sql18 = '';
        // En carga clc RH y afectaciones, limitamos a que NO sean de Pagos a Terceros (C)
        // Pero en afectaciones limitamos a que NO sean de Pagos a Terceros (C) y además NO de Gastos de Operación (B)
        // PENDIENTE
        // MARCOS: PREGUNTA ¿ porqué si es consolidada los Gastos de Operación (B) sí incluyen los campos de tno_id, mno_id smn_id ?
        if ($this->tipoCarga == '3') {
            $filtraTipoCLC = 'tcl_id NOT IN("B","C")';
        } else {
            $filtraTipoCLC = 'tcl_id NOT IN("C")';
        }

        $meses = [
            '(v.vig_ene + v.vig_feb + v.vig_mar)',
            '(v.vig_abr + v.vig_may + v.vig_jun)',
            '(v.vig_jul + v.vig_ago + v.vig_sep)',
            '(v.vig_oct + v.vig_nov + v.vig_dic)',
        ];
        $meses2 = [
            '(d.dev_ene + d.dev_feb + d.dev_mar)',
            '(d.dev_abr + d.dev_may + d.dev_jun)',
            '(d.dev_jul + d.dev_ago + d.dev_sep)',
            '(d.dev_oct + d.dev_nov + d.dev_dic)',
        ];
        $trimestre = (int) ceil(date('n') / 3) - 1;
        $trimestreVigente = $meses[$trimestre];
        $trimestreComprometido = $meses2[$trimestre];
        $meses = ['enero-febrero-marzo', 'abril-mayo-junio', 'julio-agosto-septiembre', 'octubre-noviembre-diciembre'];
        #se cambio la consulta que valida el documento ya que el documento puede que ya exista pero se valida que no sobrepase el monto de este mismo
        $sql0 = '
            #VARIABLE sql0
            SELECT 
                DISTINCT "Se excede del monto del documento" AS mensaje,
                CONCAT(rfc_id," - ",clc_referencia ) AS dato 
            FROM (
                SELECT rfc_id,clc_referencia, 
                    SUM(ncp_percepcionNeto) AS ncp_percepcionNeto, 
                    IF(isnull(percepcionNeto)
                        ,bdo_percepcionPresupuestal
                        , disponibleDoc) AS disponibleDoc
                FROM ' . BD_TEMPORAL . '.tem_Presupuestal
                    INNER JOIN(
                        SELECT 
                            b.rfc_id ,b.bdo_referencia AS clc_referencia , 
                            sum(bdo_percepcionPresupuestal) AS bdo_percepcionPresupuestal, 
                            sum(p.ncp_percepcionNeto) AS percepcionNeto, 
                            sum(bdo_percepcionPresupuestal) - sum(p.ncp_percepcionNeto) AS disponibleDoc
                        FROM cat_BancoDocumentos b 
                            LEFT JOIN (
                                SELECT 
                                    gpo_id,ur_id,
                                    rfc_id,ncp_referencia AS bdo_referencia,
                                    SUM(ncp_percepcionNeto) AS ncp_percepcionNeto
                                FROM scp_Afectacion a
                                    INNER JOIN scp_Presupuestal p USING (gpo_id,ur_id,afe_id)
                                WHERE
                                    eaf_id != "C"
                                GROUP BY rfc_id,ncp_referencia,gpo_id, ur_id
                            )p USING(rfc_id,bdo_referencia)
                        GROUP BY b.rfc_id,b.bdo_referencia, b.gpo_id, b.ur_id
                    )a USING(rfc_id,clc_referencia)
                WHERE 
                    tem_carga = "' . $tem_carga . '" 
                    AND tcl_id !="A"
                GROUP BY rfc_id,clc_referencia,percepcionNeto,bdo_percepcionPresupuestal,disponibleDoc
                HAVING disponibleDoc < ncp_percepcionNeto
            ) doc
            UNION ALL 
            SELECT 
                DISTINCT "Se excede del monto del documento" AS mensaje,
                CONCAT(rfc_id," - ",clc_referencia ) AS dato 
            FROM (SELECT 
                    gpo_id,ur_id,rfc_id,clc_referencia, 
                    SUM(ncp_percepcionNeto) AS ncp_percepcionNeto, 
                    IF( isnull(percepcionNeto)
                        , bdo_percepcionPresupuestal
                        , disponibleDoc ) AS disponibleDoc
                FROM ' . BD_TEMPORAL . '.tem_Presupuestal
                INNER JOIN (SELECT gpo_id,ur_id,b.rfc_id,
                            b.bdo_referencia AS clc_referencia, 
                            sum(bdo_percepcionPresupuestal) AS bdo_percepcionPresupuestal,
                            sum(p.ncp_percepcionNeto) AS percepcionNeto, 
                            sum(bdo_percepcionPresupuestal) - sum(p.ncp_percepcionNeto) AS disponibleDoc
                    FROM cat_BancoDocumentos b 
                        LEFT JOIN (SELECT
                                gpo_id,ur_id,rfc_id,ncp_referencia AS bdo_referencia,
                                SUM(ncp_percepcionNeto) AS ncp_percepcionNeto
                            FROM scp_Afectacion a
                                INNER JOIN scp_Presupuestal p USING (gpo_id,ur_id,afe_id)
                            WHERE 
                                eaf_id != "C"
                            GROUP BY rfc_id,ncp_referencia,gpo_id,ur_id
                        )p USING (gpo_id,ur_id,rfc_id,bdo_referencia)
                    GROUP BY b.rfc_id,b.bdo_referencia,gpo_id,ur_id
                )a USING (gpo_id,ur_id,rfc_id,clc_referencia)
                WHERE 
                    tem_carga = "' . $tem_carga . '" 
                    AND tcl_id ="A"
                GROUP BY rfc_id,clc_referencia,gpo_id,ur_id
                HAVING disponibleDoc < ncp_percepcionNeto
            ) doc1
            ';

        // En COMPROMISOS no usamos ésta parte
        if ($this->tipoCarga <> '3' && $this->tipoCarga <> '4') {
            // Revisamos que la clave presupuestal no este en un movimiento de calendarización o rectificación no autorizado,
            // o que intenten costear más de lo que tienen vigente en el mes
            $sql1 = '
                #VARIABLE sql1
                UNION ALL 
                SELECT 
                    CASE 
                        WHEN pre_mov = "C" 
                            THEN CONCAT("La clave presupuestal está en una calendarización en proceso") 
                        WHEN pre_mov = "R" 
                            THEN CONCAT("La clave presupuestal está en una reducción en proceso") 
                        WHEN (' . $trimestreVigente . ' < (' . $trimestreComprometido . ' + t.ncp_percepcionNeto))
                            THEN CONCAT("La clave presupuestal tiene un saldo en el trimestre de ' . $meses[$trimestre] . ' de ", FORMAT((' . $trimestreVigente . ' - ' . $trimestreComprometido . '), 2), " y la suma de las afectaciones es ",  FORMAT(t.ncp_percepcionNeto, 2)) 
                        END AS mensaje,
                    t.partida AS dato
                    FROM (SELECT 
                            gpo_id, ur_id, ue_id,
                            epg_id, pg_id, pe_id,
                            ecf_id, tcp_id,
                            SUM(ncp_percepcionNeto) AS ncp_percepcionNeto,
                            CONCAT(gpo_id, ur_id, ue_id, "-", prg_id, spr_id, py_id, oa_id, "-", pg_id, "-", pe_id, "-", cff_id,fte_id, sft_id, ori_id, sor_id,cfi_id, cfi_anio) AS partida
                        FROM ' . BD_TEMPORAL . '.tem_Presupuestal
                        WHERE 
                            tem_carga = "' . $tem_carga . '" 
                        GROUP BY gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, prg_id, spr_id, py_id, oa_id, cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio, tcp_id 
                    ) AS t
                        INNER JOIN presupuestoVigente v USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        INNER JOIN presupuestoDevengado d USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    WHERE 
                        (v.pre_mov IN ("C", "R") OR 
                        (' . $trimestreVigente . ' < ( ' . $trimestreComprometido . ' + t.ncp_percepcionNeto)))
                    ';

            // VALIDANDO EXISTENCIA DE LA CUENTA BANCARIA ADEMAS DE HAYA SIDO CREADO POR LA UR Y QUE ESTE HABILITADO
            // Esto se valida cuando se cargan CLCs, y por lo tanto NO LO HACEMOS cuando sólo carguen afectaciones
            if ($this->tipoCarga != '1') {
                // Cuando se trate de CLC de RH validamos que el uso de la cuenta bancaria NO sea 3 - genérica
                // Para CLCs Consolidadas valida que NO sea 3 - consolidada, NI TAMPOCO 4 - Pago Directo
                if ($this->tipoCarga == '2') {
                    // CLC Recursos Humanos
                    $usoCuentaBancaria = 'ucb_id!=3';
                } else {
                    // CLC Consolidada
                    $usoCuentaBancaria = 'ucb_id not IN(3,4)';
                }

                $sql2 = '
                    #VARIABLE sql2
                    UNION ALL 
                    SELECT 
                        DISTINCT "La cuenta bancaria no existe, no está habilitada, o no es válida para éste tipo de CLC" AS mensaje,
                        CONCAT(clc.gpo_id,clc.ur_id," - ", clc.bur_id, " tipo clc de la cuenta: ",ebn.tcl_id ) AS dato
                    FROM ' . BD_TEMPORAL . '.tem_Clc clc
                        LEFT JOIN cat_BancosUr bur USING (bur_id)
                        LEFT JOIN( 
                            SELECT 
                                bur_id,
                                GROUP_CONCAT(tcl_id) AS tcl_id 
                            FROM vin_BancosUrTipoClc 
                            GROUP BY bur_id
                        )ebn USING (bur_id)
                    WHERE 
                        bur_id != 0 
                        AND (FIND_IN_SET(clc.tcl_id,ebn.tcl_id) = 0 
                            OR ISNULL(eba_id) 
                            OR eba_id != 6 
                            OR (' . $usoCuentaBancaria . ' AND (bur.gpo_id != clc.gpo_id OR bur.ur_id != clc.ur_id) )) 
                        AND  tem_carga = "' . $tem_carga . '"';
            }
        }

        if ($this->tipoCarga == '4') {
            $tabla = BD_TEMPORAL . '.tem_Presupuestal';
        } else {
            $tabla = BD_TEMPORAL . '.tem_Clc';
        }

        /*VALIDANDO que la clave de financiamiento este presupuestado*/
        $sql3 = '
            #VARIABLE sql3
            #xxxxxxxxxxx Validando la clave de financiaiento xxxxxxx
            UNION ALL 
                SELECT 
                    DISTINCT "la Clave de Financiamiento no está presupuestada" AS mensaje,  
                    CONCAT( t.gpo_id, t.ur_id, "-", t.cff_id, t.fte_id, t.sft_id, t.ori_id, t.sor_id, t.cfi_id, t.cfi_anio ) AS dato
                FROM ' . $tabla . ' t
                    LEFT JOIN presupuesto p USING (gpo_id, ur_id , ecf_id)
                WHERE 
                    tem_carga = "' . $tem_carga . '" 
                    AND ISNULL(pre_claveCompleta)
            #xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

        /*MODALIDAD POR EL SUBTIPO*/
        $sql4 = '
            #VARIABLE sql4
            #xxxxxxxxxx Validando la modalidad por subtipo de CLC
            UNION ALL 
                SELECT 
                    DISTINCT "La Modalidad - Subtipo de CLC no es válida" AS mensaje,
                    mno_id AS dato
                FROM ' . $tabla . ' t
                    LEFT JOIN enc_ModalidadSubtipoClc m USING (tcl_id,stc_id,mno_id)
                WHERE 
                    tem_carga = "' . $tem_carga . '" 
                    AND ISNULL(m.mno_id)
                    AND ' . $filtraTipoCLC . '
            #xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

        $dosEjerciciosAtras = (int) substr($_SESSION[_S]['ejercicio'], 0, 4);
        $cuatroEjerciciosAtras = (string) $dosEjerciciosAtras - 4;
        $dosEjerciciosAtras = (string) $dosEjerciciosAtras - 2;

        /* Verificamos que la fecha del número de control presupuesta corresponda al ejercicio actual o a 2 años anteriores */
        $sql5 = '
            #VARIABLE sql5
            #xxx Verifica la fecha del número de control presupuesto corresponda al ejercicio actual o 2 años anteriores xxxx
            UNION ALL 
                SELECT 
                    "Al menos una fecha en las afectaciones Presupuestales corresponde a un ejercicio distinto al actual" AS mensaje,
                    YEAR( ncp_fechaReferencia ) AS dato 
                FROM ' . BD_TEMPORAL . '.tem_Presupuestal 
                WHERE tem_carga = "' . $tem_carga . '" 
                    AND NOT (YEAR( ncp_fechaReferencia) BETWEEN ' . $dosEjerciciosAtras . ' AND ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . '  
                    AND (gpo_id,ur_id) != (4,45))
                    AND NOT (YEAR( ncp_fechaReferencia ) BETWEEN ' . $cuatroEjerciciosAtras . ' AND ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' 
                    AND (gpo_id,ur_id) = (4,45))
                GROUP BY YEAR(ncp_fechaReferencia)
            #xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

        // VALIDAMOS QUE LA PARTIDA COMPLETA EXISTA EN LA TABLA PRESUPUESTO: USAMOS LOS SIGUIENTES CAMPOS
        // gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id
        $sql5 .= '
            #VARIABLE sql5 parte 2
            #xxxxxxx Se valida la partida presupuestal exista en la tabla de presupuesto xxxxx
            UNION ALL 
            SELECT 
                DISTINCT "La partida presupuestal no existe en el presupuesto" AS mensaje, 
                CONCAT( t.gpo_id, t.ur_id, t.ue_id, ".", t.prg_id, t.spr_id, t.py_id, t.oa_id, ".",'
            . ' t.pg_id, ".",t.pe_id, ".",t.cff_id, t.fte_id, t.sft_id, t.ori_id,'
            . ' t.sor_id, t.cfi_id, t.cfi_anio, ".", t.tcp_id ) AS dato
            FROM ' . BD_TEMPORAL . '.tem_Presupuestal t
                LEFT JOIN presupuesto p USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
            WHERE
                tem_carga = "' . $tem_carga . '" 
                AND pre_clave IS NULL
            #xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

        if ($this->tipoCarga != '4') {
            /* Verificamos que todas las cuentas contables usadas en los números de control contable existan en la contabilidad */
            $sql6 = '
                #VARIABLE sql6
                #xxxxxx VERIFICAR todas las cuentas contables usadas en los numeros de control contable xxxxx
                UNION ALL 
                SELECT 
                    "La cuenta contable no existe en la contabilidad" AS mensaje, 
                    CONCAT( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, ".", '
                . ' cta_gpo_id, cta_ur_id, ".", cta_ue_id, ".", cgc_id, ".", cta_id ) AS dato
                    FROM ' . BD_TEMPORAL . '.tem_Contable t
                        LEFT JOIN cg_PlanCuentas p USING (ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id)
                    WHERE 
                        t.tem_carga = "' . $tem_carga . '" 
                        AND p.cta_cuenta IS NULL
                #xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
        }

        if ($this->tipoCarga == '4') {
            $tabla = BD_TEMPORAL . '.tem_Presupuestal';
        } else {
            $tabla = BD_TEMPORAL . '.tem_Afectacion';
        }

        /* Verificamos que sea un subtipo de CLC de acuerdo a la UR */
        $sql7 = '
            #VARIABLE sql7
            #xxxxxxx Verificar el subtipo de CLC de acuerdo a la UR xxxxxxx
            UNION ALL 
                SELECT 
                    DISTINCT CONCAT("Modalidad de nómina no válida para tipo de CLC ", a.tcl_id,", '
            . 'subtipo(concepto de pago) ",a.stc_id) AS mensaje, a.mno_id AS dato
                FROM ' . $tabla . ' a
                    LEFT JOIN enc_ModalidadSubtipoClc m USING (tcl_id,stc_id,mno_id)
                WHERE 
                    isnull(m.mno_id) 
                    AND tcl_id = "A" 
                    AND tem_carga = "' . $tem_carga . '"
            #xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

        /* Verificamos que exista el RFC del proveedor, tanto en las afetaciones presupuestales como las contables */
        $sql8 = ' 
            #VARIABLE sql8
            #xxxxx Se verifica que exista el rfc del proveedor xxxxx
            UNION ALL 
            SELECT 
                DISTINCT "El rfc del proveedor no está registrado en el sistema" AS mensaje, 
                rfc_id AS dato 
            FROM ' . BD_TEMPORAL . '.tem_Presupuestal
                LEFT JOIN cat_Proveedor p USING (rfc_id) 
            WHERE 
                ISNULL( p.rfc_id ) 
                AND tem_carga = "' . $tem_carga . '"
            UNION ALL
            SELECT 
                DISTINCT "El rfc del proveedor no está registrado en el sistema" AS mensaje,
                rfc_id AS dato 
            FROM ' . BD_TEMPORAL . '.tem_Contable 
                LEFT JOIN cat_Proveedor p USING (rfc_id) 
            WHERE ISNULL( p.rfc_id ) 
                AND tem_carga = "' . $tem_carga . '"
            #xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

        // PENDIENTE:
        // Preguntar por qué en la consolidada también se verifica el disponible anual
        // y en la de RH y Afectaciones no se hace
        /* Verificar la cobertura que tienen en el presupuesto */
        if ($this->tipoCarga != '4') {
            if ($this->tipoCarga == '3') {
                // Verificación específica para la Carga de CLCs Consolidadas
                // Que no sobrepase el disponible del mes, PERO TAMPOCO el anual
                $sql8 .= ' 
                    #VARIABLE sql8 parte 2
                    #xxxxx Verificacion especiica para la carga de CLCs consolidadas xxxxx
                    UNION ALL 
                    SELECT DISTINCT 
                        CONCAT( "Disponible en ' . $meses[$trimestre] . ' de ", 
                            FORMAT((' . $trimestreVigente . ') - (' . $trimestreComprometido . ') , 2 ), 
                            ", Disponible ANUAL de ", 
                            FORMAT((p.pre_vigente - p.pre_comprometido), 2),
                            " y la suma de las afectaciones es ", 
                            FORMAT(t.ncp_percepcionNeto, 2)
                            ) AS mensaje,
                        p.pre_claveCompleta AS dato
                    FROM presupuestoVigente v
                        INNER JOIN presupuestoDevengado  d USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id)
                        INNER JOIN ' . BD_TEMPORAL . '.tem_Presupuestal t USING (gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id)
                    WHERE tem_carga = "' . $tem_carga . '"
                    GROUP BY gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id
                    HAVING disponible < importe OR disponibleAnual < importe
                    #xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
            } else {
                // Verificación para CLC de RH y Afectaciones
                // Que no sobrepase el disponible del mes
                $sql8 .= '
                    #VARIABLE sql8 parte 2
                    #xxxxxxx Verificacion CLC de RH y Afectaciones xxxx
                    UNION ALL 
                    SELECT DISTINCT  
                        CONCAT("Disponible a comprometer es de ", 
                            FORMAT((pre_vigente - pre_comprometido), 2), 
                            " y la suma de las afectaciones es ",  
                            FORMAT(t.ncp_percepcionNeto, 2)) AS mensaje, 
                        p.pre_claveCompleta AS dato
                    FROM presupuestoVigente
                        INNER JOIN presupuestoComprometido USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        INNER JOIN presupuesto p USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        INNER JOIN ' . BD_TEMPORAL . '.tem_Presupuestal t USING (gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id)
                    WHERE 
                        tem_carga = "' . $tem_carga . '" 
                        AND (pre_vigente - pre_comprometido) < t.ncp_percepcionNeto 
                    GROUP BY gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id,ncp_percepcionNeto 
                    #xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
            }
        }

        if ($this->tipoCarga != '3' && $this->tipoCarga != '4') {
            /*verificar saldos de amortizaciones*/
            $sql9 = '
                #VARIABLE sql9
                #xxxx Verificar saldos de amortizciones xxx
                UNION ALL 
                SELECT DISTINCT 
                    CONCAT( ncc_id, " excedido en amortizacion" ) AS mensaje, 
                    IF(ncc_percepcion = 0 ,
                        ncc_retencion - totalP,
                        ncc_percepcion - totalR) AS dato
                    FROM scp_Contable
                        INNER JOIN(SELECT 
                                gpo_id,ur_id,ncc_nccamortiza ncc_id,
                                SUM(ncc_percepcion) AS amortizadoP,
                                percepcion,
                                SUM(ncc_percepcion) + percepcion AS totalP,
                                SUM(ncc_retencion) AS amortizadoR,
                                retencion,
                                retencion + SUM(ncc_retencion) AS totalR
                            FROM scp_Contable
                                INNER JOIN scp_Afectacion USING (gpo_id,ur_id,afe_id)
                                INNER JOIN(
                                    SELECT DISTINCT 
                                        ncc_amortiza AS ncc_nccamortiza,
                                        SUM(ncc_percepcion) AS percepcion,
                                        SUM(ncc_retencion) AS retencion
                                    FROM ' . BD_TEMPORAL . '.tem_Contable
                                    WHERE 
                                        ncc_amortiza != 0 
                                        AND tem_carga = "' . $tem_carga . '"
                                    GROUP BY ncc_amortiza
                                ) amortizadoras USING (ncc_nccamortiza)
                            WHERE
                                eaf_id != "C"
                            GROUP BY ncc_nccamortiza,gpo_id,ur_id 
                            )amortizando USING (ncc_id)
                    HAVING dato < 0
                #xxxxxxxxxxxxxxxxxxxxxxxxxx';
        }

        if ($this->tipoCarga != '4') {
            /* Verificar la cuenta contable */
            if ($this->tipoCarga == '3') {
                // La carga de CLCs consolidadas excluye más cuentas
                $cgCuentasExcluir = 'IN("1002","1073","1184","1307","1327")';
            } else {
                // La CLC de RH y Afectaciones excluye menos cuentas
                $cgCuentasExcluir = 'BETWEEN "1001" AND "1570"';
            }
            $sql10 = '
                #VARIABLE sql10
                #xxxxx VERFICAR LA CUENTA CONTABLE
                UNION ALL 
                SELECT  DISTINCT 
                    "La cuenta contable no pasa la validación en la cédula" AS mensaje, 
                    CONCAT(ger_id,
                        cgp_id,
                        rub_id,
                        cgcu_id,
                        cgsc_id,
                        cta_gpo_id,
                        cta_ur_id,
                        cta_ue_id,
                        cgc_id,
                        cta_id) AS dato
                FROM ' . BD_TEMPORAL . '.tem_Contable
                WHERE
                    tem_carga = "' . $tem_carga . '" 
                    AND ((gpo_id = 8 AND ur_id = 01 
                            AND tcl_id = "C" 
                            AND NOT (CONCAT(ger_id,
                                    cgp_id,
                                    rub_id,
                                    cgcu_id,
                                    cgsc_id,
                                    cta_gpo_id,
                                    cta_ur_id,
                                    cta_ue_id,
                                    cgc_id) = "21179315001092" 
                        AND cta_id BETWEEN "1001" AND "1570")) 
                        OR (gpo_id = 8 AND ur_id = 01 
                        AND ((stc_id = 2 AND ncc_tipoMovimiento = "-") 
                            OR stc_id != 2) 
                        AND tcl_id = "I" 
                        AND (NOT ((CONCAT(ger_id,
                                cgp_id,
                                rub_id,
                                cgcu_id,
                                cgsc_id,
                                cta_gpo_id,
                                cta_ur_id,
                                cta_ue_id,
                                cgc_id) IN ("11253315001001","11229315001010","12135315001002") 
                        AND cta_id BETWEEN "1001" AND "1570") 
                        OR (CONCAT(ger_id,
                                cgp_id,
                                rub_id,
                                cgcu_id,
                                cgsc_id,
                                cta_gpo_id,
                                cta_ur_id,
                                cta_ue_id,
                                cgc_id) = "11239315001011" 
                                AND cta_id ' . $cgCuentasExcluir . ') 
                        OR CONCAT(ger_id,
                            cgp_id,
                            rub_id,
                            cgcu_id,
                            cgsc_id,
                            cta_gpo_id,
                            cta_ur_id,
                            cta_ue_id,
                            cgc_id,
                            cta_id) IN ("112293150010010021184",
                                "211793150010951143",
                                "211793150010951385",
                                "112393150010112002",
                                "112293150010021184")
                                )
                            ) 
                        ) 
                        OR (gpo_id = 8 AND ur_id = 02 
                            AND (cta_gpo_id,cta_ur_id) NOT IN((3,15),(8,02))) 
                        OR (gpo_id = 8 AND ur_id = 02 
                            AND (cta_gpo_id,cta_ur_id) NOT IN((3,15),(8,02)))
                        OR (tcl_id = "B" AND ncc_tipoMovimiento = "+")
                        OR (tcl_id = "D" AND ncc_tipoMovimiento = "-")
                        OR (tcl_id = "E" AND ncc_tipoMovimiento = "-")
                        OR (tcl_id = "F" AND ncc_tipoMovimiento = "+")
                        OR (tcl_id = "G" AND ncc_tipoMovimiento = "+")
                        OR (tcl_id = "H" AND ncc_tipoMovimiento = "-")
                        OR (tcl_id = "L")
                        OR (tcl_id = "M" AND ncc_tipoMovimiento = "-")
                    ) 
                    #xxxxxxxxxxxxxxxxxxxxxxx';
            /*verificar el tipo y subtipo para las amortizaciones*/
            $sql11 = '
                #VARIABLE sql11
                #xxxxx VERIFICAR EL TIPO Y SUBTIPO PARA LAS AMORTIZACIONES xxxx
                UNION ALL 
                SELECT DISTINCT 
                    "Validación de tipo y subtipo para amortizaciones" AS mensaje, 
                    CONCAT( a.ncc_amortiza, " - ", b.ncc_amortiza ) AS dato
                FROM ' . BD_TEMPORAL . '.tem_Contable a
                    LEFT JOIN (
                        SELECT 
                            gpo_id,ur_id,tcl_id ,ncc_id AS ncc_amortiza,
                            ecl_id,afe.stc_id, clc.ecf_id
                        FROM scp_Contable
                            INNER JOIN scp_Afectacion afe USING (gpo_id,ur_id,afe_id,clc_id)
                            INNER JOIN scp_Clc clc USING (gpo_id,ur_id,clc_id,tcl_id)
                        WHERE 
                            tcl_id IN ("A","B","D","E","F","G")
                    ) b USING (ncc_amortiza)
                WHERE 
                    tem_carga = "' . $tem_carga . '" 
                    AND ((a.tcl_id = "C" AND b.tcl_id = "A" 
                        AND a.ncc_amortiza = 0 
                        OR ISNULL(a.ncc_amortiza)) 
                    OR (a.ncc_amortiza != 0 AND ISNULL(b.ncc_amortiza))
                    AND (
                            (
                                (a.tcl_id IN ("A","B") 
                                    AND ((b.tcl_id = "D" AND b.stc_id != 2) 
                                    OR (b.tcl_id = "E" AND b.stc_id NOT IN (1, 2))))
                            OR (a.tcl_id IN ("A") 
                                AND (b.tcl_id = "E" 
                                AND b.stc_id != 1))) 
                        OR (a.tcl_id = "C" AND b.tcl_id NOT IN("A","B","F","G")) 
                        OR (a.tcl_id = "G" AND (b.tcl_id != "D" OR b.stc_id != 1)) 
                        OR (a.tcl_id = "I" AND (b.tcl_id != "I" OR b.stc_id != 2)) 
                        OR ((CONCAT(a.gpo_id,a.ur_id) != "118") AND (a.gpo_id != b.gpo_id OR b.ur_id != a.ur_id))) 
                        OR (ecl_id NOT BETWEEN 3 AND 18) 
                        OR a.ecf_id != b.ecf_id )
                #xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
            // PENDIENTE: Preguntar porqué en la carga masiva no se revisa ésta parte
            if ($this->tipoCarga != '3') {
                /* Verifica que el numero de control a amortizar no este amortizando */
                $sql12 = ' 
                    UNION ALL 
                        SELECT DISTINCT "Número de control a amortizar ya está amortizado" AS mensaje, 
                        CONCAT( a.ncc_amortiza, " - ", b.ncc_nccamortiza ) AS dato
                        FROM ' . BD_TEMPORAL . '.tem_Contable a
                        JOIN (
                            SELECT ncc_id,ncc_nccamortiza
                            FROM scp_Contable
                        )b ON b.ncc_id = a.ncc_amortiza
                        WHERE a.tem_carga = "' . $tem_carga . '"
                        AND ncc_nccamortiza != 0 AND !ISNULL(ncc_nccamortiza)
                ';
            }
        }
        // En el caso de carga masiva consolidada, revisamos que:
        // 1. El disponible MAS el importe a calendarizar sea mayor o igual al importe de la afectación
        // 2. que el importe a calendarizar sea mayor que cero
        // 3. que solamente el disponible NO alcance para pagar la afectación
        // Si se cumplen éstas condiciones, quiere decir que es necesaria una calendarización, ya que el simple
        // importe disponible no alcanza, pero el disponible más el calendarizado sí.
        // Sólo para las partidas ESPECIFICAS 201, 205 y 207, que sean de Compras Consolidadas,
        // y que NO TENGAN modificaciones pendientes de autorizar
        if ($this->tipoCarga == '3') {
            $cadena = '';
            // Obtenemos el disponible (vigente - comprometido) de cada mes y lo sumamos
            // HASTA EL MES ANTERIOR al actual
            for ($i = 1; $i <= (date('n') - 1); $i++) {
                if ($cadena <> '') {
                    $cadena .= '+';
                }
                $cadena .= '(vig_' . substr($meses[$i], 0, 3) . '-' . 'com_' . substr($meses[$i], 0, 3) . ')';
            }
            // Luego le sumamos el disponible de diciembre (vigente - comprometido) y lo ponemos como importe disponible para calendarizar
            // Y también obtenemos el disponible del mes
            $cadena .= ' +(vig_dic-com_dic) as importeCalendarizar,(vig_' . $meses[date('n')] . '-' . 'com_' . $meses[date('n')] . ') as disponible';

            $sql13 = '
                UNION ALL 
                    SELECT "AVISO: Se calendarizará automáticamente" AS mensaje, 
                    CONCAT( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id ) AS dato 
                        FROM  (  
                            SELECT DISTINCT gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id,SUM(ncp_percepcionNeto) importe ,' . $cadena . '
                            FROM presupuesto
                            INNER JOIN ' . BD_TEMPORAL . '.tem_Presupuestal tp USING (gpo_id , ur_id , ue_id , epg_id , pg_id , pe_id , ecf_id , tcp_id)
                            LEFT JOIN(
                                /* Dame todas las modificaciones que NO estén autorizadas */
                                SELECT DISTINCT gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id
                                FROM scp_MaestroModificacion
                                INNER JOIN scp_DetalleModificacion d using(omo_id,mod_gpo_id,mod_ur_id,mod_id,ecf_id)
                                WHERE emo_id not in (12,13)
                            ) as d USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                            WHERE tem_carga = "' . $tem_carga . '" AND pe_id in (201,205,207) AND tcp_id="C" AND d.pe_id IS NULL
                            GROUP BY gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id
                            HAVING disponible + importeCalendarizar >= importe AND importeCalendarizar >0 AND importe > disponible
                        )calendario LIMIT 1
                ';

            $sql14 = '
                UNION ALL 
                /* Cuando sea CLC de Gtos Op, Compra consolidada, y NO sea pe_id 201, 205 y 207 */
                SELECT  DISTINCT "Partida Generica no válida para Gastos de Operación" AS mensaje, CONCAT( tcl_id, "-", stc_id ) AS dato
                FROM ' . BD_TEMPORAL . '.tem_Presupuestal
                WHERE tcl_id = "B" AND tcp_id = "C" AND pe_id NOT IN(201, 205, 207) AND tem_carga = "' . $tem_carga . '"
                UNION ALL 
                /* Cuando sea CLC de Serv Pers y el subtipo NO sea 1, 2 o 6 */
                SELECT  DISTINCT "subtipo CLC no válido para Servicios Personales" AS mensaje, CONCAT( tcl_id, "-", stc_id ) AS dato
                FROM ' . BD_TEMPORAL . '.tem_Presupuestal
                WHERE tcl_id = "A" AND stc_id NOT IN(1,2,6) AND tem_carga = "' . $tem_carga . '"
                UNION ALL
                /*verificar la existencia de la clave presupuestal y que no tenga Reduccion o Calendarizacion pendiente*/
                SELECT DISTINCT "Clave presupuestal inválida o en Calendarización o Rectificación" AS mensaje,  
                    CONCAT(gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id, "-", pre_mov ) AS dato
                FROM ' . BD_TEMPORAL . '.tem_Presupuestal
                LEFT JOIN presupuestoVigente USING (gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id)
                WHERE ( ISNULL(pre_claveCompleta) OR pre_mov IN ("C","R") ) AND tem_carga = "' . $tem_carga . '")';
        }            #se valida las cuentas que no son permitidas las perseciones para las clc de servicios personales
        $sql15 = '
            UNION ALL
            SELECT DISTINCT "Percepciones no disponible para cuenta contable en servicios personales,'
            . ' Para el pago de pension alimenticia use una Afectación de Pagos a Terceros" AS mensaje, 
            CONCAT( ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id," - ",cta_nombre) AS dato
            FROM ' . BD_TEMPORAL . '.tem_Contable 
            WHERE tem_carga = "' . $tem_carga . '" AND ger_id = 2 AND cgp_id = 1 
                AND rub_id = 1 AND cgcu_id = 7 AND cgsc_id = 9  
                AND cgc_id = 005 AND ncc_tipoMovimiento = "+" AND tcl_id = "A"';

        // Marcos: 17/mayo/2016
        // Se adiciona la regla de validación que verifique antes de cargar que
        // las clcs de gastos de operación NO pueden tener tcp_id vacio
        $sql15 = '
            UNION ALL
            SELECT DISTINCT "Las CLC de Gastos de Operación no pueden tener tcp vacío" AS mensaje, 
            CONCAT( tcp_id) AS dato
            FROM ' . BD_TEMPORAL . '.tem_Presupuestal
            WHERE tcl_id = "B" AND TRIM( tcp_id ) = "" AND tem_carga = "' . $tem_carga . '"';

        $sql16 = '
            UNION ALL
            SELECT DISTINCT "No puede realizar carga masiva de FONDO ROTATORIO" AS mensaje, 
            CONCAT( bur_id," - ",bur_alias) AS dato
            FROM ' . BD_TEMPORAL . '.tem_Clc
            JOIN cat_BancosUr USING(gpo_id,ur_id,bur_id)
            WHERE bur_fondoRotatorio = "S" AND tem_carga = "' . $tem_carga . '"';
        $sql17 = '
            UNION ALL
            SELECT 
                DISTINCT "No coinciden la cuenta contable de la amortizadora y la de origen" AS mensaje, 
                CONCAT(CONCAT("AMORTIZADORA - ", a.ger_id,a.cgp_id,a.rub_id,a.cgcu_id,a.cgsc_id,a.cta_gpo_id,a.cta_ur_id,a.cta_ue_id,a.cgc_id,a.cta_id ),
                CONCAT("ORIGEN - ",b.ger_id,b.cgp_id,b.rub_id,b.cgcu_id,b.cgsc_id,b.cta_gpo_id,b.cta_ur_id,b.cta_ue_id,b.cgc_id,b.cta_id) )dato
            FROM ' . BD_TEMPORAL . '.tem_Contable a
            LEFT JOIN (
                SELECT ncc_id ncc_amortiza,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id
                FROM scp_Contable
                JOIN scp_Afectacion afe USING(gpo_id,ur_id,afe_id,clc_id)
                JOIN scp_Clc clc USING(gpo_id,ur_id,clc_id,tcl_id)
                WHERE tcl_id IN("A","B","D","E","F","G")
            ) b USING( ncc_amortiza )
            WHERE 
                tem_carga = "' . $tem_carga . '" AND 
                a.tcl_id = "C" AND(
                    a.ger_id !=  b.ger_id OR 
                    a.cgp_id !=  b.cgp_id OR 
                    a.rub_id !=  b.rub_id OR 
                    a.cgcu_id !=  b.cgcu_id OR 
                    a.cgsc_id !=  b.cgsc_id OR 
                    a.cta_gpo_id !=  b.cta_gpo_id OR 
                    a.cta_ur_id !=  b.cta_ur_id OR 
                    a.cta_ue_id !=  b.cta_ue_id OR 
                    a.cgc_id !=  b.cgc_id OR 
                    a.cta_id !=  b.cta_id 
                )';

        /*VALIDANDO que las cuentas contables no es manejable por administracion 11/08/2022 @ggarciaz*/
        if ($this->tipoCarga == '2' || $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == '118') {
            $sql18 = '
                #VARIABLE sql18
                #xxxxxxxxxxx Validando que la cuenta contable no es manejable por administracion xxxxxxx
                UNION ALL
                    SELECT 
                        DISTINCT "La cuenta contable no es manejada por administracion" AS mensaje,  
                        CONCAT(t.cta_gpo_id, t.cta_ur_id, t.cta_ue_id, t.ger_id, t.cgp_id, t.rub_id, t.cgcu_id, t.cgsc_id, t.cgc_id, t.cta_id, t.cta_nombre ) AS dato
                    FROM ' . BD_TEMPORAL . '.tem_Contable t
                        LEFT JOIN cat_UrAdministracion as cua ON cua.gpo_id=t.cta_gpo_id AND cua.ur_id=t.cta_ur_id
                    WHERE tem_carga = "' . $tem_carga . '" 
                    AND cua.gpo_id IS NULL
                #xxxxxxxxxxxxxxxxxxxxse identaxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
        }

        $sql19 = '
            #VARIABLE sql18
            #xxxxxxxxxxx Validando que los numeros de control a amortizar existan en el ejercicio y pertenezcan a la UR xxxxxxx
            UNION ALL
            SELECT 
            CONCAT("No existe en el ejercicio actual o no pertenece a la UR") as mensaje,
            CONCAT( "El número de control ", ncc_id, " de la UR - ", gpo_id, ur_id) as dato
            FROM scp_Contable c
            INNER JOIN scp_Afectacion USING (gpo_id,ur_id,afe_id)
            RIGHT JOIN(
                    SELECT cta_gpo_id gpo_id, cta_ur_id ur_id, ncc_amortiza ncc_id
                    FROM ctrl2026Temporal.tem_Contable
                    WHERE ncc_amortiza != 0  AND tem_carga = "' . $tem_carga . '"
            ) amortizadoras USING (gpo_id, ur_id, ncc_id)
            WHERE ISNULL(c.gpo_id) AND ISNULL(c.ur_id) AND ISNULL(c.ncc_id)
        ';

        // Armamos la consulta (las separamos para claridad y comentarios solamente)
        $sql = $sql0 . $sql1 . $sql2 . $sql3 . $sql4 . $sql5 . $sql6 . $sql7 . $sql8 . $sql9
            . $sql11 . $sql12 . $sql13 . $sql14 . $sql15 . $sql16 . $sql17 . $sql18 . $sql19;

        // Validamos ...
        $this->bitacora(36, 'VALIDANDO ... ');
        $this->setSql($sql);
        $this->conectar();
        $this->consultar();
        $this->desconectar();

        $numErrores = $this->getNumeroRegistros();
        $rutalog = 'log/carga' . $tem_carga . '.log';

        /******************* LOG DE CONSULTAS************************************/
        //Se guarda la consulta, por cualquier detalle de la carga
        $file = fopen($rutalog, 'a');
        $tempdata = '';
        $hoy = date('Y-m-d-H-i-s');
        $tempdata .= '|IPADDRESS=' . $_SESSION[_S]['ses_ip'];
        $tempdata .= '|USRID=' . $_SESSION[_S]['usr_id'];

        fwrite($file, 'Consulta general que valida la carga masiva:' . $hoy . $tempdata . ':' . $sql . PHP_EOL);
        fclose($file);
        $peso = filesize($rutalog);
        $peso = ($peso / 1024) / 1024;
        if ($peso > 8) {
            $rutamodificada = 'log/Carga' . $tem_carga . $hoy . '.txt';
            rename($rutalog, $rutamodificada);
            $file = fopen($rutalog, 'a');
            fwrite($file, 'Creado:' . $hoy . PHP_EOL);
            fclose($file);
        }
        /******************* LOG DE CONSULTAS FALLIDAS************************************/
        // Si hubo errores ... los mostramos
        if ($numErrores > 0) {


            $this->bitacora(95, 'ERRORES EN LA VALIDACION se guarda la consulta en ' . $rutalog);

            while ($row = $this->extraer()) {

                $this->mixScript('$("#listaCarga").append(\'<li class="list-group-item datoCarga"><label class="alert alert-danger">ERROR: ' . $row['dato'] . ' - ' . json_encode($row['mensaje']) . '   </label></li>\');');
            }
            $this->mixScript('
                $("#btnRevisarArchivo").html("Revisar");
            ');

            #si hay alguna inconsistencia desbloqueamos las claves presupestales
            $this->bitacora(5, 'LIBERAMOS LAS CLAVES PRESUPUESTALES ');
            $this->conectar();
            $this->setSql('
                SET 
                @bit_observacion ="SE MARCA LAS CLAVES PARA CARGA MASIVA",
                @usr_id = ?,
                @usr_ip = INET_ATON(?);
            ');
            $this->consultar([$this->usr_id, $this->usr_ip]);
            $this->setSql('
                UPDATE presupuestoVigente 
                JOIN ' . BD_TEMPORAL . '.tem_Presupuestal USING(gpo_id,ur_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                SET pre_mov = ""
                WHERE tem_carga = ?
                AND pre_mov ="M"; 
            ');
            $this->consultar([$tem_carga]);

            $this->setSql('
                UPDATE presupuesto
                JOIN ' . BD_TEMPORAL . '.tem_Presupuestal USING(gpo_id,ur_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                SET pre_cargaMasiva = 0
                WHERE tem_carga = ?
                AND pre_cargaMasiva = 1;
            ');
            $this->consultar([$tem_carga]);
            $this->desconectar();
            $this->actualizaCarga(70, 'REGRESAMOS A UN ESTADO ANTERIOR PARA CORREGIR LOS DATOS SI ES NECESARIO SI NO SE QUEDA AQUI');
            // Si hubo errores les avisamos ...
        } else {
            $this->actualizaCarga(6, 'FIN DE LA REVISION');
            $boton = new stdClass();
            $boton->id = 'btnCargarArchivo';
            // $boton->title='Buscar Archivo';
            $boton->value = ' Cargar';
            $boton->icon = 'upload';
            $boton->color = 'verde';
            $boton->click = '
                $(this).attr("disabled","disabled").css("background-color","#348fe2")
                .css("border-color","#858585")
                .append($("<img/>").attr("src","img/loader2.gif"));
                comunica("' . $this->pantalla . '",{
                    metodo:"cargarDatos",
                    afe_fr:$("#afe_fondoRotatorio").attr("value")
                });
            ';
            $this->setContenedor('botonesCarga');
            $this->mixHtml($this->ponBoton($boton));
            $this->mixScript('
                $("#listaCarga").append(\'<li class="list-group-item datoCarga"><label class="alert-success">Paso 3 completo:</label><b>REVISION TERMINADA</b> Puedes cargar la información al Presupuesto.</li>\');
                $("#botonesCarga").prepend(\'<button type="button" value=" copiar"class="btn btn-outline-secondary me-1 disabled" <span class="fa-exchange"></span> copiar</button><button type="button" value=" copiar"class="btn btn-outline-secondary me-1 disabled" <span class="glyphicon glyphicon-certificate"></span> preparar</button><button type="button" value=" copiar"class="btn btn-outline-secondary me-1 disabled" <span class="glyphicon glyphicon-check"></span> revisar</button>\');
            ');
        }
        $datetime2 = new DateTime('now');
        $interval = date_diff($datetime1, $datetime2);
        $tiempo = ($interval->format('%i minutos %s segundos'));
        $this->bitacora(6, 'La revision de datos tardo ' . $tiempo);
    }

    // Limpiamos cargas del temporal, de la misma ur y usuario
    private function limpiaCarga()
    {
        // Obtenemos el número de carga temporal asignado...
        $tem_carga = $_SESSION[_S]['tem_Carga'];
        $tipoCarga = $this->tipoCarga;

        // Borramos las cargas del grupo ... y usuario actual
        $tem1 = $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '-';
        $tem2 = $_SESSION[_S]['usr_id'];

        // Borramos una carga previa
        $conexion = new AccesoDatos();

        $conexion->setSql('
            DELETE FROM ' . BD_TEMPORAL . '.tem_Clc 
            WHERE tem_carga LIKE "' . $tem1 . '%" 
            AND SUBSTRING( tem_carga, 12, 3 ) = ?;
        ');
        $conexion->consultar([$tem2]);

        $conexion->setSql('
            DELETE FROM ' . BD_TEMPORAL . '.tem_Afectacion
            WHERE tem_carga LIKE "' . $tem1 . '%" 
            AND SUBSTRING( tem_carga, 12, 3 ) = ?;
        ');
        $conexion->consultar([$tem2]);

        $conexion->setSql('
            DELETE FROM ' . BD_TEMPORAL . '.tem_Presupuestal 
            WHERE tem_carga LIKE "' . $tem1 . '%" 
            AND SUBSTRING( tem_carga, 12, 3 ) = ?;
        ');
        $conexion->consultar([$tem2]);

        $conexion->setSql('
            DELETE FROM ' . BD_TEMPORAL . '.tem_Contable
            WHERE tem_carga LIKE "' . $tem1 . '%"
            AND SUBSTRING( tem_carga, 12, 3 ) = ?;
        ');
        $conexion->consultar([$tem2]);

        /*$conexion->setSql('DELETE FROM tem_Avance WHERE tem_carga = "' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . $_SESSION[_S]['usr_id'] . '";'); La tabla no existe, validar, de ser así eliminar esto*/
        //$conexion->consultar();

        // Cuando se trate de carga masiva de CLCs consolidadas, también ejecutamos ésto
        if ($tipoCarga == '3') {
            $conexion->setSql('
                UPDATE scp_Clc
                SET clc_consolidada = 0
                WHERE tcl_id = "B"
                AND tcp_id = "C"
                AND tem_carga = ?;
            ');
            $conexion->consultar([$tem_carga]);
        }

        $conexion->desconectar();
    }

    // Cargamos a las tablas definitivas la información ya validada
    private function cargar()
    {
        if ($this->getEstadoCarga($_SESSION[_S]['tem_carga']) != 6) {
            $this->mixScript('notify("Ya se inicio el copiado o la carga tiene errores","warning");');
            return false;
        }
        $datetime1 = new DateTime('now');

        $this->actualizaCarga(69, 'INICIA LA CARGA DE DATOS TIPO DE CARGA' . $this->tipoCarga);
        // Obtenemos el número de carga temporal asignado...

        if ($this->temCargaValido($_SESSION[_S]['tem_carga'])) {

            // Valores para tc ( Tipo de Carga ) : 1 = sólo Afectaciones    2 = CLC Recursos Humanos      3 = CLC Consolidada       4 = Compromisos

            if ($this->tipoCarga != '1' && $this->tipoCarga != '4') {

                $insertados = $this->crearCLCS($this->tipoCarga);
                $this->creaAfectaciones(3);
            } elseif ($this->tipoCarga == '1') {

                $this->bitacora(69, 'MANDAR A LLAMAR LA CREACION DE AFECTACIONES ');
                $insertados = $this->creaAfectaciones(2);
            } elseif ($this->tipoCarga == '4') {

                $insertados = $this->creaCompromisos();
            }

            $estado = $this->getEstadoCarga($_SESSION[_S]['tem_carga']);

            $procesa = false;
            switch ($estado) {
                case 66:
                case 70:
                    $procesa = true;
                    break;
                default:
                    $procesa = false;
                    break;
            }

            if (!$procesa) {
                $this->bitacora($estado, ' error en el proceso linea ' . __LINE__);
                $this->mixScript('notify("OCURRIO UN ERROR EN LA GENERACION DE LA INFORMACION LINEA 2983","error");');
                return false;
            }

            $this->conectar();
            // actualiza totales de la clc
            if ($this->tipoCarga != '1' && $this->tipoCarga != '4') {
                $this->setSql('
                    UPDATE scp_Clc
                    JOIN(
                        SELECT gpo_id,ur_id,clc_id,SUM(afe_percepcion) percepcion,SUM(afe_retencion) retencion
                        FROM scp_Afectacion
                        WHERE tem_carga = :tem_carga
                        GROUP BY gpo_id,ur_id,clc_id
                    )sumas USING(gpo_id,ur_id,clc_id)
                    SET clc_percepcion = percepcion,
                        clc_retencion = retencion
                    WHERE tem_carga = :tem_carga;
                ');
                $this->consultar([':tem_carga' => $_SESSION[_S]['tem_carga']]);
            }
            $this->bitacora(66, ' ACTUALIZA IMPORTES DE LA CLC');
            // PENDIENTE
            // Nota personal: Esta consulta viene para la carga de CLC de RH y Afectaciones, PERO NO VIENE en la carga masiva
            // consolidada.
            // Checar con Anuar
            // Una vez ya cargada la información en las tablas definitivas, buscamos todos los números de control
            // contable que traigan números de control a amortizar ( ncc_nccamortiza ).   Lo que hacemos aquí es
            // decirle al registro original que estamos amortizando, con qué CLC y de qué UR se amortizó el
            // importe.
            // Puede ser de otra UR solamente en el caso de la 318, ya que ella amortiza registros de otras URs

            if ($this->tipoCarga != '3' && $this->tipoCarga != '4') {
                $this->setSql('
                    UPDATE scp_Contable AS c
                    JOIN(
                        SELECT t.gpo_id AS gpo, t.ur_id AS ur, t.clc_id AS clc, t.ncc_id AS ncc_nccamortiza
                        FROM scp_Contable t
                        INNER JOIN (
                            SELECT ncc_nccamortiza AS ncc_id
                            FROM scp_Contable 
                            WHERE tem_carga = :tem_carga
                        ) AS e USING(ncc_id)
                    )atz USING(ncc_nccamortiza) 
                    SET c.ncc_gpo = atz.gpo,
                        c.ncc_ur = atz.ur,
                        c.ncc_clc = atz.clc
                    WHERE c.tem_carga = :tem_carga;
                ');

                $this->consultar([':tem_carga' => $_SESSION[_S]['tem_carga']]);
            }
            $this->bitacora(66, ' AUTORIZACION DE COMPROMISOS ');


            $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
            #se actualiza el estado ya que se generaron en estado "A" para evitar que las vea y modifique la dependencia
            $this->setSql('
                UPDATE scp_Compromiso 
                SET ecm_id = "G" 
                WHERE tem_carga = ?;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);

            #se consultan los compromisos generados para comprometer el presupuesto
            $this->setSql('
                SELECT gpo_id,ur_id,cmp_id
                FROM scp_Compromiso
                WHERE tem_carga = ?;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);

            $conn = new AccesoDatos();
            while ($r = $this->extraer()) {
                $conn->conectar();
                $conn->setSql('
                    SET
                    @usr_id = ?,
                    @usr_ip = INET_ATON(?),
                    @bit_observacion="AUTORIZACION DEL COMPROMISO CARGAMASIVA";
                ');
                $conn->consultar([$_SESSION[_S]['usr_id'], $ip]);
                $conn->setSql('
                    CALL sp_AutorizarCompromiso' . APP_EJERCICIO . '(?, ?, ?, INET_ATON(?), ?);
                ');
                $params = [
                    $r['gpo_id'],
                    $r['ur_id'],
                    $r['cmp_id'],
                    $ip,
                    $_SESSION[_S]['usr_id'],
                ];
                $conn->consultar($params);
                $conn->setSql('
                    SELECT @banderaPresupuesto AS ban, @mensajePresupuesto AS mensaje;
                ');
                $conn->consultar();

                $res = $conn->extraer();
                if ($res['ban'] != '1') {
                    $conn->setSql('
                        UPDATE scp_Compromiso
                        SET ecm_id = "G" 
                        WHERE gpo_id = ?
                        AND ur_id = ?
                        AND cmp_id = ?
                    ');
                    $conn->consultar([$r['gpo_id'], $r['ur_id'], $r['cmp_id']]);
                }
                $conn->desconectar();
            }
            $this->bitacora(66, ' SE AUTORIZAN TODOS LOS COMPROMISOS ');
            $this->bitacora(66, ' SE AUTORIZAN TODAS LAS AFECTACIONES ');
            #se actualiza el estado ya que se generaron en estado "A" para evitar que las vea y modifique la dependencia
            $this->setSql('
                UPDATE scp_Afectacion
                SET eaf_id = "G" 
                WHERE tem_carga = ?;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);
            #se consultan los compromisos generados para comprometer el presupuesto
            $this->setSql('
                SELECT gpo_id,ur_id,afe_id
                FROM scp_Afectacion
                WHERE tem_carga = ?; 
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);

            while ($r = $this->extraer()) {
                $conn->conectar();
                $conn->setSql('
                    SET @usr_id = ?,
                    @usr_ip = INET_ATON(?),
                    @bit_observacion="AUTORIZACION DE LA AFECTACION CARGAMASIVA";
                ');
                $conn->consultar([$_SESSION[_S]['usr_id'], $ip]);

                $conn->setSql('
                    CALL sp_AutorizarAfectacion' . APP_EJERCICIO . '(?, ?, ?, INET_ATON(?), ?);
                ');
                $params = [
                    $r['gpo_id'],
                    $r['ur_id'],
                    $r['afe_id'],
                    $ip,
                    $_SESSION[_S]['usr_id'],
                ];
                $conn->consultar($params);
                $conn->setSql('
                    SELECT @banderaPresupuesto AS ban, @mensajePresupuesto AS mensaje,@usr_id AS usr,@usr_ip AS ip,@bit_observacion AS obs;
                ');
                $conn->consultar();
                $res = $conn->extraer();
                if ($res['ban'] != 1) {
                    $conn->setSql('
                        UPDATE scp_Afectacion
                        SET eaf_id = "G"
                        WHERE gpo_id = ?
                        AND ur_id = ?
                        AND afe_id = ?
                    ');
                    $params = [
                        $r['gpo_id'],
                        $r['ur_id'],
                        $r['afe_id'],
                    ];
                    $conn->consultar($params);
                }
                // $conn->desconectar();
                /******************* LOG DE CONSULTAS FALLIDAS************************************/
                $tem_carga = '';
                $rutalog = 'log/carga/AutorizaCarga' . $tem_carga . '.log';
                $file = fopen($rutalog, 'a');
                $tempdata = '';
                $hoy = date('Y-m-d-H-i-s');
                $tempdata .= '|IPADDRESS=' . $_SESSION[_S]['ses_ip'];
                $tempdata .= '|USRID=' . $_SESSION[_S]['usr_id'];
                $tempdata = '|Bandera:' . $res['ban'] . '@@Mensaje:' . $res['mensaje'] . '@@Usuario:' . $res['usr'] . '@@IP:' . $res['ip'] . '@@Observacion:' . $res['obs'];
                fwrite($file, 'Autorizaciones de la Afectacion:' . $hoy . $tempdata . ':' . PHP_EOL);
                fclose($file);
                $peso = filesize($rutalog);
                $peso = ($peso / 1024) / 1024;
                if ($peso > 8) {
                    $rutamodificada = 'log/carga/AutorizaCarga' . $tem_carga . $hoy . '.txt';
                    rename($rutalog, $rutamodificada);
                    $file = fopen($rutalog, 'a');
                    fwrite($file, 'Creado:' . $hoy . PHP_EOL);
                    fclose($file);
                }
                /******************* LOG DE CONSULTAS FALLIDAS************************************/

                $conn->desconectar();
            }
            $this->bitacora(66, ' FINALIZA LA AUTORIZACION DE AFECTACIONES ');
            $this->setSql('
                UPDATE scp_Compromiso
                SET ecm_id = "D"
                WHERE tem_carga = ?;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);

            if ($this->tipoCarga == 2 && isset($_SESSION[_S]['prm']['S']['9']['1']['23'])) {
                #se actualiza el estado ya que se generaron en estado 3 para evitar que las vea y modifique la dependencia
                $this->setSql(' SET @desactivaClc  = 1;');
                $this->consultar();
                $this->setSql('
                    UPDATE scp_Clc
                    SET ecl_id = 2
                    WHERE tem_carga = ?;
                ');
                $this->consultar([$_SESSION[_S]['tem_carga']]);
                #se consultan los compromisos generados para comprometer el presupuesto
                $this->setSql('
                    SELECT gpo_id,ur_id,clc_id
                    FROM scp_Clc
                    WHERE tem_carga = ?;
                ');
                $this->consultar([$_SESSION[_S]['tem_carga']]);
                while ($r = $this->extraer()) {
                    $conn->conectar();
                    $conn->setSql('
                        SET
                        @usr_id = ?,
                        @usr_ip = INET_ATON(?),
                        @bit_observacion = "AUTORIZACION DE LA CLC CARGAMASIVA";
                    ');
                    $conn->consultar([$_SESSION[_S]['usr_id'], $ip]);
                    $conn->setSql('
                        CALL sp_AutorizarCLC' . APP_EJERCICIO . '(?, ?, ?, INET_ATON(?), ?);
                    ');
                    $params = [
                        $r['gpo_id'],
                        $r['ur_id'],
                        $r['clc_id'],
                        $ip,
                        $_SESSION[_S]['usr_id'],
                    ];
                    $conn->consultar($params);
                    $conn->desconectar();
                }
                $this->bitacora(66, ' FINALIZA LA AUTORIZACION DE CLC ');
            }
            $this->setSql('
                SET 
                @bit_observacion = "SE MARCA LAS CLAVES PARA CARGA MASIVA",
                @usr_id = ?,
                @usr_ip = INET_ATON(?);
            ');
            $this->consultar([$this->usr_id, $this->usr_ip]);
            $this->setSql('
                UPDATE presupuesto 
                JOIN (
                    SELECT DISTINCT gpo_id, ur_id, epg_id, pg_id, pe_id, ecf_id, tcp_id
                    FROM presupuesto
                    JOIN ' . BD_TEMPORAL . '.tem_Presupuestal USING (gpo_id,ur_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    WHERE tem_carga = ?
                ) usadas USING (gpo_id,ur_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                SET pre_cargaMasiva = 0
                WHERE pre_cargaMasiva = 1;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);

            $this->setSql('
                UPDATE presupuestoVigente
                JOIN (
                    SELECT DISTINCT gpo_id, ur_id, epg_id, pg_id, pe_id, ecf_id, tcp_id 
                    FROM presupuestoVigente 
                    JOIN ' . BD_TEMPORAL . '.tem_Presupuestal USING (gpo_id,ur_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    WHERE tem_carga = ?
                ) usadas USING (gpo_id,ur_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                SET pre_mov = ""
                WHERE pre_mov = "M";
            ');

            $this->consultar([$_SESSION[_S]['tem_carga']]);
            $this->bitacora(66, 'SE LIBERAN LAS CLAVES PRESUPUESTALES');
            $this->desconectar();
            $this->actualizaCarga(100, 'CARGA EXITOSA');
            $this->mixScript('
                alertify.confirm("CARGA MASIVA CONCLUIDA", "Se cargaron " + ' . number_format($insertados) . ' + " ' . (($this->tipoCarga != '1' && $this->tipoCarga != '4') ? 'CLCs' : (($this->tipoCarga != '4') ? 'Afectaciones' : 'Compromisos')) . ' por Carga Masiva", function(){}).set("labels", {ok:"Aceptar"});

                $("#btnCargarArchivo").html("Cargar").removeAttr("id");
                comunica("scp/admon/ControladorCargaMasiva.php",{metodo:"resCarga"
                });
            ');

            if ($this->tipoCarga != '1' && $this->tipoCarga != '4') {
                $this->mixScript('
                    var form = $("<form>")
                        .attr("action", "rep/doc/rep_clc.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "tem_carga").attr("value", "' . $_SESSION[_S]['tem_carga'] . '" ));
                    $("body").append(form);
                        form.submit();
                    comunica("scp/admon/ControladorCargaMasiva.php",{metodo:"resCarga"});
                ');
            }

            $this->mixScript('
                comunica("scp/admon/ControladorCargaMasiva.php",{
                    metodo:"resCarga"
                });
            ');
        }
        $datetime2 = new DateTime('now');
        $interval = date_diff($datetime1, $datetime2);
        $tiempo = ($interval->format('%i minutos %s segundos'));

        $this->bitacora(66, 'la carga de datos  tardo ' . $tiempo);
    }

    // Cargamos en tablas temporales el contenido de la pestaña CLC del archivo Excel */
    private function cargaClcs($clcs, $tipoCarga)
    {
        if ($this->getEstadoCarga($_SESSION[_S]['tem_carga']) != 61) {
            $this->mixScript('notify("Ya se inicio la carga de CLCS","warning");');
            return false;
        }
        $datetime1 = new DateTime('now');

        $this->actualizaCarga(17, 'INICIA LA CARGA DE CLC DE RECURSOS HUMANOS');

        $maxRegistrosPorInsert = 500;

        $sqlInsert = '
            INSERT INTO ' . BD_TEMPORAL . '.tem_Clc ( clc_id,ecf_id,clc_percepcion,clc_retencion,mpc_id,clc_lineaCaptura,clc_rectificada,clc_cuentaProductiva,clc_referencia,tcl_id,stc_id,gpo_id,ur_id,bur_id,cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,mno_id,smn_id,tno_id,clc_beneficiario,clc_concepto,clc_firmas,tem_carga,usr_id ) VALUES ';
        $values = '';
        $fila = 3;
        $numRegClc = 0;
        $this->conectar();
        while ($clcs->getCell('A' . $fila)->getValue() != '') {
            $mno_id = '';   // modalidad de nómina
            $smn_id = '';   // submodalidad de nómina

            // Dependiendo de la modalidad, ajustamos el campo de submodalidad
            // Hacemos la conversión cuando tengan valores entre 61 y 69 ...
            switch ($clcs->getCell('N' . $fila)->getValue()) {
                case 61:
                    $smn_id = '1';
                    // no break
                case 62:
                    $smn_id = ($smn_id == '') ? '2' : $smn_id;
                    // no break
                case 63:
                    $smn_id = ($smn_id == '') ? '3' : $smn_id;
                    // no break
                case 64:
                    $smn_id = ($smn_id == '') ? '4' : $smn_id;
                    // no break
                case 65:
                    $smn_id = ($smn_id == '') ? '5' : $smn_id;
                    // no break
                case 66:
                    $smn_id = ($smn_id == '') ? '6' : $smn_id;
                    // no break
                case 67:
                    $smn_id = ($smn_id == '') ? '7' : $smn_id;
                    // no break
                case 68:
                    $smn_id = ($smn_id == '') ? '8' : $smn_id;
                    // no break
                case 69:
                    $smn_id = ($smn_id == '') ? '9' : $smn_id;
                    $mno_id = '6';
                    break;
                    // Si no, le ponemos lo que traiga (si no trae nada le ponemos 0 )
                default:
                    $mno_id = $clcs->getCell('N' . $fila)->getValue();
                    $mno_id = (empty($mno_id)) ? '0' : $mno_id;
                    $smn_id = '0';
                    break;
            }

            $tno_id = $clcs->getCell('O' . $fila)->getValue();
            $tno_id = (empty($tno_id)) ? 'null' : $tno_id;
            $stc_id = $clcs->getCell('C' . $fila)->getValue(); //stc_id,   --> COLUMNA "CONCEPTO DE PAGO"
            $stc_id = (empty($stc_id)) ? '0' : $stc_id; //stc_id,

            $con = new AccesoDatos();
            $con->conectar();
            // La columna D tiene el grupo, y E tiene la ur
            // Si están vacías entonces NO validamos que tenga sólo una UR por afectación
            if (
                trim($clcs->getCell('D' . $fila)->getValue()) != '' and
                trim($clcs->getCell('E' . $fila)->getValue()) != ''
            ) {
                $whereGpoUr = ' AND gpo_id = ' . $clcs->getCell('D' . $fila)->getValue() . ' AND ur_id = ' . $clcs->getCell('E' . $fila)->getValue();
                $soloUnaURporAfectacion = true;
            } else {
                $whereGpoUr = '';
                $soloUnaURporAfectacion = false;
            }
            /* En el caso de CLCs CONSOLIDADAS, debe generarse ésta entrada de CLC por cada UR que la utilice en las afectaciones.
            Para homologar el código, validaremos que cuando NO SEAN consolidadas se permita únicamente una
            sola UR en todas las afectaciones que pertenezcan a una CLC
            Ergo, lo que estamos haciendo con ésta parte de la consulta es obtener todas las URs que contengan registros para
            una sola CLC, y generamos UNA CLC POR CADA UNA DE ESAS URs de la CLC actual, buscamos cuántas URs vienen en una sola CLC */

            $sql = '
                SELECT DISTINCT gpo_id,ur_id FROM (
                SELECT DISTINCT gpo_id,ur_id 
                FROM ' . BD_TEMPORAL . '.tem_Presupuestal
                WHERE clc_referencia = :clc_referencia
                AND tcl_id = :tcl_id
                AND stc_id = :stc_id
                AND mno_id = :mno_id
                AND smn_id = :smn_id
                AND ' . (($tno_id == 'null') ? ' ISNULL(tno_id )' : 'tno_id = ' . $tno_id) . ' AND
                    tem_carga = "' . $_SESSION[_S]['tem_carga'] . '" ' . $whereGpoUr . '
                UNION ALL
                SELECT DISTINCT gpo_id,ur_id 
                FROM ' . BD_TEMPORAL . '.tem_Contable 
                WHERE clc_referencia = :clc_referencia
                AND tcl_id = :tcl_id
                AND stc_id = :stc_id
                AND mno_id = :mno_id
                AND smn_id = :smn_id
                AND ' . (($tno_id == 'null') ? ' ISNULL(tno_id )' : 'tno_id = ' . $tno_id) . ' AND
                    tem_carga = "' . $_SESSION[_S]['tem_carga'] . '" ' . $whereGpoUr . ')urs;
            ';
            $params = [
                ':clc_referencia' => $clcs->getCell('A' . $fila)->getValue(),
                ':tcl_id'         => $clcs->getCell('B' . $fila)->getValue(),
                ':stc_id'         => $stc_id,
                ':mno_id'         => $mno_id,
                ':smn_id'         => $smn_id,
            ];
            $con->setSql($sql);
            $con->consultar($params);

            /* SI NO ES DE CLCS CONSOLIDADAS, DEBE REGRESAR UN SOLO REGISTRO ( O SEA UNA SOLA UR POR AFECTACIÓN )
                NO VALIDAMOS EL TIPOCARGA == 1 PORQUE COMO SON SÓLO AFECTACIONES, NUNCA GENERARÁN UNA CLC
                Y POR LO TANTO NUNCA ENTRARÁN A ÉSTA PARTE DEL CÓDIGO */

            if ($tipoCarga == '2') {
                if ($soloUnaURporAfectacion == true) {
                    if ($con->getNumeroRegistros() != 1) {
                        $conexion = new AccesoDatos();
                        $conexion->conectar();
                        if ($con->getNumeroRegistros() > 1) {
                            $conexion->setSql('
                                INSERT INTO ' . BD_TEMPORAL . ".tem_Clc( gpo_id, ur_id, clc_referencia, clc_concepto, tem_carga, usr_id ) 
                                VALUES(?, ?, 9999, 'ERROR: No se permite más de una Unidad Responsable por CLC" . $whereGpoUr . '  --' . $fila . "', ?, ?);
                            ");
                        } elseif ($con->getNumeroRegistros() == 0) {
                            $conexion->setSql('
                                INSERT INTO ' . BD_TEMPORAL . ".tem_Clc( gpo_id, ur_id, clc_referencia, clc_concepto, tem_carga, usr_id ) 
                                VALUES(?, ?, 9999, 'ERROR EN LA FILA " . $fila . ' : NO EXISTEN AFECTACIONES-INICIO PARA LA CLC CON REFERENCIA ' . $clcs->getCell('A' . $fila)->getValue() . " O ALGUNO DE LOS CAMPOS MODALIDAD, TIPO NOMINA, TIPO O SUBTIPO DE CLC', ?, ?);
                            ");
                        }
                        $params = [
                            $_SESSION[_S]['clasAdm-gpo'],
                            $_SESSION[_S]['clasAdm-ur'],
                            $_SESSION[_S]['tem_carga'],
                            $_SESSION[_S]['usr_id'],
                        ];
                        $conexion->consultar($params);
                        $conexion->desconectar();
                        $this->actualizaCarga(99, 'ERROR EN LA CARGA DE CLC DE RECURSOS HUMANOS, SE TRUNCAN LOS REGISTROS EN LA FILA ' . $fila . ' DEL FICHERO DE EXCEL');
                        return false;
                    }
                }
            }

            // Procedemos a cargar las CLCs en la tabla temporal
            while ($row = $con->extraer()) {
                $values .= ($values != '') ? ', ' : '';
                $values .= '(0,0,0,0,0,"",0,"", "' . $clcs->getCell('A' . $fila)->getValue() . '",'; //clc_referencia
                $values .= '"' . $clcs->getCell('B' . $fila)->getValue() . '",'; //tcl_id
                $values .= '"' . $clcs->getCell('C' . $fila)->getValue() . '",'; //stc_id
                $values .= $row['gpo_id'] . ','; //gpo_id
                $values .= $row['ur_id'] . ','; //ur_id
                $values .= str_replace("'", '', $clcs->getCell('F' . $fila)->getValue() . ','); //bur_id
                $values .= '"' . $clcs->getCell('G' . $fila)->getValue() . '",'; //cff_id
                $values .= '"' . $clcs->getCell('H' . $fila)->getValue() . '",'; //fte_id
                $values .= '"' . $clcs->getCell('I' . $fila)->getValue() . '",'; //sft_id
                $values .= '"' . $clcs->getCell('J' . $fila)->getValue() . '",'; //ori_id
                $values .= '"' . $clcs->getCell('K' . $fila)->getValue() . '",'; //sor_id
                $values .= $clcs->getCell('L' . $fila)->getValue() . ','; //cfi_id
                $values .= $clcs->getCell('M' . $fila)->getValue() . ','; //cfi_anio
                $values .= $mno_id . ','; //mno_id
                $values .= $smn_id . ','; //smn_id
                $values .= $tno_id . ','; //tno_id
                $values .= '"' . $clcs->getCell('P' . $fila)->getValue() . '",'; //clc_beneficiario
                $values .= '"' . $clcs->getCell('Q' . $fila)->getValue() . '",'; //clc_concepto
                $values .= $clcs->getCell('R' . $fila)->getValue() . ','; //clc_firmas
                $values .= '"' . $_SESSION[_S]['tem_carga'] . '",' . $_SESSION[_S]['usr_id'] . ')'; //tem_carga
                $numRegClc++;
                if ($numRegClc > $maxRegistrosPorInsert) {
                    $this->setSql($sqlInsert . $values);
                    $this->consultar();
                    $values = '';
                    $numRegClc = 0;
                }
            }
            $fila++;
        }
        $this->desconectar();
        if ($values != '') {
            $this->conectar();
            $this->setSql($sqlInsert . $values);
            $this->consultar();
            $this->desconectar();
        }
        $datetime2 = new DateTime('now');
        $interval = date_diff($datetime1, $datetime2);
        $tiempo = ($interval->format('%i minutos %s segundos'));
        $this->bitacora(18, 'La carga de clcs tardo ' . $tiempo);
        $this->actualizaCarga(18, 'TERMINA LA CARGA DE CLC DE RECURSOS HUMANOS');
        return true;
    }

    // Marcos: tratamos de meter todo lo que se pudiera dentro de una consulta.
    // El ciclo de clc por clc se mantiene para el devengado únicamente
    private function crearCLCS($tipoCarga)
    {
        if ($this->getEstadoCarga($_SESSION[_S]['tem_carga']) != 69) {
            $this->mixScript('notify("Ya se inicio el copiado o la carga tiene errores","warning");');
            return false;
        }
        $datetime1 = new DateTime('now');

        $this->actualizaCarga(17, 'CREAR CLCLS');
        $nRegistros = 0;

        $this->conectar();

        // Hay que ejecutar ésto aquí porque smn_id es parte del JOIN al actualizar el campo tem_Clc
        // y si lo hacemos en otro lugar NO actualizará correctamente éstos datos
        //Para que inserte sin ningun problema en las tablas de scp el campo de submodalidad se pone en NULL donde sea 0
        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Clc 
            SET smn_id = null 
            WHERE tem_carga = ?
            AND smn_id = 0;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);

        // Para carga masiva de RH o sólo afectaciones, el estado de la clc será 3 ( ENVIADA A SEFIN )
        if ($tipoCarga != '3') {
            $ecl_id = 2;
            // Si es Consolidada, el estado de la CLC será 2 ( AUTORIZADA POR LA UR )
        } else {
            $ecl_id = 2;
        }

        // Como una carga de afectaciones que realizan las URs no llegan hasta CLC, entonces aquí
        // Siempre será su origen Recursos Humanos, que es -hasta el momento- el único que puede
        // cargar CLCs masivamente
        $ocl_id = 3;   // Origen de la CLC ( 3 = Recursos Humanos )
        $tcp_id = 'D';   // Compra Directa

        // Pasamos todas las CLCs temporales a la tabla definitiva
        // NOTA: Usaremos en scp_Clc el campo clc_cereCo provisionalmente como vínculo entre
        // el temporal y definitivo, almacenando ahí el número de CLC temporal
        $this->setSql("
            INSERT INTO scp_Clc(clc_tipoCereCo,clc_nombreAdministrativo,clc_cargoAdministrativo,clc_nombreTitular, clc_cargoTitular,clc_nombreAdministracion,clc_nombreRecursos,
            gpo_id, ur_id, tcp_id, clc_concepto, clc_beneficiario, clc_fechaElaboracion, clc_percepcion, clc_retencion, ecl_id, mpc_id, bur_id, ocl_id, ecf_id, clc_firmas, tcl_id, stc_id, tno_id, mno_id, smn_id, tem_carga, usr_id,usr_fecha, clc_consolidada, clc_formaPago )
            SELECT 
            IF(
                tcl_id IN ('A','B','F','G','L') OR (tcl_id IN ('I','J','K')  AND stc_id IN (01,03))
                ,1
                ,IF(
                    tcl_id IN ('C','D','E','H','M') OR (tcl_id IN ('I','J','K')  AND stc_id = 02)
                    ,2
                    ,0
                )
            ),'','','', '','','',
            gpo_id, ur_id, '$tcp_id' AS tcp_id, clc_concepto, clc_beneficiario, NOW() AS clc_fechaElaboracion, clc_percepcion, clc_retencion, '$ecl_id' AS ecl_id,1 mpc_id, bur_id, 
            '$ocl_id' AS ocl_id, ecf_id, clc_firmas, tcl_id, stc_id, tno_id, mno_id, if(isnull(smn_id),0,smn_id ) smn_id, tem_carga, " . $_SESSION[_S]['usr_id'] . ", NOW() AS usr_fecha, tem_Clc AS clc_consolidada, 'S'
                FROM " . BD_TEMPORAL . '.tem_Clc
                WHERE tem_carga = ?;
            ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->bitacora(17, 'Creamos las CLC-INSERT INTO scp_Clc - 3142');
        // ACTUALIZAMOS EL VALOR REAL DE CLC_ID EN LAS TABLAS TEMPORALES - CLC-AFECTACION - PRESUPUESTAL - CONTABLE
        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Clc t 
            JOIN scp_Clc s
            ON ( t.gpo_id = s.gpo_id
                AND t.ur_id = s.ur_id 
                AND t.tem_Clc  = s.clc_consolidada
                AND t.tem_carga = s.tem_carga )
            SET t.clc_id = s.clc_id
            WHERE t.tem_carga = ?;
        ');

        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->bitacora(17, ' Actualizamos el valor real de clc_id en las tablas temporales - CLC');

        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Afectacion t 
            JOIN scp_Clc s
            ON ( t.gpo_id = s.gpo_id 
                AND t.ur_id = s.ur_id 
                AND t.tem_Clc  = s.clc_consolidada 
                AND t.tem_carga = s.tem_carga )
            SET t.clc_id = s.clc_id
            WHERE t.tem_carga = ?;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->bitacora(17, ' Actualizamos el valor real de clc_id en las tablas temporales - Afectacion');

        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Presupuestal t 
            JOIN scp_Clc s
            ON ( t.gpo_id = s.gpo_id 
                AND t.ur_id = s.ur_id 
                AND t.tem_Clc  = s.clc_consolidada 
                AND t.tem_carga = s.tem_carga )
            SET t.clc_id = s.clc_id
            WHERE t.tem_carga = ?;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->bitacora(17, ' Actualizamos el valor real de clc_id en las tablas temporales - Presupuestal');

        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Contable t 
            JOIN scp_Clc s
            ON ( t.gpo_id = s.gpo_id 
                AND t.ur_id = s.ur_id 
                AND t.tem_Clc  = s.clc_consolidada 
                AND t.tem_carga = s.tem_carga )
            SET t.clc_id = s.clc_id
            WHERE t.tem_carga = ?;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->bitacora(17, ' Actualizamos el valor real de clc_id en las tablas temporales - Contable');

        // CARGA MASIVA DE CLCs CONSOLIDADA
        if ($tipoCarga == '3') {
            //Agrupamos las CLC'S para ver que numero de clc consolidada le corresponde
            $this->setSql('
                SELECT GROUP_CONCAT("(",gpo_id,",",ur_id,",",clc_id,")") clcs
                FROM ' . BD_TEMPORAL . '.tem_Clc
                WHERE tem_carga  = ?
                GROUP BY clc_referencia,tcl_id,stc_id,mno_id ,smn_id ,tno_id ,tem_carga;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);
            $this->bitacora(17, "Agrupamos las CLC'S para ver que numero de clc consolidada le corresponde");
            while ($row = $this->extraer()) {
                $con = new AccesoDatos();
                $con->conectar();
                //consultamos el numero maximo de las clc consolidadas y calculamos el sig numero
                $con->setSql('SELECT MAX(clc_consolidada) + 1 nc FROM  scp_Clc;');
                $con->consultar();
                $numConsolidado = $con->extraer();
                $con->setSql('
                    UPDATE scp_Clc
                    SET clc_consolidada = ' . $numConsolidado['nc'] . ' 
                    WHERE (gpo_id,ur_id,clc_id) IN (' . $row['clcs'] . ');
                ');
                $con->consultar();
                $con->desconectar();
                $this->bitacora(17, 'consultamos el numero maximo de las clc consolidadas y calculamos el sig numero');
            }
        }

        //Actualizamos nombres para las firmas

        $this->bitacora(17, 'iniciamos Actualializar nombres para las firmas');
        $this->setSql('
            UPDATE scp_Clc
            JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            JOIN aux_UnidadResponsable  USING ( gpo_id ,ur_id )
            SET
                clc_fechaEnvio = now(),
                clc_nombreAdministrativo = ur_nombreAdministrativo,
                clc_cargoAdministrativo = ur_cargoAdministrativo ,
                clc_nombreTitular = ur_nombreTitular , 
                clc_cargoTitular = ur_cargoTitular  ,
                clc_nombreAdministracion = (
                                            SELECT ur_nombreTitular 
                                            FROM cat_UnidadResponsable 
                                            JOIN aux_UnidadResponsable  USING ( gpo_id ,ur_id )
                                            WHERE gpo_id = 1 ANd ur_id = 18
                                            ),
                clc_nombreRecursos = (
                                        SELECT ur_nombreAdministrativo
                                        FROM cat_UnidadResponsable
                                        JOIN aux_UnidadResponsable  USING ( gpo_id ,ur_id )
                                        WHERE gpo_id = 1 ANd ur_id = 18
                                    )
            WHERE tem_carga  = "' . $_SESSION[_S]['tem_carga'] . '";
        ');
        $this->consultar();
        $this->bitacora(17, ' Actualizamos nombres para las firmas - TITULAR Y ADMINISTRATIVO');
        /*
                #SECRETARIO DE ADMINISTRACIÓN clc_nombreAdministracion
                SELECT ur_nombreTitular,ur_cargoTitular FROM cat_UnidadResponsable WHERE gpo_id = 3 ANd ur_id = 18;
                #DIRECTOR DE RECURSOS HUMANOS clc_nombreRecursos
                SELECT ur_nombreAdministrativo,ur_cargoAdministrativo FROM cat_UnidadResponsable WHERE gpo_id = 3 ANd ur_id = 18;
            */
        //Actualizamos nombres para las firmas
        $this->setSql('
            UPDATE scp_Clc s
            JOIN cat_UnidadResponsable
            USING(gpo_id,ur_id)
            SET clc_beneficiario = ur_nombre
            WHERE tem_carga  = ?
            AND TRIM( s.clc_beneficiario ) = "";
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->bitacora(17, ' Actualizamos nombres para las firmas- BENEFICIARIO');

        // Cuando NO SE TRATE de carga masiva de CLCs consolidadas, devengaremos en éste momento
        // ( cuando se trate de consolidadas, devengaremos más adelante )
        // ------------------------------------------------------------------------------------------
        if ($tipoCarga != '3') {
            $this->setSql('
                SELECT gpo_id,ur_id,clc_id
                FROM scp_Clc
                WHERE tem_carga = ?;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);
            $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
            $user = $_SESSION[_S]['usr_id'];
            while ($clc = $this->extraer()) {
                $con = new AccesoDatos();
                $con->conectar();
                /*Este sp no existe en ctrl2025, validar si es necesrio o eliminar */
                $con->setSql('
                    CALL sp_Devengar2014(' . $clc['gpo_id'] . ',' . $clc['ur_id'] . ',' . $clc['clc_id'] . ',INET_ATON("' . $ip . '"),' . $user . '); ');
                $con->desconectar();
                $nRegistros++;
            }
            $this->desconectar();
        }
        $this->bitacora(17, 'FIN DE LA FUNCION');
        $this->actualizaCarga(18, 'COPIANDO ARCHIVO');
        $datetime2 = new DateTime('now');
        $interval = date_diff($datetime1, $datetime2);
        $tiempo = ($interval->format('%i minutos %s segundos'));
        $this->bitacora(17, 'la creacion de clcs tardo ' . $tiempo);
        return $nRegistros;
    }

    // Marcos: tratamos de meter todo lo que se pudiera dentro de una consulta.
    private function creaAfectaciones($origen)
    {
        $estado = $this->getEstadoCarga($_SESSION[_S]['tem_carga']);
        $procesa = false;
        switch ($estado) {
            case 18:
            case 69:
                $procesa = true;
                break;
            default:
                $procesa = false;
                break;
        }

        if (!$procesa) {
            $this->bitacora(17, 'ocurrio un error en el estado de la afectacion creaAfectaciones ' . $this->getEstadoCarga($_SESSION[_S]['tem_carga']));
            $this->actualizaCarga(6, 'SE DEJA EN ESTADO 6 PARA REPOROCESAR');
            return false;
        }
        $datetime1 = new DateTime('now');
        $this->actualizaCarga(69, 'CREAR AFECTACIONES');

        $this->conectar();

        //Para que inserte sin ningun problema en las tablas de scp el campo de submodalidad se pone en NULL donde sea 0
        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Afectacion
            SET smn_id = null
            WHERE tem_carga = ?
            AND smn_id = 0;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Presupuestal
            SET smn_id = null
            WHERE tem_carga = ?
            AND smn_id = 0;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Contable
            SET smn_id = null
            WHERE tem_carga = ?
            AND smn_id = 0;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        //para el caso de las CLC que no contengan o no requieran el tipo de nomina igual se actualizara
        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Afectacion
            SET mno_id = null
            WHERE tem_carga = ?
            AND mno_id = 0;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Presupuestal
            SET mno_id = null 
            WHERE tem_carga = ?
            AND mno_id = 0;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Contable
            SET mno_id = null
            WHERE tem_carga = ?
            AND mno_id = 0;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);

        // =========================================================================================================
        // Insertamos los documentos en el Banco de Documentos
        // =========================================================================================================
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $this->setSql('
            SET @usr_ip = INET_ATON(?),
            @usr_id = ?,
            @bit_observacion="SE CREAN LOS DOCUMENTOS DESDE CARGA MASIVA" ;
        ');
        $this->consultar([$ip, $_SESSION[_S]['usr_id']]);

        $this->setSql('
            INSERT INTO cat_BancoDocumentos( gpo_id, ur_id, bdo_referencia, bdo_concepto, bdo_agrupacionGlobal, rfc_id, bdo_fondoRotatorio,
                bdo_percepcionPresupuestal, bdo_retencionContable, bdo_percepcionContable, 
                bdo_fechaDocumento, sts_id, bdo_estadoPago, bdo_estadoSat,  bdo_Origen, tdf_id, tem_carga, usr_id)
            SELECT 
                gpo_id, ur_id, bdo_referencia, 
                MIN(nc.bdo_concepto) AS bdo_concepto,
                "GRAL" AS bdo_agrupacionGlobal, rfc_id, "' . $this->prm->afe_fr . '",
                SUM( percepcionesPresupuestal ) AS percepcionesPresupuestal , 
                SUM( retencionesContables ) AS retencionesContables, 
                SUM( percepcionesContables ) AS percepcionesContables,
                MIN(fechaDocumento) AS fechaDocumento,
                MIN(sts) AS sts,
                MIN(estadoPago) AS estadoPago,
                MIN(estadoSat) AS estadoSat,
                MIN(origen) AS origen,
                MIN(tdf) AS tdf,
                "' . $_SESSION[_S]['tem_carga'] . '" tem_carga,' . $_SESSION[_S]['usr_id'] . '
            FROM(
                SELECT gpo_id, ur_id, clc_referencia AS bdo_referencia, ncp_concepto AS bdo_concepto, rfc_id, 
                    ncp_percepcionNeto  AS percepcionesPresupuestal, 0 AS percepcionesContables, 0 AS retencionesContables,
                    ncp_fechaReferencia AS fechaDocumento, 1 AS sts, 1 AS estadoPago, 3 AS estadoSat, 3 AS origen, "GRAL" AS tdf, tem_carga
                FROM ' . BD_TEMPORAL . '.tem_Presupuestal 
                WHERE tem_carga = :tem_carga
                UNION ALL
                SELECT gpo_id, ur_id, clc_referencia AS bdo_referencia, ncc_concepto AS bdo_concepto,  rfc_id, 
                    0 AS percepcionesPresupuestal, ncc_percepcion  AS percepcionesContables, ncc_retencion  AS retencionesContables,
                    ncc_fechaReferencia AS fechaDocumento, 1 AS sts, 1 AS estadoPago, 3 AS estadoSat, 3 AS origen, "GRAL" AS tdf, tem_carga
                FROM ' . BD_TEMPORAL . '.tem_Contable nc 
                WHERE tem_carga = :tem_carga
                #GROUP BY gpo_id, ur_id, rfc_id, bdo_referencia
            )nc
            LEFT JOIN cat_BancoDocumentos bdo USING(gpo_id,ur_id,rfc_id,bdo_referencia)
            WHERE isnull(bdo.bdo_id)
            GROUP BY gpo_id , ur_id , tdf_id, rfc_id , bdo_referencia; 
        ');
        $this->bindParams([':tem_carga' => $_SESSION[_S]['tem_carga']]);
        //$this->consultar();

        reboot3799:
        if (!$this->consultar([], false)) {
            if ($this->getErrno() == 1213) {
                $this->bitacora(17, 'Deadlock found when trying to get lock; try restarting transaction, esperamos 2 segundos e intentamos nuevamente');
                sleep(2);
                goto reboot3799;
            } else {
                /******************* LOG DE CONSULTAS FALLIDAS************************************/
                $tem_carga = '';
                $rutalog = 'log/carga/' . $tem_carga . '.log';
                $file = fopen($rutalog, 'a');
                $tempdata = '';
                $hoy = date('Y-m-d-H-i-s');
                $tempdata .= '|IPADDRESS=' . $_SESSION[_S]['ses_ip'];
                $tempdata .= '|USRID=' . $_SESSION[_S]['usr_id'];

                fwrite($file, $this->getError() . 'Error de carga masiva en esta consulta:' . $hoy . $tempdata . ':' . $this->getSql() . PHP_EOL);
                fclose($file);
                $this->mixScript('notify("Ocurrio un error en su carga masiva (3) : Error ' . $this->getErrno() . '","error",false);');
                return false;
                /******************* LOG DE CONSULTAS FALLIDAS************************************/
            }
        }
        $this->logtxt('3519', $this->getSql(), 'CREA DOCUMENTOS');
        $this->bitacora(69, 'CREAR DOCUMENTOS');

        // Y le ponemos dicho número definitivo en las tablas temporales
        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Presupuestal t 
            JOIN cat_BancoDocumentos s 
            ON ( t.gpo_id = s.gpo_id AND t.ur_id = s.ur_id AND t.rfc_id = s.rfc_id AND t.clc_referencia = s.bdo_referencia)
            SET t.bdo_id = s.bdo_id
            WHERE t.tem_carga = ?;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->logtxt('3530', $this->getSql(), 'Y LE PONEMOS DICHO NÚMERO DEFINITIVO EN LAS TABLAS TEMPORALES');
        $this->bitacora(69, 'Y LE PONEMOS DICHO NÚMERO DEFINITIVO EN LAS TABLAS TEMPORALES');

        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Contable t 
            JOIN cat_BancoDocumentos s 
            ON ( t.gpo_id = s.gpo_id AND t.ur_id = s.ur_id AND t.rfc_id = s.rfc_id AND t.clc_referencia = s.bdo_referencia)
            SET t.bdo_id = s.bdo_id
            WHERE t.tem_carga = ?;
        ');
        //$this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->logtxt('3541', $this->getSql(), '');

        // =========================================================================================================
        // INSERTAMOS LOS COMPROMISOS EN LA TABLA DEFINITIVA
        // =========================================================================================================
        $this->setSql('
            INSERT INTO scp_Compromiso( 
            gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id, tcl_id, stc_id, tno_id, mno_id, smn_id, rfc_id, 
            cmp_fechaInicio, cmp_fechaFin, cmp_numOrden, cmp_fechaOrden, cmp_concepto, cmp_tipoMovimiento, cmp_importe, iva_id, tpd_id, ecm_id, 
            cmp_fechaAutorizado, cmp_tipoCompromiso, tem_carga, cmp_Temporal,usr_id )
            SELECT gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id, tcl_id, stc_id, if(tno_id=0,null,tno_id) , if(isnull(mno_id),0,mno_id ) mno_id, if(isnull(smn_id),0,smn_id ) smn_id, rfc_id,
            afe_fechaInicio AS cmp_fechaInicio, afe_fechaFin AS cmp_fechaFin, clc_referencia AS cmp_numOrden, ncp_fechaReferencia AS cmp_fechaOrden, 
            ncp_concepto AS cmp_concepto, "+" AS cmp_tipoMovimiento, SUM( ncp_percepcionNeto ) AS cmp_importe, iva_id, 2 AS tpd_id, 
            "G" AS ecm_id, NOW() AS cmp_fechaAutorizado, "N" AS cmp_tipoCompromiso, tem_Carga, tem_afe,' . $_SESSION[_S]['usr_id'] . '
            FROM ' . BD_TEMPORAL . '.tem_Presupuestal
            WHERE tem_carga = ?
            GROUP BY gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id, tcl_id, stc_id, tno_id, mno_id, smn_id, rfc_id, clc_referencia, ncp_concepto, afe_fechaInicio, afe_fechaFin, ncp_fechaReferencia, iva_id, tem_afe;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->logtxt('3560', $this->getSql(), 'INSERTAMOS LOS COMPROMISOS EN LA TABLA DEFINITIVA');
        $this->bitacora(69, 'INSERTAMOS LOS COMPROMISOS EN LA TABLA DEFINITIVA');

        // Y LE PONEMOS DICHO NÚMERO DEFINITIVO EN LAS TABLAS TEMPORALES
        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Afectacion t
            JOIN scp_Compromiso s 
            ON ( t.gpo_id = s.gpo_id AND t.ur_id = s.ur_id AND t.tem_afe = s.cmp_Temporal AND t.tem_carga = s.tem_carga )
            SET t.afe_cmp_id = s.cmp_id
            WHERE t.tem_carga = ?;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->logtxt('3572', $this->getSql(), 'Y LE PONEMOS DICHO NÚMERO DEFINITIVO EN LAS TABLAS TEMPORALES');
        $this->bitacora(69, 'Y LE PONEMOS DICHO NÚMERO DEFINITIVO EN LAS TABLAS TEMPORALES');

        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Presupuestal p
            JOIN(
                SELECT gpo_id,ur_id,epg_id,pg_id,pe_id,ecf_id,tcp_id,rfc_id,tem_carga,cmp_numOrden clc_referencia,cmp_id, cmp_concepto as ncp_concepto
                FROM scp_Compromiso 
                WHERE tem_carga = :tem_carga
            ) c USING(gpo_id,ur_id,epg_id,pg_id,pe_id,ecf_id,tcp_id,rfc_id,clc_referencia,tem_carga,ncp_concepto)
            SET p.cmp_id = c.cmp_id
            WHERE tem_carga = :tem_carga;
        ');
        $this->consultar([':tem_carga' => $_SESSION[_S]['tem_carga']]);
        $this->logtxt('3586', $this->getSql(), '');

        // =========================================================================================================
        // Insertamos las AFECTACIONES en la tabla definitiva eaf_id
        // =========================================================================================================
        $this->setSql('SET @cargaMasivaEnProceso = 1;');
        $this->consultar();
        $this->setSql('
            INSERT INTO scp_Afectacion( 
            gpo_id, ur_id, clc_id, afe_id, afe_cmp_id, epg_id, ecf_id, ue_id, tcp_id, tcl_id, stc_id, tno_id, mno_id, smn_id, afe_fechaElaboracion, afe_fechaInicio, afe_fechaFin, afe_fondoRotatorio, tem_carga, usr_id, usr_fecha, afe_Temporal )
            SELECT 
            gpo_id, ur_id, clc_id, afe_id, afe_cmp_id, epg_id, ecf_id, ue_id, IF( tcl_id ="C" OR tcp_id="" ,null,tcp_id )tcp_id, tcl_id, stc_id, IF( tno_id = 0, NULL, tno_id), if(isnull(mno_id),0,mno_id ) mno_id,if(isnull(smn_id),0,smn_id ) smn_id, NOW(),afe_fechaInicio,afe_fechaFin, "' . $this->prm->afe_fr . '", tem_carga, ' . $_SESSION[_S]['usr_id'] . ' usr_id, NOW(), tem_afe
            FROM ' . BD_TEMPORAL . '.tem_Afectacion
            WHERE tem_carga = ?
            ORDER BY gpo_id, ur_id, epg_id, ecf_id, ue_id, tcp_id;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->logtxt('3600', $this->getSql(), 'INSERTAMOS LAS AFECTACIONES EN LA TABLA DEFINITIVA');
        $this->bitacora(69, 'INSERTAMOS LAS AFECTACIONES EN LA TABLA DEFINITIVA');

        $registrados = $this->getNumeroModificados();

        // Y le ponemos dicho número definitivo en las tablas temporales
        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Afectacion t
            JOIN scp_Afectacion s
            ON ( t.gpo_id = s.gpo_id AND t.ur_id = s.ur_id AND t.clc_id = s.clc_id AND t.tem_afe = s.afe_Temporal AND t.tem_carga = s.tem_carga )
            SET t.afe_id = s.afe_id
            WHERE t.tem_carga = ?;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->logtxt('3613', $this->getSql(), 'Y LE PONEMOS DICHO NÚMERO DEFINITIVO EN LAS TABLAS TEMPORALES');
        $this->bitacora(69, 'Y LE PONEMOS DICHO NÚMERO DEFINITIVO EN LAS TABLAS TEMPORALES');

        // En Consolidadas, actualizamos afe_id y clc_id
        if ($_SESSION[_S]['tem_carga'] == '3') {
            $this->setSql('
                UPDATE ' . BD_TEMPORAL . '.tem_Presupuestal p
                JOIN (
                    SELECT  gpo_id,ur_id,epg_id,pg_id,pe_id,ecf_id,tcp_id,rfc_id,cmp_importe ncp_percepcionNeto,tem_carga,cmp_id,cmp_numOrden ncp_referencia
                    FROM scp_Compromiso 
                    WHERE tem_Carga = :tem_carga
                    ) c USING(gpo_id,ur_id,epg_id,pg_id,pe_id,ecf_id,tcp_id,rfc_id,ncp_referencia,ncp_percepcionNeto,tem_carga)
                SET p.cmp_id = c.cmp_id
                WHERE p.tem_carga = :tem_carga;
            ');
            $this->consultar([':tem_carga' => $_SESSION[_S]['tem_carga']]);
            $this->logtxt('3628', $this->getSql(), '');

            $this->setSql('
                UPDATE ' . BD_TEMPORAL . '.tem_Contable t JOIN scp_Afectacion s 
                ON ( t.gpo_id = s.gpo_id AND t.ur_id = s.ur_id AND t.clc_id = s.clc_id AND t.tem_afe = s.afe_Temporal AND t.tem_carga = s.tem_carga )
                SET t.clc_id = s.clc_id, t.afe_id = s.afe_id
                WHERE t.tem_carga = ?;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);
            $this->logtxt('3635', $this->getSql(), '');
            // En el caso de RH y Afectaciones, sólo afe_id es actualizada
        } else {
            $this->setSql('
                UPDATE ' . BD_TEMPORAL . '.tem_Presupuestal t 
                JOIN scp_Afectacion s 
                ON ( t.gpo_id = s.gpo_id AND t.ur_id = s.ur_id AND t.clc_id = s.clc_id AND t.tem_afe = s.afe_Temporal AND t.tem_carga = s.tem_carga )
                SET t.afe_id = s.afe_id
                WHERE t.tem_carga = ?;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);
            $this->logtxt('3643', $this->getSql(), '');

            $this->setSql('
                UPDATE ' . BD_TEMPORAL . '.tem_Contable t 
                JOIN scp_Afectacion s 
                ON ( t.gpo_id = s.gpo_id AND t.ur_id = s.ur_id AND t.clc_id = s.clc_id AND t.tem_afe = s.afe_Temporal AND t.tem_carga = s.tem_carga )
                SET t.afe_id = s.afe_id
                WHERE t.tem_carga = ?;
            ');
            $this->consultar([$_SESSION[_S]['tem_carga']]);
            $this->logtxt('3650', $this->getSql(), 'PONEMOS EL NUMERO DE AFECTACION DEFINITIVA EN LAS TEMPORALES');
            $this->bitacora(69, 'PONEMOS EL NUMERO DE AFECTACION DEFINITIVA EN LAS TEMPORALES');
        }

        $this->setSql('
            INSERT INTO scp_Presupuestal (gpo_id,ur_id,clc_id,afe_id, cmp_id, ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id,rfc_id,ncp_concepto,ncp_referencia,ncp_fechaReferencia,ncp_percepcionBruto,iva_id,ncp_percepcionIva,ncp_percepcionNeto, bdo_id, tpd_id, tem_carga,usr_id,usr_fecha)
            SELECT gpo_id,ur_id,clc_id,afe_id, cmp_id, ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id,rfc_id,ncp_concepto,clc_referencia,ncp_fechaReferencia,ncp_percepcionBruto,iva_id,ncp_percepcionIva,ncp_percepcionNeto, bdo_id, 2 AS tpd_id, tem_carga,' . $_SESSION[_S]['usr_id'] . ' usr_id,"' . $this->hoy . '" usr_fecha
            FROM ' . BD_TEMPORAL . '.tem_Presupuestal
            WHERE tem_carga = ?;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->logtxt('3662', $this->getSql(), 'INSERTAMOS EN SCP_PRESUPUTESTAL');
        $this->bitacora(69, 'INSERTAMOS EN SCP_PRESUPUESTAL');

        $this->setSql('
            INSERT INTO scp_Contable(gpo_id,ur_id,clc_id,afe_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id,rfc_id,ncc_concepto,ncc_referencia,ncc_fechaReferencia,ncc_tipoMovimiento,ncc_percepcion,ncc_retencion,ncc_nccamortiza,bdo_id,tem_carga,usr_id,usr_fecha)
            SELECT c.gpo_id,c.ur_id,clc_id,afe_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id,c.rfc_id,ncc_concepto,c.clc_referencia,c.ncc_fechaReferencia,ncc_tipoMovimiento,ncc_percepcion,ncc_retencion,ncc_amortiza,bdo_id,c.tem_carga,' . $_SESSION[_S]['usr_id'] . ' usr_id,"' . $this->hoy . '" usr_fecha
            FROM ' . BD_TEMPORAL . '.tem_Contable c
            JOIN cat_BancoDocumentos bd  ON(c.gpo_id = bd.gpo_id AND c.ur_id = bd.ur_id AND c.rfc_id = bd.rfc_id AND c.clc_referencia = bd.bdo_referencia)
            WHERE c.tem_carga = ?
            AND ( ( ncc_tipoMovimiento="-" AND ncc_retencion >0 ) or( ncc_tipoMovimiento="+" AND ncc_percepcion >0))
            ;
        ');

        $n = 0;
        reboot3926:
        if (!$this->consultar([$_SESSION[_S]['tem_carga']], false)) {
            if ($this->getErrno() == 1213) {
                $n++;
                $this->bitacora(17, 'Deadlock found when trying to get lock; try restarting transaction');
                sleep(2);

                if ($n < 10) {
                    $this->setErrno(0);
                    goto reboot3926;
                } else {
                    die();
                }
            } else {
                /******************* LOG DE CONSULTAS FALLIDAS************************************/
                $tem_carga = '';
                $rutalog = 'log/carga/' . $tem_carga . '.log';
                $file = fopen($rutalog, 'a');
                $tempdata = '';
                $hoy = date('Y-m-d-H-i-s');
                $tempdata .= '|IPADDRESS=' . $_SESSION[_S]['ses_ip'];
                $tempdata .= '|USRID=' . $_SESSION[_S]['usr_id'];
                $sqlInsertC = '';
                $valuesC = '';
                fwrite($file, 'Error de carga masiva en esta consulta:' . $hoy . $tempdata . ':' . $sqlInsertC . $valuesC . PHP_EOL);
                fclose($file);
                $this->mixScript('notify("Ocurrio un error en su carga masiva (4) : Error ' . $this->getErrno() . '","error",false);');
                return false;
                /******************* LOG DE CONSULTAS FALLIDAS************************************/
            }
        }

        $this->logtxt('3675', $this->getSql(), 'INSERTAMOS EN SCP_CONTABLE');
        $this->bitacora(69, 'INSERTAMOS EN SCP_CONTABLE');
        //se actualizan los totales de la precepcion de la afectacion
        $this->setSql('
            UPDATE scp_Afectacion
            JOIN(
                SELECT SUM(percepcion)percepcion ,SUM(retencion)retencion ,gpo_id,ur_id,afe_id FROM(
                    SELECT sum(ncp_percepcionNeto) percepcion, 0 retencion ,gpo_id,ur_id,afe_id
                    FROM scp_Presupuestal
                    WHERE tem_carga = :tem_carga
                    GROUP BY gpo_id,ur_id,afe_id
                    UNION ALL
                    SELECT sum(ncc_percepcion) percepcion,sum(ncc_retencion) retencion ,gpo_id,ur_id,afe_id
                    FROM scp_Contable
                    WHERE tem_carga = :tem_carga
                    GROUP BY gpo_id,ur_id,afe_id
                )p GROUP BY gpo_id,ur_id,afe_id
            )per USING(gpo_id,ur_id,afe_id)
            SET afe_percepcion = percepcion, afe_retencion = retencion, ocl_id = ' . $origen . '
            WHERE tem_carga = :tem_carga;
        ');
        $this->consultar([':tem_carga' => $_SESSION[_S]['tem_carga']]);
        $this->logtxt('3699', $this->getSql(), 'ACTUALIZAMOS LOS IMPORTES DE LA AFECTACION SUMANDO RETENCIONES , PERCEPCONES');
        $this->bitacora(69, 'ACTUALIZAMOS LOS IMPORTES DE LA AFECTACION SUMANDO RETENCIONES , PERCEPCONES');

        $this->setSql('
            UPDATE scp_Contable AS c
            INNER JOIN scp_Afectacion AS a USING (gpo_id,ur_id,afe_id)
            JOIN(
                SELECT e.gpo_id AS gpo, e.ur_id AS ur, e.clc_id AS clc, e.ncc_id AS ncc_nccamortiza
                FROM scp_Contable AS e
                INNER JOIN (
                    SELECT ncc_nccamortiza AS ncc_id 
                    FROM scp_Contable 
                        WHERE tem_carga = ?
                )AS t USING (ncc_id)
            )AS b USING (ncc_nccamortiza)
            SET c.ncc_gpo = b.gpo,
                c.ncc_ur = b.ur,
                c.ncc_clc = b.clc
            WHERE a.eaf_id = "G"
            ;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->logtxt('3715', $this->getSql(), 'SI HAY AMORTIZACIONES ACTUALIZAMOS  ncc_gpo,ncc_ur,ncc_clc');
        $this->bitacora(69, 'SI HAY AMORTIZACIONES ACTUALIZAMOS  ncc_gpo,ncc_ur,ncc_clc');

        $this->setSql('SET @scp =1;');
        $this->consultar();
        $this->setSql('
            UPDATE scp_Contable 
            JOIN (
                SELECT ncc_gpo gpo_id,ncc_ur ur_id,ncc_clc clc_id, ncc_nccamortiza ncc_id 
                FROM scp_Contable 
                WHERE tem_carga = ?
            )a USING(gpo_id,ur_id,clc_id,ncc_id)
            SET ncc_amortizado = 1 
            WHERE ncc_amortizado = 2;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);

        $this->logtxt('3731', $this->getSql(), 'PASAMOS A AMORTIZADO LOS NUMEROS DE CONTROL AMORTIZADOS ( LOS DE ORIGEN )');
        $this->setSql('SET @scp =null;');
        $this->consultar();
        $this->bitacora(69, 'PASAMOS A AMORTIZADO LOS NUMEROS DE CONTROL AMORTIZADOS ( LOS DE ORIGEN )');

        $this->setSql('
            UPDATE scp_Contable p
            INNER JOIN scp_Afectacion c
                ON p.gpo_id = c.gpo_id AND p.ur_id =c.ur_id AND p.clc_id = c.clc_id AND p.afe_id = c.afe_id
            INNER JOIN scp_Contable o
                ON p.ncc_nccamortiza = o.ncc_id
            INNER JOIN scp_Afectacion a
                ON o.gpo_id = a.gpo_id AND o.ur_id = a.ur_id AND o.clc_id = a.clc_id AND o.afe_id = a.afe_id
            SET c.afe_fechaInicio = a.afe_fechaInicio, c.afe_fechaFin = a.afe_fechaFin
            WHERE p.ncc_nccamortiza !=0 AND c.afe_fechaFin IS NULL ;
        ');
        $this->consultar();
        reboot4073:

        if (!$this->consultar([], false)) {
            if ($this->getErrno() == 1213) {
                $this->bitacora(17, 'Deadlock found when trying to get lock; try restarting transaction');
                sleep(2);
                goto reboot4073;
            } else {
                /******************* LOG DE CONSULTAS FALLIDAS************************************/
                $tem_carga = '';
                $sqlInsertC = '';
                $valuesC = '';
                $rutalog = 'log/carga/' . $tem_carga . '.log';
                $file = fopen($rutalog, 'a');
                $tempdata = '';
                $hoy = date('Y-m-d-H-i-s');
                $tempdata .= '|IPADDRESS=' . $_SESSION[_S]['ses_ip'];
                $tempdata .= '|USRID=' . $_SESSION[_S]['usr_id'];
                fwrite($file, 'Error de carga masiva en esta consulta:' . $hoy . $tempdata . ':' . $sqlInsertC . $valuesC . PHP_EOL);
                fclose($file);
                $this->mixScript('notify("Ocurrio un error en su carga masiva (5) : Error ' . $this->getErrno() . '","error",false);');
                return false;
                /******************* LOG DE CONSULTAS FALLIDAS************************************/
            }
        }
        $this->logtxt('3731', $this->getSql(), 'ACTUALIZAMOS EL PERIODO FECHA INICIAL-FINAL EN LA AFECTACION PAGADORA');
        $this->bitacora(69, 'ACTUALIZAMOS EL PERIODO FECHA INICIAL-FINAL EN LA AFECTACION PAGADORA');

        // Cuando SE TRATE de carga masiva de CLCs consolidadas, devengaremos en éste momento
        // ( cuando se trate de CLC de RH y sólo Afectaciones, devengaremos en otro momento )
        // ------------------------------------------------------------------------------------------
        if ($this->tipoCarga == '3') {

            $ip = (isset($_SESSION['ses_ip']) && $_SESSION['ses_ip'] != '::1') ? $_SESSION['ses_ip'] : '127.0.0.1';
            $user = $_SESSION['s18-id'];

            $this->setSql('
                SELECT DISTINCT clc_consolidada
                FROM scp_Clc
                WHERE tem_carga = "' . $this->prm->tem_carga . '";
            ');
            $this->consultar();

            while ($clc = $this->extraer()) {
                $conecta = new AccesoDatos();
                $conecta->conectar();
                /*EL  SP sp_DevengarConsolidada2014 no existe en ctrl2025*/
                $conecta->setSql(' CALL sp_DevengarConsolidada2014( ' . $clc['clc_consolidada'] . ', INET_ATON("' . $ip . '"), ' . $user . ' ); ');
                $conecta->desconectar();
            }
        }

        // Ya que terminamos de pasar las tablas temporales a las definitivas, limpiamos los campos
        // provisionales que usamos para la vinculación de temporal-definitivo
        // Insertamos en la tabla definitiva
        $this->setSql('
            UPDATE scp_Afectacion 
            SET afe_Temporal = 0
            WHERE tem_carga = ?;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->logtxt('3781', $this->getSql(), '');
        $this->setSql('
            UPDATE scp_Clc
            SET clc_consolidada = 0
            WHERE tem_carga = ?;
        ');

        reboot4059:
        if (!$this->consultar([$_SESSION[_S]['tem_carga']], false)) {
            if ($this->getErrno() == 1213) {
                sleep(3);
                goto reboot4059;
            }
        }
        $this->logtxt('3784', $this->getSql(), '');
        $this->setSql('SET @cargaMasivaEnProceso = NULL;');
        $this->consultar();
        // Terminamos y desconectamos
        $this->desconectar();
        $this->actualizaCarga(70, ' FIN DE CREAR AFECTACIONES');
        $datetime2 = new DateTime('now');
        $interval = date_diff($datetime1, $datetime2);
        $tiempo = ($interval->format('%i minutos %s segundos'));
        $this->bitacora(70, 'la creacion de Afectaciones tardo ' . $tiempo);
        return $registrados;
    }

    // Marcos: tratamos de meter todo lo que se pudiera dentro de una consulta.
    private function creaCompromisos()
    {
        $this->conectar();
        if ($this->getEstadoCarga($_SESSION[_S]['tem_carga']) != 70) {
            $this->bitacora(70, 'Error en el estado de la carga');
            return false;
        }
        $datetime1 = new DateTime('now');

        $this->actualizaCarga(65, 'CREAR COMPROMISOS');
        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Presupuestal 
            SET smn_id = null 
            WHERE tem_carga = ?
            AND smn_id = 0;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Presupuestal
            SET mno_id = null
            WHERE tem_carga = ?
            AND mno_id = 0;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);

        // =========================================================================================================
        // Insertamos los documentos en el Banco de Documentos
        // =========================================================================================================
        $this->setSql('
            INSERT INTO cat_BancoDocumentos( gpo_id, ur_id, bdo_referencia, bdo_concepto, rfc_id,  bdo_percepcionPresupuestal, bdo_retencionContable, bdo_percepcionContable, bdo_fechaDocumento, sts_id, bdo_estadoPago, bdo_estadoSat, bdo_Origen, tdf_id, tem_carga, usr_id )
            SELECT gpo_id, ur_id,bdo_referencia, nc.bdo_concepto, rfc_id, percepcionesPresupuestal, retencionesContables, percepcionesContables,fechaDocumento, sts, estadoPago, estadoSat, origen, tdf, "' . $_SESSION[_S]['tem_carga'] . '" tem_carga,' . $_SESSION[_S]['usr_id'] . '
            FROM(
                SELECT gpo_id, ur_id, clc_referencia AS bdo_referencia, ncp_concepto AS bdo_concepto, rfc_id, SUM( ncp_percepcionNeto ) AS percepcionesPresupuestal, 
                0 AS percepcionesContables, 0 AS retencionesContables,
                ncp_fechaReferencia AS fechaDocumento, 1 AS sts, 1 AS estadoPago, 3 AS estadoSat, 3 AS origen, "GRAL" AS tdf, tem_carga
                FROM ' . BD_TEMPORAL . '.tem_Presupuestal 
                WHERE tem_carga = "' . $_SESSION[_S]['tem_carga'] . '"
                GROUP BY gpo_id, ur_id, rfc_id, bdo_referencia
                UNION ALL
                SELECT gpo_id, ur_id, clc_referencia AS bdo_referencia, ncc_concepto AS bdo_concepto, rfc_id, 0 AS percepcionesPresupuestal, 
                SUM( ncc_percepcion ) AS percepcionesContables, SUM( ncc_retencion ) AS retencionesContables,
                ncc_fechaReferencia AS fechaDocumento, 1 AS sts, 1 AS estadoPago, 3 AS estadoSat, 3 AS origen, "GRAL" AS tdf, tem_carga
                FROM ' . BD_TEMPORAL . '.tem_Contable nc 
                WHERE tem_carga = "' . $_SESSION[_S]['tem_carga'] . '"
                GROUP BY gpo_id, ur_id, rfc_id, bdo_referencia
            )nc
            LEFT JOIN cat_BancoDocumentos bdo USING(gpo_id,ur_id,rfc_id,bdo_referencia)
            WHERE isnull(bdo.bdo_id)
            GROUP BY gpo_id, ur_id, rfc_id, bdo_referencia;
        ');

        $this->consultar();
        $this->bitacora(65, 'CARGA DOCUMENTOS-COMPROMISOS');

        // Y le ponemos dicho número definitivo en las tablas temporales
        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Presupuestal t 
            JOIN cat_BancoDocumentos s 
            ON ( t.gpo_id = s.gpo_id AND t.ur_id = s.ur_id AND t.clc_referencia = s.bdo_referencia AND t.tem_carga = s.tem_carga )
            SET t.bdo_id = s.bdo_id
            WHERE t.tem_carga = ?;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);

        $this->setSql('
            UPDATE ' . BD_TEMPORAL . '.tem_Presupuestal p
            JOIN (
                SELECT  gpo_id,ur_id,epg_id,pg_id,pe_id,ecf_id,tcp_id,rfc_id,cmp_importe ncp_percepcionNeto,tem_carga,cmp_id,cmp_numOrden clc_referencia
                FROM scp_Compromiso 
                WHERE tem_Carga = :tem_carga
            )c USING(gpo_id,ur_id,epg_id,pg_id,pe_id,ecf_id,tcp_id,rfc_id,clc_referencia,ncp_percepcionNeto,tem_carga)
            SET p.cmp_id = c.cmp_id
            WHERE tem_Carga = :tem_carga;
        ');
        $this->consultar([':tem_carga' => $_SESSION[_S]['tem_carga']]);

        // Determinamos cuántos compromisos insertamos
        $this->setSql('
            SELECT COUNT(*) AS total
            FROM scp_Compromiso
            WHERE tem_carga = ?;
        ');
        $this->consultar([$_SESSION[_S]['tem_carga']]);
        $row = $this->extraer();
        $nRegistros = $row['total'];

        // Terminamos y desconectamos
        $this->desconectar();
        $this->bitacora(65, ' FIN DE LA FUNCION DE COMPROMISOS');
        $this->actualizaCarga(66, 'FIN DE CREAR COMPROMISOS');
        $datetime2 = new DateTime('now');
        $interval = date_diff($datetime1, $datetime2);
        $tiempo = ($interval->format('%i minutos %s segundos'));
        $this->bitacora(66, 'la creacion de compromisos tardo ' . $tiempo);
        return $nRegistros;
    }

    private function muestraCarga()
    {
        $this->setContenedor('divContenedorGeneralModal');
        $this->setSql('
            SELECT gpo_id,ur_id,clc_id,bur_id,clc_percepcion,clc_retencion, clc_percepcion - clc_retencion importe,ecl_nombre,ecl_id
            FROM scp_Clc 
            JOIN cat_EstadoClc USING(ecl_id)
            WHERE tem_carga = ?
            ORDER BY gpo_id,ur_id,clc_id
        ');
        $this->conectar();
        $this->consultar([$this->prm->id]);
        $this->mixHtml('
            <div class="modal" id="modalNewCosteo">
                <div class="modal-dialog"  style="width: 95% !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close cerrar" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">Agregar Costeo</h4>
                        </div>
                        <div class="modal-body">
                            <p>
                                <table class="table table-bordered table-striped warning">
                                    <thead>
                                        <tr>
                                            <th>UR</th>
                                            <th>CLC</th>
                                            <th>CLABE INTERBANCARIA</th>
                                            <th>Percepciones</th>
                                            <th>Retenciones</th>
                                            <th>IMPORTE NETO</th>
                                            <th>Estado</th>
                                            <th>Imprimir</th>
                                            <th>Enviar a Sefin</th>
                                        </tr>
                                    </thead>
                                <tbody>
        ');
        while ($r = $this->extraer()) {
            $this->mixHtml('
                <tr>
                    <td>' . $r['gpo_id'] . $r['ur_id'] . '</td>
                    <td>' . $r['clc_id'] . '</td>
                    <td>' . $r['bur_id'] . '</td>
                    <td>$ ' . number_format($r['clc_percepcion'], 2) . '</td>
                    <td>$ ' . number_format($r['clc_retencion'], 2) . '</td>
                    <td>$ ' . number_format($r['importe'], 2) . '</td>
                    <td>' . $r['ecl_nombre'] . '</td>
            ');
            if ($r['ecl_id'] == 1) {
                $this->mixHtml('
                    <td></td>
                    <td>
                        <button class="btn btn-outline-secondary" id="envia' . $r['gpo_id'] . $r['ur_id'] . $r['clc_id'] . '">
                            <i class="fa fa-print"></i>
                        </button>
                    </td>
                ');
                $this->mixScript('
                    $("#envia' . $r['gpo_id'] . $r['ur_id'] . $r['clc_id'] . '").click(function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"enviaClc",
                            gpo_id:"' . $r['gpo_id'] . '",
                            ur_id:"' . $r['ur_id'] . '",
                            clc_id:"' . $r['clc_id'] . '",
                            id:"' . $this->prm->id . '"
                        });
                    });
                ');
            } else {
                $this->mixHtml('<td><button class="btn btn-outline-secondary" id="imprime' . $r['gpo_id'] . $r['ur_id'] . $r['clc_id'] . '"><i class="fa fa-print"></i></button></td><td></td>');
                $this->mixScript('
                    $("#imprime' . $r['gpo_id'] . $r['ur_id'] . $r['clc_id'] . '").click(function(){
                        var form = $("<form>")
                        .attr("action", "rep/doc/rep_clc.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "gpo_id").attr("value", "' . $r['gpo_id'] . '" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "ur_id").attr("value", "' . $r['ur_id'] . '" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "clc_id").attr("value", "' . $r['clc_id'] . '" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "tem_carga").attr("value", "' . $this->prm->id . '" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "claveOpcion").attr("value", "' . $this->prm->opcion->claveOpcion . '" ));
                        $("body").append(form);
                        form.submit();
                        form.remove();
                    });
                ');
            }
            $this->mixHtml('</tr>');
        }
        $this->desconectar();
        $this->mixHtml('
            </tbody>
                </table>
                    </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary cerrar" data-dismiss="modal">Cerrar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
            ');
        $this->mixScript('
            $("#modalNewCosteo").modal();
            $("#modalNewCosteo").on("hidden.bs.modal", function (e) { $("#modalNewCosteo").remove(); });
        ');
    }

    private function enviaClc()
    {
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $where = 'WHERE tem_carga = "' . $this->prm->id . '" ';
        $where .= (isset($this->prm->enviaTodas)) ? '' : 'AND gpo_id = ' . $this->prm->gpo_id . ' AND ur_id     = ' . $this->prm->ur_id . ' AND clc_id     = ' . $this->prm->clc_id . ' ';
        $this->setSql('
            UPDATE scp_Clc
            SET ecl_id = 2 ' . $where . '
            AND ecl_id = 1
        ');
        $this->conectar();
        $this->consultar();
        $this->setSql(
            '
            SELECT gpo_id,ur_id,clc_id,tcl_id,stc_id,ecf_id
            FROM scp_Clc ' . $where
        );
        $this->consultar();
        $con = new AccesoDatos();
        $con->conectar();

        $claves = claveFinRecursoFederal(true);
        $unidades = ['315', '802'];

        while ($r = $this->extraer()) {
            $clcPensiones = in_array($r['ecf_id'], $claves) /* La clave de financiamiento se encuentra en el arreglo */
                && !in_array($r['gpo_id'] . $r['ur_id'], $unidades) /* Y la UR no esta en el arreglo */
                && !($r['tcl_id'] == 'A' && $r['stc_id'] == 02) /* Y la CLC no es de subtipo 02 y tipo "A"*/
                && isset($_SESSION[_S]['prm']['S']['2']['2']['76']);/* Y tiene el permiso necesario */

            $clcNormal = !in_array($r['ecf_id'], $claves) /* La clave de financiamiento no se encuentra en el arreglo */
                || in_array($r['gpo_id'] . $r['ur_id'], $unidades) /* O la UR esta en el arreglo */
                || ($r['tcl_id'] == 'A' && $r['stc_id'] == 02); /* O el subtipo de la CLC es 02 y tipo "A"*/

            if ($clcPensiones || $clcNormal) {
                $con->setSql('
                    SET @usr_id = ?, 
                    @usr_ip = INET_ATON(?),
                    @bit_observacion="AUTORIZACION DE LA CLC CARGAMASIVA";
                ');
                $con->consultar([$_SESSION[_S]['usr_id'], $ip]);

                $con->setSql('
                    CALL sp_AutorizarCLC' . APP_EJERCICIO . '(?, ?, ?, INET_ATON(?), ?);
                ');
                $params = [
                    $r['gpo_id'],
                    $r['ur_id'],
                    $r['clc_id'],
                    $ip,
                    $_SESSION[_S]['usr_id'],
                ];
                $con->consultar($params);

                if ($clcPensiones) {
                    $con->setSql('
                        UPDATE scp_Clc
                        SET ecl_id = 12
                        WHERE  gpo_id = ?
                        AND ur_id = ?
                        AND clc_id = ?;
                    ');
                    $params = [
                        $r['gpo_id'],
                        $r['ur_id'],
                        $r['clc_id'],
                    ];
                    $con->consultar($params);

                    $con->setSql('
                        SET @usr_id = ?,
                        @usr_ip = INET_ATON(?),
                        @bit_observacion="AUTORIZACION DE LA CLC CARGAMASIVA";
                    ');
                    $con->consultar([$_SESSION[_S]['usr_id'], $ip]);

                    $con->setSql('
                        CALL sp_AutorizarCLC' . APP_EJERCICIO . '(?, ?, ?, INET_ATON(?), ?);
                    ');
                    $params = [
                        $r['gpo_id'],
                        $r['ur_id'],
                        $r['clc_id'],
                        $ip,
                        $_SESSION[_S]['usr_id'],
                    ];
                    $con->consultar($params);

                    $this->setSql('
                        INSERT INTO bit_Clc(gpo_id,ur_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                        VALUES (?, ?, ?, 12,"PROCESO DE CLC POR EL RECURSO FEDERAL (carga masiva)", INET_ATON(?), ?,NOW());
                    ');
                    $params = [
                        $r['gpo_id'],
                        $r['ur_id'],
                        $r['clc_id'],
                        $ip,
                        $_SESSION[_S]['usr_id'],
                    ];
                    $this->consultar($params);    /*Se ejecuta el ministrado de la CLC */

                    $this->setSql('
                        SET @usr_id = ?,
                        @usr_ip = INET_ATON(?),
                        @bit_observacion="MINISTRAR DESDE CARGA MASIVA";
                    ');
                    $this->consultar([$_SESSION[_S]['usr_id'], $ip]);

                    $this->setSql('
                        CALL sp_Ministrar' . APP_EJERCICIO . '(?, ?, ?, "RECURSO FEDERAL", NOW(),INET_ATON(?), ?);
                    ');
                    $params = [
                        $r['gpo_id'],
                        $r['ur_id'],
                        $r['clc_id'],
                        $ip,
                        $_SESSION[_S]['usr_id'],
                    ];
                    $this->consultar($params);
                }
            }
        }
        $con->desconectar();
        $this->desconectar();
        if (isset($this->prm->enviaTodas)) {
            $this->mixScript('notify("CLC\' de la Carga ' . $this->prm->id . ' han sido enviadas a la SEFIN","success");');
        } else {
            $this->mixScript('notify("CLC ' . $this->prm->clc_id . ' de la UR ' . $this->prm->gpo_id . $this->prm->ur_id . ' ha sido enviada a la SEFIN","success");');
        }
        $this->muestraCarga();
    }

    private function temCargaValido($temCarga)
    {
        list($ur, $fecha, $usr_id, $hora) = explode('-', $temCarga);
        #si el id del usuario recuperado de la tem_carga es igual al id de sesion

        if (((int) $usr_id === (int) $_SESSION[_S]['usr_id'])
            #si la UR recuperado de la tem_carga es igual a la UR de sesion
            && (!empty($ur) && ((int) $ur == (int) $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id']))
            #si la FECHA recuperado de la tem_carga es una fecha valida
            && (!empty($fecha) && checkdate((int) substr($fecha, 2, 2), (int) substr($fecha, 4, 2), (int) substr($fecha, 0, 2)))
        ) {

            return true;
        } else {

            return false;
        }
    }

    public function resCarga()
    {
        $gpo_id = $_SESSION[_S]['gpo_id'];
        $ur_id = $_SESSION[_S]['ur_id'];
        $carga = $_SESSION[_S]['tem_carga'];
        $detalleCarga = explode('-', $carga);
        $usr_id = $_SESSION[_S]['usr_id'];
        $nivelUsuario = $_SESSION[_S]['nvj_id'];

        if (count($detalleCarga) != 4) {
            $this->mixScript(' notify("Error en el Identificador de carga","warning");');
            return false;
        }
        if ($gpo_id . $ur_id != $detalleCarga[0] && $nivelUsuario < 3) {
            $this->mixScript('notify("E2:La carga ' . $carga . ' no corresponde a su UR","error");');
            return false;
        }
        if ($usr_id != $detalleCarga[2] && $nivelUsuario < 3) {
            $this->mixScript('notify("E1:EL usuario actual no es propietario de la Carga Masiva ' . $carga . ' ","warning");');
            return false;
        }

        if (!$this->model->cargaExiste($carga)) {
            $this->mixScript('notify("E3: La carga  ' . $carga . ' no existe","warning");');
            return false;
        }

        $obs = new StdClass();
        $obs->id = 'bit_observacion';
        $obs->label = 'ERROR GENERADO POR LA CARGA';
        $obs->enDiv = true;
        $obs->placeholder = 'MOTIVO DE LIBERACI&Oacute;N';

        $btnLibera = new stdClass();
        $btnLibera->value = 'Liberar Registros';
        $btnLibera->id = 'btn_libera';
        $btnLibera->type = 'button';
        $btnLibera->diabled = 'disabled';
        $btnLibera->class = 'btn btn-test ';
        $btnLibera->icon = 'glyphicon glyphicon-hand-search';

        $btnLibera->click = ' 
            observa = $("#bit_observacion").val();
            if( observa ==""){
                notify("La observacion es necesaria","warning");
                return false;
            }
            if( observa.length < 20 ){
                notify("La observacion es muy corta","warning");
                return false;
            }
            comunica("' . $this->pantalla . '",{
                metodo:"sp_libera",
                observacion:observa
            });
        ';

        $btnLibera = $this->escritura == true ? $this->ponBoton($btnLibera)
            : '<div class="danger">Sin permiso de Escritura</div>';
        $html = '';

        /*  INFORMACIÓN DE LA CARGA MASIVA  */
        $datosCarga = $this->model->getInfoCarga($carga);

        if ($datosCarga['registros'] == 0) {
            $this->setContenedor('div_acciones');
            $html = $this->vista->fn_validaciones(4);
            $this->mixHtml($html);
            $this->mixScript('notify("La carga ' . $carga . ' no existe","error");$("#analiza_res").html("");');
            return;
        }

        $html = $this->vista->fn_pintaTabla($datosCarga);
        $estadoCarga = $datosCarga['datos'][0]['Estado'];
        $registros = $datosCarga['datos'][0]['compromisos'];
        $registros = $registros + $datosCarga['datos'][0]['afectaciones'];
        $registros = $registros + $datosCarga['datos'][0]['clc'];
        $registros = intval($registros);

        /* DETALLE DE LA CARGA EN PESTANAS (CLC / Afectaciones / Compromisos), mismo diseno que
           el modal de la lista de rechazo. La pestana CLC solo aparece si la carga trae CLC.
           El llenado de cada tabla lo resuelve ControladorRechazaCargaMasiva::llenaDetalleRegistros. */
        $esClc = intval($datosCarga['datos'][0]['clc']) > 0;
        $html .= $this->tabsDetalleCarga($carga, $esClc);
        $this->mixHtml($html);

        $this->setContenedor('div_acciones');
        $html = '';

        if ($nivelUsuario < 2 && $usr_id != $detalleCarga[2]) {
            $this->mixHtml('<div class="warning">
                    E2:EL usuario no es propietario de la carga</div>
                ');
        } elseif ($nivelUsuario > 2 && !$this->escritura) {
            $html = $this->vista->fn_validaciones(1);
        } elseif ($estadoCarga == 'Completada') {
            $html = $this->vista->fn_validaciones(2);
        } else {
            $html = '<fieldset class="col-md-8">' .
                $this->ponInput($obs) .
                '</fieldset><fieldset class="col-md-4"><br>' .
                $btnLibera .
                '</fieldset>';
            $_SESSION[_S]['id_carga'] = $carga;
        }

        $this->mixHtml($html);
        // Dispara el llenado de las tablas del detalle (solo las que aplican).
        $this->mixScript($this->scriptTablasDetalle($esClc));
    }

    /**
     * Detalle de la carga en pestanas (CLC / Afectaciones / Compromisos) con wrPonTabla,
     * mismo diseno que el modal de la lista de rechazo. La pestana CLC solo se incluye cuando
     * la carga trae CLC ($esClc). El llenado de cada tabla lo resuelve
     * ControladorRechazaCargaMasiva::llenaDetalleRegistros (dispatchable via comunica).
     */
    private function tabsDetalleCarga($carga, $esClc)
    {
        $tAfe = $this->wrShellTablaDetalle('tbl_det_afe', 'afe', $carga, array(
            'UR' => 'UR', 'Tipo' => 'Tipo', 'Subtipo' => 'Subtipo', 'Numero' => 'Numero',
            'Percepción' => 'Percepcion', 'Retención' => 'Retencion',
            'Estado' => 'Estado'
        ), array('Percepcion', 'Retencion'));
        $tCmp = $this->wrShellTablaDetalle('tbl_det_cmp', 'cmp', $carga, array(
            'UR' => 'UR', 'Numero' => 'Numero', 'Importe' => 'Importe',
            'Clave Presupuestal' => 'ClavePresupuestal', 'Estado' => 'Estado'
        ), array('Importe'));

        $navClc = '';
        $paneClc = '';
        if ($esClc) {
            $tClc = $this->wrShellTablaDetalle('tbl_det_clc', 'clc', $carga, array(
                'UR' => 'UR', 'Tipo' => 'Tipo', 'Subtipo' => 'Subtipo',
                'Numero' => 'Numero', 'Concepto' => 'Concepto',
                'Percepción' => 'Percepcion', 'Retención' => 'Retencion',
                'Estado' => 'Estado'
            ), array('Percepcion', 'Retencion'));
            $navClc = '
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-clc" data-bs-toggle="tab" data-bs-target="#pane-clc" type="button" role="tab">CLC</button>
                </li>';
            $paneClc = '<div class="tab-pane fade show active" id="pane-clc" role="tabpanel" style="max-height:60vh;overflow:auto">' . $tClc . '</div>';
        }

        // Si no hay CLC, la pestana activa por defecto es Afectaciones.
        $activaAfe = $esClc ? '' : ' active';
        $paneActivaAfe = $esClc ? '' : ' show active';

        return '
            <style>td.Percepcion, td.Retencion, td.Importe { text-align: right; }</style>
            <ul class="nav nav-tabs" id="detalleTab" role="tablist">
                ' . $navClc . '
                <li class="nav-item" role="presentation">
                    <button class="nav-link' . $activaAfe . '" id="tab-afe" data-bs-toggle="tab" data-bs-target="#pane-afe" type="button" role="tab">Afectaciones</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-cmp" data-bs-toggle="tab" data-bs-target="#pane-cmp" type="button" role="tab">Compromisos</button>
                </li>
            </ul>
            <div class="tab-content pt-3">
                ' . $paneClc . '
                <div class="tab-pane fade' . $paneActivaAfe . '" id="pane-afe" role="tabpanel" style="max-height:60vh;overflow:auto">' . $tAfe . '</div>
                <div class="tab-pane fade" id="pane-cmp" role="tabpanel" style="max-height:60vh;overflow:auto">' . $tCmp . '</div>
            </div>';
    }

    /**
     * JS que dispara la carga de las tablas del detalle (solo las que aplican).
     */
    private function scriptTablasDetalle($esClc)
    {
        return ($esClc ? $this->wrLlamaLlenaTabla('tbl_det_clc') : '') . '
            ' . $this->wrLlamaLlenaTabla('tbl_det_afe') . '
            ' . $this->wrLlamaLlenaTabla('tbl_det_cmp');
    }

    /**
     * Shell de una wrPonTabla del detalle. El llenado apunta a RechazaCargaMasiva, que es
     * donde vive llenaDetalleRegistros. $cols: [titulo => campoTxt]; $derecha: campos alineados a la derecha.
     */
    private function wrShellTablaDetalle($id, $tipo, $carga, $cols, $derecha = array())
    {
        $tabla = new stdClass();
        $tabla->id = $id;
        $tabla->cabeceraFija = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'scp/afectaciones/ControladorRechazaCargaMasiva.php';
        $tabla->comunica->metodo = 'llenaDetalleRegistros';
        $tabla->comunica->tipo = $tipo;
        $tabla->comunica->carga = $carga;
        $tabla->filasAmostrar = array(10, 50, 100, 250, 500);
        $tabla->numFilas = 50;

        $tr = array();
        $trbody = array();
        foreach ($cols as $titulo => $campo) {
            $th = new stdClass();
            $th->texto = $titulo;
            // El plugin de tabla alinea el encabezado con th.align (text-align).
            if (in_array($campo, $derecha)) {
                $th->align = 'right';
            }
            array_push($tr, $th);

            $td = new stdClass();
            $td->campoTxt = $campo;
            array_push($trbody, $td);
        }
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        return $this->wrPonTabla($tabla);
    }
}
//Verifica si el usuario tiene los permisos
if (isset($_SESSION[_S]['prm']['S']['2']['1']['1']) || isset($_SESSION[_S]['prm']['S']['9']['1']['23'])) {
    new ControladorCargaMasiva();
} else {
    WebResponse::sinPermisos();
}
