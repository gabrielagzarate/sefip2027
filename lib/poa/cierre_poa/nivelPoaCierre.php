<?php

/**
 * Description of function nivelPoaCierre
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 31-ago-2018
 * Comment: -----
 * Objective: Ordena los datos consultados en niveles de acuerdo a la opcion que se requiera, cierre poa.
 * Regla de Negocio: -----
 *
 * */

require_once __DIR__ . '/consultaPoaCierre.php';

function nivelPoaCierre($gpo_id, $ur_id, $ccd_id)
{

    $cedula = consultaPoaCierre($gpo_id, $ur_id, $ccd_id);
    $cedula = array_reverse($cedula);

    $urAnterior = '';
    $prgAnterior = '';
    $sprAnterior = '';
    $ueAnterior = '';
    $pyAnterior = '';
    $oaAnterior = '';
    $cfiAnterior = '';
    $pgAnterior = '';

    switch ($ccd_id) {
        case 1:
            break;
        default:
            $urAnterior = $cedula[0]['ur_clave'];
            $prgAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'];
            $sprAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'];
            $ueAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['ue_id'];
            $pyAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['ue_id'] . '.' . $cedula[0]['py_id'];
            $oaAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['ue_id'] . '.' . $cedula[0]['py_id'] . '.' . $cedula[0]['oa_id'];
            $cfiAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['ue_id'] . '.' . $cedula[0]['py_id'] . '.' . $cedula[0]['oa_id'] . '.' . $cedula[0]['ecf_id'];
            $pgAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['ue_id'] . '.' . $cedula[0]['py_id'] . '.' . $cedula[0]['oa_id'] . '.' . $cedula[0]['ecf_id'] . '.' . $cedula[0]['pg_id'];
            break;
    }
    $urSuma = 0;

    switch ($ccd_id) {
        case 1:
            $pg = true;
            $pe = true;
            $peAnterior = '';
            $peKey = 0;
            $subtotal = false;
            break;
        default:
            echo 'kym';
            break;
    }
    $datos = [];

    if ($cedula[0]['nivel'] != 1) {
        foreach ($cedula as $key => $val) {
            // Definir el nivel 10
            $urSuma += $cedula[$key]['pre_original'];
            switch ($ccd_id) {
                case 1:
                    $dato = [];
                    $dato['gran_fuente'] = $cedula[$key]['ter_id'] . ':' . $cedula[$key]['ter_nombre'];
                    $dato['clase_gasto'] = $cedula[$key]['crc_id'] . ':' . $cedula[$key]['crc_nombre'];
                    $dato['techo_financiero'] = $cedula[$key]['tec_techoPresupuestal'];
                    $dato['presupuestado'] = $cedula[$key]['pre_original'];
                    $dato['diferencia'] = $cedula[$key]['diferencia'];
                    $dato['nivel'] = 10;
                    array_push($datos, $dato);
                    break;
                default:
                    echo 'kym';
                    break;
            }

            // Definir el nivel 9 y 8
            switch ($ccd_id) {
                case 1:
                    break;
                default:
                    echo 'kym';
                    break;
            }

            // Difinir el nivel 7
            switch ($ccd_id) {
                case 1:
                    break;
            }
            // Definir el nivel 6
            switch ($ccd_id) {
                case 1:
                    break;
            }
            // Definir el nivel 5
            switch ($ccd_id) {
                case 1:
                    break;
            }

            // Definir el nivel 4
            switch ($ccd_id) {
                case 1:
                    break;
            }

            // Definir el nivel 3
            switch ($ccd_id) {
                case 1:
                    break;
            }
            // Definir el nivel 2
            switch ($ccd_id) {
                case 1:
                    break;
            }
        }
        // Definir el nivel 1
        switch ($ccd_id) {
            case 1:
                $dato = [];
                $dato['importe'] = $urSuma;
                $dato['nivel'] = 1;
                array_push($datos, $dato);
                break;
        }
    } else {
        switch ($ccd_id) {
            case 1:
                break;
            default:
                $dato['clave'] = $cedula[0]['ur_clave'];
                $dato['nombre'] = $cedula[0]['ur_nombre'];
                $dato['importe'] = $urSuma;
                $dato['nivel'] = 1;
                array_push($datos, $dato);
                break;
        }
    }

    return $datos;
}
