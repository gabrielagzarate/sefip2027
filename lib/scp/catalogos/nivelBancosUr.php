<?php

/**
 * Description of function nivelBancosUr
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Update: 03-Octubre-2019
 * Comment: -----
 * Objective: Ordena los datos consultados en niveles, para hacer el catálogo de cuentas bancarias,
     bancos unidad responsable.
 * Regla de Negocio: -----
 * 
 * */

require_once dirname(__FILE__) . '/consultaBancosUr.php';

function nivelBancosUr($RepUr)
{

    $conexion = new AccesoDatos();
    $conexion->conectar();
    $clave = consultaBancosUr($conexion, $RepUr, false);

    $datos = array();
    foreach ($clave as $key => $val) {
        $serv = '';
        $gast = '';
        $inv = '';
        $mun = '';
        $deud = '';
        $bursa = '';
        $transfer = '';
        $otros = '';

        // Definir el nivel 11
        $dato = array();
        $dato['gpo_id'] = $clave[$key]['gpo_id'];
        $dato['ur_id'] = $clave[$key]['ur_id'];
        $dato['ur'] = $clave[$key]['gpo_id'] . $clave[$key]['ur_id'];
        $dato['clabe'] = $clave[$key]['clabe'];
        $dato['cuenta'] = $clave[$key]['cuenta'];
        $dato['iba_nombre'] = $clave[$key]['iba_nombre'];
        $dato['bur_alias'] = $clave[$key]['bur_alias'];
        $dato['eba_id'] = $clave[$key]['eba_id'];
        $dato['eba_nombre'] = $clave[$key]['eba_nombre'];
        $dato['bur_beneficiario'] = $clave[$key]['bur_beneficiario'];
        $dato['FECHAAuto'] = substr($clave[$key]['FECHAAuto'], 0, 10);

        $cuenta = consultaBancosUr($conexion, false, $clave[$key]['clabe']);

        foreach ($cuenta as $key1 => $val1) {
            switch ($cuenta[$key1]['tcl_id']) {
                case 'A':
                    $serv = 'X';
                    break;
                case 'B':
                    $gast = 'X';
                    break;
                case 'F':
                case 'G':
                case 'H':
                    $inv = 'X';
                    break;
                case 'I':
                    $mun = 'X';
                    break;
                case 'J':
                    $deud = 'X';
                    break;
                case 'K':
                    $bursa = 'X';
                    break;
                case 'L':
                    $transfer = 'X';
                    break;
                case 'M':
                    $otros = 'X';
                    break;
            }
        }

        $dato['serv'] = $serv;
        $dato['gast'] = $gast;
        $dato['inv'] = $inv;
        $dato['mun'] = $mun;
        $dato['deud'] = $deud;
        $dato['bursa'] = $bursa;
        $dato['transfer'] = $transfer;
        $dato['otros'] = $otros;

        $dato['nivel'] = 11;
        array_push($datos, $dato);
    }

    $conexion->desconectar();
    return array_reverse($datos);
}
