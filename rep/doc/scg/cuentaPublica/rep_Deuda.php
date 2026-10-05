<?php

session_start();
//error_reporting("E_ERROR");
ini_set('memory_limit', '20M');

set_time_limit(0);

define('Ingresos', 4);
define('Gastos', 5);

/*ESTE REPORTE CORRESPONDE A LA OPCION DE ESTADO DE ACTIVIDADES.*/
require_once('../../../../ent/UnidadResponsable.php');
require_once('../../../../lib/scg/conciliacion/consolidacion.php');
require_once('../../../lib/reportetabla.1.8.php');
require_once('../../../../lib/helpers.php');
//Extendiendo la clase pdf para escribir el encabezado y pie de página
class reportetabla extends PDF
{
    public $mesDesde;
    public $mesHasta;
    public $firmante1;
    public $firmante2;
    public $firmante3;
    public $cargo1;
    public $cargo2;
    public $cargo3;
    public $leyenda;
    public $no;
    public $incCta;
    public $ue;
    public $numUe;
    public $ancho;
    public $anchoImagen;
    public $altoImagen;
    public $logo;
    public $nlineas;
    public function __construct()
    {
        parent::__construct();

        $this->mesDesde = isset($_POST['comboMesDesde']) ? $_POST['comboMesDesde'] : -1;//Informacion de acuerdo al mes
        $this->mesHasta = isset($_POST['comboMesHasta']) ? $_POST['comboMesHasta'] : -1;//Informacion de acuerdo al mes
        $this->firmante1 = isset($_POST['firmante1']) ? $_POST['firmante1'] : '';
        $this->firmante2 = isset($_POST['firmante2']) ? $_POST['firmante2'] : '';
        $this->firmante3 = isset($_POST['firmante3']) ? $_POST['firmante3'] : '';
        $this->cargo1 = isset($_POST['cargo1']) ? $_POST['cargo1'] : '';
        $this->cargo2 = isset($_POST['cargo2']) ? $_POST['cargo2'] : '';
        $this->cargo3 = isset($_POST['cargo3']) ? $_POST['cargo3'] : '';
        $this->leyenda = isset($_POST['leyenda']) ? $_POST['leyenda'] : '';
        $this->no = isset($_POST['no']) ? $_POST['no'] : '';
        $this->incCta = isset($_POST['incCta']) ? $_POST['incCta'] : '';
        $this->ue = isset($_POST['ue']) ? $_POST['ue'] : '';
        $this->numUe = isset($_POST['numUe']) ? $_POST['numUe'] : '';

    }

    // FIRMAS
    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    public function Footer($lastPage = true)
    {
        $this->AddFont('univiapro', '');
        $this->AddFont('univiapro', 'B');
        $row = $this->getDatos();
        $tmpb = $this->GetBorders();
        if ($this->leyenda == 1 || ($this->cargo1 != '' && $this->cargo2 != '' && $this->firmante1 != '' && $this->firmante2 != '')) {
            $this->SetY(-30);
            $this->Cell(190, 2, utf8Decode(''), 0, 0, 'C');
            $this->Ln(5);
        } else {
            $this->SetY(-25);
        }

        if ($lastPage) {
            // $this->Cell(190,2,utf8Decode(''),0,0,'C');
            // $this->Ln(5);
            if ($this->leyenda == 1) {
                $this->SetFont('univiapro', '', 7);
                if ($_SESSION[_S]['reportes'] == 'C97' && $this->mesDesde >= 1 && $this->mesHasta <= 3 && ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 122 && $_SESSION[_S]['nvj_id'] == 2)) {
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
            $this->MultiAlignCell(57, 3, utf8Decode($this->cargo2), 0, 0, 'C');
            $this->MultiAlignCell(97, 3, utf8Decode($this->cargo3), 0, 1, 'C');
            $this->Ln(4);
            $this->SetFont('univiapro', '', 7);
            $this->MultiAlignCell(97, 3, utf8Decode($this->firmante1), 0, 0, 'C');
            $this->MultiAlignCell(57, 3, utf8Decode($this->firmante2), 0, 0, 'C');
            $this->MultiAlignCell(97, 3, utf8Decode($this->firmante3), 0, 1, 'C');
        }
        $this->SetFont('univiapro', '', 7);

        //pie de página
        $this->SetY(-11);
        $this->SetFont('univiapro', '', 5);
        // $this->Cell(10,10,date("H:i:s"),0,-2,"R");
        $this->Cell(275, 10, utf8Decode('Página') . $this->PageNo() . '/{nb}', 0, 0, 'C');
        // $this->Cell(240,10,date("d/m/y"),0,2,"L");
    }


    // Marcos: 22/mar/2018
    // Intentaremos estandarizar la impresión del logo y las líneas de encabezado, para no estar inventando
    // el hilo negro en cada reporte.php
    // $orientacion: P para portrait, L para landscape
    public function drawHeadersAndLogo($orientacion, $linea1 = '', $linea2 = '', $linea3 = '', $linea4 = '', $linea5 = '', $linea6 = '', $linea7 = '')
    {

        $this->ancho = 282; // 297 que es el ancho del tamaño carta apaisada - 10

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
        if ($nlineas <= 4) {
            $this->SetY($this->GetY() + 2);
            $this->ln(5);
        }

        // Límite para asegurar la buena visilibidad del reporte y el correcto aprovechamiento de pantalla
        // Si sale sobreencimado con los encabezados de columna, tal vez haya que revisar las líneas del encabezado
        $this->setY(35);

    }








    // ENCABEZADO
    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    public function Header()
    {
        /*SE IMPRIME EL ENCABEZADO DEL REPORTE.*/
        $this->AddFont('univiapro', '');
        $this->AddFont('univiapro', 'B');
        $this->ejercicio = '';
        $this->gpo_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['s24-gpo'];
        $this->ur_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['s24-ur'];

        if ($this->ejercicio == '') {
            $this->ur = new UnidadResponsable();
            $this->ur->setGpo_id($this->gpo_id);
            $this->ur->setUr_id($this->ur_id);
            $this->ur->select();
        }

        $ix = $this->GetX();
        $iy = $this->GetY();
        if ($this->no == 1) {
            $this->SetFont('univiapro', 'B', 50);
            $this->SetTextColor(200, 200, 200);
            $this->RotatedText($ix + 30, $iy + 160, utf8Decode('NO APLICA'), 45);
            $this->RotatedText($ix + 150, $iy + 160, utf8Decode('NO APLICA'), 45);
            $this->RotatedText($ix, $iy + 80, utf8Decode('NO APLICA'), 45);
            $this->RotatedText($ix + 150, $iy + 80, utf8Decode('NO APLICA'), 45);
        }

        $row = $this->getDatos();

        if ($_SESSION[_S]['reportes'] != 'C97') {
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
                    $this->SetXY($ix + 15, $iy + 6);
                    $ytmp = $this->GetY();
                    $this->Cell(235, 3, utf8Decode('Dirección de Contabilidad Gubernamental'), 0, 0, 'C');
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
					SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = ? AND ur_id = ? AND ue_id = ?;
				');
            $ejecutora->consultar([$this->gpo_id,$this->ur_id,$this->ue]);
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

        $linea5 = utf8Decode('Estado Analítico de la Deuda y Otros Pasivos');

        $mesDesde = (isset($_POST['comboMesDesde']) && $_POST['comboMesDesde'] != '') ? $_POST['comboMesDesde'] : '';

        $sqlMes = ' select msc_nombre, msc_dias from cat_MesContable where msc_id= ? ;';
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sqlMes);
        $con->consultar([$mesDesde]);
        $con->desconectar();
        $rowMes = $con->extraer();

        $mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta'] != '') ? $_POST['comboMesHasta'] : '';

        $sqlMes2 = ' select msc_nombre, msc_dias from cat_MesContable where msc_id= ? ;';
        $con2 = new AccesoDatos();
        $con2->conectar();
        $con2->setSql($sqlMes2);
        $con2->consultar([$mesHasta]);
        $con2->desconectar();
        $rowMes2 = $con2->extraer();

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

        if (isset($_POST['MilPes'])) {
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
        $this->SetFont('univiapro', 'B', 8);
        $this->SetTextColor(0, 0, 0);
        $this->setX(10);
        //$this->SetDrawColor(180,180,180);
        // $this->SetFillColor(220,220,220);
        $this->setDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->setAltoLinea(3);

        $this->SetBorders(1);
        $this->setAltoLinea(2.5);

        $this->SetWidths([150,30,30,30,30,30]);
        $this->SetAligns(['C','C','C','C','C']);
        $this->Row([	utf8Decode('DENOMINACIÓN DE LAS DEUDAS'),
                        utf8Decode('MONEDA DE CONTRATACIÓN'),
                        utf8Decode('INSTITUCION O PAÍS ACREEDOR'),
                        utf8Decode('SALDO INICIAL DEL PERIODO'),
                        utf8Decode('SALDO FINAL DEL PERIODO'),
                    ]);
        $this->setRelleno('no');
        $this->SetBorders('LR');

    }

}

$mesDesde = (isset($_POST['comboMesDesde']) != '') ? $_POST['comboMesDesde'] : -1;
$mesHasta = (isset($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta'] : -1;
$tipoReporte = (isset($_POST['tipoReporte']) != '') ? $_POST['tipoReporte'] : '';
$MilPesos = (isset($_POST['MilPes']) != '') ? 1000 : 1;
$grupos = isset($_POST['grupos']) ? $_POST['grupos'] : '';
$excluir = isset($_POST['excluir']) ? $_POST['excluir'] : '';
$cc = isset($_POST['cc']) && $_POST['cc'] == 1 ? $_POST['cc'] : '';
$ue = isset($_POST['ue']) ? $_POST['ue'] : '';

//cuerpo de la tabla
$pdf = new Reportetabla('L', 'mm', 'Letter');
$pdf->SetProtection(['print','annot-forms'], '', PDF_PASSWORD_OWNER_SCG);
$pdf->setOrientation('L');
$pdf->AddFont('univiapro', '');
$pdf->AddFont('univiapro', 'B');
$pdf->SetFont('univiapro', '', 7);
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetWidths([150,44,44,44,44]);
$pdf->SetAligns(['L','R','R','R','R']);

$arrayApe2014 = consultaCuentas($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 0, $mesDesde - 1, '', $tipoReporte, $grupos, $excluir, $pdf->incCta);
$arrayDesdeHasta2014 = consultaCuentas($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesDesde, $mesHasta, '', $tipoReporte, $grupos, $excluir, $pdf->incCta);

$ix = 10;
$iy = $pdf->GetY();
$res2131 = ($arrayApe2014[2131]['resultado'] + $arrayDesdeHasta2014[2131]['resultado']);
$res223 = ($arrayApe2014[223]['resultado'] + $arrayDesdeHasta2014[223]['resultado']);
$res2116 = ($arrayApe2014[2116]['resultado'] + $arrayDesdeHasta2014[2116]['resultado']);

$totalApe = ($arrayApe2014[2131]['resultado'] + $arrayApe2014[223]['resultado'] + $arrayApe2014[2116]['resultado']) / $MilPesos;
$totalPeriodo = ($res2131 + $res223 + $res2116) / $MilPesos;

// Como la mayoría de los conceptos NO tienen cuenta contable, lo remarcamos en la impresión
// para validación, para que vayan checando ésta situación
if ($pdf->incCta == '1') {
    $strPrefijo = '?.?.?.? ';
} else {
    $strPrefijo = '';
}


$pdf->ln(1);

$pdf->setX(10);

$fill = false;
$pdf->SetFont('univiapro', 'B', 9);
$pdf->Cell(25, 3, utf8Decode('DEUDA PÚBLICA'), 0, 0, 'L', $fill);
$pdf->ln(3);
$pdf->SetXY($ix + 40, $iy + 5);
$pdf->Cell(35, 3, 'Corto Plazo', 0, 0, 'L', $fill);
$pdf->ln(3);
$pdf->SetXY($ix + 20, $iy + 8);
$pdf->Cell(35, 3, 'Deuda Interna', 0, 0, 'L', $fill);
$pdf->ln(3);
$pdf->SetXY($ix + 40, $iy + 11);
$pdf->SetFont('univiapro', '', 9);

// Si quieren -para efectos de validación- incluir la cuenta en el nombre...
if ($pdf->incCta == '1') {
    $strPasoTexto = '2.1.3.1 Instituciones de Crédito';
} else {
    $strPasoTexto = 'Instituciones de Crédito';
}

$pdf->Cell(35, 3, utf8Decode($strPasoTexto), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 160, $iy + 11);
$pdf->Cell(35, 3, utf8Decode('PESOS'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 190, $iy + 11);
$pdf->Cell(35, 3, utf8Decode('MEXICO'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 11);
if ($_SESSION[_S]['reportes'] == 'C117') {
    $pdf->Cell(35, 3, number_format(round($arrayApe2014[2131]['resultado'] / $MilPesos, 0), 0), 0, 0, 'R', $fill);
} else {
    $pdf->Cell(35, 3, number_format($arrayApe2014[2131]['resultado'] / $MilPesos, 2), 0, 0, 'R', $fill);
}
$pdf->SetXY($ix + 235, $iy + 11);
if ($_SESSION[_S]['reportes'] == 'C117') {
    $pdf->Cell(35, 3, number_format(round($res2131 / $MilPesos, 0), 0), 0, 0, 'R', $fill);
} else {
    $pdf->Cell(35, 3, number_format($res2131 / $MilPesos, 2), 0, 0, 'R', $fill);
}
$pdf->ln(3);
$pdf->SetXY($ix + 40, $iy + 14);
$pdf->Cell(35, 3, utf8Decode($strPrefijo . 'Titulos y Valores'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 14);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->SetXY($ix + 235, $iy + 14);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->ln(3);
$pdf->SetXY($ix + 40, $iy + 17);
$pdf->Cell(35, 3, utf8Decode($strPrefijo . 'Arrendamientos Financieros'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 17);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->SetXY($ix + 235, $iy + 17);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->ln(3);
$pdf->SetXY($ix + 20, $iy + 21);
$pdf->SetFont('univiapro', 'B', 9);
$pdf->Cell(35, 3, 'Deuda Externa', 0, 0, 'L', $fill);
$pdf->ln(3);
$pdf->SetXY($ix + 40, $iy + 25);
$pdf->SetFont('univiapro', '', 9);
$pdf->Cell(35, 3, utf8Decode($strPrefijo . 'Organismos Financieros Internacionales'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 25);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->SetXY($ix + 235, $iy + 25);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->ln(3);
$pdf->SetXY($ix + 40, $iy + 28);
$pdf->Cell(35, 3, utf8Decode($strPrefijo . 'Deuda Bilateral'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 28);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->SetXY($ix + 235, $iy + 28);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->ln(3);
$pdf->SetXY($ix + 40, $iy + 31);
$pdf->Cell(35, 3, utf8Decode($strPrefijo . 'Titulos y Valores'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 31);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->SetXY($ix + 235, $iy + 31);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->ln(3);
$pdf->SetXY($ix + 40, $iy + 34);
$pdf->Cell(35, 3, utf8Decode($strPrefijo . 'Arrendamientos Financieros'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 34);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->SetXY($ix + 235, $iy + 34);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->ln(5);
$pdf->SetXY($ix + 80, $iy + 40);
$pdf->SetFont('univiapro', 'B', 9);
$pdf->Cell(35, 3, utf8Decode('Subtotal a Corto Plazo'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 40);
if ($_SESSION[_S]['reportes'] == 'C117') {
    $pdf->Cell(35, 3, number_format(round($arrayApe2014[2131]['resultado'] / $MilPesos, 0), 0), 0, 0, 'R', $fill);
} else {
    $pdf->Cell(35, 3, number_format($arrayApe2014[2131]['resultado'] / $MilPesos, 2), 0, 0, 'R', $fill);
}
$pdf->SetXY($ix + 235, $iy + 40);
if ($_SESSION[_S]['reportes'] == 'C117') {
    $pdf->Cell(35, 3, number_format(round($res2131 / $MilPesos, 0), 0), 0, 0, 'R', $fill);
} else {
    $pdf->Cell(35, 3, number_format($res2131 / $MilPesos, 2), 0, 0, 'R', $fill);
}
$pdf->ln(6);
$pdf->SetXY($ix + 40, $iy + 44);
$pdf->Cell(35, 3, 'Largo Plazo', 0, 0, 'L', $fill);
$pdf->ln(3);
$pdf->SetXY($ix + 20, $iy + 52);
$pdf->Cell(35, 3, 'Deuda Interna', 0, 0, 'L', $fill);
$pdf->ln(3);
$pdf->SetXY($ix + 40, $iy + 56);
$pdf->SetFont('univiapro', '', 9);

// Si quieren -para efectos de validación- incluir la cuenta en el nombre...
if ($pdf->incCta == '1') {
    $strPasoTexto = '2.2.3 Instituciones de Crédito';
} else {
    $strPasoTexto = 'Instituciones de Crédito';
}

$pdf->Cell(35, 3, utf8Decode($strPasoTexto), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 160, $iy + 56);
$pdf->Cell(35, 3, utf8Decode('PESOS'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 190, $iy + 56);
$pdf->Cell(35, 3, utf8Decode('MEXICO'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 56);
if ($_SESSION[_S]['reportes'] == 'C117') {
    $pdf->Cell(35, 3, number_format(round($arrayApe2014[223]['resultado'] / $MilPesos, 0), 0), 0, 0, 'R', $fill);
} else {
    $pdf->Cell(35, 3, number_format($arrayApe2014[223]['resultado'] / $MilPesos, 2), 0, 0, 'R', $fill);
}
$pdf->SetXY($ix + 235, $iy + 56);
if ($_SESSION[_S]['reportes'] == 'C117') {
    $pdf->Cell(35, 3, number_format(round($res223 / $MilPesos, 0), 0), 0, 0, 'R', $fill);
} else {
    $pdf->Cell(35, 3, number_format($res223 / $MilPesos, 2), 0, 0, 'R', $fill);
}
$pdf->ln(3);
$pdf->SetXY($ix + 40, $iy + 59);
$pdf->Cell(35, 3, utf8Decode($strPrefijo . 'Titulos y Valores'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 59);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->SetXY($ix + 235, $iy + 59);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->ln(3);
$pdf->SetXY($ix + 40, $iy + 62);
$pdf->Cell(35, 3, utf8Decode($strPrefijo . 'Arrendamientos Financieros'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 62);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->SetXY($ix + 235, $iy + 62);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->ln(3);
$pdf->SetXY($ix + 20, $iy + 66);
$pdf->SetFont('univiapro', 'B', 9);
$pdf->Cell(35, 3, 'Deuda Externa', 0, 0, 'L', $fill);
$pdf->ln(3);
$pdf->SetXY($ix + 40, $iy + 70);
$pdf->SetFont('univiapro', '', 9);
$pdf->Cell(35, 3, utf8Decode($strPrefijo . 'Organismos Financieros Internacionales'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 70);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->SetXY($ix + 235, $iy + 70);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->ln(3);
$pdf->SetXY($ix + 40, $iy + 73);
$pdf->Cell(35, 3, utf8Decode($strPrefijo . 'Deuda Bilateral'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 73);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->SetXY($ix + 235, $iy + 73);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->ln(3);
$pdf->SetXY($ix + 40, $iy + 76);
$pdf->Cell(35, 3, utf8Decode($strPrefijo . 'Titulos y Valores'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 76);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->SetXY($ix + 235, $iy + 76);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->ln(3);
$pdf->SetXY($ix + 40, $iy + 79);
$pdf->Cell(35, 3, utf8Decode($strPrefijo . 'Arrendamientos Financieros'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 79);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->SetXY($ix + 235, $iy + 79);
$pdf->Cell(35, 3, number_format(0, 0), 0, 0, 'R', $fill);
$pdf->ln(5);
$pdf->SetXY($ix + 80, $iy + 84);
$pdf->SetFont('univiapro', 'B', 9);
$pdf->Cell(35, 3, utf8Decode('Subtotal a Largo Plazo'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 84);
if ($_SESSION[_S]['reportes'] == 'C117') {
    $pdf->Cell(35, 3, number_format(round($arrayApe2014[223]['resultado'] / $MilPesos, 0), 0), 0, 0, 'R', $fill);
} else {
    $pdf->Cell(35, 3, number_format($arrayApe2014[223]['resultado'] / $MilPesos, 2), 0, 0, 'R', $fill);
}
$pdf->SetXY($ix + 235, $iy + 84);
if ($_SESSION[_S]['reportes'] == 'C117') {
    $pdf->Cell(35, 3, number_format(round($res223 / $MilPesos, 0), 0), 0, 0, 'R', $fill);
} else {
    $pdf->Cell(35, 3, number_format($res223 / $MilPesos, 2), 0, 0, 'R', $fill);
}
$pdf->ln(3);
$pdf->SetXY($ix, $iy + 90);
$pdf->Cell(35, 3, 'OTROS PASIVOS', 0, 0, 'L', $fill);
$pdf->ln(3);
$pdf->SetXY($ix + 40, $iy + 93);
$pdf->SetFont('univiapro', '', 9);

// Si quieren -para efectos de validación- incluir la cuenta en el nombre...
if ($pdf->incCta == '1') {
    $strPasoTexto = '2.1.1.6 OTROS';
} else {
    $strPasoTexto = 'OTROS';
}

$pdf->Cell(35, 3, utf8Decode($strPasoTexto), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 160, $iy + 93);
$pdf->Cell(35, 3, utf8Decode('PESOS'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 190, $iy + 93);
$pdf->Cell(35, 3, utf8Decode('MEXICO'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 93);
if ($_SESSION[_S]['reportes'] == 'C117') {
    $pdf->Cell(35, 3, number_format(round($arrayApe2014[2116]['resultado'] / $MilPesos, 0), 0), 0, 0, 'R', $fill);
} else {
    $pdf->Cell(35, 3, number_format($arrayApe2014[2116]['resultado'] / $MilPesos, 2), 0, 0, 'R', $fill);
}
$pdf->SetXY($ix + 235, $iy + 93);
if ($_SESSION[_S]['reportes'] == 'C117') {
    $pdf->Cell(35, 3, number_format(round($res2116 / $MilPesos, 0), 0), 0, 0, 'R', $fill);
} else {
    $pdf->Cell(35, 3, number_format($res2116 / $MilPesos, 2), 0, 0, 'R', $fill);
}
$pdf->ln(3);
$pdf->SetXY($ix + 40, $iy + 96);
$pdf->SetFont('univiapro', 'B', 9);


$pdf->Cell(35, 3, utf8Decode('SUBTOTAL OTROS PASIVOS'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 96);
if ($_SESSION[_S]['reportes'] == 'C117') {
    $pdf->Cell(35, 3, number_format(round($arrayApe2014[2116]['resultado'] / $MilPesos, 0), 0), 0, 0, 'R', $fill);
} else {
    $pdf->Cell(35, 3, number_format($arrayApe2014[2116]['resultado'] / $MilPesos, 2), 0, 0, 'R', $fill);
}
$pdf->SetXY($ix + 235, $iy + 96);
if ($_SESSION[_S]['reportes'] == 'C117') {
    $pdf->Cell(35, 3, number_format(round($res2116 / $MilPesos, 0), 0), 0, 0, 'R', $fill);
} else {
    $pdf->Cell(35, 3, number_format($res2116 / $MilPesos, 2), 0, 0, 'R', $fill);
}
$pdf->ln(3);
$pdf->SetXY($ix + 40, $iy + 99);


$pdf->Cell(35, 3, utf8Decode('TOTAL DEUDA Y OTROS PASIVOS'), 0, 0, 'L', $fill);
$pdf->SetXY($ix + 205, $iy + 99);
if ($_SESSION[_S]['reportes'] == 'C117') {
    $pdf->Cell(35, 3, number_format(round($totalApe, 0), 0), 0, 0, 'R', $fill);
} else {
    $pdf->Cell(35, 3, number_format($totalApe, 2), 0, 0, 'R', $fill);
}
$pdf->SetXY($ix + 235, $iy + 99);
if ($_SESSION[_S]['reportes'] == 'C117') {
    $pdf->Cell(35, 3, number_format(round($totalPeriodo, 0), 0), 0, 0, 'R', $fill);
} else {
    $pdf->Cell(35, 3, number_format($totalPeriodo, 2), 0, 0, 'R', $fill);
}

$pdf->Output();
?>
<?php

function consultaCuentas($gpo_id, $ur_id, $ue, $mesDesde, $mesHasta, $ejercicio, $tipoReporte, $grupos, $excluir, $incCta)
{

    if ($tipoReporte == '') {
        $tipoReporte = 1;
    }

    $ejercicio2 = (($ejercicio != '') ? 'ctrl' . $ejercicio . '.' : '');

    $otroWhere = '';
    if ($tipoReporte == 3 && $_SESSION[_S]['nvj_id'] >= 5) {
        // Si limitaron por grupo o excluyeron alguna UR
        if ($grupos != '') {
            $otroWhere .= '  WHERE gpo_id IN ( ' . rtrim($grupos) . ' ) AND ';

            // Si excluyeron a alguna UR
            if ($excluir != '') {
                $otroWhere .= ' ' . ($grupos != '' ? '' : ' WHERE ') . ' CONCAT( gpo_id, ur_id ) NOT IN ( ' . rtrim($excluir) . ' ) AND ';
            }

            // Si no usaron las campos especiales, es un where estandar
        }
    }


    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql("
            SELECT cuenta, debe, haber, cgm_naturaleza FROM (
                SELECT if(isnull(MAX(cuenta)),'2131',MAX(cuenta)) cuenta,ger_id,cgp_id,rub_id,cgcu_id, IFNULL( MAX(debe), 0 ) debe, IFNULL( MAX(haber), 0 ) haber, MAX(sc.nat_id) cgm_naturaleza
                FROM cat_CgSubcuenta sc
                LEFT JOIN(
                    SELECT '2131' cuenta,ger_id,cgp_id,rub_id,cgcu_id, SUM(pdt_debe) debe, SUM(pdt_haber) haber, MAX(nat_id)  AS nat_id
                    FROM " . $ejercicio2 . 'cg_PolizaDetalle d
                    INNER JOIN cg_PolizaMaestro USING(gpo_id,ur_id,ue_id,msc_id,pot_id,prf_id,pma_id)
                    ' . ($tipoReporte == 2 ? ($_SESSION[_S]['nvj_id'] < 6 ? '   INNER JOIN cat_CargaTrabajo t USING( gpo_id, ur_id )
                    ' : '') . ' WHERE ' : (($tipoReporte == 3) ? $otroWhere : "WHERE gpo_id = $gpo_id AND ur_id = $ur_id " . ($ue != '' ? ' AND d.ue_id = ' . $ue . '' : '') . '
                    AND ')) . '
                     ' . ($mesDesde < 0 ? "msc_id <= $mesHasta" : ($mesDesde == $mesHasta ? "msc_id = $mesDesde" : "( msc_id BETWEEN $mesDesde AND $mesHasta )")) . "
                    AND ger_id=2 AND cgp_id=1 AND rub_id=3 AND cgcu_id=1
                    AND epl_id NOT IN ('C','H')
                     " . ($tipoReporte == 2 && $_SESSION[_S]['nvj_id'] < 6 ? 'AND t.usr_id = ' . $_SESSION[_S]['usr_id'] : '') . "
                )d USING (ger_id , cgp_id , rub_id , cgcu_id)
                WHERE ger_id=2 AND cgp_id=1 AND rub_id=3 AND cgcu_id=1
                GROUP BY ger_id,cgp_id,rub_id,cgcu_id
                UNION ALL
                SELECT if(isnull(MAX(cuenta)),'223',MAX(cuenta)) cuenta, ger_id,cgp_id,rub_id,'' cgcu_id, IFNULL( MAX(debe), 0 ) debe, IFNULL( MAX(haber), 0 ) haber, MAX(sc.nat_id) cgm_naturaleza
                FROM cat_CgSubcuenta sc
                LEFT JOIN(
                    SELECT '223' cuenta,ger_id,cgp_id,rub_id,MAX(cgcu_id) AS cgcu_id, SUM(pdt_debe) debe, SUM(pdt_haber) haber, MAX(nat_id)
                    FROM " . $ejercicio2 . 'cg_PolizaDetalle d
                    INNER JOIN cg_PolizaMaestro USING(gpo_id,ur_id,ue_id,msc_id,pot_id,prf_id,pma_id)
                    ' . ($tipoReporte == 2 ? ($_SESSION[_S]['nvj_id'] < 6 ? '   INNER JOIN cat_CargaTrabajo t USING( gpo_id, ur_id )
                    ' : '') . ' WHERE ' : (($tipoReporte == 3) ? $otroWhere : "WHERE gpo_id = $gpo_id AND ur_id = $ur_id " . ($ue != '' ? ' AND d.ue_id = ' . $ue . '' : '') . '
                    AND')) . '
                     ' . ($mesDesde < 0 ? "msc_id <= $mesHasta" : ($mesDesde == $mesHasta ? "msc_id = $mesDesde" : "( msc_id BETWEEN $mesDesde AND $mesHasta )")) . "
                    AND ger_id=2 and cgp_id=2 and rub_id=3 #and cgcu_id=1
                    AND epl_id NOT IN ('C','H')
                     " . ($tipoReporte == 2 && $_SESSION[_S]['nvj_id'] < 6 ? 'AND t.usr_id = ' . $_SESSION[_S]['usr_id'] : '') . "
                )d USING (ger_id , cgp_id , rub_id)
                WHERE ger_id=2 AND cgp_id=2 AND rub_id=3
                GROUP BY ger_id,cgp_id,rub_id
                UNION ALL
                SELECT if(isnull(MAX(cuenta)),'2116',MAX(cuenta)) cuenta, ger_id,cgp_id,rub_id,cgcu_id, IFNULL( MAX(debe), 0 ) debe, IFNULL( MAX(haber), 0 ) haber, MAX(sc.nat_id) cgm_naturaleza
                FROM cat_CgSubcuenta sc
                LEFT JOIN(
                    SELECT '2116' cuenta,ger_id,cgp_id,rub_id,cgcu_id, SUM(pdt_debe) debe, SUM(pdt_haber) haber, MAX(nat_id)
                    FROM " . $ejercicio2 . 'cg_PolizaDetalle d
                    INNER JOIN cg_PolizaMaestro USING(gpo_id,ur_id,ue_id,msc_id,pot_id,prf_id,pma_id)
                    ' . ($tipoReporte == 2 ? ($_SESSION[_S]['nvj_id'] < 6 ? '   INNER JOIN cat_CargaTrabajo t USING( gpo_id, ur_id )
                    ' : '') . ' WHERE ' : (($tipoReporte == 3) ? $otroWhere : "WHERE gpo_id = $gpo_id AND ur_id = $ur_id " . ($ue != '' ? ' AND d.ue_id = ' . $ue . '' : '') . '
                    AND')) . '
                     ' . ($mesDesde < 0 ? "msc_id <= $mesHasta" : ($mesDesde == $mesHasta ? "msc_id = $mesDesde" : "( msc_id BETWEEN $mesDesde AND $mesHasta )")) . "
                    AND ger_id=2 and cgp_id=1 and rub_id=1 and cgcu_id=6
                    AND epl_id NOT IN ('C','H')
                     " . ($tipoReporte == 2 && $_SESSION[_S]['nvj_id'] < 6 ? 'AND t.usr_id = ' . $_SESSION[_S]['usr_id'] : '') . '
                )d USING (ger_id , cgp_id , rub_id , cgcu_id)
                WHERE ger_id=2 and cgp_id=1 and rub_id=1 and cgcu_id=6
                GROUP BY ger_id,cgp_id,rub_id,cgcu_id
            )cuenta;
        ');

    $conexion->consultar();
    $conexion->desconectar();

    $registro = [];
    while ($rowRegistro = $conexion->extraer()) {

        $key = $rowRegistro['cuenta'];

        $registro[$key]['cuenta'] = $rowRegistro['cuenta'];
        $registro[$key]['debe'] = $rowRegistro['debe'];
        $registro[$key]['haber'] = $rowRegistro['haber'];
        $registro[$key]['naturalezaCuenta'] = $rowRegistro['cgm_naturaleza'];
        $registro[$key]['resultado'] = (($registro[$key]['naturalezaCuenta'] == 'H') ? $registro[$key]['haber'] - $registro[$key]['debe'] : $registro[$key]['debe'] - $registro[$key]['haber']);
    }

    return $registro;
}

// Fin del código fuente
