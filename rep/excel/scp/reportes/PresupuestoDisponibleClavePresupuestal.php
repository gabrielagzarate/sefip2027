<?php
session_start();

require_once('../../../../lib/AccesoDatos.php');
require_once('../../../lib/conversionNumLetras.php');
require_once('../../../../lib/AccesoDatos.php');
require_once('../../../../lib/helpers.php');
require_once('../../../../ent/UnidadResponsable.php');

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
function ur($ur)
{
    echo ('	<tr>
                <td style="background-color:#58ACFA; font-size:18px;" colspan="20">UE ' . utf8decode($ur) . '</td>
            </tr>
        ');
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
            </tr>');
}
function Oa($oa_id, $oa_nombre)
{
    echo ('	<tr>
               <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                <td style="backgorund-color:#58ACFA; font-size:14px;" colspan="16">' . utf8decode($oa_id . ' - ' . $oa_nombre) . '</td>
            </tr>');
}
function Idpartida($idp_id, $idp_nombre)
{
    echo utf8decode('<tr>
                        <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                        <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                        <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                        <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                        <td style="background-color:#F6E3CE; font-size:12px; font-weight:bold" colspan="16">' . ($idp_id . ' - ' . $idp_nombre) . '</td>
                    </tr>');
}
function clave($cfi_id, $cfi_nombre)
{
    echo utf8decode('<tr>
                        <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                        <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                        <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                        <td style="backgorund-color:#58ACFA; font-size:14px;">&nbsp;</td>
                        <td style="background-color:#F6E3CE; font-size:12px; font-weight:bold" colspan="16">' . ($cfi_id . ' - ' . $cfi_nombre) . '</td>
                    </tr>');
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
            JOIN presupuestoEjercido USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
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
            WHERE gpo_id IS NOT NULL ';

if (!isset($_POST['check'])) {
    $sql .= ' AND pre_vigente  > ? ';
    array_push($params, 0);
}

if ($_SESSION[_S]['nvj_id'] < 3) {
    $sql .= ' AND gpo_id = ? and ur_id = ?';
    array_push($params, $gpo_id, $ur_id);
} else {
    if (isset($_POST['gpour']) && $_POST['gpour'] != "") {
        $gpo_id = substr($_POST['gpour'], 0, 1);
        $ur_id  = substr($_POST['gpour'], 1, 2);
        $sql .= ' AND gpo_id = ? and ur_id =?';
        array_push($params, $gpo_id, $ur_id);
    } else {
        if (!isset($_POST['rep_id'])) {
            $sql .= ' AND gpo_id  > ? ';
            array_push($params, 0);
        } else {
            $sql .= ' AND gpo_id = ? and ur_id = ?';
            array_push($params, $gpo_id, $ur_id);
        }
    }
}

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
if (isset($_POST['suborigen'])     && $_POST['suborigen'] <> "") {
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
    $sql .= ' AND pg_id = ?';
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
    array_push($params, $_POST['compra']);
}

$sql .= ' ORDER BY gpo_id,ur_id,presupuesto.ue_id,prg_id,spr_id,py_id,oa_id,ecf_id,idp_id,pg_id,pe_id';


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
echo '<tr>';
echo '<td colspan=20 align="CENTER" ><b>SUBSECRETARIA DE EGRESOS, CONTABILIDAD Y TESORERÍA</td>';
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

$MesesUE  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
$MesesUEE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

$MesesPR  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
$MesesPRE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

$MesesSPR  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
$MesesSPRE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

$MesesPY  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
$MesesPYE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

$MesesOA  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
$MesesOAE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

$MesesIDP  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
$MesesIDPE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

$MesesPG  =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
$MesesPGE =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

$MesesT   =     array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
$MesesTE  =     array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
$MesesTDif  =     array('dif' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);


if ($row) {
    /* variables  de cambios */
    $ue_id = $row['ue_id'];
    $prg_id = $row['prg_id'];
    $spr_id = $row['spr_id'];
    $py_id = $row['py_id'];
    $oa_id = $row['oa_id'];
    $idp_id = $row['idp_id'];
    $idp_nombre = $row['idp_nombre'];
    $pg_id = $row['pg_id'];
    $ur  = $row['gpo_id'] . $row['ur_id'] . '-' . $row['ur_nombre'];

    /*  fin variables  */
    if ($_SESSION[_S]['nvj_id'] > 2)
    ur($ur);
    ue($row['ue_id'], $row['ue_nombre']);
    Programa($row['prg_id'], $row['prg_nombre']);
    subPrograma($row['spr_id'], $row['spr_nombre']);
    Proyecto($row['py_id'], $row['py_nombre']);
    Oa($row['oa_id'], $row['oa_nombre']);
    clave($row['cfi_clave'], 'SUBORIGEN DEL RECURSO: ' . $row['sor_nombre']);
    Idpartida($row['idp_id'], $row['idp_nombre']);
    cabecera();

    do {
        if ($ur <> $row['gpo_id'] . $row['ur_id'] . '-' . $row['ur_nombre']) {
            totales('TOTAL ' . $idp_nombre, $MesesIDP);
            totales('TOTAL EJERCIDO ' . $idp_nombre, $MesesIDPE);
            totalesdif('TOTAL POR EJERCER ' . $idp_nombre, $MesesIDP, $MesesIDPE, 'IP');

            if ($totoa) {
                totales('TOTAL OBRA/ACTIVIDAD', $MesesOA);
                totales('TOTAL EJERCIDO OBRA/ACTIVIDAD', $MesesOAE);
                totalesdif('TOTAL POR EJERCER OBRA/ACTIVIDAD', $MesesOA, $MesesOAE, 'OA');
            }
            if ($totpy) {
                totales('TOTAL PROYECTO', $MesesPY);
                totales('TOTAL EJERCIDO PROYECTO', $MesesPYE);
                totalesdif('TOTAL POR EJERCER PROYECTO', $MesesPY, $MesesPYE, 'PY');
            }
            if ($totspr) {
                totales('TOTAL SUBPROGRAMA', $MesesSPR);
                totales('TOTAL EJERCIDO SUBPROGRAMA', $MesesSPRE);
                totalesdif('TOTAL POR EJERCER SUBPROGRAMA', $MesesSPR, $MesesSPRE, 'SP');
            }
            if ($totprg) {
                totales('TOTAL PROGRAMA', $MesesPR);
                totales('TOTAL EJERCIDO PROGRAMA', $MesesPRE);
                totalesdif('TOTAL POR EJERCER PROGRAMA', $MesesPR, $MesesPRE, 'PR');
            }
            if ($totue) {
                totales('TOTAL UNIDAD EJECUTORA', $MesesUE);
                totales('TOTAL EJERCIDO UNIDAD EJECUTORA', $MesesUEE);
                totalesdif('TOTAL POR EJERCER UNIDAD EJECUTORA', $MesesUE, $MesesUEE, 'UE');
            }
            totales('TOTAL UR', $MesesT, 'UR');
            totales('TOTAL DE LA UR EJERCIDO', $MesesTE, 'UR');
            totales('TOTAL DE LA POR EJERCER', $MesesTDif, 'UR');

            $ur  = $row['gpo_id'] . $row['ur_id'] . '-' . $row['ur_nombre'];

            ur($ur);
            ue($row['ue_id'], $row['ue_nombre']);
            Programa($row['prg_id'], $row['prg_nombre']);
            subPrograma($row['spr_id'], $row['spr_nombre']);
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
            $MesesT = 0;
            $MesesTE = 0;

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

        if ($ue_id <> $row['ue_id']) {
            totales('TOTAL ' . $idp_nombre, $MesesIDP);
            totales('TOTAL EJERCIDO ' . $idp_nombre, $MesesIDPE);
            totalesdif('TOTAL POR EJERCER ' . $idp_nombre, $MesesIDP, $MesesIDPE, 'IP');

            if ($totoa) {
                totales('TOTAL OBRA/ACTIVIDAD', $MesesOA);
                totales('TOTAL EJERCIDO OBRA/ACTIVIDAD', $MesesOAE);
                totalesdif('TOTAL POR EJERCER OBRA/ACTIVIDAD', $MesesOA, $MesesOAE, 'OA');
            }
            if ($totpy) {
                totales('TOTAL PROYECTO', $MesesPY);
                totales('TOTAL EJERCIDO PROYECTO', $MesesPYE);
                totalesdif('TOTAL POR EJERCER PROYECTO', $MesesPY, $MesesPYE, 'PY');
            }
            if ($totspr) {
                totales('TOTAL SUBPROGRAMA', $MesesSPR);
                totales('TOTAL EJERCIDO SUBPROGRAMA', $MesesSPRE);
                totalesdif('TOTAL POR EJERCER SUBPROGRAMA', $MesesSPR, $MesesSPRE, 'SP');
            }
            if ($totprg) {
                totales('TOTAL PROGRAMA', $MesesPR);
                totales('TOTAL EJERCIDO PROGRAMA', $MesesPRE);
                totalesdif('TOTAL POR EJERCER PROGRAMA', $MesesPR, $MesesPRE, 'PR');
            }
            if ($totue) {
                totales('TOTAL UNIDAD EJECUTORA', $MesesUE);
                totales('TOTAL EJERCIDO UNIDAD EJECUTORA', $MesesUEE);
                totalesdif('TOTAL POR EJERCER UNIDAD EJECUTORA', $MesesUE, $MesesUEE, 'UE');
            }

            ue($row['ue_id'], $row['ue_nombre']);
            Programa($row['prg_id'], $row['prg_nombre']);
            subPrograma($row['spr_id'], $row['spr_nombre']);
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
            totales('TOTAL EJERCIDO ' . $idp_nombre, $MesesIDPE);
            totalesdif('TOTAL POR EJERCER ' . $idp_nombre, $MesesIDP, $MesesIDPE, 'IP');

            if ($totoa) {
                totales('TOTAL OBRA/ACTIVIDAD', $MesesOA);
                totales('TOTAL EJERCIDO OBRA/ACTIVIDAD', $MesesOAE);
                totalesdif('TOTAL POR EJERCER OBRA/ACTIVIDAD', $MesesOA, $MesesOAE, 'OA');
            }
            if ($totpy) {
                totales('TOTAL PROYECTO', $MesesPY);
                totales('TOTAL EJERCIDO PROYECTO', $MesesPYE);
                totalesdif('TOTAL POR EJERCER PROYECTO', $MesesPY, $MesesPYE, 'PY');
            }
            if ($totspr) {
                totales('TOTAL SUBPROGRAMA', $MesesSPR);
                totales('TOTAL EJERCIDO SUBPROGRAMA', $MesesSPRE);
                totalesdif('TOTAL POR EJERCER SUBPROGRAMA', $MesesSPR, $MesesSPRE, 'SP');
            }
            if ($totprg) {
                totales('TOTAL PROGRAMA', $MesesPR);
                totales('TOTAL EJERCIDO PROGRAMA', $MesesPRE);
                totalesdif('TOTAL POR EJERCER PROGRAMA', $MesesPR, $MesesPRE, 'SP');
            }

            Programa($row['prg_id'], $row['prg_nombre']);
            subPrograma($row['spr_id'], $row['spr_nombre']);
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
            totales('TOTAL EJERCIDO ' . $idp_nombre, $MesesIDPE);
            totalesdif('TOTAL POR EJERCER ' . $idp_nombre, $MesesIDP, $MesesIDPE, 'IP');

            if ($totoa) {
                totales('TOTAL OBRA/ACTIVIDAD', $MesesOA);
                totales('TOTAL EJERCIDO OBRA/ACTIVIDAD', $MesesOAE);
                totalesdif('TOTAL POR EJERCER OBRA/ACTIVIDAD', $MesesOA, $MesesOAE, 'OA');
            }
            if ($totpy) {
                totales('TOTAL PROYECTO', $MesesPY);
                totales('TOTAL EJERCIDO PROYECTO', $MesesPYE);
                totalesdif('TOTAL POR EJERCER PROYECTO', $MesesPY, $MesesPYE, 'PY');
            }
            if ($totspr) {
                totales('TOTAL SUBPROGRAMA', $MesesSPR);
                totales('TOTAL EJERCIDO SUBPROGRAMA', $MesesSPRE);
                totalesdif('TOTAL POR EJERCER SUBPROGRAMA', $MesesSPR, $MesesSPRE, 'SP');
            }

            subPrograma($row['spr_id'], $row['spr_nombre']);
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
            totales('TOTAL EJERCIDO ' . $idp_nombre, $MesesIDPE);
            totalesdif('TOTAL POR EJERCER ' . $idp_nombre, $MesesIDP, $MesesIDPE, 'IP');

            if ($totoa) {
                totales('TOTAL OBRA/ACTIVIDAD', $MesesOA);
                totales('TOTAL EJERCIDO OBRA/ACTIVIDAD', $MesesOAE);
                totalesdif('TOTAL POR EJERCER OBRA/ACTIVIDAD', $MesesOA, $MesesOAE, 'OA');
            }
            if ($totpy) {
                totales('TOTAL PROYECTO', $MesesPY);
                totales('TOTAL EJERCIDO PROYECTO', $MesesPYE);
                totalesdif('TOTAL POR EJERCER PROYECTO', $MesesPY, $MesesPYE, 'OA');
            }

            Proyecto($row['py_id'], $row['py_nombre']);
            Oa($row['oa_id'], $row['oa_nombre']);
            Idpartida($row['idp_id'], $row['idp_nombre']);


            $idp_id = $row['idp_id'];
            $idp_nombre = $row['idp_nombre'];
            $oa_id = $row['oa_id'];
            $py_id = $row['py_id'];
            $celda = 0;

            $MesesIDP  =  array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesIDPE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

            $MesesOA  = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesOAE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

            $MesesPY  = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesPYE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
        }
        if ($oa_id <> $row['oa_id']) {

            totales('TOTAL ' . $idp_nombre, $MesesIDP);
            totales('TOTAL EJERCIDO ' . $idp_nombre, $MesesIDPE);
            totalesdif('TOTAL POR EJERCER ' . $idp_nombre, $MesesIDP, $MesesIDPE, 'IP');

            if ($totoa) {
                totales('TOTAL OBRA/ACTIVIDAD', $MesesOA);
                totales('TOTAL EJERCIDO OBRA/ACTIVIDAD', $MesesOAE);
                totalesdif('TOTAL POR EJERCER OBRA/ACTIVIDAD', $MesesOA, $MesesOAE, 'OA');
            }

            Oa($row['oa_id'], $row['oa_nombre']);
            Idpartida($row['idp_id'], $row['idp_nombre']);


            $idp_id = $row['idp_id'];
            $idp_nombre = $row['idp_nombre'];
            $oa_id = $row['oa_id'];
            $celda = 0;

            $MesesIDP  = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesIDPE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);

            $MesesOA  = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesOAE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
        }
        if ($idp_id <> $row['idp_id']) {

            totales('TOTAL ' . $idp_nombre, $MesesIDP);
            totales('TOTAL EJERCIDO ' . $idp_nombre, $MesesIDPE);
            totalesdif('TOTAL POR EJERCER ' . $idp_nombre, $MesesIDP, $MesesIDPE, 'IP');
            Idpartida($row['idp_id'], $row['idp_nombre']);
            $idp_id = $row['idp_id'];
            $idp_nombre = $row['idp_nombre'];
            $celda = 0;

            $MesesIDP  = array('vig' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
            $MesesIDPE = array('eje' => 0, 'ene' => 0, 'feb' => 0, 'mar' => 0, 'abr' => 0, 'may' => 0, 'jun' => 0, 'jul' => 0, 'ago' => 0, 'sep' => 0, 'oct' => 0, 'nov' => 0, 'dic' => 0,);
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
            '<td style="background-color:' . $color . ';" colspan="5">EJERCIDO</td>' .
            '<td style="background-color:' . $color . ';">&nbsp;</td>' .
            '<td style="background-color:' . $color . ';">&nbsp;</td>' .
            '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['pre_ejercido'], 2)) . '</td>' .
            '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['eje_ene'], 2)) . '</td>' .
            '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['eje_feb'], 2)) . '</td>' .
            '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['eje_mar'], 2)) . '</td>' .
            '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['eje_abr'], 2)) . '</td>' .
            '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['eje_may'], 2)) . '</td>' .
            '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['eje_jun'], 2)) . '</td>' .
            '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['eje_jul'], 2)) . '</td>' .
            '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['eje_ago'], 2)) . '</td>' .
            '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['eje_sep'], 2)) . '</td>' .
            '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['eje_oct'], 2)) . '</td>' .
            '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['eje_nov'], 2)) . '</td>' .
            '<td style="background-color:' . $color . ';">' . utf8decode(number_format($row['eje_dic'], 2)) . '</td>' .
            '</tr>');
            echo ('<tr>' .
            '<td style="background-color:' . $color2 . ';" colspan="5">POR EJERCER</td>' .
            '<td style="background-color:' . $color2 . ';">&nbsp;</td>' .
            '<td style="background-color:' . $color2 . ';">&nbsp;</td>' .
            '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['pre_vigente'] - $row['pre_ejercido']), 2)) . '</td>' .
            '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_ene'] - $row['eje_ene']), 2)) . '</td>' .
            '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_feb'] - $row['eje_feb']), 2)) . '</td>' .
            '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_mar'] - $row['eje_mar']), 2)) . '</td>' .
            '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_abr'] - $row['eje_abr']), 2)) . '</td>' .
            '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_may'] - $row['eje_may']), 2)) . '</td>' .
            '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_jun'] - $row['eje_jun']), 2)) . '</td>' .
            '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_jul'] - $row['eje_jul']), 2)) . '</td>' .
            '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_ago'] - $row['eje_ago']), 2)) . '</td>' .
            '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_sep'] - $row['eje_sep']), 2)) . '</td>' .
            '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_oct'] - $row['eje_oct']), 2)) . '</td>' .
            '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_nov'] - $row['eje_nov']), 2)) . '</td>' .
            '<td style="background-color:' . $color2 . ';">' . utf8decode(number_format(($row['vig_dic'] - $row['eje_dic']), 2)) . '</td>' .
            '</tr>');
        }
        /*  sumas     */ {
            /* unidad ejecutora */
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
            $MesesUEE['eje'] += $row['pre_ejercido'];
            $MesesUEE['ene'] += $row['eje_ene'];
            $MesesUEE['feb'] += $row['eje_feb'];
            $MesesUEE['mar'] += $row['eje_mar'];
            $MesesUEE['abr'] += $row['eje_abr'];
            $MesesUEE['may'] += $row['eje_may'];
            $MesesUEE['jun'] += $row['eje_jun'];
            $MesesUEE['jul'] += $row['eje_jul'];
            $MesesUEE['ago'] += $row['eje_ago'];
            $MesesUEE['sep'] += $row['eje_sep'];
            $MesesUEE['oct'] += $row['eje_oct'];
            $MesesUEE['nov'] += $row['eje_nov'];
            $MesesUEE['dic'] += $row['eje_dic'];
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
            $MesesPRE['eje'] += $row['pre_ejercido'];
            $MesesPRE['ene'] += $row['eje_ene'];
            $MesesPRE['feb'] += $row['eje_feb'];
            $MesesPRE['mar'] += $row['eje_mar'];
            $MesesPRE['abr'] += $row['eje_abr'];
            $MesesPRE['may'] += $row['eje_may'];
            $MesesPRE['jun'] += $row['eje_jun'];
            $MesesPRE['jul'] += $row['eje_jul'];
            $MesesPRE['ago'] += $row['eje_ago'];
            $MesesPRE['sep'] += $row['eje_sep'];
            $MesesPRE['oct'] += $row['eje_oct'];
            $MesesPRE['nov'] += $row['eje_nov'];
            $MesesPRE['dic'] += $row['eje_dic'];
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
            $MesesSPRE['eje'] += $row['pre_ejercido'];
            $MesesSPRE['ene'] += $row['eje_ene'];
            $MesesSPRE['feb'] += $row['eje_feb'];
            $MesesSPRE['mar'] += $row['eje_mar'];
            $MesesSPRE['abr'] += $row['eje_abr'];
            $MesesSPRE['may'] += $row['eje_may'];
            $MesesSPRE['jun'] += $row['eje_jun'];
            $MesesSPRE['jul'] += $row['eje_jul'];
            $MesesSPRE['ago'] += $row['eje_ago'];
            $MesesSPRE['sep'] += $row['eje_sep'];
            $MesesSPRE['oct'] += $row['eje_oct'];
            $MesesSPRE['nov'] += $row['eje_nov'];
            $MesesSPRE['dic'] += $row['eje_dic'];
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
            $MesesPYE['eje'] += $row['pre_ejercido'];
            $MesesPYE['ene'] += $row['eje_ene'];
            $MesesPYE['feb'] += $row['eje_feb'];
            $MesesPYE['mar'] += $row['eje_mar'];
            $MesesPYE['abr'] += $row['eje_abr'];
            $MesesPYE['may'] += $row['eje_may'];
            $MesesPYE['jun'] += $row['eje_jun'];
            $MesesPYE['jul'] += $row['eje_jul'];
            $MesesPYE['ago'] += $row['eje_ago'];
            $MesesPYE['sep'] += $row['eje_sep'];
            $MesesPYE['oct'] += $row['eje_oct'];
            $MesesPYE['nov'] += $row['eje_nov'];
            $MesesPYE['dic'] += $row['eje_dic'];
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
            $MesesOAE['eje'] += $row['pre_ejercido'];
            $MesesOAE['ene'] += $row['eje_ene'];
            $MesesOAE['feb'] += $row['eje_feb'];
            $MesesOAE['mar'] += $row['eje_mar'];
            $MesesOAE['abr'] += $row['eje_abr'];
            $MesesOAE['may'] += $row['eje_may'];
            $MesesOAE['jun'] += $row['eje_jun'];
            $MesesOAE['jul'] += $row['eje_jul'];
            $MesesOAE['ago'] += $row['eje_ago'];
            $MesesOAE['sep'] += $row['eje_sep'];
            $MesesOAE['oct'] += $row['eje_oct'];
            $MesesOAE['nov'] += $row['eje_nov'];
            $MesesOAE['dic'] += $row['eje_dic'];
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
            $MesesIDPE['eje'] += $row['pre_ejercido'];
            $MesesIDPE['ene'] += $row['eje_ene'];
            $MesesIDPE['feb'] += $row['eje_feb'];
            $MesesIDPE['mar'] += $row['eje_mar'];
            $MesesIDPE['abr'] += $row['eje_abr'];
            $MesesIDPE['may'] += $row['eje_may'];
            $MesesIDPE['jun'] += $row['eje_jun'];
            $MesesIDPE['jul'] += $row['eje_jul'];
            $MesesIDPE['ago'] += $row['eje_ago'];
            $MesesIDPE['sep'] += $row['eje_sep'];
            $MesesIDPE['oct'] += $row['eje_oct'];
            $MesesIDPE['nov'] += $row['eje_nov'];
            $MesesIDPE['dic'] += $row['eje_dic'];
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
            $MesesPGE['eje'] += $row['pre_ejercido'];
            $MesesPGE['ene'] += $row['eje_ene'];
            $MesesPGE['feb'] += $row['eje_feb'];
            $MesesPGE['mar'] += $row['eje_mar'];
            $MesesPGE['abr'] += $row['eje_abr'];
            $MesesPGE['may'] += $row['eje_may'];
            $MesesPGE['jun'] += $row['eje_jun'];
            $MesesPGE['jul'] += $row['eje_jul'];
            $MesesPGE['ago'] += $row['eje_ago'];
            $MesesPGE['sep'] += $row['eje_sep'];
            $MesesPGE['oct'] += $row['eje_oct'];
            $MesesPGE['nov'] += $row['eje_nov'];
            $MesesPGE['dic'] += $row['eje_dic'];
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
            $MesesTE['eje'] += $row['pre_ejercido'];
            $MesesTE['ene'] += $row['eje_ene'];
            $MesesTE['feb'] += $row['eje_feb'];
            $MesesTE['mar'] += $row['eje_mar'];
            $MesesTE['abr'] += $row['eje_abr'];
            $MesesTE['may'] += $row['eje_may'];
            $MesesTE['jun'] += $row['eje_jun'];
            $MesesTE['jul'] += $row['eje_jul'];
            $MesesTE['ago'] += $row['eje_ago'];
            $MesesTE['sep'] += $row['eje_sep'];
            $MesesTE['oct'] += $row['eje_oct'];
            $MesesTE['nov'] += $row['eje_nov'];
            $MesesTE['dic'] += $row['eje_dic'];
            /* GENERALES DIFERENCIA */
            $MesesTDif['dif'] += ($row['pre_vigente'] - $row['pre_ejercido']);
            $MesesTDif['ene'] += ($row['vig_ene'] - $row['eje_ene']);
            $MesesTDif['feb'] += ($row['vig_feb'] - $row['eje_feb']);
            $MesesTDif['mar'] += ($row['vig_mar'] - $row['eje_mar']);
            $MesesTDif['abr'] += ($row['vig_abr'] - $row['eje_abr']);
            $MesesTDif['may'] += ($row['vig_may'] - $row['eje_may']);
            $MesesTDif['jun'] += ($row['vig_jun'] - $row['eje_jun']);
            $MesesTDif['jul'] += ($row['vig_jul'] - $row['eje_jul']);
            $MesesTDif['ago'] += ($row['vig_ago'] - $row['eje_ago']);
            $MesesTDif['sep'] += ($row['vig_sep'] - $row['eje_sep']);
            $MesesTDif['oct'] += ($row['vig_oct'] - $row['eje_oct']);
            $MesesTDif['nov'] += ($row['vig_nov'] - $row['eje_nov']);
            $MesesTDif['dic'] += ($row['vig_dic'] - $row['eje_dic']);
        }
    } while ($row = $conexion->extraer());
    totales('TOTAL ' . $idp_nombre, $MesesIDP);
    totales('TOTAL EJERCIDO ' . $idp_nombre, $MesesIDPE);
    totalesdif('TOTAL POR EJERCER ' . $idp_nombre, $MesesIDP, $MesesIDPE, 'IP');

    if ($totoa) {
        totales('TOTAL OBRA/ACTIVIDAD', $MesesOA);
        totales('TOTAL EJERCIDO OBRA/ACTIVIDAD', $MesesOAE);
        totalesdif('TOTAL POR EJERCER OBRA/ACTIVIDAD', $MesesOA, $MesesOAE, 'OA');
    }
    if ($totpy) {
        totales('TOTAL PROYECTO', $MesesPY);
        totales('TOTAL EJERCIDO PROYECTO', $MesesPYE);
        totalesdif('TOTAL POR EJERCER PROYECTO', $MesesPY, $MesesPYE, 'PY');
    }
    if ($totspr) {
        totales('TOTAL SUBPROGRAMA', $MesesSPR);
        totales('TOTAL EJERCIDO SUBPROGRAMA', $MesesSPRE);
        totalesdif('TOTAL POR EJERCER SUBPROGRAMA', $MesesSPR, $MesesSPRE, 'SP');
    }
    if ($totprg) {
        totales('TOTAL PROGRAMA', $MesesPR);
        totales('TOTAL EJERCIDO PROGRAMA', $MesesPRE);
        totalesdif('TOTAL POR EJERCER PROGRAMA', $MesesPR, $MesesPRE, 'PR');
    }
    if ($totue) {
        totales('TOTAL UNIDAD EJECUTORA', $MesesUE);
        totales('TOTAL EJERCIDO UNIDAD EJECUTORA', $MesesUEE);
        totalesdif('TOTAL POR EJERCER UNIDAD EJECUTORA', $MesesUE, $MesesUEE, 'UE');
    }
    if ($totur) {
        totales('TOTAL UR', $MesesT, 'UR');
        totales('TOTAL DE LA UR EJERCIDO', $MesesTE, 'UR');
        totales('TOTAL DE LA POR EJERCER', $MesesTDif, 'UR');
    }
}
echo ' <tr>';
echo '<td colspan="20" align="left" style="font-size: 13px;"><b>Fecha ' . $hoy . ' </b></td>';
echo '</tr>';
echo '</table>';
