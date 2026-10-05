<?php
//librerias
DEFINE("LINEAS_POR_PAGINA", 1000);
session_start();
require_once dirname(__FILE__) . '/../../lib/getDocumento.php';
require_once dirname(__FILE__) . '/../../lib/opcionConteo.php';
require_once dirname(__FILE__) . '/../../lib/pintaClc.php';
require_once dirname(__FILE__) . '/../../lib/scg/pintaPagado.php';
require_once dirname(__FILE__) . '/../../lib/scg/vinculacion/vinculacionPagado.php';
require_once dirname(__FILE__) . '/../../lib/scg/vinculacion/validaTipoPago.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPoliza.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPolizaAgrupador.php';
require_once dirname(__FILE__) . '/../../lib/scg/pintaPoliza.php';
require_once dirname(__FILE__) . '/../../lib/validaFecha.php';
require_once dirname(__FILE__) . '/../../lib/estadoMesContable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/vinculacion/sp_AutorizarPagoDocumento.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/vinculacion/sp_CancelarPagoDocumento.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/vinculacion/sp_PagadoCancelado.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/vinculacion/sp_PagadoAutorizado.php';

class ControladorRegistroPagado extends WebResponse
{
    private $ejercicioActual;
    private $escritura;
    private $gpo_id;
    private $ur_id;
    private $ue_id;
    private $origen;
    private $campoHabilitado;
    private $usr_ip;
    private $usr_id;
    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        //Permisos de acceso a las tareas del controlador
        $this->escritura = isset($_SESSION[_S]['prm']['C']['5']['6']['2']) ? TRUE : FALSE;
        $this->gpo_id = getSession('clasAdm-gpo', true);
        $this->ur_id = getSession('clasAdm-ur', true);
        $this->ue_id = getSession('clasAdm-ue', true);
        
        $this->ejercicioActual = getSession('ejercicioActual');

        // Marcos: 16/feb/2016
        // Determinaremos si viene de la vinculación por CLC, para que en éste caso, inhabilite los cuadros de texto
        $this->campoHabilitado = "";
        if (isset($this->prm->origen) && $this->prm->origen == "clc") {
            $this->campoHabilitado = " disabled ";
        }

        $this->usr_ip = $this->getIp();
        $this->usr_id = getSession('usr_id');

        $this->origen = isset($this->prm->origen) ? $this->prm->origen : 'opcion';
        if ($this->origen == 'opcion')
            $this->prm->mes = isset($this->prm->mes) ? $this->prm->mes : DATE("m");

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
            case 'PagarDetalle':
                $this->PagarDetalle();
                break;
            case 'Pagar':
                $this->Pagar();
                break;
            case 'DocumentoDetalle':
                $this->DocumentoDetalle();
                break;
            case 'CallProcedure':
                $this->CallProcedure();
                break;
            default:
                opcionConteo(
                    $_POST['opcion']['sis_id'],
                    $_POST['opcion']['men_id'],
                    $_POST['opcion']['opc_id']
                );
                $this->main();
                break;
        }

        $this->returnJson();
    }

    public function main()
    {
        $html = $this->getTablaRegistroPagado();
        $this->ponBarraBotones();
        $this->setContenedor('contenedor');
        $es116 = $this->gpo_id.$this->ur_id == 116;
        $clc = ((isset($this->prm->clc) && $this->prm->clc != "") ? $this->prm->clc : "");
        $this->mixHtml($html);
    }

    private function PonBarraBotones()
    {
        //Proviene de la pantalla de Vinculacion/Clc
        $tipo = ((isset($this->prm->tipo) && $this->prm->tipo != "") ? $this->prm->tipo : "");
        $edo = ((isset($this->prm->edo) && $this->prm->edo != "") ? $this->prm->edo : "");
        $buscarVinculacion = ((isset($this->prm->p) && $this->prm->p != "") ? $this->prm->p : "");

        // Botones y opciones que se dibujarán en la barra de botones
        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->type = 'button';
        $btnRegresa->icon = 'solid fa-circle-left me-2';
        $btnRegresa->color = 'gris';
        $btnRegresa->class = 'me-1';

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar';
        $btnLimpiar->value = 'Limpiar';
        $btnLimpiar->type = 'button';
        $btnLimpiar->icon = "refresh me-2";
        $btnLimpiar->color = "cielo";
        $btnLimpiar->class = "me-1";

        $btnPagar = new stdClass();
        $btnPagar->id = 'btnPagar';
        $btnPagar->name = 'btnPagar';
        $btnPagar->text = 'Pagar';
        $btnPagar->value = 'Pagar';
        $btnPagar->type = 'button';
        $btnPagar->class = "btn btn-outline-primary me-2";
        $btnPagar->icon = "credit-card me-2";

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
        $botones = '
            <div class="d-flex flex-row">
                ' . ((isset($this->prm->origen) && $this->prm->origen == "clc")
                ? '
                <fieldset class="col-xs-2" style="padding-right: 10px !important;">
                ' . $this->ponBoton($btnRegresa) . '
                </fieldset>' : '') . '
                <fieldset class="col-xs-1" style="padding-right: 10px !important;">
                ' . $this->ponBoton($btnPagar) . '
                </fieldset>
                <fieldset class="col-xs-1" style="padding-right: 10px !important;">
                    <input id="inputClc_id" placeHolder="CLC" type="text" ' . $this->campoHabilitado . ' maxlength=6 class="form-control" value="' . ($clcFiltro
                    ? $this->prm->clc_id : "") . '"/>
                </fieldset>
                <fieldset class="col-xs-2" style="padding-right: 10px !important;">
                    <input id="inputBdo_id" placeHolder="# Documento" ' . $this->campoHabilitado . ' maxlength=7 class="form-control" value="' . ($bdoFiltro
                    ? $this->prm->bdo_id : "") . '"/>
                </fieldset>
                <fieldset class="col-xs-2" style="padding-right: 10px !important;">
                    <input id="inputRfc_id" placeHolder="RFC" ' . $this->campoHabilitado . ' maxlength=20 class="form-control" value="' . ($bdoFiltro
                    ? $this->prm->rfc_id : "") . '"/>
                </fieldset>
                <fieldset class="col-xs-2" style="padding-right: 10px !important;">
                    <input id="inputBdo_referencia" placeHolder="Referencia" ' . $this->campoHabilitado . ' class="form-control" value="' . ($bdoFiltro
                    ? $this->prm->bdo_referencia : "") . '"/>
                </fieldset>
                <fieldset class="col-xs-1" style="padding-right: 10px !important;">
                    <button id="excelPagado" class="btn btn-outline-success ladda-button" data-style="slide-left"><i class="fa-solid fa-file-excel me-2"></i>Excel</button>
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
            WHERE gpo_id = ' . $this->gpo_id . ' AND ur_id = ' . $this->ur_id . '
                AND msc_id NOT IN ( 0, 13 )
        ');
        $this->consultar();
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

        $this->mixHtml('
            <div id="page-tabs"><ul class="nav nav-tabs">
            ' . $pestana . '
            </ul></div>
        ');

        $this->mixScript('
            $.getScript( "js/scg/fields-formater.js", function(data, textStatus, jqxhr){ });
        ');

        // Dibujamos el contenido
        switch ($this->origen) {
            case 'opcion':
                $this->mixScript('
                    $("#btnRegresa").click(function(e){
                        $("nav").menusinpres("clickMenu");
                    });

                    comunica("' . $this->pantalla . '",{
                        metodo: "MuestraDatos",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: $("#page-tabs li").children(".active").attr("mes"),
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
                    $("#btnRegresa").click(function(e){
                        e.preventDefault();
                        $("#btnRegresa").attr("origen", "");
                        comunica("scg/vinculacion/ControladorVinculacionClc.php",{ 
                            mes:  ' . $this->prm->mes . ', 
                            tipo: "'.$tipo.'",  
                            edo: "'.$edo.'", 
                            buscar: "'.$buscarVinculacion.'"
                        });
                        e.stopPropagation();
                    });

                    comunica("' . $this->pantalla . '",{
                        metodo: "MuestraDatos",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion
                        : "") . '",
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
                    mes: $("#page-tabs li").children(".active").attr("mes"),
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                    gur: $("#inputGur").val(),
                    clc_id: $("#inputClc_id").val(),
                    bdo_id: $("#inputBdo_id").val(),
                    rfc_id: $("#inputRfc_id").val(),
                    bdo_referencia: $("#inputBdo_referencia").val()
                });
            });
            ' . (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();' : '') . '
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );

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
                    mes: $("#page-tabs li").children(".active").attr("mes"),
                    gur: $("#inputGur").val(),
                    clc_id: $("#inputClc_id").val(),
                    bdo_id: $("#inputBdo_id").val(),
                    rfc_id: $("#inputRfc_id").val(),
                    bdo_referencia: $("#inputBdo_referencia").val()
                });

                e.stopPropagation();
                return false;
            });

            $("#excelPagado").click( function(e) {
                var form = $("<form>")
                    .attr("action", "rep/excel/scg/vinculacion/exp_RegistroPagado.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "5" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "6" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "P" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "seleccion").attr("value", "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs li").children(".active").attr("mes") ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_id").attr("value", $("#inputBdo_id").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputRfc_id").attr("value", $("#inputRfc_id").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_referencia").attr("value", $("#inputBdo_referencia").val() ))

                $("body").append(form);
                form.submit();
            });

            $("#imprimirPagado").click( function(e) {
                var form = $("<form>")
                    .attr("action", "rep/doc/scg/reporte.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "5" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "6" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "P" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "seleccion").attr("value", "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs li").children(".active").attr("mes") ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))
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

        $html = $this->getTablaRegistroPagado();
        $this->mixHtml($html);
        $this->DibujaPantalla();
    }

    public function MuestraDatosPagina($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array(), $paginado = false, $nInicial = 0, $numreg = 0, $ejecsql2 = false)
    {
        $sqlFiltro = '';
        $sqlFiltroP = '';
        $sqlParams = [
            ':gpo' => $gpo_id,
            ':ur' => $ur_id,
        ];

        if ($parametroExtra['gur'] != '') {
            $sqlFiltro = " AND t.gpo_id = :tgpo AND t.ur_id = :tur ";
            $sqlParams[':tgpo'] = substr($parametroExtra['gur'], 0, 1);
            $sqlParams[':tur'] = substr($parametroExtra['gur'], 1, 2);
        }
        if ($parametroExtra['clc_id'] != '') {
            $sqlFiltro .= " AND t.clc_id =  :tclc ";
            $sqlParams[':tclc'] = $parametroExtra['clc_id'];
        }
        if ($parametroExtra['bdo_id'] != '') {
            $sqlFiltro .= " AND t.bdo_id = :tbdo ";
            $sqlParams[':tbdo'] = $parametroExtra['bdo_id'];
        }
        if ($parametroExtra['rfc_id'] != '') {
            $sqlFiltro .= " AND b.rfc_id = :brfc ";
            $sqlParams[':brfc'] = $parametroExtra['rfc_id'];
        }
        if ($parametroExtra['bdo_referencia'] != '') {
            $sqlFiltro .= " AND b.bdo_referencia IN (" . $parametroExtra['bdo_referencia'] . ") ";
        }
        $var_MAX = " MAX";
        if ($periodo['mesDesde'] != '') {
            $sqlFiltro .= " AND t.msc_id = :mesdesde ";
            $sqlParams[":mesdesde"] = $periodo['mesDesde'];
            $var_MAX = "";
        }

        $sqlFiltroP = "WHERE  gpo_id = :gpo AND ur_id = :ur AND bdp_estado ='A' ";
        if ($periodo['mesDesde'] != '') {
            $sqlFiltroP = "WHERE  gpo_id = :gpo AND ur_id = :ur AND msc_id >= " . $periodo['mesDesde'] . " AND bdp_estado ='A' ";
        }

        $sqlFiltro .= " AND bdm_estado='A' AND c.tcl_id != 'C' AND v.bdp_estado IS NULL ";
        $paginadosql = "";
        if ($paginado == true) $paginadosql = " LIMIT " . $nInicial . ", " . $numreg . " ";

        $sql =
            "SELECT
                t.gpo_id, t.ur_id,CONCAT(t.gpo_id,t.ur_id) AS gur, MAX(t.clc_id) AS clc_id, MAX(t.afe_id) AS afe_id,group_concat(distinct t.afe_id) as stringafec,
                t.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia, b.bdo_concepto,
                MAX(v.tdf_id) AS tdf_id_pagado, MAX(v.bdp_referencia) as bdp_referencia ,
                b.bdo_percepcionPresupuestal, b.bdo_percepcionContable, b.bdo_retencionContable, CAST((b.bdo_percepcionPresupuestal + b.bdo_percepcionContable) AS SIGNED) -  b.bdo_retencionContable AS bdo_importe,
                (v.msc_id),
                (v.bdp_estado) AS estado, DATE( MAX(v.bdp_fechaAutorizado) ) AS fechaAutorizacion, DATE( MAX(v.bdp_fechaCancelado) ) AS fechaCancelacion,
                (v.bdp_agrupador) AS agrupador,
                CONCAT( t.gpo_id, t.ur_id, '.', LPAD( $var_MAX(t.msc_id), 2, 000 ), '.', LPAD( MAX(t.afe_id), 6, 0000000 ), '.', LPAD( t.bdo_id, 6, 000000 )  ) AS id
            FROM cat_BancoDocumentos b
            INNER JOIN tesoreria.min_BancoDocumentoMinistrado_$this->ejercicioActual t USING( gpo_id, ur_id, bdo_id )
            INNER JOIN cg_MesContable ms USING( gpo_id, ur_id, msc_id )
            INNER JOIN tesoreria.cat_TipoMinistracion USING( tmi_id )
            INNER JOIN scp_Clc c USING( gpo_id, ur_id, clc_id )
            LEFT JOIN(
                    SELECT * FROM cg_BancoDocumentoPagado
                    {$sqlFiltroP}
                ) v USING( gpo_id, ur_id, afe_id, bdo_id )
                WHERE b.bdo_id IS NOT NULL {$sqlFiltro}
            GROUP BY gpo_id, ur_id, bdo_id,v.msc_id,v.bdp_estado,v.bdp_agrupador
            ORDER BY gpo_id, ur_id, bdo_id,v.msc_id,v.bdp_estado,v.bdp_agrupador;";

        $this->setSql($sql);
        $this->setParams($sqlParams);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = '2000';
        $nav->idTabla = 'tbl-registro-pagado';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No se encontraron CLCs para Registro de Pagado","warning");');
        } else {
            $this->mixScript('
              //Funcion para el multi-check y selec all
                $("#checkAll").on("change", function () {
                    $(`table tbody input[type="checkbox"]`)
                        .prop("checked", this.checked);
                });
                $("#checkAll").click(function () {
                    $("tr.multiseleccionable.seleccionable.seleccionado td:first-child").click();
                    if ($("#checkAll").is(":checked")) {
                        $("tr.multiseleccionable.seleccionable td:first-child").click();
                    }
                });
                $(document).on("click", "#tbl-registro-pagado tbody tr", function () {
                    // Agregar selección a esta fila
                    $(this).addClass("selected-row");
                    // Marcar el checkbox de esta fila
                    $(this).find(".checkselect").prop("checked", $(this).find(".checkselect").prop("checked"));
                });

                //end multicheck
                $("#btnPagar").click(function(e){  
                    const prm =  $("#tbl-registro-pagado").tabla("getFilaSeleccion");
                    var filas = []; //se crea el arreglo en donde se alacenarán todos las filas
                    var pagados = 0;
                    
                    var checados = false;

                    $(".checkselect").each(function(){
                        if($(this).is(":checked")){
                            filas.push({"bdo_id":$(this).attr("bdo_id"),"afe_id":$(this).attr("stringafec")});

                            if($(this).attr("bdo_id") == "A"){
                                pagados++;
                            }
                            checados = true;
                        }
                    });

                    var var_clcFiltro = 1;
                    var var_bdoFiltro = 1;
                    if( $("#inputClc_id").val() == "" ){
                        var_clcFiltro = 2;
                    }
                    if( $("#inputBdo_id").val() == "" && $("#inputRfc_id").val() == "" && $("#inputBdo_referencia").val() == "" ){
                        var_bdoFiltro = 2;
                    }

                    comunica("' . $this->pantalla . '",{
                        metodo: "PagarDetalle",
                        data: filas,
                        datas: pagados,
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion: "") . '",
                        mes: $("#page-tabs li").children(".active").attr("mes"),
                        clc_id: $("#inputClc_id").val(),
                        clcFiltro: var_clcFiltro,
                        gur: prm.gur,
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val(),
                        bdoFiltro: var_bdoFiltro,
                        tmc_id: "A"
                        },
                        true,
                        true
                    );
                });
            ');

            $this->mixScript('
                $(".panel-heading.header").remove();
                $(".panel-footer").remove();
                $(".tabla-footer").remove(); 
            ');
        }
    }

    private function DameInformacionPagina()
    {

        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $clc_id = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');
        $rfc_id = (isset($this->prm->rfc_id) ? $this->prm->rfc_id : '');
        $bdo_referencia = '';
        if (isset($this->prm->bdo_referencia) && !empty($this->prm->bdo_referencia)) {
            $referencias = explode(
                ",",
                str_replace(' ', '', $this->prm->bdo_referencia)
            );
            foreach ($referencias as $bdo) {
                $bdo_referencia .= '"' . $bdo . '",';
            }
            $bdo_referencia = substr($bdo_referencia, 0, -1);
        }
        $periodo['mesDesde'] = (isset($this->prm->mes) ? $this->prm->mes : '');
        $periodo['mesHasta'] = (isset($this->prm->mes) ? $this->prm->mes : '');
        $paginado = (isset($this->prm->paginado) ? $this->prm->paginado : false);
        $numpagina = (isset($this->prm->numpagina) ? $this->prm->numpagina : 1);
        $numreg = (isset($this->prm->numreg) ? $this->prm->numreg : 0);
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

        $detalle = FALSE;

        $nInicial = (($numpagina - 1) * LINEAS_POR_PAGINA);   // Como aqui cargamos la pagina inicialmente, es la primera
        $datos = vinculacionRegistroPagado(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'P',
            $niveles,
            array(
                "gur" => $gur, "clc_id" => $clc_id, "bdo_id" => $bdo_id, "rfc_id" => $rfc_id,
                "bdo_referencia" => $bdo_referencia
            ),
            $paginado,
            $nInicial,
            LINEAS_POR_PAGINA
        );

        if ($paginado == true) {
            unset($datos[0]);
        }

        return json_encode($datos);
    }


    
    private function getTablaRegistroPagado(): string
    {

        $tabla = new stdClass();
        $tabla->esCompacta = true;
        $tabla->id = 'tbl-registro-pagado';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = '';
        //$tabla->seleccionable = true;
        $tabla->multiseleccionable = true;
        $tabla->cabeceraFija = true;

        $tr = array();
        $th = new stdClass();
          $th->texto = '<input type="checkbox" id = "checkAll" class="checkselect">';
        $th->width = '20px';
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
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TDF';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'REFERENCIA PAGADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->width = '10%';
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
        $td->tipo = "check";
        $td->name = 'micheck[]';
        $td->attr = new stdClass();
        $td->attr->clc_id = 'clc_id';
        $td->attr->stringafec = 'stringafec';
        $td->attr->bdo_id = 'bdo_id';
        $td->attr->estado = 'estado';
        $td->attr->activo = 0;
        $td->class='checkselect';
        $td->seleccionable = true;
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
        $td->campoTxt->campos = ['tdf_id_pagado']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdp_referencia']; 
        array_push($trbody, $td);
        
        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdo_importe' ];
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

    private function DibujaPantalla()
    {
        $this->setContenedor("contenedor");

        $this->mixScript('
            $("#page-tabs a").click( function(e){
                e.preventDefault();
                var seleccionado = $(this);
                $("#page-tabs li a").each(function (index) { $(this).removeClass("active"); });
                seleccionado.closest("a").addClass("active");

                comunica("' . $this->pantalla . '",{
                    metodo: "MuestraDatos",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion: "") . '",
                    mes: seleccionado.attr( "mes" ),
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

        $html = $this->getTablaRegistroPagado();
        $this->mixHtml($html);
    }

    private function MuestraDatos()
    {

        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $clc_id = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');
        $rfc_id = (isset($this->prm->rfc_id) ? $this->prm->rfc_id : '');
        $bdo_referencia = '';
        if (isset($this->prm->bdo_referencia) && !empty($this->prm->bdo_referencia)) {

            $referencias = explode(
                ",",
                str_replace(' ', '', $this->prm->bdo_referencia)
            );
            foreach ($referencias as $bdo) {
                $bdo_referencia .= '"' . $bdo . '",';
            }
            $bdo_referencia = substr($bdo_referencia, 0, -1);
        }
        $periodo['mesDesde'] = (isset($this->prm->mes) ? $this->prm->mes : '');
        $periodo['mesHasta'] = (isset($this->prm->mes) ? $this->prm->mes : '');

        if ($clc_id || $bdo_id || $rfc_id || $bdo_referencia) {
            $this->mixScript('
                $(".checkboxPagado .slick-column-name").html("<input type=\"checkbox\">");
            ');
        } else {
            $this->mixScript('
                $(".checkboxPagado .slick-column-name").html("");
            ');
        }

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

        $detalle = FALSE;
        $paginado = TRUE;
        $registroInicial = 0;
        $PrimeraCon = TRUE;
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
            'P',
            $niveles,
            array(
                "gur" => $gur, "clc_id" => $clc_id, "bdo_id" => $bdo_id, "rfc_id" => $rfc_id,
                "bdo_referencia" => $bdo_referencia
            ),
            $paginado,
            $registroInicial,
            LINEAS_POR_PAGINA,
            $PrimeraCon
        );
        return false;
    }

    private function DameInformacion()
    {
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $clc_id = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');
        $rfc_id = (isset($this->prm->rfc_id) ? $this->prm->rfc_id : '');
        $bdo_referencia = '';
        if (isset($this->prm->bdo_referencia) && !empty($this->prm->bdo_referencia)) {

            $referencias = explode(
                ",",
                str_replace(' ', '', $this->prm->bdo_referencia)
            );
            foreach ($referencias as $bdo) {
                $bdo_referencia .= '"' . $bdo . '",';
            }
            $bdo_referencia = substr($bdo_referencia, 0, -1);
        }
        $periodo['mesDesde'] = (isset($this->prm->mes) ? $this->prm->mes : '');
        $periodo['mesHasta'] = (isset($this->prm->mes) ? $this->prm->mes : '');

        if ($clc_id || $bdo_id || $rfc_id || $bdo_referencia) {
            $this->mixScript('
                $("#myCheck").css("display", "block");
            ');
        } else {
            $this->mixScript('
                $("#myCheck").css("display", "none");
                $("#myCheck").prop("checked", false); 
            ');
        }

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

        $detalle = FALSE;
        $paginado = TRUE;
        $registroInicial = 0;
        $datos = vinculacionRegistroPagado(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'P',
            $niveles,
            array(
                "gur" => $gur, "clc_id" => $clc_id, "bdo_id" => $bdo_id, "rfc_id" => $rfc_id,
                "bdo_referencia" => $bdo_referencia
            ),
            $paginado,
            $registroInicial,
            LINEAS_POR_PAGINA
        );

        $nTotalPages = 0;
        if ($paginado == true) {

            $nTotalPages = $datos[0]['gpo_id'];
            unset($datos[0]);
        }
        if ($nTotalPages > 1 && $paginado == true) {
            $this->mixScript('
                $("#PagadoPaginador").attr("last", 1 );
                var options = {
                    numberOfPages: 5,
                    currentPage: 1,
                    totalPages: ' . $nTotalPages . ',
                    containerClass: "pull-right",
                    listContainerClass: "nav nav-tabs",
                    itemContainerClass: function ( type, page, current ) { return (page === current) ? "active" : "pointer-cursor"; },
                    onPageClicked: function(e,originalEvent,type,page){
                        // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                        if ( $("#PagadoPaginador").attr("last") != page ) {
                            comunica("' . $this->pantalla . '",{
                                metodo: "MuestraDatosPagina",
                                mes: $("#page-tabs li").children(".active").attr("mes"),
                                origen: "' . $this->origen . '",
                                gur: $("#inputGur").val(),
                                clc_id: $("#inputClc_id").val(),
                                bdo_id: $("#inputBdo_id").val(),
                                rfc_id: $("#inputRfc_id").val(),
                                bdo_referencia: $("#inputBdo_referencia").val().replace(/\s/g, ""),
                                paginado: true,
                                numpagina: page ,
                                numreg: ' . LINEAS_POR_PAGINA . '
                            });
                            $("#PagadoPaginador").attr("last", page );
                        }
                    }
                }
                console.log(options);
                $("#PagadoPaginador").bootstrapPaginator(options);
            ');
        } else {
            $this->mixScript('$("#PagadoPaginador").html("");');
        }
        return json_encode($datos);
    }

    private function CallProcedure()
    {
        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
        switch ($this->prm->tmc_id) {
            case 'A':
                $bdos = '';
                $afes = '';
                $bdos = $this->prm->data;
                $bdoArray = explode(',', $bdos);
                $afes = $this->prm->afes;

                $agrupador = $gpo_id_documento . $ur_id_documento . $this->prm->mes . 'P';
                if (count(explode(",", $this->prm->data)) > 1) {
                    $agrupador .= "A" . "" . md5($this->prm->data); // AGRUPADOR POR VARIOS DOCUMENTOS
                } else {
                    $agrupador .= "A" . "" . $this->prm->data; // AGRUPADOR PARA UN SOLO DOCUMENTO
                }
                $this->prm->bdp_concepto = strtoupper(htmlspecialchars(str_replace(
                    '"',
                    '\"',
                    $this->prm->bdp_concepto
                )));
                $espagoDirec = validaTipoPago(
                    $gpo_id_documento,
                    $ur_id_documento,
                    $bdoArray[0]
                );
                // INICIAMOS LA TRANSACCIÓN
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $this->prm->bdp_referencia = str_replace(
                    "'",
                    "\'",
                    $this->prm->bdp_referencia
                );
                $this->prm->bdp_concepto = str_replace(
                    "'",
                    "\'",
                    $this->prm->bdp_concepto
                );
                $afest = '';
                $cadena = substr($afes,-1);
                if ($cadena == ',') {
                    $afest = substr($afes, 0, -1);
                } else {
                    $afest = $afes;
                }
                $sql = "
                    INSERT INTO ctrl" . $this->ejercicioActual . "Temporal.tem_BancoDocumentoPagado(
                    gpo_id, ur_id, msc_id, clc_id, afe_id, bdo_id,
                    bdp_fechaAutorizado,
                    bdp_percepcionPresupuestal, bdp_percepcionContable, bdp_retencionContable,
                    bdp_agrupador, tdf_id, bdp_referencia,
                    bdp_concepto,
                    pot_id, bdp_estado, bdp_tipoAgrupador,
                    bdp_poliza,
                    usr_id
                    )
                    select * from(
                        SELECT
                        t.gpo_id, t.ur_id, " . $this->prm->mes . " AS  mes , t.clc_id, t.afe_id, t.bdo_id,
                        '" . $this->prm->fechaMysql . "',
                        SUM(a.ncp_percepcionNeto) AS fncp_percepcionNeto, SUM(a.ncc_percepcion) as fncc_percepcion, SUM(a.ncc_retencion) as fncc_retencion,
                        '$agrupador', '" . $this->prm->tdf_id_pagado . "', '" . strtoupper($this->prm->bdp_referencia) . "' as bdp_referencia,
                        '" . $this->prm->bdp_concepto . "',
                        '" . $this->prm->pot_id . "' as poliza, 'A' as Estado, 'D' as tipoAgrupador,
                        " . $this->prm->bdp_poliza . " as bdp_poliza,
                        " . $this->usr_id . "
                        FROM cat_BancoDocumentos b
                        INNER JOIN tesoreria.min_BancoDocumentoMinistrado_" . $this->ejercicioActual . " t USING( gpo_id, ur_id, bdo_id )
                        LEFT JOIN (
                            SELECT n.gpo_id, n.ur_id, n.clc_id, n.afe_id, n.bdo_id,
                                SUM(n.ncp_percepcionNeto ) AS ncp_percepcionNeto, 0 AS ncc_retencion, 0 AS ncc_percepcion
                            FROM scp_Presupuestal n
                            INNER JOIN scp_Afectacion a USING( gpo_id, ur_id, clc_id, afe_id, epg_id, ecf_id )
                            LEFT JOIN(
                                SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id,max(bdp_estado) as bdp_estado FROM  cg_BancoDocumentoPagado
                                WHERE gpo_id = $gpo_id_documento AND ur_id = $ur_id_documento
                                AND bdo_id IN ( $bdos )
                                AND afe_id IN ( $afest )
                                AND (  bdp_estado = 'A' )
                                GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                            )p USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                            WHERE n.gpo_id = $gpo_id_documento AND n.ur_id = $ur_id_documento
                            AND n.bdo_id IN ( $bdos )
                            AND n.afe_id IN ( $afest )
                            AND a.eaf_id = 'A'
                            AND ( p.bdp_estado IS NULL OR p.bdp_estado = 'C' )
                            GROUP BY n.gpo_id, n.ur_id, n.clc_id, n.afe_id, n.bdo_id
                            UNION ALL
                            SELECT  n.gpo_id, n.ur_id, n.clc_id, n.afe_id, n.bdo_id,
                                0 AS ncp_percepcionNeto, SUM(n.ncc_retencion) AS ncc_retencion, SUM(n.ncc_percepcion) AS ncc_percepcion
                            FROM scp_Contable n
                            INNER JOIN scp_Afectacion a USING( gpo_id, ur_id, clc_id, afe_id )
                            LEFT JOIN (
                                SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id,max(bdp_estado) as bdp_estado FROM  cg_BancoDocumentoPagado
                                WHERE gpo_id = $gpo_id_documento AND ur_id = $ur_id_documento
                                AND bdo_id IN ( $bdos )
                                AND afe_id IN ( $afest )
                                AND (  bdp_estado = 'A' )
                                GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                            )p USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                            WHERE n.gpo_id = $gpo_id_documento AND n.ur_id = $ur_id_documento
                                AND n.bdo_id IN ( $bdos )
                                AND n.afe_id IN ( $afest )
                                AND a.eaf_id = 'A'
                                AND ( p.bdp_estado IS NULL OR p.bdp_estado = 'C' )
                                GROUP BY n.gpo_id, n.ur_id, n.clc_id, n.afe_id, n.bdo_id
                        )a USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                        WHERE t.gpo_id = $gpo_id_documento AND t.ur_id = $ur_id_documento
                        AND t.bdo_id IN ( $bdos )
                        AND t.afe_id IN ( $afest )
                        AND t.bdm_estado = 'A'
                        AND a.afe_id IS NOT NULL
                        GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                    ) pagado1
                    ON DUPLICATE KEY UPDATE
                        bdp_fechaAutorizado = '" . $this->prm->fechaMysql . "',
                        bdp_agrupador = '$agrupador',
                        tdf_id = '" . $this->prm->tdf_id_pagado . "',
                        bdp_referencia = '" . strtoupper($this->prm->bdp_referencia) . "',
                        bdp_concepto = '" . $this->prm->bdp_concepto . "',
                        pot_id = '" . $this->prm->pot_id . "',
                        bdp_estado = 'A',
                        bdp_poliza = " . $this->prm->bdp_poliza . ",
                        usr_id = " . $this->usr_id . ",
                        bdp_percepcionPresupuestal=pagado1.fncp_percepcionNeto,
                        bdp_percepcionContable=pagado1.fncc_percepcion,
                        bdp_retencionContable=pagado1.fncc_retencion
                ";
                $conexion->setSql($sql);
                $conexion->consultar();
                $conexion->desconectar();

                $bandera = 0;
                $mensajesp = "ERROR";
                $generaPagado = new sp_AutorizarPagoDocumento();
                $generaPagado->setGpo_id($gpo_id_documento);
                $generaPagado->setUr_id($ur_id_documento);
                $generaPagado->setBdp_agrupador($agrupador);
                $generaPagado->setTmc_id('A');
                $generaPagado->setBit_observacion("REGISTRO DE PAGO DESDE LA PANTALLA DE VINCULACIÓN");
                $generaPagado->setEjercicio($this->ejercicioActual);
                $generaPagado->setSis_id("SCP");
                $generaPagado->execute(false, false);
                $bandera = $generaPagado->getBandera();
                $mensajesp = $generaPagado->getMensaje();
                $mensaje = "";
                $tipo = "";

                if ($bandera != 1) {
                    $conexion->conectar();
                    $mensaje = "ERROR: " . $mensajesp;
                    $tipo = "error";
                    $conexion->setSql("
                        DELETE
                        FROM ctrl" . $this->ejercicioActual . "Temporal.tem_BancoDocumentoPagado
                        WHERE gpo_id = $gpo_id_documento AND ur_id = $ur_id_documento
                        AND bdp_agrupador = '$agrupador'
                    ");
                    $conexion->consultar();
                    $conexion->desconectar();

                    unset($conexion);
                    $this->mixScript('
                        notify("' . $mensaje . '","' . $tipo . '");
                        $("#btnRegistraPago").removeAttr("disabled");
                    ');
                    return FALSE;
                } else {
                    unset($conexion);
                    $mensaje = $mensajesp;
                    $tipo = "success";

                    $this->mixScript('
                        notify("' . $mensaje . '","' . $tipo . '");
                        $("#btnRegistraPago").removeAttr("disabled");
                        comunica("' . $this->pantalla . '",{
                        metodo: "Documento",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion
                        : "") . '",
                        mes: "' . $this->prm->mes . '",
                        gur: "' . $this->prm->gur . '",
                        clc_id: "' . $this->prm->clc_id . '",
                        clcFiltro: "' . $this->prm->clcFiltro . '",
                        bdo_id: "' . $bdoArray[0] . '",
                            afe_id: "' . ($afes) . '",
                        rfc_id: "",
                        bdo_referencia: "",
                        bdoFiltro: "' . $this->prm->bdoFiltro . '",
                        agrupador: "' . $agrupador . '"
                        });
                    ');
                }
                break;
            case 'C':
                break;
        }
    }

    private function Documento()
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql(" SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = $this->gpo_id AND ur_id = $this->ur_id ");
        $conexion->consultar();

        $mesContableAbierto = FALSE;
        while ($dataRow = $conexion->extraer()) {
            $mesContable[$dataRow['msc_id']] = $dataRow;
            if (!$mesContableAbierto && $dataRow['msc_id'] == 'A') {
                $mesContableAbierto = $dataRow['msc_id'];
            }
        }
        if (isset($this->prm->gur)) {
            $gpo_id_documento = substr($this->prm->gur, 0, 1);
            $ur_id_documento = substr($this->prm->gur, 1, 2);
        } else {
            $gpo_id_documento = $this->gpo_id;
            $ur_id_documento = $this->ur_id;
            $this->prm->gur = $this->gpo_id . $this->ur_id;
        }
        $clc_id_documento = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id_documento = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');
        $afe_idstr = (isset($this->prm->afe_id) ? $this->prm->afe_id : '');
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
        $btnRegresa->icon = 'solid fa-circle-left me-2';
        $btnRegresa->color = 'gris';
        $btnRegresa->class = 'me-1';

        $botones = $this->ponBoton($btnRegresa); //*/

        $this->agregaTools($botones);

        $documento = getDocumento(
            $gpo_id_documento,
            $ur_id_documento,
            $bdo_id_documento,
            $this->prm->rfc_id,
            $this->prm->bdo_referencia,
            array("ejercicio" => $this->ejercicioActual, "tdf_id" => (isset($this->prm->tdf_id) ? $this->prm->tdf_id
                : ''), "mmc_id" => "P", "afe_id" => $afe_idstr)
        );
        if (!isset($this->prm->tdf_id)) {
            $this->prm->tdf_id = $documento->tipoDocumentoFuente;
        }
        $html = '
            <legend style="border-bottom: 1px solid #e5e5e5; margin-bottom: 22px;">Análisis del documento a generar la póliza del pagado</legend>
            <div>
                <fieldset class="col-md-12 col-xs-12">
                    <legend style="border-bottom: 1px solid #e5e5e5; margin-bottom: 22px;">Información del Documento</legend>
                    <table class="table table-bordered">
                        <tr class="success" style="background-color: #dff0d8 !important;">
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
                            <td width="10%" align="right"><span style="float:left">$</span>' . number_format($documento->percepcionPresupuestal,2) . '</td>
                            <td width="10%" align="right"><span style="float:left">$</span>' . number_format($documento->percepcionContable,2) . '</td>
                            <td width="10%" align="right"><span style="float:left">$</span>' . number_format($documento->retencionContable,2) . '</td>
                            <td>' . $documento->fechaDocumento . '</td>
                        </tr>
                    </table>
                </fieldset><br/>
        ';
        //preguntamos la(s) CLC(s) del documento
        //preguntamos el estado de los meses contables en funcion de las fechas de la ministracion
        foreach ($documento->detallePagado as $key => $value) {
            if ($clc_id_documento != '') {
                if ($clc_id_documento == $value->clc_id) {
                    $clc_array[$value->gpo_id . $value->ur_id . $value->clc_id]['gpo_id']
                        = $value->gpo_id;
                    $clc_array[$value->gpo_id . $value->ur_id . $value->clc_id]['ur_id']
                        = $value->ur_id;
                    $clc_array[$value->gpo_id . $value->ur_id . $value->clc_id]['clc_id']
                        = $value->clc_id;
                }
            } else {
                $clc_array[$value->gpo_id . $value->ur_id . $value->clc_id]['gpo_id']
                    = $value->gpo_id;
                $clc_array[$value->gpo_id . $value->ur_id . $value->clc_id]['ur_id']
                    = $value->ur_id;
                $clc_array[$value->gpo_id . $value->ur_id . $value->clc_id]['clc_id']
                    = $value->clc_id;
            }

            if ($this->prm->agrupador == $value->agrupador) {
                if ($value->tipoPagado != 3) {
                    $generaPolizaAutorizado = TRUE;
                    $fechaAutorizado = $value->fechaAutorizado;
                }
                $estadoMesAutorizado = $mesContable[str_pad(
                    $value->mesAutorizado,
                    2,
                    '0',
                    STR_PAD_LEFT
                )]['ems_id'];
                if ($value->estado == 'C') {
                    if ($generaPolizaAutorizado) {
                        $generaPolizaCancelado = TRUE;
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
            $estadoPago = $value->estado;
        }

        /* INFORMACIÓN DE LA CLC EN LA QUE ESTA EL DOCUMENTO */
        if (count($clc_array) != 0) {
            $html .= '
                <fieldset class="col-md-12 col-xs-12 ">
                    <legend>CLC en la que esta el documento
                        <span id="menclc" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                    </legend>
            ';
            foreach ($clc_array as $key => $value) {
                $clcDocumento = pintaClc(
                    $value['gpo_id'],
                    $value['ur_id'],
                    $value['clc_id'],
                    array("ejercicio" => $this->ejercicioActual, "bdo_id" => $bdo_id_documento, "todo" => TRUE)
                );
                $html .= $clcDocumento->html;
            }
            $html .= '
                </fieldset></br>
            ';
        }

        /* DATOS DEL REGISTRO DE PAGO */
        $html .= '
            <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                <legend>Datos del Registro de Pago
                    <span id="pagado" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                </legend>
        ';
        $pagado = pintaPagado(
            $gpo_id_documento,
            $ur_id_documento,
            $bdo_id_documento,
            array("ejercicio" => $this->ejercicioActual, "clc_id" => $clc_id_documento, "agrupador" => $this->prm->agrupador)
        );
        $html .= $pagado->html;
        $html .= '
            </fieldset></br>
        ';

        /*         * *********************************** Información de la Póliza Contable ************************************************************** */
        $polizas = getPolizaAgrupador(
            $this->gpo_id,
            $this->ur_id,
            $this->prm->agrupador
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
                            $btnGenera->icon = 'solid fa-check me-2';
                            $btnGenera->class = 'me-1';
                            $botonGeneraPa = $this->ponBoton($btnGenera);

                            $this->mixScript('
                                $("#btnReGeneraPolizaA").click(function(){   
                                    $(".ajs-button").removeClass("btn-primary").addClass("btn-success");

                                    alertify.confirm(
                                        "RE HACER LA POLIZA ' . (isset($value->poliza) ? $value->poliza : '') . '",
                                        "Esta acción re construira la póliza de autorización como fue generada por el sistema de manera automatica, desea proceder?",
                                        function(){
                                            comunica("' . $this->pantalla . '",{
                                                metodo: "GeneraPoliza",
                                                origen: "' . $this->origen . '",
                                                seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                                mes: "' . $this->prm->mes . '",
                                                gur: "' . $this->prm->gur . '",
                                                clc_id: "' . $this->prm->clc_id . '",
                                                clcFiltro: "' . $this->prm->clcFiltro . '",
                                                bdo_id: "' . $this->prm->bdo_id . '",
                                                tdf_id: "' . $this->prm->tdf_id . '",
                                                rfc_id: "' . $this->prm->rfc_id . '",
                                                bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                                bdoFiltro: "' . $this->prm->bdoFiltro . '",
                                                gpo_id: "' . $value->gpo_id . '",
                                                ur_id: "' . $value->ur_id . '",
                                                ue_id: "' . $value->ue_id . '",
                                                fecha: "' . $fechaAutorizado . '",
                                                agrupador: "' . $this->prm->agrupador . '",
                                                tmc_id: "A"
                                            });
                                        },
                                        undefined
                                    ).set("labels", {
                                        ok: "Aceptar", cancel: "Cancelar"
                                    });
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
                            $btnGenera->icon = 'solid fa-check me-2';
                            $btnGenera->class = 'me-1';
                            $botonGeneraPc = $this->ponBoton($btnGenera);

                            $this->mixScript('
                                $("#btnReGeneraPolizaC").click(function(){
                                    $(".ajs-button").removeClass("btn-primary").addClass("btn-success");

                                    alertify.confirm(
                                        "RE HACER LA POLIZA ' . (isset($value->poliza) ? $value->poliza : '') . '",
                                        "Esta acción re construira la póliza de cancelación como fue generada por el sistema de manera automatica, desea proceder?",
                                        function(){
                                            comunica("' . $this->pantalla . '",{
                                                metodo: "GeneraPoliza",
                                                origen: "' . $this->origen . '",
                                                seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                                mes: "' . $this->prm->mes . '",
                                                gur: "' . $this->prm->gur . '",
                                                clc_id: "' . $this->prm->clc_id . '",
                                                clcFiltro: "' . $this->prm->clcFiltro . '",
                                                bdo_id: "' . $this->prm->bdo_id . '",
                                                tdf_id: "' . $this->prm->tdf_id . '",
                                                rfc_id: "' . $this->prm->rfc_id . '",
                                                bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                                bdoFiltro: "' . $this->prm->bdoFiltro . '",
                                                gpo_id: "' . $value->gpo_id . '",
                                                ur_id: "' . $value->ur_id . '",
                                                ue_id: "' . $value->ue_id . '",
                                                fecha: "' . $fechaCancelado . '",
                                                agrupador: "' . $this->prm->agrupador . '",
                                                tmc_id: "C"
                                            });
                                        },
                                        undefined
                                    ).set("labels", {
                                        ok: "Aceptar", cancel: "Cancelar"
                                    });
                                });
                            ');
                        } else {
                            $botonGeneraPc = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado </div>';
                        }
                    } elseif ($estadoMesCancelado == 'A') {
                        $botonGeneraPc = '<div style="display:inline-block; color:#900; padding:10px;" >Regenerar Póliza</div>';
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
                $btnGeneraPoliza->icon = 'solid fa-check me-2';
                $btnGeneraPoliza->class = 'me-1';

                $botones = $this->ponBoton($btnGeneraPoliza);
                //si el mes contable se encuentra abierto
                if ($estadoMesAutorizado == 'A') {
                    $this->mixScript('
                        $("#btnGeneraPolizaA").click(function(){
                            comunica("' . $this->pantalla . '",{
                                metodo: "GeneraPoliza",
                                origen: "' . $this->origen . '",
                                seleccion: "' . (isset($this->prm->seleccion)
                                ? $this->prm->seleccion : "") . '",
                                mes: "' . $this->prm->mes . '",
                                gur: "' . $this->prm->gur . '",
                                clc_id: "' . $this->prm->clc_id . '",
                                clcFiltro: "' . $this->prm->clcFiltro . '",
                                bdo_id: "' . $this->prm->bdo_id . '",
                                tdf_id: "' . $this->prm->tdf_id . '",
                                rfc_id: "' . $this->prm->rfc_id . '",
                                bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                bdoFiltro: "' . $this->prm->bdoFiltro . '",
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
                    $btnGeneraPolizaCerrado->icon = 'solid fa-check me-2';
                    $btnGeneraPolizaCerrado->class = 'me-1';

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
                    $btnCancelar->icon = 'times me-2';
                    $btnCancelar->name = 'nuevo';
                    $btnCancelar->color = 'rojo';
                    $btnCancelar->text = 'Cancelar';
                    $btnCancelar->value = 'Cancelar';

                    $html .= '
                        <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                            <div id="capfecha">
                                <legend>Mes del Pagado esta cerrado, capture una nueva fecha</legend>
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
                                seleccion: "' . (isset($this->prm->seleccion)
                                ? $this->prm->seleccion : "") . '",
                                mes: "' . $this->prm->mes . '",
                                gur: "' . $this->prm->gur . '",
                                clc_id: "' . $this->prm->clc_id . '",
                                clcFiltro: "' . $this->prm->clcFiltro . '",
                                bdo_id: "' . $this->prm->bdo_id . '",
                                tdf_id: "' . $this->prm->tdf_id . '",
                                rfc_id: "' . $this->prm->rfc_id . '",
                                bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                bdoFiltro: "' . $this->prm->bdoFiltro . '",
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
            }
        } elseif ($estadoMesAutorizado == 'A') {
            $botones = '<div style="display:inline-block; color:#900; padding:10px;">Generar Póliza</div>';
        } elseif ($estadoMesAutorizado == 'C') {
            $botones = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización esta cerrado</div>';
        }

        if ($this->escritura) {
            if (
                $generaPolizaAutorizado && $polizaAut->valido && $generaPolizaCancelado
                && !$polizaCan->valido
            ) {

                $btnGeneraPoliza = new stdClass();
                $btnGeneraPoliza->id = 'btnGeneraPolizaC';
                $btnGeneraPoliza->name = 'btnGeneraPolizaC';
                $btnGeneraPoliza->text = 'Genera Póliza de Cancelación';
                $btnGeneraPoliza->value = 'Genera Póliza de Cancelación';
                $btnGeneraPoliza->color = 'verde';
                $btnGeneraPoliza->icon = 'solid fa-check me-2';
                $btnGeneraPoliza->class = 'me-1';

                $botones = $this->ponBoton($btnGeneraPoliza);

                if ($estadoMesCancelado == 'A') {
                    $this->mixScript('
                        $("#btnGeneraPolizaC").click(function(){
                            comunica("' . $this->pantalla . '",{
                                metodo: "GeneraPoliza",
                                origen: "' . $this->origen . '",
                                seleccion: "' . (isset($this->prm->seleccion)
                                ? $this->prm->seleccion : "") . '",
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
                    $btnGeneraPolizaCerrado->icon = 'solid fa-check me-2';
                    $btnGeneraPolizaCerrado->class = 'me-1';

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
                    $btnCancelar->icon = 'times me-2';
                    $btnCancelar->name = 'nuevo';
                    $btnCancelar->color = 'rojo';
                    $btnCancelar->text = 'Cancelar';
                    $btnCancelar->value = 'Cancelar';

                    $html .= '
                        <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                            <div id="capfecha">
                                <legend>Mes de cancelación del Pagado esta cerrado, capture una nueva fecha</legend>
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
                                seleccion: "' . (isset($this->prm->seleccion)
                                ? $this->prm->seleccion : "") . '",
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
            }
        } elseif ($estadoMesCancelado == 'A') {
            $botones = '<div style="display:inline-block; color:#900; padding:10px;">Generar Póliza</div>';
        } elseif ($estadoMesCancelado == 'C') {
            $botones = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado</div>';
        }

        $this->agregaTools($botones);
        $this->mixHtml($html);

        $this->setContenedor('botonA');
        $this->mixHtml($botonGeneraPa);
        $this->setContenedor('botonC');
        $this->mixHtml($botonGeneraPc);

        $this->mixScript('
            $("#btnRegresa").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo: "PonBarraBotones",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "") . '",
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
            $fechaValidar->anio = $this->ejercicioActual;
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
            $generaPolizaPagado = new sp_PagadoCancelado();
            $generaPolizaPagado->setGpo_id($gpo_id_documento);
            $generaPolizaPagado->setUr_id($ur_id_documento);
            $generaPolizaPagado->setBdp_agrupador($this->prm->agrupador);
            $generaPolizaPagado->setPma_fecha($fecha);
            $generaPolizaPagado->setTmc_id($this->prm->tmc_id);
            $generaPolizaPagado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DEL PAGADO");
            $generaPolizaPagado->setEjercicio($this->ejercicioActual);
            $generaPolizaPagado->setSis_id("SCG");
            $generaPolizaPagado->execute(false, false);
            $bandera = $generaPolizaPagado->getBandera();
            $mensajesp = $generaPolizaPagado->getMensaje();
        } else {

            $generaPolizaPagado = new sp_PagadoAutorizado();
            $generaPolizaPagado->setGpo_id($gpo_id_documento);
            $generaPolizaPagado->setUr_id($ur_id_documento);
            $generaPolizaPagado->setBdp_agrupador($this->prm->agrupador);
            $generaPolizaPagado->setPma_fecha($fecha);
            $generaPolizaPagado->setTmc_id($this->prm->tmc_id);
            $generaPolizaPagado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DEL PAGADO");
            $generaPolizaPagado->setEjercicio($this->ejercicioActual);
            $generaPolizaPagado->setSis_id("SCG");
            $generaPolizaPagado->execute(false, false);
            $bandera = $generaPolizaPagado->getBandera();
            $mensajesp = $generaPolizaPagado->getMensaje();
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
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "") . '",
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
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "") . '",
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

    //muestra la informacion de los datos de transferencia de los documentos a pagar
    private function PagarDetalle()
    {
        $conexion = new AccesoDatos();
        if (!isset($this->prm->gur)) {
            $this->prm->gur = $this->gpo_id . $this->ur_id;
        }
        if(isset($this->prm->data)){
        $datos = $this->prm->data;
        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
        $pdSi = 0;
        $pdNo = 0;
        foreach ($datos as $row) {
            if (validaTipoPago($gpo_id_documento, $ur_id_documento, $row->bdo_id))
                $pdSi++;
            else $pdNo++;
        }
        if ($pdSi > 0 && $pdNo > 0) {
            $this->mixScript('notify("Seleccionó documentos de pago directo y documentos normales", "warning" );');
            $this->PonBarraBotones();
        } else {
            $botones = '';
            $btnRegresa = new stdClass();
            $btnRegresa->id = 'btnRegresa';
            $btnRegresa->name = 'btnRegresa';
            $btnRegresa->text = 'Regresar';
            $btnRegresa->value = 'Regresar';
            $btnRegresa->type = 'button';
            $btnRegresa->icon = 'solid fa-circle-left me-2';
            $btnRegresa->color = 'gris';
            $btnRegresa->class = 'me-1';

            $botones = $this->ponBoton($btnRegresa);

            $bdos = '';
            $afes = '';
            foreach ($datos as $bd) {
                if ($bdos == "") $bdos .= $bd->bdo_id;
                else $bdos .= "," . $bd->bdo_id;
                if ($afes == "") {

                    $afes .= $bd->afe_id;
                } else {
                    $pos = strpos($afes, $bd->afe_id);
                    if ($pos === false) {
                        $afes .= "," . $bd->afe_id;
                    }
                }
            }

            $html = '<span id="bds" bds="' . $bdos . '"/><span id="afes" afes="' . $afes . '"/><div>';
            if (count(array($datos)) > 1)
                $html .= '<legend>Análisis de los documentos a pagar</legend>';
            else $html .= '<legend>Análisis del documento a pagar</legend>';


            $bandera = false;
            $om = $this->DocumentoPagar($datos);
            if ($pdSi > 0 && isset($om->clc_id)) {
                $bandera = true;
                $query = "
                    SELECT bdm_referencia, tdf_id, bdm_fechaAutorizado FROM tesoreria.min_BancoDocumentoMinistrado_" . $this->ejercicioActual . "
                    WHERE gpo_id = " . $gpo_id_documento . " AND ur_id = " . $ur_id_documento . " AND clc_id = " . $om->clc_id . " and bdm_estado='A'
                    LIMIT 1";
                $conexion->conectar();
                $conexion->setSql($query);
                $conexion->consultar();
                $conexion->desconectar();
                $datosInput = $conexion->extraer();
            }
            $cadena = substr($afes,-1);
            if ($cadena == ',') {
                $afest = substr($afes, 0, -1);
            } else {
                $afest = $afes;
            }
            
            //Obtener fecha ministrado para mostrar como sugerencia
            $query = "
                SELECT MAX(bdm_fechaAutorizado) AS bdm_fechaAutorizado
                FROM tesoreria.min_BancoDocumentoMinistrado_" . $this->ejercicioActual . "
                WHERE gpo_id = " . $gpo_id_documento . " AND ur_id = " . $ur_id_documento . " AND bdo_id IN( " . $bdos . " ) AND afe_id IN( " . $afest . " ) and bdm_estado='A' ";
            $conexion->conectar();
            $conexion->setSql($query);
            $conexion->consultar();
            $conexion->desconectar();
            $datosInputFecha = $conexion->extraer();

            $html .= '
                <fieldset class="col-md-12 col-xs-12">
                    ' . $om->html . '
                </fieldset><br/>
            ';

            $this->mixScript($om->script);

            if ($om->valido) {
                $bdp_concepto = new stdClass();
                $bdp_concepto->id = 'bdp_concepto';
                $bdp_concepto->placeholder = "Concepto de pago";
                $bdp_concepto->required = true;
                $bdp_concepto->maxlength = 600;
                $bdp_concepto->label = "Concepto de pago";
                $bdp_concepto->value = htmlspecialchars($om->concepto);

                $bdp_referencia = new stdClass();
                $bdp_referencia->id = 'bdp_referencia';
                $bdp_referencia->placeholder = "Número de documento";
                $bdp_referencia->required = true;
                $bdp_referencia->maxlength = 100;
                $bdp_referencia->label = "Número de documento";
                $bdp_referencia->value = htmlspecialchars($bandera ? $datosInput["bdm_referencia"]
                    : "");

                $tdf_id = new stdClass();
                $tdf_id->id = 'tdf_id';
                $tdf_id->placeholder = "Tipo de Documeto";
                $tdf_id->required = true;
                $tdf_id->label = "Tipo de Documeto";
                $tdf_id->sql = "SELECT * FROM cat_TipoDocumentoFuente";
                $tdf_id->comoAttr = "tdf_id";
                $tdf_id->selecciona = $bandera ? $datosInput["tdf_id"] : "";

                $pot_id = new stdClass();
                $pot_id->id = 'pot_id';
                $pot_id->placeholder = "Tipo de Poliza";
                $pot_id->required = true;
                $pot_id->label = "Tipo de Poliza";
                $pot_id->sql = 'SELECT pot_id,pot_nombre FROM cat_TipoPoliza WHERE pot_id IN("D","E")';
                $pot_id->comoAttr = "pot_id";

                $bdp_polizaSql = '
                    SELECT 1 AS bdp_poliza, "ORDEN Y GASTO"
                    UNION ALL
                    SELECT 2 AS bdp_poliza, "SOLO ORDEN"
                ';
                $pot_id->selecciona = $bandera ? "D" : "";

                $bdp_poliza = new stdClass();
                $bdp_poliza->id = 'bdp_poliza';
                $bdp_poliza->placeholder = "Generar Poliza";
                $bdp_poliza->required = true;
                $bdp_poliza->label = "Generar poliza";
                $bdp_poliza->sql = $bdp_polizaSql;
                $bdp_poliza->comoAttr = "bdp_poliza";
                $bdp_poliza->selecciona = $bandera ? 1 : "";

                $bdp_fechaAutorizado = new stdClass();
                $bdp_fechaAutorizado->id = 'bdp_fechaAutorizado';
                $bdp_fechaAutorizado->name = 'control';
                $bdp_fechaAutorizado->class = 'fecha';
                $bdp_fechaAutorizado->label = "Fecha de pago"; #5337329
                $bdp_fechaAutorizado->placeholder = "Fecha";
                $bdp_fechaAutorizado->required = true;
                $bdp_fechaAutorizado->formato = 'fecha';
                $bdp_fechaAutorizado->labelClass = '_32 txtL';
                $bdp_fechaAutorizado->enDiv = true;
                $bdp_fechaAutorizado->value = date(
                    'd/m/Y',
                    strtotime($datosInputFecha["bdm_fechaAutorizado"])
                );

                $html .= '<fieldset class="col-md-12 col-xs-12"><fieldset class="col-md-4 col-xs-4">';
                $html .= $this->ponInput($bdp_concepto);
                $html .= '</fieldset>';

                $html .= '<fieldset class="col-md-4 col-xs-4">';
                $html .= $this->ponInput($bdp_referencia);
                $html .= '</fieldset>';

                $html .= '<fieldset class="col-md-4 col-xs-4">';
                $html .= $this->ponCombo($tdf_id);
                $html .= '</fieldset><br>';

                $html .= '<fieldset class="col-md-6 col-xs-6">';
                $html .= $this->ponCombo($pot_id);
                $html .= '</fieldset>';

                $html .= '<fieldset class="col-md-2 col-xs-2">';
                $html .= $this->ponInput($bdp_fechaAutorizado);
                $html .= '</fieldset>';

                $html .= '<fieldset class="col-md-4 col-xs-4">';
                $html .= $this->ponCombo($bdp_poliza);
                $html .= '</fieldset></fieldset>';

                if ($this->escritura) {
                    $btnRegistraPago = new stdClass();
                    $btnRegistraPago->id = 'btnRegistraPago';
                    $btnRegistraPago->icon = 'send';
                    $btnRegistraPago->name = 'nuevo';
                    $btnRegistraPago->color = 'verde';
                    $btnRegistraPago->text = 'Nuevo';
                    $btnRegistraPago->value = 'Registrar Pago';
                    $botones .= $this->ponBoton($btnRegistraPago);

                    $anio = $this->ejercicioActual;
                    $mes = date('m');
                    $dia = date('d');
                    $year = date('Y');

                    $this->mixScript('
                        $("#bdp_referencia").keypress(function (key) {
                            //window.console.log(key.charCode)
                            if ((key.charCode < 97 || key.charCode > 122)//letras mayusculas
                                && (key.charCode < 65 || key.charCode > 90) //letras minusculas
                                && (key.charCode < 48 || key.charCode > 57) //letras minusculas
                                //&& (key.charCode != 45) //retroceso
                                //&& (key.charCode != 241) //ñ
                                 //&& (key.charCode != 209) //Ñ
                                 //&& (key.charCode != 32) //espacio
                                 //&& (key.charCode != 225) //á
                                 //&& (key.charCode != 233) //é
                                 //&& (key.charCode != 237) //í
                                 //&& (key.charCode != 243) //ó
                                 //&& (key.charCode != 250) //ú
                                 //&& (key.charCode != 193) //Á
                                 //&& (key.charCode != 201) //É
                                 //&& (key.charCode != 205) //Í
                                 //&& (key.charCode != 211) //Ó
                                 //&& (key.charCode != 218) //Ú
                            )
                            return false;
                        });

                        $("#btnRegistraPago").click(function(){
                            $("#btnRegistraPago").attr("disabled", "disabled");

                            if( $("#bdp_concepto").val() == "" ){
                                notify("Es necesario el concepto","error");
                                $("#btnRegistraPago").removeAttr("disabled");
                                return false;
                            }
                            if( $("#bdp_referencia").val() == "" ){
                                notify("Es necesario un numero de documento","error");
                                $("#btnRegistraPago").removeAttr("disabled");
                                return false;
                            }
                            if( $("#tdf_id").val() == "" ){
                                notify("Es necesario un tipo de documento","error");
                                $("#btnRegistraPago").removeAttr("disabled");
                                return false;
                            }
                            if( $("#bdp_fechaAutorizado").val() == "" ){
                                notify("Es necesario un tipo de documento","error");
                                $("#btnRegistraPago").removeAttr("disabled");
                                return false;
                            }
                            fec = $("#bdp_fechaAutorizado").val().split("/");
                            if( fec[2] != ' . $anio . ' ){
                                notify("El año debe ser ' . $anio . '","error");
                                $("#btnRegistraPago").removeAttr("disabled");
                                return false;
                            }
                            if( (' . $anio . ') == ' . $year . ' ){
                                if( fec[1] > ' . $mes . ' ){
                                    notify("El mes  debe ser igual o menor al actual","error");
                                    $("#btnRegistraPago").removeAttr("disabled");
                                    return false;
                                }else{
                                    if( fec[1] == ' . $mes . ' && fec[0] > ' . $dia . ' ){
                                        notify("El dia  debe ser igual o inferior al actual (' . $dia . ')","error");
                                        $("#btnRegistraPago").removeAttr("disabled");
                                        return false;
                                    }
                                }
                            }else if ( (' . $anio . '+1) == ' . $year . ' ){
                                if( (new Date("' . $this->ejercicioActual . '-12-31") < new Date(fec[2]+"-"+fec[1]+"-"+fec[0])))
                                {
                                    notify("La fecha maxima de pago es el 31 de DICIEMBRE","error");
                                    $("#btnRegistraPago").removeAttr("disabled");
                                    return false;
                                }
                            }else{
                                notify("Favor de validar la fecha ","error");
                                $("#btnRegistraPago").removeAttr("disabled");
                                return false;
                            }
                            if(fec[2] + "-" + fec[1] + "-" + fec[0] < "' . $datosInputFecha["bdm_fechaAutorizado"] . '"){
                                notify("La fecha no puede ser menor a ' . date('d/m/Y',strtotime($datosInputFecha["bdm_fechaAutorizado"])) . '","error");
                                $("#btnRegistraPago").removeAttr("disabled");
                                return false;
                            }
                            if( $("#pot_id").val() == "" ){
                                notify("Seleccione el tipo de póliza a generar","error");
                                $("#btnRegistraPago").removeAttr("disabled");
                                return false;
                            }
                            if( $("#bdp_poliza").val() == "" ){
                                notify("Seleccione un Valor para la generación de poliza","error");
                                $("#btnRegistraPago").removeAttr("disabled");
                                return false;
                            }

                            comunica("' . $this->pantalla . '",{
                                metodo: "Pagar",
                                origen: "' . $this->prm->origen . '",
                                seleccion: "' . (isset($this->prm->seleccion)
                                ? $this->prm->seleccion : "") . '",
                                mes: ' . $this->prm->mes . ',
                                gur: ' . $this->prm->gur . ',
                                clc_id: "' . $this->prm->clc_id . '",
                                clcFiltro: "' . $this->prm->clcFiltro . '",
                                bdo_id: "' . $this->prm->bdo_id . '",
                                rfc_id: "' . $this->prm->rfc_id . '",
                                bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                bdoFiltro: "' . $this->prm->bdoFiltro . '",
                                data: $("#bds").attr("bds"),
                                afes: $("#afes").attr("afes"),
                                bdp_concepto: $("#bdp_concepto").val(),
                                bdp_referencia: $("#bdp_referencia").val(),
                                tdf_id_pagado: $("#tdf_id").val(),
                                bdp_fechaAutorizado: $("#bdp_fechaAutorizado").val(),
                                pot_id: $("#pot_id").val(),
                                bdp_poliza: $("#bdp_poliza").val(),
                                tmc_id: "A"
                            });
                        });
                    ');
                } else {
                    if ($_SESSION[_S]['nvj_id'] > 2)
                        $html .= '<div class="alert alert-danger">EL REGISTRO DE PAGO LO REALIZA LA UNIDAD RESPONSABLE</div>';
                    else
                        $html .= '<div class="alert alert-danger">NO TIENE PERMISOS PARA GENERAR EL PAGO</div>';
                }
            }
        }

        $html .= '</div>';
        $this->agregaTools($botones);
        $this->mixHtml($html);
        } //CIERRE DEL IF DE $data

        $this->mixScript('
            $("#btnRegresa").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo: "PonBarraBotones",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "") . '",
                    mes: "' . $this->prm->mes . '",
                    gur: "' . $this->prm->gur . '",
                    clc_id: "' . $this->prm->clc_id . '",
                    clcFiltro: "' . $this->prm->clcFiltro . '",
                    bdo_id: "' . $this->prm->bdo_id . '",
                    regresa: "true",
                    rfc_id: "' . $this->prm->rfc_id . '",
                    bdo_referencia: "' . $this->prm->bdo_referencia . '",
                    bdoFiltro: "' . $this->prm->bdoFiltro . '"
                });
            });
        ');
    }

    private function DocumentoPagar($prm = NULL)
    {
        if (!$prm) return FALSE;

        $bdos = "";
        $i = 0;
        $afes = "";
        foreach ($prm as $bd) {
            $i++;
            if ($bdos == "") $bdos .= $bd->bdo_id;
            else $bdos .= "," . $bd->bdo_id;
            if ($afes == "") {

                $afes .= $bd->afe_id;
            } else {
                $pos = strpos($afes, $bd->afe_id);
                if ($pos === false) {
                    $afes .= "," . $bd->afe_id;
                }
            }
        }

        $concepto = "";
        $html = '
            <legend>Registro de pago de los Documentos</legend>
            <table class="table table-bordered"cellpadding="10" style="padding:10px; width:100%; border:1px solid #EAEAEA; box-shadow:1px 2px 3px #999;">
                <tr bgcolor="#EAEAEA">
                    <th colspan="8" style="text-align:center;" class="warning">Datos del Documento</th>
                    <th style="text-align:center;border: 1px solid rgba(45,171,102,0.5);" colspan="5" class="success">Importes Presupuestales/Contables </th>
                </tr>
                <tr bgcolor="#EAEAEA">
                    <th class="warning">UR</th>
                    <th class="warning">clc_id</th>
                    <th class="warning">Afectacion</th>
                    <th class="warning">Referencia</th>
                    <th class="warning">Proveedor</th>
                    <th class="warning">Importe</th>
                    <th class="warning">Retención</th>
                    <th class="warning">Percepción</th>
                    <th style="border: 1px solid rgba(45,171,102,0.5);" class="success">Ministrado</th>
                    <th style="border: 1px solid rgba(45,171,102,0.5);" class="success">Retenciones</th>
                    <th style="border: 1px solid rgba(45,171,102,0.5);" class="success">Percepciones</th>
                    <th style="border: 1px solid rgba(45,171,102,0.5);" class="success">Resultado</th>
                    <th style="border: 1px solid rgba(45,171,102,0.5);" class="success">Detalle</th>
                </tr>';

        $valido = true;

        $pag = $this->DocumentoDatos($bdos, $afes, false);
        foreach ($pag as $data) {
            if ($i > 1) {
                $ag = $i;
                $concepto = $data->clc_concepto;
            } else {
                $ag = $data->bdo_id;
                $concepto = $data->bdo_concepto;
            }

            $imp = round($data->bdo_importe, 2);
            $min = round($data->Percepcion, 2);
            $per = round($data->ncc_percepcion, 2);
            $ret = round($data->ncc_retencion, 2);
            $perD = round($data->bdo_percepcionContable, 2);
            $retD = round($data->bdo_retencionContable, 2);

            $html .= '
                <tr>
                    <td>' . $data->gpo_id . $data->ur_id . '</td>
                    <td>' . $data->clc_id . '</td>
                    <td>' . $data->afe_id . '</td>
                    <td>' . $data->bdo_referencia . '</td>
                    <td>' . $data->rfc_id . '</td>
                    <td style="text-align:right"><span style="float:left">$</span>' 
                        . number_format($data->bdo_importe, 2) . '</td>
                    <td style="text-align:right"><span style="float:left">$</span>' 
                        . number_format($data->bdo_retencionContable, 2) . '</td>
                    <td style="text-align:right"><span style="float:left">$</span>' 
                        . number_format($data->bdo_percepcionContable, 2) . '</td>
                    <td style="text-align:right"><span style="float:left">$</span>' 
                        . number_format($data->Percepcion, 2) . '</td>
                    <td style="text-align:right"><span style="float:left">$</span>' 
                        . number_format($data->ncc_retencion, 2) . '</td>
                    <td style="text-align:right"><span style="float:left">$</span>' 
                        . number_format($data->ncc_percepcion, 2) . '</td>
            ';
            if ($imp == $min && $per == $perD && $retD == $ret)
                $html .= '<td class="success">PAGO COMPLETO</td>';
            else $html .= '<td class="error">PAGO PARCIAL</td>';

            $html .= '<td align="center">';
            $html .= '<a href="#" class="DocumentoDetalle" bdo="' . $data->bdo_id . '"  afes="' . $data->afe_id . '">Ver</a></td>';
            $html .= '</tr>';
        }
        $html .= '
            </table>
            <script type="text/javascript" src="js/jma_viewer.js?1" id="scri_pdf" />
            <script type="text/javascript">
                $(document).keyup(function (e){
                    if ( e.which == 27 )
                        $(".documento_fs").animate({
                            opacity:0,
                            height:"0px",
                            width:"0px"
                        },400,function(){ $(".documento_fs").remove(); });
                });
            </script>
        ';
        $script = '
            $(".DocumentoDetalle").click(function(){
                bdo = $(this).attr("bdo");
                afes = $(this).attr("afes");
                //console.log(afes+"asdasd");
                load_viewer(bdo,"DETALLE DEL USO DE DOCUMENTO","div");
                comunica("' . $this->pantalla . '",{
                    metodo: "DocumentoDetalle",
                    bdo_id: bdo,
                    afe_id: afes
                });
            });
        ';

        $r = new stdClass;
        $r->valido = $valido;
        $r->html = $html;
        $r->concepto = $concepto;
        $r->script = $script;
        $r->clc_id = $pag[0]->clc_id;
        return $r;
    }

    private function Pagar()
    {
        /* VALIDAR LOS DATOS DE LOS DOCUMENTOS QUE VIENEN  */
        $ta = ''; //TIPO AGRUPADOR  A:AGRUPADO: D: DOCUMENTO
        if (isset($this->prm->data)) {
            if (count(array($this->prm->data)) == 0) {
                $this->mixScript(' notify("Error en el proceso de generar el pago","error");'
                    . ' $("#btnRegistraPago").removeAttr("disabled");');
                return FALSE;
            } else $ta = count(array($this->prm->data)) > 1 ? "A" : "D";
        } else {
            $this->mixScript(' notify("Error en el proceso de generar el pago","error");'
                . '$("#btnRegistraPago").removeAttr("disabled");');
            return FALSE;
        }

        $fecha = new stdClass();
        $fecha->gpo_id = $this->gpo_id;
        $fecha->ur_id = $this->ur_id;
        $fecha->fecha = $this->prm->bdp_fechaAutorizado;
        $aniActual = date("Y");
        if ($this->prm->tdf_id_pagado == "SPEI") {
            $hoy = new DateTime("now");
            $fecha->fechaMaxima = $hoy->format('d/m/Y');
            unset($hoy);
            if ($aniActual != $this->ejercicioActual) {
                $fecha->fechaMaxima = "31/12/" . $this->ejercicioActual;
            }
        } else $fecha->fechaMaxima = "31/12/" . $this->ejercicioActual;

        $fecha->fechaMinima = "01/01/" . $this->ejercicioActual;

        $res = validaFecha($fecha);

        if ($res->valido) {
            if (!$res->fechaMaxima) {
                $this->mixScript('notify("' . $res->mensaje . '","error"); '
                    . '$("#btnRegistraPago").removeAttr("disabled");');
                return FALSE;
            }
            if (!$res->fechaMinima) {
                $this->mixScript('notify("' . $res->mensaje . '","error"); '
                    . '$("#btnRegistraPago").removeAttr("disabled");');
                return FALSE;
            }
        } else {
            $this->mixScript('notify("Error 1:' . $res->mensaje . '","error");'
                . '$("#btnRegistraPago").removeAttr("disabled"); ');
            return FALSE;
        }

        $this->prm->fechaMysql = $res->fechaMysql;
        $this->prm->mes = $res->mes;

        if ($res->edoMesContable == 'C') {
            $this->mixScript('notify("El mes esta cerrado","error"); '
                . '$("#btnRegistraPago").removeAttr("disabled");');
            return FALSE;
        }

        $this->CallProcedure();
    }

    private function DocumentoDetalle()
    {
        $this->setContenedor("det_fs" . $this->prm->bdo_id);
        $pagado = $this->DocumentoDatos(
            $this->prm->bdo_id,
            $this->prm->afe_id,
            true
        );
        $trsm = '';
        $trsp = '';
        $paga = new stdClass();
        $paga->pres = 0;
        $paga->perc = 0;
        $paga->retc = 0;
        $mini = new stdClass();
        $mini->pres = 0;
        $mini->perc = 0;
        $mini->retc = 0;

        $top = new stdClass();
        $top->pres = 0;
        $top->perc = 0;
        $top->retc = 0;
        foreach ($pagado as $res) {
            $min = $res->bdm_fechaAutorizado != "" ? 'OK' : '';
            $pag = $res->bdp_fechaAutorizado != "" ? 'OK' : '';
            if ($pag != '') {
                $trsp .= '
                    <tr>
                        <td>' . $res->gpo_id . $res->ur_id . '</td>
                        <td>' . $res->clc_id . '</td>
                        <td>' . $res->afe_id . '</td>
                        <td align="right">' . number_format($res->Percepcion, 2) . '</td>
                        <td align="right">' . number_format($res->ncc_percepcion,2) . '</td>
                        <td align="right">' . number_format($res->ncc_retencion,2) . '</td>
                        <td>' . $min . '</td>
                        <td>' . $res->bdm_fechaAutorizado . '</td>
                        <td>' . $pag . '</td>
                        <td>' . $res->bdp_fechaAutorizado . '</td>
                        </tr>';
                $paga->pres += $res->Percepcion;
                $paga->perc += $res->ncc_percepcion;
                $paga->retc += $res->ncc_retencion;
            } else {
                $trsm .= '
                    <tr>
                        <td>' . $res->gpo_id . $res->ur_id . '</td>
                        <td>' . $res->clc_id . '</td>
                        <td>' . $res->afe_id . '</td>
                        <td align="right">' . number_format($res->Percepcion, 2) . '</td>
                        <td align="right">' . number_format($res->ncc_percepcion,2) . '</td>
                        <td align="right">' . number_format($res->ncc_retencion,2) . '</td>
                        <td>' . $min . '</td>
                        <td>' . $res->bdm_fechaAutorizado . '</td>
                        </tr>';
                $top->pres += $res->Percepcion;
                $top->perc += $res->ncc_percepcion;
                $top->retc += $res->ncc_retencion;
            }
            $mini->pres += $res->Percepcion;
            $mini->perc += $res->ncc_percepcion;
            $mini->retc += $res->ncc_retencion;
        }

        $html = '
            <h4>Informaci&oacute;n del documento</h4>
            <table class="table table-bordered">
                <tr>
                    <td class="warning">ID</td>
                    <td class="warning">RFC</td>
                    <td class="warning">Referencia</td>
                    <td class="warning">Fecha</td>
                    <td class="warning">Concepto</td>
                    <td class="warning">TDF</td>
                    <td class="warning">Percepcion Presupuestal</td>
                    <td class="warning">Percepcion Contable</td>
                    <td class="warning">Retencion Contable</td>
                </tr>
                <tr>
                    <td >' . $pagado[0]->bdo_id . '</td>
                    <td >' . $pagado[0]->rfc_id . '</td>
                    <td >' . $pagado[0]->bdo_referencia . '</td>
                    <td >' . $pagado[0]->bdo_fechaDocumento . '</td>
                    <td >' . $pagado[0]->bdo_concepto . '</td>
                    <td >' . $pagado[0]->tdf_id . '</td>
                    <td align="right">' . number_format($pagado[0]->bdo_importe, 2) . ' </td>
                    <td align="right">' . number_format($pagado[0]->bdo_percepcionContable, 2) . ' </td>
                    <td align="right">' . number_format($pagado[0]->bdo_retencionContable, 2) . ' </td>
                </tr>
                <tr>
                    <td colspan="6" class="">Total Ministrado</td>
                    <td align="right">' . number_format($mini->pres, 2) . ' </td>
                    <td align="right">' . number_format($mini->perc, 2) . ' </td>
                    <td align="right">' . number_format($mini->retc, 2) . ' </td>
                </tr>
                <tr>
                    <td colspan="6" class="">Total Pagado</td>
                    <td align="right">' . number_format($paga->pres, 2) . ' </td>
                    <td align="right">' . number_format($paga->perc, 2) . ' </td>
                    <td align="right">' . number_format($paga->retc, 2) . ' </td>
                </tr>
                <tr>
                    <td colspan="6" class="success">Por Pagar</td>
                    <td align="right" class="success">' . number_format(($mini->pres - $paga->pres), 2) . ' </td>
                    <td align="right" class="success">' . number_format(($mini->perc - $paga->perc), 2) . ' </td>
                    <td align="right" class="success">' . number_format(($mini->retc - $paga->retc), 2) . ' </td>
                </tr>
            </table>
            <h4>Ya pagado</h4>
            <table class="table table-bordered">
                <tr>
                    <td class="warning">UR</td>
                    <td class="warning">CLC</td>
                    <td class="warning">Afectacion</td>
                    <td class="warning">Percepcion Presupuestal</td>
                    <td class="warning">Percepcion Contable</td>
                    <td class="warning">Retencion</td>
                    <td class="warning">Ministrado</td>
                    <td class="warning">Fecha Ministrado</td>
                    <td class="warning">Pagado</td>
                    <td class="warning">Fecha Pagado</td>
                </tr>' 
                    . ($trsp == "" ? '<tr><td colspan="10">Sin registros</td></tr>' : $trsp) . '</table>
                <h4>Este Pago</h4>
                <table class="table table-bordered">
                    <tr>
                        <td class="warning">UR</td>
                        <td class="warning">CLC</td>
                        <td class="warning">Afectacion</td>
                        <td class="warning">Percepcion Presupuestal</td>
                        <td class="warning">Percepcion Contable</td>
                        <td class="warning">Retencion</td>
                        <td class="warning">Ministrado</td>
                        <td class="warning">Fecha Ministrado</td>
                    </tr>.' . $trsm . '
                    <tr>
                        <td colspan="3" class="success">Total a Pagar</td>
                        <td align="right" class="success">' . number_format($top->pres, 2) . ' </td>
                        <td align="right" class="success">' . number_format($top->perc, 2) . ' </td>
                        <td align="right" class="success">' . number_format($top->retc, 2) . ' </td>
                        <td align="right" class="success"></td>
                        <td align="right" class="success"></td>
                    </tr>
                </table>
        ';
        $this->mixHtml($html);
    }

    private function DocumentoDatos($bdos, $afes, $all = false)
    {
        
        $afest = '';
        $cadena = '';
        $cadena = substr($afes,-1);
        if ($cadena == ',') {
            $afest = substr($afes, 0, -1);
        } else {
            $afest = $afes;
        }
        
        $res = array();
        $conexion = new AccesoDatos();
        $sqlParams = [
            ':gpo' => $this->gpo_id,
            ':ur' => $this->ur_id,
        ];
        $groupBy = ($all ? 'gpo_id, ur_id, clc_id, afe_id ' : 'bdo_id,afe_id,clc_id');
        $sql = 
            "SELECT
                gpo_id, ur_id,
                clc_id, afe_id,
                bdo_id, bdo_referencia, rfc_id, bdo_fechaDocumento, b.tdf_id, bdo_concepto,
                bdo_percepcionPresupuestal AS bdo_importe, bdo_retencionContable, bdo_percepcionContable,
                (Percepcion) AS Percepcion, (ncc_retencion) AS ncc_retencion, (ncc_percepcion) AS ncc_percepcion,
                clc_concepto,
                bdp_fechaAutorizado, bdm_fechaAutorizado
            FROM cat_BancoDocumentos b
            INNER JOIN tesoreria.min_BancoDocumentoMinistrado_$this->ejercicioActual t USING( gpo_id, ur_id, bdo_id )
            LEFT JOIN (
                SELECT
                    gpo_id, ur_id, clc_id, afe_id, bdo_id,
                    SUM(ncp_percepcionNeto) AS Percepcion, 0 AS ncc_retencion, 0 AS ncc_percepcion,
                    clc_concepto
                FROM scp_Presupuestal
                    INNER JOIN scp_Clc USING( gpo_id, ur_id, clc_id )
                    LEFT JOIN cg_BancoDocumentoPagado USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                WHERE gpo_id = {$this->gpo_id} AND ur_id = {$this->ur_id}
                    AND bdo_id IN ( $bdos )
                    AND afe_id IN ( $afest )
                    AND clc_id > 0 AND ecl_id > 11
                    AND ( bdp_estado IS NULL OR bdp_estado = 'C' )
                GROUP BY {$groupBy}
                UNION ALL
                SELECT
                    gpo_id, ur_id, clc_id, afe_id, bdo_id,
                    0 AS Percepcion, SUM(ncc_retencion) AS ncc_retencion, SUM(ncc_percepcion) AS ncc_percepcion,
                    clc_concepto
                FROM scp_Contable
                    INNER JOIN scp_Clc USING( gpo_id, ur_id, clc_id )
                    LEFT JOIN cg_BancoDocumentoPagado USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                WHERE gpo_id = :gpo AND ur_id = :ur
                    AND    bdo_id IN ( $bdos )
                        AND    afe_id IN ( $afest )
                    AND clc_id > 0 AND ecl_id > 11
                    AND ( bdp_estado IS NULL OR bdp_estado = 'C' )
                GROUP BY {$groupBy}
            )a USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
            LEFT JOIN(
                SELECT
                    gpo_id, ur_id, clc_id, afe_id,
                    bdp_estado, bdp_fechaAutorizado
                FROM cg_BancoDocumentoPagado
                WHERE bdo_id IN ( $bdos )
                        AND afe_id IN ( $afest )
                    AND bdp_estado = 'A'
            )p USING( gpo_id, ur_id, clc_id, afe_id )
            WHERE gpo_id = :gpo AND ur_id = :ur
                AND    bdo_id IN ( $bdos )
                AND    afe_id IN ( $afest )
                AND bdm_estado = 'A'
                AND ( Percepcion > 0 OR ncc_retencion > 0 OR ncc_percepcion > 0 )
        ";
        $conexion->conectar();
        $conexion->bindParams($sqlParams);
        $conexion->setSql($sql);
        $conexion->consultar();
        $conexion->desconectar();
        while ($datos = $conexion->extraer()) {
            $d = new stdClass();
            foreach ($datos as $name => $valor) {
                $d->$name = $valor;
            }
            array_push($res, $d);
        }
        return $res;
    }
}
//Permisos de acceso al controlador
if (isset($_SESSION[_S]['prm']['C']['5']['6']['1'])) {
    new ControladorRegistroPagado();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
