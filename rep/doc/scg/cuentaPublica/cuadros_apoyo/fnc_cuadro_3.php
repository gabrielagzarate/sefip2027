<?php


/*  Regresamos un array con todo listo para enviar a excel el cuadro de apoyo usando array2excel()
    3. CONCILIACIÓN DE FLUJOS DE EFECTIVO NETOS
*/
function prepara_y_arma_cuadro_apoyo_3( &$data_cuadro_3, &$headers_cuadro_3, &$table ) {

    $gpo = ( $_SESSION[_S]['nvj_id'] > 2 ) ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
    $ur  = ( $_SESSION[_S]['nvj_id'] > 2 ) ? $_SESSION[_S]['clasAdm-ur']  : $_SESSION[_S]['ur_id'];
    $ue  = isset( $_POST['ue'] ) ?  $_POST['ue'] : "";

    $ur_actual = new UnidadResponsable();
    $ur_actual->setGpo_id($gpo);
    $ur_actual->setUr_id($ur);
    $ur_actual->select();

    $data_cuadro_3 = [
        [ 'mensaje' => 'ESTE CUADRO NO SE GENERA DESDE EL SISTEMA.' ], 
        [ 'mensaje' => 'EL USUARIO DEBERÁ CAPTURAR MANUALMENTE LA INFORMACIÓN' ], 
        [ 'mensaje' => 'DEL CUADRO DE LAS NOTAS EN LA FRACCIÓN IV NÚMERO 3' ], 
        [ 'mensaje' => '( CONCILIACIÓN DE FLUJOS DE EFECTIVO NETOS )' ] 
    ];

    $anchoColumnas = array( 100 );
    $nombreColumnas = array( "MENSAJE" );
    $incluirColumnas = array( "mensaje" );
    $tipoColumnas = array( "c" );

    foreach( $incluirColumnas as $key=>$val ){
        $table['cuadro_3']['nombre'][$val] = $nombreColumnas[$key];
        $table['cuadro_3']['ancho'][$val] = $anchoColumnas[$key];
        $table['cuadro_3']['campo'][$val] = $key;
        $table['cuadro_3']['tipo'][$val] = $tipoColumnas[$key];
    }

    // Armamos los parametros opcionales
    $headers_cuadro_3 = array(
        'encabLin1' => "GOBIERNO DEL ESTADO DE OAXACA", 
        'encabLin2' => "UR: " . $ur_actual->getClave() . " - " . $ur_actual->getNombre(), 
        'encabLin3' => "CONCILIACIÓN DE FLUJOS DE EFECTIVO NETOS", 
        'ancho' => $anchoColumnas
    );

    return true;

}


// Fin del código