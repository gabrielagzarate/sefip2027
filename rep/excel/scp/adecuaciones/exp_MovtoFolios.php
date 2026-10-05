<?php

/**
 * Description of ControladorRegistroModificaciones
 *
 * @author  Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 29-Nov-2018
 * Comment: -----
 * Objective: Muestra en excel el detallado de los movimientos del folio.
 * Regla de Negocio: -----
 **/
session_start();
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once '../../../../lib/scp/nivelFolioMovto.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../ent/Usuario.php';
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../ent/scp/OrigenOperacion.php';
require_once '../../../../ent/scp/TipoModificacion.php';

class exp_MovtoFolios extends generaPhpspreadsheet
{
    public $nvj;
    public $ue_id;
    public $usr_id;
    public $adm_gpo;
    public $adm_ur;
    private $omo_id;
    private $mod_gpo_id;
    private $mod_ur_id;
    private $mod_id;
    private $enc_oop;
    private $envia;
    private $ur_id;
    private $datos;
    private $ur;
    private $nombreColumnas;
    private $nombre;

    public $encabLin4;
    public $encabLin5;
    public $encabLin6;
    public $encabLin7;
    public $encabLin8;
    public $encabLin9;
    public $encabLin10;

    public function __construct()
    {

        parent::__construct();

        $this->omo_id = (isset($_POST['omo_id']) ? $_POST['omo_id'] : '');
        $this->mod_gpo_id = (isset($_POST['mod_gpo_id']) ? $_POST['mod_gpo_id'] : '');
        $this->mod_ur_id = (isset($_POST['mod_ur_id']) ? $_POST['mod_ur_id'] : '');
        $this->mod_id = (isset($_POST['mod_id']) ? $_POST['mod_id'] : '');
        $this->enc_oop = (isset($_POST['enc_oop']) ? $_POST['enc_oop'] : '');
        $this->envia = (isset($_POST['envia']) ? $_POST['envia'] : '');
        $this->ur_id = (isset($_POST['mod_ur_id']) ? $_POST['mod_ur_id'] : '');

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($this->ur_id);
        $this->ur->select();
        $this->usr_id = $_SESSION[_S]['usr_id'];

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
        } //fin switch

        $nombre = (trim($this->nombre . ': ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre()));

        $enc_oop = new OrigenOperacion(['enc_oop' => $this->enc_oop]);
        $tipoMod = new TipoModificacion(['tmd_id' => $enc_oop->getTmd_id()]);

        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'CONTROL PRESUPUESTAL ' . APP_EJERCICIO;
        $this->encabLin3 = $nombre;
        $this->encabLin4 = 'ADECUACIÓN PRESUPUESTAL - MOVIMIENTOS DEL FOLIO';
        $this->encabLin5 = '';
        $this->encabLin6 = '';
        $this->encabLin7 = '';
        $this->encabLin8 = '';
        $this->encabLin9 = 'NÚMERO DE FOLIO: ' . $_POST['mod_id'];
        $this->encabLin10 = 'TIPO DE ADECUACIÓN: ' . $tipoMod->getNombre();

        $this->datos = array_reverse(nivelFolioMovto($this->omo_id, $this->mod_gpo_id, $this->mod_ur_id, $this->mod_id, $this->envia, 'EXCEL')); //datos obtenido de la consulta
        $this->contenido(); //llamdo a la function contenido
    }

    public function contenido()
    {
        $encabezado = ['encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3, 'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6, 'encabLin7' => $this->encabLin7, 'encabLin8' => $this->encabLin8, 'encabLin9' => $this->encabLin9, 'encabLin10' => $this->encabLin10, 'alineacionenc' => 'I'];

        $this->nombreColumnas = ['GPO', 'UR', 'UE', 'PRG', 'SPR', 'PY', 'OA', 'PG', 'PE', 'CFF', 'FTE', 'SFT', 'ORI', 'SUBORI', 'CFI', 'CFI_AÑO', 'TCP', 'IMPORTE', 'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
        $incluirColumnas = ['gpo_id', 'ur_id', 'ue_id', 'prg_id', 'spr_id', 'py_id', 'oa_id', 'pg_id', 'pe_id', 'cff_id', 'fte_id', 'sft_id', 'ori_id', 'sor_id', 'cfi_id', 'cfi_anio', 'tcp_id', 'dmo_importe', 'dmo_ene', 'dmo_feb', 'dmo_mar', 'dmo_abr', 'dmo_may', 'dmo_jun', 'dmo_jul', 'dmo_ago', 'dmo_sep', 'dmo_oct', 'dmo_nov', 'dmo_dic'];
        $tipoColumnas = ['cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n'];

        $anchoColumnas = [5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 10, 5, 10, 5, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20];

        $table = [];

        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
            $table['ancho'][$val] = $anchoColumnas[$key];
        }

        $path_image = dirname(__FILE__) . '/../../../../img/' . LOGO_ESCUDO;
        $tablaImagen = ['imagen' => 1];
        $tablaImagen[0] = ['ruta' => $path_image, 'nombre' => 'XXX', 'colImg' => 'R1', 'altura' => 100, 'alingXimg' => 126];
        //llamado a generaExcel de generaPhpspreadsheet
        $this->generaExcel($this->datos, $encabezado, $table, ['nombre' => $this->encabLin2, 'tablaimg' => $tablaImagen]);
    }
}
$exp_ajustes = new exp_MovtoFolios();
