<?php
//ini_set('display_errors',1);
session_start();
require_once dirname(__FILE__).'/../../../lib/generaPhpspreadsheet.php';
require_once('../../../../lib/tes/consulta/consultaLotes.php');
require_once('../../lib/AccesoDatos.php');
require_once('../../tes/models/clcs2017.php');
require_once('../../tes/models/clcs2018.php');
require_once('../../tes/models/clcs2019.php');
require_once('../../tes/models/clcs2020.php');
require_once('../../tes/models/clcs2021.php');

class ExcelReporteLotes extends generaPhpspreadsheet{
    public $fpa_id;
    public $lot_tipo;
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

        $lot_id = (isset($_POST['lot_id']) && $_POST['lot_id'] !='') ? $_POST['lot_id'] : '';
        $fpa_id = (isset($_POST['fpa_id']) && $_POST['fpa_id'] !='') ? $_POST['fpa_id'] : '';
        $msc_id = (isset($_POST['msc_id']) && $_POST['msc_id'] !='') ? $_POST['msc_id'] : '';
        $lot_ejercicio = (isset($_POST['lot_ejercicio']) && $_POST['lot_ejercicio'] !='') ? $_POST['lot_ejercicio'] : '';
        $lot_tipo = (isset($_POST['lot_tipo']) && $_POST['lot_tipo'] !='') ? $_POST['lot_tipo'] : '';
        
        $this->nombreArchivoReporte = 'ReporteLotes';

        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'SECRETARÍA DE FINANZAS TESORERÍA '.APP_EJERCICIO;
        $this->encabLin3 = 'TESORERÍA';
        $this->encabLin4 = 'LISTADOS DE LOTES';
        $this->encabLin6 = '';
        $this->encabLin5 = '';  
        $this->datos = consultaLote($lot_ejercicio, $lot_tipo, $fpa_id, $msc_id, $lot_id);        
        $this->encabLin7 = '';

        $this->contenido(); //llamando a la function contenido

    }//fin construct

    public function contenido(){
        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
        'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
        'encabLin7' => $this->encabLin7,'alineacionenc'=>'I');
        if($fpa_id==3  && $lot_tipo==9 ){
            $this->nombreColumnas = array("#", "FECHA PAGO","LOTE EGRESO","UR","CLC","TIPO","ORDEN","RESPONSABLE","BENEFICIARIO","CONCEPTO","IMPORTE","BANCO","CTA CARGO","CTA NOMBRE","LINEA CAPTURA","CVE_FIN","PROP PAGO");
            $incluirColumnas = array("contador","modo","tipo","lot_ejercicio","lote","pro_id","elo_nombre","lot_fechaPago","lot_importe");
        }
        else{
            $this->nombreColumnas = array("#", "FECHA PAGO","LOTE EGRESO","UR","CLC","TIPO","ORDEN","RESPONSABLE","BENEFICIARIO","CONCEPTO","IMPORTE","BANCO","CTA CARGO","CTA NOMBRE","CTA DESTINO","CVE_FIN","PROP PAGO");
            $incluirColumnas = array("contador","modo","tipo","lot_ejercicio","lote","pro_id","elo_nombre","lot_fechaPago","lot_importe");
        }
        $tipoColumnas = array("cC","cC","cC","cC","cC","cC","cC","cC","cC","cC","cC","cC","n","cC","n","cC","cC");
        // $anchoColumnas = array(10, 13, 45, 60, 40,18);

        $table = array();

        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
            // $table['tipoTabla']= "M";
        }
        
        $path_image     = dirname(__FILE__)."/../../../../img/Logo Institucional/114.jpg";
        $tablaImagen    = array("imagen"=>1);
        $tablaImagen[0] = array("ruta"=>$path_image,"nombre"=>"XXX","colImg"=>"F1","altura"=>100,"alingXimg"=>100);
        //llamado a generaExcel de generaPhpspreadsheet
        $this->generaExcel($this->datos,$encabezado,$table,array("nombre"=>$this->nombreArchivoReporte,"tablaimg"=>$tablaImagen));
    }//fin contenido

}//fin de la clase
$exp_ajustes = new ExcelReporteLotes();
?>

