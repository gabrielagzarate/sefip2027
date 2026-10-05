<?php

/**
 * Description of Pdf_Poa5
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
 * Objective: Generar Reporte POA-05 - VINCULACIÓN MATRIZ DE INDICADORES PARA RESULTADOS - ESTRUCTURA PROGRAMÁTICA
 * Regla de Negocio:
 * Esta pantalla no pregunta por permisos de escritura ó lectura
 */

session_start();
ini_set('memory_limit', '20M');
set_time_limit(0);
require_once __DIR__ . '/../../../lib/reportetabla.1.8.php';
require_once __DIR__ . '/../../../../lib/AccesoDatos.php';
require_once __DIR__ . '/../../../../ent/UnidadResponsable.php';
require_once __DIR__ . '/../../../../lib/TruncateCadena.php';
require_once __DIR__ . '/../../../../lib/constantes.php';
require_once __DIR__ . '/../../../../lib/helpers.php';

class Pdf_Poa5 extends PDF
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
    public $nombreUR;
    public $longitud;
    public $filtroUe = '';
    public $paramsUe = [];
    public const TOA_PRESUPUESTARIA = 1;

    public array $anchos = [12, 52, 12, 88, 24, 62, 30];

    private array $alineacionFilas = ['C', 'L', 'C', 'L', 'R', 'L', 'R'];

    /** Tonalidades azul-gris POA-23: más intenso en propósito, más claro en niveles inferiores */
    private array $colorEncabezadoNivel = [
        'proposito' => [199, 209, 219],
        'componente' => [215, 222, 230],
        'actividad' => [232, 236, 241],
    ];

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

        if ($this->nvj <= 1) {
            $this->filtroUe = ' AND ep.ue_id = ? ';
            $this->paramsUe = [$this->adm_ue];
        }
    }

    public function Footer()
    {
        $row = $this->getDatos();
        $this->SetY(-23);

        if ($row == '') {
            $conexion3 = new AccesoDatos();
            $conexion3->conectar();
            $conexion3->setSql('
                SELECT ur_nombreAdministrativo, ur_nombreTitular, ur_cargoAdministrativo, ur_cargoTitular,
                    ur_nombrePlaneacion, ur_cargoPlaneacion
                FROM aux_UnidadResponsable
                WHERE gpo_id = ? AND ur_id = ?');
            $conexion3->consultar([$this->adm_gpo, $this->adm_ur]);
            $conexion3->desconectar();
            $row = $conexion3->extraer();
        }

        if ($row['ur_nombrePlaneacion'] == '' && $row['ur_cargoPlaneacion'] == '') {
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(96, 2, utf8Decode('REVISÓ'), 0, 0, 'C');
            $this->Cell(260, 2, utf8Decode('AUTORIZÓ'), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->ln(1);
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(96, 2, utf8Decode($row['ur_nombreAdministrativo']), 0, 0, 'C');
            $this->Cell(260, 2, utf8Decode($row['ur_nombreTitular']), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(96, 2, utf8Decode($row['ur_cargoAdministrativo']), 0, 0, 'C');
            $this->Cell(260, 2, utf8Decode($row['ur_cargoTitular']), 0, 1, 'C');
        } else {
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(70, 2, utf8Decode('ELABORÓ'), 0, 0, 'C');
            $this->Cell(110, 2, utf8Decode('REVISÓ'), 0, 0, 'C');
            $this->Cell(70, 2, utf8Decode('AUTORIZÓ'), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(70, 2, utf8Decode($row['ur_nombrePlaneacion']), 0, 0, 'C');
            $this->Cell(110, 2, utf8Decode($row['ur_nombreAdministrativo']), 0, 0, 'C');
            $this->Cell(70, 2, utf8Decode($row['ur_nombreTitular']), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $y = $this->getY();
            $x = $this->getX();
            $this->setX(10);
            $this->multicell(70, 3, utf8Decode($row['ur_cargoPlaneacion']), 0, 'C');
            $this->setY($y);
            $this->setX($x + 90);
            $x = $this->getX();
            $this->multicell(70, 3, utf8Decode($row['ur_cargoAdministrativo']), 0, 'C');
            $this->setY($y);
            $this->setX($x + 90);
            $this->multicell(70, 3, utf8Decode($row['ur_cargoTitular']), 0, 'C');
        }

        $this->SetFont('univiapro', '', 5);
        $this->SetY(-7);
        $this->setX(4);
        $this->MultiCell(57, 2, utf8Decode(' - '), 0, 'L');
        $this->Cell(0, 0, utf8Decode('Página ' . $this->PageNo() . '/{nb}'), 0, 0, 'C');
        $this->Cell(0, 0, date('d/m/y') . ' - ' . date('H:i:s') . '        ', 1, 0, 'R');
    }

    public function Header()
    {
        $year = APP_EJERCICIO;
        $anchoContenido = $this->getAnchoEncabezadoPoa(280);

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

        $logoAlto = $this->pintarLogoEncabezadoPoa($ix, $iy, $anchoContenido);

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
        $this->Cell(180, 5, 'PROGRAMA OPERATIVO ANUAL ' . $year, 0, 1, 'L');
        $this->SetXY($ix + 4, $ytmp + 4);
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $ytmp = $this->GetY();

        $this->longitud = 68;
        $celdaUr = $anchoContenido - 31;
        $this->nombreUR = TruncateCadena(
            utf8Decode(trim('UR: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre())),
            $this->longitud
        );

        $this->Cell(5, 5, substr($this->nombreUR, 0, $this->longitud), 0, 0, 'L');
        $this->Cell($celdaUr, 5, '', 0, 1, 'R');

        if (strlen($this->nombreUR) > $this->longitud) {
            $this->SetXY($ix + 4, $ytmp + 4);
            $this->Cell(5, 5, substr($this->nombreUR, $this->longitud, $this->longitud), 0, 0, 'L');
            $this->Cell($celdaUr, 5, '', 0, 1, 'R');
        }
        if (strlen($this->nombreUR) > $this->longitud * 2) {
            $this->SetXY($ix + 4, $ytmp + 8);
            $this->Cell(5, 5, substr($this->nombreUR, $this->longitud * 2, $this->longitud), 0, 0, 'L');
            $this->Cell($celdaUr, 5, '', 0, 1, 'R');
        }

        $this->pintarCodigoSubtituloPoa(
            $ix,
            $iy,
            $logoAlto,
            'POA-05',
            utf8Decode('VINCULACIÓN MATRIZ DE INDICADORES PARA RESULTADOS - ESTRUCTURA PROGRAMÁTICA'),
            $anchoContenido,
            $ytmp
        );

        $ytmp = $this->GetY();
        $this->setX(10);
        $this->setDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, 10 + $anchoContenido, $ytmp);
        $this->ln(3);
    }

    public function encabezadoSeccion(array $columnas, string $nivel = 'proposito')
    {
        $this->CheckPageBreak(12);
        $this->ln(1);
        $this->setX(10);
        [$r, $g, $b] = $this->colorEncabezadoNivel[$nivel] ?? $this->colorEncabezadoNivel['proposito'];
        $this->SetFillColor($r, $g, $b);
        $this->SetWidths($this->anchos);
        $this->SetAligns(['C', 'C', 'C', 'C', 'C', 'C', 'C']);
        $this->SetFont('UniviaPro-Bold', 'B', 5.5);
        $this->setBorders(0);
        $this->setAltoLinea(3);
        $this->SetRelleno('si');
        $this->Row(array_map(function ($c) {
            return utf8Decode($c);
        }, $columnas));
        $this->SetRelleno('no');
        $this->SetFillColor(255, 255, 255);
        $this->setAltoLinea(2.5);
        $this->setBorders(0);
    }

    public function formatearMeta($meta, $umiId, $tivId = null, $soloNumerico = false)
    {
        if ($meta === '' || $meta === null) {
            return '';
        }
        if ($soloNumerico) {
            return number_format((float) $meta, 4);
        }
        if (in_array((int) $umiId, [3, 4, 5], true)) {
            return number_format((float) $meta, 4) . '%';
        }
        if ((int) $umiId === 2 && in_array((int) $tivId, [1, 2], true)) {
            return number_format((float) $meta, 4) . '%';
        }
        if ((int) $umiId === 1) {
            return number_format((float) $meta, 2);
        }
        return number_format((float) $meta, 4);
    }

    private function posicionesColumnas(): array
    {
        $x = 10.0;
        $posiciones = [];
        foreach ($this->anchos as $ancho) {
            $posiciones[] = $x;
            $x += $ancho;
        }

        return $posiciones;
    }

    private function alturaCeldaTexto(float $ancho, string $texto): float
    {
        $texto = trim($texto);
        if ($texto === '') {
            return 0;
        }

        return ($this->GetAltoLinea() * max(1, $this->NbLines($ancho, $texto))) + $this->espaciado;
    }

    private function celdaVerticalCentrada(float $x, float $y, float $ancho, float $alto, string $texto, string $align = 'L'): void
    {
        $texto = trim($texto);
        if ($texto === '' || $alto <= 0) {
            return;
        }

        $lineas = max(1, $this->NbLines($ancho, $texto));
        $altoTexto = ($this->GetAltoLinea() * $lineas) + $this->espaciado;
        $padding = max(0, ($alto - $altoTexto) / 2);
        $alturaLinea = ($altoTexto - $this->espaciado) / $lineas;

        $this->SetXY($x, $y + $padding);
        $this->MultiCell($ancho, $alturaLinea, $texto, 0, $align);
    }

    private function filaIndicador(array $xCols, float $y, float $alto, array $valores, array $columnas): void
    {
        $aligns = $this->alineacionFilas;

        foreach ($columnas as $col) {
            $texto = trim((string) ($valores[$col] ?? ''));
            $ancho = $this->anchos[$col];
            $lineas = max(1, $texto === '' ? 1 : $this->NbLines($ancho, $texto));
            $alturaLinea = ($alto - $this->espaciado) / $lineas;
            $align = $aligns[$col] ?? 'L';

            $this->SetXY($xCols[$col], $y);
            $this->MultiCell($ancho, $alturaLinea, $texto, 0, $align);
        }
    }

    public function dibujarBloque(array $config)
    {
        $indicadores = $config['indicadores'];
        if (empty($indicadores)) {
            $indicadores = [['ind_id' => '', 'ind_nombre' => '', 'umi_id' => 0, 'meta' => '']];
        }

        $this->SetWidths($this->anchos);
        $this->SetFont('UniviaPro-Regular', '', 5);
        $this->setBorders(0);

        $filas = [];
        foreach ($indicadores as $ind) {
            $filas[] = [
                $config['id'] ?? '',
                utf8Decode($config['texto'] ?? ''),
                $ind['ind_id'],
                utf8Decode($ind['ind_nombre']),
                $this->formatearMeta(
                    $ind['meta'],
                    $ind['umi_id'],
                    $ind['tiv_id'] ?? null,
                    $config['metaNumerica'] ?? false
                ),
                utf8Decode($config['referencia'] ?? ''),
                number_format((float) $config['presupuesto'], 2),
            ];
        }

        if (count($filas) === 1) {
            $this->CheckPageBreak(12);
            $this->setX(10);
            $this->SetAligns($this->alineacionFilas);
            $this->Row($filas[0]);

            $this->ln(2);

            return;
        }

        $combinarReferencia = $config['combinarReferencia'] ?? true;
        $columnasCombinadas = [0, 1, 6];
        if ($combinarReferencia) {
            $columnasCombinadas[] = 5;
        }
        $columnasIndicador = [2, 3, 4];
        $xCols = $this->posicionesColumnas();

        $altosFilas = [];
        foreach ($filas as $idx => $fila) {
            $lineas = 1;
            $colsAltura = $columnasIndicador;
            if (!$combinarReferencia && $idx === 0) {
                $colsAltura[] = 5;
            }
            foreach ($colsAltura as $col) {
                $lineas = max($lineas, max(1, $this->NbLines($this->anchos[$col], trim((string) $fila[$col]))));
            }
            $altosFilas[] = ($this->GetAltoLinea() * $lineas) + $this->espaciado;
        }

        $alturaCombinada = 0;
        foreach ($columnasCombinadas as $col) {
            $alturaCombinada = max($alturaCombinada, $this->alturaCeldaTexto($this->anchos[$col], (string) $filas[0][$col]));
        }

        $alturaIndicadores = array_sum($altosFilas);
        $alturaTotal = max($alturaIndicadores, $alturaCombinada);
        if ($alturaTotal > $alturaIndicadores) {
            $altosFilas[count($altosFilas) - 1] += ($alturaTotal - $alturaIndicadores);
        }

        $this->CheckPageBreak($alturaTotal);
        $yInicio = $this->GetY();

        foreach ($columnasCombinadas as $col) {
            $this->celdaVerticalCentrada(
                $xCols[$col],
                $yInicio,
                $this->anchos[$col],
                $alturaTotal,
                (string) $filas[0][$col],
                $this->alineacionFilas[$col] ?? 'L'
            );
        }

        $yFila = $yInicio;
        foreach ($filas as $idx => $fila) {
            $colsFila = $columnasIndicador;
            if (!$combinarReferencia && $idx === 0) {
                $colsFila[] = 5;
            }
            if (!$combinarReferencia && $idx > 0) {
                $fila[5] = '';
            }
            $this->filaIndicador($xCols, $yFila, $altosFilas[$idx], $fila, $colsFila);
            $yFila += $altosFilas[$idx];
        }

        $this->SetY($yInicio + $alturaTotal);
        $this->ln(2);
    }

    public function consultarPresupuesto($filtroExtra = '', array $paramsExtra = [])
    {
        $sql = '
            SELECT COALESCE(SUM(po.pre_original), 0) AS total
            FROM presupuesto pre
            INNER JOIN presupuestoOriginal po USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
            INNER JOIN enc_Programatico ep USING(gpo_id, ur_id, epg_id)
            INNER JOIN cat_ObraActividad oa ON oa.gpo_id = ep.gpo_id AND oa.ur_id = ep.ur_id
                AND oa.prg_id = ep.prg_id AND oa.spr_id = ep.spr_id
                AND oa.py_id = ep.py_id AND oa.oa_id = ep.oa_id
                AND oa.toa_id = ' . self::TOA_PRESUPUESTARIA . '
            INNER JOIN cat_MetaActividad ma USING(epg_id)
            INNER JOIN vin_ProductoIntermedioIndicador pii USING(pii_id)
            INNER JOIN vin_ProductoFinalProductoIntermedio vpfi USING(pdf_id, pdi_id)
            WHERE ep.gpo_id = ? AND ep.ur_id = ?
            ' . $this->filtroUe . '
            AND vpfi.sts_id = 1
            ' . $filtroExtra;

        $params = array_merge([$this->adm_gpo, $this->adm_ur], $this->paramsUe, $paramsExtra);

        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar($params);
        $con->desconectar();
        $row = $con->extraer();

        return $row ? (float) $row['total'] : 0;
    }
}

$pdf = new Pdf_Poa5('L', 'mm', 'Letter');
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_POA);
$pdf->setOrientation('L');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->setAltoLinea(2.5);
$pdf->AddPage();

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql('
    SELECT DISTINCT vpm.prgs_id, pg.prgs_nombre, IFNULL(vpm.vpm_resumenNarrativo, "") AS proposito,
        ur.ur_nombre, aux.ur_nombreTitular, aux.ur_nombreAdministrativo, aux.ur_cargoTitular,
        aux.ur_cargoAdministrativo, aux.ur_nombrePlaneacion, aux.ur_cargoPlaneacion, ur.eur_id
    FROM vin_UrProgramaMir vupm
    INNER JOIN vin_ProgramasMir vpm ON vpm.vpm_id = vupm.vpm_id
    INNER JOIN cat_Programas pg ON pg.prgs_id = vpm.prgs_id
    INNER JOIN cat_UnidadResponsable ur ON ur.gpo_id = vupm.gpo_id AND ur.ur_id = vupm.ur_id
    INNER JOIN aux_UnidadResponsable aux ON aux.gpo_id = vupm.gpo_id AND aux.ur_id = vupm.ur_id
    WHERE vupm.gpo_id = ? AND vupm.ur_id = ?
    ORDER BY vpm.prgs_id
');
$conexion->consultar([$pdf->adm_gpo, $pdf->adm_ur]);

$programas = [];
while ($row = $conexion->extraer()) {
    $programas[] = $row;
}
$conexion->desconectar();

if (!empty($programas)) {
    $pdf->setDatos($programas[0]);
}

foreach ($programas as $programa) {
    $prgsId = $programa['prgs_id'];

    $conProp = new AccesoDatos();
    $conProp->setSql('
        SELECT pi.ind_id, i.ind_nombreMIR AS ind_nombre, i.umi_id, i.tiv_id,
            CASE
                WHEN i.umi_id IN (2, 4, 5) THEN IFNULL(aux.mpr_numeradorT1, IFNULL(mpt.mprt_numerador, ""))
                ELSE IFNULL(aux.mpr_modificado, IFNULL(mp.mpr_modificado, ""))
            END AS meta
        FROM vin_ProgramasMir vpm
        INNER JOIN vin_ProgramaMirIndicador pi ON pi.vpm_id = vpm.vpm_id
        INNER JOIN cat_Indicador i ON i.nim_id = pi.nim_id AND i.ind_id = pi.ind_id
        LEFT JOIN cat_MetaProposito mp ON mp.pmi_id = pi.pmi_id
        LEFT JOIN cat_MetaPropositoTrimestre mpt ON mpt.pmi_id = pi.pmi_id
            AND mpt.mpr_id = mp.mpr_id AND mpt.tri_id = 4
        LEFT JOIN aux_HistoricoModificacionMetaProposito aux ON aux.pmi_id = pi.pmi_id
            AND aux.mpr_id = mp.mpr_id AND aux.tri_id = 1
        WHERE vpm.prgs_id = ? AND pi.nim_id = 2 AND pi.sts_id = 1
        ORDER BY pi.pmi_orden, pi.ind_id
    ');
    $conProp->conectar();
    $conProp->consultar([$prgsId]);
    $indicadoresProposito = [];
    while ($rowInd = $conProp->extraer()) {
        $indicadoresProposito[] = $rowInd;
    }
    $conProp->desconectar();

    $presupuestoPrograma = $pdf->consultarPresupuesto(
        ' AND EXISTS (
            SELECT 1 FROM enc_UrSubprogramas es
            WHERE es.prgs_id = ? AND es.gpo_id = ep.gpo_id AND es.ur_id = ep.ur_id
        )',
        [$prgsId]
    );

    $pdf->encabezadoSeccion(['ID', 'PROPÓSITO', 'ID', 'IND. NIVEL PROPÓSITO', 'META', 'PROGRAMA:', 'PRESUPUESTO TOTAL'], 'proposito');
    $pdf->dibujarBloque([
        'id' => '',
        'texto' => strtoupper($programa['proposito']),
        'indicadores' => $indicadoresProposito,
        'referencia' => $prgsId . ' ' . $programa['prgs_nombre'],
        'presupuesto' => $presupuestoPrograma,
    ]);

    $conComp = new AccesoDatos();
    $conComp->setSql('
        SELECT pf.pdf_id, pf.pdf_mir, sp.sprs_id, sp.sprs_nombre,
            MIN(epi.epfi_orden) AS epfi_orden
        FROM enc_UrSubprogramas es
        INNER JOIN cat_Subprogramas sp ON sp.prgs_id = es.prgs_id AND sp.sprs_id = es.sprs_id
        INNER JOIN cat_ProductoFinal pf ON pf.pdf_id = sp.pdf_id
        INNER JOIN enc_ProductoFinalIndicador epi ON epi.pdf_id = pf.pdf_id AND epi.trim_0 = 1
        WHERE es.prgs_id = ? AND es.gpo_id = ? AND es.ur_id = ?
            AND pf.pdf_id != 0
            AND EXISTS (
                SELECT 1
                FROM vin_ProductoFinalProductoIntermedio vpfi
                INNER JOIN vin_ProductoIntermedioIndicador pii ON pii.pdf_id = vpfi.pdf_id AND pii.pdi_id = vpfi.pdi_id
                INNER JOIN cat_MetaActividad ma ON ma.pii_id = pii.pii_id
                INNER JOIN enc_Programatico ep ON ep.epg_id = ma.epg_id
                INNER JOIN cat_ObraActividad oa ON oa.gpo_id = ep.gpo_id AND oa.ur_id = ep.ur_id
                    AND oa.prg_id = ep.prg_id AND oa.spr_id = ep.spr_id
                    AND oa.py_id = ep.py_id AND oa.oa_id = ep.oa_id
                    AND oa.toa_id = ' . Pdf_Poa5::TOA_PRESUPUESTARIA . '
                WHERE vpfi.pdf_id = pf.pdf_id
                    AND vpfi.sts_id = 1
                    AND ep.gpo_id = es.gpo_id
                    AND ep.ur_id = es.ur_id
            )
        GROUP BY pf.pdf_id, pf.pdf_mir, sp.sprs_id, sp.sprs_nombre
        ORDER BY epfi_orden, pf.pdf_id
    ');
    $conComp->conectar();
    $conComp->consultar([$prgsId, $pdf->adm_gpo, $pdf->adm_ur]);

    while ($componente = $conComp->extraer()) {
        $conIndComp = new AccesoDatos();
        $conIndComp->setSql('
            SELECT i.ind_id, i.ind_nombreMIR AS ind_nombre, i.umi_id, i.tiv_id,
                CASE
                    WHEN i.umi_id IN (2, 3, 4, 5) THEN
                        IF(i.tiv_id = 1,
                            IFNULL(aux.mcot_numeradorT2, 0) + IFNULL(aux.mcot_numeradorT4, 0),
                            IFNULL(aux.mcot_numeradorT4, ""))
                    ELSE
                        IF(i.tiv_id = 1,
                            IFNULL(aux.mcot_modificadoT2, 0) + IFNULL(aux.mcot_modificadoT4, 0),
                            IFNULL(aux.mcot_modificadoT4, IFNULL(aux.mco_original, "")))
                END AS meta
            FROM enc_ProductoFinalIndicador epi
            INNER JOIN cat_Indicador i USING(nim_id, ind_id)
            LEFT JOIN aux_HistoricoModificacionMetaComponente aux
                ON aux.prgs_id = ? AND aux.sprs_id = ?
                AND aux.epfi_id = epi.epfi_id AND aux.gpo_id = ? AND aux.ur_id = ?
                AND aux.tri_id = 0
            WHERE epi.pdf_id = ? AND epi.trim_0 = 1
            ORDER BY epi.epfi_orden, i.ind_id
        ');
        $conIndComp->conectar();
        $conIndComp->consultar([
            $prgsId, $componente['sprs_id'], $pdf->adm_gpo, $pdf->adm_ur, $componente['pdf_id'],
        ]);
        $indicadoresComponente = [];
        while ($rowInd = $conIndComp->extraer()) {
            $indicadoresComponente[] = $rowInd;
        }
        $conIndComp->desconectar();

        $presupuestoComponente = $pdf->consultarPresupuesto(
            ' AND vpfi.pdf_id = ?',
            [$componente['pdf_id']]
        );

        $pdf->encabezadoSeccion(['ID', 'COMPONENTE', 'ID', 'IND. NIVEL COMPONENTE', 'META', 'SUBPROGRAMA', 'PRESUPUESTO'], 'componente');
        $pdf->dibujarBloque([
            'id' => $componente['pdf_id'],
            'texto' => strtoupper($componente['pdf_mir']),
            'indicadores' => $indicadoresComponente,
            'referencia' => str_pad($componente['sprs_id'], 3, '0', STR_PAD_LEFT) . ' ' . $componente['sprs_nombre'],
            'presupuesto' => $presupuestoComponente,
            'metaNumerica' => true,
        ]);

        $conAct = new AccesoDatos();
        $conAct->setSql('
            SELECT pi.pdi_id, pi.pdi_mir, ep.oa_id, oa.oa_nombre, pii.pii_id, pii.pii_orden
            FROM cat_MetaActividad ma
            INNER JOIN enc_Programatico ep ON ep.epg_id = ma.epg_id
            INNER JOIN cat_ObraActividad oa ON oa.gpo_id = ep.gpo_id AND oa.ur_id = ep.ur_id
                AND oa.prg_id = ep.prg_id AND oa.spr_id = ep.spr_id
                AND oa.py_id = ep.py_id AND oa.oa_id = ep.oa_id
                AND oa.toa_id = ' . Pdf_Poa5::TOA_PRESUPUESTARIA . '
            INNER JOIN vin_ProductoIntermedioIndicador pii ON pii.pii_id = ma.pii_id AND pii.trim_0 = 1
            INNER JOIN vin_ProductoFinalProductoIntermedio vpfi ON vpfi.pdi_id = pii.pdi_id
                AND vpfi.pdf_id = ? AND vpfi.sts_id = 1
            INNER JOIN cat_ProductoIntermedio pi ON pi.pdi_id = pii.pdi_id
            WHERE pii.pdf_id = ?
                AND ep.gpo_id = ? AND ep.ur_id = ?
                AND ep.prg_id = ? AND ep.spr_id = ?
                ' . $pdf->filtroUe . '
            ORDER BY ep.oa_id, pii.pii_orden
        ');
        $conAct->conectar();
        $paramsAct = array_merge(
            [$componente['pdf_id'], $componente['pdf_id'], $pdf->adm_gpo, $pdf->adm_ur, $prgsId, $componente['sprs_id']],
            $pdf->paramsUe
        );
        $conAct->consultar($paramsAct);

        while ($actividad = $conAct->extraer()) {
            $conIndAct = new AccesoDatos();
            $conIndAct->setSql('
                SELECT i.ind_id, i.ind_nombreMIR AS ind_nombre, i.umi_id, i.tiv_id,
                    CASE
                        WHEN i.umi_id IN (2, 3, 4, 5) THEN
                            IF(i.tiv_id = 1,
                                IF(IFNULL(m.macm_modificadoT1, 0) != 0, IFNULL(m.macm_modificadoT1, 0), IFNULL(m.macm_numeradorT1, 0))
                                + IF(IFNULL(m.macm_modificadoT2, 0) != 0, IFNULL(m.macm_modificadoT2, 0), IFNULL(m.macm_numeradorT2, 0))
                                + IF(IFNULL(m.macm_modificadoT3, 0) != 0, IFNULL(m.macm_modificadoT3, 0), IFNULL(m.macm_numeradorT3, 0))
                                + IF(IFNULL(m.macm_modificadoT4, 0) != 0, IFNULL(m.macm_modificadoT4, 0), IFNULL(m.macm_numeradorT4, 0)),
                                IF(IFNULL(m.macm_modificadoT4, 0) != 0, IFNULL(m.macm_modificadoT4, 0), IFNULL(m.macm_numeradorT4, "")))
                        ELSE
                            IF(i.tiv_id = 1,
                                IFNULL(m.macm_modificadoT1, 0) + IFNULL(m.macm_modificadoT2, 0)
                                    + IFNULL(m.macm_modificadoT3, 0) + IFNULL(m.macm_modificadoT4, 0),
                                IFNULL(m.macm_modificadoT4, IFNULL(m.macm_original, "")))
                    END AS meta
                FROM vin_ProductoIntermedioIndicador pii
                INNER JOIN cat_Indicador i USING(nim_id, ind_id)
                LEFT JOIN (
                    SELECT mac.pii_id, mac.gpo_id, mac.ur_id, aux.macm_original,
                        aux.macm_modificadoT1, aux.macm_modificadoT2, aux.macm_modificadoT3, aux.macm_modificadoT4,
                        aux.macm_numeradorT1, aux.macm_numeradorT2, aux.macm_numeradorT3, aux.macm_numeradorT4
                    FROM cat_MetaActividadMir mac
                    LEFT JOIN aux_HistoricoMetaActividadMir aux
                        ON aux.pii_id = mac.pii_id AND aux.macm_id = mac.macm_id AND aux.tri_id = 0
                ) m ON m.pii_id = pii.pii_id AND m.gpo_id = ? AND m.ur_id = ?
                WHERE pii.pii_id = ? AND pii.trim_0 = 1
                ORDER BY i.ind_id
            ');
            $conIndAct->conectar();
            $conIndAct->consultar([
                $pdf->adm_gpo, $pdf->adm_ur, $actividad['pii_id'],
            ]);
            $indicadoresActividad = [];
            while ($rowInd = $conIndAct->extraer()) {
                $indicadoresActividad[] = $rowInd;
            }
            $conIndAct->desconectar();

            $presupuestoActividad = $pdf->consultarPresupuesto(
                ' AND vpfi.pdf_id = ? AND ep.oa_id = ?',
                [$componente['pdf_id'], $actividad['oa_id']]
            );

            $pdf->encabezadoSeccion(['ID', 'ACTIVIDAD MIR', 'ID', 'IND. NIVEL ACTIVIDAD', 'META', 'ACTIVIDAD PRESUPUESTARIA', 'PRESUPUESTO'], 'actividad');
            $pdf->dibujarBloque([
                'id' => $actividad['pdi_id'],
                'texto' => strtoupper($actividad['pdi_mir']),
                'indicadores' => $indicadoresActividad,
                'referencia' => str_pad($actividad['oa_id'], 3, '0', STR_PAD_LEFT) . ' ' . $actividad['oa_nombre'],
                'presupuesto' => $presupuestoActividad,
                'metaNumerica' => true,
                'combinarReferencia' => false,
            ]);
        }
        $conAct->desconectar();
    }
    $conComp->desconectar();
}

$pdf->Output();
