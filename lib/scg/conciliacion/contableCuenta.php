<?php
    
    set_time_limit(0);
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
    
    function contableCuenta( $gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array() ){
        $params = array();
        $periodoSql = ( $periodo['mesDesde'] == $periodo['mesHasta'] ? "AND msc_id = ".$periodo['mesDesde']:"AND ( msc_id BETWEEN ".$periodo['mesDesde']." AND ".$periodo['mesHasta']." )" );
        $programaticoSql = '';
        $cvefinSql = '';

        if ($programatico['epg_id'] != '') {
            $programaticoSql = "AND epg_id = :epg_id";
            $params[':epg_id'] = $programatico['epg_id'];
        }

        
        if (!empty($claveFinanciamiento['ecf_id'])) {
            $cvefinSql = " AND ecf_id = :ecf_id ";
            $params[':ecf_id'] = $claveFinanciamiento['ecf_id'];
        } elseif (!empty($claveFinanciamiento['ter_id'])) {
            $cvefinSql = " AND ter_id = :ter_id ";
            $params[':ter_id'] = $claveFinanciamiento['ter_id'];
        }

        $ordenar = "gpo_id, ur_id";
        $agrupador = "m.gpo_id, m.ur_id";
        $key = "m.gpo_id, m.ur_id";
        if( $niveles[2] > 0 ){
            $ordenar .= ", ue_id";
            $agrupador .= ", x.cta_ue_id";
            $key .= ", '.', x.cta_ue_id";
        }
        if( $niveles[3] > 0 ){
            $ordenar .= ", epg_clave";
            $agrupador .= ", ep.epg_clave";
            $key .= ", '.', IFNULL( ep.epg_clave, '00000000000' )";
        }
        if( $niveles[4] > 0 ){
            $ordenar .= ", tg_id";
            $agrupador .= ", tg.tg_id";
            $key .= ", '.', IFNULL( tg.tg_id, '0' )";
        }
        if( $niveles[5] > 0 ){
            $ordenar .= ", idp_id";
            $agrupador .= ", i.idp_id";
            $key .= ", '.', IFNULL( i.idp_id, '0' )";
        }
        if( $niveles[6] > 0 ){
            $ordenar .= ", pg_id";
            $agrupador .= ", pg.pg_id";
            $key .= ", '.', IFNULL( pg.pg_id, '000' )";
        }
        if( $niveles[7] > 0 ){
            $ordenar .= ", pe_id";
            $agrupador .= ", pe.pe_id";
            $key .= ", '.', IFNULL( pe.pe_id, '000' )";
        }
        if( $niveles[8] > 0 ){
            $ordenar .= ", ter_id";
            $agrupador .= ", tr.ter_id";
            $key .= ", '.', IFNULL( tr.ter_id, '0' )";
        }
        if( $niveles[9] > 0 ){
            $ordenar .= ", ecf_clave";
            $agrupador .= ", ef.ecf_clave";
            $key .= ", '.', IFNULL( ef.ecf_clave, '000000000' )";
        }
        
        $key .= ", '.', ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id";
        $agrupador .= ", ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id";
        // Armamos la consulta
        $sql = "
        SELECT gpo_id, ur_id, ur_clave, ur_nombre, ue_id, 
            epg_id, prg_id, spr_id, py_id, oa_id, epg_clave, oa_nombre, 
            tg_id, tg_nombre, 
            idp_id, idp_nombre, 
            pg_id, pg_nombre, 
            pe_id, pe_nombre, 
            ter_id, ter_nombre, 
            ecf_id, ecf_clave, cfi_nombre, 
            pdx_debe, pdx_haber, 
            clc_id, afe_id, bdo_id, cmp_id, poliza, 
            cuenta, cta_nombre, 
            pdx_ejercicio, 
            id 
        FROM ( 
            SELECT m.gpo_id, m.ur_id, ur.ur_clave, ur.ur_nombre,MIN(x.cta_ue_id) AS ue_id,
                CONCAT( ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', cgsc_id, '.', cgc_id, '.', cta_id ) AS cuenta, 
                MIN(cta_nombre) AS cta_nombre, 
                SUM( pdx_debe ) AS pdx_debe, 
                SUM( pdx_haber ) AS pdx_haber, 
                GROUP_CONCAT( DISTINCT pdx_clc_id_origen ORDER BY pdx_clc_id_origen ) AS clc_id, 
                GROUP_CONCAT( DISTINCT pdx_afe_id_origen ORDER BY pdx_afe_id_origen ) AS afe_id, 
                GROUP_CONCAT( DISTINCT pdx_bdo_id_origen ORDER BY pdx_bdo_id_origen ) AS bdo_id, 
                GROUP_CONCAT( DISTINCT pdx_cmp_id_origen ORDER BY pdx_cmp_id_origen ) AS cmp_id, 
                GROUP_CONCAT( DISTINCT CONCAT( pot_id, '-', prf_id, pma_id ) ) AS poliza, 
                MIN(epg_id) AS epg_id, MIN(oa.prg_id) AS prg_id, MIN(oa.spr_id) AS spr_id, MIN(oa.py_id) AS py_id, MIN(oa.oa_id) AS oa_id,
                MIN(ep.epg_clave) AS epg_clave, MAX(oa.oa_nombre) AS oa_nombre,
                MIN(tg.tg_id) AS tg_id, MIN(tg_nombre) AS tg_nombre,
                MIN(idp_id) AS idp_id, MIN(idp_nombre) AS idp_nombre,
                MIN(pg.pg_id) AS pg_id,  MIN(pg_nombre) AS pg_nombre,
                MIN(pe_id) AS pe_id, MIN(pe_nombre) AS pe_nombre,
                MIN(ter_id) AS ter_id, MIN(ter_nombre) AS ter_nombre,
                MIN(ecf_id) AS ecf_id, MIN(ecf_clave) AS ecf_clave, MIN(cfi_nombre) AS cfi_nombre,
                pdx_ejercicio,
                CONCAT($key, IFNULL(pdx_ejercicio, 0000)) AS id 
            FROM cg_PolizaMaestro m 
                INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
                INNER JOIN cat_UnidadResponsable ur USING( gpo_id, ur_id ) 
                INNER JOIN cg_PlanCuentas cc USING( cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id ) 
                LEFT JOIN cat_PartidaGenerica pg USING( pg_id ) 
                LEFT JOIN cat_PartidaEspecifica pe USING( pg_id, pe_id ) 
                LEFT JOIN cat_IdentificadorPartida i USING( idp_id ) 
                LEFT JOIN cat_TipoGasto tg ON pe.tg_id = tg.tg_id 
                LEFT JOIN ".BD_SINPRES.".enc_ClaveFinanciamiento ef USING( ecf_id ) 
                LEFT JOIN ".BD_SINPRES.".cat_ClaveFinanciamiento".APP_EJERCICIO." cf USING( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                LEFT JOIN ".BD_SINPRES.".cat_SuborigenRecurso".APP_EJERCICIO." cs USING( cff_id, fte_id, sft_id, ori_id, sor_id )
                LEFt JOIN ".BD_SINPRES.".cat_TipoRecurso tr USING( ter_id ) 
                LEFT JOIN enc_Programatico ep USING( epg_id ) 
                LEFT JOIN cat_ObraActividad oa ON ep.gpo_id = oa.gpo_id AND ep.ur_id = oa.ur_id AND ep.prg_id = oa.prg_id AND ep.spr_id = oa.spr_id AND ep.py_id = oa.py_id AND ep.oa_id = oa.oa_id 
            ".( ( $reporte['tipo'] == 2 && $_SESSION[_S]['nvj_id'] < 6 ) ? "   INNER JOIN cat_CargaTrabajo t ON t.gpo_id = m.gpo_id AND t.ur_id = m.ur_id
            WHERE t.usr_id = ".$_SESSION[_S]['usr_id']:"WHERE m.gpo_id = $gpo_id AND m.ur_id = $ur_id " )."
                ".( ($ue_id != '') ? "AND x.cta_ue_id = $ue_id ":"" )."
                $periodoSql
                ".( $parametroExtra['ger_id'] != '' ? "AND ger_id = ".$parametroExtra['ger_id']." ":"" )."
                AND m.epl_id NOT IN ( 'C', 'H' ) 
                $programaticoSql
                ".( $claveFinanciamiento['ter_id'] == 'C' ? 'AND tr.ter_id IS NOT NULL': $cvefinSql )."
            GROUP BY $agrupador, pdx_ejercicio 
        )c 
        ORDER BY $ordenar 
        ";

        $contable = new AccesoDatos();//variable de konexion para los saldos iniciales
        $contable->conectar();
        $contable->setSql( $sql );
        $contable->consultar($params);
        $contable->desconectar();
        
        $datos = array();
        
        while( $rowContable = $contable->extraer() ){
            
            $datos[$rowContable['id']] = array( 
                    'id'=>$rowContable['id'],
                    'gpo_id'=>$rowContable['gpo_id'],
                    'ur_id'=>$rowContable['ur_id'],
                    'ur_clave'=>$rowContable['ur_clave'],
                    'ur_nombre'=>$rowContable['ur_nombre'],
                    'ue_id'=>$rowContable['ue_id'],
                    'cmp_id'=>$rowContable['cmp_id'], 
                    'afe_id'=>$rowContable['afe_id'], 
                    'clc_id'=>$rowContable['clc_id'], 
                    'bdo_id'=>$rowContable['bdo_id'], 
                    'poliza'=>$rowContable['poliza'], 
                    'epg_id'=>$rowContable['epg_id'], 
                    'oa_clave'=>$rowContable['epg_clave'], 
                    'oa_nombre'=>$rowContable['oa_nombre'], 
                    'tg_id'=>$rowContable['tg_id'], 
                    'tg_nombre'=>$rowContable['tg_nombre'], 
                    'idp_id'=>$rowContable['idp_id'], 
                    'idp_nombre'=>$rowContable['idp_nombre'], 
                    'pg_id'=>$rowContable['pg_id'], 
                    'pg_nombre'=>$rowContable['pg_nombre'], 
                    'pe_id'=>$rowContable['pe_id'], 
                    'pe_nombre'=>$rowContable['pe_nombre'], 
                    'ter_id'=>$rowContable['ter_id'], 
                    'ter_nombre'=>$rowContable['ter_nombre'], 
                    'ecf_id'=>$rowContable['ecf_id'], 
                    'cfi_clave'=>$rowContable['ecf_clave'], 
                    'cfi_nombre'=>$rowContable['cfi_nombre'], 
                    'cuenta'=>$rowContable['cuenta'], 
                    'pdx_debe'=>$rowContable['pdx_debe'], 
                    'pdx_haber'=>$rowContable['pdx_haber'], 
                    'cta_nombre'=>$rowContable['cta_nombre'], 
                    'pdx_ejercicio'=>$rowContable['pdx_ejercicio'] 
                
            );
            
        }
        
        if( count( $datos ) == 0 ) {
            
            $datos['vacio'] = array( 
                    'id'=>'',
                    'gpo_id'=>'',
                    'ur_id'=>'',
                    'ur_clave'=>'',
                    'ur_nombre'=>'',
                    'ue_id'=>'',
                    'cmp_id'=>'', 
                    'afe_id'=>'', 
                    'clc_id'=>'', 
                    'bdo_id'=>'', 
                    'poliza'=>'', 
                    'epg_id'=>'', 
                    'oa_clave'=>'', 
                    'oa_nombre'=>'', 
                    'tg_id'=>'', 
                    'tg_nombre'=>'', 
                    'idp_id'=>'', 
                    'idp_nombre'=>'', 
                    'pg_id'=>'', 
                    'pg_nombre'=>'', 
                    'pe_id'=>'', 
                    'pe_nombre'=>'', 
                    'ter_id'=>'', 
                    'ter_nombre'=>'', 
                    'ecf_id'=>'', 
                    'cfi_clave'=>'', 
                    'cfi_nombre'=>'', 
                    'pdx_debe'=>'', 
                    'pdx_haber'=>'', 
                    'cuenta'=>'', 
                    'cta_nombre'=>'', 
                    'pdx_ejercicio'=>'' 
                )
            ;
            
        }

        return $datos;
        
    }
    
?>