<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function formatoTexto( $cadena, $formato ){
    switch( $formato ){
        case "telefono":
            return "(".substr( $cadena, 0, 3 ).")".substr( $cadena, 3,3 )."-".substr( $cadena, 6 ); 
            break;
        default :
            return $cadena;
            break;
    }
}