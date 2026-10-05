<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function getDocumentoContableTercero($gpo_id, $ur_id, $bdo_id, $parametroExtra = array()) {
    $ejercicio = APP_EJERCICIO;
    $sqlParams = [
        ':gpo' => $gpo_id,
        ':ur' => $ur_id,
        ':ogpo' => $parametroExtra['gpo_id_origen'],
        ':our' => $parametroExtra['ur_id_origen'],
        ':bdo' => $bdo_id,
        
    ];
    if (isset($parametroExtra['afe_id']) && $parametroExtra['afe_id'] != '') {
        $sqlFiltro = " AND  n.afe_id in (" . $parametroExtra['afe_id'] . ") ";
    }
    $sql = 
    "SELECT v.gpo_id, v.ur_id, max(v.clc_id) as clc_id, min(v.afe_id) as afe_id, v.bdo_id,
        n.tipo, max(n.num_id) as num_id, max(n.concepto) as concepto,
        SUM(n.percepcionPresupuestal) AS percepcionPresupuestal, SUM(n.percepcionContable) AS percepcionContable, SUM(n.retencionContable) AS retencionContable,
        DATE(max(v.bdp_fechaAutorizado)) AS fechaAutorizado, max(v.msc_id) as msc_id,
        DATE(max(v.bdp_fechaCancelado)) AS fechaCancelado, max(v.msc_id_cancelado) as msc_id_cancelado,
        '' AS tmi_id, max(v.bdp_poliza) as bdp_poliza,
        v.bdp_estado AS estado, max(v.bdp_agrupador) AS agrupador,
        v.gpo_id_origen, v.ur_id_origen, max(v.clc_id_origen) as clc_id_origen, max(v.afe_id_origen) as afe_id_origen, max(v.bdo_id_origen) as bdo_id_origen
    FROM cg_BancoDocumentoPagadoContable v 
        INNER JOIN (
            SELECT n.gpo_id, n.ur_id, n.afe_id, n.ncc_id AS num_id, n.bdo_id, 
                'C' AS tipo, n.ncc_concepto AS concepto, 
                0 AS percepcionPresupuestal, n.ncc_percepcion AS percepcionContable, n.ncc_retencion AS retencionContable, 
                IFNULL(o.gpo_id, n.gpo_id) AS gpo_id_origen, IFNULL(o.ur_id, n.ur_id) AS ur_id_origen, IFNULL(o.clc_id, n.clc_id) AS
                clc_id_origen, IFNULL(o.afe_id, n.afe_id) AS afe_id_origen, IFNULL(o.bdo_id, n.bdo_id) AS bdo_id_origen
            FROM scp_Contable n 
                LEFT JOIN ctrl{$ejercicio}.scp_Contable o ON n.ncc_gpo = o.gpo_id AND n.ncc_ur = o.ur_id AND n.ncc_clc = o.clc_id AND n.ncc_nccamortiza = o.ncc_id
            WHERE n.gpo_id = :gpo AND n.ur_id = :ur
                AND n.bdo_id = :bdo
                {$sqlFiltro}
    )n USING(gpo_id, ur_id, afe_id, bdo_id, gpo_id_origen, ur_id_origen, clc_id_origen, afe_id_origen, bdo_id_origen)
    WHERE v.gpo_id_origen = :ogpo AND v.ur_id_origen = :our
        AND   n.bdo_id  = :bdo
        {$sqlFiltro}
        AND bdp_estado='A'
    GROUP BY gpo_id, ur_id, bdo_id
    ";

    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->bindParams($sqlParams);
    $conexion->setSql($sql);
    $conexion->consultar();
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
        $dato->concepto = $dataRow['concepto'];

        $date = new stdClass;
        $date->gpo_id = $dataRow['gpo_id_origen'];
        $date->ur_id = $dataRow['ur_id_origen'];
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
        $dato->fechaCancelado = (isset($fecha->fecha) ? $fecha->fecha : '');

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

        $data[$dataRow['num_id'].$dato->mesAutorizado] = $dato;
    }

    unset($conexion);
    if (count($data) == 0) {
        $data = FALSE;
    }

    return $data;
}