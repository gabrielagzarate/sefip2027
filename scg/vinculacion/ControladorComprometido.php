<?php
//librerias
DEFINE("LINEAS_POR_PAGINA", 1000);
session_start();
require_once dirname(__FILE__) . '/../../lib/getCompromiso.php';
require_once dirname(__FILE__) . '/../../lib/opcionConteo.php';
require_once dirname(__FILE__) . '/../../lib/pintaClc.php';
require_once dirname(__FILE__) . '/../../lib/scg/vinculacion/vinculacionComprometido.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPoliza.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPolizaAgrupador.php';
require_once dirname(__FILE__) . '/../../lib/scg/pintaPoliza.php';
require_once dirname(__FILE__) . '/../../lib/validaFecha.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/vinculacion/sp_Comprometido.php';

class ControladorComprometido extends WebResponse
{
    private $lectura;
    private $escritura;
    private $gpo_id;
    private $ur_id;
    private $ue_id;
    private $campoHabilitado;
    private $origen;

    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        //Permisos de acceso a las tareas del controlador
        $this->escritura = isset($_SESSION[_S]['prm']['C']['5']['1']['2']) ? true : false;
        $this->gpo_id = getSession('clasAdm-gpo');
        $this->ur_id = getSession('clasAdm-ur');
        $this->ue_id = getSession('clasAdm-ue', true);

        // Marcos: 16/feb/2016
        // Determinaremos si viene de la vinculación por CLC, para que en éste caso, inhabilite los cuadros de texto
        $this->campoHabilitado = "";
        if (isset($this->prm->origen) && $this->prm->origen == "clc") {
            $this->campoHabilitado = " disabled ";
        }

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
        $html = $this->getTablaComprometido();
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
        $btnRegresa->class = "me-1";

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
        $bntExcel->id = 'excelComprometido';
        $bntExcel->name = 'bntExcel';
        $bntExcel->text = 'Excel';
        $bntExcel->value = 'Excel';
        $bntExcel->type = 'button';
        $bntExcel->icon = 'solid fa-file-excel me-2';
        $bntExcel->class = "btn btn-outline-success me-1";

        $clcFiltro = false;
        $cmpFiltro = false;

        switch ($this->origen) {
            case "clc":
                $clcFiltro = true;
                break;
            case "cmp":
                $cmpFiltro = true;
                break;
            default:
                if (isset($this->prm->clcFiltro) && $this->prm->clcFiltro == 1) {
                    $clcFiltro = true;
                }
                if (isset($this->prm->cmpFiltro) && $this->prm->cmpFiltro == 1) {
                    $cmpFiltro = true;
                }
                break;
        }

        $botones = '
            <div class="row row-cols-auto gap-1">
            ' . ((isset($this->prm->origen) && $this->prm->origen == "clc")
            ? '
                <fieldset class="col">
                ' . $this->ponBoton($btnRegresa) . '
                </fieldset>
                ' : '') . '
                <fieldset class="col">
                    <input id="inputClc_id" placeHolder="CLC" type="text" maxlength=6 ' . $this->campoHabilitado . ' class="form-control" value="' . ($clcFiltro
            ? $this->prm->clc_id : "") . '"/>
                </fieldset>
                <fieldset class="col">
                    <input id="inputCmp_id" placeHolder="# Compromiso" ' . $this->campoHabilitado . ' maxlength=7 class="form-control" value="' . ($cmpFiltro
            ? $this->prm->cmp_id : "") . '"/>
                </fieldset>
                <fieldset class="col">
                    <input id="inputRfc_id" placeHolder="RFC" ' . $this->campoHabilitado . ' maxlength=20 class="form-control" value="' . ($cmpFiltro
            ? $this->prm->rfc_id : "") . '"/>
                </fieldset>
                <fieldset class="col">
                    <input id="inputCmp_numOrden" placeHolder="# Orden" ' . $this->campoHabilitado . ' class="form-control" value="' . ($cmpFiltro
            ? $this->prm->cmp_numOrden : "") . '"/>
                </fieldset>
                <fieldset class="col">
                    ' . $this->ponBoton($bntExcel) . '
                </fieldset>
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
                        $("#btnRegresa").click(function(e){
                            e.preventDefault();
                            $("nav").menusinpres("clickMenu");
                            e.stopPropagation();
                        });

                        comunica("' . $this->pantalla . '",{
                            metodo: "MuestraDatos",
                            origen: "' . $this->origen . '",
                            seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion
                    : "") . '",
                            mes: $("#page-tabs li").children(".active").attr("mes"),
                            gur: $("#inputGur").val(),
                            clc_id: $("#inputClc_id").val(),
                            cmp_id: $("#inputCmp_id").val(),
                            rfc_id: $("#inputRfc_id").val(),
                            cmp_numOrden: $("#inputCmp_numOrden").val()
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
                            cmp_id: $("#inputCmp_id").val(),
                            rfc_id: $("#inputRfc_id").val(),
                            cmp_numOrden: $("#inputCmp_numOrden").val()
                        });
                    ');
                break;
        }

        $this->mixScript('
            ' .
            (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();'
                : '') . '
                $("#opciones").click( function(e){
                    comunica("' . $this->pantalla . '", {
                        metodo: "Filtro",
                        origen: "' . $this->origen . '",
                        mes: $("#page-tabs li").children(".active").attr("mes"),
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        cmp_id: $("#inputCmp_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        cmp_numOrden: $("#inputCmp_numOrden").val()
                    });
                });

                $("#btnLimpiar").click( function(e){
                    $("#inputGur").val("");
                    $("#inputClc_id").val("");
                    $("#inputCmp_id").val("");
                    $("#inputRfc_id").val("");
                    $("#inputCmp_numOrden").val("");

                    e.preventDefault();
                    var seleccionado = $(".meses");
                    $("#page-tabs li").each(function (index) { $(this).children("a").removeClass("active"); });
                    seleccionado.closest("li").addClass("active");

                    comunica("' . $this->pantalla . '",{
                        metodo: "MuestraDatos",
                        mes: seleccionado.attr( "mes" ),
                        origen: "' . $this->origen . '",
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        cmp_id: $("#inputCmp_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        cmp_numOrden: $("#inputCmp_numOrden").val()
                    });

                    e.stopPropagation();
                    return false;
                });

                $("#excelComprometido").click( function(e) {
                        var form = $("<form>")
                            .attr("action", "rep/excel/scg/vinculacion/exp_Comprometido.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "5" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "1" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "C" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs li").children(".active").attr("mes") ))

                            .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputCmp_id").attr("value", $("#inputCmp_id").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputRfc_id").attr("value", $("#inputRfc_id").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputCmp_numOrden").attr("value", $("#inputCmp_numOrden").val() ))

                        $("body").append(form);
                        form.submit();
                });

                $("#imprimirComprometido").click( function(e) {
                        var form = $("<form>")
                            .attr("action", "rep/doc/scg/reporte.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "5" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "1" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "C" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs li").children(".active").attr("mes") ))

                            .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputCmp_id").attr("value", $("#inputCmp_id").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputRfc_id").attr("value", $("#inputRfc_id").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputCmp_numOrden").attr("value", $("#inputCmp_numOrden").val() ))

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

            $html = $this->getTablaComprometido();
            $this->mixHtml($html);

        $this->DibujaPantalla();
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
                        clc_id: $("#inputClc_id").val(),
                        cmp_id: $("#inputCmp_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        cmp_numOrden: $("#inputCmp_numOrden").val()
                    });

                    e.stopPropagation();
                    return false;
                });
            ');

        if ($this->origen == 'clc') {
            $this->mixScript('
                    $("#page-tabs").hide();
                    $("#inputCmp_id").hide();
                    $("#inputRfc_id").hide();
                    $("#inputCmp_numOrden").hide();
                ');
        }
    }

    private function MuestraDatos()
    {
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $clc_id = $this->getPrm('clc_id', '');
        $cmp_id = $this->getPrm('cmp_id', '');
        $rfc_id = $this->getPrm('rfc_id', '');
        $cmp_numOrden = '';
        if ($prmNumOrden = $this->getPrm('cmp_numOrden', null)){
            $numOrden = explode(",",str_replace(' ', '', (string) $prmNumOrden));
            foreach ($numOrden as $cmp) {
                $cmp_numOrden .= '"' . $cmp . '",';
            }
            $cmp_numOrden = substr($cmp_numOrden, 0, -1);
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
            'C',
            $niveles,
            array(
                "gur" => $gur,
                "clc_id" => $clc_id,
                "cmp_id" => $cmp_id,
                "rfc_id" => $rfc_id,
                "cmp_numOrden" => $cmp_numOrden
            ),
            $paginado,
            $registroInicial,
            LINEAS_POR_PAGINA,
            $PrimeraCon
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
        $parametroExtra = array(),
        $paginado = false,
        $nInicial = 0,
        $numreg = 0,
        $ejecsql2 = false
    ) {
    
        $sqlFiltro = '';
        $parametrosSQL = [
            ':gpo' => $gpo_id,
            ':ur' => $ur_id,
            ':mmc' => $mmc_id
        ];
        if ($parametroExtra['gur'] != '') {
            $sqlFiltro = " AND c.gpo_id = :cgpo and c.ur_id = :cur ";
            $parametrosSQL[':cgpo'] = substr($parametroExtra['gur'], 0, 1);
            $parametrosSQL[':cur'] = substr($parametroExtra['gur'],1,2);
        }
        if ($parametroExtra['clc_id'] != '') {
            $sqlFiltro .= " AND n.clc_id = :nclc ";
            $parametrosSQL[':nclc'] =$parametroExtra['clc_id'];
        }
        if ($parametroExtra['cmp_id'] != '') {
            $sqlFiltro .= " AND c.cmp_id = :ccmp ";
            $parametrosSQL[':ccmp'] = $parametroExtra['cmp_id'];
        }
        if ($parametroExtra['rfc_id'] != '') {
            $sqlFiltro .= " AND c.rfc_id = :crfc ";
            $parametrosSQL[':crfc'] = $parametroExtra['rfc_id'];
        }
        if ($parametroExtra['cmp_numOrden'] != '') {
            $orden = $parametroExtra['cmp_numOrden'];
            $sqlFiltro .= " AND c.cmp_numOrden IN ( {$orden} ) ";
        }
        if ($periodo['mesDesde'] != '') {
            $sqlFiltro .= " AND c.msc_id_autorizado =  :mesdesde ";
            $parametrosSQL[':mesdesde'] = $periodo['mesDesde'];
        }
        $paginadosql = "";
        if ($paginado == true) {
            $paginadosql = " LIMIT " . intval($nInicial) . ", " . intval($numreg) . " ";
        }
    
        $sql = "
        SELECT SQL_CALC_FOUND_ROWS
            c.gpo_id, c.ur_id, concat(gpo_id, ur_id) as gur,
            GROUP_CONCAT( DISTINCT n.clc_id ) AS clc_id, GROUP_CONCAT( DISTINCT n.afe_id ) AS afe_id, GROUP_CONCAT( DISTINCT n.bdo_id ) AS bdo_id,
            c.cmp_id, c.rfc_id, c.cmp_numOrden, c.cmp_fechaOrden, c.cmp_concepto,
            c.cmp_tipoCompromiso, c.cmp_tipoMovimiento, c.cmp_importe,
            c.msc_id_autorizado,
            CASE c.ecm_id WHEN 'C' THEN 'C' ELSE 'A' END AS estado, DATE( c.cmp_fechaAutorizado ) AS fechaAutorizacion, DATE( c.cmp_fechaCancelado ) AS fechaCancelacion,
            CONCAT( c.gpo_id, c.ur_id, LPAD( c.msc_id_autorizado, 2, 000 ), 'C', c.cmp_tipoCompromiso, c.cmp_id ) AS agrupador,
    
            pa.polizaAutorizado, MAX(pa.fechaAutorizado) AS fechaAutorizado,
            MAX(pa.gpo_id_polizaAutorizado) AS gpo_id_polizaAutorizado, MAX(pa.ur_id_polizaAutorizado) AS ur_id_polizaAutorizado, MAX(pa.ue_id_polizaAutorizado) AS ue_id_polizaAutorizado,
            MAX(pa.msc_id_polizaAutorizado) AS msc_id_polizaAutorizado, MAX(pa.pot_id_polizaAutorizado) AS pot_id_polizaAutorizado, MAX(pa.prf_id_polizaAutorizado) AS prf_id_polizaAutorizado,
            MAX(pa.pma_id_polizaAutorizado) AS pma_id_polizaAutorizado,
    
            MAX(pc.polizaCancelado) AS polizaCancelado, MAX(pc.fechaCancelado) as fechaCancelado,
            MAX(pc.gpo_id_polizaCancelado) AS gpo_id_polizaCancelado, MAX(pc.ur_id_polizaCancelado) AS ur_id_polizaCancelado, MAX(pc.ue_id_polizaCancelado) AS ue_id_polizaCancelado,
            MAX(pc.msc_id_polizaCancelado) AS msc_id_polizaCancelado, MAX(pc.pot_id_polizaCancelado) AS pot_id_polizaCancelado, MAX(pc.prf_id_polizaCancelado) AS prf_id_polizaCancelado,
            MAX(pc.pma_id_polizaCancelado) AS pma_id_polizaCancelado,
    
            CONCAT( c.gpo_id, c.ur_id, '.', LPAD( c.msc_id_autorizado, 2, 000 ), '.', LPAD( c.cmp_id, 6, 000000 ) ) AS id
        FROM scp_Compromiso c
            LEFT JOIN scp_Presupuestal n USING( gpo_id, ur_id, cmp_id )
            LEFT JOIN(
                SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado,MAX(m.ue_id) AS ue_id_polizaAutorizado,
                    MAX(m.msc_id) AS msc_id_polizaAutorizado, MAX(m.pot_id) AS pot_id_polizaAutorizado, MAX(m.prf_id) AS prf_id_polizaAutorizado,
                    MAX(m.pma_id) AS pma_id_polizaAutorizado, MAX(m.pma_fecha) AS fechaAutorizado,
                    CONCAT( MAX(m.pot_id), '-', MAX(m.prf_id), MAX(m.pma_id),' ',MAX(m.apg_id)  ) AS polizaAutorizado,
                    (x.pdx_gpo_id_vinculacion) AS gpo_id, (x.pdx_ur_id_vinculacion) AS ur_id,
                    x.pdx_cmp_id_vinculacion AS cmp_id,
                    (x.pdx_agrupador) AS agrupador
                FROM cg_PolizaMaestro m
                    INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                    AND m.mmc_id = :mmc
                    AND m.tmc_id = 'A'
                    AND x.pdx_ejercicio = " . APP_EJERCICIO . "
                GROUP BY m.gpo_id, m.ur_id, x.pdx_cmp_id_vinculacion,gpo_id, ur_id, cmp_id,agrupador
            )pa USING( gpo_id, ur_id, cmp_id )
            LEFT JOIN (
                SELECT m.gpo_id AS gpo_id_polizaCancelado, m.ur_id AS ur_id_polizaCancelado, MAX(m.ue_id) AS ue_id_polizaCancelado,
                    MAX(m.msc_id) AS msc_id_polizaCancelado, MAX(m.pot_id) AS pot_id_polizaCancelado, MAX(m.prf_id) AS prf_id_polizaCancelado,
                    MAX(m.pma_id) AS pma_id_polizaCancelado, MAX(m.pma_fecha) AS fechaCancelado,
                    CONCAT( MAX(m.pot_id), '-', MAX(m.prf_id), MAX(m.pma_id),' ',MAX(m.apg_id)  ) AS polizaCancelado,
                    (x.pdx_gpo_id_vinculacion) AS gpo_id, (x.pdx_ur_id_vinculacion) AS ur_id,
                    x.pdx_cmp_id_vinculacion AS cmp_id,
                    (x.pdx_agrupador) AS agrupador
                FROM cg_PolizaMaestro m
                    INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                    AND m.mmc_id = :mmc
                    AND m.tmc_id = 'C'
                    AND x.pdx_ejercicio = " . APP_EJERCICIO . "
                GROUP BY m.gpo_id, m.ur_id, x.pdx_cmp_id_vinculacion,gpo_id, ur_id, cmp_id,agrupador
            )pc USING( gpo_id, ur_id, cmp_id )
        WHERE c.cmp_id IS NOT NULL {$sqlFiltro}
        GROUP BY gpo_id, ur_id, cmp_id, pa.polizaAutorizado;
        ";
        $this->setSql($sql);
        $this->setParams($parametrosSQL);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf      = '2000';
        $nav->idTabla = 'tbl-comprometido';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No se encontraron polizas con los criterios seleccionados","warning");');
        } else {
            $this->mixScript('
                $("#tbl-comprometido tr").on("dblclick", function (ev){
                    ev.stopPropagation();
                    var var_clcFiltro = 1;
                    var var_cmpFiltro = 1;
                    if( $("#inputClc_id").val() == "" ){
                        var_clcFiltro = 2;
                    }
                    if( $("#inputCmp_id").val() == "" && $("#inputRfc_id").val() == "" && $("#inputCmp_numOrden").val() == "" ){
                        var_cmpFiltro = 2;
                    }
                    const prm = $("#tbl-comprometido").tabla("getFilaSeleccion");

                    comunica("' . $this->pantalla . '", {
                        metodo: "Documento",
                        origen: "' . $this->origen . '",
                        mes: $("#page-tabs li").children(".active").attr("mes"),
                        clc_id: prm.clc_id,
                        clcFiltro: var_clcFiltro,
                        gur: prm.gur,
                        cmp_id: prm.cmp_id,
                        rfc_id: prm.rfc_id,
                        cmp_numOrden: prm.cmp_numOrden,
                        cmpFiltro: var_cmpFiltro,
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

    private function DameInformacion()
    {

        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $clc_id = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $cmp_id = (isset($this->prm->cmp_id) ? $this->prm->cmp_id : '');
        $rfc_id = (isset($this->prm->rfc_id) ? $this->prm->rfc_id : '');
        $cmp_numOrden = '';
        if (isset($this->prm->cmp_numOrden) && !empty($this->prm->cmp_numOrden)) {
            $numOrden = explode(
                ",",
                str_replace(' ', '', $this->prm->cmp_numOrden)
            );
            foreach ($numOrden as $cmp) {
                $cmp_numOrden .= '"' . $cmp . '",';
            }
            $cmp_numOrden = substr($cmp_numOrden, 0, -1);
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

        $detalle = false;
        $paginado = true;
        $PrimeraCon = true;
        $registroInicial = 0;
        $datos = vinculacionComprometido(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'C',
            $niveles,
            array(
                "gur" => $gur,
                "clc_id" => $clc_id,
                "cmp_id" => $cmp_id,
                "rfc_id" => $rfc_id,
                "cmp_numOrden" => $cmp_numOrden
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
            $this->mixScript('$("#ComprometidoPaginador").attr("last", 1 );
                    var options = {
                        numberOfPages: 5,
                        currentPage: 1,
                        totalPages: ' . $nTotalPages . ',
                        containerClass: "pull-right",
                        listContainerClass: "nav nav-tabs",
                        itemContainerClass: function ( type, page, current ) { return (page === current) ? "active" : "pointer-cursor"; },
                        onPageClicked: function(e,originalEvent,type,page){
                            // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                            if ( $("#ComprometidoPaginador").attr("last") != page ) {
                                comunica("' . $this->pantalla . '",{
                                    metodo: "MuestraDatosPagina",
                                    mes: $("#page-tabs li").children(".active").attr("mes"),
                                    origen: "' . $this->origen . '",
                                    gur: $("#inputGur").val(),
                                    clc_id: $("#inputClc_id").val(),
                                    cmp_id: $("#inputCmp_id").val(),
                                    rfc_id: $("#inputRfc_id").val(),
                                    cmp_numOrden: $("#inputCmp_numOrden").val(),
                                    paginado: true,
                                    numpagina: page ,
                                    numreg: ' . LINEAS_POR_PAGINA . '
                                });
                                $("#ComprometidoPaginador").attr("last", page );
                            }
                        }
                    }
                    $("#ComprometidoPaginador").bootstrapPaginator(options);
                ');
        } else {
            $this->mixScript('$("#ComprometidoPaginador").html("")');
        }
        return json_encode($datos);
    }

    private function DameInformacionPagina()
    {

        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $clc_id = $this->getPrm('clc_id', '');
        $cmp_id = $this->getPrm('cmp_id', '');
        $rfc_id = $this->getPrm('rfc_id', '');
        $cmp_numOrden = '';
        if ($prmNumOrden = $this->getPrm('cmp_numOrden', null)){
            $numOrden = explode(",",str_replace(' ', '', (string) $prmNumOrden));
            foreach ($numOrden as $cmp) {
                $cmp_numOrden .= '"' . $cmp . '",';
            }
            $cmp_numOrden = substr($cmp_numOrden, 0, -1);
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
        $detalle = false;
        $nInicial = (($numpagina - 1) * LINEAS_POR_PAGINA);   // Como aqui cargamos la pagina inicialmente, es la primera
        $datos = vinculacionComprometido(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'C',
            $niveles,
            array(
                "gur" => $gur,
                "clc_id" => $clc_id,
                "cmp_id" => $cmp_id,
                "rfc_id" => $rfc_id,
                "cmp_numOrden" => $cmp_numOrden
            ),
            $paginado,
            $nInicial,
            $numreg
        );
        if ($paginado == true) {
            unset($datos[0]);
        }
        return json_encode($datos);
    }

    private function getTablaComprometido(): string
    {
        $tabla = new stdClass();
        $tabla->esCompacta = true;
        $tabla->inputFiltro = "buscar";
        $tabla->id = 'tbl-comprometido';
        $tabla->seleccionable = true;
        $tabla->cabeceraFija = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaComprometido';

        $tr = array();
        $th = new stdClass();
        $th->texto = 'CMP';
        $th->width = '90px';
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
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['cmp_id']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['rfc_id']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['cmp_numOrden'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['cmp_concepto'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['cmp_importe'];
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['estado'];
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaAutorizacion'];
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaCancelacion'];
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['polizaAutorizado'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaAutorizado'];
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['polizaCancelado'];
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['fechaCancelado'];
        $td->align = 'center';
        array_push($trbody, $td); 

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        return $this->wrPonTabla($tabla);
    }

    private function Documento()
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = ? AND ur_id = ?");
        $conexion->consultar([$this->gpo_id, $this->ur_id]);

        while ($dataRow = $conexion->extraer()) {
            $mesContable[$dataRow['msc_id']] = $dataRow;
        }
        
        $gpo_id_compromiso = substr($this->prm->gur, 0, 1);
        $ur_id_compromiso = substr($this->prm->gur, 1, 2);
        $clc_id_compromiso = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $cmp_id_compromiso = (isset($this->prm->cmp_id) ? $this->prm->cmp_id : '');

        $clc_array = array();

        $generaPolizaAutorizado = false;
        $generaPolizaCancelado = false;

        $fechaAutorizado = '';
        $fechaCancelado = '';

        $estadoMesCancelado = false;
        $existeFechaCan = true;
        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->icon = 'solid fa-circle-left me-2';
        $btnRegresa->color = 'gris';
        $btnRegresa->class = "me-1";

        $botones = $this->ponBoton($btnRegresa);
        $this->agregaTools($botones);

        $compromiso = getCompromiso(
            $gpo_id_compromiso,
            $ur_id_compromiso,
            $cmp_id_compromiso,
            $this->prm->rfc_id,
            $this->prm->cmp_numOrden,
            array("ejercicio" => APP_EJERCICIO, "mmc_id" => "C")
        );
        $html = '
                <legend style="border-bottom: 1px solid #e5e5e5; margin-bottom: 22px;">Análisis del compromiso a generar la póliza del comprometido</legend>
                <div class="px-3">
                    <fieldset class="col-md-12 col-xs-12">
                        <legend style="border-bottom: 1px solid #e5e5e5; margin-bottom: 22px;">Información del Compromiso</legend>
                        <table class="table table-bordered">
                            <tr class="success" style="background-color: #dff0d8 !important;">
                                <th>Proveedor</th>
                                <th>Referencia</th>
                                <th>Concepto</th>
                                <th>Percepción Presupuestal</th>
                                <th>Fecha</th>
                            </tr>
                            <tr>
                                <td>' . $compromiso->rfc_id . "-" . $compromiso->nombreProveedor . '</td>
                                <td>' . $compromiso->referencia . '</td>
                                <td>' . $compromiso->concepto . '</td>
                                <td width="10%" align="right"><span style="float:left">$</span>' . number_format(
            $compromiso->importe,
            2
        ) . '</td>
                                <td>' . $compromiso->fechaDocumento . '</td>
                            </tr>
                        </table>
                    </fieldset>
            ';

        //preguntamos el estado de los meses contables en funcion de las fechas del compromiso
        foreach ($compromiso->detalleComprometido as $key => $value) {
            if ($this->prm->agrupador == $value->agrupador) {
                if ($value->importe != 0) {
                    $generaPolizaAutorizado = true;
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
        }
        if (!isset($estadoMesCancelado) || $estadoMesCancelado == "") {
            $existeFechaCan = false;
        }
        //preguntamos la(s) CLC(s) del documento
        if ($compromiso->detalleEjercido) {
            foreach ($compromiso->detalleEjercido as $key => $value) {
                if ($value->clc_id != 0) {
                    if ($clc_id_compromiso != '') {
                        if ($clc_id_compromiso == $value->clc_id) {
                            $clc_array[$value->clc_id] = $value->clc_id;
                        }
                    } else {
                        $clc_array[$value->clc_id] = $value->clc_id;
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
                foreach ($clc_array as $clc_id) {
                    $clcDocumento = pintaClc(
                        $gpo_id_compromiso,
                        $ur_id_compromiso,
                        $clc_id,
                        array("ejercicio" => APP_EJERCICIO, "cmp_id" => $cmp_id_compromiso)
                    );
                    $html .= $clcDocumento->html;
                }
                $html .= '
                    </fieldset></br>
                ';
            }
        }
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
                                            "RE HACER LA POLIZA ' . (isset($value->poliza)? $value->poliza : '') . '",
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
                                                    cmp_id: ' . $this->prm->cmp_id . ',
                                                    rfc_id: "' . $this->prm->rfc_id . '",
                                                    cmp_numOrden: "' . $this->prm->cmp_numOrden . '",
                                                    cmpFiltro: ' . $this->prm->cmpFiltro . ',
                                                    gpo_id: "' . $value->gpo_id . '",
                                                    ur_id: "' . $value->ur_id . '",
                                                    ue_id: "' . $value->ue_id . '",
                                                    fecha: "' . $fechaAutorizado . '",
                                                    agrupador: "' . $this->prm->agrupador . '",
                                                    tmc_id: "A"
                                                });
                                            },
                                            undefined
                                        ).set("defaultFocus","cancel")
                                        .set("labels", {
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
                                                    cmp_id: ' . $this->prm->cmp_id . ',
                                                    rfc_id: "' . $this->prm->rfc_id . '",
                                                    cmp_numOrden: "' . $this->prm->cmp_numOrden . '",
                                                    cmpFiltro: ' . $this->prm->cmpFiltro . ',
                                                    gpo_id: "' . $value->gpo_id . '",
                                                    ur_id: "' . $value->ur_id . '",
                                                    ue_id: "' . $value->ue_id . '",
                                                    fecha: "' . $fechaCancelado . '",
                                                    agrupador: "' . $this->prm->agrupador . '",
                                                    tmc_id: "C"
                                                });
                                            },
                                            undefined
                                        ).set("defaultFocus","cancel")
                                        .set("labels", {
                                            ok: "Aceptar", cancel: "Cancelar"
                                        });
                                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                                        if (btnAceptar) {
                                            btnAceptar.classList.remove("btn-primary");
                                            btnAceptar.classList.add("btn-success");
                                        }
                                    });
                                ');
                        } elseif ($existeFechaCan == true) {
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
                                    seleccion: "' . (isset($this->prm->seleccion)
                        ? $this->prm->seleccion : "") . '",
                                    mes: ' . $this->prm->mes . ',
                                    gur: ' . $this->prm->gur . ',
                                    clc_id: "' . $this->prm->clc_id . '",
                                    clcFiltro: ' . $this->prm->clcFiltro . ',
                                    cmp_id: ' . $this->prm->cmp_id . ',
                                    rfc_id: "' . $this->prm->rfc_id . '",
                                    cmp_numOrden: "' . $this->prm->cmp_numOrden . '",
                                    cmpFiltro: ' . $this->prm->cmpFiltro . ',
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
                    $btnCancelar->icon = 'times me-2';
                    $btnCancelar->name = 'nuevo';
                    $btnCancelar->color = 'rojo';
                    $btnCancelar->text = 'Cancelar';
                    $btnCancelar->value = 'Cancelar';

                    $html .= '
                            <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                                <div id="capfecha">
                                    <legend>Mes del Comprometido esta cerrado, capture una nueva fecha</legend>
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
                                    mes: ' . $this->prm->mes . ',
                                    gur: ' . $this->prm->gur . ',
                                    clc_id: "' . $this->prm->clc_id . '",
                                    clcFiltro: ' . $this->prm->clcFiltro . ',
                                    cmp_id: ' . $this->prm->cmp_id . ',
                                    rfc_id: "' . $this->prm->rfc_id . '",
                                    cmp_numOrden: "' . $this->prm->cmp_numOrden . '",
                                    cmpFiltro: ' . $this->prm->cmpFiltro . ',
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
                                    cmp_id: ' . $this->prm->cmp_id . ',
                                    rfc_id: "' . $this->prm->rfc_id . '",
                                    cmp_numOrden: "' . $this->prm->cmp_numOrden . '",
                                    cmpFiltro: ' . $this->prm->cmpFiltro . ',
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
                    $btnCancelar->icon = 'times me-2';
                    $btnCancelar->name = 'nuevo';
                    $btnCancelar->color = 'rojo';
                    $btnCancelar->text = 'Cancelar';
                    $btnCancelar->value = 'Cancelar';

                    $html .= '
                            <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                                <div id="capfecha">
                                    <legend>Mes de cancelación del Comprometido esta cerrado, capture una nueva fecha</legend>
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
                                    cmp_id: ' . $this->prm->cmp_id . ',
                                    rfc_id: "' . $this->prm->rfc_id . '",
                                    cmp_numOrden: "' . $this->prm->cmp_numOrden . '",
                                    cmpFiltro: ' . $this->prm->cmpFiltro . ',
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
                        metodo: "main",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion
            : "") . '",
                        mes: ' . $this->prm->mes . ',
                        gur: ' . $this->prm->gur . ',
                        clc_id: "' . $this->prm->clc_id . '",
                        clcFiltro: ' . $this->prm->clcFiltro . ',
                        cmp_id: ' . $this->prm->cmp_id . ',
                        rfc_id: "' . $this->prm->rfc_id . '",
                        cmp_numOrden: "' . $this->prm->cmp_numOrden . '",
                        cmpFiltro: ' . $this->prm->cmpFiltro . '
                    });
                });
            ');
    }

    private function GeneraPoliza()
    {
        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
        $cmp_id_documento = $this->prm->cmp_id;
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

        $generaPolizaComprometido = new sp_Comprometido();
        $generaPolizaComprometido->setGpo_id($gpo_id_documento);
        $generaPolizaComprometido->setUr_id($ur_id_documento);
        $generaPolizaComprometido->setCmp_id($cmp_id_documento);
        $generaPolizaComprometido->setPma_fecha($fecha);
        $generaPolizaComprometido->setTmc_id($this->prm->tmc_id);
        $generaPolizaComprometido->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DEL COMPROMETIDO");
        $generaPolizaComprometido->setEjercicio(APP_EJERCICIO);
        $generaPolizaComprometido->setSis_id("SCG");


        $generaPolizaComprometido->execute(false, false);

        $bandera = $generaPolizaComprometido->getBandera();
        $mensajesp = $generaPolizaComprometido->getMensaje();

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
                        cmp_id: ' . $this->prm->cmp_id . ',
                        rfc_id: "' . $this->prm->rfc_id . '",
                        cmp_numOrden: "' . $this->prm->cmp_numOrden . '",
                        cmpFiltro: ' . $this->prm->cmpFiltro . ',
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
                        cmp_id: ' . $this->prm->cmp_id . ',
                        rfc_id: "' . $this->prm->rfc_id . '",
                        cmp_numOrden: "' . $this->prm->cmp_numOrden . '",
                        cmpFiltro: ' . $this->prm->cmpFiltro . ',
                        agrupador: "' . $this->prm->agrupador . '",
                        fecha: "' . $fecha . '",
                        tmc_id: "A"
                    });
                ');
        }
    }
}
//Permisos de acceso al controlador
if (isset($_SESSION[_S]['prm']['C']['5']['1']['1'])) {
    new ControladorComprometido();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
