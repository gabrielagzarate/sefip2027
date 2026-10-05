<?php
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';

class ControladorReportesPresupuesto extends WebResponse
{
    public $gpo_id;
    public $ur_id;
    public $permisoLectura;
    public $usr_id;
    public $nvj;
    public $permiso49;
    public $permiso51;
    public $permiso52;
    public $permiso53;
    public $permiso79;
    public $permiso80;
    public $permiso85;
    public $server;
    public $btnclass = 'btn btn-outline-secondary me-1';

    public function __construct()
    {
        parent::__construct();
        /**
         *  Validación temporal para que usuarios con nvj mayor a 3 solo puedan acceder
         *  ya que aun no se encuentran funcionando gran parte de los reportes.
         */

        $this->permisoLectura = isset($_SESSION[_S]['prm']['S']['4']['1']['1']) ? true : false;
        $this->permiso49 = isset($_SESSION[_S]['prm']['S']['4']['1']['49']) ? true : false;
        $this->permiso51 = isset($_SESSION[_S]['prm']['S']['4']['1']['51']) ? true : false;
        $this->permiso52 = isset($_SESSION[_S]['prm']['S']['4']['1']['52']) ? true : false;
        $this->permiso53 = isset($_SESSION[_S]['prm']['S']['4']['1']['53']) ? true : false;
        $this->permiso79 = isset($_SESSION[_S]['prm']['S']['4']['1']['79']) ? true : false;
        $this->permiso80 = isset($_SESSION[_S]['prm']['S']['4']['1']['80']) ? true : false;
        $this->permiso85 = isset($_SESSION[_S]['prm']['S']['4']['1']['85']) ? true : false;

        $this->nvj = $_SESSION[_S]['nvj_id'];
        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->ur_id = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : '';
        $this->gpo_id = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : '';

        //Variable para obtener el servidor actual
        $this->server = $_SERVER['SERVER_ADDR'];

        switch ($this->nvj) {
            case 1:
            case 2:
            case 1:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
                switch ($this->metodo) {
                    case 'filtros':
                        $this->filtros();
                        break;
                    case 'activaopcion':
                        $this->activaopcion();
                        break;
                    case 'sl3':
                        $this->sl3();
                        break;
                    case 'php':
                        $this->phprep();
                        break;
                    case 'phpAdmin':
                        $this->phprepAdmin();
                        break;
                    case 'changeUr':
                        $this->changeUr();
                        break;
                    default:
                        $this->main();
                        break;
                }
                break;
        }

        $this->returnJson();
    }

    /************* FUNCION PRINCIPAL  *******************/
    private function main()
    {
        $this->agregaTools('');
        $adminL = false; //lectura
        $admin = false; //escritura

        if ($this->permiso49) {
            $adminL = true;
        } // REPORTES SEFIN
        if ($this->permiso52) {
            $admin = true;
        } // ESCRITURA REPORTES SEFIN
        if ($this->permiso51) {
            $adminL = true;
        } // REPORTES PLANEACIÓN
        if ($this->permiso53) {
            $admin = true;
        } // ESCRITURA REPORTES PLANEACIÓN
        if ($this->permiso85) {
            $adminL = true;
        } // REPOTES CONTRALORIA - no se encontro este permiso
        $this->conectar();

        $sql = 'SELECT  * FROM rep_DireccionEgresos WHERE die_id NOT IN ( ?, ?, ?, ?, ?, ? ) AND sis_id = ?';
        $params = [100, 102, 105, 106, 107, 108, 'S'];
        if ($admin || $adminL) {
            $sql .= " AND die_manto IN('P', 'N', 'M') "; // verificar esta linea ya que si se cumple cualquier permiso de arriba ya no respecta los especificos
        } else {
            $sql .= " AND sts_id = 1 AND die_tipo IN ('DEPENDENCIA'"
                . ($this->permiso79 ? ",'SUBSEPLAN'" : '') // lectura subseplan
                . ($this->permiso80 ? ",'CUENTA'" : '') // lectura cuenta
                . ')';
        }

        /**
         * Se agrego validación para pruebas, donde si el usuario solo tiene permiso de Lectura SEFIN, pueda ver los reporte básicos + SEFIN
         */

        if ($this->usr_id == 7 || $this->usr_id == 8 || $this->usr_id == 181) { // verificar quienes son estos usuarios
            $sql = 'SELECT * FROM rep_DireccionEgresos WHERE die_id NOT IN (?, ?, ?, ?, ?, ?) AND sis_id = ?';
            $adminL = true;
            $admin = true;
        }
        if (count($_SESSION[_S]['prm']['S']['4']['1']) == 1 and $this->permiso79) { // permisos de lectura y reportes subseplan
            $sql .= " AND  die_tipo='SUBSEPLAN' ";
        }

        if (count($_SESSION[_S]['prm']['S']['4']['1']) == 1 and $this->permiso85) {
            $sql .= " AND  die_tipo='CONTRALORIA' and sts_id=1 ";
        }

        if ($this->nvj <= 2) {
            $sql .= ' AND sts_id = 1';
        }

        $sql .= ' AND die_id NOT IN ( 100,102,105,106,107,108)  ORDER BY die_tipo,die_numero ';
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();
        $data = $this->extraer();

        $he = ($adminL ? '41px;' : 'auto;');
        if ($data && isset($this->prm->section) && $this->prm->section == $data['die_tipo']) {
            $he = 'auto;';
        }

        $html = '
            <style>
                .lista-reportes-pres .reporte-pres-cabecera {
                    display: flex;
                    flex-wrap: wrap;
                    align-items: center;
                    justify-content: space-between;
                    gap: .75rem;
                    margin-bottom: 1rem;
                }
                .lista-reportes-pres .reporte-pres-tabla {
                    margin-bottom: 0;
                    border: 1px solid #adb5bd;
                    border-radius: .375rem;
                    overflow: hidden;
                    background: #fff;
                }
                .lista-reportes-pres .reporte-pres-tabla thead th {
                    background: #f8f9fa;
                    border-bottom: 1px solid #adb5bd;
                    border-right: 1px solid #ced4da;
                    font-size: .78rem;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: .02em;
                    color: #495057;
                    vertical-align: middle;
                    white-space: nowrap;
                    padding: .65rem .75rem;
                }
                .lista-reportes-pres .reporte-pres-tabla thead th:last-child {
                    border-right: 0;
                }
                .lista-reportes-pres .reporte-pres-tabla tbody td {
                    vertical-align: middle;
                    padding: .85rem .75rem;
                    border-color: #ced4da;
                    border-right: 1px solid #ced4da;
                }
                .lista-reportes-pres .reporte-pres-tabla tbody td:last-child {
                    border-right: 0;
                }
                .lista-reportes-pres .reporte-pres-tabla tbody tr:hover {
                    background: #f8fbff;
                }
                .lista-reportes-pres .reporte-pres-tabla tbody tr:last-child td {
                    border-bottom: 0;
                }
                .lista-reportes-pres .reporte-pres-col-num {
                    width: 3.5rem;
                    text-align: center;
                    color: #6c757d;
                    font-size: .85rem;
                }
                .lista-reportes-pres .reporte-pres-col-formato {
                    width: 7.5rem;
                    text-align: center;
                    white-space: nowrap;
                }
                .lista-reportes-pres .reporte-pres-col-activo {
                    width: 4.5rem;
                    text-align: center;
                }
                .lista-reportes-pres .reporte-pres-accion {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: .25rem;
                    min-height: 2rem;
                }
                .lista-reportes-pres .imprimir {
                    flex-shrink: 0;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 2rem;
                    padding: .3rem .7rem;
                    font-weight: 600;
                }
                .reportessefin {
                    overflow: hidden;
                    margin-bottom: 1.25rem;
                }
            </style>
        ';

        $titulo = '';
        $registro = true;
        $usuario = false;
        $color = '';
        $mensaje = '';
        $inicial = true;
        $tipo = '';

        $cerrarSeccion = '
                            </tbody>
                            </table>
                        </div>
                    </div>';

        $abrirSeccion = function ($idSeccion, $idLista, $tituloSeccion, $altura) {
            $btnTexto = ($altura == 'auto;') ? '▲ Contraer' : '▼ Desplegar';
            $conActivo = ($this->nvj > 2);
            $colActivo = $conActivo ? '<th class="text-center reporte-pres-col-activo">Activo</th>' : '';
            return '
                    <div id="' . $idSeccion . '" class="reportessefin" style="height:' . $altura . '">
                        <div class="lista-reportes-pres" id="' . $idLista . '">
                            <div class="reporte-pres-cabecera">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-outline-warning btn-sm cerrar_tabla_sefin">' . $btnTexto . '</button>
                                    <h5 class="m-0 fw-bold">' . $tituloSeccion . '</h5>
                                </div>
                            </div>
                            <table class="table reporte-pres-tabla">
                                <thead>
                                    <tr>
                                        <th class="text-center reporte-pres-col-num">N°</th>
                                        <th>Nombre</th>
                                        <th class="text-center reporte-pres-col-formato">Ver PDF</th>
                                        <th class="text-center reporte-pres-col-formato">Exportar a Excel</th>
                                        <th class="text-center reporte-pres-col-formato">Excel sin formato</th>
                                        <th class="text-center reporte-pres-col-formato">MS Word</th>
                                        <th class="text-center reporte-pres-col-formato">HTML</th>
                                        ' . $colActivo . '
                                    </tr>
                                </thead>
                                <tbody>';
        };

        $tituloPorTipo = function ($tipoSeccion, $inicialSeccion = false) {
            switch ($tipoSeccion) {
                case 'DEPENDENCIA':
                    return 'Reportes Presupuesto';
                case 'CUENTA':
                    return $inicialSeccion ? 'Reportes de cuenta pública e Informes Trimestrales' : 'Reportes de cuenta publica';
                case 'SEFIN':
                    return 'Reportes SEFIN';
                case 'PLANEACION':
                    return 'Reportes de la Coord. de Seguimiento Presupuestario a Gastos de Operaci&oacute;n';
                case 'SUBSEPLAN':
                    return 'Reportes SUBSEPLAN';
                case 'POA':
                    return 'Reportes POA';
                case 'SEGUIMIENTO':
                    return 'Reportes de Seguimiento';
                case 'CONTRALORIA':
                    return 'Reportes de Contraloria';
                case 'ADMINISTRACION':
                    return 'Secretaria de Administración - Dirección de Recursos Materiale';
                default:
                    return '';
            }
        };

        if ($data) {
            $reportesDisponibles = array_merge(
                range(1, 41),
                [47, 101, 120, 122, 123, 125, 126]
            );
            do {
                // Solo reportes activos (o nvj > 2) cuyo id ya está liberado.
                if (($data['sts_id'] == 1 || $this->nvj > 2)
                    && in_array((int) $data['die_id'], $reportesDisponibles, true)
                ) {
                        if ($inicial === true) {
                            $inicial = false;
                            $tipo = $data['die_tipo'];
                            $titulo = $tituloPorTipo($data['die_tipo'], true);

                            if ($titulo != '') {
                                $html .= $abrirSeccion(
                                    'reportes' . $data['die_tipo'],
                                    'lista-' . $data['die_tipo'],
                                    $titulo,
                                    $he
                                );
                            }
                        }
                        if ($data['die_manto'] == 'M') {
                            $color = 'background-color:rgba(255,0,0,.3) !important;';
                            $mensaje = '<span style="font-weight:bold; padding:5px; background-color:#FFF; color:#F00">[ EN MANTENIMIENTO ] <span class="glyphicon glyphicon-warning-sign"></span></span>';
                        } else {
                            $color = '';
                            $mensaje = '';
                        }

                        if ($tipo != $data['die_tipo']) {
                            $html .= $titulo != '' ? $cerrarSeccion : '';

                            $tipo = $data['die_tipo'];
                            $titulo = $tituloPorTipo($tipo, false);

                            if ($titulo != '') {
                                if (($tipo == 'SEFIN' || $tipo == 'SUBSEPLAN')
                                    && !$this->permiso49
                                    && !$this->permiso79
                                ) {
                                    $registro = false;
                                } elseif (
                                    $tipo == 'PLANEACION'
                                    && !$this->permiso51
                                    && $this->usr_id != 279
                                ) {
                                    $registro = false;
                                } else {
                                    $registro = true;
                                    $he = ($adminL ? '39px;' : 'auto;');
                                    if (isset($this->prm->section) && $this->prm->section == $data['die_tipo']) {
                                        $he = 'auto;';
                                    }

                                    $html .= $abrirSeccion(
                                        'reportes' . $data['die_id'],
                                        'lista-' . $data['die_tipo'],
                                        $titulo,
                                        $he
                                    );
                                }
                            }
                        }

                        if ($titulo != '') {
                            if ($adminL || $admin || $this->usr_id == 8) {
                                $usuario = true;
                            } else {
                                if ($data['sts_id'] == 1) {
                                    $usuario = true;
                                } else {
                                    $usuario = false;
                                }
                            }

                            /**
                             * Se agrego esta validación para recuperar cuando es admin un usuario.
                             *
                             */

                            if ($data['die_tipo'] == 'DEPENDENCIA' && $this->permiso51 && $this->usr_id != 8) {
                                $admin = false;
                            } elseif ($data['die_tipo'] == 'PLANEACION' && $this->permiso53 && ($this->usr_id != 8)) {
                                $admin = true;
                            } elseif ($data['die_tipo'] == 'SEFIN' && $this->permiso52 && $this->usr_id != 8 && $this->nvj > 2) {
                                $admin = true;
                            }

                            $pdf = '<button type="button" section="' . $data['die_tipo'] . '" rep_id="' . $data['die_id'] . '" formato="PDF" class="btn btn-outline-danger btn-sm imprimir" title="Ver PDF"  ' . ($data['die_pdf'] == 2 && $this->nvj <= 2 ? 'style="display: none;"' : '') . '><i class="fa-solid fa-file-pdf"></i></button>';
                            $pdfch = '<input class="activaopcionreporte" ' . ($data['die_pdf'] == 1 ? 'checked="checked"' : '') . ' type="checkbox" rep_id="' . $data['die_id'] . '" option="die_pdf" title="Activar PDF">';
                            $xls1 = '<button type="button" section="' . $data['die_tipo'] . '" rep_id="' . $data['die_id'] . '" formato="EXCEL" class="btn btn-outline-success btn-sm imprimir" title="Exportar a Excel" ' . ($data['die_excel'] == 2 && $this->nvj <= 2 ? 'style="display: none;"' : '') . '><i class="fa-solid fa-file-excel"></i></button>';
                            $xls1c = '<input class="activaopcionreporte" rep_id="' . $data['die_id'] . '" option="die_excel" type="checkbox" ' . ($data['die_excel'] == 1 ? 'checked="checked"' : '') . ' title="Activar Excel">';
                            $xls2 = '<button type="button" section="' . $data['die_tipo'] . '" rep_id="' . $data['die_id'] . '" formato="EXCEL2" class="btn btn-outline-success btn-sm imprimir" title="Exportar a Excel sin formato" ' . ($data['die_excelInf'] == 2 && $this->nvj <= 2 ? 'style="display: none;"' : '') . '><i class="fa-solid fa-file-excel"></i></button>';
                            $xls2c = '<input class="activaopcionreporte" rep_id="' . $data['die_id'] . '" option="die_excelInf" type="checkbox" ' . ($data['die_excelInf'] == 1 ? 'checked="checked"' : '') . ' title="Activar Excel sin formato">';
                            $doc = '<button type="button" section="' . $data['die_tipo'] . '" rep_id="' . $data['die_id'] . '" formato="WORD" class="btn btn-outline-info btn-sm imprimir" title="Exportar a Word" ' . ($data['die_doc'] == 2 && $this->nvj <= 2 ? 'style="display: none;"' : '') . '><i class="fa-solid fa-file-word"></i></button>';
                            $docc = '<input class="activaopcionreporte" rep_id="' . $data['die_id'] . '" option="die_doc" type="checkbox" ' . ($data['die_doc'] == 1 ? 'checked="checked"' : '') . ' title="Activar Word">';
                            $xls3 = '<button type="button" section="' . $data['die_tipo'] . '" rep_id="' . $data['die_id'] . '" formato="HTML" clase="2" tipo="dos" class="btn btn-outline-secondary btn-sm imprimir" title="Exportar a HTML" ' . ($data['die_excelWork'] == 2 && $this->nvj <= 2 ? 'style="display: none;"' : '') . '><i class="fa-solid fa-code"></i></button>';
                            $xls3c = '<input class="activaopcionreporte" rep_id="' . $data['die_id'] . '" option="die_excelWork" type="checkbox" ' . ($data['die_excelWork'] == 1 ? 'checked="checked"' : '') . ' title="Activar HTML">';

                            if ($this->nvj <= 2) {
                                $pdfch = $xls1c = $xls2c = $docc = $xls3c = '';
                            }

                            if (($registro && $usuario) || $this->usr_id == 8) {
                                $colPdf = ($data['die_pdf'] == 1 || $admin)
                                    ? '<span class="reporte-pres-accion">' . $pdf . ($admin ? $pdfch : '') . '</span>'
                                    : '&nbsp;';
                                $colExcel = ($data['die_excel'] == 1 || $admin)
                                    ? '<span class="reporte-pres-accion">' . $xls1 . ($admin ? $xls1c : '') . '</span>'
                                    : '&nbsp;';
                                $colExcelSf = ($data['die_excelInf'] == 1 || $admin)
                                    ? '<span class="reporte-pres-accion">' . ($data['die_excelInf'] == 1 ? $xls2 : '') . ($admin ? $xls2c : '') . '</span>'
                                    : '&nbsp;';
                                $colWord = ($data['die_doc'] == 1 || $admin)
                                    ? '<span class="reporte-pres-accion">' . ($data['die_doc'] == 1 ? $doc : '') . ($admin ? $docc : '') . '</span>'
                                    : '&nbsp;';
                                $colHtml = ($data['die_excelWork'] == 1 || $admin)
                                    ? '<span class="reporte-pres-accion">' . ($data['die_excelWork'] == 1 ? $xls3 : '') . ($admin ? $xls3c : '') . '</span>'
                                    : '&nbsp;';

                                $colActivo = ($this->nvj > 2)
                                    ? '<td class="reporte-pres-col-activo">' . (
                                        $admin
                                        ? '<input class="activaopcionreporte" type="checkbox" rep_id="' . $data['die_id'] . '" option="sts_id" ' . ($data['sts_id'] == 1 ? 'checked="checked"' : '') . ' title="Activo">'
                                        : '&nbsp;'
                                    ) . '</td>'
                                    : '';

                                $nombreEsc = htmlspecialchars($data['die_nombre'], ENT_QUOTES, 'UTF-8');

                                $html .= '
                                    <tr class="reporte-pres-item" style="' . $color . '">
                                        <td class="reporte-pres-col-num">' . $data['die_numero'] . '</td>
                                        <td>
                                            <div class="fw-semibold text-dark" title="' . $nombreEsc . '">' . $data['die_nombre'] . ' ' . $mensaje . '</div>
                                        </td>
                                        <td class="reporte-pres-col-formato">' . $colPdf . '</td>
                                        <td class="reporte-pres-col-formato">' . $colExcel . '</td>
                                        <td class="reporte-pres-col-formato">' . $colExcelSf . '</td>
                                        <td class="reporte-pres-col-formato">' . $colWord . '</td>
                                        <td class="reporte-pres-col-formato">' . $colHtml . '</td>
                                        ' . $colActivo . '
                                    </tr>
                                ';
                            }
                        }
                    }
            } while ($data = $this->extraer());
        }
        $html .= ($this->getNumeroRegistros() > 0 && $titulo != '' ? $cerrarSeccion : '');
        $this->mixHtml($html);
        if ($adminL || $admin) {
            $this->mixScript('
                $(".activaopcionreporte").click(function(){

                    if($(this).is(":checked")){
                        var rep_id = $(this).attr("rep_id");
                        var option = $(this).attr("option");
                        alertify.confirm("Confirmar", "¿Realmente desea Activar esta opcion para este reporte?",
                            function () {
                                comunica("scp/reportes/ControladorReportesPresupuesto.php", {
                                    metodo: "activaopcion",
                                    rep_id: rep_id,
                                    option: option,
                                    valor: "1"
                                });
                            }, function () { }).set("defaultFocus", "cancel").set("labels", { ok: "Aceptar", cancel: "Cancelar" });

                    }else{

                        var rep_id = $(this).attr("rep_id");
                        var option = $(this).attr("option");
                        alertify.confirm("Confirmar", "¿Realmente desea Desactivar esta opcion para este reporte?",
                            function () {
                                comunica("scp/reportes/ControladorReportesPresupuesto.php", {
                                    metodo: "activaopcion",
                                    rep_id: rep_id,
                                    option: option,
                                    valor: "2"
                                });
                            }, function () { }).set("defaultFocus", "cancel").set("labels", { ok: "Aceptar", cancel: "Cancelar" });
                        }
                });
            ');
        }
        $this->mixScript('
            $(".cerrar_tabla_sefin").click(function(){
                var wrap = $(this).closest(".reportessefin");
                var lista = wrap.find(".lista-reportes-pres");
                if (wrap[0].offsetHeight < 45) {
                    wrap.css("height", lista.outerHeight(true));
                    $(this).html("▲ Contraer");
                } else {
                    wrap.css("height", "41px");
                    $(this).html("▼ Desplegar");
                }
            });

            $(".imprimir").click(function() {
                if ($(this).attr("rep_id") == 8 || $(this).attr("rep_id") == 47 || $(this).attr("rep_id") == 103 || $(this).attr("rep_id") == 126) {
                    comunica("scp/reportes/ControladorReportesPresupuesto.php",{
                        rep_id:$(this).attr("rep_id"),
                        formato:$(this).attr("formato"),
                        metodo:"php",
                        section:$(this).attr("section")
                        }
                    );
                } else if($(this).attr("rep_id") == 124) {
                    comunica("scp/reportes/ControladorReportesPresupuesto.php",{
                        rep_id:$(this).attr("rep_id"),
                        formato:$(this).attr("formato"),
                        metodo:"phpAdmin",
                        section:$(this).attr("section")
                        }
                    );
                } else {
                    comunica("scp/reportes/ControladorReportesPresupuesto.php",{
                        rep_id:$(this).attr("rep_id"),
                        formato:$(this).attr("formato"),
                        metodo:"filtros",
                        section:$(this).attr("section")
                        }
                    );
                }
            });
        ');
    }

    /************* AQUI SE CAPTURAN LOS FILTROS DE  LOS REPORTES *******************/
    private function filtros()
    {
        $sql = 'SELECT * FROM rep_DireccionEgresos WHERE die_id = ?';
        $value_boton = '';
        $target = '_blank';
        $icon = '';
        switch ($this->prm->formato) {
            case 'PDF':
                $value_boton = 'PDF';
                $color = 'danger';
                $icon = 'fas fa-file-pdf';
                $target = 'det_fs' . $this->prm->rep_id;
                break;
            case 'EXCEL':
            case 'EXCEL2':
                $value_boton = 'EXCEL';
                $color = 'success';
                $icon = 'fas fa-file-excel';
                break;
            case 'WORD':
                $value_boton = 'Descargar';
                $color = 'info';
                $icon = '';
                break;
            case 'HTML':
                $value_boton = 'Ver';
                $color = 'default';
                $icon = '';
                $target = 'det_fs' . $this->prm->rep_id;
                break;
        }

        $this->conectar();
        $this->setSql($sql);
        $this->consultar([$this->prm->rep_id]);
        $data = $this->extraer();
        $this->desconectar();

        $ur = new StdClass();
        $ur->id = 'unidadresponsable';
        $ur->label = 'Unidad Responsable';
        $ur->placeholder = 'Unidad Responsable';
        $ur->maxlength = 3;
        $ur->formato = 'entero';
        $ur->enDiv = true;

        $ur_clave = new StdClass();
        $ur_clave->id = 'ur_clave';
        $ur_clave->label = 'Unidad Responsable';
        $ur_clave->placeholder = 'Unidad Responsable';
        $ur_clave->maxlength = 3;
        $ur_clave->formato = 'entero';
        $ur_clave->enDiv = true;

        $mesinicial = new stdClass();
        $mesinicial->sql = 'SELECT msc_id,msc_nombre FROM cat_MesContable WHERE msc_id BETWEEN 1 AND 12';
        $mesinicial->id = 'mesini';
        $mesinicial->search = true;
        $mesinicial->name = 'mesini';
        $mesinicial->label = 'Selecciona mes inicial';

        $tipoclc = new stdClass();
        $tipoclc->sql = 'SELECT tcl_id,tcl_nombre FROM cat_TipoClc';
        $tipoclc->id = 'tipocedula';
        $tipoclc->name = 'tipocedula';
        $tipoclc->search = true;
        $tipoclc->label = 'Selecciona el tipo de CLC';
        $tipoclc->class = 'required';

        $estadoclc = new stdClass();
        $estadoclc->sql = 'SELECT ecl_id,ecl_nombre FROM cat_EstadoClc';
        $estadoclc->id = 'ecl_id';
        $estadoclc->name = 'ecl_id';
        $estadoclc->label = 'Selecciona el tipo de CLC';

        $solomes = new stdClass();
        $solomes->sql = 'SELECT msc_id,msc_nombre FROM cat_MesContable WHERE msc_id BETWEEN 1 AND 12';
        $solomes->id = 'mesini';
        $solomes->name = 'mesini';
        $solomes->search = true;
        $solomes->label = 'Selecciona el mes';
        $solomes->class = 'required';

        $mesf = new stdClass();
        $mesf->sql = 'SELECT msc_id,msc_nombre FROM cat_MesContable WHERE msc_id BETWEEN 1 AND 12';
        $mesf->id = 'mesf';
        $mesf->name = 'mesf';
        $mesf->search = true;
        $mesf->label = 'Selecciona mes Final';

        $modalidad = new stdClass();
        $modalidad->id = 'modalidad';
        $modalidad->name = 'modalidad';
        $modalidad->enDiv = true;
        $modalidad->search = true;
        $modalidad->camposVal = 'mmd_id';
        $modalidad->agrupaPor = 'mmd_id';
        $modalidad->campoLabelAgrupacion = 'mmd_nombre';
        $modalidad->mensaje = 'Ambas';
        $modalidad->label = 'Modalidad de Modificacion';
        $modalidad->comoAtr = 'mmd_id';
        $modalidad->sql = 'SELECT mmd_id,mmd_nombre FROM cat_ModalidadModificacion';

        $tipo = new stdClass();
        $tipo->id = 'tipo';
        $tipo->name = 'tipo';
        $tipo->enDiv = true;
        $tipo->search = true;
        $tipo->camposVal = 'tmd_id';
        $tipo->agrupaPor = 'tmd_id';
        $tipo->campoLabelAgrupacion = 'tmd_nombre';
        $tipo->mensaje = 'Todas';
        $tipo->label = 'Tipo de Modificacion';
        $tipo->comoAtr = 'tmd_id';
        $tipo->sql = 'SELECT tmd_id,tmd_nombre FROM cat_TipoModificacion';

        $tipoObra = new stdClass();
        $tipoObra->id = 'toa_id';
        $tipoObra->name = 'toa_id';
        $tipoObra->search = true;
        $tipoObra->label = 'Tipo de Obra';
        $tipoObra->sql = 'SELECT toa_id,toa_nombre FROM cat_TipoObraActividad WHERE toa_id in(1,2)';

        $tipoObra2 = new stdClass();
        $tipoObra2->id = 'toa';
        $tipoObra2->name = 'toa';
        $tipoObra2->enDiv = true;
        $tipoObra2->search = true;
        $tipoObra2->camposVal = 'toa_id';
        $tipoObra2->agrupaPor = 'toa_id';
        $tipoObra2->campoLabelAgrupacion = 'tmd_nombre';
        $tipoObra2->mensaje = 'Todas';
        $tipoObra2->label = 'Tipo de Obra';
        $tipoObra2->opciones = ['1' => 'Operacion', '2' => 'Inversion'];

        $tipocompra = new stdClass();
        $tipocompra->id = 'tipocompra';
        $tipocompra->name = 'tipocompra';
        $tipocompra->enDiv = true;
        $tipocompra->search = true;
        $tipocompra->camposVal = 'tcp_id';
        $tipocompra->agrupaPor = 'tcp_id';
        $tipocompra->campoLabelAgrupacion = 'tcp_nombre';
        $tipocompra->mensaje = 'DIRECTAS Y CONSOLIDADAS';
        $tipocompra->label = 'Tipo de Compra';
        $tipocompra->comoAtr = 'tcp_id';
        $tipocompra->sql = "SELECT tcp_id,tcp_nombre FROm cat_TipoCompra WHERE tcp_id!='P'";

        //Similar a la anterior solo que agrega previsiones
        $tipocompraPrev = new stdClass();
        $tipocompraPrev->id = 'tcp_prev';
        $tipocompraPrev->name = 'tcp_prev';
        $tipocompraPrev->search = true;
        $tipocompraPrev->label = 'Tipo de Compra';
        $tipocompraPrev->sql = 'SELECT tcp_id,tcp_nombre FROm cat_TipoCompra';

        $tipoRecurso = new stdClass();
        $tipoRecurso->id = 'cff_id';
        $tipoRecurso->name = 'cff_id';
        $tipoRecurso->search = true;
        $tipoRecurso->label = 'Fuente de Financiamiento';
        $tipoRecurso->sql = "SELECT ter_id, CASE WHEN ter_id = 'A' THEN 'NO ETIQUETADO' ELSE 'ETIQUETADO' END as ter_nombre FROM sinpres.cat_TipoRecurso";

        $fechainicio = new stdClass();
        $fechainicio->id = 'inicial';
        $fechainicio->name = 'inicial';
        $fechainicio->class = 'fecha';
        $fechainicio->label = 'Inicio';
        $fechainicio->enDiv = true;
        $fechainicio->formato = 'fecha';
        $fechainicio->labelClass = '_32 txtL';

        $fechafin = new stdClass();
        $fechafin->id = 'final';
        $fechafin->name = 'final';
        $fechafin->class = 'fecha';
        $fechafin->label = 'Fin';
        $fechafin->enDiv = true;
        $fechafin->formato = 'fecha';
        $fechafin->labelClass = '_32 txtL';

        $cvefin = new stdClass();
        $cvefin->id = 'cvefin';
        $cvefin->name = 'cvefin';
        $cvefin->class = 'cvefin';
        $cvefin->label = 'Clave de Financiamiento';
        $cvefin->labelClass = '_32 txtL';
        $cvefin->enDiv = true;
        $cvefin->maxLength = 9;
        $cvefin->placeholder = 'XXXXX0000';

        $clcid = new stdClass();
        $clcid->id = 'clcid';
        $clcid->name = 'clcid';
        $clcid->class = 'required';
        $clcid->label = 'Numero de CLC';
        $clcid->required = true;
        $clcid->labelClass = '_32 txtL';
        $clcid->enDiv = true;
        $clcid->maxLength = 9;
        $clcid->formato = 'entero';

        $cuenta1 = new stdClass();
        $cuenta1->id = 'cuenta1';
        $cuenta1->name = 'cuenta1';
        $cuenta1->class = 'required';
        $cuenta1->label = 'CUENTA PRIMER NIVEL';
        $cuenta1->required = true;
        $cuenta1->labelClass = '_32 txtL';
        $cuenta1->enDiv = true;
        $cuenta1->maxLength = 5;
        $cuenta1->formato = 'entero';

        $cuenta4 = new stdClass();
        $cuenta4->id = 'cuenta4';
        $cuenta4->name = 'cuenta4';
        $cuenta4->class = 'required';
        $cuenta4->label = 'CUENTA CUARTO NIVEL';
        $cuenta4->required = true;
        $cuenta4->labelClass = '_32 txtL';
        $cuenta4->enDiv = true;
        $cuenta4->maxLength = 5;

        $edoclc = new stdClass();
        $edoclc->id = 'edoclc';
        $edoclc->name = 'edoclc';
        $edoclc->enDiv = true;
        $edoclc->search = true;
        $edoclc->camposVal = 'ecl_id';
        $edoclc->agrupaPor = 'ecl_id';
        $edoclc->comoAtr = 'ecl_id';
        $edoclc->opciones = [
            '1,2,3'                            => 'Generada, Autorizada y Enviada (UR)',
            '4,5,6'                            => 'Recibida, Revisada y Aplicada (SEFIN)',
            '7,8,9,10,11,12,13,14,15,16,17,18' => 'con CERE/CERECO y PAGADA.',
        ];
        $edoclc->label = 'Estado de la Cedula';
        $edoclc->mensaje = 'Todas';

        $tipocedula = new stdClass();
        $tipocedula->id = 'tipocedula';
        $tipocedula->name = 'tipocedula';
        $tipocedula->enDiv = true;
        $tipocedula->comoAtr = 'ecl_id';
        $tipocedula->opciones = ['01' => 'OPERACIÓN', '02' => 'INVERSIÓN'];
        $tipocedula->label = 'Tipo de Cedula';
        $tipocedula->mensaje = 'Todas';

        $periodo = new stdClass();
        $periodo->id = 'per_id';
        $periodo->name = 'per_id';
        $periodo->enDiv = true;
        $periodo->search = true;
        $periodo->camposVal = 'per_id';
        $periodo->agrupaPor = 'per_id';
        $periodo->comoAtr = 'per_id';
        $periodo->label = 'Seleccione periodo';
        $periodo->mensaje = 'Periodo';
        $periodo->sql = 'SELECT 1 AS per_id, "PRIMER PERIODO ENERO-MARZO"';
        $periodo->sql .= 'UNION ALL SELECT 2 AS per_id, "SEGUNDO PERIODO ENERO-JUNIO"';
        $periodo->sql .= 'UNION ALL SELECT 3 AS per_id, "TERCER PERIODO ENERO-SEPTIEMBRE"';
        $periodo->sql .= 'UNION ALL SELECT 4 AS per_id, "CUARTO PERIODO ENERO-DICIEMBRE"';
        $periodo->sql .= 'UNION ALL SELECT 5 AS per_id, "CIERRE (CUENTA PÚBLICA ' . APP_EJERCICIO . ')"';
        $periodo->class = 'required';

        $depid = new stdClass();
        $depid->id = 'dep_id';
        $depid->name = 'dep_id';
        $depid->enDiv = true;
        $depid->search = true;
        $depid->camposVal = 'dep_id';
        $depid->agrupaPor = 'dep_id';
        $depid->comoAtr = 'dep_id';
        $depid->label = 'Departamento';
        $depid->mensaje = 'Selecciona';
        $depid->class = 'required';
        $depid->sql = 'SELECT "44,45,46,48,49" AS dep_id,"TODOS" AS dep_nombre';
        $depid->sql .= ' UNION ALL SELECT 44 AS dep_id, "SEGUIMIENTO A PRESUPUESTO A" AS dep_nombre';
        $depid->sql .= ' UNION ALL SELECT 45 AS dep_id, "SEGUIMIENTO A PRESUPUESTO B" AS dep_nombre';
        $depid->sql .= ' UNION ALL SELECT 46 AS dep_id, "SEGUIMIENTO A PRESUPUESTO C" AS dep_nombre';
        $depid->sql .= ' UNION ALL SELECT 48 AS dep_id, "GESTION PRESUPUESTARIA A" AS dep_nombre';
        $depid->sql .= ' UNION ALL SELECT 49 AS dep_id, "GESTION PRESUPUESTARIA B" AS dep_nombre';

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'regresar';
        $btnRegresar->name = 'regresar';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->class = $this->btnclass;

        $grupo = new StdClass();
        $grupo->id = 'grupo';
        $grupo->label = 'Grupo';
        $grupo->placeholder = 'Grupo';
        $grupo->maxlength = 3;
        $grupo->formato = 'entero';
        $grupo->enDiv = true;

        $rangodias = new stdClass();
        $rangodias->id = 'rangodia3';
        $rangodias->name = 'rangodia3';
        $rangodias->label = 'Rango de dias';
        $rangodias->required = false;
        $rangodias->labelClass = '_32 txtL';
        $rangodias->enDiv = true;
        $rangodias->maxLength = 3;
        $rangodias->formato = 'entero';

        $generica = new StdClass();
        $generica->id = 'generica';
        $generica->maxlength = 3;
        $generica->label = 'Partida Genérica';
        $generica->placeholder = 'Partida Genérica';
        $generica->enDiv = true;
        $generica->required = true;


        $especifica = new StdClass();
        $especifica->id = 'especifica';
        $especifica->maxlength = 3;
        $especifica->label = 'Partida Especifica';
        $especifica->placeholder = 'Partida Especifica';
        $especifica->enDiv = true;
        $especifica->required = true;


        $host = 'scp/reportes/get_pdf.php';
        $html = '';
        $html .= '<div><h4>' . $data['die_nombre'] . '</h4></div>';
        $html .= '<form id="reportes" action="' . $host . '" method="post" target="' . $target . '" style="width:50%; border:1px solid #EAEAEA; padding:10px;">';
        $html .= '<input type="hidden" id="rep_id" name="rep_id" value="' . $this->prm->rep_id . '"/><br/>';
        /******************* ENCRIPTADO DE LA SESION *************************/

        $clave = '';
        $salt = '$BF8AsdL3t-cw13z20'; //LLAVE 2019
        //$salt .= isset($this->prm->rep_id) ? $this->prm->rep_id : "";
        $nose = md5($this->gpo_id . $salt . $this->ur_id);
        $clave = strtoupper($nose);

        $html .= '
            <input type="hidden" id = "hgpo" name="gpo_id" value="' . $this->gpo_id . '" />
            <input type="hidden" id = "hur" name="ur_id" value="' . $this->ur_id . '" />
            <input type="hidden" name="SSL875JJNF9" id="SSL875JJNF9" value="' . $clave . '" />
            <input type="hidden" name="numrep" value="' . (isset($this->prm->rep_id) ? $this->prm->rep_id : '') . '" />
            <input type="hidden" name="aplicacion" value="' . (isset($this->prm->formato) ? $this->prm->formato : 'PDF') . '" />
        ';
        /************** FIN ENCRIPTADO DE LA SESION *******************/
        $filtros = explode(',', $data['die_filtro']);

        foreach ($filtros as $item => $value) {
            $pos = strpos($value, '[');
            $parameters = '';

            if ($pos != false) {
                $pos2 = strpos($value, ']');
                $parameters = str_replace('-', ',', substr($value, $pos + 1, $pos2 - ($pos + 1)));
                $value = substr($value, 0, $pos);
            }

            switch ($value) {
                case 'toa_id':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponCombo($tipoObra) . '</fieldset></div > ';
                    break;
                case 'toa':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponCombo($tipoObra2) . '</fieldset></div > ';
                    break;
                case 'cff_id':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponCombo($tipoRecurso) . '</fieldset></div > ';
                    break;
                case 'periodo':
                    $html .= $this->ponCombo($periodo);
                    break;
                case 'dep_id':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponCombo($depid) . '</fieldset></div > ';
                    break;
                case 'ur':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponInput($ur) . '</fieldset></div > ';
                    break;
                case 'ur_clave':
                    $html .= $this->ponInput($ur_clave);
                    break;

                case 'tipocedula':
                    if ($parameters != '') {
                        if ($parameters == 'todas') {
                            $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponCombo($tipoclc) . '</fieldset></div > ';
                        } else {
                            $tipoclc->sql = 'SELECT tcl_id,tcl_nombre FROM cat_TipoClc WHERE tcl_id IN(' . $parameters . ')';
                            $html .= $this->ponCombo($tipoclc);
                        }
                    } else {
                        $html .= $this->ponCombo($tipocedula);
                    }
                    break;
                case 'edocedula':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponCombo($edoclc) . '</fieldset></div > ';
                    break;
                case 'cuenta1':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponInput($cuenta1) . '</fieldset></div > ';
                    break;
                case 'cuenta4':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponInput($cuenta4) . '</fieldset></div > ';
                    break;
                case 'clc':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponInput($clcid) . '</fieldset></div > ';
                    break;
                case 'tipocompra':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponCombo($tipocompra) . '</fieldset></div > ';
                    break;
                case 'tcp_prev':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponCombo($tipocompraPrev) . '</fieldset></div > ';
                    break;
                case 'mes':
                case 'mes2':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponCombo($solomes) . '</fieldset></div > ';
                    break;
                case 'cvefin':
                case 'cvefins':
                case 'cvefin2':
                case 'cvefin3':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponInput($cvefin) . '</fieldset></div > ';
                    break;
                case 'pormes':
                case 'pormes2':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponCombo($mesinicial) . '</fieldset></div > ';
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponCombo($mesf) . '</fieldset></div > ';
                    break;
                case 'rangodia':
                case 'rangodia2':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-6 col-xs-3">' . $this->ponInput($fechainicio) . '</fieldset></div > ';
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-6 col-xs-3">' . $this->ponInput($fechafin) . '</fieldset></div > ';
                    break;
                case 'rangodia3':
                    $html .= $this->ponInput($rangodias);
                    break;
                case 'modalidad':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponCombo($modalidad) . '</fieldset></div > ';
                    break;
                case 'modificacion':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponCombo($tipo) . '</fieldset></div > ';
                    break;
                case 'grupo':
                    $html .= $this->ponInput($grupo);
                    break;
                case 'generica':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponInput($generica) . '</fieldset></div > ';
                    break;
                case 'especifica':
                    $html .= '<div class="row mb-2"> <fieldset class="col-md-12 col-xs-12">' . $this->ponInput($especifica) . '</fieldset></div > ';
                    break;
            }
        }
        $html .= '<button id="imprimir" type="button" class="btn btn-outline-' . $color . ' me-1">
  <i class="' . $icon . '"></i> ' . $value_boton . '
</button>';
        $html .= $this->ponBoton($btnRegresar) . '</form>';
        $html .= '
            <script type="text/javascript" id="scri_pdf"> </script>
            <script type="text/javascript">
                $(document).keyup(function (e){
                    if ( e.which == 27 ) {
                        $(".documento_fs").animate({opacity:0,height:"0px",width:"0px"},400,function(){ $(".documento_fs").remove(); });
                    }
                });
            </script>
        ';
        $this->mixHtml($html);
        $this->mixScript('
            $("#combour").change(function(){
                if($("#unidadresponsable").length ==1){
                    $("#unidadresponsable").val($(this).val());
                }
                $("#hgpo").val( $("#combour").find("option:selected").attr("gpo_id"));
                $("#hur").val( $("#combour").find("option:selected").attr("ur_id"));
                comunica("' . $this->pantalla . '",{metodo:"changeUr",rep_id:"' . $this->prm->rep_id . '"});
            });
            $("#combour").change();

            $("#regresar").click(function(){
                comunica("scp/reportes/ControladorReportesPresupuesto.php",{section:"' . $this->prm->section . '"});
            });

            $("#imprimir").click(function(){

             
                var enviar = true;
                if($("#reportes input[required]").length > 0){
                $("#reportes input[required]").each(function(){
                   if(!$.trim($(this).val()) && $(this).attr("name") !== undefined){
                        enviar  = false;
                        $("#"+ $(this).attr("name")).focus(); 
                    }
                   }
                  )
                }  
                $(".required").each(function(){
                    if(!$.trim($(this).val()) && $(this).attr("name") !== undefined){
                        enviar  = false;
                        $(this).css("border","1px solid #F00");
                    }
                });
                if (!enviar) {
                    notify("Existen campos vacíos!!!","error");
                    return false;
                }
                comunica("scp/reportes/ControladorReportesPresupuesto.php",{
                    "metodo":"sl3",
                    sltres:$("#SSL875JJNF9").val()
                });
                if ($("#reportes").attr("target") !="_blank") {
                    load_viewer(' . $this->prm->rep_id . ',"' . $data['die_nombre'] . '","frame");
                }
                $("#reportes").submit();
            });
        ');
    }

    /************* AQUI SE ACTIVAN LAS OPCIONES DE LOS REPORTES *******************/
    private function activaopcion()
    {
        /*    1: ES ACTIVO             2 ES DESACTIVADO             */
        if ($this->prm->rep_id == 8 && ($this->prm->option != 'die_pdf' && $this->prm->option != 'die_excel' && $this->prm->option != 'sts_id')) {
            $this->mixScript('notify("Esta opción no esta disponible para este reporte!!!","warning");');
            return;
        }
        $sql = 'UPDATE rep_DireccionEgresos SET ' . $this->prm->option . ' = ' . $this->prm->valor . ' ';
        if ($this->prm->option == 'sts_id' && $this->prm->valor == 1) {
            $sql .= ',die_manto="N" ';
        }
        $sql .= 'WHERE die_id=' . $this->prm->rep_id;
        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();
        $this->mixScript('
            notify("Opción actualizada correctamente","success");
            comunica("scp/reportes/ControladorReportesPresupuesto.php");
        ');
    }

    /************* AQUI SE PROCESAN LOS REPORTES EN PHP *******************/
    private function phprep()
    {
        $unidad = new StdClass();
        $unidad->id = 'unidad';
        $unidad->name = 'unidad';
        $unidad->label = 'Unidad Ejecutora';
        $unidad->placeholder = 'Unidad Ejecutora';
        $unidad->maxlength = 3;
        $unidad->enDiv = true;
        $unidad->formato = 'entero';

        $ur = new StdClass();
        $ur->id = 'unidadresponsable';
        $ur->id = 'gpour';
        $ur->label = 'Unidad Responsable';
        $ur->placeholder = 'Unidad Responsable';
        $ur->maxlength = 3;
        $ur->formato = 'entero';
        $ur->enDiv = true;

        $ur_clave = new StdClass();
        $ur_clave->id = 'ur_clave';
        $ur_clave->label = 'Unidad Responsable';
        $ur_clave->placeholder = 'Unidad Responsable';
        $ur_clave->maxlength = 3;
        $ur_clave->formato = 'entero';
        $ur_clave->enDiv = true;

        $programa = new StdClass();
        $programa->id = 'programa';
        $programa->name = 'programa';
        $programa->label = 'Programa';
        $programa->maxlength = 3;
        $programa->placeholder = 'Programa';
        $programa->enDiv = true;
        $programa->formato = 'entero';

        $subprograma = new StdClass();
        $subprograma->id = 'subprograma';
        $subprograma->label = 'Subprograma';
        $subprograma->maxlength = 2;
        $subprograma->placeholder = 'SubPrograma';
        $subprograma->enDiv = true;
        $subprograma->formato = 'entero';

        $py = new StdClass();
        $py->id = 'py';
        $py->label = 'Proyecto';
        $py->maxlength = 3;
        $py->placeholder = 'Proyecto';
        $py->enDiv = true;
        $py->formato = 'entero';

        $oa = new StdClass();
        $oa->id = 'oa';
        $oa->label = 'Obra Actividad';
        $oa->maxlength = 3;
        $oa->placeholder = 'Obra Actividad';
        $oa->enDiv = true;
        $oa->formato = 'entero';

        $clasificacion = new StdClass();
        $clasificacion->id = 'clasificacion';
        $clasificacion->maxlength = 2;
        $clasificacion->label = 'Clasificación';
        $clasificacion->placeholder = 'Clasificación';
        $clasificacion->enDiv = true;

        $fuente = new StdClass();
        $fuente->id = 'fuente';
        $fuente->maxlength = 1;
        $fuente->label = 'Fuente';
        $fuente->placeholder = 'Fuente';
        $fuente->enDiv = true;

        $subfuente = new StdClass();
        $subfuente->id = 'subfuente';
        $subfuente->maxlength = 1;
        $subfuente->label = 'Subfuente';
        $subfuente->placeholder = 'Subfuente';
        $subfuente->enDiv = true;

        $origen = new StdClass();
        $origen->id = 'origen';
        $origen->maxlength = 1;
        $origen->label = 'Origen';
        $origen->placeholder = 'Origen';
        $origen->enDiv = true;

        $suborigen = new StdClass();
        $suborigen->id = 'suborigen';
        $suborigen->maxlength = 1;
        $suborigen->label = 'Suborigen';
        $suborigen->placeholder = 'Suborigen';
        $suborigen->enDiv = true;

        $cfi_anio = new StdClass();
        $cfi_anio->id = 'cfi_anio';
        $cfi_anio->maxlength = 2;
        $cfi_anio->minlength = 2;
        $cfi_anio->label = 'Año';
        $cfi_anio->placeholder = '14';
        $cfi_anio->enDiv = true;

        $referencia = new StdClass();
        $referencia->id = 'referencia';
        $referencia->maxlength = 2;
        $referencia->minlength = 2;
        $referencia->label = 'Referencia';
        $referencia->placeholder = 'Referencia';
        $referencia->enDiv = true;

        $generica = new StdClass();
        $generica->id = 'generica';
        $generica->maxlength = 3;
        $generica->label = 'Partida Genérica';
        $generica->placeholder = 'Partida Genérica';
        $generica->enDiv = true;

        $especifica = new StdClass();
        $especifica->id = 'especifica';
        $especifica->maxlength = 3;
        $especifica->label = 'Partida Especifica';
        $especifica->placeholder = 'Partida Especifica';
        $especifica->enDiv = true;

        $compra = new stdClass();
        $compra->sql = 'SELECT tcp_id,tcp_nombre from cat_TipoCompra where tcp_id != "P"';
        $compra->id = 'compra';
        $compra->search = true;
        $compra->name = 'compra';
        $compra->label = 'Selecciona el Tipo de Compra';

        $presupuesto = new stdClass();
        $presupuesto->id = 'presupuesto';
        $presupuesto->opciones = [1 => 'Comprometido', 2 => 'Devengado', 3 => 'Ejercido'];
        $presupuesto->search = true;
        $presupuesto->name = 'presupuesto';
        $presupuesto->label = 'Selecciona el Tipo de Presupuesto';

        $mesinicial = new stdClass();
        $mesinicial->sql = 'SELECT msc_id,msc_nombre FROM cat_MesContable WHERE msc_id BETWEEN 1 AND 12';
        $mesinicial->id = 'mesini';
        $mesinicial->name = 'mesini';
        $mesinicial->search = true;
        $mesinicial->label = 'Selecciona mes inicial';

        $mesf = new stdClass();
        $mesf->sql = 'SELECT msc_id,msc_nombre FROM cat_MesContable WHERE msc_id BETWEEN 1 AND 12';
        $mesf->id = 'mesf';
        $mesf->search = true;
        $mesf->name = 'mesf';
        $mesf->label = 'Selecciona mes Final';

        $limpiar = new stdClass();
        $limpiar->id = 'limpiar';
        $limpiar->color = 'verde';
        $limpiar->text = 'limpiar';
        $limpiar->value = 'Limpiar';
        $limpiar->attr = new stdClass();
        $limpiar = $this->ponBoton($limpiar);

        $imprimir = new stdClass();
        $imprimir->class = $this->prm->formato == 'EXCEL' ? 'btn btn-outline-success me-1' : 'btn btn-outline-danger me-1';
        $imprimir->icon = $this->prm->formato == 'EXCEL' ? 'fa-solid fa-file-excel me-1' : 'fa-solid fa-file-pdf me-1';
        $imprimir->id = 'imprimir';
        $imprimir->name = 'imprimir';
        $imprimir->value = $this->prm->formato == 'EXCEL' ? 'EXCEL' : 'PDF';
        $imprimir = $this->ponBoton($imprimir);

        $grupo = new StdClass();
        $grupo->id = 'grupo';
        $grupo->label = 'Grupo';
        $grupo->placeholder = 'Grupo';
        $grupo->maxlength = 3;
        $grupo->formato = 'entero';
        $grupo->enDiv = true;

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'regresar';
        $btnRegresar->class = $this->btnclass;
        $btnRegresar->name = 'regresar';
        $btnRegresar->icon = 'fa-solid fa-circle-left me-2';
        $btnRegresar->value = 'Regresar';

        $tipo = '';
        $html = '';
        if ($this->prm->formato == 'PDF') {
            switch ($this->prm->rep_id) {
                case 8:
                case 103:
                    $tipo = 'rep/doc/scp/reportes/PresupuestoDisponibleClavePresupuestal.php';
                    break;
                case 47:
                    $tipo = 'rep/doc/scp/reportes/PresupuestoPorComprometerCvePresupuestal.php';
                    break;
                case 126:
                    $tipo = 'rep/doc/scp/reportes/PresupuestoPorCvePresupuestalUr.php';
                    break;
            }
        } else {
            switch ($this->prm->rep_id) {
                case 8:
                case 103:
                case 126:
                    $tipo = 'rep/excel/scp/reportes/PresupuestoDisponibleClavePresupuestal.php';
                    break;
                case 47:
                    $tipo = 'rep/excel/scp/reportes/PresupuestoPorComprometerCvePresupuestal.php';
                    break;
                case 124:
                    $tipo = 'rep/excel/scp/reportes/ComprasConsolidadas.php';
                    break;
            }
        }

        $html = '<h3>PRESUPUESTO CALENDARIZADO DISPONIBLE POR CLAVE PRESUPUESTAL</h3>';
        $html .= '<span style="color:#900"> PARA ESTE REPORTE EL PRESUPUESTO SE TOMA DE MANERA <strong>TRIMESTRAL</strong> YA QUE EL PRESUPUESTO DE EJERCE DE ';
        $html .= 'MANERA TRIMESTRAL, SI SE MUESTRAN CIFRAS NEGATIVAS EN UN MES, TIENE QUE HACER LA SUMA DEL TRIMESTRE PARA SACAR SU DISPONIBLE TRIMESTRAL. CUALQUIER DUDA CON SU ANALISTA DE LA DIRECCIÓN DE PRESUPUESTO.</span>';
        $html .= '<form  id="filtros" action="' . $tipo . '" method="post" target="' . ($this->prm->formato == 'PDF' ? 'det_fs' . $this->prm->rep_id : '_blank') . '">';
        $html .= '
            <legend>Encuadre Programatico</legend>
            <div class="row">
                ' . ($this->nvj > 2 && ($this->prm->rep_id == 103 || $this->prm->rep_id == 126) ?
            '<fieldset class="col-md-2">' . $this->ponInput($ur) . '</fieldset>'
            : '<input type="hidden" value="' . $this->prm->rep_id . '" name="rep_id" />') . '
                <fieldset class="col-md-2">' . $this->ponInput($unidad) . '</fieldset>
                <fieldset class="col-md-2">' . $this->ponInput($programa) . '</fieldset>
                <fieldset class="col-md-2">' . $this->ponInput($subprograma) . '</fieldset>
                <fieldset class="col-md-2">' . $this->ponInput($py) . '</fieldset>
                <fieldset class="col-md-2">' . $this->ponInput($oa) . '</fieldset>
            </div>
            <legend>Clave de Financiamiento</legend>
            <div class="row">
                <fieldset class="col-md-2">' . $this->ponInput($clasificacion) . '</fieldset>
                <fieldset class="col-md-2">' . $this->ponInput($fuente) . '</fieldset>
                <fieldset class="col-md-2">' . $this->ponInput($subfuente) . '</fieldset>
                <fieldset class="col-md-2">' . $this->ponInput($origen) . '</fieldset>
                <fieldset class="col-md-2">' . $this->ponInput($suborigen) . '</fieldset>
                <fieldset class="col-md-1">' . $this->ponInput($referencia) . '</fieldset>
                <fieldset class="col-md-1">' . $this->ponInput($cfi_anio) . '</fieldset>
            </div>
            <legend>Partida Presupuestal</legend>
            <div class="row">
                <fieldset class="col-md-2">' . $this->ponInput($generica) . '</fieldset>
                <fieldset class="col-md-2">' . $this->ponInput($especifica) . '</fieldset>
                <fieldset class="col-md-3">' . $this->ponCombo($compra) . '</fieldset>
                <fieldset class="col-md-3">' . $this->ponCombo($presupuesto) . '</fieldset>
                <fieldset class="col-md-3">
                    Mostrar Claves en $ 0.00 ?
                    <div class="squaredTwo" style="display: inline-block;text-align: center;vertical-align: middle;margin-left: 8px;">
                <input type="checkbox" value="None" id="squaredTwo" name="check" style="display:none" />
                <label for="squaredTwo"></label>
                </div> <br><span id="mf" style="display:block">
                </fieldset>';
        $html .= '</div>
            <div class="row">
                <fieldset class="col-md-12">
                    <br>
                    ' . $imprimir . '
                    ' . $this->ponBoton($btnRegresar) . '
                </fieldset>
            </div>
            </form>
            <script type="text/javascript" id="scri_pdf" />
            <script type="text/javascript">
                $(document).keyup(function (e){
                    if ( e.which == 27 )
                        $(".documento_fs").animate({opacity:0,height:"0px",width:"0px"},400,function(){ $("#fs"+id).remove(); });
                });
            </script>
        ';
        $this->mixHtml($html);
        $this->mixScript('
            $("#imprimir").click(function(){
                tot = 0;
                $(".form-control").each(function(){
                    if ($(this).attr("id") != undefined &&  $(this).attr("id") != "combour") {
                        if ( $("#"+$(this).attr("id")).val() != "" ){
                            tot ++;
                            console.log($("#"+$(this).attr("id")).val());
                        }
                    }
                });
                if (tot > 1) {
                    envia();
                } else {
                    alertify.confirm("TIEMPO DE EJECUCION", "Ha elegido "+ tot +" filtros, esto puede causar un tiempo muy grande al procesar su reporte, desea continuar?",
                    function(){
                    envia();
                    },function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                }
            });
            function envia(){
                if ($("#filtros").attr("target") !="_blank" ) {
                    load_viewer(' . $this->prm->rep_id . ',"PRESUPUESTO CALENDARIZADO DISPONIBLE POR CLAVE PRESUPUESTAL","frame");
                    $("#filtros").submit();
                } else
                    $("#filtros").submit();
            }
            $("#regresar").click(function(){
                comunica("scp/reportes/ControladorReportesPresupuesto.php",{section:"' . $this->prm->section . '"});
            });
        ');
    }

    private function phprepAdmin()
    {
        $imprimir = new stdClass();
        $imprimir->class = $this->prm->formato == 'EXCEL' ? 'btn btn-outline-success me-1' : 'btn btn-outline-danger me-1';
        $imprimir->icon = $this->prm->formato == 'EXCEL' ? 'fa-solid fa-file-excel' : 'fa-solid fa-file-pdf';
        $imprimir->id = 'imprimir';
        $imprimir->name = 'imprimir';
        $imprimir->value = $this->prm->formato == 'EXCEL' ? 'Exportar' : 'Imprimir';
        $imprimir = $this->ponBoton($imprimir);

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'regresar';
        $btnRegresar->class = $this->btnclass;
        $btnRegresar->name = 'regresar';
        $btnRegresar->icon = 'fa-solid fa-circle-left me-2';
        $btnRegresar->value = 'Regresar';

        $tipo = '';
        $html = '';
        if ($this->prm->formato == 'PDF') {
            switch ($this->prm->rep_id) {
                case 124:
                    $tipo = 'rep/doc/scp/reportes/PresupuestoDisponibleClavePresupuestal.php';
                    break;
            }
        } else {
            switch ($this->prm->rep_id) {
                case 124:
                    $tipo = 'rep/excel/scp/reportes/ComprasConsolidadas.php';
                    break;
            }
        }
        $html .= '<form  id="filtros" action="' . $tipo . '" method="post" target="' . ($this->prm->formato == 'PDF' ? 'det_fs' . $this->prm->rep_id : '_blank') . '">';
        $html .= '</div>
            <div class="row">
                <fieldset class="col-md-12">
                    <br>
                    ' . $imprimir . '
                    ' . $this->ponBoton($btnRegresar) . '
                </fieldset>
            </div>
            </form>
            <script type="text/javascript" id="scri_pdf" />
            <script type="text/javascript">
                $(document).keyup(function (e){
                    if ( e.which == 27 )
                        $(".documento_fs").animate({opacity:0,height:"0px",width:"0px"},400,function(){ $("#fs"+id).remove(); });
                });
            </script>
        ';
        $this->mixHtml($html);
        $this->mixScript('
            $("#imprimir").click(function(){
                tot = 0;
                $(".form-control").each(function(){
                    if ($(this).attr("id") != undefined &&  $(this).attr("id") != "combour") {
                        if ( $("#"+$(this).attr("id")).val() != "" ){
                            tot ++;
                            console.log($("#"+$(this).attr("id")).val());
                        }
                    }
                });
                if (tot > 1) {
                    envia();
                } else {
                    alertify.confirm("TIEMPO DE EJECUCION", "Ha elegido "+ tot +" filtros, esto puede causar un tiempo muy grande al procesar su reporte, desea continuar?",
                        function(){
                            console.log("entro");
                            envia();
                        },
                        function(){
                        }).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                }
            });
            function envia(){
                if ($("#filtros").attr("target") !="_blank" ) {
                    load_viewer(' . $this->prm->rep_id . ',"PRESUPUESTO CALENDARIZADO DISPONIBLE POR CLAVE PRESUPUESTAL","frame");
                    $("#filtros").submit();
                } else
                    $("#filtros").submit();
            }
            $("#regresar").click(function(){
                comunica("scp/reportes/ControladorReportesPresupuesto.php",{section:"' . $this->prm->section . '"});
            });
        ');
    }

    /************* AQUI SE ENVIA LA SESION A LA BASE DE DATOS *******************/
    private function sl3()
    {
        $this->conectar();
        $sql = 'DELETE FROM ' . BD_TEMPORAL . '.tem_SessionReporte WHERE ssr_id = ?';

        $this->setSql($sql);
        $this->consultar([$this->prm->sltres]);
        $sql = 'INSERT INTO ' . BD_TEMPORAL . '.tem_SessionReporte VALUES( ? )';
        $this->setSql($sql);
        $this->consultar([$this->prm->sltres]);
        $this->desconectar();
    }

    private function changeUr()
    {
        if ($this->nvj < 3) {
            return false;
        }

        $clave = '';
        $salt = '$BF8AsdL3t-cw13z20'; //LLAVE
        //$salt .= isset($this->prm->rep_id) ? $this->prm->rep_id : "";
        $nose2 = $this->gpo_id . $salt . $this->ur_id;
        $nose = md5($this->gpo_id . $salt . $this->ur_id);
        $clave = strtoupper($nose);
        $this->mixScript('
            $("#SSL875JJNF9").val("' . $clave . '");
        ');
    }
} // Fin class

if ($_SESSION[_S]['prm']['S']['4']['1']) {
    new ControladorReportesPresupuesto();
} else {
    WebResponse::sinPermisos();
}
