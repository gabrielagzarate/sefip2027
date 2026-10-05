<?php

function validaTipoPago($gpo_id, $ur_id, $bdo_id)
{
    $return = new stdClass();
    $conexion = new AccesoDatos();
    $sql = "
        SELECT gpo_id,ur_id,clc_id,clc_fondoRotatorio, mpc_id,tmi_id 
        FROM tesoreria.min_BancoDocumentoMinistrado_".APP_EJERCICIO."
        INNER JOIN scp_Clc using(gpo_id,ur_id,clc_id)
        WHERE gpo_id= ? 
        AND ur_id = ? 
        AND bdo_id IN ( $bdo_id )
        AND bdm_estado = 'A' LIMIT 1;
    ";
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar([$gpo_id, $ur_id]);
    while ($infoClc = $conexion->extraer()) {
        $clc_id = $infoClc['clc_id'];
        $clc_fondoRotatorio = $infoClc['clc_fondoRotatorio'];
        $mpc_id = $infoClc['mpc_id'];
        $tmi_id = $infoClc['tmi_id'];
    }
    $conexion->desconectar();
    if (($tmi_id != 2) && (( $tmi_id = 4 || $tmi_id = 6 || $tmi_id = 7 || $tmi_id = 9) || (($gpo_id = 1 && $ur_id = 16) || ($gpo_id = 9 && $ur_id = 01) || ($gpo_id = 9 && $ur_id = 02)) || ($clc_fondoRotatorio = 'S') || $mpc_id != 1)) {
        $return->PagDirecto = true;
    } else {
        $return->PagDirecto = false;
    }
    return $return;
}
