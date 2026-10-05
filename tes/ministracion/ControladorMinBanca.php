<?php

/**
 * Description of ControladorMinBanca
 *
 * @author ?
 * Collaborators: Adriana Santiago Morales <adriana.morales@finanzasoaxaca.gob.mx> and Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 16-Dic-2019
 * Comment: Autorización de movimientos bancarios, generación de layout de pago, mostrara layout y autorizar lotes especiales
 * Objective:
 * Regla de Negocio:
 * Permits:
 * */
/**
 * Controlador que se encarga, de la autorización de los movimientos bancarios,
 * asi como la generación de los pagos.
 * @author SANCHEZ MENDEZ JOSE EDUARDO
 */
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/tes/linea/LineaCaptura.php';
require_once dirname(__FILE__) . '/../../ent/tes/bancos/EntidadMovimientoBancario.php';
require_once dirname(__FILE__) . '/../../ent/tes/vinculacion/LoteFondeo.php';
require_once dirname(__FILE__) . '/../../ent/tes/banca/layout.php';
require_once dirname(__FILE__) . '/../../ent/tes/banca/vinLayout.php';
require_once dirname(__FILE__) . '/../../ent/tes/banca/layArchivo.php';
require_once dirname(__FILE__) . '/../../ent/tes/banca/larDetalle.php';
require_once dirname(__FILE__) . '/../../lib/tes/layout.php';
require_once dirname(__FILE__) . '/../../lib/tes/downloadLayout.php';
require_once dirname(__FILE__) . '/../models/clcs2017.php';
require_once dirname(__FILE__) . '/../models/clcs2018.php';
require_once dirname(__FILE__) . '/../models/clcs2019.php';
require_once dirname(__FILE__) . '/../models/clcs2020.php';
require_once dirname(__FILE__) . '/../models/clcs2021.php';
require_once dirname(__FILE__) . '/../models/clcs2022.php';
require_once dirname(__FILE__) . '/../models/clcs2023.php';
require_once dirname(__FILE__) . '/../models/clcs2024.php';
require_once dirname(__FILE__) . '/../models/clcs2025.php';
require_once dirname(__FILE__) . '/../models/clcs2026.php';
require_once dirname(__FILE__) . '/../../ent/scp/Clc.php';
require_once dirname(__FILE__) . '/../../lib/FicheroSefip.php';
class ControladorMinBanca extends WebResponse
{

    public $lay;
    public $detalle;
    public $lineac;
    public $escritura;

    public function __construct()
    {
        parent::__construct();
        $this->pantalla = "tes/ministracion/ControladorMinBanca.php";
        $this->escritura = isset($_SESSION[_S]['prm']['T']['4']['5']['2']);
        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
                switch ($this->metodo) {
                    case 'validaMovBancarios':
                        $this->validaMovBancarios();
                        break;
                    case 'llenaTablaAutorizaLote':
                        $this->llenaTablaAutorizaLote();
                        break;
                    case 'llenaTablaGenLayout':
                        $this->llenaTablaGenLayout();
                        break;
                    case 'detalleFondeo':
                        $this->detalleFondeo();
                        break;
                    case 'validaFondeo':
                        $this->validaFondeo();
                        break;
                    case 'llenaTablaDetalleLote':
                        $this->llenaTablaDetalleLote();
                        break;
                    case 'detalleLote':
                        $this->detalleLote();
                        break;
                    case 'generarLayout':
                        $this->generarLayout();
                        break;
                    case 'generacionLayoutDetalles':
                        $this->generacionLayoutDetalles();
                        break;
                    case 'getLayouts':
                        $this->getLayouts();
                        break;
                    case 'llenaTablaLayouts':
                        $this->llenaTablaLayouts();
                        break;
                    case 'creaArchivoLay':
                        $this->creaArchivoLay();
                        break;
                    case 'tablaDetLayout':
                        $this->tablaDetLayout();
                        break;
                    case 'llenaTablaDetLayout':
                        $this->llenaTablaDetLayout();
                        break;
                    case 'muestraAutorizarLotesEspeciales':
                        $this->muestraAutorizarLotesEspeciales();
                        break;
                    case 'llenaTablaAutorizaLotesEspeciales':
                        $this->llenaTablaAutorizaLotesEspeciales();
                        break;
                    case 'autorizarLotesEspeciales':
                        $this->autorizarLotesEspeciales();
                        break;
                    default:
                        $this->main();
                        break;
                }
        }
        $this->returnJson();
    }
    /**
     * funcion main
     * define la pagina principal del modulo de banca electroica
     * con los botones de cada una de las funciones que este modulo realiza
     * @access public
     */
    public function main()
    {
        $this->mixHtml('
            <div class="row">
                <legend>BANCA ELECTRONICA</legend>
            </div>
        ');

        $this->mixHtml('
                <div style="width:100%; padding-left:35%; padding-bottom:12px;">
                    <div titpp="Generar Layout" class="boton_opcion" id="genera_layout">
                        <div class="boton_opcion_titulo" >Generar Layout</div>
                        <div>
                            <div >
                                <strong><img  src="img/GeneraLote.png"><br></strong>
                                <small>Generar </small> </div>
                            </div>
                        </div>
                        <div style="display:table-cell;width:5px;"></div>
                        <div style="width:5px; display:table-cell"></div>
                        <div titpp="Autorizar Mov. Bancario" class="boton_opcion" id="autoriza_movb">
                            <div class="boton_opcion_titulo" >Mov. Bancarios</div>
                                <div>
                                    <strong><img  src="img/ListadoDelMes.png"><br></strong>
                                    <small>Autorizar </small>
                                </div>
                            </div>
                        </div>

                        <div style="width:100%; padding-left:35%; padding-bottom:12px;">
                        <div titpp="LAYOUTS" class="boton_opcion" id="get_layouts">
                            <div class="boton_opcion_titulo" >LAYOUTS</div>
                                <div>
                                    <strong><img  src="img/Revision_Folio.png"><br></strong>
                                    <small>Descargar </small>
                                </div>
                            </div>
                            <div style="display:table-cell;width:5px;"></div>
                            <div style="width:5px; display:table-cell"></div>
                            <div titpp="Lotes Especiales" class="boton_opcion" id="autorizarLotesEspeciales">
                                <div class="boton_opcion_titulo" >Lotes Especiales</div>
                                    <div>
                                        <strong><img  src="img/GeneraLote.png"><br></strong>
                                        <small>Autorizar </small>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            ');
        $this->mixScript('
                $("#genera_layout").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"generarLayout"
                    });
                });

                $("#autoriza_movb").click(function(){

                    comunica("' . $this->pantalla . '",{
                        metodo:"validaMovBancarios"
                    });
                });

                $("#get_layouts").click(function(){

                    comunica("' . $this->pantalla . '",{
                        metodo:"getLayouts"
                    });
                });

                $("#autorizarLotesEspeciales").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo:"muestraAutorizarLotesEspeciales"
                    });
                });


                $("#homeMenu").click( function(e) {
                    $("#contenedor").html("");
                    $("#toolsPantalla").html("");
                    ActivaAtajosMenu();
                });

            ');
        $this->agregaTools(""); //limpiamos la barra
    }
    /**
     * funcion validaMovBancarios()
     * Define la pantalla principal de la opcion
     * validar movimientos bancarios,donde se podran visualizar
     * cada uno de los movimientos pendientes de autorización.
     * @access private
     */
    private function validaMovBancarios()
    {

        //Crea el boton de regreso
        $this->mixScript('
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
            e.stopPropagation(); });
            $("#homeMenu").click( function(e) {
                    $("#contenedor").html("");
                    $("#toolsPantalla").html("");
                    ActivaAtajosMenu();
            });
            $("#btnregresarindex").removeClass("btn-default");
        ');

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = "gris";
        $btnregresarindex->icon = "circle-left me-1";
        $btnregresarindex->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        /* Declaramos los elementos a utilizar/ */
        $sqlTipoFondeo = '
            SELECT
                tmb.tmb_id,
                tmb.tmb_nombre
            FROM
                ' . BD_TESORERIA . '.cat_TipoMovimientoBancario AS tmb;
        ';
        if ($this->serverProduccion) {
            $anio = array('1' => 2026,'2' => 2025, '3' => 2024, '4' => 2023, '5' => 2022, '6' => 2021, '7' => 2020, '8' => 2019, '9' => 2018,
                '10' => 2017);
        } else {
            $anio = array('1' => 2026, '2' => 2025, '3' => 2024, '4' => 2023, '5' => 2022, '6' => 2021);
        }

        $inputTipoFondeo = new StdClass();
        $inputTipoFondeo->sql = $sqlTipoFondeo;
        $inputTipoFondeo->id = 'tipoFondeo';
        $inputTipoFondeo->name = 'tipoFondeo';
        $inputTipoFondeo->mensaje = 'SELECCIONE MOV. BANCARIO';
        $inputTipoFondeo->enDiv = true;
        $inputTipoFondeo->label = 'TIPO DE MOVIMIENTO BANCARIO';

        $inputejercicio = new stdClass();
        $inputejercicio->opciones = $anio;
        $inputejercicio->id = 'ejercicio';
        $inputejercicio->name = 'ejercicio';
        $inputejercicio->mensaje = 'ejercicio';
        $inputejercicio->enDiv = true;
        $inputejercicio->label = 'EJERCICIO';

        $inputFechaFondeo = new StdClass();
        $inputFechaFondeo->id = 'fechaFondeo';
        $inputFechaFondeo->name = 'fechaFondeo';
        $inputFechaFondeo->placeholder = 'FECHA MOV. BANCARIO';
        $inputFechaFondeo->enDiv = true;
        $inputFechaFondeo->label = 'FECHA MOV. BANCARIO';
        $inputFechaFondeo->formato = 'fecha';

        $btnBuscar = new stdClass();
        $btnBuscar->id = "btnBuscarFondeo";
        $btnBuscar->name = "btnBuscarFondeo";
        $btnBuscar->value = "Buscar";
        $btnBuscar->icon = "fa fa-search me-1";
        $btnBuscar->color = "gris";
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"llenaTablaAutorizaLote",
                tipoFondeo:$("#tipoFondeo").val(),
                fechaFondeo:$("#fechaFondeo").val(),
                ejercicio:$("#ejercicio").val()
            })';

        $tabla = new stdClass();
        $tabla->id = 'tblValidaFondeo';
        $tabla->titulo = 'VALIDACION DE MOVIMIENTOS BANCARIOS';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaAutorizaLote';

        /* Array para dibujar la tabla / */
        $tr = array();

        //Encabezados

        $th = new stdClass();
        $th->texto = 'DETALLE';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'MOVIMIENTO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA ';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = '# DE LOTES';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TOTAL';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ELIMINAR';
        $th->align = 'center';
        array_push($tr, $th);

        //Array de cuerpo de la tabla
        $trbody = array();

        //Cuerpo de la tabla
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'fa fa-search';
        $td->align = 'center';
        $td->class = 'fondeoDetalle';
        $td->attr = new stdClass();
        $td->attr->mbn_id = 'mbn_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_nombre';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_id';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_fechaMovimiento';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotes';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_total';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'fa-solid fa-trash';
        $td->color = 'rojo';
        $td->align = 'center';
        $td->class = 'fondeoElimina';
        $td->attr = new stdClass();
        $td->attr->mbn_id = 'mbn_id';
        $td->attr->disabled = 'true';
        if (isset($_SESSION[_S]['prm']['T']['4']['5']['2'])) {
            array_push($trbody, $td);
        }

        /* Llenamos tabla / */
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $html = '
            <div class="row">
                <legend>AUTORIZACIÓN DE MOVIMIENTOS BANCARIOS</legend>
            </div>
            <div class="row mb-4">
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponCombo($inputTipoFondeo) . '
                </fieldset>
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponCombo($inputejercicio) . '
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponInput($inputFechaFondeo) . '
                </fieldset>
            </div>

            <div class="row mb-4">
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
            </div>
            <br>

            <div class="row">
                <fieldset class="col-xs-12 col-md-12">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
        ';

        $this->mixHtml($html);
    }
    /**
     * funcion llenaTablaAutorizaLote()
     * Esta funcion es la encargada de llenar la tabla de movimientos
     * bancarios, de acuerdo a los parametros de busqueda realizados.
     * @access private
     * @param tipoFondeo int Tipo de movimiento bancario a buscar (fondeo,traspaso,inversion)
     * @param fechaFondeo date fecha del movimiento bancario
     * @param anio int año del moviiento bancario por default 2018
     */
    private function llenaTablaAutorizaLote()
    {
        $tipoFondeo = isset($this->prm->tipoFondeo) ? $this->prm->tipoFondeo : '';
        $fechaFondeo = isset($this->prm->fechaFondeo) ? $this->prm->fechaFondeo : '';
        $anio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : '';
        $parametros =[];
        if ($anio == 1) {
            $anio = 2026;
        } elseif ($anio == 2) {
            $anio = 2025;
        } elseif ($anio == 3) {
            $anio = 2024;
        } elseif ($anio == 4) {
            $anio = 2023;
        } elseif ($anio == 5) {
            $anio = 2022;
        } elseif ($anio == 6) {
            $anio = 2021;
        } elseif ($anio == 7) {
            $anio = 2020;
        } elseif ($anio == 8) {
            $anio = 2019;
        } elseif ($anio == 9) {
            $anio = 2018;
        } elseif ($anio == 10) {
            $anio = 2017;
        }

        $sql = 'SELECT
            mbn.mbn_id,
            tmb.tmb_nombre AS mbn_nombre,
            mbn.mbn_fechaMovimiento,
            COUNT( l.lot_id ) AS lotes,
            IFNULL(
                SUM( l.lot_importe ),
                0
            ) AS mbn_total
            FROM
                ' . BD_TESORERIA . '.vin_LoteFondeo as vlf
            inner join ' . BD_TESORERIA . '.ban_MovimientoBancario as mbn
                    using(
                    mbn_id,
                    msc_id
                )
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoMovimientoBancario AS tmb
                    USING(tmb_id)
            INNER JOIN ' . BD_TESORERIA . '.lot_Lote as l
                    using(
                    fpa_id,
                    msc_id,
                    lot_ejercicio,
                    lot_id
                )
            WHERE
                l.elo_id = 6
                AND l.lot_ejr = ' . APP_EJERCICIO . '
                AND emb_id = 2
                AND lot_ejercicio = :lot_ejercicio';

        $parametros += [
            'lot_ejercicio' => $anio,
        ];
        if (!empty($tipoFondeo)) {
            $parametros += [
                'tmb_id' => $tipoFondeo,
            ];
            $sql .= ' AND mbn.tmb_id = :tmb_id
            ';
        }

        if (!empty($fechaFondeo)) {
            $fecha = DateTime::createFromFormat('d/m/Y', $fechaFondeo);
            $parametros += [
                'mbn_fechaMovimiento' => $fecha->format('Y-m-d'),
            ];
            $sql .= ' AND mbn.mbn_fechaMovimiento = :mbn_fechaMovimiento
            ';
        }

        $sql .= '
            GROUP BY
                mbn.mbn_id;
        ';

        $this->conectar();
        $this->setSql($sql);
        $this->setParams($parametros);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tblValidaFondeo';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".fondeoDetalle").click(function(){
                    comunica("' . $this->pantalla . '", {
                            metodo: "detalleFondeo",
                            mbn_id: $(this).attr("mbn_id"),
                            anio: "' . $anio . '",
                            fechaFondeo:"' . $fechaFondeo . '",
                            tipoFondeo:"' . $tipoFondeo . '" ,
                            ejercicio:"' . $this->prm->ejercicio . '"

                    });
            });


        ');
    }
    /**
     * funcion detalleFondeo()
     * Es la encargada de generar la tabla para mostrar el detalle de los movimientos bancarios
     * recibiendo los parametros del movimiento a detallar
     * @access private
     * @param mbn_id int id del movimiento bancario
     * @param anio int anio del movimiento bancario
     * @param tipoFondeo int tipo de movimiento bancario
     * @param ejercicio int anio de los documentos que dieron origen al movimiento bancario
     * @param fechaFondeo date fecha de generación del movimiento bancario
     */
    private function detalleFondeo()
    {
        $mbn_id = isset($this->prm->mbn_id) ? $this->prm->mbn_id : '';
        $anio = isset($this->prm->anio) ? $this->prm->anio : '';
        $ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : '';
        $tipoFondeo = isset($this->prm->tipoFondeo) ? $this->prm->tipoFondeo : '';
        $fechaFondeo = isset($this->prm->fechaFondeo) ? $this->prm->fechaFondeo : '';
        if (empty($mbn_id)) {
            return;
        }

        $this->mixScript('
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
            e.stopPropagation(); });

            $("#homeMenu").click(function(e) {
                    $("#contenedor").html("");
                    $("#toolsPantalla").html("");
                    ActivaAtajosMenu();
            });
        ');

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->name = 'btnRegresar';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->type = 'button';
        $btnRegresar->color = "gris";
        $btnRegresar->icon = "fa fa-circle-left me-1";
        $btnRegresar->click = '
                // Regresa a la pantalla anterior
                comunica("' . $this->pantalla . '",{
                        metodo: "validaMovBancarios"
                });
                comunica("' . $this->pantalla . '",{
                    metodo: "llenaTablaAutorizaLote",
                    ejercicio:"' . $ejercicio . '",
                    tipoFondeo:"' . $fechaFondeo . '",
                    fechaFondeo:"' . $fechaFondeo . '"
                });
        ';

        $btnEliminar = new stdClass();
        $btnEliminar->id = 'btnEliminar';
        $btnEliminar->name = 'btnEliminar';
        $btnEliminar->text = 'Eliminar';
        $btnEliminar->value = 'Eliminar';
        $btnEliminar->type = 'button';
        $btnEliminar->color = "rojo";
        $btnEliminar->icon = "fa-solid fa-trash me-1";
        $btnEliminar->disabled = 'true';
        $btnEliminar->click = '
                //Mandamos a llamar al metodo para cambiar el estado del lote a Cancelado
                alertify.confirm(
                    "<h5> CANCELAR FONDEO </h5>",
                    "¿Realmente desea cancelar el fondeo?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                                metodo: "eliminaFondeo",
                                mbn_id: ' . $mbn_id . '
                        });
                    },
                    function(){},
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
        ';

        $btnValidar = new stdClass();
        $btnValidar->id = 'btnValidar';
        $btnValidar->name = 'btnValidar';
        $btnValidar->text = 'Validar';
        $btnValidar->value = 'Validar';
        $btnValidar->type = 'button';
        $btnValidar->color = "verde";
        $btnValidar->class = "me-1";
        $btnValidar->icon = "check me-1";
        $btnValidar->click = '
            //Mandamos a llamar al metodo para cambiar el estado del lote a Cancelado
            alertify.confirm(
                    "<h5> VALIDAR FONDEO </h5>",
                    "¿Realmente desea validar el fondeo?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo: "validaFondeo",
                            mbn_id: ' . $mbn_id . ',
                            anio:' . $anio . '
                        });
                    },
                    function(){},
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
        ';

        /* Dibuja los botones / */
        $this->agregaTools($this->ponBoton($btnRegresar));

        /* Verificamos permisos de Escritura para dibujar el elemento/ */
        if (isset($_SESSION[_S]['prm']['T']['4']['5']['2'])) {
            $this->agregaTools($this->ponBoton($btnValidar));
        }

        if (isset($_SESSION[_S]['prm']['T']['4']['5']['2'])) {
            $this->agregaTools($this->ponBoton($btnEliminar));
        }

        $tabla = new stdClass();
        $tabla->id = 'tblDetFondeo';
        $tabla->titulo = 'DETALLE DEL FONDEO ' . $mbn_id;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDetFondeo';

        /* Array para dibujar la tabla / */
        $tr = array();

        /* Encabezados / */
        $th = new stdClass();
        $th->texto = 'EJERCICIO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA REGISTRO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DE LA CUENTA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PARA LA CUENTA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->formato = 'moneda';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->align = 'center';
        array_push($tr, $th);

        /* Array de cuerpo de la tabla / */
        $trbody = array();

        /* Cuerpo de la tabla / */
        $td = new stdClass();
        $td->campoTxt = 'lot_ejercicio';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tmb_nombre';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mbn_fechaMovimiento';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_id_cargo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_id_abono';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_importe';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_concepto';
        $td->align = 'center';
        array_push($trbody, $td);

        /* Llenamos tabla / */
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
                <div class="row">
                        <legend>VALIDACION FONDEO</legend>
                </div>
                <div class="row">
                        <fieldset class="col-xs-12 col-md-12">
                                ' . $this->wrPonTabla($tabla) . '
                        </fieldset>
                </div>
        ');
        $this->llenaTablaValidaLote();
    }
    /**
     * funcion llenaTablaValidaLote()
     * Encargada de llenar la tabla de detalle de movimiento bancario
     * @access private
     * @param mbn_id id del movimiento bancario
     */
    private function llenaTablaValidaLote()
    {
        $mbn_id = isset($this->prm->mbn_id) ? $this->prm->mbn_id : '';

        if (empty($mbn_id)) {
            return;
        }

        $sql = '
          SELECT
        l.lot_ejercicio,
        tmb.tmb_nombre,
        @lote := CONCAT( IF( l.fpa_id = 1, "T", IF( l.fpa_id = 2, "C", "L" )),
        LPAD( l.msc_id, 2, "0" ), LPAD( l.lot_id, 4, "0" ), SUBSTR( l.lot_ejercicio, 3 )) AS lot,
        mbn.mbn_fechaMovimiento,
        mbn.cbs_id_cargo,
        mbn.cbs_id_abono,
        l.lot_importe,
        CONCAT( "PAGO VIA: ", fpa.fpa_nombre, " - ", @lote ) as lot_concepto
        FROM
            ' . BD_TESORERIA . '.lot_Lote AS l
        INNER JOIN ' . BD_TESORERIA . '.vin_LoteFondeo AS vlf
                USING(
                fpa_id,
                msc_id,
                lot_ejercicio,
                lot_id
            )
        INNER JOIN ' . BD_TESORERIA . '.ban_MovimientoBancario AS mbn
                USING(mbn_id)
        INNER JOIN ' . BD_TESORERIA . '.cat_TipoMovimientoBancario AS tmb
                USING(tmb_id)
        INNER JOIN ' . BD_TESORERIA . '.cat_FormaPago AS fpa
                USING(fpa_id)
        WHERE
            l.elo_id = 6
            AND mbn.emb_id = 2
            AND mbn.mbn_id = :mbn_id
        ';

        $parametros = [
            'mbn_id' => $mbn_id,
        ];

        $this->conectar();
        $this->setSql($sql);
        $this->setParams($parametros);

        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 5;
        $nav->idTabla = "tblDetFondeo";
        $this->wrLlenaTabla($nav);
    }
    /**
     * funcion validaFondeo()
     * Esta funcion sirve como intermediario para invocar al a funcion cambiaEstadoLote()
     * enviando los parametros necesarios para dicho cambio
     * @access private
     * @param mbn_id id id del movimiento bancario
     * @param anio id anio del movimiento bancario
     */
    private function validaFondeo()
    {
        $mbn_id = isset($this->prm->mbn_id) ? $this->prm->mbn_id : '';
        $anio = isset($this->prm->anio) ? $this->prm->anio : '';
        if (empty($mbn_id)) {
            return;
        }
        $this->setSql('SELECT CURDATE() as fecha');
        $this->conectar();
        $this->consultar();
        if ($row = $this->extraer()) {
            $fechaActual = $row['fecha'];
        }
        $this->prm->mbn_id = $mbn_id;
        $this->prm->accionLote = 'autorizar';
        $this->prm->anio = $anio;
        $this->prm->redirige = false;
        $this->cambiaEstadoLote();
        $this->validaMovBancarios();
    }
    /**
     * funcion cambiaEstadoLote()
     * Esta funcion es la encargada de afectar los registros en la base de datos
     * tanto de lotes como de movimientos bancarios al estado que corresponda
     * @access private
     * @param mbn_id id del movimiento bancario
     * @param lot_id id del lote
     * @param fpa_id id de la forma de pago del lote
     * @param msc_id id del mes contable del lote
     * @param ejercicio anio del ejercicio del lote
     * @param accionLote String accion a ejecutar (autorizar,banca)
     * autorizar: autoriza los movimientos bancarios, afectando el estado del movimiento, como el del lote y clc
     * banca: afecta el estado del lote a enviado a banca electronica, asi como los estados de la clc,
     * segun el ejercicio correspondiente.
     * @access private
     */
    private function cambiaEstadoLote()
    {
        $lot = new stdClass();
        $fechaActual = '';
        $mbn_id = isset($this->prm->mbn_id) ? $this->prm->mbn_id : "";
        $lot->lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : "";
        $lot->fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : "";
        $lot->msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : "";
        $lot->ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : "";
        $redirige = isset($this->prm->redirige) ? $this->prm->redirige : true;

        switch ($this->prm->accionLote) {
            case 'autorizar':
                if ($mbn_id != "") {
                    try {
                        $usr = $_SESSION[_S]['usr_id'];
                        $conexion = new AccesoDatos();
                        $conexion->conectar();
                        $set = 'SET @bit_observacion="FONDEO AUTORIZADO DESDE EL SISTEMA",@usr_id= :usr ,@usr_ip= sinpres.fn_get_ip();';
                        $paramSet = [
                            'usr' => $usr,
                        ];
                        $conexion->setSql($set);
                        $conexion->consultar($paramSet);
                        $detalle = new AccesoDatos();
                        $sqlDet = $detalle->setSql('SELECT * FROM ' . BD_TESORERIA . '.vin_LoteFondeo as vlf
                        WHERE vlf.mbn_id = :mbn_id');
                        $paramDet = [
                            'mbn_id' => $mbn_id,
                        ];
                        $detalle->conectar();
                        $detalle->consultar($paramDet);
                        while ($fila = $detalle->extraer()) {
                            $lot->lot_id = $fila['lot_id'];
                            $lot->fpa_id = $fila['fpa_id'];
                            $lot->msc_id = $fila['msc_id'];
                            $lot->ejercicio = $fila['lot_ejercicio'];
                            $sql0 = 'START TRANSACTION;';
                            $conexion->setSql($sql0);
                            $conexion->consultar();
                            $tipo = new AccesoDatos();
                            $sqltip = "SELECT tlo_id AS tipo,curdate() AS fecha
                                       FROM " . BD_TESORERIA . ".lot_Lote
                                       WHERE fpa_id= :fpa_id AND msc_id= :msc_id AND lot_ejercicio= :lot_ejercicio AND lot_id= :lot_id
                                      AND lot_ejr = :lot_ejr;";
                            $paramTip = [
                                'fpa_id' => $lot->fpa_id,
                                'msc_id' => $lot->msc_id,
                                'lot_ejercicio' => $lot->ejercicio,
                                'lot_id' => $lot->lot_id,
                                'lot_ejr' => APP_EJERCICIO,
                            ];
                            $tipo->setSql($sqltip);
                            $tipo->conectar();
                            $tipo->consultar($paramTip);
                            $tipoL = 0;
                            if ($row = $tipo->extraer()) {
                                $fechaActual = $row['fecha'];
                                $tipoL = $row['tipo'];
                            }
                            $sql1 = "UPDATE " . BD_TESORERIA . ".lot_Lote SET elo_id=7 WHERE fpa_id= :fpa_id AND msc_id= :msc_id AND lot_ejercicio= :lot_ejercicio AND lot_id= :lot_id
                            AND lot_ejr = :lot_ejr";
                            
                            $conexion->setSql($sql1);
                            $conexion->consultar($paramTip);
                            if ($tipoL == 6) {
                                #cambia estado propuesta manual
                                $sqlM = "UPDATE " . BD_TESORERIA . ".lot_PropuestaManual SET elo_id=7
                                WHERE fpa_id= :fpa_id AND msc_id= :msc_id AND lot_ejercicio= :lot_ejercicio AND lot_id= :lot_id";
                                $paramM = [
                                    'fpa_id' => $lot->fpa_id,
                                    'msc_id' => $lot->msc_id,
                                    'lot_ejercicio' => $lot->ejercicio,
                                    'lot_id' => $lot->lot_id,
                                ];
                                $conexion->setSql($sqlM);
                                $conexion->consultar($paramM);
                            }
                            if ($lot->ejercicio == 2018) {
                                $sql2 = 'SELECT gpo_id,ur_id,clc_id
                                        FROM ' . BD_TESORERIA . '.lot_LoteClc as lc '
                                . 'INNER JOIN ' . BD_CTRL_2018 . '.scp_Clc as c using (gpo_id,ur_id,clc_id)'
                                . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id)'
                                . 'WHERE lc.fpa_id= :fpa_id AND lc.msc_id= :msc_id AND lc.lot_ejercicio= :lot_ejercicio AND lc.lot_id= :lot_id AND ant.ecl_id=14
                                        AND ant.ejr_id_clc = 2018';
                                $paramS2018 = [
                                    'fpa_id' => $lot->fpa_id,
                                    'msc_id' => $lot->msc_id,
                                    'lot_ejercicio' => $lot->ejercicio,
                                    'lot_id' => $lot->lot_id,
                                ];
                                $conexion->setSql($sql2);
                                $conexion->consultar($paramS2018);
                                $clcs = array();
                                while ($clc = $conexion->extraer()) {
                                    array_push($clcs, $clc);
                                }
                                foreach ($clcs as $value) {
                                    $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=15
                                            WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2018; ";
                                    $param3 = [
                                        'gpo_id' => $value['gpo_id'],
                                        'ur_id' => $value['ur_id'],
                                        'clc_id' => $value['clc_id'],
                                    ];
                                    $conexion->setSql($sql3);
                                    $conexion->consultar($param3);
                                }
                            }
                            if ($lot->ejercicio == 2019) {
                                $sql2 = 'SELECT gpo_id,ur_id,clc_id
                                         FROM ' . BD_TESORERIA . '.lot_LoteClc as lc '
                                . 'INNER JOIN ' . BD_CTRL_2019 . '.scp_Clc as c using (gpo_id,ur_id,clc_id)'
                                . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id)'
                                . 'WHERE lc.fpa_id= :fpa_id AND lc.msc_id= :msc_id AND lc.lot_ejercicio= :lot_ejercicio AND lc.lot_id= :lot_id AND ant.ecl_id=14
                                        AND ant.ejr_id_clc = 2019';
                                $param2019 = [
                                'fpa_id' => $lot->fpa_id,
                                'msc_id' => $lot->msc_id,
                                'lot_ejercicio' => $lot->ejercicio,
                                'lot_id' => $lot->lot_id,
                                ];
                                $conexion->setSql($sql2);
                                $conexion->consultar($param2019);
                                $clcs = array();
                                while ($clc = $conexion->extraer()) {
                                    array_push($clcs, $clc);
                                }
                                foreach ($clcs as $value) {
                                    $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=15
                                        WHERE gpo_id= :gpo_id and ur_id= :ur_id AND clc_id= :clc_id
                                        AND ejr_id_clc = 2019;";
                                    $paramSql3 = [
                                        'gpo_id' => $value['gpo_id'],
                                        'ur_id' => $value['ur_id'],
                                        'clc_id' => $value['clc_id'],
                                    ];
                                    $conexion->setSql($sql3);
                                    $conexion->consultar($paramSql3);
                                }
                            }
                            if ($lot->ejercicio == 2020) {
                                $sql2 = 'SELECT gpo_id,ur_id,clc_id
                                         FROM ' . BD_TESORERIA . '.lot_LoteClc as lc '
                                . 'INNER JOIN ' . BD_CTRL_2020 . '.scp_Clc as c using (gpo_id,ur_id,clc_id)'
                                . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id)'
                                . 'WHERE lc.fpa_id= :fpa_id AND lc.msc_id= :msc_id AND lc.lot_ejercicio= :lot_ejercicio AND lc.lot_id= :lot_id AND ant.ecl_id=14
                                        AND ant.ejr_id_clc = 2020';
                                $param2020 = [
                                    'fpa_id' => $lot->fpa_id,
                                    'msc_id' => $lot->msc_id,
                                    'lot_ejercicio' => $lot->ejercicio,
                                    'lot_id' => $lot->lot_id,
                                    ];
                                $conexion->setSql($sql2);
                                $conexion->consultar($param2020);
                                $clcs = array();
                                while ($clc = $conexion->extraer()) {
                                    array_push($clcs, $clc);
                                }
                                foreach ($clcs as $value) {
                                    $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=15
                                        WHERE gpo_id= :gpo_id and ur_id= :ur_id AND clc_id= :clc_id
                                        AND ejr_id_clc = 2020;";
                                    $paramSql3 = [
                                        'gpo_id' => $value['gpo_id'],
                                        'ur_id' => $value['ur_id'],
                                        'clc_id' => $value['clc_id'],
                                    ];
                                    $conexion->setSql($sql3);
                                    $conexion->consultar($paramSql3);
                                }
                            }
                            if ($lot->ejercicio == 2021) {
                                $sql2 = 'SELECT gpo_id,ur_id,clc_id
                                         FROM ' . BD_TESORERIA . '.lot_LoteClc as lc '
                                . 'INNER JOIN ' . BD_CTRL_2021 . '.scp_Clc as c using (gpo_id,ur_id,clc_id)'
                                . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id)'
                                . 'WHERE lc.fpa_id= :fpa_id AND lc.msc_id= :msc_id AND lc.lot_ejercicio= :lot_ejercicio AND lc.lot_id= :lot_id AND ant.ecl_id=14
                                        AND ant.ejr_id_clc = 2021';
                                $param2021 = [
                                    'fpa_id' => $lot->fpa_id,
                                    'msc_id' => $lot->msc_id,
                                    'lot_ejercicio' => $lot->ejercicio,
                                    'lot_id' => $lot->lot_id,
                                    ];
                                $conexion->setSql($sql2);
                                $conexion->consultar($param2021);
                                $clcs = array();
                                while ($clc = $conexion->extraer()) {
                                    array_push($clcs, $clc);
                                }
                                foreach ($clcs as $value) {
                                    $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=15
                                        WHERE gpo_id= :gpo_id and ur_id= :ur_id AND clc_id= :clc_id
                                        AND ejr_id_clc = 2021;";
                                    $paramSql3 = [
                                        'gpo_id' => $value['gpo_id'],
                                        'ur_id' => $value['ur_id'],
                                        'clc_id' => $value['clc_id'],
                                    ];
                                    $conexion->setSql($sql3);
                                    $conexion->consultar($paramSql3);
                                }
                            }
                            if ($lot->ejercicio == 2022) {
                                $sql2 = 'SELECT gpo_id,ur_id,clc_id
                                         FROM ' . BD_TESORERIA . '.lot_LoteClc as lc '
                                . 'INNER JOIN ' . BD_CTRL_2022 . '.scp_Clc as c using (gpo_id,ur_id,clc_id)'
                                . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id)'
                                . 'WHERE lc.fpa_id= :fpa_id AND lc.msc_id= :msc_id AND lc.lot_ejercicio= :lot_ejercicio AND lc.lot_id= :lot_id AND ant.ecl_id=14
                                        AND ant.ejr_id_clc = 2022';
                                $param2022 = [
                                    'fpa_id' => $lot->fpa_id,
                                    'msc_id' => $lot->msc_id,
                                    'lot_ejercicio' => $lot->ejercicio,
                                    'lot_id' => $lot->lot_id,
                                    ];
                                $conexion->setSql($sql2);
                                $conexion->consultar($param2022);
                                $clcs = array();
                                while ($clc = $conexion->extraer()) {
                                    array_push($clcs, $clc);
                                }
                                foreach ($clcs as $value) {
                                    $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=15
                                        WHERE gpo_id= :gpo_id and ur_id= :ur_id AND clc_id= :clc_id
                                        AND ejr_id_clc = 2022;";
                                    $paramSql3 = [
                                        'gpo_id' => $value['gpo_id'],
                                        'ur_id' => $value['ur_id'],
                                        'clc_id' => $value['clc_id'],
                                    ];
                                    $conexion->setSql($sql3);
                                    $conexion->consultar($paramSql3);
                                }
                            }
                            if ($lot->ejercicio == 2023) {
                                $sql2 = 'SELECT gpo_id,ur_id,clc_id
                                         FROM ' . BD_TESORERIA . '.lot_LoteClc as lc '
                                . 'INNER JOIN ' . BD_CTRL_2023 . '.scp_Clc as c using (gpo_id,ur_id,clc_id)'
                                . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id)'
                                . 'WHERE lc.fpa_id= :fpa_id AND lc.msc_id= :msc_id AND lc.lot_ejercicio= :lot_ejercicio AND lc.lot_id= :lot_id AND ant.ecl_id=14
                                        AND ant.ejr_id_clc = 2023';
                                $param2023 = [
                                    'fpa_id' => $lot->fpa_id,
                                    'msc_id' => $lot->msc_id,
                                    'lot_ejercicio' => $lot->ejercicio,
                                    'lot_id' => $lot->lot_id,
                                    ];
                                $conexion->setSql($sql2);
                                $conexion->consultar($param2023);
                                $clcs = array();
                                while ($clc = $conexion->extraer()) {
                                    array_push($clcs, $clc);
                                }
                                foreach ($clcs as $value) {
                                    $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=15
                                        WHERE gpo_id= :gpo_id and ur_id= :ur_id AND clc_id= :clc_id
                                        AND ejr_id_clc = 2023;";
                                    $paramSql3 = [
                                        'gpo_id' => $value['gpo_id'],
                                        'ur_id' => $value['ur_id'],
                                        'clc_id' => $value['clc_id'],
                                    ];
                                    $conexion->setSql($sql3);
                                    $conexion->consultar($paramSql3);
                                }
                            }
                            if ($lot->ejercicio == 2024) {
                                $sql2 = 'SELECT gpo_id,ur_id,clc_id
                                         FROM ' . BD_TESORERIA . '.lot_LoteClc as lc '
                                . 'INNER JOIN ' . BD_CTRL_2024 . '.scp_Clc as c using (gpo_id,ur_id,clc_id)'
                                . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id)'
                                . 'WHERE lc.fpa_id= :fpa_id AND lc.msc_id= :msc_id AND lc.lot_ejercicio= :lot_ejercicio AND lc.lot_id= :lot_id AND ant.ecl_id=14
                                        AND ant.ejr_id_clc = 2024';
                                $param2024 = [
                                    'fpa_id' => $lot->fpa_id,
                                    'msc_id' => $lot->msc_id,
                                    'lot_ejercicio' => $lot->ejercicio,
                                    'lot_id' => $lot->lot_id,
                                    ];
                                $conexion->setSql($sql2);
                                $conexion->consultar($param2024);
                                $clcs = array();
                                while ($clc = $conexion->extraer()) {
                                    array_push($clcs, $clc);
                                }
                                foreach ($clcs as $value) {
                                    $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=15
                                        WHERE gpo_id= :gpo_id and ur_id= :ur_id AND clc_id= :clc_id
                                        AND ejr_id_clc = 2024;";
                                    $paramSql3 = [
                                        'gpo_id' => $value['gpo_id'],
                                        'ur_id' => $value['ur_id'],
                                        'clc_id' => $value['clc_id'],
                                    ];
                                    $conexion->setSql($sql3);
                                    $conexion->consultar($paramSql3);
                                }
                            }
                            if ($lot->ejercicio == 2025) {
                                $sql2 = 'SELECT gpo_id,ur_id,clc_id
                                         FROM ' . BD_TESORERIA . '.lot_LoteClc as lc '
                                . 'INNER JOIN ' . BD_CTRL_2025 . '.scp_Clc as c using (gpo_id,ur_id,clc_id)'
                                . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id)'
                                . 'WHERE lc.fpa_id= :fpa_id AND lc.msc_id= :msc_id AND lc.lot_ejercicio= :lot_ejercicio AND lc.lot_id= :lot_id AND ant.ecl_id=14
                                        AND ant.ejr_id_clc = 2025';
                                $param2025 = [
                                    'fpa_id' => $lot->fpa_id,
                                    'msc_id' => $lot->msc_id,
                                    'lot_ejercicio' => $lot->ejercicio,
                                    'lot_id' => $lot->lot_id,
                                    ];
                                $conexion->setSql($sql2);
                                $conexion->consultar($param2025);
                                $clcs = array();
                                while ($clc = $conexion->extraer()) {
                                    array_push($clcs, $clc);
                                }
                                foreach ($clcs as $value) {
                                    $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=15
                                        WHERE gpo_id= :gpo_id and ur_id= :ur_id AND clc_id= :clc_id
                                        AND ejr_id_clc = 2025;";
                                    $paramSql3 = [
                                        'gpo_id' => $value['gpo_id'],
                                        'ur_id' => $value['ur_id'],
                                        'clc_id' => $value['clc_id'],
                                    ];
                                    $conexion->setSql($sql3);
                                    $conexion->consultar($paramSql3);
                                }
                            }
                            if ($lot->ejercicio == 2026) {
                                $sql2 = 'SELECT gpo_id,ur_id,clc_id
                                         FROM ' . BD_TESORERIA . '.lot_LoteClc as lc '
                                . 'INNER JOIN ' . BD_CTRL . '.scp_Clc as c using (gpo_id,ur_id,clc_id)'
                                . 'WHERE lc.fpa_id= :fpa_id AND lc.msc_id= :msc_id AND lc.lot_ejercicio= :lot_ejercicio AND lc.lot_id= :lot_id AND c.ecl_id=14';
                                $param2025 = [
                                    'fpa_id' => $lot->fpa_id,
                                    'msc_id' => $lot->msc_id,
                                    'lot_ejercicio' => $lot->ejercicio,
                                    'lot_id' => $lot->lot_id,
                                    ];
                                $conexion->setSql($sql2);
                                $conexion->consultar($param2025);
                                $clcs = array();
                                while ($clc = $conexion->extraer()) {
                                    array_push($clcs, $clc);
                                }
                                foreach ($clcs as $value) {
                                    $sql3 = "UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id=15
                                        WHERE gpo_id= :gpo_id and ur_id= :ur_id AND clc_id= :clc_id;";
                                    $paramSql3 = [
                                        'gpo_id' => $value['gpo_id'],
                                        'ur_id' => $value['ur_id'],
                                        'clc_id' => $value['clc_id'],
                                    ];
                                    $conexion->setSql($sql3);
                                    $conexion->consultar($paramSql3);
                                }
                            }
                        }

                        $sqlmov = "UPDATE " . BD_TESORERIA . ".ban_MovimientoBancario set emb_id=3,mbn_fechaAutorizacion= :mbn_fechaAutorizacion where mbn_id= :mbn_id;";
                        $paramMov = [
                            'mbn_fechaAutorizacion' => $fechaActual,
                            'mbn_id' => $mbn_id,
                        ];
                        $conexion->setSql($sqlmov);
                        $conexion->consultar($paramMov);
                        $sql4 = 'COMMIT';
                        $conexion->setSql($sql4);
                        $conexion->consultar();
                        $this->mixScript('notify("MOVIMIENTO NUMERO : ' . $mbn_id . ' AUTORIZADO ","success",4000)');
                    } catch (Exception $e) {
                        $sql5 = 'ROLLBACK;';
                        $conexion->setSql($sql5);
                        $conexion->consultar();
                        $this->mixScript('notify("ERROR AL AUTORIZAR EL MOVIMIENTO : '
                            . $mbn_id . ' NOTIFIQUE AL ADMINISTRADOR ","success",4000)');
                    }
                }
                break;
            case 'banca':
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $sqltip = "SELECT tlo_id AS tipo,curdate() AS fecha
                                FROM " . BD_TESORERIA . ".lot_Lote
                                WHERE fpa_id= :fpa_id AND msc_id= :msc_id AND lot_ejercicio= :lot_ejercicio AND lot_id= :lot_id
                                AND lot_ejr = :lot_ejr;";
                $paramTipB = [
                    'fpa_id' => $lot->fpa_id,
                    'msc_id' => $lot->msc_id,
                    'lot_ejercicio' => $lot->ejercicio,
                    'lot_id' => $lot->lot_id,
                    'lot_ejr' => APP_EJERCICIO,
                ];
                $conexion->setSql($sqltip);
                $conexion->consultar($paramTipB);
                $tipoL = 0;
                if ($row = $conexion->extraer()) {
                    $fechaActual = $row['fecha'];
                    $tipoL = $row['tipo'];
                }
                $conexion->desconectar();
                try {
                    $usr = $_SESSION[_S]['usr_id'];
                    $conexion = new AccesoDatos();
                    $conexion->conectar();
                    $set = 'SET @bit_observacion="LOTE ENVIADO A BANCA ELECTRONICA DESDE EL SISTEMA",@usr_id= :usr ,@usr_ip= sinpres.fn_get_ip();';
                    $paramSetB = [
                        'usr' => $usr,
                    ];
                    $conexion->setSql($set);
                    $conexion->consultar($paramSetB);
                    $sql0 = 'START TRANSACTION;';
                    $conexion->setSql($sql0);
                    $conexion->consultar();
                    $sql1 = "UPDATE " . BD_TESORERIA . ".lot_Lote SET elo_id=8 WHERE fpa_id= :fpa_id AND msc_id= :msc_id AND lot_ejercicio= :lot_ejercicio AND lot_id= :lot_id
                        AND lot_ejr = :lot_ejr ;";
                    $paramUpdateB = [
                        'fpa_id' => $lot->fpa_id,
                        'msc_id' => $lot->msc_id,
                        'lot_ejercicio' => $lot->ejercicio,
                        'lot_id' => $lot->lot_id,
                        'lot_ejr' => APP_EJERCICIO,
                    ];
                    $conexion->setSql($sql1);
                    $conexion->consultar($paramUpdateB);
                    if ($tipoL == 6) {
                        #cambia estado propuesta manual
                        $sqlM = "UPDATE " . BD_TESORERIA . ".lot_PropuestaManual SET elo_id=8
                            WHERE fpa_id= :fpa_id AND msc_id= :msc_id AND lot_ejercicio= :lot_ejercicio and lot_id= :lot_id";
                        $paramSqlMB = [
                            'fpa_id' => $lot->fpa_id,
                            'msc_id' => $lot->msc_id,
                            'lot_ejercicio' => $lot->ejercicio,
                            'lot_id' => $lot->lot_id,
                        ];
                        $conexion->setSql($sqlM);
                        $conexion->consultar($paramSqlMB);
                    }
                    switch ($lot->ejercicio) {
                        case 2026:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND c.ecl_id in (15)';
                            break;
                        case 2025:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL_2025 . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND ca.ecl_id in (15) AND ca.ejr_id_clc = 2025';
                            break;
                        case 2024:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL_2024 . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND ca.ecl_id in (15) AND ca.ejr_id_clc = 2024';
                            break;
                        case 2023:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL_2023 . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND ca.ecl_id in (15) AND ca.ejr_id_clc = 2023';
                            break;
                        case 2022:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL_2022 . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND ca.ecl_id in (15) AND ca.ejr_id_clc = 2022';
                            break;
                        case 2021:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL_2021 . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND ca.ecl_id in (15) AND ca.ejr_id_clc = 2021';
                            break;
                        case 2020:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL_2020 . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND ca.ecl_id in (15) AND ca.ejr_id_clc = 2020';
                            break;
                        case 2019:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL_2019 . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND ca.ecl_id in (15) AND ca.ejr_id_clc = 2019';
                            break;
                        case 2018:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL_2018 . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND ca.ecl_id in (15) AND ca.ejr_id_clc = 2018';
                            break;
                        case 2017:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL_2017 . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND ca.ecl_id in (11) AND ca.ejr_id_clc = 2017';
                            break;
                    }
                    $paramCase = [
                        'fpa_id' => $lot->fpa_id,
                        'msc_id' => $lot->msc_id,
                        'lot_ejercicio' => $lot->ejercicio,
                        'lot_id' => $lot->lot_id,
                    ];
                    $conexion->setSql($sql2);
                    $conexion->consultar($paramCase);
                    $clcs = array();
                    while ($clc = $conexion->extraer()) {
                        array_push($clcs, $clc);
                    }
                    switch ($lot->ejercicio) {
                        case 2026:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id=16 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id; ";
                                $paramAux2025 = [
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramAux2025);
                            }
                            break;
                        case 2025:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=16 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2025; ";
                                $paramAux2025 = [
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramAux2025);
                            }
                            break;
                        case 2024:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=16 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2024; ";
                                $paramAux2024 = [
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramAux2024);
                            }
                            break;
                        case 2023:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=16 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2023; ";
                                $paramAux2023 = [
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramAux2023);
                            }
                            break;
                        case 2022:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=16 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2022; ";
                                $paramAux2022 = [
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramAux2022);
                            }
                            break;
                        case 2021:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=16 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2021; ";
                                $paramAux2021 = [
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramAux2021);
                            }
                            break;
                        case 2020:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=16 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2020; ";
                                $paramAux2020 = [
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramAux2020);
                            }
                            break;
                        case 2019:
                            foreach ($clcs as $value) {

                                $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=16 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2019; ";
                                $paramAux2019 = [
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramAux2019);
                            }
                            break;
                        case 2018:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=16 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2018; ";
                                $paramAux2018 = [
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramAux2018);
                            }
                            break;
                        case 2017:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=12 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2017;";
                                $paramAux2017 = [
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramAux2017);
                            }
                            break;
                    }
                    $sql4 = 'COMMIT';
                    $conexion->setSql($sql4);
                    $conexion->consultar();
                } catch (Exception $e) {
                    $sql5 = 'ROLLBACK;';
                    $conexion->setSql($sql5);
                    $conexion->consultar();
                    $this->mixScript('notify("ERROR AL CAMBIAR ESTADO DEL LOTE, NOTIFIQUE AL ADMINISTRADOR ","error")');
                }
                break;
        }
    }
    /**
     * funcion generarLayout();
     * Pantalla principal de la opcion generarLayout, donde se crea la tabla
     * de todos aquellos lotes que estan en condiciones de generar un layout de pago,
     * asi como todos los controles para la busqueda especifica
     * @access private
     */
    private function generarLayout()
    {
        //Crea el boton de regreso
        $this->mixScript('
                    DesactivaAtajosMenu();
                    Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
                    e.stopPropagation(); });

                    $("#homeMenu").click(function(e) {
                            $("#contenedor").html("");
                            $("#toolsPantalla").html("");
                            ActivaAtajosMenu();
                    });
                    $("#btnregresarindex").removeClass("btn-default");
            ');

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = "gris";
        $btnregresarindex->icon = "fa fa-circle-left me-1";
        $btnregresarindex->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';

        $btnGenerarLayout = new stdClass();
        $btnGenerarLayout->id = 'btnGenerarLayout';
        $btnGenerarLayout->name = 'btnGenerarLayout';
        $btnGenerarLayout->value = 'Generar Layout';
        $btnGenerarLayout->color = 'gris';
        $btnGenerarLayout->icon = 'check me-1';
        $btnGenerarLayout->click = '
            if(seleccion = $("#tblGenFondeo").tabla("getFilaSeleccion")){
                alertify.confirm(
                    "<h5> Generar Layout </h5>",
                    "¿Desea generar el layout con los lotes seleccionados?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo: "generacionLayoutDetalles",
                            fondeoDetalles: seleccion
                        });
                    },
                    function(){},
                ).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            }
        ';

        //Dibuja el boton
        $this->agregaTools($this->ponBoton($btnregresarindex));
        if (isset($_SESSION[_S]['prm']['T']['4']['5']['2'])) {
            $this->agregaTools($this->ponBoton($btnGenerarLayout));
        }

        /* Declaramos los elementos a utilizar/ */
        $inputLotePago = new StdClass();
        $inputLotePago->id = 'lotePago';
        $inputLotePago->name = 'lotePago';
        $inputLotePago->placeholder = 'LOTE DE PAGO';
        $inputLotePago->enDiv = true;
        $inputLotePago->label = 'LOTE DE PAGO';

        $inputFechaMinAut = new StdClass();
        $inputFechaMinAut->id = 'fechaMinAut';
        $inputFechaMinAut->name = 'fechaMinAut';
        $inputFechaMinAut->placeholder = 'FECHA MÍNIMA';
        $inputFechaMinAut->enDiv = true;
        $inputFechaMinAut->label = 'FECHA MÍNIMA DE AUTORIZACIÓN';
        $inputFechaMinAut->formato = 'fecha';

        $inputFechaMaxAut = new StdClass();
        $inputFechaMaxAut->id = 'fechaMaxAut';
        $inputFechaMaxAut->name = 'fechaMaxAut';
        $inputFechaMaxAut->placeholder = 'FECHA MÁXIMA';
        $inputFechaMaxAut->enDiv = true;
        $inputFechaMaxAut->label = 'FECHA MÁXIMA DE AUTORIZACIÓN';
        $inputFechaMaxAut->formato = 'fecha';

        $inputFechaMinPago = new StdClass();
        $inputFechaMinPago->id = 'fechaMinPago';
        $inputFechaMinPago->name = 'fechaMinPago';
        $inputFechaMinPago->placeholder = 'FECHA MÍNIMA';
        $inputFechaMinPago->enDiv = true;
        $inputFechaMinPago->label = 'FECHA MÍNIMA DE PAGO';
        $inputFechaMinPago->formato = 'fecha';

        $inputFechaMaxPago = new StdClass();
        $inputFechaMaxPago->id = 'fechaMaxPago';
        $inputFechaMaxPago->name = 'fechaMaxPago';
        $inputFechaMaxPago->placeholder = 'FECHA MÁXIMA';
        $inputFechaMaxPago->enDiv = true;
        $inputFechaMaxPago->label = 'FECHA MÁXIMA DE PAGO';
        $inputFechaMaxPago->formato = 'fecha';

        $sqlTipoLote = '
                    SELECT
                            tlo_id,
                            tlo_nombre
                    FROM
                            ' . BD_TESORERIA . '.cat_TipoLote ;
            ';
        $inputTipoLote = new StdClass();
        $inputTipoLote->id = 'tipoLote';
        $inputTipoLote->sql = $sqlTipoLote;
        $inputTipoLote->name = 'tipoLote';
        $inputTipoLote->placeholder = 'TIPO DE LOTE';
        $inputTipoLote->enDiv = true;
        $inputTipoLote->label = 'TIPO LOTE';

        if ($this->serverProduccion) {
            $anio = array('1' => 2026, '2' => 2025, '3' => 2024, '4' => 2023, '5' => 2022, '6' => 2021,
                '7' => 2020, '8' => 2019, '9' => 2018, '10' => 2017);
        } else {
            $anio = array('1' => 2026, '2' => 2025, '3' => 2024, '4' => 2023, '5' => 2022, '6' => 2021);
        }

        $inputejercicio = new stdClass();
        $inputejercicio->opciones = $anio;
        $inputejercicio->id = 'ejercicio';
        $inputejercicio->name = 'ejercicio';
        $inputejercicio->mensaje = 'ejercicio';
        $inputejercicio->enDiv = true;
        $inputejercicio->label = 'EJERCICIO';

        $btnBuscar = new stdClass();
        $btnBuscar->id = "btnBuscar";
        $btnBuscar->name = "btnBuscar";
        $btnBuscar->value = "Buscar";
        $btnBuscar->color = "gris";
        $btnBuscar->icon = "fa fa-search me-1";
        $btnBuscar->click = '
                comunica("' . $this->pantalla . '",{
                        metodo:"llenaTablaGenLayout",
                        ejercicio:$("#ejercicio").val(),
                        lotePago:$("#lotePago").val(),
                        fechaMinAut:$("#fechaMinAut").val(),
                        fechaMaxAut:$("#fechaMaxAut").val(),
                        fechaMinPago:$("#fechaMinPago").val(),
                        fechaMaxPago:$("#fechaMaxPago").val(),
                        tipo:$("#tipoLote").val()
                    })';

        $tabla = new stdClass();
        $tabla->multiseleccionable = true;
        $tabla->prmSeleccion = array('fpa_id', 'msc_id', 'lot_ejercicio', 'lot_id', 'tlo_id');

        $tabla->casoSeleccionable = new stdClass();
        $tabla->casoSeleccionable->campo = 'elo_id';
        $tabla->casoSeleccionable->valor = 7;
        $tabla->casoSeleccionable->colorEnFalso = "rgba(8, 255, 18, 0.20)";

        $tabla->id = 'tblGenFondeo';
        $tabla->titulo = 'Resumen de lotes';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaGenLayout';
        $tabla->comunica->ejercicio = '{$("#ejercicio").val()}';
        $tabla->comunica->tipo = '{$("#tipoLote").val()}';
        $tabla->comunica->lotePago = '{$("#lotePago").val()}';
        $tabla->comunica->fechaMinAut = '{$("#fechaMinAut").val()}';
        $tabla->comunica->fechaMaxAut = '{$("#fechaMaxAut").val()}';
        $tabla->comunica->fechaMinPago = '{$("#fechaMinPago").val()}';
        $tabla->comunica->fechaMaxPago = '{$("#fechaMaxPago").val()}';

        /* Array para dibujar la tabla / */
        $tr = array();

        //Encabezados
        $th = new stdClass();
        $th->texto = 'DETALLE';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EJERCICIO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        $th->width = '10rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'MODO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE';
        $th->width = '6rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC\'S';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA AUTORIZACIÓN';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA PAGO';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TOTAL';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EDO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'OBSERVACION';
        $th->align = 'center';
        array_push($tr, $th);

        //Array de cuerpo de la tabla
        $trbody = array();

        //Cuerpo de la tabla
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'fa fa-search';
        $td->class = 'lotDet';
        $td->attr = new stdClass();
        $td->attr->lotDet = 'lot_id';
        $td->attr->lotFpa = 'fpa_id';
        $td->attr->lotMsc = 'msc_id';
        $td->attr->lotEje = 'lot_ejercicio';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_ejercicio';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_tipo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_modo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'numclc';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_fechaAutorizacion';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_fechaPago';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_total';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_edo';
        $td->align = 'right';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_obs';
        $td->align = 'right';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'elo_id';
        $td->casos->valor = 7;

        /* Llenamos tabla / */
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $html = '
            <div class="row">
                <legend>GENERACIÓN DEL LAYOUT</legend>
            </div>
            <div class="row">
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponInput($inputLotePago) . '
                </fieldset>
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponCombo($inputTipoLote) . '
                </fieldset>
                                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponCombo($inputejercicio) . '
                </fieldset>
            </div>
            <div class="row">
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponInput($inputFechaMinAut) . '
                </fieldset>
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponInput($inputFechaMaxAut) . '
                </fieldset>
            </div>
            <div class="row">
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponInput($inputFechaMinPago) . '
                </fieldset>
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponInput($inputFechaMaxPago) . '
                </fieldset>
            </div>
            </br>
            <div class="row">
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
            </div>
            <hr>
            <div class="row">
                <fieldset class="col-xs-12 col-md-12">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
        ';

        $this->mixHtml($html);
    }
    /**
     * funcion llenaTablaGenLayout()
     * Encargada de llenar la tabla de todos aquellos lotes aptos para
     * poder generar su layout de pago
     * @access private
     * @param ejercicio|"" int anio del ejercicio del lote
     * @param lotePago|"" lote identificador de lote (fpa_id,msc_id,lot_ejercicio,lot_id)
     * @param fechaMinAut|"" fecha minima de autorización
     * @param fechaMaxAut|"" fecha maxima de autorización
     * @param fechaminPago|"" fecha minima de pago
     * @param fechaMaxPago|"" fecha maxima de pago
     * @param tipo tipo de lote
     */
    private function llenaTablaGenLayout()
    {
        $get = new clcs2017;
        $sql = $get->getMovimientosAutorizados($this->prm);
        $this->conectar();
        $this->setSql($sql);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;
        $nav->idTabla = "tblGenFondeo";
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".lotDet").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "detalleLote",
                    lot_id: $(this).attr("lotDet"),
                    fpa_id: $(this).attr("lotFpa"),
                    msc_id: $(this).attr("lotMsc"),
                    lot_ejercicio: $(this).attr("lotEje"),
                    ejercicio:' . (isset($this->prm->ejercicio) ? $this->prm->ejercicio : '') . ',
                    lotePago:"' . (isset($this->prm->lotePago) ? $this->prm->lotePago : '') . '",
                    fechaMinAut:"' . (isset($this->prm->fechaMinAut) ? $this->prm->fechaMinAut : '') . '",
                    fechaMaxAut:"' . (isset($this->prm->fechaMaxAut) ? $this->prm->fechaMaxAut : '') . '",
                    fechaMinPago:"' . (isset($this->prm->fechaMinPago) ? $this->prm->fechaMinPago : '') . '",
                    fechaMaxPago:"' . (isset($this->prm->fechaMaxPago) ? $this->prm->fechaMaxPago : '') . '",
                    tipo:"' . (isset($this->prm->tipo) ? $this->prm->tipo : '') . '"
                });
            });
        ');
    }
    /**
     * funcion detalleLote()
     * Encargada de definir la tabla detalle del lote
     * @access private
     */
    private function detalleLote()
    {
        $lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : "";
        $fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : "";
        $msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : "";
        $lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : "";
        $sub_modulo = isset($this->prm->sub_modulo) ? $this->prm->sub_modulo : "";

        if (empty($lot_id) || empty($fpa_id) || empty($msc_id) || empty($lot_ejercicio)) {
            return;
        }

        /* Crea el boton de regreso / */
        $this->mixScript('
                DesactivaAtajosMenu();
                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
                e.stopPropagation(); });

                $("#homeMenu").click(function(e) {
                        $("#contenedor").html("");
                        $("#toolsPantalla").html("");
                        ActivaAtajosMenu();
                });
                $("#btnRegresar").removeClass("btn-default");
        ');

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->color = "gris";
        $btnRegresar->name = 'btnRegresar';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->type = 'button';
        $btnRegresar->icon = "fa fa-circle-left me-1";
        $btnRegresar->click = '

                // Regresa a la pantalla anterior
                comunica("' . $this->pantalla . '",{
                        metodo: "' . (empty($sub_modulo) ? 'generarLayout' : 'muestraAutorizarLotesEspeciales') . '"
                });
                comunica("' . $this->pantalla . '",{
                        metodo: "' . (empty($sub_modulo) ? 'llenaTablaGenLayout' : 'llenaTablaAutorizaLotesEspeciales') . '",
                        ejercicio:"' . $this->prm->ejercicio . '",
                        lotePago:"' . $this->prm->lotePago . '",
                        fechaMinAut:"' . $this->prm->fechaMinAut . '",
                        fechaMaxAut:"' . $this->prm->fechaMaxAut . '",
                        fechaMinPago:"' . $this->prm->fechaMinPago . '",
                        fechaMaxPago:"' . $this->prm->fechaMaxPago . '",
                        tipo:"' . $this->prm->tipo . '"

                });
        ';
        // boten de excel
        $btnExcel = new stdClass();
        $btnExcel->id = "ListadoExcel";
        $btnExcel->color = "verde";
        $btnExcel->class = "me-1";
        $btnExcel->value = "Excel";
        $btnExcel->icon = "fa-solid fa-file-excel me-1";
        $btnExcel->name = "prueba";
        $btnExcel->click = '
        printCmp();
        ';
        $this->mixScript('
        function printCmp(){
        var form = $("<form>")
        .attr("action", "rep/excel/tes/ministracion/exp_BancaDetLote.php")
        .attr("method", "post")
        .attr("target", "_blank")
        .attr("id", "enviaPost")
        .append($("<input>").attr("type", "hidden")
        .attr("name", "lot_id").val(' . $lot_id . '))
        .append($("<input>").attr("type", "hidden")
        .attr("name", "fpa_id").val(' . $fpa_id . '))
        .append($("<input>").attr("type", "hidden")
        .attr("name", "msc_id").val(' . $msc_id . '))
        .append($("<input>").attr("type", "hidden")
        .attr("name", "lot_ejercicio").val(' . $lot_ejercicio . '))
        $("body").append(form);
        form.submit();
        form.remove();
                           }
                        ');

        $btnPDF = new stdClass();
        $btnPDF->id = "ListadoPDF";
        $btnPDF->color = "rojo";
        $btnPDF->class = 'me-1';
        $btnPDF->value = "PDF";
        $btnPDF->icon = "fa-solid fa-file-pdf me-1";
        $btnPDF->name = "prueba";
        $btnPDF->click = '
        print();
        ';
        $this->mixScript('
            function print(){
                var form = $("<form>")
                .attr("action", "rep/doc/tes/ministracion/rep_Banca.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .attr("id", "enviaPost")
                .append($("<input>").attr("type", "hidden")
                .attr("name", "lot_id").val(' . $lot_id . '))
                .append($("<input>").attr("type", "hidden")
                .attr("name", "fpa_id").val(' . $fpa_id . '))
                .append($("<input>").attr("type", "hidden")
                .attr("name", "msc_id").val(' . $msc_id . '))
                .append($("<input>").attr("type", "hidden")
                .attr("name", "lot_ejercicio").val(' . $lot_ejercicio . '))
                .append($("<input>").attr("type", "hidden")
                .attr("name", "usr_id").val(' . $_SESSION[_S]['usr_id'] . '))
                $("body").append(form);
                form.submit();
                form.remove();
            }
        ');

        /* Dibuja los botones / */
        $this->agregaTools($this->ponBoton($btnRegresar));
        $this->agregaTools($this->ponBoton($btnExcel));
        $this->agregaTools($this->ponBoton($btnPDF));

        /* Verificamos permisos de Escritura para dibujar el elemento/ */

        $tabla = new stdClass();
        $tabla->id = 'tblDetalleLote';
        $tabla->titulo = 'DETALLE DEL LOTE ' . $lot_id;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDetalleLote';
        $tabla->comunica->lot_id = $this->prm->lot_id;
        $tabla->comunica->fpa_id = $this->prm->fpa_id;
        $tabla->comunica->msc_id = $this->prm->msc_id;
        $tabla->comunica->lot_ejercicio = $this->prm->lot_ejercicio;

        /* Array para dibujar la tabla / */
        $tr = array();

        /* Encabezados / */
        $th = new stdClass();
        $th->texto = 'LOTE';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UR';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FOLIO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        if ($fpa_id == 3) {
            $th = new stdClass();
            $th->texto = 'FOLIO_LINEACAPTURA';
            $th->width = '8rem';
            $th->align = 'center';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'LINEA_CAPTURA';
            $th->width = '8rem';
            $th->align = 'center';
            array_push($tr, $th);
        }

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BENEFICIARIO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA_CARGO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA_DESTINO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        $th->width = '10rem';
        $th->align = 'center';
        array_push($tr, $th);

        /* Array de cuerpo de la tabla / */
        $trbody = array();

        /* Cuerpo de la tabla / */
        $td = new stdClass();
        $td->campoTxt = 'lotDet';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotDet_ur';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotDet_clc';
        $td->align = 'center';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'lotDet_tipo';
        $td->align = 'center';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'lotDet_folio';
        $td->align = 'center';
        array_push($trbody, $td);

        if ($fpa_id == 3) {
            $td = new stdClass();
            $td->campoTxt = 'lotDet_folioL';
            $td->align = 'center';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->campoTxt = 'lotDet_lineaC';
            $td->align = 'center';
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->campoTxt = 'lotDet_importe';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotDet_beneficiario';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotDet_CtaCargo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotDet_CtaAbono';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotDet_concepto';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lotDet_fecha';
        $td->align = 'center';
        array_push($trbody, $td);
        /* Llenamos tabla / */
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->mixHtml('
                <div class="row">
                        <legend>GENERACIÓN DEL LAYOUT</legend>
                </div>
                <div class="row">
                        <fieldset class="col-xs-12 col-md-12">
                                ' . $this->wrPonTabla($tabla) . '
                        </fieldset>
                </div>
        ');
        $this->llenaTablaDetalleLote();
    }
    /**
     * funcion llenaTablaDetalleLote()
     * Encargada de llenar la tabla detalle lote, con los datos obtenidos del lote seleccionado
     * @access private
     * @param lot_id id del lote
     * @param fpa_id identificador de la forma de pago
     * @param msc_id identificador del mes
     * @param lot_ejercicio identificador del ejercicio del lote
     */
    //Llenamos la tabla del detalle del Lote
    private function llenaTablaDetalleLote()
    {
        $lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : "";
        $fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : "";
        $msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : "";
        $lot_ejercicio = isset($this->prm->lot_ejercicio) ? $this->prm->lot_ejercicio : "";
        $sql ='';
        $conexiontipo = new AccesoDatos();
        $conexiontipo->conectar();
        $sqlt = 'SELECT
                    tlo_id
                FROM
                    ' . BD_TESORERIA . '.lot_Lote AS l
                WHERE
                    l.lot_id = :lot_id
                    AND l.fpa_id= :fpa_id
                    AND l.msc_id= :msc_id
                    AND l.lot_ejercicio= :lot_ejercicio
                    AND l.lot_ejr = :lot_ejr;';
        $parametroSqlt =[
            'lot_id' => $lot_id,
            'fpa_id' => $fpa_id,
            'msc_id' => $msc_id,
            'lot_ejercicio' => $lot_ejercicio,
            'lot_ejr' => APP_EJERCICIO,
        ];
        $conexiontipo->setSql($sqlt);
        $conexiontipo->consultar($parametroSqlt);
        $t = $conexiontipo->extraer();
        $tipoLote = $t['tlo_id'];
        $e2017 = new clcs2017;
        $e2018 = new clcs2018;
        $e2019 = new clcs2019;
        $e2020 = new clcs2020;
        $e2021 = new clcs2021;
        $e2022 = new clcs2022;
        $e2023 = new clcs2023;
        $e2024 = new clcs2024;
        $e2025 = new clcs2025;
        $e2026 = new clcs2026;

        if ($fpa_id == 3) {
            if ($lot_ejercicio == 2026) {
                $sql = $e2026->getDetalleLoteLinea2026($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2025) {
                $sql = $e2025->getDetalleLoteLinea2025($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2024) {
                $sql = $e2024->getDetalleLoteLinea2024($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2023) {
                $sql = $e2023->getDetalleLoteLinea2023($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2022) {
                $sql = $e2022->getDetalleLoteLinea2022($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2021) {
                $sql = $e2021->getDetalleLoteLinea2021($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2020) {
                $sql = $e2020->getDetalleLoteLinea2020($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2019) {
                $sql = $e2019->getDetalleLoteLinea2019($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2018) {
                $sql = $e2018->getDetalleLoteLinea2018($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2017) {
                //todo: generar consulta para detalle de linea de captura ejercicio 2017
                $sql;
            }
        } else {
            switch ($tipoLote) {
                case 6:
                    $sql = $e2026->DetalleManual($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
                    break;
                case 4:
                    $sql = $e2026->getDetalleMunicipio2026($fpa_id, $msc_id, $lot_ejercicio, $lot_id);
                    break;
                default:
                    if ($lot_ejercicio == 2017) {
                        $sql = $e2017->DetalleLoteLayout2017($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
                    } elseif ($lot_ejercicio == 2018) {
                        $sql = $e2018->DetalleLoteLayout2018($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
                    } elseif ($lot_ejercicio == 2019) {
                        $sql = $e2019->DetalleLoteLayout2019($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
                    } elseif ($lot_ejercicio == 2020) {
                        $sql = $e2020->DetalleLoteLayout2020($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
                    } elseif ($lot_ejercicio == 2021) {
                        $sql = $e2021->DetalleLoteLayout2021($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
                    } elseif ($lot_ejercicio == 2022) {
                        $sql = $e2022->DetalleLoteLayout2022($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
                    } elseif ($lot_ejercicio == 2023) {
                        $sql = $e2023->DetalleLoteLayout2023($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
                    } elseif ($lot_ejercicio == 2024) {
                        $sql = $e2024->DetalleLoteLayout2024($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
                    } elseif ($lot_ejercicio == 2025) {
                        $sql = $e2025->DetalleLoteLayout2025($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
                    } elseif ($lot_ejercicio == 2026) {
                        $sql = $e2026->DetalleLoteLayout2026($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
                    }
                    break;
            }
        }
        /* Hacemos la consulta y llenamos la tabla con el resultado / */
        $this->conectar();
        $this->setSql($sql);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 5;
        $nav->idTabla = 'tblDetalleLote';
        $this->wrLlenaTabla($nav);
    }
    /**
     * funcion generacionLayoutDetalles
     * Esta funcion es la encargada de generar, cada una de las lineas que se escribiran
     * en el archivo de layout de pago, para cada uno de los bancos que defina la CLC a pagar.
     * @access private
     * @param layoutDetalles array de los lotes seleccionados, para generar su o sus layouts de pago
     */
    private function generacionLayoutDetalles()
    {
        $lineabx = '';
        $lineabv = '';
        $lineabni = '';
        $lineabne = '';
        $lineasti = '';
        $lineaste = '';
        $lineahi = '';
        $lineahe = '';
        $lineaScotiabank = '';
        $lineaAzt = '';

        $layoutDetalles = isset($this->prm->fondeoDetalles) ? $this->prm->fondeoDetalles : '';
        $lot_id = '';
        $statusLay = false;
        foreach ($layoutDetalles as $key => $value) {
            $lot_id .= "(" . $value->fpa_id . "," . $value->msc_id . ","
            . $value->lot_ejercicio . "," . $value->lot_id . "),";
            $anio = $value->lot_ejercicio;
            $formaPago = $value->fpa_id;
        }
        $lotes = trim($lot_id, ',');
        $conexiontipo = new AccesoDatos();
        $conexiontipo->conectar();
        $sqlt = "SELECT
                    tlo_id
                    FROM
                " . BD_TESORERIA . ".lot_Lote AS l
                WHERE (l.fpa_id,l.msc_id,l.lot_ejercicio,l.lot_id) in (
                " . $lotes . ")
                AND l.lot_ejr = :lot_ejr;";
        
        $parametrost = [
            'lot_ejr' => APP_EJERCICIO,
        ];
        $conexiontipo->setSql($sqlt);
        $conexiontipo->consultar($parametrost);
        $t = $conexiontipo->extraer();
        $tipoLote = $t['tlo_id'];
        $e2026 = new clcs2026;
        $e2025 = new clcs2025;
        $e2024 = new clcs2024;
        $e2023 = new clcs2023;
        $e2022 = new clcs2022;
        $e2021 = new clcs2021;
        $e2020 = new clcs2020;
        $e2019 = new clcs2019;
        $e2018 = new clcs2018;
        $e2017 = new clcs2017;
        if ($formaPago == 3) {
            if ($tipoLote != 5) {
                switch ($anio) {
                    case 2017:
                        $sql = $e2017->getDetalleLoteLc2017($lotes);
                        break;
                    case 2018:
                        $sql = $e2018->getDetalleLoteLc2018($lotes);
                        break;
                    case 2019:
                        $sql = $e2019->getDetalleLoteLc2019($lotes);
                        break;
                    case 2020:
                        $sql = $e2020->getDetalleLoteLc2020($lotes);
                        break;
                    case 2021:
                        $sql = $e2021->getDetalleLoteLc2021($lotes);
                        break;
                    case 2022:
                        $sql = $e2022->getDetalleLoteLc2022($lotes);
                        break;
                    case 2023:
                        $sql = $e2023->getDetalleLoteLc2023($lotes);
                        break;
                    case 2024:
                        $sql = $e2024->getDetalleLoteLc2024($lotes);
                        break;
                    case 2025:
                        $sql = $e2025->getDetalleLoteLc2025($lotes);
                        break;
                    case 2026:
                        $sql = $e2026->getDetalleLoteLc2026($lotes);
                        break;
                }
            }
        } else {
            switch ($tipoLote) {
                case 2:
                    if ($anio == 2017) {
                        $sql = $e2017->getDetLotePdp2017($lotes);
                    } elseif ($anio == 2018) {
                        $sql = $e2018->getDetLotePdp2018($lotes);
                    } elseif ($anio == 2019) {
                        $sql = $e2019->getDetLotePdp2019($lotes);
                    } elseif ($anio == 2020) {
                        $sql = $e2020->getDetLotePdp2020($lotes);
                    } elseif ($anio == 2021) {
                        $sql = $e2021->getDetLotePdp2021($lotes);
                    } elseif ($anio == 2022) {
                        $sql = $e2022->getDetLotePdp2022($lotes);
                    } elseif ($anio == 2023) {
                        $sql = $e2023->getDetLotePdp2023($lotes);
                    } elseif ($anio == 2024) {
                        $sql = $e2024->getDetLotePdp2024($lotes);
                    } elseif ($anio == 2025) {
                        $sql = $e2025->getDetLotePdp2025($lotes);
                    } elseif ($anio == 2026) {
                        $sql = $e2026->getDetLotePdp2026($lotes);
                    }
                    break;
                case 4:
                    $sql = $e2026->getDetalleLoteMunicipio($lotes);
                    break;
                case 6:
                case 7:
                    $sql = $e2026->getDetalleLoteManual($lotes);
                    break;
                default:
                    if ($anio == 2017) {
                        $sql = $e2017->getDetalleLote2017($lotes);
                    } elseif ($anio == 2018) {
                        $sql = $e2018->getDetalleLote2018($lotes);
                    } elseif ($anio == 2019) {
                        $sql = $e2019->getDetalleLote2019($lotes);
                    } elseif ($anio == 2020) {
                        $sql = $e2020->getDetalleLote2020($lotes);
                    } elseif ($anio == 2021) {
                        $sql = $e2021->getDetalleLote2021($lotes);
                    } elseif ($anio == 2022) {
                        $sql = $e2022->getDetalleLote2022($lotes);
                    } elseif ($anio == 2023) {
                        $sql = $e2023->getDetalleLote2023($lotes);
                    } elseif ($anio == 2024) {
                        $sql = $e2024->getDetalleLote2024($lotes);
                    } elseif ($anio == 2025) {
                        $sql = $e2025->getDetalleLote2025($lotes);
                    } else {
                        $sql = $e2026->getDetalleLote2026($lotes);
                    }
                    break;
            }
        }

        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $formatoLayout = 'txt';
        $total_transacciones = 0;
        $total_transaccioneshs = 0;
        /*Se separan las variables de conteo de transacciones por banco
        para evitar que el en el layout de SCOTIAL sea incorrecto.
         */
        $total_transaccionesSC = 0;
        $total_transaccionesAZT = 0;
        $suma_importes = 0;
        $tipo = 0;
        $bancoPropio = '';
        $contador = 0;
        $contadorhs = 0;
        $contadorSc = 0;
        $contadorAzt = 0;
        $clcs = array();
        $detLayBx = array();
        $detLayBv = array();
        $detLayBnI = array();
        $detLayBnE = array();
        $detLayStE = array();
        $detLayStI = array();
        $detLayHsI = array();
        $detLayHsE = array();
        $detLayIn = array();
        $detLaySc = array();
        $detLayAzt = array();
        $esLoteMixto = false;
        while ($clc = $this->extraer()) {
            if ($formaPago == 3) {
                if ($anio == 2026) {
                    $vinFolio = $e2026->consultaFolios26($clc['orden']);
                } elseif ($anio == 2025) {
                    $vinFolio = $e2025->consultaFolios25($clc['orden']);
                } elseif ($anio == 2024) {
                    $vinFolio = $e2024->consultaFolios24($clc['orden']);
                } elseif ($anio == 2023) {
                    $vinFolio = $e2023->consultaFolios23($clc['orden']);
                } elseif ($anio == 2022) {
                    $vinFolio = $e2022->consultaFolios22($clc['orden']);
                } elseif ($anio == 2021) {
                    $vinFolio = $e2021->consultaFolios21($clc['orden']);
                } elseif ($anio == 2020) {
                    $vinFolio = $e2020->consultaFolios20($clc['orden']);
                } elseif ($anio == 2019) {
                    $vinFolio = $e2019->consultaFolios19($clc['orden']);
                } elseif ($anio == 2018) {
                    $vinFolio = $e2018->consultaFolios18($clc['orden']);
                } elseif ($anio == 2017) {
                    $vinFolio = $e2017->consultaFolios17($clc['orden']);
                }
                $folios = "";
                $conexionFolios = new AccesoDatos();
                $conexionFolios->conectar();
                $conexionFolios->setSql($vinFolio);
                $conexionFolios->conectar();
                $conexionFolios->consultar();
                while ($foliopagos = $conexionFolios->extraer()) {
                    $aniof = substr($foliopagos['lot_ejercicio'], 2, 2);
                    $folios .= $aniof . $foliopagos['clc_tipoCereCo'] . $foliopagos['clc_cereCo'] . " ";
                }
                $conexionFolios->desconectar();
                $clc['folios'] = $folios;
                if ($clc['estadolc'] == 5) {
                    if ($anio == 2018) {
                        $sqlVencida = $e2018->consultaLcVencida($clc['orden']);
                    } elseif ($anio == 2019) {
                        $sqlVencida = $e2019->consultaLcVencida($clc['orden']);
                    } elseif ($anio == 2020) {
                        $sqlVencida = $e2020->consultaLcVencida($clc['orden']);
                    } elseif ($anio == 2021) {
                        $sqlVencida = $e2021->consultaLcVencida($clc['orden']);
                    } elseif ($anio == 2022) {
                        $sqlVencida = $e2022->consultaLcVencida($clc['orden']);
                    } elseif ($anio == 2023) {
                        $sqlVencida = $e2023->consultaLcVencida($clc['orden']);
                    } elseif ($anio == 2024) {
                        $sqlVencida = $e2024->consultaLcVencida($clc['orden']);
                    } elseif ($anio == 2025) {
                        $sqlVencida = $e2025->consultaLcVencida($clc['orden']);
                    } elseif ($anio == 2026) {
                        $sqlVencida = $e2026->consultaLcVencida($clc['orden']);
                    }
                    $conexionVencida = new AccesoDatos();
                    $conexionVencida->conectar();
                    $conexionVencida->setSql($sqlVencida);
                    $conexionVencida->consultar();
                    while ($lcpago = $conexionVencida->extraer()) {
                        $reemplazos = array(
                            "orden" => $lcpago['orden'], "linea_captura" => $lcpago['linea_captura'],
                            "importe" => $lcpago['importe'],
                        );
                        $lcv = array_replace($clc, $reemplazos);
                    }
                    array_push($clcs, $lcv);
                    $conexionVencida->desconectar();
                } else {
                    array_push($clcs, $clc);
                }
            } else {
                array_push($clcs, $clc);
            }

            $extrar3PrimerosNumeros = substr($clc['ctaCargo'], 0, 3);
            if (
                $extrar3PrimerosNumeros == "132" || $extrar3PrimerosNumeros == "102"
                || $extrar3PrimerosNumeros == "042"
            ) {
                $esLoteMixto = true;
            }
        }

        $suma_importesSc = 0;
        $suma_importesAzt = 0;
        if ($esLoteMixto == false) {
            foreach ($clcs as $clc) {
                $clabe = $clc['ctaCargo'];
                $clabebancoCargo = substr($clabe, 0, 3);
                $conexion2 = new AccesoDatos();
                $conexion2->conectar();
                $sql2 = 'select cbs_id from ' . BD_TESORERIA . '.ban_CuentaBancariaSefin where cbs_id= :cbs_id';
                $params2 = [
                    'cbs_id' => $clc['ctaAbono'],
                ];
                $conexion2->setSql($sql2);
                $conexion2->consultar($params2);
                $tipomov = $conexion2->getNumeroRegistros();
                $conexion2->desconectar();
                $clabebancoDestino = substr($clc['ctaAbono'], 0, 3);
                $flatExterno = 0;
                $bancoPropioIt ='';
                $ibait = '';
                #validar que el bur_id !=0000000
                switch ($clabebancoCargo) {
                    case "002":
                        $formatoLayout = "txt";
                        $arrayBx = array(
                            "ctaAbono" => $clc['ctaAbono'], "ctaCargo" => $clc['ctaCargo'], "importe" => $clc['importe'], "tipo" => $clc['tipo'],
                        );
                        $bancoPropioBx = 'BX';
                        $ibabx = 1;
                        $contador = $contador + 1;
                        if ($tipoLote == 2) {
                            $lineapdp = crea_bxPdp($clc, $contador);
                            $arrayBx += array("linea" => $lineapdp, "referencia" => $clc['documento_id']);
                            $lineabx .= $lineapdp;
                        } else {
                            $linean = crea_bx($clc, $contador);
                            $arrayBx += array("linea" => $linean, "referencia" => $clc['orden']);
                            $lineabx .= $linean;
                        }
                        $suma_importes += floatval(number_format($clc['importe'], 2, '.', ''));
                        array_push($detLayBx, $arrayBx);
                        $total_transacciones++;
                        break;
                    case "012":
                        $formatoLayout = "txt";
                        $bancoPropioBv = 'BB';
                        $ibabb = 2;
                        $lineabb = BANCOMER($clc, $tipomov);
                        $arrayBb = array(
                            "ctaAbono" => $clc['ctaAbono'], "ctaCargo" => $clc['ctaCargo'],
                            "referencia" => $clc['orden'], "importe" => $clc['importe'], "tipo" => $clc['tipo'],
                            "linea" => $lineabb,
                        );
                        $lineabv .= $lineabb;
                        array_push($detLayBv, $arrayBb);
                        break;
                    case "072":
                    case "037":
                        $formatoLayout = "txt";
                        $ibabn = 7;
                        $clabeDestino = substr($clc["ctaAbono"], 0, 3);
                        $clabeCargo = substr($clc["ctaCargo"], 0, 3);

                        if (($clabeDestino == $clabeCargo) || ($clabeDestino == "072" || $clabeDestino == "037")) {
                            $bancoPropioBni = 'BN-INT';
                            $lineabnti = BANORTE_INTERNO($clc);
                            $arrayBnI = array(
                                "ctaAbono" => $clc['ctaAbono'], "ctaCargo" => $clc['ctaCargo'],
                                "referencia" => $clc['orden'], "importe" => $clc['importe'], "tipo" => $clc['tipo'],
                                "linea" => $lineabnti,
                            );
                            array_push($detLayBnI, $arrayBnI);
                            $lineabni .= $lineabnti;
                        } else {
                            $bancoPropioBne = 'BN-EXT';
                            $lineabnte = BANORTE_EXTERNO($clc);
                            $arrayBnE = array(
                                "ctaAbono" => $clc['ctaAbono'], "ctaCargo" => $clc['ctaCargo'],
                                "referencia" => $clc['orden'], "importe" => $clc['importe'], "tipo" => $clc['tipo'],
                                "linea" => $lineabnte,
                            );
                            array_push($detLayBnE, $arrayBnE);
                            $lineabne .= $lineabnte;
                        }
                        break;
                    case "014":
                        $formatoLayout = "txt";
                        $ibast = 5;
                        if ($clabebancoCargo == $clabebancoDestino) {
                            $bancoPropioSi = "ST-INT";
                            $lineastit = SANTANDER_INTERNO($clc);
                            $arrayStI = array(
                                "ctaAbono" => $clc['ctaAbono'], "ctaCargo" => $clc['ctaCargo'],
                                "referencia" => $clc['orden'], "importe" => $clc['importe'], "tipo" => $clc['tipo'],
                                "linea" => $lineastit,
                            );
                            array_push($detLayStI, $arrayStI);
                            $lineasti .= $lineastit;
                        } else {
                            $flatExterno = 1;
                            $bancoPropioSe = "ST-EXT";
                            $lineastet = SANTANDER_EXTERNO($clc);
                            $arrayStE = array(
                                "ctaAbono" => $clc['ctaAbono'], "ctaCargo" => $clc['ctaCargo'],
                                "referencia" => $clc['orden'], "importe" => $clc['importe'], "tipo" => $clc['tipo'],
                                "linea" => $lineastet,
                            );
                            array_push($detLayStE, $arrayStE);
                            $lineaste .= $lineastet;
                        }
                        break;
                    case "021":
                        $formatoLayout = "csv";
                        $ibahs = 3;
                        if ($clabebancoCargo == $clabebancoDestino) {
                            $contadorhs = $contadorhs + 1;
                            $bancoPropioHi = 'HS_INT';
                            $lineahit = HSBC_INTERNO($clc, $contadorhs);
                            $arrayhsI = array(
                                "ctaAbono" => $clc['ctaAbono'], "ctaCargo" => $clc['ctaCargo'],
                                "referencia" => $clc['orden'], "importe" => $clc['importe'], "tipo" => $clc['tipo'],
                                "linea" => $lineahit,
                            );
                            array_push($detLayHsI, $arrayhsI);
                            $lineahi .= $lineahit;
                            $total_transaccioneshs++;
                        } else {
                            $contadorEx = $contador + 1;
                            $bancoPropioHe = 'HS_EXT';
                            $lineahet = HSBC_EXTERNO($clc);
                            $arrayHsE = array(
                                "ctaAbono" => $clc['ctaAbono'], "ctaCargo" => $clc['ctaCargo'],
                                "referencia" => $clc['orden'], "importe" => $clc['importe'], "tipo" => $clc['tipo'],
                                "linea" => $lineahet,
                            );
                            array_push($detLayHsE, $arrayHsE);
                            $lineahe .= $lineahet;
                        }
                        break;
                    case "044":
                        $formatoLayout = "txt";
                        $ibasc = 4;
                        $bancoPropioSc = 'SC';
                        $contador = $contadorSc + 1;
                        $lineaSct = SCOTIABANK($clc, $contador);
                        $lineaScotiabank .= $lineaSct;
                        $arraySc = array(
                            "ctaAbono" => $clc['ctaAbono'], "ctaCargo" => $clc['ctaCargo'],
                            "referencia" => $clc['orden'], "importe" => $clc['importe'], "tipo" => $clc['tipo'],
                            "linea" => $lineaSct,
                        );
                        array_push($detLaySc, $arraySc);
                        $suma_importesSc += floatval(number_format($clc['importe'], 2, '.', ''));
                        $total_transaccionesSC++;
                        break;
                    case "127":
                        $formatoLayout = "txt";
                        $ibaazt = 9;
                        $bancoPropioAzt = 'AZT';
                        $contadorAzt = $contadorAzt + 1;
                        $conexion3 = new AccesoDatos();
                        $conexion3->conectar();
                        $conexion3->setSql('SELECT cbs_cuenta FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin WHERE cbs_id= :cbs_id');
                        $paramsCon3 = ['cbs_id' => $clc['ctaCargo']];
                        $conexion3->consultar($paramsCon3);
                        $cbs_cuenta = $conexion3->obtener();
                        $conexion3->desconectar();
                        //Reemplazar las primeros 4 digitos de la cuenta por 0000 Ticket #SDS-429556
                        $clc["cbs_cuenta"] = '0000' . substr($cbs_cuenta->cbs_cuenta, 4);
                        $clc["tipoLote"] = $tipoLote;

                        $lineaAzteca = AZTECA($clc, $contadorAzt);
                        $lineaAzt .= $lineaAzteca;
                        $arrayAzt = array(
                            "ctaAbono" => $clc['ctaAbono'], "ctaCargo" => $clc['ctaCargo'],
                            "referencia" => $clc['orden'], "importe" => $clc['importe'], "tipo" => $clc['tipo'],
                            "linea" => $lineaAzteca,
                        );
                        array_push($detLayAzt, $arrayAzt);
                        $suma_importesAzt += floatval(number_format($clc['importe'], 2, '.', ''));
                        $total_transaccionesAZT++;
                        break;
                }
            }
            $nombres = '';
            if (!empty($lineabx)) {
                $statusLay = $this->creaArchivoLay($lineabx, $suma_importes, $total_transacciones, $layoutDetalles, $bancoPropioBx, $lotes, $tipoLote, $ibabx, $detLayBx, $formatoLayout);
                $nombres .= $statusLay . "--";
            }
            if (!empty($lineabv)) {
                $statusLay = $this->creaArchivoLay($lineabv, $suma_importes, $total_transacciones, $layoutDetalles, $bancoPropioBv, $lotes, $tipoLote, $ibabb, $detLayBv, $formatoLayout);
                $nombres .= $statusLay . " --";
            }
            if (!empty($lineabni)) {
                $statusLay = $this->creaArchivoLay($lineabni, $suma_importes, $total_transacciones, $layoutDetalles, $bancoPropioBni, $lotes, $tipoLote, $ibabn, $detLayBnI, $formatoLayout);
                $nombres .= $statusLay . "--";
            }
            if (!empty($lineabne)) {
                $statusLay = $this->creaArchivoLay($lineabne, $suma_importes, $total_transacciones, $layoutDetalles, $bancoPropioBne, $lotes, $tipoLote, $ibabn, $detLayBnE, $formatoLayout);
                $nombres .= $statusLay . "--";
            }
            if (!empty($lineasti)) {
                $statusLay = $this->creaArchivoLay($lineasti, $suma_importes, $total_transacciones, $layoutDetalles, $bancoPropioSi, $lotes, $tipoLote, $ibast, $detLayStI, $formatoLayout);
                $nombres .= $statusLay . " -- ";
            }
            if (!empty($lineaste)) {
                $statusLay = $this->creaArchivoLay($lineaste, $suma_importes, $total_transacciones, $layoutDetalles, $bancoPropioSe, $lotes, $tipoLote, $ibast, $detLayStE, $formatoLayout);
                $nombres .= $statusLay . " -- ";
            }
            if (!empty($lineahi)) {
                $statusLay = $this->creaArchivoLay($lineahi, $suma_importes, $total_transaccioneshs, $layoutDetalles, $bancoPropioHi, $lotes, $tipoLote, $ibahs, $detLayHsI, "csv");
                $nombres .= $statusLay . "-- ";
            }
            if (!empty($lineahe)) {
                $statusLay = $this->creaArchivoLay($lineahe, $suma_importes, $total_transacciones, $layoutDetalles, $bancoPropioHe, $lotes, $tipoLote, $ibahs, $detLayHsE, "csv");
                $nombres .= $statusLay . " \t ";
            }
            if (!empty($lineaIn)) {
                $statusLay = $this->creaArchivoLay($lineaIn, $suma_importes, $total_transacciones, $layoutDetalles, $bancoPropioIt, $lotes, $tipoLote, $ibait, $detLayIn, $formatoLayout);
                $nombres .= $statusLay . " -- ";
            }
            if (!empty($lineaAzt)) {
                $statusLay = $this->creaArchivoLay($lineaAzt, $suma_importes, $total_transacciones, $layoutDetalles, $bancoPropioAzt, $lotes, $tipoLote, $ibaazt, $detLayAzt, $formatoLayout);
                $nombres .= $statusLay . " -- ";
            }
            if (!empty($lineaScotiabank)) {
                $salto = chr(13) . chr(10);
                $tipo_archivo_cab_gral = "EE"; //CONSTANTE. ARCHIVO DE MOVIMIENTOS DE ENTRADA
                $tipo_registro_cab_gral = "HA"; //CONSTANTE.
                $num_contrato = "06646"; //CONSTANTE.
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $sql = "select * from " . BD_TESORERIA . ".lot_Layout where iba_id=4 and date(lay_fechaSistema)=date(now());";
                $conexion->setSql($sql);
                $conexion->consultar();
                $secuencia = $conexion->getNumeroRegistros();
                $secuencia++;
                $secuencia = str_pad($secuencia, 2, "0", STR_PAD_LEFT);

                //ESTOS CAMPOS LOS OCUPA SCOTIA PARA LA RESPUESTA///////////////////////
                $fecha_generacion_archivo = str_repeat("0", 8); //CONSTANTE, 8 CERO´S
                $fecha_inicial = str_repeat("0", 8); //CONSTANTE, 8 CERO´S
                $fecha_final = str_repeat("0", 8); //CONSTANTE, 8 CERO´S
                $codigo_status_registro_cab_gral = str_repeat("0", 3); //CONSTANTE, 3 CERO´S
                $filler_cab_gral = str_repeat(" ", 332); //CONSTANTE. 332 ESPACIOS
                ////////////////////////////////////////////////////////////////////////
                $encabezado_general = $tipo_archivo_cab_gral .
                    $tipo_registro_cab_gral .
                    $num_contrato .
                    $secuencia .
                    $fecha_generacion_archivo .
                    $fecha_inicial .
                    $fecha_final .
                    $codigo_status_registro_cab_gral .
                    $filler_cab_gral . $salto;
                ///////////////////////////////////////////////////////////////RESUMEN
                $tipo_archivo_res_gral = "EE"; //CONSTANTE. ARCHIVO DE MOVIMIENTOS DE ENTRADA
                $tipo_registro_res_gral = "TA"; //CONSTANTE.
                $tot_mov_altas_res_gral = str_pad($total_transaccionesSC, 7, "0", STR_PAD_LEFT);
                $importe_mov_altas_res_gral = str_pad($suma_importesSc * 100, 17, "0", STR_PAD_LEFT);
                $tot_mov_bajas_res_gral = str_repeat("0", 7);
                $importe_mov_bajas_res_gral = str_repeat("0", 17);

                //ESTOS CAMPOS LOS OCUPA SCOTIA PARA LA RESPUESTA///////////////////////
                $codigos_movimientos_res_gral = str_repeat("0", 198); //CONSTANTE, 3 CERO´S
                $filler_res_gral = str_repeat(" ", 120); //CONSTANTE. 123 ESPACIOS
                ////////////////////////////////////////////////////////////////////////
                $resumen_general = $tipo_archivo_res_gral .
                    $tipo_registro_res_gral .
                    $tot_mov_altas_res_gral .
                    $importe_mov_altas_res_gral .
                    $tot_mov_bajas_res_gral .
                    $importe_mov_bajas_res_gral .
                    $codigos_movimientos_res_gral .
                    $filler_res_gral .
                    $salto;
                $lineaFinal = $encabezado_general .
                    $lineaScotiabank .
                    $resumen_general;
                $statusLay = $this->creaArchivoLay($lineaFinal, $suma_importesSc, $total_transaccionesSC, $layoutDetalles, $bancoPropioSc, $lotes, $tipoLote, $ibasc, $detLaySc, $formatoLayout);
                $nombres .= $statusLay . " -- ";
            }
            if ($statusLay != false) {
                foreach ($layoutDetalles as $key => $value) {
                    $this->prm->accionLote = 'banca';
                    $this->prm->fpa_id = $value->fpa_id;
                    $this->prm->msc_id = $value->msc_id;
                    $this->prm->ejercicio = $value->lot_ejercicio;
                    $this->prm->lot_id = $value->lot_id;
                    $this->cambiaEstadoLote();
                }
                $this->mixScript('notify("LAYOUT GENERADO : ' . $nombres . ' ","success",4000)');
                $this->mixScript('
                    comunica("' . $this->pantalla . '",{
                        metodo:"llenaTablaGenLayout",
                        ejercicio:$("#ejercicio").val(),
                        lotePago:$("#lotePago").val(),
                        fechaMinAut:$("#fechaMinAut").val(),
                        fechaMaxAut:$("#fechaMaxAut").val(),
                        fechaMinPago:$("#fechaMinPago").val(),
                        fechaMaxPago:$("#fechaMaxPago").val(),
                        tipo:$("#tipoLote").val()
                    })');
            } else {
                $this->mixScript('notify("ERROR AL GENERAR LAYOUT NOTIFIQUE AL ADMINISTRADOR ","error")');
            }
        } else {
            $this->mixScript('
                notify("Error al generar layout, el lote es mixto","warning",4000);
            ');
        }
    }
    /**
     * funcion generaPass
     * Encargada de generar un password para cada uno de los layouts generados
     * @access private
     * @return pass retorna el password generado
     */
    private function generaPass()
    {
        $cadena = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        $longitudCadena = strlen($cadena);
        $pass = "";
        $longitudPass = 10;
        for ($i = 1; $i <= $longitudPass; $i++) {
            $pos = rand(0, $longitudCadena - 1);
            $pass .= substr($cadena, $pos, 1);
        }
        return $pass;
    }
    /**
     * funcion getLayouts
     * Define la pantalla principal para la busqueda de todos los layouts generados
     * @access private
     */
    private function getLayouts()
    {
        $this->mixScript('
                DesactivaAtajosMenu();
                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
                e.stopPropagation(); });

                $("#homeMenu").click( function(e) {
                        $("#contenedor").html("");
                        $("#toolsPantalla").html("");
                        ActivaAtajosMenu();
                });
                $("#btnregresarindex").removeClass("btn-default");
        ');

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->color = "gris";
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->icon = "circle-left me-1";
        $btnregresarindex->click = '
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                // Regresa a la pantalla anterior
                comunica("' . $this->pantalla . '",{
                        metodo: "main"
                });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));
        $inputFechaGeneracion = new StdClass();
        $inputFechaGeneracion->id = 'fechaLayout';
        $inputFechaGeneracion->name = 'fechaLayout';
        $inputFechaGeneracion->placeholder = 'FECHA DE GENERACIÓN';
        $inputFechaGeneracion->enDiv = true;
        $inputFechaGeneracion->label = 'FECHA DE GENERACIÓN';
        $inputFechaGeneracion->formato = 'fecha';
        $sqlTipoLayout = '
                SELECT
                        l.tla_id,l.tla_nombre
                FROM
                        ' . BD_TESORERIA . '.cat_TipoLayout AS l;
        ';
        $inputTipoLayout = new StdClass();
        $inputTipoLayout->sql = $sqlTipoLayout;
        $inputTipoLayout->id = 'tipoLayout';
        $inputTipoLayout->name = 'tipoFondeo';
        $inputTipoLayout->mensaje = 'SELECCIONE TIPO DE LAYOUT';
        $inputTipoLayout->enDiv = true;
        $inputTipoLayout->label = 'TIPO DE LAYOUTS';

        $inputLotes = new StdClass();
        $inputLotes->sql = $sqlTipoLayout;
        $inputLotes->id = 'lotesLayout';
        $inputLotes->name = 'LOTE';
        $inputLotes->enDiv = true;
        $inputLotes->label = 'LOTES';

        $btnBuscar = new stdClass();
        $btnBuscar->id = "btnBuscarFondeo";
        $btnBuscar->name = "btnBuscarFondeo";
        $btnBuscar->color = "gris";
        $btnBuscar->value = "Buscar";
        $btnBuscar->icon = "fa fa-search";
        $btnBuscar->click = '
                comunica("' . $this->pantalla . '",{
                        metodo:"llenaTablaLayouts",
                        tipoLayout:$("#tipoLayout").val(),
                        fechaGeneracion:$("#fechaLayout").val(),
                        lotes:$("#lotesLayout").val()

                })';
        $tabla = new stdClass();
        $tabla->id = 'tblLayouts';
        $tabla->titulo = 'LAYOUT´S GENERADOS';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaLayouts';
        $tabla->comunica->lotes = '{$("#lotes").val()}';
        $tabla->comunica->fechaGeneracion = '{$("#fechaLayout").val()}';
        $tabla->comunica->tipoLayout = '{$("#tipoLayout").val()}';

        /* Array para dibujar la tabla / */
        $tr = array();
        //Encabezados
        $th = new stdClass();
        $th->texto = 'DETALLE';

        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EJERCICIO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        $th->width = '10rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ARCHIVO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA GENERACIÓN';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'NUMERO DE LOTES';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DESCARGAR';
        $th->width = '4rem';
        $th->align = 'center';
        array_push($tr, $th);
        //Array de cuerpo de la tabla
        $trbody = array();

        //Cuerpo de la tabla
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'fa fa-search';
        $td->class = 'lotDetLayout';
        $td->attr = new stdClass();
        $td->attr->lay_id = 'lay_id';
        $td->attr->lay_archivo = 'lay_archivo';
        $td->attr->tla_id = 'tla_id';
        $td->attr->ejercicio = 'ejercicio';

        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ejercicio';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tla_nombre';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lay_archivo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lay_fechaSistema';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'num_lotes';
        $td->align = 'center';
        array_push($trbody, $td);

        //Botón descarga
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->class = 'Downloadb';
        $td->campoTxt = 'Download';
        $td->color = 'verde';
        $td->icono = 'fa fa-download';
        $td->attr = new stdClass();
        $td->attr->fpa_id = 'fpa_id';
        $td->attr->msc_id = 'msc_id';
        $td->attr->lot_ejercicio = 'ejercicio';
        $td->attr->lot_id = 'lot_id';
        $td->attr->lai_id = 'lay_id';
        $td->aling = 'center';
        array_push($trbody, $td);

        /* Llenamos tabla / */
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $html = '
                <div class="row">
                        <legend>LAYOUTS GENERADOS</legend>
                </div>
                <div class="row mb-4">
                        <fieldset class="col-xs-3 col-md-3">
                                ' . $this->ponCombo($inputTipoLayout) . '
                        </fieldset>
                         <fieldset class="col-xs-3 col-md-3">
                                ' . $this->ponInput($inputLotes) . '
                        </fieldset>

                </div>
                <div class="row mb-4">
                        <fieldset class="col-xs-3 col-md-3">
                                ' . $this->ponInput($inputFechaGeneracion) . '
                        </fieldset>
                </div>

                <div class="row mb-4">
                        <fieldset class="col-xs-3 col-md-3">
                                ' . $this->ponBoton($btnBuscar) . '
                        </fieldset>
                </div>
                <br>
                <div class="row">
                        <fieldset class="col-xs-12 col-md-12">
                                ' . $this->wrPonTabla($tabla) . '
                        </fieldset>
                </div>
            ';
        $this->mixHtml($html);
    }
    /**
     * funcion llenaTablaLayouts
     * Muestra todos los layouts generados, de acuerdo a los parametros de busqueda, recibidos
     * @access private
     * @param tipo int tipo de lote
     * @param lotes string cadena de los lotes a buscar
     * @param fecha date fecha de la generación del layout
     */
    private function llenaTablaLayouts()
    {
        $tipo = isset($this->prm->tipoLayout) ? $this->prm->tipoLayout : "";
        $lotes = isset($this->prm->lotes) ? $this->prm->lotes : "";
        $fecha = isset($this->prm->fechaGeneracion) ? $this->prm->fechaGeneracion : "";
        $sql = 'SELECT
            lay_id as lay_id,
            la.lar_archivo as lay_archivo,
            vl.lot_ejercicio as ejercicio,
            tl.tla_nombre,
            l.lay_fechaSistema,
            COUNT(*) as num_lotes,
            l.usr_id,
            vl.fpa_id,
            la.lar_numeroCopia as copias,
            la.msc_id,
            l.tla_id
            FROM
            ' . BD_TESORERIA . '.lot_Layout as l
            INNER JOIN ' . BD_TESORERIA . '.vin_LoteLayout vl
            using(lay_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLayout tl
            using(tla_id)
            INNER JOIN ' . BD_TESORERIA . '.lot_LayoutArchivo as la
            using(lay_id)
            INNER JOIN (
                SELECT lay_id
                FROM ' . BD_TESORERIA . '.lot_LayoutArchivoDetalle
                GROUP BY lay_id
            ) as det using(lay_id)
            WHERE
            l.ela_id = 1
            ';
        $parametrosLtl = [];

        if (!empty($tipo)) {
            $parametrosLtl += [
                'tla_id' => $tipo,
            ];
            $sql .= ' AND l.tla_id= :tla_id';
        }

        if (!empty($fecha)) {
            $fecha = DateTime::createFromFormat('d/m/Y', $fecha);
            $parametrosLtl += [
                'lay_fechaSistema' => $fecha->format('Y-m-d'),
            ];
            $sql .= ' AND  l.lay_fechaSistema= :lay_fechaSistema';
        }

        if (!empty($lotes)) {
            $lotes = strtoupper($lotes);
            $nlotes = explode(",", $lotes);
            $findLotes = '';
            $lote = '';
            $fpa = 0;
            foreach ($nlotes as $value) {
                $fpa_id = substr($value, 0, 1);
                switch ($fpa_id) {
                    case 'T':
                        $fpa = 1;
                        break;
                    case 'C':
                        $fpa = 2;
                        break;
                    case 'L':
                        $fpa = 3;
                        break;
                }
                $mes = substr($value, 1, 2);
                $lot = substr($value, 3, 4);
                $ejercicio = substr($value, 7, 2);
                $ejercicio = "20" . $ejercicio;
                $lote .= "(" . $fpa . "," . $mes . "," . $ejercicio . "," . $lot . "),";
            }

            $findLotes = substr($lote, 0, -1);

            $sql .= "  AND (vl.fpa_id,vl.msc_id,vl.lot_ejercicio,vl.lot_id) "
                . "in ( ".$findLotes.")";
        }
        $parametrosLtl += [
            'lay_anioSinpres' => APP_EJERCICIO,
        ];
        $sql .= "
            AND l.lay_anioSinpres = :lay_anioSinpres
         GROUP BY lay_id, la.lar_archivo,lot_ejercicio,tla_nombre,lay_fechaSistema,l.usr_id,fpa_id,la.lar_numeroCopia,la.msc_id,l.tla_id";

        $this->conectar();
        $this->setSql($sql);
        $this->setParams($parametrosLtl);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tblLayouts';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".Downloadb").click(function(){
                $.ajax({
                    url : "lib/tes/downloadLayout.php",
                    data : {
                        fpa_id : $(this).attr("fpa_id"),
                        msc_id:$(this).attr("msc_id"),
                        lot_ejercicio: $(this).attr("lot_ejercicio"),
                        lot_id:$(this).attr("lot_id"),
                        lai_id:$(this).attr("lai_id")
                    },
                    // especifica si será una petición POST o GET
                    type : "POST",
                    // el tipo de información que se espera de respuesta
                    dataType : "json",
                    success : function(json) {
                        var form = $("<form>")
                            .attr("action","lib/file_download.php?r="+json.fichero+"&type_Disposition=\'attachment\' ")
                            .attr("method", "post")
                            .attr("target", "_blank")
                        $("body").append(form);
                        form.submit();
                    }, error : function(xhr, status) {

                    }
                });
             });
            $(".lotDetLayout").click(function(){
                comunica("' . $this->pantalla . '",{
                metodo: "tablaDetLayout",
                laiD_id: $(this).attr("lay_id"),
                nombre:$(this).attr("lay_archivo"),
                tipo:$(this).attr("tla_id"),
                ejercicio:$(this).attr("ejercicio"),
                tipoLayout:"' . $tipo . '",
                lotes:"' . $lotes . '",
                fechaGeneracion:"' . $this->prm->fechaGeneracion . '"

                });
            });
        ');
    }
    /**
     * funcion creaArchivoLay
     * Se encarga de escribir en un archivo las lineas que conformaran el layout de pago
     * @access private
     * @param linea1 string linea(s) que conforma el archivo a crear
     * @param suma_importe suma de los importes del layout
     * @param total_transacciones total de movimientos bancarios
     * @param layoutDetalles array contiene cada uno de los parametros para realizar el registro
     * del layout de pago en la tabla lot_LoteLayout
     * @param bancoPropio string define la siglas del banco para el nombre del layout
     * @param lotes lote(s) de los cuales se tendra que realizar la vinculación al o los layouts a generar
     * @param tipoLote identificador del tipo de lote, que en funcion de este se define el tipo de layout
     * @param iba_id identificador de la institucion bancaria
     * @param detalle array que almacena el detalle del layout generado
     * @param return nombre del layout
     */
    private function creaArchivoLay($linea1, $suma_importes, $total_transacciones, $layoutDetalles, $bancoPropio, $lotes, $tipoLote, $iba, $detalle, $formatoLayout)
    {
        $espacio = chr(32);
        $sql2 = 'SELECT CURDATE()as fecha,DAY(CURDATE())as dia,msc_id,msc_nombre as mes
        from ' . BD_TESORERIA . '.cat_Mes where msc_id=MONTH(CURDATE());';
        $this->setSql($sql2);
        $this->consultar();
        $fechas = $this->extraer();
        $fecha = $fechas['fecha'];
        $idmes = $fechas['msc_id'];
        $dia = $fechas['dia'];
        $dia = $dia < 10 ? '0' . $dia : $dia;
        $lay = 'SELECT MAX(lay_id)as id FROM ' . BD_TESORERIA . '.lot_Layout for UPDATE;';
        $this->setSql($lay);
        $this->consultar();
        $id_lay = $this->extraer();
        $id_lay = $id_lay['id'] + 1;
        $usr = $_SESSION[_S]['usr_id'];
        $nombre_layout = $bancoPropio . substr($fecha, 0, 4) . $idmes . $dia . '-' . $usr . '-' . $id_lay . "." . $formatoLayout;
        $directorioLayout = 'Layout/' . substr($fecha, 0, 4) . '/' . $idmes . '/' . $dia . '/' . $usr;
        if (!file_exists($directorioLayout)) {
            mkdir($directorioLayout, 0777, true);
        }
        $this->lay = new stdClass();
        $this->lay->fechaSistema = $fecha;
        $this->lay->iba_id = $iba;
        $this->lay->anioSinpres = substr($fecha, 0, 4);
        $this->lay->propuesta = "S/N";
        $this->lay->archivo = $nombre_layout;
        $this->lay->password = $this->generaPass();
        $this->lay->observacion = "REGISTRADO";
        $this->lay->tla_id = $tipoLote;
        $this->lay->ela_id = 1;
        $this->lay->abc = 'a';

        //Obtener la longitud de $layoutDetalles ya que la funcion COUNT nos devuelve un error de tipo advertencia
        // indicandonos que debe ser un arreglo o un objeto, pero esta variable su primer nivel es un arreglo
        // y el segundo son objetos.
        $i = 0;
        foreach ($layoutDetalles as $key => $value) {
            $i++;
        }
        if ($i > 0) {
            $this->prm->lay_id = Layout::abc($this->lay);
            $id_real = $this->prm->lay_id;
            $this->prm->abc = 'a';
            //guardando ruta del archivo en lay_Archivo
            $layArchivo = new stdClass();
            $layArchivo->lay_id = $id_real;
            $layArchivo->ejercicio = substr($fecha, 0, 4);
            $layArchivo->msc_id = $idmes;
            $layArchivo->dia = $dia;
            $nombre_layout = $bancoPropio . substr($fecha, 0, 4) . $idmes . $dia . '-' . $usr . '-' . $id_real . "." . $formatoLayout;
            $layArchivo->archivo = $nombre_layout;
            $layArchivo->numeroCopia = 1; //** definir si se descarga al mormento de generar
            $layArchivo->abc = 'a';
            LayoutArchivo::abc($layArchivo);
            foreach ($layoutDetalles as $key => $value) {
                $this->prm->lay_id = $id_real;
                $this->prm->accionLote = 'banca';
                $this->prm->fpa_id = $value->fpa_id;
                $this->prm->msc_id = $value->msc_id;
                $this->prm->ejercicio = $value->lot_ejercicio;
                $this->prm->lot_id = $value->lot_id;
                LoteLayout::abc($this->prm);
            }
            $nombre_real = $bancoPropio . substr($fecha, 0, 4) . $idmes . $dia . '-' . $usr . '-' . $id_real . "." . $formatoLayout;
            $file = fopen($directorioLayout . '/' . $nombre_real, "w");
            switch ($bancoPropio) {
                case 'BX':
                    $total_formato = number_format($suma_importes, 2, '.', '');
                    $linea2 = registro_totales($total_transacciones, $total_formato);
                    fwrite($file, $linea1 . $linea2);
                    fclose($file);
                    break;
                case 'HS_INT':
                    $numBloque = 1;
                    $total_trans = $total_transacciones;
                    $concepto = "";
                    $tab = ',';
                    limpiar($concepto, "esp");
                    $linea2 = $numBloque . $tab . $total_trans;
                    fwrite($file, $linea1 . $linea2);
                    fclose($file);
                    break;
                default:
                    fwrite($file, $linea1);
                    break;
            }
            if (file_exists($directorioLayout . "/" . $nombre_real)) {
                // Cambiar de ubicacion el archivo
                $archivo = array("name" => $nombre_real, "tmp_name" => $directorioLayout . '/' . $nombre_real, "eslocal" => true, "error" => false);

                $up = new FicheroSefip();
                $up->setTipos(array($formatoLayout));
                $up->setModulo('tes');
                $up->setfile($nombre_real);
                $up->setRuta('/' . $directorioLayout);
                $up->setUrFichero($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id']);
                $up->setAnioFichero(APP_EJERCICIO);
                $up->setFichero($archivo);

                if ($up->upload()) {
                    // Se subio correctamente el archivo
                } else {
                    $this->mixScript('notify("No se logró cargar el archivo","warning");');
                    return false;
                }
                // Fin
                $this->lay->lay_id = $id_real;
                $this->lay->archivo = $directorioLayout . "/" . $nombre_real;
                $this->lay->abc = 'c';
                Layout::abc($this->lay);
                foreach ($detalle as $key => $value) {
                    $this->detalle = new stdClass();
                    $this->detalle->lay_id = $id_real;
                    $this->detalle->ejercicio = APP_EJERCICIO;
                    $this->detalle->msc_id = $idmes;
                    $this->detalle->dia = $dia;
                    $this->detalle->importe = $value['importe'];
                    $this->detalle->cre_id = $value['tipo'];
                    $this->detalle->cbs_id = $value['ctaCargo'];
                    $this->detalle->cbb_id = $value['ctaAbono'];
                    $this->detalle->referenciaAPagar = $value['referencia'];
                    $this->detalle->cadena = $value['linea'];
                    $this->detalle->abc = 'a';
                    LayoutArchivoDetalle::abc($this->detalle);
                    if ($value['tipo'] == 5) {
                        $this->lineac = new stdClass();
                        $this->lineac->lca_id = $value['referencia'];
                        $this->lineac->ecl_id = 2;
                        $this->lineac->abc = 'c';
                        LineaCaptura::abc($this->lineac);
                    }
                }
                return $nombre_real;
            } else {
                $this->lay->lay_id = $id_real;
                $this->lay->observacion = "CANCELADO POR EL SISTEMA: ARCHIVO NO ENCONTRADO";
                $this->lay->ela_id = 2;
                $this->lay->abc = 'c';
                Layout::abc($this->lay);
                return false;
            }
        }
    }
    /**
     * funcion tablaDetLayout
     * Define la vista principal de la tabla del detalle del layout
     * @access private
     */
    private function tablaDetLayout()
    {
        $laiD = isset($this->prm->laiD_id) ? $this->prm->laiD_id : "";
        $nombre = isset($this->prm->nombre) ? $this->prm->nombre : "";
        $tipoLayout = isset($this->prm->tipoLayout) ? $this->prm->tipoLayout : "";
        $fechaGeneracion = isset($this->prm->fechaGeneracion) ? $this->prm->fechaGeneracion : "";
        $lotes = isset($this->prm->lotes) ? $this->prm->lotes : "";
        $this->mixScript('
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
            e.stopPropagation(); });

            $("#homeMenu").click(function(e) {
                    $("#contenedor").html("");
                    $("#toolsPantalla").html("");
                    ActivaAtajosMenu();
            });
            $("#btnRegresar").removeClass("btn-default");
        ');
        $this->agregaTools('');
        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->name = 'btnRegresar';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->type = 'button';
        $btnRegresar->color = "gris";
        $btnRegresar->icon = "fa fa-circle-left me-1";
        $btnRegresar->click = '
                comunica("' . $this->pantalla . '",{
                        metodo: "getLayouts"
                });
                 comunica("' . $this->pantalla . '",{
                        metodo:"llenaTablaLayouts",
                        tipoLayout:"' . $tipoLayout . '",
                        lotes:"' . $lotes . '",
                        fechaGeneracion:"' . $fechaGeneracion . '"
                });


        ';
        $this->agregaTools($this->ponBoton($btnRegresar));

        $tabla = new stdClass();
        $tabla->id = 'tblDetalleLayout';
        $tabla->titulo = 'DETALLE DEL LAYOUT:  ' . $nombre;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDetLayout';
        $tabla->numFilas = 5;
        $tabla->comunica->laiD_id = $laiD;
        $tabla->comunica->nombre = $nombre;
        $tabla->comunica->tipo = $this->prm->tipo;
        $tabla->comunica->ejercicio = $this->prm->ejercicio;

        /* Array para dibujar la tabla / */
        $tr = array();

        /* Encabezados / */
        $th = new stdClass();
        $th->texto = 'LOTE';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UR';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FOLIO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RESPONSABLE';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BENEFICIARIO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA_CARGO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA_DESTINO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        $th->width = '10rem';
        $th->align = 'center';
        array_push($tr, $th);

        /* Array de cuerpo de la tabla / */
        $trbody = array();

        /* Cuerpo de la tabla / */
        $td = new stdClass();
        $td->campoTxt = 'layDet_lote';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'layDet_ur';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'layDet_clc';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'layDet_tipo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'layDet_folio';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'layDet_importe';
        $td->align = 'center';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'layDet_responsable';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'layDet_beneficiario';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'layDet_CtaCargo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'layDet_CtaAbono';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'layDet_concepto';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'layDet_fecha';
        $td->align = 'center';
        array_push($trbody, $td);

        /* Llenamos tabla / */
        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $html = '
                <div class="row">
                        <legend>DETALLES DEL LAYOUT</legend>
                </div>

                <div class="row">
                        <fieldset class="col-xs-12 col-md-12">
                                ' . $this->wrPonTabla($tabla) . '
                        </fieldset>
                </div>
            ';
        $this->llenaTablaDetLayout();
    }
    /**
     * funcion llenaTablaDetLayout
     * Encargada de mostrar la información de todos los layouts generados en estado 1, de acuerdo a los
     * parametros de busqueda realizados
     * @access private
     * @param laiD id del layout a detallar
     * @param tipo id del tipo de layout
     * @param ejercicio anio del ejercicio de la información
     * @param archivo nombre del archivo a detallar
     */
    private function llenaTablaDetLayout()
    {
        $laiD = isset($this->prm->laiD_id) ? $this->prm->laiD_id : "";
        $tipo = isset($this->prm->tipo) ? $this->prm->tipo : "";
        $ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : "";
        $archivo = isset($this->prm->nombre) ? $this->prm->nombre : "";
        $e2026 = new clcs2026;
        $e2025 = new clcs2025;
        $e2024 = new clcs2024;
        $e2023 = new clcs2023;
        $e2022 = new clcs2022;
        $e2021 = new clcs2021;
        $e2020 = new clcs2020;
        $e2019 = new clcs2019;
        $e2018 = new clcs2018;
        $e2017 = new clcs2017;
        $conexiontipo = new AccesoDatos();
        $conexiontipo->conectar();
        $sqltip = " SELECT fpa_id FROM " . BD_TESORERIA . ".vin_LoteLayout WHERE lay_id= :lay_id";
        $paramTip = [
            'lay_id' => $laiD,
        ];
        $conexiontipo->setSql($sqltip);
        $conexiontipo->consultar($paramTip);
        $modoPago = 0;
        while ($m = $conexiontipo->extraer()) {
            $modoPago = $m['fpa_id'];
        }

        if ($modoPago == 3) {
            if ($ejercicio == 2017) {
                $sql = $e2017->getDetLayoutLc2017($laiD, $tipo, $ejercicio);
            } elseif ($ejercicio == 2018) {
                $sql = $e2018->getDetLayoutLc2018($laiD, $tipo, $ejercicio);
            } elseif ($ejercicio == 2019) {
                $sql = $e2019->getDetLayoutLc2019($laiD, $tipo, $ejercicio);
            } elseif ($ejercicio == 2020) {
                $sql = $e2020->getDetLayoutLc2020($laiD, $tipo, $ejercicio);
            } elseif ($ejercicio == 2021) {
                $sql = $e2021->getDetLayoutLc2021($laiD, $tipo, $ejercicio);
            } elseif ($ejercicio == 2022) {
                $sql = $e2022->getDetLayoutLc2022($laiD, $tipo, $ejercicio);
            } elseif ($ejercicio == 2023) {
                $sql = $e2023->getDetLayoutLc2023($laiD, $tipo, $ejercicio);
            } elseif ($ejercicio == 2024) {
                $sql = $e2024->getDetLayoutLc2024($laiD, $tipo, $ejercicio);
            } elseif ($ejercicio == 2025) {
                $sql = $e2025->getDetLayoutLc2025($laiD, $tipo, $ejercicio);
            } elseif ($ejercicio == 2026) {
                $sql = $e2026->getDetLayoutLc2026($laiD, $tipo, $ejercicio);
            }
        } else {
            switch ($tipo) {
                case 2:
                    if ($ejercicio == 2026) {
                        $sql = $e2026->getDetLayoutPdp2026($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2025) {
                        $sql = $e2025->getDetLayoutPdp2025($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2024) {
                        $sql = $e2024->getDetLayoutPdp2024($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2023) {
                        $sql = $e2023->getDetLayoutPdp2023($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2022) {
                        $sql = $e2022->getDetLayoutPdp2022($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2021) {
                        $sql = $e2021->getDetLayoutPdp2021($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2020) {
                        $sql = $e2020->getDetLayoutPdp2020($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2019) {
                        $sql = $e2019->getDetLayoutPdp2019($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2018) {
                        $sql = $e2018->getDetLayoutPdp2018($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2017) {
                        $sql = $e2017->getDetLayoutPdp2017($laiD, $tipo, $ejercicio);
                    }
                    break;
                case 6:
                    if ($ejercicio == 2026) {
                        $sql = $e2026->getDetLayoutManual2026($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2025) {
                        $sql = $e2025->getDetLayoutManual2025($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2024) {
                        $sql = $e2024->getDetLayoutManual2024($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2023) {
                        $sql = $e2023->getDetLayoutManual2023($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2022) {
                        $sql = $e2022->getDetLayoutManual2022($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2021) {
                        $sql = $e2021->getDetLayoutManual2021($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2020) {
                        $sql = $e2020->getDetLayoutManual2020($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2019) {
                        $sql = $e2019->getDetLayoutManual2019($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2018) {
                        $sql = $e2018->getDetLayoutManual2018($laiD, $tipo, $ejercicio);
                    }
                    break;
                default:
                    if ($ejercicio == 2026) {
                        $sql = $e2026->getDetLayout2026($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2025) {
                        $sql = $e2025->getDetLayout2025($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2024) {
                        $sql = $e2024->getDetLayout2024($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2023) {
                        $sql = $e2023->getDetLayout2023($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2022) {
                        $sql = $e2022->getDetLayout2022($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2021) {
                        $sql = $e2021->getDetLayout2021($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2020) {
                        $sql = $e2020->getDetLayout2020($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2019) {
                        $sql = $e2019->getDetLayout2019($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2018) {
                        $sql = $e2018->getDetLayout2018($laiD, $tipo, $ejercicio);
                    } elseif ($ejercicio == 2017) {
                        $sql = $e2017->getDetLayout2017($laiD, $tipo, $ejercicio);
                    }
                    break;
            }
        }
        /* Hacemos la consulta y llenamos la tabla con el resultado / */
        $this->conectar();
        $this->setSql($sql);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 5;
        $nav->idTabla = 'tblDetalleLayout';
        $this->wrLlenaTabla($nav);
    }
    /**
     * funcion muestraAutorizarLotesEspeciales
     * Dibuja la pantalla principal para la busqueda de todos los layouts que sean de las instituciones
     *      bancarias Banco Azteca, S.A., Multiva, S.A., Accendo Banco y S.A., Banca Mifel, S.A.
     * @access private
     */
    private function muestraAutorizarLotesEspeciales()
    {
        $this->mixScript('
                    DesactivaAtajosMenu();
                    Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
                    e.stopPropagation(); });

                    $("#homeMenu").click(function(e) {
                            $("#contenedor").html("");
                            $("#toolsPantalla").html("");
                            ActivaAtajosMenu();
                    });
                    $("#btnregresarindex").removeClass("btn-default");
        ');

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->color = "gris";
        $btnregresarindex->icon = "fa fa-circle-left me-1";
        $btnregresarindex->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            // Regresa a la pantalla anterior
            comunica("' . $this->pantalla . '",{
                metodo: "main"
            });
        ';

        $btnGenerarLayout = new stdClass();
        $btnGenerarLayout->id = 'btnAutorizarLotesEspeciales';
        $btnGenerarLayout->name = 'btnAutorizarLotesEspeciales';
        $btnGenerarLayout->value = 'Autorizar lote';
        $btnGenerarLayout->color = 'cielo';
        $btnGenerarLayout->icon = 'fa fa-check me-1';
        $btnGenerarLayout->click = '
            if(seleccion = $("#tblGenFondeo").tabla("getFilaSeleccion")){
                    if(confirm("¿ Desea autorizar los lotes seleccionados?")){
                            comunica("' . $this->pantalla . '",{
                                    metodo: "autorizarLotesEspeciales",
                                    fondeoDetalles: seleccion
                            });
                    }
            }
            ';

        $this->agregaTools($this->ponBoton($btnregresarindex));
        if (isset($_SESSION[_S]['prm']['T']['4']['5']['2'])) {
            $this->agregaTools($this->ponBoton($btnGenerarLayout));
        }

        /* Declaramos los elementos a utilizar/ */
        $inputLotePago = new StdClass();
        $inputLotePago->id = 'lotePago';
        $inputLotePago->name = 'lotePago';
        $inputLotePago->placeholder = 'LOTE DE PAGO';
        $inputLotePago->enDiv = true;
        $inputLotePago->label = 'LOTE DE PAGO';

        $inputFechaMinAut = new StdClass();
        $inputFechaMinAut->id = 'fechaMinAut';
        $inputFechaMinAut->name = 'fechaMinAut';
        $inputFechaMinAut->placeholder = 'FECHA MÍNIMA';
        $inputFechaMinAut->enDiv = true;
        $inputFechaMinAut->label = 'FECHA MÍNIMA DE AUTORIZACIÓN';
        $inputFechaMinAut->formato = 'fecha';

        $inputFechaMaxAut = new StdClass();
        $inputFechaMaxAut->id = 'fechaMaxAut';
        $inputFechaMaxAut->name = 'fechaMaxAut';
        $inputFechaMaxAut->placeholder = 'FECHA MÁXIMA';
        $inputFechaMaxAut->enDiv = true;
        $inputFechaMaxAut->label = 'FECHA MÁXIMA DE AUTORIZACIÓN';
        $inputFechaMaxAut->formato = 'fecha';

        $inputFechaMinPago = new StdClass();
        $inputFechaMinPago->id = 'fechaMinPago';
        $inputFechaMinPago->name = 'fechaMinPago';
        $inputFechaMinPago->placeholder = 'FECHA MÍNIMA';
        $inputFechaMinPago->enDiv = true;
        $inputFechaMinPago->label = 'FECHA MÍNIMA DE PAGO';
        $inputFechaMinPago->formato = 'fecha';

        $inputFechaMaxPago = new StdClass();
        $inputFechaMaxPago->id = 'fechaMaxPago';
        $inputFechaMaxPago->name = 'fechaMaxPago';
        $inputFechaMaxPago->placeholder = 'FECHA MÁXIMA';
        $inputFechaMaxPago->enDiv = true;
        $inputFechaMaxPago->label = 'FECHA MÁXIMA DE PAGO';
        $inputFechaMaxPago->formato = 'fecha';

        $sqlTipoLote = '
                    SELECT
                            tlo_id,
                            tlo_nombre
                    FROM
                            ' . BD_TESORERIA . '.cat_TipoLote ;
            ';
        $inputTipoLote = new StdClass();
        $inputTipoLote->id = 'tipoLote';
        $inputTipoLote->sql = $sqlTipoLote;
        $inputTipoLote->name = 'tipoLote';
        $inputTipoLote->placeholder = 'TIPO DE LOTE';
        $inputTipoLote->enDiv = true;
        $inputTipoLote->label = 'TIPO LOTE';

        if ($this->serverProduccion) {
            $anio = array('1' => 2026, '2' => 2025, '3' => 2024, '4' => 2023, '5' => 2022, '6' => 2021,
                '7' => 2020, '8' => 2019, '9' => 2018, '10' => 2017);
        } else {
            $anio = array('1' => 2026, '2' => 2025, '3' => 2024, '4' => 2023, '5' => 2022, '6' => 2021);
        }
        $inputejercicio = new stdClass();
        $inputejercicio->opciones = $anio;
        $inputejercicio->id = 'ejercicio';
        $inputejercicio->name = 'ejercicio';
        $inputejercicio->mensaje = 'ejercicio';
        $inputejercicio->enDiv = true;
        $inputejercicio->label = 'EJERCICIO';

        $inputNombreBanco = new StdClass();
        $inputNombreBanco->sql = '
            SELECT ban.iba_numero, ban.iba_abreviatura FROM ' . BD_TESORERIA . '.cat_InstitucionBancaria  ban
            INNER JOIN ' . BD_CTRL . '.cat_InstitucionesBancarias b USING (iba_id)
            WHERE ban.iba_numero IN(132, 102, 042) AND ban.sts_id=1;
        ';
        $inputNombreBanco->id = 'nombreBanco';
        $inputNombreBanco->name = 'nombreBanco';
        $inputNombreBanco->label = 'Nombre del Banco';
        $inputNombreBanco->camposVal = "iba_numero";
        $inputNombreBanco->camposText = "iba_abreviatura";
        $inputNombreBanco->comoAtr = "iba_abreviatura";

        $btnBuscar = new stdClass();
        $btnBuscar->id = "btnBuscar";
        $btnBuscar->name = "btnBuscar";
        $btnBuscar->value = "Buscar";
        $btnBuscar->color = "gris";
        $btnBuscar->icon = "fa fa-search";
        $btnBuscar->click = '
                comunica("' . $this->pantalla . '",{
                        metodo:"llenaTablaAutorizaLotesEspeciales",
                        ejercicio:$("#ejercicio").val(),
                        lotePago:$("#lotePago").val(),
                        fechaMinAut:$("#fechaMinAut").val(),
                        fechaMaxAut:$("#fechaMaxAut").val(),
                        fechaMinPago:$("#fechaMinPago").val(),
                        fechaMaxPago:$("#fechaMaxPago").val(),
                        tipo:$("#tipoLote").val(),
                        banco:$("#nombreBanco").val()
                    })';

        $tabla = new stdClass();
        $tabla->multiseleccionable = true;
        $tabla->prmSeleccion = array('fpa_id', 'msc_id', 'lot_ejercicio', 'lot_id', 'tlo_id');

        $tabla->casoSeleccionable = new stdClass();
        $tabla->casoSeleccionable->campo = 'elo_id';
        $tabla->casoSeleccionable->valor = 7;
        $tabla->casoSeleccionable->colorEnFalso = "rgba(8, 255, 18, 0.20)";

        $tabla->id = 'tblGenFondeo';
        $tabla->titulo = 'Resumen de lotes';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaAutorizaLotesEspeciales';
        $tabla->comunica->ejercicio = '{$("#ejercicio").val()}';
        $tabla->comunica->tipo = '{$("#tipoLote").val()}';
        $tabla->comunica->lotePago = '{$("#lotePago").val()}';
        $tabla->comunica->fechaMinAut = '{$("#fechaMinAut").val()}';
        $tabla->comunica->fechaMaxAut = '{$("#fechaMaxAut").val()}';
        $tabla->comunica->fechaMinPago = '{$("#fechaMinPago").val()}';
        $tabla->comunica->fechaMaxPago = '{$("#fechaMaxPago").val()}';
        $tabla->comunica->banco = '{$("#nombreBanco").val()}';

        $tr = array();

        $th = new stdClass();
        $th->texto = 'DETALLE';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EJERCICIO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        $th->width = '10rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'MODO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE';
        $th->width = '6rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC\'S';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA AUTORIZACIÓN';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA PAGO';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TOTAL';
        $th->width = '12rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'EDO';
        $th->width = '8rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'OBSERVACION';
        $th->align = 'center';
        array_push($tr, $th);

        $trbody = array();

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'fa fa-search';
        $td->class = 'lotDet';
        $td->attr = new stdClass();
        $td->attr->lotDet = 'lot_id';
        $td->attr->lotFpa = 'fpa_id';
        $td->attr->lotMsc = 'msc_id';
        $td->attr->lotEje = 'lot_ejercicio';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_ejercicio';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_tipo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_modo';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'numclc';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_fechaAutorizacion';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_fechaPago';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_total';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_edo';
        $td->align = 'right';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_obs';
        $td->align = 'right';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'elo_id';
        $td->casos->valor = 7;

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $html = '
            <div class="row">
                <legend>AUTORIZAR LOTES ESPECIALES</legend>
            </div>
            <div class="row mb-4">
                <div class="col-md-3">
                    ' . $this->ponInput($inputLotePago) . '
                </div>
                <div class="col-md-3">
                    ' . $this->ponCombo($inputTipoLote) . '
                </div>
                <div class="col-md-3">
                    ' . $this->ponCombo($inputejercicio) . '
                </div>
                <div class="col-md-3">
                    ' . $this->ponCombo($inputNombreBanco) . '
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-3">
                    ' . $this->ponInput($inputFechaMinAut) . '
                </div>
                <div class="col-md-3">
                    ' . $this->ponInput($inputFechaMaxAut) . '
                </div>
                <div class="col-md-3">
                    ' . $this->ponInput($inputFechaMinPago) . '
                </div>
                <div class="col-md-3">
                    ' . $this->ponInput($inputFechaMaxPago) . '
                </div>
            </div>
            <div class="row">
                <fieldset class="col-xs-3 col-md-3">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
            </div>
            <hr>
            <div class="row">
                <fieldset class="col-xs-12 col-md-12">
                    ' . $this->wrPonTabla($tabla) . '
                </fieldset>
            </div>
        ';

        $this->mixHtml($html);
    }
    /**
     * funcion llenaTablaAutorizaLotesEspeciales()
     * Encargada de llenar la tabla de todos aquellos lotes de las instituciones bancarias
     *         Banco Azteca, S.A., Multiva, S.A., Accendo Banco y S.A., Banca Mifel, S.A.
     * @access private
     * @param ejercicio|"" int anio del ejercicio del lote
     * @param lotePago|"" lote identificador de lote (fpa_id,msc_id,lot_ejercicio,lot_id)
     * @param fechaMinAut|"" fecha minima de autorización
     * @param fechaMaxAut|"" fecha maxima de autorización
     * @param fechaminPago|"" fecha minima de pago
     * @param fechaMaxPago|"" fecha maxima de pago
     * @param tipo tipo de lote
     */
    private function llenaTablaAutorizaLotesEspeciales()
    {
        $get = new clcs2017;
        $sql = $get->getMovimientosAutorizadosALE($this->prm);
        $this->conectar();
        $this->setSql($sql);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 50;
        $nav->idTabla = "tblGenFondeo";
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".lotDet").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "detalleLote",
                    lot_id: $(this).attr("lotDet"),
                    fpa_id: $(this).attr("lotFpa"),
                    msc_id: $(this).attr("lotMsc"),
                    lot_ejercicio: $(this).attr("lotEje"),
                    ejercicio:' . (isset($this->prm->ejercicio) ? $this->prm->ejercicio : '') . ',
                    lotePago:"' . (isset($this->prm->lotePago) ? $this->prm->lotePago : '') . '",
                    fechaMinAut:"' . (isset($this->prm->fechaMinAut) ? $this->prm->fechaMinAut : '') . '",
                    fechaMaxAut:"' . (isset($this->prm->fechaMaxAut) ? $this->prm->fechaMaxAut : '') . '",
                    fechaMinPago:"' . (isset($this->prm->fechaMinPago) ? $this->prm->fechaMinPago : '') . '",
                    fechaMaxPago:"' . (isset($this->prm->fechaMaxPago) ? $this->prm->fechaMaxPago : '') . '",
                    tipo:"' . (isset($this->prm->tipo) ? $this->prm->tipo : '') . '",
                    sub_modulo:"Autorizar lotes especiales",
                });
            });
        ');
    }
    /**
     * funcion  autorizarLotesEspeciales
     * Esta funcion es la encargada de autorizar aquellos lotes de las instituciones bancarias
     *         Banco Azteca, S.A., Multiva, S.A., Accendo Banco y S.A., Banca Mifel, S.A.
     * @access private
     * @param layoutDetalles array de los lotes seleccionados, para autorizarlos.
     */
    private function autorizarLotesEspeciales()
    {
        $layoutDetalles = isset($this->prm->fondeoDetalles) ? $this->prm->fondeoDetalles : '';
        $lot_id = '';
        $statusLay = false;
        foreach ($layoutDetalles as $key => $value) {
            $lot_id .= "(" . $value->fpa_id . "," . $value->msc_id . ","
            . $value->lot_ejercicio . "," . $value->lot_id . "),";
            $anio = $value->lot_ejercicio;
            $formaPago = $value->fpa_id;
        }
        $lotes = trim($lot_id, ',');
        $conexiontipo = new AccesoDatos();
        $conexiontipo->conectar();
        $sqlt = "SELECT
                    tlo_id
                    FROM
                " . BD_TESORERIA . ".lot_Lote AS l
                WHERE (l.fpa_id,l.msc_id,l.lot_ejercicio,l.lot_id) in (
                " . $lotes . ")
                AND l.lot_ejr = :lot_ejr;";
        $paramt = [
            'lot_ejr' => APP_EJERCICIO,
        ];
        $conexiontipo->setSql($sqlt);
        $conexiontipo->consultar($paramt);
        $t = $conexiontipo->extraer();
        $tipoLote = $t['tlo_id'];
        $e2026 = new clcs2026;
        $e2025 = new clcs2025;
        $e2024 = new clcs2024;
        $e2023 = new clcs2023;
        $e2022 = new clcs2022;
        $e2021 = new clcs2021;
        $e2020 = new clcs2020;
        $e2019 = new clcs2019;
        $e2018 = new clcs2018;
        $e2017 = new clcs2017;
        if ($formaPago == 3) {
            if ($tipoLote != 5) {
                switch ($anio) {
                    case 2017:
                        $sql = $e2017->getDetalleLoteLc2017($lotes);
                        break;
                    case 2018:
                        $sql = $e2018->getDetalleLoteLc2018($lotes);
                        break;
                    case 2019:
                        $sql = $e2019->getDetalleLoteLc2019($lotes);
                        break;
                    case 2020:
                        $sql = $e2020->getDetalleLoteLc2020($lotes);
                        break;
                    case 2021:
                        $sql = $e2021->getDetalleLoteLc2021($lotes);
                        break;
                    case 2022:
                        $sql = $e2022->getDetalleLoteLc2022($lotes);
                        break;
                    case 2023:
                        $sql = $e2023->getDetalleLoteLc2023($lotes);
                        break;
                    case 2024:
                        $sql = $e2024->getDetalleLoteLc2024($lotes);
                        break;
                    case 2025:
                        $sql = $e2025->getDetalleLoteLc2025($lotes);
                        break;
                    case 2026:
                        $sql = $e2026->getDetalleLoteLc2026($lotes);
                        break;
                }
            }
        } else {
            switch ($tipoLote) {
                case 2:
                    if ($anio == 2017) {
                        $sql = $e2017->getDetLotePdp2017($lotes);
                    } elseif ($anio == 2018) {
                        $sql = $e2018->getDetLotePdp2018($lotes);
                    } elseif ($anio == 2019) {
                        $sql = $e2019->getDetLotePdp2019($lotes);
                    } elseif ($anio == 2020) {
                        $sql = $e2020->getDetLotePdp2020($lotes);
                    } elseif ($anio == 2021) {
                        $sql = $e2021->getDetLotePdp2021($lotes);
                    } elseif ($anio == 2022) {
                        $sql = $e2022->getDetLotePdp2022($lotes);
                    } elseif ($anio == 2023) {
                        $sql = $e2023->getDetLotePdp2023($lotes);
                    } elseif ($anio == 2024) {
                        $sql = $e2024->getDetLotePdp2024($lotes);
                    } elseif ($anio == 2025) {
                        $sql = $e2025->getDetLotePdp2025($lotes);
                    } elseif ($anio == 2026) {
                        $sql = $e2026->getDetLotePdp2026($lotes);
                    }
                    break;
                case 4:
                    $sql = $e2026->getDetalleLoteMunicipio($lotes);
                    break;
                case 6:
                case 7:
                    $sql = $e2026->getDetalleLoteManual($lotes);
                    break;
                default:
                    if ($anio == 2017) {
                        $sql = $e2017->getDetalleLote2017($lotes);
                    } elseif ($anio == 2018) {
                        $sql = $e2018->getDetalleLote2018($lotes);
                    } elseif ($anio == 2019) {
                        $sql = $e2019->getDetalleLote2019($lotes);
                    } elseif ($anio == 2020) {
                        $sql = $e2020->getDetalleLote2020($lotes);
                    } elseif ($anio == 2021) {
                        $sql = $e2021->getDetalleLote2021($lotes);
                    } elseif ($anio == 2022) {
                        $sql = $e2022->getDetalleLote2022($lotes);
                    } elseif ($anio == 2023) {
                        $sql = $e2023->getDetalleLote2023($lotes);
                    } elseif ($anio == 2024) {
                        $sql = $e2024->getDetalleLote2024($lotes);
                    } elseif ($anio == 2025) {
                        $sql = $e2025->getDetalleLote2025($lotes);
                    } elseif ($anio == 2026) {
                        $sql = $e2026->getDetalleLote2026($lotes);
                    }
                    break;
            }
        }

        $this->conectar();
        $this->setSql($sql);
        $this->consultar();

        /*Se separan las variables de conteo de transacciones por banco
        para evitar que el en el layout de SCOTIAL sea incorrecto.
         */
        $total_transaccionesSC = 0;
        $suma_importes = 0;
        $tipo = 0;
        $bancoPropio = '';
        $contador = 0;
        $contadorhs = 0;
        $contadorSc = 0;
        $clcs = array();
        $detLayBx = array();
        $detLayBv = array();
        $detLayBnI = array();
        $detLayBnE = array();
        $detLayStE = array();
        $detLayStI = array();
        $detLayHsI = array();
        $detLayHsE = array();
        $detLayIn = array();
        $detLaySc = array();

        $esLoteEspecial = true;
        while ($clc = $this->extraer()) {
            if ($formaPago == 3) {
                if ($anio == 2026) {
                    $vinFolio = $e2026->consultaFolios26($clc['orden']);
                } elseif ($anio == 2025) {
                    $vinFolio = $e2025->consultaFolios25($clc['orden']);
                } elseif ($anio == 2024) {
                    $vinFolio = $e2024->consultaFolios24($clc['orden']);
                } elseif ($anio == 2023) {
                    $vinFolio = $e2023->consultaFolios23($clc['orden']);
                } elseif ($anio == 2022) {
                    $vinFolio = $e2022->consultaFolios22($clc['orden']);
                } elseif ($anio == 2021) {
                    $vinFolio = $e2021->consultaFolios21($clc['orden']);
                } elseif ($anio == 2020) {
                    $vinFolio = $e2020->consultaFolios20($clc['orden']);
                } elseif ($anio == 2019) {
                    $vinFolio = $e2019->consultaFolios19($clc['orden']);
                } elseif ($anio == 2018) {
                    $vinFolio = $e2018->consultaFolios18($clc['orden']);
                } elseif ($anio == 2017) {
                    $vinFolio = $e2017->consultaFolios17($clc['orden']);
                }
                $folios = "";
                $conexionFolios = new AccesoDatos();
                $conexionFolios->conectar();
                $conexionFolios->setSql($vinFolio);
                $conexionFolios->conectar();
                $conexionFolios->consultar();
                while ($foliopagos = $conexionFolios->extraer()) {
                    $aniof = substr($foliopagos['lot_ejercicio'], 2, 2);
                    $folios .= $aniof . $foliopagos['clc_tipoCereCo'] . $foliopagos['clc_cereCo'] . " ";
                }
                $conexionFolios->desconectar();
                $clc['folios'] = $folios;
                if ($clc['estadolc'] == 5) {
                    if ($anio == 2018) {
                        $sqlVencida = $e2018->consultaLcVencida($clc['orden']);
                    } elseif ($anio == 2019) {
                        $sqlVencida = $e2019->consultaLcVencida($clc['orden']);
                    } elseif ($anio == 2020) {
                        $sqlVencida = $e2020->consultaLcVencida($clc['orden']);
                    } elseif ($anio == 2021) {
                        $sqlVencida = $e2021->consultaLcVencida($clc['orden']);
                    } elseif ($anio == 2022) {
                        $sqlVencida = $e2022->consultaLcVencida($clc['orden']);
                    } elseif ($anio == 2023) {
                        $sqlVencida = $e2023->consultaLcVencida($clc['orden']);
                    } elseif ($anio == 2024) {
                        $sqlVencida = $e2024->consultaLcVencida($clc['orden']);
                    } elseif ($anio == 2025) {
                        $sqlVencida = $e2025->consultaLcVencida($clc['orden']);
                    } elseif ($anio == 2026) {
                        $sqlVencida = $e2026->consultaLcVencida($clc['orden']);
                    }
                    $conexionVencida = new AccesoDatos();
                    $conexionVencida->conectar();
                    $conexionVencida->setSql($sqlVencida);
                    $conexionVencida->consultar();
                    while ($lcpago = $conexionVencida->extraer()) {
                        $reemplazos = array(
                            "orden" => $lcpago['orden'], "linea_captura" => $lcpago['linea_captura'],
                            "importe" => $lcpago['importe'],
                        );
                        $lcv = array_replace($clc, $reemplazos);
                    }
                    array_push($clcs, $lcv);
                    $conexionVencida->desconectar();
                } else {
                    array_push($clcs, $clc);
                }
            } else {
                array_push($clcs, $clc);
            }

            $extrar3PrimerosNumeros = substr($clc['ctaCargo'], 0, 3);
            if (
                $extrar3PrimerosNumeros == "132" || $extrar3PrimerosNumeros == "102"
                || $extrar3PrimerosNumeros == "042"
            ) {
                $esLoteEspecial = true;
            } else {
                $esLoteEspecial = false;
                break;
            }
        }

        if ($esLoteEspecial) {
            foreach ($layoutDetalles as $key => $value) {
                $this->prm->accionLote = 'banca';
                $this->prm->fpa_id = $value->fpa_id;
                $this->prm->msc_id = $value->msc_id;
                $this->prm->ejercicio = $value->lot_ejercicio;
                $this->prm->lot_id = $value->lot_id;
                $this->cambiaEstadoLotesEspeciales();
            }
            $this->mixScript('
                notify("LOTES AUTORIZADO ","success",4000);
                comunica("' . $this->pantalla . '",{
                        metodo:"llenaTablaAutorizaLotesEspeciales",
                        ejercicio:$("#ejercicio").val(),
                        lotePago:$("#lotePago").val(),
                        fechaMinAut:$("#fechaMinAut").val(),
                        fechaMaxAut:$("#fechaMaxAut").val(),
                        fechaMinPago:$("#fechaMinPago").val(),
                        fechaMaxPago:$("#fechaMaxPago").val(),
                        tipo:$("#tipoLote").val(),
                        banco:$("#nombreBanco").val()
                    });
            ');
        } else {
            $this->mixScript('
                notify("Error al autorizar lote, el lote es mixto","warning",4000);
            ');
        }
    }
    /**
     * funcion cambiaEstadoLotesEspeciales()
     * Esta funcion es la encargada de afectar los cambios de estado que correspondan
     *  a las instituciones bancarias Banco Azteca, S.A., Multiva, S.A., Accendo Banco y S.A., Banca Mifel, S.A.
     * @access private
     * @param mbn_id id del movimiento bancario
     * @param lot_id id del lote
     * @param fpa_id id de la forma de pago del lote
     * @param msc_id id del mes contable del lote
     * @param ejercicio anio del ejercicio del lote
     * @param accionLote String accion a ejecutar (autorizar,banca)
     * autorizar: autoriza los movimientos bancarios, afectando el estado del movimiento, como el del lote y clc
     * banca: afecta el estado del lote a enviado a banca electronica, asi como los estados de la clc,
     * segun el ejercicio correspondiente.
     * @access private
     */
    private function cambiaEstadoLotesEspeciales()
    {
        $lot = new stdClass();
        $fechaActual = '';
        $mbn_id = isset($this->prm->mbn_id) ? $this->prm->mbn_id : "";
        $lot->lot_id = isset($this->prm->lot_id) ? $this->prm->lot_id : "";
        $lot->fpa_id = isset($this->prm->fpa_id) ? $this->prm->fpa_id : "";
        $lot->msc_id = isset($this->prm->msc_id) ? $this->prm->msc_id : "";
        $lot->ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : "";
        $redirige = isset($this->prm->redirige) ? $this->prm->redirige : true;

        $conexionTlo_id = new AccesoDatos();
        $conexionTlo_id->conectar();
        $sqlt = 'SELECT
                    tlo_id
                    FROM
                ' . BD_TESORERIA . '.lot_Lote AS l
                WHERE (l.fpa_id,l.msc_id,l.lot_ejercicio,l.lot_id)
                    IN ((:fpa_id, :msc_id, :ejercicio,:lot_id))
                AND l.lot_ejr = :lot_ejr;';
        $paramsTlo = [
            'fpa_id' => $lot->fpa_id,
            'msc_id' => $lot->msc_id,
            'ejercicio' => $lot->ejercicio,
            'lot_id' => $lot->lot_id,
            'lot_ejr' => APP_EJERCICIO,
        ];
        $conexionTlo_id->setSql($sqlt);
        $conexionTlo_id->consultar($paramsTlo);
        $resultConexion = $conexionTlo_id->extraer();
        $tipoL = $resultConexion['tlo_id'];

        switch ($this->prm->accionLote) {
            case 'banca':
                try {
                    $usr = $_SESSION[_S]['usr_id'];
                    $conexion = new AccesoDatos();
                    $conexion->conectar();
                    $set = 'SET @bit_observacion="LOTE ENVIADO A BANCA ELECTRONICA DESDE EL SISTEMA",@usr_id= :usr,@usr_ip= sinpres.fn_get_ip();';
                    $paramSet = [
                        'usr' => $usr,
                    ];
                    $conexion->setSql($set);
                    $conexion->consultar($paramSet);
                    $sql0 = 'START TRANSACTION;';
                    $conexion->setSql($sql0);
                    $conexion->consultar();
                    $sql1 = "UPDATE " . BD_TESORERIA . ".lot_Lote SET elo_id=8 WHERE fpa_id= :fpa_id AND msc_id= :msc_id AND lot_ejercicio= :lot_ejercicio AND lot_id= :lot_id
                    AND lot_ejr = :lot_ejr;";
                    $paramsql1 = [
                        'fpa_id' => $lot->fpa_id,
                        'msc_id' => $lot->msc_id,
                        'lot_ejercicio' => $lot->ejercicio,
                        'lot_id' => $lot->lot_id,
                        'lot_ejr' => APP_EJERCICIO,
                    ];
                    $conexion->setSql($sql1);
                    $conexion->consultar($paramsql1);
                    if ($tipoL == 6) {
                        #cambia estado propuesta manual
                        $sqlM = "UPDATE " . BD_TESORERIA . ".lot_PropuestaManual SET elo_id=8
                        WHERE fpa_id= :fpa_id AND msc_id= :msc_id AND lot_ejercicio= :lot_ejercicio and lot_id= :lot_id";
                        $paramSqlM = [
                            'fpa_id' => $lot->fpa_id,
                            'msc_id' => $lot->msc_id,
                            'lot_ejercicio' => $lot->ejercicio,
                            'lot_id' => $lot->lot_id,
                        ];
                        $conexion->setSql($sqlM);
                        $conexion->consultar($paramSqlM);
                    }
                    $paramLe = [
                        'fpa_id' => $lot->fpa_id,
                        'msc_id' => $lot->msc_id,
                        'lot_ejercicio' => $lot->ejercicio,
                        'lot_id' => $lot->lot_id,
                    ];
                    switch ($lot->ejercicio) {
                        case 2026:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND c.ecl_id in (15)';
                            break;
                        case 2025:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL_2025 . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND c.ecl_id in (15)
                            AND ejr_id_clc = 2025';
                            break;
                        case 2024:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL_2024 . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND c.ecl_id in (15)
                            AND ejr_id_clc = 2024';
                            break;
                        case 2023:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL_2023 . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND c.ecl_id in (15)
                            AND ejr_id_clc = 2023';
                            break;
                        case 2022:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL_2022 . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND c.ecl_id in (15)
                            AND ejr_id_clc = 2022';
                            break;
                        case 2021:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL_2021 . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND c.ecl_id in (15)
                            AND ejr_id_clc = 2021';
                            break;
                        case 2020:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL_2020 . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND c.ecl_id in (15)
                            AND ejr_id_clc = 2020';
                            break;
                        case 2019:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL_2019 . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND c.ecl_id in (15)
                            AND ejr_id_clc = 2019';
                            break;
                        case 2018:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL_2018 . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND ca.ecl_id in (15)
                            AND ejr_id_clc = 2018';
                            break;
                        case 2017:
                            $sql2 = 'SELECT gpo_id,ur_id,clc_id '
                            . 'FROM ' . BD_TESORERIA . '.lot_LoteClc AS l '
                            . 'INNER JOIN ' . BD_CTRL_2017 . '.scp_Clc AS c using (gpo_id,ur_id,clc_id)'
                            . 'INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca using (gpo_id,ur_id,clc_id)'
                            . 'WHERE l.fpa_id= :fpa_id AND l.msc_id= :msc_id AND l.lot_ejercicio= :lot_ejercicio AND l.lot_id= :lot_id AND ca.ecl_id in (11)
                            AND ejr_id_clc = 2017';
                            break;
                    }
                    $conexion->setSql($sql2);
                    $conexion->consultar($paramLe);
                    $clcs = array();
                    while ($clc = $conexion->extraer()) {
                        array_push($clcs, $clc);
                    }
                    switch ($lot->ejercicio) {
                        case 2026:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRL . ".scp_ClcAuxiliar SET ecl_id=16 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id; ";
                                $paramUpdate =[
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramUpdate);
                            }
                            break;
                        case 2025:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=16 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2025 ";
                                $paramUpdate =[
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramUpdate);
                            }
                            break;
                        case 2024:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=16 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2024 ";
                                $paramUpdate =[
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramUpdate);
                            }
                            break;
                        case 2023:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=16 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2023 ";
                                $paramUpdate =[
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramUpdate);
                            }
                            break;
                        case 2022:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=16 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2022 ";
                                $paramUpdate =[
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramUpdate);
                            }
                            break;
                        case 2021:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=16 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2021 ";
                                $paramUpdate =[
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramUpdate);
                            }
                            break;
                        case 2020:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=16 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2020 ";
                                $paramUpdate =[
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramUpdate);
                            }
                            break;
                        case 2019:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=16 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2019 ";
                                $paramUpdate =[
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramUpdate);
                            }
                            break;
                        case 2018:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=16 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2018; ";
                                $paramUpdate =[
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramUpdate);
                            }
                            break;
                        case 2017:
                            foreach ($clcs as $value) {
                                $sql3 = "UPDATE " . BD_CTRLANT . ".scp_Clc SET ecl_id=12 WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ejr_id_clc = 2017;";
                                $paramUpdate =[
                                    'gpo_id' => $value['gpo_id'],
                                    'ur_id' => $value['ur_id'],
                                    'clc_id' => $value['clc_id'],
                                ];
                                $conexion->setSql($sql3);
                                $conexion->consultar($paramUpdate);
                            }
                            break;
                    }
                    $sql4 = 'COMMIT';
                    $conexion->setSql($sql4);
                    $conexion->consultar();
                } catch (Exception $e) {
                    $sql5 = 'ROLLBACK;';
                    $conexion->setSql($sql5);
                    $conexion->consultar();
                    $this->mixScript('notify("ERROR AL CAMBIAR ESTADO DEL LOTE, NOTIFIQUE AL ADMINISTRADOR ","error")');
                }
                break;
        }
    }
}

if (isset($_SESSION[_S]['prm']['T']['4']['5']['1'])) {
    new ControladorMinBanca();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
