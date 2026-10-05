<?php
    /**
         * Description of function nivelFolio
         *
         * @author ?
         * Collaborators: Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
         * Created: 12-Dic-2019
         * Comment: -----
         * Objective: Mostrar en un Excel datos detallados de lotes
         * Regla de Negocio: Obtener el detalle del lote de acuerdo al filtro de la pantalla  a partir del ejercicio 2017 segun sea el caso
         *
    **/
    ini_set('display_errors',1);
    set_time_limit(600);
    session_start();
    require_once dirname(__FILE__).'/../../../lib/generaPhpspreadsheet.php';
    require_once('../../../../lib/tes/consulta/consultaLoteDetallado.php');
    require_once('../../../../lib/AccesoDatos.php');

    class ExcelReporteLoteDetallado extends generaPhpspreadsheet
    {
        public $fpa_id;
        public $tlo_id;
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
        function __construct()
        {
            parent::__construct();

            $lot_ejercicio = isset($_POST['comboEjercicio']) ? $_POST['comboEjercicio'] : '';
            $this->fpa_id = isset($_POST['comboFormaPago']) ? $_POST['comboFormaPago'] : '';
            $lot_tipoCereCo = isset($_POST['comboDocumento']) ? $_POST['comboDocumento'] : '';
            $this->tlo_id = isset($_POST['comboTipoLote']) ? $_POST['comboTipoLote'] : '';
            $fechaMin = isset($_POST['inputFechaMin']) ? $_POST['inputFechaMin'] : '';
            $fechaMax = isset($_POST['inputFechaMax']) ? $_POST['inputFechaMax'] : '';
            $lote = isset($_POST['inputLote']) ? $_POST['inputLote'] : '';
            $pro_id = isset($_POST['inputPropuesta']) ? $_POST['inputPropuesta'] : '';

            $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
            $this->encabLin2 = 'SECRETARÍA DE FINANZAS TESORERÍA '.APP_EJERCICIO;
            $this->encabLin3 = 'TESORERÍA';
            $this->encabLin4 = 'REPORTE DEL LOTE DETALLADO';
            $this->encabLin6 = '';

            $this->encabLin5 = '';
            $this->datos = consultaLoteDetallado($lot_ejercicio, $this->fpa_id, $lot_tipoCereCo, $this->tlo_id, $fechaMin,  $fechaMax,  $lote, $pro_id);
            $this->encabLin7 = '';

            $hoy = date('Y-m-d');  // Obtiene fecha actual para impresión
            $this->nombreArchivoReporte = 'ReporteLotesDetallado-' . $hoy;

            $this->contenido(); //llamando a la function contenido
        }//fin constructor

        public function contenido()
        {
            $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7' => $this->encabLin7,'alineacionenc'=>'I');

            if ($this->tlo_id == 5 && $this->fpa_id == 3) {
                $this->nombreColumnas = array("#", "MODO","TIPO","EJERCICIO","FECHA GENERACIÓN","FECHA GENERACIÓN CERE/CERECO","LOTE EGRESO","UR","CLC","CERE/CERECO","BENEFICIARIO","CONCEPTO","IMPORTE","BANCO","CUENTA CARGO","NOMBRE CUENTA","CUENTA DESTINO/LINEA","CLAVE FINANCIAMIENTO","LOTE","ESTADO","CUENTA CONTABLE");

                $incluirColumnas = array("contador","modo","tipo","lot_ejercicio","lot_fechaGeneracion","lot_fechaPago","clc_generacionCereCo","ur","clc","clc_cereCo","beneficiario","concepto","importe","banco","origen","nombre_cta","linea","ecf_clave","lote","estado","contable");

            }
            else{
                $this->nombreColumnas = array("#", "MODO","TIPO","EJERCICIO","FECHA GENERACIÓN","FECHA GENERACIÓN CERE/CERECO","LOTE EGRESO","UR","CLC","CERE/CERECO","BENEFICIARIO","CONCEPTO","IMPORTE","BANCO","CUENTA CARGO","NOMBRE CUENTA","CUENTA DESTINO/LINEA","CLAVE FINANCIAMIENTO","LOTE","ESTADO","CUENTA CONTABLE");

                $incluirColumnas = array("contador","modo","tipo","lot_ejercicio","lot_fechaGeneracion","lot_fechaPago","clc_generacionCereCo","ur","clc","clc_cereCo","beneficiario","concepto","importe","banco","origen","nombre_cta","destino","ecf_clave","lote","estado","contable");
            }//15,17

            if ($this->tlo_id == 5 && $this->fpa_id == 3) {
                $tipoColumnas = array("cC", "cC", "cC","cC","cC","cC","cC","cC","cC","cC","cC","cC","n","cC","n","cC","n","cC","cC","cC","cC");
                $anchoColumnas = array(2,9,13,11,19,34,23,4,8,14,35,100,15,36,25,22,24,24,11,49,22);
            } else {
                $tipoColumnas = array("cC", "cC", "cC","cC","cC","cC","cC","cC","cC","cC","cC","cC","n","cC","cD","cC","cD","cC","cC","cC","cC");
                $anchoColumnas = array(2,9,13,11,19,34,23,4,8,14,35,100,15,36,25,22,24,24,11,49,22);
            }

            $table = array();

            foreach ($incluirColumnas as $key => $val) {
                $table['nombre'][$val] = $this->nombreColumnas[$key];
                $table['ancho'][$val] = $anchoColumnas[$key];
                $table['campo'][$val] = $key;
                $table['tipo'][$val] = $tipoColumnas[$key];
                // $table['tipoTabla']= "M";
            }

            $path_image     = dirname(__FILE__)."/../../../../img/LOGO.jpg";
            $tablaImagen    = array("imagen"=>1);
            $tablaImagen[0] = array("ruta"=>$path_image,"nombre"=>"XXX","colImg"=>"F1","altura"=>100,"alingXimg"=>100);

            //Columnas que queremos que sean tipo Texto en donde el valor
            //inicia con cero o numero pero no queremos
            //que tenga formato por que son cuentas bancarias
            $columnas = ['O','Q'];

            //Llamado a generaExcel de generaPhpspreadsheet
            $this->generaExcel($this->datos,$encabezado,$table,array("nombre"=>$this->nombreArchivoReporte,"tablaimg"=>$tablaImagen,"formatoTexto"=>$columnas));

        }//fin contenido

    }//fin clase
    $exp_ajustes = new ExcelReporteLoteDetallado();
?>
