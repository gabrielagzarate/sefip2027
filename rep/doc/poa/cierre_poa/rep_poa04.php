<?php

/**
 * Description of Pdf_Poa04
 *
 * @author Adriana Lopez
 * Created: 09-Jun-2026
 * Objective: Generar Reporte de: ESTRUCTURA PROGRAMÁTICA (POA-04)
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

class Pdf_Poa04 extends PDF
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
    public $margenIzq = 10;
    public $anchoContenido = 193;
    public $anchoCol = 93;
    public $anchoGap = 7;

    public function __construct()
    {
        parent::__construct();

        $this->gpo_id = $_SESSION[_S]['gpo_id'] ?? '';
        $this->ur_id = $_SESSION[_S]['ur_id'] ?? '';
        $this->nvj = $_SESSION[_S]['nvj_id'] ?? '';
        $this->adm_gpo = $_SESSION[_S]['clasAdm-gpo'] ?? '';
        $this->adm_ur = $_SESSION[_S]['clasAdm-ur'] ?? '';
        $this->adm_ue = $_SESSION[_S]['clasAdm-ue'] ?? '';

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
        $this->nombreUR = TruncateCadena(
            utf8Decode(trim('UR: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre())),
            $this->longitud
        );

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

        $this->pintarCodigoSubtituloPoa($ix, $iy, $logoAlto, 'POA-04', utf8Decode('ESTRUCTURA PROGRAMÁTICA'), 194, $ytmp);
        $this->ln(4);
        $this->iniciarContenido();
    }

    private function iniciarContenido(): void
    {
        $this->setX($this->margenIzq);
        $this->SetAligns(['L']);
        $this->SetRelleno('no');
        $this->SetBorders(0);
        $this->setBorders(0);
        $this->setAltoLinea(4);
        $this->SetFont('UniviaPro-Regular', '', 7);
    }

    private function textoReporte(?string $valor): string
    {
        if ($valor === null || trim($valor) === '' || strtoupper(trim($valor)) === 'N/A') {
            return 'NO APLICA';
        }

        return strtoupper(utf8Decode($valor));
    }

    private function altoLineaMm(): float
    {
        return 4;
    }

    private function espacioDisponible(): float
    {
        return $this->PageBreakTrigger - $this->GetY();
    }

    private function estimarAltoFilas(array $textos, float $ancho = 193): float
    {
        $this->SetFont('UniviaPro-Regular', '', 7);
        $alto = 0;

        foreach ($textos as $texto) {
            $lineas = max(1, $this->NbLines($ancho, $texto));
            $alto += $lineas * $this->altoLineaMm();
        }

        return $alto;
    }

    private function textosActividades(array $actividades): array
    {
        $textos = [];

        foreach ($actividades as $actividad) {
            $textos[] = str_pad($actividad['oa_id'], 3, '0', STR_PAD_LEFT) . ' '
                . $this->textoReporte($actividad['oa_nombre']);
        }

        return $textos;
    }

    private function estimarAltoProyectoYObraActividad(array $actividades): float
    {
        $textosActividades = $this->textosActividades($actividades);

        return ($this->altoLineaMm() * 2) + 1
            + $this->altoLineaMm()
            + $this->estimarAltoFilas($textosActividades)
            + 4;
    }

    private function estimarAltoSubprograma(array $sub): float
    {
        $actividades = $sub['actividades'] ?? [];

        return ($this->altoLineaMm() * 5)
            + 1
            + $this->estimarAltoProyectoYObraActividad($actividades);
    }

    private function nuevaPaginaSiNoCabe(float $altoNecesario): void
    {
        if ($altoNecesario > $this->espacioDisponible()) {
            $this->AddPage($this->CurOrientation);
        }
    }

    private function imprimirProyecto(array $sub): void
    {
        $this->iniciarContenido();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->SetWidths([193]);
        $this->Row([utf8Decode('PROYECTO')]);
        $this->SetFont('UniviaPro-Regular', '', 6.5);
        $this->SetWidths([193]);
        $pyNombre = ($sub['py_nombre'] ?? '') !== '' ? $sub['py_nombre'] : '----';
        $this->Row([
            str_pad($sub['py_id'] ?? '0', 3, '0', STR_PAD_LEFT) . ': ' . $this->textoReporte($pyNombre),
        ]);
        $this->ln(1);
    }

    private function imprimirObraActividad(array $actividades): void
    {
        $textosActividades = $this->textosActividades($actividades);

        $this->iniciarContenido();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->SetWidths([193]);
        $this->Row([utf8Decode('OBRA ACTIVIDAD')]);
        $this->SetFont('UniviaPro-Regular', '', 7);
        $this->SetWidths([193]);

        if ($textosActividades === []) {
            $this->ln(4);
            return;
        }

        $total = count($textosActividades);

        for ($i = 0; $i < $total; $i++) {
            $texto = $textosActividades[$i];
            $altoFila = max(1, $this->NbLines(193, $texto)) * $this->altoLineaMm();
            $pendientes = $total - $i;
            $altoPendiente = $this->estimarAltoFilas(array_slice($textosActividades, $i));
            $espacioDisponible = $this->espacioDisponible();

            if ($pendientes > 1 && $altoPendiente > $espacioDisponible) {
                $altoRestanteSinActual = $this->estimarAltoFilas(array_slice($textosActividades, $i + 1));

                if ($altoFila <= $espacioDisponible && $altoRestanteSinActual > ($espacioDisponible - $altoFila)) {
                    $this->AddPage($this->CurOrientation);
                    $this->iniciarContenido();
                    $this->SetFont('UniviaPro-Regular', '', 7);
                    $this->SetWidths([193]);
                    $espacioDisponible = $this->espacioDisponible();
                }
            }

            if ($altoFila > $espacioDisponible) {
                $this->AddPage($this->CurOrientation);
                $this->iniciarContenido();
                $this->SetFont('UniviaPro-Regular', '', 7);
                $this->SetWidths([193]);
            }

            $this->Row([$texto]);
        }

        $this->ln(4);
    }

    private function imprimirProyectoYObraActividad(array $sub): void
    {
        $actividades = $sub['actividades'] ?? [];
        $this->imprimirProyecto($sub);
        $this->imprimirObraActividad($actividades);
    }

    public function imprimirPrograma(array $datos): void
    {
        $this->CheckPageBreak(20);
        $this->iniciarContenido();
        $this->SetWidths([22, 171]);
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Row([
            utf8Decode('PROGRAMA:'),
            str_pad($datos['prgs_id'], 3, '0', STR_PAD_LEFT) . ' ' . $this->textoReporte($datos['prg_nombre']),
        ]);
        $this->SetFont('UniviaPro-Regular', '', 7);
        $this->ln(3);

        $this->CheckPageBreak(25);
        $this->iniciarContenido();
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->SetWidths([193]);
        $this->Row([utf8Decode('VINCULACIÓN PED')]);
        $this->SetFont('UniviaPro-Regular', '', 6.5);
        $this->SetWidths([18, 175]);
        $this->Row([utf8Decode('EJE:'), $this->textoReporte($datos['eje_nombre'] ?? '')]);
        $this->Row([utf8Decode('TEMA:'), $this->textoReporte($datos['tem_nombre'] ?? '')]);
        $this->Row([utf8Decode('OBJETIVO:'), $this->textoReporte($datos['obj_nombre'] ?? '')]);
        $this->ln(3);

        $this->CheckPageBreak(25);
        $this->iniciarContenido();
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->SetWidths([$this->anchoCol, $this->anchoGap, $this->anchoCol]);
        $this->Row([
            utf8Decode('PLAN ESTRATÉGICO SECTORIAL'),
            '',
            utf8Decode('PLAN ESPECIAL'),
        ]);
        $this->SetFont('UniviaPro-Regular', '', 6.5);
        $this->SetWidths([20, 73, $this->anchoGap, 20, 73]);
        $this->Row([
            utf8Decode('SECTOR:'),
            $this->textoReporte($datos['spe_nombre'] ?? ''),
            '',
            utf8Decode('PLAN:'),
            $this->textoReporte($datos['ple_nombre'] ?? ''),
        ]);
        $this->Row([
            utf8Decode('OBJETIVO:'),
            $this->textoReporte($datos['obp_nombre'] ?? ''),
            '',
            utf8Decode('OBJETIVO:'),
            $this->textoReporte($datos['ope_nombre'] ?? ''),
        ]);
        $this->ln(3);

        $this->CheckPageBreak(20);
        $this->iniciarContenido();
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->SetWidths([193]);
        $this->Row([utf8Decode('ODS')]);
        $this->SetFont('UniviaPro-Regular', '', 6.5);
        $this->SetWidths([18, 175]);
        $this->Row([utf8Decode('ODS:'), $this->textoReporte($datos['ods_nombre'] ?? '')]);
        $this->ln(3);

        $this->CheckPageBreak(15);
        $this->iniciarContenido();
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->SetWidths([193]);
        $this->Row([utf8Decode('CLASIFICACIÓN FUNCIONAL')]);
        $this->SetFont('UniviaPro-Regular', '', 6.5);
        $this->SetWidths([22, 171]);
        $this->Row([utf8Decode('FINALIDAD:'), $this->textoReporte($datos['fin_nombre'] ?? '')]);
        $this->ln(2);
    }

    public function imprimirSubprograma(array $sub, float $margenSuperior = 0): void
    {
        if ($margenSuperior > 0) {
            $this->ln($margenSuperior);
        }

        $this->nuevaPaginaSiNoCabe($this->estimarAltoSubprograma($sub));
        $this->iniciarContenido();

        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->SetWidths([193]);
        $this->Row([
            utf8Decode('SubPr: ' . str_pad($sub['sprs_id'], 2, '0', STR_PAD_LEFT) . ' ')
            . $this->textoReporte($sub['spr_nombre']),
        ]);

        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->SetWidths([$this->anchoCol, $this->anchoGap, $this->anchoCol]);
        $this->Row([
            utf8Decode('CLASIFICACIÓN FUNCIONAL'),
            '',
            utf8Decode('CLASIFICACIÓN PROGRAMÁTICA'),
        ]);

        $this->SetFont('UniviaPro-Regular', '', 6.5);
        $this->SetWidths([22, 71, $this->anchoGap, 28, 65]);
        $this->Row([
            utf8Decode('Función:'),
            $this->textoReporte($sub['fun_nombre'] ?? ''),
            '',
            utf8Decode('Tipología:'),
            $this->textoReporte($sub['tpg_nombre'] ?? ''),
        ]);
        $this->Row([
            utf8Decode('Subfunción:'),
            $this->textoReporte($sub['sfu_nombre'] ?? ''),
            '',
            utf8Decode('Subtipología:'),
            $this->textoReporte($sub['stp_nombre'] ?? ''),
        ]);
        $this->Row([
            '',
            '',
            '',
            utf8Decode('Clasificación Tipología:'),
            $this->textoReporte($sub['ctp_nombre'] ?? ''),
        ]);
        $this->ln(1);

        $this->imprimirProyectoYObraActividad($sub);
    }
}

$pdf = new Pdf_Poa04('P', 'mm', 'Letter');
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_POA);
$pdf->AliasNbPages();
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->SetAutoPageBreak(true, 50);

$sqlProgramas = '
    SELECT
        p.prgs_id, p.prg_nombre, p.gpo_id, p.ur_id,
        eje_nombre, tem_nombre, obj_nombre, fin_nombre,
        cs.spe_nombre, co.obp_nombre, pe.ple_nombre, ope.ope_nombre, ods_nombre,
        ur_nombre, ur_nombreTitular, ur_nombreAdministrativo, ur_cargoTitular,
        ur_cargoAdministrativo, ur_nombrePlaneacion, ur_cargoPlaneacion, eur_id
    FROM (
        SELECT gpo_id, ur_id, prg_id AS prgs_id, prg_nombre
        FROM cat_Programa
    ) p
    INNER JOIN cat_Programas USING (prgs_id)
    INNER JOIN cat_EstadoPrograma USING (epr_id)
    INNER JOIN vin_ProgramasMir vpm USING (prgs_id, gpo_id, ur_id)
    INNER JOIN cat_UnidadResponsable USING (gpo_id, ur_id)
    INNER JOIN aux_UnidadResponsable USING (gpo_id, ur_id)
    INNER JOIN vin_Programa vp USING (prgs_id)
    INNER JOIN enc_PedObjetivo USING (epo_id)
    INNER JOIN cat_Eje USING (eje_id)
    INNER JOIN cat_Tema USING (eje_id, tem_id)
    INNER JOIN cat_Objetivo USING (eje_id, tem_id, obj_id)
    INNER JOIN cat_Subprogramas USING (prgs_id)
    INNER JOIN enc_PedODS USING (epods_id)
    INNER JOIN cat_ODS USING (ods_id)
    INNER JOIN cat_Finalidad USING (fin_id)
    INNER JOIN cat_SectorPes cs ON vp.spe_id = cs.spe_id
    INNER JOIN cat_ObjetivoPes co ON vp.obp_id = co.obp_id AND vp.spe_id = co.spe_id
    INNER JOIN enc_ProgramaPresupuestario USING (epp_id)
    INNER JOIN cat_PlanEspecial pe USING (ple_id)
    INNER JOIN cat_ObjetivoPe ope USING (ple_id, ope_id)
    WHERE gpo_id = ? AND ur_id = ? AND epr_id = 4
    GROUP BY gpo_id, ur_id, prgs_id
    ORDER BY prgs_id ASC;
';

$paramsProgramas = [$pdf->adm_gpo, $pdf->adm_ur];
$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sqlProgramas);
$conexion->consultar($paramsProgramas);

$pdf->AddPage();

while ($programa = $conexion->extraer()) {
    $pdf->setDatos($programa);
    $pdf->imprimirPrograma($programa);

    $sqlSubprogramas = '
        SELECT
            sp.sprs_id, sp.spr_nombre, sp.prgs_id,
            tpg_nombre, stp_nombre, ctp_nombre, fun_nombre, sfu_nombre,
            IFNULL(py.py_id, 0) AS py_id, py.py_nombre
        FROM (
            SELECT gpo_id, ur_id, prg_id AS prgs_id, spr_id AS sprs_id, pdf_id, spr_nombre
            FROM cat_Subprograma
        ) sp
        INNER JOIN cat_Subprogramas USING (prgs_id, sprs_id, pdf_id)
        INNER JOIN enc_ProgramaPresupuestario USING (epp_id)
        INNER JOIN cat_Tipologia USING (tpg_id)
        INNER JOIN cat_SubTipologia USING (tpg_id, stp_id)
        INNER JOIN cat_ClasificacionTipologia USING (tpg_id, stp_id, ctp_id)
        INNER JOIN enc_Funcional ef USING (efn_id)
        INNER JOIN cat_Funcion cfun ON cfun.fin_id = ef.fin_id AND cfun.fun_id = ef.fun_id
        INNER JOIN cat_Subfuncion sfun ON sfun.fin_id = ef.fin_id AND sfun.fun_id = ef.fun_id AND sfun.sfu_id = ef.sfu_id
        LEFT JOIN cat_Proyecto py ON py.gpo_id = sp.gpo_id AND py.ur_id = sp.ur_id
            AND py.prg_id = sp.prgs_id AND py.spr_id = sp.sprs_id AND py.py_id = 0
        WHERE sp.gpo_id = ? AND sp.ur_id = ? AND sp.prgs_id = ? AND sprs_id != 0 AND esr_id = 4
        GROUP BY sp.gpo_id, sp.ur_id, sp.prgs_id, sp.sprs_id
        ORDER BY sp.sprs_id ASC;
    ';

    $conexionSpr = new AccesoDatos();
    $conexionSpr->conectar();
    $conexionSpr->setSql($sqlSubprogramas);
    $conexionSpr->consultar([$pdf->adm_gpo, $pdf->adm_ur, $programa['prgs_id']]);

    $indiceSubprograma = 0;
    while ($subprograma = $conexionSpr->extraer()) {
        if ($pdf->nvj > 1) {
            $sqlActividades = '
                SELECT oa_id, oa_nombre
                FROM cat_ObraActividad
                INNER JOIN cat_EstadoObraActividad USING (eoa_id)
                WHERE gpo_id = ? AND ur_id = ? AND prg_id = ? AND spr_id = ? AND py_id = ? AND oa_id !=0
                    AND eoa_id = 4 AND toa_id=1
                ORDER BY oa_id ASC;
            ';
            $paramsActividades = [
                $pdf->adm_gpo,
                $pdf->adm_ur,
                $programa['prgs_id'],
                $subprograma['sprs_id'],
                $subprograma['py_id'] ?? 0,
            ];
        } else {
            $sqlActividades = '
                SELECT oa_id, oa_nombre
                FROM cat_ObraActividad
                INNER JOIN cat_EstadoObraActividad USING (eoa_id)
                WHERE gpo_id = ? AND ur_id = ? AND prg_id = ? AND spr_id = ? AND py_id = ? 
                    AND ue_id = ? AND oa_id !=0 AND eoa_id = 4 AND toa_id=1
                ORDER BY oa_id ASC;
            ';
            $paramsActividades = [
                $pdf->adm_gpo,
                $pdf->adm_ur,
                $programa['prgs_id'],
                $subprograma['sprs_id'],
                $subprograma['py_id'] ?? 0,
                $pdf->adm_ue,
            ];
        }

        $conexionOa = new AccesoDatos();
        $conexionOa->conectar();
        $conexionOa->setSql($sqlActividades);
        $conexionOa->consultar($paramsActividades);

        $actividades = [];
        while ($actividad = $conexionOa->extraer()) {
            $actividades[] = $actividad;
        }
        $conexionOa->desconectar();

        $subprograma['actividades'] = $actividades;
        $margenSuperior = ($indiceSubprograma === 0) ? 8 : 6;
        $pdf->imprimirSubprograma($subprograma, $margenSuperior);
        $indiceSubprograma++;
    }

    $conexionSpr->desconectar();
}

$conexion->desconectar();
$pdf->Output();
