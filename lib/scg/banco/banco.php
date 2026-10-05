<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/polizaBanco.php';
require_once dirname(__FILE__) . '/../../redondear.php';
require_once dirname(__FILE__) . '/../../ordenarArray.php';
require_once dirname(__FILE__) . '/../../compararCadenas.php';

function banco($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $tipo, $niveles, $parametroExtra = array())
{

    $contable = polizaBanco($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $tipo, $niveles, $parametroExtra);
    $sumaContable = 0;

    $matriz = array();
    $matriz['C'] = $contable;
    asort($matriz['C']);
    foreach ($matriz as $key => $value) {
        foreach ($value as $llave => $valor) {
            $datos[$llave]['id'] = $llave;
        }
    }
    asort($datos);
    $dat = array();

    foreach ($datos as $ind => $indval) {
        foreach ($matriz as $key => $value) {
            foreach ($value as $llave => $valor) {
                if (compararCadenas($ind, $llave) == 0) {
                    $row = new stdClass();
                    $row->key = $value[$llave]['id'];
                    $row->gpo_id = $value[$llave]['gpo_id'];
                    $row->ur_id = $value[$llave]['ur_id'];
                    $row->ur_clave = $value[$llave]['ur_clave'];
                    $row->ur_nombre = $value[$llave]['ur_nombre'];
                    $row->ue_id = $value[$llave]['ue_id'];
                    $row->tdf_id = ($value[$llave]['referencia'] == '' ? $value[$llave]['tdf_id_poliza'] : $value[$llave]['tdf_id']);
                    $row->referencia = ($value[$llave]['referencia'] == '' ? $value[$llave]['pdx_referencia'] : $value[$llave]['referencia']);
                    $row->fechaReferencia = (($value[$llave]['referencia'] == '' ? $value[$llave]['pdx_fechaReferencia'] : $value[$llave]['fechaReferencia']) != '' ? ($value[$llave]['referencia'] == '' ? $value[$llave]['pdx_fechaReferencia'] : $value[$llave]['fechaReferencia']) : $value[$llave]['pma_fecha']);
                    $row->banco_nombre = ($value[$llave]['iba_id'] == '' ? $value[$llave]['cgc_nombre'] : $value[$llave]['iba_nombre']);
                    $row->banco_cuenta = ($value[$llave]['iba_id'] == '' ? '' : $value[$llave]['bur_cuentaBancaria']);
                    $row->banco_alias = ($value[$llave]['iba_id'] == '' ? $value[$llave]['cta_nombre'] : $value[$llave]['bur_alias']);
                    $row->ter_id = $value[$llave]['ter_id'];
                    $row->ter_nombre = $value[$llave]['ter_nombre'];
                    $row->ecf_id = $value[$llave]['ecf_id'];
                    $row->cfi_clave = $value[$llave]['cfi_clave'];
                    $row->cfi_nombre = $value[$llave]['cfi_nombre'];
                    $row->pdx_ejercicio = $value[$llave]['pdx_ejercicio'];
                    switch ($key) {
                        case 'C':
                            $datos[$ind]['pdx_importe'] = redondear(intval($value[$llave]['pdx_importe']), 2);
                            $row->afe_id = $value[$llave]['afe_id'];
                            $row->bdo_id = $value[$llave]['bdo_id'];
                            $row->clc_id = $value[$llave]['clc_id'];
                            $row->poliza = $value[$llave]['poliza'];
                            $row->pdx_importe = redondear(intval($value[$llave]['pdx_importe']), 2);
                            break;
                    }
                    array_push($dat, $row);
                    break;
                }
            }
            if (compararCadenas($ind, $llave) == 0) {
                $x = array_shift($matriz[$key]);
            }
        }
        $sumaContable += $datos[$ind]['pdx_importe'];
    }
    $dato = '';
    $datos = ordenarArray($datos, '', 'ASC', true);
    if ($reporte['tipo'] != 1 && $reporte['tipo'] != '')
        $datos = $dato;

    $row = new stdClass();
    $row->key = 'total';
    $row->gpo_id = '';
    $row->ur_id = '';
    $row->ur_clave = '';
    $row->ur_nombre = '';
    $row->ur_id = '';
    $row->tdf_id = '';
    $row->referencia = '';
    $row->fechaReferencia = '';
    $row->banco_nombre = '';
    $row->banco_cuenta = '';
    $row->banco_alias = 'TOTAL';
    $row->ter_id = '';
    $row->ter_nombre = '';
    $row->ecf_id = '';
    $row->cfi_clave = '';
    $row->cfi_nombre = '';
    $row->pdx_importe = redondear($sumaContable, 2);
    $row->afe_id = '';
    $row->bdo_id = '';
    $row->clc_id = '';
    $row->poliza = '';
    $row->pdx_ejercicio = '';
    array_push($dat, $row);
    $datos["data"] = $dat;
    return $datos;
}
