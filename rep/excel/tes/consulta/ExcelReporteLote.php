<?php
/**
 * Description of function nivelFolio
 *
 * @author ?
 * Collaborators: Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 12-Dic-2019
 * Comment: -----
 * Objective: Mostrar en un Excel datos de lotes
 * Regla de Negocio: Obtener los lotes de acuerdo al filtro de la pantalla  a partir del ejercicio 2017 segun sea el caso
 *
 **/
ini_set('display_errors', 1);
session_start();
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once '../../../../lib/tes/consulta/nivelConsultaLote.php';
require_once '../../../../lib/AccesoDatos.php';

class ExcelReporteLote extends generaPhpspreadsheet
{
    public string|null $encabLin1;
    public string|null $encabLin2;
    public string|null $encabLin3;
    public  $encabLin4;
    public  $encabLin5;
    public  $encabLin6;
    public  $encabLin7;
    public $datos;
    public $nombreArchivoReporte;
    public $nombreColumnas;
    public function __construct()
    {
        parent::__construct();

        $lot_ejercicio = isset($_POST['comboEjercicio']) ? $_POST['comboEjercicio'] : '';
        $fpa_id = isset($_POST['comboFormaPago']) ? $_POST['comboFormaPago'] : '';
        $lot_tipoCereCo = isset($_POST['comboDocumento']) ? $_POST['comboDocumento'] : '';
        $tlo_id = isset($_POST['comboTipoLote']) ? $_POST['comboTipoLote'] : '';
        $fechaMin = isset($_POST['inputFechaMin']) ? $_POST['inputFechaMin'] : '';
        $fechaMax = isset($_POST['inputFechaMax']) ? $_POST['inputFechaMax'] : '';
        $lote = isset($_POST['inputLote']) ? $_POST['inputLote'] : '';
        $pro_id = isset($_POST['inputPropuesta']) ? $_POST['inputPropuesta'] : '';

        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'SECRETARÍA DE FINANZAS TESORERÍA ' . APP_EJERCICIO;
        $this->encabLin3 = 'TESORERÍA';
        $this->encabLin4 = 'LISTADOS DE LOTES';
        $this->encabLin6 = '';

        $this->encabLin5 = '';
        $this->datos = nivelConsultaLote($lot_ejercicio, $fpa_id, $lot_tipoCereCo, $tlo_id, $fechaMin, $fechaMax, $lote, $pro_id);
        $this->encabLin7 = '';
        $this->nombreArchivoReporte = 'ReporteLote';

        $this->contenido(); //llamando a la function contenido

    } //fin construct
    public function contenido()
    {
        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7' => $this->encabLin7, 'alineacionenc' => 'I');

        $this->nombreColumnas = array("#", "MODO", "TIPO", "EJERCICIO", "LOTE", "PROPUESTA", "ESTADO LOTE", "FECHA PAGO", "IMPORTE");
        $incluirColumnas = array("contador", "modo", "tipo", "lot_ejercicio", "lote", "pro_id", "elo_nombre", "lot_fechaPago", "lot_importe");
        $tipoColumnas = array("cC", "cI", "cC", "f", "cC", "cC", "cI", "cI", "n");

        $table = array();

        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }

        $path_image = dirname(__FILE__) . "/../../../../img/LOGO.jpg";
        $tablaImagen = array("imagen" => 1);
        $tablaImagen[0] = array("ruta" => $path_image, "nombre" => "XXX", "colImg" => "F1", "altura" => 100, "alingXimg" => 100);
        //llamado a generaExcel de generaPhpspreadsheet
        $this->generaExcel($this->datos, $encabezado, $table, array("nombre" => $this->nombreArchivoReporte, "tablaimg" => $tablaImagen));
    }

} // fin clase

$exp_ajustes = new ExcelReporteLote();
