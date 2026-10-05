<?php

require_once dirname(__FILE__) . '/contableTransferido.php';
require_once dirname(__FILE__) . '/contable.php';
require_once dirname(__FILE__) . '/contableCog.php';
require_once dirname(__FILE__) . '/../../compararCadenas.php';
require_once dirname(__FILE__) . '/../../redondear.php';

function conciliacionCog($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array())
{
    switch ($mmc_id) {
        case 'D':
            $contable = contable($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra);
            $contableCog = contableCog($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra);
            break;
        case 'T':
            $contable = contableTransferido($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra);
            $contableCog = contableCog($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra);
            break;
        case 'P':
            $contable = contableCog($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, 'K', $niveles, $parametroExtra);
            $contableCog = contableCog($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra);
            break;
        default:
            echo 'kym';
            break;
    }

    $sumaContable = 0;
    $sumaContableCog = 0;
    $sumaDiferencia = 0;
    $datos = [];

    $matriz = array();
    $matriz['C'] = $contable;
    $matriz['P'] = $contableCog;

    foreach ($matriz as $key => $value) {
        foreach ($value as $llave => $valor) {
            if ($llave != 'vacio') {
                $datos[$llave]['id'] = $llave;
            }
        }
    }

    if (!empty($datos)) {
        asort($datos);
    }

    foreach ($datos as $ind => $indval) {
        foreach ($matriz as $key => $value) {
            foreach ($value as $llave => $valor) {
                if (compararCadenas($ind, $llave) == 0) {
                    $datos[$ind]['key'] = $value[$llave]['id'];
                    $datos[$ind]['gpo_id'] = $value[$llave]['gpo_id'];
                    $datos[$ind]['ur_id'] = $value[$llave]['ur_id'];
                    $datos[$ind]['ur_clave'] = $value[$llave]['ur_clave'];
                    $datos[$ind]['ur_nombre'] = $value[$llave]['ur_nombre'];
                    $datos[$ind]['ue_id'] = $value[$llave]['ue_id'];
                    $datos[$ind]['epg_id'] = $value[$llave]['epg_id'];
                    $datos[$ind]['oa_clave'] = $value[$llave]['oa_clave'];
                    $datos[$ind]['oa_nombre'] = $value[$llave]['oa_nombre'];
                    $datos[$ind]['tg_id'] = $value[$llave]['tg_id'];
                    $datos[$ind]['tg_nombre'] = $value[$llave]['tg_nombre'];
                    $datos[$ind]['idp_id'] = $value[$llave]['idp_id'];
                    $datos[$ind]['idp_nombre'] = $value[$llave]['idp_nombre'];
                    $datos[$ind]['pg_id'] = $value[$llave]['pg_id'];
                    $datos[$ind]['pg_nombre'] = $value[$llave]['pg_nombre'];
                    $datos[$ind]['pe_id'] = $value[$llave]['pe_id'];
                    $datos[$ind]['pe_nombre'] = $value[$llave]['pe_nombre'];
                    $datos[$ind]['ter_id'] = $value[$llave]['ter_id'];
                    $datos[$ind]['ter_nombre'] = $value[$llave]['ter_nombre'];
                    $datos[$ind]['ecf_id'] = $value[$llave]['ecf_id'];
                    $datos[$ind]['cfi_clave'] = $value[$llave]['cfi_clave'];
                    $datos[$ind]['cfi_nombre'] = $value[$llave]['cfi_nombre'];
                    switch ($key) {
                        case 'C':
                            $datos[$ind]['poliza'] = $value[$llave]['poliza'];
                            $datos[$ind]['cmp_id'] = $value[$llave]['cmp_id'];
                            $datos[$ind]['afe_id'] = $value[$llave]['afe_id'];
                            $datos[$ind]['bdo_id'] = $value[$llave]['bdo_id'];
                            $datos[$ind]['clc_id'] = $value[$llave]['clc_id'];
                            if ($mmc_id != 'D' && $value[$llave]['cuenta'] != '') {
                                $datos[$ind]['cuenta'] = $value[$llave]['cuenta'];
                            }

                            $datos[$ind]['pdx_importe'] = redondear($value[$llave]['pdx_importe'], 2);

                            $diferencia = $value[$llave]['pdx_importe'];
                            $sumaContable += $value[$llave]['pdx_importe'];
                            break;
                        case 'P':
                            $datos[$ind]['cog_poliza'] = $value[$llave]['poliza'];
                            $datos[$ind]['cog_cmp_id'] = $value[$llave]['cmp_id'];
                            $datos[$ind]['cog_afe_id'] = $value[$llave]['afe_id'];
                            $datos[$ind]['cog_bdo_id'] = $value[$llave]['bdo_id'];
                            $datos[$ind]['cog_clc_id'] = $value[$llave]['clc_id'];
                            $datos[$ind]['cuenta'] = $value[$llave]['cuenta'];

                            $datos[$ind]['cog_importe'] = redondear($value[$llave]['cog_importe'], 2);
                            $diferencia = $value[$llave]['cog_importe'];

                            $sumaContableCog += $value[$llave]['cog_importe'];
                            break;
                    }
                    break;
                } else if (compararCadenas($ind, $llave) < 0) {
                    break;
                }
            }
            if (compararCadenas($ind, $llave) == 0) {
                $x = array_shift($matriz[$key]);
            }
        }

        if (!isset($datos[$ind]['pdx_importe'])) {
            $datos[$ind]['poliza'] = '';
            $datos[$ind]['cmp_id'] = '';
            $datos[$ind]['afe_id'] = '';
            $datos[$ind]['bdo_id'] = '';
            $datos[$ind]['clc_id'] = '';

            $datos[$ind]['pdx_importe'] = 0;
        }
        if (!isset($datos[$ind]['cog_importe'])) {
            $datos[$ind]['cog_poliza'] = '';
            $datos[$ind]['cog_cmp_id'] = '';
            $datos[$ind]['cog_afe_id'] = '';
            $datos[$ind]['cog_bdo_id'] = '';
            $datos[$ind]['cog_clc_id'] = '';
            if (!isset($datos[$ind]['cuenta'])) {
                $datos[$ind]['cuenta'] = '';
            }

            $datos[$ind]['cog_importe'] = 0;
        }
        $datos[$ind]['diferencia'] = redondear($datos[$ind]['pdx_importe'] - $datos[$ind]['cog_importe'], 2);
        $sumaDiferencia += $datos[$ind]['diferencia'];
    }

    if ($reporte['tipo'] != 1 && $reporte['tipo'] != '') {
        if ($niveles[0] != 1) {
            $gpo_idA = 0;
            $ur_idA = 00;
            $ur_claveA = 000;
            $ur_nombreA = '';
            $ue_idA = 000;
            $sumaContableUr = 0;
            $sumaContableCogUr = 0;
            $sumaDiferenciaUr = 0;
            $gpo_idE = '';
            $ur_idE = '';
            $ur_claveE = '';
            $ur_nombreE = '';
            $ue_idE = '';
            foreach ($datos as $key => $value) {
                if ($ur_claveA < $datos[$key]['ur_clave']) {
                    if ($ur_claveA != 000) {
                        $dato[$ur_claveA . '.' . 'subtotal']['key'] = $ur_claveA . '.' . 'subtotal';
                        $dato[$ur_claveA . '.' . 'subtotal']['gpo_id'] = $gpo_idA;
                        $dato[$ur_claveA . '.' . 'subtotal']['ur_id'] = $ur_idA;
                        $dato[$ur_claveA . '.' . 'subtotal']['ur_clave'] = $ur_claveA;
                        $dato[$ur_claveA . '.' . 'subtotal']['ur_nombre'] = $ur_nombreA;
                        $dato[$ur_claveA . '.' . 'subtotal']['ue_id'] = $ue_idA;
                        $dato[$ur_claveA . '.' . 'subtotal']['epg_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['oa_clave'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['oa_nombre'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['tg_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['tg_nombre'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['idp_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['idp_nombre'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['pg_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['pg_nombre'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['pe_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['pe_nombre'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['ter_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['ter_nombre'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['ecf_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['cfi_clave'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['cfi_nombre'] = 'subtotal';
                        $dato[$ur_claveA . '.' . 'subtotal']['pdx_importe'] = redondear($sumaContableUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['cog_importe'] = redondear($sumaContableCogUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['diferencia'] = redondear($sumaDiferenciaUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['cmp_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['afe_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['bdo_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['clc_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['poliza'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['cog_cmp_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['cog_afe_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['cog_bdo_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['cog_clc_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['cog_poliza'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['cuenta'] = '';

                        $sumaContableUr = 0;
                        $sumaContableCogUr = 0;
                        $sumaDiferenciaUr = 0;
                    }

                    $gpo_idA = $datos[$key]['gpo_id'];
                    $ur_idA = $datos[$key]['ur_id'];
                    $ur_claveA = $datos[$key]['ur_clave'];
                    $ur_nombreA = $datos[$key]['ur_nombre'];
                    $ue_idA = $datos[$key]['ue_id'];
                }
                $dato[$key] = $datos[$key];

                $sumaContableUr += $datos[$key]['pdx_importe'];
                $sumaContableCogUr += $datos[$key]['cog_importe'];
                $sumaDiferenciaUr += $datos[$key]['diferencia'];

                $gpo_idE = $datos[$key]['gpo_id'];
                $ur_idE = $datos[$key]['ur_id'];
                $ur_claveE = $datos[$key]['ur_clave'];
                $ur_nombreE = $datos[$key]['ur_nombre'];
                $ue_idE = $datos[$key]['ue_id'];
            }

            $dato[$ur_claveE . '.' . 'subtotal']['key'] = $ur_claveE . '.' . 'subtotal';
            $dato[$ur_claveE . '.' . 'subtotal']['gpo_id'] = $gpo_idE;
            $dato[$ur_claveE . '.' . 'subtotal']['ur_id'] = $ur_idE;
            $dato[$ur_claveE . '.' . 'subtotal']['ur_clave'] = $ur_claveE;
            $dato[$ur_claveE . '.' . 'subtotal']['ur_nombre'] = $ur_nombreE;
            $dato[$ur_claveE . '.' . 'subtotal']['ue_id'] = $ue_idE;
            $dato[$ur_claveE . '.' . 'subtotal']['epg_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['oa_clave'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['oa_nombre'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['tg_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['tg_nombre'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['idp_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['idp_nombre'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['pg_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['pg_nombre'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['pe_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['pe_nombre'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['ter_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['ter_nombre'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['ecf_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['cfi_clave'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['cfi_nombre'] = 'subtotal';
            $dato[$ur_claveE . '.' . 'subtotal']['pdx_importe'] = redondear($sumaContableUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['cog_importe'] = redondear($sumaContableCogUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['diferencia'] = redondear($sumaDiferenciaUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['cmp_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['afe_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['bdo_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['clc_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['poliza'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['cog_cmp_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['cog_afe_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['cog_bdo_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['cog_clc_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['cog_poliza'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['cuenta'] = '';
        }
        $datos = $dato;
    }

    $datos['total']['key'] = 'total';
    $datos['total']['gpo_id'] = '';
    $datos['total']['ur_id'] = '';
    $datos['total']['ur_clave'] = '';
    $datos['total']['ur_nombre'] = '';
    $datos['total']['ue_id'] = '';
    $datos['total']['epg_id'] = '';
    $datos['total']['oa_clave'] = '';
    $datos['total']['oa_nombre'] = '';
    $datos['total']['tg_id'] = '';
    $datos['total']['tg_nombre'] = '';
    $datos['total']['idp_id'] = '';
    $datos['total']['idp_nombre'] = '';
    $datos['total']['pg_id'] = '';
    $datos['total']['pg_nombre'] = '';
    $datos['total']['pe_id'] = '';
    $datos['total']['pe_nombre'] = '';
    $datos['total']['ter_id'] = '';
    $datos['total']['ter_nombre'] = '';
    $datos['total']['ecf_id'] = '';
    $datos['total']['cfi_clave'] = '';
    $datos['total']['cfi_nombre'] = 'TOTAL';
    $datos['total']['pdx_importe'] = redondear($sumaContable, 2);
    $datos['total']['cog_importe'] = redondear($sumaContableCog, 2);
    $datos['total']['diferencia'] = redondear($sumaDiferencia, 2);
    $datos['total']['cmp_id'] = '';
    $datos['total']['afe_id'] = '';
    $datos['total']['bdo_id'] = '';
    $datos['total']['clc_id'] = '';
    $datos['total']['poliza'] = '';
    $datos['total']['cog_cmp_id'] = '';
    $datos['total']['cog_afe_id'] = '';
    $datos['total']['cog_bdo_id'] = '';
    $datos['total']['cog_clc_id'] = '';
    $datos['total']['cog_poliza'] = '';
    $datos['total']['cuenta'] = '';

    return $datos;
}
