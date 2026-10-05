<?php


/*  Regresamos un array con todo listo para enviar a excel el cuadro de apoyo usando array2excel()
    1. EFECTIVO Y EQUIVALENTES
*/
function prepara_y_arma_cuadro_apoyo_7( &$data_cuadro_7, &$headers_cuadro_7, &$table ) {

    $base_actual = "ctrl" . $_SESSION[_S]['ejercicioActual'];

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

    $sql = "WITH p AS ( 
            SELECT ger_id, cgp_id, rub_id, cgcu_id, 
            sum( p.pdt_debe ) AS debe, sum( p.pdt_haber ) as haber
            FROM cg_PolizaDetalle p 
            JOIN cg_PolizaMaestro pm using ( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
            WHERE 
            gpo_id = :gpo AND 
            ur_id = :ur AND 
            $where_ue
            msc_id <= :mes AND         
            ger_id = 8 and cgp_id = 2 
            AND pm.epl_id NOT IN ( 'C', 'H' )
            GROUP BY ger_id, cgp_id, rub_id
        )
        SELECT CONCAT( c.ger_id, c.cgp_id, c.rub_id, c.cgcu_id ) as cuenta, c.cgcu_nombre as nombre, 
                    IFNULL( 
            CASE s.nat_id
                WHEN 'D' THEN 
                    ( p.debe - p.haber )
                ELSE
                    ( p.haber - p.debe )
            END, 0 ) AS saldo
            FROM cat_CgCuenta c
                    join cat_CgSubcuenta s using ( ger_id, cgp_id, rub_id, cgcu_id )
            LEFT JOIN p on (
                c.ger_id = p.ger_id AND 
                c.cgp_id = p.cgp_id AND 
                c.rub_id = p.rub_id AND 
                c.cgcu_id = p.cgcu_id
            )
            WHERE 
                c.ger_id = 8 and c.cgp_id = 2 
            GROUP BY c.ger_id, c.cgp_id, c.rub_id, c.cgcu_id;
    ";

    $con = new AccesoDatos();
    $con->conectar();
    $con->setSql($sql);
    $con->consultar(array(':gpo'=>$gpo, ':ur'=>$ur, ':mes'=>$mes));

    while ( $row = $con->extraer() ) {
        $data_cuadro_7[] = $row;
    }

    $con->desconectar();
    unset($con);

    $anchoColumnas = array( 20, 50, 15 );
    $nombreColumnas = array( "Cuenta", "Concepto", $_SESSION[_S]['ejercicioActual'] );
    $incluirColumnas = array( "cuenta", "nombre", "saldo" );
    $tipoColumnas = array( "c", "c", "n" );

    foreach( $incluirColumnas as $key=>$val ){
        $table['cuadro_7']['nombre'][$val] = $nombreColumnas[$key];
        $table['cuadro_7']['ancho'][$val] = $anchoColumnas[$key];
        $table['cuadro_7']['campo'][$val] = $key;
        $table['cuadro_7']['tipo'][$val] = $tipoColumnas[$key];
    }

    // Armamos los parametros opcionales
    $headers_cuadro_7 = array(
        'encabLin1' => "GOBIERNO DEL ESTADO DE OAXACA", 
        'encabLin2' => "UR: " . $ur_actual->getClave() . " - " . $ur_actual->getNombre(), 
        'encabLin3' => "AL MES DE " . MesNombre( $mes ) . " DEL " . $_SESSION[_S]['ejercicioActual'], 
        'encabLin4' => "CUENTAS DE ORDEN PRESUPUESTARIAS DE EGRESOS", 
        'ancho' => $anchoColumnas
    );

    return true;

}


// Fin del código