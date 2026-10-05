<?php
/**
 * Devuelve los detalles del documento (Contable) dependiendo del momento contable solicitado
 * Esto es el registro auxiliar del documento en las tablas contables (NO DATOS DE LA POLIZA)
 * @param int $gpo_id  grupo 
 * @param int $ur_id unidad responsable
 * @param int $bdo_id identificador del documento
 * @param string $mmc_id momento contable 
 * @param array $parametroExtra datos y filtros adicionales
 * @return array arreglo con los detalles del registro contable
 */
function getDocumentoContable($gpo_id, $ur_id, $bdo_id, $mmc_id, $parametroExtra = array()) {
    $parametrosSQL = [
        ':gpo_id' => $gpo_id,
        ':ur_id' => $ur_id,
        ':bdo_id' => $bdo_id,
        ':mmc_id' => $mmc_id,
    ];
    $sqlFiltro = '';
    if (isset($parametroExtra['afe_id']) && $parametroExtra['afe_id'] != '') {
        $sqlFiltro = " AND  afe_id in (:afes) ";
        $parametrosSQL[':afes'] = $parametroExtra['afe_id'];
    }else{
        $sqlFiltro = "";
    }
    $ordeyby = '';
    switch ($mmc_id) {
        case 'B':
            $tabla =  BD_TESORERIA.'.min_BancoDocumentoMinistrado_'.APP_EJERCICIO;
            $tmi_id = "v.tmi_id, '' AS bdp_poliza, ";
            $letraAuxiliar = 'm';
            break;
        case 'D':
            $tabla = 'scp_BancoDocumentoDevengado';
            $tmi_id = "'' AS tmi_id, '' AS bdp_poliza, ";
            $letraAuxiliar = 'd';
            break;
        case 'E':
            $tabla = 'scp_BancoDocumentoEjercido';
            $tmi_id = "'' AS tmi_id, '' AS bdp_poliza, ";
            $letraAuxiliar = 'e';
            break;
        case 'G':
            $tabla = 'scp_BancoDocumentoCereCo';
            $tmi_id = "'' AS tmi_id, '' AS bdp_poliza, ";
            $letraAuxiliar = 'g';
            break;
        case 'T':
            $tabla = BD_TESORERIA.'.min_BancoDocumentoMinistrado_'.APP_EJERCICIO;
            $tmi_id = "v.tmi_id, '' AS bdp_poliza, ";
            $letraAuxiliar = 'm';
            break;
        case 'P':
            $tabla = 'cg_BancoDocumentoPagado';
            $tmi_id = "'' AS tmi_id, v.bdp_poliza, ";
            $letraAuxiliar = 'p';
            $ordeyby=" ORDER BY bdp_estado DESC ";
            break;
        case 'N':
            $tabla = 'cg_BancoDocumentoProvisionTransferido';
            $tmi_id = "'' AS tmi_id, '' AS bdp_poliza, ";
            $letraAuxiliar = 'n';
            break;
    }
    
    $sql = "SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id,
            tipo, num_id, concepto, 
            percepcionPresupuestal, percepcionContable, retencionContable, 
            DATE(bd{$letraAuxiliar}_fechaAutorizado) AS fechaAutorizado, msc_id, 
            DATE(bd{$letraAuxiliar}_fechaCancelado) AS fechaCancelado, msc_id_cancelado, 
            {$tmi_id} bd{$letraAuxiliar}_estado AS estado, bd{$letraAuxiliar}_agrupador AS agrupador 
        FROM {$tabla} v 
            INNER JOIN (
                SELECT gpo_id, ur_id, afe_id, ncp_id AS num_id, bdo_id, 
                    'P' AS tipo, ncp_concepto AS concepto, 
                    ncp_percepcionNeto AS percepcionPresupuestal, 0 AS percepcionContable, 0 AS retencionContable 
                FROM scp_Presupuestal 
                WHERE gpo_id = :gpo_id AND ur_id = :ur_id 
                    AND bdo_id = :bdo_id
                    {$sqlFiltro}
            UNION ALL
                SELECT gpo_id, ur_id, afe_id, ncc_id AS num_id, bdo_id, 
                    'C' AS tipo, ncc_concepto AS concepto, 
                    0 AS percepcionPresupuestal, ncc_percepcion AS percepcionContable, ncc_retencion AS retencionContable 
                FROM scp_Contable 
                WHERE gpo_id = :gpo_id AND ur_id = :ur_id
                    AND bdo_id = :bdo_id
                    {$sqlFiltro}
            )n USING(gpo_id, ur_id, afe_id, bdo_id)
        {$ordeyby}
    ";
    
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($parametrosSQL);
    $conexion->desconectar();
    $data = array();
    
    while ($dataRow = $conexion->extraer()) {
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
        
        $data[$dataRow['num_id'].$dato->mesAutorizado] = $dato;
    }
    
    unset($conexion);
    if (count($data) == 0) {
        $data = FALSE;
    }
    
    return $data;
}