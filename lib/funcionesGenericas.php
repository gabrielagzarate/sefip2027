<?php
// Marcos: 12/oct/2016
// La idea es poner en éste archivo funciones que NO tengan que ver con tablas, bases de datos, cosas muy puntuales de
// una pantalla u otra, sino funciones que complementen o extiendan el lenguaje PHP
// Así, si necesito una función de éstas, no tengo que llamar a donde vienen muchas cosas más

// Emulamos el comportamiento de mysql_real_escape_string ... porque es muuuuuuuy leeeeentooooo
// ------------------------------------------------------------------------------------------------------------------------
function mysql_escape_mimic($inp)
{
    if (is_array($inp)) {
        return array_map(__METHOD__, $inp);
    }

    if (!empty($inp) && is_string($inp)) {
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
    }

    return $inp;
}

// Para la importación de información a excel, nos aseguramos que se cargue en fomrato año-mes-dia
// evidentemente la fecha debe venir en formato dia-mes-año
// ------------------------------------------------------------------------------------------------------------------------

function date_to_mysqldate($fecha)
{
    if (!strcmp($fecha, "now()")) {
        return $fecha;
    } elseif ($fecha != "") {
        // Por si escriben guiones en lugar de diagonales, reemplazamos parejo
        $fecha = str_replace("-", "/", $fecha);
        $trozos = explode("/", $fecha, 3);
        if (count($trozos) == 3) {
            if (strlen($trozos[0]) == 2) {
                return $trozos[2] . "/" . $trozos[1] . "/" . $trozos[0];
            } else { /*si ya viene en formato año, mes, dia*/
                return $trozos[0] . "/" . $trozos[1] . "/" . $trozos[2];
            }
        } else {
            return "NULL";
        }
    }
    return "NULL";
}

// Para regresar el nombre de un mes
function MesNombre($mes)
{
    switch ($mes) {
        case 0:
            return 'APERTURA';
            break;
        case 1:
            return 'ENERO';
            break;
        case 2:
            return 'FEBRERO';
            break;
        case 3:
            return 'MARZO';
            break;
        case 4:
            return 'ABRIL';
            break;
        case 5:
            return 'MAYO';
            break;
        case 6:
            return 'JUNIO';
            break;
        case 7:
            return 'JULIO';
            break;
        case 8:
            return 'AGOSTO';
            break;
        case 9:
            return 'SEPTIEMBRE';
            break;
        case 10:
            return 'OCTUBRE';
            break;
        case 11:
            return 'NOVIEMBRE';
            break;
        case 12:
            return 'DICIEMBRE';
            break;
        case 13:
            return 'CIERRE';
            break;
        default:
            return '';
            break;
    }
}

// ===================================================================================================================
// Fin del código
// ===================================================================================================================
