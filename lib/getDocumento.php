<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once dirname(__FILE__) . '/scg/getDocumentoContable.php';
require_once dirname(__FILE__) . '/scg/getDocumentoContableTercero.php';
require_once dirname(__FILE__) . '/scg/getDocumentoContable2015.php';
require_once dirname(__FILE__) . '/scg/getDocumentoContable2016.php';
require_once dirname(__FILE__) . '/scg/getDocumentoContable2017.php';
require_once dirname(__FILE__) . '/scg/getDocumentoContableAnteriores.php';
require_once dirname(__FILE__) . '/getDocumentoRectificado.php';
require_once dirname(__FILE__) . '/validaFecha.php';

function getDocumento($gpo_id, $ur_id, $bdo_id = '', $rfc_id = '', $referencia = '', $parametroExtra = array())
{
    $tipo = (isset($parametroExtra['tdf_id']) ? $parametroExtra['tdf_id'] : '');
    $ejercicio = (isset($parametroExtra['ejercicio']) ? $parametroExtra['ejercicio'] : '');

    if ($bdo_id == '') {
        return false;
    }

    $table = "ctrl{$ejercicio}.cat_BancoDocumentos";
    $documento = new stdClass();

    /* ------#VALIDAR QUE ENVIEN EL DOCUMENTO Ó (RFC Y REFERENCIA) */
    if ($bdo_id == '' && ($rfc_id == '' || $referencia == '')) {
        $documento->valido = false;
        $documento->error = 1;
        $documento->mensaje = 'NO ES POSIBLE VALIDAR EL DOCUMENTO FALTA LA REFERENCIA O EL RFC';
        return $documento;
    }

    $parametrosSql = [];

    $sql = "SELECT * FROM {$table} WHERE ";
    if ($bdo_id == '' && $rfc_id != '' && $referencia != '') {
        $sql .= " gpo_id = :gpo AND ur_id = :ur AND rfc_id = :rfc AND bdo_referencia = :referencia ";
        $parametrosSql = [
            ':gpo' => $gpo_id,
            ':ur' => $ur_id,
            ':rfc' => $rfc_id,
            ':referencia' => $referencia
        ];

        if ($tipo != '') {
            $sql .= " AND tdf_id = :tdf ";
            $parametrosSql[':tdf'] = $tipo;
        }
    } elseif ($bdo_id != '' && ($rfc_id == '' || $referencia == '') && ($gpo_id == '' || $ur_id == '')) {
        $sql .= "bdo_id = :bdo ";
        $parametrosSql[':bdo'] = $bdo_id;
    } elseif ($bdo_id != '' && ($rfc_id == '' || $referencia == '')) {
        $sql .= " bdo_id = :bdo ";
        $parametrosSql[':bdo'] = $bdo_id;
    } elseif ($bdo_id != '' && $rfc_id != '' && $referencia != '') {
        $sql .= " gpo_id = :gpo AND ur_id = :ur AND bdo_id = :bdo 
            AND rfc_id = :rfc AND bdo_referencia = :referencia ";
        $parametrosSql = [
            ':gpo' => $gpo_id,
            ':ur' => $ur_id,
            ':bdo' => $bdo_id,
            ':rfc' => $rfc_id,
            ':referencia' => $referencia
        ];
    }
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($parametrosSql);
    $conexion->desconectar();

    /* ------#VALIDAR SI EXISTE UN DOCUMENTO QUE SOLO SE DIFERENCIA POR EL TIPO DE DOCUMENTO  */
    if ($conexion->getNumeroRegistros() > 1) {
        $documento->valido = false;
        $documento->error = 2;
        $documento->mensaje = 'EXISTE MAS DE UN DOCUMENTO DEL PROVEEDOR "' . $rfc_id 
            . '" CON REFERENCIA "' . $referencia . '" POR FAVOR ESPECIFIQUE UN TIPO DE DOCUMENTO';
        $conexion->desconectar();
        unset($conexion);
        return $documento;
    } elseif ($conexion->getNumeroRegistros() == 0) {
        $documento->valido = false;
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

    /* ------#DATOS GENERALES DEL DOCUMENTO      */
    $documento->valido = true;
    $documento->gpo_id = $dataRow['gpo_id'];
    $documento->ur_id = $dataRow['ur_id'];
    $documento->bdo_id = $dataRow['bdo_id'];
    $documento->sts_id = $dataRow['sts_id'];
    $documento->referencia = $dataRow['bdo_referencia'];
    $documento->importe = $dataRow['bdo_percepcionPresupuestal'] + $dataRow['bdo_percepcionContable'] - $dataRow['bdo_retencionContable'];
    $documento->concepto = $dataRow['bdo_concepto'];
    $documento->tipoDocumentoFuente = $dataRow['tdf_id'];
    $documento->percepcionPresupuestal = $dataRow['bdo_percepcionPresupuestal'];
    $documento->percepcionContable = $dataRow['bdo_percepcionContable'];
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

    /* ------# DETALLE DEL PROVEEDOR      -------- */
    $sql = "SELECT rfc_id, rfc_nombre, rfc_paterno, rfc_materno, tcy_id, soc_nombre
            FROM ".BD_SINPRES.".cat_Proveedores
                INNER JOIN sinpres.cat_SociedadProveedor USING(soc_id)
            WHERE rfc_id = ? ";
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar([$dataRow['rfc_id']]);
    $conexion->desconectar();
    $dataRow = $conexion->extraer();
    if(isset($dataRow['rfc_id'])){
        $documento->rfc_id = $dataRow['rfc_id'];
    }else{
        $documento->rfc_id = "";
    }
    if(isset($dataRow['rfc_nombre']) and isset($dataRow['rfc_paterno']) and isset($dataRow['rfc_materno']) and isset($dataRow['tcy_id']) and isset($dataRow['soc_nombre'])){
        $documento->nombreProveedor = $dataRow['rfc_nombre'] . ($dataRow['tcy_id'] == 1 || $dataRow['tcy_id'] == 4 ? " " . $dataRow['rfc_paterno'] . " " . $dataRow['rfc_materno'] : ($dataRow['tcy_id'] == 2 ? ", " . $dataRow['soc_nombre'] : ""));
    }else{
        $documento->nombreProveedor = "";
    }
    unset($dataRow);
    unset($conexion);

    $afectacion = isset($parametroExtra['afe_id']) ? $parametroExtra['afe_id'] : '';
    if (isset($parametroExtra['mmc_id']))
        switch ($parametroExtra['mmc_id']) {
            case 'D':
                $documento->detalleDevengado = getDocumentoContable(
                    $documento->gpo_id,
                    $documento->ur_id,
                    $documento->bdo_id,
                    $parametroExtra['mmc_id'],
                    array('afe_id' => $afectacion)
                );
                break;
            case 'E':
                $documento->detalleEjercido = getDocumentoContable(
                    $documento->gpo_id,
                    $documento->ur_id,
                    $documento->bdo_id,
                    $parametroExtra['mmc_id'],
                    array('afe_id' => $afectacion)
                );
                break;
            case 'G':
                $documento->detalleCereCo = getDocumentoContable(
                    $documento->gpo_id,
                    $documento->ur_id,
                    $documento->bdo_id,
                    $parametroExtra['mmc_id'],
                    array('afe_id' => $afectacion)
                );
                break;
            case 'N':
                $documento->detalleProvision = getDocumentoContable(
                    $documento->gpo_id,
                    $documento->ur_id,
                    $documento->bdo_id,
                    $parametroExtra['mmc_id'],
                    array('afe_id' => $afectacion)
                );
                break;
            case 'T':
            case 'B':
            case 'P':
                $property = $parametroExtra['mmc_id'] == 'P' ? "detallePagado" : "detalleMinistrado";
                $function = "getDocumentoContable";
                if ($ejercicio < 2018) {
                    $property .= $ejercicio;
                    $function .= $ejercicio;
                } else if($ejercicio < APP_EJERCICIO) {
                    $property .= $ejercicio;
                    $function .= "Anteriores";
                }
                $documento->$property = $function(
                    $documento->gpo_id,
                    $documento->ur_id,
                    $documento->bdo_id,
                    $parametroExtra['mmc_id'],
                    array("ejercicio" => $ejercicio,'afe_id' => $afectacion)
                );
                break;
            case 'R':
                $documento->detalleRectificado = getDocumentoRectificado(
                    $documento->gpo_id,
                    $documento->ur_id,
                    $documento->bdo_id,
                    'R'
                );
                break;
            case 'K':
                $documento->detalleRectificado = getDocumentoRectificado(
                    $documento->gpo_id,
                    $documento->ur_id,
                    $documento->bdo_id,
                    'K'
                );
                break;
            default:
                $documento->detalleDevengado = getDocumentoContable(
                    $documento->gpo_id,
                    $documento->ur_id,
                    $documento->bdo_id,
                    'D',
                    array('afe_id' => $afectacion)
                );
                $documento->detalleEjercido = getDocumentoContable(
                    $documento->gpo_id,
                    $documento->ur_id,
                    $documento->bdo_id,
                    'E',
                    array('afe_id' => $afectacion)
                );
                $documento->detalleCereCo = getDocumentoContable(
                    $documento->gpo_id,
                    $documento->ur_id,
                    $documento->bdo_id,
                    'G',
                    array('afe_id' => $afectacion)
                );
                switch ($ejercicio) {
                    case 2016:
                        $documento->detalleMinistrado2016 = getDocumentoContable2016(
                            $documento->gpo_id,
                            $documento->ur_id,
                            $documento->bdo_id,
                            'T',
                            array("ejercicio" => 2016)
                        );
                        $documento->detallePagado2016 = getDocumentoContable2016(
                            $documento->gpo_id,
                            $documento->ur_id,
                            $documento->bdo_id,
                            'P',
                            array("ejercicio" => 2016)
                        );
                        break;
                    default:
                        $documento->detalleMinistrado = getDocumentoContable(
                            $documento->gpo_id,
                            $documento->ur_id,
                            $documento->bdo_id,
                            'T',
                            array('afe_id' => $afectacion)
                        );
                        $documento->detallePagado = getDocumentoContable(
                            $documento->gpo_id,
                            $documento->ur_id,
                            $documento->bdo_id,
                            'P',
                            array('afe_id' => $afectacion)
                        );
                        break;
                }
                break;
        }

    if (!isset($documento->detalleRectificado)) {
        $documento->detalleRectificado = getDocumentoRectificado($documento->gpo_id, $documento->ur_id, $documento->bdo_id, 'R');
    }

    if ($documento->detalleRectificado) {
        $documento->rectificaciones = count($documento->detalleRectificado);
        foreach ($documento->detalleRectificado as $numId => $detalle) {
            $documento->rectificacionPresupuestal += $detalle->rectificadoPresupuestal;
            $documento->rectificacionPercepcionContable += $detalle->rectificadoPercepcion;
            $documento->rectificacionRetencionContable += $detalle->rectificadoRetencion;
            /**
             * WARNING
             * codigo viejo que no tiene sentido, en caso de fallo revertir!!!! 
             */
            /*
            $documento->rectificacionPresupuestal += round($dataRow['rectificadoPresupuestal'], 2);
            $documento->rectificacionPercepcionContable += round($dataRow['rectificadoPercepcion'], 2);
            $documento->rectificacionRetencionContable += round($dataRow['rectificadoRetencion'], 2);
    
            if ($dataRow['eaf_id'] == 'A') {
                $documento->devengado += round($dataRow['percepcionpresupuestal'] - $dataRow['rectificacionPresupuestal'], 2);
                $documento->retenD += round($dataRow['retencionContable'] - $dataRow['rectificacionRetencionContable'], 2);
                $documento->perceD += round($dataRow['percepcionContable'] - $dataRow['rectificacionPercepcionContable'], 2);
            }*/
        }
    }

    return $documento;
}
