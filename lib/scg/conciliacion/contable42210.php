<?php
    
    set_time_limit(0);
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
    
    function contable42210( $gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array() ){
        $params = [];
        $periodoSql = ( $periodo['mesDesde'] == $periodo['mesHasta'] ? "AND msc_id = ".$periodo['mesDesde']:"AND ( msc_id BETWEEN ".$periodo['mesDesde']." AND ".$periodo['mesHasta']." )" );
        $programaticoSql = "";
        $cvefinSql = "";
        
        $ordenar = "gpo_id, ur_id";
        $agrupador = "m.gpo_id, m.ur_id";
        $key = "m.gpo_id, m.ur_id";

        if ($programatico['epg_id'] != '') {
            $programaticoSql = " epg_id = :epg_id";
            $params[':epg_id'] = $programatico['epg_id'];
        }
        
        if ($claveFinanciamiento['ecf_id'] != '') {
            $cvefinSql = " ecf_id = :ecf_id";
            $params[':ecf_id'] =  $claveFinanciamiento['ecf_id'];
        } elseif ($claveFinanciamiento['ter_id'] != '') {
            $cvefinSql = " ter_id = :ter_id";
            $params[':ter_id'] = $claveFinanciamiento['ter_id'];
        } else {
            $cvefinSql = '';
        }
        

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
        if( $niveles[10] > 0 ){
            $ordenar .= ", cuenta";
            $agrupador .= ", cta_id";
            $key .= ", '.', cta_id";
        }
        
        if ($reporte['tipo'] == 2 && $_SESSION[_S]['nvj_id'] < 6) {
            $whereClause = " INNER JOIN cat_CargaTrabajo t ON t.gpo_id = m.gpo_id AND t.ur_id = m.ur_id WHERE t.usr_id = :usr_id ";
            $params[':usr_id'] = $_SESSION[_S]['usr_id'];
        } elseif ($reporte['tipo'] == 1) {
            $whereClause = " WHERE m.gpo_id = :gpo_id AND m.ur_id = :ur_id ";
            $params[':gpo_id'] = $gpo_id;
            $params[':ur_id'] = $ur_id;
        } elseif ($_SESSION[_S]['nvj_id'] > 5 && $reporte['tipo'] != 1) {
            $whereClause = " WHERE 1 ";
        } elseif ($ue_id != '') {
            $whereClause = " AND x.cta_ue_id = :ue_id ";
            $params[':ue_id'] = $ue_id; 
        }

        $prefijoSql = '';
        $cvefinSql = ( $claveFinanciamiento['ter_id'] == 'C' ? "tr.ter_id IS NOT NULL":$cvefinSql );
        $sufijoSql = ( $programaticoSql != '' ? " AND $programaticoSql":"" );
        $sufijoSql .= ( $cvefinSql != '' ? " AND ".$cvefinSql." ":"" );
        
        // Armamos la consulta
        $sql = $prefijoSql."
        SELECT gpo_id, ur_id, ur_clave, ur_nombre, pdx_ur_clave_origen, 
            ue_id, 
            epg_id, prg_id, spr_id, py_id, oa_id, epg_clave, oa_nombre, 
            tg_id, tg_nombre, 
            idp_id, idp_nombre, 
            pg_id, pg_nombre, 
            pe_id, pe_nombre, 
            ecf_id, ecf_clave, cfi_nombre, 
            estatal, federal, 
            clc_id, afe_id, bdo_id, cmp_id, poliza, 
            rfc_id, 
            rfc_cta_id AS cuenta, CASE WHEN cta_nombre IS NULL THEN nombre ELSE cta_nombre END AS nombre, 
            id 
        FROM ( 
            SELECT m.gpo_id, m.ur_id, ur.ur_clave, ur.ur_nombre,  MAX(x.cta_ue_id) AS ue_id,
                MAX(x.cta_id) AS rfc_cta_id, MAX(pv.rfc_id) AS rfc_id,
                CASE MAX(soc_id) WHEN 0 THEN CONCAT( MAX(pv.rfc_nombre), ' ', MAX(pv.rfc_paterno), ' ', MAX(pv.rfc_materno) ) ELSE CONCAT( MAX(pv.rfc_nombre), ', ', MAX(soc_nombre) ) END AS nombre,
                MAX(cc.cta_nombre) AS cta_nombre,
                SUM( CASE x.cgc_id WHEN '001' THEN x.pdx_haber ELSE 0 END ) AS estatal,
                SUM( CASE x.cgc_id WHEN '002' THEN x.pdx_haber ELSE 0 END ) AS federal,
                GROUP_CONCAT( DISTINCT CONCAT( x.pdx_gpo_id_origen, x.pdx_ur_id_origen ) ORDER BY CONCAT( x.pdx_gpo_id_origen, x.pdx_ur_id_origen ) ) AS pdx_ur_clave_origen,
                GROUP_CONCAT( DISTINCT x.pdx_clc_id_origen ORDER BY x.pdx_clc_id_origen ) AS clc_id,
                GROUP_CONCAT( DISTINCT x.pdx_afe_id_origen ORDER BY x.pdx_afe_id_origen ) AS afe_id,
                GROUP_CONCAT( DISTINCT x.pdx_bdo_id_origen ORDER BY x.pdx_bdo_id_origen ) AS bdo_id,
                GROUP_CONCAT( DISTINCT x.pdx_cmp_id_origen ORDER BY x.pdx_cmp_id_origen ) AS cmp_id,
                GROUP_CONCAT( DISTINCT CONCAT( m.pot_id, '-', m.prf_id, m.pma_id ) ) AS poliza,
                MAX(epg_id) AS epg_id, MAX(oa.prg_id) AS prg_id, MAX(oa.spr_id) AS spr_id, MAX(oa.py_id) AS py_id,
                MAX(oa.oa_id) AS oa_id, MAX(ep.epg_clave) AS epg_clave, MAX(oa.oa_nombre) AS oa_nombre,
                MAX(tg.tg_id) AS tg_id, MAX(tg.tg_nombre) AS tg_nombre,
                MAX(i.idp_id) AS idp_id, MAX(i.idp_nombre) AS idp_nombre,
                pg.pg_id, pg.pg_nombre,
                MAX(pe.pe_id) AS pe_id, MAX(pe.pe_nombre) AS pe_nombre,
                MAX(ef.ecf_id) AS ecf_id, MAX(ef.ecf_clave) as ecf_clave, MAX(cf.cfi_nombre) AS cfi_nombre,
                CONCAT( $key ) AS id 
            FROM cg_PolizaMaestro m 
                INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
                INNER JOIN cat_UnidadResponsable ur USING( gpo_id, ur_id ) 
                INNER JOIN cg_PlanCuentas cc USING( cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id ) 
                LEFT JOIN cat_PartidaGenerica pg USING( pg_id ) 
                LEFT JOIN cat_PartidaEspecifica pe USING( pg_id, pe_id ) 
                LEFT JOIN cat_IdentificadorPartida i USING( idp_id ) 
                LEFT JOIN cat_TipoGasto tg ON pe.tg_id = tg.tg_id 
                LEFT JOIN enc_ClaveFinanciamiento ef USING( ecf_id ) 
                INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " cs USING( cff_id, fte_id, sft_id, ori_id, sor_id )
                INNER JOIN " . BD_SINPRES . ".cat_TipoRecurso tr USING( ter_id ) 
                LEFT JOIN enc_Programatico ep USING( epg_id ) 
                LEFT JOIN cat_ObraActividad oa ON ep.gpo_id = oa.gpo_id AND ep.ur_id = oa.ur_id AND ep.prg_id = oa.prg_id AND ep.spr_id = oa.spr_id AND ep.py_id = oa.py_id AND ep.oa_id = oa.oa_id 
                LEFT JOIN " . BD_SINPRES . ".cat_Proveedores pv ON rfc_cta_id = cta_id 
                LEFT JOIN " . BD_SINPRES . ".cat_SociedadProveedor USING( soc_id ) 
                $whereClause
                $periodoSql
                AND x.ger_id = 4 AND x.cgp_id = 2 AND x.rub_id = 2 AND x.cgcu_id = 1 AND x.cgsc_id = 0 
                AND m.epl_id NOT IN ( 'C', 'H' ) 
                $sufijoSql
            GROUP BY $agrupador 
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
                    'pdx_ur_clave_origen'=>$rowContable['pdx_ur_clave_origen'], 
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
                    'ecf_id'=>$rowContable['ecf_id'], 
                    'cfi_clave'=>$rowContable['ecf_clave'], 
                    'cfi_nombre'=>$rowContable['cfi_nombre'], 
                    'rfc_id'=>$rowContable['rfc_id'], 
                    'cuenta'=>$rowContable['cuenta'], 
                    'nombre'=>$rowContable['nombre'], 
                    'estatal'=>$rowContable['estatal'], 
                    'federal'=>$rowContable['federal'], 
                    'total'=>$rowContable['estatal'] + $rowContable['federal'] 
                
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
                    'pdx_ur_clave_origen'=>'', 
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
                    'ecf_id'=>'', 
                    'cfi_clave'=>'', 
                    'cfi_nombre'=>'', 
                    'rfc_id'=>'', 
                    'cuenta'=>'', 
                    'nombre'=>'', 
                    'estatal'=>'', 
                    'federal'=>'', 
                    'total'=>'' 
                )
            ;
        }
        
        return $datos;
        
    }
    
?>