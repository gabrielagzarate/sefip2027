<?php
session_start();
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../lib/helpers.php';
$ruta = utf8Decode("ClcsRecibidasDepto.xls");
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=" . $ruta);
header("Pragma: no-cache");
header("Expires: 0");

$tipo = isset($_POST['tcl_id']) ? $_POST['tcl_id'] : '';
$depto = isset($_POST['depto']) ? $_POST['depto'] : '';

$in = $wf = '';

if (isset($_POST['depto']) and ($_POST['depto'] != '')) {
    $wf .= ($wf == '') ? 'where dep_id = ' . $_POST['depto'] . ' ' : ' and dep_id = ' . $_POST['depto'] . ' ';
}

if (count($tipo) > 0 && $tipo != '') {
    foreach ($tipo as $key => $val) {
        $in .= '"' . $val . '",';
    }
    $in = trim($in, ',');
    if ($in != '') {
        $wf .= ($wf == '') ? 'where tcl_id in (' . $in . ') ' : ' and tcl_id in (' . $in . ') ';
    }
}

function titulo($ue_id, $ue_nombre)
{
    echo ('<tr>
            <td style="background-color:#58ACFA; font-size:18px;" colspan="4">' . utf8Decode($ue_id . ' - ' . $ue_nombre) . '</td>
            </tr>
        ');
}
$depart = '';
switch ($_POST['depto']) {
    case 44:
        $firma = 'LIC. IRACEMA EDNA  FLORES MENDOZA';
        $cargo = 'JEFA DEL DEPARTAMENTO DE APLICACIÓN PRESUPUESTAL SECTOR CENTRAL';
        $depart = 'DEPARTAMENTO DE APLICACIÓN PRESUPUESTAL SECTOR CENTRAL';
        break;
    case 45:
        $firma = 'LIC. HECTOR EFREN TOMAS YESCAS';
        $cargo = 'JEFE DEL DEPARTAMENTO DE APLICACIÓN PRESUPUESTAL SECTOR FORÁNEO';
        $depart = 'DEPARTAMENTO DE APLICACIÓN PRESUPUESTAL SECTOR FORÁNEO';
        break;
    case 46:
        $firma = 'L.C.P. ANDREA RAMÍREZ LÓPEZ';
        $cargo = 'JEFA DEL DEPARTAMENTO DE SEGUIMIENTO A COMITÉS DE COMPRAS';
        $depart = 'DEPARTAMENTO DE SEGUIMIENTO A COMITÉS DE COMPRAS';
        break;
}

$hoy = date("Y-m-d H:i:s");
echo '<table align="center" width="100%">';
echo '<tr>';
echo '<td colspan="4" align="CENTER"><b>ENTREGA DE CLCs 2025</b></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="4" align="CENTER" ><b>' . utf8Decode($depart) . '</td>';
echo '</tr>';

echo '<tr>';
echo '<td  colspan="4" align="left" >FECHA DE LISTA : ' . ($_POST['Fechainicio'] != "" ? DateToQuotedMySQLDate($_POST['Fechainicio']) : '') . '</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="4" align="CENTER" ></td>';
echo '</tr>';

function cabecera()
{
    echo (' <tr>
        <td align="left"  style="font-size: 12.5; background-color: #003366; color: white;">UR</td>
        <td align="left" style="font-size: 12.5; background-color: #003366; color: white;">DEPENDENCIA O ENTIDAD</td>
        <td align="left" style="font-size: 12.5; background-color: #003366; color: white;">FOLIOS </td>
        <td align="left" style="font-size: 12.5; background-color: #003366; color: white;">HORA</td>
    </tr>');
}

$eclid = isset($_POST["ecl_id"]) && $_POST["ecl_id"] != '' ? $_POST["ecl_id"] : 0;

$actual = date("Y-m-d");
$fechaIni = isset($_POST['Fechainicio']) && $_POST["Fechainicio"] != "" ? $_POST['Fechainicio'] : $actual;
$fechaFin = isset($_POST['Fechafinal']) && $_POST["Fechafinal"] != "" ? $_POST['Fechafinal'] : $actual;

$where = ' AND a.ecl_id= ' . $eclid . '  AND  DATE(FechaProceso) BETWEEN ' . DateToQuotedMySQLDate($fechaIni) . ' AND ' . DateToQuotedMySQLDate($fechaFin);

$sql = '
    SELECT
        gpo_id,ur_id,ur_clave,
        ur_nombre,
        if(clc_tipocereCo = 1
            ,"CERE",if( clc_tipocereCo=2
            , "CERECO","SIN CERE(CO)")) AS cereco,
        tcl_id,tcl_nombre,clc_concepto,
        b.ecl_id,ecl_nombre,dep_nombre,
        DATE_FORMAT(FechaProceso,"%d/%m/%Y") AS FechaProceso,
        hora,clc_id recibidas,
        TRUNCATE (clc_percepcion,2) -TRUNCATE (clc_retencion,2) AS importe,
        dep_id
    FROM scp_Clc a
        INNER JOIN scp_ClcAuxiliar USING (gpo_id,ur_id,clc_id,ecl_id)
        INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
        INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
        INNER JOIN sinpres.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id/*,cso_id*/)
        INNER JOIN sinpres.cat_TipoRecurso t USING (ter_id)
        INNER JOIN cat_EstadoClc USING (ecl_id)
        INNER JOIN cat_TipoClc USING (tcl_id)
        INNER JOIN (SELECT
                gpo_id,ur_id,
                ecl_id,usr_fecha AS FechaProceso,
                clc_id,hour(usr_fecha) AS hora
            FROM ' . BD_BITACORA . '.bit_Clc
            WHERE
                ecl_id = ' . $eclid . '
            GROUP BY gpo_id,ur_id,clc_id,FechaProceso
            ORDER BY gpo_id,ur_id,clc_id,FechaProceso
        )b USING (gpo_id,ur_id,clc_id)
        INNER JOIN enc_FiltroClc USING (gpo_id, ur_id, tcl_id,stc_id,mno_id)
        INNER JOIN cat_Departamento USING (dep_id)
        ' . $wf . ' and clc_id != 0 ' . $where . '
    GROUP by gpo_id,ur_id,clc_id,dep_nombre,FechaProceso,tcl_nombre
    ORDER by gpo_id,ur_id,clc_id
        ';
$datos = new AccesoDatos();
$datos->conectar();
$datos->setSql($sql);
$datos->consultar();
$datos->desconectar();
$row = $datos->extraer();

/* variables  de cambios */
$tcl_id = ($row ? $row['tcl_id'] : '');
$tcl_nombre = ($row ? $row['tcl_nombre'] : '');

$celda = 0;
titulo(($row ? $row['tcl_id'] : ''), ($row ? $row['tcl_nombre']:''));
cabecera();

do {
    if ($tcl_id != ($row ? $row['tcl_id'] : '')) {
        titulo($row['tcl_id'], $row['tcl_nombre']);
        cabecera();
    }
    echo ' <tr>';
    echo '<td align="left" style="font-size: 10.5; "><b>' . ($row ? $row['ur_clave'] : '') . '</b></td>';
    echo '<td align="left" style="font-size: 10.5; "><b>' . ($row ? utf8Decode($row['ur_nombre']) : '') . '</b></td>';
    echo '<td align="left" style="font-size: 10.5;"><b>' . ($row ? $row['recibidas'] : ''). '</b></td>';
    echo '<td align="left" style="font-size: 10.5; "><b>' . ($row ? $row['hora'] : ''). '</b></td>';
    echo '</tr>';
} while ($row = $datos->extraer());

echo ' <tr>';
echo '<td colspan="2" align="center" style="font-size: 10.5; "><b></b></td>';
echo '</tr>';

echo ' <tr>';
echo '<td colspan="2" align="center" style="font-size: 10.5; "><b></b></td>';
echo '</tr>';

echo ' <tr>';
echo '<td colspan="2" align="center" style="font-size: 10.5; "><b></b></td>';
echo '</tr>';

echo ' <tr>';
echo '<td colspan="2" align="center" style="font-size: 10.5; "><b>RECIBE:</b></td>';
echo '</tr>';

echo ' <tr>';
echo '<td colspan="2" align="center" style="font-size: 10.5; "><b>' . (isset($firma) ? utf8Decode($firma) : '') . '</b></td>';
echo '</tr>';

echo ' <tr>';
echo '<td colspan="2" align="center" style="font-size: 10.5;"><b>' . (isset($cargo) ? utf8Decode($cargo) : ''). '</b></td>';
echo '</tr>';

echo ' <tr>';
echo '<td colspan="2" align="center" style="font-size: 10.5; "><b>' . date("d/m/y") . ' - ' . date("H:i:s") . '</b></td>';
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
    if ($Fecha != "" && strpos($Fecha, "/")) {
        $trozos = explode("/", $Fecha, 3);
        return "'" . $trozos[2] . "-" . $trozos[1] . "-" . $trozos[0] . "'";
    } else if ($Fecha != "") {
        return "'" . $Fecha . "'";
    } else {
        return "NULL";
    }
}
