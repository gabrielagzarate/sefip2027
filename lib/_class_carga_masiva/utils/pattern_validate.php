<?php

/**
 * @filesource : pattern_validate.php
 * @author : @rvargas
 * @copyright (c) 2018, rvargas
 *
 *  */

// Testing
// print_r(validateColumn("true,decimal,null,1,15", "-1500.40" ));

function validateColumn($array, $value)
{
    $function = explode(',', $array);
    $required = $function[0];
    $type_data = $function[1];
    $length = $function[2];
    $min_length = $function[3];
    $max_length = $function[4];
    $optional = false;
    $result = false;
    $validate = false;

    if (isset($function[5]) && $function[5] == 'optional') {
        $optional = true;
    }
    if ($required) {
        $result = is_required($value);
    } else {
        $result = true;
    }

    // PHP 8.1+: evita "Passing null to parameter ... trim()/str_replace()/preg_match()" cuando
    // la celda del Excel viene vacía (getValue() devuelve null). No altera la validación.
    $value = (string) $value;

    if ($result) {

        if ($optional && strlen(trim($value)) == 0) {
            $result = true;
        } else {
            // Clean date
            if ($type_data == 'date') {
                $value = str_replace("'", '', $value);
            }
            if ($length != 'null') {
                $validate = validLen(strlen(trim($value)), $length);
            } elseif ($min_length != 'null') {
                $validate = validMinMax(strlen(trim($value)), $min_length, $max_length);
            } else {
                $validate = true;
            }

            if ($validate) {
                switch ($type_data) {
                    case 'int':
                        if ($value) {
                            $result = is_number($value);
                        }
                        break;
                    case 'char':
                        $result = is_character($value);
                        break;
                    case 'string':
                        $result = is_strings($value);
                        break;
                    case 'decimal':
                        $result = is_decimal($value);
                        break;
                    case 'date':
                        $result = is_date($value);
                        break;
                    case 'rfc':
                        $result = is_rfc($value);
                        break;
                    default:
                        $result = false;
                        break;
                }
            } else {
                $result = false;
            }
        }
    }
    return $result;
}

function is_required($element)
{
    if ($element != '' || $element != ' ' || $element != null || strlen($element) > 0) {
        return true;
    } else {
        return false;
    }
}

function validLen($element_length, $length)
{
    if ($element_length != $length) {
        return false;
    }
    return true;
}

function validMinMax($element_length, $min, $max)
{
    if ($element_length < $min) {
        return false;
    }
    if ($max != 'null') {
        if ($element_length > $max) {
            return false;
        }
    }
    return true;
}

function is_number($element)
{
    return !preg_match('/[^0-9]/', $element);
}

function is_character($element)
{
    $patron = '/^[a-zA-Z, ]*$/';
    return preg_match($patron, $element);
}

function is_strings($element)
{
    if (!preg_match('/^[\w áéíóúñüÁÉÍÓÚÑÜ|\-ª°¯\/.:¦\'",#={}´;?()!¡¨¿+*$%&"\']+$/', $element)) {
        return false;
    } else {
        return true;
    }
}
function is_decimal($element)
{

    if (preg_match('#^-?\d+(?:\.\d{1,2})?$#', $element, $match)) {
        return true;
    } else {
        return false;
    }

    // $price = strlen(substr(strrchr($element, "."), 1));
    // if($price > 1){
    //   if (preg_match("/^(\\\$?)((([1-9])([0-9]{0,2})((,?[0-9]{3}){0,2}))|([0-9]?))((\.[0-9]{2})?)$/", $element)) {
    //     return true;
    //   } else return false;
    // } else {
    //   if (preg_match("/^(\\\$?)((([1-9])([0-9]{0,2})((,?[0-9]{3}){0,2}))|([0-9]?))((\.[0-9]{1})?)$/", $element)) {
    //     return true;
    //   } else return false;
    // }

}
function is_rfc($element)
{
    // $element = str_replace("-", "", $element);
    if (preg_match('/[A-Z,Ñ,&]{3,4}[0-9]{2}[0-1][0-9][0-3][0-9][A-Z,0-9]?[A-Z,0-9]?[0-9,A-Z]?/', $element)) {
        return true;
    } else {
        return false;
    }

    $cuartoelement = substr($element, 3, 1);
    //RFC without homoclave
    if (strlen($element) == 10) {
        $chars = substr($element, 0, 4);
        $numbers = substr($element, 4, 6);
        if (ctype_alpha($chars) && ctype_digit($numbers)) {
            return true;
        }
        return false;
    }
    // Only homoclave
    elseif (strlen($element) == 3) {
        $homoclave = $element;
        if (ctype_alnum($homoclave)) {
            return true;
        }
        return false;
    }
    //RFC Moral.
    elseif (ctype_digit($cuartoelement) && strlen($element) == 12) {
        $chars = substr($element, 0, 3);
        $numbers = substr($element, 3, 6);
        $homoclave = substr($element, 9, 3);
        if (ctype_alpha($chars) && ctype_digit($numbers) && ctype_alnum($homoclave)) {
            return true;
        }
        return false;
        //RFC Física.
    } elseif (ctype_alpha($cuartoelement) && strlen($element) == 13) {
        $chars = substr($element, 0, 4);
        $numbers = substr($element, 4, 6);
        $homoclave = substr($element, 10, 3);
        if (ctype_alpha($chars) && ctype_digit($numbers) && ctype_alnum($homoclave)) {
            return true;
        }
        return false;
    } else {
        return false;
    }
}

function is_date($dt)
{
    $dtArr = explode('/', $dt);

    if (strpos($dt, '-') !== false) {
        $dtArr = explode('-', $dt); 
    } 
    if (count($dtArr) < 3) {
        return false;
    }
    $year  = $dtArr[0];
    $month = $dtArr[1];
    $day = $dtArr[2];

    if (is_number($year) && strlen($year) == 4) {
        if (is_number($month) && strlen($month) == 2 && $month <= 12) {
            if (is_number($day) && strlen($day) == 2 && $day <= 31) {
                return true;
            } else {
                return false;
            }

        } else {
            return false;
        }
    } else {
        return false;
    }

}
