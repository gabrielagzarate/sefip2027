<?php
    /**
         * Description of function nivelFolio
         *
         * @author ?
         * Collaborators: Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
         * Created: 12-Dic-2019
         * Comment: -----
         * Objective: Mostrar en un Excel datos detallado de una Clc especifica
         * Regla de Negocio: Obtener el detalle de una clc especifca de acuerdo al filtro de la pantalla  a partir del ejercicio 2017 segun sea el caso
         * 
    **/
    ini_set('display_errors',1);
    session_start();
    require_once dirname(__FILE__).'/../../../lib/generaPhpspreadsheet.php';
    require_once('../../../../lib/tes/consulta/consultaDetalleClc.php');
    require_once('../../../../lib/AccesoDatos.php');

    class ExcelReporteDetalleClc extends generaPhpspreadsheet
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
        function __construct(){
            parent::__construct();

            $gpo_id = isset($_POST['gpo_id']) ? $_POST['gpo_id'] : '';
            $ur_id = isset($_POST['ur_id']) ? $_POST['ur_id'] : '';
            $clc_id = isset($_POST['clc_id']) ? $_POST['clc_id'] : '';
            $ejercicio = isset($_POST['ejercicio']) ? $_POST['ejercicio'] : '';

            $hoy = date('Y-m-d');  // Obtiene fecha actual para impresión
            $ruta = 'ReporteCLC-' . $gpo_id . $ur_id . $clc_id;

            $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
            $this->encabLin2 = 'SECRETARÍA DE FINANZAS TESORERÍA '.APP_EJERCICIO;
            $this->encabLin3 = 'TESORERÍA';
            $this->encabLin4 = 'LISTADOS DETALLE DE LA CLC';
            $this->encabLin6 = '';
            
            $this->encabLin5 = '';  
            $this->datos = consultaDetalleClc($ejercicio, $gpo_id, $ur_id, $clc_id);        
            $this->encabLin7 = '';
            $this->nombreArchivoReporte = 'ReporteCLC';

            $this->contenido(); //llamdo a la function contenido
        }//FIN CONSTRUCT

        public function contenido(){
            $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7' => $this->encabLin7,'alineacionenc'=>'I');

            $this->nombreColumnas = array("AFECTACIÓN", "DOCUMENTO", "CONCEPTO","REFERENCIA", "FECHA DOCUMENTO", "R.F.C. PROVEEDOR", "IMPORTE", ('PERCEPCIÓN'), ('RETENCIÓN'));
            $incluirColumnas = array("afe_id","bdo_id","bdo_concepto","bdo_referencia","bdo_fechaDocumento","rfc_id","importe","percepcion","retencion");
            $tipoColumnas = array("cC","c","cI","cI","f","cC","n","n","n");
            $anchoColumnas = array(15, 15, 55, 55, 25,18, 30, 30,30);

            $table = array();

            foreach ($incluirColumnas as $key => $val) {
                $table['nombre'][$val] = $this->nombreColumnas[$key];
                $table['campo'][$val] = $key;
                $table['tipo'][$val] = $tipoColumnas[$key];
                $table['ancho'][$val] = $anchoColumnas[$key];
            }
            
            $path_image     = dirname(__FILE__)."/../../../../img/LOGO.jpg";
            $tablaImagen    = array("imagen"=>1);
            $tablaImagen[0] = array("ruta"=>$path_image,"nombre"=>"XXX","colImg"=>"D1","altura"=>100,"alingXimg"=>100);
            //llamado a generaExcel de generaPhpspreadsheet
            $this->generaExcel($this->datos,$encabezado,$table,array("nombre"=>$this->nombreArchivoReporte,"tablaimg"=>$tablaImagen));

        }//FIN CONTENIDO
    }// FIN DE LA CLASE

    $exp_ajustes = new ExcelReporteDetalleClc();

?>