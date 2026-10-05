<?php
session_start();
require_once __DIR__ . '/../../lib/WebResponse.php';
require_once __DIR__ . '/../../srv/ClasificacionAdministrativaB.php';

class ControladorTechoFinancieroPoa extends WebResponse
{
    protected $clasAdmGpo;
    protected $clasAdmUr;

    public function __construct()
    {
        parent::__construct();

        $this->clasAdmGpo = (isset($_SESSION[_S]['clasAdm-gpo']) && $_SESSION[_S]['clasAdm-gpo'] != '')
            ? $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->clasAdmUr = (isset($_SESSION[_S]['clasAdm-ur']) && $_SESSION[_S]['clasAdm-ur'] != '')
            ? $_SESSION[_S]['clasAdm-ur'] : '';

        switch ($this->metodo) {
            case 'opcion0':
                $this->identificadorDeTechos();
                break;
            case 'opcion1':
                $this->techosPorClasificadorFinanciamiento();
                break;
            case 'opcion2':
                $this->techosPorClasificadorFinanciamientoClaseGasto();
                break;
            case 'opcion2Detalle':
                $this->techosPorClasificadorFinanciamientoClaseGastoDetalle();
                break;
            case 'opcion3':
                $this->clasificadorFinanciamientoClaseGasto();
                break;
            case 'opcion4':
                $this->claseGastoPartida();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    /* -------------------------------------------------------------------------
        Pantalla principal: muestra las 5 tarjetas con las opciones.
    ------------------------------------------------------------------------- */
    private function main()
    {
        $this->agregaTools('
            <button id="btnExcelComparativo" type="button"
                class="btn btn-outline-success me-1"
                title="Exportar Comparativo Presupuestario (Ur-Gf-Clg-Céd-Partida) a Excel">
                <i class="fa-solid fa-file-excel me-2"></i>Excel
            </button>
        ');

        $this->mixScript('$("#techos").hide(); $("#muestraTechos").hide();');

        $this->mixScript(
            ClasificacionAdministrativa::onChangeUr('
                comunica("' . $this->pantalla . '", {"metodo":""});
            ')
        );

        $cards = [];

        $c = new stdClass();
        $c->id        = 'cardTfp_0';
        $c->titulo    = 'Opción 0';
        $c->icon      = 'fa-solid fa-bullseye';
        $c->iconColor = '#5c6bc0';
        $c->label     = 'Identificador de Techos';
        $c->title     = 'Gráfica de Techos por Clase de Gasto';
        $cards[] = $c;

        $c = new stdClass();
        $c->id        = 'cardTfp_1';
        $c->titulo    = 'Opción 1';
        $c->icon      = 'fa-solid fa-coins';
        $c->iconColor = '#2e7d32';
        $c->label     = 'Techos por Clasif. Financiamiento';
        $c->title     = 'Gráfica de Techos por Tipo de Recurso (Gran Fuente)';
        $cards[] = $c;

        $c = new stdClass();
        $c->id        = 'cardTfp_2';
        $c->titulo    = 'Opción 2';
        $c->icon      = 'fa-solid fa-chart-pie';
        $c->iconColor = '#1565c0';
        $c->label     = 'Techos por Clasif. - Clase Gasto';
        $c->title     = 'Gráfica de Techos por Clasificador de Financiamiento - Clase de Gasto';
        $cards[] = $c;

        $c = new stdClass();
        $c->id        = 'cardTfp_3';
        $c->titulo    = 'Opción 3';
        $c->icon      = 'fa-solid fa-table-cells';
        $c->iconColor = '#8E418D';
        $c->label     = 'Clasif. Financ. - Clase Gasto';
        $c->title     = 'Tabla Clasificador de Financiamiento - Clase de Gasto';
        $cards[] = $c;

        $c = new stdClass();
        $c->id        = 'cardTfp_4';
        $c->titulo    = 'Opción 4';
        $c->icon      = 'fa-solid fa-list-check';
        $c->iconColor = '#00897b';
        $c->label     = 'Clase de Gasto - Partida';
        $c->title     = 'Tabla Clase de Gasto - Partida';
        $cards[] = $c;

        $this->mixHtml(
            $this->ponCards($cards) .
            '<div id="contenedorOpcion" class="mt-3"></div>'
        );

        $this->mixScript('
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });

            $("#homeMenu").click( function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });

            $(".card_opcion[id^=\'cardTfp_\']").click(function(){
                var idx    = $(this).attr("id").split("_")[1];
                var metodo = "opcion" + idx;

                $(".card_opcion[id^=\'cardTfp_\']").removeClass("active");
                $(this).addClass("active");

                $("#contenedorOpcion").html(
                    "<div class=\'text-center py-5 text-muted\'><i class=\'fa fa-spinner fa-spin me-2\'></i>Cargando...</div>"
                );
                comunica("' . $this->pantalla . '", { metodo: metodo });
            });

            $("#btnExcelComparativo").off("click").on("click", function(){
                var form = $("<form>")
                    .attr("action", "rep/excel/poa/presupuestacion/exp_ComparativoPresupuestario.php")
                    .attr("method", "post")
                    .attr("target", "_blank");
                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');
    }

    /* =========================================================================
        OPCIÓN 0 - Identificador de Techos
    ========================================================================= */
    private function identificadorDeTechos()
    {
        $this->setContenedor('contenedorOpcion');

        if (!$this->validaUr()) {
            return;
        }

        $prm = ['adm_gpo' => $this->clasAdmGpo, 'adm_ur' => $this->clasAdmUr];
        $this->setSql('
            SELECT clg_id, clg_nombre, SUM(tec_techoPresupuestal) AS techo,
                IF(ISNULL(costeo), 0, costeo) AS costeo,
                SUM(tec_techoPresupuestal) - IF(ISNULL(costeo), 0, costeo) AS diferencia
            FROM cat_Techos
                INNER JOIN cat_ClaseGasto USING (clg_id)
                LEFT JOIN (
                    SELECT gpo_id, ur_id, clg_id, SUM(pre_original) AS costeo
                    FROM presupuesto
                        INNER JOIN presupuestoOriginal USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        INNER JOIN cat_PartidaEspecifica USING (pg_id, pe_id)
                    WHERE gpo_id = :adm_gpo AND ur_id = :adm_ur
                    GROUP BY clg_id
                ) AS pres USING (gpo_id, ur_id, clg_id)
            WHERE gpo_id = :adm_gpo
                AND ur_id  = :adm_ur
                AND tec_techoPresupuestal > 0
            GROUP BY clg_id;
        ');

        $this->conectar();
        $this->consultar($prm);
        $this->desconectar();

        $this->pintaChartTechos('Identificador de Techos - Clase de Gasto', 'clg_id', 'clg_nombre');
    }

    /* =========================================================================
        OPCIÓN 1 - Techos por Clasificador de Financiamiento (Tipo de Recurso)
    ========================================================================= */
    private function techosPorClasificadorFinanciamiento()
    {
        $this->setContenedor('contenedorOpcion');

        if (!$this->validaUr()) {
            return;
        }

        $prm = ['adm_gpo' => $this->clasAdmGpo, 'adm_ur' => $this->clasAdmUr];
        $this->setSql('
            SELECT ter_id, ter_nombre, SUM(tec_techoPresupuestal) AS techo,
                IF(ISNULL(costeo), 0, costeo) AS costeo,
                SUM(tec_techoPresupuestal) - IF(ISNULL(costeo), 0, costeo) AS diferencia
            FROM cat_Techos
                INNER JOIN ' . BD_SINPRES . '.cat_TipoRecurso USING (ter_id)
                LEFT JOIN (
                    SELECT gpo_id, ur_id, ter_id, SUM(pre_original) AS costeo
                    FROM presupuesto
                        INNER JOIN presupuestoOriginal USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so USING (cff_id, fte_id, sft_id, ori_id, sor_id)
                    WHERE gpo_id = :adm_gpo AND ur_id = :adm_ur
                    GROUP BY ter_id
                ) AS pres USING (gpo_id, ur_id, ter_id)
            WHERE gpo_id = :adm_gpo
                AND ur_id  = :adm_ur
                AND tec_techoPresupuestal > 0
            GROUP BY ter_id;
        ');

        $this->conectar();
        $this->consultar($prm);
        $this->desconectar();

        $this->pintaChartTechos('Techos por Clasificador de Financiamiento', 'ter_id', 'ter_nombre');
    }

    /* =========================================================================
        OPCIÓN 2 - Techos por Clasif. Financiamiento - Clase de Gasto
    ========================================================================= */
    private function techosPorClasificadorFinanciamientoClaseGasto()
    {
        $this->setContenedor('contenedorOpcion');

        if (!$this->validaUr()) {
            return;
        }

        $prm = ['adm_gpo' => $this->clasAdmGpo, 'adm_ur' => $this->clasAdmUr];
        $this->setSql('
            SELECT DISTINCT ter_id, ter_nombre
            FROM cat_Techos
                INNER JOIN ' . BD_SINPRES . '.cat_TipoRecurso USING (ter_id)
            WHERE gpo_id = :adm_gpo
                AND ur_id  = :adm_ur
                AND tec_techoPresupuestal > 0;
        ');
        $this->conectar();
        $this->consultar($prm);

        $botones = '';
        $primer  = '';
        while ($row = $this->extraer()) {
            $esActivo = ($primer == '');
            if ($primer == '') {
                $primer = $row['ter_id'];
            }
            $clase = $esActivo ? 'btn btn-primary active' : 'btn btn-outline-primary';
            $botones .= '
                <button type="button"
                    class="' . $clase . ' tfp_ter"
                    data-ter-id="' . $row['ter_id'] . '"
                    title="' . htmlspecialchars($row['ter_nombre']) . '">
                    ' . $row['ter_id'] . ' - ' . $row['ter_nombre'] . '
                </button>
            ';
        }
        $this->desconectar();

        if ($primer == '') {
            $this->mixHtml('
                <div class="alert alert-warning" role="alert">
                    No existen techos registrados para la Unidad Responsable seleccionada.
                </div>
            ');
            return;
        }

        $this->mixHtml('
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="m-0 fw-bold">Tipo de Recurso</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 mb-3" role="group" aria-label="Tipo de Recurso" id="tfp_ter_grp">
                        ' . $botones . '
                    </div>
                    <div id="contenedorOpcion2Chart" style="min-width:310px; height:380px;"></div>
                </div>
            </div>
        ');

        $this->mixScript('
            $("#tfp_ter_grp").on("click", ".tfp_ter", function(){
                $("#tfp_ter_grp .tfp_ter")
                    .removeClass("active btn-primary")
                    .addClass("btn-outline-primary");
                $(this)
                    .addClass("active btn-primary")
                    .removeClass("btn-outline-primary");
                comunica("' . $this->pantalla . '", {
                    metodo: "opcion2Detalle",
                    ter_id: $(this).data("ter-id")
                });
            });
            $("#tfp_ter_grp .tfp_ter.active").first().trigger("click");
        ');
    }

    private function techosPorClasificadorFinanciamientoClaseGastoDetalle()
    {
        $this->setContenedor('contenedorOpcion2Chart');

        $prm = [
            'adm_gpo' => $this->clasAdmGpo,
            'adm_ur'  => $this->clasAdmUr,
            'ter_id'  => $this->prm->ter_id,
        ];

        $this->setSql('
            SELECT clg_id, clg_nombre, SUM(tec_techoPresupuestal) AS techo,
                IF(ISNULL(costeo), 0, costeo) AS costeo,
                SUM(tec_techoPresupuestal) - IF(ISNULL(costeo), 0, costeo) AS diferencia
            FROM cat_Techos
                INNER JOIN cat_ClaseGasto USING (clg_id)
                LEFT JOIN (
                    SELECT gpo_id, ur_id, clg_id, SUM(pre_original) AS costeo
                    FROM presupuesto
                        INNER JOIN presupuestoOriginal USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        INNER JOIN cat_PartidaEspecifica USING (pg_id, pe_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so USING (cff_id, fte_id, sft_id, ori_id, sor_id)
                    WHERE gpo_id = :adm_gpo
                        AND ur_id  = :adm_ur
                        AND ter_id = :ter_id
                    GROUP BY clg_id
                ) AS pres USING (gpo_id, ur_id, clg_id)
            WHERE gpo_id = :adm_gpo
                AND ur_id  = :adm_ur
                AND ter_id = :ter_id
                AND tec_techoPresupuestal > 0
            GROUP BY clg_id;
        ');

        $this->conectar();
        $this->consultar($prm);
        $this->desconectar();

        $this->pintaChartTechos(
            'Techos - Clase de Gasto (' . $this->prm->ter_id . ')',
            'clg_id',
            'clg_nombre',
            'contenedorOpcion2Chart'
        );
    }

    /* =========================================================================
        OPCIÓN 3 - Clasificador de Financiamiento - Clase de Gasto
    ========================================================================= */
    private function clasificadorFinanciamientoClaseGasto()
    {
        $this->setContenedor('contenedorOpcion');

        if (!$this->validaUr()) {
            return;
        }

        $prm = ['adm_gpo' => $this->clasAdmGpo, 'adm_ur' => $this->clasAdmUr];
        $sql = '
            SELECT ter_id, clg_id, clg_nombre, SUM(techo) AS techo, SUM(costeo) AS costeo
            FROM (
                SELECT ter_id, clg_id, SUM(tec_techoPresupuestal) AS techo, 0 AS costeo
                FROM cat_Techos
                WHERE gpo_id = :adm_gpo
                    AND ur_id  = :adm_ur
                    AND tec_techoPresupuestal <> 0
                GROUP BY ter_id, clg_id
                UNION ALL
                SELECT ter_id, clg_id, 0 AS techo, SUM(pre_original) AS costeo
                FROM presupuesto
                    INNER JOIN presupuestoOriginal USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                    INNER JOIN cat_PartidaEspecifica USING (pg_id, pe_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so USING (cff_id, fte_id, sft_id, ori_id, sor_id)
                WHERE gpo_id = :adm_gpo
                    AND ur_id  = :adm_ur
                GROUP BY ter_id, clg_id
            ) AS datos
                INNER JOIN cat_ClaseGasto USING (clg_id)
            GROUP BY ter_id, clg_id
            ORDER BY ter_id, clg_id;
        ';

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($prm);

        $html = '
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="m-0 fw-bold">3 - Clasificador de Financiamiento - Clase de Gasto</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" colspan="5">CLASIFICADOR DE FINANCIAMIENTO - CLASE DE GASTO</th>
                                </tr>
                                <tr>
                                    <th class="text-center" style="width:8%">ID GASTO</th>
                                    <th class="text-center">NOMBRE</th>
                                    <th class="text-center" style="width:18%">TECHO</th>
                                    <th class="text-center" style="width:18%">COSTEO</th>
                                    <th class="text-center" style="width:18%">DIFERENCIA</th>
                                </tr>
                            </thead>
                            <tbody>
        ';

        $ter_id      = '';
        $ter_nombre  = '';
        $paso        = false;
        $tot_ter     = 0;
        $tot_tercos  = 0;
        $tot_gral    = 0;
        $tot_gralcos = 0;
        $hayDatos    = false;

        while ($row = $this->extraer()) {
            $hayDatos = true;

            if ($ter_id != $row['ter_id']) {
                if ($paso) {
                    $html .= $this->renderSubtotal($ter_nombre, $tot_ter, $tot_tercos);
                    $tot_ter    = 0;
                    $tot_tercos = 0;
                }

                $ter_nombre = ($row['ter_id'] == 'A') ? 'ESTATAL' : 'FEDERAL';
                $html .= '
                    <tr class="table-secondary">
                        <th colspan="5">' . $ter_nombre . '</th>
                    </tr>
                ';

                $ter_id = $row['ter_id'];
                $paso   = true;
            }

            $diferencia = $row['techo'] - $row['costeo'];
            $html .= '
                <tr>
                    <td class="text-center">' . $row['clg_id'] . '</td>
                    <td>' . $row['clg_nombre'] . '</td>
                    <td class="text-end">' . number_format($row['techo'], 2) . '</td>
                    <td class="text-end">' . number_format($row['costeo'], 2) . '</td>
                    <td class="text-end">' . number_format($diferencia, 2) . '</td>
                </tr>
            ';

            $tot_ter     += $row['techo'];
            $tot_tercos  += $row['costeo'];
            $tot_gral    += $row['techo'];
            $tot_gralcos += $row['costeo'];
        }
        $this->desconectar();

        if ($paso) {
            $html .= $this->renderSubtotal($ter_nombre, $tot_ter, $tot_tercos);
        }

        if ($hayDatos) {
            $html .= '
                <tr class="table-primary fw-bold">
                    <td colspan="2" class="text-end">TOTAL GENERAL</td>
                    <td class="text-end">' . number_format($tot_gral, 2) . '</td>
                    <td class="text-end">' . number_format($tot_gralcos, 2) . '</td>
                    <td class="text-end">' . number_format($tot_gral - $tot_gralcos, 2) . '</td>
                </tr>
            ';
        } else {
            $html .= '
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">
                        No existen techos registrados para la Unidad Responsable seleccionada.
                    </td>
                </tr>
            ';
        }

        $html .= '
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        ';

        $this->mixHtml($html);
    }

    /* =========================================================================
        OPCIÓN 4 - Clase de Gasto - Partida (tabla plana)
    ========================================================================= */
    private function claseGastoPartida()
    {
        $this->setContenedor('contenedorOpcion');

        if (!$this->validaUr()) {
            return;
        }

        $prm = ['adm_gpo' => $this->clasAdmGpo, 'adm_ur' => $this->clasAdmUr];
        $this->setSql('
            SELECT clg_id, clg_nombre, pg_id, pe_id, pe_nombre, ter_id, SUM(pre_original) AS original
            FROM presupuesto
                INNER JOIN presupuestoOriginal USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                INNER JOIN cat_PartidaEspecifica USING (pg_id, pe_id)
                INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so USING (cff_id, fte_id, sft_id, ori_id, sor_id)
                INNER JOIN cat_ClaseGasto USING (clg_id)
            WHERE gpo_id = :adm_gpo
                AND ur_id  = :adm_ur
            GROUP BY ter_id, clg_id, pg_id, pe_id
            HAVING original <> 0
            ORDER BY ter_id, clg_id, pg_id, pe_id;
        ');

        $this->conectar();
        $this->consultar($prm);

        $html = '
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="m-0 fw-bold">4 - Clase de Gasto - Partida</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" colspan="5">CLASE DE GASTO - PARTIDA</th>
                                </tr>
                                <tr>
                                    <th class="text-center" style="width:10%">CLASE DE GASTO</th>
                                    <th class="text-center">NOMBRE CLASE DE GASTO</th>
                                    <th class="text-center" style="width:10%">PARTIDA</th>
                                    <th class="text-center">NOMBRE PARTIDA</th>
                                    <th class="text-center" style="width:18%">MONTO PARTIDA</th>
                                </tr>
                            </thead>
                            <tbody>
        ';

        $hayDatos = false;
        $total    = 0;
        while ($row = $this->extraer()) {
            $hayDatos = true;
            $total   += $row['original'];
            $html .= '
                <tr>
                    <td class="text-center">' . $row['clg_id'] . '</td>
                    <td>' . $row['clg_nombre'] . '</td>
                    <td class="text-center">' . $row['pg_id'] . ' - ' . $row['pe_id'] . '</td>
                    <td>' . $row['pe_nombre'] . '</td>
                    <td class="text-end">' . number_format($row['original'], 2) . '</td>
                </tr>
            ';
        }
        $this->desconectar();

        if ($hayDatos) {
            $html .= '
                <tr class="table-primary fw-bold">
                    <td colspan="4" class="text-end">TOTAL GENERAL</td>
                    <td class="text-end">' . number_format($total, 2) . '</td>
                </tr>
            ';
        } else {
            $html .= '
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">
                        No existen partidas costeadas para la Unidad Responsable seleccionada.
                    </td>
                </tr>
            ';
        }

        $html .= '
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        ';

        $this->mixHtml($html);
    }

    /* =========================================================================
        Helpers
    ========================================================================= */
    private function validaUr()
    {
        if (empty($this->clasAdmGpo) || empty($this->clasAdmUr)) {
            $this->mixHtml('
                <div class="alert alert-warning" role="alert">
                    Seleccione una Unidad Responsable para realizar la consulta.
                </div>
            ');
            return false;
        }
        return true;
    }

    private function renderSubtotal($ter_nombre, $tot_ter, $tot_tercos)
    {
        return '
            <tr class="table-info fw-bold">
                <td colspan="2" class="text-end">TOTAL ' . $ter_nombre . '</td>
                <td class="text-end">' . number_format($tot_ter, 2) . '</td>
                <td class="text-end">' . number_format($tot_tercos, 2) . '</td>
                <td class="text-end">' . number_format($tot_ter - $tot_tercos, 2) . '</td>
            </tr>
        ';
    }

    private function pintaChartTechos($titulo, $idCampo, $nombreCampo, $contenedorChart = null)
    {
        $categoria  = [];
        $costeado   = [];
        $porCostear = [];
        $min        = 0;

        while ($row = $this->extraer()) {
            $diferencia    = floatval($row['diferencia']);
            $montoCosteado = floatval($row['costeo']);

            $categoria[] = $row[$idCampo] . ' - ' . $row[$nombreCampo];

            if ($diferencia < 0) {
                $montoCosteado = $montoCosteado * -1;
                $diferencia    = 0;
                $min = ($min > $montoCosteado) ? $montoCosteado : $min;
            }
            $costeado[]   = $montoCosteado;
            $porCostear[] = $diferencia;
        }

        if (empty($categoria)) {
            $this->mixHtml('
                <div class="alert alert-warning" role="alert">
                    No existen datos para graficar con los filtros actuales.
                </div>
            ');
            return;
        }

        $chartContainer = $contenedorChart ?? 'chartTechosOp';
        if (is_null($contenedorChart)) {
            $this->mixHtml('
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="m-0 fw-bold">' . $titulo . '</h6>
                    </div>
                    <div class="card-body">
                        <div id="' . $chartContainer . '" style="min-width:310px; height:420px;"></div>
                    </div>
                </div>
            ');
        }

        $this->mixScript("
            $(function () {
                $('#" . $chartContainer . "').highcharts({
                    chart:  { type: 'bar', spacingRight: 80 },
                    title:  { text: '" . addslashes($titulo) . "' },
                    xAxis:  { categories: " . json_encode($categoria) . " },
                    yAxis:  {
                        min: " . $min . ",
                        title: { text: 'Importe' },
                        stackLabels: {
                            enabled: true,
                            style: {
                                fontWeight: 'bold',
                                color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                            }
                        }
                    },
                    legend: { backgroundColor: '#FFFFFF', reversed: true },
                    plotOptions: { series: { stacking: 'normal' } },
                    series: [
                        { name: 'Por Costear', data: " . json_encode($porCostear) . ", color: '#BDBA07' },
                        { name: 'Costeado',    data: " . json_encode($costeado)   . ", color: '#088A08' }
                    ]
                });
            });
        ");
    }
}

if (isset($_SESSION[_S]['prm']['A']['3']['10']['1'])) {
    new ControladorTechoFinancieroPoa();
} else {
    WebResponse::sinPermisos();
}
