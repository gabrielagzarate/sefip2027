<?php
require_once dirname(__FILE__) . '/../../redondear.php';
require_once dirname(__FILE__) . '/sql_situacionFinanciera.php';
require_once dirname(__FILE__) . '/lib_sincronizarSituacionFinanciera.php';
require_once dirname(__FILE__) . '/lib_nivelSituacionFinanciera.php';
require_once dirname(__DIR__) . '/conciliacion/consolidacion.php';
function lib_situacionFinanciera($ent_id, $msc_trimestre, $consolidado = false, $ent_idC = '', $sec_idC = '', $gpo_idC = '', $con_idC = '', $parametroExtra = array())
{
    $ejercicioActual = $_SESSION[_S]['ejercicioActual'];
    $ejercicioAnterior = $ejercicioActual - 1;

    $tipoReporte = (isset($parametroExtra['tipoReporte']) ? $parametroExtra['tipoReporte'] : '');
    $ue = (isset($parametroExtra['ue']) ? $parametroExtra['ue'] : '');
    $grupos = (isset($parametroExtra['grupos']) ? $parametroExtra['grupos'] : '');
    $excluir = (isset($parametroExtra['excluir']) ? $parametroExtra['excluir'] : '');
    $checkMiles = (isset($parametroExtra['ckmiles'])) ? 1000 : 1;
    $ceros = 1;
    $incCta = 0;

    ############ obtenemos la información de la base de datos

    ///obtiene la informacion de Activo Circulante
    $activoTrimestralEjActual = sql_situacionFinanciera(
        $ent_id,
        $msc_trimestre,
        $ejercicioActual,
        $consolidado,
        $ent_idC,
        $sec_idC,
        $gpo_idC,
        $con_idC,
        array("gpo_id" => $_SESSION[_S]['clasAdm-gpo'], "ur_id" => $_SESSION[_S]['clasAdm-ur'], "ue" => $ue,"ger_id" => 1, "nivel" => 4, "tipoReporte" => $tipoReporte, "ue" => $ue, "grupos" => $grupos, "excluir" => $excluir)
    );
    $activoAnualEjAnterior = sql_situacionFinanciera(
        $ent_id,
        4,
        $ejercicioAnterior,
        $consolidado,
        $ent_idC,
        $sec_idC,
        $gpo_idC,
        $con_idC,
        array("gpo_id" => $_SESSION[_S]['clasAdm-gpo'], "ur_id" => $_SESSION[_S]['clasAdm-ur'], "ue" => $ue,"ger_id" => 1, "nivel" => 4, "tipoReporte" => $tipoReporte, "ue" => $ue, "grupos" => $grupos, "excluir" => $excluir)
    );

    ///obtiene la informacion de Pasivo  Circulante
    $pasivoTrimestralEjActual = sql_situacionFinanciera(
        $ent_id,
        $msc_trimestre,
        $ejercicioActual,
        $consolidado,
        $ent_idC,
        $sec_idC,
        $gpo_idC,
        $con_idC,
        array("gpo_id" => $_SESSION[_S]['clasAdm-gpo'], "ur_id" => $_SESSION[_S]['clasAdm-ur'], "ue" => $ue,"ger_id" => 2, "nivel" => 4, "tipoReporte" => $tipoReporte, "ue" => $ue, "grupos" => $grupos, "excluir" => $excluir)
    );
    $pasivoAnualEjAnterior = sql_situacionFinanciera(
        $ent_id,
        4,
        $ejercicioAnterior,
        $consolidado,
        $ent_idC,
        $sec_idC,
        $gpo_idC,
        $con_idC,
        array("gpo_id" => $_SESSION[_S]['clasAdm-gpo'], "ur_id" => $_SESSION[_S]['clasAdm-ur'], "ue" => $ue,"ger_id" => 2, "nivel" => 4, "tipoReporte" => $tipoReporte, "ue" => $ue, "grupos" => $grupos, "excluir" => $excluir)
    );

    ///obtiene la informacion de Hacienda Publica/Patrimonial
    $haciendaTrimestralEjActual = sql_situacionFinanciera(
        $ent_id,
        $msc_trimestre,
        $ejercicioActual,
        $consolidado,
        $ent_idC,
        $sec_idC,
        $gpo_idC,
        $con_idC,
        array("gpo_id" => $_SESSION[_S]['clasAdm-gpo'], "ur_id" => $_SESSION[_S]['clasAdm-ur'], "ue" => $ue,"ger_id" => 3, "nivel" => 3, "tipoReporte" => $tipoReporte, "ue" => $ue, "grupos" => $grupos, "excluir" => $excluir)
    );
    $haciendaAnualEjAnterior = sql_situacionFinanciera(
        $ent_id,
        4,
        $ejercicioAnterior,
        $consolidado,
        $ent_idC,
        $sec_idC,
        $gpo_idC,
        $con_idC,
        array("gpo_id" => $_SESSION[_S]['clasAdm-gpo'], "ur_id" => $_SESSION[_S]['clasAdm-ur'], "ue" => $ue,"ger_id" => 3, "nivel" => 3, "tipoReporte" => $tipoReporte, "ue" => $ue, "grupos" => $grupos, "excluir" => $excluir)
    );

    /******************* */
    switch ($msc_trimestre) {
        case 1:
            $msc_trimestre = '03';
            break;
        case 2:
            $msc_trimestre = '06';
            break;
        case 3:
            $msc_trimestre = '09';
            break;
        case 4:
            $msc_trimestre = '12';
            break;
    }
    /********************** */
    list($ingresosActuales) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 01, $msc_trimestre, 4, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4), $checkMiles, $grupos, $excluir, $ceros, $incCta);
    list($gastosActuales) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 01, $msc_trimestre, 5, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4), $checkMiles, $grupos, $excluir, $ceros, $incCta);
    $totalIngresoActual = (($ingresosActuales[4]['naturalezaCuenta'] == 'D') ? ($ingresosActuales[4]['debe'] - $ingresosActuales[4]['haber']) : ($ingresosActuales[4]['haber'] - $ingresosActuales[4]['debe']));
    $totalGastoActual = (($gastosActuales[5]['naturalezaCuenta'] == 'D') ? ($gastosActuales[5]['debe'] - $gastosActuales[5]['haber']) : ($gastosActuales[5]['haber'] - $gastosActuales[5]['debe']));
    $ingresoGtoActual = $totalIngresoActual - $totalGastoActual;
    
    /********************** */
    list($ingresosAnteriores) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 01, 12, 4, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4) - 1, $checkMiles, $grupos, $excluir, $ceros, $incCta);
    list($gastosAnteriores) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 01, 12, 5, $tipoReporte, substr($_SESSION[_S]['ejercicio'], 0, 4) - 1, $checkMiles, $grupos, $excluir, $ceros, $incCta);
    $totalIngresoAnterior = (($ingresosAnteriores[4]['naturalezaCuenta'] == 'D') ? ($ingresosAnteriores[4]['debe'] - $ingresosAnteriores[4]['haber']) : ($ingresosAnteriores[4]['haber'] - $ingresosAnteriores[4]['debe']));
    $totalGastoAnterior = (($gastosAnteriores[5]['naturalezaCuenta'] == 'D') ? ($gastosAnteriores[5]['debe'] - $gastosAnteriores[5]['haber']) : ($gastosAnteriores[5]['haber'] - $gastosAnteriores[5]['debe']));
    $ingresoGtoAnterior = $totalIngresoAnterior - $totalGastoAnterior;

    ######### generamos 
    $arrayActivo = lib_sincronizarSituacionFinanciera($activoTrimestralEjActual, $activoAnualEjAnterior);
    $arrayPasivo = lib_sincronizarSituacionFinanciera($pasivoTrimestralEjActual , $pasivoAnualEjAnterior);
    $arrayHacienda = lib_sincronizarSituacionFinanciera($haciendaTrimestralEjActual, $haciendaAnualEjAnterior);

    $arrayIzquierda = lib_nivelSituacionFinanciera($arrayActivo, 4);
    unset($arrayIzquierda['C.1.1.5.1']);
    unset($arrayIzquierda['C.1.2.1.1']);
    unset($arrayIzquierda['C.1.2.1.2']);
    unset($arrayIzquierda['C.1.2.1.3']);
    unset($arrayIzquierda['C.1.2.1.4']);
    unset($arrayIzquierda['C.1.2.2.1']);
    unset($arrayIzquierda['C.1.2.2.2']);
    unset($arrayIzquierda['C.1.2.2.3']);
    unset($arrayIzquierda['C.1.2.2.4']);
    unset($arrayIzquierda['C.1.2.2.9']);
    unset($arrayIzquierda['C.1.2.3.1']);
    unset($arrayIzquierda['C.1.2.3.2']);
    unset($arrayIzquierda['C.1.2.3.3']);
    unset($arrayIzquierda['C.1.2.3.4']);
    unset($arrayIzquierda['C.1.2.3.5']);
    unset($arrayIzquierda['C.1.2.3.6']);
    unset($arrayIzquierda['C.1.2.3.9']);
    unset($arrayIzquierda['C.1.2.4.1']);
    unset($arrayIzquierda['C.1.2.4.2']);
    unset($arrayIzquierda['C.1.2.4.3']);
    unset($arrayIzquierda['C.1.2.4.4']);
    unset($arrayIzquierda['C.1.2.4.5']);
    unset($arrayIzquierda['C.1.2.4.6']);
    unset($arrayIzquierda['C.1.2.4.7']);
    unset($arrayIzquierda['C.1.2.4.8']);
    unset($arrayIzquierda['C.1.2.5.1']);
    unset($arrayIzquierda['C.1.2.5.2']);
    unset($arrayIzquierda['C.1.2.5.3']);
    unset($arrayIzquierda['C.1.2.5.4']);
    unset($arrayIzquierda['C.1.2.5.9']);
    unset($arrayIzquierda['C.1.2.6.1']);
    unset($arrayIzquierda['C.1.2.6.2']);
    unset($arrayIzquierda['C.1.2.6.3']);
    unset($arrayIzquierda['C.1.2.6.4']);
    unset($arrayIzquierda['C.1.2.6.5']);
    unset($arrayIzquierda['C.1.2.7.1']);
    unset($arrayIzquierda['C.1.2.7.2']);
    unset($arrayIzquierda['C.1.2.7.3']);
    unset($arrayIzquierda['C.1.2.7.4']);
    unset($arrayIzquierda['C.1.2.7.5']);
    unset($arrayIzquierda['C.1.2.7.9']);
    unset($arrayIzquierda['C.1.2.8.1']);
    unset($arrayIzquierda['C.1.2.8.2']);
    unset($arrayIzquierda['C.1.2.8.3']);
    unset($arrayIzquierda['C.1.2.8.4']);
    unset($arrayIzquierda['C.1.2.8.9']);
    unset($arrayIzquierda['C.1.2.9.1']);
    unset($arrayIzquierda['C.1.2.9.2']);
    unset($arrayIzquierda['C.1.2.9.3']);

    $nuevoArray = [];
    $agregado = false;

    foreach ($arrayIzquierda as $key => $value) {
        $nuevoArray[$key] = $value;
        $nuevoArray[$key]['nombre'] = formatoCadena($value['nombre']);
        if ($key === 'C.1.1.9.4' && !$agregado) {
            $nuevoArray['C.IA'] = [
                'id' => 'IA',
                'ger_cuenta' => '1',
                'ger_nombre' => 'ACTIVO',
                'cgp_cuenta' => '1.1',
                'cgp_nombre' => 'ACTIVO CIRCULANTE',
                'rub_cuenta' => 'IA',
                'rub_nombre' => 'TOTAL DE ACTIVOS CIRCULANTES',
                'cgcu_cuenta' => '',
                'cgcu_nombre' => '',
                'cgsc_cuenta' => '',
                'cgsc_nombre' => '',
                'cuenta' => 'IA',
                'nombre' => 'Total de Activos Circulantes',
                'nat_id' => 'D',
                'nivel' => '2',
                'importeActual' => $arrayIzquierda['C.1.1']['importeActual'],
                'importeAnterior' => $arrayIzquierda['C.1.1']['importeAnterior']
            ];
            $agregado = true;
        }
    }

    $nuevoArray['C.IB'] = [
        'id' => 'IB',
        'ger_cuenta' => '1',
        'ger_nombre' => 'ACTIVO',
        'cgp_cuenta' => '1.2',
        'cgp_nombre' => 'ACTIVO NO CIRCULANTE',
        'rub_cuenta' => 'IB',
        'rub_nombre' => 'TOTAL DE ACTIVOS NO CIRCULANTES',
        'cgcu_cuenta' => '',
        'cgcu_nombre' => '',
        'cgsc_cuenta' => '',
        'cgsc_nombre' => '',
        'cuenta' => 'IB',
        'nombre' => 'Total de Activos No Circulantes',
        'nat_id' => 'D',
        'nivel' => '2',
        'importeActual' => $arrayIzquierda['C.1.2']['importeActual'],
        'importeAnterior' => $arrayIzquierda['C.1.2']['importeAnterior']
    ];

    $nuevoArray['C.I'] = [
        'id' => 'I',
        'ger_cuenta' => '1',
        'ger_nombre' => 'ACTIVO',
        'cgp_cuenta' => '',
        'cgp_nombre' => '',
        'rub_cuenta' => '',
        'rub_nombre' => '',
        'cgcu_cuenta' => '',
        'cgcu_nombre' => '',
        'cgsc_cuenta' => '',
        'cgsc_nombre' => '',
        'cuenta' => 'I',
        'nombre' => 'Total del Activo',
        'nat_id' => 'D',
        'nivel' => '1',
        'importeActual' => $arrayIzquierda['C.1']['importeActual'],
        'importeAnterior' => $arrayIzquierda['C.1']['importeAnterior']
    ];

    $nuevoArray['C.1']['nombre'] = 'ACTIVO';

    $arrayIzquierda = $nuevoArray;

    $arrayDerecha = lib_nivelSituacionFinanciera($arrayPasivo, 4) + lib_nivelSituacionFinanciera($arrayHacienda, 3);

    unset($arrayDerecha['C.2.1.3.2']);
    unset($arrayDerecha['C.2.1.4.1']);
    unset($arrayDerecha['C.2.1.4.2']);
    unset($arrayDerecha['C.2.2.1.1']);
    unset($arrayDerecha['C.2.2.1.2']);
    unset($arrayDerecha['C.2.2.2.1']);
    unset($arrayDerecha['C.2.2.2.2']);
    unset($arrayDerecha['C.2.2.2.9']);
    unset($arrayDerecha['C.2.2.3.1']);
    unset($arrayDerecha['C.2.2.3.2']);
    unset($arrayDerecha['C.2.2.3.3']);
    unset($arrayDerecha['C.2.2.3.4']);
    unset($arrayDerecha['C.2.2.3.5']);
    unset($arrayDerecha['C.2.2.4.1']);
    unset($arrayDerecha['C.2.2.4.2']);
    unset($arrayDerecha['C.2.2.4.9']);
    unset($arrayDerecha['C.2.2.5.1']);
    unset($arrayDerecha['C.2.2.5.2']);
    unset($arrayDerecha['C.2.2.5.3']);
    unset($arrayDerecha['C.2.2.5.4']);
    unset($arrayDerecha['C.2.2.5.5']);
    unset($arrayDerecha['C.2.2.5.6']);
    unset($arrayDerecha['C.2.2.6.1']);
    unset($arrayDerecha['C.2.2.6.2']);
    unset($arrayDerecha['C.2.2.6.3']);
    unset($arrayDerecha['C.2.2.6.9']);

    $arrayDerecha['C.2.1.3.1']['nombre'] = 'PORCIÓN A CORTO PLAZO DE LA DEUDA PÚBLICA';
    $arrayDerecha['C.2.1.3.1']['cuenta'] = '2.1.3.1';
    $arrayDerecha['C.2.1.3.1']['nivel'] = '4';
    $arrayDerecha['C.2.1.3.1']['importeActual'] = '0';
    $arrayDerecha['C.2.1.3.1']['importeAnterior'] = '0';

    $arrayDerecha['C.3.2.1']['nombre'] = 'Resultados del Ejercicio (Ahorro/Desahorro)';
    $arrayDerecha['C.3.2.1']['cuenta'] = '3.2.1';
    $arrayDerecha['C.3.2.1']['nivel'] = '3';
    $arrayDerecha['C.3.2.1']['importeActual'] = $ingresoGtoActual;
    $arrayDerecha['C.3.2.1']['importeAnterior'] = $ingresoGtoAnterior;

    $nuevoArray = [];
    $agregadoPasivo = false;
    $agregadoHacienda = false;

    foreach ($arrayDerecha as $key => $value) {
        $nuevoArray[$key] = $value;
        $nuevoArray[$key]['nombre'] = formatoCadena($value['nombre']);
        if ($key === 'C.2.1.9.9' && !$agregadoPasivo) {
            $nuevoArray['C.IIA'] = [
                'id' => 'IIA',
                'ger_cuenta' => '2',
                'ger_nombre' => 'PASIVO',
                'cgp_cuenta' => '2.1',
                'cgp_nombre' => 'PASIVO CIRCULANTE',
                'rub_cuenta' => '',
                'rub_nombre' => '',
                'cgcu_cuenta' => '',
                'cgcu_nombre' => '',
                'cgsc_cuenta' => '',
                'cgsc_nombre' => '',
                'cuenta' => 'IIA',
                'nombre' => 'Total de Pasivos Circulantes',
                'nat_id' => 'H',
                'nivel' => '2',
                'importeActual' => $arrayDerecha['C.2.1']['importeActual'],
                'importeAnterior' => $arrayDerecha['C.2.1']['importeAnterior']
            ];
            $agregadoPasivo = true;
        } elseif ($key === 'C.2.2.6' && !$agregadoHacienda) {
            $nuevoArray['C.IIB'] = [
                'id' => 'IIB',
                'ger_cuenta' => '2',
                'ger_nombre' => 'PASIVO',
                'cgp_cuenta' => '2.2',
                'cgp_nombre' => 'PASIVO NO CIRCULANTE',
                'rub_cuenta' => '',
                'rub_nombre' => '',
                'cgcu_cuenta' => '',
                'cgcu_nombre' => '',
                'cgsc_cuenta' => '',
                'cgsc_nombre' => '',
                'cuenta' => 'IIB',
                'nombre' => 'Total de Pasivos No Circulantes',
                'nat_id' => 'H',
                'nivel' => '2',
                'importeActual' => $arrayDerecha['C.2.2']['importeActual'],
                'importeAnterior' => $arrayDerecha['C.2.2']['importeAnterior']
            ];
            $nuevoArray['C.II'] = [
                'id' => 'II',
                'ger_cuenta' => '2',
                'ger_nombre' => 'PASIVO',
                'cgp_cuenta' => '',
                'cgp_nombre' => '',
                'rub_cuenta' => '',
                'rub_nombre' => '',
                'cgcu_cuenta' => '',
                'cgcu_nombre' => '',
                'cgsc_cuenta' => '',
                'cgsc_nombre' => '',
                'cuenta' => 'II',
                'nombre' => 'Total del Pasivo',
                'nat_id' => 'H',
                'nivel' => '1',
                'importeActual' => $arrayDerecha['C.2']['importeActual'],
                'importeAnterior' => $arrayDerecha['C.2']['importeAnterior']
            ];
            $agregadoHacienda = true;
        }
    }

    $nuevoArray['C.III'] = [
        'id' => 'III',
        'ger_cuenta' => '3',
        'ger_nombre' => 'HACIENDA PÚBLICA/PATRIMONIO',
        'cgp_cuenta' => '',
        'cgp_nombre' => '',
        'rub_cuenta' => '',
        'rub_nombre' => '',
        'cgcu_cuenta' => '',
        'cgcu_nombre' => '',
        'cgsc_cuenta' => '',
        'cgsc_nombre' => '',
        'cuenta' => 'III',
        'nombre' => utf8Decode('Total Hacienda Pública/Patrimonio'),
        'nat_id' => 'H',
        'nivel' => '1',
        'importeActual' => $arrayDerecha['C.3']['importeActual'] + $ingresoGtoActual,
        'importeAnterior' => $arrayDerecha['C.3']['importeAnterior'] + $ingresoGtoAnterior
    ];

    $nuevoArray['C.IV'] = [
        'id' => 'IV',
        'ger_cuenta' => '3',
        'ger_nombre' => 'HACIENDA PÚBLICA/PATRIMONIO',
        'cgp_cuenta' => '',
        'cgp_nombre' => '',
        'rub_cuenta' => '',
        'rub_nombre' => '',
        'cgcu_cuenta' => '',
        'cgcu_nombre' => '',
        'cgsc_cuenta' => '',
        'cgsc_nombre' => '',
        'cuenta' => 'IV',
        'nombre' => utf8Decode('Total del Pasivo y Hacienda Pública/Patrimonio'),
        'nat_id' => 'H',
        'nivel' => '1',
        'importeActual' => $arrayDerecha['C.2']['importeActual'] + $arrayDerecha['C.3']['importeActual'] + $ingresoGtoActual,
        'importeAnterior' => $arrayDerecha['C.2']['importeAnterior'] + $arrayDerecha['C.3']['importeAnterior'] + $ingresoGtoAnterior
    ];

    $nuevoArray['C.2']['nombre'] = 'PASIVO';
    $nuevoArray['C.3']['nombre'] = utf8Decode('HACIENDA PÚBLICA/PATRIMONIO');

    $arrayDerecha = $nuevoArray;

    /*
    echo "<pre>";
    print_r($arrayDerecha);
    echo "</pre>";
    */

    $ind = 0;

    foreach (array_keys($arrayIzquierda) as $key) {
        $datosIzquierda['C.' . $ind]['cuentaIzquierda'] = $arrayIzquierda[$key]['cuenta'];
        $datosIzquierda['C.' . $ind]['nombreIzquierda'] = $arrayIzquierda[$key]['nombre'];
        $datosIzquierda['C.' . $ind]['importeActualIzquierda'] = $arrayIzquierda[$key]['importeActual'];
        $datosIzquierda['C.' . $ind]['importeAnteriorIzquierda'] = $arrayIzquierda[$key]['importeAnterior'];
        $datosIzquierda['C.' . $ind]['nivelIzquierda'] = $arrayIzquierda[$key]['nivel'];
        $ind++;
    }

    $ind = 0;

    foreach (array_keys($arrayDerecha) as $key) {
        $datosDerecha['C.' . $ind]['cuentaDerecha'] = $arrayDerecha[$key]['cuenta'];
        $datosDerecha['C.' . $ind]['nombreDerecha'] = $arrayDerecha[$key]['nombre'];
        $datosDerecha['C.' . $ind]['importeActualDerecha'] = $arrayDerecha[$key]['importeActual'];
        $datosDerecha['C.' . $ind]['importeAnteriorDerecha'] = $arrayDerecha[$key]['importeAnterior'];
        $datosDerecha['C.' . $ind]['nivelDerecha'] = $arrayDerecha[$key]['nivel'];
        $ind++;
    }

    $datos = array_merge(array_fill_keys(array_keys($datosIzquierda), ''), array_fill_keys(array_keys($datosDerecha), ''));

    $totalImporteActualIzquierda = 0;
    $totalImporteAnteriorIzquierda = 0;
    $totalImporteActualDerecha = 0;
    $totalImporteAnteriorDerecha = 0;

    foreach (array_keys($datos) as $ind) {
        $datos[$ind] = array();

        if (isset($datosIzquierda[$ind])) {
            $datos[$ind] = $datosIzquierda[$ind];
            if ($datos[$ind]['nivelIzquierda'] == 1) {
                $totalImporteActualIzquierda = $totalImporteActualIzquierda + $datosIzquierda[$ind]['importeActualIzquierda'];
                $totalImporteAnteriorIzquierda = $totalImporteAnteriorIzquierda + $datosIzquierda[$ind]['importeAnteriorIzquierda'];
            }
        } else {
            $datos[$ind]['cuentaIzquierda'] = '';
            $datos[$ind]['nombreIzquierda'] = '';
            $datos[$ind]['importeActualIzquierda'] = '';
            $datos[$ind]['importeAnteriorIzquierda'] = '';
            $datos[$ind]['nivelIzquierda'] = '';
        }

        if (isset($datosDerecha[$ind])) {
            $datos[$ind] = $datos[$ind] + $datosDerecha[$ind];
            if ($datos[$ind]['nivelDerecha'] == 1) {
                $totalImporteActualDerecha = $totalImporteActualDerecha + $datosDerecha[$ind]['importeActualDerecha'];
                $totalImporteAnteriorDerecha = $totalImporteAnteriorDerecha + $datosDerecha[$ind]['importeAnteriorDerecha'];
            }
        } else {
            $datos[$ind]['cuentaDerecha'] = '';
            $datos[$ind]['nombreDerecha'] = '';
            $datos[$ind]['importeActualDerecha'] = '';
            $datos[$ind]['importeAnteriorDerecha'] = '';
            $datos[$ind]['nivelDerecha'] = '';
        }
    }

    return isset($datos) ? $datos : false;
}

function formatoCadena($cadena): string
{
    $cadenaFormateada = ucwords(mb_strtolower($cadena, 'UTF-8'));
    $subStrBuscar = array(' Y/O ', ' Y/o ', ' A ', ' Y ', ' O ', ' De ', ' La ', ' Del ', ' En ', ' Con ', ' Por ', ' Para ', 's/t', 's/d', 'a/p');
    $reemplazaSubStr = array(' y/o ', ' y/o ', ' a ', ' y ', ' o ', ' de ', ' la ', ' del ', ' en ', ' con ', ' por ', ' para ', 's/T', 's/D', 'a/P');
    $cadenaFormateada = str_replace($subStrBuscar, $reemplazaSubStr, $cadenaFormateada);
    return utf8Decode($cadenaFormateada);
}
