<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/../../../compararCadenas.php';
require_once dirname(__FILE__) . '/../../../redondear.php';

function vinculacionPagadoTercero2022($gpo_id, $ur_id, $ue_id, $pot_id,
    $periodo, $programatico,
    $claveFinanciamiento, $reporte, $detalle,
    $mmc_id, $niveles,
    $parametroExtra = array()) {


    $sqlFiltro = '';
    if ($parametroExtra['gur'] != '') {
        $sqlFiltro = "n.cta_gpo_id = " . substr($parametroExtra['gur'], 0, 1) . " AND n.cta_ur_id = " . substr($parametroExtra['gur'],
            1, 2) . " ";

    } else {
        $sqlFiltro = "n.cta_gpo_id  = '' AND n.cta_ur_id =''  ";
    }

    if ($parametroExtra['clc_id'] != '') {
        $sqlFiltro .= (($sqlFiltro != '' ? "AND " : "") . "t.clc_id = " . $parametroExtra['clc_id'] . " ");
    }

    if ($parametroExtra['bdo_id'] != '') {
        $sqlFiltro .= ($sqlFiltro != '' ? "AND " : "") . "t.bdo_id = " . $parametroExtra['bdo_id'] . " ";
    }

    if ($parametroExtra['rfc_id'] != '') {
        $sqlFiltro .= ($sqlFiltro != '' ? "AND " : "") . "b.rfc_id = '" . $parametroExtra['rfc_id'] . "' ";
    }

    if ($parametroExtra['bdo_referencia'] != '') {
        $sqlFiltro .= ($sqlFiltro != '' ? "AND " : "") . "b.bdo_referencia = '" . $parametroExtra['bdo_referencia'] . "' ";
    }

    if ($periodo['mesDesde'] != '') {
        $sqlFiltro .= ($sqlFiltro != '' ? "AND " : "") . "t.msc_id = " . $periodo['mesDesde'] . " ";
    }
    $sqlFiltro .= ($sqlFiltro != '' ? "AND " : "") . "ejr_id_clc=2021  AND ejr_id_autorizado = 2022 ";
    $sql = "
            SELECT
                t.gpo_id, t.ur_id, MAX(t.clc_id)AS clc_id, MAX(t.afe_id) AS afe_id, group_concat(distinct t.afe_id) as stringafec,
                t.bdo_id, t.tdf_id, t.rfc_id, t.bdo_referencia, t.bdo_concepto,
                MAX(v.tdf_id) AS tdf_id_pagado, MAX(v.bdp_referencia) AS bdp_referencia,
                SUM(t.bdo_percepcionPresupuestal) AS bdo_percepcionPresupuestal, SUM(t.bdo_percepcionContable) AS bdo_percepcionContable, SUM(t.bdo_retencionContable) AS bdo_retencionContable,
                MAX(v.msc_id) msc_id,
                MAX(v.bdp_estado) AS estado, DATE( MAX(v.bdp_fechaAutorizado) ) AS fechaAutorizacion, DATE( MAX(v.bdp_fechaCancelado) ) AS fechaCancelacion,
                MAX(v.bdp_agrupador) AS agrupador,

                pa.polizaAutorizado, MAX(pa.fechaAutorizado) as fechaAutorizado,
                MAX(pa.gpo_id_polizaAutorizado) AS gpo_id_polizaAutorizado, MAX(pa.ur_id_polizaAutorizado) AS ur_id_polizaAutorizado, MAX(pa.ue_id_polizaAutorizado) AS ue_id_polizaAutorizado,
                MAX(pa.msc_id_polizaAutorizado) AS msc_id_polizaAutorizado, MAX(pa.pot_id_polizaAutorizado) AS pot_id_polizaAutorizado, MAX(pa.prf_id_polizaAutorizado) AS prf_id_polizaAutorizado,
                MAX(pa.pma_id_polizaAutorizado) AS pma_id_polizaAutorizado,

                pc.polizaCancelado, max(pc.fechaCancelado) AS fechaCancelado,
                max(pc.gpo_id_polizaCancelado) AS gpo_id_polizaCancelado, max(pc.ur_id_polizaCancelado) AS ur_id_polizaCancelado, max(pc.ue_id_polizaCancelado) AS ue_id_polizaCancelado,
                max(pc.msc_id_polizaCancelado) AS msc_id_polizaCancelado, max(pc.pot_id_polizaCancelado) AS pot_id_polizaCancelado, max(pc.prf_id_polizaCancelado) AS  prf_id_polizaCancelado,
                max(pc.pma_id_polizaCancelado) AS pma_id_polizaCancelado,

                CONCAT( t.gpo_id, t.ur_id, '.', LPAD( t.msc_id, 2, 000 ), '.', LPAD( MAX(t.afe_id), 6, 0000000 ), '.', LPAD( t.bdo_id, 6, 000000 ) ) AS id
            FROM (
                SELECT
                    t.gpo_id, t.ur_id, MAX(t.clc_id) AS clc_id, (t.afe_id) AS afe_id, t.msc_id,
                    t.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia, b.bdo_concepto,
                    SUM(t.bdm_percepcionPresupuestal) AS bdo_percepcionPresupuestal, SUM(t.bdm_percepcionContable) AS bdo_percepcionContable, SUM(t.bdm_retencionContable) AS bdo_retencionContable,
                    IFNULL(o.gpo_id, n.gpo_id) AS gpo_id_origen, IFNULL(o.ur_id, n.ur_id) AS ur_id_origen, IFNULL(MAX(o.clc_id), MAX(n.clc_id)) AS
                    clc_id_origen, IFNULL(MAX(o.afe_id), MAX(n.afe_id)) AS afe_id_origen, IFNULL(o.bdo_id, n.bdo_id) AS bdo_id_origen,ejr_id_clc
                FROM ctrl2022.cat_BancoDocumentos b
                    INNER JOIN ctrlAnterior.min_BancoDocumentoMinistrado t USING( gpo_id, ur_id, bdo_id )
                    INNER JOIN ctrl2022.scp_Contable n USING(gpo_id, ur_id, clc_id,afe_id,bdo_id)
                    INNER JOIN ctrlAnterior.scp_Clc ac USING(ejr_id_clc, gpo_id, ur_id, clc_id )
                    INNER JOIN ctrl2022.scp_Clc c USING( gpo_id, ur_id, clc_id )
                    LEFT JOIN ctrl2022.scp_Contable o ON n.ncc_gpo = o.gpo_id AND n.ncc_ur = o.ur_id AND n.ncc_clc = o.clc_id AND n.ncc_nccamortiza = o.ncc_id
                WHERE $sqlFiltro
                    AND c.tcl_id = 'C'
                GROUP BY gpo_id, ur_id, afe_id,bdo_id, gpo_id_origen, ur_id_origen, bdo_id_origen,ejr_id_clc,ejr_id_autorizado
            )t
                LEFT JOIN ctrlAnterior.cg_BancoDocumentoPagadoContable v USING( ejr_id_clc,gpo_id, ur_id, afe_id,bdo_id, gpo_id_origen, ur_id_origen, afe_id_origen, bdo_id_origen )
                LEFT JOIN(
                    SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado, m.ue_id AS ue_id_polizaAutorizado,
                        m.msc_id AS msc_id_polizaAutorizado, m.pot_id AS pot_id_polizaAutorizado, m.prf_id AS prf_id_polizaAutorizado,
                        m.pma_id AS pma_id_polizaAutorizado, m.pma_fecha AS fechaAutorizado,
                        CONCAT( m.pot_id, '-', m.prf_id, m.pma_id ) AS polizaAutorizado,
                        x.pdx_gpo_id_origen AS gpo_id_origen, x.pdx_ur_id_origen AS ur_id_origen,
                        x.pdx_afe_id_origen AS afe_id_origen, x.pdx_bdo_id_origen AS bdo_id_origen,
                        x.pdx_agrupador AS bdp_agrupador
                    FROM cg_PolizaMaestro m
                        INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = $gpo_id AND m.ur_id = $ur_id
                        AND m.mmc_id = '$mmc_id'
                        AND m.tmc_id = 'A'
                        AND x.pdx_ejercicio = 2022
                GROUP BY gpo_id_origen, ur_id_origen, afe_id_origen, bdo_id_origen, bdp_agrupador,ue_id_polizaAutorizado,msc_id_polizaAutorizado,
                    pot_id_polizaAutorizado,prf_id_polizaAutorizado,pma_id_polizaAutorizado
                )pa USING( gpo_id_origen, ur_id_origen, afe_id_origen, bdo_id_origen, bdp_agrupador )
                LEFT JOIN (
                    SELECT m.gpo_id AS gpo_id_polizaCancelado, m.ur_id AS ur_id_polizaCancelado, m.ue_id AS ue_id_polizaCancelado,
                        m.msc_id AS msc_id_polizaCancelado, m.pot_id AS pot_id_polizaCancelado, m.prf_id AS prf_id_polizaCancelado,
                        m.pma_id AS pma_id_polizaCancelado, m.pma_fecha AS fechaCancelado,
                        CONCAT( m.pot_id, '-', m.prf_id, m.pma_id ) AS polizaCancelado,
                        x.pdx_gpo_id_origen AS gpo_id_origen, x.pdx_ur_id_origen AS ur_id_origen,
                        x.pdx_afe_id_origen AS afe_id_origen, x.pdx_bdo_id_origen AS bdo_id_origen,
                        x.pdx_agrupador AS bdp_agrupador
                    FROM cg_PolizaMaestro m
                        INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = $gpo_id AND m.ur_id = $ur_id
                        AND m.mmc_id = '$mmc_id'
                        AND m.tmc_id = 'C'
                        AND x.pdx_ejercicio = 2022
                    GROUP BY gpo_id_origen, ur_id_origen, afe_id_origen, bdo_id_origen, bdp_agrupador,ue_id_polizaCancelado ,msc_id_polizaCancelado,
                    pot_id_polizaCancelado,prf_id_polizaCancelado,pma_id_polizaCancelado
                )pc USING( gpo_id_origen, ur_id_origen, afe_id_origen, bdo_id_origen, bdp_agrupador )
            GROUP BY ejr_id_clc,gpo_id,ur_id, bdo_id,ejr_id_autorizado, bdp_agrupador,polizaAutorizado,polizaCancelado
        ";


    $conexion = new AccesoDatos(); //variable de konexion para los saldos iniciales
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar();
    $conexion->desconectar();

    $datos = array();

    while ($rowData = $conexion->extraer()) {
        $datos[$rowData['id']] = array(
            'id' => $rowData['id'],
            'gpo_id' => $rowData['gpo_id'],
            'ur_id' => $rowData['ur_id'],
            'gur' => $rowData['gpo_id'] . $rowData['ur_id'],
            'clc_id' => $rowData['clc_id'],
            'afe_id' => $rowData['afe_id'],
            'bdo_id' => $rowData['bdo_id'],
            'tdf_id' => $rowData['tdf_id'],
            'rfc_id' => $rowData['rfc_id'],
            'bdo_referencia' => $rowData['bdo_referencia'],
            'bdo_concepto' => $rowData['bdo_concepto'],
            'bdo_importe' => $rowData['bdo_percepcionPresupuestal'] + $rowData['bdo_percepcionContable'] - $rowData['bdo_retencionContable'],
            'estado' => $rowData['estado'],
            'tdf_id_pagado' => $rowData['tdf_id_pagado'],
            'bdp_referencia' => $rowData['bdp_referencia'],
            'fechaAutorizacion' => $rowData['fechaAutorizacion'],
            'fechaCancelacion' => $rowData['fechaCancelacion'],
            'polizaAutorizado' => $rowData['polizaAutorizado'],
            'fechaAutorizado' => $rowData['fechaAutorizado'],
            'polizaCancelado' => $rowData['polizaCancelado'],
            'fechaCancelado' => $rowData['fechaCancelado'],
            'agrupador' => $rowData['agrupador'],
            'stringafec' => $rowData['stringafec'],
        );
    }

    return $datos;
}
