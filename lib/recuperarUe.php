<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of recuperarUe
 *
 * @author Peralta, Alfonso <alfonso.peralta@finanzasoaxaca.gob.mx>
 * Created: 07-nov-2017
 * Comment: 
 * Esta funcion recupera la UE de la tabla cat_ObraActividad de acuerdo al Encuadre Programatico proporcionado.
 * Se implemento debido a que las cedulas de costeo registraban la UE incorrectamente.
 * 
 * */
function recuperarUe($epg_id) {
    $ue = null;
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql('
        SELECT ue_id
        FROM enc_Programatico
            INNER JOIN cat_ObraActividad USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
        WHERE epg_id = ?;
    ');
    $conexion->consultar([$epg_id]);
    $conexion->desconectar();
    
    if ($row = $conexion->extraer()) {
        $ue = $row['ue_id'];
    }
    
    return $ue;
}