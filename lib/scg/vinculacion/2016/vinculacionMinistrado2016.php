<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

    require_once dirname(__FILE__).'/../../../compararCadenas.php';
    require_once dirname(__FILE__).'/../../../redondear.php';

    function vinculacionMinistrado2016( $gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array() ){
        //echo "<pre>";print_r($parametroExtra);echo "</pre>";

        switch( $mmc_id ){
            case 'B':
                $polizaSql = 'CASE tmi_id WHEN 2 THEN tmi_nombre ELSE pa.polizaAutorizado END AS polizaAutorizado, pa.fechaAutorizado, ';
                break;
            case 'T':
                $polizaSql = 'CASE tmi_id WHEN 1 THEN pa.polizaAutorizado ELSE tmi_nombre END AS polizaAutorizado, pa.fechaAutorizado, ';
                break;
            default:
                echo 'kym';
                break;
        }

        $sqlFiltro = '';
        if( $parametroExtra['gur'] != '' )
            $sqlFiltro = "v.gpo_id = ".substr( $parametroExtra['gur'], 0, 1 )." AND v.ur_id = ".substr( $parametroExtra['gur'], 1, 2 )." ";
        if( $parametroExtra['clc_id'] != '' )
            $sqlFiltro .= ( ($sqlFiltro != '' ? "AND ":"")."v.clc_id = ".$parametroExtra['clc_id']." " );
        if( $parametroExtra['bdo_id'] != '' )
            $sqlFiltro .= ($sqlFiltro != '' ? "AND ":"")."v.bdo_id = ".$parametroExtra['bdo_id']." ";
        if( $parametroExtra['rfc_id'] != '' )
            $sqlFiltro .= ($sqlFiltro != '' ? "AND ":"")."b.rfc_id = '".$parametroExtra['rfc_id']."' ";
        if( $parametroExtra['bdo_referencia'] != '' )
            $sqlFiltro .= ($sqlFiltro != '' ? "AND ":"")."b.bdo_referencia IN (".$parametroExtra['bdo_referencia'].") ";
        if( isset($parametroExtra['pensiones']) && $parametroExtra['pensiones'] ){
            $pensiones = "AND v.tmi_id = 7 ";
        }else{
            $pensiones = "";
        }
        if( $periodo['mesDesde'] != '' ){
            $sqlFiltro .= ($sqlFiltro != '' ? "AND ":"")."MONTH( c.ing_fechaPago ) = ".$periodo['mesDesde']." ";
        }

        $sql = "
            SELECT
                v.gpo_id, v.ur_id, v.clc_id, v.afe_id, concat(gpo_id, ur_id) as gur,
                v.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia, b.bdo_concepto,
                v.tdf_id AS tdf_id_ministrado, v.bdm_referencia,
                b.bdo_percepcionPresupuestal, b.bdo_percepcionContable, b.bdo_retencionContable,
                MONTH( c.ing_fechaPago ) AS msc_id,
                v.bdm_estado AS estado, DATE( c.ing_fechaPago ) AS fechaAutorizacion, DATE( v.bdm_fechaCancelado ) AS fechaCancelacion,
                v.bdm_agrupador AS agrupador,
                $polizaSql
                pa.gpo_id_polizaAutorizado, pa.ur_id_polizaAutorizado, pa.ue_id_polizaAutorizado,
                pa.msc_id_polizaAutorizado, pa.pot_id_polizaAutorizado, pa.prf_id_polizaAutorizado,
                pa.pma_id_polizaAutorizado,

                pc.polizaCancelado, pc.fechaCancelado,
                pc.gpo_id_polizaCancelado, pc.ur_id_polizaCancelado, pc.ue_id_polizaCancelado,
                pc.msc_id_polizaCancelado, pc.pot_id_polizaCancelado, pc.prf_id_polizaCancelado,
                pc.pma_id_polizaCancelado,

                CONCAT( v.gpo_id, v.ur_id, '.', LPAD( v.msc_id, 2, 000 ), '.', LPAD( v.afe_id, 6, 0000000 ), '.', LPAD( v.bdo_id, 6, 000000 ) ) AS id
            FROM ctrl2016.cat_BancoDocumentos b
                INNER JOIN ctrl2016.ing_BancoDocumentoMinistrado v USING( gpo_id, ur_id, bdo_id )
                INNER JOIN ctrl2016.scp_Clc c USING( gpo_id, ur_id, clc_id )
                INNER JOIN ctrl2017.cat_TipoMinistracion USING( tmi_id )
                LEFT JOIN(
                    SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado, m.ue_id AS ue_id_polizaAutorizado,
                        m.msc_id AS msc_id_polizaAutorizado, m.pot_id AS pot_id_polizaAutorizado, m.prf_id AS prf_id_polizaAutorizado,
                        m.pma_id AS pma_id_polizaAutorizado, m.pma_fecha AS fechaAutorizado,
                        CONCAT( m.pot_id, '-', m.prf_id, m.pma_id ) AS polizaAutorizado,
                        x.pdx_gpo_id_vinculacion AS gpo_id, x.pdx_ur_id_vinculacion AS ur_id,
                        x.pdx_afe_id_vinculacion AS afe_id, x.pdx_bdo_id_vinculacion AS bdo_id,
                        x.pdx_agrupador AS agrupador
                    FROM ctrl2023.cg_PolizaMaestro m
                        INNER JOIN ctrl2023.cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = $gpo_id AND m.ur_id = $ur_id
                        AND m.mmc_id = '$mmc_id'
                        AND m.tmc_id = 'A'
                        AND x.pdx_ejercicio = 2016
                    GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_origen, x.pdx_bdo_id_vinculacion
                )pa USING( gpo_id, ur_id, afe_id, bdo_id )
                LEFT JOIN (
                    SELECT m.gpo_id AS gpo_id_polizaCancelado, m.ur_id AS ur_id_polizaCancelado, m.ue_id AS ue_id_polizaCancelado,
                        m.msc_id AS msc_id_polizaCancelado, m.pot_id AS pot_id_polizaCancelado, m.prf_id AS prf_id_polizaCancelado,
                        m.pma_id AS pma_id_polizaCancelado, m.pma_fecha AS fechaCancelado,
                        CONCAT( m.pot_id, '-', m.prf_id, m.pma_id ) AS polizaCancelado,
                        x.pdx_gpo_id_vinculacion AS gpo_id, x.pdx_ur_id_vinculacion AS ur_id,
                        x.pdx_afe_id_vinculacion AS afe_id, x.pdx_bdo_id_vinculacion AS bdo_id,
                        x.pdx_agrupador AS agrupador
                    FROM ctrl2023.cg_PolizaMaestro m
                        INNER JOIN ctrl2023.cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = $gpo_id AND m.ur_id = $ur_id
                        AND m.mmc_id = '$mmc_id'
                        AND m.tmc_id = 'C'
                        AND x.pdx_ejercicio = 2016
                    GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_vinculacion, x.pdx_bdo_id_vinculacion
                )pc USING( gpo_id, ur_id, afe_id, bdo_id )
            WHERE $sqlFiltro
                AND YEAR( c.ing_fechaPago ) = 2022
                $pensiones
            GROUP BY gpo_id, ur_id, clc_id, bdo_id, afe_id
        ";
        //echo '<pre>';echo $sql;echo '</pre>';die();

        $conexion = new AccesoDatos();//variable de konexion para los saldos iniciales
        $conexion->conectar();
        $conexion->setSql( $sql );
        $conexion->consultar();
        $conexion->desconectar();

		$datos = array();

		/*$dat = array();
		while ($rowData = $conexion->extraer()) {
			array_push($dat, $rowData);
		}
		$datos["data"] = $dat;
		//echo '<br><pre>C-';print_r($datos);echo '</pre>';die();//
		return $datos;*/


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
                'tdf_id_ministrado'=>$rowData['tdf_id_ministrado'],
                'bdm_referencia'=>$rowData['bdm_referencia'],
                'fechaAutorizacion'=>$rowData['fechaAutorizacion'],
                'fechaCancelacion'=>$rowData['fechaCancelacion'],
                'polizaAutorizado'=>$rowData['polizaAutorizado'],
                'fechaAutorizado'=>$rowData['fechaAutorizado'],
                'polizaCancelado'=>$rowData['polizaCancelado'],
                'fechaCancelado'=>$rowData['fechaCancelado'],
                'agrupador'=>$rowData['agrupador']
            );

        }

        //echo '<br><pre>C-';print_r($datos);echo '</pre>';die();//
        return $datos;

    }

?>
