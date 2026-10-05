<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/../../compararCadenas.php';
require_once dirname(__FILE__) . '/../../redondear.php';

function vinculacionRegistroPagado(
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
    $numreg = 0
) {
    $sqlFiltro = '';
    $sqlFiltroP = '';
    $sqlParams = [
        ':gpo' => $gpo_id,
        ':ur' => $ur_id,
    ];

    if ($parametroExtra['gur'] != '') {
        $sqlFiltro = " AND t.gpo_id = :tgpo AND t.ur_id = :tur ";
        $sqlParams[':tgpo'] = substr($parametroExtra['gur'], 0, 1);
        $sqlParams[':tur'] = substr($parametroExtra['gur'], 1, 2);
    }
    if ($parametroExtra['clc_id'] != '') {
        $sqlFiltro .= " AND t.clc_id =  :tclc ";
        $sqlParams[':tclc'] = $parametroExtra['clc_id'];
    }
    if ($parametroExtra['bdo_id'] != '') {
        $sqlFiltro .= " AND t.bdo_id = :tbdo ";
        $sqlParams[':tbdo'] = $parametroExtra['bdo_id'];
    }
    if ($parametroExtra['rfc_id'] != '') {
        $sqlFiltro .= " AND b.rfc_id = :brfc ";
        $sqlParams[':brfc'] = $parametroExtra['rfc_id'];
    }
    if ($parametroExtra['bdo_referencia'] != '') {
        $sqlFiltro .= " AND b.bdo_referencia IN (" . $parametroExtra['bdo_referencia'] . ") ";
    }
    $var_MAX = " MAX";
    if ($periodo['mesDesde'] != '') {
        $sqlFiltro .= " AND t.msc_id = :mesdesde ";
        $sqlParams[":mesdesde"] = $periodo['mesDesde'];
        $var_MAX = "";
    }

    $sqlFiltroP = "WHERE  gpo_id = :gpo AND ur_id = :ur AND bdp_estado ='A' ";
    if ($periodo['mesDesde'] != '') {
        $sqlFiltroP = "WHERE  gpo_id = :gpo AND ur_id = :ur AND msc_id >= " . $periodo['mesDesde'] . " AND bdp_estado ='A' ";
    }

    $sqlFiltro .= " AND bdm_estado='A' AND c.tcl_id != 'C' AND v.bdp_estado IS NULL ";
    $paginadosql = "";
    if ($paginado == true) $paginadosql = " LIMIT " . $nInicial . ", " . $numreg . " ";
    $sql =
        "SELECT
        t.gpo_id, t.ur_id, MAX(t.clc_id) AS clc_id, MAX(t.afe_id) AS afe_id,group_concat(distinct t.afe_id) as stringafec,
        t.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia, b.bdo_concepto,
        MAX(v.tdf_id) AS tdf_id_pagado, MAX(v.bdp_referencia) as bdp_referencia ,
        b.bdo_percepcionPresupuestal, b.bdo_percepcionContable, b.bdo_retencionContable,
        (v.msc_id),
        (v.bdp_estado) AS estado, DATE( MAX(v.bdp_fechaAutorizado) ) AS fechaAutorizacion, DATE( MAX(v.bdp_fechaCancelado) ) AS fechaCancelacion,
        (v.bdp_agrupador) AS agrupador,
        CONCAT( t.gpo_id, t.ur_id, '.', LPAD( $var_MAX(t.msc_id), 2, 000 ), '.', LPAD( MAX(t.afe_id), 6, 0000000 ), '.', LPAD( t.bdo_id, 6, 000000 )  ) AS id
    FROM cat_BancoDocumentos b
        INNER JOIN ". BD_TESORERIA .".min_BancoDocumentoMinistrado_". APP_EJERCICIO ." t USING( gpo_id, ur_id, bdo_id )
        INNER JOIN cg_MesContable ms USING( gpo_id, ur_id, msc_id )
        INNER JOIN ". BD_TESORERIA .".cat_TipoMinistracion USING( tmi_id )
        INNER JOIN scp_Clc c USING( gpo_id, ur_id, clc_id )
        LEFT JOIN(
            SELECT * FROM cg_BancoDocumentoPagado
            {$sqlFiltroP}
        ) v USING( gpo_id, ur_id, afe_id, bdo_id )
    WHERE b.bdo_id IS NOT NULL {$sqlFiltro}
    GROUP BY gpo_id, ur_id, bdo_id,v.msc_id,v.bdp_estado,v.bdp_agrupador
    ORDER BY gpo_id, ur_id, bdo_id,v.msc_id,v.bdp_estado,v.bdp_agrupador 
    {$paginadosql}";


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
            'tdf_id_pagado' => $rowData['tdf_id_pagado'],
            'bdp_referencia' => $rowData['bdp_referencia'],
            'fechaAutorizacion' => $rowData['fechaAutorizacion'],
            'fechaCancelacion' => $rowData['fechaCancelacion'],
            'polizaAutorizado' => "", //$rowData['polizaAutorizado'],
            'fechaAutorizado' =>  "", //$rowData['fechaAutorizado'],
            'polizaCancelado' =>  "", //$rowData['polizaCancelado'],
            'fechaCancelado' =>  "", //$rowData['fechaCancelado'],
            'agrupador' =>  "", //$rowData['agrupador'],
            'stringafec' =>  $rowData['stringafec']
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
            'bdo_fechaDocumento' => '',
            'bdo_concepto' => '',
            'bdo_fondoRotatorio' => '',
            'bdo_origen' => '',
            'bdo_importe' => '',
            'bdo_percepcionPresupuestal' => '',
            'estado' => '',
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

function vinculacionPagado(
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
    $numreg = 0
) {
    $sqlFiltro = '';
    $sqlFiltroP = '';
    $sqlParams = [
        ':gpo' => $gpo_id,
        ':ur' => $ur_id,
        ':mmc' => $mmc_id,
    ];
    if ($parametroExtra['gur'] != '') {
        $sqlFiltro = " AND t.gpo_id = :tgpo AND t.ur_id = :tur ";
        $sqlParams[':tgpo'] = substr($parametroExtra['gur'], 0, 1);
        $sqlParams[':tur'] = substr($parametroExtra['gur'], 1, 2);
    }
    if ($parametroExtra['clc_id'] != '') {
        $sqlFiltro .= " AND t.clc_id = :tclc ";
        $sqlParams[':tclc'] = $parametroExtra['clc_id'];
    }
    if ($parametroExtra['bdo_id'] != '') {
        $sqlFiltro .= " AND t.bdo_id = :tbdo ";
        $sqlParams[':tbdo'] = $parametroExtra['bdo_id'];
    }
    if ($parametroExtra['rfc_id'] != '') {
        $sqlFiltro .= " AND b.rfc_id = :brfc ";
        $sqlParams[':brfc'] = $parametroExtra['rfc_id'];
    }
    if ($parametroExtra['bdo_referencia'] != '') {
        $sqlFiltro .= " AND b.bdo_referencia IN (" . $parametroExtra['bdo_referencia'] . ") ";
    }
    $var_MAX = " MAX";
    $sqlFiltroP = "WHERE  gpo_id = :gpo AND ur_id = :ur AND bdp_estado='A' ";
    if (isset($parametroExtra['repacumulado']) && $parametroExtra['repacumulado']) {
        $periodo['mesDesde'] = '';
    }
    if (isset($parametroExtra['repacumuladomes']) && $parametroExtra['repacumuladomes']) {
        if ($periodo['mesDesde'] != '') {
            $sqlFiltro .= " AND t.msc_id <= :mesdesde ";
            $sqlParams[':mesdesde'];
        }
    } else {
        if ($periodo['mesDesde'] != '') {
            $sqlFiltro .= " AND t.msc_id = :mesdesde ";
            $var_MAX = "";
            $sqlFiltroP = "WHERE  gpo_id = :gpo AND ur_id = :ur AND bdp_estado='A' 
                AND msc_id >= :mesdesde ";
            $sqlParams[':mesdesde'] = $periodo['mesDesde'];
        }
    }

    $sqlFiltro .= " AND bdm_estado='A' AND c.tcl_id != 'C' ";
    $paginadosql = "";
    if ($paginado == true) $paginadosql = " LIMIT " . $nInicial . ", " . $numreg . " ";
    $sql =
        "SELECT SQL_CALC_FOUND_ROWS 
            t.gpo_id, t.ur_id, MAX(t.clc_id) AS clc_id , MAX(t.afe_id) AS afe_id,
            t.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia, b.bdo_concepto,
            MAX(v.tdf_id) AS tdf_id_pagado, MAX(v.bdp_referencia) AS bdp_referencia,
            b.bdo_percepcionPresupuestal, b.bdo_percepcionContable, b.bdo_retencionContable,
            MAX(v.msc_id) AS msc_id,
            v.bdp_estado AS estado, DATE( MAX(v.bdp_fechaAutorizado) ) AS fechaAutorizacion, DATE( MAX(v.bdp_fechaCancelado) ) AS fechaCancelacion,
            MAX(v.bdp_agrupador) AS agrupador,

            pa.polizaAutorizado, MAX(pa.fechaAutorizado) AS fechaAutorizado,
            MAX(pa.gpo_id_polizaAutorizado) AS gpo_id_polizaAutorizado, MAX(pa.ur_id_polizaAutorizado) AS ur_id_polizaAutorizado, MAX(pa.ue_id_polizaAutorizado) AS ue_id_polizaAutorizado,
            MAX(pa.msc_id_polizaAutorizado) AS msc_id_polizaAutorizado, MAX(pa.pot_id_polizaAutorizado) AS pot_id_polizaAutorizado, MAX(pa.prf_id_polizaAutorizado) AS prf_id_polizaAutorizado,
            MAX(pa.pma_id_polizaAutorizado) AS pma_id_polizaAutorizado,

            MAX(pc.polizaCancelado) AS polizaCancelado, MAX(pc.fechaCancelado) AS fechaCancelado,
            MAX(pc.gpo_id_polizaCancelado) AS gpo_id_polizaCancelado, MAX(pc.ur_id_polizaCancelado) AS ur_id_polizaCancelado, MAX(pc.ue_id_polizaCancelado) AS ue_id_polizaCancelado,
            MAX(pc.msc_id_polizaCancelado) AS msc_id_polizaCancelado, MAX(pc.pot_id_polizaCancelado) AS pot_id_polizaCancelado, MAX(pc.prf_id_polizaCancelado) AS prf_id_polizaCancelado,
            MAX(pc.pma_id_polizaCancelado) AS pma_id_polizaCancelado,

            CONCAT( t.gpo_id, t.ur_id, '.', LPAD( $var_MAX(t.msc_id), 2, 000 ), '.', LPAD( MAX(t.afe_id), 6, 0000000 ), '.', LPAD( t.bdo_id, 6, 000000 ), '.', COALESCE( MAX(pa.pma_id_polizaAutorizado), 000000 ) ) AS id,
            ecf_clave
        FROM cat_BancoDocumentos b
            INNER JOIN ". BD_TESORERIA .".min_BancoDocumentoMinistrado_". APP_EJERCICIO ." t USING( gpo_id, ur_id, bdo_id )
            INNER JOIN cg_MesContable ms USING( gpo_id, ur_id, msc_id )
            INNER JOIN ". BD_TESORERIA .".cat_TipoMinistracion USING( tmi_id )
            INNER JOIN scp_Clc c USING( gpo_id, ur_id, clc_id )
            inner join enc_ClaveFinanciamiento using(ecf_id)
            INNER JOIN
                (SELECT * FROM cg_BancoDocumentoPagado
                    $sqlFiltroP ) v USING( gpo_id, ur_id, afe_id, bdo_id )
            LEFT JOIN(
                SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado, MAX(m.ue_id) AS ue_id_polizaAutorizado,
                    MAX(m.msc_id) AS msc_id_polizaAutorizado, MAX(m.pot_id) AS pot_id_polizaAutorizado, MAX(m.prf_id) AS prf_id_polizaAutorizado,
                    m.pma_id AS pma_id_polizaAutorizado, MAX(m.pma_fecha) AS fechaAutorizado,
                    CONCAT( MAX(m.pot_id), '-', MAX(m.prf_id), m.pma_id ,' ',MAX(m.apg_id)) AS polizaAutorizado,
                    x.pdx_gpo_id_vinculacion AS gpo_id, x.pdx_ur_id_vinculacion AS ur_id,
                    x.pdx_afe_id_vinculacion AS afe_id, x.pdx_bdo_id_vinculacion AS bdo_id,
                    x.pdx_agrupador AS bdp_agrupador
                FROM cg_PolizaMaestro m
                    INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                    AND m.mmc_id = :mmc
                    AND m.tmc_id = 'A'
                    AND x.pdx_ejercicio = ". APP_EJERCICIO ."
                GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_origen, x.pdx_bdo_id_origen, pma_id,gpo_id,ur_id,afe_id,bdo_id,bdp_agrupador
            )pa USING( gpo_id, ur_id, afe_id, bdo_id,bdp_agrupador)
            LEFT JOIN (
                SELECT m.gpo_id AS gpo_id_polizaCancelado, m.ur_id AS ur_id_polizaCancelado, MAX(m.ue_id) AS ue_id_polizaCancelado,
                    MAX(m.msc_id) AS msc_id_polizaCancelado, MAX(m.pot_id) AS pot_id_polizaCancelado, MAX(m.prf_id) AS prf_id_polizaCancelado,
                    m.pma_id AS pma_id_polizaCancelado, MAX(m.pma_fecha) AS fechaCancelado,
                    CONCAT( MAX(m.pot_id), '-', MAX(m.prf_id), m.pma_id ,' ',MAX(m.apg_id)) AS polizaCancelado,
                    x.pdx_gpo_id_vinculacion AS gpo_id, x.pdx_ur_id_vinculacion AS ur_id,
                    x.pdx_afe_id_vinculacion AS afe_id, x.pdx_bdo_id_vinculacion AS bdo_id,
                    x.pdx_agrupador AS bdp_agrupador
                FROM cg_PolizaMaestro m
                    INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                    AND m.mmc_id = :mmc
                    AND m.tmc_id = 'C'
                    AND x.pdx_ejercicio = ". APP_EJERCICIO ."
                GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_origen, x.pdx_bdo_id_origen, pma_id,gpo_id,ur_id,afe_id,bdo_id,bdp_agrupador
            )pc USING( gpo_id, ur_id, afe_id, bdo_id,bdp_agrupador)
        WHERE b.bdo_id IS NOT NULL {$sqlFiltro}
        GROUP BY gpo_id, ur_id, bdo_id, pa.polizaAutorizado,ecf_clave 
        ORDER BY gpo_id, ur_id, bdo_id, pa.polizaAutorizado {$paginadosql}";

    $conexion = new AccesoDatos(); //variable de konexion para los saldos iniciales
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($sqlParams);

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
            'ecf_clave' => $rowData['ecf_clave']
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
            'bdo_fechaDocumento' => '',
            'bdo_concepto' => '',
            'bdo_fondoRotatorio' => '',
            'bdo_origen' => '',
            'bdo_importe' => '',
            'bdo_percepcionPresupuestal' => '',
            'estado' => '',
            'fechaAutorizacion' => '',
            'fechaCancelacion' => '',
            'polizaAutorizado' => '',
            'fechaAutorizado' => '',
            'polizaCancelado' => '',
            'fechaCancelado' => '',
            'agrupador' => '',
            'ecf_clave' => ''
        );
    }

    return $datos;
}
