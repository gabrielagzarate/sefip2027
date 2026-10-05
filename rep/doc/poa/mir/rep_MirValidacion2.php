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
error_reporting(E_ERROR);
class reportetabla extends PDF
{

    public $gpo_id = '';
    public $ur_id = '';
    private $showFooter = true; // Variable para controlar si se muestra el footer o no

    public function __construct()
    {
        parent::__construct();
    }

    // Función para establecer si se debe mostrar el footer
    public function setShowFooter($show)
    {
        $this->showFooter = $show;
    }

    public function Footer()
    {
        if ($this->showFooter) {
            $this->SetY(-10);

            $this->Cell(0, 5, '', 0, 0, 'C');
            $this->SetX(10);
            $this->Image('../../../../img/cintilla.png', 10, 202, 280, 2, 'png');
            $this->SetX(9);
            $this->SetFont('arial', '', 9);
            $this->Cell(0, 12, "Decreto 22", 0, 1, 'L');
            $this->SetFont('UniviaPro-Regular', '', 5);
        }
    }

    public function encabezados($ix, $ytmp)
    {
        $this->SetXY($ix + 17, $ytmp + 2);
        $ytmp = $this->GetY();
        $this->ln(23);
        $this->SetXY($ix + 17, $ytmp + 2);
        $ytmp = $this->GetY();
        $this->ln(23);
    }

    public function Header()
    { //Encabezado del documento
        $this->SetMargins(10, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $row = $this->getDatos();
        $conexion2 = new AccesoDatos();
        $conexion2->conectar();
        $conexion2->setSql('
            SELECT  a.emi_id
            FROM cat_Mir a
            INNER JOIN vin_ProgramasMir USING(mir_id)
            INNER JOIN vin_UrProgramaMir vupm USING(vpm_id,prgs_id)
            WHERE vupm.gpo_id=?
            AND vupm.ur_id=?
            AND prgs_id =?
        ');
        $parametros = [
            $_SESSION[_S]['clasAdm-gpo'],
            $_SESSION[_S]['clasAdm-ur'],
            $_SESSION[_S]['clasAdm-prg']
        ];
        $conexion2->consultar($parametros);
        $conexion2->desconectar();
        $detalle = $conexion2->extraer();
        if ($detalle['emi_id'] != 9) {
            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('UniviaPro-Bold', 'B', 50);
            $this->SetTextColor(200, 200, 200);
            $this->RotatedText($ix + 30, $iy + 160, utf8Decode('EN REVISIÓN'), 45);
            $this->RotatedText($ix + 150, $iy + 160, utf8Decode('EN REVISIÓN'), 45);
            $this->RotatedText($ix, $iy + 80, utf8Decode('EN REVISIÓN'), 45);
            $this->RotatedText($ix + 150, $iy + 80, utf8Decode('EN REVISIÓN'), 45);
        }
        $this->SetMargins(10, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 12);
        $this->SetXY($ix + 17, $iy + 4);
        $ytmp = $this->GetY();

        $this->Cell(264, 5, utf8Decode(''), 0, 1, 'L');
        //Ajuste para añadir logos
        if (isset($_POST['logo'])) {
            if ($_POST['logo'] == 2) {
                $this->setShowFooter(true);
                $this->Image('../../../../img/Legislativo_PNG.png', $ix, $iy, 80.50, 50.20, 'png');
                $this->SetFont('arial', 'B', 10);
                $this->Cell(280, 52, utf8Decode('ANEXO 1. MATRÍZ DE INDICADORES PARA RESULTADOS'), 0, 1, 'C');
                $this->encabezados($ix - 35, $ytmp - 10);

            }if ($_POST['logo'] == 3) {
                $this->setShowFooter(false);
                $this->Image('../../../../img/LXVI Legislatura.jpg', $ix + 80, $iy + 5, 130, 40, 'jpg');
                $this->SetTextColor(0, 0, 0);
                $this->Ln(30);
                $this->SetFont('UniviaPro-Bold', 'B', 10);
                $this->Cell(265, 30, utf8Decode('ANEXO 1. MATRIZ DE INDICADORES PARA RESULTADOS'), 0, 1, 'C');
                $this->Ln(2);
                $this->encabezados($ix, $ytmp);
            }if ($_POST['logo'] == 4) {
                $this->setShowFooter(false);
                $this->SetFont('UniviaPro-Bold', 'B', 10);
                $this->Cell(265, 10, utf8Decode('ANEXO 1. MATRIZ DE INDICADORES PARA RESULTADOS'), 0, 1, 'C');
            }
        } else {
            $this->setShowFooter(true);
            $this->Cell(264, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
            $this->Cell(264, 5, 'PROGRAMA OPERATIVO ANUAL ' . APP_EJERCICIO, 0, 1, 'L');
            $this->Cell(264, 5, utf8Decode('MATRÍZ DE INDICADORES PARA RESULTADOS'), 0, 1, 'L');
            $this->Cell(264, 5, utf8Decode('REPORTE MIR INICIAL'), 0, 1, 'L');
            $this->setRouteImg2(" ");
            $this->Image($this->getRouteImg(), $ix + 170, $iy + 5, '110', '15', 'JPG', '');
            $this->encabezados($ix, $ytmp);

        }

        if ($this->PageNo() > 1) {
            $fill = false;
            $this->ln(30);
            $this->SetY(65);
            $this->Cell(280, 5, '', 'T', '', $fill);
            $this->SetRelleno("no");
            $this->CheckPageBreak(10);
            $this->setX(10);
            $this->SetFillColor(199, 209, 219);
            $this->setBorders('LR');
            $this->setAltoLinea(2.5);
            //Denominación, Método de cálculo,tipo-dimensión-frecuencia-sentido-meta anual

            $this->SetWidths(array(20, 35, 65, 80, 80));
            $this->SetAligns(array('C', 'C', 'C', 'C', 'C'));
            $this->SetFont('UniviaPro-Bold', 'B', 5);
            $this->Row(array('', '', 'INDICADOR ', '', ''));
            $this->SetRelleno("no");
            $this->setBorders(0);
            $this->SetFillColor(255, 255, 255);

            $this->Cell(-1, 5, '', '', '', $fill);
            $this->Cell(56, 5, '', '', '', $fill);
            $this->Cell(65, 5, '', 'T', '', $fill);

            $this->SetRelleno("no");
            $this->CheckPageBreak(10);
            $this->setX(10);
            $this->SetFillColor(199, 209, 219);
            $this->setBorders('LR');
            $this->setAltoLinea(2.5);
            //Denominación, Método de cálculo,tipo-dimensión-frecuencia-sentido-meta anual
            $this->SetWidths(array(20, 35, 20, 23, 22, 80, 80));
            $this->SetAligns(array('C', 'C', 'L', 'L', 'L', 'C', 'C'));
            $this->SetArrayBorders(array('LR', 'LR', 'L', 0, 'R', 'LR', 'LR'));
            $this->SetFont('UniviaPro-Bold', 'B', 5);
            $this->Row(array('NIVEL', 'RESUMEN NARRATIVO',
                utf8Decode('A)ID INDICADOR 1.NOMBRE
            4.DIMENSIÓN
            7.META ANUAL'), utf8Decode('2. FÓRMULA DE CÁLCULO
            5.FRECUENCIA
            '), '3. TIPO
            6.SENTIDO
            ', utf8Decode('MEDIOS DE VERIFICACIÓN'), 'SUPUESTOS'));

            $this->setBorders(1);

            $this->SetArrayBorders(null);
            $this->SetWidths(array(20, 35, 65, 80, 80));
            $this->SetAligns(array('C', 'L', 'L', 'L', 'L')); //Reiniciando los valores cuando cambia de página
            $this->SetRelleno("no");
        }

        $this->yHeader = $this->GetY();
    }
}

$pdf = new reportetabla('L', 'mm', 'Letter');
$pdf->SetProtection(array("print"), "", "WAsN8Kywfj5eAjp");
$pdf->setOrientation('L');
$pdf->AliasNbPages();
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->SetAutoPageBreak(true, 10);
$orden = $PantallMir = $filtro = '';
$pdf->setAltoLinea(1.5);
$pdf->AddPage();
$fill = false;

$pdf->ln();
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetY(65);
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
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

$pdf->SetFont('UniviaPro-Regular', '', 6);
$pdf->SetFont('UniviaPro-Bold', 'B', 6);
$pdf->Cell(140, 5, '', 'LR', 0, 'C', $fill);
$pdf->Cell(140, 5, '', 'LR', 1, 0, $fill);
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

$c1 = new AccesoDatos();
$c1->setSql('
    SELECT eje_nombre,obj_nombre,tem_nombre,prgs_nombre,ods_nombre, spe_nombre, pb.pbf_nombre, prgs_hombres, prgs_mujeres, prgs_total,  
    prgs_hombresO AS total_hombres, prgs_mujeresO AS total_mujeres, prgs_totalO AS total_sprs, prgs_MedioVerificacion,pbO.pbf_nombre AS pbf_nombreO, obp_nombre, spe_nombre
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
    INNEr JOIN cat_ODS  USING (ods_id)
    WHERE prgs_id = ?
    GROUP BY eje_nombre, obj_nombre, tem_nombre, prgs_nombre, spe_nombre, pbf_nombre;
');
$c1->conectar();
$c1->consultar([$_SESSION[_S]['clasAdm-prg']]);
$d1 = $c1->extraer();
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
$pdf->Row(array(utf8Decode('POBLACIÓN O ÁREA DE ENFOQUE')));
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

$pdf->setX(10);
$pdf->SetFont('UniviaPro-Bold', 'B', 6);
$pdf->Cell(42, 5, utf8Decode('Población o área de enfoque potencial:'), 'LT', 0, "L", $fill);
$pdf->SetFont('UniviaPro-Regular', '', 6);
$pdf->Cell(238, 5, utf8Decode($d1['pbf_nombre']), 'TR', 0, "L", $fill);

$pdf->setX(140);
$pdf->SetFont('UniviaPro-Bold', 'B', 6);
$pdf->Cell(42, 5, utf8Decode('Población o área de enfoque objetivo:'), 0, 0, "L", $fill);
$pdf->SetFont('UniviaPro-Regular', '', 6);
$pdf->Cell(238, 5, utf8Decode($d1['pbf_nombreO']), 'R', 1, "L", $fill);

$pdf->setX(10);
$pdf->Cell(85, 5, ' Hombres:   ' . $d1['prgs_hombres'] . '         Mujeres:   ' . $d1['prgs_mujeres'] . '         Total:   ' . $d1['prgs_total'], 'L', 0, "R", $fill);
$pdf->Cell(195, 5, '', 'R', 0, "L", $fill);

$pdf->setX(140);
$pdf->Cell(85, 5, ' Hombres:   ' . $d1['total_hombres'] . '         Mujeres:   ' . $d1['total_mujeres'] . '         Total:   ' . $d1['total_sprs'], 0, 0, "R", $fill);
$pdf->Cell(195, 5, '', 'R', 1, "L", $fill);

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
$pdf->setAltoLinea(2);
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

$pdf->setFuentes($fuentes);
$pdf->Row(array('EJE:', strtoupper(utf8Decode($d1['eje_nombre'])), 'TEMA:', strtoupper(utf8Decode($d1['tem_nombre'])), 'OBJETIVO:', strtoupper(utf8Decode($d1['obj_nombre']))), 'LR');
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

/* FINALIDAD Y PLAN ESTRATEGICO */
$pdf->SetFont('UniviaPro-Bold', 'B', 6);
$pdf->setX(10);
$pdf->Cell(280, 4, utf8Decode('PLAN ESTRATÉGICO SECTORIAL'), 'LR', 2, 'L', $fill);

$pdf->SetRelleno("no");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(199, 209, 219);
$pdf->setBorders('LR');
$pdf->setAltoLinea(2);
$pdf->SetWidths(array(15, 125, 15, 125,));
$pdf->SetAligns(array('L', 'L', 'L', 'L'));

$pdf->setFuentes($fuentes);
$pdf->Row(array('OBJETIVO:', strtoupper(utf8Decode($d1['obp_nombre'])), 'SECTOR:', strtoupper(utf8Decode($d1['spe_nombre']))), 'LR');
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

$pdf->SetRelleno("no");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(199, 209, 219);
$pdf->setBorders('LR');
$pdf->setAltoLinea(2.5);
$pdf->SetWidths(array(25, 255));
$pdf->SetAligns(array('L', 'L'));

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
$pdf->Row(array(utf8Decode('VINCULACIÓN ODS: '), strtoupper(utf8Decode($d1['ods_nombre']))), 'LR');
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

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

$pdf->SetRelleno("no");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(199, 209, 219);
$pdf->setBorders('LR');
$pdf->setAltoLinea(2.5);
//Denominación, Método de cálculo,tipo-dimensión-frecuencia-sentido-meta anual
$pdf->SetWidths(array(20, 35, 65, 80, 80));
$pdf->SetAligns(array('C', 'C', 'C', 'C', 'C'));
$pdf->SetFont('UniviaPro-Bold', 'B', 6);
$pdf->Row(array('', '', 'INDICADOR', '', ''));
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

$pdf->Cell(-1, 5, '', '', '', $fill);
$pdf->Cell(56, 5, '', '', '', $fill);
$pdf->Cell(65, 5, '', 'T', '', $fill);

$pdf->SetRelleno("no");
$pdf->CheckPageBreak(10);
$pdf->setX(10);
$pdf->SetFillColor(199, 209, 219);
$pdf->setBorders('LR');
$pdf->setAltoLinea(2.5);
$pdf->SetWidths(array(20, 35, 20, 23, 22, 80, 80));
$pdf->SetAligns(array('C', 'C', 'L', 'L', 'L', 'C', 'C'));
$pdf->SetArrayBorders(array('LR', 'LR', 'L', 0, 'R', 'LR', 'LR'));
$pdf->SetFont('UniviaPro-Bold', 'B', 5);
$pdf->Row(array('NIVEL', 'RESUMEN NARRATIVO',
    utf8Decode('A)ID INDICADOR 1.NOMBRE
4.DIMENSIÓN
7.META ANUAL'), utf8Decode('2. FÓRMULA DE CÁLCULO
5.FRECUENCIA
'), '3. TIPO
6.SENTIDO
', utf8Decode('MEDIOS DE VERIFICACIÓN'), 'SUPUESTOS'));
$pdf->SetArrayBorders(null);
$pdf->SetRelleno("no");
$pdf->setBorders(0);
$pdf->SetFillColor(255, 255, 255);

/* DETALLE DEL PRIMER NIVEL FIN */
$c1 = new AccesoDatos();
$c1->setSql('
    SELECT
        IFNULL(ind_fin,0) ind_fin, IFNULL(ind_id,0) ind_id,IFNULL(fin_formulaCalculo,\'-\') fin_formulaCalculo,
        IFNULL(tpi_fin,0) tpi_fin, IFNULL(dim_fin,0) dim_fin, IFNULL(frm_fin,0) frm_fin, IFNULL(ses_fin,0) ses_fin,
        IF(ISNULL(mtf_original),"",mtf_original) AS mem_original,mir_resumenNarrativo,
        mediosFin, SupuestosFin, obj_nombre AS fin
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
        INNER JOIN cat_Indicador USING(nim_id,ind_id)
        JOIN cat_TipoIndicador USING(tpi_id)
        JOIN cat_Dimension USING(dim_id)
        JOIN cat_FrecuenciaMedicion USING(frm_id)
        JOIN cat_SentidoEsperado USING(ses_id)
        LEFT JOIN cat_MetaFin USING(pmi_id)
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
    WHERE prgs_id=:prg_id;
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

            ' . $uno . strtoupper(($row['ind_fin'])) . '
            ' . $dos . (strtoupper($row['fin_formulaCalculo'])) . '
            ' . $tres . ($row['tpi_fin']) . '
            ' . $cuatro . ($row['dim_fin']) . '
            ' . $cinco . ($row['frm_fin']) . '
            ' . $seis . ($row['ses_fin']) . '
            ' . $siete . ($row['mem_original']);

        $Fin = $row['mir_resumenNarrativo'];
        $mediosFin = str_replace('|', '', $row['mediosFin']);
        $supFin = str_replace('|', '', $row['SupuestosFin']);

        $indFin = $row['ind_id'];
    }
}
$c1->desconectar();

$pdf->Row(array('FIN', strtoupper(utf8Decode($Fin)), utf8Decode($ind), strtoupper(utf8Decode($mediosFin)), strtoupper(utf8Decode($supFin))));
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
    SELECT IFNULL(vpm_id,0) vpm_id, IFNULL(ind_pro,"") ind_pro, IFNULL(ind_id,0) ind_id,
        IFNULL(pro_formulaCalculo,"") pro_formulaCalculo, IFNULL(tpi_pro,"") tpi_pro,
        IFNULL(dim_pro,"") dim_pro, IFNULL(frm_pro,"") frm_pro, IFNULL(ses_pro,"") ses_pro,
        0 lac_id, IFNULL(vpm_resumenNarrativo,"") proposito,
        IFNULL(mpr_original,"") mpr_original, IFNULL(mpr_anteriorIndicador,"") mpr_anteriorIndicador,
        IFNULL(umi_id,0) umi_id, mpr_denominador, mprt_denominador
    FROM cat_Mir
    INNER JOIN vin_ProgramasMir USING(mir_id)
    LEFT JOIN(
        SELECT vpm_id, pmi_id, nim_id, ind_id, ind_nombreMIR AS ind_pro, ind_formulaCalculo AS pro_formulaCalculo,
            tpi_nombre tpi_pro,dim_nombre dim_pro,frm_nombre frm_pro,ses_nombre ses_pro, umi_id,
            m.mpr_id, mpr_original, mpr_anteriorIndicador, mpr_denominador, mprt_denominador
        FROM vin_ProgramaMirIndicador
        INNER JOIN cat_Indicador USING(nim_id,ind_id)
        JOIN cat_TipoIndicador USING(tpi_id)
        JOIN cat_Dimension USING(dim_id)
        JOIN cat_FrecuenciaMedicion USING(frm_id)
        JOIN cat_SentidoEsperado USING(ses_id)
        LEFT JOIN cat_MetaProposito m USING(pmi_id)
        LEFT JOIN cat_MetaPropositoTrimestre USING(pmi_id)
        WHERE nim_id=2 AND vin_ProgramaMirIndicador.sts_id=1
    )s USING(vpm_id)
    WHERE prgs_id=:prg_id;
');
$parametros = [
    ':prg_id' => $_SESSION[_S]['clasAdm-prg']
];
$p1->conectar();
$p1->consultar($parametros);
$ind_id = '';
$nombrepro = '';
$supuestoPUR = '';
$mediosVerPUR = '';
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
        $prop .= utf8Decode($rowP['proposito']);
        $nombrepro = $rowP['vpm_id'];

        $supPro = new AccesoDatos();
        $supPro->setSql('
            SELECT *
            FROM cat_SupuestoProgramaMir s
            WHERE vpm_id=? AND nim_id=2 AND spm_trimestreCierre = ?;
        ');

        $parametros = [
            $rowP['vpm_id'],
            $_POST['eri_trimestre']
        ];
        $supPro->conectar();
        $supPro->consultar($parametros);
        $supPro->desconectar();

        while ($supMediosP = $supPro->extraer()) {
            $supuestoPUR .= $supMediosP['spm_nombre'] . "\n" . "\n";
        }

        $supMePro = new AccesoDatos();
        $supMePro->setSql('
            SELECT *
            FROM cat_MedioVerificacionProgramaMir m
            WHERE vpm_id=? AND nim_id=2 AND mvpm_trimestreCierre = ?;
        ');
        $parametros = [
            $rowP['vpm_id'],
            $_POST['eri_trimestre']
        ];
        $supMePro->conectar();
        $supMePro->consultar($parametros);
        $supMePro->desconectar();

        while ($MediosP = $supMePro->extraer()) {
            $mediosVerPUR .= $MediosP['mvpm_nombre']. "\n"."\n";
        }
    }

    if ($ind_id != $rowP['ind_id']) {

        $valorMeta = $rowP['mprt_denominador'];

        if ($valorMeta != '') {
            $siete = '7 - ';
        } else {
            $siete = '';
        }

        $ind .= '

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

    $pdf->SetRelleno("no");
    $pdf->setBorders(1);
    $pdf->SetFillColor(255, 255, 255);
}
$pdf->Row(array($tex, $prop, utf8Decode($ind), strtoupper(utf8Decode($mediosVerPUR)), strtoupper(utf8Decode($supuestoPUR))));
$p1->desconectar();
/* FIN DETALLE DE NIVEL PROPOSITO */

/* DETALLE DEL REPORTE A NIVEL COMPONENTE Y ACTIVIDAD */
$texTotal = '';
$sql = "
    SELECT sprs_id, pdf_id, pdf_mir AS componente, epfi_id AS ipf_id, ind_id,
    GROUP_CONCAT('UR ', m.gpo_id, m.ur_id,' ---- ', IF(tiv_id=1,(mcot_denominadorT2+mcot_denominadorT4),mcot_denominadorT4),'\n' ORDER BY m.gpo_id, m.ur_id SEPARATOR '|') AS urs,
    ind_nombreMIR, ind_formulaCalculo,tpi_id,tpi_nombre, dim_id, dim_nombre, frm_id,
    frm_nombre, ses_id, ses_nombre,MAX( mco_descripcion) AS mem_descripcion, ind_nombre, tiv_id
    FROM cat_Subprogramas
    INNER JOIN cat_ProductoFinal USING (pdf_id)
    INNER JOIN enc_ProductoFinalIndicador e USING (pdf_id)
    LEFT JOIN cat_MetaComponente m USING (prgs_id, sprs_id, epfi_id)
    LEFT JOIN aux_HistoricoModificacionMetaComponente aux USING(prgs_id,sprs_id,epfi_id,gpo_id,ur_id)
    LEFT JOIN cat_Indicador USING(nim_id,ind_id)
    LEFT JOIN cat_TipoIndicador USING(tpi_id)
    LEFT JOIN cat_Dimension USING(dim_id)
    LEFT JOIN cat_FrecuenciaMedicion USING(frm_id)
    LEFT JOIN cat_SentidoEsperado USING(ses_id)
    INNER JOIN cat_TipoValor USING(tiv_id)
    WHERE prgs_id = ? AND (esr_id=? OR sprs_id=?) AND pdf_id!=? AND e.sts_id=? AND m.mco_original >= 0 AND aux.tri_id=?
    GROUP BY prgs_id,sprs_id,pdf_id,epfi_id,ind_id
    ORDER BY epfi_orden, sprs_id, pdf_id, ind_id;
";
$parametros = [
    $_SESSION[_S]['clasAdm-prg'],
    4,
    0,
    0,
    1,
    $_POST['eri_trimestre']
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
    $pdf->setAltoLinea(1.7);
    $pdf->SetWidths(array(20, 35, 65, 80, 80));
    $pdf->SetAligns(array('C', 'L', 'L', 'L', 'L'));
    $pdf->SetFont('UniviaPro-Regular', '', 5);
    //Denominación, Método de cálculo,tipo-dimensión-frecuencia-sentido-meta anual

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
        $texto = '7 - ';
    } else {
        $texto = '';
    }

    $ind = '

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
            AND (spc_id IS NOT NULL) AND spc_trimestreCierre IN(?)
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

        while ($supMediosC = $supCom->extraer()) {
            $supuestoUR .= "UR " . $supMediosC['gpo_id'] . $supMediosC['ur_id'] . ": " . $supMediosC['spc_nombre'] . "\n" . "\n";
        }

        $supMeCom = new AccesoDatos();
        $supMeCom->setSql('
            SELECT *
            FROM cat_MedioVerificacionComponente m
            WHERE prgs_id = ? AND sprs_id = ?
            AND (mvcm_id IS NOT NULL) AND mvcm_trimestreCierre IN(?)
            ORDER BY prgs_id, sprs_id;
        ');
        $parametros = [
            $_SESSION[_S]['clasAdm-prg'],
            $rowC['sprs_id'],
            $_POST['eri_trimestre']
        ];
        $supMeCom->conectar();
        $supMeCom->consultar($parametros);
        $supMeCom->desconectar();

        while ($MediosC = $supMeCom->extraer()) {
            $mediosVerUR .= "UR " . $MediosC['gpo_id']. $MediosC['ur_id'] .": " . $MediosC['mvcm_nombre']. "\n" . "\n";
        }

        $numComponente = 'COMPONENTE ';
        $Componente = $rowC['componente'];

        $prodFinal = $rowC['pdf_id'];

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

    $pdf->SetWidths(array(20, 35, 65, 80, 80));
    $pdf->SetAligns(array('C', 'L', 'L', 'L', 'L'));
    $pdf->SetFont('UniviaPro-Regular', '', 5);
    $pdf->Row(array($numComponente, utf8Decode($Componente), utf8Decode($ind) . str_replace('|', '', $rowC['urs']), strtoupper(utf8Decode($mediosVerUR)), strtoupper(utf8Decode($supuestoUR))));

    $pdf->SetRelleno("no");
    $pdf->setBorders(0);
    $pdf->SetFillColor(255, 255, 255);

    if ($contInd == $numIndicadoresCom) {
        $metaAct = new AccesoDatos();
        $metaAct->setSql('
            SELECT pdf_id, pdi_id, pdi_mir, ind_id, ind_nombre, MAX(macm_descripcion), ind_nombreMIR, ind_definicion,
            ind_formulaCalculo, tpi_id, tpi_nombre, dim_id, dim_nombre, frm_id, frm_nombre, ses_id, ses_nombre,
            GROUP_CONCAT("UR ", m.gpo_id, m.ur_id, " ---- ", IF(tiv_id=1,(macm_denominadorT1+macm_denominadorT2+macm_denominadorT3+macm_denominadorT4),macm_denominadorT4), "\n" ORDER BY gpo_id, ur_id SEPARATOR "|") AS urs,
            CONCAT("TOTAL ---- ", SUM(macm_original), "\n") AS total
            FROM cat_ProductoIntermedio
            INNER JOIN vin_ProductoIntermedioIndicador v USING(pdi_id)
            INNER JOIN vin_ProductoFinalProductoIntermedio vpfi USING(pdf_id,pdi_id)
            LEFT JOIN (
                SELECT pii_id, mac.gpo_id, mac.ur_id, mac.macm_original, macm_descripcion, macm_denominadorT1,macm_denominadorT2,macm_denominadorT3,macm_denominadorT4, tri_id
                FROM cat_MetaActividadMir mac
                LEFT JOIN aux_HistoricoMetaActividadMir USING(pii_id,macm_id)
                GROUP BY  gpo_id, ur_id,  pii_id,macm_id
            )m USING(pii_id)
            INNER JOIN cat_Indicador USING(nim_id,ind_id)
            INNER JOIN cat_TipoIndicador USING(tpi_id)
            INNER JOIN cat_Dimension USING(dim_id)
            INNER JOIN cat_FrecuenciaMedicion USING(frm_id)
            INNER JOIN cat_SentidoEsperado USING(ses_id)
            INNER JOIN cat_TipoValor USING(tiv_id)
            WHERE pdf_id = ? AND v.sts_id = ?
                AND vpfi.sts_id=? AND macm_original >= 0 AND m.tri_id=?
            GROUP BY pdi_id,pii_id, ind_id
            ORDER BY pii_orden, pdf_id, pdi_id, ind_id;
        ');

        $parametros = [
            $pdf_id,
            1,
            1,
            $_POST['eri_trimestre']
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

        while ($Actividad = $metaAct->extraer()) {
            $pdf->SetRelleno("no");
            $pdf->CheckPageBreak(10);
            $pdf->setX(10);
            $pdf->SetFillColor(199, 209, 219);
            $pdf->setBorders(1);
            $pdf->setAltoLinea(1.9);
            //Denominación, Método de cálculo,tipo-dimensión-frecuencia-sentido-meta anual

            $pdf->SetWidths(array(20, 35, 65, 80, 80));
            $pdf->SetAligns(array('C', 'L', 'L', 'L', 'L'));
            $pdf->SetFont('UniviaPro-Regular', '', 5);

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
                $texto2 = '7 - ';
            } else {
                $texto2 = '';
            }

            $ind2 = '
                ' . $uno . $Actividad['ind_nombreMIR'] . '
                ' . $dos . strtoupper($Actividad['ind_formulaCalculo']) . '
                ' . $tres . $Actividad['tpi_nombre'] . '
                ' . $cuatro . $Actividad['dim_nombre'] . '
                ' . $cinco . $Actividad['frm_nombre'] . '
                ' . $seis . $Actividad['ses_nombre'] . '
                ' . $texto2;

            $supAct = new AccesoDatos();
            $supAct->setSql('
                SELECT *
                FROM cat_SupuestoActividad s
                WHERE pdf_id = ? AND pdi_id = ? AND spa_trimestreCierre IN(?)
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

            $supMeAct2 = new AccesoDatos();
            $supMeAct2->setSql('
                SELECT *
                FROM cat_MedioVerificacionActividad m
                WHERE pdf_id = ? AND pdi_id = ? AND mvac_trimestreCierre IN(?)
                ORDER BY gpo_id, ur_id;
            ');
            $parametros = [
                $pdf_id,
                $Actividad['pdi_id'],
                $_POST['eri_trimestre']
            ];
            $supMeAct2->conectar();
            $supMeAct2->consultar($parametros);
            $supMeAct2->desconectar();

            $textoActividad = '';
            $NombreActividad = '';

            if ($pdi_id != $Actividad['pdi_id']) {

                $textoActividad = 'ACTIVIDAD ';

                $NombreActividad = utf8Decode($Actividad['pdi_mir']);

                while ($supMediosA = $supAct->extraer()) {
                    $supuestoURAct .= "UR " . $supMediosA['gpo_id'] . $supMediosA['ur_id'] .": " . $supMediosA['spa_nombre'] . "\n" . "\n";
                }

                while ($supA = $supMeAct2->extraer()) {
                    $mediosVerURAct .= "UR " . $supA['gpo_id'] . $supA['ur_id'] . ": " . $supA['mvac_nombre'] . "\n" . "\n";
                }

                $pdi_id = $Actividad['pdi_id'];

                $contadorAct++;
            }

            $ind2 = str_replace(array('“', '”'), '"', $ind2);

            $pdf->Row(array($textoActividad, $NombreActividad, utf8Decode($ind2) . str_replace('|', '', $Actividad['urs']), strtoupper(utf8Decode($mediosVerURAct)), strtoupper(utf8Decode($supuestoURAct))));

            $mediosVerURAct = '';
            $supuestoURAct = '';
            $contInd = 0;
        }
    }

    $pdf->SetRelleno("no");
    $pdf->SetFillColor(255, 255, 255);

    $contInd++;
    $mediosVerUR = '';
    $supuestoUR = '';

    /* FIN DETALLE DEl REPORTE A NIVEL ACTIVIDAD */
}

$pdf->Output();
