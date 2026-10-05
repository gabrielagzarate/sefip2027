<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
    
    function MesAlias( $mes ){
        switch( $mes ){
            case 0:    return '';      break;
            case 1:    return 'ene';   break;
            case 2:    return 'feb';   break;
            case 3:    return 'mar';   break;
            case 4:    return 'abr';   break;
            case 5:    return 'may';   break;
            case 6:    return 'jun';   break;
            case 7:    return 'jul';   break;
            case 8:    return 'ago';   break;
            case 9:    return 'sep';   break;
            case 10:   return 'oct';   break;
            case 11:   return 'nov';   break;
            case 12:   return 'dic';   break;
            case 13:   return '';      break;
            default:   return 'kym';   break;
        }
    }
    