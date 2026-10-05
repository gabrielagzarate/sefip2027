<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once dirname(__FILE__) . '/scg/getDocumentoContableTercero.php';
require_once dirname(__FILE__) . '/scg/getDocumentoContableTercero2016.php';
require_once dirname(__FILE__) . '/scg/getDocumentoContableTercero2017.php';
require_once dirname(__FILE__) . '/scg/getDocumentoContableTerceroAnteriores.php';
require_once dirname(__FILE__) . '/getDocumentoRectificado.php';
require_once dirname(__FILE__) . '/validaFecha.php';

function getDocumentoTercero($gpo_id, $ur_id, $bdo_id = '', $rfc_id = '', $referencia = '', $parametroExtra = array())
{
    $tipo = (isset($parametroExtra['tdf_id']) ? $parametroExtra['tdf_id'] : '');
    $ejercicio = (isset($parametroExtra['ejercicio']) ? $parametroExtra['ejercicio'] : APP_EJERCICIO);

    if ($bdo_id == '') {
        return false;
    }
    $table = "ctrl{$ejercicio}.cat_BancoDocumentos b ";

    $documento = new stdClass();

    /*------#VALIDAR QUE ENVIEN EL DOCUMENTO Ó ( RFC Y REFERENCIA )*/
    if ($bdo_id == '' && ($rfc_id == '' || $referencia == '')) {
        $documento->valido = false;
        $documento->error = 1;
        $documento->mensaje = 'NO ES POSIBLE VALIDAR EL DOCUMENTO FALTA LA REFERENCIA O EL RFC';
        return $documento;
    }

    $sqlParametros = [];
    if ($bdo_id == '' && $rfc_id != '' && $referencia != '') {
        $sqlParametros = [
            ':nccgpo' => $parametroExtra['gpo_id_origen'],
            ':nccur' => $parametroExtra['ur_id_origen'],
            ':gpo' => $gpo_id,
            ':ur' => $ur_id,
            ':rfc' => $rfc_id,
            ':referencia' => $referencia,
        ];
        $sql =
            "SELECT b.gpo_id, b.ur_id, b.bdo_id, b.sts_id, b.bdo_referencia, date_format(b.usr_fecha,'%Y-%m-%d') AS bdo_fechaDocumento, 
                0 AS bdo_percepcionPresupuestal, SUM(ncc_percepcion) AS bdo_percepcionContable, 
                SUM(ncc_retencion) AS bdo_retencionContable, 
                b.bdo_concepto, b.tdf_id, b.bdo_fondoRotatorio, bdo_rectificaciones, bdo_estadoSat, b.rfc_id 
            FROM {$table}
            LEFT JOIN (
                SELECT ncc_gpo, ncc_ur, bdo_id, ncc_percepcion, ncc_retencion 
                FROM ctrl{$ejercicio}.scp_Contable
                WHERE ncc_gpo = :nccgpo
                AND ncc_ur = :nccur
            ) n USING( bdo_id ) 
            WHERE b.gpo_id = :gpo
            AND b.ur_id = :ur
            AND b.rfc_id = :rfc
            AND b.bdo_referencia = :referencia
        ";
        if ($tipo != '') {
            $sql .= " AND b.tdf_id = :tipo ";
            $sqlParametros[':tipo'];
        }
    } else if ($bdo_id != '' && ($rfc_id == '' || $referencia == '') && ($gpo_id == '' || $ur_id == '')) {
        $sqlParametros = [
            ':nccgpo' => $parametroExtra['gpo_id_origen'],
            ':nccur' => $parametroExtra['ur_id_origen'],
            ':bdo' => $bdo_id,
        ];
        $sql =
            "SELECT b.gpo_id, b.ur_id, b.bdo_id, b.sts_id, b.bdo_referencia, date_format(b.usr_fecha,'%Y-%m-%d') AS bdo_fechaDocumento, 
                0 AS bdo_percepcionPresupuestal, SUM(ncc_percepcion) AS bdo_percepcionContable, 
                SUM(ncc_retencion) AS bdo_retencionContable, b.bdo_concepto, b.tdf_id, 
                b.bdo_fondoRotatorio, bdo_rectificaciones, bdo_estadoSat, b.rfc_id 
            FROM {$table} 
            LEFT JOIN (
                SELECT ncc_gpo, ncc_ur, bdo_id, ncc_percepcion, ncc_retencion 
                FROM ctrl{$ejercicio}.scp_Contable
                WHERE ncc_gpo = :nccgpo
                AND ncc_ur = :nccur
            ) n USING( bdo_id ) 
            WHERE b.bdo_id =:bdo";

    } else if ($bdo_id != '' && ($rfc_id == '' || $referencia == '')) {
        $sqlParametros = [
            ':nccgpo' => $parametroExtra['gpo_id_origen'],
            ':nccur' => $parametroExtra['ur_id_origen'],
            ':gpo' => $gpo_id,
            ':ur' => $ur_id,
            ':bdo' => $bdo_id,
        ];
        $sql =
            "SELECT b.gpo_id, b.ur_id, b.bdo_id, b.sts_id, b.bdo_referencia, date_format(b.usr_fecha,'%Y-%m-%d') AS bdo_fechaDocumento, 
                0 AS bdo_percepcionPresupuestal, SUM(ncc_percepcion) AS bdo_percepcionContable, 
                SUM(ncc_retencion) AS bdo_retencionContable, b.bdo_concepto, b.tdf_id, 
                b.bdo_fondoRotatorio, bdo_rectificaciones, bdo_estadoSat, b.rfc_id 
            FROM {$table}
            LEFT JOIN (
                SELECT ncc_gpo, ncc_ur, bdo_id, ncc_percepcion, ncc_retencion 
                FROM ctrl{$ejercicio}.scp_Contable
                WHERE ncc_gpo = :nccgpo
                AND ncc_ur = :nccur
            ) n USING( bdo_id )
            WHERE  b.gpo_id = :gpo
            AND b.ur_id = :ur
            AND b.bdo_id = :bdo";
    } else if ($bdo_id != '' && $rfc_id != '' && $referencia != '') {
        $sqlParametros = [
            ':nccgpo' => $parametroExtra['gpo_id_origen'],
            ':nccur' => $parametroExtra['ur_id_origen'],
            ':gpo' => $gpo_id,
            ':ur' => $ur_id,
            ':rfc' => $rfc_id,
            ':bdo' => $bdo_id,
            ':referencia' => $referencia,
        ];
        $sql = 
            "SELECT b.gpo_id, b.ur_id, b.bdo_id, b.sts_id, b.bdo_referencia, date_format(b.usr_fecha,'%Y-%m-%d') AS bdo_fechaDocumento, 
                0 AS bdo_percepcionPresupuestal, SUM(ncc_percepcion) AS bdo_percepcionContable, 
                SUM(ncc_retencion) AS bdo_retencionContable, b.bdo_concepto, b.tdf_id, 
                b.bdo_fondoRotatorio, bdo_rectificaciones, bdo_estadoSat, b.rfc_id 
            FROM $table 
            LEFT JOIN (
                SELECT ncc_gpo, ncc_ur, bdo_id, ncc_percepcion, ncc_retencion 
                FROM ctrl{$ejercicio}.scp_Contable
                WHERE ncc_gpo = :nccgpo
                AND ncc_ur = :nccur
            ) n USING( bdo_id ) 
            WHERE b.gpo_id = :gpo
            AND b.ur_id = :ur
            AND b.bdo_id = :bdo
            AND b.rfc_id = :rfc
            AND b.bdo_referencia = :referencia
        ";
    }
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->bindParams($sqlParametros);
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
    unset($conexion);
    $date = new stdClass;
    $date->gpo_id = $dataRow['gpo_id'];
    $date->ur_id = $dataRow['ur_id'];
    $date->fecha = $dataRow['bdo_fechaDocumento'];
    $fecha = validaFecha($date);

    /*------#DATOS GENERALES DEL DOCUMENTO      */
    $documento->valido = TRUE;
    $documento->gpo_id = $dataRow['gpo_id'];
    $documento->ur_id = $dataRow['ur_id'];
    $documento->bdo_id = $dataRow['bdo_id'];
    $documento->sts_id = $dataRow['sts_id'];
    $documento->referencia = $dataRow['bdo_referencia'];
    $documento->importe = $dataRow['bdo_percepcionPresupuestal'] + $dataRow['bdo_percepcionContable'] - $dataRow['bdo_retencionContable'];
    $documento->concepto = $dataRow['bdo_concepto'];
    $documento->tipoDocumentoFuente  = $dataRow['tdf_id'];
    $documento->percepcionPresupuestal = $dataRow['bdo_percepcionPresupuestal'];
    $documento->percepcionContable  = $dataRow['bdo_percepcionContable'];
    $documento->retencionContable = $dataRow['bdo_retencionContable'];
    $documento->fechaDocumento = $fecha->fecha;
    $documento->fondoRotatorio = $dataRow['bdo_fondoRotatorio'];
    $documento->rectificaciones = $dataRow['bdo_rectificaciones'];
    $documento->estadoSat = $dataRow['bdo_estadoSat'];
    $documento->retenciones = 0;
    $documento->impuestos = 0;
    $documento->referenciado = 0;
    $documento->rectificacionPresupuestal = 0;
    $documento->rectificacionPercepcionContable = 0;
    $documento->rectificacionRetencionContable = 0;

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
    $documento->nombreProveedor = $dataRow['rfc_nombre'] . ($dataRow['tcy_id'] == 1 || $dataRow['tcy_id'] == 4 ? " " . $dataRow['rfc_paterno'] . " " . $dataRow['rfc_materno'] : ($dataRow['tcy_id'] == 2 ? ", " . $dataRow['soc_nombre'] : ""));

    unset($conexion);

    $property = "detallePagadoTercero";
    $function = "getDocumentoContableTercero";
    switch (true) {
        case $ejercicio < 2018:
            $property .= $ejercicio;
            $function .= $ejercicio;
            break;
        case $ejercicio < APP_EJERCICIO:
            $property .= $ejercicio;
            $function .= 'Anteriores';
            break;
    }
    $parametroExtra['ejercicio'] = $ejercicio;
    $documento->$property = $function($documento->gpo_id, $documento->ur_id, $documento->bdo_id, $parametroExtra);
    $documento->detalleRectificado = getDocumentoRectificado($documento->gpo_id, $documento->ur_id, $documento->bdo_id, 'R');

    if ($documento->detalleRectificado) {
        $documento->rectificaciones = count($documento->detalleRectificado);
        foreach($documento->detalleRectificado  as $detalle) {
            $documento->rectificacionPresupuestal += round($detalle->rectificadoPresupuestal, 2);
            $documento->rectificacionPercepcionContable += round($detalle->rectificadoPercepcion, 2);
            $documento->rectificacionRetencionContable += round($detalle->rectificadoRetencion, 2);
        }
        /* 
        $documento->importeRectificado += round($dataRow['rectificacionPresupuestal'], 2);
        $documento->importeRectificadoRetencion += round($dataRow['rectificacionRetencionContable'], 2);
        $documento->importeRectificadoPercepcion += round($dataRow['rectificacionPercepcionContable'], 2);

        if ($dataRow['eaf_id'] == 'A') {
            $documento->devengado += round($dataRow['percepcionpresupuestal'] - $dataRow['rectificacionPresupuestal'], 2);
            $documento->retenD += round($dataRow['retencionContable'] - $dataRow['rectificacionRetencionContable'], 2);
            $documento->perceD += round($dataRow['percepcionContable'] - $dataRow['rectificacionPercepcionContable'], 2);
        } */
    }

    return $documento;
}
