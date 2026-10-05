<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function getCompromisoContable( $gpo_id, $ur_id, $cmp_id, $mmc_id, $parametroExtra = array() ){
    
    $sql = 
    "SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, cmp_id, 
        tipo, num_id, concepto, 
        percepcionPresupuestal, percepcionContable, retencionContable, 
        DATE( bde_fechaAutorizado ) AS fechaAutorizado, msc_id, 
        DATE( bde_fechaCancelado ) AS fechaCancelado, msc_id_cancelado, 
        '' AS tmi_id, 
        bde_estado AS estado, bde_agrupador AS agrupador 
    FROM scp_BancoDocumentoEjercido v 
        INNER JOIN ( 
            SELECT gpo_id, ur_id, afe_id, ncp_id AS num_id, cmp_id, bdo_id, 
                'P' AS tipo, ncp_concepto AS concepto, 
                ncp_percepcionNeto AS percepcionPresupuestal, 0 AS percepcionContable, 0 AS retencionContable 
            FROM scp_Presupuestal 
            WHERE gpo_id = :gpo AND ur_id = :ur AND cmp_id = :cmp
        )n USING( gpo_id, ur_id, afe_id, bdo_id )";
    
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar([
        ':gpo' => $gpo_id,
        ':ur' => $ur_id,
        ':cmp' => $cmp_id
    ]);
    $conexion->desconectar();
    
    //$data = new stdClass();
    $data = array();
    
    while( $dataRow = $conexion->extraer() ){
        $dato = new stdClass();
        $dato->tipo = $dataRow['tipo'];
        $dato->num_id = $dataRow['num_id'];
        $dato->afe_id = $dataRow['afe_id'];
        $dato->clc_id = $dataRow['clc_id'];
        $dato->concepto	= $dataRow['concepto'];
        
        $date = new stdClass;
        $date->gpo_id = $dataRow['gpo_id'];
        $date->ur_id = $dataRow['ur_id'];
        $date->fecha = $dataRow['fechaAutorizado'];
        $fecha = new stdClass;
        $fecha = validaFecha($date);
        $dato->fechaAutorizado = $fecha->fecha;
        
        $date = new stdClass;
        $date->gpo_id = $dataRow['gpo_id'];
        $date->ur_id = $dataRow['ur_id'];
        $date->fecha = $dataRow['fechaCancelado'];
        $fecha = new stdClass;
        $fecha = validaFecha($date);
        $dato->fechaCancelado = ( isset( $fecha->fecha ) ? $fecha->fecha:'' );
        
        $dato->Presupuestal = $dataRow['percepcionPresupuestal'];
        $dato->Percepcion = $dataRow['percepcionContable'];
        $dato->Retencion = $dataRow['retencionContable'];
        
        $dato->tipoMinistracion = $dataRow['tmi_id'];
        $dato->estado = $dataRow['estado'];
        $dato->mesAutorizado = $dataRow['msc_id'];
        $dato->mesCancelado = $dataRow['msc_id_cancelado'];
        $dato->agrupador = $dataRow['agrupador'];
        
        //array_push( $data, $dato );
        $data[$dataRow['num_id'].$dato->mesAutorizado] = $dato;
        //echo '<pre>'; print_r($dato);echo '</pre>';//die();
    }
    unset($conexion);
    //echo '<pre>'; print_r($data);echo '</pre>';//die();
    if( count($data) == 0 ){
        $data = FALSE;
    }
    
    return $data;
}
?>