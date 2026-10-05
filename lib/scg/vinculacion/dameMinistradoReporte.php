<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function dameMinistradoReporte($gpo_id, $ur_id, $bdo_id,
    $parametroExtra = []) {
    if ($gpo_id == '' || $ur_id == '' || $bdo_id == '') {
        return false;
    }

    $clc_id    = (isset($parametroExtra['clc_id']) ? $parametroExtra['clc_id'] : '');
    $ejercicio = (isset($parametroExtra['ejercicio']) ? $parametroExtra['ejercicio']
        : '');

    if ($ejercicio <= 2019) {
        switch ($ejercicio) {
            case 2016:
                $campo = "t.tmi_id, t.tmi_nombre, m.tdf_id, m.bdm_referencia, m.bdm_concepto, ing_fechaPago AS bdm_fechaAutorizado, '' AS bdm_fechaCancelado, ";
                $tabla = "FROM ctrl" . $ejercicio . ".ing_BancoDocumentoMinistrado m ";
                break;

            default:
                $campo = "t.tmi_id, t.tmi_nombre, m.tdf_id, m.bdm_referencia,
                            case when bdm_tipoAgrupador= 'C' Then c.clc_concepto
                when bdm_tipoAgrupador= 'D' Then bd.bdo_concepto
                 else ''
            END AS bdm_concepto, m.bdm_fechaAutorizado, m.bdm_fechaCancelado, ";
                $tabla = "FROM ".BD_CTRLANT.".min_BancoDocumentoMinistrado m "
                    . "INNER JOIN ctrl" . $ejercicio . ".cat_BancoDocumentos bd USING( gpo_id, ur_id, bdo_id ) ";

                break;
        }
    } else {

        $campo = "t.tmi_id, t.tmi_nombre, m.tdf_id, m.bdm_referencia,
                            case when bdm_tipoAgrupador= 'C' Then c.clc_concepto
                when bdm_tipoAgrupador= 'D' Then bd.bdo_concepto
                else ''
            END AS bdm_concepto, m.bdm_fechaAutorizado, m.bdm_fechaCancelado, ";
        $tabla = "FROM ".BD_TESORERIA.".min_BancoDocumentoMinistrado_$ejercicio m
                        INNER JOIN ctrl" . $ejercicio . ".cat_BancoDocumentos bd USING( gpo_id, ur_id, bdo_id ) ";
    }

    $html   = '';
    $return = new stdClass();

    $params =[];
    $sql = "
        SELECT m.gpo_id, m.ur_id, m.clc_id, m.afe_id, m.bdo_id,
            $campo
            m.bdm_estado, m.bdm_percepcionPresupuestal, m.bdm_percepcionContable, m.bdm_retencionContable,
            m.bdm_percepcionPresupuestal + m.bdm_percepcionContable - m.bdm_retencionContable AS importe,
            i.iba_id, i.iba_nombre, b.bur_cuentaBancaria, b.bur_alias, b.bur_beneficiario
        $tabla
            INNER JOIN ctrl" . $ejercicio . ".scp_Clc c USING( gpo_id, ur_id, clc_id )
            INNER JOIN ctrl" . $ejercicio . ".cat_BancosUr b USING( bur_id )
            INNER JOIN ctrl" . $ejercicio . ".cat_InstitucionesBancarias i USING( iba_id )
            " . (($ejercicio == 2025) ? "INNER JOIN ".BD_TESORERIA.".cat_TipoMinistracion t USING( tmi_id )"
        : (($ejercicio >= 2017 && $ejercicio < 2025) ? "INNER JOIN ".BD_CTRLANT.".cat_TipoMinistracion t USING( tmi_id )"
            :
            "INNER JOIN ".BD_TESORERIA.".cat_TipoMinistracion t USING( tmi_id )")) .
        "
        WHERE m.gpo_id = :gpo_id AND m.ur_id = :ur_id
            AND m.bdo_id = :bdo_id ";
    $params =[
        'gpo_id' => $gpo_id,
        'ur_id' => $ur_id,
        'bdo_id' => $bdo_id
    ];
    if ($ejercicio == 2015) {
        $sql = "
        SELECT n.gpo_id, n.ur_id, n.clc_id, n.afe_id, n.bdo_id,
            1 AS tmi_id, '' AS tmi_nombre, '' AS tdf_id, bdm_referencia, bdm_concepto, bdm_fechaAutorizado, '' AS bdm_fechaCancelado,
            'A' AS bdm_estado, n.bdm_percepcionPresupuestal, n.bdm_percepcionContable, n.bdm_retencionContable,
            n.bdm_percepcionPresupuestal + n.bdm_percepcionContable - n.bdm_retencionContable AS importe,
            i.iba_id, i.iba_nombre, b.bur_cuentaBancaria, b.bur_alias, b.bur_beneficiario
        FROM (
                SELECT n.gpo_id, n.ur_id, n.clc_id, n.afe_id, n.bdo_id, c.bur_id,
                    c.ing_referenciaPago AS bdm_referencia, c.clc_concepto AS bdm_concepto, c.ing_fechaPago AS bdm_fechaAutorizado,
                    SUM(n.ncp_percepcionNeto) AS bdm_percepcionPresupuestal, 0 AS bdm_percepcionContable, 0 AS bdm_retencionContable
                FROM ".BD_CTRL_2015.".scp_Clc c
                    INNER JOIN ".BD_CTRL_2015.".scp_Presupuestal n USING( gpo_id, ur_id, clc_id )
                WHERE n.gpo_id = :gpo_id AND n.ur_id = :ur_id
                    AND n.clc_id = :clc_id
                    AND c.ecl_id IN ( 13, 16 )
            UNION ALL
                SELECT n.gpo_id, n.ur_id, n.clc_id, n.afe_id, n.bdo_id, c.bur_id,
                    c.ing_referenciaPago AS bdm_referencia, c.clc_concepto AS bdm_concepto, c.ing_fechaPago AS bdm_fechaAutorizado,
                    0 AS bdm_percepcionPresupuestal, SUM(n.ncc_percepcion) AS bdm_percepcionContable, SUM(n.ncc_retencion) AS bdm_retencionContable
                FROM ".BD_CTRL_2015.".scp_Clc c
                    INNER JOIN ".BD_CTRL_2015.".scp_Contable n USING( gpo_id, ur_id, clc_id )
                WHERE n.gpo_id = :gpo_id AND n.ur_id = :ur_id
                    AND n.clc_id = :clc_id
                    AND c.ecl_id IN ( 13, 16 )
            )n
            INNER JOIN ".BD_CTRL_2015.".cat_BancosUr b USING( bur_id )
            INNER JOIN ".BD_CTRL_2015.".cat_InstitucionesBancarias i USING( iba_id )
    ";
    $params =[
        'gpo_id' => $gpo_id,
        'ur_id' => $ur_id,
        'clc_id' => $clc_id
    ];
    }

    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($params);
    $dataRow = $conexion->extraer();

    $date            = new stdClass;
    $date->gpo_id    = $dataRow['gpo_id'];
    $date->ur_id     = $dataRow['ur_id'];
    $date->fecha     = $dataRow['bdm_fechaAutorizado'];
    $fechaAutorizado = new stdClass;
    $fechaAutorizado = validaFecha($date);
    $date            = new stdClass;
    $date->gpo_id    = $dataRow['gpo_id'];
    $date->ur_id     = $dataRow['ur_id'];
    $date->fecha     = $dataRow['bdm_fechaCancelado'];
    $fechaCancelado  = new stdClass;
    $fechaCancelado  = validaFecha($date);
    $bdmReferencia   = str_replace(" ", "", $dataRow['bdm_referencia']);

    $importeMinistrado = ($dataRow['bdm_percepcionPresupuestal'] + $dataRow['bdm_percepcionContable']
         - $dataRow['bdm_retencionContable']);
    $datos = [];
    $datos = [
        'id'               => $dataRow['gpo_id'] . $dataRow['ur_id'] . ' - ' . $dataRow['clc_id'],
        'TipoMinistración' => $dataRow['tmi_id'] . ' - ' . $dataRow['tmi_nombre'],
        'Referencia'       => $dataRow['tdf_id'] . ' - ' . $dataRow['bdm_referencia'],
        'Concepto'         => $dataRow['bdm_concepto'],
        'FechaMinistrado'  => ($fechaAutorizado->valido ? $fechaAutorizado->fecha
            : ''),
        'FechaCancelado'   => ($fechaCancelado->valido ? $fechaCancelado->fecha : ''),
        'Banco'            => $dataRow['iba_id'] . ' - ' . $dataRow['iba_nombre'],
        'Cuenta'           => $dataRow['bur_cuentaBancaria'] . ' - ' . $dataRow['bur_alias'],
        'Beneficiario'     => $dataRow['bur_beneficiario'],
        'Importe'          => number_format($importeMinistrado, 2),
    ];
    while ($dataRow = $conexion->extraer()) {

        $importeMinistrado += ($dataRow['bdm_percepcionPresupuestal'] + $dataRow['bdm_percepcionContable']
             - $dataRow['bdm_retencionContable']);
    }

    $datos['importe'] = number_format($importeMinistrado, 2);

    $conexion->desconectar();
    unset($conexion);

    return $datos;
}
