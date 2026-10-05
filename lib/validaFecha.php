<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
    
    ENTRADA
    
    dd/mm/aaaa
    dd/mm/aa
    aaaa/mm/dd ** pendiente
    
    SALIDAS
    
    latino dd/mm/aaaa
    mysql aaaa-mm-dd
    
    
 * Update:
 * Israel: Ene 26, 2018: AJUSTE DE LOS MENSAJES
 */

require_once dirname(__FILE__).'/fecha.php';

function validaFecha($fechaEntrada = NULL) {
    
    $fechaSalida = new stdClass();
    $fechaSalida->valido = TRUE;
    $fechaSalida->ayuda  = "
        Recibe un stdClass<br>
            parametros de recepcion
            <ul>
                <li>fecha  cadena obligatorio
                <li>aniomenor  numerico mayor a 2019 y menor a 2025[opcional] </li>
                <li>edoMesContable [opcional]</li>
                <li>validaMesContable (si no especifica predeterminado FALSE)</li>
                <li>gpo_id </li>
                <li>ur_id </li>
                <li>fechaMaxima , fecha maxima  aceptada [opcional]</li>
                <li>fechaMinima , fecha minima  aceptada [opcional]</li>
                <li>anio 		, año aceptado en la fecha [opcional]</li>
            </ul><br>
        Esta funcion valida fechas en los formatos dd/mm/aa, dd/mm/aaaa , dd-mm-aa, dd-mm-aaaa, ddmmaa, ddmmaaa , <br>
        valida que el dia no sobrepase los 31, el mes los 12 <br>
        el año no sea mayor al 2025 y menor de manera predeterminada 2019, o si se envia el parametro aniomenor ahi el año minimo de aceptación<br>
        devuelve lo siguiente
            <ul>
                <li>fecha  		formato mexico dd/mm/aa (si valido = TRUE) </li>
                <li>fechaMysql  formato MySql aaaa-mm-dd(si valido = TRUE)</li>
                <li>edoMesContable  </li>
                <li>valido  TRUE o FALSE si la fecha no cumple las validaciones </li>
                <li>fechaMaxima  TRUE  si la fecha es menor  a la maxima  FALSE si la fecha supera esta fecha</li>
                <li>fechaMinima  TRUE  si la fecha es mayor a la minima  FALSE si la fecha es menor</li>
                <li><anio  TRUE /FALSE si parametr anio lleva algo y cumple la fecha con ese anio devolvera TRUE, si no FALSE/li>
                <li>ayuda  imprime esta ayuda </li>
            </ul>";
    
    if (!$fechaEntrada || !isset($fechaEntrada->fecha) || $fechaEntrada->fecha ===null) {
        $fechaSalida->valido  = FALSE;
        $fechaSalida->mensaje = 'VALIDAFECHA: ERROR EN LOS PARAMETROS';
        return $fechaSalida;
    }
    $dia = '';
    $mes = '';
    $anio = '';
    $n = FALSE;
    $fechaArray = '';

    $f = $fechaEntrada->fecha;
    $anioniomenor = isset($fechaEntrada->aniomenor) ? $fechaEntrada->aniomenor:date('Y')-2;
    if($anioniomenor < 2019)
        $anioniomenor = 2019;
    if($anioniomenor > APP_EJERCICIO)
        $anioniomenor = APP_EJERCICIO;

    if (strpos($f,'/') !== FALSE) {
        $fechaArray = explode('/',$f);
        $n = TRUE;
    } elseif (strpos($f,'-') !== FALSE) {
        $fechaArray = explode('-',$f);
        $n = TRUE;
    } else {
        if (!is_numeric($f)) {
            $fechaSalida->valido = FALSE;
            $fechaSalida->mensaje = 'VALIDAFECHA: NO SE PUEDE DETERMINAR EL FORMATO DE LA FECHA';
            return $fechaSalida; 
        }
        
        switch (strlen($f)) {
            case 6:
                $dia = substr($f, 0, 2);
                $mes = substr($f, 2, 2);
                $anio = '20'.substr($f, 4, 2);
                break;
            case 8:
                $dia = substr($f, 0, 2);
                $mes = substr($f, 2, 2);
                $anio = substr($f, 4, 4);
                break;
            case 4:
                $dia = substr($f,0,2);
                $mes = substr($f,2,2);
                $anio = ''.APP_EJERCICIO;
                break;
            default:
                $fechaSalida->valido = FALSE;
                $fechaSalida->mensaje = 'VALIDAFECHA: NO SE PUEDE DETERMINAR EL FORMATO DE LA FECHA';
                return $fechaSalida;
                break;
        }
    }

    $bolIncluyeHora = false;

    if ($n) {
        if (count($fechaArray) != 3) {
            $fechaSalida->valido  = FALSE;
            $fechaSalida->mensaje = 'VALIDAFECHA: PARAMETROS DE FECHA INCORRECTOS';
            return $fechaSalida;
        } else {
            if (strpos($f,'-') !== FALSE) {
                $dia = trim( $fechaArray[2] );
                $mes = $fechaArray[1];
                $anio = strlen($fechaArray[0]) == 2 ? '20'.$fechaArray[0] : $fechaArray[0];

                // Agregamos validación de hora, por requerimiento de programar evento al minuto en ControladorMesContable
                if (strpos($dia,' ') !== FALSE && strpos($dia,':') !== FALSE) {
                    $fecha_hora = explode(' ',$dia);
                    if (count($fecha_hora) != 2) {
                        $fechaSalida->valido = FALSE;
                        $fechaSalida->mensaje = 'VALIDAFECHA_HORA: NO SE PUEDE SEPARAR LA HORA DE LA FECHA';
                        return $fechaSalida; 
                    } else {
                        $dia = $fecha_hora[0];
                        $hora_minuto = explode(':',$fecha_hora[1]);
                        if (count($hora_minuto) != 2) {
                            $fechaSalida->valido = FALSE;
                            $fechaSalida->mensaje = 'VALIDAFECHA_HORA: NO SE PUEDE DETERMINAR EL FORMATO DE LA HORA';
                            return $fechaSalida; 
                        } else {
                            $hora = $hora_minuto[0];
                            $minuto = $hora_minuto[1];
                            $bolIncluyeHora = true;
                        }
                    }
                }                
                
            } else {
                $dia = $fechaArray[0];
                $mes = $fechaArray[1];
                $anio = strlen($fechaArray[2]) == 2 ? '20'.$fechaArray[2] : $fechaArray[2];

                // Agregamos validación de hora, por requerimiento de programar evento al minuto en ControladorMesContable
                if (strpos($anio,' ') !== FALSE && strpos($anio,':') !== FALSE) {
                    $fecha_hora = explode(' ',$anio);
                    if (count($fecha_hora) != 2) {
                        $fechaSalida->valido = FALSE;
                        $fechaSalida->mensaje = 'VALIDAFECHA_HORA: NO SE PUEDE SEPARAR LA HORA DE LA FECHA';
                        return $fechaSalida; 
                    } else {
                        $anio = $fecha_hora[0];
                        $hora_minuto = explode(':',$fecha_hora[1]);
                        if (count($hora_minuto) != 2) {
                            $fechaSalida->valido = FALSE;
                            $fechaSalida->mensaje = 'VALIDAFECHA_HORA: NO SE PUEDE DETERMINAR EL FORMATO DE LA HORA';
                            return $fechaSalida; 
                        } else {
                            $hora = $hora_minuto[0];
                            $minuto = $hora_minuto[1];
                            $bolIncluyeHora = true;
                        }
                    }
                }                

            }
        }
    }
    
    $validarFecha = fecha($dia, $mes, $anio);
    if (!$validarFecha->valido) {
        $fechaSalida->valido = FALSE;
        $fechaSalida->mensaje = 'VALIDAFECHA: ' . $validarFecha->mensaje;
        return $fechaSalida;
    }

    // Agregamos validación de hora, por requerimiento de programar evento al minuto en ControladorMesContable
    if ( $bolIncluyeHora ) {
        $validarHora = hora($hora, $minuto);
        if (!$validarHora->valido) {
            $fechaSalida->valido = FALSE;
            $fechaSalida->mensaje = 'VALIDAFECHA_HORA: ' . $validarHora->mensaje;
            return $fechaSalida;
        }        
    }

    if (isset($fechaEntrada->gpo_id) && isset($fechaEntrada->ur_id)) {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql( " 
            SELECT ems_id
            FROM cg_MesContable
            WHERE gpo_id = '$fechaEntrada->gpo_id' AND ur_id = '$fechaEntrada->ur_id'
                AND msc_id = $mes 
        ");
        
        $conexion->consultar();
        $g = $conexion->extraer();
        if(isset($g['ems_id'])){
            $fechaSalida->edoMesContable = $g['ems_id'];
        }else{
            $fechaSalida->edoMesContable = '';
        }
        $conexion->desconectar();
    } else {
        $fechaSalida->edoMesContable = '';
    }
    unset($conexion);
    
    /*  --------------------- VALIDA FECHA MAXIMA ------------------------------ */
    if(isset($fechaEntrada->fechaMaxima) && $fechaEntrada->fechaMaxima != ''){
        $fechaSalida->fechaMaxima = TRUE;
        
        if (strpos($fechaEntrada->fechaMaxima, '/') !== FALSE) {
            $fm = explode('/', $fechaEntrada->fechaMaxima);
        } elseif (strpos($fechaEntrada->fechaMaxima, '-') !== FALSE){
            $fm = explode('-', $fechaEntrada->fechaMaxima);
        }
        
        if (count($fm) != 3) {
            $fechaSalida->fechaMaxima  = FALSE;
            $fechaSalida->mensaje = 'VALIDAFECHA: LA FECHA MÁXIMA NO ES VÁLIDA';
            return $fechaSalida;
        }
        
        $validaFechaMaxima = fecha($fm[0], $fm[1], $fm[2]);
        
        if (!$validaFechaMaxima->valido) {
            $fechaSalida->fechaMaxima  = FALSE;
            $fechaSalida->mensaje = 'VALIDAFECHA: ' . $validaFechaMaxima->mensaje;
            return $fechaSalida;
        }
        
        $fo = new DateTime($anio . '-' . $mes . '-' . $dia);
        $fm = new DateTime($fm[2] . '-' . $fm[1] . '-' . $fm[0]);
        
        if ($fo > $fm) {
            $fechaSalida->fechaMaxima = FALSE;
            $fechaSalida->mensaje = 'VALIDAFECHA: LA FECHA EXCEDE EL RANGO PERMITIDO [' . $fechaEntrada->fechaMaxima . ']';
            return $fechaSalida;
        }
        
    }
    
    $fm = '';
    /*  --------------------- VALIDA FECHA MINIMA ------------------------------ */
    if (isset($fechaEntrada->fechaMinima) && $fechaEntrada->fechaMinima != '') {
        $fechaSalida->fechaMinima = TRUE;
        
        if(strpos($fechaEntrada->fechaMinima, '/') !== FALSE) {
            $fm = explode('/', $fechaEntrada->fechaMinima);
        } elseif (strpos($fechaEntrada->fechaMinima, '-') !== FALSE) {
            $fm = explode('-',$fechaEntrada->fechaMinima);
        }
        
        if (count($fm) != 3) {
            $fechaSalida->fechaMaxima = FALSE;
            $fechaSalida->mensaje = 'VALIDAFECHA: LA FECHA MÍNIMA NO ES VÁLIDA';
            return $fechaSalida;
        }
        
        $validaFechaMinima = fecha($fm[0], $fm[1], $fm[2]);
        
        if( !$validaFechaMinima->valido ){
            $fechaSalida->fechaMinima = FALSE;
            $fechaSalida->mensaje = 'VALIDAFECHA: ' . $validaFechaMinima->mensaje;
            return $fechaSalida;
        }
        $fo = new DateTime($anio . '-' . $mes . '-' . $dia);
        $fm = new DateTime($fm[2] . '-' . $fm[1] . '-' . $fm[0]);
        
        if ($fo < $fm) {
            $fechaSalida->fechaMinima = FALSE;
            $fechaSalida->mensaje = 'VALIDAFECHA: LA FECHA ES MENOR A LA FECHA MÍNIMA [' . $fechaEntrada->fechaMinima . ']';
            return $fechaSalida;
        } else {
            $fechaSalida->fechaMinima = TRUE;
        }
    }
    
    /*  --------------------- VALIDA AÑO --------------------------------------- */
    if (isset($fechaEntrada->anio) && $fechaEntrada->anio != '') {
        if (!is_numeric($fechaEntrada->anio)) {
            $fechaSalida->anio  = FALSE;
            $fechaSalida->mensaje = 'VALIDAFECHA: EL AÑO NO ES CORRECTO';
        } else {
            if ($anio != $fechaEntrada->anio) {
                $fechaSalida->anio = FALSE;
                $fechaSalida->mensaje = 'VALIDAFECHA: EL AÑO NO ES VÁLIDO';
            } else {
                $fechaSalida->anio = TRUE;
            }
        }
    }
    
    // Agregamos validación de hora, por requerimiento de programar evento al minuto en ControladorMesContable
    if ( $bolIncluyeHora ) {
        $fechaSalida->mes = $mes;
        $fechaSalida->fecha = $dia . '/' . $mes . '/' . $anio . ' ' . $hora . ':' . $minuto . ':00';
        $fechaSalida->fechaMysql = $anio . '-' . $mes . '-' . $dia . ' ' . $hora . ':' . $minuto . ':00';
        $fechaSalida->mensaje = 'VALIDAFECHA: OK';
    } else {
        $fechaSalida->mes = $mes;
        $fechaSalida->fecha = $dia . '/' . $mes . '/' . $anio;
        $fechaSalida->fechaMysql = $anio . '-' . $mes . '-' . $dia;
        $fechaSalida->mensaje = 'VALIDAFECHA: OK';
    }
    
    return $fechaSalida;
}
