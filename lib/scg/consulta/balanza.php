<?php
require_once dirname(__FILE__) . '/importeSql.php'; //2026
require_once dirname(__FILE__) . '/importeSql2025.php';
require_once dirname(__FILE__) . '/importeSql2024.php';
require_once dirname(__FILE__) . '/importeSql2023.php';
require_once dirname(__FILE__) . '/importeSql2022.php';
require_once dirname(__FILE__) . '/importeSql2021.php';
require_once dirname(__FILE__) . '/importeSql2020.php';
require_once dirname(__FILE__) . '/importeSql2019.php';
require_once dirname(__FILE__) . '/importeSql2018.php';
require_once dirname(__FILE__) . '/importeSql2017.php';
require_once dirname(__FILE__) . '/importeSql2016.php';
require_once dirname(__FILE__) . '/importeSql2015.php';
require_once dirname(__FILE__) . '/../../calculaPeso.php';

function balanza($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra)
{

    // Marcos: 17/mayo/2016
    // Para que funcione la balanza a nivel de detalle en pantalla, usaremos LIKE
    // en lugar de =, y para eso adicionamos la bandera $detalle
    //preguntamos que tipo de reporte solicitaron 1->UR, 2->Carga de Trabajo, 3->Jefe(personalizado)

    //$reporte = explode(",", $reporte);
    if ($reporte['tipo'] == '') {
        $reporte['tipo'] = 1;
    }

    //preguntamos cual es el nivel maximo que solicitaron y se guarda al inicio del array

    // Si piden las cantidades en miles de pesos
    //$parametroExtra = explode(",", $parametroExtra);
    if ($parametroExtra['miles'] == 1) {
        $divisor = 1000;
    } else {
        $divisor = 1;
    }

    $agrupador = ($reporte['agrupador'] == 1 ? 1 : 0);

    $inicial = [];
    $movimiento = [];

    switch ($parametroExtra['ejercicio']) {
        case 2026:
            $parametroExtra['I'] = true;
            $inicial             = importeSql($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                $programatico, $claveFinanciamiento, $reporte, $detalle,
                $mmc_id, $niveles, $parametroExtra);
            $parametroExtra['I'] = false;
            $movimiento          = importeSql($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                $programatico, $claveFinanciamiento, $reporte, $detalle,
                $mmc_id, $niveles, $parametroExtra);
            break;
        case 2025:
            $parametroExtra['I'] = true;
            $inicial             = importeSql2025($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                $programatico, $claveFinanciamiento, $reporte, $detalle,
                $mmc_id, $niveles, $parametroExtra);
            $parametroExtra['I'] = false;
            $movimiento          = importeSql2025($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                $programatico, $claveFinanciamiento, $reporte, $detalle,
                $mmc_id, $niveles, $parametroExtra);
            break;
        case 2024:
            $parametroExtra['I'] = true;
            $inicial             = importeSql2024($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                $programatico, $claveFinanciamiento, $reporte, $detalle,
                $mmc_id, $niveles, $parametroExtra);
            $parametroExtra['I'] = false;
            $movimiento          = importeSql2024($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                $programatico, $claveFinanciamiento, $reporte, $detalle,
                $mmc_id, $niveles, $parametroExtra);
            break;
        case 2023:
            $parametroExtra['I'] = true;
            $inicial             = importeSql2023($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                $programatico, $claveFinanciamiento, $reporte, $detalle,
                $mmc_id, $niveles, $parametroExtra);
            $parametroExtra['I'] = false;
            $movimiento          = importeSql2023($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                $programatico, $claveFinanciamiento, $reporte, $detalle,
                $mmc_id, $niveles, $parametroExtra);
            break;

        case 2022:
            $parametroExtra['I'] = true;
            $inicial             = importeSql2022($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                $programatico, $claveFinanciamiento, $reporte, $detalle,
                $mmc_id, $niveles, $parametroExtra);
            $parametroExtra['I'] = false;
            $movimiento          = importeSql2022($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                $programatico, $claveFinanciamiento, $reporte, $detalle,
                $mmc_id, $niveles, $parametroExtra);
            break;

        case 2021:
            $parametroExtra['I'] = true;
            $inicial             = importeSql2021($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                $programatico, $claveFinanciamiento, $reporte, $detalle,
                $mmc_id, $niveles, $parametroExtra);
            $parametroExtra['I'] = false;
            $movimiento          = importeSql2021($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                $programatico, $claveFinanciamiento, $reporte, $detalle,
                $mmc_id, $niveles, $parametroExtra);
            break;

        case 2020:
            $parametroExtra['I'] = true;
            $inicial             = importeSql2020($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                $programatico, $claveFinanciamiento, $reporte, $detalle,
                $mmc_id, $niveles, $parametroExtra);
            $parametroExtra['I'] = false;
            $movimiento          = importeSql2020($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                $programatico, $claveFinanciamiento, $reporte, $detalle,
                $mmc_id, $niveles, $parametroExtra);
            break;

        case 2019:
            $parametroExtra['I'] = true;
            $inicial             = importeSql2019($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                $programatico, $claveFinanciamiento, $reporte, $detalle,
                $mmc_id, $niveles, $parametroExtra);
            $parametroExtra['I'] = false;
            $movimiento          = importeSql2019($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                $programatico, $claveFinanciamiento, $reporte, $detalle,
                $mmc_id, $niveles, $parametroExtra);
            break;

        case 2018:
            $parametroExtra['I'] = true;
            $inicial             = importeSql2018($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                $programatico, $claveFinanciamiento, $reporte, $detalle,
                $mmc_id, $niveles, $parametroExtra);
            $parametroExtra['I'] = false;
            $movimiento          = importeSql2018($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                $programatico, $claveFinanciamiento, $reporte, $detalle,
                $mmc_id, $niveles, $parametroExtra);
            break;

        case 2017:
            $parametroExtra['I'] = true;
            $inicial             = importeSql2017($gpo_id, $ur_id, $ue_id, $pot_id,
                $periodo, $programatico, $claveFinanciamiento, $reporte,
                $detalle, $mmc_id, $niveles, $parametroExtra);
            $parametroExtra['I'] = false;
            $movimiento          = importeSql2017($gpo_id, $ur_id, $ue_id, $pot_id,
                $periodo, $programatico, $claveFinanciamiento, $reporte,
                $detalle, $mmc_id, $niveles, $parametroExtra);
            break;

        case 2016:
            $parametroExtra['I'] = true;
            $inicial             = importeSql2016($gpo_id, $ur_id, $ue_id, $pot_id,
                $periodo, $programatico, $claveFinanciamiento, $reporte,
                $detalle, $mmc_id, $niveles, $parametroExtra);
            $parametroExtra['I'] = false;
            $movimiento          = importeSql2016($gpo_id, $ur_id, $ue_id, $pot_id,
                $periodo, $programatico, $claveFinanciamiento, $reporte,
                $detalle, $mmc_id, $niveles, $parametroExtra);
            break;

        case 2015:
            $parametroExtra['I'] = true;
            $inicial             = importeSql2015($gpo_id, $ur_id, $ue_id, $pot_id,
                $periodo, $programatico, $claveFinanciamiento, $reporte,
                $detalle, $mmc_id, $niveles, $parametroExtra);
            $parametroExtra['I'] = false;
            $movimiento          = importeSql2015($gpo_id, $ur_id, $ue_id, $pot_id,
                $periodo, $programatico, $claveFinanciamiento, $reporte,
                $detalle, $mmc_id, $niveles, $parametroExtra);
            break;
        default:break;
    }

    $sumaDebeInicial  = 0;
    $sumaHaberInicial = 0;
    $sumaDebePeriodo  = 0;
    $sumaHaberPeriodo = 0;
    $sumaDebeFinal    = 0;
    $sumaHaberFinal   = 0;

  
    $datos = array_merge(array_fill_keys(array_keys($inicial), ''), array_fill_keys(array_keys($movimiento), ''));
 
    ksort($datos);
    $NUMREG = count($datos);
    $peso   = calcularPeso($NUMREG, 20);

    ini_set('memory_limit', $peso . "M");
    foreach ($datos as $ind => $indval) {
        $datos[$ind] = [];

        if (isset($inicial[$ind])) {

            $datos[$ind]['id']             = (isset($inicial['id'])) ? $inicial[$ind]['id'] : "";
            $datos[$ind]['ur_clave']       = $inicial[$ind]['ur_clave'];
            $datos[$ind]['ur_nombre']      = $inicial[$ind]['ur_nombre'];
            $datos[$ind]['cta_gpo_id']     = $inicial[$ind]['cta_gpo_id'];
            $datos[$ind]['cta_ur_id']      = $inicial[$ind]['cta_ur_id'];
            $datos[$ind]['cta_ue_id']      = $inicial[$ind]['cta_ue_id'];
            $datos[$ind]['ger_cuenta']     = $inicial[$ind]['ger_cuenta'];
            $datos[$ind]['ger_nombre']     = $inicial[$ind]['ger_nombre'];
            $datos[$ind]['cgp_cuenta']     = $inicial[$ind]['cgp_cuenta'];
            $datos[$ind]['cgp_nombre']     = $inicial[$ind]['cgp_nombre'];
            $datos[$ind]['rub_cuenta']     = $inicial[$ind]['rub_cuenta'];
            $datos[$ind]['rub_nombre']     = $inicial[$ind]['rub_nombre'];
            $datos[$ind]['cgcu_cuenta']    = $inicial[$ind]['cgcu_cuenta'];
            $datos[$ind]['cgcu_nombre']    = $inicial[$ind]['cgcu_nombre'];
            $datos[$ind]['cgsc_cuenta']    = $inicial[$ind]['cgsc_cuenta'];
            $datos[$ind]['cgsc_nombre']    = $inicial[$ind]['cgsc_nombre'];
            $datos[$ind]['cgc_cuenta']     = $inicial[$ind]['cgc_cuenta'];
            $datos[$ind]['cgc_nombre']     = $inicial[$ind]['cgc_nombre'];
            $datos[$ind]['cgq_cuenta']     = $inicial[$ind]['cgq_cuenta'];
            $datos[$ind]['cgq_nombre']     = $inicial[$ind]['cgq_nombre'];
            $datos[$ind]['cta_cuenta']     = $inicial[$ind]['cta_cuenta'];
            $datos[$ind]['cta_nombre']     = $inicial[$ind]['cta_nombre'];
            $datos[$ind]['cuenta']         = $inicial[$ind]['cuenta'];
            $datos[$ind]['nombre']         = $inicial[$ind]['nombre'];
            $datos[$ind]['cgm_naturaleza'] = $inicial[$ind]['cgm_naturaleza'];
            $datos[$ind]['nivel']          = $inicial[$ind]['nivel'];
            switch ($inicial[$ind]['cgm_naturaleza']) {
                case 'D':
                    $datos[$ind]['pdx_debeInicial'] = redondear($inicial[$ind]['pdx_debe']
                         - $inicial[$ind]['pdx_haber'], 2);
                    $datos[$ind]['pdx_haberInicial'] = 0;
                    break;
                case 'H':
                    $datos[$ind]['pdx_debeInicial']  = 0;
                    $datos[$ind]['pdx_haberInicial'] = redondear($inicial[$ind]['pdx_haber']
                         - $inicial[$ind]['pdx_debe'], 2);
                    break;
                default:
                    echo 'kym 2 ';
                    break;
            }
        }

        if (isset($movimiento[$ind])) {

            $datos[$ind]['id']               = (isset($movimiento[$ind]['id'])) ? $movimiento[$ind]['id'] : "";
            $datos[$ind]['ur_clave']         = $movimiento[$ind]['ur_clave'];
            $datos[$ind]['ur_nombre']        = $movimiento[$ind]['ur_nombre'];
            $datos[$ind]['cta_gpo_id']       = $movimiento[$ind]['cta_gpo_id'];
            $datos[$ind]['cta_ur_id']        = $movimiento[$ind]['cta_ur_id'];
            $datos[$ind]['cta_ue_id']        = $movimiento[$ind]['cta_ue_id'];
            $datos[$ind]['ger_cuenta']       = $movimiento[$ind]['ger_cuenta'];
            $datos[$ind]['ger_nombre']       = $movimiento[$ind]['ger_nombre'];
            $datos[$ind]['cgp_cuenta']       = $movimiento[$ind]['cgp_cuenta'];
            $datos[$ind]['cgp_nombre']       = $movimiento[$ind]['cgp_nombre'];
            $datos[$ind]['rub_cuenta']       = $movimiento[$ind]['rub_cuenta'];
            $datos[$ind]['rub_nombre']       = $movimiento[$ind]['rub_nombre'];
            $datos[$ind]['cgcu_cuenta']      = $movimiento[$ind]['cgcu_cuenta'];
            $datos[$ind]['cgcu_nombre']      = $movimiento[$ind]['cgcu_nombre'];
            $datos[$ind]['cgsc_cuenta']      = $movimiento[$ind]['cgsc_cuenta'];
            $datos[$ind]['cgsc_nombre']      = $movimiento[$ind]['cgsc_nombre'];
            $datos[$ind]['cgc_cuenta']       = $movimiento[$ind]['cgc_cuenta'];
            $datos[$ind]['cgc_nombre']       = $movimiento[$ind]['cgc_nombre'];
            $datos[$ind]['cgq_cuenta']       = $movimiento[$ind]['cgq_cuenta'];
            $datos[$ind]['cgq_nombre']       = $movimiento[$ind]['cgq_nombre'];
            $datos[$ind]['cta_cuenta']       = $movimiento[$ind]['cta_cuenta'];
            $datos[$ind]['cta_nombre']       = $movimiento[$ind]['cta_nombre'];
            $datos[$ind]['cuenta']           = $movimiento[$ind]['cuenta'];
            $datos[$ind]['nombre']           = $movimiento[$ind]['nombre'];
            $datos[$ind]['cgm_naturaleza']   = $movimiento[$ind]['cgm_naturaleza'];
            $datos[$ind]['nivel']            = $movimiento[$ind]['nivel'];
            $datos[$ind]['pdx_debePeriodo']  = $movimiento[$ind]['pdx_debe'];
            $datos[$ind]['pdx_haberPeriodo'] = $movimiento[$ind]['pdx_haber'];
        }
        if (! isset($datos[$ind]['pdx_debeInicial'])) {
            $datos[$ind]['pdx_debeInicial'] = 0;

            $datos[$ind]['pdx_haberInicial'] = 0;
        }
        if (! isset($datos[$ind]['pdx_debePeriodo'])) {
            $datos[$ind]['pdx_debePeriodo']  = 0;
            $datos[$ind]['pdx_haberPeriodo'] = 0;
        }
        switch ($datos[$ind]['cgm_naturaleza']) {
            case 'D':
                $datos[$ind]['pdx_debeFinal'] = redondear($datos[$ind]['pdx_debeInicial']
                     + $datos[$ind]['pdx_debePeriodo'] - $datos[$ind]['pdx_haberPeriodo'],
                    2);
                $datos[$ind]['pdx_haberFinal'] = 0;
                break;
            case 'H':
                $datos[$ind]['pdx_debeFinal']  = 0;
                $datos[$ind]['pdx_haberFinal'] = redondear($datos[$ind]['pdx_haberInicial']
                     + $datos[$ind]['pdx_haberPeriodo'] - $datos[$ind]['pdx_debePeriodo'],
                    2);
                break;
            default:
                echo 'kym 3 ';
                break;
        }
        $sumaDebeInicial  = redondear($sumaDebeInicial, 2) + redondear($datos[$ind]['pdx_debeInicial'], 2);
        $sumaHaberInicial = redondear($sumaHaberInicial, 2) + redondear($datos[$ind]['pdx_haberInicial'], 2);
        $sumaDebePeriodo  = redondear($sumaDebePeriodo, 2) + redondear($datos[$ind]['pdx_debePeriodo'], 2);
        $sumaHaberPeriodo = redondear($sumaHaberPeriodo, 2) + redondear($datos[$ind]['pdx_haberPeriodo'], 2);
        $sumaDebeFinal    = redondear($sumaDebeFinal, 2) + redondear($datos[$ind]['pdx_debeFinal'], 2);
        $sumaHaberFinal   = redondear($sumaHaberFinal, 2) + redondear($datos[$ind]['pdx_haberFinal'], 2);
    }

    #si pidieron mas del primer nivel, invertimos el array, para acumular saldos y colocarlos en el orden 'natural' del proceso
    if (isset($datos)) {

        $datos = array_reverse($datos, true);
        /*
         * preguntamos el nivel que solicitaron en la consulta
         * el nivel mas alto preguntara si el nivel - 1, fue solicitado en la balanza,
         * ya que pueden pedir el primero y el cuarto nivel(por ejemplo), y no seria necesario generar el tercero ni el segundo
         * preguntamos si solicitan el quinto nivel, hablando de cuentas de estructura 3(este es un caso especial)
         * y la unica manera de saber si la cuenta inicial es de estructura 3, es midiendo la longitud(20), sin formato
         * comparamos la cuenta actual, contra la cuenta anterior al nivel solicitado
         * si son diferentes, almacenamos el nivel generado y reiniciamos las variables
         * en caso contrario seguimos acumulando el nivel
         */

        switch ($niveles[0]) {
            case 8:
                //si solicitaron el septimo nivel
                if ($niveles[7] > 0) {
                    //iniciamos las variables
                    $cgq_cuenta    = '';
                    $sumaCgqDebeI  = 0;
                    $sumaCgqHaberI = 0;
                    $sumaCgqDebeM  = 0;
                    $sumaCgqHaberM = 0;
                    $sumaCgqDebeF  = 0;
                    $sumaCgqHaberF = 0;
                    $registros     = []; #arreglo de registros acumulativo, almacena los registros e intercala los siguientes niveles
                    $registro      = []; #almacena los datos del registro al nivel que se esta
                                         #generamos el 'quinto' nivel
                    foreach ($datos as $key => $value) {
                        #preguntamos si la cuenta(key) es de 20 caracteres(6 niveles)
                        $var = explode('.', $key);
                        // MARCOS: 18/may/2016
                        // Cuando lo pido desde la pantalla del detalle de balanza, llenando a 1 todos los niveles
                        // aquí truena porque el valor 7 no existe (nivel de registro)
                        // así que le adicioné el isset()
                        if (isset($var[7]) && (strlen($var[7]) == 2)) {
                            #obtenemos la cuenta a quinto nivel y
                            #comparamos contra el quinto nivel anterior
                            #y no solo es la variable inicializada
                            $cuentaNivel = $var[0] . '.' . $var[1] . '.' . $var[2] . '.' . $var[3] . '.' . $var[4] . '.' . $var[5] . '.' . $var[6] . '.' . $var[7] . (($reporte['tipo']
                                == 2 && $reporte['agrupador'] == 1) ? '.' . $var[8]
                                : '');

                            if ($cuentaNivel != $cgq_cuenta && $cgq_cuenta != '') {
                                //almacenamos los valores del quinto nivel buscado, en un array secundario
                                $registros[$cgq_cuenta]                     = $registro;
                                $registros[$cgq_cuenta]['pdx_debeInicial']  = $sumaCgqDebeI;
                                $registros[$cgq_cuenta]['pdx_haberInicial'] = $sumaCgqHaberI;
                                $registros[$cgq_cuenta]['pdx_debePeriodo']  = $sumaCgqDebeM;
                                $registros[$cgq_cuenta]['pdx_haberPeriodo'] = $sumaCgqHaberM;
                                $registros[$cgq_cuenta]['pdx_debeFinal']    = $sumaCgqDebeF;
                                $registros[$cgq_cuenta]['pdx_haberFinal']   = $sumaCgqHaberF;

                                //reiniciamos los valores de los acumuladores
                                $sumaCgqDebeI  = 0;
                                $sumaCgqHaberI = 0;
                                $sumaCgqDebeM  = 0;
                                $sumaCgqHaberM = 0;
                                $sumaCgqDebeF  = 0;
                                $sumaCgqHaberF = 0;
                            }

                            //respaldamos los datos del registro actual
                            $registro           = $datos[$key];
                            $registro['cuenta'] = $datos[$key]['cgq_cuenta'];
                            $registro['nombre'] = $datos[$key]['cgq_nombre'];
                            $registro['nivel']  = 7;

                            //acumulamos los montos del nivel actual
                            $sumaCgqDebeI  = $sumaCgqDebeI + $datos[$key]['pdx_debeInicial'];
                            $sumaCgqHaberI = $sumaCgqHaberI + $datos[$key]['pdx_haberInicial'];
                            $sumaCgqDebeM  = $sumaCgqDebeM + $datos[$key]['pdx_debePeriodo'];
                            $sumaCgqHaberM = $sumaCgqHaberM + $datos[$key]['pdx_haberPeriodo'];
                            $sumaCgqDebeF  = $sumaCgqDebeF + $datos[$key]['pdx_debeFinal'];
                            $sumaCgqHaberF = $sumaCgqHaberF + $datos[$key]['pdx_haberFinal'];
                        } elseif ($cgq_cuenta != '') { #si la cuenta no es de 8 niveles
                                                           #preguntamos si estabamos acumulando
                                                           //almacenamos los valores del quinto nivel buscado, en un array secundario
                            $registros[$cgq_cuenta]                     = $registro;
                            $registros[$cgq_cuenta]['pdx_debeInicial']  = $sumaCgqDebeI;
                            $registros[$cgq_cuenta]['pdx_haberInicial'] = $sumaCgqHaberI;
                            $registros[$cgq_cuenta]['pdx_debePeriodo']  = $sumaCgqDebeM;
                            $registros[$cgq_cuenta]['pdx_haberPeriodo'] = $sumaCgqHaberM;
                            $registros[$cgq_cuenta]['pdx_debeFinal']    = $sumaCgqDebeF;
                            $registros[$cgq_cuenta]['pdx_haberFinal']   = $sumaCgqHaberF;

                            //reiniciamos los valores de los acumuladores
                            $sumaCgqDebeI  = 0;
                            $sumaCgqHaberI = 0;
                            $sumaCgqDebeM  = 0;
                            $sumaCgqHaberM = 0;
                            $sumaCgqDebeF  = 0;
                            $sumaCgqHaberF = 0;

                            $cgq_cuenta = '';
                        }
                        //agregamos el registro de nivel superior(aqui es a toda la cuenta inicial)
                        $registros[$key] = $datos[$key];
                        //respaldamos el quinto nivel actual para compararlo en el ciclo
                        // MARCOS: 18/may/2016
                        // Cuando lo pido desde la pantalla del detalle de balanza, llenando a 1 todos los niveles
                        // aquí truena porque el valor 7 no existe (nivel de registro)
                        // así que le reactivé el isset() que ya existía, pero estaba comentado
                        if (isset($var[7])) {
                            if (strlen($var[7]) == 2) {
                                $cgq_cuenta = 'C.' . ($agrupador == 1 ? $registro['cta_gpo_id'] . $registro['cta_ur_id'] . '.' : '') . $registro['cuenta'];
                            }
                        }

                    }
                    #si el ultimo registro era una cuenta de 6 niveles
                    if ($cgq_cuenta != '') {

                        //almacenamos el ultimo registro, que en el ciclo ya no se compara con el siguiente(al no existir)
                        $registros[$cgq_cuenta]                     = $registro;
                        $registros[$cgq_cuenta]['pdx_debeInicial']  = $sumaCgqDebeI;
                        $registros[$cgq_cuenta]['pdx_haberInicial'] = $sumaCgqHaberI;
                        $registros[$cgq_cuenta]['pdx_debePeriodo']  = $sumaCgqDebeM;
                        $registros[$cgq_cuenta]['pdx_haberPeriodo'] = $sumaCgqHaberM;
                        $registros[$cgq_cuenta]['pdx_debeFinal']    = $sumaCgqDebeF;
                        $registros[$cgq_cuenta]['pdx_haberFinal']   = $sumaCgqHaberF;
                    }
                    //guardamos el resultado en el arreglo original
                    $datos = $registros;
                }
            //break;//deshabilitamos el break porque debe iniciar desde el nivel mayor y de ahi hacer los demas
            case 7:
                //si solicitaron el sexto nivel
                if ($niveles[6] > 0) {
                    //iniciamos las variables
                    $cgc_cuenta    = '';
                    $sumaCgcDebeI  = 0;
                    $sumaCgcHaberI = 0;
                    $sumaCgcDebeM  = 0;
                    $sumaCgcHaberM = 0;
                    $sumaCgcDebeF  = 0;
                    $sumaCgcHaberF = 0;
                    $registros     = [];
                    $registro      = [];
                    $n             = 0;

                    //generamos el cuarto nivel
                    foreach ($datos as $key => $value) {
                        #obtenemos la cuenta a sexto nivel
                        #si el actual sexto nivel es diferente del anterior
                        #si existe el sexto nivel anterior y no solo es la variables inicializada
                        $var         = explode('.', $key);
                        $cuentaNivel = $var[0] . '.' . $var[1] . '.' . $var[2] . '.' . $var[3] . '.' . $var[4] . '.' . $var[5] . '.' . $var[6] . ($agrupador == 1 ? '.' . $var[7] : '');

                        if ($cuentaNivel != $cgc_cuenta && $cgc_cuenta != '') {
                            //almacenamos los valores del cuarto nivel buscado, en un array secundario
                            $registros[$cgc_cuenta]                     = $registro;
                            $registros[$cgc_cuenta]['pdx_debeInicial']  = $sumaCgcDebeI;
                            $registros[$cgc_cuenta]['pdx_haberInicial'] = $sumaCgcHaberI;
                            $registros[$cgc_cuenta]['pdx_debePeriodo']  = $sumaCgcDebeM;
                            $registros[$cgc_cuenta]['pdx_haberPeriodo'] = $sumaCgcHaberM;
                            $registros[$cgc_cuenta]['pdx_debeFinal']    = $sumaCgcDebeF;
                            $registros[$cgc_cuenta]['pdx_haberFinal']   = $sumaCgcHaberF;

                            //reiniciamos los valores de los acumuladores
                            $sumaCgcDebeI  = 0;
                            $sumaCgcHaberI = 0;
                            $sumaCgcDebeM  = 0;
                            $sumaCgcHaberM = 0;
                            $sumaCgcDebeF  = 0;
                            $sumaCgcHaberF = 0;
                        }
                        //respaldamos los datos del registro actual
                        $registro           = $datos[$key];
                        $registro['cuenta'] = $datos[$key]['cgc_cuenta'];
                        $registro['nombre'] = $datos[$key]['cgc_nombre'];
                        $registro['nivel']  = 6;

                        if (($datos[$key]['nivel'] != 7 && $niveles[0] == 8) || $niveles[0]
                            == 7) {
                            //acumulamos los montos del nivel actual
                            $sumaCgcDebeI  = $sumaCgcDebeI + $datos[$key]['pdx_debeInicial'];
                            $sumaCgcHaberI = $sumaCgcHaberI + $datos[$key]['pdx_haberInicial'];
                            $sumaCgcDebeM  = $sumaCgcDebeM + $datos[$key]['pdx_debePeriodo'];
                            $sumaCgcHaberM = $sumaCgcHaberM + $datos[$key]['pdx_haberPeriodo'];
                            $sumaCgcDebeF  = $sumaCgcDebeF + $datos[$key]['pdx_debeFinal'];
                            $sumaCgcHaberF = $sumaCgcHaberF + $datos[$key]['pdx_haberFinal'];
                        }
                        //agregamos el registro de nivel superior
                        $registros[$key] = $datos[$key];

                        //las llaves de cada row es la cuenta al nivel que se genera
                        $cgc_cuenta = 'C.' . ($agrupador == 1 ? $registro['cta_gpo_id'] . $registro['cta_ur_id'] . '.' : '') . $registro['cuenta'];
                    }

                    //almacenamos el ultimo registro, que en el ciclo ya no se compara con el siguiente(al no existir)
                    $registros[$cgc_cuenta]                     = $registro;
                    $registros[$cgc_cuenta]['pdx_debeInicial']  = $sumaCgcDebeI;
                    $registros[$cgc_cuenta]['pdx_haberInicial'] = $sumaCgcHaberI;
                    $registros[$cgc_cuenta]['pdx_debePeriodo']  = $sumaCgcDebeM;
                    $registros[$cgc_cuenta]['pdx_haberPeriodo'] = $sumaCgcHaberM;
                    $registros[$cgc_cuenta]['pdx_debeFinal']    = $sumaCgcDebeF;
                    $registros[$cgc_cuenta]['pdx_haberFinal']   = $sumaCgcHaberF;

                    //guardamos el resultado en el arreglo original
                    $datos = $registros;
                }
            //break;
            case 6:
                //si solicitaron el quinto nivel
                if ($niveles[5] > 0) {
                    //iniciamos las variables
                    $cgsc_cuenta    = '';
                    $sumaCgscDebeI  = 0;
                    $sumaCgscHaberI = 0;
                    $sumaCgscDebeM  = 0;
                    $sumaCgscHaberM = 0;
                    $sumaCgscDebeF  = 0;
                    $sumaCgscHaberF = 0;
                    $registros      = [];
                    $registro       = [];

                    //generamos el cuarto nivel
                    foreach ($datos as $key => $value) {
                        #obtenemos la cuenta a cuarto nivel
                        #si el actual cuarto nivel es diferente del anterior
                        #si existe el cuarto nivel anterior y no solo es la variables inicializada
                        $var         = explode('.', $key);
                        $cuentaNivel = $var[0] . '.' . $var[1] . '.' . $var[2] . '.' . $var[3] . '.' . $var[4] . '.' . $var[5] . ($agrupador == 1 ? '.' . $var[6] : '');

                        if ($cuentaNivel != $cgsc_cuenta && $cgsc_cuenta != '') {
                            //almacenamos los valores del cuarto nivel buscado, en un array secundario
                            $registros[$cgsc_cuenta]                     = $registro;
                            $registros[$cgsc_cuenta]['pdx_debeInicial']  = $sumaCgscDebeI;
                            $registros[$cgsc_cuenta]['pdx_haberInicial'] = $sumaCgscHaberI;
                            $registros[$cgsc_cuenta]['pdx_debePeriodo']  = $sumaCgscDebeM;
                            $registros[$cgsc_cuenta]['pdx_haberPeriodo'] = $sumaCgscHaberM;
                            $registros[$cgsc_cuenta]['pdx_debeFinal']    = $sumaCgscDebeF;
                            $registros[$cgsc_cuenta]['pdx_haberFinal']   = $sumaCgscHaberF;

                            //reiniciamos los valores de los acumuladores
                            $sumaCgscDebeI  = 0;
                            $sumaCgscHaberI = 0;
                            $sumaCgscDebeM  = 0;
                            $sumaCgscHaberM = 0;
                            $sumaCgscDebeF  = 0;
                            $sumaCgscHaberF = 0;
                        }

                        //respaldamos los datos de registro actual
                        $registro           = $datos[$key];
                        $registro['cuenta'] = $datos[$key]['cgsc_cuenta'];
                        $registro['nombre'] = $datos[$key]['cgsc_nombre'];
                        $registro['nivel']  = 5;

                        if ($niveles[0] == $datos[$key]['nivel']) {
                            //acumulamos los montos del nivel actual
                            $sumaCgscDebeI  = $sumaCgscDebeI + $datos[$key]['pdx_debeInicial'];
                            $sumaCgscHaberI = $sumaCgscHaberI + $datos[$key]['pdx_haberInicial'];
                            $sumaCgscDebeM  = $sumaCgscDebeM + $datos[$key]['pdx_debePeriodo'];
                            $sumaCgscHaberM = $sumaCgscHaberM + $datos[$key]['pdx_haberPeriodo'];
                            $sumaCgscDebeF  = $sumaCgscDebeF + $datos[$key]['pdx_debeFinal'];
                            $sumaCgscHaberF = $sumaCgscHaberF + $datos[$key]['pdx_haberFinal'];
                        }
                        //agregamos el registro de nivel superior
                        $registros[$key] = $datos[$key];

                        //las llaves de cada row es la cuenta al nivel que se genera, sin formato de puntos
                        $cgsc_cuenta = 'C.' . ($agrupador == 1 ? $registro['cta_gpo_id'] . $registro['cta_ur_id'] . '.' : '') . $registro['cuenta'];
                    }

                    //almacenamos el ultimo registro, que en el ciclo ya no se compara con el siguiente(al no existir)
                    $registros[$cgsc_cuenta]                     = $registro;
                    $registros[$cgsc_cuenta]['pdx_debeInicial']  = $sumaCgscDebeI;
                    $registros[$cgsc_cuenta]['pdx_haberInicial'] = $sumaCgscHaberI;
                    $registros[$cgsc_cuenta]['pdx_debePeriodo']  = $sumaCgscDebeM;
                    $registros[$cgsc_cuenta]['pdx_haberPeriodo'] = $sumaCgscHaberM;
                    $registros[$cgsc_cuenta]['pdx_debeFinal']    = $sumaCgscDebeF;
                    $registros[$cgsc_cuenta]['pdx_haberFinal']   = $sumaCgscHaberF;

                    //guardamos el resultado en el arreglo original
                    $datos = $registros;
                }

            //break;
            case 5:
                //si solicitaron el cuarto nivel
                if ($niveles[4] > 0) {
                    //iniciamos las variables
                    $cgcu_cuenta    = '';
                    $sumaCgcuDebeI  = 0;
                    $sumaCgcuHaberI = 0;
                    $sumaCgcuDebeM  = 0;
                    $sumaCgcuHaberM = 0;
                    $sumaCgcuDebeF  = 0;
                    $sumaCgcuHaberF = 0;
                    $registros      = [];
                    $registro       = [];

                    //generamos el cuarto nivel
                    foreach ($datos as $key => $value) {
                        #obtenemos la cuenta a cuarto nivel
                        #si el actual cuarto nivel es diferente del anterior
                        #si existe el cuarto nivel anterior y no solo es la variables inicializada
                        $var         = explode('.', $key);
                        $cuentaNivel = $var[0] . '.' . $var[1] . '.' . $var[2] . '.' . $var[3] . '.' . $var[4] . ($agrupador == 1 ? '.' . $var[5] : '');

                        if ($cuentaNivel != $cgcu_cuenta && $cgcu_cuenta != '') {
                            //almacenamos los valores del cuarto nivel buscado, en un array secundario
                            $registros[$cgcu_cuenta]                     = $registro;
                            $registros[$cgcu_cuenta]['pdx_debeInicial']  = $sumaCgcuDebeI;
                            $registros[$cgcu_cuenta]['pdx_haberInicial'] = $sumaCgcuHaberI;
                            $registros[$cgcu_cuenta]['pdx_debePeriodo']  = $sumaCgcuDebeM;
                            $registros[$cgcu_cuenta]['pdx_haberPeriodo'] = $sumaCgcuHaberM;
                            $registros[$cgcu_cuenta]['pdx_debeFinal']    = $sumaCgcuDebeF;
                            $registros[$cgcu_cuenta]['pdx_haberFinal']   = $sumaCgcuHaberF;

                            //reiniciamos los valores de los acumuladores
                            $sumaCgcuDebeI  = 0;
                            $sumaCgcuHaberI = 0;
                            $sumaCgcuDebeM  = 0;
                            $sumaCgcuHaberM = 0;
                            $sumaCgcuDebeF  = 0;
                            $sumaCgcuHaberF = 0;
                        }

                        //respaldamos los datos de registro actual
                        $registro           = $datos[$key];
                        $registro['cuenta'] = $datos[$key]['cgcu_cuenta'];
                        $registro['nombre'] = $datos[$key]['cgcu_nombre'];
                        $registro['nivel']  = 4;

                        if ($niveles[0] == $datos[$key]['nivel']) {
                            //acumulamos los montos del nivel actual
                            $sumaCgcuDebeI  = $sumaCgcuDebeI + $datos[$key]['pdx_debeInicial'];
                            $sumaCgcuHaberI = $sumaCgcuHaberI + $datos[$key]['pdx_haberInicial'];
                            $sumaCgcuDebeM  = $sumaCgcuDebeM + $datos[$key]['pdx_debePeriodo'];
                            $sumaCgcuHaberM = $sumaCgcuHaberM + $datos[$key]['pdx_haberPeriodo'];
                            $sumaCgcuDebeF  = $sumaCgcuDebeF + $datos[$key]['pdx_debeFinal'];
                            $sumaCgcuHaberF = $sumaCgcuHaberF + $datos[$key]['pdx_haberFinal'];
                        }
                        //agregamos el registro de nivel superior
                        $registros[$key] = $datos[$key];

                        //las llaves de cada row es la cuenta al nivel que se genera, sin formato de puntos
                        $cgcu_cuenta = 'C.' . ($agrupador == 1 ? $registro['cta_gpo_id'] . $registro['cta_ur_id'] . '.' : '') . $registro['cuenta'];
                    }

                    //almacenamos el ultimo registro, que en el ciclo ya no se compara con el siguiente(al no existir)
                    $registros[$cgcu_cuenta]                     = $registro;
                    $registros[$cgcu_cuenta]['pdx_debeInicial']  = $sumaCgcuDebeI;
                    $registros[$cgcu_cuenta]['pdx_haberInicial'] = $sumaCgcuHaberI;
                    $registros[$cgcu_cuenta]['pdx_debePeriodo']  = $sumaCgcuDebeM;
                    $registros[$cgcu_cuenta]['pdx_haberPeriodo'] = $sumaCgcuHaberM;
                    $registros[$cgcu_cuenta]['pdx_debeFinal']    = $sumaCgcuDebeF;
                    $registros[$cgcu_cuenta]['pdx_haberFinal']   = $sumaCgcuHaberF;

                    //guardamos el resultado en el arreglo original
                    $datos = $registros;
                }

            //break;
            case 4:
                //si solicitaron el tercer nivel
                if ($niveles[3] > 0) {
                    //iniciamos las variables
                    $rub_cuenta    = '';
                    $sumaRubDebeI  = 0;
                    $sumaRubHaberI = 0;
                    $sumaRubDebeM  = 0;
                    $sumaRubHaberM = 0;
                    $sumaRubDebeF  = 0;
                    $sumaRubHaberF = 0;
                    $registros     = [];
                    $registro      = [];

                    //generamos el tercer nivel
                    foreach ($datos as $key => $value) {

                        $var         = explode('.', $key);
                        $cuentaNivel = $var[0] . '.' . $var[1] . '.' . $var[2] . '.' . $var[3] . ($agrupador == 1 ? '.' . $var[4] : '');
                        if ($cuentaNivel != $rub_cuenta && $rub_cuenta != '') {

                            $registros[$rub_cuenta]                     = $registro;
                            $registros[$rub_cuenta]['pdx_debeInicial']  = $sumaRubDebeI;
                            $registros[$rub_cuenta]['pdx_haberInicial'] = $sumaRubHaberI;
                            $registros[$rub_cuenta]['pdx_debePeriodo']  = $sumaRubDebeM;
                            $registros[$rub_cuenta]['pdx_haberPeriodo'] = $sumaRubHaberM;
                            $registros[$rub_cuenta]['pdx_debeFinal']    = $sumaRubDebeF;
                            $registros[$rub_cuenta]['pdx_haberFinal']   = $sumaRubHaberF;

                            $sumaRubDebeI  = 0;
                            $sumaRubHaberI = 0;
                            $sumaRubDebeM  = 0;
                            $sumaRubHaberM = 0;
                            $sumaRubDebeF  = 0;
                            $sumaRubHaberF = 0;
                        }

                        $registro           = $datos[$key];
                        $registro['cuenta'] = $datos[$key]['rub_cuenta'];
                        $registro['nombre'] = $datos[$key]['rub_nombre'];
                        $registro['nivel']  = 3;

                        if ($niveles[0] == $datos[$key]['nivel']) {
                            $sumaRubDebeI  = $sumaRubDebeI + $datos[$key]['pdx_debeInicial'];
                            $sumaRubHaberI = $sumaRubHaberI + $datos[$key]['pdx_haberInicial'];
                            $sumaRubDebeM  = $sumaRubDebeM + $datos[$key]['pdx_debePeriodo'];
                            $sumaRubHaberM = $sumaRubHaberM + $datos[$key]['pdx_haberPeriodo'];
                            $sumaRubDebeF  = $sumaRubDebeF + $datos[$key]['pdx_debeFinal'];
                            $sumaRubHaberF = $sumaRubHaberF + $datos[$key]['pdx_haberFinal'];
                        }

                        $registros[$key] = $datos[$key];
                        $rub_cuenta      = 'C.' . ($agrupador == 1 ? $registro['cta_gpo_id'] . $registro['cta_ur_id'] . '.' : '') . $registro['cuenta'];
                    }

                    $registros[$rub_cuenta]                     = $registro;
                    $registros[$rub_cuenta]['pdx_debeInicial']  = $sumaRubDebeI;
                    $registros[$rub_cuenta]['pdx_haberInicial'] = $sumaRubHaberI;
                    $registros[$rub_cuenta]['pdx_debePeriodo']  = $sumaRubDebeM;
                    $registros[$rub_cuenta]['pdx_haberPeriodo'] = $sumaRubHaberM;
                    $registros[$rub_cuenta]['pdx_debeFinal']    = $sumaRubDebeF;
                    $registros[$rub_cuenta]['pdx_haberFinal']   = $sumaRubHaberF;

                    //guardamos el resultado en el arreglo original
                    $datos = $registros;
                }

            //break;
            case 3:
                //si solicitaron el segundo nivel
                if ($niveles[2] > 0) {
                    //reiniciamos las variables
                    $cgp_cuenta    = '';
                    $sumaCgpDebeI  = 0;
                    $sumaCgpHaberI = 0;
                    $sumaCgpDebeM  = 0;
                    $sumaCgpHaberM = 0;
                    $sumaCgpDebeF  = 0;
                    $sumaCgpHaberF = 0;
                    $registro      = [];
                    $registros     = [];

                    //generamos el segundo nivel
                    foreach ($datos as $key => $value) {
                        $var         = explode('.', $key);
                        $cuentaNivel = $var[0] . '.' . $var[1] . '.' . $var[2] . ($agrupador == 1 ? '.' . $var[3] : '');
                        if ($cuentaNivel != $cgp_cuenta && $cgp_cuenta != '') {

                            $registros[$cgp_cuenta]                     = $registro;
                            $registros[$cgp_cuenta]['pdx_debeInicial']  = $sumaCgpDebeI;
                            $registros[$cgp_cuenta]['pdx_haberInicial'] = $sumaCgpHaberI;
                            $registros[$cgp_cuenta]['pdx_debePeriodo']  = $sumaCgpDebeM;
                            $registros[$cgp_cuenta]['pdx_haberPeriodo'] = $sumaCgpHaberM;
                            $registros[$cgp_cuenta]['pdx_debeFinal']    = $sumaCgpDebeF;
                            $registros[$cgp_cuenta]['pdx_haberFinal']   = $sumaCgpHaberF;

                            $sumaCgpDebeI  = 0;
                            $sumaCgpHaberI = 0;
                            $sumaCgpDebeM  = 0;
                            $sumaCgpHaberM = 0;
                            $sumaCgpDebeF  = 0;
                            $sumaCgpHaberF = 0;
                        }

                        $registro           = $datos[$key];
                        $registro['cuenta'] = $datos[$key]['cgp_cuenta'];
                        $registro['nombre'] = $datos[$key]['cgp_nombre'];
                        $registro['nivel']  = 2;

                        if ($niveles[0] == $datos[$key]['nivel']) {
                            $sumaCgpDebeI  = $sumaCgpDebeI + $datos[$key]['pdx_debeInicial'];
                            $sumaCgpHaberI = $sumaCgpHaberI + $datos[$key]['pdx_haberInicial'];
                            $sumaCgpDebeM  = $sumaCgpDebeM + $datos[$key]['pdx_debePeriodo'];
                            $sumaCgpHaberM = $sumaCgpHaberM + $datos[$key]['pdx_haberPeriodo'];
                            $sumaCgpDebeF  = $sumaCgpDebeF + $datos[$key]['pdx_debeFinal'];
                            $sumaCgpHaberF = $sumaCgpHaberF + $datos[$key]['pdx_haberFinal'];
                        }

                        $registros[$key] = $datos[$key];
                        $cgp_cuenta      = 'C.' . ($agrupador == 1 ? $registro['cta_gpo_id'] . $registro['cta_ur_id'] . '.' : '') . $registro['cuenta'];
                    }

                    $registros[$cgp_cuenta]                     = $registro;
                    $registros[$cgp_cuenta]['pdx_debeInicial']  = $sumaCgpDebeI;
                    $registros[$cgp_cuenta]['pdx_haberInicial'] = $sumaCgpHaberI;
                    $registros[$cgp_cuenta]['pdx_debePeriodo']  = $sumaCgpDebeM;
                    $registros[$cgp_cuenta]['pdx_haberPeriodo'] = $sumaCgpHaberM;
                    $registros[$cgp_cuenta]['pdx_debeFinal']    = $sumaCgpDebeF;
                    $registros[$cgp_cuenta]['pdx_haberFinal']   = $sumaCgpHaberF;

                    //guardamos el resultado en el arreglo original
                    $datos = $registros;
                }

            //break;
            case 2:
                //reiniciamos las variables
                $ger_cuenta    = '';
                $sumaGerDebeI  = 0;
                $sumaGerHaberI = 0;
                $sumaGerDebeM  = 0;
                $sumaGerHaberM = 0;
                $sumaGerDebeF  = 0;
                $sumaGerHaberF = 0;
                $registros     = [];
                $registro      = [];

                //generamos el primer nivel
                foreach ($datos as $key => $value) {
                    if(!empty($key)){
                        $var         = explode('.', $key);
                        $cuentaNivel = $var[0] . '.' . $var[1] . ($agrupador == 1 ? '.' . $var[2] : '');
                    

                        if ($cuentaNivel != $ger_cuenta && $ger_cuenta != '') {

                            $registros[$ger_cuenta]                     = $registro;
                            $registros[$ger_cuenta]['pdx_debeInicial']  = $sumaGerDebeI;
                            $registros[$ger_cuenta]['pdx_haberInicial'] = $sumaGerHaberI;
                            $registros[$ger_cuenta]['pdx_debePeriodo']  = $sumaGerDebeM;
                            $registros[$ger_cuenta]['pdx_haberPeriodo'] = $sumaGerHaberM;
                            $registros[$ger_cuenta]['pdx_debeFinal']    = $sumaGerDebeF;
                            $registros[$ger_cuenta]['pdx_haberFinal']   = $sumaGerHaberF;

                            $sumaGerDebeI  = 0;
                            $sumaGerHaberI = 0;
                            $sumaGerDebeM  = 0;
                            $sumaGerHaberM = 0;
                            $sumaGerDebeF  = 0;
                            $sumaGerHaberF = 0;
                        }
                    
                        $registro           = $datos[$key];
                        $registro['cuenta'] = $datos[$key]['ger_cuenta'];
                        $registro['nombre'] = $datos[$key]['ger_nombre'];
                        $registro['nivel']  = 1;

                        if ($niveles[0] == $datos[$key]['nivel']) {
                            $sumaGerDebeI  = $sumaGerDebeI + $datos[$key]['pdx_debeInicial'];
                            $sumaGerHaberI = $sumaGerHaberI + $datos[$key]['pdx_haberInicial'];
                            $sumaGerDebeM  = $sumaGerDebeM + $datos[$key]['pdx_debePeriodo'];
                            $sumaGerHaberM = $sumaGerHaberM + $datos[$key]['pdx_haberPeriodo'];
                            $sumaGerDebeF  = $sumaGerDebeF + $datos[$key]['pdx_debeFinal'];
                            $sumaGerHaberF = $sumaGerHaberF + $datos[$key]['pdx_haberFinal'];
                        }

                        $registros[$key] = $datos[$key];
                        $ger_cuenta      = 'C.' . ($agrupador == 1 ? $registro['cta_gpo_id'] . $registro['cta_ur_id'] . '.' : '') . $registro['cuenta'];
                    }
                }
                $registros[$ger_cuenta]                     = $registro;
                $registros[$ger_cuenta]['pdx_debeInicial']  = $sumaGerDebeI;
                $registros[$ger_cuenta]['pdx_haberInicial'] = $sumaGerHaberI;
                $registros[$ger_cuenta]['pdx_debePeriodo']  = $sumaGerDebeM;
                $registros[$ger_cuenta]['pdx_haberPeriodo'] = $sumaGerHaberM;
                $registros[$ger_cuenta]['pdx_debeFinal']    = $sumaGerDebeF;
                $registros[$ger_cuenta]['pdx_haberFinal']   = $sumaGerHaberF;

                //guardamos el resultado en el arreglo original
                $datos = $registros;
                break;

            case 1:break;

            default:
                echo 'kym 4 ';
                break;
        }

        if ($reporte['agrupador'] == 1 && $reporte['tipo'] != 1) {

            //reiniciamos las variables
            $ur_cuenta    = '';
            $sumaUrDebeI  = 0;
            $sumaUrHaberI = 0;
            $sumaUrDebeM  = 0;
            $sumaUrHaberM = 0;
            $sumaUrDebeF  = 0;
            $sumaUrHaberF = 0;
            $registros    = [];
            $registro     = [];

            //generamos el nivel de la UR
            foreach ($datos as $key => $value) {
                $var = explode('.', $key);

                if ($var[1] != $ur_cuenta && $ur_cuenta != '') {

                    $registros[$ur_cuenta]                     = $registro;
                    $registros[$ur_cuenta]['pdx_debeInicial']  = $sumaUrDebeI;
                    $registros[$ur_cuenta]['pdx_haberInicial'] = $sumaUrHaberI;
                    $registros[$ur_cuenta]['pdx_debePeriodo']  = $sumaUrDebeM;
                    $registros[$ur_cuenta]['pdx_haberPeriodo'] = $sumaUrHaberM;
                    $registros[$ur_cuenta]['pdx_debeFinal']    = $sumaUrDebeF;
                    $registros[$ur_cuenta]['pdx_haberFinal']   = $sumaUrHaberF;

                    $sumaUrDebeI  = 0;
                    $sumaUrHaberI = 0;
                    $sumaUrDebeM  = 0;
                    $sumaUrHaberM = 0;
                    $sumaUrDebeF  = 0;
                    $sumaUrHaberF = 0;
                }

                $registro           = $datos[$key];
                $registro['cuenta'] = $datos[$key]['cta_gpo_id'] . $datos[$key]['cta_ur_id'];
                $registro['nombre'] = $datos[$key]['ur_nombre'];
                $registro['nivel']  = 0;

                if ($niveles[0] == $datos[$key]['nivel']) {
                    $sumaUrDebeI  = $sumaUrDebeI + $datos[$key]['pdx_debeInicial'];
                    $sumaUrHaberI = $sumaUrHaberI + $datos[$key]['pdx_haberInicial'];
                    $sumaUrDebeM  = $sumaUrDebeM + $datos[$key]['pdx_debePeriodo'];
                    $sumaUrHaberM = $sumaUrHaberM + $datos[$key]['pdx_haberPeriodo'];
                    $sumaUrDebeF  = $sumaUrDebeF + $datos[$key]['pdx_debeFinal'];
                    $sumaUrHaberF = $sumaUrHaberF + $datos[$key]['pdx_haberFinal'];
                }

                $registros[$key] = $datos[$key];
                $ur_cuenta       = $registro['cta_gpo_id'] . $registro['cta_ur_id'];
            }

            $registros[$ur_cuenta]                     = $registro;
            $registros[$ur_cuenta]['pdx_debeInicial']  = $sumaUrDebeI;
            $registros[$ur_cuenta]['pdx_haberInicial'] = $sumaUrHaberI;
            $registros[$ur_cuenta]['pdx_debePeriodo']  = $sumaUrDebeM;
            $registros[$ur_cuenta]['pdx_haberPeriodo'] = $sumaUrHaberM;
            $registros[$ur_cuenta]['pdx_debeFinal']    = $sumaUrDebeF;
            $registros[$ur_cuenta]['pdx_haberFinal']   = $sumaUrHaberF;

            //guardamos el resultado en el arreglo original
            $datos = $registros;
        }

        //guardamos el resultado en el arreglo original
        $datos = array_reverse($datos, true);

        foreach ($datos as $key => $value) {
            $datos[$key]['pdx_debeInicial']  = ($datos[$key]['pdx_debeInicial'] / $divisor);
            $datos[$key]['pdx_haberInicial'] = ($datos[$key]['pdx_haberInicial'] / $divisor);
            $datos[$key]['pdx_debePeriodo']  = ($datos[$key]['pdx_debePeriodo'] / $divisor);
            $datos[$key]['pdx_haberPeriodo'] = ($datos[$key]['pdx_haberPeriodo'] / $divisor);
            $datos[$key]['pdx_debeFinal']    = ($datos[$key]['pdx_debeFinal'] / $divisor);
            $datos[$key]['pdx_haberFinal']   = ($datos[$key]['pdx_haberFinal'] / $divisor);
        }

        $datos['total']['id']               = 'total';
        $datos['total']['ur_clave']         = '';
        $datos['total']['ur_nombre']        = '';
        $datos['total']['cta_gpo_id']       = '';
        $datos['total']['cta_ur_id']        = '';
        $datos['total']['cta_ue_id']        = '';
        $datos['total']['ger_cuenta']       = '';
        $datos['total']['ger_nombre']       = '';
        $datos['total']['cgp_cuenta']       = '';
        $datos['total']['cgp_nombre']       = '';
        $datos['total']['rub_cuenta']       = '';
        $datos['total']['rub_nombre']       = '';
        $datos['total']['cgcu_cuenta']      = '';
        $datos['total']['cgcu_nombre']      = '';
        $datos['total']['cgsc_cuenta']      = '';
        $datos['total']['cgsc_nombre']      = '';
        $datos['total']['cgc_cuenta']       = '';
        $datos['total']['cgc_nombre']       = '';
        $datos['total']['cgq_cuenta']       = '';
        $datos['total']['cgq_nombre']       = '';
        $datos['total']['cta_cuenta']       = '';
        $datos['total']['cta_nombre']       = '';
        $datos['total']['cuenta']           = '';
        $datos['total']['nombre']           = 'TOTAL';
        $datos['total']['cgm_naturaleza']   = '';
        $datos['total']['pdx_debeInicial']  = redondear($sumaDebeInicial, 2);
        $datos['total']['pdx_haberInicial'] = redondear($sumaHaberInicial, 2);
        $datos['total']['pdx_debePeriodo']  = redondear($sumaDebePeriodo, 2);
        $datos['total']['pdx_haberPeriodo'] = redondear($sumaHaberPeriodo, 2);
        $datos['total']['pdx_debeFinal']    = redondear($sumaDebeFinal, 2);
        $datos['total']['pdx_haberFinal']   = redondear($sumaHaberFinal, 2);
    } else {

        $datos['vacio']['id']               = 'vacio';
        $datos['vacio']['ur_clave']         = '';
        $datos['vacio']['ur_nombre']        = '';
        $datos['vacio']['cta_gpo_id']       = '';
        $datos['vacio']['cta_ur_id']        = '';
        $datos['vacio']['cta_ue_id']        = '';
        $datos['vacio']['ger_cuenta']       = '';
        $datos['vacio']['ger_nombre']       = '';
        $datos['vacio']['cgp_cuenta']       = '';
        $datos['vacio']['cgp_nombre']       = '';
        $datos['vacio']['rub_cuenta']       = '';
        $datos['vacio']['rub_nombre']       = '';
        $datos['vacio']['cgcu_cuenta']      = '';
        $datos['vacio']['cgcu_nombre']      = '';
        $datos['vacio']['cgsc_cuenta']      = '';
        $datos['vacio']['cgsc_nombre']      = '';
        $datos['vacio']['cgc_cuenta']       = '';
        $datos['vacio']['cgc_nombre']       = '';
        $datos['vacio']['cgq_cuenta']       = '';
        $datos['vacio']['cgq_nombre']       = '';
        $datos['vacio']['cta_cuenta']       = '';
        $datos['vacio']['cta_nombre']       = '';
        $datos['vacio']['cuenta']           = '';
        $datos['vacio']['nombre']           = 'NO EXISTE INFORMACIÓN';
        $datos['vacio']['cgm_naturaleza']   = '';
        $datos['vacio']['pdx_debeInicial']  = 0;
        $datos['vacio']['pdx_haberInicial'] = 0;
        $datos['vacio']['pdx_debePeriodo']  = 0;
        $datos['vacio']['pdx_haberPeriodo'] = 0;
        $datos['vacio']['pdx_debeFinal']    = 0;
        $datos['vacio']['pdx_haberFinal']   = 0;
        $datos['vacio']['nivel']            = 0;
    }

    return isset($datos) ? $datos : false;
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
