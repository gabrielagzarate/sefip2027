<?php
ini_set('display_errors', 1);
session_start();
require_once '../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once '../../../../lib/tes/consulta/consultaDetalleMunicipiosDocumentos.php';

class ExcelDetalleMunicipiosDocumentos extends generaPhpspreadsheet
{
    public string | null $encabLin1;
        public string | null $encabLin2;
        public string | null $encabLin3;
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

        $fpa_id = isset($_POST['fpa_id']) ? $_POST['fpa_id'] : '';
        $msc_id = isset($_POST['msc_id']) ? $_POST['msc_id'] : '';
        $lot_ejercicio = isset($_POST['lot_ejercicio']) ? $_POST['lot_ejercicio'] : '';
        $lot_id = isset($_POST['lot_id']) ? $_POST['lot_id'] : '';
        $lote = isset($_POST['lote']) ? $_POST['lote'] : '';

        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'SECRETARÍA DE FINANZAS TESORERÍA '.APP_EJERCICIO;
        $this->encabLin3 = 'TESORERÍA';
        $this->encabLin4 = 'REPORTE POR DOCUMENTOS DEL LOTE ' . $lote;
        $this->encabLin6 = '';

        $this->encabLin5 = '';
        $this->datos = consultaDetalleMunicipiosDocumentos($lot_ejercicio, $fpa_id, $msc_id, $lot_id);
        $this->encabLin7 = '';

        $hoy = date('Y-m-d'); // Obtiene fecha actual
        $this->nombreArchivoReporte = 'ReporteLoteMunicipio-' . $lote;
        $this->contenido(); //llamando a la function contenido
    } //fin construct

    public function contenido() {
    	$encabezado = array(
            'encabLin1' => $this->encabLin1,
            'encabLin2' => $this->encabLin2,
            'encabLin3' => $this->encabLin3,
            'encabLin4' => $this->encabLin4,
            'encabLin5' => $this->encabLin5,
            'encabLin6' => $this->encabLin6,
           'encabLin7' => $this->encabLin7,
            'alineacionenc'=>'I'
        );

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
            "ESTADO"
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
            "clc_id",
            "clc_cereCo",
            "benef",
            "concepto",
            "importe",
            "banco",
            "ctaCargo",
            "nombre_cta",
            "ctaAbono",
            "ecf_clave",
            "lote",
            "estado"
        );
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
            "cC",
            "cC",
            "cC",
            "cC",
            "cC",
            "cC",
            "cC",
            "cC"
        );
        $table = array();

        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }

        $path_image = dirname(__FILE__) . "/../../../../img/LOGO.jpg";
        $tablaImagen = array("imagen" => 1);
        $tablaImagen[0] = array("ruta" => $path_image, "nombre" => "XXX", "colImg" => "F1", "altura" => 100, "alingXimg" => 100);

        //Columnas que queremos que sean tipo Texto en donde el valor
        //inicia con cero o numero pero no queremos
        //que tenga formato por que son cuentas bancarias
        $columnas = ['O','Q'];

        //llamado a generaExcel de generaPhpspreadsheet
        $this->generaExcel($this->datos,$encabezado,$table,array("nombre" => $this->nombreArchivoReporte, "tablaimg" => $tablaImagen,"formatoTexto"=>$columnas));

    } // fin contenido

} //fin class
$exp_ajustes = new ExcelDetalleMunicipiosDocumentos();