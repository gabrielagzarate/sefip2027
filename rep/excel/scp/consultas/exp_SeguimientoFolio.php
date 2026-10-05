<?php
/**
 * Description of ControladorSeguimientoPrevisiones
 *
 * @author  Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 30-sep-2020
 * Comment: -----
 * Objective: Muestra en Excel los datos para visualizar el seguimiento de folio.
 * Regla de Negocio: -----
 **/
session_start();
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once '../../../../lib/scp/consultas/consultaSeguimientoFolio.php';
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../ent/Usuario.php';

class exp_SeguimientoFolio extends generaPhpspreadsheet
{
    public $nvj;
    public $perfil;
    public $CvePres;
    public $grupo;
    public $ur;
    public $encabLin4;
    public $encabLin5;
    public $encabLin6;
    public $encabLin7;
    public $datos;
    public $nombreColumnas;

    public function __construct()
    {
        parent::__construct();
        //variables POST
        $this->nvj = (isset($_POST['nvj']) && $_POST['nvj'] != '') ? $_POST['nvj'] : '';
        $this->perfil = (isset($_POST['perfil']) && $_POST['perfil'] != '') ? $_POST['perfil'] : '';
        $this->CvePres = (isset($_POST['cvePres']) && $_POST['cvePres'] != '') ? $_POST['cvePres'] : '';
        $this->grupo = (isset($_POST['grupo']) && $_POST['grupo'] != '') ? $_POST['grupo'] : '';
        $this->ur = (isset($_POST['ur']) && $_POST['ur'] != '') ? $_POST['ur'] : '';

        $this->encabLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
        $this->encabLin2 = "CONTROL PRESUPUESTAL " . APP_EJERCICIO . "";
        $this->encabLin3 = "SECRETARÍA DE FINANZAS";
        $this->encabLin4 = "SEGUIMIENTO DE FOLIO";
        $this->encabLin5 = "";
        $this->encabLin6 = "";
        $this->encabLin7 = "";

        $this->datos = array_reverse(consultaSeguimientoFolio($this->nvj, $this->perfil, $this->CvePres, $this->grupo, $this->ur)); //datos obtenido de la

        $this->contenido(); //llamdo a la function contenido

    } //fin __construct

    public function contenido()
    {
        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3, 'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6, 'encabLin7' => $this->encabLin7, 'alineacionenc' => 'I');

        $this->nombreColumnas = array('CLAVE PRESUPUESTAL', 'FOLIO', 'UNIDAD RESPONSABLE', 'ORIGEN', 'STATUS');
        $incluirColumnas = array("cvePresupuestal", "mod_id", "unidadR", "omo_nombre", "emo_nombre");
        $tipoColumnas = array("cL", "cC", "cL", "cL", "cL");

        $anchoColumnas = array(80, 20, 40, 50, 80);

        $table = array();

        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
            $table['ancho'][$val] = $anchoColumnas[$key];
        }

        $path_image = dirname(__FILE__) . "/../../../../img/".LOGO_ESCUDO;
        $tablaImagen = array("imagen" => 1);
        $tablaImagen[0] = array("ruta" => $path_image, "nombre" => "XXX", "colImg" => "B1", "altura" => 100, "alingXimg" => 126);
        //llamado a generaExcel de generaPhpspreadsheet
        $this->generaExcel($this->datos, $encabezado, $table, array("nombre" => $this->encabLin4, "tablaimg" => $tablaImagen));

    } // fin contenido

} // fin class

$exp_ajustes = new exp_SeguimientoFolio();
