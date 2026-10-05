<?php

declare (strict_types = 1);
session_start();
set_time_limit(0);
//librerias
// Obligatorio para que el reporteador funcione
require_once dirname(__FILE__) . '/../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../lib/TruncateCadena.php';
require_once dirname(__FILE__) . '/../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../../ent/AuxiliarUnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../ent/enc_ClaveFinanciamiento.php';
require_once dirname(__FILE__) . '/../../../ent/enc_Programatico.php';
require_once dirname(__FILE__) . '/../../../ent/Usuario.php';
require_once dirname(__FILE__) . '/../../../ent/scp/Clc.php';
require_once dirname(__FILE__) . '/../../../lib/helpers.php';
// Se incluyen los PHP necesarios en función de lo que se imprime
require_once dirname(__FILE__) . '/../../../lib/MesNombre.php';
require_once dirname(__FILE__) . '/../../../lib/redondear.php';

require_once dirname(__FILE__) . '/../../../ent/scg/cuentaContable/Rubro.php';
require_once dirname(__FILE__) . '/../../../ent/scg/cuentaContable/PlanCuentas.php';
require_once dirname(__FILE__) . '/../../../ent/scg/cuentaContable/CuentaCuarto.php';
require_once dirname(__FILE__) . '/../../../ent/scg/cuentaContable/CgSubcuenta.php';
require_once dirname(__FILE__) . '/../../../lib/scg/cuentaContable/cuentaContable.php';

// Por ejemplo, estas son librerías propias del reporta a imprimir
// en éste caso, se usan al imprimir catálogos, o la balanza de comprobación

require_once dirname(__FILE__) . '/../../../lib/scg/catalogo/catalogo.php';
require_once dirname(__FILE__) . '/../../../lib/scg/catalogo/mesContable.php';

require_once dirname(__FILE__) . '/../../../lib/scg/consulta/contable.php';

// Marcos: 19/feb/2018
// Para el preprocesado de la cuenta contable, que agregará puntos a nivel de mayor
require_once dirname(__FILE__) . '/../../../lib/constantes.php';

class reportetabla extends PDF
{

    public $reporte;
    public $ur;
    public $usr_id;
    public $usuario;
    public $ue;
    public $firmar;
    public $nombreReporte;
    public $nombreUR;
    public $longitud;
    public $claveFinanciamiento;
    public $detalle;
    public $busqueda;
    public $imagen;
    public $ancho;
    public $ue_id;
    public $sis_id;
    public $nivel;
    public $buscar;
    public $nombreUE;
    public $seccion;
    public $men_id;
    public $opc_id;
    public $ejercicio;
    public $mayor;
    public $desde;
    public $hasta;
    public $filtroUE;
    public $miles;
    public $logo;
    public $firma;
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter')
    {
        parent::__construct($orientation, $unit, $format);

        $this->reporte['tipo'] = "";

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
        } elseif (isset($_POST['check2024']) && $_POST['check2024'] == 1) {
            $this->ejercicio = "2024";
        } elseif (isset($_POST['check2023']) && $_POST['check2023'] == 1) {
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
        } elseif (isset($_POST['check2014']) && $_POST['check2014'] == 1) {
            $this->ejercicio = "2014";
        } elseif (isset($_POST['check2013']) && $_POST['check2013'] == 1) {
            $this->ejercicio = "2013";
        } elseif (isset($_POST['check2012']) && $_POST['check2012'] == 1) {
            $this->ejercicio = "2012";
        } else {
            $this->ejercicio = APP_EJERCICIO;
        }

        // -----------------------------------------------------------------------------------------
        // Paso 1: Aquí preparamos las opciones globales y variables que usaremos en la sección CONTENT()
        // -----------------------------------------------------------------------------------------
        switch ($this->men_id) {
            // Menú CUENTA
            case 1:
                // Parámetros específicos de cada opción
                switch ($this->opc_id) {
                    case 1: // CONAC
                        $this->seccion = trim(strtoupper(((isset($_POST['seccion'])
                            && trim($_POST['seccion']) != '') ? $_POST['seccion']
                            : "*** ERROR ***")));
                        break;
                    case 2: // Maestro
                        $this->seccion = trim(((isset($_POST['seccion']) && trim($_POST['seccion'])
                            != '') ? $_POST['seccion'] : "*** ERROR ***"));
                        $this->buscar = (isset($_POST['buscar']) ? $_POST['buscar']
                            : "");
                        $this->mayor = (isset($_POST['mayor']) ? $_POST['mayor']
                            : "");
                        $this->desde = (isset($_POST['desde']) ? $_POST['desde']
                            : "");
                        $this->hasta = (isset($_POST['hasta']) ? $_POST['hasta']
                            : "");
                        break;
                    case 3: // Plan de Cuentas
                        $this->seccion = trim(((isset($_POST['seccion']) && trim($_POST['seccion'])
                            != '') ? $_POST['seccion'] : "*** ERROR ***"));
                        $this->buscar = (isset($_POST['buscar']) ? $_POST['buscar']
                            : "");
                        $this->mayor = (isset($_POST['mayor']) ? $_POST['mayor']
                            : "");
                        $this->desde = (isset($_POST['desde']) ? $_POST['desde']
                            : "");
                        $this->hasta = (isset($_POST['hasta']) ? $_POST['hasta']
                            : "");
                        $this->filtroUE = (isset($_POST['filtroUE']) ? $_POST['filtroUE']
                            : "");
                        break;
                }
                break;
            case 2: // Menú catalogo
                // Parámetros que comparten todos los reportes de éste menú
                $this->setOrientation('P');
                $this->miles = false;
                $this->claveFinanciamiento = false;
                $this->detalle = false;
                switch ($this->opc_id) {
                    case 1:
                        if ($_SESSION[_S]['nvj_id'] > 2) {
                            $this->setOrientation('L');
                        } else {
                            $this->setOrientation('P');
                        }
                        break;
                }

                break;
            case 4:
                break;
            case 10:
                break;
            default:
                break;
        } //*/
    }

    public function Header()
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
                // Parámetros específicos de cada opción
                switch ($this->opc_id) {
                    case 1: // Catálogo CONAC
                        $this->nombreReporte = utf8Decode('CATÁLOGO CONAC. - ' . $this->seccion);
                        break;
                    case 2: // Catálogo Maestro
                        $this->nombreReporte = utf8Decode('CATÁLOGO MAESTRO');
                        break;
                    case 3: // Plan de Cuentas
                        $this->nombreReporte = utf8Decode('PLAN CUENTAS');
                        break;
                }
                break;
            //Menú Catálogo
            case 2:
                switch ($this->opc_id) {
                    case 1:
                        $this->nombreReporte = 'MES CONTABLE';
                        break;
                    case 2:
                        $this->nombreReporte = 'AGRUPACIÓN DE CUENTA CONTABLE';
                        break;
                    case 3:
                        $this->nombreReporte = 'TIPO DE DOCUMENTO FUENTE';
                        break;
                    case 4:
                        $this->nombreReporte = 'AGRUPACIÓN DE DOCUMENTO FUENTE';
                        break;
                    case 5:
                        $this->nombreReporte = 'AGRUPACIÓN DE PÓLIZA CONTABLE';
                        break;
                    case 6:
                        $this->nombreReporte = 'PREFIJO';
                        break;
                    case 7:
                        $this->nombreReporte = 'MOMENTO CONTABLE';
                        break;
                    case 8:
                        $this->nombreReporte = 'VINCULACIÓN COG';
                        break;
                    case 9:
                        $this->nombreReporte = 'VINCULACIÓN RETENCIÓN';
                        break;
                    case 10:
                        $this->nombreReporte = 'PROVEEDOR';
                        break;
                    default:
                        break;
                } //*/
                $this->nombreReporte = utf8Decode('CATÁLOGO - ' . $this->nombreReporte);
                break;
            case 4:

                break;
            case 10:

                break;
            default:
                break;
        } //*/
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

        $this->Image('../../../img/' . LOGO_COLOR, $this->logo, $iy, 74, 20, 'png');

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
        $this->nombreUR = TruncateCadena(utf8Decode(trim($this->nombreUR)), $this->longitud);
        $iyt += 4;
        $this->SetY($iy + $iyt);
        $this->Cell(5, 5, substr($this->nombreUR, 0, $this->longitud), 0, 0, 'L');
        if (strlen($this->nombreUR) > $this->longitud) {
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->Cell(5, 5,
                substr($this->nombreUR, $this->longitud, $this->longitud), 0, 0,
                'L');
        }
        if (strlen($this->nombreUR) > $this->longitud * 2) {
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->Cell(5, 5, substr($this->nombreUR, $this->longitud * 2, $this->longitud), 0, 0, 'L');
        }

        if ($this->ue_id) {
            $this->SetFont('univiapro', '', 7);
            $this->nombreUE = TruncateCadena(utf8Decode(trim("UE: " . $this->ue->getUe_id() . " - " . $this->ue->getNombre())), $this->longitud);
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->Cell(5, 5, substr($this->nombreUE, 0, $this->longitud), 0, 0,
                'L');

            if (strlen($this->nombreUE) > $this->longitud) {
                $iyt += 4;
                $this->SetY($iy + $iyt);
                $this->Cell(5, 5,
                    substr($this->nombreUE, $this->longitud, $this->longitud),
                    0, 0, 'L');
            }
            if (strlen($this->nombreUE) > $this->longitud * 2) {
                $iyt += 4;
                $this->SetY($iy + $iyt);
                $this->Cell(5, 5, substr($this->nombreUE, $this->longitud * 2),
                    0, 0, 'L');
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
            $this->Cell(180, 5,
                utf8Decode("(del rubro " . $rubro->getCuenta() . " - " . $rubro->getNombre() . ")"),
                0, 1, 'L');

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
                // Parámetros específicos de cada opción
                switch ($this->opc_id) {
                    case 1: // Catálogo CONAC
                        if (utf8Decode($this->seccion) == "SUBCUENTA") {
                            $this->SetWidths(array(30, 105, 10, 10, 10, 10));
                            $this->SetAligns(array('L', 'L'));
                            $this->Row(array(utf8Decode($this->seccion), 'NOMBRE',
                                'NAT.', 'EST.', 'TIPO', 'POS.'));
                        } else {
                            $this->SetWidths(array(30, 145));
                            $this->SetAligns(array('L', 'L'));
                            $this->Row(array(utf8Decode($this->seccion), 'NOMBRE'));
                        }
                        break;
                    case 2: // Catálogo Maestro
                        $this->SetWidths(array(40, 135));
                        $this->SetAligns(array('L', 'L'));
                        $this->Row(array('CUENTA', 'NOMBRE'));
                        break;
                    case 3: // Catálogo Plan Cuenta
                        $this->SetWidths(array(35, 108, 24, 8));
                        $this->SetAligns(array('L', 'L', 'L', 'L', 'L', 'L', 'L'));
                        $this->Row(array('CUENTA', 'NOMBRE', 'NATURALEZA', 'UE'));
                        break;
                }
                break;
            case 2:
                switch ($this->opc_id) {
                    case 1:
                        if ($_SESSION[_S]['nvj_id'] > 2) {
                            $this->SetWidths(array(7, 18, 15, 16, 15, 15, 15, 15,
                                15, 15, 21, 18, 20, 18, 16));
                            $this->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C',
                                'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
                            $this->Row(array('UR', 'APERTURA', 'ENERO', 'FEBRERO',
                                'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO',
                                'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE',
                                'CIERRE'));
                        } else {
                            $this->SetWidths(array(35, 35, 35, 35, 35, 35));
                            $this->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C'));
                            $this->Row(array('MES', 'FECHA APERTURA', 'FECHA CIERRE',
                                'ESTADO', 'USUARIO'));
                        }
                        break;
                    case 2:
                        $this->SetWidths(array(30, $this->ancho - 78, 48));
                        $this->SetAligns(array('L', 'L', 'R'));
                        $this->Row(array('CLAVE', 'NOMBRE', utf8Decode('DESCRIPCIÓN')));
                        break;
                    case 8:
                        $this->SetWidths(array(9, 40, 18, 40, 9, 18, 41));
                        $this->SetAligns(array('L', 'L', 'L', 'L', 'C', 'L', 'L'));
                        $this->Row(array('COG', 'NOMBRE', 'CUENTA', 'NOMBRE', 'TIPO',
                            'C CUENTA', 'NOMBRE'));
                        break;
                    case 9:
                        $this->SetWidths(array(20, 75, 20, $this->ancho - 115));
                        $this->SetAligns(array('L', 'L', 'L', 'L'));
                        $this->Row(array('RETENCION', 'NOMBRE', 'CUENTA', 'NOMBRE'));
                        break;
                    default:
                        $this->SetWidths(array(30, $this->ancho - 68, 38));
                        $this->SetAligns(array('L', 'L', 'R'));
                        $this->Row(array('CLAVE', 'NOMBRE', 'ALIAS'));
                        break;
                }
                break;
            case 4:
                break;
            case 10:
                break;
            default:
                break;
        }
        $this->ln(1);
    }

    public function Content($datos)
    {
        //realizamos la impresion de acuerdo al nivel(aqui no se procesa la informacion, el array debe estar procesado y ordenado
        $this->SetFont('univiapro', '', 7); ///tipo de letra para cada titulo
        $this->setDrawColor(170, 170, 170);
        $this->SetFillColor(224, 224, 224);
        $relleno = true;
        // -----------------------------------------------------------------------------------------
        // Paso 4: Imprimirmos los datos obtenidos en el paso 5... ésta es la impresión del reporte en sí
        // -----------------------------------------------------------------------------------------
        switch ($this->men_id) {
            // Menú CUENTA
            case 1:
                // Parámetros específicos de cada opción
                switch ($this->opc_id) {
                    case 1: // Catálogo CONAC
                        // aquí hay setWidths() y SetAligns() en los demás ejemplos, pero como ya lo hicimos
                        // en el paso 3, lo quitamos para checar qué pasa :D
                        foreach ($datos as $key => $dato) {
                            $this->SetRelleno($relleno);
                            if (utf8Decode($this->seccion) == "SUBCUENTA") {
                                $this->Row(array($dato['clave'], utf8Decode($dato['nombre']),
                                    $dato['nat'], $dato['str'], $dato['t'], $dato['p']));
                            } else {
                                $this->Row(array($dato['clave'], utf8Decode($dato['nombre'])));
                            }
                            $relleno = !$relleno;
                        }
                        break;
                    case 2: // Catálogo Maestro
                        foreach ($datos as $key => $dato) {
                            $this->SetRelleno($relleno);
                            $this->Row(array($dato['cuenta'], utf8Decode($dato['nombre'])));
                            $relleno = !$relleno;
                        }
                        break;
                    case 3: // Plan de Cuentas
                        foreach ($datos as $key => $dato) {
                            $this->SetRelleno($relleno);
                            $this->Row(array($dato['cuenta'], utf8Decode($dato['nombre']),
                                $dato['nat'],
                                (($dato['ue'] != "") ? str_pad($dato['ue'], 3,
                                    "0", STR_PAD_LEFT) : ""),
                            ));
                            $relleno = !$relleno;
                        }
                        break;
                }
                break;
            // Menú Catalogo
            case 2:
                switch ($this->opc_id) {
                    // Mes Contable
                    case 1:
                        if ($_SESSION[_S]['nvj_id'] > 2) {
                            $this->SetWidths(array(7, 18, 15, 16, 15, 15, 15, 15,
                                15, 15, 21, 18, 20, 18, 16));
                            $this->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C',
                                'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
                            foreach ($datos as $key => $row) {
                                $this->SetRelleno($relleno);
                                $this->Row(array($row['gpo_id'] . str_pad($row['ur_id'],
                                    2, "0", STR_PAD_LEFT), $row['ems_nombre0'],
                                    $row['ems_nombre1'], $row['ems_nombre2'], $row['ems_nombre3'],
                                    $row['ems_nombre4'], $row['ems_nombre5'], $row['ems_nombre6'],
                                    $row['ems_nombre7'], $row['ems_nombre8'], $row['ems_nombre9'],
                                    $row['ems_nombre10'], $row['ems_nombre11'], $row['ems_nombre12'],
                                    $row['ems_nombre13']));
                                $relleno = !$relleno;
                            }
                        } else {
                            $this->SetWidths(array(35, 35, 35, 35, 35, 35));
                            $this->SetAligns(array('L', 'C', 'C', 'C', 'C', 'L'));
                            foreach ($datos as $key => $row) {
                                $this->SetRelleno($relleno);
                                $this->Row(array($row['msc_nombre'], ($row['abierto']),
                                    ($row['cerrado']), $row['ems_nombre'], $row['usr_usuario']));
                                $relleno = !$relleno;
                            }
                        }
                        break;
                    case 2:
                        $this->SetWidths(array(30, $this->ancho - 78, 48));
                        $this->SetAligns(array('L', 'L', 'R'));
                        foreach ($datos as $key => $dato) {
                            $this->SetRelleno($relleno);
                            $this->Row(array($dato['clave'], utf8Decode($dato['nombre']), utf8Decode($dato['descrip'])));
                            $relleno = !$relleno;
                        }
                        break;
                    case 8:
                        $this->SetWidths(array(9, 40, 18, 40, 9, 18, 41));
                        $this->SetAligns(array('L', 'L', 'L', 'L', 'C', 'L', 'L'));
                        foreach ($datos as $key => $dato) {
                            $this->SetRelleno($relleno);
                            $this->Row(array($dato['pg_id'], utf8Decode($dato['pg_nombre']),
                                $dato['cog_cuenta'], utf8Decode($dato['cgsc_nombre']),
                                $dato['cog_tipo'], $dato['cog_contracuenta'], utf8Decode($dato['cgm_contranombre'])));
                            $relleno = !$relleno;
                        }
                        break;
                    case 9:
                        $this->SetWidths(array(20, 75, 20, $this->ancho - 115));
                        $this->SetAligns(array('L', 'L', 'L', 'L'));
                        foreach ($datos as $key => $dato) {
                            $this->SetRelleno($relleno);
                            $this->Row(array($dato['cgc_cuenta'], utf8Decode($dato['cgc_nombre']),
                                $dato['cta_id'], utf8Decode($dato['cta_nombre'])));
                            $relleno = !$relleno;
                        }
                        break;
                    default:
                        $this->SetWidths(array(30, $this->ancho - 68, 38));
                        $this->SetAligns(array('L', 'L', 'R'));
                        foreach ($datos as $key => $dato) {
                            $this->SetRelleno($relleno);
                            $this->Row(array($dato['clave'], utf8Decode($dato['nombre']),
                                $dato['alias']));
                            $relleno = !$relleno;
                        }
                        break;
                }
                break;
            case 4:
                break;
            case 10:
                break;
            default:
                break;
        }
    }

    public function Footer($lastPage = true)
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
            $this->MultiCell(57, 4, utf8Decode($this->firma->getNombreAdministrativo()), 'T', 'C',
                false);

            $this->SetY($y);
            $this->setX($x + 57);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);

            $this->SetY($y);
            $this->setX($x + 57 + $distancia);
            $this->MultiCell(57, 4, utf8Decode($this->firma->getNombrePlaneacion()), 'T', 'C',
                false);

            $this->SetY($y);
            $this->setX($x + 114 + $distancia);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);

            $this->SetY($y);
            $this->setX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 4, utf8Decode($this->firma->getNombreTitular()), 'T', 'C', false);

            $y = -15;
            $this->SetY($y);
            $this->setX($x);
            $this->MultiCell(57, 3, utf8Decode($this->firma->getCargoAdministrativo()), 0, 'C');

            $this->SetY($y);
            $this->setX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);

            $this->SetY($y);
            $this->setX($x + 57 + $distancia);
            $this->MultiCell(57, 3, utf8Decode($this->firma->getCargoPlaneacion()), 0, 'C');

            $this->SetY($y);
            $this->setX($x + 114 + $distancia);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);

            $this->SetY($y);
            $this->setX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 3, utf8Decode($this->firma->getCargoTitular()), 0, 'C');
        }

        $this->SetFont('univiapro', '', 5);
        $y = -7;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(57, 2, utf8Decode($this->usuario->getUsr_id() . " - " . $this->usuario->getUsuario() . " "),
            0, 'L');

        $this->SetY($y);
        $this->setX($x + 57);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);

        $this->SetY($y);
        $this->setX($x + 57 + $distancia);
        $this->MultiCell(57, 2, utf8Decode('Página ' . $this->PageNo() . '/{nb}'),
            0, 'C');

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
$pdf->buscar = "";
switch ($pdf->men_id) {
    // Menú CUENTA
    case 1:
        // Parámetros específicos de cada opción
        switch ($pdf->opc_id) {
            case 1: // Catálogo CONAC
                $datos = dameCatalogoCONAC($pdf->seccion, $pdf->buscar);
                break;
            case 2: // Catálogo CONAC
                $datos = dameCatalogoMaestro($pdf->seccion, $pdf->buscar,
                    $pdf->mayor, $pdf->desde, $pdf->hasta);
                break;
            case 3: // Plan de Cuentas
                $datos = damePlanDeCuentas($pdf->seccion, $pdf->buscar,
                    $pdf->mayor, $pdf->desde, $pdf->hasta, $pdf->filtroUE);
                break;
        }
        break;
    case 2:
        switch ($pdf->opc_id) {
            case 1:
                // Reporte Mes contable
                $datos = mesContable($pdf->ur->getGpo_id(),
                    $pdf->ur->getUr_id(), $pdf->nivel,
                    $pdf->usuario->getUsr_id());
                break;
            default:
                $datos = catalogo($pdf->ur->getGpo_id(), $pdf->ur->getUr_id(),
                    '', '', '',
                    array("sis_id" => $pdf->sis_id, "men_id" => $pdf->men_id, "opc_id" => $pdf->opc_id));
                break;
        }
        break;
    case 4:
        break;
    case 10:
        break;
    default:
        break;
}

$pdf->Content($datos);

$pdf->Output();
