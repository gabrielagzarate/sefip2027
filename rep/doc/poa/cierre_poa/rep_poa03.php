<?php

/**
 * Description of Pdf_Poa03
 *
 * @author Adriana Lopez
 * Created: 10-Jul-2026
 * Comment:
 * Se agrega condicion $this->opc
 *         1 = Aplica, mostrar datos
 *         0 = No aplica, no mostrar datos
 * Estado Costeo:
 * - 1 = Abierto
 * - 2 = Cerrado
 * Objective: Generar Reporte POA-03 - MATRIZ DE INDICADORES PARA RESULTADOS
 *            (estructura y construcción de datos del módulo MIR Inicial)
 * Regla de Negocio:
 * Esta pantalla no pregunta por permisos de escritura ó lectura
 * Los medios de verificación deben corresponder al indicador (pmi_id)
 */

session_start();
ini_set('memory_limit', '128M');
set_time_limit(0);
require_once __DIR__ . '/../../../lib/reportetabla.1.8.php';
require_once __DIR__ . '/../../../../lib/AccesoDatos.php';
require_once __DIR__ . '/../../../../ent/UnidadResponsable.php';
require_once __DIR__ . '/../../../../lib/TruncateCadena.php';
require_once __DIR__ . '/../../../../lib/constantes.php';
require_once __DIR__ . '/../../../../lib/helpers.php';

class Pdf_Poa03 extends PDF
{
    public const ANCHOS_MATRIZ = [20, 35, 65, 80, 80];
    public const ANCHO_TOTAL = 280;
    /** Altura mínima antes de iniciar una fila de matriz (evita trozos minúsculos al final de página). */
    public const ALTO_MIN_FILA = 18;
    /** Estilo tipográfico alineado a reportes POA (p. ej. POA-23). */
    public const FONT_TITULO = 7;
    public const FONT_ENCABEZADO = 6;
    public const FONT_DATO = 5;
    public const COLOR_ENCABEZADO = [199, 209, 219];
    public const COLOR_BORDE = [200, 200, 200]; // mismo tono que POA-18


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
    public $activarEncabezado = true;
    public $imprimirEncabezadoTabla = false;
    public $mostrarEncabezadoTabla = false;
    public $encabezadoImpreso = false;
    public $trimestre = 0;

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
        $this->trimestre = isset($_POST['eri_trimestre']) ? (int) $_POST['eri_trimestre'] : 0;
    }

    public function Footer()
    {
        $row = $this->getDatos();
        $this->SetBorders(0);
        $this->setBorders(0);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetY(-25);

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
            $this->Cell(96, 4, utf8Decode('REVISÓ'), 0, 0, 'C');
            $this->Cell(260, 4, utf8Decode('AUTORIZÓ'), 0, 1, 'C');
            $this->Cell(130, 3, '', 0, 1, 'C');
            $this->ln(1);
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(96, 4, utf8Decode($row['ur_nombreAdministrativo']), 0, 0, 'C');
            $this->Cell(260, 4, utf8Decode($row['ur_nombreTitular']), 0, 1, 'C');
            $this->Cell(130, 3, '', 0, 1, 'C');
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(96, 4, utf8Decode($row['ur_cargoAdministrativo']), 0, 0, 'C');
            $this->Cell(260, 4, utf8Decode($row['ur_cargoTitular']), 0, 1, 'C');
        } else {
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(70, 4, utf8Decode('ELABORÓ'), 0, 0, 'C');
            $this->Cell(110, 4, utf8Decode('REVISÓ'), 0, 0, 'C');
            $this->Cell(70, 4, utf8Decode('AUTORIZÓ'), 0, 1, 'C');
            $this->Cell(130, 3, '', 0, 1, 'C');
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(70, 4, utf8Decode($row['ur_nombrePlaneacion']), 0, 0, 'C');
            $this->Cell(110, 4, utf8Decode($row['ur_nombreAdministrativo']), 0, 0, 'C');
            $this->Cell(70, 4, utf8Decode($row['ur_nombreTitular']), 0, 1, 'C');
            $this->Cell(130, 3, '', 0, 1, 'C');
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
        $this->Cell(0, 0, date('d/m/y') . ' - ' . date('H:i:s') . '        ', 0, 0, 'R');
    }

    public function Header()
    {
        if (!$this->activarEncabezado) {
            return;
        }

        $year = APP_EJERCICIO;
        $anchoContenido = $this->getAnchoEncabezadoPoa(self::ANCHO_TOTAL);

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
            'POA-03',
            utf8Decode('MATRIZ DE INDICADORES PARA RESULTADOS'),
            $anchoContenido,
            $ytmp
        );

        $ytmp = $this->GetY();
        $this->setX(10);
        $this->setDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, 10 + $anchoContenido, $ytmp);
        $this->aplicarEstiloBordeMatriz();
        $this->ln(3);

        if ($this->PageNo() > 1 && $this->imprimirEncabezadoTabla) {
            if (!$this->encabezadoImpreso) {
                return;
            }
            $this->Ln(2);
            $this->imprimirEncabezadoTabla();
        }
    }

    public function imprimirEncabezadoTabla()
    {
        $this->setX(10);
        $this->aplicarEstiloBordeMatriz();
        [$r, $g, $b] = self::COLOR_ENCABEZADO;
        $this->SetFillColor($r, $g, $b);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('UniviaPro-Bold', 'B', self::FONT_ENCABEZADO);
        $this->setAltoLinea(2.5);

        $x0 = 10.0;
        $anchos = self::ANCHOS_MATRIZ;
        $anchoTotal = array_sum($anchos);
        $altoLinea = 2.5;

        $txtInd1 = utf8Decode("A)ID INDICADOR 1.NOMBRE\n4.DIMENSIÓN\n7.META ANUAL");
        $txtInd2 = utf8Decode("2. FÓRMULA DE CÁLCULO\n5.FRECUENCIA");
        $txtInd3 = "3. TIPO\n6.SENTIDO";

        // Altura: fila título INDICADOR + fila etiquetas (hasta 3 líneas).
        $lineasSub = max(
            $this->NbLines(20, $txtInd1),
            $this->NbLines(23, $txtInd2),
            $this->NbLines(22, $txtInd3),
            3
        );
        $hTitulo = $altoLinea + 1.2;
        $hSub = ($altoLinea * $lineasSub) + 1.2;
        $hTotal = $hTitulo + $hSub;

        $this->CheckPageBreak($hTotal + 2);
        $this->setX($x0);
        $y0 = $this->GetY();

        // Fondo continuo (evita costuras blancas entre celdas con relleno).
        $this->SetDrawColor($r, $g, $b);
        $this->Rect($x0, $y0, $anchoTotal, $hTotal, 'F');

        // Marco y divisores principales en negro (como la matriz MIR).
        $this->aplicarEstiloBordeMatriz();
        $this->Rect($x0, $y0, $anchoTotal, $hTotal);
        $x = $x0;
        for ($i = 0; $i < count($anchos) - 1; $i++) {
            $x += $anchos[$i];
            $this->Line($x, $y0, $x, $y0 + $hTotal);
        }
        // Línea horizontal bajo el título INDICADOR (solo sobre esa columna).
        $xInd = $x0 + $anchos[0] + $anchos[1];
        $this->Line($xInd, $y0 + $hTitulo, $xInd + $anchos[2], $y0 + $hTitulo);

        // Textos sin bordes de celda.
        $this->SetFont('UniviaPro-Bold', 'B', self::FONT_ENCABEZADO);

        // NIVEL / RESUMEN centrados en toda la altura del encabezado.
        $this->SetXY($x0, $y0 + ($hTotal - $altoLinea) / 2);
        $this->Cell($anchos[0], $altoLinea, 'NIVEL', 0, 0, 'C');
        $this->SetXY($x0 + $anchos[0], $y0 + ($hTotal - $altoLinea) / 2);
        $this->Cell($anchos[1], $altoLinea, 'RESUMEN NARRATIVO', 0, 0, 'C');

        // INDICADOR (título + subetiquetas).
        $this->SetXY($xInd, $y0 + 0.4);
        $this->Cell($anchos[2], $altoLinea, 'INDICADOR', 0, 0, 'C');

        $xSub = $xInd;
        $this->SetXY($xSub, $y0 + $hTitulo + 0.3);
        $this->MultiCell(20, $altoLinea, $txtInd1, 0, 'L');
        $this->SetXY($xSub + 20, $y0 + $hTitulo + 0.3);
        $this->MultiCell(23, $altoLinea, $txtInd2, 0, 'L');
        $this->SetXY($xSub + 43, $y0 + $hTitulo + 0.3);
        $this->MultiCell(22, $altoLinea, $txtInd3, 0, 'L');

        // MEDIOS / SUPUESTOS.
        $xMed = $xInd + $anchos[2];
        $this->SetXY($xMed, $y0 + ($hTotal - $altoLinea) / 2);
        $this->Cell($anchos[3], $altoLinea, utf8Decode('MEDIOS DE VERIFICACIÓN'), 0, 0, 'C');
        $this->SetXY($xMed + $anchos[3], $y0 + ($hTotal - $altoLinea) / 2);
        $this->Cell($anchos[4], $altoLinea, 'SUPUESTOS', 0, 0, 'C');

        $this->SetY($y0 + $hTotal);
        $this->SetArrayBorders(null);
        $this->SetRelleno('no');
        $this->SetFillColor(255, 255, 255);
        $this->estiloMatriz();
    }

    public function imprimirEncabezadoTablaPrimeraVez()
    {
        if ($this->GetY() + 20 > $this->GetPageHeight() - $this->bMargin) {
            $this->AddPage();
        }
        $this->imprimirEncabezadoTabla();
        $this->encabezadoImpreso = true;
        $this->mostrarEncabezadoTabla = true;
    }

    /* ------------------------------------------------------------------
     * Helpers de consulta / formato / dibujo de matriz
     * ------------------------------------------------------------------ */

    private function aplicarEstiloBordeMatriz(): void
    {
        // Bordes divisores de la matriz MIR original (cuadrícula completa).
        [$r, $g, $b] = self::COLOR_BORDE;
        $this->SetDrawColor($r, $g, $b);
        $this->SetLineWidth(0.2);
    }

    private function consultar(string $sql, array $params = []): array
    {
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar($params);
        $rows = [];
        while ($row = $con->extraer()) {
            $rows[] = $row;
        }
        $con->desconectar();

        return $rows;
    }

    private function consultarUno(string $sql, array $params = []): ?array
    {
        $rows = $this->consultar($sql, $params);

        return $rows[0] ?? null;
    }

    private function tituloSeccion(string $texto, float $fontSize = null): void
    {
        $fontSize = $fontSize ?? self::FONT_TITULO;
        $this->asegurarEspacio(12);
        $this->setX(10);
        $this->aplicarEstiloBordeMatriz();
        $this->SetRelleno('si');
        [$r, $g, $b] = self::COLOR_ENCABEZADO;
        $this->SetFillColor($r, $g, $b);
        $this->setBorders(1);
        $this->setAltoLinea(2.5);
        $this->SetWidths([self::ANCHO_TOTAL]);
        $this->SetAligns(['C']);
        $this->SetFont('UniviaPro-Bold', 'B', $fontSize);
        $this->Row([utf8Decode($texto)]);
        $this->SetRelleno('no');
        $this->setBorders(0);
        $this->SetFillColor(255, 255, 255);
    }

    private function fuentesEtiquetaValor(int $pares = 3): array
    {
        $fuentes = [];
        for ($i = 0; $i < $pares; $i++) {
            $bold = new stdClass();
            $bold->font = 'UniviaPro-Bold';
            $bold->style = 'B';
            $bold->size = (string) self::FONT_ENCABEZADO;
            $fuentes[] = $bold;

            $regular = new stdClass();
            $regular->font = 'UniviaPro-Regular';
            $regular->style = '';
            $regular->size = (string) self::FONT_DATO;
            $fuentes[] = $regular;
        }

        return $fuentes;
    }

    /**
     * Arma el bloque 1..7 del indicador. Solo incluye campos con valor.
     *
     * @param array $campos [1=>nombre, 2=>formula, 3=>tipo, 4=>dim, 5=>freq, 6=>sentido, 7=>meta]
     */
    private function formatoIndicador(array $campos, bool $formulaMayusculas = true): string
    {
        $lineas = [];
        foreach ($campos as $num => $valor) {
            $valor = trim((string) $valor);
            if ($valor === '' || $valor === '0') {
                continue;
            }
            if ($formulaMayusculas && (int) $num === 2) {
                $valor = strtoupper($valor);
            }
            $lineas[] = $num . ' - ' . $valor;
        }

        return implode("\n", $lineas);
    }

    private function estiloMatriz(): void
    {
        $this->aplicarEstiloBordeMatriz();
        $this->SetWidths(self::ANCHOS_MATRIZ);
        $this->SetAligns(['C', 'L', 'L', 'L', 'L']);
        $this->SetFont('UniviaPro-Regular', '', self::FONT_DATO);
        $this->setAltoLinea(2.5);
        $this->setBorders(1);
        $this->SetRelleno('no');
        $this->setTopAlign(true);
    }

    private function asegurarEspacio(float $altoMinimo): void
    {
        if ($this->GetY() + $altoMinimo > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
        $this->setX(10);
    }

    /**
     * Dibuja una fila de la matriz. Row2 parte filas altas en chunks entre páginas
     * y el Header reimprime el encabezado de columnas en cada hoja.
     */
    private function filaMatriz(
        string $nivel,
        string $resumen,
        string $indicador,
        string $medios,
        string $supuestos
    ): void {
        $this->estiloMatriz();
        $this->asegurarEspacio(self::ALTO_MIN_FILA);
        $this->Row2([
            $nivel,
            $resumen,
            $indicador,
            $medios,
            $supuestos,
        ], '', 2.5);
    }

    private function unirTextos(array $rows, string $campo, string $sep = "\n\n"): string
    {
        $partes = [];
        foreach ($rows as $row) {
            $valor = trim((string) ($row[$campo] ?? ''));
            if ($valor !== '') {
                $partes[] = $valor;
            }
        }

        return implode($sep, $partes);
    }

    private function unirTextosConUr(array $rows, string $campo): string
    {
        $partes = [];
        foreach ($rows as $row) {
            $valor = trim((string) ($row[$campo] ?? ''));
            if ($valor === '') {
                continue;
            }
            $partes[] = 'UR ' . $row['gpo_id'] . $row['ur_id'] . ': ' . $valor;
        }

        return implode("\n", $partes);
    }

    private function mediosPrograma(int $vpmId, int $nimId, $pmiId): string
    {
        if ($pmiId === null || $pmiId === '' || $pmiId === false) {
            return '';
        }
        $rows = $this->consultar(
            'SELECT mvpm_nombre
             FROM cat_MedioVerificacionProgramaMir
             WHERE vpm_id = ? AND nim_id = ? AND pmi_id = ? AND mvpm_trimestreCierre = ?',
            [$vpmId, $nimId, $pmiId, $this->trimestre]
        );

        return $this->unirTextos($rows, 'mvpm_nombre');
    }

    private function supuestosPrograma(int $vpmId, int $nimId): string
    {
        $rows = $this->consultar(
            'SELECT spm_nombre
             FROM cat_SupuestoProgramaMir
             WHERE vpm_id = ? AND nim_id = ? AND spm_trimestreCierre = ?',
            [$vpmId, $nimId, $this->trimestre]
        );

        return $this->unirTextos($rows, 'spm_nombre');
    }

    private function mediosComponente($prgsId, $sprsId, $epfiId): string
    {
        $rows = $this->consultar(
            'SELECT gpo_id, ur_id, mvcm_nombre
             FROM cat_MedioVerificacionComponente
             WHERE prgs_id = ? AND sprs_id = ? AND epfi_id = ?
               AND mvcm_id IS NOT NULL AND mvcm_trimestreCierre IN (?)
             ORDER BY gpo_id, ur_id',
            [$prgsId, $sprsId, $epfiId, $this->trimestre]
        );

        return $this->unirTextosConUr($rows, 'mvcm_nombre');
    }

    private function supuestosComponente($prgsId, $sprsId): string
    {
        $rows = $this->consultar(
            'SELECT gpo_id, ur_id, spc_nombre
             FROM cat_SupuestoComponente
             WHERE prgs_id = ? AND sprs_id = ?
               AND spc_id IS NOT NULL AND spc_trimestreCierre IN (?)
             ORDER BY gpo_id, ur_id',
            [$prgsId, $sprsId, $this->trimestre]
        );

        return $this->unirTextosConUr($rows, 'spc_nombre');
    }

    private function mediosActividad($pdfId, $pdiId, $piiId): string
    {
        $rows = $this->consultar(
            'SELECT gpo_id, ur_id, mvac_nombre
             FROM cat_MedioVerificacionActividad
             WHERE pdf_id = ? AND pdi_id = ? AND pii_id = ? AND mvac_trimestreCierre IN (?)
             ORDER BY gpo_id, ur_id',
            [$pdfId, $pdiId, $piiId, $this->trimestre]
        );

        return $this->unirTextosConUr($rows, 'mvac_nombre');
    }

    private function supuestosActividad($pdfId, $pdiId): string
    {
        $rows = $this->consultar(
            'SELECT gpo_id, ur_id, spa_nombre
             FROM cat_SupuestoActividad
             WHERE pdf_id = ? AND pdi_id = ? AND spa_trimestreCierre IN (?)
             ORDER BY gpo_id, ur_id',
            [$pdfId, $pdiId, $this->trimestre]
        );

        return $this->unirTextosConUr($rows, 'spa_nombre');
    }

    /* ------------------------------------------------------------------
     * Orquestación por programa
     * ------------------------------------------------------------------ */

    public function generarPrograma($prgsId)
    {
        if (!$this->pintarDatosPrograma($prgsId)) {
            return;
        }
        $this->pintarPoblacionAlineacion($prgsId);
        $this->pintarMatrizIndicadores($prgsId);
    }

    private function pintarDatosPrograma($prgsId): bool
    {
        $this->tituloSeccion('DATOS DEL PROGRAMA');

        $urs = $this->consultar(
            'SELECT DISTINCT prgs_id, prgs_nombre, ur_clave, ur_nombre FROM (
                SELECT prgs_id, prgs_nombre, ur_clave, ur_nombre
                FROM vin_ProgramasMir
                JOIN cat_Programas USING (prgs_id)
                JOIN cat_UnidadResponsable USING (gpo_id, ur_id)
                WHERE prgs_id = :prgs_id
                UNION ALL
                SELECT prgs_id, prgs_nombre, ur_clave, ur_nombre
                FROM enc_UrSubprogramas e
                JOIN vin_ProgramasMir USING (prgs_id)
                JOIN cat_Programas USING (prgs_id)
                JOIN cat_UnidadResponsable c ON c.gpo_id = e.gpo_id AND c.ur_id = e.ur_id
                WHERE prgs_id = :prgs_id
                GROUP BY prgs_id, e.gpo_id, e.ur_id
            ) a',
            ['prgs_id' => $prgsId]
        );

        if (empty($urs)) {
            $this->SetFont('UniviaPro-Bold', 'B', 8);
            $this->Cell(self::ANCHO_TOTAL, 8, utf8Decode('Sin datos MIR para el programa ' . $prgsId), 0, 1, 'C');

            return false;
        }

        $this->setBorders(1);
        $this->SetWidths([self::ANCHO_TOTAL]);
        $this->SetAligns(['C']);
        $this->SetFont('UniviaPro-Bold', 'B', self::FONT_TITULO);
        $this->Row([
            'PROGRAMA ' . str_pad($urs[0]['prgs_id'], 2, '0', STR_PAD_LEFT) . ' ' . utf8Decode($urs[0]['prgs_nombre']),
        ]);
        $this->SetRelleno('no');
        $this->setBorders(0);

        $this->pintarUrsPrograma($urs);

        return true;
    }

    private function pintarUrsPrograma(array $urs): void
    {
        if (empty($urs)) {
            return;
        }

        $altoLinea = 3.5;
        $anchoCol = 140.0;
        $x0 = 10.0;
        $xMedio = $x0 + $anchoCol;

        // Distribución MIR: 1ª UR a la izquierda (responsable),
        // resto en zigzag derecha/izquierda como participantes.
        $lineasIzq = [];
        $lineasDer = [];
        foreach ($urs as $i => $ur) {
            $textoUr = $ur['ur_clave'] . ' ' . utf8Decode($ur['ur_nombre']);
            if ($i === 0) {
                $lineasIzq[] = 'UR Responsable: ' . $textoUr;
                continue;
            }
            if ($i % 2 === 1) {
                $prefijo = empty($lineasDer) ? "UR's Participantes: " : '';
                $lineasDer[] = $prefijo . $textoUr;
            } else {
                $prefijo = ($i === 2) ? "UR's Participantes: " : '';
                $lineasIzq[] = $prefijo . $textoUr;
            }
        }

        $textoIzq = implode("\n", $lineasIzq);
        $textoDer = implode("\n", $lineasDer);

        $this->SetFont('UniviaPro-Regular', '', self::FONT_DATO);
        $hIzq = max(1, $this->NbLines($anchoCol - 4, $textoIzq)) * $altoLinea;
        $hDer = max(1, $this->NbLines($anchoCol - 4, $textoDer ?: ' ')) * $altoLinea;
        $h = max($hIzq, $hDer, $altoLinea * 2) + 2;

        $this->asegurarEspacio($h + 2);
        $y0 = $this->GetY();

        $this->aplicarEstiloBordeMatriz();
        // Solo marco exterior + divisor central (sin líneas horizontales internas).
        $this->Rect($x0, $y0, $anchoCol * 2, $h);
        $this->Line($xMedio, $y0, $xMedio, $y0 + $h);

        $this->SetXY($x0 + 2, $y0 + 1);
        $this->MultiCell($anchoCol - 4, $altoLinea, $textoIzq, 0, 'L');
        $this->SetXY($xMedio + 2, $y0 + 1);
        $this->MultiCell($anchoCol - 4, $altoLinea, $textoDer, 0, 'L');

        $this->SetY($y0 + $h);
        $this->setBorders(0);
    }

    private function pintarPoblacionAlineacion($prgsId): void
    {
        $d1 = $this->consultarUno(
            'SELECT eje_nombre, obj_nombre, tem_nombre, prgs_nombre, ods_nombre, spe_nombre,
                pb.pbf_nombre, prgs_hombres, prgs_mujeres, prgs_total,
                prgs_hombresO AS total_hombres, prgs_mujeresO AS total_mujeres, prgs_totalO AS total_sprs,
                prgs_MedioVerificacion, pbO.pbf_nombre AS pbf_nombreO, obp_nombre, ple_nombre, ope_nombre
             FROM vin_Programa vp
             INNER JOIN cat_Programas p USING (prgs_id)
             INNER JOIN cat_PoblacionBeneficiada pb USING (pbf_id)
             INNER JOIN cat_PoblacionBeneficiada AS pbO ON pbO.pbf_id = p.pbf_idO
             INNER JOIN enc_PedObjetivo e USING (epo_id)
             INNER JOIN cat_Eje USING (eje_id)
             INNER JOIN cat_Tema t USING (eje_id, tem_id)
             INNER JOIN cat_Objetivo o ON o.eje_id = e.eje_id AND o.tem_id = t.tem_id AND o.obj_id = e.obj_id
             INNER JOIN enc_Pes ps ON ps.obp_id = vp.obp_id AND ps.spe_id = vp.spe_id
             INNER JOIN cat_ObjetivoPes op ON op.obp_id = vp.obp_id AND vp.spe_id = op.spe_id
             INNER JOIN cat_SectorPes s ON s.spe_id = vp.spe_id
             INNER JOIN enc_PedODS USING (epods_id)
             INNER JOIN cat_ODS USING (ods_id)
             INNER JOIN cat_PlanEspecial USING (ple_id)
             INNER JOIN cat_ObjetivoPe pl ON vp.ple_id = pl.ple_id AND vp.ope_id = pl.ope_id
             WHERE prgs_id = ?
             GROUP BY prgs_id',
            [$prgsId]
        );

        if (!$d1) {
            return;
        }

        $this->tituloSeccion('POBLACIÓN O ÁREA DE ENFOQUE');

        // Contenido sin borde superior (el título ya cierra la franja).
        // Los laterales se trazan al final según la altura real del bloque.
        $yInicio = $this->GetY();
        $this->setBorders(0);
        $this->aplicarEstiloBordeMatriz();

        $this->setX(10);
        $this->SetFont('UniviaPro-Bold', 'B', self::FONT_ENCABEZADO);
        $this->Cell(42, 5, utf8Decode('Población o área de enfoque potencial:'), 0, 0, 'L');
        $this->SetFont('UniviaPro-Regular', '', self::FONT_DATO);
        $yPot = $this->GetY();
        $this->MultiCell(98, 5, utf8Decode($d1['pbf_nombre']), 0, 'L');
        $yDespuesPot = $this->GetY();

        $this->SetXY(150, $yPot);
        $this->SetFont('UniviaPro-Bold', 'B', self::FONT_ENCABEZADO);
        $this->Cell(42, 5, utf8Decode('Población o área de enfoque objetivo:'), 0, 0, 'L');
        $this->SetFont('UniviaPro-Regular', '', self::FONT_DATO);
        $this->MultiCell(98, 5, utf8Decode($d1['pbf_nombreO']), 0, 'L');
        $yDespuesObj = $this->GetY();
        $this->SetY(max($yDespuesPot, $yDespuesObj));

        $this->setX(10);
        $this->SetFont('UniviaPro-Regular', '', self::FONT_DATO);
        $this->Cell(
            140,
            5,
            ' Hombres:   ' . $d1['prgs_hombres'] . '         Mujeres:   ' . $d1['prgs_mujeres'] . '         Total:   ' . $d1['prgs_total'],
            0,
            0,
            'L'
        );
        $this->Cell(
            140,
            5,
            ' Hombres:   ' . $d1['total_hombres'] . '         Mujeres:   ' . $d1['total_mujeres'] . '         Total:   ' . $d1['total_sprs'],
            0,
            1,
            'L'
        );

        $this->setX(10);
        $this->SetFont('UniviaPro-Bold', 'B', self::FONT_ENCABEZADO);
        $this->Cell(30, 5, utf8Decode('Medio de verificación:'), 0, 0, 'L');
        $this->SetFont('UniviaPro-Regular', '', self::FONT_DATO);
        $this->MultiCell(250, 5, utf8Decode($d1['prgs_MedioVerificacion']), 0, 'L');

        $yFin = $this->GetY();
        // Marco lateral continuo del bloque (sin línea superior extra).
        $this->Line(10, $yInicio, 10, $yFin);
        $this->Line(290, $yInicio, 290, $yFin);
        $this->Line(10, $yFin, 290, $yFin);

        $this->tituloSeccion('ALINEACIÓN');

        $this->SetFont('UniviaPro-Bold', 'B', self::FONT_ENCABEZADO);
        $this->setX(10);
        $this->Cell(self::ANCHO_TOTAL, 4, 'PLAN ESTATAL DE DESARROLLO', 'LR', 1, 'L');

        $this->setX(10);
        $this->setBorders('LR');
        $this->setAltoLinea(4);
        $this->SetWidths([7, 83, 9, 81, 14, 86]);
        $this->SetAligns(['L', 'L', 'L', 'L', 'L', 'L']);
        $this->setFuentes($this->fuentesEtiquetaValor(3));
        $this->Row([
            'EJE:',
            strtoupper(utf8Decode($d1['eje_nombre'])),
            'TEMA:',
            strtoupper(utf8Decode($d1['tem_nombre'])),
            'OBJETIVO:',
            strtoupper(utf8Decode($d1['obj_nombre'])),
        ], 'LR');

        $this->setX(10);
        $this->SetFont('UniviaPro-Bold', 'B', self::FONT_ENCABEZADO);
        if ($d1['obp_nombre'] != 'N/A') {
            $this->Cell(self::ANCHO_TOTAL, 4, utf8Decode('PLAN ESTRATÉGICO SECTORIAL'), 'LR', 1, 'L');
            $this->setBorders('LR');
            $this->SetWidths([15, 125, 15, 125]);
            $this->SetAligns(['L', 'L', 'L', 'L']);
            $this->setFuentes($this->fuentesEtiquetaValor(2));
            $this->Row([
                'OBJETIVO:',
                strtoupper(utf8Decode($d1['obp_nombre'])),
                'SECTOR:',
                strtoupper(utf8Decode($d1['spe_nombre'])),
            ], 'LR');
        } else {
            $this->Cell(self::ANCHO_TOTAL, 4, utf8Decode('PLAN ESPECIAL'), 'LR', 1, 'L');
            $this->setBorders('LR');
            $this->SetWidths([15, 125, 15, 125]);
            $this->SetAligns(['L', 'L', 'L', 'L']);
            $this->setFuentes($this->fuentesEtiquetaValor(2));
            $this->Row([
                'PLAN:',
                strtoupper(utf8Decode($d1['ple_nombre'])),
                'OBJETIVO:',
                strtoupper(utf8Decode($d1['ope_nombre'])),
            ], 'LR');
        }

        $this->setX(10);
        $this->setBorders('LR');
        $this->setAltoLinea(2.5);
        $this->SetWidths([25, 255]);
        $this->SetAligns(['L', 'L']);
        $this->setFuentes($this->fuentesEtiquetaValor(1));
        $this->Row([
            utf8Decode('VINCULACIÓN ODS: '),
            strtoupper(utf8Decode($d1['ods_nombre'])),
        ], 'LR');
        $this->setBorders(0);
    }

    private function pintarMatrizIndicadores($prgsId): void
    {
        $this->tituloSeccion('INDICADORES PARA RESULTADOS');
        $this->imprimirEncabezadoTabla = true;
        $this->imprimirEncabezadoTablaPrimeraVez();

        $this->pintarNivelFin($prgsId);
        $this->pintarNivelProposito($prgsId);
        $this->pintarComponentesActividades($prgsId);
    }

    private function pintarNivelFin($prgsId): void
    {
        $rows = $this->consultar(
            'SELECT IFNULL(ind_fin, 0) ind_fin, IFNULL(ind_id, 0) ind_id,
                IFNULL(fin_formulaCalculo, \'-\') fin_formulaCalculo,
                IFNULL(tpi_fin, 0) tpi_fin, IFNULL(dim_fin, 0) dim_fin,
                IFNULL(frm_fin, 0) frm_fin, IFNULL(ses_fin, 0) ses_fin,
                IF(ISNULL(mtf_original), "", mtf_original) AS mem_original,
                mir_resumenNarrativo, s.pmi_id, vpm_id
             FROM cat_Mir
             INNER JOIN vin_ProgramasMir USING (mir_id)
             INNER JOIN vin_Programa USING (prgs_id)
             INNER JOIN enc_PedObjetivo USING (epo_id)
             INNER JOIN cat_Objetivo USING (eje_id, tem_id, obj_id)
             LEFT JOIN (
                SELECT vpm_id, pmi_id, nim_id, ind_id, ind_nombreMIR AS ind_fin,
                    ind_formulaCalculo AS fin_formulaCalculo,
                    tpi_nombre tpi_fin, dim_nombre dim_fin, frm_nombre frm_fin, ses_nombre ses_fin,
                    mtf_id, mtf_original
                FROM vin_ProgramaMirIndicador
                INNER JOIN cat_Indicador USING (nim_id, ind_id)
                JOIN cat_TipoIndicador USING (tpi_id)
                JOIN cat_Dimension USING (dim_id)
                JOIN cat_FrecuenciaMedicion USING (frm_id)
                JOIN cat_SentidoEsperado USING (ses_id)
                LEFT JOIN cat_MetaFin USING (pmi_id)
                WHERE nim_id = 1 AND vin_ProgramaMirIndicador.sts_id = 1
             ) s USING (vpm_id)
             WHERE prgs_id = :prg_id',
            [':prg_id' => $prgsId]
        );

        $vpmVisto = null;
        $primerFin = true;
        foreach ($rows as $row) {
            $resumen = '';
            $supuestos = '';
            if ($vpmVisto !== $row['vpm_id']) {
                $vpmVisto = $row['vpm_id'];
                $resumen = strtoupper(utf8Decode($row['mir_resumenNarrativo']));
                $supuestos = strtoupper(utf8Decode($this->supuestosPrograma((int) $row['vpm_id'], 1)));
            }

            $indicador = utf8Decode($this->formatoIndicador([
                1 => strtoupper((string) $row['ind_fin']),
                2 => $row['fin_formulaCalculo'],
                3 => $row['tpi_fin'],
                4 => $row['dim_fin'],
                5 => $row['frm_fin'],
                6 => $row['ses_fin'],
                7 => $row['mem_original'],
            ]));

            $medios = strtoupper(utf8Decode($this->mediosPrograma((int) $row['vpm_id'], 1, $row['pmi_id'])));
            $nivel = $primerFin ? 'FIN' : '';
            $primerFin = false;

            $this->filaMatriz($nivel, $resumen, $indicador, $medios, $supuestos);
        }
    }

    private function pintarNivelProposito($prgsId): void
    {
        $rows = $this->consultar(
            'SELECT IFNULL(vpm_id, 0) vpm_id, IFNULL(ind_pro, "") ind_pro, IFNULL(ind_id, 0) ind_id,
                IFNULL(pro_formulaCalculo, "") pro_formulaCalculo, IFNULL(tpi_pro, "") tpi_pro,
                IFNULL(dim_pro, "") dim_pro, IFNULL(frm_pro, "") frm_pro, IFNULL(ses_pro, "") ses_pro,
                IFNULL(vpm_resumenNarrativo, "") proposito,
                IFNULL(mpr_original, "") mpr_original, IFNULL(mpr_anteriorIndicador, "") mpr_anteriorIndicador,
                IFNULL(umi_id, 0) umi_id, mpr_denominador, mprt_denominador, pmi_id
             FROM cat_Mir
             INNER JOIN vin_ProgramasMir USING (mir_id)
             LEFT JOIN (
                SELECT vpm_id, pmi_id, nim_id, ind_id, ind_nombreMIR AS ind_pro,
                    ind_formulaCalculo AS pro_formulaCalculo,
                    tpi_nombre tpi_pro, dim_nombre dim_pro, frm_nombre frm_pro, ses_nombre ses_pro, umi_id,
                    m.mpr_id, mpr_original, mpr_anteriorIndicador, mpr_denominador, mprt_denominador
                FROM vin_ProgramaMirIndicador
                INNER JOIN cat_Indicador USING (nim_id, ind_id)
                JOIN cat_TipoIndicador USING (tpi_id)
                JOIN cat_Dimension USING (dim_id)
                JOIN cat_FrecuenciaMedicion USING (frm_id)
                JOIN cat_SentidoEsperado USING (ses_id)
                LEFT JOIN cat_MetaProposito m USING (pmi_id)
                LEFT JOIN cat_MetaPropositoTrimestre USING (pmi_id)
                WHERE nim_id = 2 AND vin_ProgramaMirIndicador.sts_id = 1
             ) s USING (vpm_id)
             WHERE prgs_id = :prg_id',
            [':prg_id' => $prgsId]
        );

        $vpmVisto = null;
        $primerPro = true;
        foreach ($rows as $row) {
            $resumen = '';
            $supuestos = '';
            if ($vpmVisto !== $row['vpm_id']) {
                $vpmVisto = $row['vpm_id'];
                $resumen = utf8Decode($row['proposito']);
                $supuestos = strtoupper(utf8Decode($this->supuestosPrograma((int) $row['vpm_id'], 2)));
            }

            if (in_array((int) $row['umi_id'], [3, 4, 5], true)) {
                $meta = $row['mpr_anteriorIndicador'];
            } elseif ((int) $row['umi_id'] === 2) {
                $meta = $row['mprt_denominador'];
            } else {
                $meta = $row['mpr_original'];
            }

            $indicador = utf8Decode($this->formatoIndicador([
                1 => $row['ind_pro'],
                2 => $row['pro_formulaCalculo'],
                3 => $row['tpi_pro'],
                4 => $row['dim_pro'],
                5 => $row['frm_pro'],
                6 => $row['ses_pro'],
                7 => $meta,
            ]));

            $medios = strtoupper(utf8Decode($this->mediosPrograma((int) $row['vpm_id'], 2, $row['pmi_id'])));
            $nivel = $primerPro ? utf8Decode('PROPÓSITO') : '';
            $primerPro = false;

            $this->filaMatriz($nivel, $resumen, $indicador, $medios, $supuestos);
        }
    }

    private function pintarComponentesActividades($prgsId): void
    {
        $componentes = $this->consultar(
            "SELECT sprs_id, pdf_id, pdf_mir AS componente, epfi_id AS ipf_id, ind_id,
                GROUP_CONCAT('UR ', m.gpo_id, m.ur_id, ' ---- ',
                    CASE
                        WHEN umi_id IN (1, 3, 4) THEN aux.mco_original
                        WHEN umi_id = 5 THEN aux.mco_anteriorIndicador
                        WHEN umi_id = 2 THEN IF(tiv_id = 1, (mcot_denominadorT2 + mcot_denominadorT4), mcot_denominadorT4)
                    END,
                    '\n' ORDER BY m.gpo_id, m.ur_id SEPARATOR '|'
                ) AS urs,
                ind_nombreMIR, ind_formulaCalculo, tpi_id, tpi_nombre, dim_id, dim_nombre,
                frm_id, frm_nombre, ses_id, ses_nombre, ind_nombre, tiv_id
             FROM cat_Subprogramas
             INNER JOIN cat_ProductoFinal USING (pdf_id)
             INNER JOIN enc_ProductoFinalIndicador e USING (pdf_id)
             LEFT JOIN cat_MetaComponente m USING (prgs_id, sprs_id, epfi_id)
             LEFT JOIN aux_HistoricoModificacionMetaComponente aux USING (prgs_id, sprs_id, epfi_id, gpo_id, ur_id)
             LEFT JOIN cat_Indicador i USING (nim_id, ind_id)
             LEFT JOIN cat_TipoIndicador USING (tpi_id)
             LEFT JOIN cat_Dimension USING (dim_id)
             LEFT JOIN cat_FrecuenciaMedicion USING (frm_id)
             LEFT JOIN cat_SentidoEsperado USING (ses_id)
             INNER JOIN cat_TipoValor USING (tiv_id)
             WHERE prgs_id = ? AND (esr_id = ? OR sprs_id = ?) AND pdf_id != ?
               AND e.trim_0 = ? AND m.mco_original >= 0 AND aux.tri_id = ?
             GROUP BY prgs_id, sprs_id, pdf_id, epfi_id, ind_id
             ORDER BY epfi_orden, sprs_id, pdf_id, ind_id",
            [$prgsId, 4, 0, 0, 1, $this->trimestre]
        );

        $pdfVisto = null;
        $numIndicadoresCom = 1;
        $contInd = 1;

        foreach ($componentes as $rowC) {
            $nivel = '';
            $resumen = '';
            $supuestos = '';

            if ($pdfVisto !== $rowC['pdf_id']) {
                $pdfVisto = $rowC['pdf_id'];
                $nivel = 'COMPONENTE ';
                $resumen = utf8Decode($rowC['componente']);
                $supuestos = utf8Decode($this->supuestosComponente($prgsId, $rowC['sprs_id']));
                $contInd = 1;

                $conteo = $this->consultarUno(
                    'SELECT COUNT(*) AS numInd FROM enc_ProductoFinalIndicador WHERE pdf_id = ? AND trim_0 = ?',
                    [$pdfVisto, 1]
                );
                $numIndicadoresCom = (int) ($conteo['numInd'] ?? 1);
            }

            $meta = (($rowC['ind_nombre'] ?? '') !== '')
                ? str_replace('|', '', (string) $rowC['urs'])
                : '';

            $indicador = utf8Decode($this->formatoIndicador([
                1 => $rowC['ind_nombreMIR'],
                2 => $rowC['ind_formulaCalculo'],
                3 => $rowC['tpi_nombre'],
                4 => $rowC['dim_nombre'],
                5 => $rowC['frm_nombre'],
                6 => $rowC['ses_nombre'],
                7 => $meta,
            ]));

            $medios = utf8Decode($this->mediosComponente($prgsId, $rowC['sprs_id'], $rowC['ipf_id']));
            $this->filaMatriz($nivel, $resumen, $indicador, $medios, $supuestos);

            if ($contInd === $numIndicadoresCom) {
                $this->pintarActividadesDeComponente((int) $rowC['pdf_id']);
            }
            $contInd++;
        }
    }

    private function pintarActividadesDeComponente(int $pdfId): void
    {
        $actividades = $this->consultar(
            'SELECT pdf_id, pdi_id, pdi_mir, ind_id, ind_nombre, ind_nombreMIR, ind_definicion,
                ind_formulaCalculo, tpi_id, tpi_nombre, dim_id, dim_nombre, frm_id, frm_nombre,
                ses_id, ses_nombre, pii_id,
                GROUP_CONCAT("UR ", m.gpo_id, m.ur_id, " ---- ",
                    CASE
                        WHEN umi_id IN (1, 3, 4) THEN m.macm_original
                        WHEN umi_id = 5 THEN m.macm_anteriorIndicador
                        WHEN umi_id = 2 THEN IF(
                            tiv_id = 1,
                            (macm_denominadorT1 + macm_denominadorT2 + macm_denominadorT3 + macm_denominadorT4),
                            macm_denominadorT4
                        )
                    END,
                    "\n" ORDER BY gpo_id, ur_id SEPARATOR "|"
                ) AS urs
             FROM cat_ProductoIntermedio
             INNER JOIN vin_ProductoIntermedioIndicador v USING (pdi_id)
             INNER JOIN vin_ProductoFinalProductoIntermedio vpfi USING (pdf_id, pdi_id)
             LEFT JOIN (
                SELECT pii_id, mac.gpo_id, mac.ur_id, mac.macm_original, macm_descripcion,
                    macm_denominadorT1, macm_denominadorT2, macm_denominadorT3, macm_denominadorT4,
                    tri_id, aux.macm_anteriorIndicador
                FROM cat_MetaActividadMir mac
                LEFT JOIN aux_HistoricoMetaActividadMir aux USING (pii_id, macm_id)
                WHERE tri_id = 0
                GROUP BY gpo_id, ur_id, pii_id, macm_id
             ) m USING (pii_id)
             INNER JOIN cat_Indicador i USING (nim_id, ind_id)
             INNER JOIN cat_TipoIndicador USING (tpi_id)
             INNER JOIN cat_Dimension USING (dim_id)
             INNER JOIN cat_FrecuenciaMedicion USING (frm_id)
             INNER JOIN cat_SentidoEsperado USING (ses_id)
             INNER JOIN cat_TipoValor USING (tiv_id)
             WHERE pdf_id = ? AND v.trim_0 = ? AND vpfi.sts_id = ? AND macm_original >= 0
             GROUP BY pdi_id, ind_id
             ORDER BY pii_orden, pdf_id, pdi_id, ind_id',
            [$pdfId, 1, 1]
        );

        $pdiVisto = null;
        foreach ($actividades as $act) {
            $nivel = '';
            $resumen = '';
            $supuestos = '';

            if ($pdiVisto !== $act['pdi_id']) {
                $pdiVisto = $act['pdi_id'];
                $nivel = 'ACTIVIDAD ';
                $resumen = utf8Decode($act['pdi_mir']);
                $supuestos = utf8Decode($this->supuestosActividad($pdfId, $act['pdi_id']));
            }

            $indBase = $this->formatoIndicador([
                1 => $act['ind_nombreMIR'],
                2 => $act['ind_formulaCalculo'],
                3 => $act['tpi_nombre'],
                4 => $act['dim_nombre'],
                5 => $act['frm_nombre'],
                6 => $act['ses_nombre'],
            ]);
            if (trim((string) $act['ind_definicion']) !== '') {
                $indBase .= ($indBase !== '' ? "\n" : '') . '7 - ';
            }
            $indBase = str_replace(['“', '”'], '"', $indBase);
            $indicador = utf8Decode($indBase) . str_replace('|', '', (string) $act['urs']);
            $medios = utf8Decode($this->mediosActividad($pdfId, $act['pdi_id'], $act['pii_id']));

            $this->filaMatriz($nivel, $resumen, $indicador, $medios, $supuestos);
        }
    }
}

$pdf = new Pdf_Poa03('L', 'mm', 'Letter');
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_POA);
$pdf->setOrientation('L');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 28);
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->setAltoLinea(2.5);
$pdf->activarEncabezado = true;
$pdf->AddPage();

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql('
    SELECT DISTINCT vpm.prgs_id, pg.prgs_nombre,
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

$primero = true;
foreach ($programas as $programa) {
    if (!$primero) {
        $pdf->imprimirEncabezadoTabla = false;
        $pdf->encabezadoImpreso = false;
        $pdf->AddPage();
    }
    $primero = false;
    $pdf->generarPrograma($programa['prgs_id']);
}

if (empty($programas)) {
    $pdf->SetFont('UniviaPro-Bold', 'B', 10);
    $pdf->Cell(280, 10, utf8Decode('No hay programas MIR vinculados a la Unidad Responsable.'), 0, 1, 'C');
}

$pdf->Output();
