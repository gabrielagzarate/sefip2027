<?php

session_start();
ini_set('memory_limit', '20M');
set_time_limit(0);
//librerias
require_once '../../../../rep/lib/reportetabla.1.8.php';
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../ent/Usuario.php';
require_once '../../../../lib/formatoTexto.php';
require_once '../../../../lib/TruncateCadena.php';
require_once '../../../../lib/tes/consulta/consultaPropuesta.php';
require_once '../../../../lib/helpers.php';

$fpa_id = (isset($_POST['fpa_id']) && $_POST['fpa_id'] != '') ? $_POST['fpa_id'] : '';
$msc_id = (isset($_POST['msc_id']) && $_POST['msc_id'] != '') ? $_POST['msc_id'] : '';
$lot_ejercicio = (isset($_POST['lot_ejercicio']) && $_POST['lot_ejercicio'] != '') ? $_POST['lot_ejercicio'] : '';
$lot_id = (isset($_POST['lot_id']) && $_POST['lot_id'] != '') ? $_POST['lot_id'] : '';
$lot_tipo = (isset($_POST['lot_tipo']) && $_POST['lot_tipo'] != '') ? $_POST['lot_tipo'] : '';
$lote = (isset($_POST['lote']) && $_POST['lote'] != '') ? $_POST['lote'] : '';

class reportetabla extends PDF
{
    public $gpo_id = '';
    public $ur_id = '';
    public $fecha = "";
    public $suma = 0.00;
    public $cadena;
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

        $this->usr_id = $_SESSION[_S]['usr_id'];

        if ($this->usr_id != '' && $this->usr_id != 0) {
            $this->usuario = new Usuario();
            $this->usuario->setUsr_id($this->usr_id);
            $this->usuario->select();
        }
    }

    //Encabezado del documento
    public function header()
    {
        switch ($this->getOrientation()) {
            case 'P':
                $this->longitud = 55;
                $this->ancho = 125;
                $this->imagen = 190;
                $this->logo = 122;
                break;
            case 'L':
                $this->longitud = 95;
                $this->ancho = 190;
                $this->imagen = 255;
                $this->logo = 186;
                break;
        } // fin switch

        $this->SetMargins(10, 10, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);

        $this->SetMargins(10, 1, 1, 1);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();
        $iyt = 0;

        $this->setRouteImg2("LOGO");
        $this->Image($this->getRouteImg(), $ix + 165, $iy - 3, '100', '29', 'JPG', '');

        $this->SetFont('UniviaPro-Bold', 'B', 16);
        $this->SetTextColor(200, 200, 200);
        $this->RotatedText($ix + 5, $iy + 18, $this->GetYear('y'), 90);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->SetXY($ix + 7, $iy + 4);
        $ytmp = $this->GetY();

        $this->Cell(195, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Regular', '', 8);
        $this->Cell(180, 5, utf8Decode('SECRETARIA DE FINANZAS TESORERIA ' . APP_EJERCICIO), 0, 1, 'L');

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt += 16;

        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(180, 5, 'COORDINACION DE CONTROL FINANCIERO', 0, 0, 'L');

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt += 2;
        $this->Cell(189, 5, 'DEPARTAMENTO DE PROGRAMACION DE MINISTRACIONES Y PAGOS', 0, 0, 'L');

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt += 2;
        $this->Cell(189, 5, 'REPORTE DEL LOTE ' . $_POST['lote'], 0, 0, 'L');

        $this->SetY($iy + 3 + $iyt);
        $this->SetX($ix + 7);

        $this->ln(4);
        $this->SetDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->SetX(10);

        $this->SetWidths(array(5, 15, 10, 15, 30, 30, 20, 20, $this->ancho - 162, 27, 28, 13, 17));
        $this->SetAligns(array('L', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
        $this->SetArrayBorders(array('TLRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB'));
        $this->SetFont('UniviaPro-Bold', 'B', 6);
        $this->setAltoLinea(4);
        $this->SetRelleno("si");

        if ($_POST['fpa_id'] == 3 && $_POST['lot_tipo'] == 5) {
            $this->Row(
                array(
                    '#',
                    'FE.PAGO',
                    'CLC',
                    'ORDEN',
                    'UR RESPONSABLE',
                    'BENEFICIARIO',
                    'IMPORTE',
                    'BANCO',
                    'CTA CARGO',
                    'CTA NOMBRE',
                    'LINEA CAPTURA',
                    'CVE. FIN.',
                    'PROP PAGO',
                )
            );
        } else {
            $this->Row(
                array(
                    '#',
                    'FE.PAGO',
                    'CLC',
                    'ORDEN',
                    'UR RESPONSABLE',
                    'BENEFICIARIO',
                    'IMPORTE',
                    'BANCO',
                    'CTA CARGO',
                    'CTA NOMBRE',
                    'CTA DESTINO',
                    'CVE. FIN.',
                    'PROP PAGO',
                )
            );
        }

        $this->ln(1);
        $this->SetAltoLinea(2.5);
        $this->SetRelleno("no");
    }

    public function Content($registros)
    {
        while ($row = array_pop($registros)) {
            $this->CheckPageBreak(40);
            $this->fecha = $row['fecha_Pago'];
            if ($_POST['fpa_id'] == 3 && $_POST['lot_tipo'] == 5) {
                $this->SetFont('UniviaPro-Bold', 'B', 6);
                $this->SetWidths(array(5, 15, 10, 15, 30, 30, 20, 20, $this->ancho - 162, 27, 28, 13, 17));
                $this->SetAligns(array('L', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
                $this->SetArrayBorders(array('TLRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB'));
                $this->Row(array($row['contador'], $row['fecha_Pago'], $row['lotDet_clc'],
                    $row['Folio'], utf8Decode($row['responsable']), utf8Decode($row['lotDet_beneficiario']),
                    '$' . number_format($row['lotDet_importe'], 2), $row['nombre_banco'], $row['cargo'],
                    $row['nombre_cuenta'], $row['clc_lineaCaptura'], $row['cve_fin'], $row['lote_id']));
                $row['importe'] = str_replace(',', '', $row['importe']);
                if (is_numeric($row['importe'])) {
                    $this->suma += (float) $row['importe'];  // Asegura que se convierta correctamente a flotante
                } else {
                    $this->suma += 0.00;  // Maneja el caso de valores no válidos
                }
                $this->cadena .= $row['lote_egreso'] . "|" . $row['lotDet_clc'] . "|" . $row['lca_importe'] . "|" . $row['lote_id'];
            } else {
                $this->SetFont('UniviaPro-Bold', 'B', 6);
                $this->SetWidths(array(5, 15, 10, 15, 30, 30, 20, 20, $this->ancho - 162, 27, 28, 13, 17));
                $this->SetAligns(array('L', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
                $this->SetArrayBorders(array('TLRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB', 'TRB'));
                $this->Row(array($row['contador'], $row['fecha_Pago'], $row['lotDet_clc'],
                    $row['Folio'], utf8Decode($row['responsable']), utf8Decode($row['lotDet_beneficiario']),
                    '$' . number_format($row['lotDet_importe'], 2), $row['nombre_banco'], $row['cargo'],
                    $row['nombre_cuenta'], $row['destino'], $row['cve_fin'], $row['lote_id']));
                $this->suma += $row['lotDet_importe'];
                $this->cadena .= $row['lote_egreso'] . "|" . $row['lotDet_clc'] . "|" . $row['lotDet_importe'] . "|" . $row['lote_id'];
            }

        } //fin while
        $arrFecha = explode("-", $this->fecha);
        $mes = "";

        $this->SetFont('UniviaPro-Bold', 'B', 8);

        $this->ln(4);
        $this->setAltoLinea(3);
        $this->SetX(60);
        $this->MultiCell(180, 4, 'TOTAL: ' . number_format($this->suma, 2), 0, 'J');
        $this->ln(4);
        $this->SetX(15);
        $this->MultiCell(180, 4, 'CADENA ORIGINAL: ' . md5($this->cadena), 0, 'J');

        switch ($arrFecha[1]) {
            case 1:
                $mes = "Enero";
                break;
            case 2:
                $mes = "Febrero";
                break;
            case 3:
                $mes = "Marzo";
                break;
            case 4:
                $mes = "Abril";
                break;
            case 5:
                $mes = "Mayo";
                break;
            case 6:
                $mes = "Junio";
                break;
            case 7:
                $mes = "Julio";
                break;
            case 8:
                $mes = "Agosto";
                break;
            case 9:
                $mes = "Septiembre";
                break;
            case 10:
                $mes = "Octubre";
                break;
            case 11:
                $mes = "Noviembre";
                break;
            case 12:
            default:
                $mes = "Diciembre";
                break;

        } //fin switch

        $this->SetFont('UniviaPro-Bold', 'B', 8);

        $this->ln(4);
        $this->setAltoLinea(3);

        $y = $this->GetX();
        $x = 0;
        $this->SetY($y - 70);
        $this->setX($x + 57 + 25);
        $this->MultiCell(25 * 2 + 50, 0, utf8Decode('Reyes Mantecón, Oaxaca a ' . $arrFecha[2] . ' de ' . $mes . ' del ' . $arrFecha[0]), 0, 'R', false);
        $this->SetY($y - 57);
        $this->MultiCell(25 + 95, 3, 'ELABORADO Y REVISADO', 0, 'C', false);
        $this->SetY($y + 165);
        $this->MultiCell(25 + 95, 3, utf8Decode('LIC. GUADALUPE VIRGINIA CORTEZ MARTINEZ'), 0, 'C', false);
        $this->SetY($y - 60);
        $this->setX($x - 139);
        $this->MultiCell(25 + 95, 3, utf8Decode('AUTORIZADO'), 0, 'C', false);
        $this->setX($x - 150);
        $this->SetY($y + 162);
        $this->MultiCell(25 + 352, 3, utf8Decode('  IVÁN ELADIO GÓMEZ SANTIAGO'), 0, 'C', false);
        $this->setX($x - 150);
        $this->SetY($y + 168);
        $this->MultiCell(25 + 350, 3, utf8Decode('  DEPARTAMENTO DE PLANEACION Y EVALUACION FINANCIERA'), 0, 'C', false);
    } //fin content

    public function footer($lastPage = true)
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
        $this->MultiCell(63, 2, date("d/m/y") . ' - ' . date("H:i:s"), 0, 'R');
    } //fin footer

} //fin de la clase

$pdf = new reportetabla('L', 'mm', 'Letter');
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->SetProtection(array('print', 'annot-forms'), "", PDF_PASSWORD_OWNER_TES); //password PDF

$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 30);
$pdf->setAltoLinea(1.5);
$pdf->AddPage();

$registros = consultaPropuesta($lot_tipo, $fpa_id, $msc_id, $lot_ejercicio, $lot_id);
$registros = array_reverse($registros);
$pdf->Content($registros);

$pdf->Output();
