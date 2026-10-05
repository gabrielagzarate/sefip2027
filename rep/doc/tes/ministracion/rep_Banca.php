<?php
/**
 * Description of Pdf_Banca
 *
 * @author ?
 * Collaborators: Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Created: 09-Ene-2019
 * Updated: 23-10-2023
 * Comment: -----
 * Objective: Elabora el reporte de los departamento de programacion de ministraciones y pagos.
 * Regla de Negocio: -----
 * Permits: Esta pantalla no pregunta por permisos de escritura.
 * */
session_start();

ini_set('memory_limit', '20M');
set_time_limit(0);

require_once dirname(__FILE__) . '/../../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../../../lib/formatoTexto.php';
require_once dirname(__FILE__) . '/../../../../lib/TruncateCadena.php';
require_once dirname(__FILE__) . '/../../../../ent/Usuario.php';
require_once dirname(__FILE__) . '/../../../../lib/tes/ministracion/nivelBanca.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2017.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2018.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2019.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2020.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2021.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2022.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2023.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2024.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2025.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2026.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';

class Pdf_Banca extends PDF
{
    public $lot_id;
    public $fpa_id;
    public $msc_id;
    public $lot_ejercicio;
    public $usr_id;
    public $firmar;
    public $marca;
    public $LOTE;
    public $ue;
    public $usuario;
    public $longitud;
    public $ancho;
    public $imagen;
    public $cedulaNombre;
    public $epg_id;
    public $vinFondeo;
    public $tipo;
    public $mbn_id;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

        $this->lot_id = (isset($_POST['lot_id']) && $_POST['lot_id'] != '') ? $_POST['lot_id'] : '';
        $this->fpa_id = (isset($_POST['fpa_id']) && $_POST['fpa_id'] != '') ? $_POST['fpa_id'] : '';
        $this->msc_id = (isset($_POST['msc_id']) && $_POST['msc_id'] != '') ? $_POST['msc_id'] : '';
        $this->lot_ejercicio = (isset($_POST['lot_ejercicio']) && $_POST['lot_ejercicio'] != '') ? $_POST['lot_ejercicio'] : '';

        $this->usr_id = $_SESSION[_S]['usr_id'];

        $this->firmar = true;
        $this->marca = ($_SERVER['SERVER_ADDR'] != "sefip.finanzasoaxaca.gob.mx") ? false : true;

        $tipoPago = $this->fpa_id;
        switch ($tipoPago) {
            case 1:
                $tipoPago = 'T';
                break;
            case 2:
                $tipoPago = 'C';
                break;
            case 3:
                $tipoPago = 'L';
                break;
        }

        $anio = substr($this->lot_ejercicio, 2, 2);
        $mes = str_pad($this->msc_id, 2, "0", STR_PAD_LEFT);
        $lot_id = str_pad($this->lot_id, 4, "0", STR_PAD_LEFT);
        $this->LOTE = $tipoPago . $mes . $lot_id . $anio;

        if ($_SESSION[_S]['nvj_id'] < 2) {
            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $this->ue->setUr_id($_SESSION[_S]['clasAdm-ur']);

            //Obtener Ue
            if (empty($_SESSION[_S]['ue_id'])) {
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $sql = "SELECT ue_id FROM ".BD_USUARIO." WHERE usr_id= :usr_id";
                $parametros = [
                    'usr_id' => $_SESSION[_S]['usr_id'],
                ];
                $conexion->setSql($sql);
                $conexion->consultar($parametros);
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

        $this->cedulaNombre = 'DETALLE DEL LOTE: ' . $this->LOTE;

        $this->SetMargins(10, 10, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);

        if (!$this->firmar) {
            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('UniviaPro-Bold', 'B', 50);
            $this->SetTextColor(225, 225, 225);
            $this->RotatedText($ix + 30, $iy + 160, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix + 150, $iy + 160, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix, $iy + 80, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix + 150, $iy + 80, 'HOJA DE TRABAJO', 45);
        }

        if ($this->marca) {
            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('UniviaPro-Bold', 'B', 50);
            $this->SetTextColor(225, 225, 225);
            $this->RotatedText($ix + 30, $iy + 160, 'PRUEBAS', 45);
            $this->RotatedText($ix + 150, $iy + 160, 'PRUEBAS', 45);
            $this->RotatedText($ix, $iy + 80, 'PRUEBAS', 45);
            $this->RotatedText($ix + 150, $iy + 80, 'PRUEBAS', 45);
        }

        $this->SetMargins(10, 1, 1, 1);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();

        $getClave = 114;

        $this->setRouteImg($getClave);
        $this->Image($this->getRouteImg(), $ix + 164, $iy - 1, '100', '18', 'JPG', '');

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
        $this->Cell(180, 5, utf8Decode("SECRETARÍA DE FINANZAS TESORERÍA " . APP_EJERCICIO), 0, 1, 'L');

        $ur_nombre = '';
        $getNombre = 'COORDINACIÓN DE CONTROL FINANCIERO';

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt = 0;
        $iyt += 16;
        $nombre_ur = TruncateCadena(utf8Decode(trim($ur_nombre . "116 - " . $getNombre)), ($this->longitud - 10));
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

        //generamos los titulos de la tabla
        $this->SetDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        if ($this->fpa_id == 3) {
            $this->setX(10);
            $this->SetWidths(array(10, 15, 10, 10, 10, 10, 20, 20, 20, 25, 20, 35, 20, 20, 20, 10, 10));
            $this->SetAligns(array('L', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
            $this->SetFont('UniviaPro-Bold', 'B', 7);
            $this->setBorders('');
            $this->setAltoLinea(4);
            $this->Row(array('#', 'E.PAGO', 'UR', 'CLC', 'TIPO', 'FOLIO', 'FOLIO LINEACAPTURA', 'LINEA CAPTURA', 'IMPORTE', 'RESPONSABLE', 'BENEFICIARIO', 'CONCEPTO', 'BANCO', 'CTA_CARGO', 'CTA_DESTINO', 'CVE. FIN', 'LOTE'));
        } else {
            $this->setX(10);
            $this->SetWidths(array(10, 15, 10, 10, 10, 10, 20, 25, 20, 43, 20, 20, 20, 15, 10));
            $this->SetAligns(array('L', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
            $this->SetFont('UniviaPro-Bold', 'B', 7);
            $this->setBorders('');
            $this->setAltoLinea(4);
            $this->Row(array('#', 'E.PAGO', 'UR', 'CLC', 'TIPO', 'FOLIO', 'IMPORTE', 'RESPONSABLE', 'BENEFICIARIO', 'CONCEPTO', 'BANCO', 'CTA_CARGO', 'CTA_DESTINO', 'CVE. FIN', 'LOTE'));
        }

        $this->ln(1);

        $this->SetAltoLinea(1.5);
        $this->SetRelleno("no");
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
                        $this->SetRelleno("no");
                        $this->SetWidths(array(40, $this->ancho - 2, 30));
                        $this->SetAligns(array('L', 'L', 'R'));
                        $this->Row(array('', 'TOTAL UNIDAD RESPONSABLE', number_format($dato['importe'], 2)));
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
                    $this->SetRelleno("no");
                    $this->SetWidths(array(28, 7, 3, $this->ancho, 30));
                    $this->SetAligns(array('L', 'R', 'C', 'L', 'R'));
                    $this->Row(array('PROGRAMA', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)));
                    $this->SetFillColor(255, 255, 255);
                    $this->SetRelleno("no");
                    $pintaEncabezado = true;
                    break;
                case 3:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(200, 200, 200);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 6); //tipo de letra para cada titulo
                    $this->SetRelleno("no");
                    $this->SetWidths(array(28, 7, 3, $this->ancho, 30));
                    $this->SetAligns(array('L', 'R', 'C', 'L', 'R'));
                    $this->Row(array('SUBPROGRAMA', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)));
                    $pintaEncabezado = true;
                    break;
                case 4:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(200, 200, 200);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 6);
                    $this->SetWidths(array(28, 7, 3, $this->ancho, 30));
                    $this->SetAligns(array('L', 'R', 'C', 'L', 'R'));
                    $this->Row(array('UE', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)));
                    $pintaEncabezado = true;
                    break;
                case 5:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths(array(28, 7, 3, $this->ancho, 30));
                    $this->SetAligns(array('L', 'R', 'C', 'L', 'R'));
                    $this->Row(array('PY', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)));
                    $pintaEncabezado = true;
                    break;
                case 6:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths(array(28, 7, 3, $this->ancho, 30));
                    $this->SetAligns(array('L', 'R', 'C', 'L', 'R'));
                    $this->Row(array('OA', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)));
                    $pintaEncabezado = true;
                    break;
                case 7:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 6);
                    $this->SetWidths(array(17, 18, 3, $this->ancho, 30));
                    $this->SetAligns(array('L', 'R', 'C', 'L', 'R'));
                    $this->Row(array('CVEFIN', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)));
                    $pintaEncabezado = true;
                    break;
                case 8:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths(array(28, 7, 3, $this->ancho, 30));
                    $this->SetAligns(array('L', 'R', 'C', 'L', 'R'));
                    $this->Row(array('PG', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)));
                    $pintaEncabezado = true;
                    break;
                case 9:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths(array(28, 7, 3, $this->ancho, 30));
                    $this->SetAligns(array('L', 'R', 'C', 'L', 'R'));
                    $this->Row(array('PE', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)));
                    $pintaEncabezado = true;
                    break;
                case 10:
                    $this->CheckPageBreak(5);
                    $this->SetX(15);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetRelleno("no");
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths(array(5, 30, 30, 30, 30, 20, 40));
                    $this->SetAligns(array('L', 'C', 'C', 'C', 'C', 'C', 'C'));
                    $this->Row(array($dato['contador'], $dato['cargo'], $dato['cbs_id_cargo'], utf8Decode($dato['abono']), utf8Decode($dato['cbs_id_abono']), '$' . $dato['importe'], utf8Decode($dato['mbn_observacion'])));
                    $this->ln(5);
                    $pintaEncabezado = false;
                    break;
                case 11:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('BT');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 5);
                    $this->SetRelleno("no");

                    if ($this->fpa_id == 3) {
                        $this->SetWidths(array(10, 15, 10, 10, 10, 10, 20, 20, 20, 25, 20, 35, 20, 20, 20, 10, 10));
                        $this->SetAligns(array('L', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
                        $this->Row(array($dato['contador'], utf8Decode($dato['lotDet_fechaPago']), utf8Decode($dato['lotDet_ur']), $dato['lotDet_clc'], $dato['lotDet_tipo'], $dato['lotDet_folio'], $dato['lotDet_folioL'], $dato['lotDet_lineaC'], utf8Decode('$' . $dato['lotDet_importe']), utf8Decode($dato['lotDet_responsable']), utf8Decode($dato['lotDet_beneficiario']), utf8Decode($dato['lotDet_concepto']), $dato['banco'], $dato['lotDet_CtaCargo'], $dato['lotDet_CtaAbono'], utf8Decode($dato['cvefin']), $dato['lotDet']));
                    } else {
                        $this->SetWidths(array(10, 15, 10, 10, 10, 10, 20, 25, 20, 43, 20, 20, 20, 15, 10));
                        $this->SetAligns(array('L', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
                        $this->Row(array($dato['contador'], utf8Decode($dato['lotDet_fechaPago']), utf8Decode($dato['lotDet_ur']), $dato['lotDet_clc'], $dato['lotDet_tipo'], $dato['lotDet_folio'], utf8Decode('$' . $dato['lotDet_importe']), utf8Decode($dato['lotDet_responsable']), utf8Decode($dato['lotDet_beneficiario']), utf8Decode($dato['lotDet_concepto']), $dato['banco'], $dato['lotDet_CtaCargo'], $dato['lotDet_CtaAbono'], utf8Decode($dato['cvefin']), $dato['lotDet']));
                    }

                    $pintaEncabezado = true;
                    break;
                case 12:

                    $this->SetFont('UniviaPro-Bold', 'B', 6);
                    $this->CheckPageBreak(30);
                    $this->ln(4);
                    $this->Row(array("", "", "", "", "", "", "", "", "", "", "", "", "", "", ""));
                    $this->Row(array("", "", "", "", "", "TOTAL", "$" . number_format($dato['suma'], 2), "", "", "", "", "", "", "", ""));
                    $this->ln(4);
                    $this->setAltoLinea(3);
                    $this->SetX(15);
                    $this->MultiCell(180, 4, 'CADENA ORIGINAL: ' . md5($dato['cadena_original']), 0, 'J');
                    $this->ln(8);
                    $this->setAltoLinea(3);
                    $this->SetX(15);
                    $this->MultiCell(260, 4, utf8Decode($dato['lugar_DeProcedencia']), 0, 'C');

                    $pintaEncabezado = true;
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
        
        $extra = '';

        if (isset($this->mbn_id) && !empty($this->mbn_id)) {
            $extra = 'AND mbn_id = ' . $this->mbn_id;
        }

        $row = $this->getDatos();
        $tmpb = $this->GetBorders();

        $this->SetY(-40);

        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
        $this->Cell(90, 2, 'ELABORO', 0, 0, 'C');
        $this->Cell(250, 2, utf8Decode('AUTORIZÓ'), 0, 1, 'C');

        $this->Cell(130, 2, '', 0, 1, 'C');
        $this->ln(1);
        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
        $this->Cell(90, 2, utf8Decode('C.P. OLEGARIO GERARDO SILVA CRUZ'), 0, 0, 'C');
        $this->Cell(250, 2, utf8Decode('IVÁN ELADIO GÓMEZ SANTIAGO'), 0, 1, 'C');

        $this->Cell(130, 2, '', 0, 1, 'C');
        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
        $this->Cell(90, 2, utf8Decode(''), 0, 0, 'C');
        $this->Cell(250, 2, utf8Decode(' DEPARTAMENTO DE PLANEACIÓN Y EVALUACIÓN FINANCIERA'), 0, 1, 'C');

        $this->SetFont('univiapro', '', 5);
        $y = -7;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(57, 2,
            utf8Decode($this->usuario->getUsr_id() . " - " . $this->usuario->getUsuario() . " "),
            0, 'L');

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

        $this->SetX($x + 114 + 2 * ($distancia - 2));
        $this->MultiCell(63, 2, date("d/m/Y") . ' - ' . date("H:i:s"), 0, 'R');
    }
}

$pdf = new Pdf_Banca('L', 'mm', 'letter');
$pdf->SetProtection(array('print', 'annot-forms'), "", PDF_PASSWORD_OWNER_TES);
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->setOrientation('L');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 40);
$pdf->SetAltoLinea(1.5);
$pdf->AddPage();
$datos = nivelBanca($pdf->lot_id, $pdf->fpa_id, $pdf->msc_id, $pdf->lot_ejercicio);
$pdf->Content($datos);
$pdf->Output();
