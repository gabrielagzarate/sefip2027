<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/../../compararCadenas.php';
require_once dirname(__FILE__) . '/../../redondear.php';

function vinculacionComprometido(
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

    $sqlFiltro = '';
    $parametrosSQL = [
        ':gpo' => $gpo_id,
        ':ur' => $ur_id,
        ':mmc' => $mmc_id
    ];
    if ($parametroExtra['gur'] != '') {
        $sqlFiltro = " AND c.gpo_id = :cgpo and c.ur_id = :cur ";
        $parametrosSQL[':cgpo'] = substr($parametroExtra['gur'], 0, 1);
        $parametrosSQL[':cur'] = substr($parametroExtra['gur'],1,2);
    }
    if ($parametroExtra['clc_id'] != '') {
        $sqlFiltro .= " AND n.clc_id = :nclc ";
        $parametrosSQL[':nclc'] =$parametroExtra['clc_id'];
    }
    if ($parametroExtra['cmp_id'] != '') {
        $sqlFiltro .= " AND c.cmp_id = :ccmp ";
        $parametrosSQL[':ccmp'] = $parametroExtra['cmp_id'];
    }
    if ($parametroExtra['rfc_id'] != '') {
        $sqlFiltro .= " AND c.rfc_id = :crfc ";
        $parametrosSQL[':crfc'] = $parametroExtra['rfc_id'];
    }
    if ($parametroExtra['cmp_numOrden'] != '') {
        $orden = $parametroExtra['cmp_numOrden'];
        $sqlFiltro .= " AND c.cmp_numOrden IN ( {$orden} ) ";
    }
    if ($periodo['mesDesde'] != '') {
        $sqlFiltro .= " AND c.msc_id_autorizado =  :mesdesde ";
        $parametrosSQL[':mesdesde'] = $periodo['mesDesde'];
    }
    $paginadosql = "";
    if ($paginado == true) {
        $paginadosql = " LIMIT " . intval($nInicial) . ", " . intval($numreg) . " ";
    }

    $sql = 
    "SELECT SQL_CALC_FOUND_ROWS
        c.gpo_id, c.ur_id, GROUP_CONCAT( DISTINCT n.clc_id ) AS clc_id, GROUP_CONCAT( DISTINCT n.afe_id ) AS afe_id, GROUP_CONCAT( DISTINCT n.bdo_id ) AS bdo_id,
        c.cmp_id, c.rfc_id, c.cmp_numOrden, c.cmp_fechaOrden, c.cmp_concepto,
        c.cmp_tipoCompromiso, c.cmp_tipoMovimiento, c.cmp_importe,
        c.msc_id_autorizado,
        CASE c.ecm_id WHEN 'C' THEN 'C' ELSE 'A' END AS estado, DATE( c.cmp_fechaAutorizado ) AS fechaAutorizacion, DATE( c.cmp_fechaCancelado ) AS fechaCancelacion,
        CONCAT( c.gpo_id, c.ur_id, LPAD( c.msc_id_autorizado, 2, 000 ), 'C', c.cmp_tipoCompromiso, c.cmp_id ) AS agrupador,

        pa.polizaAutorizado, MAX(pa.fechaAutorizado) AS fechaAutorizado,
        MAX(pa.gpo_id_polizaAutorizado) AS gpo_id_polizaAutorizado, MAX(pa.ur_id_polizaAutorizado) AS ur_id_polizaAutorizado, MAX(pa.ue_id_polizaAutorizado) AS ue_id_polizaAutorizado,
        MAX(pa.msc_id_polizaAutorizado) AS msc_id_polizaAutorizado, MAX(pa.pot_id_polizaAutorizado) AS pot_id_polizaAutorizado, MAX(pa.prf_id_polizaAutorizado) AS prf_id_polizaAutorizado,
        MAX(pa.pma_id_polizaAutorizado) AS pma_id_polizaAutorizado,

        MAX(pc.polizaCancelado) AS polizaCancelado, MAX(pc.fechaCancelado) as fechaCancelado,
        MAX(pc.gpo_id_polizaCancelado) AS gpo_id_polizaCancelado, MAX(pc.ur_id_polizaCancelado) AS ur_id_polizaCancelado, MAX(pc.ue_id_polizaCancelado) AS ue_id_polizaCancelado,
        MAX(pc.msc_id_polizaCancelado) AS msc_id_polizaCancelado, MAX(pc.pot_id_polizaCancelado) AS pot_id_polizaCancelado, MAX(pc.prf_id_polizaCancelado) AS prf_id_polizaCancelado,
        MAX(pc.pma_id_polizaCancelado) AS pma_id_polizaCancelado,

        CONCAT( c.gpo_id, c.ur_id, '.', LPAD( c.msc_id_autorizado, 2, 000 ), '.', LPAD( c.cmp_id, 6, 000000 ) ) AS id
    FROM scp_Compromiso c
        LEFT JOIN scp_Presupuestal n USING( gpo_id, ur_id, cmp_id )
        LEFT JOIN(
            SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado,MAX(m.ue_id) AS ue_id_polizaAutorizado,
                MAX(m.msc_id) AS msc_id_polizaAutorizado, MAX(m.pot_id) AS pot_id_polizaAutorizado, MAX(m.prf_id) AS prf_id_polizaAutorizado,
                MAX(m.pma_id) AS pma_id_polizaAutorizado, MAX(m.pma_fecha) AS fechaAutorizado,
                CONCAT( MAX(m.pot_id), '-', MAX(m.prf_id), MAX(m.pma_id),' ',MAX(m.apg_id)  ) AS polizaAutorizado,
                (x.pdx_gpo_id_vinculacion) AS gpo_id, (x.pdx_ur_id_vinculacion) AS ur_id,
                x.pdx_cmp_id_vinculacion AS cmp_id,
                (x.pdx_agrupador) AS agrupador
            FROM cg_PolizaMaestro m
                INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
            WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                AND m.mmc_id = :mmc
                AND m.tmc_id = 'A'
                AND x.pdx_ejercicio = " . APP_EJERCICIO . "
            GROUP BY m.gpo_id, m.ur_id, x.pdx_cmp_id_vinculacion,gpo_id, ur_id, cmp_id,agrupador
        )pa USING( gpo_id, ur_id, cmp_id )
        LEFT JOIN (
            SELECT m.gpo_id AS gpo_id_polizaCancelado, m.ur_id AS ur_id_polizaCancelado, MAX(m.ue_id) AS ue_id_polizaCancelado,
                MAX(m.msc_id) AS msc_id_polizaCancelado, MAX(m.pot_id) AS pot_id_polizaCancelado, MAX(m.prf_id) AS prf_id_polizaCancelado,
                MAX(m.pma_id) AS pma_id_polizaCancelado, MAX(m.pma_fecha) AS fechaCancelado,
                CONCAT( MAX(m.pot_id), '-', MAX(m.prf_id), MAX(m.pma_id),' ',MAX(m.apg_id)  ) AS polizaCancelado,
                (x.pdx_gpo_id_vinculacion) AS gpo_id, (x.pdx_ur_id_vinculacion) AS ur_id,
                x.pdx_cmp_id_vinculacion AS cmp_id,
                (x.pdx_agrupador) AS agrupador
            FROM cg_PolizaMaestro m
                INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
            WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                AND m.mmc_id = :mmc
                AND m.tmc_id = 'C'
                AND x.pdx_ejercicio = " . APP_EJERCICIO . "
            GROUP BY m.gpo_id, m.ur_id, x.pdx_cmp_id_vinculacion,gpo_id, ur_id, cmp_id,agrupador
        )pc USING( gpo_id, ur_id, cmp_id )
    WHERE c.cmp_id IS NOT NULL $sqlFiltro
    GROUP BY gpo_id, ur_id, cmp_id, pa.polizaAutorizado $paginadosql
    ";
    $conexion = new AccesoDatos(); //variable de konexion para los saldos iniciales
    $conexion->conectar();
    $datos = array();
    $conexion->bindParams($parametrosSQL);
    $conexion->setSql($sql);
    $conexion->consultar();
    while ($rowData = $conexion->extraer()) {
        $datos[$rowData['id']] = array(
            'id' => $rowData['id'],
            'gpo_id' => $rowData['gpo_id'],
            'ur_id' => $rowData['ur_id'],
            'gur' => $rowData['gpo_id'] . $rowData['ur_id'],
            'clc_id' => $rowData['clc_id'],
            'afe_id' => $rowData['afe_id'],
            'bdo_id' => $rowData['bdo_id'],
            'cmp_id' => $rowData['cmp_id'],
            'rfc_id' => $rowData['rfc_id'],
            'cmp_numOrden' => $rowData['cmp_numOrden'],
            'cmp_fechaOrden' => $rowData['cmp_fechaOrden'],
            'cmp_concepto' => $rowData['cmp_concepto'],
            'cmp_tipoCompromiso' => $rowData['cmp_tipoCompromiso'],
            'cmp_tipoMovimiento' => $rowData['cmp_tipoMovimiento'],
            'cmp_importe' => $rowData['cmp_importe'],
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
            'cmp_id' => '',
            'rfc_id' => '',
            'cmp_numOrden' => '',
            'cmp_fechaOrden' => '',
            'cmp_concepto' => '',
            'cmp_tipoCompromiso' => '',
            'cmp_tipoMovimiento' => '',
            'cmp_importe' => '',
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
