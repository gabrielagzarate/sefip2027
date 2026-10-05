<?php

session_start();
ini_set('memory_limit', '20M');

set_time_limit(0);
require_once dirname(__FILE__) . "/../../../../lib/constantes.php";
require_once dirname(__FILE__) . "/../../../../lib/helpers.php";
require_once '../../../lib/reportetabla.1.8.php';
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../ent/UnidadResponsable.php';

$size = isset($_POST['size']) ? $_POST['size'] : 6.5;
$size = $size <= 6.5 ? 6.5 : $size;
$size = $size >= 11 ? 11 : $size; // definimos el tama�o de la fuente para que sea dinamico

class reportetabla extends PDF
{
    public $gpo_id = '';
    public $ur_id = '';
    public function __construct()
    {
        parent::__construct();
    }

    public function Footer($lastPage = true)
    {
        $size = isset($_POST['size']) ? $_POST['size'] : 6.5;
        $size = $size <= 6.5 ? 6.5 : $size;
        $size = $size >= 11 ? 11 : $size; // definimos el tama�o de la fuente para que sea dinamico
        $row = $this->getDatos();
        $tmpb = $this->GetBorders();
        $this->SetY(-15);
        $firma = $cargo = "";
        switch (($row ? $row['dep_id'] : '')) {
            case 44:
                $firma = utf8Decode('LIC. NATIVIDAD GRACÍA CRUZ');
                $cargo = utf8Decode('JEFA DEL DEPARTAMENTO DE SEGUIMIENTO A GASTO DE OPERACIÓN "A"');
                break;
            case 45:
                $firma = utf8Decode('LIC. GABRIELA ENRÍQUEZ MARTÍNEZ');
                $cargo = utf8Decode('JEFA DEL DEPARTAMENTO DE SEGUIMIENTO A GASTO DE OPERACIÓN "B"');
                break;
            case 46:
                $firma = utf8Decode('L.C.P. ANDREA RAMÍREZ LÓPEZ');
                $cargo = utf8Decode('JEFA DEL DEPARTAMENTO DE SEGUIMIENTO A GASTO DE OPERACIÓN "C"');
                break;
        }
        //Firma de quien recibe
        $this->SetFont('Arial', 'B', $size);
        $this->Cell(190, 2, 'RECIBE:', 0, 0, 'C');
        //Nombre del que firma
        $this->Cell(140, 2, '', 0, 1, 'C');
        $this->ln(1);
        $this->SetFont('Arial', 'B', $size);
        $this->Cell(190, 2, $firma, 0, 0, 'C');
        //Cargo del que firma
        $this->Cell(190, 2, '', 0, 1, 'C');
        $this->SetFont('Arial', 'B', $size);
        $this->Cell(190, 3, $cargo, 0, 0, 'C');
        //Datos del documento
        $this->ln();
        $this->Cell(0, 10, utf8Decode('Página ' . $this->PageNo() . '/{nb}'), 0, 0, 'C');
        $this->Cell(0, 10, date("d/m/y") . ' - ' . date("H:i:s") . '        ', 1, 0, 'R');
    }

    public function Header() //Encabezado del documento

    {
        // definicion del tamaño de fuentes
        $size = isset($_POST['size']) ? $_POST['size'] : 6.5;
        $size = $size <= 6.5 ? 6.5 : $size;
        $size = $size >= 11 ? 11 : $size; // definimos el tamaño de la fuente para que sea dinamico

        $this->SetMargins(10, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);

        $row = $this->getDatos();

        $conexion2 = new AccesoDatos();
        $conexion2->conectar();
        $conexion2->setSql('
            SELECT
            eur_id
            FROM cat_UnidadResponsable
            WHERE gpo_id = ?
            AND ur_id = ? ;
        ');
        $params = [$_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur']];
        $conexion2->consultar($params);
        $conexion2->desconectar();

        $this->SetMargins(10, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(3);
        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->Image('../../../../img/' . LOGO_GRIS, $ix, $iy - 3, '60', '20', 'PNG', '');

        //Anio en vertical
        $this->SetFont('Arial', 'B', $size + 6.5);
        $this->RotatedText($ix + 62, $iy + 16, implode(" ", str_split(APP_EJERCICIO)), 90);
        
        // ----------  Titulos del documento
        $this->SetFont('Arial', 'B', $size + 1.5);
        $this->SetXY($ix + 26, $iy + 1);
        $ytmp = $this->GetY();
        $this->Cell(166, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'C');
        $this->SetXY($ix + 17, $ytmp + 3.5);
        $ytmp = $this->GetY();

        $this->SetFont('Arial', 'B', $size + 1.5);
        $this->SetXY($ix + 26, $iy + 4);
        $ytmp = $this->GetY();
        $this->Cell(165, 5, 'SECRETARIA DE FINANZAS DEL ESTADO DE OAXACA', 0, 1, 'C');
        $this->SetXY($ix + 17, $ytmp + 3.5);
        $ytmp = $this->GetY();

        $this->SetFont('Arial', 'B', $size + 1.5);
        $this->SetXY($ix + 26, $iy + 7);
        $ytmp = $this->GetY();
        $this->Cell(165, 5, 'ENTREGA DE CLCS ' . APP_EJERCICIO . ' ', 0, 1, 'C');
        $this->SetXY($ix + 17, $ytmp + 3.5);
        $ytmp = $this->GetY();
        
        $this->SetFont('Arial', 'B', $size + 1.5);
        $this->Cell(190, 5, 'TIPO DE CLC: ' . ($row ? utf8Decode($row['tcl_nombre']) : ''), 0, 1, 'C');
        $this->SetXY($ix + 17, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('Arial', 'B', $size + 1.5);
        $this->Cell(186, 5, ($row ? utf8Decode($row['dep_nombre']) : ''), 0, 1, 'C');
        $this->SetXY($ix + 17, $ytmp + 4);
        $this->SetFont('Arial', 'B', $size + 1.5);
        $ytmp = $this->GetY();
        $this->SetFont('Arial', 'B', $size - 0.5);
        $this->Cell(163, 5, 'FECHA DE LA LISTA: ' . ($row ? $row['FechaProceso'] : ''), 0, 1, 'R');

        $ytmp = $this->GetY();
        $this->setX(10);
        $this->setDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, 203, $ytmp);
        $this->ln(1);
        $this->setDrawColor(170, 170, 170);

        $this->SetFillColor(189, 199, 209);
        $this->setX(10);
        $this->SetWidths(array(10, 70, 50, 31.5));
        $this->SetAligns(array('L', 'L', 'L', 'R','R'));
        $this->SetFont('Arial', 'B', $size + 0.5);
        $this->setBorders("");
        $this->setAltoLinea(4);
        $this->SetRelleno("si");
        $this->Row(array('UR', 'DEPENDENCIA O ENTIDAD ', 'FOLIOS', 'HORA','FECHA RECHAZO'));
        $this->ln(1);
        $this->setBorders(0);
        $this->SetRelleno("no");
    }
}

$pdf = new reportetabla('P', 'mm', 'Letter');
$pdf->setOrientation('P');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 40);
$ecl_id = $in = $wf = '';
$params = [];
$tipo = isset($_POST['tcl_id']) ? $_POST['tcl_id'] : '';
$depto = isset($_POST['depto']) ? $_POST['depto'] : '';
$estado = isset($_POST['ecl_id']) ? $_POST['ecl_id'] : 3;
$horaI = isset($_POST['horaI']) ? $_POST['horaI'] : 0;
$horaF = isset($_POST['horaF']) ? $_POST['horaF'] : 0;

if (isset($_POST['depto']) and ($_POST['depto'] != '')) {
    $wf .= ($wf == '') ? 'WHERE dep_id = :depto ' : ' AND dep_id = :depto ';
    $params[':depto'] = $_POST['depto'];
}

if ($tipo != '') {
    switch ($tipo) {
        case "A":
            if ($depto != '') {
                switch ($depto) {
                    case 44:
                    case 45:
                        $wf .= ($wf == '') ?
                        ' WHERE (tcl_id = "A" OR (tcl_id="C" AND stc_id = 01 )) ' : ' AND (tcl_id = "A" OR  (tcl_id="C" AND stc_id = 01 )) ';
                        break;
                }
            }
            break;
        case "B":
            if ($depto != '') {
                switch ($depto) {
                    case 44:
                        $wf .= ($wf == '') ?
                        'WHERE (tcl_id in("B","D","E","I","J","K","L","M") OR (tcl_id = "C" AND stc_id = 02))  ' : ' AND (tcl_id in("B","D","E","I","J","K","L","M") OR (tcl_id = "C" AND stc_id = 02)) ';
                        break;
                    case 45:
                        $wf .= ($wf == '') ?
                        ' WHERE (tcl_id in("B","E","D") OR (tcl_id = "C" AND stc_id = 02)) ' : ' AND (tcl_id in("B","E","D") OR (tcl_id = "C" AND stc_id = 02)) ';
                        break;
                }
            }
            break;
        case "D":
            if ($depto != '') {
                switch ($depto) {
                    case 46:
                        $wf .= ($wf == '') ?
                        ' WHERE (tcl_id in("D","F","G","H") OR (tcl_id = "C" AND stc_id in (03,04))) '
                        : ' AND  (tcl_id in("D","F","G","H") OR (tcl_id = "C" AND stc_id in (03,04))) ';
                        break;
                }
            }
            break;
    }
}

$actual = date("Y-m-d");
$letra = '';

switch ($estado){
    case 1:
    $ecl_id = 'WHERE  ecl_id = 1';
    $letra = 'a.ecl_id';
    break;
    case 3:
    $ecl_id = 'WHERE  ecl_id = 3';
    $letra = 'a.ecl_id';
    break;
    case 4:
    $ecl_id = ' WHERE  ecl_id = 4';
    $letra = 'a.ecl_id >';
    break;
}

if ($_POST['Fechalista'] != "" && $_POST['Fechalista'] != 0) {
    $_POST['Fechalista'] = DateToQuotedMySQLDate($_POST['Fechalista']);
    if ($horaI != "" && $horaF != "") {
        $where = ' AND ' . $letra . '= :estado AND  DATE_FORMAT(FechaProceso,"%Y-%m-%d %H:%i:%S") BETWEEN  :fechaInicio  AND :fechaFin';
        
        $params[':estado'] = $estado; 
        $params[':fechaInicio'] = $_POST['Fechalista'] . ' ' . $horaI;
        $params[':fechaFin'] = $_POST['Fechalista'] . ' ' . $horaF;
    } else {
        $where = ' AND ' . $letra . '= :estado AND  DATE(FechaProceso) = :fechalista';
        
        $params[':estado'] = $estado;
        $params[':fechalista'] = $_POST['Fechalista'];
    }
    $where2 = ' AND DATE(usr_fecha) = :fechalista ';
    $params[':fechalista'] = $_POST['Fechalista'];
} else {
    $where = ' AND ' . $letra . ' = :estado AND  DATE(FechaProceso) = :factual ';
    $where2 = ' AND  DATE(usr_fecha) = :factual ';
    
    $params[':estado'] = $estado;
    $params[':factual'] = $actual;
}

$anyValue = "";

if ($_SERVER['SERVER_NAME'] == "sefip.finanzasoaxaca.gob.mx") {
    $anyValue = "";
} elseif ($_SERVER['SERVER_NAME'] == "uat-sefip.finanzasoaxaca.gob.mx") {
    $anyValue = "";
} else {
    $anyValue = "ANY_VALUE";
}

$sql = '
    SELECT
        gpo_id,
        ur_id,
        ur_clave,
        ur_nombre,
        IF(' . $anyValue . '(clc_tipocereCo) = 1 ,
            "CERE",
            IF(' . $anyValue . '(clc_tipocereCo) = 2,
                "CERECO",
                "SIN CERE(CO)")) AS cereco,
        ' . $anyValue . '(tcl_id) AS tcl_id,
        ' . $anyValue . '(tcl_nombre) AS tcl_nombre,
        ' . $anyValue . '(clc_concepto) AS clc_concepto,
        b.ecl_id,
        ' . $anyValue . '(ecl_nombre) AS ecl_nombre,
        ' . $anyValue . '(dep_nombre) AS dep_nombre,
        ' . $anyValue . '(FechaProceso) AS FechaProceso,
        hora,
        GROUP_CONCAT(DISTINCT(' . $anyValue . '(clc_id)) ORDER BY clc_id) AS recibidas,
        ' . $anyValue . '(TRUNCATE(clc_percepcion,2) - TRUNCATE(clc_retencion,2)) AS importe,
        dep_id
    FROM scp_Clc a
        INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
        INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
        INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING (cff_id, fte_id, sft_id, ori_id, sor_id)
        INNER JOIN ' . BD_SINPRES . '.cat_TipoRecurso t USING (ter_id)
        INNER JOIN cat_EstadoClc USING (ecl_id)
        INNER JOIN cat_TipoClc USING (tcl_id)
        INNER JOIN
        (
            SELECT
                gpo_id,
                ur_id,
                ecl_id,
                MAX(DATE_FORMAT(usr_fecha,"%Y/%m/%d %H:%i:%S")) AS FechaProceso,
                clc_id,
                MAX(DATE_FORMAT(usr_fecha,"%H:%i:%S")) AS hora
            FROM ' . BD_BITACORA . '.bit_Clc
            ' . $ecl_id . '
            ' . $where2 . '
            GROUP BY gpo_id,ur_id,clc_id,ecl_id
            ORDER BY gpo_id,ur_id,clc_id
        )b USING (gpo_id,ur_id,clc_id)
        INNER JOIN enc_FiltroClc USING(gpo_id, ur_id,tcl_id,stc_id,mno_id)
        INNER JOIN cat_Departamento USING(dep_id)
    ' . $wf . '
        AND clc_id != 0
        ' . $where . '
        AND clc_recursoFederal = 2
    GROUP by hora,gpo_id,ur_id
    ORDER by hora,gpo_id,ur_id,FechaProceso
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
$pintaEncabezado = true;
$relleno = false;
$pdf->SetFillColor(229, 239, 249);

do {
    $pdf->setDatos($row);
    $pdf->setAltoLinea(2.5);
    $pdf->CheckPageBreak(30);

    $pdf->CheckPageBreak(30);
    $pdf->ln(1);
    $pdf->SetX(10);
    $pdf->SetFont('Arial', 'B', $size - 0.5); ///tipo de letra para cada titulo

    if ($relleno) {
        $pdf->SetRelleno("si");
        $relleno = false;
    } else {
        $pdf->SetRelleno("no");
        $relleno = true;
    }

    $pdf->SetWidths(array(10, 70, 50, 31.5));
    $pdf->SetAligns(array('L', 'L', 'L', 'R','R'));
    if ($estado == 1 ){
        $pdf->Row(array(($row ? $row['ur_clave'] : ''), ($row ? utf8Decode($row['ur_nombre']) : ''), ($row ? $row['recibidas'] : ''), ($row ? $row['hora'] : ''),($row ? $row['FechaProceso'] : '')));
    } else {
        $pdf->Row(array(($row ? $row['ur_clave'] : ''), ($row ? utf8Decode($row['ur_nombre']) : ''), ($row ? $row['recibidas'] : ''), ($row ? $row['hora'] : ''),($row ? "-" : '')));
    }
} while ($row = $datos->extraer());
$pdf->SetProtection(array('print', 'annot-forms'), "", PDF_PASSWORD_OWNER_SCP);
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
        return "" . $trozos[2] . "-" . $trozos[1] . "-" . $trozos[0] . "";
    } else {
        return "NULL";
    }
}
