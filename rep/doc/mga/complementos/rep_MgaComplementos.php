<?php

/**
 * Description of Pdf_MgaComplementos
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Update: 26-Jul-2019
 * Comment: -----
 * Objective: Elabora el reporte de acuerdo a la opcion que se requiera, modificar techos, altas de previsiones o ajustes.
 * Regla de Negocio: -----
 * Permits: Esta pantalla no pregunta por permisos de escritura.
 * */
session_start();

require_once dirname(__FILE__) . '/../../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../../../lib/formatoTexto.php';
require_once dirname(__FILE__) . '/../../../../lib/TruncateCadena.php';
require_once dirname(__FILE__) . '/../../../../lib/mga/nivelMgaComplementosPrevisiones.php';
require_once dirname(__FILE__) . '/../../../../ent/Usuario.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';

class Pdf_MgaComplementos extends PDF
{
    public UnidadResponsable $ur;
    public $epg_id;
    public $ecf_id;
    public $ccd_id;
    private UnidadEjecutora $ue;
    private $usuario;
    private $usr_id;
    private $estadoCosteo;
    private $firmar;
    private $firma;
    private $imagen;
    private $longitud;
    private $ancho;
    private $cedulaNombre;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();
        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->estadoCosteo = $this->ur->getUr_estadoCosteo();

        if (isset($_POST['firma']) && $_POST['firma'] != 0) {
            $this->firmar = true;
        } else {
            $this->firmar = false;
        }

        if ($_SESSION[_S]['nvj_id'] < 2) {
            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $this->ue->setUr_id($_SESSION[_S]['clasAdm-ur']);

            if (empty($_SESSION[_S]['ue_id'])) {
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $sql = 'SELECT ue_id FROM cat_Usuario WHERE usr_id = ?';
                $conexion->setSql($sql);
                $conexion->consultar([$_SESSION[_S]['usr_id']]);
                $conexion->desconectar();
                $dato = $conexion->extraer();
                $ue = $dato['ue_id'];
            } else {
                $ue = $_SESSION[_S]['ue_id'];
            }

            $this->ue->setUe_id($ue);
            $this->ue->select();
        }

        if ($this->usr_id != '' && $this->usr_id != 0) {
            $this->usuario = new Usuario();
            $this->usuario->setUsr_id($this->usr_id);
            $this->usuario->select();
        }

        $this->ecf_id = (isset($_POST['ecf_id']) ? $_POST['ecf_id'] : '');
        $this->epg_id = (isset($_POST['epg_id']) ? $_POST['epg_id'] : '');
        $this->ccd_id = (isset($_POST['ccd_id']) ? $_POST['ccd_id'] : '');
    }

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

        switch ($this->ccd_id) {
            case 1:
                $this->cedulaNombre = 'TECHO PRESUPUESTAL';
                break;
            case 2:
                $this->cedulaNombre = 'PREVISIÓN PRESUPUESTAL';
                break;
            case 3:
                $this->cedulaNombre = 'AJUSTE PRESUPUESTAL';
                break;
        }

        $this->SetMargins(10, 10, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);

        if (!$this->firmar && $this->ur->getEur_id() < 5 && $this->estadoCosteo == 1) {
            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('UniviaPro-Bold', 'B', 50);
            $this->SetTextColor(225, 225, 225);
            $this->RotatedText($ix + 30, $iy + 160, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix + 150, $iy + 160, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix, $iy + 80, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix + 150, $iy + 80, 'HOJA DE TRABAJO', 45);
        }

        $this->SetMargins(10, 1, 1, 1);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();

        $this->Image(__DIR__ . '/../../../../img/' . LOGO_COLOR, $ix + 97, $iy - 1, '100', '18', 'PNG', '');

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
        $iyt = 0;
        $ur_nombre = ($_SESSION[_S]['nvj_id'] > 2) ? 'DPR' : 'UR';

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt += 16;
        $nombre_ur = TruncateCadena(utf8Decode(trim($ur_nombre . ': ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre())), ($this->longitud - 10));
        $this->Cell(5, 5, substr($nombre_ur, 0, ($this->longitud - 10)), 0, 0, 'L');

        if (strlen($nombre_ur) > ($this->longitud - 10)) {
            $this->SetY($iy + $iyt);
            $this->SetX($ix + 7);
            $this->Cell(5, 5, substr($nombre_ur, ($this->longitud - 10), ($this->longitud - 10)), 0, 0, 'L');
        }

        if (strlen($nombre_ur) > ($this->longitud - 10) * 2) {
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->SetX($ix + 7);
            $this->Cell(5, 5, substr($nombre_ur, ($this->longitud - 10) * 2, ($this->longitud - 10)), 0, 0, 'L');
        }

        if (strlen($nombre_ur) > ($this->longitud - 10) * 3) {
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->SetX($ix + 7);
            $this->Cell(5, 5, substr($nombre_ur, ($this->longitud - 10) * 3, ($this->longitud - 10)), 0, 0, 'L');
        }

        $ytmp = $this->GetY();
        $this->SetXY($ix + 7, $ytmp + 4);

        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(180, 5, utf8Decode($this->cedulaNombre), 0, 1, 'L');

        $ytmp = $this->GetY();
        $this->SetX(10);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, $this->imagen + 33, $ytmp);

        $this->ln(4);
        $this->SetDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->SetX(10);
        $this->SetWidths([30, $this->ancho, 38]);
        $this->SetAligns(['L', 'C', 'C', 'C', 'C', 'R']);
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->SetAltoLinea(4);
        $this->SetRelleno('si');
        $this->Row(['CONCEPTO', utf8Decode('DESCRIPCIÓN'), 'PRESUPUESTO  $']);
        $this->ln(1);

        $this->SetAltoLinea(1.5);
        $this->SetRelleno('no');
    }

    public function Content($datos)
    {
        //realizamos la impresion de acuerdo al nivel(aqui no se procesa la informacion, el array debe estar procesado y ordenado
        $pintaEncabezado = true;
        while ($dato = array_pop($datos)) {
            //del 1 al 7 no se mueven, solo en caso de que cambie el formato
            //el 8 es cuando se imprime la partida especifica
            //en el 9 es el detalle de las cedulas o su agrupacion del costeo
            $this->SetAltoLinea(1.5);
            switch ($dato['nivel']) {
                case 1:
                    if ($this->epg_id == '') {
                        $this->CheckPageBreak(30);
                        $this->ln(1);
                        $this->SetX(10);
                        $this->SetFont('UniviaPro-Bold', 'B', 7); //tipo de letra para cada titulo
                        $this->SetRelleno('no');
                        $this->SetWidths([40, $this->ancho - 2, 30]);
                        $this->SetAligns(['L', 'L', 'R']);
                        $this->Row(['', 'TOTAL UNIDAD RESPONSABLE', number_format($dato['importe'], 2)]);
                    }
                    break;
                case 2:
                    $this->CheckPageBreak(30);
                    $this->ln(4);
                    $this->SetAltoLinea(3);
                    $this->SetBorders('B');
                    $this->SetDrawColor(160, 160, 160);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 7); //tipo de letra para cada titulo
                    $this->SetRelleno('no');
                    $this->SetWidths([28, 7, 3, $this->ancho, 30]);
                    $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                    $this->Row(['PROGRAMA', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)]);
                    $this->SetFillColor(255, 255, 255);
                    $this->SetRelleno('no');
                    $pintaEncabezado = true;
                    break;
                case 3:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(200, 200, 200);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 6); //tipo de letra para cada titulo
                    $this->SetRelleno('no');
                    $this->SetWidths([28, 7, 3, $this->ancho, 30]);
                    $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                    $this->Row(['SUBPROGRAMA', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 4:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(200, 200, 200);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 6);
                    $this->SetWidths([28, 7, 3, $this->ancho, 30]);
                    $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                    $this->Row(['UE', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 5:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths([28, 7, 3, $this->ancho, 30]);
                    $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                    $this->Row(['PY', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 6:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths([28, 7, 3, $this->ancho, 30]);
                    $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                    $this->Row(['OA', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 7:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 6);
                    $this->SetWidths([17, 18, 3, $this->ancho, 30]);
                    $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                    $this->Row(['CVEFIN', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 8:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths([28, 7, 3, $this->ancho, 30]);
                    $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                    $this->Row(['PG', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 9:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths([28, 7, 3, $this->ancho, 30]);
                    $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                    $this->Row(['PE', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 10:
                    switch ($this->ccd_id) {
                        case 1:
                            if ($pintaEncabezado) {
                                $this->SetAltoLinea(1.5);
                                $this->ln(2);
                                $this->SetX(10);
                                $this->SetBorders(0);
                                $this->SetFillColor(221, 228, 233);
                                $this->RoundedRect(15, $this->GetY() - .5, 170, 4, 1, 'F', '1234');
                                $this->SetWidths([5, 70, 70, 30]); //175
                                $this->SetAligns(['L', 'L', 'L', 'R']);
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(['', 'GRAN FUENTE DE FINANCIAMIENTO', 'CLASE DE GASTO', 'IMPORTE']);
                                $this->ln(1);
                            }
                            $this->SetRelleno('no');
                            $this->SetFont('UniviaPro-Regular', '', 6);
                            $this->SetWidths([5, 70, 70, 30]); //175
                            $this->SetAligns(['L', 'L', 'L', 'R']);
                            $this->Row(['', utf8Decode($dato['asr_nombre']), utf8Decode($dato['gbf_nombre']), '$' . number_format($dato['ali_beneficiarios'], 0)]);
                            $pintaEncabezado = false;
                            break;
                        case 2:
                            if ($pintaEncabezado) {
                                $this->ln(2);
                                $this->SetX(10);
                                $this->SetBorders(0);
                                $this->SetFillColor(221, 228, 233);
                                $this->RoundedRect(15, $this->GetY() - .5, 170, 4, 1, 'DF', '1234');
                                $this->SetWidths([5, 30, 80, 20, 20, 20]); //175
                                $this->SetAligns(['L', 'L', 'L', 'R', 'R', 'R']);
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(['', 'Cve. Fin.', 'Nombre Clave de Financiamiento', 'PGen', 'PEsp', 'Importe']);
                                $this->ln(1);
                            }
                            $this->SetFont('UniviaPro-Regular', '', 6);
                            $this->SetWidths([5, 30, 80, 20, 20, 20]); //175
                            $this->SetAligns(['L', 'L', 'L', 'R', 'R', 'R']);
                            $this->Row(['', utf8Decode($dato['gbf_nombre']), $dato['ali_beneficiarios'], (($dato['ali_cuotaDiaria'] > 0) ? number_format($dato['ali_cuotaDiaria'], 0) : ''), (($dato['ali_diasOtorgados'] > 0) ? str_pad($dato['ali_diasOtorgados'], 3, 0, STR_PAD_LEFT) : ''), number_format(floatval($dato['total']), 2)]);
                            $pintaEncabezado = false;
                            break;
                        case 3:
                            if ($pintaEncabezado) {
                                $this->SetAltoLinea(1.5);
                                $this->ln(2);
                                $this->SetX(10);
                                $this->SetBorders(0);
                                $this->SetFillColor(221, 228, 233);
                                $this->RoundedRect(15, $this->GetY() - .5, 170, 4, 1, 'F', '1234');
                                $this->SetWidths([5, 9, 63, 9, 63, 9, 15]); //175
                                $this->SetAligns(['L', 'C', 'L', 'C', 'L', 'C', 'R']);
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(['', 'Pg', utf8Decode('Partida genérica'), 'Pe', utf8Decode('Partida específica'), 'Tipo', 'Importe']);
                                $this->ln(1);
                            }
                            $this->SetRelleno('no');
                            $this->SetFont('UniviaPro-Regular', '', 6);
                            $this->SetWidths([5, 9, 63, 9, 63, 9, 15]); //175
                            $this->SetAligns(['L', 'C', 'L', 'C', 'L', 'C', 'R']);
                            $this->Row(['', $dato['pg_id'], utf8Decode($dato['pg_nombre']), str_pad($dato['pe_id'], 3, 0, STR_PAD_LEFT), utf8Decode($dato['pe_nombre']), $dato['tcp_id'], '$' . number_format($dato['total'], 2)]);
                            $pintaEncabezado = false;
                            break;
                        default:
                            break;
                    }
                    break;
                default:
                    break;
            }
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

        if (isset($this->firma)) {
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);

            $y = -40;
            $this->SetY($y);
            $this->SetX($x);
            $this->MultiCell(57, 3, utf8Decode('REVISÓ'), 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 3, utf8Decode('REVISÓ'), 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 3, utf8Decode('AUTORIZÓ'), 0, 'C', false);

            $y = -20;
            $this->SetY($y);
            $this->SetX($x);
            $this->MultiCell(57, 4, utf8Decode($this->firma->getNombreAdministrativo()), 'T', 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 4, utf8Decode($this->firma->getNombrePlaneacion()), 'T', 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 4, utf8Decode($this->firma->getNombreTitular()), 'T', 'C', false);

            $y = -15;
            $this->SetY($y);
            $this->SetX($x);
            $this->MultiCell(57, 3, utf8Decode($this->firma->getCargoAdministrativo()), 0, 'C');
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 3, utf8Decode($this->firma->getCargoPlaneacion()), 0, 'C');
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 3, utf8Decode($this->firma->getCargoTitular()), 0, 'C');
        }

        $this->SetFont('univiapro', '', 5);
        $y = -7;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(57, 2, utf8Decode($this->usuario->getUsr_id() . ' - ' . $this->usuario->getUsuario() . ' '), 0, 'L');
        $this->SetY($y);
        $this->SetX($x + 57);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);
        $this->SetY($y);
        $this->SetX($x + 57 + $distancia);
        $this->MultiCell(57, 2, utf8Decode('Página ' . $this->PageNo() . '/{nb}'), 0, 'C');
        $this->SetY($y);
        $this->SetX($x + 114 + $distancia);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);
        $this->SetY($y);
        $this->SetX($x + 114 + 2 * $distancia);
        $this->MultiCell(63, 2, date('d/m/Y') . ' - ' . date('H:i:s'), 0, 'R');
    }
}

$pdf = new Pdf_MgaComplementos('P', 'mm', 'letter');
$pdf->SetProtection(['print'], '', PDF_PASSWORD_OWNER_MGA);
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');

switch ($pdf->ccd_id) {
    case 1:
        $pdf->setOrientation('P');
        break;
    case 2:
        $pdf->setOrientation('P');
        break;
    case 3:
        $pdf->setOrientation('P');
        break;
    default:
        break;
}

$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 40);
$pdf->SetAltoLinea(1.5);
$pdf->AddPage();
$datos = nivelMgaComplementos($pdf->ur->getGpo_id(), $pdf->ur->getUr_id(), '', $pdf->epg_id, $pdf->ecf_id, $pdf->ccd_id);
$pdf->Content($datos);
$pdf->Output();
