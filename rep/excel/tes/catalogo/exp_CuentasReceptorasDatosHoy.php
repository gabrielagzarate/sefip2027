<?php
    /**
     * Description of Exp_CuentasReceptorasDatosHoy
     *
     * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
     * Collaborators: 
     * Created: 19-Sep-2018
     * Comment: -----
     * Objective: Generar un archivo en Excel de la relacion de cuenta para dar de alta para conciliar.
     * Regla de Negocio: -----
     * Permits: Esta pantalla no pregunta por permisos de escritura.
     * */                
session_start();
require_once dirname(__FILE__).'/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__).'/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__).'/../../../../ent/UnidadEjecutora.php';
// Funcion para obtener los datos desde la base de datos
require_once dirname(__FILE__).'/../../../../lib/tes/catalogo/formatoDatosCuentasReceptorasDatosHoy.php';

class Exp_CuentasReceptorasDatosHoy extends generaPhpspreadsheet
{
    public $datos;
    public $nombreColumnas;
    public $nombreArchivoReporte;
    function __construct()
    {
        parent::__construct();                
        
        $this->encabLin1 = 'SECRETARÍA DE FINANZAS';
        $this->encabLin2 = 'TESORERÍA ' . APP_EJERCICIO;
        $this->encabLin3 = 'SECRETARÍA DE FINANZAS';
        $this->encabLin4 = 'DIRECCIÓN DE PRESUPUESTO';
        $this->encabLin6 = 'RELACIÓN DE CUENTAS PARA DAR DE ALTA PARA CONCILIAR';
        
        $this->encabLin5 = 'DATOS HOY';
        $this->datos = formatoDatosCuentasReceptorasDatosHoy();
        $this->encabLin7 = '';
        $this->nombreArchivoReporte = 'DATOS HOY';
                
        $this->contenido();
    }
    public function contenido()
    {
        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
        'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
        'encabLin7' => $this->encabLin7,'alineacionenc'=>'C');
        
        $this->nombreColumnas = array('CUENTA BANCARIA', 'CLAVE DEL BANCO', 'NOMBRE DE LA CUENTA');
        $incluirColumnas = array('cff_id','fte_id','sft_id');
        $tipoColumnas = array('c', 'c', 'c');
        // $anchoColumnas = array(15,8,12);

        $table = array();
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];

        }
        $this->generaExcel($this->datos, $encabezado, $table, array('nombre'=>$this->nombreArchivoReporte));
    }
}
$exp_ajustes =new Exp_CuentasReceptorasDatosHoy();
