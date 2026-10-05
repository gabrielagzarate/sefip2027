<?php
session_start();
ini_set('memory_limit', '20M');

set_time_limit(0);
require_once '../../../lib/reportetabla.1.8.php';
require_once '../../../../lib/helpers.php';
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../lib/TruncateCadena.php';

class reportetabla extends PDF
{
    public $gpo_id;
    public $ur_id;
    public $ur;
    public $ancho;
    public $imagen;
    public $cuentas;

    public function __construct()
    {
        parent::__construct();
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();
    }

    public function Footer($lastPage = true)
    {
        $this->SetY(-15);
        $this->ln();
        $this->Cell(0, 10, utf8Decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
        $this->Cell(0, 10, date("d/m/y") . ' - ' . date("H:i:s") . '        ', 1, 0, 'R');
    }

    public function Header() //Encabezado del documento
    {
        switch ($this->getOrientation()) {
            case 'P':
                $this->ancho = 123;
                $this->imagen = 168;
                break;
            case 'L':
                $this->ancho = 190;
                $this->imagen = 235;
                break;
        }

        $row = $this->getDatos();

        $this->SetMargins(10, 10, 10, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();

        $this->Image("../../../../img/" . LOGO_COLOR, $ix + 96, $iy + 1, '95', '20', 'PNG', '');

        $this->SetFont('UniviaPro-Bold', 'B', 16);
        $this->SetTextColor(200, 200, 200);
        $this->RotatedText($ix + 4, $iy + 22, $this->GetYear('y'), 90);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->SetXY($ix + 4, $iy + 4);
        $ytmp = $this->GetY();
        $this->Cell(195, 5, utf8Decode('SECRETARÍA DE FINANZAS DEL ESTADO DE OAXACA'), 0, 1, 'L');
        $this->SetXY($ix + 4, $ytmp + 4);
        $ytmp = $this->GetY();

        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(180, 5, utf8Decode('DIRECCIÓN DE PRESUPUESTO'), 0, 1, 'L');
        $this->SetXY($ix + 4, $ytmp + 4);
        $ytmp = $this->GetY();

        $this->SetFont('UniviaPro-Regular', '', 8);
        $this->Cell(180, 5, utf8Decode('COORDINACIÓN DE EJERCICIO PRESUPUESTAL'), 0, 1, 'L');
        $this->SetXY($ix + 4, $ytmp + 4);
        $ytmp = $this->GetY();

        $leyen = '';
        switch ($_SESSION[_S]['dep_id']) {
            case 44:
                $leyen = 'DEPARTAMENTO DE SEGUIMIENTO A GASTO DE OPERACIÓN "A" ';
                break;
            case 45:
                $leyen = 'DEPARTAMENTO DE SEGUIMIENTO A GASTO DE OPERACIÓN "B" ';
                break;
            case 46:
                $leyen = 'DEPARTAMENTO DE SEGUIMIENTO A GASTO DE OPERACIÓN "C" ';
                break;
        }

        $this->Cell(180, 5, utf8Decode($leyen), 0, 1, 'L');
        $this->SetXY($ix + 4, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);

        $estado = isset($_POST['ecl_id']) ? $_POST['ecl_id'] : '';
        if ($estado == 5) {
            $estados = 'REVISADAS';
        } else if ($estado == 6) {
            $estados = 'APLICADAS';
        } else {
            $estados = 'RECHAZADAS';
        }

        $this->longitud = 55;
        $this->cuentas = TruncateCadena(utf8Decode(trim("CUENTAS POR LIQUIDAR CERTIFICADAS " . $estados . " Y ENTREGADAS AL DEPARTAMENTO DE CUENTAS POR PAGAR")), $this->longitud);

        $this->Cell(5, 5, substr($this->cuentas, 0, $this->longitud), 0, 0, 'L');
        $this->Cell(180, 5, '', 0, 1, 'R');

        if (strlen($this->cuentas) > $this->longitud) {
            $this->SetXY($ix + 4, $ytmp + 4);
            $this->Cell(
                5,
                5,
                substr($this->cuentas, $this->longitud, $this->longitud),
                0,
                0,
                'L'
            );
            $this->Cell(180, 5, '', 0, 1, 'R');
        }

        if (strlen($this->cuentas) > $this->longitud * 2) {
            $this->SetXY($ix + 4, $ytmp + 4);
            $this->Cell(
                5,
                5,
                substr($this->cuentas, $this->longitud * 2, $this->longitud),
                0,
                0,
                'L'
            );
            $this->Cell(180, 5, '', 0, 1, 'R');
        }

        $fechainicio = "";
        $fechafinal = "";
        $fechainicio = isset($_POST['Fechainicio']) && $_POST['Fechainicio'] != ""  ? DateToQuotedMySQLDate($_POST['Fechainicio'])  : date("Y-m-d");
        $fechafinal = isset($_POST['Fechafinal']) && $_POST['Fechafinal'] != "" ? DateToQuotedMySQLDate($_POST['Fechafinal']) : date("Y-m-d");

        $conexion2 = new AccesoDatos();
        $conexion2->conectar();
        $conexion2->setSql(' SELECT eur_id FROM cat_UnidadResponsable where gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' and ur_id=' . $_SESSION[_S]['clasAdm-ur'] . ' ');
        $conexion2->consultar();
        $conexion2->desconectar();

        $recursos = '';
        if (isset($_POST['recurso']) and ($_POST['recurso'] != '')) {
            if ($_POST['recurso'] == "A") {
                $recursos = 'RECURSO ESTATAL';
            }
            if ($_POST['recurso'] == "B") {
                $recursos = 'RECURSO FEDERAL';
            }
        } else {
            $recursos = '';
        }

        $this->Cell(150, 5, $recursos, 0, 1, 'C');
        $this->SetXY($ix + 17, $ytmp + 3.5);
        $ytmp = $this->GetY();

        $this->SetFont('UniviaPro-Bold', 'B', 6);
        $bandera = '';
        if ($bandera == true) {
            $this->Cell(175, 5, 'FECHA DE LA LISTA: ' . $row['FechaProceso'], 0, 1, 'R');
        } else {
            $this->Cell(175, 5, 'FECHA DE LA LISTA: DEL  ' . $fechainicio . ' AL ' . $fechafinal, 0, 1, 'R');
        }

        $ytmp = $this->GetY();
        $this->setX(10);
        $this->setDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, 203, $ytmp);
        $this->ln(1);
        $this->setDrawColor(170, 170, 170);

        $this->SetFillColor(189, 199, 209);
        $this->setX(10);
        $this->SetWidths(array(5, 25, 10, 30, 28, 30, 20, 15, 15, 15, 15));
        $this->SetAligns(array('L', 'L', 'L', 'L', 'L', 'L', 'R', 'R', 'R', 'R'));
        $this->SetFont('Arial', 'B', 7);
        $this->setBorders("");
        $this->setAltoLinea(4);
        $this->SetRelleno("si");

        if ($_POST['recurso'] != '') {
            if ($_POST['recurso'] == "A") {
                $Rec = '';
            }
            if ($_POST['recurso'] == "B") {
                $Rec = '';
            }
        } else {
            if ($row['ter_id'] == "A") {
                $Rec = 'RECURSO ESTATAL';
            }
            if ($row['ter_id'] == "B") {
                $Rec = 'RECURSO FEDERAL';
            }
        }

        $this->Cell(185, 5, $Rec, 0, 1, 'C');
        $this->SetWidths(array(8, 8, 10, 15, 23, 25, 30, 25, 20, 14));
        $this->SetAligns(array('L', 'L', 'L', 'L', 'L', 'L', 'L', 'L', 'L', 'R'));
        $this->Row(array('NP', 'UR', 'CLC', 'CVEFIN', 'PROGRAMA', 'CONCEPTO', 'AFE.PRESUPUESTAL', 'PERCEPCIONES', 'RETENCIONES', 'IMPORTE', 'FECHA'));
        $this->ln(1);
        $this->setBorders(0);
        $this->SetRelleno("no");
    }
}

$pdf = new reportetabla('P', 'mm', 'Letter');
$pdf->SetProtection(array('print', 'annot-forms'), "", PDF_PASSWORD_OWNER_SCP);
$pdf->setOrientation('P');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 40);
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$in = $sub = $wf = '';

$tipo = isset($_POST['tcl_id']) ? $_POST['tcl_id'] : 0;

$fechainicio = isset($_POST['Fechainicio']) && $_POST['Fechainicio'] != ""  ? DateToQuotedMySQLDate($_POST['Fechainicio'])  : date("Y-m-d");
$fechafinal = isset($_POST['Fechafinal']) && $_POST['Fechafinal'] != "" ? DateToQuotedMySQLDate($_POST['Fechafinal']) : date("Y-m-d");

$estado = isset($_POST['ecl_id']) ? $_POST['ecl_id'] : '';
$clc_id = isset($_POST['bxClc']) && $_POST['bxClc'] != "" ? $_POST['bxClc'] : '';
$gpoUr = isset($_POST['bxUr']) && $_POST['bxUr'] != "" ? $_POST['bxUr'] : '';
$stClc = isset($_POST['stc_id']) && $_POST['stc_id'] != "" ? $_POST['stc_id'] : '';
$bitacora = '';
$bitacoraFecha = '';
$periodo = '';
$params = [];

if (count($tipo) > 0 && $tipo != '') {
    foreach ($tipo as $key => $val) {
        $in .= '"' . $val . '",';
    }
    $in = trim($in, ',');

    if ($in != '') {
        $wf .= ($wf == '') ? 'WHERE tcl_id in (' . $in . ') ' : ' and tcl_id in (' . $in . ') ';
    }
}

$size = 0;
if (isset($_POST['stc_id']) && is_array($_POST['stc_id'])) {
    foreach ($_POST['stc_id'] as $key => $value) {
        $size++;
    }
}
if ($stClc != '' && count($stClc) > 0) {
    if ($size > 0) {
        foreach ($stClc as $key => $valor) {
            $sub .= '"' . $valor . '",';
        }
        $sub = trim($sub, ',');

        if ($sub != '') {
            $wf .= ($wf == '') ? 'WHERE stc_id in (' . $sub . ') ' : ' AND stc_id in (' . $sub . ') ';
        }
    }
}

if (isset($_POST['depto']) and ($_POST['depto'] != '')) {
    $wf .= ($wf == '') ? 'WHERE dep_id = :depto ' : ' AND dep_id = :depto ';
    $params[':depto'] = $_POST['depto'];
}

if (isset($_POST['recurso']) and ($_POST['recurso'] == '')) {
    $wf .= ($wf == '') ? 'WHERE ter_id IN ("A","B") ' : ' AND ter_id IN ("A","B") ';
} else {
    $wf .= ($wf == '') ? 'WHERE ter_id = :recurso ' : ' AND ter_id = :recurso ';
    $params[':recurso'] = $_POST['recurso'];
}

$where = '';
$fila = '';
if (isset($_POST['datos']) && count($_POST['datos']) != 0) {
    foreach ($_POST['datos'] as $key => $val) {
        $tmp = explode(',', $val);
        if ($where == "") {
            $where .= 'AND (clc_id=' . $tmp[0] . ' AND gpo_id=' . substr($tmp[1], 0, 1) . ' AND ur_id=' . substr($tmp[1], 1, 2) . ')';
        } else {
            $where .= ' OR ( (clc_id=' . $tmp[0] . ' AND gpo_id=' . substr($tmp[1], 0, 1) . ' AND ur_id=' . substr($tmp[1], 1, 2) . '))';
        }
    }
    $fila = trim($fila, ',');
}

if ($clc_id != '') {
    $wf .= ($wf == '') ? 'WHERE clc_id = :clc_id ' : ' AND clc_id = :clc_id ';
    $params[':clc_id'] = $clc_id;
}

if ($gpoUr != '') {
    $wf .= ($wf == '') ? 'WHERE concat(gpo_id,ur_id) = :gpour ' : ' AND concat(gpo_id,ur_id) = :gpour ';
    $params[':gpour'] = $gpoUr;
}
$est = '';
if ($estado == 5) {
    $estado = ' a.ecl_id = 5 ';
    $est = ' ecl_id = 5 ';
} elseif ($estado == 6) {
    $estado = ' a.ecl_id = 6 ';
    $est = ' ecl_id = 6 ';
} else {
    $estado = ' a.ecl_id = 1 ';
    $est = ' ecl_id = 1 ';
    $bitacora = ' AND bit_observacion = "CLC RECHAZADA"';
}

if ($fechainicio != "" && $fechafinal != "") {
    if ($fechainicio == $fechafinal) {
        $periodo = ' DATE(FechaProceso) = :fechainicio';
    } else {
        $periodo = ' (DATE(FechaProceso) BETWEEN :fechainicio AND :fechafinal) ';
        $params[':fechafinal'] = $fechafinal;
    }
    $params[':fechainicio'] = $fechainicio;

    $wf .= ($wf == '') ? 'WHERE ' . $periodo . ' and ' . $estado . ' ' : '  and ' . $periodo . ' and ' . $estado . ' ';
}
$sql = '
    SELECT DISTINCT gpo_id,ur_id, ur_clave,ur_nombre,
        if(clc_tipocereCo=1
            ,"CERE"
            ,if(clc_tipocereCo=2
                    ,"CERECO","SIN CERE(CO)")
            ) AS cereco,
        tcl_id,tcl_nombre,clc_concepto,
        b.ecl_id,ecl_nombre,dep_nombre,
        cfi_clave,cfi_nombre,ter_id,
        a.clc_id,
        DATE_FORMAT(FechaProceso,"%d/%m/%Y") AS FechaProceso,
        aplicadas,
        TRUNCATE (clc_percepcion,2) - TRUNCATE (clc_retencion,2) AS importe,
        clc_percepcion AS percepcion,
        clc_retencion AS retencion,
        if(SUM(ncp_percepcionNeto) is null
            , 0
            , SUM(ncp_percepcionNeto)) AS afe_presupuestal
    FROM scp_Clc a
    -- bloque de join agregado para imprimir datos
        INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
        INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
        INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING ( cff_id,fte_id, sft_id, ori_id, sor_id )
        INNER JOIN ' . BD_SINPRES . '.cat_TipoRecurso t USING (ter_id)
        INNER JOIN cat_EstadoClc USING (ecl_id)
        INNER JOIN cat_ModoPagoClc USING (mpc_id)
        INNER JOIN cat_TipoClc USING (tcl_id)
    -- fin bloque agregado
    -- antes estaba con el bloque de join del segmento siguiente
        INNER JOIN scp_ClcAuxiliar USING (gpo_id,ur_id,clc_id,ecl_id)
        LEFT JOIN scp_Presupuestal USING (gpo_id, ur_id, clc_id,ecf_id)
        LEFT JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING (cff_id, fte_id, sft_id, ori_id, sor_id,cfi_id,cfi_anio)
        INNER JOIN (
            SELECT
                gpo_id,ur_id,
                ecl_id,usr_fecha AS FechaProceso,
                clc_id AS aplicadas,clc_id
            FROM ' . BD_BITACORA . '.bit_Clc
            WHERE
                ' . $est . $bitacora . '
            GROUP BY gpo_id,ur_id,clc_id,usr_fecha
            ORDER BY gpo_id,ur_id,usr_fecha DESC
        )b USING (gpo_id,ur_id,clc_id)
        INNER JOIN enc_FiltroClc USING (gpo_id, ur_id, tcl_id,stc_id)
        INNER JOIN cat_Departamento USING (dep_id)
        ' . $wf . '
        ' . $where . '
        AND clc_id != 0
    GROUP by gpo_id,ur_id,clc_id,dep_nombre,cfi_clave,cfi_nombre,FechaProceso,ter_id,tcl_nombre
    ORDER by ter_id,gpo_id,ur_id,clc_id,FechaProceso;
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
$celda = 1;
$contador = 0;

$pintaEncabezado = true;

$relleno = false;
$pdf->SetFillColor(229, 239, 249);
if ($row) {
    $ter = $row['ter_id'];
    $id = 0;
    do {
        $id = $id + 1;
        $id = str_pad($id, 2, '0', STR_PAD_LEFT);
        if ($ter != $row['ter_id']) {
            $ter = $row['ter_id'];
            $pdf->ln(1);
            $pdf->setDrawColor(170, 170, 170);

            $pdf->SetFillColor(189, 199, 209);

            $pdf->SetFont('Arial', 'B', 7);
            $pdf->setBorders("");
            $pdf->setAltoLinea(4);
            $pdf->SetRelleno("si");
            $pdf->ln(3);
            $pdf->Cell(185, 5, "RECURSO FEDERAL", 0, 1, 'C');
            $pdf->Row(array('NP', 'UR', 'CLC', 'CVEFIN', 'PROGRAMA', 'CONCEPTO', 'MONTO'));
            $pdf->ln(1);
            $pdf->setBorders(0);
            $pdf->SetRelleno("no");
            $pdf->SetFillColor(229, 239, 249);
        }

        $contador++;

        $pdf->setDatos($row);
        $pdf->setAltoLinea(2.5);
        $pdf->CheckPageBreak(30);

        $pdf->CheckPageBreak(30);
        $pdf->ln(1);
        $pdf->SetX(10);
        $pdf->SetFont('Arial', 'B', 6); ///tipo de letra para cada titulo
        if ($relleno) {
            $pdf->SetRelleno("si");
            $relleno = false;
        } else {
            $pdf->SetRelleno("no");
            $relleno = true;
        }

        $pdf->SetWidths(array(5, 25, 10, 25, 25, 30, 15, 15, 15, 15, 15));
        $pdf->SetAligns(array('L', 'L', 'L', 'L', 'L', 'L', 'R', 'R', 'R', 'R'));
        $pdf->Row(array($id, $row['ur_clave'] . ' : ' . utf8Decode($row['ur_nombre']), $row['aplicadas'], $row['cfi_clave'], utf8Decode($row['cfi_nombre']), utf8Decode($row['clc_concepto']), number_format($row['afe_presupuestal'], 2), number_format($row['percepcion'], 2), number_format($row['retencion'], 2), number_format($row['importe'], 2), $row['FechaProceso']));
    } while ($row = $datos->extraer());
}
$pdf->Output();

function MySQLDateToDate($MySQLFecha)
{
    if (($MySQLFecha == "") or ($MySQLFecha == "0000-00-00")) {
        return "";
    } else {
        return date("d/m/Y", strtotime($MySQLFecha));
    }
}

function DateToQuotedMySQLDate($Fecha)
{
    if ($Fecha != "") {
        $trozos = explode("/", $Fecha, 3);
        return $trozos[2] . "-" . $trozos[1] . "-" . $trozos[0];
    } else {
        return "NULL";
    }
}
