<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

    // Arma un combo con las Unidades Ejecutoras a mostrar para el filtro
    // Se hizo así porque si uso ponCombo() tal y como viene en otros .phps nomás no me queda
    // cuando la consulta regresa un solo registro, me pone el combo y me pone un textbox
    // ------------------------------------------------------------------------------------------------------------------------
function ComboUnidadesEjecutoras()
{
    $conexion = new AccesoDatos();
    $conexion->conectar();

    // Si en cat_CargaTrabajo tiene ue_id == null entonces NO está limitado por UE
    // Pero si sí está definido un valor en ése campo, quiere decir que sí debemos limitarlo a dichas UEs
    $sql = "SELECT ue_id FROM cat_CargaTrabajo WHERE usr_id = " . $_SESSION[_S]['usr_id'];
    $conexion->setSql( $sql );
    $conexion->consultar();
    $numRegistros = $conexion->getNumeroRegistros();
    $row = $conexion->extraer();

    // Si no tiene carga, puede ver todo
    if ( $numRegistros == 0 ) {
        $joinUE = '';
    } else {
        if ( is_null( $row['ue_id'] ) ) {
            $joinUE = '';
        } else {
            $joinUE = 'JOIN ( SELECT ue_id FROM cat_CargaTrabajo WHERE usr_id = ' . $_SESSION[_S]['usr_id'] . ' ) ct USING( ue_id )';
        }
    }

    // Ahora obtenemos todos los que tiene asignados
    $sql = "SELECT ue_id, ue_nombre FROM cat_UnidadEjecutora $joinUE WHERE gpo_id = " . $_SESSION[_S]['clasAdm-gpo'] . " AND ur_id = " . $_SESSION[_S]['clasAdm-ur'].(isset($_SESSION[_S]['ue_id']) && $_SESSION[_S]['nvj_id'] == 1 && !is_null($_SESSION[_S]['ue_id']) ? " AND ue_id = ".$_SESSION[_S]['ue_id']."":"") ;
    
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql( $sql );
    $conexion->consultar();
    $numRegistros = $conexion->getNumeroRegistros();
    $conexion->desconectar();
    
    switch ($numRegistros) {
        case 0:
            $strCombo = '<select name="ueFiltro" id="ueFiltro" disabled class="form-select"><option value="XXX">ERROR</option></select>';
            break;
        case 1:
            $strCombo = '<select name="ueFiltro" id="ueFiltro" disabled class="form-select">';
            while( $row = $conexion->extraer() ){
                $strCombo .= '<option value="' . $row['ue_id'] . '">' . $row['ue_id'] . " - " . $row['ue_nombre'] . '</option>';
            }
            $strCombo .= '</select>';
            break;
        default:
            $strCombo = '<select name="ueFiltro" id="ueFiltro" class="form-select">';
            $strCombo .= '<option value="">Mostrar todas las ejecutoras</option>';
            while( $row = $conexion->extraer() ){
                $strCombo .= '<option value="' . $row['ue_id'] . '">' . $row['ue_id'] . " - " . $row['ue_nombre'] . '</option>';
            }
            $strCombo .= '</select>';
            break;
    }
    return $strCombo;
}