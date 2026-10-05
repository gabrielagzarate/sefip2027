<?php
/**
 * Description of function nivelBanca
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 09-Ene-2018
 * Comment: -----
 * Objective: Ordena los datos consultados en niveles.
 * Regla de Negocio: -----
 *
 * */

require_once dirname(__FILE__) . '/consultaBanca.php';

function nivelBanca($lot_id, $fpa_id, $msc_id, $lot_ejercicio)
{

    $clave = consultaBanca($lot_id, $fpa_id, $msc_id, $lot_ejercicio);


    $datos = array();
    if ($clave[0]['nivel'] != 1) {
        $contador = 1;
        $cadena = "";
        $fecha = "";
        $suma = 0;
        foreach ($clave as $key => $val) {
            // Definir el nivel 11
            if ($clave[$key]['lotDet_fechaPago'] != '') {
                $dato = array();

                $dato['contador'] = $contador;
                $dato['lotDet_fechaPago'] = $clave[$key]['lotDet_fechaPago'];
                $dato['lote_egreso'] = $clave[$key]['lote_egreso'];
                $dato['lotDet_ur'] = $clave[$key]['lotDet_ur'];
                $dato['lotDet_clc'] = $clave[$key]['lotDet_clc'];
                $dato['lotDet_tipo'] = $clave[$key]['lotDet_tipo'];
                $dato['lotDet_folio'] = $clave[$key]['lotDet_folio'];
                $dato['lotDet_folioL'] = $clave[$key]['lotDet_folioL'];
                $dato['lotDet_lineaC'] = $clave[$key]['lotDet_lineaC'];
                $dato['lotDet_importe'] = $clave[$key]['lotDet_importe'];
                $dato['lotDet_responsable'] = $clave[$key]['lotDet_responsable'];
                $dato['lotDet_beneficiario'] = $clave[$key]['lotDet_beneficiario'];
                $dato['lotDet_concepto'] = $clave[$key]['lotDet_concepto'];
                $dato['banco'] = $clave[$key]['banco'];
                $dato['lotDet_CtaCargo'] = $clave[$key]['lotDet_CtaCargo'];
                $dato['lotDet_CtaAbono'] = $clave[$key]['lotDet_CtaAbono'];
                $dato['cvefin'] = $clave[$key]['cvefin'];
                $dato['lotDet'] = $clave[$key]['lotDet'];

                $importe = str_replace(",", "", $clave[$key]['lotDet_importe']);
                $suma += floatval(number_format($importe, 2, '.', ''));

                $dato['nivel'] = 11;
                array_push($datos, $dato);

                $cadena .= $clave[$key]['lote_egreso'] . "|" . $clave[$key]['lotDet_clc'] . "|" . $clave[$key]['lotDet_importe'] . "|" . $clave[$key]['lotDet'];
                $fecha = $clave[$key]['lotDet_fechaPago'];

                $contador++;
            }
        }

        $dato = array();
        $dato['suma'] = $suma;
        $dato['cadena_original'] = md5($cadena);

        $arrFechaA = explode("-", $fecha);

        $mes = "";
        switch ($arrFechaA[1]) {
            case 1:
                $mes = "Enero";
                break;
            case 2:
                $mes = "Febrero";
                break;
            case 3:
                $mes = "Marzo";
                break;
            case 4:
                $mes = "Abril";
                break;
            case 5:
                $mes = "Mayo";
                break;
            case 6:
                $mes = "Junio";
                break;
            case 7:
                $mes = "Julio";
                break;
            case 8:
                $mes = "Agosto";
                break;
            case 9:
                $mes = "Septiembre";
                break;
            case 10:
                $mes = "Octubre";
                break;
            case 11:
                $mes = "Noviembre";
                break;
            case 12:
                $mes = "Diciembre";
                break;
            default:
                $mes = "s/m";
                break;
        }
        $dato['lugar_DeProcedencia'] = 'Reyes Mantecón, Oaxaca a ' . $arrFechaA[2] . ' de ' . $mes . ' del ' . $arrFechaA[0];

        $dato['nivel'] = 12;
        array_push($datos, $dato);
    }

    return array_reverse($datos);
}
