<?php

/**
 * Description of Exp_CadenasReporteLotes
 *
 * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Collaborators: Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Update: 21-10-2022
 * Comment: -----
 * Objective: Generar un archivo Excel de los lotes generados hoy.
 * Regla de Negocio: -----
 * Permits: Esta pantalla no pregunta por permisos de lectura ni de escritura.
 * */
session_start();
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/tes/tesoreria/consultaCadenas.php';

class Exp_CadenasReporteLotes extends generaPhpspreadsheet
{
    private $lot_ejercicio;
    private $fpa_id;
    private $lot_tipoCereCo;
    private $tlo_id;
    private $fechaMin;
    private $fechaMax;
    private $lote;
    private $pro_id;
    public $encabLin4;
    public $encabLin5;
    public $encabLin6;
    public $encabLin7;
    private $datos;
    private $nombreArchivoReporte;
    private $nombreColumnas;

    public function __construct()
    {
        parent::__construct();

        $this->lot_ejercicio = isset($_POST['comboEjercicio']) ? $_POST['comboEjercicio'] : '';
        $this->fpa_id = isset($_POST['comboFormaPago']) ? $_POST['comboFormaPago'] : '';
        $this->lot_tipoCereCo = isset($_POST['comboDocumento']) ? $_POST['comboDocumento'] : '';
        $this->tlo_id = isset($_POST['comboTipoLote']) ? $_POST['comboTipoLote'] : '';
        $this->fechaMin = isset($_POST['inputFechaMin']) ? $_POST['inputFechaMin'] : '';
        $this->fechaMax = isset($_POST['inputFechaMax']) ? $_POST['inputFechaMax'] : '';
        $this->lote = isset($_POST['inputLote']) ? $_POST['inputLote'] : '';
        $this->pro_id = isset($_POST['inputPropuesta']) ? $_POST['inputPropuesta'] : '';

        if (!empty($this->lot_ejercicio) && $this->lot_ejercicio == 1) {
            $this->lot_ejercicio = 2026;
        } elseif (!empty($this->lot_ejercicio) && $this->lot_ejercicio == 2) {
            $this->lot_ejercicio = 2025;
        } elseif (!empty($this->lot_ejercicio) && $this->lot_ejercicio == 3) {
            $this->lot_ejercicio = 2024;
        } elseif (!empty($this->lot_ejercicio) && $this->lot_ejercicio == 4) {
            $this->lot_ejercicio = 2023;
        } elseif (!empty($this->lot_ejercicio) && $this->lot_ejercicio == 5) {
            $this->lot_ejercicio = 2022;
        } elseif (!empty($this->lot_ejercicio) && $this->lot_ejercicio == 6) {
            $this->lot_ejercicio = 2021;
        } elseif (!empty($this->lot_ejercicio) && $this->lot_ejercicio == 7) {
            $this->lot_ejercicio = 2022;
        } elseif (!empty($this->lot_ejercicio) && $this->lot_ejercicio == 8) {
            $this->lot_ejercicio = 2019;
        } elseif (!empty($this->lot_ejercicio) && $this->lot_ejercicio == 9) {
            $this->lot_ejercicio = 2018;
        } elseif (!empty($this->lot_ejercicio) && $this->lot_ejercicio == 10) {
            $this->lot_ejercicio = 2017;
        }

        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'SECRETARÍA DE FINANZAS';
        $this->encabLin3 = 'REPORTE DE LOTES';
        $this->encabLin4 = '';
        $this->encabLin6 = '';

        $this->encabLin5 = '';
        $this->datos = consultaCadenas(1, $this->lot_ejercicio, $this->fpa_id, $this->lot_tipoCereCo, $this->tlo_id, $this->fechaMin, $this->fechaMax, $this->lote, $this->pro_id);

        $this->encabLin7 = '';

        $hoy = date('Y-m-d');
        $this->nombreArchivoReporte = 'ReporteLotes-' . $hoy;

        $this->contenido();
    }
    public function contenido()
    {
        $encabezado = array(
            'encabLin1' => $this->encabLin1,
            'encabLin2' => $this->encabLin2,
            'encabLin3' => $this->encabLin3,
            'encabLin4' => $this->encabLin4,
            'encabLin5' => $this->encabLin5,
            'encabLin6' => $this->encabLin6,
            'encabLin7' => $this->encabLin7,
            'alineacionenc' => 'I',
        );

        $this->nombreColumnas = array('#', 'MODO', 'TIPO', 'EJERCICIO', 'LOTE', 'PROPUESTA', 'ESTADO', 'FECHA', 'IMPORTE');
        $incluirColumnas = array('contador', 'modo', 'tipo', 'lot_ejercicio', 'lote', 'pro_id', 'elo_nombre', 'lot_fechaPago', 'lot_importe');
        $tipoColumnas = array('cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'n');

        $table = array();
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }
        $table['textoCompleto'] = false;
        $name = 114;
        $path_image = dirname(__FILE__) . '/../../../../img/Logo Institucional/' . $name . '.jpg';
        $tablaImagen = array('imagen' => 1);
        $tablaImagen[0] = array('ruta' => $path_image, 'nombre' => 'XXX', 'colImg' => 'F1', 'altura' => 100, 'alingXimg' => 0);
        $this->generaExcel($this->datos, $encabezado, $table, array('nombre' => $this->nombreArchivoReporte, 'tablaimg' => $tablaImagen));
    }
}
$exp = new Exp_CadenasReporteLotes();
