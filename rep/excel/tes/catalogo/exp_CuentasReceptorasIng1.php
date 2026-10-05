<?php
    /**
     * Description of Exp_CuentasReceptorasIng1
     *
     * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
     * Collaborators: 
     * Created: 18-Sep-2018
     * Comment: -----
     * Objective: Generar un archivo en Excel de la relacion de cuentas para configuracion.
     * Regla de Negocio: -----
     * Permits: Esta pantalla no pregunta por permisos de escritura.
     * */                
session_start();
require_once dirname(__FILE__).'/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__).'/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__).'/../../../../ent/UnidadEjecutora.php';
// Funcion para obtener los datos desde la base de datos
require_once dirname(__FILE__).'/../../../../lib/tes/catalogo/formatoDatosCuentasReceptorasIng1.php';

class Exp_CuentasReceptorasIng1 extends generaPhpspreadsheet
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
        $this->encabLin4 = 'RELACIÓN  DE CUENTAS  PARA CONFIGURACIÓN';
        $this->encabLin5 = '';
        $this->encabLin6 = '';
        
        $this->datos = formatoDatosCuentasReceptorasIng1();
        $this->encabLin7 = '';
        $this->nombreArchivoReporte = 'ING1';
                
        $this->contenido();
    }
    public function contenido()
    {
        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
        'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
        'encabLin7' => $this->encabLin7,'alineacionenc'=>'C');
        
        $this->nombreColumnas = array('      ' ,'      ' ,'      ', '      ', '      ','      ','      ');
        $incluirColumnas = array('iba_abreviatura', 'cbs_cuenta', 'mxn', 'egr_ing', 'sap_inversion', 'cbs_nombre', 'cta_cheques');
        $tipoColumnas = array('cI', 'cC', 'cC','cC','cC','cC', 'cC');
        $anchoColumnas = array(20, 15, 5, 15, 10, 50, 15);

        $table = array();
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['ancho'][$val] = $anchoColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }
        $this->generaExcel($this->datos, $encabezado, $table, array('nombre' => $this->nombreArchivoReporte));
    }
}
$exp_ajustes =new Exp_CuentasReceptorasIng1();
