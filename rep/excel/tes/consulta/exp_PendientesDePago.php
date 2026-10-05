<?php

/**
 * Description of Exp_PendientesDePago
 *
 * @author 
 * Collaborators: Adriana Santiago Morales
 * Created: 12-Junio-2019
 * Comment: -----
 * Objective: Generar un archivo Excel de las CLC´s pendientes el cual depende del año enviado
 * Regla de Negocio: -----
 * Permits: Esta pantalla no pregunta por permisos de lectura ni de escritura.
 **/
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '54000M');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once(dirname(__FILE__) . '/../../../../lib/tes/reportes/consultaPendientesDePago.php');

class Exp_PendientesDePago extends generaPhpspreadsheet
{
    public string | null $encabLin1;
    public string | null $encabLin2;
    public string | null $encabLin3;
    public  $encabLin4;
    public  $encabLin5;
    public  $encabLin6;
    public  $encabLin7;
    public $datos;
    public $nombreArchivoReporte;
    public $nombreColumnas;
    public $anio;
    function __construct()
    {
        parent::__construct();

        $this->anio = (isset($_POST['anio_id']) && $_POST['anio_id'] != '') ? $_POST['anio_id'] : '';



        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'TESORERÍA ' . APP_EJERCICIO;
        $this->encabLin3 = 'SECRETARÍA DE FINANZAS';
        $this->encabLin4 = 'CLC´S ' . $this->anio . ' PENDIENTES DE PAGO';
        $this->encabLin5 = '';
        $this->encabLin6 = '';

        $this->datos = consultaPendientesDePago($this->anio);

        $this->encabLin7 = '';
        $this->nombreArchivoReporte = 'CLC´S ' . $this->anio . ' PENDIENTES DE PAGO';

        $this->contenido();
    }
    public function contenido()
    {
        $encabezado = array(
            'encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7' => $this->encabLin7, 'alineacionenc' => 'I'
        );

        $this->nombreColumnas = array(
            'MPC_ID', 'UR', 'UR_NOMBRE', 'UE_ID', 'TIPODOC', 'ORDEN_TRASNF_BANC', 'ESTADO_CLC',
            'CLC', 'TCL_NOMBRE', 'STC_NOMBRE', 'CONCEPTO', 'NETO', 'FECHA_ELEBORACION', 'FECHA_ENVIO', 'AUTORIZACION',
            'FECHA_OTB', 'CVEFIN', 'CAT_BANCO_CARGO', 'CAT_BANCO_ABONO', 'UCB_NOMBRE', 'LOTE_EGRESOS', 'LOTE_PAGO',
            'INGRESO_FECHA_PAGO', 'FECHA', 'SPEI'
        );
        $incluirColumnas = array(
            'mpc_id', 'ur', 'ur_nombre', 'ue', 'tipodoc', 'otb', 'edo_clc', 'clc', 'tcl_nombre',
            'stc_nombre', 'concepto', 'neto', 'fecha_elaboracion', 'fecha_envio', 'autorizacion', 'fecha_otb', 'cvefin',
            'cat_banco_cargo', 'cat_banco_abono', 'ucb_nombre', 'lote_egresos', 'lote_pago', 'fecha_pago', 'fecha', 'spei'
        );

        $tipoColumnas = array('cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'n', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC');

        $table = array();
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }
        $name = 114;
        $path_image = dirname(__FILE__) . '/../../../../img/Logo Institucional/' . $name . '.jpg';
        $tablaImagen = array('imagen' => 1);
        $tablaImagen[0] = array('ruta' => $path_image, 'nombre' => 'XXX', 'colImg' => 'H1', 'altura' => 100, 'alingXimg' => 0);
        $this->generaExcel($this->datos, $encabezado, $table, array('nombre' => $this->nombreArchivoReporte, 'tablaimg' => $tablaImagen));
    }
}
$exp = new Exp_PendientesDePago();
