<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/../../compararCadenas.php';
require_once dirname(__FILE__) . '/../../redondear.php';

function vinculacionRegistroPagadoTercero(
    $gpo_id,
    $ur_id,
    $ue_id,
    $pot_id,
    $periodo,
    $programatico,
    $claveFinanciamiento,
    $reporte,
    $detalle,
    $mmc_id,
    $niveles,
    $parametroExtra = array(),
    $paginado = false,
    $nInicial = 0,
    $numreg = 0,
    $ejecsql2 = false
) {
    $paginadosql = "";
    if ($paginado == true) {
        $paginadosql = " LIMIT " . $nInicial . ", " . $numreg . " ";
    }

    $sqlParametros = [];
    $ejercicio = APP_EJERCICIO;
    $sqlFiltro = '';
    if ($parametroExtra['gur'] != '') {
        $sqlFiltro = " AND n.cta_gpo_id = :ngpo AND n.cta_ur_id = :nur";
        $sqlParametros[':ngpo'] = substr($parametroExtra['gur'], 0, 1);
        $sqlParametros[':nur'] = substr($parametroExtra['gur'], 1, 2);
    }
    if ($parametroExtra['clc_id'] != '') {
        $sqlFiltro .= " AND t.clc_id = :tclc ";
        $sqlParametros[':tclc'] = $parametroExtra['clc_id'];
    }
    if ($parametroExtra['bdo_id'] != '') {
        $sqlFiltro .= " AND t.bdo_id = :tbdo ";
        $sqlParametros[':tbdo'] = $parametroExtra['bdo_id'];
    }
    if ($parametroExtra['rfc_id'] != '') {
        $sqlFiltro .= " AND b.rfc_id = :brfc ";
        $sqlParametros[':brfc'] = $parametroExtra['rfc_id'];
    }
    if ($parametroExtra['bdo_referencia'] != '') {
        $sqlFiltro .= " AND b.bdo_referencia IN (" . $parametroExtra['bdo_referencia'] . ") ";
    }
    $var_MAX = " MAX";

    $sqlFiltroP = "WHERE  bdp_estado ='A' ";
    if ($periodo['mesDesde'] != '') {
        $sqlFiltroP = "WHERE  msc_id >= :mesdesde AND bdp_estado ='A' ";
        $sqlFiltro .= " AND t.msc_id = :mesdesde ";

        $sqlParametros[':mesdesde'] = $periodo['mesDesde'];
        $var_MAX = "";
    }

    $sql =
        "SELECT
            t.gpo_id, t.ur_id,MAX(t.clc_id) AS clc_id, MAX(t.afe_id) AS afe_id,group_concat(distinct t.afe_id) as stringafec,
            t.bdo_id, t.tdf_id, t.rfc_id, t.bdo_referencia, t.bdo_concepto,
            MAX(v.tdf_id) AS tdf_id_pagado, MAX(v.bdp_referencia) AS bdp_referencia,
            MAX(t.bdo_percepcionPresupuestal) AS bdo_percepcionPresupuestal, MAX(t.bdo_percepcionContable) AS bdo_percepcionContable, MAX(t.bdo_retencionContable) AS bdo_retencionContable,
            MAX(v.msc_id) AS msc_id,
            MAX(v.bdp_estado) AS estado, DATE( MAX(v.bdp_fechaAutorizado) ) AS fechaAutorizacion, DATE( MAX(v.bdp_fechaCancelado) ) AS fechaCancelacion,
            MAX(v.bdp_agrupador) AS agrupador,
            CONCAT( t.gpo_id, t.ur_id, '.', LPAD( {$var_MAX}(t.msc_id), 2, 000 ), '.', LPAD( MAX(t.afe_id), 6, 0000000 ), '.', LPAD( t.bdo_id, 6, 000000 ) ) AS id
        FROM (
            SELECT
                t.gpo_id, t.ur_id, MAX(t.clc_id) AS clc_id , t.afe_id, {$var_MAX}(t.msc_id) AS  msc_id,
                t.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia, b.bdo_concepto,
                SUM(t.bdm_percepcionPresupuestal) AS bdo_percepcionPresupuestal, SUM(t.bdm_percepcionContable) AS bdo_percepcionContable, SUM(t.bdm_retencionContable) AS bdo_retencionContable,
                IFNULL(o.gpo_id, n.gpo_id) AS gpo_id_origen, IFNULL(o.ur_id, n.ur_id) AS ur_id_origen, IFNULL(MAX(o.clc_id), MAX(n.clc_id)) AS
                clc_id_origen, IFNULL(MAX(o.afe_id), n.afe_id) AS afe_id_origen, IFNULL(o.bdo_id, n.bdo_id) AS bdo_id_origen
            FROM cat_BancoDocumentos b
                INNER JOIN tesoreria.min_BancoDocumentoMinistrado_{$ejercicio} t USING( gpo_id, ur_id, bdo_id )
                INNER JOIN scp_Contable n USING(gpo_id, ur_id, clc_id,afe_id,bdo_id)
                INNER JOIN scp_Clc c USING( gpo_id, ur_id, clc_id )
                LEFT JOIN ctrl{$ejercicio}.scp_Contable o ON n.ncc_gpo = o.gpo_id AND n.ncc_ur = o.ur_id AND n.ncc_clc = o.clc_id AND n.ncc_nccamortiza = o.ncc_id
            WHERE b.bdo_id IS NOT NULL {$sqlFiltro}
                AND c.tcl_id = 'C'
            GROUP BY gpo_id, ur_id,t.afe_id, bdo_id, gpo_id_origen, ur_id_origen, bdo_id_origen
        )t
        LEFT JOIN (
            SELECT gpo_id, ur_id, bdo_id,afe_id,tdf_id,bdp_referencia,msc_id,bdp_estado,bdp_fechaAutorizado,bdp_fechaCancelado,bdp_agrupador
            FROM cg_BancoDocumentoPagadoContable 
        {$sqlFiltroP}
        )v USING( gpo_id, ur_id, bdo_id,afe_id )
        WHERE   v.bdp_estado IS NULL
        GROUP BY gpo_id, ur_id, bdo_id {$paginadosql}";

    $conexion = new AccesoDatos(); //variable de konexion para los saldos iniciales
    $conexion->conectar();
    $conexion->bindParams($sqlParametros);
    $conexion->setSql($sql);
    $conexion->consultar();
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
            'bdo_importe' => $rowData['bdo_percepcionPresupuestal'] + $rowData['bdo_percepcionContable']
                - $rowData['bdo_retencionContable'],
            'estado' => $rowData['estado'],
            'tdf_id_pagado' => $rowData['tdf_id_pagado'],
            'bdp_referencia' => $rowData['bdp_referencia'],
            'fechaAutorizacion' => $rowData['fechaAutorizacion'],
            'fechaCancelacion' => $rowData['fechaCancelacion'],
            'polizaAutorizado' => '',
            'fechaAutorizado' => '',
            'polizaCancelado' => '',
            'fechaCancelado' => '',
            'agrupador' => $rowData['agrupador'],
            'stringafec' => $rowData['stringafec']
        );
    }

    $nTotalPages = 0;
    if ($paginado == true) {

        $conexion->setSql("SELECT FOUND_ROWS() numReg");
        $conexion->consultar();
        $totalRegistros = $conexion->sacar();

        $numregConsul = $totalRegistros[0];
        $nTotalPages = ceil($numregConsul / $numreg);
    }
    $conexion->desconectar();

    if ($paginado == true) {
        $datos[0] = array(
            'id' => 'num',
            'gpo_id' => $nTotalPages,
            'ur_id' => '',
            'gur' => '',
            'clc_id' => '',
            'afe_id' => '',
            'bdo_id' => '',
            'tdf_id' => '',
            'rfc_id' => '',
            'bdo_referencia' => '',
            'bdo_concepto' => '',
            'bdo_importe' => '',
            'estado' => '',
            'tdf_id_pagado' => '',
            'bdp_referencia' => '',
            'fechaAutorizacion' => '',
            'fechaCancelacion' => '',
            'polizaAutorizado' => '',
            'fechaAutorizado' => '',
            'polizaCancelado' => '',
            'fechaCancelado' => '',
            'agrupador' => '',
            'stringafec' => ''
        );
    }


    return $datos;
}

function vinculacionPagadoTercero(
    $gpo_id,
    $ur_id,
    $ue_id,
    $pot_id,
    $periodo,
    $programatico,
    $claveFinanciamiento,
    $reporte,
    $detalle,
    $mmc_id,
    $niveles,
    $parametroExtra = array(),
    $paginado = false,
    $nInicial = 0,
    $numreg = 0,
    $ejecsql2 = false
) {
    $paginadosql = "";
    if ($paginado == true) {

        $paginadosql = " LIMIT " . $nInicial . ", " . $numreg . " ";
    }
    $ejercicio = APP_EJERCICIO;
    $sqlFiltro = '';
    $sqlParametros = [
        ':gpo' => $gpo_id,
        ':ur' => $ur_id,
        ':mmc' => $mmc_id,
    ];
    if ($parametroExtra['gur'] != '') {
        $sqlFiltro = " AND n.cta_gpo_id = :gpo AND n.cta_ur_id = :nur";
        $sqlParametros[':ngpo'] = substr($parametroExtra['gur'], 0, 1);
        $sqlParametros[':nur'] = substr($parametroExtra['gur'], 1, 2);
    }
    if ($parametroExtra['clc_id'] != '') {
        $sqlFiltro .= " AND t.clc_id = :tclc ";
        $sqlParametros[':tclc'] = $parametroExtra['clc_id'];
    }
    if ($parametroExtra['bdo_id'] != '') {
        $sqlFiltro .= " AND t.bdo_id = :tbdo ";
        $sqlParametros[':tbdo'] = $parametroExtra['bdo_id'];
    }
    if ($parametroExtra['rfc_id'] != '') {
        $sqlFiltro .= " AND b.rfc_id = :brfc ";
        $sqlParametros[':brfc'] = $parametroExtra['rfc_id'];
    }
    if ($parametroExtra['bdo_referencia'] != '') {
        $sqlFiltro .= " AND b.bdo_referencia IN (" . $parametroExtra['bdo_referencia'] . ") ";
    }

    $var_MAX = " MAX";

    if (isset($parametroExtra['repacumulado']) && $parametroExtra['repacumulado']) {
        $periodo['mesDesde'] = '';
    }
    if (isset($parametroExtra['repacumuladomes']) && $parametroExtra['repacumuladomes']) {
        if ($periodo['mesDesde'] != '') {
            $sqlFiltro .= " AND t.msc_id <= :mesdesde ";
            $sqlParametros[':mesdesde'] = $periodo['mesDesde'];
        }
    } else {
        if ($periodo['mesDesde'] != '') {
            $sqlFiltro .= " AND t.msc_id = :mesdesde ";
            $sqlParametros[':mesdesde'] = $periodo['mesDesde'];
            $var_MAX = "";
        }
    }
    $sql =
        "SELECT
            t.gpo_id, t.ur_id,MAX(t.clc_id) AS clc_id, MAX(t.afe_id) AS afe_id,
            t.bdo_id, t.tdf_id, t.rfc_id, t.bdo_referencia, t.bdo_concepto,
            MAX(v.tdf_id) AS tdf_id_pagado, MAX(v.bdp_referencia) AS bdp_referencia,
            MAX(t.bdo_percepcionPresupuestal) AS bdo_percepcionPresupuestal, MAX(t.bdo_percepcionContable) AS bdo_percepcionContable, MAX(t.bdo_retencionContable) AS bdo_retencionContable,
            MAX(v.msc_id) AS msc_id,
            MAX(v.bdp_estado) AS estado, DATE( MAX(v.bdp_fechaAutorizado) ) AS fechaAutorizacion, DATE( MAX(v.bdp_fechaCancelado) ) AS fechaCancelacion,
            v.bdp_agrupador AS agrupador,
            pa.polizaAutorizado, max(pa.fechaAutorizado) AS fechaAutorizado ,
            max(pa.gpo_id_polizaAutorizado) AS gpo_id_polizaAutorizado, max(pa.ur_id_polizaAutorizado) AS ur_id_polizaAutorizado, max(pa.ue_id_polizaAutorizado) AS ue_id_polizaAutorizado,
            max(pa.msc_id_polizaAutorizado) AS gpo_id_polizaAutorizado, max(pa.pot_id_polizaAutorizado) AS pot_id_polizaAutorizado, max(pa.prf_id_polizaAutorizado)AS prf_id_polizaAutorizado,
            max(pa.pma_id_polizaAutorizado) AS gpo_id_polizaAutorizado,
            max(pc.polizaCancelado) AS polizaCancelado, max(pc.fechaCancelado) AS  fechaCancelado,
            max(pc.gpo_id_polizaCancelado) AS gpo_id_polizaCancelado, max(pc.ur_id_polizaCancelado) AS ur_id_polizaCancelado, max(pc.ue_id_polizaCancelado) AS ue_id_polizaCancelado,
            max(pc.msc_id_polizaCancelado) AS msc_id_polizaCancelado, max(pc.pot_id_polizaCancelado) AS pot_id_polizaCancelado, max(pc.prf_id_polizaCancelado) AS prf_id_polizaCancelado,
            max(pc.pma_id_polizaCancelado) AS pma_id_polizaCancelado,

            CONCAT( t.gpo_id, t.ur_id, '.', LPAD( {$var_MAX}(t.msc_id), 2, 000 ), '.', LPAD( MAX(t.afe_id), 6, 0000000 ), '.', LPAD( t.bdo_id, 6, 000000 ) ) AS id,ecf_clave
        FROM (
            SELECT
                t.gpo_id, t.ur_id, MAX(t.clc_id) AS clc_id , t.afe_id, {$var_MAX}(t.msc_id) AS  msc_id,
                t.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia, b.bdo_concepto,
                SUM(t.bdm_percepcionPresupuestal) AS bdo_percepcionPresupuestal, SUM(t.bdm_percepcionContable) AS bdo_percepcionContable, SUM(t.bdm_retencionContable) AS bdo_retencionContable,
                IFNULL(o.gpo_id, n.gpo_id) AS gpo_id_origen, IFNULL(o.ur_id, n.ur_id) AS ur_id_origen, IFNULL(MAX(o.clc_id), MAX(n.clc_id)) AS
                clc_id_origen, IFNULL(MAX(o.afe_id), n.afe_id) AS afe_id_origen, IFNULL(o.bdo_id, n.bdo_id) AS bdo_id_origen,ecf_clave
            FROM cat_BancoDocumentos b
                INNER JOIN tesoreria.min_BancoDocumentoMinistrado_{$ejercicio} t USING( gpo_id, ur_id, bdo_id )
                INNER JOIN scp_Contable n USING(gpo_id, ur_id, clc_id,afe_id,bdo_id)
                INNER JOIN scp_Clc c USING( gpo_id, ur_id, clc_id )
                INNER JOIN enc_ClaveFinanciamiento using(ecf_id)
                LEFT JOIN ctrl{$ejercicio}.scp_Contable o ON n.ncc_gpo = o.gpo_id AND n.ncc_ur = o.ur_id AND n.ncc_clc = o.clc_id AND n.ncc_nccamortiza = o.ncc_id
            WHERE b.bdo_id IS NOT NULL {$sqlFiltro}
                AND c.tcl_id = 'C'
            GROUP BY gpo_id, ur_id,t.afe_id, bdo_id, gpo_id_origen, ur_id_origen, bdo_id_origen
        )t
            INNER JOIN (
                SELECT
                    gpo_id, ur_id, bdo_id,afe_id,tdf_id,bdp_referencia,msc_id,bdp_estado,bdp_fechaAutorizado,bdp_fechaCancelado,bdp_agrupador
                FROM cg_BancoDocumentoPagadoContable
                WHERE -- gpo_id = $gpo_id AND ur_id = $ur_id and
                    bdp_estado='A'
        )v USING( gpo_id, ur_id, bdo_id,afe_id )
            LEFT JOIN(
                SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado, MAX(m.ue_id) AS ue_id_polizaAutorizado,
                    MAX(m.msc_id) AS msc_id_polizaAutorizado, MAX(m.pot_id) AS pot_id_polizaAutorizado, MAX(m.prf_id) AS prf_id_polizaAutorizado,
                    m.pma_id AS pma_id_polizaAutorizado, MAX(m.pma_fecha) AS fechaAutorizado,
                    CONCAT( MAX(m.pot_id), '-', MAX(m.prf_id), m.pma_id ) AS polizaAutorizado,
                    x.pdx_gpo_id_origen AS gpo_id_origen, x.pdx_ur_id_origen AS ur_id_origen,
                    x.pdx_afe_id_origen AS afe_id_origen, x.pdx_bdo_id_origen AS bdo_id_origen,
                    x.pdx_agrupador AS bdp_agrupador
                FROM cg_PolizaMaestro m
                    INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                    AND m.mmc_id = :mmc
                    AND m.tmc_id = 'A'
                    AND x.pdx_ejercicio = {$ejercicio}
                GROUP BY gpo_id_origen, ur_id_origen, afe_id_origen, bdo_id_origen, bdp_agrupador,pma_id
            )pa USING( bdp_agrupador )
            LEFT JOIN (
                SELECT m.gpo_id AS gpo_id_polizaCancelado, m.ur_id AS ur_id_polizaCancelado, MAX(m.ue_id) AS ue_id_polizaCancelado,
                    MAX(m.msc_id) AS msc_id_polizaCancelado, MAX(m.pot_id) AS pot_id_polizaCancelado, MAX(m.prf_id) AS prf_id_polizaCancelado,
                    m.pma_id AS pma_id_polizaCancelado, MAX(m.pma_fecha) AS fechaCancelado,
                    CONCAT( MAX(m.pot_id), '-', MAX(m.prf_id), m.pma_id ) AS polizaCancelado,
                    x.pdx_gpo_id_origen AS gpo_id_origen, x.pdx_ur_id_origen AS ur_id_origen,
                    x.pdx_afe_id_origen AS afe_id_origen, x.pdx_bdo_id_origen AS bdo_id_origen,
                    x.pdx_agrupador AS bdp_agrupador
                FROM cg_PolizaMaestro m
                    INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                    AND m.mmc_id = :mmc
                    AND m.tmc_id = 'C'
                    AND x.pdx_ejercicio = {$ejercicio}
                GROUP BY gpo_id_origen, ur_id_origen, afe_id_origen, bdo_id_origen, bdp_agrupador,pma_id
            )pc USING( bdp_agrupador )
        GROUP BY gpo_id, ur_id, bdo_id, bdp_agrupador,polizaAutorizado {$paginadosql}
        ";

    $conexion = new AccesoDatos(); //variable de konexion para los saldos iniciales
    $conexion->conectar();
    $conexion->bindParams($sqlParametros);
    $conexion->setSql($sql);
    $conexion->consultar();
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
            'bdo_importe' => $rowData['bdo_percepcionPresupuestal'] + $rowData['bdo_percepcionContable']
                - $rowData['bdo_retencionContable'],
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
            'ecf_clave' => $rowData['ecf_clave'],
            'stringafec' => ""
        );
    }

    $nTotalPages = 0;
    if ($paginado == true) {

        $conexion->setSql("SELECT FOUND_ROWS() numReg");
        $conexion->consultar();
        $totalRegistros = $conexion->sacar();

        $numregConsul = $totalRegistros[0];
        $nTotalPages = ceil($numregConsul / $numreg);
    }

    $conexion->desconectar();


    if ($paginado == true) {
        $datos[0] = array(
            'id' => 'num',
            'gpo_id' => $nTotalPages,
            'ur_id' => '',
            'gur' => '',
            'clc_id' => '',
            'afe_id' => '',
            'bdo_id' => '',
            'tdf_id' => '',
            'rfc_id' => '',
            'bdo_referencia' => '',
            'bdo_concepto' => '',
            'bdo_importe' => '',
            'estado' => '',
            'tdf_id_pagado' => '',
            'bdp_referencia' => '',
            'fechaAutorizacion' => '',
            'fechaCancelacion' => '',
            'polizaAutorizado' => '',
            'fechaAutorizado' => '',
            'polizaCancelado' => '',
            'fechaCancelado' => '',
            'agrupador' => '',
            'stringafec' => '',
            'ecf_clave' => ''
        );
    }


    return $datos;
}
