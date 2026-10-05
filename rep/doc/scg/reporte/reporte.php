<?php
session_start();
//librerias
// Obligatorio para que el reporteador funcione
require_once dirname(__FILE__) . '/../../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/TruncateCadena.php';
require_once dirname(__FILE__) . '/../../../../ent/Usuario.php';
require_once dirname(__FILE__) . '/../../../../lib/Math.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';
// Se incluyen los PHP necesarios en función de lo que se imprime
require_once dirname(__FILE__) . '/../../../../lib/MesNombre.php';
require_once dirname(__FILE__) . '/../../../../lib/calculaPeso.php';
// Por ejemplo, estas son librerías propias del reporta a imprimir
require_once dirname(__FILE__) . '/../../../../lib/scg/reporte/validacionPoliza.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/reporte/diario.php';
// Librerias auxiliar
require_once dirname(__FILE__) . '/../../../../lib/scg/reporte/obtenerTituloCuentaAuxiliar.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/reporte/auxiliar.php';

class reportetabla extends PDF
{
    public $cuenta;
    public $pot_id;
    public $caja;
    public $deue;
    public $claveFinanciamientoclave;
    public $detalle;
    public $ur;
    public $usr_id;
    public $usuario;
    public $ue;
    public $firmar;
    public $firma;
    public $men_id;
    public $opc_id;
    public $periodo;
    public $claveFinanciamiento;
    public $reporte;
    public $nombreReporte;
    public $logo;
    public $nombreUR;
    public $longitud;
    public $ue_id;
    public $nombreUE;
    public $ancho;
    public $ejercicio;
    public $busqueda;
    public $imagen;
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
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
        }

        $this->sis_id = (isset($_POST['sis_id']) ? $_POST['sis_id'] : '');
        $this->men_id = (isset($_POST['men_id']) ? $_POST['men_id'] : '');
        $this->opc_id = (isset($_POST['opc_id']) ? $_POST['opc_id'] : '');

        if (isset($_POST['check2025']) && $_POST['check2025'] == 1) {
            $this->ejercicio = APP_EJERCICIO_ANTERIOR;
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
            //menu Reporte

            case 10:
                switch ($this->opc_id) {
                    case 1:
                        $this->busqueda = isset($_POST['radcta']) ? $_POST['radcta']
                        : -1;
                        $this->periodo['mesDesde'] = ((isset($_POST['comboMesDesde'])
                            && trim($_POST['comboMesDesde']) != '') ? $_POST['comboMesDesde']
                            : -1);
                        $this->periodo['mesHasta'] = ((isset($_POST['comboMesHasta'])
                            && trim($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta']
                            : -1);
                        $this->cuenta['cuentaDesde'] = (isset($_POST['comboDesde'])
                            ?
                            $_POST['comboDesde'] : "");
                        $this->cuenta['cuentaHasta'] = (isset($_POST['comboHasta'])
                            ?
                            $_POST['comboHasta'] : "");
                        $this->cuenta['cuenta'] = (isset($_POST['cta']) ?
                            $_POST['cta'] : "");

                         $this->cuenta['nombreCuenta'] = (isset($_POST['nombreCuenta']) ?
                            $_POST['nombreCuenta'] : "");

                        $this->cuenta['cta2rango'] = (isset($_POST['cta2']) ?
                            $_POST['cta2'] : "");

                        $this->pot_id['tipo'] = (isset($_POST['pot_id']) ?
                            $_POST['pot_id'] : "");

                        $this->caja['cjaconep'] = ((isset($_POST['ccm']) && $_POST['ccm']
                            != '') ?
                            $_POST['ccm'] : "");
                        $this->caja['cjaMmc'] = ((isset($_POST['mmc-id']) && count($_POST['mmc-id']))
                            ?
                            $_POST['mmc-id'] : array());
                        $this->caja['cjaOrden'] = (isset($_POST['ofe']) ?
                            $_POST['ofe'] : "");

                        $this->caja['cjaFe'] = (isset($_POST['fe']) ?
                            "on" : "");

                        $this->caja['cjaPc'] = (isset($_POST['pc_']) ?
                            $_POST['pc_'] : "");

                        $this->deue = ((isset($_POST['comboue']) && $_POST['comboue']
                            != "") ? $_POST['comboue'] : "");

                        $this->setOrientation('L');
                        $this->miles = false;
                        $this->claveFinanciamiento = isset($_POST['cfi']) ? $_POST['cfi']
                        : ""; // busqueda por clave de financiamiento
                        $this->claveFinanciamientoclave = isset($_POST['cfi_clave']) ? $_POST['cfi_clave']
                        : ""; // busqueda por clave de financiamiento

                        $this->claveFinanciamiento = ($this->claveFinanciamiento
                            != "") ? " AND cta_id = '" . $this->claveFinanciamiento . "'"
                        : "";
                        $this->cuenta['cuenta2'] = (isset($_POST['ctareg_id']) ?
                            $_POST['ctareg_id'] : "");
                        $this->cuenta['masivo'] = (isset($_POST['masivo']) ?
                            $_POST['masivo'] : 0);

                        $this->cuenta['busquedaRangoRegistro'] = '';
                        if ($this->busqueda == "N") {
                            $this->busqueda = "R";

                            $this->cuenta['busquedaRangoRegistro'] = 'N';
                            $this->cuenta['cuentaDesde'] = $this->cuenta['cuenta'];
                            $this->cuenta['cuentaHasta'] = $this->cuenta['cta2rango'];
                        }
                        if ($this->cuenta['masivo'] == 1) {
                            $this->busqueda = "C";
                            $this->cuenta['cuenta'] = $this->cuenta['cuenta2'];
                            $this->cuenta['cuentaDesde'] = '';
                            $this->cuenta['cuentaHasta'] = '';
                        }
                        break;
                    case 2:
                        $this->periodo['mesDesde'] = ((isset($_POST['comboMesDesde'])
                            && trim($_POST['comboMesDesde']) != '') ? $_POST['comboMesDesde']
                            : -1);
                        $this->periodo['mesHasta'] = ((isset($_POST['comboMesHasta'])
                            && trim($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta']
                            : -1);

                        $this->detalle = ((isset($_POST['checkDetalle']) && trim($_POST['checkDetalle'])
                            != '') ? true : false);

                        $this->pot_id['diario'] = ((isset($_POST['checkDiario'])
                            && trim($_POST['checkDiario']) != '') ? 'D' : '');
                        $this->pot_id['ingresos'] = ((isset($_POST['checkIngreso'])
                            && trim($_POST['checkIngreso']) != '') ? 'I' : '');
                        $this->pot_id['egresos'] = ((isset($_POST['checkEgreso'])
                            && trim($_POST['checkEgreso']) != '') ? 'E' : '');

                        $this->setOrientation('L');
                        $this->miles = false;
                        $this->claveFinanciamiento = false;
                        break;
                    case 10:
                        $this->periodo['mesDesde'] = ((isset($_POST['comboMesDesde'])
                            && trim($_POST['comboMesDesde']) != '') ? $_POST['comboMesDesde']
                            : -1);
                        $this->periodo['mesHasta'] = ((isset($_POST['comboMesHasta'])
                            && trim($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta']
                            : -1);

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

                        $this->setOrientation('P');
                        $this->miles = false;
                        $this->claveFinanciamiento = false;
                        $this->detalle = false;
                        break;
                    default:
                        break;
                }
                break;
            default:
                break;
        }
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
            case 10:
                switch ($this->opc_id) {
                    case 1:
                        $this->nombreReporte = 'LIBRO AUXILIAR CORRESPONDIENTE A LA CUENTA ';
                        $tituloCuenta = "";
                        if ($this->cuenta['cuentaDesde'] != "" && $this->cuenta['cuentaHasta']
                            != "") {
                            $tituloCuenta = $this->cuenta['cuentaDesde'] . ' A LA ' . $this->cuenta['cuentaHasta'];
                        } else {
                            $tituloCuenta = utf8Decode(obtenerTituloCuentaAuxiliar($this->ur->getGpo_id(),
                                $this->ur->getUr_id(), $this->deue,
                                $this->cuenta['cuenta'], $this->cuenta['nombreCuenta']));
                        }

                        if ($this->periodo['mesHasta'] == $this->periodo['mesDesde']) {
                            if ($this->periodo['mesHasta'] == 0) {
                                $this->nombreReporte .= $tituloCuenta . ' DEL MES DE APERTURA ' . $this->ejercicio . '.';
                            } else {
                                $this->nombreReporte .= $tituloCuenta . ' DEL MES DE ' . MesNombre($this->periodo['mesDesde']) . ' DEL ' . $this->ejercicio . '.';
                            }

                        } else {
                            $this->nombreReporte .= $tituloCuenta . ' DEL PERIODO DE ' . MesNombre($this->periodo['mesDesde']) . '-' . MesNombre($this->periodo['mesHasta']) . ' DEL ' . $this->ejercicio . '.';
                        }
                        break;
                    case 2:
                        $this->nombreReporte = 'REPORTE DIARIO';
                        if ($this->periodo['mesHasta'] == $this->periodo['mesDesde']) {
                            if ($this->periodo['mesHasta'] == 0) {
                                $this->nombreReporte .= ' DEL PERIODO DE APERTURA ' . $this->ejercicio . '.';
                            } else {
                                $this->nombreReporte .= ' DEL MES DE ' . MesNombre($this->periodo['mesDesde']) . ' DE ' . $this->ejercicio . '.';
                            }

                        } else {
                            $this->nombreReporte .= ' DEL PERIODO DE ' . MesNombre($this->periodo['mesDesde']) . '-' . MesNombre($this->periodo['mesHasta']) . ' DE ' . $this->ejercicio . '.';
                        }
                        break;
                    case 10:
                        $this->nombreReporte = 'REPORTE DE INCONSISTENCIAS';
                        break;
                    default:
                        break;
                }
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

        $this->Image('../../../../img/logoOaxaca-color-fblanco_v2.png', $this->logo, $iy, 74, 22, 'png');

        $iyt = 0;

        $this->SetFont('univiapro', 'B', 8);
        $this->SetY($iy + $iyt);
        $this->Cell(195, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');

        $iyt += 4;
        $this->SetY($iy + $iyt);
        $this->Cell(180, 5, 'CONTABILIDAD GUBERNAMENTAL '.APP_EJERCICIO, 0, 1, 'L');

        $this->SetFont('univiapro', '', 8);
        if ($_SESSION[_S]['nvj_id'] > 2) {
            if(isset($this->reporte['tipo'])){
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
            }else{
                $this->nombreUR = "DCG: " . $this->ur->getClave() . " - " . $this->ur->getNombre();
            }
        } else {
            if ($this->ur !== null) {
                $this->nombreUR = "UR: " . $this->ur->getClave() . " - " . $this->ur->getNombre();
            } else {
                $this->nombreUR = "";
            }
        }
        $this->nombreUR = TruncateCadena(utf8Decode(trim($this->nombreUR)),
            $this->longitud);
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
            $this->Cell(5, 5,
                substr($this->nombreUR, $this->longitud * 2, $this->longitud),
                0, 0, 'L');
        }

        if ($this->ue_id) {
            $this->SetFont('univiapro', '', 7);
            $this->nombreUE = TruncateCadena(utf8Decode(trim("UE: " . $this->ue->getUe_id() . " - " . $this->ue->getNombre())),
                $this->longitud);
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->Cell(5, 5, substr($this->nombreUE, 0, $this->longitud), 0, 0, 'L');

            if (strlen($this->nombreUE) > $this->longitud) {
                $iyt += 4;
                $this->SetY($iy + $iyt);
                $this->Cell(5, 5, substr($this->nombreUE, $this->longitud, $this->longitud), 0, 0, 'L');
            }
            if (strlen($this->nombreUE) > $this->longitud * 2) {
                $iyt += 4;
                $this->SetY($iy + $iyt);
                $this->Cell(5, 5, substr($this->nombreUE, $this->longitud * 2), 0, 0, 'L');
            }
            $this->SetFont('univiapro', 'B', 8);
        }

        $iyt += 4;
        $this->SetY($iy + $iyt);
        $this->MultiCell($this->ancho - 75, 5, $this->nombreReporte, 0, 1, 'L');

        $this->SetFont('univiapro', '', 7);
        if (isset($this->claveFinanciamiento) && $this->claveFinanciamiento) {
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->SetFont('univiapro', 'B', 7);
            $this->Cell(180, 5, $this->claveFinanciamientoclave, 0, 1, 'L');
        }

        if (isset($this->miles) && $this->miles) {
            $iyt += 4;
            $this->setY($iy + $iyt);
            $this->SetFont('univiapro', '', 6);
            $this->Cell(180, 5, "(en miles de pesos)", 0, 1, 'L');
        }

        if (strlen($this->nombreReporte) > 106) {
            $iyt += 5;
        }
        if (isset($this->pot_id['tipo'])) {
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->SetFont('univiapro', 'B', 7);
            $this->MultiCell(180, 5, utf8Decode('TIPO POLIZA ' . $this->pot_id['tipo']), 0, 1, 'L');
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
        $relleno = true;

        // -----------------------------------------------------------------------------------------
        // Paso 3: Configuramos el nombre y ancho de cada columna del reporte
        // El ancho total para PDF en modo "portrait" es 175
        // -----------------------------------------------------------------------------------------
        switch ($this->men_id) {

            case 10:
                switch ($this->opc_id) {
                    case 1:
                        $this->SetFont('univiapro', '', 6);
                        $this->SetWidths(array(12, 9, 13, 12, 20, 60, 18, 19, 20, 20, 20, 5, 10));
                        $this->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'R', 'R', 'R', 'C', 'C'));
                        $this->Row(array(utf8Decode('PÓLIZA'), 'UE', 'FECHA', 'TIPO DF',
                            'NUMERO DF', 'COMENTARIO', 'CVE.FINANC.', 'ID DF', 'DEBE',
                            'HABER', 'SALDO', 'M', 'EDO'));
                        $this->SetBorders(0);
                        $this->SetAligns(array('L', 'L', 'L', 'L', 'L', 'L', 'L', 'L', 'R', 'R', 'R', 'C', 'C'));
                        break;
                    case 2:
                        $this->SetWidths(array(15, 10, 25, $this->ancho - 130, 30,
                            30, 10, 10));
                        $this->SetAligns(array('L', 'C', 'L', 'L', 'R', 'R', 'C',
                            'C'));
                        $this->Row(array(utf8Decode('PÓLIZA'), 'UE', 'FECHA', 'CONCEPTO',
                            'DEBE', 'HABER', 'MC', 'EST'));
                        if ($this->detalle) {
                            $this->SetWidths(array(15, 35, $this->ancho - 110, 30,
                                30));
                            $this->SetAligns(array('C', 'L', 'L', 'R', 'R'));
                            $this->Row(array(utf8Decode('UE PÓLIZA'), 'CUENTA',
                                'NOMBRE', 'DEBE', 'HABER'));
                        }
                        break;
                    case 10:
                        $this->SetWidths(array(15, 15, $this->ancho - 105, 30, 30,
                            15));
                        $this->SetAligns(array('L', 'L', 'L', 'R', 'R', 'C'));
                        $this->Row(array('ERROR', utf8Decode('PÓLIZA'), 'ERROR',
                            'DEBE', 'HABER', 'MES'));
                        break;
                    default:
                        break;
                }
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

            case 10:
                switch ($this->opc_id) {
                    case 1:
                        foreach ($datos as $key => $dato) {
                            $inicial = $dato["inicial"]??"";
                            $saldo = 0;
                            $fill = false;
                            $nat = $inicial['nat_id']??'';
                            if ($nat == "D") {
                                $saldo = $inicial['pdx_debe'] - $inicial['pdx_haber'];
                            } elseif ($nat == "H") {
                                $saldo = $inicial['pdx_haber'] - $inicial['pdx_debe'];
                            }
                            $naturaleza = $inicial['nat_id']??'';

                            if (isset($dato["pdx_concepto"]) && $dato["pdx_concepto"]
                                == "TOTAL") {
                                $this->SetRelleno(false);
                                $this->SetAligns(array('L', 'L', 'L','L', 'L', 'C','L', 'L', 'R','R', 'L', 'L')); 
                                $this->Row(array('', '', '', '', '', '', '', 'Total',
                                    number_format($dato["pdx_debe"], 2), number_format($dato["pdx_haber"],
                                        2), '', '', ''));
                            } else {
                                $this->SetFont('univiapro', 'B', 7);
                                $this->Cell(20, 5, isset($inicial['cta_ue_id']) ? $inicial['cta_ue_id'] : '', 0, 0,
                                    'R', $fill);
                                $this->Cell(38, 5, isset($inicial['cta_cuenta']) ? $inicial['cta_cuenta'] : '', 0, 0,
                                    'R', $fill);
                                $this->Cell(8, 5, '', 0, 0, 'C', $fill);
                                $this->Cell(103, 5,
                                    utf8Decode(isset($inicial['cta_nombre']) ? $inicial['cta_nombre'] : '' ), '', 0,
                                    'L', $fill);
                                $this->Cell(17, 5, '', 0, 0, 'C', $fill);
                                $this->Cell(11, 5, '', 0, 0, 'C', $fill);
                                $this->Cell(25, 5, number_format($saldo, 2), 0,
                                    0, 'R', $fill);
                                $this->Cell(15, 5, '', 0, 0, 'R', $fill);
                                $this->ln();
                                $saldoTotal = Math::redondear($saldo, 2);
                                $saldo1 = $saldoTotal;
                                $this->SetFont('univiapro', '', 7);
                                $relleno = true;
                                $subtotalDebe = 0;
                                $subtotalHaber = 0;
                                if (isset($dato["movimiento"]) && is_array($dato["movimiento"])) {
                                    if(!empty($dato["movimiento"])){
                                        foreach ($dato["movimiento"] as $keyMovimiento => $movimiento) {
                                            $this->SetRelleno($relleno);
                                            if ($movimiento['epl_id'] != 'C' && $movimiento['epl_id']
                                                != 'H') { //son los diferentes casos para los detalles de la cuenta
                                                if ($naturaleza == "D" && $movimiento['nat_id']
                                                    == "D") {
                                                    $saldoTotal = Math::redondear($saldoTotal,
                                                        2) + Math::redondear($movimiento['pdx_debe'],
                                                        2) + Math::redondear($movimiento['pdx_haber'],
                                                        2);
                                                    $saldo1 = Math::redondear($saldoTotal,
                                                        2);
                                                } elseif ($naturaleza == "D" && $movimiento['nat_id']
                                                    == "H") {
                                                    $saldoTotal = Math::redondear($saldoTotal,
                                                        2) - Math::redondear($movimiento['pdx_debe'],
                                                        2) - Math::redondear($movimiento['pdx_haber'],
                                                        2);
                                                    $saldo1 = Math::redondear($saldoTotal,
                                                        2);
                                                } elseif ($naturaleza == "H" && $movimiento['nat_id']
                                                    == "D") {
                                                        $saldoTotal = Math::redondear($saldoTotal,
                                                        2) - Math::redondear($movimiento['pdx_debe'],
                                                        2) - Math::redondear($movimiento['pdx_haber'],
                                                        2);
                                                        $saldo1 = Math::redondear($saldoTotal,
                                                        2);
                                                } elseif ($naturaleza == "H" && $movimiento['nat_id']
                                                    == "H") {
                                                        $saldoTotal = Math::redondear($saldoTotal,
                                                        2) + Math::redondear($movimiento['pdx_debe'],
                                                        2) + Math::redondear($movimiento['pdx_haber'],
                                                        2);
                                                        $saldo1 = Math::redondear($saldoTotal,
                                                        2);
                                            }
                                        }

                                        if (isset($movimiento['pma_fecha']) && $movimiento['pma_fecha']
                                            != '') {
                                            $year = substr($movimiento['pma_fecha'],
                                                0, 3);
                                            $mes = substr($movimiento['pma_fecha'],
                                                5, 2);
                                            $dia = substr($movimiento['pma_fecha'],
                                                8, 2);
                                        }

                                        if ($movimiento['pdx_debe'] == 0) {
                                            $debe2 = '';
                                        } else {
                                            $debe2 = number_format($movimiento['pdx_debe'],
                                                2);
                                            $subtotalDebe += $movimiento['pdx_debe'];
                                        }

                                        if ($movimiento['pdx_haber'] == 0) {
                                            $haber2 = '';
                                        } else {
                                            $haber2 = number_format($movimiento['pdx_haber'],
                                                2);
                                            $subtotalHaber += $movimiento['pdx_haber'];
                                        }

                                        if ($this->caja['cjaconep'] != 'on' && strlen($movimiento['concepto'])
                                            > 37) {
                                            $docNumero = substr($movimiento['concepto'],
                                                0, 37);
                                            $docNumero .= '...';
                                            $this->SetBorders(0); //quitar
                                            $this->SetFont('univiapro', '', 7);
                                            $this->SetWidths(array(12, 9, 13, 12, 20,
                                                60, 18, 19, 20, 20, 20, 5, 10));
                                            $this->SetAligns(array('L', 'L', 'L', 'L',
                                                'L', 'L', 'L', 'L', 'R', 'R', 'R', 'C',
                                                'C'));
                                            $this->Row(array($movimiento['pot_id'] . '-' . $movimiento['prf_id'] . $movimiento['pma_id'],
                                                $movimiento['ue_id'], $dia . '-' . substr(MesNombre($mes),
                                                    0, 3), $movimiento['tdf_id'], $movimiento['pdx_referencia'],
                                                utf8Decode($docNumero), $movimiento['ecf_clave'],
                                                $movimiento['pdx_bdo_id_origen'], $debe2,
                                                $haber2, number_format($saldo1, 2), $movimiento['mmc_id'],
                                                $movimiento['epl_id']));
                                            $this->ln(1.6);
                                        } else { // Si no sobrepasa de 50 caracteres, lo imprimimos sin problemas
                                            $this->SetBorders(0);
                                            $this->SetFont('univiapro', '', 7);
                                            $this->SetWidths(array(12, 9, 13, 12, 20,
                                                60, 18, 19, 20, 20, 20, 5, 10));
                                            $this->SetAligns(array('L', 'L', 'L', 'L',
                                                'L', 'L', 'L', 'L', 'R', 'R', 'R', 'C',
                                                'C'));
                                            $this->SetAltoLinea(2.2);
                                            $this->Row(array($movimiento['pot_id'] . '-' . $movimiento['prf_id'] . $movimiento['pma_id'],
                                                $movimiento['ue_id'], $dia . '-' . substr(MesNombre($mes),
                                                    0, 3), $movimiento['tdf_id'], $movimiento['pdx_referencia'],
                                                utf8Decode($movimiento['concepto']),
                                                $movimiento['ecf_clave'], $movimiento['pdx_bdo_id_origen'],
                                                $debe2, $haber2, number_format($saldo1,
                                                    2), $movimiento['mmc_id'], $movimiento['epl_id']));
                                            $this->ln(1.6);
                                        }

                                        $relleno = (!$this->detalle ? !$relleno : true);
                                    }
                                    }
                                }
                                $this->SetRelleno(false);
                                $this->SetFont('univiapro', 'B', 7);
                                $this->SetWidths(array(12, 9, 13, 12, 20, 60, 18,
                                    19, 22, 22, 16, 5, 10));
                                $this->Row(array('', '', '', '', '', '', '', 'Subtotal',
                                    number_format($subtotalDebe, 2), number_format($subtotalHaber,
                                        2), '', '', ''));
                            }
                        }
                        break;
                    case 2:
                        $polizaAnterior = '..';
                        foreach ($datos as $key => $dato) {
                            if ($polizaAnterior != $dato['ue_id'] . '.' . $dato['msc_id'] . '.' . $dato['poliza']) {
                                $this->SetRelleno($relleno);
                                $this->SetWidths(array(15, 10, 25, $this->ancho - 130,
                                    30, 30, 10, 10));
                                $this->SetAligns(array('L', 'C', 'L', 'L', 'R', 'R',
                                    'C', 'C'));
                                $this->Row(array($dato['poliza'], $dato['ue_id'],
                                    $dato['pma_fecha'], utf8Decode($dato['pma_concepto']),
                                    number_format($dato['pma_debe'], 2), number_format($dato['pma_haber'],
                                        2), $dato['mmc_id'], $dato['epl_id']));
                                $polizaAnterior = $dato['ue_id'] . '.' . $dato['msc_id'] . '.' . $dato['poliza'];
                            }
                            if ($this->detalle) {
                                $this->SetRelleno(false);
                                $this->SetWidths(array(15, 35, $this->ancho - 110,
                                    30, 30));
                                $this->SetAligns(array('C', 'L', 'L', 'R', 'R'));
                                $this->Row(array($dato['cta_ue_id'], $dato['cta_cuenta'],
                                    utf8Decode($dato['cta_nombre']), number_format($dato['pdx_debe'],
                                        2), number_format($dato['pdx_haber'], 2)));
                            }
                            $relleno = (!$this->detalle ? !$relleno : true);
                        }
                        break;
                    case 10:
                        $this->SetWidths(array(15, 15, $this->ancho - 105, 30, 30,15));
                        $this->SetAligns(array('L', 'L', 'L', 'R', 'R', 'C'));
                        foreach ($datos as $key => $dato) {
                            $this->SetRelleno($relleno);
                            if ($dato['error'] == 3 || $dato['error'] == 4) {
                                $this->Row(array($dato['error'], $dato['pot_id'] . '-' . $dato['prf_id'] . $dato['pma_id'],
                                    utf8Decode($dato['mensaje']), number_format($dato['a'],
                                        2), number_format($dato['b'], 2), $dato['msc_id']));
                            } else {
                                $this->Row(array($dato['error'], $dato['pot_id'] . '-' . $dato['prf_id'] . $dato['pma_id'],
                                    utf8Decode($dato['mensaje']), $dato['a'], $dato['b'],
                                    $dato['msc_id']));
                            }
                            $relleno = !$relleno;
                        }
                        break;
                    default:
                        break;
                }
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
            $this->MultiCell(57, 4, utf8Decode($this->firma->getNombreAdministrativo()), 'T', 'C', false);

            $this->SetY($y);
            $this->setX($x + 57);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);

            $this->SetY($y);
            $this->setX($x + 57 + $distancia);
            $this->MultiCell(57, 4, utf8Decode($this->firma->getNombrePlaneacion()), 'T', 'C', false);

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
        $this->MultiCell(57, 2, utf8Decode($this->usuario->getUsr_id() . " - " . $this->usuario->getUsuario() . " "), 0, 'L');

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
// el código para obtenerlo, sino en otro lugar (generalmente llamado en un #include)
// Lo haremos así porque si no éste código crecería muchísimo
// -----------------------------------------------------------------------------------------
switch ($pdf->men_id) {

    case 10:
        switch ($pdf->opc_id) {
            case 1:
                $datos = auxiliar($pdf->cuenta['cuenta'], $pdf->ur->getGpo_id(),
                    $pdf->ur->getUr_id(), $pdf->pot_id['tipo'], $pdf->periodo,
                    $pdf->caja, $pdf->busqueda, $pdf->cuenta, $pdf->deue,
                    $pdf->claveFinanciamiento);
                $NUMREG = count($datos);
                $columnas = (13 * 2);
                $peso = calcularPeso($NUMREG, $columnas);
                if ($peso < 3) {
                    $peso = 4;
                }
                ini_set('memory_limit', ($peso * 20) . "M");
                break;
            case 2:
                $datos = diario($pdf->ur->getGpo_id(), $pdf->ur->getUr_id(), $pdf->ue_id, $pdf->pot_id, $pdf->periodo, $pdf->claveFinanciamiento, '', $pdf->detalle);
                $NUMREG = count($datos);
                $columnas = (13 * 2);
                $peso = calcularPeso($NUMREG, $columnas);
                if ($peso < 3) {
                    $peso = 4;
                }
                ini_set('memory_limit', ($peso * 2) . "M");
                break;
            case 10:
                $parametroExtra["limitarUE"] = true;
                $datos = validacionPoliza($pdf->ur->getGpo_id(), $pdf->ur->getUr_id(), $pdf->ue_id, '', $pdf->periodo, '', '', $pdf->reporte, '', '', '', $parametroExtra);
                $NUMREG = count($datos);
                $columnas = (13 * 2);
                $peso = calcularPeso($NUMREG, $columnas);
                if ($peso < 3) {
                    $peso = 4;
                }
                ini_set('memory_limit', ($peso * 2) . "M");
                break;
            default:
                break;
        }
        break;
    default:
        break;
}
//var_dump($datos);
$pdf->Content($datos);

$pdf->Output();
