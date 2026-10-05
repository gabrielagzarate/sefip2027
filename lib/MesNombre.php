<?php
    
    /* 
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */
    
    function MesNombre( $mes ){
        switch( $mes ){
            case 0:    return 'APERTURA';   break;
            case 1:    return 'ENERO';      break;
            case 2:    return 'FEBRERO';    break;
            case 3:    return 'MARZO';      break;
            case 4:    return 'ABRIL';      break;
            case 5:    return 'MAYO';       break;
            case 6:    return 'JUNIO';      break;
            case 7:    return 'JULIO';      break;
            case 8:    return 'AGOSTO';     break;
            case 9:    return 'SEPTIEMBRE'; break;
            case 10:   return 'OCTUBRE';    break;
            case 11:   return 'NOVIEMBRE';  break;
            case 12:   return 'DICIEMBRE';  break;
            case 13:   return 'CIERRE'; break;
            default:   return '';   break;
        }
    }