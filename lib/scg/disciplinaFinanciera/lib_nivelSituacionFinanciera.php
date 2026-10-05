<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of nivelSituacionFinanciera
 *
 * @author Faustino Cruz, Israel <israel.faustino@finanzasoaxaca.gob.mx>
 * Created: 25-abr-2020
 * Comment:
 *
 * */
function lib_nivelSituacionFinanciera($datos, $nivel){

    if(isset($datos)){
        $datos = array_reverse($datos, true);

        switch($nivel){
            case 5:
                $cgcu_cuenta = '';
                $sumaCuenta2020 = 0;
                $sumaCuenta2022 = 0;
                $registros = array();
                $registro = '';

                foreach($datos as $key => $value){
                    $var = explode('.', $key);
                    $cuentaNivel = $var[0].'.'.$var[1].'.'.$var[2].'.'.$var[3].'.'.$var[4];
                    if($cuentaNivel != $cgcu_cuenta && $cgcu_cuenta != ''){
                        $registros[$cgcu_cuenta] = $registro;
                        $registros[$cgcu_cuenta]['importeAnterior'] = $sumaCuenta2020;
                        $registros[$cgcu_cuenta]['importeActual'] = $sumaCuenta2022;

                        $sumaCuenta2020 = 0;
                        $sumaCuenta2022 = 0;
                    }

                    $registro = $datos[$key];
                    $registro['cuenta'] = $datos[$key]['cgcu_cuenta'];
                    $registro['nombre'] = $datos[$key]['cgcu_nombre'];
                    $registro['nivel'] = 4;

                    if($datos[$key]['nivel'] == $nivel){
                        $sumaCuenta2020 = $sumaCuenta2020 + $datos[$key]['importeAnterior'];
                        $sumaCuenta2022 = $sumaCuenta2022 + $datos[$key]['importeActual'];
                    }

                    $registros[$key] = $datos[$key];

                    $cgcu_cuenta = 'C.'.$registro['cuenta'];
                }

                $registros[$cgcu_cuenta] = $registro;
                $registros[$cgcu_cuenta]['importeAnterior'] = $sumaCuenta2020;
                $registros[$cgcu_cuenta]['importeActual'] = $sumaCuenta2022;

                //guardamos el resultado en el arreglo original
                $datos = $registros;

            case 4:
                //iniciamos las variables
                $rub_cuenta = '';
                $sumaRubro2020 = 0;
                $sumaRubro2022 = 0;
                $registros = array();
                $registro = '';

                //generamos el tercer nivel
                foreach($datos as $key => $value){
                    $var = explode('.', $key);
                    $cuentaNivel = $var[0].'.'.$var[1].'.'.$var[2].'.'.$var[3];
                    if($cuentaNivel != $rub_cuenta && $rub_cuenta != ''){
                        $registros[$rub_cuenta] = $registro;
                        $registros[$rub_cuenta]['importeAnterior'] = $sumaRubro2020;
                        $registros[$rub_cuenta]['importeActual'] = $sumaRubro2022;

                        $sumaRubro2020 = 0;
                        $sumaRubro2022 = 0;
                    }

                    $registro = $datos[$key];
                    $registro['cuenta'] = $datos[$key]['rub_cuenta'];
                    $registro['nombre'] = $datos[$key]['rub_nombre'];
                    $registro['nivel'] = 3;

                    if($datos[$key]['nivel'] == $nivel){
                        $sumaRubro2020 = $sumaRubro2020 + $datos[$key]['importeAnterior'];
                        $sumaRubro2022 = $sumaRubro2022 + $datos[$key]['importeActual'];
                    }

                    $registros[$key] = $datos[$key];
                    $rub_cuenta = 'C.'.$registro['cuenta'];
                }

                $registros[$rub_cuenta] = $registro;
                $registros[$rub_cuenta]['importeAnterior'] = $sumaRubro2020;
                $registros[$rub_cuenta]['importeActual'] = $sumaRubro2022;

                //guardamos el resultado en el arreglo original
                $datos = $registros;

            case 3:
                //reiniciamos las variables
                $cgp_cuenta = '';
                $sumaGrupo2020 = 0;
                $sumaGrupo2022 = 0;
                $registros = array();
                $registro = '';

                //generamos el segundo nivel
                foreach($datos as $key => $value){
                    $var = explode('.', $key);
                    $cuentaNivel = $var[0].'.'.$var[1].'.'.$var[2];
                    if($cuentaNivel != $cgp_cuenta && $cgp_cuenta != ''){
                        $registros[$cgp_cuenta] = $registro;
                        $registros[$cgp_cuenta]['importeAnterior'] = $sumaGrupo2020;
                        $registros[$cgp_cuenta]['importeActual'] = $sumaGrupo2022;

                        $sumaGrupo2020 = 0;
                        $sumaGrupo2022 = 0;
                    }

                    $registro = $datos[$key];
                    $registro['cuenta'] = $datos[$key]['cgp_cuenta'];
                    $registro['nombre'] = $datos[$key]['cgp_nombre'];
                    $registro['nivel'] = 2;

                    if($datos[$key]['nivel'] == $nivel){
                        $sumaGrupo2020 = $sumaGrupo2020 + $datos[$key]['importeAnterior'];
                        $sumaGrupo2022 = $sumaGrupo2022 + $datos[$key]['importeActual'];
                    }

                    $registros[$key] = $datos[$key];

                    $cgp_cuenta = 'C.'.$registro['cuenta'];
                }

                $registros[$cgp_cuenta] = $registro;
                $registros[$cgp_cuenta]['importeAnterior'] = $sumaGrupo2020;
                $registros[$cgp_cuenta]['importeActual'] = $sumaGrupo2022;

                //guardamos el resultado en el arreglo original
                $datos = $registros;

            case 2:
                //reiniciamos las variables
                $ger_cuenta = '';
                $sumaGenero2020 = 0;
                $sumaGenero2022 = 0;
                $registros = array();
                $registro = '';

                //generamos el primer nivel
                foreach($datos as $key => $value){
                    $var = explode('.', $key);
                    $cuentaNivel = $var[0].'.'.$var[1];
                    if($cuentaNivel != $ger_cuenta && $ger_cuenta != ''){
                        $registros[$ger_cuenta] = $registro;
                        $registros[$ger_cuenta]['importeAnterior'] = $sumaGenero2020;
                        $registros[$ger_cuenta]['importeActual'] = $sumaGenero2022;

                        $sumaGenero2020 = 0;
                        $sumaGenero2022 = 0;
                    }

                    $registro = $datos[$key];
                    $registro['cuenta'] = $datos[$key]['ger_cuenta'];
                    $registro['nombre'] = $datos[$key]['ger_nombre'];
                    $registro['nivel'] = 1;

                    if($datos[$key]['nivel'] == $nivel){
                        $sumaGenero2020 = $sumaGenero2020 + $datos[$key]['importeAnterior'];
                        $sumaGenero2022 = $sumaGenero2022 + $datos[$key]['importeActual'];
                    }

                    $registros[$key] = $datos[$key];

                    $ger_cuenta = 'C.'.$registro['cuenta'];
                }

                $registros[$ger_cuenta] = $registro;
                $registros[$ger_cuenta]['importeAnterior'] = $sumaGenero2020;
                $registros[$ger_cuenta]['importeActual'] = $sumaGenero2022;

                //guardamos el resultado en el arreglo original
                $datos = $registros;

                break;
            case 1:
                // nothing;
                break;
            default:
                echo 'kym 4 ';
                break;
        }
    }

    $datos = array_reverse($datos, true);

    return isset($datos) ? $datos:FALSE;
}
