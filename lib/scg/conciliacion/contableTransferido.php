<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function contableTransferido($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array())
{
    $params = [];
    $periodoSql = "";
    $programaticoSql = "";
    $cvefinSql = "";
    $cargaTrabajo = "";
    $ur = "";

    if ($periodo['mesDesde'] == $periodo['mesHasta']) {
        $periodoSql = "AND msc_id = :mesDesde ";
        $params['mesDesde'] = $periodo['mesDesde'];
    } else {
        $periodoSql = "AND ( msc_id BETWEEN :mesDesde AND :mesHasta) ";
        $params['mesDesde'] = $periodo['mesDesde'];
        $params['mesHasta'] = $periodo['mesHasta'];
    }

    if ($programatico['epg_id'] != '') {
        $programaticoSql = "AND epg_id = :epg ";
        $params['epg'] = $programatico['epg_id'];
    }
    
    if ($claveFinanciamiento['ecf_id'] != '') {
        $cvefinSql = "AND ecf_id = :ecf ";
        $params['ecf'] = $claveFinanciamiento['ecf_id'];
    } else if ($claveFinanciamiento['ter_id'] != '') {
        $cvefinSql = "AND ter_id = :ter ";
        $params['ter'] =  $claveFinanciamiento['ter_id'];
    }

    if ($reporte['tipo'] == 2 && $_SESSION[_S]['nvj_id'] < 6) {
        $cargaTrabajo = " INNER JOIN cat_CargaTrabajo t ON t.gpo_id = m.gpo_id AND t.ur_id = m.ur_id
                WHERE t.usr_id = :usr ";
        $params['usr'] = $_SESSION[_S]['usr_id'];
    } else {
        $ur = " WHERE m.gpo_id = :gpo AND m.ur_id = :ur ";
        $params['gpo'] = $gpo_id;
        $params['ur'] = $ur_id;
        if ($ue_id != '') {
            $ur .= " AND m.ue_id = :ue ";
            $params['ue'] = $ue_id;
        }
    }

    $ordenar = "m.gpo_id, m.ur_id";
    $ordenart = "gpo_id, ur_id";
    $key = "m.gpo_id, m.ur_id";
    if ($niveles[2] > 0) {
        $ordenar .= ", m.ue_id";
        $ordenart .= ", ue_id";
        $key .= ", '.', m.ue_id";
    }
    if ($niveles[3] > 0) {
        $ordenar .= ", epg_clave";
        $ordenart .= ", epg_clave";
        $key .= ", '.', IFNULL( epg_clave, '00000000000' )";
    }
    if ($niveles[4] > 0) {
        $ordenar .= ", tg.tg_id";
        $ordenart .= ", tg_id";
        $key .= ", '.', IFNULL( tg.tg_id, '0' )";
    }
    $maxidp_id = " MAX";
    if ($niveles[5] > 0) {
        $maxidp_id = " ";
        $ordenar .= ", idp_id";
        $ordenart .= ", idp_id";
        $key .= ", '.', IFNULL( MAX(idp_id), '0' )";
    }

    if ($niveles[6] > 0) {
        $ordenar .= ", pg_id";
        $ordenart .= ", pg_id";
        $key .= ", '.', IFNULL( max(pg_id), '000' )";
    }
    $maxpe_id = " MAX";
    if ($niveles[7] > 0) {
        $ordenar .= ", pe_id";
        $maxpe_id = " ";
        $ordenart .= ", pe_id";
        $key .= ", '.', IFNULL( max(pe_id), '000' )";
    }
    $maxter_id = " MAX";
    if ($niveles[8] > 0) {
        $maxter_id = " ";
        $ordenar .= ", ter_id";
        $ordenart .= ", ter_id";
        $key .= ", '.', IFNULL( max(ter_id), '0' )";
    }
    if ($niveles[9] > 0) {
        $ordenar .= ", ecf_clave";
        $ordenart .= ", ecf_clave";
        $key .= ", '.', IFNULL( ecf_clave, '000000000' )";
    }

    // Armamos la consulta
    // -----------------------------------------------------------------------------------------------------------------------
    $sql = "
        SELECT m.gpo_id, m.ur_id, max(ur.ur_clave) as ur_clave, max(ur.ur_nombre) as ur_nombre, MAX(x.cta_ue_id) AS ue_id,
            MAX(s.cgsc_cuenta) AS cuenta,
            MAX(x.epg_id) AS epg_id, MAX(oa.prg_id) AS prg_id, MAX(oa.spr_id) AS spr_id,
            MAX(oa.py_id) AS py_id, MAX(oa.oa_id) AS oa_id, MAX(ep.epg_clave) AS epg_clave, MAX(oa.oa_nombre) AS oa_nombre,
            MAX(tg.tg_id) AS tg_id, MAX(tg.tg_nombre) AS tg_nombre,
            $maxidp_id(i.idp_id) AS idp_id, MAX(i.idp_nombre) AS idp_nombre,
            pg.pg_id, MAX(pg.pg_nombre) AS pg_nombre,
            $maxpe_id(pe.pe_id) AS pe_id, MAX(pe.pe_nombre) AS pe_nombre,
            $maxter_id(tr.ter_id) AS ter_id, MAX(tr.ter_nombre) AS ter_nombre,
            MAX(ef.ecf_id) AS ecf_id, ef.ecf_clave, CASE WHEN MAX(cf.cfi_nombre) IS NULL THEN 'Captura Manual' ELSE MAX(cf.cfi_nombre) END AS cfi_nombre,
            SUM( x.pdx_haber ) AS pdx_importe, 
            GROUP_CONCAT( DISTINCT pdx_cmp_id_origen ORDER BY pdx_cmp_id_origen ) AS cmp_id, 
            GROUP_CONCAT( DISTINCT pdx_afe_id_origen ORDER BY pdx_afe_id_origen ) AS afe_id, 
            GROUP_CONCAT( DISTINCT pdx_clc_id_origen ORDER BY pdx_clc_id_origen ) AS clc_id, 
            GROUP_CONCAT( DISTINCT pdx_bdo_id_origen ORDER BY pdx_bdo_id_origen ) AS bdo_id, 
            GROUP_CONCAT( DISTINCT CONCAT( pot_id, '-', prf_id, pma_id ) ) AS poliza, 
            CONCAT( $key ) AS id 
        FROM cg_PolizaMaestro m 
            INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
            LEFT JOIN cat_PartidaGenerica pg USING( pg_id ) 
            LEFT JOIN cat_PartidaEspecifica pe USING( pg_id, pe_id ) 
            LEFT JOIN vin_Cog v USING( pg_id ) 
            LEFT JOIN cat_CgSubcuenta s ON v.ger_id = s.ger_id AND v.cgp_id = s.cgp_id AND v.rub_id = s.rub_id AND v.cgcu_id = s.cgcu_id AND v.cgsc_id = s.cgsc_id 
            LEFT JOIN cat_IdentificadorPartida i USING( idp_id ) 
            LEFT JOIN cat_TipoGasto tg ON pe.tg_id = tg.tg_id 
            LEFT JOIN enc_ClaveFinanciamiento ef USING( ecf_id ) 
            LEFT JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
            LEFT JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " cs USING( cff_id, fte_id, sft_id, ori_id, sor_id )
            LEFT JOIN " . BD_SINPRES . ".cat_TipoRecurso tr USING( ter_id ) 
            LEFT JOIN enc_Programatico ep USING( epg_id ) 
            LEFT JOIN cat_ObraActividad oa ON ep.gpo_id = oa.gpo_id AND ep.ur_id = oa.ur_id AND ep.prg_id = oa.prg_id AND ep.spr_id = oa.spr_id AND ep.py_id = oa.py_id AND ep.oa_id = oa.oa_id 
            INNER JOIN cat_UnidadResponsable ur ON m.gpo_id = ur.gpo_id AND m.ur_id = ur.ur_id 
            $cargaTrabajo
            $ur
            $periodoSql 
            AND m.mmc_id NOT IN ( 'N', 'T', 'P', 'R' ) 
            AND x.ger_id = 4 AND x.cgp_id = 2 AND x.rub_id = 2 AND x.cgcu_id = 1 AND x.cgsc_id = 0 
            AND m.epl_id NOT IN ( 'C', 'H' ) 
            $programaticoSql 
            " . ( $claveFinanciamiento['ter_id'] == 'C' ? 'AND tr.ter_id IS NOT NULL' : $cvefinSql ) . "
        GROUP BY $ordenar 
            UNION ALL 
        SELECT gpo_id, ur_id, MAX(ur_clave) AS ur_clave, MAX(ur_nombre), LPAD( MAX(ue_id), 3, 0 ) AS ue_id,
            MAX(cuenta) AS cuenta,
            MAX(epg_id) AS epg_id, MAX(prg_id) AS prg_id, MAX(spr_id) AS spr_id,
            MAX(py_id) AS py_id, MAX(oa_id) AS oa_id, MAX(epg_clave) AS epg_clave, MAX(oa_nombre) AS oa_nombre,
            MAX(tg_id) AS tg_id, MAX(tg_nombre) AS tg_nombre,
            $maxidp_id(idp_id) AS idp_id, MAX(idp_nombre) AS idp_nombre,
            LPAD( pg_id, 3, 0 ) AS pg_id, MAX(pg_nombre) AS pg_nombre,
            LPAD( MAX(pe_id), 3, 0 ) AS pe_id, MAX(pe_nombre) AS pe_nombre,
            $maxter_id(ter_id) AS ter_id, MAX(ter_nombre) AS ter_nombre,
            MAX(ecf_id) AS ecf_id, ecf_clave, MAX(cfi_nombre) AS cfi_nombre,
            SUM( pdx_importe ) AS pdx_importe,
            GROUP_CONCAT( DISTINCT cmp_id ORDER BY cmp_id ) AS cmp_id, 
            GROUP_CONCAT( DISTINCT afe_id ORDER BY afe_id ) AS afe_id, 
            GROUP_CONCAT( DISTINCT clc_id ORDER BY clc_id ) AS clc_id, 
            GROUP_CONCAT( DISTINCT bdo_id ORDER BY bdo_id ) AS bdo_id, 
            GROUP_CONCAT( DISTINCT poliza ORDER BY poliza ) AS poliza, 
            MAX(id) AS id
        FROM ( 
            SELECT m.gpo_id, m.ur_id,MAX(ur.ur_clave) AS ur_clave, MAX(ur.ur_nombre) AS ur_nombre, MAX(x.cta_ue_id) AS ue_id,
                MAX(s.cgsc_cuenta) AS cuenta,
                MAX(x.epg_id) AS epg_id, MAX(oa.prg_id) AS prg_id, MAX(oa.spr_id) AS spr_id,
                MAX(oa.py_id) AS py_id, MAX(oa.oa_id) AS oa_id, MAX(ep.epg_clave) AS epg_clave, MAX(oa.oa_nombre) AS oa_nombre,
                MAX(tg.tg_id) AS tg_id, MAX(tg.tg_nombre) AS tg_nombre,
                $maxidp_id(i.idp_id) AS idp_id, MAX(i.idp_nombre) AS idp_nombre,
                pg.pg_id, MAX(pg.pg_nombre) AS pg_nombre,
                $maxpe_id(pe.pe_id) AS pe_id, MAX(pe.pe_nombre) AS pe_nombre,
                $maxter_id(tr.ter_id) AS ter_id, MAX(tr.ter_nombre) AS ter_nombre,
                max(ef.ecf_id) AS ecf_id, ef.ecf_clave, max(cf.cfi_nombre) AS cfi_nombre,
                SUM( x.pdx_haber ) AS pdx_importe, 
                GROUP_CONCAT( DISTINCT pdx_cmp_id_origen ORDER BY pdx_cmp_id_origen ) AS cmp_id, 
                GROUP_CONCAT( DISTINCT pdx_afe_id_origen ORDER BY pdx_afe_id_origen ) AS afe_id, 
                GROUP_CONCAT( DISTINCT pdx_clc_id_origen ORDER BY pdx_clc_id_origen ) AS clc_id, 
                GROUP_CONCAT( DISTINCT pdx_bdo_id_origen ORDER BY pdx_bdo_id_origen ) AS bdo_id, 
                GROUP_CONCAT( DISTINCT CONCAT( pot_id, '-', prf_id, pma_id ) ) AS poliza, 
                CONCAT( $key ) AS id 
            FROM cg_PolizaMaestro m 
                INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
                INNER JOIN cat_PartidaGenerica pg USING( pg_id ) 
                INNER JOIN cat_PartidaEspecifica pe USING( pg_id, pe_id ) 
                LEFT JOIN vin_Cog v USING( pg_id ) 
                LEFT JOIN cat_CgSubcuenta s ON v.ger_id = s.ger_id AND v.cgp_id = s.cgp_id AND v.rub_id = s.rub_id AND v.cgcu_id = s.cgcu_id AND v.cgsc_id = s.cgsc_id 
                INNER JOIN cat_IdentificadorPartida i USING( idp_id ) 
                INNER JOIN cat_TipoGasto tg ON pe.tg_id = tg.tg_id 
                INNER JOIN enc_ClaveFinanciamiento ef USING( ecf_id ) 
                INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " cs USING( cff_id, fte_id, sft_id, ori_id, sor_id )
                INNER JOIN " . BD_SINPRES . ".cat_TipoRecurso tr USING( ter_id ) 
                LEFT JOIN enc_Programatico ep USING( epg_id ) 
                LEFT JOIN cat_ObraActividad oa ON ep.gpo_id = oa.gpo_id AND ep.ur_id = oa.ur_id AND ep.prg_id = oa.prg_id AND ep.spr_id = oa.spr_id AND ep.py_id = oa.py_id AND ep.oa_id = oa.oa_id 
                INNER JOIN cat_UnidadResponsable ur ON m.gpo_id = ur.gpo_id AND m.ur_id = ur.ur_id 
                $cargaTrabajo
                $ur
                $periodoSql 
                AND m.mmc_id = 'N' 
                AND x.ger_id = 4 AND x.cgp_id = 2 AND x.rub_id = 2 AND x.cgcu_id = 1 AND x.cgsc_id = 0 
                AND m.epl_id NOT IN ( 'C', 'H' ) 
                $programaticoSql 
                " . ( $claveFinanciamiento['ter_id'] == 'C' ? 'AND tr.ter_id IS NOT NULL' : $cvefinSql ) . "
            GROUP BY $ordenar 
                UNION ALL 
            SELECT m.gpo_id, m.ur_id,  MAX(ur.ur_clave) AS ur_clave, MAX(ur.ur_nombre) AS ur_nombre,  MAX(x.cta_ue_id) AS ue_id,
                MAX(s.cgsc_cuenta) AS cuenta,
                MAX(x.epg_id) AS epg_id, MAX(oa.prg_id) AS prg_id, MAX(oa.spr_id) AS spr_id,
                MAX(oa.py_id) AS py_id, MAX(oa.oa_id) AS oa_id, MAX(ep.epg_clave) AS epg_clave, MAX(oa.oa_nombre) AS oa_nombre,
                MAX(tg.tg_id) AS tg_id, MAX(tg.tg_nombre) AS tg_nombre,
                $maxidp_id(i.idp_id) AS idp_id, MAX(i.idp_nombre) AS idp_nombre,
                pg.pg_id, MAX(pg.pg_nombre) AS pg_nombre,
                $maxpe_id(pe.pe_id) AS pe_id, MAX(pe.pe_nombre) AS pe_nombre,
                $maxter_id(tr.ter_id) AS ter_id, MAX(tr.ter_nombre) AS ter_nombre,

                max(ef.ecf_id) AS ecf_id, ef.ecf_clave, max(cf.cfi_nombre) AS cfi_nombre,
                SUM( x.pdx_haber ) AS pdx_importe, 
                GROUP_CONCAT( DISTINCT pdx_cmp_id_origen ORDER BY pdx_cmp_id_origen ) AS cmp_id, 
                GROUP_CONCAT( DISTINCT pdx_afe_id_origen ORDER BY pdx_afe_id_origen ) AS afe_id, 
                GROUP_CONCAT( DISTINCT pdx_clc_id_origen ORDER BY pdx_clc_id_origen ) AS clc_id, 
                GROUP_CONCAT( DISTINCT pdx_bdo_id_origen ORDER BY pdx_bdo_id_origen ) AS bdo_id, 
                GROUP_CONCAT( DISTINCT CONCAT( pot_id, '-', prf_id, pma_id ) ) AS poliza, 
                CONCAT( $key ) AS id 
            FROM cg_PolizaMaestro m 
                INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
                INNER JOIN cat_UnidadResponsable ur USING( gpo_id, ur_id ) 
                INNER JOIN cat_PartidaGenerica pg USING( pg_id ) 
                INNER JOIN cat_PartidaEspecifica pe USING( pg_id, pe_id ) 
                LEFT JOIN vin_Cog v USING( pg_id ) 
                LEFT JOIN cat_CgSubcuenta s ON v.ger_id = s.ger_id AND v.cgp_id = s.cgp_id AND v.rub_id = s.rub_id AND v.cgcu_id = s.cgcu_id AND v.cgsc_id = s.cgsc_id 
                INNER JOIN cat_IdentificadorPartida i USING( idp_id ) 
                INNER JOIN cat_TipoGasto tg ON pe.tg_id = tg.tg_id 
                INNER JOIN enc_ClaveFinanciamiento ef USING( ecf_id ) 
                INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " cs USING( cff_id, fte_id, sft_id, ori_id, sor_id )
                INNER JOIN " . BD_SINPRES . ".cat_TipoRecurso tr USING( ter_id ) 
                LEFT JOIN enc_Programatico ep USING( epg_id ) 
                LEFT JOIN cat_ObraActividad oa ON ep.gpo_id = oa.gpo_id AND ep.ur_id = oa.ur_id AND ep.prg_id = oa.prg_id AND ep.spr_id = oa.spr_id AND ep.py_id = oa.py_id AND ep.oa_id = oa.oa_id 
                $cargaTrabajo
                $ur
                $periodoSql 
                AND m.mmc_id = 'T' 
                AND x.ger_id = 4 AND x.cgp_id = 2 AND x.rub_id = 2 AND x.cgcu_id = 1 AND x.cgsc_id = 0 
                AND m.epl_id NOT IN ( 'C', 'H' ) 
                $programaticoSql 
                " . ( $claveFinanciamiento['ter_id'] == 'C' ? 'AND tr.ter_id IS NOT NULL' : $cvefinSql ) . "
            GROUP BY $ordenar 
                UNION ALL 
                    SELECT m.gpo_id, m.ur_id, MAX(ur.ur_clave) AS ur_clave, MAX(ur.ur_nombre) AS ur_nombre, MAX(x.cta_ue_id) AS ue_id,
                    MAX(s.cgsc_cuenta) AS cuenta,
                    MAX(x.epg_id) AS epg_id, MAX(oa.prg_id) AS prg_id, MAX(oa.spr_id) AS spr_id,
                    MAX(oa.py_id) AS py_id, MAX(oa.oa_id) AS oa_id, MAX(ep.epg_clave) AS epg_clave, MAX(oa.oa_nombre) AS oa_nombre,
                    MAX(tg.tg_id) AS tg_id, MAX(tg.tg_nombre) AS tg_nombre,
                    $maxidp_id(i.idp_id) AS idp_id, MAX(i.idp_nombre) AS idp_nombre,
                    pg.pg_id, MAX(pg.pg_nombre) AS pg_nombre,
                    $maxpe_id(pe.pe_id) AS pe_id, MAX(pe.pe_nombre) AS pe_nombre,
                    $maxter_id(tr.ter_id) AS ter_id, MAX(tr.ter_nombre) AS ter_nombre,

                max(ef.ecf_id) AS ecf_id, ef.ecf_clave, max(cf.cfi_nombre) AS cfi_nombre, SUM( x.pdx_haber ) AS pdx_importe,
                GROUP_CONCAT( DISTINCT pdx_cmp_id_origen ORDER BY pdx_cmp_id_origen ) AS cmp_id, 
                GROUP_CONCAT( DISTINCT pdx_afe_id_origen ORDER BY pdx_afe_id_origen ) AS afe_id, 
                GROUP_CONCAT( DISTINCT pdx_clc_id_origen ORDER BY pdx_clc_id_origen ) AS clc_id, 
                GROUP_CONCAT( DISTINCT pdx_bdo_id_origen ORDER BY pdx_bdo_id_origen ) AS bdo_id, 
                GROUP_CONCAT( DISTINCT CONCAT( pot_id, '-', prf_id, pma_id ) ) AS poliza, 
                CONCAT( $key ) AS id 
            FROM cg_PolizaMaestro m 
                INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
                INNER JOIN cat_PartidaGenerica pg USING( pg_id ) 
                INNER JOIN cat_PartidaEspecifica pe USING( pg_id, pe_id ) 
                LEFT JOIN vin_Cog v USING( pg_id ) 
                LEFT JOIN cat_CgSubcuenta s ON v.ger_id = s.ger_id AND v.cgp_id = s.cgp_id AND v.rub_id = s.rub_id AND v.cgcu_id = s.cgcu_id AND v.cgsc_id = s.cgsc_id 
                INNER JOIN cat_IdentificadorPartida i USING( idp_id ) 
                INNER JOIN cat_TipoGasto tg ON pe.tg_id = tg.tg_id 
                INNER JOIN enc_ClaveFinanciamiento ef USING( ecf_id ) 
                INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " cs USING( cff_id, fte_id, sft_id, ori_id, sor_id )
                INNER JOIN " . BD_SINPRES . ".cat_TipoRecurso tr USING( ter_id ) 
                LEFT JOIN enc_Programatico ep USING( epg_id ) 
                LEFT JOIN cat_ObraActividad oa ON ep.gpo_id = oa.gpo_id AND ep.ur_id = oa.ur_id AND ep.prg_id = oa.prg_id AND ep.spr_id = oa.spr_id AND ep.py_id = oa.py_id AND ep.oa_id = oa.oa_id 
                INNER JOIN cat_UnidadResponsable ur ON m.gpo_id = ur.gpo_id AND m.ur_id = ur.ur_id 
                $cargaTrabajo
                $ur
                $periodoSql
                AND m.mmc_id = 'P' 
                AND x.ger_id = 4 AND x.cgp_id = 2 AND x.rub_id = 2 AND x.cgcu_id = 1 AND x.cgsc_id = 0 
                AND m.epl_id NOT IN ( 'C', 'H' ) 
                $programaticoSql
                " . ( $claveFinanciamiento['ter_id'] == 'C' ? 'AND tr.ter_id IS NOT NULL' : $cvefinSql ) . "
            GROUP BY $ordenar
            ORDER BY $ordenart
        )m 
        GROUP BY $ordenart
        ORDER BY gpo_id, ur_id 
    ";
    
    //variable de conexion para los saldos iniciales
    $contable = new AccesoDatos();
    $contable->conectar();
    $contable->setSql($sql);
    $contable->consultar($params);
    $contable->desconectar();

    $datos = array();

    while ($rowContable = $contable->extraer()) {

        $datos[$rowContable['id']] = array(
            'id' => $rowContable['id'],
            'gpo_id' => $rowContable['gpo_id'],
            'ur_id' => $rowContable['ur_id'],
            'ur_clave' => $rowContable['ur_clave'],
            'ur_nombre' => $rowContable['ur_nombre'],
            'ue_id' => $rowContable['ue_id'],
            'cmp_id' => $rowContable['cmp_id'],
            'afe_id' => $rowContable['afe_id'],
            'clc_id' => $rowContable['clc_id'],
            'bdo_id' => $rowContable['bdo_id'],
            'poliza' => $rowContable['poliza'],
            'cuenta' => $rowContable['cuenta'],
            'epg_id' => $rowContable['epg_id'],
            'oa_clave' => $rowContable['epg_clave'],
            'oa_nombre' => $rowContable['oa_nombre'],
            'tg_id' => $rowContable['tg_id'],
            'tg_nombre' => $rowContable['tg_nombre'],
            'idp_id' => $rowContable['idp_id'],
            'idp_nombre' => $rowContable['idp_nombre'],
            'pg_id' => $rowContable['pg_id'],
            'pg_nombre' => $rowContable['pg_nombre'],
            'pe_id' => $rowContable['pe_id'],
            'pe_nombre' => $rowContable['pe_nombre'],
            'ter_id' => $rowContable['ter_id'],
            'ter_nombre' => $rowContable['ter_nombre'],
            'ecf_id' => $rowContable['ecf_id'],
            'cfi_clave' => $rowContable['ecf_clave'],
            'cfi_nombre' => $rowContable['cfi_nombre'],
            'pdx_importe' => $rowContable['pdx_importe']
        );
    }

    if (count($datos) == 0) {

        $datos['vacio'] = array(
            'id' => '',
            'gpo_id' => '',
            'ur_id' => '',
            'ur_clave' => '',
            'ur_nombre' => '',
            'ue_id' => '',
            'cmp_id' => '',
            'afe_id' => '',
            'clc_id' => '',
            'bdo_id' => '',
            'poliza' => '',
            'cuenta' => '',
            'epg_id' => '',
            'oa_clave' => '',
            'oa_nombre' => '',
            'tg_id' => '',
            'tg_nombre' => '',
            'idp_id' => '',
            'idp_nombre' => '',
            'pg_id' => '',
            'pg_nombre' => '',
            'pe_id' => '',
            'pe_nombre' => '',
            'ter_id' => '',
            'ter_nombre' => '',
            'ecf_id' => '',
            'cfi_clave' => '',
            'cfi_nombre' => '',
            'pdx_importe' => ''
                )
        ;
    }

    return $datos;
}

?>