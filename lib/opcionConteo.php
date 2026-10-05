<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function opcionConteo($sis_id, $men_id, $opc_id)
{

    $anio = date("Y");
    $mes = date("m");
    if ($anio == APP_EJERCICIO) {
        $campo = "opc_";
        switch ($mes) {
            case 1:
                $campo .= "ene";
                break;
            case 2:
                $campo .= "feb";
                break;
            case 3:
                $campo .= "mar";
                break;
            case 4:
                $campo .= "abr";
                break;
            case 5:
                $campo .= "may";
                break;
            case 6:
                $campo .= "jun";
                break;
            case 7:
                $campo .= "jul";
                break;
            case 8:
                $campo .= "ago";
                break;
            case 9:
                $campo .= "sep";
                break;
            case 10:
                $campo .= "oct";
                break;
            case 11:
                $campo .= "nov";
                break;
            case 12:
                $campo .= "dic";
                break;
        }
    } elseif ($anio < APP_EJERCICIO) {
        $campo = "opc_anterior";
    } else {
        $campo = "opc_posterior";
    }
}
