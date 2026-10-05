<?php
session_start();
ini_set('memory_limit', '1000M');
set_time_limit(0);
//librerias
require_once '../../../lib/reportetabla.1.8.php';
require_once '../../../lib/conversionNumLetras.php';
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../lib/constantes.php';
require_once '../../../../lib/helpers.php';

class reportetabla extends PDF
{
    public function __construct()
    {
        parent::__construct();
    }

    public function Footer($lastPage = true)
    {
        $this->SetY(-45);
        $this->SetY(-15);
        if ($_SERVER['SERVER_NAME'] != "sefip.finanzasoaxaca.gob.mx") {
            $this->setTextColor(200, 0, 0);
            $this->Cell(50, 10, utf8decode('IMPRESIÓN DE PRUEBA '), 0, 0, 'R');
            $this->setTextColor(0, 0, 0);
        }
        $this->SetFont('Arial', '', 6.5);
        $this->Cell(0, 10, utf8decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
        $this->Cell(0, 10, date("d/m/y") . ' - ' . date("H:i:s") . '        ', 1, 0, 'R');
    }

    public function Header() //Encabezado del documento
    {
        $row = $this->getDatos();
        $this->SetMargins(10, 10, '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);

        $row = $this->getDatos();
        $this->SetMargins(15, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $iy = $this->GetY();
        if ($_SESSION[_S]['nvj_id'] > 2)
        //Por elecciones se actualiza el logo
        {
            $this->Image('../../../../img/logo_sefin _rep_presu.jpeg', 240, 3, '35', '20', 'JPEG', '');
        }

        //$this->Image('../../../../img/logosefin-color-fblanco.png', 185,0    , '100' , '29' , 'PNG', '');
        else {
            $this->Image('../../../../img/escudo2.jpg', 219, 6, '60', '17.4', 'JPG', '');
        }

        $ytmp = $this->GetY();

        $this->SetFont('Arial', 'B', 10);
        $this->SetXY(5, $iy + 4);
        $ytmp = $this->GetY();
        $this->Cell(195, 4, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
        $this->SetXY(5, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(180, 4, utf8decode('SUBSECRETARIA DE EGRESOS, CONTABILIDAD Y TESORERÍA'), 0, 1, 'L');
        $this->ln(3);
        $this->SetXY(5, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(180, 4, utf8decode('PRESUPUESTO CALENDARIZADO DISPONIBLE POR CLAVE PRESUPUESTAL'), 0, 1, 'L');
        $this->ln(3);
        $ytmp = $this->GetY();
        $this->SetXY(5, $ytmp - 3);
        $ytmp = $this->GetY();
        $this->SetFont('Arial', 'B', 8);
        if ($_SESSION[_S]['nvj_id'] < 3) {
            if ($row) {
                $this->Cell(180, 4, utf8decode('UNIDAD RESPONSABLE:' . $row['gpo_id'] . $row['ur_id'] . ' -' . $row['ur_nombre']), 0, 1, 'L');
            }
        }
        $this->ln(3);
        $ytmp = $this->GetY();
        $this->setX(5);
        $this->ln(1);
    }

    public function cabecera($fin, $ur = '')
    {
        $this->setRelleno("no");
        $this->SetFont('Arial', 'B', 5);
        $this->SetTextColor(0, 0, 0);
        $this->setX(5);
        $this->SetDrawColor(180, 180, 180);
        $this->SetBorders("TB");
        if ($fin) {
            $this->SetWidths(array(65, 20, 17, 17, 17, 17, 17, 17, 17, 17, 17, 17, 17, 17));
            // $this->SetWidths(array(39,5,34,18,16,16,16,16,16,16,16,16,16,16,16,16));
            $this->SetAligns(array('L', 'C', 'C', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R'));
            $this->Row(array(
                'TOTAL ',
                utf8decode('VIGENTE'),
                utf8decode('ENE'),
                utf8decode('FEB'),
                utf8decode('MAR'),
                utf8decode('ABR'),
                utf8decode('MAY'),
                utf8decode('JUN'),
                utf8decode('JUL'),
                utf8decode('AGO'),
                utf8decode('SEP'),
                utf8decode('OCT'),
                utf8decode('NOV'),
                utf8decode('DIC'),
            ));
        } else {
            if ($_SESSION[_S]['nvj_id'] > 2) {
                $this->SetWidths(array(200));
                $this->SetAligns(array('L'));
                $this->row(array(utf8decode($ur)));
                $this->setX(5);
            }
            $this->SetWidths(array(39, 5, 34, 18, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16));
            $this->SetAligns(array('L', 'C', 'C', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R'));

            $this->Row(array(
                utf8decode('CLAVE PRESUPUESTAL'),
                utf8decode('TC'),
                utf8decode('PARTIDA ESPECíFICA'),
                utf8decode('MODIFICADO'),
                utf8decode('ENE'),
                utf8decode('FEB'),
                utf8decode('MAR'),
                utf8decode('ABR'),
                utf8decode('MAY'),
                utf8decode('JUN'),
                utf8decode('JUL'),
                utf8decode('AGO'),
                utf8decode('SEP'),
                utf8decode('OCT'),
                utf8decode('NOV'),
                utf8decode('DIC'),
            ));
        }
        $this->SetTextColor(0, 0, 0);
        $this->setX(5);
    }

    public function ur($ur)
    {
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(0, 0, 0);
        $this->setX(5);
        $this->SetFillColor(189, 255, 184);
        $this->Cell(30, 4, 'Unidad Responsable', 0, 0, 'L', true);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(145, 4, utf8decode($ur), 0, 1, 'L', true);
        $this->setRelleno("no");
        $this->setX(5);
    }

    public function ue($ue_id, $ue_nombre)
    {
        $this->SetFont('Arial', 'B', 6);
        $this->SetTextColor(0, 0, 0);
        $this->setX(5);
        $this->SetFillColor(210, 210, 210);
        $this->Cell(30, 4, 'Unidad Ejecutora', 0, 0, 'L', true);
        $this->SetFont('Arial', '', 6);
        $this->Cell(258, 4, utf8decode($ue_id . ' - ' . $ue_nombre), 0, 1, 'L', true);
        $this->ln(2);
        $this->setRelleno("no");
    }

    public function Programa($prg_id, $prg_nombre)
    {
        $this->SetFont('Arial', 'B', 7);
        $this->SetTextColor(0, 0, 0);
        $this->setX(5);
        $this->SetFillColor(210, 210, 210);
        $this->Cell(15, 3, 'Programa', 0, 0, 'L', false);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(282, 3, utf8decode($prg_id . ' - ' . $prg_nombre), 0, 1, 'L', false);
        $this->ln(2);
        $this->setRelleno("no");
    }

    public function subPrograma($spr_id, $spr_nombre)
    {
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(0, 0, 0);
        $this->setX(5);
        $this->SetFillColor(210, 210, 210);
        $this->Cell(16, 2, 'Subprograma', 0, 0, 'L', false);
        $this->SetFont('Arial', '', 7);
        $this->Cell(282, 2, utf8decode($spr_id . ' - ' . $spr_nombre), 0, 1, 'L', false);
        $this->ln(2);
        $this->setRelleno("no");
    }

    public function Proyecto($py_id, $py_nombre)
    {
        $this->SetFont('Arial', 'B', 6);
        $this->SetTextColor(0, 0, 0);
        $this->setX(5);
        $this->SetFillColor(210, 210, 210);
        $this->Cell(15, 3, 'Proyecto', 0, 0, 'L', false);
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(282, 3, utf8decode($py_id . ' - ' . $py_nombre), 0, 1, 'L', false);
        $this->ln(2);
        $this->setRelleno("no");
    }

    public function Oa($oa_id, $oa_nombre)
    {
        $this->SetFont('Arial', '', 6);
        $this->SetTextColor(0, 0, 0);
        $this->setX(5);
        $this->SetFillColor(210, 210, 210);
        $this->Cell(4, 3, '', 0, 0, 'L', false);
        $this->Cell(15, 3, 'Obra/Actividad', 0, 0, 'L', false);
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(269, 3, utf8decode($oa_id . ' - ' . $oa_nombre), 0, 1, 'L', false);
        $this->ln(2);
        $this->setRelleno("no");
    }

    public function Idpartida($idp_id, $idp_nombre)
    {
        $this->setRelleno("si");
        $this->SetFont('Arial', 'B', 6);
        $this->SetTextColor(0, 0, 0);
        $this->setX(5);
        $this->SetFillColor(240, 240, 240);
        $this->Cell(8, 4, '', 0, 0, 'L', true);
        $this->Cell(8, 4, $idp_id, 0, 0, 'L', true);
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(272, 4, utf8decode($idp_nombre), 0, 1, 'L', true);
        $this->setRelleno("no");
    }

    public function clave($ecf, $ecf_nombre)
    {
        $this->setRelleno("si");
        $this->SetFont('Arial', '', 6);
        $this->SetTextColor(0, 0, 0);
        $this->setX(5);
        $this->SetFillColor(250, 240, 240);
        $this->Cell(4, 3, '', 0, 0, 'L', true);
        $this->Cell(18, 3, $ecf, 0, 0, 'L', true);
        $this->SetFont('Arial', '', 6);
        $this->Cell(266, 3, utf8decode($ecf_nombre), 0, 1, 'L', true);
        $this->setRelleno("no");
    }

    public function totales($mensaje, $totales, $fin, $borde = false)
    {
        $total = 0;
        if (isset($totales['vig'])) {
            $total = $totales['vig'];
        }
        if (isset($totales['com'])) {
            $total = $totales['com'];
        }
        if (isset($totales['dev'])) {
            $total = $totales['dev'];
        }
        if (isset($totales['eje'])) {
            $total = $totales['eje'];
        }
        if (isset($totales['dif'])) {
            $total = $totales['dif'];
        }

        $this->setAltoLinea(3);
        $this->SetFillColor(230, 230, 230);
        $this->SetRelleno("no");
        $this->setX(5);
        if ($fin) {
            $this->SetWidths(array(78, 18, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16));
        } else {
            $this->SetWidths(array(78, 18, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16));
        }
        $this->SetDrawColor(220, 220, 220);
        $this->SetBorders(1);
        $this->SetAligns(array('R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R'));
        $this->setDrawColor(00, 00, 00);
        if ($borde) {
            $this->SetBorders("B");
        } else {
            $this->SetBorders("T");
        }
        $this->SetFont('Arial', '', 5);
        $this->Row(array(
            utf8decode($mensaje),
            number_format($total, 2),
            number_format($totales['ene'], 2),
            number_format($totales['feb'], 2),
            number_format($totales['mar'], 2),
            number_format($totales['abr'], 2),
            number_format($totales['may'], 2),
            number_format($totales['jun'], 2),
            number_format($totales['jul'], 2),
            number_format($totales['ago'], 2),
            number_format($totales['sep'], 2),
            number_format($totales['oct'], 2),
            number_format($totales['nov'], 2),
            number_format($totales['dic'], 2),
        ));
        $this->SetRelleno("no");
        $this->SetTextColor(0, 0, 0);
        $this->setaltoLinea(2.5);
    }

    public function totalesdif($mensaje, $totales, $totalesEje, $fin)
    {
        $this->setaltoLinea(3);
        $this->SetFillColor(230, 230, 230);
        $this->SetRelleno("no");
        $this->setX(5);

        if ($fin) {
            $this->SetWidths(array(78, 18, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16));
        } else {
            $this->SetWidths(array(78, 18, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16));
        }
        $this->SetDrawColor(230, 230, 230);
        $this->SetAligns(array('R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R'));
        $this->setDrawColor(0, 0, 0);
        $this->SetBorders("TB");
        $this->SetFont('Arial', 'B', 5);
        $this->Row(array(
            utf8decode($mensaje),
            number_format($totales['vig'] - $totalesEje['eje'], 2),
            number_format($totales['ene'] - $totalesEje['ene'], 2),
            number_format($totales['feb'] - $totalesEje['feb'], 2),
            number_format($totales['mar'] - $totalesEje['mar'], 2),
            number_format($totales['abr'] - $totalesEje['abr'], 2),
            number_format($totales['may'] - $totalesEje['may'], 2),
            number_format($totales['jun'] - $totalesEje['jun'], 2),
            number_format($totales['jul'] - $totalesEje['jul'], 2),
            number_format($totales['ago'] - $totalesEje['ago'], 2),
            number_format($totales['sep'] - $totalesEje['sep'], 2),
            number_format($totales['oct'] - $totalesEje['oct'], 2),
            number_format($totales['nov'] - $totalesEje['nov'], 2),
            number_format($totales['dic'] - $totalesEje['dic'], 2),
        ));
        $this->SetRelleno("no");
        $this->SetTextColor(0, 0, 0);
        $this->setaltoLinea(2.5);
    }
} //Fin class

$pdf = new reportetabla('L', 'mm', array(612, 1008));
$pdf->SetProtection(array('print', 'annot-forms'), "", PDF_PASSWORD_OWNER_SCP);
$pdf->setOrientation('L');
$pdf->AliasNbPages();
$pdf->setAltoLinea(2);
$pdf->SetAutoPageBreak(true, 20);
$gpo_id = $_SESSION[_S]['clasAdm-gpo'];
$ur_id = $_SESSION[_S]['clasAdm-ur'];

/* variables de totales */
$totue = $totprg = $totspr = $totpy = $totoa = true;
$totur = true;

$sql = '
        SELECT * FROM presupuesto
        INNER JOIN presupuestoVigente USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        INNER JOIN presupuestoEjercido USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        INNER JOIN cat_UnidadEjecutora USING(gpo_id,ur_id,ue_id)
        INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
        INNER JOIN cat_Subprograma USING(gpo_id,ur_id,prg_id,spr_id)
        INNER JOIN cat_Proyecto USING(gpo_id,ur_id,prg_id,spr_id,py_id)
        INNER JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
        INNER JOIN cat_TipoObraActividad USING(toa_id)
        INNER JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,/*cso_id,*/cfi_id,cfi_anio)
        INNER JOIN sinpres.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id)
        INNER JOIN cat_PartidaGenerica USING(pg_id)
        INNER JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
        INNER JOIN cat_IdentificadorPartida USING(idp_id)
        WHERE gpo_id IS NOT NULL';
$params = [];
if (!isset($_POST['check'])) {
    $sql .= ' AND pre_vigente  > ? ';
    array_push($params, 0);
}

if ($_SESSION[_S]['nvj_id'] < 3) {
    $sql .= ' AND gpo_id = ? and ur_id = ?';
    array_push($params, $gpo_id, $ur_id);
} else {
    if (isset($_POST['gpour']) && $_POST['gpour'] != "") {
        $gpo_id = substr($_POST['gpour'], 0, 1);
        $ur_id = substr($_POST['gpour'], 1, 2);
        $sql .= 'AND gpo_id = ? and ur_id = ?';
        array_push($params, $gpo_id, $ur_id);
    } else {
        if (!isset($_POST['rep_id'])) {
            $sql .= ' AND gpo_id  > ? ';
            array_push($params, 0);
        } else {
            $sql .= ' AND gpo_id = ? and ur_id = ?';
            array_push($params, $gpo_id, $ur_id);
        }
    }
}

if (isset($_POST['oa']) && $_POST['oa'] != "") {
    $sql .= ' AND oa_id = ?';
    array_push($params, $_POST['oa']);
    $totue = $totprg = $totspr = $totpy = $totoa = true;
    $totue = $totprg = $totspr = $totpy = false;
    $totur = false;
}

if (isset($_POST['py']) && $_POST['py'] != "") {
    $sql .= ' AND py_id = ?';
    array_push($params, $_POST['py']);
    $totue = $totprg = $totspr = $totpy = $totoa = true;
    $totue = $totprg = $totspr = false;
    $totur = false;
}

if (isset($_POST['subprograma']) && $_POST['subprograma'] != "") {
    $sql .= ' AND spr_id = ?';
    array_push($params, $_POST['subprograma']);
    $totue = $totprg = $totspr = $totpy = $totoa = true;
    $totue = $totprg = false;
    $totur = false;
}

if (isset($_POST['programa']) && $_POST['programa'] != "") {
    $sql .= ' AND prg_id = ?';
    array_push($params, $_POST['programa']);
    $totue = $totprg = $totspr = $totpy = $totoa = true;
    $totue = false;
    $totur = false;
}

if (isset($_POST['unidad']) && $_POST['unidad'] != "") {
    $sql .= ' AND presupuesto.ue_id = ?';
    array_push($params, $_POST['unidad']);
    $totue = $totprg = $totspr = $totpy = $totoa = true;
    $totur = false;
}

/* CLAVE DE FINANCIAMIENTO */
if (isset($_POST['fuente']) && $_POST['fuente'] != "") {
    $sql .= ' AND fte_id = ?';
    array_push($params, $_POST['fuente']);
    $totur = false;
}

if (isset($_POST['subfuente']) && $_POST['subfuente'] != "") {
    $sql .= ' AND sft_id = ?';
    array_push($params, $_POST['subfuente']);
    $totur = false;
}

if (isset($_POST['origen']) && $_POST['origen'] != "") {
    $sql .= ' AND ori_id = ?';
    array_push($params, $_POST['origen']);
    $totur = false;
}

if (isset($_POST['suborigen']) && $_POST['suborigen'] != "") {
    $sql .= ' AND sor_id = ?';
    array_push($params, $_POST['suborigen']);
    $totur = false;
}

if (isset($_POST['clasificacion']) && $_POST['clasificacion'] != "") {
    $sql .= ' AND cff_id = ?';
    array_push($params, $_POST['clasificacion']);
    $totur = false;
}

if (isset($_POST['referencia']) && $_POST['referencia'] != "") {
    $sql .= ' AND cfi_id = ?';
    array_push($params, $_POST['referencia']);
    $totur = false;
}

if (isset($_POST['cfi_anio']) && $_POST['cfi_anio'] != "") {
    $sql .= ' AND cfi_anio = ?';
    array_push($params, $_POST['cfi_anio']);
    $totur = false;
}

if (isset($_POST['generica']) && $_POST['generica'] != "") {
    $sql .= ' AND pg_id = ?';
    array_push($params, $_POST['generica']);
    $totur = false;
    $totue = $totprg = $totspr = $totpy = $totoa = false;
}

if (isset($_POST['especifica']) && $_POST['especifica'] != "") {
    $sql .= ' AND pe_id = ?';
    array_push($params, $_POST['especifica']);
    $totur = false;
    $totue = $totprg = $totspr = $totpy = $totoa = false;
}

if (isset($_POST['compra']) && $_POST['compra'] != "") {
    $sql .= ' AND tcp_id = ?';
    array_push($params, $_POST['compra']);
    $totur = false;
}

$sql .= ' ORDER BY gpo_id,ur_id,presupuesto.ue_id,prg_id,spr_id,py_id,oa_id,ecf_id,idp_id,pg_id,pe_id';
$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar($params);
$conexion->desconectar();
$row = $conexion->extraer();
$pdf->setDatos($row);
$pdf->AddPage();

/* variables  de montos*/
$celda = 0;
$totalVigenteUR = 0;
$totalComproUR = 0;

$MesesUE = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
$MesesUEE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

$MesesPR = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
$MesesPRE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

$MesesSPR = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
$MesesSPRE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

$MesesPY = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
$MesesPYE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

$MesesOA = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
$MesesOAE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

$MesesIDP = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
$MesesIDPE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

$MesesPG = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
$MesesPGE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

$MesesT = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
$MesesTE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
$MesesTDif = array('dif' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
if($row){
/* variables  de cambios */
$ue_id = $row['ue_id'];
$prg_id = $row['prg_id'];
$spr_id = $row['spr_id'];
$py_id = $row['py_id'];
$oa_id = $row['oa_id'];
$idp_id = $row['idp_id'];
$idp_nombre = $row['idp_nombre'];
$pg_id = $row['pg_id'];
$ur = $row['gpo_id'] . $row['ur_id'] . '-' . $row['ur_nombre'];

/*  fin variables  */
if ($_SESSION[_S]['nvj_id'] > 2) {
    $pdf->ur($ur);
    $pdf->setX(5);
}

$pdf->ue($row['ue_id'], $row['ue_nombre']);
$pdf->Programa($row['prg_id'], $row['prg_nombre']);
$pdf->subPrograma($row['spr_id'], $row['spr_nombre']);
$pdf->Proyecto($row['py_id'], $row['py_nombre']);
if ($row['toa_id'] == 1) {
    $pdf->Oa($row['oa_id'], $row['oa_nombre'] . '  TIPO DE OBRA: ' . $row['toa_nombre']);
} else {
    $pdf->Oa($row['oa_id'], $row['oa_nombre'] . '  TIPO DE OBRA: ' . $row['toa_nombre'] . ' NÚMERO DE OBRA ' . $row['oa_numObra']);
}
$pdf->clave($row['cfi_clave'], '- ' . $row['cfi_nombre']);
$pdf->Idpartida($row['idp_id'], $row['idp_nombre']);
$pdf->setX(5);
$pdf->cabecera(false, $ur);

do {
    if ($ur != $row['gpo_id'] . $row['ur_id'] . '-' . $row['ur_nombre']) {
        if ($totoa) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL OBRA ACTIVIDAD', $MesesOA, false);
            $pdf->totales('TOTAL EJERCIDO OBRA ACTIVIDAD ', $MesesOAE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER OBRA ACTIVIDAD ', $MesesOA, $MesesOAE, false);
        }

        if ($totpy) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL PROYECTO', $MesesPY, false);
            $pdf->totales('TOTAL EJERCIDO PROYECTO ', $MesesPYE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER PROYECTO ', $MesesPY, $MesesPYE, false);
        }

        if ($totspr) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL SUBPROGRAMA', $MesesSPR, false);
            $pdf->totales('TOTAL EJERCIDO SUBPROGRAMA ', $MesesSPRE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER SUBPROGRAMA ', $MesesSPR, $MesesSPRE, false);
        }

        if ($totprg) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL PROGRAMA', $MesesPR, false);
            $pdf->totales('TOTAL EJERCIDO PROGRAMA ', $MesesPRE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER PROGRAMA ', $MesesPR, $MesesPRE, false);
        }

        if ($totue) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL UNIDAD EJECUTORA', $MesesUE, false);
            $pdf->totales('TOTAL EJERCIDO UNIDAD EJECUTORA ', $MesesUEE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER UNIDAD EJECUTORA ', $MesesUE, $MesesUEE, false);
        }

        $pdf->totales('TOTAL DE LA UR', $MesesT, true, false);
        $pdf->totales('TOTAL DE LA UR EJERCIDO', $MesesTE, true, true);
        $pdf->totalesdif('TOTAL DE LA UR POR EJERCER', $MesesT, $MesesTE, true, true);
        $pdf->AddPage();

        $ur = $row['gpo_id'] . $row['ur_id'] . '-' . $row['ur_nombre'];
        $pdf->ur($ur);
        $pdf->ue($row['ue_id'], $row['ue_nombre']);
        $pdf->subPrograma($row['spr_id'], $row['spr_nombre']);
        $pdf->Proyecto($row['py_id'], $row['py_nombre']);
        $pdf->Oa($row['oa_id'], $row['oa_nombre']);
        $pdf->clave($row['cfi_clave'], '- ' . $row['cfi_nombre']);
        $pdf->IdPartida($row['idp_id'], $row['idp_nombre']);

        $celda = 0;
        $idp_id = $row['idp_id'];
        $idp_nombre = $row['idp_nombre'];
        $oa_id = $row['oa_id'];
        $py_id = $row['py_id'];
        $spr_id = $row['spr_id'];
        $prg_id = $row['prg_id'];
        $ue_id = $row['ue_id'];
        $MesesT = 0;
        $MesesTE = 0;

        $MesesUE = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesUEE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesPR = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesPRE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesSPR = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesSPRE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesPY = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesPYE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesOA = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesOAE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesIDP = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesIDPE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
    }

    if ($ue_id != $row['ue_id']) {
        if ($totoa) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL OBRA ACTIVIDAD', $MesesOA, false);
            $pdf->totales('TOTAL EJERCIDO OBRA ACTIVIDAD ', $MesesOAE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER OBRA ACTIVIDAD ', $MesesOA, $MesesOAE, false);
        }

        if ($totpy) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL PROYECTO', $MesesPY, false);
            $pdf->totales('TOTAL EJERCIDO PROYECTO ', $MesesPYE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER PROYECTO ', $MesesPY, $MesesPYE, false);
        }

        if ($totspr) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL SUBPROGRAMA', $MesesSPR, false);
            $pdf->totales('TOTAL EJERCIDO SUBPROGRAMA ', $MesesSPRE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER SUBPROGRAMA ', $MesesSPR, $MesesSPRE, false);
        }

        if ($totprg) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL PROGRAMA', $MesesPR, false);
            $pdf->totales('TOTAL EJERCIDO PROGRAMA ', $MesesPRE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER PROGRAMA ', $MesesPR, $MesesPRE, false);
        }

        if ($totue) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL UNIDAD EJECUTORA', $MesesUE, false);
            $pdf->totales('TOTAL EJERCIDO UNIDAD EJECUTORA ', $MesesUEE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER UNIDAD EJECUTORA ', $MesesUE, $MesesUEE, false);
        }

        if ($pdf->CheckPageBreak(25)) {
            $pdf->setX(5);
            $pdf->ue($row['ue_id'], $row['ue_nombre']);
            $pdf->Programa($row['prg_id'], $row['prg_nombre']);
            $pdf->subPrograma($row['spr_id'], $row['spr_nombre']);
            $pdf->Proyecto($row['py_id'], $row['py_nombre']);
            $pdf->Oa($row['oa_id'], $row['oa_nombre']);
            $pdf->clave($row['cfi_clave'], '- ' . $row['cfi_nombre']);
            $pdf->IdPartida($row['idp_id'], $row['idp_nombre']);
            $pdf->cabecera(false, $ur);
        } else {
            $pdf->ue($row['ue_id'], $row['ue_nombre']);
            $pdf->subPrograma($row['spr_id'], $row['spr_nombre']);
            $pdf->Proyecto($row['py_id'], $row['py_nombre']);
            $pdf->Oa($row['oa_id'], $row['oa_nombre']);
            $pdf->clave($row['cfi_clave'], '- ' . $row['cfi_nombre']);
            $pdf->IdPartida($row['idp_id'], $row['idp_nombre']);
        }

        $celda = 0;
        $idp_id = $row['idp_id'];
        $idp_nombre = $row['idp_nombre'];
        $oa_id = $row['oa_id'];
        $py_id = $row['py_id'];
        $spr_id = $row['spr_id'];
        $prg_id = $row['prg_id'];
        $ue_id = $row['ue_id'];

        $MesesUE = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesUEE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesPR = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesPRE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesSPR = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesSPRE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesPY = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesPYE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesOA = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesOAE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesIDP = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesIDPE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
    }

    if ($prg_id != $row['prg_id']) {
        if ($totoa) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL OBRA ACTIVIDAD', $MesesOA, false);
            $pdf->totales('TOTAL EJERCIDO OBRA ACTIVIDAD ', $MesesOAE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER OBRA ACTIVIDAD ', $MesesOA, $MesesOAE, false);
        }

        if ($totpy) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL PROYECTO', $MesesPY, false);
            $pdf->totales('TOTAL EJERCIDO PROYECTO ', $MesesPYE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER PROYECTO ', $MesesPY, $MesesPYE, false);
        }

        if ($totspr) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL SUBPROGRAMA', $MesesSPR, false);
            $pdf->totales('TOTAL EJERCIDO SUBPROGRAMA ', $MesesSPRE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER SUBPROGRAMA ', $MesesSPR, $MesesSPRE, false);
        }

        if ($totprg) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL PROGRAMA', $MesesPR, false);
            $pdf->totales('TOTAL EJERCIDO PROGRAMA ', $MesesPRE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER PROGRAMA ', $MesesPR, $MesesPRE, false);
        }

        if ($pdf->CheckPageBreak(25)) {
            $pdf->setX(5);
            $pdf->Programa($row['prg_id'], $row['prg_nombre']);
            $pdf->subPrograma($row['spr_id'], $row['spr_nombre']);
            $pdf->Proyecto($row['py_id'], $row['py_nombre']);
            $pdf->Oa($row['oa_id'], $row['oa_nombre']);
            $pdf->clave($row['cfi_clave'], '- ' . $row['cfi_nombre']);
            $pdf->IdPartida($row['idp_id'], $row['idp_nombre']);
            $pdf->cabecera(false, $ur);
        } else {
            $pdf->subPrograma($row['spr_id'], $row['spr_nombre']);
            $pdf->Proyecto($row['py_id'], $row['py_nombre']);
            $pdf->Oa($row['oa_id'], $row['oa_nombre']);
            $pdf->clave($row['cfi_clave'], '- ' . $row['cfi_nombre']);
            $pdf->IdPartida($row['idp_id'], $row['idp_nombre']);
        }

        $celda = 0;
        $idp_id = $row['idp_id'];
        $idp_nombre = $row['idp_nombre'];
        $oa_id = $row['oa_id'];
        $py_id = $row['py_id'];
        $spr_id = $row['spr_id'];
        $prg_id = $row['prg_id'];

        $MesesPR = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesPRE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesSPR = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesSPRE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesPY = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesPYE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesOA = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesOAE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesIDP = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesIDPE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
    }

    if ($spr_id != $row['spr_id']) {
        if ($totoa) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL OBRA ACTIVIDAD', $MesesOA, false);
            $pdf->totales('TOTAL EJERCIDO OBRA ACTIVIDAD ', $MesesOAE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER OBRA ACTIVIDAD ', $MesesOA, $MesesOAE, false);
        }

        if ($totpy) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL PROYECTO', $MesesPY, false);
            $pdf->totales('TOTAL EJERCIDO PROYECTO ', $MesesPYE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER PROYECTO ', $MesesPY, $MesesPYE, false);
        }

        if ($totspr) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL SUBPROGRAMA', $MesesSPR, false);
            $pdf->totales('TOTAL EJERCIDO SUBPROGRAMA ', $MesesSPRE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER SUBPROGRAMA ', $MesesSPR, $MesesSPRE, false);
        }

        if ($pdf->CheckPageBreak(25)) {
            $pdf->setX(5);
            $pdf->subPrograma($row['spr_id'], $row['spr_nombre']);
            $pdf->Proyecto($row['py_id'], $row['py_nombre']);
            $pdf->Oa($row['oa_id'], $row['oa_nombre']);
            $pdf->clave($row['cfi_clave'], '- ' . $row['cfi_nombre']);
            $pdf->IdPartida($row['idp_id'], $row['idp_nombre']);
            $pdf->cabecera(false, $ur);
        } else {
            $pdf->subPrograma($row['spr_id'], $row['spr_nombre']);
            $pdf->Proyecto($row['py_id'], $row['py_nombre']);
            $pdf->Oa($row['oa_id'], $row['oa_nombre']);
            $pdf->clave($row['cfi_clave'], '- ' . $row['cfi_nombre']);
            $pdf->IdPartida($row['idp_id'], $row['idp_nombre']);
        }

        $celda = 0;
        $idp_id = $row['idp_id'];
        $idp_nombre = $row['idp_nombre'];
        $oa_id = $row['oa_id'];
        $py_id = $row['py_id'];
        $spr_id = $row['spr_id'];

        $MesesSPR = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesSPRE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesPY = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesPYE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesOA = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesOAE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesIDP = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesIDPE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
    }

    if ($py_id != $row['py_id']) {
        if ($pdf->CheckPageBreak(25)) {
            $pdf->cabecera(false, $ur);
        }

        if ($totoa) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL OBRA ACTIVIDAD', $MesesOA, false);
            $pdf->totales('TOTAL EJERCIDO OBRA ACTIVIDAD ', $MesesOAE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER OBRA ACTIVIDAD ', $MesesOA, $MesesOAE, false);
        }

        if ($totpy) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->cabecera(false, $ur);
            }
            $pdf->totales('TOTAL PROYECTO', $MesesPY, false);
            $pdf->totales('TOTAL EJERCIDO PROYECTO ', $MesesPYE, false, true);
            $pdf->totalesdif('TOTAL POR EJERCER PROYECTO ', $MesesPY, $MesesPYE, false);
        }

        $pdf->ln(2);
        if ($pdf->CheckPageBreak(25)) {
            $pdf->setX(5);
            $pdf->Proyecto($row['py_id'], $row['py_nombre']);
            $pdf->Oa($row['oa_id'], $row['oa_nombre']);
            $pdf->clave($row['cfi_clave'], '- ' . $row['cfi_nombre']);
            $pdf->IdPartida($row['idp_id'], $row['idp_nombre']);
            $pdf->cabecera(false, $ur);
        } else {
            $pdf->Proyecto($row['py_id'], $row['py_nombre']);
            $pdf->Oa($row['oa_id'], $row['oa_nombre']);
            $pdf->clave($row['cfi_clave'], '- ' . $row['cfi_nombre']);
            $pdf->IdPartida($row['idp_id'], $row['idp_nombre']);
        }

        $celda = 0;
        $idp_id = $row['idp_id'];
        $idp_nombre = $row['idp_nombre'];
        $oa_id = $row['oa_id'];
        $py_id = $row['py_id'];

        $MesesPY = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesPYE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesOA = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesOAE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesIDP = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesIDPE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
    }

    if ($oa_id != $row['oa_id']) {
        if ($totoa) {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->setX(5);
                $pdf->cabecera(false, $ur);
                $pdf->totales('TOTAL OBRA ACTIVIDAD', $MesesOA, false);
                $pdf->totales('TOTAL EJERCIDO OBRA ACTIVIDAD ', $MesesOAE, false, true);
                $pdf->totalesdif('TOTAL POR EJERCER OBRA ACTIVIDAD ', $MesesOA, $MesesOAE, false);
            } else {
                $pdf->totales('TOTAL OBRA ACTIVIDAD', $MesesOA, false);
                $pdf->totales('TOTAL EJERCIDO OBRA ACTIVIDAD ', $MesesOAE, false, true);
                $pdf->totalesdif('TOTAL POR EJERCER OBRA ACTIVIDAD ', $MesesOA, $MesesOAE, false);
            }
        }

        $pdf->ln(2);
        if ($pdf->CheckPageBreak(25)) {
            $pdf->setX(5);
            if ($row['toa_id'] == 1) {
                $pdf->Oa($row['oa_id'], $row['oa_nombre'] . '  TIPO DE OBRA: ' . $row['toa_nombre']);
            } else {
                $pdf->Oa($row['oa_id'], $row['oa_nombre'] . '  TIPO DE OBRA: ' . $row['toa_nombre'] . ' NÚMERO DE OBRA ' . $row['oa_numObra']);
            }
            $pdf->clave($row['cfi_clave'], '- ' . $row['cfi_nombre']);
            $pdf->IdPartida($row['idp_id'], $row['idp_nombre']);
            $pdf->cabecera(false, $ur);
        } else {
            $pdf->Oa($row['oa_id'], $row['oa_nombre']);
            $pdf->clave($row['cfi_clave'], '- ' . $row['cfi_nombre']);
            $pdf->IdPartida($row['idp_id'], $row['idp_nombre']);
        }

        $celda = 0;
        $idp_id = $row['idp_id'];
        $idp_nombre = $row['idp_nombre'];
        $oa_id = $row['oa_id'];

        $MesesOA = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesOAE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);

        $MesesIDP = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesIDPE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
    }

    if ($idp_id != $row['idp_id']) {
        if ($pdf->CheckPageBreak(29)) {
            $pdf->setX(5);
        }

        $idp_id = $row['idp_id'];
        $idp_nombre = $row['idp_nombre'];
        $pdf->Idpartida($row['idp_id'], $row['idp_nombre']);

        $MesesIDP = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
        $MesesIDPE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0);
    }
    /*  Registro     */{
        if ($pdf->CheckPageBreak(25)) {
            $pdf->Programa($row['prg_id'], $row['prg_nombre']);
            $pdf->Oa($row['oa_id'], $row['oa_nombre']);
            $pdf->clave($row['cfi_clave'], '- ' . $row['cfi_nombre']);
            $pdf->Idpartida($row['idp_id'], $row['idp_nombre']);
            $pdf->cabecera(false, $ur);
        }
        $pdf->setX(5);
        $pdf->setFillColor(230, 230, 230);
        $pdf->setDrawcolor(100, 100, 100);
        if ($celda == 1) {
            $pdf->setRelleno("si");
            $celda = 0;
        } else {
            $pdf->setRelleno("no");
            $celda = 1;
        }
        $pdf->SetFont('Arial', '', 5.5);
        $pdf->setBorders("T");
        $pdf->setAltoLinea(2);
        $pdf->setWidths(array(40, 5, 31, 18, 17, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16, 17));
        $pdf->setAligns(array('L', 'L', 'L', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R'));
        $pdf->Row(array(
            utf8decode($row['pre_claveCompleta']),
            utf8decode($row['tcp_id']),
            utf8decode($row['pe_id'] . '-' . $row['pe_nombre']),
            utf8decode(number_format($row['pre_vigente'], 2)),
            utf8decode(number_format($row['vig_ene'], 2)),
            utf8decode(number_format($row['vig_feb'], 2)),
            utf8decode(number_format($row['vig_mar'], 2)),
            utf8decode(number_format($row['vig_abr'], 2)),
            utf8decode(number_format($row['vig_may'], 2)),
            utf8decode(number_format($row['vig_jun'], 2)),
            utf8decode(number_format($row['vig_jul'], 2)),
            utf8decode(number_format($row['vig_ago'], 2)),
            utf8decode(number_format($row['vig_sep'], 2)),
            utf8decode(number_format($row['vig_oct'], 2)),
            utf8decode(number_format($row['vig_nov'], 2)),
            utf8decode(number_format($row['vig_dic'], 2)),
        ));
        $pdf->setBorders(0);
        $pdf->setX(5);
        $pdf->Row(array(
            utf8decode('EJERCIDO:'),
            utf8decode(''),
            utf8decode(''),
            utf8decode(number_format($row['pre_ejercido'], 2)),
            utf8decode(number_format($row['eje_ene'], 2)),
            utf8decode(number_format($row['eje_feb'], 2)),
            utf8decode(number_format($row['eje_mar'], 2)),
            utf8decode(number_format($row['eje_abr'], 2)),
            utf8decode(number_format($row['eje_may'], 2)),
            utf8decode(number_format($row['eje_jun'], 2)),
            utf8decode(number_format($row['eje_jul'], 2)),
            utf8decode(number_format($row['eje_ago'], 2)),
            utf8decode(number_format($row['eje_sep'], 2)),
            utf8decode(number_format($row['eje_oct'], 2)),
            utf8decode(number_format($row['eje_nov'], 2)),
            utf8decode(number_format($row['eje_dic'], 2)),
        ));
        $pdf->setDrawcolor(240, 240, 240);
        $pdf->setBorders("B");
        $pdf->setX(5);
        $pdf->Row(array(
            utf8decode('POR EJERCER:'),
            utf8decode(''),
            utf8decode(''),
            utf8decode(number_format($row['pre_vigente'] - $row['pre_ejercido'], 2)),
            utf8decode(number_format($row['vig_ene'] - $row['eje_ene'], 2)),
            utf8decode(number_format($row['vig_feb'] - $row['eje_feb'], 2)),
            utf8decode(number_format($row['vig_mar'] - $row['eje_mar'], 2)),
            utf8decode(number_format($row['vig_abr'] - $row['eje_abr'], 2)),
            utf8decode(number_format($row['vig_may'] - $row['eje_may'], 2)),
            utf8decode(number_format($row['vig_jun'] - $row['eje_jun'], 2)),
            utf8decode(number_format($row['vig_jul'] - $row['eje_jul'], 2)),
            utf8decode(number_format($row['vig_ago'] - $row['eje_ago'], 2)),
            utf8decode(number_format($row['vig_sep'] - $row['eje_sep'], 2)),
            utf8decode(number_format($row['vig_oct'] - $row['eje_oct'], 2)),
            utf8decode(number_format($row['vig_nov'] - $row['eje_nov'], 2)),
            utf8decode(number_format($row['vig_dic'] - $row['eje_dic'], 2)),
        ));
        $pdf->setDrawcolor(240, 240, 240);
    }
    /*  sumas     */
    /* unidad ejecutora */
    $MesesUE['vig'] += $row['pre_vigente'];
    $MesesUE['ene'] += $row['vig_ene'];
    $MesesUE['feb'] += $row['vig_feb'];
    $MesesUE['mar'] += $row['vig_mar'];
    $MesesUE['abr'] += $row['vig_abr'];
    $MesesUE['may'] += $row['vig_may'];
    $MesesUE['jun'] += $row['vig_jun'];
    $MesesUE['jul'] += $row['vig_jul'];
    $MesesUE['ago'] += $row['vig_ago'];
    $MesesUE['sep'] += $row['vig_sep'];
    $MesesUE['oct'] += $row['vig_oct'];
    $MesesUE['nov'] += $row['vig_nov'];
    $MesesUE['dic'] += $row['vig_dic'];
    $MesesUEE['eje'] += $row['pre_ejercido'];
    $MesesUEE['ene'] += $row['eje_ene'];
    $MesesUEE['feb'] += $row['eje_feb'];
    $MesesUEE['mar'] += $row['eje_mar'];
    $MesesUEE['abr'] += $row['eje_abr'];
    $MesesUEE['may'] += $row['eje_may'];
    $MesesUEE['jun'] += $row['eje_jun'];
    $MesesUEE['jul'] += $row['eje_jul'];
    $MesesUEE['ago'] += $row['eje_ago'];
    $MesesUEE['sep'] += $row['eje_sep'];
    $MesesUEE['oct'] += $row['eje_oct'];
    $MesesUEE['nov'] += $row['eje_nov'];
    $MesesUEE['dic'] += $row['eje_dic'];

    /* Programa */
    $MesesPR['vig'] += $row['pre_vigente'];
    $MesesPR['ene'] += $row['vig_ene'];
    $MesesPR['feb'] += $row['vig_feb'];
    $MesesPR['mar'] += $row['vig_mar'];
    $MesesPR['abr'] += $row['vig_abr'];
    $MesesPR['may'] += $row['vig_may'];
    $MesesPR['jun'] += $row['vig_jun'];
    $MesesPR['jul'] += $row['vig_jul'];
    $MesesPR['ago'] += $row['vig_ago'];
    $MesesPR['sep'] += $row['vig_sep'];
    $MesesPR['oct'] += $row['vig_oct'];
    $MesesPR['nov'] += $row['vig_nov'];
    $MesesPR['dic'] += $row['vig_dic'];
    $MesesPRE['eje'] += $row['pre_ejercido'];
    $MesesPRE['ene'] += $row['eje_ene'];
    $MesesPRE['feb'] += $row['eje_feb'];
    $MesesPRE['mar'] += $row['eje_mar'];
    $MesesPRE['abr'] += $row['eje_abr'];
    $MesesPRE['may'] += $row['eje_may'];
    $MesesPRE['jun'] += $row['eje_jun'];
    $MesesPRE['jul'] += $row['eje_jul'];
    $MesesPRE['ago'] += $row['eje_ago'];
    $MesesPRE['sep'] += $row['eje_sep'];
    $MesesPRE['oct'] += $row['eje_oct'];
    $MesesPRE['nov'] += $row['eje_nov'];
    $MesesPRE['dic'] += $row['eje_dic'];

    /* Sub Programa */
    $MesesSPR['vig'] += $row['pre_vigente'];
    $MesesSPR['ene'] += $row['vig_ene'];
    $MesesSPR['feb'] += $row['vig_feb'];
    $MesesSPR['mar'] += $row['vig_mar'];
    $MesesSPR['abr'] += $row['vig_abr'];
    $MesesSPR['may'] += $row['vig_may'];
    $MesesSPR['jun'] += $row['vig_jun'];
    $MesesSPR['jul'] += $row['vig_jul'];
    $MesesSPR['ago'] += $row['vig_ago'];
    $MesesSPR['sep'] += $row['vig_sep'];
    $MesesSPR['oct'] += $row['vig_oct'];
    $MesesSPR['nov'] += $row['vig_nov'];
    $MesesSPR['dic'] += $row['vig_dic'];
    $MesesSPRE['eje'] += $row['pre_ejercido'];
    $MesesSPRE['ene'] += $row['eje_ene'];
    $MesesSPRE['feb'] += $row['eje_feb'];
    $MesesSPRE['mar'] += $row['eje_mar'];
    $MesesSPRE['abr'] += $row['eje_abr'];
    $MesesSPRE['may'] += $row['eje_may'];
    $MesesSPRE['jun'] += $row['eje_jun'];
    $MesesSPRE['jul'] += $row['eje_jul'];
    $MesesSPRE['ago'] += $row['eje_ago'];
    $MesesSPRE['sep'] += $row['eje_sep'];
    $MesesSPRE['oct'] += $row['eje_oct'];
    $MesesSPRE['nov'] += $row['eje_nov'];
    $MesesSPRE['dic'] += $row['eje_dic'];

    /* Proyecto */
    $MesesPY['vig'] += $row['pre_vigente'];
    $MesesPY['ene'] += $row['vig_ene'];
    $MesesPY['feb'] += $row['vig_feb'];
    $MesesPY['mar'] += $row['vig_mar'];
    $MesesPY['abr'] += $row['vig_abr'];
    $MesesPY['may'] += $row['vig_may'];
    $MesesPY['jun'] += $row['vig_jun'];
    $MesesPY['jul'] += $row['vig_jul'];
    $MesesPY['ago'] += $row['vig_ago'];
    $MesesPY['sep'] += $row['vig_sep'];
    $MesesPY['oct'] += $row['vig_oct'];
    $MesesPY['nov'] += $row['vig_nov'];
    $MesesPY['dic'] += $row['vig_dic'];
    $MesesPYE['eje'] += $row['pre_ejercido'];
    $MesesPYE['ene'] += $row['eje_ene'];
    $MesesPYE['feb'] += $row['eje_feb'];
    $MesesPYE['mar'] += $row['eje_mar'];
    $MesesPYE['abr'] += $row['eje_abr'];
    $MesesPYE['may'] += $row['eje_may'];
    $MesesPYE['jun'] += $row['eje_jun'];
    $MesesPYE['jul'] += $row['eje_jul'];
    $MesesPYE['ago'] += $row['eje_ago'];
    $MesesPYE['sep'] += $row['eje_sep'];
    $MesesPYE['oct'] += $row['eje_oct'];
    $MesesPYE['nov'] += $row['eje_nov'];
    $MesesPYE['dic'] += $row['eje_dic'];

    /* Obra Actividad */
    $MesesOA['vig'] += $row['pre_vigente'];
    $MesesOA['ene'] += $row['vig_ene'];
    $MesesOA['feb'] += $row['vig_feb'];
    $MesesOA['mar'] += $row['vig_mar'];
    $MesesOA['abr'] += $row['vig_abr'];
    $MesesOA['may'] += $row['vig_may'];
    $MesesOA['jun'] += $row['vig_jun'];
    $MesesOA['jul'] += $row['vig_jul'];
    $MesesOA['ago'] += $row['vig_ago'];
    $MesesOA['sep'] += $row['vig_sep'];
    $MesesOA['oct'] += $row['vig_oct'];
    $MesesOA['nov'] += $row['vig_nov'];
    $MesesOA['dic'] += $row['vig_dic'];
    $MesesOAE['eje'] += $row['pre_ejercido'];
    $MesesOAE['ene'] += $row['eje_ene'];
    $MesesOAE['feb'] += $row['eje_feb'];
    $MesesOAE['mar'] += $row['eje_mar'];
    $MesesOAE['abr'] += $row['eje_abr'];
    $MesesOAE['may'] += $row['eje_may'];
    $MesesOAE['jun'] += $row['eje_jun'];
    $MesesOAE['jul'] += $row['eje_jul'];
    $MesesOAE['ago'] += $row['eje_ago'];
    $MesesOAE['sep'] += $row['eje_sep'];
    $MesesOAE['oct'] += $row['eje_oct'];
    $MesesOAE['nov'] += $row['eje_nov'];
    $MesesOAE['dic'] += $row['eje_dic'];

    /* IDENTIFICADOR DE PARTIDA */
    $MesesIDP['vig'] += $row['pre_vigente'];
    $MesesIDP['ene'] += $row['vig_ene'];
    $MesesIDP['feb'] += $row['vig_feb'];
    $MesesIDP['mar'] += $row['vig_mar'];
    $MesesIDP['abr'] += $row['vig_abr'];
    $MesesIDP['may'] += $row['vig_may'];
    $MesesIDP['jun'] += $row['vig_jun'];
    $MesesIDP['jul'] += $row['vig_jul'];
    $MesesIDP['ago'] += $row['vig_ago'];
    $MesesIDP['sep'] += $row['vig_sep'];
    $MesesIDP['oct'] += $row['vig_oct'];
    $MesesIDP['nov'] += $row['vig_nov'];
    $MesesIDP['dic'] += $row['vig_dic'];
    $MesesIDPE['eje'] += $row['pre_ejercido'];
    $MesesIDPE['ene'] += $row['eje_ene'];
    $MesesIDPE['feb'] += $row['eje_feb'];
    $MesesIDPE['mar'] += $row['eje_mar'];
    $MesesIDPE['abr'] += $row['eje_abr'];
    $MesesIDPE['may'] += $row['eje_may'];
    $MesesIDPE['jun'] += $row['eje_jun'];
    $MesesIDPE['jul'] += $row['eje_jul'];
    $MesesIDPE['ago'] += $row['eje_ago'];
    $MesesIDPE['sep'] += $row['eje_sep'];
    $MesesIDPE['oct'] += $row['eje_oct'];
    $MesesIDPE['nov'] += $row['eje_nov'];
    $MesesIDPE['dic'] += $row['eje_dic'];

    /* Partida Generica */
    $MesesPG['vig'] += $row['pre_vigente'];
    $MesesPG['ene'] += $row['vig_ene'];
    $MesesPG['feb'] += $row['vig_feb'];
    $MesesPG['mar'] += $row['vig_mar'];
    $MesesPG['abr'] += $row['vig_abr'];
    $MesesPG['may'] += $row['vig_may'];
    $MesesPG['jun'] += $row['vig_jun'];
    $MesesPG['jul'] += $row['vig_jul'];
    $MesesPG['ago'] += $row['vig_ago'];
    $MesesPG['sep'] += $row['vig_sep'];
    $MesesPG['oct'] += $row['vig_oct'];
    $MesesPG['nov'] += $row['vig_nov'];
    $MesesPG['dic'] += $row['vig_dic'];
    $MesesPGE['eje'] += $row['pre_ejercido'];
    $MesesPGE['ene'] += $row['eje_ene'];
    $MesesPGE['feb'] += $row['eje_feb'];
    $MesesPGE['mar'] += $row['eje_mar'];
    $MesesPGE['abr'] += $row['eje_abr'];
    $MesesPGE['may'] += $row['eje_may'];
    $MesesPGE['jun'] += $row['eje_jun'];
    $MesesPGE['jul'] += $row['eje_jul'];
    $MesesPGE['ago'] += $row['eje_ago'];
    $MesesPGE['sep'] += $row['eje_sep'];
    $MesesPGE['oct'] += $row['eje_oct'];
    $MesesPGE['nov'] += $row['eje_nov'];
    $MesesPGE['dic'] += $row['eje_dic'];

    /* GENERALES */
    $MesesT['vig'] += $row['pre_vigente'];
    $MesesT['ene'] += $row['vig_ene'];
    $MesesT['feb'] += $row['vig_feb'];
    $MesesT['mar'] += $row['vig_mar'];
    $MesesT['abr'] += $row['vig_abr'];
    $MesesT['may'] += $row['vig_may'];
    $MesesT['jun'] += $row['vig_jun'];
    $MesesT['jul'] += $row['vig_jul'];
    $MesesT['ago'] += $row['vig_ago'];
    $MesesT['sep'] += $row['vig_sep'];
    $MesesT['oct'] += $row['vig_oct'];
    $MesesT['nov'] += $row['vig_nov'];
    $MesesT['dic'] += $row['vig_dic'];
    $MesesTE['eje'] += $row['pre_ejercido'];
    $MesesTE['ene'] += $row['eje_ene'];
    $MesesTE['feb'] += $row['eje_feb'];
    $MesesTE['mar'] += $row['eje_mar'];
    $MesesTE['abr'] += $row['eje_abr'];
    $MesesTE['may'] += $row['eje_may'];
    $MesesTE['jun'] += $row['eje_jun'];
    $MesesTE['jul'] += $row['eje_jul'];
    $MesesTE['ago'] += $row['eje_ago'];
    $MesesTE['sep'] += $row['eje_sep'];
    $MesesTE['oct'] += $row['eje_oct'];
    $MesesTE['nov'] += $row['eje_nov'];
    $MesesTE['dic'] += $row['eje_dic'];

    /* GENERALES DIFERENCIA */
    $MesesTDif['dif'] += ($row['pre_vigente'] - $row['pre_ejercido']);
    $MesesTDif['ene'] += ($row['vig_ene'] - $row['eje_ene']);
    $MesesTDif['feb'] += ($row['vig_feb'] - $row['eje_feb']);
    $MesesTDif['mar'] += ($row['vig_mar'] - $row['eje_mar']);
    $MesesTDif['abr'] += ($row['vig_abr'] - $row['eje_abr']);
    $MesesTDif['may'] += ($row['vig_may'] - $row['eje_may']);
    $MesesTDif['jun'] += ($row['vig_jun'] - $row['eje_jun']);
    $MesesTDif['jul'] += ($row['vig_jul'] - $row['eje_jul']);
    $MesesTDif['ago'] += ($row['vig_ago'] - $row['eje_ago']);
    $MesesTDif['sep'] += ($row['vig_sep'] - $row['eje_sep']);
    $MesesTDif['oct'] += ($row['vig_oct'] - $row['eje_oct']);
    $MesesTDif['nov'] += ($row['vig_nov'] - $row['eje_nov']);
    $MesesTDif['dic'] += ($row['vig_dic'] - $row['eje_dic']);
} while ($row = $conexion->extraer());
$pdf->CheckPageBreak(10);

if ($pdf->CheckPageBreak(25)) {
    $pdf->cabecera(true, $ur);
    $pdf->totales('TOTAL ' . $idp_nombre, $MesesIDP, true);
    $pdf->totales('TOTAL EJERCIDO ' . $idp_nombre, $MesesIDPE, true, true);
    $pdf->totalesdif('TOTAL POR EJERCER ' . $idp_nombre, $MesesIDP, $MesesIDPE, true);
}

if ($totoa) {
    $pdf->setX(5);
    $pdf->totales('TOTAL OBRA ACTIVIDAD', $MesesOA, true);
    $pdf->totales('TOTAL EJERCIDO OBRA ACTIVIDAD ', $MesesOAE, true, true);
    $pdf->totalesdif('TOTAL POR EJERCER OBRA ACTIVIDAD ', $MesesOA, $MesesOAE, true);
}

if ($totpy) {
    $pdf->totales('TOTAL PROYECTO', $MesesPY, true);
    $pdf->totales('TOTAL EJERCIDO PROYECTO ', $MesesPYE, true, true);
    $pdf->totalesdif('TOTAL POR EJERCER PROYECTO ', $MesesPY, $MesesPYE, true);
}

if ($totspr) {
    $pdf->totales('TOTAL SUBPROGRAMA', $MesesSPR, true);
    $pdf->totales('TOTAL EJERCIDO SUBPROGRAMA ', $MesesSPRE, true, true);
    $pdf->totalesdif('TOTAL POR EJERCER SUBPROGRAMA ', $MesesSPR, $MesesSPRE, true);
}

if ($totprg) {
    $pdf->totales('TOTAL PROGRAMA', $MesesPR, true);
    $pdf->totales('TOTAL EJERCIDO PROGRAMA ', $MesesPRE, true, true);
    $pdf->totalesdif('TOTAL POR EJERCER PROGRAMA ', $MesesPR, $MesesPRE, true);
}

if ($totue) {
    $pdf->totales('TOTAL UNIDAD EJECUTORA', $MesesUE, true);
    $pdf->totales('TOTAL EJERCIDO UNIDAD EJECUTORA ', $MesesUEE, true, true);
    $pdf->totalesdif('TOTAL POR EJERCER UNIDAD EJECUTORA ', $MesesUE, $MesesUEE, true, true);
}

if ($totur) {
    $pdf->totales('TOTAL DE LA UR', $MesesT, true, false);
    $pdf->totales('TOTAL DE LA UR EJERCIDO', $MesesTE, true, true);
    $pdf->totalesdif('TOTAL DE LA UR POR EJERCER', $MesesT, $MesesTE, true, true);
}
}
$pdf->Output();
