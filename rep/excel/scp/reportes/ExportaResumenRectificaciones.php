<?php

session_start();
set_time_limit(0);
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/claveFinRecursoFederal.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';

$ruta = utf8Decode('ResumenRectificacion.xls');
header('Content-type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename=' . $ruta);
header('Pragma: no-cache');
header('Expires: 0');

$ur_ent = '';
$nivel = $_SESSION[_S]['nvj_id'];

if ($_SESSION[_S]['nvj_id'] <= 2) {
    $gpo = $_SESSION[_S]['gpo_id'];
    $ur = $_SESSION[_S]['ur_id'];
    $ur_ent = $gpo . $ur;
} else {
    $ur = $_SESSION[_S]['clasAdm-ur'];
    $gpo = $_SESSION[_S]['clasAdm-gpo'];
    $ur_ent = $gpo . $ur;
}

$inicio = DateToQuotedMySQLDate($_POST['inicio']);
$termino = DateToQuotedMySQLDate($_POST['termino']);
$tcl_id = '';
$parametros = [];

if (isset($_POST['tipo']) && $_POST['tipo'] !== '' && $_POST['tipo'] !== '0') {
    $tcl_id = 'AND tcl_id = :tlc_id';
    $parametros += [
        'tlc_id' => $_POST['tipo'],
    ];
} elseif (isset($_POST['SelectAll']) && $_POST['SelectAll'] !== '') {
    $tcl_id = '';
}

$fechas = '';

if ($inicio != '' and $termino != '') {
    $fechas = ' AND rct_fechaElaboracion BETWEEN ' . $inicio . ' AND ' . $termino . '';
} else {
    $fechas = '';
}

$miconexion = new AccesoDatos();
$miconexion->conectar();
$miconexion->setSql('
    SELECT
        ur_nombre
    FROM cat_UnidadResponsable
    WHERE ur_clave = :ur_clave');
$params = [
    'ur_clave' => $ur_ent,
];
$miconexion->consultar($params);
$miconexion->desconectar();
while ($row = $miconexion->extraer()) {
    $nom_ur = $row['ur_nombre'];
}

echo '<table>';

if ($nivel > 2) {
    echo '<tr>'; //SE EMPIEZA A FORMAR EL ENCABEZADO
    echo '<td COLSPAN="10" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:18px;" ALIGN="center"><b>GOBIERNO DEL ESTADO DE OAXACA</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td COLSPAN="10" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:16px;" ALIGN="center"><b>SECRETARIA DE FINANZAS - DIRECCION DE PRESUPUESTO ' . APP_EJERCICIO . '</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td COLSPAN="10" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:14px;" ALIGN="center"><b>LISTADO DE RECTIFICACIONES DE LOS MESES ' . $inicio . ' AL  ' . $termino . '</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td COLSPAN="10" style="border-color: #FFFFFF; border-width:1px; border-style: none" ALIGN="center"><b>' . $gpo . $ur . ' -' . utf8Decode($nom_ur) . '</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td ALIGN="center" style="border-color: #FFFFFF; border-width:0px; border-style: solid"></td>';
    echo '</tr>';
} else {
    echo '<tr>'; //SE EMPIEZA A FORMAR EL ENCABEZADO
    echo '<td COLSPAN="8" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:18px;" ALIGN="center"><b>GOBIERNO DEL ESTADO DE OAXACA</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td COLSPAN="8" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:16px;" ALIGN="center"><b>SECRETARIA DE FINANZAS - DIRECCION DE EGRESOS ' . APP_EJERCICIO . '</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td COLSPAN="8" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:14px;" ALIGN="center"><b>LISTADO DE RECTIFICACIONES DE LA FECHA' . $inicio . ' A LA  ' . $termino . ' </b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td COLSPAN="8" style="border-color: #FFFFFF; border-width:1px; border-style: none" ALIGN="center"><b>' . $gpo . $ur . ' -' . utf8Decode($nom_ur) . '</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td ALIGN="center" style="border-color: #FFFFFF; border-width:0px; border-style: solid"></td>';
    echo '</tr>';
}

//ESTE EXPORTAR SOLO ES PARA LOS DE NIVEL 8

if ($nivel > 2) {
    echo '<tr>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>' . utf8Decode('RECTIFICACIÓN') . '</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>CLC</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>' . utf8Decode('TIPO RECTIFICACIÓN') . '</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>' . utf8Decode('FECHA ELABORACIÓN') . '</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>TIPO DE CLC</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>' . utf8Decode('JUSTIFICACIÓN') . '</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>ESTADO</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>' . utf8Decode('FECHA DE APLICACIÓN') . '</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>IMPORTE</b></td>';
    echo '</tr>';
} else {
    echo '<tr>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>' . utf8Decode('RECTIFICACIÓN') . '</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>CLC</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>' . utf8Decode('TIPO RECTIFICACIÓN') . '</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>' . utf8Decode('FECHA ELABORACIÓN') . '</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>TIPO DE CLC</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>' . utf8Decode('JUSTIFICACIÓN') . '</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>' . utf8Decode('FECHA DE APLICACIÓN') . '</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>IMPORTE </b></td>';
    echo '</tr>';
}

if ($nivel > 2) {
    $federales = ' AND ((
        (gpo_id,ur_id) NOT IN ((3,15),(8,02)) AND NOT (tcl_id="A" AND stc_id=2) AND
        ecf_id NOT IN (' . claveFinRecursoFederal() . ') AND date(clc_fechaElaboracion)>="2015-06-01"
        )OR (gpo_id,ur_id) IN ((3,15),(8,02)) OR (tcl_id="A" AND stc_id=2) )
    ';
} else {
    $federales = '';
}

//SE EMPIEZA A ARMAR EL CUERPO
$sql = '
    SELECT
        gpo_id,
        ur_id,
        rct_id,
        clc_id,
        rct_justificacion,
        DATE_FORMAT( rct_fechaAutorizacion,"%d/%m/%Y") AS rct_fechaAutorizacion,
        DATE_FORMAT( rct_fechaElaboracion,"%d/%m/%Y") AS rct_fechaElaboracion ,
        rct_percepcion AS percepciones,
        rct_retencion AS  retenciones,
        rct_percepcion-rct_retencion AS total,
        tcl_nombre,
        CASE
            WHEN a.ecl_id = 1 THEN "GENERADA POR LA UR"
        WHEN a.ecl_id = 2 THEN "AUTORIZADA POR LA UR"
            WHEN a.ecl_id = 3 THEN "ENVIADA A FINANZAS"
            WHEN a.ecl_id = 4 THEN "RECIBIDA POR FINANZAS"
            WHEN a.ecl_id = 5 THEN "REVISADA POR FINANZAS"
            WHEN a.ecl_id = 6 AND b.ecl_id=18 AND b.clc_recursoFederal = 1 THEN "APLICADA POR LA UR "
            WHEN a.ecl_id = 6 AND b.ecl_id=18 AND b.clc_recursoFederal = 2 THEN "APLICADA POR FINANZAS"
        END AS ecl_nombre,
        if(rct_reintegro = 1,
            "CON REINTEGRO",
            "SIN REINTEGRO") AS reintegro
    FROM scp_MaestroRectificacion a
        INNER JOIN cat_EstadoClc USING(ecl_id)
        INNER JOIN scp_Clc b USING(gpo_id,ur_id,clc_id)
        INNER JOIN enc_FiltroRectificacion f USING (gpo_id,ur_id,tcl_id,stc_id)
        INNER JOIN cat_TipoClc USING(tcl_id)
    WHERE gpo_id = :gpo
        AND ur_id = :ur
        ' . $tcl_id . '
        ' . $fechas . '
    GROUP BY gpo_id,ur_id,rct_id
    ORDER BY gpo_id,ur_id,rct_id
';

$parametros += [
    'gpo' => $gpo,
    'ur'  => $ur,
];
$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar($parametros);
$conexion->desconectar();

$filas = 0;
while ($row = $conexion->extraer()) {
    if ($nivel > 2) {
        if ($filas % 2 == 0) {
            echo '<tr>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['rct_id'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['clc_id'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['reintegro'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['rct_fechaElaboracion'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['tcl_nombre']) . '</b></td>';
            echo '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['rct_justificacion']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['ecl_nombre']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['rct_fechaAutorizacion']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($row['total'], 2) . '</b></td>';
            echo '</tr>';
        } else {
            echo '<tr>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['rct_id'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['clc_id'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['reintegro'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['rct_fechaElaboracion'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['tcl_nombre']) . '</b></td>';
            echo '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['rct_justificacion']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['ecl_nombre']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['rct_fechaAutorizacion'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($row['total'], 2) . '</b></td>';
            echo '</tr>';
        }
    } else {
        if ($filas % 2 == 0) {
            echo '<tr>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['rct_id'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['clc_id'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['reintegro'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['rct_fechaElaboracion'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['tcl_nombre']) . '</b></td>';
            echo '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['rct_justificacion']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['rct_fechaAutorizacion']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($row['total'], 2) . '</b></td>';
            echo '</tr>';
        } else {
            echo '<tr>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['rct_id'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['clc_id'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['reintegro'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['rct_fechaElaboracion'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['tcl_nombre']) . '</b></td>';
            echo '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['rct_justificacion']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['rct_fechaAutorizacion']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($row['total'], 2) . '</b></td>';
            echo '</tr>';
        }
    }
    $filas++;
}

//FIN DEL CUERPO DE LA TABLA
echo '</table>';
?>

<?php

function fechan($fechavieja)
{
    $d = substr($fechavieja, 8, 2);
    $m = substr($fechavieja, 5, 2);
    $a = substr($fechavieja, 0, 4);
    return $d . '/' . $m . '/' . $a;
}

function MySQLDateToDate($MySQLFecha)
{
    if (($MySQLFecha == '') or ($MySQLFecha == '0000-00-00')) {
        return '';
    } else {
        return date('d/m/Y', strtotime($MySQLFecha));
    }
}

function DateToQuotedMySQLDate($Fecha)
{
    if ($Fecha != '') {
        $trozos = explode('/', $Fecha, 3);
        return "'" . $trozos[2] . '-' . $trozos[1] . '-' . $trozos[0] . "'";
    } else {
        return 'NULL';
    }
}

?>
