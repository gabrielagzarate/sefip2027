<?php

if (!function_exists('u8d')) {
    function u8d($value)
    {
        return mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
    }
}

/**
 * Description of ControladorObservarSeguimientoGastos
 *
 * @author  Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 23-Ene-2020
 * Comment: -----
 * Objective: Muestra en PDF los datos de gatos mensuales.
 * Regla de Negocio: -----
 **/
session_start();
require_once dirname(__FILE__) . '/../../../../rep/lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../../../lib/formatoTexto.php';
require_once dirname(__FILE__) . '/../../../../lib/poa/seguimiento/consultaGastosOperacion.php';
require_once dirname(__FILE__) . '/../../../../ent/Usuario.php';

class rep_GastosOperacion extends PDF
{
    private $usr_id;
    private $mes;
    private $trimestre;
    private $grupo;
    private $ur;
    private $unidadResp;
    private $titulo;
    private $usuario;
    private $longitud;
    private $ancho;
    private $imagen;
    private $logo;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);
        $this->usr_id = $_SESSION[_S]['usr_id'];

        $this->mes = (isset($_POST['mes']) && $_POST['mes'] != '') ? $_POST['mes'] : '';
        $this->trimestre = (isset($_POST['trimestre']) && $_POST['trimestre'] != '') ? $_POST['trimestre'] : '';
        $this->grupo = (isset($_POST['gpo']) && $_POST['gpo'] != '') ? $_POST['gpo'] : '';
        $this->ur = (isset($_POST['ur']) && $_POST['ur'] != '') ? $_POST['ur'] : '';
        $this->unidadResp = (isset($_POST['unidadResp']) && $_POST['unidadResp'] != '') ? $_POST['unidadResp'] : '';

        $titulo = '';
        if ($this->mes) {
            $nombreMes = ['', 'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
            $this->titulo = $nombreMes[$this->mes];
        } else {
            switch ($this->trimestre) {
                case 1:
                    $this->titulo = 'PRIMER TRIMESTRE';
                    break;
                case 2:
                    $this->titulo = 'SEGUNDO TRIMESTRE';
                    break;
                case 3:
                    $this->titulo = 'TERCER TRIMESTRE';
                    break;
                case 4:
                    $this->titulo = 'CUARTO TRIMESTRE';
                    break;
            }
        }

        if ($this->usr_id != '' && $this->usr_id != 0) {
            $this->usuario = new Usuario();
            $this->usuario->setUsr_id($this->usr_id);
            $this->usuario->select();
        }
    } //fin __construct

    public function Header()
    { //Encabezado del documento
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
        }

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

        if ($this->getOrientation() == 'L') {
            $this->Image('../../../../img/Logo Institucional/114.jpg', $ix + 164, $iy - 1, '100', '18', 'JPG', '');
        } else {
            $this->Image('../../../../img/Logo Institucional/114.jpg', $ix + 97, $iy - 1, '100', '18', 'JPG', '');
        }

        $this->SetFont('UniviaPro-Bold', 'B', 16);
        $this->SetTextColor(200, 200, 200);
        $this->RotatedText($ix + 5, $iy + 18, APP_EJERCICIO, 90);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->SetXY($ix + 7, $iy + 4);
        $ytmp = $this->GetY();
        $this->Cell(195, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Regular', '', 8);
        $this->Cell(180, 5, 'PROGRAMA OPERATIVO ANUAL ' . APP_EJERCICIO, 0, 1, 'L');

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);

        $this->Cell(5, 5, u8d($this->unidadResp), 0, 0, 'L');
        $this->Cell(163, 5, '', 0, 1, 'R');

        $ytmp = $this->GetY();
        $this->SetXY($ix + 7, $ytmp - 1);
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        if ($this->mes) {
            $title = 'MENSUAL';
        } else {
            $title = 'TRIMESTRAL';
        }

        $this->Cell(180, 5, u8d("SEGUIMIENTO GASTOS OPERACIÓN ") . $title, 0, 1, 'L');

        $ytmp = $this->GetY();
        $this->setX(10);
        $this->setDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, $this->imagen + 12, $ytmp);

    } //fin Header

    public function Content($result)
    {
        $this->ln(3);
        $this->setDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->setX(10);
        $this->SetWidths(array($this->ancho + 68));
        $this->SetAligns(array('C'));
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->setAltoLinea(4);
        $this->SetRelleno("si");
        $this->Row(array($this->titulo));
        $this->ln(1);
        $this->SetAltoLinea(2.5);
        $this->SetRelleno("no");

        $cvefin = '';
        $ur = '';
        $prg = '';
        $spr = '';
        $py = '';
        $oa = '';

        while ($row = array_pop($result)) {
            if ($row['cfi_clave'] != $cvefin) {
                $this->CheckPageBreak(30);
                $this->ln(2);
                $this->setAltoLinea(3);
                $this->setBorders('B');
                $this->setDrawColor(160, 160, 160);
                $this->SetX(10);
                $this->SetFont('UniviaPro-Bold', 'B', 7);
                $this->SetRelleno("no");
                $this->SetFillColor(235, 235, 235);
                $this->SetWidths(array(15, 17, $this->ancho + 36));
                $this->SetAligns(array('L', 'L', 'L'));
                $this->Row(array('CVFIN:', $row['cfi_clave'], u8d($row['cfi_nombre'])));
                $this->SetFillColor(255, 255, 255);
                $this->SetRelleno("no");
                $this->setAltoLinea(2);
                $this->ln(2);

                $cvefin = $row['cfi_clave'];
            }
            if ($row['prg_id'] != $prg) {
                $this->CheckPageBreak(30);
                $this->ln(2);
                $this->setAltoLinea(3);
                $this->setDrawColor(160, 160, 160);
                $this->SetX(10);
                $this->SetFont('UniviaPro-Regular', '', 7);
                $this->SetRelleno("no");
                $this->SetFillColor(235, 235, 235);
                $this->SetWidths(array(15, 17, $this->ancho + 36));
                $this->SetAligns(array('L', 'L', 'L'));
                $this->Row(array('PR:', $row['prg_id'], u8d($row['prg_nombre'])));
                $this->SetFillColor(255, 255, 255);
                $this->SetRelleno("no");
                $this->setAltoLinea(1.5);
            }
            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr) {
                $this->CheckPageBreak(30);
                $this->ln(2);
                $this->setAltoLinea(3);
                $this->setDrawColor(160, 160, 160);
                $this->SetX(10);
                $this->SetFont('UniviaPro-Regular', '', 7);
                $this->SetRelleno("no");
                $this->SetFillColor(235, 235, 235);
                $this->SetWidths(array(15, 17, $this->ancho + 36));
                $this->SetAligns(array('L', 'L', 'L'));
                $this->Row(array('SPR:', $row['spr_id'], u8d($row['spr_nombre'])));
                $this->SetFillColor(255, 255, 255);
                $this->SetRelleno("no");
                $this->setAltoLinea(1.5);
            }
            if ($row['prg_id'] != $prg || $row['spr_id'] != $spr || $row['py_id'] != $py) {
                $this->CheckPageBreak(30);
                $this->ln(2);
                $this->setAltoLinea(3);
                $this->setDrawColor(160, 160, 160);
                $this->SetX(10);
                $this->SetFont('UniviaPro-Regular', '', 7);
                $this->SetRelleno("no");
                $this->SetFillColor(235, 235, 235);
                $this->SetWidths(array(15, 17, 58, 23, 23, 23, 23, 23, 20, $this->ancho - 157));
                $this->SetAligns(array('L', 'L', 'L', 'R', 'C', 'C', 'C', 'C', 'R', 'C'));
                $this->Row(array('PYT:', $row['py_id'], u8d($row['py_nombre']), 'APROBADO', 'MODIFICADO', 'COMPROMETIDO', 'DEVENGADO', 'EJERCIDO', 'PRESUPUESTO', 'ESTADO'));
                $this->SetFillColor(255, 255, 255);
                $this->SetRelleno("no");
                $this->setAltoLinea(1.5);
            }
            $clase = ($row['eme_id'] != "" ? u8d("CON OBSERVACIÓN") : u8d("SIN OBSERVACIÓN"));
            $this->CheckPageBreak(30);
            $this->ln(2);
            $this->setAltoLinea(3);
            $this->setDrawColor(160, 160, 160);
            $this->SetX(10);
            $this->SetFont('UniviaPro-Regular', '', 6);
            $this->SetRelleno("no");
            $this->SetFillColor(235, 235, 235);
            $this->SetWidths(array(32, 10, 48, 23, 23, 23, 23, 23, 20, $this->ancho - 157));
            $this->SetAligns(array('L', 'L', 'L', 'R', 'R', 'R', 'R', 'R', 'R', 'R'));
            $this->Row(array('', $row['oa_id'], u8d($row['oa_nombre']), '$' . number_format($row['aprobado'], 2), '$' . number_format($row['vigente'], 2), '$' . number_format($row['comprometido'], 2), '$' . number_format($row['devengado'], 2), '$' . number_format($row['ejercido'], 2), $row['porcentajePresupuesto'] . '%', $clase));
            $this->SetFillColor(255, 255, 255);
            $this->SetRelleno("no");
            $this->setAltoLinea(1.5);

            $prg = $row['prg_id'];
            $spr = $row['spr_id'];
            $py = $row['py_id'];
        } // fin while
    } //fin Content

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

        $this->SetFont('univiapro', '', 5);
        $y = -7;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(57, 2, u8d($this->usuario->getUsr_id() . " - " . $this->usuario->getUsuario() . " "),
            0, 'L');

        $this->SetY($y);
        $this->setX($x + 57);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);

        $this->SetY($y);
        $this->setX($x + 57 + $distancia);
        $this->MultiCell(57, 2, u8d('Página ' . $this->PageNo() . '/{nb}'),
            0, 'C');

        $this->SetY($y);
        $this->setX($x + 114 + $distancia);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);

        $this->SetY($y);
        $this->setX($x + 104 + 2 * $distancia);
        $this->MultiCell(63, 2, date("d/m/y") . ' - ' . date("H:i:s"), 0, 'R');

    } //fin Footer

} //fin Class

$mes = (isset($_POST['mes']) && $_POST['mes'] != '') ? $_POST['mes'] : '';
$trimestre = (isset($_POST['trimestre']) && $_POST['trimestre'] != '') ? $_POST['trimestre'] : '';
$grupo = (isset($_POST['gpo']) && $_POST['gpo'] != '') ? $_POST['gpo'] : '';
$ur = (isset($_POST['ur']) && $_POST['ur'] != '') ? $_POST['ur'] : '';
$filtro = (isset($_POST['filtro']) && $_POST['filtro'] != '') ? $_POST['filtro'] : '';
$estado = (isset($_POST['estado']) && $_POST['estado'] != '') ? $_POST['estado'] : '';

$pdf = new rep_GastosOperacion('L', 'mm', 'letter');
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->SetProtection(array('print', 'annot-forms'), "", PDF_PASSWORD_OWNER_POA);

$pdf->setOrientation('L');

$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 40);
$pdf->setAltoLinea(1.5);
$pdf->AddPage();
$datos = consultaGastosOperacion($mes, $trimestre, $grupo, $ur, $filtro, $estado);

$pdf->Content($datos);
$pdf->Output();