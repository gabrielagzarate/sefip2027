<?php
session_start();
ini_set('memory_limit', '20M');

set_time_limit(0);

require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../lib/helpers.php';

function cabecera()
{
    echo ('<thead style="background-color:#A4A4A4; border-bottom:1px solid #999; color:#FFF;">
            <td style="font-size:16px; color:#FFF;background-color:#A4A4A4; ">' . utf8Decode('NP') . '</td>
            <td style="font-size:16px; color:#FFF;background-color:#A4A4A4; ">Unidad Responsable</td>
            <td style="font-size:16px; color:#FFF;background-color:#A4A4A4; text-align:center; ">CLC</td>
            <td style="font-size:16px; color:#FFF;background-color:#A4A4A4; text-align:center; ">CVE_FIN</td>
            <td style="font-size:16px; color:#FFF;background-color:#A4A4A4; text-align:center; ">PROGRAMA</td>
            <td style="font-size:16px; color:#FFF;background-color:#A4A4A4; text-align:center; ">CONCEPTO</td>
            <td style="font-size:16px; color:#FFF;background-color:#A4A4A4;text-align:right; width:3px;  "></td>
            <td style="font-size:16px; color:#FFF;background-color:#A4A4A4;text-align:right; ">Afec. Presupuestal</td>
            <td style="font-size:16px; color:#FFF;background-color:#A4A4A4;text-align:right; ">Percepciones</td>
            <td style="font-size:16px; color:#FFF;background-color:#A4A4A4;text-align:right; ">Retenciones</td>
            <td style="font-size:16px; color:#FFF;background-color:#A4A4A4;text-align:right; ">Importe</td>
            <td style="font-size:16px; color:#FFF;background-color:#A4A4A4;text-align:right; ">Fecha</td>
        </thead>');
}

function totales($mensaje, $total)
{
    echo ('<tr">
        <td>&nbsp</td>
        <td colspan="4" style="font-size:16px; font-weight:bold;">' . utf8Decode($mensaje) . '</td>
        <td style="font-size:16px; font-weight:bold;">$' . number_format($total, 2) . '</td>

    </tr>');
}

function titulo($ue_id, $ue_nombre)
{
    echo ('<tr>
        <td style="background-color:#58ACFA; font-size:14px;" colspan="12">' . utf8Decode($ue_id . ' - ' . $ue_nombre) . '</td>
        </tr>
    ');
}

$ruta = utf8Decode('ReporteListadoCLC.xls');
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=" . $ruta);
header("Pragma: no-cache");
header("Expires: 0");
$hoy = date("Y-m-d H:i:s");

$recurso = isset($_POST['recurso']) && $_POST['recurso'] != "" ? $_POST['recurso'] : '';
$estado = isset($_POST['ecl_id']) && $_POST['ecl_id'] != "" ? $_POST['ecl_id'] : '';
$fechaI = isset($_POST['Fechainicio']) && $_POST['Fechainicio'] != "" ? DateToQuotedMySQLDate($_POST['Fechainicio']) : date("Y-m-d");
$fechaF = isset($_POST['Fechafinal']) && $_POST['Fechafinal'] != "" ? DateToQuotedMySQLDate($_POST['Fechafinal']) : date("Y-m-d");
$depto = isset($_POST['depto']) && $_POST['depto'] != "" ? $_POST['depto'] : '';
$tcl_id = isset($_POST['tcl_id']) && $_POST['tcl_id'] != "" ? $_POST['tcl_id'] : '';
$stClc = isset($_POST['stc_id']) && $_POST['stc_id'] != "" ? $_POST['stc_id'] : '';
$gpo_id = isset($_POST['gpo_id']) && $_POST['gpo_id'] != "" ? $_POST['gpo_id'] : '';
$ur_id = isset($_POST['ur_id']) && $_POST['ur_id'] != "" ? $_POST['ur_id'] : '';
$clc_id = isset($_POST['bxClc']) && $_POST['bxClc'] != "" ? $_POST['bxClc'] : '';
$gpoUr = isset($_POST['bxUr']) && $_POST['bxUr'] != "" ? $_POST['bxUr'] : '';

$in = $sub = $bitacora = '';
$fila = '';
$where = '';
$params = [];
$select = $wf = "";
if (count($tcl_id) > 0 && $tcl_id != '') {
    foreach ($tcl_id as $key => $val) {
        $in .= '"' . $val . '",';
    }
    $in = trim($in, ',');

    if ($in != '') {
        $wf .= ($wf == '') ? 'WHERE tcl_id in (' . $in . ') ' : ' AND tcl_id in (' . $in . ') ';
    }
}
if (isset($_POST['stc_id'])) {
    $size = 0;
    foreach ($_POST['stc_id'] as $key => $value) {
        $size++;
    }
    if (count($stClc) > 0 && $stClc != '') {
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
}

if ($clc_id != '') {
    $wf .= ($wf == '') ? 'WHERE clc_id = :clc_id ' : ' AND clc_id = :clc_id ';
    $params[':clc_id'] = $clc_id;
}

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

if ($gpoUr != '') {
    $wf .= ($wf == '') ? 'WHERE concat(gpo_id,ur_id) = :gpour ' : ' AND concat(gpo_id,ur_id) = :gpour ';
    $params[':gpour'] = $gpoUr;
}

if ($depto != '') {
    $wf .= ($wf == '') ? 'WHERE dep_id = :depto ' : ' AND dep_id = :depto ';
    $params[':depto'] = $depto;
}

if ($recurso == '') {
    $wf .= ($wf == '') ? 'WHERE ter_id IN ("A","B") ' : ' AND ter_id IN ("A","B") ';
} else {
    $wf .= ($wf == '') ? 'WHERE ter_id = :recurso ' : ' AND ter_id = :recurso ';
    $params[':recurso'] = $recurso;
}

if ($estado == 5) {
    $est = ' a.ecl_id=5 ';
    $texEstado = 'REVISADAS';
} elseif ($estado == 6) {
    $est = ' a.ecl_id=6 ';
    $texEstado = 'APLICADAS';
} else {
    $est = ' a.ecl_id = 1 ';
    $texEstado = 'RECHAZADAS';
    $bitacora = ' AND bit_observacion = "CLC RECHAZADA"';
}

$recursos = '';
if (isset($_POST['recurso']) and ($_POST['recurso'] != '')) {
    if ($_POST['recurso'] == "A") {
        $recursos = 'RECURSO ESTATAL';
    } else {
        $recursos = 'RECURSO FEDERAL';
    }
} else {
    $recursos = '';
}

if ($estado != '') {
    $wf .= ($wf == '') ? 'WHERE ' . $est . ' ' : ' AND ' . $est . '   ';
}

$select = 'DATE_FORMAT(FechaProceso,"%d/%m/%Y") FechaProceso,aplicadas,';
$join = '
    INNER JOIN (SELECT
        gpo_id,ur_id,
        ecl_id,usr_fecha as FechaProceso,
        clc_id as aplicadas,
        clc_id
    FROM ' . BD_BITACORA . '.bit_Clc
    WHERE
        ecl_id=' . $estado . $bitacora . '
    group by gpo_id,ur_id,clc_id,usr_fecha
    order by gpo_id,ur_id,usr_fecha
    )b using(gpo_id,ur_id,clc_id)
    ';

$periodo = "";
$ur_b = "";
if ($fechaI != "" && $fechaF != "") {
    if ($fechaI == $fechaF) {
        $periodo = ' DATE(FechaProceso) = :fechainicio ';
    } else {
        $periodo = ' ( DATE(FechaProceso) BETWEEN :fechainicio AND :fechafinal) ';
        $params[':fechafinal'] = $fechaF;
    }
    $params[':fechainicio'] = $fechaI;
    $wf .= ($wf == '') ? 'WHERE ' . $periodo . ' ' : '  AND ' . $periodo . ' ';

}

$group = '
    GROUP by gpo_id,ur_id,clc_id, dep_nombre,cfi_clave,cfi_nombre,FechaProceso,ter_id,tcl_nombre
    ORDER by  ter_id ,gpo_id , ur_id , clc_id , FechaProceso
';

$sql = '
    SELECT DISTINCT
        gpo_id,ur_id,
        ur_clave,ur_nombre,
        a.clc_id,
        if(clc_tipocereCo = 1
            ,"CERE"
            ,if(clc_tipocereCo = 2
                , "CERECO"
                ,"SIN CERE(CO)")
            ) AS cereco,
        tcl_id,tcl_nombre,
        b.ecl_id,ecl_nombre,
        ' . $select . '
        TRUNCATE (clc_percepcion,2) - TRUNCATE (clc_retencion,2) AS importe,
        clc_percepcion AS percepcion,
        clc_retencion AS retencion,
        if(SUM(ncp_percepcionNeto) is null
            , 0
            , SUM(ncp_percepcionNeto)) AS afe_presupuestal,
        ter_id,dep_nombre,
        cfi_clave,cfi_nombre,
        clc_concepto,
        DATE_FORMAT(clc_fechaEnvio,"%d/%m/%Y") AS clc_fechaEnvio,
        mpc_nombre,clc_percepcion,
        clc_retencion,bur_id
    FROM scp_Clc a
        INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
        INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
        INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING (cff_id,fte_id, sft_id, ori_id, sor_id)
        INNER JOIN ' . BD_SINPRES . '.cat_TipoRecurso t USING (ter_id)
        INNER JOIN cat_EstadoClc USING (ecl_id)
        INNER JOIN cat_ModoPagoClc USING (mpc_id)
        INNER JOIN cat_TipoClc USING (tcl_id)
        INNER JOIN scp_ClcAuxiliar USING (gpo_id,ur_id,clc_id,ecl_id)
        LEFT JOIN scp_Presupuestal USING (gpo_id, ur_id, clc_id,ecf_id)
        INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
        ' . $join . '
        INNER JOIN enc_FiltroClc USING (gpo_id, ur_id, tcl_id,stc_id)
        INNER JOIN cat_Departamento USING (dep_id)
    ' . $wf . '
        ' . $where . '
    ' . $group . '
        ;';

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);

$conexion->consultar($params);
$conexion->desconectar();
$row = $conexion->extraer();

echo '<table align="center" width="100%">';

/* ANCHOS DE LA TABLA */
echo '<tr>';
//No,Unidad Responsable, Clc, CVE_FIN
echo '<td style="width:50px;">&nbsp;</td>
            <td style="width:250px;">&nbsp;</td>
            <td style="width:50px;">&nbsp;</td>
            <td style="width:80px;">&nbsp;</td>';
//programa, concepto, vacio,importe
echo '<td style="width:200px;">&nbsp;</td>
            <td style="width:500px;">&nbsp;</td>
            <td style="width:3px;">&nbsp;</td>
            <td style="width:100px;">&nbsp;</td>';
echo '</tr>';
/* ANCHOS DE LA TABLA */
$leyen = '';
switch ($_SESSION[_S]['dep_id']) {
    case 44:
        $leyen = ' DEPARTAMENTO DE SEGUIMIENTO A GASTO DE OPERACIÓN "A" ';
        break;
    case 45:
        $leyen = ' DEPARTAMENTO DE SEGUIMIENTO A GASTO DE OPERACIÓN "B" ';
        break;
    case 46:
        $leyen = ' DEPARTAMENTO DE SEGUIMIENTO A GASTO DE OPERACIÓN "C" ';
        break;
}

echo '<tr>';
echo utf8Decode('<td colspan=11 align="CENTER" ><b>SECRETARÍA DE FINANZAS DEL ESTADO DE OAXACA SECRETARÍA DE FINANZAS DEL ESTADO DE OAXACA</td>');
echo '</tr>';
echo '<tr>';
echo utf8Decode('<td colspan=11 align="CENTER" ><b>DIRECCIÓN DE PRESUPUESTO</td>');
echo '</tr>';
echo '<tr>';
echo utf8Decode('<td colspan=11 align="CENTER" ><b>COORDINACIÓN DE EJERCICIO PRESUPUESTAL</td>');
echo '</tr>';
echo '<tr>';
echo utf8Decode('<td colspan=11 align="CENTER" ><b>' . $leyen . '</td>');
echo '</tr>';
echo '<tr>';
echo '<td colspan=11 align="CENTER" ><b>CUENTAS POR LIQUIDAR CERTIFICADAS ' . $texEstado . ' Y ENTREGADAS AL DEPARTAMENTO DE PROCESAMIENTO DE CUENTAS POR LIQUIDAR CERTIFICADAS</td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan=11 align="CENTER" ><b>' . $recursos . '</td>';
echo '</tr>';
echo '<tr>';
echo utf8Decode('<td colspan="5" align="left" ></td>
                <td colspan="1" align="right" ></td>');

echo '</tr>';
echo '<tr>';

if ($fechaI == $fechaF) {
    echo utf8Decode('
        <td style="font-size: 12px;" colspan="8" align="right" >
        <b>FECHA DE LA LISTA: ' . $fechaF . '</b></td>
    ');
} else {
    echo utf8Decode('
        <td style="font-size: 12px;" colspan="8" align="right" >
        <b>FECHA DE LA LISTA: DEL ' . $fechaI . ' AL ' . $fechaF . '</b></td>
    ');
}
echo '</tr>';
echo '<tr>';
echo '<td colspan=6 align="CENTER" ></td>';
echo '</tr>';
/* variables  de montos*/
$celda = 0;
$suma = 0;
$sumas = 0;
$contador = 0;
if ($row) {
    /* variables  de cambios */
    $ter = $row['ter_id'];

    /*  fin variables  */
    $celda = 0;

    if ($row['ter_id'] == 'A') {
        titulo($row['ter_id'], "RECURSO ESTATAL");
    } else {
        titulo($row['ter_id'], "RECURSO FEDERAL");
    }

    cabecera();
    do {
        if ($ter != $row['ter_id']) {
            echo ' <tr><td colspan="8" align="left" style="font-size: 16px;"></td></tr>';
            $ter = $row['ter_id'];
            titulo($row['ter_id'], "RECURSO FEDERAL");
            cabecera();
        }

        /*  Registro     */
        $contador++;
        $color = "#FFF";
        if ($celda == 0) {
            $color = "#FFF";
            $celda = 1;
        } else {
            $color = "#EAEAEA";
            $celda = 0;
        }
        echo ('<tr>' .
            '<td style="background-color:' . $color . ';" align="CENTER" >' . $contador . '</td>' .
            '<td style="background-color:' . $color . ';">' . utf8Decode($row['ur_clave'] . " - " . $row['ur_nombre']) . '</td>' .
            '<td style="background-color:' . $color . ';" align="CENTER">' . $row['clc_id'] . '</td>' .
            '<td style="background-color:' . $color . ';">' . $row['cfi_clave'] . '</td>' .
            '<td style="background-color:' . $color . ';">' . utf8Decode($row['cfi_nombre']) . '</td>' .
            '<td style="background-color:' . $color . ';">' . utf8Decode($row['clc_concepto']) . '</td>' .
            '<td style="background-color:' . $color . '; width:3px;"></td>' .
            '<td style="background-color:' . $color . ';">$' . number_format($row['afe_presupuestal'], 2) . '</td>' .
            '<td style="background-color:' . $color . ';">$' . number_format($row['percepcion'], 2) . '</td>' .
            '<td style="background-color:' . $color . ';">$' . number_format($row['retencion'], 2) . '</td>' .
            '<td style="background-color:' . $color . ';">$' . number_format($row['importe'], 2) . '</td>' .
            '<td style="background-color:' . $color . ';">' . $row['FechaProceso'] . '</td>' .
            '</tr>
        ');
    } while ($row = $conexion->extraer());
}
echo ' <tr>';
echo '<td colspan="6" align="left" style="font-size: 10px;"><b>Fecha ' . $hoy . ' </b></td>';
echo '</tr>';
echo '</table>';

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
