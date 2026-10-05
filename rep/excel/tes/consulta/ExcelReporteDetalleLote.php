<?php
ini_set('display_errors', 1);
session_start();
require_once '../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once '../../../../lib/tes/consulta/consultaDetalleLote.php';

class ExcelReporteDetalleLote extends generaPhpspreadsheet
{
    public string | null $encabLin1;
    public string | null $encabLin2;
    public string | null $encabLin3;
    public  $encabLin4;
    public  $encabLin5;
    public  $encabLin6;
    public  $encabLin7;
    public  $datos;
    public $nombreArchivoReporte;
    public $nombreColumnas;

    public function __construct()
    {
        parent::__construct();

        $fpa_id = isset($_POST['fpa_id']) ? $_POST['fpa_id'] : '';
        $msc_id = isset($_POST['msc_id']) ? $_POST['msc_id'] : '';
        $lot_ejercicio = isset($_POST['lot_ejercicio']) ? $_POST['lot_ejercicio'] : '';
        $lot_id = isset($_POST['lot_id']) ? $_POST['lot_id'] : '';
        $tlo_id = isset($_POST['tlo_id']) ? $_POST['tlo_id'] : '';
        $lote = isset($_POST['lote']) ? $_POST['lote'] : '';

        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'SECRETARÍA DE FINANZAS TESORERÍA '.APP_EJERCICIO;
        $this->encabLin3 = 'COORDINACION DE CONTROL FINANCIERO';
        $this->encabLin4 = 'DEPARTAMENTO DE PROGRAMACION DE MINISTRACIONES Y PAGOS';
        $this->encabLin6 = '';

        $this->encabLin5 = 'REPORTE DEL LOTE ' . $lote;
        $this->datos = consultaDetalleLote($lot_ejercicio, $fpa_id, $msc_id, $tlo_id, $lot_id);
        $this->encabLin7 = '';

        $this->nombreArchivoReporte = 'ReporteLote-' . $lote;
        $this->contenido(); //llamando a la function contenido
    } //fin construct

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

        if ($_POST['tlo_id'] == 5 && $_POST['fpa_id'] == 3) { //Lote federal de linea de captura
            $this->nombreColumnas = array(
                "#",
                "MODO",
                "TIPO",
                "EJERCICIO",
                "FECHA GENERACIÓN",
                "FECHA PAGO",
                "FECHA GENERACIÓN CERE/CERECO",
                "UR",
                "CLC",
                "TIPO",
                "CERE/CERECO",
                "BENEFICIARIO",
                "CONCEPTO",
                "IMPORTE",
                "BANCO",
                "CUENTA CARGO",
                "NOMBRE CUENTA",
                "LINEA DE CAPTURA",
                "CLAVE FINANCIAMIENTO",
                "LOTE",
            );

            $incluirColumnas = array(
                "contador",
                "modo",
                "tipo",
                "lot_ejercicio",
                "lot_fechaGeneracion",
                "lot_fechaPago",
                "clc_generacionCereCo",
                "ur",
                "clc",
                "tipo",
                "clc_cereCo",
                "beneficiario",
                "concepto",
                "importe",
                "banco",
                "origen",
                "nombre_cta",
                "linea",
                "ecf_clave",
                "lote",
            );
        } else {
            $this->nombreColumnas = array(
                "#",
                "MODO",
                "TIPO",
                "EJERCICIO",
                "FECHA GENERACIÓN",
                "FECHA PAGO",
                "FECHA GENERACIÓN CERE/CERECO",
                "UR",
                "CLC",
                "TIPO",
                "CERE/CERECO",
                "BENEFICIARIO",
                "CONCEPTO",
                "IMPORTE",
                "BANCO",
                "CUENTA CARGO",
                "NOMBRE CUENTA",
                "CUENTA DESTINO",
                "CLAVE FINANCIAMIENTO",
                "LOTE",
            );
            $incluirColumnas = array(
                "contador",
                "modo",
                "tipo",
                "lot_ejercicio",
                "lot_fechaGeneracion",
                "lot_fechaPago",
                "clc_generacionCereCo",
                "ur",
                "clc",
                "tipo",
                "clc_cereCo",
                "beneficiario",
                "concepto",
                "importe",
                "banco",
                "origen",
                "nombre_cta",
                "destino",
                "ecf_clave",
                "lote",
            );
        }

        if ($_POST['tlo_id'] == 6 || $_POST['tlo_id'] == 7) { //Lote manual
            $this->nombreColumnas = array(
                "#",
                "MODO",
                "TIPO",
                "EJERCICIO",
                "FECHA GENERACIÓN",
                "FECHA PAGO",
                "FECHA GENERACIÓN CERE/CERECO",
                "UR",
                "CLC",
                "TIPO",
                "CERE/CERECO",
                "BENEFICIARIO",
                "CONCEPTO",
                "IMPORTE",
                "BANCO",
                "CUENTA CARGO",
                "NOMBRE CUENTA",
                "CUENTA DESTINO",
                "CUENTA CONTABLE",
                "CLAVE FINANCIAMIENTO",
                "LOTE",
            );
            $incluirColumnas = array(
                "contador",
                "modo",
                "tipo",
                "lot_ejercicio",
                "lot_fechaGeneracion",
                "lot_fechaPago",
                "clc_generacionCereCo",
                "ur",
                "clc",
                "tipo",
                "clc_cereCo",
                "beneficiario",
                "concepto",
                "importe",
                "banco",
                "origen",
                "nombre_cta",
                "destino",
                "contable",
                "ecf_clave",
                "lote",
            );
        }

        $tipoColumnas = array(
            "cC",
            "cC",
            "cC",
            "cC",
            "cC",
            "cC",
            "cC",
            "cC",
            "cC",
            "cC",
            "cC",
            "cC",
            "cC",
            "n",
            "cC",
            "cD",
            "cC",
            "cD",
            "cC",
            "cC",
            "cC",
        );
        $table = array();

        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }
        //print_r($this->datos); die();
        $path_image = dirname(__FILE__) . "/../../../../img/LOGO.jpg";
        $tablaImagen = array("imagen" => 1);
        $tablaImagen[0] = array("ruta" => $path_image, "nombre" => "XXX", "colImg" => "F1", "altura" => 100, "alingXimg" => 100);

        //Columnas que queremos que sean tipo Texto en donde el valor
        //inicia con cero o numero pero no queremos
        //que tenga formato por que son cuentas bancarias
        $columnas = ['O','Q'];

        //llamado a generaExcel de generaPhpspreadsheet
        $this->generaExcel($this->datos, $encabezado, $table, array("nombre" => $this->nombreArchivoReporte, "tablaimg" => $tablaImagen,"formatoTexto"=>$columnas));

    } // fin contenido

} // fin class
$exp_ajustes = new ExcelReporteDetalleLote();
