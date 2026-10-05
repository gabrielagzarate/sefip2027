<?php
/**
 * Description of Exp_PendientesMin2025ClcEnSefip2025
 *
 * @author ?
 * Collaborators: Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Created: 18-02-2025
 * Comment: -----
 * Objective: Generar un archivo Excel de los pendiente de ministrar 2025 CLC.
 * Regla de Negocio: -----
 * Permits: Esta pantalla no pregunta por permisos de lectura ni de escritura.
 * */
session_start();

require_once dirname(__FILE__).'/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__).'/../../../../lib/AccesoDatos.php';
require_once(dirname(__FILE__).'/../../../../lib/tes/reportes/consultaTesPendientesMin2025ClcSefip2025.php');
    
class Exp_PendientesMin2025ClcEnSefip2025 extends generaPhpspreadsheet
{
    public $tipo;
    public $fechaMin;
    public $fechaMax;
    public $ejercicio;
    public $modo;
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
    
    function __construct()
    {
        parent::__construct();                
        
        $this->tipo = (isset($_POST['tipo']) && $_POST['tipo'] !='') ? $_POST['tipo'] : 1;
        $this->fechaMin = (isset($_POST['fechaMin']) && $_POST['fechaMin'] !='') ? $_POST['fechaMin'] : '';
        $this->fechaMax = (isset($_POST['fechaMax']) && $_POST['fechaMax'] !='') ? $_POST['fechaMax'] : '';
        $this->ejercicio = (isset($_POST['ejercicio']) && $_POST['ejercicio'] !='') ? $_POST['ejercicio'] : '';
        $this->modo = (isset($_POST['modo']) && $_POST['modo'] !='') ? $_POST['modo'] : '';
        
        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'TESORERÍA '.APP_EJERCICIO;
        $this->encabLin3 = 'SECRETARÍA DE FINANZAS';
        $this->encabLin4 = 'MINISTRADO 2025';
        $this->encabLin5 = 'CLC PENDIENTES DE CONCILIAR DEL '.$this->fechaMin.' AL '.$this->fechaMax;  
        $this->encabLin6 = 'CLCS EN ESTADO 16 QUE NO GENERARON LOTE EN LA TESORERÍA '.APP_EJERCICIO;
        
        $this->datos = consultaTesPendientesMin2025ClcSefip2025($this->tipo, $this->fechaMin, $this->fechaMax);
        
        $this->encabLin7 = '';
        $this->nombreArchivoReporte = 'MINISTRADO 2025 CLC SIN LOTE EN SEFIP '.APP_EJERCICIO;
                
        $this->contenido();
    }
    public function contenido(){
        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
        'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
        'encabLin7' => $this->encabLin7,'alineacionenc'=>'I');
        
        $this->nombreColumnas = array('UR' ,'CLC' ,'CERE/CERECO' ,'CE/CO' , 'IMPORTE','CONCEPTO','CUENTA CARGO','CUENTA ABONO','LOTE','FECHA LOTE','TIPO DE MINISTRACIÓN','TIPO DE DOCUMENTO DE PAGO','SPEI/CHEQUE','FECHA PAGO');
        $incluirColumnas = array('ur','clc','cereCo','cere','importe','concepto','cta_Cargo','cta_Abono','lote','lot_FechaGeneracion','tmi_id','tdf_id','spei','fechaPago');
        $tipoColumnas = array('cC','cC','cC','cC','n','cI','cC','cC','cC','cC','cC','cC','n','cC');
        $anchoColumnas = array(6,6,14,8,15,40,20,20,10,14,20,20,15,13);

        $table = array();
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['ancho'][$val] = $anchoColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }
        $name=114;        
        $path_image=dirname(__FILE__).'/../../../../img/Logo Institucional/'.$name.'.jpg';
        $tablaImagen=array('imagen'=>1);
        $tablaImagen[0]=array('ruta'=>$path_image,'nombre'=>'XXX','colImg'=>'H1','altura'=>100,'alingXimg'=>0);
        $this->generaExcel($this->datos,$encabezado,$table,array('nombre'=>$this->nombreArchivoReporte,'tablaimg'=>$tablaImagen));
    }
}
$exp =new Exp_PendientesMin2025ClcEnSefip2025();
?>