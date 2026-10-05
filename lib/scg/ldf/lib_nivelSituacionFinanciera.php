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
    //put your code here
    //echo '<pre>';print_r($datos);echo '</pre>';die();

    if(isset($datos)){
        //if( $niveles[0] != 1 )
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
                    //echo "3<pre>";print_r($datos[$key]);echo "</pre></br>";//die();// */
                    $cuentaNivel = $var[0].'.'.$var[1].'.'.$var[2].'.'.$var[3].'.'.$var[4];
                    if($cuentaNivel != $cgcu_cuenta && $cgcu_cuenta != ''){
                        $registros[$cgcu_cuenta] = $registro;
                        $registros[$cgcu_cuenta]['importe2020'] = $sumaCuenta2020;
                        $registros[$cgcu_cuenta]['importe2022'] = $sumaCuenta2022;

                        //echo "R<pre>";print_r($registros);echo "</pre></br>";//die();// */
                        $sumaCuenta2020 = 0;
                        $sumaCuenta2022 = 0;
                    }

                    $registro = $datos[$key];
                    //$cuenta = $datos[$key]['ger_id'].'.'.$datos[$key]['cgp_id'].'.'.$datos[$key]['rub_id'];
                    //$registro['id'] = 'C'.$cuenta;
                    $registro['cuenta'] = $datos[$key]['cgcu_cuenta'];
                    //$registro['cuenta'] = $cuenta;
                    $registro['nombre'] = $datos[$key]['cgcu_nombre'];
                    $registro['nivel'] = 4;

                    if($datos[$key]['nivel'] == $nivel){
                        $sumaCuenta2020 = $sumaCuenta2020 + $datos[$key]['importe2020'];
                        $sumaCuenta2022 = $sumaCuenta2022 + $datos[$key]['importe2022'];

                        //echo "s<pre>".$sumaRubro2018.'-'.$sumaRubro2020."</pre></br>";//die();// */

                    }

                    $registros[$key] = $datos[$key];

                    #$rub_cuenta = substr( $key, 0, 5 );
                    $cgcu_cuenta = 'C.'.$registro['cuenta'];
                    //$rub_cuenta = $registro['cta_gpo_id'].$registro['cta_ur_id'].'.'.$registro['cuenta'];
                }

                $registros[$cgcu_cuenta] = $registro;
                $registros[$cgcu_cuenta]['importe2020'] = $sumaCuenta2020;
                $registros[$cgcu_cuenta]['importe2022'] = $sumaCuenta2022;

                //guardamos el resultado en el arreglo original
                $datos = $registros;

                //echo 'N3<pre>';print_r($datos);echo '</pre>';die();
                //break;

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
                    //echo "3<pre>";print_r($datos[$key]);echo "</pre></br>";//die();// */
                    $cuentaNivel = $var[0].'.'.$var[1].'.'.$var[2].'.'.$var[3];
                    if($cuentaNivel != $rub_cuenta && $rub_cuenta != ''){
                        $registros[$rub_cuenta] = $registro;
                        $registros[$rub_cuenta]['importe2020'] = $sumaRubro2020;
                        $registros[$rub_cuenta]['importe2022'] = $sumaRubro2022;

                        //echo "R<pre>";print_r($registros);echo "</pre></br>";//die();// */
                        $sumaRubro2020 = 0;
                        $sumaRubro2022 = 0;
                    }

                    $registro = $datos[$key];
                    //$cuenta = $datos[$key]['ger_id'].'.'.$datos[$key]['cgp_id'].'.'.$datos[$key]['rub_id'];
                    //$registro['id'] = 'C'.$cuenta;
                    $registro['cuenta'] = $datos[$key]['rub_cuenta'];
                    //$registro['cuenta'] = $cuenta;
                    $registro['nombre'] = $datos[$key]['rub_nombre'];
                    $registro['nivel'] = 3;

                    if($datos[$key]['nivel'] == $nivel){
                        $sumaRubro2020 = $sumaRubro2020 + $datos[$key]['importe2020'];
                        $sumaRubro2022 = $sumaRubro2022 + $datos[$key]['importe2022'];
                        //echo "s<pre>".$sumaRubro2018.'-'.$sumaRubro2020."</pre></br>";//die();// */
                    }

                    $registros[$key] = $datos[$key];

                    #$rub_cuenta = substr( $key, 0, 5 );
                    $rub_cuenta = 'C.'.$registro['cuenta'];
                    //$rub_cuenta = $registro['cta_gpo_id'].$registro['cta_ur_id'].'.'.$registro['cuenta'];
                }

                $registros[$rub_cuenta] = $registro;
                $registros[$rub_cuenta]['importe2020'] = $sumaRubro2020;
                $registros[$rub_cuenta]['importe2022'] = $sumaRubro2022;

                //guardamos el resultado en el arreglo original
                $datos = $registros;

                //echo 'N3<pre>';print_r($datos);echo '</pre>';die();
                //break;
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
                    //echo '<pre>';print_r($var);echo '</pre></br>';
                    //echo $var[1].'.'.$var[1].'-'.$cgp_cuenta.'</br>';
                    /* echo "2-<pre>";
                      print_r($var);
                      echo "</pre></br>";// */
                    $cuentaNivel = $var[0].'.'.$var[1].'.'.$var[2];
                    //echo $cuentaNivel.'-'.$cgp_cuenta.'</br>';
                    if($cuentaNivel != $cgp_cuenta && $cgp_cuenta != ''){
                        $registros[$cgp_cuenta] = $registro;
                        $registros[$cgp_cuenta]['importe2020'] = $sumaGrupo2020;
                        $registros[$cgp_cuenta]['importe2022'] = $sumaGrupo2022;

                        $sumaGrupo2020 = 0;
                        $sumaGrupo2022 = 0;
                    }

                    $registro = $datos[$key];
                    //$cuenta = $datos[$key]['ger_id'].'.'.$datos[$key]['cgp_id'];
                    //$registro['id'] = 'C'.$cuenta;
                    $registro['cuenta'] = $datos[$key]['cgp_cuenta'];
                    //$registro['cuenta'] = $cuenta;
                    $registro['nombre'] = $datos[$key]['cgp_nombre'];
                    $registro['nivel'] = 2;

                    if($datos[$key]['nivel'] == $nivel){
                        $sumaGrupo2020 = $sumaGrupo2020 + $datos[$key]['importe2020'];
                        $sumaGrupo2022 = $sumaGrupo2022 + $datos[$key]['importe2022'];
                    }

                    $registros[$key] = $datos[$key];

                    #$cgp_cuenta = substr( $key, 0, 3 );
                    $cgp_cuenta = 'C.'.$registro['cuenta'];
                    //$cgp_cuenta = $registro['cta_gpo_id'].$registro['cta_ur_id'].'.'.$registro['cuenta'];
                }

                $registros[$cgp_cuenta] = $registro;
                $registros[$cgp_cuenta]['importe2020'] = $sumaGrupo2020;
                $registros[$cgp_cuenta]['importe2022'] = $sumaGrupo2022;

                //guardamos el resultado en el arreglo original
                $datos = $registros;

                //echo 'N2<pre>';print_r($datos);echo '</pre>';die();
                //break;
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
                    /* echo "1-<pre>";
                      print_r($var);
                      echo "</pre></br>";die();// */
                    $cuentaNivel = $var[0].'.'.$var[1];
                    //$cuentaNivel = ( $agrupador == 1 ? $var[0].'.':'').$var[1];
                    //echo $cuentaNivel.'-'.$ger_cuenta.'</br>';
                    if($cuentaNivel != $ger_cuenta && $ger_cuenta != ''){
                        $registros[$ger_cuenta] = $registro;
                        $registros[$ger_cuenta]['importe2020'] = $sumaGenero2020;
                        $registros[$ger_cuenta]['importe2022'] = $sumaGenero2022;

                        $sumaGenero2020 = 0;
                        $sumaGenero2022 = 0;
                    }

                    $registro = $datos[$key];
                    //$cuenta = $datos[$key]['ger_id'];
                    //$registro['id'] = 'C'.$cuenta;
                    $registro['cuenta'] = $datos[$key]['ger_cuenta'];
                    //$registro['cuenta'] = $cuenta;
                    $registro['nombre'] = $datos[$key]['ger_nombre'];
                    $registro['nivel'] = 1;

                    if($datos[$key]['nivel'] == $nivel){
                        $sumaGenero2020 = $sumaGenero2020 + $datos[$key]['importe2020'];
                        $sumaGenero2022 = $sumaGenero2022 + $datos[$key]['importe2022'];
                    }

                    $registros[$key] = $datos[$key];

                    #$ger_cuenta = substr( $key, 0, 1 );
                    $ger_cuenta = 'C.'.$registro['cuenta'];
                    //$ger_cuenta = $registro['cta_gpo_id'].$registro['cta_ur_id'].'.'.$registro['cuenta'];
                }

                $registros[$ger_cuenta] = $registro;
                $registros[$ger_cuenta]['importe2020'] = $sumaGenero2020;
                $registros[$ger_cuenta]['importe2022'] = $sumaGenero2022;

                //guardamos el resultado en el arreglo original
                $datos = $registros;

                //echo 'N1<pre>';print_r($datos);echo '</pre>';die();
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
