<?php
/**
 * Description of Exp_CuentaContable
 *
 * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Collaborators:
 * Created: 05-03-2019
 * Comment: -----
 * Objective: Generar un archivo Excel de reportes de cuentas del mes X al MES Y.
 * Regla de Negocio: -----
 * Permits: -----
 * */
session_start();

require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../../../ent/enc_ClaveFinanciamiento.php';
require_once dirname(__FILE__) . '/../../../../ent/enc_Programatico.php';
require_once dirname(__FILE__) . '/../../../../ent/scg/cuentaContable/Genero.php';
require_once dirname(__FILE__) . '/../../../../lib/redondear.php';
require_once dirname(__FILE__) . '/../../../../lib/MesNombre.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/reporte/cuenta.php';

class Exp_CuentaContable extends generaPhpspreadsheet
{
    public $gpo_id;
    public $ur_id;
    public $ue_id;
    public $comboMesDesde;
    public $comboMesHasta;
    public $s23_nvj;
    public $ue;
    public $tipoReporte;
    public $checkAgrupadoUr;
    public $inputGrupos;
    public $inputExcluir;
    public $estatal;
    public $federal;
    public $cveFin;
    public $programatico;
    public $ger_id;
    public $nombreEspecial;
    public $ejercicio;
    public $niveles;
    public $reporte;
    public $datos;
    public string | null $encabLin1;
    public string | null $encabLin2;
    public string | null $encabLin3;
    public $encabLin4;
    public $encabLin5;
    public $encabLin6;
    public $encabLin7;
    public $nombreArchivoReporte;

    public function __construct()
    {
        parent::__construct();

        $this->gpo_id = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : false;
        $this->ur_id = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : false;
        $this->ue_id = isset($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : false;
        $this->comboMesDesde = ((isset($_POST['comboMesDesde']) && trim($_POST['comboMesDesde']) != '') ? $_POST['comboMesDesde'] : -1);
        $this->comboMesHasta = ((isset($_POST['comboMesHasta']) && trim($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta'] : -1);
        $this->s23_nvj = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : false;

        $this->ue = isset($_POST['ue']) ? $_POST['ue'] : false;
        $this->tipoReporte = isset($_POST['tipoReporte']) ? $_POST['tipoReporte'] : false;
        $this->checkAgrupadoUr = isset($_POST['checkAgrupadoUr']) ? $_POST['checkAgrupadoUr'] : '';
        $this->inputGrupos = isset($_POST['inputGrupos']) ? $_POST['inputGrupos'] : '';
        $this->inputExcluir = isset($_POST['inputExcluir']) ? $_POST['inputExcluir'] : '';
        $this->estatal = (isset($_POST['checkEstatal']) && $_POST['checkEstatal'] == 1) ? true : false;
        $this->federal = (isset($_POST['checkFederal']) && $_POST['checkFederal'] == 1) ? true : false;
        $this->cveFin = isset($_POST['cveFin']) ? $_POST['cveFin'] : '';
        $this->programatico = isset($_POST['programatico']) ? $_POST['programatico'] : '';
        $this->ger_id = (isset($_POST['ger_id'])) ? $_POST['ger_id'] : 0;
        $this->nombreEspecial = (isset($_POST['nombreEspecial'])) ? $_POST['nombreEspecial'] : '';
        $this->ejercicio = isset($_SESSION[_S]['ejercicio']) ? $_SESSION[_S]['ejercicio'] : false;

        $ur = new UnidadResponsable();
        $ur->setGpo_id($this->gpo_id);
        $ur->setUr_id($this->ur_id);
        $ur->select();

        if ($this->ue_id) {
            $ue = new UnidadEjecutora();
            $ue->setGpo_id($this->gpo_id);
            $ue->setUr_id($this->ur_id);
            $ue->setUe_id($this->ue_id);
            $ue->select();
        } elseif (trim($this->ue) != '') {
            $ue = new UnidadEjecutora();
            $ue->setGpo_id($this->gpo_id);
            $ue->setUr_id($this->ur_id);
            $ue->setUe_id(trim($this->ue));
            $ue->select();
        } else {
            $ue = false;
        }

        $ue_id = ($ue ? $ue->getUe_id() : false);

        $periodo['mesDesde'] = $this->comboMesDesde;
        $periodo['mesHasta'] = $this->comboMesHasta;

        $this->niveles = array();
        $this->niveles[1] = ((isset($_POST['checkUR']) && trim($_POST['checkUR']) != '') ? $_POST['checkUR'] : 1);
        $this->niveles[2] = ($ue ? $ue->getUe_id() : -1);
        $this->niveles[3] = ((isset($_POST['checkEP']) && trim($_POST['checkEP']) != '') ? $_POST['checkEP'] : -1);
        $this->niveles[4] = ((isset($_POST['checkTG']) && trim($_POST['checkTG']) != '') ? $_POST['checkTG'] : -1);
        $this->niveles[5] = ((isset($_POST['checkIP']) && trim($_POST['checkIP']) != '') ? $_POST['checkIP'] : -1);
        $this->niveles[6] = ((isset($_POST['checkPG']) && trim($_POST['checkPG']) != '') ? $_POST['checkPG'] : -1);
        $this->niveles[7] = ((isset($_POST['checkPE']) && trim($_POST['checkPE']) != '') ? $_POST['checkPE'] : -1);
        $this->niveles[8] = ((isset($_POST['checkTR']) && trim($_POST['checkTR']) != '') ? $_POST['checkTR'] : -1);
        $this->niveles[9] = ((isset($_POST['checkCF']) && trim($_POST['checkCF']) != '') ? $_POST['checkCF'] : -1);

        $this->niveles[0] = ($this->niveles[9] > 0) ? 9 : (
            ($this->niveles[8] > 0) ? 8 : (
                ($this->niveles[7] > 0) ? 7 : (
                    ($this->niveles[6] > 0) ? 6 : (
                        ($this->niveles[5] > 0) ? 5 : (
                            ($this->niveles[4] > 0) ? 4 : (
                                ($this->niveles[3] > 0) ? 3 : (
                                    ($this->niveles[2] > 0) ? 2 : 1
                                )
                            )
                        )
                    )
                )
            )
        );
        $this->reporte = array();
        $this->reporte['tipo'] = (!empty($this->tipoReporte) ? $this->tipoReporte : 1);
        if ($this->reporte['tipo'] != 1) {
            $this->ue_id = null;
            $ue_id = null;
        }
        $this->reporte['agrupador'] = $this->checkAgrupadoUr;
        $this->reporte['grupos'] = $this->inputGrupos;
        $this->reporte['excluir'] = $this->inputExcluir;

        if ($this->niveles[9] > 0) {
            $claveFinanciamiento['ecf_id'] = $this->cveFin; // busqueda por clave de financiamiento
        } else {
            $claveFinanciamiento['ecf_id'] = '';
        }
        if ($this->niveles[8] > 0) {
            $claveFinanciamiento['ter_id'] = ($claveFinanciamiento['ecf_id'] != '') ? '' : (($this->federal && $this->estatal) ? '' : ($this->federal ? 'B' : ($this->estatal ? 'A' : '')));
        } else {
            $claveFinanciamiento['ter_id'] = '';
        }
        if ($this->niveles[3] > 0 && $this->reporte['tipo'] == 1) {
            $programatico['epg_id'] = $this->programatico;
        } else {
            $programatico['epg_id'] = '';
        }

        $ger_id = $this->ger_id;

        $this->datos = cuenta($this->gpo_id, $this->ur_id, $ue_id, '', $periodo, $programatico, $claveFinanciamiento, $reporte, '', '', $niveles, array("ger_id" => $ger_id));

        $this->encabLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
        $this->encabLin2 = "CONTABILIDAD GUBERNAMENTAL ".APP_EJERCICIO;

        if ($this->s23_nvj > 2) {
            switch ($this->reporte['tipo']) {
                case 2: // Todas las URs de la carga de trabajo
                    $this->encabLin3 = "DIRECCIÓN DE CONTABILIDAD GUBERNAMENTAL";
                    $this->encabLin6 = "SECTOR ";
                    $ue = false;
                    break;
                case 3: // Determinar qué incluir y qué excluir
                    $this->encabLin3 = "DIRECCIÓN DE CONTABILIDAD GUBERNAMENTAL";
                    if (!empty($this->nombreEspecial)) {
                        $this->encabLin6 = trim($this->nombreEspecial);
                    } else {
                        $this->encabLin6 = '';
                    }
                    $ue = false;
                    break;
                default:
                    $this->encabLin3 = "DIRECCIÓN DE CONTABILIDAD GUBERNAMENTAL: " . $ur->getClave() . " - " . $ur->getNombre();
                    $this->encabLin6 = '';
                    break;
            }
        } else {
            $this->encabLin3 = "UR: " . $ur->getClave() . " - " . $ur->getNombre();
            $this->encabLin6 = '';
        }

        if ($ue) {
            $this->encabLin4 = "UE: " . $ue->getUe_id() . " - " . $ue->getNombre();
        } else {
            $this->encabLin4 = '';
        }

        if ($ger_id != 0) {
            $ger = new Genero();
            $ger->setGer_id($ger_id);
            $ger->select();
            $cuenta = " " . $ger->getGer_id() . " - " . $ger->getNombre();
        } else {
            $cuenta = '';
        }

        if ($periodo['mesDesde'] == $periodo['mesHasta']) {
            if ($periodo['mesHasta'] == 0) {
                $encabLin5 = "REPORTE DE LAS CUENTAS $cuenta DEL MES DE APERTURA DE " . substr($this->ejercicio, 0, 4) . ".";
            } else {
                $encabLin5 = "REPORTE DE LAS CUENTAS $cuenta DEL MES DE " . MesNombre($periodo['mesDesde']) . " DE " . substr($this->ejercicio, 0, 4) . ".";
            }

        } else {
            $encabLin5 = "REPORTE DE LAS CUENTAS $cuenta DEL PERIODO " . MesNombre($periodo['mesDesde']) . "-" . MesNombre($periodo['mesHasta']) . " DE " . substr($this->ejercicio, 0, 4) . ".";
        }

        if ($programatico['epg_id'] != '') {
            $encProg = new enc_Programatico();
            $encProg->setEpg_id($programatico['epg_id']);
            $encProg->select();

            $encabLin6 = "Obra/Actividad: " . $encProg->getClave() . " - " . $encProg->getNombre();

        }

        if ($claveFinanciamiento['ecf_id'] != '') {
            $cvefin = new enc_ClaveFinanciamiento();
            $cvefin->setEcf_id($claveFinanciamiento['ecf_id']);
            $cvefin->select();

            $encabLin7 = "Clave de Financiamiento: " . $cvefin->getClave() . " - " . $cvefin->getNombre();

        } elseif ($this->federal && $this->estatal) {
            $encabLin7 = '';
        } elseif ($this->federal) {
            $encabLin7 = "Recurso Federal";
        } elseif ($this->estatal) {
            $encabLin7 = "Recurso Estatal";
        } else {
            $encabLin7 = '';
        }

        $this->nombreArchivoReporte = 'CUENTAS';

        $this->contenido();
    }

    public function contenido()
    {
        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7' => $this->encabLin7, 'alineacionenc' => 'I');

        $nombreColumnas = array();
        $incluirColumnas = array();
        $tipoColumnas = array();
        $anchoColumnas = array();
        if ($this->reporte['tipo'] != 1) {
            if ($this->niveles[1] > 0) {
                array_push($anchoColumnas, 5);
                array_push($nombreColumnas, "UR");
                array_push($incluirColumnas, "ur_clave");
                array_push($tipoColumnas, "c");
            }
        }
        if ($this->niveles[2] > 0) {
            array_push($anchoColumnas, 5);
            array_push($nombreColumnas, "UE");
            array_push($incluirColumnas, "ue_id");
            array_push($tipoColumnas, "c");
        }
        if ($this->niveles[3] > 0) {
            array_push($anchoColumnas, 10);
            array_push($anchoColumnas, 30);
            array_push($nombreColumnas, "OBRA/ACTIVIDAD");
            array_push($nombreColumnas, "NOMBRE");
            array_push($incluirColumnas, "oa_clave");
            array_push($incluirColumnas, "oa_nombre");
            array_push($tipoColumnas, "c");
            array_push($tipoColumnas, "c");
        }
        if ($this->niveles[4] > 0) {
            array_push($anchoColumnas, 5);
            array_push($anchoColumnas, 30);
            array_push($nombreColumnas, "TIPO DE GASTO");
            array_push($nombreColumnas, "NOMBRE");
            array_push($incluirColumnas, "tg_id");
            array_push($incluirColumnas, "tg_nombre");
            array_push($tipoColumnas, "c");
            array_push($tipoColumnas, "c");
        }
        if ($this->niveles[5] > 0) {
            array_push($anchoColumnas, 5);
            array_push($anchoColumnas, 30);
            array_push($nombreColumnas, "IDENTIFICADOR DE PARTIDA");
            array_push($nombreColumnas, "NOMBRE");
            array_push($incluirColumnas, "idp_id");
            array_push($incluirColumnas, "idp_nombre");
            array_push($tipoColumnas, "c");
            array_push($tipoColumnas, "c");
        }
        if ($this->niveles[6] > 0) {
            array_push($anchoColumnas, 5);
            array_push($anchoColumnas, 30);
            array_push($nombreColumnas, "PARTIDA GENÉRICA");
            array_push($nombreColumnas, "NOMBRE");
            array_push($incluirColumnas, "pg_id");
            array_push($incluirColumnas, "pg_nombre");
            array_push($tipoColumnas, "c");
            array_push($tipoColumnas, "c");
        }
        if ($this->niveles[7] > 0) {
            array_push($anchoColumnas, 5);
            array_push($anchoColumnas, 30);
            array_push($nombreColumnas, "PARTIDA ESPECÍFICA");
            array_push($nombreColumnas, "NOMBRE");
            array_push($incluirColumnas, "pe_id");
            array_push($incluirColumnas, "pe_nombre");
            array_push($tipoColumnas, "c");
            array_push($tipoColumnas, "c");
        }
        if ($this->niveles[8] > 0) {
            array_push($anchoColumnas, 5);
            array_push($anchoColumnas, 15);
            array_push($nombreColumnas, "TIPO DE RECURSO");
            array_push($nombreColumnas, "NOMBRE");
            array_push($incluirColumnas, "ter_id");
            array_push($incluirColumnas, "ter_nombre");
            array_push($tipoColumnas, "c");
            array_push($tipoColumnas, "c");
        } //*/
        if ($this->niveles[9] > 0) {
            array_push($anchoColumnas, 15);
            array_push($anchoColumnas, 30);
            array_push($nombreColumnas, "CLAVE DE FINANCIAMIENTO");
            array_push($nombreColumnas, "NOMBRE");
            array_push($incluirColumnas, "cfi_clave");
            array_push($incluirColumnas, "cfi_nombre");
            array_push($tipoColumnas, "c");
            array_push($tipoColumnas, "c");
        }

        array_push($anchoColumnas, 20);
        array_push($nombreColumnas, "CUENTA");
        array_push($incluirColumnas, "cuenta");
        array_push($tipoColumnas, "c");

        array_push($anchoColumnas, 30);
        array_push($nombreColumnas, "NOMBRE");
        array_push($incluirColumnas, "cta_nombre");
        array_push($tipoColumnas, "c");

        //IMPORTES
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "DEBE");
        array_push($incluirColumnas, "pdx_debe");
        array_push($tipoColumnas, "n");

        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "HABER");
        array_push($incluirColumnas, "pdx_haber");
        array_push($tipoColumnas, "n");

        if ($this->reporte['tipo'] == 1) {
            //COMPROMISO
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "CMP");
            array_push($incluirColumnas, "cmp_id");
            array_push($tipoColumnas, "c"); //*/
            //AFE
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "AFE");
            array_push($incluirColumnas, "afe_id");
            array_push($tipoColumnas, "c"); //*/
            //CLC
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "CLC");
            array_push($incluirColumnas, "clc_id");
            array_push($tipoColumnas, "c"); //*/
            //FACTURA
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "BDO");
            array_push($incluirColumnas, "bdo_id");
            array_push($tipoColumnas, "c"); //*/
            //POLIZA
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "POLIZA");
            array_push($incluirColumnas, "poliza");
            array_push($tipoColumnas, "c"); //*/

        }
        //EJERCICIO
        array_push($anchoColumnas, 10);
        array_push($nombreColumnas, "EJERCICIO");
        array_push($incluirColumnas, "pdx_ejercicio");
        array_push($tipoColumnas, "c"); //*/

        $table = array();
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $nombreColumnas[$key];
            $table['ancho'][$val] = $anchoColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }
        $name = 114;
        $path_image = dirname(__FILE__) . '/../../../../img/Logo Institucional/' . $name . '.jpg';
        $tablaImagen = array('imagen' => 1);
        $tablaImagen[0] = array('ruta' => $path_image, 'nombre' => 'XXX', 'colImg' => 'F1', 'altura' => 100, 'alingXimg' => 0);
        $this->generaExcel($this->datos, $encabezado, $table, array('nombre' => $this->nombreArchivoReporte, 'tablaimg' => $tablaImagen));
    }
}
$exp = new Exp_CuentaContable();
