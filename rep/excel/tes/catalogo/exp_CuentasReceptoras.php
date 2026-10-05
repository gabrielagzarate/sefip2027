<?php
    /**
     * Description of Exp_CuentasReceptoras
     *
     * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
     * Collaborators: 
     * Created: 19-Sep-2018
     * Comment: -----
     * Objective: Generar archivo Excel del catálogos de claves de financiamiento.
     * Regla de Negocio: -----
     * Permits: Esta pantalla no pregunta por permisos de escritura.
     * */                
session_start();
require_once dirname(__FILE__).'/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__).'/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__).'/../../../../ent/UnidadEjecutora.php';
// Funcion para obtener los datos desde la base de datos
require_once dirname(__FILE__).'/../../../../lib/tes/catalogo/formatoDatosCuentasReceptoras.php';

class Exp_CuentasReceptoras extends generaPhpspreadsheet
{
    public $datos;
    public $nombreArchivoReporte;
    public $nombreColumnas;

    function __construct()
    {
        parent::__construct();                
        
        $clabe = (isset($_POST['clabe'])) ? $_POST['clabe'] : '';
        $fechaAperturaBMin = (isset($_POST['fechaAperturaBMin'])) ? $_POST['fechaAperturaBMin'] : '';
        $fechaAperturaBMax = (isset($_POST['fechaAperturaBMax'])) ? $_POST['fechaAperturaBMax'] : '';
        $estadocuenta = (isset($_POST['estadocuenta'])) ? $_POST['estadocuenta'] : '';
        $prm = new StdClass();
        $prm->tipo = (isset($_POST['tipo']) ) ? $_POST['tipo'] : '';
        $prm->aliasCuenta = (isset($_POST['aliasCuenta']) ) ? $_POST['aliasCuenta'] : '';
        $prm->nombreCuenta = (isset($_POST['nombreCuenta']) ) ? $_POST['nombreCuenta'] : '';
        $prm->nombreBanco = (isset($_POST['nombreBanco']) ) ? $_POST['nombreBanco'] : '';

        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'TESORERÍA ' . APP_EJERCICIO;
        $this->encabLin3 = 'SECRETARÍA DE FINANZAS';
        $this->encabLin4 = '';
        $this->encabLin6 = '';
        
        $this->encabLin5 = 'CATÁLOGO DE CLAVES DE FINANCIAMIENTO';  
        $this->datos = formatoDatosCuentasReceptoras($clabe, $estadocuenta, $fechaAperturaBMin, $fechaAperturaBMax, $prm);
        $this->encabLin7 = '';
        $this->nombreArchivoReporte = 'CATÁLOGO DE CLAVES DE FINANCIAMIENTO';
                
        $this->contenido();
    }
    public function contenido()
    {
        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
        'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
        'encabLin7' => $this->encabLin7,'alineacionenc'=>'I');
        
        $this->nombreColumnas = array("CLASIFICACIÓN" ,"FUENTE" ,"SUBFUENTE" ,"ORIGEN" ,"SUBORIGEN" ,"REFERENCIA" ,"A" ,"CLASIFICACIÓN" ,"FUENTE" ,"SUBFUENTE" ,"ORIGEN" ,"SUBORIGEN" ,"REFERENCIA" ,"CLAVE" ,"BANCO" ,"CUENTA BANCARIA" ,"CLABE ESTANDARIZADA" ,"CUENTA CONTABLE DE INGRESOS" ,"PLAZA" ,"SUCURSAL");
        $incluirColumnas = array('cff_id','fte_id','sft_id','ori_id','sor_id','cfi_id','cfi_anio','cff_nombre','fte_nombre','sft_nombre','ori_nombre','sor_nombre','cfi_nombre','cfi_clave','abreviatura','CUENTA','CLABE','ARMONI','PLAZA','SUCURSAL');
        $tipoColumnas = array("cC", "cC", "cC","cC","cC","cC","cC","c", "c", "c","cI","c","c","cC","cC", "cC","cC","cC","cD","cD");
        $anchoColumnas = array(15, 8, 12, 8, 12, 12, 8, 20, 20, 20, 20, 20, 45, 15, 22, 20, 20, 20, 18, 18);

        $table = array();
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['ancho'][$val] = $anchoColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
            $table['tipoTabla'] = 'M';
        }
        $name = 114;        
        $path_image = dirname(__FILE__) . '/../../../../img/Logo Institucional/' . $name . '.jpg';
        $tablaImagen = array('imagen' => 1);
        $tablaImagen[0] = array(
            'ruta' => $path_image,
            'nombre' => 'XXX',
            'colImg' => 'I1',
            'altura' => 100,
            'alingXimg' => 100
        );
        $this->generaExcel(
            $this->datos,
            $encabezado,
            $table,
            array('nombre' => $this->nombreArchivoReporte, 'tablaimg' => $tablaImagen)
        );
    }
}
$exp_ajustes =new Exp_CuentasReceptoras();
