<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/../../compararCadenas.php';
require_once dirname(__FILE__) . '/../../redondear.php';

function vinculacionRectificado(
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
    $sqlParams = [
        ':gpo' => $gpo_id,
        ':ur' => $ur_id,
        ':mmc' => $mmc_id,
    ];
    if ($parametroExtra['gur'] != ''){
        $sqlFiltro = " AND v.gpo_id = :vgpo  AND v.ur_id = :vur ";
        $sqlParams[':vgpo'] = substr($parametroExtra['gur'], 0, 1);
        $sqlParams[':vur'] = substr($parametroExtra['gur'], 1, 2);
    }
    if ($parametroExtra['rct_id'] != ''){
        $sqlFiltro .= " AND v.rct_id = :vrct ";
        $sqlParams[':vrct'] = $parametroExtra['rct_id'];
    }
    if ($parametroExtra['clc_id'] != '') {
        $sqlFiltro .= " AND v.clc_id = :clc ";
        $sqlParams[':clc'] = $parametroExtra['clc_id'];
    }
    if ($parametroExtra['bdo_id'] != ''){
        $sqlFiltro .= " AND v.bdo_id = :vbdo ";
        $sqlParams[':vbdo'] = $parametroExtra['bdo_id'];
    }
    if ($parametroExtra['rfc_id'] != '') {
        $sqlFiltro .= " AND b.rfc_id = :brfc " ;
        $sqlParams[':brfc'] = $parametroExtra['rfc_id'];
    }
    if ($parametroExtra['bdo_referencia'] != '') {
        $sqlFiltro .= " AND b.bdo_referencia IN (" . $parametroExtra['bdo_referencia'] . ") ";
    }
    if ($periodo['mesDesde'] != '') {
        $sqlFiltro .= " AND v.msc_id = :mesdesde ";
        $sqlParams[':mesdesde'] = $periodo['mesDesde'];
    }

    $sql = 
    "SELECT SQL_CALC_FOUND_ROWS
        v.gpo_id, v.ur_id, MAX(v.clc_id) AS clc_id, v.afe_id,
        v.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia,
        SUM( v.bdr_percepcionPresupuestal ) AS bdr_percepcionPresupuestal, 
        SUM( v.bdr_percepcionContable ) AS bdr_percepcionContable, 
        SUM( v.bdr_retencionContable ) AS bdr_retencionContable,
        v.msc_id,
        m.rct_id, m.rct_justificacion,
        MAX( v.bdr_estado) AS estado, DATE( MAX(v.bdr_fechaAutorizado) ) AS fechaAutorizacion, 
        DATE( MAX(v.bdr_fechaCancelado) ) AS fechaCancelacion,
        MAX(v.bdr_agrupador) AS agrupador,
        pa.polizaAutorizado, MAX(pa.fechaAutorizado) AS fechaAutorizado,
        MAX(pa.gpo_id_polizaAutorizado) AS gpo_id_polizaAutorizado, MAX(pa.ur_id_polizaAutorizado) AS ur_id_polizaAutorizado, 
        MAX(pa.ue_id_polizaAutorizado) AS ue_id_polizaAutorizado,
        MAX(pa.msc_id_polizaAutorizado) AS msc_id_polizaAutorizado, MAX(pa.pot_id_polizaAutorizado) AS pot_id_polizaAutorizado,
        MAX(pa.prf_id_polizaAutorizado) AS prf_id_polizaAutorizado,
        MAX(pa.pma_id_polizaAutorizado) AS pma_id_polizaAutorizado,
        CONCAT( v.gpo_id, v.ur_id, '.', LPAD( MAX(v.msc_id), 2, 000 ), '.', LPAD( v.afe_id, 6, 0000000 ), '.', LPAD( v.bdo_id, 6, 000000 ), '.', LPAD( m.rct_id, 6, 000000 ), '.', COALESCE( MAX(pa.pma_id_polizaAutorizado), 000000 ) ) AS id
    FROM cat_BancoDocumentos b
        INNER JOIN scp_BancoDocumentoRectificado v USING( gpo_id, ur_id, bdo_id )
        INNER JOIN scp_MaestroRectificacion m USING( gpo_id, ur_id, rct_id )
        INNER JOIN cg_MesContable ms USING( gpo_id, ur_id, msc_id )
        LEFT JOIN(
            SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado, MAX(m.ue_id) AS ue_id_polizaAutorizado,
                MAX(m.msc_id) AS msc_id_polizaAutorizado, MAX(m.pot_id) AS pot_id_polizaAutorizado, MAX(m.prf_id) AS prf_id_polizaAutorizado,
                m.pma_id AS pma_id_polizaAutorizado, MAX(m.pma_fecha) AS fechaAutorizado,
                CONCAT( MAX(m.pot_id), '-', MAX(m.prf_id), m.pma_id ,' ',MAX(m.apg_id)) AS polizaAutorizado,
                MAX(x.pdx_gpo_id_vinculacion) AS gpo_id, MAX(x.pdx_ur_id_vinculacion) AS ur_id,
                x.pdx_afe_id_origen AS afe_id, x.pdx_bdo_id_origen AS bdo_id,
                x.pdx_rct_id_vinculacion AS rct_id,
                x.pdx_agrupador AS agrupador
            FROM cg_PolizaMaestro m
                INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
            WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                AND m.mmc_id = :mmc
                AND m.tmc_id = 'A'
                AND x.pdx_ejercicio = {$ejercicio}
            GROUP BY  m.gpo_id, m.ur_id, x.pdx_afe_id_origen, x.pdx_bdo_id_origen, x.pdx_rct_id_vinculacion,x.pdx_agrupador, m.pma_id
        )pa USING( gpo_id, ur_id, afe_id, bdo_id, rct_id )
    WHERE b.bdo_id IS NOT NULL $sqlFiltro
        GROUP BY gpo_id, ur_id, afe_id, bdo_id, rct_id, pa.polizaAutorizado {$paginadosql} ";

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
            'bdr_importe' => $rowData['bdr_percepcionPresupuestal'] + $rowData['bdr_percepcionContable']
                - $rowData['bdr_retencionContable'],
            'bdr_percepcionPresupuestal' => $rowData['bdr_percepcionPresupuestal'],
            'estado' => $rowData['estado'],
            'rct_id' => $rowData['rct_id'],
            'rct_justificacion' => $rowData['rct_justificacion'],
            'fechaAutorizacion' => $rowData['fechaAutorizacion'],
            'polizaAutorizado' => $rowData['polizaAutorizado'],
            'fechaAutorizado' => $rowData['fechaAutorizado'],
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
            'id' => 'num', 'gpo_id' => $nTotalPages, 'ur_id' => '',
            'gur' => '', 'clc_id' => '', 'afe_id' => '', 'bdo_id' => '',
            'tdf_id' => '', 'rfc_id' => '', 'bdo_referencia' => '',
            'bdr_importe' => '', 'bdr_percepcionPresupuestal' => '',
            'estado' => '', 'rct_id' => '', 'rct_justificacion' => '',
            'fechaAutorizacion' => '', 'polizaAutorizado' => '',
            'fechaAutorizado' => '', 'agrupador' => ''
        );
    }
    return $datos;
}
