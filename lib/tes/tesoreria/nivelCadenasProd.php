<?php

/**
 * Description of function nivelCadenasProd
 *
 * @author ?
 * Collaborators: Adriana Santiago Morales <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 14-Nov-2018
 * Update: 10-Oct-2022
 * Comment: -----
 * Objective: Ordena los datos consultados en niveles de acuerdo a la opcion que se requiera, claveFinanciamiento.
 * Regla de Negocio: -----
 * 
 * */
require_once dirname(__FILE__) . '/consultaCadenasRepPdf.php';

function nivelCadenasProd($fpa_id, $msc_id, $lot_ejercicio, $lot_id, $lot_tipo)
{
    $clave = consultaCadenasRepPdf($fpa_id, $msc_id, $lot_ejercicio, $lot_id, $lot_tipo);

    $nivel11 = '';
    $datos = array();

    if ($clave[0]['nivel'] != 1) {
        $suma = 0;
        $cadena = "";
        $contador = 1;
        $fechaAut = '';
        foreach ($clave as $key => $val) {
            // Definir el nivel 11
            if ($clave[$key]['lotDet_fpa'] != '') {
                $dato = array();
                $dato['contador'] = $contador;
                $dato['lotDet_fpa'] = $clave[$key]['lotDet_fpa'];
                $dato['lotDet_msc'] = $clave[$key]['lotDet_msc'];
                $dato['lotDet_ejercicio'] = $clave[$key]['lotDet_ejercicio'];
                $dato['lotDet'] = $clave[$key]['lotDet'];
                $dato['lotDet_gpo'] = $clave[$key]['lotDet_gpo'];
                $dato['lotDet_ur'] = $clave[$key]['lotDet_ur'];
                $dato['lotDet_clc'] = $clave[$key]['lotDet_clc'];
                $dato['lotDet_importe'] = $clave[$key]['lotDet_importe'];
                $dato['lotDet_beneficiario'] = $clave[$key]['lotDet_beneficiario'];
                $dato['lotDet_concepto'] = $clave[$key]['lotDet_concepto'];
                $dato['ur'] = $clave[$key]['ur'];
                $dato['cere'] = $clave[$key]['cere'];
                $dato['Folio'] = $clave[$key]['Folio'];
                $dato['lote_id'] = $clave[$key]['lote_id'];
                $dato['cargo'] = $clave[$key]['cargo'];
                $dato['cve_fin'] = $clave[$key]['cve_fin'];
                $dato['responsable'] = $clave[$key]['responsable'];
                $dato['lotDet_fecha'] = $clave[$key]['lotDet_fecha'];
                $dato['fecha_Pago'] = $clave[$key]['fecha_Pago'];
                $dato['lote_egreso'] = $clave[$key]['lote_egreso'];
                $dato['nombre_cuenta'] = $clave[$key]['nombre_cuenta'];
                $dato['nombre_banco'] = $clave[$key]['nombre_banco'];
                if ($fpa_id == 3 && $lot_tipo == 5) {
                    $dato['clc_lineaCaptura'] = $clave[$key]['clc_lineaCaptura'];
                    $cadena.=$clave[$key]['lote_egreso'] . "|" . $clave[$key]['lotDet_clc'] . "|" . $clave[$key]['lca_importe'] . "|" . $clave[$key]['lote_id'];
                    $suma += $clave[$key]['lca_importe'];
                } else {
                    $dato['destino'] = $clave[$key]['destino'];
                    $cadena.=$clave[$key]['lote_egreso'] . "|" . $clave[$key]['lotDet_clc'] . "|" . $clave[$key]['lotDet_importe'] . "|" . $clave[$key]['lote_id'];
                    $suma += $clave[$key]['lotDet_importe'];
                }

                $dato['nivel'] = 11;
                array_push($datos, $dato);

                $contador++;

                $fechaAut = $clave[$key]['fecha_Pago'];
            }
        }
        $dato = array();
        $dato['suma'] = $suma;
        $dato['cadena_original'] = md5($cadena);

        $arrFechaA = explode("-", $fechaAut);

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
    } else {
        switch ($ccd_id) {
            case 1:
                break;
        }
    }

    return array_reverse($datos);
}
?>