<?php
ini_set('display_errors',1);
require_once dirname(__FILE__) . "/../../../../lib/helpers.php";


session_start();
require_once('../../../../lib/AccesoDatos.php');

$rct_id = $_POST['rct_id'];

$ruta = utf8Decode('ReportePagadoras.xls');
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=" . $ruta);
header("Pragma: no-cache");
header("Expires: 0");

$conexion = new AccesoDatos();
$conexion->conectar();

$sql = '';
$params = [];
if ($_SESSION[_S]['nvj_id'] > 2 || $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == '118') {
    $ur = 'gpo_id = :gpo_id AND ur_id = :ur_id ';
    $params[':gpo_id'] = $_POST['gpo_id'];
    $params[':ur_id'] = $_POST['ur_id'];
} else {
    $ur = 'gpo_id = :gpo_id AND ur_id = :ur_id ';
    $params[':gpo_id'] = $_SESSION[_S]['gpo_id'] ;
    $params[':ur_id'] = $_SESSION[_S]['ur_id'];
}
$conexion->setSql('
    SELECT gpo_origen, ur_origen, tcl_origen, afe_origen, clc_origen, ncc_origen, percepcion_origen, retencion_origen,
        importe_origen, importeRectificacion_origen, gpo_pagadora, ur_pagadora, tcl_pagadora, afe_pagadora, clc_pagadora,
        ncc_pagadora, ncc_nccamortiza, percepcion_pagadora, retencion_pagadora, importe_pagadora, importeRectificacion_pagadora
    FROM(
        SELECT gpo_id gpo_origen, ur_id ur_origen, tcl_id tcl_origen, afe_id afe_origen, clc_id clc_origen,
            ncc_id ncc_origen, co.ncc_percepcion AS percepcion_origen, co.ncc_retencion AS retencion_origen,
            IF(co.ncc_tipoMovimiento = "+", co.ncc_percepcion, co.ncc_retencion) AS importe_origen,
            SUM(IF(rco.ncc_tipoMovimiento = "+", rco.ncc_percepcion, rco.ncc_retencion)) AS importeRectificacion_origen
        FROM scp_Contable co
            INNER JOIN scp_Afectacion USING(gpo_id,ur_id,afe_id,clc_id)
            LEFT JOIN scp_RectificacionContable rco USING(gpo_id,ur_id,afe_id,clc_id,ncc_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,ncc_tipoMovimiento)
        WHERE ' . $_POST['origen'] . '
            ' . $ur . ' 
            AND (
                (ncc_amortizado = 1 AND (ncc_nccamortiza = 0 OR ISNULL(ncc_nccamortiza))) 
                OR (ger_id = 2 AND cgp_id = 1 AND rub_id = 1 
                    AND (
                        (tcl_id = "F" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "-")
                        OR (tcl_id = "F" AND cgcu_id = 7 AND cgsc_id = 9 AND ncc_tipomovimiento = "-")
                        OR (tcl_id = "G" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "-")
                        OR (tcl_id = "H" AND cgcu_id = 9 AND cgsc_id = 4 AND ncc_tipomovimiento = "+")
                        OR (tcl_id = "D" AND cgcu_id = 9 AND cgsc_id = 2 AND ncc_tipomovimiento = "+")
                        OR (tcl_id = "E" AND cgcu_id = 9 AND cgsc_id = 1 AND ncc_tipomovimiento = "+")
                    )
                )
            ) AND eaf_id != "C"
        GROUP BY gpo_id, ur_id,clc_id,afe_id, ncc_id
    )origen
        INNER JOIN(
            SELECT gpo_id gpo_pagadora, ur_id ur_pagadora, tcl_id tcl_pagadora, afe_id afe_pagadora, clc_id clc_pagadora,
                ncc_id ncc_pagadora, ncc_nccamortiza, co.ncc_percepcion percepcion_pagadora, co.ncc_retencion retencion_pagadora,
                CASE 
                    co.ncc_tipoMovimiento
                    WHEN 
                        "+" 
                    THEN 
                        if(isnull(co.ncc_percepcion), 0, co.ncc_percepcion)
                    WHEN 
                        "-" 
                    THEN 
                        if(isnull(co.ncc_retencion),0,co.ncc_retencion)
                END AS importe_pagadora, 
                SUM(
                    CASE rco.ncc_tipoMovimiento
                        WHEN 
                            "+" 
                        THEN 
                            if( isnull(rco.ncc_percepcion),0,rco.ncc_percepcion)
                        WHEN 
                            "-" 
                        THEN 
                            if( isnull(rco.ncc_retencion),0,rco.ncc_retencion)
                        else 0
                    END) importeRectificacion_pagadora
            FROM scp_Contable co
                INNER JOIN scp_Afectacion USING(gpo_id,ur_id,afe_id,clc_id)
                LEFT JOIN (
                    SELECT 
                        gpo_id,ur_id,afe_id,clc_id,ncc_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,ncc_tipoMovimiento,ncc_percepcion,ncc_retencion
                    FROM scp_MaestroRectificacion
                        INNER JOIN scp_RectificacionContable USING(gpo_id,ur_id,rct_id,clc_id)
                    WHERE ecl_id = 6 AND ' . $ur . '
                ) rco USING(gpo_id,ur_id,afe_id,clc_id,ncc_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,ncc_tipoMovimiento)
            WHERE ' . $_POST['pagadora'] . '
                (
                    (ncc_amortizado = 2 AND (ncc_nccamortiza != 0 AND NOT ISNULL(ncc_nccamortiza)))
                    OR( ger_id = 2 AND cgp_id = 1 AND rub_id = 1 
                        AND(
                            (tcl_id = "H" AND cgcu_id = 9 AND cgsc_id = 4 AND ncc_tipomovimiento = "-")
                            OR (tcl_id = "D" AND cgcu_id = 9 AND cgsc_id = 2 AND ncc_tipomovimiento = "-")
                            OR (tcl_id = "E" AND cgcu_id = 9 AND cgsc_id = 1 AND ncc_tipomovimiento = "-")
                            OR (tcl_id = "F" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "+")
                            OR (tcl_id = "G" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "+")
                        )
                    )
                ) AND eaf_id != "C"
            GROUP BY gpo_id, ur_id,clc_id,afe_id, ncc_id
        )pagadora ON(ncc_nccamortiza = ncc_origen)
    ;
');

$conexion->consultar($params);
$conexion->desconectar();


$conexion2 = new AccesoDatos();
$conexion2->conectar();
$conexion2->setSql(' 
    SELECT 
        count(ncc_origen) AS numero,ncc_origen 
    FROM(
        SELECT gpo_id gpo_origen,
            ur_id ur_origen,
            tcl_id tcl_origen,
            afe_id afe_origen,
            clc_id clc_origen,
            ncc_id ncc_origen,
            co.ncc_percepcion percepcion_origen,
            co.ncc_retencion retencion_origen,
            IF(co.ncc_tipoMovimiento = "+", co.ncc_percepcion, co.ncc_retencion) importe_origen,
            SUM(IF( rco.ncc_tipoMovimiento = "+", rco.ncc_percepcion, rco.ncc_retencion)) importeRectificacion_origen
        FROM scp_Contable co
            INNER JOIN scp_Afectacion USING(gpo_id,ur_id,afe_id,clc_id)
            LEFT JOIN scp_RectificacionContable rco USING(gpo_id,ur_id,afe_id,clc_id,ncc_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,ncc_tipoMovimiento)
        WHERE ' . $_POST['origen'] . '
            ' . $ur . ' 
            AND (
                (ncc_amortizado = 1 AND (ncc_nccamortiza = 0 OR ISNULL(ncc_nccamortiza))) 
                OR ( ger_id = 2 AND cgp_id = 1 AND rub_id = 1 
                    AND (
                        (tcl_id = "F" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "-")
                        OR (tcl_id = "F" AND cgcu_id = 7 AND cgsc_id = 9 AND ncc_tipomovimiento = "-")
                        OR (tcl_id = "G" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "-")
                        OR (tcl_id = "H" AND cgcu_id = 9 AND cgsc_id = 4 AND ncc_tipomovimiento = "+")
                        OR (tcl_id = "D" AND cgcu_id = 9 AND cgsc_id = 2 AND ncc_tipomovimiento = "+")
                        OR (tcl_id = "E" AND cgcu_id = 9 AND cgsc_id = 1 AND ncc_tipomovimiento = "+")
                    )
                )
            ) AND eaf_id != "C"
        GROUP BY gpo_id, ur_id,clc_id,afe_id, ncc_id
    )origen
        INNER JOIN(
            SELECT gpo_id gpo_pagadora,
                ur_id ur_pagadora,
                tcl_id tcl_pagadora,
                afe_id afe_pagadora,
                clc_id clc_pagadora,
                ncc_id ncc_pagadora,
                ncc_nccamortiza,
                co.ncc_percepcion percepcion_pagadora,
                co.ncc_retencion retencion_pagadora,
                CASE co.ncc_tipoMovimiento
                    WHEN 
                        "+" 
                    THEN 
                        if( isnull(co.ncc_percepcion), 0, co.ncc_percepcion )
                    WHEN 
                        "-" 
                    THEN 
                        if( isnull(co.ncc_retencion),0,co.ncc_retencion)
                END as importe_pagadora, 
                SUM(
                    CASE rco.ncc_tipoMovimiento
                        WHEN 
                            "+" 
                        THEN 
                            if( isnull(rco.ncc_percepcion),0,rco.ncc_percepcion)
                        WHEN 
                            "-" 
                        THEN 
                            if( isnull(rco.ncc_retencion),0,rco.ncc_retencion)
                        else 0
                    END) importeRectificacion_pagadora
            FROM scp_Contable co
                INNER JOIN scp_Afectacion USING(gpo_id,ur_id,afe_id,clc_id)
                LEFT JOIN (
                    SELECT 
                        gpo_id,ur_id,afe_id,clc_id,ncc_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,ncc_tipoMovimiento,ncc_percepcion,ncc_retencion
                    FROM scp_MaestroRectificacion
                    INNER JOIN scp_RectificacionContable USING(gpo_id,ur_id,rct_id,clc_id)
                    WHERE ecl_id = 6 AND ' . $ur . '
                ) rco USING(gpo_id,ur_id,afe_id,clc_id,ncc_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,ncc_tipoMovimiento)
            WHERE ' . $_POST['pagadora'] . '
                (
                    (ncc_amortizado = 2 AND (ncc_nccamortiza != 0 AND NOT ISNULL(ncc_nccamortiza)))
                    OR (ger_id = 2 AND cgp_id = 1 AND rub_id = 1 
                            AND (
                            (tcl_id = "H" AND cgcu_id = 9 AND cgsc_id = 4 AND ncc_tipomovimiento = "-")
                            OR (tcl_id = "D" AND cgcu_id = 9 AND cgsc_id = 2 AND ncc_tipomovimiento = "-")
                            OR (tcl_id = "E" AND cgcu_id = 9 AND cgsc_id = 1 AND ncc_tipomovimiento = "-")
                            OR (tcl_id = "F" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "+")
                            OR (tcl_id = "G" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "+")
                            )
                    )
                ) AND eaf_id != "C"
            GROUP BY gpo_id, ur_id,clc_id,afe_id, ncc_id
        )pagadora ON(ncc_nccamortiza = ncc_origen)	
    GROUP by ncc_origen;
');
$conexion2->consultar($params);
$conexion2->desconectar();
$dat = $conexion2->extraer();
$pinta = true;
$bolcontrol = false; 
$boldetalle = false; 
$varcontrol = ''; 
$html = ''; 
$html2 = '';
$html3 = '';
$netrectificado = 0; 
$porRectificar = 0; 
$acum = 0;
$netoPagadora = 0; 
$netPagador = 0;
$header='<table class = "table table-bordered class="warning"">
    <thead>
        <tr>
            <th colspan="8" style="background-color:#dff0d8; border: 1px solid #c1d2ba;">CLC de ORIGEN</th>
        </tr>
        <tr class="warning">
            <th style="background-color:#dff0d8; border: 1px solid #c1d2ba;">CLC</th>
            <th style="background-color:#dff0d8; border: 1px solid #c1d2ba;">Tipo</th>
            <th style="background-color:#dff0d8; border: 1px solid #c1d2ba;">Afectacion</th>
            <th style="background-color:#dff0d8; border: 1px solid #c1d2ba;">Num. Control</th>
            <th style="background-color:#dff0d8; border: 1px solid #c1d2ba;">' . utf8Decode('Percepción') . '</th>
            <th style="background-color:#dff0d8; border: 1px solid #c1d2ba;">' . utf8Decode('Retención') . '</th>
            <th style="background-color:#dff0d8; border: 1px solid #c1d2ba;">' . utf8Decode('Rectificado') . '</th>
            <th style="background-color:#dff0d8; border: 1px solid #c1d2ba;">Original - Rectificacion</th>
        </tr>
    </thead>
';

$header2='<table class = "table table-bordered class="warning"">
    <thead>
        <tr>
            <th colspan="9" style="background-color:#fcf8e3; border: 1px solid #e8e4cf;">CLC PAGADORA</th>
        </tr>
        <tr>
            <th style="background-color:#fcf8e3; border: 1px solid #e8e4cf;">UR</th>
            <th style="background-color:#fcf8e3; border: 1px solid #e8e4cf;">CLC</th>
            <th style="background-color:#fcf8e3; border: 1px solid #e8e4cf;">Tipo</th>
            <th style="background-color:#fcf8e3; border: 1px solid #e8e4cf;">Afectacion</th>
            <th style="background-color:#fcf8e3; border: 1px solid #e8e4cf;">Num. Control</th>
            <th style="background-color:#fcf8e3; border: 1px solid #e8e4cf;">' . utf8Decode('Percepción') . '</th>
            <th style="background-color:#fcf8e3; border: 1px solid #e8e4cf;">' . utf8Decode('Retención') . '</th>
            <th style="background-color:#fcf8e3; border: 1px solid #e8e4cf;">' . utf8Decode('Rectificado') . '</th>
            <th style="background-color:#fcf8e3; border: 1px solid #e8e4cf;">Original - Rectificacion</th>	
        <tr>
    </thead>
';

while ($row = $conexion->extraer()) {
    //guardamos el número de control en la variable 
    $numcontrol = ( (empty($row['ncc_origen'])) ? $row['ncc_nccamortiza'] : $row['ncc_origen'] );
    
    $netOrigen = $row['importe_origen'];
    $netoOriginal = $row['importe_origen'] - $row['importeRectificacion_origen'];
    $netoPagadora = $row['importe_pagadora'] - $row['importeRectificacion_pagadora'];

    if ($varcontrol != $numcontrol) {
        /* Pintamos el header de la CLC de origen  */
        if($bolcontrol == false){
            $html .= $header;
        }
        $html .= '
            <tr >
                <td align="center">' . $row['clc_origen'] . '</td>
                <td align="center">' . $row['tcl_origen'] . '</td>
                <td align="center">' . $row['afe_origen'] . '</td>
                <td align="center">' . ((empty($row['ncc_origen'])) ? $row['ncc_nccamortiza'] : $row['ncc_origen']) . '</td>
                <td align="center">' . number_format( $row['percepcion_origen'],2) . '</td>
                <td align="center">' . number_format( $row['retencion_origen'],2) . '</td>
                <td align="center">' . number_format( $row['importeRectificacion_origen'],2) . '</td>
                <td align="center">' . number_format( $netoOriginal,2) . '</td>
            </tr>
        ';
        $varcontrol = $numcontrol;
        $bolcontrol = true;
        
    }

    $acumlulador = $varcontrol;
    
    $netPagador += ($row['importe_pagadora'] - $row['importeRectificacion_pagadora']);
    $netrectificado += $row['importeRectificacion_pagadora'] ;
    $porRectificar = $netPagador - $netoOriginal;
    
    if ($acumlulador == $numcontrol) {
        if ($boldetalle == false) {
            $html2 .= $header2; 
        }
        
        $boldetalle = true;
        
        $html2 .= '
            <tr>
                <td align="center" >' . $row['gpo_pagadora'] . $row['ur_pagadora'] . '</td>
                <td align="center" >' . $row['clc_pagadora'] . '</td>
                <td align="center">' . $row['tcl_pagadora'] . '</td>
                <td align="center">' . $row['afe_pagadora'] . '</td>
                <td align="center">' . $row['ncc_pagadora'] . '</td>
                <td align="center">' . number_format( $row['percepcion_pagadora'],2) . '</td>
                <td align="center">' . number_format( $row['retencion_pagadora'],2) . '</td>
                <td align="center">' . number_format( $row['importeRectificacion_pagadora'],2) . '</td>
                <td align="center">' . number_format( $netoPagadora,2) . '</td>
            </tr>
        ';

    } else {
        $bolcontrol=false;
    }
    
    if ($dat['numero'] == 1) {
        $pinta = true;
    } else {
        if ($numcontrol == $dat['ncc_origen']) { // $cuantos2
            $dat['numero'] = $dat['numero'] - $acum;
            if ($dat['numero'] > 1) {
                $pinta = false;
                $acum = $dat['numero'] - 1;
            } else {
                $pinta = true;
            }
        } else {
            $acum = 0; 
        }
    }
    
    if ($pinta) {
        $html3.='
            <table class = "table table-bordered class="danger"">
                <thead>
                    <tr>
                        <th style="background-color:#dff0d8; border: 1px solid #c1d2ba;">'.utf8Decode('Neto del N° de control').' '.$numcontrol.' </th>
                        <th style="background-color:#dff0d8; border: 1px solid #c1d2ba;"> Neto - Rectificado</th>
                        <th style="background-color:#dff0d8; border: 1px solid #c1d2ba;"> Disponible a Rectificar</th>
                        <th style="background-color:#fcf8e3; border: 1px solid #e8e4cf;"> Neto Pagador</th>
                        <th style="background-color:#fcf8e3; border: 1px solid #e8e4cf;"> Neto Recticado</th>
                        <th style="background-color:#fcf8e3; border: 1px solid #e8e4cf;"> Neto Pagador</th>
                        
                        <th style="background-color:#FFFFFF; border: 1px solid #e8e4cf;">NetoPagado - Disponible a Rectificar</th>
                        
                        <th style="background-color:#F72440; border: 1px solid #e8e4cf;">  Neto a Rectificar </th>
                    </tr>
                    <tr>
                        <td> '.number_format($netOrigen,2).' </td>
                        <td> '.number_format($row['importeRectificacion_origen'],2).' </td>
                        <td> '.number_format($netoOriginal,2).' </td>
                        
                        <td> '.number_format($netPagador,2).' </td>
                        <td> '.number_format($netrectificado,2).' </td>
                        <td> '.number_format($netoPagadora,2).' </td>
                        
                        <td> '.number_format($netoOriginal,2).' - ' . number_format($netoPagadora,2) . ' </td>
                        <td> '.number_format($porRectificar,2).' </td>
                    </tr>
                </thead>
            </table>
        ';
        $netPagador=0 ;
        $netrectificado=0 ;
        $porRectificar = 0;
    }
}

$html.='</table><br>';
$html2.='</table><br>';
echo$html;
echo$html2;
echo$html3;