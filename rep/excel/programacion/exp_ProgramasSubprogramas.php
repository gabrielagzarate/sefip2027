<?php

if (!function_exists('u8d')) {
    function u8d($value)
    {
        return mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
    }
}

session_start();
	
	ini_set('memory_limit','200M');
    ini_set('max_execution_time',300);

	set_time_limit(0);
	
	$desdeMenu = false;
	require_once dirname(__FILE__)."/../../../lib/constantes.php";	
	if (!isset($_SESSION[_S])) { session_start(); $desdeMenu = true;}
	
    //librerias
	require_once dirname(__FILE__).'/../../../lib/WebResponse.php';
	
	$ruta= u8d("Programas.xls");
	header('Content-type: application/vnd.ms-excel');
	header("Content-Disposition: attachment; filename=".$ruta);
	header("Pragma: no-cache");
	header("Expires: 0");
	 
    $encabLin1 	= "SECRETARIA DE FINANZAS. EJERCICIO ".APP_EJERCICIO;
	$encabLin2 	= "LISTADO DE PROGRAMAS Y SUBPROGRAMAS" ;
	$filtro = (isset($_POST['filtro']) ? $_POST['filtro']:"");
   	
	echo'<table align="center" width="100%">';
		echo'<tr>';
			echo'<td colspan=8 align="CENTER"><b>'.u8d($encabLin1).'</b></td>';
		echo'</tr>';
		echo'<tr>';
			 echo'<td colspan=8 align="CENTER" ><b>'.u8d($encabLin2).'</b></td>';
		echo'</tr>';
		echo'<tr><td colspan=5></td></tr>';
	echo'</table>';	
	
	$where = '';
	if ( $filtro != "" ) {
		$where = 'WHERE ( ( prgs_id LIKE "%' . $filtro . '%" ) OR ( prgs_nombre LIKE "%' . $filtro . '%" ) OR ( epr_nombre LIKE "%' . $filtro . '%" ) )';
	}
	if ($_SESSION[_S]['nvj_id'] < 3) {
		$where = ' WHERE gpo_id = '.$_SESSION[_S]['gpo_id'].' AND ur_id = '.$_SESSION[_S]['ur_id'];
	}
	echo'<table align="center" width="100%" border=1>';
		echo'<tr>';
			echo'<td align="center" style="font-size: 12.5; background-color: #007989; color: white;"><b>ID_UR</b></td>';
			echo'<td align="center" style="font-size: 12.5; background-color: #007989; color: white;"><b>ID_PRG</b></td>';
			echo'<td align="center" style="font-size: 12.5; background-color: #007989; color: white;"><b>'.u8d('DENOMINACIÓN_PROGRAMA').'</b></td>';
			echo'<td align="center" style="font-size: 12.5; background-color: #007989; color: white;"><b>ID_SPR</b></td>';
			echo'<td align="center" style="font-size: 12.5; background-color: #007989; color: white;"><b>'.u8d('DENOMINACIÓN SUBPROGRAMA').'</b></td>';
			echo'<td align="center" style="font-size: 12.5; background-color: #007989; color: white;"><b>PF</b></td>';
			echo'<td align="center" style="font-size: 12.5; background-color: #007989; color: white;"><b>PF_NOMBRE</b></td>';
			echo'<td align="center" style="font-size: 12.5; background-color: #007989; color: white;"><b>ESTADO PROGRAMA</b></td>';
		echo'</tr>';
	
	$p = new AccesoDatos();
	$p->conectar();
	$p->setSql('
		SELECT ur_clave,prgs_id,prgs_nombre,sprs_id,sprs_nombre,pdf_id,pdf_plural,epr_nombre
		FROM cat_Programas
		INNER JOIN cat_Subprogramas USING(prgs_id)
		INNER JOIN cat_ProductoFinal USING(pdf_id)
		INNER JOIN cat_EstadoPrograma USING(epr_id)
		LEFT JOIN enc_UrSubprogramas USING(prgs_id,sprs_id)
		LEFT JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
		'.$where.'
		GROUP BY gpo_id,ur_id,prgs_id,sprs_id
		ORDER BY prgs_id,sprs_id;
	');
	$p->consultar();
	$p->desconectar();

	while($datos = $p->extraer()){
		echo'<tr>';
			echo'<td align="center" style="font-size: 12.5; background-color: #FFFFFF; color: black;">'.$datos['ur_clave'].'</td>';
			echo'<td align="center" style="font-size: 12.5; background-color: #FFFFFF; color: black;">'.$datos['prgs_id'].'</td>';
			echo'<td align="left" style="font-size: 12.5; background-color: #FFFFFF; color: black;">'.u8d($datos['prgs_nombre']).'</td>';
			echo'<td align="center" style="font-size: 12.5; background-color: #FFFFFF; color: black;">'.$datos['sprs_id'].'</td>';
			echo'<td align="left" style="font-size: 12.5; background-color: #FFFFFF; color: black;">'.u8d($datos['sprs_nombre']).'</td>';
			echo'<td align="center" style="font-size: 12.5; background-color: #FFFFFF; color: black;">'.$datos['pdf_id'].'</td>';
			echo'<td align="left" style="font-size: 12.5; background-color: #FFFFFF; color: black;">'.u8d($datos['pdf_plural']).'</td>';
			echo'<td align="left" style="font-size: 12.5; background-color: #FFFFFF; color: black;">'.u8d($datos['epr_nombre']).'</td>';
		echo'</tr>';
	}
	echo'</table>';