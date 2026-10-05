<?php

require_once dirname(__FILE__) . '/getPolizaDetalle.php';

function getPolizaAgrupador($gpo_id, $ur_id, $agrupador, $mmc_id = '', $ejer_id = '', $afectacion = '')
{
    $polizas = new stdClass();
    $ejercicioActual = $_SESSION[_S]['ejercicioActual'];
    $ejer_id = ($ejer_id == '') ? $ejercicioActual : $ejer_id;
    if ($gpo_id == '' || $ur_id == '' || $agrupador == '') {
        $polizas->valido = false;
        return $polizas;
    }
    $parametrosSql = [
        ':gpo' => $gpo_id,
        ':ur' => $ur_id,
        ':ejercicio' => $ejer_id,
        ':agrupador' => $agrupador,
    ];
    if ($mmc_id !='') {
        $parametrosSql[':mmc'] = $mmc_id;
        $mmc_id = ' AND mmc_id = :mmc';
    }
    if ($afectacion !='') {
        $parametrosSql[':afectacion'] = $afectacion;
        $afectacion = "AND pdx_afe_id_vinculacion = :afectacion";
    }
    $sql = "SELECT gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id, pma_fecha, pma_concepto, epl_id, mmc_id, tmc_id, apg_id, ems_id, pma_debe, pma_haber
            FROM ctrl{$ejercicioActual}.cg_PolizaMaestro
                INNER JOIN ctrl{$ejercicioActual}.cg_PolizaDetalleAuxiliar USING(gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id)
                INNER JOIN ctrl{$ejercicioActual}.cg_MesContable s USING(gpo_id, ur_id, msc_id)
            WHERE gpo_id = :gpo AND ur_id = :ur AND pdx_ejercicio = :ejercicio 
                AND pdx_agrupador = :agrupador
                {$mmc_id}
                {$afectacion}
            GROUP BY gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id;";
    $conexion = new AccesoDatos();
    $conexion->bindParams($parametrosSql);
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($parametrosSql);

    if ($conexion->getNumeroRegistros() == 0) {
        $polizas->valido = false;
        $polizas->error = 2;
        $polizas->mensaje = '<div class="alert alert-warning">No hay registros de poliza(' . $agrupador . ')</div>';
        $conexion->desconectar();
        unset($conexion);
        return $polizas;
    }

    $array = array();
    $date = new stdClass;
    while ($dataRow = $conexion->extraer()) {
        $date->gpo_id = $dataRow['gpo_id'];
        $date->ur_id = $dataRow['ur_id'];
        $date->fecha = $dataRow['pma_fecha'];
        $fecha = validaFecha($date);

        $dato = new stdClass;

        $polizas->valido = true;
        $dato = new stdClass;
        $dato->gpo_id = $dataRow['gpo_id'];
        $dato->ur_id = $dataRow['ur_id'];
        $dato->ue_id = $dataRow['ue_id'];
        $dato->msc_id = $dataRow['msc_id'];
        $dato->pot_id = $dataRow['pot_id'];
        $dato->prf_id = $dataRow['prf_id'];
        $dato->pma_id = $dataRow['pma_id'];
        $dato->fecha = $fecha->fecha;
        $dato->concepto = $dataRow['pma_concepto'];
        $dato->estado = $dataRow['epl_id'];
        $dato->momento = $dataRow['mmc_id'];
        $dato->tipoMomento = $dataRow['tmc_id'];
        $dato->agrupadorPoliza = $dataRow['apg_id'];
        $dato->estadoMes = $dataRow['ems_id'];
        $dato->debe = $dataRow['pma_debe'];
        $dato->haber = $dataRow['pma_haber'];

        $array[$dataRow['gpo_id'] . $dataRow['ur_id'] . $dataRow['ue_id'] . $dataRow['msc_id'] . $dataRow['pot_id'] . $dataRow['prf_id'] . $dataRow['pma_id']] = $dato;
    }
    unset($conexion);
    $polizas->polizas = $array;
    return $polizas;
}
