<?php
session_start();
/**
 * Description of ControladorRegistroModificaciones
 *
 * @author  Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 18-Dic-2018
 * Migration: 03-Oct-2019
 * Comment: -----
 * Objective: Muestra en pdf los movimientos del folio.
 * Regla de Negocio: -----
 **/

require_once '../../../../rep/lib/reportetabla.1.8.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../lib/scp/nivelFolioMovto.php';
require_once '../../../../lib/TruncateCadena.php';
require_once '../../../../ent/Usuario.php';
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../ent/scp/OrigenOperacion.php';
require_once '../../../../ent/scp/TipoModificacion.php';
require_once '../../../../lib/helpers.php';

class reporte_MovtoFolios extends PDF
{
    private $nvj;
    private $ue_id;
    private $usr_id;
    private $adm_gpo;
    private $adm_ur;
    public $omo_id;
    public $mod_gpo_id;
    public $mod_ur_id;
    public $mod_id;
    public $enc_oop;
    public $envia;
    private $ur_id;
    private $ue;
    private $ur;
    private $usuario;
    private $nombre;
    private $longitud;
    private $ancho;
    private $imagen;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

        //variables de sesión
        $this->nvj = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : '';
        $this->ue_id = isset($_SESSION[_S]['ue_id']) ? $_SESSION[_S]['ue_id'] : '';
        $this->usr_id = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : '';
        $this->adm_gpo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->adm_ur = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : '';

        $this->omo_id = (isset($_POST['omo_id']) ? $_POST['omo_id'] : '');
        $this->mod_gpo_id = (isset($_POST['mod_gpo_id']) ? $_POST['mod_gpo_id'] : '');
        $this->mod_ur_id = (isset($_POST['mod_ur_id']) ? $_POST['mod_ur_id'] : '');
        $this->mod_id = (isset($_POST['mod_id']) ? $_POST['mod_id'] : '');
        $this->enc_oop = (isset($_POST['enc_oop']) ? $_POST['enc_oop'] : '');
        $this->envia = (isset($_POST['envia']) ? $_POST['envia'] : '');
        $this->ur_id = (isset($_POST['mod_ur_id']) ? $_POST['mod_ur_id'] : '');

        //unidad responsable
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($this->adm_gpo);
        $this->ur->setUr_id($this->ur_id);
        $this->ur->select();
        $this->usr_id = $this->usr_id;

        if ($this->nvj < 2) {
            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($this->adm_gpo);
            $this->ue->setUr_id($this->adm_ur);

            //Obtener Ue
            if (empty($this->ue_id)) {
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $sql = "SELECT ue_id FROM cat_Usuario WHERE usr_id=?";
                $conexion->setSql($sql);
                $conexion->consultar([$this->usr_id]);
                $conexion->desconectar();
                $dato = $conexion->extraer();
                $ue = $dato['ue_id'];
            } else {
                $ue = $this->ue_id;
            }

            $this->ue->setUe_id($ue);
            $this->ue->select();
        }
        if ($this->usr_id != '' && $this->usr_id != 0) {
            $this->usuario = new Usuario();
            $this->usuario->setUsr_id($this->usr_id);
            $this->usuario->select();
        }

        switch ($this->envia) {
            case 'ur':
                $this->nombre = "UR";
                break;
            case 'planeacion':
                $this->nombre = "PL";
                break;
            case 'presupuesto':
                $this->nombre = "PR";
                break;
            case 'drh':
                $this->nombre = "DRH";
                break;
            case 'sefin':
                $this->nombre = "SEFIN";
                break;
            default:
                break;

        } //fin switch
    } // FIN __construct

    public function Header()
    {
        $enc_oop = new OrigenOperacion(array("enc_oop" => $_POST['enc_oop']));
        $tipoMod = new TipoModificacion(array("tmd_id" => $enc_oop->getTmd_id()));

        switch ($this->getOrientation()) {
            case 'P':
                $this->longitud = 60;
                $this->ancho = 123;
                $this->imagen = 168;
                break;
            case 'L':
                $this->longitud = 95;
                $this->ancho = 190;
                $this->imagen = 235;
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
        $iyt = $this->GetY();

        $this->SetFont('UniviaPro-Bold', 'B', 50);
        $this->SetTextColor(225, 225, 225);
        $this->RotatedText($ix + 20, $iy + 160, 'HOJA DE TRABAJO', 45);
        $this->RotatedText($ix + 80, $iy + 220, 'HOJA DE TRABAJO', 45);
        $this->RotatedText($ix, $iy + 80, 'HOJA DE TRABAJO', 45);
        $this->RotatedText($ix + 140, $iy + 100, 'HOJA DE TRABAJO', 45);

        $this->Image('../../../../img/'.LOGO_ESCUDO, $ix + 164, $iy - 1, '85', '18', 'JPG', '');

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
        $this->Cell(180, 5, utf8Decode('CONTROL PRESUPUESTAL ' . APP_EJERCICIO), 0, 1, 'L');

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt += 16;

        $nombre = TruncateCadena(utf8Decode(trim($this->nombre . ": " . $this->ur->getClave() . " - " . $this->ur->getNombre())), ($this->longitud - 10));
        $this->Cell(5, 5, substr($nombre, 0, ($this->longitud - 10)), 0, 0, 'L');
        if (strlen($nombre) > ($this->longitud - 10)) {
            $this->SetY($iy + $iyt);
            $this->SetX($ix + 7);
            $this->Cell(5, 5, substr($nombre, ($this->longitud - 10), ($this->longitud - 10)), 0, 0, 'L');
        }
        if (strlen($nombre) > ($this->longitud - 10) * 2) {
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->SetX($ix + 7);
            $this->Cell(5, 5, substr($nombre, ($this->longitud - 10) * 2, ($this->longitud - 10)), 0, 0, 'L');
        }
        if (strlen($nombre) > ($this->longitud - 10) * 3) {
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->SetX($ix + 7);
            $this->Cell(5, 5, substr($nombre, ($this->longitud - 10) * 3, ($this->longitud - 10)), 0, 0, 'L');
        }
        $ytmp = $this->GetY();
        $this->SetXY($ix + 7, $ytmp + 4);

        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(180, 5, utf8Decode('ADECUACIÓN PRESUPUESTAL - MOVIMIENTOS DEL FOLIO'), 0, 1, 'L');

        $ytmp = $this->GetY();
        $this->SetX(10);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, $this->imagen + 33, $ytmp);

        $this->ln(4);
        $this->SetDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->SetX(10);

        $this->SetFont('UniviaPro-Regular', '', 8);
        $this->SetWidths(array(185));
        $this->SetAligns(array('L'));
        $this->SetBorders(0);
        $this->Row(array(utf8Decode('NÚMERO DE FOLIO: ' . $_POST['mod_id'])));

        $this->ln(3);
        $this->SetFont('UniviaPro-Regular', '', 8);
        $this->SetWidths(array(185));
        $this->SetAligns(array('L'));
        $this->Row(array(utf8Decode('MODALIDAD DE ADECUACIÓN: ' . ($enc_oop->getMmd_id() == "E" ? 'EXTERNAS' : 'INTERNAS'))));

        $this->ln(3);
        $this->SetFont('UniviaPro-Regular', '', 8);
        $this->SetWidths(array(185));
        $this->SetAligns(array('L'));
        $this->Row(array(utf8Decode('TIPO DE ADECUACIÓN: ' . $tipoMod->getNombre())));

        $this->CheckPageBreak(25);
        $this->SetFont('UniviaPro-Regular', '', 7);
        $this->SetWidths(array(60, 70, 40, 20));
        $this->SetAligns(array('L', 'L', 'L', 'L'));

        $this->ln(4);
        $this->SetDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->SetX(10);

        $this->SetWidths(array(54, 10, $this->ancho - 70, 40, 34));
        $this->SetAligns(array('C', 'C', 'L', 'R', 'L'));
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->setAltoLinea(4);
        $this->SetRelleno("si");
        $this->Row(array('PARTIDA PRESUPUESTAL', 'TIPO', utf8Decode('DESCRIPCIÓN'), 'IMPORTE', 'TIPO MOVIMIENTO'));

        $this->ln(1);
        $this->SetAltoLinea(2);
        $this->SetRelleno("no");

    }

    public function Content($registros)
    {

        while ($registro = array_pop($registros)) {

            $this->SetFillColor(199, 209, 219);
            $this->setX(10);
            $this->SetWidths(array(54, 10, $this->ancho - 70, 40, 34));
            $this->SetAligns(array('L', 'C', 'L', 'R', 'L'));
            $this->SetFont('UniviaPro-Regular', '', 7);
            $this->setBorders('');
            $this->setAltoLinea(4);
            $this->SetRelleno("no");
            $this->Row(array($registro['clave'], $registro['tipo'], utf8Decode($registro['descripcion']), '$' . number_format($registro['importe'], 2), utf8Decode($registro['movimiento'])));
            $this->ln(1);
            $this->setBorders(0);
            $this->SetFillColor(255, 255, 255);
            $this->SetRelleno("no");

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

    }
} // FIN class reporte_MovtoFolios

$pdf = new reporte_MovtoFolios('L', 'mm', 'Letter');
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->SetProtection(array('print', 'annot-forms'), "", PDF_PASSWORD_OWNER_SCP); //password PDF

$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 40);
$pdf->setAltoLinea(1.5);
$pdf->AddPage();

$registros = nivelFolioMovto($pdf->omo_id, $pdf->mod_gpo_id, $pdf->mod_ur_id, $pdf->mod_id, $pdf->envia, 'PDF');

$pdf->Content($registros);

$pdf->Output();
