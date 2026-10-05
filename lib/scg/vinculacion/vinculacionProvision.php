<?php
//
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/../../compararCadenas.php';
require_once dirname(__FILE__) . '/../../redondear.php';

function vinculacionProvision(
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

    $sqlFiltroGur = "";
    $sqlFiltroClc = "";
    $sqlFiltroBdo = "";
    $sqlFiltroRfc = "";
    $sqlFiltroRef = "";
    $sqlFiltroMes = "";

    $sqlParams = [
        ':gpo' => $gpo_id,
        ':ur' => $ur_id,
        ':mmc' => $mmc_id,
    ];
    $ejercicio = APP_EJERCICIO;
    if ($parametroExtra['gur'] != '') {
        $sqlFiltroGur = " WHERE b.gpo_id = :bgpo AND b.ur_id = :bur ";
        $sqlParams[':bgpo'] = substr($parametroExtra['gur'], 0, 1);
        $sqlParams[':bur'] = substr($parametroExtra['gur'], 1, 2);
    }
    if ($parametroExtra['clc_id'] != '') {
        $sqlFiltroClc = " AND clc_id = :clc ";
        $sqlParams[':clc'] = $parametroExtra['clc_id'];
    }
    if ($parametroExtra['bdo_id'] != '') {
        $sqlFiltroBdo = " AND bdo_id = :bdo ";
        $sqlParams[':bdo'] = $parametroExtra['bdo_id'];
    }
    if ($parametroExtra['rfc_id'] != '') {
        $sqlFiltroRfc = " AND rfc_id = :rfc ";
        $sqlParams[':rfc'] = $parametroExtra['rfc_id'];
    }
    if ($parametroExtra['bdo_referencia'] != '') {
        $sqlFiltroRef = " AND bdo_referencia IN (" . $parametroExtra['bdo_referencia'] . ") ";
    }

    if ($periodo['mesDesde'] != '') {
        $sqlFiltroMes = " :mesdesde ";
        $sqlParams[':mesdesde'] = $periodo['mesDesde'];
    }

    $paginadosql = "";
    if ($paginado == true) {
        $paginadosql = " LIMIT " . $nInicial . ", " . $numreg . " ";
    }

    $sql =
        "SELECT SQL_CALC_FOUND_ROWS gpo_id, ur_id, clc_id, afe_id, bdo_id,
            tdf_id, rfc_id, bdo_referencia, bdo_concepto,
            bdo_percepcionPresupuestal, bdo_percepcionContable,
            bdo_retencionContable, msc_id, estado, fechaAutorizacion,
            fechaCancelacion, agrupador, polizaAutorizado, fechaAutorizado,
            gpo_id_polizaAutorizado, ur_id_polizaAutorizado, ue_id_polizaAutorizado,
            msc_id_polizaAutorizado, pot_id_polizaAutorizado, prf_id_polizaAutorizado,
            pma_id_polizaAutorizado, polizaCancelado, fechaCancelado, gpo_id_polizaCancelado,
            ur_id_polizaCancelado, ue_id_polizaCancelado, msc_id_polizaCancelado,
            pot_id_polizaCancelado, prf_id_polizaCancelado, pma_id_polizaCancelado,
            id 
        FROM 
        (
            SELECT
                c.gpo_id, c.ur_id, c.clc_id, c.afe_id,
                c.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia, b.bdo_concepto,
                b.bdo_percepcionPresupuestal, b.bdo_percepcionContable, b.bdo_retencionContable,
                MIN(v.msc_id) AS msc_id,
                MIN(v.bdn_estado) AS estado, DATE( MIN(v.bdn_fechaAutorizado) ) AS fechaAutorizacion, DATE( MIN(v.bdn_fechaCancelado) ) AS fechaCancelacion,
                MIN(v.bdn_agrupador) AS agrupador,
                MIN(pa.polizaAutorizado) AS polizaAutorizado, MIN(pa.fechaAutorizado) AS fechaAutorizado,
                pa.gpo_id_polizaAutorizado, pa.ur_id_polizaAutorizado, MIN(pa.ue_id_polizaAutorizado) AS ue_id_polizaAutorizado,
                MIN(pa.msc_id_polizaAutorizado) AS msc_id_polizaAutorizado, MIN(pa.pot_id_polizaAutorizado) AS pot_id_polizaAutorizado,
                MIN(pa.prf_id_polizaAutorizado) AS prf_id_polizaAutorizado,
                pa.pma_id_polizaAutorizado,
                MIN(pc.polizaCancelado) AS polizaCancelado, MIN(pc.fechaCancelado) AS fechaCancelado,
                MIN(pc.gpo_id_polizaCancelado) AS gpo_id_polizaCancelado, 
                MIN(pc.ur_id_polizaCancelado) AS ur_id_polizaCancelado, 
                MIN(pc.ue_id_polizaCancelado) AS ue_id_polizaCancelado,
                MIN(pc.msc_id_polizaCancelado) AS msc_id_polizaCancelado,
                MIN(pc.pot_id_polizaCancelado) AS pot_id_polizaCancelado,
                MIN(pc.prf_id_polizaCancelado) AS prf_id_polizaCancelado,
                MIN(pc.pma_id_polizaCancelado) AS pma_id_polizaCancelado,
                CONCAT( c.gpo_id, c.ur_id, '.', LPAD( c.msc_id_ejercido, 2, 000 ), '.', LPAD( c.afe_id, 6, 0000000 ), '.', LPAD( c.bdo_id, 6, 000000 ), '.', COALESCE( NULL, 000000 ) ) AS id
            FROM cat_BancoDocumentos b
                INNER JOIN (
                    SELECT n.gpo_id, n.ur_id, MIN(n.clc_id) AS clc_id, n.afe_id, n.bdo_id,
                        MONTH(MIN(c.clc_fechaEnvio)) AS msc_id_ejercido
                    FROM scp_Presupuestal n
                        INNER JOIN scp_Clc c USING( gpo_id, ur_id, clc_id )
                    WHERE gpo_id = :gpo AND ur_id = :ur
                        {$sqlFiltroClc}
                        {$sqlFiltroBdo}
                        AND MONTH(c.clc_fechaEnvio) = {$sqlFiltroMes}
                    GROUP BY gpo_id, ur_id, afe_id, bdo_id
                )c USING( gpo_id, ur_id, bdo_id )
                LEFT JOIN (
                    SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id,
                        msc_id AS msc_id_ministrado
                    FROM tesoreria.min_BancoDocumentoMinistrado_{$ejercicio}
                    WHERE gpo_id = :gpo AND ur_id = :ur
                        {$sqlFiltroClc}
                        {$sqlFiltroBdo}
                        AND msc_id = {$sqlFiltroMes}
                        AND bdm_estado = 'A'
                )t USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                LEFT JOIN cg_BancoDocumentoProvisionTransferido v USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                LEFT JOIN(
                    SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado, MIN(m.ue_id) AS ue_id_polizaAutorizado,
                        MIN(m.msc_id) AS msc_id_polizaAutorizado, MIN(m.pot_id) AS pot_id_polizaAutorizado, MIN(m.prf_id) AS prf_id_polizaAutorizado,
                        m.pma_id AS pma_id_polizaAutorizado, MIN(m.pma_fecha) AS fechaAutorizado,
                        CONCAT( MIN(m.pot_id), '-', MIN(m.prf_id), m.pma_id ,' ',MIN(m.apg_id) ) AS polizaAutorizado,
                        MIN(x.pdx_gpo_id_vinculacion) AS gpo_id, MIN(x.pdx_ur_id_vinculacion) AS ur_id,
                        MIN(x.pdx_afe_id_vinculacion) AS afe_id, MIN(x.pdx_bdo_id_vinculacion) AS bdo_id,
                        MIN(x.pdx_agrupador) AS agrupador
                    FROM cg_PolizaMaestro m
                        INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                        AND m.mmc_id = :mmc
                        AND m.tmc_id = 'A'
                        AND x.pdx_ejercicio = {$ejercicio}
                    GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_origen, x.pdx_bdo_id_origen, pma_id
                )pa USING( gpo_id, ur_id, afe_id, bdo_id )
                LEFT JOIN (
                    SELECT m.gpo_id AS gpo_id_polizaCancelado, m.ur_id AS ur_id_polizaCancelado, MIN(m.ue_id) AS ue_id_polizaCancelado,
                        MIN(m.msc_id) AS msc_id_polizaCancelado, MIN(m.pot_id) AS pot_id_polizaCancelado, MIN(m.prf_id) AS prf_id_polizaCancelado,
                        m.pma_id AS pma_id_polizaCancelado,MIN(m.pma_fecha) AS fechaCancelado,
                        CONCAT( MIN(m.pot_id), '-', MIN(m.prf_id), m.pma_id ,' ',MIN(m.apg_id)) AS polizaCancelado,
                        MIN(x.pdx_gpo_id_vinculacion) AS gpo_id, MIN(x.pdx_ur_id_vinculacion) AS ur_id,
                        MIN(x.pdx_afe_id_vinculacion) AS afe_id, MIN(x.pdx_bdo_id_vinculacion) AS bdo_id,
                        MIN(x.pdx_agrupador) AS agrupador
                    FROM cg_PolizaMaestro m
                        INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                        AND m.mmc_id = :mmc
                        AND m.tmc_id = 'C'
                        AND x.pdx_ejercicio = {$ejercicio}
                    GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_origen, x.pdx_bdo_id_origen, pma_id
                )pc USING( gpo_id, ur_id, afe_id, bdo_id )
                {$sqlFiltroGur}
                AND t.msc_id_ministrado IS NULL
                AND v.bdn_agrupador IS NULL
                {$sqlFiltroRfc}
                {$sqlFiltroRef}
            GROUP BY gpo_id, ur_id, bdo_id, afe_id, pa.pma_id_polizaAutorizado
            UNION ALL
            SELECT
                v.gpo_id, v.ur_id, v.clc_id, v.afe_id,
                v.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia, b.bdo_concepto,
                b.bdo_percepcionPresupuestal, b.bdo_percepcionContable, b.bdo_retencionContable,
                v.msc_id,
                v.bdn_estado AS estado, DATE( v.bdn_fechaAutorizado ) AS fechaAutorizacion, DATE( v.bdn_fechaCancelado ) AS fechaCancelacion,
                v.bdn_agrupador AS agrupador,
                MIN(pa.polizaAutorizado) AS polizaAutorizado, MIN(pa.fechaAutorizado) AS fechaAutorizado,
                pa.gpo_id_polizaAutorizado, pa.ur_id_polizaAutorizado,
                MIN(pa.ue_id_polizaAutorizado) AS ue_id_polizaAutorizado,
                MIN(pa.msc_id_polizaAutorizado) AS msc_id_polizaAutorizado,
                MIN(pa.pot_id_polizaAutorizado) AS pot_id_polizaAutorizado, 
                MIN(pa.prf_id_polizaAutorizado) AS prf_id_polizaAutorizado, pa.pma_id_polizaAutorizado,
                MIN(pc.polizaCancelado) AS polizaCancelado,
                MIN(pc.fechaCancelado) AS fechaCancelado,
                MIN(pc.gpo_id_polizaCancelado) AS gpo_id_polizaCancelado,
                MIN(pc.ur_id_polizaCancelado) AS ur_id_polizaCancelado,
                MIN(pc.ue_id_polizaCancelado) AS ue_id_polizaCancelado,
                MIN(pc.msc_id_polizaCancelado) AS msc_id_polizaCancelado,
                MIN(pc.pot_id_polizaCancelado) AS pot_id_polizaCancelado,
                MIN(pc.prf_id_polizaCancelado) AS prf_id_polizaCancelado,
                MIN(pc.pma_id_polizaCancelado),
                CONCAT( v.gpo_id, v.ur_id, '.', LPAD( v.msc_id, 2, 000 ), '.', LPAD( v.afe_id, 6, 0000000 ), '.', LPAD( v.bdo_id, 6, 000000 ), '.', COALESCE( NULL, 000000 ) ) AS id
            FROM cat_BancoDocumentos b
                INNER JOIN (
                    SELECT gpo_id, ur_id, MIN(clc_id) AS clc_id, afe_id, bdo_id,
                        msc_id,
                        MIN(bdn_estado) AS bdn_estado, MIN(bdn_fechaAutorizado) AS bdn_fechaAutorizado, MIN(bdn_fechaCancelado) AS bdn_fechaCancelado,
                        MIN(bdn_agrupador) AS bdn_agrupador
                    FROM cg_BancoDocumentoProvisionTransferido
                    WHERE gpo_id = :gpo AND ur_id = :ur
                        {$sqlFiltroClc}
                        {$sqlFiltroBdo}
                        AND msc_id = {$sqlFiltroMes}
                    GROUP BY gpo_id, ur_id, afe_id, bdo_id
                )v USING( gpo_id, ur_id, bdo_id )
                LEFT JOIN (
                    SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id,
                        msc_id AS msc_id_ministrado
                    FROM  tesoreria.min_BancoDocumentoMinistrado_{$ejercicio}
                    WHERE gpo_id = :gpo AND ur_id = :ur
                        {$sqlFiltroClc}
                        {$sqlFiltroBdo}
                        AND msc_id = {$sqlFiltroMes}
                        AND bdm_estado = 'A'
                )t USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                LEFT JOIN(
                    SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado,
                        MIN(m.ue_id) AS ue_id_polizaAutorizado,
                        MIN(m.msc_id) AS msc_id_polizaAutorizado, MIN(m.pot_id) AS pot_id_polizaAutorizado,
                        MIN(m.prf_id) AS prf_id_polizaAutorizado,
                        m.pma_id AS pma_id_polizaAutorizado, MIN(m.pma_fecha) AS fechaAutorizado,
                        CONCAT( MIN(m.pot_id), '-', MIN(m.prf_id), m.pma_id,' ',MIN(m.apg_id) ) AS polizaAutorizado,
                        MIN(x.pdx_gpo_id_vinculacion) AS gpo_id, MIN(x.pdx_ur_id_vinculacion) AS ur_id,
                        MIN(x.pdx_afe_id_vinculacion) AS afe_id, MIN(x.pdx_bdo_id_vinculacion) AS bdo_id,
                        MIN(x.pdx_agrupador) AS agrupador
                    FROM cg_PolizaMaestro m
                        INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                        AND m.mmc_id = :mmc
                        AND m.tmc_id = 'A'
                        AND x.pdx_ejercicio = {$ejercicio}
                    GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_origen, x.pdx_bdo_id_origen, pma_id
                )pa USING( gpo_id, ur_id, afe_id, bdo_id )
                LEFT JOIN (
                    SELECT m.gpo_id AS gpo_id_polizaCancelado, m.ur_id AS ur_id_polizaCancelado,  MIN(m.ue_id) AS ue_id_polizaCancelado,
                        MIN(m.msc_id) AS msc_id_polizaCancelado, MIN(m.pot_id) AS pot_id_polizaCancelado, MIN(m.prf_id) AS prf_id_polizaCancelado,
                        m.pma_id AS pma_id_polizaCancelado,  MIN(m.pma_fecha) AS fechaCancelado,
                        CONCAT( MIN(m.pot_id), '-', MIN(m.prf_id), m.pma_id ,' ',MIN(m.apg_id)) AS polizaCancelado,
                        MIN(x.pdx_gpo_id_vinculacion) AS gpo_id, MIN(x.pdx_ur_id_vinculacion) AS ur_id,
                        MIN(x.pdx_afe_id_vinculacion) AS afe_id, MIN(x.pdx_bdo_id_vinculacion) AS bdo_id,
                        MIN(x.pdx_agrupador) AS agrupador
                    FROM cg_PolizaMaestro m
                        INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                        AND m.mmc_id = :mmc
                        AND m.tmc_id = 'C'
                        AND x.pdx_ejercicio = {$ejercicio}
                    GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_origen, x.pdx_bdo_id_origen, pma_id
                )pc USING( gpo_id, ur_id, afe_id, bdo_id )
                {$sqlFiltroGur}
                {$sqlFiltroRfc}
                {$sqlFiltroRef}
            GROUP BY gpo_id, ur_id, bdo_id, afe_id, pa.pma_id_polizaAutorizado
            ORDER BY afe_id 
        ) MMM {$paginadosql} ";

    $conexion = new AccesoDatos(); //variable de konexion para los saldos iniciales
    $conexion->conectar();
    $conexion->bindParams($sqlParams);
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
            'fechaAutorizacion' => $rowData['fechaAutorizacion'],
            'fechaCancelacion' => $rowData['fechaCancelacion'],
            'polizaAutorizado' => $rowData['polizaAutorizado'],
            'fechaAutorizado' => $rowData['fechaAutorizado'],
            'polizaCancelado' => $rowData['polizaCancelado'],
            'fechaCancelado' => $rowData['fechaCancelado'],
            'agrupador' => $rowData['agrupador']
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
            'fechaAutorizacion' => '',
            'fechaCancelacion' => '',
            'polizaAutorizado' => '',
            'fechaAutorizado' => '',
            'polizaCancelado' => '',
            'fechaCancelado' => '',
            'agrupador' => ''
        );
    }
    return $datos;
}
