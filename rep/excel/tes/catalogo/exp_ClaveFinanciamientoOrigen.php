<?php
/**
 * Description of Exp_ClaveFinanciamientoOrigen
 *
 * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Collaborators:
 * Created: 09-Sep-2019
 * Comment: -----
 * Objective: Generar un archivo Excel de la lista de Origen.
 * Regla de Negocio: -----
 * Permits: Esta pantalla no pregunta por permisos de lectura ni de escritura.
 * */
session_start();
require_once dirname(__FILE__).'/../../../lib/generaPhpspreadsheet.php';
// Funcion para obtener los datos desde la base de datos
require_once dirname(__FILE__).'/../../../../lib/tes/catalogo/consultaClaveFinanciamiento.php';
require_once dirname(__FILE__).'/../../../../lib/tes/catalogo/funcionesClaveFinanciamiento.php';

class Exp_ClaveFinanciamientoOrigen extends generaPhpspreadsheet
{
    public $primeraParteClave;
    public $segundaParteClave;
    public $datos;
    public $nombreArchivoReporte;
    public $nombreColumnas;

    function __construct()
    {
        parent::__construct();

        $this->primeraParteClave = (isset( $_POST['primeraParteClave'] ) ? $_POST['primeraParteClave']:'');
        $this->segundaParteClave = (isset( $_POST['segundaParteClave'] ) ? $_POST['segundaParteClave']:'');
        
        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = getModuloClaveFinanciamiento();
        $this->encabLin3 = 'SECRETARÍA DE FINANZAS';
        $this->encabLin4 = '';
        $this->encabLin6 = '';
        
        $this->encabLin5 = 'ORIGEN DEL RECURSO';
        $this->datos = consultaClaveFinanciamiento(2, $this->primeraParteClave, $this->segundaParteClave);
        if (!empty($this->primeraParteClave)) {
            $this->encabLin6 = 'SUB-FUENTE: ' . $this->datos[0]['subfuente'] . ' - ' . $this->datos[0]['sft_nombre'];
        }
        $this->encabLin7 = '';
        $this->nombreArchivoReporte = 'ORIGEN DEL RECURSO';
        
        $this->contenido();
    }
    public function contenido()
    {
        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
        'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
        'encabLin7' => $this->encabLin7,'alineacionenc'=>'I');
        
        $this->nombreColumnas = array('CLASIFICADOR FUENTE' ,'FUENTE' ,'SUB-FUENTE', 'ORIGEN', 'NOMBRE DEL ORIGEN');
        $incluirColumnas = array('cff_id', 'fte_id', 'sft_id', 'ori_id', 'ori_nombre');
        $tipoColumnas = array('cC', 'cC', 'cC', 'cC', 'cI');

        $table = array();
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }
        $name = 114;
        $pathImage = dirname(__FILE__) . '/../../../../img/Logo Institucional/' . $name . '.jpg';
        $tablaImagen = array('imagen' => 1);
        $tablaImagen[0] = array(
            'ruta' => $pathImage,
            'nombre' => 'XXX',
            'colImg' => 'E1',
            'altura' => 100,
            'alingXimg' => 50
        );
        $this->generaExcel(
            $this->datos,
            $encabezado,
            $table,
            array('nombre' => $this->nombreArchivoReporte,'tablaimg' => $tablaImagen)
        );
    }
}
$exp_ajustes = new Exp_ClaveFinanciamientoOrigen();
