<?php

/**
 * Description of Pdf_Poa01
 *
 * @author Adriana Lopez
 * 
 * Created: 31-Jul-2020
 * Comment:
 * Se agrega condicion $this->opc
 *         1 = Aplica, mostrar datos
 *         0 = No aplica, no mostrar datos
 * Estado Costeo:
 * - 1 = Abierto
 * - 2 = Cerrado
 * Objective: Generar Reporte de: IDENTIFICACIÓN INSTITUCIONAL (POA-01)
 * Regla de Negocio:
 * Esta pantalla no pregunta por permisos de escritura ó lectura
 *
 * */

session_start();
ini_set('memory_limit', '20M');
set_time_limit(0);
require_once __DIR__ . '/../../../lib/reportetabla.1.8.php';
require_once __DIR__ . '/../../../../lib/AccesoDatos.php';
require_once __DIR__ . '/../../../../ent/UnidadResponsable.php';
require_once __DIR__ . '/../../../../lib/TruncateCadena.php';
require_once __DIR__ . '/../../../../lib/constantes.php';
require_once __DIR__ . '/../../../../lib/helpers.php';

class Pdf_Poa01 extends PDF
{
    public $adm_gpo;
    public $adm_ur;
    public UnidadResponsable $ur;
    public $estadoCosteo;
    public $ur_cierreForzado;
    public $opc;
    public $longitud;
    public $nombreUR;

    public function __construct()
    {
        parent::__construct();

        $this->adm_gpo = (isset($_SESSION[_S]['clasAdm-gpo']) && $_SESSION[_S]['clasAdm-gpo'] != '') ? $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->adm_ur = (isset($_SESSION[_S]['clasAdm-ur']) && $_SESSION[_S]['clasAdm-ur'] != '') ? $_SESSION[_S]['clasAdm-ur'] : '';

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($this->adm_gpo);
        $this->ur->setUr_id($this->adm_ur);
        $this->ur->select();
        $this->estadoCosteo = $this->ur->getUr_estadoCosteo();
        $this->ur_cierreForzado = $this->ur->getUr_cierreForzado();
        $this->opc = $_POST['opc'] ?? 1;
    }

    public function Footer($lastPage = true)
    {
        $row = $this->getDatos();
        $this->SetY(-45);

        if ($row == '') {
            $conexion3 = new AccesoDatos();
            $conexion3->conectar();
            $conexion3->setSql('
                SELECT
                    ur_nombreAdministrativo, ur_nombreTitular, ur_cargoAdministrativo, ur_cargoTitular,
                    ur_nombrePlaneacion, ur_cargoPlaneacion
                FROM aux_UnidadResponsable
                WHERE gpo_id = ? AND ur_id = ?');
            $params = [$this->adm_gpo, $this->adm_ur];
            $conexion3->consultar($params);
            $conexion3->desconectar();
            $row = $conexion3->extraer();
        }

        if ($row['ur_nombrePlaneacion'] == '' && $row['ur_cargoPlaneacion'] == '') {
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(90, 2, utf8Decode('REVISÓ'), 0, 0, 'C');
            $this->Cell(90, 2, utf8Decode('AUTORIZÓ'), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->ln(1);
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(90, 2, utf8Decode($row['ur_nombreAdministrativo']), 0, 0, 'C');
            $this->Cell(90, 2, utf8Decode($row['ur_nombreTitular']), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(90, 2, utf8Decode($row['ur_cargoAdministrativo']), 0, 0, 'C');
            $this->Cell(90, 2, utf8Decode($row['ur_cargoTitular']), 0, 1, 'C');
        } else {
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(50, 2, utf8Decode('ELABORÓ'), 0, 0, 'C');
            $this->Cell(90, 2, utf8Decode('REVISÓ'), 0, 0, 'C');
            $this->Cell(50, 2, utf8Decode('AUTORIZÓ'), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->ln(1);
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(50, 2, utf8Decode($row['ur_nombrePlaneacion']), 0, 0, 'C');
            $this->Cell(90, 2, utf8Decode($row['ur_nombreAdministrativo']), 0, 0, 'C');
            $this->Cell(50, 2, utf8Decode($row['ur_nombreTitular']), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $y = $this->getY();
            $x = $this->getX();
            $this->setX(10);
            $this->multicell(50, 3, utf8Decode($row['ur_cargoPlaneacion']), 0, 'C');
            $this->setY($y);
            $this->setX($x + 70);
            $x = $this->getX();
            $this->multicell(50, 3, utf8Decode($row['ur_cargoAdministrativo']), 0, 'C');
            $this->setY($y);
            $this->setX($x + 70);
            $this->multicell(50, 3, utf8Decode($row['ur_cargoTitular']), 0, 'C');
        }
        $this->ln();

        $this->SetFont('univiapro', '', 5);
        $x = 4;
        $y = -7;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(57, 2, utf8Decode('' . ' - ' . '' . ' '), 0, 'L');

        $this->Cell(0, 0, utf8Decode('Página ' . $this->PageNo() . '/{nb}'), 0, 0, 'C');
        $this->Cell(0, 0, date('d/m/y') . ' - ' . date('H:i:s') . '        ', 1, 0, 'R');
    }

    public function Header()
    {
        $year = APP_EJERCICIO;

        $this->SetMargins(10, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);

        $this->pintarMarcasAguaPoa(
            $this->estadoCosteo,
            $this->ur_cierreForzado,
            $this->ur->getEur_id(),
            $this->opc ?? 1
        );

        $this->SetMargins(10, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();

        $logoAlto = $this->pintarLogoEncabezadoPoa($ix, $iy);

        $this->SetFont('UniviaPro-Bold', 'B', 16);
        $this->SetTextColor(200, 200, 200);
        $this->RotatedText($ix + 4, $iy + 17, $year, 90);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->SetXY($ix + 4, $iy + 4);
        $ytmp = $this->GetY();
        $this->Cell(195, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
        $this->SetXY($ix + 4, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(180, 5, 'PROGRAMA OPERATIVO ANUAL ' . $year, 0, 1, 'L');
        $this->SetXY($ix + 4, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 7);

        $this->longitud = 68;
        $this->nombreUR = TruncateCadena(utf8Decode(trim('UR: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre())), $this->longitud);

        $this->Cell(5, 5, substr($this->nombreUR, 0, $this->longitud), 0, 0, 'L');
        $this->Cell(163, 5, '', 0, 1, 'R');

        if (strlen($this->nombreUR) > $this->longitud) {
            $this->SetXY($ix + 4, $ytmp + 4);
            $this->Cell(5, 5, substr($this->nombreUR, $this->longitud, $this->longitud), 0, 0, 'L');
            $this->Cell(163, 5, '', 0, 1, 'R');
        }
        if (strlen($this->nombreUR) > $this->longitud * 2) {
            $this->SetXY($ix + 4, $ytmp + 8);
            $this->Cell(5, 5, substr($this->nombreUR, $this->longitud * 2, $this->longitud), 0, 0, 'L');
            $this->Cell(163, 5, '', 0, 1, 'R');
        }

        $this->pintarCodigoSubtituloPoa($ix, $iy, $logoAlto, 'POA-01', utf8Decode('IDENTIFICACIÓN INSTITUCIONAL'), 194, $ytmp);
        $this->ln(4);
    }
}

$sql = '
    SELECT
        gpo_id, ur_id, ur_nombre, ur_nombreTitular, ur_cargoTitular, ur_nombreAdministrativo, ur_cargoAdministrativo,
        ur_inicioPeriodicoOficial, IFNULL(ur_inicioFecha, 0) AS ur_inicioFecha,
        ur_modificacionPeriodico, ur_nombrePlaneacion, ur_cargoPlaneacion,
        IFNULL(ur_modificacionFecha, 0) AS ur_modificacionFecha,
        ur_internoArchivo, IFNULL(ur_planeacionFecha, 0) AS ur_planeacionFecha,
        IFNULL(ur_validacionFecha, 0) AS ur_validacionFecha, ur_organigramaArchivo, eur_id
    FROM cat_UnidadResponsable
    JOIN aux_UnidadResponsable USING(gpo_id, ur_id)
    WHERE gpo_id = ? AND ur_id = ?;
';
$params = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']];
$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar($params);

$pdf = new Pdf_Poa01();
$pdf->AliasNbPages();
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_POA);
$pdf->SetAutoPageBreak(true, 45);

if ($row = $conexion->extraer()) {
    do {
        $pdf->setDatos($row);
        $datos = $pdf->getDatos();
        $pdf->AddPage();

        $pdf->CheckPageBreak(25);
        $pdf->SetFont('UniviaPro-Bold', 'B', 8);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->Row([utf8Decode('DECRETO DE CREACIÓN')]);
        $pdf->ln(5);

        $pdf->SetFont('UniviaPro-Bold', 'B', 7);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->Row([utf8Decode('A) Inicio de operaciones')]);

        if ($datos['ur_inicioFecha'] != 0) {
            $fechaInicio = date('d-m-Y', strtotime($datos['ur_inicioFecha']));
        } else {
            $fechaInicio = '';
        }
        if ($datos['ur_modificacionFecha'] != 0) {
            $fechaModificacion = date('d-m-Y', strtotime($datos['ur_modificacionFecha']));
        } else {
            $fechaModificacion = '';
        }
        if ($datos['ur_planeacionFecha'] != 0) {
            $fechaPlaneacion = date('d-m-Y', strtotime($datos['ur_planeacionFecha']));
        } else {
            $fechaPlaneacion = '';
        }
        if ($datos['ur_validacionFecha'] != 0) {
            $fechaOrganigrama = date('d-m-Y', strtotime($datos['ur_validacionFecha']));
        } else {
            $fechaOrganigrama = '';
        }

        $pdf->ln(4);
        $pdf->SetFont('UniviaPro-Regular', '', 6.5);
        $pdf->SetWidths([60, 70, 40, 20]);
        $pdf->SetAligns(['L', 'L', 'L', 'L']);
        $pdf->Row([
            utf8Decode('No. de Periódico Oficial:'),
            utf8Decode($datos['ur_inicioPeriodicoOficial']),
            utf8Decode('Fecha de Publicación:'),
            $fechaInicio,
        ]);
        $pdf->ln(6);

        $pdf->CheckPageBreak(25);
        $pdf->SetFont('UniviaPro-Bold', 'B', 7);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->Row([utf8Decode('B) Modificaciones y reformas a las disposiciones del decreto de creación')]);
        $pdf->SetFont('UniviaPro-Regular', '', 6.5);
        $pdf->SetWidths([60, 70, 40, 20]);
        $pdf->SetAligns(['L', 'L', 'L', 'L']);
        $pdf->ln(4);
        $pdf->Row([
            utf8Decode('No. de Periódico Of. de la última Modificación:'),
            utf8Decode($datos['ur_modificacionPeriodico']),
            utf8Decode('Fecha de Publicación:'),
            $fechaModificacion,
        ]);
        $pdf->ln(10);

        $pdf->CheckPageBreak(25);
        $pdf->SetFont('UniviaPro-Bold', 'B', 8);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->Row(['REGLAMENTO INTERNO']);
        $pdf->ln(5);
        $pdf->SetFont('UniviaPro-Regular', '', 6.5);
        $pdf->SetWidths([60, 70, 40, 20]);
        $pdf->SetAligns(['L', 'L', 'L', 'L']);
        $pdf->Row([
            utf8Decode('Archivo Digital:'),
            utf8Decode($datos['ur_internoArchivo']),
            utf8Decode('Fecha de Publicación:'),
            $fechaPlaneacion,
        ]);
        $pdf->ln(10);

        $pdf->CheckPageBreak(25);
        $pdf->SetFont('UniviaPro-Bold', 'B', 8);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->Row([utf8Decode('ESTRUCTURA ORGÁNICA')]);
        $pdf->ln(5);
        $pdf->SetFont('UniviaPro-Regular', '', 6.5);
        $pdf->SetWidths([60, 70, 40, 20]);
        $pdf->SetAligns(['L', 'L', 'L', 'L']);
        $pdf->Row([
            utf8Decode('Archivo Digital:'),
            utf8Decode($datos['ur_organigramaArchivo']),
            utf8Decode('Fecha de Publicación:'),
            $fechaOrganigrama,
        ]);
    } while ($row = $conexion->extraer());
}

$conexion->desconectar();
$pdf->Output();
