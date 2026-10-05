<?php

require_once dirname(__FILE__) . '/getPolizaDetalle.php';
require_once dirname(__FILE__) . '/../validaFecha.php';

function getPoliza($gpo_id, $ur_id, $ue_id, $msc_id, $pot_id, $prf_id, $pma_id)
{

    $poliza = new stdClass();
    if ($gpo_id == '' || $ur_id == '' || $ue_id == '' || $msc_id == '' || $pot_id == '' || $prf_id == '' || $pma_id == '') {
        $poliza->valido = false;
        return $poliza;
    }
    $parametrosSql = [
        ':gpo_id' => $gpo_id,
        ':ur_id' => $ur_id,
        ':ue_id' => $ue_id,
        ':msc_id' => $msc_id,
        ':pot_id' => $pot_id,
        ':prf_id' => $prf_id,
        ':pma_id' => $pma_id,
    ];
    
    $sql = 
        "SELECT m.gpo_id, m.ur_id, m.ue_id, m.msc_id, m.pot_id, m.prf_id, m.pma_id, 
            m.pma_fecha, m.pma_concepto, 
            m.pma_debe, m.pma_haber, 
            m.epl_id, 
            m.mmc_id, m.tmc_id, 
            m.apg_id, 
            s.ems_id
        FROM ".BD_CTRL.".cg_PolizaMaestro m
            INNER JOIN cg_MesContable s USING(gpo_id, ur_id, msc_id) 
        WHERE m.gpo_id = :gpo_id AND m.ur_id = :ur_id 
            AND m.ue_id = :ue_id 
            AND m.msc_id = :msc_id 
            AND m.pot_id = :pot_id 
            AND m.prf_id = :prf_id
            AND m.pma_id = :pma_id 
    ";
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($parametrosSql);

    if ($conexion->getNumeroRegistros() == 0) {
        $poliza->valido = FALSE;
        $poliza->error = 2;
        $poliza->mensaje = '<div class="alert alert-warning">No hay registros de poliza ( ' . $pot_id . '-' . $prf_id . $pma_id . ')</div>';
        $conexion->desconectar();
        unset($conexion);
        return $poliza;
    }

    $dataRow = $conexion->extraer();
    unset($conexion);
    $date = new stdClass;
    $date->gpo_id = $dataRow['gpo_id'];
    $date->ur_id = $dataRow['ur_id'];
    $date->fecha = $dataRow['pma_fecha'];
    $fecha = validaFecha($date);

    $poliza->valido = TRUE;
    $poliza->gpo_id = $dataRow['gpo_id'];
    $poliza->ur_id = $dataRow['ur_id'];
    $poliza->ue_id = $dataRow['ue_id'];
    $poliza->msc_id = $dataRow['msc_id'];
    $poliza->pot_id = $dataRow['pot_id'];
    $poliza->prf_id = $dataRow['prf_id'];
    $poliza->pma_id = $dataRow['pma_id'];
    $poliza->poliza = $dataRow['pot_id'] . '-' . $dataRow['prf_id'] . $dataRow['pma_id'];
    $poliza->fecha = $fecha->fecha;
    $poliza->concepto = $dataRow['pma_concepto'];
    $poliza->estado = $dataRow['epl_id'];
    $poliza->momento = $dataRow['mmc_id'];
    $poliza->tipoMomento = $dataRow['tmc_id'];
    $poliza->agrupador = $dataRow['apg_id'];
    $poliza->estadoMes = $dataRow['ems_id'];
    $poliza->debe = $dataRow['pma_debe'];
    $poliza->haber = $dataRow['pma_haber'];

    $poliza->detalleMayor = getPolizaDetalle($gpo_id, $ur_id, $ue_id, $msc_id, $pot_id, $prf_id, $pma_id, 1);
    $poliza->detalleRegistro = getPolizaDetalle($gpo_id, $ur_id, $ue_id, $msc_id, $pot_id, $prf_id, $pma_id, 2);
    $poliza->detalleAuxiliar = getPolizaDetalle($gpo_id, $ur_id, $ue_id, $msc_id, $pot_id, $prf_id, $pma_id, 3);
    return $poliza;
}
