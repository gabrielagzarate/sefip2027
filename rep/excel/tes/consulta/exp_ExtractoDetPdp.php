<?php
/**
 * Description of Exp_ExtractoDetPdp
 *
 * @author Adriana Santiago Morales <adriana.morales@finanzasoaxaca.gob.mx>
 * Collaborators:
 * Created: 12-Feb-2019
 * Comment: -----
 * Objective: Generar un archivo Excel con las referencias del Extracto Bancario de PDP.
 * Regla de Negocio: -----
 * Permits: -----
 * */
session_start();
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/tes/consulta/consultaExtractoDetPdp.php';

class Exp_ExtractoDetPdp extends generaPhpspreadsheet
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
    public function __construct()
    {
        parent::__construct();

        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'TESORERÍA ' . APP_EJERCICIO;
        $this->encabLin3 = 'SECRETARÍA DE FINANZAS';
        $this->encabLin4 = 'REPORTE EXTRACTO BANCARIO PDP';
        $this->encabLin5 = '';
        $this->encabLin6 = '';

        $this->encabLin5 = '';
        $fechaMin = (isset($_POST['fechaMin']) && $_POST['fechaMin'] != '') ? $_POST['fechaMin'] : '';
        $fechaMax = (isset($_POST['fechaMax']) && $_POST['fechaMax'] != '') ? $_POST['fechaMax'] : '';
        $fechaMin = DateTime::createFromFormat('d/m/Y', $fechaMin);
        $fechaMin = $fechaMin->format('Y-m-d');
        $fechaMax = DateTime::createFromFormat('d/m/Y', $fechaMax);
        $fechaMax = $fechaMax->format('Y-m-d');
        $this->datos = consultaExtractoDetPdp(true, $fechaMin, $fechaMax);

        $this->encabLin7 = '';

        $hoy = date('Y-m-d');
        $this->nombreArchivoReporte = 'ReporteExtractoBancarioPDP-' . $hoy;

        $this->contenido();
    }

    public function contenido()
    {
        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7' => $this->encabLin7, 'alineacionenc' => 'I');

        $this->nombreColumnas = array('#', 'CTA_CARGO', 'REFERENCIA', 'CARGO', 'ABONO', 'IMPORTE',
            'FECHA TRANSACCIÓN', 'DESCRIPCIÓN', 'SPEI', 'UR', 'CLC', 'CERE/CERECO');
        $incluirColumnas = array('contador', 'cbs_id', 'exb_referencia', 'exb_cargo', 'exb_abono',
            'exb_importe', 'exb_fechaTransaccion', 'exb_descripcion', 'exb_spei', 'ur', 'clc_id', 'clc_cereCo');
        $tipoColumnas = array('cC', 'cC', 'cC', 'n', 'n', 'n', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC');

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
$exp = new Exp_ExtractoDetPdp();
