<?php
/**
 * Description of ControladorConsultaExtractoBancario
 *
 * @author
 * Collaborators: Adriana Santiago Morales <adriana.morales@finanzasoaxaca.gob.mx>
 * Created: 11-Feb-2019
 * Comment: -----
 * Objective: Consultar y generar reportes en Excel de los Extractos Bancarios
 * Regla de Negocio:
 * Permits: Solicita lectura, escritura y validacion
 * */
//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/tes/consulta/consultaExtractoBancarioDetalle.php';
require_once dirname(__FILE__) . '/../../lib/tes/consulta/consultaExtractoDetLineaCaptura.php';
require_once dirname(__FILE__) . '/../../lib/tes/consulta/consultaExtractoDetPdp.php';

class ControladorConsultaExtractoBancario extends WebResponse
{
    public $tipo;
    public $fechaMin;
    public $fechaMax;
    public function __construct()
    {

        parent::__construct();
        $this->tipo = (isset($this->prm->tipo) && $this->prm->tipo != '' ? $this->prm->tipo : "");
        $this->fechaMin = (isset($this->prm->fechaMin) && $this->prm->fechaMin != '' ? $this->prm->fechaMin : "");
        $this->fechaMax = (isset($this->prm->fechaMax) && $this->prm->fechaMax != '' ? $this->prm->fechaMax : "");
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
                    case 'extractoGeneral':
                        $this->extractoGeneral();
                        break;
                    case 'pintaTabla':
                        $this->pintaTabla();
                        break;
                    case 'llenaTabla':
                        $this->llenaTabla();
                        break;
                    default:
                        $this->main();
                        break;
                }
                break;
        }
        $this->returnJson();
    }
    private function main()
    {
        $this->mixScript('
            $("#toolsPantalla").html("");
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click();
            e.stopPropagation(); });

            $("#homeMenu").click(function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
        ');

        $this->mixHtml('
                <div class="container">
                    <div class="row col-md-4 mb-4"><legend>CONSULTAS</legend></div>
                    <div class="row justify-content-center">
                        <div class="col-4"  id="">
                            <div titpp="CONSULTA DE PROPUESTAS" class="boton_opcion" id="consulta_ExGeneral">
                                <div class="boton_opcion_titulo" >EXTRACTO GENERAL</div>
                                <strong><img height="60" width="60" src="img/Autoriza_Folio.png"><br/></strong>
                                <small>Consulta </small>
                            </div>
                        </div>
                    </div>
                </div>
        ');
        $this->mixScript('
                $("#consulta_ExGeneral").click(function(){
                    $("#SitemaActivo").hide();
                    comunica("' . $this->pantalla . '",{
                        metodo:"extractoGeneral"
                    });
                });
            ');
    }
    private function extractoGeneral()
    {
        //crea el boton de regreso
        $this->mixScript('
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation();});
            $("#homeMenu").click( function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
        ');
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnRegresar';
        $btnregresarindex->name = 'btnRegresar';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = "me-2";
        $btnregresarindex->color = "gris";
        $btnregresarindex->icon = "fa-solid fa-circle-left me-1";
        $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
            metodo: "main"
            });
        ';
        // boten de excel
        $btnExcel = new stdClass();
        $btnExcel->id = "exbExcel";
        $btnExcel->class = 'me-2';
        $btnExcel->color = 'verde';
        $btnExcel->value = "Exportar Excel";
        $btnExcel->icon = "fa-solid fa-file-excel me-2";
        $btnExcel->name = "prueba";
        $btnExcel->attr = new stdClass();
        $btnExcel->attr->tip_Reporte = 1;
        $btnExcel->click = '
            var tipReporte = $(this).attr("tip_Reporte");
            if (tipReporte == 1) {
                $("#SearchExtracto").prop("action","rep/excel/tes/consulta/exp_ExtractoBancarioDetalle.php");
            } else if(tipReporte == 2) {
                $("#SearchExtracto").prop("action","rep/excel/tes/consulta/exp_ExtractoDetPdp.php");
            } else if (tipReporte == 3) {
                $("#SearchExtracto").prop("action","rep/excel/tes/consulta/exp_ExtractoDetLineaCaptura.php");
            }
            $("#SearchExtracto").submit();
        ';

        $this->agregaTools($this->ponBoton($btnregresarindex));
        $this->agregaTools($this->ponBoton($btnExcel));

        $tipo = array('1' => "General", '2' => "PDP", '3' => "LINEA DE CAPTURA");
        $inputTipo = new stdClass();
        $inputTipo->opciones = $tipo;
        $inputTipo->id = 'tipo';
        $inputTipo->name = 'tipo';
        $inputTipo->mensaje = 'tipo';
        $inputTipo->enDiv = true;
        $inputTipo->label = 'TIPO';
        $inputTipo->change = '
            $("#exbExcel").attr("tip_Reporte",$(this).val());
        ';

        $inputFechaMin = new StdClass();
        $inputFechaMin->id = 'fechaMin';
        $inputFechaMin->name = 'fechaMin';
        $inputFechaMin->placeholder = 'FECHA MÍNIMA';
        $inputFechaMin->enDiv = true;
        $inputFechaMin->label = 'FECHA MÍNIMA';
        $inputFechaMin->formato = 'fecha';

        $inputFechaMax = new StdClass();
        $inputFechaMax->id = 'fechaMax';
        $inputFechaMax->name = 'fechaMax';
        $inputFechaMax->placeholder = 'FECHA MÁXIMA';
        $inputFechaMax->enDiv = true;
        $inputFechaMax->label = 'FECHA MÁXIMA';
        $inputFechaMax->formato = 'fecha';

        $btnBuscar = new stdClass();
        $btnBuscar->id = "buscar";
        $btnBuscar->name = "buscar";
        $btnBuscar->value = "Buscar";
        $btnBuscar->color = "gris";
        $btnBuscar->icon = "fa fa-search me-2";
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"pintaTabla",
                fechaMin:$("#fechaMin").val(),
                fechaMax:$("#fechaMax").val(),
                tipo:$("#tipo").val()
            })';
        //generamos el html con los elementos a utilizar y los tributos adecuados
        $html = '
            <div class="row mb-4"><legend>CONSULTA EXTRACTO BANCARIO</legend></div>
            <div class="row mb-4 mt-5">
                <form method="post" target="_blank" id="SearchExtracto">
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-3">' . $this->ponInput($inputFechaMin) . '</div>
                            <div class="col-3">' . $this->ponInput($inputFechaMax) . '</div>
                            <div class="col-4">' . $this->ponCombo($inputTipo) . '</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="row">
                <fieldset class="col-md-2 col-xs-2" id="">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
            </div>
            <div class="row">
                <fieldset id="divTabla" class="col-md-12 col-xs-12">
            </div>

        ';
        $this->mixHtml($html);
    }
    private function pintaTabla()
    {
        $this->setContenedor('divTabla');
        $tabla = new stdClass();
        $tabla->id = 'tbl-Extracto';
        $tabla->titulo = 'Extracto Bancario';
        $tabla->comunica = new stdClass();
        $tabla->comunica->metodo = 'llenaTabla';
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->tipo = $this->prm->tipo;
        $tabla->comunica->fechaMin = $this->prm->fechaMin;
        $tabla->comunica->fechaMax = $this->prm->fechaMax;
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = array(5, 10, 50, 100, 150);
        $tipo = $this->prm->tipo;
        $tr = array();
        $th = new stdClass();
        $th->texto = 'CTA_CARGO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'REFERENCIA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CARGO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ABONO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FE.TRANSACCION';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DESCRIPCION';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'SPEI';
        array_push($tr, $th);
        if ($tipo > 1) {
            $th = new stdClass();
            $th->texto = 'UR';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'CLC';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'CERECO';
            array_push($tr, $th);
        }
        if ($tipo == 3) {
            $th = new stdClass();
            $th->texto = 'LINEA-CAPTURA';
            array_push($tr, $th);
        }
        //CUERPO DE LA TABLA
        $trbody = array();
        $td = new stdClass();
        $td->campoTxt = 'cbs_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'exb_referencia';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'exb_cargo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'exb_abono';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'exb_importe';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'exb_fechaTransaccion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'exb_descripcion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'exb_spei';
        array_push($trbody, $td);
        if ($tipo > 1) {
            $td = new stdClass();
            $td->campoTxt = 'ur';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->campoTxt = 'clc_id';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->campoTxt = 'clc_cereCo';
            array_push($trbody, $td);
        }
        if ($tipo == 3) {
            $td = new stdClass();
            $td->campoTxt = 'lineaCaptura';
            array_push($trbody, $td);
        }

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTabla();
    }
    private function llenaTabla()
    {
        $tipo = isset($this->prm->tipo) ? $this->prm->tipo : "";
        $fechaMin = isset($this->prm->fechaMin) ? $this->prm->fechaMin : "";
        $fechaMax = isset($this->prm->fechaMax) ? $this->prm->fechaMax : "";
        $params = [];
        if ($fechaMin == "" || $fechaMax == "") {
            $this->mixScript('notify("INTRODUZCA UN RANGO DE FECHA PARA BÚSQUEDA ","error")');
            return;
        }

        $fechaMin = DateTime::createFromFormat('d/m/Y', $fechaMin);
        $fechaMin = $fechaMin->format('Y-m-d');
        $fechaMax = DateTime::createFromFormat('d/m/Y', $fechaMax);
        $fechaMax = $fechaMax->format('Y-m-d');

        switch ($tipo) {
            case 2:
                $sql = consultaExtractoDetPdp(false, $fechaMin, $fechaMax);
                break;
            case 3:
                $sql = consultaExtractoDetLineaCaptura(false, $fechaMin, $fechaMax);
                break;
            default:
                $sql = consultaExtractoBancarioDetalle(false, $fechaMin, $fechaMax);
                break;
        }
        $params = [':fmin'=>$fechaMin,':fmax'=>$fechaMax];
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();

        $nav->idTabla = "tbl-Extracto";
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);
    }
}
if (isset($_SESSION[_S]['prm']['T']['1']['1'][TAREA_LECTURA])) {
    new ControladorConsultaExtractoBancario();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
