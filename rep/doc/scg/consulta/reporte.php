<?php

declare(strict_types=1);
session_start();
set_time_limit(0);
//librerias
// Obligatorio para que el reporteador funcione
require_once dirname(__FILE__) . '/../../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';
//require_once dirname(__FILE__).'/../../../lib/reportetabla.1.0.0.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/TruncateCadena.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../../../ent/AuxiliarUnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/enc_ClaveFinanciamiento.php';
require_once dirname(__FILE__) . '/../../../../ent/enc_Programatico.php';
require_once dirname(__FILE__) . '/../../../../ent/Usuario.php';
require_once dirname(__FILE__) . '/../../../../ent/scp/Clc.php';
// Se incluyen los PHP necesarios en función de lo que se imprime
require_once dirname(__FILE__) . '/../../../../lib/MesNombre.php';
require_once dirname(__FILE__) . '/../../../../lib/redondear.php';
require_once dirname(__FILE__) . '/../../../../ent/scg/cuentaContable/Rubro.php';
require_once dirname(__FILE__) . '/../../../../ent/scg/cuentaContable/PlanCuentas.php';
require_once dirname(__FILE__) . '/../../../../ent/scg/cuentaContable/CuentaCuarto.php';
require_once dirname(__FILE__) . '/../../../../ent/scg/cuentaContable/CgSubcuenta.php';
// Por ejemplo, estas son librerías propias del reporta a imprimir
// en éste caso, se usan al imprimir catálogos, o la balanza de comprobación

require_once dirname(__FILE__) . '/../../../../lib/scg/consulta/balanza.php';
/* require_once dirname(__FILE__).'/../../../lib/scg/consulta/balanzaFondos.php'; */
require_once dirname(__FILE__) . '/../../../../lib/scg/consulta/contable.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/cuentaContable/cuentaContable.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/catalogo/mesContable.php';
require_once('../../../../lib/calculaPeso.php');

class reportetabla extends PDF
{
    public $ur;
    public $ue;
    private $nivel;
    private $usr_id;
    private $usuario;
    public $formato2015;
    private $firmar;
    private $firma;
    private $sis_id;
    public $men_id;
    public $opc_id;
    public $ejercicio;
    public $periodo;
    public $ecf;
    public $reporte;
    public $busqueda;
    public $claveFinanciamiento;
    public $detalle;
    public $niveles;
    public $miles;
    public $movimiento;
    public $nombreUE;
    public $nombreUR;

    private $nombreReporte;
    public $ancho;
    public $imagen;
    public $logo;


    public function __construct(
        $orientation = 'P',
        $unit = 'mm',
        $format = 'letter',
        $obj = null
    ) {
        parent::__construct($orientation, $unit, $format, $obj);

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();

        $this->ur->setUsr_id($_SESSION[_S]['usr_id']);
        $this->nivel = $_SESSION[_S]['nvj_id'];
        $this->usr_id = $_SESSION[_S]['usr_id'];

        if ($this->usr_id != '' && $this->usr_id != 0) {
            $this->usuario = new Usuario();
            $this->usuario->setUsr_id($this->usr_id);
            $this->usuario->select();
        }

        if (isset($_SESSION[_S]['clasAdm-ue'])) {
            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $this->ue->setUr_id($_SESSION[_S]['clasAdm-ur']);
            $this->ue->setUe_id($_SESSION[_S]['clasAdm-ue']);
            $this->ue->select();
        } elseif (isset($_POST['ue']) && trim($_POST['ue']) != '') {
            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $this->ue->setUr_id($_SESSION[_S]['clasAdm-ur']);
            $this->ue->setUe_id(trim($_POST['ue']));
            $this->ue->select();
        } else {
            $this->ue = false;
        }

        $this->ue_id = ($this->ue ? $this->ue->getUe_id() : false);

        if (isset($_POST['firma']) && $_POST['firma'] != 0) {
            $this->firmar = true;
        } else {
            $this->firmar = false;
        }

        if ($this->firmar) {
            $this->firma = new AuxiliarUnidadResponsable();
            $this->firma->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $this->firma->setUr_id($_SESSION[_S]['clasAdm-ur']);
            $this->firma->select();
        }

        $this->sis_id = (isset($_POST['sis_id']) ? $_POST['sis_id'] : '');
        $this->men_id = (isset($_POST['men_id']) ? $_POST['men_id'] : '');
        $this->opc_id = (isset($_POST['opc_id']) ? $_POST['opc_id'] : '');

        if (isset($_POST['check2025']) && $_POST['check2025'] == 1) {
            $this->ejercicio = "2025";
        }elseif (isset($_POST['check2024']) && $_POST['check2024'] == 1) {
            $this->ejercicio = "2024";
        }elseif (isset($_POST['check2023']) && $_POST['check2023'] == 1) {
            $this->ejercicio = "2023";
        } elseif (isset($_POST['check2022']) && $_POST['check2022'] == 1) {
            $this->ejercicio = "2022";
        } elseif (isset($_POST['check2021']) && $_POST['check2021'] == 1) {
            $this->ejercicio = "2021";
        } elseif (isset($_POST['check2020']) && $_POST['check2020'] == 1) {
            $this->ejercicio = "2020";
        } elseif (isset($_POST['check2019']) && $_POST['check2019'] == 1) {
            $this->ejercicio = "2019";
        } elseif (isset($_POST['check2018']) && $_POST['check2018'] == 1) {
            $this->ejercicio = "2018";
        } elseif (isset($_POST['check2017']) && $_POST['check2017'] == 1) {
            $this->ejercicio = "2017";
        } elseif (isset($_POST['check2016']) && $_POST['check2016'] == 1) {
            $this->ejercicio = "2016";
        } elseif (isset($_POST['check2015']) && $_POST['check2015'] == 1) {
            $this->ejercicio = "2015";
        } else {
            $this->ejercicio = APP_EJERCICIO;
        }

        // -----------------------------------------------------------------------------------------
        // Paso 1: Aquí preparamos las opciones globales y variables que usaremos en la sección CONTENT()
        // -----------------------------------------------------------------------------------------
        switch ($this->men_id) {
                // Menú CUENTA
            case 1:

                break;
            case 2: // Menú catalogo

                break;
            case 4:
                switch ($this->opc_id) {
                    case 2:
                        $this->setOrientation('L');

                        $this->periodo['mesDesde'] = ((isset($_POST['comboMesDesde'])
                            && trim($_POST['comboMesDesde']) != '') ? $_POST['comboMesDesde']
                            : -1);

                        $this->busqueda['tipo'] = 1;

                        $this->busqueda['gur'] = isset($_POST['inputGur']) ? $_POST['inputGur']
                            : '';
                        $this->busqueda['clc_id'] = isset($_POST['inputClc_id'])
                            ? $_POST['inputClc_id'] : ''; //CLC a buscar

                        $this->busqueda['cuentaMayor'] = isset($_POST['inputCuentaMayor'])
                            ? ($_POST['inputCuentaMayor'] != '' ? $_POST['inputCuentaMayor']
                                : '') : '';
                        $this->busqueda['cuentaSefin'] = isset($_POST['inputCuentaSefin'])
                            ? ($_POST['inputCuentaSefin'] != '' ? $_POST['inputCuentaSefin']
                                : '') : '';
                        $this->busqueda['cuentaRegistro'] = isset($_POST['inputCuentaRegistro'])
                            ? ($_POST['inputCuentaRegistro'] != '' ? $_POST['inputCuentaRegistro']
                                : '') : '';

                        $this->reporte['tipo'] = 1;
                        $this->reporte['agrupador'] = 1;
                        $this->ecf = ((isset($_POST['ecf_id']) && $_POST['ecf_id']
                            != "") ? $_POST['ecf_id'] : "");

                        break;
                    case 3:
                        $this->setOrientation('L');

                        $this->periodo['mesDesde'] = ((isset($_POST['comboMesDesde'])
                            && trim($_POST['comboMesDesde']) != '') ? $_POST['comboMesDesde']
                            : -1);
                        $this->periodo['mesHasta'] = ((isset($_POST['comboMesHasta'])
                            && trim($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta']
                            : -1);

                        $this->niveles[1] = ((isset($_POST['checkNivel1']) && trim($_POST['checkNivel1'])
                            != '') ? $_POST['checkNivel1'] : -1);
                        $this->niveles[2] = ((isset($_POST['checkNivel2']) && trim($_POST['checkNivel2'])
                            != '') ? $_POST['checkNivel2'] : -1);
                        $this->niveles[3] = ((isset($_POST['checkNivel3']) && trim($_POST['checkNivel3'])
                            != '') ? $_POST['checkNivel3'] : -1);
                        $this->niveles[4] = ((isset($_POST['checkNivel4']) && trim($_POST['checkNivel4'])
                            != '') ? $_POST['checkNivel4'] : -1);
                        $this->niveles[5] = ((isset($_POST['checkNivel5']) && trim($_POST['checkNivel5'])
                            != '') ? $_POST['checkNivel5'] : -1);
                        $this->niveles[6] = ((isset($_POST['checkNivel6']) && trim($_POST['checkNivel6'])
                            != '') ? $_POST['checkNivel6'] : -1);
                        $this->niveles[7] = ((isset($_POST['checkNivel7']) && trim($_POST['checkNivel7'])
                            != '') ? $_POST['checkNivel7'] : -1);
                        $this->niveles[8] = ((isset($_POST['checkNivelR']) && trim($_POST['checkNivelR'])
                            != '') ? $_POST['checkNivelR'] : -1);
                        $this->niveles[9] = ((isset($_POST['checkNivelGur']) && trim($_POST['checkNivelGur'])
                            != '') ? $_POST['checkNivelGur'] : -1);

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

                        $this->busqueda['cuentaDesde'] = isset($_POST['inputCuentaDesde'])
                            ? ($_POST['inputCuentaDesde'] != '' ? $_POST['inputCuentaDesde']
                                : 10000) : 10000;
                        $this->busqueda['cuentaHasta'] = isset($_POST['inputCuentaHasta'])
                            ? ($_POST['inputCuentaHasta'] != '' ? $_POST['inputCuentaHasta']
                                : 99999) : 99999;

                        $this->formato2015 = ((isset($_POST['checkFormato2015'])
                            && $_POST['checkFormato2015'] == 1) ? $_POST['checkFormato2015']
                            : false);
                        $this->miles = ((isset($_POST['checkMiles']) && $_POST['checkMiles']
                            == 1) ? $_POST['checkMiles'] : false);
                        $this->movimiento = ((isset($_POST['checkMovimiento'])
                            && $_POST['checkMovimiento'] == 1) ? true : false);

                        if (!$this->formato2015 && $this->ejercicio == APP_EJERCICIO) {

                            $this->busqueda['tipo'] = (isset($_POST['checkGur'])
                                && $_POST['checkGur'] == 1 ? 4 : (isset($_POST['checkClc'])
                                    && $_POST['checkClc'] == 1 ? 2 : (isset($_POST['checkBdo'])
                                        && $_POST['checkBdo'] == 1 ? 3 : 1)));
                            #opcion UR
                            $this->busqueda['gur'] = isset($_POST['inputGur']) ? $_POST['inputGur']
                                : -1; //UR a buscar
                            #opcion CLC
                            $this->busqueda['clc_id'] = isset($_POST['inputClc'])
                                ? $_POST['inputClc'] : -1; //CLC a buscar
                            #opcion Bdo
                            $this->busqueda['bdo_id'] = isset($_POST['inputBdo'])
                                ? $_POST['inputBdo'] : -1; //Bdo a buscar

                            $this->reporte['tipo'] = isset($_POST['tipoReporte'])
                                ? $_POST['tipoReporte'] : 1;
                            $this->reporte['agrupador'] = isset($_POST['checkAgrupadoUr'])
                                ? $_POST['checkAgrupadoUr'] : "";
                            switch ($this->reporte['tipo']) {
                                case 2:
                                    $this->ue_id = false;
                                    $this->ue = false;
                                    break;
                                case 3:
                                    $this->reporte['grupos'] = isset($_POST['inputGrupos'])
                                        ? $_POST['inputGrupos'] : "";
                                    $this->reporte['excluir'] = isset($_POST['inputExcluir'])
                                        ? $_POST['inputExcluir'] : "";
                                    $this->ue_id = false;
                                    $this->ue = false;
                                    break;
                            }

                            $estatal = (isset($_POST['checkEstatal']) && $_POST['checkEstatal']
                                == 1) ? true : false;
                            $federal = (isset($_POST['checkFederal']) && $_POST['checkFederal']
                                == 1) ? true : false;
                            $this->claveFinanciamiento['ecf_id'] = isset($_POST['cveFin'])
                                ? $_POST['cveFin'] : ''; // busqueda por clave de financiamiento
                            $this->claveFinanciamiento['ter_id'] = ($this->claveFinanciamiento['ecf_id']
                                != '') ? '' : (($federal && $estatal) ? '' : ($federal
                                ? 'B' : ($estatal ? 'A' : '')));

                            // Marcos: 18/mayo/2016
                            // Adicionamos un indicador para saber si la petición viene de la pantalla detalle
                            // ya que cambia un poco el where
                            $this->detalle = isset($_POST['detalle']) ? true : false;
                        } else {
                            $this->busqueda['tipo'] = 1;

                            $this->reporte['tipo'] = isset($_POST['tipoReporte']) ? $_POST['tipoReporte'] : 1;
                            $this->reporte['agrupador'] = isset($_POST['checkAgrupadoUr']) ? $_POST['checkAgrupadoUr'] : "";
                            switch ($this->reporte['tipo']) {
                                case 2:
                                    $this->ue_id = false;
                                    $this->ue = false;
                                    break;
                                case 3:
                                    $this->reporte['grupos'] = isset($_POST['inputGrupos']) ? $_POST['inputGrupos'] : "";
                                    $this->reporte['excluir'] = isset($_POST['inputExcluir']) ? $_POST['inputExcluir'] : "";
                                    $this->ue_id = false;
                                    $this->ue = false;
                                    break;
                            }

                            $estatal = false;
                            $federal = false;
                            $this->claveFinanciamiento['ecf_id'] = '';
                            $this->claveFinanciamiento['ter_id'] = '';

                            $this->detalle = false;
                        }

                        break;

                        // Báscula de Comprobación
                    case 4:
                        $this->setOrientation('L');

                        $this->periodo['mesDesde'] = ((isset($_POST['comboMesDesde'])
                            && trim($_POST['comboMesDesde']) != '') ? $_POST['comboMesDesde']
                            : -1);
                        $this->periodo['mesHasta'] = ((isset($_POST['comboMesHasta'])
                            && trim($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta']
                            : -1);

                        $this->niveles[1] = ((isset($_POST['checkNivel1']) && trim($_POST['checkNivel1'])
                            != '') ? $_POST['checkNivel1'] : -1);
                        $this->niveles[2] = ((isset($_POST['checkNivel2']) && trim($_POST['checkNivel2'])
                            != 'n_0') ? $_POST['checkNivel2'] : -1);
                        $this->niveles[3] = ((isset($_POST['checkNivel3']) && trim($_POST['checkNivel3'])
                            != 'n_0') ? $_POST['checkNivel3'] : -1);
                        $this->niveles[4] = ((isset($_POST['checkNivel4']) && trim($_POST['checkNivel4'])
                            != 'n_0') ? $_POST['checkNivel4'] : -1);
                        $this->niveles[5] = ((isset($_POST['checkNivel5']) && trim($_POST['checkNivel5'])
                            != 'n_0') ? $_POST['checkNivel5'] : -1);
                        $this->niveles[6] = ((isset($_POST['checkNivel6']) && trim($_POST['checkNivel6'])
                            != 'n_0') ? $_POST['checkNivel6'] : -1);
                        $this->niveles[7] = ((isset($_POST['checkNivel7']) && trim($_POST['checkNivel7'])
                            != 'n_0') ? $_POST['checkNivel7'] : -1);
                        $this->niveles[8] = ((isset($_POST['checkNivel8']) && trim($_POST['checkNivel8'])
                            != 'n_0') ? $_POST['checkNivel8'] : -1);

                        // Determinamos el máximo nivel solicitado
                        $this->niveles[0] = ($this->niveles[8] > 0) ? 8 : (
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
                        );

                        $this->busqueda['cuentaDesde'] = isset($_POST['inputCuentaDesde'])
                            ? ($_POST['inputCuentaDesde'] != '' ? $_POST['inputCuentaDesde']
                                : 10000) : 10000;
                        $this->busqueda['cuentaHasta'] = isset($_POST['inputCuentaHasta'])
                            ? ($_POST['inputCuentaHasta'] != '' ? $_POST['inputCuentaHasta']
                                : 99999) : 99999;

                        $this->formato2015 = ((isset($_POST['checkFormato2015'])
                            && $_POST['checkFormato2015'] == 1) ? $_POST['checkFormato2015']
                            : false);
                        $this->miles = ((isset($_POST['checkMiles']) && $_POST['checkMiles']
                            == 1) ? $_POST['checkMiles'] : false);
                        $this->movimiento = ((isset($_POST['checkMovimiento'])
                            && $_POST['checkMovimiento'] == 1) ? true : false);

                        $this->busqueda['tipo'] = (isset($_POST['checkGur']) && $_POST['checkGur']
                            == 1 ? 4 : (isset($_POST['checkClc']) && $_POST['checkClc']
                                == 1 ? 2 : (isset($_POST['checkBdo']) && $_POST['checkBdo']
                                    == 1 ? 3 : 1)));
                        #opcion UR
                        $this->busqueda['gur'] = isset($_POST['inputGur']) ? $_POST['inputGur']
                            : -1; //UR a buscar
                        #opcion CLC
                        $this->busqueda['clc_id'] = isset($_POST['inputClc']) ? $_POST['inputClc']
                            : -1; //CLC a buscar
                        #opcion Bdo
                        $this->busqueda['bdo_id'] = isset($_POST['inputBdo']) ? $_POST['inputBdo']
                            : -1; //Bdo a buscar

                        $this->reporte['tipo'] = isset($_POST['tipoReporte']) ? $_POST['tipoReporte']
                            : 1;
                        $this->reporte['agrupador'] = isset($_POST['checkAgrupadoUr'])
                            ? $_POST['checkAgrupadoUr'] : "";
                        if ($this->reporte['tipo'] == 3) {
                            $this->reporte['grupos'] = isset($_POST['inputGrupos'])
                                ? $_POST['inputGrupos'] : "";
                            $this->reporte['excluir'] = isset($_POST['inputExcluir'])
                                ? $_POST['inputExcluir'] : "";
                        }

                        $estatal = (isset($_POST['checkEstatal']) && $_POST['checkEstatal']
                            == 1) ? true : false;
                        $federal = (isset($_POST['checkFederal']) && $_POST['checkFederal']
                            == 1) ? true : false;
                        $this->claveFinanciamiento['ecf_id'] = isset($_POST['cveFin'])
                            ? $_POST['cveFin'] : ''; // busqueda por clave de financiamiento
                        $this->claveFinanciamiento['ter_id'] = ($this->claveFinanciamiento['ecf_id']
                            != '') ? '' : (($federal && $estatal) ? '' : ($federal
                            ? 'B' : ($estatal ? 'A' : '')));

                        // Marcos: 18/mayo/2016
                        // Adicionamos un indicador para saber si la petición viene de la pantalla detalle
                        // ya que cambia un poco el where
                        $this->detalle = isset($_POST['detalle']) ? true : false;
                        break;

                    default:
                        break;
                }
                break;
            case 10:
                break;
            default:
                break;
        }
    }

    function Header()
    { //Encabezado del documento
        switch ($this->getOrientation()) {
            case 'P':
                $this->longitud = 60;
                $this->ancho = 175;
                $this->imagen = 195;
                $this->logo = 122;
                break;
            case 'L':
                $this->longitud = 95;
                $this->ancho = 239;
                $this->imagen = 259;
                $this->logo = 186;
                break;
        }
        // -----------------------------------------------------------------------------------------
        // Paso 2: Configuramos el texto del encabezado
        // -----------------------------------------------------------------------------------------
        $encabezadoCuenta = false;
        $encabezadoCuentaTexto = '';
        switch ($this->men_id) {
                // Menú CUENTA
            case 1:
                break;
                //Menú Catálogo
            case 2:
                break;
            case 4:
                switch ($this->opc_id) {
                    case 2:
                        $this->nombreReporte = 'LIBRO MAYOR';
                        if ($this->periodo['mesDesde'] == 0)
                            $this->nombreReporte .= ' DEL PERIODO DE APERTURA ' . $this->ejercicio . '.';
                        else
                            $this->nombreReporte .= ' DEL MES DE ' . MesNombre($this->periodo['mesDesde']) . ' DE ' . $this->ejercicio . '.';

                        if ($this->busqueda['cuentaRegistro'] != '') {
                            $arrCta = explode(
                                ".",
                                $this->busqueda['cuentaMayor']
                            );
                            $elementos = count($arrCta);
                            if ($elementos != 5) {
                                echo "verificar cuenta de mayor ";
                                die();
                            }


                            $ger_id = $arrCta[0];
                            $cgp_id = $arrCta[1];
                            $rub_id = $arrCta[2];
                            $cgcu_id = $arrCta[3];
                            $cgsc_id = $arrCta[4];

                            $cgc_id = $this->busqueda['cuentaSefin'];
                            $cta_id = $this->busqueda['cuentaRegistro'];
                            $planCuentas = new PlanCuentas();
                            $planCuentas->setCta_gpo_id($this->ur->getGpo_id());
                            $planCuentas->setCta_ur_id($this->ur->getUr_id());
                            $planCuentas->setCta_ue_id($this->ue_id ? $this->ue_id
                                : $this->ur->getUeContable());
                            $planCuentas->setGer_id($ger_id);
                            $planCuentas->setCgp_id($cgp_id);
                            $planCuentas->setRub_id($rub_id);
                            $planCuentas->setCgcu_id($cgcu_id);
                            $planCuentas->setCgsc_id($cgsc_id);
                            $planCuentas->setCgc_id($cgc_id);
                            $planCuentas->setCta_id($cta_id);
                            $planCuentas->select();

                            $encabezadoCuenta = true;
                            $encabezadoCuentaTexto = "de la cuenta " . $planCuentas->getCuenta() . "-" . $planCuentas->getNombre();
                        } elseif ($this->busqueda['cuentaSefin'] != '') {
                            $arrCta = explode(
                                ".",
                                $this->busqueda['cuentaMayor']
                            );
                            $elementos = count($arrCta);
                            if ($elementos != 5) {
                                echo "verificar cuenta de mayor ";
                                die();
                            }
                            $ger_id = $arrCta[0];
                            $cgp_id = $arrCta[1];
                            $rub_id = $arrCta[2];
                            $cgcu_id = $arrCta[3];
                            $cgsc_id = $arrCta[4];
                            $cgc_id = $this->busqueda['cuentaSefin'];
                            $sefin = new CuentaCuarto();
                            $sefin->setGer_id($ger_id);
                            $sefin->setCgp_id($cgp_id);
                            $sefin->setRub_id($rub_id);
                            $sefin->setCgcu_id($cgcu_id);
                            $sefin->setCgsc_id($cgsc_id);
                            $sefin->setCgc_id($cgc_id);
                            $sefin->select();

                            $encabezadoCuenta = true;
                            $encabezadoCuentaTexto = "de la cuenta " . $sefin->getCuenta() . "-" . $sefin->getNombre();
                        } elseif ($this->busqueda['cuentaMayor'] != '') {

                            $arrCta = explode(
                                ".",
                                $this->busqueda['cuentaMayor']
                            );
                            $elementos = count($arrCta);
                            if ($elementos != 5) {
                                echo "verificar cuenta de mayor ";
                                die();
                            }

                            $ger_id = $arrCta[0];
                            $cgp_id = $arrCta[1];
                            $rub_id = $arrCta[2];
                            $cgcu_id = $arrCta[3];
                            $cgsc_id = $arrCta[4];

                            $cgsubcuenta = new CgSubcuenta();
                            $cgsubcuenta->setGer_id($ger_id);
                            $cgsubcuenta->setCgp_id($cgp_id);
                            $cgsubcuenta->setRub_id($rub_id);
                            $cgsubcuenta->setCgcu_id($cgcu_id);
                            $cgsubcuenta->setCgsc_id($cgsc_id);
                            $cgsubcuenta->select();

                            $encabezadoCuenta = true;
                            $encabezadoCuentaTexto = "de la cuenta " . $cgsubcuenta->getCuenta() . "-" . $cgsubcuenta->getNombre();
                        }
                        if ($this->busqueda['clc_id'] != '') {
                            $clc = new Clc();
                            $clc->setGpo_id($this->ur->getGpo_id());
                            $clc->setUr_id($this->ur->getUr_id());
                            $clc->setClc_id($this->busqueda['clc_id']);
                            $clc->select();

                            $encabezadoCuenta = true;
                            $encabezadoCuentaTexto .= " de la CLC " . $clc->getClc_id() . "-" . substr($clc->getConcepto(), 0, 60) . "...";
                        }

                        break;
                    case 3:
                        $this->nombreReporte = 'BALANZA DE COMPROBACIÓN';
                        if ($this->periodo['mesHasta'] == $this->periodo['mesDesde']) {
                            if ($this->periodo['mesHasta'] == 0)
                                $this->nombreReporte .= ' DEL PERIODO DE APERTURA ' . $this->ejercicio . '.';
                            else
                                $this->nombreReporte .= ' DEL MES DE ' . MesNombre($this->periodo['mesDesde']) . ' DE ' . $this->ejercicio . '.';
                        } else {
                            $this->nombreReporte .= ' DEL PERIODO DE ' . MesNombre($this->periodo['mesDesde']) . '-' . MesNombre($this->periodo['mesHasta']) . ' DE ' . $this->ejercicio . '.';
                        }

                        if ($this->claveFinanciamiento['ecf_id'] != '') {
                            $cvefin = new enc_ClaveFinanciamiento();
                            $cvefin->setEcf_id($this->claveFinanciamiento['ecf_id']);
                            $cvefin->select();

                            $this->claveFinanciamiento['mensaje'] = utf8Decode('Clave de Financiamiento: ' . $cvefin->getClave() . ' - ' . $cvefin->getNombre());
                        } elseif ($this->claveFinanciamiento['ter_id'] == 'B') {
                            $this->claveFinanciamiento['mensaje'] = 'Recurso Federal';
                        } elseif ($this->claveFinanciamiento['ter_id'] == 'A') {
                            $this->claveFinanciamiento['mensaje'] = 'Recurso Estatal';
                        } else {
                            $this->claveFinanciamiento['mensaje'] = '';
                        }

                        break;

                    case 4:
                        $this->nombreReporte = 'BÁSCULA DE COMPROBACIÓN';
                        if ($this->periodo['mesHasta'] == $this->periodo['mesDesde']) {
                            if ($this->periodo['mesHasta'] == 0)
                                $this->nombreReporte .= ' DEL PERIODO DE APERTURA ' . $this->ejercicio . '.';
                            else
                                $this->nombreReporte .= ' DEL MES DE ' . MesNombre($this->periodo['mesDesde']) . ' DE ' . $this->ejercicio . '.';
                        } else {
                            $this->nombreReporte .= ' DEL PERIODO DE ' . MesNombre($this->periodo['mesDesde']) . '-' . MesNombre($this->periodo['mesHasta']) . ' DE ' . $this->ejercicio . '.';
                        }
                        if ($this->claveFinanciamiento['ecf_id'] != '') {
                            $cvefin = new enc_ClaveFinanciamiento();
                            $cvefin->setEcf_id($this->claveFinanciamiento['ecf_id']);
                            $cvefin->select();

                            $this->claveFinanciamiento['mensaje'] = utf8Decode('Clave de Financiamiento: ' . $cvefin->getClave() . ' - ' . $cvefin->getNombre());
                        } elseif ($this->claveFinanciamiento['ter_id'] == 'B') {
                            $this->claveFinanciamiento['mensaje'] = 'Recurso Federal';
                        } elseif ($this->claveFinanciamiento['ter_id'] == 'A') {
                            $this->claveFinanciamiento['mensaje'] = 'Recurso Estatal';
                        } else {
                            $this->claveFinanciamiento['mensaje'] = '';
                        }

                        break;
                    default:
                        break;
                }
                $this->nombreReporte = utf8Decode($this->nombreReporte);
                break;
            case 10:
                break;
            default:
                break;
        }
        if ($_SESSION[_S]['nvj_id'] > 2) {
            $this->nombreUR = 'DCG';
        } else {
            $this->nombreUR = 'UR';
        }

        $this->SetMargins(20, 10, 20);
        $this->SetXY(20, 10);
        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);

        $this->Image(
            '../../../../img/logoOaxaca-color-fblanco_v2.png',
            $this->logo,
            $iy,
            74,
            20,
            'png'
        );
        $iyt = 0;

        $this->SetFont('univiapro', 'B', 8);
        $this->SetY($iy + $iyt);
        $this->Cell(195, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');

        $iyt += 4;
        $this->SetY($iy + $iyt);
        $this->Cell(180, 5, 'CONTABILIDAD GUBERNAMENTAL ' . APP_EJERCICIO, 0, 1, 'L');

        $this->SetFont('univiapro', '', 8);
        if ($_SESSION[_S]['nvj_id'] > 2) {
            switch ($this->reporte['tipo']) {
                case 2:
                    $this->nombreUR = "DIRECCIÓN DE CONTABILIDAD GUBERNAMENTAL";
                    break;
                case 3:
                    $this->nombreUR = "DIRECCIÓN DE CONTABILIDAD GUBERNAMENTAL";
                    break;
                default:
                    $this->nombreUR = "DCG: " . $this->ur->getClave() . " - " . $this->ur->getNombre();
                    break;
            }
        } else {
            $this->nombreUR = "UR: " . $this->ur->getClave() . " - " . $this->ur->getNombre();
        }
        $this->nombreUR = TruncateCadena(
            utf8Decode(trim($this->nombreUR)),
            $this->longitud
        );
        $iyt += 4;
        $this->SetY($iy + $iyt);
        $this->Cell(5, 5, substr($this->nombreUR, 0, $this->longitud), 0, 0, 'L');
        if (strlen($this->nombreUR) > $this->longitud) {
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->Cell(
                5,
                5,
                substr($this->nombreUR, $this->longitud, $this->longitud),
                0,
                0,
                'L'
            );
        }
        if (strlen($this->nombreUR) > $this->longitud * 2) {
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->Cell(
                5,
                5,
                substr($this->nombreUR, $this->longitud * 2, $this->longitud),
                0,
                0,
                'L'
            );
        }

        if ($this->ue_id) {
            $this->SetFont('univiapro', '', 7);
            $this->nombreUE = TruncateCadena(
                utf8Decode(trim("UE: " . $this->ue->getUe_id() . " - " . $this->ue->getNombre())),
                $this->longitud
            );
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->Cell(
                5,
                5,
                substr($this->nombreUE, 0, $this->longitud),
                0,
                0,
                'L'
            );

            if (strlen($this->nombreUE) > $this->longitud) {
                $iyt += 4;
                $this->SetY($iy + $iyt);
                $this->Cell(
                    5,
                    5,
                    substr($this->nombreUE, $this->longitud, $this->longitud),
                    0,
                    0,
                    'L'
                );
            }
            if (strlen($this->nombreUE) > $this->longitud * 2) {
                $iyt += 4;
                $this->SetY($iy + $iyt);
                $this->Cell(
                    5,
                    5,
                    substr($this->nombreUE, $this->longitud * 2),
                    0,
                    0,
                    'L'
                );
            }
            $this->SetFont('univiapro', 'B', 8);
        }

        $iyt += 4;
        $this->SetY($iy + $iyt);
        $this->Cell(180, 5, $this->nombreReporte, 0, 1, 'L');

        $this->SetFont('univiapro', '', 7);
        if (isset($this->claveFinanciamiento) && $this->claveFinanciamiento) {
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->SetFont('univiapro', 'B', 7);
            $this->Cell(180, 5, $this->claveFinanciamiento['mensaje'], 0, 1, 'L');
        }

        if (isset($this->miles) && $this->miles) {
            $iyt += 4;
            $this->setY($iy + $iyt);
            $this->SetFont('univiapro', '', 6);
            $this->Cell(180, 5, "(en miles de pesos)", 0, 1, 'L');
        }

        if ($this->men_id == 4 && $this->opc_id == 3 && isset($this->detalle) && $this->detalle) {
            $cuenta = str_replace(".", "", $this->busqueda['cuentaDesde']);
            $ger_id = substr($cuenta, 0, 1);
            $cgp_id = substr($cuenta, 1, 1);
            $rub_id = substr($cuenta, 2, 1);
            $rubro = new Rubro();
            $rubro->setGer_id($ger_id);
            $rubro->setCgp_id($cgp_id);
            $rubro->setRub_id($rub_id);
            $rubro->select();

            $iyt += 4;
            $this->setY($iy + $iyt);
            $this->SetFont('univiapro', '', 6);
            $this->Cell(
                180,
                5,
                utf8Decode("(del rubro " . $rubro->getCuenta() . " - " . $rubro->getNombre() . ")"),
                0,
                1,
                'L'
            );
        }

        if ($encabezadoCuenta) {
            $iyt += 4;
            $this->setY($iy + $iyt);
            $this->SetFont('univiapro', '', 6);
            $this->Cell(180, 5, utf8Decode($encabezadoCuentaTexto), 0, 1, 'L');
        }

        if ($iyt < 20) {
            $iyt = 24;
        } else {
            $iyt += 4;
        }
        $this->Line($ix, $iy + $iyt, $this->imagen, $iy + $iyt);

        $iyt += 2;
        $this->setY($iy + $iyt);
        $this->SetBorders(true);
        $this->SetRelleno(true);
        $this->SetFont('univiapro', 'B', 8);
        $this->setDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->setAltoLinea(3);

        // -----------------------------------------------------------------------------------------
        // Paso 3: Configuramos el nombre y ancho de cada columna del reporte
        // El ancho total para PDF en modo "portrait" es 175
        // -----------------------------------------------------------------------------------------
        switch ($this->men_id) {
                // Menú CUENTA
            case 1:

                break;
            case 2:

                break;
            case 4:
                switch ($this->opc_id) {
                    case 2:
                        $this->SetWidths(array(
                            10,
                            15,
                            15,
                            30,
                            10,
                            $this->ancho - 185,
                            15,
                            11,
                            11,
                            11,
                            11,
                            23,
                            23
                        ));
                        $this->SetAligns(array(
                            'C',
                            'L',
                            'C',
                            'L',
                            'L',
                            'L',
                            'C',
                            'C',
                            'C',
                            'C',
                            'R',
                            'R',
                            'R'
                        ));
                        $this->Row(array(
                            'UE',
                            utf8Decode('PÓLIZA'),
                            'FECHA',
                            'RFC',
                            'TDF',
                            'REFERENCIA',
                            'FECHA REF.',
                            'CLC',
                            'AFE',
                            'CMP',
                            'BDO',
                            'DEBE',
                            'HABER'
                        ));
                        break;
                    case 3:
                    case 4:
                        $this->SetWidths(array(
                            35,
                            $this->ancho - 189,
                            48,
                            48,
                            48,
                            10
                        ));
                        $this->SetAligns(array('L', 'L', 'C', 'C', 'C', 'C'));
                        $this->Row(array(
                            'CUENTA',
                            utf8Decode('DESCRIPCION'),
                            'SALDOS INICIALES',
                            'MOVIMIENTOS',
                            'SALDOS FINALES',
                            'NAT'
                        ));

                        $this->SetWidths(array(
                            35,
                            $this->ancho - 189,
                            24,
                            24,
                            24,
                            24,
                            24,
                            24,
                            10
                        ));
                        $this->SetAligns(array(
                            'L',
                            'L',
                            'R',
                            'R',
                            'R',
                            'R',
                            'R',
                            'R',
                            'C'
                        ));
                        $this->Row(array(
                            '',
                            '',
                            'DEBE',
                            'HABER',
                            'DEBE',
                            'HABER',
                            'DEBE',
                            'HABER',
                            ''
                        ));
                        break;
                    default:
                        break;
                }
                break;
            case 10:
                break;
            default:
                break;
        }
        $this->ln(1);
    }

    function Content($datos)
    {

        //realizamos la impresion de acuerdo al nivel(aqui no se procesa la informacion, el array debe estar procesado y ordenado
        $this->SetFont('univiapro', '', 6); ///tipo de letra para cada titulo
        $this->setDrawColor(170, 170, 170);
        $this->SetFillColor(224, 224, 224);
        $relleno = true;
        // -----------------------------------------------------------------------------------------
        // Paso 4: Imprimirmos los datos obtenidos en el paso 5... ésta es la impresión del reporte en sí
        // -----------------------------------------------------------------------------------------
        switch ($this->men_id) {
                // Menú CUENTA
            case 1:

                break;
                // Menú Catalogo
            case 2:

                break;
            case 4:
                switch ($this->opc_id) {
                    case 2:
                        $this->SetWidths(array(10, 15, 15, 30, 10, $this->ancho - 185, 15, 11, 11, 11, 11, 23, 23));
                        $this->SetAligns(array('C', 'L', 'C', 'L', 'L', 'L', 'C', 'C', 'C', 'C', 'R', 'R'));
                        foreach ($datos as $key => $dato) {
                            $this->SetRelleno($relleno);
                            $this->Row(array(
                                $dato['cta_ue_id'],
                                $dato['poliza'],
                                $dato['pma_fecha'],
                                $dato['rfc_id'],
                                $dato['tdf_id'],
                                $dato['pdx_referencia'],
                                $dato['pdx_fechaReferencia'],
                                $dato['clc_id'],
                                $dato['afe_id'],
                                $dato['cmp_id'],
                                $dato['bdo_id'],
                                ((bccomp(
                                    $dato['pdx_debe'],
                                    "0",
                                    2
                                )) ? number_format(floatval($dato['pdx_debe']), 2)
                                    : '---'),
                                ((bccomp(
                                    $dato['pdx_haber'],
                                    "0",
                                    2
                                )) ? number_format(
                                    floatval($dato['pdx_haber']),
                                    2
                                ) : '---')
                            ));
                            $relleno = !$relleno;
                        }

                        break;
                    case 3:
                    case 4:
                        $this->SetWidths(array(35, $this->ancho - 189, 24, 24, 24, 24, 24, 24, 10));
                        $this->SetAligns(array('L', 'L', 'R', 'R', 'R', 'R', 'R', 'R', 'C'));
                        foreach ($datos as $key => $dato) {

                            $this->SetRelleno($relleno);
                            $this->Row(array((is_null($dato['cuenta']) ? '' : utf8Decode($dato['cuenta'])),
                                (is_null($dato['nombre']) ? '' : utf8Decode($dato['nombre'])),
                                number_format(round($dato['pdx_debeInicial'], 2), 2) == 0 ? '-' : number_format(round($dato['pdx_debeInicial'], 2), 2),
                                number_format(round($dato['pdx_haberInicial'], 2), 2) == 0 ? '-' : number_format(round($dato['pdx_haberInicial'], 2), 2),
                                number_format(round($dato['pdx_debePeriodo'], 2), 2) == 0 ? '-' : number_format(round($dato['pdx_debePeriodo'], 2), 2),
                                number_format(round($dato['pdx_haberPeriodo'], 2), 2) == 0 ? '-' : number_format(round($dato['pdx_haberPeriodo'], 2), 2),
                                number_format(round($dato['pdx_debeFinal'], 2), 2) == 0 ? '-' : number_format(round($dato['pdx_debeFinal'], 2), 2),
                                number_format(round($dato['pdx_haberFinal'], 2), 2) == 0 ? '-' : number_format(round($dato['pdx_haberFinal'], 2), 2),
                                $dato['cgm_naturaleza']
                            ));
                            $relleno = !$relleno;
                        }
                        break;
                    default:
                        break;
                }
                break;
            case 10:

                break;
            default:
                break;
        }
    }

    function Footer($lastPage = true)
    {

        switch ($this->getOrientation()) {
            case 'P':
                $x = 15;
                $distancia = 5;
                break;
            case 'L':
                $x = 30;
                $distancia = 25;
                break;
        }

        if ($this->firmar) {
            $this->SetFont('univiapro', 'B', 6.5);

            $y = -40;
            $this->SetY($y);
            $this->setX($x);
            $this->MultiCell(57, 3, utf8Decode('REVISÓ'), 0, 'C', false);

            $this->SetY($y);
            $this->setX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);

            $this->SetY($y);
            $this->setX($x + 57 + $distancia);
            $this->MultiCell(57, 3, utf8Decode('REVISÓ'), 0, 'C', false);

            $this->SetY($y);
            $this->setX($x + 114 + $distancia);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);

            $this->SetY($y);
            $this->setX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 3, utf8Decode('AUTORIZÓ'), 0, 'C', false);

            $y = -20;
            $this->SetY($y);
            $this->setX($x);
            $this->MultiCell(
                57,
                4,
                utf8Decode($this->firma->getNombreAdministrativo()),
                'T',
                'C',
                false
            );

            $this->SetY($y);
            $this->setX($x + 57);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);

            $this->SetY($y);
            $this->setX($x + 57 + $distancia);
            $this->MultiCell(
                57,
                4,
                utf8Decode($this->firma->getNombrePlaneacion()),
                'T',
                'C',
                false
            );

            $this->SetY($y);
            $this->setX($x + 114 + $distancia);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);

            $this->SetY($y);
            $this->setX($x + 114 + 2 * $distancia);
            $this->MultiCell(
                57,
                4,
                utf8Decode($this->firma->getNombreTitular()),
                'T',
                'C',
                false
            );

            $y = -15;
            $this->SetY($y);
            $this->setX($x);
            $this->MultiCell(
                57,
                3,
                utf8Decode($this->firma->getCargoAdministrativo()),
                0,
                'C'
            );

            $this->SetY($y);
            $this->setX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);

            $this->SetY($y);
            $this->setX($x + 57 + $distancia);
            $this->MultiCell(
                57,
                3,
                utf8Decode($this->firma->getCargoPlaneacion()),
                0,
                'C'
            );

            $this->SetY($y);
            $this->setX($x + 114 + $distancia);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);

            $this->SetY($y);
            $this->setX($x + 114 + 2 * $distancia);
            $this->MultiCell(
                57,
                3,
                utf8Decode($this->firma->getCargoTitular()),
                0,
                'C'
            );
        }

        $this->SetFont('univiapro', '', 5);
        $y = -7;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(
            57,
            2,
            utf8Decode($this->usuario->getUsr_id() . " - " . $this->usuario->getUsuario() . " "),
            0,
            'L'
        );

        $this->SetY($y);
        $this->setX($x + 57);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);

        $this->SetY($y);
        $this->setX($x + 57 + $distancia);
        $this->MultiCell(
            57,
            2,
            utf8Decode('Página ' . $this->PageNo() . '/{nb}'),
            0,
            'C'
        );

        $this->SetY($y);
        $this->setX($x + 114 + $distancia);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);

        $this->SetY($y);
        $this->setX($x + 104 + 2 * $distancia);
        $this->MultiCell(63, 2, date("d/m/Y") . ' - ' . date("H:i:s"), 0, 'R');
    }
}
$pdf = new reportetabla('P', 'mm', 'letter');
$pdf->SetProtection(array("print", "annot-forms"), "", PDF_PASSWORD_OWNER_SCG);

$pdf->AliasNbPages();

$pdf->AddPage();
// -----------------------------------------------------------------------------------------
// Paso 5: Obtenemos los datos a imprimir... DE OTRA FUENTE... Es decir, en éste PHP NO estará
// el código para obtenerlo, sino en otro lugar (generalmente llamado en un #include )
// Lo haremos así porque si no éste código crecería muchísimo
// -----------------------------------------------------------------------------------------
switch ($pdf->men_id) {
        // Menú CUENTA
    case 1:

        break;
    case 2:

        break;
    case 4:
        switch ($pdf->opc_id) {
            case 2:

                $datos = contable(
                    $pdf->ur->getGpo_id(),
                    $pdf->ur->getUr_id(),
                    $pdf->ue_id,
                    '',
                    $pdf->periodo,
                    '',
                    $pdf->ecf,
                    $pdf->reporte,
                    false,
                    '',
                    '',
                    array(
                        "busqueda" => $pdf->busqueda,
                        "gur" => $pdf->busqueda['gur'],
                        "clc_id" => $pdf->busqueda['clc_id']
                    )
                );

                break;
            case 3:
                $datos = balanza(
                    $pdf->ur->getGpo_id(),
                    $pdf->ur->getUr_id(),
                    $pdf->ue_id,
                    '',
                    $pdf->periodo,
                    '',
                    $pdf->claveFinanciamiento,
                    $pdf->reporte,
                    $pdf->detalle,
                    '',
                    $pdf->niveles,
                    array(
                        "busqueda" => $pdf->busqueda,
                        "miles" => $pdf->miles,
                        "movimiento" => $pdf->movimiento,
                        "ejercicio" => $pdf->ejercicio
                    )
                );
                break;
            case 4:
                throw new Exception('Not implemented');
                /*
                $pdf->setOrientation('L');
                $datos = balanzaFondos(
                    $pdf->ur->getGpo_id(),
                    $pdf->ur->getUr_id(),
                    $pdf->ue_id,
                    '',
                    $pdf->periodo,
                    '',
                    $pdf->claveFinanciamiento,
                    $pdf->reporte,
                    $pdf->detalle,
                    '',
                    $pdf->niveles,
                    array(
                        "busqueda" => $pdf->busqueda,
                        "miles" => $pdf->miles,
                        "movimiento" => $pdf->movimiento,
                        "ejercicio" => $pdf->ejercicio
                    )
                );*/
                break;
            default:
                break;
        } //*/
        break;
    case 10:

        break;
    default:
        break;
}

$NUMREG = count($datos);
$columnas = count(array_values($datos)[0]) * 2;
$peso = calcularPeso($NUMREG, $columnas);
ini_set('memory_limit', ($peso * 20) . "M");
$pdf->Content($datos);
ob_end_flush();
// ob_end_clean();

$pdf->Output();
