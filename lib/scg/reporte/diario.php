<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
    
    function diario( $gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $claveFinanciamiento, $order, $detalle ){
        $ordenar = '';
        $params = [];
        switch( $order ){
            case 'F':
                $ordenar = "ORDER BY ue_id, pma_fecha, ";
                break;
            case 'P':
                $ordenar = "ORDER BY ue_id, pma_id, ";
                break;
            case 'T':
                $ordenar = "ORDER BY ue_id, pot_id, pma_id, ";
                break;
            default:
                $ordenar = "ORDER BY ";
                break;
        }
        
        $ordenar .= "gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id";
        $var_Max="";
        
        $periodoSql = ( $periodo['mesDesde'] == $periodo['mesHasta'] ? "AND msc_id = ".$periodo['mesDesde']:"AND ( msc_id BETWEEN ".$periodo['mesDesde']." AND ".$periodo['mesHasta']." )" );
        $cvefinSql = ( isset($claveFinanciamiento['ecf_id']) != '' ? "AND ecf_id = ".$claveFinanciamiento['ecf_id']:( isset($claveFinanciamiento['ter_id']) != '' ? "AND ter_id = '".$claveFinanciamiento['ter_id']."'":'' ) );
        
        $tipoSql = ( $pot_id['diario'] != "" ? "'".$pot_id['diario']."'":'' );
        $tipoSql = ( $pot_id['ingresos'] != "" ? ( $tipoSql != "" ? $tipoSql.", ":"" )."'".$pot_id['ingresos']."'":$tipoSql );
        $tipoSql = ( $pot_id['egresos'] != "" ? ( $tipoSql != "" ? $tipoSql.", ":"" )."'".$pot_id['egresos']."'":$tipoSql );
        $tipoSql = "AND pot_id IN ( ".( $tipoSql != "" ? $tipoSql:"''")." ) ";
        
        $detalleSql = ( !$detalle ? "GROUP BY gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id":"" );
        $ordenar .= ( $detalle ? ", nat_id, cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id ":" " );
        $var_Max .= ( !$detalle ? " MAX":" " );
        // Armamos la consulta
        $sql = "
            SELECT m.gpo_id, m.ur_id, m.ue_id, 
                m.msc_id, m.pot_id, m.prf_id, m.pma_id, $var_Max(x.pdx_id) AS pdx_id,
                m.pma_fecha, m.pma_concepto, 
                m.pma_debe, m.pma_haber, 
                m.mmc_id, mmc_nombre, m.tmc_id, m.epl_id, epl_nombre, 
                $var_Max(x.cta_ue_id) AS cta_ue_id, $var_Max(cta_cuenta) AS cta_cuenta, $var_Max(cta_nombre) AS cta_nombre,
                $var_Max(x.pdx_debe) AS pdx_debe, $var_Max(x.pdx_haber) AS pdx_haber, $var_Max(x.nat_id) AS nat_id,
                $var_Max(x.tdf_id) AS tdf_id, $var_Max(x.rfc_id) AS rfc_id,$var_Max(x.pdx_referencia) AS  pdx_referencia, $var_Max(x.pdx_fechaReferencia) AS pdx_fechaReferencia, "
            . "$var_Max(x.pdx_concepto) AS pdx_concepto,  $var_Max(x.epg_id) AS epg_id, $var_Max(oa.prg_id) AS prg_id, $var_Max(oa.spr_id) AS spr_id, $var_Max(oa.py_id) AS py_id, "
            . "$var_Max(oa.oa_id) AS oa_id, $var_Max(ep.epg_clave) AS epg_clave, $var_Max(oa.oa_nombre) AS oa_nombre,
                $var_Max(tg.tg_id) AS tg_id, $var_Max(tg.tg_nombre) AS tg_nombre,
                $var_Max(i.idp_id) AS idp_id, $var_Max(i.idp_nombre) AS idp_nombre,
                $var_Max(pg.pg_id) AS pg_id, $var_Max(pg.pg_nombre) AS pg_nombre,
                $var_Max(pe.pe_id) AS pe_id, $var_Max(pe.pe_nombre) AS pe_nombre,
                $var_Max(tr.ter_id) AS ter_id, $var_Max(tr.ter_nombre) AS ter_nombre,
                $var_Max(ef.ecf_id)AS ecf_id, $var_Max(ef.ecf_clave) AS ecf_clave, $var_Max(cf.cfi_nombre) AS cfi_nombre,
                $var_Max(x.pdx_clc_id_origen) AS pdx_clc_id_origen, $var_Max(x.pdx_afe_id_origen) AS pdx_afe_id_origen,
                $var_Max(x.pdx_bdo_id_origen) AS pdx_bdo_id_origen, $var_Max(x.pdx_cmp_id_origen) AS pdx_cmp_id_origen, $var_Max(pdx_ejercicio) AS pdx_ejercicio,
                CONCAT( m.gpo_id, m.ur_id, '.', m.ue_id, '.', m.msc_id, '.', m.pot_id, '.', m.prf_id, '.', m.pma_id".( $detalle ? ", '.', x.pdx_id":'' )." ) AS id 
            FROM cg_PolizaMaestro m 
                LEFT JOIN cat_EstadoPoliza USING( epl_id ) 
                LEFT JOIN cat_MomentoContable USING( mmc_id ) 
                INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
                INNER JOIN cg_PlanCuentas USING( cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id ) 
                LEFT JOIN cat_UnidadResponsable ur USING( gpo_id, ur_id ) 
                LEFT JOIN cat_PartidaGenerica pg USING( pg_id ) 
                LEFT JOIN cat_PartidaEspecifica pe USING( pg_id, pe_id ) 
                LEFT JOIN cat_IdentificadorPartida i USING( idp_id ) 
                LEFT JOIN cat_TipoGasto tg ON pe.tg_id = tg.tg_id 
                LEFT JOIN enc_ClaveFinanciamiento ef USING( ecf_id ) 
                LEFT JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING( cff_id,fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                LEFT JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " cs USING( cff_id,fte_id, sft_id, ori_id, sor_id)
                LEFT JOIN " . BD_SINPRES . ".cat_TipoRecurso tr USING( ter_id ) 
                LEFT JOIN enc_Programatico ep USING( epg_id ) 
                LEFT JOIN cat_ObraActividad oa ON ep.gpo_id = oa.gpo_id AND ep.ur_id = oa.ur_id AND ep.prg_id = oa.prg_id AND ep.spr_id = oa.spr_id AND ep.py_id = oa.py_id AND ep.oa_id = oa.oa_id 
            WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                ".( ($ue_id != '') ? "AND m.ue_id = $ue_id ":"" )." 
                $periodoSql
                $tipoSql
                ".( isset($claveFinanciamiento['ter_id']) == 'C' ? 'AND ter_id IS NOT NULL ':$cvefinSql )."
            $detalleSql
            $ordenar
        ";
        $params = [':gpo'=>$gpo_id,':ur' => $ur_id];
        $reporteDiario = new AccesoDatos();//variable de konexion para los saldos iniciales
        $reporteDiario->conectar();
        $reporteDiario->setSql( $sql );
        $reporteDiario->consultar($params);
        $reporteDiario->desconectar();
        
        $datos = array();
        
        while( $rowDiario = $reporteDiario->extraer() ){
            
            $datos[$rowDiario['id']] = array( 
                    'id'=>$rowDiario['id'],
                    'ue_id'=>$rowDiario['ue_id'],
                    'msc_id'=>$rowDiario['msc_id'], 
                    'pot_id'=>$rowDiario['pot_id'], 
                    'prf_id'=>$rowDiario['prf_id'], 
                    'pma_id'=>$rowDiario['pma_id'],
                    'poliza'=>$rowDiario['pot_id'].'-'.$rowDiario['prf_id'].$rowDiario['pma_id'],
                    'dia'=>date_format( date_create( $rowDiario['pma_fecha'] ), 'd' ), 
                    'pma_fecha'=>date_format( date_create( $rowDiario['pma_fecha'] ), 'd-m-Y' ), 
                    'pma_concepto'=>strtoupper( $rowDiario['pma_concepto'] ),
                    'pma_debe'=>$rowDiario['pma_debe'],
                    'pma_haber'=>$rowDiario['pma_haber'],
                    'mmc_id'=>$rowDiario['mmc_id'].$rowDiario['tmc_id'],
                    'mmc_nombre'=>$rowDiario['mmc_nombre'],
                    'clc_id'=>$rowDiario['pdx_clc_id_origen'],
                    'afe_id'=>$rowDiario['pdx_afe_id_origen'],
                    'bdo_id'=>$rowDiario['pdx_bdo_id_origen'],
                    'cmp_id'=>$rowDiario['pdx_cmp_id_origen'],
                    'pdx_ejercicio'=>$rowDiario['pdx_ejercicio'],
                    'epg_clave'=>$rowDiario['epg_clave'],
                    'oa_nombre'=>$rowDiario['oa_nombre'],
                    'pg_id'=>$rowDiario['pg_id'],
                    'pg_nombre'=>$rowDiario['pg_nombre'],
                    'pe_id'=>$rowDiario['pe_id'],
                    'pe_nombre'=>$rowDiario['pe_nombre'],
                    'ecf_clave'=>$rowDiario['ecf_clave'],
                    'cfi_nombre'=>$rowDiario['cfi_nombre'],
                    'epl_id'=>$rowDiario['epl_id'],
                    'epl_nombre'=>$rowDiario['epl_nombre'],
                    'cta_ue_id'=>$rowDiario['cta_ue_id'], 
                    'cta_cuenta'=>$rowDiario['cta_cuenta'], 
                    'cta_nombre'=>$rowDiario['cta_nombre'], 
                    'pdx_debe'=>$rowDiario['pdx_debe'], 
                    'pdx_haber'=>$rowDiario['pdx_haber'], 
                    'tdf_id'=>$rowDiario['tdf_id'], 
                    'rfc_id'=>$rowDiario['rfc_id'], 
                    'pdx_referencia'=>$rowDiario['pdx_referencia'], 
                    'pdx_fechaReferencia'=>$rowDiario['pdx_fechaReferencia'], 
                    'pdx_concepto'=>$rowDiario['pdx_concepto'] 
                    )
            ;
            
        }
        
        if( !isset( $datos ) ){
            $datos = array( 
                'id'=>'',
                'ue_id'=>'',
                'msc_id'=>'', 
                'pot_id'=>'', 
                'prf_id'=>'', 
                'pma_id'=>'',
                'poliza'=>'',
                'dia'=>'',
                'pma_fecha'=>'',
                'pma_concepto'=>'',
                'pma_debe'=>'',
                'pma_haber'=>'',
                'mmc_id'=>'',
                'mmc_nombre'=>'',
                'clc_id'=>'',
                'afe_id'=>'',
                'bdo_id'=>'',
                'cmp_id'=>'',
                'pdx_ejercicio'=>'',
                'epg_clave'=>'',
                'oa_nombre'=>'',
                'pg_id'=>'',
                'pg_nombre'=>'',
                'pe_id'=>'',
                'pe_nombre'=>'',
                'ecf_clave'=>'',
                'cfi_nombre'=>'',
                'epl_id'=>'',
                'epl_nombre'=>'',
                'cta_ue_id'=>'', 
                'cta_cuenta'=>'', 
                'cta_nombre'=>'', 
                'pdx_debe'=>'', 
                'pdx_haber'=>'', 
                'tdf_id'=>'', 
                'rfc_id'=>'', 
                'pdx_referencia'=>'', 
                'pdx_fechaReferencia'=>'', 
                'pdx_concepto'=>'' ) 
            ;
            
        }
        
        return $datos;
        
    }
    
?>