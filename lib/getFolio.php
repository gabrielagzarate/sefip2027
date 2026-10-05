<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once dirname(__FILE__).'/getModificado.php';
require_once dirname(__FILE__).'/validaFecha.php';

function getFolio( $gpo_id, $ur_id, $omo_id = '', $mod_id = '', $parametroExtra = array() ){
    $ejercicio = ( isset( $parametroExtra['ejercicio'] ) ? $parametroExtra['ejercicio']:'' );
    
    if( $gpo_id == '' || $ur_id == '' ){
        return false;
    }
    $table = "ctrl".$ejercicio.".scp_MaestroModificacion m ";
    
    $folio = new stdClass();
    
    /*------#VALIDAR QUE ENVIEN EL DOCUMENTO Ó ( RFC Y REFERENCIA )*/
    if( $omo_id == '' && $mod_id == '' ){
        $folio->valido = false;
        $folio->error = 1;
        $folio->mensaje ='NO ES POSIBLE VALIDAR EL FOLIO';
        return $folio;
    }
    
    if( $omo_id != '' && $mod_id != '' ){
        $sql = "
            SELECT * 
            FROM $table 
                INNER JOIN ctrl".$ejercicio.".enc_OrigenOperacion USING( enc_oop ) 
                INNER JOIN ctrl".$ejercicio.".cat_TipoFolioModificacion USING( tfm_id ) 
                INNER JOIN ctrl".$ejercicio.".cat_TipoModificacion USING( tmd_id ) 
                INNER JOIN ctrl".$ejercicio.".cat_ModalidadModificacion USING( mmd_id ) 
                INNER JOIN sinpres.enc_ClaveFinanciamiento USING( ecf_id ) 
                INNER JOIN ctrl".$ejercicio.".cat_OrigenModificacion o ON o.omo_id = m.omo_id 
            WHERE m.omo_id = '$omo_id' 
                AND m.mod_gpo_id = $gpo_id AND m.mod_ur_id = $ur_id 
                AND m.mod_id = $mod_id 
        ";
    }
    
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar();
    $conexion->desconectar();
    
    if( $conexion->getNumeroRegistros() == 0 ){
        $folio->valido = FALSE;
        $folio->error = 3;
        $folio->mensaje = 'EL FOLIO NO EXISTE CON LOS DATOS PROPORCIONADOS';
        $conexion->desconectar();
        unset($conexion);
        return $folio;
    }
    
    $dataRow = $conexion->extraer();
    //echo '<br><pre>';print_r($dataRow);echo '</pre>';die();//*/
    unset($conexion);
    $date = new stdClass;
    $date->gpo_id = $gpo_id;
    $date->ur_id = $ur_id;
    $date->fecha = substr( $dataRow['mod_fechaAutorizacion'], 0, 10 );
    $fecha = validaFecha($date);
    
    /*------#DATOS DEL FOLIO      */	
    $folio->valido = TRUE;
    $folio->gpo_id 		= $dataRow['mod_gpo_id'];
    $folio->ur_id 		= $dataRow['mod_ur_id'];
    $folio->omo_id 		= $dataRow['omo_id'];
    $folio->folio 		= $dataRow['mod_id'];
    $folio->modalidad 	= $dataRow['mmd_nombre'];
    $folio->origen 	= $dataRow['omo_nombre'];
    $folio->tmd_id 	= $dataRow['tmd_id'];
    $folio->tipoAdecuacion 	= $dataRow['tmd_nombre'];
    $folio->concepto 	= $dataRow['mod_concepto'];
    //$folio->ampliacion 		= $dataRow['mod_ampliacion'];
    //$folio->reduccion 		= $dataRow['mod_reduccion'];
    $folio->fechaAutorizacion 	= $fecha->fecha;
    $folio->mesAutorizado 	= $dataRow['msc_id_autorizacion'];
    $folio->subsecretaria  = ( $dataRow['mod_Subsecretaria'] == 1 ? 'EGRESOS':'PLANEACIÓN' );
    $folio->tipoFolio = $dataRow['tfm_nombre'];
    $folio->cvefin	= $dataRow['ecf_clave'];
    
    unset($dataRow);
    unset($conexion);
    
    $folio->detalleModificado = getModificado( $folio->omo_id, $folio->gpo_id, $folio->ur_id, $folio->folio, $parametroExtra );
    //echo "<pre>"; print_r($folio); echo "</pre>";die();
    return $folio;
}
?>