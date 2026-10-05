<?php

session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ObjetoGasto.php';

class ControladorReporteObraPublica extends WebResponse
{
    public function __construct() //Constructor
    {
        parent::__construct();
        $this->pantalla = 'scp/reportes/ControladorReporteObraPublica.php';
        switch ($this->metodo) {
            case 'tablaCapturarDatos':
                $this->tablaCapturarDatos();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }
    public function main()
    {
        $this->titulo('Reportes de Obra Pública');
        $this->agregaTools('');

        $reportes = [
            [
                'reporte' => 'rep/excel/scp/reportes/RepObrasV2.php',
                'nombre'  => 'Reporte Obra Pública por Clave Presupuestal',
                'clase'   => 'repObrasV2',
            ],
            [
                'reporte' => 'rep/excel/scp/reportes/RepContratistas.php',
                'nombre'  => 'Reporte de Contratistas',
                'clase'   => 'repContratistas',
            ],
            [
                'reporte' => 'rep/excel/scp/reportes/RepMetas.php',
                'nombre'  => 'Reporte de Metas',
                'clase'   => 'repMetas',
            ],
        ];

        if (isset($_SESSION[_S]['prm']['S']['4']['4']['121'])) {
            $reportes[] = [
                'reporte' => 'rep/excel/scp/reportes/RepObrasRembolso.php',
                'nombre'  => 'Reporte Obra Pública por Clave Presupuestal (reembolso)',
                'clase'   => 'repAncticipoObra',
            ];
        }

        $itemsHtml = '';
        foreach ($reportes as $item) {
            $nombreEsc = htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8');
            $itemsHtml .= '
                <div class="reporte-op-item list-group-item list-group-item-action border-0 border-bottom px-3 py-3"
                    data-buscar="' . htmlspecialchars(mb_strtolower($item['nombre'], 'UTF-8'), ENT_QUOTES, 'UTF-8') . '">
                    <div class="d-flex align-items-center reporte-op-fila">
                        <div class="flex-grow-1 reporte-op-nombre">
                            <div class="fw-semibold text-dark text-truncate" title="' . $nombreEsc . '">' . $nombreEsc . '</div>
                        </div>
                        <button type="button" class="btn btn-outline-success btn-sm excel ' . $item['clase'] . '"
                            id="' . $item['reporte'] . '"
                            title="Exportar Excel: ' . $nombreEsc . '">
                            <i class="fa-solid fa-file-excel"></i>
                            <span>Descargar</span>
                        </button>
                    </div>
                </div>';
        }
        $listaHtml = '<div class="list-group list-group-flush border rounded">' . $itemsHtml . '</div>';

        $this->mixHtml('
            <style>
                #lista-reportes-op .reporte-op-cabecera {
                    display: flex;
                    flex-wrap: wrap;
                    align-items: center;
                    justify-content: space-between;
                    gap: .75rem;
                    margin-bottom: 1rem;
                }
                #lista-reportes-op .reporte-op-buscar {
                    max-width: 320px;
                    width: 100%;
                }
                #lista-reportes-op .reporte-op-fila {
                    gap: 1rem;
                }
                #lista-reportes-op .reporte-op-nombre {
                    min-width: 0;
                }
                #lista-reportes-op .reporte-op-item:hover {
                    background: #f8fbff;
                }
                #lista-reportes-op .reporte-op-item:last-child {
                    border-bottom: 0 !important;
                }
                #lista-reportes-op .excel {
                    flex-shrink: 0;
                    display: inline-flex;
                    align-items: center;
                    gap: .4rem;
                    border-radius: 2rem;
                    padding: .35rem .85rem;
                    font-weight: 600;
                }
                #lista-reportes-op .reporte-op-vacio {
                    display: none;
                }
                #lista-reportes-op.sin-resultados .reporte-op-vacio {
                    display: block;
                }
            </style>
            <div class="row btn_margin_left_right">
                <div class="col-12">
                    <div id="lista-reportes-op" class="mb-3">
                        <div class="reporte-op-cabecera">
                            <div>
                                <h5 class="m-0 fw-bold">Reportes de Obra Pública</h5>
                            </div>
                            <div class="input-group reporte-op-buscar">
                                <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                <input type="search" id="buscar-reporte-op" class="form-control"
                                    placeholder="Buscar reporte..." autocomplete="off">
                            </div>
                        </div>
                        ' . $listaHtml . '
                        <div class="reporte-op-vacio text-center text-muted py-5 border rounded">
                            <i class="fa-solid fa-magnifying-glass mb-2 d-block" style="font-size:1.5rem;"></i>
                            No se encontraron reportes con ese criterio.
                        </div>
                    </div>
                </div>
            </div>');

        $this->mixScript('
                $(".excel").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"tablaCapturarDatos",
                        nomrep: this.id
                    });
                });

                $("#buscar-reporte-op").on("input", function() {
                    var q = $.trim($(this).val()).toLowerCase();
                    var visibles = 0;
                    $(".reporte-op-item").each(function() {
                        var ok = !q || (($(this).attr("data-buscar") || "").indexOf(q) !== -1);
                        $(this).toggle(ok);
                        if (ok) visibles++;
                    });
                    $("#lista-reportes-op").toggleClass("sin-resultados", visibles === 0);
                });

                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });
            ');
    }

    public function tablaCapturarDatos()
    {
        $programa = new StdClass();
        $programa->id = 'prg';
        $programa->label = 'Programa';
        $programa->maxlength = 3;
        $programa->placeholder = 'Programa';
        $programa->enDiv = true;
        $programa->class = 'clean';
        $programa->formato = 'entero';

        $subprograma = new StdClass();
        $subprograma->id = 'spr';
        $subprograma->label = 'Subprograma';
        $subprograma->maxlength = 2;
        $subprograma->placeholder = 'SubPrograma';
        $subprograma->enDiv = true;
        $subprograma->class = 'clean';
        $subprograma->formato = 'entero';

        $proyecto = new StdClass();
        $proyecto->id = 'py';
        $proyecto->label = 'Proyecto';
        $proyecto->maxlength = 3;
        $proyecto->placeholder = 'Proyecto';
        $proyecto->enDiv = true;
        $proyecto->class = 'clean';
        $proyecto->formato = 'entero';

        $actividad = new StdClass();
        $actividad->id = 'oa';
        $actividad->label = 'Obra Actividad';
        $actividad->maxlength = 3;
        $actividad->placeholder = 'Obra/Actividad';
        $actividad->enDiv = true;
        $actividad->class = 'clean';
        $actividad->formato = 'entero';

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'limpiar';
        $btnLimpiar->color = 'amarillo';
        $btnLimpiar->text = ' Limpiar';
        $btnLimpiar->value = ' Limpiar';
        $btnLimpiar->class = 'me-1';
        $btnLimpiar->icon = 'trash';

        $btnExportar = new stdClass();
        $btnExportar->id = 'expExcel';
        $btnExportar->color = 'verde';
        $btnExportar->text = ' Exportar';
        $btnExportar->value = ' Descargar';
        $btnExportar->form = 'filtros';
        $btnExportar->type = 'submit';
        $btnExportar->icon = 'file-excel';
        $btnExportar->class = 'me-1';

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'regresar';
        $btnRegresar->name = ' regresar';
        $btnRegresar->value = ' Regresar';
        $btnRegresar->icon = 'circle-left';
        $btnRegresar->color = 'gris';
        $btnRegresar->class = 'me-1';

        $this->agregaTools($this->ponBoton($btnRegresar) . (($this->prm->nomrep != 'rep/excel/ExportaRepObraCveFin.php' && $this->prm->nomrep != 'rep/excel/ExportaRepObraCveFin2.php') ? $this->ponBoton($btnLimpiar) : '') . $this->ponBoton($btnExportar));

        $campos = <<<HTML
                <div class="row">
                    <fieldset class="col-md-3 col-xs-6">
                        {$this->ponInput($programa)}
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-6">
                        {$this->ponInput($subprograma)}
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-6">
                        {$this->ponInput($proyecto)}
                    </fieldset>
                    <fieldset class="col-md-3 col-xs-6">
                        {$this->ponInput($actividad)}
                    </fieldset>
                </div>
        HTML;

        $valida = ($this->prm->nomrep != 'rep/excel/ExportaRepObraCveFin.php' && $this->prm->nomrep != 'rep/excel/ExportaRepObraCveFin2.php') ? $campos : '';
        $comboUR = <<<HTML
        <br>
                <div class="row">
                        <fieldset class="col-md-2 col-xs-6">
                            <input type="checkbox" id="todas" name="todas" value=0/> Todas las URs
                        </fieldset>
                    </div>

        HTML;
        $sesion = ($_SESSION[_S]['usr_id'] == 7 || ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 115 && $_SESSION[_S]['nvj_id'] >= 3)) ? $comboUR : '';

        $html = <<<HTML
                <form id="filtros" name="filtros" method="post" action="" target="_blank">
                    {$valida}
                    {$sesion}
                    <div class="row">
                        <fieldset class="col-md-2 col-xs-6">
                        </fieldset>
                        <fieldset class="col-md-2 col-xs-6">
                        </fieldset>
                        <fieldset class="col-md-4 col-xs-6">
                        </fieldset>
                        <fieldset class="col-md-6 col-xs-6">
                        </fieldset>
                    </div>
                </form>
        HTML;

        $this->mixHtml($html);

        $script = <<<JS
            $("#expExcel").click(function(){
                $("#filtros").attr("action", "{$this->prm->nomrep}");
                $("#filtros").submit();
                return false;
            });

            $("#limpiar").click(function(){
                $(".clean").val("");
                return false;
            });

            $("#regresar").click(function(){
                comunica( "{$this->pantalla}",
                {
                    metodo:"main"
                });
                return false;
            });

            $("#todas").click(function(){
                if($(this).is(":checked")){
                    $(this).val(1)
                }else if(!$(this).is(":checked")){
                    $(this).val(0)
                }
            });
        JS;

        $this->mixScript($script);
    }
}

if ($_SESSION[_S]['prm']['S']['4']['4']['1']) {
    new ControladorReporteObraPublica();
} else {
    WebResponse::sinPermisos();
}
