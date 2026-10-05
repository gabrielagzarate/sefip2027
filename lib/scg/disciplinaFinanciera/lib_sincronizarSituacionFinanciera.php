<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of lib_sincronizar
 *
 * @author Faustino Cruz, Israel <israel.faustino@finanzasoaxaca.gob.mx>
 * Created: 25-abr-2020
 * Comment:
 *
 * */

function lib_sincronizarSituacionFinanciera($trimestral, $anual){

    $datos = array_merge(array_fill_keys(array_keys($trimestral), ''), array_fill_keys(array_keys($anual), ''));

    ksort($datos);

    foreach($datos as $ind => $indval){
        $datos[$ind] = array();
        if(isset($trimestral[$ind])){
            $datos[$ind]['id'] = $trimestral[$ind]['id'];
            $datos[$ind]['ger_cuenta'] = $trimestral[$ind]['ger_cuenta'];
            $datos[$ind]['ger_nombre'] = $trimestral[$ind]['ger_nombre'];
            $datos[$ind]['cgp_cuenta'] = $trimestral[$ind]['cgp_cuenta'];
            $datos[$ind]['cgp_nombre'] = $trimestral[$ind]['cgp_nombre'];
            $datos[$ind]['rub_cuenta'] = $trimestral[$ind]['rub_cuenta'];
            $datos[$ind]['rub_nombre'] = $trimestral[$ind]['rub_nombre'];
            $datos[$ind]['cgcu_cuenta'] = $trimestral[$ind]['cgcu_cuenta'];
            $datos[$ind]['cgcu_nombre'] = $trimestral[$ind]['cgcu_nombre'];
            $datos[$ind]['cgsc_cuenta'] = $trimestral[$ind]['cgsc_cuenta'];
            $datos[$ind]['cgsc_nombre'] = $trimestral[$ind]['cgsc_nombre'];
            $datos[$ind]['cuenta'] = $trimestral[$ind]['cuenta'];
            $datos[$ind]['nombre'] = $trimestral[$ind]['nombre'];
            $datos[$ind]['nat_id'] = $trimestral[$ind]['nat_id'];
            $datos[$ind]['nivel'] = $trimestral[$ind]['nivel'];


            switch($trimestral[$ind]['nat_id']){

                case 'D':
                    $datos[$ind]['importeActual'] = redondear($trimestral[$ind]['debe'] - $trimestral[$ind]['haber'], 2);
                    break;
                case 'H':
                    $datos[$ind]['importeActual'] = redondear($trimestral[$ind]['haber'] - $trimestral[$ind]['debe'], 2);
                    break;
                default:
                    echo 'kym 2 ';
                    break;
            }
        }

        if(isset($anual[$ind])){
            $datos[$ind]['id'] = $anual[$ind]['id'];
            $datos[$ind]['ger_cuenta'] = $anual[$ind]['ger_cuenta'];
            $datos[$ind]['ger_nombre'] = $anual[$ind]['ger_nombre'];
            $datos[$ind]['cgp_cuenta'] = $anual[$ind]['cgp_cuenta'];
            $datos[$ind]['cgp_nombre'] = $anual[$ind]['cgp_nombre'];
            $datos[$ind]['rub_cuenta'] = $anual[$ind]['rub_cuenta'];
            $datos[$ind]['rub_nombre'] = $anual[$ind]['rub_nombre'];
            $datos[$ind]['cgcu_cuenta'] = $anual[$ind]['cgcu_cuenta'];
            $datos[$ind]['cgcu_nombre'] = $anual[$ind]['cgcu_nombre'];
            $datos[$ind]['cgsc_cuenta'] = $anual[$ind]['cgsc_cuenta'];
            $datos[$ind]['cgsc_nombre'] = $anual[$ind]['cgsc_nombre'];
            $datos[$ind]['cuenta'] = $anual[$ind]['cuenta'];
            $datos[$ind]['nombre'] = $anual[$ind]['nombre'];
            $datos[$ind]['nat_id'] = $anual[$ind]['nat_id'];
            $datos[$ind]['nivel'] = $anual[$ind]['nivel'];


            switch($anual[$ind]['nat_id']){

                case 'D':
                    $datos[$ind]['importeAnterior'] = redondear($anual[$ind]['debe'] - $anual[$ind]['haber'], 2);
                    break;
                case 'H':
                    $datos[$ind]['importeAnterior'] = redondear($anual[$ind]['haber'] - $anual[$ind]['debe'], 2);
                    break;
                default:
                    echo 'kym 2 ';
                    break;
            }
        }

        if(!isset($datos[$ind]['importeActual'])){
            $datos[$ind]['importeActual'] = 0;
        }
        if(!isset($datos[$ind]['importeAnterior'])){
            $datos[$ind]['importeAnterior'] = 0;
        }

    }

    return isset($datos) ? $datos : FALSE;
}
