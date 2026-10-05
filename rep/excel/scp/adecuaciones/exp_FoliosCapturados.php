<?php

/**
 * Description of ControladorRegistroModificaciones
 *
 * @author  Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 29-Nov-2018
 * Migration: 01-Oct-2019
 * Comment: -----
 * Objective: Muestra en excel los folios que se dieron de alta.
 * Regla de Negocio: -----
 **/
session_start();
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/scp/nivelFolio.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../ent/Usuario.php';

class exp_FoliosCapturados extends generaPhpspreadsheet
{
    private $usr_id;
    private $ue_id;
    private $gpo_id;
    private $ur_id;
    private $ecf_id;
    private $envia;
    private $ur;
    private $ue;
    private $usuario;
    private $nombre;
    public $encabLin4;
    public $encabLin5;
    public $encabLin6;
    public $encabLin7;
    public $datos;
    private $nombreColumnas;

    public function __construct()
    {
        parent::__construct();

        //variables de sesion
        $this->usr_id = (isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : '');
        $this->ue_id = (isset($_SESSION[_S]['ue_id']) ? $_SESSION[_S]['ue_id'] : '');
        $this->gpo_id = (isset($_POST['mod_gpo_id']) ? $_POST['mod_gpo_id'] : '');
        $this->ur_id = (isset($_POST['mod_ur_id']) ? $_POST['mod_ur_id'] : '');
        $this->ecf_id = (isset($_POST['ecf_id']) ? $_POST['ecf_id'] : '');
        $this->envia = (isset($_POST['envia']) ? $_POST['envia'] : '');

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($this->ur_id);
        $this->ur->select();

        if ($this->usr_id < 2) {
            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $this->ue->setUr_id($_SESSION[_S]['clasAdm-ur']);

            //Obtener Ue
            if (empty($this->ue_id)) {
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $sql = 'SELECT ue_id FROM cat_Usuario WHERE usr_id=' . $this->usr_id;
                $conexion->setSql($sql);
                $conexion->consultar();
                $conexion->desconectar();
                $dato = $conexion->extraer();
                $ue = $dato['ue_id'];
            } else {
                $ue = $this->ue_id;
            }

            $this->ue->setUe_id($ue);
            $this->ue->select();
        }

        if ($this->usr_id != '' && $this->usr_id != 0) {
            $this->usuario = new Usuario();
            $this->usuario->setUsr_id($this->usr_id);
            $this->usuario->select();
        }

        switch ($this->envia) {
            case 'ur':
                $this->nombre = 'UR';
                break;
            case 'planeacion':
                $this->nombre = 'PL';
                break;
            case 'presupuesto':
                $this->nombre = 'PR';
                break;
            case 'drh':
                $this->nombre = 'DRH';
                break;
            case 'sefin':
                $this->nombre = 'SEFIN';
                break;
            default:
                break;
        }

        $nombre = (trim($this->nombre . ': ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre()));

        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'CONTROL PRESUPUESTAL ' . APP_EJERCICIO;
        $this->encabLin3 = $nombre;
        $this->encabLin4 = 'ADECUACIÓN PRESUPUESTAL - LISTADO DE FOLIOS';
        $this->encabLin5 = '';
        $this->encabLin6 = '';
        $this->encabLin7 = '';
        $this->datos = array_reverse(nivelFolio($this->gpo_id, $this->ur_id, $this->ecf_id, $this->envia)); //datos obtenido de la consulta
        $this->contenido(); //llamdo a la function contenido
    } //FIN __construct

    public function contenido()
    {
        $encabezado = ['encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3, 'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7'            => $this->encabLin7,
            'alineacionenc'        => 'I'];

        $this->nombreColumnas = ['FOLIO', 'MODALIDAD', 'TIPO ADECUACIÓN', 'JUSTIFICACIÓN', 'ESTADO', 'FECHA CAPTURA'];
        $incluirColumnas = ['mod_id', 'mmd_id', 'tmd_nombre', 'mod_concepto', 'emo_nombre', 'mod_fechaFolio'];
        $tipoColumnas = ['cC', 'cC', 'cI', 'cI', 'cC', 'cI'];
        $anchoColumnas = [10, 13, 45, 60, 40, 18];

        $table = [];

        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
            $table['ancho'][$val] = $anchoColumnas[$key];
            $table['tipoTabla'] = 'M';
        }

        $path_image = dirname(__FILE__) . '/../../../../img/' . LOGO_ESCUDO;

        $tablaImagen = ['imagen' => 1];
        $tablaImagen[0] = ['ruta' => $path_image, 'nombre' => 'XXX', 'colImg' => 'D1', 'altura' => 100, 'alingXimg' => 126];
        //llamado a generaExcel de generaPhpspreadsheet
        $this->generaExcel($this->datos, $encabezado, $table, ['nombre' => $this->encabLin3, 'tablaimg' => $tablaImagen]);
    } //FIN contenido

} // fin clase exp_FoliosCapturados

$exp_ajustes = new exp_FoliosCapturados();
