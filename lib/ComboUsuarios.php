<?php
    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */
     
// Arma un combo con los usuarios de la UR / UE que hayan adicionado / modificado pólizas
    // ------------------------------------------------------------------------------------------------------------------------
    function ComboUsuarios()
    {

        $sql = 'SELECT usr_id, usr_usuario FROM cat_Usuario U
                INNER JOIN ctrlUsuario.cat_Usuario ctrU USING (usr_id,usr_usuario)
                INNER JOIN cat_PermisosUsuarios PU USING ( usr_id )
            WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . '  AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'];

        // Si es un usuario de nivel UE, forzamos a que siempre vean sólo su UE
        if ( isset( $_SESSION[_S]['clasAdm-ue'] ) && $_SESSION[_S]['clasAdm-ue'] != "" ) {
        // MARCOS: 27/abr/2015
        // Por cambio del modelo de carga de trabajo ... no se muestra nada, así que mientras comentamos
        // la siguiente línea
        // $sql .= " AND ue_id = " . $_SESSION[_S]['clasAdm-ue'];
        // Si NO es un usuario de nivel UE, puede ver solamente el nivel 2 y 1 ( UR y UE )
        } else {
            $sql .= ' AND ( nvj_id = 2 OR nvj_id = 1 ) ';
        }

        $sql .= " AND sis_id = 'C' GROUP BY usr_id, usr_usuario;";

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql( $sql );
        $conexion->consultar();
        $numRegistros = $conexion->getNumeroRegistros();
        $conexion->desconectar();

        switch ($numRegistros) {
            case 0:
                $strCombo = '<select name="usr_id" id="usr_id" disabled class="form-select"><option value="XXX">ERROR</option></select>';
                break;
            case 1:
                $strCombo = '<select name="usr_id" id="usr_id" disabled class="form-select">';
                $strCombo .= '<option value="">Mostrar todos</option>';
                while ($row = $conexion->extraer()) {
                    $strCombo .= '<option value="' . $row['usr_id'] . '">' . $row['usr_usuario'] . '</option>';
                }
                $strCombo .= '</select>';
                break;
            default:
                $strCombo = '<select name="usr_id" id="usr_id" class="form-select">';
                $strCombo .= '<option value="">Mostrar todos</option>';
                while ($row = $conexion->extraer()) {
                    $strCombo .= '<option value="' . $row['usr_id'] . '">' . $row['usr_usuario'] . '</option>';
                }
                $strCombo .= '</select>';
                break;
        }
        return $strCombo;
    }