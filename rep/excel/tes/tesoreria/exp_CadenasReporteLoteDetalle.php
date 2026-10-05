<?php

/**
 * Description of Exp_CadenasReporteLoteDetalle
 *
 * @author Adriana Santiago Morales <adriana.morales@finanzasoaxaca.gob.mx>
 * Collaborators:
 * Created: 06-Ene-2019
 * Updated: 10-Nov-2022
 * Comment: -----
 * Objective: Generar un archivo Excel con el detalle de las CLC´S del lote.
 * Regla de Negocio: -----
 * Permits: Esta pantalla no pregunta por permisos de lectura ni de escritura.
 * */
session_start();
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/tes/tesoreria/consultaCadenasDetalleLote.php';

class Exp_CadenasReporteLoteDetalle extends generaPhpspreadsheet
{

    public function __construct()
    {
        parent::__construct();

        $this->fpa_id = isset($_POST['fpa_id']) ? $_POST['fpa_id'] : '';
        $this->msc_id = isset($_POST['msc_id']) ? $_POST['msc_id'] : '';
        $this->lot_ejercicio = isset($_POST['lot_ejercicio']) ? $_POST['lot_ejercicio'] : '';
        $this->lot_id = isset($_POST['lot_id']) ? $_POST['lot_id'] : '';
        $this->tlo_id = isset($_POST['tlo_id']) ? $_POST['tlo_id'] : '';
        $this->lote = isset($_POST['lote']) ? $_POST['lote'] : '';

        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'SECRETARÍA DE FINANZAS';
        $this->encabLin3 = 'REPORTE DE LOTE ' . $this->lote;
        $this->encabLin4 = '';
        $this->encabLin6 = '';

        $this->encabLin5 = '';
        $this->datos = consultaCadenasDetalleLote($this->fpa_id, $this->msc_id, $this->lot_ejercicio, $this->lot_id, $this->tlo_id);

        $this->encabLin7 = '';

        $hoy = date('Y-m-d');
        $this->nombreArchivoReporte = 'ReporteLote-' . $this->lote . $hoy;

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
            'alineacionenc' => 'I'
        );

        $this->nombreColumnas = array(
            '#',
            'MODO',
            'TIPO',
            'EJERCICIO',
            'FECHA GENERACIÓN',
            'FECHA PAGO',
            'FECHA GENERACIÓN CERE/CERECO',
            'LOTE EGRESO',
            'UR',
            'CLC',
            'CERE/CERECO',
            'BENEFICIARIO',
            'CONCEPTO',
            'IMPORTE',
            'BANCO',
            'CUENTA CARGO',
            'NOMBRE CUENTA',
            'CLAVE FINANCIAMIENTO',
            'LOTE',
            'ESTADO'
        );
        $incluirColumnas = array(
            'contador',
            'modo',
            'tipo',
            'lot_ejercicio',
            'lot_fechaGeneracion',
            'lot_fechaPago',
            'clc_generacionCereCo',
            'lote_egreso',
            'ur',
            'clc',
            'clc_cereCo',
            'beneficiario',
            'concepto',
            'importe',
            'banco',
            'origen',
            'nombre_cta',
            'ecf_clave',
            'lote',
            'estado'
        );
        $tipoColumnas = array('cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'n', 'n', 'n', 'n', 'n', 'cC', 'cC');

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

$exp = new Exp_CadenasReporteLoteDetalle();
