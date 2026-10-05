<?php
    /**
     * Description of Exp_CuentasReceptorasIng2
     *
     * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
     * Collaborators: 
     * Created: 19-Sep-2018
     * Comment: -----
     * Objective: Generar un archivo en Excel de la direccion de ingresos.
     * Regla de Negocio: -----
     * Permits: Esta pantalla no pregunta por permisos de escritura.
     * */                
session_start();
require_once dirname(__FILE__).'/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__).'/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__).'/../../../../ent/UnidadEjecutora.php';
// Funcion para obtener los datos desde la base de datos
require_once dirname(__FILE__).'/../../../../lib/tes/catalogo/consultaCuentasReceptorasIng2.php';

class Exp_CuentasReceptorasIng2 extends generaPhpspreadsheet
{
    public $datos;
    public $nombreArchivoReporte;
    public $nombreColumnas;

    function __construct()
    {
        parent::__construct();

        $this->encabLin1 = 'SECRETARÍA DE FINANZAS';
        $this->encabLin2 = 'TESORERÍA ' . APP_EJERCICIO;
        $this->encabLin3 = 'DIRECCIÓN DE PRESUPUESTO';
        $this->encabLin4 = '';
        $this->encabLin6 = '';
        
        $this->encabLin5 = 'ING2';
        $this->datos = consultaCuentasReceptorasIng2();
        $this->encabLin7 = '';
        $this->nombreArchivoReporte = 'ING2';
                
        $this->contenido();
    }   
    public function contenido()
    {
        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
        'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
        'encabLin7' => $this->encabLin7, 'alineacionenc' => 'C');
        
        $this->nombreColumnas = array("BANCO", "TIPO", "CTA CARGO","CUENTA", "CONCEPTO");
        $incluirColumnas = array("iba_abreviatura", "ter_nombre","sapImproductivas", "cbs_cuenta", "cbs_nombre");
        $tipoColumnas = array("c", "c", "c", "c", "c");

        $table = array();
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];

        }
        $this->generaExcel($this->datos, $encabezado, $table, array("nombre"=>$this->nombreArchivoReporte));
    }
}
$exp_ajustes =new Exp_CuentasReceptorasIng2();
