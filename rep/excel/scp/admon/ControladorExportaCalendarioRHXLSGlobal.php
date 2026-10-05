<?php
require_once '../../../../lib/AccesoDatos.php';

$conexion = new AccesoDatos();
$ruta = "PresupuestoRH.xls";
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=" . $ruta);
header("Pragma: no-cache");
header("Expires: 0");

$conexion->conectar();
$params = [];
if (!empty($_POST['gpo_id'])) {
    $sql = ' AND gpo_id = ? AND ur_id = ?';
    $params = [$_POST['gpo_id'], $_POST['ur_id']];
} else {
    $sql = 'OR( gpo_id in ( 1,5,6,7,8) and concat(gpo_id,ur_id) not in (105, 115, 116, 530, 531, 540,553 ) )';
}
$sql = '
    SELECT concat(pre_claveCompleta,"-",tcp_id) CLAVE,pe_nombre,pe_id,pre_vigente VIGENTE,pre_comprometido COMPROM, pre_devengado DEVENGADO ,vig_ene ENE,vig_feb FEB,vig_mar MAR,vig_abr ABR,vig_may MAY,vig_jun JUN,vig_jul JUL,vig_ago AGO,vig_sep SEP,vig_oct OCT,vig_nov NOV,vig_dic DIC,DEV_ENE,DEV_FEB,DEV_MAR,DEV_ABR,DEV_MAY,DEV_JUN,DEV_JUL,DEV_AGO,DEV_SEP,DEV_OCT,DEV_NOV,DEV_DIC
    FROM presupuesto
    JOIN presupuestoVigente AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
    JOIN presupuestoComprometido AS com USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
    JOIN presupuestoEjercido AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
    JOIN presupuestoDevengado AS dev USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
    JOIN cat_PartidaEspecifica USING (pg_id,pe_id)
    JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
    WHERE (crh_id = 1 ' . $sql . ' )AND tcp_id != "P" AND /* (idp_id = "A" or pe_id IN (394,395,396,397,398,399,411) ) */ clg_id=1
        ';

$conexion->setSql($sql);
$conexion->consultar($params);
$conexion->desconectar();
echo '<table>';
echo '<tr >';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>CLAVE PRESUPUESTAL</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>PARTIDA ESPECIFICA</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>MODIFICADO</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>DEVENGADO</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>ENE</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>FEB</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>MAR</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>ABR</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>MAY</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>JUN</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>JUL</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>AGO</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>SEP</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>OCT</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>NOV</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>DIC</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>DEV_ENE</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>DEV_FEB</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>DEV_MAR</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>DEV_ABR</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>DEV_MAY</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>DEV_JUN</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>DEV_JUL</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>DEV_AGO</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>DEV_SEP</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>DEV_OCT</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>DEV_NOV</b></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"><b>DEV_DIC</b></td>';

echo '</tr>';
while ($row = $conexion->extraer()) {
    echo '<tr>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['CLAVE'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['pe_id'] . '-' . $row['pe_nombre'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['VIGENTE'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['DEVENGADO'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['ENE'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['FEB'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['MAR'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['ABR'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['MAY'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['JUN'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['JUL'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['AGO'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['SEP'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['OCT'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['NOV'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['DIC'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['DEV_ENE'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['DEV_FEB'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['DEV_MAR'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['DEV_ABR'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['DEV_MAY'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['DEV_JUN'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['DEV_JUL'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['DEV_AGO'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['DEV_SEP'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['DEV_OCT'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['DEV_NOV'] . '</td>';
    echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['DEV_DIC'] . '</td>';
    echo '</tr>';
}
echo '</table>';
