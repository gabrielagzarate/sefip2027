<?php
    
    set_time_limit(0);
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
    
    function contable11131( $gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array() ){
        //print_r($claveFinanciamiento);
        $periodoSql = ( $periodo['mesDesde'] == $periodo['mesHasta'] ? "AND msc_id = ".$periodo['mesDesde']:"AND ( msc_id BETWEEN ".$periodo['mesDesde']." AND ".$periodo['mesHasta']." )" );
        $programaticoSql = ( $programatico['epg_id'] != '' ? "epg_id = ".$programatico['epg_id']:'' );
        $cvefinSql = ( $claveFinanciamiento['ecf_id'] != '' ? "ecf_id = ".$claveFinanciamiento['ecf_id']:( $claveFinanciamiento['ter_id'] != '' ? "ter_id = '".$claveFinanciamiento['ter_id']."'":'' ) );
        
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
        if( $niveles[10] > 0 ){
            $ordenar .= ", cuenta";
            $agrupador .= ", cta_id";
            $key .= ", '.', cta_id";
        }
        
        $prefijoSql = '';
        $cvefinSql = ( $claveFinanciamiento['ter_id'] == 'C' ? "tr.ter_id IS NOT NULL":$cvefinSql );
        $sufijoSql = ( $programaticoSql != '' ? "AND $programaticoSql":"" );
        $sufijoSql .= ( $cvefinSql != '' ? "AND ".$cvefinSql." ":"" );
        
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
            estatal, federal, indefinido,
            clc_id, afe_id, bdo_id, cmp_id, poliza, 
            rfc_id, 
            rfc_cta_id AS cuenta, CASE WHEN cta_nombre IS NULL THEN nombre ELSE cta_nombre END AS nombre, 
            id 
        FROM ( 
            SELECT m.gpo_id, m.ur_id, ur.ur_clave, ur.ur_nombre, x.cta_ue_id AS ue_id, 
                x.cta_id AS rfc_cta_id, pv.rfc_id, 
                CASE soc_id WHEN 0 THEN CONCAT( pv.rfc_nombre, ' ', pv.rfc_paterno, ' ', pv.rfc_materno ) ELSE CONCAT( pv.rfc_nombre, ', ', soc_nombre ) END AS nombre, 
                cc.cta_nombre, 
                SUM( CASE tr.ter_id WHEN 'A' THEN x.pdx_debe ELSE 0 END ) - SUM( CASE tr.ter_id WHEN 'A' THEN x.pdx_haber ELSE 0 END ) AS estatal, 
                SUM( CASE tr.ter_id WHEN 'B' THEN x.pdx_debe ELSE 0 END ) - SUM( CASE tr.ter_id WHEN 'B' THEN x.pdx_haber ELSE 0 END ) AS federal, 
                SUM( CASE WHEN tr.ter_id IS NULL THEN x.pdx_debe ELSE 0 END ) - SUM( CASE WHEN tr.ter_id IS NULL THEN x.pdx_haber ELSE 0 END ) AS indefinido, 
                GROUP_CONCAT( DISTINCT CONCAT( x.pdx_gpo_id_origen, x.pdx_ur_id_origen ) ORDER BY CONCAT( x.pdx_gpo_id_origen, x.pdx_ur_id_origen ) ) AS pdx_ur_clave_origen, 
                GROUP_CONCAT( DISTINCT x.pdx_clc_id_origen ORDER BY x.pdx_clc_id_origen ) AS clc_id, 
                GROUP_CONCAT( DISTINCT x.pdx_afe_id_origen ORDER BY x.pdx_afe_id_origen ) AS afe_id, 
                GROUP_CONCAT( DISTINCT x.pdx_bdo_id_origen ORDER BY x.pdx_bdo_id_origen ) AS bdo_id, 
                GROUP_CONCAT( DISTINCT x.pdx_cmp_id_origen ORDER BY x.pdx_cmp_id_origen ) AS cmp_id, 
                GROUP_CONCAT( DISTINCT CONCAT( m.pot_id, '-', m.prf_id, m.pma_id ) ) AS poliza, 
                epg_id, oa.prg_id, oa.spr_id, oa.py_id, oa.oa_id, ep.epg_clave, oa.oa_nombre, 
                tg.tg_id, tg.tg_nombre, 
                i.idp_id, i.idp_nombre, 
                pg.pg_id, pg.pg_nombre, 
                pe.pe_id, pe.pe_nombre, 
                ef.ecf_id, ef.ecf_clave, cf.cfi_nombre, 
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
                LEFT JOIN sinpres.cat_ClaveFinanciamiento2023 cf USING( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                LEFT JOIN sinpres.cat_SuborigenRecurso2023 cs USING( cff_id, fte_id, sft_id, ori_id, sor_id )
                LEFT JOIN sinpres.cat_TipoRecurso tr USING( ter_id ) 
                LEFT JOIN enc_Programatico ep USING( epg_id ) 
                LEFT JOIN cat_ObraActividad oa ON ep.gpo_id = oa.gpo_id AND ep.ur_id = oa.ur_id AND ep.prg_id = oa.prg_id AND ep.spr_id = oa.spr_id AND ep.py_id = oa.py_id AND ep.oa_id = oa.oa_id 
                LEFT JOIN sinpres.cat_Proveedores pv ON rfc_cta_id = cta_id 
                LEFT JOIN sinpres.cat_SociedadProveedor USING( soc_id ) 
            ".( ( $reporte['tipo'] == 2 && $_SESSION[_S]['nvj_id'] < 6 ) ? "    INNER JOIN cat_CargaTrabajo t ON t.gpo_id = m.gpo_id AND t.ur_id = m.ur_id
            WHERE t.usr_id = ".$_SESSION[_S]['s24-id']." ":( $reporte['tipo'] == 1 ? "WHERE m.gpo_id = $gpo_id AND m.ur_id = $ur_id ":"" ) )."
                ".( $_SESSION[_S]['nvj_id'] > 5  && $reporte['tipo'] != 1 ? "WHERE 1 ":( $ue_id != '' ? "AND x.cta_ue_id = $ue_id ":"" ) )."
                $periodoSql
                AND x.ger_id = 1 AND x.cgp_id = 1 AND x.rub_id = 1 AND x.cgcu_id = 3 AND x.cgsc_id = 1
                AND m.epl_id NOT IN ( 'C', 'H' ) 
                $sufijoSql
            GROUP BY $agrupador 
        )c 
        ORDER BY $ordenar 
        ";
        //echo $mmc_id."<pre>";print_r($sql);echo "</pre>";die();
        
        $contable = new AccesoDatos();//variable de konexion para los saldos iniciales
        $contable->conectar();
        $contable->setSql( $sql );
        $contable->consultar();
        $contable->desconectar();
        
        $datos = array();
        
        while( $rowContable = $contable->extraer() ){
            
            /*echo '<br><pre>C-';
            print_r($rowContable);
            echo '</pre>';//*/
            //$datos[$rowContable['gpo_id'].$rowContable['ur_id'].'.'.$rowContable['tg_id'].'.'.$rowContable['idp_id'].'.'.$rowContable['pg_id'].'.'.$rowContable['pe_id'].'.'.$rowContable['ter_id'].'.'.$rowContable['cfi_clave']] = array(
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
                    'total'=>$rowContable['estatal'] + $rowContable['federal'] + $rowContable['indefinido'] 
                
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
        
        //echo '<br><pre>C-';print_r($datos);echo '</pre>';die();//*/
        return $datos;
        
    }
    
?>