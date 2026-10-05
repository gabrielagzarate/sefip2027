<?php
/**
 * Description of Pdf_Fondeo
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 12-Dic-2019
 * Comment: -----
 * Objective: Elabora el reporte de los movimietos bancarios en estado autorizado y de tipo traspaso, inversion y fondeo.
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
require_once dirname(__FILE__) . '/../../../../lib/tes/ministracion/nivelFondeo.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';

class Pdf_Fondeo extends PDF
{
    public $fecha;
    public $fechaMax;
    public $tipo;
    public $mbn_id;
    public $usr_id;
    public $firmar;
    public $marca;
    public $ue;
    public $usuario;
    public $longitud;
    public $ancho;
    public $imagen;
    public $cedulaNombre;
    public $epg_id;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

        $this->fecha = (isset($_POST['vinFondeo']) && $_POST['vinFondeo'] != '') ? $_POST['vinFondeo'] : '';
        $this->fechaMax = (isset($_POST['fechaMax']) && $_POST['fechaMax'] != '') ? $_POST['fechaMax'] : '';
        $this->tipo = (isset($_POST['tipo']) && $_POST['tipo'] != '') ? $_POST['tipo'] : '';
        $this->mbn_id = (isset($_POST['mbn_id']) && $_POST['mbn_id'] != '') ? $_POST['mbn_id'] : '';

        $this->usr_id = $_SESSION[_S]['usr_id'];

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
                $sql = "SELECT ue_id FROM cat_Usuario WHERE usr_id= :usr_id ";
                $params = ['usr_id' => $_SESSION[_S]['usr_id']];
                $conexion->setSql($sql);
                $conexion->consultar($params);
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

        $this->cedulaNombre = 'DEPARTAMENTO DE PLANEACIÓN Y EVALUACIÓN FINANCIERA';

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
        $this->Image($this->getRouteImg(), $ix + 97, $iy - 1, '100', '18', 'JPG', '');

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
        $this->Cell(180, 5, utf8Decode("SECRETARÍA DE FINANZAS TESORERÍA"), 0, 1, 'L');

        $ur_nombre = '';
        $getNombre = 'COORDINACIÓN DE CONTROL FINANCIERO';

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt = 0;
        $iyt += 16;
        $nombre_ur = TruncateCadena(utf8Decode(trim($ur_nombre . $getClave . " - " . $getNombre)), ($this->longitud - 10));
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
        //Fin de imagen

        $this->setX(10);
        $this->SetWidths(array(30, 124, 39));
        $this->SetAligns(array('L', 'L', 'R'));
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->setBorders('');
        $this->setAltoLinea(4);
        $this->SetRelleno("si");
        $this->ln(1);
        $this->setBorders(0);
        $this->SetFillColor(255, 255, 255);

        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(180, 5, '', 0, 1, 'L');
        $this->SetXY($ix + 4, $ytmp + 4);
        $ytmp = $this->GetY();

        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(180, 5, 'PARA: SERGIO ANTONIO ENRIQUEZ JIMENEZ', 0, 1, 'L');
        $this->SetXY($ix + 4, $ytmp + 4);
        $ytmp = $this->GetY();

        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(180, 5, utf8Decode('DEPARTAMENTO: PROGRAMACIÓN Y MINISTRACIÓN DE PAGOS'), 0, 1, 'L');
        $this->SetXY($ix + 4, $ytmp + 4);
        $ytmp = $this->GetY();

        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(180, 5, 'DE: ALBERTO MENDEZ CANSECO', 0, 1, 'L');
        $this->SetXY($ix + 4, $ytmp + 4);
        $ytmp = $this->GetY();

        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(180, 5, utf8Decode('DEPARTAMENTO: PLANEACIÓN Y EVALUACIÓN FINANCIERA'), 0, 1, 'L');
        $this->SetXY($ix + 4, $ytmp + 4);
        $ytmp = $this->GetY();

        $this->SetFont('UniviaPro-Regular', '', 8);

        $this->SetXY($ix + 4, $ytmp + 4);
        $ytmp = $this->GetY();

        $this->MultiCell(180, 4, utf8Decode('Solicito atentamente efectuar el traspaso entre cuentas propias por medio del Sistema de Pagos Electrónicos Interbancarios que a continuación se detallan:'), 0, 'J');
        //generamos los titulos de la tabla

        $this->SetXY($ix + 5, $ytmp + 10);
        $ytmp = $this->GetY();

        $this->SetDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->SetFont('UniviaPro-Bold', 'B', 5);
        $this->SetAltoLinea(4);
        $this->SetRelleno("si");
        $this->SetWidths(array(5, 25, 25, 30, 30, 20, 33, 17));
        $this->SetAligns(array('L', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
        $this->Row(array('#', 'BANCO', 'DE CTA', 'BANCO', 'PARA CTA', 'IMPORTE', 'CONCEPTO', 'FECHA'));
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
                    $this->SetX(15);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);

                    $this->SetRelleno("no");
                    $this->SetFont('UniviaPro-Regular', '', 6);

                    $this->SetWidths(array(5, 25, 25, 30, 30, 20, 33, 17));
                    $this->SetAligns(array('L', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
                    $this->Row(array($dato['contador'], $dato['cargo'], $dato['cbs_id_cargo'], utf8Decode($dato['abono']), utf8Decode($dato['cbs_id_abono']), '$' . $dato['importe'], utf8Decode($dato['mbn_observacion']), $dato['mbn_fechaMovimiento']));
                    $this->ln(5);
                    $pintaEncabezado = false;
                    break;
                case 11:
                    $this->SetFont('UniviaPro-Bold', 'B', 8);
                    $this->CheckPageBreak(30);
                    $this->ln(8);
                    $this->setAltoLinea(3);
                    $this->SetX(15);
                    $this->MultiCell(180, 4, 'CADENA ORIGINAL: ' . md5($dato['cadena_original']), 0, 'J');

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
        $tipo = $this->tipo;
        $fechaMax = $this->fechaMax;
        $fecha = $this->fecha;
        $parametros = [
            'tipo' => $tipo,
        ];
        if (!empty($fecha)) {
            $fecha = explode("/", $fecha);
            $fecha = $fecha[2] . "-" . $fecha[1] . "-" . $fecha[0];
            $parametros += [
                'mbn_fechaMovimiento' => $fecha];
            $sqlFechaMomiviento = 'AND mbn_fechaMovimiento >= :mbn_fechaMovimiento ';
        }
        if (!empty($fechaMax)) {
            $fechaMax = explode("/", $fechaMax);
            $fechaMax = $fechaMax[2] . "-" . $fechaMax[1] . "-" . $fechaMax[0];
            $parametros += [
                'mbn_fechaMovimientoMax' => $fechaMax,
            ];

            $sqlFechaMomiviento .= 'AND mbn_fechaMovimiento <= :mbn_fechaMovimientoMax ';
        }
        $extra = '';

        if (isset($this->mbn_id) && !empty($this->mbn_id)) {
            $parametros += [
                'mbn_id' => $this->mbn_id,
            ];
            $extra = 'AND mbn_id = :mbn_id';
        }

        $row = $this->getDatos();
        $tmpb = $this->GetBorders();
        $this->SetY(-40);
        $sql = 'SELECT CONCAT(usr_nombre," ",usr_paterno," ",usr_materno) AS nombre,
                    usr_titulo, usr_puesto,
                    mbn_fechaAutorizacion, mbn_id, tmb_id
                FROM ' . BD_USUARIO . '.cat_Usuario AS u
                INNER JOIN ' . BD_TESORERIA . '.ban_MovimientoBancario USING (usr_id)
                WHERE emb_id = 3
                AND tmb_id = :tipo
                ' . $sqlFechaMomiviento . '
                ' . $extra . '
                GROUP BY mbn_id, mbn_fechaMovimiento';

        $conexion3 = new AccesoDatos();
        $conexion3->conectar();
        $conexion3->setSql($sql);
        $conexion3->consultar($parametros);
        $conexion3->desconectar();
        $row = $conexion3->extraer();

        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
        $this->Cell(90, 2, 'ELABORO', 0, 0, 'C');
        $this->Cell(90, 2, utf8Decode('AUTORIZÓ'), 0, 1, 'C');

        $this->Cell(130, 2, '', 0, 1, 'C');
        $this->ln(1);
        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
        $this->Cell(90, 2, utf8Decode($row['usr_titulo'] . ' ' . $row['nombre']), 0, 0, 'C');
        $this->Cell(90, 2, utf8Decode('  ALBERTO MENDEZ CANSECO'), 0, 1, 'C');

        $this->Cell(130, 2, '', 0, 1, 'C');
        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
        $this->Cell(90, 2, utf8Decode($row['usr_puesto']), 0, 0, 'C');
        $this->Cell(90, 2, utf8Decode(' DEPARTAMENTO DE PLANEACIÓN Y EVALUACIÓN FINANCIERA'), 0, 1, 'C');

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

$pdf = new Pdf_Fondeo('P', 'mm', 'letter');
$pdf->SetProtection(array('print', 'annot-forms'), "", PDF_PASSWORD_OWNER_TES);
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->setOrientation('P');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 40);
$pdf->SetAltoLinea(1.5);
$pdf->AddPage();
$datos = nivelFondeo($pdf->tipo, $pdf->fecha, $pdf->fechaMax, $pdf->mbn_id);
$pdf->Content($datos);
$pdf->Output();
