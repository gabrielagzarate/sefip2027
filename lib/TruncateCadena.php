<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

    //devuelve la cadena para truncarla un espacio antes de la palabra final.
    function TruncateCadena( $cadena, $distancia ){
        //$cadena = utf8_decode( $cadena );
        if( strlen( $cadena ) > $distancia )
            for( $i = 0; ( $i * $distancia ) < strlen( $cadena ); $i++ ){
                $subcadena = '';
                $len = strrpos( substr( $cadena, $i * $distancia, $distancia ), ' ' );
                for( $j = 1; $j < $distancia - $len; $j++ ){
                    if( substr( $cadena, ( $i + 1 ) * $distancia, $distancia ) != FALSE )
                        $subcadena .= ' ';
                }
                $cadena = substr( $cadena, 0, ( $i * $distancia ) + $len ).$subcadena.substr( $cadena, ( $i * $distancia ) + $len );
                
            }
        return $cadena;
    }
    
?>