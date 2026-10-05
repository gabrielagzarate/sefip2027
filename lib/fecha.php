<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * 
 * Update:
 * Israel: Ene 26, 2018: AJUSTE DE LOS MENSAJES
*/

function fecha($d = null, $m = null, $a = null)
{
    $obj = new stdClass();

    if ($d == null ||  $m == null || $a == null) {
        $obj->valido = FALSE;
        $obj->mensaje =  'FECHA: FALTAN PARAMETROS';
        return $obj;
    }

    if (!is_numeric($d) || $d > 31) {
        $obj->valido = FALSE;
        $obj->mensaje = 'FECHA: EL DÍA NO ES CORRECTO';
        return $obj;
    }

    if (!is_numeric($m) || $m > 12) {
        $obj->valido  = FALSE;
        $obj->mensaje = 'FECHA: EL MES NO ES CORRECTO';
        return $obj;
    } else {
        switch ($m) {
            case 1:
                if ($m > 31) {
                    $obj->valido  = FALSE;
                    $obj->mensaje = 'FECHA: ENERO SOLO TIENE 31 DÍAS';
                    return $obj;
                }
                break;
            case 2:
                $max = 0;
                if (checkdate(2, 29, $a))
                    $max = 29;
                else
                    $max = 28;

                if ($m > $max) {
                    $obj->valido  = FALSE;
                    $obj->mensaje = 'FECHA: FEBRERO SOLO TIENE ' . $max . ' DÍAS';
                    return $obj;
                }
                break;
            case 3:
                if ($m > 31) {
                    $obj->valido  = FALSE;
                    $obj->mensaje = 'FECHA: MARZO SOLO TIENE 31 DÍAs';
                    return $obj;
                }
                break;
            case 4:
                if ($m > 30) {
                    $obj->valido  = FALSE;
                    $obj->mensaje = 'FECHA: ABRIL SOLO TIENE 30 DÍAS';
                    return $obj;
                }
                break;
            case 5:
                if ($m > 31) {
                    $obj->valido  = FALSE;
                    $obj->mensaje = 'FECHA: MAYO SOLO TIENE 31 DÍAS';
                    return $obj;
                }
                break;
            case 6:
                if ($m > 30) {
                    $obj->valido  = FALSE;
                    $obj->mensaje = 'FECHA: JUNIO SOLO TIENE 30 DÍAS';
                    return $obj;
                }
                break;
            case 7:
                if ($m > 31) {
                    $obj->valido  = FALSE;
                    $obj->mensaje = 'FECHA: JULIO SOLO TIENE 31 DÍAS';
                    return $obj;
                }
                break;
            case 8:
                if ($m > 31) {
                    $obj->valido  = FALSE;
                    $obj->mensaje = 'FECHA: AGOSTO SOLO TIENE 31 DÍAS';
                    return $obj;
                }
                break;
            case 9:
                if ($m > 30) {
                    $obj->valido  = FALSE;
                    $obj->mensaje = 'FECHA: SEPTIEMBRE SOLO TIENE 30 DÍAS';
                    return $obj;
                }
                break;
            case 10:
                if ($m > 31) {
                    $obj->valido  = FALSE;
                    $obj->mensaje = 'FECHA: OCTUBRE SOLO TIENE 31 DÍAS';
                    return $obj;
                }
                break;
            case 11:
                if ($m > 30) {
                    $obj->valido  = FALSE;
                    $obj->mensaje = 'FECHA: NOVIEMBRE SOLO TIENE 30 DÍAS';
                    return $obj;
                }
                break;
            case 12:
                if ($m > 31) {
                    $obj->valido  = FALSE;
                    $obj->mensaje = 'FECHA: DICIEMBRE SOLO TIENE 31 DÍAS';
                    return $obj;
                }
                break;
            default:
                $obj->valido  = FALSE;
                $obj->mensaje = 'FECHA: ERROR EN EL MES';
                return $obj;
                break;
        }
    }

    if (strlen($a) == 2)
        $a = '20' . $a;

    if (!is_numeric($a) || $a > APP_EJERCICIO) {
        $obj->valido = FALSE;
        $obj->mensaje = 'FECHA: EL AÑO ' . $a . ' NO ES CORRECTO';
        return $obj;
    }

    $obj->valido = TRUE;
    $obj->mensaje = 'FECHA: OK';

    return $obj;
}




// 2-feb-2024 - para el requerimiento de programar evento a la hora en ControladorMesContable
function hora($h = null, $m = null)
{
    $obj = new stdClass();

    if ($h == null ||  $m == null) {
        $obj->valido = FALSE;
        $obj->mensaje =  'FECHA_HORA: FALTAN PARAMETROS';
        return $obj;
    }

    if (!is_numeric($h) || ( $h < 0 || $h > 23) ) {
        $obj->valido = FALSE;
        $obj->mensaje = 'FECHA_HORA: LA HORA NO ES CORRECTA';
        return $obj;
    }

    if (!is_numeric($m) || ( $m < 0 || $m > 59 ) ) {
        $obj->valido  = FALSE;
        $obj->mensaje = 'FECHA_HORA: EL MINUTO NO ES CORRECTO';
        return $obj;
    }

    $obj->valido = TRUE;
    $obj->mensaje = 'HORA: OK';

    return $obj;
}
