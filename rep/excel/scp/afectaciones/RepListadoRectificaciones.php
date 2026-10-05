<?php
session_start();
set_time_limit(0);
require_once('../../../../lib/MesNombre.php');
require_once('../../../../lib/helpers.php');
require_once('../../../../lib/AccesoDatos.php');
require_once('../../../lib/conversionNumLetras.php');	

$ruta = utf8Decode("ResumenLoteRectificaciones.xls");
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=" . $ruta);
header("Pragma: no-cache");
header("Expires: 0");

// VARIABLES PARA EL REPORTE
$importe = $importeUr = 0;
$contador = 1;
$filas = 0;
$lote = $leyenda = $unidad = $urnombre = '';
$ur = 'NINGUNA';
$bandera = false;
$cadena_tabla = "";
$params = [];

$wf = '';
if (isset($_POST['tipoRct']) && $_POST['tipoRct'] != '') {
    switch ($_POST['tipoRct']) {
        case 1: 
            $wf.= ($wf == '') ? ' rct_reintegro = 1' : '';
            break;
        case 2: 
            $wf.= ($wf == '') ? ' rct_reintegro IN (2,3)' : '';
            break;
    }
}	

if (isset($_POST['ur_id'])) {
    $wf.= ' AND  concat(gpo_id,ur_id) = (:ur_id) ';
    $params['ur_id'] = $_POST['ur_id'];
}

$wh = '';
if (isset($_POST['meses'])) {
    $wh .= 'AND lrct.lrct_mes = :meses ';
    $params['meses'] = $_POST['meses'];
}
if(isset($_POST['listado'])) {
    $wh .= ' AND lrct.lrct_listado_id = :listado ';
    $params['listado'] = $_POST['listado'];
}

//a qui solo se hace la consulta para el encabezado y solo un registro
$miconexion = new AccesoDatos();
$miconexion->conectar();
$miconexion->setSql('
    SELECT gpo_id, ur_id, clc_id, tcl_id, 
        tcl_nombre, rct_id, rct_justificacion,  
        rct_reintegro,  
        DATE_FORMAT( rct_fechaAutorizacion,"%d/%m/%Y") AS rct_fechaAutorizacion,    
        ecl_nombre, rct_percepcion-rct_retencion AS rct_importe,
        lrct_dia, lrct_mes, lrct_anio, lrct_reintegro, 
        DATE_FORMAT( lrct_fechaListado,"%d/%m/%Y") AS lrct_fechaListado,    
        DATE_FORMAT( rct_fechaElaboracion,"%d/%m/%Y") AS rct_fechaElaboracion,  
        ur_nombre, lrct_listado_id 
    FROM scp_MaestroRectificacion a
        INNER JOIN scp_ListadoRectificacion lrct USING (gpo_id, ur_id, rct_id)
        INNER JOIN scp_Clc b USING (gpo_id,ur_id,clc_id)
        INNER JOIN enc_FiltroRectificacion f USING (gpo_id,ur_id,tcl_id,stc_id)
        INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
        INNER JOIN sinpres.cat_ClaveFinanciamiento'.APP_EJERCICIO.' USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
        INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
        INNER JOIN cat_TipoClc USING (tcl_id)
        INNER JOIN cat_EstadoClc e  ON  a.ecl_id = e.ecl_id
    WHERE ' . $wf . '
        ' . $wh . '
        AND dep_id = :dep_id
        AND (a.ecl_id=6 AND b.ecl_id = 18 and clc_recursoFederal=2)
    GROUP BY gpo_id,ur_id,rct_id,clc_id
    ORDER BY gpo_id,ur_id,rct_id,clc_id;
');

$params['dep_id'] = $_SESSION[_S]['dep_id'];
$miconexion->consultar($params);
$miconexion->desconectar();

$contEnca = true;

echo'<table>'; // INICIO DEL DOCUMENTO
while ($row = $miconexion->extraer()) {
    $unidad = $row['gpo_id'].$row['ur_id'];
    if ($contEnca == true){
        $contEnca = false;
        echo'<tr>';//SE EMPIEZA A FORMAR EL ENCABEZADO
            echo'<td COLSPAN="8" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:18px;" ALIGN="center"><b> GOBIERNO DE OAXACA '.APP_EJERCICIO.'</b></td>';
        echo'</tr>';
        echo'<tr>';
            echo utf8Decode('<td COLSPAN="8" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:16px;" ALIGN="center"><b>SUBSECRETARÍA DE EGRESOS, CONTABILIDAD Y TESORERÍA</b></td>');
        echo'</tr>';
        echo'<tr>';
            echo'<td COLSPAN="8" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:16px;" ALIGN="center"><b>PRESUPUESTO DE EGRESOS</b></td>';
        echo'</tr>';
        echo'<tr>';
            echo'<td COLSPAN="8" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:16px;" ALIGN="center"><b>RESUMEN DE RECTIFICACIONES</b></td>';
        echo'</tr>';
        echo'<tr>';
            echo'<td ALIGN="center" style="border-color: #003366; border-width:0px; border-style: solid"></td>';
        echo'</tr>';
        echo'<tr>';

        echo'<td COLSPAN="4" align="LEFT" style="font-size: 13px; background-color: #003366; color: white;"><b> ' . $row['lrct_fechaListado'] . ' </b></td>';

        if ($_POST['tipoRct'] == '1') {
            echo'<td COLSPAN="5" align="right" style="font-size: 13px; background-color: #003366; color: white;"><b>LISTA DE RECTIFICACIONES CON REINTEGRO DEL MES DE ' . MesNombre($_POST['meses']) . '</b></td>';     
        
        } else {
            echo'<td COLSPAN="5" align="right" style="font-size: 13px; background-color: #003366; color: white;"><b>LISTA DE RECTIFICACIONES SIN REINTEGRO DEL MES DE ' . MesNombre($_POST['meses']) . '</b></td>';     
        }

        echo'</tr>';
        echo'<tr>';
            echo'<td align="center" style="font-size: 13px; background-color: #000000; color: white;"><b>No. PROG</b></td>';
            echo'<td align="center" style="font-size: 13px; background-color: #000000; color: white;"><b>No. FOLIO</b></td>';
            echo'<td align="center" style="font-size: 13px; background-color: #000000; color: white;"><b>' . utf8Decode("FECHA DE ELABORACIÓN") . '</b></td>';
            echo'<td align="center" style="font-size: 13px; background-color: #000000; color: white;"><b>STATUS</b></td>';
            echo'<td align="center" style="font-size: 13px; background-color: #000000; color: white;"><b>' . utf8Decode("FECHA DE AUTORIZACIÓN") . '</b></td>';
            echo'<td align="center" style="font-size: 13px; background-color: #000000; color: white;"><b>TIPO DE RECTIF.</b></td>';
            echo'<td align="center" style="font-size: 13px; background-color: #000000; color: white;"><b>No. CEDULA</b></td>';
            echo'<td align="center" style="font-size: 13px; background-color: #000000; color: white;"><b>' . utf8Decode("JUSTIFICACIÓN") . '</b></td>';
            echo'<td align="center" style="font-size: 13px; background-color: #000000; color: white;"><b>' . utf8Decode("IMPORTE") . '</b></td>';
        echo'</tr>';
    }

    if ($row['lrct_reintegro'] == 'CR') {
        $leyenda = 'CON REINTEGRO';
    } else {
        $leyenda = 'SIN REINTEGRO';
    }

    if ($unidad != $ur) {
        $ur = $row['gpo_id'] . $row['ur_id'];
        $unidad = $row['gpo_id'] . $row['ur_id'];
        $urnombre = $row['ur_nombre'];
        //OBTENER TOTAL DE LA UNIDAD RESPOSABLE
        $conexionTotal = new AccesoDatos(); 
        $conexionTotal->conectar();

        
        $sql = '
            SELECT 
                SUM(rct_importe) AS total
            FROM(
                SELECT gpo_id, ur_id, rct_percepcion - rct_retencion AS rct_importe 
                FROM scp_MaestroRectificacion a
                    INNER JOIN scp_ListadoRectificacion lrct USING (gpo_id, ur_id, rct_id)
                    INNER JOIN scp_Clc b USING(gpo_id,ur_id,clc_id)
                    INNER JOIN enc_FiltroRectificacion f USING (gpo_id,ur_id,tcl_id,stc_id)
                    INNER JOIN cat_TipoClc USING (tcl_id)
                WHERE ' . $wf . '
                    ' . $wh . ' 
                    AND gpo_id = :gpo_id
                    AND ur_id = :ur_id
                    AND dep_id = :dep_id
                    AND (a.ecl_id=6 AND b.ecl_id=18 AND clc_recursoFederal=2)
                GROUP BY gpo_id,ur_id,rct_id,clc_id
            ) AS t
        ';
        $params['gpo_id'] = $row['gpo_id'];
        $params['ur_id'] = $row['ur_id'];
        
        $conexionTotal->setSql($sql);
        $conexionTotal->consultar($params);
        $conexionTotal->desconectar();
        $rowTotal = $conexionTotal->extraer();

        $importeUr = $rowTotal ['total'];

        echo'<tr>';
            echo'<td colspan="7" align="left" style="font-size: 13px; background-color: #FFFFFF; color: BLACK;"><b>' . $ur . ' .- ' . utf8Decode($urnombre) . '</b></td>';
            echo'<td align="right" style="font-size: 13px; background-color: #FFFFFF; color: BLACK;">
            <b></b></td>';
            echo'<td align="right" style="font-size: 13px; background-color: #FFFFFF; color: BLACK;">
            <b>' . '$' . number_format($importeUr,2) . '</b></td>';
        echo'</tr>';
        
        $importeUr = 0;
        $urnombre = '';
    }

    if ($filas%2 == 0) {
        $cadena_tabla .= '<tr>';
            $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>'.$contador.'</b></td>';
            $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>'.$row['rct_id'].'</b></td>';
            $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>'.$row['rct_fechaElaboracion'].'</b></td>';
            $cadena_tabla .= '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>'.utf8Decode($row['ecl_nombre']).'</b></td>';
            $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>"'.$row['rct_fechaAutorizacion'].'"</b></td>';
            $cadena_tabla .= '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>'.utf8Decode($row['rct_reintegro'] == 1 ? "CON REINTEGRO" : "SIN REINTEGRO").'</b></td>';
            $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>'.$row['clc_id'].'</b></td>';
            $cadena_tabla .= '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>'.utf8Decode($row['rct_justificacion']).'</b></td>';
            $cadena_tabla .= '<td align="right" style="font-size: 11px; background-color: #99CC99; color: black;"><b>'.number_format($row['rct_importe'],2).'</b></td>';
        $cadena_tabla .= '</tr>';
    } else {
        
        $cadena_tabla .= '<tr>';
            $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>'.$contador.'</b></td>';
            $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>'.$row['rct_id'].'</b></td>';
            $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>'.$row['rct_fechaElaboracion'].'</b></td>';
            $cadena_tabla .= '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b></b>'.utf8Decode($row['ecl_nombre']).'</td>';
            $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>"'.$row['rct_fechaAutorizacion'].'"</b></td>';
            $cadena_tabla .= '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>'.utf8Decode($row['rct_reintegro'] == 1 ? "CON REINTEGRO" : "SIN REINTEGRO").'</b></td>';
            $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>'.$row['clc_id'].'</b></td>';
            $cadena_tabla .= '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>'.utf8Decode($row['rct_justificacion']).'</b></td>';
            $cadena_tabla .= '<td align="right" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>'.number_format($row['rct_importe'],2).'</b></td>';
        $cadena_tabla .= '</tr>';
    }   

    echo $cadena_tabla;
    $cadena_tabla = "";

    $contador++;
    $importeUr += $row['rct_importe'];
    $importe += $row['rct_importe'];
    $urnombre = $row['ur_nombre'];
    $unidad = $row['gpo_id'] . $row['ur_id']; 
    $filas++;
};

echo'<tr>';
    echo'<br><br>';
    echo'<td colspan="8" align="right" style="font-size: 15px; background-color: #000000; color: WHITE;"><b>TOTAL GENERAL</b></td>';
    echo'<td align="right" style="font-size: 16px; background-color: #000000; color: WHITE;"><b>'.'$'.number_format($importe,2).'</b></td>';
echo'</tr>';

$totaldetotales = $importe;
$totaldetotales = number_format($totaldetotales, 2, ".", "");
$dec = explode(".",$totaldetotales);

//FIN DEL CUERPO DE LA TABLA
echo'</table>';

function fechan($fechavieja){
    $d = substr($fechavieja, 8, 2);
    $m = substr($fechavieja, 5, 2);;
    $a = substr($fechavieja, 0, 4);;	
    
    return $d."/".$m."/".$a;
}

function MySQLDateToDate($MySQLFecha)
{
    if (($MySQLFecha == "") or ($MySQLFecha == "0000-00-00")) {
        return "";
    } else {
        return date("d/m/Y",strtotime($MySQLFecha));
    }
}

function DateToQuotedMySQLDate($Fecha)
{
    if ($Fecha <> "") {
        $trozos = explode("/",$Fecha,3);
        return "'".$trozos[2]."-".$trozos[1]."-".$trozos[0]."'"; 
    } else {
        return "NULL";
    }
}