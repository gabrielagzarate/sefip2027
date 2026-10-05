<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//redondear los numeros de punto flotante a decimales especificos
function redondear($numero, $decimales) {
    $factor = pow(10, $decimales);
    return (round((float)$numero * $factor) / $factor);
    
}