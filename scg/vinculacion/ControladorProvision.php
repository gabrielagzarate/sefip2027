<?php
DEFINE("LINEAS_POR_PAGINA", 1000);
//librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/getDocumento.php';
require_once dirname(__FILE__) . '/../../lib/opcionConteo.php';
require_once dirname(__FILE__) . '/../../lib/pintaClc.php';
require_once dirname(__FILE__) . '/../../lib/scg/vinculacion/vinculacionProvision.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPoliza.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPolizaAgrupador.php';
require_once dirname(__FILE__) . '/../../lib/scg/pintaPoliza.php';
require_once dirname(__FILE__) . '/../../lib/validaFecha.php';
require_once dirname(__FILE__) . '/../../lib/estadoMesContable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/vinculacion/sp_ProvisionAutorizado.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/vinculacion/sp_ProvisionCancelado.php';

class ControladorProvision extends WebResponse
{
    private $escritura;
    private $gpo_id;
    private $ur_id;
    private $nivel;
    private $ue_id;
    private $usr_ip;
    private $usr_id;
    private $origen;
    private $ejercicio;
    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        //Permisos de acceso a las tareas del controlador
        $this->escritura = isset($_SESSION[_S]['prm']['C']['5']['4']['2']) ? true : false;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->nivel = $_SESSION[_S]['nvj_id'];
        $this->ue_id = getSession('clasAdm-ue', true);
        $this->ejercicio = APP_EJERCICIO;
        $this->usr_ip = $this->getIp();
        $this->usr_id = getSession('usr_id');
        if ($this->nivel < 3) {
            $this->mixScript('$("#toggleCA").hide();');
        }
        $this->origen = isset($this->prm->origen) ? $this->prm->origen : 'opcion';
        if ($this->origen == 'opcion') {
            $this->prm->mes = isset($this->prm->mes) ? $this->prm->mes : (date("m") - 1);
            $aniActual = date("Y");
            if ($aniActual != APP_EJERCICIO) {
                $this->prm->mes = 12;
            }
        }
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
            case 'GeneraPoliza':
                $this->GeneraPoliza();
                break;
            case 'PonBarraBotones':
                $this->PonBarraBotones();
                break;
            case 'ProvisionarDetalle':
                $this->ProvisionarDetalle();
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
        $html = $this->getTablaProvision();
        $this->ponBarraBotones();
        $this->setContenedor('contenedor');
        $this->mixHtml($html);
    }

    private function PonBarraBotones()
    {
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

        $btnProvision = new stdClass();
        $btnProvision->id = 'btnProvision';
        $btnProvision->name = 'btnProvision';
        $btnProvision->text = 'Provisionar';
        $btnProvision->value = 'Provisionar';
        $btnProvision->type = 'button';
        $btnProvision->icon = 'tasks me-2';
        $btnProvision->class = "btn btn-outline-primary me-1";

        $btnSeleccionar = new stdClass();
        $btnSeleccionar->id = 'btn-seleccion';
        $btnSeleccionar->name = 'btnSeleccionar';
        $btnSeleccionar->text = 'Seleccionar todos';
        $btnSeleccionar->value = 'Seleccionar todos';
        $btnSeleccionar->type = 'button';
        $btnSeleccionar->class = "btn btn-warning";

        $bntExcel = new stdClass();
        $bntExcel->id = 'excelProvision';
        $bntExcel->name = 'bntExcel';
        $bntExcel->text = 'Excel';
        $bntExcel->value = 'Excel';
        $bntExcel->type = 'button';
        $bntExcel->icon = 'solid fa-file-excel me-2';
        $bntExcel->class = "btn btn-outline-success me-1";

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
            <div class="d-flex flex-row">
                <fieldset class="col-xs-2 me-1" style="padding-right: 1px !important;">
                ' . $this->ponBoton($btnProvision) . '
                </fieldset>
                <fieldset class="col-xs-1 me-1" style="padding-right: 1px !important;">
                    <input id="inputClc_id" placeHolder="CLC" type="text" maxlength=6 class="form-control"
                    value="' . ($clcFiltro ? $this->prm->clc_id : "") . '"/>
                </fieldset>
                <fieldset class="col-xs-1 me-1" style="padding-right: 1px !important;">
                    <input id="inputBdo_id" placeHolder="# Documento" maxlength=7 class="form-control"
                        value="' . ($bdoFiltro ? $this->prm->bdo_id : "") . '"/>
                </fieldset>
                <fieldset class="col-xs-2 me-1" style="padding-right: 1px !important;">
                    <input id="inputRfc_id" placeHolder="RFC" maxlength=20 class="form-control"
                        value="' . ($bdoFiltro ? $this->prm->rfc_id : "") . '"/>
                </fieldset>
                <fieldset class="col-xs-2 me-1" style="padding-right: 1px !important;">
                    <input id="inputBdo_referencia" placeHolder="Referencia" maxlength=20 class="form-control"
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
        $aniActual = date("Y");
        $sql = "SELECT msc_id, msc_nombre, msc_alias, ems_id AS estado
            FROM cat_MesContable
            INNER JOIN cg_MesContable USING( msc_id )
            WHERE gpo_id = ? AND ur_id = ? ";

        $sql .= $aniActual != APP_EJERCICIO ? " AND msc_id NOT IN ( 0, 13 ) "
        : " AND msc_id < MONTH(NOW()) AND msc_id NOT IN ( 0, 13 )";

        $this->setSql($sql);
        $this->consultar([$this->gpo_id, $this->ur_id]);
        $this->desconectar();

        while ($row = $this->extraer()) {
            if ($this->prm->mes == $row['msc_id']) {
                $inicio = 'active';
            } else {
                $inicio = '';
            }

            $pestana = str_replace('ultimo', '', $pestana);
            $pestana = str_replace('lastdiv', '', $pestana);

            $pestana .= '
                <li opcion="' . $row['msc_id'] . '" ' . $inicio . ' ultimo>
                    <a class="nav-link ' . $inicio . ' meses" mes="' . $row['msc_id'] . '" estado="' . $row['estado'] . '"> ' . $row['msc_alias'] . ' </a> lastdiv
                </li>
            ';

            $contenedor .= '<div id="contenedor' . $row['msc_id'] . '" style="display:none" class="contenedores"></div>';
        }

        $pestana = str_replace('ultimo', 'class="last"', $pestana);
        $pestana = str_replace('lastdiv', '<div class="last"></div>', $pestana);

        $this->mixHtml('
                <div id="page-tabs"><ul class="nav nav-tabs">
                ' . $pestana . '
                </ul></div><br>
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
                        $("#btnRegresa").attr("origen", "")
                        $(".navbar").hide();
                        $("#vclcPantallaCompleta").show();
                        e.stopPropagation();
                    });

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
                    mes: $("#page-tabs li").children(".active").attr("mes"),
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
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

            $("#excelProvision").click( function(e) {
                var form = $("<form>")
                    .attr("action", "rep/excel/scg/vinculacion/exp_Provision.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "5" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "4" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "N" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "seleccion")
                    .attr("value", "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs li").children(".active").attr("mes") ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_id").attr("value", $("#inputBdo_id").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputRfc_id").attr("value", $("#inputRfc_id").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_referencia").attr("value", $("#inputBdo_referencia").val() ))
                $("body").append(form);
                form.submit();
            });

            $("#imprimirProvision").click( function(e) {
                var form = $("<form>")
                    .attr("action", "rep/doc/scg/reporte.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "5" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "4" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "N" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "seleccion")
                    .attr("value", "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '" ))
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

        $html = $this->getTablaProvision();
        $this->mixHtml($html);

        $this->DibujaPantalla();
    }

    private function DibujaPantalla()
    {
        $this->mixScript('
            $("#page-tabs a").click( function(e){
                e.preventDefault();
                var seleccionado = $(this);
                $("#page-tabs li a").each(function (index) { $(this).removeClass("active");});

                seleccionado.closest("a").addClass("active");
                $("#page-tabs a").attr("disabled", true);console.log($("#page-tabs li").children(".active").attr("mes"));
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
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
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

        $detalle = false;
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
            'N',
            $niveles,
            array(
                "gur" => $gur,
                "clc_id" => $clc_id,
                "bdo_id" => $bdo_id,
                "rfc_id" => $rfc_id,
                "bdo_referencia" => $bdo_referencia,
            ),
            $paginado,
            $registroInicial,
            LINEAS_POR_PAGINA,
            $PrimeraCon
        );
        return false;
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

        $sqlFiltroGur = "";
        $sqlFiltroClc = "";
        $sqlFiltroBdo = "";
        $sqlFiltroRfc = "";
        $sqlFiltroRef = "";
        $sqlFiltroMes = "";

        $sqlParams = [
            ':gpo' => $gpo_id,
            ':ur' => $ur_id,
            ':mmc' => $mmc_id,
        ];
        $ejercicio = APP_EJERCICIO;
        if ($parametroExtra['gur'] != '') {
            $sqlFiltroGur = " WHERE b.gpo_id = :bgpo AND b.ur_id = :bur ";
            $sqlParams[':bgpo'] = substr($parametroExtra['gur'], 0, 1);
            $sqlParams[':bur'] = substr($parametroExtra['gur'], 1, 2);
        }
        if ($parametroExtra['clc_id'] != '') {
            $sqlFiltroClc = " AND clc_id = :clc ";
            $sqlParams[':clc'] = $parametroExtra['clc_id'];
        }
        if ($parametroExtra['bdo_id'] != '') {
            $sqlFiltroBdo = " AND bdo_id = :bdo ";
            $sqlParams[':bdo'] = $parametroExtra['bdo_id'];
        }
        if ($parametroExtra['rfc_id'] != '') {
            $sqlFiltroRfc = " AND rfc_id = :rfc ";
            $sqlParams[':rfc'] = $parametroExtra['rfc_id'];
        }
        if ($parametroExtra['bdo_referencia'] != '') {
            $sqlFiltroRef = " AND bdo_referencia IN (" . $parametroExtra['bdo_referencia'] . ") ";
        }

        if ($periodo['mesDesde'] != '') {
            $sqlFiltroMes = " :mesdesde ";
            $sqlParams[':mesdesde'] = $periodo['mesDesde'];
        }

        $paginadosql = "";
        if ($paginado == true) {
            $paginadosql = " LIMIT " . $nInicial . ", " . $numreg . " ";
        }

        $sql =
            "SELECT SQL_CALC_FOUND_ROWS gpo_id, ur_id, clc_id, afe_id, bdo_id,
                tdf_id, rfc_id, bdo_referencia, bdo_concepto,
                bdo_percepcionPresupuestal, bdo_percepcionContable,
                bdo_retencionContable, msc_id, estado, fechaAutorizacion,
                fechaCancelacion, agrupador, polizaAutorizado, fechaAutorizado,
                gpo_id_polizaAutorizado, ur_id_polizaAutorizado, ue_id_polizaAutorizado,
                msc_id_polizaAutorizado, pot_id_polizaAutorizado, prf_id_polizaAutorizado,
                pma_id_polizaAutorizado, polizaCancelado, fechaCancelado, gpo_id_polizaCancelado,
                ur_id_polizaCancelado, ue_id_polizaCancelado, msc_id_polizaCancelado,
                pot_id_polizaCancelado, prf_id_polizaCancelado, pma_id_polizaCancelado,
                id 
            FROM 
            (
                SELECT
                    c.gpo_id, c.ur_id, c.clc_id, c.afe_id,
                    c.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia, b.bdo_concepto,
                    b.bdo_percepcionPresupuestal, b.bdo_percepcionContable, b.bdo_retencionContable,
                    MIN(v.msc_id) AS msc_id,
                    MIN(v.bdn_estado) AS estado, DATE( MIN(v.bdn_fechaAutorizado) ) AS fechaAutorizacion, DATE( MIN(v.bdn_fechaCancelado) ) AS fechaCancelacion,
                    MIN(v.bdn_agrupador) AS agrupador,
                    MIN(pa.polizaAutorizado) AS polizaAutorizado, MIN(pa.fechaAutorizado) AS fechaAutorizado,
                    pa.gpo_id_polizaAutorizado, pa.ur_id_polizaAutorizado, MIN(pa.ue_id_polizaAutorizado) AS ue_id_polizaAutorizado,
                    MIN(pa.msc_id_polizaAutorizado) AS msc_id_polizaAutorizado, MIN(pa.pot_id_polizaAutorizado) AS pot_id_polizaAutorizado,
                    MIN(pa.prf_id_polizaAutorizado) AS prf_id_polizaAutorizado,
                    pa.pma_id_polizaAutorizado,
                    MIN(pc.polizaCancelado) AS polizaCancelado, MIN(pc.fechaCancelado) AS fechaCancelado,
                    MIN(pc.gpo_id_polizaCancelado) AS gpo_id_polizaCancelado, 
                    MIN(pc.ur_id_polizaCancelado) AS ur_id_polizaCancelado, 
                    MIN(pc.ue_id_polizaCancelado) AS ue_id_polizaCancelado,
                    MIN(pc.msc_id_polizaCancelado) AS msc_id_polizaCancelado,
                    MIN(pc.pot_id_polizaCancelado) AS pot_id_polizaCancelado,
                    MIN(pc.prf_id_polizaCancelado) AS prf_id_polizaCancelado,
                    MIN(pc.pma_id_polizaCancelado) AS pma_id_polizaCancelado,
                    CONCAT( c.gpo_id, c.ur_id, '.', LPAD( c.msc_id_ejercido, 2, 000 ), '.', LPAD( c.afe_id, 6, 0000000 ), '.', LPAD( c.bdo_id, 6, 000000 ), '.', COALESCE( NULL, 000000 ) ) AS id
                FROM cat_BancoDocumentos b
                    INNER JOIN (
                        SELECT n.gpo_id, n.ur_id, MIN(n.clc_id) AS clc_id, n.afe_id, n.bdo_id,
                            MONTH(MIN(c.clc_fechaEnvio)) AS msc_id_ejercido
                        FROM scp_Presupuestal n
                            INNER JOIN scp_Clc c USING( gpo_id, ur_id, clc_id )
                        WHERE gpo_id = :gpo AND ur_id = :ur
                            {$sqlFiltroClc}
                            {$sqlFiltroBdo}
                            AND MONTH(c.clc_fechaEnvio) = {$sqlFiltroMes}
                        GROUP BY gpo_id, ur_id, afe_id, bdo_id
                    )c USING( gpo_id, ur_id, bdo_id )
                    LEFT JOIN (
                        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id,
                            msc_id AS msc_id_ministrado
                        FROM tesoreria.min_BancoDocumentoMinistrado_{$ejercicio}
                        WHERE gpo_id = :gpo AND ur_id = :ur
                            {$sqlFiltroClc}
                            {$sqlFiltroBdo}
                            AND msc_id = {$sqlFiltroMes}
                            AND bdm_estado = 'A'
                    )t USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                    LEFT JOIN cg_BancoDocumentoProvisionTransferido v USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                    LEFT JOIN(
                        SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado, MIN(m.ue_id) AS ue_id_polizaAutorizado,
                            MIN(m.msc_id) AS msc_id_polizaAutorizado, MIN(m.pot_id) AS pot_id_polizaAutorizado, MIN(m.prf_id) AS prf_id_polizaAutorizado,
                            m.pma_id AS pma_id_polizaAutorizado, MIN(m.pma_fecha) AS fechaAutorizado,
                            CONCAT( MIN(m.pot_id), '-', MIN(m.prf_id), m.pma_id ,' ',MIN(m.apg_id) ) AS polizaAutorizado,
                            MIN(x.pdx_gpo_id_vinculacion) AS gpo_id, MIN(x.pdx_ur_id_vinculacion) AS ur_id,
                            MIN(x.pdx_afe_id_vinculacion) AS afe_id, MIN(x.pdx_bdo_id_vinculacion) AS bdo_id,
                            MIN(x.pdx_agrupador) AS agrupador
                        FROM cg_PolizaMaestro m
                            INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                        WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                            AND m.mmc_id = :mmc
                            AND m.tmc_id = 'A'
                            AND x.pdx_ejercicio = {$ejercicio}
                        GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_origen, x.pdx_bdo_id_origen, pma_id
                    )pa USING( gpo_id, ur_id, afe_id, bdo_id )
                    LEFT JOIN (
                        SELECT m.gpo_id AS gpo_id_polizaCancelado, m.ur_id AS ur_id_polizaCancelado, MIN(m.ue_id) AS ue_id_polizaCancelado,
                            MIN(m.msc_id) AS msc_id_polizaCancelado, MIN(m.pot_id) AS pot_id_polizaCancelado, MIN(m.prf_id) AS prf_id_polizaCancelado,
                            m.pma_id AS pma_id_polizaCancelado,MIN(m.pma_fecha) AS fechaCancelado,
                            CONCAT( MIN(m.pot_id), '-', MIN(m.prf_id), m.pma_id ,' ',MIN(m.apg_id)) AS polizaCancelado,
                            MIN(x.pdx_gpo_id_vinculacion) AS gpo_id, MIN(x.pdx_ur_id_vinculacion) AS ur_id,
                            MIN(x.pdx_afe_id_vinculacion) AS afe_id, MIN(x.pdx_bdo_id_vinculacion) AS bdo_id,
                            MIN(x.pdx_agrupador) AS agrupador
                        FROM cg_PolizaMaestro m
                            INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                        WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                            AND m.mmc_id = :mmc
                            AND m.tmc_id = 'C'
                            AND x.pdx_ejercicio = {$ejercicio}
                        GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_origen, x.pdx_bdo_id_origen, pma_id
                    )pc USING( gpo_id, ur_id, afe_id, bdo_id )
                    {$sqlFiltroGur}
                    AND t.msc_id_ministrado IS NULL
                    AND v.bdn_agrupador IS NULL
                    {$sqlFiltroRfc}
                    {$sqlFiltroRef}
                GROUP BY gpo_id, ur_id, bdo_id, afe_id, pa.pma_id_polizaAutorizado
                UNION ALL
                SELECT
                    v.gpo_id, v.ur_id, v.clc_id, v.afe_id,
                    v.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia, b.bdo_concepto,
                    b.bdo_percepcionPresupuestal, b.bdo_percepcionContable, b.bdo_retencionContable,
                    v.msc_id,
                    v.bdn_estado AS estado, DATE( v.bdn_fechaAutorizado ) AS fechaAutorizacion, DATE( v.bdn_fechaCancelado ) AS fechaCancelacion,
                    v.bdn_agrupador AS agrupador,
                    MIN(pa.polizaAutorizado) AS polizaAutorizado, MIN(pa.fechaAutorizado) AS fechaAutorizado,
                    pa.gpo_id_polizaAutorizado, pa.ur_id_polizaAutorizado,
                    MIN(pa.ue_id_polizaAutorizado) AS ue_id_polizaAutorizado,
                    MIN(pa.msc_id_polizaAutorizado) AS msc_id_polizaAutorizado,
                    MIN(pa.pot_id_polizaAutorizado) AS pot_id_polizaAutorizado, 
                    MIN(pa.prf_id_polizaAutorizado) AS prf_id_polizaAutorizado, pa.pma_id_polizaAutorizado,
                    MIN(pc.polizaCancelado) AS polizaCancelado,
                    MIN(pc.fechaCancelado) AS fechaCancelado,
                    MIN(pc.gpo_id_polizaCancelado) AS gpo_id_polizaCancelado,
                    MIN(pc.ur_id_polizaCancelado) AS ur_id_polizaCancelado,
                    MIN(pc.ue_id_polizaCancelado) AS ue_id_polizaCancelado,
                    MIN(pc.msc_id_polizaCancelado) AS msc_id_polizaCancelado,
                    MIN(pc.pot_id_polizaCancelado) AS pot_id_polizaCancelado,
                    MIN(pc.prf_id_polizaCancelado) AS prf_id_polizaCancelado,
                    MIN(pc.pma_id_polizaCancelado),
                    CONCAT( v.gpo_id, v.ur_id, '.', LPAD( v.msc_id, 2, 000 ), '.', LPAD( v.afe_id, 6, 0000000 ), '.', LPAD( v.bdo_id, 6, 000000 ), '.', COALESCE( NULL, 000000 ) ) AS id
                FROM cat_BancoDocumentos b
                    INNER JOIN (
                        SELECT gpo_id, ur_id, MIN(clc_id) AS clc_id, afe_id, bdo_id,
                            msc_id,
                            MIN(bdn_estado) AS bdn_estado, MIN(bdn_fechaAutorizado) AS bdn_fechaAutorizado, MIN(bdn_fechaCancelado) AS bdn_fechaCancelado,
                            MIN(bdn_agrupador) AS bdn_agrupador
                        FROM cg_BancoDocumentoProvisionTransferido
                        WHERE gpo_id = :gpo AND ur_id = :ur
                            {$sqlFiltroClc}
                            {$sqlFiltroBdo}
                            AND msc_id = {$sqlFiltroMes}
                        GROUP BY gpo_id, ur_id, afe_id, bdo_id
                    )v USING( gpo_id, ur_id, bdo_id )
                    LEFT JOIN (
                        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id,
                            msc_id AS msc_id_ministrado
                        FROM  tesoreria.min_BancoDocumentoMinistrado_{$ejercicio}
                        WHERE gpo_id = :gpo AND ur_id = :ur
                            {$sqlFiltroClc}
                            {$sqlFiltroBdo}
                            AND msc_id = {$sqlFiltroMes}
                            AND bdm_estado = 'A'
                    )t USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                    LEFT JOIN(
                        SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado,
                            MIN(m.ue_id) AS ue_id_polizaAutorizado,
                            MIN(m.msc_id) AS msc_id_polizaAutorizado, MIN(m.pot_id) AS pot_id_polizaAutorizado,
                            MIN(m.prf_id) AS prf_id_polizaAutorizado,
                            m.pma_id AS pma_id_polizaAutorizado, MIN(m.pma_fecha) AS fechaAutorizado,
                            CONCAT( MIN(m.pot_id), '-', MIN(m.prf_id), m.pma_id,' ',MIN(m.apg_id) ) AS polizaAutorizado,
                            MIN(x.pdx_gpo_id_vinculacion) AS gpo_id, MIN(x.pdx_ur_id_vinculacion) AS ur_id,
                            MIN(x.pdx_afe_id_vinculacion) AS afe_id, MIN(x.pdx_bdo_id_vinculacion) AS bdo_id,
                            MIN(x.pdx_agrupador) AS agrupador
                        FROM cg_PolizaMaestro m
                            INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                        WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                            AND m.mmc_id = :mmc
                            AND m.tmc_id = 'A'
                            AND x.pdx_ejercicio = {$ejercicio}
                        GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_origen, x.pdx_bdo_id_origen, pma_id
                    )pa USING( gpo_id, ur_id, afe_id, bdo_id )
                    LEFT JOIN (
                        SELECT m.gpo_id AS gpo_id_polizaCancelado, m.ur_id AS ur_id_polizaCancelado,  MIN(m.ue_id) AS ue_id_polizaCancelado,
                            MIN(m.msc_id) AS msc_id_polizaCancelado, MIN(m.pot_id) AS pot_id_polizaCancelado, MIN(m.prf_id) AS prf_id_polizaCancelado,
                            m.pma_id AS pma_id_polizaCancelado,  MIN(m.pma_fecha) AS fechaCancelado,
                            CONCAT( MIN(m.pot_id), '-', MIN(m.prf_id), m.pma_id ,' ',MIN(m.apg_id)) AS polizaCancelado,
                            MIN(x.pdx_gpo_id_vinculacion) AS gpo_id, MIN(x.pdx_ur_id_vinculacion) AS ur_id,
                            MIN(x.pdx_afe_id_vinculacion) AS afe_id, MIN(x.pdx_bdo_id_vinculacion) AS bdo_id,
                            MIN(x.pdx_agrupador) AS agrupador
                        FROM cg_PolizaMaestro m
                            INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                        WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                            AND m.mmc_id = :mmc
                            AND m.tmc_id = 'C'
                            AND x.pdx_ejercicio = {$ejercicio}
                        GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_origen, x.pdx_bdo_id_origen, pma_id
                    )pc USING( gpo_id, ur_id, afe_id, bdo_id )
                    {$sqlFiltroGur}
                    {$sqlFiltroRfc}
                    {$sqlFiltroRef}
                GROUP BY gpo_id, ur_id, bdo_id, afe_id, pa.pma_id_polizaAutorizado
                ORDER BY afe_id 
            ) MMM
        ";

        $this->setSql($sql);
        $this->setParams($sqlParams);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = '2000';
        $nav->idTabla = 'tbl-provision';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No se encontraron registros con los criterios seleccionados","warning");');
        } else {
            $this->mixScript('
                //Funcion para el multi-check y selec all
                $("#checkAll").on("change", function () {
                    $(`table tbody input[type="checkbox"]`).prop("checked", this.checked);
                });
                $("#checkAll").click(function () {
                    $("tr.multiseleccionable.seleccionable.seleccionado td:first-child").click();
                    if ($("#checkAll").is(":checked")) {
                        $("tr.multiseleccionable.seleccionable td:first-child").click();
                    }
                });
                $(document).on("click", "#tbl-provision tbody tr", function () {
                    // Agregar selección a esta fila
                    $(this).addClass("selected-row");
                    // Marcar el checkbox de esta fila
                    $(this).find(".checkselect").prop("checked", $(this).find(".checkselect").prop("checked"));
                });
                //end multicheck

                $("#tbl-provision tr").on("dblclick", function (ev){
                    ev.stopPropagation();
                    var var_clcFiltro = 1;
                    var var_bdoFiltro = 1;
                    if( $("#inputClc_id").val() == "" ){
                        var_clcFiltro = 2;
                    }
                    if( $("#inputBdo_id").val() == "" && $("#inputRfc_id").val() == "" && $("#inputBdo_referencia").val() == "" ){
                        var_bdoFiltro = 2;
                    }
                        
                    $(".checkselect").each(function(){
                        if($(this).is(":checked")){
                            if( $(this).attr("estado") != "A" ){
                                notify("Debe realizar la provisión, para ver el detalle", "warning" );
                            } else {
                                comunica("' . $this->pantalla . '", {
                                    metodo: "Documento",
                                    origen: "' . $this->origen . '",
                                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                    mes: $("#page-tabs li").children(".active").attr("mes"),
                                    clc_id: $(this).attr("clc_id"),
                                    clcFiltro: var_clcFiltro,
                                    gur: $(this).attr("gur"),
                                    afe_id: $(this).attr("afe_id"),
                                    bdo_id: $(this).attr("bdo_id"),
                                    tdf_id: $(this).attr("tdf_id"),
                                    rfc_id: $(this).attr("rfc_id"),
                                    bdo_referencia: $(this).attr("bdo_referencia"),
                                    bdoFiltro: var_bdoFiltro,
                                    agrupador: $(this).attr("agrupador")
                                 });
                            }
                        }
                    });
                });

                $("#btnProvision").click(function(e){
                    datos =[];
                    banderaProvision = true;
                    const seleccion = $("#tbl-provision").tabla("getFilaSeleccion");

                    var checados = false;
                    var provision = 0;

                    $(".checkselect").each(function(){
                        if($(this).is(":checked")){
                            datos.push({"bdo_id":$(this).attr("bdo_id"),"afe_id":$(this).attr("afe_id"),"bdo_referencia":$(this).attr("bdo_referencia"),"rfc_id":$(this).attr("rfc_id"),"estado":$(this).attr("estado")});

                            if($(this).attr("bdo_id") == "A"){
                                provision++;
                            }

                            if($(this).attr("estado") == "A"){
                                banderaProvision = false;
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
                    
                    if ( !banderaProvision ){
                        notify("Uno o mas documentos ya se encuentran provisionados", "warning" );
                    } else{
                        comunica("' . $this->pantalla . '",{
                            metodo: "ProvisionarDetalle",
                            data: datos,
                            origen: "' . $this->origen . '",
                            seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                            mes: $("#page-tabs li").children(".active").attr("mes"),
                            clc_id: $("#inputClc_id").val(),
                            clcFiltro: var_clcFiltro,
                            gur: seleccion.gur,
                            bdo_id: seleccion.bdo_id,
                            tdf_id: seleccion.tdf_id,
                            rfc_id: seleccion.rfc_id,
                            bdo_referencia: seleccion.bdo_referencia,
                            bdoFiltro: var_bdoFiltro,
                            tmc_id: "A"
                            },
                            true,
                            true
                        );
                    }
                });
            ');
            $this->mixScript('
                $(".panel-heading.header").remove();
                $(".panel-footer").remove();
                $(".tabla-footer").remove();'
            );
        }
    }

    private function DameInformacion()
    {
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
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

        if ($clc_id || $bdo_id || $rfc_id || $bdo_referencia) {
            $this->mixScript('$(".checkboxProvision .slick-column-name").html("<input type=\"checkbox\">");');
        } else {
            $this->mixScript('$(".checkboxProvision .slick-column-name").html("");');
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

        $detalle = false;
        $paginado = true;
        $registroInicial = 0;
        $PrimeraCon = true;
        $datos = vinculacionProvision(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'N',
            $niveles,
            array(
                "gur" => $gur,
                "clc_id" => $clc_id,
                "bdo_id" => $bdo_id,
                "rfc_id" => $rfc_id,
                "bdo_referencia" => $bdo_referencia,
            ),
            $paginado,
            $registroInicial,
            LINEAS_POR_PAGINA,
            $PrimeraCon
        );

        $nTotalPages = 0;

        if ($paginado == true) {
            $nTotalPages = $datos[0]['gpo_id'];
            unset($datos[0]);
        }

        if ($nTotalPages > 1 && $paginado == true) {
            $this->mixScript('
                $("#ProvisionPaginador").attr("last", 1 );
                var options = {
                    numberOfPages: 5,
                    currentPage: 1,
                    totalPages: ' . $nTotalPages . ',
                    containerClass: "pull-right",
                    listContainerClass: "nav nav-tabs",
                    itemContainerClass: function ( type, page, current ) { return (page === current) ? "active" : "pointer-cursor"; },
                    onPageClicked: function(e,originalEvent,type,page){
                        // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                        if ( $("#ProvisionPaginador").attr("last") != page ) {
                            comunica("' . $this->pantalla . '",{
                                metodo: "MuestraDatosPagina",
                                mes: $("#page-tabs li").children(".active").attr("mes"),
                                origen: "' . $this->origen . '",
                                seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                gur: $("#inputGur").val(),
                                clc_id: $("#inputClc_id").val(),
                                bdo_id: $("#inputBdo_id").val(),
                                rfc_id: $("#inputRfc_id").val(),
                                bdo_referencia: $("#inputBdo_referencia").val(),
                                paginado: true,
                                numpagina: page ,
                                numreg: ' . LINEAS_POR_PAGINA . '
                            });
                            $("#ProvisionPaginador").attr("last", page );
                        }
                    }
                }
                $("#ProvisionPaginador").bootstrapPaginator(options);
            ');
        } else {
            $this->mixScript('$("#ProvisionPaginador").html("")');
        }
        return json_encode($datos);
    }

    private function getTablaProvision(): string
    {
        $tabla = new stdClass();
        $tabla->esCompacta = true;
        $tabla->inputFiltro = "buscar";
        $tabla->id = 'tbl-provision';
        $tabla->multiseleccionable = true;
        //$tabla->seleccionable = true;
        $tabla->cabeceraFija = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaProvision';

        $tr = array();
        $th = new stdClass();
        $th->texto = '<input type="checkbox" id = "checkAll" class="checkselect">';
        $th->width = '20px';
        array_push($tr, $th);

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
        $th->texto = 'IMPORTE';
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
        $td->tipo = 'check';
        $td->id = 'myCheck';
        //$td->name = 'micheck[]';
        $td->attr = new stdClass();
        $td->attr->afe_id  = 'afe_id';
        $td->attr->bdo_id = 'bdo_id';
        $td->attr->tdf_id  = 'tdf_id';
        $td->attr->rfc_id  = 'rfc_id';
        $td->attr->bdo_referencia  = 'bdo_referencia';
        $td->attr->estado  = 'estado';
        $td->attr->clc_id = 'clc_id';
        $td->attr->activo = 0;
        $td->attr->agrupador = 'agrupador';
        $td->class='checkselect';
        $td->seleccionable = true;
        array_push($trbody, $td);

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
        $td->campoTxt->campos = ['bdo_percepcionPresupuestal'];
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

    private function DameInformacionPagina()
    {
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
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

        $detalle = false;
        $nInicial = (($numpagina - 1) * LINEAS_POR_PAGINA); // Como aqui cargamos la pagina inicialmente, es la primera
        $datos = vinculacionProvision(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'N',
            $niveles,
            array(
                "gur" => $gur,
                "clc_id" => $clc_id,
                "bdo_id" => $bdo_id,
                "rfc_id" => $rfc_id,
                "bdo_referencia" => $bdo_referencia,
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

    private function Documento()
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql(" SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = ? AND ur_id = ? ");
        $conexion->consultar([$this->gpo_id, $this->ur_id]);

        $mesContableAbierto = false;
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
            array("ejercicio" => APP_EJERCICIO, "tdf_id" => (isset($this->prm->tdf_id) ? $this->prm->tdf_id
                : ''), "mmc_id" => "N")
        );
        if (!isset($this->prm->tdf_id)) {
            $this->prm->tdf_id = $documento->tipoDocumentoFuente;
        }
        $html = '
            <legend style="border-bottom: 1px solid #e5e5e5; margin-bottom: 22px;">Análisis del documento a generar la póliza de la provisión</legend>
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
        . number_format($documento->percepcionPresupuestal, 2) . '</td>
                            <td width="10%" align="right"><span style="float:left">$</span>'
        . number_format($documento->percepcionContable, 2) . '</td>
                            <td width="10%" align="right"><span style="float:left">$</span>'
        . number_format($documento->retencionContable, 2) . '</td>
                            <td>' . $documento->fechaDocumento . '</td>
                        </tr>
                    </table>
                </fieldset><br/>';
        //preguntamos la(s) CLC(s) del documento
        //preguntamos el estado de los meses contables en funcion de las fechas de la ministracion
        foreach ($documento->detalleProvision as $key => $value) {
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
                $generaPolizaAutorizado = true;
                $fechaAutorizado = $value->fechaAutorizado;
                $estadoMesAutorizado = $mesContable[str_pad($value->mesAutorizado, 2, '0', STR_PAD_LEFT)]['ems_id'];
                if ($value->estado == 'C') {
                    if ($generaPolizaAutorizado) {
                        $generaPolizaCancelado = true;
                        $fechaCancelado = $value->fechaCancelado;
                    }
                    $estadoMesCancelado = $mesContable[str_pad($value->mesCancelado, 2, '0', STR_PAD_LEFT)]['ems_id'];
                }
            }
            $estadoPago = $value->estado;
        }

        /* INFORMACIÓN DE LA CLC EN LA QUE ESTA EL DOCUMENTO */
        if (count($clc_array) != 0) {
            $html .= '
                <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                    <legend>CLC en la que esta el documento
                        <span id="menclc" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                    </legend>
            ';
            foreach ($clc_array as $key => $value) {
                $clcDocumento = pintaClc(
                    $value['gpo_id'],
                    $value['ur_id'],
                    $value['clc_id'],
                    array("ejercicio" => APP_EJERCICIO, "bdo_id" => $bdo_id_documento, "todo" => true)
                );
                $html .= $clcDocumento->html;
            }
            $html .= '
                    </fieldset></br>
                ';
        }

        /************************************* Información de la Póliza Contable ************************************************************** */
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
                            $btnGenera->icon = 'solid fa-check me-2';
                            $btnGenera->class = 'me-1';
                            $botonGeneraPa = $this->ponBoton($btnGenera);

                            $this->mixScript('
                                $("#btnReGeneraPolizaA").click(function(){
                                    alertify.confirm(
                                        "RE HACER LA POLIZA ' . (isset($value->poliza) ? $value->poliza : '') . '",
                                        "Esta acción re construira la póliza de autorización como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
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
                                        undefined
                                    ).set("labels", {
                                        ok: "Aceptar", cancel: "Cancelar"
                                    });
                                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                                    if (btnAceptar) {
                                        btnAceptar.classList.remove("btn-primary");
                                        btnAceptar.classList.add("btn-success");
                                    }
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
                            $btnGenera->icon = 'solid fa-check me-2';
                            $btnGenera->class = 'me-1';
                            $botonGeneraPc = $this->ponBoton($btnGenera);

                            $this->mixScript('
                                $("#btnReGeneraPolizaC").click(function(){
                                    alertify.confirm(
                                        "RE HACER LA POLIZA ' . (isset($value->poliza) ? $value->poliza : '') . '",
                                        "Esta acción re construira la póliza de cancelación como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
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
                                        undefined
                                    ).set("labels", {
                                        ok: "Aceptar", cancel: "Cancelar"
                                    });
                                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                                    if (btnAceptar) {
                                        btnAceptar.classList.remove("btn-primary");
                                        btnAceptar.classList.add("btn-success");
                                    }
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
                    $inputFecha->enDiv = true;
                    $inputFecha->formato = 'fecha';
                    $inputFecha->labelClass = '_32 txtL';
                    $inputFecha->required = true;

                    $btnCancelar = new stdClass();
                    $btnCancelar->id = 'btnCancelar';
                    $btnCancelar->icon = 'block me-2';
                    $btnCancelar->name = 'nuevo';
                    $btnCancelar->color = 'rojo';
                    $btnCancelar->text = 'Cancelar';
                    $btnCancelar->value = 'Cancelar';

                    $html .= '
                        <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                            <div id="capfecha">
                                <legend>Mes del Provision esta cerrado, capture una nueva fecha</legend>
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
                    $btnGeneraPolizaCerrado->icon = 'solid fa-check me-2';
                    $btnGeneraPolizaCerrado->class = 'me-1';

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
                                    <legend>Mes de cancelación del Provision esta cerrado, capture una nueva fecha</legend>
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
            }
        } elseif ($estadoMesCancelado == 'A') {
            $botones = '<div style="display:inline-block; color:#900; padding:10px;">Generar Póliza</div>';
        } elseif ($estadoMesCancelado == 'C') {
            $botones = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado</div>';
        }

        if ($this->escritura && $estadoPago == 'A') {
            $btnCancelar = new stdClass();
            $btnCancelar->id = 'btnCancelar';
            $btnCancelar->name = 'nuevo';
            $btnCancelar->color = 'rojo';
            $btnCancelar->text = 'Cancelar Provisión';
            $btnCancelar->icon = 'remove me-2';
            $btnCancelar->value = 'Cancelar Provisión';

            $botones .= $this->ponBoton($btnCancelar);

            $mensajecancela = '';
            if ($estadoMesAutorizado == 'C') {
                $mensajecancela = 'Mes de pago Cerrado, se generara póliza de CANCELACIÓN en el primer mes abierto<br/>';
            }
            $this->mixScript('
                $("#btnCancelar").click(function(){
                    alertify.confirm(
                        "CANCELAR PROVISIÓN ",
                        "' . $mensajecancela . 'Esta acción Cancelara su registro de provisión, desea proceder? ",
                        function(){
                            comunica("' . $this->pantalla . '",{
                                metodo: "CallProcedure",
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
                                fecha: "' . $fechaAutorizado . '",
                                mesContableAbierto: "' . $mesContableAbierto . '",
                                tmc_id: "C"
                            });
                        },
                        undefined
                    ).set("labels", {
                        ok: "Aceptar", cancel: "Cancelar"
                    });
                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                    if (btnAceptar) {
                        btnAceptar.classList.remove("btn-primary");
                        btnAceptar.classList.add("btn-success");
                    }
                });
            ');
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
                    metodo: "main",
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
        $fecha = "";
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

        $provisionAutorizado = new sp_ProvisionAutorizado();
        $provisionAutorizado->setGpo_id($gpo_id_documento);
        $provisionAutorizado->setUr_id($ur_id_documento);
        $provisionAutorizado->setAgrupador($this->prm->agrupador);
        $provisionAutorizado->setPma_fecha($fecha);
        //Este no sabia bien que se debia poner
        $provisionAutorizado->setTmc_id($this->prm->tmc_id);

        $provisionAutorizado->setSis_id('SCG');
        $provisionAutorizado->setBit_observacion('PÓLIZA GENERADA DESDE LA PANTALLA C_5_4');
        $provisionAutorizado->setEjercicio(APP_EJERCICIO);

        $provisionAutorizado->execute();
        if (!$provisionAutorizado->getBandera()) {
            $tipo = "error";
            $mensaje = $provisionAutorizado->getMensaje();
        } else {
            $mensaje = "PROVISIONADO CORRECTAMENTE" . $provisionAutorizado->getMensaje();
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
        //$conexion->desconectar();
    }

    //muestra la informacion de los datos de transferencia de los documentos a provisionar
    private function ProvisionarDetalle()
    {
        if (!isset($this->prm->gur)) {
            $this->prm->gur = $this->gpo_id . $this->ur_id;
        }
        $datos = $this->prm->data;
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
        $i = 0;
        foreach ($datos as $bd) {
            if ($bdos == "") {
                $bdos .= $bd->bdo_id;
                $afes .= $bd->afe_id;
            } else {
                $bdos .= "," . $bd->bdo_id;
                $afes .= "," . $bd->afe_id;
            }
            $i++;
        }

        $html = '<span id="bds" bds="' . $bdos . '"/>';
        $html .= '<span id="afs" afs="' . $afes . '"/><div>';
        if ($i > 1) {
            $html .= '<legend>Análisis de los documentos a provisionar</legend>';
        } else {
            $html .= '<legend>Análisis del documento a provisionar</legend>';
        }

        $om = $this->DocumentoProvisionar($datos);
        if (!$om) {
            $this->mixScript(' notify("Error en la CLC, puede ser que no exista o este cancelado, favor de revisar","error");');
            return false;
        }
        $html .= '
                <fieldset class="col-md-12 col-xs-12">
                    ' . $om->html . '
                </fieldset><br/>
            ';

        $this->mixScript($om->script);
        if ($om->valido) {
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql("
                    SELECT msc_id, msc_dias
                    FROM cat_MesContable
                        INNER JOIN cg_MesContable USING( msc_id )
                    WHERE gpo_id = $this->gpo_id AND ur_id = $this->ur_id
                        AND msc_id >= " . $om->mes . "
                        AND ems_id = 'A'
                ");
            $conexion->consultar();
            $conexion->desconectar();
            $dato = $conexion->extraer();
            if ($dato['msc_id'] == '') {
                $dato['msc_id'] = 12;
                $dato['msc_dias'] = 31;
            }

            $bdp_concepto = new stdClass();
            $bdp_concepto->id = 'bdn_concepto';
            $bdp_concepto->placeholder = "Concepto de provisión";
            $bdp_concepto->required = true;
            $bdp_concepto->maxlength = 600;
            $bdp_concepto->label = "Concepto de provisión";
            $bdp_concepto->value = htmlspecialchars($om->concepto);

            $bdn_fechaAutorizado = new stdClass();
            $bdn_fechaAutorizado->id = 'bdn_fechaAutorizado';
            $bdn_fechaAutorizado->name = 'control';
            $bdn_fechaAutorizado->class = 'fecha';
            $bdn_fechaAutorizado->label = "Fecha de provision"; #5337329
            $bdn_fechaAutorizado->placeholder = "Fecha";
            $bdn_fechaAutorizado->required = true;
            $bdn_fechaAutorizado->labelClass = '_32 txtL';
            $bdn_fechaAutorizado->enDiv = true;
            $bdn_fechaAutorizado->value = $dato['msc_dias'] . "/" . $dato['msc_id'] . "/{$this->ejercicio}";

            $html .= '<fieldset class="col-md-12 col-xs-12"><fieldset class="col-md-4 col-xs-4">';
            $html .= $this->ponInput($bdp_concepto);
            $html .= '</fieldset>';

            $html .= '<fieldset class="col-md-2 col-xs-2">';
            $html .= $this->ponInput($bdn_fechaAutorizado);
            $html .= '</fieldset>';

            if ($this->escritura) {
                $btnRegistraProvision = new stdClass();
                $btnRegistraProvision->id = 'btnRegistraProvision';
                $btnRegistraProvision->icon = 'paper-plane me-2';
                $btnRegistraProvision->name = 'nuevo';
                $btnRegistraProvision->color = 'azul';
                $btnRegistraProvision->text = 'Nuevo';
                $btnRegistraProvision->value = 'Registrar Provisión';
                $botones .= $this->ponBoton($btnRegistraProvision);

                $anio = APP_EJERCICIO;
                $mes = date('m');
                $dia = date('d');
                $year = date('Y');
                $this->mixScript('
                    $("#btnRegistraProvision").click(function(){
                        if( $("#bdn_concepto").val() == "" ){
                            notify("Es necesario el concepto","error");
                            return false;
                        }
                        if( $("#bdp_referencia").val() == "" ){
                            notify("Es necesario un numero de documento","error");
                            return false;
                        }
                        if( $("#tdf_id").val() == "" ){
                            notify("Es necesario un tipo de documento","error");
                            return false;
                        }
                        if( $("#bdn_fechaAutorizado").val() == "" ){
                            notify("Es necesario un tipo de documento","error");
                            return false;
                        }
                        fec = $("#bdn_fechaAutorizado").val().split("/");
                        if( fec[2] != ' . $anio . ' ){
                            notify("El año debe ser ' . $anio . '","error");
                            return false;
                        }
                        if( (' . $anio . ') == ' . $year . ' ){
                            if( fec[1] > ' . $mes . ' ){
                                notify("El mes  debe ser igual o menor al actual","error");
                                return false;
                            }else{
                                if( fec[1] == ' . $mes . ' && fec[0] > ' . $dia . ' ){
                                    notify("El dia  debe ser igual o inferior al actual (' . $dia . ')","error");
                                    return false;
                                }
                            }
                        }else if ( (' . $anio . '+1) == ' . $year . ' ){

                            if(
                                (new Date("' . $this->ejercicio . '-12-31").getTime() < new Date(fec[2]+"-"+fec[1]+"-"+fec[0]).getTime())
                            ){
                                notify("La fecha maxima de pago es el 31 de DICIEMBRE","error");
                                return false;
                            }
                        } else {
                            notify("Favor de validar la fecha ","error");
                            return false;
                        }

                        if( $("#pot_id").val() == "" ){
                            notify("Seleccione el tipo de póliza a generar","error");
                            return false;
                        }
                        if( $("#bdp_poliza").val() == "" ){
                            notify("Seleccione un Valor para la generación de poliza","error");
                            return false;
                        }

                        comunica("' . $this->pantalla . '",{
                            metodo: "Pagar",
                            origen: "' . $this->origen . '",
                            seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                            mes: ' . $this->prm->mes . ',
                            gur: ' . $this->prm->gur . ',
                            clc_id: "' . $this->prm->clc_id . '",
                            clcFiltro: ' . $this->prm->clcFiltro . ',
                            bdo_id: "' . (isset($this->prm->bdo_id) ? $this->prm->bdo_id : "") . '",
                            rfc_id: "' . (isset($this->prm->afe_id) ? $this->prm->afe_id : "") . '",
                            bdo_referencia: "' . (isset($this->prm->bdo_referencia) ? $this->prm->bdo_referencia : "") . '",
                            bdoFiltro: ' . $this->prm->bdoFiltro . ',
                            data: $("#bds").attr("bds"),
                            afs: $("#afs").attr("afs"),
                            bdp_concepto: $("#bdn_concepto").val(),
                            bdp_referencia: $("#bdp_referencia").val(),
                            tdf_id_pagado: $("#tdf_id").val(),
                            bdn_fechaAutorizado: $("#bdn_fechaAutorizado").val(),
                            pot_id: $("#pot_id").val(),
                            bdp_poliza: $("#bdp_poliza").val(),
                            tmc_id: "A"
                        });
                    });
                ');
            } else {
                if ($_SESSION[_S]['nvj_id'] > 2) {
                    $html .= '<div class="alert alert-danger">EL REGISTRO DE PAGO LO REALIZA LA UNIDAD RESPONSABLE</div>';
                } else {
                    $html .= '<div class="alert alert-danger">NO TIENE PERMISOS PARA GENERAR EL PAGO</div>';
                }

            }
        }

        $html .= '</div>';
        $this->agregaTools($botones);
        $this->mixHtml($html);

        $this->mixScript('
            $("#btnRegresa").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo: "main",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                    mes: ' . $this->prm->mes . ',
                    gur: ' . $this->prm->gur . ',
                    clc_id: "' . $this->prm->clc_id . '",
                    clcFiltro: false,
                    bdo_id: "' . (isset($this->prm->bdo_id) ? $this->prm->bdo_id : "") . '",
                    rfc_id: "' . (isset($this->prm->rfc_id) ? $this->prm->rfc_id : "") . '",
                    bdo_referencia: "' . (isset($this->prm->bdo_referencia) ? $this->prm->bdo_referencia : "") . '",
                    bdoFiltro: false
                });
            });
        ');
    }

    private function DocumentoProvisionar($prm = null)
    {
        if (!$prm) {
            return false;
        }

        $bdos = "";
        $afes = "";
        $i = 0;

        foreach ($prm as $bd) {
            $i++;
            if ($bdos == "") {
                $bdos .= $bd->bdo_id;
                $afes .= $bd->afe_id;
            } else {
                $bdos .= "," . $bd->bdo_id;
                $afes .= "," . $bd->afe_id;
            }
        }
        $concepto = "";
        $html = '
                <legend>Registro de la provisión de los Documentos</legend>
                <table class="table table-bordered"cellpadding="10" style="padding:10px; width:100%; border:1px solid #EAEAEA; box-shadow:1px 2px 3px #999;">
                    <tr bgcolor="#EAEAEA">
                        <th colspan="7" style="text-align:center;" class="warning">Datos del Documento</th>
                        <th style="text-align:center;border: 1px solid rgba(45,171,102,0.5); background-color: #dff0d8 !important;" colspan="4" class="success">Importes Presupuestales</th><th></th>
                    </tr>
                    <tr bgcolor="#EAEAEA">
                        <th class="warning">UR</th>
                        <th class="warning">clc_id</th>
                        <th class="warning">Afectacion</th>
                        <th class="warning">Referencia</th>
                        <th class="warning">Proveedor</th>
                        <th class="warning">Percepción</th>
                        <th style="border: 1px solid rgba(45,171,102,0.5); background-color: #dff0d8 !important;" class="success">Ejercido</th>
                        <th style="border: 1px solid rgba(45,171,102,0.5); background-color: #dff0d8 !important;" class="success">Resultado</th>
                        <th style="border: 1px solid rgba(45,171,102,0.5); background-color: #dff0d8 !important;" class="success">Detalle</th>
                    </tr>
            ';

        $valido = true;

        $provisionado = $this->DocumentoDatos($bdos, $afes, true);
        
        if (!$provisionado) {
            return false;
        }
        $fecha = '';
        $gpo = '';
        $ur = '';

        foreach ($provisionado as $data) {
            $fecha = $data->bde_fechaAutorizado;
            $gpo = $data->gpo_id;
            $ur = $data->ur_id;
            if ($i > 1) {
                $ag = $i;
                $concepto = $data->clc_concepto;
            } else {
                $ag = $data->bdo_id;
                $concepto = $data->bdo_concepto;
            }

            $imp = round($data->bdo_importe, 2);
            $eje = round($data->Percepcion, 2);

            $html .= '
                    <tr>
                        <td>' . $data->gpo_id . $data->ur_id . '</td>
                        <td>' . $data->clc_id . '</td>
                        <td>' . $data->afe_id . '</td>
                        <td>' . $data->bdo_referencia . '</td>
                        <td>' . $data->rfc_id . '</td>
                        <td style="text-align:right"><span style="float:left">$</span>' . number_format(
                $data->bdo_importe,
                2
            ) . '</td>
                        <td style="text-align:right"><span style="float:left">$</span>' . number_format(
                $data->Percepcion,
                2
            ) . '</td>
                        ';
            if ($imp == $eje) {
                $html .= '<td class="success" style="background-color: #dff0d8 !important;">PROVISIÓN COMPLETA</td>';
            } else {
                $html .= '<td class="error">PROVISIÓN PARCIAL</td>';
            }

            $html .= '<td align="center">';
            $html .= '<a href="#" class="DocumentoDetalle" bdo="' . $data->bdo_id . '">Ver</a></td>';
            $html .= '</tr>';
        }
        $html .= '</table>
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
                    load_viewer(bdo,"DETALLE DEL USO DE DOCUMENTO","div");
                    comunica("' . $this->pantalla . '",{
                        metodo: "DocumentoDetalle",
                        bdo_id: bdo
                    });
                });
            ';

        $fechaValidar = new stdClass();
        $fechaValidar->fecha = $fecha;
        $res = new stdClass;
        $res = validaFecha($fechaValidar);

        $r = new stdClass;
        $r->valido = $valido;
        $r->html = $html;
        $r->concepto = $concepto;
        $r->mes = $res->mes;
        $r->script = $script;

        return $r;
    }

    private function Pagar()
    {
        /* VALIDAR LOS DATOS DE LOS DOCUMENTOS QUE VIENEN  */
        $ta = ''; //TIPO AGRUPADOR  A:AGRUPADO: D: DOCUMENTO

        if (isset($this->prm->data)) {
            if (count(array($this->prm->data)) == 0) {
                $this->mixScript(' notify("Error en el proceso de generar el pago","error");');
                return false;
            } else {
                $ta = count(array($this->prm->data)) > 1 ? "A" : "D";
            }

        } else {
            $this->mixScript(' notify("Error en el proceso de generar el pago","error");');
            return false;
        }

        $fecha = new stdClass();
        $fecha->gpo_id = $this->gpo_id;
        $fecha->ur_id = $this->ur_id;
        $fecha->fecha = $this->prm->bdn_fechaAutorizado;

        $fecha->fechaMaxima = "31/12/{$this->ejercicio}";

        $fecha->fechaMinima = "01/01/{$this->ejercicio}";

        $res = validaFecha($fecha);

        if ($res->valido) {
            if (!$res->fechaMaxima) {
                $this->mixScript('notify("' . $res->mensaje . '","error"); ');
                return false;
            }
            if (!$res->fechaMinima) {
                $this->mixScript('notify("' . $res->mensaje . '","error"); ');
                return false;
            }
        } else {
            $this->mixScript('notify("Error 1:' . $res->mensaje . '","error"); ');
            return false;
        }

        $this->prm->fechaMysql = $res->fechaMysql;
        $this->prm->mes = $res->mes;

        if ($res->edoMesContable == 'C') {
            $this->mixScript('notify("El mes esta cerrado","error"); ');
            return false;
        }

        $this->CallProcedure();
    }

    private function DocumentoDetalle()
    {
        $this->setContenedor("det_fs" . $this->prm->bdo_id);
        $provisionado = $this->DocumentoDatos($this->prm->bdo_id, true);
        $trsm = '';
        $trsp = '';
        $provi = new stdClass();
        $provi->pres = 0;
        $ejer = new stdClass();
        $ejer->pres = 0;

        $top = new stdClass();
        $top->pres = 0;
        foreach ($provisionado as $res) {
            $eje = $res->bde_fechaAutorizado != "" ? 'OK' : '';
            $pro = $res->bdn_fechaAutorizado != "" ? 'OK' : '';
            if ($pro != '') {
                $trsp .= '
                    <tr>
                        <td>' . $res->gpo_id . $res->ur_id . '</td>
                        <td>' . $res->clc_id . '</td>
                        <td>' . $res->afe_id . '</td>
                        <td align="right">' . number_format($res->Percepcion, 2) . '</td>
                        <td>' . $eje . '</td>
                        <td>' . $res->bde_fechaAutorizado . '</td>
                        <td>' . $pro . '</td>
                        <td>' . $res->bdn_fechaAutorizado . '</td>
                        </tr>';
                $provi->pres += $res->Percepcion;
            } else {
                $trsm .= '
                    <tr>
                        <td>' . $res->gpo_id . $res->ur_id . '</td>
                        <td>' . $res->clc_id . '</td>
                        <td>' . $res->afe_id . '</td>
                        <td align="right">' . number_format($res->Percepcion, 2) . '</td>
                        <td>' . $eje . '</td>
                        <td>' . $res->bde_fechaAutorizado . '</td>
                        </tr>';
                $top->pres += $res->Percepcion;
            }
            $ejer->pres += $res->Percepcion;
        }
        $bdo_id = isset($provisionado[0]->bdo_id) ? $provisionado[0]->bdo_id : "";
        $rfc_id = isset($provisionado[0]->rfc_id) ? $provisionado[0]->rfc_id : "";
        $bdo_referencia = isset($provisionado[0]->bdo_referencia) ? $provisionado[0]->bdo_referencia : "";
        $bdo_fechaDocumento = isset($provisionado[0]->bdo_fechaDocumento) ? $provisionado[0]->bdo_fechaDocumento : "";
        $bdo_concepto = isset($provisionado[0]->bdo_concepto) ? $provisionado[0]->bdo_concepto : "";
        $tdf_id = isset($provisionado[0]->tdf_id) ? $provisionado[0]->tdf_id : "";
        $bdo_importe = isset($provisionado[0]->bdo_importe) ? number_format($provisionado[0]->bdo_importe, 2) : number_format(0, 2);
        $html = '
                <h4>Información del documento</h4>
                <table class="table table-bordered">
                    <tr>
                        <td class="warning">ID</td>
                        <td class="warning">RFC</td>
                        <td class="warning">Referencia</td>
                        <td class="warning">Fecha</td>
                        <td class="warning">Concepto</td>
                        <td class="warning">TDF</td>
                        <td class="warning">Percepcion Presupuestal</td>
                    </tr>
                    <tr>
                        <td >' . $bdo_id . '</td>
                        <td >' . $rfc_id . '</td>
                        <td >' . $bdo_referencia . '</td>
                        <td >' . $bdo_fechaDocumento . '</td>
                        <td >' . $bdo_concepto . '</td>
                        <td >' . $tdf_id . '</td>
                        <td align="right">' . $bdo_importe . ' </td>
                    </tr>
                    <tr>
                        <td colspan="6" class="">Total Ejercido</td>
                        <td align="right">' . number_format($ejer->pres, 2) . ' </td>
                    </tr>
                    <tr>
                        <td colspan="6" class="">Total Provision</td>
                        <td align="right">' . number_format($provi->pres, 2) . ' </td>
                    </tr>
                    <tr>
                        <td colspan="6" class="success">Por Provisionar</td>
                        <td align="right" class="success">' . number_format(($ejer->pres
             - $provi->pres), 2) . ' </td>
                    </tr>
                </table>
                <h4>Ya provisionado</h4>
                <table class="table table-bordered">
                    <tr>
                        <td class="warning">UR</td>
                        <td class="warning">CLC</td>
                        <td class="warning">Afectacion</td>
                        <td class="warning">Percepcion Presupuestal</td>
                        <td class="warning">Ejercido</td>
                        <td class="warning">Fecha Ejercido</td>
                        <td class="warning">Provisión</td>
                        <td class="warning">Fecha Provisión</td>
                    </tr>' . ($trsp == "" ? '<tr><td colspan="10">Sin registros</td></tr>'
            : $trsp) . '</table>
                    <h4>Esta Provisión</h4>
                    <table class="table table-bordered">
                    <tr>
                        <td class="warning">UR</td>
                        <td class="warning">CLC</td>
                        <td class="warning">Afectacion</td>
                        <td class="warning">Percepcion Presupuestal</td>
                        <td class="warning">Provisionar</td>
                        <td class="warning">Fecha Provisionar</td>
                    </tr>.' . $trsm . '
                    <tr>
                        <td colspan="3" class="success">Total a Provisionar</td>
                        <td align="right" class="success">' . number_format(
            $top->pres,
            2
        ) . ' </td>
                        <td align="right" class="success"></td>
                        <td align="right" class="success"></td>
                    </tr>
                    </table>

            ';
        $this->mixHtml($html);
    }

    private function DocumentoDatos($bdos, $afes, $all = false)
    {
        $res = array();
        $conexion = new AccesoDatos();
        $sqlParams = [
            ':gpo' => $this->gpo_id,
            ':ur' => $this->ur_id,
        ];
        $groupBy = ($all ? 'gpo_id, ur_id, clc_id,bdo_id, afe_id ' : 'bdo_id,afe_id');
        $sql =
            "SELECT
                gpo_id, ur_id,
                clc_id, afe_id,
                bdo_id, bdo_referencia, rfc_id, bdo_fechaDocumento, b.tdf_id, bdo_concepto,
                bdo_percepcionPresupuestal AS bdo_importe,
                (Percepcion) AS Percepcion,
                clc_concepto,
                bdn_fechaAutorizado, DATE( bde_fechaAutorizado ) AS bde_fechaAutorizado
            FROM cat_BancoDocumentos b
                INNER JOIN scp_BancoDocumentoEjercido t USING( gpo_id, ur_id, bdo_id )
                LEFT JOIN (
                    SELECT
                        gpo_id, ur_id, clc_id, afe_id, bdo_id,
                        SUM(ncp_percepcionNeto) AS Percepcion,
                        clc_concepto
                    FROM scp_Presupuestal
                        INNER JOIN scp_Clc USING( gpo_id, ur_id, clc_id )
                        LEFT JOIN cg_BancoDocumentoProvisionTransferido USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                    WHERE gpo_id = :gpo AND ur_id = :ur
                        AND    bdo_id IN ( $bdos ) AND afe_id IN ( $afes )
                        AND clc_id > 0 AND ecl_id > 2
                        AND ( bdn_estado IS NULL OR bdn_estado = 'C' )
                    GROUP BY {$groupBy}
                )a USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                LEFT JOIN (
                    SELECT
                        gpo_id, ur_id, clc_id, afe_id,
                        bdn_estado, bdn_fechaAutorizado
                    FROM cg_BancoDocumentoProvisionTransferido
                    WHERE bdo_id IN ( $bdos ) AND afe_id IN ( $afes )
                        AND bdn_estado = 'A'
                )p USING( gpo_id, ur_id, clc_id, afe_id )
            WHERE gpo_id = :gpo AND ur_id = :ur
                AND    bdo_id IN ( $bdos )
                AND bde_estado = 'A'
                AND Percepcion > 0
        ";
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($sqlParams);
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

    private function CallProcedure()
    {
        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);

        switch ($this->prm->tmc_id) {
            case 'A':
                $bdos = '';
                $bdos = $this->prm->data;
                $afes = $this->prm->afs;
                $bdoArray = explode(',', $bdos);
                $agrupador = $gpo_id_documento . $ur_id_documento . $this->prm->mes . 'P';
                if (count(explode(",", $this->prm->data)) > 1) {
                    $agrupador .= "D" . "-" . md5($this->prm->data); // AGRUPADOR POR VARIOS DOCUMENTOS
                } else {
                    $agrupador .= "D" . "-" . $this->prm->data; // AGRUPADOR PARA UN SOLO DOCUMENTO
                }

                $this->prm->bdp_concepto = strtoupper(htmlspecialchars(str_replace(
                    '"',
                    '\"',
                    $this->prm->bdp_concepto
                )));

                // INICIAMOS LA TRANSACCIÓN...esto iba en la linea 2059 pero se cambio el nombre de la tabla ...INSERT INTO ctrl201Bitacora.tem_BancoDocumentoProvision(
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $sqlParams = [
                    ':mes' => $this->prm->mes,
                    ':fecha' => $this->prm->fechaMysql,
                    ':agrupador' => $agrupador,
                    ':usr' => $this->usr_id,
                    ':dgpo' => $gpo_id_documento,
                    ':dur' => $ur_id_documento,
                ];
                $sql =
                    "INSERT INTO cg_BancoDocumentoProvisionTransferido(
                        gpo_id, ur_id, msc_id, clc_id, afe_id, bdo_id,
                        bdn_fechaAutorizado,
                        bdn_percepcionPresupuestal,
                        bdn_tipoAgrupador, bdn_agrupador,
                        bdn_estado,
                        usr_id
                    )
                    SELECT
                        gpo_id, ur_id, :mes, MIN(clc_id), afe_id, bdo_id, :fecha,
                        SUM(Percepcion) AS Percepcion, 'D', :agrupador, 'A', :usr
                    FROM cat_BancoDocumentos b
                        INNER JOIN scp_BancoDocumentoEjercido t USING( gpo_id, ur_id, bdo_id )
                        LEFT JOIN (
                            SELECT
                                gpo_id, ur_id, clc_id, afe_id, bdo_id,
                                SUM(ncp_percepcionNeto) AS Percepcion,
                                clc_concepto
                            FROM scp_Presupuestal
                                INNER JOIN scp_Clc USING( gpo_id, ur_id, clc_id )
                                LEFT JOIN cg_BancoDocumentoProvisionTransferido USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                            WHERE gpo_id = :dgpo AND ur_id = :dur
                                AND    bdo_id IN ( $bdos ) AND afe_id IN ( $afes )
                                AND clc_id > 0 AND ecl_id > 2
                                AND ( bdn_estado IS NULL OR bdn_estado = 'C' )
                            GROUP BY gpo_id, ur_id, clc_id, afe_id,bdo_id
                        )a USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                        LEFT JOIN (
                            SELECT
                                gpo_id, ur_id, clc_id, afe_id,
                                bdn_estado, bdn_fechaAutorizado
                            FROM cg_BancoDocumentoProvisionTransferido
                            WHERE bdo_id IN ( $bdos ) AND afe_id IN ( $afes )
                                AND bdn_estado = 'A'
                        )p USING( gpo_id, ur_id, clc_id, afe_id )
                    WHERE gpo_id = :dgpo AND ur_id = :dur
                        AND    bdo_id IN ( $bdos ) AND afe_id IN ( $afes )
                        AND bde_estado = 'A'
                        AND Percepcion > 0
                    GROUP BY gpo_id, ur_id, bdo_id, afe_id
                    ON DUPLICATE KEY UPDATE
                        bdn_fechaAutorizado = :fecha ,
                        bdn_agrupador = :agrupador,
                        bdn_estado = 'A',
                        usr_id = :usr ";

                $conexion->setSql("SET @bit_observacion = 'REGISTRO DE PAGO DESDE LA PANTALLA DE VINCULACIÓN PROVISION', @usr_ip = INET_ATON('" . $this->usr_ip . "'), @usr_id = " . $this->usr_id);
                $conexion->consultar();
                $conexion->bindParams($sqlParams);
                $conexion->setSql($sql);
                $conexion->consultar();
                $provisionAutorizado = new sp_ProvisionAutorizado();
                $provisionAutorizado->setGpo_id($gpo_id_documento);
                $provisionAutorizado->setUr_id($ur_id_documento);
                $provisionAutorizado->setAgrupador($agrupador);
                $provisionAutorizado->setPma_fecha($this->prm->fechaMysql);
                //Este no sabia bien que se debia poner
                $provisionAutorizado->setTmc_id($this->prm->tmc_id);

                $provisionAutorizado->setSis_id('SCG');
                $provisionAutorizado->setBit_observacion('PROVISION AUTORIZADO DESDE LA PANTALLA C_5_4');
                $provisionAutorizado->setEjercicio(APP_EJERCICIO);

                $provisionAutorizado->execute();
                if (!$provisionAutorizado->getBandera()) {
                    $tipo = "error";
                    $mensaje = $provisionAutorizado->getMensaje();
                } else {
                    $mensaje = $provisionAutorizado->getMensaje();
                    $tipo = "success";
                }

                $conexion->desconectar();
                unset($conexion);
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
                        bdo_id: "' . ($this->prm->bdoFiltro == 2 ? $bdoArray[0] : $this->prm->bdo_id) . '",
                        rfc_id: "",
                        bdo_referencia: "",
                        bdoFiltro: ' . $this->prm->bdoFiltro . ',
                        agrupador: "' . $agrupador . '"
                    });
                ');
                break;
            case 'C':
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $old = " -- SELECT msc_id, bdp_concepto, bdp_referencia, tdf_id, bdn_fechaAutorizado, pot_id, bdp_poliza, bdo_id ";
                $sql = "SELECT msc_id, bdo_id
                    FROM cg_BancoDocumentoProvisionTransferido
                    WHERE gpo_id = ? AND ur_id = ? AND bdn_agrupador = ? ";

                $conexion->setSql($sql);
                $conexion->consultar([$gpo_id_documento, $ur_id_documento, $this->prm->agrupador]);
                $data = $conexion->extraer();

                $res = null;

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

                        $dr2 = new stdClass();
                        $dr2->gpo_id = $this->gpo_id;
                        $dr2->ur_id = $this->ur_id;
                        $arre = explode("-", $res->fechaMysql);
                        $dr2->mes = $arre[1];
                        $mesIndex = $dr2->mes;
                        $mesIndex++;
                        for (; $mesIndex <= 12; $mesIndex++) {
                            $dr2->mes = $mesIndex;
                            $ems_id = estadoMesContable($dr2);
                            if ($ems_id == "A") {
                                break;
                            }
                        }
                        if ($ems_id == "A") {
                            if ($mesIndex == (int) date("m")) {
                                $fechaNueva = date("d") . '/' . date("m") . '/' . date("Y");
                            } else {
                                $fecha = new DateTime($fechaValidar->anio . '-' . $mesIndex . '-01');
                                $fecha->modify('last day of this month');
                                $fechaNueva = $fecha->format('d/m/Y'); // imprime por ejemplo: 31/12/2012
                            }

                            $fechaValidar2 = new stdClass();
                            $fechaValidar2->gpo_id = $gpo_id_documento;
                            $fechaValidar2->ur_id = $ur_id_documento;
                            $fechaValidar2->fecha = $fechaNueva;
                            $fechaValidar2->anio = APP_EJERCICIO;
                            $res = null;
                            $res = validaFecha($fechaValidar2);
                            if (!$res->valido) {
                                $this->mixScript('notify("error:' . $res->mensaje . '","error");');
                                return false;
                            }
                        } else {
                            $this->mixScript('notify("Favor de revisar el mes de la Provisión","error");');
                            return false;
                        }
                    }
                    $fecha = $res->fechaMysql;
                } else {
                    $dr = new stdClass();
                    $dr->gpo_id = $this->gpo_id;
                    $dr->ur_id = $this->ur_id;
                    $dr->mes = $data['msc_id'];
                    $ems_id = estadoMesContable($dr);
                    if ($ems_id == 'C') {
                        $this->mixScript('notify("el mes esta cerrado","error");');
                        return false;
                    }
                }

                // INICIAMOS LA TRANSACCIÓN
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setObservacion("CANCELACIÓN DEL REGISTRO DE PROVISIÓN DESDE LA PANTALLA DE VINCULACIÓN");
                $sql = "UPDATE cg_BancoDocumentoProvisionTransferido
                    SET bdn_fechaCancelado = :fecha , msc_id_cancelado = :mes, bdn_estado = 'C'
                    WHERE gpo_id = :dgpo AND ur_id = :dur AND bdn_agrupador = :agrupador ";
                $conexion->setSql($sql);
                $conexion->consultar([
                    ':fecha' => $res->fechaMysql,
                    ':mes' => $data['msc_id'],
                    ':dgpo' => $gpo_id_documento,
                    ':dur' => $ur_id_documento,
                    ':agrupador' => $this->prm->agrupador,
                ]);
                $provisionCancelado = new sp_ProvisionCancelado();
                $provisionCancelado->setGpo_id($gpo_id_documento);
                $provisionCancelado->setUr_id($ur_id_documento);
                $provisionCancelado->setAgrupador($this->prm->agrupador);
                $provisionCancelado->setPma_fecha($res->fechaMysql);
                //Este no sabia bien que se debia poner
                $provisionCancelado->setTmc_id($this->prm->tmc_id);

                $provisionCancelado->setSis_id('SCG');
                $provisionCancelado->setBit_observacion('PAGO CANCELADO DESDE LA PANTALLA C_5_4');
                $provisionCancelado->setEjercicio(APP_EJERCICIO);

                $provisionCancelado->execute();

                if (!$provisionCancelado->getBandera()) {
                    $conexion->setObservacion("CANCELACIÓN DEL REGISTRO DE PROVISIÓN DESDE LA PANTALLA DE VINCULACIÓN");
                    $sql = "UPDATE cg_BancoDocumentoProvisionTransferido
                        SET bdn_fechaCancelado = NULL, msc_id_cancelado = NULL, bdn_estado = 'A'
                        WHERE gpo_id = :dgpo AND ur_id = :dur AND bdn_agrupador = :agrupador ";
                    $conexion->setSql($sql);
                    $conexion->consultar([
                        ':dgpo' => $gpo_id_documento,
                        ':dur' => $ur_id_documento,
                        ':agrupador' => $this->prm->agrupador,
                    ]);
                    $tipo = "error";
                    $mensaje = $provisionCancelado->getMensaje();
                } else {
                    $mensaje = "LA PROVISIÓN SE CANCELO SATISFACTORIAMENTE " . $provisionCancelado->getMensaje();
                    $tipo = "success";
                }

                $conexion->desconectar();
                unset($conexion);

                $this->mixScript('
                    notify("' . $mensaje . '","' . $tipo . '");
                    comunica("' . $this->pantalla . '", {
                        metodo: "main",
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
                ');
                break;
        }
    }
}
//Permisos de acceso al controlador
if (isset($_SESSION[_S]['prm']['C']['5']['4']['1'])) {
    new ControladorProvision();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}