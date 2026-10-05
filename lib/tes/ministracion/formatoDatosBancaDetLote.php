<?php
/**
 * Description of function formatoDatosBancaDetLote
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 15-Ene-2019
 * Comment: -----
 * Objective: Ordena los datos consultados para poder hacer el reporte del detalle lote.
 * Regla de Negocio: -----
 *
 * */

require_once dirname(__FILE__) . '/consultaBanca.php';

function formatoDatosBancaDetLote($lot_id, $fpa_id, $msc_id, $lot_ejercicio)
{

    $clave = consultaBanca($lot_id, $fpa_id, $msc_id, $lot_ejercicio);

    $datos = array();

    $contador = 1;
    $suma = 0;
    foreach ($clave as $key => $val) {
        if ($clave[$key]['lotDet_fechaPago'] != '') {
            $dato = array();
            $dato['contador'] = $contador;
            $dato['lotDet_fechaPago'] = $clave[$key]['lotDet_fechaPago'];
            $dato['lote_egreso'] = empty($clave[$key]['lote_egreso']) ? '--' : $clave[$key]['lote_egreso'];
            $dato['lotDet_ur'] = $clave[$key]['lotDet_ur'];
            $dato['lotDet_clc'] = empty($clave[$key]['lotDet_clc']) ? '--' : $clave[$key]['lotDet_clc'];
            $dato['lotDet_tipo'] = empty($clave[$key]['lotDet_tipo']) ? '--' : $clave[$key]['lotDet_tipo'];
            $dato['lotDet_folio'] = $clave[$key]['lotDet_folio'];
            $dato['lotDet_folioL'] = $clave[$key]['lotDet_folioL'];
            $dato['lotDet_lineaC'] = $clave[$key]['lotDet_lineaC'];
            $dato['lotDet_importe'] = $clave[$key]['lotDet_importe'];
            $dato['lotDet_responsable'] = empty($clave[$key]['lotDet_responsable']) ? '--' : $clave[$key]['lotDet_responsable'];
            $dato['lotDet_beneficiario'] = $clave[$key]['lotDet_beneficiario'];
            $dato['lotDet_concepto'] = $clave[$key]['lotDet_concepto'];
            $dato['banco'] = empty($clave[$key]['banco']) ? '--' : $clave[$key]['banco'];
            $dato['lotDet_CtaCargo'] = "'" . $clave[$key]['lotDet_CtaCargo'];
            $dato['lotDet_CtaAbono'] = "'" . $clave[$key]['lotDet_CtaAbono'];
            $dato['cvefin'] = empty($clave[$key]['cvefin']) ? '--' : $clave[$key]['cvefin'];
            $dato['lotDet'] = $clave[$key]['lotDet'];

            $importe = str_replace(",", "", $clave[$key]['lotDet_importe']);
            $suma += floatval(number_format($importe, 2, '.', ''));

            array_push($datos, $dato);

            $contador++;
        }
    }

    $dato = array();
    $dato['contador'] = '';
    $dato['lotDet_fechaPago'] = '';
    $dato['lote_egreso'] = '';
    $dato['lotDet_ur'] = '';
    $dato['lotDet_clc'] = '';
    $dato['lotDet_tipo'] = '';
    $dato['lotDet_folio'] = 'TOTAL';
    $dato['lotDet_folioL'] = '';
    $dato['lotDet_lineaC'] = '';
    $dato['lotDet_importe'] = $suma;
    $dato['lotDet_responsable'] = '';
    $dato['lotDet_beneficiario'] = '';
    $dato['lotDet_concepto'] = '';
    $dato['banco'] = '';
    $dato['lotDet_CtaCargo'] = '';
    $dato['lotDet_CtaAbono'] = '';
    $dato['cvefin'] = '';
    $dato['lotDet'] = '';

    array_push($datos, $dato);
    return $datos;
}
