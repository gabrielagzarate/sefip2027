<?php

if (!function_exists('u8d')) {
    function u8d($value)
    {
        return mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
    }
}

session_start();
ini_set('memory_limit','1G');
set_time_limit(0);
require_once('../../../../lib/AccesoDatos.php');

    if (!isset($_SESSION[_S]['s24-id'])){
		echo "VERBOTEN!!!!";
        die();
	}

    //echo 'TOA: ' . $_POST['toa_id']; die;
    $wh_toa = ' toa_id = ' . $_POST['toa_id'];

    if ($_POST['gpo_id'] != 'X' &&  (isset($_POST['ur_id']) && $_POST['ur_id'] != 'X')){
        $wh_gpo = ' and gpo_id = '  . $_POST['gpo_id'] . ' and ur_id = ' . substr($_POST['ur_id'],1,2) ;
    }
    else if ($_POST['gpo_id'] == 'X'){
        $wh_gpo = '';
    }
    else if($_POST['gpo_id'] != 'X' && (isset($_POST['ur_id']) && $_POST['ur_id'] == 'X')){
        $wh_gpo = ' and gpo_id= ' . $_POST['gpo_id'];
    }

    //echo $wh_gpo ;

if (!isset($_POST['html'])) {
    $ruta = u8d("RepGastoOpePartidas.xls");
    if ($_POST['toa_id'] == 2) {
        $ruta = u8d("RepInvPublicaPartidas.xls");
    }
    header('Content-type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=" . $ruta);
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
    #PRESUPUESTOS & POA OBEJETO DE GASTO
    CvePresupuestal, cap_clave, Capitulo, cto_clave, Concepto,
    pg_clave, PartidaGenerica, pe_clave, PartidaEspecifica,idp_id,idp_nombre,
    #PRESUPUESTO
    Aprobado, Modificado, Comprometido, PorComprometer, Devengado, Ejercido, PorEjercer, Pagado,PorPagar,Calendarizado,
    Ene, Feb, Mar, Abr, May, Jun, Jul, Ago, Sep, Voc, Nov, Dic
    FROM
        ( #Obra actividad
            SELECT gpo_id,ur_id,ue_id,epg_id,prg_id,prg_nombre,spr_id,spr_nombre,py_id,py_nombre,oa_id,oa_nombre
            FROM cat_Programa
            INNER JOIN cat_Subprograma USING (gpo_id,ur_id,prg_id )
            INNER JOIN cat_Proyecto USING (gpo_id,ur_id,prg_id,spr_id )
            INNER JOIN cat_ObraActividad oa USING ( gpo_id,ur_id,prg_id,spr_id,py_id )
            INNER JOIN enc_Programatico USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)# tabla para unir las claves de financiamiento
            WHERE   ' . $wh_toa . ' #TipoObraActivadad 1 = gastos operativos ; 2 = inversion
                    ' . $wh_gpo . ' 
        )oa
    INNER JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
    INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id )
    # financiamiento
    INNER JOIN poa_OrigenRecurso USING(gpo_id,ur_id,epg_id)
    INNER JOIN enc_ClaveFinanciamiento USING(ecf_id )
    INNER JOIN sinpres.cat_ClaveFinanciamiento2022 USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
    # Presupuesto Adecuaciones requeridas ticket 219237
    INNER JOIN (
            SELECT
                gpo_id,ur_id,epg_id,ecf_id,
                pre_claveCompleta CvePresupuestal, cap_clave, cap_nombre Capitulo, cto_clave, cto_nombre Concepto,
                pg_clave, pg_nombre PartidaGenerica, pe_clave, pe_nombre PartidaEspecifica,idp_id,idp_nombre,
                SUM(pre_aprobado) Aprobado,
                SUM(pre_vigente) Modificado,
                SUM(pre_comprometido) Comprometido,
                SUM(pre_vigente) - SUM(pre_comprometido) AS PorComprometer,
                SUM(pre_devengado) Devengado,
                SUM(pre_ejercido) Ejercido,
                SUM(pre_vigente) - SUM(pre_ejercido) PorEjercer,
                SUM(pre_pagado) Pagado,
                SUM(pre_vigente) - SUM(pre_pagado) as PorPagar,
                (CASE MONTH(NOW())
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
                    END) Calendarizado,
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
                INNER JOIN cat_PartidaEspecifica USING(pg_id, pe_id)
                INNER JOIN cat_IdentificadorPartida USING(idp_id)
                INNER JOIN cat_PartidaGenerica USING(pg_id)
                INNER JOIN cat_Concepto USING(cap_id, cto_id)
                INNER JOIN cat_Capitulo USING(cap_id)
                WHERE gpo_id is not null
                    ' . $wh_gpo . ' 
            GROUP BY gpo_id,ur_id,epg_id,pg_id,pe_id,ecf_id,pre_claveCompleta
        )z USING(gpo_id , ur_id , epg_id,ecf_id);
	';
 
	$conexion->setSql($sql);
	//echo "<pre>".$conexion->getSql()."</pre>";die();

	$conexion->consultar();
	$conexion->desconectar();	
            
	$cuerpo = '<table align="center" width="100%" border="0" cellspacing="0" cellpadding="1" class="tabla">';			
		$cuerpo.='<tr>';
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CVE_UR</th>'; #1
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>UNIDAD RESPONSABLE</th>'; #2
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CVE_UE</th>'; #3
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>UNIDAD EJECUTORA</th>'; #4

		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CVE_PRG</th>';#3
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>PROGRAMA</th>';#5
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CVE_SPR</th>';#6
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>SUBPROGRAMA</th>';#7
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CVE_PROYECTO</th>';#8
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>PROYECTO</th>';#9
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CVE_OBRA/ACTIVIDAD</th>';#10
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>NOMBRE OBRA/ACTIVIDAD</b></th>';#11

        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CLAVE DE FINANCIAMIENTO</b></th>';#12
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>NOMBRE FUENTE DE FINANCIAMIENTO</b></th>';#13
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>EJERCICIO</b></th>';#13

        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CLAVE_PRESUPUESTAL</b></th>';#14
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CVE_CAPITULO</b></th>';#15
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CAPITULO</b></th>';#16
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CVE CONCEPTO</b></th>';#17
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CONCEPTO</b></th>';#18
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CVE PARTIDA_GENERICA</b></th>';#19
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>PARTIDA_GENERICA</b></th>';#20
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CVE PARTIDA_ESPECIFICA</b></th>';#21
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>PARTIDA_ESPECIFICA</b></th>';#22
        
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CLAVE_IDENTIFICADOR_PARTIDA</b></th>';
		$cuerpo.='<th align="center" class="encabezadoCuerpo"><b>IDENTIFICADOR_PARTIDA</b></th>';


        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>APROBADO</b></th>';#16
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>MODIFICADO</b></th>';#17
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>COMPROMETIDO</b></th>';#18
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>POR COMPROMETER</b></th>';#19
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>DEVENGADO</b></th>';#20
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>EJERCIDO</b></th>';#21
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>POR EJERCER</b></th>';#22
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>PAGADO</b></th>';#23
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>POR PAGAR</b></th>';#24
        $cuerpo.='<th align="center" class="encabezadoCuerpo"><b>CALENDARIZADO</b></th>';#24

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
					
			$cuerpo.='<td align="left" class="sEstilo"><b>'.u8d($row['CvePresupuestal']).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.u8d($row['cap_clave']).'</b></td>';
			$cuerpo.='<td align="left" class="sEstilo"><b>'.u8d($row['Capitulo']).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.u8d($row['cto_clave']).'</b></td>';
			$cuerpo.='<td align="left" class="sEstilo"><b>'.u8d($row['Concepto']).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.u8d($row['pg_clave']).'</b></td>';
			$cuerpo.='<td align="left" class="sEstilo"><b>'.u8d($row['PartidaGenerica']).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.u8d($row['pe_clave']).'</b></td>';
			$cuerpo.='<td align="left" class="sEstilo"><b>'.u8d($row['PartidaEspecifica']).'</b></td>';

            $cuerpo.='<td align="center" class="sEstilo"><b>'.u8d($row['idp_id']).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.u8d($row['idp_nombre']).'</b></td>';

            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Aprobado'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Modificado'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Comprometido'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['PorComprometer'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Devengado'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Ejercido'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['PorEjercer'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Pagado'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['PorPagar'],2).'</b></td>';
            $cuerpo.='<td align="center" class="sEstilo"><b>'.number_format($row['Calendarizado'],2).'</b></td>';

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
$numcolumnas = 22;
$numcolizq = ($numcolumnas - ($numcolumnas - 3) );
$numcolder = ($numcolumnas - ($numcolumnas - 3) );
$numcolcentral = $numcolumnas - ($numcolizq + $numcolder);

$encabezado='<table align="center" width="200%" border = "0" >';
	$encabezado.='<tr>';
		$encabezado.='<td colspan="'.$numcolizq.'" align="left" style="font-size: 10px;"><b>Fecha '.$hoy.' </b></td>';	
		$encabezado.='<td colspan="'.$numcolcentral.'" align="CENTER"><b>GOBIERNO DEL ESTADO DE OAXACA</b></td>';
		$encabezado.='<td colspan="'.$numcolder.'" rowspan="4" align="right">
          <!--  <img type="image" name="logo" src="https://sefip.finanzasoaxaca.gob.mx/img/logosefin-color-fblanco.png" height="90" width="200" />-->
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
		<td colspan="'.$numcolcentral.'" align="CENTER" ><b> REPORTE GASTO OPERATIVO PARTIDAS  </b></td>
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