<?php

/**
 * Description of function consultaBancosUr
 *
 * author: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Update: 03-Octubre-2019
 * Comment: -----
 * Objective: Consulta datos del catálogo de cuentas bancarias.
 * Regla de Negocio: -----
 * 
 * */

function consultaBancosUr($conexion, $RepUr = false, $clabe = false)
{

    $sql_extra = '';
    $params = [];
    if ($clabe == false) {
        if ($RepUr != '') {
            $sql_extra = 'and concat(gpo_id, ur_id)= ? ';
            $params = [$RepUr];
        }
        $sql = '
            SELECT  gpo_id,ur_id,bur_id clabe, bur_cuentaBancaria cuenta,
                    iba_nombre,bur_alias,eba_id,eba_nombre,
                    bur_beneficiario,FECHAAuto
            FROM cat_BancosUr
                JOIN cat_InstitucionesBancarias USING(iba_id)
                JOIN cat_EstadoBancos USING (eba_id)
                LEFT JOIN (SELECT bur_id,usr_fecha AS FECHAAuto 
                            FROM ' . BD_BITACORA . '.bit_ModificacionBancosUr
                            WHERE bur_tipoCambio ="A"
                ) a USING(bur_id)
            WHERE eba_id =6 and bur_id > 000000000000000000 ' . $sql_extra . '
            ORDER BY gpo_id,ur_id
            ';
    } else if ($clabe != '') {
        $sql = 'SELECT tcl_id
            FROM vin_BancosUrTipoClc
            WHERE bur_id = ?';
             $params = [$clabe];
    }

    $conexion->setSql($sql);
    $conexion->consultar($params);

    $datos = array();
    while ($row = $conexion->extraer()) {
        if ($clabe == false) {
            $dato = array(
                'gpo_id' => $row['gpo_id'],
                'ur_id' => $row['ur_id'],
                'clabe' => $row['clabe'],
                'cuenta' => $row['cuenta'],
                'iba_nombre' => $row['iba_nombre'],
                'bur_alias' => $row['bur_alias'],
                'eba_id' => $row['eba_id'],
                'eba_nombre' => $row['eba_nombre'],
                'bur_beneficiario' => $row['bur_beneficiario'],
                'FECHAAuto' => ($row['FECHAAuto'] != NULL) ? date('d/m/Y', strtotime($row['FECHAAuto'])) : ''
            );
        } else if ($clabe != '') {
            $dato = array(
                'tcl_id' => $row['tcl_id']
            );
        }

        array_push($datos, $dato);
    }
    if (count($datos) == 0) {
        if ($clabe == false) {
            $dato = array(
                'gpo_id' => '',
                'ur_id' => '',
                'clabe' => '',
                'cuenta' => '',
                'iba_nombre' => '',
                'bur_alias' => '',
                'eba_id' => '',
                'eba_nombre' => '',
                'bur_beneficiario' => '',
                'FECHAAuto' => ''
            );
        } else if ($clabe != '') {
            $dato = array(
                'tcl_id' => ''
            );
        }

        array_push($datos, $dato);
    }

    return $datos;
}
