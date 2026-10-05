<?php
/**
 * Description of nivelMgaComplementos:
 *
 * Objective: Ordena los datos consultados en niveles de acuerdo a la opcion que se requiera, modificar techos,
 *               altas de previsiones o ajustes.
 * Author : -----
 * Collaborators : Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Update: 26-Jul-2019
 * Permits: Esta pantalla no pregunta por permisos de escritura.
 **/

require_once dirname(__FILE__) . '/consultaMgaComplementosPrevisiones.php';

function nivelMgaComplementos($gpo_id, $ur_id, $ue_id, $epg_id, $ecf_id, $ccd_id)
{
    $cedula = consultaMgaComplementos($gpo_id, $ur_id, $ue_id, $epg_id, $ecf_id, $ccd_id);

    $cedula = array_reverse($cedula);

    $urAnterior = $cedula[0]['ur_clave'];
    $prgAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'];
    $sprAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'];
    $ueAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['ue_id'];
    $pyAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['ue_id'] . '.' . $cedula[0]['py_id'];
    $oaAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['ue_id'] . '.' . $cedula[0]['py_id'] . '.' . $cedula[0]['oa_id'];
    $cfiAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['ue_id'] . '.' . $cedula[0]['py_id'] . '.' . $cedula[0]['oa_id'] . '.' . $cedula[0]['ecf_id'];
    $pgAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['ue_id'] . '.' . $cedula[0]['py_id'] . '.' . $cedula[0]['oa_id'] . '.' . $cedula[0]['ecf_id'] . '.' . $cedula[0]['pg_id'];
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
        case 1:
            $pg = false;
            $pe = false;
            $peKey = 0;
            $subtotal = false;
            break;
        case 2:
            $pg = true;
            $pe = true;
            $peAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['ue_id'] . '.' . $cedula[0]['py_id'] . '.' . $cedula[0]['oa_id'] . '.' . $cedula[0]['ecf_id'] . '.' . $cedula[0]['pg_id'] . '.' . $cedula[0]['pe_id'];
            $peKey = 0;
            $subtotal = false;
            break;
        case 3:
            $pg = true;
            $pe = true;
            $peAnterior = $cedula[0]['ur_clave'] . '.' . $cedula[0]['prg_id'] . '.' . $cedula[0]['spr_id'] . '.' . $cedula[0]['ue_id'] . '.' . $cedula[0]['py_id'] . '.' . $cedula[0]['oa_id'] . '.' . $cedula[0]['ecf_id'] . '.' . $cedula[0]['pg_id'] . '.' . $cedula[0]['pe_id'];
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
                if ($peAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id'] . '.' . $cedula[$key]['ecf_id'] . '.' . $cedula[$key]['pg_id'] . '.' . $cedula[$key]['pe_id']) {
                    $dato['clave'] = $cedula[$peKey]['pe_id'];
                    $dato['nombre'] = $cedula[$peKey]['pe_nombre'];
                    $dato['importe'] = $peSuma;
                    $dato['nivel'] = 9;
                    $peAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id'] . '.' . $cedula[$key]['ecf_id'] . '.' . $cedula[$key]['pg_id'] . '.' . $cedula[$key]['pe_id'];
                    $peKey = $key;
                    $peSuma = 0;
                }
            }
            if ($pg) {
                if ($pgAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id'] . '.' . $cedula[$key]['ecf_id'] . '.' . $cedula[$key]['pg_id']) {
                    $dato['clave'] = $cedula[$pgKey]['pg_id'];
                    $dato['nombre'] = $cedula[$pgKey]['pg_nombre'];
                    $dato['importe'] = $pgSuma;
                    $dato['nivel'] = 8;
                    $pgAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id'] . '.' . $cedula[$key]['ecf_id'] . '.' . $cedula[$key]['pg_id'];
                    $pgKey = $key;
                    $pgSuma = 0;
                }
            }

            if ($cfiAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id'] . '.' . $cedula[$key]['ecf_id']) {
                $dato['clave'] = $cedula[$cfiKey]['ter_id'] . ' - ' . $cedula[$cfiKey]['cfi_clave'];
                $dato['nombre'] = $cedula[$cfiKey]['cfi_nombre'];
                $dato['importe'] = $cfiSuma;
                $dato['nivel'] = 7;
                switch ($ccd_id) {
                    case 2:
                        break;
                    case 3:
                        array_push($datos, $dato);
                        break;
                }
                $cfiAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id'] . '.' . $cedula[$key]['ecf_id'];
                $cfiKey = $key;
                $cfiSuma = 0;
                $pgSuma = 0;
                $peSuma = 0;
            }
            if ($oaAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id']) {
                $dato['clave'] = $cedula[$oaKey]['oa_id'];
                $dato['nombre'] = $cedula[$oaKey]['oa_nombre'];
                $dato['importe'] = $oaSuma;
                $dato['nivel'] = 6;
                array_push($datos, $dato);
                $oaAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id'] . '.' . $cedula[$key]['oa_id'];
                $oaKey = $key;
                $oaSuma = 0;
                $cfiSuma = 0;
                $pgSuma = 0;
                $peSuma = 0;
            }
            if ($pyAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id']) {
                $dato['clave'] = $cedula[$pyKey]['py_id'];
                $dato['nombre'] = $cedula[$pyKey]['py_nombre'];
                $dato['importe'] = $pySuma;
                $dato['nivel'] = 5;
                array_push($datos, $dato);
                $pyAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'] . '.' . $cedula[$key]['py_id'];
                $pyKey = $key;
                $pySuma = 0;
                $oaSuma = 0;
                $cfiSuma = 0;
                $pgSuma = 0;
                $peSuma = 0;
            }
            if ($ueAnterior != $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id']) {
                $dato['clave'] = $cedula[$ueKey]['ue_id'];
                $dato['nombre'] = $cedula[$ueKey]['ue_nombre'];
                $dato['importe'] = $ueSuma;
                $dato['nivel'] = 4;
                array_push($datos, $dato);
                $ueAnterior = $cedula[$key]['ur_clave'] . '.' . $cedula[$key]['prg_id'] . '.' . $cedula[$key]['spr_id'] . '.' . $cedula[$key]['ue_id'];
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
                array_push($datos, $dato);
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

            $dato = array();
            $dato['nivel'] = 10;
            $dato['total'] = $cedula[$key]['total'];
            switch ($ccd_id) {
                case 1:
                    $dato['asr_nombre'] = $cedula[$key]['ter_id'] . '    ' . $cedula[$key]['ter_nombre'];
                    $dato['gbf_nombre'] = $cedula[$key]['gbf_nombre'];
                    $dato['ali_beneficiarios'] = $cedula[$key]['ali_beneficiarios'];
                    break;
                case 2:
                    $dato['asr_nombre'] = $cedula[$key]['oa_nombre'];
                    $dato['gbf_nombre'] = $cedula[$key]['cfi_clave'];
                    $dato['ali_beneficiarios'] = $cedula[$key]['cfi_nombre'];
                    $dato['ali_cuotaDiaria'] = $cedula[$key]['pg_id'];
                    $dato['ali_diasOtorgados'] = $cedula[$key]['pe_id'];
                    $pgSuma += intval($cedula[$key]['total']);
                    $peSuma += intval($cedula[$key]['total']);
                    break;
                case 3:
                    $dato['gpo_id'] = $cedula[$key]['gpo_id'];
                    $dato['ur_id'] = $cedula[$key]['ur_id'];
                    $dato['ue_id'] = $cedula[$key]['ue_id'];
                    $dato['prg_id'] = $cedula[$key]['prg_id'];
                    $dato['spr_id'] = $cedula[$key]['spr_id'];
                    $dato['py_id'] = $cedula[$key]['py_id'];
                    $dato['oa_id'] = $cedula[$key]['oa_id'];
                    $dato['pg_id'] = $cedula[$key]['pg_id'];
                    $dato['pg_nombre'] = $cedula[$key]['pg_nombre'];
                    $dato['pe_id'] = $cedula[$key]['pe_id'];
                    $dato['pe_nombre'] = $cedula[$key]['pe_nombre'];
                    $dato['cff_id'] = $cedula[$key]['cff_id'];
                    $dato['fte_id'] = $cedula[$key]['fte_id'];
                    $dato['sft_id'] = $cedula[$key]['sft_id'];
                    $dato['ori_id'] = $cedula[$key]['ori_id'];
                    $dato['sor_id'] = $cedula[$key]['sor_id'];
                    $dato['cfi_id'] = $cedula[$key]['cfi_id'];
                    $dato['cfi_anio'] = $cedula[$key]['cfi_anio'];
                    $dato['tcp_id'] = $cedula[$key]['tcp_id'];
                    break;
                default:
                    echo "kym";
                    break;
            }

            array_push($datos, $dato);

            $urSuma += intval($cedula[$key]['total']);
            $prgSuma += intval($cedula[$key]['total']);
            $sprSuma += intval($cedula[$key]['total']);
            $ueSuma += intval($cedula[$key]['total']);
            $pySuma += intval($cedula[$key]['total']);
            $oaSuma += intval($cedula[$key]['total']);
            $cfiSuma += intval($cedula[$key]['total']);

            $pgUltima = $dato;
        }

        $dato = array();
        //telefonia imprime el nivel de partida especifica
        switch ($ccd_id) {
            case 1:
                break;
            case 2:
                $dato['clave'] = $cedula[$peKey]['pe_id'];
                $dato['nombre'] = $cedula[$peKey]['pe_nombre'];
                $dato['importe'] = $peSuma;
                $dato['nivel'] = 9;
                $dato['clave'] = $cedula[$peKey]['pg_id'];
                $dato['nombre'] = $cedula[$peKey]['pg_nombre'];
                $dato['importe'] = $pgSuma;
                $dato['nivel'] = 8;
                break;
            case 3:

                break;
            default:
                echo "kym";
                break;
        }

        $dato['clave'] = $cedula[$cfiKey]['ter_id'] . ' - ' . $cedula[$cfiKey]['cfi_clave'];
        $dato['nombre'] = $cedula[$cfiKey]['cfi_nombre'];
        $dato['importe'] = $cfiSuma;
        $dato['nivel'] = 7;
        switch ($ccd_id) {
            case 1:
                break;
            case 2:

                break;
            case 3:
                array_push($datos, $dato);
                break;
        }

        $dato['clave'] = $cedula[$oaKey]['oa_id'];
        $dato['nombre'] = $cedula[$oaKey]['oa_nombre'];
        $dato['importe'] = $oaSuma;
        $dato['nivel'] = 6;
        switch ($ccd_id) {
            case 1:
                break;
            case 2:
                array_push($datos, $dato);
                break;
            case 3:
                array_push($datos, $dato);
                break;
        }

        $dato['clave'] = $cedula[$pyKey]['py_id'];
        $dato['nombre'] = $cedula[$pyKey]['py_nombre'];
        $dato['importe'] = $pySuma;
        $dato['nivel'] = 5;
        switch ($ccd_id) {
            case 1:
                break;
            case 2:
                array_push($datos, $dato);
                break;
            case 3:
                array_push($datos, $dato);
                break;
        }

        $dato['clave'] = $cedula[$ueKey]['ue_id'];
        $dato['nombre'] = $cedula[$ueKey]['ue_nombre'];
        $dato['importe'] = $ueSuma;
        $dato['nivel'] = 4;
        switch ($ccd_id) {
            case 1:
                break;
            case 2:
                array_push($datos, $dato);
                break;
            case 3:
                array_push($datos, $dato);
                break;
        }

        $dato['clave'] = $cedula[$sprKey]['spr_id'];
        $dato['nombre'] = $cedula[$sprKey]['spr_nombre'];
        $dato['importe'] = $sprSuma;
        $dato['nivel'] = 3;
        switch ($ccd_id) {
            case 1:
                break;
            case 2:
                array_push($datos, $dato);
                break;
            case 3:
                array_push($datos, $dato);
                break;
        }

        $dato['clave'] = $cedula[$prgKey]['prg_id'];
        $dato['nombre'] = $cedula[$prgKey]['prg_nombre'];
        $dato['importe'] = $prgSuma;
        $dato['nivel'] = 2;
        switch ($ccd_id) {
            case 1:
                break;
            case 2:
                array_push($datos, $dato);
                break;
            case 3:
                array_push($datos, $dato);
                break;
        }

        $dato['clave'] = $cedula[$urKey]['ur_clave'];
        $dato['nombre'] = $cedula[$urKey]['ur_nombre'];
        $dato['importe'] = $urSuma;
        $dato['nivel'] = 1;
        array_push($datos, $dato);
    } else {
        $dato['clave'] = $cedula[0]['ur_clave'];
        $dato['nombre'] = $cedula[0]['ur_nombre'];
        $dato['importe'] = $urSuma;
        $dato['nivel'] = 1;
        array_push($datos, $dato);
    }
    return $datos;
}
