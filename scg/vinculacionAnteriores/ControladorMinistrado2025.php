<?php
//librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/getDocumento.php';
require_once dirname(__FILE__) . '/../../lib/opcionConteo.php';
require_once dirname(__FILE__) . '/../../lib/pintaClc.php';
require_once dirname(__FILE__) . '/../../lib/scg/pintaMinistrado.php';
require_once dirname(__FILE__) . '/../../lib/scg/vinculacion/2025/vinculacionMinistrado2025.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPoliza.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPolizaAgrupador.php';
require_once dirname(__FILE__) . '/../../lib/scg/pintaPoliza.php';
require_once dirname(__FILE__) . '/../../lib/validaFecha.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/2025/sp_MinistradoAutorizado2025.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/2025/sp_MinistradoCancelado2025.php';

class ControladorMinistrado2025 extends WebResponse
{
    public $escritura;
    public $gpo_id;
    public $ur_id;
    public $ue_id;
    public $gpo_id_2025;
    public $ur_id_2025;
    public $usr_ip;
    public $usr_id;
    public $origen;
    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        //Permisos de acceso a las tareas del controlador
        $this->escritura = isset($_SESSION[_S]['prm']['C']['17']['14'][TAREA_ESCRITURA]) ? true : false;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = false;
        }

        $sql = '
            SELECT
                gpo_id_2025 AS gpo_id, ur_id_2025 AS ur_id
                FROM ' . BD_SINPRES . '.vin_UnidadResponsable_2025_2026
                WHERE gpo_id_2026 = ? AND ur_id_2026 = ?
                LIMIT 1;
        ';
        $params = [$this->gpo_id, $this->ur_id];
        $conexion = new AccesoDatos();
        $conexion->setSql($sql);
        $conexion->conectar();
        $conexion->consultar($params);
        $data = $conexion->extraer();
        $conexion->desconectar();
        $this->gpo_id_2025 = $data['gpo_id'];
        $this->ur_id_2025 = $data['ur_id'];
        unset($conexion);

        $this->usr_ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip']
            != "::1") ? $_SESSION[_S]['ses_ip'] : 'ERROR';
        $this->usr_id = $_SESSION[_S]['usr_id'];

        $this->origen = isset($this->prm->origen) ? $this->prm->origen : 'opcion';
        if ($this->origen == 'opcion') {
            $this->prm->mes = isset($this->prm->mes) ? $this->prm->mes : DATE("m");
        }

        switch ($this->metodo) {
            case 'DibujaPantalla':
                $this->DibujaPantalla();
                break;
            case 'MuestraDatos':
                $this->MuestraDatos();
                break;
            case 'Documento':
                $this->Documento();
                break;
            case 'GeneraPoliza':
                $this->GeneraPoliza();
                break;
            case 'PonBarraBotones':
                $this->PonBarraBotones();
                break;
            case 'pintaTablaMinistrado';
                $this->PonBarraBotones();
                $this->setContenedor('contenedor');
                $this->mixHtml($this->getTablaMinistrado());
                break;
            default:
                opcionConteo(
                    $_POST['opcion']['sis_id'],
                    $_POST['opcion']['men_id'],
                    $_POST['opcion']['opc_id']
                );
                if ($this->origen == 'clc') {
                    $this->PonBarraBotones();
                } else {
                    if ($this->gpo_id . $this->ur_id == 801) {
                        $this->pintaBotonesVinculaciones();
                    } else {
                        $this->PonBarraBotones();
                        $this->prm->seleccion = "clc";
                    }
                }
                break;
        }

        $this->returnJson();
    }

    private function main()
    {
        $html = $this->getTablaMinistrado();
        $this->PonBarraBotones();
        $this->setContenedor('contenedor');
        $es801 = ($this->gpo_id . $this->ur_id == 801) ? 801 : 0;
        $this->origen = ((isset($this->prm->origen) && $this->prm->origen != "") ? $this->prm->origen : "");
        $this->seleccion = ((isset($this->prm->seleccion) && $this->prm->seleccion != "") ? $this->prm->seleccion : "");
        $this->mixHtml($html);
    }

    private function pintaBotonesVinculaciones()
    {
        $this->agregaTools('');

        $html = '
            <center>
                <div  id="vinculacionClc" style="" class="boton_opcion">
                        <div class="boton_opcion_titulo">VINCULACIÓN <br> POR CLC</div>
                        <div class="price">
                            <div class="center validafoliour">
                                <img src="img/ListadoDelMes.png">
                            </div>
                        </div>
                </div>

                ' . ($this->gpo_id . $this->ur_id == 801 ? '
                <div style="display:table-cell;width:5px;"></div>
                <div style="width:5px; display:table-cell"></div>
                <div id="vinculacionPensiones" class="boton_opcion">
                    <div class="boton_opcion_titulo">PENSIONES<br><br></div>
                    <div class="price">
                        <div class="center validafoliour">
                            <img src="img/ListadoDelMes.png">
                        </div>
                    </div>
                </div>
                ' : '') . '

            </center>
            ';

        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->type = 'button';
        $btnRegresa->class = "me-2";
        $btnRegresa->color = "gris";
        $btnRegresa->icon = "fa-solid fa-circle-lef me-1";

        $this->mixHtml($html);
        $this->mixScript('
                $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
                $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
                //ES DE LA TABLA tes_BancoDocumentoMinistrado
                $("#vinculacionClc").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "pintaTablaMinistrado",
                        origen: "' . $this->origen . '",
                        mes: "' . $this->prm->mes . '",
                        seleccion: "clc"
                    });
                });

                //ES DE LA TABLA tes_MinistradoPlaneacion
                //mip_tipoRecurso = 1
                $("#vinculacionFederal").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "tes",
                        origen: "' . $this->origen . '",
                        mes: "' . $this->prm->mes . '",
                        seleccion: "federal"
                    });
                });

                $("#vinculacionPlaneacion").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "tes",
                        origen: "' . $this->origen . '",
                        mes: "' . $this->prm->mes . '",
                        seleccion: "planeacion"
                    });
                });
                ' . ($this->gpo_id . $this->ur_id == 801 ? '
                $("#vinculacionPensiones").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "pintaTablaMinistrado",
                        origen: "' . $this->origen . '",
                        mes: "' . $this->prm->mes . '",
                        seleccion: "pensiones"
                    });
                });
                ' : '') . '
                $("#btnRegresa").click(function(){
                    $("nav").menusinpres("clickMenu");
                });
            ');
    }

    private function PonBarraBotones()
    {
        if (isset($_SESSION[_S]['s26-nvj']) && $_SESSION[_S]['s26-nvj'] < 3) {
            $this->mixScript('$("#toggleCA").hide();'); // ocultamos la unidad responsable si el nivel de usuario es menor a 3
        }
        // Botones y opciones que se dibujarán en la barra de botones
        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar';
        $btnLimpiar->value = 'Limpiar';
        $btnLimpiar->type = 'button';
        $btnLimpiar->class = "btn btn-outline-success";

        $clcFiltro = false;
        $bdoFiltro = false;

        switch ($this->origen) {
            case "clc":
                $clcFiltro = true;
                break;
            case "bdo":
                $bdoFiltro = true;
                break;
            default:
                if (isset($this->prm->clcFiltro) && $this->prm->clcFiltro == 1) {
                    $clcFiltro = true;
                }
                if (isset($this->prm->bdoFiltro) && $this->prm->bdoFiltro == 1) {
                    $bdoFiltro = true;
                }
                break;
        }

        $botones = '
                <div class="row">
                    ' . (($this->gpo_id . $this->ur_id == 801 && isset($this->prm->seleccion)
            && $this->prm->seleccion == 'pensiones') ? '<fieldset class="col-md-1" style="padding-right: 1px !important;">
                        <input id="inputGur" placeHolder="UR" type="text" maxlength=3 class="form-control" value="' . (isset($this->prm->gur)
            ? $this->prm->gur : "") . '"/>
                    </fieldset>' : '') . '
                    <fieldset class="col-md-1" style="padding-right: 1px !important;">
                        <input id="inputClc_id" placeHolder="CLC" type="text" maxlength=6 class="form-control" value="' . ($clcFiltro
            ? $this->prm->clc_id : "") . '"/>
                    </fieldset>
                    <fieldset class="col-md-2" style="padding-right: 1px !important;">
                        <input id="inputBdo_id" placeHolder="# Documento" maxlength=7 class="form-control" value="' . ($bdoFiltro
            ? $this->prm->bdo_id : "") . '"/>
                    </fieldset>
                    <fieldset class="col-md-2" style="padding-right: 1px !important;">
                        <input id="inputRfc_id" placeHolder="RFC" maxlength=20 class="form-control" value="' . ($bdoFiltro
            ? $this->prm->rfc_id : "") . '"/>
                    </fieldset>
                    <fieldset class="col-md-2" style="padding-right: 1px !important;">
                        <input id="inputBdo_referencia" placeHolder="Referencia" maxlength=20 class="form-control" value="' . ($bdoFiltro
            ? $this->prm->bdo_referencia : "") . '"/>
                    </fieldset>
                    <fieldset class="col-md-2" style="padding-right: 1px !important;">
                    <!--  <button id="excelMinistrado2025" class="btn btn-success ladda-button" data-style="slide-left"><i class="glyphicon glyphicon-download-alt"></i></button> -->
                    </fieldset>
                </div>
            ';

        $this->agregaTools($botones);
        // dibujamos los Tabuladores
        $pestana = "";
        $contenedor = "";
        $inicio = "";
        $this->conectar();
        $this->setSql('
            SELECT msc_id, msc_nombre, msc_alias, ems_id AS estado
            FROM cat_MesContable
            INNER JOIN cg_MesContable USING( msc_id )
            WHERE gpo_id = ? AND ur_id = ?
            AND msc_id NOT IN ( 0, 13 )
        ');
        $params = [$this->gpo_id, $this->ur_id];
        $this->consultar($params);
        $this->desconectar();

        while ($row = $this->extraer()) {
            if ($this->prm->mes == $row['msc_id']) {
                $inicio = 'class="active"';
                $active = 'active';
            } else {
                $inicio = '';
                $active = '';
            }

            $pestana = str_replace('ultimo', '', $pestana);
            $pestana = str_replace('lastdiv', '', $pestana);

            $pestana .= '
                    <li opcion="' . $row['msc_id'] . '" ' . $inicio . ' ultimo>
                        <div></div><a class="nav-link ' . $active . ' meses" mes="' . $row['msc_id'] . '" estado="' . $row['estado'] . '"> ' . $row['msc_alias'] . ' </a> lastdiv
                    </li>
                ';

            $contenedor .= '
                    <div id="contenedor' . $row['msc_id'] . '" style="display:none" class="contenedores"></div>
                ';
        }

        $pestana = str_replace('ultimo', 'class="last"', $pestana);
        $pestana = str_replace('lastdiv', '<div class="last"></div>', $pestana);

        $this->mixHtml('
                <div id="page-tabs">
                    <ul class="nav nav-tabs">
                        ' . $pestana . '
                    </ul>
                </div>
            ');

        // Dibujamos el contenido
        switch ($this->origen) {
            case 'opcion':
                $this->mixScript('
                    comunica("' . $this->pantalla . '",{
                        metodo: "MuestraDatos",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });
                ');
                break;
            case 'clc':
                $this->mixScript('
                    comunica("' . $this->pantalla . '",{
                        metodo: "MuestraDatos",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });
                ');
                break;
        }

        $this->mixScript('
                $("#opciones").click( function(e){
                    comunica("' . $this->pantalla . '", {
                        metodo: "Filtro",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });
                });

                $("#btnLimpiar").click( function(e){
                    $("#inputGur").val("");
                    $("#inputClc_id").val("");
                    $("#inputBdo_id").val("");
                    $("#inputRfc_id").val("");
                    $("#inputBdo_referencia").val("");

                    comunica("' . $this->pantalla . '",{
                        metodo: "MuestraDatos",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });

                    e.stopPropagation();
                    return false;
                });

                $("#excelMinistrado2025").click( function(e) {
                    var form = $("<form>")
                        .attr("action", "rep/excel/scg/vinculacion2025/exp_Ministrado2025.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "6" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "1" ))

                        .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "T"))
                        .append($("<input>").attr("type", "hidden").attr("name", "seleccion").attr("value", "' . (isset($this->prm->seleccion)
            ? $this->prm->seleccion : "") . '" ))

                        .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs a").parent(".active").children("a").attr("mes") ))

                        .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_id").attr("value", $("#inputBdo_id").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputRfc_id").attr("value", $("#inputRfc_id").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_referencia").attr("value", $("#inputBdo_referencia").val() ))

                    $("body").append(form);
                    form.submit();
                });

                $("#imprimirMinistrado2025").click( function(e) {
                        var form = $("<form>")
                            .attr("action", "rep/doc/scg/reporte.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "6" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "1" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "T" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "seleccion").attr("value", "' . (isset($this->prm->seleccion)
            ? $this->prm->seleccion : "") . '" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs a").parent(".active").children("a").attr("mes") ))

                            .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", ' . $this->gpo_id_2025 . $this->ur_id_2025 . ' ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_id").attr("value", $("#inputBdo_id").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputRfc_id").attr("value", $("#inputRfc_id").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_referencia").attr("value", $("#inputBdo_referencia").val() ))

                        $("body").append(form);
                        form.submit();
                });

                // Programamos una combinación de ESC ESC para disparar el botón REGRESAR
                Mousetrap.bind("esc esc", function(e) {
                    e.preventDefault();
                    $("#btnHomeMenu").click();
                    e.stopPropagation();
                });

            ');
        $html = $this->getTablaMinistrado();
        $this->mixHtml($html);
        $this->DibujaPantalla();
    }

    private function DibujaPantalla()
    {
        $this->mixScript('
            $("#page-tabs a").click( function(e){
                e.preventDefault();
                var seleccionado = $(this);
                $("#page-tabs li").each(function (index) { $(this).removeClass("active"); });
                seleccionado.closest("li").addClass("active");
                
                $("#page-tabs a").each(function (index) { $(this).removeClass("active"); });
                seleccionado.closest("a").addClass("active");

                comunica("' . $this->pantalla . '",{
                    metodo: "MuestraDatos",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                    mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                    gur: $("#inputGur").val(),
                    clc_id: $("#inputClc_id").val(),
                    bdo_id: $("#inputBdo_id").val(),
                    rfc_id: $("#inputRfc_id").val(),
                    bdo_referencia: $("#inputBdo_referencia").val()
                });

                e.stopPropagation();
                return false;
            });
        ');

        if ($this->origen == 'clc') {
            $this->mixScript('
                $("#page-tabs").hide();
                $("#inputBdo_id").hide();
                $("#inputRfc_id").hide();
                $("#inputBdo_referencia").hide();
            ');
        }
    }

    private function getTablaMinistrado(): string
    {
        $tabla = new stdClass();
        $tabla->esCompacta = true;
        $tabla->cabeceraFija = true;
        $tabla->inputFiltro = "buscar";
        $tabla->id = 'tbl-ministrado';
        $tabla->seleccionable = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'MuestraDatos';

        $tr = array();
        $th = new stdClass();
        $th->texto = 'AFE';
        $th->width = '90px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DOC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TDF';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RFC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'REFERENCIA';
        $th->width = "15%";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->width = "20%";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TDF';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'REFERENCIA MINISTRADO';
        $th->width = "20%";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->width = "20%";
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'AUTORIZACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CANCELACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'POL AUT.';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'POL CAN.';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        array_push($tr, $th);

        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['afe_id'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdo_id'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['tdf_id'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['rfc_id'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdo_referencia'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdo_concepto'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['tdf_id_ministrado'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdm_referencia'];
        $td->align = 'right';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['importe'];
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['estado'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaAutorizacion'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaCancelacion'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['polizaAutorizado'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaAutorizado'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['polizaCancelado'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaCancelado'];
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        return $this->wrPonTabla($tabla);
    }

    private function MuestraDatos()
    {
        $pensiones = (isset($this->prm->seleccion) && $this->prm->seleccion == 'pensiones' ? true : false);
        $gur = ($pensiones ? (isset($this->prm->gur) ? $this->prm->gur : '') : $this->gpo_id_2025 . $this->ur_id_2025);
        $clc_id = $this->prm->clc_id; //Informacion de acuerdo al mes
        $bdo_id = $this->prm->bdo_id; //Informacion de acuerdo al mes
        $rfc_id = $this->prm->rfc_id; //Informacion de acuerdo al mes
        $bdo_referencia = $this->prm->bdo_referencia; //Informacion de acuerdo al mes
        $periodo['mesDesde'] = $this->prm->mes; //Informacion de acuerdo al mes
        $periodo['mesHasta'] = $this->prm->mes; //Informacion de acuerdo al mes

        $reporte['tipo'] = 1;
        $programatico['epg_id'] = '';
        $ClaveFinanciamiento['ecf_id'] = '';
        $ClaveFinanciamiento['ter_id'] = '';

        $niveles[9] = 9;
        $niveles[8] = 0;
        $niveles[7] = 0;
        $niveles[6] = 6;
        $niveles[5] = 0;
        $niveles[4] = 0;
        $niveles[3] = 0;
        $niveles[2] = 0;
        $niveles[1] = 1;

        $detalle = false;

        $this->MuestraDatosPagina(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'T',
            $niveles,
            array(
                "gur" => $gur,
                "clc_id" => $clc_id,
                "bdo_id" => $bdo_id,
                "rfc_id" => $rfc_id,
                "bdo_referencia" => $bdo_referencia,
                "pensiones" => $pensiones
            )
        );

        return false;
    }

    private function MuestraDatosPagina(
        $gpo_id,
        $ur_id,
        $ue_id,
        $pot_id,
        $periodo,
        $programatico,
        $claveFinanciamiento,
        $reporte,
        $detalle,
        $mmc_id,
        $niveles,
        $parametroExtra = array()
    ) {
        switch ($mmc_id) {
            case 'B':
                $tablaAuxiliar = 'ctrlAnterior.min_BancoDocumentoMinistrado';
                $letraAuxiliar = 'm';
                $polizaSql = 'CASE tmi_id WHEN 2 THEN tmi_nombre ELSE pa.polizaAutorizado END AS polizaAutorizado, max(pa.fechaAutorizado) as fechaAutorizado, ';
                break;
            case 'T':
                $tablaAuxiliar = 'ctrlAnterior.min_BancoDocumentoMinistrado';
                $letraAuxiliar = 'm';
                $polizaSql = 'CASE tmi_id WHEN 1 THEN pa.polizaAutorizado ELSE tmi_nombre END AS polizaAutorizado, max(pa.fechaAutorizado) as fechaAutorizado, ';
                break;
            default:
                echo 'kym';
                break;
        }

        $sqlFiltro = '';
        if ($parametroExtra['gur'] != '') {
            $sqlFiltro = "v.gpo_id = " . substr($parametroExtra['gur'], 0, 1) . " AND v.ur_id = " .
                substr($parametroExtra['gur'], 1, 2) . " ";
        }
        if ($parametroExtra['clc_id'] != '') {
            $sqlFiltro .= (($sqlFiltro != '' ? "AND " : "") . "v.clc_id = " . $parametroExtra['clc_id'] . " ");
        }

        if ($parametroExtra['bdo_id'] != '') {
            $sqlFiltro .= ($sqlFiltro != '' ? "AND " : "") . "v.bdo_id = '" . $parametroExtra['bdo_id'] . "' ";
        }

        if ($parametroExtra['rfc_id'] != '') {
            $sqlFiltro .= ($sqlFiltro != '' ? "AND " : "") . "b.rfc_id = '" . $parametroExtra['rfc_id'] . "' ";
        }

        if ($parametroExtra['bdo_referencia'] != '') {
            $sqlFiltro .= ($sqlFiltro != '' ? "AND " : "") . "b.bdo_referencia IN ('" . $parametroExtra['bdo_referencia'] . "') ";
        }

        if (isset($parametroExtra['pensiones']) && $parametroExtra['pensiones']) {
            $pensiones = "AND v.tmi_id = 7 ";
        } else {
            $pensiones = "";
        }
        if ($periodo['mesDesde'] != '') {
            $sqlFiltro .= ($sqlFiltro != '' ? "AND " : "") . "v.msc_id = " . $periodo['mesDesde'] . " ";
        }
        $sqlFiltro .= ($sqlFiltro != '' ? " AND " : " ") . "v.ejr_id_clc = 2025 AND ejr_id_autorizado = 2026 ";

        $sql = "
            SELECT
                v.gpo_id, v.ur_id, v.clc_id, v.afe_id,
                concat(gpo_id, ur_id) as gur,
                v.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia, b.bdo_concepto,
                v.tdf_id AS tdf_id_ministrado, v.bdm_referencia,
                (b.bdo_percepcionPresupuestal + b.bdo_percepcionContable - b.bdo_retencionContable) as importe,
                b.bdo_percepcionPresupuestal, b.bdo_percepcionContable, b.bdo_retencionContable,
                v.msc_id,
                v.bd" . $letraAuxiliar . "_estado AS estado, DATE( v.bd" . $letraAuxiliar . "_fechaAutorizado ) AS fechaAutorizacion, DATE( v.bd" . $letraAuxiliar . "_fechaCancelado ) AS fechaCancelacion,
                v.bd" . $letraAuxiliar . "_agrupador AS agrupador,

                $polizaSql
                max(pa.gpo_id_polizaAutorizado) AS gpo_id_polizaAutorizado, max(pa.ur_id_polizaAutorizado) AS ur_id_polizaAutorizado, max(pa.ue_id_polizaAutorizado) AS ue_id_polizaAutorizado,
                max(pa.msc_id_polizaAutorizado) AS msc_id_polizaAutorizado, max(pa.pot_id_polizaAutorizado) AS pot_id_polizaAutorizado, max(pa.prf_id_polizaAutorizado) AS prf_id_polizaAutorizado,
                max(pa.pma_id_polizaAutorizado) AS pma_id_polizaAutorizado,

                pc.polizaCancelado, MAX(pc.fechaCancelado) AS fechaCancelado,
                MAX(pc.gpo_id_polizaCancelado) AS gpo_id_polizaCancelado, MAX(pc.ur_id_polizaCancelado) AS ur_id_polizaCancelado, MAX(pc.ue_id_polizaCancelado) AS ue_id_polizaCancelado,
                MAX(pc.msc_id_polizaCancelado) AS msc_id_polizaCancelado, MAX(pc.pot_id_polizaCancelado) AS pot_id_polizaCancelado, MAX(pc.prf_id_polizaCancelado) AS prf_id_polizaCancelado ,
                MAX(pc.pma_id_polizaCancelado) AS pma_id_polizaCancelado,

                CONCAT( v.gpo_id, v.ur_id, '.', LPAD( v.msc_id, 2, 000 ), '.', LPAD( v.afe_id, 6, 0000000 ), '.', LPAD( v.bdo_id, 6, 000000 ) ) AS id
            FROM " . BD_CTRL_2025 . ".cat_BancoDocumentos b
                INNER JOIN $tablaAuxiliar v USING( gpo_id, ur_id, bdo_id )
                INNER JOIN " . BD_CTRL . ".cg_MesContable ms USING( gpo_id, ur_id, msc_id )
                INNER JOIN " . BD_CTRLANT . ".cat_TipoMinistracion USING( tmi_id )
                LEFT JOIN(
                    SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado, m.ue_id AS ue_id_polizaAutorizado,
                        m.msc_id AS msc_id_polizaAutorizado, m.pot_id AS pot_id_polizaAutorizado, m.prf_id AS prf_id_polizaAutorizado,
                        m.pma_id AS pma_id_polizaAutorizado, m.pma_fecha AS fechaAutorizado,
                        CONCAT( m.pot_id, '-', m.prf_id, m.pma_id ) AS polizaAutorizado,
                        MAX(x.pdx_gpo_id_vinculacion) AS gpo_id, MAX(x.pdx_ur_id_vinculacion) AS ur_id,
                        MAX(x.pdx_afe_id_vinculacion) AS afe_id, MAX(x.pdx_bdo_id_vinculacion) AS bdo_id,
                        MAX(x.pdx_agrupador) AS agrupador
                    FROM " . BD_CTRL . ".cg_PolizaMaestro m
                        INNER JOIN " . BD_CTRL . ".cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = ? AND m.ur_id = ?
                        AND m.mmc_id = ?
                        AND m.tmc_id = 'A'
                        AND x.pdx_ejercicio = 2025
                    GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_vinculacion, x.pdx_bdo_id_vinculacion,ue_id_polizaAutorizado,msc_id_polizaAutorizado,
                    pot_id_polizaAutorizado,prf_id_polizaAutorizado,pma_id_polizaAutorizado
                )pa USING( gpo_id, ur_id, afe_id, bdo_id )
                LEFT JOIN (
                    SELECT m.gpo_id AS gpo_id_polizaCancelado, m.ur_id AS ur_id_polizaCancelado, m.ue_id AS ue_id_polizaCancelado,
                        m.msc_id AS msc_id_polizaCancelado, m.pot_id AS pot_id_polizaCancelado, m.prf_id AS prf_id_polizaCancelado,
                        m.pma_id AS pma_id_polizaCancelado, m.pma_fecha AS fechaCancelado,
                        CONCAT( m.pot_id, '-', m.prf_id, m.pma_id ) AS polizaCancelado,
                        MAX(x.pdx_gpo_id_vinculacion) AS gpo_id, MAX(x.pdx_ur_id_vinculacion) AS ur_id,
                        MAX(x.pdx_afe_id_vinculacion) AS afe_id, MAX(x.pdx_bdo_id_vinculacion) AS bdo_id,
                        MAX(x.pdx_agrupador) AS agrupador
                    FROM " . BD_CTRL . ".cg_PolizaMaestro m
                        INNER JOIN " . BD_CTRL . ".cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = ? AND m.ur_id = ?
                        AND m.mmc_id = ?
                        AND m.tmc_id = 'C'
                        AND x.pdx_ejercicio = 2025
                    GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_vinculacion, x.pdx_bdo_id_vinculacion,ue_id_polizaCancelado,msc_id_polizaCancelado,
                    pot_id_polizaCancelado,prf_id_polizaCancelado,pma_id_polizaCancelado
                )pc USING( gpo_id, ur_id, afe_id, bdo_id )
            WHERE $sqlFiltro
                $pensiones
            GROUP BY ejr_id_clc,gpo_id, ur_id,msc_id, clc_id,afe_id, bdo_id ,polizaAutorizado,polizaCancelado
        ";
        $parametrosSQL = [$gpo_id, $ur_id, $mmc_id, $gpo_id, $ur_id, $mmc_id];

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametrosSQL);
        $this->desconectar();
        $this->setParams($parametrosSQL);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = $this->getNumeroRegistros();
        $nav->idTabla = 'tbl-ministrado';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No se encontraron polizas con los criterios seleccionados","warning");');
        } else {

            $this->mixScript('
                $("#tbl-ministrado tr").on("dblclick", function (ev){
                    ev.stopPropagation();

                    var var_clcFiltro = 1;
                    var var_bdoFiltro = 1;
                    if( $("#inputClc_id").val() == "" ){
                        var_clcFiltro = 2;
                    }
                    if( $("#inputBdo_id").val() == "" && $("#inputRfc_id").val() == "" && $("#inputBdo_referencia").val() == "" ){
                        var_bdoFiltro = 2;
                    }

                    const prm = $("#tbl-ministrado").tabla("getFilaSeleccion");
                    comunica("' . $this->pantalla . '", {
                        metodo: "Documento",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: $("#page-tabs li").children(".active").attr("mes"),
                        clc_id: prm.clc_id,
                        clcFiltro: var_clcFiltro,
                        gur: prm.gur,
                        afe_id: prm.afe_id,
                        bdo_id: prm.bdo_id,
                        tdf_id: prm.tdf_id,
                        rfc_id: prm.rfc_id,
                        bdo_referencia: prm.bdo_referencia,
                        bdoFiltro: var_bdoFiltro,
                        agrupador: prm.agrupador,
                    });

                });
            ');

            $this->mixScript(
                '
                $(".panel-heading.header").remove();
                $(".panel-footer").remove();
                $(".tabla-footer").remove();'
            );
        }
    }

    private function DameInformacion()
    {
        $pensiones = (isset($this->prm->seleccion) && $this->prm->seleccion == 'pensiones' ? true : false);
        $gur = ($pensiones ? (isset($this->prm->gur) ? $this->prm->gur : '') : $this->gpo_id_2025 . $this->ur_id_2025);
        $clc_id = $this->prm->clc_id; //Informacion de acuerdo al mes
        $bdo_id = $this->prm->bdo_id; //Informacion de acuerdo al mes
        $rfc_id = $this->prm->rfc_id; //Informacion de acuerdo al mes
        $bdo_referencia = $this->prm->bdo_referencia; //Informacion de acuerdo al mes
        $periodo['mesDesde'] = $this->prm->mes; //Informacion de acuerdo al mes
        $periodo['mesHasta'] = $this->prm->mes; //Informacion de acuerdo al mes

        $reporte['tipo'] = 1;
        $programatico['epg_id'] = '';
        $ClaveFinanciamiento['ecf_id'] = '';
        $ClaveFinanciamiento['ter_id'] = '';

        $niveles[9] = 9;
        $niveles[8] = 0;
        $niveles[7] = 0;
        $niveles[6] = 6;
        $niveles[5] = 0;
        $niveles[4] = 0;
        $niveles[3] = 0;
        $niveles[2] = 0;
        $niveles[1] = 1;

        $detalle = false;

        $this->MuestraDatosPagina(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'T',
            $niveles,
            array(
                "gur" => $gur,
                "clc_id" => $clc_id,
                "bdo_id" => $bdo_id,
                "rfc_id" => $rfc_id,
                "bdo_referencia" => $bdo_referencia,
                "pensiones" => $pensiones
            )
        );

        return json_encode($datos);
    }

    private function Documento()
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = $this->gpo_id AND ur_id = $this->ur_id ");
        $conexion->consultar();

        while ($dataRow = $conexion->extraer()) {
            $mesContable[$dataRow['msc_id']] = $dataRow;
        }

        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
        $clc_id_documento = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id_documento = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');

        $clc_array = array();

        $generaPolizaAutorizado = false;
        $generaPolizaCancelado = false;

        $fechaAutorizado = '';
        $fechaCancelado = '';

        $estadoMesCancelado = false;

        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->icon = 'fa-solid fa-circle-left me-1';
        $btnRegresa->color = 'gris';

        $botones = $this->ponBoton($btnRegresa);
        $this->agregaTools($botones);

        $documento = getDocumento(
            $gpo_id_documento,
            $ur_id_documento,
            $bdo_id_documento,
            $this->prm->rfc_id,
            $this->prm->bdo_referencia,
            array("ejercicio" => intval(APP_EJERCICIO_ANTERIOR), "tdf_id" => $this->prm->tdf_id, "mmc_id" => "T")
        );
        $html = '<legend>Análisis del documento 2025 a generar la póliza del ministrado</legend>';
        $html .= '
                <div>
                    <fieldset class="col-md-12 col-xs-12">
                        <legend>Información del Documento</legend>
                        <table class="table table-bordered">
                            <tr class="success">
                                <th>Proveedor</th>
                                <th>Referencia</th>
                                <th>Concepto</th>
                                <th>Percepción Presupuestal</th>
                                <th>Percepción Contable</th>
                                <th>Retención Contable</th>
                                <th>Fecha</th>
                            </tr>
                            <tr>
                                <td>' . $documento->rfc_id . "-" . $documento->nombreProveedor . '</td>
                                <td>' . $documento->tipoDocumentoFuente . "-" . $documento->referencia . '</td>
                                <td>' . $documento->concepto . '</td>
                                <td width="10%" align="right"><span style="float:left">$</span>' . number_format($documento->percepcionPresupuestal, 2) . '</td>
                                <td width="10%" align="right"><span style="float:left">$</span>' . number_format($documento->percepcionContable, 2) . '</td>
                                <td width="10%" align="right"><span style="float:left">$</span>' . number_format($documento->retencionContable, 2) . '</td>
                                <td>' . $documento->fechaDocumento . '</td>
                            </tr>
                        </table>
                    </fieldset><br/>
            ';
        $tipoministrado = 2;
        //preguntamos el estado de los meses contables en funcion de las fechas de la ministracion
        if (!empty($documento->detalleMinistrado2025)) {
            foreach ($documento->detalleMinistrado2025 as $key => $value) {
                if ($clc_id_documento != '') {
                    if ($clc_id_documento == $value->clc_id) {
                        $clc_array[$value->clc_id] = $value->clc_id;
                    }
                } else {
                    $clc_array[$value->clc_id] = $value->clc_id;
                }

                if ($this->prm->agrupador == $value->agrupador) {
                    $tipoministrado = $value->tipoMinistracion;
                    if ($value->tipoMinistracion == 1) {
                        $generaPolizaAutorizado = true;
                        $fechaAutorizado = $value->fechaAutorizado;
                    }
                    $estadoMesAutorizado = $mesContable[str_pad($value->mesAutorizado, 2, '0', STR_PAD_LEFT)]['ems_id'];
                    if ($value->estado == 'C') {
                        if ($generaPolizaAutorizado) {
                            $generaPolizaCancelado = true;
                            $fechaCancelado = $value->fechaCancelado;
                        }
                        $estadoMesCancelado = $mesContable[str_pad(
                            $value->mesCancelado,
                            2,
                            '0',
                            STR_PAD_LEFT
                        )]['ems_id'];
                    }
                }
            } //*/

            /* INFORMACIÓN DE LA CLC EN LA QUE ESTA EL DOCUMENTO */
            if (count($clc_array) != 0) {
                $html .= '
                    <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                        <legend>CLC en la que esta el documento
                            <span id="menclc" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                        </legend>
                    ';
                foreach ($clc_array as $clc_id) {
                    $clcDocumento = pintaClc(
                        $gpo_id_documento,
                        $ur_id_documento,
                        $clc_id,
                        array("ejercicio" => intval(APP_EJERCICIO_ANTERIOR), "bdo_id" => $bdo_id_documento, "todo" => true)
                    );
                    $html .= $clcDocumento->html;
                }
                $html .= '
                        </fieldset></br>
                    ';
            }

            /* DATOS DE LA MINISTRACIÓN */
            $html .= '
                    <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                        <legend>Datos de la Ministración
                            <span id="ministrado" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                        </legend>
                ';
            $ministrado = pintaMinistrado(
                $gpo_id_documento,
                $ur_id_documento,
                $bdo_id_documento,
                array("ejercicio" => intval(APP_EJERCICIO_ANTERIOR), "clc_id" => $clc_id_documento)
            );
            $html .= $ministrado->html;
            $html .= '
                    </fieldset></br>
                ';
            if ($tipoministrado == 1) {
                /*             * *********************************** Información de la Póliza Contable ************************************************************** */
                $polizas = getPolizaAgrupador(
                    $this->gpo_id,
                    $this->ur_id,
                    $this->prm->agrupador,
                    '',
                    intval(APP_EJERCICIO_ANTERIOR)
                );

                $polizaAut = new stdClass;
                $botonGeneraPa = '';

                //preguntamos los datos de la póliza contable de autorización
                if ($polizas->valido) {
                    foreach ($polizas->polizas as $key => $value) {
                        if ($value->tipoMomento == 'A') {
                            $polizaAutorizado[$key] = getPoliza(
                                $value->gpo_id,
                                $value->ur_id,
                                $value->ue_id,
                                $value->msc_id,
                                $value->pot_id,
                                $value->prf_id,
                                $value->pma_id
                            );
                            $polizaAut->valido = $polizaAutorizado[$key]->valido;
                        }
                    }
                }
                if (!isset($polizaAut->valido)) {
                    $polizaAut->valido = false;
                }
                //si existe una póliza
                if (isset($polizaAutorizado)) {
                    foreach ($polizaAutorizado as $key => $value) {
                        if ($value->valido) {
                            $polizaAutorizadoHtml = pintaPoliza($polizaAutorizado[$key]);
                            $html .= $polizaAutorizadoHtml->html;
                            //si el mes NO esta cerrado
                            if ($this->escritura) {
                                if ($estadoMesAutorizado == 'A') {
                                    //podra regenerar la póliza contable
                                    $btnGenera = new stdClass();
                                    $btnGenera->id = 'btnReGeneraPolizaA';
                                    $btnGenera->name = 'btnReGeneraPolizaA';
                                    $btnGenera->text = 'Re-Genera Póliza de Autorización';
                                    $btnGenera->value = 'Re-Genera Póliza de Autorización';
                                    $btnGenera->color = 'verde';
                                    $btnGenera->icon = 'tasks';
                                    $botonGeneraPa = $this->ponBoton($btnGenera);

                                    $this->mixScript('
                                        $("#btnReGeneraPolizaA").click(function(){
                                            alertify.confirm("RE HACER LA POLIZA ' . (isset($value->poliza) ? $value->poliza : '') . '", "Esta acción re construira la póliza de autorización como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
                                                function(){
                                                    comunica("' . $this->pantalla . '",{
                                                        metodo: "GeneraPoliza",
                                                        origen: "' . $this->origen . '",
                                                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                                        mes: ' . $this->prm->mes . ',
                                                        gur: ' . $this->prm->gur . ',
                                                        clc_id: "' . $this->prm->clc_id . '",
                                                        clcFiltro: ' . $this->prm->clcFiltro . ',
                                                        bdo_id: ' . $this->prm->bdo_id . ',
                                                        tdf_id: "' . $this->prm->tdf_id . '",
                                                        rfc_id: "' . $this->prm->rfc_id . '",
                                                        bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                                        bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                                        gpo_id: "' . $value->gpo_id . '",
                                                        ur_id: "' . $value->ur_id . '",
                                                        ue_id: "' . $value->ue_id . '",
                                                        fecha: "' . $fechaAutorizado . '",
                                                        agrupador: "' . $this->prm->agrupador . '",
                                                        tmc_id: "A"
                                                    });
                                                },
                                                function(){},
                                            ).set("defaultFocus","cancel").set("labels", {ok:"Proceder", cancel:"Cancelar"});
                                        });
                                    ');
                                } else {
                                    $botonGeneraPa = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización esta cerrado</div>';
                                }
                            } elseif ($estadoMesAutorizado == 'A') {
                                $botonGeneraPa = '<div style="display:inline-block; color:#900; padding:10px;">Regenerar Póliza</div>';
                            } elseif ($estadoMesAutorizado == 'C') {
                                $botonGeneraPa = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización esta cerrado</div>';
                            }
                        }
                    }
                }

                $polizaCan = new stdClass;
                $botonGeneraPc = '';

                //preguntamos los datos de la póliza contable de cancelación
                if ($polizas->valido) {
                    foreach ($polizas->polizas as $key => $value) {
                        if ($value->tipoMomento == 'C') {
                            $polizaCancelado[$key] = getPoliza(
                                $value->gpo_id,
                                $value->ur_id,
                                $value->ue_id,
                                $value->msc_id,
                                $value->pot_id,
                                $value->prf_id,
                                $value->pma_id
                            );
                            $polizaCan->valido = $polizaCancelado[$key]->valido;
                        }
                    }
                }
                if (!isset($polizaCan->valido)) {
                    $polizaCan->valido = false;
                }

                //si existe tal póliza
                if (isset($polizaCancelado)) {
                    foreach ($polizaCancelado as $key => $value) {
                        if ($value->valido) {
                            $polizaCanceladoHtml = pintaPoliza($polizaCancelado[$key]);
                            $html .= $polizaCanceladoHtml->html;
                            //si el mes cpntable NO esta cerrado
                            if ($this->escritura) {
                                if ($estadoMesCancelado == 'A') {
                                    //podra regenerar la póliza contable
                                    $btnGenera = new stdClass();
                                    $btnGenera->id = 'btnReGeneraPolizaC';
                                    $btnGenera->name = 'btnReGeneraPolizaC';
                                    $btnGenera->text = 'Re-Genera Póliza de Cancelación';
                                    $btnGenera->value = 'Re-Genera Póliza de Cancelación';
                                    $btnGenera->color = 'verde';
                                    $btnGenera->icon = 'tasks';
                                    $botonGeneraPc = $this->ponBoton($btnGenera);

                                    $this->mixScript('
                                        $("#btnReGeneraPolizaC").click(function(){
                                        alertify.confirm("RE HACER LA POLIZA ' . (isset($value->poliza) ? $value->poliza : '') . '","Esta acción re construira la póliza de cancelación como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
                                            function(){
                                                comunica("' . $this->pantalla . '",{
                                                    metodo: "GeneraPoliza",
                                                    origen: "' . $this->origen . '",
                                                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                                    mes: ' . $this->prm->mes . ',
                                                    gur: ' . $this->prm->gur . ',
                                                    clc_id: "' . $this->prm->clc_id . '",
                                                    clcFiltro: ' . $this->prm->clcFiltro . ',
                                                    bdo_id: ' . $this->prm->bdo_id . ',
                                                    tdf_id: "' . $this->prm->tdf_id . '",
                                                    rfc_id: "' . $this->prm->rfc_id . '",
                                                    bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                                    bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                                    gpo_id: "' . $value->gpo_id . '",
                                                    ur_id: "' . $value->ur_id . '",
                                                    ue_id: "' . $value->ue_id . '",
                                                    fecha: "' . $fechaCancelado . '",
                                                    agrupador: "' . $this->prm->agrupador . '",
                                                    tmc_id: "C"
                                                });
                                            },
                                            function(){},
                                        ).set("defaultFocus","cancel").set("labels", {ok:"Proceder", cancel:"Cancelar"});
                                        });
                                    ');
                                } else {
                                    $botonGeneraPc = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado </div>';
                                }
                            } elseif ($estadoMesCancelado == 'A') {
                                $botonGeneraPc = '<div style="display:inline-block; color:#900; padding:10px;">Regenerar Póliza</div>';
                            } elseif ($estadoMesCancelado == 'C') {
                                $botonGeneraPc = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado</div>';
                            }
                        }
                    }
                }

                $botones = '';

                if ($this->escritura) {
                    //si deberia generar la póliza y NO existe
                    if ($generaPolizaAutorizado && !$polizaAut->valido) {
                        $btnGeneraPoliza = new stdClass();
                        $btnGeneraPoliza->id = 'btnGeneraPolizaA';
                        $btnGeneraPoliza->name = 'btnGeneraPolizaA';
                        $btnGeneraPoliza->text = 'Genera Póliza de Autorización';
                        $btnGeneraPoliza->value = 'Genera Póliza de Autorización';
                        $btnGeneraPoliza->color = 'verde';
                        $btnGeneraPoliza->icon = 'ok';

                        $botones = $this->ponBoton($btnGeneraPoliza);
                        //si el mes contable se encuentra abierto
                        if ($estadoMesAutorizado == 'A') {
                            $this->mixScript('
                                $("#btnGeneraPolizaA").click(function(){
                                    comunica("' . $this->pantalla . '",{
                                        metodo: "GeneraPoliza",
                                        origen: "' . $this->origen . '",
                                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                        mes: ' . $this->prm->mes . ',
                                        gur: ' . $this->prm->gur . ',
                                        clc_id: "' . $this->prm->clc_id . '",
                                        clcFiltro: ' . $this->prm->clcFiltro . ',
                                        bdo_id: ' . $this->prm->bdo_id . ',
                                        tdf_id: "' . $this->prm->tdf_id . '",
                                        rfc_id: "' . $this->prm->rfc_id . '",
                                        bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                        bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                        fecha: "' . $fechaAutorizado . '",
                                        agrupador: "' . $this->prm->agrupador . '",
                                        tmc_id: "A"
                                    });
                                });
                            ');
                        } else {
                            //en caso contrario, debera pedir la fecha donde debe generarse
                            $btnGeneraPolizaCerrado = new stdClass();
                            $btnGeneraPolizaCerrado->id = 'btnGeneraPolizaCerrado';
                            $btnGeneraPolizaCerrado->name = 'btnGeneraPolizaCerrado';
                            $btnGeneraPolizaCerrado->text = 'Genera Póliza de Autorización';
                            $btnGeneraPolizaCerrado->value = 'Genera Póliza de Autorización';
                            $btnGeneraPolizaCerrado->color = 'verde';
                            $btnGeneraPolizaCerrado->icon = 'ok';

                            $inputFecha = new stdClass();
                            $inputFecha->id = "cap_fecha";
                            $inputFecha->name = "control";
                            $inputFecha->class = 'fecha';
                            $inputFecha->label = '';
                            $inputFecha->enDiv = true;
                            $inputFecha->formato = 'fecha';
                            $inputFecha->labelClass = '_32 txtL';
                            $inputFecha->required = true;

                            $btnCancelar = new stdClass();
                            $btnCancelar->id = 'btnCancelar';
                            $btnCancelar->icon = 'block';
                            $btnCancelar->name = 'nuevo';
                            $btnCancelar->color = 'rojo';
                            $btnCancelar->text = 'Cancelar';
                            $btnCancelar->value = 'Cancelar';

                            $html .= '
                                <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                                    <div id="capfecha">
                                        <legend>Mes del Ministrado2025 esta cerrado, capture una nueva fecha</legend>
                                        <center>
                                            ' . $this->ponInput($inputFecha) . '
                                        </center>
                                        <hr>
                                            ' . $this->ponBoton($btnGeneraPolizaCerrado) . '
                                            ' . $this->ponBoton($btnCancelar) . '
                                    </div>
                                </div>
                            ';

                            $this->mixScript('
                                $("#btnGeneraPolizaA").click(function(){
                                    if($("#bg_capfecha").length != 0 ){
                                        $(this).hide();
                                        $("#bg_capfecha").show();
                                    }
                                });

                                $("#btnGeneraPolizaCerrado").click(function(){
                                    comunica("' . $this->pantalla . '",{
                                        metodo: "GeneraPoliza",
                                        origen: "' . $this->origen . '",
                                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                        mes: ' . $this->prm->mes . ',
                                        gur: ' . $this->prm->gur . ',
                                        clc_id: "' . $this->prm->clc_id . '",
                                        clcFiltro: ' . $this->prm->clcFiltro . ',
                                        bdo_id: ' . $this->prm->bdo_id . ',
                                        tdf_id: "' . $this->prm->tdf_id . '",
                                        rfc_id: "' . $this->prm->rfc_id . '",
                                        bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                        bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                        agrupador: "' . $this->prm->agrupador . '",
                                        fecha: $("#cap_fecha").val(),
                                        tmc_id: "A"
                                    });
                                });

                                $("#btnCancelar").click(function(){
                                    $("#bg_capfecha").hide();
                                    $("#btnGeneraPoliza").show();
                                });
                            ');
                        }
                    } //*/
                } elseif ($estadoMesAutorizado == 'A') {
                    $botones = '<div style="display:inline-block; color:#900; padding:10px;">Generar Póliza</div>';
                } elseif ($estadoMesAutorizado == 'C') {
                    $botones = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización esta cerrado</div>';
                }

                if ($this->escritura) {
                    if ($generaPolizaAutorizado && $polizaAut->valido && $generaPolizaCancelado && !$polizaCan->valido) {

                        $btnGeneraPoliza = new stdClass();
                        $btnGeneraPoliza->id = 'btnGeneraPolizaC';
                        $btnGeneraPoliza->name = 'btnGeneraPolizaC';
                        $btnGeneraPoliza->text = 'Genera Póliza de Cancelación';
                        $btnGeneraPoliza->value = 'Genera Póliza de Cancelación';
                        $btnGeneraPoliza->color = 'verde';
                        $btnGeneraPoliza->icon = 'ok';

                        $botones = $this->ponBoton($btnGeneraPoliza);

                        if ($estadoMesCancelado == 'A') {
                            $this->mixScript('
                                $("#btnGeneraPolizaC").click(function(){
                                    comunica("' . $this->pantalla . '",{
                                        metodo: "GeneraPoliza",
                                        origen: "' . $this->origen . '",
                                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                        mes: ' . $this->prm->mes . ',
                                        gur: ' . $this->prm->gur . ',
                                        clc_id: "' . $this->prm->clc_id . '",
                                        clcFiltro: ' . $this->prm->clcFiltro . ',
                                        bdo_id: ' . $this->prm->bdo_id . ',
                                        tdf_id: "' . $this->prm->tdf_id . '",
                                        rfc_id: "' . $this->prm->rfc_id . '",
                                        bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                        bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                        agrupador: "' . $this->prm->agrupador . '",
                                        fecha: "' . $fechaCancelado . '",
                                        tmc_id: "C"
                                    });
                                });
                            ');
                        } else {
                            $btnGeneraPolizaCerrado = new stdClass();
                            $btnGeneraPolizaCerrado->id = 'btnGeneraPolizaCerrado';
                            $btnGeneraPolizaCerrado->name = 'btnGeneraPolizaCerrado';
                            $btnGeneraPolizaCerrado->text = 'Genera Póliza de Cancelación';
                            $btnGeneraPolizaCerrado->value = 'Genera Póliza de Cancelación';
                            $btnGeneraPolizaCerrado->color = 'verde';
                            $btnGeneraPolizaCerrado->icon = 'ok';

                            $inputFecha = new stdClass();
                            $inputFecha->id = "cap_fecha";
                            $inputFecha->name = "control";
                            $inputFecha->class = 'fecha';
                            $inputFecha->label = '';
                            $inputFecha->enDiv = true;
                            $inputFecha->formato = 'fecha';
                            $inputFecha->labelClass = '_32 txtL';
                            $inputFecha->required = true;

                            $btnCancelar = new stdClass();
                            $btnCancelar->id = 'btnCancelar';
                            $btnCancelar->icon = 'block';
                            $btnCancelar->name = 'nuevo';
                            $btnCancelar->color = 'rojo';
                            $btnCancelar->text = 'Cancelar';
                            $btnCancelar->value = 'Cancelar';

                            $html .= '
                                <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                                    <div id="capfecha">
                                        <legend>Mes de cancelación del Ministrado2024 esta cerrado, capture una nueva fecha</legend>
                                        <center>
                                        ' . $this->ponInput($inputFecha) . '
                                        </center>
                                        <hr>
                                        ' . $this->ponBoton($btnGeneraPolizaCerrado) . '
                                        ' . $this->ponBoton($btnCancelar) . '
                                    </div>
                                </div>
                            ';

                            $this->mixScript('
                                $("#btnGeneraPolizaC").click(function(){
                                    if($("#bg_capfecha").length != 0 ){
                                        $(this).hide();
                                        $("#bg_capfecha").show();
                                    }
                                });

                                $("#btnGeneraPolizaCerrado").click(function(){
                                    comunica("' . $this->pantalla . '",{
                                        metodo: "GeneraPoliza",
                                        origen: "' . $this->origen . '",
                                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                        mes: ' . $this->prm->mes . ',
                                        gur: ' . $this->prm->gur . ',
                                        clc_id: "' . $this->prm->clc_id . '",
                                        clcFiltro: ' . $this->prm->clcFiltro . ',
                                        bdo_id: ' . $this->prm->bdo_id . ',
                                        tdf_id: "' . $this->prm->tdf_id . '",
                                        rfc_id: "' . $this->prm->rfc_id . '",
                                        bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                        bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                        agrupador: "' . $this->prm->agrupador . '",
                                        fecha: $("#cap_fecha").val(),
                                        tmc_id: "C"
                                    });
                                });

                                $("#btnCancelar").click(function(){
                                    $("#bg_capfecha").hide();
                                    $("#btnGeneraPoliza").show();
                                });
                            ');
                        }
                    } //*/
                } elseif ($estadoMesCancelado == 'A') {
                    $botones = '<div style="display:inline-block; color:#900; padding:10px;">Generar Póliza</div>';
                } elseif ($estadoMesCancelado == 'C') {
                    $botones = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado</div>';
                }

                $this->agregaTools($botones);
            } /// tipo de ministrado
        }
        $this->mixHtml($html);

        $this->setContenedor('botonA');
        if (isset($botonGeneraPa)) {
            $this->mixHtml($botonGeneraPa);
        }
        $this->setContenedor('botonC');
        if (isset($botonGeneraPc)) {
            $this->mixHtml($botonGeneraPc);
        }

        $this->mixScript('
                $("#btnRegresa").click(function(){
                    comunica("' . $this->pantalla . '", {
                        metodo: "pintaBotonesVinculaciones",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: ' . $this->prm->mes . ',
                        gur: ' . $this->prm->gur . ',
                        clc_id: "' . $this->prm->clc_id . '",
                        clcFiltro: ' . $this->prm->clcFiltro . ',
                        bdo_id: ' . $this->prm->bdo_id . ',
                        tdf_id: "' . $this->prm->tdf_id . '",
                        rfc_id: "' . $this->prm->rfc_id . '",
                        bdo_referencia: "' . $this->prm->bdo_referencia . '",
                        bdoFiltro: ' . $this->prm->bdoFiltro . '
                    });
                });
            ');
    }

    private function GeneraPoliza()
    {
        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
        if (isset($this->prm->fecha) && $this->prm->fecha != '') {
            $fecha = $this->prm->fecha;
            $fechaValidar = new stdClass();
            $fechaValidar->gpo_id = $gpo_id_documento;
            $fechaValidar->ur_id = $ur_id_documento;
            $fechaValidar->fecha = $fecha;
            $fechaValidar->anio = APP_EJERCICIO;
            $res = validaFecha($fechaValidar);

            if (!$res->valido) {
                $this->mixScript('notify("error:' . $res->mensaje . '","error");');
                return false;
            }
            if (!$res->anio) {
                $this->mixScript('notify("El año es incorrecto","error");');
                return false;
            }
            if ($res->edoMesContable == 'C') {
                $this->mixScript('notify("El mes esta cerrado","error");');
                return false;
            }
            $fecha = $res->fechaMysql;
        } else {
            $this->mixScript('notify("Error en la fecha","error");');
            return false;
        }

        $bandera = 0;
        $mensajesp = "ERROR";

        if (isset($this->prm->tmc_id) && $this->prm->tmc_id == 'C') {
            $generaPolizaMinistrado = new sp_MinistradoCancelado2025();
            $generaPolizaMinistrado->setGpo_id($gpo_id_documento);
            $generaPolizaMinistrado->setUr_id($ur_id_documento);
            $generaPolizaMinistrado->setBdm_agrupador($this->prm->agrupador);
            $generaPolizaMinistrado->setPma_fecha($fecha);
            $generaPolizaMinistrado->setTmc_id($this->prm->tmc_id);
            $generaPolizaMinistrado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DE LA MINISTRACIÓN " . APP_EJERCICIO_ANTERIOR);
            $generaPolizaMinistrado->setEjercicio(intval(APP_EJERCICIO_ANTERIOR));
            $generaPolizaMinistrado->setSis_id("SCG");
            $generaPolizaMinistrado->execute(false, false);
            $bandera = $generaPolizaMinistrado->getBandera();
            $mensajesp = $generaPolizaMinistrado->getMensaje();
        } else {
            $generaPolizaMinistrado = new sp_MinistradoAutorizado2025();
            $generaPolizaMinistrado->setGpo_id($gpo_id_documento);
            $generaPolizaMinistrado->setUr_id($ur_id_documento);
            $generaPolizaMinistrado->setBdm_agrupador($this->prm->agrupador);
            $generaPolizaMinistrado->setPma_fecha($fecha);
            $generaPolizaMinistrado->setTmc_id($this->prm->tmc_id);
            $generaPolizaMinistrado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DE LA MINISTRACIÓN " . APP_EJERCICIO_ANTERIOR);
            $generaPolizaMinistrado->setEjercicio(intval(APP_EJERCICIO_ANTERIOR));
            $generaPolizaMinistrado->setSis_id("SCG");
            $generaPolizaMinistrado->execute(false, false);
            $bandera = $generaPolizaMinistrado->getBandera();
            $mensajesp = $generaPolizaMinistrado->getMensaje();
        }

        $mensaje = "";
        $tipo = "";

        if ($bandera != 1) {
            $mensaje = "ERROR:" . $mensajesp;
            $tipo = "error";
        } else {
            $mensaje = $mensajesp;
            $tipo = "success";
        }

        if (isset($this->prm->tmc_id) && $this->prm->tmc_id == 'C') {
            $this->mixScript('
                notify("' . $mensaje . '","' . $tipo . '");
                comunica("' . $this->pantalla . '",{
                    metodo: "Documento",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                    mes: ' . $this->prm->mes . ',
                    gur: ' . $this->prm->gur . ',
                    clc_id: "' . $this->prm->clc_id . '",
                    clcFiltro: ' . $this->prm->clcFiltro . ',
                    bdo_id: ' . $this->prm->bdo_id . ',
                    tdf_id: "' . $this->prm->tdf_id . '",
                    rfc_id: "' . $this->prm->rfc_id . '",
                    bdo_referencia: "' . $this->prm->bdo_referencia . '",
                    bdoFiltro: ' . $this->prm->bdoFiltro . ',
                    agrupador: "' . $this->prm->agrupador . '",
                    fecha: "' . $fecha . '",
                    tmc_id: "C"
                });
            ');
        } else {
            $this->mixScript('
                notify("' . $mensaje . '","' . $tipo . '");
                comunica("' . $this->pantalla . '",{
                    metodo: "Documento",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                    mes: ' . $this->prm->mes . ',
                    gur: ' . $this->prm->gur . ',
                    clc_id: "' . $this->prm->clc_id . '",
                    clcFiltro: ' . $this->prm->clcFiltro . ',
                    bdo_id: ' . $this->prm->bdo_id . ',
                    tdf_id: "' . $this->prm->tdf_id . '",
                    rfc_id: "' . $this->prm->rfc_id . '",
                    bdo_referencia: "' . $this->prm->bdo_referencia . '",
                    bdoFiltro: ' . $this->prm->bdoFiltro . ',
                    agrupador: "' . $this->prm->agrupador . '",
                    fecha: "' . $fecha . '",
                    tmc_id: "A"
                });
            ');
        }
    }
}
//Permisos de acceso al controlador
if (isset($_SESSION[_S]['prm']['C']['17']['20']['1'])) {
    new ControladorMinistrado2025();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
