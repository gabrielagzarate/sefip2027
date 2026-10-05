<?php

session_start();
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';

$ruta = utf8Decode('Resumen_Proveedores.xls');
header('Content-type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename=' . $ruta);
header('Pragma: no-cache');
header('Expires: 0');

$hoy = date('Y-m-d H:i:s');

echo'<table align="center" width="100%">';
echo'<tr>';
echo'<td colspan="16" align="CENTER"><b>GOBIERNO DEL ESTADO DE OAXACA</b></td>';
echo'</tr>';

echo'<tr>';
echo'<td colspan="16" align="CENTER" ><b>SECRETARIA DE FINANZAS DEL ESTADO DE OAXACA</td>';
echo'</tr>';

echo'<tr>';
echo'<td colspan="16" align="CENTER" ><b>CONSULTA DE PROVEEDORES <b></td>';
echo'</tr>';

echo'<tr>';
echo'<td colspan="16" align="CENTER" ></td>';
echo'</tr>';

echo' <tr>';
echo'<td align="left"  style="font-size: 12.5; background-color: #003366; color: white;">UR</td>';
echo'<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">RFC</td>';
echo'<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">NOMBRE</td>';
echo'<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">CONCEPTO </td>';
echo'<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">CLAVE PRESUPUESTAL/CONTABLE</td>';
echo'<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">FACTURA/REFERENCIA</td>';
echo'<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">FECHA REFERENCIA</td>';
echo'<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">' . utf8Decode('N° BANCO DOCUMENTOS') . '</td>';
echo'<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">' . utf8Decode('N° CONTROL') . '</td>';
echo'<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">' . utf8Decode('N° AFECTACION') . '</td>';
echo'<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">CLC</td>';
echo'<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">CERE/CERECO</td>';
echo'<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">' . utf8Decode('NÚMERO') . '</td>';
echo'<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">' . utf8Decode('PERCEPCIÓN') . '</td>';
echo'<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">' . utf8Decode('RETENCIÓN') . '</td>';
echo'<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">' . utf8Decode('IMPORTE') . '</td>';

echo'</tr>';//FIN DEL ENCABEZADO DE LA TABLA

$nombre = $rfc = $clc = $facturanp = $facturanc = $factura = $np = $and = $wh = $wf = '';
if ($_SESSION[_S]['nvj_id'] > 2) {
    $wf = 'WHERE  detalle.gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '  and  detalle.ur_id =' . $_SESSION[_S]['clasAdm-ur'] . ' ';
    $wh = 'WHERE  gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . '  and  ur_id =' . $_SESSION[_S]['clasAdm-ur'] . ' ';
} else {
    $wf = 'WHERE  detalle.gpo_id=' . $_SESSION[_S]['gpo_id'] . '  and  detalle.ur_id =' . $_SESSION[_S]['ur_id'] . ' ';
    $wh = 'WHERE  gpo_id=' . $_SESSION[_S]['gpo_id'] . '  and  ur_id =' . $_SESSION[_S]['ur_id'] . ' ';
}

if (isset($_POST['rfc']) and $_POST['rfc'] != '') {
    $rfc = 'and rfc_id = "' . $_POST['rfc'] . '" ';
}

if (isset($_POST['nombre']) and $_POST['nombre'] != '') {
    $np = 'and rfc_nombre like "%' . $_POST['nombre'] . '%" ';
    $nombre = 'and rfc_nombre like "%' . $_POST['nombre'] . '%" ';
}

if (isset($_POST['factura']) and $_POST['factura'] != '') {
    $factura = 'and referencia = "' . $_POST['factura'] . '" ';
}
if (isset($_POST['numeroClc']) and $_POST['numeroClc'] != '') {
    $clc = 'and clc_id=' . $_POST['numeroClc'] . ' ';
}
$sql = '
    SELECT gpo_id,ur_id,afe_id,clc_id,bdo_id,clave,clave_nombre,tipo_Movimiento,tipo_numcontrol,numcontrol,concepto,rfc_id,nombre,
    referencia,fechaReferencia,perBruto,iva_id,percepcion_iva,percepciones,retenciones,total,
    if(clc_tipocereCo=1  ,"CERE",if( clc_tipocereCo=2, "CERECO","SIN CERE(CO)"))cereco,clc_cereCo,programatico
    FROM scp_Clc
    LEFT JOIN scp_ClcAuxiliar using(gpo_id,ur_id,clc_id,ecl_id)
    LEFT JOIN(
    SELECT detalle.gpo_id AS gpo_id,detalle.ur_id AS ur_id,afe_id,clc_id,ncp_id numcontrol,CONCAT(pre_claveCompleta) clave,pe_nombre clave_nombre,
            ncp_referencia referencia,ncp_fechaReferencia fechaReferencia,ncp_percepcionBruto perBruto,ncp_percepcionIva percepcion_iva,
            ncp_percepcionNeto percepciones, 0 retenciones,"+" tipo_Movimiento,rfc_id,CONCAT(rfc_nombre," ",COALESCE(rfc_paterno,"")," ",COALESCE(rfc_materno,"")) AS nombre,
            "P" tipo_numcontrol,iva_id AS iva_id,ncp_concepto AS concepto,rfc_nombre,ABS(sum(ncp_percepcionNeto) - sum(0)) as total,
            CONCAT( detalle.gpo_id, detalle.ur_id, "-",ue_id, "-",prg_id, "-",spr_id, "-",py_id, "-",oa_id ) AS programatico,bdo_id
    FROM scp_Presupuestal detalle
        JOIN presupuesto USING(gpo_id,ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
        JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
        INNER JOIN cat_Proveedor USING(rfc_id)
        INNER JOIN  ' . BD_SINPRES . '.cat_Proveedores using(rfc_id)
        ' . $wf . ' ' . $rfc . '  and clc_id!=0 
    GROUP BY detalle.gpo_id,detalle.ur_id,afe_id,clc_id,ncp_id,ncp_referencia
    UNION
    SELECT detalle.gpo_id AS gpo_id,detalle.ur_id AS ur_id,afe_id,clc_id,ncc_id numcontrol, cta_cuenta clave, cta_nombre clave_nombre,
        ncc_referencia referencia,ncc_fechaReferencia fechaReferencia,0 perBruto, 0 percepcion_iva,
        ncc_percepcion percepciones,ncc_retencion retenciones,
        ncc_tipoMovimiento tipo_Movimiento,rfc_id,CONCAT(rfc_nombre," ",COALESCE(rfc_paterno,"")," ",COALESCE(rfc_materno,"")) AS nombre,
        "C" tipo_numcontrol,0 AS iva_id,ncc_concepto AS concepto,rfc_nombre,ABS(sum(ncc_percepcion) - sum(ncc_retencion)) as total,
        "" programatico,bdo_id
    FROM scp_Contable detalle
        JOIN cg_PlanCuentas USING(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id)
        INNER JOIN cat_Proveedor USING(rfc_id)
        INNER JOIN  ' . BD_SINPRES . '.cat_Proveedores using(rfc_id)
    ' . $wf . ' ' . $rfc . '  and clc_id!=0
    GROUP BY detalle.gpo_id,detalle.ur_id,afe_id,clc_id,ncc_id,ncc_referencia
    )nc using(gpo_id,ur_id,clc_id)
    ' . $wh . ' ' . $rfc . ' ' . $nombre . ' ' . $factura . ' ' . $clc . ' and clc_id!=0 and ecl_id!=20 and ecl_id!=21
    GROUP BY gpo_id,ur_id,afe_id,clc_id,numcontrol,rfc_id,referencia,nc.bdo_id,nc.clave,nc.clave_nombre,nc.tipo_Movimiento, nc.tipo_numcontrol,nc.concepto,nc.nombre,nc.fechaReferencia,nc.perBruto,nc.iva_id,nc.percepcion_iva,nc.percepciones,nc.retenciones, nc.total,nc.programatico;
';
$miconexion = new AccesoDatos();
$miconexion->conectar();
$miconexion->setSql($sql);
$miconexion->consultar();
$miconexion->desconectar();

$ur = $programa = $idp = $pe = '';
$banur = $banprograma = $banidp = $banpe = false;
$filas = 0;
while ($row = $miconexion->extraer()) {//SE EMPIEZA A ARMAR EL CUERPO

    if ($filas % 2 == 0) {
        echo' <tr>';
        echo'<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['gpo_id'] . '' . $row['ur_id'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['rfc_id'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['nombre'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . utf8Decode($row['concepto']) . '</b></td>';
        echo'<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['clave'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['referencia'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['fechaReferencia'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['bdo_id'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['numcontrol'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['afe_id'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['clc_id'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['cereco'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['clc_cereCo'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . number_format($row['percepciones'], 2) . '</b></td>';
        echo'<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . number_format($row['retenciones'], 2) . '</b></td>';
        echo'<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . number_format($row['total'], 2) . '</b></td>';
        echo'</tr>';
    } else {

        echo' <tr>';
        echo'<td align="left" style="font-size: 10.5;"><b>' . $row['gpo_id'] . '' . $row['ur_id'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5;"><b>' . $row['rfc_id'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5;"><b>' . $row['nombre'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5;"><b>' . utf8Decode($row['concepto']) . '</b></td>';
        echo'<td align="left" style="font-size: 10.5;"><b>' . $row['clave'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5;"><b>' . $row['referencia'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5;"><b>' . $row['fechaReferencia'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5;"><b>' . $row['bdo_id'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5;"><b>' . $row['numcontrol'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5;"><b>' . $row['afe_id'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5;"><b>' . $row['clc_id'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5;"><b>' . $row['cereco'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5;"><b>' . $row['clc_cereCo'] . '</b></td>';
        echo'<td align="left" style="font-size: 10.5;"><b>' . number_format($row['percepciones'], 2) . '</b></td>';
        echo'<td align="left" style="font-size: 10.5;"><b>' . number_format($row['retenciones'], 2) . '</b></td>';
        echo'<td align="left" style="font-size: 10.5;"><b>' . number_format($row['total'], 2) . '</b></td>';
        echo'</tr>';
    }
    $filas++;
}

echo'<tr>';
echo'<td colspan="10" align="left" style="font-size: 10px;"></td>';
echo'<td colspan="10" align="left" style="font-size: 10px;"></td>';
echo'</tr>';

echo'<tr>';
echo'<td colspan="10" align="left" style="font-size: 14px;"><b>Fecha ' . $hoy . ' </b></td>';
echo'</tr>';

echo'</table>';
