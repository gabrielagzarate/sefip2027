<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function getModificado( $omo_id, $gpo_id, $ur_id, $mod_id, $parametroExtra = array() ){
    
    if( isset($parametroExtra['provision']) && $parametroExtra['provision'] == 2 ){
        $sqlProvision = "AND d.gpo_id = $gpo_id AND d.ur_id = $ur_id ";
    }else{
        $sqlProvision = "";
    }
    
    $sql = "
        SELECT p.pre_claveCompleta, p.tcp_id, d.mov_id, 
            CASE d.mov_id WHEN 'A' THEN d.dmo_importe ELSE 0 END AS ampliacion, 
            CASE d.mov_id WHEN 'R' THEN d.dmo_importe ELSE 0 END AS reduccion, 
            d.dmo_ene, d.dmo_feb, d.dmo_mar, d.dmo_abr, d.dmo_may, d.dmo_jun, d.dmo_jul, d.dmo_ago, d.dmo_sep, d.dmo_oct, d.dmo_nov, d.dmo_dic 
        FROM scp_DetalleModificacion d 
            INNER JOIN presupuesto p USING( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id ) 
        WHERE omo_id = '$omo_id' 
            AND mod_gpo_id = $gpo_id AND mod_ur_id = $ur_id 
            AND mod_id = $mod_id 
            $sqlProvision 
    "; 
    
    //echo '<pre>';echo $sql;echo '</pre>';//die();
    
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar();
    $conexion->desconectar();
    
    //$data = new stdClass();
    $data = array();
    
    while( $dataRow = $conexion->extraer() ){
        $dato = new stdClass();
        $dato->pre_claveCompleta = $dataRow['pre_claveCompleta'];
        $dato->tipoClave = $dataRow['tcp_id'];
        $dato->mov_id = $dataRow['mov_id'];
        $dato->ampliacion = $dataRow['ampliacion'];
        $dato->reduccion = $dataRow['reduccion'];
        $dato->ene = $dataRow['dmo_ene'];
        $dato->feb = $dataRow['dmo_feb'];
        $dato->mar = $dataRow['dmo_mar'];
        $dato->abr = $dataRow['dmo_abr'];
        $dato->may = $dataRow['dmo_may'];
        $dato->jun = $dataRow['dmo_jun'];
        $dato->jul = $dataRow['dmo_jul'];
        $dato->ago = $dataRow['dmo_ago'];
        $dato->sep = $dataRow['dmo_sep'];
        $dato->oct = $dataRow['dmo_oct'];
        $dato->nov = $dataRow['dmo_nov'];
        $dato->dic = $dataRow['dmo_dic'];
        
        //array_push( $data, $dato );
        $data[$dataRow['pre_claveCompleta']] = $dato;
        //echo '<pre>'; print_r($dato);echo '</pre>';//die();
    }
    unset($conexion);
    //echo '<pre>'; print_r($data);echo '</pre>';//die();
    //echo count($data);die();
    if( count($data) == 0 ){
        $data = FALSE;
    }
    
    return $data;
}
?>