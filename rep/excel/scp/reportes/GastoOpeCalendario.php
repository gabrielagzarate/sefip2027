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
require_once('../../../../lib/AccesoDatos.php');

    if (!isset($_SESSION[_S]['s24-id'])){
		echo "VERBOTEN!!!!";
        die();
	}
	
    if(!isset($_POST['html'])){        
        $ruta= u8d("RepGastoOpeCalendario.xls");
        header('Content-type: application/vnd.ms-excel');
        header("Content-Disposition: attachment; filename=".$ruta);
        header("Pragma: no-cache");
        header("Expires: 0");
    }		
			
	$conexion = new AccesoDatos();
	$conexion->conectar();		 

	$sql='
	SELECT 
    #UNIDAD RESPONSABLE
    CONCAT(gpo_id,ur_id)CveUR,ur_nombre UR,ue_id,ue_nombre,
    #OBRA ACTIVIDAD
    prg_id CvePrg, prg_nombre Programa, spr_id CveSpr, spr_nombre SubPrograma, py_id CvePy, py_nombre Proyecto, oa_id, oa_nombre,
    #FINANCIAMIENTO 
    cfi_clave CveFinanciamiento,cfi_nombre nomFuenteFinanciamiento,cfi_ejercicio Ejercicio,
    #PRESUPUESTOS
    Aprobado, Modificado, Comprometido, PorComprometer, Devengado, Ejercido, PorEjercer, Pagado, PorPagar,
    Ene, Feb, Mar, Abr, May, Jun, Jul, Ago, Sep, Voc, Nov, Dic
    FROM 
        ( #Obra actividad
            SELECT gpo_id,ur_id,ue_id,epg_id,prg_id,prg_nombre,spr_id,spr_nombre,py_id,py_nombre,oa_id,oa_nombre                
            FROM cat_Programa
            INNER JOIN cat_Subprograma USING (gpo_id,ur_id,prg_id )
            INNER JOIN cat_Proyecto USING (gpo_id,ur_id,prg_id,spr_id )
            INNER JOIN cat_ObraActividad oa USING ( gpo_id,ur_id,prg_id,spr_id,py_id )
            INNER JOIN enc_Programatico USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)# tabla para unir las claves de financiamiento
            WHERE   toa_id = 1 #TipoObraActivadad = gastos operativos
        )oa
    INNER JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
    INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id )
    # financiamiento
    INNER JOIN poa_OrigenRecurso USING(gpo_id,ur_id,epg_id) 
    INNER JOIN enc_ClaveFinanciamiento USING(ecf_id )
    INNER JOIN sinpres.cat_ClaveFinanciamiento2022 USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
    # Presupuestos
    INNER JOIN (
            SELECT 
                gpo_id,ur_id,epg_id,ecf_id,
                SUM(pre_aprobado) Aprobado,
                SUM(pre_vigente) Modificado,
                SUM(pre_comprometido) Comprometido,
                SUM(pre_vigente) - SUM(pre_comprometido) AS PorComprometer,
                SUM(pre_devengado) Devengado,
                SUM(pre_ejercido) Ejercido,
                SUM(pre_vigente) - SUM(pre_ejercido) PorEjercer,
                SUM(pre_pagado) Pagado,
                SUM(pre_vigente) - SUM(pre_pagado) as PorPagar,
                SUM(vig_ene)Ene, SUM(vig_feb)Feb, SUM(vig_mar)Mar, SUM(vig_abr)Abr, 
                SUM(vig_may)May, SUM(vig_jun)Jun, SUM(vig_jul)Jul, SUM(vig_ago)Ago, 
                SUM(vig_sep)Sep, SUM(vig_oct)Voc, SUM(vig_nov)Nov, SUM(vig_dic)Dic
                FROM presupuesto
                INNER JOIN presupuestoAprobado AS apr USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoComprometido AS com USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoDevengado AS dev USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoEjercido AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoVigente AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoPagado AS pag USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)                
                WHERE tcp_id !="P"
            GROUP BY gpo_id,ur_id,epg_id,ecf_id
        )p USING(gpo_id , ur_id , epg_id,ecf_id);
	';
 
	$conexion->setSql($sql);
	// echo "<pre>".$conexion->getSql()."</pre>";die();

	$conexion->consultar();
	$conexion->desconectar();	
            
	$cuerpo = '<table align="center" width="100%" border="0" cellspacing="0" cellpadding="1" class="tabla">';			
		$cuerpo.='<tr>';
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CVE_UR</th>'; #1
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>UNIDAD RESPONSABLE</th>'; #2	
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CVE_UE</th>'; #3
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>UNIDAD EJECUTORA</th>'; #4

		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CVE_PRG</th>';#5
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>PROGRAMA</th>';#6
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CVE_SPR</th>';#7
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>SUBPROGRAMA</th>';#8
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CVE_PROYECTO</th>';#9
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>PROYECTO</th>';#10
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CVE_OBRA/ACTIVIDAD</th>';#11
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>NOMBRE OBRA/ACTIVIDAD</b></th>';#12

        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CLAVE DE FINANCIAMIENTO</b></th>';#13
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>NOMBRE FUENTE DE FINANCIAMIENTO</b></th>';#14
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>EJERCICIO</b></th>';#15

        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>APROBADO</b></th>';#16
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>MODIFICADO</b></th>';#17
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>COMPROMETIDO</b></th>';#18
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>POR COMPROMETER</b></th>';#19
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>DEVENGADO</b></th>';#20
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>EJERCIDO</b></th>';#21
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>POR EJERCER</b></th>';#22
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>PAGADO</b></th>';#23
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>POR PAGAR</b></th>';#24

        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>ENE</b></th>';#25
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>FEB</b></th>';#26
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>MAR</b></th>';#27
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>ABR</b></th>';#28
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>MAY</b></th>';#29
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>JUN</b></th>';#30
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>JUL</b></th>';#31
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>AGO</b></th>';#32
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>SEP</b></th>';#33
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>OCT</b></th>';#34
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>NOV</b></th>';#35
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>DIC</b></th>';#36
		$cuerpo.='</tr>';
	
    $filas=0;
	while ($row = $conexion->extraer())	{		
        $st = '';// pintar filas intercaladamente
		if($filas%2==0){
            $st = 'bgcolor="#EAEAEA"';
		}
        else{
			$st = 'bgcolor="#FFFFFF"';
		}			        
			
            $cuerpo.='<tr '.$st.' style="border-bottom:1px solid #AAAAAA">';
			$cuerpo.='<td align="left"   class="sEstilo"><b>'.u8d($row['CveUR']).'</b></td>';
			$cuerpo.='<td align="left"   class="sEstilo"><b>'.u8d($row['UR']).'</b></td>';
            $cuerpo.='<td align="left"   class="sEstilo"><b>'.u8d($row['ue_id']).'</b></td>';
            $cuerpo.='<td align="left"   class="sEstilo"><b>'.u8d($row['ue_nombre']).'</b></td>';

			$cuerpo.='<td align="center"   class="sEstilo"><b>&nbsp;'.u8d($row['CvePrg']).'</b></td>';
			$cuerpo.='<td align="left"   class="sEstilo"><b>&nbsp;'.u8d($row['Programa']).'</b></td>';
			$cuerpo.='<td align="center"   class="sEstilo"><b>'.u8d($row['CveSpr']).'</b></td>'; 
			$cuerpo.='<td align="left"   class="sEstilo"><b>'.u8d($row['SubPrograma']).'</b></td>';
			$cuerpo.='<td align="center"   class="sEstilo"><b>'.u8d($row['CvePy']).'</b></td>';
			$cuerpo.='<td align="left"   class="sEstilo"><b>'.u8d($row['Proyecto']).'</b></td>';
			$cuerpo.='<td align="center"   class="sEstilo"><b>'.u8d($row['oa_id']).'</b></td>';
			$cuerpo.='<td align="left"   class="sEstilo"><b>'.u8d($row['oa_nombre']).'</b></td>';

			$cuerpo.='<td align="center" class="sEstilo"><b>&nbsp;'.u8d($row['CveFinanciamiento']).'</b></td>';			
            $cuerpo.='<td align="left" class="sEstilo"><b>&nbsp;'.u8d($row['nomFuenteFinanciamiento']).'</b></td>';
			$cuerpo.='<td align="left" class="sEstilo"><b>'.u8d($row['Ejercicio']).'</b></td>';
					
			$cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Aprobado'],2).'</b></td>';
			$cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Modificado'],2).'</b></td>';
			$cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Comprometido'],2).'</b></td>';
			$cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['PorComprometer'],2).'</b></td>';
			$cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Devengado'],2).'</b></td>';
			$cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Ejercido'],2).'</b></td>';
			$cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['PorEjercer'],2).'</b></td>';
			$cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Pagado'],2).'</b></td>';
			$cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['PorPagar'],2).'</b></td>';

			$cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Ene'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Feb'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Mar'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Abr'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['May'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Jun'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Jul'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Ago'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Sep'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Voc'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Nov'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Dic'],2).'</b></td>';
			$cuerpo.=' </tr>';                            

		$filas++;
	 }	
$cuerpo.='</table>';




$hoy = date("Y-m-d H:i:s");	
$estilo= '<style type="text/css">
    .tabla{
        width:100%;
    }
    .tabla td {
        font-size:10.5px; font-family:Calibri; border-bottom:1px solid #AAAAAA; border-right:1px dashed #E0E0E0;
    }
    .encabezadoCuerpo{
    	font-size: 12.5px;font-family:Calibri; background-color: #000000; color: white;
    }
</style>';
$numcolumnas = 33;
$numcolizq = ($numcolumnas - ($numcolumnas - 3) );
$numcolder = ($numcolumnas - ($numcolumnas - 3) );
$numcolcentral = $numcolumnas - ($numcolizq + $numcolder);

$encabezado='<table align="center" width="200%" border = "0" >';
	$encabezado.='<tr>';
		$encabezado.='<td colspan="'.$numcolizq.'" align="left" style="font-size: 10px;"><b>Fecha '.$hoy.' </b></td>';	
		$encabezado.='<td colspan="'.$numcolcentral.'" align="CENTER"><b>GOBIERNO DEL ESTADO DE OAXACA</b></td>';
        $encabezado.='<td colspan="'.$numcolder.'" rowspan="4" align="right">
        <!--Se quita la imagen, no se ve desde el ejercicio 2019
        <img src="data:image/png;base64, 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" />
        -->
        </td>
    </tr>';
  
	$encabezado.='<tr>
		<td colspan="'.$numcolizq.'" align="left" style="font-size: 10px;"></td>
		<td colspan="'.$numcolcentral.'" align="CENTER" ><b>SECRETARIA DE FINANZAS DEL ESTADO DE OAXACA</td>
	</tr>';
 
	$encabezado.='<tr>
		<td colspan="'.$numcolizq.'" align="left" style="font-size: 10px;"></td>
		<td colspan="'.$numcolcentral.'"  align="CENTER"><b>PRESUPUESTO DE EGRESOS 2022</td>
	</tr>';
 
	$encabezado.='<tr>
		<td colspan="'.$numcolizq.'" align="left" style="font-size: 10px;"></td>
		<td colspan="'.$numcolcentral.'" align="CENTER" ><b>REPORTE GASTO OPERATIVO CALENDARIO </b></td>
	</tr>';

    $encabezado.='<tr>
        <td colspan="'.$numcolumnas.'">
        </td>
    </tr>';

	$encabezado.='<tr>
		<td colspan="'.$numcolumnas.'">
            '.$cuerpo.
        '</td>
	</tr>';
	$encabezado.='</table>';

    $tabla = $encabezado;

	$reporte = $estilo . $tabla;
	echo $reporte;

?>