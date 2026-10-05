<?php
    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */
    // Arma un combo con las Agrupaciones de Póliza Global
    // ------------------------------------------------------------------------------------------------------------------------
    function ComboAgrupacionPolizaGlobal()
    {
        // Marcos: 06/abr/2017
        // Para los nuevos registros en cat_AgrupacionPolizaGlobal, todas verán solamente GRAL
        // La 116 además verá SAP
        $whereapg = "WHERE apg_id != 'SAP'";
        if ($_SESSION[_S]['clasAdm-gpo'] == 1 && $_SESSION[_S]['clasAdm-ur'] == 16) {
            $whereapg = "";
        }

        $sql = 'SELECT apg_id, apg_nombre AS nombre FROM cat_AgrupacionPoliza ' . $whereapg;

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar();
        $conexion->desconectar();

        $strCombo = '<select name="apg_id" id="apg_id" class="form-select">';
        $strCombo .= '<option value="">MOSTRAR TODAS LAS AGRUPACIONES</option>';
        while ($row = $conexion->extraer()) {
            $strCombo .= '<option value="' . $row['apg_id'] . '">' . $row['nombre'] . '</option>';
        }
        $strCombo .= '</select>';

        return $strCombo;
    }