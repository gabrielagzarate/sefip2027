<?php

//agregar a calidad 10:31 am-7 feb
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/contableModificado.php';
require_once dirname(__FILE__) . '/presupuestalModificado.php';
require_once dirname(__FILE__) . '/../../compararCadenas.php';
require_once dirname(__FILE__) . '/../../redondear.php';

function conciliacionModificado($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array())
{

    $contable = contableModificado($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra);
    $presupuestal = presupuestalModificado($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra);

    //variables de acumulacion de las sumas inicial, del periodo y final
    $sumaContableReduccion = 0;
    $sumaContableAmpliacion = 0;
    $sumaPresupuestalReduccion = 0;
    $sumaPresupuestalAmpliacion = 0;
    $sumaDiferenciaReduccion = 0;
    $sumaDiferenciaAmpliacion = 0;

    $matriz = array();
    $matriz['C'] = $contable;
    $matriz['P'] = $presupuestal;

    foreach ($matriz as $key => $value) {
        foreach ($value as $llave => $valor) {
            $datos[$llave]['id'] = $llave;
        }
    }

    asort($datos);

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
                    $datos[$ind]['mod_id'] = $value[$llave]['mod_id'];
                    $datos[$ind]['mod_clave'] = $value[$llave]['mod_clave'];
                    switch ($key) {
                        case 'C':
                            $datos[$ind]['poliza'] = $value[$llave]['poliza'];
                            $datos[$ind]['pdx_reduccion'] = redondear(($value[$llave]['pdx_reduccion']), 2);
                            $datos[$ind]['pdx_ampliacion'] = redondear($value[$llave]['pdx_ampliacion'], 2);
                            break;
                        case 'P':
                            $datos[$ind]['pre_reduccion'] = redondear($value[$llave]['pre_reduccion'], 2);
                            $datos[$ind]['pre_ampliacion'] = redondear($value[$llave]['pre_ampliacion'], 2);
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

        if (!isset($datos[$ind]['pdx_reduccion'])) {
            $datos[$ind]['poliza'] = '';

            $datos[$ind]['pdx_reduccion'] = 0;
            $datos[$ind]['pdx_ampliacion'] = 0;
        }
        if (!isset($datos[$ind]['pre_reduccion'])) {
            $datos[$ind]['pre_reduccion'] = 0;
            $datos[$ind]['pre_ampliacion'] = 0;
        }
        $datos[$ind]['diferenciaReduccion'] = redondear($datos[$ind]['pdx_reduccion'] - $datos[$ind]['pre_reduccion'], 2);
        $datos[$ind]['diferenciaAmpliacion'] = redondear($datos[$ind]['pdx_ampliacion'] - $datos[$ind]['pre_ampliacion'], 2);
        $sumaContableReduccion += $datos[$ind]['pdx_reduccion'];
        $sumaContableAmpliacion += $datos[$ind]['pdx_ampliacion'];
        $sumaPresupuestalReduccion += $datos[$ind]['pre_reduccion'];
        $sumaPresupuestalAmpliacion += $datos[$ind]['pre_ampliacion'];
        $sumaDiferenciaReduccion += $datos[$ind]['diferenciaReduccion'];
        $sumaDiferenciaAmpliacion += $datos[$ind]['diferenciaAmpliacion'];
    }

    if ($reporte['tipo'] != 1 && $reporte['tipo'] != '') {
        if ($niveles[0] != 1) {
            $gpo_idA = 0;
            $ur_idA = 00;
            $ur_claveA = 000;
            $ur_nombreA = '';
            $ue_idA = 000;
            $sumaContableReduccionUr = 0;
            $sumaContableAmpliacionUr = 0;
            $sumaPresupuestalReduccionUr = 0;
            $sumaPresupuestalAmpliacionUr = 0;
            $sumaDiferenciaReduccionUr = 0;
            $sumaDiferenciaAmpliacionUr = 0;
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
                        $dato[$ur_claveA . '.' . 'subtotal']['pdx_reduccion'] = redondear($sumaContableReduccionUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['pdx_ampliacion'] = redondear($sumaContableAmpliacionUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['pre_reduccion'] = redondear($sumaPresupuestalReduccionUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['pre_ampliacion'] = redondear($sumaPresupuestalAmpliacionUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['diferenciaReduccion'] = redondear($sumaDiferenciaReduccionUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['diferenciaAmpliacion'] = redondear($sumaDiferenciaAmpliacionUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['mod_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['mod_clave'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['poliza'] = '';

                        $sumaContableReduccionUr = 0;
                        $sumaContableAmpliacionUr = 0;
                        $sumaPresupuestalReduccionUr = 0;
                        $sumaPresupuestalAmpliacionUr = 0;
                        $sumaDiferenciaReduccionUr = 0;
                        $sumaDiferenciaAmpliacionUr = 0;
                    }

                    $gpo_idA = $datos[$key]['gpo_id'];
                    $ur_idA = $datos[$key]['ur_id'];
                    $ur_claveA = $datos[$key]['ur_clave'];
                    $ur_nombreA = $datos[$key]['ur_nombre'];
                    $ue_idA = $datos[$key]['ue_id'];
                }
                $dato[$key] = $datos[$key];

                $sumaContableReduccionUr += $datos[$key]['pdx_reduccion'];
                $sumaContableAmpliacionUr += $datos[$key]['pdx_ampliacion'];
                $sumaPresupuestalReduccionUr += $datos[$key]['pre_reduccion'];
                $sumaPresupuestalAmpliacionUr += $datos[$key]['pre_ampliacion'];
                $sumaDiferenciaReduccionUr += $datos[$key]['diferenciaReduccion'];
                $sumaDiferenciaAmpliacionUr += $datos[$key]['diferenciaAmpliacion'];

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
            $dato[$ur_claveE . '.' . 'subtotal']['pdx_reduccion'] = redondear($sumaContableReduccionUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['pdx_ampliacion'] = redondear($sumaContableAmpliacionUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['pre_reduccion'] = redondear($sumaPresupuestalReduccionUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['pre_ampliacion'] = redondear($sumaPresupuestalAmpliacionUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['diferenciaReduccion'] = redondear($sumaDiferenciaReduccionUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['diferenciaAmpliacion'] = redondear($sumaDiferenciaAmpliacionUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['mod_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['mod_clave'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['poliza'] = '';
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
    $datos['total']['pdx_reduccion'] = redondear($sumaContableReduccion, 2);
    $datos['total']['pdx_ampliacion'] = redondear($sumaContableAmpliacion, 2);
    $datos['total']['pre_reduccion'] = redondear($sumaPresupuestalReduccion, 2);
    $datos['total']['pre_ampliacion'] = redondear($sumaPresupuestalAmpliacion, 2);
    $datos['total']['diferenciaReduccion'] = redondear($sumaDiferenciaReduccion, 2);
    $datos['total']['diferenciaAmpliacion'] = redondear($sumaDiferenciaAmpliacion, 2);
    $datos['total']['mod_id'] = '';
    $datos['total']['mod_clave'] = '';
    $datos['total']['poliza'] = '';

    return $datos;
}

?>