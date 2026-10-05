<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function getPolizaDetalle($gpo_id, $ur_id, $ue_id, $msc_id, $pot_id, $prf_id, $pma_id, $detalle) {
    $parametrosSQL = [
        ':gpo_id' => $gpo_id,
        ':ur_id' => $ur_id,
        ':ue_id' => $ue_id,
        ':msc_id' => $msc_id,
        ':pot_id' => $pot_id,
        ':prf_id' => $prf_id,
        ':pma_id' => $pma_id,
    ];
    switch ($detalle) {
        case 1:
            $id = "MAX( x.pdx_id ) AS pdx_id,max(pdx_concepto) as pdx_concepto,";
            $atributo = "";
            $importe = "SUM( pdx_debe ) AS pdx_debe, SUM( pdx_haber ) AS pdx_haber, ";
            $group = "GROUP BY ger_id, cgp_id, rub_id, cgcu_id, cgsc_id ";
            break;
        case 2:
            $id = "MAX( x.pdx_id ) AS pdx_id, max(pdx_concepto) as pdx_concepto,";
            $atributo = "x.cgc_id, x.cta_id, ";
            $importe = " SUM( pdx_debe ) AS pdx_debe, SUM( pdx_haber ) AS pdx_haber, ";
            $group = "GROUP BY ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id ";
            break;
        case 3:
            $id = "x.pdx_id,pdx_concepto,";
            $atributo = "x.cgc_id, x.cta_id, 
                x.cta_gpo_id, x.cta_ur_id, x.cta_ue_id, ";
            $importe = "pdx_debe, pdx_haber, ";
            $group = "";
            break;
    }
    
    $sql = 
        "SELECT 
            m.gpo_id, m.ur_id, m.ue_id, m.msc_id, m.pot_id, m.prf_id, m.pma_id, 
            $id
            x.ger_id, x.cgp_id, x.rub_id, x.cgcu_id, x.cgsc_id, 
            $atributo
            $importe
            cgsc_cuenta,cgsc_nombre
        FROM cg_PolizaMaestro m
            INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
            INNER JOIN cat_CgSubcuenta USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
        WHERE gpo_id = :gpo_id AND ur_id = :ur_id 
            AND ue_id = :ue_id 
            AND msc_id = :msc_id 
            AND pot_id = :pot_id 
            AND prf_id = :prf_id 
            AND pma_id = :pma_id 
        $group 
    ";
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($parametrosSQL);
    
    $data = array();
    
    while ($dataRow = $conexion->extraer()) {
        $dato = new stdClass();
        $dato->genero = $dataRow['ger_id'];
        $dato->grupo = $dataRow['cgp_id'];
        $dato->rubro = $dataRow['rub_id'];
        $dato->cuenta = $dataRow['cgcu_id'];
        $dato->subcuenta = $dataRow['cgsc_id'];
        $dato->debe = $dataRow['pdx_debe'];
        $dato->haber = $dataRow['pdx_haber'];
        $dato->cuentaMayor = $dataRow['cgsc_cuenta'];
        $dato->cuentaMayorNombre = $dataRow['cgsc_nombre'];
        $dato->concepto = $dataRow['pdx_concepto'];
        
        switch ($detalle) {
            case 2:
                $dato->sefin = $dataRow['cgc_id'];
                $dato->registro = $dataRow['cta_id'];
                break;
            case 3:
                $dato->sefin = $dataRow['cgc_id'];
                $dato->registro = $dataRow['cta_id'];
                $dato->cta_gpo_id = $dataRow['cta_gpo_id'];
                $dato->cta_ur_id = $dataRow['cta_ur_id'];
                $dato->cta_ue_id = $dataRow['cta_ue_id'];
                break;
        }
        
        $data[$dataRow['pdx_id']] = $dato;
    }
    unset($conexion);
    
    if( count($data) == 0 ){
        $data = FALSE;
    }
    
    return $data;
}