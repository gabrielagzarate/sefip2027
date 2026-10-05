<?php
    
    set_time_limit(0);
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
    
    function fondos42210( $gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array() ){
        $periodoSql = ( $periodo['mesDesde'] == $periodo['mesHasta'] ? "AND msc_id = ".$periodo['mesDesde']:"AND ( msc_id BETWEEN ".$periodo['mesDesde']." AND ".$periodo['mesHasta']." )" );
        $programaticoSql = ( $programatico['epg_id'] != '' ? "epg_id = ".$programatico['epg_id']:'' );
        $cvefinSql = ( $claveFinanciamiento['ecf_id'] != '' ? "ecf_id = ".$claveFinanciamiento['ecf_id']:( $claveFinanciamiento['ter_id'] != '' ? "ter_id = '".$claveFinanciamiento['ter_id']."'":'' ) );
        $tipoRP = "";
        $agrupador = "x.pdx_gpo_id_origen, x.pdx_ur_id_origen";
        $key = "m.gpo_id, m.ur_id";
        if( $niveles[2] > 0 ){
            $agrupador .= ", x.cta_ue_id";
            $key .= ", '.', x.cta_ue_id";
        }
        if( $niveles[3] > 0 ){
            $agrupador .= ", ep.epg_clave";
            $key .= ", '.', IFNULL( ep.epg_clave, '00000000000' )";
        }
        if( $niveles[4] > 0 ){
            $agrupador .= ", tg.tg_id";
            $key .= ", '.', IFNULL( tg.tg_id, '0' )";
        }
        if( $niveles[5] > 0 ){
            $agrupador .= ", i.idp_id";
            $key .= ", '.', IFNULL( i.idp_id, '0' )";
        }
        if( $niveles[6] > 0 ){
            $agrupador .= ", pg.pg_id";
            $key .= ", '.', IFNULL( pg.pg_id, '000' )";
        }
        if( $niveles[7] > 0 ){
            $agrupador .= ", pe.pe_id";
            $key .= ", '.', IFNULL( pe.pe_id, '000' )";
        }
        if( $niveles[8] > 0 ){
            $agrupador .= ", tr.ter_id";
            $key .= ", '.', IFNULL( tr.ter_id, '0' )";
        }
        if( $niveles[9] > 0 ){
            $agrupador .= ", ef.ecf_clave";
            $key .= ", '.', IFNULL( ef.ecf_clave, '000000000' )";
        }
        if( $niveles[10] > 0 ){
            $agrupador .= ", cta_id";
            $key .= ", '.', cta_id";
        }
        if($reporte['tipo'] == 2 && $_SESSION[_S]['nvj_id'] < 6){
            $tipoRP.= " INNER JOIN cat_CargaTrabajo t ON t.gpo_id = x.pdx_gpo_id_origen AND t.ur_id = x.pdx_ur_id_origen
            WHERE t.usr_id = :usr AND ";
            $params = [':usr' => $_SESSION[_S]['usr_id']];
        }else{
            $tipoRP.= " WHERE ";
        }
        $prefijoSql = '';
        $cvefinSql = ( $claveFinanciamiento['ter_id'] == 'C' ? "tr.ter_id IS NOT NULL":$cvefinSql );
        $sufijoSql = ( $programaticoSql != '' ? "AND $programaticoSql":"" );
        $sufijoSql .= ( $cvefinSql != '' ? "AND ".$cvefinSql." ":"" );
        
        // Armamos la consulta
        $sql = $prefijoSql."
        SELECT gpo_id, ur_id, ur_clave, ur_nombre, MAX(ue_id) AS ue_id,
            MAX(epg_id) AS epg_id, MAX(prg_id) AS prg_id, MAX(spr_id) AS spr_id, MAX(py_id) AS py_id, MAX(oa_id) AS oa_id, MAX(epg_clave) AS epg_clave, MAX(oa_nombre) AS oa_nombre,
            MAX(tg_id) AS tg_id, MAX(tg_nombre) AS tg_nombre,
            MAX(idp_id) AS V, MAX(idp_nombre) AS idp_nombre,
            MAX(pg_id)AS pg_id, MAX(pg_nombre) AS pg_nombre,
            MAX(pe_id) AS pe_id, MAX(pe_nombre) AS pe_nombre,
            MAX(ecf_id) AS ecf_id, MAX(ecf_clave) AS ecf_clave, MAX(cfi_nombre) AS cfi_nombre,
            SUM( estatal ) AS estatal, SUM( federal ) AS federal,
            MAX(clc_id) AS clc_id, MAX(afe_id) AS afe_id, MAX(bdo_id) AS bdo_id, MAX(cmp_id) AS cmp_id,  MAX(poliza) AS poliza,
            MAX(rfc_id) AS rfc_id, 
            id 
        FROM ( 
            SELECT x.pdx_gpo_id_origen AS gpo_id, x.pdx_ur_id_origen AS ur_id, ur.ur_clave, ur.ur_nombre, MAX(x.cta_ue_id) AS ue_id,
                MAX(x.cta_id) AS rfc_cta_id, MAX(pv.rfc_id) AS rfc_id, 
                CASE MAX(soc_id) WHEN 0 THEN CONCAT( MAX(pv.rfc_nombre), ' ', MAX(pv.rfc_paterno), ' ', MAX(pv.rfc_materno) ) ELSE CONCAT( MAX(pv.rfc_nombre), ', ', MAX(soc_nombre) ) END AS nombre,
                MAX(cc.cta_nombre) AS cta_nombre,
                SUM( CASE x.cgc_id WHEN '001' THEN x.pdx_debe ELSE 0 END ) AS estatal, 
                SUM( CASE x.cgc_id WHEN '002' THEN x.pdx_debe ELSE 0 END ) AS federal, 
                GROUP_CONCAT( DISTINCT CONCAT( x.pdx_gpo_id_vinculacion, x.pdx_ur_id_vinculacion ) ORDER BY CONCAT( x.pdx_gpo_id_vinculacion, x.pdx_ur_id_vinculacion ) ) AS pdx_ur_clave_vinculacion, 
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
                MAX(ef.ecf_id) AS ecf_id, ef.ecf_clave, MAX(cf.cfi_nombre) AS cfi_nombre,
                CONCAT( x.pdx_gpo_id_origen, x.pdx_ur_id_origen ) AS id 
            FROM cg_PolizaMaestro m 
                INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
                LEFT JOIN cat_UnidadResponsable ur ON x.pdx_gpo_id_origen = ur.gpo_id AND x.pdx_ur_id_origen = ur.ur_id 
                LEFT JOIN cg_PlanCuentas cc USING( cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id ) 
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
                $tipoRP m.gpo_id = :gpo AND m.ur_id = :ur 
                AND m.ue_id = 001 
                $periodoSql
                AND x.ger_id = :ger AND x.cgp_id = :cgp AND x.rub_id = :rub AND x.cgcu_id = :cgcu AND x.cgsc_id = :cgsc 
                AND m.epl_id NOT IN ( 'C', 'H' ) 
                $sufijoSql
            GROUP BY $agrupador 
        )c 
        GROUP BY gpo_id, ur_id 
        ";
        
        $contable = new AccesoDatos();//variable de konexion para los saldos iniciales
        $contable->conectar();
        $contable->setSql( $sql);
        $contable->consultar(array(':gpo' => 1, ':ur' => 16,':ger' => 4, ':cgp' => 2,':rub' => 2,':cgcu'=>1,':cgsc'=>0));
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
                    'id'=>$rowContable['id'], 
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
                    'pdx_ur_clave_vinculacion'=>'', 
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