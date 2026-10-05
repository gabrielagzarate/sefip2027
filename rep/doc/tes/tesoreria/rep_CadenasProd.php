<?php

/**
 * Description of Pdf_CadenasProd
 *
 * @author ?
 * Collaborators: Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Created: 06-Ene-2019
 * Comment: -----
 * Objective: Elabora el reporte de acuerdo a la opcion que se requiera.
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
require_once dirname(__FILE__) . '/../../../../lib/tes/tesoreria/nivelCadenasProd.php';
require_once dirname(__FILE__) . '/../../../../ent/Usuario.php';

class Pdf_CadenasProd extends PDF
{
    private $usr_id;
    private $fpa_id;
    private $msc_id;
    private $lot_ejercicio;
    private $lot_id;
    private $lot_tipo;
    private $firmar;
    private $marca;
    private $ue;
    private $usuario;
    private $longitud;
    private $ancho;
    private $imagen;
    private $cedulaNombre;
    private $epg_id;
    private $ccd_id;
    private $presupuesto;
    private $tesoreria;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

        $this->usr_id = $_SESSION[_S]['usr_id'];

        $this->fpa_id = (isset($_POST['fpa_id']) && $_POST['fpa_id'] != '') ? $_POST['fpa_id'] : '';
        $this->msc_id = (isset($_POST['msc_id']) && $_POST['msc_id'] != '') ? $_POST['msc_id'] : '';
        $this->lot_ejercicio = (isset($_POST['lot_ejercicio']) && $_POST['lot_ejercicio'] != '') ? $_POST['lot_ejercicio'] : '';
        $this->lot_id = (isset($_POST['lot_id']) && $_POST['lot_id'] != '') ? $_POST['lot_id'] : '';
        $this->lot_tipo = (isset($_POST['lot_tipo']) && $_POST['lot_tipo'] != '') ? $_POST['lot_tipo'] : '';
        $this->firmar = true;
        $this->marca = ($_SERVER['SERVER_ADDR'] != "sefip.finanzasoaxaca.gob.mx") ? false : true;

        if ($_SESSION[_S]['nvj_id'] < 2) {
            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $this->ue->setUr_id($_SESSION[_S]['clasAdm-ur']);

            //Obtener Ue
            if (empty($_SESSION[_S]['ue_id'])) {
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $sql = "SELECT ue_id FROM cat_Usuario WHERE usr_id = ?";
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

        $this->cedulaNombre = 'DEPARTAMENTO DE PROGRAMACION DE MINISTRACIONES Y PAGOS';

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
        $iyt = 0;
        $getClave = 114;

        $this->setRouteImg($getClave);
        $this->Image('../' . $this->getRouteImg(), $ix + 164, $iy - 1, '100', '18', 'JPG', '');

        $this->SetFont('UniviaPro-Bold', 'B', 16);
        $this->SetTextColor(200, 200, 200);
        $this->RotatedText($ix + 5, $iy + 18, date('Y'), 90);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->SetXY($ix + 7, $iy + 4);
        $ytmp = $this->GetY();
        $this->Cell(195, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Regular', '', 8);
        $this->Cell(180, 5, utf8Decode('COORDINACIÓN DE CONTROL FINANCIERO'), 0, 1, 'L');

        $ur_nombre = ($_SESSION[_S]['nvj_id'] > 2) ? 'DPR' : 'UR';
        $getNombre = 'SECRETARÍA DE FINANZAS TESORERÍA';

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt += 16;
        $nombre_ur = TruncateCadena(utf8Decode(trim($ur_nombre . ": " . $getClave . " - " . $getNombre)), ($this->longitud - 10));
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
        if ($this->fpa_id == 3 && $this->lot_tipo == 5) {
            $this->SetWidths(array(5, 15, 20, 10, 15, 30, 30, 15, 20, 28, 30, 28, 20, 15));
            $this->SetAligns(array('L', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
            $this->SetFont('UniviaPro-Bold', 'B', 7);
            $this->setBorders('');
            $this->setAltoLinea(4);
            $this->Row(array(
                '#',
                'FE.PAGO',
                'LOTE EGR',
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
            ));
        } else {
            $this->SetWidths(array(5, 13, 15, 10, 11, 25, 27, 15, 20, 28, 30, 28, 18, 15));
            $this->SetAligns(array('L', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
            $this->SetFont('UniviaPro-Bold', 'B', 7);
            $this->setBorders('');
            $this->setAltoLinea(4);
            $this->Row(array(
                '#',
                'FE.PAGO',
                'LOTE EGR',
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
            ));
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
                        $this->SetFont('UniviaPro-Bold', 'B', 7); ///tipo de letra para cada titulo
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
                    $this->SetFont('UniviaPro-Bold', 'B', 7); ///tipo de letra para cada titulo
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
                    $this->SetFont('UniviaPro-Bold', 'B', 6); ///tipo de letra para cada titulo
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
                    if ($pintaEncabezado) {
                        $this->SetAltoLinea(1.5);
                        $this->ln(2);
                        $this->SetX(10);
                        $this->SetBorders(0);
                        $this->SetFillColor(221, 228, 233);
                        $this->RoundedRect(15, $this->GetY() - .5, 170, 4, 1, 'F', '1234');
                        switch ($this->ccd_id) {
                            case 4:
                                if ($this->presupuesto) {
                                    $this->SetWidths(array(15, 30, 90, 20, 20)); //175
                                    $this->SetAligns(array('L', 'L', 'L', 'L', 'L'));
                                    $this->SetFont('UniviaPro-Bold', 'B', 6);
                                    $this->Row(array('', utf8Decode($dato['nombreColumna']), 'NOMBRE', 'POA', utf8Decode('DIVISIÓN')));
                                } elseif ($this->tesoreria) {
                                    $this->SetWidths(array(15, 30, 110, 20)); //175
                                    $this->SetAligns(array('L', 'L', 'L', 'L'));
                                    $this->SetFont('UniviaPro-Bold', 'B', 6);
                                    $this->Row(array('', utf8Decode($dato['nombreColumna']), 'NOMBRE', 'PDP'));
                                } else {
                                    $this->SetWidths(array(15, 30, 130)); //175
                                    $this->SetAligns(array('L', 'L', 'L'));
                                    $this->SetFont('UniviaPro-Bold', 'B', 6);
                                    $this->Row(array('', utf8Decode($dato['nombreColumna']), 'NOMBRE'));
                                }
                                break;
                            default:
                                $this->SetWidths(array(15, 30, 130)); //175
                                $this->SetAligns(array('L', 'L', 'L'));
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(array('', utf8Decode($dato['nombreColumna']), 'NOMBRE'));
                                break;
                        }
                        $this->ln(1);
                    }
                    $this->SetRelleno("no");
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    switch ($this->ccd_id) {
                        case 4:
                            if ($this->presupuesto) {
                                $this->SetWidths(array(15, 30, 90, 20, 20)); //175
                                $this->SetAligns(array('L', 'L', 'L', 'L', 'L'));
                                $this->Row(array('', utf8Decode($dato['clave']), utf8Decode($dato['nombre']), utf8Decode($dato['ecf_poa']), utf8Decode($dato['ecf_division'])));
                            } elseif ($this->tesoreria) {
                                $this->SetWidths(array(15, 30, 110, 20)); //175
                                $this->SetAligns(array('L', 'L', 'L', 'L'));
                                $this->Row(array('', utf8Decode($dato['clave']), utf8Decode($dato['nombre']), utf8Decode($dato['pdp_id'])));
                            } else {
                                $this->SetWidths(array(15, 30, 130)); //175
                                $this->SetAligns(array('L', 'L', 'L'));
                                $this->Row(array('', utf8Decode($dato['clave']), utf8Decode($dato['nombre'])));
                            }
                            break;
                        default:
                            $this->SetWidths(array(15, 30, 130)); //175
                            $this->SetAligns(array('L', 'L', 'L'));
                            $this->Row(array('', utf8Decode($dato['clave']), utf8Decode($dato['nombre'])));
                            break;
                    }
                    $pintaEncabezado = false;
                    break;
                    break;
                case 11:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('BT');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 5);
                    $this->SetRelleno("no");

                    $this->SetAligns(array('L', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
                    $this->SetWidths(array(5, 13, 15, 10, 11, 25, 27, 15, 20, 28, 30, 28, 18, 15));
                    if ($this->fpa_id == 3 && $this->lot_tipo == 5) {
                        $this->Row(array(
                            $dato['contador'],
                            $dato['fecha_Pago'],
                            $dato['lote_egreso'],
                            $dato['lotDet_clc'],
                            $dato['Folio'],
                            utf8Decode($dato['responsable']),
                            utf8Decode($dato['lotDet_beneficiario']),
                            number_format($dato['importe'], 2),
                            $dato['nombre_banco'],
                            $dato['cargo'],
                            $dato['nombre_cuenta'],
                            $dato['clc_lineaCaptura'],
                            $dato['cve_fin'],
                            $dato['lote_id'],
                        ));
                    } else {
                        $this->Row(array(
                            $dato['contador'],
                            $dato['fecha_Pago'],
                            $dato['lote_egreso'],
                            $dato['lotDet_clc'],
                            $dato['Folio'],
                            utf8Decode($dato['responsable']),
                            utf8Decode($dato['lotDet_beneficiario']),
                            '$' . number_format($dato['lotDet_importe'], 2),
                            $dato['nombre_banco'],
                            $dato['cargo'],
                            $dato['nombre_cuenta'],
                            $dato['destino'],
                            $dato['cve_fin'],
                            $dato['lote_id'],
                        ));
                    }

                    $pintaEncabezado = true;
                    break;
                case 12:
                    $this->SetFont('UniviaPro-Bold', 'B', 8);
                    $this->CheckPageBreak(30);
                    $this->ln(4);
                    $this->MultiCell(180, 4, 'TOTAL: ' . number_format($dato['suma'], 2), 0, 'J');
                    $this->ln(4);
                    $this->setAltoLinea(3);
                    $this->SetX(15);
                    $this->MultiCell(180, 4, 'CADENA ORIGINAL: ' . md5($dato['cadena']), 0, 'J');

                    $this->ln(8);
                    $this->setAltoLinea(3);
                    $this->SetX(15);
                    $this->MultiCell(180, 4, utf8Decode($dato['lugar_DeProcedencia']), 0, 'C');

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

        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
        if ($this->firmar) {
            $y = -40;
            $this->SetY($y);
            $this->SetX($x);
            $this->MultiCell(57, 3, utf8Decode('ELABORADO Y REVISADO'), 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 3, utf8Decode('AUTORIZADO'), 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);

            $y = -20;
            $this->SetY($y);
            $this->SetX($x);
            $this->MultiCell(57, 4, utf8Decode("C.P. OLEGARIO GERARDO SILVA CRUZ"), 'T', 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 4, utf8Decode("IVÁN ELADIO GÓMEZ SANTIAGO"), 'T', 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);

            $y = -15;
            $this->SetY($y);
            $this->SetX($x);
            $this->MultiCell(57, 3, utf8Decode(""), 0, 'C');
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 3, utf8Decode("DEPARTAMENTO DE PLANEACION Y EVALUACION FINANCIERA"), 0, 'C');
        }

        $this->SetFont('univiapro', '', 5);
        $y = -7;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(
            57,
            2,
            utf8Decode($this->usuario->getUsr_id() . " - " . $this->usuario->getUsuario() . " "),
            0,
            'L'
        );

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

$pdf = new Pdf_CadenasProd('L', 'mm', 'letter');
$pdf->SetProtection(array('print', 'annot-forms'), "", PDF_PASSWORD_OWNER_TES);
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->setOrientation('L');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 40);
$pdf->SetAltoLinea(1.5);
$pdf->AddPage();
$datos = nivelCadenasProd($pdf->fpa_id, $pdf->msc_id, $pdf->lot_ejercicio, $pdf->lot_id, $pdf->lot_tipo);
$pdf->Content($datos);
$pdf->Output();
