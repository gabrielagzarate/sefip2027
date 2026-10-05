<?php
session_start();
//ini_set('memory_limit','0');
ini_set('memory_limit', '500G');
set_time_limit(0);
//librerias
require_once('../../../../lib/helpers.php');
require_once('../../../lib/reportetabla.1.8.php');
require_once('../../../../lib/scg/consulta/DameSQL_LibroDiario.php');
require_once('../../../../lib/MesNombre.php');
require_once('../../../lib/conversionNumLetras.php');
require_once('../../../../lib/AccesoDatos.php');
require_once('../../../../ent/UnidadResponsable.php');
require_once('../../../../lib/calculaPeso.php');
class TodoLibroDiario extends PDF
{
    public $mes;

    public function __construct()
    {
        parent::__construct();
        switch ($_SESSION[_S]['nvj_id']) {
            case '8':break;
            case '7':break;
            case '6':break;
            case '5':break;
            case '4':break;
            case '3':break;
            case '2':break;
            case '1':break;
            default:
                break;
        }
    }

    function Footer($lastPage = true)
    {
        $this->SetY(-10);
        $this->SetY(-15);
        $this->SetFont('Arial', '', 6.5);
        $this->Cell(20, 10, date("H:i:s"), 0, 0, 'L');
        $this->Cell(150, 10, utf8Decode('Página ').$this->PageNo().'/{nb}', 0, 0, 'C');
        $this->Cell(20, 10, date("d/m/y"), 0, 0, 'R');
    }

    function Header()
    {//Encabezado del documento
        $row = $this->getDatos();
        $this->SetMargins(10, 10, 10, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $row = $this->getDatos();
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $this->setX(5);
        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->Image('../../../../img/logoOaxaca-color-fblanco.png', $ix + 125, $iy, 74, 22, 'png');
        $ytmp = $this->GetY();

        $this->SetFont('Arial', 'B', 8);
        $this->SetXY($ix, $iy + 10);
        $ytmp = $this->GetY();
        $this->Cell(195, 4, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
        $this->SetXY($ix, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(180, 4, utf8Decode('SECRETARÍA DE FINANZAS'), 0, 1, 'L');
        $this->ln(3);
        $this->SetXY($ix, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(180, 4, utf8Decode('LIBRO DIARIO CORRESPONDIENTE AL MES DE '.$this->mes.' DE '.substr($_SESSION[_S]['ejercicio'], 0, 4)), 0, 1, 'L');
        $this->ln(3);
        $ytmp = $this->GetY();
        $this->SetXY($ix, $ytmp - 3);
        $ytmp = $this->GetY();
        $this->SetFont('Arial', 'B', 6);
        $ytmp = $this->GetY();
        $this->ln(5);
        $this->setAltoLinea(2);
        $this->setRelleno("si");
        $this->SetFont('Arial', 'B', 5);
        $this->SetTextColor(0, 0, 0);
        $this->setX(5);
        $this->SetDrawColor(180, 180, 180);
        $this->SetFillColor(220, 220, 220);
        $this->SetBorders(1);
        $this->SetWidths(array(12, 12, 90, 25, 25, 10, 6, 6, 10));
        $this->SetAligns(array('L', 'L', 'L', 'R', 'R', 'C', 'L', 'L', 'C', 'C'));
        $this->Row(array(utf8Decode('Póliza'), utf8Decode('Día'), utf8Decode('Descripción de la Póliza'), 'Debe', 'Haber', 'MC/TMC', 'Edo', 'UE', 'Usuario'));
        $this->setX(5);
    }

    public function totales($totDebe, $totHaber, $bolTotalGeneral)
    {
        $this->setX(5);
        $this->Cell(114, 4,
            ( $bolTotalGeneral ? 'SUMAS IGUALES' : 'A la hoja No. '.( $this->PageNo()
                + 1 )), 'T', 0, 'R', false);
        $this->SetFillColor(230, 230, 230);
        $this->SetDrawColor(220, 220, 220);
        $this->SetWidths(array(25));
        $this->setAligns(array('R'));
        $this->setBorders(1);
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(25, 4, utf8Decode('$'.number_format($totDebe, 2)), 1, 0,
            'R', false);
        $this->Cell(25, 4, utf8Decode('$'.number_format($totHaber, 2)), 1, 0,
            'R', false);
        $this->Cell(47, 4, '', 'T', 0, 'R', false); // Solo para terminar de dibuajr la línea
        $this->SetRelleno("no");
        $this->SetTextColor(0, 0, 0);
        $this->setBorders(0);
    }
}
// Iniciamos el reporte
// --------------------------------------------------------------------------------------------------------------------------------
$gpo_id = $_SESSION[_S]['clasAdm-gpo'];
$ur_id = $_SESSION[_S]['clasAdm-ur'];

$msc_id = ( isset($_POST['msc']) && $_POST['msc'] != "" ) ? $_POST['msc'] : 1;
$orden = ( isset($_POST['ord']) && $_POST['ord'] != "" ) ? $_POST['ord'] : 'or_T';
$tipo = ( isset($_POST['pot']) && $_POST['pot'] != "" ) ? $_POST['pot'] : '';
$numero = ( isset($_POST['num']) && $_POST['num'] != "" ) ? $_POST['num'] : '';
$vinculacion = ( isset($_POST['vin']) && $_POST['vin'] != "" ) ? $_POST['vin'] : '';
$ue = ( isset($_POST['ue']) && $_POST['ue'] != "" ) ? $_POST['ue'] : '';
$tipoCLC = ( isset($_POST['clc']) && $_POST['clc'] != "" ) ? $_POST['clc'] : '';
$usuario = ( isset($_POST['usr']) && $_POST['usr'] != "" ) ? $_POST['usr'] : '';
$textoBuscar = ( isset($_POST['tB']) && $_POST['tB'] != "" ) ? $_POST['tB'] : '';

// Para la vinculación ( pdx_*_origen )
$clc_v = trim(( ( isset($_POST['clc_v']) ) ? $_POST['clc_v'] : ''));
$afe_v = trim(( ( isset($_POST['afe_v']) ) ? $_POST['afe_v'] : ''));
$cmp_v = trim(( ( isset($_POST['cmp_v']) ) ? $_POST['cmp_v'] : ''));
$bdo_v = trim(( ( isset($_POST['bdo_v']) ) ? $_POST['bdo_v'] : ''));

// Para la vinculación ( pdx_*_vinculacion )
$clc_o = trim(( ( isset($_POST['clc_o']) ) ? $_POST['clc_o'] : ''));
$afe_o = trim(( ( isset($_POST['afe_o']) ) ? $_POST['afe_o'] : ''));
$cmp_o = trim(( ( isset($_POST['cmp_o']) ) ? $_POST['cmp_o'] : ''));
$bdo_o = trim(( ( isset($_POST['bdo_o']) ) ? $_POST['bdo_o'] : ''));

// La referencia sólo es una
$ref_v = trim(( ( isset($_POST['ref_v']) ) ? $_POST['ref_v'] : ''));

$andOr = trim(( ( isset($_POST['andOr']) ) ? $_POST['andOr'] : '0'));
$andOr = ( $andOr == "0" ? "AND" : "OR" );

$filtroTexto = trim(( ( isset($_POST['txt']) ) ? $_POST['txt'] : ''));

// Bandera para indicar si pidieron filtros por vinculación, ya que en éstos casos
// NO limitamos por tipo de póliza
if ($clc_v != "" || $afe_v != "" || $cmp_v != "" || $bdo_v != "" || $clc_o != ""
    || $afe_o != "" || $cmp_o != "" || $bdo_o != "" || $ref_v != "") {
    $bolPidieronVinculacion = true;
} else {
    $bolPidieronVinculacion = false;
}

$sql = DameSQL_LibroDiario($msc_id, $orden, $tipo, $numero, $vinculacion, $ue,
    $tipoCLC, $usuario, $textoBuscar, $clc_v, $afe_v, $cmp_v, $bdo_v, $clc_o,
    $afe_o, $cmp_o, $bdo_o, $ref_v, $andOr, $bolPidieronVinculacion,
    $filtroTexto);

$pdf = new TodoLibroDiario('L', 'mm', 'Letter');
$pdf->SetProtection(array("print","annot-forms"), "", PDF_PASSWORD_OWNER_SCG);
$pdf->setOrientation('P');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 20);
$pdf->mes = MesNombre($msc_id);

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar();
$conexion->desconectar();
$row = $conexion->extraer();

$NUMREG = $conexion->getNumeroRegistros();
$columnas = (count((array)$row) * 2);

$peso = calcularPeso($NUMREG, $columnas);

ini_set('memory_limit', ($peso*20)."M");
/* variables  de montos */
$celda = 0;
$totalDebe = 0;
$totalHaber = 0;

$bolSoloUnaVez = true;

do {
    if ($bolSoloUnaVez) {
        $pdf->setDatos($row);
        $pdf->AddPage();
        $bolSoloUnaVez = false;
    }

    if ($pdf->GetY() + 14 > $pdf->getPageBreakTrigger()) {
        $pdf->totales($totalDebe, $totalHaber, false);
        $pdf->AddPage();
    }

    $pdf->setX(5);
    $pdf->setFillColor(240, 240, 240);
    $pdf->setDrawcolor(200, 200, 200);
    if ($celda == 1) {
        $pdf->setRelleno("si");
        $celda = 0;
    } else {
        $pdf->setRelleno("no");
        $celda = 1;
    }
    $pdf->SetFont('Arial', '', 6);
    $pdf->setBorders("LR");
    $pdf->SetWidths(array(12, 12, 90, 25, 25, 10, 6, 6, 10));
    $pdf->SetAligns(array('L', 'L', 'L', 'R', 'R', 'C', 'L', 'L', 'C', 'C', 'L'));
    $pdf->Row(array($row['poliza'], $row['dia'].( $msc_id != $row['mesPol'] ? "-".substr(MesNombre($row['mesPol']), 0, 3) : "" ),
        utf8Decode($row['concepto']),
        number_format($row['debe'], 2),
        number_format($row['haber'], 2),
        $row['mc']." / ".$row['tmc'],
        $row['edo'],
        $row['ue'],
        $row['u']
    ));

    if ($row['edo'] != 'H' && $row['edo'] != 'C') {
        $totalDebe += $row['debe'];
        $totalHaber += $row['haber'];
    }
} while ($row = $conexion->extraer());

$pdf->totales($totalDebe, $totalHaber, true);
ob_end_flush();
//ob_end_clean();
$pdf->Output();