<?php

//compara las cadenas de las cuentas contables
function CompararCadenas($cadena1, $cadena2) {
    $longitud = (strlen($cadena1) < strlen($cadena2) ? strlen($cadena1) : strlen($cadena2)) + 1;

    for ($i = 0; $i < $longitud ; $i++) {
        if (isset($cadena1[$i]) && isset($cadena2[$i])) {
            if ($cadena1[$i] < $cadena2[$i]) {
                return -1;
            } elseif ($cadena1[$i] > $cadena2[$i]) {
                return 1;
            }
        } elseif (!isset($cadena1[$i]) && isset($cadena2[$i])) {
            return 1;
        } elseif (isset($cadena1[$i]) && !isset($cadena2[$i])) {
            return -1;
        }
    }
    return 0;
}