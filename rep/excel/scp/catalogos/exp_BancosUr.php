<?php
session_start();
ini_set('memory_limit', '54000M');
ini_set('upload_max_filesize', '64M');
ini_set('max_execution_time', '600'); //300 seconds = 5 minutes
ini_set('post_max_size', '54000M');
set_time_limit(0);
error_reporting(0);

require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../../../lib/scp/catalogos/nivelBancosUr.php';
class Exp_BancosUr extends generaPhpspreadsheet
{
    public $RepUr;
    public $usr_id;
    public $textoTerceraFila;
   
    public $datos;
    public $nombreArchivoReporte;
    public $nombreColumnas;

    function __construct()
    {
        parent::__construct();
        $this->RepUr = (isset($_POST['inputUr']) && $_POST['inputUr'] != '') ? $_POST['inputUr'] : '';

        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->textoTerceraFila = isset($_SESSION[_S]['prm']['T']['6']['4']['1']) ? 'TES' : (isset($_SESSION[_S]['prm']['S']['6']['2']['1']) ? 'SCP' : (isset($_SESSION[_S]['prm']['C']['8']['1']['1']) ? 'SCG' : ''));

        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'PRESUPUESTO DE EGRESOS ' . APP_EJERCICIO;
        $this->encabLin3 = 'SECRETARÍA DE FINANZAS';
        $this->encabLin4 = '';
        $this->encabLin6 = '';
        $this->encabLin5 = 'CATÁLOGO DE CUENTAS BANCARIAS';
        $this->datos = nivelBancosUr($this->RepUr);
        $this->encabLin7 = '';
        $this->nombreArchivoReporte = 'CUENTAS BANCARIAS';

        $this->contenido();
    }
    public function contenido()
    {
        $encabezado = array(
            'encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7' => $this->encabLin7, 'alineacionenc' => 'I'
        );

        $this->nombreColumnas = array("UR", "CLABE INTERBANCARIA", "CUENTA BANCARIA", "BENEFICIARIO", "BANCO", "DESCRIPCIÓN DE LA CUENTA", "(A)SP", "(B)GO", "(F,G,H)INV.PUB.", "(I)MUN.", "(J)DEUD.PUB.", "(K)BURS.", "(L)TRANS.", "(M)P.T.(OTROS)", "ESTADO", "FECHA AUT.");
        $incluirColumnas = array('ur', 'clabe', 'cuenta', 'bur_beneficiario', 'iba_nombre', 'bur_alias', 'serv', 'gast', 'inv', 'mun', 'deud', 'bursa', 'transfer', 'otros', 'eba_nombre', 'FECHAAuto');
        $tipoColumnas = array("d", "d", "d", "d", "d", "d", "cI", "cI", "cI", "cI", "cI", "cI", "cI", "cI", "d", "d");
        $anchoColumnas = array(10, 20, 15, 50, 30, 50, 10, 10, 10, 10, 10, 10, 10, 10, 15, 15);

        $table = array();
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['ancho'][$val] = $anchoColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }
        $table['textoCompleto'] = FALSE;
        $name = 114;
        $path_image = dirname(__FILE__) . '/../../../../img/Logo Institucional/' . $name . '.jpg';
        $tablaImagen = array('imagen' => 1);
        $tablaImagen[0] = array('ruta' => $path_image, 'nombre' => 'XXX', 'colImg' => 'K1', 'altura' => 100, 'alingXimg' => 80);
        $this->generaExcel($this->datos, $encabezado, $table, array('nombre' => $this->nombreArchivoReporte, 'tablaimg' => $tablaImagen));
    }
}
$Exp_BancosUr = new Exp_BancosUr();
