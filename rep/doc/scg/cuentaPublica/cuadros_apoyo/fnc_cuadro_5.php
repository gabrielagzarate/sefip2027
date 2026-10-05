<?php


/*  Regresamos un array con todo listo para enviar a excel el cuadro de apoyo usando array2excel()
    5. Conciliación entre los Egresos Presupuestarios y los Gastos Contables (2)
*/
function prepara_y_arma_cuadro_apoyo_5( &$data_cuadro_5, &$headers_cuadro_5, &$table ) {

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
    $data_5 = [];
    $sql = get_sql_cuenta_5( $gpo, $ur, $where_ue, $mes );
    procesa_datos( $data_5, $sql );

    $total_5 = 0;
    foreach ($data_5 as $item) {
        if ( str_contains( '.551.552.553.559.561.512.', $item['cuenta'] ) ) {
            $total_5 += $item['saldo'];
        }
    }

    $data_cuadro_5 = [
        [ 'cuenta' => '', 'concepto' => 'TOTAL DE EGRESOS PRESUPUESTARIOS', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => '', 'origen' => '' ], 
        [ 'cuenta' => '', 'concepto' => 'MENOS EGRESOS PRESUPUESTARIOS NO CONTABLES', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'MATERIAS PRIMAS Y MATERIALES DE PRODUCCIÓN Y COMERCIALIZACIÓN', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'MATERIALES Y SUMINISTROS', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'MOBILIARIO Y EQUIPO DE ADMINISTRACIÓN', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'MOBILIARIO Y EQUIPO EDUCACIONAL Y RECREATIVO', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'EQUIPO E INSTRUMENTAL MÉDICO Y DE LABORATORIO', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'VEHICULOS Y EQUIPO DE TRANSPORTE', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'EQUIPO DE DEFENSA Y SEGURIDAD', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'MAQUINARIA, OTROS EQUIPOS Y HERRAMINETAS', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'ACTIVOS BIOLÓGICOS', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'BIENES INMUEBLES', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'ACTIVOS INTANGIBLES', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'OBRA PÚBLICA EN BIENES DE DOMINIO PÚBLICO', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'OBRA PÚBLICA EN BIENES PROPIOS', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'ACCIONES Y PARTICIPACIONES DE CAPITAL', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'COMPRA DE TÍTULOS Y VALORES', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'CONCESIÓN DE PRÉSTAMOS', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'INVERSIONES EN FIDEICOMISOS, MANDATOS Y OTROS ANÁLOGOS', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'PROVISIONES PARA CONTINGENCIAS Y OTRAS EROGACIONES ESPECIALES', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'AMORTIZACIÓN DE LA DEUDA PÚBLICA', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'ADEUDOS DE EJERCICIOS FISCALES ANTERIORES (ADEFAS)', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => 'OTROS EGRESOS PRESUPUESTARIOS NO CONTABLES', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => '', 'origen' => '' ], 
        [ 'cuenta' => '', 'concepto' => 'MÁS GASTOS CONTABLES NO PRESUPUESTARIOS', 'origen' => $total_5 ], 
        [ 'cuenta' => '551', 'concepto' => 'ESTIMACIONES, DEPRECIACIONES, DETERIOROS, OBSOLESCENCIA Y AMORTIZACIONES', 'origen' => $data_5[ array_search('551', array_column($data_5, 'cuenta')) ]['saldo'] ], 
        [ 'cuenta' => '552', 'concepto' => 'PROVISIONES', 'origen' => $data_5[ array_search('552', array_column($data_5, 'cuenta')) ]['saldo'] ], 
        [ 'cuenta' => '553', 'concepto' => 'DISMINUCIÓN DE INVENTARIOS', 'origen' => $data_5[ array_search('553', array_column($data_5, 'cuenta')) ]['saldo'] ], 
        [ 'cuenta' => '559', 'concepto' => 'OTROS GASTOS', 'origen' => $data_5[ array_search('559', array_column($data_5, 'cuenta')) ]['saldo'] ], 
        [ 'cuenta' => '561', 'concepto' => 'INVERSIÓN PÚBLICA NO CAPITALIZABLE', 'origen' => $data_5[ array_search('561', array_column($data_5, 'cuenta')) ]['saldo'] ], 
        [ 'cuenta' => '512', 'concepto' => 'MATERIALES Y SUMINISTROS (CONSUMOS)', 'origen' => $data_5[ array_search('512', array_column($data_5, 'cuenta')) ]['saldo'] ], 
        [ 'cuenta' => '', 'concepto' => 'OTROS GASTOS CONTABLES NO PRESUPUESTARIOS', 'origen' => 0 ], 
        [ 'cuenta' => '', 'concepto' => '', 'origen' => '' ], 
        [ 'cuenta' => '', 'concepto' => 'TOTAL DE GASTOS CONTABLES', 'origen' => $total_5 ], 
    ];

    $anchoColumnas = array( 20, 85, 40 );
    $nombreColumnas = array( "CUENTA", "CONCEPTO", $_SESSION[_S]['ejercicioActual'] );
    $incluirColumnas = array( "cuenta", "concepto", "origen" );
    $tipoColumnas = array( "c", "c", "n" );

    foreach( $incluirColumnas as $key=>$val ){
        $table['cuadro_5']['nombre'][$val] = $nombreColumnas[$key];
        $table['cuadro_5']['ancho'][$val] = $anchoColumnas[$key];
        $table['cuadro_5']['campo'][$val] = $key;
        $table['cuadro_5']['tipo'][$val] = $tipoColumnas[$key];
    }

    // Armamos los parametros opcionales
    $headers_cuadro_5 = array(
        'encabLin1' => "GOBIERNO DEL ESTADO DE OAXACA", 
        'encabLin2' => "UR: " . $ur_actual->getClave() . " - " . $ur_actual->getNombre(), 
        'encabLin3' => "CONCILIACIÓN ENTRE LOS EGRESOS PRESUPUESTARIOS Y CONTABLES", 
        'encabLin4' => "", 
        'ancho' => $anchoColumnas
    );

    return true;

}



// Obtenemos la consulta para las cuentas 5
function get_sql_cuenta_5( &$gpo, &$ur, &$where_ue, &$mes ) {

    return "WITH p AS ( 
        SELECT ger_id, cgp_id, rub_id, 
        IFNULL( sum( p.pdt_debe ) - sum( p.pdt_haber ), 0 ) as saldo
        FROM cg_PolizaDetalle p 
        JOIN cg_PolizaMaestro pm using ( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
        WHERE 
        gpo_id = $gpo AND 
        ur_id = $ur AND 
        $where_ue
        msc_id <= $mes AND         
        ger_id = 5
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
            r.ger_id = 5
        GROUP BY r.ger_id, r.cgp_id, r.rub_id
    ";

}

// Fin del código