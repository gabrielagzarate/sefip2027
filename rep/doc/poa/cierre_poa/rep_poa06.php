<?php

/**
 * Description of Pdf_Poa06
 *
 * @author Adriana Lopez
 * Created: 10-Jun-2026
 * Comment:
 * Se agrega condicion $this->opc
 *         1 = Aplica, mostrar datos
 *         0 = No aplica, no mostrar datos
 * Estado Costeo:
 * - 1 = Abierto
 * - 2 = Cerrado
 * Objective: Generar Reporte de: RESUMEN DE PRESUPUESTO POR CLASIFICACIÓN FUNCIONAL (POA-06)
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

class Pdf_Poa06 extends PDF
{
    public $gpo_id = '';
    public $ur_id = '';
    public $nvj;
    public $adm_gpo;
    public $adm_ur;
    public $adm_ue;
    public UnidadResponsable $ur;
    public $estadoCosteo;
    public $ur_cierreForzado;
    public $opc;
    public $longitud;
    public $nombreUR;

    public function __construct()
    {
        parent::__construct();

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();

        $this->gpo_id = $_SESSION[_S]['gpo_id'] ?? '';
        $this->ur_id = $_SESSION[_S]['ur_id'] ?? '';
        $this->nvj = $_SESSION[_S]['nvj_id'] ?? '';
        $this->adm_gpo = $_SESSION[_S]['clasAdm-gpo'] ?? '';
        $this->adm_ur = $_SESSION[_S]['clasAdm-ur'] ?? '';
        $this->adm_ue = $_SESSION[_S]['clasAdm-ue'] ?? '';

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
                SELECT ur_nombreAdministrativo, ur_nombreTitular, ur_cargoAdministrativo, ur_cargoTitular,
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
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $ytmp = $this->GetY();

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

        $this->pintarCodigoSubtituloPoa(
            $ix,
            $iy,
            $logoAlto,
            'POA-06',
            utf8Decode('RESUMEN DE PRESUPUESTO POR CLASIFICACIÓN FUNCIONAL'),
            194,
            $ytmp
        );

        $ytmp = $this->GetY();
        $this->setX(10);
        $this->setDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, 203, $ytmp);
        $this->ln(4);
        $this->setDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);

        $this->setX(10);
        $this->SetWidths([163, 30]);
        $this->SetAligns(['L', 'R']);
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->setBorders('');
        $this->setAltoLinea(4);
        $this->SetRelleno('si');
        $this->Row(['CONCEPTO', 'PRESUPUESTO']);
        $this->ln(1);
        $this->setBorders(0);
        $this->SetFillColor(255, 255, 255);
        $this->SetRelleno('no');
    }

    private function formatoPresupuesto($monto): string
    {
        return number_format((float) $monto, 2);
    }

    public function imprimirFilaClasificacion(array $row, bool $esPrimerPrograma = false): void
    {
        $nivel = (int) $row['nivel'];
        if ($nivel < 1 || $nivel > 4) {
            return;
        }

        $sangrias = [1 => 0, 2 => 4, 3 => 8, 4 => 12];
        $sangria = $sangrias[$nivel];
        $anchoConcepto = 163 - $sangria;

        $this->CheckPageBreak(12);
        $this->setBorders(0);
        $this->SetRelleno('no');
        $this->setAltoLinea($nivel <= 2 ? 2 : 1.5);
        $this->SetAligns(['L', 'R']);
        $this->SetWidths([$anchoConcepto, 30]);

        $concepto = strtoupper(utf8Decode($row['nombre']));
        $presupuesto = $this->formatoPresupuesto($row['pre_original']);

        switch ($nivel) {
            case 1:
                if (!$esPrimerPrograma) {
                    $this->ln(3);
                }
                $this->setDrawColor(200, 200, 200);
                $this->setBorders('T');
                $this->SetX(10);
                $this->SetFont('UniviaPro-Bold', 'B', 7);
                $this->SetTextColor(0, 0, 0);
                $this->Row([$concepto, $presupuesto]);
                $this->setBorders(0);
                break;
            case 2:
                $this->SetX(10 + $sangria);
                $this->SetFont('UniviaPro-Bold', 'B', 7);
                $this->SetTextColor(0, 0, 0);
                $this->Row([$concepto, $presupuesto]);
                break;
            case 3:
                $this->SetX(10 + $sangria);
                $this->SetFont('UniviaPro-Regular', '', 6.5);
                $this->SetTextColor(130, 130, 130);
                $this->Row([$concepto, $presupuesto]);
                $this->SetTextColor(0, 0, 0);
                break;
            case 4:
                $this->SetX(10 + $sangria);
                $this->SetFont('UniviaPro-Regular', '', 6.5);
                $this->SetTextColor(130, 130, 130);
                $this->Row([$concepto, $presupuesto]);
                $this->SetTextColor(0, 0, 0);
                break;
        }
    }

    public function imprimirTotalUr(float $monto): void
    {
        if ($monto <= 0) {
            return;
        }

        $this->CheckPageBreak(12);
        $this->ln(2);
        $this->SetX(10);
        $this->SetFillColor(199, 209, 219);
        $this->SetRelleno('si');
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->SetTextColor(0, 0, 0);
        $this->setBorders('');
        $this->SetWidths([163, 30]);
        $this->SetAligns(['L', 'R']);
        $this->setAltoLinea(2);
        $this->Row(['TOTAL UNIDAD RESPONSABLE', $this->formatoPresupuesto($monto)]);
        $this->SetFillColor(255, 255, 255);
        $this->SetRelleno('no');
        $this->setBorders(0);
    }
}

$pdf = new Pdf_Poa06('P', 'mm', 'Letter');
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_POA);
$pdf->setOrientation('P');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 40);
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');

$filtro = '';
$params = [];
$joinBase = '
    INNER JOIN cat_UnidadResponsable USING(gpo_id, ur_id)
    INNER JOIN aux_UnidadResponsable USING(gpo_id, ur_id)
    INNER JOIN cat_Programa USING(gpo_id, ur_id, prg_id)
    JOIN vin_Programatico USING(epg_id)
    JOIN enc_Funcional USING(efn_id)
    INNER JOIN cat_Finalidad USING(fin_id)
    INNER JOIN cat_Funcion USING(fin_id, fun_id)
    INNER JOIN cat_Subfuncion USING(fin_id, fun_id, sfu_id)
    JOIN presupuestoOriginal USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
';

if ($pdf->nvj > 1) {
    $filtro = ' WHERE gpo_id = ? AND ur_id = ?';
    $params = [
        $pdf->adm_gpo, $pdf->adm_ur,
        $pdf->adm_gpo, $pdf->adm_ur,
        $pdf->adm_gpo, $pdf->adm_ur,
        $pdf->adm_gpo, $pdf->adm_ur,
        $pdf->adm_gpo, $pdf->adm_ur,
    ];
} else {
    $filtro = ' WHERE gpo_id = ? AND ur_id = ? AND ue_id = ?';
    $params = [
        $pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue,
        $pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue,
        $pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue,
        $pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue,
        $pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue,
    ];
}

$sql = '
    SELECT "PROGRAMA" AS es, LPAD(prg_id, 3, 0) AS clave,
        CONCAT("PROGRAMA ", LPAD(prg_id, 3, 0), " ", prg_nombre) AS nombre,
        gpo_id, ur_id, prg_id, "" AS fin_id, "" AS fun_id, "" AS sfu_id,
        SUM(pre_original) AS pre_original,
        ur_nombre, ur_nombreTitular, ur_nombreAdministrativo, ur_cargoTitular,
        ur_cargoAdministrativo, ur_nombrePlaneacion, ur_cargoPlaneacion, eur_id, 1 AS nivel
    FROM presupuesto
    ' . $joinBase . $filtro . '
    GROUP BY gpo_id, ur_id, prg_id, prg_nombre
    HAVING pre_original > 0
    UNION ALL
        SELECT "FINALIDAD" AS es, fin_id AS clave, fin_nombre AS nombre,
            gpo_id, ur_id, prg_id, fin_id, "" AS fun_id, "" AS sfu_id,
            SUM(pre_original) AS pre_original,
            ur_nombre, ur_nombreTitular, ur_nombreAdministrativo, ur_cargoTitular,
            ur_cargoAdministrativo, ur_nombrePlaneacion, ur_cargoPlaneacion, eur_id, 2 AS nivel
        FROM presupuesto
        ' . $joinBase . $filtro . '
        GROUP BY gpo_id, ur_id, prg_id, fin_id, fin_nombre
        HAVING pre_original > 0
    UNION ALL
        SELECT "FUNCION" AS es, fun_id AS clave, fun_nombre AS nombre,
            gpo_id, ur_id, prg_id, fin_id, fun_id, "" AS sfu_id,
            SUM(pre_original) AS pre_original,
            ur_nombre, ur_nombreTitular, ur_nombreAdministrativo, ur_cargoTitular,
            ur_cargoAdministrativo, ur_nombrePlaneacion, ur_cargoPlaneacion, eur_id, 3 AS nivel
        FROM presupuesto
        ' . $joinBase . $filtro . '
        GROUP BY gpo_id, ur_id, prg_id, fin_id, fun_id, fun_nombre
        HAVING pre_original > 0
    UNION ALL
        SELECT "SUBFUNCION" AS es, sfu_id AS clave, sfu_nombre AS nombre,
            gpo_id, ur_id, prg_id, fin_id, fun_id, sfu_id,
            SUM(pre_original) AS pre_original,
            ur_nombre, ur_nombreTitular, ur_nombreAdministrativo, ur_cargoTitular,
            ur_cargoAdministrativo, ur_nombrePlaneacion, ur_cargoPlaneacion, eur_id, 4 AS nivel
        FROM presupuesto
        ' . $joinBase . $filtro . '
        GROUP BY gpo_id, ur_id, prg_id, fin_id, fun_id, sfu_id, sfu_nombre
        HAVING pre_original > 0
    UNION ALL
        SELECT "UR" AS es, ur_clave AS clave, "TOTAL UNIDAD RESPONSABLE" AS nombre,
            gpo_id, ur_id, "" AS prg_id, "" AS fin_id, "" AS fun_id, "" AS sfu_id,
            SUM(pre_original) AS pre_original,
            ur_nombre, ur_nombreTitular, ur_nombreAdministrativo, ur_cargoTitular,
            ur_cargoAdministrativo, ur_nombrePlaneacion, ur_cargoPlaneacion, eur_id, 5 AS nivel
        FROM presupuesto
        ' . $joinBase . $filtro . '
        GROUP BY gpo_id, ur_id, ur_clave
        HAVING pre_original > 0
    ORDER BY gpo_id, ur_id, LPAD(prg_id, 3, 0), fin_id, fun_id, sfu_id, nivel;
';
$datos = new AccesoDatos();
$datos->conectar();
$datos->setSql($sql);
$datos->consultar($params);
$datos->desconectar();

$row = $datos->extraer();
$pdf->setDatos($row);
$pdf->setAltoLinea(1.5);
$pdf->AddPage();

$totalUrMonto = 0;
$esPrimerPrograma = true;

if ($row) {
    do {
        $pdf->setDatos($row);

        if ((int) $row['nivel'] === 5) {
            $totalUrMonto = (float) $row['pre_original'];
            continue;
        }

        if ((int) $row['nivel'] === 1) {
            $pdf->imprimirFilaClasificacion($row, $esPrimerPrograma);
            $esPrimerPrograma = false;
            continue;
        }

        $pdf->imprimirFilaClasificacion($row);
    } while ($row = $datos->extraer());

    $pdf->imprimirTotalUr($totalUrMonto);
}

$pdf->Output();
