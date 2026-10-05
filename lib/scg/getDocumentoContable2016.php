<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function getDocumentoContable2016($gpo_id, $ur_id, $bdo_id, $mmc_id, $parametroExtra = array()) {
    $parametrosSQL = [
        ':gpo'=>$gpo_id,
        ':ur'=>$ur_id,
        ':bdo'=>$bdo_id
    ];    
    switch ($mmc_id) {
        case 'B':
            $tabla = ''.BD_CTRL_2016.'.ing_BancoDocumentoMinistrado';
            $tmi_id = "v.tmi_id, '' AS bdp_poliza,  ";
            $fechas = "DATE(ing_fechaPago) AS fechaAutorizado,  MONTH(ing_fechaPago) AS msc_id, ";
            $letraAuxiliar = 'm';
            break;
        case 'T':
            $tabla = ''.BD_CTRL_2016.'.ing_BancoDocumentoMinistrado';
            $tmi_id = "v.tmi_id, '' AS bdp_poliza, ";
            $fechas = "DATE(ing_fechaPago) AS fechaAutorizado,  MONTH(ing_fechaPago) AS msc_id, ";
            $letraAuxiliar = 'm';
            break;
        case 'P':
            $tabla = ''.BD_CTRL_2016.'.cg_BancoDocumentoPagado';
            $tmi_id = "'' AS tmi_id, v.bdp_poliza, ";
            $fechas = "DATE(bdp_fechaAutorizado) AS fechaAutorizado,  MONTH(bdp_fechaAutorizado) AS msc_id, ";
            $letraAuxiliar = 'p';
            break;
    }
    
    $sql = "
        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
            tipo, num_id, concepto, 
            percepcionPresupuestal, percepcionContable, retencionContable, 
            $fechas 
            '' AS fechaCancelado, '' AS msc_id_cancelado, 
            $tmi_id 
            bd" . $letraAuxiliar . "_estado AS estado, bd" . $letraAuxiliar . "_agrupador AS agrupador 
        FROM $tabla v 
            INNER JOIN ".BD_CTRL_2016.".scp_Clc c USING(gpo_id, ur_id, clc_id) 
            INNER JOIN (
                SELECT gpo_id, ur_id, afe_id, ncp_id AS num_id, bdo_id, 
                    'P' AS tipo, ncp_concepto AS concepto, 
                    ncp_percepcionNeto AS percepcionPresupuestal, 0 AS percepcionContable, 0 AS retencionContable 
                FROM ctrl2016.scp_Presupuestal 
                WHERE gpo_id = :gpo AND ur_id = :ur 
                    AND bdo_id = :bdo 
            UNION ALL
                SELECT gpo_id, ur_id, afe_id, ncc_id AS num_id, bdo_id, 
                    'C' AS tipo, ncc_concepto AS concepto, 
                    0 AS percepcionPresupuestal, ncc_percepcion AS percepcionContable, ncc_retencion AS retencionContable 
                FROM ".BD_CTRL_2016.".scp_Contable 
                WHERE gpo_id = :gpo AND ur_id = :ur
                    AND bdo_id = :bdo
        )n USING(gpo_id, ur_id, afe_id, bdo_id)
        where YEAR(ing_fechaPago)=2023
    ";
    
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($parametrosSQL);
    $conexion->desconectar();
    
    $data = array();
    
    while($dataRow = $conexion->extraer()){
        $dato = new stdClass();
        $dato->gpo_id = $dataRow['gpo_id'];
        $dato->ur_id = $dataRow['ur_id'];
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
        $dato->fechaCancelado = (isset($fecha->fecha) ? $fecha->fecha:'');
        
        $dato->Presupuestal = $dataRow['percepcionPresupuestal'];
        $dato->Percepcion = $dataRow['percepcionContable'];
        $dato->Retencion = $dataRow['retencionContable'];
        
        $dato->tipoMinistracion = $dataRow['tmi_id'];
        $dato->tipoPagado = $dataRow['bdp_poliza'];
        $dato->estado = $dataRow['estado'];
        $dato->mesAutorizado = $dataRow['msc_id'];
        $dato->mesCancelado = $dataRow['msc_id_cancelado'];
        $dato->agrupador = $dataRow['agrupador'];
        
        $data[$dataRow['num_id']] = $dato;
    }
    unset($conexion);
    
    if (count($data) == 0) {
        $data = FALSE;
    }
    
    return $data;
}