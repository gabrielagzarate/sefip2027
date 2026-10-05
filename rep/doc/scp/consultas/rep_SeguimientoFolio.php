<?php
/**
 * Description of ControladorSeguimientoPrevisiones
 *
 * @author  Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 30-Sep-2020
 * Comment: -----
 * Objective: Muestra en pdf los datos para visualizar el seguimiento de folio.
 * Regla de Negocio: -----
 **/
session_start();
require_once dirname(__FILE__) . '/../../../../rep/lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../../../lib/formatoTexto.php';
require_once dirname(__FILE__) . '/../../../../lib/scp/consultas/consultaSeguimientoFolio.php';
require_once dirname(__FILE__) . '/../../../../ent/Usuario.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';

class rep_SeguimientoFolio extends PDF
{
    public $usr_id;
    public $nvj;
    public $perfil;
    public $CvePres;
    public $grupo;
    public $ur;
    public $usuario;
    public $longitud;
    public $ancho;
    public $imagen;
    public $logo;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

        $this->usr_id = $_SESSION[_S]['usr_id'];

        //variables POST
        $this->nvj = (isset($_POST['nvj']) && $_POST['nvj'] != '') ? $_POST['nvj'] : '';
        $this->perfil = (isset($_POST['perfil']) && $_POST['perfil'] != '') ? $_POST['perfil'] : '';
        $this->CvePres = (isset($_POST['cvePres']) && $_POST['cvePres'] != '') ? $_POST['cvePres'] : '';
        $this->grupo = (isset($_POST['grupo']) && $_POST['grupo'] != '') ? $_POST['grupo'] : '';
        $this->ur = (isset($_POST['ur']) && $_POST['ur'] != '') ? $_POST['ur'] : '';

        if ($this->usr_id != '' && $this->usr_id != 0) {
            $this->usuario = new Usuario();
            $this->usuario->setUsr_id($this->usr_id);
            $this->usuario->select();
        }
    } //fin construct

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

        $this->Image('../../../../img/Logo Institucional/114.jpg', $ix + 97, $iy - 1, '100', '18', 'JPG', '');

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
        $this->Cell(180, 5, 'CONTROL PRESUPUESTAL ' . APP_EJERCICIO, 0, 1, 'L');

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);

        $this->Cell(5, 5, utf8Decode("SECRETARÍA DE FINANZAS"), 0, 0, 'L');
        $this->Cell(163, 5, '', 0, 1, 'R');

        $ytmp = $this->GetY();
        $this->SetXY($ix + 7, $ytmp - 1);
        $this->SetFont('UniviaPro-Bold', 'B', 8);

        $this->Cell(180, 5, "SEGUIMIENTO DE FOLIO", 0, 1, 'L');

        $ytmp = $this->GetY();
        $this->setX(10);
        $this->setDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, $this->imagen + 12, $ytmp);

        $this->ln(3);
        $this->setDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->setX(10);
        $this->SetWidths(array(56, 15, 50, 25, $this->ancho - 78));
        $this->SetAligns(array('L', 'C', 'L', 'L', 'C'));
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->setAltoLinea(4);
        $this->SetRelleno("si");
        $this->Row(array('CLAVE PRESUPUESTAL', 'FOLIO', 'UNIDAD RESPONSABLE', 'ORIGEN', 'STATUS'));
        $this->ln(1);
        $this->SetAltoLinea(2);
        $this->SetRelleno("no");
    } //Fin Header

    public function Content($result)
    {

        while ($row = array_pop($result)) {
            $this->CheckPageBreak(30);
            $this->ln(2);
            $this->setAltoLinea(3);
            $this->setDrawColor(160, 160, 160);
            $this->SetX(10);
            $this->SetFont('UniviaPro-Regular', '', 7);
            $this->SetRelleno("no");
            $this->SetFillColor(235, 235, 235);
            $this->SetWidths(array(56, 15, 50, 25, $this->ancho - 78));
            $this->SetAligns(array('L', 'C', 'L', 'L', 'L'));
            $this->Row(array($row['cvePresupuestal'], $row['mod_id'], utf8Decode($row['unidadR']), utf8Decode($row['omo_nombre']), utf8Decode($row['emo_nombre'])));
            $this->SetFillColor(255, 255, 255);
            $this->SetRelleno("no");
            $this->setAltoLinea(1.5);
        } //fin while
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
        $this->MultiCell(63, 2, date("d/m/y") . ' - ' . date("H:i:s"), 0, 'R');

    } //fin Footer
} //fin Class

//variables POST
$nvj = (isset($_POST['nvj']) && $_POST['nvj'] != '') ? $_POST['nvj'] : '';
$perfil = (isset($_POST['perfil']) && $_POST['perfil'] != '') ? $_POST['perfil'] : '';
$CvePres = (isset($_POST['cvePres']) && $_POST['cvePres'] != '') ? $_POST['cvePres'] : '';
$grupo = (isset($_POST['grupo']) && $_POST['grupo'] != '') ? $_POST['grupo'] : '';
$ur = (isset($_POST['ur']) && $_POST['ur'] != '') ? $_POST['ur'] : '';

$pdf = new rep_SeguimientoFolio('L', 'mm', 'letter');
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->SetProtection(array('print', 'annot-forms'), "", PDF_PASSWORD_OWNER_SCP);

$pdf->setOrientation('P');

$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 40);
$pdf->setAltoLinea(1.5);
$pdf->AddPage();
$datos = consultaSeguimientoFolio($nvj, $perfil, $CvePres, $grupo, $ur);

$pdf->Content($datos);
$pdf->Output();
