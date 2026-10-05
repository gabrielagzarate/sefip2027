<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once dirname(__FILE__).'/scg/getCLCContable.php';

require_once dirname(__FILE__).'/validaFecha.php';

function getinfoCLC( $gpo_id, $ur_id, $clc_id = '', $parametroExtra = array() ){
    $tipo = ( isset( $parametroExtra['tdf_id'] ) ? $parametroExtra['tdf_id']:'' );
    $ejercicio = ( isset( $parametroExtra['ejercicio'] ) ? $parametroExtra['ejercicio']:'' );
    
    if( $clc_id == '' ){
        return false;
    }
    $table = "ctrl".$ejercicio.".scp_Clc";
    
    $clc_detalle = new stdClass();
    
    /*------#VALIDAR QUE ENVIEN EL DOCUMENTO Ó ( RFC Y REFERENCIA )*/
    if( $clc_id == ''  ){
        $clc_detalle->valido = false;
        $clc_detalle->error = 1;
        $clc_detalle->mensaje ='NO ES POSIBLE VALIDAR EL DOCUMENTO FALTA LA REFERENCIA O EL RFC';
        return $clc_detalle;
    }
    
    $parametro = [
        ':gpo' => $gpo_id,
        ':ur' => $ur_id,
        ':clc' => $clc_id
    ];

    if( $clc_id != '' ){
        $sql = "
            SELECT * 
            FROM $table 
            WHERE gpo_id = :gpo AND ur_id = :ur
                AND clc_id = :clc ";
        
    }else{
        $sql = "";
    }
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($parametro);
    $conexion->desconectar();

 
    unset($conexion);
		
    if( isset($parametroExtra['mmc_id'] ) )
            switch( $parametroExtra['mmc_id'] ){
            case 'G':
                $clc_detalle->detalleCereCo = getCLCContable( $gpo_id, $ur_id, $clc_id, $parametroExtra['mmc_id'] );
                break;
            case 'B':
                $clc_detalle->detalleSinFlujoEfectivo = getCLCContable( $gpo_id, $ur_id, $clc_id, $parametroExtra['mmc_id'] );
                break;
            default:
                $clc_detalle->detalleCereCo = getCLCContable( $gpo_id, $ur_id, $clc_id, "G");
                $clc_detalle->detalleSinFlujoEfectivo = getCLCContable( $gpo_id, $ur_id, $clc_id, "B");
                break;
            }
                       
    return $clc_detalle;
}
?>