<?php
//
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function getDocumentoRectificado($gpo_id, $ur_id, $bdo_id, $mmc_id, $parametroExtra = array()) {
    $parametrosSQL = [
        ':gpo_id' => $gpo_id,
        ':ur_id' => $ur_id,
        ':bdo_id' => $bdo_id,
    ];
    $sql = 
        "SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
            tipo, num_id, rct_justificacion AS concepto, 
            'A' AS estado, 
            DATE( rct_fechaAutorizacion ) AS fechaAutorizado, msc_id_autorizacion, 
            rct_id, rectificacionPresupuestal, rectificacionPercepcionContable, rectificacionRetencionContable 
        FROM ( 
                SELECT gpo_id, ur_id, clc_id, afe_id, ncp_id AS num_id, bdo_id, 
                    'P' AS tipo, 
                    rct_id, 
                    IFNULL( rectificacionPresupuestal, 0 ) AS rectificacionPresupuestal, 
                    0 AS rectificacionPercepcionContable, 0 AS rectificacionRetencionContable, 
                    rct_fechaAutorizacion, rct_justificacion, msc_id_autorizacion 
                FROM scp_Presupuestal 
                    INNER JOIN ( 
                        SELECT n.gpo_id, n.ur_id, n.ncp_id, n.rct_id, 
                            n.ncp_percepcionNeto AS rectificacionPresupuestal, 
                            m.rct_fechaAutorizacion, m.rct_justificacion, msc_id_autorizacion 
                        FROM scp_RectificacionPresupuestal n 
                            INNER JOIN scp_MaestroRectificacion m USING( gpo_id, ur_id, rct_id ) 
                        WHERE m.gpo_id = :gpo_id AND m.ur_id = :ur_id 
                            AND m.ecl_id = 6 
                    )r USING( gpo_id, ur_id, ncp_id ) 
                WHERE gpo_id = :gpo_id AND ur_id = :ur_id 
                    AND bdo_id = :bdo_id 
            UNION ALL
                SELECT gpo_id, ur_id, clc_id, afe_id, ncc_id AS num_id, bdo_id, 
                    'C' AS tipo, 
                    rct_id, 
                    0 AS rectificacionPresupuestal, 
                    IFNULL( rectificacionPercepcionContable, 0 ) AS rectificacionPercepcionContable, IFNULL( rectificacionRetencionContable, 0 ) AS rectificacionRetencionContable, 
                    rct_fechaAutorizacion, rct_justificacion, msc_id_autorizacion 
                FROM scp_Contable 
                    INNER JOIN (
                        SELECT n.gpo_id, n.ur_id, n.ncc_id, n.rct_id, 
                            n.ncc_percepcion AS rectificacionPercepcionContable, n.ncc_retencion AS rectificacionRetencionContable, 
                            m.rct_fechaAutorizacion, m.rct_justificacion, msc_id_autorizacion 
                        FROM scp_RectificacionContable n 
                            INNER JOIN scp_MaestroRectificacion m USING( gpo_id, ur_id, rct_id ) 
                        WHERE m.gpo_id = :gpo_id AND m.ur_id = :ur_id 
                            AND m.ecl_id = 6 
                    )r USING( gpo_id, ur_id, ncc_id ) 
                WHERE gpo_id = :gpo_id AND ur_id = :ur_id 
                    AND bdo_id = :bdo_id 
        )n 
    ";
    
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->bindParams($parametrosSQL);
    $conexion->setSql($sql);
    $conexion->consultar();
    $conexion->desconectar();
    $data = array();
    
    while ($dataRow = $conexion->extraer()) {
        $dato = new stdClass();
        $dato->tipo = $dataRow['tipo'];
        $dato->num_id = $dataRow['num_id'];
        $dato->afe_id = $dataRow['afe_id'];
        $dato->clc_id = $dataRow['clc_id'];
        $dato->rct_id = $dataRow['rct_id'];
        $dato->concepto	= $dataRow['concepto'];
        
        $date = new stdClass;
        $date->gpo_id = $gpo_id;
        $date->ur_id = $ur_id;
        $date->fecha = $dataRow['fechaAutorizado'];
        $fecha = new stdClass;
        $fecha = validaFecha($date);
        $dato->fechaAutorizado = $fecha->fecha;
        $dato->mesAutorizado = $dataRow['msc_id_autorizacion'];
        
        $dato->rectificadoPresupuestal = $dataRow['rectificacionPresupuestal'];
        $dato->rectificadoPercepcion = $dataRow['rectificacionPercepcionContable'];
        $dato->rectificadoRetencion = $dataRow['rectificacionRetencionContable'];
        
        $dato->estado = $dataRow['estado'];
        $data[$dataRow['num_id']] = $dato;
    }
    unset($conexion);
    if (count($data) == 0) {
        $data = FALSE;
    }
    
    return $data;
}