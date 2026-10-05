<?php
set_time_limit(0);

//librerias
session_start();
require_once dirname(__FILE__).'/../../lib/getDocumento.php';
require_once dirname(__FILE__).'/../../lib/opcionConteo.php';
require_once dirname(__FILE__).'/../../lib/pintaClc.php';
require_once dirname(__FILE__).'/../../lib/scg/pintaMinistrado.php';
require_once dirname(__FILE__).'/../../lib/scg/vinculacion/2021/vinculacionMinistrado2021.php';
require_once dirname(__FILE__).'/../../lib/scg/getPoliza.php';
require_once dirname(__FILE__).'/../../lib/scg/getPolizaAgrupador.php';
require_once dirname(__FILE__).'/../../lib/scg/pintaPoliza.php';
require_once dirname(__FILE__).'/../../lib/validaFecha.php';
require_once dirname(__FILE__).'/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__).'/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__).'/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__).'/../../ent/scg/fondos/sp_CereCoEgresoAutorizado2021.php';
require_once dirname(__FILE__).'/../../ent/scg/fondos/sp_CereCoEgresoCancelado2021.php';
require_once dirname(__FILE__).'/../../ent/scg/fondos/sp_CereEgresoAutorizado2021.php';
class ControladorMinistrado2021 extends WebResponse
{
    protected $escritura;
    protected $gpo_id;
    protected $ur_id;
    protected $ue_id;
    protected $gpo_id_2021;
    protected $ur_id_2021;
    protected $usr_ip;
    protected $usr_id;
    protected $origen;

    //Constructor
    public function __construct()
    {
        
        //mandar a llamar constructor base
        parent::__construct();
        
        //Permisos de acceso a las tareas del controlador
        $this->escritura = isset($_SESSION[_S]['prm']['C']['12']['19']['2']) ? TRUE
                : FALSE;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = FALSE;
        }
        
        $paraUnidad = [$this->gpo_id,$this->ur_id];

        $sql = "SELECT
                    gpo_id_2021 AS gpo_id, ur_id_2021 AS ur_id
                FROM ".BD_SINPRES.".vin_UnidadResponsable_2021_2022 vur2022 
                INNER JOIN ".BD_SINPRES.".vin_UnidadResponsable_2022_2023 vur2023
                    ON vur2022.gpo_id_2022 = vur2023.gpo_id_2022 AND vur2022.ur_id_2022 = vur2023.ur_id_2022 
                INNER JOIN ".BD_SINPRES.".vin_UnidadResponsable_2023_2024 vur2024                
    				ON vur2023.gpo_id_2023 = vur2024.gpo_id_2023 AND vur2023.ur_id_2023 = vur2024.ur_id_2023 
                WHERE vur2024.gpo_id_2024 = ? AND vur2024.ur_id_2024 = ? ;
            ";
        $conexion = new AccesoDatos();
        $conexion->setSql($sql);
        $conexion->conectar();
        $conexion->consultar($paraUnidad);
        $data = $conexion->extraer();
        $conexion->desconectar();
        $this->gpo_id_2021 = $data['gpo_id'];
        $this->ur_id_2021 = $data['ur_id'];
        unset($conexion);

        $this->usr_ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? 
            $_SESSION[_S]['ses_ip'] : 
            'ERROR';

        $this->usr_id = $_SESSION[_S]['usr_id'];

        $this->origen = isset($this->prm->origen) ? $this->prm->origen : 'opcion';
        if ($this->origen == 'opcion')
                $this->prm->mes = isset($this->prm->mes) ? $this->prm->mes : DATE("m");

        switch ($this->metodo) {
            case 'DibujaPantalla': $this->DibujaPantalla();
                break;
            case 'MuestraDatos': $this->MuestraDatos();
                break;
            case 'Documento': $this->Documento();
                break;
            case 'GeneraPoliza': $this->GeneraPoliza();
                break;
            case 'PonBarraBotones': $this->PonBarraBotones();
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
                    $this->PonBarraBotones();
                    $this->prm->seleccion = "clc";
                }
                break;
        }

        $this->returnJson();
    }

    private function PonBarraBotones()
    {
        if (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] < 3) {
            $this->mixScript('$("#toggleCA").hide();'); // ocultamos la unidad responsable si el nivel de usuario es menor a 3
        }
        // Botones y opciones que se dibujarán en la barra de botones
        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar';
        $btnLimpiar->value = 'Limpiar';
        $btnLimpiar->type = 'button';
        $btnLimpiar->class = " btn btn-outline-info";
        $btnLimpiar->icon = "fa fa-refresh";

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
        $inputGur->class = 'form-control';
        $inputGur->value = (isset($this->prm->gur)? $this->prm->gur : "" );
        $inputGur->maxlength = '3';

        $inputClc = new StdClass();
        $inputClc->id = 'inputClc_id';
        $inputClc->name = 'inputClc_id';
        $inputClc->placeholder = 'CLC';
        $inputClc->enDiv = true;
        $inputClc->class = 'form-control';
        $inputClc->value = ( $clcFiltro ? $this->prm->clc_id : "" );
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
        
         $botones = '
            <div class="row mb-4 class="col-md-12 col-xs-12">
                '.($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 116 ?
                '<fieldset class="col-md-3 col-xs-3 " id="">' .
                    $this->ponInput($inputGur) .
                '</fieldset>' : '') . '
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponInput($inputClc) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponInput($inputBdo) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id=""></fieldset>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponInput($inputRfc) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6" id="">
                    ' . $this->ponInput($inputReferencia) . '
                </fieldset>
            </div>';


        $this->agregaTools($botones);
        // dibujamos los Tabuladores
        $pestana = "";
        $contenedor = "";
        $inicio = "";
        $paraMes = [$this->gpo_id,$this->ur_id];
        $this->conectar();
        $this->setSql('
                SELECT msc_id, msc_nombre, msc_alias, ems_id AS estado
                FROM cat_MesContable
                    INNER JOIN cg_MesContable USING( msc_id )
                WHERE gpo_id = ? AND ur_id = ?
                    AND msc_id NOT IN ( 0, 13 )
            ');
        $this->consultar($paraMes);
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
                        $("#btnRegresa").click(function(e) {
                            $("nav").menusinpres("clickMenu");
                        });
                        '.( ( $_SESSION[_S]['nvj_id'] < 3 ) ? '$(".cabecera").hide(); $("#toggleCA").hide();'
                            : '').'
                    ');

                $this->mixScript('
                        comunica("'.$this->pantalla.'",{
                            metodo: "MuestraDatos",
                            origen: "'.$this->origen.'",
                            seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion
                            : "" ).'",
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
                        $("#btnRegresa").click(function(e) {
                            e.preventDefault();
                            $("#btnRegresa").attr("origen", "")
                            $(".navbar").hide();
                            $("#vclcPantallaCompleta").show();
                            e.stopPropagation();
                        });

                        comunica("'.$this->pantalla.'",{
                            metodo: "MuestraDatos",
                            origen: "'.$this->origen.'",
                            seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion
                            : "" ).'",
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
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : '.$_SESSION[_S]['clasAdm-gpo'].$_SESSION[_S]['clasAdm-ur'].'" );
                $("#opciones").click( function(e) {
                    comunica("'.$this->pantalla.'", {
                        metodo: "Filtro",
                        origen: "'.$this->origen.'",
                        mes: $("#page-tabs li").children(".active").attr("mes"),
                        seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "" ).'",
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });
                });

                $("#btnLimpiar").click( function(e) {
                    $("#inputGur").val("");
                    $("#inputClc_id").val("");
                    $("#inputBdo_id").val("");
                    $("#inputRfc_id").val("");
                    $("#inputBdo_referencia").val("");

                    e.preventDefault();
                    var seleccionado = $(".meses");
                    $("#page-tabs li").each(function (index) { $(this).children("a").removeClass("active"); });
                    seleccionado.closest("li").addClass("active");

                    comunica(
                        "' . $this->pantalla . '",
                        {
                            metodo: "MuestraDatos",
                            origen: "' . $this->origen . '",
                            seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                            mes: seleccionado.attr( "mes" ),
                            gur: $("#inputGur").val(),
                            clc_id: $("#inputClc_id").val(),
                            bdo_id: $("#inputBdo_id").val(),
                            rfc_id: $("#inputRfc_id").val(),
                            bdo_referencia: $("#inputBdo_referencia").val()
                        }
                    );

                    e.stopPropagation();
                    return false;
                });

                $("#excelMinistrado2021").click( function(e) {
                    var form = $("<form>")
                        .attr("action", "rep/excel/scg/fondos/exp_Ministrado2021.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "12" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "3" ))

                        .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "B" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "seleccion").attr("value", "'.(isset($this->prm->seleccion)
                    ? $this->prm->seleccion : "" ).'" ))

                        .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs li").children(".active").attr("mes") ))

                        .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_id").attr("value", $("#inputBdo_id").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputRfc_id").attr("value", $("#inputRfc_id").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_referencia").attr("value", $("#inputBdo_referencia").val() ))

                    $("body").append(form);
                    form.submit();
                });

                $("#imprimirMinistrado2021").click( function(e) {
                        var form = $("<form>")
                            .attr("action", "rep/doc/scg/reporte.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "12" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "3" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "B" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs li").children(".active").attr("mes") ))
                            .append($("<input>").attr("type", "hidden").attr("name", "seleccion").attr("value", "'.(isset($this->prm->seleccion)
                    ? $this->prm->seleccion : "" ).'" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value",  "inputGur").attr("value", '.$this->gpo_id_2021.$this->ur_id_2021.'))
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
        $html = '<div id="myGridMinistrado2021">
                <div class="tabla-contenedor col-md-12" style="overflow: scroll; max-height: 400px;">
                    <table id="tabla-fondos-ministrado2021" class="table table-striped table-bordered">
                        <div class="col-md-12 row" style="text-align:center">
                            <thead class="sticky-top" style="z-index:100;">
                                <tr class="warning" role="row">
                                    <th class="warning" role="columnheader" style= "text-align:center">UR</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">AFE</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">DOC</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">TDF</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">RFC</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">REFERENCIA</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">CONCEPTO</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">TDF MINISTRADO</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">REFERENCIA MINISTRADO</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">IMPORTE</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">ESTADO</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">AUTORIZACIÓN</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">CANCELACIÓN</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">POLIZA AUT.</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">FECHA AUT.</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">POLIZA CANC.</th>
                                    <th class="warning" role="columnheader" style= "text-align:center">FECHA CANC.</th>
                                </tr>
                            </thead>
                        </div>
                        <tbody></tbody>
                    </table> 
                </div>        
        </div>';
        $this->mixHtml($html);

        $this->mixStyle('
            .tabla-contenedor table > tbody > tr.coloreaMinistrado2021 > td {
                background-color: #000;
                background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABsAAAAoCAYAAAAPOoFWAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAPZJREFUeNq81tsOgjAMANB2ov7/7ypaN7IlIwi9rGuT8QSc9EIDAsAznxvY4pXPKr05RUE5MEVB+TyWfCEl9LZApYopCmo9C4FKSMtYoI8Bwv79aQJU4l6hXXCZrQbokJEksxHo9KMOgc6w1atHXM8K9DVC7FQnJ0i8iK3QooGgbnyKgMDygBWyYFZoqx4qS27KqLZJjA1D0jK6QJcYEQEiWv9PGkTsbqxQ8oT+ZtZB6AkdsJnQDnMoHXHLGKOgDYuCWmYhEERCI5gaamW0bnHdA3k2ltlIN+2qKRyCND0bhqSYCyTB3CAOc4WusBEIpkeBuPgJMAAX8Hs1NfqHRgAAAABJRU5ErkJggg==") repeat-x scroll left top rgb(185, 107, 107);
            }
        ');

        $this->mixScript('
                $("#page-tabs a").click( function(e) {
                    e.preventDefault();
                    var seleccionado = $(this);
                    $("#page-tabs li a").each(function (index) { $(this).removeClass("active"); });
                    seleccionado.closest("a").addClass("active");

                    comunica("'.$this->pantalla.'",{
                        metodo: "MuestraDatos",
                        origen: "'.$this->origen.'",
                        seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "" ).'",
                        mes:  seleccionado.attr( "mes" ),
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

        $this->mixScript('
                var sortcol = "CLAVE";
                var sortdir = 1;
                var searchString = "";

                $("#buscar").keyup(function (e) {
                    // ESC limpia el campo
                    if (e.which == 27) { this.value = ""; }
                    searchString = this.value.toLowerCase();
                    updateFilter();
                });

                function updateFilter() {
                    dataViewMinistrado2021.setFilterArgs({ searchString: searchString });
                    dataViewMinistrado2021.refresh ();
                }

                // Cargamos la información a mostrar
                function loadData( json ) {
                    $("#tabla-fondos-ministrado2021 tbody").html("");
                    const tbody = $("#tabla-fondos-ministrado2021 tbody");

                    dataMinistrado2021 = [];

                    i = 0;
                    for( var key in json ) {
                        //
                        const formatoMoneda = new Intl.NumberFormat("es-MX", {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });

                        var d = (dataMinistrado2021[i] = {});
                        id = json[key].id;
                        gur = json[key].gpo_id + json[key].ur_id;
                        afe_id = json[key].afe_id;
                        bdo_id = json[key].bdo_id;
                        tdf_id = json[key].tdf_id;
                        rfc_id = json[key].rfc_id;
                        bdo_referencia = json[key].bdo_referencia;
                        bdo_concepto = json[key].bdo_concepto;
                        tdf_id_ministrado = json[key].tdf_id_ministrado;
                        bdm_referencia = json[key].bdm_referencia;
                        importe = (json[key].bdo_importe == 0) ? "" : formatoMoneda.format(json[key].bdo_importe);
                        estado = json[key].estado;
                        fechaAutorizacion = (json[key].fechaAutorizacion == null) ? "" : json[key].fechaAutorizacion;
                        fechaCancelacion = (json[key].fechaCancelacion == null) ? "" : json[key].fechaCancelacion;
                        polizaAutorizado = (json[key].polizaAutorizado == null) ? "" : json[key].polizaAutorizado;
                        fechaAutorizado = (json[key].fechaAutorizado == null) ? "" : json[key].fechaAutorizado;
                        polizaCancelado = (json[key].polizaCancelado == null) ? "" : json[key].polizaCancelado;
                        fechaCancelado = (json[key].fechaCancelado == null) ? "" : json[key].fechaCancelado;
                        agrupador = json[key].agrupador;


                        /**********************************************/
                        tbody.append(
                            "<tr class=\'seleccionable\'><td style = \' text-align: center; white-space: nowrap\'>" 
                                + gur + "</td><td style = \' text-align: left; white-space: nowrap\'>" 
                                + afe_id + "</td><td style = \' text-align: center; white-space: nowrap\'>" 
                                + bdo_id + "</td><td>" 
                                + tdf_id + "</td><td style = \' text-align: left; white-space: pre-wrap\'>"
                                + rfc_id + "</td><td style = \' text-align: left; white-space: pre-wrap\'>" 
                                + bdo_referencia + "</td><td style = \' text-align: leftt; white-space: pre-wrap\'>" 
                                + bdo_concepto + "</td><td style = \' text-align: left; white-space: pre-wrap\'>" 
                                + tdf_id_ministrado + "</td><td style = \' text-align: left; white-space: pre-wrap\'>" 
                                + bdm_referencia + "</td><td style = \' text-align: right; white-space: pre-wrap\'>" 
                                + importe + "</td><td style = \' text-align: center; white-space: pre-wrap\'>"
                                + estado + "</td><td style = \' text-align: center; white-space: nowrap\'>" 
                                + fechaAutorizacion + "</td><td style = \' text-align: center; white-space: nowrap\'>" 
                                + fechaCancelacion + "</td><td style = \' text-align: left; white-space: nowrap\'>" 
                                + polizaAutorizado + "</td><td style = \' text-align: center; white-space: nowrap\'>" 
                                + fechaAutorizado + "</td><td style = \' text-align: left; white-space: nowrap\'>" 
                                + polizaCancelado + "</td><td style = \' text-align: center; white-space: nowrap\'>" 
                                + fechaCancelado + "</td><td style = \' text-align: left; display:none; white-space: nowrap\'>" 
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

                        $("#tabla-fondos-ministrado2021 tbody tr").hover(
                            function() {
                                $(this).addClass("coloreaMinistrado2021");
                            },
                            function() {                      
                                $(this).removeClass("coloreaMinistrado2021"); 
                            }
                         ); 
                    });


                }

                $("#tabla-fondos-ministrado2021").on("dblclick", "tbody>tr", function() {
                    // Obtener el índice de la fila (comenzando desde 0)
                    var rowIndex = $(this).index();

                    // Obtener el contenido de la fila
                    var rowData = [];
                    $(this).find("td").each(function() {
                        rowData.push($(this).text());
                    });

                    var gur = rowData[0];
                    var afe_id = rowData[1];
                    var bdo_id = rowData[2];
                    var tdf_id = rowData[3];
                    var rfc_id = rowData[4];
                    var bdo_referencia = rowData[5];
                    var agrupador = rowData[17];

                    // Si no hay cuenta que seleccionar, no hacemos nada
                    if ( gur == "" ) {
                        e.stopPropagation();
                        return false;
                    }

                    var var_clcFiltro = 1;
                    var var_bdoFiltro = 1;
                
                    if ($("#inputClc_id").val() == "") {
                        var_clcFiltro = 2;
                    }
                    if ($("#inputBdo_id").val() == "" && $("#inputRfc_id").val() == "" && $("#inputBdo_referencia").val() == "") {
                        var_bdoFiltro = 2;
                    }

                    comunica("'.$this->pantalla.'", {
                        metodo: "Documento",
                        origen: "'.$this->origen.'",
                        seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "" ).'",
                        mes: $("#page-tabs li").children(".active").attr("mes"),
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
                    
                    e.stopPropagation();
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
                loadData('.$data.');
            ');

        return false;
    }

    private function DameInformacion()
    {
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id_2021.$this->ur_id_2021);
        $clc_id = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');
        $rfc_id = (isset($this->prm->rfc_id) ? $this->prm->rfc_id : '');
        $bdo_referencia = '';
        if (isset($this->prm->bdo_referencia) && !empty($this->prm->bdo_referencia)) {
            $referencias = explode("," , str_replace(', ', ',', $this->prm->bdo_referencia));
            foreach ($referencias as $bdo) {
                $bdo_referencia .= '"' . $bdo . '",';
            }
            $bdo_referencia = substr($bdo_referencia, 0, -1);
        }
        $periodo['mesDesde'] = (isset($this->prm->mes) ? $this->prm->mes : '');
        $periodo['mesHasta'] = (isset($this->prm->mes) ? $this->prm->mes : '');

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

        $datos = vinculacionMinistrado2021($this->gpo_id, $this->ur_id,
            $this->ue_id, '', $periodo, $programatico, $ClaveFinanciamiento,
            $reporte, $detalle, 'B', $niveles,
            array("gur" => $gur, "clc_id" => $clc_id, "bdo_id" => $bdo_id, "rfc_id" => $rfc_id,
            "bdo_referencia" => $bdo_referencia));

        return json_encode($datos);
    }

    private function Documento()
    {
        $paraMesCont = [$this->gpo_id,$this->ur_id];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = ? AND ur_id = ? ");
        $conexion->consultar($paraMesCont);

        while ($dataRow = $conexion->extraer()) {
            $mesContable[$dataRow['msc_id']] = $dataRow;
        }

        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
        $clc_id_documento = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id_documento = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');

        $clc_array = array();
        $generaPolizaAutorizado = FALSE;
        $generaPolizaCancelado = FALSE;

        $fechaAutorizado = '';
        $fechaCancelado = '';

        $estadoMesCancelado = FALSE;

        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->color = 'gris';
        $btnRegresa->class = "me-2";
        $btnRegresa->icon = "circle-left me-2";

        $botones = $this->ponBoton($btnRegresa); 
        $this->agregaTools($botones);

        $documento = getDocumento($gpo_id_documento, $ur_id_documento,
            $bdo_id_documento, $this->prm->rfc_id, $this->prm->bdo_referencia,
            array("ejercicio" => 2021, "tdf_id" => $this->prm->tdf_id, "mmc_id" => "B"));
        $html = '<legend>Análisis del documento 2021 a generar la póliza del ministrado</legend>';
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

        //preguntamos el estado de los meses contables en funcion de las fechas de la ministracion

        foreach ($documento->detalleMinistrado2021 as $key => $value) {
            if ($clc_id_documento != '') {
                if ($clc_id_documento == $value->clc_id) {
                    $clc_array[$value->clc_id] = $value->clc_id;
                }
            } else {
                $clc_array[$value->clc_id] = $value->clc_id;
            }

            if ($this->prm->agrupador == $value->agrupador) {
                $tipoministrado = $value->tipoMinistracion;
                if ($value->tipoMinistracion != 2) {
                    $generaPolizaAutorizado = TRUE;
                    $fechaAutorizado = $value->fechaAutorizado;
                }
                
                if(isset($mesContable[str_pad($value->mesAutorizado,2, '0', STR_PAD_LEFT)]['ems_id'])){
                    $estadoMesAutorizado = $mesContable[str_pad($value->mesAutorizado,2, '0', STR_PAD_LEFT)]['ems_id'];
                }
                
                if ($value->estado == 'C') {
                    if ($generaPolizaAutorizado) {
                        $generaPolizaCancelado = TRUE;
                        $fechaCancelado = $value->fechaCancelado;
                    }
                    $estadoMesCancelado = $mesContable[str_pad($value->mesCancelado,
                            2, '0', STR_PAD_LEFT)]['ems_id'];
                }
            }
        }

        /* INFORMACIÓN DE LA CLC EN LA QUE ESTA EL DOCUMENTO */
        if (count($clc_array) != 0) {
            $html .= '
                    <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                        <legend>CLC en la que esta el documento
                            <span id="menclc" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                        </legend>
                ';
            foreach ($clc_array AS $clc_id) {
                $clcDocumento = pintaClc($gpo_id_documento, $ur_id_documento,
                    $clc_id,
                    array("ejercicio" => 2021, "bdo_id" => $bdo_id_documento, "todo" => TRUE));
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
        $ministrado = pintaMinistrado($gpo_id_documento, $ur_id_documento,
            $bdo_id_documento,
            array("ejercicio" => 2021, "clc_id" => $clc_id_documento,));
        $html .= $ministrado->html;
        $html .= '
                </fieldset></br>
            ';
        if ($tipoministrado == 1 || $tipoministrado == 4 || $tipoministrado == 5
            || $tipoministrado == 6 || $tipoministrado == 7 || $tipoministrado == 8
            || $tipoministrado == 9) {
            /*             * *********************************** Información de la Póliza Contable ************************************************************** */
            $polizas = getPolizaAgrupador(
                $this->gpo_id, 
                $this->ur_id,
                $this->prm->agrupador,
                '',
                2021
            );

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

                                $this->mixScript('
                                    $("#btnReGeneraPolizaA").click(function() {
                                        alertify.confirm("RE HACER LA POLIZA '.(isset($value->poliza) ? $value->poliza : '').'","Esta acción re construira la póliza de autorización como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
                                            function(){
                                                comunica("'.$this->pantalla.'",{
                                                    metodo: "GeneraPoliza",
                                                    origen: "'.$this->origen.'",
                                                    seleccion: "'.(isset($this->prm->seleccion)? $this->prm->seleccion : "" ).'",
                                                    mes: '.$this->prm->mes.',
                                                    gur: '.$this->prm->gur.',
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
                                $botonGeneraPa = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización esta cerrado </div>';
                            }
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
                        $polizaCancelado[$key] = getPoliza($value->gpo_id,
                            $value->ur_id, $value->ue_id, $value->msc_id,
                            $value->pot_id, $value->prf_id, $value->pma_id);
                        $polizaCan->valido = $polizaCancelado[$key]->valido;
                    }
                }
            }
            if (!isset($polizaCan->valido)) {
                $polizaCan->valido = FALSE;
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
                                    $("#btnReGeneraPolizaC").click(function() {
                                        alertify.confirm("RE HACER LA POLIZA '.(isset($value->poliza) ? $value->poliza : '').'","Esta acción re construira la póliza de cancelación como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
                                            function(){
                                                comunica("'.$this->pantalla.'",{
                                                    metodo: "GeneraPoliza",
                                                    origen: "'.$this->origen.'",
                                                    seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion : "" ).'",
                                                    mes: '.$this->prm->mes.',
                                                    gur: '.$this->prm->gur.',
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
                                                    fecha: "'.$fechaCancelado.'",
                                                    agrupador: "'.$this->prm->agrupador.'",
                                                    tmc_id: "C"
                                                });
                                            }, 
                                            function(){ }
                                        ).set("labels",{ok:"Proceder",cancel:"Cancelar"});
                                    });
                                ');
                            } else {
                                $botonGeneraPc = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado </div>';
                            }
                        }
                    }
                }
            }

            $botones = '';
            if ($this->escritura) {
                //si deberia generar la póliza y NO existe
                if ($generaPolizaAutorizado && !$polizaAut->valido) {
                     $tipoministrado = 0;
                    $btnGeneraPoliza = new stdClass();
                    $btnGeneraPoliza->id = 'btnGeneraPolizaA';
                    $btnGeneraPoliza->name = 'btnGeneraPolizaA';
                    $btnGeneraPoliza->text = 'Genera Póliza de Autorización';
                    $btnGeneraPoliza->value = 'Genera Póliza de Autorización';
                    $btnGeneraPoliza->color = 'verde';
                    $btnGeneraPoliza->icon = 'ok';

                    //Si existe variable
                    if (isset($estadoMesAutorizado )){
                    //si el mes contable se encuentra abierto
                        if ($estadoMesAutorizado == 'A') {
                            $this->mixScript('
                                $("#btnGeneraPolizaA").click(function() {
                                    comunica("'.$this->pantalla.'",{
                                        metodo: "GeneraPoliza",
                                        origen: "'.$this->origen.'",
                                        seleccion: "'.(isset($this->prm->seleccion)
                                        ? $this->prm->seleccion : "" ).'",
                                        mes: '.$this->prm->mes.',
                                        gur: '.$this->prm->gur.',
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
                            $btnCancelar->text = 'Cancelar';
                            $btnCancelar->value = 'Cancelar';

                            $this->mixScript('
                                $("#btnGeneraPolizaA").click(function() {
                                    if ($("#bg_capfecha").length != 0 ) {
                                        $(this).hide();
                                        $("#bg_capfecha").show();
                                    }
                                });

                                $("#btnGeneraPolizaCerrado").click(function() {
                                    comunica("'.$this->pantalla.'",{
                                        metodo: "GeneraPoliza",
                                        origen: "'.$this->origen.'",
                                        seleccion: "'.(isset($this->prm->seleccion)
                                        ? $this->prm->seleccion : "" ).'",
                                        mes: '.$this->prm->mes.',
                                        gur: '.$this->prm->gur.',
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

                                $("#btnCancelar").click(function() {
                                    $("#bg_capfecha").hide();
                                    $("#btnGeneraPoliza").show();
                                });
                            ');
                        }
                    }
                }
            }

            if ($this->escritura) {
                if ($generaPolizaAutorizado && $polizaAut->valido && $generaPolizaCancelado
                    && !$polizaCan->valido) {

                    $btnGeneraPoliza = new stdClass();
                    $btnGeneraPoliza->id = 'btnGeneraPolizaC';
                    $btnGeneraPoliza->name = 'btnGeneraPolizaC';
                    $btnGeneraPoliza->text = 'Genera Póliza de Cancelación';
                    $btnGeneraPoliza->value = 'Genera Póliza de Cancelación';
                    $btnGeneraPoliza->color = 'verde';
                    $btnGeneraPoliza->icon = 'ok';

                    if ($estadoMesCancelado == 'A') {
                        $this->mixScript('
                            $("#btnGeneraPolizaC").click(function() {
                                comunica("'.$this->pantalla.'",{
                                    metodo: "GeneraPoliza",
                                    origen: "'.$this->origen.'",
                                    seleccion: "'.(isset($this->prm->seleccion)
                                    ? $this->prm->seleccion : "" ).'",
                                    mes: '.$this->prm->mes.',
                                    gur: '.$this->prm->gur.',
                                    clc_id: "'.$this->prm->clc_id.'",
                                    clcFiltro: '.$this->prm->clcFiltro.',
                                    bdo_id: '.$this->prm->bdo_id.',
                                    tdf_id: "'.$this->prm->tdf_id.'",
                                    rfc_id: "'.$this->prm->rfc_id.'",
                                    bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                    bdoFiltro: '.$this->prm->bdoFiltro.',
                                    agrupador: "'.$this->prm->agrupador.'",
                                    fecha: "'.$fechaCancelado.'",
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
                        $inputFecha->enDiv = TRUE;
                        $inputFecha->formato = 'fecha';
                        $inputFecha->labelClass = '_32 txtL';
                        $inputFecha->required = TRUE;

                        $btnCancelar = new stdClass();
                        $btnCancelar->id = 'btnCancelar';
                        $btnCancelar->icon = 'block';
                        $btnCancelar->name = 'nuevo';
                        $btnCancelar->color = 'rojo';
                        $btnCancelar->text = 'Cancelar';
                        $btnCancelar->value = 'Cancelar';

                        $this->mixScript('
                            $("#btnGeneraPolizaC").click(function() {
                                if ($("#bg_capfecha").length != 0 ) {
                                    $(this).hide();
                                    $("#bg_capfecha").show();
                                }
                            });

                            $("#btnGeneraPolizaCerrado").click(function() {
                                comunica("'.$this->pantalla.'",{
                                    metodo: "GeneraPoliza",
                                    origen: "'.$this->origen.'",
                                    seleccion: "'.(isset($this->prm->seleccion)
                                    ? $this->prm->seleccion : "" ).'",
                                    mes: '.$this->prm->mes.',
                                    gur: '.$this->prm->gur.',
                                    clc_id: "'.$this->prm->clc_id.'",
                                    clcFiltro: '.$this->prm->clcFiltro.',
                                    bdo_id: '.$this->prm->bdo_id.',
                                    tdf_id: "'.$this->prm->tdf_id.'",
                                    rfc_id: "'.$this->prm->rfc_id.'",
                                    bdo_referencia: "'.$this->prm->bdo_referencia.'",
                                    bdoFiltro: '.$this->prm->bdoFiltro.',
                                    agrupador: "'.$this->prm->agrupador.'",
                                    fecha: $("#cap_fecha").val(),
                                    tmc_id: "C"
                                });
                            });

                            $("#btnCancelar").click(function() {
                                $("#bg_capfecha").hide();
                                $("#btnGeneraPoliza").show();
                            });
                        ');
                    }
                }
            }

            $this->agregaTools($botones);
        } //fin ministrado
        $this->mixHtml($html);

        $this->setContenedor('botonA');
        $this->mixHtml($botonGeneraPa);
        $this->setContenedor('botonC');
        $this->mixHtml($botonGeneraPc);

        $this->mixScript('
            $("#btnRegresa").click(function() {                                  
                comunica("'.$this->pantalla.'", {
                    metodo: "PonBarraBotones",
                    origen: "'.$this->origen.'",
                    seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion
                : "" ).'",
                    mes: "'.(isset($this->prm->mes)? $this->prm->mes : ' $("#page-tabs li").parent(".active").attr("mes")').'",
                    gur: "'.$this->prm->gur.'",
                    clc_id: "'.$this->prm->clc_id.'",
                    clcFiltro: '.$this->prm->clcFiltro.',
                    bdo_id: "'.$this->prm->bdo_id.'",
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

        $tipoP = (isset($this->prm->tipoP) ? $this->prm->tipoP : 'A');
        if (isset($this->prm->fecha) && $this->prm->fecha != '') {
            $fecha = $this->prm->fecha;
            $fechaValidar = new stdClass();
            $fechaValidar->gpo_id = $this->gpo_id;
            $fechaValidar->ur_id = $this->ur_id;
            $fechaValidar->fecha = $fecha;
            $fechaValidar->anio = 2021;
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


        $bandera = 0;
        $mensajesp = "ERROR";

        if (isset($this->prm->tmc_id) && $this->prm->tmc_id == 'C') {
            $generaPolizaMinistrado = new sp_CereCoEgresoCancelado2021();
            $generaPolizaMinistrado->setGpo_id($gpo_id_documento);
            $generaPolizaMinistrado->setUr_id($ur_id_documento);
            $generaPolizaMinistrado->setBdm_agrupador($this->prm->agrupador);
            $generaPolizaMinistrado->setPma_fecha($fecha);
            $generaPolizaMinistrado->setTmc_id($this->prm->tmc_id);
            $generaPolizaMinistrado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS MINISTRACIÓN 2021");
            $generaPolizaMinistrado->setEjercicio(2021);
            $generaPolizaMinistrado->setSis_id("SCG");
            $generaPolizaMinistrado->setSql('SET @usr_ip = INET_ATON( "'.$this->usr_ip.'" ), @usr_id = '.$this->usr_id.', @bit_observacion = "PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS DE LA MINISTRACIÓN 2021"');
            $generaPolizaMinistrado->consultar();
            $generaPolizaMinistrado->execute(false, false);
            $bandera = $generaPolizaMinistrado->getBandera();
            $mensajesp = $generaPolizaMinistrado->getMensaje();
        } else {

            $generaPolizaMinistrado = new sp_CereCoEgresoAutorizado2021();
            $generaPolizaMinistrado->setGpo_id($gpo_id_documento);
            $generaPolizaMinistrado->setUr_id($ur_id_documento);
            $generaPolizaMinistrado->setBdm_agrupador($this->prm->agrupador);
            $generaPolizaMinistrado->setPma_fecha($fecha);
            $generaPolizaMinistrado->setTmc_id($this->prm->tmc_id);
            $generaPolizaMinistrado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS MINISTRACIÓN 2021");
            $generaPolizaMinistrado->setEjercicio(2021);
            $generaPolizaMinistrado->setSis_id("SCG");
            $generaPolizaMinistrado->setSql('SET @usr_ip = INET_ATON( "'.$this->usr_ip.'" ), @usr_id = '.$this->usr_id.', @bit_observacion = "PÓLIZA GENERADA DESDE LA PANTALLA DE FONDOS DE LA MINISTRACIÓN 2018"');
            $generaPolizaMinistrado->consultar();
            $generaPolizaMinistrado->execute(false, false);
            $bandera = $generaPolizaMinistrado->getBandera();
            $mensajesp = $generaPolizaMinistrado->getMensaje();
        }

        $mensaje = "";
        $tipo = "";
        if ($bandera != 1) {
            $mensaje = "ERROR:".$mensajesp;
            $tipo = "error";
        } else {
            $mensaje = $mensajesp;
            $tipo = "success";
        }

        if (isset($this->prm->tmc_id) && $this->prm->tmc_id == 'C') {
            $this->mixScript('
                    notify("'.$mensaje.'","'.$tipo.'");
                    comunica("'.$this->pantalla.'",{
                        metodo: "Documento",
                        origen: "'.$this->origen.'",
                        seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion
                        : "" ).'",
                        mes: '.$this->prm->mes.',
                        gur: '.$this->prm->gur.',
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
                        seleccion: "'.(isset($this->prm->seleccion) ? $this->prm->seleccion
                        : "" ).'",
                        mes: '.$this->prm->mes.',
                        gur: '.$this->prm->gur.',
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
    }
}
//Permisos de acceso al controlador
if (isset($_SESSION[_S]['prm']['C']['12']['19']['1'])) {
    new ControladorMinistrado2021();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}