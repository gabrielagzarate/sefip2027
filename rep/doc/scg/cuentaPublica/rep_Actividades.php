<?php

/*
Presenta en pantalla las opciones para imprimir el Estado de Actividades

Marcos: Pase y ajuste para el ejercicio 2020
26/ene/2020

OJO: Se controla C92 (sistema-menu-opcion) para diferenciar si es el menú de Cuenta Pública
para ver si se imprime sólo el ejercicio actual o también el anterior
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

ini_set('memory_limit', '20M');

set_time_limit(0);

define('Ingresos', 4);
define('Gastos', 5);

define('MENU_CUENTA_PUBLICA', 'C92');

/*ESTE REPORTE CORRESPONDE A LA OPCION DE ESTADO DE ACTIVIDADES.*/
require_once '../../../../ent/Entidad.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../lib/scg/conciliacion/consolidacion.php';
require_once '../../../../lib/compararCadenas.php';
require_once '../../../../lib/MesNombre.php';
require_once '../../../../lib/Math.php';
require_once '../../../lib/reportetabla.1.8.php';

if (!function_exists('u8d')) {
    function u8d($value)
    {
        return mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
    }
}

//Extendiendo la clase pdf para escribir el encabezado y pie de página
class reportetabla extends PDF
{
    private $longitud = 100;
    public $longitud2 = 90;
    public $ultBorde = 0;
    public $widths = '';

    public function __construct()
    {
        parent::__construct();

        $this->mesDesde = isset($_POST['comboMes']) ? $_POST['comboMes'] : -1; //Informacion de acuerdo al mes
        $this->mesHasta = isset($_POST['comboMesHasta']) ? $_POST['comboMesHasta'] : -1; //Informacion de acuerdo al mes
        $this->firmante1 = isset($_POST['firmante1']) ? $_POST['firmante1'] : '';
        $this->firmante2 = isset($_POST['firmante2']) ? $_POST['firmante2'] : '';
        $this->firmante3 = isset($_POST['firmante3']) ? $_POST['firmante3'] : '';
        $this->cargo1 = isset($_POST['cargo1']) ? $_POST['cargo1'] : '';
        $this->cargo2 = isset($_POST['cargo2']) ? $_POST['cargo2'] : '';
        $this->cargo3 = isset($_POST['cargo3']) ? $_POST['cargo3'] : '';
        $this->leyenda = isset($_POST['leyenda']) ? $_POST['leyenda'] : '';
        $this->consolidado = isset($_POST['consolidado']) ? $_POST['consolidado'] : '';
        $this->ceros = isset($_POST['ceros']) ? $_POST['ceros'] : '';
        $this->incCta = isset($_POST['incCta']) ? $_POST['incCta'] : '';
        $this->intLin = isset($_POST['intLin']) ? $_POST['intLin'] : '';
        $this->ue = isset($_POST['ue']) ? $_POST['ue'] : '';
        $this->numUe = isset($_POST['numUe']) ? $_POST['numUe'] : '';

        // Si no viene un número en el interlineado, ponemos el default
        if (!is_numeric($this->intLin)) {
            $this->intLin = 2.5;
        }

        $this->nivel[1] = isset($_POST['checkNivel1']) ? $_POST['checkNivel1'] : -1;
        $this->nivel[2] = isset($_POST['checkNivel2']) ? $_POST['checkNivel2'] : -1;
        $this->nivel[3] = isset($_POST['checkNivel3']) ? $_POST['checkNivel3'] : -1;
        $this->nivel[4] = isset($_POST['checkNivel4']) ? $_POST['checkNivel4'] : -1;
        $this->nivel[5] = isset($_POST['checkNivel5']) ? $_POST['checkNivel5'] : -1;
        $_POST['tipoReporte'] = ((isset($_POST['tipoReporte'])) ? $_POST['tipoReporte'] : 1);
    }

    public function Footer($lastPage = true)
    {
        $this->AddFont('univiapro', '');
        $this->AddFont('univiapro', 'B');
        $this->AddFont('univiapro');
        $this->AddFont('univiaprob');
        $row = $this->getDatos();
        $tmpb = $this->GetBorders();

        if ($this->leyenda == 1 || ($this->cargo1 != '' && $this->cargo2 != '' && $this->firmante1 != '' && $this->firmante2 != '')) {
            $this->SetY(-30);
            $this->Cell(190, 2, u8d(''), 0, 0, 'C');
            $this->Ln(5);
        } else {
            $this->SetY(-25);
        }

        if ($lastPage) {
            if ($this->leyenda == 1) {
                $this->SetFont('univiapro', '', 7);
                if ($_SESSION[_S]['reportes'] == MENU_CUENTA_PUBLICA && $this->mesDesde >= 1 && $this->mesHasta <= 3 && ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 122 && $_SESSION[_S]['nvj_id'] == 2)) {
                    $this->Cell(270, 2, u8d('Lugar y fecha de generación: Reyes Mantecón, Oaxaca'), 0, 0, 'C');
                } else {
                    $this->Cell(270, 2, u8d('Lugar y fecha de generación: Reyes Mantecón, Oaxaca; a ' . date('d') . ' de ' . MesNombre(date('m')) . ' de ' . date('Y')), 0, 0, 'C');
                }
                $this->Ln(2);
                $this->Cell(270, 2, u8d('Bajo protesta de decir verdad declaramos que los estados financieros y sus Notas son razonablemente correctos y responsabilidad del emisor.'), 0, 0, 'C');
                $this->Ln(1);
            }
            $this->SetFont('univiapro', 'B', 7);
            $y = $this->GetY();

            $this->Ln(2);
            $this->MultiAlignCell(97, 3, u8d($this->cargo1), 0, 0, 'C');
            $this->MultiAlignCell(77, 3, u8d($this->cargo2), 0, 0, 'C');
            $this->MultiAlignCell(97, 3, u8d($this->cargo3), 0, 1, 'C');
            $this->Ln(4);
            $this->SetFont('univiapro', '', 7);
            $this->MultiAlignCell(97, 3, u8d($this->firmante1), 0, 0, 'C');
            $this->MultiAlignCell(77, 3, u8d($this->firmante2), 0, 0, 'C');
            $this->MultiAlignCell(97, 3, u8d($this->firmante3), 0, 1, 'C');
        }
        $this->SetFont('univiapro', '', 7);
        //pie de página
        $this->SetY(-11);
        $this->SetFont('univiapro', '', 5);
        $this->Cell(275, 10, u8d('Página') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Marcos: 22/mar/2020
    // Intentaremos estandarizar la impresión del logo y las líneas de encabezado, para no estar inventando
    // el hilo negro en cada reporte.php
    // $orientacion: P para portrait, L para landscape
    public function drawHeadersAndLogo($orientacion, $linea1 = '', $linea2 = '', $linea3 = '', $linea4 = '', $linea5 = '', $linea6 = '', $linea7 = '')
    {
        $this->ancho = 287; // 297 que es el ancho del tamaño carta apaisada - 10
        $this->anchoImagen = 74;
        $this->altoImagen = 22;
        $this->logo = ($this->ancho - $this->anchoImagen); //213;
        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->SetMargins(20, 10, 20);
        $this->SetXY(20, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $nLineas = 0;

        // Dibujamos el logo
        $this->Image('../../../../img/logoOaxaca-color-fblanco_v2.png', $this->logo, $iy, $this->anchoImagen, $this->altoImagen, 'png');
        // Dibujamos las líneas de encabezado
        $this->SetFont('univiapro', 'B', 7);

        if (trim($linea1) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea1, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea2) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea2, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea3) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea3, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea4) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea4, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea5) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea5, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea6) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea6, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea7) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea7, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        // Dejamos un espacio para la siguiente sección
        if ($nlineas <= 4) {
            $this->SetY($this->GetY() + 2);
            $this->ln(5);
        }
        // Límite para asegurar la buena visilibidad del reporte y el correcto aprovechamiento de pantalla
        // Si sale sobreencimado con los encabezados de columna, tal vez haya que revisar las líneas del encabezado
        $this->setY(35);
    }

    public function Header()
    {
        /*SE IMPRIME EL ENCABEZADO DEL REPORTE.*/
        $this->AddFont('univiapro', '');
        $this->AddFont('univiapro', 'B');
        $this->AddFont('univiapro');
        $this->AddFont('univiaprob');
        $this->ejercicio = '';
        $this->gpo_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['s24-gpo'];
        $this->ur_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['s24-ur'];

        if ($this->ejercicio == '') {
            $this->ur = new UnidadResponsable();
            $this->ur->setGpo_id($this->gpo_id);
            $this->ur->setUr_id($this->ur_id);
            $this->ur->select();
        }

        $row = $this->getDatos();

        if ($_SESSION[_S]['reportes'] != MENU_CUENTA_PUBLICA) {
            $linea1 = u8d('Cuenta de la Hacienda Pública Estatal ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
        }
        if ($_SESSION[_S]['nvj_id'] > 2) {
            $linea2 = u8d('Secretaría de Finanzas del Gobierno del Estado de Oaxaca');
        } else {
            $linea2 = u8d('Gobierno del Estado de Oaxaca');
        }
        if ($_SESSION[_S]['nvj_id'] > 2) {
            switch ($_POST['tipoReporte']) {
                case 2: // Todas las URs de la carga de trabajo
                    $texto = u8d('Dirección de Contabilidad Gubernamental');
                    break;
                case 3: // Determinar qué incluir y qué excluir
                    $texto = isset($_POST['nombreEspecial']) ? $_POST['nombreEspecial'] : 'ERROR DE PROGRAMA. NO EXISTE ENCABEZADO';
                    break;
                default:
                    if ($this->ejercicio == '') {
                        $texto = u8d('Dirección de Contabilidad Gubernamental: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre());
                    } else {
                        $texto = u8d('Dirección de Contabilidad Gubernamental: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . $this->ur['ur_nombre']);
                    }
                    break;
            }
        } else {
            if ($this->ejercicio == '') {
                if (mb_detect_encoding($this->ur->getNombre(), 'auto') == 'UTF-8') {
                    $texto = ('UR: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre());
                } else {
                    $texto = u8d('UR: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre());
                }
            } else {
                $texto = u8d('UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . $this->ur['ur_nombre']);
            }
        }

        $texto2 = '';
        if ($this->ue != '' && $this->numUe > 1) {
            $ejecutora = new AccesoDatos();
            $ejecutora->conectar();
            $ejecutora->setSql('
                SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = ' . $this->gpo_id . ' AND ur_id = ' . $this->ur_id . ' AND ue_id = ' . $this->ue . ';
            ');
            $ejecutora->consultar();
            $ejecutora->desconectar();
            $ue_id = $ejecutora->extraer();

            if (mb_detect_encoding($ue_id['ue_nombre'], 'auto') == 'UTF-8') {
                $texto2 = 'UE: ' . $ue_id['ue_id'] . ' - ' . ($ue_id['ue_nombre']) . '';
            } else {
                $texto2 = 'UE: ' . $ue_id['ue_id'] . ' - ' . u8d($ue_id['ue_nombre']) . '';
            }
        }

        // No entiendo la lógica de ésta sección... de momento se dejará tal y como está el código
        if ($_POST['tipoReporte'] != 3 && $_SESSION[_S]['nvj_id'] > 2) {
            $linea3 = $texto;
        }

        if ($this->ue != '' && $_SESSION[_S]['nvj_id'] > 2) {
            $linea4 = u8d($texto2);
        }

        if ($_POST['tipoReporte'] == 3 || $_SESSION[_S]['nvj_id'] <= 2) {
            $linea3 = u8d($texto);
            if ($this->ue != '' && $_SESSION[_S]['nvj_id'] <= 2 && $this->numUe > 1) {
                $linea4 = u8d($texto2);
            }
        }

        $linea5 = u8d('Estado de Actividades');
        $mesDesde = (isset($_POST['comboMes']) && $_POST['comboMes'] != '') ? $_POST['comboMes'] : '';

        $sqlMes = ' select msc_nombre, msc_dias from cat_MesContable where msc_id=' . $mesDesde . ';';
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sqlMes);
        $con->consultar();
        $con->desconectar();
        $rowMes = $con->extraer();

        $mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta'] != '') ? $_POST['comboMesHasta'] : '';

        $sqlMes2 = ' select msc_nombre, msc_dias from cat_MesContable where msc_id=' . $mesHasta . ';';
        $con2 = new AccesoDatos();
        $con2->conectar();
        $con2->setSql($sqlMes2);
        $con2->consultar();
        $con2->desconectar();
        $rowMes2 = $con2->extraer();

        // Marcos: 15/feb/2019
        // C92 es sistemaMenuOpcion... donde es Estados Financieros->Estado de Actividades
        //     para diferenciarlo del reporte que sale del menú de Cuenta Pública
        // Se cambiará el texto cuando sea el mes de cierre
        // ticket #598470

        if ($_SESSION[_S]['reportes'] == MENU_CUENTA_PUBLICA) {
            if ($this->mesDesde == 0 and $this->mesHasta == 0) {
                $linea6 = u8d('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                // Marcos: 15/feb/2019 adicionamos esta condicion
            } elseif ($this->mesDesde == 13 and $this->mesHasta == 13) {
                $linea6 = u8d('al cierre del ejercicio ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
            } elseif ($this->mesHasta == $this->mesDesde) {
                $linea6 = u8d('Del 1° al ' . $rowMes['msc_dias'] . ' de ' . strtolower($rowMes['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
            } else {
                if ($this->mesDesde == 0) {
                    $linea6 = u8d('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                } elseif ($this->mesHasta == 13) {
                    $linea6 = u8d('Del 1° de ' . strtolower($rowMes['msc_nombre']) . ' al cierre del ejercicio ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                } else {
                    $linea6 = u8d('Del 1° de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                }
            }
        } else {
            if ($this->mesDesde == 0 and $this->mesHasta == 0) {
                $linea6 = u8d('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
            } elseif ($this->mesHasta == $this->mesDesde) {
                $linea6 = u8d('Del 1° al ' . $rowMes['msc_dias'] . ' de ' . strtolower($rowMes['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
            } else {
                if ($this->mesDesde == 0) {
                    $linea6 = u8d('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                } else {
                    $linea6 = u8d('Del 1° de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                }
            }
        }

        if (isset($_POST['MilPes'])) {
            $linea7 = '(Miles)';
        } else {
            $linea7 = '(Pesos)';
        }

        // Dibujamos el logo y las líneas del encabezado
        $this->drawHeadersAndLogo('L', $linea1, $linea2, $linea3, $linea4, $linea5, $linea6, $linea7);
        $this->setRelleno('si');
        $this->SetFont('univiapro', 'B', 7);
        $this->SetTextColor(0, 0, 0);
        $this->setX(10);
        $this->setDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->setAltoLinea(3);
        $this->SetBorders(1);

        if ($_SESSION[_S]['reportes'] == MENU_CUENTA_PUBLICA) {
            $this->SetWidths([101.5, 33.5, 4, 101.5, 33.5]);
            $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
            $this->Row([
                u8d('CONCEPTO'),
                u8d('EJERCICIO FISCAL ' . substr($_SESSION[_S]['ejercicio'], 0, 4)),
                '',
                u8d('CONCEPTO'),
                u8d('EJERCICIO FISCAL ' . substr($_SESSION[_S]['ejercicio'], 0, 4)),
            ]);
        } else {
            $this->SetWidths([68, 33.5, 33.5, 4, 68, 33.5, 33.5]);
            $this->SetAligns(['L', 'R', 'R', 'C', 'L', 'R', 'R']);
            $this->Row([
                u8d('CONCEPTO'),
                u8d('EJERCICIO FISCAL ' . substr($_SESSION[_S]['ejercicio'], 0, 4)),
                u8d('EJERCICIO FISCAL ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4)),
                '',
                u8d('CONCEPTO'),
                u8d('EJERCICIO FISCAL ' . substr($_SESSION[_S]['ejercicio'], 0, 4)),
                u8d('EJERCICIO FISCAL ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4)),
            ]);
        }
        $this->ln(2);
        $this->setRelleno('no');
        $this->SetBorders(0);
        $this->SetFont('univiapro', 'B', 9);
    }
}

// ESTADO DE ACTIVIDADES
// ----------------------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------

$mesDesde = (isset($_POST['comboMes']) != '') ? $_POST['comboMes'] : -1;
$mesHasta = (isset($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta'] : -1;
$tipoReporte = (isset($_POST['tipoReporte']) != '') ? $_POST['tipoReporte'] : 1;
$MilPesos = (isset($_POST['MilPes']) != '') ? 1000 : 1;
$grupos = isset($_POST['grupos']) ? $_POST['grupos'] : '';
$excluir = isset($_POST['excluir']) ? $_POST['excluir'] : '';
$ue = isset($_POST['ue']) ? $_POST['ue'] : '';

$pdf = new Reportetabla('L', 'mm', 'Letter');
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_SCG);
if (!($pdf->consolidado == 1)) {
    $desde = $pdf->mesDesde;
    $hasta = $pdf->mesHasta;
} else {
    $desde = $mesHasta + 1;
    $hasta = -1;
}

list($ingresos) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 4, $tipoReporte, '', $MilPesos, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
list($gastos) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 5, $tipoReporte, '', $MilPesos, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
list($ingresos2020) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 4, $tipoReporte, 2020, $MilPesos, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
list($gastos2020) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 5, $tipoReporte, 2020, $MilPesos, $grupos, $excluir, $pdf->ceros, $pdf->incCta);

//cuerpo de la tabla
$pdf->setOrientation('L');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->AddFont('univiapro', '');
$pdf->AddFont('univiapro', 'B');

$pdf->SetFont('univiapro', '', 9);
if ($_SESSION[_S]['reportes'] == MENU_CUENTA_PUBLICA) {
    $pdf->SetWidths([101.5, 33.5, 4, 101.5, 33.5]);
    $pdf->SetAligns(['L', 'R', 'C', 'L', 'R']);
} else {
    $pdf->SetWidths([68, 33.5, 33.5, 4, 68, 33.5, 33.5]);
    $pdf->SetAligns(['L', 'R', 'R', 'C', 'L', 'R', 'R']);
}

$cuentasTotalesI = [43998];
$cuentasTotalesG = [57998, 57999];

$cuentaIn = '';
$nivelIn = '';
$importeIn = 0;
$cuentaGa = '';
$nivelGa = '';
$importeGa = 0;
$totalIn = 0;
$totalGa = 0;

$ing = []; //array para informacion de ingresos  2020
$ga = []; // array para informacion de gastos 2020.

$ing2 = []; //array para informacion de ingresos 2019
$ga2 = []; // array para informacion de gastos 2019.

if ($ingresos || $gastos) {
    foreach ($ingresos as $key => $valor) {
        if ($valor['nivel'] >= 1) {
            $cuentaIn = $valor['cuenta'];
            $nivelIn = u8d($valor['nombre']);
            if ($valor['naturalezaCuenta'] == 'D') {
                $importeIn = ($valor['debe'] - $valor['haber']) / $MilPesos;
            } else {
                $importeIn = ($valor['haber'] - $valor['debe']) / $MilPesos;
            }
        } else {
            $cuentaIn = $valor['cuenta'];
            $nivelIn = u8d($valor['nombre']);
        }
        if ($valor['nivel'] == 1) {
            $totalIn = $totalIn + $importeIn;
            $ing[$key]['cuenta'] = $cuentaIn;
            $ing[$key]['nivel'] = $nivelIn;
            $ing[$key]['importe'] = $importeIn;
            $ing[$key]['nivelCuenta'] = $valor['nivel'];
        } else {
            if ($pdf->nivel[$valor['nivel']] == 1 || in_array($valor['cuenta'], $cuentasTotalesI)) {
                $ing[$key]['cuenta'] = $cuentaIn;
                $ing[$key]['nivel'] = $nivelIn;
                $ing[$key]['importe'] = $importeIn;
                $ing[$key]['nivelCuenta'] = $valor['nivel'];
            }
        }
    }
    $ing[43998]['cuenta'] = 43998;
    $ing[43998]['nivel'] = 'TOTAL DE INGRESOS Y OTROS BENEFICIOS';
    $ing[43998]['importe'] = $totalIn;
    $ing[43998]['nivelCuenta'] = 5;

    foreach ($gastos as $key => $valor) {
        if ($valor['nivel'] >= 1) {
            $cuentaGa = $valor['cuenta'];
            $nivelGa = u8d($valor['nombre']);

            if ($valor['naturalezaCuenta'] == 'D') {
                $importeGa = ($valor['debe'] - $valor['haber']) / $MilPesos;
            } else {
                $importeGa = ($valor['haber'] - $valor['debe']) / $MilPesos;
            }
        } else {
            $cuentaGa = $valor['cuenta'];
            $nivelGa = u8d($valor['nombre']);
        }
        if ($valor['nivel'] == 1) {
            $totalGa = $totalGa + $importeGa;
            $ga[$key]['cuenta'] = $cuentaGa;
            $ga[$key]['nivel'] = $nivelGa;
            $ga[$key]['importe'] = $importeGa;
            $ga[$key]['nivelCuenta'] = $valor['nivel'];
        } else {
            if ($pdf->nivel[$valor['nivel']] == 1 || in_array($valor['cuenta'], $cuentasTotalesG)) {
                $ga[$key]['cuenta'] = $cuentaGa;
                $ga[$key]['nivel'] = $nivelGa;
                $ga[$key]['importe'] = $importeGa;
                $ga[$key]['nivelCuenta'] = $valor['nivel'];
            }
        }
    }

    $ga[57998]['cuenta'] = 57998;
    $ga[57998]['nivel'] = 'TOTAL DE GASTOS Y OTRAS PERDIDAS';
    $ga[57998]['importe'] = $totalGa;
    $ga[57998]['nivelCuenta'] = 5;
    if (($totalIn - $totalGa) >= 0) {
        $ga[57999]['cuenta'] = 57999;
        $ga[57999]['nivel'] = 'AHORRO NETO DEL EJERCICIO';
        $ga[57999]['importe'] = $totalIn - $totalGa;
        $ga[57999]['nivelCuenta'] = 5;
    } else {
        $ga[57999]['cuenta'] = 57999;
        $ga[57999]['nivel'] = 'DESAHORRO NETO DEL EJERCICIO';
        $ga[57999]['importe'] = $totalIn - $totalGa;
        $ga[57999]['nivelCuenta'] = 5;
    }
}

$cuentaIn = '';
$nivelIn = '';
$importeIn = 0;
$cuentaGa = '';
$nivelGa = '';
$importeGa = 0;
$totalIn = 0;
$totalGa = 0;

if ($ingresos2020 || $gastos2020) {
    foreach ($ingresos2020 as $key => $valor) {
        if ($valor['nivel'] >= 1) {
            $cuentaIn = $valor['cuenta'];
            $nivelIn = u8d($valor['nombre']);
            if ($valor['naturalezaCuenta'] == 'D') {
                $importeIn = ($valor['debe'] - $valor['haber']) / $MilPesos;
            } else {
                $importeIn = ($valor['haber'] - $valor['debe']) / $MilPesos;
            }
        } else {
            $cuentaIn = $valor['cuenta'];
            $nivelIn = u8d($valor['nombre']);
        }
        if ($valor['nivel'] == 1) {
            $totalIn = $totalIn + $importeIn;
            $ing2[$key]['cuenta'] = $cuentaIn;
            $ing2[$key]['nivel'] = $nivelIn;
            $ing2[$key]['importe'] = $importeIn;
            $ing2[$key]['nivelCuenta'] = $valor['nivel'];
        } else {
            if ($pdf->nivel[$valor['nivel']] == 1 || in_array($valor['cuenta'], $cuentasTotalesI)) {
                $ing2[$key]['cuenta'] = $cuentaIn;
                $ing2[$key]['nivel'] = $nivelIn;
                $ing2[$key]['importe'] = $importeIn;
                $ing2[$key]['nivelCuenta'] = $valor['nivel'];
            }
        }
    }
    $ing2[43998]['cuenta'] = 43998;
    $ing2[43998]['nivel'] = 'TOTAL DE INGRESOS Y OTROS BENEFICIOS';
    $ing2[43998]['importe'] = $totalIn;
    $ing2[43998]['nivelCuenta'] = 5;

    foreach ($gastos2020 as $key => $valor) {
        if ($valor['nivel'] >= 1) {
            $cuentaGa = $valor['cuenta'];
            $nivelGa = u8d($valor['nombre']);
            if ($valor['naturalezaCuenta'] == 'D') {
                $importeGa = ($valor['debe'] - $valor['haber']) / $MilPesos;
            } else {
                $importeGa = ($valor['haber'] - $valor['debe']) / $MilPesos;
            }
        } else {
            $cuentaGa = $valor['cuenta'];
            $nivelGa = u8d($valor['nombre']);
        }
        if ($valor['nivel'] == 1) {
            $totalGa = $totalGa + $importeGa;
            $ga2[$key]['cuenta'] = $cuentaGa;
            $ga2[$key]['nivel'] = $nivelGa;
            $ga2[$key]['importe'] = $importeGa;
            $ga2[$key]['nivelCuenta'] = $valor['nivel'];
        } else {
            if ($pdf->nivel[$valor['nivel']] == 1 || in_array($valor['cuenta'], $cuentasTotalesG)) {
                $ga2[$key]['cuenta'] = $cuentaGa;
                $ga2[$key]['nivel'] = $nivelGa;
                $ga2[$key]['importe'] = $importeGa;
                $ga2[$key]['nivelCuenta'] = $valor['nivel'];
            }
        }
    }

    $ga2[57998]['cuenta'] = 57998;
    $ga2[57998]['nivel'] = 'TOTAL DE GASTOS Y OTRAS PERDIDAS';
    $ga2[57998]['importe'] = $totalGa;
    $ga2[57998]['nivelCuenta'] = 5;

    if (($totalIn - $totalGa) >= 0) {
        $ga2[57999]['cuenta'] = 57999;
        $ga2[57999]['nivel'] = 'AHORRO NETO DEL EJERCICIO';
        $ga2[57999]['importe'] = $totalIn - $totalGa;
        $ga2[57999]['nivelCuenta'] = 5;
    } else {
        $ga2[57999]['cuenta'] = '';
        $ga2[57999]['nivel'] = 'DESAHORRO NETO DEL EJERCICIO';
        $ga2[57999]['importe'] = $totalIn - $totalGa;
        $ga2[57999]['nivelCuenta'] = 5;
    }
}

$keyIngresos2022 = key($ing);
$keyIngresos2020 = key($ing2);
$finalIng = [];

while ($keyIngresos2022 || $keyIngresos2020) {

    if (CompararCadenas((string) $keyIngresos2022, (string) $keyIngresos2020) == 0) {
        $finalIng[$keyIngresos2022]['cuenta'] = $ing[$keyIngresos2022]['cuenta'];
        $finalIng[$keyIngresos2022]['nivel'] = $ing[$keyIngresos2022]['nivel'];
        $finalIng[$keyIngresos2022]['nivelCuenta'] = $ing[$keyIngresos2022]['nivelCuenta'];
        $finalIng[$keyIngresos2022]['importe2020'] = $ing[$keyIngresos2022]['importe'];
        $finalIng[$keyIngresos2022]['importe2019'] = $ing2[$keyIngresos2020]['importe'];

        next($ing);
        $keyIngresos2022 = key($ing);

        next($ing2);
        $keyIngresos2020 = key($ing2);
    } elseif (!$keyIngresos2020 || CompararCadenas((string) $keyIngresos2022, (string) $keyIngresos2020) < 0) { //cuentas anteriores
        $finalIng[$keyIngresos2022]['cuenta'] = $ing[$keyIngresos2022]['cuenta'];
        $finalIng[$keyIngresos2022]['nivel'] = $ing[$keyIngresos2022]['nivel'];
        $finalIng[$keyIngresos2022]['nivelCuenta'] = $ing[$keyIngresos2022]['nivelCuenta'];
        $finalIng[$keyIngresos2022]['importe2020'] = $ing[$keyIngresos2022]['importe'];
        $finalIng[$keyIngresos2022]['importe2019'] = (isset($finalIng[$keyIngresos2022]['importe2019']) ? $finalIng[$keyIngresos2022]['importe2019'] : 0.00);

        next($ing);
        $keyIngresos2022 = key($ing);
    } else {
        $finalIng[$keyIngresos2020]['cuenta'] = $ing2[$keyIngresos2020]['cuenta'];
        $finalIng[$keyIngresos2020]['nivel'] = $ing2[$keyIngresos2020]['nivel'];
        $finalIng[$keyIngresos2020]['nivelCuenta'] = $ing2[$keyIngresos2020]['nivelCuenta'];
        $finalIng[$keyIngresos2020]['importe2020'] = (isset($finalIng[$keyIngresos2020]['importe2020']) ? $finalIng[$keyIngresos2020]['importe2020'] : 0.00);
        $finalIng[$keyIngresos2020]['importe2019'] = $ing2[$keyIngresos2020]['importe'];

        next($ing2);
        $keyIngresos2020 = key($ing2);
    }
}

$keyGastos2022 = key($ga);
$keyGastos2020 = key($ga2);
$finalGA = [];

while ($keyGastos2022 || $keyGastos2020) {
    if (CompararCadenas((string) $keyGastos2022, (string) $keyGastos2020) == 0) {
        $finalGA[$keyGastos2022]['cuenta'] = $ga[$keyGastos2022]['cuenta'];
        $finalGA[$keyGastos2022]['nivel'] = $ga[$keyGastos2022]['nivel'];
        $finalGA[$keyGastos2022]['nivelCuenta'] = $ga[$keyGastos2022]['nivelCuenta'];
        $finalGA[$keyGastos2022]['importe2020'] = $ga[$keyGastos2022]['importe'];
        $finalGA[$keyGastos2022]['importe2019'] = $ga2[$keyGastos2020]['importe'];

        next($ga);
        $keyGastos2022 = key($ga);

        next($ga2);
        $keyGastos2020 = key($ga2);
    } elseif (!$keyGastos2020 || CompararCadenas((string) $keyGastos2022, (string) $keyGastos2020) < 0) { //cuentas anteriores
        $finalGA[$keyGastos2022]['cuenta'] = $ga[$keyGastos2022]['cuenta'];
        $finalGA[$keyGastos2022]['nivel'] = $ga[$keyGastos2022]['nivel'];
        $finalGA[$keyGastos2022]['nivelCuenta'] = $ga[$keyGastos2022]['nivelCuenta'];
        $finalGA[$keyGastos2022]['importe2020'] = $ga[$keyGastos2022]['importe'];
        $finalGA[$keyGastos2022]['importe2019'] = 0.00;

        next($ga);
        $keyGastos2022 = key($ga);
    } else {
        $finalGA[$keyGastos2020]['cuenta'] = $ga2[$keyGastos2020]['cuenta'];
        $finalGA[$keyGastos2020]['nivel'] = $ga2[$keyGastos2020]['nivel'];
        $finalGA[$keyGastos2020]['nivelCuenta'] = $ga2[$keyGastos2020]['nivelCuenta'];
        $finalGA[$keyGastos2020]['importe2020'] = (isset($finalGA[$keyGastos2020]['importe2020']) ? $finalGA[$keyGastos2020]['importe2020'] : 0.00);
        $finalGA[$keyGastos2020]['importe2019'] = $ga2[$keyGastos2020]['importe'];

        next($ga2);
        $keyGastos2020 = key($ga2);
    }
}

/*****QUITAR O NO LOS CEROS SEGUN EL CHECK******/
if ($pdf->ceros == '') {
    if ($_SESSION[_S]['reportes'] == MENU_CUENTA_PUBLICA) {
        foreach ($finalIng as $indice => $val) {
            if ($finalIng[$indice]['importe2020'] != 0) {
                $finalIng[$indice]['cuenta'] = $finalIng[$indice]['cuenta'];
                $finalIng[$indice]['nivel'] = $finalIng[$indice]['nivel'];
                $finalIng[$indice]['nivelCuenta'] = $finalIng[$indice]['nivelCuenta'];
                $finalIng[$indice]['importe2020'] = $finalIng[$indice]['importe2020'];
                $finalIng[$indice]['importe2019'] = $finalIng[$indice]['importe'];
                $finalIng[$indice]['ceros'] = 0;
            }
        }
        foreach ($finalGA as $indice => $val) {
            if ($finalGA[$indice]['importe2020'] != 0) {
                $finalGA[$indice]['cuenta'] = $finalGA[$indice]['cuenta'];
                $finalGA[$indice]['nivel'] = $finalGA[$indice]['nivel'];
                $finalGA[$indice]['nivelCuenta'] = $finalGA[$indice]['nivelCuenta'];
                $finalGA[$indice]['importe2020'] = $finalGA[$indice]['importe2020'];
                $finalGA[$indice]['importe2019'] = $finalGA[$indice]['importe'];
                $finalGA[$indice]['ceros'] = 0;
            }
        }
    } else {
        foreach ($finalIng as $indice => $val) {
            if ($finalIng[$indice]['importe2020'] != 0 || $finalIng[$indice]['importe2019'] != 0) {
                $finalIng[$indice]['cuenta'] = $finalIng[$indice]['cuenta'];
                $finalIng[$indice]['nivel'] = $finalIng[$indice]['nivel'];
                $finalIng[$indice]['nivelCuenta'] = $finalIng[$indice]['nivelCuenta'];
                $finalIng[$indice]['importe2020'] = $finalIng[$indice]['importe2020'];
                $finalIng[$indice]['importe2019'] = $finalIng[$indice]['importe2019'];
                $finalIng[$indice]['ceros'] = 0;
            }
        }
        foreach ($finalGA as $indice => $val) {
            if ($finalGA[$indice]['importe2020'] != 0 || $finalGA[$indice]['importe2019'] != 0) {
                $finalGA[$indice]['cuenta'] = $finalGA[$indice]['cuenta'];
                $finalGA[$indice]['nivel'] = $finalGA[$indice]['nivel'];
                $finalGA[$indice]['nivelCuenta'] = $finalGA[$indice]['nivelCuenta'];
                $finalGA[$indice]['importe2020'] = $finalGA[$indice]['importe2020'];
                $finalGA[$indice]['importe2019'] = $finalGA[$indice]['importe2019'];
                $finalGA[$indice]['ceros'] = 0;
            }
        }
    }
} else {
    foreach ($finalIng as $indice => $val) {
        $finalIng[$indice]['cuenta'] = $finalIng[$indice]['cuenta'];
        $finalIng[$indice]['nivel'] = $finalIng[$indice]['nivel'];
        $finalIng[$indice]['nivelCuenta'] = $finalIng[$indice]['nivelCuenta'];
        $finalIng[$indice]['importe2020'] = $finalIng[$indice]['importe2020'];
        $finalIng[$indice]['importe2019'] = $finalIng[$indice]['importe2019'];
        $finalIng[$indice]['ceros'] = 1;
    }
    foreach ($finalGA as $indice => $val) {
        $finalGA[$indice]['cuenta'] = $finalGA[$indice]['cuenta'];
        $finalGA[$indice]['nivel'] = $finalGA[$indice]['nivel'];
        $finalGA[$indice]['nivelCuenta'] = $finalGA[$indice]['nivelCuenta'];
        $finalGA[$indice]['importe2020'] = $finalGA[$indice]['importe2020'];
        $finalGA[$indice]['importe2019'] = $finalGA[$indice]['importe2019'];
        $finalGA[$indice]['ceros'] = 1;
    }
}

/*****ORDEN***************************/
$sortIng = [];
foreach ($finalIng as $indice => $val) {
    $sortIng['indice'][$indice] = str_pad($indice, 5, '0', STR_PAD_RIGHT);
}
array_multisort($sortIng['indice'], SORT_ASC, $finalIng);

$sortGa = [];
foreach ($finalGA as $indice => $val) {
    $sortGa['indice'][$indice] = str_pad($indice, 5, '0', STR_PAD_RIGHT);
}
array_multisort($sortGa['indice'], SORT_ASC, $finalGA);
/******************************fin orden ************************************/

$in = array_merge($finalIng); //ingresos 2020
$g = array_merge($finalGA); //gastos 2020

$max = max(count($in), count($g));
$max = $max += 2; // incrementamos a 2 el valor de max para pintar los totales globales.

if (count($in) < $max) {
    $ini = count($in);
    for ($i = $ini; $i < $max; $i++) {
        $in[$i] = ['cuenta' => '', 'nivel' => '', 'nivelCuenta' => 5, 'importe2020' => '', 'importe2019' => '']; // se rellenan con arreglos vacios, esto para igualar los arreglos.
    }
}
// si gastos tiene menos elementos que el activo, lo igualamos
if (count($g) < $max) {
    $ini2 = count($g);
    for ($i = $ini2; $i < $max; $i++) {
        $g[$i] = ['cuenta' => '', 'nivel' => '', 'nivelCuenta' => 5, 'importe2020' => '', 'importe2019' => '']; // se rellenan con arreglos vacios, esto para igualar los arreglos.
    }
}

$relleno = false; // Para determinar si se colorea de gris el fondo de la línea
$altoLinea = $pdf->intLin;
$fill = false;

for ($x = 0; $x < $max; $x++) {
    $nivelI = '';
    $cuentaI = '';
    $importeIngresos2022 = '';
    $importeIngresos2020 = '';
    $nivelG = '';
    $cuentaG = '';
    $importeG = '';
    $importeGastos2022 = '';
    $importeGastos2020 = '';

    if (isset($in[$x]) && isset($in[$x]['ceros'])) {
        if ($pdf->nivel[$in[$x]['nivelCuenta']] == 1 || in_array($in[$x]['cuenta'], $cuentasTotalesI)) {
            $nivelI = $in[$x]['cuenta'];
            $cuentaI = $in[$x]['nivel'];
            if ($_SESSION[_S]['reportes'] == 'C112') {
                $importeIngresos2022 = (($in[$x]['importe2020'] != '') ? number_format(round($in[$x]['importe2020'], 2), 0) : (($in[$x]['cuenta'] != '') ? number_format(0, 0) : ''));
                $importeIngresos2020 = (($in[$x]['importe2019'] != '') ? number_format(round($in[$x]['importe2019'], 2), 0) : (($in[$x]['cuenta'] != '') ? number_format(0, 0) : ''));
            } else {
                $importeIngresos2022 = (($in[$x]['importe2020'] != '') ? number_format($in[$x]['importe2020'], 2) : (($in[$x]['cuenta'] != '') ? number_format(0, 0) : ''));
                $importeIngresos2020 = (($in[$x]['importe2019'] != '') ? number_format($in[$x]['importe2019'], 2) : (($in[$x]['cuenta'] != '') ? number_format(0, 0) : ''));
            }
        }
        $importeIngresos2022 = (($in[$x]['nivelCuenta'] != 1 && $cuentaI != '') ? $importeIngresos2022 : '');
        $importeIngresos2020 = (($in[$x]['nivelCuenta'] != 1 && $cuentaI != '') ? $importeIngresos2020 : '');
        // }
    }
    if (isset($g[$x]) && isset($g[$x]['ceros'])) {
        if ($pdf->nivel[$g[$x]['nivelCuenta']] == 1 || in_array($g[$x]['cuenta'], $cuentasTotalesG)) {
            $nivelG = $g[$x]['cuenta'];
            $cuentaG = $g[$x]['nivel'];
            if ($_SESSION[_S]['reportes'] == 'C112') {
                $importeGastos2022 = (($g[$x]['importe2020'] != '') ? number_format(round($g[$x]['importe2020'], 0), 0) : (($g[$x]['cuenta'] != '') ? number_format(0, 0) : ''));
                $importeGastos2020 = (($g[$x]['importe2019'] != '') ? number_format(round($g[$x]['importe2019'], 0), 0) : (($g[$x]['cuenta'] != '') ? number_format(0, 0) : ''));
            } else {
                $importeGastos2022 = (($g[$x]['importe2020'] != '') ? number_format(round($g[$x]['importe2020'], 2), 2) : (($g[$x]['cuenta'] != '') ? number_format(0, 0) : ''));
                $importeGastos2020 = (($g[$x]['importe2019'] != '') ? number_format(round($g[$x]['importe2019'], 2), 2) : (($g[$x]['cuenta'] != '') ? number_format(0, 0) : ''));
            }
        }
        $importeGastos2022 = (($g[$x]['nivelCuenta'] != 1 && $cuentaG != '') ? $importeGastos2022 : '');
        $importeGastos2020 = (($g[$x]['nivelCuenta'] != 1 && $cuentaG != '') ? $importeGastos2020 : '');
    }

    if ($cuentaI != '' || $cuentaG != '') {
        if ($_SESSION[_S]['reportes'] == 'C112') {
            $pdf->longitud2 = 54;
        }
        $espacio = '';
        switch ($in[$x]['nivelCuenta']) {
            case 2:
                $pdf->SetFont('univiapro', 'B', 5.4);
                $espacio = '   ';
                break;
            case 3:
                $pdf->SetFont('univiapro', '', 6);
                $espacio = '      ';
                break;
            case 4:
                $pdf->SetFont('univiapro', '', 6);
                ;
                $espacio = '         ';
                break;
            case 5:
                if (in_array($in[$x]['cuenta'], $cuentasTotalesI)) {
                    $pdf->SetFont('univiapro', 'B', 6);
                }
                $espacio = '            ';
                break;
            default:
                $pdf->SetFont('univiapro', '', 6);
                break;
        }

        $pdf->SetFillColor(192, 192, 192);

        // Si hay datos en la línea... se rellena el fondo... si no hay datos no
        // independientemente de la configuración que traiga
        // VALIDAMOS LA PARTE DE INGRESOS
        if (trim($cuentaI) != '') {
            $fill = $relleno;
        } else {
            $fill = false;
        }

        $pdf->setX(10);

        if ($_SESSION[_S]['reportes'] == MENU_CUENTA_PUBLICA) {
            $pdf->Cell(101.5, $altoLinea, $espacio . substr($cuentaI, 0, $pdf->longitud2), 0, 0, 'L', $fill);
            $pdf->Cell(33.5, $altoLinea, $importeIngresos2022, 0, 0, 'R', $fill);
        } else {
            $pdf->Cell(68, $altoLinea, $espacio . substr($cuentaI, 0, $pdf->longitud2), 0, 0, 'L', $fill);
            $pdf->Cell(33.5, $altoLinea, $importeIngresos2022, 0, 0, 'R', $fill);
            $pdf->Cell(33.5, $altoLinea, $importeIngresos2020, 0, 0, 'R', $fill);
        }
        $pdf->Cell(4, $altoLinea, '', 'LR', 0, 'R', $fill);

        switch ($g[$x]['nivelCuenta']) {
            case 2:
                $pdf->SetFont('univiapro', 'B', 5.4);
                $espacio = '   ';
                break;
            case 3:
                $pdf->SetFont('univiapro', '', 6);
                $espacio = '      ';
                break;
            case 4:
                $pdf->SetFont('univiapro', '', 6);
                $espacio = '         ';
                break;
            case 5:
                if (in_array($g[$x]['cuenta'], $cuentasTotalesG)) {
                    $pdf->SetFont('univiapro', 'B', 6);
                }
                $espacio = '            ';
                break;
            default:
                $pdf->SetFont('univiapro', '', 6);
                break;
        }

        // Si hay datos en la línea... se rellena el fondo... si no hay datos no
        // independientemente de la configuración que traiga
        // VALIDAMOS LA PARTE DE GASTOS
        if (trim($cuentaG) != '') {
            $fill = $relleno;
        } else {
            $fill = false;
        }

        if ($_SESSION[_S]['reportes'] == MENU_CUENTA_PUBLICA) {
            $pdf->Cell(101.5, $altoLinea, $espacio . substr($cuentaG, 0, $pdf->longitud2), 0, 0, 'L', $fill);
            $pdf->Cell(33.5, $altoLinea, $importeGastos2022, 0, 1, 'R', $fill);
        } else {
            $pdf->Cell(68, $altoLinea, $espacio . substr($cuentaG, 0, $pdf->longitud2), 0, 0, 'L', $fill);
            $pdf->Cell(33.5, $altoLinea, $importeGastos2022, 0, 0, 'R', $fill);
            $pdf->Cell(33.5, $altoLinea, $importeGastos2020, 0, 1, 'R', $fill);
        }

        $con2 = $con = 1;
        if (strlen($cuentaI) > $pdf->longitud2 * $con2 || strlen($cuentaG) > $pdf->longitud2 * $con) {
            switch ($in[$x]['nivelCuenta']) {
                case 2:
                    $pdf->SetFont('univiapro', 'B', 5.4);
                    $espacio = '   ';
                    break;
                case 3:
                    $pdf->SetFont('univiapro', '', 6);
                    $espacio = '      ';
                    break;
                case 4:
                    $pdf->SetFont('univiapro', '', 6);
                    $espacio = '         ';
                    break;
                case 5:
                    if (in_array($in[$x]['cuenta'], $cuentasTotalesI)) {
                        $pdf->SetFont('univiapro', 'B', 6);
                    }
                    $espacio = '            ';
                    break;
                default:
                    $pdf->SetFont('univiapro', '', 6);
                    break;
            }

            if ($_SESSION[_S]['reportes'] == MENU_CUENTA_PUBLICA) {
                $pdf->Cell(91.5, $altoLinea, $espacio . substr($cuentaI, $pdf->longitud2 * $con2, $pdf->longitud2), 0, 0, 'L', $fill);
                $pdf->Cell(33.5, $altoLinea, '', 0, 0, 'R', $fill);
            } else {
                $pdf->Cell(68, $altoLinea, $espacio . substr($cuentaI, $pdf->longitud2 * $con2, $pdf->longitud2), 0, 0, 'L', $fill);
                $pdf->Cell(33.5, $altoLinea, '', 0, 0, 'R', $fill);
                $pdf->Cell(33.5, $altoLinea, '', 0, 0, 'R', $fill);
            }
            $pdf->Cell(4, $altoLinea, '', 'LR', 0, 'R', $fill);

            switch ($g[$x]['nivelCuenta']) {
                case 2:
                    $pdf->SetFont('univiapro', 'B', 5.4);
                    $espacio = '   ';
                    break;
                case 3:
                    $pdf->SetFont('univiapro', '', 6);
                    $espacio = '      ';
                    break;
                case 4:
                    $pdf->SetFont('univiapro', '', 6);
                    $espacio = '         ';
                    break;
                case 5:
                    if (in_array($g[$x]['cuenta'], $cuentasTotalesG)) {
                        $pdf->SetFont('univiapro', 'B', 6);
                    }
                    $espacio = '            ';
                    break;
                default:
                    $pdf->SetFont('univiapro', '', 6);
                    break;
            }

            if ($_SESSION[_S]['reportes'] == MENU_CUENTA_PUBLICA) {
                $pdf->Cell(101.5, $altoLinea, $espacio . substr($cuentaG, $pdf->longitud2 * $con, $pdf->longitud2), 0, 0, 'L', $fill);
                $pdf->Cell(33.5, $altoLinea, '', 0, 1, 'R', $fill);
            } else {
                $pdf->Cell(68, $altoLinea, $espacio . substr($cuentaG, $pdf->longitud2 * $con, $pdf->longitud2), 0, 0, 'L', $fill);
                $pdf->Cell(33.5, $altoLinea, '', 0, 0, 'R', $fill);
                $pdf->Cell(33.5, $altoLinea, '', 0, 1, 'C', $fill);
            }

            $con2++;
            $con++;
        }
    }

    $relleno = ($pdf->incCta == '') ? false : !$relleno; // Para determinar si se colorea de gris el fondo de la línea

}
if ($pdf->getY() > 180) {
    $pdf->addPage();
}
$pdf->Output();

// Fin del código
