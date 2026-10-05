<?php
/**
 * Description of function formatoDatosClaveFinanciamiento
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 09-Sep-2019
 * Comment: -----
 * Objective: Ordena los datos consultados para mostrar el catalogos de claves de financiamiento.
 * Regla de Negocio: -----
 *
 * */
    
require_once dirname(__FILE__).'/consultaClaveFinanciamiento.php';

function formatoDatosClaveFinanciamiento($ccd_id, $primeraParteClave, $segundaParteClave)
{
    $cdatos = consultaClaveFinanciamiento($ccd_id, $primeraParteClave, $segundaParteClave);

    $cffAnt = '';
    $fteAnt = '';
    $sftAnt = '';
    $oriAnt = '';
    $sorAnt = '';

    $datos = array();
    foreach ($cdatos as $key => $value) {
        if ($cdatos[$key]['cff_id'] != $cffAnt) {
            $dato = array(
                'cff_id' => '','fte_id' => '','sft_id' => '','ori_id' => '','sor_id' => '','cfi_id' => '',
                'cfi_anio' => '','cff_nombre' => '', 'fte_nombre' => '','sft_nombre' => '','ori_nombre' => '',
                'sor_nombre' => '','cfi_nombre' => ''
            );
            $dato["cff_id"] = $cdatos[$key]['cff_id'];
            $dato["cff_nombre"] = $cdatos[$key]['cff_nombre'];
            $cffAnt = $cdatos[$key]['cff_id'];
            $fteAnt = '';
            $sftAnt = '';
            $oriAnt = '';
            $sorAnt = '';
            array_push($datos, $dato);
        }
        if ($cdatos[$key]['fte_id'] != $fteAnt) {
            $dato = array(
                'cff_id' => '', 'fte_id' => '', 'sft_id' => '', 'ori_id' => '', 'sor_id' => '', 'cfi_id' => '',
                'cfi_anio' => '', 'cff_nombre' => '', 'fte_nombre' => '', 'sft_nombre' => '', 'ori_nombre' => '',
                'sor_nombre' => '', 'cfi_nombre' => ''
            );
            $dato["cff_id"] = $cdatos[$key]['cff_id'];
            $dato["fte_id"] = $cdatos[$key]['fte_id'];
            $dato["fte_nombre"] = $cdatos[$key]['fte_nombre'];
            $fteAnt = $cdatos[$key]['fte_id'];
            $sftAnt = '';
            $oriAnt = '';
            $sorAnt = '';
            
            array_push($datos, $dato);
        }
        if ($cdatos[$key]['sft_id'] != $sftAnt) {
            $dato = array(
                'cff_id' => '', 'fte_id' => '', 'sft_id' => '', 'ori_id' => '', 'sor_id' => '', 'cfi_id' => '',
                'cfi_anio' => '', 'cff_nombre' => '', 'fte_nombre' => '', 'sft_nombre' => '', 'ori_nombre' => '',
                'sor_nombre' => '', 'cfi_nombre' => ''
            );
            $dato["cff_id"] = $cdatos[$key]['cff_id'];
            $dato["fte_id"] = $cdatos[$key]['fte_id'];
            $dato["sft_id"] = $cdatos[$key]['sft_id'];
            $dato["sft_nombre"] = $cdatos[$key]['sft_nombre'];

            $sftAnt = $cdatos[$key]['sft_id'];
            $oriAnt = '';
            $sorAnt = '';

            array_push($datos, $dato);
        }
        if ($cdatos[$key]['ori_id'] != $oriAnt) {
            $dato = array(
                'cff_id' => '', 'fte_id' => '', 'sft_id' => '', 'ori_id' => '', 'sor_id' => '', 'cfi_id' => '',
                'cfi_anio' => '', 'cff_nombre' => '', 'fte_nombre' => '', 'sft_nombre' => '', 'ori_nombre' => '',
                'sor_nombre' => '', 'cfi_nombre' => ''
            );

            $dato["cff_id"] = $cdatos[$key]['cff_id'];
            $dato["fte_id"] = $cdatos[$key]['fte_id'];
            $dato["sft_id"] = $cdatos[$key]['sft_id'];
            $dato["ori_id"] = $cdatos[$key]['ori_id'];

            $dato["ori_nombre"] = $cdatos[$key]['ori_nombre'];
            $oriAnt = $cdatos[$key]['ori_id'];
            $sorAnt = '';

            array_push($datos, $dato);
        }
        if ($cdatos[$key]['sor_id'] != $sorAnt) {
            $dato = array('cff_id' => '','fte_id' => '','sft_id' => '','ori_id' => '','sor_id' => '','cfi_id' => '',
                'cfi_anio' => '', 'cff_nombre' => '',  'fte_nombre' => '', 'sft_nombre' => '', 'ori_nombre' => '', 
                'sor_nombre' => '', 'cfi_nombre' => ''
            );
            
            $dato["cff_id"] = $cdatos[$key]['cff_id'];
            $dato["fte_id"] = $cdatos[$key]['fte_id'];
            $dato["sft_id"] = $cdatos[$key]['sft_id'];
            $dato["ori_id"] = $cdatos[$key]['ori_id'];
            $dato["sor_id"] = $cdatos[$key]['sor_id'];

            $dato["sor_nombre"] = $cdatos[$key]['sor_nombre'];
            $sorAnt = $cdatos[$key]['sor_id'];

            array_push($datos,  $dato);
        }

        $dato = array(
            'cff_id' => '', 'fte_id' => '', 'sft_id' => '', 'ori_id' => '', 'sor_id' => '', 'cfi_id' => '',
            'cfi_anio' => '', 'cff_nombre' => '', 'fte_nombre' => '', 'sft_nombre' => '', 'ori_nombre' => '',
            'sor_nombre' => '', 'cfi_nombre' => ''
        );
        $dato["cff_id"] = $cdatos[$key]['cff_id'];
        $dato["fte_id"] = $cdatos[$key]['fte_id'];
        $dato["sft_id"] = $cdatos[$key]['sft_id'];
        $dato["ori_id"] = $cdatos[$key]['ori_id'];
        $dato["sor_id"] = $cdatos[$key]['sor_id'];
        $dato["cfi_id"] = $cdatos[$key]['cfi_id'];
        $dato["cfi_anio"] = $cdatos[$key]['cfi_anio'];
        $dato["cfi_nombre"] = $cdatos[$key]['cfi_nombre'];
        $dato['ecf_poa'] = $cdatos[$key]['ecf_poa'];
        $dato['ecf_division'] = $cdatos[$key]['ecf_division'];
        $dato['pdp_id'] = $cdatos[$key]['pdp_id'];

        array_push($datos, $dato);
    }
    return $datos;
}
