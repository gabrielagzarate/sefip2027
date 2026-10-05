<?php


/*  Regresamos un array con todo listo para enviar a excel el cuadro de apoyo usando array2excel()
    4. Conciliación entre los Ingresos Presupuestarios y Contables (2)
*/
function prepara_y_arma_cuadro_apoyo_4( &$data_cuadro_4, &$headers_cuadro_4, &$table ) {

    $gpo = ( $_SESSION[_S]['nvj_id'] > 2 ) ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
    $ur  = ( $_SESSION[_S]['nvj_id'] > 2 ) ? $_SESSION[_S]['clasAdm-ur']  : $_SESSION[_S]['ur_id'];
    $ue  = isset( $_POST['ue'] ) ?  $_POST['ue'] : "";

    $ur_actual = new UnidadResponsable();
    $ur_actual->setGpo_id($gpo);
    $ur_actual->setUr_id($ur);
    $ur_actual->select();

    $where_ue = "";
    if ( $ue <> "" ) {
        $where_ue = " p.ue_id = $ue AND ";
    }

    $mes = isset($_POST['mes']) ? $_POST['mes'] : -1;

    // Procesamos la cuenta 123
    $datos_123 = [];
    $sql = get_sql_cuenta_43( $gpo, $ur, $where_ue, $mes );
    procesa_datos( $datos_123, $sql );

    $total_43 = 0;
    foreach ($datos_123 as $item) {
        $total_43 += $item['saldo'];
    }

    $data_cuadro_4 = [
        [ 'cuenta' => '', 'concepto' => 'TOTAL DE INGRESOS PRESUPUESTARIOS', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => ' ', 'origen' => '' ], 
        [ 'cuenta' => '', 'concepto' => 'MÁS INGRESOS CONTABLES NO PRESUPUESTARIOS', 'origen' => $total_43 ], 
        [ 'cuenta' => '431', 'concepto' => 'INGRESOS FINANCIEROS', 'origen' => $datos_123[ array_search('431', array_column($datos_123, 'cuenta')) ]['saldo'] ], 
        [ 'cuenta' => '432', 'concepto' => 'INCREMENTO POR VARIACIÓN DE INVENTARIOS', 'origen' => $datos_123[ array_search('432', array_column($datos_123, 'cuenta')) ]['saldo'] ], 
        [ 'cuenta' => '433', 'concepto' => 'DISMINUCIÓN DEL EXCESO DE ESTIMACIONES POR PÉRDIDA O DETERIORO U OBSOLESCENCIA', 'origen' => $datos_123[ array_search('433', array_column($datos_123, 'cuenta')) ]['saldo'] ], 
        [ 'cuenta' => '434', 'concepto' => 'DISMINUCIÓN DEL EXCESO DE PROVISIONES', 'origen' => $datos_123[ array_search('434', array_column($datos_123, 'cuenta')) ]['saldo'] ], 
        [ 'cuenta' => '439', 'concepto' => 'OTROS INGRESOS Y BENEFICIOS VARIOS', 'origen' => $datos_123[ array_search('439', array_column($datos_123, 'cuenta')) ]['saldo'] ], 
        [ 'cuenta' => '', 'concepto' => 'OTROS INGRESOS CONTABLES NO PRESUPUESTARIOS', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => ' ', 'origen' => '' ], 
        [ 'cuenta' => '', 'concepto' => 'MENOS INGRESOS PRESUPUESTARIOS NO CONTABLES', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'APROVECHAMIENTOS PATRIMONIALES', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'INGRESOS DERIVADOS DE FINANCIAMIENTOS', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'OTROS INGRESOS PRESUPUESTARIOS NO CONTABLES', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => '', 'origen' => '' ], 
        [ 'cuenta' => '', 'concepto' => 'TOTAL DE INGRESOS CONTABLES', 'origen' => $total_43 ] 
    ];

    $anchoColumnas = array( 20, 85, 40 );
    $nombreColumnas = array( "CUENTA", "CONCEPTO", $_SESSION[_S]['ejercicioActual'] );
    $incluirColumnas = array( "cuenta", "concepto", "origen" );
    $tipoColumnas = array( "c", "c", "n" );

    foreach( $incluirColumnas as $key=>$val ){
        $table['cuadro_4']['nombre'][$val] = $nombreColumnas[$key];
        $table['cuadro_4']['ancho'][$val] = $anchoColumnas[$key];
        $table['cuadro_4']['campo'][$val] = $key;
        $table['cuadro_4']['tipo'][$val] = $tipoColumnas[$key];
    }

    // Armamos los parametros opcionales
    $headers_cuadro_4 = array(
        'encabLin1' => "GOBIERNO DEL ESTADO DE OAXACA", 
        'encabLin2' => "UR: " . $ur_actual->getClave() . " - " . $ur_actual->getNombre(), 
        'encabLin3' => "CONCILIACIÓN ENTRE LOS INGRESOS PRESUPUESTARIOS Y CONTABLES", 
        'encabLin4' => "", 
        'ancho' => $anchoColumnas
    );

    return true;

}



// Obtenemos la consulta para las cuentas 43
function get_sql_cuenta_43( &$gpo, &$ur, &$where_ue, &$mes ) {

    return "WITH p AS ( 
        SELECT ger_id, cgp_id, rub_id, 
        IFNULL( sum( p.pdt_haber ) - sum( p.pdt_debe ), 0 ) as saldo
        FROM cg_PolizaDetalle p 
        JOIN cg_PolizaMaestro pm using ( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
        WHERE 
        gpo_id = $gpo AND 
        ur_id = $ur AND 
        $where_ue
        msc_id <= $mes AND         
        ger_id = 4 and cgp_id = 3
        AND pm.epl_id NOT IN ( 'C', 'H' )
        GROUP BY ger_id, cgp_id, rub_id
    )
    SELECT CONCAT( r.ger_id, r.cgp_id, r.rub_id ) as cuenta, r.rub_nombre as nombre, 
        IFNULL( p.saldo, 0 ) as saldo
        FROM cat_Rubro r
        LEFT JOIN p on (
            r.ger_id = p.ger_id AND 
            r.cgp_id = p.cgp_id AND 
            r.rub_id = p.rub_id 
        )
        WHERE 
            r.ger_id = 4 and r.cgp_id = 3
        GROUP BY r.ger_id, r.cgp_id, r.rub_id
    ";

}



// Fin del código