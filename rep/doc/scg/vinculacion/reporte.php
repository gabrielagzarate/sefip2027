<?php
declare (strict_types = 1);
session_start();
set_time_limit(0);
//librerias
// Obligatorio para que el reporteador funcione
require_once dirname(__FILE__) . '/../../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/TruncateCadena.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';

require_once dirname(__FILE__) . '/../../../../ent/Usuario.php';

require_once dirname(__FILE__) . '/../../../../lib/MesNombre.php';
require_once dirname(__FILE__) . '/../../../../lib/redondear.php';

require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../../../lib/getDocumento.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/vinculacion/dameClcreporte.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/vinculacion/dameMinistradoReporte.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';

class reportetabla extends PDF
{
    public $bdo_id;
    public $rfc_id;
    public $referencia;
    public function __construct($orientation = 'P', $unit = 'mm',
        $format = 'letter', $obj = null) {
        parent::__construct($orientation, $unit, $format, $obj);

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();

        $this->ur->setUsr_id($_SESSION[_S]['usr_id']);
        $this->nivel  = $_SESSION[_S]['nvj_id'];
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

        if (isset($_POST['check2020']) && $_POST['check2020'] == 1) {
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
            $this->ejercicio = "2025";
        }

        // -----------------------------------------------------------------------------------------
        // Paso 1: Aquí preparamos las opciones globales y variables que usaremos en la sección CONTENT()
        // -----------------------------------------------------------------------------------------
        switch ($this->men_id) {
            // Menú Vinculacion
            case 5:

                // Parámetros específicos de cada opción
                switch ($this->opc_id) {
                    case 5: // Ministrado

                        $this->gpo_id = (isset($_POST['gpo_id']) ? $_POST['gpo_id']
                            : '');
                        $this->ur_id = (isset($_POST['ur_id']) ? $_POST['ur_id']
                            : '');
                        $this->clc_id = (isset($_POST['clc_id']) ? $_POST['clc_id']
                            : '');
                        $this->bdo_id = (isset($_POST['bdo_id']) ? $_POST['bdo_id']
                            : '');
                        $this->rfc_id = (isset($_POST['rfc_id']) ? $_POST['rfc_id']
                            : '');
                        $this->referencia = (isset($_POST['referencia']) ? $_POST['referencia']
                            : '');

                        break;

                    default:
                        break;
                }

            default:
                break;
        } //*/
    }

    public function Header()
    { //Encabezado del documento
        switch ($this->getOrientation()) {
            case 'P':
                $this->longitud = 60;
                $this->ancho    = 175;
                $this->imagen   = 195;
                $this->logo = 122;
                break;
            case 'L':
                $this->longitud = 95;
                $this->ancho    = 239;
                $this->imagen   = 259;
                $this->logo     = 186;
                break;
        }
        // -----------------------------------------------------------------------------------------
        // Paso 2: Configuramos el texto del encabezado
        // -----------------------------------------------------------------------------------------
        $encabezadoCuenta      = false;
        $encabezadoCuentaTexto = '';
        switch ($this->men_id) {
            // Menú vinculacion
            case 5:
                // Parámetros específicos de cada opción
                switch ($this->opc_id) {
                    case 5: // Catálogo ministrado
                        $this->nombreReporte = utf8Decode('MINISTRADO ');
                        break;
                    default:
                        break;
                }
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

        $this->Image('../../../../img/logoOaxaca-color-fblanco_v2.png',
            $this->logo, $iy, 74, 22, 'png');

        $iyt = 0;

        $this->SetFont('univiapro', 'B', 8);
        $this->SetY($iy + $iyt);
        $this->Cell(195, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');

        $iyt += 4;
        $this->SetY($iy + $iyt);
        $this->Cell(180, 5, 'CONTABILIDAD GUBERNAMENTAL ' . $_SESSION[_S]['ejercicio'], 0, 1, 'L');

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
        } //*/

        if ($this->men_id == 4 && $this->opc_id == 3 && isset($this->detalle) && $this->detalle) {
            $cuenta = str_replace(".", "", $this->busqueda['cuentaDesde']);
            $ger_id = substr($cuenta, 0, 1);
            $cgp_id = substr($cuenta, 1, 1);
            $rub_id = substr($cuenta, 2, 1);
            $rubro  = new Rubro();
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
            // Menú vinculacion
            case 5:
                // Parámetros específicos de cada opción
                switch ($this->opc_id) {
                    case 5: // Catálogo ministrado

                        /*  if (utf8Decode($this->seccion) == "SUBCUENTA") {
                          $this->SetWidths(array(30, 105, 10, 10, 10, 10));
                          $this->SetAligns(array('L', 'L'));
                          $this->Row(array(utf8Decode($this->seccion), 'NOMBRE',
                          'NAT.', 'EST.', 'TIPO', 'POS.'));
                          } else {
                          $this->SetWidths(array(30, 145));
                          $this->SetAligns(array('L', 'L'));
                          $this->Row(array(utf8Decode($this->seccion), 'NOMBRE'));
                          } */
                        break;
                    default:
                        break;
                }

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
            // Menú vinculacion
            case 5:
                // Parámetros específicos de cada opción
                switch ($this->opc_id) {
                    case 5: // Catálogo ministrador
                                // aquí hay setWidths() y SetAligns() en los demás ejemplos, pero como ya lo hicimos
                                // en el paso 3, lo quitamos para checar qué pasa :D

                        $this->SetFont('univiapro', 'B', 9); ///tipo de letra para cada titulo
                        $this->MultiCell(170, 3,
                            utf8Decode('Análisis del documento a generar la póliza del ministrado'),
                            0, 'C', false);
                        $this->ln(5);

                        $this->SetFont('univiapro', '', 6); ///tipo de letra para cada titulo
                        $this->SetWidths([23, 31, 26, 26, 26, 26, 18]);
                        $this->SetAligns(['L', 'L', 'L', 'R', 'R', 'R', 'R',
                            'R']);
                        $this->Row([utf8Decode('Proveedor'), utf8Decode('Referencia'),
                            utf8Decode('Concepto'), utf8Decode('Percepción Presupuestal'),
                            utf8Decode('Presupuestal 	Percepción Contable'), utf8Decode('Retención Contable'),
                            utf8Decode('Fecha')]);
                        $relleno = false;
                        $this->SetRelleno($relleno);

                        $this->Row([utf8Decode($datos->rfc_id . "-" . $datos->nombreProveedor),
                            utf8Decode($datos->tipoDocumentoFuente . "-" . $datos->referencia),
                            utf8Decode($datos->concepto),
                            number_format(floatval($datos->percepcionPresupuestal),
                                2),
                            number_format(floatval($datos->percepcionContable),
                                2),
                            number_format(floatval($datos->retencionContable), 2),
                            utf8Decode($datos->fechaDocumento)]);

                        $clcs   = $datos->clcs;
                        $unavez = 1;
                        foreach ($clcs as $clc) {
                            if ($unavez == 1) {
                                $this->ln(5);
                                $this->SetFont('univiapro', 'B', 9); ///tipo de letra para cada titulo
                                $this->MultiCell(170, 3,
                                    utf8Decode('CLC en la que esta el documento '),
                                    0, 'C', false);
                                $this->ln(2);
                            }
                            $unavez = 2;
                            $this->SetRelleno(true);
                            $this->SetFont('univiapro', '', 6); ///tipo de letra para cada titulo
                            $this->SetWidths([15, 25, 26, 26, 18, 18, 30, 18]);
                            $this->SetAligns(['L', 'L', 'L', 'L', 'L', 'L',
                                'L',
                                'R']);
                            $this->Row([utf8Decode('UR-CLC'), utf8Decode('Tipo-Subtipo'),
                                utf8Decode('Percepciones'), utf8Decode('Retenciones'),
                                utf8Decode('Envio'), utf8Decode('Rechazo'),
                                utf8Decode('CLABE'), utf8Decode('Cve Fin.')]);
                            $this->SetRelleno(false);

                            $this->SetFont('univiapro', '', 6); ///tipo de letra para cada titulo
                            $this->SetWidths([15, 25, 26, 26, 18, 18, 30, 18]);
                            $this->SetAligns(['L', 'L', 'L', 'L', 'L', 'L',
                                'L',
                                'R']);
                            $this->Row([
                                utf8Decode(($clc['id'] == "" || is_null($clc['id'])
                                    ? '' : $clc['id']))
                                , utf8Decode(($clc['tipo_Clc'] == "" || is_null($clc['tipo_Clc'])
                                    ? '' : $clc['tipo_Clc'])),
                                utf8Decode(($clc['percepcion'] == "" || is_null($clc['percepcion'])
                                    ? '' : $clc['percepcion']))
                                ,
                                utf8Decode(($clc['retencion'] == "" || is_null($clc['retencion'])
                                    ? '' : $clc['retencion'])),
                                utf8Decode(($clc['fechaEnvio'] == "" || is_null($clc['fechaEnvio'])
                                    ? '' : $clc['fechaEnvio'])),
                                utf8Decode(($clc['fechaRechazo'] == "" || is_null($clc['fechaRechazo'])
                                    ? '' : $clc['fechaRechazo'])),
                                utf8Decode(($clc['bur_id'] == "" || is_null($clc['bur_id'])
                                    ? '' : $clc['bur_id'])),
                                utf8Decode(($clc['ecf_clave'] == "" || is_null($clc['ecf_clave'])
                                    ? '' : $clc['ecf_clave'])),
                            ]);

                            $this->SetFont('univiapro', '', 6); ///tipo de letra para cada titulo
                            $this->SetWidths([30, 146]);
                            $this->SetAligns(['L', 'L']);

                            $this->Row([utf8Decode("Concepto"), utf8Decode(($clc['clc_concepto']
                                == "" || is_null($clc['clc_concepto']) ? '' : $clc['clc_concepto'])),
                            ]);
                            $this->ln(1);
                        }

                        $ministrado = $datos->ministrado;
                        $this->ln(5);
                        $this->SetFont('univiapro', 'B', 9); ///tipo de letra para cada titulo
                        $this->MultiCell(170, 3,
                            utf8Decode('Datos de la Ministración'), 0, 'C',
                            false);
                        $this->ln(2);

                        $this->SetRelleno(true);
                        $this->SetFont('univiapro', '', 6); ///tipo de letra para cada titulo
                        $this->SetWidths([20, 20, 25, 75, 18, 18]);
                        $this->SetAligns(['L', 'L', 'R', 'L', 'L', 'L']);
                        $this->Row([utf8Decode('Tipo Ministración'), utf8Decode('Referencia'),
                            utf8Decode('Importe'), utf8Decode('Concepto'),
                            utf8Decode('Fecha Ministrado'), utf8Decode('Fecha Cancelado')]);
                        $this->SetRelleno(false);
                        $this->Row([utf8Decode(($ministrado['TipoMinistración']
                            == "" || is_null($ministrado['TipoMinistración'])
                            ? '' : $ministrado['TipoMinistración'])),
                            utf8Decode(($ministrado['Referencia'] == "" || is_null($ministrado['Referencia'])
                                ? '' : $ministrado['Referencia'])),
                            utf8Decode(($ministrado['importe'] == "" || is_null($ministrado['importe'])
                                ? '' : $ministrado['importe'])),
                            utf8Decode(($ministrado['Concepto'] == "" || is_null($ministrado['Concepto'])
                                ? '' : $ministrado['Concepto'])),
                            utf8Decode(($ministrado['FechaMinistrado'] == "" || is_null($ministrado['FechaMinistrado'])
                                ? '' : $ministrado['FechaMinistrado'])),
                            utf8Decode(($ministrado['FechaCancelado'] == "" || is_null($ministrado['FechaCancelado'])
                                ? '' : $ministrado['FechaCancelado'])),
                        ]);
                        $this->SetFont('univiapro', '', 6); ///tipo de letra para cada titulo
                        $this->SetWidths([20, 30, 20, 30, 30, 46]);
                        $this->SetAligns(['L', 'L', 'L', 'L', 'L']);

                        $this->Row([utf8Decode("Banco"),
                            utf8Decode(($ministrado['Banco'] == "" || is_null($ministrado['Banco'])
                                ? '' : $ministrado['Banco'])),
                            utf8Decode("Cuenta"), utf8Decode(($ministrado['Cuenta']
                                == "" || is_null($ministrado['Cuenta']) ? '' : $ministrado['Cuenta'])),
                            utf8Decode("Beneficiario"), utf8Decode(($ministrado['Beneficiario']
                                == "" || is_null($ministrado['Beneficiario']) ? ''
                                : $ministrado['Beneficiario'])),
                        ]);
                        $this->ln(1);
                        break;
                    default:
                        break;
                }

            default:
                break;
        }
    }

    public function Footer($lastPage = true)
    {

        switch ($this->getOrientation()) {
            case 'P':
                $x         = 15;
                $distancia = 5;
                break;
            case 'L':
                $x         = 30;
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
            $this->MultiCell(57, 4,
                utf8Decode($this->firma->getNombreAdministrativo()), 'T', 'C',
                false);

            $this->SetY($y);
            $this->setX($x + 57);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);

            $this->SetY($y);
            $this->setX($x + 57 + $distancia);
            $this->MultiCell(57, 4,
                utf8Decode($this->firma->getNombrePlaneacion()), 'T', 'C',
                false);

            $this->SetY($y);
            $this->setX($x + 114 + $distancia);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);

            $this->SetY($y);
            $this->setX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 4,
                utf8Decode($this->firma->getNombreTitular()), 'T', 'C', false);

            $y = -15;
            $this->SetY($y);
            $this->setX($x);
            $this->MultiCell(57, 3,
                utf8Decode($this->firma->getCargoAdministrativo()), 0, 'C');

            $this->SetY($y);
            $this->setX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);

            $this->SetY($y);
            $this->setX($x + 57 + $distancia);
            $this->MultiCell(57, 3,
                utf8Decode($this->firma->getCargoPlaneacion()), 0, 'C');

            $this->SetY($y);
            $this->setX($x + 114 + $distancia);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);

            $this->SetY($y);
            $this->setX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 3,
                utf8Decode($this->firma->getCargoTitular()), 0, 'C');
        }

        $this->SetFont('univiapro', '', 5);
        $y = -7;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(57, 2,
            utf8Decode($this->usuario->getUsr_id() . " - " . $this->usuario->getUsuario() . " "),
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
$pdf->SetProtection(["print", "annot-forms"], "", PDF_PASSWORD_OWNER_SCG);

$pdf->AliasNbPages();
$pdf->AddPage();
// -----------------------------------------------------------------------------------------
// Paso 5: Obtenemos los datos a imprimir... DE OTRA FUENTE... Es decir, en éste PHP NO estará
// el código para obtenerlo, sino en otro lugar (generalmente llamado en un #include )
// Lo haremos así porque si no éste código crecería muchísimo
// -----------------------------------------------------------------------------------------
switch ($pdf->men_id) {
    // Menú vinculacion
    case 5:

        // Parámetros específicos de cada opción
        $clc_id_documento = (isset($clc_id_documento) ? $clc_id_documento : '');
        switch ($pdf->opc_id) {
            case 5: // Catálogo ministrado
                $datos = getDocumento($pdf->gpo_id, $pdf->ur_id, $pdf->bdo_id,
                    $pdf->rfc_id, $pdf->referencia,
                    ["ejercicio" => $_SESSION[_S]['ejercicio'], "tdf_id" => 'FACT', "mmc_id" => "T"]);

                $ministrado = [];
                $clcs       = [];
                if (empty($datos->detalleMinistrado)) {
                    $ministrado = dameMinistradoReporte($pdf->gpo_id,
                        $pdf->ur_id, $pdf->bdo_id,
                        ["ejercicio" => APP_EJERCICIO, "clc_id" => $pdf->clc_id]);

                    if ($pdf->clc_id != "") {
                        $clcs[$pdf->clc_id] = dameClcreporte($pdf->gpo_id,
                            $pdf->ur_id, $pdf->clc_id,
                            ["ejercicio" => APP_EJERCICIO, "bdo_id" => $pdf->bdo_id,
                                "todo"            => true]);
                    }
                } else {

                    foreach ($datos->detalleMinistrado as $key => $value) {
                        if ($clc_id_documento != '') {
                            if ($clc_id_documento == $value->clc_id) {
                                $clc_array[$value->clc_id] = $value->clc_id;
                            }
                        } else {
                            $clc_array[$value->clc_id] = $value->clc_id;
                        }

                        $ministrado = dameMinistradoReporte($pdf->gpo_id,
                            $pdf->ur_id, $pdf->bdo_id,
                            ["ejercicio" => APP_EJERCICIO, "clc_id" => $clc_id_documento]);
                    }

                    if (count($clc_array) != 0) {
                        foreach ($clc_array as $clc_id) {
                            $clcs[$clc_id] = dameClcreporte($pdf->gpo_id,
                                $pdf->ur_id, $clc_id,
                                ["ejercicio" => APP_EJERCICIO, "bdo_id" => $pdf->bdo_id,
                                    "todo"            => true]);
                        }
                    }
                }
                $datos->clcs       = $clcs;
                $datos->ministrado = $ministrado;
                break;
            default:
                break;
        }

    default:
        break;
}

$pdf->Content($datos);

$pdf->Output();
