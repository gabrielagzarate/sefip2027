<?php
require_once dirname(__FILE__) . '/sql_situacionFinanciera.php';
require_once dirname(__FILE__) . '/lib_sincronizarSituacionFinanciera.php';
require_once dirname(__FILE__) . '/lib_nivelSituacionFinanciera.php';
require_once dirname(__FILE__) . '/../../calculaPeso.php';

function lib_situacionFinanciera(
    $ent_id,
    $msc_trimestre,
    $consolidado = false,
    $ent_idC = '',
    $sec_idC = '',
    $gpo_idC = '',
    $con_idC = '',
    $noAplica = false
) {
    ############ obtenemos la información de la base de datos
    ///obtiene la informacion de Activo Circulante
    $activoTrimestral21 = sql_situacionFinanciera(

        $ent_id, $msc_trimestre,
        2022, $consolidado,
        $ent_idC, $sec_idC,
        $gpo_idC, $con_idC,
        $noAplica, array("ger_id" => 1, "nivel" => 4)
    );
    //echo "T9<pre>";print_r($activoTrimestral9);echo "</pre>";//die();
    
    $activoAnual20 = sql_situacionFinanciera(
        $ent_id, 4,
        2020, $consolidado,
        $ent_idC, $sec_idC,
        $gpo_idC, $con_idC,
        $noAplica, array("ger_id" => 1, "nivel" => 4)

    );
    //echo "A8<pre>";print_r($activoAnual8);echo "</pre>";die();

    ///obtiene la informacion de Pasivo  Circulante
    $pasivoTrimestral21 = sql_situacionFinanciera(

        $ent_id, $msc_trimestre,
        2022, $consolidado,
        $ent_idC, $sec_idC,
        $gpo_idC, $con_idC,
        $noAplica, array("ger_id" => 2, "nivel" => 4)
    );
    //echo "M9<pre>";print_r($pasivoTrimestral9);echo "</pre>";die();
    $pasivoAnual20 = sql_situacionFinanciera(
        $ent_id, 4,
        2020, $consolidado,
        $ent_idC, $sec_idC,
        $gpo_idC, $con_idC,
        $noAplica, array("ger_id" => 2, "nivel" => 4)

    );
    //echo "A8<pre>";print_r($pasivoAnual8);echo "</pre>";die();

    ///obtiene la informacion de Hacienda Publica/Patrimonial
    $haciendaTrimestral21 = sql_situacionFinanciera(

        $ent_id, $msc_trimestre,
        2022, $consolidado,
        $ent_idC, $sec_idC,
        $gpo_idC, $con_idC,
        $noAplica, array("ger_id" => 3, "nivel" => 3)
    );
    //echo "T9H<pre>";print_r($haciendaTrimestral9);echo "</pre>";die();
    
    $haciendaAnual20 = sql_situacionFinanciera(
        $ent_id, 4,
        2020, $consolidado,
        $ent_idC, $sec_idC,
        $gpo_idC, $con_idC,
        $noAplica, array("ger_id" => 3, "nivel" => 3)

    );
    //echo "A8H<pre>";print_r($haciendaAnual8);echo "</pre>";die();

    // obtenermos las cuentas de ingresos
    $transferenciaTrimestral21 = sql_situacionFinanciera(

        $ent_id, $msc_trimestre,
        2022, $consolidado,
        $ent_idC, $sec_idC,
        $gpo_idC, $con_idC,
        $noAplica, array("ger_id" => 4, "nivel" => 1)
    );
    //echo "T9I<pre>";print_r($transferenciaTrimestral9);echo "</pre>";die();
    
    $transferenciaAnual20 = sql_situacionFinanciera(
        $ent_id, 4,
        2020, $consolidado,
        $ent_idC, $sec_idC,
        $gpo_idC, $con_idC,
        $noAplica, array("ger_id" => 4, "nivel" => 1)

    );
    //echo "A8I<pre>";print_r($transferenciaAnual8);echo "</pre>";die();

    // obtenermos las cuentas de gastos
    $gastoTrimestral21 = sql_situacionFinanciera(

        $ent_id, $msc_trimestre,
        2022, $consolidado,
        $ent_idC, $sec_idC,
        $gpo_idC, $con_idC,
        $noAplica, array("ger_id" => 5, "nivel" => 1)
    );
    //echo "T9G<pre>";print_r($gastoTrimestral9);echo "</pre>";die();
    
    $gastoAnual20 = sql_situacionFinanciera(
        $ent_id, 4,
        2020, $consolidado,
        $ent_idC, $sec_idC,
        $gpo_idC, $con_idC,
        $noAplica, array("ger_id" => 5, "nivel" => 1)

    );
    //echo "A8G<pre>";print_r($gastoAnual8);echo "</pre>";die();

    /*$NUMREG=count($arrayIzquierda);
    $peso = calcularPeso($NUMREG,20);
    //ECHO $peso;
    //DIE();
    ini_set('memory_limit', $peso."M"); */

    ######### generamos 
    $arrayActivo = lib_sincronizarSituacionFinanciera($activoTrimestral21, $activoAnual20);
    //echo 'A-<pre>';print_r($arrayActivo);echo '</pre>';die();
    $arrayPasivo = lib_sincronizarSituacionFinanciera($pasivoTrimestral21, $pasivoAnual20);
    //echo 'P-<pre>';print_r($arrayPasivo);echo '</pre>';die();
    $arrayHacienda = lib_sincronizarSituacionFinanciera($haciendaTrimestral21, $haciendaAnual20);
    //echo 'H-<pre>';print_r($arrayHacienda);echo '</pre>';die();
    $arrayTransferencia = lib_sincronizarSituacionFinanciera($transferenciaTrimestral21, $transferenciaAnual20);
    //echo 'T-<pre>';print_r($arrayTransferencia);echo '</pre>';die();

    $arrayGasto = lib_sincronizarSituacionFinanciera($gastoTrimestral20, $gastoAnual20);

    //echo 'G-<pre>';print_r($arrayGasto);echo '</pre>';die();

    $arrayIzquierda = lib_nivelSituacionFinanciera($arrayActivo, 4);
    //echo 'A-<pre>';print_r($arrayIzquierda);echo '</pre>';die();
    //$arrayDerecha = lib_nivelSituacionFinanciera($arrayPasivo, 4) + lib_nivelSituacionFinanciera($arrayHacienda, 3);
    //$arrayDerecha = lib_nivelSituacionFinanciera($arrayPasivo, 4) + lib_nivelSituacionFinanciera($arrayHacienda, 3) + lib_nivelSituacionFinanciera($arrayTransferencia, 1);
    //$arrayDerecha = lib_nivelSituacionFinanciera($arrayPasivo, 4) + lib_nivelSituacionFinanciera($arrayHacienda, 3) + lib_nivelSituacionFinanciera($arrayTransferencia, 1) + lib_nivelSituacionFinanciera($arrayGasto, 1);

    //echo 'x<pre>';print_r($arrayGasto['C.1'].['importe2020']);echo '</pre>';die();

    $importe2020 = $arrayTransferencia['C.4']['importe2020'] - $arrayGasto['C.5']['importe2020'];
    $importe2022 = $arrayTransferencia['C.4']['importe2022'] - $arrayGasto['C.5']['importe2022'];
    $saldo['C.9']['cuenta'] = '';
    $saldo['C.9']['nombre'] = 'RESULTADO DEL EJERCICIO';
    $saldo['C.9']['importe2020'] = $importe2020;
    $saldo['C.9']['importe2022'] = $importe2022;
    $saldo['C.9']['nivel'] = 1;
    $saldo['C.9']['nat_id'] = 'D';
    $arrayDerecha = lib_nivelSituacionFinanciera($arrayPasivo, 4) + lib_nivelSituacionFinanciera($arrayHacienda, 3) + $saldo; //*/
    //echo 'A-<pre>';print_r($arrayDerecha);echo '</pre>';die();

    $ind = 0;

    foreach($arrayIzquierda as $key => $value){
        $datosIzquierda['C.'.$ind]['cuentaIzquierda'] = $arrayIzquierda[$key]['cuenta'];
        $datosIzquierda['C.'.$ind]['nombreIzquierda'] = $arrayIzquierda[$key]['nombre'];
        $datosIzquierda['C.'.$ind]['importe2022Izquierda'] = $arrayIzquierda[$key]['importe2022'];
        $datosIzquierda['C.'.$ind]['importe2020Izquierda'] = $arrayIzquierda[$key]['importe2020'];
        $datosIzquierda['C.'.$ind]['nivelIzquierda'] = $arrayIzquierda[$key]['nivel'];
        

        $ind++;
    }

    $ind = 0;
    foreach ($arrayDerecha as $key => $value) {
        //$datosDerecha[$ind] = $arrayDerecha[$key];

        $datosDerecha['C.'.$ind]['cuentaDerecha'] = $arrayDerecha[$key]['cuenta'];
        $datosDerecha['C.'.$ind]['nombreDerecha'] = $arrayDerecha[$key]['nombre'];
        $datosDerecha['C.'.$ind]['importe2022Derecha'] = $arrayDerecha[$key]['importe2022'];
        $datosDerecha['C.'.$ind]['importe2020Derecha'] = $arrayDerecha[$key]['importe2020'];
        $datosDerecha['C.'.$ind]['nivelDerecha'] = $arrayDerecha[$key]['nivel'];
        

        $ind++;
    }
    //$datos = array_merge($datosIzquierda, $datosDerecha);
    //$datos = $datosIzquierda + $datosDerecha;
    //*/
    //echo '<pre>';print_r($datosIzquierda);echo '</pre>';die();
    $datos = array_merge(array_fill_keys(array_keys($datosIzquierda), ''), array_fill_keys(array_keys($datosDerecha), ''));

    //echo '<pre>';print_r($datos);echo '</pre>';die();

    $totalImporte2022Izquierda = 0;
    $totalImporte2020Izquierda = 0;
    $totalImporte2022Derecha = 0;
    $totalImporte2020Derecha = 0;

    foreach($datos as $ind => $indval){

        //echo '<pre>';print_r($datosIzquierda[$ind]);echo '</pre>';die();
        $datos[$ind] = array();
        if (isset($datosIzquierda[$ind])) {
            $datos[$ind] = $datosIzquierda[$ind];

            
            if($datos[$ind]['nivelIzquierda'] == 1){

                $totalImporte2022Izquierda = $totalImporte2022Izquierda + $datosIzquierda[$ind]['importe2022Izquierda'];
                $totalImporte2020Izquierda = $totalImporte2020Izquierda + $datosIzquierda[$ind]['importe2020Izquierda'];
            }
        } else {
            $datos[$ind]['cuentaIzquierda'] = '';
            $datos[$ind]['nombreIzquierda'] = '';
            $datos[$ind]['importe2022Izquierda'] = '';
            $datos[$ind]['importe2020Izquierda'] = '';
            $datos[$ind]['nivelIzquierda'] = '';
        }
        if (isset($datosDerecha[$ind])) {
            $datos[$ind] = $datos[$ind] + $datosDerecha[$ind];

            
            if($datos[$ind]['nivelDerecha'] == 1){

                $totalImporte2022Derecha = $totalImporte2022Derecha + $datosDerecha[$ind]['importe2022Derecha'];
                $totalImporte2020Derecha = $totalImporte2020Derecha + $datosDerecha[$ind]['importe2020Derecha'];
            }
        } else {
            $datos[$ind]['cuentaDerecha'] = '';
            $datos[$ind]['nombreDerecha'] = '';
            $datos[$ind]['importe2022Derecha'] = '';
            $datos[$ind]['importe2020Derecha'] = '';
            $datos[$ind]['nivelDerecha'] = '';
        }
    }

    $datos['total']['id'] = 'total';
    $datos['total']['cuentaIzquierda'] = '';
    $datos['total']['nombreIzquierda'] = 'TOTAL';
    $datos['total']['importe2022Izquierda'] = redondear($totalImporte2022Izquierda, 2);
    $datos['total']['importe2020Izquierda'] = redondear($totalImporte2020Izquierda, 2);
    $datos['total']['nivelIzquierda'] = '';
    $datos['total']['cuentaDerecha'] = '';
    $datos['total']['nombreDerecha'] = '';
    $datos['total']['importe2022Derecha'] = redondear($totalImporte2022Derecha, 2);
    $datos['total']['importe2020Derecha'] = redondear($totalImporte2020Derecha, 2);
    $datos['total']['nivelDerecha'] = '';

    //echo '<pre>';print_r($datos);echo '</pre>';die();

    return isset($datos) ? $datos : FALSE;
    /*
     * al final se retorna un array que contiene toda la informacion de cada nivel solicitado
     * esto con la finalidad de que cualquier reporte utilice un unico proceso y que no se involucren en la dificultad del proceso
     * consultas,
     * la mejor opcion fue solicitar todos los registros que cumplieran las condiciones de busqueda, agrupando a nivel de registro
     * y en la consulta externa, agrupar por el nivel mas alto solicitado y realizar los join
     * php
     * el segundo paso consiste en mezclar los registros obtenidos de las dos consultas(iniciales y del periodo)
     * y el tercer paso en agrupar los montos de acuerdo a los niveles solicitados
     * se separaron estos procesos ya que se disparaba la cantidad de condicionantes al proceso por cada nivel
     * a si vez que resulto mas sencillo y sobre todo rapido, y se tiene mas claro cada parte del proceso
     * faltante
     */
}
