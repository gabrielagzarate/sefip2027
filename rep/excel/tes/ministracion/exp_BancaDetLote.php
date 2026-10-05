<?php
/**
 * Description of Exp_BancaDetLote
 *
 * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Collaborators: Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Created: 15-Ene-2019
 * Updated: 23-10-2023
 * Comment: -----
 * Objective: Generar un archivo Excel de los detalles del lote.
 * Regla de Negocio: -----
 * Permits: Esta pantalla no pregunta por permisos de lectura ni de escritura.
 * */
session_start();

ini_set('memory_limit', '20M');
set_time_limit(0);

require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2017.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2018.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2019.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2020.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2021.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2022.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2023.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2024.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2025.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2026.php';
require_once dirname(__FILE__) . '/../../../../lib/tes/ministracion/formatoDatosBancaDetLote.php';

class Exp_BancaDetLote extends generaPhpspreadsheet
{
    public $lot_id;
    public $fpa_id;
    public $msc_id;
    public $lot_ejercicio;
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

        $this->lot_id = (isset($_POST['lot_id']) && $_POST['lot_id'] != '') ? $_POST['lot_id'] : '';
        $this->fpa_id = (isset($_POST['fpa_id']) && $_POST['fpa_id'] != '') ? $_POST['fpa_id'] : '';
        $this->msc_id = (isset($_POST['msc_id']) && $_POST['msc_id'] != '') ? $_POST['msc_id'] : '';
        $this->lot_ejercicio = (isset($_POST['lot_ejercicio']) && $_POST['lot_ejercicio'] != '') ? $_POST['lot_ejercicio'] : '';

        $tipoPago = $this->fpa_id;
        switch ($tipoPago) {
            case 1:
                $tipoPago = 'T';
                break;
            case 2:
                $tipoPago = 'C';
                break;
            case 3:
                $tipoPago = 'L';
                break;
        }
        $anio = substr($this->lot_ejercicio, 2, 2);
        $mes = str_pad($this->msc_id, 2, "0", STR_PAD_LEFT);
        $lot_id = str_pad($this->lot_id, 4, "0", STR_PAD_LEFT);
        $hoy = date("Y-m-d H:i:s"); // obtiene la hora actual para su impresion
        $LOTE = $tipoPago . $mes . $lot_id . $anio;
        $ruta = $LOTE . "_" . $hoy;

        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'SECRETARÍA DE FINANZAS TESORERÍA ' . APP_EJERCICIO;
        $this->encabLin3 = '116-COORDINACIÓN DE CONTROL FINANCIERO';
        $this->encabLin4 = 'DETALLE DEL LOTE: ' . $LOTE;
        $this->encabLin6 = '';

        $this->encabLin5 = '';
        $this->datos = formatoDatosBancaDetLote($this->lot_id, $this->fpa_id, $this->msc_id, $this->lot_ejercicio);
        $this->encabLin7 = '';
        $this->nombreArchivoReporte = $ruta;

        $this->contenido();
    }
    public function contenido()
    {
        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7' => $this->encabLin7, 'alineacionenc' => 'I');

        $this->nombreColumnas = array('#', 'FE.PAGO', 'UR', 'CLC', 'TIPO', 'FOLIO');
        if ($this->fpa_id == 3) {
            array_push($this->nombreColumnas, 'FOLIO_LINEACAPTURA', 'LINEA_CAPTURA');
        }
        array_push($this->nombreColumnas, 'IMPORTE', 'RESPONSABLE', 'BENEFICIARIO', 'CONCEPTO', 'BANCO', 'CUENTA CARGO', 'CUENTA DESTINO', 'CVE. FIN', 'LOTE');

        $incluirColumnas = array('contador', 'lotDet_fechaPago', 'lotDet_ur', 'lotDet_clc', 'lotDet_tipo', 'lotDet_folio');
        if ($this->fpa_id == 3) {
            array_push($incluirColumnas, 'lotDet_folioL', 'lotDet_lineaC');
        }
        array_push($incluirColumnas, 'lotDet_importe', 'lotDet_responsable', 'lotDet_beneficiario', 'lotDet_concepto', 'banco', 'lotDet_CtaCargo', 'lotDet_CtaAbono', 'cvefin', 'lotDet');

        $tipoColumnas = array('cC', 'f', 'cC', 'cC', 'cC', 'cC');
        if ($this->fpa_id == 3) {
            array_push($tipoColumnas, 'cC', 'cC');
        }
        array_push($tipoColumnas, 'n', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC');

        $table = array();
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }
        $name = 114;
        $path_image = dirname(__FILE__) . '/../../../../img/Logo Institucional/' . $name . '.jpg';
        $tablaImagen = array('imagen' => 1);
        $tablaImagen[0] = array('ruta' => $path_image, 'nombre' => 'XXX', 'colImg' => 'I1', 'altura' => 100, 'alingXimg' => 50);
        $this->generaExcel($this->datos, $encabezado, $table, array('nombre' => $this->nombreArchivoReporte, 'tablaimg' => $tablaImagen));
    }
}
$exp = new Exp_BancaDetLote();
