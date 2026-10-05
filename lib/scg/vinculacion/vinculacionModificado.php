<?php

require_once dirname(__FILE__) . '/../../compararCadenas.php';
require_once dirname(__FILE__) . '/../../redondear.php';

function vinculacionModificado(
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
    $sqlParams = [
        ':gpo' => $gpo_id,
        ':ur' => $ur_id,
        ':mmc' => $mmc_id
    ];

    $sqlParams[':dgpo'] = substr($parametroExtra['gur'], 0, 1);
    $sqlParams[':dur'] = substr($parametroExtra['gur'], 1, 2);    
    $sqlFiltro = " AND d.gpo_id = :dgpo AND d.ur_id = :dur ";

    if ($parametroExtra['omo_id'] != '') {
        $sqlFiltro .= "AND m.omo_id IN ('".$parametroExtra['omo_id']."')";
    }
    if ($parametroExtra['mod_id'] != '') {
        $sqlFiltro .= " AND m.mod_id IN (" . $parametroExtra['mod_id'] . ") ";
    }
    if ($periodo['mesDesde'] != '') {
        $sqlFiltro .= " AND m.msc_id_autorizacion = :mesdesde ";
        $sqlParams[':mesdesde'] = $periodo['mesDesde'];
    }

    $paginadosql = "";
    if ($paginado == true) $paginadosql = " LIMIT " . $nInicial . ", " . $numreg . " ";

    $sql = "SELECT
                m.mod_gpo_id AS gpo_id, m.mod_ur_id AS ur_id, m.omo_id, m.mod_id,
                m.mod_concepto, m.mod_oficioSefin, m.mod_oficioSolicitudSefin, m.mod_origen,
                tfm_nombre, tmd_nombre, mmd_nombre, omo_nombre, cf.ecf_clave, MAX(c.cfi_nombre) AS cfi_nombre,
                m.msc_id_autorizacion,
                CASE m.emo_id WHEN 14 THEN 'A' WHEN 15 THEN 'A' WHEN 16 THEN 'A' ELSE 'C' END AS estado, DATE( m.mod_fechaAutorizacion ) AS fechaAutorizacion,
                CONCAT( m.mod_gpo_id, m.mod_ur_id, m.msc_id_autorizacion, 'M', m.omo_id, m.mod_id ) AS agrupador,
                SUM( CASE mov_id WHEN 'A' THEN dmo_importe ELSE 0 END ) AS mod_ampliacion,
                SUM( CASE mov_id WHEN 'R' THEN dmo_importe ELSE 0 END ) AS mod_reduccion,

                pa.polizaAutorizado, MAX(pa.fechaAutorizado) AS fechaAutorizado,
                MAX(pa.gpo_id_polizaAutorizado) AS gpo_id_polizaAutorizado, MAX(pa.ur_id_polizaAutorizado) AS ur_id_polizaAutorizado, MAX(pa.ue_id_polizaAutorizado) AS ue_id_polizaAutorizado,
                MAX(pa.msc_id_polizaAutorizado) AS msc_id_polizaAutorizado, MAX(pa.pot_id_polizaAutorizado) AS pot_id_polizaAutorizado, MAX(pa.prf_id_polizaAutorizado) AS prf_id_polizaAutorizado,
                MAX(pa.pma_id_polizaAutorizado) AS pma_id_polizaAutorizado ,
                CONCAT( m.mod_gpo_id, m.mod_ur_id, '.', LPAD( m.msc_id_autorizacion, 2, 000 ), '.', m.omo_id, '.', m.mod_id ) AS id
            FROM scp_MaestroModificacion m
                INNER JOIN scp_DetalleModificacion d USING( omo_id, mod_gpo_id, mod_ur_id, mod_id )
                LEFT JOIN(
                    SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado, MAX(m.ue_id) AS ue_id_polizaAutorizado,
                        MAX(m.msc_id) AS msc_id_polizaAutorizado, MAX(m.pot_id) AS pot_id_polizaAutorizado, MAX(m.prf_id) AS prf_id_polizaAutorizado,
                        m.pma_id AS pma_id_polizaAutorizado, MAX(m.pma_fecha) AS fechaAutorizado,
                        CONCAT( MAX(m.pot_id), '-', MAX(m.prf_id), m.pma_id ,' ',MAX(m.apg_id)) AS polizaAutorizado,
                        x.pdx_gpo_id_vinculacion AS mod_gpo_id, x.pdx_ur_id_vinculacion AS mod_ur_id,
                        x.pdx_tipoPresupuestal AS omo_id, x.pdx_mod_id_vinculacion AS mod_id,
                        x.pdx_agrupador AS agrupador
                    FROM cg_PolizaMaestro m
                        INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                        AND m.mmc_id = :mmc
                        AND m.tmc_id = 'A'
                        AND x.pdx_ejercicio = ".APP_EJERCICIO."
                    GROUP BY m.gpo_id, m.ur_id, x.pdx_tipoPresupuestal, x.pdx_gpo_id_vinculacion,
                    x.pdx_ur_id_vinculacion,x.pdx_mod_id_vinculacion,agrupador,m.pma_id
                )pa USING( mod_gpo_id, mod_ur_id, omo_id, mod_id )
                INNER JOIN enc_OrigenOperacion USING( enc_oop )
                INNER JOIN cat_TipoFolioModificacion USING( tfm_id )
                INNER JOIN cat_TipoModificacion USING( tmd_id )
                INNER JOIN cat_ModalidadModificacion USING( mmd_id )
                INNER JOIN ".BD_SINPRES.".enc_ClaveFinanciamiento cf ON cf.ecf_id = m.ecf_id
                INNER JOIN ".BD_SINPRES.".cat_ClaveFinanciamiento".APP_EJERCICIO." c USING( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                INNER JOIN cat_OrigenModificacion o ON o.omo_id = m.omo_id
            WHERE m.mod_id IS NOT NULL {$sqlFiltro}
                AND ( m.emo_id = 14 OR m.emo_id = 15 OR m.emo_id = 16 )
            GROUP BY mod_gpo_id, mod_ur_id, omo_id, mod_id, pa.polizaAutorizado {$paginadosql}
        ";

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
            'omo_id' => $rowData['omo_id'],
            'mod_id' => $rowData['mod_id'],
            'mod_concepto' => $rowData['mod_concepto'],
            'tfm_nombre' => $rowData['tfm_nombre'],
            'tmd_nombre' => $rowData['tmd_nombre'],
            'mmd_nombre' => $rowData['mmd_nombre'],
            'omo_nombre' => $rowData['omo_nombre'],
            'mod_oficioSefin' => $rowData['mod_oficioSefin'],
            'mod_oficioSolicitudSefin' => $rowData['mod_oficioSolicitudSefin'],
            'ecf_clave' => $rowData['ecf_clave'],
            'cfi_nombre' => $rowData['cfi_nombre'],
            'mod_ampliacion' => $rowData['mod_ampliacion'],
            'mod_reduccion' => $rowData['mod_reduccion'],
            'estado' => $rowData['estado'],
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
            'agrupador' => ''
        );
    }


    return $datos;
}
