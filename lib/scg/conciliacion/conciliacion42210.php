<?php

require_once dirname(__FILE__) . '/contable42210.php';
require_once dirname(__FILE__) . '/fondos42210.php';
require_once dirname(__FILE__) . '/presupuestal.php';
require_once dirname(__FILE__) . '/presupuestalRectificado.php';
require_once dirname(__FILE__) . '/../../compararCadenas.php';
require_once dirname(__FILE__) . '/../../redondear.php';

function conciliacion42210($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array())
{
    if ($reporte['tipo'] != 1) {
        $fondos = fondos42210($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra);
        $niveles[0] = 1;
        $niveles[1] = 1;
        $niveles[2] = 0;
        $niveles[3] = 0;
        $niveles[4] = 0;
        $niveles[5] = 0;
        $niveles[6] = 0;
        $niveles[7] = 0;
        $niveles[8] = 0;
        $niveles[9] = 0;
        $niveles[10] = 0;

        $presupuestal = presupuestal($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, 'E', $niveles, $parametroExtra);
    }

    if ($gpo_id . $ur_id . $ue_id == '116005') {

        $contable = fondos42210($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra);
    } else {

        $contable = contable42210($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra);
    }
    $sumaContableEstatal = 0;
    $sumaContableFederal = 0;
    $sumaContableTotal = 0;
    $sumaFondosEstatal = 0;
    $sumaFondosFederal = 0;
    $sumaFondosTotal = 0;
    $sumaDiferencia = 0;
    $sumaPresupuesto = 0;
    $sumaDiferenciaUr = 0;
    $sumaDiferenciaFondos = 0;
    $sumaRectificado = 0;

    $matriz = array();
    $matriz['C'] = $contable;
    if ($reporte['tipo'] != 1) {
        $matriz['F'] = $fondos;
        $matriz['P'] = $presupuestal;
    }

    foreach ($matriz as $key => $value) {
        foreach ($value as $llave => $valor) {
            $datos[$llave]['id'] = $llave;
        }
    }
    asort($datos);

    foreach ($datos as $ind => $indval) {
        foreach ($matriz as $key => $value) {

            foreach ($value as $llave => $valor) {

                if (compararCadenas($ind . '.', $llave . '.') == 0) {
                    $datos[$ind]['key'] = $value[$llave]['id'];
                    $datos[$ind]['gpo_id'] = $value[$llave]['gpo_id'];
                    $datos[$ind]['ur_id'] = $value[$llave]['ur_id'];
                    $datos[$ind]['ur_clave'] = $value[$llave]['ur_clave'];
                    $datos[$ind]['ur_nombre'] = $value[$llave]['ur_nombre'];
                    if ($reporte['tipo'] == 1) {
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
                        $datos[$ind]['ecf_id'] = $value[$llave]['ecf_id'];
                        $datos[$ind]['cfi_clave'] = $value[$llave]['cfi_clave'];
                        $datos[$ind]['cfi_nombre'] = $value[$llave]['cfi_nombre'];
                        $datos[$ind]['cuenta'] = $value[$llave]['cuenta'];
                        $datos[$ind]['rfc_id'] = $value[$llave]['rfc_id'];
                        $datos[$ind]['nombre'] = $value[$llave]['nombre'];
                    }
                    switch ($key) {
                        case 'C':
                            if ($reporte['tipo'] == 1) {
                                $datos[$ind]['cmp_id'] = $value[$llave]['cmp_id'];
                                $datos[$ind]['afe_id'] = $value[$llave]['afe_id'];
                                $datos[$ind]['bdo_id'] = $value[$llave]['bdo_id'];
                                $datos[$ind]['clc_id'] = $value[$llave]['clc_id'];
                                $datos[$ind]['poliza'] = $value[$llave]['poliza'];
                            }
                            $datos[$ind]['estatal'] = redondear(intval($value[$llave]['estatal']), 2);
                            $datos[$ind]['federal'] = redondear(intval($value[$llave]['federal']), 2);
                            $datos[$ind]['total'] = redondear(intval($value[$llave]['total']), 2);
                            break;
                        case 'F':
                            $datos[$ind]['fondosEstatal'] = redondear(intval($value[$llave]['estatal']), 2);
                            $datos[$ind]['fondosFederal'] = redondear(intval($value[$llave]['federal']), 2);
                            $datos[$ind]['fondosTotal'] = redondear(intval($value[$llave]['total']), 2);
                            break;
                        case 'P':
                            $datos[$ind]['pre_importe'] = redondear(intval($value[$llave]['pre_importe']), 2);
                            $datos[$ind]['rec_importe'] = redondear(intval($value[$llave]['rec_importe']), 2);
                    } 
                } elseif (compararCadenas($ind . '.', $llave . '.') < 0) {
                }
            }
            if (compararCadenas($ind . '.', $llave . '.') == 0) {
                $x = array_shift($matriz[$key]);
            }
        }

        if (!isset($datos[$ind]['estatal'])) {
            $datos[$ind]['estatal'] = 0;
            $datos[$ind]['federal'] = 0;
            $datos[$ind]['total'] = 0;
        }
        if (!isset($datos[$ind]['fondosEstatal'])) {
            $datos[$ind]['fondosEstatal'] = 0;
            $datos[$ind]['fondosFederal'] = 0;
            $datos[$ind]['fondosTotal'] = 0;
        }
        if (!isset($datos[$ind]['fondosEstatal'])) {
            $datos[$ind]['fondosEstatal'] = 0;
            $datos[$ind]['fondosFederal'] = 0;
            $datos[$ind]['fondosTotal'] = 0;
        }
        if (!isset($datos[$ind]['rec_importe'])) {
            $datos[$ind]['rec_importe'] = 0;
        }
        if (isset($datos[$ind]['fondosTotal'], $datos[$ind]['total']) && is_numeric($datos[$ind]['fondosTotal']) && is_numeric($datos[$ind]['total'])) {
            $datos[$ind]['diferencia'] = $datos[$ind]['fondosTotal'] - $datos[$ind]['total'];
        }
        
        if (isset($datos[$ind]['pre_importe'], $datos[$ind]['total']) && is_numeric($datos[$ind]['pre_importe']) && is_numeric($datos[$ind]['total'])) {
            $datos[$ind]['diferenciaUr'] = $datos[$ind]['pre_importe'] - $datos[$ind]['total'];
        }
        
        if (isset($datos[$ind]['pre_importe'], $datos[$ind]['fondosTotal']) && is_numeric($datos[$ind]['pre_importe']) && is_numeric($datos[$ind]['fondosTotal'])) {
            $datos[$ind]['diferenciaFondos'] = $datos[$ind]['pre_importe'] - $datos[$ind]['fondosTotal'];
        }
        
        $sumaContableEstatal += $datos[$ind]['estatal'];
        $sumaContableFederal += $datos[$ind]['federal'];
        $sumaContableTotal += $datos[$ind]['total'];
        $sumaFondosEstatal += $datos[$ind]['fondosEstatal'];
        $sumaFondosFederal += $datos[$ind]['fondosFederal'];
        $sumaFondosTotal += $datos[$ind]['fondosTotal'];
        $sumaDiferencia += $datos[$ind]['diferencia'];
        $sumaPresupuesto += $datos[$ind]['pre_importe'] ?? 0;
        $sumaDiferenciaUr += $datos[$ind]['diferenciaUr'] ?? 0;
        $sumaDiferenciaFondos += $datos[$ind]['diferenciaFondos'] ?? 0;
        $sumaRectificado += $datos[$ind]['rec_importe'];
    }

    if ($reporte['tipo'] != 1 && $reporte['tipo'] != '') {
        if ($niveles[0] != 1) {
            $gpo_idA = 0;
            $ur_idA = 00;
            $ur_claveA = 000;
            $ur_nombreA = '';
            $ue_idA = 000;
            $sumaContableEstatalUr = 0;
            $sumaContableFederalUr = 0;
            $sumaContableTotalUr = 0;
            $sumaFondosEstatalUr = 0;
            $sumaFondosFederalUr = 0;
            $sumaFondosTotalUr = 0;
            $sumaDiferenciaUr = 0;
            $sumaRectificado = 0;
            foreach ($datos as $key => $value) {
                if ($ur_claveA < $datos[$key]['ur_clave']) {
                    if ($ur_claveA != 000) {
                        $dato[$ur_claveA . '.' . 'subtotal']['key'] = $ur_claveA . '.' . 'subtotal';
                        $dato[$ur_claveA . '.' . 'subtotal']['gpo_id'] = $gpo_idA;
                        $dato[$ur_claveA . '.' . 'subtotal']['ur_id'] = $ur_idA;
                        $dato[$ur_claveA . '.' . 'subtotal']['ur_clave'] = $ur_claveA;
                        $dato[$ur_claveA . '.' . 'subtotal']['ur_nombre'] = $ur_nombreA;
                        if ($reporte['tipo'] == 1) {
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
                            $dato[$ur_claveA . '.' . 'subtotal']['ecf_id'] = '';
                            $dato[$ur_claveA . '.' . 'subtotal']['cfi_clave'] = '';
                            $dato[$ur_claveA . '.' . 'subtotal']['cfi_nombre'] = '';
                            $dato[$ur_claveA . '.' . 'subtotal']['cuenta'] = 'subtotal';
                            $dato[$ur_claveA . '.' . 'subtotal']['rfc_id'] = '';
                            $dato[$ur_claveA . '.' . 'subtotal']['nombre'] = '';
                            $dato[$ur_claveA . '.' . 'subtotal']['cmp_id'] = '';
                            $dato[$ur_claveA . '.' . 'subtotal']['afe_id'] = '';
                            $dato[$ur_claveA . '.' . 'subtotal']['bdo_id'] = '';
                            $dato[$ur_claveA . '.' . 'subtotal']['clc_id'] = '';
                            $dato[$ur_claveA . '.' . 'subtotal']['poliza'] = '';
                        }
                        $dato[$ur_claveA . '.' . 'subtotal']['estatal'] = redondear($sumaContableEstatalUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['federal'] = redondear($sumaContableFederalUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['total'] = redondear($sumaContableTotalUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['fondosEstatal'] = redondear($sumaFondosEstatalUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['fondosFederal'] = redondear($sumaFondosFederalUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['fondosTotal'] = redondear($sumaFondosTotalUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['diferencia'] = redondear($sumaDiferenciaUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['rec_importe'] = redondear($sumaRectificado, 2);
                        $sumaContableEstatalUr = 0;
                        $sumaContableFederalUr = 0;
                        $sumaContableTotalUr = 0;
                        $sumaFondosEstatalUr = 0;
                        $sumaFondosFederalUr = 0;
                        $sumaFondosTotalUr = 0;
                        $sumaDiferenciaUr = 0;
                        $sumaRectificado = 0;

                    }

                    $gpo_idA = $datos[$key]['gpo_id'];
                    $ur_idA = $datos[$key]['ur_id'];
                    $ur_claveA = $datos[$key]['ur_clave'];
                    $ur_nombreA = $datos[$key]['ur_nombre'];
                    $ue_idA = $datos[$key]['ue_id'];
                }
                $dato[$key] = $datos[$key];

                $sumaContableEstatalUr += $datos[$key]['estatal'];
                $sumaContableFederalUr += $datos[$key]['federal'];
                $sumaContableTotalUr += $datos[$key]['total'];
                $sumaFondosEstatalUr += $datos[$key]['fondosEstatal'];
                $sumaFondosFederalUr += $datos[$key]['fondosFederal'];
                $sumaFondosTotalUr += $datos[$key]['fondosTotal'];
                $sumaDiferenciaUr += $datos[$key]['diferencia'];
                $sumaRectificado += $datos[$key]['rec_importe'];

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
            if ($reporte['tipo'] == 1) {
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
                $dato[$ur_claveE . '.' . 'subtotal']['ecf_id'] = '';
                $dato[$ur_claveE . '.' . 'subtotal']['cfi_clave'] = '';
                $dato[$ur_claveE . '.' . 'subtotal']['cfi_nombre'] = '';
                $dato[$ur_claveE . '.' . 'subtotal']['cuenta'] = 'subtotal';
                $dato[$ur_claveE . '.' . 'subtotal']['rfc_id'] = '';
                $dato[$ur_claveE . '.' . 'subtotal']['nombre'] = '';
                $dato[$ur_claveE . '.' . 'subtotal']['cmp_id'] = '';
                $dato[$ur_claveE . '.' . 'subtotal']['afe_id'] = '';
                $dato[$ur_claveE . '.' . 'subtotal']['bdo_id'] = '';
                $dato[$ur_claveE . '.' . 'subtotal']['clc_id'] = '';
                $dato[$ur_claveE . '.' . 'subtotal']['poliza'] = '';
            }
            $dato[$ur_claveE . '.' . 'subtotal']['estatal'] = redondear($sumaContableEstatalUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['federal'] = redondear($sumaContableFederalUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['total'] = redondear($sumaContableTotalUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['fondosEstatal'] = redondear($sumaFondosEstatalUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['fondosFederal'] = redondear($sumaFondosFederalUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['fondosTotal'] = redondear($sumaFondosTotalUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['diferencia'] = redondear($sumaDiferenciaUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['rec_importe'] = redondear($sumaRectificado, 2);

            $datos = $dato;
        }
    }

    $datos['total']['key'] = 'total';
    $datos['total']['gpo_id'] = '';
    $datos['total']['ur_id'] = '';
    $datos['total']['ur_clave'] = '';
    $datos['total']['ur_nombre'] = '';
    if ($reporte['tipo'] == 1) {
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
        $datos['total']['ecf_id'] = '';
        $datos['total']['cfi_clave'] = '';
        $datos['total']['cfi_nombre'] = '';
        $datos['total']['cuenta'] = 'TOTAL';
        $datos['total']['rfc_id'] = '';
        $datos['total']['nombre'] = '';
        $datos['total']['cmp_id'] = '';
        $datos['total']['afe_id'] = '';
        $datos['total']['bdo_id'] = '';
        $datos['total']['clc_id'] = '';
        $datos['total']['poliza'] = '';
    }
    $datos['total']['estatal'] = redondear($sumaContableEstatal, 2);
    $datos['total']['federal'] = redondear($sumaContableFederal, 2);
    $datos['total']['total'] = redondear($sumaContableTotal, 2);
    $datos['total']['fondosEstatal'] = redondear($sumaFondosEstatal, 2);
    $datos['total']['fondosFederal'] = redondear($sumaFondosFederal, 2);
    $datos['total']['fondosTotal'] = redondear($sumaFondosTotal, 2);
    $datos['total']['diferencia'] = redondear($sumaDiferencia, 2);
    $datos['total']['pre_importe'] = redondear($sumaPresupuesto, 2);
    $datos['total']['diferenciaUr'] = redondear($sumaDiferenciaUr, 2);
    $datos['total']['diferenciaFondos'] = redondear($sumaDiferenciaFondos, 2);
    $datos['total']['rec_importe'] = redondear($sumaRectificado, 2);

    return $datos;

}
