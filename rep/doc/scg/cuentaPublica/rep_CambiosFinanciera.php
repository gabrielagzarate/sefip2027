<?php

session_start();
// ini_set('memory_limit','0');

set_time_limit(0);
//librerias
require_once('../../../lib/reportetabla.1.8.php');
require_once('../../../../lib/scg/conciliacion/consolidacion.php');
require_once('../../../../lib/compararCadenas.php');
require_once('../../../../lib/MesNombre.php');
require_once('../../../../lib/helpers.php');
require_once('../../../lib/conversionNumLetras.php');
require_once('../../../../lib/AccesoDatos.php');
require_once('../../../../ent/UnidadResponsable.php');

class reportetabla extends PDF
{
    public $mes;
    public $texto;
    private $longitud = 100;
    public $longitud2 = 65;
    public $ultBorde = 0;
    public $widths = '';
    public $anchoImagen;
    public $altoImagen;
    public $nlineas;
    public function __construct()
    {
        parent::__construct();

        $this->gpo_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['s24-gpo'];
        $this->ur_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['s24-ur'];
        $this->mesDesde = isset($_POST['comboMesDesde']) ? $_POST['comboMesDesde'] : -1;//Informacion de acuerdo al mes
        $this->mesHasta = isset($_POST['comboMesHasta']) ? $_POST['comboMesHasta'] : -1;//Informacion de acuerdo al mes
        $this->firmante1 = isset($_POST['firmante1']) ? $_POST['firmante1'] : '';
        $this->firmante2 = isset($_POST['firmante2']) ? $_POST['firmante2'] : '';
        $this->firmante3 = isset($_POST['firmante3']) ? $_POST['firmante3'] : '';
        $this->cargo1 = isset($_POST['cargo1']) ? $_POST['cargo1'] : '';
        $this->cargo2 = isset($_POST['cargo2']) ? $_POST['cargo2'] : '';
        $this->cargo3 = isset($_POST['cargo3']) ? $_POST['cargo3'] : '';
        $this->leyenda = isset($_POST['leyenda']) ? $_POST['leyenda'] : '';
        // $this->ceros = 0;
        $this->ceros = isset($_POST['ceros']) ? $_POST['ceros'] : '';
        $this->incCta = isset($_POST['incCta']) ? $_POST['incCta'] : '';
        $this->intLin = isset($_POST['intLin']) ? $_POST['intLin'] : '';
        $this->ue = isset($_POST['ue']) ? $_POST['ue'] : '';
        $this->numUe = isset($_POST['numUe']) ? $_POST['numUe'] : '';

        // Si no viene un número en el interlineado, ponemos el default
        if (!is_numeric($this->intLin)) {
            $this->intLin = $this->intLin;
        }

        $_POST['tipoReporte'] = ((isset($_POST['tipoReporte'])) ? $_POST['tipoReporte'] : 1);

        $this->nivel[1] = isset($_POST['checkNivel1']) ? $_POST['checkNivel1'] : -1;
        $this->nivel[2] = isset($_POST['checkNivel2']) ? $_POST['checkNivel2'] : -1;
        $this->nivel[3] = isset($_POST['checkNivel3']) ? $_POST['checkNivel3'] : -1;
        $this->nivel[4] = isset($_POST['checkNivel4']) ? $_POST['checkNivel4'] : -1;
        $this->nivel[5] = isset($_POST['checkNivel5']) ? $_POST['checkNivel5'] : -1;

        $this->ejercicio = '';

        if ($this->ejercicio == '') {
            $this->ur = new UnidadResponsable();
            $this->ur->setGpo_id($this->gpo_id);
            $this->ur->setUr_id($this->ur_id);
            $this->ur->select();
        } else {
            //obtenemos los datos de la UR del ejercicio solicitado
        }
    }
    public function Footer($lastPage = true)
    {

        $this->AddFont('univiapro', '');
        $this->AddFont('univiapro', 'B');
        $this->AddFont('univiapro');
        $row = $this->getDatos();
        $tmpb = $this->GetBorders();
        // $this->SetY(-10);
        // $this->SetY(-45);
        if ($this->leyenda == 1 || ($this->cargo1 != '' && $this->cargo2 != '' && $this->firmante1 != '' && $this->firmante2 != '')) {
            $this->SetY(-30);
            $this->Cell(190, 2, utf8Decode(''), 0, 0, 'C');
            $this->Ln(5);
        } else {
            $this->SetY(-25);
        }

        if ($lastPage) {
            if ($this->leyenda == 1) {
                $this->SetFont('univiapro', '', 7);
                if ($_SESSION[_S]['reportes'] == 'C94' && $this->mesDesde >= 1 && $this->mesHasta <= 3 && ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 122 && $_SESSION[_S]['nvj_id'] == 2)) {
                    $this->Cell(270, 2, utf8Decode('Lugar y fecha de generación: Reyes Mantecón, Oaxaca'), 0, 0, 'C');
                } else {
                    $this->Cell(270, 2, utf8Decode('Lugar y fecha de generación: Reyes Mantecón, Oaxaca; a ' . date('d') . ' de ' . MesNombre(date('m')) . ' de ' . date('Y')), 0, 0, 'C');
                }
                $this->Ln(2);
                $this->Cell(270, 2, utf8Decode('Bajo protesta de decir verdad declaramos que los estados financieros y sus Notas son razonablemente correctos y responsabilidad del emisor.'), 0, 0, 'C');
                $this->Ln(1);
            }
            $this->SetFont('univiapro', 'B', 7);
            $y = $this->GetY();

            $this->Ln(2);
            $this->MultiAlignCell(97, 3, utf8Decode($this->cargo1), 0, 0, 'C');
            $this->MultiAlignCell(77, 3, utf8Decode($this->cargo2), 0, 0, 'C');
            $this->MultiAlignCell(97, 3, utf8Decode($this->cargo3), 0, 1, 'C');
            $this->Ln(4);
            $this->SetFont('univiapro', '', 7);
            $this->MultiAlignCell(97, 3, utf8Decode($this->firmante1), 0, 0, 'C');
            $this->MultiAlignCell(77, 3, utf8Decode($this->firmante2), 0, 0, 'C');
            $this->MultiAlignCell(97, 3, utf8Decode($this->firmante3), 0, 1, 'C');
        }

        $this->SetY(-11);
        $this->SetFont('univiapro', '', 5);
        $this->Cell(275, 10, utf8Decode('Página') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }


    // Marcos: 22/mar/2019
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
        //$this->Image('../../../../img/logosefin-color-fblanco.jpg', $this->logo, $iy, $this->anchoImagen, $this->altoImagen, 'jpg');
        $this->Image('../../../../img/logoOaxaca-color-fblanco.png', $this->logo, $iy, $this->anchoImagen, $this->altoImagen, 'png');
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
        if (isset($nlineas) <= 4) {
            $this->SetY($this->GetY() + 2);
            $this->ln(5);
        }

        // Límite para asegurar la buena visilibidad del reporte y el correcto aprovechamiento de pantalla
        // Si sale sobreencimado con los encabezados de columna, tal vez haya que revisar las líneas del encabezado
        $this->setY(35);

    }







    public function Header() //Encabezado del documento
    {$this->AddFont('univiapro', '');
        $this->AddFont('univiapro', 'B');
        $this->AddFont('univiapro');

        if ($_SESSION[_S]['reportes'] != 'C94') {
            $linea1 = utf8Decode('Cuenta de la Hacienda Pública Estatal ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
        }

        if ($_SESSION[_S]['nvj_id'] > 2) {
            $linea2 = utf8Decode('Secretaría de Finanzas del Gobierno del Estado de Oaxaca');
        } else {
            $linea2 = utf8Decode('Gobierno del Estado de Oaxaca');
        }

        if ($_SESSION[_S]['nvj_id'] > 2) {

            switch ($_POST['tipoReporte']) {
                case 2:   // Todas las URs de la carga de trabajo
                    $texto = 'Dirección de Contabilidad Gubernamental';
                    break;
                case 3:   // Determinar qué incluir y qué excluir
                    $texto = isset($_POST['nombreEspecial']) ? $_POST['nombreEspecial'] : 'ERROR DE PROGRAMA. NO EXISTE ENCABEZADO';
                    break;
                default:
                    if ($this->ejercicio == '') {
                        $texto = 'Dirección de Contabilidad Gubernamental: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre();
                    } else {
                        $texto = 'Dirección de Contabilidad Gubernamental: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . $this->ur['ur_nombre'];
                    }
                    break;

            }


        } else {
            if ($this->ejercicio == '') {
                $texto = 'UR: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre();
            } else {
                $texto = 'UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . $this->ur['ur_nombre'];
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
            $texto2 = 'UE: ' . $ue_id['ue_id'] . ' - ' . $ue_id['ue_nombre'] . '';
        }

        if ($_POST['tipoReporte'] != 3 && $_SESSION[_S]['nvj_id'] > 2) {
            $linea3 = utf8Decode($texto);
        }

        if ($this->ue != '' && $_SESSION[_S]['nvj_id'] > 2) {
            $linea4 = utf8Decode($texto2);
        }

        $linea5 = utf8Decode('Estado de Cambios en la Situación Financiera');

        $mesId = (isset($_POST['comboMesDesde']) && $_POST['comboMesDesde'] != '') ? $_POST['comboMesDesde'] : '';

        $sqlMes = ' select msc_nombre, msc_dias from cat_MesContable where msc_id= ? ;';
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sqlMes);
        $con->consultar([$mesId]);
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

        if ($_SESSION[_S]['reportes'] == 'C94') {
            if ($this->mesDesde == 0 and $this->mesHasta == 0) {
                $linea6 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
            } elseif ($this->mesHasta == $this->mesDesde) {
                $linea6 = utf8Decode('Del 1° al ' . $rowMes['msc_dias'] . ' de ' . strtolower($rowMes['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
            } else {
                if ($this->mesDesde == 0) {
                    $linea6 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                } else {
                    $linea6 = utf8Decode('Del 1° de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                }
            }
        } else {

            if ($this->mesDesde == 0 and $this->mesHasta == 0) {
                $linea6 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
            } elseif ($this->mesHasta == $this->mesDesde) {
                $linea6 = utf8Decode('Del 1° al ' . $rowMes['msc_dias'] . ' de ' . strtolower($rowMes['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
            } else {
                if ($this->mesDesde == 0) {
                    $linea6 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                } else {
                    $linea6 = utf8Decode('Del 1° de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                }
            }

        }

        if (isset($_POST['ckmiles'])) {
            $linea7 = '(Miles)';
        } else {
            $linea7 = '(Pesos)';
        }

        if ($_POST['tipoReporte'] == 3 || $_SESSION[_S]['nvj_id'] <= 2) {
            $linea3 = utf8Decode($texto);
            if ($this->ue != '' && $_SESSION[_S]['nvj_id'] <= 2) {
                $linea4 = utf8Decode($texto2);
            }
        }

        // Dibujamos el logo y las líneas del encabezado
        $this->drawHeadersAndLogo('L', $linea1, $linea2, $linea3, $linea4, $linea5, $linea6, $linea7);

        $this->setRelleno('si');
        $this->SetFont('univiapro', 'B', 7);
        $this->SetTextColor(0, 0, 0);
        $this->setX(10);
        // $this->SetDrawColor(180,180,180);
        // $this->SetFillColor(220,220,220);
        $this->setDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->setAltoLinea(3);

        $this->SetBorders(1);
        $this->SetWidths([68,33.5,33.5,4,68,33.5,33.5]);
        $this->SetAligns(['L','R','R','C','L','R','R']);

        $this->Row([
            utf8Decode('CONCEPTO'),
            utf8Decode('ORIGEN'),
            utf8Decode('APLICACIÓN'),
            '',
            utf8Decode('CONCEPTO'),
            utf8Decode('ORIGEN'),
            utf8Decode('APLICACIÓN'),
        ]);
        $this->setX(10);
        $this->ln(2);
        $this->setRelleno('no');
        $this->SetBorders(0);

    }
}
// Iniciamos el reporte
// --------------------------------------------------------------------------------------------------------------------------------
$mesId = (isset($_POST['comboMesDesde']) && $_POST['comboMesDesde'] != '') ? $_POST['comboMesDesde'] : '';
$mesHasta = (isset($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta'] : -1;
$tipoReporte = (isset($_POST['tipoReporte']) && $_POST['tipoReporte'] != '') ? $_POST['tipoReporte'] : '';
$checkMiles = (isset($_POST['ckmiles'])) ? 1000 : 1;
$grupos = isset($_POST['grupos']) ? $_POST['grupos'] : '';
$excluir = isset($_POST['excluir']) ? $_POST['excluir'] : '';
$ue = isset($_POST['ue']) ? $_POST['ue'] : '';

$pdf = new Reportetabla('L', 'mm', 'Letter');
$pdf->SetProtection(['print','annot-forms'], '', PDF_PASSWORD_OWNER_SCG);
if ($checkMiles == 1000) {
    $texto = '(en miles de pesos)';
} else {
    $texto = '(pesos)';
}
// print_r($_POST);

$pdf->setOrientation('L');
$pdf->AliasNbPages();
$pdf->texto = $texto;
$pdf->AddPage();
$pdf->setRelleno('no');
$pdf->AddFont('univiapro', '');
$pdf->AddFont('univiapro', 'B');
$pdf->AddFont('univiapro');
$pdf->SetFont('univiapro', '', 7);
// $pdf->SetWidths(array(18,50,33.5,33.5,4,18,50,33.5,33.5));
$pdf->SetWidths([68,33.5,33.5,4,68,33.5,33.5]);
// $pdf->SetAligns(array('L','L','R','R','C','L','L','R','R'));
$pdf->SetAligns(['L','R','R','C','L','R','R']);

$activo2018 = [];
$arrayPH2018 = [];

list($activo) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 1, $tipoReporte, '', $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
list($pasivo) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 2, $tipoReporte, '', $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
list($hacienda) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 3, $tipoReporte, '', $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);

list($ingresos) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 4, $tipoReporte, '', $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
list($gastos) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 5, $tipoReporte, '', $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);

//obtenemos resultado del ejercicio (ahorro/desahorro)
$result2019 = 0;
if (count($ingresos) > 0 && count($gastos) > 0) {

    $natIng2019 = $ingresos[4]['naturalezaCuenta'];
    $natGas2019 = $gastos[5]['naturalezaCuenta'];

    $debeI2019 = $ingresos[4]['debe'];
    $haberI2019 = $ingresos[4]['haber'];
    $debeG2019 = $gastos[5]['debe'];
    $haberG2019 = $gastos[5]['haber'];

    if ($natIng2019 == 'D') {
        $result2019 = $debeI2019 - $haberI2019;
    } else {
        $result2019 = $haberI2019 - $debeI2019;
    }

}

list($activo15) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 1, $tipoReporte, 2020, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
list($pasivo15) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 2, $tipoReporte, 2020, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
list($hacienda15) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 3, $tipoReporte, 2020, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);

list($ingresos15) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 4, $tipoReporte, 2020, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
list($gastos15) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 5, $tipoReporte, 2020, $checkMiles, $grupos, $excluir, $pdf->ceros, $pdf->incCta);

$result2018 = 0;
if (count($ingresos15) > 0 && count($gastos15) > 0) {
    $natIng2018 = $ingresos15[4]['naturalezaCuenta'];
    $natGas2018 = $gastos15[5]['naturalezaCuenta'];

    $debeI2018 = $ingresos15[4]['debe'];
    $haberI2018 = $ingresos15[4]['haber'];
    $debeG2018 = $gastos15[5]['debe'];
    $haberG2018 = $gastos15[5]['haber'];

    if ($natIng2018 == 'D') {
        $result2018 = $debeI2018 - $haberI2018;
    } else {
        $result2018 = $haberI2018 - $debeI2018;
    }
}

$activo2018 = devuelveArrayOrdenado($activo15, $pdf->nivel);
$pasivo2018 = devuelveArrayOrdenado($pasivo15, $pdf->nivel);
$hacienda2018 = devuelveArrayOrdenado($hacienda15, $pdf->nivel);

$arrayPH2018 = $pasivo2018 + $hacienda2018;


$activo2019 = devuelveArrayOrdenado($activo, $pdf->nivel);
$pasivo2019 = devuelveArrayOrdenado($pasivo, $pdf->nivel);
$hacienda2019 = devuelveArrayOrdenado($hacienda, $pdf->nivel);

#SE UNEN LOS ARREGLOS
$arrayPH2019 = $pasivo2019 + $hacienda2019;

$arrayActivo = compararCuentas($activo2018, $activo2019);
$arrayPasivoHacienda = compararCuentas($arrayPH2018, $arrayPH2019);

//añadimos el registro del resultado del ejercicio.
$arrayPasivoHacienda[49000] = ['cuenta' => '49000','nombre' => 'RESULTADOS DEL EJERCICIO (AHORRO/DESAHORRO)','nivel' => 1,'importe2019' => $result2019,'importe2018' => 0];
$arrayPasivoHacienda[49000] = ['cuenta' => '49000','nombre' => 'RESULTADOS DEL EJERCICIO (AHORRO/DESAHORRO)','nivel' => 1,'importe2019' => $result2019,'importe2018' => $result2018];

/*
    REALIZAMOS LAS OPERACIONES CORRESPONDIENTES PARA OBTENER EL ORIGEN Y LA APLICACION
    Para el activo, será origen cuando la diferencia de 2019-2018 sea negativo, en caso contrario será aplicacion
    Para el Pasivo y Hacienda, cuando la diferencia de 2019-2018 sea negativo será aplicación, caso contrario será origen.

    Los totales de los niveles 2 será la sumatoria que le corresponda a cada grupo
*/

//activo
$diferencia = $totalOrigenActivo = $totalAplicacionActivo = $totalOrigenPH = $totalAplicacionPH = $sumaO = $sumaA = 0;
$grupo = '';
foreach ($arrayActivo as $act => $val) {

    $arrayActivo[$act] = $arrayActivo[$act];
    $diferencia = ($arrayActivo[$act]['importe2019'] - $arrayActivo[$act]['importe2018']);
    if ($diferencia < 0) {
        $arrayActivo[$act]['origen'] = ($diferencia) * -1;
        $arrayActivo[$act]['aplicacion'] = 0;

    } else {
        $arrayActivo[$act]['origen'] = 0;
        $arrayActivo[$act]['aplicacion'] = $diferencia;
    }

    //reseteamos los valores del nivel 2 para obtener la suma original del origen y aplicacion
    if ($arrayActivo[$act]['nivel'] <= 2) {
        $arrayActivo[$act]['origen'] = 0;
        $arrayActivo[$act]['aplicacion'] = 0;
    }
    //obtenemos los totales del activo
    $totalOrigenActivo += $arrayActivo[$act]['origen'];
    $totalAplicacionActivo += $arrayActivo[$act]['aplicacion'];
}

//obtenemos los totales de los grupos del activo

foreach ($arrayActivo as $tot => $val) {

    //obtenemos las llaves de los grupos
    if ($arrayActivo[$tot]['nivel'] == 3) {
        $reemplazaPunto = str_replace(['.'], [''], $arrayActivo[$tot]['cuenta']);
        $subs = substr($reemplazaPunto, 0, 2);
        $llaveGrupo = str_pad($subs, 5, '0', STR_PAD_RIGHT) + 000;
        $llaveRubro = $tot;
    }
    //ponemos el total global del activo
    if ($arrayActivo[$tot]['nivel'] == 1) {
        $arrayActivo[$tot]['origen'] = $totalOrigenActivo;
        $arrayActivo[$tot]['aplicacion'] = $totalAplicacionActivo;
    }

    //obtenemos el total de cada grupo.
    if ($arrayActivo[$tot]['nivel'] == 3 && substr($llaveGrupo, 0, 2) == substr($llaveRubro, 0, 2)) {
        $arrayActivo[$llaveGrupo]['origen'] += $arrayActivo[$tot]['origen'];
        $arrayActivo[$llaveGrupo]['aplicacion'] += $arrayActivo[$tot]['aplicacion'];
    }

}

//obteniendo origen y aplicacion de todos los niveles de pasivo + hacienda
foreach ($arrayPasivoHacienda as $pas => $val) {
    $arrayPasivoHacienda[$pas] = $arrayPasivoHacienda[$pas];
    $diferencia = ($arrayPasivoHacienda[$pas]['importe2019'] - $arrayPasivoHacienda[$pas]['importe2018']);
    if ($diferencia < 0) {
        $arrayPasivoHacienda[$pas]['origen'] = 0;
        $arrayPasivoHacienda[$pas]['aplicacion'] = ($diferencia) * -1;
    } else {
        $arrayPasivoHacienda[$pas]['origen'] = $diferencia;
        $arrayPasivoHacienda[$pas]['aplicacion'] = 0;
    }
    //reseteamos el valor de los niveles 1 y 2, menos el registro de resultado del ejercicio.
    if ($arrayPasivoHacienda[$pas]['nivel'] <= 2 && $pas != 49000) {
        $arrayPasivoHacienda[$pas]['origen'] = 0;
        $arrayPasivoHacienda[$pas]['aplicacion'] = 0;
    }
}

//obtenemos los totales de los grupos del pasivo + hacienda
foreach ($arrayPasivoHacienda as $tot => $val) {

    //obtenemos las llaves de los grupos nivel 2
    if ($arrayPasivoHacienda[$tot]['nivel'] == 3) {
        $reemplazaPunto = str_replace(['.'], [''], $arrayPasivoHacienda[$tot]['cuenta']);
        $subs = substr($reemplazaPunto, 0, 2);
        $llaveGrupo = str_pad($subs, 5, '0', STR_PAD_RIGHT) + 000;
        $llaveRubro = $tot;

    }
    //obtenemos el total de cada grupo. nivel 2
    if ($arrayPasivoHacienda[$tot]['nivel'] == 3 && substr($llaveGrupo, 0, 2) == substr($llaveRubro, 0, 2)) {
        $arrayPasivoHacienda[$llaveGrupo]['origen'] += $arrayPasivoHacienda[$llaveRubro]['origen'];
        $arrayPasivoHacienda[$llaveGrupo]['aplicacion'] += $arrayPasivoHacienda[$llaveRubro]['aplicacion'];
    }

    //obtenemos el total global de pasivo + hacienda
    $totalOrigenPH += $arrayPasivoHacienda[$tot]['origen'];
    $totalAplicacionPH += $arrayPasivoHacienda[$tot]['aplicacion'];
}

//obtenemos los totales de los generos
foreach ($arrayPasivoHacienda as $key => $val) {
    //obtenemos las llaves de los generos nivel 1
    if ($arrayPasivoHacienda[$key]['nivel'] == 2) {
        $reemplazaPunto2 = str_replace(['.'], [''], $arrayPasivoHacienda[$key]['cuenta']);
        $subs2 = substr($reemplazaPunto2, 0, 1);
        $llaveGenero = str_pad($subs2, 5, '0', STR_PAD_RIGHT) + 0000;
        $llaveGrupo2 = $key;
    }
    //obtenemos el total de cada genero. nivel 1
    if ($arrayPasivoHacienda[$key]['nivel'] == 2 && substr($llaveGenero, 0, 1) == substr($llaveGrupo2, 0, 1)) {
        $arrayPasivoHacienda[$llaveGenero]['origen'] += $arrayPasivoHacienda[$llaveGrupo2]['origen'];
        $arrayPasivoHacienda[$llaveGenero]['aplicacion'] += $arrayPasivoHacienda[$llaveGrupo2]['aplicacion'];
    }

}

//agregamos los totales globales del pasivo + hacienda.
$arrayPasivoHacienda[50000] = ['cuenta' => '50000','nombre' => 'TOTALES','nivel' => 1,'origen' => $totalOrigenPH,'aplicacion' => $totalAplicacionPH];

reset($arrayActivo);
reset($arrayPasivoHacienda);
$keyActivo = key($arrayActivo);
$keyPasivoHacienda = key($arrayPasivoHacienda);
while ($keyActivo || $keyPasivoHacienda) {

    $nivelActivo = $arrayActivo[$keyActivo]['nivel'];
    $nivelPasivo = $arrayPasivoHacienda[$keyPasivoHacienda]['nivel'];

    $nombreActivo = $arrayActivo[$keyActivo]['nombre'];
    $nombreActivo = utf8Decode($nombreActivo);

    $origenActivo = $arrayActivo[$keyActivo]['origen'];
    $origenActivo = (isset($origenActivo) ? number_format(round($origenActivo / $checkMiles, 0), 0) : '');
    // if($_SESSION[_S]['reportes']=='C114'){
    $aplicacionActivo = $arrayActivo[$keyActivo]['aplicacion'];
    $aplicacionActivo = (isset($aplicacionActivo) ? number_format(round($aplicacionActivo / $checkMiles, 0), 0) : '');

    $nombrePasivo = $arrayPasivoHacienda[$keyPasivoHacienda]['nombre'];
    $nombrePasivo = utf8Decode($nombrePasivo);

    $origenPasivo = $arrayPasivoHacienda[$keyPasivoHacienda]['origen'];
    $origenPasivo = (isset($origenPasivo) ? number_format(round($origenPasivo / $checkMiles, 0), 0) : '');

    $aplicacionPasivo = $arrayPasivoHacienda[$keyPasivoHacienda]['aplicacion'];
    $aplicacionPasivo = (isset($aplicacionPasivo) ? number_format(round($aplicacionPasivo / $checkMiles, 0), 0) : '');
    // }

    //banderas para controlar el estilo de la fuente.
    $banActivo = ($nivelActivo == 1 || $nivelActivo == 2) && ($nivelPasivo != 1 || $nivelPasivo != 2);
    $banPasivo = ($nivelPasivo == 1 || $nivelPasivo == 2) && ($nivelActivo != 1 || $nivelActivo != 2);
    $banAP = ($nivelPasivo == 1 || $nivelPasivo == 2) && ($nivelActivo == 1 || $nivelActivo == 2);

    //Banderas para controlar el tamaño de la fuente
    $banNivelActivo = ($nivelActivo == 2) && ($nivelPasivo != 2);
    $banNivelPasivo = ($nivelPasivo == 2) && ($nivelActivo != 2);
    $banNivelAP = ($nivelActivo == 2) && ($nivelPasivo == 2);

    $f = new stdClass();
    $f->font = 'univiapro';
    $f->size = ($banNivelActivo || $banNivelAP ? '5.4' : '6');
    $f->style = ($banActivo || $banAP ? 'B' : '');
    $fuentes = [$f];

    $f = new stdClass();
    $f->font = 'univiapro';
    $f->style = ($banActivo || $banAP ? 'B' : '');
    $f->size = ($banNivelActivo || $banNivelAP ? '5.4' : '6');
    array_push($fuentes, $f);

    $f = new stdClass();
    $f->font = 'univiapro';
    $f->style = ($banActivo || $banAP ? 'B' : '');
    $f->size = ($banNivelActivo || $banNivelAP ? '5.4' : '6');
    array_push($fuentes, $f);
    // }

    $f = new stdClass();
    $f->font = 'univiapro';
    $f->style = 'B';
    $f->size = '6';
    array_push($fuentes, $f);

    $f = new stdClass();
    $f->font = 'univiapro';
    $f->style = ($banPasivo || $banAP ? 'B' : '');
    $f->size = ($banNivelPasivo || $banNivelAP ? '5.4' : '6');
    array_push($fuentes, $f);

    $f = new stdClass();
    $f->font = 'univiapro';
    $f->style = ($banPasivo || $banAP ? 'B' : '');
    $f->size = ($banNivelPasivo || $banNivelAP ? '5.4' : '6');
    array_push($fuentes, $f);

    // if($_SESSION[_S]['reportes']=='C114'){
    $f = new stdClass();
    $f->font = 'univiapro';
    $f->style = ($banPasivo || $banAP ? 'B' : '');
    $f->size = ($banNivelPasivo || $banNivelAP ? '5.4' : '6');
    array_push($fuentes, $f);

    $pdf->setFuentes($fuentes);
    $pdf->setAltoLinea($pdf->intLin);

    $pdf->setX(10);
    $pdf->Row([$nombreActivo,$origenActivo,$aplicacionActivo,' ',$nombrePasivo,$origenPasivo,$aplicacionPasivo], '');

    if ($pdf->getY() > 175) {
        $pdf->addPage();
    }

    next($arrayActivo);
    $keyActivo = key($arrayActivo);

    next($arrayPasivoHacienda);
    $keyPasivoHacienda = key($arrayPasivoHacienda);
}

$pdf->Output();

function compararCuentas($array2018, $array2019)
{

    $arrayGlobal = [];
    //recuperamos la posicion inicial en el arreglo.
    reset($array2018);
    reset($array2019);

    $key2019 = key($array2019);
    $key2018 = key($array2018);

    while ($key2019 || $key2018) {
        //si las cadenas son iguales
        if (CompararCadenas(( string )$key2018, ( string )$key2019) == 0) {
            $arrayGlobal[$key2019]['cuenta'] = $array2019[$key2019]['cuenta'];
            $arrayGlobal[$key2019]['nombre'] = $array2019[$key2019]['nombre'];
            $arrayGlobal[$key2019]['nivel'] = $array2019[$key2019]['nivel'];
            $arrayGlobal[$key2019]['importe2019'] = $array2019[$key2019]['importe'];
            $arrayGlobal[$key2019]['importe2018'] = $array2018[$key2019]['importe'];

            next($array2019);
            $key2019 = key($array2019);

            next($array2018);
            $key2018 = key($array2018);
            //si no existe 2018 pero si en 2019
        } elseif (!$key2018 || CompararCadenas(( string )$key2019, ( string )$key2018) < 0) {//cuentas anteriores

            $arrayGlobal[$key2019]['cuenta'] = $array2019[$key2019]['cuenta'];
            $arrayGlobal[$key2019]['nombre'] = $array2019[$key2019]['nombre'];
            $arrayGlobal[$key2019]['nivel'] = $array2019[$key2019]['nivel'];
            $arrayGlobal[$key2019]['importe2019'] = $array2019[$key2019]['importe'];
            $arrayGlobal[$key2019]['importe2018'] = (isset($arrayGlobal[$key2019]['importe2018']) ? $arrayGlobal[$key2019]['importe2018'] : 0.00);

            next($array2019);
            $key2019 = key($array2019);

        } else {
            //si no existe en 2019 pero si en 2018
            $arrayGlobal[$key2018]['cuenta'] = $array2018[$key2018]['cuenta'];
            $arrayGlobal[$key2018]['nombre'] = $array2018[$key2018]['nombre'];
            $arrayGlobal[$key2018]['nivel'] = $array2018[$key2018]['nivel'];
            $arrayGlobal[$key2018]['importe2019'] = (isset($arrayGlobal[$key2018]['importe2019']) ? $arrayGlobal[$key2018]['importe2019'] : 0.00);
            $arrayGlobal[$key2018]['importe2018'] = $array2018[$key2018]['importe'];

            next($array2018);
            $key2018 = key($array2018);

        }
    }

    return $arrayGlobal;

}
//FUNCION PARA DEVOLVER ARRAY ORDENADO
function devuelveArrayOrdenado($array, $p)
{
    $arreglo = [];
    $total = 0;
    if (is_array($array)) {
        //naturaleza de la cuenta (activo)
        $naturalezaActivo = '';

        foreach ($array as $key => $val) {
            if ($p[$array[$key]['nivel']] == 1) {
                //Agregamos a la llave del arreglo 0's a la derecha
                $arreglo[str_pad($key, 5, '0', STR_PAD_RIGHT)] = $array[$key];
            }
        }

        //ordenamos arreglo
        ksort($arreglo);

        $debe = $haber = 0;
        foreach ($arreglo as $indice => $val) {

            if ($arreglo[$indice]['nivel'] == 1 && $arreglo[$indice]['ger_id'] == 1) {
                $naturalezaActivo = $arreglo[$indice]['naturalezaCuenta'];
            }

            //obtenemos los importes, si la naturaleza de la cuenta es Debe entonces se resta debe - haber, sino lo contrario
            $debe = (is_numeric($arreglo[$indice]['debe']) ? $arreglo[$indice]['debe'] : 0.00);
            $haber = (is_numeric($arreglo[$indice]['haber']) ? $arreglo[$indice]['haber'] : 0.00);
            $arreglo[$indice]['importe'] = (($arreglo[$indice]['naturalezaCuenta'] == 'D') ? ($debe - $haber) : ($haber - $debe));
            //Si es cuenta del activo y la naturaleza de la cuenta es diferente a la del genero se pone el importe en negativo.
            if ($arreglo[$indice]['ger_id'] == 1 && $arreglo[$indice]['naturalezaCuenta'] != $naturalezaActivo && $arreglo[$indice]['nivel'] >= 3) {
                $arreglo[$indice]['importe'] = ($arreglo[$indice]['importe'] * -1);
            }
        }

        //regresamos arreglo ordenado de manera ascendente
        return $arreglo;
    }

}

// Fin del código fuente
