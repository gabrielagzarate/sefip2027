<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function getDocumentoContableTercero2016( $gpo_id, $ur_id, $bdo_id, $parametroExtra = array() ){
    //echo '<pre>';print_r($parametroExtra);echo '</pre>';//die();
    $sql = "
        SELECT v.gpo_id, v.ur_id, v.clc_id, v.afe_id, v.bdo_id, 
            n.tipo, n.num_id, n.concepto, 
            SUM(n.percepcionPresupuestal) AS percepcionPresupuestal, SUM(n.percepcionContable) AS percepcionContable, SUM(n.retencionContable) AS retencionContable, 
            DATE( v.bdp_fechaAutorizado ) AS fechaAutorizado, v.msc_id, 
            DATE( v.bdp_fechaCancelado ) AS fechaCancelado, v.msc_id_cancelado, 
            '' AS tmi_id, v.bdp_poliza, 
            v.bdp_estado AS estado, v.bdp_agrupador AS agrupador, 
            v.gpo_id_origen, v.ur_id_origen, v.clc_id_origen, v.afe_id_origen, v.bdo_id_origen 
        FROM ctrl2016.cg_BancoDocumentoPagadoContable v 
            INNER JOIN ( 
                SELECT gpo_id, ur_id, afe_id, ncp_id AS num_id, bdo_id, 
                    'P' AS tipo, ncp_concepto AS concepto, 
                    ncp_percepcionNeto AS percepcionPresupuestal, 0 AS percepcionContable, 0 AS retencionContable,
                    '' AS gpo_id_origen, '' AS ur_id_origen, '' AS ncc_id_origen 
                FROM ctrl2016.scp_Presupuestal 
                WHERE gpo_id = $gpo_id AND ur_id = $ur_id 
                    AND bdo_id = $bdo_id 
            UNION ALL
                SELECT gpo_id, ur_id, afe_id, ncc_id AS num_id, bdo_id, 
                    'C' AS tipo, ncc_concepto AS concepto, 
                    0 AS percepcionPresupuestal, ncc_percepcion AS percepcionContable, ncc_retencion AS retencionContable, 
                    ncc_gpo AS gpo_id_origen, ncc_ur AS ur_id_origen, ncc_nccamortiza AS ncc_id_origen 
                FROM ctrl2016.scp_Contable 
                WHERE gpo_id = $gpo_id AND ur_id = $ur_id 
                    AND bdo_id = $bdo_id 
            )n USING( gpo_id_origen, ur_id_origen, ncc_id_origen ) 
        WHERE v.gpo_id_origen = ".$parametroExtra['gpo_id_origen']." AND v.ur_id_origen = ".$parametroExtra['ur_id_origen']."
    ";
    //echo '<pre>';echo $sql;echo '</pre>';//die();
    
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar();
    $conexion->desconectar();
    
    //$data = new stdClass();
    $data = array();
    
    while( $dataRow = $conexion->extraer() ){
        $dato = new stdClass();
        $dato->gpo_id = $dataRow['gpo_id'];
        $dato->ur_id = $dataRow['ur_id'];
        $dato->tipo = $dataRow['tipo'];
        $dato->num_id = $dataRow['num_id'];
        $dato->afe_id = $dataRow['afe_id'];
        $dato->clc_id = $dataRow['clc_id'];
        $dato->concepto	= $dataRow['concepto'];
        
        $date = new stdClass;
        $date->gpo_id = $dataRow['gpo_id_origen'];
        $date->ur_id = $dataRow['ur_id_origen'];
        $date->fecha = $dataRow['fechaAutorizado'];
        //echo '<pre>';print_r($date);echo '</pre>';die();
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
        $dato->tipoPagado = $dataRow['bdp_poliza'];
        $dato->estado = $dataRow['estado'];
        $dato->mesAutorizado = $dataRow['msc_id'];
        $dato->mesCancelado = $dataRow['msc_id_cancelado'];
        $dato->agrupador = $dataRow['agrupador'];
        
        $dato->gpo_id_origen = $dataRow['gpo_id_origen'];
        $dato->ur_id_origen = $dataRow['ur_id_origen'];
        $dato->afe_id_origen = $dataRow['afe_id_origen'];
        $dato->clc_id_origen = $dataRow['clc_id_origen'];
        $dato->bdo_id_origen = $dataRow['bdo_id_origen'];
        
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