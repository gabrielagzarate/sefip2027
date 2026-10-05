<?php
/**
     * Description of function nivelFolio
     *
     * @author ?
     * Collaborators: Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
     * Created: 30-Nov-2018
     * Migration: 01-Oct-2019
     * Comment: -----
     * Objective: Ordena los datos consultados para obtener los folios.
     * Regla de Negocio: -----
     * 
     * */
require_once dirname(__FILE__).'/consultascpFolio.php';

function nivelFolio($gpo_id, $ur_id, $ecf_id, $envia){

    $folio = consultascpFolio($gpo_id, $ur_id, $ecf_id, $envia);
    $folio = array_reverse($folio);
    
    $result = array();

    foreach ($folio as $key => $value) {
        $dato = array();
        $dato['mod_id']= $folio[$key]['mod_id'];
        $dato['mmd_id']= $folio[$key]['mmd_id'];
        $dato['tmd_nombre']= $folio[$key]['tmd_nombre'];
        $dato['mod_concepto']= $folio[$key]['mod_concepto'];
        $dato['mod_fechaFolio']= $folio[$key]['mod_fechaFolio'];
        $dato['emo_nombre']= $folio[$key]['emo_nombre'];
        array_push($result, $dato);
    }
    return $result;
}