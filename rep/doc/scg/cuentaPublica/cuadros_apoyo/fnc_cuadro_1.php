<?php


/*  Regresamos un array con todo listo para enviar a excel el cuadro de apoyo usando array2excel()
    1. EFECTIVO Y EQUIVALENTES
*/
function prepara_y_arma_cuadro_apoyo_1( &$data_cuadro_1, &$headers_cuadro_1, &$table ) {

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

    $sql = "SELECT CONCAT( c.ger_id, c.cgp_id, c.rub_id, c.cgcu_id ) as cuenta, c.cgcu_nombre as nombre, 
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
                    p.ger_id = 1 and p.cgp_id = 1 and p.rub_id = 1
                    AND pm.epl_id NOT IN ( 'C', 'H' )                                   
            ) AS p USING ( ger_id, cgp_id, rub_id, cgcu_id )
            where 
            c.ger_id = 1 and c.cgp_id = 1 and c.rub_id = 1
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
                    p.gpo_id = :gpo AND 
                    p.ur_id = :ur AND 
                    $where_ue
                    p.msc_id <= :mes AND 
                    p.ger_id = 1 and p.cgp_id = 1 and p.rub_id = 1
                    AND pm.epl_id NOT IN ( 'C', 'H' )                                   
            ) AS p USING ( ger_id, cgp_id, rub_id, cgcu_id )
            where 
            c.ger_id = 1 and c.cgp_id = 1 and c.rub_id = 1
            group by c.ger_id, c.cgp_id, c.rub_id, c.cgcu_id
    ";

    $con = new AccesoDatos();
    $con->conectar();
    $con->setSql($sql);
    $con->consultar(array(':gpo'=>$gpo, ':ur'=>$ur, ':mes'=>$mes));

    $saldo_actual = 0;
    $saldo_anterior = 0;
    while ( $row = $con->extraer() ) {
        $data_cuadro_1[] = $row;
        $saldo_actual += $row['saldoActual'];
        $saldo_anterior += $row['saldoAnterior'];
    }

    // Agregamos los totales
    $data_cuadro_1[] = [
        "cuenta" => "",
        "nombre" => "TOTAL", 
        "saldoActual" => $saldo_actual, 
        "saldoAnterior" => $saldo_anterior
    ];

    $con->desconectar();
    unset($con);

    $anchoColumnas = array( 20, 80, 15, 15 );
    $nombreColumnas = array( "CUENTA", "CONCEPTO", $_SESSION[_S]['ejercicioActual'], ( $_SESSION[_S]['ejercicioActual'] -1 ) );
    $incluirColumnas = array( "cuenta", "nombre", "saldoAnterior", "saldoActual" );
    $tipoColumnas = array( "c", "c", "n", "n" );

    foreach( $incluirColumnas as $key=>$val ){
        $table['cuadro_1']['nombre'][$val] = $nombreColumnas[$key];
        $table['cuadro_1']['ancho'][$val] = $anchoColumnas[$key];
        $table['cuadro_1']['campo'][$val] = $key;
        $table['cuadro_1']['tipo'][$val] = $tipoColumnas[$key];
    }

    // Armamos los parametros opcionales
    $headers_cuadro_1 = array(
        'encabLin1' => "GOBIERNO DEL ESTADO DE OAXACA", 
        'encabLin2' => "UR: " . $ur_actual->getClave() . " - " . $ur_actual->getNombre(), 
        'encabLin3' => "AL MES DE " . MesNombre( $mes ) . " DEL " . $_SESSION[_S]['ejercicioActual'], 
        'encabLin4' => "EFECTIVO Y EQUIVALENTES", 
        'ancho' => $anchoColumnas
    );

    return true;

}


// Fin del código