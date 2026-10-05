<?php
if (session_id() == PHP_SESSION_NONE && !isset($_SESSION[_S])) {
    session_start();
}

error_reporting(E_ALL);
/* ------------------------------------------------------------------------------------------------------------------ */
// File name   : ControladorConciliaExtracto.php
// Version     : 2.0.0
// Begin       : 2017-04-20
// Last Update : 2018-03-01
// -------------------------------------------------------------------
//
/* ****************************************************************************************************************** */
/*  @autor LI Japhet Mata Arellanes CodeJMA®  Class Procted by 9,565,768 US patent
/* ----   Copyright (C) 2002-2014 Japhet Mata - jmacorp.onion LTD ----------------------------------------------------- */
/* ----   External Clases Rights are property of their respective owners -------------------------------------------- */
/* ----   CodeJMA is an international brand of JMGlobal Corporation ------------------------------------------------- */
/* ---------------------------------    Notes for internal purpose only -------------------------------------------- */
/* ---------------------------------    ©2018 ------------------------------------------------------------------ */
/* ------------------------------------------------------------------------------------------------------------------ */
/* ****************************************************************************************************************** */
/**     NOTES: debug are not allowed !!
 *  GARANTIA: SI LO ROMPES PUEDES CONSERVAR LOS PEDAZOS
 *  NOTAS DE LA VERSIÓN
 *      - USAMOS TABLAS POR TIPO DE MINISTRACION
 *      - USAMOS OBJETOS DE EXTRACTO BANCARIO
 *      - EN MINISTRAR TODO SE GENERA UNA LLAMADA TRAS OTRA Y NO TODAS AL MISMO TIEMPO
 *      - EL MINISTRAR TODO SE PUEDE CANCELAR SIN PROBLEMAS
 *      - SE COMPRIME EL FICHERO A MUCHAS MENOS FUNCIONES Y MAS ESTRUCTURADAS (SEGUN YO)
 *      - 2018/11/22
 *      - Usamos la mismas librerias para todos los años;
 */

ini_set('max_execution_time', 600); //300 seconds = 5 minutes

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/conciliacion/lib/libExtracto.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/conciliacion/lib/ministraExtracto.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/conciliacion/lib/RutaExtracto.php';

class ControladorConciliaExtracto extends WebResponse
{
    private $btnregresa;
    private $btnFiltra;
    private $ministraTodo;
    private $exportaExcell;
    private $library;
    private $permisoClc;
    private $permisoDocumento;

    public function __construct()
    {
        parent::__construct();
        $this->permisoClc = tienePermiso('T.7.2.88');
        $this->permisoDocumento = tienePermiso('T.7.2.89');

        $this->btnregresa = new stdClass();
        $this->btnregresa->id = 'btnregresa';
        $this->btnregresa->name = 'btnregresa';
        $this->btnregresa->value = 'Regresar';
        $this->btnregresa->type = 'button';
        $this->btnregresa->class = 'btn btn-outline-secondary me-2';
        $this->btnregresa->icon = "fa-solid fa-circle-left me-1";
        $this->library = new libExtracto();

        $this->pantalla = "tes/extracto/ControladorConciliaExtracto.php";
        switch ($this->metodo) {
            case "pintaTablas":
                $this->pintaTablas();
                break;
            case "ministrar":
                $this->ministrar();
                break;
            case "llenaTablaMinistrar":
                $this->llenaTablaMinistrar();
                break;
            case "cargaExtracto":
                $this->cargaExtracto();
                break;
            default:
                $this->main();
                break;
        }

        $this->returnJson();
    }
    public function main()
    {
        $this->agregaTools("");
        $html = '';

        $clcMongo = '
            <div titpp="Ministracion por CLC ( 2017 )" class="boton_opcion" id="clcMongo" >
                <div class="boton_opcion_titulo" >Ministrar</div>
                <div>
                    <label class="text3d">
                    CLC Mongo</label>
                    <small>CLC</small>
                </div>
            </div>';
        $clc = '
            <div titpp="Ministracion por CLC ( 2017 )" class="boton_opcion" id="clc" >
                <div class="boton_opcion_titulo" >Ministrar</div>
                <div>
                    <label class="text3d">
                    CLC</label>
                    <small>CLC</small>
                </div>
            </div>';

        $documento = '
            <div style="width:10px; display:table-cell"></div>
                <div titpp="Ministracion de documentos" class="boton_opcion" id="documento" >
                    <div class="boton_opcion_titulo" >Ministrar</div>
                    <div>
                        <label class="text3d">
                        <span class="fa fa-file"></span>
                        </label>
                        <small>Documento</small>
                    </div>
                </div>';

        $cheque = '
            <div style="width:10px; display:table-cell"></div>
            <div titpp="Ministracion de documentos" class="boton_opcion" id="cheque" >
                <div class="boton_opcion_titulo" >Ministrar Err</div>
                <div>
                    <label class="text3d">
                    <span class="fa fa-credit-card"></span>
                    </label>
                    <small>Cheque</small>
                </div>
            </div>';

        $lc = '
            <div style="width:10px; display:table-cell"></div>
            <div titpp="Ministracion de propuesta por " class="boton_opcion" id="lineacaptura" >
                <div class="boton_opcion_titulo" >Ministrar</div>
                    <div>
                        <label class="text3d">
                        <span class="fa fa-option-horizontal"></span>
                        </label>
                        <small>Lineas de Captura</small>
                </div>
            </div>';

        $manual = '
            <div style="width:10px; display:table-cell"></div>
            <div titpp="Ministracion de propuesta por" class="boton_opcion" id="manual" >
                <div class="boton_opcion_titulo" >Ministrar</div>
                    <div>
                        <label class="text3d">
                        <span class="fa fa-hand-down"></span>
                        </label>
                        <small>Propuesta Manual</small>
                </div>
            </div>';

        $municipios = '
            <div style="width:10px; display:table-cell"></div>
            <div titpp="Ministracion de propuesta por" class="boton_opcion" id="municipios" >
                <div class="boton_opcion_titulo" >Ministrar</div>
                    <div>
                        <label class="text3d">
                        <span class="fa fa-asterisk"></span>
                        </label>
                        <small>Municipios</small>
                </div>
            </div>';

        if ($_SESSION[_S]['usr_id'] == 181) {
            $html = '
                    <center>'
                . $clc
                . $documento
                . $cheque
                . $lc
                . $manual
                . $municipios
                . '</center>';
        } else {
            if ($this->permisoClc) {
                $html .= $clc;
            }
            if ($this->permisoDocumento) {
                $html .= $documento;
            }
        }
        //$html .= $clcMongo;
        $this->mixHtml('<center>' . $html . '</center>');

        $this->mixScript('
            $(".boton_opcion").click(function(){
                op = $(this).attr("id");
                if(op !="clcMongo")
                    comunica("' . $this->pantalla . '", {metodo:"pintaTablas",tipo:op});
                else
                    comunica("' . $this->pantalla . '", {metodo:"pintaTablaMongo"});
            });
        ');
    }
    public function pintaTablas()
    {
        $this->btnregresa->click = 'comunica("' . $this->pantalla . '");';
        $btnregresa = $this->ponBoton($this->btnregresa);

        $mensaje = '';
        switch ($this->prm->tipo) {
            case "clc":
                $mensaje = "Confirme que desea ministrar todas las CLCs de la página";
                break;
            case "documento":
                $mensaje = "Confirme que desea ministrar todos los Documentos de la página";
                break;
            default:
                $mensaje = "Confirme que desea ministrar todos los Documentos de la página";
                break;
        }

        $this->btnFiltra = new stdClass();
        $this->btnFiltra->id = "btnFiltra";
        $this->btnFiltra->value = "Filtrar";
        $this->btnFiltra->class = "btn btn-outline-secondary";
        $this->btnFiltra->click = 'var dialog; Mensaje();'
        . $this->wrLlamaLlenaTabla('tbl_Conceptos');

        $filtrar = $this->ponBoton($this->btnFiltra);

        $this->ministraTodo = new stdClass();
        $this->ministraTodo->id = "btnMinAll";
        $this->ministraTodo->value = "Ministrar Todo";
        $this->ministraTodo->class = "btn-outline-success me-2";
        $this->ministraTodo->click = '
            alertify.confirm(
                "MINISTRAR TODO",
                "' . $mensaje . '",
                function(){
                    $("#btnMinAll").hide().attr("disable");
                    minall();
                },
                function(){},
            ).set("labels", {ok: "Aceptar", cancel: "Cancelar"})
            .set("defaultFocus","cancel");
        ';

        $minall = $this->ponBoton($this->ministraTodo);

        $this->exportaExcell = new stdClass();
        $this->exportaExcell->id = "btnExcel";
        $this->exportaExcell->value = "Excel";
        $this->exportaExcell->class = "btn btn-outline-success me-2";
        $this->exportaExcell->icon = 'fa-solid- fa-file-excel me-1';
        $this->exportaExcell->click = 'ExportToExcel("tabla-tbl_Conceptos");';
        $excel = $this->ponBoton($this->exportaExcell);

        $this->agregaTools($btnregresa . "  " . $minall . " " . $excel);

        $fecha = new stdClass();
        $fecha->id = "inpFecha";
        $fecha->class = 'fecha';
        $fecha->label = 'Fecha';
        $fecha->enDiv = true;
        $fecha->formato = 'fecha';
        $fecha->labelClass = '_32 txtL';
        $fecha->required = true;
        $fecha->value = date('d/m/Y');
        $fecha = $this->ponInput($fecha);

        $ejercicio = new stdClass();
        $ejercicio->id = "ejr_id";
        $ejercicio->enDiv = true;
        $ejercicio->camposVal = "ejr_id";
        $ejercicio->mensaje = "Selecciona Ejercicio";
        $ejercicio->label = "Ejercicio";
        $ejercicio->selecciona = APP_EJERCICIO;
        $ejercicio->sql = "SELECT * FROM " . BD_TESORERIA . ".cat_Ejercicio";
        $ejercicio = $this->ponCombo($ejercicio);

        $mostrar = new stdClass();
        $mostrar->id = "xx";
        $mostrar->enDiv = true;
        $mostrar->label = "Mostrar";
        $mostrar->opciones = array(
            1 => "NO MINISTRADOS",
            2 => "NO MINISTRADOS C/REVERSOS",
            3 => "TODOS",
            4 => "SOLO REVERSOS",
        );
        $mostrar = $this->ponCombo($mostrar);

        $ur = new stdClass();
        $ur->id = "inpUr";
        $ur->class = 'ur';
        $ur->label = 'UR';
        $ur->enDiv = true;
        $ur = $this->ponInput($ur);
        $date = "";

        $html = '
            Puede ministrar fechas hasta ' . $date . '
                <div class="row row-cols-5">
                    <div class="col-xs-2">' . $fecha . '</div>
                    <div class="col-xs-1">' . $ur . '</div>
                    <div class="col-xs-2">' . $ejercicio . '</div>
                    <div class="col-xs-2">' . $mostrar . '</div>
                    <div class="col-xs-1"><br/>' . $filtrar . '</div>
                </div>';

        $tabla = $this->library->vwTabla($this->prm->tipo);
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaMinistrar';
        $tabla->comunica->fecha = '{$("#inpFecha").val()}';
        $tabla->comunica->ur = '{$("#inpUr").val()}';
        $tabla->comunica->anio = '{$("#ejr_id").val()}';
        $tabla->comunica->tipo = $this->prm->tipo;
        $tabla->comunica->mostrar = '{$("#xx").find("option:selected").val()}';

        $html .= $this->wrPonTabla($tabla);
        $this->mixHtml($html);
        $this->mixScript('
                var all = false;

                function Mensaje(){
                    dialog = alertify.confirm(
                        \'<span class="alert alert-success"> Filtro </span>\',
                        \'<p><i class="fa fa-spin fa-spinner"></i> buscando ...</p>\',
                        function(){},
                        function(){},
                    ).set("labels", {ok: "Aceptar", cancel: "Cancelar"})
                    .set("defaultFocus","cancel");
                }
                function ExportToExcel(objeto){
                    objeto = document.getElementById(objeto);
                    var html = objeto.outerHTML;
                    window.open(\'data:application/vnd.ms-excel,\' + encodeURIComponent(html));
                }

                function minall(){
                    all = true;
                    $("#toolsPantalla").append(
                        $("<button/>").addClass("btn btn-outline-danger")
                        .html("CANCELAR")
                        .prop("id","btnCancelar")
                        .click(function(){ all = false;
                            $(".minreg").show().parent().find("span").remove();
                            $("#btnMinAll").show().removeAttr("disable");
                            $(this).remove();
                            $(".minreg").removeClass("pending");
                        })
                    );
                    $(".minreg").hide();
                    $(".minreg").each(function(){
                        exb = $(this).attr("exb_id");
                        ids = exb.replace(/,/g,"");
                        $(this).addClass("pending")
                            .prop("id",ids)
                            .parent()
                            .append($("<span/>").html("pendiente"));
                    });
                    fn_ex_min();
                }

                function fn_ex_min(){
                    if ( $(".pending:first-child").length == 0 ){
                        $("#btnMinAll").show().removeAttr("disable");
                        $("#btnCancelar").remove();
                        return false;
                    }
                    exb = $(".pending:first-child").attr("exb_id");
                    anio = $(".pending:first-child").attr("anio");
                    ids = exb.replace(/,/g,"");
                    if ( exb != undefined && all == true ) {
                        $("#"+ids).removeClass("pending")
                            .addClass("processing")
                            .parent()
                            .append( $("<i/>")
                            .addClass("fa fa-spin fa-spinner"))
                            .attr("id","td_" + ids)
                            .find("span").remove();

                        $("#"+exb).parent().append( $("<span/>").html("procesando"));
                        comunica("' . $this->pantalla . '",{metodo:"ministrar",exb:exb,"anio":anio,"tipo":"' . $this->prm->tipo . '"})
                    } else {
                        $("#btnMinAll").show().removeAttr("disable");
                        $("#btnCancelar").remove();
                    }
                }
                var visor;
                function verExtracto(obj){
                    fch2 = obj.attr("arb_rechazo");
                    fch = obj.attr("arb_id");
                    if(fch == undefined) {
                        return false;
                    }

                    linea = obj.attr("exb_numeroLinea");
                    linea2 = obj.attr("lineaRechazo");
                    visor = new load_viewer(fch,"DETALLE DEL EXTRACTO BANCARIO","div");
                    $("#det_fs"+fch).css("background-image","").css("background-color","#FFF");
                    comunica("' . $this->pantalla . '",{
                        metodo:"cargaExtracto",
                        "fch":fch,
                        "linea":linea,
                        "fch2":fch2,
                        "linea2":linea2
                    });
                }');
    }
    public function llenaTablaMinistrar()
    {
        $sql = $this->library->getConsultaTabla($this->prm);
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 100;
        $nav->idTabla = 'tbl_Conceptos';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No hay registros ","warning");
                dialog.setContent("No hay registros.");
                setTimeout(() => {
                    dialog.close();
                }, 1000);
            ');
            return;
        }
        $this->mixScript('
            dialog.setContent("...");
            setTimeout(() => {
                dialog.close();
            }, 1000);
            $("#btnMinAll").removeAttr("disable").show();
            $(".arb_nombreOriginal").click(function(){verExtracto($(this));});
            $(".minreg").click(function(){
                exb_id = $(this).attr("exb_id");
                ids = exb_id.replace(/,/g,"");
                $(this).addClass("pending").prop("id",ids).attr("exb_id",exb_id)
                    .hide()
                    .parent().append($("<span/>").html("pendiente"));
                all = true;
                fn_ex_min();
                all = false;
            });
        ');
    }
    public function ministrar()
    {
        if (!isset($this->prm->exb)) {
            $this->mixScript('notify("error 1","error");');
            return false;
        }
        $con = new AccesoDatos();
        $usr = $_SESSION[_S]['usr_id'];
        $ip = isset($_SESSION[_S]['ses_ip']) ? $_SESSION[_S]['ses_ip'] : $_SERVER['REMOTE_ADDR'];

        $ministrado = new ministraExtracto($usr, $ip, $con, APP_EJERCICIO, _S);

        $ministrado->setTipo($this->prm->tipo);
        $ministrado->setIdExtracto($this->prm->exb);
        $ministrado->setEjercicio($this->prm->anio);
        $ministrado->ministrarExtracto();
        $resultado = $ministrado->getResultado();
        $res = $resultado ? "alert-success" : "alert-danger";
        $mensaje = $ministrado->getMensaje();
        $exb = str_replace(",", "", $this->prm->exb);
        $this->mixScript('
                $("#td_' . $exb . '").html("<div class=\'' . $res . ' div_ajustable\'>'
            . $mensaje . '</div>");
                $("#' . $exb . '").remove();
                fn_ex_min();
            ');
    }
    private function cargaExtracto()
    {
        $this->setContenedor("det_fs" . $this->prm->fch);
        $con = $this->leeLineaExtracto($this->prm->fch, $this->prm->linea, false);
        $contenido = $con['contenido'];
        $nombre1 = $con['nombre'];
        $nombre2 = '';

        $contenido2 = '';
        if (isset($this->prm->fch2) && $this->prm->fch2 != "") {
            $con2 = $this->leeLineaExtracto($this->prm->fch2, $this->prm->linea2, true);
            $contenido2 = $con2['contenido'];
            $nombre2 = $con2['nombre'];
        }

        $html = '<textarea class="mysql" style="width:100%; height:100%;"  id="txtA_' .
        $this->prm->fch . '">[CONTENIDO ORIGINAL][linea:' . $this->prm->linea . ']' .
            PHP_EOL . $contenido .
            PHP_EOL . PHP_EOL;

        if ($contenido2 != "") {
            $html .= '[CONTENIDO DEL REVERSO BANCARIO {' . $nombre2 . '}][linea:' . $this->prm->linea2 . ']' . PHP_EOL . $contenido2;
        }

        $html .= '</textarea>';
        $this->mixHtml($html);
        $this->mixScript('
                otro = "txtA_' . $this->prm->fch . '";

                window.editor =
                    CodeMirror.fromTextArea(document.getElementById(otro), {
                        mode: "text/x-mariadb",
                        theme:"twilight",
                        dragDrop: false,
                        indentWithTabs: true,
                        smartIndent: true,
                        lineNumbers: true,
                        matchBrackets : true,
                        autofocus: true,
                        extraKeys: {"Ctrl-Space": "autocomplete"},
                        hintOptions: {tables: {
                            users: {name: null, score: null, birthDate: null},
                            countries: {name: null, population: null, size: null}
                        }}
                    });
                $(".CodeMirror").css("text-align","left").css("height","100%");
                visor.setTitle("DETALLE EXTRACTO BANCARIO ' . $nombre1 . '")
                visor.max();

            ');
    }
    private function leeLineaExtracto($f, $l, $extra)
    {
        $sql = '
                SELECT iba_abreviatura,arb_nombreSistema,arb_nombreOriginal,tav_id,ejr_id,msc_id
                FROM tesoreria.ban_ArchivoBancario
                INNER JOIN tesoreria.cat_InstitucionBancaria USING (iba_id)
                WHERE arb_id = ' . $f;
        $this->conectar();
        $this->setSql($sql);
        $this->consultar();

        $contenido = '';
        $nombre = '';

        if ($this->getNumeroRegistros() == 0) {
            $contenido = "error";
        } else {
            $res = $this->extraer();
            $banco = $res['iba_abreviatura'];
            $nombre = $res['arb_nombreOriginal'];

            $path = new RutaExtracto();

            $ruta = $path->exb_buildPath($banco, $res['msc_id'], $res['ejr_id']);
            $ruta .= $res['arb_nombreSistema'] . "." . $res['tav_id'];

            // $contenido = file_get_contents($ruta,FILE_USE_INCLUDE_PATH,NULL,$l-5);
            $res = file($ruta);
            if (!$extra) {
                $contenido .= $res[$l - 3];
            }

            $contenido .= $res[$l - 2];
            $contenido .= $res[$l - 1];
            unset($res);
        }

        $this->desconectar();
        return array("contenido" => trim($contenido), "nombre" => $nombre);
    }
    private function pintaTablaMongo()
    {
    }
}
if (isset($_SESSION[_S]['prm']['T']['7']['2'])) {
    new ControladorConciliaExtracto();
} else {
    WebResponse::sinPermisos();
}
