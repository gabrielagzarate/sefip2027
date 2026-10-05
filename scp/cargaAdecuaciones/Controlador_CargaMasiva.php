<?php
/**
 * Description of ControladorRegistroModificaciones
 *
 * @author ?
 * Collaborators: Adriana Pacheco Castillo <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 12-Dic-2018
 * Comment: -----
 * Objective:
 *Registrar movimientos que contendra un folio por medio del excel
 *tomando en cuenta que el folio debe de existir antes de relizar la carga
 * Regla de Negocio: -----
 * Permits: Permisos para carga masiva.
 **/
require_once dirname(__FILE__) . '/../../ent/Usuario.php';
session_start();

ini_set('max_execution_time', 600); //300 seconds = 5 minutes
ini_set('post_max_size', '14000M');
ini_set('memory_limit', '14000M');
ini_set('upload_max_filesize', '14000M');
ini_set("display_errors", 1);
set_time_limit(0);
error_reporting(E_ALL);

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/scp/modelAdecuaciones.php';
require_once dirname(__FILE__) . "/../../lib/scp/vistaAdecuaciones.php";
require_once dirname(__FILE__) . '/../../lib/validaCargaAdecuaciones.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/scp/MaestroModificacion.php';
require_once dirname(__FILE__) . '/../../ent/scp/OrigenOperacion.php';
require_once dirname(__FILE__) . '/../../ent/scp/TipoModificacion.php';
require_once dirname(__FILE__) . '/../../ent/scp/sp_CargaDetalleModificacion2025.php';

class Controlador_CargaMasiva extends WebResponse
{
    private $fecha_actual;
    public $model;
    private $vista;
    private $usuario_id;
    public $nivelJerarquico;
    private $ip;
    public $grupo;
    public $s19_ur;
    private $adm_grupo;
    private $adm_ur;
    private $omo_id;
    private $mod_gpo_id;
    private $mod_ur_id;
    private $mod_id;
    private $subSecretaria;
    public $presupuesto;
    public $planeacion;
    public $cargaMasiva;
    private $nivelRevision;
    private $usr_id;

    public function __construct()
    {
        parent::__construct();
        $this->model = new modelAdecuaciones();
        $this->vista = new vistaAdecuaciones();

        $this->fecha_actual = date("Y-m-d");
        $this->usuario_id = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : false;
        $this->nivelJerarquico = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : false;
        $this->ip = isset($_SESSION[_S]['ses-ip']) ? $_SESSION[_S]['ses-ip'] : "";
        $this->grupo = isset($_SESSION[_S]['gpo_id']) ? $_SESSION[_S]['gpo_id'] : false;
        $this->s19_ur = isset($_SESSION[_S]['ur_id']) ? $_SESSION[_S]['ur_id'] : false;

        $this->adm_grupo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : false;
        $this->adm_ur = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : false;

        $this->omo_id = (isset($_POST['omo_id']) ? $_POST['omo_id'] : '');
        $this->mod_gpo_id = (isset($_POST['mod_gpo_id']) ? $_POST['mod_gpo_id'] : '');
        $this->mod_ur_id = (isset($_POST['mod_ur_id']) ? $_POST['mod_ur_id'] : '');
        $this->mod_id = (isset($_POST['mod_id']) ? $_POST['mod_id'] : '');
        $this->subSecretaria = isset($_SESSION[_S]['ssc_id']) ? $_SESSION[_S]['ssc_id'] : false;

        $this->presupuesto = ($this->subSecretaria != false && $this->subSecretaria == 3 ? true : false);
        $this->planeacion = ($this->subSecretaria != false && $this->subSecretaria == 2 ? true : false);

        $opcion = (isset($this->prm->claveOpcion)) ? $this->prm->claveOpcion : $this->prm->opcion->claveOpcion;
        $this->cargaMasiva = (($opcion == 'S_1_1' && isset($_SESSION[_S]['prm']['S']['1']['1']['23'])));
        $this->nivelRevision = (isset($this->prm->nivelRevision) && $this->prm->nivelRevision != '' ? $this->prm->nivelRevision : "");

        $this->usr_id = new Usuario();
        $this->usr_id->setUsr_id($this->usuario_id);
        $this->usr_id->select();

        switch ($this->metodo) {
            case "stepTransfer":
                $this->stepTransfer();
                break;
            case "prevCarga":
                $this->prevCarga();
                break;
            case "validarDatosCargaMasiva":
                $this->validarDatosCargaMasiva();
                break;
            case 'registrarModificacionesMasivas':
                $this->registrarModificacionesMasivas();
                break;
            case 'responseRevisaDatos':
                $this->responseRevisaDatos();
                break;
            case 'finalize':
                $this->finalize();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    private function main()
    {
        $tem_carga = '';
        $tabla = new stdClass();
        $mod = new MaestroModificacion($this->prm);
        $html = $this->vista->vwInformacionFolio($mod);
        $tabla->tools = $html;
        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $html . '</div>');
        $existe_error = $this->cierre_inesperado();
        if ($existe_error) {
            $this->mixScript('
                notify("La última carga masiva con id ' . $existe_error['tem_carga'] . ' está en proceso favor de terminar el proceso para continuar","error");
            ');

            $boton = new stdClass();
            $boton->id = 'btnFinalizar';
            $boton->title = 'Finalizar Proceso';
            $boton->value = ' Finalizar Proceso';
            $boton->icon = 'fa fa-right-left me-2';
            $boton->class = 'btn btn-outline-secondary';
            $boton->click = '
                comunica("' . $this->pantalla . '",{
                    metodo:"finalize",
                    crm_id:' . $existe_error['crm_id'] . ',
                    omo_id:"' . $this->prm->omo_id . '",
                    mod_gpo_id:' . $this->prm->mod_gpo_id . ',
                    mod_ur_id:"' . $this->prm->mod_ur_id . '",
                    mod_id:' . $this->prm->mod_id . ',
                    nivelRevision:"' . $this->nivelRevision . '",
                    tem_carga:"' . $tem_carga . '"
                });
            ';

            $this->agregaTools($this->ponBoton($boton) . ' ');
        } else {
            $cargaStd = $this->getAllEstadoCarga($this->fecha_actual);
            if (!$cargaStd) {
                unset($_SESSION[_S]['tem_carga']);
                unset($_SESSION[_S]['estadocarga']);

                $boton = new stdClass();
                $boton->id = 'btnRegresar';
                $boton->title = 'Regresar a Afectaciones';
                $boton->value = ' Regresar';
                $boton->class = 'btn btn-outline-secondary me-1';
                $boton->icon = 'fa-solid fa-circle-left';
                $boton->click = '
                    if($("#btnValidarArchivo").length || $("#btnRegistrarArchivos").length || $("#btnCargar").length ){
                        notify("Carga masiva en proceso, favor de terminar la carga","error");
                        return true;
                    } else {
                        $("div[aria-describedby=cargaDatosProgFin]").html("");
                        event.stopPropagation();
                        comunica("scp/adecuaciones/ControladorRegistroModificaciones.php",{
                            metodo:"tablaModificaciones",
                            ecf_id:"' . $mod->getEcf_id() . '",
                            mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                            mod_ur_id:"' . $mod->getMod_ur_id() . '",
                            omo_id:"' . $mod->getOmo_id() . '",
                            mod_id:' . $mod->getMod_id() . ',
                            enc_oop:"' . $mod->getOop() . '",
                            cambiaUR:true
                            ' . ($this->nivelRevision != '' ?
                            ',nivelRevision:"' . $this->nivelRevision . '"' : "") . '
                        });
                        return false;
                    }
                ';
                $this->agregaTools($this->ponBoton($boton) . ' ');

                $boton = new stdClass();
                $boton->id = 'btnFormato';
                $boton->title = 'Descargar Formato para Carga Masiva';
                $boton->color = 'verde';
                $boton->style = 'font-size: 15px;';
                $boton->icon = 'fa fa-download';
                $boton->class = "btn btn-outline-success me-1";
                $this->agregaTools('<a href="archivos/formatos/formatoModificaciones.zip" target="_blank">' . $this->ponBoton($boton) . '</a>');

                $botonF = new stdClass();
                $botonF->id = 'btnFinalizar';
                $botonF->title = 'Finalizar Proceso';
                $botonF->value = ' Finalizar Proceso';
                $botonF->icon = 'fa fa-right-left me-2';
                $botonF->color = 'gris';

                $this->agregaTools($this->ponBoton($botonF) . ' ');
                $this->mixScript('$("#btnFinalizar").hide();');
                $this->btnFile();
            } else {
                $this->mixScript('
                    notify("Existe una carga masiva en proceso, favor de reintentar en unos minutos","error");
                ');
            }
        }
    }

    private function btnFile()
    {
        $mod = new MaestroModificacion($this->prm);
        $this->mixHtml('
            <ul class="list-group" id="listaCarga">
                <li class="list-group-item warning" style="background:#ffffff">
                    <h4>
                        <!-- START CARGA MASIVA -->
                        <dd class="file-js">
                        <div class="box">
                            <form mehtod="post" id="export_excel">
                                <input type="hidden" name="type_carga" id="type_carga" value="ADECUACIONES" />
                                <input type="file" name="file-1" id="file-1" class="inputCargaMasiva inputCargaMasiva-1"  />
                                <label for="file-1"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/></svg> <span>Seleccionar archivo&hellip;</span></label>
                            </form>
                        </div>
                        <div id="button_txt"> </div>
                        <div id="loading_file" class="parent_loading">
                            <img  src="img/loading_xls.gif" class="child_loading" alt="loading" />
                        </div>
                        </dd>
                        <!-- END CARGA MASIVA -->
                    </h4>
                </li>
                <li class="list-group-item" id="botonesCarga"></li>
            </ul>
            <div class="row" id="tablaCarga"></div>
            <div class="row" id="resCarga"></div>
        ');

        $this->mixScript('
            // START CARGA MASIVA
            (function(e,t,n){var r=e.querySelectorAll("dd")[0];r.className=r.className.replace(/(^|\s)file-js(\s|$)/,"$1js$2")})(document,window,0);
            $("#loading_file").hide();
            $("#file-1").change(function(){
                comunica("' . $this->pantalla . '",{metodo:"prevCarga" ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : "") . '});
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
                        console.log(parsed);
                        if(parsed.success != undefined){
                            type = "success";
                            message = parsed.success;
                            comunica("' . $this->pantalla . '",{
                                metodo:"stepTransfer",
                                file_tmp:parsed.xls,
                                json_xls:parsed.json,
                                omo_id:"' . $this->omo_id . '",
                                mod_gpo_id   :"' . $this->mod_gpo_id . '",
                                mod_ur_id:"' . $this->mod_ur_id . '",
                                mod_id :"' . $this->mod_id . '"
                                ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : "") . '
                            });
                        } else if (parsed.errores != undefined) {
                            message = parsed.errores;
                            var d = new Date();
                            var month = "' . date('m') . '";
                            var ur = "' . $this->adm_grupo . $this->adm_ur . '";
                            if(parsed.txt != undefined){
                                $(\'#button_txt\').html("<a class=\'btn btn-outline-danger\' href=\'archivos/failedCargaMasiva/adecuaciones/"+month+"/"+ur+"/"+parsed.txt+".txt\' id=\'download_file\' download=\'archivos/failedCargaMasiva/adecuaciones/"+month+"/"+ur+"/"+parsed.txt+".txt\'>Descargar archivo</a>");
                            }
                        } else {
                            message = "Ocurrio un error al retornar respuesta, intente de nuevo.";
                        }
                        $("#loading_file").hide();
                        if (type != "success") {
                            notify(message,type);
                            $("#export_excel").hide();
                            $("#button_txt").click(function(){
                                notify("Se procedera a cancelar la carga masiva actual","error");
                                comunica("scp/adecuaciones/ControladorRegistroModificaciones.php",{
                                    metodo:"tablaModificaciones",
                                    ecf_id:"' . $mod->getEcf_id() . '",
                                    mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                                    mod_ur_id:"' . $mod->getMod_ur_id() . '",
                                    omo_id:"' . $mod->getOmo_id() . '",
                                    mod_id:' . $mod->getMod_id() . ',
                                    enc_oop:"' . $mod->getOop() . '",
                                    cambiaUR:true
                                    ' . ($this->nivelRevision != '' ?
                                    ',nivelRevision:"' . $this->nivelRevision . '"' : "") . '
                                });
                            });
                        }
                        else{
                            notify("Se completo el análisis del archivo",type);
                        }
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

    private function prevCarga()
    {
        if (isset($_SESSION[_S]['tem_carga']) && $_SESSION[_S]['tem_carga'] != "") {
            $this->mixScript('
                notify("Hay una carga masiva en proceso","error");
            ');
            $this->actualizaCargaMasiva(94, "ERROR AL CARGAR LOS DATOS");
            return false;
        }
        $this->mixScript('
            $("#button_txt").html("");
            $("#export_excel").submit();
            $(\'#file-1\').attr("disabled", "disabled");
        ');
    }

    private function stepTransfer()
    {
        $mod = new MaestroModificacion($this->prm);
        $file_name = explode(".", $this->prm->file_tmp);
        $ext = $file_name[1];
        $datetime1 = new DateTime('now');

        $_SESSION[_S]['tem_carga'] = $file_name[0];
        $_SESSION[_S]['tem_usr_ip'] = ($_SERVER['REMOTE_ADDR'] == "::1" ? "127.0.0.1" : $_SERVER['REMOTE_ADDR']);

        $this->conectar();

        $sql = '
            SELECT crm_id FROM  cat_CargaMasiva
            WHERE gpo_id = ?
            AND ur_id = ?
            AND tcr_id= ?
            AND tem_carga= ?
            AND usr_id= ?
        ';

        $parametros = [
            $this->adm_grupo,
            $this->adm_ur,
            3,
            $_SESSION[_S]['tem_carga'],
            $this->usuario_id
        ];

        $this->setSql($sql);
        $this->consultar($parametros);
        $this->desconectar();
        $resp = $this->extraer();
        $_SESSION[_S]['tem_carga_id'] = $resp['crm_id'];

        $boton = new stdClass();
        $boton->id = 'btnCargar';
        $boton->title = 'Cargar en Temporal';
        $boton->value = 'Cargar';
        $boton->icon = 'upload me-2';
        $boton->class = 'btn btn-outline-success';
        $boton->click = '
            comunica("scp/cargaAdecuaciones/Controlador_CargaMasiva.php",{
                metodo:"responseRevisaDatos",
                json_xls:' . json_encode($this->prm->json_xls) . ',
                omo_id:"' . $_POST["omo_id"] . '",
                mod_gpo_id:' . $_POST["mod_gpo_id"] . ',
                mod_ur_id:"' . $this->prm->mod_ur_id . '",
                mod_id:' . $this->prm->mod_id . ',
                tem_carga:"' . $_SESSION[_S]['tem_carga'] . '"
                ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : "") . '
            });
        ';

        $this->setContenedor('botonesCarga');
        $this->mixHtml($this->ponBoton($boton));
        $this->mixScript('
            $("#export_excel").hide();
            $("#button_txt").click(function(){
                notify("Se procedera a cancelar la carga masiva actual","error");
                comunica("scp/adecuaciones/ControladorRegistroModificaciones.php",{
                    metodo:"tablaModificaciones",
                    ecf_id:"' . $mod->getEcf_id() . '",
                    mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                    mod_ur_id:"' . $mod->getMod_ur_id() . '",
                    omo_id:"' . $mod->getOmo_id() . '",
                    mod_id:' . $mod->getMod_id() . ',
                    enc_oop:"' . $mod->getOop() . '",
                    cambiaUR:true
                    ' . ($this->nivelRevision != '' ?
                    ',nivelRevision:"' . $this->nivelRevision . '"' : "") . '
                });
            });
        ');
    }

    public function responseRevisaDatos()
    {
        $datetime1 = new DateTime('now');
        $exporta = new validaCargaAdecuaciones;
        $respuesta = $exporta->validaDatosExcel($this->prm->json_xls, $_SESSION[_S]['tem_carga']);
        $this->mixScript('
            $("#btnCargar").remove();
        ');

        if ($respuesta) {
            $this->failedDirectory($_SESSION[_S]['tem_carga'], $respuesta);
            $this->actualizaCargaMasiva(95, "ERROR EN LA INFORMACIÓN");
        } else {

            $boton = new stdClass();
            $boton->id = 'btnValidarArchivo';
            $boton->title = 'Valida Datos';
            $boton->value = 'Validar';
            $boton->icon = 'fa fa-check me-2';
            $boton->class = 'btn btn-outline-success';
            $boton->click = '
                $(this).attr("disabled","disabled").css("background-color","#348fe2").css("border-color","#858585");
                comunica("' . $this->pantalla . '",{
                    metodo:"validarDatosCargaMasiva",
                    omo_id:"' . $_POST["omo_id"] . '",
                    mod_gpo_id:' . $_POST["mod_gpo_id"] . ',
                    mod_ur_id:"' . $this->prm->mod_ur_id . '",
                    mod_id:' . $this->prm->mod_id . ',
                    tem_carga:"' . $_SESSION[_S]['tem_carga'] . '"
                    ' . (isset($this->prm->nivelRevision) ? ',nivelRevision:"' . $this->prm->nivelRevision . '"' : "") . '
                });
            ';
            $this->setContenedor('botonesCarga');
            $this->mixHtml($this->ponBoton($boton));
            $this->mixScript('notify("Se completo la validación de la carga","success");');

            $datetime2 = new DateTime('now');
            $interval = date_diff($datetime1, $datetime2);
            $tiempo = ($interval->format('%i minutos %s segundos'));
            $this->bitacora(2, "La Carga del Archivo tardo " . $tiempo);
        }
    }

    private function validarDatosCargaMasiva()
    {
        $fechaInicio = "";
        $fechaFinal = "";

        $this->conectar();
        # mejora para usar tabla bd 2024 carga masiva sin restrinciones presupuesto
        $sql = '
            SELECT exa_fechaInicio,exa_fechaFin,exa_usr
            FROM '.BD_CTRL.'.scp_ExcepcionCargaAdecuacion
            WHERE now() between exa_fechaInicio and exa_fechaFin
            and exa_usr = ?
            LIMIT 1;
        ';
        $this->setSql($sql);
        $this->consultar([$this->usuario_id]);
        $this->desconectar();
        $permiso_mod = $this->extraer();

        if ($this->getNumeroRegistros() > 0) {
            $fechaInicio = $permiso_mod['exa_fechaInicio'];
            $fechaFinal = $permiso_mod['exa_fechaFin'];
        }

        $casoB = 0;
        $tem_carga = $this->prm->tem_carga;

        $mod = new MaestroModificacion($this->prm);
        $exporta = new validaCargaAdecuaciones;

        switch ($this->prm->omo_id) {
            case 'A':
            case 'C':
            case 'D':
            case 'E':
            case 'F': //se realiza la validacion de claves existentes en otro folio unicamente para estos origenes, para el B-SEFIN EGRESOS se excluye.
                $casoB = 1;
                break;
            case 'B':
                # mejora para usar tabla bd 2024 carga masiva sin restrinciones presupuesto
                if ($fechaInicio <= $fechaFinal) {
                    $casoB = 0;
                } else {
                    $casoB = 1;
                }

                break;
        }

        $result = $exporta->validaDatosCA($casoB, $mod, $tem_carga);

        if ($result == "success") {
            $this->actualizaCargaMasiva(61, "TERMINA VALIDACIÓN");
            $this->mixScript('
                notify("Datos registrados correctamente en temporal","success");
                $("#btnValidarArchivo").remove();
            ');
            $boton = new stdClass();
            $boton->id = 'btnRegistrarArchivos';
            $boton->title = 'Registrar Movimientos';
            $boton->value = 'Registrar';
            $boton->icon = 'fa fa-plus me-2';
            $boton->class = 'btn btn-outline-primary';
            $boton->click = '
                $(this).attr("disabled","disabled").css("background-color","#348fe2").css("border-color","#858585");
                comunica("' . $this->pantalla . '",{
                    metodo:"registrarModificacionesMasivas",
                    omo_id:"' . $this->prm->omo_id . '",
                    mod_gpo_id:' . $this->prm->mod_gpo_id . ',
                    mod_ur_id:"' . $this->prm->mod_ur_id . '",
                    mod_id:' . $this->prm->mod_id . ',
                    tem_carga:"' . $tem_carga . '"
                    ' . ($this->nivelRevision != '' ?
                    ',nivelRevision:"' . $this->nivelRevision . '"' : "") . '
                });
            ';
            $this->mixHtml($this->ponBoton($boton));
            $this->mixScript('$(".datoCarga").remove();');
        } else { //existe errores
            $this->btnFile();
            $this->setContenedor('botonesCarga');
            $this->failedDirectory($_SESSION[_S]['tem_carga'], $result);
            $this->mixScript('
                $("#btnValidarArchivo").remove();
            ');
            $this->actualizaCargaMasiva(95, "ERROR EN LA INFORMACIÓN");
        }
    }

    public function registrarModificacionesMasivas()
    {
        $mod = new MaestroModificacion($this->prm);

        $this->actualizaCargaMasiva(62, "CARGA EN DEFINITIVA");

        $ip = ($this->ip != "" && $this->ip != "::1") ? $this->ip : '127.0.0.1';
        $storeProcedure = new sp_CargaDetalleModificacion();
        $storeProcedure->setOmo_id($mod->getOmo_id());
        $storeProcedure->setMod_gpo_id($mod->getMod_gpo_id());
        $storeProcedure->setMod_ur_id($mod->getMod_ur_id());
        $storeProcedure->setMod_id($mod->getMod_id());
        $storeProcedure->setTem_carga($this->prm->tem_carga);
        $storeProcedure->setTcr_id(3);
        $storeProcedure->setSis_id('SCP');
        $storeProcedure->setBit_observacion('PANTALLA S_1_1');
        $storeProcedure->setEjercicio(APP_EJERCICIO);
        $r = $storeProcedure->execute();

        if (!$r->bandera && !is_null($r->bandera)) {
            $this->mixScript(' notify("ERROR ' . $r->mensaje . '","error",false);');
            $this->actualizaCargaMasiva(99, "CARGA MASIVA FALLIDA");
            $this->btnFile();
            $this->setContenedor('botonesCarga');
            $this->failedDirectory($_SESSION[_S]['tem_carga'], $r->mensaje);
        } else {
            $this->mixScript('
                notify("' . $r->mensaje . '","success");
                comunica("scp/adecuaciones/ControladorRegistroModificaciones.php",{
                    metodo:"tablaModificaciones",
                    ecf_id:"' . $mod->getEcf_id() . '",
                    mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                    mod_ur_id:"' . $mod->getMod_ur_id() . '",
                    omo_id:"' . $mod->getOmo_id() . '",
                    mod_id:' . $mod->getMod_id() . ',
                    enc_oop:"' . $mod->getOop() . '"
                    ' . ($this->nivelRevision != '' ?
                    ',nivelRevision:"' . $this->nivelRevision . '"' : "") . '
                });
            ');
            $this->actualizaCargaMasiva(100, "CARGA MASIVA EXITOSA");
        }
    }

    private function bitacora($edo, $observacion)
    {
        $c = new AccesoDatos();
        $c->conectar();
        $sql = '
            INSERT INTO '.BD_BITACORA.'.bit_CargaMasiva
            SET bit_observacion= :bit_observacion,
            crm_id = :crm_id,
            tem_carga = :tem_carga,
            ecr_id = :ecr_id,
            tcr_id = :tcr_id,
            usr_id = :usr_id,
            usr_ip = INET_ATON(:usr_ip);
        ';
        $parametrosbit = [
            ':bit_observacion'=> $observacion,
            ':crm_id' => $_SESSION[_S]["tem_carga_id"],
            ':tem_carga' => $_SESSION[_S]["tem_carga"],
            ':ecr_id' => $edo,
            ':tcr_id' => 3,
            ':usr_id' => $this->usuario_id,
            ':usr_ip' => $_SESSION[_S]['tem_usr_ip']
        ];
        $c->setSql($sql);
        $c->consultar($parametrosbit);
        $c->desconectar();
    }

    //OBTIENE EL ESTADO DE LA CARGA MASIVA
    private function getAllEstadoCarga($fecha_actual)
    {
        $this->conectar();
        $sql = '
            SELECT crm_id FROM '.BD_CTRL.'.cat_CargaMasiva
            WHERE  crm_fecha = ?
            AND ecr_id  NOT IN(?,?,?,?,?,?,?)
            AND gpo_id= ? AND ur_id= ?
            ORDER BY crm_id DESC
            LIMIT 1
        ';
        $parametros = [
            $fecha_actual,
            91,92,93,94,95,99,100,
            $this->adm_grupo,
            $this->adm_ur
        ];
        $this->setSql($sql);
        $this->consultar($parametros);
        $this->extraer();
        $this->desconectar();

        if ($this->getNumeroRegistros() > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function cierre_inesperado()
    {
        $this->conectar();
        $sql = '
            SELECT crm_id,tem_carga FROM '.BD_CTRL.'.cat_CargaMasiva
            WHERE
            tcr_id = ?
            AND ecr_id  NOT IN(?,?,?,?,?,?,?)
            AND usr_id= ?
            ORDER BY crm_id DESC
            LIMIT 1
        ';
        $parametros = [
            3,
            91,92,93,94,95,99,100,
            $this->usuario_id
        ];
        $this->setSql($sql);
        $this->consultar($parametros);
        $this->desconectar();
        $row = $this->extraer();

        return $row;
    }

    private function finalize()
    {
        $mod = new MaestroModificacion($this->prm);
        $this->conectar();
        $observacion = "Proceso Finalizado por el usuario";
        $sql = '
            SET @usr_id =?,
            @usr_ip = INET_ATON(?),
            @bit_observacion=?;
        ';
        $parametrosbit = [
            $this->usuario_id,
            "127.0.0.1",
            $observacion
        ];
        $this->setSql($sql);
        $this->consultar($parametrosbit);

        $sql = '
            UPDATE cat_CargaMasiva
            SET ecr_id = 99
            WHERE tcr_id = ?
            AND gpo_id = ?
            AND ur_id = ?
            AND usr_id = ?
            AND crm_id = ?
        ';
        $parametros = [
            3,
            $this->adm_grupo,
            $this->adm_ur,
            $this->usuario_id,
            $this->prm->crm_id
        ];
        $this->setSql($sql);
        $this->consultar($parametros);
        $this->desconectar();

        $this->mixScript('
            notify("Proceso Terminado","success");
            comunica("scp/adecuaciones/ControladorRegistroModificaciones.php",{
                metodo:"tablaModificaciones",
                ecf_id:"' . $mod->getEcf_id() . '",
                mod_gpo_id:' . $mod->getMod_gpo_id() . ',
                mod_ur_id:"' . $mod->getMod_ur_id() . '",
                omo_id:"' . $mod->getOmo_id() . '",
                mod_id:' . $mod->getMod_id() . ',
                enc_oop:"' . $mod->getOop() . '",
                cambiaUR:true
                ' . ($this->nivelRevision != '' ?
                ',nivelRevision:"' . $this->nivelRevision . '"' : "") . '
            });
        ');
    }

    public function actualizaCargaMasiva($estado, $observacion)
    {
        $conex = new AccesoDatos();
        $conex->conectar();
        $sql = '
            SET @usr_id =?,
            @usr_ip = INET_ATON(?),
            @bit_observacion=?;
        ';
        $parametrosbit = [
            $this->usuario_id,
            $_SESSION[_S]['tem_usr_ip'],
            $observacion
        ];
        $conex->setSql($sql);
        $conex->consultar($parametrosbit);
        $sql = '
            UPDATE cat_CargaMasiva
            SET ecr_id = ' . $estado . '
            WHERE gpo_id = ? AND ur_id = ?
            AND tem_Carga = ?;
        ';
        $parametros = [
            $this->adm_grupo,
            $this->adm_ur,
            $_SESSION[_S]['tem_carga']
        ];
        $conex->setSql($sql);
        $conex->consultar($parametros);
        $conex->desconectar();
    }

    public function failedDirectory($tem_carga, $respuesta)
    {
        $mod = new MaestroModificacion($this->prm);
        $mes = date('m');
        $failed = '../../archivos/failedCargaMasiva/adecuaciones/' . $mes . '/' . $this->adm_grupo . $this->adm_ur;

        if (!file_exists($failed)) {
            mkdir($failed, 0777, true);
        }

        $failed .= '/' . $tem_carga . '.txt';
        file_put_contents($failed, $respuesta);

        $this->mixScript('
            notify("Hay errores en el archivo, favor de descargar el archivo para revisarlos","error");
            var erro = "' . $tem_carga . '";
            var d = new Date();
            var ur = "' . $this->adm_grupo . $this->adm_ur . '";
            var month = "' . date('m') . '";
            console.log(month);
            $(\'#button_txt\').html("<a class=\'btn btn-outline-danger\' href=\'archivos/failedCargaMasiva/adecuaciones/"+month+"/"+ur+"/"+erro+".txt\' id=\'download_file\' download=\'archivos/failedCargaMasiva/adecuaciones/"+month+"/"+erro+".txt\'>Descargar archivo</a>");
            $("#export_excel").hide();
        ');
    }
}

new Controlador_CargaMasiva();
