<?php

/**
 * Description of exp_ComparativoPresupuestario
 *
 * Excel del Comparativo Presupuestario (Ur - Gran Fuente - Clase de Gasto - Cédula - Partida)
 * disparado desde ControladorTechoFinancieroPoa (POA - Techos Financieros).
 *
 * Estructura por UR:
 *  - Renglón UR (clave + nombre)              [azul navy oscuro]
 *  - Renglón Gran Fuente (A=ESTATAL, B=FED)   [azul oscuro]
 *  - Renglón Clase de Gasto                   [azul medio]
 *  - Renglón Cédula                           [azul muy claro, bold + italic]
 *  - Renglón Partida                          [blanco]
 *
 * Permits: pantalla con permiso A-3-10-1 (Techos Financieros POA).
 */

session_start();
ini_set('display_errors', '0');
error_reporting(E_ERROR);
ob_start();

require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../lib/Phpspreadsheet.php';

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

if (!isset($_SESSION[_S]['prm']['A']['3']['10']['1'])) {
    die('Sin permisos para generar el reporte.');
}

class exp_ComparativoPresupuestario extends Phpspreadsheet
{
    /* --- Columnas físicas del reporte --- */
    private const COL_CLAVE  = 'A';
    private const COL_CONC   = 'B';
    private const COL_TECHO  = 'C';
    private const COL_PRES   = 'D';
    private const COL_DIF    = 'E';
    private const COL_PCT    = 'F';
    private const COL_FIRST  = 'A';
    private const COL_LAST   = 'F';
    private const N_COLS     = 6;

    /* --- Paleta jerárquica (RGB sin '#') --- */
    private const C_UR    = '1F4E79';
    private const C_GF    = '2E75B6';
    private const C_CLG   = '5B9BD5';
    private const C_CED   = 'DEEBF7';
    private const C_WHITE = 'FFFFFF';
    private const C_HDR   = '1F4E79';

    private $gpo_id;
    private $ur_id;
    private $ur_clave;
    private $ur_nombre;
    private $arbol = [];
    private $techos = [];
    /** Nombres de clase de gasto (p. ej. desde techo sin presupuesto costeado) */
    private $clgNombres = [];
    /** Nombres de gran fuente / clasificador de financiamiento (desde techo) */
    private $terNombres = [];

    public function __construct()
    {
        parent::__construct();

        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'] ?? '';
        $this->ur_id  = $_SESSION[_S]['clasAdm-ur']  ?? '';

        if ($this->gpo_id === '' || $this->ur_id === '') {
            die('Debe seleccionar una Unidad Responsable antes de generar el reporte.');
        }

        $ur = new UnidadResponsable();
        $ur->setGpo_id($this->gpo_id);
        $ur->setUr_id($this->ur_id);
        $ur->select();
        $this->ur_clave  = $ur->getClave();
        $this->ur_nombre = mb_strtoupper((string) $ur->getNombre(), 'UTF-8');

        $this->cargaTechos();
        $this->cargaPresupuesto();

        $this->preparaHoja();
        $linea = $this->pintaEncabezado();
        $linea = $this->pintaColumnas($linea);
        $this->pintaDatos($linea);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $this->output_web_browser('ComparativoPresupuestario_' . $this->ur_clave . '.xlsx');
    }

    /* =====================================================================
        1. Carga de datos
    ===================================================================== */
    private function cargaTechos()
    {
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql('
            SELECT t.ter_id, t.clg_id, clg.clg_nombre, ter.ter_nombre, SUM(t.tec_techoPresupuestal) AS techo
            FROM cat_Techos t
                LEFT JOIN ' . BD_SINPRES . '.cat_TipoRecurso ter ON ter.ter_id = t.ter_id
                LEFT JOIN cat_ClaseGasto clg ON clg.clg_id = t.clg_id
            WHERE t.gpo_id = :gpo
                AND t.ur_id  = :ur
            GROUP BY t.ter_id, t.clg_id
            HAVING techo <> 0
            ORDER BY t.ter_id, t.clg_id;
        ');
        $con->consultar([':gpo' => $this->gpo_id, ':ur' => $this->ur_id]);
        while ($r = $con->extraer()) {
            $this->techos[$r['ter_id']][$r['clg_id']] = (float) $r['techo'];
            if (!empty($r['clg_nombre'])) {
                $this->clgNombres[$r['clg_id']] = mb_strtoupper((string) $r['clg_nombre'], 'UTF-8');
            }
            if (!empty($r['ter_nombre'])) {
                $this->terNombres[$r['ter_id']] = mb_strtoupper((string) $r['ter_nombre'], 'UTF-8');
            }
        }
        $con->desconectar();
    }

    private function nombreGranFuente($terId, $desdeArbol = null)
    {
        if ($desdeArbol !== null && $desdeArbol !== '') {
            return $desdeArbol;
        }
        if (!empty($this->terNombres[$terId])) {
            return $this->terNombres[$terId];
        }

        return ($terId === 'A') ? 'ESTATAL' : 'FEDERAL';
    }

    private function cargaPresupuesto()
    {
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql('
            SELECT
                so.ter_id,
                cpe.clg_id,
                cg.clg_nombre,
                COALESCE(po.ccd_id, 0) AS ccd_id,
                cpe.pg_id,
                cpe.pe_id,
                cpe.pe_nombre,
                SUM(po.pre_original) AS presupuestado
            FROM presupuesto p
                INNER JOIN presupuestoOriginal po USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                INNER JOIN cat_PartidaEspecifica cpe USING (pg_id, pe_id)
                INNER JOIN cat_ClaseGasto cg USING (clg_id)
                INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so USING (cff_id, fte_id, sft_id, ori_id, sor_id)
                LEFT  JOIN enc_PartEspCed epc USING (pg_id, pe_id)
            WHERE p.gpo_id = :gpo
                AND p.ur_id  = :ur
            GROUP BY so.ter_id, cpe.clg_id, ccd_id, cpe.pg_id, cpe.pe_id
            HAVING presupuestado <> 0
            ORDER BY so.ter_id, cpe.clg_id, ccd_id, cpe.pg_id, cpe.pe_id;
        ');
        $con->consultar([':gpo' => $this->gpo_id, ':ur' => $this->ur_id]);
        while ($r = $con->extraer()) {
            $ter = $r['ter_id'];
            $clg = $r['clg_id'];
            $ccd = (int) $r['ccd_id'];
            $key = $r['pg_id'] . '-' . $r['pe_id'];

            if (!isset($this->arbol[$ter])) {
                $this->arbol[$ter] = [
                    'nombre' => ($ter == 'A') ? 'ESTATAL' : 'FEDERAL',
                    'clg'    => [],
                ];
            }
            if (!isset($this->arbol[$ter]['clg'][$clg])) {
                $this->arbol[$ter]['clg'][$clg] = [
                    'nombre' => mb_strtoupper((string) $r['clg_nombre'], 'UTF-8'),
                    'ced'    => [],
                ];
            }
            if (!isset($this->arbol[$ter]['clg'][$clg]['ced'][$ccd])) {
                $this->arbol[$ter]['clg'][$clg]['ced'][$ccd] = [
                    'nombre'   => self::nombreCedula($ccd),
                    'partidas' => [],
                ];
            }
            $this->arbol[$ter]['clg'][$clg]['ced'][$ccd]['partidas'][$key] = [
                'nombre'        => $r['pe_nombre'],
                'presupuestado' => (float) $r['presupuestado'],
            ];
        }
        $con->desconectar();
    }

    /* =====================================================================
       2. Preparación de la hoja (anchos, hoja activa, etc.)
    ===================================================================== */
    private function preparaHoja()
    {
        $sheet = $this->spreadsheet->setActiveSheetIndex(0);
        $sheet->setTitle('Comparativo');

        $sheet->getColumnDimension(self::COL_CLAVE)->setWidth(14);
        $sheet->getColumnDimension(self::COL_CONC)->setWidth(60);
        $sheet->getColumnDimension(self::COL_TECHO)->setWidth(22);
        $sheet->getColumnDimension(self::COL_PRES)->setWidth(22);
        $sheet->getColumnDimension(self::COL_DIF)->setWidth(22);
        $sheet->getColumnDimension(self::COL_PCT)->setWidth(12);
    }

    /* =====================================================================
        3. Encabezado: logo + líneas de título
    ===================================================================== */
    private function pintaEncabezado()
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        $rangoUltimo = self::COL_LAST;

        $lineas = [
            'GOBIERNO DEL ESTADO DE OAXACA',
            'ANTEPROYECTO DE PRESUPUESTO DE EGRESOS ' . APP_EJERCICIO,
            $this->ur_clave . ' ' . $this->ur_nombre,
            '',
            'COMPARATIVO PRESUPUESTARIO (Ur - Gran Fuente - Clase de Gasto - Cédula - Partida)',
        ];

        $linea = 1;
        foreach ($lineas as $idx => $texto) {
            $celdaIni = self::COL_FIRST . $linea;
            $celdaFin = $rangoUltimo . $linea;
            $sheet->mergeCells($celdaIni . ':' . $celdaFin);
            $sheet->setCellValue($celdaIni, $texto);
            $sheet->getStyle($celdaIni)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'font' => [
                    'bold' => ($idx <= 2 || $idx == 4),
                    'size' => ($idx == 0) ? 14 : (($idx == 4) ? 12 : 11),
                ],
            ]);
            $sheet->getRowDimension($linea)->setRowHeight(20);
            $linea++;
        }

        $this->pintaLogo();

        $linea++;
        return $linea;
    }

    private function pintaLogo()
    {
        $ruta = dirname(__FILE__) . '/../../../../img/logo_sefin _rep_presu.jpeg';
        if (!file_exists($ruta)) {
            return;
        }
        $drawing = new Drawing();
        $drawing->setName('Logo SEFIN');
        $drawing->setDescription('Secretaria de Finanzas Oaxaca');
        $drawing->setPath($ruta);
        $drawing->setResizeProportional(true);
        $drawing->setHeight(80);
        $drawing->setCoordinates(self::COL_FIRST . '1');
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(5);
        $drawing->setWorksheet($this->spreadsheet->getActiveSheet());
    }

    /* =====================================================================
        4. Cabecera de columnas (centrada, color C_HDR, texto blanco)
    ===================================================================== */
    private function pintaColumnas($linea)
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        $cols  = [
            self::COL_CLAVE => 'CLAVE',
            self::COL_CONC  => 'CONCEPTO',
            self::COL_TECHO => 'Techo Financiero',
            self::COL_PRES  => 'Presupuestado',
            self::COL_DIF   => 'Diferencia',
            self::COL_PCT   => '%',
        ];
        foreach ($cols as $col => $txt) {
            $sheet->setCellValue($col . $linea, $txt);
        }

        $rango = self::COL_FIRST . $linea . ':' . self::COL_LAST . $linea;
        $sheet->getStyle($rango)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => self::C_WHITE],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::C_HDR],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => self::C_WHITE],
                ],
            ],
        ]);
        $sheet->getRowDimension($linea)->setRowHeight(22);

        $linea++;
        return $linea;
    }

    /* =====================================================================
        5. Datos: UR -> Gran Fuente -> Clase de Gasto -> Cédula -> Partida
    ===================================================================== */
    private function pintaDatos($linea)
    {
        $totalUrTecho  = 0;
        $totalUrPresup = 0;

        $bufferTer = [];
        $terIds = array_unique(array_merge(
            array_keys($this->arbol),
            array_keys($this->techos)
        ));
        sort($terIds);

        foreach ($terIds as $terId) {
            $ter = $this->arbol[$terId] ?? [
                'nombre' => $this->nombreGranFuente($terId),
                'clg'    => [],
            ];
            $totTerTecho  = 0;
            $totTerPresup = 0;
            $bufferClg    = [];

            $clgIds = array_unique(array_merge(
                array_keys($ter['clg']),
                array_keys($this->techos[$terId] ?? [])
            ));
            sort($clgIds);

            foreach ($clgIds as $clgId) {
                $clgTecho  = $this->techos[$terId][$clgId] ?? 0;
                $clgPresup = 0;
                $bufferCed = [];

                if (isset($ter['clg'][$clgId])) {
                    foreach ($ter['clg'][$clgId]['ced'] as $ccdId => $ced) {
                        $cedPresup  = 0;
                        $bufferPart = [];
                        foreach ($ced['partidas'] as $claveP => $part) {
                            $cedPresup += $part['presupuestado'];
                            $bufferPart[] = [
                                'nivel'    => 'partida',
                                'clave'    => $claveP,
                                'concepto' => $part['nombre'],
                                'techo'    => 0,
                                'presup'   => $part['presupuestado'],
                            ];
                        }
                        $clgPresup += $cedPresup;
                        $bufferCed[] = [
                            'nivel'    => 'cedula',
                            'clave'    => 'Cédula',
                            'concepto' => $ced['nombre'],
                            'techo'    => $clgTecho,
                            'presup'   => $cedPresup,
                        ];
                        $bufferCed = array_merge($bufferCed, $bufferPart);
                    }
                }

                $clgNombre = $ter['clg'][$clgId]['nombre']
                    ?? $this->clgNombres[$clgId]
                    ?? ('CLG ' . $clgId);
                $bufferClg[] = [
                    'nivel'    => 'clg',
                    'clave'    => (string) $clgId,
                    'concepto' => $clgNombre,
                    'techo'    => $clgTecho,
                    'presup'   => $clgPresup,
                ];
                $bufferClg = array_merge($bufferClg, $bufferCed);

                $totTerTecho  += $clgTecho;
                $totTerPresup += $clgPresup;
            }

            $bufferTer[] = [
                'nivel'    => 'gf',
                'clave'    => $terId,
                'concepto' => $this->nombreGranFuente($terId, $ter['nombre'] ?? null),
                'techo'    => $totTerTecho,
                'presup'   => $totTerPresup,
            ];
            $bufferTer = array_merge($bufferTer, $bufferClg);

            $totalUrTecho  += $totTerTecho;
            $totalUrPresup += $totTerPresup;
        }

        $filas = [];
        $filas[] = [
            'nivel'    => 'ur',
            'clave'    => $this->ur_clave,
            'concepto' => $this->ur_nombre,
            'techo'    => $totalUrTecho,
            'presup'   => $totalUrPresup,
        ];
        $filas = array_merge($filas, $bufferTer);

        $sheet = $this->spreadsheet->getActiveSheet();
        foreach ($filas as $fila) {
            $this->pintaFila($sheet, $linea, $fila);
            $linea++;
        }
    }

    private function pintaFila($sheet, $linea, $fila)
    {
        $techo  = (float) $fila['techo'];
        $presup = (float) $fila['presup'];
        $dif    = $techo - $presup;
        $pct    = ($techo > 0) ? round(($presup / $techo) * 100, 2) : 0;

        $sheet->setCellValue(self::COL_CLAVE . $linea, $fila['clave']);
        $sheet->setCellValue(self::COL_CONC  . $linea, $fila['concepto']);
        $sheet->setCellValue(self::COL_PRES  . $linea, $presup);

        if ($fila['nivel'] === 'partida') {
            $sheet->setCellValue(self::COL_TECHO . $linea, '');
            $sheet->setCellValue(self::COL_DIF   . $linea, '');
            $sheet->setCellValue(self::COL_PCT   . $linea, '');
        } else {
            $sheet->setCellValue(self::COL_TECHO . $linea, $techo);
            $sheet->setCellValue(self::COL_DIF   . $linea, $dif);
            $sheet->setCellValue(self::COL_PCT   . $linea, $pct);
        }

        $rango = self::COL_FIRST . $linea . ':' . self::COL_LAST . $linea;
        $rangoNum = self::COL_TECHO . $linea . ':' . self::COL_DIF . $linea;
        $celdaPct = self::COL_PCT . $linea;

        $sheet->getStyle($rangoNum)->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
        $sheet->getStyle($celdaPct)->getNumberFormat()
            ->setFormatCode('0.00');
        $sheet->getStyle(self::COL_TECHO . $linea . ':' . self::COL_PCT . $linea)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $color   = self::C_WHITE;
        $fontCol = '000000';
        $bold    = false;
        $italic  = false;
        $hClave  = Alignment::HORIZONTAL_CENTER;
        $hConc   = Alignment::HORIZONTAL_LEFT;
        $indent  = 0;

        switch ($fila['nivel']) {
            case 'ur':
                $color   = self::C_UR;
                $fontCol = self::C_WHITE;
                $bold    = true;
                $hConc   = Alignment::HORIZONTAL_CENTER;
                break;
            case 'gf':
                $color   = self::C_GF;
                $fontCol = self::C_WHITE;
                $bold    = true;
                $hConc   = Alignment::HORIZONTAL_CENTER;
                break;
            case 'clg':
                $color   = self::C_CLG;
                $fontCol = self::C_WHITE;
                $bold    = true;
                $indent  = 1;
                break;
            case 'cedula':
                $color   = self::C_CED;
                $fontCol = '000000';
                $bold    = true;
                $italic  = true;
                $indent  = 2;
                break;
            case 'partida':
                $color   = self::C_WHITE;
                $fontCol = '000000';
                $indent  = 3;
                break;
        }

        $sheet->getStyle($rango)->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $color],
            ],
            'font' => [
                'bold'   => $bold,
                'italic' => $italic,
                'color'  => ['rgb' => $fontCol],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'BFBFBF'],
                ],
            ],
        ]);
        $sheet->getStyle(self::COL_CLAVE . $linea)
            ->getAlignment()->setHorizontal($hClave);
        $sheet->getStyle(self::COL_CONC . $linea)
            ->getAlignment()
            ->setHorizontal($hConc)
            ->setIndent($indent);
    }

    /* =====================================================================
        Catálogo local de Cédulas
    ===================================================================== */
    private static function nombreCedula($ccd_id)
    {
        switch ((int) $ccd_id) {
            case 1: return 'Alimentación';
            case 2: return 'Servicios personales';
            case 3: return 'Telefonía';
            case 4: return 'Vehículos';
            case 5: return 'Arrendamiento';
            case 6: return 'Seguros y Fianzas';
            case 7: return 'Servicio de Vigilancia';
            case 9: return 'Otras Partidas';
            default: return 'Sin Cédula';
        }
    }
}

new exp_ComparativoPresupuestario();
