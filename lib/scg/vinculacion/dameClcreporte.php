<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function dameClcreporte($gpo_id, $ur_id, $clc_id, $parametroExtra = array())
{
    //echo $gpo_id.$ur_id.$clc_id;die();

    if ($gpo_id == '' || $ur_id == '' || $clc_id == '') return false;

    $bdo_id = ( isset($parametroExtra['bdo_id']) ? $parametroExtra['bdo_id'] : '' );
    $todo = ( isset($parametroExtra['todo']) ? $parametroExtra['todo'] : FALSE );
    $ejercicio = ( isset($parametroExtra['ejercicio']) ? $parametroExtra['ejercicio']
            : '' );

    $parametroExtra['gpo_id_origen'] = ( isset($parametroExtra['gpo_id_origen'])
            ? $parametroExtra['gpo_id_origen'] : $gpo_id );
    $parametroExtra['ur_id_origen'] = ( isset($parametroExtra['ur_id_origen']) ? $parametroExtra['ur_id_origen']
            : $ur_id );

    $filtroContable = "
            WHERE gpo_id = $gpo_id AND ur_id = $ur_id 
                AND clc_id = $clc_id 
    ";
    if ($gpo_id.$ur_id == 118 && $parametroExtra['gpo_id_origen'].$parametroExtra['ur_id_origen']
        != 118) {
        $filtroContable .= "
                AND cta_gpo_id = ".$parametroExtra['gpo_id_origen']." AND cta_ur_id = ".$parametroExtra['ur_id_origen']." 
        ";
    }

    $html = '';
    $return = new stdClass();

    $union = "
            UNION ALL
                SELECT gpo_id, ur_id, clc_id, afe_id, ncc_id, 'N/A', bdo_id, 
                    rfc_id, ncc_referencia, ncc_percepcion AS percepcion, ncc_retencion AS retencion, 
                    CONCAT( cta_cuenta, '-', cta_nombre ) AS Cuenta, 'C' AS tipo, 
                    ncc_tipoMovimiento 
                FROM ctrl".$ejercicio.".scp_Contable 
                    INNER JOIN ctrl".$ejercicio.".cg_PlanCuentas USING( cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id ) 
                $filtroContable
                ORDER BY ncc_tipoMovimiento, retencion 
    ";

    $sql = "
        SELECT gpo_id, ur_id, clc_id, afe_id, ncp_id, cmp_id, 
            rfc_id, ncp_referencia, percepcion, retencion, 
            cuenta, tcl_nombre, clc_concepto, clc_percepcion, clc_retencion, 
            DATE(clc_fechaEnvio) AS clc_fechaEnvio, DATE(clc_fechaCancelacion) AS clc_fechaRechazo, 
            bur_id, ecf_clave, bdo_id, tipo, 
            tcl_id, stc_id, tcl_nombre, stc_nombre, mpc_nombre, mpc_id 
        FROM ctrl".$ejercicio.".scp_Clc c 
            INNER JOIN ctrl".$ejercicio.".cat_ModoPagoClc USING( mpc_id ) 
            INNER JOIN ctrl".$ejercicio.".cat_SubtipoClc USING( tcl_id, stc_id ) 
            INNER JOIN ctrl".$ejercicio.".cat_TipoClc USING( tcl_id ) 
            INNER JOIN ctrl".$ejercicio.".enc_ClaveFinanciamiento USING( ecf_id ) 
            INNER JOIN ( 
                SELECT gpo_id, ur_id, clc_id, afe_id, ncp_id, cmp_id, bdo_id, 
                    rfc_id, ncp_referencia, ncp_percepcionNeto AS percepcion, 0 AS retencion, 
                    CONCAT( pg_id, '-', pg_nombre ) AS Cuenta, 'P' AS tipo, 
                    '+' AS ncc_tipoMovimiento 
                FROM ctrl".$ejercicio.".scp_Presupuestal 
                    INNER JOIN ctrl".$ejercicio.".cat_PartidaGenerica USING( pg_id ) 
                WHERE gpo_id = $gpo_id AND ur_id = $ur_id 
                    AND clc_id = $clc_id 
                ".( $todo ? $union : '' )."
            )n USING( gpo_id, ur_id, clc_id ) 
        WHERE gpo_id = $gpo_id AND ur_id = $ur_id 
            AND clc_id = $clc_id 
        ORDER BY tipo DESC 
    ";

    //echo $sql ; die();
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar();
    $dataRow = $conexion->extraer();

    $date = new stdClass;
    $date->gpo_id = $dataRow['gpo_id'];
    $date->ur_id = $dataRow['ur_id'];
    $date->fecha = $dataRow['clc_fechaEnvio'];
    $fechaEnvio = new stdClass;
    $fechaEnvio = validaFecha($date);
    $date = new stdClass;
    $date->gpo_id = $dataRow['gpo_id'];
    $date->ur_id = $dataRow['ur_id'];
    $date->fecha = $dataRow['clc_fechaRechazo'];
    $fechaRechazo = new stdClass;
    $fechaRechazo = validaFecha($date);

    if ($gpo_id.$ur_id == 118 && $parametroExtra['gpo_id_origen'].$parametroExtra['ur_id_origen']
        != 118) {
        $percepcion = $dataRow['percepcion'];
        $retencion = $dataRow['retencion'];
    } else {
        $percepcion = $dataRow['clc_percepcion'];
        $retencion = $dataRow['clc_retencion'];
    }



    $datos = array();



    $datos= array(
        'id' => $dataRow['gpo_id'].$dataRow['ur_id'].' - '.$dataRow['clc_id'],
        'gpo_id' => $dataRow['gpo_id'],
        'ur_id' => $dataRow['ur_id'],
        'clc_id' => $dataRow['clc_id'],
        'tipo_Clc' => $dataRow['tcl_id']." - ".$dataRow['tcl_nombre'].' : '.$dataRow['stc_id']." - ".$dataRow['stc_nombre'],
        'fechaEnvio' => ( $fechaEnvio->valido ? $fechaEnvio->fecha : '' ),
        'fechaRechazo' => ( $fechaRechazo->valido ? $fechaRechazo->fecha : '' ),
        'bur_id' => $dataRow['bur_id'],
        'ecf_clave' => $dataRow['ecf_clave'],
        'clc_concepto' => $dataRow['clc_concepto'],
        'mpc_nombre' => $dataRow['mpc_nombre'],
        'percepcion' => $percepcion,
        'retencion' => $retencion
    );


    $conexion->desconectar();
    unset($conexion);
    //echo '<br><pre>C-';print_r($datos);echo '</pre>';die(); //*/
    return $datos;
}
