<?php

if (!function_exists('u8d')) {
    function u8d($value)
    {
        return mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
    }
}

session_start();
ini_set('memory_limit','200M');
set_time_limit(0);

/**
 *  Autor: Eduardo Venegas Sarabia
 *  Descripcioón: Se realizan adecuaciones al reporte, mencionadas en el ticket 219237
 *  Fecha de Actualización: 15/03/2019

 */
require_once('../../../../lib/AccesoDatos.php');

    if (!isset($_SESSION[_S]['s24-id'])){
		echo "VERBOTEN!!!!";
        die();
	}

	
    if(!isset($_POST['html'])){
        $ruta= u8d("RepObraMS.xls");
        header('Content-type: application/vnd.ms-excel');
        header("Content-Disposition: attachment; filename=".$ruta);
        header("Pragma: no-cache");
        header("Expires: 0");
    }

		$hoy = date("Y-m-d H:i:s");	
		echo'<style type="text/css">
            .tabla{
                width:100%;
            }
            .tabla td {
                font-size:10.5px; font-family:Calibri; border-bottom:1px solid #AAAAAA; border-right:1px dashed #E0E0E0;
            }
        </style>';
		echo'<table align="center" width="200%" border = "0" >';
			echo'<tr>';
				echo'<td colspan="2" align="left" style="font-size: 10px;"><b>Fecha '.$hoy.' </b></td>';	
				echo'<td colspan=25 align="CENTER"><b>GOBIERNO DEL ESTADO DE OAXACA</b></td>';
				echo'<td colspan=1 align="CENTER">
                <img src="data:image/png;base64,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" />
                </td>';
			echo'</tr>';
		  
			echo'<tr>';
				echo'<td colspan="2" align="left" style="font-size: 10px;"></td>';
				 echo'<td colspan=26 align="CENTER" ><b>SECRETARIA DE FINANZAS DEL ESTADO DE OAXACA</td>';
			echo'</tr>';
		 
			echo'<tr>';
				echo'<td colspan="2" align="left" style="font-size: 10px;"></td>';
				echo'<td colspan=26 align="CENTER"><b>PRESUPUESTO DE EGRESOS 2022</td>';
			echo'</tr>';
		 
			echo'<tr>';
				echo'<td colspan="2" align="left" style="font-size: 10px;"></td>';
				echo'<td colspan=26 align="CENTER" ><b>REPORTE DE OBRA </td>';
			echo'</tr>';
		
			echo'<tr><td colspan=26></td></tr>';
			echo'</table>';	

            $styth ='font-size: 12.5px;font-family:Calibri; background-color: #000000; color: white;';

            
			echo'<table align="center" width="100%" border="0" cellspacing="0" cellpadding="1" class="tabla">';
			
			echo'<tr> ';
			echo'<th align="center" style="'.$styth.'"><b>CVE_UR</th>'; #1
			echo'<th align="center" style="'.$styth.'"><b>UNIDAD RESPONSABLE</th>'; #2
			echo'<th align="center" style="'.$styth.'"><b>CVE_UE</th>'; #3
			echo'<th align="center" style="'.$styth.'"><b>UNIDAD EJECUTORA</th>';#4
			
			echo'<th align="center" style="'.$styth.'"><b>CVE_PROGRAMA</th>';
            echo'<th align="center" style="'.$styth.'"><b>PROGRAMA</th>';#5
            echo'<th align="center" style="'.$styth.'"><b>CVE_SUBPROGRAMA</th>';
			echo'<th align="center" style="'.$styth.'"><b>SUBPROGRAMA</th>';#6
			echo'<th align="center" style="'.$styth.'"><b>CVE_PROYECTO</th>';
			echo'<th align="center" style="'.$styth.'"><b>PROYECTO</th>';#7
			echo'<th align="center" style="'.$styth.'"><b>CVE_OA</th>';
			echo'<th align="center" style="'.$styth.'"><b>OBRA/ACTIVIDAD</b></th>';#8
			
            echo'<th align="center" style="'.$styth.'"><b>CLAVE DE LA OBRA</b></th>';#9
            echo'<th align="center" style="'.$styth.'"><b>No. DE PPI</b></th>';#11
			echo'<th align="center" style="'.$styth.'"><b>No OBRA SSPP</b></th>';#12
			echo'<th align="center" style="'.$styth.'"><b>CONTRATISTA</b></b></th>';#13	
			
			echo'<th align="center" style="'.$styth.'"><b>COBERTURA</b></th>';#10
			echo'<th align="center" style="'.$styth.'"><b>CLAVE R</b></th>';#14
			echo'<th align="center" style="'.$styth.'"><b>REGION</b></th>';#15
			echo'<th align="center" style="'.$styth.'"><b>CLAVE D</b></th>';#16
			echo'<th align="center" style="'.$styth.'"><b>DISTRITO</b></th>';#17
			echo'<th align="center" style="'.$styth.'"><b>CLAVE M</b></th>';#18
			echo'<th align="center" style="'.$styth.'"><b>MUNICIPIO</b></th>';#19
			echo'<th align="center" style="'.$styth.'"><b>CLAVE L</b></th>';#20
			echo'<th align="center" style="'.$styth.'"><b>LOCALIDAD</b></th>';#21
			echo'<th align="center" style="'.$styth.'"><b>MUJERES</b></th>';#22
			echo'<th align="center" style="'.$styth.'"><b>HOMBRES</b></th>'; #23
			echo'<th align="center" style="'.$styth.'"><b>TIPO POBLACI&Oacute;N</b></th>';#24
			
            echo'<th align="center" style="'.$styth.'"><b>CLAVE DE FINANCIAMIENTO</b></th>';#25
			echo'<th align="center" style="'.$styth.'"><b>NOMBRE FUENTE DE FINANCIAMIENTO</b></th>';#26
			echo'<th align="center" style="'.$styth.'"><b>EJERCICIO</b></th>';#27
            
            echo'<th align="center" style="'.$styth.'"><b>AUTORIZADO</b></th>';#28
			echo'<th align="center" style="'.$styth.'"><b>MODIFICADO</b></th>';#29
			echo'<th align="center" style="'.$styth.'"><b>COMPROMETIDO</b></th>';#31
			echo'<th align="center" style="'.$styth.'"><b>POR COMPROMETER</b></th>';// SE AGREGA
			echo'<th align="center" style="'.$styth.'"><b>DEVENGADO</b></th>';#32
			echo'<th align="center" style="'.$styth.'"><b>MINISTRADO</b></th>';#33
			echo'<th align="center" style="'.$styth.'"><b>RETENCION</b></th>';#33
			echo'<th align="center" style="'.$styth.'"><b>EJERCIDO</b></th>';#34
			echo'<th align="center" style="'.$styth.'"><b>POR EJERCER</b></th>';#35
			echo'<th align="center" style="'.$styth.'"><b>ANTICIPO</b></th>';#36
			echo'<th align="center" style="'.$styth.'"><b>AMORTIZACION DE ANTICIPOS</b></th>';#37
			echo'<th align="center" style="'.$styth.'"><b>ANTICIPO POR AMORTIZAR</b></th>';#38
			echo'<th align="center" style="'.$styth.'"><b>PAGADO</b></th>';#39
            
            echo'<th align="center" style="'.$styth.'"><b>AVANCE FISICO</b></th>';#39
			echo'<th align="center" style="'.$styth.'"><b>AVANCE FINANCIERO</b></th>';#40
			echo'<th align="center" style="'.$styth.'"><b>OFICIO AUTORIZACI&Oacute;N</b></th>';#41
			echo'<th align="center" style="'.$styth.'"><b>OR_FECHA OFICIO AUT.</b></th>';#42
			echo'<th align="center" style="'.$styth.'"><b>'.u8d('FECHA DE IMPORTACI&Oacute;N').'</b></th>';#43
			
            echo'<th align="center" style="'.$styth.'"><b>CVE_PRODUCTO INTERMEDIO</b></th>';#44
            echo'<th align="center" style="'.$styth.'"><b>PRODUCTO INTERMEDIO</b></th>';#45
            echo'<th align="center" style="'.$styth.'"><b>CVE_INDICADOR</b></th>';#46
            echo'<th align="center" style="'.$styth.'"><b>INDICADOR</b></th>';#47
            echo'<th align="center" style="'.$styth.'"><b>CANTIDAD INDICADOR</b></th>';#47
            echo'<th align="center" style="'.$styth.'"><b>CVE_UNIDAD DE MEDIDA</b></th>';#48
            echo'<th align="center" style="'.$styth.'"><b>UNIDAD DE MEDIDA</b></th>';#49
            echo'<th align="center" style="'.$styth.'"><b>CVE_UNIDAD DE MEDIDA_VARIABLE</b></th>';#51
            echo'<th align="center" style="'.$styth.'"><b>UNIDAD DE MEDIDA_VARIABLE</b></th>';#52  
			echo'<th align="center" style="'.$styth.'"><b>CANTIDAD VARIABLE</b></th>';#53

            /** Autor: Eduardo Venegas
             *  Descripción: Se agregan campos, solicitados en el ticket #219237
             *  Fecha: 19/03/2019
             *
             */
            echo'<th align="center" style="'.$styth.'"><b>AMPLIACION</b></th>';#54
            echo'<th align="center" style="'.$styth.'"><b>FECHA ULTIMA AMPLIACION</b></th>';#55
            echo'<th align="center" style="'.$styth.'"><b>REDUCCION</b></th>';#56
            echo'<th align="center" style="'.$styth.'"><b>FECHA ULTIMA REDUCCION</b></th>';#57
            echo'<th align="center" style="'.$styth.'"><b>CLAVE VERTIENTE</b></th>';#58
            echo'<th align="center" style="'.$styth.'"><b>'.u8d('DESCRIPCIÓN VERTIENTE').'</b></th>';#59
            echo'<th align="center" style="'.$styth.'"><b>CLAVE REFERENTE</b></th>';#60
            echo'<th align="center" style="'.$styth.'"><b>'.u8d('DESCRIPCIÓN REFERENTE').'</b></th>';#61
            echo'<th align="center" style="'.$styth.'"><b>CLAVE CLASIFICACION</b></th>';#62
            echo'<th align="center" style="'.$styth.'"><b>'.u8d('DESCRIPCIÓN CLASIFICACION').'</b></th>';#63
            echo'<th align="center" style="'.$styth.'"><b>'.u8d('OBRA/ACCIÓN').'</b></th>';#54
            echo'<th align="center" style="'.$styth.'"><b>CALENDARIZADO</b></th>';#55
              /*--------------------------------------------------------------------------------------------------------------*/

			echo'</tr>';
			
	$conexion = new AccesoDatos();
	$conexion->conectar();		 
	
	$sql='
		SELECT
        #UNIDAD RESPONSABLE
            gpo_id,ur_id,ur_nombre,ue_id,ue_nombre,
        #OBRA ACTIVIDAD
            prg_id,prg_nombre,spr_id,spr_nombre,py_id,py_nombre,oa_id,oa_nombre,ore_idMml,ore_idObra,oa_numObra,oa_hombres,oa_mujeres,oa_2021,pbf_nombre,
        #UBICACIÓN GEOGRÁFICA
            reg_id,dto_id,mun_id,loc_id,reg_nombre,dto_nombre,mun_nombre,loc_nombre,
        #CLAVE FINANCIAMIENTO
            cfi_clave,cfi_nombre,cfi_ejercicio,
        #Ampliacion/Reduccion
        #Los campos ampliacion y reduccion vienen de presupuesto
            Ampliacion,UltimaAmpliacion FechaAmpliacion, Reduccion,UltimaReduccion FechaReduccion,
        #PRESUPUESTO
            Ejercido,Aprobado,Vigente,Comprometido,por_comprometer,Devengado,Ministrado,Pagado,Calendarizado,
            ore_OficioAut,
            DATE_FORMAT(ore_fechaOficioAut,"%d/%m/%Y") AS ore_fechaOficioAut,
            DATE_FORMAT(oa.usr_fecha,"%d/%m/%Y") AS  usr_fecha,
            pdi_id,
            pdi_nombre,
            ind_id,
            ind_nombre,
            umi_id,
            umi_nombre,
            mModificadas,
            aFin,aFis,epg_clave,
            umv_id, umv_nombre,
            CantidadIndicador
        FROM
        (
            SELECT gpo_id,ur_id,epg_id,
                prg_id,prg_nombre,spr_id,spr_nombre,py_id,py_nombre,oa_id,oa_nombre,ue_id,
                reg_id,dto_id,mun_id,loc_id,oa_origen,reg_nombre,dto_nombre,mun_nombre,loc_nombre,
                oa_hombres,oa_mujeres,oa_numObra,oa.usr_fecha,oa_2021,epg_clave,
                 pbf_nombre
            FROM cat_Programa
            INNER JOIN cat_Subprograma USING (gpo_id,ur_id,prg_id )
            INNER JOIN cat_Proyecto USING (gpo_id,ur_id,prg_id,spr_id )
            INNER JOIN cat_ObraActividad oa USING ( gpo_id,ur_id,prg_id,spr_id,py_id )
            INNER JOIN enc_Programatico USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            INNER JOIN enc_UbicacionGeografica USING(eug_id)
            INNER JOIN cat_Region   USING(reg_id)
            INNER JOIN cat_Distrito USING(reg_id,dto_id)
            INNER JOIN cat_Municipio USING(reg_id,dto_id,mun_id)
            INNER JOIN cat_Localidad USING(reg_id,dto_id,mun_id,loc_id)
			INNER JOIN cat_PoblacionBeneficiada USING ( pbf_id )
            WHERE   toa_id = 2
        )oa
        INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id )
        INNER JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
        INNER JOIN poa_OrigenRecurso USING(gpo_id,ur_id,epg_id)
        INNER JOIN enc_ClaveFinanciamiento USING(ecf_id )
        INNER JOIN sinpres.cat_ClaveFinanciamiento2022 USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
        INNER JOIN (
            SELECT
                gpo_id,ur_id,epg_id,ecf_id,
                SUM(pre_ejercido) Ejercido,
                SUM(pre_aprobado) Aprobado,
                SUM(pre_vigente) Vigente,
                SUM(pre_comprometido) Comprometido,
                SUM(pre_vigente) - SUM(pre_comprometido) AS por_comprometer,
                SUM(pre_devengado) Devengado,
                SUM(pre_ministrado) Ministrado,
                SUM(pre_pagado) Pagado,
                SUM( pre_ampliacion ) Ampliacion,
                SUM( pre_reduccion ) Reduccion,
                (CASE MONTH(NOW())# Arroja los mismos resultados que el de porEJercer por usuario dice que no siempre es asi, es por eso que se deja la funcion
                     WHEN 1 THEN SUM(vig_ene)
                     WHEN 2 THEN SUM(vig_ene) + SUM(vig_feb)
                     WHEN 3 THEN SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar)
                     WHEN 4 THEN SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar) + SUM(vig_abr)
                     WHEN 5 THEN SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar) + SUM(vig_abr) + SUM(vig_may)
                     WHEN 6 THEN SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar) + SUM(vig_abr) + SUM(vig_may) + SUM(vig_jun)
                     WHEN 7 THEN SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar) + SUM(vig_abr) + SUM(vig_may) +
                                 SUM(vig_jun) + SUM(vig_jul)
                     WHEN 8 THEN SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar) + SUM(vig_abr) + SUM(vig_may) +
                                 SUM(vig_jun) + SUM(vig_jul) + SUM(vig_ago)
                     WHEN 9 THEN SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar) + SUM(vig_abr) + SUM(vig_may) +
                                 SUM(vig_jun) + SUM(vig_jul) + SUM(vig_ago) + SUM(vig_sep)
                     WHEN 10 THEN SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar) + SUM(vig_abr) + SUM(vig_may) +
                                  SUM(vig_jun) + SUM(vig_jul) + SUM(vig_ago) + SUM(vig_sep) + SUM(vig_oct)
                     WHEN 11 THEN SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar) + SUM(vig_abr) + SUM(vig_may) +
                                  SUM(vig_jun) + SUM(vig_jul) + SUM(vig_ago) + SUM(vig_sep) + SUM(vig_oct) +
                                  SUM(vig_nov)
                     WHEN 12 THEN SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar) + SUM(vig_abr) + SUM(vig_may) +
                                  SUM(vig_jun) + SUM(vig_jul) + SUM(vig_ago) + SUM(vig_sep) + SUM(vig_oct) +
                                  SUM(vig_nov) + SUM(vig_dic)
                     ELSE 0
                    END) Calendarizado
                FROM presupuesto
                INNER JOIN presupuestoEjercido AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoAprobado AS apr USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoVigente AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
				        INNER JOIN presupuestoComprometido AS com USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
				        INNER JOIN presupuestoDevengado AS dev USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoMinistrado AS min USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
				        INNER JOIN presupuestoPagado AS pag USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                -- WHERE tcp_id !="P"
            GROUP BY gpo_id,ur_id,epg_id,ecf_id
        )p USING(gpo_id , ur_id , epg_id,ecf_id)
        LEFT JOIN(
            SELECT gpo_id,ur_id,epg_id,ecf_id ,SUM(obr_avanceFinanciero) AS aFin,SUM(obr_avanceFisico) AS AFis
            FROM     scp_Clc
            INNER JOIN cat_ObraClc USING ( gpo_id,ur_id,clc_id)
            WHERE tcl_id IN ("F","G","H")
                AND ecl_id NOT IN (1,2,14,15,17)
            GROUP BY gpo_id,ur_id,epg_id,ecf_id
        )Av USING ( gpo_id,ur_id,epg_id ,ecf_id )
        
        
        LEFT JOIN (
            -- Se modifica esta parte de la consulta para que arroje los mismos resultados que 2018.
          SELECT
				epg_id,MAX(pdi_id) as pdi_id,MAX(pdi_nombre) pdi_nombre,MAX(ind_id) AS ind_id,MAX(ind_nombre) as ind_nombre,
                MAX(umi_id)  umi_id ,MAX(umi_nombre) umi_nombre,
                MAX(umv_id)  umv_id, MAX(umv_nombre) umv_nombre, #Unidad de medida variable
                SUM(mact_modificado) mModificadas,
                SUM(mac_alcanzado) mAlcanzadas,
                (SUM(mac_alcanzado)/SUM(mac_modificado))*100 CantidadIndicador
            FROM cat_MetaActividad
            JOIN cat_MetaActividadTrimestre USING(pii_id, epg_id)
            INNER JOIN vin_ProductoIntermedioIndicador USING ( pii_id)
            INNER JOIN cat_ProductoIntermedio USING ( pdi_id )
            INNER JOIN cat_Indicador USING ( nim_id,ind_id)
            INNER JOIN cat_UnidadMedidaIndicador USING (umi_id)
            INNER JOIN cat_UnidadMedidaVariable USING(umv_id)
            WHERE mac_agrupadorPresupuestal = 1
            GROUP BY epg_id
        )Meta USING( epg_id )
            #Ampliacion/Reduccion
        LEFT JOIN
               (
                   SELECT *
                     FROM (
                         (
                             SELECT gpo_id,
                                    ur_id,
                                    epg_id,
                                    SUM(dmo_importe) AS Ampliacion2,
                                    MAX(mm.mod_fechaAutorizacion)    UltimaAmpliacion
                               FROM scp_MaestroModificacion       mm
                                        INNER JOIN scp_DetalleModificacion dm USING (omo_id, mod_gpo_id, mod_ur_id, mod_id)
                                        INNER JOIN enc_Programatico USING (gpo_id, ur_id, epg_id)
                                        INNER JOIN cat_ObraActividad USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id)
                              WHERE dm.mov_id = "A" AND mm.emo_id > 13
                              GROUP BY gpo_id, ur_id, epg_id
                         ) AS A
                             LEFT JOIN
                             (
                                 SELECT gpo_id,
                                        ur_id,
                                        epg_id,
                                        SUM(dmo_importe) AS Reduccion2,
                                        MAX(mm.mod_fechaAutorizacion)  UltimaReduccion
                                   FROM scp_MaestroModificacion       mm
                                            INNER JOIN scp_DetalleModificacion dm USING (omo_id, mod_gpo_id, mod_ur_id, mod_id)
                                            INNER JOIN enc_Programatico USING (gpo_id, ur_id, epg_id)
                                            INNER JOIN cat_ObraActividad USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id)
                                  WHERE dm.mov_id = "R" AND mm.emo_id > 13
                                  GROUP BY gpo_id, ur_id, epg_id
                             ) AS B USING (gpo_id, ur_id, epg_id)
                         )
               ) Z USING (gpo_id, ur_id, epg_id);
	';
 
	$conexion->setSql($sql);
/*	echo "<pre>".$conexion->getSql()."</pre>";
	die();*/
	$conexion->consultar();
	$conexion->desconectar();

	$filas=0;
	$reg_id='';
	$dto_id='';
	$mun_id='';
	$loc_id='';
	$epg_id ='';
	$ecf_id ='';
	$disponible=0;
	$saldoXamortizar = 0;
	$amortizacionAnticipo = 0;

    $conexionSIIP = new AccesoDatos();
    $conexionSIIP->conectar('siip_sinpres');

	while ($row = $conexion->extraer())	{ 
	
		if($row['reg_id'] == 9){
			$cobertura = 'ESTATAL';
			$reg = $dto = $mre = $mun = $loc ='';
		}elseif($row['dto_id'] >= 91){
			$cobertura = 'REGIONAL';
			$reg=$row['reg_nombre'];
			$dto = $mre = $mun = $loc = '';
			$reg_id = $row['reg_id'];
		}elseif($row['mun_id'] >= 901){
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
			$reg = $row['reg_nombre'];
			$dto = $row['dto_nombre'];
			$mre = '';
			$mun = $row['mun_nombre'];
			$loc = $row['loc_nombre'];
			$reg_id = $row['reg_id'];
			$dto_id=$row['dto_id'];
			$mun_id=$row['mun_id'];
			$loc_id=$row['loc_id'];
		}
		
        $st = '';

        $ObraID = ( empty($row['ore_idObra']) ? '' : ($row['ore_idObra']>0 ? $row['ore_idObra'] : ''));
        
    /*    $ume = array('claveVertiente'=>"",'vertienteConcepto'=>"",'claveReferente'=>"",
        'referenteConcepto'=>"",'claveClasificacion'=>"",'clasifiacionConcepto'=>"",'tipoActividad'=>"");
       */ 
        
        if ($ObraID=='') {
            $ume = array('claveVertiente'=>"",'vertienteConcepto'=>"",'claveReferente'=>"",
                'referenteConcepto'=>"",'claveClasificacion'=>"",'clasifiacionConcepto'=>"",'tipoActividad'=>"");
        }
        else {
            //Consulta que se hace hacia el SAI
            $SQL =
                "SELECT 
                      ev.claveVertiente AS claveVertiente,         ev.vertiente AS vertienteConcepto,
                      ev.claveReferente AS claveReferente,         ev.referente AS referenteConcepto,
                      ev.claveClasificacion AS claveClasificacion, ev.clasificacion AS clasificacionConcepto,
                      ev.clasificacionTipoActividad tipoActividad
                    FROM consulta.vw_EtapaAutorizadaSEFIP                  ea
                        INNER JOIN consulta.vw_EncuadreVertienteSEFIP ev on (ea.idEncuadreVertiente = ev.idEncuadreVertiente)
                    WHERE ea.folioProgramacion =".$ObraID.";";

            $conexionSIIP->setSql($SQL);
            $conexionSIIP->consultar();
            $ume = $conexionSIIP->extraer();
        }
        
        if($filas%2==0)
            $st = 'bgcolor="#EAEAEA"';
        else
			$st = 'bgcolor="#FFFFFF"';
            $style='';
            // $style=' font-size:10.5px; font-family:Calibri; border-bottom:1px solid #AAAAAA; border-right:1px dashed #E0E0E0';
        
			
            echo'<tr '.$st.' style="border-bottom:1px solid #AAAAAA">';
			echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['gpo_id'].$row['ur_id']).'</b></td>';
			echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['ur_nombre']).'</b></td>';
			echo'<td align="left"   style="'.$style.'"><b>&nbsp;'.u8d($row['ue_id']).'</b></td>';
			echo'<td align="left"   style="'.$style.'"><b>&nbsp;'.u8d($row['ue_nombre']).'</b></td>';
			
			echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['prg_id']).'</b></td>'; 
			echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['prg_nombre']).'</b></td>';
			echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['spr_id']).'</b></td>';
			echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['spr_nombre']).'</b></td>';
			echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['py_id']).'</b></td>';
			echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['py_nombre']).'</b></td>';
			echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['oa_id']).'</b></td>';
			echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['oa_nombre']).'</b></td>'; // Separamos las claves de sus descripciones
			/*echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['prg_id']."-".$row['prg_nombre']).'</b></td>';
			echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['spr_id']."-".$row['spr_nombre']).'</b></td>';
			echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['py_id']."-".$row['py_nombre']).'</b></td>';
			echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['oa_id']."-".$row['oa_nombre']).'</b></td>';*/
			echo'<td align="center" style="'.$style.'"><b>&nbsp;'.u8d($row['epg_clave']).'</b></td>';
			
            echo'<td align="center" style="'.$style.'"><b>&nbsp;'.u8d($row['ore_idMml']).'</b></td>';
			echo'<td align="center" style="'.$style.'"><b>'.u8d($row['oa_numObra']).'</b></td>';
			echo'<td align="center" style="'.$style.'"><b>'.u8d(" --- ").'</b></td>';
			
			echo'<td align="center" style="'.$style.'"><b>'.$cobertura.'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.$reg_id.'</b></td>';
			echo'<td align="center" style="'.$style.'"><b>'.u8d($reg).'</b></td>';
			echo'<td align="center" style="'.$style.'"><b>'.$dto_id.'</b></td>';
			echo'<td align="center" style="'.$style.'"><b>'.u8d($dto).'</b></td>';
			echo'<td align="center" style="'.$style.'"><b>'.$mun_id.'</b></td>';
			echo'<td align="center" style="'.$style.'"><b>'.u8d($mun).'</b></td>';
			echo'<td align="center" style="'.$style.'"><b>'.$loc_id.'</b></td>';
			echo'<td align="center" style="'.$style.'"><b>'.u8d($loc).'</b></td>';
			echo'<td align="center" style="'.$style.'"><b>'.$row['oa_mujeres'].'</b></td>';
			echo'<td align="center" style="'.$style.'"><b>'.$row['oa_hombres'].'</b></td>'; 
			echo'<td align="center" style="'.$style.'"><b>'.u8d($row['pbf_nombre']).'</b></td>';
			
            echo'<td align="center" style="'.$style.'"><b>'.u8d($row['cfi_clave']).'</b></td>';
			echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['cfi_nombre']).'</b></td>';
			echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['cfi_ejercicio']).'</b></td>';
			
            echo'<td align="center" style="'.$style.'"><b>'.number_format($row['Aprobado'],2).'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.number_format($row['Vigente'],2).'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.number_format($row['Comprometido'],2).'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.number_format($row['por_comprometer'],2).'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.number_format($row['Devengado'],2).'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.number_format($row['Ministrado'],2).'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.number_format(0.0,2).'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.number_format($row['Ejercido'],2).'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.number_format($row['Vigente']-$row['Ejercido'],2).'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.number_format($row['oa_anticipo'],2).'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.number_format($row['oa_amortizacion'],2).'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.number_format($row['oa_anticipo']- floatval($row['oa_amortizacion']),2).'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.number_format($row['Pagado'],2).'</b></td>';
            
            echo'<td align="center" style="'.$style.'"><b>'.number_format($row['aFis'],2).'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.number_format($row['aFin'],2).'</b></td>';
			echo'<td align="center" style="'.$style.'"><b>'.$row['ore_OficioAut'].'</b></td>';
			echo'<td align="center" style="'.$style.'"><b>'.$row['ore_fechaOficioAut'].'</b></td>';
			
            echo'<td align="center" style="'.$style.'"><b>'.$row['usr_fecha'].'</b></td>';
			
            echo'<td align="center" style="'.$style.'"><b>'.$row['pdi_id'].'</b></td>';
            echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['pdi_nombre']).'</b></td>';
            
            echo'<td align="center" style="'.$style.'"><b>'.$row['ind_id'].'</b></td>';
            echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['ind_nombre']).'</b></td>';
            echo'<td align="center"   style="'.$style.'"><b>'.number_format($row['CantidadIndicador'],2).'</b></td>';
            
            echo'<td align="center" style="'.$style.'"><b>'.$row['umi_id'].'</b></td>';
            echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['umi_nombre']).'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.$row['umv_id'].'</b></td>';
            echo'<td align="left"   style="'.$style.'"><b>'.u8d($row['umv_nombre']).'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.number_format($row['mModificadas'],2).'</b></td>';

            // Campos agregados
            echo'<td align="center" style="'.$style.'"><b>'.number_format($row['Ampliacion'],2).'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.$row['FechaAmpliacion'].'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.number_format($row['Reduccion'],2).'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.$row['FechaReduccion'].'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.$ume['claveVertiente'].'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.$ume['vertienteConcepto'].'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.$ume['claveReferente'].'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.$ume['referenteConcepto'].'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.$ume['claveClasificacion'].'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.$ume['clasificacionConcepto'].'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.$ume['tipoActividad'].'</b></td>';
            echo'<td align="center" style="'.$style.'"><b>'.number_format($row['Calendarizado'],2).'</b></td>';


        echo' </tr>';


		$filas++;
	 }

    $conexionSIIP->desconectar();
echo'</table>';
?>