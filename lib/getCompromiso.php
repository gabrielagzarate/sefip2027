<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once dirname(__FILE__) . '/scg/getCompromisoContable.php';
require_once dirname(__FILE__) . '/validaFecha.php';

function getCompromiso($gpo_id, $ur_id, $cmp_id = '', $rfc_id = '', $referencia = '', $parametroExtra = array())
{
    $ejercicio = (isset($parametroExtra['ejercicio']) ? $parametroExtra['ejercicio'] : APP_EJERCICIO);

    if ($gpo_id == '' || $ur_id == '') {
        return false;
    }
    $table = "ctrl{$ejercicio}.scp_Compromiso";
    $documento = new stdClass();

    /*------#VALIDAR QUE ENVIEN EL DOCUMENTO Ó ( RFC Y REFERENCIA )*/
    if ($cmp_id == '' && ($rfc_id == '' || $referencia == '')) {
        $documento->valido = false;
        $documento->error = 1;
        $documento->mensaje = 'NO ES POSIBLE VALIDAR EL DOCUMENTO FALTA LA REFERENCIA O EL RFC';
        return $documento;
    }


    $conexion = new AccesoDatos();
    $conexion->conectar();

    if ($cmp_id == '' && $rfc_id != '' && $referencia != '') {
        $sql = "SELECT * FROM $table  WHERE gpo_id = :gpo AND ur_id = :ur AND rfc_id = :rfc 
            AND cmp_numOrden = :referencia";
        $conexion->bindParams([
            ':gpo' => $gpo_id,
            ':ur' => $ur_id,
            ':rfc' => $rfc_id,
            ':referencia' => $referencia
        ]);
    } elseif ($cmp_id != '' && ($rfc_id == '' || $referencia == '')) {
        $sql = "SELECT * FROM $table WHERE cmp_id = :cmp ";
        $conexion->bindParams([':cmp' => $cmp_id]);
    } elseif ($cmp_id != '' && $rfc_id != '' && $referencia != '') {
        $sql = " SELECT * FROM $table 
            WHERE gpo_id = :gpo AND ur_id = :ur AND cmp_id = :cmp AND rfc_id = :rfc 
                AND cmp_numOrden = :referencia";
        $conexion->bindParams([
            ':gpo' => $gpo_id,
            ':ur' => $ur_id,
            ':cmp' => $cmp_id,
            ':rfc' => $rfc_id,
            ':referencia' => $referencia
        ]);
    }
    
    $conexion->setSql($sql);
    $conexion->consultar();
    $conexion->desconectar();

    /*------#VALIDAR SI EXISTE UN DOCUMENTO QUE SOLO SE DIFERENCIA POR EL TIPO DE DOCUMENTO  */
    if ($conexion->getNumeroRegistros() > 1) {
        $documento->valido = FALSE;
        $documento->error = 2;
        $documento->mensaje = 'EXISTE MAS DE UN DOCUMENTO DEL PROVEEDOR "' . $rfc_id . '" CON REFERENCIA "' . $referencia . '" POR FAVOR ESPECIFIQUE UN TIPO DE DOCUMENTO';
        $conexion->desconectar();
        unset($conexion);
        return $documento;
    } elseif ($conexion->getNumeroRegistros() == 0) {
        $documento->valido = FALSE;
        $documento->error = 3;
        $documento->mensaje = 'EL DOCUMENTO NO EXISTE CON LOS DATOS PROPORCIONADOS';
        $conexion->desconectar();
        unset($conexion);
        return $documento;
    }

    $dataRow = $conexion->extraer();
    $date = new stdClass;
    $date->gpo_id = $gpo_id;
    $date->ur_id = $ur_id;
    $date->fecha = $dataRow['cmp_fechaOrden'] ? $dataRow['cmp_fechaOrden']: null;
    if ($date->fecha) {
        $fecha = validaFecha($date);
    }

    /*------#DATOS GENERALES DEL DOCUMENTO      */
    $documento->valido = TRUE;
    $documento->gpo_id = $dataRow['gpo_id'];
    $documento->ur_id = $dataRow['ur_id'];
    $documento->cmp_id = $dataRow['cmp_id'];
    $documento->referencia = $dataRow['cmp_numOrden'];
    $documento->tipoMovimiento = $dataRow['cmp_tipoMovimiento'];
    $documento->importe = $dataRow['cmp_importe'];
    $documento->concepto = $dataRow['cmp_concepto'];
    $documento->fechaDocumento = $fecha->fecha;
    $documento->tipoCompromiso = $dataRow['cmp_tipoCompromiso'];
    $documento->compromisoAjustado = $dataRow['cmp_cmp_id'];

    $documento->detalleComprometido[$dataRow['cmp_id']] = new stdClass();

    $date = new stdClass;
    $date->gpo_id = $gpo_id;
    $date->ur_id = $ur_id;
    $date->fecha = $dataRow['cmp_fechaAutorizado'] ? substr($dataRow['cmp_fechaAutorizado'], 0, 10) : null;
    $fecha = new stdClass;
    if ($date->fecha) {
        $fecha = validaFecha($date);
    }

    $documento->detalleComprometido[$dataRow['cmp_id']]->fechaAutorizado = $fecha->fecha;

    $date = new stdClass;
    $date->gpo_id = $gpo_id;
    $date->ur_id = $ur_id;
    $date->fecha = $dataRow['cmp_fechaCancelado'] ? substr($dataRow['cmp_fechaCancelado'], 0, 10) : null;
    $fecha = new stdClass;
    
    if ($date->fecha) {
        $fecha = validaFecha($date);
    }

    $documento->detalleComprometido[$dataRow['cmp_id']]->fechaCancelado = (isset($fecha->fecha) ? $fecha->fecha : '');

    $documento->detalleComprometido[$dataRow['cmp_id']]->estado = ($dataRow['ecm_id'] == 'C' ? $dataRow['ecm_id'] : 'A');
    $documento->detalleComprometido[$dataRow['cmp_id']]->mesAutorizado = $dataRow['msc_id_autorizado'];
    $documento->detalleComprometido[$dataRow['cmp_id']]->mesCancelado = $dataRow['msc_id_cancelado'];
    $documento->detalleComprometido[$dataRow['cmp_id']]->agrupador = $dataRow['gpo_id'] . $dataRow['ur_id'] . $dataRow['msc_id_autorizado'] . 'C' . $dataRow['cmp_tipoCompromiso'] . $dataRow['cmp_id'];
    $documento->detalleComprometido[$dataRow['cmp_id']]->importe = $dataRow['cmp_importe'];

    unset($conexion);
    /*------# DETALLE DEL PROVEEDOR      --------*/
    $sql = "
        SELECT rfc_id, rfc_nombre, rfc_paterno, rfc_materno, tcy_id, soc_nombre 
        FROM sinpres.cat_Proveedores 
            INNER JOIN sinpres.cat_SociedadProveedor USING( soc_id ) 
        WHERE rfc_id = '" . $dataRow['rfc_id'] . "' 
    ";
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar();
    $conexion->desconectar();
    $dataRow = $conexion->extraer();

    $documento->rfc_id = $dataRow['rfc_id'];
    $documento->nombreProveedor = $dataRow['rfc_nombre'] . ($dataRow['tcy_id'] == 1 ? " " . $dataRow['rfc_paterno'] . " " . $dataRow['rfc_materno'] : ", " . $dataRow['soc_nombre']);

    $documento->detalleEjercido = getCompromisoContable($documento->gpo_id, $documento->ur_id, $documento->cmp_id, 'E');

    unset($dataRow);
    unset($conexion);
    return $documento;
}
