<?php
/**
 * Description of Exp_Fondeo
 *
 * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Collaborators:
 * Created: 12-Dic-2019
 * Comment: -----
 * Objective: Generar un archivo Excel de los movimietos bancarios en estado autorizado y de tipo traspaso, inversion y fondeo.
 * Regla de Negocio: -----
 * Permits: Esta pantalla no pregunta por permisos de lectura ni de escritura.
 * */
session_start();

ini_set('memory_limit', '20M');
set_time_limit(0);

require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/tes/ministracion/formatoDatosFondeo.php';

class Exp_Fondeo extends generaPhpspreadsheet
{
    public $fecha;
    public $fechaMax;
    public $tipo;
    public $mbn_id;
    public $encabLin4;
    public $encabLin5;
    public $encabLin6;
    public $encabLin7;
    public $datos;
    public $nombreArchivoReporte;
    public $nombreColumnas;
    
    public function __construct()
    {
        parent::__construct();
        $this->fecha = (isset($_POST['vinFondeo']) && $_POST['vinFondeo'] != '') ? $_POST['vinFondeo'] : '';
        $this->fechaMax = (isset($_POST['fechaMax']) && $_POST['fechaMax'] != '') ? $_POST['fechaMax'] : '';
        $this->tipo = (isset($_POST['tipo']) && $_POST['tipo'] != '') ? $_POST['tipo'] : '';
        $this->mbn_id = (isset($_POST['mbn_id']) && $_POST['mbn_id'] != '') ? $_POST['mbn_id'] : '';

        $textEncabLin3Complementario = '';
        $fecha = explode("/", $this->fecha);
        $fecha = $fecha[2] . "-" . $fecha[1] . "-" . $fecha[0];
        $fechaMin = $this->fecha;
        $fechaMax = $this->fechaMax;
        if (!empty($fechaMin) && !empty($fechaMax)) {
            $textEncabLin3 = ($fechaMin == $fechaMax) ? ' DíA ' . $fechaMin : '' . $fechaMin . ' AL ' . $fechaMax;
            $textEncabLin3Complementario = 'LISTADO DE MOVIMIENTOS DEL ' . $textEncabLin3;
        } elseif (!empty($fechaMin) && empty($fechaMax)) {
            $textEncabLin3Complementario = 'LISTADO DE MOVIMIENTOS APARTIR DEL DíA ' . $fechaMin;
        } elseif (empty($fechaMin) && !empty($fechaMax)) {
            $textEncabLin3Complementario = 'LISTADO DE MOVIMIENTOS HASTA EL DíA ' . $fechaMax;
        }

        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'SECRETARÍA DE FINANZAS';
        $this->encabLin3 = 'LISTADO DE MOVIMIENTOS DEL ' . $this->fecha;
        $this->encabLin4 = '';
        $this->encabLin6 = '';

        $this->encabLin5 = '';
        $this->datos = formatoDatosFondeo($this->tipo, $this->fecha, $this->fechaMax, $this->mbn_id);
        $this->encabLin7 = '';
        $this->nombreArchivoReporte = 'Detalle';

        $this->contenido();
    }
    public function contenido()
    {
        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7' => $this->encabLin7, 'alineacionenc' => 'I');

        $this->nombreColumnas = array('#', 'BANCO', 'DE CUENTA', 'BANCO', 'PARA CUENTA', 'IMPORTE', 'CONCEPTO', 'FECHA');
        $incluirColumnas = array('contador', 'cargo', 'cbs_id_cargo', 'abono', 'cbs_id_abono', 'importe', 'mbn_observacion', 'mbn_fechaMovimiento');
        $tipoColumnas = array('cC', 'cC', 'cC', 'cC', 'cC', 'n', 'cC', 'cC');

        $table = array();
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }
        $name = 114;
        $path_image = dirname(__FILE__) . '/../../../../img/Logo Institucional/' . $name . '.jpg';
        $tablaImagen = array('imagen' => 1);
        $tablaImagen[0] = array('ruta' => $path_image, 'nombre' => 'XXX', 'colImg' => 'F1', 'altura' => 100, 'alingXimg' => 200);
        $this->generaExcel($this->datos, $encabezado, $table, array('nombre' => $this->nombreArchivoReporte, 'tablaimg' => $tablaImagen));
    }
}
$exp = new Exp_Fondeo();
