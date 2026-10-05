<?php
    
    /* 
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
    */
    
    function importeSql2015( $gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array() ){
        //echo $ue_id;die();
        //el periodo inicial, son los saldos acumulados antes del periodo solicitado
        //del periodo solicitado, si los meses son iguales, solo pidieron 1 mes; en caso contrario, solicitaron mas de 1 mes
        if( $parametroExtra['I'] ){
            $periodoSql = "msc_id < ".$periodo['mesDesde'];
            $importeIgualSql = "WHERE pdx_debe != pdx_haber";
        }else{
            $periodoSql = ( $periodo['mesDesde'] == $periodo['mesHasta'] ) ? "msc_id = ".$periodo['mesDesde']:"( msc_id BETWEEN ".$periodo['mesDesde']." AND ".$periodo['mesHasta']." )";
            $importeIgualSql = "WHERE pdx_debe != 0 OR pdx_haber != 0";
        }
        $sqlsubcuentas="";
        switch( $parametroExtra['busqueda']['tipo'] ){
            case 1://cuando elijen la busqueda normal
                // Marcos: 17/05/2016
                // Para que funcione la balanza de una cuenta a todos los niveles en pantalla
                // cambiaremos el = por LIKE
                $sqlsubcuentas=" INNER JOIN (
                                            select cgsc_cuenta,ger_id, cgp_id, rub_id, cgcu_id, cgsc_id
                                                    from ctrl2015.cat_CgSubcuenta ) cuenta
                                                            USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id ) " ;
                if( $detalle == 0 ){
                    $busquedaSql = ( $parametroExtra['busqueda']['cuentaDesde'] != $parametroExtra['busqueda']['cuentaHasta'] ) ? ( 
                                ( $parametroExtra['busqueda']['cuentaDesde'] != 10000 && $parametroExtra['busqueda']['cuentaHasta'] != 99999 ) ? ( "AND ( cgsc_cuenta BETWEEN '".$parametroExtra['busqueda']['cuentaDesde']."' AND '".$parametroExtra['busqueda']['cuentaHasta']."' )" ):(
                                    ( ( $parametroExtra['busqueda']['cuentaDesde'] == 10000 ) ? "":"AND cgsc_cuenta >= '".$parametroExtra['busqueda']['cuentaDesde']."' " ).(
                                        ( $parametroExtra['busqueda']['cuentaHasta'] == 99999 ) ? "":"AND cgsc_cuenta <= '".$parametroExtra['busqueda']['cuentaHasta']."' "
                                    ) 
                                ) 
                            ):( "AND cgsc_cuenta = '".$parametroExtra['busqueda']['cuentaDesde']."' " );
                }else{
                     $sqlsubcuentas="";
                    // y además la cuenta inicial SIEMPRE será igual a la final, así 
                    // que omitimos algunas validaciones.   Sólo mostramos el detalle de ger+cgp+rub
                    $busquedaSql = "AND CONCAT( ger_id, cgp_id, rub_id ) = '".$parametroExtra['busqueda']['cuentaDesde'] . "'";
                }
                break;
            case 2://busqueda por CLC 
                if( $parametroExtra['busqueda']['clc_id'] != '' )
                    $busquedaSql = "AND pdx_clc_id_origen = ".$parametroExtra['busqueda']['clc_id'];
                break;
            case 3://busqueda por Bdo
                if( $parametroExtra['busqueda']['bdo_id'] != '' )
                    $busquedaSql = "AND pdx_bdo_id_origen = ".$parametroExtra['busqueda']['bdo_id'];
                break;
            default:
                echo 'kym';
                break;
        }
        
        $cvefinSql = ( $claveFinanciamiento['ecf_id'] != '' ) ? "AND ecf_id = ".$claveFinanciamiento['ecf_id']:( $claveFinanciamiento['ter_id'] != '' ? "AND ter_id = '".$claveFinanciamiento['ter_id']."'":'' );
        
        /* Sólo los jefes de unidad (plinio y huitron) y la directora (evangelina) y Juan Martín tendran OPCIONALMENTE un WHERE personalizado para imprimir un rango de URs  */
        if( $reporte['tipo'] == 1 ){
            $urSql = "v.gpo_id_2017 = $gpo_id AND v.ur_id_2017 = $ur_id 
                    AND ";
            /*$urSql .= ( $ue_id ? " v.ue_id_2017 = $ue_id 
                    AND " : "" );//*/
        }elseif( $reporte['tipo'] == 3 && $_SESSION[_S]['nvj_id'] >= 5 ){
            // Si limitaron por grupo o excluyeron alguna UR
            if ( $reporte['grupos'] != '' ){
                $urSql = "v.gpo_id_2017 IN ( ".rtrim( $reporte['grupos'] )." ) 
                    AND ";
                
                // Si excluyeron a alguna UR
                if( $reporte['excluir'] != '' )
                    $urSql .= "CONCAT( v.gpo_id_2017, v.ur_id_2017 ) NOT IN ( " . rtrim( $reporte['excluir'] ) . " ) 
                    AND ";
                
            // Si no usaron las campos especiales, es un where estandar
            }else{
                $urSql = "v.gpo_id_2017 = $gpo_id AND v.ur_id_2017 = $ur_id 
                    AND "; 
                //$urSql .= ( $ue_id ? ' v.ue_id_2017 = '.$ue_id.' AND ':'' );
            }
        }else{
            $urSql = '';
        }
        
        $ordenar = '';
        $key = '';
        
        if( $reporte['agrupador'] == 1 ){
            $ordenar = "cta_gpo_id, cta_ur_id";
            $key = "cta_gpo_id, cta_ur_id";
        }
        //if( $niveles[0] > 1 ){
            $ordenar .= ( $ordenar != '' ? ", ":'' )."ger_id";
            $key .= ( $key != '' ? ", '.',":'' )."ger_id";
            $cuenta = "ger_cuenta";
            $nombre = "ger_nombre";
        //}
        if( $niveles[0] > 1 ){
            $ordenar .= ", cgp_id";
            $key .= ", '.', cgp_id";
            $cuenta = "cgp_cuenta";
            $nombre = "cgp_nombre";
        }
        if( $niveles[0] > 2 ){
            $ordenar .= ", rub_id";
            $key .= ", '.', rub_id";
            $cuenta = "rub_cuenta";
            $nombre = "rub_nombre";
        }
        if( $niveles[0] > 3 ){
            $ordenar .= ", cgcu_id";
            $key .= ", '.', cgcu_id";
            $cuenta = "cgcu_cuenta";
            $nombre = "cgcu_nombre";
        }
        if( $niveles[0] > 4 ){
            $ordenar .= ", cgsc_id";
            $key .= ", '.', cgsc_id";
            $cuenta = "cgsc_cuenta";
            $nombre = "cgsc_nombre";
        }
        if( $niveles[0] > 5 ){
            $ordenar .= ", cgc_id";
            $key .= ", '.', cgc_id";
            $cuenta = "CONCAT( ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', cgsc_id, '.', cgc_id )";
            $nombre = "cgc_nombre";
        }
        if( $niveles[0] == 7 ){
            $ordenar .= ", cgq_id";
            $key .= ", '.', cgq_id";
            $cuenta = "cgq_cuenta";
            $nombre = "cgq_nombre";
        }
        if( $niveles[0] > 7 ){
            $ordenar .= ", cta_id";
            $key .= ", '.', cta_id";
            $cuenta = "CONCAT( ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', cgsc_id, '.', cgc_id, '.', cta_id )";
            $nombre = "cta_nombre";
        }
        if( $reporte['tipo'] == 1 && $niveles[0] > 7 ){
            $key .= ( $key != '' ? ", '.',":'' )." cta_ue_id";
            $ordenar .= ", cta_ue_id";
            //$cuenta = "cta_ue_id";
            //$nombre = "ue_nombre";
        }
        
        $sql = "
            SELECT id, 
                pdx.gpo_id AS cta_gpo_id, pdx.ur_id AS cta_ur_id, pdx.ue_id AS cta_ue_id, 
                ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, 
                cgc_id, cta_id, 
                pdx_debe, pdx_haber, cgm_naturaleza, 
                $cuenta AS cuenta, $nombre AS nombre, 
                CONCAT( ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', cgsc_id, '.', cgc_id, '.', cta_id ) AS cta_cuenta, cta_nombre, 
                NULL AS cgq_cuenta, NULL AS cgq_nombre, 
                CONCAT( ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', cgsc_id, '.', cgc_id ) AS cgc_cuenta, cgc_nombre, 
                CONCAT( ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', cgsc_id ) AS cgm_cuenta, cgm_nombre, 
                cgcu_cuenta, cgcu_nombre, 
                cgsc_cuenta, cgsc_nombre, 
                rub_cuenta, rub_nombre, 
                cgp_cuenta, cgp_nombre, 
                ger_cuenta, ger_nombre, 
                ur_clave, ur_nombre, 
                nivel 
            FROM ( 
                SELECT 
                    v.gpo_id_2017 AS gpo_id, v.ur_id_2017 AS ur_id, v.ue_id_2017 AS ue_id, 
                    cta_gpo_id, cta_ur_id, cta_ue_id, 
                    x.ger_id, x.cgp_id, x.rub_id, x.cgcu_id, x.cgsc_id, 
                    x.cgc_id, x.cta_id, 
                    SUM( x.dpo_debe ) AS pdx_debe, SUM( x.dpo_haber ) AS pdx_haber, 
                    ".$niveles[0]." AS nivel, 
                    CONCAT( $key ) AS id 
                FROM ctrl2015.cg_PolizaMaestro m 
                    INNER JOIN ctrl2015.cg_PolizaDetalle x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pol_numero ) 
                    INNER JOIN sinpres.vin_UnidadEjecutora_2015_2016 s ON m.gpo_id = s.gpo_id_2015 AND m.ur_id = s.ur_id_2015 AND m.ue_id = s.ue_id_2015 
                    INNER JOIN sinpres.vin_UnidadEjecutora_2016_2017 v ON s.gpo_id_2016 = v.gpo_id_2016 AND s.ur_id_2016 = v.ur_id_2016 AND s.ue_id_2016 = v.ue_id_2016 
                ".( ( $reporte['tipo'] == 2 && $_SESSION[_S]['nvj_id'] < 6 ) ? "    INNER JOIN ctrl2017.cat_CargaTrabajo t ON t.gpo_id = v.gpo_id_2017 AND t.ur_id = v.ur_id_2017
                WHERE t.usr_id = ".$_SESSION[_S]['s24-id']."
                    AND " : 
                "WHERE $urSql " ).( ($ue_id ) ? "v.ue_id_2017 = $ue_id 
                    AND ":'' )."
                    $periodoSql
                    AND m.epl_id != 'C' AND m.epl_id != 'H' 
                    $busquedaSql
                    $cvefinSql
                GROUP BY $key 
            UNION ALL 
                SELECT 
                    v.gpo_id_2017 AS gpo_id, v.ur_id_2017 AS ur_id, v.ue_id_2017 AS ue_id, 
                    cta_gpo_id, cta_ur_id, cta_ue_id, 
                    x.ger_id, x.cgp_id, x.rub_id, x.cgcu_id, x.cgsc_id, 
                    x.cgc_id, x.cta_id, 
                    SUM( x.dpo_debe ) AS pdx_debe, SUM( x.dpo_haber ) AS pdx_haber, 
                    ".$niveles[0]." AS nivel, 
                    CONCAT( $key ) AS id 
                FROM ctrl2015.cg_PolizaMaestro m 
                    INNER JOIN ctrl2015.cg_PolizaDetalleOrden x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pol_numero )
                    ".$sqlsubcuentas."
                    INNER JOIN sinpres.vin_UnidadEjecutora_2015_2016 s ON m.gpo_id = s.gpo_id_2015 AND m.ur_id = s.ur_id_2015 AND m.ue_id = s.ue_id_2015 
                    INNER JOIN sinpres.vin_UnidadEjecutora_2016_2017 v ON s.gpo_id_2016 = v.gpo_id_2016 AND s.ur_id_2016 = v.ur_id_2016 AND s.ue_id_2016 = v.ue_id_2016 
                ".( ( $reporte['tipo'] == 2 && $_SESSION[_S]['nvj_id'] < 6 ) ? "    INNER JOIN ctrl2017.cat_CargaTrabajo t ON t.gpo_id = v.gpo_id_2017 AND t.ur_id = v.ur_id_2017
                WHERE t.usr_id = ".$_SESSION[_S]['s24-id']."
                    AND " : 
                "WHERE $urSql " ).( ($ue_id ) ? "v.ue_id_2017 = $ue_id 
                    AND ":'' )."
                    $periodoSql
                    AND m.epl_id != 'C' AND m.epl_id != 'H' 
                    $busquedaSql
                    $cvefinSql
                GROUP BY $key 
            )pdx 
                INNER JOIN ctrl2015.cg_PlanCuentas USING( cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id ) 
                INNER JOIN ctrl2015.cg_CuentaCuarto USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id ) 
                INNER JOIN ctrl2015.cg_CuentaMayor USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id ) 
                INNER JOIN ctrl2015.cat_CgSubcuenta USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id ) 
                INNER JOIN ctrl2015.cat_CgCuenta USING( ger_id, cgp_id, rub_id, cgcu_id ) 
                INNER JOIN ctrl2015.cat_Rubro USING( ger_id, cgp_id, rub_id ) 
                INNER JOIN ctrl2015.cat_CgGrupo USING( ger_id, cgp_id ) 
                INNER JOIN ctrl2015.cat_Genero USING( ger_id ) 
                INNER JOIN ctrl2015.cat_UnidadResponsable ur ON ur.gpo_id = cta_gpo_id AND ur.ur_id = cta_ur_id 
                INNER JOIN ctrl2015.cat_UnidadEjecutora ue ON ue.gpo_id = cta_gpo_id AND ue.ur_id = cta_ur_id AND ue.ue_id = cta_ue_id 
            $importeIgualSql 
            ORDER BY $ordenar 
        ";
        
        //echo '<pre>';echo $sql;echo '</pre>';//die();
        
        $contable = new AccesoDatos();//variable de konexion para los saldos iniciales
        
        $contable->conectar();
        $contable->setSql( $sql );
        $contable->consultar();
        $contable->desconectar();
        
        $datos = array();
        
        while( $rowContable = $contable->extraer() ){
            $datos['C.'.$rowContable['id']] = array( 
                'id'=>'C.'.$rowContable['id'],
                'ur_clave'=>$rowContable['ur_clave'],
                'ur_nombre'=>$rowContable['ur_nombre'],
                'cta_gpo_id'=>$rowContable['cta_gpo_id'],
                'cta_ur_id'=>$rowContable['cta_ur_id'],
                'cta_ue_id'=>$rowContable['cta_ue_id'],
                'ger_cuenta'=>$rowContable['ger_cuenta'],
                'ger_nombre'=>$rowContable['ger_nombre'],
                'cgp_cuenta'=>$rowContable['cgp_cuenta'],
                'cgp_nombre'=>$rowContable['cgp_nombre'],
                'rub_cuenta'=>$rowContable['rub_cuenta'],
                'rub_nombre'=>$rowContable['rub_nombre'],
                'cgcu_cuenta'=>$rowContable['cgcu_cuenta'],
                'cgcu_nombre'=>$rowContable['cgcu_nombre'],
                'cgsc_cuenta'=>$rowContable['cgsc_cuenta'],
                'cgsc_nombre'=>$rowContable['cgsc_nombre'],
                'cgc_cuenta'=>$rowContable['cgc_cuenta'],
                'cgc_nombre'=>$rowContable['cgc_nombre'],
                'cgq_cuenta'=>$rowContable['cgq_cuenta'],
                'cgq_nombre'=>$rowContable['cgq_nombre'],
                'cta_cuenta'=>$rowContable['cta_cuenta'],
                'cta_nombre'=>$rowContable['cta_nombre'],
                'cuenta'=>$rowContable['cuenta'],
                'nombre'=>$rowContable['nombre'],
                'pdx_debe'=>$rowContable['pdx_debe'],
                'pdx_haber'=>$rowContable['pdx_haber'],
                'cgm_naturaleza'=>$rowContable['cgm_naturaleza'],
                'nivel'=>$rowContable['nivel']
            );
            //echo $rowContable['id']."</br>";
        }
        //if( !$parametroExtra['I'] )die();
        /*if( count( $datos ) == 0 ) {
            
            $datos['vacio'] = array( 
                'id'=>'',
                'ur_clave'=>'',
                'ur_nombre'=>'',
                'cta_gpo_id'=>'',
                'cta_ur_id'=>'',
                'cta_ue_id'=>'',
                'ger_cuenta'=>'',
                'ger_nombre'=>'',
                'cgp_cuenta'=>'',
                'cgp_nombre'=>'',
                'rub_cuenta'=>'',
                'rub_nombre'=>'',
                'cgcu_cuenta'=>'',
                'cgcu_nombre'=>'',
                'cgsc_cuenta'=>'',
                'cgsc_nombre'=>'',
                'cgc_cuenta'=>'',
                'cgc_nombre'=>'',
                'cgq_cuenta'=>'',
                'cgq_nombre'=>'',
                'cta_cuenta'=>'',
                'cta_nombre'=>'',
                'cuenta'=>'',
                'nombre'=>'',
                'pdx_debe'=>'',
                'pdx_haber'=>'',
                'cgm_naturaleza'=>'',
                'nivel'=>''
            );
        }//*/
        
        //if( !$parametroExtra['I'] ){echo '<br><pre>C-';print_r($datos);echo '</pre>';die();}//*/
        return isset( $datos ) ? $datos:FALSE;
    }
?>