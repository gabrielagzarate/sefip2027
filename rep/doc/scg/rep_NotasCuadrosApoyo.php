<?php

    $pathInPieces = explode(DIRECTORY_SEPARATOR, __FILE__);
    require_once('../../../lib/constantes.php');
    require_once dirname(__FILE__) . '/../../lib/array2Excel.php';
    require_once dirname(__FILE__) . '/../../../lib/MesNombre.php';
    require_once dirname(__FILE__) . '/../../../ent/UnidadResponsable.php';

    require_once dirname(__FILE__) . '/cuentaPublica/cuadros_apoyo/fnc_cuadro_1.php';
    require_once dirname(__FILE__) . '/cuentaPublica/cuadros_apoyo/fnc_cuadro_2.php';
    require_once dirname(__FILE__) . '/cuentaPublica/cuadros_apoyo/fnc_cuadro_3.php';
    require_once dirname(__FILE__) . '/cuentaPublica/cuadros_apoyo/fnc_cuadro_4.php';
    require_once dirname(__FILE__) . '/cuentaPublica/cuadros_apoyo/fnc_cuadro_5.php';
    require_once dirname(__FILE__) . '/cuentaPublica/cuadros_apoyo/fnc_cuadro_6.php';
    require_once dirname(__FILE__) . '/cuentaPublica/cuadros_apoyo/fnc_cuadro_7.php';

    session_start();

    $gpo = ( $_SESSION[_S]['nvj_id'] > 2 ) ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
    $ur  = ( $_SESSION[_S]['nvj_id'] > 2 ) ? $_SESSION[_S]['clasAdm-ur']  : $_SESSION[_S]['ur_id'];
    $mes = isset($_POST['mes']) ? $_POST['mes'] : -1;

    // Variable global a todas las pestañas
    $table = [];

    // 1. Definimos y obtenemos los datos del cuadro 1 (Efectivo y Equivalentes)
    $data_cuadro_1 = [];
    $headers_cuadro_1 = [];
    prepara_y_arma_cuadro_apoyo_1( $data_cuadro_1, $headers_cuadro_1, $table );

    // 2. Definimos y obtenemos los datos del cuadro 2 (Adquisiciones de Actividades de Inversión efectivamente pagadas)
    $data_cuadro_2 = [];
    $headers_cuadro_2 = [];
    prepara_y_arma_cuadro_apoyo_2( $data_cuadro_2, $headers_cuadro_2, $table );

    // 3. Conciliación de Flujos de Efectivo Netos ES CAPTURA MANUAL, LO PONEMOS POR SI EN EL FUTURO ES AUTOMÁTICO
    $data_cuadro_3 = [];
    $headers_cuadro_3 = [];
    prepara_y_arma_cuadro_apoyo_3( $data_cuadro_3, $headers_cuadro_3, $table );

    // 4. Conciliación entre los Ingresos Presupuestarios y Contables (SOLO EL INSTRUCTIVO DE LLENADO, LO POEMOS POR SI EN EL FUTURO ES AUTOMÁTICO)
    $data_cuadro_4 = [];
    $headers_cuadro_4 = [];
    prepara_y_arma_cuadro_apoyo_4( $data_cuadro_4, $headers_cuadro_4, $table );

    // 5. Conciliación entre los Egresos Presupuestarios y los Gastos Contables (SOLO EL INSTRUCTIVO DE LLENADO, LO POEMOS POR SI EN EL FUTURO ES AUTOMÁTICO)
    $data_cuadro_5 = [];
    $headers_cuadro_5 = [];
    prepara_y_arma_cuadro_apoyo_5( $data_cuadro_5, $headers_cuadro_5, $table );

    // 6. Cuentas de Orden Presupuestarias de Ingresos
    $data_cuadro_6 = [];
    $headers_cuadro_6 = [];
    prepara_y_arma_cuadro_apoyo_6( $data_cuadro_6, $headers_cuadro_6, $table );

    // 7. Cuentas de Orden Presupuestarias de Egresos
    $data_cuadro_7 = [];
    $headers_cuadro_7 = [];
    prepara_y_arma_cuadro_apoyo_7( $data_cuadro_7, $headers_cuadro_7, $table );

    // el key debe ser igual a lo que se manejan en $table, si no no lo encontrará
    $datos = array( 
        'cuadro_1'=>$data_cuadro_1, 
        'cuadro_2'=>$data_cuadro_2, 
        'cuadro_3'=>$data_cuadro_3, 
        'cuadro_4'=>$data_cuadro_4, 
        'cuadro_5'=>$data_cuadro_5,
        'cuadro_6'=>$data_cuadro_6,
        'cuadro_7'=>$data_cuadro_7
    );

    $encabezado = array( 
        'cuadro_1'=>$headers_cuadro_1, 
        'cuadro_2'=>$headers_cuadro_2, 
        'cuadro_3'=>$headers_cuadro_3, 
        'cuadro_4'=>$headers_cuadro_4, 
        'cuadro_5'=>$headers_cuadro_5, 
        'cuadro_6'=>$headers_cuadro_6,
        'cuadro_7'=>$headers_cuadro_7
    );

    // Armamos los parametros opcionales
    $otrosParametros = array( 
        'comprimir'=>false, 
        'nombre'=> "cuadros_apoyo_" . $gpo . $ur . "_" . $mes,
        'cuadro_1' => array('nombre'=>'1_EfvoYEquiv'), 
        'cuadro_2' => array('nombre'=>'2_AdqActInv'), 
        'cuadro_3' => array('nombre'=>'3_ConcFlujoEfvoNeto'), 
        'cuadro_4' => array('nombre'=>'4_CIPyC'), 
        'cuadro_5' => array('nombre'=>'5_CEPyC'), 
        'cuadro_6' => array('nombre'=>'6_OrdPresIng'), 
        'cuadro_7' => array('nombre'=>'7_OrdPresEgr'), 
        'book'=>array(
            'cuadro_1', 
            'cuadro_2', 
            'cuadro_3', 
            'cuadro_4', 
            'cuadro_5', 
            'cuadro_6',
            'cuadro_7'
        )
    );

    $excel = new array2Excel();
    $excel->generaExcel( $datos, $encabezado, true, $table, $otrosParametros, "SEFIP2025", "#,##0" );

// Fin del código