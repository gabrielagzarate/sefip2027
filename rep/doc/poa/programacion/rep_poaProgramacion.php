<?php

if (!function_exists('u8d')) {
    function u8d($value)
    {
        return mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
    }
}

/**
 * Description of Pdf_PoaProgramacion
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 16-Agt-2018
 * Update for SEFIP2020: 10-Mayo-2019
 * Migrated: 08-Jul-2020
 * Comment: -----
 * Objective: Genera la estructura programatica comenzando de programa hasta actividad.
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
require_once dirname(__FILE__) . '/../../../../lib/poa/programacion/nivelpoaProgramacion.php';
require_once dirname(__FILE__) . '/../../../../ent/Usuario.php';

class Pdf_PoaProgramacion extends PDF
{
    public $usr_id;
    public $clasAdm_gpo;
    public $clasAdm_ur;
    public $nvj_id;
    public $ue_id;
    public $ur;
    public $estadoCosteo;
    public $firmar;
    public $ue;
    public $usuario;
    public $prg_id;
    public $spr_id;
    public $py_id;
    public $eoa_id;
    public $ccd_id;
    public $longitud;
    public $ancho;
    public $imagen;
    public $cedulaNombre;
    public $epg_id;
    public $firma;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->clasAdm_gpo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : false;
        $this->clasAdm_ur = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : false;
        $this->nvj_id = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : false;
        $this->ue_id = isset($_SESSION[_S]['ue_id']) ? $_SESSION[_S]['ue_id'] : false;

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($this->clasAdm_gpo);
        $this->ur->setUr_id($this->clasAdm_ur);
        $this->ur->select();
        $this->estadoCosteo = $this->ur->getUr_estadoCosteo();

        if (isset($_POST['firma']) && $_POST['firma'] != 0) {
            $this->firmar = true;
        } else {
            $this->firmar = false;
        }

        if ($this->nvj_id < 2) {
            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($this->clasAdm_gpo);
            $this->ue->setUr_id($this->clasAdm_ur);

            //Obtener Ue
            if (empty($this->ue_id)) {
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $params = [$this->usr_id];
                $sql = "SELECT ue_id FROM cat_Usuario WHERE usr_id=?";
                $conexion->setSql($sql);
                $conexion->consultar($params);
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

        $this->prg_id = (isset($_POST['prg_id']) ? $_POST['prg_id'] : '');
        $this->spr_id = (isset($_POST['spr_id']) ? $_POST['spr_id'] : '');
        $this->py_id = (isset($_POST['py_id']) ? $_POST['py_id'] : '');
        $this->eoa_id = (isset($_POST['eoa_id']) ? $_POST['eoa_id'] : '');
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
            case 2:
                $this->cedulaNombre = 'ACTIVIDADES/OBRAS';
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
            $this->SetTextColor(250, 250, 250);
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

        $this->Image(__DIR__ . "/../../../../img/logoOaxaca-color-fblanco_v2.png", $ix + 91, $iy - 1, '100', '18', 'PNG', '');

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
        $this->Cell(180, 5, u8d('PROGRAMA OPERATIVO ANUAL ' . APP_EJERCICIO), 0, 1, 'L');

        $ur_nombre = ($this->nvj_id > 2) ? 'DPR' : 'UR';

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt = 0;
        $iyt += 16;
        $nombre_ur = TruncateCadena(u8d(trim($ur_nombre . ": " . $this->ur->getClave() . " - " . $this->ur->getNombre())), ($this->longitud - 10));
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
        $this->Cell(180, 5, u8d($this->cedulaNombre), 0, 1, 'L');

        $ytmp = $this->GetY();
        $this->SetX(10);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, $this->imagen + 33, $ytmp);

        $this->ln(4);
        $this->SetDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->SetX(10);
        $this->SetWidths(array(30 + 8, $this->ancho + 30));
        $this->SetAligns(array('L', 'C', 'C', 'C', 'C', 'R'));
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->SetAltoLinea(4);
        $this->SetRelleno("si");
        $this->Row(array('CONCEPTO', u8d('DESCRIPCIÓN')));
        $this->ln(1);

        $this->SetAltoLinea(1.5);
        $this->SetRelleno("no");
    }

    public function Content($datos)
    {
        //realizamos la impresion de acuerdo al nivel(aqui no se procesa la informacion, el array debe estar procesado y
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
                        $this->SetRelleno("no");
                        $this->SetWidths(array(40, $this->ancho - 2, 30));
                        $this->SetAligns(array('L', 'L', 'R'));
                        $this->Row(array('', 'TOTAL UNIDAD RESPONSABLE', number_format($dato['importe'], 2)));
                    }
                    break;
                case 2:
                    $this->CheckPageBreak(30);
                    $this->ln(1);
                    $this->SetAltoLinea(3);
                    $this->SetBorders('B');
                    $this->SetDrawColor(160, 160, 160);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 7); ///tipo de letra para cada titulo
                    $this->SetRelleno("no");
                    $this->SetWidths(array(28, 7, 3, $this->ancho + 30));
                    $this->SetAligns(array('L', 'R', 'C', 'L'));
                    $this->Row(array('PROGRAMA', $dato['clave'], ' ', u8d($dato['nombre'])));
                    $this->SetFillColor(255, 255, 255);
                    $this->SetRelleno("no");
                    break;
                case 3:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(200, 200, 200);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 6); ///tipo de letra para cada titulo
                    $this->SetRelleno("no");
                    $this->SetWidths(array(28, 7, 3, $this->ancho + 30));
                    $this->SetAligns(array('L', 'R', 'C', 'L'));
                    $this->Row(array('SUBPROGRAMA', $dato['clave'], ' ', u8d($dato['nombre'])));
                    break;
                case 4:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(200, 200, 200);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 6);
                    $this->SetWidths(array(28, 7, 3, $this->ancho, 30));
                    $this->SetAligns(array('L', 'R', 'C', 'L', 'R'));
                    $this->Row(array('UE', $dato['clave'], '-', u8d($dato['nombre']), number_format($dato['importe'], 2)));
                    break;
                case 5:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths(array(28, 7, 3, $this->ancho + 30));
                    $this->SetAligns(array('L', 'R', 'C', 'L'));
                    $this->Row(array('PY', $dato['clave'], ' ', u8d($dato['nombre'])));
                    break;
                case 6:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths(array(28, 7, 3, $this->ancho - 10, 40));
                    $this->SetAligns(array('L', 'R', 'C', 'L', 'L'));
                    $this->Row(array('OA', $dato['clave'], ' ', u8d($dato['nombre']), u8d($dato['nombre_eoa'])));
                    break;
                case 7:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 6);
                    $this->SetWidths(array(17, 18, 3, $this->ancho, 30));
                    $this->SetAligns(array('L', 'R', 'C', 'L', 'R'));
                    $this->Row(array('CVEFIN', $dato['clave'], '-', u8d($dato['nombre']), number_format($dato['importe'], 2)));
                    break;
                case 8:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths(array(28, 7, 3, $this->ancho, 30));
                    $this->SetAligns(array('L', 'R', 'C', 'L', 'R'));
                    $this->Row(array('PG', $dato['clave'], '-', u8d($dato['nombre']), number_format($dato['importe'], 2)));
                    break;
                case 9:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths(array(28, 7, 3, $this->ancho, 30));
                    $this->SetAligns(array('L', 'R', 'C', 'L', 'R'));
                    $this->Row(array('PE', $dato['clave'], '-', u8d($dato['nombre']), number_format($dato['importe'], 2)));
                    break;
                case 10:
                    switch ($this->ccd_id) {
                        case 2:
                            break;
                    }
                    break;
                default:
                    echo "kym";
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
            $this->MultiCell(57, 3, u8d('REVISÓ'), 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 3, u8d('REVISÓ'), 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 3, u8d('AUTORIZÓ'), 0, 'C', false);

            $y = -20;
            $this->SetY($y);
            $this->SetX($x);
            $this->MultiCell(57, 4, u8d($this->ur->getNombreAdministrativo()), 'T', 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 4, u8d($this->ur->getNombrePlaneacion()), 'T', 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 4, u8d($this->ur->getNombreTitular()), 'T', 'C', false);

            $y = -15;
            $this->SetY($y);
            $this->SetX($x);
            $this->MultiCell(57, 3, u8d($this->ur->getCargoAdministrativo()), 0, 'C');
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 3, u8d($this->ur->getCargoPlaneacion()), 0, 'C');
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 3, u8d($this->ur->getCargoTitular()), 0, 'C');
        }

        $this->SetFont('univiapro', '', 5);
        $y = -7;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(
            57,
            2,
            u8d($this->usuario->getUsr_id() . " - " . $this->usuario->getUsuario() . " "),
            0,
            'L'
        );

        $this->SetY($y);
        $this->SetX($x + 57);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);

        $this->SetY($y);
        $this->SetX($x + 57 + $distancia);
        $this->MultiCell(57, 2, u8d('Página ' . $this->PageNo() . '/{nb}'), 0, 'C');
        $this->SetY($y);
        $this->SetX($x + 114 + $distancia);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);
        $this->SetY($y);
        $this->SetX($x + 114 + 2 * $distancia);
        $this->MultiCell(63, 2, date("d/m/Y", strtotime('+1 year')) . ' - ' . date("H:i:s"), 0, 'R');
    }
}

$pdf = new Pdf_PoaProgramacion('P', 'mm', 'letter');
$pdf->SetProtection(array("print"), "", PDF_PASSWORD_OWNER_POA);
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
switch ($pdf->ccd_id) {
    case 2:
        $pdf->setOrientation('P');
        break;
}
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 40);
$pdf->SetAltoLinea(1.5);
$pdf->AddPage();
$datos = nivelpoaProgramacion($pdf->ur->getGpo_id(), $pdf->ur->getUr_id(), $pdf->ccd_id, $pdf->prg_id, $pdf->spr_id, $pdf->py_id, $pdf->eoa_id);
$pdf->Content($datos);
$pdf->Output();
