<?php

    
/*
    Regresa un array con el formato del Estado de Variación de la Hacienda Pública
    este array es la base de la impresión en PDF y la exportación a Excel, ya que en ambos
    casos se ocupa exactamente el mismo.
 */


function get_machote_estado_variacion() {

    $ejercicio_actual = $_SESSION[_S]['ejercicioActual'];
    $ejercicio_anterior = $_SESSION[_S]['ejercicioActual'] -1;

    return [

        "ant_31" => [ 
            "niv1" => "Hacienda Pública/Patrimonio Contribuido Neto de " . $ejercicio_anterior,
            "niv2" => "",
            "col1" => "",
            "col2" => "",
            "col3" => "",
            "col4" => "",
            "col5" => ""
        ],
            "ant_311" => [ 
                "niv1" => "",
                "niv2" => "Aportaciones",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
            "ant_312" => [ 
                "niv1" => "",
                "niv2" => "Donaciones de Capital",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
            "ant_313" => [ 
                "niv1" => "",
                "niv2" => "Actualización de la Hacienda Pública/Patrimonio",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
        "ant_32" => [ 
            "niv1" => "Hacienda Pública/Patrimonio Generado Neto de " . $ejercicio_anterior,
            "niv2" => "",
            "col1" => "",
            "col2" => "",
            "col3" => "",
            "col4" => "",
            "col5" => ""
        ],
            "ant_321" => [ 
                "niv1" => "",
                "niv2" => "Resultado del Ejercicio (Ahorro/Desahorro)",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
            "ant_322" => [ 
                "niv1" => "",
                "niv2" => "Resultados de Ejercicios Anteriores",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
            "ant_323" => [ 
                "niv1" => "",
                "niv2" => "Revalúos",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
            "ant_324" => [ 
                "niv1" => "",
                "niv2" => "Reservas",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
            "ant_325" => [ 
                "niv1" => "",
                "niv2" => "Rectificaciones de Resultados de Ejercicios Anteriores",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
        "ant_33" => [ 
            "niv1" => "Exceso o Insuficiencia en la Actualización de la Hacienda Pública/Patrimonio Neto de " . $ejercicio_anterior,
            "niv2" => "",
            "col1" => "",
            "col2" => "",
            "col3" => "",
            "col4" => "",
            "col5" => ""
        ],
            "ant_331" => [ 
                "niv1" => "",
                "niv2" => "Resultado por Posición Monetaria",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
            "ant_332" => [ 
                "niv1" => "",
                "niv2" => "Resultado por Tenencia de Activos no Monetarios",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
        "sep1" => [ 
            "niv1" => "",
            "niv2" => "",
            "col1" => "",
            "col2" => "",
            "col3" => "",
            "col4" => "",
            "col5" => ""
        ],
        "sub_anterior" => [ 
            "niv1" => "Hacienda Pública/Patrimonio Neto Final de " . $ejercicio_anterior,
            "niv2" => "",
            "col1" => "",
            "col2" => "",
            "col3" => "",
            "col4" => "",
            "col5" => ""
        ],
        "sep2" => [ 
            "niv1" => "",
            "niv2" => "",
            "col1" => "",
            "col2" => "",
            "col3" => "",
            "col4" => "",
            "col5" => ""
        ],

        "act_31" => [ 
            "niv1" => "Cambios en la Hacienda Pública/Patrimonio Contribuido Neto de " . $ejercicio_actual,
            "niv2" => "",
            "col1" => "",
            "col2" => "",
            "col3" => "",
            "col4" => "",
            "col5" => ""
        ],
            "act_311" => [ 
                "niv1" => "",
                "niv2" => "Aportaciones",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
            "act_312" => [ 
                "niv1" => "",
                "niv2" => "Donaciones de Capital",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
            "act_313" => [ 
                "niv1" => "",
                "niv2" => "Actualización de la Hacienda Pública/Patrimonio",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
        "act_32" => [ 
            "niv1" => "Variaciones de la Hacienda Pública/Patrimonio Generado Neto de " . $ejercicio_actual,
            "niv2" => "",
            "col1" => "",
            "col2" => "",
            "col3" => "",
            "col4" => "",
            "col5" => ""
        ],
            "act_321" => [ 
                "niv1" => "",
                "niv2" => "Resultado del Ejercicio (Ahorro/Desahorro)",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
            "act_322" => [ 
                "niv1" => "",
                "niv2" => "Resultados de Ejercicios Anteriores",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
            "act_323" => [ 
                "niv1" => "",
                "niv2" => "Revalúos",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
            "act_324" => [ 
                "niv1" => "",
                "niv2" => "Reservas",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
            "act_325" => [ 
                "niv1" => "",
                "niv2" => "Rectificaciones de Resultados de Ejercicios Anteriores",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
        "act_33" => [ 
            "niv1" => "Cambios en el Exceso o Insuficiencia en la Actualización de la Hacienda Pública/Patrimonio Neto de " . $ejercicio_actual,
            "niv2" => "",
            "col1" => "",
            "col2" => "",
            "col3" => "",
            "col4" => "",
            "col5" => ""
        ],
            "act_331" => [ 
                "niv1" => "",
                "niv2" => "Resultado por Posición Monetaria",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
            "act_332" => [ 
                "niv1" => "",
                "niv2" => "Resultado por Tenencia de Activos no Monetarios",
                "col1" => "",
                "col2" => "",
                "col3" => "",
                "col4" => "",
                "col5" => ""
            ],
        "sep3" => [ 
            "niv1" => "",
            "niv2" => "",
            "col1" => "",
            "col2" => "",
            "col3" => "",
            "col4" => "",
            "col5" => ""
        ],
        "total" => [ 
            "niv1" => "Hacienda Pública/Patrimonio Neto Final de " . $ejercicio_actual,
            "niv2" => "",
            "col1" => "",
            "col2" => "",
            "col3" => "",
            "col4" => "",
            "col5" => ""
        ]
                    
    ];


}



function rellena_variacion_hacienda( &$datos, $mesDesde, $mesHasta, $tipoReporte, $MilPesos, $grupos, $excluir, $ue, $incCta, $ceros ) {

    $ejercicio_actual = $_SESSION[_S]['ejercicioActual'];
    $ejercicio_anterior = $_SESSION[_S]['ejercicioActual'] -1;


    // OJO:
    // Según las definiciones de contabilidad, ésto no compara contra el ejercicio anterior
    // sino contra el periodo anterior del mismo ejercicio... así el estado de variación de enero
    // compara contra el saldo inicial de enero (o final de apertura)
    // el estado de mayo compara contra los saldos iniciales de mayo (o finales de abril)
    // 
    // Contabilidad dixit:
    // "En principio, este reporte no es comparativo con periodos de ejercicios anteriores, 
    // sólo muestra un análisis de la situación del periodo anterior, y su objetivo es 
    // mostrar la variación entre el periodo anterior y el último corte o fecha final del reporte.
    // 
    // En ese sentido, el periodo anterior deberá ser siempre el mes inmediato anterior, 
    // y no el mismo mes del ejercicio anterior como sucede en estados como el de 
    // Situación Financiera o el de Actividades"

    // Obtenemos los datos
    // --------------------------------------------------------------------------------
    list($datos_ejercicio_actual) = consolidacion( 
        $_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'], $ue, 
        $mesDesde, $mesHasta, 3, 
        $tipoReporte, $ejercicio_actual, $MilPesos, $grupos, $excluir, $ceros,$incCta
    );

    // Para la variación, necesitamos el saldo del mes inmediato anterior, del ejercicio actual
    list($datos_ejercicio_actual_mes_anterior) = consolidacion( 
        $_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'], $ue, 
        0, $mesDesde -1, 3, 
        $tipoReporte, $ejercicio_actual, $MilPesos, $grupos, $excluir, $ceros,$incCta
    );

    // Para el resultado del ejercicio, lo obtenemos aplicando las reglas del 
    // Estado de Actividades DEL PERIODO SOLICITADO
    list($ingresos) = consolidacion(
        $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 
        $mesDesde, $mesHasta, 4, 
        $tipoReporte, $ejercicio_actual, $MilPesos, $grupos, $excluir, $ceros,$incCta
    );
    list($gastos) = consolidacion(
        $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 
        $mesDesde, $mesHasta, 5, 
        $tipoReporte, $ejercicio_actual, $MilPesos, $grupos, $excluir, $ceros,$incCta
    );    

    //obtenemos resultado del ejercicio (ahorro/desahorro)
    $ingresos_periodo = ( $ingresos[4]['haber'] - $ingresos[4]['debe'] );
    $gastos_periodo   = ( $gastos[5]['debe'] - $gastos[5]['haber'] );
    $resultado_ejercicio = ( $ingresos_periodo - $gastos_periodo );

    // rellenamos el esqueleto con sus importes
    // --------------------------------------------------------------------------------

    // Ejercicio Anterior

    // Obtenemos algunos importes de sumatorias especiales
    $suma_IB = number_format( 
        dame_saldo( $datos_ejercicio_actual_mes_anterior, '322', $MilPesos ) + 
        dame_saldo( $datos_ejercicio_actual_mes_anterior, '323', $MilPesos ) + 
        dame_saldo( $datos_ejercicio_actual_mes_anterior, '324', $MilPesos ) + 
        dame_saldo( $datos_ejercicio_actual_mes_anterior, '325', $MilPesos )
    , 0 );

    $datos['ant_31' ]['col1'] = $datos['ant_31' ]['col5'] = dame_saldo_con_formato( $datos_ejercicio_actual_mes_anterior, '31',  $MilPesos );
    $datos['ant_311']['col1'] = $datos['ant_311']['col5'] = dame_saldo_con_formato( $datos_ejercicio_actual_mes_anterior, '311', $MilPesos );
    $datos['ant_312']['col1'] = $datos['ant_312']['col5'] = dame_saldo_con_formato( $datos_ejercicio_actual_mes_anterior, '312', $MilPesos );
    $datos['ant_313']['col1'] = $datos['ant_313']['col5'] = dame_saldo_con_formato( $datos_ejercicio_actual_mes_anterior, '313', $MilPesos );

    $datos['ant_32']['col2']  = $suma_IB;
    $datos['ant_32']['col3']  = $datos['ant_321']['col5'] = dame_saldo_con_formato( $datos_ejercicio_actual_mes_anterior, '321', $MilPesos );
    $datos['ant_32']['col5']  = $datos['ant_32']['col5']  = dame_saldo_con_formato( $datos_ejercicio_actual_mes_anterior, '32',  $MilPesos );

    $datos['ant_321']['col3'] = $datos['ant_321']['col5'] = dame_saldo_con_formato( $datos_ejercicio_actual_mes_anterior, '321', $MilPesos );
    $datos['ant_322']['col2'] = $datos['ant_322']['col5'] = dame_saldo_con_formato( $datos_ejercicio_actual_mes_anterior, '322', $MilPesos );
    $datos['ant_323']['col2'] = $datos['ant_323']['col5'] = dame_saldo_con_formato( $datos_ejercicio_actual_mes_anterior, '323', $MilPesos );
    $datos['ant_324']['col2'] = $datos['ant_324']['col5'] = dame_saldo_con_formato( $datos_ejercicio_actual_mes_anterior, '324', $MilPesos );
    $datos['ant_325']['col2'] = $datos['ant_325']['col5'] = dame_saldo_con_formato( $datos_ejercicio_actual_mes_anterior, '325', $MilPesos );

    $datos['ant_33' ]['col4'] = $datos['ant_33' ]['col5'] = dame_saldo_con_formato( $datos_ejercicio_actual_mes_anterior, '33',  $MilPesos );
    $datos['ant_331']['col4'] = $datos['ant_331']['col5'] = dame_saldo_con_formato( $datos_ejercicio_actual_mes_anterior, '331', $MilPesos );
    $datos['ant_332']['col4'] = $datos['ant_332']['col5'] = dame_saldo_con_formato( $datos_ejercicio_actual_mes_anterior, '332', $MilPesos );

    // totales de ejercicio anterior
    $datos['sub_anterior']['col1'] = $datos['ant_31']['col1'];
    $datos['sub_anterior']['col2'] = $suma_IB;
    $datos['sub_anterior']['col3'] = $datos['ant_321']['col3'];
    $datos['sub_anterior']['col4'] = $datos['ant_33']['col4'];

    $datos['sub_anterior']['col5'] = number_format( 
        numero_again( $datos['sub_anterior']['col1'] ) + 
        numero_again( $datos['sub_anterior']['col2'] ) + 
        numero_again( $datos['sub_anterior']['col3'] ) + 
        numero_again( $datos['sub_anterior']['col4'] )
    , 0 );

    // Ejercicio Actual

    // Obtenemos algunos importes de sumatorias especiales
    $saldo_321_anterior = dame_saldo( $datos_ejercicio_actual_mes_anterior, '321', $MilPesos );
    $saldo_321_naturaleza_contraria = 0;
    $saldo_321_naturaleza_contraria = $saldo_321_anterior * -1;

    $suma_IIB = number_format( 
        $resultado_ejercicio + 
        $saldo_321_naturaleza_contraria + 
        dame_variacion( $datos_ejercicio_actual, $datos_ejercicio_actual_mes_anterior, '323', $MilPesos ) + 
        dame_variacion( $datos_ejercicio_actual, $datos_ejercicio_actual_mes_anterior, '324', $MilPesos ) + 
        dame_variacion( $datos_ejercicio_actual, $datos_ejercicio_actual_mes_anterior, '325', $MilPesos )
    , 0 );

    $suma_32 = number_format( numero_again( $suma_IIB ) + dame_variacion( $datos_ejercicio_actual, $datos_ejercicio_actual_mes_anterior, '322', $MilPesos ), 0 );

    $datos['act_31' ]['col1'] = $datos['act_31' ]['col5'] = dame_variacion_con_formato( $datos_ejercicio_actual, $datos_ejercicio_actual_mes_anterior, '31',  $MilPesos );
    $datos['act_311']['col1'] = $datos['act_311']['col5'] = dame_variacion_con_formato( $datos_ejercicio_actual, $datos_ejercicio_actual_mes_anterior, '311', $MilPesos );
    $datos['act_312']['col1'] = $datos['act_312']['col5'] = dame_variacion_con_formato( $datos_ejercicio_actual, $datos_ejercicio_actual_mes_anterior, '312', $MilPesos );
    $datos['act_313']['col1'] = $datos['act_313']['col5'] = dame_variacion_con_formato( $datos_ejercicio_actual, $datos_ejercicio_actual_mes_anterior, '313', $MilPesos );

    $datos['act_32']['col2']  = dame_variacion_con_formato( $datos_ejercicio_actual, $datos_ejercicio_actual_mes_anterior, '322', $MilPesos );
    $datos['act_32']['col3']  = $suma_IIB;
    $datos['act_32']['col5']  = $suma_32;

    $datos['act_321']['col3'] = $datos['act_321']['col5'] = number_format( round( $resultado_ejercicio / $MilPesos, 0 ), 0 );

    $datos['act_322']['col2'] = dame_variacion_con_formato( $datos_ejercicio_actual, $datos_ejercicio_actual_mes_anterior, '322', $MilPesos );
    $datos['act_322']['col3'] = number_format( $saldo_321_naturaleza_contraria, 0 );

    $total_act_322 = numero_again( $datos['act_322']['col2'] ) + numero_again( $datos['act_322']['col3'] );
    $datos['act_322']['col5'] = number_format( $total_act_322, 0 );

    $datos['act_323']['col3'] = $datos['act_323']['col5'] = dame_variacion_con_formato( $datos_ejercicio_actual, $datos_ejercicio_actual_mes_anterior, '323', $MilPesos );
    $datos['act_324']['col3'] = $datos['act_324']['col5'] = dame_variacion_con_formato( $datos_ejercicio_actual, $datos_ejercicio_actual_mes_anterior, '324', $MilPesos );
    $datos['act_325']['col3'] = $datos['act_325']['col5'] = dame_variacion_con_formato( $datos_ejercicio_actual, $datos_ejercicio_actual_mes_anterior, '325', $MilPesos );

    $datos['act_33' ]['col4'] = $datos['act_33' ]['col5'] = dame_variacion_con_formato( $datos_ejercicio_actual, $datos_ejercicio_actual_mes_anterior, '33',  $MilPesos );
    $datos['act_331']['col4'] = $datos['act_331']['col5'] = dame_variacion_con_formato( $datos_ejercicio_actual, $datos_ejercicio_actual_mes_anterior, '331', $MilPesos );
    $datos['act_332']['col4'] = $datos['act_332']['col5'] = dame_variacion_con_formato( $datos_ejercicio_actual, $datos_ejercicio_actual_mes_anterior, '332', $MilPesos );

    // gran total
    $datos['total']['col1'] = number_format( 
        numero_again( $datos['sub_anterior']['col1'] ) + 
        numero_again( $datos['act_31']['col1'] )
    );

    $datos['total']['col2'] = number_format( 
        numero_again( $datos['sub_anterior']['col2'] ) +
        numero_again( $datos['act_32']['col2'] )
    );

    $datos['total']['col3'] = number_format( 
        numero_again( $datos['sub_anterior']['col3'] ) +
        numero_again( $suma_IIB )
    );

    $datos['total']['col4'] = number_format( 
        numero_again( $datos['sub_anterior']['col4'] ) +
        numero_again( $datos['act_33']['col4'] )
    );

    $datos['total']['col5'] = number_format( 
        numero_again( $datos['total']['col1'] ) + 
        numero_again( $datos['total']['col2'] ) + 
        numero_again( $datos['total']['col3'] ) + 
        numero_again( $datos['total']['col4'] )
    , 0 );



}    



// Regresamos el importe para su presentación en el estado financiero
function dame_saldo_con_formato( &$array, $key, $MilPesos ) {

    // para éste estado financiero, todas las cuentas son de naturaleza acreedora
    $saldo = $array[ $key ]['haber'] - $array[ $key ]['debe'];

    return  number_format( round( $saldo / $MilPesos, 0 ), 0 );

}


// Regresamos el importe para su presentación en el estado financiero
function dame_variacion_con_formato( &$array, &$array_mes_anterior, $key, $MilPesos ) {

    // para éste estado financiero, todas las cuentas son de naturaleza acreedora
    $saldo = ( $array[ $key ]['haber'] - $array[ $key ]['debe'] );

    return  number_format( round( $saldo / $MilPesos, 0 ), 0 );

}


// Regresamos el importe para su presentación en el estado financiero
function dame_saldo( &$array, $key, $MilPesos ) {

    // para éste estado financiero, todas las cuentas son de naturaleza acreedora
    $saldo = $array[ $key ]['haber'] - $array[ $key ]['debe'];

    return  round( $saldo / $MilPesos, 0 );

}




// Regresamos el importe para su presentación en el estado financiero
function dame_variacion( &$array, &$array_mes_anterior, $key, $MilPesos ) {

    // para éste estado financiero, todas las cuentas son de naturaleza acreedora
    $saldo = ( $array[ $key ]['haber'] - $array[ $key ]['debe'] );

    return  round( $saldo / $MilPesos, 0 );

}



// Como ya tenemos el importe formateado, para sumas regresamos el float 
function numero_again( $str_numero_formateado ) {

    $str_numero = str_replace( ",", "", $str_numero_formateado );

    return floatval( $str_numero );

}