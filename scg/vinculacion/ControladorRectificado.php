<?php
//librerias
DEFINE("LINEAS_POR_PAGINA", 1000);
session_start();
require_once dirname(__FILE__) . '/../../lib/getDocumento.php';
require_once dirname(__FILE__) . '/../../lib/opcionConteo.php';
require_once dirname(__FILE__) . '/../../lib/pintaClc.php';
require_once dirname(__FILE__) . '/../../lib/pintaRectificacion.php';
require_once dirname(__FILE__) . '/../../lib/scg/vinculacion/vinculacionRectificado.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPoliza.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPolizaAgrupador.php';
require_once dirname(__FILE__) . '/../../lib/scg/pintaPoliza.php';
require_once dirname(__FILE__) . '/../../lib/validaFecha.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/vinculacion/sp_Rectificado.php';

class ControladorRectificado extends WebResponse
{
    private $escritura;
    private $gpo_id;
    private $ur_id;
    private $ue_id;
    private $origen;
    private $ejercicio;
    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        //Permisos de acceso a las tareas del controlador
        $this->escritura = isset($_SESSION[_S]['prm']['C']['5']['8']['2']) ? TRUE : FALSE;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->ue_id = getSession('clasAdm-ue');
        $this->ejercicio = APP_EJERCICIO;
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
            case 'MuestraDatosPagina':
                $this->MuestraDatosPagina();
                break;
            case 'Documento':
                $this->Documento();
                break;
            case 'ObraActividad':
                $this->ObraActividad();
                break;
            case 'ClaveFinanciamiento':
                $this->ClaveFinanciamiento();
                break;
            case 'MesHasta':
                $this->MesHasta();
                break;
            case 'GeneraPoliza':
                $this->GeneraPoliza();
                break;
            case 'PonBarraBotones':
                $this->PonBarraBotones();
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
        $html = $this->getTablaRectificado();
        $this->ponBarraBotones();
        $this->setContenedor('contenedor');
        $this->mixHtml($html);
    }

    private function PonBarraBotones()
    {

        // Botones y opciones que se dibujarán en la barra de botones
        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar';
        $btnLimpiar->value = 'Limpiar';
        $btnLimpiar->type = 'button';
        $btnLimpiar->icon = "refresh me-2";
        $btnLimpiar->color = "cielo";
        $btnLimpiar->class = "me-1";

        $bntExcel = new stdClass();
        $bntExcel->id = 'excelRectificado';
        $bntExcel->name = 'bntExcel';
        $bntExcel->text = 'Excel';
        $bntExcel->value = 'Excel';
        $bntExcel->type = 'button';
        $bntExcel->icon = 'solid fa-file-excel me-2';
        $bntExcel->class = "btn btn-outline-success me-1";

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

        $botones = '
            <div class="d-flex flex-row">
                <fieldset class="col-xs-1 me-1" style="padding-right: 1px !important;">
                    <input id="inputRct_id" placeHolder="RCT" type="text" maxlength=6 class="form-control" 
                    value="' . ($rctFiltro ? $this->prm->rct_id : "") . '"/>
                </fieldset>
                <fieldset class="col-xs-1 me-1" style="padding-right: 1px !important;">
                    <input id="inputClc_id" placeHolder="CLC" type="text" maxlength=6 class="form-control" 
                    value="' . ($clcFiltro ? $this->prm->clc_id : "") . '"/>
                </fieldset>
                <fieldset class="col-xs-2 me-1" style="padding-right: 1px !important;">
                    <input id="inputBdo_id" placeHolder="# Documento" maxlength=7 class="form-control" 
                    value="' . ($bdoFiltro ? $this->prm->bdo_id : "") . '"/>
                </fieldset>
                <fieldset class="col-xs-2 me-1" style="padding-right: 1px !important;">
                    <input id="inputRfc_id" placeHolder="RFC" maxlength=20 class="form-control" 
                    value="' . ($bdoFiltro ? $this->prm->rfc_id : "") . '"/>
                </fieldset>
                <fieldset class="col-xs-2 me-1" style="padding-right: 1px !important;">
                    <input id="inputBdo_referencia" placeHolder="Referencia" class="form-control" 
                    value="' . ($bdoFiltro ? $this->prm->bdo_referencia : "") . '"/>
                </fieldset>
                <fieldset class="col-xs-2 me-1" style="padding-right: 1px !important;">
                    ' . $this->ponBoton($bntExcel) . '
                </fieldset>
            </div>
        ';

        $this->agregaTools($botones);

        // dibujamos los Tabuladores
        $pestana = "";
        $contenedor = "";
        $inicio = "";
        $this->conectar();
        $this->setSql('SELECT msc_id, msc_nombre, msc_alias, ems_id AS estado
            FROM cat_MesContable
                INNER JOIN cg_MesContable USING( msc_id )
            WHERE gpo_id = ? AND ur_id = ? AND msc_id NOT IN ( 0, 13 ) ');
        $this->consultar([$this->gpo_id, $this->ur_id]);
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
        // Dibujamos el contenido
        switch ($this->origen) {
            case 'opcion':
                $this->mixScript('
                    comunica("' . $this->pantalla . '",{
                        metodo: "MuestraDatos",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: $("#page-tabs li").children(".active").attr("mes"),
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
                    comunica("' . $this->pantalla . '",{
                        metodo: "MuestraDatos",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion: "") . '",
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
                comunica("' . $this->pantalla . '", {
                    metodo: "Filtro",
                    origen: "' . $this->origen . '",
                    mes: $("#page-tabs li").children(".active").attr("mes"),
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
                $("#page-tabs li a").each(function (index) { $(this).removeClass("active"); });
                seleccionado.closest("a").addClass("active");

                comunica("' . $this->pantalla . '",{
                    metodo: "MuestraDatos",
                    mes: seleccionado.attr( "mes" ),
                    origen: "' . $this->origen . '",
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

            $("#excelRectificado").click( function(e) {
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
                form.submit();
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

        $html = $this->getTablaRectificado();
        $this->mixHtml($html);

        $this->DibujaPantalla();
    }

    private function MuestraDatosPagina($gpo_id,
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
    $parametroExtra = array(),
    $paginado = false,
    $nInicial = 0,
    $numreg = 0,
    $ejecsql2 = false
) {
    $paginadosql = "";
    if ($paginado == true) {
        $paginadosql = " LIMIT " . $nInicial . ", " . $numreg . " ";
    }
    $ejercicio = APP_EJERCICIO;
    $sqlFiltro = '';
    $sqlParams = [
        ':gpo' => $gpo_id,
        ':ur' => $ur_id,
        ':mmc' => $mmc_id,
    ];
    if ($parametroExtra['gur'] != ''){
        $sqlFiltro = " AND v.gpo_id = :vgpo  AND v.ur_id = :vur ";
        $sqlParams[':vgpo'] = substr($parametroExtra['gur'], 0, 1);
        $sqlParams[':vur'] = substr($parametroExtra['gur'], 1, 2);
    }
    if ($parametroExtra['rct_id'] != ''){
        $sqlFiltro .= " AND v.rct_id = :vrct ";
        $sqlParams[':vrct'] = $parametroExtra['rct_id'];
    }
    if ($parametroExtra['clc_id'] != '') {
        $sqlFiltro .= " AND v.clc_id = :clc ";
        $sqlParams[':clc'] = $parametroExtra['clc_id'];
    }
    if ($parametroExtra['bdo_id'] != ''){
        $sqlFiltro .= " AND v.bdo_id = :vbdo ";
        $sqlParams[':vbdo'] = $parametroExtra['bdo_id'];
    }
    if ($parametroExtra['rfc_id'] != '') {
        $sqlFiltro .= " AND b.rfc_id = :brfc " ;
        $sqlParams[':brfc'] = $parametroExtra['rfc_id'];
    }
    if ($parametroExtra['bdo_referencia'] != '') {
        $sqlFiltro .= " AND b.bdo_referencia IN (" . $parametroExtra['bdo_referencia'] . ") ";
    }
    if ($periodo['mesDesde'] != '') {
        $sqlFiltro .= " AND v.msc_id = :mesdesde ";
        $sqlParams[':mesdesde'] = $periodo['mesDesde'];
    }

    $sql = 
    "SELECT SQL_CALC_FOUND_ROWS
        v.gpo_id, v.ur_id, MAX(v.clc_id) AS clc_id, v.afe_id,
        v.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia,
        SUM( v.bdr_percepcionPresupuestal ) AS bdr_percepcionPresupuestal, 
        SUM( v.bdr_percepcionContable ) AS bdr_percepcionContable, 
        SUM( v.bdr_retencionContable ) AS bdr_retencionContable,
        v.msc_id, concat(gpo_id, ur_id) as gur,
        m.rct_id, m.rct_justificacion,
        MAX( v.bdr_estado) AS estado, DATE( MAX(v.bdr_fechaAutorizado) ) AS fechaAutorizacion, 
        DATE( MAX(v.bdr_fechaCancelado) ) AS fechaCancelacion,
        MAX(v.bdr_agrupador) AS agrupador,
        pa.polizaAutorizado, MAX(pa.fechaAutorizado) AS fechaAutorizado,
        MAX(pa.gpo_id_polizaAutorizado) AS gpo_id_polizaAutorizado, MAX(pa.ur_id_polizaAutorizado) AS ur_id_polizaAutorizado, 
        MAX(pa.ue_id_polizaAutorizado) AS ue_id_polizaAutorizado,
        MAX(pa.msc_id_polizaAutorizado) AS msc_id_polizaAutorizado, MAX(pa.pot_id_polizaAutorizado) AS pot_id_polizaAutorizado,
        MAX(pa.prf_id_polizaAutorizado) AS prf_id_polizaAutorizado,
        MAX(pa.pma_id_polizaAutorizado) AS pma_id_polizaAutorizado,
        CONCAT( v.gpo_id, v.ur_id, '.', LPAD( MAX(v.msc_id), 2, 000 ), '.', LPAD( v.afe_id, 6, 0000000 ), '.', LPAD( v.bdo_id, 6, 000000 ), '.', LPAD( m.rct_id, 6, 000000 ), '.', COALESCE( MAX(pa.pma_id_polizaAutorizado), 000000 ) ) AS id,
        (bdr_percepcionPresupuestal + bdr_percepcionContable - bdr_retencionContable) AS bdr_importe
    FROM cat_BancoDocumentos b
        INNER JOIN scp_BancoDocumentoRectificado v USING( gpo_id, ur_id, bdo_id )
        INNER JOIN scp_MaestroRectificacion m USING( gpo_id, ur_id, rct_id )
        INNER JOIN cg_MesContable ms USING( gpo_id, ur_id, msc_id )
        LEFT JOIN(
            SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado, MAX(m.ue_id) AS ue_id_polizaAutorizado,
                MAX(m.msc_id) AS msc_id_polizaAutorizado, MAX(m.pot_id) AS pot_id_polizaAutorizado, MAX(m.prf_id) AS prf_id_polizaAutorizado,
                m.pma_id AS pma_id_polizaAutorizado, MAX(m.pma_fecha) AS fechaAutorizado,
                CONCAT( MAX(m.pot_id), '-', MAX(m.prf_id), m.pma_id ,' ',MAX(m.apg_id)) AS polizaAutorizado,
                MAX(x.pdx_gpo_id_vinculacion) AS gpo_id, MAX(x.pdx_ur_id_vinculacion) AS ur_id,
                x.pdx_afe_id_origen AS afe_id, x.pdx_bdo_id_origen AS bdo_id,
                x.pdx_rct_id_vinculacion AS rct_id,
                x.pdx_agrupador AS agrupador
            FROM cg_PolizaMaestro m
                INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
            WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                AND m.mmc_id = :mmc
                AND m.tmc_id = 'A'
                AND x.pdx_ejercicio = {$ejercicio}
            GROUP BY  m.gpo_id, m.ur_id, x.pdx_afe_id_origen, x.pdx_bdo_id_origen, x.pdx_rct_id_vinculacion,x.pdx_agrupador, m.pma_id
        )pa USING( gpo_id, ur_id, afe_id, bdo_id, rct_id )
    WHERE b.bdo_id IS NOT NULL $sqlFiltro
        GROUP BY gpo_id, ur_id, afe_id, bdo_id, rct_id, pa.polizaAutorizado";

        $this->setSql($sql);
        $this->setParams($sqlParams);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = '2000';
        $nav->idTabla = 'tbl-rectificado';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No se encontraron polizas con los criterios seleccionados","warning");');
        } else {

            $this->mixScript('
                $("#tbl-rectificado tr").on("dblclick", function (ev){
                    ev.stopPropagation();
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
                    const prm = $("#tbl-rectificado").tabla("getFilaSeleccion");
                    comunica("' . $this->pantalla . '", {
                        metodo: "Documento",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion
                        : "") . '",
                        mes: $("#page-tabs li").children(".active").attr("mes"),
                        rct_id: prm.rct_id,
                        rctFiltro: var_rctFiltro,
                        clc_id: $("#inputClc_id").val(),
                        clcFiltro: var_clcFiltro,
                        gur: prm.gur,
                        afe_id: prm.afe_id,
                        bdo_id: prm.bdo_id,
                        tdf_id: prm.tdf_id,
                        rfc_id: prm.rfc_id,
                        bdo_referencia: prm.bdo_referencia,
                        bdoFiltro: var_bdoFiltro,
                        agrupador: prm.agrupador
                    });

                });
            ');

            $this->mixScript('
            $(".panel-heading.header").remove();
            $(".panel-footer").remove();
            $(".tabla-footer").remove();'
        );
        }
}

    private function DameInformacionPagina()
    {

        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $rct_id = $this->getPrm('rct_id','');
        $clc_id = $this->getPrm('clc_id','');
        $bdo_id = $this->getPrm('bdo_id','');
        $rfc_id = $this->getPrm('rfc_id','');
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
        $datos = vinculacionRectificado(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'R',
            $niveles,
            array(
                "gur" => $gur, "rct_id" => $rct_id, "clc_id" => $clc_id, "bdo_id" => $bdo_id,
                "rfc_id" => $rfc_id, "bdo_referencia" => $bdo_referencia
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

    private function DibujaPantalla()
    {
        $this->mixScript('
            $("#page-tabs a").click( function(e){
                e.preventDefault();
                var seleccionado = $(this);
                $("#page-tabs li a").each(function (index) { $(this).removeClass("active"); });
                seleccionado.closest("a").addClass("active");

                comunica("' . $this->pantalla . '",{
                    metodo: "MuestraDatos",
                    mes: seleccionado.attr( "mes" ),
                    origen: "' . $this->origen . '",
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


        if ($this->origen == 'clc') {
            $this->mixScript('
                $("#page-tabs").hide();
                $("#inputRct_id").hide();
                $("#inputBdo_id").hide();
                $("#inputRfc_id").hide();
                $("#inputBdo_referencia").hide();
            ');
        }
    }

    private function MuestraDatos()
    {
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $rct_id = $this->getPrm('rct_id', '');
        $clc_id = $this->getPrm('clc_id', '');
        $bdo_id = $this->getPrm('bdo_id', '');
        $rfc_id = $this->getPrm('rfc_id', '');
        $bdo_referencia = '';
        if (isset($this->prm->bdo_referencia) && !empty($this->prm->bdo_referencia)) {
            $referencias = explode(",", str_replace(' ', '', $this->prm->bdo_referencia));
            foreach ($referencias as $bdo) {
                $bdo_referencia .= '"' . $bdo . '",';
            }
            $bdo_referencia = substr($bdo_referencia, 0, -1);
        }
        $periodo['mesDesde'] = (isset($this->prm->mes) ? $this->prm->mes : '');
        $periodo['mesHasta'] = (isset($this->prm->mes) ? $this->prm->mes : '');

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
            'R',
            $niveles,
            array(
                "gur" => $gur, "rct_id" => $rct_id, "clc_id" => $clc_id, "bdo_id" => $bdo_id,
                "rfc_id" => $rfc_id, "bdo_referencia" => $bdo_referencia
            ),
            $paginado,
            $registroInicial,
            LINEAS_POR_PAGINA
        );

        return false;
    }

    private function DameInformacion()
    {

        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $rct_id = $this->getPrm('rct_id', '');
        $clc_id = $this->getPrm('clc_id', '');
        $bdo_id = $this->getPrm('bdo_id', '');
        $rfc_id = $this->getPrm('rfc_id', '');
        $bdo_referencia = '';
        if (isset($this->prm->bdo_referencia) && !empty($this->prm->bdo_referencia)) {
            $referencias = explode(",", str_replace(' ', '', $this->prm->bdo_referencia));
            foreach ($referencias as $bdo) {
                $bdo_referencia .= '"' . $bdo . '",';
            }
            $bdo_referencia = substr($bdo_referencia, 0, -1);
        }
        $periodo['mesDesde'] = (isset($this->prm->mes) ? $this->prm->mes : '');
        $periodo['mesHasta'] = (isset($this->prm->mes) ? $this->prm->mes : '');

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
        $datos = vinculacionRectificado(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'R',
            $niveles,
            array(
                "gur" => $gur, "rct_id" => $rct_id, "clc_id" => $clc_id, "bdo_id" => $bdo_id,
                "rfc_id" => $rfc_id, "bdo_referencia" => $bdo_referencia
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
                $("#RectificadoPaginador").attr("last", 1 );
                var options = {
                    numberOfPages: 5,
                    currentPage: 1,
                    totalPages: ' . $nTotalPages . ',
                    containerClass: "pull-right",
                    listContainerClass: "nav nav-tabs",
                    itemContainerClass: function ( type, page, current ) { return (page === current) ? "active" : "pointer-cursor"; },
                    onPageClicked: function(e,originalEvent,type,page){
                        // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                        if ( $("#RectificadoPaginador").attr("last") != page ) {
                            comunica("' . $this->pantalla . '",{
                                metodo: "MuestraDatosPagina",
                                mes: $("#page-tabs li").children(".active").attr("mes"),
                                origen: "' . $this->origen . '",
                                gur: $("#inputGur").val(),
                                clc_id: $("#inputClc_id").val(),
                                bdo_id: $("#inputBdo_id").val(),
                                rfc_id: $("#inputRfc_id").val(),
                                bdo_referencia: $("#inputBdo_referencia").val(),
                                paginado: true,
                                numpagina: page ,
                                numreg: ' . LINEAS_POR_PAGINA . '
                            });
                            $("#RectificadoPaginador").attr("last", page );
                        }
                    }
                }
                console.log(options);
                $("#RectificadoPaginador").bootstrapPaginator(options);
            ');
        } else {
            $this->mixScript('$("#RectificadoPaginador").html("");');
        }
        return json_encode($datos);
    }

    public function getTablaRectificado(): string
    {

        $tabla = new stdClass();
        $tabla->esCompacta = true;
        $tabla->cabeceraFija = true;
        $tabla->inputFiltro = "buscar";
        $tabla->id = 'tbl-rectificado';
        $tabla->seleccionable = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaRectificado';

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
        array_push($tr, $th);
        
        $th = new stdClass();
        $th->texto = 'RCT';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->formato = 'moneda';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO';
        array_push($tr, $th);
        
        $th = new stdClass();
        $th->texto = 'AUTORIZACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'POL AUT.';
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
        $td->campoTxt->campos = ['rct_id']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['rct_justificacion']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdr_importe']; 
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
        $td->campoTxt->campos = ['polizaAutorizado']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaAutorizado']; 
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        return $this->wrPonTabla($tabla);

    }

    private function Documento()
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = ? AND ur_id = ? ");
        $conexion->consultar([$this->gpo_id, $this->ur_id]);

        while ($dataRow = $conexion->extraer()) {
            $mesContable[$dataRow['msc_id']] = $dataRow;
        }

        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
        $clc_id_documento = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id_documento = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');
        $rct_id = (isset($this->prm->rct_id) ? $this->prm->rct_id : '');

        $mensaje = '';
        $generaPolizaAutorizado = FALSE;

        $clc_array = array();

        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->icon = 'solid fa-circle-left me-2';
        $btnRegresa->color = 'gris';
        $btnRegresa->class = 'me-1';

        $botones = $this->ponBoton($btnRegresa);
        $this->agregaTools($botones);

        $documento = getDocumento(
            $gpo_id_documento,
            $ur_id_documento,
            $bdo_id_documento,
            $this->prm->rfc_id,
            $this->prm->bdo_referencia,
            array("ejercicio" => $this->ejercicio, "tdf_id" => $this->prm->tdf_id, "mmc_id" => "R")
        );
        $html = '<legend style="border-bottom: 1px solid #e5e5e5; margin-bottom: 22px;">Análisis del documento a generar la póliza del rectificado</legend>';
        $html .= '
            <div class="px-3">
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
                            <td width="10%" align="right"><span style="float:left">$</span>' 
                            . number_format($documento->percepcionPresupuestal,2) . '</td>
                            <td width="10%" align="right"><span style="float:left">$</span>'
                            . number_format($documento->percepcionContable, 2) . '</td>
                            <td width="10%" align="right"><span style="float:left">$</span>'
                            . number_format($documento->retencionContable, 2) . '</td>
                            <td>' . $documento->fechaDocumento . '</td>
                        </tr>
                    </table>
                </fieldset><br/>
            ';

        $sqlParams = [
            ':dgpo' => $gpo_id_documento,
            ':dur' => $ur_id_documento,
            ':dbdo' => $bdo_id_documento,
        ];
        $sql = "
            SELECT gpo_id, ur_id, clc_id
            FROM(
                SELECT gpo_id, ur_id, clc_id
                FROM scp_Presupuestal
                    INNER JOIN scp_Clc USING( gpo_id, ur_id, clc_id )
                WHERE gpo_id = :dgpo AND ur_id = :dur
                    AND bdo_id = :dbdo
                    AND ecl_id NOT IN ( 1, 2, 14, 15, 17 )
            UNION ALL
                SELECT gpo_id, ur_id, clc_id
                FROM scp_Contable
                    INNER JOIN scp_Clc USING( gpo_id, ur_id, clc_id )
                WHERE gpo_id = :dgpo AND ur_id = :dur
                    AND bdo_id = :dbdo
                    AND ecl_id NOT IN ( 1, 2, 14, 15, 17 )
            )m
        ";
        if ($clc_id_documento != '') {
            $sql .= " AND m.clc_id = :mclc ";
            $sqlParams[':mclc'] = $clc_id_documento;
        }
        $sql .= " GROUP BY gpo_id, ur_id, clc_id ";

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->bindParams($sqlParams);
        $conexion->setSql($sql);
        $conexion->consultar();

        if ($conexion->getNumeroRegistros() == 0) $html .= $mensaje;
        else {
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
        foreach ($clc_array as $clc_id) {
            $clcDocumento = pintaClc(
                $gpo_id_documento,
                $ur_id_documento,
                $clc_id,
                array("ejercicio" => $this->ejercicio, "bdo_id" => $bdo_id_documento)
            );
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
        $rectificacionDocumento = pintaRectificacion(
            $gpo_id_documento,
            $ur_id_documento,
            $rct_id,
            array("ejercicio" => $this->ejercicio)
        );
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

            $estadoMesAutorizado = $mesContable[str_pad(
                $value->mesAutorizado,
                2,
                '0',
                STR_PAD_LEFT
            )]['ems_id'];
        }
        $polizas = getPolizaAgrupador(
            $this->gpo_id,
            $this->ur_id,
            $this->prm->agrupador,
            'R'
        );

        $polizaAut = new stdClass;
        $botonGeneraPa = '';

        //preguntamos los datos de la póliza contable de autorización
        if ($polizas->valido) {
            foreach ($polizas->polizas as $key => $value) {
                //echo "<pre>"; print_r($value); echo "</pre>";//die();
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
                                    alertify.confirm(
                                        "RE HACER LA POLIZA ' . (isset($value->poliza) ? $value->poliza : '') . '",
                                        "Esta acción re construira la póliza de autorización como fue generada por el sistema de manera automatica, desea proceder?",
                                        function(){
                                            comunica("' . $this->pantalla . '",{
                                                metodo: "GeneraPoliza",
                                                origen: "' . $this->origen . '",
                                                seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                                mes: ' . $this->prm->mes . ',
                                                gur: ' . $this->prm->gur . ',
                                                rct_id: ' . $this->prm->rct_id . ',
                                                rctFiltro: ' . $this->prm->rctFiltro . ',
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
                                        undefined
                                    ).set("labels", {
                                        ok: "Proceder", cancel: "Cancelar"
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
                                seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                mes: ' . $this->prm->mes . ',
                                gur: ' . $this->prm->gur . ',
                                rct_id: ' . $this->prm->rct_id . ',
                                rctFiltro: ' . $this->prm->rctFiltro . ',
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
                                <legend>Mes del Rectificado esta cerrado, capture una nueva fecha</legend>
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
                                rct_id: ' . $this->prm->rct_id . ',
                                rctFiltro: ' . $this->prm->rctFiltro . ',
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

        $this->agregaTools($botones);
        $this->mixHtml($html);

        $this->setContenedor('botonA');
        $this->mixHtml($botonGeneraPa);

        $this->mixScript('
            $("#btnRegresa").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo: "PonBarraBotones",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                    mes: ' . $this->prm->mes . ',
                    gur: ' . $this->prm->gur . ',
                    rct_id: ' . $this->prm->rct_id . ',
                    rctFiltro: ' . $this->prm->rctFiltro . ',
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
            $fechaValidar->anio = $this->ejercicio;
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

        $Rectificado = new sp_Rectificado();
        $Rectificado->setGpo_id($gpo_id_documento);
        $Rectificado->setUr_id($ur_id_documento);
        $Rectificado->setBdr_Agrupador($this->prm->agrupador);
        $Rectificado->setPma_fecha($fecha);
        //Este no sabia bien que se debia poner 11/abril
        $Rectificado->setTmc_id($this->prm->tmc_id);

        $Rectificado->setSis_id('SCG');
        $Rectificado->setBit_observacion('PÓLIZA GENERADA DESDE LA PANTALLA C_5_8');
        $Rectificado->setEjercicio($this->ejercicio);

        $Rectificado->execute();
        if (!$Rectificado->getBandera()) {
            $tipo = "success";
            $mensaje = $Rectificado->getMensaje();
        } else {
            $mensaje = "RECTIFICADO CORRECTAMENTE" . $Rectificado->getMensaje();
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
                    rct_id: ' . $this->prm->rct_id . ',
                    rctFiltro: ' . $this->prm->rctFiltro . ',
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
                    rct_id: ' . $this->prm->rct_id . ',
                    rctFiltro: ' . $this->prm->rctFiltro . ',
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
if (isset($_SESSION[_S]['prm']['C']['5']['8']['1'])) {
    new ControladorRectificado();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
