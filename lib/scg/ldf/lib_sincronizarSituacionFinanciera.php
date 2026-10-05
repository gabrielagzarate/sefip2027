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

function lib_sincronizarSituacionFinanciera($trimestral2022, $anual2020){

    //put your code here
    //echo 'at9-<pre>';print_r($trimestral2020);echo '</pre>';//die();
    //echo 'aa8-<pre>';print_r($anual2018);echo '</pre>';die();
    $datos = array_merge(array_fill_keys(array_keys($trimestral2022), ''), array_fill_keys(array_keys($anual2020), ''));
    //echo 'i-<pre>';print_r($datos);echo '</pre>';die();

    ksort($datos);
    //echo 'k-<pre>';print_r($datos);echo '</pre>';die();


    foreach($datos as $ind => $indval){
        //echo 'k-<pre>';print_r($trimestral2020[$ind]);echo '</pre>';die();
        if(isset($trimestral2022[$ind])){
            $datos[$ind] = array();
            $datos[$ind]['id'] = $trimestral2022[$ind]['id'];
            //echo "</br>ai->".$datos[$ind]['id']."---at->".$trimestral2020[$ind]['id'];die();

            $datos[$ind]['ger_cuenta'] = $trimestral2022[$ind]['ger_cuenta'];
            $datos[$ind]['ger_nombre'] = $trimestral2022[$ind]['ger_nombre'];
            $datos[$ind]['cgp_cuenta'] = $trimestral2022[$ind]['cgp_cuenta'];
            $datos[$ind]['cgp_nombre'] = $trimestral2022[$ind]['cgp_nombre'];
            $datos[$ind]['rub_cuenta'] = $trimestral2022[$ind]['rub_cuenta'];
            $datos[$ind]['rub_nombre'] = $trimestral2022[$ind]['rub_nombre'];
            $datos[$ind]['cgcu_cuenta'] = $trimestral2022[$ind]['cgcu_cuenta'];
            $datos[$ind]['cgcu_nombre'] = $trimestral2022[$ind]['cgcu_nombre'];
            $datos[$ind]['cgsc_cuenta'] = $trimestral2022[$ind]['cgsc_cuenta'];
            $datos[$ind]['cgsc_nombre'] = $trimestral2022[$ind]['cgsc_nombre'];
            $datos[$ind]['cuenta'] = $trimestral2022[$ind]['cuenta'];
            $datos[$ind]['nombre'] = $trimestral2022[$ind]['nombre'];
            $datos[$ind]['nat_id'] = $trimestral2022[$ind]['nat_id'];
            $datos[$ind]['nivel'] = $trimestral2022[$ind]['nivel'];


            switch($trimestral2022[$ind]['nat_id']){

                case 'D':
                    $datos[$ind]['importe2022'] = redondear($trimestral2022[$ind]['debe'] - $trimestral2022[$ind]['haber'], 2);
                    break;
                case 'H':
                    $datos[$ind]['importe2022'] = redondear($trimestral2022[$ind]['haber'] - $trimestral2022[$ind]['debe'], 2);
                    break;
                default:
                    echo 'kym 2 ';
                    break;
            }
            //echo 'k-<pre>';print_r($datos);echo '</pre>';//die();
        }

        //echo "<pre>";print_r($datos[$ind]);echo "</pre>";die();

        if(isset($anual2020[$ind])){

            $datos[$ind]['id'] = $anual2020[$ind]['id'];
            $datos[$ind]['ger_cuenta'] = $anual2020[$ind]['ger_cuenta'];
            $datos[$ind]['ger_nombre'] = $anual2020[$ind]['ger_nombre'];
            $datos[$ind]['cgp_cuenta'] = $anual2020[$ind]['cgp_cuenta'];
            $datos[$ind]['cgp_nombre'] = $anual2020[$ind]['cgp_nombre'];
            $datos[$ind]['rub_cuenta'] = $anual2020[$ind]['rub_cuenta'];
            $datos[$ind]['rub_nombre'] = $anual2020[$ind]['rub_nombre'];
            $datos[$ind]['cgcu_cuenta'] = $anual2020[$ind]['cgcu_cuenta'];
            $datos[$ind]['cgcu_nombre'] = $anual2020[$ind]['cgcu_nombre'];
            $datos[$ind]['cgsc_cuenta'] = $anual2020[$ind]['cgsc_cuenta'];
            $datos[$ind]['cgsc_nombre'] = $anual2020[$ind]['cgsc_nombre'];
            $datos[$ind]['cuenta'] = $anual2020[$ind]['cuenta'];
            $datos[$ind]['nombre'] = $anual2020[$ind]['nombre'];
            $datos[$ind]['nat_id'] = $anual2020[$ind]['nat_id'];
            $datos[$ind]['nivel'] = $anual2020[$ind]['nivel'];


            switch($anual2020[$ind]['nat_id']){

                case 'D':
                    $datos[$ind]['importe2020'] = redondear($anual2020[$ind]['debe'] - $anual2020[$ind]['haber'], 2);
                    break;
                case 'H':
                    $datos[$ind]['importe2020'] = redondear($anual2020[$ind]['haber'] - $anual2020[$ind]['debe'], 2);
                    break;
                default:
                    echo 'kym 2 ';
                    break;
            }
        }

        if(!isset($datos[$ind]['importe2022'])){
            $datos[$ind]['importe2022'] = 0;
        }
        if(!isset($datos[$ind]['importe2020'])){
            $datos[$ind]['importe2020'] = 0;
        }

    }

    return isset($datos) ? $datos : FALSE;
}
