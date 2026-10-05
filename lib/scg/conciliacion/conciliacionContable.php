<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/contable.php';
require_once dirname(__FILE__) . '/presupuestal.php';
require_once dirname(__FILE__) . '/contableModificado.php';
require_once dirname(__FILE__) . '/../../compararCadenas.php';
require_once dirname(__FILE__) . '/../../redondear.php';

function conciliacionContable(
    $gpo_id,
    $ur_id,
    $ue_id,
    $pot_id,
    $periodo,
    $programatico,
    $claveFinanciamiento,
    $reporte,
    $detalle,
    $mmc_id,
    $niveles,
    $parametroExtra = array()
) {

    $modificado = contableModificado(
        $gpo_id,
        $ur_id,
        $ue_id,
        $pot_id,
        $periodo,
        $programatico,
        $claveFinanciamiento,
        $reporte,
        $detalle,
        'M',
        $niveles,
        $parametroExtra
    );

    $comprometido = contable(
        $gpo_id,
        $ur_id,
        $ue_id,
        $pot_id,
        $periodo,
        $programatico,
        $claveFinanciamiento,
        $reporte,
        $detalle,
        'C',
        $niveles,
        $parametroExtra
    );

    $devengado = contable(
        $gpo_id,
        $ur_id,
        $ue_id,
        $pot_id,
        $periodo,
        $programatico,
        $claveFinanciamiento,
        $reporte,
        $detalle,
        'D',
        $niveles,
        $parametroExtra
    );

    $ejercido = contable(
        $gpo_id,
        $ur_id,
        $ue_id,
        $pot_id,
        $periodo,
        $programatico,
        $claveFinanciamiento,
        $reporte,
        $detalle,
        'E',
        $niveles,
        $parametroExtra
    );

    $ministrado = contable(
        $gpo_id,
        $ur_id,
        $ue_id,
        $pot_id,
        $periodo,
        $programatico,
        $claveFinanciamiento,
        $reporte,
        $detalle,
        'T',
        $niveles,
        $parametroExtra
    );

    $pagado = contable(
        $gpo_id,
        $ur_id,
        $ue_id,
        $pot_id,
        $periodo,
        $programatico,
        $claveFinanciamiento,
        $reporte,
        $detalle,
        'P',
        $niveles,
        $parametroExtra
    );

    $rectificado = contable(
        $gpo_id,
        $ur_id,
        $ue_id,
        $pot_id,
        $periodo,
        $programatico,
        $claveFinanciamiento,
        $reporte,
        $detalle,
        'R',
        $niveles,
        $parametroExtra
    );

    $sumaReduccion = 0;
    $sumaAmpliacion = 0;
    $sumaComprometido = 0;
    $sumaDevengado = 0;
    $sumaEjercido = 0;
    $sumaMinistrado = 0;
    $sumaPagado = 0;
    $sumaRectificado = 0;

    $matriz = array();
    $matriz['M'] = $modificado;
    $matriz['C'] = $comprometido;
    $matriz['D'] = $devengado;
    $matriz['E'] = $ejercido;
    $matriz['T'] = $ministrado;
    $matriz['P'] = $pagado;
    $matriz['R'] = $rectificado;
    $datos = [];

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
                        case 'M':
                            $datos[$ind]['mod_clave'] = $value[$llave]['mod_clave'];
                            $datos[$ind]['mod_id'] = $value[$llave]['mod_id'];
                            $datos[$ind]['polizaModificado'] = $value[$llave]['poliza'];
                            $datos[$ind]['pdx_reduccion'] = $value[$llave]['pdx_reduccion'];
                            $datos[$ind]['pdx_ampliacion'] = $value[$llave]['pdx_ampliacion'];

                            $sumaReduccion += redondear($value[$llave]['pdx_reduccion'], 2);
                            $sumaAmpliacion += redondear($value[$llave]['pdx_ampliacion'], 2);
                            break;
                        case 'C':
                            $datos[$ind]['cmp_id'] = $value[$llave]['cmp_id'];
                            $datos[$ind]['polizaComprometido'] = $value[$llave]['poliza'];
                            $datos[$ind]['pdx_comprometido'] = $value[$llave]['pdx_importe'];

                            $sumaComprometido += redondear($value[$llave]['pdx_importe'], 2);
                            break;
                        case 'D':
                            $datos[$ind]['bdo_id'] = $value[$llave]['bdo_id'];
                            $datos[$ind]['afe_id'] = $value[$llave]['afe_id'];
                            $datos[$ind]['polizaDevengado'] = $value[$llave]['poliza'];
                            $datos[$ind]['pdx_devengado'] = $value[$llave]['pdx_importe'];

                            $sumaDevengado += redondear($value[$llave]['pdx_importe'], 2);
                            break;
                        case 'E':
                            $datos[$ind]['clc_id'] = $value[$llave]['clc_id'];
                            $datos[$ind]['polizaEjercido'] = $value[$llave]['poliza'];
                            $datos[$ind]['pdx_ejercido'] = $value[$llave]['pdx_importe'];

                            $sumaEjercido += redondear($value[$llave]['pdx_importe'], 2);
                            break;
                        case 'T':
                            $datos[$ind]['polizaMinistrado'] = $value[$llave]['poliza'];
                            $datos[$ind]['pdx_ministrado'] = $value[$llave]['pdx_importe'];

                            $sumaMinistrado += redondear($value[$llave]['pdx_importe'], 2);
                            break;
                        case 'P':
                            $datos[$ind]['polizaPagado'] = $value[$llave]['poliza'];
                            $datos[$ind]['pdx_pagado'] = $value[$llave]['pdx_importe'];

                            $sumaPagado += redondear($value[$llave]['pdx_importe'], 2);
                            break;
                        case 'R':
                            $datos[$ind]['rct_id'] = $value[$llave]['clc_id'];
                            $datos[$ind]['polizaRectificado'] = $value[$llave]['poliza'];
                            $datos[$ind]['pdx_rectificado'] = $value[$llave]['pdx_importe'];

                            $sumaRectificado += redondear($value[$llave]['pdx_importe'], 2);
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

        if (!isset($datos[$ind]['polizaModificado'])) {
            $datos[$ind]['mod_clave'] = '';
            $datos[$ind]['mod_id'] = '';
            $datos[$ind]['polizaModificado'] = '';
            $datos[$ind]['pdx_reduccion'] = 0;
            $datos[$ind]['pdx_ampliacion'] = 0;
        }
        if (!isset($datos[$ind]['polizaComprometido'])) {
            $datos[$ind]['cmp_id'] = '';
            $datos[$ind]['polizaComprometido'] = '';
            $datos[$ind]['pdx_comprometido'] = 0;
        }
        if (!isset($datos[$ind]['polizaDevengado'])) {
            $datos[$ind]['afe_id'] = '';
            $datos[$ind]['bdo_id'] = '';
            $datos[$ind]['polizaDevengado'] = '';
            $datos[$ind]['pdx_devengado'] = 0;
        }
        if (!isset($datos[$ind]['polizaEjercido'])) {
            $datos[$ind]['clc_id'] = '';
            $datos[$ind]['polizaEjercido'] = '';
            $datos[$ind]['pdx_ejercido'] = 0;
        }
        if (!isset($datos[$ind]['polizaMinistrado'])) {
            $datos[$ind]['polizaMinistrado'] = '';
            $datos[$ind]['pdx_ministrado'] = 0;
        }
        if (!isset($datos[$ind]['polizaPagado'])) {
            $datos[$ind]['polizaPagado'] = '';
            $datos[$ind]['pdx_pagado'] = 0;
        }
        if (!isset($datos[$ind]['polizaRectificado'])) {
            $datos[$ind]['rct_id'] = '';
            $datos[$ind]['polizaRectificado'] = '';
            $datos[$ind]['pdx_rectificado'] = 0;
        }
    }

    if ($reporte['tipo'] != 1 && $reporte['tipo'] != '') {
        if ($niveles[0] != 1) {
            $gpo_idA = 0;
            $ur_idA = 00;
            $ur_claveA = 000;
            $ur_nombreA = '';
            $ue_idA = 000;
            $sumaReduccionUr = 0;
            $sumaAmpliacionUr = 0;
            $sumaComprometidoUr = 0;
            $sumaDevengadoUr = 0;
            $sumaEjercidoUr = 0;
            $sumaMinistradoUr = 0;
            $sumaPagadoUr = 0;
            $sumaRectificadoUr = 0;
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
                        $dato[$ur_claveA . '.' . 'subtotal']['pdx_reduccion'] = redondear($sumaReduccionUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['pdx_ampliacion'] = redondear($sumaAmpliacionUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['pdx_comprometido'] = redondear($sumaComprometidoUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['pdx_devengado'] = redondear($sumaDevengadoUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['pdx_ejercido'] = redondear($sumaEjercidoUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['pdx_ministrado'] = redondear($sumaMinistradoUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['pdx_pagado'] = redondear($sumaPagadoUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['pdx_rectificado'] = redondear($sumaRectificadoUr, 2);
                        $dato[$ur_claveA . '.' . 'subtotal']['mod_clave'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['mod_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['polizaModificado'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['cmp_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['polizaComprometido'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['afe_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['bdo_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['polizaDevengado'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['clc_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['polizaEjercido'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['polizaMinistrado'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['polizaPagado'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['rct_id'] = '';
                        $dato[$ur_claveA . '.' . 'subtotal']['polizaRectificado'] = '';

                        $sumaReduccionUr = 0;
                        $sumaAmpliacionUr = 0;
                        $sumaComprometidoUr = 0;
                        $sumaDevengadoUr = 0;
                        $sumaEjercidoUr = 0;
                        $sumaMinistradoUr = 0;
                        $sumaPagadoUr = 0;
                        $sumaRectificadoUr = 0;
                    }

                    $gpo_idA = $datos[$key]['gpo_id'];
                    $ur_idA = $datos[$key]['ur_id'];
                    $ur_claveA = $datos[$key]['ur_clave'];
                    $ur_nombreA = $datos[$key]['ur_nombre'];
                    $ue_idA = $datos[$key]['ue_id'];
                }
                $dato[$key] = $datos[$key];

                $sumaReduccionUr += redondear($datos[$key]['pdx_reduccion'], 2);
                $sumaAmpliacionUr += redondear($datos[$key]['pdx_ampliacion'], 2);
                $sumaComprometidoUr += redondear($datos[$key]['pdx_comprometido'], 2);
                $sumaDevengadoUr += redondear($datos[$key]['pdx_devengado'], 2);
                $sumaEjercidoUr += redondear($datos[$key]['pdx_ejercido'], 2);
                $sumaMinistradoUr += redondear($datos[$key]['pdx_ministrado'], 2);
                $sumaPagadoUr += redondear($datos[$key]['pdx_pagado'], 2);
                $sumaRectificadoUr += redondear($datos[$key]['pdx_rectificado'], 2);

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
            $dato[$ur_claveE . '.' . 'subtotal']['pdx_reduccion'] = redondear($sumaReduccionUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['pdx_ampliacion'] = redondear($sumaAmpliacionUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['pdx_comprometido'] = redondear($sumaComprometidoUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['pdx_devengado'] = redondear($sumaDevengadoUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['pdx_ejercido'] = redondear($sumaEjercidoUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['pdx_ministrado'] = redondear($sumaMinistradoUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['pdx_pagado'] = redondear($sumaPagadoUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['pdx_rectificado'] = redondear($sumaRectificadoUr, 2);
            $dato[$ur_claveE . '.' . 'subtotal']['mod_clave'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['mod_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['polizaModificado'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['cmp_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['polizaComprometido'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['afe_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['bdo_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['polizaDevengado'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['clc_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['polizaEjercido'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['polizaMinistrado'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['polizaPagado'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['rct_id'] = '';
            $dato[$ur_claveE . '.' . 'subtotal']['polizaRectificado'] = '';

            $datos = $dato;
        }
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
    $datos['total']['pdx_reduccion'] = redondear($sumaReduccion, 2);
    $datos['total']['pdx_ampliacion'] = redondear($sumaAmpliacion, 2);
    $datos['total']['pdx_comprometido'] = redondear($sumaComprometido, 2);
    $datos['total']['pdx_devengado'] = redondear($sumaDevengado, 2);
    $datos['total']['pdx_ejercido'] = redondear($sumaEjercido, 2);
    $datos['total']['pdx_ministrado'] = redondear($sumaMinistrado, 2);
    $datos['total']['pdx_pagado'] = redondear($sumaPagado, 2);
    $datos['total']['pdx_rectificado'] = redondear($sumaRectificado, 2);
    $datos['total']['mod_clave'] = '';
    $datos['total']['mod_id'] = '';
    $datos['total']['polizaModificado'] = '';
    $datos['total']['cmp_id'] = '';
    $datos['total']['polizaComprometido'] = '';
    $datos['total']['afe_id'] = '';
    $datos['total']['bdo_id'] = '';
    $datos['total']['polizaDevengado'] = '';
    $datos['total']['clc_id'] = '';
    $datos['total']['polizaEjercido'] = '';
    $datos['total']['polizaMinistrado'] = '';
    $datos['total']['polizaPagado'] = '';
    $datos['total']['rct_id'] = '';
    $datos['total']['polizaRectificado'] = '';

    return $datos;
}
