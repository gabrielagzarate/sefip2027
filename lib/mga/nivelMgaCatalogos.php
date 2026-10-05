<?php

/**
 * Description of function nivelCatalogos
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 20-ago-2018
 * Comment: -----
 * Objective: Ordena los datos consultados en niveles de acuerdo a la opcion que se requiera, asignacion pg-ur.
 * Regla de Negocio: -----
 *
 * */

require_once __DIR__ . '/consultaMgaCatalogos.php';
function nivelCatalogos($gpo_id, $ur_id, $ue_id, $epg_id, $ecf_id, $ccd_id)
{
    $cedula = consultaMgaCatalogos($gpo_id, $ur_id, $ue_id, $epg_id, $ecf_id, $ccd_id);
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
        case 2:
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
        case 2:
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
            // Definir el nivel 11
            switch ($ccd_id) {
                case 2:
                    $dato = [];
                    $dato['partida'] = $cedula[$key]['partida'];
                    $dato['descripcion'] = $cedula[$key]['descripcion'];
                    $dato['usada'] = $cedula[$key]['usada'];
                    $dato['nivel'] = 11;
                    array_push($datos, $dato);
                    break;
                default:
                    echo 'kym';
                    break;
            }

            // Definir el nivel 10
            switch ($ccd_id) {
                case 2:
                    break;
                default:
                    echo 'kym';
                    break;
            }

            // Definir el nivel 9 y 8
            switch ($ccd_id) {
                case 2:
                    break;
                default:
                    echo 'kym';
                    break;
            }

            // Difinir el nivel 7
            switch ($ccd_id) {
                case 2:
                    break;
            }//fi switch

            // Definir el nivel 6
            switch ($ccd_id) {
                case 2:
                    break;
            }

            // Definir el nivel 5
            switch ($ccd_id) {
                case 2:
                    break;
            }

            // Definir el nivel 4
            switch ($ccd_id) {
                case 2:
                    break;
            }

            // Definir el nivel 3
            switch ($ccd_id) {
                case 2:
                    break;
            }

            // Definir el nivel 2
            switch ($ccd_id) {
                case 2:
                    break;
            }

            // Definir el nivel 1
            switch ($ccd_id) {
                case 2:
                    break;
            }
        }
    } else {
        switch ($ccd_id) {
            case 2:
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
}// fin nivelCatalogos
