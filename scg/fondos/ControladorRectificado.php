<?php
//librerias
DEFINE("LINEAS_POR_PAGINA", 1000);
session_start();
require_once dirname(__FILE__).'/../../lib/getDocumento.php';
require_once dirname(__FILE__).'/../../lib/opcionConteo.php';
require_once dirname(__FILE__).'/../../lib/pintaClc.php';
require_once dirname(__FILE__).'/../../lib/pintaRectificacion.php';
require_once dirname(__FILE__).'/../../lib/scg/vinculacion/vinculacionRectificado.php';
require_once dirname(__FILE__).'/../../lib/scg/getPoliza.php';
require_once dirname(__FILE__).'/../../lib/scg/getPolizaAgrupador.php';
require_once dirname(__FILE__).'/../../lib/scg/pintaPoliza.php';
require_once dirname(__FILE__).'/../../lib/validaFecha.php';
require_once dirname(__FILE__).'/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__).'/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__).'/../../ent/UnidadResponsable.php';

class ControladorRectificado extends WebResponse
{
    //variables
    public $usr_ip;
    public $usr_id;
    public $origen;
    public $gpo_id;
    public $ur_id;
    public $ue_id;
    public $escritura;

    //Constructor
    public function __construct()
    {
       
        //mandar a llamar constructor base
        parent::__construct();
        //Permisos de acceso a las tareas del controlador
        $this->escritura = isset($_SESSION[_S]['prm']['C']['12']['4']['2']) ? TRUE
                : FALSE;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = FALSE;
        }

        $this->usr_ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip']
            != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $this->usr_id = $_SESSION[_S]['usr_id'];

        $this->origen = isset($this->prm->origen) ? $this->prm->origen : 'opcion';
        if ($this->origen == 'opcion')
                $this->prm->mes = isset($this->prm->mes) ? $this->prm->mes : DATE("m");

        switch ($this->metodo) {
            case 'DibujaPantalla': $this->DibujaPantalla();
                break;
            case 'MuestraDatos': $this->MuestraDatos();
                break;
            case 'MuestraDatosPagina': $this->MuestraDatosPagina();
                break;
            case 'Documento': $this->Documento();
                break;
            case 'ObraActividad': $this->ObraActividad();
                break;
            case 'ClaveFinanciamiento': $this->ClaveFinanciamiento();
                break;
            case 'MesHasta': $this->MesHasta();
                break;
            case 'GeneraPoliza': $this->GeneraPoliza();
                break;
            case 'PonBarraBotones': $this->PonBarraBotones();
                break;
            default:
                opcionConteo($_POST['opcion']['sis_id'],
                    $_POST['opcion']['men_id'], $_POST['opcion']['opc_id']);
                $this->PonBarraBotones();
                break;
        }

        $this->returnJson();
    }

    private function PonBarraBotones()
    {

        // Botones y opciones que se dibujarán en la barra de botones
        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->class = 'btn btn-outline-seconday me-2';
        $btnRegresa->color = 'gris';
        $btnRegresa->icon = 'circle-left me-2';

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar';
        $btnLimpiar->value = '&nbsp;&nbsp;Limpiar';
        $btnLimpiar->type = 'button';
        $btnLimpiar->class = "btn btn-test";

        $rctFiltro = FALSE;
        $clcFiltro = FALSE;
        $bdoFiltro = FALSE;

        switch ($this->origen) {
            case "clc":
                $clcFiltro = TRUE;
                break;
            case "bdo":
                $bdoFiltro = TRUE;
                break;
            default:
                if (isset($this->prm->rctFiltro) && $this->prm->rctFiltro == 1) {
                    $rctFiltro = TRUE;
                }
                if (isset($this->prm->clcFiltro) && $this->prm->clcFiltro == 1) {
                    $clcFiltro = TRUE;
                }
                if (isset($this->prm->bdoFiltro) && $this->prm->bdoFiltro == 1) {
                    $bdoFiltro = TRUE;
                }
                break;
        }

        $inputGur = new StdClass();
        $inputGur->id = 'inputGur';
        $inputGur->name = 'inputGur';
        $inputGur->placeholder = 'UR';
        $inputGur->enDiv = true; 
        $inputGur->value = (isset($this->prm->gur) ? $this->prm->gur : "");
        $inputGur->maxlength = '3';

        $inputRct = new StdClass();
        $inputRct->id = 'inputRct_id';
        $inputRct->name = 'inputRct_id';
        $inputRct->placeholder = 'RCT';
        $inputRct->enDiv = true;
        $inputRct->value = ($rctFiltro ? $this->prm->rct_id : "");
        $inputRct->maxlength = '6';

        $inputClc = new StdClass();
        $inputClc->id = 'inputClc_id';
        $inputClc->name = 'inputClc_id';
        $inputClc->placeholder = 'CLC';
        $inputClc->enDiv = true;
        $inputClc->value = ($clcFiltro ? $this->prm->clc_id : "");
        $inputClc->maxlength = '6';

        $inputBdo = new StdClass();
        $inputBdo->id = 'inputBdo_id';
        $inputBdo->name = 'inputBdo_id';
        $inputBdo->placeholder = '# Documento';
        $inputBdo->enDiv = true;
        $inputBdo->value = ($bdoFiltro ? $this->prm->bdo_id : "");
        $inputBdo->maxlength = '7';

        $inputRfc = new StdClass();
        $inputRfc->id = 'inputRfc_id';
        $inputRfc->name = 'inputRfc_id';
        $inputRfc->placeholder = 'R.F.C.';
        $inputRfc->enDiv = true;
        $inputRfc->value = ($bdoFiltro ? $this->prm->rfc_id : "");
        $inputRfc->maxlength = '20';

        $inputReferencia = new StdClass();
        $inputReferencia->id = 'inputBdo_referencia';
        $inputReferencia->name = 'inputBdo_referencia';
        $inputReferencia->placeholder = 'Referencia';
        $inputReferencia->enDiv = true;
        $inputReferencia->value = ($bdoFiltro ? $this->prm->bdo_referencia : "");
        $inputReferencia->maxlength = '100';

        $btnExcel = new stdClass();
        $btnExcel->id = 'excelRectificado';
        $btnExcel->name = 'excelRectificado';
        $btnExcel->text = 'Excel';
        $btnExcel->value = '  Excel';
        $btnExcel->class = "btn btn-successs me-1";
        $btnExcel->color = "verde";
        $btnExcel->icon = "fa-solid fa-file-excel";
        $btnExcel->click = '
            var form = $("<form>")
                .attr("action", "rep/excel/scg/vinculacion/exp_Rectificado.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "5" ))
                .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "8" ))

                .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "R" ))
                .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs li").children(".active").attr("mes") ))
                .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))
                .append($("<input>").attr("type", "hidden").attr("name", "inputRct_id").attr("value", $("#inputRct_id").val() ))
                .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val() ))
                .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_id").attr("value", $("#inputBdo_id").val() ))
                .append($("<input>").attr("type", "hidden").attr("name", "inputRfc_id").attr("value", $("#inputRfc_id").val() ))
                .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_referencia").attr("value", $("#inputBdo_referencia").val() ))
 
                $("body").append(form);
                var mr = $("#page-tabs li").children(".active").attr("mes");
                if(mr == undefined){
                    notify("Debe seleccionar un mes","error");
                }else{
                    form.submit();
                }
        ';

        //Viene desde  Fondos Vinculacion CLc
        if (isset($this->prm->origen) && $this->prm->origen == "clc") {
            $inputGur->disabled = true;            
            $inputRct->disabled = true;            
            $inputClc->disabled = true;
        }

        $botones = '
                ' . ((isset($this->prm->origen) && $this->prm->origen == "clc") ?
                    '<div class="row col-md-2 col-xs-2">
                        <fieldset class="col-xs-2 col-xs-2">' .
                            $this->ponBoton($btnRegresa) .'
                        </fieldset>
                    </div>' :'') . '
            <div class="row col-md-12 col-xs-12">
                ' . ((isset($this->prm->origen) && $this->prm->origen == "clc") ?
               '<fieldset class="col-md-3 col-xs-3 ">' .
                    $this->ponInput($inputGur) .'
                </fieldset>                
                <fieldset class="col-md-3 col-xs-3 ">' .
                    $this->ponInput($inputRct) .'
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($inputClc) . '
                </fieldset> 
                <fieldset class="col-xs-3 col-md-3">                   
                    ' . $this->ponBoton($btnExcel) . '
                </fieldset> ': '
                <fieldset class="col-md-3 col-xs-3">' .
                    $this->ponInput($inputGur) .'
                </fieldset>                
                <fieldset class="col-md-3 col-xs-3">' .
                    $this->ponInput($inputRct) .'
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($inputClc) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($inputBdo) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($inputRfc) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($inputReferencia) . '
                </fieldset>
                <fieldset class="col-xs-3 col-md-3">                   
                    ' . $this->ponBoton($btnExcel) . '
                </fieldset>
                ') . '
            </div>';


        
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
        $p1 = [$this->gpo_id, $this->ur_id];
        $this->consultar($p1);
        $this->desconectar();

        while ($row = $this->extraer()) {
            if ($this->prm->mes == $row['msc_id']) $inicio = 'active';
            else $inicio = '';

            $pestana = str_replace('ultimo', '', $pestana);
            $pestana = str_replace('lastdiv', '', $pestana);

            $pestana .= '
                    <li class="nav-item" opcion="' . $row['msc_id'] . '" ultimo>
                        <a class="nav-link ' . $inicio . ' meses" mes="' . $row['msc_id'] . '" estado="' . $row['estado'] . '"> ' . $row['msc_alias'] . ' </a> lastdiv
                    </li>
                ';

            $contenedor .= '
                    <div id="contenedor' . $row['msc_id'] . '" style="display:none" class="contenedores"></div>
                ';
            
        }

        $pestana = str_replace('ultimo', 'class="last"', $pestana);
        $pestana = str_replace('lastdiv', '<div class="last"></div>', $pestana);

        $this->mixHtml('<div id="page-tabs"><ul class="nav nav-tabs">' . $pestana . '</ul></div>');

        // Dibujamos el contenido
        switch ($this->origen) {
            case 'opcion':
                $this->mixScript('
                        $("#btnRegresa").click(function(e){
                            e.preventDefault();
                            $("nav").menusinpres("clickMenu");
                            e.stopPropagation();
                        });                      

                        comunica("'.$this->pantalla.'",{
                            metodo: "MuestraDatos",
                            origen: "'.$this->origen.'",
                            seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion
                            : "" ).'",
                            mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                            gur: $("#inputGur").val(),
                            rct_id: $("#inputRct_id").val(),
                            clc_id: $("#inputClc_id").val(),
                            bdo_id: $("#inputBdo_id").val(),
                            rfc_id: $("#inputRfc_id").val(),
                            bdo_referencia: $("#inputBdo_referencia").val()
                        });
                    ');
                break;
            case 'clc':
                $this->mixScript('
                        $("#btnRegresa").click(function(e){
                            e.preventDefault();
                            comunica("scg/fondos/ControladorVinculacionClcFondos.php", {
                                metodo: "PonBarraBotones",
                                bandera : "btnR"
                            });
                            e.stopPropagation();
                        });

                        comunica("'.$this->pantalla.'",{
                            metodo: "MuestraDatos",
                            origen: "'.$this->origen.'",
                            seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion
                            : "" ).'",
                            gur: $("#inputGur").val(),
                            clc_id: $("#inputClc_id").val(),
                            rct_id: $("#inputRct_id").val(),
                            bdo_id: $("#inputBdo_id").val(),
                            rfc_id: $("#inputRfc_id").val(),
                            bdo_referencia: $("#inputBdo_referencia").val()
                        });
                    ');
                break;
        }

        $this->mixScript('
                $("#opciones").click( function(e){
                    comunica("'.$this->pantalla.'", {
                        metodo: "Filtro",
                        origen: "'.$this->origen.'",
                        mes:  $("#page-tabs li").children(".active").attr("mes"),
                        gur: $("#inputGur").val(),
                        rct_id: $("#inputRct_id").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });
                });

                $("#btnLimpiar").click( function(e){
                    $("#inputGur").val("");
                    $("#inputRct_id").val("");
                    $("#inputClc_id").val("");
                    $("#inputBdo_id").val("");
                    $("#inputRfc_id").val("");
                    $("#inputBdo_referencia").val("");

                    e.preventDefault();
                    var seleccionado = $(".meses");
                    $("#page-tabs li ").each(function (index) { $(this).children("a").removeClass("active"); });
                    seleccionado.closest("li").addClass("active");
                    
                    comunica("'.$this->pantalla.'",{
                        metodo: "MuestraDatos",
                        mes: seleccionado.attr( "mes" ),
                        origen: "'.$this->origen.'",
                        gur: $("#inputGur").val(),
                        rct_id: $("#inputRct_id").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });

                    e.stopPropagation();
                    return false;
                });

                $("#imprimirRectificado").click( function(e) {
                        var form = $("<form>")
                            .attr("action", "rep/doc/scg/reporte.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "5" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "8" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "R" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs li").children(".active").attr("mes") ))

                            .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputRct_id").attr("value", $("#inputRct_id").val() ))
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

        $this->DibujaPantalla();
    }

    private function DibujaPantalla()
    {
        $html = '<div id="myGridRectificado" class="tabla-contenedor">
                <div class="tabla-contenedor col-md-12" style="overflow: scroll; max-height: 400px;">
                    <table id="tabla-fondos-rectificado" class="table table-striped table-bordered">
                        <div class="col-md-12 row" style="text-align:center">
                            <thead class="sticky-top" style="z-index:100;">
                                <tr class="warning" role="row">
                                    <th class="warning" role="columnheader" style= "text-align:center">AFE</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">DOC</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">TDF</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">RFC</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">REFERENCIA</th>                                    
                                    <th class="warning" role="columnheader" style= "text-align:center">RCT</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">CONCEPTO</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">IMPORTE</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">ESTADO</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">AUTORIZACIÓN</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">POLIZA AUT.</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">FECHA</th>
                                </tr>
                            </thead>
                        </div>
                        <tbody></tbody>
                    </table> 
                </div>
            </div> 
            <div class="tabla-footer">
                <div class="p-2 border flex-wrap"> 
                        <div class="d-flex flex-row">
                            <div class="flex-grow-1 p-1">
                                <label id="rA-tbl-vinculacion"></label>
                                <label id="rB-tbl-vinculacion"></label> 
                                <label id="rTotal-tbl-vinculacion"></label>
                            </div>
                            <div id="RectificadoPaginador" class="flex-grow-2 justify-content-center p-1" ></div>
                        </div>
                    </div>
                </div>
            </div>';
        $this->mixHtml($html);

        $this->mixStyle('
            .tabla-contenedor table > tbody > tr.coloreaRectificado > td {
                background-color: #000;
                background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABsAAAAoCAYAAAAPOoFWAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAPZJREFUeNq81tsOgjAMANB2ov7/7ypaN7IlIwi9rGuT8QSc9EIDAsAznxvY4pXPKr05RUE5MEVB+TyWfCEl9LZApYopCmo9C4FKSMtYoI8Bwv79aQJU4l6hXXCZrQbokJEksxHo9KMOgc6w1atHXM8K9DVC7FQnJ0i8iK3QooGgbnyKgMDygBWyYFZoqx4qS27KqLZJjA1D0jK6QJcYEQEiWv9PGkTsbqxQ8oT+ZtZB6AkdsJnQDnMoHXHLGKOgDYuCWmYhEERCI5gaamW0bnHdA3k2ltlIN+2qKRyCND0bhqSYCyTB3CAOc4WusBEIpkeBuPgJMAAX8Hs1NfqHRgAAAABJRU5ErkJggg==") repeat-x scroll left top rgb(185, 107, 107);
            }
        ');

        $this->mixScript('
                $("#page-tabs a").click( function(e){
                    e.preventDefault();
                    var seleccionado = $(this);
                    $("#page-tabs li a").each(function (index) { $(this).removeClass("active"); });
                    seleccionado.closest("a").addClass("active");

                    comunica("'.$this->pantalla.'",{
                        metodo: "MuestraDatos",
                        mes: seleccionado.attr( "mes" ),
                        origen: "'.$this->origen.'",
                        gur: $("#inputGur").val(),
                        rct_id: $("#inputRct_id").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });

                    e.stopPropagation();
                    return false;
                });
            ');

        $this->mixScript('
                // Cargamos la información a mostrar
                function loadData( json ){
                    $("#tabla-fondos-rectificado tbody").html("");
                    const tbody = $("#tabla-fondos-rectificado tbody");

                    dataRectificado = [];

                    i = 0;
                    for( var key in json ){
                        //
                        const formatoMoneda = new Intl.NumberFormat("es-MX", {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });

                        var d = (dataRectificado[i] = {});
                        id = json[key].id;
                        gur = json[key].gpo_id + json[key].ur_id;
                        rct_id = json[key].rct_id;
                        afe_id = json[key].afe_id;
                        bdo_id = json[key].bdo_id;
                        clc_id = json[key].clc_id;
                        tdf_id = json[key].tdf_id;
                        rfc_id = json[key].rfc_id;
                        bdo_referencia = json[key].bdo_referencia;
                        concepto = json[key].rct_justificacion;
                        importe = (json[key].bdr_importe == 0) ? "" : formatoMoneda.format(json[key].bdr_importe);
                        estado = json[key].estado;
                        fechaAutorizacion = (json[key].fechaAutorizacion == null) ? "" : json[key].fechaAutorizacion;
                        polizaAutorizado =  (json[key].polizaAutorizado == null) ? "" : json[key].polizaAutorizado;
                        fechaAutorizado = (json[key].fechaAutorizado == null) ? "" : json[key].fechaAutorizado;
                        agrupador = json[key].agrupador;

                        /**********************************************/
                        tbody.append(
                            "<tr class=\'seleccionable\'><td style = \' text-align: left; white-space: nowrap\'>" 
                                + afe_id + "</td><td style = \' text-align: center; white-space: nowrap\'>" 
                                + bdo_id + "</td><td>" 
                                + tdf_id + "</td><td style = \' text-align: left; white-space: pre-wrap\'>"
                                + rfc_id + "</td><td style = \' text-align: left; white-space: pre-wrap\'>" 
                                + bdo_referencia + "</td><td style = \' text-align: center; white-space: pre-wrap\'>" 
                                + rct_id + "</td><td style = \' text-align: leftt; white-space: pre-wrap\'>" 
                                + concepto + "</td><td style = \' text-align: right; white-space: pre-wrap\'>" 
                                + importe + "</td><td style = \' text-align: center; white-space: pre-wrap\'>"
                                + estado + "</td><td style = \' text-align: center; white-space: nowrap\'>" 
                                + fechaAutorizacion + "</td><td style = \' text-align: center; white-space: nowrap\'>" 
                                + polizaAutorizado + "</td><td style = \' text-align: center; white-space: nowrap\'>" 
                                + fechaAutorizado + "</td><td style = \' text-align: left; display:none; white-space: nowrap\'>" 
                                + gur + "</td><td style = \' text-align: left; display:none; white-space: nowrap\'>" 
                                + agrupador + "</td></tr>");
                        i++;
                    }

                    /********************************/
                    $(document).ready(function (){
                        $(".seleccionable").click(function() {                                             
                            $(".seleccionable").css("font-weight","normal");
                            $(".seleccionable").removeClass("seleccionado");
                            $(this).addClass("seleccionado");
                            $(this).css("font-weight","bold");
                        });

                        $("#tabla-fondos-rectificado tbody tr").hover(
                            function() {
                                $(this).addClass("coloreaRectificado");
                            },
                            function() {                      
                                $(this).removeClass("coloreaRectificado"); 
                            }
                         ); 
                    });
                }
                
                $("#tabla-fondos-rectificado").on("dblclick", "tbody>tr", function() {
                    // Obtener el índice de la fila (comenzando desde 0)
                    var rowIndex = $(this).index();

                    // Obtener el contenido de la fila
                    var rowData = [];
                    $(this).find("td").each(function() {
                        rowData.push($(this).text());
                    });

                    
                    var afe_id = rowData[0];
                    var bdo_id = rowData[1];
                    var tdf_id = rowData[2];
                    var rfc_id = rowData[3];
                    var bdo_referencia = rowData[4];
                    var rct_id = rowData[5];
                    var gur = rowData[12];
                    var agrupador = rowData[13];

                    // Si no hay cuenta que seleccionar, no hacemos nada
                    if ( gur == "" ) {
                        e.stopPropagation();
                        return false;
                    }

                    var var_rctFiltro = 1;
                    var var_clcFiltro = 1;
                    var var_bdoFiltro = 1;

                    if( $("#inputRct_id").val() == "" ){
                        var_rctFiltro = 2;
                    }
                    if( $("#inputClc_id").val() == "" ){
                        var_clcFiltro = 2;
                    }
                    if( $("#inputBdo_id").val() == "" && $("#inputRfc_id").val() == "" && $("#inputBdo_referencia").val() == "" ){
                        var_bdoFiltro = 2;
                    }

                    comunica("'.$this->pantalla.'", {
                        metodo: "Documento",
                        origen: "'.$this->origen.'",
                        seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "" ).'",
                        mes: $("#page-tabs li").children(".active").attr("mes"),
                        rct_id: rct_id,
                        rctFiltro: var_rctFiltro,
                        clc_id: $("#inputClc_id").val(),
                        clcFiltro: var_clcFiltro,
                        gur: gur,
                        afe_id: afe_id,
                        bdo_id: bdo_id,
                        tdf_id: tdf_id,
                        rfc_id: rfc_id,
                        bdo_referencia: bdo_referencia,
                        bdoFiltro: var_bdoFiltro,
                        agrupador: agrupador
                    });
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

    private function MuestraDatos()
    {
        $data = $this->DameInformacion();

        $this->mixScript('
            loadData( '.$data.' );
        ');

        return false;
    }

    private function MuestraDatosPagina()
    {
        $data = $this->DameInformacionPagina();

        $this->mixScript('
            loadData( '.$data.' );
        ');

        return false;
    }

    private function DameInformacion()
    {

        $gur = ( isset($this->prm->gur) ? $this->prm->gur : '');
        $rct_id = ( isset($this->prm->rct_id) ? $this->prm->rct_id : '' );
        $clc_id = ( isset($this->prm->clc_id) ? $this->prm->clc_id : '' );
        $bdo_id = ( isset($this->prm->bdo_id) ? $this->prm->bdo_id : '' );
        $rfc_id = ( isset($this->prm->rfc_id) ? $this->prm->rfc_id : '' );
        $bdo_referencia = '';
        if (isset($this->prm->bdo_referencia) && !empty($this->prm->bdo_referencia)) {
            $referencias = explode(",",
                str_replace(' ', '', $this->prm->bdo_referencia));
            foreach ($referencias as $bdo) {
                $bdo_referencia .= '"'.$bdo.'",';
            }
            $bdo_referencia = substr($bdo_referencia, 0, -1);
        }
        $periodo['mesDesde'] = ( isset($this->prm->mes) ? $this->prm->mes : '' );
        $periodo['mesHasta'] = ( isset($this->prm->mes) ? $this->prm->mes : '' );

        $reporte['tipo'] = 1;
        $programatico['epg_id'] = '';
        $ClaveFinanciamiento['ecf_id'] = ''; // busqueda por clave de financiamiento
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

        $detalle = FALSE;

        $paginado = TRUE;
        $registroInicial = 0;
        $datos = vinculacionRectificado($this->gpo_id, $this->ur_id,
            $this->ue_id, '', $periodo, $programatico, $ClaveFinanciamiento,
            $reporte, $detalle, 'K', $niveles,
            array("gur" => $gur, "rct_id" => $rct_id, "clc_id" => $clc_id, "bdo_id" => $bdo_id,
            "rfc_id" => $rfc_id, "bdo_referencia" => $bdo_referencia),
            $paginado, $registroInicial, LINEAS_POR_PAGINA);
        $nTotalPages = 0;
        if ($paginado == true) {

            $nTotalPages = $datos[0]['gpo_id'];
            unset($datos[0]);
        }
        if ($nTotalPages > 1 && $paginado == true) {
            $this->mixScript('

                                        $("#RectificadoPaginador").attr("last", 1 );
                                        var options = {
                                                numberOfPages: 5,
                                                currentPage: 1,
                                                totalPages: '.$nTotalPages.',
                                                containerClass: "pull-right",
                                                listContainerClass: "nav nav-tabs",
                                                itemContainerClass: function ( type, page, current ) { return (page === current) ? "active" : "pointer-cursor"; },
                                                onPageClicked: function(e,originalEvent,type,page){
                                                        // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                                                        if ( $("#RectificadoPaginador").attr("last") != page ) {
                                                                 comunica("'.$this->pantalla.'",{
                                                                    metodo: "MuestraDatosPagina",
                                                                    mes:  $("#page-tabs li").children(".active").attr("mes"),
                                                                    origen: "'.$this->origen.'",
                                                                    rct_id: $("#inputRct_id").val(),
                                                                    gur: $("#inputGur").val(),
                                                                    clc_id: $("#inputClc_id").val(),
                                                                    bdo_id: $("#inputBdo_id").val(),
                                                                    rfc_id: $("#inputRfc_id").val(),
                                                                    bdo_referencia: $("#inputBdo_referencia").val(),
                                                                    paginado: true,
                                                                    numpagina: page ,
                                                                    numreg: '.LINEAS_POR_PAGINA.'
                                                                });
                                                                $("#RectificadoPaginador").attr("last", page );
                                                        }
                                                }
                                        }
                                        $("#RectificadoPaginador").bootstrapPaginator(options);
                                        $("#RectificadoPaginador").addClass("pagination");
                                        $("#RectificadoPaginador li").addClass("page-item")
                                        $("#RectificadoPaginador li a").addClass("page-link cursor-pointer").css("margin", "0px")

                                ');
        } else {
            $this->mixScript('$("#RectificadoPaginador").html("");');
        }
        return json_encode($datos);
    }

    private function DameInformacionPagina()
    {

        $gur = ( isset($this->prm->gur) ? $this->prm->gur : '');
        $rct_id = ( isset($this->prm->rct_id) ? $this->prm->rct_id : '' );
        $clc_id = ( isset($this->prm->clc_id) ? $this->prm->clc_id : '' );
        $bdo_id = ( isset($this->prm->bdo_id) ? $this->prm->bdo_id : '' );
        $rfc_id = ( isset($this->prm->rfc_id) ? $this->prm->rfc_id : '' );
        $bdo_referencia = '';
        if (isset($this->prm->bdo_referencia) && !empty($this->prm->bdo_referencia)) {
            $referencias = explode(",",
                str_replace(' ', '', $this->prm->bdo_referencia));
            foreach ($referencias as $bdo) {
                $bdo_referencia .= '"'.$bdo.'",';
            }
            $bdo_referencia = substr($bdo_referencia, 0, -1);
        }
        $periodo['mesDesde'] = ( isset($this->prm->mes) ? $this->prm->mes : '' );
        $periodo['mesHasta'] = ( isset($this->prm->mes) ? $this->prm->mes : '' );
        $paginado = ( isset($this->prm->paginado) ? $this->prm->paginado : false );
        $numpagina = ( isset($this->prm->numpagina) ? $this->prm->numpagina : 1 );
        $numreg = ( isset($this->prm->numreg) ? $this->prm->numreg : 0 );

        $reporte['tipo'] = 1;
        $programatico['epg_id'] = '';
        $ClaveFinanciamiento['ecf_id'] = ''; // busqueda por clave de financiamiento
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

        $detalle = FALSE;
        $nInicial = ( ( $numpagina - 1 ) * LINEAS_POR_PAGINA );
        $datos = vinculacionRectificado($this->gpo_id, $this->ur_id,
            $this->ue_id, '', $periodo, $programatico, $ClaveFinanciamiento,
            $reporte, $detalle, 'K', $niveles,
            array("gur" => $gur, "rct_id" => $rct_id, "clc_id" => $clc_id, "bdo_id" => $bdo_id,
            "rfc_id" => $rfc_id, "bdo_referencia" => $bdo_referencia),
            $paginado, $nInicial, LINEAS_POR_PAGINA);

        if ($paginado == true) {

            unset($datos[0]);
        }
        return json_encode($datos);
    }

    private function Documento()
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = ? AND ur_id = ? ");
        $p2 = [$this->gpo_id, $this->ur_id];
        $conexion->consultar($p2);

        while ($dataRow = $conexion->extraer()) {
            $mesContable[$dataRow['msc_id']] = $dataRow;
        }

        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
        $clc_id_documento = ( isset($this->prm->clc_id) ? $this->prm->clc_id : '' );
        $bdo_id_documento = ( isset($this->prm->bdo_id) ? $this->prm->bdo_id : '' );
        $rct_id = ( isset($this->prm->rct_id) ? $this->prm->rct_id : '' );

        $mensaje = '';
        $generaPolizaAutorizado = FALSE;

        $clc_array = array();

        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->class = 'btn-outline-seconday me-2';
        $btnRegresa->color = 'gris';
        $btnRegresa->icon = 'circle-left me-2';

        $botones = $this->ponBoton($btnRegresa); 
        $this->agregaTools($botones);

        $documento = getDocumento($gpo_id_documento, $ur_id_documento,
            $bdo_id_documento, $this->prm->rfc_id, $this->prm->bdo_referencia,
            array("ejercicio" => APP_EJERCICIO, "tdf_id" => $this->prm->tdf_id, "mmc_id" => "R"));
        $html = '<legend>Análisis del documento a generar la póliza del rectificado</legend>';
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
                                <td>'.$documento->rfc_id."-".$documento->nombreProveedor.'</td>
                                <td>'.$documento->tipoDocumentoFuente."-".$documento->referencia.'</td>
                                <td>'.$documento->concepto.'</td>
                                <td width="10%" align="right"><span style="float:left">$</span>'.number_format($documento->percepcionPresupuestal,
                2).'</td>
                                <td width="10%" align="right"><span style="float:left">$</span>'.number_format($documento->percepcionContable,
                2).'</td>
                                <td width="10%" align="right"><span style="float:left">$</span>'.number_format($documento->retencionContable,
                2).'</td>
                                <td>'.$documento->fechaDocumento.'</td>
                            </tr>
                        </table>
                    </fieldset><br/>
            ';

        $sql = "
				SELECT gpo_id, ur_id, clc_id
				FROM(
					SELECT gpo_id, ur_id, clc_id
                    FROM scp_Presupuestal
                        INNER JOIN scp_Clc USING( gpo_id, ur_id, clc_id )
					WHERE gpo_id = :gpo AND ur_id = :ur
                        AND bdo_id = :bdo
						AND ecl_id NOT IN ( 1, 2, 14, 15, 17 )
                UNION ALL
					SELECT gpo_id, ur_id, clc_id
                    FROM scp_Contable
						INNER JOIN scp_Clc USING( gpo_id, ur_id, clc_id )
					WHERE gpo_id = :gpo AND ur_id = :ur
                        AND bdo_id = :bdo
						AND ecl_id NOT IN ( 1, 2, 14, 15, 17 )
				)m
            ";
        if ($clc_id_documento != ''){
            $sql .= "WHERE m.clc_id = :clc ";
            $sql .= "GROUP BY gpo_id, ur_id, clc_id";
            $p3 = [':gpo'=> $gpo_id_documento,':ur'=> $ur_id_documento,':bdo'=> $bdo_id_documento,':clc'=>$clc_id_documento];
        }else{
            $p3 = [':gpo'=> $gpo_id_documento,':ur'=> $ur_id_documento,':bdo'=> $bdo_id_documento];
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($p3);

        if ($conexion->getNumeroRegistros() == 0){
            $html .= $mensaje;
        } else {
            while ($data = $conexion->extraer()) {
                array_push($clc_array, $data['clc_id']);
            }
        }
        $conexion->desconectar();

        /* INFORMACIÓN DE LA CLC EN LA QUE ESTA EL DOCUMENTO */
        $html .= '
                <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                    <legend>CLC en la que esta el documento
                        <span id="menclc" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                    </legend>
            ';
        foreach ($clc_array AS $clc_id) {
            $clcDocumento = pintaClc($gpo_id_documento, $ur_id_documento,
                $clc_id,
                array("ejercicio" => APP_EJERCICIO, "bdo_id" => $bdo_id_documento));
            $html .= $clcDocumento->html;
        }
        $html .= '
                </fieldset></br>
            ';

        /* INFORMACIÓN DE LA RECTIFICACIÓN EN LA QUE ESTA EL DOCUMENTO */
        $html .= '
                <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                    <legend>Rectificación
                        <span id="menclc" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                    </legend>
            ';
        $rectificacionDocumento = pintaRectificacion($gpo_id_documento,
            $ur_id_documento, $rct_id, array("ejercicio" => APP_EJERCICIO));
        $html .= $rectificacionDocumento->html;
        $html .= '
                </fieldset></br>
            ';

        /*         * *********************************** Información de la Póliza Contable ************************************************************** */
        $fechaAutorizado = '';
        //preguntamos el estado de los meses contables en funcion de las fechas de la cere
        foreach ($documento->detalleRectificado as $key => $value) {
            $generaPolizaAutorizado = TRUE;
            $fechaAutorizado = $value->fechaAutorizado;

            $estadoMesAutorizado = $mesContable[str_pad($value->mesAutorizado,
                    2, '0', STR_PAD_LEFT)]['ems_id'];
        }//*/
        //die();
        // Marcos: 15/jun/2018
        // Piden  el gpo + ur de la clasificación administrativa, pero éste será 116 por el usuario que puede
        // entrar a la opción... se cambió para que pida el gpo + ur del documento que están revisando

        $polizas = getPolizaAgrupador($this->gpo_id, $this->ur_id,
            $this->prm->agrupador, 'K');

        $polizaAut = new stdClass;
        $botonGeneraPa = '';

        //preguntamos los datos de la póliza contable de autorización
        if ($polizas->valido) {
            foreach ($polizas->polizas as $key => $value) {
                if ($value->tipoMomento == 'A') {
                    $polizaAutorizado[$key] = getPoliza($value->gpo_id,
                        $value->ur_id, $value->ue_id, $value->msc_id,
                        $value->pot_id, $value->prf_id, $value->pma_id);
                    $polizaAut->valido = $polizaAutorizado[$key]->valido;
                }
            }
        }
        if (!isset($polizaAut->valido)) {
            $polizaAut->valido = FALSE;
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
                                        alertify.confirm("RE HACER LA POLIZA '.( isset($value->poliza) ? $value->poliza : '' ).'","Esta acción re construira la póliza de autorización como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
                                            function(){
                                                comunica("'.$this->pantalla.'",{
                                                    metodo: "GeneraPoliza",
                                                    origen: "'.$this->origen.'",
                                                    seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion : "" ).'",
                                                    mes: '.$this->prm->mes.',
                                                    gur: '.$this->prm->gur.',
                                                    rct_id: '.$this->prm->rct_id.',
                                                    rctFiltro: '.$this->prm->rctFiltro.',
                                                    clc_id: "'.$this->prm->clc_id.'",
                                                    clcFiltro: '.$this->prm->clcFiltro.',
                                                    bdo_id: '.$this->prm->bdo_id.',
                                                    tdf_id: "'.$this->prm->tdf_id.'",
                                                    rfc_id: "'.$this->prm->rfc_id.'",
                                                    bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                                    bdoFiltro: '.$this->prm->bdoFiltro.',
                                                    gpo_id: "'.$value->gpo_id.'",
                                                    ur_id: "'.$value->ur_id.'",
                                                    ue_id: "'.$value->ue_id.'",
                                                    fecha: "'.$fechaAutorizado.'",
                                                    agrupador: "'.$this->prm->agrupador.'",
                                                    tmc_id: "A"
                                                });
                                            }, 
                                            function(){ }
                                        ).set("labels",{ok:"Proceder",cancel:"Cancelar"});
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
                                comunica("'.$this->pantalla.'",{
                                    metodo: "GeneraPoliza",
                                    origen: "'.$this->origen.'",
                                    seleccion: "'.( isset($this->prm->seleccion)
                                ? $this->prm->seleccion : "" ).'",
                                    mes: '.$this->prm->mes.',
                                    gur: '.$this->prm->gur.',
                                    rct_id: '.$this->prm->rct_id.',
                                    rctFiltro: '.$this->prm->rctFiltro.',
                                    clc_id: "'.$this->prm->clc_id.'",
                                    clcFiltro: '.$this->prm->clcFiltro.',
                                    bdo_id: '.$this->prm->bdo_id.',
                                    tdf_id: "'.$this->prm->tdf_id.'",
                                    rfc_id: "'.$this->prm->rfc_id.'",
                                    bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                    bdoFiltro: '.$this->prm->bdoFiltro.',
                                    fecha: "'.$fechaAutorizado.'",
                                    agrupador: "'.$this->prm->agrupador.'",
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
                    $inputFecha->enDiv = TRUE;
                    $inputFecha->formato = 'fecha';
                    $inputFecha->labelClass = '_32 txtL';
                    $inputFecha->required = TRUE;

                    $btnCancelar = new stdClass();
                    $btnCancelar->id = 'btnCancelar';
                    $btnCancelar->icon = 'block';
                    $btnCancelar->name = 'nuevo';
                    $btnCancelar->color = 'rojo';
                    $btnCancelar->text = ' Cancelar';
                    $btnCancelar->value = ' Cancelar';

                    $html .= '
                            <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                                <div id="capfecha">
                                    <legend>Mes del Rectificado esta cerrado, capture una nueva fecha</legend>
                                    <center>
                                    '.$this->ponInput($inputFecha).'
                                    </center>
                                    <hr>
                                    '.$this->ponBoton($btnGeneraPolizaCerrado).'
                                    '.$this->ponBoton($btnCancelar).'
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
                                comunica("'.$this->pantalla.'",{
                                    metodo: "GeneraPoliza",
                                    origen: "'.$this->origen.'",
                                    seleccion: "'.( isset($this->prm->seleccion)
                                ? $this->prm->seleccion : "" ).'",
                                    mes: '.$this->prm->mes.',
                                    gur: '.$this->prm->gur.',
                                    rct_id: '.$this->prm->rct_id.',
                                    rctFiltro: '.$this->prm->rctFiltro.',
                                    clc_id: "'.$this->prm->clc_id.'",
                                    clcFiltro: '.$this->prm->clcFiltro.',
                                    bdo_id: '.$this->prm->bdo_id.',
                                    tdf_id: "'.$this->prm->tdf_id.'",
                                    rfc_id: "'.$this->prm->rfc_id.'",
                                    bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                    bdoFiltro: '.$this->prm->bdoFiltro.',
                                    agrupador: "'.$this->prm->agrupador.'",
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
            }
        } elseif ($estadoMesAutorizado == 'A') {
            $botones = '<div style="display:inline-block; color:#900; padding:10px;">Generar Póliza</div>';
        } elseif ($estadoMesAutorizado == 'C') {
            $botones = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización esta cerrado</div>';
        }

        $this->agregaTools($botones);
        $this->mixHtml($html);

        if (!$polizas->valido) {
            $invalidoHtml = '<br /><br />
                    <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                        <legend>Póliza Contable
                            <span id="menclc" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                        </legend>
                '.$polizas->mensaje.'
                    </fieldset></br>
                ';
            $this->mixHtml($invalidoHtml);
        }


        $this->setContenedor('botonA');
        $this->mixHtml($botonGeneraPa);

        $this->mixScript('
                $("#btnRegresa").click(function(){
                    comunica("'.$this->pantalla.'", {
                        metodo: "PonBarraBotones",
                        origen: "'.$this->origen.'",
                        seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "" ).'",
                        mes: '.$this->prm->mes.',
                        gur: '.$this->prm->gur.',
                        rct_id: '.$this->prm->rct_id.',
                        rctFiltro: '.$this->prm->rctFiltro.',
                        clc_id: "'.$this->prm->clc_id.'",
                        clcFiltro: '.$this->prm->clcFiltro.',
                        bdo_id: '.$this->prm->bdo_id.',
                        tdf_id: "'.$this->prm->tdf_id.'",
                        rfc_id: "'.$this->prm->rfc_id.'",
                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                        bdoFiltro: '.$this->prm->bdoFiltro.'
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
            $fechaValidar->gpo_id = $this->gpo_id;
            $fechaValidar->ur_id = $this->ur_id;
            $fechaValidar->fecha = $fecha;
            $fechaValidar->anio = APP_EJERCICIO;
            $res = validaFecha($fechaValidar);
            if (!$res->valido) {
                $this->mixScript('notify("error:'.$res->mensaje.'","error");');
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

        $conexion = new accesoDatos();
        $conexion->conectar();
        $conexion->setSql('SET @usr_ip = INET_ATON( "'.$this->usr_ip.'" ), @usr_id = '.$this->usr_id.', @bit_observacion = "PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DEL DEVENGADO"');
        $conexion->consultar();

        $regen = "
                CALL sp_RectificadoFondos2025( ".$gpo_id_documento.", ".$ur_id_documento.", '".$this->prm->agrupador."', '$fecha', '".$this->prm->tmc_id."', INET_ATON('".$this->usr_ip."'), $this->usr_id );
            ";

        $conexion->setSql($regen);

        $conexion->consultar();
        $conexion->setSql(' SELECT @banderaContabilidad AS bandera, @mensajeContabilidad AS mensaje');
        $conexion->consultar();
        $d = $conexion->extraer();
        $mensaje = "";
        $tipo = "";

        if ($d["bandera"] != 1) {
            $mensaje = "ERROR:".$d["mensaje"];
            $tipo = "error";
        } else {
            $mensaje = $d["mensaje"];
            $tipo = "success";
        }

        if (isset($this->prm->tmc_id) && $this->prm->tmc_id == 'C') {
            $this->mixScript('
                    notify("'.$mensaje.'","'.$tipo.'");
                    comunica("'.$this->pantalla.'",{
                        metodo: "Documento",
                        origen: "'.$this->origen.'",
                        seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion
                        : "" ).'",
                        mes: '.$this->prm->mes.',
                        gur: '.$this->prm->gur.',
                        rct_id: '.$this->prm->rct_id.',
                        rctFiltro: '.$this->prm->rctFiltro.',
                        clc_id: "'.$this->prm->clc_id.'",
                        clcFiltro: '.$this->prm->clcFiltro.',
                        bdo_id: '.$this->prm->bdo_id.',
                        tdf_id: "'.$this->prm->tdf_id.'",
                        rfc_id: "'.$this->prm->rfc_id.'",
                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                        bdoFiltro: '.$this->prm->bdoFiltro.',
                        agrupador: "'.$this->prm->agrupador.'",
                        fecha: "'.$fecha.'",
                        tmc_id: "C"
                    });
                ');
        } else {
            $this->mixScript('
                    notify("'.$mensaje.'","'.$tipo.'");
                    comunica("'.$this->pantalla.'",{
                        metodo: "Documento",
                        origen: "'.$this->origen.'",
                        seleccion: "'.( isset($this->prm->seleccion) ? $this->prm->seleccion
                        : "" ).'",
                        mes: '.$this->prm->mes.',
                        gur: '.$this->prm->gur.',
                        rct_id: '.$this->prm->rct_id.',
                        rctFiltro: '.$this->prm->rctFiltro.',
                        clc_id: "'.$this->prm->clc_id.'",
                        clcFiltro: '.$this->prm->clcFiltro.',
                        bdo_id: '.$this->prm->bdo_id.',
                        tdf_id: "'.$this->prm->tdf_id.'",
                        rfc_id: "'.$this->prm->rfc_id.'",
                        bdo_referencia: "'.$this->prm->bdo_referencia.'",
                        bdoFiltro: '.$this->prm->bdoFiltro.',
                        agrupador: "'.$this->prm->agrupador.'",
                        fecha: "'.$fecha.'",
                        tmc_id: "A"
                    });
                ');
        }

        $conexion->desconectar();
    }
}
//Permisos de acceso al controlador
if (isset($_SESSION[_S]['prm']['C']['12']['4']['1'])) {
    new ControladorRectificado();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}