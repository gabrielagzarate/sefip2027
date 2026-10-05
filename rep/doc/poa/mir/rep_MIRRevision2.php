<?php
session_start();
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
    public $yHeader;

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

    public function Header()
    { //Encabezado del documento
        $parametros = array();
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
            WHERE gpo_id=? AND ur_id=? AND prgs_id =?;
        ');
        $parametros = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $_SESSION[_S]['clasAdm-prg'],
        ];
        $conexion2->consultar($parametros);
        $conexion2->desconectar();
        $detalle = $conexion2->extraer();

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
        $this->Cell(264, 5, 'PROGRAMA OPERATIVO ANUAL ' . APP_EJERCICIO, 0, 1, 'L');
        $this->Cell(264, 5, utf8Decode('MATRÍZ DE INDICADORES PARA RESULTADOS'), 0, 1, 'L');
        $this->Cell(264, 5, utf8Decode('REPORTE MIR'), 0, 1, 'L');
        $this->SetXY($ix + 17, $ytmp + 3.5);
        $ytmp = $this->GetY();
        $this->ln(23);

        $this->yHeader = $this->GetY();
    }
}
$and = '';

switch ($_POST['eri_trimestre']) {
    case 0:
        $and = 'usr_fecha <= "'. APP_EJERCICIO_ANTERIOR.'-12-31"';
        $trim = 0;
        break;
    case 1:
        $and = 'usr_fecha <= "'.APP_EJERCICIO.'-03-31"';
        $trim = 1;
        break;
    case 2:
        $and = 'usr_fecha <= "'.APP_EJERCICIO.'-06-30"';
        $trim = 2;
        break;
    case 3:
        $and = 'usr_fecha <= "'.APP_EJERCICIO.'-09-30"';
        $trim = 3;
        break;
    case 4:
        $and = 'usr_fecha <= "'.APP_EJERCICIO.'-12-31"';
        $trim = 4;
        break;
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
$pdf->AddPage();
$fill = false;

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
    'prgs_id' => $_SESSION[_S]['clasAdm-prg'],
];
$c1->conectar();
$c1->consultar($parametros);
$d1 = $c1->extraer();

$pdf->SetWidths(array(280));
$pdf->SetAligns(array('C'));
$pdf->SetFont('UniviaPro-Bold', 'B', 7.5);
$pdf->Row(array('PROGRAMA ' . str_pad($d1['prgs_id'], 2, "0", STR_PAD_LEFT) . ' ' . utf8Decode($d1['prgs_nombre'])));
$pdf->SetRelleno("no");

/* SEGUNDA LINEA DEL REPORTE */
$pdf->SetRelleno("si");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(150, 46, 74);
$pdf->setAltoLinea(2.5);
$pdf->SetWidths(array(280));
$pdf->SetAligns(array('L'));
$pdf->SetFont('UniviaPro-Bold', 'B', 7);
$pdf->SetTextColor(255, 255, 255);
$pdf->Row(array(utf8Decode('DATOS DE IDENTIFICACIÓN DEL PROGRAMA PRESUPUESTARIO')));
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

$pdf->SetFont('UniviaPro-Bold', 'B', 6);
$pdf->SetTextColor(0, 0, 0);


$c2 = new AccesoDatos();
$c2->setSql('
    SELECT
    eje_nombre,obj_nombre,tem_nombre,prgs_nombre,ods_nombre, pb.pbf_nombre, prgs_hombres, prgs_mujeres, prgs_total,
    prgs_hombresO AS total_hombres, pbO.pbf_nombre AS pbf_nombreO, prgs_mujeresO AS total_mujeres,prgs_totalO AS total_sprs,
    prgs_MedioVerificacion, obp_nombre, spe_nombre, ple_nombre, ope_nombre, vp.spe_id, vp.ple_id
    FROM vin_Programa vp
    INNER JOIN cat_Programas p USING(prgs_id)
    INNER JOIN cat_PoblacionBeneficiada pb USING(pbf_id)
    INNER JOIN cat_PoblacionBeneficiada AS pbO ON pbO.pbf_id = p.pbf_idO
    INNER JOIN enc_PedObjetivo e USING(epo_id)
    INNER JOIN cat_Eje USING(eje_id)
    INNER JOIN cat_Tema t USING(eje_id, tem_id)
    INNER JOIN cat_Objetivo o ON o.eje_id=e.eje_id AND o.tem_id=t.tem_id AND o.obj_id=e.obj_id
    INNER JOIN enc_Pes ps ON ps.obp_id=vp.obp_id AND ps.spe_id=vp.spe_id
    INNER JOIN cat_ObjetivoPes op ON op.obp_id=vp.obp_id AND vp.spe_id=op.spe_id
    INNER JOIN cat_SectorPes s ON s.spe_id=vp.spe_id
    INNER JOIN enc_PedODS USING (epods_id)
    INNER JOIN cat_ODS  USING (ods_id)
    INNER JOIN cat_PlanEspecial USING(ple_id)
    INNER JOIN cat_ObjetivoPe pl ON vp.ple_id = pl.ple_id AND vp.ope_id = pl.ope_id
    WHERE prgs_id = ?
    -- GROUP BY eje_nombre, obj_nombre, tem_nombre, prgs_nombre, ods_nombre, pbf_nombre;
');
$c2->conectar();
$c2->consultar([$_SESSION[_S]['clasAdm-prg']]);
$d2 = $c2->extraer();
$c2->desconectar();

$conta = 1;
do {
    if ($conta == 1) {
        // UR Responsable (lado izquierdo)
        $pdf->setX(10);
        $pdf->SetFont('UniviaPro-Bold', 'B', 6);
        $pdf->Cell(18, 5, 'UR Responsable:', 0, 0, "L", $fill);
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Cell(10, 5, $d1['ur_clave'], 0, 0, "R", $fill);
        $pdf->Cell(120, 5, utf8Decode($d1['ur_nombre']), 0, 0, "L", $fill);

        $pdf->SetFont('UniviaPro-Bold', 'B', 6);
        $pdf->Cell(42, 5, utf8Decode('Población o área de enfoque potencial:'), 0, 0, "L", $fill);
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Cell(38, 5, utf8Decode($d2['pbf_nombre']), 0, 1, "L", $fill);
        $pdf->setX(205);
        $pdf->Cell(85, 5, ' Hombres:   ' . $d2['prgs_hombres'] . '         Mujeres:   ' . $d2['prgs_mujeres'] . '         Total:   ' . $d2['prgs_total'], 0, 1, "L", $fill);


        $pdf->setX(158);
        $pdf->SetFont('UniviaPro-Bold', 'B', 6);
        $pdf->Cell(42, 5, utf8Decode('Población o área de enfoque objetivo:'), 0, 0, "L", $fill);
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Cell(38, 5, utf8Decode($d2['pbf_nombreO']), 0, 1, "L", $fill);
        $pdf->setX(205);
        $pdf->Cell(85, 4, ' Hombres:   ' . $d2['total_hombres'] . '         Mujeres:   ' . $d2['total_mujeres'] . '         Total:   ' . $d2['total_sprs'], 0, 1, "L", $fill);
        $pdf->Cell(0, 2, ' ', 0, 1, "l", $fill);

        $pdf->setX(158);
        $pdf->SetFont('UniviaPro-Bold', 'B', 6);
        $pdf->Cell(42, 5, utf8Decode('Medio de verificación:'), 0, 0, "L", $fill);
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $xAntes = $pdf->GetX();
        $yAntes = $pdf->GetY();
        $pdf->MultiCell(80, 5, utf8Decode($d2['prgs_MedioVerificacion']), 0, $fill);
        $yDespuesMultiCell = $pdf->GetY();
        $pdf->ln();
    } else {
        // UR's Participantes (lado izquierdo)
        $pdf->setX(10);
        $pdf->SetFont('UniviaPro-Bold', 'B', 6);
        if ($conta == 2) {
            $pdf->setY(48);
            $pdf->Cell(19, 5, 'UR\'s Participantes: ', 0, 0, 'L', $fill);
        } else {
            $pdf->Cell(19, 5, '', 0, 0, 'L', $fill);
        }
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Cell(9, 5, $d1['ur_clave'], 0, 0, "R", $fill);
        $pdf->Cell(252, 5, utf8Decode($d1['ur_nombre']), 0, 1, "L", $fill);
    }
    $conta++;
} while ($d1 = $c1->extraer());

if ($c1->getNumeroRegistros() > 5) {
    $yFinal = max($pdf->GetY(), $yDespuesMultiCell);
    $pdf->setY($yFinal);
} elseif ($c1->getNumeroRegistros() == 1) {
    $yFinal = max($pdf->GetY(), $yDespuesMultiCell);
    $pdf->setY($yFinal);
} else {
    // Usa el mayor entre 80 y donde terminó el MultiCell
    $yFinal = max(80, $yDespuesMultiCell);
    $pdf->setY($yFinal);
}
$c1->desconectar();


// Ajusta posición Y para mostrarse en el lado derecho

/* SEGUNDA LINEA DEL REPORTE */
$pdf->SetRelleno("si");
$pdf->CheckPageBreak(10);
$pdf->setY($pdf->GetY() + 2);
$pdf->setX(10);
$pdf->SetFillColor(0, 91, 8);
$pdf->setAltoLinea(2.5);
$pdf->SetWidths(array(280));
$pdf->SetAligns(array('L'));
$pdf->SetFont('UniviaPro-Bold', 'B', 7);
$pdf->SetTextColor(255, 255, 255);
$pdf->Row(array(utf8Decode('ALINEACIÓN')));
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

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

$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('UniviaPro-Bold', 'B', 7);
$pdf->setX(10);
$pdf->Cell(280, 5, 'PLAN ESTATAL DE DESARROLLO', 0, 0, 'L', $fill);

$y = $pdf->GetY();

$pdf->SetRelleno("no");
$pdf->CheckPageBreak(10);
$pdf->setXY(10,$y+2);
$pdf->SetFillColor(199, 209, 219);
$pdf->setBorders(0);
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

// ---- EJE ----
$pdf->setFuentes($fuentes);
$pdf->Row(array('EJE:', strtoupper(utf8Decode($d2['eje_nombre'])), 'TEMA:', strtoupper(utf8Decode($d2['tem_nombre'])), 'OBJETIVO:', strtoupper(utf8Decode($d2['obj_nombre']))), '');
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

if ($d2['spe_id'] != 13) {
    /* FINALIDAD Y PLAN ESTRATEGICO */
    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
    $pdf->setX(10);
    $pdf->Cell(280, 4, utf8Decode('PLAN ESTRATÉGICO SECTORIAL'), '', 2, 'L', $fill);

    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);
    $pdf->SetFillColor(199, 209, 219);
    $pdf->setBorders(0);
    $pdf->setAltoLinea(4);
    $pdf->SetWidths(array(15, 125, 15, 125,));
    $pdf->SetAligns(array('L', 'L', 'L', 'L'));

    $pdf->setFuentes($fuentes);
    $pdf->Row(array('SECTOR:', strtoupper(utf8Decode($d2['spe_nombre'])), 'OBJETIVO:', strtoupper(utf8Decode($d2['obp_nombre']))), '');
    $pdf->SetRelleno("no");
    $pdf->setBorders(0);
    $pdf->SetFillColor(255, 255, 255);
}
if ($d2['ple_id'] != 7) {
    /* FINALIDAD Y PLAN ESTRATEGICO */
    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
    $pdf->setX(10);
    $pdf->Cell(280, 4, utf8Decode('PLAN ESPECIAL'), '', 2, 'L', $fill);

    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);
    $pdf->SetFillColor(199, 209, 219);
    $pdf->setBorders(0);
    $pdf->setAltoLinea(4);
    $pdf->SetWidths(array(15, 125, 15, 125,));
    $pdf->SetAligns(array('L', 'L', 'L', 'L'));

    $pdf->setFuentes($fuentes);
    $pdf->Row(array('PLAN:', strtoupper(utf8Decode($d2['ple_nombre'])), 'OBJETIVO:', strtoupper(utf8Decode($d2['ope_nombre']))), '');
    $pdf->SetRelleno("no");
    $pdf->setBorders(0);
    $pdf->SetFillColor(255, 255, 255);
}

$pdf->SetFont('UniviaPro-Bold', 'B', 6);
$pdf->Cell(15, 4, ' ', 0, 0, 'L', $fill);
$pdf->SetFont('UniviaPro-Regular', '', 6);
$pdf->Cell(269, 4, '', 0, 0, 'L', $fill);
$pdf->Cell(1, 4, '', 0, 1, 0, $fill);

$pdf->setX(10);
$pdf->SetFont('UniviaPro-Bold', 'B', 6);
$pdf->Cell(22, 4, 'VINCULACION ODS: ', 0, 0, 'L', $fill);
$pdf->SetFont('UniviaPro-Regular', '', 6);
$pdf->MultiCell(255, 4, strtoupper(utf8Decode($d2['ods_nombre'])), 0, 'L', $fill);

$pdf->Cell(269, 4, '', 0, 0, 'L', $fill);
$pdf->Cell(1, 4, '', 0, 1, 0, $fill);

/* TERCER LINEA DEL REPORTE */
$pdf->SetRelleno("si");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(171, 121, 76);
$pdf->setAltoLinea(2.5);
$pdf->SetWidths(array(280));
$pdf->SetAligns(array('C'));
$pdf->SetFont('UniviaPro-Bold', 'B', 7);
$pdf->Row(array(utf8Decode('Objetivos, Indicadores y Metas de la MIR')));
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

$c2 = new AccesoDatos();
$c2->setSql('
    SELECT
        IFNULL(ind_fin,0) ind_fin, IFNULL(ind_id,0) ind_id,IFNULL(fin_formulaCalculo,\'-\') fin_formulaCalculo,
        IFNULL(tpi_fin,0) tpi_fin, IFNULL(dim_fin,0) dim_fin, IFNULL(frm_fin,0) frm_fin, IFNULL(ses_fin,0) ses_fin,
        IF(ISNULL(mtf_original),"",mtf_original) AS mem_original,mir_resumenNarrativo, pmi_id, vpm_id,
        obj_nombre AS fin, tiv_nombre, umi_nombre, pmi_id, vpm_id,
        mtf_anio, mtf_valor
    FROM cat_Mir
    INNER JOIN vin_ProgramasMir USING(mir_id)
    INNER JOIN vin_Programa using(prgs_id)
    INNER JOIN enc_PedObjetivo USING (epo_id)
    INNER JOIN cat_Objetivo USING(eje_id, tem_id, obj_id)
    LEFT JOIN(
        SELECT vpm_id, pmi_id, nim_id, ind_id, ind_nombreMIR AS ind_fin, ind_formulaCalculo AS fin_formulaCalculo,
            tpi_nombre tpi_fin,dim_nombre dim_fin,frm_nombre frm_fin,ses_nombre ses_fin,
            mtf_id, mtf_original, tiv_nombre, umi_nombre, mtf_anio, mtf_valor
        FROM vin_ProgramaMirIndicador
        LEFT JOIN cat_MetaFin USING(pmi_id)
        INNER JOIN cat_Indicador USING(nim_id,ind_id)
        JOIN cat_TipoIndicador USING(tpi_id)
        INNER JOIN cat_TipoValor USING(tiv_id)
        JOIN cat_Dimension USING(dim_id)
        JOIN cat_FrecuenciaMedicion USING(frm_id)
        JOIN cat_SentidoEsperado USING(ses_id)
        INNER JOIN cat_UnidadMedidaIndicador USING(umi_id)
        WHERE nim_id=1 AND vin_ProgramaMirIndicador.trim_'. $_POST['eri_trimestre'] .'=1
    )s USING(vpm_id)
    WHERE prgs_id=:prg_id;
');
$parametros = [
    ':prg_id' => $_SESSION[_S]['clasAdm-prg']
];
$c2->conectar();
$c2->consultar($parametros);

$pdf->SetRelleno("si");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(142, 65, 141);
$pdf->setBorders(1);
$pdf->setAltoLinea(2.5);
$pdf->SetWidths(array(280));
$pdf->SetAligns(array('C'));
$pdf->SetFont('UniviaPro-Bold', 'B', 7);
$pdf->Row(array('FIN'));
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

/*  DETALLE DEL REPORTE */
while ($row = $c2->extraer()) {
$mediosFin = $supFin = '';
$supMeF = new AccesoDatos();
        $supMeF->setSql('
            SELECT *
            FROM cat_MedioVerificacionProgramaMir m
            WHERE vpm_id=? AND nim_id=1 AND pmi_id = ? AND mvpm_trimestreCierre = ?;
        ');
        $parametros = [
            $row['vpm_id'],
            $row['pmi_id'],
            $_POST['eri_trimestre']
        ];
        $supMeF->conectar();
        $supMeF->consultar($parametros);
        $supMeF->desconectar();

        while ($MediosF = $supMeF->extraer()) {
            $mediosFin .= $MediosF['mvpm_nombre']. "\n"."\n";
        }

        $supF = new AccesoDatos();
        $supF->setSql('
            SELECT *
            FROM cat_SupuestoProgramaMir s
            WHERE vpm_id=? AND nim_id=1 AND spm_trimestreCierre = ?;
        ');

        $parametros = [
            $row['vpm_id'],
            $_POST['eri_trimestre']
        ];
        $supF->conectar();
        $supF->consultar($parametros);
        $supF->desconectar();

        while ($supMediosF = $supF->extraer()) {
            $supFin .= $supMediosF['spm_nombre'] . "\n" . "\n";
        }

$pdf->setX(10);
$pdf->SetFont('UniviaPro-Bold', 'B', 6);
$pdf->Cell(27, 10, utf8Decode('RESUMEN NARRATIVO: '), 0, 0, "L", $fill);
$pdf->SetFont('UniviaPro-Regular', '', 6);
$pdf->Cell(38, 10, strtoupper(utf8Decode($row['mir_resumenNarrativo'])), 0, 1, "L", $fill);

$pdf->setX(10);
$pdf->SetFont('UniviaPro-Bold', 'B', 6);
$pdf->Cell(26, 8, utf8Decode('INDICADOR ' . strtoupper($row['ind_id'] . ' NIVEL 1:')), 0, 0, "L", $fill);
$pdf->SetFont('UniviaPro-Regular', '', 6);
$pdf->Cell(27, 8, utf8Decode(strtoupper(($row['ind_fin']))), 0, 1, "L", $fill);

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
    ':prgs_id' => $_SESSION[_S]['clasAdm-prg']
];
$c1->conectar();
$c1->consultar($parametros);
$d1 = $c1->extraer();

$pdf->Cell(280, 3, '', 0, 1, "L", $fill);

$pdf->SetRelleno("no");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(199, 209, 219);
$pdf->SetArrayBorders(array('LT', 'T', 'T', 'T', 'T', 'T', 'T', 'TR'));
$pdf->setAltoLinea(4);
$pdf->SetWidths(array(25, 29, 29, 29, 29, 35, 29, 60));
$pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C' , 'C', 'L'));
$pdf->SetFont('UniviaPro-Bold', 'B', 6);
$pdf->Row(array('UR', 'TIPO', utf8Decode('DIMENSIÓN'), 'FRECUENCIA', 'TIPO DE VALOR', 'UNIDAD DE MEDIDA DEL INDICADOR', 'META', utf8Decode('MÉTODO DE CÁLCULO')));
$pdf->SetFillColor(255, 255, 255);
$pdf->SetArrayBorders(null);
$pdf->SetArrayBorders(array('LB', 'B', 'B', 'B', 'B', 'B', 'B', 'BR'));
$pdf->SetFont('UniviaPro-Regular', '', 6);
$pdf->Row(array(strtoupper(utf8Decode($d1['ur_clave'])), utf8Decode($row['tpi_fin']), $row['dim_fin'], $row['frm_fin'], $row['tiv_nombre'], utf8Decode($row['umi_nombre']), $row['mem_original'], strtoupper(utf8Decode($row['fin_formulaCalculo']))));
$pdf->SetArrayBorders(null);
$pdf->Cell(280, 4, '', 0, 1, "L", $fill);

$pdf->SetLineWidth(.5);

if ($row['mtf_valor'] != null) {

    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);
    $pdf->setAltoLinea(4);
    $pdf->SetWidths(array(130));
    $pdf->SetArrayBorders(null);
    $pdf->SetAligns(array('L'));
    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
    $pdf->Row(array(utf8Decode('LÍNEA BASE'), ''));
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetWidths(array(50, 80));
    $pdf->SetArrayBorders(array('LTBR', 'TBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array(strtoupper($row['mtf_anio']), strtoupper($row['mtf_valor'])));
    $pdf->SetArrayBorders(null);
    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);
    $pdf->setAltoLinea(4);
    $pdf->SetWidths(array(130));
    $pdf->SetArrayBorders(null);
    $pdf->SetAligns(array('L'));
    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
    $pdf->Row(array(utf8Decode(' VALOR PROGRAMADO ANUAL'), ''));
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetWidths(array(5,125));
    $pdf->SetArrayBorders(array('LTB', 'TBR'));
    $pdf->SetAligns(array('L', 'L'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array('', APP_EJERCICIO));
    $pdf->SetWidths(array(5,125));
    $pdf->SetArrayBorders(array('LTB', 'TBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array('', strtoupper($row['mem_original'])));
    $pdf->SetWidths(array(30, 35, 35,30));
    $pdf->SetArrayBorders(null);
    $pdf->Row(array('', '','', ''));
    $pdf->SetArrayBorders(array('LTB','LTB','LTBR', 'TBR'));
    $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre')));
    $pdf->Row(array('', '','', strtoupper($row['mem_original'])));
    $pdf->SetArrayBorders(null);
    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);
}

$pdf->SetLineWidth(.3);

$pdf->setBorders(0);
$pdf->setAltoLinea(4);
$pdf->SetWidths(array(30, 150));
$pdf->SetAligns(array('L', 'L'));
$pdf->setFuentes($fuentes);
$pdf->Row(array(utf8Decode('MEDIOS DE VERIFICACIÓN: '), strtoupper(utf8Decode($mediosFin))));
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

$pdf->Cell(280, 4, '', 0, 1, "L", $fill);

$pdf->setBorders(0);
$pdf->setAltoLinea(4);
$pdf->SetWidths(array(16, 164));
$pdf->SetAligns(array('L', 'L'));
$pdf->setFuentes($fuentes);
$pdf->Row(array(utf8Decode('SUPUESTOS: '), strtoupper(utf8Decode($supFin))));
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);
$anteriorindF = $row['ind_id'];
}
/*NIVEL PROPÓSITO*/
$pdf->SetRelleno("si");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(0, 176, 166);
$pdf->setBorders(1);
$pdf->setAltoLinea(2.5);
$pdf->SetWidths(array(280));
$pdf->SetAligns(array('C'));
$pdf->SetFont('UniviaPro-Bold', 'B', 7);
$pdf->Row(array(utf8Decode('PROPÓSITO')));
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

/* DETALLE DE NIVEL PROPOSITO */
$p1 = new AccesoDatos();
$p1->setSql('
    SELECT IFNULL(MAX(s.vpm_id),0) vpm_id, IFNULL(MAX(ind_pro),"") ind_pro, IFNULL(ind_id,0) ind_id,
    IFNULL(MAX(pro_formulaCalculo),"") pro_formulaCalculo, IFNULL(MAX(tpi_pro),"") tpi_pro,
    IFNULL(MAX(dim_pro),"") dim_pro, IFNULL(MAX(frm_pro),"") frm_pro, IFNULL(MAX(ses_pro),"") ses_pro,
    0 lac_id, IFNULL(MAX(vpm_resumenNarrativo),"") proposito,
    IFNULL(MAX(mediosPro),"") mediosPro, IFNULL(MAX(mdv_id),"") mdv_id, IFNULL(MAX(SupuestosPro),"") SupuestosPro,
    IFNULL(MAX(sup_id),0) sup_id, IFNULL(MAX(mpr_original),"") mpr_original, IFNULL(MAX(mpr_anteriorIndicador),"") mpr_anteriorIndicador,
    IFNULL(MAX(umi_id),0) umi_id, umi_nombre, tiv_nombre, mpr_lb, mpr_numerador, mpr_denominador, mpr_lbIndicador, mpr_anio, mpr_anteriorValor, mpr_anterior,
    mpr_lbValor
    FROM cat_Mir
    INNER JOIN vin_ProgramasMir USING(mir_id)
    LEFT JOIN(
        SELECT vpm_id, pmi_id, nim_id, ind_id, ind_nombreMIR AS ind_pro, ind_formulaCalculo AS pro_formulaCalculo,
            tpi_nombre tpi_pro,dim_nombre dim_pro,frm_nombre frm_pro,ses_nombre ses_pro, umi_id, umi_nombre,
            mpr_id, mpr_original, mpr_anteriorIndicador, tiv_id, tiv_nombre, mpr_anio, mpr_lb, mpr_numerador, mpr_denominador, mpr_lbIndicador, mpr_anteriorValor, mpr_anterior,
            mpr_lbValor
        FROM vin_ProgramaMirIndicador
        INNER JOIN cat_Indicador USING(nim_id,ind_id)
        JOIN cat_TipoIndicador USING(tpi_id)
        JOIN cat_Dimension USING(dim_id)
        JOIN cat_FrecuenciaMedicion USING(frm_id)
        JOIN cat_SentidoEsperado USING(ses_id)
        LEFT JOIN cat_MetaProposito USING(pmi_id)
        INNER JOIN cat_TipoValor USING(tiv_id)
        INNER JOIN cat_UnidadMedidaIndicador um USING(umi_id)
        WHERE nim_id=2 AND vin_ProgramaMirIndicador.trim_'. $_POST['eri_trimestre'] .'=1 AND mpr_trimestreCierre IN(0) AND mpr_original >= 0
    )s USING(vpm_id)
    LEFT JOIN (
        SELECT GROUP_CONCAT(spm_nombre, "\n" SEPARATOR "") AS SupuestosPro, MAX(spm_id) AS sup_id, vpm_id, nim_id
        FROM cat_SupuestoProgramaMir
        WHERE nim_id = 2 AND spm_trimestreCierre = :tri_id
        GROUP BY vpm_id
    )SupFin USING(vpm_id,nim_id)
    LEFT JOIN (
        SELECT GROUP_CONCAT(mvpm_nombre, "\n" SEPARATOR "") AS mediosPro, MAX(mvpm_id) AS mdv_id, vpm_id, nim_id, pmi_id
        FROM cat_MedioVerificacionProgramaMir
        WHERE nim_id = 2 AND mvpm_trimestreCierre = :tri_id
        GROUP BY vpm_id
    )MevFin ON MevFin.vpm_id = s.vpm_id AND MevFin.nim_id = 2 AND MevFin.pmi_id = s.pmi_id
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

$pdf->setX(10);
$pdf->SetFont('UniviaPro-Bold', 'B', 6);
$pdf->Cell(27, 9, utf8Decode('RESUMEN NARRATIVO: '), 0, 0, "L", $fill);
$pdf->SetFont('UniviaPro-Regular', '', 6);
$pdf->Cell(38, 9, strtoupper(utf8Decode($rowP['proposito'])), 0, 1, "L", $fill);

$pdf->setX(10);
$pdf->SetFont('UniviaPro-Bold', 'B', 6);
$pdf->Cell(26, 6, utf8Decode('INDICADOR ' . strtoupper($rowP['ind_id'] . ' NIVEL 2:')), 0, 0, "L", $fill);
$pdf->SetFont('UniviaPro-Regular', '', 6);
$pdf->Cell(27, 6, utf8Decode(strtoupper(($rowP['ind_pro']))), 0, 1, "L", $fill);

switch ($rowP['umi_id']) {
    case 2:case 3:case 5:case 4:
        $valorMeta = $rowP['mpr_anteriorIndicador'];
        break;
    default:
        $valorMeta = $rowP['mpr_original'];
        break;
}

$pdf->SetRelleno("no");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(199, 209, 219);
$pdf->SetArrayBorders(array('LT', 'T', 'T', 'T', 'T', 'T', 'T', 'TR'));
$pdf->setAltoLinea(4);
$pdf->SetWidths(array(25, 29, 29, 29, 29, 35, 29, 60));
$pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C' , 'C', 'L'));
$pdf->SetFont('UniviaPro-Bold', 'B', 6);
$pdf->Row(array('UR', 'TIPO', utf8Decode('DIMENSIÓN'), 'FRECUENCIA', 'TIPO DE VALOR', 'UNIDAD DE MEDIDA DEL INDICADOR', 'META', utf8Decode('MÉTODO DE CÁLCULO')));
$pdf->SetFillColor(255, 255, 255);
$pdf->SetArrayBorders(null);
$pdf->SetArrayBorders(array('LB', 'B', 'B', 'B', 'B', 'B', 'B', 'BR'));
$pdf->SetFont('UniviaPro-Regular', '', 6);
$pdf->Row(array(strtoupper(utf8Decode($d1['ur_clave'])), utf8Decode($rowP['tpi_pro']), $rowP['dim_pro'], $rowP['frm_pro'], $rowP['tiv_nombre'], utf8Decode($rowP['umi_nombre']), $valorMeta,utf8Decode($rowP['pro_formulaCalculo'])));
$pdf->SetArrayBorders(null);
$pdf->Cell(280, 3, '', 0, 1, "L", $fill);

$pdf->SetLineWidth(.5);

if ($rowP['umi_id'] == 2 || $rowP['umi_id'] == 3 || $rowP['umi_id'] == 4) {
    # code...
    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);
    $pdf->setAltoLinea(4);
    $pdf->SetWidths(array(130));
    $pdf->SetArrayBorders(null);
    $pdf->SetAligns(array('L','L','L','L'));
    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
    $pdf->Row(array(utf8Decode('LÍNEA BASE'), ''));
    $pdf->SetAligns(array('L','L','L','L'));
    $pdf->SetWidths(array(30, 35, 35, 30));
    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array(utf8Decode('AÑO'), 'NUMERADOR', 'DENOMINADOR', 'VALOR LB'));
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetWidths(array(30, 35, 35, 30));
    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array(strtoupper($rowP['mpr_lb']), $rowP['mpr_numerador'], $rowP['mpr_denominador'], $rowP['mpr_lbIndicador']));
    $pdf->SetArrayBorders(null);
    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);
    $pdf->setAltoLinea(4);
    $pdf->SetWidths(array(130));
    $pdf->SetArrayBorders(null);
    $pdf->SetAligns(array('L'));
    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
    $pdf->Row(array(utf8Decode(' VALOR DESEADO'), ''));
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetAligns(array('L','L','L','L'));
    $pdf->SetWidths(array(30, 35, 35, 30));
    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'TBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array('NUMERADOR', 'DENOMINADOR', APP_EJERCICIO));
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetWidths(array(30, 35, 35, 30));
    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array($rowP['mpr_anteriorValor'], $rowP['mpr_original'], $valorMeta));
    $pdf->SetArrayBorders(null);
    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);
    $pdf->setAltoLinea(4);
    $pdf->SetAligns(array('L'));
    $pdf->SetWidths(array(130));
    $pdf->SetArrayBorders(array('LTBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array('NUMERADOR'));
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetWidths(array(30, 35, 35, 30));
    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre')));
    $pdf->Row(array('', '', '', $rowP['mpr_anteriorValor']));
    $pdf->SetAligns(array('L'));
    $pdf->SetWidths(array(130));
    $pdf->SetArrayBorders(array('LTBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array('DENOMINADOR'));
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetWidths(array(30, 35, 35, 30));
    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre')));
    $pdf->Row(array('', '', '', $rowP['mpr_original']));
    $pdf->SetArrayBorders(null);
    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);
} else if ($rowP['umi_id'] == 5) {
    # code...
    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);
    $pdf->setAltoLinea(4);
    $pdf->SetWidths(array(130));
    $pdf->SetArrayBorders(null);
    $pdf->SetAligns(array('L','L','L','L'));
    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
    $pdf->Row(array(utf8Decode('LÍNEA BASE'), ''));
    $pdf->SetAligns(array('L','L','L','L', 'L'));
    $pdf->SetWidths(array(20, 30, 20, 30, 30));
    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR', 'TBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array(utf8Decode('AÑO T-1'), utf8Decode('VALOR AÑO T-1'), utf8Decode('AÑO T0'), utf8Decode('VALOR AÑO T0'), 'VALOR INDICADOR'));
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetWidths(array(20, 30, 20, 30, 30));
    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR', 'TBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array(strtoupper($rowP['mpr_lb']), $rowP['mpr_lbValor'], $rowP['mpr_anterior'], $rowP['mpr_anteriorValor'], $rowP['mpr_lbIndicador']));
    $pdf->SetArrayBorders(null);
    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);
    $pdf->setAltoLinea(4);
    $pdf->SetWidths(array(130));
    $pdf->SetArrayBorders(null);
    $pdf->SetAligns(array('L'));
    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
    $pdf->Row(array(utf8Decode(' VALORES PROGRAMADOS'), ''));
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetAligns(array('L','L','L','L', 'L'));
    $pdf->SetWidths(array(20, 30, 20, 30, 30));
    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR', 'TBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array(utf8Decode('AÑO T-0'), utf8Decode('VALOR AÑO T-0'), utf8Decode('AÑO T1'), utf8Decode('VALOR AÑO T1'), 'VALOR INDICADOR'));
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetWidths(array(20, 30, 20, 30, 30));
    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR', 'TBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array(strtoupper($rowP['mpr_anterior']), $rowP['mpr_anteriorValor'], $rowP['mpr_anio'], $rowP['mpr_original'], $rowP['mpr_anteriorIndicador']));
    $pdf->SetArrayBorders(null);
    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);
    $pdf->setAltoLinea(4);
    $pdf->SetAligns(array('L'));
    $pdf->SetWidths(array(130));
    $pdf->SetArrayBorders(array('LTBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array('T1'));
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetWidths(array(30, 35, 35, 30));
    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre')));
    $pdf->Row(array('', '', '', $rowP['mpr_original']));
    $pdf->SetAligns(array('L'));
    $pdf->SetWidths(array(130));
    $pdf->SetArrayBorders(array('LTBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array('T0'));
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetWidths(array(30, 35, 35, 30));
    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre')));
    $pdf->Row(array('', '', '', $rowP['mpr_anteriorValor']));
    $pdf->SetArrayBorders(null);
    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

} else {
    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);
    $pdf->setAltoLinea(4);
    $pdf->SetWidths(array(130));
    $pdf->SetArrayBorders(null);
    $pdf->SetAligns(array('L'));
    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
    $pdf->Row(array(utf8Decode(' LÍNEA BASE'), ''));
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetWidths(array(20,110));
    $pdf->SetArrayBorders(array('LTBR', 'TBR'));
    $pdf->SetAligns(array('L', 'L'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array(utf8Decode('AÑO'), 'VALOR'));
    $pdf->SetWidths(array(20,110));
    $pdf->SetArrayBorders(array('LTBR', 'TBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array($rowP['mpr_lb'], $rowP['mpr_lbValor']));
    $pdf->SetArrayBorders(null);
    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);
    $pdf->setAltoLinea(4);
    $pdf->SetWidths(array(130));
    $pdf->SetArrayBorders(null);
    $pdf->SetAligns(array('L'));
    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
    $pdf->Row(array(utf8Decode(' VALOR PROGRAMADO ANUAL ')));
    
    $pdf->SetWidths(array(5,125));
    $pdf->SetArrayBorders(array('LTB','TBR'));
    $pdf->SetAligns(array('L','L'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array('',$rowP['mpr_original']));
    $pdf->SetWidths(array(30, 35, 35,30));
    $pdf->SetArrayBorders(null);
    $pdf->Row(array('', '','', ''));
    
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetWidths(array(30, 35, 35, 30));
    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR', 'TBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre')));
    $pdf->SetWidths(array(30, 35, 35, 30));
    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR', 'TBR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array('', '', '', $rowP['mpr_original']));
    $pdf->SetArrayBorders(null);
    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);


}

$pdf->SetRelleno("no");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(199, 209, 219);
$pdf->setBorders(0);
$pdf->setAltoLinea(4);
$pdf->SetWidths(array(30, 180));
$pdf->SetAligns(array('L', 'L'));
$pdf->setFuentes($fuentes);
$pdf->Row(array(utf8Decode('MEDIOS DE VERIFICACIÓN: '), strtoupper(utf8Decode($rowP['mediosPro']))));
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

$pdf->Cell(280, 3, '', 0, 1, "L", $fill);

$pdf->SetRelleno("no");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(199, 209, 219);
$pdf->setBorders(0);
$pdf->setAltoLinea(4);
$pdf->SetWidths(array(16, 264));
$pdf->SetAligns(array('L', 'L'));
$pdf->setFuentes($fuentes);
$pdf->Row(array(utf8Decode('SUPUESTOS: '), strtoupper(utf8Decode($rowP['SupuestosPro']))));
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

}

$p1->desconectar();
$pdf->Cell(280, 2, '', 0, 1, "L", $fill);
/* FIN DETALLE DE NIVEL PROPOSITO */

/* DETALLE DEL REPORTE A NIVEL COMPONENTE Y ACTIVIDAD */
$texTotal = '';
$sql = "
    SELECT sprs_id, pdf_id, pdf_mir AS componente, epfi_id AS ipf_id, ind_id,
        m.gpo_id, m.ur_id,
        m.mco_original AS total, m.mco_original,
        ind_nombreMIR, ind_formulaCalculo,tpi_id,tpi_nombre, dim_id, dim_nombre, frm_id,
        frm_nombre, ses_id, ses_nombre, MAX(mco_descripcion) AS mem_descripcion, ind_nombre, m.mco_numerador, m.mco_denominador,
        m.mco_lb, m.mco_lbValor, tiv_id, tiv_nombre, umi_id, umi_nombre, mcot_numeradorT2, mcot_numeradorT4, mcot_denominadorT2,
        mcot_denominadorT4,m.mco_lbIndicador, m.mco_anterior, m.mco_anteriorValor, m.mco_anio, mcot_modificadoT2, mcot_modificadoT4
    FROM  cat_Subprogramas
    INNER JOIN cat_ProductoFinal USING (pdf_id)
    INNER JOIN enc_ProductoFinalIndicador e USING (pdf_id)
    LEFT JOIN cat_MetaComponente m USING (prgs_id, sprs_id, epfi_id)
    INNER JOIN aux_HistoricoModificacionMetaComponente aux USING(gpo_id, ur_id,prgs_id, sprs_id, epfi_id, gpo_id, ur_id)
    LEFT JOIN cat_Indicador USING(nim_id,ind_id)
    LEFT JOIN cat_TipoIndicador USING(tpi_id)
    LEFT JOIN cat_Dimension USING(dim_id)
    LEFT JOIN cat_FrecuenciaMedicion USING(frm_id)
    LEFT JOIN cat_SentidoEsperado USING(ses_id)
    LEFT JOIN cat_TipoValor USING(tiv_id)
    LEFT JOIN cat_UnidadMedidaIndicador USING(umi_id)
    WHERE prgs_id = ? AND (esr_id=? OR sprs_id=?) AND pdf_id!=? 
    AND e.trim_". $_POST['eri_trimestre'] ."=? AND m.mco_original >= 0 AND e." . $and . "
    GROUP BY prgs_id,sprs_id, gpo_id, ur_id,pdf_id,epfi_id,ind_id
    ORDER BY epfi_orden, sprs_id, pdf_id, ind_id, gpo_id, ur_id;
";
$comMeta = new AccesoDatos();
$comMeta->setSql($sql);
$parametros = [
    $_SESSION[_S]['clasAdm-prg'],
    4,
    0,
    0,
    1
];
$comMeta->conectar();
$comMeta->consultar($parametros);

$comMeta->desconectar();

$prodFinal = '';
$anteriorspr = '';
$anteriorcomp = '';
$deseadoDenComp = '';
$deseadoNumComp = '';

//revisar por qué en el componente 01 no junta los indicadores el segundo lo muestra al final

while ($rowC = $comMeta->extraer()) {

    $pdf_id = $rowC['pdf_id'];
    if ($anteriorspr != $rowC['sprs_id']) {
        $pdf->SetRelleno("si");
        $pdf->CheckPageBreak(10);
        $pdf->setX(10);
        $pdf->SetFillColor(92, 130, 237);
        $pdf->setBorders(1);
        $pdf->setAltoLinea(2.5);
        $pdf->SetWidths(array(280));
        $pdf->SetAligns(array('C'));
        $pdf->SetFont('UniviaPro-Bold', 'B', 7);
        $pdf->Row(array(utf8Decode('COMPONENTE ' . $rowC['sprs_id'] . ' ID FINAL ' . $rowC['pdf_id'])));
        $pdf->SetRelleno("no");
        $pdf->setBorders(0);
        $pdf->SetFillColor(255, 255, 255);
    }
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);
    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
    $pdf->Cell(50, 10, utf8Decode('RESUMEN NARRATIVO PRODUCTO FINAL ') . $rowC['pdf_id'] . ': ', 0, 0, "L", $fill);
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Cell(38, 10, strtoupper(utf8Decode($rowC['componente'])), 0, 1, "L", $fill);

    $pdf->setX(10);
    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
    $pdf->Cell(26, 8, utf8Decode('INDICADOR ' . strtoupper($rowC['ind_id'] . ' NIVEL 3')), 0, 0, "L", $fill);
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Cell(27, 8, utf8Decode(strtoupper(($rowC['ind_nombreMIR']))), 0, 1, "L", $fill);

    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);
    $pdf->SetFillColor(199, 209, 219);
    $pdf->SetArrayBorders(array('LT', 'T', 'T', 'T', 'T', 'T', 'T', 'TR'));
    $pdf->setAltoLinea(4);
    $pdf->SetWidths(array(25, 29, 29, 29, 29, 35, 29, 60));
    $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C' , 'C', 'L'));
    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
    $pdf->Row(array('UR', 'TIPO', utf8Decode('DIMENSIÓN'), 'FRECUENCIA', 'TIPO DE VALOR', 'UNIDAD DE MEDIDA DEL INDICADOR', 'META', utf8Decode('MÉTODO DE CÁLCULO')));
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetArrayBorders(null);
    $pdf->SetArrayBorders(array('LB', 'B', 'B', 'B', 'B', 'B', 'B', 'BR'));
    $pdf->SetFont('UniviaPro-Regular', '', 6);
    $pdf->Row(array(strtoupper(utf8Decode($rowC['gpo_id'].$rowC['ur_id'])), utf8Decode($rowC['tpi_nombre']), $rowC['dim_nombre'], $rowC['frm_nombre'], $rowC['tiv_nombre'], utf8Decode($rowC['umi_nombre']), $rowC['total'],utf8Decode($rowC['ind_formulaCalculo'])));
    $pdf->SetArrayBorders(null);
    $pdf->Cell(280, 3, '', 0, 1, "L", $fill);

    $pdf->SetLineWidth(.5);

    if ($rowC['umi_id'] == 2) {
        $totalT2 = ($rowC['mcot_denominadorT2'] != 0 ? (($rowC['mcot_numeradorT2'] / $rowC['mcot_denominadorT2'])*100) : 0);
        $totalT4 = ($rowC['mcot_denominadorT4'] != 0 ? (($rowC['mcot_numeradorT4'] / $rowC['mcot_denominadorT4'])*100) : 0);
    } else if ($rowC['umi_id'] == 3 || $rowC['umi_id'] == 4) {
        $totalT2 = ($rowC['mcot_denominadorT2'] != 0 ? ($rowC['mcot_numeradorT2'] / $rowC['mcot_denominadorT2']) : 0);
        $totalT4 = ($rowC['mcot_denominadorT4'] != 0 ? ($rowC['mcot_numeradorT4'] / $rowC['mcot_denominadorT4']) : 0);
    } else if ($rowC['umi_id'] == 5) {
        $totalT2 = ($rowC['mcot_denominadorT2'] != 0 ? ((($rowC['mcot_numeradorT2'] / $rowC['mcot_denominadorT2'])-1)*100) : 0);
        $totalT4 = ($rowC['mcot_denominadorT4'] != 0 ? ((($rowC['mcot_numeradorT4'] / $rowC['mcot_denominadorT4'])-1)*100) : 0);
    }

    if ($rowC['tiv_id'] == 1) {
        $deseadoNumComp = ($rowC['mcot_numeradorT2'] + $rowC['mcot_numeradorT4']);
        $deseadoDenComp = ($rowC['mcot_denominadorT2'] + $rowC['mcot_denominadorT4']);
    } else if ($rowC['tiv_id'] == 2) {
        $deseadoNumComp = $rowC['mcot_numeradorT4'];
        $deseadoDenComp = $rowC['mcot_denominadorT4'];
    }

    if ($rowC['umi_id'] == 2 || $rowC['umi_id'] == 3 || $rowC['umi_id'] == 4) {

        $pdf->SetRelleno("no");
        $pdf->CheckPageBreak(10);
        $pdf->setX(10);
        $pdf->setAltoLinea(4);
        $pdf->SetWidths(array(130));
        $pdf->SetArrayBorders(null);
        $pdf->SetAligns(array('L','L','L','L'));
        $pdf->SetFont('UniviaPro-Bold', 'B', 6);
        $pdf->Row(array(utf8Decode('LÍNEA BASE'), ''));
        $pdf->SetAligns(array('L','L','L','L'));
        $pdf->SetWidths(array(30, 35, 35, 30));
        $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array(utf8Decode('AÑO'), 'NUMERADOR', 'DENOMINADOR', 'VALOR LB'));
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetWidths(array(30, 35, 35, 30));
        $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array(strtoupper($rowC['mco_lb']), $rowC['mco_numerador'], $rowC['mco_denominador'], $rowC['mco_lbIndicador']));
        $pdf->SetArrayBorders(null);
        $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

        $pdf->SetRelleno("no");
        $pdf->CheckPageBreak(10);
        $pdf->setX(10);
        $pdf->setAltoLinea(4);
        $pdf->SetWidths(array(130));
        $pdf->SetArrayBorders(null);
        $pdf->SetAligns(array('L'));
        $pdf->SetFont('UniviaPro-Bold', 'B', 6);
        $pdf->Row(array(utf8Decode(' VALOR DESEADO'), ''));
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetAligns(array('L','L','L','L'));
        $pdf->SetWidths(array(30, 35, 35, 30));
        $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'TBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array('NUMERADOR', 'DENOMINADOR', APP_EJERCICIO));
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetWidths(array(30, 35, 35, 30));
        $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array($deseadoNumComp, $deseadoDenComp, $rowC['mco_original']));
        $pdf->SetArrayBorders(null);
        $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

        $pdf->SetRelleno("no");
        $pdf->CheckPageBreak(10);
        $pdf->setX(10);
        $pdf->setAltoLinea(4);
        $pdf->SetAligns(array('L'));
        $pdf->SetWidths(array(160));
        $pdf->SetArrayBorders(array('LTBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array('NUMERADOR'));
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetWidths(array(30, 35, 35, 30, 30));
        $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'LTBR', 'TBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre'), utf8Decode('Total')));
        $pdf->Row(array('', $rowC['mcot_numeradorT2'], '', $rowC['mcot_numeradorT4'], $deseadoNumComp));
        $pdf->SetAligns(array('L'));
        $pdf->SetWidths(array(160));
        $pdf->SetArrayBorders(array('LTBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array('DENOMINADOR'));
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetWidths(array(30, 35, 35, 30, 30));
        $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'LTBR', 'TBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre'), utf8Decode('Total')));
        $pdf->Row(array('', $rowC['mcot_denominadorT2'], '', $rowC['mcot_denominadorT4'], $deseadoDenComp));
        $pdf->SetArrayBorders(null);
        $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

        $pdf->SetFont('UniviaPro-Bold', 'B', 6);
        $pdf->Row(array('RESULTADO PARCIAL'));
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetWidths(array(30, 35, 35, 30, 30));
        $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'LTBR', 'TBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre'), utf8Decode('Total')));
        $pdf->Row(array('', round(($totalT2),4), '', round(($totalT4),4), $rowC['mco_original']));
        $pdf->SetArrayBorders(null);
        $pdf->Cell(280, 4, '', 0, 1, "L", $fill);
    } else if ($rowC['umi_id'] == 5) {

        $pdf->SetRelleno("no");
        $pdf->CheckPageBreak(10);
        $pdf->setX(10);
        $pdf->setAltoLinea(4);
        $pdf->SetWidths(array(130));
        $pdf->SetArrayBorders(null);
        $pdf->SetAligns(array('L','L','L','L'));
        $pdf->SetFont('UniviaPro-Bold', 'B', 6);
        $pdf->Row(array(utf8Decode('LÍNEA BASE'), ''));
        $pdf->SetAligns(array('L','L','L','L', 'L'));
        $pdf->SetWidths(array(20, 30, 20, 30, 30));
        $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR', 'TBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array(utf8Decode('AÑO T-1'), utf8Decode('VALOR AÑO T-1'), utf8Decode('AÑO T0'), utf8Decode('VALOR AÑO T0'), 'VALOR INDICADOR'));
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetWidths(array(20, 30, 20, 30, 30));
        $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR', 'TBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array(strtoupper($rowC['mco_lb']), $rowC['mco_lbValor'], $rowC['mco_anterior'], $rowC['mco_anteriorValor'], $rowC['mco_lbIndicador']));
        $pdf->SetArrayBorders(null);
        $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

        $pdf->SetRelleno("no");
        $pdf->CheckPageBreak(10);
        $pdf->setX(10);
        $pdf->setAltoLinea(4);
        $pdf->SetWidths(array(130));
        $pdf->SetArrayBorders(null);
        $pdf->SetAligns(array('L'));
        $pdf->SetFont('UniviaPro-Bold', 'B', 6);
        $pdf->Row(array(utf8Decode(' VALORES PROGRAMADOS'), ''));
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetAligns(array('L','L','L','L', 'L'));
        $pdf->SetWidths(array(20, 30, 20, 30, 30));
        $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'LTBR', 'TBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array(utf8Decode('AÑO T-0'), utf8Decode('VALOR AÑO T-0'), utf8Decode('AÑO T1'), utf8Decode('VALOR AÑO T1'), 'VALOR INDICADOR'));
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetWidths(array(20, 30, 20, 30, 30));
        $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'LTBR', 'TBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array(strtoupper($rowC['mco_anterior']), $deseadoDenComp, $rowC['mco_anio'], $deseadoNumComp, $rowC['mco_original']));
        $pdf->SetArrayBorders(null);
        $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

        $pdf->SetRelleno("no");
        $pdf->CheckPageBreak(10);
        $pdf->setX(10);
        $pdf->setAltoLinea(4);
        $pdf->SetAligns(array('L'));
        $pdf->SetWidths(array(160));
        $pdf->SetArrayBorders(array('LTBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array('T1'));
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetWidths(array(30, 35, 35, 30, 30));
        $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'LTBR', 'TBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre'), utf8Decode('Total')));
        $pdf->Row(array('', $rowC['mcot_numeradorT2'], '', $rowC['mcot_numeradorT4'], $deseadoNumComp));
        $pdf->SetAligns(array('L'));
        $pdf->SetWidths(array(160));
        $pdf->SetArrayBorders(array('LTBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array('T0'));
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetWidths(array(30, 35, 35, 30, 30));
        $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'LTBR', 'TBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre'), utf8Decode('Total')));
        $pdf->Row(array('', $rowC['mcot_denominadorT2'], '', $rowC['mcot_denominadorT4'], $deseadoDenComp));
        $pdf->SetArrayBorders(null);
        $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

        $pdf->SetFont('UniviaPro-Bold', 'B', 6);
        $pdf->Row(array('RESULTADO PARCIAL'));
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetWidths(array(30, 35, 35, 30, 30));
        $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'LTBR', 'TBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre'), utf8Decode('Total')));
        $pdf->Row(array('', round(($totalT2),4), '', round(($totalT4),4), $rowC['mco_original']));
        $pdf->SetArrayBorders(null);
        $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

    } else {
        $pdf->SetRelleno("no");
        $pdf->CheckPageBreak(10);
        $pdf->setX(10);
        $pdf->setAltoLinea(4);
        $pdf->SetWidths(array(130));
        $pdf->SetArrayBorders(null);
        $pdf->SetAligns(array('L'));
        $pdf->SetFont('UniviaPro-Bold', 'B', 6);
        $pdf->Row(array(utf8Decode(' LÍNEA BASE'), ''));
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetWidths(array(20,110));
        $pdf->SetArrayBorders(array('LTBR', 'TBR'));
        $pdf->SetAligns(array('L', 'L'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array(utf8Decode('AÑO'), 'VALOR'));
        $pdf->SetWidths(array(20,110));
        $pdf->SetArrayBorders(array('LTBR', 'TBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array($rowC['mco_lb'], strtoupper($rowC['mco_lbValor'])));
        $pdf->SetArrayBorders(null);
        $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

        $pdf->SetRelleno("no");
        $pdf->CheckPageBreak(10);
        $pdf->setX(10);
        $pdf->setAltoLinea(4);
        $pdf->SetWidths(array(130));
        $pdf->SetArrayBorders(null);
        $pdf->SetAligns(array('L'));
        $pdf->SetFont('UniviaPro-Bold', 'B', 6);
        $pdf->Row(array(utf8Decode(' VALOR PROGRAMADO ANUAL ')));

        $pdf->SetWidths(array(5,125));
        $pdf->SetArrayBorders(array('LTB','TBR'));
        $pdf->SetAligns(array('L','L'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array('',$rowC['mco_original']));
        $pdf->SetWidths(array(30, 35, 35,30));
        $pdf->SetArrayBorders(null);
        $pdf->Row(array('', '','', ''));

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetWidths(array(30, 35, 35, 30));
        $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR', 'TBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre')));
        $pdf->SetWidths(array(30, 35, 35, 30));
        $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR', 'TBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array('', $rowC['mcot_modificadoT2'], '', $rowC['mcot_modificadoT4']));
        $pdf->SetArrayBorders(null);
        $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

        $pdf->SetFont('UniviaPro-Bold', 'B', 6);
        $pdf->Row(array('RESULTADO PARCIAL'));
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetWidths(array(30, 35, 35, 30, 30));
        $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'LTBR', 'TBR'));
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre'), utf8Decode('Total')));
        $pdf->Row(array('', $rowC['mcot_modificadoT2'], '', $rowC['mcot_modificadoT4'], $rowC['mco_original']));
        $pdf->SetArrayBorders(null);
        $pdf->Cell(280, 4, '', 0, 1, "L", $fill);
    }

    $mediosVerUR = '';
    $supuestoUR = '';

    $supMeCom = new AccesoDatos();
    $supMeCom->setSql('
        SELECT m.gpo_id, m.ur_id, mvcm_nombre
        FROM (
            SELECT gpo_id, ur_id, prg_id AS prgs_id, spr_id AS sprs_id, pdf_id, spr_nombre
            FROM cat_Subprograma
            WHERE prg_id = :prgs_id AND spr_id = :spr_id
        ) spr
        LEFT JOIN cat_MedioVerificacionComponente m USING(gpo_id, ur_id, prgs_id, sprs_id)
        WHERE prgs_id = :prgs_id AND sprs_id = :spr_id AND epfi_id = :epfi_id
        AND (mvcm_id IS NOT NULL) AND mvcm_trimestreCierre = :mvcm
        ORDER BY prgs_id, sprs_id;
    ');
    $parametros = [
        ':prgs_id' => $_SESSION[_S]['clasAdm-prg'],
        ':spr_id' => $rowC['sprs_id'],
        ':epfi_id' => $rowC['ipf_id'],
        ':mvcm' => $_POST['eri_trimestre'],
    ];
    $supMeCom->conectar();
    $supMeCom->consultar($parametros);
    $supMeCom->desconectar();

    while ($supMediosC = $supMeCom->extraer()) {
        $mediosVerUR .= "UR " . $supMediosC['gpo_id'] . $supMediosC['ur_id'] . " " . $supMediosC['mvcm_nombre'] . ", ";
    }

    $supMeCom2 = new AccesoDatos();
    $supMeCom2->setSql('
        SELECT s.gpo_id, s.ur_id, spc_nombre
        FROM (
            SELECT gpo_id, ur_id, prg_id AS prgs_id, spr_id AS sprs_id, pdf_id, spr_nombre
            FROM cat_Subprograma
            WHERE prg_id = :prgs_id AND spr_id = :spr_id
        ) spr
        LEFT JOIN cat_SupuestoComponente s USING(gpo_id, ur_id, prgs_id, sprs_id)
        WHERE prgs_id = :prgs_id AND sprs_id = :spr_id
        AND (spc_id IS NOT NULL) AND spc_trimestreCierre = :spc
        ORDER BY prgs_id, sprs_id;
    ');
    $parametros = [
        ':prgs_id' => $_SESSION[_S]['clasAdm-prg'],
        ':spr_id' => $rowC['sprs_id'],
        ':spc' => $_POST['eri_trimestre'],
    ];
    $supMeCom2->conectar();
    $supMeCom2->consultar($parametros);
    $supMeCom2->desconectar();

    while ($supMediosC2 = $supMeCom2->extraer()) {
        $supuestoUR .= "UR " . $supMediosC2['gpo_id'] . $supMediosC2['ur_id'] . " " . $supMediosC2['spc_nombre'] . ", ";
    }

    if ($prodFinal != $rowC['pdf_id']) {

        $prodFinal = $rowC['pdf_id'];

        $contInd = 1;
        $indicadorCom = new AccesoDatos();
        $indicadorCom->conectar();
        $indicadorCom->setSql('
            SELECT COUNT(pdf_id) AS numInd,sprs_id, pdf_id, pdf_mir AS componente
            FROM  cat_Subprogramas
            INNER JOIN cat_ProductoFinal USING (pdf_id)
            INNER JOIN enc_ProductoFinalIndicador e USING (pdf_id)
            LEFT JOIN cat_MetaComponente USING (prgs_id, sprs_id, epfi_id)
            LEFT JOIN cat_Indicador USING(nim_id,ind_id)
            LEFT JOIN cat_TipoIndicador USING(tpi_id)
            LEFT JOIN cat_Dimension USING(dim_id)
            LEFT JOIN cat_FrecuenciaMedicion USING(frm_id)
            LEFT JOIN cat_SentidoEsperado USING(ses_id)
            WHERE prgs_id = ? AND pdf_id=? AND e.trim_'. $_POST['eri_trimestre'] .'=? AND e.' . $and . ' AND mco_original >= 0
            GROUP BY prgs_id,sprs_id,pdf_id
            ORDER BY epfi_orden, sprs_id, pdf_id;
        ');
        $parametros = [
            $_SESSION[_S]['clasAdm-prg'],
            $prodFinal,
            1
        ];
        $indicadorCom->consultar($parametros);
        $datos = $indicadorCom->extraer();
        $numIndicadoresCom = $datos['numInd'];
        $indicadorCom->desconectar();
    }
    $pdf->SetArrayBorders(null);
    $pdf->Cell(280, 3, '', 0, 1, "L", $fill);

    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);
    $pdf->SetFillColor(199, 209, 219);
    $pdf->setBorders(0);
    $pdf->setAltoLinea(4);
    $pdf->SetWidths(array(30, 180));
    $pdf->SetAligns(array('L', 'L'));
    $pdf->setFuentes($fuentes);
    $pdf->Row(array(utf8Decode('MEDIOS DE VERIFICACIÓN: '), strtoupper(utf8Decode($mediosVerUR))));
    $pdf->SetRelleno("no");
    $pdf->setBorders(0);
    $pdf->SetFillColor(255, 255, 255);

    $pdf->Cell(280, 3, '', 0, 1, "L", $fill);

    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);
    $pdf->SetFillColor(199, 209, 219);
    $pdf->setBorders(0);
    $pdf->setAltoLinea(4);
    $pdf->SetWidths(array(16, 164));
    $pdf->SetAligns(array('L', 'L'));
    $pdf->setFuentes($fuentes);
    $pdf->Row(array(utf8Decode('SUPUESTOS: '), strtoupper(utf8Decode($supuestoUR))));
    $pdf->SetRelleno("no");
    $pdf->setBorders(0);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(280, 2, '', 0, 1, "L", $fill);

    /*DETALLES A NIVEL ACTIVIDAD*/
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);
    $pdf->SetRelleno("no");
    $pdf->setBorders(0);
    $pdf->SetFillColor(255, 255, 255);

    if ($contInd == $numIndicadoresCom) {
        $metaAct = new AccesoDatos();
        $metaAct->setSql('
            SELECT pdf_id, pdi_id, pii_id, pdi_mir, ind_id, ind_nombre,MAX( macm_descripcion), ind_nombreMIR, ind_definicion,
                ind_formulaCalculo, tpi_id, tpi_nombre, dim_id, dim_nombre, frm_id, frm_nombre, ses_id, ses_nombre,
                CONCAT("", SUM(macm_original), "\n") AS total, tiv_id,tiv_nombre, umi_id, umi_nombre,
                gpo_id, ur_id, ma.macm_original, macm_numerador, macm_denominador, macm_lbValor,
                macm_lb, macm_anio, macm_numeradorT1, macm_numeradorT2, macm_numeradorT3, macm_numeradorT4, macm_denominadorT1, macm_denominadorT2, 
                macm_denominadorT3, macm_denominadorT4, macm_modificadoT1, macm_modificadoT2, macm_modificadoT3, macm_modificadoT4, macm_lbIndicador
            FROM cat_ProductoIntermedio
            INNER JOIN vin_ProductoIntermedioIndicador v USING(pdi_id)
            INNER JOIN vin_ProductoFinalProductoIntermedio vpfi USING(pdf_id,pdi_id)
            LEFT JOIN (
                SELECT pii_id, gpo_id, ur_id, aux.macm_original, macm_descripcion, a.macm_numerador, a.macm_denominador, a.macm_lbValor,
                    a.macm_lb, a.macm_anio, macm_numeradorT1, macm_numeradorT2, macm_numeradorT3, macm_numeradorT4, macm_denominadorT1,
                    macm_denominadorT2, macm_denominadorT3, macm_denominadorT4, macm_modificadoT1, macm_modificadoT2, macm_modificadoT3,
                    macm_modificadoT4, a.macm_lbIndicador, tri_id
                FROM cat_MetaActividadMir a
                INNER JOIN aux_HistoricoMetaActividadMir aux USING(gpo_id, ur_id, pii_id)
                WHERE tri_id='. $trim .'
                GROUP BY  gpo_id, ur_id,  pii_id,a.macm_id
            )ma USING(pii_id)
            LEFT JOIN cat_Indicador USING(nim_id,ind_id)
            LEFT JOIN cat_TipoIndicador USING(tpi_id)
            LEFT JOIN cat_TipoValor USING(tiv_id)
            LEFT JOIN cat_Dimension USING(dim_id)
            LEFT JOIN cat_FrecuenciaMedicion USING(frm_id)
            LEFT JOIN cat_SentidoEsperado USING(ses_id)
            LEFT JOIN cat_UnidadMedidaIndicador USING(umi_id)
            WHERE pdf_id = ? AND v.trim_'. $trim .' = ?
            AND vpfi.sts_id= ? AND macm_original >= 0 AND v.' . $and . '
            GROUP BY gpo_id, ur_id, pdi_id,pii_id, ind_id
            ORDER BY gpo_id,ur_id,pii_orden, pdf_id, pdi_id, ind_id;
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
        $supuestoURAct = '';
        $mediosVerURAct = '';
        $anteriorA = '';
        $anteriorAct = '';
        $deseadoDenAct = 0;
        $deseadoNumAct = 0;
        $totalAT1 = 0;
        $totalAT2 = 0;
        $totalAT3 = 0;
        $totalAT4 = 0;

        while ($Actividad = $metaAct->extraer()) {
            if ($Actividad['total'] != NULL) {
                if ($anteriorAct != $Actividad['pdi_id']) {
                    $pdf->SetRelleno("si");
                    $pdf->CheckPageBreak(10);
                    $pdf->setX(10);
                    $pdf->SetFillColor(69, 247, 255);
                    $pdf->setBorders(1);
                    $pdf->setAltoLinea(2.5);
                    $pdf->SetWidths(array(280));
                    $pdf->SetAligns(array('C'));
                    $pdf->SetFont('UniviaPro-Bold', 'B', 7);
                    $pdf->Row(array(utf8Decode('ACTIVIDAD ') . $contadorAct . ' ID INTERMEDIO ' . $Actividad['pdi_id']));
                    $pdf->SetRelleno("no");
                    $pdf->setBorders(0);
                    $pdf->SetFillColor(255, 255, 255);
                }

                $pdf->setX(10);
                $pdf->SetFont('UniviaPro-Bold', 'B', 6);
                $pdf->Cell(27, 10, utf8Decode('RESUMEN NARRATIVO: '), 0, 0, "L", $fill);
                $pdf->SetFont('UniviaPro-Regular', '', 6);
                $pdf->Cell(38, 10, strtoupper(utf8Decode($Actividad['pdi_mir'])), 0, 1, "L", $fill);

                $pdf->setX(10);
                $pdf->SetFont('UniviaPro-Bold', 'B', 6);
                $pdf->Cell(27, 8, utf8Decode('INDICADOR ' . strtoupper($Actividad['ind_id'] . ' NIVEL 4:')), 0, 0, "L", $fill);
                $pdf->SetFont('UniviaPro-Regular', '', 6);
                $pdf->Cell(27, 8, utf8Decode(strtoupper(($Actividad['ind_nombreMIR']))), 0, 1, "L", $fill);

                $pdf->SetRelleno("no");
                $pdf->CheckPageBreak(10);
                $pdf->setX(10);
                $pdf->SetFillColor(199, 209, 219);
                $pdf->SetArrayBorders(array('LT', 'T', 'T', 'T', 'T', 'T', 'T', 'TR'));
                $pdf->setAltoLinea(4);
                $pdf->SetWidths(array(25, 29, 29, 29, 29, 35, 29, 60));
                $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C' , 'C', 'L'));
                $pdf->SetFont('UniviaPro-Bold', 'B', 6);
                $pdf->Row(array('UR', 'TIPO', utf8Decode('DIMENSIÓN'), 'FRECUENCIA', 'TIPO DE VALOR', 'UNIDAD DE MEDIDA DEL INDICADOR', 'META', utf8Decode('MÉTODO DE CÁLCULO')));
                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetArrayBorders(null);
                $pdf->SetArrayBorders(array('LB', 'B', 'B', 'B', 'B', 'B', 'B', 'BR'));
                $pdf->SetFont('UniviaPro-Regular', '', 6);
                $pdf->Row(array(strtoupper(utf8Decode($Actividad['gpo_id'].$Actividad['ur_id'])), utf8Decode($Actividad['tpi_nombre']), $Actividad['dim_nombre'], $Actividad['frm_nombre'], $Actividad['tiv_nombre'], utf8Decode($Actividad['umi_nombre']), $Actividad['total'],utf8Decode($Actividad['ind_formulaCalculo'])));
                $pdf->SetArrayBorders(null);
                $pdf->Cell(280, 3, '', 0, 1, "L", $fill);
                $pdf->Ln();

                if ($Actividad['umi_id'] == 2) {
                    $totalAT1 = ($Actividad['macm_denominadorT1'] != 0 ? (($Actividad['macm_numeradorT1'] / $Actividad['macm_denominadorT1'])*100) : 0);
                    $totalAT2 = ($Actividad['macm_denominadorT2'] != 0 ? (($Actividad['macm_numeradorT2']/$Actividad['macm_denominadorT2'])*100) : 0);
                    $totalAT3 = ($Actividad['macm_denominadorT3'] != 0 ? (($Actividad['macm_numeradorT3'] / $Actividad['macm_denominadorT3'])*100) : 0);
                    $totalAT4 = ($Actividad['macm_denominadorT4'] != 0 ? (($Actividad['macm_numeradorT4'] / $Actividad['macm_denominadorT4'])*100) : 0);
                } else if ($Actividad['umi_id'] == 3 || $Actividad['umi_id'] == 4) {
                    $totalAT1 = ($Actividad['macm_denominadorT1'] != 0 ? ($Actividad['macm_numeradorT1'] / $Actividad['macm_denominadorT1']) : 0);
                    $totalAT2 = ($Actividad['macm_denominadorT2'] != 0 ? ($Actividad['macm_numeradorT2'] / $Actividad['macm_denominadorT2']) : 0);
                    $totalAT3 = ($Actividad['macm_denominadorT3'] != 0 ? ($Actividad['macm_numeradorT3'] / $Actividad['macm_denominadorT3']) : 0);
                    $totalAT4 = ($Actividad['macm_denominadorT4'] != 0 ? ($Actividad['macm_numeradorT4'] / $Actividad['macm_denominadorT4']) : 0);
                } else if ($Actividad['umi_id'] == 5) {
                    $totalAT1 = ($Actividad['macm_denominadorT1'] != 0 ? ((($Actividad['macm_numeradorT1'] / $Actividad['macm_denominadorT1'])-1)*100) : 0);
                    $totalAT2 = ($Actividad['macm_denominadorT2'] != 0 ? ((($Actividad['macm_numeradorT2'] / $Actividad['macm_denominadorT2'])-1)*100) : 0);
                    $totalAT3 = ($Actividad['macm_denominadorT3'] != 0 ? ((($Actividad['macm_numeradorT3'] / $Actividad['macm_denominadorT3'])-1)*100) : 0);
                    $totalAT4 = ($Actividad['macm_denominadorT4'] != 0 ? ((($Actividad['macm_numeradorT4'] / $Actividad['macm_denominadorT4'])-1)*100) : 0);
                } else {
                    $totalAT1 = $Actividad['macm_modificadoT1'];
                    $totalAT2 = $Actividad['macm_modificadoT2'];
                    $totalAT3 = $Actividad['macm_modificadoT3'];
                    $totalAT4 = $Actividad['macm_modificadoT4'];
                }

                $trimestre1Cal1 = $Actividad['macm_modificadoT1'] != 0 ? $Actividad['macm_modificadoT1'] : $Actividad['macm_numeradorT1'];
                $trimestre2Cal1 = $Actividad['macm_modificadoT2'] != 0 ? $Actividad['macm_modificadoT2'] : $Actividad['macm_numeradorT2'];
                $trimestre3Cal1 = $Actividad['macm_modificadoT3'] != 0 ? $Actividad['macm_modificadoT3'] : $Actividad['macm_numeradorT3'];
                $trimestre4Cal1 = $Actividad['macm_modificadoT4'] != 0 ? $Actividad['macm_modificadoT4'] : $Actividad['macm_numeradorT4'];

                $trimestre1Cal2 = $Actividad['macm_denominadorT1'] != '' ? $Actividad['macm_denominadorT1'] : 0;
                $trimestre2Cal2 = $Actividad['macm_denominadorT2'] != '' ? $Actividad['macm_denominadorT2'] : 0;
                $trimestre3Cal2 = $Actividad['macm_denominadorT3'] != '' ? $Actividad['macm_denominadorT3'] : 0;
                $trimestre4Cal2 = $Actividad['macm_denominadorT4'] != '' ? $Actividad['macm_denominadorT4'] : 0;

                switch ($Actividad['umi_id']) {
                    case 2:
                    case 3:
                    case 4: // PORCENTAJE, PROMEDIO Y RAZÓN
                        if ($trimestre1Cal1 != NULL && $trimestre2Cal1 != NULL && $trimestre3Cal1 != NULL && $trimestre4Cal1 != NULL && $trimestre1Cal2 != NULL &&
                            $trimestre2Cal2 != NULL && $trimestre3Cal2 != NULL && $trimestre4Cal2 != NULL) {

                            if ($Actividad['umi_id'] == 2) {
                                $totalAT1 = (($trimestre1Cal2 != 0) ? number_format((($trimestre1Cal1 / $trimestre1Cal2) * 100), 4) : '0');
                                $totalAT2 = (($trimestre2Cal2 != 0) ? number_format((($trimestre2Cal1 / $trimestre2Cal2) * 100), 4) : '0');
                                $totalAT3 = (($trimestre3Cal2 != 0) ? number_format((($trimestre3Cal1 / $trimestre3Cal2) * 100), 4) : '0');
                                $totalAT4 = (($trimestre4Cal2 != 0) ? number_format((($trimestre4Cal1 / $trimestre4Cal2) * 100), 4) : '0');
                            } else {
                                $totalAT1 = (($trimestre1Cal2 != 0) ? number_format(($trimestre1Cal1 / $trimestre1Cal2), 4) : '0');
                                $totalAT2 = (($trimestre2Cal2 != 0) ? number_format(($trimestre2Cal1 / $trimestre2Cal2), 4) : '0');
                                $totalAT3 = (($trimestre3Cal2 != 0) ? number_format(($trimestre3Cal1 / $trimestre3Cal2), 4) : '0');
                                $totalAT4 = (($trimestre4Cal2 != 0) ? number_format(($trimestre4Cal1 / $trimestre4Cal2), 4) : '0');
                            }
                        } else {
                            if ($Actividad['umi_id']) {
                                $totalAT1 = "0%";
                                $totalAT2 = "0%";
                                $totalAT3 = "0%";
                                $totalAT4 = "0%";
                                $resParcialTo = "0%";
                            } else {
                                $totalAT1 = "0";
                                $totalAT2 = "0";
                                $totalAT3 = "0";
                                $totalAT4 = "0";
                                $resParcialTo = "0";
                            }
                        }

                        break;
                    case 5: // TASA
                        if ($trimestre1Cal1 != NULL && $trimestre2Cal1 != NULL && $trimestre3Cal1 != NULL && $trimestre4Cal1 != NULL && $trimestre1Cal2 != NULL &&
                        $trimestre2Cal2 != NULL && $trimestre3Cal2 != NULL && $trimestre4Cal2 != NULL) {

                        $totalAT1 = (($trimestre1Cal2 != 0) ? number_format(((($trimestre1Cal1 / $trimestre1Cal2) - 1) * 100), 4) : '0');
                        $totalAT2 = (($trimestre2Cal2 != 0) ? number_format(((($trimestre2Cal1 / $trimestre2Cal2) - 1) * 100), 4) : '0');
                        $totalAT3 = (($trimestre3Cal2 != 0) ? number_format(((($trimestre3Cal1 / $trimestre3Cal2) - 1) * 100), 4) : '0');
                        $totalAT4 = (($trimestre4Cal2 != 0) ? number_format(((($trimestre4Cal1 / $trimestre4Cal2) - 1) * 100), 4) : '0');

                        $totalAT1 .= "%";
                        $totalAT2 .= "%";
                        $totalAT3 .= "%";
                        $totalAT4 .= "%";
                    } else {
                        $totalAT1 = "0%";
                        $totalAT2 = "0%";
                        $totalAT3 = "0%";
                        $totalAT4 = "0%";
                    }
                        break;
                    default:
                        $trimestre1 = $Actividad['macm_modificadoT1'] != '' ? $Actividad['macm_modificadoT1'] : 0;
                        $trimestre2 = $Actividad['macm_modificadoT2'] != '' ? $Actividad['macm_modificadoT2'] : 0;
                        $trimestre3 = $Actividad['macm_modificadoT3'] != '' ? $Actividad['macm_modificadoT3'] : 0;
                        $trimestre4 = $Actividad['macm_modificadoT4'] != '' ? $Actividad['macm_modificadoT4'] : 0;

                        if ($trimestre1 != NULL && $trimestre2 != NULL && $trimestre3 != NULL && $trimestre4 != NULL) {
                                $totalAT1 = number_format(($trimestre1), 4);
                                $totalAT2 = number_format(($trimestre2), 4);
                                $totalAT3 = number_format(($trimestre3), 4);
                                $totalAT4 = number_format(($trimestre4), 4);
                                $totalAT1 .= "%";
                                $totalAT2 .= "%";
                                $totalAT3 .= "%";
                                $totalAT4 .= "%";
                        } else {
                            $totalAT1 = "0%";
                            $totalAT2 = "0%";
                            $totalAT3 = "0%";
                            $totalAT4 = "0%";
                            $resParcialTo = "0%";
                        }
                        break;
                }

                if ($Actividad['tiv_id'] == 1) {
                    $deseadoNumAct = number_format($trimestre1Cal1 + $trimestre2Cal1 + $trimestre3Cal1 + $trimestre4Cal1, 4);
                    $deseadoDenAct = number_format($trimestre1Cal2 + $trimestre2Cal2 + $trimestre3Cal2 + $trimestre4Cal2, 4);
                } else if ($Actividad['tiv_id'] == 2) {
                    $deseadoNumAct = number_format($trimestre4Cal1, 4);
                    $deseadoDenAct = number_format($trimestre4Cal2, 4);
                }

                if ($Actividad['umi_id'] == 2 || $Actividad['umi_id'] == 3 || $Actividad['umi_id'] == 4) {
                    # code...
                    $pdf->SetRelleno("no");
                    $pdf->CheckPageBreak(10);
                    $pdf->setX(10);
                    $pdf->setAltoLinea(4);
                    $pdf->SetWidths(array(130));
                    $pdf->SetArrayBorders(null);
                    $pdf->SetAligns(array('L','L','L','L'));
                    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
                    $pdf->Row(array(utf8Decode('LÍNEA BASE'), ''));
                    $pdf->SetAligns(array('L','L','L','L'));
                    $pdf->SetWidths(array(30, 35, 35, 30));
                    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array(utf8Decode('AÑO'), 'NUMERADOR', 'DENOMINADOR', 'VALOR LB'));
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetWidths(array(30, 35, 35, 30));
                    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array(strtoupper($Actividad['macm_lb']), $Actividad['macm_numerador'], $Actividad['macm_denominador'], $Actividad['macm_lbIndicador']));
                    $pdf->SetArrayBorders(null);
                    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);
                
                    $pdf->SetRelleno("no");
                    $pdf->CheckPageBreak(10);
                    $pdf->setX(10);
                    $pdf->setAltoLinea(4);
                    $pdf->SetWidths(array(130));
                    $pdf->SetArrayBorders(null);
                    $pdf->SetAligns(array('L'));
                    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
                    $pdf->Row(array(utf8Decode(' VALOR DESEADO'), ''));
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetAligns(array('L','L','L','L'));
                    $pdf->SetWidths(array(30, 35, 35, 30));
                    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'TBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array('NUMERADOR', 'DENOMINADOR', APP_EJERCICIO));
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetWidths(array(30, 35, 35, 30));
                    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array($deseadoNumAct, $deseadoDenAct, $Actividad['macm_original']));
                    $pdf->SetArrayBorders(null);
                    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);
                
                    $pdf->SetRelleno("no");
                    $pdf->CheckPageBreak(10);
                    $pdf->setX(10);
                    $pdf->setAltoLinea(4);
                    $pdf->SetAligns(array('L'));
                    $pdf->SetWidths(array(160));
                    $pdf->SetArrayBorders(array('LTBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array('NUMERADOR'));
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetWidths(array(30, 35, 35, 30, 30));
                    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'LTBR', 'TBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre'), utf8Decode('Total')));
                    $pdf->Row(array($trimestre1Cal1, $trimestre2Cal1, $trimestre3Cal1, $trimestre4Cal1, $deseadoNumAct));
                    $pdf->SetAligns(array('L'));
                    $pdf->SetWidths(array(160));
                    $pdf->SetArrayBorders(array('LTBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array('DENOMINADOR'));
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetWidths(array(30, 35, 35, 30, 30));
                    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'LTBR', 'TBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre'), utf8Decode('Total')));
                    $pdf->Row(array($trimestre1Cal2, $trimestre2Cal2, $trimestre3Cal2, $trimestre4Cal2, $deseadoDenAct));
                    $pdf->SetArrayBorders(null);
                    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

                    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
                    $pdf->Row(array('RESULTADO PARCIAL'));
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetWidths(array(30, 35, 35, 30, 30));
                    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'LTBR', 'TBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre'), utf8Decode('Total')));
                    $pdf->Row(array($totalAT1, $totalAT2, $totalAT3, $totalAT4, $Actividad['macm_original']));
                    $pdf->SetArrayBorders(null);
                    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

                } else if ($Actividad['umi_id'] == 5) {
                    # code...
                    $pdf->SetRelleno("no");
                    $pdf->CheckPageBreak(10);
                    $pdf->setX(10);
                    $pdf->setAltoLinea(4);
                    $pdf->SetWidths(array(130));
                    $pdf->SetArrayBorders(null);
                    $pdf->SetAligns(array('L','L','L','L'));
                    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
                    $pdf->Row(array(utf8Decode('LÍNEA BASE'), ''));
                    $pdf->SetAligns(array('L','L','L','L', 'L'));
                    $pdf->SetWidths(array(20, 30, 20, 30, 30));
                    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR', 'TBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array(utf8Decode('AÑO T-1'), utf8Decode('VALOR AÑO T-1'), utf8Decode('AÑO T0'), utf8Decode('VALOR AÑO T0'), 'VALOR INDICADOR'));
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetWidths(array(20, 30, 20, 30, 30));
                    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR', 'TBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array(strtoupper($Actividad['macm_lb']), $Actividad['macm_lbValor'], $Actividad['macm_anterior'], $Actividad['macm_anteriorValor'], $Actividad['macm_lbIndicador']));
                    $pdf->SetArrayBorders(null);
                    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);
                
                    $pdf->SetRelleno("no");
                    $pdf->CheckPageBreak(10);
                    $pdf->setX(10);
                    $pdf->setAltoLinea(4);
                    $pdf->SetWidths(array(130));
                    $pdf->SetArrayBorders(null);
                    $pdf->SetAligns(array('L'));
                    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
                    $pdf->Row(array(utf8Decode(' VALORES PROGRAMADOS'), ''));
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetAligns(array('L','L','L','L', 'L'));
                    $pdf->SetWidths(array(20, 30, 20, 30, 30));
                    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR', 'TBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array(utf8Decode('AÑO T-0'), utf8Decode('VALOR AÑO T-0'), utf8Decode('AÑO T1'), utf8Decode('VALOR AÑO T1'), 'VALOR INDICADOR'));
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetWidths(array(20, 30, 20, 30, 30));
                    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR', 'TBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array(strtoupper($Actividad['macm_anterior']), $deseadoDenComp, $Actividad['macm_anio'], $deseadoNumacmmp, $Actividad['macm_original']));
                    $pdf->SetArrayBorders(null);
                    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);
                
                    $pdf->SetRelleno("no");
                    $pdf->CheckPageBreak(10);
                    $pdf->setX(10);
                    $pdf->setAltoLinea(4);
                    $pdf->SetAligns(array('L'));
                    $pdf->SetWidths(array(160));
                    $pdf->SetArrayBorders(array('LTBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array('T1'));
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetWidths(array(30, 35, 35, 30));
                    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre'), utf8Decode('Total')));
                    $pdf->Row(array($trimestre1Cal1, $trimestre2Cal1, $trimestre3Cal1, $trimestre4Cal1, $deseadoNumAct));
                    $pdf->SetAligns(array('L'));
                    $pdf->SetWidths(array(160));
                    $pdf->SetArrayBorders(array('LTBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array('T0'));
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetWidths(array(30, 35, 35, 30));
                    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre'), utf8Decode('Total')));
                    $pdf->Row(array($trimestre1Cal2, $trimestre2Cal2, $trimestre3Cal2, $trimestre4Cal2), $deseadoDenAct);
                    $pdf->SetArrayBorders(null);
                    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

                    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
                    $pdf->Row(array('RESULTADO PARCIAL'));
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetWidths(array(30, 35, 35, 30, 30));
                    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'LTBR', 'TBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre'), utf8Decode('Total')));
                    $pdf->Row(array($totalAT1, $totalAT2, $totalAT3, $totalAT4, $Actividad['macm_original']));
                    $pdf->SetArrayBorders(null);
                    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

                } else {
                    $pdf->SetRelleno("no");
                    $pdf->CheckPageBreak(10);
                    $pdf->setX(10);
                    $pdf->setAltoLinea(4);
                    $pdf->SetWidths(array(130));
                    $pdf->SetArrayBorders(null);
                    $pdf->SetAligns(array('L'));
                    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
                    $pdf->Row(array(utf8Decode(' LÍNEA BASE'), ''));
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetWidths(array(20,110));
                    $pdf->SetArrayBorders(array('LTBR', 'TBR'));
                    $pdf->SetAligns(array('L', 'L'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array(utf8Decode('AÑO'), 'VALOR'));
                    $pdf->SetWidths(array(20,110));
                    $pdf->SetArrayBorders(array('LTBR', 'TBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array($Actividad['macm_lb'], strtoupper($Actividad['macm_lbValor'])));
                    $pdf->SetArrayBorders(null);
                    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);
                
                    $pdf->SetRelleno("no");
                    $pdf->CheckPageBreak(10);
                    $pdf->setX(10);
                    $pdf->setAltoLinea(4);
                    $pdf->SetWidths(array(130));
                    $pdf->SetArrayBorders(null);
                    $pdf->SetAligns(array('L'));
                    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
                    $pdf->Row(array(utf8Decode(' VALOR PROGRAMADO ANUAL ')));
                    
                    $pdf->SetWidths(array(5,125));
                    $pdf->SetArrayBorders(array('LTB','TBR'));
                    $pdf->SetAligns(array('L','L'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array('',$Actividad['macm_original']));
                    $pdf->SetWidths(array(30, 35, 35,30));
                    $pdf->SetArrayBorders(null);
                    $pdf->Row(array('', '','', ''));
                    
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetWidths(array(30, 35, 35, 30));
                    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR', 'TBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre')));
                    $pdf->SetWidths(array(30, 35, 35, 30));
                    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'TBR', 'TBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array($trimestre1Cal1, $trimestre2Cal1, $trimestre3Cal1, $trimestre4Cal1));
                    $pdf->SetArrayBorders(null);
                    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

                    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
                    $pdf->Row(array('RESULTADO PARCIAL'));
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->SetWidths(array(30, 35, 35, 30, 30));
                    $pdf->SetArrayBorders(array('LTBR', 'LTBR', 'LTBR', 'LTBR', 'TBR'));
                    $pdf->SetFont('UniviaPro-Regular', '', 6);
                    $pdf->Row(array(utf8Decode('1° Trimestre'), utf8Decode('2° Trimestre'), utf8Decode('3° Trimestre'), utf8Decode('4° Trimestre'), utf8Decode('Total')));
                    $pdf->Row(array($totalAT1, $totalAT2, $totalAT3, $totalAT4, $Actividad['macm_original']));
                    $pdf->SetArrayBorders(null);
                    $pdf->Cell(280, 4, '', 0, 1, "L", $fill);

                }

                $supMeAct = new AccesoDatos();
                $supMeAct->setSql('
                    SELECT gpo_id, ur_id,mvac_nombre
                    FROM cat_MedioVerificacionActividad
                    WHERE pdf_id = ? AND pdi_id = ? AND pii_id = ? AND mvac_trimestreCierre = ?
                    ORDER BY pdf_id, pdi_id
                ');
                $parametros = [
                    $pdf_id,
                    $Actividad['pdi_id'],
                    $Actividad['pii_id'],
                    $_POST['eri_trimestre']
                ];
                $supMeAct->conectar();
                $supMeAct->consultar($parametros);
                $supMeAct->desconectar();

                while ($MediosA = $supMeAct->extraer()) {
                    $mediosVerURAct .= "UR " . $MediosA['gpo_id'] . $MediosA['ur_id'] . " " . $MediosA['mvac_nombre'] . ", ";
                }

                $supMeAct2 = new AccesoDatos();
                $supMeAct2->setSql('
                    SELECT gpo_id, ur_id, spa_nombre
                    FROM cat_SupuestoActividad
                    WHERE pdf_id = ? AND pdi_id = ? AND spa_trimestreCierre = ?
                    ORDER BY pdf_id, pdi_id
                ');
                $parametros = [
                    $pdf_id,
                    $Actividad['pdi_id'],
                    $_POST['eri_trimestre']
                ];
                $supMeAct2->conectar();
                $supMeAct2->consultar($parametros);
                $supMeAct2->desconectar();

                while ($supMediosA = $supMeAct2->extraer()) {
                    $supuestoURAct .= "UR " . $supMediosA['gpo_id'] . $supMediosA['ur_id'] . " " . $supMediosA['spa_nombre'] . ", ";
                }

                if ($pdi_id != $Actividad['pdi_id']) {
                    $pdi_id = $Actividad['pdi_id'];
                    $contadorAct++;
                }
                $pdf->SetArrayBorders(null);
                $pdf->Cell(280, 3, '', 0, 1, "L", $fill);

                $pdf->SetRelleno("no");
                $pdf->CheckPageBreak(10);
                $pdf->setX(10);
                $pdf->SetFillColor(199, 209, 219);
                $pdf->setBorders(0);
                $pdf->setAltoLinea(4);
                $pdf->SetWidths(array(30, 180));
                $pdf->SetAligns(array('L', 'L'));
                $pdf->setFuentes($fuentes);
                $pdf->Row(array(utf8Decode('MEDIOS DE VERIFICACIÓN: '), strtoupper(utf8Decode($mediosVerURAct))));
                $pdf->SetRelleno("no");
                $pdf->setBorders(0);
                $pdf->SetFillColor(255, 255, 255);

                $pdf->Cell(280, 3, '', 0, 1, "L", $fill);

                $pdf->SetRelleno("no");
                $pdf->CheckPageBreak(10);
                $pdf->setX(10);
                $pdf->SetFillColor(199, 209, 219);
                $pdf->setBorders(0);
                $pdf->setAltoLinea(4);
                $pdf->SetWidths(array(16, 164));
                $pdf->SetAligns(array('L', 'L'));
                $pdf->setFuentes($fuentes);
                $pdf->Row(array(utf8Decode('SUPUESTOS: '), strtoupper(utf8Decode($supuestoURAct))));
                $pdf->SetRelleno("no");
                $pdf->setBorders(0);
                $pdf->SetFillColor(255, 255, 255);

                $pdf->Cell(280, 2, '', 0, 1, "L", $fill);
                $anteriorA = $Actividad['pdf_id'];
                $anteriorAct = $Actividad['pdi_id'];
                $mediosVerURAct = '';
                $supuestoURAct = '';
            }
        }
    }

    $pdf->SetRelleno("no");
    $pdf->setBorders(0);
    $pdf->SetFillColor(255, 255, 255);
    $contInd++;
    $anteriorspr = $rowC['sprs_id'];
    $anteriorcomp = $rowC['pdf_id'];

    /* FIN DETALLE DEl REPORTE A NIVEL ACTIVIDAD */
}

$pdf->Output();
