<?php
//ini_set('display_errors',1);
session_start();
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../tes/models/clcs2017.php';
require_once '../../../../tes/models/clcs2018.php';
require_once '../../../../tes/models/clcs2019.php';
require_once '../../../../tes/models/clcs2020.php';
require_once '../../../../tes/models/clcs2021.php';
require_once '../../../../tes/models/clcs2022.php';
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once '../../../../lib/tes/consulta/consultaReporteManual.php';

class ExcelReporteManual extends generaPhpspreadsheet
{

    public string|null $encabLin1;
    public string|null $encabLin2;
    public string|null $encabLin3;
    public $encabLin4;
    public $encabLin5;
    public $encabLin6;
    public $encabLin7;
    public $datos;
    public $nombreArchivoReporte;
    public $nombreColumnas;

    public function __construct()
    {
        parent::__construct();

        $lot_ejercicio = isset($_POST['lot_ejercicio']) ? $_POST['lot_ejercicio'] : '';
        $msc_id = isset($_POST['msc_id']) ? $_POST['msc_id'] : '';
        $fpa_id = isset($_POST['fpa_id']) ? $_POST['fpa_id'] : '';
        $lot_id = isset($_POST['lot_id']) ? $_POST['lot_id'] : '';

        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'SECRETARÍA DE FINANZAS TESORERÍA ' . APP_EJERCICIO;
        $this->encabLin3 = 'TESORERÍA';
        $this->encabLin4 = 'LISTADOS DE CLCs MANUALES';
        $this->encabLin6 = '';

        $this->encabLin5 = '';
        $this->datos = consultaReporteManual($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
        $this->encabLin7 = '';
        $this->nombreArchivoReporte = 'ReporteManual';

        $this->contenido(); //llamando a la function contenido

    } //fin construct

    public function contenido()
    {

        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7' => $this->encabLin7, 'alineacionenc' => 'I');

        $this->nombreColumnas = array("FECHA DE ELABORACION", "MODO", "LOTE", "BENEFICIARIO", "CONCEPTO", "CARGO", "DESTINO", "IMPORTE");
        $incluirColumnas = array("", "MANUAL", "lote_id", "lom_beneficiario", "lom_concepto", "cbs_id", "cbb_id", "lom_importe");
        $tipoColumnas = array("f", "cC", "cC", "cC", "cC", "cC", "cC", "n");

        $table = array();

        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }

        $path_image = dirname(__FILE__) . "/../../../../img/Logo Institucional/114.jpg";
        $tablaImagen = array("imagen" => 1);
        $tablaImagen[0] = array("ruta" => $path_image, "nombre" => "XXX", "colImg" => "F1", "altura" => 100, "alingXimg" => 100);
        //llamado a generaExcel de generaPhpspreadsheet
        $this->generaExcel($this->datos, $encabezado, $table, array("nombre" => $this->nombreArchivoReporte, "tablaimg" => $tablaImagen));

    }

} //fin class

$exp_ajustes = new ExcelReporteManual();
