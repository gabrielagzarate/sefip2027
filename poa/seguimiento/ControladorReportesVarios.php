<?php

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';

/**
 * Autor: Eduardo Venegas
 * Fecha de creación: 27/03/2019
 * Se modifico las variables de sesion y de servicios, ahora se instancian en el contructor.
 */

class ControladorReportesVarios extends WebResponse
{
    private $nvj;
    private $usu;
    private $id;
    private $gpo_id;
    private $ur_id;
    private $clasAdm_gpo;
    private $clasAdm_ur;
    private $server2;
    private $noEtiquet;
    private $opciones;
    private $claseSec;
    private $tituloE;
    private $iconD;
    private $tituloEx;
    private $claseIn;
    private $tituloP;
    private $iconoPdf;
    private $tituloPdf;
    private $claseSecXs;

    //Constructor

    public function __construct()
    {

        //mandar a llamar constructor base
        parent::__construct();

        $this->nvj = $_SESSION[_S]['nvj_id'];
        $this->usu = isset($_SESSION[_S]['usu']) ? $_SESSION[_S]['usu'] : '';
        $this->id = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : '';
        $this->gpo_id = $_SESSION[_S]['gpo_id'];
        $this->ur_id = $_SESSION[_S]['ur_id'];
        $this->clasAdm_gpo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->clasAdm_ur = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : '';

        $this->server2 = $_SERVER['SERVER_ADDR'];
        $this->noEtiquet = 'NO ETIQUETADO';
        $this->opciones = 'Selecciona el tipo de actividad';
        $this->claseSec = 'btn btn-outline-secondary me-1';
        $this->tituloE = 'Exportar';
        $this->iconD = 'fa fa-cloud-download me-2';
        $this->tituloEx = 'Exportar a Excel';
        $this->claseIn = 'btn btn-xs btn-outline-info me-1';
        $this->tituloP = 'PDF';
        $this->iconoPdf = 'fa-solid fa-file-pdf me-2';
        $this->tituloPdf = 'Imprimir en PDF';
        $this->claseSecXs = 'btn btn-xs btn-outline-secondary me-1';

        switch ($this->nvj) {
            case 1:case 2:case 3:case 4:case 5:case 6:case 7:case 8:case 9:
                switch ($this->metodo) {
                    default:
                        $this->mainReportes();
                        break;
                    case 'formulario':
                        $this->formulario();
                        break;
                    case 'verPantallaReporte':
                        $this->verPantallaReporte();
                        break;
                    case 'llenaTablaMetas':
                        $this->llenaTablaMetas();
                        break;
                    case 'verDetalleSeguimiento':
                        $this->verDetalleSeguimiento();
                        break;
                    case 'llenaDetalleSeguimiento':
                        $this->llenaDetalleSeguimiento();
                        break;
                    case 'DetalleActividad':
                        $this->DetalleActividad();
                        break;
                    case 'llenaDetalleActividad':
                        $this->llenaDetalleActividad();
                        break;
                    case 'DetalleAdecuacionActividad':
                        $this->DetalleAdecuacionActividad();
                        break;
                    case 'llenaDetalleAdecuacionActividad':
                        $this->llenaDetalleAdecuacionActividad();
                        break;
                    case 'DetalleCalendarioModDev':
                        $this->DetalleCalendarioModDev();
                        break;
                    case 'llenaDetalleCalendarioModDev':
                        $this->llenaDetalleCalendarioModDev();
                        break;
                    case 'llenaTablaPresupuestoUrPrSpr':
                        $this->llenaTablaPresupuestoUrPrSpr();
                        break;
                    case 'seleccionaCorte':
                        $this->seleccionaCorte();
                        break;
                    case 'seleccionaperiodo':
                        $this->seleccionaperiodo();
                        break;
                    case 'verPantallaSemaforizacion':
                        $this->verPantallaSemaforizacion();
                        break;
                    case 'verPantallaObsMetas':
                        $this->verPantallaObsMetas();
                        break;
                    case 'llenaTablaSemaMetas':
                        $this->llenaTablaSemaMetas();
                        break;
                    case 'llenaTablaObsMetas':
                        $this->llenaTablaObsMetas();
                        break;
                    case 'print':
                        $this->imprime();
                        break;

                        // Reportes Seguimiento Presupuestario
                    case 'verPantallaSeg1':
                        $this->verPantallaSeg1();
                        break;
                    case 'llenaTabla1':
                        $this->llenaTabla1();
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
                    case 'verPantallaSegObs':
                        $this->verPantallaSegObs();
                        break;
                    case 'llenaTablaObs':
                        $this->llenaTablaObs();
                        break;
                    case 'verPantallaSegM1':
                        $this->verPantallaSegM1();
                        break;
                    case 'llenaTablaSegM1':
                        $this->llenaTablaSegM1();
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
                    case 'llenaTablaTrimMetas':
                        $this->llenaTablaTrimMetas();
                        break;

                        // Reportes del ticket 227699 relacionado con reportes de transparencia
                    case 'verPantallaSemaforizacionPresupuestoTransparencia':
                        $this->verPantallaSemaforizacionPresupuestoTransparencia();
                        break;
                    case 'verPantallaSemaforizacionPresupuestoTransparenciaExcel':
                        $this->verPantallaSemaforizacionPresupuestoTransparenciaExcel();
                        break;
                    case 'llenaTablaSemPreTrans':
                        $this->llenaTablaSemPreTrans();
                        break;
                    case 'verPantallaPre7Transparencia':
                        $this->verPantallaPre7Transparencia();
                        break;
                    case 'llenaTablaPre7Trans':
                        $this->llenaTablaPre7Trans();
                        break;
                    case 'verPantallaSeg8MetasTransparencia':
                        $this->verPantallaSeg8MetasTransparencia();
                        break;
                    case 'verPantallaSeg10MetasTransparencia':
                        $this->verPantallaSeg10MetasTransparencia();
                        break;
                    case 'llenaTablaSegMetasTransparencia':
                        $this->llenaTablaSegMetasTransparencia();
                        break;
                        //---------------------
                    case 'verPantallaMetasProgramadas':
                        $this->verPantallaMetasProgramadas();
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

        /*
        Castro Jordy 15-07-18
        En el caso de los tres reportes de transparencia se bloquearan para que la LIC. DOLORES SOLO LA PUEDA VER ELLA. En base
        al ticket 227699.
         */
        $reportesMetas = '';
        // Se cambio el id de "250" a "163" ya que en 2019 el usr DGARCIAC tiene este id
        if ((isset($this->usu) && $this->usu == 'VGARCIAG' && isset($this->id) && $this->id == 459)
            //ID 181 para el usuario de Japhet Mata y 495 para Eduardo Venegas
            || $this->id == 181 || $this->id == 495
        ) {
            $reportesMetas = '
                <tr>
                    <td align="center">
                        <button id="pantallaSegui8" class="btn btn-outline-secondary" id="egresosSA6" nombre="" title="">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                    <td><b>REPORTE DE SEGUIMIENTO A METAS PARA TRANSPARENCIA</b></td>
                    <div id="ReporteSA8"></div>
                </tr>
                <tr>
                    <td align="center">
                        <button id="pantallaSegui10" class="btn btn-outline-secondary" id="egresosSA6" nombre="" title="">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                    <td><b>REPORTE DE SEGUIMIENTO A METAS PARA TRANSPARENCIA (EXCEL)</b></td>
                    <div id="ReporteSA10"></div>
                </tr>
                <tr>
                    <td align="center">
                        <button id="pantallaSegui9" class="btn btn-outline-secondary" id="egresosSA6" nombre="" title="">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                    <td><b>REPORTE DE SEMAFORIZACIÓN CON PRESUPUESTO</b></td>
                    <div id="ReporteSA9"></div>
                </tr>
                <tr>
                    <td align="center">
                        <button id="pantallaSegui11" class="btn btn-outline-secondary" id="egresosSA6" nombre="" title="">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                    <td><b>REPORTE DE SEMAFORIZACIÓN CON PRESUPUESTO (EXCEL)</b></td>
                    <div id="ReporteSA11"></div>
                </tr>
                <tr>
                    <td align="center">
                        <button id="pantallaSegui12" class="btn btn-outline-secondary" id="egresosSA6" nombre="" title="">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                    <td><b>REPORTE DE METAS PROGRAMADAS</b></td>
                    <div id="ReporteSA12"></div>
                </tr>
            ';
        }

        switch ($this->prm->opcion->claveOpcion) {
            case 'A_11_9':
                $this->mixHtml('
                    <div class="container">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="success">PDF</th>
                                    <th  class="success">NOMBRE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!--
                                <tr>
                                    <td align="center">
                                        <button id="pantalla" class="btn btn-outline-secondary" id="egresosAG" nombre="Reporte avance de Gestión" title="Ver Reporte"><i class="fa-solid fa-eye"></i></button>
                                    </td>
                                    <td><b>REPORTE AVANCE DE GESTIÓN</b></td>
                                    <div id="ReporteAG"></div>
                                </tr>
                                Se comentan estos reportes, ya que aun no estan migrados -->
                                <!--
                                <tr>
                                    <td align="center">
                                        <button id="pantallaSegui1" class="btn btn-outline-secondary" id="egresosSA" nombre="" title=""><i class="fa-solid fa-eye"></i></button>
                                    </td>
                                    <td><b>UR-PROG-SUBPROGRAMA</b></td>
                                    <div id="ReporteSA"></div>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <button id="pantallaSegui2" class="btn btn-outline-secondary" id="egresosSA2" nombre="" title=""><i class="fa-solid fa-eye"></i></button>
                                    </td>
                                    <td><b>UR-PROG-SUBPROGRAMA-ACTIVIDAD/CONCEPTO</b></td>
                                    <div id="ReporteSA2"></div>
                                </tr>
                                -->

                                <tr>
                                    <td align="center">
                                        <button id="pantallaSegui3" class="btn btn-outline-secondary" id="egresosSA3" nombre="" title=""><i class="fa-solid fa-eye"></i></button>
                                    </td>
                                    <td><b>ADECUACIONES (ACTIVIDAD)</b></td>
                                    <div id="ReporteSA3"></div>
                                </tr>

                                <!--
                                <tr>
                                    <td align="center">
                                        <button id="pantallaSegui4" class="btn btn-outline-secondary" id="egresosSA4" nombre="" title=""><i class="fa-solid fa-eye"></i></button>
                                    </td>
                                    <td><b>CALENDARIO MODIFICADO VS EJERCICIO</b></td>
                                    <div id="ReporteSA4"></div>
                                </tr>

                                <tr>
                                    <td align="center">
                                        <button id="pantallaSegui5" class="btn btn-outline-secondary" id="egresosSA5" nombre="" title="">
                                        <i class="fa-solid fa-eye"></i></button>
                                    </td>
                                    <td> <b>OBSERVACIONES</b>
                                    </td>
                                <div id="ReporteSA5"></div>
                                </tr>
                                -->
                                <!--
                                <tr>
                                    <td align="center">
                                        <button id="pantallaSegui6" class="btn" id="egresosSA6" nombre="" title="">
                                        <i class="glyphicon glyphicon-eye-open"></i></button>
                                    </td>
                                    <td><b>REPORTE DE SEMAFORIZACIÓN DE METAS</b></td>
                                    <div id="ReporteSA6"></div>
                                </tr>
                                -->
                                ' . $reportesMetas . '
                            </tbody>
                            </b>
                        </table>
                    </div>
                    <div id="div_ues"></div>
                ');
                $this->mixScript('
                    $("#pantalla").click(function(){
                        comunica("poa/seguimiento/ControladorReportesVarios.php",{metodo:"verPantallaReporte"});
                    });

                    $("#pantallaSegui1").click(function(){
                        comunica("poa/seguimiento/ControladorReportesVarios.php",{
                            metodo:"verDetalleSeguimiento",
                            rep_id:48
                        });
                    });

                    $("#pantallaSegui2").click(function(){
                        comunica("poa/seguimiento/ControladorReportesVarios.php",{metodo:"DetalleActividad"});
                    });

                    $("#pantallaSegui3").click(function(){
                        comunica("poa/seguimiento/ControladorReportesVarios.php",{metodo:"DetalleAdecuacionActividad"});
                    });

                    $("#pantallaSegui4").click(function(){
                        comunica("poa/seguimiento/ControladorReportesVarios.php",{metodo:"DetalleCalendarioModDev"});
                    });

                    $("#pantallaSegui5").click(function(){
                        comunica("poa/seguimiento/ControladorReportesVarios.php",{metodo:"verPantallaObsMetas"});
                    });

                    $("#pantallaSegui6").click(function(){
                        comunica("poa/seguimiento/ControladorReportesVarios.php",{metodo:"verPantallaSemaforizacion"});
                    });

                    //Nuevo reporte, solo genera Excel
                    $("#pantallaSegui10").click(function(){
                        comunica("poa/seguimiento/ControladorReportesVarios.php",{
                            metodo:"verPantallaSeg10MetasTransparencia"
                        });
                    });

                    // --------------------------------------------- Castro Jordy 10-06-18 - Scripts para los reportes de transparencia
                    $("#pantallaSegui7").click(function(){
                        comunica("poa/seguimiento/ControladorReportesVarios.php",{
                            metodo:"verPantallaPre7Transparencia"
                        });
                    });

                    $("#pantallaSegui8").click(function(){
                        comunica("poa/seguimiento/ControladorReportesVarios.php",{
                            metodo:"verPantallaSeg8MetasTransparencia"
                        });
                    });

                    $("#pantallaSegui9").click(function(){
                        comunica("poa/seguimiento/ControladorReportesVarios.php",{
                            metodo:"verPantallaSemaforizacionPresupuestoTransparencia"
                        });
                    });
                    $("#pantallaSegui11").click(function(){
                        comunica("poa/seguimiento/ControladorReportesVarios.php",{
                            metodo:"verPantallaSemaforizacionPresupuestoTransparenciaExcel"
                        });
                    });

                    $("#pantallaSegui12").click(function(){
                        comunica("poa/seguimiento/ControladorReportesVarios.php",{
                            metodo:"verPantallaMetasProgramadas"
                        });
                    });

                    //-----------------------------------------------------------------------------------------

                    $("#reptd").click( function(e) {
                        var form = $("<form>")
                            .attr("action", "rep/doc/rep_Reporte.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "encabLin1").attr("value", "SECRETARIA DE FINANZAS.   EJERCICIO ' . substr(APP_EJERCICIO, 0, 4) . '" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "encabLin2").attr("value", "PRESUPUESTO AUTORIZADO, INFORMACION PARA TABLAS DINAMICAS" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "buscar").attr("value", "" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "tipo").attr("value", "xls" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "sql").attr("value", "poa1" ));
                        $("body").append(form);
                        form.submit();
                    });
                ');
                break;
            case 'A_11_12':
                $this->mixScript('
                    comunica("poa/seguimiento/ControladorReporteSeguimientoGO.php", {
                        metodo:"seleccionaCorte"
                    })
                ');
                break;
            default:
                break;
        }
    }

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
            '' => 'Seleccione una opción de la lista',
            1  => 'Mensual',
            2  => 'Trimestral',
        ];
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
    }

    //Función que rellena la información del combo de acuerdo al corte seleccionado
    public function seleccionaperiodo()
    {
        //botón que regresa a la pantalla anterior
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = '&nbsp;&nbsp;Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = 'btn btn-test';
        $btnregresarindex->icon = 'glyphicon glyphicon-hand-left';
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
                            <tr>
                                <td align="center">
                                    <button id="pantallaS1" class="btn btn-outline-secondary" id="seguimiento1" nombre="" title="">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </td>
                                <td><b>UR.-PROG.-SPROG</b></td>
                                <div id="ReporteSG1"></div>
                            </tr>
                            <tr>
                                <td align="center">
                                    <button id="pantallaS2" class="btn btn-outline-secondary" id="seguimiento2" nombre="" title="">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </td>
                                <td><b>UR.-PROG.-SPROG.-PY.-OBRA O ACTIVIDAD</b></td>
                                <div id="ReporteSG2"></div>
                            </tr>
                            <tr>
                                <td align="center">
                                    <button id="pantallaS3" class="btn btn-outline-secondary" id="seguimiento3" nombre="" title="">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </td>
                                <td><b>UR.-PROG.-SPROG.-PY.-OBRA O ACTIVIDAD-CAPÍTULO(COMPARATIVO)</b></td>
                                <div id="ReporteSG3"></div>
                            </tr>
                            <tr>
                                <td align="center">
                                    <button id="pantallaS4" class="btn btn-outline-secondary" id="seguimiento4" nombre="" title="">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </td>
                                <td><b>MODIFICACIONES PRESUPUESTALES</b></td>
                                <div id="ReporteSG4"></div>
                            </tr>
                            <tr>
                                <td align="center">
                                    <button id="pantallaS5" class="btn btn-outline-secondary" id="seguimiento5" nombre="" title="">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </td>
                                <td><b>OBSERVACIONES</b></td>
                                <div id="ReporteSG5"></div>
                            </tr>
                        </tbody>
                        </b>
                    </table>
                </div>
                <div id="div_ues"></div>
            ');
            $this->mixScript('
                $("#pantallaS1").click(function(){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{metodo:"verPantallaSeg1"});
                });

                $("#pantallaS2").click(function(){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"verPantallaSeg2",
                        rep_id:48
                    });
                });

                $("#pantallaS3").click(function(){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{metodo:"verPantallaSeg3"});
                });

                $("#pantallaS4").click(function(){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{metodo:"verPantallaSeg4"});
                });

                $("#pantallaS5").click(function(){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{metodo:"verPantallaSegObs"});
                });

                $("#reptd").click( function(e) {
                    var form = $("<form>")
                        .attr("action", "rep/doc/rep_Reporte.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "encabLin1").attr("value", "SECRETARIA DE FINANZAS.   EJERCICIO ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . '" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "encabLin2").attr("value", "PRESUPUESTO AUTORIZADO, INFORMACION PARA TABLAS DINAMICAS" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "buscar").attr("value", "" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "tipo").attr("value", "xls" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "sql").attr("value", "poa1" ));
                    $("body").append(form);
                    form.submit();
                });
            ');
        }
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
                            <tr>
                                <td align="center">
                                    <button id="pantallaSM1" class="btn btn-outline-secondary" id="seguimientoM1" nombre="" title="">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </td>
                                <td><b>UR.-PROG.-SPROG</b></td>
                                <div id="ReporteSGM1"></div>
                            </tr>
                            <tr>
                                <td align="center">
                                    <button id="pantallaSM2" class="btn btn-outline-secondary" id="seguimientoM2" nombre="" title="">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </td>
                                <td><b>UR.-PROG.-SPROG.-PY.-OBRA O ACTIVIDAD</b></td>
                                <div id="ReporteSGM2"></div>
                            </tr>
                            <tr>
                                <td align="center">
                                    <button id="pantallaSM3" class="btn btn-outline-secondary" id="seguimientoM3" nombre="" title="">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </td>
                                <td><b>UR.-PROG.-SPROG.-PY.-OBRA O ACTIVIDAD-CAPÍTULO(COMPARATIVO)</b></td>
                                <div id="ReporteSGM3"></div>
                            </tr>
                        </tbody>
                        </b>
                    </table>
                </div>
            ');
            $this->mixScript('
                $("#pantallaSM1").click(function(){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"verPantallaSegM1",
                        rep_id:111
                    });
                });

                $("#pantallaSM2").click(function(){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"verPantallaSegM2",
                        rep_id:112
                    });
                });

                $("#pantallaSM3").click(function(){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"verPantallaSegM3",
                        rep_id:113
                    });
                });

                $("#reptd").click( function(e) {
                    var form = $("<form>")
                        .attr("action", "rep/doc/rep_Reporte.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "encabLin1").attr("value", "SECRETARIA DE FINANZAS.   EJERCICIO ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . '" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "encabLin2").attr("value", "PRESUPUESTO AUTORIZADO, INFORMACION PARA TABLAS DINAMICAS" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "buscar").attr("value", "" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "tipo").attr("value", "xls" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "sql").attr("value", "poa1" ));
                    $("body").append(form);
                    form.submit();
                });
            ');
        }
    }

    public function formulario()
    {
        $key = 'Gt6Fty4W8';
        $publicRoute = '';
        //Dependiendo del servidor es el host
        switch ($this->server2) {
            case '172.17.90.17':
                $publicRoute = 'https://reportessinpres.finanzasoaxaca.gob.mx/Reportes2121/';
                break;
            case '172.17.90.18':
                $publicRoute = 'https://qreportessinpres.finanzasoaxaca.gob.mx/Reportes2121v2/';
                break;
            default:
                $publicRoute = 'http://localhost:5898/crystal_reports/Reportes2121_Prueba/';
        }
        //Se pone cambia, ahora se triangula
        $publicRoute = 'scp/reportes/get_pdf.php';

        $html = '';
        $ssl = strtoupper(md5($this->gpo_id . $key . $this->ur_id));

        $html .= '<form id="formularioreportes" action="' . $publicRoute . '" method="post" target="_blank">';
        $html .= '<input type="hidden" id="tipo" name="tipo" value="' . $this->prm->application . '"/>';
        $html .= '<input type="hidden" id="rep" name="pe" value="poa_rep"/>';
        $html .= '<input type="hidden" id="ssl" name="ssl" value="' . $ssl . '"/>';
        if ($this->nvj < 3) {
            $html .= '<input type="hidden" id="gpour" name="gpour" value="' . $this->gpo_id . $this->ur_id . '"/>';
        }

        $gpour = new stdClass();
        $gpour->id = 'gpour';
        $gpour->name = 'gpour';
        $gpour->required = true;
        $gpour->enDiv = true;
        $gpour->camposVal = 'gpour';
        $gpour->agrupaPor = 'gpour';
        $gpour->campoLabelAgrupacion = 'gpour';
        $gpour->mensaje = 'Selecciona';
        $gpour->label = 'UNIDAD RESPONSABLE';
        $gpour->comoAtr = 'gpour';
        $gpour->sql = 'SELECT CONCAT(gpo_id,ur_id) AS gpour,ur_nombre FROM cat_UnidadResponsable ORDER BY gpour';

        if ($this->nvj > 2) {
            $html .= $this->ponCombo($gpour);
        }

        $html .= '<fieldset style="width:50%"> ';
        $html .= '</fieldset>';
        $html .= '</form>';
        $this->mixHtml($html);
        $this->agregaTools(
            '
            <button id="btn_regresar" class="btn"> <i class="glyphicon glyphicon-hand-left"></i> Regresar</button>
            <button type="button" id="btn_imprimir" class="btn"> <i class="glyphicon glyphicon-print"></i> Imprimir</button>'
        );
        $this->mixScript('
            $("#btn_imprimir").click(function(){
                $("#formularioreportes").submit();
            });

            $("#btn_regresar").click(function(){
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("poa/seguimiento/ControladorReportesVarios.php");
            });

            $("#prg_id").keypress(function(event){
                var anularTecla = false;
                console.log(event.which);
                var esPermitido = Array.indexOf([0,8], event.which) >= 0;
                anularTecla = ((event.which < 48 ||  event.which > 57) && !esPermitido) ;
                if( anularTecla ) event.preventdefault();
            });
        ');
    }

    public function verPantallaReporte()
    {
        $this->setContenedor('divContenedorGeneralModal');

        //filtro por trimestre
        $comboTrimestre = new stdClass();
        $comboTrimestre->id = 'comboTrimestre';
        $comboTrimestre->label = 'Trimestre';
        $comboTrimestre->opciones = [
            '' => 'Seleccione una opción de la lista',
            1  => 'Trimestre',
            2  => 'Trimestre',
            3  => 'Trimestre',
            4  => 'Trimestre',
        ];
        $comboTrimestre->width = '400px';
        $comboTrimestre->selecciona = (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? $this->prm->trimestre : 1;
        $comboTrimestre->search = true;

        $comboTipoRec = new stdClass();
        $comboTipoRec->id = 'comboTipoRec';
        $comboTipoRec->opciones = [
            'N' => 'TODOS',
            'A' => $this->noEtiquet,
            'B' => 'ETIQUETADO',
        ];
        $comboTipoRec->label = 'Tipo de Recurso';
        $comboTipoRec->style = 'display:inline !important';

        $ur = new stdClass();
        $ur->id = 'ur';
        $ur->label = 'UR';
        $ur->maxlength = 3;
        $ur->formato = 'entero';
        $ur->value = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ur_clave : '';

        $ue = new stdClass();
        $ue->id = 'ue';
        $ue->label = 'UE';
        $ue->maxlength = 3;
        $ue->formato = 'entero';
        $ue->value = (isset($this->prm->ue_id) && $this->prm->ue_id != '') ? $this->prm->ue_id : '';

        $programa = new stdClass();
        $programa->id = 'programa';
        $programa->label = 'Programa';
        $programa->maxlength = 3;
        $programa->formato = 'entero';
        $programa->value = (isset($this->prm->prg_id) && $this->prm->prg_id != '') ? $this->prm->prg_id : '';

        $subprograma = new stdClass();
        $subprograma->id = 'subprograma';
        $subprograma->label = 'Subprograma';
        $subprograma->maxlength = 3;
        $subprograma->formato = 'entero';
        $subprograma->value = (isset($this->prm->spr_id) && $this->prm->spr_id != '') ? $this->prm->spr_id : '';

        $proyecto = new stdClass();
        $proyecto->id = 'proyecto';
        $proyecto->label = 'Proyecto';
        $proyecto->maxlength = 3;
        $proyecto->formato = 'entero';
        $proyecto->value = (isset($this->prm->py_id) && $this->prm->py_id != '') ? $this->prm->py_id : '';

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';
        $actividad->value = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? $this->prm->oa_id : '';

        $sql = 'SELECT toa_id, toa_nombre FROM cat_TipoObraActividad WHERE toa_id not LIKE "3";';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();
        $tipos_act = ['' => $this->opciones];
        while ($row = $this->extraer()) {
            $tipos_act[$row['toa_id']] = $row['toa_nombre'];
        }
        $comboTipoAct = new stdClass();
        $comboTipoAct->id = 'comboTipoAct';
        $comboTipoAct->opciones = $tipos_act;
        $comboTipoAct->label = 'Tipo Actividad';
        $comboTipoAct->selecciona = (isset($this->prm->toa_id) && $this->prm->toa_id != '') ? $this->prm->toa_id : 1;

        $porcentaje = new stdClass();
        $porcentaje->id = 'porcentaje';
        $porcentaje->formato = 'numero';

        $porcentaje2 = new stdClass();
        $porcentaje2->id = 'porcentaje2';
        $porcentaje2->formato = 'numero';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscar';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar = new stdClass();
        $exportar->id = 'exportar';
        $exportar->value = $this->tituloE;
        $exportar->type = 'submit';
        $exportar->icon = $this->iconD;
        $exportar->title = $this->tituloEx;
        $exportar->class = $this->claseIn;

        $imprimir = new stdClass();
        $imprimir->id = 'imprimir';
        $imprimir->value = $this->tituloP;
        $imprimir->type = 'submit';
        $imprimir->icon = $this->iconoPdf;
        $imprimir->title = $this->tituloPdf;
        $imprimir->class = $this->claseSecXs;

        $tabla = new stdClass();
        $tabla->titulo = 'Lista de Obras/Actividades por trimestre';
        $tabla->id = 'tbl-Metas';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE';
        $th->width = '5px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD RESPONSABLE';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD EJECUTORA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'SUB-PROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NUM ACT/OBRA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ACT/OBRA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'OFICIO DE AUTORIZACIÓN';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NÚM. IND';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NÚM. META';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROGRAMADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ACTUALIZADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ALCANZADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = '% ALCANZADO';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'ur_clave';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ur_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ue_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'prg_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'spr_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'oa_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'oa_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ore_oficioAut';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ind_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'mem_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'mac_oriTrim';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'mac_modTrim';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'mac_alcTrim';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'porcentaje';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Avance de Gestión</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <fieldset class="col-md-2 col-xs-2">
                                    ' . $this->ponCombo($comboTrimestre) . '
                                </fieldset>
                                <fieldset class="col-md-2 col-xs-2">
                                    ' . $this->ponCombo($comboTipoRec) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponInput($ur) . '
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
                                    ' . $this->ponCombo($comboTipoAct) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    <label>Porcentaje</label><br/>
                                    DEL ' . $this->ponInput($porcentaje) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    <label></label><br/>
                                    AL ' . $this->ponInput($porcentaje2) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponBoton($btnBuscar) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponBoton($exportar) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponBoton($imprimir) . '
                                </fieldset>
                            </div>
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
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#btnBuscar").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"llenaTablaMetas",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        ter_id:$("#comboTipoRec").val(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        oa_id:$("#actividad").val(),
                        toa_id:$("#comboTipoAct").val(),
                        porcentaje:$("#porcentaje").val(),
                        porcentaje2:$("#porcentaje2").val(),
                    });
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#exportar").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        ter_id:$("#comboTipoRec").val(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        oa_id:$("#actividad").val(),
                        toa_id:$("#comboTipoAct").val(),
                        porcentaje:$("#porcentaje").val(),
                        porcentaje2:$("#porcentaje2").val(),
                        aplicacion:"EXCEL",
                        rep_id:121
                    });
                    $("#reportes").submit();
                }else{
                    notify("Seleccione un trimestre","error");
                }
                console.log("Todo bien");
            });

            $("#imprimir").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        ter_id:$("#comboTipoRec").val(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        oa_id:$("#actividad").val(),
                        toa_id:$("#comboTipoAct").val(),
                        porcentaje:$("#porcentaje").val(),
                        porcentaje2:$("#porcentaje2").val(),
                        aplicacion:"PDF",
                        rep_id:121
                    });
                    $("#reportes").submit();
                }else{
                    notify("Seleccione un trimestre","error");
                }
                console.log("Todo bien");
            });
        ');
    }

    private function llenaTablaMetas()
    {
        $ur = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? ' AND CONCAT(gpo_id,ur_id) =' . $this->prm->ur_clave : '';
        $ue = (isset($this->prm->ue_id) && $this->prm->ue_id != '') ? ' AND ue_id=' . $this->prm->ue_id : '';
        $ter_id = '';
        if (isset($this->prm->ter_id) && $this->prm->ter_id != '') {
            if ($this->prm->ter_id != 'N') {
                $ter_id .= ' AND ter_id = "' . $this->prm->ter_id . '"';
            }
        }
        $prg = (isset($this->prm->prg_id) && $this->prm->prg_id != '') ? ' AND prg_id=' . $this->prm->prg_id : '';
        $spr = (isset($this->prm->spr_id) && $this->prm->spr_id != '') ? ' AND spr_id=' . $this->prm->spr_id : '';
        $py = (isset($this->prm->py_id) && $this->prm->py_id != '') ? ' AND py_id=' . $this->prm->py_id : '';
        $oa = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? ' AND oa_id=' . $this->prm->oa_id : '';
        $toa_id = (isset($this->prm->toa_id) && $this->prm->toa_id != '') ? ' AND toa_id=' . $this->prm->toa_id : '';
        $tri_id = (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? ' AND tri_id=' . $this->prm->trimestre : '';
        $porcentaje = (isset($this->prm->porcentaje) && $this->prm->porcentaje != '') ? ' AND ROUND((mact_alcanzado/mact_modificado)*100,2) BETWEEN ' . $this->prm->porcentaje . ' AND ' . $this->prm->porcentaje2 : '';

        switch ($this->prm->trimestre) {
            case 1:
                $trim = ' AND tri_id= 1';
                break;
            case 2:
                $trim = ' AND tri_id= 2';
                break;
            case 3:
                $trim = ' AND tri_id= 3';
                break;
            case 4:
                $trim = ' AND tri_id= 4';
                break;
        }

        $sql = '
            SELECT CONCAT(pre.gpo_id,pre.ur_id) AS ur_clave,cat_UnidadResponsable.ur_nombre AS ur_nombre,
            CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
            CAST(pre.prg_id AS CHAR) AS prg_id,cat_Programa.prg_nombre AS despgma,
            CAST(pre.spr_id AS CHAR) AS spr_id,cat_Subprograma.spr_nombre AS desspgma,
            CAST(pre.py_id AS CHAR) AS py_id,cat_Proyecto.py_nombre AS desproyecto,
            CAST(pre.oa_id AS CHAR) AS oa_id,cat_ObraActividad.oa_nombre AS oa_nombre,
            CONCAT(pre.gpo_id,pre.ur_id,pre.ue_id,pre.prg_id,pre.spr_id,pre.py_id,pre.oa_id) AS ep,
            pre.epg_id,toa_nombre,pre.vigente,
            ind_id , pii_id as mem_id ,mact_original AS mac_oriTrim,mact_modificado AS mac_modTrim,mact_alcanzado AS mac_alcTrim,
            ((mact_alcanzado/mact_modificado)*100) AS porcentaje,tri_id AS trimestre
                FROM
                ( SELECT gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,MAX(epg_id) as epg_id,SUM(pre_vigente) AS vigente
                FROM presupuesto
                    JOIN presupuestoAprobado AS apr USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    JOIN presupuestoVigente AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN ( SELECT * FROM ' . BD_SINPRES . '.cat_SuborigenRecurso ' . APP_EJERCICIO . ' WHERE sor_ejercicio=' . APP_EJERCICIO . ' ) AS cso USING(cff_id,fte_id,sft_id,ori_id,sor_id)
                WHERE tcp_id<>"P" AND (pre_aprobado+pre_vigente)<>0
                    ' . $ur . '
                    ' . $ter_id . '
                    ' . $ue . '
                GROUP BY gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id ) AS pre
                LEFT JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                LEFT JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
                LEFT JOIN cat_Programa USING (gpo_id,ur_id,prg_id)
                LEFT JOIN cat_Subprograma USING (gpo_id,ur_id,prg_id,spr_id)
                LEFT JOIN cat_Proyecto USING (gpo_id,ur_id,prg_id,spr_id,py_id)
                LEFT JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                LEFT JOIN cat_TipoObraActividad USING (toa_id)
                LEFT JOIN cat_MetaActividadTrimestre ON pre.epg_id=cat_MetaActividadTrimestre.epg_id
                LEFT JOIN vin_ProductoIntermedioIndicador USING (pii_id) -- ind_id
                LEFT JOIN cat_Indicador USING (nim_id,ind_id)
                WHERE gpo_id is not null
                    ' . $oa . '
                    ' . $py . '
                    ' . $prg . '
                    ' . $spr . '
                    ' . $toa_id . '
                    ' . $porcentaje . '
                    ' . $trim . '
                ORDER BY pre.gpo_id,pre.ur_id,pre.ue_id,pre.prg_id,pre.spr_id,pre.py_id,pre.oa_id
        ';

        $this->setSql($sql);
        $this->conectar();
        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Metas';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }

    private function llenaTablaPresupuestoUrPrSpr()
    {
        $ur = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? ' AND ur_clave=' . $this->prm->ur_clave : '';
        $ue = (isset($this->prm->ue_id) && $this->prm->ue_id != '') ? ' AND ue_id=' . $this->prm->ue_id : '';
        $prg = (isset($this->prm->prg_id) && $this->prm->prg_id != '') ? ' AND prg_id=' . $this->prm->prg_id : '';
        $spr = (isset($this->prm->spr_id) && $this->prm->spr_id != '') ? ' AND spr_id=' . $this->prm->spr_id : '';
        $oa = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? ' AND oa_id=' . $this->prm->oa_id : '';
        $porcentaje = (isset($this->prm->porcentaje) && $this->prm->porcentaje != '') ? ' AND ROUND((alc_trimestre' . $this->prm->trimestre . '/mod_trimestre' . $this->prm->trimestre . ')*100,2) BETWEEN ' . $this->prm->porcentaje . ' AND ' . $this->prm->porcentaje2 : '';

        switch ($this->prm->trimestre) {
            case 1:
                $where = " <= '2026-03-31' ";
                break;
            case 2:
                $where = " <= '2026-06-30' ";
                break;
            case 3:
                $where = " <= '2026-09-30' ";
                break;
            case 4:
                $where = " <= '2026-12-31' ";
                break;
        }

        $this->setSql('
            SELECT ur_clave, ur_nombre, ue_id, prg_id,spr_id,oa_id, oa_nombre, ind_id,mem_id,
                mem_original, ori_trimestre1, ori_trimestre2, ori_trimestre3, ori_trimestre4,
                mem_modificado,mod_trimestre1,mod_trimestre2,mod_trimestre3,mod_trimestre4,
                mem_alcanzado, alc_trimestre1, alc_trimestre2, alc_trimestre3, alc_trimestre4,
                ori_trimestre' . $this->prm->trimestre . ' AS mac_oriTrim,mod_trimestre' . $this->prm->trimestre . ' AS mac_modTrim,
                alc_trimestre' . $this->prm->trimestre . ' AS mac_alcTrim,
                IF(alc_trimestre' . $this->prm->trimestre . ' !=0 , ROUND((alc_trimestre' . $this->prm->trimestre . '/mod_trimestre' . $this->prm->trimestre . ')*100,2) , 0.00) AS porcentaje
            FROM enc_Programatico
            INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            INNER JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
            INNER JOIN cat_Subprograma USING(gpo_id,ur_id,prg_id,spr_id)
            INNER JOIN cat_ObraActividad o USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            INNER JOIN cat_MetaIndicadorProductoIntermedio USING(epg_id)
            INNER JOIN enc_IndicadorProductoIntermedio USING (ipi_id)
            INNER JOIN cat_Indicador USING(nim_id,ind_id)
            WHERE DATE(o.usr_fecha)' . $where . $ur . $ue . $prg . $spr . $oa . '
            ' . $porcentaje . '
            ORDER BY ur_clave
            ;
        ');
        $this->conectar();
        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Metas';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }

    private function verDetalleSeguimiento()
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
        $comboTrimestre->width = '400px';
        $comboTrimestre->selecciona = (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? $this->prm->trimestre : 1;
        $comboTrimestre->search = true;

        $ur = new stdClass();
        $ur->id = 'ur';
        $ur->label = 'UR';
        $ur->maxlength = 3;
        $ur->formato = 'entero';
        $ur->value = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ur_clave : '';

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

        $sql = 'SELECT toa_id, toa_nombre FROM cat_TipoObraActividad;';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();
        $tipos_act = ['' => $this->opciones];
        while ($row = $this->extraer()) {
            $tipos_act[$row['toa_id']] = $row['toa_nombre'];
        }

        $comboTipoAct = new stdClass();
        $comboTipoAct->id = 'comboTipoAct';
        $comboTipoAct->opciones = $tipos_act;
        $comboTipoAct->label = 'Tipo Actividad';
        $comboTipoAct->selecciona = (isset($this->prm->toa_id) && $this->prm->toa_id != '') ? $this->prm->toa_id : 1;

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscar1';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar1 = new stdClass();
        $exportar1->id = 'exportar1';
        $exportar1->value = $this->tituloE;
        $exportar1->type = 'submit';
        $exportar1->icon = $this->iconD;
        $exportar1->title = $this->tituloEx;
        $exportar1->class = $this->claseIn;

        $imprimir1 = new stdClass();
        $imprimir1->id = 'imprimir1';
        $imprimir1->value = $this->tituloP;
        $imprimir1->type = 'submit';
        $imprimir1->icon = $this->iconoPdf;
        $imprimir1->title = $this->tituloPdf;
        $imprimir1->class = $this->claseSecXs;

        $ponur = '';
        if ($this->nvj > 2) {
            $ponur = $this->ponInput($ur);
        }

        $tools = '
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->templateCombo($comboTrimestre) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $ponur . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($programa) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($subprograma) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboTipoAct) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar1) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($imprimir1) . '
                </fieldset>
            </div><br/>
        ';

        $tabla = new stdClass();
        $tabla->titulo = 'UR - PROGRAMA - SUBPROGRAMA';
        $tabla->id = 'tbl-ProgramaSubprograma';
        $tabla->tools = $tools;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';
        $tabla->comunica->trimestre = '{$("#comboTrimestre").val()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE';
        $th->width = '5px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROGRAMA';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'SUBPROGRAMA';
        $th->width = '60px';
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
        $th = new stdClass();
        $th->texto = 'DEVENGADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'EJERCIDO';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'prg_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'spr_id';
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
        $td = new stdClass();
        $td->campoTxt = 'devenga';
        $td->formato = 'moneda';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ejercid';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">1. UR-PROGRAMA-SUBPROGRAMA</h4>
                            <button type="button" class="btn-close cerrar" data-bs-dismiss="modal" aria-label="Close"></button>
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
            <script type="text/javascript" src="js/jma_viewer.js?1" id="scri_pdf" />
        ');
        $this->recargaCombo($comboTrimestre);

        $this->mixScript('
            $("#myModal").modal("show");
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#btnBuscar1").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"llenaDetalleSeguimiento",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        toa_id:$("#comboTipoAct").val()
                    });
                }else{
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#imprimir1").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        toa_id:$("#comboTipoAct").val(),
                        aplicacion:"PDF",
                        rep_id:48
                    });
                    $("#reportes").submit();
                }else{
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#exportar1").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        toa_id:$("#comboTipoAct").val(),
                        aplicacion:"EXCEL",
                        rep_id:48
                    });
                    $("#reportes").submit();
                }else{
                    notify("Seleccione un trimestre","error");
                }
            });
        ');
    }

    private function llenaDetalleSeguimiento()
    {
        $urclave = '';
        $prg = '';
        $spr = '';
        $filtro = '';
        $AND = '';
        if (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') {
            $filtro .= ' AND CONCAT(gpo_id,ur_id)="' . $this->prm->ur_clave . '"';
        }

        if (isset($this->prm->prg_id) && $this->prm->prg_id != '') {
            $filtro .= ' AND prg_id="' . $this->prm->prg_id . '"';
        }

        if (isset($this->prm->spr_id) && $this->prm->spr_id != '') {
            $filtro .= ' AND spr_id="' . $this->prm->spr_id . '"';
        }

        if (isset($this->prm->toa_id) && $this->prm->toa_id != '') {
            $filtro .= ' AND toa_id="' . $this->prm->toa_id . '"';
        }

        if ($this->nvj <= 2) {
            $filtro .= ' AND CONCAT(gpo_id,ur_id)="' . $this->clasAdm_gpo . $this->clasAdm_ur . '"';
        }

        $trimestre = (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1);
        switch ($trimestre) {
            case 1:
                $consultatri = '
                    SUM(apr_ene+apr_feb+apr_mar) AS origina,
                    SUM(vig_ene+vig_feb+vig_mar) AS vigente,
                    SUM(com_ene+com_feb+com_mar) AS comprom,
                    SUM(dev_ene+dev_feb+dev_mar) AS devenga,
                    SUM(eje_ene+eje_feb+eje_mar) AS ejercid';
                break;
            case 2:
                $consultatri = '
                    SUM(apr_abr+apr_may+apr_jun) AS origina,
                    SUM(vig_abr+vig_may+vig_jun) AS vigente,
                    SUM(com_abr+com_may+com_jun) AS comprom,
                    SUM(dev_abr+dev_may+dev_jun) AS devenga,
                    SUM(eje_abr+eje_may+eje_jun) AS ejercid';
                break;
            case 3:
                $consultatri = '
                    SUM(apr_jul+apr_ago+apr_sep) AS origina,
                    SUM(vig_jul+vig_ago+vig_sep) AS vigente,
                    SUM(com_jul+com_ago+com_sep) AS comprom,
                    SUM(dev_jul+dev_ago+dev_sep) AS devenga,
                    SUM(eje_jul+eje_ago+eje_sep) AS ejercid';
                break;
            case 4:
                $consultatri = '
                    SUM(apr_oct+apr_nov+apr_dic) AS origina,
                    SUM(vig_oct+vig_nov+vig_dic) AS vigente,
                    SUM(com_oct+com_nov+com_dic) AS comprom,
                    SUM(dev_oct+dev_nov+dev_dic) AS devenga,
                    SUM(eje_oct+eje_nov+eje_dic) AS ejercid';
                break;
        }
        $this->conectar();
        $this->setSql('
            SELECT
            CONCAT(pre.gpo_id,pre.ur_id) AS ur,cat_UnidadResponsable.ur_nombre AS desEntidad,
                CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
                CAST(pre.prg_id AS CHAR) AS prg_id,cat_Programa.prg_nombre AS despgma,
                CAST(pre.spr_id AS CHAR) AS spr_id,cat_Subprograma.spr_nombre AS desspgma,
                pre.origina,pre.vigente,pre.comprom,pre.devenga,pre.ejercid
            FROM
            (
                SELECT
                    gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,tcp_id,
                    ' . $consultatri . "
                FROM presupuesto
                    JOIN presupuestoAprobado AS apr USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    JOIN presupuestoVigente AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    JOIN presupuestoComprometido AS com USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    JOIN presupuestoDevengado AS dev USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    JOIN presupuestoEjercido AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                GROUP BY gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id
            ) AS pre
                LEFT JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                LEFT JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
                LEFT JOIN cat_Programa USING (gpo_id,ur_id,prg_id)
                LEFT JOIN cat_Subprograma USING (gpo_id,ur_id,prg_id,spr_id)
                LEFT JOIN cat_ObraActividad USING (gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id)
            WHERE tcp_id<>'P'
            " . $filtro . '
            -- GROUP BY pre.gpo_id,pre.ur_id,pre.prg_id,pre.spr_id
            ORDER BY pre.gpo_id,pre.ur_id,pre.prg_id,pre.spr_id;
        ');
        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ProgramaSubprograma';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }

    }

    private function DetalleActividad()
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

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';
        $actividad->value = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? $this->prm->oa_id : '';

        $sql = 'SELECT toa_id, toa_nombre FROM cat_TipoObraActividad;';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();
        $tipos_act = ['' => $this->opciones];
        while ($row = $this->extraer()) {
            $tipos_act[$row['toa_id']] = $row['toa_nombre'];
        }

        $comboTipoAct = new stdClass();
        $comboTipoAct->id = 'comboTipoAct';
        $comboTipoAct->opciones = $tipos_act;
        $comboTipoAct->label = 'Tipo Actividad';
        $comboTipoAct->selecciona = (isset($this->prm->toa_id) && $this->prm->toa_id != '') ? $this->prm->toa_id : 1;

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscar2';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar2 = new stdClass();
        $exportar2->id = 'exportar2';
        $exportar2->type = 'submit';
        $exportar2->icon = $this->iconD;
        $exportar2->title = $this->tituloEx;
        $exportar2->value = $this->tituloE;
        $exportar2->class = $this->claseIn;

        $imprimir2 = new stdClass();
        $imprimir2->id = 'imprimir2';
        $imprimir2->type = 'submit';
        $imprimir2->icon = $this->iconoPdf;
        $imprimir2->title = $this->tituloPdf;
        $imprimir2->value = $this->tituloP;
        $imprimir2->class = $this->claseSecXs;

        $div = '
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->templateCombo($comboTrimestre) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($ur) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($programa) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($subprograma) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($actividad) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboTipoAct) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar2) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($imprimir2) . '
                </fieldset>
            </div>
        ';

        $tabla = new stdClass();
        $tabla->titulo = 'UR-PROGRAMA-SUBPROGRAMA-ACTIVIDAD/CONCEPTO';
        $tabla->id = 'tbl-ProgramaSubprogramaActividad';
        $tabla->tools = $div;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';
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
        $th = new stdClass();
        $th->texto = 'PROGRAMA';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'SUBPROGRAMA';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ACTIVIDAD';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CONCEPTO';
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
        $th = new stdClass();
        $th->texto = 'DEVENGADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'EJERCIDO';
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
        $td->campoTxt = 'prg_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'spr_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'obraaccion';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'desObra';
        array_push($trbody, $td);

        $aprobado = '';
        $vigente = '';
        $comprometido = '';
        $devengado = '';
        $ejercido = '';

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
        $td = new stdClass();
        $td->campoTxt = 'devenga';
        $td->formato = 'moneda';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ejercid';
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
        $td = new stdClass();
        $td->campoTxt = 'por';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'colorTrim';
        $td->color = 'colorTrim';
        $td->campoTxt = 'porcentajeTrim';
        $td->lbl = 'porcentajeTrim';
        $td->title = 'porcentajeTrim';
        $td->align = 'center';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">2. UR-PROG-SUBPROGRAMA-ACTIVIDAD/CONCEPTO</h4>
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
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#btnBuscar2").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"llenaDetalleActividad",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        toa_id:$("#comboTipoAct").val()
                    });
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#imprimir2").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        toa_id:$("#comboTipoAct").val(),
                        aplicacion:"PDF",
                        rep_id:49
                    });
                    $("#reportes").submit();
                }else{
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#exportar2").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        toa_id:$("#comboTipoAct").val(),
                        aplicacion:"EXCEL",
                        rep_id:49
                    });
                    $("#reportes").submit();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });
        ');
    }

    private function llenaDetalleActividad()
    {
        $filtro = '';
        $AND = '';
        $porcentajePresupuesto = 0;

        if (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') {
            $filtro .= ' AND CONCAT(gpo_id,ur_id)="' . $this->prm->ur_clave . '"';
        }

        if (isset($this->prm->prg_id) && $this->prm->prg_id != '') {
            $filtro .= ' AND prg_id="' . $this->prm->prg_id . '"';
        }

        if (isset($this->prm->spr_id) && $this->prm->spr_id != '') {
            $filtro .= ' AND spr_id="' . $this->prm->spr_id . '"';
        }

        if (isset($this->prm->oa_id) && $this->prm->oa_id != '') {
            $filtro .= ' AND oa_id="' . $this->prm->oa_id . '"';
        }

        if (isset($this->prm->toa_id) && $this->prm->toa_id != '') {
            $filtro .= ' AND toa_id="' . $this->prm->toa_id . '"';
        }
        //Swicth de parámetros de busqueda por trimestre
        $trimestre = (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1);
        switch ($trimestre) {
            case 1:
                $trim = '
                    round(((100*(SUM(eje_ene) + SUM(eje_feb) + SUM(eje_mar)))/(SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar))),2) as porcentajeTrim,
                    CASE WHEN (round(((100*(SUM(eje_ene) + SUM(eje_feb) + SUM(eje_mar)))/(SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar))),2) BETWEEN 0 AND 25) THEN "b20000"
                    WHEN (round(((100*(SUM(eje_ene) + SUM(eje_feb) + SUM(eje_mar)))/(SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar))),2) BETWEEN 26 AND 49) THEN "f1ee0b"
                    WHEN (round(((100*(SUM(eje_ene) + SUM(eje_feb) + SUM(eje_mar)))/(SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar))),2) BETWEEN 50 AND 100) THEN "35B200"
                    ELSE "b20000" END AS colorTrim';
                $consultatri = '
                    SUM(apr_ene+apr_feb+apr_mar) AS origina,
                    SUM(vig_ene+vig_feb+vig_mar) AS vigente,
                    SUM(com_ene+com_feb+com_mar) AS comprom,
                    SUM(dev_ene+dev_feb+dev_mar) AS devenga,
                    SUM(eje_ene+eje_feb+eje_mar) AS ejercid';
                $por = '(SUM(vig_ene+vig_feb+vig_mar) - SUM(eje_ene+eje_feb+eje_mar)) as por';
                break;
            case 2:
                $trim = '
                    round(((100*(SUM(eje_abr) + SUM(eje_may) + SUM(eje_jun)))/(SUM(vig_abr) + SUM( vig_may) + SUM(vig_jun))),2) as porcentajeTrim,
                    CASE WHEN (round(((100*(SUM(eje_abr) + SUM(eje_may) + SUM(eje_jun)))/(SUM(vig_abr) + SUM( vig_may) + SUM(vig_jun))),2) BETWEEN 0 AND 25) THEN "b20000"
                    WHEN (round(((100*(SUM(eje_abr) + SUM(eje_may) + SUM(eje_jun)))/(SUM(vig_abr) + SUM(vig_may) + SUM(vig_jun))),2) BETWEEN 26 AND 49) THEN "f1ee0b"
                    WHEN (round(((100*(SUM(eje_abr) + SUM(eje_may) + SUM(eje_jun)))/(SUM(vig_abr) + SUM(vig_may) + SUM(vig_jun))),2) BETWEEN 50 AND 100) THEN "35B200"
                    ELSE "b20000" END AS colorTrim';
                $consultatri = '
                    SUM(apr_abr+apr_may+apr_jun) AS origina,
                    SUM(vig_abr+vig_may+vig_jun) AS vigente,
                    SUM(com_abr+com_may+com_jun) AS comprom,
                    SUM(dev_abr+dev_may+dev_jun) AS devenga,
                    SUM(eje_abr+eje_may+eje_jun) AS ejercid';
                $por = '(SUM(vig_abr+vig_may+vig_jun) - SUM(eje_abr+eje_may+eje_jun)) as por';
                break;
            case 3:
                $trim = '
                    round(((100*(SUM(eje_jul) + SUM(eje_ago) + SUM(eje_sep)))/(SUM(vig_jul) + SUM(vig_ago) + SUM(vig_sep))),2) as porcentajeTrim,
                    CASE WHEN (round(((100*(SUM(eje_jul) + SUM(eje_ago) + SUM(eje_sep)))/(SUM(vig_jul) + SUM(vig_ago) + SUM(vig_sep))),2) BETWEEN 0 AND 25) THEN "b20000"
                    WHEN (round(((100*(SUM(eje_jul) + SUM(eje_ago) + SUM(eje_sep)))/(SUM(vig_jul) + SUM(vig_ago) + SUM(vig_sep))),2) BETWEEN 26 AND 49) THEN "f1ee0b"
                    WHEN (round(((100*(SUM(eje_jul) + SUM(eje_ago) + SUM(eje_sep)))/(SUM(vig_jul) + SUM(vig_ago) + SUM(vig_sep))),2) BETWEEN 50 AND 100) THEN "35B200"
                    ELSE "b20000" END AS colorTrim';
                $consultatri = '
                    SUM(apr_jul+apr_ago+apr_sep) AS origina,
                    SUM(vig_jul+vig_ago+vig_sep) AS vigente,
                    SUM(com_jul+com_ago+com_sep) AS comprom,
                    SUM(dev_jul+dev_ago+dev_sep) AS devenga,
                    SUM(eje_jul+eje_ago+eje_sep) AS ejercid';
                $por = '(SUM(vig_jul+vig_ago+vig_sep) - SUM(eje_jul+eje_ago+eje_sep)) as por';
                break;
            case 4:
                $trim = '
                    round(((100*(SUM(eje_oct) + SUM(eje_nov) + SUM(eje_dic)))/(SUM(vig_oct) + SUM( vig_nov) + SUM(vig_dic))),2) as porcentajeTrim,
                    CASE WHEN (round(((100*(SUM(eje_oct) + SUM(eje_nov) + SUM(eje_dic)))/(SUM(vig_oct) + SUM( vig_nov) + SUM(vig_dic))),2) BETWEEN 0 AND 25) THEN "b20000"
                    WHEN (round(((100*(SUM(eje_oct) + SUM(eje_nov) + SUM(eje_dic)))/(SUM(vig_oct) + SUM(vig_nov) + SUM(vig_dic))),2) BETWEEN 26 AND 49) THEN "f1ee0b"
                    WHEN (round(((100*(SUM(eje_oct) + SUM(eje_nov) + SUM(eje_dic)))/(SUM(vig_oct) + SUM(vig_nov) + SUM(vig_dic))),2) BETWEEN 50 AND 100) THEN "35B200"
                    ELSE "b20000" END AS colorTrim';
                $consultatri = '
                    SUM(apr_oct+apr_nov+apr_dic) AS origina,
                    SUM(vig_oct+vig_nov+vig_dic) AS vigente,
                    SUM(com_oct+com_nov+com_dic) AS comprom,
                    SUM(dev_oct+dev_nov+dev_dic) AS devenga,
                    SUM(eje_oct+eje_nov+eje_dic) AS ejercid';
                $por = '(SUM(vig_oct+vig_nov+vig_dic) - SUM(eje_oct+eje_nov+eje_dic)) as por';
                break;
        }
        $this->conectar();
        $this->setSql('
            SELECT
                CONCAT(pre.gpo_id,pre.ur_id) AS ur,cat_UnidadResponsable.ur_nombre AS desEntidad,
                CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,' . $porcentajePresupuesto . '
                CAST(pre.prg_id AS CHAR) AS prg_id,cat_Programa.prg_nombre AS despgma,
                CAST(pre.spr_id AS CHAR) AS spr_id,cat_Subprograma.spr_nombre AS desspgma,
                CAST(pre.py_id AS CHAR) AS py_id,cat_Proyecto.py_nombre AS desproyecto,
                CAST(pre.oa_id AS CHAR) AS obraaccion,cat_ObraActividad.oa_nombre AS desObra,
                pre.origina,pre.vigente,pre.comprom,pre.devenga,pre.ejercid,
                CASE (pre.vigente+pre.devenga) WHEN 0 THEN 0
                ELSE CAST(ROUND(((pre.devenga*100)/pre.vigente),0) AS CHAR)  END AS sema,
                pre.por,pre.colorTrim,pre.porcentajeTrim
            FROM(
                SELECT
                    gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,tcp_id,
                    ' . $consultatri . ',' . $por . ',' . $trim . '
                FROM presupuesto
                JOIN presupuestoAprobado AS apr USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoVigente AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoComprometido AS com USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoDevengado AS dev USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoEjercido AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                GROUP BY gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id
            ) AS pre
            LEFT JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            LEFT JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
            LEFT JOIN cat_Programa USING (gpo_id,ur_id,prg_id)
            LEFT JOIN cat_Subprograma USING (gpo_id,ur_id,prg_id,spr_id)
            LEFT JOIN cat_Proyecto USING (gpo_id,ur_id,prg_id,spr_id,py_id)
            LEFT JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            WHERE tcp_id<>"P" and vigente >0
                ' . $filtro . '
            ORDER BY pre.gpo_id,pre.ur_id,pre.ue_id,pre.prg_id,pre.spr_id,pre.oa_id
        ');
        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ProgramaSubprogramaActividad';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }

    private function DetalleAdecuacionActividad()
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

        $comboTipoRec = new stdClass();
        $comboTipoRec->id = 'comboTipoRec';
        $comboTipoRec->opciones = [
            'N' => 'TODOS',
            'A' => $this->noEtiquet,
            'B' => 'ETIQUETADO',
        ];
        $comboTipoRec->label = 'Tipo de Recurso';
        $comboTipoRec->style = 'display:inline !important';

        $ur = new stdClass();
        $ur->id = 'ur';
        $ur->label = 'UR';
        $ur->maxlength = 3;
        $ur->formato = 'entero';
        $ur->value = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ur_clave : '';

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

        $proyecto = new stdClass();
        $proyecto->id = 'proyecto';
        $proyecto->label = 'Proyecto';
        $proyecto->maxlength = 3;
        $proyecto->formato = 'entero';
        $proyecto->value = (isset($this->prm->py_id) && $this->prm->py_id != '') ? $this->prm->py_id : '';

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';
        $actividad->value = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? $this->prm->oa_id : '';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscar3';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = 'btn btn-outline-secondary ';
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar3 = new stdClass();
        $exportar3->id = 'exportar3';
        $exportar3->type = 'submit';
        $exportar3->icon = $this->iconD;
        $exportar3->title = $this->tituloEx;
        $exportar3->value = $this->tituloE;
        $exportar3->class = 'btn-xs btn-outline-info me-1';

        $imprimir3 = new stdClass();
        $imprimir3->id = 'imprimir3';
        $imprimir3->type = 'submit';
        $imprimir3->icon = $this->iconoPdf;
        $imprimir3->title = $this->tituloPdf;
        $imprimir3->value = $this->tituloP;
        $imprimir3->class = 'btn btn-xs btn-outline-secondary';

        $div = '
            <div class="d-flex justify-content-around mb-4">
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->templateCombo($comboTrimestre) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboTipoRec) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($ur) . '
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
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar3) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($imprimir3) . '
                </fieldset>
            </div>
        ';

        $tabla = new stdClass();
        $tabla->titulo = 'REPORTE DE ADECUACIONES A NIVEL ACTIVIDAD';
        $tabla->id = 'tbl-AdecuacionActividad';
        $tabla->tools = $div;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';
        $tabla->comunica->trimestre = '{$("#comboTrimestre").val()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';
        $tabla->comunica->py_id = '{$("#proyecto").val()}';
        $tabla->comunica->oa_id = '{$("#actividad").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE';
        $th->width = '5px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD RESPONSABLE';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROGRAMA';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'SUBPROGRAMA';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROYECTO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ACTIVIDAD';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'TIPO ADECUACIÓN';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'No. FOLIO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'IMPORTE AMPLIACIÓN';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'IMPORTE REDUCCIÓN';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META PROGRAMADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META ACTUALIZADA';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ur_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'prg_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'spr_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'py_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'oa_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'tmd_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'mod_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ampliacion';
        $td->formato = 'moneda';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'reduccion';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mact_original';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'mact_modificado';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="max-width: 1500px!important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">3. ADECUACIONES (ACTIVIDAD)</h4>
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
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#btnBuscar3").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"llenaDetalleAdecuacionActividad",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        ter_id:$("#comboTipoRec").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        oa_id:$("#actividad").val(),
                        //toa_id:$("#comboTipoAct").val()
                    });
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#imprimir3").click(function(){
                //console.log("toa_id :" + $("#comboTipoAct").val());
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        ter_id:$("#comboTipoRec").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        oa_id:$("#actividad").val(),
                        //toa_id:$("#comboTipoAct").val(),
                        aplicacion:"PDF",
                        //Se actualizo, antes era el 50 ahora el 108
                        rep_id:108
                    });
                    $("#reportes").submit();
                }else{
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#exportar3").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        ter_id:$("#comboTipoRec").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        oa_id:$("#actividad").val(),
                        //toa_id:$("#comboTipoAct").val(),
                        aplicacion:"EXCEL",
                        //Se actualizo, antes era el 50 ahora el 108
                        rep_id:108
                    });
                    $("#reportes").submit();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });
        ');
    }

    private function llenaDetalleAdecuacionActividad()
    {
        $filtro = '';
        $filtro2 = '';
        $ur_clave = '';
        $prg_id = '';
        $spr_id = '';
        $py_id = '';
        $oa_id = '';
        $ter_id = '';
        $trimestre = '';
        $trimestreMes = '';
        $AND = '';

        if (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') {
            $ur_clave .= ' AND CONCAT(mod_gpo_id,mod_ur_id)="' . $this->prm->ur_clave . '"';
        }

        if (isset($this->prm->ter_id) && $this->prm->ter_id != '') {
            if ($this->prm->ter_id != 'N') {
                $ter_id .= ' AND ter_id = "' . $this->prm->ter_id . '"';
            }
        }

        if (isset($this->prm->prg_id) && $this->prm->prg_id != '') {
            $prg_id .= ' AND ep.prg_id="' . $this->prm->prg_id . '"';
        }

        if (isset($this->prm->spr_id) && $this->prm->spr_id != '') {
            $spr_id .= ' AND ep.spr_id="' . $this->prm->spr_id . '"';
        }

        if (isset($this->prm->py_id) && $this->prm->py_id != '') {
            $py_id .= ' AND ep.py_id="' . $this->prm->py_id . '"';
        }

        if (isset($this->prm->oa_id) && $this->prm->oa_id != '') {
            $oa_id .= ' AND ep.oa_id="' . $this->prm->oa_id . '"';
        }

        $trimestre = (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1);

        switch ($trimestre) {
            case 1:
                $trimestreMes .= ' AND MONTH(mod_fechaAutorizacion) BETWEEN 01 AND 03';
                break;
            case 2:
                $trimestreMes .= ' AND MONTH(mod_fechaAutorizacion) BETWEEN 04 AND 06';
                break;
            case 3:
                $trimestreMes .= ' AND MONTH(mod_fechaAutorizacion) BETWEEN 07 AND 09';
                break;
            case 4:
                $trimestreMes .= ' AND MONTH(mod_fechaAutorizacion) BETWEEN 10 AND 12';
                break;
            default:
                $trimestreMes .= ' AND MONTH(mod_fechaAutorizacion) BETWEEN 01 AND 03';
                break;
        }
        $this->conectar();
        $this->setSql('
            SELECT det.omo_id,det.gpo_id,det.ur_id,det.mod_id,
            CONCAT(CAST(det.gpo_id AS CHAR),CAST(det.ur_id AS CHAR)) AS ur,ur_nombre,det.epg_id,
            CAST(ep.prg_id AS CHAR) AS prg_id,prg_nombre,CAST(ep.spr_id AS CHAR) AS spr_id,spr_nombre,CAST(ep.py_id AS CHAR) AS py_id,py_nombre,mod_concepto,
            CAST(ep.oa_id AS CHAR) AS oa_id,oa_nombre,tmd_id,tmd_nombre,ampliacion,reduccion,
            ma.mact_original,ma.mact_modificado
            FROM (
                SELECT omo_id,mod_gpo_id AS gpo_id,mod_ur_id AS ur_id,mod_id,epg_id,enc_oop,mod_concepto,
                SUM(CASE mov_id WHEN "A" THEN dmo_importe ELSE 0 END) AS Ampliacion,
                SUM(CASE WHEN mov_id="R" THEN dmo_importe ELSE 0 END) AS reduccion
                FROM scp_DetalleModificacion
                LEFT JOIN scp_MaestroModificacion AS m USING (omo_id,mod_gpo_id,mod_ur_id,mod_id)
                LEFT JOIN enc_ClaveFinanciamiento AS ec ON m.ecf_id=ec.ecf_id
                LEFT JOIN (SELECT * FROM ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' WHERE sor_ejercicio=' . APP_EJERCICIO . ') AS cso USING (cff_id,fte_id,sft_id,ori_id,sor_id)
                WHERE mov_id IN ("A","R")  AND tcp_id<>"P"  AND  m.emo_id IN ("14","15","16")
                ' . $ur_clave . '
                ' . $ter_id . '
                ' . $trimestreMes . '
                GROUP BY omo_id,mod_gpo_id,mod_ur_id,mod_id,epg_id,enc_oop
                ORDER BY omo_id,mod_gpo_id,mod_ur_id,mod_id
            ) AS det
            LEFT JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
            LEFT JOIN enc_OrigenOperacion AS eo USING (enc_oop)
            LEFT JOIN cat_TipoModificacion AS tm USING (tmd_id)
            LEFT JOIN enc_Programatico AS ep USING (epg_id)
            LEFT JOIN cat_Programa AS prg ON ep.gpo_id=prg.gpo_id AND ep.ur_id=prg.ur_id AND ep.prg_id=prg.prg_id
            LEFT JOIN cat_Subprograma AS spr ON ep.gpo_id=spr.gpo_id AND ep.ur_id=spr.ur_id AND ep.prg_id=spr.prg_id AND ep.spr_id=spr.spr_id
            LEFT JOIN cat_Proyecto AS py ON ep.gpo_id=py.gpo_id AND ep.ur_id=py.ur_id AND ep.prg_id=py.prg_id AND ep.spr_id=py.spr_id AND ep.py_id=py.py_id
            LEFT JOIN cat_ObraActividad AS ob ON ep.gpo_id=ob.gpo_id AND ep.ur_id=ob.ur_id AND ep.prg_id=ob.prg_id AND ep.spr_id=ob.spr_id AND ep.py_id=ob.py_id AND ep.oa_id=ob.oa_id
            LEFT JOIN cat_MetaActividadTrimestre AS ma ON det.epg_id=ma.epg_id AND ma.tri_id= ' . $trimestre . '  -- primer trimestre
            WHERE det.gpo_id is not null
            and ob.toa_id = 1
            ' . $prg_id . '
            ' . $spr_id . '
            ' . $oa_id . '
            ' . $py_id . '
        ');
        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-AdecuacionActividad';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }

    private function DetalleCalendarioModDev()
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

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';
        $actividad->value = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? $this->prm->oa_id : '';

        $partidaGen = new stdClass();
        $partidaGen->id = 'partidaGen';
        $partidaGen->label = 'Capítulo';
        $partidaGen->maxlength = 1;
        $partidaGen->formato = 'entero';
        $partidaGen->value = (isset($this->prm->pg_id) && $this->prm->pg_id != '') ? $this->prm->pg_id : '';

        $sql = 'SELECT toa_id, toa_nombre FROM cat_TipoObraActividad;';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();
        $tipos_act = ['' => $this->opciones];
        while ($row = $this->extraer()) {
            $tipos_act[$row['toa_id']] = $row['toa_nombre'];
        }

        $comboTipoAct = new stdClass();
        $comboTipoAct->id = 'comboTipoAct';
        $comboTipoAct->opciones = $tipos_act;
        $comboTipoAct->label = 'Tipo Actividad';
        $comboTipoAct->selecciona = (isset($this->prm->toa_id) && $this->prm->toa_id != '') ? $this->prm->toa_id : 1;

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscar4';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar4 = new stdClass();
        $exportar4->id = 'exportar4';
        $exportar4->type = 'submit';
        $exportar4->icon = $this->iconD;
        $exportar4->title = $this->tituloEx;
        $exportar4->value = $this->tituloE;
        $exportar4->class = $this->claseIn;

        $imprimir4 = new stdClass();
        $imprimir4->id = 'imprimir4';
        $imprimir4->type = 'submit';
        $imprimir4->icon = $this->iconoPdf;
        $imprimir4->title = $this->tituloPdf;
        $imprimir4->value = $this->tituloP;
        $imprimir4->class = $this->claseSecXs;

        $div = '
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->templateCombo($comboTrimestre) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($ur) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($programa) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($subprograma) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($actividad) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($partidaGen) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboTipoAct) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar4) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($imprimir4) . '
                </fieldset>
            </div>
        ';

        $tabla = new stdClass();
        $tabla->titulo = 'CALENDARIO MODIFICADO VS EJERCIDO';
        $tabla->id = 'tbl-CalendarioModDev';
        $tabla->tools = $div;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';
        $tabla->comunica->trimestre = '{$("#comboTrimestre").val()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';
        $tabla->comunica->oa_id = '{$("#actividad").val()}';
        $tabla->comunica->pg_id = '{$("#partidaGen").val()}';
        $tabla->comunica->pe_id = '{$("#partidaEsp").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE';
        $th->width = '5px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROGRAMA';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'SUBPROGRAMA';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ACTIVIDAD';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CAPÍTULO';
        array_push($tr, $th);

        switch ($this->prm->trimestre) {
            case 1:
                $NombreMes = 'ENE|FEB|MAR';
                $NombreCampo = 'ene|feb|mar';
                break;
            case 2:
                $NombreMes = 'ABR|MAY|JUN';
                $NombreCampo = 'abr|may|jun';
                break;
            case 3:
                $NombreMes = 'JUL|AGO|SEP';
                $NombreCampo = 'jul|ago|sep';
                break;
            case 4:
                $NombreMes = 'OCT|NOV|DIC';
                $NombreCampo = 'oct|nov|dic';
                break;
            default:
                $NombreMes = 'ENE|FEB|MAR';
                $NombreCampo = 'ene|feb|mar';
                break;
        }

        $th = new stdClass();
        $th->texto = 'MOD ' . substr($NombreMes, 0, -8) . '';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'MOD ' . substr($NombreMes, 4, -4) . '';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'MOD ' . substr($NombreMes, 8, 3) . '';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'EJE ' . substr($NombreMes, 0, -8) . '';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'EJE ' . substr($NombreMes, 4, -4) . '';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'EJE ' . substr($NombreMes, 8, 3) . '';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Programa';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'Subprograma';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'obraaccion';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'desObra';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'capitulo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'vig_' . substr($NombreCampo, 0, -8) . '';
        $td->formato = 'moneda';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'vig_' . substr($NombreCampo, 4, -4) . '';
        $td->formato = 'moneda';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'vig_' . substr($NombreCampo, 8, 3) . '';
        $td->formato = 'moneda';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'eje_' . substr($NombreCampo, 0, -8) . '';
        $td->formato = 'moneda';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'eje_' . substr($NombreCampo, 4, -4) . '';
        $td->formato = 'moneda';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'eje_' . substr($NombreCampo, 8, 3) . '';
        $td->formato = 'moneda';
        array_push($trbody, $td);
        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style=max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">4. CALENDARIO MODIFICADO VS EJERCIDO</h4>
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
            </div>');

        $this->recargaCombo($comboTrimestre);

        $this->llenaDetalleCalendarioModDev();

        $this->mixScript('
            $("#myModal").modal("show");
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#btnBuscar4").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"DetalleCalendarioModDev",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        pg_id:$("#partidaGen").val(),
                        toa_id:$("#comboTipoAct").val()
                    });
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#imprimir4").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        pg_id:$("#partidaGen").val(),
                        toa_id:$("#comboTipoAct").val(),
                        aplicacion:"PDF",
                        rep_id:51
                    });
                    $("#reportes").submit();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#exportar4").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        pg_id:$("#partidaGen").val(),
                        toa_id:$("#comboTipoAct").val(),
                        aplicacion:"EXCEL",
                        rep_id:51
                    });
                    $("#reportes").submit();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });
        ');
    }

    private function llenaDetalleCalendarioModDev()
    {
        $filtro = '';
        $AND = '';
        $trimestre = ((($this->prm->trimestre) && $this->prm->trimestre != '') ? $this->prm->trimestre : 1);
        switch ($trimestre) {
            case '1':
                $meses = 'vig_ene,vig_feb,vig_mar,eje_ene,eje_feb,eje_mar,1 AS trimestre';
                $modvig = ' SUM(vig_ene) AS vig_ene,SUM(vig_feb) AS vig_feb,SUM(vig_mar) AS vig_mar,
                            SUM(eje_ene) AS eje_ene,SUM(eje_feb) AS eje_feb,SUM(eje_mar) AS eje_mar ';
                break;
            case '2':
                $meses = 'vig_abr,vig_may,vig_jun,eje_abr,eje_may,eje_jun,2 AS trimestre';
                $modvig = ' SUM(vig_abr) AS vig_abr,SUM(vig_may) AS vig_may,SUM(vig_jun) AS vig_jun,
                            SUM(eje_abr) AS eje_abr,SUM(eje_may) AS eje_may,SUM(eje_jun) AS eje_jun ';
                break;
            case '3':
                $meses = 'vig_jul,vig_ago,vig_sep,eje_jul,eje_ago,eje_sep,3 AS trimestre';
                $modvig = ' SUM(vig_jul) AS vig_jul,SUM(vig_ago) AS vig_ago,SUM(vig_sep) AS vig_sep,
                            SUM(eje_jul) AS eje_jul,SUM(eje_ago) AS eje_ago,SUM(eje_sep) AS eje_sep ';
                break;
            case '4':
                $meses = 'vig_oct,vig_nov,vig_dic,eje_oct,eje_nov,eje_dic,4 AS trimestre';
                $modvig = ' SUM(vig_oct) AS vig_oct,SUM(vig_nov) AS vig_nov,SUM(vig_dic) AS vig_dic,
                            SUM(eje_oct) AS eje_oct,SUM(eje_nov) AS eje_nov,SUM(eje_dic) AS eje_dic ';
                break;
        }

        if (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') {
            $filtro .= ' AND  CONCAT(gpo_id,ur_id)="' . $this->prm->ur_clave . '"';
        }

        if (isset($this->prm->prg_id) && $this->prm->prg_id != '') {
            $filtro .= ' AND prg_id="' . $this->prm->prg_id . '"';
        }

        if (isset($this->prm->spr_id) && $this->prm->spr_id != '') {
            $filtro .= ' AND spr_id="' . $this->prm->spr_id . '"';
        }

        if (isset($this->prm->oa_id) && $this->prm->oa_id != '') {
            $filtro .= ' AND oa_id="' . $this->prm->oa_id . '"';
        }

        if (isset($this->prm->pg_id) && $this->prm->pg_id != '') {
            $filtro .= ' AND capitulo="' . $this->prm->pg_id . '"';
        }

        if (isset($this->prm->toa_id) && $this->prm->toa_id != '') {
            $filtro .= ' AND toa_id="' . $this->prm->toa_id . '"';
        }

        $calTrim1i = $calTrim1x = $calTrim1y = '1';

        $this->conectar();
        $this->setSql('
            SELECT
                CONCAT(CAST(pre.gpo_id AS CHAR),CAST(pre.ur_id AS CHAR)) AS ur,cat_UnidadResponsable.ur_nombre AS desEntidad,
                CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
                CAST(pre.prg_id AS CHAR) AS Programa, cat_Programa.prg_nombre AS desPrograma,
                CAST(pre.spr_id AS CHAR) AS Subprograma,cat_Subprograma.spr_nombre AS desSubprograma,
                CAST(pre.py_id AS CHAR) AS proyecto,cat_Proyecto.py_nombre AS desProyecto,
                CAST(pre.oa_id AS CHAR) AS obraaccion,cat_ObraActividad.oa_nombre AS desObra,
                pre.capitulo,cap.cap_nombre,' . $meses . '
            FROM(
                SELECT
                    gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,SUBSTRING(pg_id,1,1) AS capitulo,tcp_id,
                    ' . $modvig . '
                FROM presupuesto
                JOIN presupuestoVigente AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoEjercido AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                GROUP BY gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,SUBSTRING(pg_id,1,1)
            ) AS pre
            LEFT JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            LEFT JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
            LEFT JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
            LEFT JOIN cat_Subprograma USING(gpo_id,ur_id,prg_id,spr_id)
            LEFT JOIN cat_Proyecto USING(gpo_id,ur_id,prg_id,spr_id,py_id)
            LEFT JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            LEFT JOIN cat_Capitulo AS cap ON pre.capitulo=cap.cap_id
            WHERE tcp_id<>"P"
            ' . $filtro . '
            ORDER BY pre.gpo_id,ur_id,pre.ue_id,pre.prg_id,pre.spr_id,pre.py_id,pre.oa_id,pre.capitulo');
        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-CalendarioModDev';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }

    // Este metodo hace la comunicacion con el .NET
    private function imprime()
    {
        $salt = '$BF8AsdL3t-cw13z20'; //LLAVE

        //$salt .= $this->prm->rep_id; // numero de reporte rep_id
        $nose = MD5($this->clasAdm_gpo . $salt . $this->clasAdm_ur);
        $clave = strtoupper($nose);
        $this->sesion($clave);
        $this->mixScript(' console.log(' . $this->prm->rep_id . ')');
        $target = '_blank';
        $loadviewer = '';
        switch ($this->prm->aplicacion) {
            case 'PDF':
                $target = 'det_fs' . $this->prm->rep_id;
                $loadviewer = 'load_viewer(' . $this->prm->rep_id . ',"","frame")';
                break;
        }

        $todas = 'SI';
        $numReporte = '';
        $post = '';
        if (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') {
            $todas = 'NO';
        }
        switch ($this->prm->rep_id) {
            case 49:
            case 50:
                $post = '.append( $("<input/>").attr("name","oa_id" ).attr("value","' . $this->prm->oa_id . '").attr("type","hidden") )';
                break;
            case 51:
                $post = '
                    .append( $("<input/>").attr("name","oa_id" ).attr("value","' . $this->prm->oa_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","capitulo" ).attr("value","' . $this->prm->pg_id . '").attr("type","hidden") )';
                break;
            default:
                break;
        }

        switch ($this->server2) {
            case '172.17.90.17':
                $action = 'https://reportessinpres.finanzasoaxaca.gob.mx/Reportes2121/';
                break;
            case '172.17.90.18':
                $action = 'https://qreportessinpres.finanzasoaxaca.gob.mx/Reportes2121v2/';
                break;
            default:
                $action = 'http://localhost:5898/crystal_reports/Reportes2121_Prueba/';
        }
        //Se pone cambia, ahora se triangula
        $action = 'scp/reportes/get_pdf.php';
        $script = '';

        // Castro Jordy  03/08/2018 se agrega la variable $servidorReporte
        $servidorReporte = 'PRODUCCION';

        switch ($this->prm->rep_id) {
            case 48:
            case 49:
            case 50:
            case 51:
                $script = '
                    var n = $("<form/>")
                    .attr("id","repodi")
                    .attr("action","' . $action . '")
                    .attr("method","post")
                    .attr("target","' . $target . '")
                    .append( $("<input/>").attr("name","gpo_id").attr("value","' . $this->clasAdm_gpo . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_id" ).attr("value","' . $this->clasAdm_ur . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","SSL875JJNF9" ).attr("value","' . $clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","numrep" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","rep_id" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","trimestre" ).attr("value","' . (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1) . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","prg_id" ).attr("value","' . $this->prm->prg_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","spr_id" ).attr("value","' . $this->prm->spr_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","toa_id" ).attr("value","' . $this->prm->toa_id . '").attr("type","hidden") )
                    ' . $post . '
                    .append( $("<input/>").attr("name","todas" ).attr("value","' . $todas . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_clave" ).attr("value","' . $this->prm->ur_clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","servidor" ).attr("value","' . $servidorReporte . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","aplicacion" ).attr("value","' . $this->prm->aplicacion . '").attr("type","hidden") )
                    ;

                    n.appendTo("body");
                    n.submit();
                    delete n;
                    $("#repodi").remove();
                ';
                break;
            case 109:
                $script = '
                    var n = $("<form/>")
                    .attr("id","repodi")
                    .attr("action","' . $action . '")
                    .attr("method","post")
                    .attr("target","' . $target . '")
                    .append( $("<input/>").attr("name","gpo_id").attr("value","' . $this->clasAdm_gpo . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_id" ).attr("value","' . $this->clasAdm_ur . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","SSL875JJNF9" ).attr("value","' . $clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","numrep" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","rep_id" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","trimestresema" ).attr("value","' . (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1) . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","prg_id" ).attr("value","' . $this->prm->prg_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","spr_id" ).attr("value","' . $this->prm->spr_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_clave" ).attr("value","' . $this->prm->ur_clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","servidor" ).attr("value","' . $servidorReporte . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","aplicacion" ).attr("value","' . $this->prm->aplicacion . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","oa_id" ).attr("value","' . $this->prm->oa_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","toa_id" ).attr("value","' . $this->prm->toa_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","toa_id2" ).attr("value","' . $this->prm->toa_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ue_id" ).attr("value","' . $this->prm->ue_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","porcentaje" ).attr("value","' . $this->prm->porcentaje . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","porcentaje2" ).attr("value","' . $this->prm->porcentaje2 . '").attr("type","hidden") )
                    ;

                    n.appendTo("body");
                    n.submit();
                    delete n;
                    $("#repodi").remove();
                ';
                break;
            case 105:
            case 106: // Reportes Seguimiento Gastos OP
                $script = '
                    var n = $("<form/>")
                    .attr("id","repodi")
                    .attr("action","' . $action . '")
                    .attr("method","post")
                    .attr("target","_blank")
                    .append( $("<input/>").attr("name","gpo_id").attr("value","' . $this->clasAdm_gpo . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_id" ).attr("value","' . $this->clasAdm_ur . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","SSL875JJNF9" ).attr("value","' . $clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","numrep" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","rep_id" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","trimestre" ).attr("value","' . (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1) . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_clave" ).attr("value","' . $this->prm->ur_clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ue_id" ).attr("value","' . $this->prm->ue_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","prg_id" ).attr("value","' . $this->prm->prg_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","spr_id" ).attr("value","' . $this->prm->spr_id . '").attr("type","hidden") )
                    ' . $post . '
                    .append( $("<input/>").attr("name","cfi_clave" ).attr("value","' . $this->prm->cfi_clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","oa_id" ).attr("value","' . $this->prm->oa_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","todas" ).attr("value","' . $todas . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","servidor" ).attr("value","' . $servidorReporte . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","aplicacion" ).attr("value","' . $this->prm->aplicacion . '").attr("type","hidden") )
                    ;

                    n.appendTo("body");
                    n.submit();
                    delete n;
                    $("#repodi").remove();
                ';
                break;
            case 107: // Reportes Seguimiento Gastos OP
                $script = '
                    var n = $("<form/>")
                    .attr("id","repodi")
                    .attr("action","' . $action . '")
                    .attr("method","post")
                    .attr("target","_blank")
                    .append( $("<input/>").attr("name","gpo_id").attr("value","' . $this->clasAdm_gpo . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_id" ).attr("value","' . $this->clasAdm_ur . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","SSL875JJNF9" ).attr("value","' . $clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","numrep" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","rep_id" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_clave" ).attr("value","' . $this->prm->ur_clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ue_id" ).attr("value","' . $this->prm->ue_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","prg_id" ).attr("value","' . $this->prm->prg_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","spr_id" ).attr("value","' . $this->prm->spr_id . '").attr("type","hidden") )
                    ' . $post . '
                    .append( $("<input/>").attr("name","numTrimestre" ).attr("value","' . (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1) . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","cfi_clave" ).attr("value","' . $this->prm->cfi_clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","oa_id" ).attr("value","' . $this->prm->oa_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","todas" ).attr("value","' . $todas . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","servidor" ).attr("value","' . $servidorReporte . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","aplicacion" ).attr("value","' . $this->prm->aplicacion . '").attr("type","hidden") )
                    ;

                    n.appendTo("body");
                    n.submit();
                    delete n;
                    $("#repodi").remove();
                ';
                break;
            case 108:
                // 09/04/2019 Para resumen de adecuaciones a nivel actividad
                // Antes era para: Reportes Seguimiento Gastos OP
                $script = '
                    var n = $("<form/>")
                    .attr("id","repodi")
                    .attr("action","' . $action . '")
                    .attr("method","post")
                    .attr("target","_blank")
                    .append( $("<input/>").attr("name","gpo_id").attr("value","' . $this->clasAdm_gpo . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_id" ).attr("value","' . $this->clasAdm_ur . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","SSL875JJNF9" ).attr("value","' . $clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","numrep" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","rep_id" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","trimestreMP" ).attr("value","' . (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1) . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ter_id" ).attr("value","' . $this->prm->ter_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_clave" ).attr("value","' . $this->prm->ur_clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ue_id" ).attr("value","' . ((isset($this->prm->ue_id) && $this->prm->ue_id != '') ? $this->prm->ue_id : '') . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","prg_id" ).attr("value","' . $this->prm->prg_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","py_id" ).attr("value","' . $this->prm->py_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","spr_id" ).attr("value","' . $this->prm->spr_id . '").attr("type","hidden") )
                    ' . $post . '
                    .append( $("<input/>").attr("name","cfi_clave" ).attr("value","' . ((isset($this->prm->cfi_clave) && $this->prm->cfi_clave != '') ? $this->prm->cfi_clave : '') . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","oa_id" ).attr("value","' . $this->prm->oa_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","todas" ).attr("value","' . $todas . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","servidor" ).attr("value","' . $servidorReporte . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","aplicacion" ).attr("value","' . $this->prm->aplicacion . '").attr("type","hidden") )
                    ;

                    n.appendTo("body");
                    n.submit();
                    delete n;
                    $("#repodi").remove();
                ';
                break;
            case 111:
            case 112:
                $script = '
                    var n = $("<form/>")
                    .attr("id","repodi")
                    .attr("action","' . $action . '")
                    .attr("method","post")
                    .attr("target","' . $target . '")
                    .append( $("<input/>").attr("name","gpo_id").attr("value","' . $this->clasAdm_gpo . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_id" ).attr("value","' . $this->clasAdm_ur . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","SSL875JJNF9" ).attr("value","' . $clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","numrep" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","rep_id" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","mesSegGtos" ).attr("value","' . (isset($this->prm->mes) && $this->prm->mes != '' ? $this->prm->mes : 1) . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","cfi_clave" ).attr("value","' . $this->prm->cfi_clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ue_id" ).attr("value","' . $this->prm->ue_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","prg_id" ).attr("value","' . $this->prm->prg_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","spr_id" ).attr("value","' . $this->prm->spr_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","oa_id" ).attr("value","' . $this->prm->oa_id . '").attr("type","hidden") )
                    ' . $post . '
                    .append( $("<input/>").attr("name","todas" ).attr("value","' . $todas . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_clave" ).attr("value","' . $this->prm->ur_clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","servidor" ).attr("value","' . $servidorReporte . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","aplicacion" ).attr("value","' . $this->prm->aplicacion . '").attr("type","hidden") )
                    ;
                    n.appendTo("body");
                    n.submit();
                    delete n;
                    $("#repodi").remove();
                ';

                break;
            case 113:
                $script = '
                    var n = $("<form/>")
                    .attr("id","repodi")
                    .attr("action","' . $action . '")
                    .attr("method","post")
                    .attr("target","' . $target . '")
                    .append( $("<input/>").attr("name","gpo_id").attr("value","' . $this->clasAdm_gpo . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_id" ).attr("value","' . $this->clasAdm_ur . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","SSL875JJNF9" ).attr("value","' . $clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","numrep" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","rep_id" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","mescal" ).attr("value","' . (isset($this->prm->mes) && $this->prm->mes != '' ? $this->prm->mes : 1) . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","cfi_clave" ).attr("value","' . $this->prm->cfi_clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ue_id" ).attr("value","' . $this->prm->ue_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","prg_id" ).attr("value","' . $this->prm->prg_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","spr_id" ).attr("value","' . $this->prm->spr_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","oa_id" ).attr("value","' . $this->prm->oa_id . '").attr("type","hidden") )
                    ' . $post . '
                    .append( $("<input/>").attr("name","todas" ).attr("value","' . $todas . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_clave" ).attr("value","' . $this->prm->ur_clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","servidor" ).attr("value","' . $servidorReporte . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","aplicacion" ).attr("value","' . $this->prm->aplicacion . '").attr("type","hidden") )
                    ;
                    n.appendTo("body");
                    n.submit();
                    delete n;
                    $("#repodi").remove();
                ';
                break;
            case 114:
                //Reporte de transparencia
                $script = '
                    var n = $("<form/>")
                    .attr("id","repodi")
                    .attr("action","' . $action . '")
                    .attr("method","post")
                    .attr("target","' . $target . '")
                    .append( $("<input/>").attr("name","gpo_id").attr("value","' . $this->clasAdm_gpo . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_id" ).attr("value","' . $this->clasAdm_ur . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","SSL875JJNF9" ).attr("value","' . $clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","numrep" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","rep_id" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","trimestresema" ).attr("value","' . (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1) . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","trimestre" ).attr("value","' . (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1) . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","preTrimestre" ).attr("value","' . (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1) . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ter_id" ).attr("value","' . $this->prm->ter_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","prg_id" ).attr("value","' . $this->prm->prg_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","spr_id" ).attr("value","' . $this->prm->spr_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","py_id" ).attr("value","' . $this->prm->py_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_clave" ).attr("value","' . $this->prm->ur_clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","servidor" ).attr("value","' . $servidorReporte . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","aplicacion" ).attr("value","' . $this->prm->aplicacion . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","oa_id" ).attr("value","' . $this->prm->oa_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ue_id" ).attr("value","' . $this->prm->ue_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","porcentaje" ).attr("value","' . $this->prm->porcentaje . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","porcentaje2" ).attr("value","' . $this->prm->porcentaje2 . '").attr("type","hidden") )
                    ;
                    n.appendTo("body");
                    n.submit();
                    delete n;
                    $("#repodi").remove();
                ';
                break;
            case 116:
                //Reporte de transparencia
                $script = '
                    var n = $("<form/>")
                    .attr("id","repodi")
                    .attr("action","' . $action . '")
                    .attr("method","post")
                    .attr("target","' . $target . '")
                    .append( $("<input/>").attr("name","gpo_id").attr("value","' . $this->clasAdm_gpo . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_id" ).attr("value","' . $this->clasAdm_ur . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","SSL875JJNF9" ).attr("value","' . $clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","numrep" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","rep_id" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","numeroTrimestre" ).attr("value","' . (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1) . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","trimestre" ).attr("value","' . (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1) . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","preTrimestre" ).attr("value","' . (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1) . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ter_id" ).attr("value","' . $this->prm->ter_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","prg_id" ).attr("value","' . $this->prm->prg_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","spr_id" ).attr("value","' . $this->prm->spr_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","py_id" ).attr("value","' . $this->prm->py_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_clave" ).attr("value","' . $this->prm->ur_clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","servidor" ).attr("value","' . $servidorReporte . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","aplicacion" ).attr("value","' . $this->prm->aplicacion . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","oa_id" ).attr("value","' . $this->prm->oa_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","toa_id" ).attr("value","' . $this->prm->toa_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ue_id" ).attr("value","' . $this->prm->ue_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","porcentaje" ).attr("value","' . $this->prm->porcentaje . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","porcentaje2" ).attr("value","' . $this->prm->porcentaje2 . '").attr("type","hidden") )
                    ;
                    n.appendTo("body");
                    n.submit();
                    delete n;
                    $("#repodi").remove();
                ';
                break;
            case 115:
                //Reportes de transparencia
                $script = '
                    var n = $("<form/>")
                    .attr("id","repodi")
                    .attr("action","' . $action . '")
                    .attr("method","post")
                    .attr("target","' . $target . '")
                    .append( $("<input/>").attr("name","gpo_id").attr("value","' . $this->clasAdm_gpo . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_id" ).attr("value","' . $this->clasAdm_ur . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","SSL875JJNF9" ).attr("value","' . $clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","numrep" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","rep_id" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","trimestresema" ).attr("value","' . (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1) . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","trimestreHistorial" ).attr("value","' . (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1) . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ter_id" ).attr("value","' . $this->prm->ter_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","prg_id" ).attr("value","' . $this->prm->prg_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","spr_id" ).attr("value","' . $this->prm->spr_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","py_id" ).attr("value","' . $this->prm->py_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_clave" ).attr("value","' . $this->prm->ur_clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","servidor" ).attr("value","' . $servidorReporte . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","aplicacion" ).attr("value","' . $this->prm->aplicacion . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","oa_id" ).attr("value","' . $this->prm->oa_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ue_id" ).attr("value","' . $this->prm->ue_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","porcentaje" ).attr("value","' . $this->prm->porcentaje . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","porcentaje2" ).attr("value","' . $this->prm->porcentaje2 . '").attr("type","hidden") )
                    ;
                    n.appendTo("body");
                    n.submit();
                    delete n;
                    $("#repodi").remove();
                ';
                break;
            case 121: // Nuevo RPT ya que antes se generaba mediante un php
                $script = '
                    var n = $("<form/>")
                    .attr("id","repodi")
                    .attr("action","' . $action . '")
                    .attr("method","post")
                    .attr("target","' . $target . '")
                    .append( $("<input/>").attr("name","gpo_id").attr("value","' . $this->clasAdm_gpo . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_id" ).attr("value","' . $this->clasAdm_ur . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","SSL875JJNF9" ).attr("value","' . $clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","numrep" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","rep_id" ).attr("value","' . $this->prm->rep_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","trimestresema" ).attr("value","' . (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1) . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ter_id" ).attr("value","' . $this->prm->ter_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","prg_id" ).attr("value","' . $this->prm->prg_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","spr_id" ).attr("value","' . $this->prm->spr_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","py_id" ).attr("value","' . $this->prm->py_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ur_clave" ).attr("value","' . $this->prm->ur_clave . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","servidor" ).attr("value","' . $servidorReporte . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","aplicacion" ).attr("value","' . $this->prm->aplicacion . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","oa_id" ).attr("value","' . $this->prm->oa_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","toa_id" ).attr("value","' . $this->prm->toa_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","ue_id" ).attr("value","' . $this->prm->ue_id . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","porcentaje" ).attr("value","' . $this->prm->porcentaje . '").attr("type","hidden") )
                    .append( $("<input/>").attr("name","porcentaje2" ).attr("value","' . $this->prm->porcentaje2 . '").attr("type","hidden") )
                    ;
                    n.appendTo("body");
                    n.submit();
                    delete n;
                    $("#repodi").remove();
                ';
                break;
            default:
                break;
        }
        $this->mixScript('
            ' . $script . '
        ');
    }
    //---------------------------------------------------

    public function verPantallaSeg1()
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

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscarS1';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar = new stdClass();
        $exportar->id = 'exportarS1';
        $exportar->value = $this->tituloE;
        $exportar->type = 'submit';
        $exportar->icon = $this->iconD;
        $exportar->title = $this->tituloEx;
        $exportar->class = $this->claseIn;

        $imprimir = new stdClass();
        $imprimir->id = 'imprimirS1';
        $imprimir->value = $this->tituloP;
        $imprimir->type = 'submit';
        $imprimir->icon = $this->iconoPdf;
        $imprimir->title = $this->tituloPdf;
        $imprimir->class = $this->claseSecXs;

        $tabla = new stdClass();
        $tabla->titulo = 'Lista de Obras/Actividades por trimestre';
        $tabla->id = 'tbl-Presupuesto1';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE';
        $th->width = '5px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLAVE FIN';
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

        $th = new stdClass();
        $th->texto = 'DEVENGADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EJERCIDO';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ue_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cfi_clave';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->formato = 'moneda';
        $td->campoTxt = 'origina';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->formato = 'moneda';
        $td->campoTxt = 'vigente';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->formato = 'moneda';
        $td->campoTxt = 'comprom';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->formato = 'moneda';
        $td->campoTxt = 'devenga';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->formato = 'moneda';
        $td->campoTxt = 'ejercid';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">1. UR.-PROG.-SPROG.</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <fieldset class="col-md-2 col-xs-2">
                                    ' . $this->ponCombo($comboTrimestre) . '
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
                                    ' . $this->ponInput($actividad) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponBoton($btnBuscar) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponBoton($exportar) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponBoton($imprimir) . '
                                </fieldset>
                            </div>
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
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#btnBuscarS1").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"llenaTabla1",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        trimestre:$("#comboTrimestre").val(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                    });
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#exportarS1").click(function(){
                if($("#comboTrimestre").val() !=""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        trimestre:$("#comboTrimestre").val(),
                        aplicacion:"EXCEL",
                        rep_id:106
                    });
                    $("#reportes").submit();
                } else {
                    notify("Debe seleccionar el trimestre","error");
                }
            });

            $("#imprimirS1").click(function(){
                if($("#comboTrimestre").val() !=""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        trimestre:$("#comboTrimestre").val(),
                        aplicacion:"PDF",
                        rep_id:106
                    });
                    $("#reportes").submit();
                } else {
                    notify("Debe seleccionar el trimestre","error");
                }
            });
        ');
    }

    private function llenaTabla1()
    {
        $ur = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? ' AND ur_clave=' . $this->prm->ur_clave : '';
        $ue = (isset($this->prm->ue_id) && $this->prm->ue_id != '') ? ' AND ue_id=' . $this->prm->ue_id : '';
        $prg = (isset($this->prm->prg_id) && $this->prm->prg_id != '') ? ' AND prg_id=' . $this->prm->prg_id : '';
        $spr = (isset($this->prm->spr_id) && $this->prm->spr_id != '') ? ' AND spr_id=' . $this->prm->spr_id : '';
        $oa = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? ' AND oa_id=' . $this->prm->oa_id : '';
        $cvefin = (isset($this->prm->cfi_clave) && $this->prm->cfi_clave != '') ? ' AND cf.cfi_clave="' . $this->prm->cfi_clave . '"' : '';
        $trimestre = (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1);
        switch ($trimestre) {
            case 1:
                $consultatri = '
                    SUM(apr_ene+apr_feb+apr_mar) AS origina,
                    SUM(vig_ene+vig_feb+vig_mar) AS vigente,
                    SUM(com_ene+com_feb+com_mar) AS comprom,
                    SUM(dev_ene+dev_feb+dev_mar) AS devenga,
                    SUM(eje_ene+eje_feb+eje_mar) AS ejercid';
                break;
            case 2:
                $consultatri = '
                    SUM(apr_abr+apr_may+apr_jun) AS origina,
                    SUM(vig_abr+vig_may+vig_jun) AS vigente,
                    SUM(com_abr+com_may+com_jun) AS comprom,
                    SUM(dev_abr+dev_may+dev_jun) AS devenga,
                    SUM(eje_abr+eje_may+eje_jun) AS ejercid';
                break;
            case 3:
                $consultatri = '
                    SUM(apr_jul+apr_ago+apr_sep) AS origina,
                    SUM(vig_jul+vig_ago+vig_sep) AS vigente,
                    SUM(com_jul+com_ago+com_sep) AS comprom,
                    SUM(dev_jul+dev_ago+dev_sep) AS devenga,
                    SUM(eje_jul+eje_ago+eje_sep) AS ejercid';
                break;
            case 4:
                $consultatri = '
                    SUM(apr_oct+apr_nov+apr_dic) AS origina,
                    SUM(vig_oct+vig_nov+vig_dic) AS vigente,
                    SUM(com_oct+com_nov+com_dic) AS comprom,
                    SUM(dev_oct+dev_nov+dev_dic) AS devenga,
                    SUM(eje_oct+eje_nov+eje_dic) AS ejercid';
                break;
        }
        $this->conectar();
        $this->setSql('
            SELECT
                CONCAT(pre.gpo_id,pre.ur_id) AS ur,cat_UnidadResponsable.ur_nombre AS desEntidad,
                CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
                CAST(pre.prg_id AS CHAR) AS prg_id,cat_Programa.prg_nombre AS despgma,
                CAST(pre.spr_id AS CHAR) AS spr_id,cat_Subprograma.spr_nombre AS desspgma,
                cf.cfi_clave,cf.cfi_nombre,
                sum(pre.origina) as origina,sum(pre.vigente) as vigente, sum(pre.comprom) as comprom,sum(pre.devenga) as devenga,sum(pre.ejercid) as ejercid
            FROM
            (
                SELECT
                    gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,tcp_id,
                    cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,
                    ' . $consultatri . '
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
            LEFT JOIN cat_ObraActividad USING (gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id)
            LEFT JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' AS cf USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            WHERE toa_id="1"' . $ur . $cvefin . $ue . $prg . $spr . $oa . '
            GROUP BY pre.gpo_id,pre.ur_id,cf.cfi_clave,ue_nombre
            ORDER BY pre.gpo_id,pre.ur_id,cf.cfi_clave,pre.ue_id,pre.prg_id,pre.spr_id
        ');
        $this->consultar();
        $this->desconectar();
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Presupuesto1';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }

    private function verPantallaSeg2()
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

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';
        $actividad->value = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? $this->prm->oa_id : '';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscarS2';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar2 = new stdClass();
        $exportar2->id = 'exportarS2';
        $exportar2->type = 'submit';
        $exportar2->icon = $this->iconD;
        $exportar2->title = $this->tituloEx;
        $exportar2->value = $this->tituloE;
        $exportar2->class = $this->claseIn;

        $imprimir2 = new stdClass();
        $imprimir2->id = 'imprimirS2';
        $imprimir2->type = 'submit';
        $imprimir2->icon = $this->iconoPdf;
        $imprimir2->title = $this->tituloPdf;
        $imprimir2->value = $this->tituloP;
        $imprimir2->class = $this->claseSecXs;

        $div = '
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
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
                    ' . $this->ponInput($actividad) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar2) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($imprimir2) . '
                </fieldset>
            </div>
        ';

        $tabla = new stdClass();
        $tabla->titulo = ' UR.- CVEFIN - UE -PROG.-SPROG.-PY.-OBRA O ACTIVIDAD';
        $tabla->id = 'tbl-ProgramaSubprogramaActividad';
        $tabla->tools = $div;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';
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

        $th = new stdClass();
        $th->texto = 'DEVENGADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EJERCIDO';
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
        $td->campoTxt = 'desObra';
        array_push($trbody, $td);

        $aprobado = '';
        $vigente = '';
        $comprometido = '';
        $devengado = '';
        $ejercido = '';

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

        $td = new stdClass();
        $td->campoTxt = 'devenga';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ejercid';
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
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">2.  UR.-PROG.-SPROG.-PY.-OBRA O ACTIVIDAD</h4>
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
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#btnBuscarS2").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"llenaTabla2",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        trimestre:$("#comboTrimestre").val(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        fecha:$("#fecha").val(),
                        fecha2:$("#fecha2").val()
                    });
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#imprimirS2").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
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
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        aplicacion:"EXCEL",
                        rep_id:105
                    });
                    $("#reportes").submit();
                }else{
                    notify("Seleccione un trimestre","error");
                }
            });
        ');
    }

    private function llenaTabla2()
    {

        $ur = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? ' AND CONCAT(gpo_id,ur_id)=' . $this->prm->ur_clave : '';
        $ue = (isset($this->prm->ue_id) && $this->prm->ue_id != '') ? ' AND pre.ue_id=' . $this->prm->ue_id : '';
        $prg = (isset($this->prm->prg_id) && $this->prm->prg_id != '') ? ' AND prg_id=' . $this->prm->prg_id : '';
        $spr = (isset($this->prm->spr_id) && $this->prm->spr_id != '') ? ' AND spr_id=' . $this->prm->spr_id : '';
        $oa = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? ' AND oa_id=' . $this->prm->oa_id : '';
        $cvefin = (isset($this->prm->cfi_clave) && $this->prm->cfi_clave != '') ? ' AND cf.cfi_clave="' . $this->prm->cfi_clave . '"' : '';

        $trimestre = (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1);
        switch ($trimestre) {
            case 1:
                $consultatri = '
                    SUM(apr_ene+apr_feb+apr_mar) AS origina,
                    SUM(vig_ene+vig_feb+vig_mar) AS vigente,
                    SUM(com_ene+com_feb+com_mar) AS comprom,
                    SUM(dev_ene+dev_feb+dev_mar) AS devenga,
                    SUM(eje_ene+eje_feb+eje_mar) AS ejercid';
                $por = '(SUM(vig_ene+vig_feb+vig_mar) - SUM(eje_ene+eje_feb+eje_mar)) as por';
                break;
            case 2:
                $consultatri = '
                    SUM(apr_abr+apr_may+apr_jun) AS origina,
                    SUM(vig_abr+vig_may+vig_jun) AS vigente,
                    SUM(com_abr+com_may+com_jun) AS comprom,
                    SUM(dev_abr+dev_may+dev_jun) AS devenga,
                    SUM(eje_abr+eje_may+eje_jun) AS ejercid';
                $por = '(SUM(vig_abr+vig_may+vig_jun) - SUM(eje_abr+eje_may+eje_jun)) as por';
                break;
            case 3:
                $consultatri = '
                    SUM(apr_jul+apr_ago+apr_sep) AS origina,
                    SUM(vig_jul+vig_ago+vig_sep) AS vigente,
                    SUM(com_jul+com_ago+com_sep) AS comprom,
                    SUM(dev_jul+dev_ago+dev_sep) AS devenga,
                    SUM(eje_jul+eje_ago+eje_sep) AS ejercid';
                $por = '(SUM(vig_jul+vig_ago+vig_sep) - SUM(eje_jul+eje_ago+eje_sep)) as por';
                break;
            case 4:
                $consultatri = '
                    SUM(apr_oct+apr_nov+apr_dic) AS origina,
                    SUM(vig_oct+vig_nov+vig_dic) AS vigente,
                    SUM(com_oct+com_nov+com_dic) AS comprom,
                    SUM(dev_oct+dev_nov+dev_dic) AS devenga,
                    SUM(eje_oct+eje_nov+eje_dic) AS ejercid';
                $por = '(SUM(vig_oct+vig_nov+vig_dic) - SUM(eje_oct+eje_nov+eje_dic)) as por';
                break;
        }

        $this->conectar();
        /*

         */
        $this->setSql('
            SELECT
                CONCAT(pre.gpo_id,pre.ur_id) AS ur,cat_UnidadResponsable.ur_nombre AS desEntidad,
                CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
                CAST(pre.prg_id AS CHAR) AS prg_id,cat_Programa.prg_nombre AS despgma,
                CAST(pre.spr_id AS CHAR) AS spr_id,cat_Subprograma.spr_nombre AS desspgma,
                CAST(pre.py_id AS CHAR) AS py_id,cat_Proyecto.py_nombre AS desproyecto,
                CAST(pre.oa_id AS CHAR) AS obraaccion,cat_ObraActividad.oa_nombre AS desObra,
                cf.cfi_clave,cf.cfi_nombre,
                sum(pre.origina) as origina,sum(pre.vigente) as vigente,
                sum(pre.comprom) as comprom,sum(pre.devenga) as devenga,
                sum(pre.ejercid) as ejercid,
                CASE (pre.vigente+pre.ejercid) WHEN 0 THEN 0  ELSE CAST(ROUND(((pre.ejercid*100)/pre.vigente),0) AS CHAR)  END AS sema,
                CASE WHEN (ROUND(((pre.ejercid*100)/pre.vigente),0) BETWEEN 0 AND 40) OR ROUND(((pre.ejercid*100)/pre.vigente),0) IS NULL THEN "FF0000"
                    WHEN (ROUND(((pre.ejercid*100)/pre.vigente),0) BETWEEN 41 AND 75) THEN "F3C12A"
                    WHEN (ROUND(((pre.ejercid*100)/pre.vigente),0) BETWEEN 76 AND 100) THEN "41A62A"
                    ELSE "0080FF" END AS color,pre.por
                FROM
                (
                    SELECT
                        gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,tcp_id,
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
                WHERE  toa_id="1" ' . $ur . $cvefin . $ue . $prg . $spr . $oa . '
                GROUP BY pre.gpo_id,pre.ur_id,desObra,cf.cfi_clave
                ORDER BY pre.gpo_id,pre.ur_id,cf.cfi_clave,pre.ue_id,pre.prg_id,pre.spr_id,pre.oa_id;
            ');

        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ProgramaSubprogramaActividad';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }

    }

    private function verPantallaSeg3()
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
        $claveFinanciamiento->value = (isset($this->prm->cfi_clave) && $this->prm->cfi_clave != '') ? $this->prm->cfi_clave : '';

        $ue = new stdClass();
        $ue->id = 'ue';
        $ue->label = 'UE';
        $ue->maxlength = 3;
        $ue->formato = 'entero';
        $ue->value = (isset($this->prm->ue_id) && $this->prm->ue_id != '') ? $this->prm->ue_id : '';

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

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';
        $actividad->value = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? $this->prm->oa_id : '';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscarS3';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar4 = new stdClass();
        $exportar4->id = 'exportarS3';
        $exportar4->type = 'submit';
        $exportar4->icon = $this->iconD;
        $exportar4->title = $this->tituloEx;
        $exportar4->value = $this->tituloE;
        $exportar4->class = $this->claseIn;

        $imprimir4 = new stdClass();
        $imprimir4->id = 'imprimirS3';
        $imprimir4->type = 'submit';
        $imprimir4->icon = $this->iconoPdf;
        $imprimir4->title = $this->tituloPdf;
        $imprimir4->value = $this->tituloP;
        $imprimir4->class = $this->claseSecXs;

        $div = '
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
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
                    ' . $this->ponInput($actividad) . '
                </fieldset>

                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar4) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($imprimir4) . '
                </fieldset>
            </div>
        ';

        $tabla = new stdClass();
        $tabla->titulo = 'CALENDARIO MODIFICADO VS EJERCIDO';
        $tabla->id = 'tbl-ResumenSeg';
        $tabla->tools = $div;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';

        $tabla->comunica->trimestre = '{$("#comboTrimestre").val()}';
        $tabla->comunica->cfi_clave = '{$("#cfi_clave").val().toUpperCase()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->ue_id = '{$("#ue").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';
        $tabla->comunica->oa_id = '{$("#actividad").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->width = '300px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = '';
        $th->width = '25px';
        array_push($tr, $th);

        $mes = (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1);
        $NombreMes = '';
        if ($mes == 1) {
            $NombreMes = 'ENERO|FEBRERO|MARZO';
        } elseif ($mes == 2) {
            $NombreMes = 'ABRIL|MAYO|JUNIO';
        } elseif ($mes == 3) {
            $NombreMes = 'JULIO|AGOSTO|SEPTIEMBRE';
        } elseif ($mes == 4) {
            $NombreMes = 'OCTUBRE|NOVIEMBRE|DICIEMBRE';
        }

        if ($mes == 1) {
            $th = new stdClass();
            $th->texto = '' . substr($NombreMes, 0, 5) . '';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = '' . substr($NombreMes, 6, 7) . '';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = '' . substr($NombreMes, 14, 5) . '';
            array_push($tr, $th);
        } elseif ($mes == 2) {
            $th = new stdClass();
            $th->texto = '' . substr($NombreMes, 0, 5) . '';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = '' . substr($NombreMes, 6, 4) . '';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = '' . substr($NombreMes, 11, 5) . '';
            array_push($tr, $th);
        } elseif ($mes == 3) {
            $th = new stdClass();
            $th->texto = '' . substr($NombreMes, 0, 5) . '';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = '' . substr($NombreMes, 6, 6) . '';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = '' . substr($NombreMes, 13, 10) . '';
            array_push($tr, $th);
        } elseif ($mes == 4) {
            $th = new stdClass();
            $th->texto = '' . substr($NombreMes, 0, 7) . '';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = '' . substr($NombreMes, 8, 9) . '';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = '' . substr($NombreMes, 18, 9) . '';
            array_push($tr, $th);
        }

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'cap_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'Tipo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mesUno';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mesDos';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mesTres';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">3.UR.-PROG.-SPROG.-PY.-OBRA O ACTIVIDAD-CAPÍTULO</h4>
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

        $this->llenaTabla3();

        $this->mixScript('
            $("#myModal").modal("show");
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#btnBuscarS3").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"verPantallaSeg3",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        trimestre:$("#comboTrimestre").val(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val()
                    });
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#imprimirS3").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        aplicacion:"PDF",
                        rep_id:107
                    });
                    $("#reportes").submit();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#exportarS3").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                    metodo:"print",
                    ur_clave:$("#ur").val(),
                    cfi_clave: $("#cfi_clave").val().toUpperCase(),
                    ue_id:$("#ue").val(),
                    trimestre:$("#comboTrimestre").val(),
                    prg_id:$("#programa").val(),
                    spr_id:$("#subprograma").val(),
                    oa_id:$("#actividad").val(),
                    aplicacion:"EXCEL",
                    rep_id:107
                });
                $("#reportes").submit();
                }else{
                    notify("Seleccione un trimestre","error");
                }
            });
        ');
    }

    private function llenaTabla3()
    {

        $ur = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? ' AND CONCAT(gpo_id,ur_id)=' . $this->prm->ur_clave : '';
        $ue = (isset($this->prm->ue_id) && $this->prm->ue_id != '') ? ' AND pre.ue_id=' . $this->prm->ue_id : '';
        $prg = (isset($this->prm->prg_id) && $this->prm->prg_id != '') ? ' AND prg_id=' . $this->prm->prg_id : '';
        $spr = (isset($this->prm->spr_id) && $this->prm->spr_id != '') ? ' AND spr_id=' . $this->prm->spr_id : '';
        $oa = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? ' AND oa_id=' . $this->prm->oa_id : '';
        $cvefin = (isset($this->prm->cfi_clave) && $this->prm->cfi_clave != '') ? ' AND cf.cfi_clave="' . $this->prm->cfi_clave . '"' : '';
        $trimestre = (isset($this->prm->trimestre) && $this->prm->trimestre != '' ? $this->prm->trimestre : 1);
        switch ($trimestre) {
            case 1:
                $consultatri = 'SUM(vig_ene) AS mes_vig_uno,SUM(vig_feb) AS mes_vig_dos,SUM(vig_mar) AS mes_vig_tres,SUM(eje_ene) AS mes_eje_uno,SUM(eje_feb) AS mes_eje_dos,SUM(eje_mar) AS mes_eje_tres';
                break;
            case 2:
                $consultatri = 'SUM(vig_abr) AS mes_vig_uno,SUM(vig_may) AS mes_vig_dos,SUM(vig_jun) AS mes_vig_tres,SUM(eje_abr) AS mes_eje_uno,SUM(eje_may) AS mes_eje_dos,SUM(eje_jun) AS mes_eje_tres';
                break;
            case 3:
                $consultatri = 'SUM(vig_jul) AS mes_vig_uno,SUM(vig_ago) AS mes_vig_dos,SUM(vig_sep) AS mes_vig_tres,SUM(eje_jul) AS mes_eje_uno,SUM(eje_ago) AS mes_eje_dos,SUM(eje_sep) AS mes_eje_tres';
                break;
            case 4:
                $consultatri = 'SUM(vig_oct) AS mes_vig_uno,SUM(vig_nov) AS mes_vig_dos,SUM(vig_dic) AS mes_vig_tres,SUM(eje_oct) AS mes_eje_uno,SUM(eje_nov) AS mes_eje_dos,SUM(eje_dic) AS mes_eje_tres';
                break;
        }

        $this->conectar();
        $this->setSql('
            SELECT
                CONCAT(CAST(pre.gpo_id AS CHAR),CAST(pre.ur_id AS CHAR)) AS ur,cat_UnidadResponsable.ur_nombre AS desEntidad,
                CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
                CAST(pre.prg_id AS CHAR) AS Programa, cat_Programa.prg_nombre AS desPrograma,
                CAST(pre.spr_id AS CHAR) AS Subprograma,cat_Subprograma.spr_nombre AS desSubprograma,
                CAST(pre.py_id AS CHAR) AS proyecto,cat_Proyecto.py_nombre AS desProyecto,
                CAST(pre.oa_id AS CHAR) AS obraaccion,cat_ObraActividad.oa_nombre AS desObra,
                cf.cfi_clave,cf.cfi_nombre,pre.capitulo,cap.cap_nombre,(mes_vig_uno-mes_eje_uno) as mesUno,(mes_vig_dos-mes_eje_dos) as mesDos,(mes_vig_tres-mes_eje_tres) as mes_Tres
            FROM
            (
                SELECT
                    gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,SUBSTRING(pg_id,1,1) AS capitulo,tcp_id,
                    cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,
                    ' . $consultatri . '
                FROM presupuesto
                JOIN presupuestoVigente AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoEjercido AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                WHERE tcp_id<>"P"
                GROUP BY gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,SUBSTRING(pg_id,1,1),cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio
            ) AS pre
            LEFT JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            LEFT JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
            LEFT JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
            LEFT JOIN cat_Subprograma USING(gpo_id,ur_id,prg_id,spr_id)
            LEFT JOIN cat_Proyecto USING(gpo_id,ur_id,prg_id,spr_id,py_id)
            LEFT JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            LEFT JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' AS cf USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            LEFT JOIN cat_Capitulo AS cap ON pre.capitulo=cap.cap_id
            WHERE  toa_id="1"
                ' . $ur . $cvefin . $ue . $prg . $spr . $oa . '
            ORDER BY pre.gpo_id,ur_id,cf.cfi_clave,pre.ue_id,pre.prg_id,pre.spr_id,pre.py_id,pre.oa_id,pre.capitulo;
        ');
        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ResumenSeg';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }

    private function verPantallaSeg4()
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

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';
        $actividad->value = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? $this->prm->oa_id : '';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscarS4';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar3 = new stdClass();
        $exportar3->id = 'exportarS4';
        $exportar3->type = 'submit';
        $exportar3->icon = $this->iconD;
        $exportar3->title = $this->tituloEx;
        $exportar3->value = $this->tituloE;
        $exportar3->class = $this->claseIn;

        $imprimir3 = new stdClass();
        $imprimir3->id = 'imprimirS4';
        $imprimir3->type = 'submit';
        $imprimir3->icon = $this->iconoPdf;
        $imprimir3->title = $this->tituloPdf;
        $imprimir3->value = $this->tituloP;
        $imprimir3->class = $this->claseSecXs;

        $div = '
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
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
                    ' . $this->ponInput($actividad) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar3) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($imprimir3) . '
                </fieldset>
            </div>
        ';

        $tabla = new stdClass();
        $tabla->titulo = 'REPORTE DE MODIFICACIONES';
        $tabla->id = 'tbl-AdecuacionActividad';
        $tabla->tools = $div;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';
        $tabla->comunica->trimestre = '{$("#comboTrimestre").val()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';
        $tabla->comunica->py_id = '{$("#proyecto").val()}';
        $tabla->comunica->oa_id = '{$("#actividad").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'ORIGEN';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'NO. FOLIO';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO ADECUACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'AMPLIACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'REDUCCIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'META PROGRAMADA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'META ACTUALIZADA';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'omo_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mod_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tmd_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ampliacion';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'reduccion';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mod_concepto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mact_original';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mact_modificado';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">4.-MODIFICACIONES PRESUPUESTALES</h4>
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
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#btnBuscarS4").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"llenaTabla4",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        trimestre:$("#comboTrimestre").val(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val()
                    });
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#imprimirS4").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        aplicacion:"PDF",
                        rep_id:108
                    });
                    $("#reportes").submit();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#exportarS4").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        aplicacion:"EXCEL",
                        rep_id:108
                    });
                $("#reportes").submit();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });
        ');
    }

    private function llenaTabla4()
    {

        $ur = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? ' AND CONCAT(MM.mod_gpo_id,MM.mod_ur_id)=' . $this->prm->ur_clave : '';
        $ue = (isset($this->prm->ue_id) && $this->prm->ue_id != '') ? ' AND P.ue_id=' . $this->prm->ue_id : '';
        $prg = (isset($this->prm->prg_id) && $this->prm->prg_id != '') ? ' AND PRO.prg_id=' . $this->prm->prg_id : '';
        $spr = (isset($this->prm->spr_id) && $this->prm->spr_id != '') ? ' AND SP.spr_id=' . $this->prm->spr_id : '';
        $oa = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? ' AND OA.oa_id=' . $this->prm->oa_id : '';
        $cvefin = (isset($this->prm->cfi_clave) && $this->prm->cfi_clave != '') ? ' WHERE cf.cfi_clave="' . $this->prm->cfi_clave . '"' : '';

        switch ($this->prm->trimestre) {
            case 1:
                $trim = ' MONTH(MM.mod_fechaAutorizacion) BETWEEN 01 AND 03 ';
                $timestre = '1';
                break;
            case 2:
                $trim = ' MONTH(MM.mod_fechaAutorizacion) BETWEEN 04 AND 06 ';
                $timestre = '2';
                break;
            case 3:
                $trim = ' MONTH(MM.mod_fechaAutorizacion) BETWEEN 07 AND 09';
                $timestre = '3';
                break;
            case 4:
                $trim = ' MONTH(MM.mod_fechaAutorizacion) BETWEEN 10 AND 12 ';
                $timestre = '4';
                break;
        }

        $this->conectar();
        $this->setSql('
            SELECT
                det.omo_id,CONCAT(det.mod_gpo_id,det.mod_ur_id ) AS ur,
                det.mod_id,ur_nombre,cf.cfi_clave,cf.cfi_nombre,
                det.prg_id,prg_nombre,det.spr_id,spr_nombre,det.oa_id,
                oa_nombre,mod_concepto,ampliacion,reduccion,
                det.mact_original,det.mact_modificado,
                eo.tmd_id,tmd_nombre,det.epg_id
            FROM (
                SELECT
                    omo_id,mod_gpo_id,mod_ur_id,mod_gpo_id AS gpo_id,mod_ur_id AS ur_id,
                    mod_id, EP.epg_id,EF.ecf_id,enc_oop,mod_concepto,
                    CAST(PRO.prg_id AS CHAR) AS prg_id,PRO.prg_nombre,
                    CAST(SP.spr_id AS CHAR) AS spr_id,spr_nombre,
                    CAST(OA.oa_id AS CHAR) AS oa_id, oa_nombre,
                    SUM(CASE mov_id WHEN "A" THEN dmo_importe ELSE 0 END) AS ampliacion,
                    SUM(CASE WHEN mov_id="R" THEN dmo_importe ELSE 0 END) AS reduccion,
                    mact_original,mact_modificado
                FROM scp_MaestroModificacion MM
                JOIN scp_DetalleModificacion DM USING(omo_id, mod_gpo_id, mod_ur_id, mod_id)
                JOIN enc_OrigenOperacion USING(omo_id, enc_oop)
                JOIN cat_TipoModificacion USING(tmd_id)
                JOIN presupuesto P ON P.gpo_id=DM.gpo_id  AND P.ur_id=DM.ur_id AND P.ue_id= DM.ue_id AND P.epg_id=DM.epg_id AND P.pg_id=DM.pg_id AND P.pe_id= DM.pe_id  AND P.ecf_id=DM.ecf_id AND P.tcp_id=DM.tcp_id
                JOIN enc_Programatico EP ON P.epg_id=EP.epg_id AND P.spr_id=EP.spr_id AND P.py_id=EP.py_id AND P.oa_id=EP.oa_id
                JOIN cat_ObraActividad OA ON OA.gpo_id=EP.gpo_id AND OA.ur_id=EP.ur_id AND OA.prg_id=EP.prg_id AND OA.spr_id=EP.spr_id AND OA.py_id =EP.py_id AND OA.oa_id =EP.oa_id
                JOIN cat_Proyecto PR ON PR.gpo_id=OA.gpo_id AND PR.ur_id=OA.ur_id AND PR.prg_id=OA.prg_id AND PR.spr_id=OA.spr_id AND PR.py_id=OA.py_id
                JOIN cat_Subprograma SP ON SP.gpo_id=PR.gpo_id AND SP.ur_id=PR.ur_id AND SP.prg_id=PR.prg_id AND SP.spr_id=PR.spr_id
                JOIN cat_Programa PRO ON PRO.gpo_id=SP.gpo_id AND PRO.ur_id=SP.ur_id AND PRO.prg_id=SP.prg_id
                JOIN cat_UnidadResponsable UR ON UR.gpo_id=PRO.gpo_id AND UR.ur_id=PRO.ur_id
                JOIN enc_ClaveFinanciamiento EF ON EF.ecf_id=P.ecf_id AND EF.cff_id=P.cff_id AND EF.fte_id=P.fte_id AND EF.fte_id=P.fte_id AND EF.sft_id=P.sft_id AND EF.ori_id=P.ori_id AND EF.sor_id=P.sor_id AND EF.cfi_id=P.cfi_id AND EF.cfi_anio=P.cfi_anio
                JOIN cat_MetaActividad MA ON MA.epg_id=EP.epg_id
                JOIN cat_MetaActividadTrimestre MAT ON MAT.pii_id=MA.pii_id AND MAT.epg_id=MA.epg_id
                WHERE
                    ' . $trim . '
                    AND MM.emo_id IN ("14","15","16")
                    AND DM.mov_id IN ("A","R") AND DM.tcp_id<>"P" # Adecuaciones presupuestales
                    AND MAT.tri_id=' . $timestre . ' #MIR
                    ' . $ur . $ue . $prg . $spr . $oa . '
                GROUP BY epg_id,mod_id
            ) AS det
            INNER JOIN cat_UnidadResponsable AS cur USING (gpo_id,ur_id)
            INNER JOIN enc_ClaveFinanciamiento AS ecf USING (ecf_id)
            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' AS cf USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            INNER JOIN enc_OrigenOperacion AS eo USING (enc_oop)
            INNER JOIN cat_TipoModificacion AS tm USING (tmd_id)
            ' . $cvefin . '
            ORDER BY det.mod_gpo_id,det.mod_ur_id,det.ecf_id,det.mod_id;
        ');
        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-AdecuacionActividad';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }

    private function verPantallaSegObs()
    {
        $this->setContenedor('divContenedorGeneralModal');

        //filtro por trimestre
        $comboTrimestre = new stdClass();
        $comboTrimestre->id = 'comboTrimestre';
        $comboTrimestre->label = 'Trimestre';
        $comboTrimestre->opciones = [
            '' => 'Seleccione una opción de la lista',
            1  => 'Trimestre',
            2  => 'Trimestre',
            3  => 'Trimestre',
            4  => 'Trimestre',
        ];
        $comboTrimestre->width = '400px';
        $comboTrimestre->search = true;

        $ur = new stdClass();
        $ur->id = 'ur';
        $ur->label = 'UR';
        $ur->maxlength = 3;
        $ur->formato = 'entero';

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

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';

        $desde = new stdClass();
        $desde->id = 'desde';
        $desde->class = 'fecha';
        $desde->label = 'Desde';
        $desde->placeholder = 'Desde';
        $desde->enDiv = true;
        $desde->formato = 'fecha';
        $desde->labelClass = '_32 txtL';
        $desde->required = true;

        $hasta = new stdClass();
        $hasta->id = 'hasta';
        $hasta->class = 'fecha';
        $hasta->label = 'Hasta';
        $hasta->placeholder = 'Hasta';
        $hasta->enDiv = true;
        $hasta->formato = 'fecha';
        $hasta->labelClass = '_32 txtL';
        $hasta->required = true;

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscarOb';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar = new stdClass();
        $exportar->id = 'exportarObserva';
        $exportar->value = $this->tituloE;
        $exportar->type = 'submit';
        $exportar->icon = $this->iconD;
        $exportar->title = $this->tituloEx;
        $exportar->class = $this->claseIn;

        $imprimir = new stdClass();
        $imprimir->id = 'imprimir';
        $imprimir->value = $this->tituloP;
        $imprimir->type = 'submit';
        $imprimir->icon = $this->iconoPdf;
        $imprimir->title = $this->tituloPdf;
        $imprimir->class = $this->claseSecXs;

        $tabla = new stdClass();
        $tabla->titulo = 'Lista de Obras/Actividades por trimestre';
        $tabla->id = 'tbl-ObservaSeg';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE';
        $th->width = '5px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD RESPONSABLE';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD EJECUTORA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'SUB-PROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NUM ACT/OBRA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ACT/OBRA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'FECHA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'HORA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'OBSERVACIÓN';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'RESPUESTA';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'ur_clave';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ur_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ue_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'prg_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'spr_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'oa_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'oa_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'fecha';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'hora';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'hms_mensaje';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'hms_mensajeRespuesta';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">5.-OBSERVACIONES</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <fieldset class="col-md-2 col-xs-2">
                                    ' . $this->ponCombo($comboTrimestre) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponInput($ur) . '
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
                                    ' . $this->ponInput($actividad) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponInput($desde) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponInput($hasta) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponBoton($btnBuscar) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponBoton($exportar) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponBoton($imprimir) . '
                                </fieldset>
                            </div>
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
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#btnBuscarOb ").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"llenaTablaObs",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        desde:$("#desde").val(),
                        hasta:$("#hasta").val(),
                    });
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#exportarObserva").click(function(){
                if($("#comboTrimestre").val() !=""){
                    var form = $("<form>")
                        .attr("action", "rep/excel/poa/rep_ExportaObservaGastosOp.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "trimestre").val( $("#comboTrimestre").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "ur_clave").val( $("#ur").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "ue_id").val( $("#ue").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "prg_id").val( $("#programa").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "spr_id").val( $("#subprograma").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "oa_id").val( $("#actividad").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "desde").val( $("#desde").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "hasta").val( $("#hasta").val() ))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                } else {
                    notify("Debe seleccionar el trimestre","error");
                }
            });

            $("#imprimir").click(function(){
                if($("#comboTrimestre").val() !=""){
                    var form = $("<form>")
                        .attr("action", "rep/doc/poa/rep_ObservaGastosOp.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "trimestre").val( $("#comboTrimestre").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "ur_clave").val( $("#ur").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "ue_id").val( $("#ue").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "prg_id").val( $("#programa").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "spr_id").val( $("#subprograma").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "oa_id").val( $("#actividad").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "desde").val( $("#desde").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "hasta").val( $("#hasta").val() ))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                } else {
                    notify("Debe seleccionar el trimestre","error");
                }
            });
        ');
    }

    private function llenaTablaObs()
    {

        $ur = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? 'AND CONCAT(gpo_id,ur_id)=' . $this->prm->ur_clave : '';
        $ue = (isset($this->prm->ue_id) && $this->prm->ue_id != '') ? ' AND ue_id=' . $this->prm->ue_id : '';
        $prg = (isset($this->prm->prg_id) && $this->prm->prg_id != '') ? ' AND prg_id=' . $this->prm->prg_id : '';
        $spr = (isset($this->prm->spr_id) && $this->prm->spr_id != '') ? ' AND spr_id=' . $this->prm->spr_id : '';
        $oa = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? ' AND oa_id=' . $this->prm->oa_id : '';
        $trim = (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? ' AND hms_trimestre=' . $this->prm->trimestre : '';
        $desde = (isset($this->prm->desde) && $this->prm->desde != '') ? $this->prm->desde : '';
        $hasta = (isset($this->prm->hasta) && $this->prm->hasta != '') ? $this->prm->hasta : '';
        $filtroFechas = '';
        if ($desde != '' || $hasta != '') {

            if ($desde != '' && $hasta != '') {
                $filtroFechas = " AND DATE_FORMAT(chms.usr_fecha,'%d/%m/%y') BETWEEN '" . $desde . "' AND '" . $hasta . "' ";
            } elseif ($desde != '') {
                $filtroFechas = " AND DATE_FORMAT(chms.usr_fecha,'%d/%m/%y') >='" . $desde . "' ";
            } elseif ($hasta != '') {
                $filtroFechas = " AND DATE_FORMAT(chms.usr_fecha,'%d/%m/%y') <='" . $hasta . "' ";
            }
        }
        $fechas = (isset($this->prm->fecha) && $this->prm->fecha != '') ? ' AND hm.usr_fecha=' . $this->prm->trimestre : '';

        $this->conectar();

        $this->setSql('
            SELECT
                CONCAT(gpo_id,ur_id) AS ur_clave,ur_nombre,ue_id,prg_id,spr_id,py_id,oa_id,oa_nombre,
                CONCAT(ci.nim_id,"-",ci.ind_id) AS ind_id,toa_nombre, m.pii_id AS meta,
                mact_original as programada,mact_modificado as actualizada,
                mact_alcanzado as alcanzada,
                IF(mact_alcanzado !=0 , ROUND((mact_alcanzado/mact_modificado)*100,2), 0.00) as alcanzado,
                hms_trimestre,hms_mensaje,DATE(chms.usr_fecha) AS fecha, TIME(chms.usr_fecha) as hora,hms_mensajeRespuesta
            FROM cat_MetaActividad m
            INNER JOIN cat_MetaActividadTrimestre mt using(pii_id, epg_id)
            INNER JOIN enc_Programatico as e using(epg_id)
            INNER JOIN cat_ObraActividad using(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            INNER JOIN cat_TipoObraActividad USING (toa_id)
            INNER JOIN vin_ProductoIntermedioIndicador pii USING(pii_id)
            INNER JOIN cat_Indicador ci USING(nim_id,ind_id)
            INNER JOIN cat_UnidadResponsable ur using(gpo_id,ur_id)
            INNER JOIN cat_MensajeSeguimiento cms ON e.epg_id = cms.epg_id
            INNER JOIN cat_HistorialMensajeSeguimiento chms on cms.msj_id = chms.msj_id
            WHERE chms.tim_id = 2  AND IFNULL(hms_mensaje,"")!=""
                ' . $ur . $ue . $prg . $spr . $oa . $trim . $filtroFechas . '
            GROUP BY cms.msj_id
            ORDER BY gpo_id,ur_id,ind_id,oa_id;
        ');
        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ObservaSeg';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }

    /***********Inician Reportes Mensuales Seguimiento a Gastos de Operación*****************/
    private function verPantallaSegM1()
    {
        $this->setContenedor('divContenedorGeneralModal');
        //filtro por mes
        $comboMes = new stdClass();
        $comboMes->id = 'comboMes';
        $comboMes->label = 'Mes';
        $comboMes->mensaje = 'SELECCIONE EL MES A CONSULTAR';
        $comboMes->opciones = ['' => 'Seleccione una opción de la lista', 1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
        $comboMes->width = '400px';
        $comboMes->selecciona = (isset($this->prm->mes) && $this->prm->mes != '') ? $this->prm->mes : 0;

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

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscarS1';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar = new stdClass();
        $exportar->id = 'exportarS1';
        $exportar->value = $this->tituloE;
        $exportar->type = 'submit';
        $exportar->icon = $this->iconD;
        $exportar->title = $this->tituloEx;
        $exportar->class = $this->claseIn;

        $imprimir = new stdClass();
        $imprimir->id = 'imprimirS1';
        $imprimir->value = $this->tituloP;
        $imprimir->type = 'submit';
        $imprimir->icon = $this->iconoPdf;
        $imprimir->title = $this->tituloPdf;
        $imprimir->class = $this->claseSecXs;

        $tabla = new stdClass();
        $tabla->titulo = 'Lista de Obras/Actividades por mes';
        $tabla->id = 'tbl-Presupuesto1';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE';
        $th->width = '5px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UE';
        $th->width = '5px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CVE FIN';
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

        $th = new stdClass();
        $th->texto = 'DEVENGADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EJERCIDO';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ue_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ue_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cfi_clave';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->formato = 'moneda';
        $td->campoTxt = 'origina';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->formato = 'moneda';
        $td->campoTxt = 'vigente';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->formato = 'moneda';
        $td->campoTxt = 'comprom';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->formato = 'moneda';
        $td->campoTxt = 'devenga';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->formato = 'moneda';
        $td->campoTxt = 'ejercid';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">1. UR.-PROG.-SPROG. MENSUAL</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <fieldset class="col-md-2 col-xs-2">
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
                                    ' . $this->ponInput($actividad) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponBoton($btnBuscar) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponBoton($exportar) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponBoton($imprimir) . '
                                </fieldset>
                            </div>
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
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#btnBuscarS1").click(function(){
                if($("#comboMes").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"llenaTablaSegM1",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        mes:$("#comboMes").val(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                    });
                } else {
                    notify("Seleccione un mes","error");
                }
            });

            $("#exportarS1").click(function(){
                if($("#comboMes").val() !=""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        mes:$("#comboMes").val(),
                        aplicacion:"EXCEL",
                        rep_id:111
                    });
                    $("#reportes").submit();
                } else {
                    notify("Debe seleccionar el mes","error");
                }
            });

            $("#imprimirS1").click(function(){
                if($("#comboMes").val() !=""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        mes:$("#comboMes").val(),
                        aplicacion:"PDF",
                        rep_id:111
                    });
                    $("#reportes").submit();
                } else {
                    notify("Debe seleccionar el mes","error");
                }
            });
        ');
    }

    private function llenaTablaSegM1()
    {
        $ur = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? ' AND ur_clave=' . $this->prm->ur_clave : '';
        $ue = (isset($this->prm->ue_id) && $this->prm->ue_id != '') ? ' AND ue_id=' . $this->prm->ue_id : '';
        $prg = (isset($this->prm->prg_id) && $this->prm->prg_id != '') ? ' AND prg_id=' . $this->prm->prg_id : '';
        $spr = (isset($this->prm->spr_id) && $this->prm->spr_id != '') ? ' AND spr_id=' . $this->prm->spr_id : '';
        $oa = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? ' AND oa_id=' . $this->prm->oa_id : '';
        $cvefin = (isset($this->prm->cfi_clave) && $this->prm->cfi_clave != '') ? ' AND cfi_clave="' . $this->prm->cfi_clave . '"' : '';

        $mesSeleccionado = $this->prm->mes;
        //Declaración de arreglo de meses
        $meses = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

        //Inicialización de parámetros de consulta de acuerdo al mes seleccionado
        $presupuesto = '
            (SUM(apr_' . $meses[$mesSeleccionado] . ')) AS origina,
            (SUM(vig_' . $meses[$mesSeleccionado] . ')) AS vigente,
            (SUM(com_' . $meses[$mesSeleccionado] . ')) AS comprom,
            (SUM(dev_' . $meses[$mesSeleccionado] . ')) AS devenga,
            (SUM(eje_' . $meses[$mesSeleccionado] . ')) AS ejercid ';

        $this->conectar();
        $this->setSql('
            SELECT
                CONCAT(pre.gpo_id,pre.ur_id) AS ur,cat_UnidadResponsable.ur_nombre AS desEntidad,
                CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
                CAST(pre.prg_id AS CHAR) AS prg_id,cat_Programa.prg_nombre AS despgma,
                CAST(pre.spr_id AS CHAR) AS spr_id,cat_Subprograma.spr_nombre AS desspgma,
                cf.cfi_clave,cf.cfi_nombre,
                sum(pre.origina) as origina,sum(pre.vigente) as vigente,
                sum(pre.comprom) as comprom,sum(pre.devenga) as devenga,
                sum(pre.ejercid) as ejercid,pre.oa_id
            FROM
            (
                SELECT
                    gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,tcp_id,
                    cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,
                    ' . $presupuesto . '
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
            LEFT JOIN cat_ObraActividad USING (gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id)
            LEFT JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' AS cf USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            WHERE toa_id="1"' . $ur . $cvefin . $ue . $prg . $spr . $oa . '
            group by pre.gpo_id,pre.ur_id,cf.cfi_clave,ue_nombre
            ORDER BY pre.gpo_id,pre.ur_id,cf.cfi_clave,pre.ue_id,pre.prg_id,pre.spr_id
        ');
        $this->consultar();
        $this->desconectar();
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-Presupuesto1';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }

    private function verPantallaSegM2()
    {
        $this->setContenedor('divContenedorGeneralModal');
        //filtro por mes
        $comboMes = new stdClass();
        $comboMes->id = 'comboMes';
        $comboMes->label = 'Mes';
        $comboMes->mensaje = 'SELECCIONE EL MES A CONSULTAR';
        $comboMes->opciones = ['' => 'Seleccione una opción de la lista', 1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
        $comboMes->width = '400px';
        $comboMes->selecciona = (isset($this->prm->mes) && $this->prm->mes != '') ? $this->prm->mes : 0;

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

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscarS1';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar = new stdClass();
        $exportar->id = 'exportarS1';
        $exportar->value = $this->tituloE;
        $exportar->type = 'submit';
        $exportar->icon = $this->iconD;
        $exportar->title = $this->tituloEx;
        $exportar->class = $this->claseIn;

        $imprimir = new stdClass();
        $imprimir->id = 'imprimirS1';
        $imprimir->value = $this->tituloP;
        $imprimir->type = 'submit';
        $imprimir->icon = $this->iconoPdf;
        $imprimir->title = $this->tituloPdf;
        $imprimir->class = $this->claseSecXs;

        $tabla = new stdClass();
        $tabla->titulo = 'Lista de Obras/Actividades por mes';
        $tabla->id = 'tbl-ProgramaSubprogramaActividad';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE';
        $th->width = '5px';
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

        $th = new stdClass();
        $th->texto = 'DEVENGADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EJERCIDO';
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
        $td->campoTxt = 'desObra';
        array_push($trbody, $td);

        $aprobado = '';
        $vigente = '';
        $comprometido = '';
        $devengado = '';
        $ejercido = '';

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

        $td = new stdClass();
        $td->campoTxt = 'devenga';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ejercid';
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
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">2. UR.-PROG.-SPROG.-PY.-OBRA O ACTIVIDAD MENSUAL</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <fieldset class="col-md-2 col-xs-2">
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
                                    ' . $this->ponInput($actividad) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponBoton($btnBuscar) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponBoton($exportar) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponBoton($imprimir) . '
                                </fieldset>
                            </div>
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
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#btnBuscarS1").click(function(){
                if($("#comboMes").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"llenaTablaSegM2",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        mes:$("#comboMes").val(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                    });
                } else {
                    notify("Seleccione un mes","error");
                }
            });

            $("#exportarS1").click(function(){
                if($("#comboMes").val() !=""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        mes:$("#comboMes").val(),
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
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        mes:$("#comboMes").val(),
                        aplicacion:"PDF",
                        rep_id:112
                    });
                    $("#reportes").submit();
                } else {
                    notify("Debe seleccionar el mes","error");
                }
            });
        ');
    }
    private function llenaTablaSegM2()
    {
        $ur = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? ' AND CONCAT(gpo_id,ur_id)=' . $this->prm->ur_clave : '';
        $ue = (isset($this->prm->ue_id) && $this->prm->ue_id != '') ? ' AND pre.ue_id=' . $this->prm->ue_id : '';
        $prg = (isset($this->prm->prg_id) && $this->prm->prg_id != '') ? ' AND prg_id=' . $this->prm->prg_id : '';
        $spr = (isset($this->prm->spr_id) && $this->prm->spr_id != '') ? ' AND spr_id=' . $this->prm->spr_id : '';
        $oa = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? ' AND oa_id=' . $this->prm->oa_id : '';
        $cvefin = (isset($this->prm->cfi_clave) && $this->prm->cfi_clave != '') ? ' AND cf.cfi_clave="' . $this->prm->cfi_clave . '"' : '';

        $mesSeleccionado = $this->prm->mes;
        //Declaración de arreglo de meses
        $meses = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
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
                CAST(pre.prg_id AS CHAR) AS prg_id,cat_Programa.prg_nombre AS despgma,
                CAST(pre.spr_id AS CHAR) AS spr_id,cat_Subprograma.spr_nombre AS desspgma,
                CAST(pre.py_id AS CHAR) AS py_id,cat_Proyecto.py_nombre AS desproyecto,
                CAST(pre.oa_id AS CHAR) AS obraaccion,cat_ObraActividad.oa_nombre AS desObra,
                cf.cfi_clave,cf.cfi_nombre,
                sum(pre.origina) as origina,sum(pre.vigente) as vigente,
                sum(pre.comprom) as comprom,sum(pre.devenga) as devenga,
                sum(pre.ejercid) as ejercid,
                CASE (pre.vigente+pre.ejercid) WHEN 0 THEN 0  ELSE CAST(ROUND(((pre.ejercid*100)/pre.vigente),0) AS CHAR)  END AS sema,
                CASE
                    WHEN (ROUND(((pre.ejercid*100)/pre.vigente),0) BETWEEN 0 AND 40) OR ROUND(((pre.ejercid*100)/pre.vigente),0) IS NULL THEN "FF0000"
                    WHEN (ROUND(((pre.ejercid*100)/pre.vigente),0) BETWEEN 41 AND 75) THEN "F3C12A"
                    WHEN (ROUND(((pre.ejercid*100)/pre.vigente),0) BETWEEN 76 AND 100) THEN "41A62A"
                ELSE "afafaf" END AS color,pre.por
            FROM
            (
                SELECT
                    gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,tcp_id,
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
            WHERE  toa_id="1" ' . $ur . $cvefin . $ue . $prg . $spr . $oa . '
            GROUP BY pre.gpo_id,pre.ur_id,desObra,cf.cfi_clave
            ORDER BY pre.gpo_id,pre.ur_id,cf.cfi_clave,pre.ue_id,pre.prg_id,pre.spr_id,pre.oa_id;
        ');

        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ProgramaSubprogramaActividad';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }

    }
    private function verPantallaSegM3()
    {
        $this->setContenedor('divContenedorGeneralModal');
        //filtro por mes
        $comboMes = new stdClass();
        $comboMes->id = 'comboMes';
        $comboMes->label = 'Mes';
        $comboMes->mensaje = 'SELECCIONE EL MES A CONSULTAR';
        $comboMes->opciones = ['' => 'Seleccione una opción de la lista', 1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
        $comboMes->width = '400px';
        $comboMes->selecciona = (isset($this->prm->mes) && $this->prm->mes != '') ? $this->prm->mes : 0;

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

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscarS1';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar = new stdClass();
        $exportar->id = 'exportarS1';
        $exportar->value = $this->tituloE;
        $exportar->type = 'submit';
        $exportar->icon = $this->iconD;
        $exportar->title = $this->tituloEx;
        $exportar->class = $this->claseIn;

        $imprimir = new stdClass();
        $imprimir->id = 'imprimirS1';
        $imprimir->value = $this->tituloP;
        $imprimir->type = 'submit';
        $imprimir->icon = $this->iconoPdf;
        $imprimir->title = $this->tituloPdf;
        $imprimir->class = $this->claseSecXs;

        $tabla = new stdClass();
        $tabla->titulo = 'Lista de Obras/Actividades por mes';
        $tabla->id = 'tbl-ResumenSeg';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';

        $tabla->comunica->mes = '{$("#comboMes").val()}';
        $tabla->comunica->cfi_clave = '{$("#cfi_clave").val().toUpperCase()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->ue_id = '{$("#ue").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';
        $tabla->comunica->oa_id = '{$("#actividad").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'UR';
        $th->width = '100px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PROGRAMA';
        $th->width = '300px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'SUB-PROGRAMA';
        $th->width = '300px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'OBRA/ACT';
        $th->width = '300px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->width = '300px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLAVE';
        $th->width = '300px';
        array_push($tr, $th);

        $mes = (isset($this->prm->mes) && $this->prm->mes != '' ? $this->prm->mes : 1);
        $nombreMes = ['', 'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
        $th = new stdClass();
        $th->texto = '' . $nombreMes[$mes] . '';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'desPrograma';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'desSubprograma';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'desObra';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cap_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cfi_clave';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mesUno';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">3. UR.-PROG.-SPROG.-PY.-OBRA O ACTIVIDAD-CAPÍTULO(COMPARATIVO) MENSUAL</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <fieldset class="col-md-2 col-xs-2">
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
                                    ' . $this->ponInput($actividad) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponBoton($btnBuscar) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponBoton($exportar) . '
                                </fieldset>
                                <fieldset class="col-md-1 col-xs-1">
                                    ' . $this->ponBoton($imprimir) . '
                                </fieldset>
                            </div>
                            ' . $this->wrPonTabla($tabla) . '
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary cancelaPt" id="cancelaPt" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');

        $this->llenaTablaSegM3();

        $this->mixScript('
            $("#myModal").modal("show");
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#btnBuscarS1").click(function(){
                if($("#comboMes").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"verPantallaSegM3",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        mes:$("#comboMes").val(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                    });
                } else {
                    notify("Seleccione un mes","error");
                }
            });

            $("#exportarS1").click(function(){
                if($("#comboMes").val() !=""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        mes:$("#comboMes").val(),
                        aplicacion:"EXCEL",
                        rep_id:113
                    });
                    $("#reportes").submit();
                }else{
                    notify("Debe seleccionar el mes","error");
                }
            });

            $("#imprimirS1").click(function(){
                if($("#comboMes").val() !=""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        cfi_clave: $("#cfi_clave").val().toUpperCase(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        oa_id:$("#actividad").val(),
                        mes:$("#comboMes").val(),
                        aplicacion:"PDF",
                        rep_id:113
                    });
                    $("#reportes").submit();
                } else {
                    notify("Debe seleccionar el mes","error");
                }
            });
        ');
    }
    private function llenaTablaSegM3()
    {

        $ur = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? ' AND CONCAT(gpo_id,ur_id)=' . $this->prm->ur_clave : '';
        $ue = (isset($this->prm->ue_id) && $this->prm->ue_id != '') ? ' AND pre.ue_id=' . $this->prm->ue_id : '';
        $prg = (isset($this->prm->prg_id) && $this->prm->prg_id != '') ? ' AND prg_id=' . $this->prm->prg_id : '';
        $spr = (isset($this->prm->spr_id) && $this->prm->spr_id != '') ? ' AND spr_id=' . $this->prm->spr_id : '';
        $oa = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? ' AND oa_id=' . $this->prm->oa_id : '';
        $cvefin = (isset($this->prm->cfi_clave) && $this->prm->cfi_clave != '') ? ' AND cf.cfi_clave="' . $this->prm->cfi_clave . '"' : '';
        $mesSeleccionado = (isset($this->prm->mes) && $this->prm->mes != '' ? $this->prm->mes : 1);
        //Declaración de arreglo de meses
        $meses = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        $consultames = 'SUM(vig_' . $meses[$mesSeleccionado] . ') AS mes_vig_uno,SUM(eje_' . $meses[$mesSeleccionado] . ') AS mes_eje_uno';

        $this->conectar();

        $this->setSql('
            SELECT
                CONCAT(CAST(pre.gpo_id AS CHAR),CAST(pre.ur_id AS CHAR)) AS ur,cat_UnidadResponsable.ur_nombre AS desEntidad,
                CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
                CAST(pre.prg_id AS CHAR) AS Programa, cat_Programa.prg_nombre AS desPrograma,
                CAST(pre.spr_id AS CHAR) AS Subprograma,cat_Subprograma.spr_nombre AS desSubprograma,
                CAST(pre.py_id AS CHAR) AS proyecto,cat_Proyecto.py_nombre AS desProyecto,
                CAST(pre.oa_id AS CHAR) AS obraaccion,cat_ObraActividad.oa_nombre AS desObra,
                cf.cfi_clave,cf.cfi_nombre,pre.capitulo,cap.cap_nombre,(mes_vig_uno-mes_eje_uno) as mesUno
            FROM
            (
                SELECT
                    gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,SUBSTRING(pg_id,1,1) AS capitulo,tcp_id,
                    cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,
                    ' . $consultames . '
                FROM presupuesto
                JOIN presupuestoVigente AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoEjercido AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                WHERE tcp_id<>"P"
                GROUP BY gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,SUBSTRING(pg_id,1,1),cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio
            ) AS pre
            LEFT JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            LEFT JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
            LEFT JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
            LEFT JOIN cat_Subprograma USING(gpo_id,ur_id,prg_id,spr_id)
            LEFT JOIN cat_Proyecto USING(gpo_id,ur_id,prg_id,spr_id,py_id)
            LEFT JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            LEFT JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' AS cf USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            LEFT JOIN cat_Capitulo AS cap ON pre.capitulo=cap.cap_id
            WHERE  toa_id="1"
                ' . $ur . $cvefin . $ue . $prg . $spr . $oa . '
            ORDER BY pre.gpo_id,ur_id,cf.cfi_clave,pre.ue_id,pre.prg_id,pre.spr_id,pre.py_id,pre.oa_id,pre.capitulo;
        ');
        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ResumenSeg';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }
    /***********finalizan Reportes Mensuales Seguimiento a Gastos de Operación*****************/

    private function verPantallaSemaforizacion()
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
        $comboTrimestre->width = '400px';
        $comboTrimestre->selecciona = 1;
        $comboTrimestre->search = true;

        $ur = new stdClass();
        $ur->id = 'ur';
        $ur->label = 'UR';
        $ur->maxlength = 3;
        $ur->formato = 'entero';
        $ur->value = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ur_clave : '';

        $ue = new stdClass();
        $ue->id = 'ue';
        $ue->label = 'UE';
        $ue->maxlength = 3;
        $ue->formato = 'entero';
        $ue->value = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ue_id : '';

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

        $proyecto = new stdClass();
        $proyecto->id = 'proyecto';
        $proyecto->label = 'Proyecto';
        $proyecto->maxlength = 3;
        $proyecto->formato = 'entero';
        $proyecto->value = (isset($this->prm->py_id) && $this->prm->py_id != '') ? $this->prm->py_id : '';

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';
        $actividad->value = (isset($this->prm->spr_id) && $this->prm->oa_id != '') ? $this->prm->oa_id : '';

        $tipoact = new stdClass();
        $tipoact->id = 'tipoact';
        $tipoact->label = 'Tipo Actividad ';
        $tipoact->maxlength = 3;
        $tipoact->formato = 'entero';
        $tipoact->value = (isset($this->prm->toa_id) && $this->prm->toa_id != '') ? $this->prm->toa_id : '';

        $porcentaje = new stdClass();
        $porcentaje->id = 'porcentaje';
        $porcentaje->formato = 'numero';
        $porcentaje->value = (isset($this->prm->porcentaje) && $this->prm->porcentaje != '') ? $this->prm->porcentaje : '';

        $porcentaje2 = new stdClass();
        $porcentaje2->id = 'porcentaje2';
        $porcentaje2->formato = 'numero';
        $porcentaje2->value = (isset($this->prm->porcentaje2) && $this->prm->porcentaje2 != '') ? $this->prm->porcentaje2 : '';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'BuscarSemaMeta';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar1 = new stdClass();
        $exportar1->id = 'exportarSemaMeta';
        $exportar1->value = $this->tituloE;
        $exportar1->type = 'submit';
        $exportar1->icon = $this->iconD;
        $exportar1->title = $this->tituloEx;
        $exportar1->class = $this->claseIn;

        $imprimir1 = new stdClass();
        $imprimir1->id = 'imprimirSemaMeta';
        $imprimir1->value = $this->tituloP;
        $imprimir1->type = 'submit';
        $imprimir1->icon = $this->iconoPdf;
        $imprimir1->title = $this->tituloPdf;
        $imprimir1->class = $this->claseSecXs;

        $tools = '
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->templateCombo($comboTrimestre) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($ur) . '
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
                    ' . $this->ponInput($tipoact) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    <label>Porcentaje</label><br/>
                    DEL ' . $this->ponInput($porcentaje) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    <label></label><br/>
                    AL ' . $this->ponInput($porcentaje2) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar1) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($imprimir1) . '
                </fieldset>
            </div><br/>
        ';

        $tabla = new stdClass();
        $tabla->titulo = 'REPORTE DE SEMAFORIZACIÓN DE METAS';
        $tabla->id = 'tbl-ProgramaSemaMeta';
        $tabla->tools = $tools;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';
        $tabla->comunica->trimestre = '{$("#comboTrimestre").val()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';
        $tabla->comunica->py_id = '{$("#proyecto").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE UR';
        $th->width = '5px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD RESPONSABLE';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD EJECUTORA';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'SUBPROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROYECTO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NUM ACT/OBRA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ACT/ OBRA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NÚM IND.';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'TIPO DE ACTIVIDAD';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NÚM. META';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META PROGRAMADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META ACTUALIZADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META ALCANZADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = '% ALCANZADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'TRIMESTRE';
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
        $td->campoTxt = 'ue_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'prg_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'spr_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'py_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'obraaccion';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'desObra';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'indicador';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'toa_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'meta';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'programada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'actualizada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'alcanzada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'porcentaje';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'trimestre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'center';
        $td->tipo = 'bolita';
        $td->linea = '000000';
        $td->color = 'color';
        $td->campoTxt = 'color';
        $td->title = 'porcentaje';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;" data-backdrop="false">
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel"> REPORTE DE SEMAFORIZACIÓN DE METAS</h4>
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
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#BuscarSemaMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"llenaTablaSemaMetas",
                        ur_clave:$("#ur").val(),
                        ue_id:$("#ue").val(),
                        trimestre:$("#comboTrimestre").val(),
                        toa_id:$("#tipoact").val(),
                        oa_id:$("#actividad").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        porcentaje:$("#porcentaje").val(),
                        porcentaje2:$("#porcentaje2").val()
                    });
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#imprimirSemaMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        porcentaje:$("#porcentaje").val(),
                        porcentaje2:$("#porcentaje2").val(),
                        toa_id:$("#tipoact").val(),
                        oa_id:$("#actividad").val(),
                        ue_id:$("#ue").val(),
                        aplicacion:"PDF",
                        rep_id:109
                    });
                    $("#reportes").submit();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#exportarSemaMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        porcentaje:$("#porcentaje").val(),
                        porcentaje2:$("#porcentaje2").val(),
                        toa_id:$("#tipoact").val(),
                        oa_id:$("#actividad").val(),
                        ue_id:$("#ue").val(),
                        aplicacion:"EXCEL",
                        rep_id:109
                    });
                    $("#reportes").submit();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });
        ');
    }

    private function llenaTablaSemaMetas()
    {
        $filtro = '';
        $AND = '';
        if (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') {
            $filtro .= 'AND CONCAT(pre.gpo_id,pre.ur_id)="' . $this->prm->ur_clave . '" ';
        }

        if (isset($this->prm->ue_id) && $this->prm->ue_id != '') {
            $filtro .= 'AND pre.ue_id="' . $this->prm->ue_id . '" ';
        }

        if (isset($this->prm->prg_id) && $this->prm->prg_id != '') {
            $filtro .= 'AND pre.prg_id="' . $this->prm->prg_id . '" ';
        }

        if (isset($this->prm->spr_id) && $this->prm->spr_id != '') {
            $filtro .= 'AND pre.spr_id="' . $this->prm->spr_id . '" ';
        }
        if (isset($this->prm->toa_id) && $this->prm->toa_id != '') {
            $filtro .= 'AND toa_id="' . $this->prm->toa_id . '" ';
        }

        if (isset($this->prm->oa_id) && $this->prm->oa_id != '') {
            $filtro .= 'AND pre.oa_id="' . $this->prm->oa_id . '" ';
        }
        if (isset($this->prm->porcentaje) && $this->prm->porcentaje != '' && isset($this->prm->porcentaje2) && $this->prm->porcentaje2 != '') {
            $filtro .= 'AND IFNULL(ROUND(((mact_alcanzado / mact_modificado) * 100),2),0.00) BETWEEN ' . $this->prm->porcentaje . ' AND ' . $this->prm->porcentaje2 . '';
        }
        $trim = (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? $this->prm->trimestre : '1';

        $this->conectar();

        $this->setSql('
            SELECT
                CONCAT(pre.gpo_id,pre.ur_id) AS ur,cat_UnidadResponsable.ur_nombre AS desEntidad,
                CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
                CAST(pre.prg_id AS CHAR) AS prg_id,cat_Programa.prg_nombre AS despgma,
                CAST(pre.spr_id AS CHAR) AS spr_id,cat_Subprograma.spr_nombre AS desspgma,
                CAST(pre.py_id AS CHAR) AS py_id,cat_Proyecto.py_nombre AS desproyecto,
                CAST(pre.oa_id AS CHAR) AS obraaccion,cat_ObraActividad.oa_nombre AS desObra,
                toa_nombre,pre.vigente,
                ind_id AS indicador, pii_id AS meta,mact_original AS programada,mact_modificado AS actualizada,mact_alcanzado AS alcanzada,
                ((mact_alcanzado/mact_modificado)*100) AS porcentaje,tri_id AS trimestre,
                case
                WHEN ((mact_alcanzado/mact_modificado)*100)  <= 0 THEN
                    "d9534f"
                WHEN ((mact_alcanzado/mact_modificado)*100)>= 01  AND ((mact_alcanzado/mact_modificado)*100)<= 59 THEN
                    "d9534f"
                WHEN ((mact_alcanzado/mact_modificado)*100)>=60  AND ((mact_alcanzado/mact_modificado)*100)<= 80  THEN
                    "ff9900"
                WHEN ((mact_alcanzado/mact_modificado)*100)>=81  AND ((mact_alcanzado/mact_modificado)*100)<= 100  THEN
                    "5cb85c"
                WHEN ((mact_alcanzado/mact_modificado)*100)>100   THEN
                    "d9534f"
                else "d9534f"
                end as color
            FROM
            (
                SELECT gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,epg_id,pre_vigente AS vigente
                FROM presupuesto
                JOIN presupuestoAprobado AS apr USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoVigente AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                WHERE tcp_id<>"P" AND (pre_aprobado+pre_vigente)<>0
                GROUP BY gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id
            ) AS pre
            INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            INNER JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
            INNER JOIN cat_Programa USING (gpo_id,ur_id,prg_id)
            INNER JOIN cat_Subprograma USING (gpo_id,ur_id,prg_id,spr_id)
            INNER JOIN cat_Proyecto USING (gpo_id,ur_id,prg_id,spr_id,py_id)
            INNER JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            INNER JOIN cat_TipoObraActividad USING (toa_id)
            INNER JOIN cat_MetaActividadTrimestre mat ON pre.epg_id=mat.epg_id
            INNER JOIN vin_ProductoIntermedioIndicador USING (pii_id)
            INNER JOIN cat_Indicador USING (nim_id,ind_id)
            WHERE
                mat.tri_id=' . $trim . '
                ' . $filtro . '
            GROUP BY gpo_id,ur_id,prg_id,spr_id,py_id,oa_id
            HAVING (programada > 0 || actualizada >0 || alcanzada>0)
            ORDER BY gpo_id,ur_id,ue_id,prg_id,spr_id,oa_id
        ');

        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ProgramaSemaMeta';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }

    private function verPantallaObsMetas()
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
        $comboTrimestre->width = '400px';
        $comboTrimestre->selecciona = (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? $this->prm->trimestre : 1;
        $comboTrimestre->search = true;

        $sql = 'SELECT toa_id, toa_nombre FROM cat_TipoObraActividad WHERE toa_id not LIKE "3";';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();
        $tipos_act = ['' => $this->opciones];
        while ($row = $this->extraer()) {
            $tipos_act[$row['toa_id']] = $row['toa_nombre'];
        }

        $comboTipoAct = new stdClass();
        $comboTipoAct->id = 'comboTipoAct';
        $comboTipoAct->opciones = $tipos_act;
        $comboTipoAct->label = 'Tipo Actividad';
        $comboTipoAct->selecciona = (isset($this->prm->toa_id) && $this->prm->toa_id != '') ? $this->prm->toa_id : 1;

        $ur = new stdClass();
        $ur->id = 'ur';
        $ur->label = 'UR';
        $ur->maxlength = 3;
        $ur->formato = 'entero';
        $ur->value = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ur_clave : '';

        $ue = new stdClass();
        $ue->id = 'ue';
        $ue->label = 'UE';
        $ue->maxlength = 2;
        $ue->formato = 'entero';
        $ue->value = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ue_id : '';

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

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';
        $actividad->value = (isset($this->prm->spr_id) && $this->prm->spr_id != '') ? $this->prm->spr_id : '';

        $porcentaje = new stdClass();
        $porcentaje->id = 'porcentaje';
        $porcentaje->formato = 'numero';
        $porcentaje->value = (isset($this->prm->porcentaje) && $this->prm->porcentaje != '') ? $this->prm->porcentaje : '';

        $porcentaje2 = new stdClass();
        $porcentaje2->id = 'porcentaje2';
        $porcentaje2->formato = 'numero';
        $porcentaje2->value = (isset($this->prm->porcentaje2) && $this->prm->porcentaje2 != '') ? $this->prm->porcentaje2 : '';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'BuscarObserMeta';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar1 = new stdClass();
        $exportar1->id = 'exportarObserMeta';
        $exportar1->value = $this->tituloE;
        $exportar1->type = 'submit';
        $exportar1->icon = $this->iconD;
        $exportar1->title = $this->tituloEx;
        $exportar1->class = $this->claseIn;

        $imprimir1 = new stdClass();
        $imprimir1->id = 'imprimirObserMeta';
        $imprimir1->value = $this->tituloP;
        $imprimir1->type = 'submit';
        $imprimir1->icon = $this->iconoPdf;
        $imprimir1->title = $this->tituloPdf;
        $imprimir1->class = $this->claseSecXs;

        $tools = '
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->templateCombo($comboTrimestre) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($ur) . '
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
                    ' . $this->ponInput($actividad) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboTipoAct) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    <label>Porcentaje</label><br/>
                    DEL ' . $this->ponInput($porcentaje) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    <label></label><br/>
                    AL ' . $this->ponInput($porcentaje2) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar1) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($imprimir1) . '
                </fieldset>
            </div><br/>
        ';

        $tabla = new stdClass();
        $tabla->titulo = 'REPORTE DE OBSERVACIONES ';
        $tabla->id = 'tbl-ObseMeta';
        $tabla->tools = $tools;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';
        $tabla->comunica->trimestre = '{$("#comboTrimestre").val()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE UR';
        $th->width = '5px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD RESPONSABLE';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD EJECUTORA';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'SUBPROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROYECTO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NUM ACT/OBRA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'OBRA/ACTIVIDAD DEFINICIÓN';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NÚM. IND.';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'TIPO DE ACTIVIDAD ';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NÚM. META';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META PROGRAMADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META ACTUALIZADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META ALCANZADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = '% ALCANZADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'TRIMESTRE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'OBSERVACIÓN';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'FECHA DE LA OBSERVACIÓN';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'HORA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'HISTORIAL DE OBSERVACIÓN POR META';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'ur_clave';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ur_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ue_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'prg_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'spr_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'py_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'oa_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'oa_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ind_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'toa_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'meta';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'programada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'actualizada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'alcanzada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'alcanzado';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'hms_trimestre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'hms_mensaje';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'fecha';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'hora';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'hms_mensajeRespuesta';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel"> REPORTE DE OBSERVACIONES </h4>
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
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#BuscarObserMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"llenaTablaObsMetas",
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        ue_id:$("#ue").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        toa_id:$("#comboTipoAct").val(),
                        oa_id:$("#actividad").val(),
                        porcentaje:$("#porcentaje").val(),
                        porcentaje2:$("#porcentaje2").val()
                    });
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#imprimirObserMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                var form = $("<form>")
                    .attr("action", "rep/doc/poa/seguimiento/rep_ObservarMetas.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "trimestre").val( $("#comboTrimestre").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "ur_clave").val( $("#ur").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "ue_id").val( $("#ue").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "prg_id").val( $("#programa").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "spr_id").val( $("#subprograma").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "oa_id").val( $("#actividad").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "toa_id").val( $("#tipoact").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "porcentaje").val( $("#porcentaje").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "porcentaje2").val( $("#porcentaje2").val() ))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#exportarObserMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                    var form = $("<form>")
                        .attr("action", "rep/excel/poa/Seguimiento/rep_ExportaObservarMetas.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "trimestre").val( $("#comboTrimestre").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "ur_clave").val( $("#ur").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "ue_id").val( $("#ue").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "prg_id").val( $("#programa").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "spr_id").val( $("#subprograma").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "oa_id").val( $("#actividad").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "toa_id").val( $("#tipoact").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "porcentaje").val( $("#porcentaje").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "porcentaje2").val( $("#porcentaje2").val() ))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });
        ');
    }
    private function llenaTablaObsMetas()
    {
        $ur = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? 'AND CONCAT(gpo_id,ur_id)=' . $this->prm->ur_clave : '';
        $ue = (isset($this->prm->ue_id) && $this->prm->ue_id != '') ? ' AND ue_id=' . $this->prm->ue_id : '';
        $prg = (isset($this->prm->prg_id) && $this->prm->prg_id != '') ? ' AND prg_id=' . $this->prm->prg_id : '';
        $spr = (isset($this->prm->spr_id) && $this->prm->spr_id != '') ? ' AND spr_id=' . $this->prm->spr_id : '';
        $oa = (isset($this->prm->oa_id) && $this->prm->oa_id != '') ? ' AND oa_id=' . $this->prm->oa_id : '';
        $tri_id = (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? ' AND tri_id=' . $this->prm->trimestre : '';
        $trim = (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? ' AND hms_trimestre=' . $this->prm->trimestre : '';
        $porc2 = (isset($this->prm->porcentaje) && $this->prm->porcentaje != '' && isset($this->prm->porcentaje2) && $this->prm->porcentaje2 == '') ? $this->prm->porcentaje : $this->prm->porcentaje2;
        $porcentaje = (isset($this->prm->porcentaje) && $this->prm->porcentaje != '') ? ' AND ROUND((mact_alcanzado/mact_modificado)*100,2) BETWEEN ' . $this->prm->porcentaje . ' AND ' . $porc2 : '';
        $toa = (isset($this->prm->toa_id) && $this->prm->toa_id != '') ? ' AND toa_id=' . $this->prm->toa_id : '';
        $sql = '
            SELECT CONCAT(gpo_id,ur_id) AS ur_clave,ur_nombre,ue_id,prg_id,spr_id,py_id,oa_id,oa_nombre,
                CONCAT(ci.nim_id,"-",ci.ind_id) AS ind_id,toa_nombre, m.pii_id AS meta,
                mact_original as programada,
                mact_modificado as actualizada,
                mact_alcanzado as alcanzada,
                IF(mact_alcanzado !=0 , ROUND((mact_alcanzado/mact_modificado)*100,2), 0.00) as alcanzado,
                hms_trimestre,
                hms_mensaje,
                DATE(chms.usr_fecha) AS fecha, TIME(chms.usr_fecha) as hora,
                hms_mensajeRespuesta
            FROM cat_MetaActividad m
            INNER JOIN cat_MetaActividadTrimestre mt using(pii_id, epg_id)
            INNER JOIN enc_Programatico as e using(epg_id)
            INNER JOIN cat_ObraActividad using(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            INNER JOIN cat_TipoObraActividad USING (toa_id)
            INNER JOIN vin_ProductoIntermedioIndicador pii USING(pii_id)
            INNER JOIN cat_Indicador ci USING(nim_id,ind_id)
            INNER JOIN cat_UnidadResponsable ur using(gpo_id,ur_id)
            INNER JOIN cat_MensajeSeguimiento cms USING(epg_id,gpo_id,ur_id,nim_id,pii_id)
            INNER JOIN cat_HistorialMensajeSeguimiento chms USING (msj_id)
            WHERE chms.tim_id = 1  AND IFNULL(hms_mensaje,"")!=""
                ' . $ur . $ue . $prg . $spr . $toa . $oa . $trim . $tri_id . $porcentaje . '
            GROUP BY cms.msj_id,hms_id
            ORDER BY gpo_id,ur_id,ind_id,oa_id;
        ';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ObseMeta';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }

    /* Castro @Jordy 6-6-2018 EN ESTE APARTADO SE PONDRAN LOS METODOS CORRESPONDIENTES AL TICKET 227699*/
    private function verPantallaSemaforizacionPresupuestoTransparencia()
    {
        //Se pone este nombre a la función verPantallaSemaforizacionPresupuestoTransparencia() porque el nombre del archivo es
        //semaforizacion primer trimestre_presupuesto.rpt, para que haya cierta relación entre el nombre y el tema.
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
        $comboTrimestre->width = '400px';
        $comboTrimestre->selecciona = 1;
        $comboTrimestre->search = true;

        $comboTipoRec = new stdClass();
        $comboTipoRec->id = 'comboTipoRec';
        $comboTipoRec->opciones = [
            'N' => 'TODOS',
            'A' => $this->noEtiquet,
            'B' => 'ETIQUETADO',
        ];
        $comboTipoRec->label = 'Tipo de Recurso';
        $comboTipoRec->style = 'display:inline !important';

        $ur = new stdClass();
        $ur->id = 'ur';
        $ur->label = 'UR';
        $ur->maxlength = 3;
        $ur->formato = 'entero';
        $ur->value = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ur_clave : '';

        $ue = new stdClass();
        $ue->id = 'ue';
        $ue->label = 'UE';
        $ue->maxlength = 3;
        $ue->formato = 'entero';
        $ue->value = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ue_id : '';

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

        $proyecto = new stdClass();
        $proyecto->id = 'proyecto';
        $proyecto->label = 'Proyecto';
        $proyecto->maxlength = 3;
        $proyecto->formato = 'entero';
        $proyecto->value = (isset($this->prm->py_id) && $this->prm->py_id != '') ? $this->prm->py_id : '';

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';
        $actividad->value = (isset($this->prm->spr_id) && $this->prm->oa_id != '') ? $this->prm->oa_id : '';

        $porcentaje = new stdClass();
        $porcentaje->id = 'porcentaje';
        $porcentaje->formato = 'numero';
        $porcentaje->value = (isset($this->prm->porcentaje) && $this->prm->porcentaje != '') ? $this->prm->porcentaje : '';

        $porcentaje2 = new stdClass();
        $porcentaje2->id = 'porcentaje2';
        $porcentaje2->formato = 'numero';
        $porcentaje2->value = (isset($this->prm->porcentaje2) && $this->prm->porcentaje2 != '') ? $this->prm->porcentaje2 : '';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'BuscarSemaMeta';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar1 = new stdClass();
        $exportar1->id = 'exportarSemaMeta';
        $exportar1->value = $this->tituloE;
        $exportar1->type = 'submit';
        $exportar1->icon = $this->iconD;
        $exportar1->title = $this->tituloEx;
        $exportar1->class = $this->claseIn;

        $imprimir1 = new stdClass();
        $imprimir1->id = 'imprimirSemaMeta';
        $imprimir1->value = $this->tituloP;
        $imprimir1->type = 'submit';
        $imprimir1->icon = $this->iconoPdf;
        $imprimir1->title = $this->tituloPdf;
        $imprimir1->class = $this->claseSecXs;

        $tools = '
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->templateCombo($comboTrimestre) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboTipoRec) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($ur) . '
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
                    <label>Porcentaje</label><br/>
                    DEL ' . $this->ponInput($porcentaje) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    <label></label><br/>
                    AL ' . $this->ponInput($porcentaje2) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar1) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($imprimir1) . '
                </fieldset>
            </div><br/>
        ';

        $tabla = new stdClass();
        $tabla->titulo = 'REPORTE DE SEMAFORIZACIÓN CON PRESUPUESTO';
        $tabla->id = 'tbl-ProgramaSemaMetaTrans';
        $tabla->tools = $tools;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';
        $tabla->comunica->trimestre = '{$("#comboTrimestre").val()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';
        $tabla->comunica->py_id = '{$("#proyecto").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE UR';
        $th->width = '5px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD RESPONSABLE';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD EJECUTORA';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'SUBPROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROYECTO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NUM ACT/OBRA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ACT/ OBRA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NÚM IND.';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'TIPO DE ACTIVIDAD';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NÚM. META';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META PROGRAMADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META ACTUALIZADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META ALCANZADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = '% ALCANZADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'TRIMESTRE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'APROBADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'MODIFICADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'DEVENGADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'EJERCIDO';
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
        $td->campoTxt = 'ue_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'prg_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'spr_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'py_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'obraaccion';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'desObra';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'indicador';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'toa_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'meta';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'programada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'actualizada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'alcanzada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'porcentaje';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'trimestre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'aprobado';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'vigente';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'devengado';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ejercido';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'center';
        $td->tipo = 'bolita';
        $td->linea = '000000';
        $td->color = 'color';
        $td->campoTxt = 'color';
        $td->title = 'porcentaje';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;" data-backdrop="false">
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel"> REPORTE DE SEMAFORIZACIÓN CON PRESUPUESTO</h4>
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
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#BuscarSemaMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"llenaTablaSemPreTrans",
                        ur_clave:$("#ur").val(),
                        ter_id:$("#comboTipoRec").val(),
                        ue_id:$("#ue").val(),
                        trimestre:$("#comboTrimestre").val(),
                        oa_id:$("#actividad").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        porcentaje:$("#porcentaje").val(),
                        porcentaje2:$("#porcentaje2").val()
                    });
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#imprimirSemaMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        ter_id:$("#comboTipoRec").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        porcentaje:$("#porcentaje").val(),
                        porcentaje2:$("#porcentaje2").val(),
                        oa_id:$("#actividad").val(),
                        ue_id:$("#ue").val(),
                        aplicacion:"PDF",
                        rep_id:114
                    });
                    $("#reportes").submit();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#exportarSemaMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        ter_id:$("#comboTipoRec").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        porcentaje:$("#porcentaje").val(),
                        porcentaje2:$("#porcentaje2").val(),
                        oa_id:$("#actividad").val(),
                        ue_id:$("#ue").val(),
                        aplicacion:"EXCEL",
                        rep_id:114
                    });
                    $("#reportes").submit();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });
        ');
    }

    private function verPantallaSemaforizacionPresupuestoTransparenciaExcel()
    {
        //Se pone este nombre a la función verPantallaSemaforizacionPresupuestoTransparencia() porque el nombre del archivo es
        //semaforizacion primer trimestre_presupuesto.rpt, para que haya cierta relación entre el nombre y el tema.
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
        $comboTrimestre->width = '400px';
        $comboTrimestre->selecciona = 1;
        $comboTrimestre->search = true;

        $comboTipoRec = new stdClass();
        $comboTipoRec->id = 'comboTipoRec';
        $comboTipoRec->opciones = [
            'N' => 'TODOS',
            'A' => $this->noEtiquet,
            'B' => 'ETIQUETADO',
        ];
        $comboTipoRec->label = 'Tipo de Recurso';
        $comboTipoRec->style = 'display:inline !important';

        $ur = new stdClass();
        $ur->id = 'ur';
        $ur->label = 'UR';
        $ur->maxlength = 3;
        $ur->formato = 'entero';
        $ur->value = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ur_clave : '';

        $ue = new stdClass();
        $ue->id = 'ue';
        $ue->label = 'UE';
        $ue->maxlength = 3;
        $ue->formato = 'entero';
        $ue->value = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ue_id : '';

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

        $proyecto = new stdClass();
        $proyecto->id = 'proyecto';
        $proyecto->label = 'Proyecto';
        $proyecto->maxlength = 3;
        $proyecto->formato = 'entero';
        $proyecto->value = (isset($this->prm->py_id) && $this->prm->py_id != '') ? $this->prm->py_id : '';

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';
        $actividad->value = (isset($this->prm->spr_id) && $this->prm->oa_id != '') ? $this->prm->oa_id : '';

        $tipoact = new stdClass();
        $tipoact->id = 'tipoact';
        $tipoact->label = 'Tipo Actividad ';
        $tipoact->maxlength = 3;
        $tipoact->formato = 'entero';
        $tipoact->value = (isset($this->prm->toa_id) && $this->prm->toa_id != '') ? $this->prm->toa_id : '';

        $porcentaje = new stdClass();
        $porcentaje->id = 'porcentaje';
        $porcentaje->formato = 'numero';
        $porcentaje->value = (isset($this->prm->porcentaje) && $this->prm->porcentaje != '') ? $this->prm->porcentaje : '';

        $porcentaje2 = new stdClass();
        $porcentaje2->id = 'porcentaje2';
        $porcentaje2->formato = 'numero';
        $porcentaje2->value = (isset($this->prm->porcentaje2) && $this->prm->porcentaje2 != '') ? $this->prm->porcentaje2 : '';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'BuscarSemaMeta';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar1 = new stdClass();
        $exportar1->id = 'exportarSemaMeta';
        $exportar1->value = $this->tituloE;
        $exportar1->type = 'submit';
        $exportar1->icon = $this->iconD;
        $exportar1->title = $this->tituloEx;
        $exportar1->class = $this->claseIn;

        $imprimir1 = new stdClass();
        $imprimir1->id = 'imprimirSemaMeta';
        $imprimir1->value = $this->tituloP;
        $imprimir1->type = 'submit';
        $imprimir1->icon = $this->iconoPdf;
        $imprimir1->title = $this->tituloPdf;
        $imprimir1->class = $this->claseSecXs;

        $tools = '
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->templateCombo($comboTrimestre) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboTipoRec) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($ur) . '
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
                    <label>Porcentaje</label><br/>
                    DEL ' . $this->ponInput($porcentaje) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    <label></label><br/>
                    AL ' . $this->ponInput($porcentaje2) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar1) . '
                </fieldset>
            </div><br/>
        ';

        $tabla = new stdClass();
        $tabla->titulo = 'REPORTE DE SEMAFORIZACIÓN CON PRESUPUESTO (EXCEL)';
        $tabla->id = 'tbl-ProgramaSemaMetaTrans';
        $tabla->tools = $tools;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';
        $tabla->comunica->trimestre = '{$("#comboTrimestre").val()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';
        $tabla->comunica->py_id = '{$("#proyecto").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE UR';
        $th->width = '5px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD RESPONSABLE';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD EJECUTORA';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'SUBPROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROYECTO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NUM ACT/OBRA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ACT/ OBRA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NÚM IND.';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'TIPO DE ACTIVIDAD';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NÚM. META';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META PROGRAMADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META ACTUALIZADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META ALCANZADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = '% ALCANZADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'TRIMESTRE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'APROBADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'MODIFICADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'DEVENGADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'EJERCIDO';
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
        $td->campoTxt = 'ue_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'prg_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'spr_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'py_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'obraaccion';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'desObra';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'indicador';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'toa_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'meta';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'programada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'actualizada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'alcanzada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'porcentaje';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'trimestre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'aprobado';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'vigente';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'devengado';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ejercido';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->align = 'center';
        $td->tipo = 'bolita';
        $td->linea = '000000';
        $td->color = 'color';
        $td->campoTxt = 'color';
        $td->title = 'porcentaje';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;" data-backdrop="false">
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel"> REPORTE DE SEMAFORIZACIÓN CON PRESUPUESTO (EXCEL)</h4>
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
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#BuscarSemaMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"llenaTablaSemPreTrans",
                        ur_clave:$("#ur").val(),
                        ter_id:$("#comboTipoRec").val(),
                        ue_id:$("#ue").val(),
                        trimestre:$("#comboTrimestre").val(),
                        toa_id:$("#tipoact").val(),
                        oa_id:$("#actividad").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        porcentaje:$("#porcentaje").val(),
                        porcentaje2:$("#porcentaje2").val()
                    });
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#exportarSemaMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                        var form = $("<form>")
                            .attr("action", "rep/excel/poa/Seguimiento/semMetasAct.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "trimestre").val( $("#comboTrimestre").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "ter_id").val( $("#comboTipoRec").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "ur_clave").val( $("#ur").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "ue_id").val( $("#ue").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "prg_id").val( $("#programa").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "spr_id").val( $("#subprograma").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "py_id").val( $("#proyecto").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "oa_id").val( $("#actividad").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "porcentaje").val( $("#porcentaje").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "porcentaje2").val( $("#porcentaje2").val() ))
                            $("body").append(form);
                    form.submit();
                    form.remove();
                }else{
                    notify("Seleccione un trimestre","error");
                }
            });
        ');
    }

    private function verPantallaMetasProgramadas()
    {
        //Se pone este nombre a la función verPantallaSemaforizacionPresupuestoTransparencia() porque el nombre del archivo es
        //semaforizacion primer trimestre_presupuesto.rpt, para que haya cierta relación entre el nombre y el tema.
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
        $comboTrimestre->width = '400px';
        $comboTrimestre->selecciona = 1;
        $comboTrimestre->search = true;

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'BuscarTrimMeta';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar1 = new stdClass();
        $exportar1->id = 'exportartrimMetas';
        $exportar1->value = $this->tituloE;
        $exportar1->type = 'submit';
        $exportar1->icon = $this->iconD;
        $exportar1->title = $this->tituloEx;
        $exportar1->class = $this->claseIn;

        $tools = '
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->templateCombo($comboTrimestre) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar1) . '
                </fieldset>
            </div><br/>
        ';

        $tabla = new stdClass();
        $tabla->titulo = 'REPORTE DE METAS PROGRAMADAS';
        $tabla->id = 'tbl-llenaTablaTrimMetas';
        $tabla->tools = $tools;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';
        $tabla->comunica->trimestre = '{$("#comboTrimestre").val()}';
        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE UR';
        $th->width = '5px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD RESPONSABLE';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META PROGRAMADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META ACTUALIZADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META ALCANZADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'APROBADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'MODIFICADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'DEVENGADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'EJERCIDO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'COMPROMETIDO';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'desEntidad';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'programada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'actualizada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'alcanzada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'aprobado';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'vigente';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'devengado';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ejercido';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'comprometido';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;" data-backdrop="false">
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel"> REPORTE DE METAS PROGRAMADAS</h4>
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
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#BuscarTrimMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"llenaTablaTrimMetas",
                        trimestre:$("#comboTrimestre").val()
                    });
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#exportartrimMetas").click(function(){
                if($("#comboTrimestre").val() != ""){
                        var form = $("<form>")
                            .attr("action", "rep/excel/poa/Seguimiento/metasProg.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "trimestre").val( $("#comboTrimestre").val() ))
                            $("body").append(form);
                    form.submit();
                    form.remove();
                }else{
                    notify("Seleccione un trimestre","error");
                }
            });
        ');
    }

    private function llenaTablaSemPreTrans()
    {
        $porcentaje = '';
        $AND = '';
        $ur_clave = '';
        $prg_id = '';
        $spr_id = '';
        $py_id = '';
        $oa_id = '';
        $ter_id = '';
        $ue_id = '';
        $trimestre = '';
        $porcentaje = '';

        if (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') {
            $ur_clave .= 'AND CONCAT(gpo_id,ur_id)="' . $this->prm->ur_clave . '" ';
        }

        if (isset($this->prm->ue_id) && $this->prm->ue_id != '') {
            $ue_id .= 'AND ue_id=' . $this->prm->ue_id . ' ';
        }

        if (isset($this->prm->prg_id) && $this->prm->prg_id != '') {
            $prg_id .= 'AND prg_id=' . $this->prm->prg_id . ' ';
        }

        if (isset($this->prm->spr_id) && $this->prm->spr_id != '') {
            $spr_id .= 'AND spr_id=' . $this->prm->spr_id . ' ';
        }

        if (isset($this->prm->py_id) && $this->prm->py_id != '') {
            $py_id .= 'AND py_id=' . $this->prm->py_id . ' ';
        }

        if (isset($this->prm->ter_id) && $this->prm->ter_id != '') {
            if ($this->prm->ter_id != 'N') {
                $ter_id .= ' AND ter_id = "' . $this->prm->ter_id . '"';
            }
        }

        if (isset($this->prm->oa_id) && $this->prm->oa_id != '') {
            $oa_id .= 'AND oa_id=' . $this->prm->oa_id . ' ';
        }

        if (isset($this->prm->porcentaje) && $this->prm->porcentaje != '' && isset($this->prm->porcentaje2) && $this->prm->porcentaje2 != '') {
            $porcentaje .= 'AND IFNULL(ROUND(((mact_alcanzado / mact_modificado) * 100),2),0.00) BETWEEN ' . $this->prm->porcentaje . ' AND ' . $this->prm->porcentaje2 . '';
        }

        $trim = (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? $this->prm->trimestre : '1';

        if (isset($this->prm->trimestre) && $this->prm->trimestre != '') {
            switch ($this->prm->trimestre) {
                case 1:
                    $trimestre .= '
                        SUM(apr_ene+apr_feb+apr_mar) AS aprobado,
                        SUM(vig_ene+vig_feb+vig_mar) AS vigente,
                        SUM(dev_ene+dev_feb+dev_mar) AS devengado,
                        SUM(eje_ene+eje_feb+eje_mar) AS ejercido
                    ';
                    break;
                case 2:
                    $trimestre .= '
                        SUM(apr_abr+apr_may+apr_jun) AS aprobado,
                        SUM(vig_abr+vig_may+vig_jun) AS vigente,
                        SUM(dev_abr+dev_may+dev_jun) AS devengado,
                        SUM(eje_abr+eje_may+eje_jun) AS ejercido
                    ';
                    break;
                case 3:
                    $trimestre .= '
                        SUM(apr_jul+apr_ago+apr_sep) AS aprobado,
                        SUM(vig_jul+vig_ago+vig_sep) AS vigente,
                        SUM(dev_jul+dev_ago+dev_sep) AS devengado,
                        SUM(eje_jul+eje_ago+eje_sep) AS ejercido
                    ';
                    break;
                case 4:
                    $trimestre .= '
                        SUM(apr_oct+apr_nov+apr_dic) AS aprobado,
                        SUM(vig_oct+vig_nov+vig_dic) AS vigente,
                        SUM(dev_oct+dev_nov+dev_dic) AS devengado,
                        SUM(eje_oct+eje_nov+eje_dic) AS ejercido
                    ';
                    break;
                default:
                    break;
            }
        }

        $this->conectar();
        $this->setSql('
            SELECT pre.epg_id,CONCAT(pre.gpo_id,pre.ur_id) AS ur,ur_nombre AS desEntidad,
            CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
            CAST(pre.prg_id AS CHAR) AS prg_id, prg_nombre AS desPrograma,
            CAST(pre.spr_id AS CHAR) AS spr_id,spr_nombre AS desSubprograma,
            CAST(pre.py_id AS CHAR) AS py_id,py_nombre AS desProyecto,
            CAST(pre.oa_id AS CHAR) AS obraaccion,oa_nombre AS desObra,toa_nombre,
            cat_Indicador.nim_id,ind_id AS indicador, cat_MetaActividadTrimestre.pii_id AS meta,mact_original AS programada,mact_modificado AS actualizada,mact_alcanzado AS alcanzada,
            IF(mact_modificado=0,0,(mact_alcanzado*100/mact_modificado)) AS porcentaje,tri_id AS trimestre,
            ppto.aprobado,ppto.vigente,ppto.devengado,ppto.ejercido
            FROM
            (
                SELECT p.gpo_id,p.ur_id,ur_nombre,p.ue_id,ue_nombre,p.prg_id,prg_nombre,p.spr_id,spr_nombre,p.py_id,py_nombre,p.oa_id,oa_nombre,ep.epg_id,toa_nombre
                FROM cat_ObraActividad AS p
                JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
                JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
                JOIN cat_Programa USING (gpo_id,ur_id,prg_id)
                JOIN cat_Subprograma USING (gpo_id,ur_id,prg_id,spr_id)
                JOIN cat_Proyecto USING (gpo_id,ur_id,prg_id,spr_id,py_id)
                JOIN cat_TipoObraActividad  AS toa USING (toa_id)
                JOIN enc_Programatico AS ep USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                WHERE  eoa_id IN (4,5) and toa_id = 1
                ' . $ur_clave . '
                ' . $prg_id . '
                ' . $ue_id . '
                ' . $spr_id . '
                ' . $py_id . '
                ' . $oa_id . '
                GROUP BY p.gpo_id,p.ur_id,p.ue_id,p.prg_id,p.spr_id,p.py_id,p.oa_id
            ) AS pre
            LEFT JOIN (
                SELECT epg_id,
                ' . $trimestre . '
                FROM presupuesto
                JOIN presupuestoAprobado  AS apr USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoVigente   AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoDevengado AS dev USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoEjercido  AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN (
                    SELECT * FROM ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' WHERE sor_ejercicio=' . APP_EJERCICIO . '
                ) AS cso USING (cff_id,fte_id,sft_id,ori_id,sor_id)
                WHERE tcp_id<>"P"
                ' . $ur_clave . '
                ' . $prg_id . '
                ' . $spr_id . '
                ' . $py_id . '
                ' . $ter_id . '
                GROUP BY epg_id
            ) AS ppto USING (epg_id)
            LEFT JOIN cat_MetaActividadTrimestre ON pre.epg_id=cat_MetaActividadTrimestre.epg_id AND tri_id= ' . $trim . ' -- selecciona el trimestre
            LEFT JOIN vin_ProductoIntermedioIndicador USING (pii_id)
            LEFT JOIN cat_Indicador USING (nim_id,ind_id)
            WHERE  (mact_original+mact_modificado+mact_alcanzado)<>0 OR (ppto.aprobado+ppto.vigente)<>0
            ' . $porcentaje . '
            ORDER BY pre.gpo_id,ur_id,pre.prg_id,pre.spr_id,pre.py_id,pre.oa_id;
        ');
        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ProgramaSemaMetaTrans';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }

    private function llenaTablaTrimMetas()
    {

        $trimestre = '';

        $trim = (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? $this->prm->trimestre : '1';

        if (isset($this->prm->trimestre) && $this->prm->trimestre != '') {
            switch ($this->prm->trimestre) {
                case 1:
                    $trimestre .= '
                        SUM(apr_ene+apr_feb+apr_mar) AS aprobado,
                        SUM(vig_ene+vig_feb+vig_mar) AS vigente,
                        SUM(dev_ene+dev_feb+dev_mar) AS devengado,
                        SUM(eje_ene+eje_feb+eje_mar) AS ejercido,
                        SUM(com_ene + com_feb + com_mar) AS comprometido
                    ';
                    break;
                case 2:
                    $trimestre .= '
                        SUM(apr_abr+apr_may+apr_jun) AS aprobado,
                        SUM(vig_abr+vig_may+vig_jun) AS vigente,
                        SUM(dev_abr+dev_may+dev_jun) AS devengado,
                        SUM(eje_abr+eje_may+eje_jun) AS ejercido,
                        SUM(com_abr + com_may + com_jun) AS comprometido
                    ';
                    break;
                case 3:
                    $trimestre .= '
                        SUM(apr_jul+apr_ago+apr_sep) AS aprobado,
                        SUM(vig_jul+vig_ago+vig_sep) AS vigente,
                        SUM(dev_jul+dev_ago+dev_sep) AS devengado,
                        SUM(eje_jul+eje_ago+eje_sep) AS ejercido,
                        SUM(com_jul + com_ago + com_sep) AS comprometido
                    ';
                    break;
                case 4:
                    $trimestre .= '
                        SUM(apr_oct+apr_nov+apr_dic) AS aprobado,
                        SUM(vig_oct+vig_nov+vig_dic) AS vigente,
                        SUM(dev_oct+dev_nov+dev_dic) AS devengado,
                        SUM(eje_oct+eje_nov+eje_dic) AS ejercido,
                        SUM(com_oct + com_nov + com_dic) AS comprometido
                    ';
                    break;
                default:
                    break;
            }
        }

        $this->conectar();
        $this->setSql('
        SELECT CONCAT(pre.gpo_id,pre.ur_id) AS ur,ur_nombre AS desEntidad,
        SUM(mact_original) AS programada,
        SUM(mact_modificado) AS actualizada,SUM(mact_alcanzado) AS alcanzada,
        SUM(ppto.aprobado) AS aprobado,SUM(ppto.vigente) AS vigente,SUM(ppto.devengado) AS devengado,
        SUM(ppto.ejercido) AS ejercido,SUM(ppto.comprometido) AS comprometido
        FROM
            (
                SELECT p.gpo_id,p.ur_id,ur_nombre,p.ue_id,ue_nombre,p.prg_id,prg_nombre,p.spr_id,spr_nombre,p.py_id,py_nombre,p.oa_id,oa_nombre,ep.epg_id,toa_nombre
                FROM cat_ObraActividad AS p
                JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
                JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
                JOIN cat_Programa USING (gpo_id,ur_id,prg_id)
                JOIN cat_Subprograma USING (gpo_id,ur_id,prg_id,spr_id)
                JOIN cat_Proyecto USING (gpo_id,ur_id,prg_id,spr_id,py_id)
                JOIN cat_TipoObraActividad  AS toa USING (toa_id)
                JOIN enc_Programatico AS ep USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                WHERE  eoa_id IN (4,5) and toa_id = 1
                GROUP BY p.gpo_id,p.ur_id,p.ue_id,p.prg_id,p.spr_id,p.py_id,p.oa_id
            ) AS pre
            LEFT JOIN (
                SELECT epg_id,
                ' . $trimestre . '
                FROM presupuesto
                JOIN presupuestoAprobado  AS apr USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoVigente   AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoDevengado AS dev USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoEjercido  AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoComprometido as comp USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                JOIN (
                    SELECT * FROM ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' WHERE sor_ejercicio=' . APP_EJERCICIO . '
                ) AS cso USING (cff_id,fte_id,sft_id,ori_id,sor_id)
                WHERE tcp_id<>"P"
                GROUP BY epg_id
            ) AS ppto USING (epg_id)
            LEFT JOIN cat_MetaActividadTrimestre ON pre.epg_id=cat_MetaActividadTrimestre.epg_id AND tri_id= ' . $trim . ' -- selecciona el trimestre
            LEFT JOIN vin_ProductoIntermedioIndicador USING (pii_id)
            LEFT JOIN cat_Indicador USING (nim_id,ind_id)
            WHERE  (mact_original+mact_modificado+mact_alcanzado)<>0 OR (ppto.aprobado+ppto.vigente)<>0
            GROUP BY pre.gpo_id, pre.ur_id
            ORDER BY pre.gpo_id,ur_id,pre.prg_id,pre.spr_id,pre.py_id,pre.oa_id;
        ');
        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-llenaTablaTrimMetas';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }

    private function verPantallaPre7Transparencia()
    {
        /*
        @Jordy 6-6-2018 METODO DE LA PANTALLA REFERENTE AL REPORTE PRESUPUESTO UR-PROG-SUBPROGRAMA-ACTIVIDAD
        EL CUAL TIENE EL NUMERO 7
         */
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
        $comboTrimestre->width = '400px';
        $comboTrimestre->selecciona = 1;
        $comboTrimestre->search = true;

        $comboTipoRec = new stdClass();
        $comboTipoRec->id = 'comboTipoRec';
        $comboTipoRec->opciones = [
            'N' => 'TODOS',
            'A' => $this->noEtiquet,
            'B' => 'ETIQUETADO',
        ];
        $comboTipoRec->label = 'Tipo de Recurso';
        $comboTipoRec->style = 'display:inline !important';

        $ur = new stdClass();
        $ur->id = 'ur';
        $ur->label = 'UR';
        $ur->maxlength = 3;
        $ur->formato = 'entero';
        $ur->value = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ur_clave : '';

        $ue = new stdClass();
        $ue->id = 'ue';
        $ue->label = 'UE';
        $ue->maxlength = 3;
        $ue->formato = 'entero';
        $ue->value = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ue_id : '';

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

        $proyecto = new stdClass();
        $proyecto->id = 'proyecto';
        $proyecto->label = 'Proyecto';
        $proyecto->maxlength = 3;
        $proyecto->formato = 'entero';
        $proyecto->value = (isset($this->prm->py_id) && $this->prm->py_id != '') ? $this->prm->py_id : '';

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';
        $actividad->value = (isset($this->prm->spr_id) && $this->prm->oa_id != '') ? $this->prm->oa_id : '';

        $tipoact = new stdClass();
        $tipoact->id = 'tipoact';
        $tipoact->label = 'Tipo Actividad ';
        $tipoact->maxlength = 3;
        $tipoact->formato = 'entero';
        $tipoact->value = (isset($this->prm->toa_id) && $this->prm->toa_id != '') ? $this->prm->toa_id : '';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'BuscarSemaMeta';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar1 = new stdClass();
        $exportar1->id = 'exportarSemaMeta';
        $exportar1->value = $this->tituloE;
        $exportar1->type = 'submit';
        $exportar1->icon = $this->iconD;
        $exportar1->title = $this->tituloEx;
        $exportar1->class = $this->claseIn;

        $imprimir1 = new stdClass();
        $imprimir1->id = 'imprimirSemaMeta';
        $imprimir1->value = $this->tituloP;
        $imprimir1->type = 'submit';
        $imprimir1->icon = $this->iconoPdf;
        $imprimir1->title = $this->tituloPdf;
        $imprimir1->class = $this->claseSecXs;
        $tools = '
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->templateCombo($comboTrimestre) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboTipoRec) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($ur) . '
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
                    ' . $this->ponInput($tipoact) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar1) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($imprimir1) . '
                </fieldset>
            </div><br/>
        ';

        $tabla = new stdClass();
        $tabla->titulo = 'REPORTE PRESUPUESTO UR-PROG-SUBPROGRAMA-ACTIVIDAD';
        $tabla->id = 'tbl-PresupuestoSeg7Trans';
        $tabla->tools = $tools;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';
        $tabla->comunica->trimestre = '{$("#comboTrimestre").val()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';
        $tabla->comunica->py_id = '{$("#proyecto").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE UR';
        $th->width = '5px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD RESPONSABLE';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD EJECUTORA';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'SUBPROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROYECTO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NUM ACT/OBRA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ACT/ OBRA';
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
        $th = new stdClass();
        $th->texto = 'DEVENGADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'EJERCIDO';
        array_push($tr, $th);

        $trbody = [];

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
        $td->campoTxt = 'prg_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'spr_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'py_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'obraaccion';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'desObra';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'aprobado';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'modificado';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'comprometido';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'devengado';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ejercido';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;" data-backdrop="false">
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel"> REPORTE PRESUPUESTO UR-PROG-SUBPROGRAMA-ACTIVIDAD</h4>
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
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#BuscarSemaMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"llenaTablaPre7Trans",
                        ur_clave:$("#ur").val(),
                        ter_id:$("#comboTipoRec").val(),
                        ue_id:$("#ue").val(),
                        trimestre:$("#comboTrimestre").val(),
                        toa_id:$("#tipoact").val(),
                        oa_id:$("#actividad").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        porcentaje:$("#porcentaje").val(),
                        porcentaje2:$("#porcentaje2").val()
                    });
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#imprimirSemaMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        ter_id:$("#comboTipoRec").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        porcentaje:$("#porcentaje").val(),
                        porcentaje2:$("#porcentaje2").val(),
                        toa_id:$("#tipoact").val(),
                        oa_id:$("#actividad").val(),
                        ue_id:$("#ue").val(),
                        aplicacion:"PDF",
                        rep_id:116
                    });
                    $("#reportes").submit();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#exportarSemaMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ur_clave:$("#ur").val(),
                        ter_id:$("#comboTipoRec").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        porcentaje:$("#porcentaje").val(),
                        porcentaje2:$("#porcentaje2").val(),
                        toa_id:$("#tipoact").val(),
                        oa_id:$("#actividad").val(),
                        ue_id:$("#ue").val(),
                        aplicacion:"EXCEL",
                        rep_id:116
                    });
                    $("#reportes").submit();
                }else{
                    notify("Seleccione un trimestre","error");
                }
            });
        ');
    }
    private function llenaTablaPre7Trans()
    {
        $filtro = '';
        $filtro2 = '';
        $trimestre = '';
        $AND = '';
        if (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') {
            $filtro .= 'AND CONCAT(gpo_id,ur_id)=' . $this->prm->ur_clave . ' ';
        }

        if (isset($this->prm->ue_id) && $this->prm->ue_id != '') {
            $filtro .= 'AND ue_id=' . $this->prm->ue_id . ' ';
        }

        if (isset($this->prm->prg_id) && $this->prm->prg_id != '') {
            $filtro .= 'AND prg_id=' . $this->prm->prg_id . ' ';
        }

        if (isset($this->prm->py_id) && $this->prm->py_id != '') {
            $filtro .= 'AND py_id=' . $this->prm->py_id . ' ';
        }

        if (isset($this->prm->spr_id) && $this->prm->spr_id != '') {
            $filtro .= 'AND spr_id=' . $this->prm->spr_id . ' ';
        }
        if (isset($this->prm->oa_id) && $this->prm->oa_id != '') {
            $filtro .= 'AND oa_id=' . $this->prm->oa_id . ' ';
        }
        if (isset($this->prm->ter_id) && $this->prm->ter_id != '') {
            if ($this->prm->ter_id != 'N') {
                $filtro .= ' AND ter_id = "' . $this->prm->ter_id . '"';
            }
        }
        if (isset($this->prm->toa_id) && $this->prm->toa_id != '') {
            $filtro2 .= 'AND toa_id=' . $this->prm->toa_id . ' ';
        }

        if (isset($this->prm->trimestre) && $this->prm->trimestre != '') {
            switch ($this->prm->trimestre) {
                case 1:
                    $trimestre .= '
                        SUM(apr_ene+apr_feb+apr_mar) AS origina, -- sumatoria de trimestre
                        SUM(vig_ene+vig_feb+vig_mar) AS vigente, -- sumatoria de trimestre
                        SUM(com_ene+com_feb+com_mar) AS comprom, -- sumatoria de trimestre
                        SUM(dev_ene+dev_feb+dev_mar) AS devenga, -- sumatoria de trimestre
                        SUM(eje_ene+eje_feb+eje_mar) AS ejercid -- sumatoria de trimestre
                    ';
                    break;
                case 2:
                    $trimestre .= '
                        SUM(apr_abr+apr_may+apr_jun) AS origina, -- sumatoria de trimestre
                        SUM(vig_abr+vig_may+vig_jun) AS vigente, -- sumatoria de trimestre
                        SUM(com_abr+com_may+com_jun) AS comprom, -- sumatoria de trimestre
                        SUM(dev_abr+dev_may+dev_jun) AS devenga, -- sumatoria de trimestre
                        SUM(eje_abr+eje_may+eje_jun) AS ejercid -- sumatoria de trimestre
                    ';
                    break;
                case 3:
                    $trimestre .= '
                        SUM(apr_jul+apr_ago+apr_sep) AS origina, -- sumatoria de trimestre
                        SUM(vig_jul+vig_ago+vig_sep) AS vigente, -- sumatoria de trimestre
                        SUM(com_jul+com_ago+com_sep) AS comprom, -- sumatoria de trimestre
                        SUM(dev_jul+dev_ago+dev_sep) AS devenga, -- sumatoria de trimestre
                        SUM(eje_jul+eje_ago+eje_sep) AS ejercid -- sumatoria de trimestre
                    ';
                    break;
                case 4:
                    $trimestre .= '
                        SUM(apr_oct+apr_nov+apr_dic) AS origina, -- sumatoria de trimestre
                        SUM(vig_oct+vig_nov+vig_dic) AS vigente, -- sumatoria de trimestre
                        SUM(com_oct+com_nov+com_dic) AS comprom, -- sumatoria de trimestre
                        SUM(dev_oct+dev_nov+dev_dic) AS devenga, -- sumatoria de trimestre
                        SUM(eje_oct+eje_nov+eje_dic) AS ejercid -- sumatoria de trimestre
                    ';
                    break;
                default:
                    break;
            }
        }
        $trim = (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? $this->prm->trimestre : '1';

        $this->conectar();

        $this->setSql('
            SELECT
                CONCAT(pre.gpo_id,pre.ur_id) AS ur,
                cat_UnidadResponsable.ur_nombre AS desEntidad,
                CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
                CAST(pre.prg_id AS CHAR) AS prg_id,
                cat_Programa.prg_nombre AS desPrograma,
                CAST(pre.spr_id AS CHAR) AS spr_id,
                cat_Subprograma.spr_nombre AS desSubprograma,
                CAST(pre.py_id AS CHAR) AS py_id,
                cat_Proyecto.py_nombre AS desProyecto,
                CAST(pre.oa_id AS CHAR) AS obraaccion,
                cat_ObraActividad.oa_nombre AS desObra,
                pre.origina AS aprobado,
                pre.vigente AS modificado,
                pre.comprom AS comprometido,
                pre.devenga AS devengado,
                pre.ejercid AS ejercido
            FROM (
                SELECT
                    gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,
                    ' . $trimestre . '
                FROM presupuesto
                INNER JOIN presupuestoAprobado USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoVigente USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoComprometido USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoDevengado USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoEjercido USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . '  c USING (cff_id, fte_id, sft_id, ori_id, sor_id)
                INNER JOIN ' . BD_SINPRES . '.cat_TipoRecurso tr USING (ter_id)
                WHERE gpo_id IS NOT NULL
                    ' . $filtro . '
                GROUP BY gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id
            ) AS pre
            LEFT JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            LEFT JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
            LEFT JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
            LEFT JOIN cat_Subprograma USING(gpo_id,ur_id,prg_id,spr_id)
            LEFT JOIN cat_Proyecto USING(gpo_id,ur_id,prg_id,spr_id,py_id)
            LEFT JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            WHERE gpo_id IS NOT NULL
                AND (pre.origina+pre.vigente) > 0
                ' . $filtro2 . '
            ORDER BY pre.gpo_id,ur_id,pre.py_id,pre.oa_id ');
        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-PresupuestoSeg7Trans';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }

    private function verPantallaSeg8MetasTransparencia()
    {
        /*
        Castro Jordy 6-6-2018 METODO DE LA PANTALLA REFERENTE AL REPORTE DE SEGUIMIENTO A METAS PARA TRANSPARENCIA
        EL CUAL TIENE EL NUMERO 8
         */
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
        $comboTrimestre->width = '400px';
        $comboTrimestre->selecciona = 1;
        $comboTrimestre->search = true;

        $comboTipoRec = new stdClass();
        $comboTipoRec->id = 'comboTipoRec';
        $comboTipoRec->opciones = [
            'N' => 'TODOS',
            'A' => $this->noEtiquet,
            'B' => 'ETIQUETADO',
        ];
        $comboTipoRec->label = 'Tipo de Recurso';
        $comboTipoRec->style = 'display:inline !important';

        $ur = new stdClass();
        $ur->id = 'ur';
        $ur->label = 'UR';
        $ur->maxlength = 3;
        $ur->formato = 'entero';
        $ur->value = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ur_clave : '';

        $ue = new stdClass();
        $ue->id = 'ue';
        $ue->label = 'UE';
        $ue->maxlength = 3;
        $ue->formato = 'entero';
        $ue->value = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ue_id : '';

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

        $proyecto = new stdClass();
        $proyecto->id = 'proyecto';
        $proyecto->label = 'Proyecto';
        $proyecto->maxlength = 3;
        $proyecto->formato = 'entero';
        $proyecto->value = (isset($this->prm->py_id) && $this->prm->py_id != '') ? $this->prm->py_id : '';

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';
        $actividad->value = (isset($this->prm->spr_id) && $this->prm->oa_id != '') ? $this->prm->oa_id : '';

        $porcentaje = new stdClass();
        $porcentaje->id = 'porcentaje';
        $porcentaje->formato = 'numero';
        $porcentaje->value = (isset($this->prm->porcentaje) && $this->prm->porcentaje != '') ? $this->prm->porcentaje : '';

        $porcentaje2 = new stdClass();
        $porcentaje2->id = 'porcentaje2';
        $porcentaje2->formato = 'numero';
        $porcentaje2->value = (isset($this->prm->porcentaje2) && $this->prm->porcentaje2 != '') ? $this->prm->porcentaje2 : '';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'BuscarSemaMeta';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar1 = new stdClass();
        $exportar1->id = 'exportarSemaMeta';
        $exportar1->value = $this->tituloE;
        $exportar1->type = 'submit';
        $exportar1->icon = $this->iconD;
        $exportar1->title = $this->tituloEx;
        $exportar1->class = $this->claseIn;

        $imprimir1 = new stdClass();
        $imprimir1->id = 'imprimirSemaMeta';
        $imprimir1->value = $this->tituloP;
        $imprimir1->type = 'submit';
        $imprimir1->icon = $this->iconoPdf;
        $imprimir1->title = $this->tituloPdf;
        $imprimir1->class = $this->claseSecXs;

        $tools = '
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->templateCombo($comboTrimestre) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboTipoRec) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($ur) . '
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
                    <label>Porcentaje</label><br/>
                    DEL ' . $this->ponInput($porcentaje) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    <label></label><br/>
                    AL ' . $this->ponInput($porcentaje2) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar1) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($imprimir1) . '
                </fieldset>
            </div><br/>
        ';

        $tabla = new stdClass();
        $tabla->titulo = 'REPORTE DE SEGUIMIENTO A METAS PARA TRANSPARENCIA';
        //aqui
        $tabla->id = 'tbl-PresupuestoMetasTransparencia';
        $tabla->tools = $tools;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';
        $tabla->comunica->trimestre = '{$("#comboTrimestre").val()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';
        $tabla->comunica->py_id = '{$("#proyecto").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE UR';
        $th->width = '5px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD RESPONSABLE';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD EJECUTORA';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'SUBPROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROYECTO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NUM ACT/OBRA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'DEFINICIÓN';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NÚM IND.';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'TIPO DE ACTIVIDAD';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NÚM. META';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META PROGRAMADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META ACTUALIZADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META ALCANZADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = '% ALCANZADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'OBSERVACIÓN REALIZADA A LA UR';
        $th->width = '80px';
        array_push($tr, $th);

        $trbody = [];

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
        $td->campoTxt = 'prg_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'spr_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'py_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'obraaccion';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'desObra';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'indicador';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'toa_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'meta';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'programada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'actualizada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'alcanzada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'porcentaje';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'hms_mensaje';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;" data-backdrop="false">
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel"> REPORTE DE SEGUIMIENTO A METAS PARA TRANSPARENCIA</h4>
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
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#BuscarSemaMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"llenaTablaSegMetasTransparencia",
                        ur_clave:$("#ur").val(),
                        ter_id:$("#comboTipoRec").val(),
                        ue_id:$("#ue").val(),
                        trimestre:$("#comboTrimestre").val(),
                        oa_id:$("#actividad").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        porcentaje:$("#porcentaje").val(),
                        porcentaje2:$("#porcentaje2").val()
                    });
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#imprimirSemaMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ter_id:$("#comboTipoRec").val(),
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        porcentaje:$("#porcentaje").val(),
                        porcentaje2:$("#porcentaje2").val(),
                        oa_id:$("#actividad").val(),
                        ue_id:$("#ue").val(),
                        aplicacion:"PDF",
                        rep_id:115
                    });
                    $("#reportes").submit();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#exportarSemaMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"print",
                        ter_id:$("#comboTipoRec").val(),
                        ur_clave:$("#ur").val(),
                        trimestre:$("#comboTrimestre").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        porcentaje:$("#porcentaje").val(),
                        porcentaje2:$("#porcentaje2").val(),
                        oa_id:$("#actividad").val(),
                        ue_id:$("#ue").val(),
                        aplicacion:"EXCEL",
                        rep_id:115
                    });
                    $("#reportes").submit();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });
        ');
    }

    public function verPantallaSeg10MetasTransparencia()
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
        $comboTrimestre->width = '400px';
        $comboTrimestre->selecciona = 1;
        $comboTrimestre->search = true;

        $comboTipoRec = new stdClass();
        $comboTipoRec->id = 'comboTipoRec';
        $comboTipoRec->opciones = [
            'N' => 'TODOS',
            'A' => $this->noEtiquet,
            'B' => 'ETIQUETADO',
        ];
        $comboTipoRec->label = 'Tipo de Recurso';
        $comboTipoRec->style = 'display:inline !important';

        $ur = new stdClass();
        $ur->id = 'ur';
        $ur->label = 'UR';
        $ur->maxlength = 3;
        $ur->formato = 'entero';
        $ur->value = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ur_clave : '';

        $ue = new stdClass();
        $ue->id = 'ue';
        $ue->label = 'UE';
        $ue->maxlength = 3;
        $ue->formato = 'entero';
        $ue->value = (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') ? $this->prm->ue_id : '';

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

        $proyecto = new stdClass();
        $proyecto->id = 'proyecto';
        $proyecto->label = 'Proyecto';
        $proyecto->maxlength = 3;
        $proyecto->formato = 'entero';
        $proyecto->value = (isset($this->prm->py_id) && $this->prm->py_id != '') ? $this->prm->py_id : '';

        $actividad = new stdClass();
        $actividad->id = 'actividad';
        $actividad->label = 'Actividad';
        $actividad->maxlength = 3;
        $actividad->formato = 'entero';
        $actividad->value = (isset($this->prm->spr_id) && $this->prm->oa_id != '') ? $this->prm->oa_id : '';

        $porcentaje = new stdClass();
        $porcentaje->id = 'porcentaje';
        $porcentaje->formato = 'numero';
        $porcentaje->value = (isset($this->prm->porcentaje) && $this->prm->porcentaje != '') ? $this->prm->porcentaje : '';

        $porcentaje2 = new stdClass();
        $porcentaje2->id = 'porcentaje2';
        $porcentaje2->formato = 'numero';
        $porcentaje2->value = (isset($this->prm->porcentaje2) && $this->prm->porcentaje2 != '') ? $this->prm->porcentaje2 : '';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'BuscarSemaMeta';
        $btnBuscar->type = 'button';
        $btnBuscar->title = 'Buscar';
        $btnBuscar->value = 'Buscar';
        $btnBuscar->class = $this->claseSec;
        $btnBuscar->icon = 'fa fa-search me-2';

        $exportar1 = new stdClass();
        $exportar1->id = 'exportarSemaMeta';
        $exportar1->value = $this->tituloE;
        $exportar1->type = 'submit';
        $exportar1->icon = $this->iconD;
        $exportar1->title = $this->tituloEx;
        $exportar1->class = $this->claseIn;

        $imprimir1 = new stdClass();
        $imprimir1->id = 'imprimirSemaMeta';
        $imprimir1->value = $this->tituloP;
        $imprimir1->type = 'submit';
        $imprimir1->icon = $this->iconoPdf;
        $imprimir1->title = $this->tituloPdf;
        $imprimir1->class = $this->claseSecXs;

        $tools = '
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->templateCombo($comboTrimestre) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponCombo($comboTipoRec) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponInput($ur) . '
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
                    <label>Porcentaje</label><br/>
                    DEL ' . $this->ponInput($porcentaje) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    <label></label><br/>
                    AL ' . $this->ponInput($porcentaje2) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
                <fieldset class="col-md-1 col-xs-1">
                    ' . $this->ponBoton($exportar1) . '
                </fieldset>
            </div><br/>
        ';

        $tabla = new stdClass();
        $tabla->titulo = 'REPORTE DE SEGUIMIENTO A METAS PARA TRANSPARENCIA';
        $tabla->id = 'tbl-PresupuestoMetasTransparencia';
        $tabla->tools = $tools;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'poa/seguimiento/ControladorReportesVarios.php';
        $tabla->comunica->trimestre = '{$("#comboTrimestre").val()}';
        $tabla->comunica->ur_clave = '{$("#ur").val()}';
        $tabla->comunica->prg_id = '{$("#programa").val()}';
        $tabla->comunica->spr_id = '{$("#subprograma").val()}';
        $tabla->comunica->py_id = '{$("#proyecto").val()}';

        $tr = [];

        $th = new stdClass();
        $th->texto = 'CLAVE UR';
        $th->width = '5px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD RESPONSABLE';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UNIDAD EJECUTORA';
        $th->width = '60px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'SUBPROGRAMA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PROYECTO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NUM ACT/OBRA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'DEFINICIÓN';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NÚM IND.';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'TIPO DE ACTIVIDAD';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NÚM. META';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META PROGRAMADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META ACTUALIZADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'META ALCANZADA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = '% ALCANZADO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'OBSERVACIÓN REALIZADA A LA UR';
        $th->width = '80px';
        array_push($tr, $th);

        $trbody = [];

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
        $td->campoTxt = 'prg_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'spr_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'py_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'obraaccion';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'desObra';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'indicador';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'toa_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'meta';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'programada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'actualizada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'alcanzada';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'porcentaje';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'hms_mensaje';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;" data-backdrop="false">
                <div class="modal-dialog" style="max-width: 1500px !IMPORTANT;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel"> REPORTE DE SEGUIMIENTO A METAS PARA TRANSPARENCIA (EXCEL)</h4>
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
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal("hide");
                $("#myModal").remove();
            });

            $("#BuscarSemaMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                    comunica("poa/seguimiento/ControladorReportesVarios.php",{
                        metodo:"llenaTablaSegMetasTransparencia",
                        ur_clave:$("#ur").val(),
                        ter_id:$("#comboTipoRec").val(),
                        ue_id:$("#ue").val(),
                        trimestre:$("#comboTrimestre").val(),
                        oa_id:$("#actividad").val(),
                        prg_id:$("#programa").val(),
                        spr_id:$("#subprograma").val(),
                        py_id:$("#proyecto").val(),
                        porcentaje:$("#porcentaje").val(),
                        porcentaje2:$("#porcentaje2").val()
                    });
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });

            $("#exportarSemaMeta").click(function(){
                if($("#comboTrimestre").val() != ""){
                    var form = $("<form>")
                        .attr("action", "rep/excel/poa/Seguimiento/segMetas.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "trimestre").val( $("#comboTrimestre").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "ter_id").val( $("#comboTipoRec").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "ur_clave").val( $("#ur").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "ue_id").val( $("#ue").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "prg_id").val( $("#programa").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "spr_id").val( $("#subprograma").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "py_id").val( $("#proyecto").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "oa_id").val( $("#actividad").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "porcentaje").val( $("#porcentaje").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "porcentaje2").val( $("#porcentaje2").val() ))
                    $("body").append(form);
                    form.submit();
                    form.remove();
                } else {
                    notify("Seleccione un trimestre","error");
                }
            });
        ');
    }

    public function llenaTablaSegMetasTransparencia()
    {
        $ur_clave = '';
        $prg_id = '';
        $spr_id = '';
        $py_id = '';
        $oa_id = '';
        $ter_id = '';
        $ue_id = '';
        $trimestre = '';
        $porcentaje = '';
        $AND = '';
        if (isset($this->prm->ur_clave) && $this->prm->ur_clave != '') {
            $ur_clave .= 'AND CONCAT(gpo_id,ur_id)="' . $this->prm->ur_clave . '" ';
        }

        if (isset($this->prm->ue_id) && $this->prm->ue_id != '') {
            $ue_id .= ' AND ue_id=' . $this->prm->ue_id . ' ';
        }

        if (isset($this->prm->prg_id) && $this->prm->prg_id != '') {
            $prg_id .= ' AND prg_id=' . $this->prm->prg_id . ' ';
        }

        if (isset($this->prm->py_id) && $this->prm->py_id != '') {
            $py_id .= ' AND py_id=' . $this->prm->py_id . ' ';
        }

        if (isset($this->prm->spr_id) && $this->prm->spr_id != '') {
            $spr_id .= ' AND spr_id=' . $this->prm->spr_id . ' ';
        }
        if (isset($this->prm->oa_id) && $this->prm->oa_id != '') {
            $oa_id .= ' AND oa_id=' . $this->prm->oa_id . '';
        }

        if (isset($this->prm->ter_id) && $this->prm->ter_id != '') {
            if ($this->prm->ter_id != 'N') {
                $ter_id .= ' AND ter_id = "' . $this->prm->ter_id . '"';
            }
        }

        if (isset($this->prm->porcentaje) && $this->prm->porcentaje != '' && isset($this->prm->porcentaje2) && $this->prm->porcentaje2 != '') {
            $porcentaje .= 'AND IFNULL(ROUND(((mact_alcanzado / mact_modificado) * 100),2),0.00) BETWEEN ' . $this->prm->porcentaje . ' AND ' . $this->prm->porcentaje2 . ' ';
        }

        $trim = (isset($this->prm->trimestre) && $this->prm->trimestre != '') ? $this->prm->trimestre : '1';

        $this->conectar();
        $this->setSql('
            SELECT pre.epg_id,CONCAT(pre.gpo_id,pre.ur_id) AS ur,ur_nombre AS desEntidad,
            CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
            CAST(pre.prg_id AS CHAR) AS prg_id, prg_nombre AS desPrograma,
            CAST(pre.spr_id AS CHAR) AS spr_id,spr_nombre AS desSubprograma,
            CAST(pre.py_id AS CHAR) AS py_id,py_nombre AS desProyecto,
            CAST(pre.oa_id AS CHAR) AS obraaccion,oa_nombre AS desObra,toa_nombre,
            cat_Indicador.nim_id,ind_id AS indicador, cat_MetaActividadTrimestre.pii_id AS meta,mact_original AS programada,mact_modificado AS actualizada,mact_alcanzado AS alcanzada,
            (mact_alcanzado*100/mact_modificado) AS porcentaje ,msj_id,hms_mensaje
            FROM
            (
                SELECT gpo_id,ur_id,ur_nombre,p.ue_id,ue_nombre,prg_id,prg_nombre,spr_id,spr_nombre,py_id,py_nombre,oa_id,oa_nombre,e.epg_id,toa_nombre
                FROM cat_ObraActividad AS p
                JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
                JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
                JOIN cat_Programa USING (gpo_id,ur_id,prg_id)
                JOIN cat_Subprograma USING (gpo_id,ur_id,prg_id,spr_id)
                JOIN cat_Proyecto USING (gpo_id,ur_id,prg_id,spr_id,py_id)
                JOIN cat_TipoObraActividad  AS toa USING (toa_id)
                JOIN enc_Programatico AS e USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                JOIN presupuesto USING (gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id)
                JOIN (SELECT * FROM ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' WHERE sor_ejercicio= ' . APP_EJERCICIO . ') AS cso USING (cff_id,fte_id,sft_id,ori_id,sor_id)
            WHERE eoa_id IN (4,5)  and toa_id = 1
            ' . $oa_id . '
            ' . $prg_id . '
            ' . $spr_id . '
            ' . $py_id . '
            ' . $ue_id . '
            ' . $ter_id . '
            ' . $ur_clave . '
            GROUP BY gpo_id,ur_id,p.ue_id,prg_id,spr_id,py_id,oa_id ) AS pre
            LEFT JOIN cat_MetaActividadTrimestre ON pre.epg_id=cat_MetaActividadTrimestre.epg_id AND tri_id = ' . $trim . ' -- pii-id meta
            LEFT JOIN vin_ProductoIntermedioIndicador USING (pii_id)
            LEFT JOIN cat_Indicador USING (nim_id,ind_id)
            LEFT JOIN
            (SELECT cat_m.epg_id,cat_m.msj_id,cat_m.pii_id,hms_trimestre,hms_mensaje  FROM cat_MensajeSeguimiento AS cat_m
            JOIN cat_HistorialMensajeSeguimiento AS cat_h ON cat_m.msj_id=cat_h.msj_id AND eme_id IN (2,4,5,6) AND tim_id=1  AND cat_h.hms_trimestre = ' . $trim . ' )
            AS msj ON pre.epg_id=msj.epg_id  AND vin_ProductoIntermedioIndicador.pii_id=msj.pii_id
            WHERE  (mact_original+mact_modificado+mact_alcanzado)<>0
            ' . $porcentaje . '
            ' . $ur_clave . '
            ORDER BY pre.gpo_id,ur_id,pre.prg_id,pre.spr_id,pre.py_id,pre.oa_id
        ');

        $this->consultar();
        $this->desconectar();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-PresupuestoMetasTransparencia';
        $nav->nfDefault = 1000;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","warning");');
        }
    }
    /*----------------------------------------------------------------------------------------------------*/
    //Se revisa el funcionamiento de la sesion para este controlador
    /* sesion  */
    public function sesion($par)
    {
        $this->conectar();
        $sql = 'DELETE FROM ' . BD_TEMPORAL . '.tem_SessionReporte WHERE ssr_id ="' . $par . '"';
        $this->setSql($sql);
        $this->consultar();
        $sql = 'INSERT INTO ' . BD_TEMPORAL . '.tem_SessionReporte VALUES("' . $par . '")';
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();
    }
}
if (isset($_SESSION[_S]['prm']['A']['11']['9']['1'])) {
    new ControladorReportesVarios();
} else {
    WebResponse::sinPermisos();
}
