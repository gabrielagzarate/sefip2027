<?php

if (!function_exists('u8d')) {
    function u8d($value)
    {
        return mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
    }
}

ini_set('display_errors', 1);

require_once dirname(__FILE__) . '/../../../../lib/MesNombre.php';
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';

session_start();
set_time_limit(0);

require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../lib/conversionNumLetras.php';

$ruta = u8d("'ResumenLoteRectificaciones.xls'");

header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=" . $ruta);
header("Pragma: no-cache");
header("Expires: 0");

$wh = '';
if (isset($_POST['meses'])) {
    $wh .= 'AND lr.lrct_mes = ' . $_POST['meses'] . '';
}
if (isset($_POST['listado'])) {
    $wh .= ' AND lr.lrct_listado_id = ' . $_POST['listado'] . '';
}

$wf = '';
if (isset($_POST['tipoRct']) && $_POST['tipoRct'] != '') {
    switch ($_POST['tipoRct']) {
        case 1:
            $wf .= ($wf == '') ? ' rct_reintegro = 1' : '';
            break;
        case 2:
            $wf .= ($wf == '') ? ' rct_reintegro IN (2,3)' : '';
            break;
    }
}

if (isset($_POST['ur_id'])) {
    $wf .= ' AND  concat(gpo_id,ur_id) = ("' . $_POST['ur_id'] . '") ';
}

/**solo se hace la consulta para el encabezado y solo un registro */

$miconexion = new AccesoDatos();
$miconexion->conectar();
$miconexion->setSql('
        SELECT 
            gpo_id,
            ur_id,
            clc_id,
            tcl_id,
            tcl_nombre,
            rct_id,
            rct_justificacion,
            rct_reintegro,
            DATE_FORMAT(rct_fechaAutorizacion,"%d/%m/%Y") AS rct_fechaAutorizacion,
            ecl_nombre,
            (rct_percepcion-rct_retencion) AS rct_importe,
            #lr.lrct_dia,
            #lr.lrct_mes,
            #lr.lrct_anio,
            #rct_reintegroLote,
            lr.lrct_reintegro,
            DATE_FORMAT(lr.lrct_fechaListado,"%d/%m/%Y") AS rct_fechaListado, 
            DATE_FORMAT(rct_fechaElaboracion,"%d/%m/%Y") AS rct_fechaElaboracion,
            cfi_clave,
            cfi_nombre,
            ur_nombre,
            lr.lrct_listado_id
        FROM scp_MaestroRectificacion a
            INNER JOIN scp_ListadoRectificacion lr USING (gpo_id, ur_id, rct_id)
            INNER JOIN scp_Clc b USING (gpo_id,ur_id,clc_id)
            INNER JOIN enc_FiltroRectificacion f USING (gpo_id,ur_id,tcl_id,stc_id)
            INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
            INNER JOIN sinpres.cat_ClaveFinanciamiento2022 USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
            INNER JOIN cat_TipoClc USING (tcl_id)
            INNER JOIN cat_EstadoClc e ON a.ecl_id = e.ecl_id
		WHERE ' . $wf . ' 
            AND (a.ecl_id = 6 AND b.ecl_id = 18 AND clc_recursoFederal = 2)
		' . $wh . '  
		GROUP BY gpo_id,ur_id,rct_id,clc_id,cfi_clave,cfi_nombre
        ORDER BY gpo_id,ur_id,rct_id,clc_id
        LIMIT 1;
    ');

//echo "<pre>" . $miconexion->getSql($sql) . "</pre>";
//die();

$miconexion->consultar();
$miconexion->desconectar();
$datos = $miconexion->extraer();

echo '<table>';

/** SE EMPIEZA EL ENCABEZADO */
echo '<tr>';
echo '<td COLSPAN="8" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:18px;" ALIGN="center"><b>GOBIERNO DEL ESTADO DE OAXACA</b></td>';
echo '</tr>';
echo '<tr>';
echo '<td COLSPAN="8" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:18px;" ALIGN="center"><b>2022</b></td>';
echo '</tr>';
echo '<tr>';
echo u8d('<td COLSPAN="8" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:16px;" ALIGN="center"><b>SUBSECRETARÍA DE EGRESOS, CONTABILIDAD Y TESORERÍA</b></td>');
echo '</tr>';
echo '<tr>';
echo '<td COLSPAN="8" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:16px;" ALIGN="center"><b>PRESUPUESTO DE EGRESOS</b></td>';
echo '</tr>';
echo '<tr>';
echo '<td COLSPAN="8" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:16px;" ALIGN="center"><b>RESUMEN DE RECTIFICACIONES</b></td>';
echo '</tr>';
echo '<tr>';
echo '<td ALIGN="center" style="border-color: #003366; border-width:0px; border-style: solid"></td>';
echo '</tr>';
echo '<tr>';
echo '<td COLSPAN="4" align="LEFT" style="font-size: 13px; background-color: #003366; color: white;"><b> ' . $datos['rct_fechaListado'] . ' </b></td>';

if ($_POST['tipoRct'] == '1') {
    echo '<td COLSPAN="5" align="right" style="font-size: 13px; background-color: #003366; color: white;"><b>LISTA DE RECTIFICACIONES CON REINTEGRO DEL MES DE ' . MesNombre($_POST['meses']) . '</b></td>';
} else {
    echo '<td COLSPAN="5" align="right" style="font-size: 13px; background-color: #003366; color: white;"><b>LISTA DE RECTIFICACIONES SIN REINTEGRO DEL MES DE ' . MesNombre($_POST['meses']) . '</b></td>';
}

echo '</tr>';
echo '<tr>';
echo '<td align="center" style="font-size: 13px; background-color: #000000; color: white;"><b>No. PROG</b></td>';
echo '<td align="center" style="font-size: 13px; background-color: #000000; color: white;"><b>No. FOLIO</b></td>';
echo '<td align="center" style="font-size: 13px; background-color: #000000; color: white;"><b>' . u8d("FECHA DE ELABORACIÓN") . '</b></td>';
echo '<td align="center" style="font-size: 13px; background-color: #000000; color: white;"><b>STATUS</b></td>';
echo '<td align="center" style="font-size: 13px; background-color: #000000; color: white;"><b>' . u8d("FECHA DE AUTORIZACIÓN") . '</b></td>';
echo '<td align="center" style="font-size: 13px; background-color: #000000; color: white;"><b>TIPO DE RECTIF.</b></td>';
echo '<td align="center" style="font-size: 13px; background-color: #000000; color: white;"><b>No. CEDULA</b></td>';
echo '<td align="center" style="font-size: 13px; background-color: #000000; color: white;"><b>' . u8d("JUSTIFICACIÓN") . '</b></td>';
echo '<td align="center" style="font-size: 13px; background-color: #000000; color: white;"><b>' . u8d("IMPORTE") . '</b></td>';
echo '</tr>';

//SE EMPIEZA A ARMAR EL CUERPO

$sql = '
        SELECT
            gpo_id,
            ur_id,
            clc_id,
            tcl_id,
            tcl_nombre,
            rct_id,
            rct_justificacion,
            rct_reintegro,
            DATE_FORMAT(rct_fechaAutorizacion,"%d/%m/%Y") AS rct_fechaAutorizacion,
            ecl_nombre,
            rct_importe,
            lr.lrct_dia,
            lr.lrct_mes,
            lr.lrct_anio,
            #rct_reintegroLote,
            lr.lrct_reintegro,
            DATE_FORMAT(lr.lrct_fechaListado,"%d/%m/%Y") AS rct_fechaLote,
            DATE_FORMAT( rct_fechaElaboracion,"%d/%m/%Y") AS rct_fechaElaboracion,
            cfi_clave,
            cfi_nombre,
            ur_nombre,
            #rct_idLote
            lr.lrct_listado_id
        FROM scp_MaestroRectificacion a
            INNER JOIN scp_ListadoRectificacion lr USING (gpo_id, ur_id, rct_id)
            INNER JOIN scp_Clc sclc USING (gpo_id,ur_id,clc_id)
            INNER JOIN enc_FiltroRectificacion f USING (gpo_id,ur_id,tcl_id,stc_id)
            INNER JOIN enc_ClaveFinanciamiento b USING (ecf_id)
            INNER JOIN sinpres.cat_ClaveFinanciamiento2022 USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
            INNER JOIN cat_TipoClc USING (tcl_id)
            INNER JOIN cat_EstadoClc e  on  a.ecl_id = e.ecl_id
		WHERE ' . $wf . ' 
            AND (a.ecl_id = 6 AND sclc.ecl_id = 18 AND clc_recursoFederal = 2)
            ' . $wh . '
		GROUP BY gpo_id,ur_id,rct_id,clc_id,cfi_clave,cfi_nombre
		ORDER BY gpo_id,ur_id,rct_id,clc_id
	';

/*echo "<pre>" . $sql . "</pre>";
die();*/
$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar();
$conexion->desconectar();
$row = $conexion->extraer();
$importe = $importeUr = 0;
$contador = 1;
$filas = 0;
$ur = $lote = $lote = $leyenda = $unidad = $urnombre = '';
$bandera = false;
$lote = str_pad($row['rct_dia'], 2, "0", STR_PAD_LEFT) . str_pad($row['lrct_mes'], 2, "0", STR_PAD_LEFT) . $row['lrct_anio'] . $row['lrct_reintegro'] . str_pad($row['lrct_listado_id'], 3, "0", STR_PAD_LEFT);

if ($row['lrct_reintegro'] == 'CR') {
    $leyenda = 'CON REINTEGRO';
} else {
    $leyenda = 'SIN REINTEGRO';
}
$unidad = $row['gpo_id'] . $row['ur_id'];
$cadena_tabla = "";
do {

    if ($unidad != $row['gpo_id'] . $row['ur_id']) {
        echo '<tr>';
        echo '<td colspan="7" align="left" style="font-size: 13px; background-color: #FFFFFF; color: BLACK;"><b>' . $ur . ' .- ' . u8d($urnombre) . '</b></td>';
        echo '<td align="right" style="font-size: 13px; background-color: #FFFFFF; color: BLACK;">
                <b></b></td>';
        echo '<td align="right" style="font-size: 13px; background-color: #FFFFFF; color: BLACK;">
                <b>' . '$' . number_format($importeUr, 2) . '</b></td>';
        echo '</tr>';
        echo $cadena_tabla;
        $importeUr = 0;
        $urnombre = '';
        $ur = '';
        $unidad = $row['gpo_id'] . $row['ur_id'];
        $cadena_tabla = "";
    }


    if ($filas % 2 == 0) {

        $cadena_tabla .= '<tr>';
        $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $contador . '</b></td>';
        $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['rct_id'] . '</b></td>';
        $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['rct_fechaElaboracion'] . '</b></td>';
        $cadena_tabla .= '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . u8d($row['ecl_nombre']) . '</b></td>';
        $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>"' . $row['rct_fechaAutorizacion'] . '"</b></td>';
        $cadena_tabla .= '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . u8d($row['rct_reintegro'] == 1 ? "CON REINTEGRO" : "SIN REINTEGRO") . '</b></td>';
        $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['clc_id'] . '</b></td>';
        $cadena_tabla .= '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . u8d($row['rct_justificacion']) . '</b></td>';
        $cadena_tabla .= '<td align="right" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($row['rct_importe'], 2) . '</b></td>';
        $cadena_tabla .= '</tr>';
    } else {

        $cadena_tabla .= '<tr>';
        $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $contador . '</b></td>';
        $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['rct_id'] . '</b></td>';
        $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['rct_fechaElaboracion'] . '</b></td>';
        $cadena_tabla .= '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b></b>' . u8d($row['ecl_nombre']) . '</td>';
        $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>"' . $row['rct_fechaAutorizacion'] . '"</b></td>';
        $cadena_tabla .= '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . u8d($row['rct_reintegro'] == 1 ? "CON REINTEGRO" : "SIN REINTEGRO") . '</b></td>';
        $cadena_tabla .= '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['clc_id'] . '</b></td>';
        $cadena_tabla .= '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . u8d($row['rct_justificacion']) . '</b></td>';
        $cadena_tabla .= '<td align="right" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($row['rct_importe'], 2) . '</b></td>';
        $cadena_tabla .= '</tr>';
    }

    $contador++;
    $importeUr += $row['rct_importe'];
    $importe += $row['rct_importe'];
    $urnombre = $row['ur_nombre'];
    $ur = $row['gpo_id'] . $row['ur_id'];
    $filas++;
} while ($row = $conexion->extraer());

echo '<tr>';
echo '<br><br>';
echo '<td colspan="7" align="left" style="font-size: 13px; background-color: #FFFFFF; color: BLACK;"><b>' . $ur . ' .- ' . u8d($urnombre) . '</b></td>';
echo '<td align="right" style="font-size: 13px; background-color: #FFFFFF; color: BLACK;"><b>' . '$' . number_format($importeUr, 2) . '</b></td>';
echo '</tr>';

echo $cadena_tabla;

echo '<tr>';
echo '<br><br>';
echo '<td colspan="8" align="right" style="font-size: 15px; background-color: #000000; color: WHITE;"><b>TOTAL GENERAL</b></td>';
echo '<td align="right" style="font-size: 16px; background-color: #000000; color: WHITE;"><b>' . '$' . number_format($importe, 2) . '</b></td>';
echo '</tr>';

$totaldetotales = $importe;
$totaldetotales = number_format($totaldetotales, 2, ".", "");
$dec = explode(".", $totaldetotales);

//FIN DEL CUERPO DE LA TABLA
echo '</table>';
