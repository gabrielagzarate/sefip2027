<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of claveFinRecursoFederal
 *
 * @author 
 * Created: 
 * Comment: 
 * 
 * */

function claveFinRecursoFederal($array = false)
{
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql('
        SELECT ecf_id
        FROM  enc_ClaveFinanciamiento
            INNER JOIN sinpres.cat_SuborigenRecurso'.APP_EJERCICIO.' USING(cff_id, fte_id, sft_id, ori_id, sor_id)
        WHERE ter_id = "B" AND sts_id = 1
            AND ecf_id <> 0
    ');
    $conexion->consultar();
    $conexion->desconectar();
    $cvefinRf='';
    while( $row=$conexion->extraer() ){
        $cvefinRf.=$row['ecf_id'].',';
    }
    
    $cvefinRf = trim($cvefinRf, ',');
    
    if($array) return explode(',',$cvefinRf);
    else return $cvefinRf;
}