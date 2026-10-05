<?php
    /**
     * Description of Exp_TesCuentasReceptorasCuentasValidar
     *
     * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
     * Collaborators: Adriana Santiago Morales <adriana.morales@finanzasoaxaca.gob.mx>
     * Created: 21-Oct-2020
     * Comment: -----
     * Objective: Generar archivo Excel de las cuentas a validar.
     * Regla de Negocio: -----
     * Permits: Esta pantalla no pregunta por permisos.
     * */                
session_start();
require_once dirname(__FILE__).'/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__).'/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__).'/../../../../ent/UnidadEjecutora.php';
// Funcion para obtener los datos desde la base de datos
require_once dirname(__FILE__).'/../../../../lib/tes/catalogo/consultaCuentasReceptorasCuentasValidar.php';

class Exp_TesCuentasReceptorasCuentasValidar extends generaPhpspreadsheet
{
    public $datos;
    public $nombreArchivoReporte;
    public $nombreColumnas;

    function __construct()
    {
        parent::__construct();                        
        $this->encabLin1 = 'SECRETARÍA DE FINANZAS DE OAXACA';
        $this->encabLin2 = 'TESORERÍA ' . APP_EJERCICIO;
        $this->encabLin3 = 'SECRETARÍA DE FINANZAS';
        $this->encabLin4 = 'REPORTE DE CUENTAS A VALIDAR';
        $this->encabLin6 = '';
        $this->encabLin5 = '';  
        $this->encabLin7 = '';
        $this->nombreArchivoReporte = 'Cuentas a validar';
        $this->datos = consultaCuentasReceptorasCuentasValidar();                                
        $this->contenido();
    }

    public function contenido()
    {
        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
        'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
        'encabLin7' => $this->encabLin7,'alineacionenc'=>'I');
        
        $this->nombreColumnas = array('FECHA APERTURA','CLABE','TIPO','ESTADO','NOMBRE DE LA CUENTA');
        $incluirColumnas = array('F_Apertura','CLABE','TIPO','E_Cuenta','N_Cuenta');
        $tipoColumnas = array('c', 'c', 'c','c','i');

        $table = array();
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }       
        $name = 114;        
        $path_image = dirname(__FILE__) . '/../../../../img/Logo Institucional/' . $name . '.jpg';
        $tablaImagen = array('imagen' => 1);
        $tablaImagen[0] = array('ruta' => $path_image, 'nombre' => 'XXX', 'colImg' => 'D1', 'altura' => 100, 'alingXimg' => 80);
        $this->generaExcel($this->datos, $encabezado, $table, array('nombre' => $this->nombreArchivoReporte, 'tablaimg' => $tablaImagen));
    }
}
$exp_ajustes =new Exp_TesCuentasReceptorasCuentasValidar();