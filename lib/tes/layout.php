<?php
/*
 * Libreria encargada de la generación de los diferentes layouts
 *  de pagos, que maneja cada Institución Bancaria.
 */
function crea_bx($row, $cont)
{
    extract($row); //crear variables del arreglo del row automaticamente
    $espacio = chr(32);
    $salto = chr(13) . chr(10);

    //obtener valores de los bancos
    $clabeCargo = substr($ctaCargo, 0, 3); //clave del banco a donde se cargara el importe
    $clabeAbono = substr($ctaAbono, 0, 3); //clave delabeAbono a donde se abonara el importe
    // FIXME:***** Pos 1 ************************************************* /
    $linea1 = "PAY485";

    // FIXME:***** Pos 7 ************************************************* /
    $linea1 .= rellena_con($espacio, 10);

    // FIXME:***** Pos 17 ************************************************* /
    $linea1 .= AAMMDD($fecha); //cambia la fecha a formato AAMMDD
    // FIXME:***** Pos 23 ************************************************* /
    $linea1 .= $clabeAbono == "002" ? "072" : "001"; // solo se crean layouts para tranferencia
    // FIXME:***** Pos 26 ************************************************* /
    //2015-10-08
    $dato = $orden;
    if (isset($referencia)) {
        $dato = $referencia;
    }
    $linea1 .= rellena_con($espacio, 15, substr("$anio$tipo$dato UR$ur", 0, 15));

    // FIXME:***** Pos 41 ************************************************* /
    $linea1 .= rellena_con("0", 8, $cont, "izq"); //numero secuencial de la transaccion
    // FIXME:***** Pos 49 ************************************************* /
    $linea1 .= rellena_con($espacio, 20, "X");

    // FIXME:***** Pos 69 ************************************************* /
    $linea1 .= "MXN"; //moneda del pago (fijo)
    //**************************** CODIGO DEL BENEFICIARIO ****************************
    // FIXME:***** Pos 72 ************************************************* /
    $benf = substr("UR" . $ur . " " . (isset($ur_siglas)?$ur_siglas:''), 0, 20); //Codigo del Beneficiaro -> clc_beneficiario
    if (strlen($benf) < 20) {
        $benf = rellena_con($espacio, 20, limpiar($benf, "cd_b"));
    }
    $linea1 .= $benf; //insertar beneficiario
    // FIXME:***** Pos 92 ************************************************* /
    $import = $importe * 100; //quitar el punto decimal del importe multiplicandolo por 100

    if (strlen($import) < 15) {
        $import = rellena_con("0", 15, $import, "izq");
    } else {
        echo "ERROR DE LONGITUD EN EL IMPORTE";
        die;
    }
    $linea1 .= $import; //insertar importe
    // FIXME:***** Pos 107 ************************************************* /
    $linea1 .= rellena_con($espacio, 6); //como siempre seran transacciones este campo no se necesita poner
    // FIXME:***** Pos 113 ************************************************* /
    //$linea1.= rellena_con($espacio, 35,$orden);//referencia numerica
    if (isset($linea_captura)) {
        $linea1 .= rellena_con($espacio, 35, limpiar($linea_captura));
    } elseif ($ctaAbono=="002180087005035351") {
        $linea1 .= rellena_con($espacio, 35, "2089063");
    } else {
        $linea1 .= rellena_con($espacio, 35, $orden); //referencia numerica
    }

        /** *************************** referencia alfanumerica******************************* */
    // FIXME:***** Pos 148 ************************************************* /
    //$ref_alfa = limpiar($tipo." ".$orden." ".$concepto,"ref");

    if (isset($linea_captura)) {
        $ref_alfa = limpiar($linea_captura);
    } elseif ($ctaAbono=="002180087005665530") {
        $ref_alfa = "PROV0000000002120201";
    } elseif ($ctaAbono=="002180087005035351") {
        $ref_alfa = "2089063";
    } elseif ($ctaAbono=="002180087005738357") {
        $ref_alfa = "2074199301470";
    } else {
        $ref_alfa = limpiar($anio . ' ' . $tipo . " " . $orden . " " . trim($concepto), "ref");
    }
    
    

    if (strlen($ref_alfa) >= 35) {
        $ref_alfa = substr($ref_alfa, 0, 35);
    } else {
        $ref_alfa = rellena_con($espacio, 35, $ref_alfa);
    }
    $linea1 .= $ref_alfa; //insertar referencia alfanumerica en este caso concepto del pago
    // FIXME:***** Pos 183 ************************************************* /
    /*     * ************************* mas detalles ***************** */
    $linea1 .= rellena_con($espacio, 70);

    // FIXME:***** Pos 253 ************************************************* /
    /*     * ************************ comprobande fiscal ?? ******************** */
    $linea1 .= "05"; //05 sin comprobante, 06 con oomprobante
    // FIXME:***** Pos 255 ************************************************* /
    $linea1 .= "01"; //tipo de cuenta CHEQUES

    /*     * ************************* NOMBRE Y DIRECCIONES DEL BENEFICIARIO *************** */
    // FIXME:***** Pos 257 ************************************************* /
    $benf = substr(limpiar($benef, "nm_b"), 0, 35); //Codigo del Beneficiaro -> clc_beneficiario
    $benf = trim($benf);
    if (strlen($benf) < 35) {
        $benf = rellena_con($espacio, 35, $benf);
    }
    $linea1 .= $benf; //insertar nombre del beneficiario solo 35 caracteres para mexico
    //completar los 80 espacios reservados
    $linea1 .= rellena_con($espacio, 45);

    // FIXME:***** Pos 337 ************************************************* /
    //70 espacios por las direcciones del beneficiario
    if (isset($linea_captura)) {
        if (strlen($folios) > 70) {
            $folios = substr($folios, 0, 70);
        }
        $linea1 .= rellena_con($espacio, 70, $folios);
    } else {
        $linea1 .= rellena_con($espacio, 70, "X");
    }


    // FIXME:***** Pos 407 ************************************************* /
    //otros 45 en blanco
    $linea1 .= rellena_con($espacio, 45);

    // FIXME:***** Pos 452 ************************************************* /
    $linea1 .= $clabeAbono == "002" ? "000" : $clabeAbono; //codigo del banco del beneficiario
    // FIXME:***** Pos 455 ************************************************* /
    $linea1 .= rellena_con($espacio, 8);

    // FIXME:***** Pos 463 ************************************************* /
    /*     * ********************* cuenta del beneficiario ********************* */
    $linea1 .= rellena_con($espacio, 35, $ctaAbono);

    // FIXME:***** Pos 498 ************************************************* /
    $linea1 .= "05"; //para CLABE interbancaria
    // FIXME:***** Pos 500 ************************************************* /
    $linea1 .= rellena_con($espacio, 30, "BcoBenef");

    // FIXME:***** Pos 530 ************************************************* /
    /*     * *********************** impuesto *************************** */
    $linea1 .= rellena_con("0", 17);
    // FIXME:***** Pos 547 y 548 ************************************************* /
    $linea1 .= "N"; //TODO: prioridad de dispersion ??
    $linea1 .= "N"; //TODO: confidencialS ??
    // FIXME:***** Pos 549 ************************************************* /
    $linea1 .= $clabeAbono == "002" ? rellena_con($espacio, 2) : "01"; //SPEI
    // FIXME:***** Pos 551 ************************************************* /
    /*     * ********************* cuenta CITIDIRECT ****************** */
    $linea1 .= rellena_con($espacio, 20, substr($ctaCargo, 6, 11)); //TODO: cual es la cuenta CITI ??
    // FIXME:***** Pos 571 ************************************************* /
    $linea1 .= rellena_con($espacio, 116);

    // FIXME:***** Pos 687 ************************************************* /
    $linea1 .= "00000"; //codigo de activacion
    // FIXME:***** Pos 692 ************************************************* /
    $linea1 .= rellena_con($espacio, 50, "banamex.pagos@finanzasoaxaca.gob.mx");

    // FIXME:***** Pos 742 ************************************************* /
    $linea1 .= rellena_con(9, 15);

    // FIXME:***** Pos 757 ************************************************* /
    $linea1 .= $espacio;

    // FIXME:***** Pos 758 ************************************************* /
    $linea1 .= rellena_con($espacio, 11, "E-Mail");

    // FIXME:***** Pos 769 ************************************************* /
    $linea1 .= rellena_con($espacio, 256);
    $linea1 .= $salto;

    return $linea1; //regresar las dos lineas creadas
}
function crea_bxPdp($row, $cont)
{
    extract($row); //crear variables del arreglo del row automaticamente
    $espacio = chr(32); //insertar un caracter de espacio
    $salto = chr(13) . chr(10); //avanza una linea

    /*     * FIXME:*****************************************inicio del proceso*********************************** */
    //obtener valores de los bancos
    $clabeCargo = substr($ctaCargo, 0, 3); //clave del banco a donde se cargara el importe
    $clabeAbono = substr($ctaAbono, 0, 3);

    $linea1 = "PAY485";

    // FIXME:***** Pos 7 ************************************************* /
    $linea1 .= rellena_con($espacio, 10);

    // FIXME:***** Pos 17 ************************************************* /
    $linea1 .= AAMMDD($fecha); //cambia la fecha a formato AAMMDD
    // FIXME:***** Pos 23 ************************************************* /
    $linea1 .= $clabeAbono == "002" ? "072" : "001"; // solo se crean layouts para tranferencia
    // FIXME:***** Pos 26 ************************************************* /
    //$linea1.=rellena_con($espacio,15,substr("$tipo$orden UR$ur_clave",0,15));
    //naturaleza del movimiento + numero del movimiento
    //2015-10-08
    $dato = $documento_id;
    if (isset($referencia)) {
        $dato = $referencia;
    }
    $linea1 .= rellena_con($espacio, 15, substr("$anio$tipo$dato UR$ur_clave", 0, 15));

    // FIXME:***** Pos 41 ************************************************* /
    $linea1 .= rellena_con("0", 8, $cont, "izq"); //numero secuencial de la transaccion
    // FIXME:***** Pos 49 ************************************************* /
    $linea1 .= rellena_con($espacio, 20, $rfc);

    // FIXME:***** Pos 69 ************************************************* /
    $linea1 .= "MXN"; //moneda del pago (fijo)
    //**************************** CODIGO DEL BENEFICIARIO ****************************
    // FIXME:***** Pos 72 ************************************************* /
    $benf = substr("UR" . $ur_clave . " " . (isset($ur_siglas)?$ur_siglas:''), 0, 20); //Codigo del Beneficiaro -> clc_beneficiario
    if (strlen($benf) < 20) {
        $benf = rellena_con($espacio, 20, limpiar($benf, "cd_b"));
    }
    $linea1 .= $benf; //insertar beneficiario
    // FIXME:***** Pos 92 ************************************************* /
    $import = $importe * 100; //quitar el punto decimal del importe multiplicandolo por 100

    if (strlen($import) < 15) {
        $import = rellena_con("0", 15, $import, "izq");
    } else {
        echo "ERROR DE LONGITUD EN EL IMPORTE";
        die;
    }
    $linea1 .= $import; //insertar importe
    // FIXME:***** Pos 107 ************************************************* /
    $linea1 .= rellena_con($espacio, 6); //como siempre seran transacciones este campo no se necesita poner
    // FIXME:***** Pos 113 ************************************************* /
    //$linea1.= rellena_con($espacio, 35,$orden);//referencia numerica
    if (isset($linea_captura)) {
        $linea1 .= rellena_con($espacio, 35, limpiar($linea_captura));
    } elseif ($ctaAbono=="002180087005035351") {
        $linea1 .= rellena_con($espacio, 35, "2089063");
    } else {
        $linea1 .= rellena_con($espacio, 35, $orden); //referencia numerica
    }
        /*         * *************************** referencia alfanumerica******************************* */
    // FIXME:***** Pos 148 ************************************************* /
    //$ref_alfa = limpiar($tipo." ".$orden." ".$concepto,"ref");
    $documentoLimpio =limpiar($documento, "ref");
    if (isset($linea_captura)) {
        $ref_alfa = limpiar($linea_captura);
    } elseif ($ctaAbono=="002180087005035351") {
        $ref_alfa = "2089063";
    } elseif ($ctaAbono=="002180087005738357") {
        $ref_alfa = "2074199301470";
    } else {
        $ref_alfa = limpiar($anio . ' ' . $tipo . " " . $orden . " " . $documento_id . " FACT " .$documentoLimpio);
    }

    if (strlen($ref_alfa) >= 105) {
        $ref_alfa = substr($ref_alfa, 0, 105);
    } else {
        $ref_alfa = rellena_con($espacio, 105, $ref_alfa);
    }
    $linea1 .= $ref_alfa; //insertar referencia alfanumerica en este caso concepto del pago
    // FIXME:***** Pos 183 ************************************************* /
    /*     * ************************* mas detalles ***************** */
    // $linea1.= rellena_con($espacio, 70);
    // FIXME:***** Pos 253 ************************************************* /
    /*     * ************************ comprobande fiscal ?? ******************** */
    $linea1 .= "05"; //05 sin comprobante, 06 con oomprobante
    // FIXME:***** Pos 255 ************************************************* /
    $linea1 .= "01"; //tipo de cuenta CHEQUES

    /*     * ************************* NOMBRE Y DIRECCIONES DEL BENEFICIARIO *************** */
    // FIXME:***** Pos 257 ************************************************* /
    $benf = substr(limpiar($benef, "nm_b"), 0, 35); //Codigo del Beneficiaro -> clc_beneficiario
    $benef = trim($benef);
    if (strlen($benf) < 35) {
        $benf = rellena_con($espacio, 35, $benf);
    }
    $linea1 .= $benf; //insertar nombre del beneficiario solo 35 caracteres para mexico
    //completar los 80 espacios reservados
    $linea1 .= rellena_con($espacio, 45);

    // FIXME:***** Pos 337 ************************************************* /
    //70 espacios por las direcciones del beneficiario
    $linea1 .= rellena_con($espacio, 70, "X");

    // FIXME:***** Pos 407 ************************************************* /
    //otros 45 en blanco
    $linea1 .= rellena_con($espacio, 45);

    // FIXME:***** Pos 452 ************************************************* /
    $linea1 .= $clabeAbono == "002" ? "000" : $clabeAbono; //codigo del banco del beneficiario
    // FIXME:***** Pos 455 ************************************************* /
    $linea1 .= rellena_con($espacio, 8);

    // FIXME:***** Pos 463 ************************************************* /
    /*     * ********************* cuenta del beneficiario ********************* */
    $linea1 .= rellena_con($espacio, 35, $ctaAbono);

    // FIXME:***** Pos 498 ************************************************* /
    $linea1 .= "05"; //para CLABE interbancaria
    // FIXME:***** Pos 500 ************************************************* /
    $linea1 .= rellena_con($espacio, 30, "BcoBenef");

    // FIXME:***** Pos 530 ************************************************* /
    /*     * *********************** impuesto *************************** */
    $linea1 .= rellena_con("0", 17);

    // FIXME:***** Pos 547 y 548 ************************************************* /
    $linea1 .= "N"; //TODO: prioridad de dispersion ??
    $linea1 .= "N"; //TODO: confidencialS ??
    // FIXME:***** Pos 549 ************************************************* /
    $linea1 .= $clabeAbono == "002" ? rellena_con($espacio, 2) : "01"; //SPEI
    // FIXME:***** Pos 551 ************************************************* /
    /*     * ********************* cuenta CITIDIRECT ****************** */
    $linea1 .= rellena_con($espacio, 20, substr($ctaCargo, 6, 11)); //TODO: cual es la cuenta CITI ??
    // FIXME:***** Pos 571 ************************************************* /
    $linea1 .= rellena_con($espacio, 116);

    // FIXME:***** Pos 687 ************************************************* /
    $linea1 .= "00000"; //codigo de activacion
    // FIXME:***** Pos 692 ************************************************* /
    $linea1 .= rellena_con($espacio, 50, "banamex.pagos@finanzasoaxaca.gob.mx");

    // FIXME:***** Pos 742 ************************************************* /
    $linea1 .= rellena_con(9, 15);

    // FIXME:***** Pos 757 ************************************************* /
    $linea1 .= $espacio;

    // FIXME:***** Pos 758 ************************************************* /
    $linea1 .= rellena_con($espacio, 11, "E-Mail");

    // FIXME:***** Pos 769 ************************************************* /
    $linea1 .= rellena_con($espacio, 256);
    $linea1 .= $salto;
    return $linea1; //regresar las dos lineas creadas
}

function BANCOMER($row, $tipomov)
{
    extract($row);
    $anioClc=$anio;
    $salto = chr(13) . chr(10);
    $cbeCargo = substr($ctaCargo, 0, 3);
    $cbeAbono = substr($ctaAbono, 0, 3);
    $mixt = '';
    if ($cbeCargo == $cbeAbono && $tipomov == 1) {
        $mixt = 'TNN';
    }
    if ($cbeCargo != $cbeAbono && $tipomov == 1) {
        $mixt = 'TSC';
    }
    if ($cbeCargo == $cbeAbono && $tipomov == 0) {
        $mixt = 'PTC';
    }
    if ($cbeCargo != $cbeAbono && $tipomov == 0) {
        $mixt = 'PSC';
    }
    $espacio = chr(32);
    $dia = strftime("%d");
    $mes = strftime("%m");
    $anio = strftime("%Y");
    $fecha = $dia . "/" . $mes . "/" . $anio;
    $fecha_formateada = $dia . $mes . $anio;
    $fecha_base = substr($anio, 2, 2) . $mes . $dia . "000000";
    $cuenta_cargo = str_pad(substr($ctaCargo, 6, 11), 18, "0", STR_PAD_LEFT);
    $importe1 = 1;
    $moneda = "MXP";
    $importe = str_pad($importe, 16, "0", STR_PAD_LEFT);
    $trans = array("À" => "A", "È" => "E", "Ì" => "I", "Ò" => "O", "Ù" => "U", "{" => " ", "}" => " "
    , "$" => " ", "|" => " ", "\"" => " ", "Á" => "A", "É" => "E", "Í" => "I", "Ó" => "O"
    , "Ú" => "U", "º" => " ", "ª" => " ", "(" => "", ")" => "", "." => "", "%" => "", "/" => ""
    , "-" => "", "," => "", ":" => "", ";" => "", "_" => "", ":" => "", "Ñ" => "N", "°" => " ");
    
    $comprobante = 0;
    $rfc = str_pad(" ", 18);
    $iva = "000000000000.00";
    if ($mixt == "TNN" || $mixt == "PTC") {
        $string_mayus = strtoupper(substr(strtr($anioClc . $tipo . $orden . " UR" . $ur, $trans), 0, 30));
        $string_mayus=str_pad($string_mayus, 30, $espacio, STR_PAD_RIGHT);
        $string_mayus = limpiar($string_mayus, "ref");
        $cuenta_abono = str_pad(substr($ctaAbono, 6, 11), 18, "0", STR_PAD_LEFT);
        $referencia = str_pad($string_mayus, 30, $espacio);
        $referencia = limpiar($referencia, "ref");
        $lay_linea = $mixt . $cuenta_abono . $cuenta_cargo . $moneda . $importe . $referencia;
    } else {
        $cuenta_abono = str_pad(substr($ctaAbono, 0, 18), 18, "0", STR_PAD_LEFT);
        $tipo_cuenta = 40;
        $referencia = str_pad($anioClc . $tipo . $orden, 7, "0", STR_PAD_LEFT);
        $referencia= substr($referencia, 0, 7);
        $referencia = limpiar($referencia, "ref");
        $numero_banco = $cbeAbono;
        $string_mayus = strtoupper(substr(strtr($benef, $trans), 0, 30));
        
        $string_mayus=str_pad($string_mayus, 30, $espacio, STR_PAD_RIGHT);
        $string_mayus_concepto = strtoupper(substr(strtr($anioClc.$tipo.$orden." UR".$ur, $trans), 0, 30));
        $string_mayus_concepto = limpiar($string_mayus_concepto,"esp");
        $motivo_pago = str_pad($string_mayus_concepto, 30, $espacio);
        $disponibilidad = "H";
        // Ojo: Del ticket #287505 quitar la referencia y dejar espacios en blanco.
        $espaciosAdejar=7;
        $referencia = str_pad("", $espaciosAdejar, "0");
        $lay_linea = $mixt . $cuenta_abono . $cuenta_cargo . $moneda . $importe . $string_mayus .
                $tipo_cuenta . $numero_banco . $motivo_pago . $referencia .
                $disponibilidad;
    }
    $lay_linea .= $salto;
    return $lay_linea;
}

// fin de la funcion bancomer lay mixto

function BANORTE_INTERNO($row)
{
    extract($row);
    $anioClc = $anio;
    $espacio = chr(32);
    $salto = chr(13) . chr(10);
    $trans = array("À" => "A", "È" => "E", "Ì" => "I", "Ò" => "O", "Ù" => "U", "{" => " ",
        "}" => " ", "$" => " ", "|" => " ", "\"" => " ","Á" => "A", "É" => "E", "Í" => "I",
        "Ó" => "O", "Ú" => "U", "º" => " ", "ª" => " ", "(" => "", ")" => "", "." => "",
        "%" => "", "/" => "", "-" => "", "," => "", ":" => "", ";" => "", "_" => "", ":" => "",
        "Ñ" => "N");
    $operacion = "";
    $clabeDestino = substr($ctaAbono, 0, 3);
    $clabeCargo = substr($ctaCargo, 0, 3);
    // if (($clabeDestino == $clabeCargo) || ($clabeDestino == "072" || $clabeDestino == "037")) {
        $operacion = '02';
        $pos = 10;
        $cuenta_abono = substr($ctaAbono, 6, 11);
        $cuenta_destino = str_pad($cuenta_abono, 20, "0", STR_PAD_LEFT);
        $instruccion= str_pad(" ", 69, $espacio);
    // } 
    $clave_id = str_pad("567981", 13, $espacio);
    $cuenta_cargo = substr($ctaCargo, 6, 11);
    $cuenta_origen = str_pad($cuenta_cargo, 20, "0", STR_PAD_LEFT);
    $importes = $importe;
    $importe_sin_punto = strtr($importes, $trans);
    $importe2 = str_pad($importe_sin_punto, 14, "0", STR_PAD_LEFT);
    $ref = $anioClc . $tipo . $orden;
    $ref=substr($ref, 0, 10);
    $referencia = str_pad($ref, 10, "0", STR_PAD_LEFT);
    $concepto=str_pad($anio.$tipo.$orden." UR".$ur, 30, $espacio);
    $concepto=limpiar($concepto,"esp");
    $concepto=substr($concepto, 0, 30);
    $monedaOr=1;
    $monedaDet=1;
    $rfc = "GEO621201KIA ";
    $iva = "00000000000000";
    $email= rellena_con($espacio, 39, "banorte.pagos@finanzasoaxaca.gob.mx");
    $fecha_aplicacion = str_pad(" ", 8, $espacio);
   
    $lay_linea = $operacion . $clave_id . $cuenta_origen . $cuenta_destino . $importe2 . $referencia . $concepto .
            $monedaOr.$monedaDet.$rfc . $iva .$email. $fecha_aplicacion . $instruccion."X";
    

    $lay_linea .= $salto;
    return $lay_linea;
}
function BANORTE_EXTERNO($row)
{
    extract($row);
    $espacio = chr(32);
    $salto = chr(13) . chr(10);
    $trans = array("À" => "A", "È" => "E", "Ì" => "I", "Ò" => "O", "Ù" => "U", "{" => " ",
        "}" => " ", "$" => " ", "|" => " ", "\"" => " ","Á" => "A", "É" => "E", "Í" => "I",
        "Ó" => "O", "Ú" => "U", "º" => " ", "ª" => " ", "(" => "", ")" => "", "." => "",
        "%" => "", "/" => "", "-" => "", "," => "", ":" => "", ";" => "", "_" => "", ":" => "",
        "Ñ" => "N");
    $operacion = "";
    $clabeDestino = substr($ctaAbono, 0, 3);
    $clabeCargo = substr($ctaCargo, 0, 3);    
        $operacion = '04';
        $cuenta_destino = str_pad($ctaAbono, 20, "0", STR_PAD_LEFT);
        $pos = 7;
        $anioClc = substr($anio, -1);
        $string_bene = strtoupper(substr(strtr($benef, $trans), 0, 69));
        $instruccion = str_pad($string_bene, 69, $espacio, STR_PAD_RIGHT);
    $clave_id = str_pad("567981", 13, $espacio);
    $cuenta_cargo = substr($ctaCargo, 6, 11);
    $cuenta_origen = str_pad($cuenta_cargo, 20, "0", STR_PAD_LEFT);
    $importes = $importe;
    $importe_sin_punto = strtr($importes, $trans);
    $importe2 = str_pad($importe_sin_punto, 14, "0", STR_PAD_LEFT);
    $ref = $anioClc . $tipo . $orden;
    $ref=substr($ref, 0, 10);
    $referencia = str_pad($ref, 10, "0", STR_PAD_LEFT);
    $concepto=str_pad($anio.$tipo.$orden." UR".$ur, 30, $espacio);
    $concepto=limpiar($concepto,"esp");
    $concepto=substr($concepto, 0, 30);
    $monedaOr=1;
    $monedaDet=1;
    $rfc = "GEO621201KIA ";
    $iva = "00000000000000";
    $email= rellena_con($espacio, 39, "banorte.pagos@finanzasoaxaca.gob.mx");
    $fecha_aplicacion = str_pad(" ", 8, $espacio);
   
    $lay_linea = $operacion . $clave_id . $cuenta_origen . $cuenta_destino . $importe2 . $referencia . $concepto .
            $monedaOr.$monedaDet.$rfc . $iva .$email. $fecha_aplicacion . $instruccion."X";
    

    $lay_linea .= $salto;
    return $lay_linea;
}

// termina la funcion de banorte interna

function SANTANDER_INTERNO($row)
{
   
    extract($row);
    $anioClc=$anio;
    $espacio = chr(32);
    $salto = chr(13) . chr(10);
    $dia = strftime("%d");
    $mes = strftime("%m");
    $anio = strftime("%Y");
    $fecha = $dia . "/" . $mes . "/" . $anio;
    $fecha_formateada = $dia . $mes . $anio;
    $fecha_base = substr($anio, 2, 2) . $mes . $dia . "000000";

    /* Se extraen los digitos de la cuenta de cargo */
    $cuenta_cargo = substr($ctaCargo, 6, 11);
    $espacios = str_pad(" ", 7, $espacio);
    /* se extraen los digito de la cuenta de abono */
    $cuenta_abono = substr($ctaAbono, 6, 11);
    $espaciosParaCuentaAbono = str_pad(" ", 9, $espacio);
    $trans = array("À" => "A", "È" => "E", "Ì" => "I", "Ò" => "O", "Ù" => "U", "{" => " ", "}" => " ", "$" => " ",
    "|" => " ", "\"" => " ", "Á" => "A", "É" => "E", "Í" => "I", "Ó" => "O", "Ú" => "U",
    "º" => " ", "ª" => " ", "(" => "", ")" => "", "." => "", "%" => "", "/" => "", "-" => "",
    "," => "", ":" => "", ";" => "", "_" => "", ":" => "", "Ñ" => "N");
    $importe_sin_punto = strtr($importe, $trans);
    $importe = str_pad($importe_sin_punto, 18, "0", STR_PAD_LEFT);
   
    $string_mayus =substr($anioClc.$tipo.$orden. " UR". $ur, 0, 40);
    $string_mayus = limpiar($string_mayus,"esp");
    $concepto = str_pad($string_mayus, 40, $espacio);
    $mail_beneficiario=str_pad("santander.pagos@finanzasoaxaca.gob.mx",40, " ");
    $lay_linea = 'LTX07'.$cuenta_cargo . $espacios . $cuenta_abono . $espaciosParaCuentaAbono . $importe . $concepto . $fecha_formateada.$mail_beneficiario;

    // $lay_linea = limpiar($lay_linea, "ref");
    $lay_linea .= $salto;
    return $lay_linea;
}

//fin de la funcion para cuentas del banco serfin

function SANTANDER_EXTERNO($row)
{
    extract($row);
    $espacio = chr(32);
    $salto = chr(13) . chr(10);
    /* Se declaran las claves de bancos */
    $banco["001"] = "BANCO";
    $banco["000"] = "BANCO";
    $banco["006"] = "BCEXT";
    $banco["009"] = "BOBRA";
    $banco["019"] = "BEJER";
    $banco["135"] = "NAFIN";
    $banco["166"] = "BANSE";
    $banco["168"] = "HIFED";
    $banco["002"] = "BANAM";
    $banco["012"] = "BACOM";
    $banco["014"] = "BANME";
    $banco["021"] = "BITAL";
    $banco["030"] = "BAJIO";
    $banco["032"] = "BAIXE";
    $banco["036"] = "BINBU";
    $banco["037"] = "BINTE";
    $banco["042"] = "MIFEL";
    $banco["044"] = "COMER";
    $banco["058"] = "BANRE";
    $banco["059"] = "BINVE";
    $banco["060"] = "BANSI";
    $banco["062"] = "BAFIR";
    $banco["072"] = "BBANO";
    $banco["102"] = "ABNBA";
    $banco["103"] = "AMEX";
    $banco["106"] = "BAMSA";
    $banco["108"] = "TOKYO";
    $banco["110"] = "CHASE";
    $banco["112"] = "CMCA";
    $banco["113"] = "DRESD";
    $banco["116"] = "INGBA";
    $banco["124"] = "DEUTB";
    $banco["126"] = "CRESU";
    $banco["127"] = "BAZTE";
    $banco["128"] = "BAUTO";
    $banco["129"] = "BARCL";
    $banco["130"] = "BCOMP";
    $banco["131"] = "FAMSA";
    $banco["132"] = "MULTI";
    $banco["133"] = "PRUDE";
    $banco["134"] = "BWALL";
    $banco["136"] = "REGIO";
    $banco["137"] = "COPEL";
    $banco["138"] = "AMIGO";
    $banco["139"] = "UBSBA";
    $banco["140"] = "FACIL";
    $banco["141"] = "VOLKS";
    $banco["143"] = "CONSU";


    /* se inicia la creacion del layout del banco serfin Se extraen los digitos de la cuenta de cargo */
    $cuenta_cargo = str_pad(substr($ctaCargo, 6, 11), 18, $espacio);
    /* la cuenta de abono queda con una longitud determinada de 20 caracteres */
    $cuenta_abono = str_pad($ctaAbono, 20, $espacio);
    /* se identifica la clave del banco de la cuenta bancaria de abono */
    $clave_banco = substr($ctaAbono, 0, 3);

    /* identificando la clave del banco se obtiene su equivalencia */
    $ini_banco = str_pad($banco[$clave_banco],5,$espacio);

    $trans = array("À" => "A", "È" => "E", "Ì" => "I", "Ò" => "O", "Ù" => "U", "{" => " ", "}" => " ",
    "$" => " ", "|" => " ", "\"" => " ", "Á" => "A", "É" => "E", "Í" => "I", "Ó" => "O", "Ú" => "U",
    "º" => " ", "ª" => " ", "(" => "", ")" => "", "." => "", "%" => "", "/" => "", "-" => "",
    "," => "", ":" => "", ";" => "", "_" => "", ":" => "", "Ñ" => "N", "´" => " ", "*" => " ", "¨" => " ");
    /* se obtiene el beneficiario */
    $string_mayus = strtoupper(substr(strtr($benef, $trans), 0, 40));
    $beneficiario2 = str_pad($string_mayus, 40, $espacio);
    $sucursal = "0000";
    /* se obtiene y da formato al importe */
    $importes = $importe;
    $importe_sin_punto = strtr($importes, $trans);
    $importe2 = str_pad($importe_sin_punto, 18, "0", STR_PAD_LEFT);
    $importe_format = str_pad($importe2, 18, "0");
    $plazabanxico = "00000";
    /* Se obtiene el concepto con un texto de 40 posiciones */
    $string_mayus_concep = strtoupper(substr(strtr($anio.$tipo.$orden. " UR" .$ur, $trans), 0, 40));
    $string_mayus_concep = limpiar($string_mayus_concep,"esp");
    $conceptos = str_pad($string_mayus_concep, 40, $espacio);
    // Ojo: del ticket 815224 cuando se genera un layout que viene de una propuesta que se hizo desde el módulo
    //          de programacion entonces checamos la longitud de $order.
    //          Si la longitu de orden es <=4 entonces los espacios que se dejaran seran de 90
    $espaciosAdejar=89;
    $espaciosAdejar=(strlen($orden)<=4)?90:$espaciosAdejar;
    $espacios = str_pad(" ", $espaciosAdejar, $espacio);
    /* La orden de referencia se rellena a 7 posiciones con 0 a la izquierda */
    $referencia_ordenante = str_pad($anio.$tipo.$orden, 7, "0", STR_PAD_LEFT);
    $forma_apli = 1;
    $mail_beneficiario=str_pad("santander.pagos@finanzasoaxaca.gob.mx",40, " ");
    $lay_linea = 'LTX05'.$cuenta_cargo . $cuenta_abono . $ini_banco . $beneficiario2 . $sucursal . $importe_format .
            $plazabanxico . $conceptos . $referencia_ordenante;

    $lay_linea = limpiar($lay_linea, "ref");
    $lay_linea .= $mail_beneficiario;
    $lay_linea .= $salto;
    return $lay_linea;
}

//termina la funcion del banco serfin

function SCOTIABANK($row, $secuencia)
{
    extract($row);
    $anioClc=$anio;
    $dia = strftime("%d");
    $mes = strftime("%m");
    $anio = strftime("%Y");
    $tot_mov_cta = 0;
    $imp_mov_cta = 0;
    $salto = chr(13) . chr(10);
    $detalle_cuenta = '';
    $detalle_mail_cuenta = '';
    $ctaban_cargo = '';
    $trans = array("À" => "A", "È" => "E", "Ì" => "I", "Ò" => "O", "Ù" => "U",
    "{" => " ", "}" => " ", "$" => " ", "|" => " ", "\"" => " ", "Á" => "A", "É" => "E", "Í" => "I", "Ó" => "O",
    "Ú" => "U", "º" => " ", "ª" => " ", "(" => "", ")" => "", "." => "", "%" => "",
    "/" => "", "-" => "", "," => "", ":" => "", ";" => "", "_" => "", ":" => "", "Ñ" => "N", "´" => " ", "*" => " ", "¨" => " ");

    $tot_mov_cta++;

    $tipo_archivo_det_cta = "EE";
    $tipo_registro_det_cta = "DA";
    $tipo_movimiento = "04";
    $cve_moneda_pago = "00";
    $importe_orden = str_pad($importe * 100, 15, "0", STR_PAD_LEFT); //SACAR DE LA CONSULTA
    $fecha_aplicacion = $anio . $mes . $dia; //FECHA DE APLICACION
    $servicio_concepto = "03"; //CONSTANTE. OTRAS TRANSFERENCIAS
    $cve_beneficiario = str_pad($anioClc.$tipo.$orden." UR" . $ur, 20, " ", STR_PAD_RIGHT);
    $rfc_beneficiario = str_repeat(" ", 13); //RFC DEL BENEFICIARIO. RELLENAR CON ESPACIOS
    $nombre_beneficiario = str_pad(substr(strtr($benef, $trans), 0, 40), 40, " ", STR_PAD_RIGHT);
    $referencia_pago = str_pad($anioClc . $tipo . $orden, 16, "0", STR_PAD_LEFT);
    $referencia_pago=substr($referencia_pago, 0, 16);
    $plaza_pago = str_repeat("0", 5); //CONSTANTE, CEROS
    $sucursal_pago = str_repeat("0", 5); //CONSTANTE, CEROS
    $num_cta_beneficiario = str_pad($ctaAbono, 20, "0", STR_PAD_LEFT);
    $pais = str_repeat("0", 5); //CONSTANTE, CEROS
    $ciudad = str_repeat(" ", 40); //CONSTANTE, ESPACIOS
    $tipo_cuenta = "9"; //CONSTANTE. CUENTA CLABE => 9
    $digito_intercambio = " "; //CONSTANTE. ESPACIO
    $plaza_cuenta_banco = str_repeat("0", 5); //CONSTANTE, CEROS
    $num_banco_emisor = "044"; //CONSTANTE. SCOTIABANK => 044
    $num_banco_receptor = substr($ctaAbono, 0, 3);
    $dias_vigencia = "001"; //CONSTANTE
    $concepto=substr($concepto, 0, 50);
    $concepto=limpiar($concepto,"esp");
    $concepto_pago = str_pad($concepto, 50, " ", STR_PAD_RIGHT); //SACAR DE LA CONSULTA, CERE O CERECO Y EL NÚMERO
    $uso_empresa_1 = str_repeat(" ", 20); //SACAR $ur DE LA CONSULTA. ES LA CLAVE DEL BENEFICIARIO
    $uso_empresa_2 = str_repeat(" ", 20);
    $uso_empresa_3 = str_repeat(" ", 20);

    //ESTOS CAMPOS LOS OCUPA SCOTIA PARA LA RESPUESTA///////////////////////
    $codigo_status_registro_det_cta = str_repeat("0", 3); //CONSTANTE, 3 CERO´S
    $cve_cambio_inst = "0";
    $codigo_status_cambio_inst = str_repeat("0", 3); //CONSTANTE, 3 CERO´S
    $fecha_pago_det_cta = str_repeat("0", 8); //CONSTANTE, 8 CERO´S
    $plaza_pago_det_cta = str_repeat("0", 5); //CONSTANTE, 8 CERO´S
    $suc_pago_det_cta = str_repeat("0", 5); //CONSTANTE, 8 CERO´S
    $filler_det_cta = str_repeat(" ", 22); //CONSTANTE. 22 ESPACIOS
    ////////////////////////////////////////////////////////////////////////

    //DETALLE CUENTA 
    $detalle_cuenta .= $tipo_archivo_det_cta .
            $tipo_registro_det_cta .
            $tipo_movimiento .
            $cve_moneda_pago .
            $importe_orden .
            $fecha_aplicacion .
            $servicio_concepto .
            $cve_beneficiario .
            $rfc_beneficiario .
            $nombre_beneficiario .
            $referencia_pago .
            $plaza_pago .
            $sucursal_pago .
            $num_cta_beneficiario .
            $pais .
            $ciudad .
            $tipo_cuenta .
            $digito_intercambio .
            $plaza_cuenta_banco .
            $num_banco_emisor .
            $num_banco_receptor .
            $dias_vigencia .
            $concepto_pago .
            $uso_empresa_1 .
            $uso_empresa_2 .
            $uso_empresa_3 .
            $codigo_status_registro_det_cta .
            $cve_cambio_inst .
            $codigo_status_cambio_inst .
            $fecha_pago_det_cta .
            $plaza_pago_det_cta .
            $suc_pago_det_cta .
            $filler_det_cta .
            $salto;
    $imp_mov_cta += $importe;

    //DETALLE MAIL - CUENTA    
    $tipo_archivo_mail_cta = "EE"; //CONSTANTE ARCHIVO DE MOVIMIENTOS DE ENTRADA
    $tipo_registro_mail_cta = "DM"; //CONSTANTE DETALE DE MAIL -CUENTA
    $mail_beneficiario=str_pad("scotiabank.pagos@finanzasoaxaca.gob.mx", 100, " "); //MAIL
    $uso_futuro = str_repeat("0", 34); //CONSTANTE, 34 CEROS
    $filler_mail_cta = str_repeat(" ", 232); //CONSTANTE, 232 ESPACIOS

    $detalle_mail_cuenta = $tipo_archivo_mail_cta .
            $tipo_registro_mail_cta .
            $mail_beneficiario . 
            $uso_futuro .
            $filler_mail_cta .
            $salto;

    /*     * *************************ENCABEZADO CUENTA 1 $encabezado_cuenta **************/
    $tipo_archivo_cab_cta = "EE"; //CONSTANTE. ARCHIVO DE MOVIMIENTOS DE ENTRADA
    $tipo_registro_cab_cta = "HB"; //CONSTANTE
    $moneda_cta_cargo = "00"; //CONSTANTE. MONEDA LOCAL 00
    $uso_futuro = str_repeat("0", 4); //CONSTANTE. USO FUTURO, SIEMPRE CEROS
    //$ctaban_cargo=$renglon['CTABAN_CARGO']; //CUENTA DE CARGO DE LOS MOVIMIENTOS
    $cuenta_cargo = substr($ctaCargo, 6, 11); //CUENTA DE CARGO DE LOS MOVIMIENTOS
    $referencia_empresa = $anio . $mes . $dia . $secuencia;
    $referencia_empresa=str_pad($referencia_empresa, 10, "0");
    //ESTOS CAMPOS LOS OCUPA SCOTIA PARA LA RESPUESTA///////////////////////
    $codigo_status_registro_cab_cta = str_repeat("0", 3); //CONSTANTE, 3 CERO´S
    $filler_cab_cta = str_repeat(" ", 336); //CONSTANTE. 332 ESPACIOS
    ////////////////////////////////////////////////////////////////////////


    $encabezado_cuenta = $tipo_archivo_cab_cta .
            $tipo_registro_cab_cta .
            $moneda_cta_cargo .
            $uso_futuro .
            $cuenta_cargo .
            $referencia_empresa .
            $codigo_status_registro_cab_cta .
            $filler_cab_cta .
            $salto;

    $tipo_archivo_res_cta = "EE"; //CONSTANTE. ARCHIVO DE MOVIMIENTOS DE ENTRADA
    $tipo_registro_res_cta = "TB"; //CONSTANTE
    $tot_mov_altas_cta = str_pad($tot_mov_cta, 7, "0", STR_PAD_LEFT);
    $importe_mov_altas_cta = str_pad($imp_mov_cta * 100, 17, "0", STR_PAD_LEFT);
    $tot_mov_bajas_cta = str_repeat("0", 7);
    $importe_mov_bajas_cta = str_repeat("0", 17);

    //ESTOS CAMPOS LOS OCUPA SCOTIA PARA LA RESPUESTA///////////////////////
    $codigos_movimientos_resp_res_cta = str_repeat("0", 195); //CONSTANTE, 3 CERO´S
    $filler_res_cta = str_repeat(" ", 123); //CONSTANTE. 123 ESPACIOS
    ////////////////////////////////////////////////////////////////////////


    $resumen_cuenta = $tipo_archivo_res_cta .
            $tipo_registro_res_cta .
            $tot_mov_altas_cta .
            $importe_mov_altas_cta .
            $tot_mov_bajas_cta .
            $importe_mov_bajas_cta .
            $codigos_movimientos_resp_res_cta .
            $filler_res_cta .
            $salto;

    $encabezado_cuenta = limpiar($encabezado_cuenta, "ref");
    $detalle_cuenta = limpiar($detalle_cuenta, "ref");
    $resumen_cuenta = limpiar($resumen_cuenta, "ref");
    $lay_linea = $encabezado_cuenta . $detalle_cuenta . $detalle_mail_cuenta . $resumen_cuenta;

    return $lay_linea;
}
function HSBC_INTERNO($row, $nmovimiento)
{
  
    $espacio = chr(32);
    $salto = chr(13) . chr(10);
    $trans = array("À" => "A", "È" => "E", "Ì" => "I", "Ò" => "O", "Ù" => "U", "{" => " ", "}" => " ",
    "$" => " ", "|" => " ", "\"" => " ", "Á" => "A", "É" => "E", "Í" => "I", "Ó" => "O", "Ú" => "U",
    "º" => " ", "ª" => " ", "(" => "", ")" => "", "." => "", "%" => "", "/" => "", "-" => "",
    "," => "", ":" => "", ";" => "", "_" => "", ":" => "", "Ñ" => "N");
    extract($row);
    $numero_movimiento = $nmovimiento; // campo 1
    $cuenta_ordenante = substr($ctaCargo, 7, 10); // campo 2
    $cuenta_beneficiaria = substr($ctaAbono, 7, 10); // campo 3
    $monto = $importe; // campo 4
    $moneda = 1; // campo 5
    $referencia = $anio . $tipo . $orden . " UR" . $ur; //campo 6
    $referencia = substr($referencia, 0, 30);
    $nombre_beneficiario = substr(limpiar($benef, "nm_b"), 0, 40); //campo 7
    $nombre_beneficiario = limpiar($nombre_beneficiario, "ref");
    $comprobante_fiscal = "0"; //campo 8
    $rfc_beneficiario = ""; //campo 9
    $iva = ""; //campo 10
    $email = "hsbc.pagos@finanzasoaxaca.gob.mx"; //campo 11
    $tab = ',';
    $lay_linea = $numero_movimiento . $tab . $cuenta_ordenante . $tab . $cuenta_beneficiaria 
                . $tab . $monto . $tab . $moneda . $tab. $referencia . $tab . $nombre_beneficiario
                . $tab . $comprobante_fiscal . $tab . $rfc_beneficiario . $tab . $iva . $tab . $email;
    $lay_linea .= $salto;
    return $lay_linea;
}


function HSBC_EXTERNO($row)
{
    extract($row);
    $espacio = chr(32);
    $salto = chr(13) . chr(10);
    $trans = array("À" => "A", "È" => "E", "Ì" => "I", "Ò" => "O", "Ù" => "U", "{" => " ", "}" => " ",
    "$" => " ", "|" => " ", "\"" => " ", "Á" => "A", "É" => "E", "Í" => "I", "Ó" => "O", "Ú" => "U",
    "º" => " ", "ª" => " ", "(" => "", ")" => "", "." => "", "%" => "", "/" => "", "-" => "",
    "," => "", ":" => "", ";" => "", "_" => "", ":" => "", "Ñ" => "N");
    $cuenta_ordenante = substr($ctaCargo, 7, 10); //campo 1
    $rfc_beneficiario = "x"; //campo 2
    $cuenta_beneficiaria = $ctaAbono;
    $cuenta_beneficiaria=str_pad($cuenta_beneficiaria, 20, "0", STR_PAD_LEFT); //campo 3
    $banco_beneficiario = substr($ctaAbono, 0, 3); //campo 4
    $beneficiario = strtoupper(substr(strtr($benef, $trans), 0, 34));
    $titular = limpiar($beneficiario, "ref"); //campo 5
    $monto = $importe; //campo 6
    $referencia_numerica = str_pad($anio-1 . $tipo . $orden, 7, "0", STR_PAD_LEFT); //campo 7
    $referencia_numerica =substr($referencia_numerica, 0, 7);
    $referencia_numerica = limpiar($referencia_numerica, "ref");
    $concepto_pago = $anio . $tipo . $orden . " UR" . $ur; //campo 8
    $concepto_pago= substr($concepto_pago, 0, 40);
    $referencia_ordenante = $anio . $tipo . $orden . " UR" . $ur; //campo 9
    $referencia_ordenante = substr($referencia_ordenante, 0, 16);
    $tab = ',';
    $lay_linea = $cuenta_ordenante . $tab . $rfc_beneficiario. $tab . $cuenta_beneficiaria .$tab . $banco_beneficiario. $tab .$titular. $tab .$monto . $tab . $referencia_numerica . $tab .$concepto_pago. $tab .$referencia_ordenante;
    $lay_linea .= $salto;
    return $lay_linea;
}

function INTERACCION($row)
{

    extract($row); //crear variables del arreglo del row automaticamente

    $anioClc = $anio;
    $espacio = chr(32);
    $salto = chr(13) . chr(10);
    $cuenta = substr($ctaAbono, 0, 18);

    $importe = $importe;
    $importe = str_pad($importe, 15, "0", STR_PAD_LEFT);
    $trans = array("À" => "A", "È" => "E", "Ì" => "I", "Ò" => "O", "Ù" => "U", "{" => " ",
    "}" => " ", "$" => " ", "|" => " ", "\"" => " ", "Á" => "A", "É" => "E", "Í" => "I",
    "Ó" => "O", "Ú" => "U", "º" => " ", "ª" => " ", "(" => "", ")" => "", "." => "",
    "%" => "", "/" => "", "-" => "", "," => "", ":" => "", ";" => "", "_" => "", ":" => "", "Ñ" => "N", "?" => "");
    $string_mayus = strtoupper(substr(strtr($benef, $trans), 0, 55));
    $beneficiario = str_pad($string_mayus, 55, $espacio);
    $beneficiario = limpiar($beneficiario, "ref");
    /* al concepto se le concatena la orden al principio */
    $concepto = str_pad(strtoupper(substr($anioClc.$tipo.$orden." UR".$ur, 0, 50)), 50, " ");
    $concepto = limpiar($concepto, "ref");
   
    $lay_linea = $cuenta . $importe . $beneficiario . $concepto;

    // $lay_linea = limpiar($lay_linea, "ref");
    $lay_linea .= $salto;

    return $lay_linea;
}
function registro_totales($cont, $importe_total)
{
    $espacio = chr(32); //insertar un caracter de espacio
    $salto = chr(13) . chr(10); //avanza una linea

    $linea1 = "TRL";
    $linea1 .= rellena_con("0", 15, $cont, "izq"); //registro de pagos

    $import = $importe_total * 100;

    if (strlen($import) < 15) {
        $import = rellena_con("0", 15, $import, "izq");
    } else {
        echo "ERROR DE LONGITUD EN EL IMPORTE";
        die;
    }

    $linea1 .= $import;
    $linea1 .= rellena_con("0", 15);
    $linea1 .= rellena_con("0", 15, $cont, "izq");
    $linea1 .= rellena_con($espacio, 37);

    return $linea1;
}

function rellena_con($relleno, $cuantos, $cadena = "", $hacia = "der")
{
    $hacia = $hacia == "der" ? STR_PAD_RIGHT : STR_PAD_LEFT;
    $cadena = str_pad($cadena, $cuantos, $relleno, $hacia);
    return $cadena;
}

function AAMMDD($fecha)
{
    if ($fecha == 0) {
        return('');
    }
    $parts = explode('-', $fecha);
    if ($parts[0] != "0000") {
        $part_u = explode(" ", $parts[2]);
        $newDate = substr($parts[0], 2, 2) . $parts[1] . $part_u[0]; // AAMMDD
        return $newDate;
    } else {
        return "";
    }
}
// fin de la funcion del banco interacciones

function limpiar($cadena, $tipo = "def")
{
    $espacio = chr(32); //insertar un caracter de espacio
    $arreglo["tr_c"] = array(" ","!", '"', "#", "$", "%", "/", "(", ")", "=", "?", "¡", "¨", "*", "[", "]", ";",
    ":", "_", "'", "¿", "´", "+", "{", "}", ",", ">", "<", "°", "º", "|", "@", "¬", "\\", "~", "`", "^");
    $arreglo["rfc"] = array(" ","!", '"', "#", "$", "%", "/", "(", ")", "=", "?", "¡", "¨", "*", "[", "]",
    ";", ":", "_", "'", "¿", "´", "+", "{", "}", ",", ".", "-", ">", "<", "°", "º", "|", "@", "¬",
    "\\", "~", "`", "^", " ");
    $arreglo["cd_b"] = array(" ","!", '"', "#", "$", "%", "&", "=", "¡", "¨", "*", "[", "]", ";", "_",
    "¿", "´", ">", "<", "°", "º", "|", "@", "¬", "\\", "~", "`", "^");
    $arreglo["ref"] = array(" ","!", '"', "$", "%", "&", "/", "(", ")", "=", "?", "¡", "¨", "*",
    "[", "]", ";", ":", "_", "'", "¿", "´", "+", "{", "}", ",", ".", "-", "\u{2010}", "\u{2011}","\u{2013}",
    "\u{2014}", ">", "<", "°", "º", "|", "@", "¬", "\\", "~", "`", "^");
    $arreglo["hsbc"] = array(" ","!", '"', "$", "%", "&", "/", "(", ")", "=", "?", "¡", "¨", "*",
    "[", "]", ";", ":", "_", "'", "¿", "´", "+", "{", "}", ",", ".", "-", ">", "<", "°", "º",
    "|", "@", "¬", "\\", "~", "`", "^");
    $arreglo["nm_b"] = array(" ","!", '"', "#", "$", "%", "/", "(", ")", "=", "?", "¡", "¨", "*",
    "[", "]", ";", ":", "_", "¿", "´", "+", "{", "}", ",", ".", "-", ">", "<", "°", "º", "|",
    "@", "¬", "\\", "~", "`", "^");
    $arreglo["dr_b"] = array(" ","!", '"', "#", "$", "%", "(", ")", "=", "?", "¡", "¨", "*", "[",
    "]", ";", ":", "_", "¿", "´", "+", "{", "}", ".", "-", ">", "<", "°", "º", "|", "¬", "\\",
    "~", "`", "^");
    $arreglo["e_b"] = array(" ","!", '"', "$", "%", "&", "/", "(", ")", "=", "?", "¡", "¨", "*",
    "[", "]", ";", ":", "'", "¿", "´", "+", "{", "}", ",", ">", "<", "°", "º", "|", "¬", "\\",
    "~", "`", "^");
    $arreglo["det"] = array(" ","!", "=", "¡", "¨", "[", "]", ":", "¿", "´", "{", "}", ">", "<",
    "°", "º", "|", "¬", "\\", "~", "`", "^");
    $arreglo["def"] = array("");

    $arreglo["acento"] = array("Á", "É", "Í", "Ó", "Ú", "Ñ", "á", "é", "í", "ó", "ú", "ñ", "Ü",
    "ü", "È", "À", "Ì", "Ò", "Ù", "à", "è", "ì", "ò", "ù");
    $arreglo["sacento"] = array("A", "E", "I", "O", "U", "N", "a", "e", "i", "o", "u", "n", "U",
    "u", "E", "A", "I", "O", "U", "a", "e", "i", "o", "u");

    $arreglo["esp"] = array(" ","\\t","\\n","\\r","\\0","\\x0B");

    //MINISTRACION Nª 7 DE RECURSOS CORRESPONDIENTE AL PROGRAMA AFASPE 2016

    $arreglo["especial"] = array("ª");
    $arreglo["especial_remp"] = array("a");

    //quitar acentos
    $cadena = str_replace(
        $arreglo["acento"],
        $arreglo["sacento"],
        $cadena
    );

    $cadena = str_replace(
        $arreglo["$tipo"],
        $espacio,
        $cadena
    );

    $cadena = str_replace(
        $arreglo["especial"],
        $arreglo["especial_remp"],
        $cadena
    );

    return $cadena;
}
function AZTECA($row, $secuencia)
{
    extract($row);
    $espacio = chr(32);
    $salto = chr(13) . chr(10);
    //obtener valores de los bancos
    $clabeCargo = substr($ctaCargo, 0, 3); //clave del banco a donde se cargara el importe
    $clabeAbono = substr($ctaAbono, 0, 3); //clave delabeAbono a donde se abonara el importe
    
    // Secuencia
    $linea1 = rellena_con(0, 7, $secuencia, 'LEFT');

    // Fecha
    $date=date('Y-m-d');
    $date=explode("-", $date);
    $date=implode("", $date);
    $linea1.=$date;
    
    // Servicio
    $linea1.='02';
    
    // Tipo cuenta
    $linea1.='01';
    
    // Cuenta cargo
    $linea1.=rellena_con($espacio,20,$cbs_cuenta);
    
    // Titular cuenta
    $linea1.=rellena_con($espacio,40, 'GOBIERNO DEL ESTADO DE OAXACA');
    
    // RFC titular
    $linea1.=rellena_con($espacio,18, 'GEO621201KIA');
    
    // Leyenda cargo
    $linea1.=rellena_con($espacio,30, limpiar(substr($concepto, 0,30),'tr_c'));
    
    // Importe
    $import=$importe * 100;
    if (strlen($import) < 15) {
        $import = rellena_con("0", 15, $import, "izq");
    } else {
        echo "ERROR DE LONGITUD EN EL IMPORTE";
        die();
    }
    $linea1.=$import;
    
    // Moneda
    $linea1.='MXP';
    
    // Banco receptor
    $linea1.=$clabeAbono;
    
    // Tipo cuenta beneficiario
    $linea1.=40;
    
    // Numero de cuenta beneficiario
    $linea1.=rellena_con($espacio, 20, $ctaAbono);
    
    // Nombre del beneficiario
    $linea1.=rellena_con($espacio, 40, limpiar(substr($benef, 0,40),'tr_c'));
    
    // Leyenda del abono
    if($tipoLote==2){
        $linea1.=rellena_con($espacio, 30, $anio.$tipo.$orden.$documento." UR".$ur);
    }else{
        $linea1.=rellena_con($espacio, 30, $anio.$tipo.$orden." UR".$ur);
    }
    
    // RFC del beneficiario
    $linea1.=rellena_con($espacio, 18, (isset($rfc)?$rfc:''));
    
    // IVA
    $linea1.='00';
    
    // Referencia de cobranza
    $linea1.=rellena_con($espacio, 40, '');
    
    // Clave
    $linea1.=rellena_con($espacio, 12, '');
    
    // Tipo pago
    $linea1.=$clabeAbono=='127'?'03':'01';
    
    // Filler
    $linea1.=rellena_con($espacio, 40, '');

    // Salto de linea
    $linea1.=$salto;
    return $linea1;
}