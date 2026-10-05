<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
    
    require_once dirname(__FILE__).'/../../../compararCadenas.php';
    require_once dirname(__FILE__).'/../../../redondear.php';
    
    function vinculacionPagado2020( $gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array() ){
        //echo "<pre>";print_r($parametroExtra);echo "</pre>";
        
        $sqlFiltro = '';
        if( $parametroExtra['gur'] != '' ){
            $sqlFiltro = "t.gpo_id = ".substr( $parametroExtra['gur'], 0, 1 )." AND t.ur_id = ".substr( $parametroExtra['gur'], 1, 2 )." ";
        } else{
            $sqlFiltro = "t.gpo_id = '' AND t.ur_id =''  ";
        }
        if( $parametroExtra['clc_id'] != '' )
            $sqlFiltro .= ( ($sqlFiltro != '' ? "AND ":"")."t.clc_id = ".$parametroExtra['clc_id']." " );
        if( $parametroExtra['bdo_id'] != '' )
            $sqlFiltro .= ($sqlFiltro != '' ? "AND ":"")."t.bdo_id = ".$parametroExtra['bdo_id']." ";
        if( $parametroExtra['rfc_id'] != '' )
            $sqlFiltro .= ($sqlFiltro != '' ? "AND ":"")."b.rfc_id = '".$parametroExtra['rfc_id']."' ";
        if( $parametroExtra['bdo_referencia'] != '' )
            $sqlFiltro .= ($sqlFiltro != '' ? "AND ":"")."b.bdo_referencia = '".$parametroExtra['bdo_referencia']."' ";
        if( $periodo['mesDesde'] != '' ){
            $sqlFiltro .= ($sqlFiltro != '' ? "AND ":"")."t.msc_id = ".$periodo['mesDesde']." ";
        }
        $sqlFiltro .= ($sqlFiltro != '' ? "AND ":"")." bdm_estado='A'";

        $sqlFiltro .= ($sqlFiltro != '' ? "AND ":"")."c.tcl_id != 'C'";

         $sqlFiltro .= ($sqlFiltro != '' ? " AND ":" ")."ejr_id_clc = 2020  and ejr_id_autorizado=2022";
        $sql = "
            SELECT 
                t.gpo_id, t.ur_id, t.clc_id, t.afe_id,group_concat(distinct t.afe_id) as stringafec,
                t.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia, b.bdo_concepto, 
                v.tdf_id AS tdf_id_pagado, v.bdp_referencia, 
                b.bdo_percepcionPresupuestal, b.bdo_percepcionContable, b.bdo_retencionContable, 
                v.msc_id, 
                v.bdp_estado AS estado, DATE( v.bdp_fechaAutorizado ) AS fechaAutorizacion, DATE( v.bdp_fechaCancelado ) AS fechaCancelacion, 
                v.bdp_agrupador AS agrupador, 
                
                pa.polizaAutorizado, MAX(pa.fechaAutorizado) as fechaAutorizado,
                MAX(pa.gpo_id_polizaAutorizado) as gpo_id_polizaAutorizado, MAX(pa.ur_id_polizaAutorizado) as ur_id_polizaAutorizado, MAX(pa.ue_id_polizaAutorizado) as ue_id_polizaAutorizado,
                MAX(pa.msc_id_polizaAutorizado) as msc_id_polizaAutorizado, MAX(pa.pot_id_polizaAutorizado) as pot_id_polizaAutorizado, MAX(pa.prf_id_polizaAutorizado) as prf_id_polizaAutorizado,
                MAX(pa.pma_id_polizaAutorizado) as pma_id_polizaAutorizado,
                
                pc.polizaCancelado, MAX(pc.fechaCancelado) as fechaCancelado,
                MAX(pc.gpo_id_polizaCancelado) as gpo_id_polizaCancelado,  MAX(pc.ur_id_polizaCancelado) as ur_id_polizaCancelado, MAX(pc.ue_id_polizaCancelado) as ue_id_polizaCancelado,
                MAX(pc.msc_id_polizaCancelado) as msc_id_polizaCancelado, MAX(pc.pot_id_polizaCancelado) as pot_id_polizaCancelado, MAX(pc.prf_id_polizaCancelado) as prf_id_polizaCancelado,
                MAX(pc.pma_id_polizaCancelado) as pma_id_polizaCancelado,
                
                CONCAT( t.gpo_id, t.ur_id, '.', LPAD( t.msc_id, 2, 000 ), '.', LPAD( t.afe_id, 6, 0000000 ), '.', LPAD( t.bdo_id, 6, 000000 ), '.', COALESCE( max(pa.pma_id_polizaAutorizado), 000000 ) ) AS id
            FROM ctrl2020.cat_BancoDocumentos b
                INNER JOIN ctrlAnterior.min_BancoDocumentoMinistrado t USING( gpo_id, ur_id, bdo_id )
                INNER JOIN ctrl2023.cg_MesContable ms USING( gpo_id, ur_id, msc_id )
                INNER JOIN ctrlAnterior.cat_TipoMinistracion USING( tmi_id )
                INNER JOIN ctrl2020.scp_Clc c USING( gpo_id, ur_id, clc_id )
                LEFT JOIN  ctrlAnterior.cg_BancoDocumentoPagado v USING(  ejr_id_clc,gpo_id,ur_id, clc_id,afe_id,bdo_id,ejr_id_autorizado )
                LEFT JOIN( 
                    SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado, m.ue_id AS ue_id_polizaAutorizado, 
                        m.msc_id AS msc_id_polizaAutorizado, m.pot_id AS pot_id_polizaAutorizado, m.prf_id AS prf_id_polizaAutorizado, 
                        m.pma_id AS pma_id_polizaAutorizado, m.pma_fecha AS fechaAutorizado, 
                        CONCAT( m.pot_id, '-', m.prf_id, m.pma_id ) AS polizaAutorizado, 
                        MAX(x.pdx_gpo_id_vinculacion) AS gpo_id, MAX(x.pdx_ur_id_vinculacion) AS ur_id,
                        MAX(x.pdx_afe_id_vinculacion) AS afe_id, MAX(x.pdx_bdo_id_vinculacion) AS bdo_id,
                        MAX(x.pdx_agrupador) AS agrupador
                    FROM ctrl2023.cg_PolizaMaestro m
                        INNER JOIN ctrl2023.cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = $gpo_id AND m.ur_id = $ur_id 
                        AND m.mmc_id = '$mmc_id' 
                        AND m.tmc_id = 'A' 
                        AND x.pdx_ejercicio = 2020
                    GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_origen, x.pdx_bdo_id_origen,ue_id_polizaAutorizado,msc_id_polizaAutorizado,
                    pot_id_polizaAutorizado,prf_id_polizaAutorizado,pma_id_polizaAutorizado
                )pa USING( gpo_id, ur_id, afe_id, bdo_id ) 
                LEFT JOIN ( 
                    SELECT m.gpo_id AS gpo_id_polizaCancelado, m.ur_id AS ur_id_polizaCancelado, m.ue_id AS ue_id_polizaCancelado, 
                        m.msc_id AS msc_id_polizaCancelado, m.pot_id AS pot_id_polizaCancelado, m.prf_id AS prf_id_polizaCancelado, 
                        m.pma_id AS pma_id_polizaCancelado, m.pma_fecha AS fechaCancelado, 
                        CONCAT( m.pot_id, '-', m.prf_id, m.pma_id ) AS polizaCancelado, 
                        MAX(x.pdx_gpo_id_vinculacion) AS gpo_id, MAX(x.pdx_ur_id_vinculacion) AS ur_id,
                        MAX(x.pdx_afe_id_vinculacion) AS afe_id, MAX(x.pdx_bdo_id_vinculacion) AS bdo_id,
                        MAX(x.pdx_agrupador) AS agrupador
                    FROM ctrl2023.cg_PolizaMaestro m
                        INNER JOIN ctrl2023.cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = $gpo_id AND m.ur_id = $ur_id 
                        AND m.mmc_id = '$mmc_id' 
                        AND m.tmc_id = 'C' 
                        AND x.pdx_ejercicio = 2020
                    GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_origen, x.pdx_bdo_id_origen,ue_id_polizaCancelado,msc_id_polizaCancelado,
                    pot_id_polizaCancelado,prf_id_polizaCancelado,pma_id_polizaCancelado
                )pc USING( gpo_id, ur_id, afe_id, bdo_id ) 
            WHERE $sqlFiltro 
            GROUP BY ejr_id_clc,gpo_id,ur_id,msc_id, clc_id,afe_id,bdo_id,polizaAutorizado,polizaCancelado
        ";
        //echo '<pre>';echo $sql;echo '</pre>';die();
        
        $conexion = new AccesoDatos();//variable de konexion para los saldos iniciales
        $conexion->conectar();
        $conexion->setSql( $sql );
        $conexion->consultar();
        $conexion->desconectar();
        
        $datos = array();
        
        while( $rowData = $conexion->extraer() ){
            $datos[$rowData['id']] = array( 
                'id'=>$rowData['id'],
                'gpo_id'=>$rowData['gpo_id'],
                'ur_id'=>$rowData['ur_id'], 
                'gur'=>$rowData['gpo_id'].$rowData['ur_id'], 
                'clc_id'=>$rowData['clc_id'], 
                'afe_id'=>$rowData['afe_id'], 
                'bdo_id'=>$rowData['bdo_id'], 
                'tdf_id'=>$rowData['tdf_id'], 
                'rfc_id'=>$rowData['rfc_id'], 
                'bdo_referencia'=>$rowData['bdo_referencia'], 
                'bdo_concepto'=>$rowData['bdo_concepto'], 
                'bdo_importe'=>$rowData['bdo_percepcionPresupuestal']+$rowData['bdo_percepcionContable']-$rowData['bdo_retencionContable'], 
                'estado'=>$rowData['estado'], 
                'tdf_id_pagado'=>$rowData['tdf_id_pagado'], 
                'bdp_referencia'=>$rowData['bdp_referencia'], 
                'fechaAutorizacion'=>$rowData['fechaAutorizacion'], 
                'fechaCancelacion'=>$rowData['fechaCancelacion'], 
                'polizaAutorizado'=>$rowData['polizaAutorizado'], 
                'fechaAutorizado'=>$rowData['fechaAutorizado'], 
                'polizaCancelado'=>$rowData['polizaCancelado'], 
                'fechaCancelado'=>$rowData['fechaCancelado'], 
                'agrupador'=>$rowData['agrupador'],
                'stringafec' => $rowData['stringafec']
            );
            
        }
        
        //echo '<br><pre>C-';print_r($datos);echo '</pre>';die();//*/
        return $datos;
        
    }
    
?>