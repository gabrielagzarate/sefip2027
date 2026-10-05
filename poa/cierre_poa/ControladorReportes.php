<?php

/**
 * Description of ControladorReportes
 *
 * @author ?
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 31-Jul-2020
 * Comment: Se agrega simple condicion a traves de $_SERVER["HTTP_HOST"] para mostrar todos los reportes en calidad solamente.
 * Objective: Listado de Reportes POA para la generación de sus respectivos reportes.
 * Regla de Negocio:
 * Esta pantalla pregunta por permisos de lectura, se basa en el nivel jerarquico del usuario
 *
 * */

session_start();
require_once __DIR__ . '/../../lib/WebResponse.php';
require_once __DIR__ . '/../../ent/UnidadResponsable.php';
require_once __DIR__ . '/../../srv/ClasificacionAdministrativaB.php';

class ControladorReportes extends WebResponse
{
    public UnidadResponsable $ur;
    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        //Instanciando la Ur
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();

        $this->mainReportes();

        $this->returnJson();
    } // fin __construct

    public function mainReportes()
    {
        $this->agregaTools('');

        $route = 'rep/doc/poa/cierre_poa/';

        // Se settea valor = 1 para liberarlo a produccion, el valor debería ser = 0
        $opcReporte = 1;

        $reportes = [
            ['reporte' => 'rep_poa01', 'codigo' => '01', 'nombre' => 'Identificación institucional'],
            ['reporte' => 'rep_poa02', 'codigo' => '02', 'nombre' => 'Planeación institucional'],
            ['reporte' => 'rep_poa03', 'codigo' => '03', 'nombre' => 'Matriz de indicadores para resultados'],
            ['reporte' => 'rep_poa04', 'codigo' => '04', 'nombre' => 'Estructura programática'], // reporte rep_poa19
            ['reporte' => 'rep_poa05', 'codigo' => '05', 'nombre' => 'Vinculación MIR - Estructura programática'],
            ['reporte' => 'rep_poa06', 'codigo' => '06', 'nombre' => 'Clasificación funcional'],
            ['reporte' => 'rep_poa07', 'codigo' => '07', 'nombre' => 'Clasificación programática'],
            ['reporte' => 'rep_poa08', 'codigo' => '08', 'nombre' => 'Resumen presupuestal por eje - tema - objetivo'],
            ['reporte' => 'rep_poa09', 'codigo' => '09', 'nombre' => 'Programa - Subprograma - UE - Proyecto - Actividad'], // reporte rep_poa02
            ['reporte' => 'rep_poa10', 'codigo' => '10', 'nombre' => 'Programa - Fuente de financiamiento'], // reporte rep_poa04
            ['reporte' => 'rep_poa11', 'codigo' => '11', 'nombre' => 'Resumen presupuestal por identificador de partida'], // reporte rep_poa05
            ['reporte' => 'rep_poa12', 'codigo' => '12', 'nombre' => 'UR - UE - Programa - Subprograma - Proyecto - Obra actividad - Partida'], // reporte rep_poa06
            ['reporte' => 'rep_poa13', 'codigo' => '13', 'nombre' => 'Inventario de plazas'], // reporte rep_poa07
            ['reporte' => 'rep_poa14', 'codigo' => '14', 'nombre' => 'Despliegue por relación laboral'], // reporte rep_poa08
            ['reporte' => 'rep_poa15', 'codigo' => '15', 'nombre' => 'Modalidad de contratación - Partida específica'], // reporte rep_poa09
            ['reporte' => 'rep_poa16', 'codigo' => '16', 'nombre' => 'Servicios de alimentación (comedores y víveres)'], // reporte rep_poa10
            ['reporte' => 'rep_poa17', 'codigo' => '17', 'nombre' => 'Servicios de telefonía (celular y fija)'], // reporte rep_poa11
            ['reporte' => 'rep_poa18', 'codigo' => '18', 'nombre' => 'Inventario de vehículos por UR'], // reporte rep_poa12
            ['reporte' => 'rep_poa19', 'codigo' => '19', 'nombre' => 'Servicios de arrendamiento (terrenos y edificios)'], // reporte rep_poa13
            ['reporte' => 'rep_poa20', 'codigo' => '20', 'nombre' => 'Seguros y fianzas de bienes inmuebles'], // reporte rep_poa14
            ['reporte' => 'rep_poa21', 'codigo' => '21', 'nombre' => 'Servicios de vigilancia'], // reporte rep_poa15
            ['reporte' => 'rep_poa22', 'codigo' => '22', 'nombre' => 'Otras partidas'], // reporte rep_poa16
            ['reporte' => 'rep_poa23', 'codigo' => '23', 'nombre' => 'UR - Capítulo - Identificador de partida - Partida'], // reporte rep_poa17
        ];

        $itemsHtml = '';
        foreach ($reportes as $item) {
            $nombreCompleto = 'POA ' . $item['codigo'] . ' - ' . mb_strtoupper($item['nombre'], 'UTF-8');
            $nombreEsc = htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8');
            $tituloEsc = htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8');
            $itemsHtml .= '
                <div class="reporte-poa-item list-group-item list-group-item-action border-0 border-bottom px-3 py-3"
                    data-buscar="' . htmlspecialchars(mb_strtolower($nombreCompleto, 'UTF-8'), ENT_QUOTES, 'UTF-8') . '">
                    <div class="d-flex align-items-center reporte-poa-fila">
                        <span class="reporte-poa-codigo">' . $item['codigo'] . '</span>
                        <div class="flex-grow-1 reporte-poa-nombre">
                            <div class="fw-semibold text-dark text-truncate" title="' . $tituloEsc . '">' . $nombreEsc . '</div>
                            <div class="small text-muted">POA ' . $item['codigo'] . '</div>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-reporte-pdf"
                            reporte="' . $item['reporte'] . '" data="' . $opcReporte . '"
                            title="Descargar PDF: ' . $tituloEsc . '">
                            <i class="fa-solid fa-file-pdf"></i>
                            <span>Descargar</span>
                        </button>
                    </div>
                </div>';
        }
        $totalReportes = count($reportes);
        $listaHtml = '<div class="list-group list-group-flush border rounded">' . $itemsHtml . '</div>';

        $this->mixHtml('
            <style>
                #lista-reportes-poa .reporte-poa-cabecera {
                    display: flex;
                    flex-wrap: wrap;
                    align-items: center;
                    justify-content: space-between;
                    gap: .75rem;
                    margin-bottom: 1rem;
                }
                #lista-reportes-poa .reporte-poa-buscar {
                    max-width: 320px;
                    width: 100%;
                }
                #lista-reportes-poa .reporte-poa-fila {
                    gap: 1rem;
                }
                #lista-reportes-poa .reporte-poa-codigo {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                    width: 2.5rem;
                    height: 2.5rem;
                    border-radius: .5rem;
                    background: #e8f1fb;
                    color: #1e5a96;
                    font-weight: 700;
                    font-size: .85rem;
                    letter-spacing: .02em;
                }
                #lista-reportes-poa .reporte-poa-nombre {
                    min-width: 0;
                }
                #lista-reportes-poa .reporte-poa-item:hover {
                    background: #f8fbff;
                }
                #lista-reportes-poa .reporte-poa-item:last-child {
                    border-bottom: 0 !important;
                }
                #lista-reportes-poa .btn-reporte-pdf {
                    flex-shrink: 0;
                    display: inline-flex;
                    align-items: center;
                    gap: .4rem;
                    border-radius: 2rem;
                    padding: .35rem .85rem;
                    font-weight: 600;
                }
                #lista-reportes-poa .reporte-poa-vacio {
                    display: none;
                }
                #lista-reportes-poa.sin-resultados .reporte-poa-vacio {
                    display: block;
                }
            </style>
            <div class="row btn_margin_left_right">
                <div class="col-12">
                    <div id="lista-reportes-poa" class="mb-3">
                        <div class="reporte-poa-cabecera">
                            <div>
                                <h5 class="m-0 fw-bold">Reportes POA</h5>
                                <div class="small text-muted">' . $totalReportes . ' reportes disponibles</div>
                            </div>
                            <div class="input-group reporte-poa-buscar">
                                <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                <input type="search" id="buscar-reporte-poa" class="form-control"
                                    placeholder="Buscar reporte..." autocomplete="off">
                            </div>
                        </div>
                        ' . $listaHtml . '
                        <div class="reporte-poa-vacio text-center text-muted py-5 border rounded">
                            <i class="fa-solid fa-magnifying-glass mb-2 d-block" style="font-size:1.5rem;"></i>
                            No se encontraron reportes con ese criterio.
                        </div>
                    </div>
                </div>
            </div>

            <div id="div_ues"></div>');

        $this->mixScript('
            $("#techos").show();
            $(".btn-reporte-pdf").click(function(e) {
                var data    = $(this).attr("data");
                var reporte = $(this).attr("reporte");
                var ruta = "' . $route . '"+reporte+".php";
                var form = $("<form>")
                    .attr("action", ruta)
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "opc").attr("value", data ));
                $("body").append(form);
                form.submit();
            });
            $("#buscar-reporte-poa").on("input", function() {
                var q = $.trim($(this).val()).toLowerCase();
                var visibles = 0;
                $(".reporte-poa-item").each(function() {
                    var ok = !q || (($(this).attr("data-buscar") || "").indexOf(q) !== -1);
                    $(this).toggle(ok);
                    if (ok) visibles++;
                });
                $("#lista-reportes-poa").toggleClass("sin-resultados", visibles === 0);
            });
        ');
    } // fin mainReportes
} // fin class

if (isset($_SESSION[_S]['prm']['A']['8']['3'])) { //cambiar cuando se use 2026 ['A']['6']['7']
    new ControladorReportes();
} else {
    WebResponse::sinPermisos();
}
