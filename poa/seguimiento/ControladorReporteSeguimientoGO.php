<?php

/**
 * Description of ControladorRegistroModificaciones
 *
 * @author ?
 * Collaborators: Adriana Pacheco Castillo <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Modif: 08-Enero-2022
 * Comment: -----
 * Objective:
- Reportes
- Mensual
- Trimestral
- Separación reporte seguimiento de gasto de operación de controlador reporte varios
 * Regla de Negocio: -----
 * Permits: Esta pantalla tiene permisos de lectura.
 **/

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';

class ControladorReporteSeguimientoGO extends WebResponse
{
    private $nvj;
    private $usu;
    private $id;
    private $gpo_id;
    private $ur_id;
    private $clasAdm_gpo;
    private $clasAdm_ur;
    private $server2;
    private $opcionCons;
    private $noEtiq;
    private $porc1;
    private $porc2;
    private $porc3;
    private $clase;
    private $iconS;
    private $iconD;
    private $tituloE;
    private $claseIn;
    private $tituloPdf;
    private $iconoPdf;
    private $claseSec;
    private $usingSR;
    private $tablaSuborigen;
    private $vigente;
    private $eje;

    public function __construct()
    {
        // constructor
        parent::__construct();

        // variables de session
        $this->nvj = $_SESSION[_S]['nvj_id'];
        $this->usu = isset($_SESSION[_S]['usu']) ? $_SESSION[_S]['usu'] : '';
        $this->id = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : '';
        $this->gpo_id = $_SESSION[_S]['gpo_id'];
        $this->ur_id = $_SESSION[_S]['ur_id'];
        $this->clasAdm_gpo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->clasAdm_ur = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : '';
        $this->opcionCons = 'Seleccione una opción de la lista';
        $this->noEtiq = 'NO ETIQUETADO';
        $this->porc1 = '0-49 %';
        $this->porc2 = '50-79 %';
        $this->porc3 = '80-100';
        $this->clase = 'btn btn-outline-secondary me-1';
        $this->iconS = 'fa fa-search me-2';
        $this->iconD = 'fa fa-cloud-download me-2';
        $this->tituloE = 'Exportar a Excel';
        $this->claseIn = 'btn btn-xs btn-outline-info me-1';
        $this->tituloPdf = 'Imprimir en PDF';
        $this->iconoPdf = 'fa-solid fa-file-pdf me-2';
        $this->claseSec = 'btn btn-xs btn-outline-secondary me-1';
        $this->usingSR = 'USING ( cff_id,fte_id,sft_id,ori_id,sor_id )';
        $this->tablaSuborigen = '.cat_SuborigenRecurso';
        $this->vigente = '+vig_';
        $this->eje = '+eje_';

        switch ($this->nvj) {
            case 1:case 2:case 3:case 4:case 5:case 6:case 7:case 8:case 9:
                switch ($this->metodo) {
                    default:$this->mainReportes();
                        break;
                    case 'seleccionaCorte':
                        $this->seleccionaCorte();
                        break;
                    case 'seleccionaperiodo':
                        $this->seleccionaperiodo();
                        break;
                    case 'print':
                        $this->imprime();
                        break;
                    case 'verPantallaSeg2':
                        $this->verPantallaSeg2();
                        break;
                    case 'llenaTabla2':
                        $this->llenaTabla2();
                        break;
                    case 'verPantallaSeg3':
                        $this->verPantallaSeg3();
                        break;
                    case 'llenaTabla3':
                        $this->llenaTabla3();
                        break;
                    case 'verPantallaSeg4':
                        $this->verPantallaSeg4();
                        break;
                    case 'llenaTabla4':
                        $this->llenaTabla4();
                        break;
                    case 'verPantallaSegM2':
                        $this->verPantallaSegM2();
                        break;
                    case 'llenaTablaSegM2':
                        $this->llenaTablaSegM2();
                        break;
                    case 'verPantallaSegM3':
                        $this->verPantallaSegM3();
                        break;
                    case 'llenaTablaSegM3':
                        $this->llenaTablaSegM3();
                        break;
                    case 'verPantallaSegM4':
                        $this->verPantallaSegM4();
                        break;
                    case 'llenaTablaSegM4':
                        $this->llenaTablaSegM4();
                        break;
                }
                break;
            default:
                break;
        }
        $this->returnJson();
    }

    public function mainReportes()
    {
        $this->mixScript('
            comunica("' . $this->pantalla . '", {
                metodo:"seleccionaCorte"
            })
        ');
    } //Fin mainReportes

    //Metodo que se usa para seleccionar el tipo de corte que se consultara.
    private function seleccionaCorte()
    {
        //Combo de selección de tipo de corte
        $comboPeriodo = new stdClass();
        $comboPeriodo->id = 'comboPeriodo';
        $comboPeriodo->name = 'comboPeriodo';
        $comboPeriodo->enDiv = true;
        $comboPeriodo->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboPeriodo->label = 'Periodo';
        $comboPeriodo->opciones = [
            '' => $this->opcionCons,
            1  => 'Mensual',
            2  => 'Trimestral',
        ];
        $comboPeriodo->search = true;

        $comboPeriodo->change = '
            if( $(this).val() != "" ){
                comunica("' . $this->pantalla . '",{
                    metodo : "seleccionaperiodo",
                    opc_id : $("#comboPeriodo").val()
                })
            }
        ';
        $comboTipoPeriodo = new stdClass();
        $comboTipoPeriodo->id = 'comboTipoPeriodo';
        $comboTipoPeriodo->name = 'comboTipoPeriodo';
        $comboTipoPeriodo->enDiv = true;
        $comboTipoPeriodo->mensaje = 'SELECCIONE UNA OPCIÓN';
        $comboTipoPeriodo->opciones = [];
        $comboTipoPeriodo->search = true;
        //Inicialización del combbo
        $this->mixHtml('
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->ponCombo($comboPeriodo) . '
                </fieldset>
            </div>
        ');
        //Java para mostrar la información del corte seleccionado
        if (!empty($this->prm->opc_id)) {
            $this->mixScript('
                comunica("' . $this->pantalla . '",{
                    metodo : "seleccionaperiodo",
                    opc_id : $("#comboPeriodo").val()
                });
            ');
        }
    } // Fin seleccionar corte

    //Función que rellena la información del combo de acuerdo al corte seleccionado
    public function seleccionaperiodo()
    {
        //botón que regresa a la pantalla anterior
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = 'btn btn-outline-secondary';
        $btnregresarindex->icon = 'fa-solid fa-circle-left me-2';
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "seleccionaCorte",
            });
        ';
        //Se declara el botón en el panel de herramientas
        $this->agregaTools($this->ponboton($btnregresarindex));
        //condicíón para evitar valores nulos
        $this->prm->opc_id = isset($this->prm->opc_id) ? $this->prm->opc_id : '';
        //SI LA OPCION ES 2, MOSTRARA LAS OPCIONES TRIMESTRALES
        if ($this->prm->opc_id == 2) {
            //Listado de reportes Trimestral
            $this->mixHtml('
                <div class="container">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th colspan="2" align="center">REPORTES TRIMESTRALES</th>
                            </tr>
                            <tr>
                                <th class="success">PDF</th>
                                <th  class="success">NOMBRE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!--<tr>
                                <td align="center">
                                    <button id="pantallaS1" class="btn" id="seguimiento1" nombre="" title="">
                                        <i class="glyphicon glyphicon-eye-open"></i></button>
                                </td>
                                <td><b>UR.-PROG.-SPROG</b></td>
                                <div id="ReporteSG1"></div>
                            </tr>-->
                            <tr>
                                <td align="center">
                                    <button id="pantallaS2" class="btn btn-outline-secondary" id="seguimiento2" nombre="" title="">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </td>
                                <td><b>UR.-PROG.-SPROG.-PY.-OBRA O ACTIVIDAD</b></td>
                                <div id="ReporteSG2"></div>
                            </tr>
                            <!-- <tr> se modifica para el ejercicio 2020
                                <td align="center">
                                    <button id="pantallaS3" class="btn" id="seguimiento3" nombre="" title="">
                                        <i class="glyphicon glyphicon-eye-open"></i></button>
                                </td>
                                <td><b>UR.-PROG.-SPROG.-PY.-OBRA O ACTIVIDAD-CAPÍTULO(COMPARATIVO)</b></td>
                                <div id="ReporteSG3"></div>
                            </tr> -->
                            <tr>
                                <td align="center">
                                    <button id="pantallaS3" class="btn btn-outline-secondary" id="seguimiento3" nombre="" title="">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </td>
                                <td><b>UR.-UE- CAPÍTULO(COMPARATIVO)</b></td>
                                <div id="ReporteSG3"></div>
                            </tr>
                            <tr>
                                <td align="center">
                                    <button id="pantallaS4" class="btn btn-outline-secondary" id="seguimiento4" nombre="" title="">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </td>
                                <td><b>ADECUACIONES PRESUPUESTALES</b></td>
                                <div id="ReporteSG4"></div>
                            </tr>
                            <!-- <tr> se modifica para el ejercicio 2020
                                <td align="center">
                                    <button id="pantallaS5" class="btn" id="seguimiento5" nombre="" title="">
                                        <i class="glyphicon glyphicon-eye-open"></i></button>
                                </td>
                                <td><b>OBSERVACIONES</b></td>
                                <div id="ReporteSG5"></div>
                            </tr> -- >
                        </tbody>
                        </b>
                    </table>
                </div>
                <div id="div_ues"></div>
            ');

            $this->mixScript('
                $("#pantallaS1").click(function(){
                    comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{metodo:"verPantallaSeg1"});
                });

                $("#pantallaS2").click(function(){
                    comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{
                        metodo:"verPantallaSeg2",
                        rep_id:48
                    });
                });

                $("#pantallaS3").click(function(){
                    comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{metodo:"verPantallaSeg3"});
                });

                $("#pantallaS4").click(function(){
                    comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{metodo:"verPantallaSeg4"});
                });

                $("#pantallaS5").click(function(){
                    comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{metodo:"verPantallaSegObs"});
                });
            ');
        } else {

            if ($this->prm->opc_id == 1) {
                //Listado de reportes mensuales
                $this->mixHtml('
                    <div class="container">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th colspan="2" align="center">REPORTES MENSUALES</th>
                                </tr>
                                <tr>
                                    <th class="success">PDF</th>
                                    <th  class="success">NOMBRE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!--<tr>
                                    <td align="center">
                                        <button id="pantallaSM1" class="btn" id="seguimientoM1" nombre="" title="">
                                            <i class="glyphicon glyphicon-eye-open"></i></button>
                                    </td>
                                    <td><b>UR.-PROG.-SPROG</b></td>
                                    <div id="ReporteSGM1"></div>
                                </tr>-->
                                <tr>
                                    <td align="center">
                                        <button id="pantallaSM2" class="btn btn-outline-secondary" id="seguimientoM2" nombre="" title="">
                                            <i class="fa-solid fa-eye"></i>
                                        </i></button>
                                    </td>
                                    <td><b>UR.-PROG.-SPROG.-PY.-OBRA O ACTIVIDAD</b></td>
                                    <div id="ReporteSGM2"></div>
                                </tr>
                                <!-- <tr>
                                    <td align="center">
                                        <button id="pantallaSM3" class="btn" id="seguimientoM3" nombre="" title="">
                                            <i class="glyphicon glyphicon-eye-open"></i></button>
                                    </td>
                                    <td><b>UR.-PROG.-SPROG.-PY.-OBRA O ACTIVIDAD-CAPÍTULO(COMPARATIVO)</b></td>
                                    <div id="ReporteSGM3"></div>
                                </tr> -->
                                <tr>
                                    <td align="center">
                                        <button id="pantallaSM3" class="btn btn-outline-secondary" id="seguimientoM3" nombre="" title="">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </td>
                                    <td><b>UR.-UE- CAPÍTULO(COMPARATIVO)</b></td>
                                    <div id="ReporteSGM3"></div>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <button id="pantallaSM4" class="btn btn-outline-secondary" id="seguimientoM4" nombre="" title="">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </td>
                                    <td><b>ADECUACIONES PRESUPUESTALES</b></td>
                                    <div id="ReporteSGM4"></div>
                                </tr>
                            </tbody>
                            </b>
                        </table>
                    </div>
                ');

                $this->mixScript('
                    $("#pantallaSM2").click(function(){
                        comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{
                            metodo:"verPantallaSegM2",
                            rep_id:112
                        });
                    });

                    $("#pantallaSM3").click(function(){
                        comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{
                            metodo:"verPantallaSegM3",
                            rep_id:113
                        });
                    });

                    $("#pantallaSM4").click(function(){
                        comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{
                            metodo:"verPantallaSegM4",
                            rep_id:104
                        });
                    });
                ');
            }
        }
    } //Fin seleccionar seleccionaperiodo

    private function pantallaGeneralM()
    {
        $this->setContenedor('divContenedorGeneralModal');
        //filtro por mes
        $comboMes = new stdClass();
        $comboMes->id = 'comboMes';
        $comboMes->label = 'Mes';
        $comboMes->mensaje = 'SELECCIONE EL MES A CONSULTAR';
        $comboMes->opciones = ['' => $this->opcionCons, 1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
        $comboMes->width = '400px';
        $comboMes->selecciona = (isset($this->prm->mes) && $this->prm->mes != '') ? $this->prm->mes : 0;
        $comboMes->search = true;

        $ur = new stdClass();
        $ur->id = 'ur';
        $ur->label = 'UR';
        $ur->maxlength = 3;
        $ur->formato = 'entero';

        $claveFinanciamiento = new stdClass();
        $claveFinanciamiento->id = 'cfi_clave';
        $claveFinanciamiento->label = 'CVEFIN';
        $claveFinanciamiento->maxlength = 10;

        $ue = new stdClass();
        $ue->id = 'ue';
        $ue->label = 'UE';
        $ue->maxlength = 3;
        $ue->formato = 'entero';

        $programa = new stdClass();
        $programa->id = 'programa';
        $programa->label = 'Programa';
        $programa->maxlength = 3;
        $programa->formato = 'entero';

        $subprograma = new stdClass();
        $subprograma->id = 'subprograma';
        $subprograma->label = 'Subprograma';
        $subprograma->maxlength = 3;
        $subprograma->formato = 'entero';

        // se agrega filtro proyecto
        $proyecto = new stdClass();
        $proyecto->id = 'proyecto';
        $proyecto->label = 'Proyecto';
        $proyecto->maxlength = 3;
        $proyecto->formato = 'entero';

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';

        $comboTipoRec = new stdClass();
        $comboTipoRec->id = 'comboTipoRec';
        $comboTipoRec->opciones = [
            'N' => 'TODOS',
            'A' => $this->noEtiq,
            'B' => 'ETIQUETADO',
        ];
        $comboTipoRec->label = 'Tipo Recurso';
        $comboTipoRec->search = true;

        $html_sema = '';
        if ($this->prm->rep_id == 112 || $this->prm->rep_id == 113) {
            $comboSemaforizacion = new stdClass();
            $comboSemaforizacion->id = 'comboSema';
            $comboSemaforizacion->label = 'Semaforizacion';
            $comboSemaforizacion->mensaje = 'SELECCIONE UNA OPCION';
            $comboSemaforizacion->opciones = ['' => $this->opcionCons, 'R' => $this->porc1, 'A' => $this->porc2, 'V' => $this->porc3];
            $comboSemaforizacion->width = '400px';
            $comboSemaforizacion->selecciona = (isset($this->prm->sema_id) && $this->prm->sema_id != '') ? $this->prm->sema_id : '';
            $comboSemaforizacion->search = true;
            $html_sema = '
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboSemaforizacion) . '
                </fieldset>
            ';
        }

        $html_cap = '';
        if ($this->prm->rep_id == 113 || $this->prm->rep_id == 104) {
            $sql = "
                SELECT '' AS cap_id, 'SELECCIONE UNA OPCION' AS cap_nombre
                    UNION ALL
                SELECT cap_id, cap_nombre FROM cat_Capitulo;";
            $this->conectar();
            $this->setSql($sql);
            $this->consultar();
            $this->desconectar();
            $capitulo = [];
            while ($row = $this->extraer()) {
                $capitulo[$row['cap_id']] = $row['cap_nombre'];
            }
            $comboCapitulo = new stdClass();
            $comboCapitulo->id = 'comboCap';
            $comboCapitulo->opciones = $capitulo;
            $comboCapitulo->label = 'Capitulo Gasto';
            $comboCapitulo->width = '400px';
            $comboCapitulo->selecciona = (isset($this->prm->cap_id) && $this->prm->cap_id != '') ? $this->prm->cap_id : '';
            $comboCapitulo->search = true;
            $html_cap = '
                <fieldset class="col-md-2 col-xs-2 me-1">
                    ' . $this->ponCombo($comboCapitulo) . '
                </fieldset>
            ';
        }
        $html_rep113 = '';
        if ($this->prm->rep_id == 113) {
            $html_rep113 .= $html_cap;
            $html_cap = '';
        }

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscarS1';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->clase;
        $btnBuscar->icon = $this->iconS;

        $exportar = new stdClass();
        $exportar->id = 'exportarS1';
        $exportar->value = ' Exportar ';
        $exportar->type = 'submit';
        $exportar->icon = $this->iconD;
        $exportar->title = $this->tituloE;
        $exportar->class = $this->claseIn;

        $imprimir = new stdClass();
        $imprimir->id = 'imprimirS1';
        $imprimir->value = ' PDF ';
        $imprimir->type = 'submit';
        $imprimir->icon = $this->iconoPdf;
        $imprimir->title = $this->tituloPdf;
        $imprimir->class = $this->claseSec;

        $html_ter = '';
        if ($this->prm->rep_id != 104) {
            $html_ter = '
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboTipoRec) . '
                </fieldset>
            ';
        }

        $html = '
            <div class="d-flex justify-content-around mb-4">
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboMes) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($ur) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($claveFinanciamiento) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($ue) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($programa) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($subprograma) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($proyecto) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($actividad) . '
                </fieldset>
                ' . $html_ter . '
                ' . $html_cap . '
                ' . $html_sema . '
            </div>
            <div class="d-flex justify-content-start">
                ' . $html_rep113 . '
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
            </div>
            <div class="d-flex justify-content-end">
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($imprimir) . '
                </fieldset>
            </div>
        ';
        return $html;
    } //fin pantallaGeneralM

    private function verPantallaSegM2() //UR.-PROG.-SPROG.-PY.-OBRA O ACTIVIDAD
    {
        $html = $this->pantallaGeneralM();

        $tabla = new stdClass();
        $tabla->titulo = 'Lista de Obras/Actividades por mes';
        $tabla->id = 'tbl-ProgramaSubprogramaActividad';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;

        $tabla->comunica->mes = '{$("#comboMes").val()}';
        $tabla->comunica->cfi_clave = '{$("#cfi_clave").val().toUpperCase()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->ue_id = '{$("#ue").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';
        $tabla->comunica->oa_id = '{$("#actividad").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE';
        $th->width = '5px';
        array_push($tr, $th);

        //se agrega campo para nombre ur
        $th = new stdClass();
        $th->texto = 'UNIDAD RESPONSABLE';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->width = '50px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'APROBADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'MODIFICADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'COMPROMETIDO';
        array_push($tr, $th);

        // add campo semaforización comprometido
        $th = new stdClass();
        $th->texto = 'SEMAFORIZACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DEVENGADO';
        array_push($tr, $th);

        // add campo semaforización devengado
        $th = new stdClass();
        $th->texto = 'SEMAFORIZACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EJERCIDO';
        array_push($tr, $th);

        // add campo semaforización ejercido
        $th = new stdClass();
        $th->texto = 'SEMAFORIZACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'POR COMPROMETER';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'POR DEVENGAR';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'POR EJERCER';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'SEMAFORIZACIÓN';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'desEntidad';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'desObra';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'origina';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'vigente';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'comprom';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        // add semaforizacion comprometido
        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'color_comprom';
        $td->color = 'color_comprom';
        $td->campoTxt = 'semaf_comprom';
        $td->lbl = 'semaf_comprom';
        $td->title = 'semaf_comprom';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'devenga';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        // add semaforizacion devengado
        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'color_devenga';
        $td->color = 'color_devenga';
        $td->campoTxt = 'semaf_devenga';
        $td->lbl = 'semaf_devenga';
        $td->title = 'semaf_devenga';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ejercid';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        // add semaforizacion ejercido
        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'color_ejercid';
        $td->color = 'color_ejercid';
        $td->campoTxt = 'semaf_ejercid';
        $td->lbl = 'semaf_ejercid';
        $td->title = 'semaf_ejercid';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'por';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'por';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'por';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        //Semaforizacion
        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'color';
        $td->color = 'color';
        $td->campoTxt = 'sema';
        $td->lbl = 'sema';
        $td->title = 'sema';
        $td->align = 'center';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="max-width: 1500px!important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">
                                1. UR.-PROG.-SPROG.-PY.-OBRA O ACTIVIDAD MENSUAL <!-- modf.2019-->
                            </h4>
                            <button type="button" class="btn-close cerrar" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ' . $html . '
                            ' . $this->wrPonTabla($tabla) . '
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary cancelaPt" id="cancelaPt" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');

        $this->mixScript('
            $("#myModal").modal("show");
            $(".cancelaPt").click(function () {
                $("#myModal").remove();
            });

            $("#btnBuscarS1").click(function(){
                if($("#comboMes").val() != ""){
                    comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{
                        metodo:"llenaTablaSegM2",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        mes:$("#comboMes").val(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        oa_id:$("#actividad").val(),
                        ter_id:$("#comboTipoRec").val(),
                        sema_id:$("#comboSema").val(),
                    });
                } else {
                    notify("Seleccione un mes","error");
                }
            });

            $("#exportarS1").click(function(){
                if($("#comboMes").val() !=""){
                    comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        mes:$("#comboMes").val(),
                        py_id:$("#proyecto").val(),
                        ter_id:$("#comboTipoRec").val(),
                        sema_id:$("#comboSema").val(),
                        aplicacion:"EXCEL",
                        rep_id:112
                    });
                    $("#reportes").submit();
                } else {
                    notify("Debe seleccionar el mes","error");
                }
            });

            $("#imprimirS1").click(function(){
                if($("#comboMes").val() !=""){
                    comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        mes:$("#comboMes").val(),
                        py_id:$("#proyecto").val(),
                        ter_id:$("#comboTipoRec").val(),
                        sema_id:$("#comboSema").val(),
                        aplicacion:"PDF",
                        rep_id:112
                    });
                    $("#reportes").submit();
                } else {
                    notify("Debe seleccionar el mes","error");
                }
            });
        ');
    } // Fin verPantallaSegM2

    private function llenaTablaSegM2()
    {
        $filtro = ' WHERE  toa_id="1"';
        $filtro_sema = '';

        if ((isset($this->prm->ur_clave) && $this->prm->ur_clave != '')) {
            $filtro .= ' AND ur_clave = ' . $this->prm->ur_clave . '';
        }
        if ((isset($this->prm->ue_id) && $this->prm->ue_id != '')) {
            $filtro .= ' AND pre.ue_id = ' . $this->prm->ue_id . '';
        }
        if ((isset($this->prm->prg_id) && $this->prm->prg_id != '')) {
            $filtro .= ' AND prg_id = ' . $this->prm->prg_id . '';
        }
        if ((isset($this->prm->spr_id) && $this->prm->spr_id != '')) {
            $filtro .= ' AND spr_id = ' . $this->prm->spr_id . '';
        }
        if ((isset($this->prm->py_id) && $this->prm->py_id != '')) {
            $filtro .= ' AND py_id = ' . $this->prm->py_id . '';
        }
        if ((isset($this->prm->oa_id) && $this->prm->oa_id != '')) {
            $filtro .= ' AND oa_id = ' . $this->prm->oa_id . '';
        }
        if ((isset($this->prm->cfi_clave) && $this->prm->cfi_clave != '')) {
            $filtro .= 'AND cfi_clave = "' . $this->prm->cfi_clave . '"';
        }
        if ((isset($this->prm->ter_id) && $this->prm->ter_id != '' && $this->prm->ter_id != 'N')) {
            $filtro .= ' AND ter_id = "' . $this->prm->ter_id . '"';
        }
        if ((isset($this->prm->sema_id) && $this->prm->sema_id != '')) {
            if ($this->prm->sema_id == 'R') {
                $filtro_sema = " HAVING	color_ejercid ='FF0000' ";
            }
            if ($this->prm->sema_id == 'A') {
                $filtro_sema = " HAVING	color_ejercid ='F3C12A' ";
            }
            if ($this->prm->sema_id == 'V') {
                $filtro_sema = " HAVING	color_ejercid ='41A62A' ";
            }
        }

        $mesSeleccionado = $this->prm->mes;
        //Declaración de arreglo de meses
        $meses = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        //Validación ultimo día del mes
        //Inicialización de parámetros de consulta de acuerdo al mes seleccionado
        $presupuesto = '
            (SUM(apr_' . $meses[$mesSeleccionado] . ')) AS origina,
            (SUM(vig_' . $meses[$mesSeleccionado] . ')) AS vigente,
            (SUM(com_' . $meses[$mesSeleccionado] . ')) AS comprom,
            (SUM(dev_' . $meses[$mesSeleccionado] . ')) AS devenga,
            (SUM(eje_' . $meses[$mesSeleccionado] . ')) AS ejercid ';
        $porcentajePresupuesto = ' round(((100*(SUM(eje_' . $meses[$mesSeleccionado] . ')))/(SUM(vig_' . $meses[$mesSeleccionado] . '))),2) porcentajePresupuesto ';
        $por = '(SUM(vig_' . $meses[$mesSeleccionado] . ') - SUM(eje_' . $meses[$mesSeleccionado] . ')) as por';
        $this->conectar();

        $this->setSql('
            SELECT
                CONCAT(pre.gpo_id,pre.ur_id) AS ur,cat_UnidadResponsable.ur_nombre AS desEntidad,
                CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
                MAX(CAST(pre.prg_id AS CHAR)) AS prg_id,MAX(cat_Programa.prg_nombre) AS despgma,
                MAX(CAST(pre.spr_id AS CHAR)) AS spr_id,MAX(cat_Subprograma.spr_nombre) AS desspgma,
                CAST(pre.py_id AS CHAR) AS py_id,cat_Proyecto.py_nombre AS desproyecto,
                MAX(CAST(pre.oa_id AS CHAR)) AS obraaccion,cat_ObraActividad.oa_nombre AS desObra,
                cf.cfi_clave,MAX(cf.cfi_nombre)cfi_nombre,
                sum(pre.origina) as origina,sum(pre.vigente) as vigente,
                sum(pre.comprom) as comprom,
                CASE (sum(pre.comprom)+sum(pre.vigente)) WHEN 0 THEN 0  ELSE CAST(ROUND((sum(pre.comprom)/sum(pre.vigente))*100,2) AS CHAR)  END AS semaf_comprom,
                CASE
                    WHEN (ROUND((sum(pre.comprom)/sum(pre.vigente)*100),2) BETWEEN 0 AND 49) OR ROUND((sum(pre.comprom)/sum(pre.vigente)*100),2) IS NULL THEN "FF0000"
                    WHEN (ROUND((sum(pre.comprom)/sum(pre.vigente)*100),2) BETWEEN 50 AND 79) THEN "F3C12A"
                    WHEN (ROUND((sum(pre.comprom)/sum(pre.vigente)*100),2) BETWEEN 80 AND 100) THEN "41A62A"
                ELSE "AFAFAF" END AS color_comprom,
                sum(pre.devenga) as devenga,
                CASE (sum(pre.devenga)+sum(pre.vigente)) WHEN 0 THEN 0  ELSE CAST(ROUND((sum(pre.devenga)/sum(pre.vigente))*100,2) AS CHAR)  END AS semaf_devenga,
                CASE
                    WHEN (ROUND((sum(pre.devenga)/sum(pre.vigente)*100),2) BETWEEN 0 AND 49) OR ROUND((sum(pre.devenga)/sum(pre.vigente)*100),2) IS NULL THEN "FF0000"
                    WHEN (ROUND((sum(pre.devenga)/sum(pre.vigente)*100),2) BETWEEN 50 AND 79) THEN "F3C12A"
                    WHEN (ROUND((sum(pre.devenga)/sum(pre.vigente)*100),2) BETWEEN 80 AND 100) THEN "41A62A"
                ELSE "AFAFAF" END AS color_devenga,
                sum(pre.ejercid) as ejercid,
                CASE (sum(pre.ejercid)+sum(pre.vigente)) WHEN 0 THEN 0  ELSE CAST(ROUND((sum(pre.ejercid)/sum(pre.vigente))*100,2) AS CHAR)  END AS semaf_ejercid,
                CASE
                    WHEN (ROUND((sum(pre.ejercid)/sum(pre.vigente)*100),2) BETWEEN 0 AND 49) OR ROUND((sum(pre.ejercid)/sum(pre.vigente)*100),2) IS NULL THEN "FF0000"
                    WHEN (ROUND((sum(pre.ejercid)/sum(pre.vigente)*100),2) BETWEEN 50 AND 79) THEN "F3C12A"
                    WHEN (ROUND((sum(pre.ejercid)/sum(pre.vigente)*100),2) BETWEEN 80 AND 100) THEN "41A62A"
                ELSE "AFAFAF" END AS color_ejercid,
                CASE (pre.vigente+pre.por) WHEN 0 THEN 0  ELSE CAST(ROUND(((pre.por*100)/pre.vigente),2) AS CHAR)  END AS sema,
                CASE
                    WHEN (ROUND(((pre.por*100)/pre.vigente),2) BETWEEN 0 AND 49) OR ROUND(((pre.por*100)/pre.vigente),2) IS NULL THEN "FF0000"
                    WHEN (ROUND(((pre.por*100)/pre.vigente),2) BETWEEN 50 AND 79) THEN "F3C12A"
                    WHEN (ROUND(((pre.por*100)/pre.vigente),2) BETWEEN 80 AND 100) THEN "41A62A"
                ELSE "AFAFAF" END AS color,
                (pre.por)por
            FROM
                (   SELECT
                        gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,MAX(tcp_id)tcp_id,
                        cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,
                        ' . $presupuesto . ',' . $por . ',' . $porcentajePresupuesto . '
                    FROM presupuesto
                        JOIN presupuestoAprobado AS apr USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        JOIN presupuestoVigente AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        JOIN presupuestoComprometido AS com USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        JOIN presupuestoDevengado AS dev USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        JOIN presupuestoEjercido AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    WHERE tcp_id<>"P"
                    GROUP BY gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio
                ) AS pre
            LEFT JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            LEFT JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
            LEFT JOIN cat_Programa USING (gpo_id,ur_id,prg_id)
            LEFT JOIN cat_Subprograma USING (gpo_id,ur_id,prg_id,spr_id)
            LEFT JOIN cat_Proyecto USING (gpo_id,ur_id,prg_id,spr_id,py_id)
            LEFT JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            LEFT JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' AS cf USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            LEFT JOIN ' . BD_SINPRES . $this->tablaSuborigen . APP_EJERCICIO . ' c ' . $this->usingSR . '
            LEFT JOIN ' . BD_SINPRES . '.cat_TipoRecurso tr USING (ter_id)
            ' . $filtro . '
            GROUP BY pre.gpo_id,pre.ur_id,pre.ue_id,pre.py_id,py_nombre,desObra,cf.cfi_clave,pre.vigente,pre.ejercid,pre.por
            ' . $filtro_sema . '
            ORDER BY pre.gpo_id,pre.ur_id,cf.cfi_clave,pre.ue_id,prg_id,spr_id;
        ');
        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ProgramaSubprogramaActividad';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    } //Fin llenaTablaSegM2

    private function verPantallaSegM3() // UR.-UE- CAPÍTULO(COMPARATIVO)
    {
        $html = $this->pantallaGeneralM();

        $tabla = new stdClass();
        $tabla->titulo = 'Lista de Obras/Actividades por mes';
        $tabla->id = 'tbl-CapituloComparativo';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;

        $tabla->comunica->mes = '{$("#comboMes").val()}';
        $tabla->comunica->cfi_clave = '{$("#cfi_clave").val().toUpperCase()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->ue_id = '{$("#ue").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';
        $tabla->comunica->oa_id = '{$("#actividad").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE';
        $th->width = '5px';
        array_push($tr, $th);

        //se agrega campo para nombre ur
        $th = new stdClass();
        $th->texto = 'UNIDAD RESPONSABLE';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->width = '50px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'MODIFICADO';
        $th->width = '50px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EJERCIDO';
        $th->width = '50px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'POR EJERCER';
        $th->width = '50px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'SEMAFORIZACIÓN EJERCIDO  / MODIFICADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'SEMAFORIZACIÓN POR EJERCER/ MODIFICADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PORCENTAJE TOTAL';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'desEntidad';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'desPrograma';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'vigente';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ejercid';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'porjercer';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        // add semaforizacion
        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'color_ejer_mod';
        $td->color = 'color_ejer_mod';
        $td->campoTxt = 'cal_ejer_mod';
        $td->lbl = 'cal_ejer_mod';
        $td->title = 'cal_ejer_mod';
        $td->align = 'center';
        array_push($trbody, $td);

        // add semaforizacion
        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'color_ejercer_mod';
        $td->color = 'color_ejercer_mod';
        $td->campoTxt = 'cal_ejercer_mod';
        $td->lbl = 'cal_ejercer_mod';
        $td->title = 'cal_ejercer_mod';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'total';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="max-width: 1500px!important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">
                                2. UR.-UE- CAPÍTULO(COMPARATIVO) MENSUAL<!-- modf. 2019 -->
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ' . $html . '
                            ' . $this->wrPonTabla($tabla) . '
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary cancelaPt" id="cancelaPt" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');

        $this->mixScript('
            $("#myModal").modal("show");
            $(".cancelaPt").click(function () {
                $("#myModal").remove();
            });

            $("#btnBuscarS1").click(function() {
                if($("#comboMes").val() != "") {
                    comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{
                        metodo:"llenaTablaSegM3",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        mes:$("#comboMes").val(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        oa_id:$("#actividad").val(),
                        ter_id:$("#comboTipoRec").val(),
                        sema_id:$("#comboSema").val(),
                        cap_id:$("#comboCap").val(),
                    });
                } else {
                    notify("Seleccione un mes","error");
                }
            });

            $("#exportarS1").click(function() {
                if($("#comboMes").val() !="") {
                    comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        mes:$("#comboMes").val(),
                        py_id:$("#proyecto").val(),
                        ter_id:$("#comboTipoRec").val(),
                        sema_id:$("#comboSema").val(),
                        cap_id:$("#comboCap").val(),
                        aplicacion:"EXCEL",
                        rep_id:113
                    });
                    $("#reportes").submit();
                } else {
                    notify("Debe seleccionar el mes","error");
                }
            });

            $("#imprimirS1").click(function() {
                if($("#comboMes").val() !="") {
                    comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        mes:$("#comboMes").val(),
                        py_id:$("#proyecto").val(),
                        ter_id:$("#comboTipoRec").val(),
                        sema_id:$("#comboSema").val(),
                        cap_id:$("#comboCap").val(),
                        aplicacion:"PDF",
                        rep_id:113
                    });
                    $("#reportes").submit();
                } else {
                    notify("Debe seleccionar el mes","error");
                }
            });
        ');
    } //Fin verPantallaSegM3

    private function llenaTablaSegM3()
    {
        $filtro = '';
        $gpo_id = '';
        $ur_id = '';
        $filtro_sema = '';

        if ((isset($this->prm->ur_clave) && $this->prm->ur_clave != '')) {
            $gpo_id = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '' && strlen($this->prm->ur_clave) == 3) ? ' AND gpo_id=' . substr($this->prm->ur_clave, 0, 1) . ' ' : '';
            $ur_id = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '' && strlen($this->prm->ur_clave) == 3) ? ' AND ur_id=' . substr($this->prm->ur_clave, 1, 2) . ' ' : '';
        }

        if ((isset($this->prm->ue_id) && $this->prm->ue_id != '')) {
            $filtro .= '  AND pre.ue_id = ' . $this->prm->ue_id . '';
        }
        if ((isset($this->prm->prg_id) && $this->prm->prg_id != '')) {
            $filtro .= '  AND pre.prg_id = ' . $this->prm->prg_id . '';
        }
        if ((isset($this->prm->spr_id) && $this->prm->spr_id != '')) {
            $filtro .= '  AND pre.spr_id = ' . $this->prm->spr_id . '';
        }
        if ((isset($this->prm->py_id) && $this->prm->py_id != '')) {
            $filtro .= '  AND pre.py_id = ' . $this->prm->py_id . '';
        }
        if ((isset($this->prm->oa_id) && $this->prm->oa_id != '')) {
            $filtro .= '  AND pre.oa_id = ' . $this->prm->oa_id . '';
        }
        if ((isset($this->prm->cfi_clave) && $this->prm->cfi_clave != '')) {
            $filtro .= ' AND cf.cfi_clave =  "' . $this->prm->cfi_clave . '" ';
        }
        if ((isset($this->prm->ter_id) && $this->prm->ter_id != '' && $this->prm->ter_id != 'N')) {
            $filtro .= ' AND ter_id = "' . $this->prm->ter_id . '"';
        }
        if ((isset($this->prm->sema_id) && $this->prm->sema_id != '')) {
            if ($this->prm->sema_id == 'R') {
                $filtro_sema = " HAVING	color_ejer_mod ='FF0000' ";
            }
            if ($this->prm->sema_id == 'A') {
                $filtro_sema = " HAVING	color_ejer_mod ='F3C12A' ";
            }
            if ($this->prm->sema_id == 'V') {
                $filtro_sema = " HAVING	color_ejer_mod ='41A62A' ";
            }
        }
        if ((isset($this->prm->cap_id) && $this->prm->cap_id != '')) {
            $filtro .= ' AND cap.cap_id = "' . $this->prm->cap_id . '"';
        }

        $meses = '';
        switch ($this->prm->mes) {
            case 1:
                $meses = ' SUM(vig_ene) AS vigente,SUM(eje_ene) AS ejercid  ';
                break;
            case 2:
                $meses = ' SUM(vig_feb) AS vigente,SUM(eje_feb) AS ejercid  ';
                break;
            case 3:
                $meses = ' SUM(vig_mar) AS vigente,SUM(eje_mar) AS ejercid  ';
                break;
            case 4:
                $meses = ' SUM(vig_abr) AS vigente,SUM(eje_abr) AS ejercid  ';
                break;
            case 5:
                $meses = ' SUM(vig_may) AS vigente,SUM(eje_may) AS ejercid  ';
                break;
            case 6:
                $meses = ' SUM(vig_jun) AS vigente,SUM(eje_jun) AS ejercid  ';
                break;
            case 7:
                $meses = ' SUM(vig_jul) AS vigente,SUM(eje_jul) AS ejercid  ';
                break;
            case 8:
                $meses = ' SUM(vig_ago) AS vigente,SUM(eje_ago) AS ejercid  ';
                break;
            case 9:
                $meses = ' SUM(vig_sep) AS vigente,SUM(eje_sep) AS ejercid  ';
                break;
            case 10:
                $meses = ' SUM(vig_oct) AS vigente,SUM(eje_oct) AS ejercid  ';
                break;
            case 11:
                $meses = ' SUM(vig_nov) AS vigente,SUM(eje_nov) AS ejercid  ';
                break;
            case 12:
                $meses = ' SUM(vig_dic) AS vigente,SUM(eje_dic) AS ejercid  ';
                break;
        }

        $this->conectar();
        $this->setSql(
            "
            SELECT
                CONCAT(CAST(pre.gpo_id AS CHAR),CAST(pre.ur_id AS CHAR)) AS ur,cat_UnidadResponsable.ur_nombre AS desEntidad,
                CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
                CAST(pre.prg_id AS CHAR) AS Programa, cat_Programa.prg_nombre AS desPrograma,
                CAST(pre.spr_id AS CHAR) AS Subprograma,cat_Subprograma.spr_nombre AS desSubprograma,
                CAST(pre.py_id AS CHAR) AS proyecto,cat_Proyecto.py_nombre AS desProyecto,
                CAST(pre.oa_id AS CHAR) AS obraaccion,cat_ObraActividad.oa_nombre AS desObra,
                cf.cfi_clave,cf.cfi_nombre,
                pre.capitulo,cap.cap_nombre,vigente,ejercid,(vigente - ejercid) porjercer,
                CASE
                    WHEN (ROUND((ejercid/(vigente)*100),2) BETWEEN 0 AND 49.99) OR ROUND(((ejercid)/(vigente)*100),2) IS NULL THEN 'FF0000'
                    WHEN (ROUND((ejercid/(vigente)*100),2) BETWEEN 50 AND 79.99) THEN 'F3C12A'
                    WHEN (ROUND((ejercid/(vigente)*100),2) BETWEEN 80 AND 100) THEN '41A62A'
                    ELSE 'afafaf' END AS color_ejer_mod,
                CASE WHEN ROUND((ejercid/(vigente)*100),2) = 0  OR ROUND((ejercid/(vigente)*100),2)  IS NULL THEN 0
                    else ROUND((ejercid/(vigente)*100),2) end as cal_ejer_mod,
                CASE
                        WHEN (ROUND(((vigente-ejercid)/(vigente)*100),2) BETWEEN 0 AND 49.99) OR ROUND(((vigente-ejercid)/(vigente)*100),2) IS NULL THEN 'FF0000'
                        WHEN (ROUND(((vigente-ejercid)/(vigente)*100),2) BETWEEN 50 AND 79.99) THEN 'F3C12A'
                        WHEN (ROUND(((vigente-ejercid)/(vigente)*100),2) BETWEEN 80 AND 100) THEN '41A62A'
                        ELSE 'afafaf' END AS color_ejercer_mod,
                CASE WHEN ROUND(((vigente-ejercid)/(vigente)*100),2) = 0  OR ROUND(((vigente-ejercid)/(vigente)*100),2)  IS NULL THEN 0
                    else ROUND(((vigente-ejercid)/(vigente)*100),2) end as cal_ejercer_mod,
                CASE WHEN (ROUND((ejercid/(vigente)*100),2) +ROUND(((vigente-ejercid)/(vigente)*100),2) ) = 0
                    OR (ROUND((ejercid/(vigente)*100),2) +ROUND(((vigente-ejercid)/(vigente)*100),2) )  IS NULL THEN 0
                    else (ROUND((ejercid/(vigente)*100),2) +ROUND(((vigente-ejercid)/(vigente)*100),2)  )end
                    as total
            FROM (
            SELECT
                gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,SUBSTRING(pg_id,1,1) AS capitulo,
                cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,
                $meses -- cambian segun el trimrestre seleccionado en el filtro de la pantalla
            FROM presupuesto
                JOIN presupuestoVigente AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoEjercido AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            WHERE tcp_id<>'P'
                $gpo_id $ur_id -- el grupo y la ur son seleccionables
            GROUP BY gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,
                SUBSTRING(pg_id,1,1),
                cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio
            ) AS pre
            LEFT JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            LEFT JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
            LEFT JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
            LEFT JOIN cat_Subprograma USING(gpo_id,ur_id,prg_id,spr_id)
            LEFT JOIN cat_Proyecto USING(gpo_id,ur_id,prg_id,spr_id,py_id)
            LEFT JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            LEFT JOIN
            (
                SELECT clave.*,ter_id FROM " . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' clave
                LEFT JOIN ' . BD_SINPRES . $this->tablaSuborigen . APP_EJERCICIO . ' c ' . $this->usingSR . '
                LEFT JOIN ' . BD_SINPRES . '.cat_TipoRecurso tr USING (ter_id)
                WHERE cfi_ejercicio2=' . APP_EJERCICIO . "
            )
            AS cf USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            LEFT JOIN cat_Capitulo AS cap ON pre.capitulo=cap.cap_id
            WHERE  toa_id='1' -- validacion fija en el reporte
            $filtro
            GROUP BY pre.gpo_id,ur_id,cf.cfi_clave,pre.ue_id,pre.prg_id,pre.spr_id,pre.py_id,pre.oa_id,pre.capitulo,cf.cfi_nombre,pre.vigente,pre.ejercid
            $filtro_sema
            ORDER BY pre.gpo_id,ur_id,cf.cfi_clave,pre.ue_id,pre.prg_id,pre.spr_id,pre.py_id,pre.oa_id,pre.capitulo;"
        );
        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-CapituloComparativo';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    } //Fin llenaTablaSegM3

    private function verPantallaSegM4() // MODIFICACIONES PRESUPUESTALES
    {
        $html = $this->pantallaGeneralM();

        $tabla = new stdClass();
        $tabla->titulo = 'REPORTE DE MODIFICACIONES';
        $tabla->id = 'tbl-ResumenSeg';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;

        $tabla->comunica->mes = '{$("#comboMes").val()}';
        $tabla->comunica->cfi_clave = '{$("#cfi_clave").val().toUpperCase()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->ue_id = '{$("#ue").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';
        $tabla->comunica->oa_id = '{$("#actividad").val()}';
        $tabla->comunica->py_id = '{$("#programa").val()}';
        $tabla->comunica->cap_id = '{$("#comboCap").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE';
        $th->width = '5px';
        array_push($tr, $th);

        //se agrega campo para nombre ur
        $th = new stdClass();
        $th->texto = 'UNIDAD RESPONSABLE';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UNIDAD EJECUTORA';
        $th->width = '50px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PROGRAMA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'SUB-PROGRAMA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PROYECTO';
        array_push($tr, $th);

        // add campo semaforización comprometido
        $th = new stdClass();
        $th->texto = 'OBRA ACTIVIDAD';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DEFINICION';
        array_push($tr, $th);

        // add campo semaforización devengado
        $th = new stdClass();
        $th->texto = 'TIPO ACTIVIDAD';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'MODFICICADO';
        array_push($tr, $th);

        // add campo semaforización ejercido
        $th = new stdClass();
        $th->texto = 'EJERCIDO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'POR EJERCER';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = '% ALCANZADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'OBSERVACION REALIZADA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO OBSERVACION';
        array_push($tr, $th);

        $trbody = [];

        // se muestra nombre ur
        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'desEntidad';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ue_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'Programa';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'Subprograma';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'proyecto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'obraaccion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'desObra';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'toa_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'vigente';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ejercid';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'porejercer';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'porcentaje';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'hms_mensaje';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'estado_mensaje';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="max-width: 1500px!important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">
                                3.-ADECUACIONES PRESUPUESTALES <!-- modf. 2020-->
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ' . $html . '
                            ' . $this->wrPonTabla($tabla) . '
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary cancelaPt" id="cancelaPt" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');

        $this->mixScript('
            $("#myModal").modal("show");
            $(".cancelaPt").click(function () {
                $("#myModal").remove();
            });

            $("#btnBuscarS1").click(function() {
                if($("#comboMes").val() != "") {
                    comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{
                        metodo:"llenaTablaSegM4",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        mes:$("#comboMes").val(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        oa_id:$("#actividad").val(),
                        ter_id:$("#comboTipoRec").val(),
                        cap_id:$("#comboCap").val(),
                    });
                } else {
                    notify("Seleccione un mes","error");
                }
            });

            $("#exportarS1").click(function(){
                if($("#comboMes").val() !=""){
                    comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        mes:$("#comboMes").val(),
                        py_id:$("#proyecto").val(),
                        cap_id:$("#comboCap").val(),
                        aplicacion:"EXCEL",
                        rep_id:104
                    });
                    $("#reportes").submit();
                } else {
                    notify("Debe seleccionar el mes","error");
                }
            });

            $("#imprimirS1").click(function(){
                if($("#comboMes").val() !=""){
                    comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        mes:$("#comboMes").val(),
                        py_id:$("#proyecto").val(),
                        cap_id:$("#comboCap").val(),
                        aplicacion:"PDF",
                        rep_id:104
                    });
                    $("#reportes").submit();
                } else {
                    notify("Debe seleccionar el mes","error");
                }
            });
        ');
    } //Fin verPantallaSegM3

    private function llenaTablaSegM4()
    {
        $gpo_id = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '' && strlen($this->prm->ur_clave) == 3) ? ' AND gpo_id=' . substr($this->prm->ur_clave, 0, 1) . ' ' : '';
        $ur_id = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '' && strlen($this->prm->ur_clave) == 3) ? ' AND ur_id=' . substr($this->prm->ur_clave, 1, 2) . ' ' : '';
        $ue = (isset($this->prm->ue_id) && $this->prm->ue_id != '') ? ' AND p.ue_id=' . $this->prm->ue_id : '';
        $prg = (isset($this->prm->prg_id) && $this->prm->prg_id != '') ? ' AND prg_id=' . $this->prm->prg_id : '';
        $spr = (isset($this->prm->spr_id) && $this->prm->spr_id != '') ? ' AND spr_id=' . $this->prm->spr_id : '';
        $oa = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? ' AND oa_id=' . $this->prm->oa_id : '';
        $cvefin = (isset($this->prm->cfi_clave) && $this->prm->cfi_clave != '') ? ' AND cfi_clave="' . $this->prm->cfi_clave . '" ' : '';
        $py_id = (isset($this->prm->py_id) && $this->prm->py_id != '') ? ' AND py_id=' . $this->prm->oa_id : '';
        $ter_id = (isset($this->prm->ter_id) && $this->prm->ter_id != '' && $this->prm->ter_id != 'N') ? ' AND ter_id="' . $this->prm->ter_id . '"' : '';
        $py_id = (isset($this->prm->py_id) && $this->prm->py_id != '') ? ' AND py_id=' . $this->prm->py_id : '';
        $cap_id = (isset($this->prm->cap_id) && $this->prm->cap_id != '') ? ' AND SUBSTRING(pg_id,1,1) =' . $this->prm->cap_id : '';
        $meses = $wheremes = $nummes= '';

        switch ($this->prm->mes) {
            case 1:
                $meses = ' SUM(vig_ene) AS vigente,SUM(eje_ene) AS ejercid  ';
                $nummes = '1';
                $wheremes = ' AND (vig_ene+eje_ene)<>0  ';
                break;
            case 2:
                $meses = ' SUM(vig_feb) AS vigente,SUM(eje_feb) AS ejercid  ';
                $nummes = '2';
                $wheremes = ' AND (vig_feb+eje_feb)<>0  ';
                break;
            case 3:
                $meses = ' SUM(vig_mar) AS vigente,SUM(eje_mar) AS ejercid  ';
                $nummes = '3';
                $wheremes = ' AND (vig_mar+eje_mar)<>0  ';
                break;
            case 4:
                $meses = ' SUM(vig_abr) AS vigente,SUM(eje_abr) AS ejercid  ';
                $nummes = '4';
                $wheremes = ' AND (vig_abr+eje_abr)<>0  ';
                break;
            case 5:
                $meses = ' SUM(vig_may) AS vigente,SUM(eje_may) AS ejercid  ';
                $nummes = '5';
                $wheremes = ' AND (vig_may+eje_may)<>0  ';
                break;
            case 6:
                $meses = ' SUM(vig_jun) AS vigente,SUM(eje_jun) AS ejercid  ';
                $nummes = '6';
                $wheremes = ' AND (vig_jun+eje_jun)<>0  ';
                break;
            case 7:
                $meses = ' SUM(vig_jul) AS vigente,SUM(eje_jul) AS ejercid  ';
                $nummes = '7';
                $wheremes = ' AND (vig_jul+eje_jul)<>0  ';
                break;
            case 8:
                $meses = ' SUM(vig_ago) AS vigente,SUM(eje_ago) AS ejercid  ';
                $nummes = '8';
                $wheremes = ' AND (vig_ago+eje_ago)<>0  ';
                break;
            case 9:
                $meses = ' SUM(vig_sep) AS vigente,SUM(eje_sep) AS ejercid  ';
                $nummes = '9';
                $wheremes = ' AND (vig_sep+eje_sep)<>0  ';
                break;
            case 10:
                $meses = ' SUM(vig_oct) AS vigente,SUM(eje_oct) AS ejercid  ';
                $nummes = '10';
                $wheremes = ' AND (vig_oct+eje_oct)<>0  ';
                break;
            case 11:
                $meses = ' SUM(vig_nov) AS vigente,SUM(eje_nov) AS ejercid  ';
                $nummes = '11';
                $wheremes = ' AND (vig_nov+eje_nov)<>	0  ';
                break;
            case 12:
                $meses = ' SUM(vig_dic) AS vigente,SUM(eje_dic) AS ejercid  ';
                $nummes = '12';
                $wheremes = ' AND (vig_dic+eje_dic)<>0  ';
                break;
        }

        $this->conectar();
        $this->setSql(
            "
            SELECT
                CONCAT(pre.gpo_id,pre.ur_id,pre.ue_id,pre.prg_id,pre.spr_id,pre.py_id,pre.oa_id) AS epg,
                CONCAT(pre.gpo_id,pre.ur_id) AS ur,ur_nombre AS desEntidad,
                CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
                CAST(pre.prg_id AS CHAR) AS Programa, prg_nombre AS desPrograma,
                CAST(pre.spr_id AS CHAR) AS Subprograma,spr_nombre AS desSubprograma,
                CAST(pre.py_id AS CHAR) AS proyecto,py_nombre AS desProyecto,
                CAST(pre.oa_id AS CHAR) AS obraaccion,oa_nombre AS desObra,toa_nombre,
                pre.vigente,pre.ejercid,
                (pre.vigente - pre.ejercid) porejercer,
                IF(pre.vigente=0,0,(pre.ejercid*100/pre.vigente)) AS porcentaje,
                IF(msj_id IS NULL,0,msj_id) AS msj_id,
                IF(hms_mensaje IS NULL,'',hms_mensaje) AS hms_mensaje,eme_id,
                CASE WHEN eme_id=1 THEN
                    'MENSAJE GENERADO'
                WHEN eme_id=2 THEN
                    'MENSAJE ENVIADO'
                WHEN eme_id=3 THEN
                    'MENSAJE ELIMINADO'
                WHEN eme_id=4 THEN
                    'MENSAJE SOLVENTADO'
                WHEN eme_id=5 THEN
                    'LEIDO POR LA UR'
                WHEN eme_id=6 THEN
                    'LEIDO POR LA SEFIN'
                ELSE '' END AS estado_mensaje
            FROM(
                SELECT gpo_id,ur_id,p.ue_id,prg_id,spr_id,py_id,oa_id,MAX(epg_id) as epg_id,toa_id,oa_nombre,
                    $meses  -- cambian segun el trimestre seleccionado en el filtro de la pantalla
                FROM presupuesto p
                JOIN presupuestoVigente  AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoEjercido AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                LEFT JOIN " . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' AS cf USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                LEFT JOIN ' . BD_SINPRES . $this->tablaSuborigen . APP_EJERCICIO . ' c ' . $this->usingSR . '
                LEFT JOIN ' . BD_SINPRES . ".cat_TipoRecurso tr USING (ter_id)
                WHERE tcp_id<>'P' AND toa_id=1 -- condiciones fijas
                    $wheremes  -- valida que tenga presupuesto en el trimestre
                    $gpo_id $ur_id-- el grupo y la ur son seleccionables
                    $ue $prg $spr $py_id  $oa
                    $cvefin $ter_id-- CALVE DE FINANCIAMIENTO
                    $cap_id -- capitulo de gasto
                GROUP BY gpo_id,ur_id,p.ue_id,prg_id,spr_id,py_id,oa_id --  POSIBLEMENTE NECESITE cfi_clave
            ) AS pre
            JOIN cat_UnidadResponsable  USING (gpo_id,ur_id)
            JOIN cat_UnidadEjecutora   USING (gpo_id,ur_id,ue_id)
            JOIN cat_Programa          USING (gpo_id,ur_id,prg_id)
            JOIN cat_Subprograma       USING (gpo_id,ur_id,prg_id,spr_id)
            JOIN cat_Proyecto          USING (gpo_id,ur_id,prg_id,spr_id,py_id)
            JOIN cat_TipoObraActividad  AS toa USING (toa_id)
            JOIN enc_Programatico USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            LEFT JOIN (
            SELECT epg_id,msj_id,hms_id,hms_mensaje,eme_id,hms_trimestre FROM cat_MensajeSeguimiento
            JOIN cat_HistorialMensajeSeguimiento USING (msj_id)
            WHERE tim_id=2 AND eme_id IN (2,4,5,6)  AND hms_mensual=$nummes
            ) AS msj ON pre.epg_id=msj.epg_id -- solo cambia el HMS_TRIMESTRE depende del trimestre
            ORDER BY pre.gpo_id,pre.ur_id,pre.ue_id,pre.prg_id,pre.spr_id,pre.py_id,pre.oa_id;"
        );
        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ResumenSeg';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    } //Fin llenaTablaSegM4

    private function verPantallaSeg2() // UR.-PROG.-SPROG.-PY.-OBRA O ACTIVIDAD
    {
        $this->setContenedor('divContenedorGeneralModal');
        //filtro por trimestre
        $comboTrimestre = new stdClass();
        $comboTrimestre->id = 'comboTrimestre';
        $comboTrimestre->label = 'Trimestre';
        $comboTrimestre->sql = '
            SELECT 1 AS comboTrimestre,"Trimestre" as trim_nombre
                union all
            SELECT 2 AS comboTrimestre,"Trimestre" as trim_nombre
                union all
            SELECT 3 AS comboTrimestre,"Trimestre" as trim_nombre
                union all
            SELECT 4 AS comboTrimestre,"Trimestre" as trim_nombre';
        $comboTrimestre->width = '800px';
        $comboTrimestre->selecciona = (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? $this->prm->trimestre : 1;
        $comboTrimestre->search = true;

        $ur = new stdClass();
        $ur->id = 'ur';
        $ur->label = 'UR';
        $ur->maxlength = 3;
        $ur->formato = 'entero';
        $ur->value = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ur_clave : '';

        $claveFinanciamiento = new stdClass();
        $claveFinanciamiento->id = 'cfi_clave';
        $claveFinanciamiento->label = 'CVEFIN';
        $claveFinanciamiento->maxlength = 10;

        $ue = new stdClass();
        $ue->id = 'ue';
        $ue->label = 'UE';
        $ue->maxlength = 3;
        $ue->formato = 'entero';

        $programa = new stdClass();
        $programa->id = 'programa';
        $programa->label = 'Programa';
        $programa->maxlength = 3;
        $programa->formato = 'entero';
        $programa->value = (isset($this->prm->prg_id) && $this->prm->prg_id != '') ? $this->prm->prg_id : '';

        $subprograma = new stdClass();
        $subprograma->id = 'subprograma';
        $subprograma->label = 'Subprograma';
        $subprograma->maxlength = 2;
        $subprograma->formato = 'entero';
        $subprograma->value = (isset($this->prm->spr_id) && $this->prm->spr_id != '') ? $this->prm->spr_id : '';

        // se agrega filtro proyecto
        $proyecto = new stdClass();
        $proyecto->id = 'proyecto';
        $proyecto->label = 'Proyecto';
        $proyecto->maxlength = 3;
        $proyecto->formato = 'entero';

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';
        $actividad->value = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? $this->prm->oa_id : '';

        $comboTipoRec = new stdClass();
        $comboTipoRec->id = 'comboTipoRec';
        $comboTipoRec->opciones = [
            'N' => 'TODOS',
            'A' => $this->noEtiq,
            'B' => 'ETIQUETADO',
        ];
        $comboTipoRec->label = 'Tipo Recurso';
        $comboTipoRec->search = true;
        $comboTipoRec->style = 'display:inline !important';

        $comboSemaforizacion = new stdClass();
        $comboSemaforizacion->id = 'comboSema';
        $comboSemaforizacion->label = 'Semaforizacion';
        $comboSemaforizacion->mensaje = 'SELECCIONE UNA OPCION';
        $comboSemaforizacion->opciones = ['' => $this->opcionCons, 'R' => $this->porc1, 'A' => $this->porc2, 'V' => $this->porc3];
        $comboSemaforizacion->width = '400px';
        $comboSemaforizacion->search = true;
        $comboSemaforizacion->style = 'display:inline !important';
        $comboSemaforizacion->selecciona = (isset($this->prm->sema_id) && $this->prm->sema_id != '') ? $this->prm->sema_id : '';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscarS2';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->clase;
        $btnBuscar->icon = $this->iconS;

        $exportar2 = new stdClass();
        $exportar2->id = 'exportarS2';
        $exportar2->type = 'submit';
        $exportar2->icon = $this->iconD;
        $exportar2->title = $this->tituloE;
        $exportar2->value = 'Exportar';
        $exportar2->class = $this->claseIn;

        $imprimir2 = new stdClass();
        $imprimir2->id = 'imprimirS2';
        $imprimir2->type = 'submit';
        $imprimir2->icon = $this->iconoPdf;
        $imprimir2->title = $this->tituloPdf;
        $imprimir2->value = 'PDF';
        $imprimir2->class = $this->claseSec;

        $div = '
            <div class="d-flex justify-content-around mb-4">
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->templateCombo($comboTrimestre) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($ur) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($claveFinanciamiento) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($ue) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($programa) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($subprograma) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($proyecto) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($actividad) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboTipoRec) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboSemaforizacion) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
            </div>
        ';
        $div2 = '
            <div class="d-flex justify-content-end">
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar2) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($imprimir2) . '
                </fieldset><br>
            </div><br>
        ';

        $tabla = new stdClass();
        $tabla->titulo = ' UR.- CVEFIN - UE -PROG.-SPROG.-PY.-OBRA O ACTIVIDAD';
        $tabla->id = 'tbl-ProgramaSubprogramaActividad';
        $tabla->tools = $div . $div2;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->trimestre = '{$("#comboTrimestre").val()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';
        $tabla->comunica->oa_id = '{$("#actividad").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE';
        $th->width = '5px';
        array_push($tr, $th);

        //se agrega campo para nombre ur
        $th = new stdClass();
        $th->texto = 'UNIDAD RESPONSABLE';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->width = '50px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'APROBADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'MODIFICADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'COMPROMETIDO';
        array_push($tr, $th);

        // add campo semaforización comprometido
        $th = new stdClass();
        $th->texto = 'SEMAFORIZACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DEVENGADO';
        array_push($tr, $th);

        // add campo semaforización devengado
        $th = new stdClass();
        $th->texto = 'SEMAFORIZACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EJERCIDO';
        array_push($tr, $th);

        // add campo semaforización ejercido
        $th = new stdClass();
        $th->texto = 'SEMAFORIZACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'POR COMPROMETER';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'POR DEVENGAR';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'POR EJERCER';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'SEMAFORIZACIÓN';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);

        // se muestra nombre ur
        $td = new stdClass();
        $td->campoTxt = 'desEntidad';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'desObra';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'origina';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'vigente';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'comprom';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        // add semaforizacion comprometido
        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'color_comprom';
        $td->color = 'color_comprom';
        $td->campoTxt = 'semaf_comprom';
        $td->lbl = 'semaf_comprom';
        $td->title = 'semaf_comprom';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'devenga';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        // add semaforizacion devengado
        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'color_devenga';
        $td->color = 'color_devenga';
        $td->campoTxt = 'semaf_devenga';
        $td->lbl = 'semaf_devenga';
        $td->title = 'semaf_devenga';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ejercid';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        // add semaforizacion ejercido
        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'color_ejercid';
        $td->color = 'color_ejercid';
        $td->campoTxt = 'semaf_ejercid';
        $td->lbl = 'semaf_ejercid';
        $td->title = 'semaf_ejercid';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'por';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'por';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'por';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        //Semaforizacion
        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'color';
        $td->color = 'color';
        $td->campoTxt = 'sema';
        $td->lbl = 'sema';
        $td->title = 'sema';
        $td->align = 'center';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="max-width: 1500px!important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">
                                1.  UR.-PROG.-SPROG.-PY.-OBRA O ACTIVIDAD <!-- modf.2019-->
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ' . $this->wrPonTabla($tabla) . '
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary cancelaPt" id="cancelaPt" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');
        $this->recargaCombo($comboTrimestre);
        $this->mixScript('
            $("#myModal").modal("show");
            $(".cancelaPt").click(function () {
                $("#myModal").remove();
            });

            $("#btnBuscarS2").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{
                        metodo:"llenaTabla2",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        trimestre:$("#comboTrimestre").val(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        fecha:$("#fecha").val(),
                        fecha2:$("#fecha2").val(),
                        py_id:$("#proyecto").val(),
                        ter_id:$("#comboTipoRec").val(),
                        sema_id:$("#comboSema").val(),
                    });
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#imprimirS2").click(function() {
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        py_id:$("#proyecto").val(),
                        ter_id:$("#comboTipoRec").val(),
                        sema_id:$("#comboSema").val(),
                        aplicacion:"PDF",
                        rep_id:105
                    });
                    $("#reportes").submit();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#exportarS2").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        py_id:$("#proyecto").val(),
                        ter_id:$("#comboTipoRec").val(),
                        sema_id:$("#comboSema").val(),
                        aplicacion:"EXCEL",
                        rep_id:105
                    });
                    $("#reportes").submit();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });
        ');
    } //Fin verPantallaSeg2

    public function llenaTabla2()
    {
        $filtro = $this->armaFiltroTabla2();
        $filtro_sema = $this->armaFiltroSemaforo();
        $datosTrimestre = $this->armaConsultaTrimestre();
    
        $this->conectar();
        $this->setSql($this->armaSqlTabla2($datosTrimestre['consultatri'], $datosTrimestre['por'], $filtro, $filtro_sema));
        $this->consultar();
        $this->desconectar();
    
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ProgramaSubprogramaActividad';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }
    
    private function armaFiltroTabla2()
    {
        $filtro = 'WHERE  toa_id="1" ';
    
        $condicionesSimples = [
            'ur_clave' => 'ur_clave',
            'ue_id'    => 'pre.ue_id',
            'prg_id'   => 'prg_id',
            'spr_id'   => 'spr_id',
            'py_id'    => 'py_id',
            'oa_id'    => 'oa_id',
        ];
    
        foreach ($condicionesSimples as $propiedad => $columna) {
            if (isset($this->prm->$propiedad) && $this->prm->$propiedad != '') {
                $filtro .= ' AND ' . $columna . ' = ' . $this->prm->$propiedad;
            }
        }
    
        if (isset($this->prm->cfi_clave) && $this->prm->cfi_clave != '') {
            $filtro .= ' AND cfi_clave = "' . $this->prm->cfi_clave . '"';
        }
    
        if (isset($this->prm->ter_id) && $this->prm->ter_id != '' && $this->prm->ter_id != 'N') {
            $filtro .= ' AND ter_id = "' . $this->prm->ter_id . '"';
        }
    
        return $filtro;
    }
    
    private function armaFiltroSemaforo()
    {
        $colores = [
            'R' => 'FF0000',
            'A' => 'F3C12A',
            'V' => '41A62A',
        ];
    
        if (isset($this->prm->sema_id) && isset($colores[$this->prm->sema_id])) {
            return ' HAVING color_ejercid ="' . $colores[$this->prm->sema_id] . '" ';
        }
    
        return '';
    }
    
    private function armaConsultaTrimestre()
    {
        $meses = [
            1 => ['ene', 'feb', 'mar'],
            2 => ['abr', 'may', 'jun'],
            3 => ['jul', 'ago', 'sep'],
            4 => ['oct', 'nov', 'dic'],
        ];
    
        $trimestre = (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? $this->prm->trimestre : 1;
        $mesesTrimestre = $meses[$trimestre] ?? $meses[1];
    
        [$m1, $m2, $m3] = $mesesTrimestre;
    
        $consultatri = '
            SUM(apr_' . $m1 . '+apr_' . $m2 . '+apr_' . $m3 . ') AS origina,
            SUM(vig_' . $m1 . $this->vigente . $m2 . $this->vigente . $m3 . ') AS vigente,
            SUM(com_' . $m1 . '+com_' . $m2 . '+com_' . $m3 . ') AS comprom,
            SUM(dev_' . $m1 . '+dev_' . $m2 . '+dev_' . $m3 . ') AS devenga,
            SUM(eje_' . $m1 . $this->eje . $m2 . $this->eje . $m3 . ') AS ejercid';
    
        $por = '(SUM(vig_' . $m1 . $this->vigente . $m2 . $this->vigente . $m3 . ') - SUM(eje_' . $m1 . $this->eje . $m2 . $this->eje . $m3 . ')) as por';
    
        return ['consultatri' => $consultatri, 'por' => $por];
    }
    
    private function armaSqlTabla2($consultatri, $por, $filtro, $filtro_sema)
    {
        return '
            SELECT
                CONCAT(pre.gpo_id,pre.ur_id) AS ur,cat_UnidadResponsable.ur_nombre AS desEntidad,
                CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
                MAX(CAST(pre.prg_id AS CHAR)) AS prg_id,MAX(cat_Programa.prg_nombre) AS despgma,
                MAX(CAST(pre.spr_id AS CHAR)) AS spr_id,MAX(cat_Subprograma.spr_nombre) AS desspgma,
                CAST(pre.py_id AS CHAR) AS py_id,cat_Proyecto.py_nombre AS desproyecto,
                MAX(CAST(pre.oa_id AS CHAR)) AS obraaccion,cat_ObraActividad.oa_nombre AS desObra,
                cf.cfi_clave,MAX(cf.cfi_nombre)cfi_nombre,
                sum(pre.origina) as origina,sum(pre.vigente) as vigente,
                sum(pre.comprom) as comprom,
                CASE (sum(pre.comprom)+sum(pre.vigente)) WHEN 0 THEN 0
                    ELSE CAST(ROUND((sum(pre.comprom)/sum(pre.vigente))*100,2) AS CHAR)
                    END AS semaf_comprom,
                CASE
                    WHEN (ROUND((sum(pre.comprom)/sum(pre.vigente)*100),2) BETWEEN 0 AND 49) OR ROUND((sum(pre.comprom)/sum(pre.vigente)*100),2) IS NULL THEN "FF0000"
                    WHEN (ROUND((sum(pre.comprom)/sum(pre.vigente)*100),2) BETWEEN 50 AND 79) THEN "F3C12A"
                    WHEN (ROUND((sum(pre.comprom)/sum(pre.vigente)*100),2) BETWEEN 80 AND 100) THEN "41A62A"
                ELSE "afafaf" END AS color_comprom,
                sum(pre.devenga) as devenga,
                CASE (sum(pre.devenga)+sum(pre.vigente)) WHEN 0 THEN 0  ELSE CAST(ROUND((sum(pre.devenga)/sum(pre.vigente))*100,2) AS CHAR)  END AS semaf_devenga,
                CASE
                    WHEN (ROUND((sum(pre.devenga)/sum(pre.vigente)*100),2) BETWEEN 0 AND 49) OR ROUND((sum(pre.devenga)/sum(pre.vigente)*100),0) IS NULL THEN "FF0000"
                    WHEN (ROUND((sum(pre.devenga)/sum(pre.vigente)*100),2) BETWEEN 50 AND 79) THEN "F3C12A"
                    WHEN (ROUND((sum(pre.devenga)/sum(pre.vigente)*100),2) BETWEEN 80 AND 100) THEN "41A62A"
                ELSE "afafaf" END AS color_devenga,
                sum(pre.ejercid) as ejercid,
                CASE (sum(pre.ejercid)+sum(pre.vigente)) WHEN 0 THEN 0  ELSE CAST(ROUND((sum(pre.ejercid)/sum(pre.vigente))*100,2) AS CHAR)  END AS semaf_ejercid,
                CASE
                    WHEN (ROUND((sum(pre.ejercid)/sum(pre.vigente)*100),2) BETWEEN 0 AND 49) OR ROUND((sum(pre.ejercid)/sum(pre.vigente)*100),2) IS NULL THEN "FF0000"
                    WHEN (ROUND((sum(pre.ejercid)/sum(pre.vigente)*100),2) BETWEEN 50 AND 79) THEN "F3C12A"
                    WHEN (ROUND((sum(pre.ejercid)/sum(pre.vigente)*100),2) BETWEEN 80 AND 100) THEN "41A62A"
                ELSE "afafaf" END AS color_ejercid,
                CASE (pre.vigente+pre.por) WHEN 0 THEN 0  ELSE CAST(ROUND(((pre.por*100)/pre.vigente),2) AS CHAR)  END AS sema,
                CASE
                    WHEN (ROUND(((pre.por*100)/pre.vigente),2) BETWEEN 0 AND 49) OR ROUND(((pre.por*100)/pre.vigente),2) IS NULL THEN "FF0000"
                    WHEN (ROUND(((pre.por*100)/pre.vigente),2) BETWEEN 50 AND 79) THEN "F3C12A"
                    WHEN (ROUND(((pre.por*100)/pre.vigente),2) BETWEEN 80 AND 100) THEN "41A62A"
                ELSE "afafaf" END AS color,
                (pre.por)por
            FROM
                (   SELECT
                        gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,MAX(tcp_id)tcp_id,
                        cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,
                        ' . $consultatri . ',' . $por . '
                    FROM presupuesto
                        JOIN presupuestoAprobado AS apr USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        JOIN presupuestoVigente AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        JOIN presupuestoComprometido AS com USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        JOIN presupuestoDevengado AS dev USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        JOIN presupuestoEjercido AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    WHERE tcp_id<>"P"
                    GROUP BY gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio
                ) AS pre
            LEFT JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            LEFT JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
            LEFT JOIN cat_Programa USING (gpo_id,ur_id,prg_id)
            LEFT JOIN cat_Subprograma USING (gpo_id,ur_id,prg_id,spr_id)
            LEFT JOIN cat_Proyecto USING (gpo_id,ur_id,prg_id,spr_id,py_id)
            LEFT JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            LEFT JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' AS cf USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            LEFT JOIN ' . BD_SINPRES . $this->tablaSuborigen . APP_EJERCICIO . ' c ' . $this->usingSR . '
            LEFT JOIN ' . BD_SINPRES . '.cat_TipoRecurso tr USING (ter_id)
            ' . $filtro . '
            GROUP BY pre.gpo_id,pre.ur_id,pre.ue_id,pre.py_id,py_nombre,desObra,cf.cfi_clave,pre.vigente,pre.ejercid,pre.por
            ' . $filtro_sema . '
            ORDER BY pre.gpo_id,pre.ur_id,cf.cfi_clave,pre.ue_id,prg_id,spr_id;
        ';
    }

    private function verPantallaSeg3()
    {
        $this->setContenedor('divContenedorGeneralModal');
    
        $comboTrimestre = $this->armaComboTrimestreSeg();
        $filtros = $this->armaInputsFiltroSeg3();
        $comboTipoRec = $this->armaComboTipoRecSeg3();
        $comboSemaforizacion = $this->armaComboSemaforizacionSeg3();
        $comboCapitulo = $this->armaComboCapituloSeg3();
        $botones = $this->armaBotonesSeg3();
    
        $div = $this->armaDivFiltrosSeg3($comboTrimestre, $filtros, $comboTipoRec, $comboSemaforizacion, $comboCapitulo, $botones['buscar']);
        $div2 = $this->armaDivAccionesSeg3($botones['exportar'], $botones['imprimir']);
    
        $tabla = $this->armaTablaSeg3($div, $div2);
    
        $this->mixHtml($this->armaModalSeg3($tabla));
    
        $this->recargaCombo($comboTrimestre);
    
        $this->mixScript($this->armaScriptSeg3());
    }
    
    private function armaComboTrimestreSeg()
    {
        $comboTrimestre = new stdClass();
        $comboTrimestre->id = 'comboTrimestre';
        $comboTrimestre->label = 'Trimestre';
        $comboTrimestre->sql = '
            SELECT 1 AS comboTrimestre,"Trimestre" as trim_nombre
                union all
            SELECT 2 AS comboTrimestre,"Trimestre" as trim_nombre
                union all
            SELECT 3 AS comboTrimestre,"Trimestre" as trim_nombre
                union all
            SELECT 4 AS comboTrimestre,"Trimestre" as trim_nombre';
        $comboTrimestre->width = '800px';
        $comboTrimestre->selecciona = (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? $this->prm->trimestre : 1;
        $comboTrimestre->search = true;
    
        return $comboTrimestre;
    }
    
    private function armaInputsFiltroSeg3()
    {
        $definiciones = [
            'ur'           => ['label' => 'UR', 'maxlength' => 3, 'formato' => 'entero'],
            'cfi_clave'    => ['label' => 'CVEFIN', 'maxlength' => 10, 'id' => 'cfi_clave'],
            'ue'           => ['label' => 'UE', 'maxlength' => 3, 'formato' => 'entero'],
            'programa'     => ['label' => 'Programa', 'maxlength' => 3, 'formato' => 'entero'],
            'subprograma'  => ['label' => 'Subprograma', 'maxlength' => 2, 'formato' => 'entero'],
            'proyecto'     => ['label' => 'Proyecto', 'maxlength' => 3, 'formato' => 'entero'],
            'actividad'    => ['label' => 'Actividad', 'maxlength' => 3, 'formato' => 'entero'],
        ];
    
        $inputs = [];
        foreach ($definiciones as $clave => $config) {
            $input = new stdClass();
            $input->id = $config['id'] ?? $clave;
            $input->label = $config['label'];
            $input->maxlength = $config['maxlength'];
            if (isset($config['formato'])) {
                $input->formato = $config['formato'];
            }
            $inputs[$clave] = $input;
        }
    
        return $inputs;
    }
    
    private function armaComboTipoRecSeg3()
    {
        $comboTipoRec = new stdClass();
        $comboTipoRec->id = 'comboTipoRec';
        $comboTipoRec->opciones = [
            'N' => 'TODOS',
            'A' => $this->noEtiq,
            'B' => 'ETIQUETADO',
        ];
        $comboTipoRec->label = 'Tipo Recurso';
        $comboTipoRec->search = true;
        $comboTipoRec->style = 'display:inline !important';
    
        return $comboTipoRec;
    }
    
    private function armaComboSemaforizacionSeg3()
    {
        $comboSemaforizacion = new stdClass();
        $comboSemaforizacion->id = 'comboSema';
        $comboSemaforizacion->label = 'Semaforizacion';
        $comboSemaforizacion->mensaje = 'SELECCIONE UNA OPCION';
        $comboSemaforizacion->opciones = ['' => $this->opcionCons, 'R' => $this->porc1, 'A' => $this->porc2, 'V' => $this->porc3];
        $comboSemaforizacion->width = '400px';
        $comboSemaforizacion->selecciona = (isset($this->prm->sema_id) && $this->prm->sema_id != '') ? $this->prm->sema_id : '';
        $comboSemaforizacion->search = true;
        $comboSemaforizacion->style = 'display:inline !important';
    
        return $comboSemaforizacion;
    }
    
    private function armaComboCapituloSeg3()
    {
        $sql = "
            SELECT '' AS cap_id, 'SELECCIONE UNA OPCION' AS cap_nombre
                UNION ALL
            SELECT cap_id, cap_nombre FROM cat_Capitulo;";
        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();
        $capitulo = [];
        while ($row = $this->extraer()) {
            $capitulo[$row['cap_id']] = $row['cap_nombre'];
        }
        $comboCapitulo = new stdClass();
        $comboCapitulo->id = 'comboCap';
        $comboCapitulo->opciones = $capitulo;
        $comboCapitulo->label = 'Capítulo Gasto';
        $comboCapitulo->width = '400px';
        $comboCapitulo->selecciona = (isset($this->prm->cap_id) && $this->prm->cap_id != '') ? $this->prm->cap_id : '';
        $comboCapitulo->search = true;
        $comboCapitulo->style = 'display:inline !important';
    
        return $comboCapitulo;
    }
    
    private function armaBotonesSeg3()
    {
        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscarS3';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->clase;
        $btnBuscar->icon = $this->iconS;
    
        $exportar4 = new stdClass();
        $exportar4->id = 'exportarS3';
        $exportar4->type = 'submit';
        $exportar4->icon = $this->iconD;
        $exportar4->title = $this->tituloE;
        $exportar4->value = 'Exportar';
        $exportar4->class = $this->claseIn;
    
        $imprimir4 = new stdClass();
        $imprimir4->id = 'imprimirS3';
        $imprimir4->type = 'submit';
        $imprimir4->icon = $this->iconoPdf;
        $imprimir4->title = $this->tituloPdf;
        $imprimir4->value = 'PDF';
        $imprimir4->class = $this->claseSec;
    
        return ['buscar' => $btnBuscar, 'exportar' => $exportar4, 'imprimir' => $imprimir4];
    }

    private function armaDivFiltrosSeg3($comboTrimestre, $filtros, $comboTipoRec, $comboSemaforizacion, $comboCapitulo, $btnBuscar)
    {
        return '
            <div class="d-flex justify-content-around mb-4">
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->templateCombo($comboTrimestre) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($filtros['ur']) . '
                </fieldset>
                    <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($filtros['cfi_clave']) . '
                </fieldset>
                    <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($filtros['ue']) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($filtros['programa']) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($filtros['subprograma']) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($filtros['proyecto']) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboTipoRec) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboSemaforizacion) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboCapitulo) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
            </div>
        ';
    }
    
    private function armaDivAccionesSeg3($exportar4, $imprimir4)
    {
        return '
            <div class="d-flex justify-content-end">
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar4) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($imprimir4) . '
                </fieldset><br>
            </div><br>';
    }
    
    private function armaTablaSeg3($div, $div2)
    {
        $tabla = new stdClass();
        $tabla->titulo = 'CALENDARIO MODIFICADO VS EJERCIDO';
        $tabla->id = 'tbl-ResumenSeg';
        $tabla->tools = $div . $div2;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->trimestre = '{$("#comboTrimestre").val()}';
        $tabla->comunica->cfi_clave = '{$("#cfi_clave").val().toUpperCase()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->ue_id = '{$("#ue").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';
        $tabla->comunica->oa_id = '{$("#actividad").val()}';
    
        $tabla->encabezado = [$this->armaEncabezadoSeg3()];
        $tabla->cuerpo = $this->armaCuerpoSeg3();

        return $tabla;
    }

    private function armaEncabezadoSeg3()
    {
        $columnas = [
            ['texto' => 'CLAVE', 'width' => '5px'],
            ['texto' => 'UNIDAD RESPONSABLE', 'width' => '60px'],
            ['texto' => 'CONCEPTO', 'width' => '50px'],
            ['texto' => 'MODIFICADO', 'width' => '50px'],
            ['texto' => 'EJERCIDO', 'width' => '50px'],
            ['texto' => 'POR EJERCER', 'width' => '50px'],
            ['texto' => 'SEMAFORIZACIÓN EJERCIDO  / MODIFICADO'],
            ['texto' => 'SEMAFORIZACIÓN POR EJERCER/ MODIFICADO'],
            ['texto' => 'PORCENTAJE TOTAL'],
        ];

        $tr = [];
        foreach ($columnas as $columna) {
            $th = new stdClass();
            $th->texto = $columna['texto'];
            if (isset($columna['width'])) {
                $th->width = $columna['width'];
            }
            array_push($tr, $th);
        }
    
        return $tr;
    }
    
    private function armaCuerpoSeg3()
    {
        $campos = [
            ['campoTxt' => 'ur'],
            ['campoTxt' => 'desEntidad'],
            ['campoTxt' => 'desPrograma'],
            ['campoTxt' => 'vigente', 'formato' => 'moneda'],
            ['campoTxt' => 'ejercid', 'formato' => 'moneda'],
            ['campoTxt' => 'porjercer', 'formato' => 'moneda'],
        ];
    
        $trbody = [];
        foreach ($campos as $campo) {
            $td = new stdClass();
            $td->campoTxt = $campo['campoTxt'];
            if (isset($campo['formato'])) {
                $td->formato = $campo['formato'];
            }
            array_push($trbody, $td);
        }
    
        $semaforos = [
            ['linea' => 'color_ejer_mod', 'color' => 'color_ejer_mod', 'campoTxt' => 'cal_ejer_mod', 'lbl' => 'cal_ejer_mod', 'title' => 'cal_ejer_mod'],
            ['linea' => 'color_ejercer_mod', 'color' => 'color_ejercer_mod', 'campoTxt' => 'cal_ejercer_mod', 'lbl' => 'cal_ejercer_mod', 'title' => 'cal_ejercer_mod'],
        ];
    
        foreach ($semaforos as $semaforo) {
            $td = new stdClass();
            $td->tipo = 'bolita';
            $td->linea = $semaforo['linea'];
            $td->color = $semaforo['color'];
            $td->campoTxt = $semaforo['campoTxt'];
            $td->lbl = $semaforo['lbl'];
            $td->title = $semaforo['title'];
            $td->align = 'center';
            array_push($trbody, $td);
        }
    
        $td = new stdClass();
        $td->campoTxt = 'total';
        array_push($trbody, $td);
    
        return $trbody;
    }
    
    private function armaModalSeg3($tabla)
    {
        return '
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="max-width: 1500px!important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">
                                2.UR.-PROG.-SPROG.-PY.-OBRA O ACTIVIDAD-CAPÍTULO
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ' . $this->wrPonTabla($tabla) . '
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary cancelaPt" id="cancelaPt" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ';
    }

    private function armaScriptSeg3()
    {
        return '
            $("#myModal").modal("show");
            $(".cancelaPt").click(function () {
                $("#myModal").remove();
            });
    
            $("#btnBuscarS3").click(function() {
                ' . $this->armaValidacionYComunicaSeg3('llenaTabla3') . '
            });
    
            $("#imprimirS3").click(function() {
                ' . $this->armaValidacionYComunicaSeg3('print', 'PDF') . '
            });
    
            $("#exportarS3").click(function(){
                ' . $this->armaValidacionYComunicaSeg3('print', 'EXCEL') . '
            });
        ';
    }
    
    private function armaValidacionYComunicaSeg3($metodo, $aplicacion = null)
    {
        $extra = $aplicacion !== null
            ? ',aplicacion:"' . $aplicacion . '",rep_id:107'
            : '';
    
        $submit = $aplicacion !== null ? '$("#reportes").submit();' : '';
    
        return '
            if($("#comboTrimestre").val() != "") {
                comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{
                    metodo:"' . $metodo . '",
                    ur_clave:$("#ur").val(),
                    cfi_clave: $("#cfi_clave").val().toUpperCase(),
                    trimestre:$("#comboTrimestre").val(),
                    ue_id:$("#ue").val(),
                    prg_id:$("#programa").val(),
                    spr_id:$("#subprograma").val(),
                    oa_id:$("#actividad").val(),
                    py_id:$("#proyecto").val(),
                    ter_id:$("#comboTipoRec").val(),
                    sema_id:$("#comboSema").val(),
                    cap_id:$("#comboCap").val()' . $extra . '
                });
                ' . $submit . '
            } else {
                notify("Seleccione un trimestre","error");
            }
        ';
    }

    public function llenaTabla3()
    {
        $filtroUr = $this->armaFiltroUrTabla();
        $filtro = $this->armaFiltroTabla3();
        $filtro_sema = $this->armaFiltroSemaforoTabla3();
        $trim = $this->armaConsultaTrimestreTabla3();

        $this->conectar();
        $this->setSql($this->armaSqlTabla3($trim, $filtroUr['gpo_id'], $filtroUr['ur_id'], $filtro, $filtro_sema));
        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ResumenSeg';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    } //Fin llenaTabla3

    private function armaFiltroUrTabla()
    {
        $gpo_id = '';
        $ur_id = '';

        if (isset($this->prm->ur_clave) && $this->prm->ur_clave != '' && strlen($this->prm->ur_clave) == 3) {
            $gpo_id = ' AND gpo_id=' . substr($this->prm->ur_clave, 0, 1) . ' ';
            $ur_id = ' AND ur_id=' . substr($this->prm->ur_clave, 1, 2) . ' ';
        }

        return ['gpo_id' => $gpo_id, 'ur_id' => $ur_id];
    }

    private function armaFiltroTabla3()
    {
        $filtro = '';

        $condicionesSimples = [
            'ue_id'  => 'pre.ue_id',
            'prg_id' => 'pre.prg_id',
            'spr_id' => 'pre.spr_id',
            'py_id'  => 'pre.py_id',
            'oa_id'  => 'pre.oa_id',
        ];

        foreach ($condicionesSimples as $propiedad => $columna) {
            if (isset($this->prm->$propiedad) && $this->prm->$propiedad != '') {
                $filtro .= ' AND ' . $columna . ' = ' . $this->prm->$propiedad;
            }
        }

        if (isset($this->prm->cfi_clave) && $this->prm->cfi_clave != '') {
            $filtro .= ' AND cf.cfi_clave =  "' . $this->prm->cfi_clave . '" ';
        }

        if (isset($this->prm->ter_id) && $this->prm->ter_id != '' && $this->prm->ter_id != 'N') {
            $filtro .= ' AND ter_id = "' . $this->prm->ter_id . '"';
        }

        if (isset($this->prm->cap_id) && $this->prm->cap_id != '') {
            $filtro .= ' AND cap.cap_id = "' . $this->prm->cap_id . '"';
        }

        return $filtro;
    }

    private function armaFiltroSemaforoTabla3()
    {
        $colores = [
            'R' => 'FF0000',
            'A' => 'F3C12A',
            'V' => '41A62A',
        ];

        if (isset($this->prm->sema_id) && isset($colores[$this->prm->sema_id])) {
            return ' HAVING color_ejer_mod ="' . $colores[$this->prm->sema_id] . '" ';
        }

        return '';
    }

    private function armaConsultaTrimestreTabla3()
    {
        $meses = [
            1 => ['ene', 'feb', 'mar'],
            2 => ['abr', 'may', 'jun'],
            3 => ['jul', 'ago', 'sep'],
            4 => ['oct', 'nov', 'dic'],
        ];

        $trimestre = (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? $this->prm->trimestre : 1;
        $mesesTrimestre = $meses[$trimestre] ?? $meses[1];
        [$m1, $m2, $m3] = $mesesTrimestre;

        // Nota: el caso 3 original usaba vig_ene/feb/mar en vez de vig_jul/ago/sep para "vigente".
        // Si es un bug real (no intencional), usa $m1,$m2,$m3 también para "vigente".
        // Aquí se replica el comportamiento original tal cual estaba.
        if ($trimestre == 3) {
            return ' SUM(vig_ene+vig_feb+vig_mar) AS vigente,SUM(eje_' . $m1 . $this->eje . $m2 . $this->eje . $m3 . ') AS ejercid';
        }

        return ' SUM(vig_' . $m1 . $this->vigente . $m2 . $this->vigente . $m3 . ') AS vigente,SUM(eje_' . $m1 . $this->eje . $m2 . $this->eje . $m3 . ') AS ejercid ';
    }

    private function armaSqlTabla3($trim, $gpo_id, $ur_id, $filtro, $filtro_sema)
    {
        return "
            SELECT
                CONCAT(CAST(pre.gpo_id AS CHAR),CAST(pre.ur_id AS CHAR)) AS ur,cat_UnidadResponsable.ur_nombre AS desEntidad,
                CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
                CAST(pre.prg_id AS CHAR) AS Programa, cat_Programa.prg_nombre AS desPrograma,
                CAST(pre.spr_id AS CHAR) AS Subprograma,cat_Subprograma.spr_nombre AS desSubprograma,
                CAST(pre.py_id AS CHAR) AS proyecto,cat_Proyecto.py_nombre AS desProyecto,
                CAST(pre.oa_id AS CHAR) AS obraaccion,cat_ObraActividad.oa_nombre AS desObra,
                cf.cfi_clave,cf.cfi_nombre,
                pre.capitulo,cap.cap_nombre,vigente,ejercid,(vigente - ejercid) porjercer,
                CASE
                    WHEN (ROUND((ejercid/(vigente)*100),2) BETWEEN 0 AND 49.99) OR ROUND(((ejercid)/(vigente)*100),2) IS NULL THEN 'FF0000'
                    WHEN (ROUND((ejercid/(vigente)*100),2) BETWEEN 50 AND 79.99) THEN 'F3C12A'
                    WHEN (ROUND((ejercid/(vigente)*100),2) BETWEEN 80 AND 100) THEN '41A62A'
                    ELSE 'afafaf' END AS color_ejer_mod,
                CASE WHEN ROUND((ejercid/(vigente)*100),2) = 0  OR ROUND((ejercid/(vigente)*100),2)  IS NULL THEN 0
                    else ROUND((ejercid/(vigente)*100),2) end as cal_ejer_mod,
                CASE
                    WHEN (ROUND(((vigente-ejercid)/(vigente)*100),2) BETWEEN 0 AND 49.99) OR ROUND(((vigente-ejercid)/(vigente)*100),2) IS NULL THEN 'FF0000'
                    WHEN (ROUND(((vigente-ejercid)/(vigente)*100),2) BETWEEN 50 AND 79.99) THEN 'F3C12A'
                    WHEN (ROUND(((vigente-ejercid)/(vigente)*100),2) BETWEEN 80 AND 100) THEN '41A62A'
                    ELSE 'afafaf' END AS color_ejercer_mod,
                CASE WHEN ROUND(((vigente-ejercid)/(vigente)*100),2) = 0  OR ROUND(((vigente-ejercid)/(vigente)*100),2)  IS NULL THEN 0
                    else ROUND(((vigente-ejercid)/(vigente)*100),2) end as cal_ejercer_mod,
                CASE WHEN (ROUND((ejercid/(vigente)*100),2) +ROUND(((vigente-ejercid)/(vigente)*100),2) ) = 0
                    OR (ROUND((ejercid/(vigente)*100),2) +ROUND(((vigente-ejercid)/(vigente)*100),2) )  IS NULL THEN 0
                    else (ROUND((ejercid/(vigente)*100),2) +ROUND(((vigente-ejercid)/(vigente)*100),2)  )end
                    AS total
            FROM (
                SELECT
                gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,SUBSTRING(pg_id,1,1) AS capitulo,
                cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,
                $trim
                FROM presupuesto
                JOIN presupuestoVigente AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoEjercido AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                WHERE tcp_id<>'P'
                $gpo_id $ur_id
                GROUP BY gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,
                    SUBSTRING(pg_id,1,1),
                    cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio
            ) AS pre
            LEFT JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            LEFT JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
            LEFT JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
            LEFT JOIN cat_Subprograma USING(gpo_id,ur_id,prg_id,spr_id)
            LEFT JOIN cat_Proyecto USING(gpo_id,ur_id,prg_id,spr_id,py_id)
            LEFT JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            LEFT JOIN
            (
                SELECT clave.*,ter_id FROM " . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' clave
                LEFT JOIN ' . BD_SINPRES . $this->tablaSuborigen . APP_EJERCICIO . ' c ' . $this->usingSR . '
                LEFT JOIN ' . BD_SINPRES . '.cat_TipoRecurso tr USING (ter_id)
            WHERE cfi_ejercicio2=' . APP_EJERCICIO . "
            )
            AS cf USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            LEFT JOIN cat_Capitulo AS cap ON pre.capitulo=cap.cap_id
            WHERE  toa_id='1'
            " . $filtro . "
            GROUP BY pre.gpo_id,ur_id,cf.cfi_clave,pre.ue_id,pre.prg_id,pre.spr_id,pre.py_id,pre.oa_id,pre.capitulo,cf.cfi_nombre,pre.vigente,pre.ejercid
            $filtro_sema
            ORDER BY pre.gpo_id,ur_id,cf.cfi_clave,pre.ue_id,pre.prg_id,pre.spr_id,pre.py_id,pre.oa_id,pre.capitulo
        ";
    }

    private function verPantallaSeg4()
    {
        $this->setContenedor('divContenedorGeneralModal');

        $comboTrimestre = $this->armaComboTrimestreSeg();
        $filtros = $this->armaInputsFiltroSeg4();
        $comboCapitulo = $this->armaComboCapituloSeg4();
        $botones = $this->armaBotonesSeg4();

        $div = $this->armaDivFiltrosSeg4($comboTrimestre, $filtros, $comboCapitulo, $botones['buscar']);
        $div2 = $this->armaDivAccionesSeg4($botones['exportar'], $botones['imprimir']);

        $tabla = $this->armaTablaSeg4($div, $div2);

        $this->mixHtml($this->armaModalSeg4($tabla));

        $this->recargaCombo($comboTrimestre);

        $this->mixScript($this->armaScriptSeg4());
    } /*Fin verPantallaSeg4*/

    private function armaInputsFiltroSeg4()
    {
        $definiciones = [
            'ur'          => ['label' => 'UR', 'maxlength' => 3, 'formato' => 'entero', 'valorProp' => 'ur_clave'],
            'cfi_clave'   => ['label' => 'CVEFIN', 'maxlength' => 10],
            'ue'          => ['label' => 'UE', 'maxlength' => 3, 'formato' => 'entero'],
            'programa'    => ['label' => 'Programa', 'maxlength' => 3, 'formato' => 'entero', 'valorProp' => 'prg_id'],
            'subprograma' => ['label' => 'Subprograma', 'maxlength' => 2, 'formato' => 'entero', 'valorProp' => 'spr_id'],
            'proyecto'    => ['label' => 'Proyecto', 'maxlength' => 3, 'formato' => 'entero'],
            'actividad'   => ['label' => 'Actividad', 'maxlength' => 3, 'formato' => 'entero', 'valorProp' => 'oa_id'],
        ];

        $inputs = [];
        foreach ($definiciones as $clave => $config) {
            $input = new stdClass();
            $input->id = $clave;
            $input->label = $config['label'];
            $input->maxlength = $config['maxlength'];
            if (isset($config['formato'])) {
                $input->formato = $config['formato'];
            }
            if (isset($config['valorProp']) && isset($this->prm->{$config['valorProp']}) && $this->prm->{$config['valorProp']} != '') {
                $input->value = $this->prm->{$config['valorProp']};
            }
            $inputs[$clave] = $input;
        }

        return $inputs;
    }

    private function armaComboCapituloSeg4()
    {
        $sql = "
            SELECT '' AS cap_id, 'SELECCIONE UNA OPCION' AS cap_nombre
                UNION ALL
            SELECT cap_id, cap_nombre FROM cat_Capitulo;";
        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();

        $capitulo = [];
        while ($row = $this->extraer()) {
            $capitulo[$row['cap_id']] = $row['cap_nombre'];
        }

        $comboCapitulo = new stdClass();
        $comboCapitulo->id = 'comboCap';
        $comboCapitulo->opciones = $capitulo;
        $comboCapitulo->label = 'Capítulo Gasto';
        $comboCapitulo->width = '400px';
        $comboCapitulo->selecciona = (isset($this->prm->cap_id) && $this->prm->cap_id != '') ? $this->prm->cap_id : '';
        $comboCapitulo->search = true;
        $comboCapitulo->style = 'display:inline !important';

        return $comboCapitulo;
    }

    private function armaComboTipoActSeg4()
    {
        $sql = "
            SELECT 'N' AS ter_id, 'TODOS' AS ter_nombre
            UNION ALL
            SELECT toa_id, toa_nombre FROM cat_TipoObraActividad;";
        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();

        $tipos_act = [];
        while ($row = $this->extraer()) {
            $tipos_act[$row['ter_id']] = $row['ter_nombre'];
        }

        $comboTipoAct = new stdClass();
        $comboTipoAct->id = 'comboTipoAct';
        $comboTipoAct->label = 'Tipo Act.';
        $comboTipoAct->opciones = $tipos_act;
        $comboTipoAct->selecciona = (isset($this->prm->toa_id) && $this->prm->toa_id != '') ? $this->prm->toa_id : 1;

        return $comboTipoAct;
    }

    private function armaBotonesSeg4()
    {
        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscarS4';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->clase;
        $btnBuscar->icon = $this->iconS;

        $exportar3 = new stdClass();
        $exportar3->id = 'exportarS4';
        $exportar3->type = 'submit';
        $exportar3->icon = $this->iconD;
        $exportar3->title = $this->tituloE;
        $exportar3->value = 'Exportar';
        $exportar3->class = $this->claseIn;

        $imprimir3 = new stdClass();
        $imprimir3->id = 'imprimirS4';
        $imprimir3->type = 'submit';
        $imprimir3->icon = $this->iconoPdf;
        $imprimir3->title = $this->tituloPdf;
        $imprimir3->value = 'PDF';
        $imprimir3->class = $this->claseSec;

        return ['buscar' => $btnBuscar, 'exportar' => $exportar3, 'imprimir' => $imprimir3];
    }

    private function armaDivFiltrosSeg4($comboTrimestre, $filtros, $comboCapitulo, $btnBuscar)
    {
        return '
            <div class="d-flex justify-content-around mb-4">
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->templateCombo($comboTrimestre) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($filtros['ur']) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($filtros['cfi_clave']) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($filtros['ue']) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($filtros['programa']) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($filtros['subprograma']) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($filtros['proyecto']) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($filtros['actividad']) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboCapitulo) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
            </div>
        ';
    }

    private function armaDivAccionesSeg4($exportar3, $imprimir3)
    {
        return '
            <div class="d-flex justify-content-end">
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar3) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($imprimir3) . '
                </fieldset><br>
            </div><br>
        ';
    }

    private function armaTablaSeg4($div, $div2)
    {
        $tabla = new stdClass();
        $tabla->titulo = 'REPORTE DE MODIFICACIONES';
        $tabla->id = 'tbl-AdecuacionActividad';
        $tabla->tools = $div . $div2;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->trimestre = '{$("#comboTrimestre").val()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';
        $tabla->comunica->py_id = '{$("#proyecto").val()}';
        $tabla->comunica->oa_id = '{$("#actividad").val()}';

        $tabla->encabezado = [$this->armaEncabezadoSeg4()];
        $tabla->cuerpo = $this->armaCuerpoSeg4();

        return $tabla;
    }

    private function armaEncabezadoSeg4()
    {
        $columnas = [
            ['texto' => 'CLAVE', 'width' => '5px'],
            ['texto' => 'UNIDAD RESPONSABLE', 'width' => '60px'],
            ['texto' => 'UNIDAD EJECUTORA', 'width' => '50px'],
            ['texto' => 'PROGRAMA'],
            ['texto' => 'SUB-PROGRAMA'],
            ['texto' => 'PROYECTO'],
            ['texto' => 'OBRA ACTIVIDAD'],
            ['texto' => 'DEFINICION'],
            ['texto' => 'TIPO ACTIVIDAD'],
            ['texto' => 'MODFICICADO'],
            ['texto' => 'EJERCIDO'],
            ['texto' => 'POR EJERCER'],
            ['texto' => '% ALCANZADO'],
            ['texto' => 'OBSERVACION REALIZADA'],
            ['texto' => 'ESTADO OBSERVACION'],
        ];

        $tr = [];
        foreach ($columnas as $columna) {
            $th = new stdClass();
            $th->texto = $columna['texto'];
            if (isset($columna['width'])) {
                $th->width = $columna['width'];
            }
            array_push($tr, $th);
        }

        return $tr;
    }

    private function armaCuerpoSeg4()
    {
        $campos = [
            ['campoTxt' => 'ur'],
            ['campoTxt' => 'desEntidad'],
            ['campoTxt' => 'ue_id'],
            ['campoTxt' => 'Programa'],
            ['campoTxt' => 'Subprograma'],
            ['campoTxt' => 'proyecto'],
            ['campoTxt' => 'obraaccion'],
            ['campoTxt' => 'desObra'],
            ['campoTxt' => 'toa_nombre'],
            ['campoTxt' => 'vigente', 'formato' => 'moneda'],
            ['campoTxt' => 'ejercid'],
            ['campoTxt' => 'porejercer'],
            ['campoTxt' => 'porcentaje'],
            ['campoTxt' => 'hms_mensaje'],
            ['campoTxt' => 'estado_mensaje'],
        ];

        $trbody = [];
        foreach ($campos as $campo) {
            $td = new stdClass();
            $td->campoTxt = $campo['campoTxt'];
            if (isset($campo['formato'])) {
                $td->formato = $campo['formato'];
            }
            array_push($trbody, $td);
        }

        return $trbody;
    }

    private function armaModalSeg4($tabla)
    {
        return '
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="max-width: 1500px!important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">
                                3.-ADECUACIONES PRESUPUESTALES <!-- modf. 2020-->
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ' . $this->wrPonTabla($tabla) . '
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary cancelaPt" id="cancelaPt" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>';
    }

    private function armaScriptSeg4()
    {
        return '
            $("#myModal").modal("show");
            $(".cancelaPt").click(function () {
                $("#myModal").remove();
            });

            $("#btnBuscarS4").click(function(){
                ' . $this->armaValidacionYComunicaSeg4('llenaTabla4') . '
            });

            $("#imprimirS4").click(function() {
                ' . $this->armaValidacionYComunicaSeg4('print', 'PDF') . '
            });

            $("#exportarS4").click(function() {
                ' . $this->armaValidacionYComunicaSeg4('print', 'EXCEL') . '
            });
        ';
    }

    private function armaValidacionYComunicaSeg4($metodo, $aplicacion = null)
    {
        $extra = $aplicacion !== null
            ? ',aplicacion:"' . $aplicacion . '",rep_id:108'
            : ',ter_id:$("#comboTipoRec").val()';

        $submit = $aplicacion !== null ? '$("#reportes").submit();' : '';

        return '
            if($("#comboTrimestre").val() != "") {
                comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php",{
                    metodo:"' . $metodo . '",
                    ur_clave:$("#ur").val(),
                    cfi_clave: $("#cfi_clave").val().toUpperCase(),
                    trimestre:$("#comboTrimestre").val(),
                    ue_id:$("#ue").val(),
                    prg_id:$("#programa").val(),
                    spr_id:$("#subprograma").val(),
                    oa_id:$("#actividad").val(),
                    py_id:$("#proyecto").val(),
                    cap_id:$("#comboCap").val()' . $extra . '
                });
                ' . $submit . '
            } else {
                notify("Seleccione un trimestre","error");
            }
        ';
    }

    public function llenaTabla4()
    {
        $filtroUr = $this->armaFiltroUrTabla();
        $filtro = $this->armaFiltroTabla4();
        $datosTrimestre = $this->armaConsultaTrimestreTabla4();
    
        $this->conectar();
        $this->setSql($this->armaSqlTabla4(
            $datosTrimestre['trim'],
            $datosTrimestre['wheretrim'],
            $datosTrimestre['timestre'],
            $filtroUr,
            $filtro
        ));
        $this->consultar();
        $this->desconectar();
    
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-AdecuacionActividad';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    } // Fin llenaTabla4
    
    private function armaFiltroTabla4()
    {
        $condicionesSimples = [
            'ue_id'  => 'p.ue_id',
            'prg_id' => 'prg_id',
            'spr_id' => 'spr_id',
            'oa_id'  => 'oa_id',
            'py_id'  => 'py_id',
        ];
    
        $filtros = [];
        foreach ($condicionesSimples as $propiedad => $columna) {
            $filtros[$propiedad] = (isset($this->prm->$propiedad) && $this->prm->$propiedad != '')
                ? ' AND ' . $columna . '=' . $this->prm->$propiedad
                : '';
        }
    
        $filtros['cvefin'] = (isset($this->prm->cfi_clave) && $this->prm->cfi_clave != '')
            ? ' AND cfi_clave="' . $this->prm->cfi_clave . '" '
            : '';
    
        $filtros['ter_id'] = (isset($this->prm->ter_id) && $this->prm->ter_id != '' && $this->prm->ter_id != 'N')
            ? ' AND ter_id="' . $this->prm->ter_id . '"'
            : '';
    
        $filtros['cap_id'] = (isset($this->prm->cap_id) && $this->prm->cap_id != '')
            ? ' AND SUBSTRING(pg_id,1,1)=' . $this->prm->cap_id
            : '';
    
        return $filtros;
    }
    
    private function armaConsultaTrimestreTabla4()
    {
        $meses = [
            1 => ['ene', 'feb', 'mar'],
            2 => ['abr', 'may', 'jun'],
            3 => ['jul', 'ago', 'sep'],
            4 => ['oct', 'nov', 'dic'],
        ];
    
        $trimestre = $this->prm->trimestre;
        $mesesTrimestre = $meses[$trimestre] ?? $meses[1];
        [$m1, $m2, $m3] = $mesesTrimestre;
    
        $wheretrim = ' AND (vig_' . $m1 . $this->vigente . $m2 . $this->vigente . $m3 . $this->eje . $m1 . $this->eje . $m2 . $this->eje . $m3 . ') <> 0 ';
    
        // Nota: el caso 3 original usa vig_ene/feb/mar para "vigente" en el SELECT (posible bug, ver aparte)
        if ($trimestre == 3) {
            $trim = ' SUM(vig_ene+vig_feb+vig_mar) AS vigente,SUM(eje_' . $m1 . $this->eje . $m2 . $this->eje . $m3 . ') AS ejercid';
        } else {
            $trim = ' SUM(vig_' . $m1 . $this->vigente . $m2 . $this->vigente . $m3 . ') AS vigente,SUM(eje_' . $m1 . $this->eje . $m2 . $this->eje . $m3 . ') AS ejercid ';
        }
    
        return [
            'trim' => $trim,
            'wheretrim' => $wheretrim,
            'timestre' => (string) $trimestre,
        ];
    }
    
    private function armaSqlTabla4($trim, $wheretrim, $timestre, $filtroUr, $filtro)
    {
        return "
            SELECT
                CONCAT(pre.gpo_id,pre.ur_id,pre.ue_id,pre.prg_id,pre.spr_id,pre.py_id,pre.oa_id) AS epg,
                CONCAT(pre.gpo_id,pre.ur_id) AS ur,ur_nombre AS desEntidad,
                CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
                CAST(pre.prg_id AS CHAR) AS Programa, prg_nombre AS desPrograma,
                CAST(pre.spr_id AS CHAR) AS Subprograma,spr_nombre AS desSubprograma,
                CAST(pre.py_id AS CHAR) AS proyecto,py_nombre AS desProyecto,
                CAST(pre.oa_id AS CHAR) AS obraaccion,oa_nombre AS desObra,toa_nombre,
                    pre.vigente,pre.ejercid, (pre.vigente - pre.ejercid) porejercer,
                IF(pre.vigente=0,0,(pre.ejercid*100/pre.vigente)) AS porcentaje,
                IF(msj_id IS NULL,0,msj_id) AS msj_id,
                IF(hms_mensaje IS NULL,'',hms_mensaje) AS hms_mensaje,eme_id,
                CASE WHEN eme_id=1 THEN
                    'MENSAJE GENERADO'
                WHEN eme_id=2 THEN
                    'MENSAJE ENVIADO'
                WHEN eme_id=3 THEN
                    'MENSAJE ELIMINADO'
                WHEN eme_id=4 THEN
                    'MENSAJE SOLVENTADO'
                WHEN eme_id=5 THEN
                    'LEIDO POR LA UR'
                WHEN eme_id=6 THEN
                    'LEIDO POR LA SEFIN'
                ELSE '' END AS estado_mensaje
            FROM (
                SELECT gpo_id,ur_id,p.ue_id,prg_id,spr_id,py_id,oa_id,MAX(epg_id) as epg_id,toa_id,oa_nombre,
                $trim
                FROM presupuesto p
                JOIN presupuestoVigente  AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoEjercido AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                LEFT JOIN " . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' AS cf USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                LEFT JOIN ' . BD_SINPRES . $this->tablaSuborigen . APP_EJERCICIO . ' c ' . $this->usingSR . '
                LEFT JOIN ' . BD_SINPRES . ".cat_TipoRecurso tr USING (ter_id)
                WHERE tcp_id<>'P' AND toa_id=1
                $wheretrim
                {$filtroUr['gpo_id']} {$filtroUr['ur_id']}
                {$filtro['ue_id']} {$filtro['prg_id']} {$filtro['spr_id']} {$filtro['py_id']} {$filtro['oa_id']}
                {$filtro['cvefin']} {$filtro['ter_id']}
                {$filtro['cap_id']}
                GROUP BY gpo_id,ur_id,p.ue_id,prg_id,spr_id,py_id,oa_id
            ) AS pre
            JOIN cat_UnidadResponsable  USING (gpo_id,ur_id)
            JOIN cat_UnidadEjecutora   USING (gpo_id,ur_id,ue_id)
            JOIN cat_Programa          USING (gpo_id,ur_id,prg_id)
            JOIN cat_Subprograma       USING (gpo_id,ur_id,prg_id,spr_id)
            JOIN cat_Proyecto          USING (gpo_id,ur_id,prg_id,spr_id,py_id)
            JOIN cat_TipoObraActividad  AS toa USING (toa_id)
            JOIN enc_Programatico USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            LEFT JOIN (
            SELECT epg_id,msj_id,hms_id,hms_mensaje,eme_id,hms_trimestre FROM cat_MensajeSeguimiento
            JOIN cat_HistorialMensajeSeguimiento USING (msj_id)
            WHERE eme_id IN (2,4,5,6) AND tim_id=2 AND hms_Trimestre=$timestre
            ) AS msj ON pre.epg_id=msj.epg_id
            ORDER BY pre.gpo_id,pre.ur_id,pre.ue_id,pre.prg_id,pre.spr_id,pre.py_id,pre.oa_id;";
    }

    // Este metodo hace la comunicacion con el .NET
    public function imprime()
    {
        $salt = '$BF8AsdL3t-cw13z20'; //LLAVE
        $nose = MD5($this->clasAdm_gpo . $salt . $this->clasAdm_ur);
        $clave = strtoupper($nose);
        $this->sesion($clave);
        $this->mixScript(' console.log(' . $this->prm->rep_id . ')');

        $todas = $this->armaTodasReporte();
        $servidorReporte = $this->armaServidorReporte();

        $datosReporte = $this->armaCamposReporte($this->prm->rep_id, $todas, $servidorReporte);

        $script = ($datosReporte !== null)
            ? $this->armaFormularioReporte($datosReporte['campos'], $datosReporte['target'], $clave)
            : '';

        $this->mixScript('' . $script . '');
    } //Fin imprime

    private function armaTargetReporte()
    {
        switch ($this->prm->aplicacion) {
            case 'PDF':
                return 'det_fs' . $this->prm->rep_id;
            default:
                return '_blank';
        }
    }

    private function armaTodasReporte()
    {
        return (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? 'NO' : 'SI';
    }

    private function armaServidorReporte()
    {
        switch ($this->server2) {
            case '172.17.90.17':
                return 'PRODUCCION';
            case '172.17.90.18':
                return 'PRUEBA';
            default:
                return 'PRUEBA';
        }
    }

    private function valorTrimestre()
    {
        return (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? $this->prm->trimestre : 1;
    }

    private function valorMes()
    {
        return (isset($this->prm->mes) && $this->prm->mes != '') ? $this->prm->mes : 1;
    }

    private function armaCamposReporte($rep_id, $todas, $servidorReporte)
    {
        $finales = [
            ['todas', $todas],
            ['servidor', $servidorReporte],
            ['aplicacion', $this->prm->aplicacion],
        ];

        // Reportes que SIEMPRE abren en '_blank', sin importar $this->prm->aplicacion
        $repsTargetFijo = [104, 105, 106, 107, 108];
        $target = in_array($rep_id, $repsTargetFijo) ? '_blank' : $this->armaTargetReporte();

        switch ($rep_id) {
            case 104:
                $especificos = [
                    ['mesAP', $this->valorMes()],
                    ['ur_clave', $this->prm->ur_clave],
                    ['ue_id', $this->prm->ue_id],
                    ['prg_id', $this->prm->prg_id],
                    ['py_id', $this->prm->py_id],
                    ['spr_id', $this->prm->spr_id],
                    ['cap_id', $this->prm->cap_id],
                    ['cfi_clave', $this->prm->cfi_clave],
                    ['oa_id', $this->prm->oa_id],
                ];
                break;

            case 105:
                $especificos = [
                    ['trimestre', $this->valorTrimestre()],
                    ['ur_clave', $this->prm->ur_clave],
                    ['ue_id', $this->prm->ue_id],
                    ['prg_id', $this->prm->prg_id],
                    ['spr_id', $this->prm->spr_id],
                    ['cfi_clave', $this->prm->cfi_clave],
                    ['oa_id', $this->prm->oa_id],
                    ['py_id', $this->prm->py_id],
                    ['ter_id', $this->prm->ter_id],
                    ['sema_id', $this->prm->sema_id],
                ];
                break;

            case 106:
                $especificos = [
                    ['trimestre', $this->valorTrimestre()],
                    ['trimestreHistorial', $this->valorTrimestre()],
                    ['ur_clave', $this->prm->ur_clave],
                    ['ue_id', $this->prm->ue_id],
                    ['prg_id', $this->prm->prg_id],
                    ['spr_id', $this->prm->spr_id],
                    ['cfi_clave', $this->prm->cfi_clave],
                    ['oa_id', $this->prm->oa_id],
                    ['py_id', $this->prm->py_id],
                    ['ter_id', $this->prm->ter_id],
                    ['cap_id', $this->prm->cap_id],
                ];
                break;

            case 107:
                $especificos = [
                    ['ur_clave', $this->prm->ur_clave],
                    ['ue_id', $this->prm->ue_id],
                    ['prg_id', $this->prm->prg_id],
                    ['spr_id', $this->prm->spr_id],
                    ['numTrimestre', $this->valorTrimestre()],
                    ['cfi_clave', $this->prm->cfi_clave],
                    ['oa_id', (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? $this->prm->oa_id : ''],
                    ['py_id', $this->prm->py_id],
                    ['ter_id', $this->prm->ter_id],
                    ['sema_id', $this->prm->sema_id],
                    ['cap_id', $this->prm->cap_id],
                ];
                break;

            case 108:
                $especificos = [
                    ['trimestreMP', $this->valorTrimestre()],
                    ['ur_clave', $this->prm->ur_clave],
                    ['ue_id', $this->prm->ue_id],
                    ['prg_id', $this->prm->prg_id],
                    ['py_id', $this->prm->py_id],
                    ['spr_id', $this->prm->spr_id],
                    ['cap_id', $this->prm->cap_id],
                    ['cfi_clave', $this->prm->cfi_clave],
                    ['oa_id', $this->prm->oa_id],
                ];
                break;

            case 111:
                $especificos = [
                    ['mescal', $this->valorMes()],
                    ['trimestreHistorial', $this->valorMes()],
                    ['cfi_clave', $this->prm->cfi_clave],
                    ['ue_id', $this->prm->ue_id],
                    ['prg_id', $this->prm->prg_id],
                    ['spr_id', $this->prm->spr_id],
                    ['oa_id', $this->prm->oa_id],
                    ['py_id', $this->prm->py_id],
                    ['ter_id', $this->prm->ter_id],
                    ['cap_id', $this->prm->cap_id],
                    ['ur_clave', $this->prm->ur_clave],
                ];
                break;

            case 112:
                $especificos = [
                    ['mesSegGtos', $this->valorMes()],
                    ['cfi_clave', $this->prm->cfi_clave],
                    ['ue_id', $this->prm->ue_id],
                    ['prg_id', $this->prm->prg_id],
                    ['spr_id', $this->prm->spr_id],
                    ['oa_id', $this->prm->oa_id],
                    ['py_id', $this->prm->py_id],
                    ['ter_id', $this->prm->ter_id],
                    ['sema_id', $this->prm->sema_id],
                    ['ur_clave', $this->prm->ur_clave],
                ];
                break;

            case 113:
                $especificos = [
                    ['mescal', $this->valorMes()],
                    ['cfi_clave', $this->prm->cfi_clave],
                    ['ue_id', $this->prm->ue_id],
                    ['prg_id', $this->prm->prg_id],
                    ['spr_id', $this->prm->spr_id],
                    ['oa_id', $this->prm->oa_id],
                    ['py_id', $this->prm->py_id],
                    ['ter_id', $this->prm->ter_id],
                    ['sema_id', $this->prm->sema_id],
                    ['cap_id', $this->prm->cap_id],
                    ['ur_clave', $this->prm->ur_clave],
                ];
                break;

            default:
                return null;
        }

        $campos = array_merge($especificos, $finales);

        return ['campos' => $campos, 'target' => $target];
    }

    private function armaFormularioReporte($camposEspecificos, $target, $clave)
    {
        $action = 'scp/reportes/get_pdf.php';

        $inputs = '';
        $inputs .= $this->armaInputHidden('gpo_id', $this->clasAdm_gpo);
        $inputs .= $this->armaInputHidden('ur_id', $this->clasAdm_ur);
        $inputs .= $this->armaInputHidden('SSL875JJNF9', $clave);
        $inputs .= $this->armaInputHidden('numrep', $this->prm->rep_id);
        $inputs .= $this->armaInputHidden('rep_id', $this->prm->rep_id);

        foreach ($camposEspecificos as $campo) {
            $inputs .= $this->armaInputHidden($campo[0], $campo[1]);
        }

        return '
            var n = $("<form/>")
            .attr("id","repodi")
            .attr("action","' . $action . '")
            .attr("method","post")
            .attr("target","' . $target . '")
            ' . $inputs . '
            ;
            n.appendTo("body");
            n.submit();
            delete n;
            $("#repodi").remove();
        ';
    }

    private function armaInputHidden($nombre, $valor)
    {
        return '.append( $("<input/>").attr("name","' . $nombre . '").attr("value","' . $valor . '").attr("type","hidden") )' . "\n";
    }

    // Se revisa el funcionamiento de la sesion para este controlador
    public function sesion($par)
    {
        $this->conectar();
        $sql = 'DELETE FROM ' . BD_TEMPORAL . '.tem_SessionReporte WHERE ssr_id =?';
        $this->setSql($sql);
        $this->consultar([$par]);
        $sql = 'INSERT INTO ' . BD_TEMPORAL . '.tem_SessionReporte VALUES("' . $par . '")';
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();
    }
}

if (isset($_SESSION[_S]['prm']['A']['11']['12']['1'])) {
    new ControladorReporteSeguimientoGO();
} else {
    WebResponse::sinPermisos();
}
