<?php
session_start();

/* --------------------------------------------------------------------------------------------------------
Objetivo: REPORTE PARA LAS UR´S Y VALIDADORES
Elaboró : Zuli D. López Barbosa, Soledad López Velasco
-------------------------------------------------------------------------------------------------------- */

//inicio de sesion y llamada a las librerias

ini_set('memory_limit', '20M');

//librerias
//ULTIMA VERSION
require_once '../../../lib/reportetabla.1.8.php';
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../lib/WebResponse.php';
set_time_limit(0);

class reportetabla extends PDF
{

    public $gpo_id = '';
    public $ur_id = '';
    private $showFooter = true; // Variable para controlar si se muestra el footer o no
    public $activarEncabezado = false;
    public $imprimirEncabezadoTabla = false;
    public $mostrarEncabezadoTabla = false;  
    public $encabezadoImpreso = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function Footer()
    {
        $row = $this->getDatos();
        $tmpb = $this->GetBorders();
        $this->SetY(-10);

        $this->Cell(0, 5, utf8Decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
        $this->Cell(0, 5, date("d/m/y") . ' - ' . date("H:i:s") . '        ', 0, 0, 'R');
    }

    public function encabezados($ix, $ytmp)
    {
        $this->SetXY($ix + 17, $ytmp + 80);
        $ytmp = $this->GetY();
        $this->ln(23);
        $this->SetXY($ix + 17, $ytmp + 80);
        $ytmp = $this->GetY();
        $this->ln(23);
    }

    public function Header()
    { //Encabezado del documento
        if (!$this->activarEncabezado) {
            return;
        }
        $this->SetMargins(10, 10, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $row = $this->getDatos();
        $conexion2 = new AccesoDatos();
        $conexion2->conectar();
        $conexion2->setSql('
            select a.emi_id from cat_Mir a
            join vin_ProgramasMir USING(mir_id)
            join vin_UrProgramaMir USING(vpm_id,gpo_id,ur_id,prgs_id)
            WHERE gpo_id= ? AND ur_id=? AND prgs_id = ?;
        ');
        $parametros = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $_SESSION[_S]['clasAdm-prg']
        ];
        $conexion2->consultar($parametros);
        $conexion2->desconectar();
        $detalle = $conexion2->extraer();

        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 50);
        $this->SetTextColor(200, 200, 200);
        $this->RotatedText($ix + 30, $iy + 160, utf8Decode('EN REVISIÓN'), 45);
        $this->RotatedText($ix + 150, $iy + 160, utf8Decode('EN REVISIÓN'), 45);
        $this->RotatedText($ix, $iy + 80, utf8Decode('EN REVISIÓN'), 45);
        $this->RotatedText($ix + 150, $iy + 80, utf8Decode('EN REVISIÓN'), 45);

        $this->SetMargins(10, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->setRouteImg2(" ");
        $this->Image($this->getRouteImg(), $ix + 170, $iy + 5, '110', '15', 'JPG', '');
        $this->SetFont('UniviaPro-Bold', 'B', 12);
        $this->SetXY($ix + 17, $iy + 4);
        $ytmp = $this->GetY();

        $this->Cell(264, 5, utf8Decode(''), 0, 1, 'L');
        $this->Cell(264, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
        $this->Cell(264, 5, 'PROGRAMA OPERATIVO ANUAL '. APP_EJERCICIO, 0, 1, 'L');
        $this->Cell(264, 5, utf8Decode('MATRÍZ DE INDICADORES PARA RESULTADOS'), 0, 1, 'L');
        $this->Cell(264, 5, utf8Decode('REPORTE DE REVISIÓN MIR INICIAL'), 0, 1, 'L');
        $this->SetXY($ix + 17, $ytmp + 3.5);
        $ytmp = $this->GetY();
        $this->ln(23);

        $this->Cell(280, 5, '', 0, 1, 0);

        if ($this->PageNo() > 1 && $this->imprimirEncabezadoTabla) {
        if (!$this->encabezadoImpreso) return;

        $this->Ln(5);
        $this->imprimirEncabezadoTabla();
        }
        }

    public function imprimirEncabezadoTabla()
    {
        // Línea
        $this->setX(10);

        $this->SetFillColor(199, 209, 219);
        $this->setBorders('LTR');
        $this->setAltoLinea(2.5);

        // Primera fila
        $this->SetWidths(array(20, 35, 65, 80, 80));
        $this->SetAligns(array('C', 'C', 'C', 'C', 'C'));
        $this->SetFont('UniviaPro-Bold', 'B', 6);
        $this->Row(array('', '', 'INDICADOR', '', ''),2.5);

        $this->SetWidths(array(20, 35, 20, 23, 22, 80, 80));
        $this->SetAligns(array('C', 'C', 'L', 'L', 'L', 'C', 'C'));
        $this->SetArrayBorders(array('LR', 'LR', 'LT', 'T', 'RT', 'LR', 'LR'));

        $this->Row(array(
            'NIVEL',
            'RESUMEN NARRATIVO',
            utf8Decode("A)ID INDICADOR 1.NOMBRE\n4.DIMENSIÓN\n7.META ANUAL"),
            utf8Decode("2. FÓRMULA DE CÁLCULO\n5.FRECUENCIA\n"),
            "3. TIPO\n6.SENTIDO\n",
            utf8Decode("MEDIOS DE VERIFICACIÓN"),
            "SUPUESTOS"
        ));

        $this->SetBorders(1);
        $this->SetArrayBorders(null);
        $this->SetWidths(array(20, 35, 65, 80, 80));
        $this->SetAligns(array('C', 'L', 'L', 'L', 'L'));
        $this->SetRelleno("no");
    }

    public function imprimirEncabezadoTablaPrimeraVez()
{
    // Altura estimada
    $altura = 20;
    $y = $this->GetY();
    $limite = $this->GetPageHeight() - $this->bMargin;

    if ($y + $altura > $limite) {
        $this->AddPage();
    }

    $this->imprimirEncabezadoTabla();

    // Ya se imprimió por primera vez
    $this->encabezadoImpreso = true;

    // Activamos para que el Header lo imprima en páginas siguientes
    $this->mostrarEncabezadoTabla = true;
}
}

$pdf = new reportetabla('L', 'mm', 'Letter');
$pdf->SetProtection(array("print"), "", PDF_PASSWORD_OWNER_POA);
$pdf->setOrientation('L');
$pdf->AliasNbPages();
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->SetAutoPageBreak(true, 10);
$orden = $PantallMir = $filtro = '';
$pdf->setAltoLinea(1.5);
$pdf->activarEncabezado = true;
$pdf->AddPage();
$fill = false;

$pdf->ln();
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->setBorders(1);
$pdf->setAltoLinea(2.5);
$pdf->SetFillColor(199, 209, 219);
$pdf->SetWidths(array(280));
$pdf->SetAligns(array('C'));
$pdf->SetFont('UniviaPro-Bold', 'B', 7.5);
$pdf->Row(array('DATOS DEL PROGRAMA'));
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

/* PRIMER LINEA DEL REPORTE */
$pdf->SetFont('UniviaPro-Bold', 'B', 6);
$pdf->setX(10);

$c1 = new AccesoDatos();
$c1->setSql('
    select distinct prgs_id, prgs_nombre, ur_clave, ur_nombre from
    (select prgs_id,prgs_nombre,ur_clave,ur_nombre
    from vin_ProgramasMir
    join cat_Programas using(prgs_id)
    join cat_UnidadResponsable using(gpo_id, ur_id)
    where prgs_id = :prgs_id
    union all
    SELECT prgs_id,prgs_nombre,ur_clave,ur_nombre
    FROM enc_UrSubprogramas e
    JOIN vin_ProgramasMir using(prgs_id)
    JOIN cat_Programas USING(prgs_id)
    JOIN cat_UnidadResponsable c on c.gpo_id = e.gpo_id and c.ur_id = e.ur_id
    WHERE prgs_id = :prgs_id
    GROUP BY prgs_id,e.gpo_id,e.ur_id) a;
');
$parametros = [
    'prgs_id' => $_SESSION[_S]['clasAdm-prg']
];
$c1->conectar();
$c1->consultar($parametros);
$d1 = $c1->extraer();

$pdf->setBorders(1);
$pdf->SetWidths(array(280));
$pdf->SetAligns(array('C'));
$pdf->SetFont('UniviaPro-Bold', 'B', 7.5);
$pdf->Row(array('PROGRAMA ' . str_pad($d1['prgs_id'], 2, "0", STR_PAD_LEFT) . ' ' . utf8Decode($d1['prgs_nombre'])));
$pdf->SetRelleno("no");

$pdf->SetFont('UniviaPro-Regular', '', 6);

$conta = 1;
do {

    if ($conta % 2 == 1) {
        $pdf->Line(10, $pdf->GetY() - 5, 10, $pdf->GetY() + 5); // Línea vertical desde Y -5 a Y +5
        if ($conta == 1) {
            $pdf->Cell(18, 3, 'UR Responsable:', 'L', 0, "L");
        }

        $pdf->SetFont('UniviaPro-Regular', '', 6);

        if ($conta == 1) {
            $pdf->Cell(10, 3, $d1['ur_clave'], 0, 0, "R");
        } else {
            if ($conta == 3) {
                $pdf->Line(10, $pdf->GetY() - 5, 10, $pdf->GetY() + 5); // Línea vertical desde Y -5 a Y +5
                $pdf->Cell(20, 3, 'UR\'s Participantes: ', 'L', 0, 'C');
                $pdf->Cell(8, 3, $d1['ur_clave'], 0, 0, "R");
            } else {
                $pdf->Cell(28, 3, $d1['ur_clave'], 'L', 0, "R");
            }
        }

        $pdf->MultiCell(112, 3, utf8Decode($d1['ur_nombre']), 'R', 0, "L");
        $pdf->Line(150, $pdf->GetY() - 10, 150, $pdf->GetY() + 10); // Línea vertical desde Y -5 a Y +5
        $pdf->Line(290, $pdf->GetY() - 5, 290, $pdf->GetY() + 5); // Línea vertical desde Y -5 a Y +5

        if ($c1->getNumeroRegistros() == $conta) {
            $pdf->Line(10, $pdf->GetY() - 5, 10, $pdf->GetY() + 5); // Línea vertical desde Y -5 a Y +5
            $pdf->setX(10);
            $pdf->Cell(140, 8, '', 'LR', 0, 0);
            $pdf->Cell(140, 8, '', 'R', 1, 0);
        }
    } elseif ($conta % 2 == 0) {
        $pdf->Line(10, $pdf->GetY() - 5, 10, $pdf->GetY() + 5); // Línea vertical desde Y -5 a Y +5
        $pdf->setX(150);
        $pdf->SetFont('UniviaPro-Regular', '', 6);

        if ($conta == 2) {
            $pdf->Line(10, $pdf->GetY() - 5, 10, $pdf->GetY() + 5); // Línea vertical desde Y -5 a Y +5
            $pdf->Cell(20, 3, 'UR\'s Participantes: ', 'L', 0, 'C');
            $pdf->Cell(8, 3, $d1['ur_clave'], 0, 0, "R");
        } else {
            $pdf->Cell(28, 3, $d1['ur_clave'], 'L', 0, "R");
        }

        $pdf->MultiCell(112, 3, utf8Decode($d1['ur_nombre']), 'R', 0, "L");
        $pdf->Line(290, $pdf->GetY() - 5, 290, $pdf->GetY() + 5); // Línea vertical desde Y -5 a Y +5
    }
    if ($c1->getNumeroRegistros() == $conta && $c1->getNumeroRegistros() % 2 == 1) {
        $pdf->Line(10, $pdf->GetY() - 5, 10, $pdf->GetY() + 5); // Línea vertical desde Y -5 a Y +5
        $pdf->Line(290, $pdf->GetY() - 5, 290, $pdf->GetY() + 5); // Línea vertical desde Y -5 a Y +5
    }

    $conta++;
} while ($d1 = $c1->extraer());
$c1->desconectar();

/* SEGUNDA LINEA DEL REPORTE */
$pdf->SetRelleno("si");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(199, 209, 219);
$pdf->setBorders(1);
$pdf->setAltoLinea(2.5);
$pdf->SetWidths(array(280));
$pdf->SetAligns(array('C'));
$pdf->SetFont('UniviaPro-Bold', 'B', 7);
$pdf->Row(array(utf8Decode('ALINEACIÓN')));
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

$pdf->SetFont('UniviaPro-Bold', 'B', 6);
$pdf->setX(10);
$pdf->Cell(280, 4, 'PLAN ESTATAL DE DESARROLLO', 'LR', 2, 'L', $fill);

$pdf->SetRelleno("no");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(199, 209, 219);
$pdf->setBorders('LR');
$pdf->setAltoLinea(4);
$pdf->SetWidths(array(7, 83, 9, 81, 14, 86));
$pdf->SetAligns(array('L', 'L', 'L', 'L', 'L', 'L'));

$f = new stdClass();
$f->font = 'UniviaPro-Bold';
$f->style = 'B';
$f->size = '6';
$fuentes = array($f);
$f = new stdClass();
$f->font = 'UniviaPro-Regular';
$f->style = '';
$f->size = '6';
array_push($fuentes, $f);

$f = new stdClass();
$f->font = 'UniviaPro-Bold';
$f->style = 'B';
$f->size = '6';
array_push($fuentes, $f);

$f = new stdClass();
$f->font = 'UniviaPro-Regular';
$f->style = '';
$f->size = '6';
array_push($fuentes, $f);

$f = new stdClass();
$f->font = 'UniviaPro-Bold';
$f->style = 'B';
$f->size = '6';
array_push($fuentes, $f);

$f = new stdClass();
$f->font = 'UniviaPro-Regular';
$f->style = '';
$f->size = '6';
array_push($fuentes, $f);

$c1 = new AccesoDatos();
$c1->setSql('
    SELECT eje_nombre,obj_nombre,tem_nombre,prgs_nombre,spe_nombre, pb.pbf_nombre, prgs_hombres, prgs_mujeres, prgs_total,
    SUM(prgs_hombresO) AS total_hombres, pbO.pbf_nombre AS pbf_nombreO, SUM(prgs_mujeresO) AS total_mujeres, SUM(prgs_totalO) AS total_sprs,
    prgs_MedioVerificacion
    FROM vin_Programa vp
    INNER JOIN cat_Programas p USING(prgs_id)
    INNER JOIN cat_PoblacionBeneficiada pb USING(pbf_id)
    INNER JOIN cat_PoblacionBeneficiada AS pbO ON pbO.pbf_id = p.pbf_idO
    INNER JOIN enc_PedObjetivo e USING(epo_id)
    INNER JOIN cat_Eje USING(eje_id)
    INNER JOIN cat_Tema t USING(eje_id, tem_id)
    INNER JOIN cat_Objetivo o ON o.eje_id=e.eje_id AND o.tem_id=t.tem_id AND o.obj_id=e.obj_id
    INNER JOIN cat_SectorPes s ON s.spe_id=vp.spe_id
    WHERE prgs_id = ?;
');
$c1->conectar();
$c1->consultar([$_SESSION[_S]['clasAdm-prg']]);
$d1 = $c1->extraer();
$c1->desconectar();

$pdf->setFuentes($fuentes);
$pdf->Row(array('EJE:', strtoupper(utf8Decode($d1['eje_nombre'])), 'TEMA:', strtoupper(utf8Decode($d1['tem_nombre'])), 'OBJETIVO:', strtoupper(utf8Decode($d1['obj_nombre']))), 'LR');
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

/* FINALIDAD Y PLAN ESTRATEGICO */

$pdf->SetRelleno("no");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(199, 209, 219);
$pdf->setBorders('LR');
$pdf->setAltoLinea(2.5);
$pdf->SetWidths(array(30, 105, 40, 105));
$pdf->SetAligns(array('L', 'L', 'L', 'L'));

$f = new stdClass();
$f->font = 'UniviaPro-Bold';
$f->style = 'B';
$f->size = '6';
$fuentes = array($f);

$f = new stdClass();
$f->font = 'UniviaPro-Regular';
$f->style = '';
$f->size = '6';
array_push($fuentes, $f);

$f = new stdClass();
$f->font = 'UniviaPro-Bold';
$f->style = 'B';
$f->size = '6';
array_push($fuentes, $f);

$f = new stdClass();
$f->font = 'UniviaPro-Regular';
$f->style = '';
$f->size = '6';
array_push($fuentes, $f);

$pdf->setFuentes($fuentes);
$pdf->Row(array('SECTOR:', strtoupper(utf8Decode($d1['spe_nombre']))), 'LR');
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

$pdf->SetRelleno("si");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(199, 209, 219);
$pdf->setBorders(1);
$pdf->setAltoLinea(2.5);

$pdf->SetWidths(array(280));
$pdf->SetAligns(array('C'));
$pdf->SetFont('UniviaPro-Bold', 'B', 7);
$pdf->Row(array(utf8Decode('POBLACIÓN O ÁREA DE ENFOQUE')));
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

$pdf->SetFillColor(199, 209, 219);
$pdf->setBorders('LR');
$pdf->setAltoLinea(2.5);
$pdf->SetWidths(array(56, 62, 27, 27, 27, 27, 27, 27));
$pdf->SetAligns(array('L', 'L', 'L', 'L', 'L', 'L', 'L', 'L'));

$pdf->setFuentes($fuentes);
$pdf->Row(array(utf8Decode('Atributo de la población o área de enfoque potencial:'), strtoupper(utf8Decode($d1['pbf_nombre'])), utf8Decode('Hombres:'), utf8Decode($d1['prgs_hombres']), utf8Decode('Mujeres:'), utf8Decode($d1['prgs_mujeres']), utf8Decode('Total:'), utf8Decode($d1['prgs_total'])), 'LR');
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

$pdf->setFuentes($fuentes);
$pdf->Row(array(utf8Decode('Atributo de la población o área de enfoque objetivo:'), strtoupper(utf8Decode($d1['pbf_nombreO'])), utf8Decode('Hombres:'), utf8Decode($d1['total_hombres']), utf8Decode('Mujeres:'), utf8Decode($d1['total_mujeres']), utf8Decode('Total:'), utf8Decode($d1['total_sprs'])), 'LR');
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

$pdf->SetRelleno("no");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(199, 209, 219);
$pdf->SetWidths(array(30,250));
$pdf->SetAligns(array('L','L'));
$pdf->setFuentes($fuentes);
$pdf->Row(array(utf8Decode('Medio de verificación:'),utf8Decode($d1['prgs_MedioVerificacion'])),'LR');
$pdf->SetRelleno("no");

/*  DETALLE DEL REPORTE */

$pdf->SetRelleno("si");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(199, 209, 219);
$pdf->setBorders(1);
$pdf->setAltoLinea(2.5);
$pdf->SetWidths(array(280));
$pdf->SetAligns(array('C'));
$pdf->SetFont('UniviaPro-Bold', 'B', 7);
$pdf->Row(array('INDICADORES PARA RESULTADOS'));
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

$pdf->imprimirEncabezadoTabla = true;
$pdf->imprimirEncabezadoTablaPrimeraVez();

/* DETALLE DEL PRIMER NIVEL FIN */
$c1 = new AccesoDatos();
$c1->setSql('
    SELECT
        ifNull(ind_fin,0) ind_fin, ifNull(ind_id,0) ind_id,ifnull(fin_formulaCalculo,\'-\') fin_formulaCalculo,
        ifNull(tpi_fin,0) tpi_fin, ifNull(dim_fin,0) dim_fin, ifNull(frm_fin,0) frm_fin, ifNull(ses_fin,0) ses_fin,
        IF(ISNULL(mtf_original),"",mtf_original) AS mem_original,mir_resumenNarrativo,
        CONCAT(ifNull(mediosFin,"")) mediosFin, CONCAT(ifNull(SupuestosFin,"")) SupuestosFin, obj_nombre AS fin
    FROM cat_Mir
    INNER JOIN vin_ProgramasMir USING(mir_id)
    INNER JOIN vin_Programa using(prgs_id)
    INNER JOIN enc_PedObjetivo USING (epo_id)
    INNER JOIN cat_Objetivo USING(eje_id, tem_id, obj_id)
    LEFT JOIN(
        SELECT vpm_id, pmi_id, nim_id, ind_id, ind_nombreMIR AS ind_fin, ind_formulaCalculo AS fin_formulaCalculo,
        tpi_nombre tpi_fin,dim_nombre dim_fin,frm_nombre frm_fin,ses_nombre ses_fin,
        mtf_id, mtf_original
        FROM vin_ProgramaMirIndicador
        LEFT JOIN cat_MetaFin USING(pmi_id)
        INNER JOIN cat_Indicador USING(nim_id,ind_id)
        INNER JOIN cat_TipoIndicador USING(tpi_id)
        INNER JOIN cat_Dimension USING(dim_id)
        INNER JOIN cat_FrecuenciaMedicion USING(frm_id)
        INNER JOIN cat_SentidoEsperado USING(ses_id)
        WHERE nim_id=1 AND vin_ProgramaMirIndicador.sts_id=1
        )s USING(vpm_id)
    LEFT JOIN (
        SELECT GROUP_CONCAT(spm_nombre, "\n" SEPARATOR "|") AS SupuestosFin, vpm_id, nim_id
        from cat_SupuestoProgramaMir
        WHERE nim_id = 1 AND spm_trimestreCierre = :tri_id GROUP BY vpm_id
    )SupFin USING(vpm_id,nim_id)
    LEFT JOIN (
        SELECT GROUP_CONCAT(mvpm_nombre, "\n" SEPARATOR "|") AS mediosFin ,vpm_id, nim_id
        FROM cat_MedioVerificacionProgramaMir
        WHERE nim_id = 1 AND mvpm_trimestreCierre = :tri_id GROUP BY vpm_id
    )MevFin USING(vpm_id,nim_id)
    WHERE prgs_id= :prg_id
    ;
');
$parametros = [
    ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
    ':tri_id' => $_POST['eri_trimestre']
];
$c1->conectar();
$c1->consultar($parametros);

$pdf->setAltoLinea(2.5);
$pdf->CheckPageBreak(10);
$pdf->setTopAlign(true);
$pdf->SetAligns(array('C', 'L', 'L', 'L', 'L'));

$pdf->SetRelleno("no");
$pdf->CheckPageBreak(10);
$pdf->SetFillColor(199, 209, 219);
$pdf->setBorders(1);
$pdf->setAltoLinea(2.5);

$pdf->SetWidths(array(20, 35, 65, 80, 80));
$pdf->SetAligns(array('C', 'L', 'L', 'L', 'L'));
$pdf->SetFont('UniviaPro-Regular', '', 6);
$uno = $dos = $tres = $cuatro = $cinco = $seis = $siete = $indFin = $ind = '';
$Fin = $mediosFin = $supFin = '';

while ($row = $c1->extraer()) {

    if ($row['ind_fin'] != '') {
        $uno = '1 - ';
    } else {
        $uno = '';
    }
    if ($row['fin_formulaCalculo'] != '') {
        $dos = '2 - ';
    } else {
        $dos = '';
    }
    if ($row['tpi_fin'] != '') {
        $tres = '3 - ';
    } else {
        $tres = '';
    }
    if ($row['dim_fin'] != '') {
        $cuatro = '4 - ';
    } else {
        $cuatro = '';
    }
    if ($row['frm_fin'] != '') {
        $cinco = '5 - ';
    } else {
        $cinco = '';
    }
    if ($row['ses_fin'] != '') {
        $seis = '6 - ';
    } else {
        $seis = '';
    }
    #consultar metas
    if ($row['mem_original'] != '') {
        $siete = '7 - ';
    } else {
        $siete = '';
    }

    if ($indFin != $row['ind_id']) {

        $ind .= '
            A) INDICADOR ' . $row['ind_id'] . ' NIVEL 1
            ' . $uno . strtoupper(($row['ind_fin'])) . '
            ' . $dos . (strtoupper($row['fin_formulaCalculo'])) . '
            ' . $tres . ($row['tpi_fin']) . '
            ' . $cuatro . ($row['dim_fin']) . '
            ' . $cinco . ($row['frm_fin']) . '
            ' . $seis . ($row['ses_fin']) . '
            ' . $siete . ($row['mem_original']);

        $Fin = $row['mir_resumenNarrativo'];
        $mediosFin = str_replace('|', "\n" . "\n", $row['mediosFin']);
        $supFin = str_replace('|', "\n" . "\n", $row['SupuestosFin']);

        $indFin = $row['ind_id'];
    }
}
$c1->desconectar();

$pdf->Row2(array(utf8Encode('FIN'), utf8Decode(mb_strtoupper($Fin)), utf8Decode($ind), utf8Decode(mb_strtoupper($mediosFin)), utf8Decode(mb_strtoupper($supFin))));
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

$pdf->SetRelleno("no");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(199, 209, 219);
$pdf->setBorders(1);
$pdf->setAltoLinea(2.5);
/* FIN DEL DETALLE NIVEL FIN */

/* DETALLE DE NIVEL PROPOSITO */
$p1 = new AccesoDatos();
$p1->setSql('
    SELECT IFNULL(MAX(vpm_id),0) vpm_id, IFNULL(MAX(ind_pro),"") ind_pro, IFNULL(ind_id,0) ind_id,
    IFNULL(MAX(pro_formulaCalculo),"") pro_formulaCalculo, IFNULL(MAX(tpi_pro),"") tpi_pro,
    IFNULL(MAX(dim_pro),"") dim_pro, IFNULL(MAX(frm_pro),"") frm_pro, IFNULL(MAX(ses_pro),"") ses_pro,
    0 lac_id, IFNULL(MAX(vpm_resumenNarrativo),"") proposito,
    IFNULL(MAX(mediosPro),"") mediosPro, IFNULL(MAX(mdv_id),"") mdv_id, IFNULL(MAX(SupuestosPro),"") SupuestosPro,
    IFNULL(MAX(sup_id),0) sup_id, IFNULL(MAX(mpr_original),"") mpr_original, IFNULL(MAX(mpr_anteriorIndicador),"") mpr_anteriorIndicador,
    IFNULL(MAX(umi_id),0) umi_id
    FROM cat_Mir
    INNER JOIN vin_ProgramasMir USING(mir_id)
    LEFT JOIN(
        SELECT vpm_id, pmi_id, nim_id, ind_id, ind_nombreMIR AS ind_pro, ind_formulaCalculo AS pro_formulaCalculo,
            tpi_nombre tpi_pro,dim_nombre dim_pro,frm_nombre frm_pro,ses_nombre ses_pro, umi_id,
            mpr_id, mpr_original, mpr_anteriorIndicador
        FROM vin_ProgramaMirIndicador
        INNER JOIN cat_Indicador USING(nim_id,ind_id)
        JOIN cat_TipoIndicador USING(tpi_id)
        JOIN cat_Dimension USING(dim_id)
        JOIN cat_FrecuenciaMedicion USING(frm_id)
        JOIN cat_SentidoEsperado USING(ses_id)
        LEFT JOIN cat_MetaProposito USING(pmi_id)
        WHERE nim_id=2 AND vin_ProgramaMirIndicador.sts_id=1
    )s USING(vpm_id)
    LEFT JOIN (
        SELECT GROUP_CONCAT(spm_nombre, "\n" SEPARATOR "|") AS SupuestosPro, MAX(spm_id) AS sup_id, vpm_id, nim_id
        FROM cat_SupuestoProgramaMir
        WHERE nim_id = 2 AND spm_trimestreCierre = :tri_id
        GROUP BY vpm_id
    )SupFin USING(vpm_id,nim_id)
    LEFT JOIN (
        SELECT GROUP_CONCAT(mvpm_nombre, "\n" SEPARATOR "|") AS mediosPro, MAX(mvpm_id) AS mdv_id, vpm_id, nim_id
        FROM cat_MedioVerificacionProgramaMir
        WHERE nim_id = 2 AND mvpm_trimestreCierre = :tri_id
        GROUP BY vpm_id
    )MevFin USING(vpm_id,nim_id)
    WHERE prgs_id=:prg_id
    GROUP BY  gpo_id, ur_id, prgs_id,ind_id;

');
$parametros = [
    ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
    ':tri_id' => $_POST['eri_trimestre']
];
$p1->conectar();
$p1->consultar($parametros);
$ind_id = '';
$nombrepro = '';
$medios = '';
$supuestos = '';
$ind = "";
$med = "";
$sup = "";
$tex = "";
$prop = "";
$valorMeta = '';
while ($rowP = $p1->extraer()) {

    $pdf->SetWidths(array(20, 35, 65, 80, 80));
    $pdf->SetAligns(array('C', 'L', 'L', 'L', 'L'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $uno = $dos = $tres = $cuatro = $cinco = $seis = $siete = '';

    if ($rowP['ind_pro'] != '') {
        $uno = '1 - ';
    } else {
        $uno = '';
    }
    if ($rowP['pro_formulaCalculo'] != '') {
        $dos = '2 - ';
    } else {
        $dos = '';
    }
    if ($rowP['tpi_pro'] != '') {
        $tres = '3 - ';
    } else {
        $tres = '';
    }
    if ($rowP['dim_pro'] != '') {
        $cuatro = '4 - ';
    } else {
        $cuatro = '';
    }
    if ($rowP['frm_pro'] != '') {
        $cinco = '5 - ';
    } else {
        $cinco = '';
    }
    if ($rowP['ses_pro'] != '') {
        $seis = '6 - ';
    } else {
        $seis = '';
    }
    #consultar metas

    if ($nombrepro != $rowP['vpm_id']) {
        $tex .= utf8Decode('PROPÓSITO');
        $prop .= utf8Decode(mb_strtoupper($rowP['proposito']));
        $nombrepro = $rowP['vpm_id'];
    }

    if ($ind_id != $rowP['ind_id']) {

        switch ($rowP['umi_id']) {
            case 2:case 3:case 5:case 4:
                $valorMeta = $rowP['mpr_anteriorIndicador'];
                break;
            default:
                $valorMeta = $rowP['mpr_original'];
                break;
        }

        if ($valorMeta != '') {
            $siete = '7 - ';
        } else {
            $siete = '';
        }

        $ind .= '
            A) INDICADOR ' . $rowP['ind_id'] . ' NIVEL 2
            ' . $uno . $rowP['ind_pro'] . '
            ' . $dos . strtoupper(($rowP['pro_formulaCalculo'])) . '
            ' . $tres . $rowP['tpi_pro'] . '
            ' . $cuatro . $rowP['dim_pro'] . '
            ' . $cinco . $rowP['frm_pro'] . '
            ' . $seis . $rowP['ses_pro'] . '
            ' . $siete . $valorMeta . '
            ' . ' ';
        $ind_id = $rowP['ind_id'];
    }

    if ($medios != $rowP['mdv_id']) {
        $med .= str_replace('|', "\n" . "\n", $rowP['mediosPro']);
        $medios = $rowP['mdv_id'];
    }
    if ($supuestos != $rowP['sup_id']) {
        $sup .= str_replace('|', "\n" . "\n", $rowP['SupuestosPro']);
        $supuestos = $rowP['sup_id'];
    }

    $pdf->SetRelleno("no");
    $pdf->setBorders(1);
    $pdf->SetFillColor(255, 255, 255);
}
$pdf->Row2(array($tex, $prop, utf8Decode($ind), utf8Decode($med), utf8Decode($sup)));
$p1->desconectar();
/* FIN DETALLE DE NIVEL PROPOSITO */

/* DETALLE DEL REPORTE A NIVEL COMPONENTE Y ACTIVIDAD */
$texTotal = '';
$sql = "
    SELECT sprs_id, pdf_id, pdf_mir AS componente, epfi_id AS ipf_id, ind_id,
        GROUP_CONCAT('UR ', m.gpo_id, m.ur_id,' ---- ', m.mco_original,'
        ' ORDER BY m.gpo_id, m.ur_id SEPARATOR '|') AS urs, m.mco_original,
        ind_nombreMIR, ind_formulaCalculo,tpi_id,tpi_nombre, dim_id, dim_nombre, frm_id,
        frm_nombre, ses_id, ses_nombre, MAX(mco_descripcion) AS mem_descripcion, ind_nombre, tiv_id, umi_id
    FROM  cat_Subprogramas
        INNER JOIN cat_ProductoFinal USING (pdf_id)
        INNER JOIN enc_ProductoFinalIndicador e USING (pdf_id)
        LEFT JOIN cat_MetaComponente m USING (prgs_id, sprs_id, epfi_id)
        LEFT JOIN cat_Indicador USING(nim_id,ind_id)
        LEFT JOIN cat_TipoIndicador USING(tpi_id)
        LEFT JOIN cat_Dimension USING(dim_id)
        LEFT JOIN cat_FrecuenciaMedicion USING(frm_id)
        LEFT JOIN cat_SentidoEsperado USING(ses_id)
        INNER JOIN cat_TipoValor USING(tiv_id)
    WHERE prgs_id = ? AND (esr_id=? OR sprs_id=?) AND pdf_id!=? AND e.sts_id=? AND mco_original != 0
    GROUP BY prgs_id,sprs_id,pdf_id,epfi_id,ind_id
    ORDER BY epfi_orden, sprs_id, pdf_id, ind_id;
";
$parametros = [
    $_SESSION[_S]['clasAdm-prg'],
    4,
    0,
    0,
    1
];
$comMeta = new AccesoDatos();
$comMeta->setSql($sql);
$comMeta->conectar();
$comMeta->consultar($parametros);

$comMeta->desconectar();

$prodFinal = '';

$numIndicadoresCom = 1;
$contInd = 1;

while ($rowC = $comMeta->extraer()) {

    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);
    $pdf->SetFillColor(199, 209, 219);
    $pdf->setBorders(1);
    $pdf->setAltoLinea(2.5);
    //Denominación, Método de cálculo,tipo-dimensión-frecuencia-sentido-meta anual
    $pdf->SetWidths(array(25, 56, 87, 56, 56));
    $pdf->SetAligns(array('C', 'L', 'L', 'L', 'L'));

    $pdf_id = $rowC['pdf_id'];

    $numComponente = '';
    $Componente = '';
    $supuestoUR = '';
    $mediosVerUR = '';
    $texto = '';
    $ind = '';

    $uno = $dos = $tres = $cuatro = $cinco = $seis = $siete = '';

    if ($rowC['ind_nombreMIR'] != '') {
        $uno = '1 - ';
    } else {
        $uno = '';
    }
    if ($rowC['ind_formulaCalculo'] != '') {
        $dos = '2 - ';
    } else {
        $dos = '';
    }
    if ($rowC['tpi_id'] != '') {
        $tres = '3 - ';
    } else {
        $tres = '';
    }
    if ($rowC['dim_id'] != '') {
        $cuatro = '4 - ';
    } else {
        $cuatro = '';
    }
    if ($rowC['frm_id'] != '') {
        $cinco = '5 - ';
    } else {
        $cinco = '';
    }
    if ($rowC['ses_id'] != '') {
        $seis = '6 - ';
    } else {
        $seis = '';
    }
    if ($rowC['ind_nombre'] != '') {
        $texto = 'VARIABLES DEL INDICADOR
    7 - ' . $rowC['ind_nombre'] . '
    ';
    } else {
        $texto = '';
    }

    $ind = '
        A) INDICADOR ' . $rowC['ind_id'] . ' NIVEL 3
        ' . $uno . $rowC['ind_nombreMIR'] . '
        ' . $dos . strtoupper($rowC['ind_formulaCalculo']) . '
        ' . $tres . $rowC['tpi_nombre'] . '
        ' . $cuatro . $rowC['dim_nombre'] . '
        ' . $cinco . $rowC['frm_nombre'] . '
        ' . $seis . $rowC['ses_nombre'] . '
        ' . $texto;

    if ($prodFinal != $rowC['pdf_id']) {

        $supCom = new AccesoDatos();
        $supCom->setSql('
            SELECT *
            FROM cat_SupuestoComponente s
            WHERE prgs_id = ? AND sprs_id = ?
            AND (spc_id IS NOT NULL) AND spc_trimestreCierre = ?
            ORDER BY gpo_id, ur_id;
        ');
        $parametros = [
            $_SESSION[_S]['clasAdm-prg'],
            $rowC['sprs_id'],
            $_POST['eri_trimestre']
        ];
        $supCom->conectar();
        $supCom->consultar($parametros);
        $supCom->desconectar();

        $numComponente = 'COMPONENTE ';
        $Componente = $rowC['componente'];

        while ($supMediosC = $supCom->extraer()) {
            $supuestoUR .= "UR " . $supMediosC['gpo_id'] . $supMediosC['ur_id'] . ": " . $supMediosC['spc_nombre'] . "\n" . "\n";
        }

        $supMeCom = new AccesoDatos();
        $supMeCom->setSql('
            SELECT *
            FROM cat_MedioVerificacionComponente m
            WHERE prgs_id = :prg_id AND sprs_id = :spr_id
            AND (mvcm_id IS NOT NULL) AND mvcm_trimestreCierre = :tri_id
            ORDER BY gpo_id, ur_id;
        ');
        $parametros = [
            ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
            ':spr_id' => $rowC['sprs_id'],
            ':tri_id' => $_POST['eri_trimestre']
        ];
        $supMeCom->conectar();
        $supMeCom->consultar($parametros);
        $supMeCom->desconectar();

        while ($MediosC = $supMeCom->extraer()) {
            $mediosVerUR .= "UR " . $MediosC['gpo_id']. $MediosC['ur_id'] .": " . $MediosC['mvcm_nombre']. "\n" . "\n";
        }

        $prodFinal = $rowC['pdf_id'];

        $contInd = 1;
        $indicadorCom = new AccesoDatos();
        $indicadorCom->conectar();
        $indicadorCom->setSql('
            SELECT COUNT(*) AS numInd FROM enc_ProductoFinalIndicador
            WHERE pdf_id = ?
            AND sts_id = ?;
        ');
        $parametros = [
            $prodFinal,
            1
        ];
        $indicadorCom->consultar($parametros);
        $datos = $indicadorCom->extraer();
        $numIndicadoresCom = $datos['numInd'];
        $indicadorCom->desconectar();
    }

        # code...
        $pdf->SetWidths(array(20, 35, 65, 80, 80));
        $pdf->SetAligns(array('C', 'L', 'L', 'L', 'L'));
        $pdf->Row2(array($numComponente, utf8Decode($Componente), utf8Decode($ind) . str_replace('|', '', $rowC['urs']), utf8Decode($mediosVerUR), utf8Decode($supuestoUR)));


    $pdf->SetRelleno("no");
    $pdf->setBorders(0);
    $pdf->SetFillColor(255, 255, 255);

    if ($contInd == $numIndicadoresCom) {
        $metaAct = new AccesoDatos();
        $metaAct->setSql('
            SELECT pdf_id, pdi_id, pdi_mir, ind_id, ind_nombre,MAX( macm_descripcion), ind_nombreMIR, ind_definicion,
                ind_formulaCalculo, tpi_id, tpi_nombre, dim_id, dim_nombre, frm_id, frm_nombre, ses_id, ses_nombre,
                GROUP_CONCAT("UR ", gpo_id, ur_id, " ---- ", macm_original, "\n" ORDER BY gpo_id, ur_id SEPARATOR "|") AS urs,
                CONCAT("TOTAL ---- ", SUM(macm_original), "\n") AS total
            FROM cat_ProductoIntermedio
            INNER JOIN vin_ProductoIntermedioIndicador v USING(pdi_id)
            INNER JOIN vin_ProductoFinalProductoIntermedio vpfi USING(pdf_id,pdi_id)
            LEFT JOIN (
                SELECT pii_id, gpo_id, ur_id, macm_original, macm_descripcion
                FROM cat_MetaActividadMir
                GROUP BY  gpo_id, ur_id,  pii_id,macm_id
            )m USING(pii_id)
            LEFT JOIN cat_Indicador USING(nim_id,ind_id)
            LEFT JOIN cat_TipoIndicador USING(tpi_id)
            LEFT JOIN cat_Dimension USING(dim_id)
            LEFT JOIN cat_FrecuenciaMedicion USING(frm_id)
            LEFT JOIN cat_SentidoEsperado USING(ses_id)
            WHERE pdf_id = ? AND v.sts_id = ?
            AND vpfi.sts_id= ? AND macm_original != 0
            GROUP BY pdi_id,pii_id, ind_id
            ORDER BY pii_orden, pdf_id, pdi_id, ind_id;
        ');
        $parametros = [
            $pdf_id,
            1,
            1
        ];
        $metaAct->conectar();
        $metaAct->consultar($parametros);
        $metaAct->desconectar();

        $contadorAct = 1;
        $ind2 = '';
        $texTotalA = '';
        $totalActividad = 0;
        $mediosVerActUR = $supuestoActUR = '';
        $pdi_id = '';
        $vum_id = '';
        $VaribleIndicador = '';

        while ($Actividad = $metaAct->extraer()) {
            if ($Actividad['total'] != NULL) {
                $pdf->SetRelleno("no");
                $pdf->CheckPageBreak(10);
                $pdf->setX(10);
                $pdf->SetFillColor(199, 209, 219);
                $pdf->setBorders(1);
                $pdf->setAltoLinea(2.5);
                //Denominación, Método de cálculo,tipo-dimensión-frecuencia-sentido-meta anual

                $pdf->SetWidths(array(20, 35, 65, 80, 80));
                $pdf->SetAligns(array('C', 'L', 'L', 'L', 'L'));
                $pdf->SetFont('UniviaPro-Regular', '', 6);

                $ind2 = '';
                $texto2 = '';
                $uno = $dos = $tres = $cuatro = $cinco = $seis = $siete = '';

                if ($Actividad['ind_nombreMIR'] != '') {
                    $uno = '1 - ';
                } else {
                    $uno = '';
                }
                if ($Actividad['ind_formulaCalculo'] != '') {
                    $dos = '2 - ';
                } else {
                    $dos = '';
                }
                if ($Actividad['tpi_id'] != '') {
                    $tres = '3 - ';
                } else {
                    $tres = '';
                }
                if ($Actividad['dim_id'] != '') {
                    $cuatro = '4 - ';
                } else {
                    $cuatro = '';
                }
                if ($Actividad['frm_id'] != '') {
                    $cinco = '5 - ';
                } else {
                    $cinco = '';
                }
                if ($Actividad['ses_id'] != '') {
                    $seis = '6 - ';
                } else {
                    $seis = '';
                }
                if ($Actividad['ind_definicion'] != '') {
                    $texto2 = 'VARIABLES DEL INDICADOR
                7 - ' . $Actividad['ind_definicion'] . '
                ';
                } else {
                    $texto2 = '';
                }

                $supAct = new AccesoDatos();
                $supAct->setSql('
                    SELECT *
                    FROM cat_SupuestoActividad s
                    WHERE pdf_id = ? AND pdi_id = ? AND spa_trimestreCierre = ?
                    ORDER BY gpo_id, ur_id;
                ');
                $parametros = [
                    $pdf_id,
                    $Actividad['pdi_id'],
                    $_POST['eri_trimestre']
                ];
                $supAct->conectar();
                $supAct->consultar($parametros);
                $supAct->desconectar();

                $ind2 = '
                    A) INDICADOR ' . $Actividad['ind_id'] . ' NIVEL 4
                    ' . $uno . $Actividad['ind_nombreMIR'] . '
                    ' . $dos . strtoupper($Actividad['ind_formulaCalculo']) . '
                    ' . $tres . $Actividad['tpi_nombre'] . '
                    ' . $cuatro . $Actividad['dim_nombre'] . '
                    ' . $cinco . $Actividad['frm_nombre'] . '
                    ' . $seis . $Actividad['ses_nombre'] . '
                    ' . $texto2;

                $supMeAct = new AccesoDatos();
                $supMeAct->setSql('
                    SELECT *
                    FROM cat_MedioVerificacionActividad m
                    WHERE pdf_id = ? AND pdi_id = ? AND mvac_trimestreCierre = ?
                    ORDER BY gpo_id, ur_id;
                ');
                $parametros = [
                    $pdf_id,
                    $Actividad['pdi_id'],
                    $_POST['eri_trimestre']
                ];
                $supMeAct->conectar();
                $supMeAct->consultar($parametros);
                $supMeAct->desconectar();

                $textoActividad = '';
                $NombreActividad = '';
                $supuestoURAct = '';
                $mediosVerURAct = '';

                if ($pdi_id != $Actividad['pdi_id']) {

                    $textoActividad = 'ACTIVIDAD ' . $contadorAct . '
                    ID INTERMEDIO ' . $Actividad['pdi_id'];

                    $NombreActividad = utf8Decode($Actividad['pdi_mir']);

                    while ($supMediosA = $supAct->extraer()) {
                        $supuestoURAct .= "UR " . $supMediosA['gpo_id'] . $supMediosA['ur_id'] .": " . $supMediosA['spa_nombre'] . "\n" . "\n";
                    }

                    while ($supA = $supMeAct->extraer()) {
                        $mediosVerURAct .= "UR " . $supA['gpo_id'] . $supA['ur_id'] . ": " . $supA['mvac_nombre'] . "\n" . "\n";
                    }

                    $pdi_id = $Actividad['pdi_id'];
                    $contadorAct++;
                }

                $pdf->Row2(array($textoActividad, $NombreActividad, utf8Decode($ind2) . str_replace('|', '', $Actividad['urs']) . $Actividad['total'], utf8Decode($mediosVerURAct), utf8Decode($supuestoURAct)));
            }
        }
    }

    $pdf->SetRelleno("no");
    //$pdf->setBorders(0);
    $pdf->SetFillColor(255, 255, 255);

    $contInd++;

    /* FIN DETALLE DEl REPORTE A NIVEL ACTIVIDAD */
}

$pdf->Output();
