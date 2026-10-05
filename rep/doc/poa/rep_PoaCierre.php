<?php

/**
 * Description of Pdf_PoaCierre
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 31-Agt-2018
 * Comment: -----
 * Objective: Elabora el reporte de acuerdo a la opcion que se requiera, cierre poa.
 * Regla de Negocio: -----
 * Permits: Esta pantalla no pregunta por permisos de escritura.
 * */
session_start();
require_once dirname(__FILE__) . '/../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../lib/formatoTexto.php';
require_once dirname(__FILE__) . '/../../../lib/TruncateCadena.php';
require_once dirname(__FILE__) . '/../../../lib/poa/cierre_poa/nivelPoaCierre.php';
require_once dirname(__FILE__) . "/../../../lib/constantes.php";
require_once dirname(__FILE__) . "/../../../lib/helpers.php";
require_once dirname(__FILE__) . '/../../../ent/Usuario.php';
class Pdf_PoaCierre extends PDF
{
    private $usrNvj;
    private $usrId;
    private $usrUe;
    private $clasAdmGpo;
    private $clasAdmUr;
    public $ur;
    private $ue;
    private $usr_id;
    private $estadoCosteo;
    private $ur_cierreForzado;
    private $firmar;
    private $firma;
    private $usuario;
    public $ecf_id;
    private $epg_id;
    public $ccd_id;
    private $longitud;
    private $ancho;
    private $imagen;
    private $cedulaNombre;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);
        $ur_id = substr($_POST['ur_id'], 1, 2);

        /** variables de sesion del usuario*/
        $this->usrNvj = $_SESSION[_S]['nvj_id'];
        $this->usrId = $_SESSION[_S]['usr_id'];
        $this->usrUe = isset($_SESSION[_S]['ue_id']) && ($_SESSION[_S]['ue_id'] != '') ? $_SESSION[_S]['ue_id'] : 0;
        /** variable de clasificación administrativa */
        $this->clasAdmGpo = $_SESSION[_S]['clasAdm-gpo'];
        $this->clasAdmUr = $_SESSION[_S]['clasAdm-ur'];

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($this->clasAdmGpo);
        $this->ur->setUr_id($ur_id);
        $this->ur->select();
        $this->usr_id = $this->usrNvj;
        $this->estadoCosteo = $this->ur->getUr_estadoCosteo();
        $this->ur_cierreForzado = $this->ur->getUr_cierreForzado();

        if (isset($_POST['firma']) && $_POST['firma'] != 0) {
            $this->firmar = true;
        } else {
            $this->firmar = false;
        }

        if ($this->usrNvj < 2) {
            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($this->clasAdmGpo);
            $this->ue->setUr_id($this->clasAdmUr);

            //Obtener Ue
            if (empty($this->usrUe)) {
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $sql = '
                    SELECT
                        ue_id F
                    FROM cat_Usuario
                    WHERE usr_id = ?';
                $conexion->setSql($sql);
                $conexion->consultar([$this->usrId]);
                $conexion->desconectar();
                $dato = $conexion->extraer();
                $ue = $dato['ue_id'];
            } else {
                $ue = $this->usrUe;
            }

            $this->ue->setUe_id($ue);
            $this->ue->select();
        } else {
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
        $iyt = 0;
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
                $this->cedulaNombre = 'CIERRE POA';
                break;
        }
        $this->SetMargins(10, 10, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        //Marca de agua
        if (! $this->firmar && $this->ur->getEur_id() < 5 && $this->estadoCosteo == 1) {
            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('UniviaPro-Bold', 'B', 50);
            $this->SetTextColor(225, 225, 225);
            $this->RotatedText($ix + 30, $iy + 160, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix + 150, $iy + 160, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix, $iy + 80, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix + 150, $iy + 80, 'HOJA DE TRABAJO', 45);
        }
        if ($this->ur_cierreForzado == 2) {
            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('UniviaPro-Bold', 'B', 50);
            $this->SetTextColor(225, 225, 225);
            $this->RotatedText($ix + 30, $iy + 160, 'SIN VALIDEZ', 45);
            $this->RotatedText($ix + 150, $iy + 160, 'SIN VALIDEZ', 45);
            $this->RotatedText($ix, $iy + 80, 'SIN VALIDEZ', 45);
            $this->RotatedText($ix + 150, $iy + 80, 'SIN VALIDEZ', 45);
        }

        $this->SetMargins(10, 1, 1, 1);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();

        $this->setRouteImg($this->ur->getClave());
        //$this->Image(($this->getRouteImg()), $ix + 97, $iy - 1, '100', '18', 'JPG', '');
        $this->Image(__DIR__ . "/../../../img/" . LOGO_COLOR, $ix + 115, $iy - 1, '80', '18', 'PNG', '');

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
        $this->Cell(180, 5, utf8decode('PROGRAMA OPERATIVO ANUAL ' . APP_EJERCICIO), 0, 1, 'L');

        $ur_nombre = ($this->usrNvj > 2) ? 'DPR' : 'UR';

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt += 16;

        $nombre_ur = TruncateCadena(utf8decode(trim($ur_nombre . ': ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre())), ($this->longitud - 10));
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
        $this->Cell(180, 5, utf8decode($this->cedulaNombre), 0, 1, 'L');

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
        $this->Row(['CONCEPTO', utf8decode('DESCRIPCIÓN'), 'PRESUPUESTO  $']);
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
                        $this->SetFont('UniviaPro-Bold', 'B', 7); ///tipo de letra para cada titulo
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
                    $this->SetFont('UniviaPro-Bold', 'B', 7); ///tipo de letra para cada titulo
                    $this->SetRelleno('no');
                    $this->SetWidths([28, 7, 3, $this->ancho, 30]);
                    $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                    $this->Row(['PROGRAMA', $dato['clave'], '-', utf8decode($dato['nombre']), number_format($dato['importe'], 2)]);
                    $this->SetFillColor(255, 255, 255);
                    $this->SetRelleno('no');
                    $pintaEncabezado = true;
                    break;
                case 3:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(200, 200, 200);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 6); ///tipo de letra para cada titulo
                    $this->SetRelleno('no');
                    $this->SetWidths([28, 7, 3, $this->ancho, 30]);
                    $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                    $this->Row(['SUBPROGRAMA', $dato['clave'], '-', utf8decode($dato['nombre']), number_format($dato['importe'], 2)]);
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
                    $this->Row(['UE', $dato['clave'], '-', utf8decode($dato['nombre']), number_format($dato['importe'], 2)]);
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
                    $this->Row(['PY', $dato['clave'], '-', utf8decode($dato['nombre']), number_format($dato['importe'], 2)]);
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
                    $this->Row(['OA', $dato['clave'], '-', utf8decode($dato['nombre']), number_format($dato['importe'], 2)]);
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
                    $this->Row(['CVEFIN', $dato['clave'], '-', utf8decode($dato['nombre']), number_format($dato['importe'], 2)]);
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
                    $this->Row(['PG', $dato['clave'], '-', utf8decode($dato['nombre']), number_format($dato['importe'], 2)]);
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
                    $this->Row(['PE', $dato['clave'], '-', utf8decode($dato['nombre']), number_format($dato['importe'], 2)]);
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
                                $this->SetWidths([5, 40, 55, 25, 25, 25]); //175
                                $this->SetAligns(['L', 'L', 'L', 'R', 'R', 'R']);
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(['', 'GRAN FUENTE', 'CLASE DE GASTO', 'TECHO FINANCIERO', 'PRESUPUESTADO', 'DIFERENCIA']);
                                $this->ln(1);
                            }
                            $this->SetRelleno('no');
                            $this->SetFont('UniviaPro-Regular', '', 6);
                            $this->SetWidths([5, 40, 55, 25, 25, 25]); //175
                            $this->SetAligns(['L', 'L', 'L', 'R', 'R', 'R']);
                            $this->Row(['', utf8decode($dato['gran_fuente']), utf8decode($dato['clase_gasto']), '$' . number_format($dato['techo_financiero'], 2), '$' . number_format($dato['presupuestado'], 2), '$' . number_format($dato['diferencia'], 2)]);
                            $pintaEncabezado = false;
                            break;
                        default:
                            echo 'kym->cedula indefinida';
                            break;
                    }
                    break;
                default:
                    echo 'kym';
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
            $this->MultiCell(57, 3, utf8decode('REVISÓ'), 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 3, utf8decode('REVISÓ'), 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 3, utf8decode('AUTORIZÓ'), 0, 'C', false);

            $y = -20;
            $this->SetY($y);
            $this->SetX($x);
            $this->MultiCell(57, 4, utf8decode($this->firma->getNombreAdministrativo()), 'T', 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 4, utf8decode($this->firma->getNombrePlaneacion()), 'T', 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 4, utf8decode($this->firma->getNombreTitular()), 'T', 'C', false);

            $y = -15;
            $this->SetY($y);
            $this->SetX($x);
            $this->MultiCell(57, 3, utf8decode($this->firma->getCargoAdministrativo()), 0, 'C');
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 3, utf8decode($this->firma->getCargoPlaneacion()), 0, 'C');
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 3, utf8decode($this->firma->getCargoTitular()), 0, 'C');
        }

        $this->SetFont('univiapro', '', 5);
        $y = -7;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(57, 2, utf8decode($this->usuario->getUsr_id() . ' - ' . $this->usuario->getUsuario() . ' '), 0, 'L');

        $this->SetY($y);
        $this->SetX($x + 57);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);
        $this->SetY($y);
        $this->SetX($x + 57 + $distancia);
        $this->MultiCell(57, 2, utf8decode('Página ' . $this->PageNo() . '/{nb}'), 0, 'C');
        $this->SetY($y);
        $this->SetX($x + 114 + $distancia);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);
        $this->SetY($y);
        $this->SetX($x + 114 + 2 * $distancia);
        $this->MultiCell(63, 2, date('d/m/Y') . ' - ' . date('H:i:s'), 0, 'R');
    }
}

$pdf = new Pdf_PoaCierre('P', 'mm', 'letter');
$pdf->SetProtection(['print', 'annot-forms'], '', 'nUtq8Zsy42jkLvU');
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
switch ($pdf->ccd_id) {
    case 1:
        $pdf->setOrientation('P');
        break;
    default:
        echo 'kym';
        break;
}

$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 40);
$pdf->SetAltoLinea(1.5);
$pdf->AddPage();
$datos = nivelPoaCierre($pdf->ur->getGpo_id(), $pdf->ur->getUr_id(), $pdf->ccd_id);
$pdf->Content($datos);
$pdf->Output();
