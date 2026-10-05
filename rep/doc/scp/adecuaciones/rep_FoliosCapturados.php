<?php

/**
 * Description of ControladorRegistroModificaciones
 *
 * @author  Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 29-Nov-2018
 * Migration: 26-Nov-2019
 * Comment: ------
 * Objective: Muestra en pdf los folios que se dieron de alta.
 * Regla de Negocio: -----
 **/
session_start();
require_once '../../../../rep/lib/reportetabla.1.8.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../ent/Usuario.php';
require_once '../../../../lib/scp/nivelFolio.php';
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../lib/TruncateCadena.php';
require_once '../../../../lib/helpers.php';

class reporte_Folios extends PDF
{
    public $gpo_id;
    public $ur_id;
    public $ecf_id;
    public $envia;
    private $nvj;
    private $ssc;
    private $ue_id;
    private $usr_id;
    private $alta_folioUr;
    private $alta_folioRh;
    private $ur;
    private $ue;
    public $drh;
    private $presupuesto;
    private $planeacion;
    private $usuario;
    private $nombre;
    private $ancho;
    private $imagen;
    private $longitud;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

        $this->gpo_id = (isset($_POST['mod_gpo_id']) ? $_POST['mod_gpo_id'] : '');
        $this->ur_id = (isset($_POST['mod_ur_id']) ? $_POST['mod_ur_id'] : '');
        $ur = (isset($_POST['mod_ur_id']) ? $_POST['mod_ur_id'] : '');
        $this->ecf_id = (isset($_POST['ecf_id']) ? $_POST['ecf_id'] : '');
        $this->envia = (isset($_POST['envia']) ? $_POST['envia'] : '');

        $this->nvj = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : '';
        $this->ssc = isset($_SESSION[_S]['ssc_id']) ? $_SESSION[_S]['ssc_id'] : '';
        $this->ue_id = isset($_SESSION[_S]['ue_id']) ? $_SESSION[_S]['ue_id'] : '';
        $this->usr_id = isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : '';
        $this->alta_folioUr = isset($_SESSION[_S]['prm']['S']['1']['1']['7']) ? true : false;
        $this->alta_folioRh = isset($_SESSION[_S]['prm']['S']['1']['1']['82']) ? true : false;
        $this->ur = ($this->nvj != false && $this->nvj == 2 && $this->alta_folioUr == true ? true : false);
        $this->ue = ($this->nvj != false && $this->nvj == 1 ? true : false);
        $this->drh = ($this->alta_folioRh == true ? true : false);
        $this->presupuesto = ($this->ssc != false && $this->ssc == 3 ? true : false);
        $this->planeacion = ($this->ssc != false && $this->ssc == 2 ? true : false);

        $esSEFIN = $this->presupuesto || $this->planeacion;
        $esUR = $this->ur || $this->ue;

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($this->ur_id);
        $this->ur->select();

        if ($this->nvj < 2) {
            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $this->ue->setUr_id($_SESSION[_S]['clasAdm-ur']);

            //Obtener Ue
            if (empty($this->ue_id)) {
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $sql = 'SELECT ue_id FROM cat_Usuario WHERE usr_id=' . $this->usr_id;
                $conexion->setSql($sql);
                $conexion->consultar();
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
        } // fin usr_id

        switch ($this->envia) {
            case 'ur':
                $this->nombre = 'UR';
                break;
            case 'planeacion':
                $this->nombre = 'PL';
                break;
            case 'presupuesto':
                $this->nombre = 'PR';
                break;
            case 'drh':
                $this->nombre = 'DRH';
                break;
            case 'sefin':
                $this->nombre = 'SEFIN';
                break;
            default:
                break;

        } //fin switch
    } //FIN __construct

    public function Header()
    {
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
        $iyt = 0;

        $this->Image('../../../../img/' . LOGO_ESCUDO, $ix + 164, $iy - 1, '85', '18', 'JPG', '');

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

        $nombre = TruncateCadena(utf8Decode(trim($this->nombre . ': ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre())), ($this->longitud - 10));
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
        $this->Cell(180, 5, utf8Decode('ADECUACIÓN PRESUPUESTAL - LISTADO DE FOLIOS'), 0, 1, 'L');

        $ytmp = $this->GetY();
        $this->SetX(10);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, $this->imagen + 33, $ytmp);

        $this->ln(4);
        $this->SetDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->SetX(10);

        $this->SetWidths([15, 28, 44, $this->ancho - 88, 43, 26]);
        $this->SetAligns(['C', 'C', 'L', 'L', 'L', 'C']);
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->setAltoLinea(4);
        $this->SetRelleno('si');
        $this->Row(['FOLIO', 'MODALIDAD', utf8Decode('TIPO ADECUACIÓN'), utf8Decode('JUSTIFICACIÓN'), 'ESTADO', 'FECHA CAPTURA']);
        $this->ln(1);
        $this->SetAltoLinea(2);
        $this->SetRelleno('no');

    } //Fin Header

    public function Content($registros)
    {
        while ($registro = array_pop($registros)) {
            $fecha = $registro['mod_fechaFolio'];
            $newFecha = date('d-m-Y', strtotime($fecha));
            if (count((array) $fecha) > 0) {
                $this->CheckPageBreak(30);
                $this->ln(2);
                $this->setAltoLinea(3);
                $this->setDrawColor(160, 160, 160);
                $this->SetX(10);
                $this->SetFont('UniviaPro-Regular', '', 7);
                $this->SetRelleno('no');
                $this->SetFillColor(235, 235, 235);
                $this->SetWidths([15, 28, 44, $this->ancho - 88, 43, 26]);
                $this->SetAligns(['C', 'C', 'L', 'L', 'L', 'C']);
                $this->Row([$registro['mod_id'], $registro['mmd_id'], utf8Decode($registro['tmd_nombre']), utf8Decode($registro['mod_concepto']), utf8Decode($registro['emo_nombre']), $newFecha]);
                $this->SetFillColor(255, 255, 255);
                $this->SetRelleno('no');
                $this->setAltoLinea(1.5);
            }
        }

    } //FIN Content

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
        $this->MultiCell(
            57,
            2,
            utf8Decode($this->usuario->getUsr_id() . ' - ' . $this->usuario->getUsuario() . ' '),
            0,
            'L'
        );

        $this->SetY($y);
        $this->setX($x + 57);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);

        $this->SetY($y);
        $this->setX($x + 57 + $distancia);
        $this->MultiCell(
            57,
            2,
            utf8Decode('Página ' . $this->PageNo() . '/{nb}'),
            0,
            'C'
        );

        $this->SetY($y);
        $this->setX($x + 114 + $distancia);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);

        $this->SetY($y);
        $this->setX($x + 104 + 2 * $distancia);
        $this->MultiCell(63, 2, date('d/m/y') . ' - ' . date('H:i:s'), 0, 'R');

    } //FIN Footer

} // FIN class reporte_Folios

$pdf = new reporte_Folios('L', 'mm', 'letter');
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_SCP); //password PDF

$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 40);
$pdf->setAltoLinea(1.5);
$pdf->AddPage();

$registros = nivelFolio($pdf->gpo_id, $pdf->ur_id, $pdf->ecf_id, $pdf->envia);

$pdf->Content($registros);

$pdf->Output();
