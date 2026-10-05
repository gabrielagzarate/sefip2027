<?php
/**
     * Description of function nivelFolio
     *
     * @author ?
     * Collaborators: Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
     * Created: 18-Dic-2018
     * Migration: 03-Oct-2019
     * Comment: -----
     * Objective: Ordena los datos consultados para obtener los movimientos del folio.
     * Regla de Negocio: -----
     * 
     * */
require_once dirname(__FILE__).'/consulta_MovtoFolio.php';

function nivelFolioMovto($omo_id, $mod_gpo_id, $mod_ur_id, $mod_id, $envia, $bandera)
{
    if ($bandera == 'PDF') {
        $folio = consulta_MovtoFolio($omo_id, $mod_gpo_id, $mod_ur_id, $mod_id, $envia,$bandera);
        $folio = array_reverse($folio);
        $result = array();

        foreach ($folio as $key => $value) {
            $dato = array();
            $dato['tipo'] = $folio[$key]['tipo'];
            $dato['descripcion'] = $folio[$key]['descripcion'];
            $dato['movimiento'] = $folio[$key]['movimiento'];
            $dato['importe'] = $folio[$key]['importe'];
            $dato['clave'] = $folio[$key]['clave'];
            array_push($result, $dato);
        }
        return $result;
    } else {
        $folio  = consulta_MovtoFolio($omo_id, $mod_gpo_id, $mod_ur_id, $mod_id, $envia,$bandera);
        $folio  = array_reverse($folio);
        $result = array();

        foreach ($folio as $key => $value) {
            $dato = array();

            $dato['gpo_id'] = $folio[$key]['gpo_id'];
            $dato['ur_id'] = $folio[$key]['ur_id'];
            $dato['ue_id'] = $folio[$key]['ue_id'];
            $dato['prg_id'] = $folio[$key]['prg_id'];
            $dato['spr_id'] = $folio[$key]['spr_id'];
            $dato['py_id'] = $folio[$key]['py_id'];
            $dato['oa_id'] = $folio[$key]['oa_id'];
            $dato['pg_id'] = $folio[$key]['pg_id'];
            $dato['pe_id'] = $folio[$key]['pe_id'];
            $dato['cff_id'] = $folio[$key]['cff_id'];
            $dato['fte_id'] = $folio[$key]['fte_id'];
            $dato['sft_id'] = $folio[$key]['sft_id'];
            $dato['ori_id'] = $folio[$key]['ori_id'];
            $dato['sor_id'] = $folio[$key]['sor_id'];
            $dato['spr_id'] = $folio[$key]['spr_id'];
            $dato['cfi_id'] = $folio[$key]['cfi_id'];
            $dato['cfi_anio'] = $folio[$key]['cfi_anio'];
            $dato['tcp_id'] = $folio[$key]['tcp_id'];

            $dato['dmo_importe'] = $folio[$key]['dmo_importe'];
            $dato['dmo_ene'] = $folio[$key]['dmo_ene'];
            $dato['dmo_feb'] = $folio[$key]['dmo_feb'];
            $dato['dmo_mar'] = $folio[$key]['dmo_mar'];
            $dato['dmo_abr'] = $folio[$key]['dmo_abr'];
            $dato['dmo_may'] = $folio[$key]['dmo_may'];
            $dato['dmo_jun'] = $folio[$key]['dmo_jun'];
            $dato['dmo_jul'] = $folio[$key]['dmo_jul'];
            $dato['dmo_ago'] = $folio[$key]['dmo_ago'];
            $dato['dmo_sep'] = $folio[$key]['dmo_sep'];
            $dato['dmo_oct'] = $folio[$key]['dmo_oct'];
            $dato['dmo_nov'] = $folio[$key]['dmo_nov'];
            $dato['dmo_dic']= $folio[$key]['dmo_dic'];

            array_push($result, $dato);
        }//fin $bandera
        return $result;
    }
}//FIN nivelFolioMovto