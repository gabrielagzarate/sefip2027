<?php
    define ("_FTP_SERVER", "172.17.90.16");
    define ("_FTP_PASSWORD", "8L6c709!f3QE");
    define ("_FTP_PORT","21");
    define ("_FTP_USER","tesoreria");
    define ("_ERR_1","CTRLF:_ERR_1 -PARAMETROS INCORRECTOS ::");
    define ("_ERR_2","CTRLF:_ERR_2 -NO SE DETERMINO EL BANCO ::");
    define ("_ERR_3","CTRLF:_ERR_3 -NO SE DETERMINO EL NOMBRE DEL FICHERO A BUSCAR ::");
    define ("_ERR_4","CTRLF:_ERR_4 -NO SE DETERMINO DONDE GUARDAR EL FICHERO ::");
    define ("_ERR_5","CTRLF:_ERR_5 -CONEXION FALLIDA AL SERVIDOR FTP ::");
    define ("_ERR_6","CTRLF:_ERR_6 -OCURRIO UN ERROR AL DESCARGAR EL ARCHIVO ::");
    define ("_ERR_7","CTRLF:_ERR_7 -OCURRIO UN ERROR AL GUARDAR EL REGISTRO DE LA BASE DE DATOS ::");
    define ("_ERR_8","CTRLF:_ERR_8 - LA RUTA NO EXISTE ::");
    define ("_ERR_10","CTRLF:_ERR_10 - ERROR AL PROCESAR EL ARCHIVO ::");
    define ("_ERR_9","CTRLF:_ERR_9 - ERROR AL OBTENER LOS BASICOS DEL FICHERO ::");
    define ("_ERR_11","CTRLF:_ERR_11 - LA BASE DE DATOS NO REGRESO EL ID ADECUADAMENTE ::");
    define ("_ERR_12","CTRLF:_ERR_12 - ERROR CON LAS CREDENCIALES DEL FTP ::");
    define ("_ERR_13","CTRLF:_ERR_13 - ERROR AL OBTENER EL FICHERO DEL FTP ::");
    define ("_WAR_1","CTRLF:_WAR_1 - ERROR AL OBTENER LOS BASICOS DEL FICHERO, PERO SE CONTINUA ::");
    define ("_WAR_2","CTRLF:_WAR_2 - NO HAY FICHERO DEL BANCO SELECCIONADO ::");
    define ("_WAR_3","CTRLF:_WAR_3 - EL FICHERO YA HA SIDO GUARDADO EN LA BASE DE DATOS ::");
    define ("_WAR_4","CTRLF:_WAR_4 - OCURRION UN ERROR AL ACTUALIZAR EL AÑO SINPRES   ::");
    define ("_WAR_5","CTRLF:_WAR_5 - EL ARCHIVO YA HA SIDO PROCESADO ANTERIORMENTE ::");
    define ("_SUCCESS_1","CTRLF:SUCCES_1 - ARCHIVO CARGADO CORRECTAMENTE ::");
    define ("_SUCCESS_2","CTRLF:SUCCES_2 - ARCHIVO GUARDADO EXITOSAMENTE EN LA BASE DE DATOS ::");
    define ("_SUCCESS_3","CTRLF:SUCCES_3 - ARCHIVO PROCESADO CORRECTAMENTE ::");
    define ("__ERROR","error");
    define ("__MENSAJE","mensaje");
    
    $aproves = array(
        "127.0.0.1",
        "172.17.94.96",
        "172.17.90.16",
        "172.17.90.15"
    );
    $meses  = array("NINGUNO",
        "ENERO","FEBRERO","MARZO","ABRIL",
        "MAYO","JUNIO","JULIO","AGOSTO",
        "SEPTIEMBRE","OCTUBRE","NOVIEMBRE","DICIEMBRE",
    );
    $arr_bancos = array(
        1 => array(
            "iab_id" => "1",
            "iba_id" => "1",
            "nombre" => "Banamex",
            "nombreFichero" => "layout_bxconciliacion",
            "extension" => "*",
            "bancoDestino" => "banamex",
            "carpetaDestino" => "Banamex",
                "datePrm" => "Ymd"
        ),
        2 => array(
            "iab_id"=>"2",
            "iba_id"=>"2",
            "nombre"=>"Bancomer",
            "nombreFichero"=>"MH43",
            "extension"=>"*.TXT",
            "bancoDestino" =>"bancomer/netcas/egresosTesoreria",
            "carpetaDestino"=>"Bancomer",
            "datePrm" => "ymd"
        ),
        3 => array(
            "iab_id"=>"17",
            "iba_id"=>"7",
            "nombre"=>"Banorte",
            "nombreFichero"=>"SM022013_",
            "extension"=>".TXT",
            "bancoDestino" =>"banorte/Recibido",
            "carpetaDestino"=>"Banorte",
                "datePrm" => "md"
            ),
        4 => array(
            "iab_id"=>"5",
            "iba_id"=>"3",
            "nombre"=>"Hsbc",
            "nombreFichero"=>"REPMOVCON_DIARIO_MXSFOAXRDM-",
            "extension"=>"*.txt",
            "bancoDestino" =>"hsbc",
            "carpetaDestino"=>"HSBC",
            "datePrm" => "Ymd"
            ),
        5 => array(
            "iab_id" => "3",
            "iba_id" => "5",
            "nombre" => "Santander",
            "nombreFichero" => "80021459268000000014754",
            "extension" => "*.TXT",
            "bancoDestino" => "santander",
            "carpetaDestino" => "Santander",
            "datePrm" => "Ymd"
        ),
        6 => array(
            "iab_id" => "12",
            "iba_id" => "4",
            "nombre" => "Scotiabank",
            "nombreFichero" => "H2HSED0000000143000000055529",
            "extension" => ".2001007F1200001",
            "bancoDestino" => "scotiabank",
            "carpetaDestino" => "Scotiabank",
            "datePrm" => "Ymd"
        ),
        7 => array(
            "iab_id" => "10",
            "iba_id" => "8",
            "nombre" => "Interacciones",
            "nombreFichero" => "",
            "extension" => "",
            "bancoDestino" => "interacciones",
            "carpetaDestino" => "Interacciones",
            "datePrm" => "ymd"
        ),    
    );
