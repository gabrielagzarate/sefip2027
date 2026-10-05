<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
 require_once dirname(__FILE__).'/../../../lib/scg/reporte/importeSqlAuxiliar.php';
require_once dirname(__FILE__).'/../../../lib/calculaPeso.php';
 
	function auxiliar( $cuenta, $gpo_id, $ur_id, $tipo, $periodoArreglo, $cajaArreglo, $busqueda, $cuentaArreglo, $deue, $cfi ){
		$pesoInicial = '';
		$datosExtra['inicial'] = TRUE;
		$datosInicial = importeSqlAuxiliar( $cuenta, $gpo_id, $ur_id, $tipo, $periodoArreglo, $cajaArreglo, $busqueda, $cuentaArreglo, $deue, $cfi, $datosExtra );
		$peso = calcularPeso( count($datosInicial) * 100000, 50 );
		ini_set('memory_limit', $peso.'M' );
		
		$datos = Array();
		$debe = 0;
		$haber = 0;
		foreach ($datosInicial as $key => $inicial) {
			$datosExtra["gpo"] = $inicial["cta_gpo_id"];
			$datosExtra["ur"] = $inicial["cta_ur_id"];
			$datosExtra["ue"] = $inicial["cta_ue_id"];
			$datosExtra['ger_id'] = $inicial['ger_id'];
			$datosExtra['cgp_id'] = $inicial['cgp_id'];
			$datosExtra['rub_id'] = $inicial['rub_id'];
			$datosExtra['cgcu_id'] = $inicial['cgcu_id'];
			$datosExtra['cgsc_id'] = $inicial['cgsc_id'];
			$datosExtra['cgc_id'] = $inicial['cgc_id'];
			$datosExtra['cta_id'] = $inicial['cta_id'];
			$datosExtra['inicial'] = FALSE;
			$datos[$inicial['id']]['inicial'] = $inicial;
			$datosMovimiento = importeSqlAuxiliar( $cuenta, $gpo_id, $ur_id, $tipo, $periodoArreglo, $cajaArreglo, $busqueda, $cuentaArreglo, $deue, $cfi, $datosExtra, $pesoInicial );
			
			foreach ($datosMovimiento as $key => $movimiento) {
				//echo '<pre>';print_r($movimiento);echo '</pre>';die();
				$datos[$inicial['id']]['movimiento'][] = $movimiento;
				$debe = (($movimiento['epl_id']!='C' && $movimiento['epl_id']!='H') ? $debe + $movimiento['pdx_debe'] : $debe);
				$haber = (($movimiento['epl_id']!='C' && $movimiento['epl_id']!='H') ? $haber + $movimiento['pdx_haber'] : $haber);
			}
			//echo '<pre>';print_r($datosMovimiento);echo '</pre>';die();
		}
		$datos['total'] = array(
			'gpo_id' => '',
			'ur_id' => '',
			'ue_id' => '',
			'pot_id' => '',
			'prf_id' => '',
			'msc_id' => '',
			'pma_id' => '',
			'pdt_id' => '',
			'pdx_id' => '',
			'cta_gpo_id' => '',
			'cta_ur_id' => '',
			'cta_ue_id' => '',
			'ger_id' => '',
			'cgp_id' => '',
			'rub_id' => '',
			'cgcu_id' => '',
			'cgsc_id' => '',
			'cgc_id' => '',
			'cta_id' => '',
			'pdx_debe' => $debe,
			'pdx_haber' => $haber,
			'nat_id' => '',
			'pdx_concepto' => 'TOTAL',
			'tdf_id' => '',
			'pdx_referencia' => '',
			'pdx_fechaReferencia' => '',
			'ecf_id' => '',
			'pdx_bdo_id_origen' => '',
			'mmc_id' => '',
			'epl_id' => '',
			'pma_concepto' => '',
			'concepto' => '',
			'pma_fecha' => '',
			'cta_cuenta' => '',
			'cta_nombre' => '',
			'cgsc_naturalezaFija' => '',
			'cgsc_cuenta' => '',
			'cgsc_nombre' => '',
			'ecf_clave' => ''
		);
		//echo '<pre>';print_r($datos);echo '</pre>';die();
		return $datos;
	}
	
?>