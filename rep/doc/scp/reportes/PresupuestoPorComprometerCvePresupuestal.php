<?php
session_start();
set_time_limit(0);
//librerias
require_once '../../../lib/reportetabla.1.8.php';
require_once '../../../lib/conversionNumLetras.php';
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../lib/constantes.php';
require_once '../../../../lib/helpers.php';
require_once '../../../../ent/UnidadResponsable.php';

class reportetabla extends PDF
{
    public function __construct()
    {
        parent::__construct();
        switch ($_SESSION[_S]['nvj_id']) {
            case '8':
                break;
            case '7':
                break;
            case '6':
                break;
            case '5':
                break;
            case '4':
                break;
            case '3':
                break;
            case '2':
                break;
            case '1':
                break;
            default:
                break;
        }
    }

    public function Footer($lastPage = true)
    {
        $row = $this->getDatos();
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

    public function Header()
    { //Encabezado del documento

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
        $ix = $this->GetX();
        $iy = $this->GetY();
        if ($_SESSION[_S]['nvj_id'] > 2)
        //Por elecciones se actualiza logo
        {
            $this->Image('../../../../img/logo_sefin _rep_presu.jpeg', $ix + 220, $iy, '35', '20', 'JPEG', '');
        }

        //$this->Image('../../../../img/logosefin-color-fblanco.png', $ix+178,$iy    , '99' , '22' , 'PNG', '');
        else {
            $this->Image('../../../../img/escudo2.jpg', $ix + 178, $iy, '99', '22', 'JPG', '');
        }

        $ytmp = $this->GetY();

        $this->SetFont('Arial', 'B', 10);
        $this->SetXY(5, $iy + 4);
        $ytmp = $this->GetY();
        $this->Cell(195, 4, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
        $this->SetXY(5, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(180, 4, utf8decode('SECRETARÍA DE FINANZAS - SUBSECRETARÍA DE EGRESOS, CONTABILIDAD Y TESORERÍA'), 0, 1, 'L');
        $this->ln(3);
        $this->SetXY(5, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(180, 4, utf8decode('PRESUPUESTO CALENDARIZADO DISPONIBLE POR CLAVE PRESUPUESTAL'), 0, 1, 'L');
        $this->ln(3);
        $ytmp = $this->GetY();
        $this->SetXY(5, $ytmp - 3);
        $ytmp = $this->GetY();
        if ($row) {
            $this->SetFont('Arial', 'B', 8);
            $this->Cell(180, 4, utf8decode('UNIDAD RESPONSABLE:' . $row['gpo_id'] . $row['ur_id'] . ' -' . $row['ur_nombre']), 0, 1, 'L');
            $this->ln(3);
            $ytmp = $this->GetY();
            $this->ln(1);
        }
    }

    public function cabecera($fin)
    {
        $this->setRelleno("no");
        $this->SetFont('Arial', 'B', 5);
        $this->SetTextColor(0, 0, 0);
        $this->setX(5);
        $this->SetDrawColor(180, 180, 180);
        $this->SetBorders("TB");
        if ($fin) {
            $this->SetWidths(array(65, 20, 17, 17, 17, 17, 17, 17, 17, 17, 17, 17, 17, 17));
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
}

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
$params = [$gpo_id, $ur_id];
$sql = '
        SELECT * FROM presupuesto
        JOIN presupuestoVigente USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        JOIN presupuestoComprometido USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
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
        WHERE gpo_id = ? and ur_id = ?';

if (isset($_POST['oa']) && $_POST['oa'] != "") {
    $sql .= ' AND oa_id = ?';
    $totue = $totprg = $totspr = $totpy = $totoa = true;
    $totue = $totprg = $totspr = $totpy = false;
    $totur = false;
    array_push($params, $_POST['oa']);
}

if (isset($_POST['py']) && $_POST['py'] != "") {
    $sql .= ' AND py_id = ? ';
    $totue = $totprg = $totspr = $totpy = $totoa = true;
    $totue = $totprg = $totspr = false;
    $totur = false;
    array_push($params, $_POST['py']);
}

if (isset($_POST['subprograma']) && $_POST['subprograma'] != "") {
    $sql .= ' AND spr_id = ?';
    $totue = $totprg = $totspr = $totpy = $totoa = true;
    $totue = $totprg = false;
    $totur = false;
    array_push($params, $_POST['subprograma']);
}

if (isset($_POST['programa']) && $_POST['programa'] != "") {
    $sql .= ' AND prg_id= ? ';
    $totue = $totprg = $totspr = $totpy = $totoa = true;
    $totue = false;
    $totur = false;
    array_push($params, $_POST['programa']);
}

if (isset($_POST['unidad']) && $_POST['unidad'] != "") {
    $sql .= ' AND presupuesto.ue_id = ?';
    $totue = $totprg = $totspr = $totpy = $totoa = true;
    $totur = false;
    array_push($params, $_POST['unidad']);
}

/* CLAVE DE FINANCIAMIENTO */
if (isset($_POST['fuente']) && $_POST['fuente'] != "") {
    $sql .= ' AND fte_id = ?';
    $totur = false;
    array_push($params, $_POST['fuente']);
}

if (isset($_POST['subfuente']) && $_POST['subfuente'] != "") {
    $sql .= ' AND sft_id = ?';
    $totur = false;
    array_push($params, $_POST['subfuente']);
}

if (isset($_POST['origen']) && $_POST['origen'] != "") {
    $sql .= ' AND ori_id = ?';
    $totur = false;
    array_push($params, $_POST['origen']);
}

if (isset($_POST['suborigen']) && $_POST['suborigen'] != "") {
    $sql .= ' AND sor_id = ?';
    $totur = false;
    array_push($params, $_POST['suborigen']);
}

if (isset($_POST['clasificacion']) && $_POST['clasificacion'] != "") {
    $sql .= ' AND cff_id = ?';
    $totur = false;
    array_push($params, $_POST['clasificacion']);
}

if (isset($_POST['referencia']) && $_POST['referencia'] != "") {
    $sql .= ' AND cfi_id = ?';
    $totur = false;
    array_push($params, $_POST['referencia']);
}

if (isset($_POST['cfi_anio']) && $_POST['cfi_anio'] != "") {
    $sql .= ' AND cfi_anio = ?';
    $totur = false;
    array_push($params, $_POST['cfi_anio']);
}

if (isset($_POST['generica']) && $_POST['generica'] != "") {
    $sql .= ' AND pg_id = ?';
    $totur = false;
    $totue = $totprg = $totspr = $totpy = $totoa = false;
    array_push($params, $_POST['generica']);
}

if (isset($_POST['especifica']) && $_POST['especifica'] != "") {
    $sql .= ' AND pe_id = ?';
    $totur = false;
    $totue = $totprg = $totspr = $totpy = $totoa = false;
    array_push($params, $_POST['especifica']);
}

if (isset($_POST['compra']) && $_POST['compra'] != "") {
    $sql .= ' AND tcp_id = ?';
    $totur = false;
    array_push($params, $_POST['compra']);
}

$sql .= ' ORDER BY presupuesto.ue_id,prg_id,spr_id,py_id,oa_id,ecf_id,idp_id,pg_id,pe_id';
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
if ($row) {
    /* variables  de cambios */
    $ue_id = $row['ue_id'];
    $prg_id = $row['prg_id'];
    $spr_id = $row['spr_id'];
    $py_id = $row['py_id'];
    $oa_id = $row['oa_id'];
    $idp_id = $row['idp_id'];
    $idp_nombre = $row['idp_nombre'];
    $pg_id = $row['pg_id'];

    /*  fin variables  */
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
    $pdf->cabecera(false);

    do {
        if ($ue_id != $row['ue_id']) {
            if ($totoa) {
                if ($pdf->CheckPageBreak(25)) {
                    $pdf->cabecera(false);
                }
                $pdf->totales('TOTAL OBRA ACTIVIDAD', $MesesOA, false);
                $pdf->totales('TOTAL COMPROMETIDO OBRA ACTIVIDAD ', $MesesOAE, false, true);
                $pdf->totalesdif('TOTAL POR COMPROMETER OBRA ACTIVIDAD ', $MesesOA, $MesesOAE, false);
            }

            if ($totpy) {
                if ($pdf->CheckPageBreak(25)) {
                    $pdf->cabecera(false);
                }
                $pdf->totales('TOTAL PROYECTO', $MesesPY, false);
                $pdf->totales('TOTAL COMPROMETIDO PROYECTO ', $MesesPYE, false, true);
                $pdf->totalesdif('TOTAL POR COMPROMETER PROYECTO ', $MesesPY, $MesesPYE, false);
            }

            if ($totspr) {
                if ($pdf->CheckPageBreak(25)) {
                    $pdf->cabecera(false);
                }
                $pdf->totales('TOTAL SUBPROGRAMA', $MesesSPR, false);
                $pdf->totales('TOTAL COMPROMETIDO SUBPROGRAMA ', $MesesSPRE, false, true);
                $pdf->totalesdif('TOTAL POR COMPROMETER SUBPROGRAMA ', $MesesSPR, $MesesSPRE, false);
            }

            if ($totprg) {
                if ($pdf->CheckPageBreak(25)) {
                    $pdf->cabecera(false);
                }
                $pdf->totales('TOTAL PROGRAMA', $MesesPR, false);
                $pdf->totales('TOTAL COMPROMETIDO PROGRAMA ', $MesesPRE, false, true);
                $pdf->totalesdif('TOTAL POR COMPROMETER PROGRAMA ', $MesesPR, $MesesPRE, false);
            }

            if ($totue) {
                if ($pdf->CheckPageBreak(25)) {
                    $pdf->cabecera(false);
                }
                $pdf->totales('TOTAL UNIDAD EJECUTORA', $MesesUE, false);
                $pdf->totales('TOTAL COMPROMETIDO UNIDAD EJECUTORA ', $MesesUEE, false, true);
                $pdf->totalesdif('TOTAL POR COMPROMETER UNIDAD EJECUTORA ', $MesesUE, $MesesUEE, false);
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
                $pdf->cabecera(false);
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
                    $pdf->cabecera(false);
                }
                $pdf->totales('TOTAL OBRA ACTIVIDAD', $MesesOA, false);
                $pdf->totales('TOTAL COMPROMETIDO OBRA ACTIVIDAD ', $MesesOAE, false, true);
                $pdf->totalesdif('TOTAL POR COMPROMETER OBRA ACTIVIDAD ', $MesesOA, $MesesOAE, false);
            }

            if ($totpy) {
                if ($pdf->CheckPageBreak(25)) {
                    $pdf->cabecera(false);
                }
                $pdf->totales('TOTAL PROYECTO', $MesesPY, false);
                $pdf->totales('TOTAL COMPROMETIDO PROYECTO ', $MesesPYE, false, true);
                $pdf->totalesdif('TOTAL POR COMPROMETER PROYECTO ', $MesesPY, $MesesPYE, false);
            }

            if ($totspr) {
                if ($pdf->CheckPageBreak(25)) {
                    $pdf->cabecera(false);
                }
                $pdf->totales('TOTAL SUBPROGRAMA', $MesesSPR, false);
                $pdf->totales('TOTAL COMPROMETIDO SUBPROGRAMA ', $MesesSPRE, false, true);
                $pdf->totalesdif('TOTAL POR COMPROMETER SUBPROGRAMA ', $MesesSPR, $MesesSPRE, false);
            }

            if ($totprg) {
                if ($pdf->CheckPageBreak(25)) {
                    $pdf->cabecera(false);
                }
                $pdf->totales('TOTAL PROGRAMA', $MesesPR, false);
                $pdf->totales('TOTAL COMPROMETIDO PROGRAMA ', $MesesPRE, false, true);
                $pdf->totalesdif('TOTAL POR COMPROMETER PROGRAMA ', $MesesPR, $MesesPRE, false);
            }

            if ($pdf->CheckPageBreak(25)) {
                $pdf->setX(5);
                $pdf->Programa($row['prg_id'], $row['prg_nombre']);
                $pdf->subPrograma($row['spr_id'], $row['spr_nombre']);
                $pdf->Proyecto($row['py_id'], $row['py_nombre']);
                $pdf->Oa($row['oa_id'], $row['oa_nombre']);
                $pdf->clave($row['cfi_clave'], '- ' . $row['cfi_nombre']);
                $pdf->IdPartida($row['idp_id'], $row['idp_nombre']);
                $pdf->cabecera(false);
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
                    $pdf->cabecera(false);
                }
                $pdf->totales('TOTAL OBRA ACTIVIDAD', $MesesOA, false);
                $pdf->totales('TOTAL COMPROMETIDO OBRA ACTIVIDAD ', $MesesOAE, false, true);
                $pdf->totalesdif('TOTAL POR COMPROMETER OBRA ACTIVIDAD ', $MesesOA, $MesesOAE, false);
            }

            if ($totpy) {
                if ($pdf->CheckPageBreak(25)) {
                    $pdf->cabecera(false);
                }
                $pdf->totales('TOTAL PROYECTO', $MesesPY, false);
                $pdf->totales('TOTAL COMPROMETIDO PROYECTO ', $MesesPYE, false, true);
                $pdf->totalesdif('TOTAL POR COMPROMETER PROYECTO ', $MesesPY, $MesesPYE, false);
            }

            if ($totspr) {
                if ($pdf->CheckPageBreak(25)) {
                    $pdf->cabecera(false);
                }
                $pdf->totales('TOTAL SUBPROGRAMA', $MesesSPR, false);
                $pdf->totales('TOTAL COMPROMETIDO SUBPROGRAMA ', $MesesSPRE, false, true);
                $pdf->totalesdif('TOTAL POR COMPROMETER SUBPROGRAMA ', $MesesSPR, $MesesSPRE, false);
            }

            if ($pdf->CheckPageBreak(25)) {
                $pdf->setX(5);
                $pdf->subPrograma($row['spr_id'], $row['spr_nombre']);
                $pdf->Proyecto($row['py_id'], $row['py_nombre']);
                $pdf->Oa($row['oa_id'], $row['oa_nombre']);
                $pdf->clave($row['cfi_clave'], '- ' . $row['cfi_nombre']);
                $pdf->IdPartida($row['idp_id'], $row['idp_nombre']);
                $pdf->cabecera(false);
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
                $pdf->cabecera(false);
            }

            if ($totoa) {
                if ($pdf->CheckPageBreak(25)) {
                    $pdf->cabecera(false);
                }
                $pdf->totales('TOTAL OBRA ACTIVIDAD', $MesesOA, false);
                $pdf->totales('TOTAL COMPROMETIDO OBRA ACTIVIDAD ', $MesesOAE, false, true);
                $pdf->totalesdif('TOTAL POR COMPROMETER OBRA ACTIVIDAD ', $MesesOA, $MesesOAE, false);
            }

            if ($totpy) {
                if ($pdf->CheckPageBreak(25)) {
                    $pdf->cabecera(false);
                }
                $pdf->totales('TOTAL PROYECTO', $MesesPY, false);
                $pdf->totales('TOTAL COMPROMETIDO PROYECTO ', $MesesPYE, false, true);
                $pdf->totalesdif('TOTAL POR COMPROMETER PROYECTO ', $MesesPY, $MesesPYE, false);
            }

            $pdf->ln(2);
            if ($pdf->CheckPageBreak(25)) {
                $pdf->setX(5);
                $pdf->Proyecto($row['py_id'], $row['py_nombre']);
                $pdf->Oa($row['oa_id'], $row['oa_nombre']);
                $pdf->clave($row['cfi_clave'], '- ' . $row['cfi_nombre']);
                $pdf->IdPartida($row['idp_id'], $row['idp_nombre']);
                $pdf->cabecera(false);
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
                    $pdf->cabecera(false);
                    $pdf->totales('TOTAL OBRA ACTIVIDAD', $MesesOA, false);
                    $pdf->totales('TOTAL COMPROMETIDO OBRA ACTIVIDAD ', $MesesOAE, false, true);
                    $pdf->totalesdif('TOTAL POR COMPROMETER OBRA ACTIVIDAD ', $MesesOA, $MesesOAE, false);
                } else {
                    $pdf->totales('TOTAL OBRA ACTIVIDAD', $MesesOA, false);
                    $pdf->totales('TOTAL COMPROMETIDO OBRA ACTIVIDAD ', $MesesOAE, false, true);
                    $pdf->totalesdif('TOTAL POR COMPROMETER OBRA ACTIVIDAD ', $MesesOA, $MesesOAE, false);
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
                $pdf->cabecera(false);
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
        /*  Registro     */ {
            if ($pdf->CheckPageBreak(25)) {
                $pdf->Programa($row['prg_id'], $row['prg_nombre']);
                $pdf->Oa($row['oa_id'], $row['oa_nombre']);
                $pdf->clave($row['cfi_clave'], '- ' . $row['cfi_nombre']);
                $pdf->Idpartida($row['idp_id'], $row['idp_nombre']);
                $pdf->cabecera(false);
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
                utf8decode('COMPROMETIDO:'),
                utf8decode(''),
                utf8decode(''),
                utf8decode(number_format($row['pre_comprometido'], 2)),
                utf8decode(number_format($row['com_ene'], 2)),
                utf8decode(number_format($row['com_feb'], 2)),
                utf8decode(number_format($row['com_mar'], 2)),
                utf8decode(number_format($row['com_abr'], 2)),
                utf8decode(number_format($row['com_may'], 2)),
                utf8decode(number_format($row['com_jun'], 2)),
                utf8decode(number_format($row['com_jul'], 2)),
                utf8decode(number_format($row['com_ago'], 2)),
                utf8decode(number_format($row['com_sep'], 2)),
                utf8decode(number_format($row['com_oct'], 2)),
                utf8decode(number_format($row['com_nov'], 2)),
                utf8decode(number_format($row['com_dic'], 2)),
            ));
            $pdf->setDrawcolor(240, 240, 240);
            $pdf->setBorders("B");
            $pdf->setX(5);
            $pdf->Row(array(
                utf8decode('POR COMPROMETER:'),
                utf8decode(''),
                utf8decode(''),
                utf8decode(number_format($row['pre_vigente'] - $row['pre_comprometido'], 2)),
                utf8decode(number_format($row['vig_ene'] - $row['com_ene'], 2)),
                utf8decode(number_format($row['vig_feb'] - $row['com_feb'], 2)),
                utf8decode(number_format($row['vig_mar'] - $row['com_mar'], 2)),
                utf8decode(number_format($row['vig_abr'] - $row['com_abr'], 2)),
                utf8decode(number_format($row['vig_may'] - $row['com_may'], 2)),
                utf8decode(number_format($row['vig_jun'] - $row['com_jun'], 2)),
                utf8decode(number_format($row['vig_jul'] - $row['com_jul'], 2)),
                utf8decode(number_format($row['vig_ago'] - $row['com_ago'], 2)),
                utf8decode(number_format($row['vig_sep'] - $row['com_sep'], 2)),
                utf8decode(number_format($row['vig_oct'] - $row['com_oct'], 2)),
                utf8decode(number_format($row['vig_nov'] - $row['com_nov'], 2)),
                utf8decode(number_format($row['vig_dic'] - $row['com_dic'], 2)),
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
        $MesesUEE['eje'] += $row['pre_comprometido'];
        $MesesUEE['ene'] += $row['com_ene'];
        $MesesUEE['feb'] += $row['com_feb'];
        $MesesUEE['mar'] += $row['com_mar'];
        $MesesUEE['abr'] += $row['com_abr'];
        $MesesUEE['may'] += $row['com_may'];
        $MesesUEE['jun'] += $row['com_jun'];
        $MesesUEE['jul'] += $row['com_jul'];
        $MesesUEE['ago'] += $row['com_ago'];
        $MesesUEE['sep'] += $row['com_sep'];
        $MesesUEE['oct'] += $row['com_oct'];
        $MesesUEE['nov'] += $row['com_nov'];
        $MesesUEE['dic'] += $row['com_dic'];

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
        $MesesPRE['eje'] += $row['pre_comprometido'];
        $MesesPRE['ene'] += $row['com_ene'];
        $MesesPRE['feb'] += $row['com_feb'];
        $MesesPRE['mar'] += $row['com_mar'];
        $MesesPRE['abr'] += $row['com_abr'];
        $MesesPRE['may'] += $row['com_may'];
        $MesesPRE['jun'] += $row['com_jun'];
        $MesesPRE['jul'] += $row['com_jul'];
        $MesesPRE['ago'] += $row['com_ago'];
        $MesesPRE['sep'] += $row['com_sep'];
        $MesesPRE['oct'] += $row['com_oct'];
        $MesesPRE['nov'] += $row['com_nov'];
        $MesesPRE['dic'] += $row['com_dic'];

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
        $MesesSPRE['eje'] += $row['pre_comprometido'];
        $MesesSPRE['ene'] += $row['com_ene'];
        $MesesSPRE['feb'] += $row['com_feb'];
        $MesesSPRE['mar'] += $row['com_mar'];
        $MesesSPRE['abr'] += $row['com_abr'];
        $MesesSPRE['may'] += $row['com_may'];
        $MesesSPRE['jun'] += $row['com_jun'];
        $MesesSPRE['jul'] += $row['com_jul'];
        $MesesSPRE['ago'] += $row['com_ago'];
        $MesesSPRE['sep'] += $row['com_sep'];
        $MesesSPRE['oct'] += $row['com_oct'];
        $MesesSPRE['nov'] += $row['com_nov'];
        $MesesSPRE['dic'] += $row['com_dic'];

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
        $MesesPYE['eje'] += $row['pre_comprometido'];
        $MesesPYE['ene'] += $row['com_ene'];
        $MesesPYE['feb'] += $row['com_feb'];
        $MesesPYE['mar'] += $row['com_mar'];
        $MesesPYE['abr'] += $row['com_abr'];
        $MesesPYE['may'] += $row['com_may'];
        $MesesPYE['jun'] += $row['com_jun'];
        $MesesPYE['jul'] += $row['com_jul'];
        $MesesPYE['ago'] += $row['com_ago'];
        $MesesPYE['sep'] += $row['com_sep'];
        $MesesPYE['oct'] += $row['com_oct'];
        $MesesPYE['nov'] += $row['com_nov'];
        $MesesPYE['dic'] += $row['com_dic'];

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
        $MesesOAE['eje'] += $row['pre_comprometido'];
        $MesesOAE['ene'] += $row['com_ene'];
        $MesesOAE['feb'] += $row['com_feb'];
        $MesesOAE['mar'] += $row['com_mar'];
        $MesesOAE['abr'] += $row['com_abr'];
        $MesesOAE['may'] += $row['com_may'];
        $MesesOAE['jun'] += $row['com_jun'];
        $MesesOAE['jul'] += $row['com_jul'];
        $MesesOAE['ago'] += $row['com_ago'];
        $MesesOAE['sep'] += $row['com_sep'];
        $MesesOAE['oct'] += $row['com_oct'];
        $MesesOAE['nov'] += $row['com_nov'];
        $MesesOAE['dic'] += $row['com_dic'];

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
        $MesesIDPE['eje'] += $row['pre_comprometido'];
        $MesesIDPE['ene'] += $row['com_ene'];
        $MesesIDPE['feb'] += $row['com_feb'];
        $MesesIDPE['mar'] += $row['com_mar'];
        $MesesIDPE['abr'] += $row['com_abr'];
        $MesesIDPE['may'] += $row['com_may'];
        $MesesIDPE['jun'] += $row['com_jun'];
        $MesesIDPE['jul'] += $row['com_jul'];
        $MesesIDPE['ago'] += $row['com_ago'];
        $MesesIDPE['sep'] += $row['com_sep'];
        $MesesIDPE['oct'] += $row['com_oct'];
        $MesesIDPE['nov'] += $row['com_nov'];
        $MesesIDPE['dic'] += $row['com_dic'];

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
        $MesesPGE['eje'] += $row['pre_comprometido'];
        $MesesPGE['ene'] += $row['com_ene'];
        $MesesPGE['feb'] += $row['com_feb'];
        $MesesPGE['mar'] += $row['com_mar'];
        $MesesPGE['abr'] += $row['com_abr'];
        $MesesPGE['may'] += $row['com_may'];
        $MesesPGE['jun'] += $row['com_jun'];
        $MesesPGE['jul'] += $row['com_jul'];
        $MesesPGE['ago'] += $row['com_ago'];
        $MesesPGE['sep'] += $row['com_sep'];
        $MesesPGE['oct'] += $row['com_oct'];
        $MesesPGE['nov'] += $row['com_nov'];
        $MesesPGE['dic'] += $row['com_dic'];

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
        $MesesTE['eje'] += $row['pre_comprometido'];
        $MesesTE['ene'] += $row['com_ene'];
        $MesesTE['feb'] += $row['com_feb'];
        $MesesTE['mar'] += $row['com_mar'];
        $MesesTE['abr'] += $row['com_abr'];
        $MesesTE['may'] += $row['com_may'];
        $MesesTE['jun'] += $row['com_jun'];
        $MesesTE['jul'] += $row['com_jul'];
        $MesesTE['ago'] += $row['com_ago'];
        $MesesTE['sep'] += $row['com_sep'];
        $MesesTE['oct'] += $row['com_oct'];
        $MesesTE['nov'] += $row['com_nov'];
        $MesesTE['dic'] += $row['com_dic'];

        /* GENERALES DIFERENCIA */
        $MesesTDif['dif'] += ($row['pre_vigente'] - $row['pre_comprometido']);
        $MesesTDif['ene'] += ($row['vig_ene'] - $row['com_ene']);
        $MesesTDif['feb'] += ($row['vig_feb'] - $row['com_feb']);
        $MesesTDif['mar'] += ($row['vig_mar'] - $row['com_mar']);
        $MesesTDif['abr'] += ($row['vig_abr'] - $row['com_abr']);
        $MesesTDif['may'] += ($row['vig_may'] - $row['com_may']);
        $MesesTDif['jun'] += ($row['vig_jun'] - $row['com_jun']);
        $MesesTDif['jul'] += ($row['vig_jul'] - $row['com_jul']);
        $MesesTDif['ago'] += ($row['vig_ago'] - $row['com_ago']);
        $MesesTDif['sep'] += ($row['vig_sep'] - $row['com_sep']);
        $MesesTDif['oct'] += ($row['vig_oct'] - $row['com_oct']);
        $MesesTDif['nov'] += ($row['vig_nov'] - $row['com_nov']);
        $MesesTDif['dic'] += ($row['vig_dic'] - $row['com_dic']);
    } while ($row = $conexion->extraer());
    $pdf->CheckPageBreak(10);
    if ($pdf->CheckPageBreak(25)) {
        $pdf->cabecera(true);
        $pdf->totales('TOTAL ' . $idp_nombre, $MesesIDP, true);
        $pdf->totales('TOTAL COMPROMETIDO ' . $idp_nombre, $MesesIDPE, true, true);
        $pdf->totalesdif('TOTAL POR COMPROMETER ' . $idp_nombre, $MesesIDP, $MesesIDPE, true);
    }

    if ($totoa) {
        $pdf->setX(5);
        $pdf->totales('TOTAL OBRA ACTIVIDAD', $MesesOA, true);
        $pdf->totales('TOTAL COMPROMETIDO OBRA ACTIVIDAD ', $MesesOAE, true, true);
        $pdf->totalesdif('TOTAL POR COMPROMETER OBRA ACTIVIDAD ', $MesesOA, $MesesOAE, true);
    }

    if ($totpy) {
        $pdf->totales('TOTAL PROYECTO', $MesesPY, true);
        $pdf->totales('TOTAL COMPROMETIDO PROYECTO ', $MesesPYE, true, true);
        $pdf->totalesdif('TOTAL POR COMPROMETER PROYECTO ', $MesesPY, $MesesPYE, true);
    }

    if ($totspr) {
        $pdf->totales('TOTAL SUBPROGRAMA', $MesesSPR, true);
        $pdf->totales('TOTAL COMPROMETIDO SUBPROGRAMA ', $MesesSPRE, true, true);
        $pdf->totalesdif('TOTAL POR COMPROMETER SUBPROGRAMA ', $MesesSPR, $MesesSPRE, true);
    }

    if ($totprg) {
        $pdf->totales('TOTAL PROGRAMA', $MesesPR, true);
        $pdf->totales('TOTAL COMPROMETIDO PROGRAMA ', $MesesPRE, true, true);
        $pdf->totalesdif('TOTAL POR COMPROMETER PROGRAMA ', $MesesPR, $MesesPRE, true);
    }

    if ($totue) {
        $pdf->totales('TOTAL UNIDAD EJECUTORA', $MesesUE, true);
        $pdf->totales('TOTAL COMPROMETIDO UNIDAD EJECUTORA ', $MesesUEE, true, true);
        $pdf->totalesdif('TOTAL POR COMPROMETER UNIDAD EJECUTORA ', $MesesUE, $MesesUEE, true, true);
    }

    if ($totur) {
        $pdf->totales('TOTAL DE LA UR', $MesesT, true, false);
        $pdf->totales('TOTAL DE LA UR COMPROMETIDO', $MesesTE, true, true);
        $pdf->totalesdif('TOTAL DE LA UR POR COMPROMETER', $MesesT, $MesesTE, true, true);
    }
}
$pdf->Output();
