<?php

/**
 * Description of function nivelpoaProgramacion
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 16-Agt-2018
 * Update for SEFIP2020: 10-Mayo-2019
 * Migrated: 08-Jul-2020
 * Comment: -----
 * Objective: Ordena los datos consultados para mostrarlo en el PDF comenzando con programa, subprograma,proyecto
y actividad/obra.
 * Regla de Negocio: -----
 * Permits: Esta pantalla no pregunta por permisos de escritura.
 * */

require_once dirname(__FILE__) . '/consultapoaProgramacion.php';

function nivelpoaProgramacion($gpo_id, $ur_id, $ccd_id, $prg_id, $spr_id, $py_id, $eoa_id)
{
    $cedula = consultapoaProgramacion($gpo_id, $ur_id, $ccd_id, $prg_id, $spr_id, $py_id, $eoa_id);

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
            $urAnterior = $cedula[0]['ur_clave'];
            $prgAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'];
            $sprAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'];
            $ueAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'];
            $pyAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['py_id'];
            $oaAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['py_id'] . '.' . $cedula[0]['oa_id'];
            $cfiAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['py_id'] . '.' . $cedula[0]['oa_id'];
            $pgAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['py_id'] . '.' . $cedula[0]['oa_id'];
            break;
    }
    $urKey = 0;
    $prgKey = 0;
    $sprKey = 0;
    $ueKey = 0;
    $pyKey = 0;
    $oaKey = 0;
    $cfiKey = 0;
    $pgKey = 0;
    $urSuma = 0;
    $prgSuma = 0;
    $sprSuma = 0;
    $ueSuma = 0;
    $pySuma = 0;
    $oaSuma = 0;
    $cfiSuma = 0;
    $pgSuma = 0;
    $peSuma = 0;
    $pgUltima = '';

    switch ($ccd_id) {
        case 2:
            $pg = false;
            $pe = false;
            $peAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['py_id'] . '.' . $cedula[0]['oa_id'];
            $peKey = 0;
            $subtotal = false;
            break;
        default:
            echo "kym";
            break;
    }
    $datos = array();

    if ($cedula[0]['nivel'] != 1) {
        foreach ($cedula as $key => $val) {
            $dato = array();
            if ($pe) {
                if ($peAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id'] . '.' . $cedula[$key]['pg_id']) {
                    $dato['nombre'] = $cedula[$peKey]['pe_nombre'];
                    $dato['importe'] = $peSuma;
                    $dato['nivel'] = 9;
                    $peAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id'];
                    $peKey = $key;
                    $peSuma = 0;
                }
            }
            if ($pg) {
                if ($pgAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id']) {
                    $dato['nombre'] = $pgUltima['pg_nombre'];
                    $dato['importe'] = $pgSuma;
                    $dato['nivel'] = 8;
                    $pgAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id'];
                    $pgKey = $key;
                    $pgSuma = 0;
                }
            }

            if ($cfiAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id']) {
                $dato['importe'] = $cfiSuma;
                $dato['nivel'] = 7;
                switch ($ccd_id) {
                    case 2:
                        break;
                }
                $cfiAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id'];
                $cfiKey = $key;
                $cfiSuma = 0;
                $pgSuma = 0;
                $peSuma = 0;
            }
            if ($pyAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['py_id']) {
                $dato['clave'] = $cedula[$pyKey]['py_id'];
                $dato['nombre'] = $cedula[$pyKey]['py_nombre'];
                $dato['importe'] = $pySuma;
                $dato['nivel'] = 5;
                array_push($datos, $dato);
                $pyAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['py_id'];
                $pyKey = $key;
                $pySuma = 0;
                $oaSuma = 0;
                $cfiSuma = 0;
                $pgSuma = 0;
                $peSuma = 0;
            }
            if ($ueAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id']) {
                $dato['importe'] = $ueSuma;
                $dato['nivel'] = 4;
                switch ($ccd_id) {
                    case 2:
                        break;
                    default:
                        array_push($datos, $dato);
                        break;
                }
                $ueAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'];
                $ueKey = $key;
                $ueSuma = 0;
                $pySuma = 0;
                $oaSuma = 0;
                $cfiSuma = 0;
                $pgSuma = 0;
                $peSuma = 0;
            }
            if ($sprAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id']) {
                $dato['clave'] = $cedula[$sprKey]['spr_id'];
                $dato['nombre'] = $cedula[$sprKey]['spr_nombre'];
                $dato['importe'] = $sprSuma;
                $dato['nivel'] = 3;
                array_push($datos, $dato);
                $sprAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'];
                $sprKey = $key;
                $sprSuma = 0;
                $ueSuma = 0;
                $pySuma = 0;
                $oaSuma = 0;
                $cfiSuma = 0;
                $pgSuma = 0;
                $peSuma = 0;
            }
            if ($prgAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id']) {
                $dato['clave'] = $cedula[$prgKey]['prg_id'];
                $dato['nombre'] = $cedula[$prgKey]['prg_nombre'];
                $dato['nivel'] = 2;
                $dato['importe'] = $prgSuma;
                array_push($datos, $dato);
                $prgAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'];
                $prgKey = $key;
                $prgSuma = 0;
                $sprSuma = 0;
                $ueSuma = 0;
                $pySuma = 0;
                $oaSuma = 0;
                $cfiSuma = 0;
                $pgSuma = 0;
                $peSuma = 0;
            }
            if ($urAnterior != $cedula[$key]['ur_clave']) {
                $dato['clave'] = $cedula[$urKey]['ur_clave'];
                $dato['nombre'] = $cedula[$urKey]['ur_nombre'];
                $dato['nivel'] = 1;
                $dato['importe'] = $urSuma;
                switch ($ccd_id) {
                    case 2:
                        break;
                    default:
                        array_push($datos, $dato);
                        break;
                }
                $urAnterior = $cedula[$key]['ur_clave'];
                $urKey = $key;
                $urSuma = 0;
                $prgSuma = 0;
                $sprSuma = 0;
                $ueSuma = 0;
                $pySuma = 0;
                $oaSuma = 0;
                $cfiSuma = 0;
                $pgSuma = 0;
                $peSuma = 0;
            }
            $dato['nivel'] = 10;
            $dato['total'] = $cedula[$key]['total'];
            switch ($ccd_id) {
                case 2:
                    break;
                default:
                    echo "kym";
                    break;
            }

            array_push($datos, $dato);

            $urSuma += (int) $cedula[$key]['total'];
            $prgSuma += (int) $cedula[$key]['total'];
            $sprSuma += (int) $cedula[$key]['total'];
            $ueSuma += (int) $cedula[$key]['total'];
            $pySuma += (int) $cedula[$key]['total'];
            $oaSuma += (int) $cedula[$key]['total'];
            $cfiSuma += (int) $cedula[$key]['total'];

            $pgUltima = $dato;

            switch ($ccd_id) {
                case 2:
                    break;
                default:
                    echo "kym";
                    break;
            }

            $dato['importe'] = $cfiSuma;
            $dato['nivel'] = 7;
            switch ($ccd_id) {
                case 2:
                    break;
            }

            $dato['clave'] = $cedula[$key]['oa_id'];
            $dato['nombre'] = $cedula[$key]['oa_nombre'];
            $dato['nombre_eoa'] = $cedula[$key]['eoa_nombre'];
            $dato['importe'] = $oaSuma;
            $dato['nivel'] = 6;
            switch ($ccd_id) {
                case 2:
                    array_push($datos, $dato);
                    break;
            }

            $dato['clave'] = $cedula[$key]['py_id'];
            $dato['nombre'] = $cedula[$key]['py_nombre'];
            $dato['importe'] = $pySuma;
            $dato['nivel'] = 5;
            switch ($ccd_id) {
                case 2:
                    array_push($datos, $dato);
                    break;
            }

            $dato['importe'] = $ueSuma;
            $dato['nivel'] = 4;
            switch ($ccd_id) {
                case 2:
                    break;
                default:
                    array_push($datos, $dato);
                    break;
            }

            $dato['clave'] = $cedula[$key]['spr_id'];
            $dato['nombre'] = $cedula[$key]['spr_nombre'];
            $dato['importe'] = $sprSuma;
            $dato['nivel'] = 3;
            switch ($ccd_id) {
                case 2:
                    array_push($datos, $dato);
                    break;
            }

            $dato['clave'] = $cedula[$key]['prg_id'];
            $dato['nombre'] = $cedula[$key]['prg_nombre'];
            $dato['importe'] = $prgSuma;
            $dato['nivel'] = 2;
            switch ($ccd_id) {
                case 2:
                    array_push($datos, $dato);
                    break;
            }

            $dato['clave'] = $cedula[$key]['ur_clave'];
            $dato['nombre'] = $cedula[$key]['ur_nombre'];
            $dato['importe'] = $urSuma;
            $dato['nivel'] = 1;
            switch ($ccd_id) {
                case 2:
                    break;
                default:
                    array_push($datos, $dato);
                    break;
            }
        }
    }
    return $datos;
}
