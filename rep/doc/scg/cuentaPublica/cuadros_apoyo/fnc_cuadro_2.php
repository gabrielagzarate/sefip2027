<?php


/*  Regresamos un array con todo listo para enviar a excel el cuadro de apoyo usando array2excel()
    2. ADQUISICIONES DE ACTIVIDADES DE INVERSIÓN EFECTIVAMENTE PAGADAS
*/
function prepara_y_arma_cuadro_apoyo_2( &$data_cuadro_2, &$headers_cuadro_2, &$table ) {

    $base_actual = "ctrl" . $_SESSION[_S]['ejercicioActual'];
    $base_anterior = "ctrl" . ( $_SESSION[_S]['ejercicioActual'] -1 );

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
    $sql = get_sql_cuenta_123( $base_actual, $base_anterior, $gpo, $ur, $where_ue, $mes );
    procesa_datos( $data_cuadro_2, $sql );



    // Procesamos la cuenta 124
    $sql = get_sql_cuenta_124( $base_actual, $base_anterior, $gpo, $ur, $where_ue, $mes );
    procesa_datos( $data_cuadro_2, $sql );

    // Procesamos la cuenta 125
    $sql = get_sql_cuenta_125( $base_actual, $base_anterior, $gpo, $ur, $where_ue, $mes );
    procesa_datos( $data_cuadro_2, $sql );

    // Agregamos el gran total
    $pos_123 = array_search('123 ', array_column($data_cuadro_2, 'cuenta'));    
    $pos_124 = array_search('124 ', array_column($data_cuadro_2, 'cuenta'));    
    $pos_125 = array_search('125 ', array_column($data_cuadro_2, 'cuenta'));    
    $data_cuadro_2[] = [
        'cuenta' => '',
        'nombre' => 'Total', 
        'saldoActual' => ( $data_cuadro_2[$pos_123]['saldoActual'] + 
                           $data_cuadro_2[$pos_124]['saldoActual'] + 
                           $data_cuadro_2[$pos_125]['saldoActual'] ),
        'saldoAnterior' => ( $data_cuadro_2[$pos_123]['saldoAnterior'] + 
                             $data_cuadro_2[$pos_124]['saldoAnterior'] + 
                             $data_cuadro_2[$pos_125]['saldoAnterior'] )
    ];

    $anchoColumnas = array( 20, 80, 15, 15 );
    $nombreColumnas = array( "CUENTA", "CONCEPTO", $_SESSION[_S]['ejercicioActual'], ( $_SESSION[_S]['ejercicioActual'] -1 ) );
    $incluirColumnas = array( "cuenta", "nombre", "saldoAnterior", "saldoActual" );
    $tipoColumnas = array( "c", "c", "n", "n" );

    foreach( $incluirColumnas as $key=>$val ) {
        $table['cuadro_2']['nombre'][$val] = $nombreColumnas[$key];
        $table['cuadro_2']['ancho'][$val] = $anchoColumnas[$key];
        $table['cuadro_2']['campo'][$val] = $key;
        $table['cuadro_2']['tipo'][$val] = $tipoColumnas[$key];
    }


    // Armamos los parametros opcionales
    $headers_cuadro_2 = array(
        'encabLin1' => "GOBIERNO DEL ESTADO DE OAXACA", 
        'encabLin2' => "UR: " . $ur_actual->getClave() . " - " . $ur_actual->getNombre(), 
        'encabLin3' => "AL MES DE " . MesNombre( $mes ) . " DEL " . $_SESSION[_S]['ejercicioActual'], 
        'encabLin4' => "ADQUISICIONES DE ACTIVIDADES DE INVERSIÓN EFECTIVAMENTE PAGADAS", 
        'ancho' => $anchoColumnas
    );

    return true;

}


// Extraemos los datos de la consulta, y lo vamos acumulando al array
function procesa_datos( &$array, &$sql ) {

    $con = new AccesoDatos();
    $con->conectar();
    $con->setSql($sql);
    $con->consultar();

    while ( $row = $con->extraer() ) {
        $array[] = $row;
    }

    $con->desconectar();
    unset($con);

}


// Obtenemos la consulta para las cuentas 123
function get_sql_cuenta_123( &$base_actual, &$base_anterior, &$gpo, &$ur, &$where_ue, &$mes ) {

    return "WITH base AS ( 
        SELECT CONCAT( c.ger_id, c.cgp_id, c.rub_id, c.cgcu_id ) as cuenta, c.cgcu_nombre as nombre, 
        0.00 as saldoAnterior, 
        IFNULL( sum( p.pdt_debe ) - sum( p.pdt_haber ), 0 ) as saldoActual
        from $base_actual.cat_CgCuenta c 
        LEFT JOIN 
        (
            SELECT p.msc_id, p.ger_id, p.cgp_id, p.rub_id, p.cgcu_id, p.pdt_debe, p.pdt_haber
            FROM 
                $base_actual.cg_PolizaDetalle p
                JOIN $base_actual.cg_PolizaMaestro pm USING ( gpo_id, ur_id, ue_id, msc_id, pma_id, pot_id, prf_id )
                where 
                p.gpo_id = $gpo AND 
                p.ur_id = $ur AND 
                $where_ue
                p.msc_id <= $mes AND 
                p.ger_id = 1 and p.cgp_id = 2 and p.rub_id = 3
                AND pm.epl_id NOT IN ( 'C', 'H' )                                   
        ) AS p USING ( ger_id, cgp_id, rub_id, cgcu_id )
        where 
        c.ger_id = 1 and c.cgp_id = 2 and c.rub_id = 3
        group by c.ger_id, c.cgp_id, c.rub_id, c.cgcu_id

        union all

        SELECT CONCAT( c.ger_id, c.cgp_id, c.rub_id, c.cgcu_id ) as cuenta, c.cgcu_nombre as nombre, 
        0.00 as saldoAnterior, 
        IFNULL( sum( p.pdt_debe ) - sum( p.pdt_haber ), 0 ) as saldoActual
        from $base_anterior.cat_CgCuenta c 
        LEFT JOIN 
        (
            SELECT p.msc_id, p.ger_id, p.cgp_id, p.rub_id, p.cgcu_id, p.pdt_debe, p.pdt_haber
            FROM 
                $base_anterior.cg_PolizaDetalle p
                JOIN $base_anterior.cg_PolizaMaestro pm USING ( gpo_id, ur_id, ue_id, msc_id, pma_id, pot_id, prf_id )
                where 
                p.gpo_id = $gpo AND 
                p.ur_id = $ur AND 
                $where_ue
                p.msc_id <= $mes AND 
                p.ger_id = 1 and p.cgp_id = 2 and p.rub_id = 3
                AND pm.epl_id NOT IN ( 'C', 'H' )                                   
        ) AS p USING ( ger_id, cgp_id, rub_id, cgcu_id )
        where 
        c.ger_id = 1 and c.cgp_id = 2 and c.rub_id = 3
        group by c.ger_id, c.cgp_id, c.rub_id, c.cgcu_id
        )

        SELECT cuenta, nombre, saldoAnterior, saldoActual
        FROM base 

        union

        SELECT '123 ' as cuenta, 'BIENES INMUEBLES, INFRAESTRUCTURA Y CONSTRUCCIONES EN PROCESO' as nombre, sum( saldoAnterior ) as saldoAnterior, sum( saldoActual  ) as saldoActual
        FROM base

        union 

        SELECT cuenta, nombre, sum( saldoAnterior ) as saldoAnterior, sum( saldoActual  ) as saldoActual
                FROM base
        GROUP BY cuenta

        ORDER BY cuenta;
    ";


}


// Obtenemos la consulta para las cuentas 124
function get_sql_cuenta_124( &$base_actual, &$base_anterior, &$gpo, &$ur, &$where_ue, &$mes ) {

    return "WITH base AS ( 
        SELECT CONCAT( c.ger_id, c.cgp_id, c.rub_id, c.cgcu_id ) as cuenta, c.cgcu_nombre as nombre, 
        0.00 as saldoAnterior, 
        IFNULL( sum( p.pdt_debe ) - sum( p.pdt_haber ), 0 ) as saldoActual
        from $base_actual.cat_CgCuenta c 
        LEFT JOIN 
        (
            SELECT p.msc_id, p.ger_id, p.cgp_id, p.rub_id, p.cgcu_id, p.pdt_debe, p.pdt_haber
            FROM 
                $base_actual.cg_PolizaDetalle p
                JOIN $base_actual.cg_PolizaMaestro pm USING ( gpo_id, ur_id, ue_id, msc_id, pma_id, pot_id, prf_id )
                where 
                p.gpo_id = $gpo AND 
                p.ur_id = $ur AND 
                $where_ue
                p.msc_id <= $mes AND 
                p.ger_id = 1 and p.cgp_id = 2 and p.rub_id = 4
                AND pm.epl_id NOT IN ( 'C', 'H' )                                   
        ) AS p USING ( ger_id, cgp_id, rub_id, cgcu_id )
        where 
        c.ger_id = 1 and c.cgp_id = 2 and c.rub_id = 4
        group by c.ger_id, c.cgp_id, c.rub_id, c.cgcu_id

        union all

        SELECT CONCAT( c.ger_id, c.cgp_id, c.rub_id, c.cgcu_id ) as cuenta, c.cgcu_nombre as nombre, 
        0.00 as saldoAnterior, 
        IFNULL( sum( p.pdt_debe ) - sum( p.pdt_haber ), 0 ) as saldoActual
        from $base_anterior.cat_CgCuenta c 
        LEFT JOIN 
        (
            SELECT p.msc_id, p.ger_id, p.cgp_id, p.rub_id, p.cgcu_id, p.pdt_debe, p.pdt_haber
            FROM 
                $base_anterior.cg_PolizaDetalle p
                JOIN $base_anterior.cg_PolizaMaestro pm USING ( gpo_id, ur_id, ue_id, msc_id, pma_id, pot_id, prf_id )
                where 
                p.gpo_id = $gpo AND 
                p.ur_id = $ur AND 
                $where_ue
                p.msc_id <= $mes AND 
                p.ger_id = 1 and p.cgp_id = 2 and p.rub_id = 4
                AND pm.epl_id NOT IN ( 'C', 'H' )                                   
        ) AS p USING ( ger_id, cgp_id, rub_id, cgcu_id )
        where 
        c.ger_id = 1 and c.cgp_id = 2 and c.rub_id = 4
        group by c.ger_id, c.cgp_id, c.rub_id, c.cgcu_id
        ) 

        SELECT cuenta, nombre, saldoAnterior, saldoActual
        FROM base 

        union 

        SELECT '124 ' as cuenta, 'BIENES MUEBLES' as nombre, sum( saldoAnterior ) as saldoAnterior, sum( saldoActual  ) as saldoActual
        FROM base

        union 

        SELECT cuenta, nombre, sum( saldoAnterior ) as saldoAnterior, sum( saldoActual  ) as saldoActual
                FROM base
        GROUP BY cuenta

        ORDER BY cuenta;
    ";

}

// Obtenemos la consulta para las cuentas 125
// Esta la pidieron a nivel rubro solamente
function get_sql_cuenta_125( &$base_actual, &$base_anterior, &$gpo, &$ur, &$where_ue, &$mes ) {

    return "SELECT CONCAT( c.ger_id, c.cgp_id, c.rub_id ) as cuenta, c.rub_nombre as nombre, 
            0.00 as saldoAnterior, 
            IFNULL( sum( p.pdt_debe ) - sum( p.pdt_haber ), 0 ) as saldoActual
            from $base_actual.cat_Rubro c 
            LEFT JOIN 
            (
                SELECT p.msc_id, p.ger_id, p.cgp_id, p.rub_id, p.pdt_debe, p.pdt_haber
                FROM 
                    $base_actual.cg_PolizaDetalle p
                    JOIN $base_actual.cg_PolizaMaestro pm USING ( gpo_id, ur_id, ue_id, msc_id, pma_id, pot_id, prf_id )
                    where 
                    p.gpo_id = $gpo AND 
                    p.ur_id = $ur AND 
                    $where_ue
                    p.msc_id <= $mes AND 
                    p.ger_id = 1 and p.cgp_id = 2 and p.rub_id = 5
                    AND pm.epl_id NOT IN ( 'C', 'H' )                                   
            ) AS p USING ( ger_id, cgp_id, rub_id )
            where 
            c.ger_id = 1 and c.cgp_id = 2 and c.rub_id = 5
            group by c.ger_id, c.cgp_id, c.rub_id

        union all

            SELECT CONCAT( c.ger_id, c.cgp_id, c.rub_id ) as cuenta, c.rub_nombre as nombre, 
            0.00 as saldoAnterior, 
            IFNULL( sum( p.pdt_debe ) - sum( p.pdt_haber ), 0 ) as saldoActual
            from $base_anterior.cat_Rubro c 
            LEFT JOIN 
            (
                SELECT p.msc_id, p.ger_id, p.cgp_id, p.rub_id, p.pdt_debe, p.pdt_haber
                FROM 
                    $base_anterior.cg_PolizaDetalle p
                    JOIN $base_anterior.cg_PolizaMaestro pm USING ( gpo_id, ur_id, ue_id, msc_id, pma_id, pot_id, prf_id )
                    where 
                    p.gpo_id = $gpo AND 
                    p.ur_id = $ur AND 
                    $where_ue
                    p.msc_id <= $mes AND 
                    p.ger_id = 1 and p.cgp_id = 2 and p.rub_id = 5
                    AND pm.epl_id NOT IN ( 'C', 'H' )                                   
            ) AS p USING ( ger_id, cgp_id, rub_id )
            where 
            c.ger_id = 1 and c.cgp_id = 2 and c.rub_id = 5
            group by c.ger_id, c.cgp_id, c.rub_id
    ";

}