<?php
session_start();

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/CatalogoProgramaticoFinanciamiento.php';
require_once dirname(__FILE__) . '/../../lib/funciones.php';

class ControladorAjuste extends WebResponse
{
    private $ur;
    private $cierre;
    private $estadoCosteo;
    private $escritura;
    private $lectura;

    public function __construct()
    {
        parent::__construct();
        // Instanciando la Ur para verificar lo del cierre del poa
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();
        $this->cierre = $this->ur->getEur_id();
        $this->estadoCosteo = $this->ur->getUr_estadoCosteo();

        // Asignación de permisos de lectura y escritura
        if (isset($_SESSION[_S]['prm']['M']['4']['3'][TAREA_ESCRITURA]) && ($_SESSION[_S]['nvj_id'] >= 2) && ($this->cierre >= 5 && $this->estadoCosteo == 2)) {
            $this->escritura = true;
        } else {
            $this->escritura = false;
        }

        if (isset($_SESSION[_S]['prm']['M']['4']['3'][TAREA_LECTURA])) {
            $this->lectura = true;
        } else {
            $this->lectura = false;
        }

        switch ($this->metodo) {
            case 'llenaFiltros':
                $this->llenaFiltros();
                break;
            case 'guardaAutorizado':
                $this->guardaAutorizado();
                break;
            case 'cambioClg':
                $this->cargaPg();
                $this->llenaTablaPresupuesto();
                break;
            case 'cambioPg':
                $this->cargaPe();
                $this->llenaTablaPresupuesto();
                break;
            case 'llenaTablaPresupuesto':
                $this->llenaTablaPresupuesto();
                break;
            default:
                $this->main();
                break;
        }

        $this->returnJson();
    }

    private function main()
    {
        $this->mixScript(
            ClasificacionAdministrativa::onChangeUr('
                comunica("' . $this->pantalla . '", {"metodo":""} );
            ')
        );
        // div contenedor y estilo para mostrar la modal de clave de financiamiento
        $this->mixHtml('<div id="contenedorCFModal"></div>');
        $this->mixStyle('
            legend{font-size:14px!important;margin-bottom:3px!important; font-weight:bold;}
            .modal-header{padding:8px!important; background: #B6C2C9; color:black;}
            .modal-footer{padding:10px 20px!important}
            .close { color:black; opacity: 0.8;};
        ');
        $this->mixHtml('<span id="mensajeCosteo">');
        // Si el costeo NO está cerrado, no le permitimos modificar nada
        if ($this->cierre < 5 && $this->estadoCosteo != 2) {
            $this->mixHtml('
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                    <strong>El costeo de la Unidad Responsable NO ha sido cerrado</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            ');
            $this->escritura = false;
        }
        $this->mixHtml('</span>');

        // Para dibujar la modal la clasificación administrativa y clave de financiamiento
        $this->mixHtml(DibujaPantallaBuscarProgramaFinanciamientoHTML());
        $this->mixScript(DibujaPantallaBuscarProgramaFinanciamientoJS());

        $this->mixScript('
            var progID = "encProg";
            var progNom = "nomProg";
            var cveFID = "encCveFin";
            var cveFNom = "nomCveFin";
            var progAnterior = "";
            setTimeout( function() { $( "#encProg" ).focus() }, 100 );
        ');

        $valorEpg = '';
        $nombreEpg = '';
        $valorCF = '';
        $nombreCF = '';

        // agregar mixscript para la tecla del F1 y F2
        $html = '';
        $html .= CatalogoProgramaticoFinanciamiento::ponF1();
        $this->mixHtml($html);

        $clasAdmin = new stdClass();
        $clasAdmin->required = true;
        $clasAdmin->change = 'comunica("' . $this->pantalla . '",{ metodo:"llenaFiltros" });';
        $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($clasAdmin) . '
            ' . ClasificacionAdministrativa::onChangeUr('') . '
            var progAnterior = $("#encProg").val();
                $("#encProg").blur(function() {
                    if (progAnterior != $(this).val()) {
                        comunica("' . $this->pantalla . '", {
                            metodo: "llenaFiltros",
                            prog: $(this).val(),
                            encProg: $(this).attr("enc"),
                            cveFin: $("#encCveFin").val(),
                            encCveFin: $("#encCveFin").attr("enc"),
                            nomFin: $("#nomCveFin").val()
                        });
                        progAnterior = $(this).val();
                    }
                });

            var ultimoValEncP = $("#encProg").val();
            setInterval(function() {
                var valActual = $("#encProg").val();
                if (valActual !== ultimoValEncP) {
                    ultimoValEncP = valActual;
                    $("#encProg").trigger("blur");
                }
            }, 200);

            if ($("#combour").attr("disabled") != undefined) {
                $("#combour").attr("disabled", false);
                $("#combour").selectpicker("refresh");
            }
        ');

        $combo = new stdClass();
        $combo->id = 'clg_id';
        $combo->label = 'Clase del gasto';
        $combo->change = 'comunica("' . $this->pantalla . '",{ metodo:"cambioClg", clg_id:$(this).val() });';
        $combo->sql = 'SELECT cg.clg_id, cg.clg_nombre FROM cat_ClaseGasto AS cg;';
        $c_gto = $this->templateCombo($combo);

        $combo = new stdClass();
        $combo->id = 'gral_id';
        $combo->label = 'Partida Genérica';
        $combo->change = 'comunica("' . $this->pantalla . '",{ metodo:"cambioPg", pg_id:$(this).val() });';
        $combo->sql = 'SELECT pg.pg_id,pg.pg_nombre FROM cat_PartidaGenerica AS pg;';
        $combo->enDiv = true;
        $pg = $this->templateCombo($combo);

        $combo = new stdClass();
        $combo->id = 'esp_id';
        $combo->label = 'Partida Específica';
        $combo->change = '
            comunica("' . $this->pantalla . '",{
                metodo: "llenaTablaPresupuesto",
                clg_id: $("#clg_id").val(),
                pg_id: $("#gral_id").val(),
                pe_id: $("#esp_id").val()
            });
        ';
        $pe = $this->templateCombo($combo);

        $botonRegresar = new stdClass();
        $botonRegresar->color = 'gris';
        $botonRegresar->title = 'Regresar';
        $botonRegresar->icon = 'fa-circle-left me-2';
        $botonRegresar->class = 'btn btn-outline-secondary me-1';
        $botonRegresar->value = 'Regresar';
        $botonRegresar->id = 'btnRegresar';
        $botonRegresar->click = '
            $("nav").menusinpres("clickMenu");
            // Para que cuando entre y salga de la pantalla donde pide los encuadres CA y CF no salga vacía la segunda vez
            $("div[aria-describedby=cargaDatosProgFin]").html("");
        ';

        $boton = new stdClass();
        $boton->title = 'Agregar Nueva Clave';
        $boton->icon = 'fa-plus me-2';
        $boton->class = 'me-1';
        $boton->color = 'azul';
        $boton->value = 'Nueva';
        $boton->id = 'newObjGto';
        $boton->click = '$("div[aria-describedby=cargaDatosProgFin]").html(""); comunica("srv/ControladorAltaClave.php",{metodo:"desdeAjuste"});';

        $botones = '
            <button id="printAjuste" type="button" class="btn btn-outline-danger me-1" title="Imprimir ajustes">
                <i class="fa-solid fa-file-pdf me-1"></i> PDF
            </button>
            <button id="printAjusteExcel" type="button" class="btn btn-outline-success me-1" title="Imprimir ajustes en excel">
                <i class="fa-solid fa-file-excel"></i> Excel
            </button>';
        $this->agregaTools($this->ponBoton($boton) . $botones);

        $boton = new stdClass();
        $boton->color = 'azul'; // No toma el color
        $boton->title = 'Consultar';
        $boton->icon = 'search me-1';
        $boton->class = 'mb-4';

        $boton->value = 'Consultar';
        $boton->id = 'buscar';
        $boton->click = '
            comunica("' . $this->pantalla . '",{
                metodo: "llenaTablaPresupuesto",
                clg_id: $("#clg_id").val(),
                pg_id: $("#gral_id").val(),
                pe_id: $("#esp_id").val()
            });
        ';

        $filtros = '
            <div class="row">
                <fieldset class="col-md-12 col-xs-12">
                    <legend>Filtros</legend>
                    <div class="row mb-4">
                    <fieldset class="col-md-4 col-xs-4">
                        ' . $c_gto . '
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-4">
                        ' . $pg . '
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-4">
                        ' . $pe . '
                    </fieldset>
                    </div>
                </fieldset>
                <fieldset class="col-md-12 col-xs-12">
                    <fieldset class="col-md-3 col-xs-3">
                        ' . $this->ponBoton($boton) . '
                    </fieldset>
                </fieldset>
            </div>
        ';

        $scriptFiltros = $this->getScript();
        $this->setScript('');

        $tabla = new stdClass();
        $tabla->id = 'tablaPresupuesto';
        $tabla->titulo = 'Claves Presupuestales';

        $tr = [];
        $th = new stdClass();
        $th->texto = 'Gpo';
        $th->width = '5px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Ur';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Ue';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Prg';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Spr';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Py';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Oa';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Pg';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Pe';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Cff';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Fte';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Sft';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Ori';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Sor';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Cfi';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Año';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Tipo';
        array_push($tr, $th);

        $tabla->encabezado = [$tr];
        $tabla->tools = $filtros;
        $tabla->template = '
            <tr class="odd success">
                <td class="sorting_1">${gpo_id}</td>
                <td class="sorting_1">${ur_id}</td>
                <td class="sorting_1">${ue_id}</td>
                <td class="sorting_1">${prg_id}</td>
                <td class="sorting_1">${spr_id}</td>
                <td class="sorting_1">${py_id}</td>
                <td class="sorting_1">${oa_id}</td>
                <td class="sorting_1">${pg_id}</td>
                <td class="sorting_1">${pe_id}</td>
                <td class="sorting_1">${cff_id}</td>
                <td class="sorting_1">${fte_id}</td>
                <td class="sorting_1">${sft_id}</td>
                <td class="sorting_1">${ori_id}</td>
                <td class="sorting_1">${sor_id}</td>
                <td class="sorting_1">${cfi_id}</td>
                <td class="sorting_1">${cfi_anio}</td>
                <td class="sorting_1">${tcp_id}</td>
            </tr>
            <tr class="odd">
                <td class="sorting_1" colspan="17">
                    <table>
                        <tr class="odd">
                            <td class="sorting_1">Enero</td>
                            <td class="sorting_1">Febrero</td>
                            <td class="sorting_1">Marzo</td>
                            <td class="sorting_1">Abril</td>
                            <td class="sorting_1">Mayo</td>
                            <td class="sorting_1">Junio</td>
                        </tr>
                        <tr class="odd">
                            <td class="sorting_1"><input type="text" class="moneda" value="${apr_ene}" name="apr_ene" form="cal_${pre_claveCompleta}" clave="${pre_claveCompleta}"/></td>
                            <td class="sorting_1"><input type="text" class="moneda" value="${apr_feb}" name="apr_feb" form="cal_${pre_claveCompleta}" clave="${pre_claveCompleta}"/></td>
                            <td class="sorting_1"><input type="text" class="moneda" value="${apr_mar}" name="apr_mar" form="cal_${pre_claveCompleta}" clave="${pre_claveCompleta}"/></td>
                            <td class="sorting_1"><input type="text" class="moneda" value="${apr_abr}" name="apr_abr" form="cal_${pre_claveCompleta}" clave="${pre_claveCompleta}"/></td>
                            <td class="sorting_1"><input type="text" class="moneda" value="${apr_may}" name="apr_may" form="cal_${pre_claveCompleta}" clave="${pre_claveCompleta}"/></td>
                            <td class="sorting_1"><input type="text" class="moneda" value="${apr_jun}" name="apr_jun" form="cal_${pre_claveCompleta}" clave="${pre_claveCompleta}"/></td>
                        </tr>
                        <tr class="odd">
                            <td class="sorting_1">Julio</td>
                            <td class="sorting_1">Agosto</td>
                            <td class="sorting_1">Septiembre</td>
                            <td class="sorting_1">Octubre</td>
                            <td class="sorting_1">Noviembre</td>
                            <td class="sorting_1">Diciembre</td>
                            <td class="sorting_1 pre_original" style="text-align: right; background-color: rgb(153, 204, 255); color: blue;">${pre_original}</td>
                        </tr>
                        <tr class="odd">
                            <td class="sorting_1"><input type="text" class="moneda" value="${apr_jul}" name="apr_jul" form="cal_${pre_claveCompleta}" clave="${pre_claveCompleta}"/></td>
                            <td class="sorting_1"><input type="text" class="moneda" value="${apr_ago}" name="apr_ago" form="cal_${pre_claveCompleta}" clave="${pre_claveCompleta}"/></td>
                            <td class="sorting_1"><input type="text" class="moneda" value="${apr_sep}" name="apr_sep" form="cal_${pre_claveCompleta}" clave="${pre_claveCompleta}"/></td>
                            <td class="sorting_1"><input type="text" class="moneda" value="${apr_oct}" name="apr_oct" form="cal_${pre_claveCompleta}" clave="${pre_claveCompleta}"/></td>
                            <td class="sorting_1"><input type="text" class="moneda" value="${apr_nov}" name="apr_nov" form="cal_${pre_claveCompleta}" clave="${pre_claveCompleta}"/></td>
                            <td class="sorting_1"><input type="text" class="moneda" value="${apr_dic}" name="apr_dic" form="cal_${pre_claveCompleta}" clave="${pre_claveCompleta}"/></td>
                            <td class="sorting_1 warning " id="total_${pre_claveCompleta}" style="text-align: right;"></td>
                        </tr>
                        <tr class="odd">
                            <td class="sorting_1" colspan="7" style="text-align: end;"><form class="calendario" id="cal_${pre_claveCompleta}">
                                <input name="epg_id" value="${epg_id}" class="desaparece" />
                                <input name="ecf_id" value="${ecf_id}" class="desaparece" />
                                <input name="pg_id" value="${pg_id}" class="desaparece" />
                                <input name="pe_id" value="${pe_id}" class="desaparece" />
                                <input name="ue_id" value="${ue_id}" class="desaparece" />
                                <input name="tcp_id" value="${tcp_id}" class="desaparece" />
                                <button type="submit" class="btn btn-sm btn-outline-primary">Actualizar</button></form>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>';

        $this->mixHtml($this->wrPonTabla($tabla));

        $this->mixScript($scriptFiltros);
        $this->mixScript('
            // Quitamos la info de una paginación que no usaremos en ésta pantalla
            $(".tabla-footer").remove();

            // Para que cuando entre y salga de la pantalla donde pide los encuadres CA y CF no salga vacía la segunda vez
            $(".breadcrumbs").click(function (e) {
                $("div[aria-describedby=cargaDatosProgFin]").html("");
            });

            $("#printAjuste").click(function () {
                var form =
                    $("<form>")
                        .attr("action", "rep/doc/mga/complementos/rep_MgaComplementos.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "ur_id").attr("value", $("#combour").val()))
                        .append($("<input>").attr("type", "hidden").attr("name", "ue_id").attr("value", $("#comboue").val()))
                        .append($("<input>").attr("type", "hidden").attr("name", "prg_id").attr("value", $("#comboprg").val()))
                        .append($("<input>").attr("type", "hidden").attr("name", "sp_id").attr("value", $("#combosp").val()))
                        .append($("<input>").attr("type", "hidden").attr("name", "py_id").attr("value", $("#combopy").val()))
                        .append($("<input>").attr("type", "hidden").attr("name", "oa_id").attr("value", $("#combooa").val()))
                        .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 3));
                $("body").append(form);
                form.submit();
                form.remove();
            });

            $("#printAjusteExcel").click(function () {
                var form = $("<form>")
                    .attr("action", "rep/excel/mga/exp_Ajustes.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "ur_id").attr("value", $("#combour").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "ue_id").attr("value", $("#comboue").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "prg_id").attr("value", $("#comboprg").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "sp_id").attr("value", $("#combosp").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "py_id").attr("value", $("#combopy").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "oa_id").attr("value", $("#combooa").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", 3));
                $("body").append(form);
                form.submit();
                form.remove();
            });

            $("#printAjusteExcelConPhpSpredsheet").click(function () {
                var form = $("<form>")
                    .attr("action", "rep/excel/mga/exp_Ajustes_phpspreadsheet_prueba.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "ur_id").attr("value", $("#combour").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "ue_id").attr("value", $("#comboue").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "prg_id").attr("value", $("#comboprg").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "sp_id").attr("value", $("#combosp").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "py_id").attr("value", $("#combopy").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "oa_id").attr("value", $("#combooa").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", 12));
                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');
    }

    private function llenaTablaPresupuesto()
    {
        $join = '';
        $filtro = '';
        $parametros = [];

        if (!empty($this->prm->clg_id)) {
            $join .= 'INNER JOIN ' . BD_CTRL . '.cat_PartidaEspecifica pe USING(pg_id, pe_id)';
            $filtro .= ' AND pe.clg_id = :clgId';
            $parametros[':clgId'] = $this->prm->clg_id;
        }

        if (!empty($this->prm->pg_id)) {
            $filtro .= ' AND p.pg_id = :pgId';
            $parametros[':pgId'] = $this->prm->pg_id;
        }

        if (!empty($this->prm->pe_id)) {
            $filtro .= ' AND p.pe_id = :peId';
            $parametros[':peId'] = $this->prm->pe_id;
        }

        if (!empty($_SESSION[_S]['cvefin']->ecf)) {
            $filtro .= ' AND p.ecf_id = :ecfId';
            $parametros[':ecfId'] = $_SESSION[_S]['cvefin']->ecf;
        }

        $sql = '
            SELECT p.gpo_id, p.ur_id, p.ue_id,
                p.prg_id, p.spr_id, p.py_id, p.oa_id,
                p.pg_id, p.pe_id,
                p.cff_id, p.fte_id, p.sft_id, p.ori_id, p.sor_id, p.cfi_id, p.cfi_anio,
                p.tcp_id,
                p.epg_id, p.ecf_id,
                p.pre_claveCompleta,
                apr.apr_ene, apr.apr_feb, apr.apr_mar, apr.apr_abr, apr.apr_may, apr.apr_jun,
                apr.apr_jul, apr.apr_ago, apr.apr_sep, apr.apr_oct, apr.apr_nov, apr.apr_dic,
                (ori.ori_ene+ori.ori_feb+ori.ori_mar+ori.ori_abr+ori.ori_may+ori.ori_jun+ori.ori_jul+ori.ori_ago+ori.ori_sep+ori.ori_oct+ori.ori_nov+ori.ori_dic) AS pre_original
            FROM ' . BD_CTRL . '.presupuesto AS p
                INNER JOIN ' . BD_CTRL . '.cat_UnidadResponsable AS ur USING(gpo_id, ur_id)
                INNER JOIN ' . BD_CTRL . ".presupuestoOriginal AS ori USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                $join
                LEFT JOIN " . BD_CTRL . '.presupuestoAprobado AS apr USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
            WHERE p.gpo_id = :gpo
                AND p.ur_id = :ur';

        if (isset($_SESSION[_S]['clasAdm-prg']) && !is_null($_SESSION[_S]['clasAdm-prg'])) {
            $sql .= ' AND p.prg_id = :prg';
            $parametros[':prg'] = $_SESSION[_S]['clasAdm-prg'];
        }

        if (isset($_SESSION[_S]['clasAdm-spr']) && !is_null($_SESSION[_S]['clasAdm-spr'])) {
            $sql .= ' AND p.spr_id = :spr';
            $parametros[':spr'] = $_SESSION[_S]['clasAdm-spr'];
        }

        if (isset($_SESSION[_S]['clasAdm-py']) && !is_null($_SESSION[_S]['clasAdm-py'])) {
            $sql .= ' AND p.py_id = :py';
            $parametros[':py'] = $_SESSION[_S]['clasAdm-py'];
        }

        if (isset($_SESSION[_S]['clasAdm-oa']) && !is_null($_SESSION[_S]['clasAdm-oa'])) {
            $sql .= ' AND p.oa_id = :oa';
            $parametros[':oa'] = $_SESSION[_S]['clasAdm-oa'];
        }

        $sql .= " AND ur_poa = 1 AND p.tcp_id != 'P'
                $filtro
                ORDER BY p.pre_claveCompleta;";

        $parametros[':gpo'] = !is_null($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : 'NULL';
        $parametros[':ur'] = !is_null($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : 'NULL';

        $this->setSql($sql);
        $this->setParams($parametros);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaPresupuesto';
        $nav->nf = '999';

        $NR = $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".pre_original").each(function () {
                $(this).text(formatNumber($(this).text(), "$ "));
            });

            $(".moneda").focus(function () {
                $(this).val(unformatNumber($(this).val()));
            });

            $(".moneda").blur(function () {
                clave = $(this).attr("clave");
                total = 0;
                $(".moneda[clave=\'" + clave + "\']").each(function () {
                    total += unformatNumber($(this).val());
                });
                var t = Math.round(total * 100) / 100;
                $("#total_" + clave).text(formatNumber(t, "$ "));
                $(this).val(formatNumber($(this).val(), "$ "));
            });

            $(".moneda").blur();

            $(".moneda").css("text-align", "end");

            $(".desaparece").css("float", "left").css("position", "absolute").css("top", "-2600px");

            $(".calendario").submit(function (e) {
                e.preventDefault();

                $(".entero,.moneda,.numero,.decimal").each(function () {
                    $(this).val(unformatNumber($(this).val()));
                });

                let json = { metodo: "guardaAutorizado" };
                let p = $(this).serializeArray();

                $.each(p, function () {
                    if (json[this.name] !== undefined) {
                        if (!json[this.name].push) {
                            json[this.name] = [json[this.name]];
                        }
                        json[this.name].push(this.value || "");
                    } else {
                        json[this.name] = this.value || "";
                    }
                });

                $.ajax({
                    url: "' . $this->pantalla . '",
                    data: json,
                    type: "POST",
                    dataType: "json"
                }).done(function (data) {
                    comunicaCallback(data.comunica);
                }).fail(function (data) {
                    errorCallback(data);
                    alert("ocurrio un Error");
                });
            });
        ');
    }

    private function llenaFiltros()
    {
        if (isset($this->prm->prog)) {
            $prog = $this->prm->prog;
            $_SESSION[_S]['clasAdm-ue'] = substr($prog, 4, 3);
            $_SESSION[_S]['clasAdm-prg'] = substr($prog, 8, 3);
            $_SESSION[_S]['clasAdm-spr'] = substr($prog, 12, 2);
            $_SESSION[_S]['clasAdm-py'] = substr($prog, 15, 3);
            $_SESSION[_S]['clasAdm-oa'] = substr($prog, 19, 3);
            $_SESSION[_S]['clasAdm-epg'] = $this->prm->encProg;
        }

        if (isset($this->prm->cf)) {
            $cveFin = $this->prm->cveFin;
            if (!isset($_SESSION[_S]['cvefin'])) {
                $_SESSION[_S]['cvefin'] = new stdClass();
            }
            $_SESSION[_S]['cvefin']->cff = substr($cveFin, 0, 1);
            $_SESSION[_S]['cvefin']->fte = substr($cveFin, 2, 1);
            $_SESSION[_S]['cvefin']->sft = substr($cveFin, 3, 1);
            $_SESSION[_S]['cvefin']->ori = substr($cveFin, 4, 1);
            $_SESSION[_S]['cvefin']->sor = substr($cveFin, 5, 1);
            $_SESSION[_S]['cvefin']->cfi = substr($cveFin, 7, 2);
            $_SESSION[_S]['cvefin']->anio = substr($cveFin, 9, 2);
            $_SESSION[_S]['cvefin']->cveFin = substr($cveFin, 0, 1) . substr($cveFin, 2, 4) . substr($cveFin, 7, 4);
            $_SESSION[_S]['cvefin']->nombre = $this->prm->nomFin;
            $_SESSION[_S]['cvefin']->ecf = $this->prm->encCveFin;
            $_SESSION[_S]['cvefin']->lectura = 'true';
            $_SESSION[_S]['cvefin']->filtro = 'poa';
        }

        $this->cargaClg();
        $this->setContenedor('mensajeCosteo');
        // Si el costeo NO está cerrado, no le permitimos modificar nada
        if ($this->cierre < 5 && $this->estadoCosteo != 2) {
            $this->mixHtml('
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" align="center">
                    <strong>El costeo de la Unidad Responsable NO ha sido cerrado</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>');
            $this->escritura = false;
        } else {
            $this->mixHtml('<div></div>');
        }
    }

    private function cargaClg()
    {
        $combo = new stdClass();
        $combo->id = 'clg_id';
        $combo->detonar = false;

        $sql = 'SELECT pe.clg_id, clg.clg_nombre
                FROM ' . BD_CTRL . '.presupuesto p
                INNER JOIN ' . BD_CTRL . '.cat_PartidaEspecifica AS pe USING(pg_id, pe_id)
                INNER JOIN ' . BD_CTRL . '.cat_ClaseGasto AS clg USING(clg_id)
                WHERE p.gpo_id = :gpo AND p.ur_id = :ur';

        $parametros = [
            ':gpo' => !is_null($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : 'NULL',
            ':ur'  => !is_null($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : 'NULL',
        ];

        if ($_SESSION[_S]['clasAdm-epg'] != '' && isset($_SESSION[_S]['cvefin']->ecf)) {
            if (!empty($_SESSION[_S]['clasAdm-epg'])) {
                $sql .= ' AND p.epg_id = :epg';
                $parametros[':epg'] = $_SESSION[_S]['clasAdm-epg'];
            }

            if (!empty($_SESSION[_S]['cvefin']->ecf)) {
                $sql .= ' AND p.ecf_id = :ecf';
                $parametros[':ecf'] = $_SESSION[_S]['cvefin']->ecf;
            }
        } else {
            if (!empty($_SESSION[_S]['clasAdm-prg'])) {
                $sql .= ' AND p.prg_id = :prg';
                $parametros[':prg'] = $_SESSION[_S]['clasAdm-prg'];
            }

            if (!empty($_SESSION[_S]['clasAdm-spr'])) {
                $sql .= ' AND p.spr_id = :spr';
                $parametros[':spr'] = $_SESSION[_S]['clasAdm-spr'];
            }

            if (!empty($_SESSION[_S]['clasAdm-py'])) {
                $sql .= ' AND p.py_id = :py';
                $parametros[':py'] = $_SESSION[_S]['clasAdm-py'];
            }

            if (!empty($_SESSION[_S]['clasAdm-oa'])) {
                $sql .= ' AND p.oa_id = :oa';
                $parametros[':oa'] = $_SESSION[_S]['clasAdm-oa'];
            }
        }

        $sql .= ' GROUP BY clg.clg_id;';

        $combo->sql = $sql;
        $combo->params = $parametros;
        $this->recargaCombo($combo);
        $this->prm->clg_id = $this->comboValorUnico;
        $this->cargaPg();
    }

    private function cargaPg()
    {
        $combo = new stdClass();
        $combo->id = 'gral_id';
        $combo->detonar = false;
        $filtro = '';
        $join = '';
        $parametros = [];

        if (!empty($this->prm->clg_id)) {
            $filtro = ' AND pe.clg_id = :clgId';
            $join = 'INNER JOIN cat_PartidaEspecifica AS pe USING(pg_id, pe_id)';
            $parametros[':clgId'] = $this->prm->clg_id;
        }

        $sql = "SELECT DISTINCT p.pg_id, pg.pg_nombre
                FROM presupuesto AS p
                INNER JOIN cat_PartidaGenerica pg USING(pg_id)
                $join
                WHERE p.gpo_id = :gpo
                AND p.ur_id = :ur";

        $parametros[':gpo'] = !is_null($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : 'NULL';
        $parametros[':ur'] = !is_null($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : 'NULL';

        if (!is_null($_SESSION[_S]['clasAdm-prg'])) {
            $sql .= ' AND p.prg_id = :prg';
            $parametros[':prg'] = $_SESSION[_S]['clasAdm-prg'];
        }

        if (!is_null($_SESSION[_S]['clasAdm-spr'])) {
            $sql .= ' AND p.spr_id = :spr';
            $parametros[':spr'] = $_SESSION[_S]['clasAdm-spr'];
        }

        if (!is_null($_SESSION[_S]['clasAdm-py'])) {
            $sql .= ' AND p.py_id = :py';
            $parametros[':py'] = $_SESSION[_S]['clasAdm-py'];
        }

        if (!is_null($_SESSION[_S]['clasAdm-oa'])) {
            $sql .= ' AND p.oa_id = :oa';
            $parametros[':oa'] = $_SESSION[_S]['clasAdm-oa'];
        }

        $sql .= "
            $filtro
            AND (p.prg_id NOT BETWEEN 900 AND 999)
            ORDER BY p.pg_id;
            ";

        $combo->sql = $sql;
        $combo->params = $parametros;

        $this->recargaCombo($combo);
        $this->prm->pg_id = $this->comboValorUnico;
        $combo->detonar = false;
        $this->cargaPe();
    }

    private function cargaPe()
    {
        $parametros = [];

        $sql = 'SELECT DISTINCT pe.pe_id, pe.pe_nombre
                FROM presupuesto AS p INNER JOIN cat_PartidaEspecifica AS pe USING(pg_id, pe_id)
                WHERE p.gpo_id = :gpo AND p.ur_id = :ur AND (p.prg_id NOT BETWEEN 900 AND 999)';

        $parametros[':gpo'] = !is_null($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : 'NULL';
        $parametros[':ur'] = !is_null($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : 'NULL';

        if (!is_null($_SESSION[_S]['clasAdm-prg'])) {
            $sql .= ' AND p.prg_id = :prg';
            $parametros[':prg'] = $_SESSION[_S]['clasAdm-prg'];
        }

        if (!is_null($_SESSION[_S]['clasAdm-spr'])) {
            $sql .= ' AND p.spr_id = :spr';
            $parametros[':spr'] = $_SESSION[_S]['clasAdm-spr'];
        }

        if (!is_null($_SESSION[_S]['clasAdm-py'])) {
            $sql .= ' AND p.py_id = :py';
            $parametros[':py'] = $_SESSION[_S]['clasAdm-py'];
        }

        if (!is_null($_SESSION[_S]['clasAdm-oa'])) {
            $sql .= ' AND p.oa_id = :oa';
            $parametros[':oa'] = $_SESSION[_S]['clasAdm-oa'];
        }

        if (!empty($this->prm->pg_id)) {
            $sql .= ' AND p.pg_id = :pgId';
            $parametros[':pgId'] = $this->prm->pg_id;
        }

        if (!empty($this->prm->clg_id)) {
            $sql .= ' AND clg_id = :clgId';
            $parametros[':clgId'] = $this->prm->clg_id;
        }

        $sql .= ' ORDER BY pe.pe_id;';

        $combo = new stdClass();
        $combo->id = 'esp_id';
        $combo->detonar = false;
        $combo->sql = $sql;
        $combo->params = $parametros;
        $this->recargaCombo($combo);
    }

    private function guardaAutorizado()
    {
        if ($this->escritura) {
            $this->conectar();
            $this->setSql('SET @usr_id = :usrId, @usr_ip = ' . BD_SINPRES . '.fn_get_ip()');
            $this->consultar([':usrId' => $_SESSION[_S]['usr_id']]);
            $this->setSql('SET @bit_observacion = "REGISTRO INSERTADO DESDE PANTALLA DE AJUSTES";');
            $this->consultar();
            $sql = 'INSERT INTO ' . BD_CTRL . '.poa_Ajuste (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id, mon_ene, mon_feb, mon_mar, mon_abr, mon_may, mon_jun, mon_jul, mon_ago, mon_sep, mon_oct, mon_nov, mon_dic, usr_id)
                    VALUES (:gpoId, :urId, :ueId, :epgId, :pgId, :peId, :ecfId, :tcpId, :aprEne, :aprFeb, :aprMar, :aprAbr, :aprMay, :aprJun, :aprJul, :aprAgo, :aprSep, :aprOct, :aprNov, :aprDic, :usrId)
                    ON DUPLICATE KEY UPDATE mon_ene = :aprEne, mon_feb = :aprFeb, mon_mar = :aprMar, mon_abr = :aprAbr, mon_may = :aprMay, mon_jun = :aprJun, mon_jul = :aprJul, mon_ago = :aprAgo, mon_sep = :aprSep, mon_oct = :aprOct, mon_nov = :aprNov, mon_dic = :aprDic, usr_id = :usrId;';
            $parametros = [
                ':gpoId'  => $_SESSION[_S]['clasAdm-gpo'],
                ':urId'   => $_SESSION[_S]['clasAdm-ur'],
                ':ueId'   => $this->prm->ue_id,
                ':epgId'  => $this->prm->epg_id,
                ':pgId'   => $this->prm->pg_id,
                ':peId'   => $this->prm->pe_id,
                ':ecfId'  => $this->prm->ecf_id,
                ':tcpId'  => $this->prm->tcp_id,
                ':aprEne' => $this->prm->apr_ene,
                ':aprFeb' => $this->prm->apr_feb,
                ':aprMar' => $this->prm->apr_mar,
                ':aprAbr' => $this->prm->apr_abr,
                ':aprMay' => $this->prm->apr_may,
                ':aprJun' => $this->prm->apr_jun,
                ':aprJul' => $this->prm->apr_jul,
                ':aprAgo' => $this->prm->apr_ago,
                ':aprSep' => $this->prm->apr_sep,
                ':aprOct' => $this->prm->apr_oct,
                ':aprNov' => $this->prm->apr_nov,
                ':aprDic' => $this->prm->apr_dic,
                ':usrId'  => $_SESSION[_S]['usr_id'],
            ];

            $this->setSql($sql);
            $this->consultar($parametros);
            $this->desconectar();
            $this->mixScript('
                notify("Montos actualizados");
                $(".entero,.moneda,.numero,.decimal").each(function(){
                    $(this).val( formatNumber( $(this).val(),"$ " ) );
                });
            ');
        } else {
            $this->mixScript('
                notify("El costeo de la UR NO esta cerrado, NO puede ajustar.", "error");
                $(".entero,.moneda,.numero,.decimal").each(function(){
                    $(this).val( formatNumber( $(this).val(),"$ " ) );
                });
            ');
        }
    }
}

if (isset($_SESSION[_S]['prm']['M']['4']['3'][TAREA_LECTURA])) {
    new ControladorAjuste();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
