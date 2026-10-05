<?php
/**
 * Description of Exp_ClaveFinanciamientoClaveF
 *
 * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Collaborators:
 * Created: 09-Sep-2019
 * Comment: -----
 * Objective: Generar un archivo Excel de la lista de Clave Financiamiento.
 * Regla de Negocio: -----
 * Permits: Esta pantalla no pregunta por permisos de lectura ni de escritura.
 * */
session_start();
require_once dirname(__FILE__).'/../../../lib/generaPhpspreadsheet.php';
// Funcion para obtener los datos desde la base de datos
require_once dirname(__FILE__).'/../../../../lib/tes/catalogo/formatoDatosClaveFinanciamiento.php';
require_once dirname(__FILE__).'/../../../../lib/tes/catalogo/funcionesClaveFinanciamiento.php';

class Exp_ClaveFinanciamientoClaveF extends generaPhpspreadsheet
{
    public $primeraParteClave;
    public $segundaParteClave;
    public $tipoRecurso;
    public $datos;
    public $nombreArchivoReporte;
    public $nombreColumnas;
    public $presupuesto;
    public $tesoreria;

    function __construct()
    {
        parent::__construct();
        $this->presupuesto = (tienePermiso("T.6.3.99") || tienePermiso("S.6.4.99") || tienePermiso("C.2.11.99"));
        $this->tesoreria = (tienePermiso("T.6.3.100") || tienePermiso("S.6.4.100") || tienePermiso("C.2.11.100"));

        $this->primeraParteClave = ( isset( $_POST['primeraParteClave'] ) ? $_POST['primeraParteClave'] : '');
        $this->segundaParteClave = ( isset( $_POST['segundaParteClave'] ) ? $_POST['segundaParteClave'] : '');
        
        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = getModuloClaveFinanciamiento();
        $this->encabLin3 = 'SECRETARÍA DE FINANZAS';
        $this->encabLin4 = '';
        $this->encabLin6 = '';
        
        $this->encabLin5 = 'LISTA DE CLAVE FINANCIAMIENTO';
        $this->datos = formatoDatosClaveFinanciamiento(
            4, 
            $this->primeraParteClave, 
            $this->segundaParteClave, 
            $this->presupuesto, 
            $this->tesoreria
        );
        if (!empty($this->primeraParteClave)) {
            $this->encabLin6 = 'SUB-ORIGEN: ' . $this->primeraParteClave . $this->segundaParteClave;
        }
        $this->encabLin7 = '';
        $this->nombreArchivoReporte = 'LISTA DE CLAVE FINANCIAMIENTO';
                
        $this->contenido();
    }
    public function contenido()
    {
        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
        'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
        'encabLin7' => $this->encabLin7,'alineacionenc'=>'I');
        
        $this->nombreColumnas = array("CLASIFICACIÓN", "FUENTE", "SUBFUENTE", "ORIGEN", "SUBORIGEN", "REFERENCIA", "A", "CLASIFICACIÓN", "FUENTE", "SUBFUENTE", "ORIGEN", "SUBORIGEN", "REFERENCIA");
        $incluirColumnas = array('cff_id', 'fte_id', 'sft_id', 'ori_id', 'sor_id', 'cfi_id', 'cfi_anio', 'cff_nombre', 'fte_nombre', 'sft_nombre', 'ori_nombre', 'sor_nombre', 'cfi_nombre');
        $tipoColumnas = array("d", "d", "d", "d", "d", "d", "d", "cI", "cI", "cI", "cI", "cI", "cI");
        $anchoColumnas = array(5, 5, 5, 5, 5, 5, 5, 10, 10, 10, 10, 10, 60);

        if ($this->presupuesto) {
            array_push($this->nombreColumnas, 'POA', 'DIVISIÓN');
            array_push($incluirColumnas, 'ecf_poa', 'ecf_division');
            array_push($tipoColumnas, 'cI', 'cI');
            array_push($anchoColumnas, 10, 10);
        }
        if ($this->tesoreria) {
            array_push($this->nombreColumnas, 'PDP');
            array_push($incluirColumnas, 'pdp_id');
            array_push($tipoColumnas, 'cI');
            array_push($anchoColumnas, 10);
        }

        $table = array();
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['ancho'][$val] = $anchoColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];

        }
        $table['textoCompleto'] = false;
        $name = 114;
        $path_image = dirname(__FILE__) . '/../../../../img/Logo Institucional/' . $name . '.jpg';
        $tablaImagen = array('imagen' => 1);
        $tablaImagen[0]=array(
            'ruta' => $path_image,
            'nombre' => 'XXX',
            'colImg' => 'K1',
            'altura' => 100,
            'alingXimg' => 80
        );
        $this->generaExcel(
            $this->datos,
            $encabezado,
            $table,
            array('nombre' => $this->nombreArchivoReporte, 'tablaimg' => $tablaImagen)
        );
    }
}
$exp_ajustes = new Exp_ClaveFinanciamientoClaveF();
