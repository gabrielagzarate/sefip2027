<?php

session_start();
set_time_limit(0);
require_once dirname(__FILE__) . "/../../../../lib/constantes.php";
require_once dirname(__FILE__) . "/../../../../lib/helpers.php";
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../lib/conversionNumLetras.php';

$ruta = utf8Decode("ResumenLista.xls");
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=" . $ruta);
header("Pragma: no-cache");
header("Expires: 0");

$today = date("F j, Y, g:i a");

echo '<table>';
echo '<tr>';
echo '<td COLSPAN="7" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:15px;" ALIGN="center"><b>' . utf8Decode("GOBIERNO DEL ESTADO DE OAXACA") . '</b></td>';
echo '</tr>';
echo '<tr>';
echo '<td COLSPAN="7" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:15px;" ALIGN="center"><b>' . utf8Decode("DIRECCIÓN DE EGRESOS Y CONTROL PRESUPUESTAL") . '</b></td>';
echo '</tr>';
echo '<tr>';
echo '<td COLSPAN="7" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:15px;" ALIGN="center"><b>' . utf8Decode("COORDINACIÓN DE EJERCICIO PRESUPUESTAL") . '</b></td>';
echo '</tr>';
echo '<tr>';
echo '<td COLSPAN="7" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:15px;" ALIGN="center"><b>DEPARTAMENTO DE CUENTAS POR PAGAR</b></td>';
echo '</tr>';
echo '<tr>';
echo '<td COLSPAN="7" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:15px;" ALIGN="center"><b>' . utf8Decode("RECEPCIÓN DE CLCs EN VENTANILLA") . '</b></td>';
echo '</tr>';
echo '<tr>';
echo '<td ALIGN="center" style="border-color: #003366; border-width:0px; border-style: solid"></td>';
echo '</tr>';
echo '<tr>';
echo '<td COLSPAN="7" align="right" style="font-size: 13px; background-color: #003366; color: white;"><b>FECHA DE LA LISTA:  ' . $today . '  </b></td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="2" align="center" style="font-size: 15px; background-color: #000000; color: white;"><b>UR</b></td>';
echo '<td align="center" style="font-size: 15px; background-color: #000000; color: white;"><b>' . utf8Decode("SERVICIOS PERSONALES") . '</b></td>';
echo '<td align="center" style="font-size: 15px; background-color: #000000; color: white;"><b>OBRA</b></td>';
echo '<td align="center" style="font-size: 15px; background-color: #000000; color: white;"><b>GASTO ENTIDADES</b></td>';
echo '<td align="center" style="font-size: 15px; background-color: #000000; color: white;"><b>' . utf8Decode("GASTO SECTOR CENTRAL") . '</b></td>';
echo '<td align="center" style="font-size: 15px; background-color: #000000; color: white;"><b>' . utf8Decode("TOTAL") . '</b></td>';
echo '</tr>';

$actual = date("Y-m-d");
$params = [];

if ($_POST['Fechalista'] != "" && $_POST['Fechalista'] != 0) {
    $_POST['Fechalista'] = DateToQuotedMySQLDate($_POST['Fechalista']);
    $where = ' WHERE  a.ecl_id >= 4  AND  DATE(FechaProceso) = :fechalista ';
    $where2 = ' AND DATE(usr_fecha) = :fechalista
        AND bit_Observacion = "RECIBIDA POR LA SEFIN"';
    $params['fechalista'] = $_POST['Fechalista'];
} else {
    $where = ' WHERE a.ecl_id >= 4 AND  DATE(FechaProceso)= "' . $actual . '" ';
    $where2 = ' AND DATE(usr_fecha) = "' . $actual . '" ';
    $params['factual'] = $actual;
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
    SELECT gpo_id, ur_id, ur_clave, ur_nombre,
        COUNT(IF((tcl_id = "A " OR (tcl_id = "C" AND stc_id = 01)),tcl_id,NULL)) AS Servicios,
        COUNT(IF(((tcl_id in ("D","F","G","H") OR (tcl_id = "C" AND stc_id = 02)) AND dep_id = 46),tcl_id,NULL)) AS Obra,
        COUNT(IF(((tcl_id in ("B","D","E","I","J","K","L","M") OR (tcl_id = "C" AND stc_id = 02)) AND dep_id = 44),tcl_id,NULL)) AS GasCen,
        COUNT(IF(((tcl_id in("B","D","E") OR(tcl_id="C" AND stc_id = 02)) AND dep_id = 45),tcl_id,NULL)) AS GasEnt,
        ' . $anyValue . '(FechaProceso) AS FechaProceso
    FROM scp_Clc a
    INNER JOIN cat_UnidadResponsable using(gpo_id,ur_id)
    INNER JOIN cat_TipoClc using(tcl_id)
    INNER JOIN (SELECT gpo_id, ur_id, ecl_id,
                DATE_FORMAT(usr_fecha,"%Y-%m-%d %H:%i") AS FechaProceso,
                clc_id
            FROM ' . BD_BITACORA . '.bit_Clc
            WHERE ecl_id = 4
            ' . $where2 . '
            GROUP BY gpo_id,ur_id,clc_id,usr_fecha
            ORDER BY gpo_id,ur_id,clc_id,usr_fecha
        )b USING (gpo_id,ur_id,clc_id)
    INNER JOIN (SELECT gpo_id, ur_id, tcl_id, stc_id,
            group_concat(distinct dep_id order by dep_id) AS dep_id
        FROM enc_FiltroClc
        GROUP BY gpo_id,ur_id,tcl_id,stc_id
    )e USING(gpo_id, ur_id,tcl_id,stc_id)
        INNER JOIN cat_Departamento USING(dep_id)
    ' . $where . '
        AND clc_recursoFederal = 2
    GROUP by gpo_id,ur_id
    ORDER by gpo_id,ur_id,FechaProceso
';
$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);

$conexion->consultar($params);
$conexion->desconectar();
$row = $conexion->extraer();
$importe = $importeUr = 0;
$filas = 0;
$horizontal = 0;
$Ser = 0;
$Obra = 0;
$GastoCen = 0;
$GastoEnt = 0;
$Vertical = 0;

do {
    $horizontal = ($row ? $row['Servicios'] : 0) + ($row ? $row['Obra'] : 0) + ($row ? $row['GasCen'] : 0) + ($row ? $row['GasEnt'] : 0);
    if ($filas % 2 == 0) {
        echo '<tr>';
        echo '<td align="left" style="font-size: 13px; background-color: #99CC99; color: black;"><b>' . ($row ? $row['ur_clave'] : '') . '</b></td>';
        echo '<td align="left" style="font-size: 13px; background-color: #99CC99; color: black;"><b>' . ($row ? utf8Decode($row['ur_nombre']) : '') . '</b></td>';
        echo '<td align="right" style="font-size: 13px; background-color: #99CC99; color: black;"><b>' . ($row ? $row['Servicios'] : 0) . '</b></td>';
        echo '<td align="right" style="font-size: 13px; background-color: #99CC99; color: black;"><b>' . ($row ? $row['Obra'] : 0) . '</b></td>';
        echo '<td align="right" style="font-size: 13px; background-color: #99CC99; color: black;"><b>' . ($row ? $row['GasEnt'] : 0) . '</b></td>';
        echo '<td align="right" style="font-size: 13px; background-color: #99CC99; color: black;"><b>' . ($row ? $row['GasCen'] : 0) . '</b></td>';
        echo '<td align="right" style="font-size: 13px; background-color: #99CC99; color: black;"><b>' . ($horizontal) . '</b></td>';
        echo '</tr>';
    } else {
        echo '<tr>';
        echo '<td align="left" style="font-size: 13px; background-color: #C6E7CE; color: black;"><b>' . ($row ? $row['ur_clave'] : '') . '</b></td>';
        echo '<td align="left" style="font-size: 13px; background-color: #C6E7CE; color: black;"><b>' . ($row ? utf8Decode($row['ur_nombre']) : '') . '</b></td>';
        echo '<td align="right" style="font-size: 13px; background-color: #C6E7CE; color: black;"><b>' . ($row ? $row['Servicios'] : 0) . '</b></td>';
        echo '<td align="right" style="font-size: 13px; background-color: #C6E7CE; color: black;"><b>' . ($row ? $row['Obra'] : 0) . '</b></td>';
        echo '<td align="right" style="font-size: 13px; background-color: #C6E7CE; color: black;"><b>' . ($row ? $row['GasEnt'] : 0) . '</b></td>';
        echo '<td align="right" style="font-size: 13px; background-color: #C6E7CE; color: black;"><b>' . ($row ? $row['GasCen'] : 0) . '</b></td>';
        echo '<td align="right" style="font-size: 13px; background-color: #C6E7CE; color: black;"><b>' . $horizontal . '</b></td>';
        echo '</tr>';
    }

    $filas++;
    $Ser += ($row ? $row['Servicios'] : 0);
    $Obra += ($row ? $row['Obra'] : 0);
    $GastoCen += ($row ? $row['GasEnt'] : 0);
    $GastoEnt += ($row ? $row['GasCen'] : 0);
    $Vertical += ($horizontal);
} while ($row = $conexion->extraer());

echo '<tr>';
echo '<br><br>';
echo '<td colspan="2"; align="right" style="font-size: 14px; background-color: #000000; color: #FFFFFF;"><b>TOTAL : </b></td>';
echo '<td align="right" style="font-size: 14px; background-color: #000000; color: #FFFFFF;"><b>' . $Ser . '</b></td>';
echo '<td align="right" style="font-size: 14px; background-color: #000000; color: #FFFFFF;"><b>' . $Obra . '</b></td>';
echo '<td align="right" style="font-size: 14px; background-color: #000000; color: #FFFFFF;"><b>' . $GastoEnt . '</b></td>';
echo '<td align="right" style="font-size: 14px; background-color: #000000; color: #FFFFFF;"><b>' . $GastoCen . '</b></td>';
echo '<td align="right" style="font-size: 14px; background-color: #000000; color: #FFFFFF;"><b>' . $Vertical . '</b></td>';
echo '</tr>';
//FIN DEL CUERPO DE LA TABLA
echo '</table>';
?>

<?php

function fechan($fechavieja)
{
    $d = substr($fechavieja, 8, 2);
    $m = substr($fechavieja, 5, 2);
    $a = substr($fechavieja, 0, 4);
    return $d . "/" . $m . "/" . $a;
}

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