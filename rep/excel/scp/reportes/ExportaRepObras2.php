<?php

if (!function_exists('u8d')) {
    function u8d($value)
    {
        return mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
    }
}

/*
    @Jordy actualizacion correspondiente al Ticket 478673
    Se agrega al reporte la clave y el concepto de la vertiente, referente y concepto.
    Se agrega la descripcion del programa, subprocgrama, proyecto. 
*/



session_start();

/*ini_set("display_errors", 2);
set_time_limit(0);
error_reporting(E_ALL);
error_reporting(E_ERROR);*/

ini_set('memory_limit','200M');
set_time_limit(0);
require_once('../../../../lib/AccesoDatos.php');

$gpo = $_SESSION[_S]['clasAdm-gpo'];
$ur = $_SESSION[_S]['clasAdm-ur'];
$prg = ((isset($_POST['prg']) && $_POST['prg']!='' ) ? $_POST['prg']:"");
$spr = ((isset($_POST['spr']) && $_POST['spr']!='' ) ? $_POST['spr']:"");
$py = ((isset($_POST['py']) && $_POST['py']!='' ) ? $_POST['py']:"");
$oa = ((isset($_POST['oa']) && $_POST['oa']!='' ) ? $_POST['oa']:"");
$wh = '';
$wh2 = '';
$wh3 = '';
$wh4 = '';

switch ($_SESSION[_S]['s20-nvj']) {
	case 1: 
		$ue .= $_SESSION[_S]['s20-ue'];
		$wh .= 'gpo_id ='.$gpo.' AND ur_id='.$ur.' ';
		$wh2 .= 'clc.gpo_id ='.$gpo.' AND clc.ur_id='.$ur.' ';
		$wh3 .= 'gpo_id ='.$gpo.' AND ur_id='.$ur.' AND ue_id='.$ue.' ';
		break;
	default:
		if(!isset($_POST['todas']) || $_POST['todas']==0){
			$wh  .= ' gpo_id ='.$gpo.' AND ur_id='.$ur.' ';
			$wh2 .= ' clc.gpo_id ='.$gpo.' AND clc.ur_id='.$ur.' ';
			$wh3 .= ' gpo_id ='.$gpo.' AND ur_id='.$ur.' ';
		}
		break;
}

/*
    @Jordy 14-09-2018
    Se agrego la siguiente clasificación para los filtros de consulta
    por error en la consulta conforme a las pruebas realizadas del ticket 478673
*/

if (!(isset($wh3) && $wh3 !='')) {
	$validador=0;
	if (isset($prg) && $prg!='') {
		switch($validador){
			case 0: 
			    $wh3 .= ' prg_id='.$prg.' ';
			    $validador=1;
			    break;
		}
	}
	if (isset($spr) && $spr!='') {
		switch($validador){
			case 0: 
			    $wh3 .= ' AND spr_id='.$spr.' ';
			    $validador=1;
			    break;
			default :
			    $wh3 .= ' AND spr_id='.$spr.' ';
			    break;
		}
	}
	if (isset($py) && $py!='') {
		switch($validador){
			case 0: 
			    $wh3 .= ' py_id='.$py.' ';
			    $validador=1;
			    break;
			default :
			    $wh3 .= ' AND py_id='.$py.' ';
			    break;
		}
	}
	if (isset($oa) && $oa!='') {
		switch($validador){
			case 0: 
			    $wh3 .= ' oa_id='.$oa.' ';
			    $validador=1;
			    break;
			default :
			    $wh3 .= ' AND oa_id='.$oa.' ';
			    break;
		}
	}
	if (isset($wh3) && $wh3 != '') {
		$wh4 .= ' AND '.$wh3.' ';
	}
	
} else {
	$wh3.= ((isset($prg) && $prg!='') ? ' AND prg_id='.$prg.' ':"");
	$wh3.= ((isset($spr) && $spr!='') ? ' AND spr_id='.$spr.' ':"");
	$wh3.= ((isset($py) && $py!='') ? ' AND py_id='.$py.' ':"");
	$wh3.= ((isset($oa) && $oa!='') ? ' AND oa_id='.$oa.' ':"");
}

$ruta= u8d("Rep_ObraCvePresupuestal.xls");

header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=".$ruta);
header("Pragma: no-cache");
header("Expires: 0");

	$hoy = date("Y-m-d H:i:s");	
		/* MOMENTANEAMENTE * /
		echo'<style>
		table {
			border:1px solid #333;
		}
		td{
			border-left :1px solid #E8E8E8;
			border-bottom :1px solid #AAA;

		}
		
		</style>';
        /*  */
		//TABLA DE LA CABECERA

		echo'
		<table align="center" width="100%" border=0; cellspacing=0; cellpadding=0;>
			<tr>
				<td colspan="2" align="left" style="font-size: 10px;"><b>Fecha '.$hoy.' </b></td>
				<td colspan=56 align="CENTER"><b>GOBIERNO DEL ESTADO DE OAXACA</b></td>
			</tr>
		  	<tr>
				<td colspan="2" align="left" style="font-size: 10px;"></td>
				<td colspan=56 align="CENTER" ><b>SECRETARIA DE FINANZAS DEL ESTADO DE OAXACA</td>
			</tr>
			<tr>
				<td colspan="2" align="left" style="font-size: 10px;"></td>
				<td colspan=56 align="CENTER"><b>PRESUPUESTO DE EGRESOS 2019</td>
			</tr>
			<tr>
				<td colspan="2" align="left" style="font-size: 10px;"></td>
				<td colspan=56 align="CENTER" ><b>REPORTE DE OBRA '.u8d('PÚBLICA').' POR CLAVE PRESUPUESTAL</td>
			</tr>
			<tr>
				<td colspan=50></td>
			</tr>
		</table>';	
			
		//TABLA PRINCIPAL DE DATOS  
		echo'<table align="center" width="100%" border=0; cellspacing=0; cellpadding=0;>';
		//CABECERA
		echo' <tr>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>EJECUTORA</td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>CLAVE UE</td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>TIPO</td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>FUENTE</td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>UR</td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>CLAVE PROGRAMA</td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>'.u8d('DESCRIPCIÓN PROGRAMA').'</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>CLAVE SUBPROGRAMA</td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>'.u8d('DESCRIPCIÓN SUBPROGRAMA').'</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>CLAVE PROYECTO</td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>'.u8d('DESCRIPCIÓN PROYECTO').'</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>OBRA/ACTIVIDAD</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>CLAVE DE LA OBRA</b></td>';
		/*----------------------------@Jordy 14-09-08 Adecuacion para el ticket 478673----------------------------------*/
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>CLAVE VERTIENTE</b></b></td>';
        echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>'.u8d('DESCRIPCIÓN VERTIENTE').'</b></b></td>';
        echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>CLAVE REFERENTE</b></b></td>';
        echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>'.u8d('DESCRIPCIÓN REFERENTE').'</b></b></td>';
        echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>CLAVE CONCEPTO</b></b></td>';
        echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>'.u8d('DESCRIPCIÓN CONCEPTO').'</b></b></td>';
		/*--------------------------------------------------------------------------------------------------------------*/
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>No. DE PPI</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>NOMBRE</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>No OBRA SSPP</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>CONTRATISTA</b></b></td>';	
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>COBERTURA</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>CLAVE R</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>REGION</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>CLAVE D</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>DISTRITO</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>CLAVE M</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>MUNICIPIO</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>CLAVE L</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>LOCALIDAD</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>MUJERES</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>HOMBRES</b></td>'; 
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>CLAVE PRESUPUESTAL</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>P.ESPECIFICA</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>CLAVE CVEFIN</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>CFI_NOMBRE</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>AUTORIZADO</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>MODIFICADO</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>COMPROMETIDO</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>DEVENGADO</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>EJERCICIO</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>MINISTRADO</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>PAGADO</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>RETENCIONES</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>EJERCIDO</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>POR EJERCER</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>ANTICIPO</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>AMORTIZACION DE ANTICIPOS</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>ANTICIPO POR AMORTIZAR</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>AVANCE FINANCIERO</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>OFICIO AUT.</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>OR_FECHA OFICIO AUT.</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>'.u8d('FECHA DE IMPORTACIÓN').'</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>META NOMBRE</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>META CANTIDAD</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #000000; color: white;"><b>META ALCANZADA</b></td>';
		echo'</tr>';
			
	$conexion = new AccesoDatos();
	$conexion->conectar();

	//Consulta 2018
/*	$sql=
	'SELECT
        #UNIDAD RESPONSABLE
    	gpo_id,ur_id,ur_nombre,ue_nombre,
        #OBRA ACTIVIDAD
		prg_id,spr_id,py_id,oa_id,ue_id,oa_nombre,oa_hombres,oa_mujeres,oa_anticipo,percepcion,retenciones,oa_2017,oa.usr_fecha,oa.oa_origen,
        #UBICACIÓN GEOGRÁFICA
		reg_id,dto_id,mun_id,loc_id,reg_nombre,dto_nombre,mun_nombre,loc_nombre,
        #CLAVE FINANCIAMIENTO
		cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,cfi_clave,cfi_nombre,cfi_ejercicio,fte_nombre,
        #PRESUPUESTO
    	VIGENTE,COMPROM,EJERCID, DEVENGADO, MINISTRADO,PAGADO,XDEVENGAR,0 AS DISPONIBLE,XEJERCID,ORIGINA,pre_claveCompleta,pe_nombre,
        #DETALLE OBRA
		ore_OficioAut,ore_fechaOficioAut,oa_amortizacion,oa_numObra,obr_fianza,obr_avanceFinanciero,obr_avanceFisico,
    	toa_id,ore_idMml,
    	metasModificadas, metasAlcanzadas, mac_descripcion, 
    	epg_id,nombre,ore_idObra,
    	#DETALLE DEL PROGRAMA, SUBPROGRAMA Y PROYECTO
    	prg_nombre,spr_nombre,py_nombre
		FROM (
			SELECT 
			    gpo_id,ur_id,epg_id,prg_id,spr_id,py_id,oa_id,ue_id,
			    prg_nombre,spr_nombre,py_nombre,
			    oa_nombre,oa_origen,oa_hombres,oa_mujeres,
			    (oa_anticipo + oa_anticipo2016) AS oa_anticipo,oa_amortizacion,Reembolso,
	            reg_id,dto_id,ug.mun_id,loc_id,
				reg_nombre,dto_nombre,mun_nombre,loc_nombre,
	        	oa_numObra,toa_id,oa_2017,oa2.usr_fecha,
	            rfc_id,CONCAT(rfc_nombre,CASE TRIM(IFNULL(rfc_paterno,"")) WHEN "" THEN "" ELSE concat(" ",rfc_paterno )END,
				CASE TRIM(IFNULL(rfc_materno,"")) WHEN "" THEN "" ELSE concat(" ",rfc_materno ) END) as nombre
	     	FROM cat_ObraActividad oa2 
	     	INNER JOIN cat_Programa USING (gpo_id,ur_id,prg_id)
	     	INNER JOIN cat_Subprograma USING (gpo_id,ur_id,prg_id,spr_id)
	     	INNER JOIN cat_Proyecto USING (gpo_id,ur_id,prg_id,spr_id,py_id) 
			INNER JOIN enc_UbicacionGeografica ug USING(eug_id)
			INNER JOIN cat_Region   USING(reg_id)
			INNER JOIN cat_Distrito USING(reg_id,dto_id)
			INNER JOIN cat_Municipio USING(reg_id,dto_id,mun_id)
			INNER JOIN cat_Localidad USING(reg_id,dto_id,mun_id,loc_id)
	    	INNER JOIN enc_Programatico USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
			LEFT JOIN (
				SELECT
					gpo_id,ur_id,epg_id,ecf_id,
					SUM(rao_importe) AS Reembolso
				FROM scp_ReintegroAnticipoObra
				WHERE   era_id = 6 
				'.(($wh=='') ? '   ':' AND '.$wh.' ').'
				GROUP BY gpo_id,ur_id,epg_id
			)R USING ( gpo_id,ur_id,epg_id	)
			LEFT JOIN enc_InversionObra USING ( gpo_id,ur_id,epg_id)
			LEFT JOIN sinpres.cat_InversionPublica  i USING ( ipu_id )
	        LEFT JOIN sinpres.cat_Proveedores prov  USING ( rfc_id )
			WHERE  '.(($wh3=='') ? ' toa_id=2  ':' '.$wh3.' AND toa_id=2 ').'
		)oa INNER JOIN (
			SELECT
				gpo_id,ur_id,epg_id,prg_id,spr_id,py_id,oa_id,pg_id,pe_id,ecf_id,tcp_id,
				SUM(pre_vigente) VIGENTE, SUM(pre_comprometido) COMPROM,
				SUM(pre_ejercido)  EJERCID, SUM(pre_devengado) DEVENGADO,
				SUM(pre_ministrado)  MINISTRADO, SUM(pre_pagado) PAGADO,
				SUM(pre_vigente) - SUM(pre_ejercido) XEJERCID,
				SUM(pre_vigente)-SUM(pre_devengado) XDEVENGAR,
				SUM(pre_aprobado) ORIGINA,
				pre_claveCompleta
			FROM presupuesto
			JOIN presupuestoVigente AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
			JOIN presupuestoEjercido AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
			JOIN presupuestoMinistrado min USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
			JOIN presupuestoComprometido AS com USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
			JOIN presupuestoDevengado AS dev USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
			JOIN presupuestoPagado AS pag USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
			JOIN presupuestoAprobado AS apr USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
				'.(($wh=='') ? '   ':' WHERE '.$wh.' ').'
			GROUP BY gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id
		)p USING( gpo_id , ur_id , epg_id,prg_id,spr_id,py_id,oa_id )
		INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id )
		INNER JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
		INNER JOIN cat_PartidaEspecifica USING (pg_id,pe_id)
		INNER JOIN poa_OrigenRecurso USING(gpo_id,ur_id,epg_id,ecf_id)
		INNER JOIN enc_ClaveFinanciamiento USING(ecf_id )
		INNER JOIN sinpres.cat_ClaveFinanciamiento2018 USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
		INNER JOIN sinpres.cat_Fuente using(fte_id)
		LEFT JOIN (
			SELECT
				gpo_id,ur_id,clc_id,epg_id,
				obr_avanceFinanciero,
				obr_avanceFisico,
				SUM(ncc_percepcion) AS percepcion,
				SUM(ncc_retencion) AS retenciones,
				obr_fianza
			FROM scp_Clc c
			INNER JOIN scp_Contable USING ( gpo_id,ur_id,clc_id )
			LEFT JOIN cat_ObraClc  oc USING( gpo_id,ur_id,clc_id )
			WHERE  ecl_id NOT IN( 1,2,19,20,21)
				AND tcl_id IN( "F","G","H")
				'.(($wh=='') ? '   ':' AND '.$wh.' ').'
			GROUP BY gpo_id,ur_id,epg_id
		)afec USING (gpo_id,ur_id,epg_id)
		LEFT JOIN (
			SELECT 
				SUM(mac_modificado) metasModificadas, SUM(mac_alcanzado) MetasAlcanzadas, 
				mac_descripcion, epg_id
			FROM cat_MetaActividad 
			JOIN cat_MetaActividadTrimestre USING (pii_id, epg_id)   
			GROUP BY epg_id
		)metas USING(epg_id)
		'.((isset($_POST['todas']) && $_POST['todas']==1) ? ' where toa_id=2 '.$wh4.' ':((!isset($_POST['todas']) || (isset($_POST['todas']) && $_POST['todas']==0)) ? ' where '.$wh3.' and toa_id=2 ':"")).' 
		GROUP BY gpo_id,ur_id,ue_id,epg_id,pre_claveCompleta
		ORDER BY epg_id;';*/


	$sql=
	'
	SELECT gpo_id,
       ur_id,
       ur_nombre,
       ue_nombre,
       prg_id,
       spr_id,
       py_id,
       oa_id,
       ue_id,
       oa_nombre,
       oa_hombres,
       oa_mujeres,
       oa_anticipo,
       percepcion,
       retenciones,
       oa_2018,
       oa.usr_fecha,
       oa.oa_origen,
       reg_id,
       dto_id,
       mun_id,
       loc_id,
       reg_nombre,
       dto_nombre,
       mun_nombre,
       loc_nombre,
       cff_id,
       fte_id,
       sft_id,
       ori_id,
       sor_id,
       cfi_id,
       cfi_anio,
       cfi_clave,
       cfi_nombre,
       cfi_ejercicio,
       fte_nombre,
       VIGENTE,
       COMPROM,
       EJERCID,
       DEVENGADO,
       MINISTRADO,
       PAGADO,
       XDEVENGAR,
       0 AS DISPONIBLE,
       XEJERCID,
       ORIGINA,
       pre_claveCompleta,
       pe_nombre,
       ore_OficioAut,
       ore_fechaOficioAut,
       oa_amortizacion,
       oa_numObra,
       obr_fianza,
       ((EJERCID)/(VIGENTE)*100) obr_avanceFinanciero,
       toa_id,
       ore_idMml,
       metasModificadas,
       metasAlcanzadas,
       mac_descripcion,
       epg_id,
       nombre,
       ore_idObra,
       prg_nombre,
       spr_nombre,
       py_nombre
  FROM
       (
           SELECT gpo_id,
                  ur_id,
                  epg_id,
                  prg_id,
                  spr_id,
                  py_id,
                  oa_id,
                  ue_id,
                  prg_nombre,
                  spr_nombre,
                  py_nombre,
                  oa_nombre,
                  oa_origen,
                  oa_hombres,
                  oa_mujeres,
                  (oa_anticipo + oa2.oa_anticipo2018) AS oa_anticipo,
                  oa_amortizacion,
                  Reembolso,
                  reg_id,
                  dto_id,
                  ug.mun_id,
                  loc_id,
                  reg_nombre,
                  dto_nombre,
                  mun_nombre,
                  loc_nombre,
                  oa_numObra,
                  toa_id,
                  oa2.oa_2018,
                  oa2.usr_fecha,
                  rfc_id,
                  	CONCAT(rfc_nombre,CASE TRIM(IFNULL(rfc_paterno,"")) WHEN "" THEN "" ELSE concat(" ",rfc_paterno )END,
				CASE TRIM(IFNULL(rfc_materno,"")) WHEN "" THEN "" ELSE concat(" ",rfc_materno ) END) as nombre
             FROM cat_ObraActividad                           oa2
                      INNER JOIN cat_Programa USING (gpo_id, ur_id, prg_id)
                      INNER JOIN cat_Subprograma USING (gpo_id, ur_id, prg_id, spr_id)
                      INNER JOIN cat_Proyecto USING (gpo_id, ur_id, prg_id, spr_id, py_id)
                      INNER JOIN enc_UbicacionGeografica      ug USING (eug_id)
                      INNER JOIN cat_Region USING (reg_id)
                      INNER JOIN cat_Distrito USING (reg_id, dto_id)
                      INNER JOIN cat_Municipio USING (reg_id, dto_id, mun_id)
                      INNER JOIN cat_Localidad USING (reg_id, dto_id, mun_id, loc_id)
                      INNER JOIN enc_Programatico USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                      LEFT JOIN
                                 (
                                     SELECT gpo_id, ur_id, epg_id, SUM(rao_importe) AS Reembolso
                                       FROM scp_ReintegroAnticipoObra
                                      WHERE era_id = 6 
                                            '.(($wh=='') ? '   ':' AND '.$wh.' ').'
                                      GROUP BY gpo_id, ur_id, epg_id
                                 )                            R USING (gpo_id, ur_id, epg_id)
                      LEFT JOIN  ( SELECT  gpo_id,ur_id,epg_id,ipu_id FROM enc_InversionObra GROUP BY gpo_id,ur_id,epg_id,ipu_id) AS X USING (gpo_id, ur_id, epg_id)
                      LEFT JOIN  sinpres.cat_InversionPublica i USING (ipu_id)
                      LEFT JOIN  sinpres.cat_Proveedores      prov USING (rfc_id)
                      WHERE
            '.(($wh3=='') ? ' toa_id=2  ':' '.$wh3.' AND toa_id=2 ').'
       )                oa
           INNER JOIN
                      (
                          SELECT gpo_id,
                                 ur_id,
                                 epg_id,
                                 prg_id,
                                 spr_id,
                                 py_id,
                                 oa_id,
                                 pg_id,
                                 pe_id,
                                 ecf_id,
                                 tcp_id,
                                 SUM(pre_vigente)                      VIGENTE,
                                 SUM(pre_comprometido)                 COMPROM,
                                 SUM(pre_ejercido)                     EJERCID,
                                 SUM(pre_devengado)                    DEVENGADO,
                                 SUM(pre_ministrado)                   MINISTRADO,
                                 SUM(pre_pagado)                       PAGADO,
                                 SUM(pre_vigente) - SUM(pre_ejercido)  XEJERCID,
                                 SUM(pre_vigente) - SUM(pre_devengado) XDEVENGAR,
                                 SUM(pre_aprobado)                     ORIGINA,
                                 pre_claveCompleta
                            FROM presupuesto
                                     JOIN       presupuestoVigente AS      vig USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                                     JOIN       presupuestoEjercido AS     eje USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                                     JOIN       presupuestoMinistrado      min USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                                     JOIN       presupuestoComprometido AS com USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                                     JOIN       presupuestoDevengado AS    dev USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                                     JOIN       presupuestoPagado AS       pag USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                                     JOIN       presupuestoAprobado AS     apr USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                                     '.(($wh=='') ? '   ':' WHERE '.$wh.' ').'
                           GROUP BY gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id
                      ) p USING (gpo_id, ur_id, epg_id, prg_id, spr_id, py_id, oa_id)
           INNER JOIN cat_UnidadResponsable USING (gpo_id, ur_id)
           INNER JOIN cat_UnidadEjecutora USING (gpo_id, ur_id, ue_id)
           INNER JOIN cat_PartidaEspecifica USING (pg_id, pe_id)
           INNER JOIN poa_OrigenRecurso USING (gpo_id, ur_id, epg_id, ecf_id)
           INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
           INNER JOIN sinpres.cat_ClaveFinanciamiento2019 USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
           INNER JOIN sinpres.cat_Fuente USING (fte_id)
           LEFT JOIN
                      (
                          SELECT gpo_id,
                                 ur_id,
                                 epg_id,
                                 SUM(percepcion) as percepcion,
                                 SUM(retenciones) as retenciones,
                                 SUM(obr_fianza) as obr_fianza
                            FROM
                                 (
                                     SELECT gpo_id,
                                            ur_id,
                                            clc_id,
                                            SUM(ncp_percepcionNeto) AS percepcion,
                                            0                       AS retenciones,
                                            o.epg_id,
                                            obr_fianza
                                       FROM scp_Clc                    c
                                                INNER JOIN cat_ObraClc o USING (gpo_id, ur_id, clc_id)
                                                INNER JOIN scp_Presupuestal USING (gpo_id, ur_id, clc_id)
                                      WHERE ecl_id NOT IN (1, 2, 19, 20, 21) 
                                            AND tcl_id IN( "F","G","H")
                                            '.(($wh=='') ? '   ':' AND '.$wh.' ').'
                                      GROUP BY gpo_id, ur_id, clc_id
                                     UNION ALL
                                     SELECT gpo_id,
                                            ur_id,
                                            clc_id,
                                            SUM(ncc_percepcion) AS percepcion,
                                            SUM(ncc_retencion)     retenciones,
                                            o.epg_id,
                                            obr_fianza
                                       FROM scp_Clc                    c
                                                INNER JOIN cat_ObraClc o USING (gpo_id, ur_id, clc_id)
                                                INNER JOIN scp_Contable USING (gpo_id, ur_id, clc_id)
                                      WHERE ecl_id NOT IN (1, 2, 19, 20, 21) 
                                            AND tcl_id IN( "F","G","H")
                                            '.(($wh=='') ? '   ':' AND '.$wh.' ').'
                                      GROUP BY gpo_id, ur_id, clc_id
                                 ) b
                           GROUP BY gpo_id, ur_id, b.epg_id
                      ) afec USING (gpo_id, ur_id, epg_id)
           LEFT JOIN
                      (
                          SELECT SUM(mac_modificado) metasModificadas,
                                 SUM(mac_alcanzado)  MetasAlcanzadas,
                                 mac_descripcion,
                                 epg_id
                            FROM cat_MetaActividad
                                     INNER JOIN cat_MetaActividadTrimestre USING (pii_id, epg_id)
                           WHERE mac_agrupadorPresupuestal = 1
                           GROUP BY epg_id, mac_descripcion
                      ) metas USING (epg_id)
		'.((isset($_POST['todas']) && $_POST['todas']==1) ? ' where toa_id=2 '.$wh4.' ':((!isset($_POST['todas']) || (isset($_POST['todas']) && $_POST['todas']==0)) ? ' where '.$wh3.' and toa_id=2 ':"")).' 
 # GROUP BY gpo_id, ur_id, ue_id, epg_id, pre_claveCompleta,cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,cfi_clave,cfi_nombre,cfi_ejercicio,    pe_nombre,mac_descripcion,nombre
 ORDER BY epg_id;
	';

	$conexion->setSql($sql);

//	echo "<pre>".$conexion->getSql()."</pre>";
//$conexion->extraer();
//die();

$conexion->consultar();

//echo "<pre>"; var_dump($conexion->extraer());
$conexion->desconectar();
//$row = $conexion->extraer();
$filas=0;
$reg_id='';
$dto_id='';
$mun_id='';
$loc_id='';
// $saldoXamortizar = 0;
$epg_id ='';
$ecf_id ='';
$disponible=0;
$saldoXamortizar = 0;
$amortizacionAnticipo = 0;

/* @Jordy 14-09-2018 Se abre la conexion al SAI 2018*/
$conexionSIIP = new AccesoDatos();
$conexionSIIP->conectar('siip_sinpres');
/*-------------------------------------------------*/


while ($row = $conexion->extraer())	{
    if($row['reg_id'] == 9){
        $cobertura = 'ESTATAL';
        $reg = $dto = $mre = $mun = $loc ='';
        // $reg_id = $row['reg_id'];
    }
    elseif($row['dto_id'] >= 91){
        $cobertura = 'REGIONAL';
			$reg=$row['reg_nombre'];
			$dto = $mre = $mun = $loc = '';
			$reg_id = $row['reg_id'];
		}
		elseif($row['mun_id'] >= 901){
			$cobertura = 'DISTRITAL';
			$reg=$row['reg_nombre'];
			$dto=$row['dto_nombre'];
			$mre = $mun = $loc='';
			$reg_id = $row['reg_id'];
			$dto_id=$row['dto_id'];
		}elseif($row['loc_id'] == 0 || $row['loc_id'] == 999){
			$cobertura = 'MUNICIPAL';
			$reg=$row['reg_nombre'];
			$dto=$row['dto_nombre'];
			$mre='';
			$mun=$row['mun_nombre'];
			$loc='';
			$reg_id = $row['reg_id'];
			$dto_id=$row['dto_id'];
			$mun_id=$row['mun_id'];
		}else{
			$cobertura = 'LOCALIDAD';
			$reg=$row['reg_nombre'];
			$dto=$row['dto_nombre'];
			$mre='';
			$mun=$row['mun_nombre'];
			$loc=$row['loc_nombre'];
			$reg_id = $row['reg_id'];
			$dto_id=$row['dto_id'];
			$mun_id=$row['mun_id'];
			$loc_id=$row['loc_id'];
		}
		
		//Obtener información de anticipos
		
		$amortizacionAnticipo = 0;
		$saldoXamortizar = 0;
		if ($epg_id<>$row['epg_id'] ) {
			$saldoXamortizar = 0;
			$amortizacionAnticipo = 0;
		}

    // El campo $row['Reembolso'], no se devuelve en la consulta, por eso se quita, anteriormente tal vez si.
    //$saldoXamortizar =  $row['oa_anticipo'] -((float)$row['oa_amortizacion']+(float)$row['Reembolso']) ;

    $saldoXamortizar =  $row['oa_anticipo'] -((float)$row['oa_amortizacion']) ;

		//------------Datos para establecer la conexion al SAI a la base de datos de SQL-------
		$ObraID = ( empty($row['ore_idObra']) ? '' : ($row['ore_idObra']>0 ? $row['ore_idObra'] : ''));

        if ($ObraID=='') {

          //  echo 'No hizo consulta en el SAI';
            $ume = array('claveVertiente'=>"",'vertienteConcepto'=>"",'claveReferente'=>"",
                'referenteConcepto'=>"",'claveClasificacion'=>"",'clasifiacionConcepto'=>"");
        }
        else {

            // Consulta en 2018
            /*$SQL =
                "SELECT
                v.cveVertiente AS claveVertiente, v.vertiente AS vertienteConcepto,
                r.cveReferente AS claveReferente, r.referente AS referenteConcepto,
                c.cveClasificacion AS claveClasificacion, c.clasificacion AS clasifiacionConcepto
            FROM acceso.OBRA o 
            INNER JOIN catalogo.vw_EncuadreVertiente_SEFIP vin ON (vin.idEncVertiente = o.idEncVertiente)
            INNER JOIN catalogo.Vertiente v ON (vin.idVertiente = v.idVertiente)
            INNER JOIN catalogo.Referente r ON (vin.idReferente = r.idReferente)
            INNER JOIN catalogo.Clasificacion c ON (vin.idClasificacion = c.idClasificacion)
            WHERE IDOBRA=".$ObraID.";";*/

            //Consulta 2019, la consulta se hace directo a las vistas.
            $SQL =
                "SELECT 
                      ev.claveVertiente AS claveVertiente,         ev .vertiente AS vertienteConcepto,
                      ev.claveReferente AS claveReferente,         ev.referente AS referenteConcepto,
                      ev.claveClasificacion AS claveClasificacion, ev.clasificacion AS clasifiacionConcepto
                    FROM consulta.vw_EtapaAutorizadaSEFIP                  ea
                        INNER JOIN consulta.vw_EncuadreVertienteSEFIP ev on (ea.idEncuadreVertiente = ev.idEncuadreVertiente)
                    WHERE ea.folioProgramacion =".$ObraID.";";

            $conexionSIIP->setSql($SQL);
            $conexionSIIP->consultar();
            $ume = $conexionSIIP->extraer();

        }
        /*---------------------------------------------------------------------------------------*/
		
		if ($filas%2==0) {

			echo'<tr>';
			echo'<td align="left" style="font-size: 10.5;"><b>'.u8d($row['ue_nombre']).'</b></td>';
			echo'<td align="left" style="font-size: 10.5;"><b>&nbsp;'.u8d($row['ue_id']).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.$row['toa_id'].'</b></td>';
			echo'<td align="left" style="font-size: 10.5;"><b>'.u8d($row['fte_nombre']).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.$row['gpo_id'].''.$row['ur_id'].'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>&nbsp;'.$row['prg_id'].'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>&nbsp;'.u8d($row['prg_nombre']).'</b></td>'; #nombre del programa
			echo'<td align="center" style="font-size: 10.5;"><b>&nbsp;'.$row['spr_id'].'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>&nbsp;'.u8d($row['spr_nombre']).'</b></td>'; #nombre del subprograma
			echo'<td align="center" style="font-size: 10.5;"><b>&nbsp;'.$row['py_id'].'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>&nbsp;'.u8d($row['py_nombre']).'</b></td>'; #nombre del proyecto 
			echo'<td align="center" style="font-size: 10.5;"><b>&nbsp;'.$row['oa_id'].'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>&nbsp;'.$row['oa_2018'].'</b></td>';
			/*----------------------------@Jordy 14-09-08 Adecuacion para el ticket 478673----------------------------------*/
		    echo'<td align="center" style="font-size: 10.5;"><b>&nbsp;'.$ume['claveVertiente'].'</b></td>';
            echo'<td align="center" style="font-size: 10.5;"><b>&nbsp;'.$ume['vertienteConcepto'].'</b></td>';
            echo'<td align="center" style="font-size: 10.5;"><b>&nbsp;'.$ume['claveReferente'].'</b></td>';
            echo'<td align="center" style="font-size: 10.5;"><b>&nbsp;'.$ume['referenteConcepto'].'</b></td>';
            echo'<td align="center" style="font-size: 10.5;"><b>&nbsp;'.$ume['claveClasificacion'].'</b></td>';
            echo'<td align="center" style="font-size: 10.5;"><b>&nbsp;'.$ume['clasifiacionConcepto'].'</b></td>';
			/*--------------------------------------------------------------------------------------------------------------*/
			echo'<td align="center" style="font-size: 10.5;"><b>&nbsp;'.u8d($row['ore_idMml']).'</b></td>';
			echo'<td align="left" style="font-size: 10.5;"><b><b>'.u8d($row['oa_nombre']).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.u8d($row['oa_numObra']).'</b></td>';
			echo'<td align="left" style="font-size: 10.5;"><b>'.u8d($row['nombre']).'</b></td>';	
			echo'<td align="center" style="font-size: 10.5;"><b>'.$cobertura.'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.$reg_id.'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.u8d($reg).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.$dto_id.'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.u8d($dto).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.$mun_id.'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.u8d($mun).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.$loc_id.'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.u8d($loc).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.$row['oa_mujeres'].'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.$row['oa_hombres'].'</b></td>'; 
			echo'<td align="center" style="font-size: 10.5;"><b>'.$row['pre_claveCompleta'].'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.u8d($row['pe_nombre']).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.u8d($row['cfi_clave']).'</b></td>';
			echo'<td align="left" style="font-size: 10.5;"><b>'.u8d($row['cfi_nombre']).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.number_format($row['ORIGINA'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.number_format($row['VIGENTE'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.number_format($row['COMPROM'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.number_format($row['DEVENGADO'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.$row['cfi_ejercicio'].'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.number_format($row['MINISTRADO'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.number_format($row['PAGADO'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.number_format($row['retenciones'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.number_format($row['EJERCID'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.number_format($row['XEJERCID'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.number_format($row['oa_anticipo'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.number_format($row['oa_amortizacion'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.number_format($saldoXamortizar,2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.number_format($row['obr_avanceFinanciero'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.$row['ore_OficioAut'].'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.$row['ore_fechaOficioAut'].'</b></td>';
			if($row['oa_origen']=='I')
			echo'<td align="center" style="font-size: 10.5;"><b>'.$row['usr_fecha'].'</b></td>';
			else
			echo'<td align="center" style="font-size: 10.5;"><b>'.$row['usr_fecha'].'</b></td>';
			
			echo'<td align="left" style="font-size: 10.5;"><b>'.u8d($row['mac_descripcion']).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.number_format($row['metasModificadas'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.number_format($row['metasAlcanzadas'],2).'</b></td>';	
			echo' </tr>';                            

		}
		else {

            echo' <tr>';
			// echo'<tr></tr>';
			echo'<td align="left" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.u8d($row['ue_nombre']).'</b></td>';
			echo'<td align="left" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>&nbsp;'.u8d($row['ue_id']).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.$row['toa_id'].'</b></td>';
			echo'<td align="left" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.u8d($row['fte_nombre']).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.$row['gpo_id'].''.$row['ur_id'].'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>&nbsp;'.$row['prg_id'].'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>&nbsp; '.u8d($row['prg_nombre']).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>&nbsp;'.$row['spr_id'].'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>&nbsp;'.u8d($row['spr_nombre']).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>&nbsp;'.$row['py_id'].'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>&nbsp;'.u8d($row['py_nombre']).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>&nbsp;'.$row['oa_id'].'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>&nbsp;'.$row['oa_2018'].'</b></td>';
			/*----------------------------@Jordy 14-09-08 Adecuacion para el ticket 478673----------------------------------*/
		    echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>&nbsp;'.$ume['claveVertiente'].'</b></td>';
            echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>&nbsp;'.$ume['vertienteConcepto'].'</b></td>';
            echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>&nbsp;'.$ume['claveReferente'].'</b></td>';
            echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>&nbsp;'.$ume['referenteConcepto'].'</b></td>';
            echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>&nbsp;'.$ume['claveClasificacion'].'</b></td>';
            echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>&nbsp;'.$ume['clasifiacionConcepto'].'</b></td>';
			/*--------------------------------------------------------------------------------------------------------------*/
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>&nbsp;'.u8d($row['ore_idMml']).'</b></td>';
			echo'<td align="left" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b><b>'.u8d($row['oa_nombre']).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.u8d($row['oa_numObra']).'</b></td>';
			echo'<td align="left" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.u8d($row['nombre']).'</b></td>';	
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.$cobertura.'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.$reg_id.'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.u8d($reg).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.$dto_id.'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.u8d($dto).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.$mun_id.'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.u8d($mun).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.$loc_id.'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.u8d($loc).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.$row['oa_mujeres'].'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.$row['oa_hombres'].'</b></td>'; 
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.$row['pre_claveCompleta'].'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.u8d($row['pe_nombre']).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.u8d($row['cfi_clave']).'</b></td>';
			echo'<td align="left" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.u8d($row['cfi_nombre']).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.number_format($row['ORIGINA'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.number_format($row['VIGENTE'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.number_format($row['COMPROM'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.number_format($row['DEVENGADO'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.$row['cfi_ejercicio'].'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.number_format($row['MINISTRADO'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.number_format($row['PAGADO'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.number_format($row['retenciones'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.number_format($row['EJERCID'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.number_format($row['XEJERCID'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.number_format($row['oa_anticipo'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.number_format($row['oa_amortizacion'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.number_format($saldoXamortizar,2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.number_format($row['obr_avanceFinanciero'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.$row['ore_OficioAut'].'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.$row['ore_fechaOficioAut'].'</b></td>';
			if($row['oa_origen']=='I')
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.$row['usr_fecha'].'</b></td>';
			else
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.$row['usr_fecha'].'</b></td>';
			echo'<td align="left" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.u8d($row['mac_descripcion']).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.number_format($row['metasModificadas'],2).'</b></td>';
			echo'<td align="center" style="font-size: 10.5; background-color: #F5ECCE; color: BLACK;"><b>'.number_format($row['metasAlcanzadas'],2).'</b></td>';	
		 echo'</tr>';
		}
		
		$filas++;
		$epg_id = $row['epg_id'];
		$ecf_id = (isset($row['ecf_id']) ? $row['ecf_id'] : '' );
	 }
	//FIN DEL CUERPO DE LA TABLA

	$conexionSIIP->desconectar(); /* @Jordy 14-09-2018 Se desconecta la conexion al SAI 2018*/
echo'</table>';
?>