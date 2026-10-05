<?php
    /**
     * Description of Exp_CuentasReceptorasTabla
     *
     * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
     * Collaborators: 
     * Created: 19-Sep-2018
     * Comment: -----
     * Objective: Generar archivo Excel de las cuentas bancarias.
     * Regla de Negocio: -----
     * Permits: Esta pantalla no pregunta por permisos de escritura.
     * */                
session_start();
require_once dirname(__FILE__).'/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__).'/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__).'/../../../../ent/UnidadEjecutora.php';
// Funcion para obtener los datos desde la base de datos
require_once dirname(__FILE__).'/../../../../lib/tes/catalogo/consultaCuentasReceptorasTabla.php';

class Exp_CuentasReceptorasTabla extends generaPhpspreadsheet
{
    public $datos;
    public $nombreColumnas;
    function __construct()
    {
        parent::__construct();                        

        $clabe = (isset($_POST['clabe'])) ? $_POST['clabe'] : '';
        $fechaAperturaB = (isset($_POST['fechaAperturaB'])) ? $_POST['fechaAperturaB'] : '';
        $estadocuenta = (isset($_POST['estadocuenta'])) ? $_POST['estadocuenta'] : '';
        $prm  =  new StdClass();
        $prm->tipo = (isset($_POST['tipo'])) ? $_POST['tipo'] : '';
        $prm->aliasCuenta = (isset($_POST['aliasCuenta'])) ? $_POST['aliasCuenta'] : '';
        $prm->nombreCuenta = (isset($_POST['nombreCuenta'])) ? $_POST['nombreCuenta'] : '';
        $prm->nombreBanco = (isset($_POST['nombreBanco'])) ? $_POST['nombreBanco'] : '';

        $this->encabLin1 = 'SECRETARÍA DE FINANZAS DE OAXACA';
        $this->encabLin2 = 'TESORERÍA ' . APP_EJERCICIO;
        $this->encabLin3 = 'SECRETARÍA DE FINANZAS';
        $this->encabLin4 = 'REPORTE DE CUENTAS BANCARIAS';
        $this->encabLin6 = '';
        
        $this->encabLin5 = '';  
        $this->datos = consultaCuentasReceptorasTabla($clabe, $estadocuenta, $fechaAperturaB, $prm);                                
        $this->encabLin7 = '';
                
        $this->contenido();
    }   
    public function contenido()
    {
        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
        'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
        'encabLin7' => $this->encabLin7,'alineacionenc'=>'I');
        
        $this->nombreColumnas = array('Banco', 'Nombre', 'Contrato', 'Cuenta Cheques', 'Cuenta Inversión', 'Clabe', 'Cargo Cheque', 'Armonizada Cheque', 'Cargo Inversión', 'Armonizada', 'Tipo', 'Fecha Apertura', 'Fecha Cancelación', 'Cuenta interes', 'Banco Propio');
        $incluirColumnas = array('iba_abreviatura', 'cbs_nombre', 'cbs_contrato', 'cta_cheque', 'cta_inversion', 'clabe', 'cargo_cheque', 'armoni_cheque', 'cargo_inversion', 'armoni_inversion', 'ter_nombre', 'cbs_fechaApertura', 'cbs_fechaCancelacion', 'cta_interes', 'BAN_PROP');
        $tipoColumnas = array('c',  'c',  'c', 'c', 'c', 'c',  'c',  'c', 'c', 'c', 'c',  'c',  'c', 'c', 'c');

        $table = array();
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];

        }
        $name = 114;        
        $path_image = dirname(__FILE__).'/../../../../img/Logo Institucional/'.$name.'.jpg';
        $tablaImagen = array('imagen' => 1);
        $tablaImagen[0] = array(
            'ruta' => $path_image,
            'nombre' => 'XXX',
            'colImg' => 'F1',
            'altura' => 100,
            'alingXimg' => 10
        );
        $this->generaExcel(
            $this->datos,
            $encabezado,
            $table,
            array('nombre' => 'TABLA','tablaimg' => $tablaImagen)
        );
    }
}
$exp_ajustes =new Exp_CuentasReceptorasTabla();
