<?php
session_start();

require_once('../../../../lib/AccesoDatos.php');
require_once('../../../../lib/helpers.php');
require_once('../../../../lib/constantes.php');
require_once('../../../../ent/UnidadResponsable.php');
require_once('../../../lib/conversionNumLetras.php');

function cabecera()
{
    echo ('<thead style="background-color:#000; border-bottom:1px solid #999; color:#FFF;">
                <td style="color:#FFF;background-color:#000; width:px" colspan="5">CLAVE PRESUPUESTAL</td>
                <td style="color:#FFF;background-color:#000; width:px">TC</td>
                <td style="color:#FFF;background-color:#000; width:px">PARTIDA E.</td>
                <td style="color:#FFF;background-color:#000; width:px">VIGENTE</td>
                <td style="color:#FFF;background-color:#000; width:px">ENE</td>
                <td style="color:#FFF;background-color:#000; width:px">FEB</td>
                <td style="color:#FFF;background-color:#000; width:px">MAR</td>
                <td style="color:#FFF;background-color:#000; width:px">ABR</td>
                <td style="color:#FFF;background-color:#000; width:px">MAY</td>
                <td style="color:#FFF;background-color:#000; width:px">JUN</td>
                <td style="color:#FFF;background-color:#000; width:px">JUL</td>
                <td style="color:#FFF;background-color:#000; width:px">AGO</td>
                <td style="color:#FFF;background-color:#000; width:px">SEP</td>
                <td style="color:#FFF;background-color:#000; width:px">OCT</td>
                <td style="color:#FFF;background-color:#000; width:px">NOV</td>
                <td style="color:#FFF;background-color:#000; width:px">DIC</td>
            </thead>');
}
function totales($mensaje, $totales, $tipo = "")
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

    $color = "";
    switch ($tipo) {
        case 'PA':
            $color = "color:#000;background-color:#F6E3CE;";
            break;
        case 'UE':
            $color = "color:#000;background-color:#EAEAEA;";
            break;
        case 'PR':
            $color = "color:#000;background-color:#DDD;";
            break;
        case 'SP':
            $color = "color:#000;background-color:#DDD;";
            break;
        case 'PY':
            $color = "color:#000;background-color:#DDD;";
            break;
        case 'OA':
            $color = "color:#000;background-color:#DDD;";
            break;
        case 'UR':
            $color = "color:#FFF;background-color:#000; font-size:16px;";
            break;
    }
    echo ('<tr style="background-color:#000; border-bottom:1px solid #999; color:#FFF;">
                <td style="' . $color . ' " colspan="7">' . utf8decode($mensaje) . '</td>
                <td style="' . $color . ' ">$' . number_format($total, 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['ene'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['feb'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['mar'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['abr'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['may'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['jun'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['jul'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['ago'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['sep'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['oct'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['nov'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['dic'], 2) . '</td>
            </tr>');
}
function totalesdif($mensaje, $totales, $totalesEje, $tipo = "")
{
    $color = "";
    switch ($tipo) {
        case 'IP':
            $color = "color:#000;background-color:#F6E3CE;";
            break;
        case 'PA':
            $color = "color:#000;background-color:#F6E3CE;";
            break;
        case 'UE':
            $color = "color:#000;background-color:#EAEAEA;";
            break;
        case 'PR':
            $color = "color:#000;background-color:#DDD;";
            break;
        case 'SP':
            $color = "color:#000;background-color:#DDD;";
            break;
        case 'PY':
            $color = "color:#000;background-color:#DDD;";
            break;
        case 'OA':
            $color = "color:#000;background-color:#DDD;";
            break;
        case 'UR':
            $color = "color:#FFF;background-color:#000; font-size:16px;";
            break;
    }
    echo ('<tr style="background-color:#000; border-bottom:1px solid #999; color:#FFF;">
                <td style="' . $color . ' " colspan="7">' . utf8decode($mensaje) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['vig'] - $totalesEje['eje'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['ene'] - $totalesEje['ene'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['feb'] - $totalesEje['feb'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['mar'] - $totalesEje['mar'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['abr'] - $totalesEje['abr'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['may'] - $totalesEje['may'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['jun'] - $totalesEje['jun'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['jul'] - $totalesEje['jul'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['ago'] - $totalesEje['ago'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['sep'] - $totalesEje['sep'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['oct'] - $totalesEje['oct'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['nov'] - $totalesEje['nov'], 2) . '</td>
                <td style="' . $color . ' ">$' . number_format($totales['dic'] - $totalesEje['dic'], 2) . '</td>
                
            </tr>');
}
function ue($ue_id, $ue_nombre)
{
    echo ('	<tr>
                    <td style="background-color:#58ACFA; font-size:18px;" colspan="20">UE ' . utf8decode($ue_id . ' - ' . $ue_nombre) . '</td>
                </tr>
        ');
}
function Programa($prg_id, $prg_nombre)
{
    echo ('	<tr>
                    <td style="backgorund-color:#58ACFA; font-weight:bold;">&nbsp;</td>
                    <td style="backgorund-color:#58ACFA font-weight:bold;" colspan="19">PR ' . utf8decode($prg_id . ' - ' . $prg_nombre) . '</td>
                </tr>
        ');
}
function subPrograma($spr_id, $spr_nombre)
{
    echo ('	<tr>
                    <td style="backgorund-color:#58ACFA;">&nbsp;</td>
                    <td style="backgorund-color:#58ACFA;">&nbsp;</td>
                    <td style="backgorund-color:#58ACFA " colspan="18">SPR ' . utf8decode($spr_id . ' - ' . $spr_nombre) . '</td>
                </tr>
        ');
}
function Proyecto($py_id, $py_nombre)
{
    echo ('	<tr>
                    <td style="backgorund-color:#58ACFA; font-size:14px; font-weight:bold">&nbsp;</td>
                    <td style="backgorund-color:#58ACFA; font-size:14px; font-weight:bold">&nbsp;</td>
                    <td style="backgorund-color:#58ACFA; font-size:14px; font-weight:bold">&nbsp;</td>
                    <td style="backgorund-color:#58ACFA; font-size:14px; font-weight:bold" colspan="17">PY ' . utf8decode($py_id . ' - ' . $py_nombre) . '</td>
                </tr>
        ');
}
function Oa($oa_id, $oa_nombre)
{
    echo ('	<tr>
                    <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                    <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                    <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                    <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                    <td style="backgorund-color:#58ACFA; font-size:14px;" colspan="16">' . utf8decode($oa_id . ' - ' . $oa_nombre) . '</td>
                </tr>
        ');
}
function Idpartida($idp_id, $idp_nombre)
{
    echo utf8decode('	<tr>
                    <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                    <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                    <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                    <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                    <td style="background-color:#F6E3CE; font-size:12px; font-weight:bold" colspan="16">' . ($idp_id . ' - ' . $idp_nombre) . '</td>
                </tr>
        ');
}
function clave($cfi_id, $cfi_nombre)
{
    echo utf8decode('	<tr>
                    <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                    <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                    <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                    <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                    <td style="background-color:#F6E3CE; font-size:12px; font-weight:bold" colspan="16">' . ($cfi_id . ' - ' . $cfi_nombre) . '</td>
                </tr>
        ');
}
$ruta = utf8decode("PresupuestoCalendarizadoDisponiblePorClavePresupuestalUR" . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . ".xls");
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=" . $ruta);
header("Pragma: no-cache");
header("Expires: 0");
$hoy = date("Y-m-d H:i:s");

$gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
$ur_id  = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];


$totue = $totprg = $totspr = $totpy = $totoa = true;
$totur = true;
$params = [];
$sql = 'SELECT * FROM presupuesto
            JOIN presupuestoVigente USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            JOIN presupuestoComprometido USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            JOIN cat_UnidadEjecutora USING(gpo_id,ur_id,ue_id)
            JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
            JOIN cat_Subprograma USING(gpo_id,ur_id,prg_id,spr_id)
            JOIN cat_Proyecto USING(gpo_id,ur_id,prg_id,spr_id,py_id)
            JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,/*cso_id,*/cfi_id,cfi_anio)
            JOIN sinpres.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id)
            JOIN cat_PartidaGenerica USING(pg_id)
            JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
            JOIN cat_IdentificadorPartida USING(idp_id)
            WHERE gpo_id = ? and ur_id = ? AND pre_vigente > ?';
$params = [$gpo_id, $ur_id, 0];
if (isset($_POST['oa']) && $_POST['oa'] <> "") {
    $sql .= ' AND oa_id = ?';
    $totue = $totprg = $totspr = $totpy = $totoa = true;
    $totue = $totprg = $totspr = $totpy = false;
    $totur = false;
    array_push($params, $_POST['oa']);
}
if (isset($_POST['py']) && $_POST['py'] <> "") {
    $sql .= ' AND py_id = ?';
    $totue = $totprg = $totspr = $totpy = $totoa = true;
    $totue = $totprg = $totspr = false;
    $totur  = false;
    array_push($params, $_POST['py']);
}
if (isset($_POST['subprograma']) && $_POST['subprograma'] <> "") {
    $sql .= ' AND spr_id = ?';
    $totue = $totprg = $totspr = $totpy = $totoa = true;
    $totue = $totprg = false;
    $totur = false;
    array_push($params, $_POST['subprograma']);
}
if (isset($_POST['programa']) && $_POST['programa'] <> "") {
    $sql .= ' AND prg_id = ?';
    $totue = $totprg = $totspr = $totpy = $totoa = true;
    $totue = false;
    $totur = false;
    array_push($params, $_POST['programa']);
}
if (isset($_POST['unidad']) && $_POST['unidad'] <> "") {
    $sql .= ' AND presupuesto.ue_id = ?';
    $totue = $totprg = $totspr = $totpy = $totoa = true;
    $totur = false;
    array_push($params, $_POST['unidad']);
}

/* CLAVE DE FINANCIAMIENTO */
if (isset($_POST['fuente']) && $_POST['fuente'] <> "") {
    $sql .= ' AND fte_id = ?';
    $totur = false;
    array_push($params, $_POST['fuente']);
}
if (isset($_POST['subfuente']) && $_POST['subfuente'] <> "") {
    $sql .= ' AND sft_id = ?';
    $totur = false;
    array_push($params, $_POST['subfuente']);
}
if (isset($_POST['origen']) && $_POST['origen'] <> "") {
    $sql .= ' AND ori_id = ?';
    $totur = false;
    array_push($params, $_POST['origen']);
}
if (isset($_POST['suborigen']) && $_POST['suborigen'] <> "") {
    $sql .= ' AND sor_id = ?';
    $totur = false;
    array_push($params, $_POST['suborigen']);
}
if (isset($_POST['clasificacion']) && $_POST['clasificacion'] <> "") {
    $sql .= ' AND cff_id = ?';
    $totur = false;
    array_push($params, $_POST['clasificacion']);
}
if (isset($_POST['referencia']) && $_POST['referencia'] <> "") {
    $sql .= ' AND cfi_id = ?';
    $totur = false;
    array_push($params, $_POST['referencia']);
}
if (isset($_POST['cfi_anio']) && $_POST['cfi_anio'] <> "") {
    $sql .= ' AND cfi_anio = ?';
    $totur = false;
    array_push($params, $_POST['cfi_anio']);
}
if (isset($_POST['generica']) && $_POST['generica'] <> "") {
    $sql .= ' AND pg_id= ?';
    $totur = false;
    $totue = $totprg = $totspr = $totpy = $totoa = false;
    array_push($params, $_POST['generica']);
}
if (isset($_POST['especifica']) && $_POST['especifica'] <> "") {
    $sql .= ' AND pe_id = ?';
    $totur = false;
    $totue = $totprg = $totspr = $totpy = $totoa = false;
    array_push($params, $_POST['especifica']);
}
if (isset($_POST['compra']) && $_POST['compra'] <> "") {
    $sql .= ' AND tcp_id = ?';
    $totur = false;
    array_push($params, $_POST['especifica']);
}

$sql .= ' ORDER BY prg_id,spr_id,py_id,oa_id,ecf_id,idp_id,pg_id,pe_id';

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar($params);
$conexion->desconectar();
$row = $conexion->extraer();

echo '<table align="center" width="100%">';

/* ANCHOS DE LA TABLA */
echo '<tr>';
echo '<td style="width:40px;">&nbsp;</td>	<td style="width:40px;">&nbsp;</td>		<td style="width:40px;">&nbsp;</td>		<td style="width:40px;">&nbsp;</td>';
echo '<td style="width:80px;">&nbsp;</td>	<td style="width:80px;">&nbsp;</td>		<td style="width:290px;">&nbsp;</td>	<td style="width:100px;">&nbsp;</td>';
echo '<td style="width:100px;">&nbsp;</td>	<td style="width:100px;">&nbsp;</td>	<td style="width:100px;">&nbsp;</td>	<td style="width:100px;">&nbsp;</td>	<td style="width:100px;">&nbsp;</td>	<td style="width:100px;">&nbsp;</td>';
echo '<td style="width:100px;">&nbsp;</td>	<td style="width:100px;">&nbsp;</td>	<td style="width:100px;">&nbsp;</td>	<td style="width:100px;">&nbsp;</td>	<td style="width:100px;">&nbsp;</td>	<td style="width:100px;">&nbsp;</td>';
echo '</tr>';
/* ANCHOS DE LA TABLA */

echo '<tr>';
echo '<td colspan=20 align="CENTER"><b>GOBIERNO DEL ESTADO DE OAXACA</b></td>';
echo '</tr>';
// echo'<tr>';
// echo'<td colspan=20 align="CENTER" ><b>PROGRAMA OPERATIVO ANUAL 2016</td>';
// echo'</tr>';
echo '<tr>';
echo '<td colspan=20 align="CENTER" ><b>SUBSECRETARÍA DE EGRESOS, DE CONTABILIDAD Y TESOREIA</td>';
echo '</tr>';
echo '<tr>';
echo utf8decode('<td colspan="20" align="left" ><b>PRESUPUESTO CALENDARIZADO POR CLAVE PRESUPUESTAL</td>');
echo '</tr>';
echo '<tr><td colspan="20" align="left" ><b>UNIDAD RESPONSABLE: ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '</td></tr>';
echo '<tr>';
echo '<td colspan="20" align="left" style="font-size: 10px;"></td>';
echo '</tr>';

/* variables  de montos*/
$celda = 0;
$totalVigenteUR = 0;

$MesesUE  = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
$MesesUEE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

$MesesPR  = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
$MesesPRE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

$MesesSPR  = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
$MesesSPRE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

$MesesPY  = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
$MesesPYE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

$MesesOA  = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
$MesesOAE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

$MesesIDP  = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
$MesesIDPE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

$MesesPG  = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
$MesesPGE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

$MesesT   = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
$MesesTE  = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
$MesesTDif  = array('dif' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);



/* variables  de cambios */
if ($row) {
    $ue_id = $row['ue_id'];
    $prg_id = $row['prg_id'];
    $spr_id = $row['spr_id'];
    $py_id = $row['py_id'];
    $oa_id = $row['oa_id'];
    $idp_id = $row['idp_id'];
    $idp_nombre = $row['idp_nombre'];
    $pg_id = $row['pg_id'];

    /*  fin variables  */
    ue($row['ue_id'], $row['ue_nombre']);
    Programa($row['prg_id'], $row['prg_nombre']);
    subPrograma($row['spr_id'], $row['spr_nombre']);
    Proyecto($row['py_id'], $row['py_nombre']);
    Oa($row['oa_id'], $row['oa_nombre']);
    clave($row['cfi_clave'], 'SUBORIGEN DEL RECURSO: ' . $row['sor_nombre']);
    Idpartida($row['idp_id'], $row['idp_nombre']);
    cabecera();

    do {
        if ($ue_id <> $row['ue_id']
        ) {
            totales('TOTAL ' . $idp_nombre, $MesesIDP);
            totales('TOTAL COMPROMETIDO ' . $idp_nombre, $MesesIDPE);
            totalesdif('TOTAL POR COMROMETER ' . $idp_nombre, $MesesIDP, $MesesIDPE, 'IP');

            if ($totoa) {
                totales('TOTAL OBRA/ACTIVIDAD', $MesesOA);
                totales('TOTAL COMPROMETIDO OBRA/ACTIVIDAD', $MesesOAE);
                totalesdif('TOTAL POR COMROMETER OBRA/ACTIVIDAD', $MesesOA, $MesesOAE, 'OA');
            }
            if ($totpy) {
                totales('TOTAL PROYECTO', $MesesPY);
                totales('TOTAL COMPROMETIDO PROYECTO', $MesesPYE);
                totalesdif('TOTAL POR COMROMETER PROYECTO', $MesesPY, $MesesPYE, 'PY');
            }
            if ($totspr) {
                totales('TOTAL SUBPROGRAMA', $MesesSPR);
                totales('TOTAL COMPROMETIDO SUBPROGRAMA', $MesesSPRE);
                totalesdif('TOTAL POR COMROMETER SUBPROGRAMA', $MesesSPR, $MesesSPRE, 'SP');
            }
            if ($totprg) {
                totales('TOTAL PROGRAMA', $MesesPR);
                totales('TOTAL COMPROMETIDO PROGRAMA', $MesesPRE);
                totalesdif('TOTAL POR COMROMETER PROGRAMA', $MesesPR, $MesesPRE, 'PR');
            }
            if ($totue) {
                totales('TOTAL UNIDAD EJECUTORA', $MesesUE);
                totales('TOTAL COMPROMETIDO UNIDAD EJECUTORA', $MesesUEE);
                totalesdif('TOTAL POR COMROMETER UNIDAD EJECUTORA', $MesesUE, $MesesUEE, 'UE');
            }

            ue($row['ue_id'], $row['ue_nombre']);
            Programa($row['prg_id'], $row['prg_nombre']);
            subPrograma($row['spr_id'],
                $row['spr_nombre']
            );
            Proyecto($row['py_id'], $row['py_nombre']);
            Oa($row['oa_id'], $row['oa_nombre']);
            Idpartida($row['idp_id'], $row['idp_nombre']);


            $idp_id = $row['idp_id'];
            $idp_nombre = $row['idp_nombre'];
            $oa_id = $row['oa_id'];
            $py_id = $row['py_id'];
            $spr_id = $row['spr_id'];
            $prg_id = $row['prg_id'];
            $ue_id = $row['ue_id'];
            $celda = 0;

            $MesesIDP  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesIDPE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

            $MesesOA  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesOAE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

            $MesesPY  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesPYE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

            $MesesSPR  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesSPRE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

            $MesesPR  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesPRE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

            $MesesUE  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesUEE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
        }
        if ($prg_id <> $row['prg_id']) {
            totales('TOTAL ' . $idp_nombre, $MesesIDP);
            totales('TOTAL COMPROMETIDO ' . $idp_nombre, $MesesIDPE);
            totalesdif('TOTAL POR COMROMETER ' . $idp_nombre, $MesesIDP, $MesesIDPE, 'IP');

            if ($totoa) {
                totales('TOTAL OBRA/ACTIVIDAD', $MesesOA);
                totales('TOTAL COMPROMETIDO OBRA/ACTIVIDAD', $MesesOAE);
                totalesdif('TOTAL POR COMROMETER OBRA/ACTIVIDAD', $MesesOA, $MesesOAE, 'OA');
            }
            if ($totpy) {
                totales('TOTAL PROYECTO', $MesesPY);
                totales('TOTAL COMPROMETIDO PROYECTO', $MesesPYE);
                totalesdif('TOTAL POR COMROMETER PROYECTO', $MesesPY, $MesesPYE, 'PY');
            }
            if ($totspr) {
                totales('TOTAL SUBPROGRAMA', $MesesSPR);
                totales('TOTAL COMPROMETIDO SUBPROGRAMA', $MesesSPRE);
                totalesdif('TOTAL POR COMROMETER SUBPROGRAMA', $MesesSPR, $MesesSPRE, 'SP');
            }
            if ($totprg) {
                totales('TOTAL PROGRAMA', $MesesPR);
                totales('TOTAL COMPROMETIDO PROGRAMA', $MesesPRE);
                totalesdif('TOTAL POR COMROMETER PROGRAMA', $MesesPR, $MesesPRE, 'SP');
            }

            Programa($row['prg_id'], $row['prg_nombre']);
            subPrograma($row['spr_id'],
                $row['spr_nombre']
            );
            Proyecto($row['py_id'], $row['py_nombre']);
            Oa($row['oa_id'], $row['oa_nombre']);
            Idpartida($row['idp_id'], $row['idp_nombre']);


            $idp_id = $row['idp_id'];
            $idp_nombre = $row['idp_nombre'];
            $oa_id = $row['oa_id'];
            $py_id = $row['py_id'];
            $spr_id = $row['spr_id'];
            $prg_id = $row['prg_id'];
            $celda = 0;

            $MesesIDP  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesIDPE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

            $MesesOA  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesOAE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

            $MesesPY  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesPYE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

            $MesesSPR  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesSPRE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

            $MesesPR  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesPRE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
        }
        if ($spr_id <> $row['spr_id']) {
            totales('TOTAL ' . $idp_nombre, $MesesIDP);
            totales('TOTAL COMPROMETIDO ' . $idp_nombre, $MesesIDPE);
            totalesdif('TOTAL POR COMROMETER ' . $idp_nombre, $MesesIDP, $MesesIDPE, 'IP');

            if ($totoa) {
                totales('TOTAL OBRA/ACTIVIDAD', $MesesOA);
                totales('TOTAL COMPROMETIDO OBRA/ACTIVIDAD', $MesesOAE);
                totalesdif('TOTAL POR COMROMETER OBRA/ACTIVIDAD', $MesesOA, $MesesOAE, 'OA');
            }
            if ($totpy) {
                totales('TOTAL PROYECTO', $MesesPY);
                totales('TOTAL COMPROMETIDO PROYECTO', $MesesPYE);
                totalesdif('TOTAL POR COMROMETER PROYECTO', $MesesPY, $MesesPYE, 'PY');
            }
            if ($totspr) {
                totales('TOTAL SUBPROGRAMA', $MesesSPR);
                totales('TOTAL COMPROMETIDO SUBPROGRAMA', $MesesSPRE);
                totalesdif('TOTAL POR COMROMETER SUBPROGRAMA', $MesesSPR, $MesesSPRE, 'SP');
            }

            subPrograma($row['spr_id'],
                $row['spr_nombre']
            );
            Proyecto($row['py_id'], $row['py_nombre']);
            Oa($row['oa_id'], $row['oa_nombre']);
            Idpartida($row['idp_id'], $row['idp_nombre']);


            $idp_id = $row['idp_id'];
            $idp_nombre = $row['idp_nombre'];
            $oa_id = $row['oa_id'];
            $py_id = $row['py_id'];
            $spr_id = $row['spr_id'];
            $celda = 0;

            $MesesIDP  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesIDPE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

            $MesesOA  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesOAE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

            $MesesPY  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesPYE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

            $MesesSPR  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesSPRE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
        }
        if ($py_id <> $row['py_id']) {

            totales('TOTAL ' . $idp_nombre, $MesesIDP);
            totales('TOTAL COMPROMETIDO ' . $idp_nombre, $MesesIDPE);
            totalesdif('TOTAL POR COMROMETER ' . $idp_nombre, $MesesIDP, $MesesIDPE, 'IP');

            if ($totoa) {
                totales('TOTAL OBRA/ACTIVIDAD', $MesesOA);
                totales('TOTAL COMPROMETIDO OBRA/ACTIVIDAD', $MesesOAE);
                totalesdif('TOTAL POR COMROMETER OBRA/ACTIVIDAD', $MesesOA, $MesesOAE, 'OA');
            }
            if ($totpy) {
                totales('TOTAL PROYECTO', $MesesPY);
                totales('TOTAL COMPROMETIDO PROYECTO', $MesesPYE);
                totalesdif('TOTAL POR COMROMETER PROYECTO', $MesesPY, $MesesPYE, 'OA');
            }

            Proyecto($row['py_id'], $row['py_nombre']);
            Oa($row['oa_id'], $row['oa_nombre']);
            Idpartida($row['idp_id'], $row['idp_nombre']);


            $idp_id = $row['idp_id'];
            $idp_nombre = $row['idp_nombre'];
            $oa_id = $row['oa_id'];
            $py_id = $row['py_id'];
            $celda = 0;

            $MesesIDP  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesIDPE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

            $MesesOA  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesOAE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

            $MesesPY  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesPYE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
        }
        if ($oa_id <> $row['oa_id']) {

            totales('TOTAL ' . $idp_nombre, $MesesIDP);
            totales('TOTAL COMPROMETIDO ' . $idp_nombre, $MesesIDPE);
            totalesdif('TOTAL POR COMROMETER ' . $idp_nombre, $MesesIDP, $MesesIDPE, 'IP');

            if ($totoa) {
                totales('TOTAL OBRA/ACTIVIDAD', $MesesOA);
                totales('TOTAL COMPROMETIDO OBRA/ACTIVIDAD', $MesesOAE);
                totalesdif('TOTAL POR COMROMETER OBRA/ACTIVIDAD', $MesesOA, $MesesOAE, 'OA');
            }

            Oa($row['oa_id'], $row['oa_nombre']);
            Idpartida($row['idp_id'], $row['idp_nombre']);


            $idp_id = $row['idp_id'];
            $idp_nombre = $row['idp_nombre'];
            $oa_id = $row['oa_id'];
            $celda = 0;

            $MesesIDP  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesIDPE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

            $MesesOA  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesOAE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
        }
        if ($idp_id <> $row['idp_id']) {

            totales('TOTAL ' . $idp_nombre, $MesesIDP);
            totales('TOTAL COMPROMETIDO ' . $idp_nombre, $MesesIDPE);
            totalesdif('TOTAL POR COMPROMETER ' . $idp_nombre, $MesesIDP, $MesesIDPE, 'IP');

            Idpartida($row['idp_id'],
                $row['idp_nombre']
            );


            $idp_id = $row['idp_id'];
            $idp_nombre = $row['idp_nombre'];
            $celda = 0;

            $MesesIDP  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesIDPE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
        }

        /*  Registro     */ {
            $color = "#FFF";
            if ($celda == 0) {
                $color = "#FFF";
                $celda = 1;
            } else {
                $color = "#EAEAEA";
                $celda = 0;
            }
            $color2 = "#999";
            echo ('<tr>' .
                '<td style="background-color:' . $color . ';" colspan="5">' . utf8decode($row['pre_claveCompleta']) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode($row['tcp_id']) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode($row['pe_id'] . '-' . $row['pe_nombre']) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['pre_vigente'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['vig_ene'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['vig_feb'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['vig_mar'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['vig_abr'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['vig_may'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['vig_jun'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['vig_jul'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['vig_ago'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['vig_sep'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['vig_oct'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['vig_nov'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['vig_dic'], 2)) . '</td>' .
                '</tr>');
            echo ('<tr>' .
                '<td style="background-color:' . $color . ';" colspan="5">COMPROMETIDO</td>' .
                '<td style="background-color:' . $color . ';">&nbsp;</td>' .
                '<td style="background-color:' . $color . ';">&nbsp;</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['pre_comprometido'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['com_ene'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['com_feb'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['com_mar'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['com_abr'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['com_may'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['com_jun'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['com_jul'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['com_ago'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['com_sep'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['com_oct'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['com_nov'], 2)) . '</td>' .
                '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['com_dic'], 2)) . '</td>' .
                '</tr>');
            echo ('<tr>' .
                '<td style="background-color:' . $color2 . ';" colspan="5">POR COMPROMETER</td>' .
                '<td style="background-color:' . $color2 . ';">&nbsp;</td>' .
                '<td style="background-color:' . $color2 . ';">&nbsp;</td>' .
                '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['pre_vigente'] - $row['pre_comprometido']), 2)) . '</td>' .
                '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_ene'] - $row['com_ene']), 2)) . '</td>' .
                '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_feb'] - $row['com_feb']), 2)) . '</td>' .
                '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_mar'] - $row['com_mar']), 2)) . '</td>' .
                '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_abr'] - $row['com_abr']), 2)) . '</td>' .
                '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_may'] - $row['com_may']), 2)) . '</td>' .
                '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_jun'] - $row['com_jun']), 2)) . '</td>' .
                '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_jul'] - $row['com_jul']), 2)) . '</td>' .
                '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_ago'] - $row['com_ago']), 2)) . '</td>' .
                '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_sep'] - $row['com_sep']), 2)) . '</td>' .
                '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_oct'] - $row['com_oct']), 2)) . '</td>' .
                '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_nov'] - $row['com_nov']), 2)) . '</td>' .
                '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_dic'] - $row['com_dic']), 2)) . '</td>' .
                '</tr>');
        }
        /*  sumas     */ {
            /* unidad comcutora */
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
        }
    } while ($row = $conexion->extraer());
    totales('TOTAL ' . $idp_nombre, $MesesIDP);
    totales('TOTAL COMPROMETIDO ' . $idp_nombre, $MesesIDPE);
    totalesdif('TOTAL POR COMROMETER ' . $idp_nombre, $MesesIDP, $MesesIDPE, 'IP');

    if ($totoa) {
        totales('TOTAL OBRA/ACTIVIDAD',
            $MesesOA
        );
        totales('TOTAL COMPROMETIDO OBRA/ACTIVIDAD', $MesesOAE);
        totalesdif('TOTAL POR COMROMETER OBRA/ACTIVIDAD', $MesesOA, $MesesOAE, 'OA');
    }
    if ($totpy) {
        totales('TOTAL PROYECTO', $MesesPY);
        totales('TOTAL COMPROMETIDO PROYECTO', $MesesPYE);
        totalesdif('TOTAL POR COMROMETER PROYECTO', $MesesPY, $MesesPYE, 'PY');
    }
    if ($totspr) {
        totales('TOTAL SUBPROGRAMA', $MesesSPR);
        totales('TOTAL COMPROMETIDO SUBPROGRAMA', $MesesSPRE);
        totalesdif('TOTAL POR COMROMETER SUBPROGRAMA', $MesesSPR, $MesesSPRE, 'SP');
    }
    if ($totprg) {
        totales('TOTAL PROGRAMA', $MesesPR);
        totales('TOTAL COMPROMETIDO PROGRAMA', $MesesPRE);
        totalesdif('TOTAL POR COMROMETER PROGRAMA', $MesesPR, $MesesPRE, 'PR');
    }
    if ($totue) {
        totales('TOTAL UNIDAD EJECUTORA', $MesesUE);
        totales('TOTAL COMPROMETIDO UNIDAD EJECUTORA', $MesesUEE);
        totalesdif('TOTAL POR COMROMETER UNIDAD EJECUTORA', $MesesUE, $MesesUEE, 'UE');
    }
    if ($totur) {
        totales('TOTAL UR', $MesesT, 'UR');
        totales('TOTAL DE LA UR COMPROMETIDO', $MesesTE, 'UR');
        totales('TOTAL DE LA POR COMROMETER', $MesesTDif, 'UR');
    }
}
echo ' <tr>';
echo '<td colspan="20" align="left" style="font-size: 13px;"><b>Fecha ' . $hoy . ' </b></td>';
echo '</tr>';
echo '</table>';
