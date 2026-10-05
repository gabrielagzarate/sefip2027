<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function estadoMesContable( $dato ){
    if( !$dato )
        return false;
    
    $conexion = new AccesoDatos();
    $conexion->conectar();	
    $sql = "
        SELECT ems_id 
        FROM cg_MesContable 
        WHERE gpo_id = $dato->gpo_id AND ur_id = $dato->ur_id 
            AND msc_id = $dato->mes 
    ";
    $conexion->setSql($sql);
    $conexion->consultar();
    $estadoMes = $conexion->extraer();
    $conexion->desconectar();
    
    return $estadoMes['ems_id'];
}