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

    //librerias
    require_once('../../../../ent/UnidadResponsable.php');
    require_once('../../../../lib/compararCadenas.php');

    $mesDesde = isset( $_POST['comboMesDesde'] ) ? $_POST['comboMesDesde'] : -1 ;//Informacion de acuerdo al mes
    $mesHasta = isset( $_POST['comboMesHasta'] ) ? $_POST['comboMesHasta'] : -1 ;//Informacion de acuerdo al mes
    $tipoReporte = isset( $_POST['tipoReporte'] ) ? $_POST['tipoReporte'] : 1;
    $chkEnMiles = isset( $_POST['ckmiles'] ) ? 1000 : 1;
	$grupos = isset( $_POST['grupos'] ) ? $_POST['grupos'] : "";
    $excluir = isset( $_POST['excluir'] ) ? $_POST['excluir'] : "";
	$ceros = (isset( $_POST['ceros'] ) && $_POST['ceros']!="") ? $_POST['ceros'] : 0;
    $incCta = isset( $_POST['incCta'] ) ?  $_POST['incCta'] : "";
	$ue = isset( $_POST['ue'] ) ?  $_POST['ue'] : "";
	$gpo_id = $_SESSION[_S]['clasAdm-gpo'];
	$ur_id = $_SESSION[_S]['clasAdm-ur'];
    $numUe = isset($_POST['numUe']) ? $_POST['numUe'] : "";

	/*$nivel[1] = isset( $_POST['checkNivel1'] ) ? $_POST['checkNivel1'] : -1;
	$nivel[2] = isset( $_POST['checkNivel2'] ) ? $_POST['checkNivel2'] : -1;
	$nivel[3] = isset( $_POST['checkNivel3'] ) ? $_POST['checkNivel3'] : -1;
	$nivel[4] = isset( $_POST['checkNivel4'] ) ? $_POST['checkNivel4'] : -1;
	$nivel[5] = isset( $_POST['checkNivel5'] ) ? $_POST['checkNivel5'] : -1;*/

    //de alguna manera aki se le asignara 'ctrl2012.' o 'ctrl2013.', segun sea el caso. para el 2014 se deja en blanco
    $ejercicio = "";
    $encabLin6 = "";
     $encabLin1 =  "Secretaría de Finanzas del Gobierno del Estado de Oaxaca";
	 $encabLin3 = "Relación de Bienes Muebles que Componen el Patrimonio";

    if( $ejercicio == "" ){
        $ur = new UnidadResponsable( );
        $ur->setGpo_id( $_SESSION[_S]['clasAdm-gpo'] );
        $ur->setUr_id( $_SESSION[_S]['clasAdm-ur'] );
        $ur->select();
    }else{
        //obtenemos los datos de la UR del ejercicio solicitado
    }

    if( $_SESSION[_S]['nvj_id'] > 2 ){
        switch ( $tipoReporte ){
            case 2:   // Todas las URs de la carga de trabajo
                 $encabLin2 = "Dirección de Contabilidad Gubernamental";
                break;
            case 3:   // Determinar qué incluir y qué excluir
				 $encabLin2 = "Dirección de Contabilidad Gubernamental";
				$encabLin6 = isset( $_POST['nombreEspecial'] ) ? $_POST['nombreEspecial'] : "ERROR DE PROGRAMA. NO EXISTE ENCABEZADO";
			break;
            default:
                if( $ejercicio == "" )
                    $encabLin2 = "Dirección de Contabilidad Gubernamental: ".$ur->getClave()." - ".$ur->getNombre();
                else
                    $encabLin2 = "Dirección de Contabilidad Gubernamental: ".$ur['gpo_id'].$ur['ur_id']." - ".$ur['ur_nombre'];
                break;
        }
    }else{

        if( $ejercicio == "" )
            $encabLin2 = "UR: ".$ur->getClave()." - ".$ur->getNombre();
        else
            $encabLin2 = "UR: ".$ur['gpo_id'].$ur['ur_id']." - ".$ur['ur_nombre'];

    }

	$texto2='';
	if($ue!='' && $numUe>1){
		$ejecutora = new AccesoDatos();
		$ejecutora->conectar();
		$ejecutora->setSql('
			SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = '.$ur->getGpo_id().' AND ur_id = '.$ur->getUr_id().' AND ue_id = '.$ue.';
		');
		$ejecutora->consultar();
		$ejecutora->desconectar();
		$ue_id = $ejecutora->extraer();
		$texto2 = 'UE: '.$ue_id['ue_id'].' - '.$ue_id['ue_nombre'].'';
	}
	$encabLin7='';
	if($ue!='')
		$encabLin7 = $texto2;

	$mesDesde = ( isset( $_POST['comboMesDesde'] ) && $_POST['comboMesDesde'] != "" ) ? $_POST['comboMesDesde'] : '';

	$sqlMes =' select msc_nombre, msc_dias from cat_MesContable where msc_id='.$mesDesde.';';
	$con = new AccesoDatos();
	$con->conectar();
	$con->setSql($sqlMes);
	$con->consultar();
	$con->desconectar();
	$rowMes = $con->extraer();

	$mesHasta = ( isset( $_POST['comboMesHasta'] ) && $_POST['comboMesHasta'] != "" ) ? $_POST['comboMesHasta'] : '';

	$sqlMes2 =' select msc_nombre, msc_dias from cat_MesContable where msc_id='.$mesHasta.';';
	$con2 = new AccesoDatos();
	$con2->conectar();
	$con2->setSql($sqlMes2);
	$con2->consultar();
	$con2->desconectar();
	$rowMes2 = $con2->extraer();

	if($mesDesde==0 and $mesHasta==0){
		 $encabLin4 = 'Del mes de ' .$rowMes['msc_nombre']. ' ' . substr( $_SESSION[_S]['ejercicio'], 0, 4)." y ".substr( $_SESSION[_S]['ejercicio']-1, 0, 4 ) ;
	}elseif( $mesHasta == $mesDesde ){
		 $encabLin4 = 'Del 1° al '.$rowMes['msc_dias'].' de ' .$rowMes['msc_nombre']. ' de ' . substr( $_SESSION[_S]['ejercicio'], 0, 4) ;
	}else{
		if($mesDesde ==0)
			$encabLin4 = 'Del mes de ' .$rowMes['msc_nombre']. ' al '.$rowMes2['msc_dias'].' de '.$rowMes2['msc_nombre'].' de ' . substr( $_SESSION[_S]['ejercicio'], 0, 4) ;
		else
			$encabLin4 = 'Del 1° de ' .$rowMes['msc_nombre']. ' al '.$rowMes2['msc_dias'].' de '.$rowMes2['msc_nombre'].' de ' . substr( $_SESSION[_S]['ejercicio'], 0, 4) ;
	}

    if( $chkEnMiles == 1000 )
        $encabLin5 = "(en miles de pesos)";
	else
        $encabLin5 = "(pesos)";


	$ruta= u8d("BienesMuebles.xls");
	header('Content-type: application/vnd.ms-excel');
	header("Content-Disposition: attachment; filename=".$ruta);
	header("Pragma: no-cache");
	header("Expires: 0");

	$num = 10;

	$html = '<table align="center" width="100%">
				<tr>
					<td colspan='.$num.' align="CENTER"><b>'.u8d($encabLin1).'</b></td>
				</tr>
				<tr>
					 <td colspan='.$num.' align="CENTER" ><b>'.u8d($encabLin2).'</td>
				</tr>
				'.($encabLin7!='' ? '
					<tr>
						 <td colspan='.$num.' align="CENTER" ><b>'.u8d($encabLin7).'</td>
					</tr> '
				:"").'
				<tr>
					<td colspan='.$num.' align="CENTER"><b>'.u8d($encabLin3).'</td>
				</tr>
				<tr>
					<td colspan='.$num.' align="CENTER" ><b>'.u8d($encabLin4).'</td>
				</tr>
				<tr>
					<td colspan='.$num.' align="CENTER" ><b>'.u8d($encabLin5).'</td>
				</tr>
				<tr>
					<td colspan='.$num.' align="CENTER" ><b>'.u8d($encabLin6).'</td>
			</table>
	<table align="center" width="100%" border=0>';

	echo $html;

	$otroWhere = "";
	if ( $tipoReporte == 3 && $_SESSION[_S]['nvj_id'] >= 5 ){
		// Si limitaron por grupo o excluyeron alguna UR
		if ( $grupos != "" ){
			$otroWhere .= "  WHERE gpo_id IN ( " . rtrim( $grupos ) . " ) AND ";

			// Si excluyeron a alguna UR
			if ( $excluir != "" )
				$otroWhere .= " ".($grupos != "" ? "": " WHERE ")." CONCAT( gpo_id,ur_id ) NOT IN ( " . rtrim( $excluir ) . " ) AND ";
		// Si no usaron las campos especiales, es un where estandar
		}
	}

	$conexion = new AccesoDatos();
	$conexion->conectar();
	$conexion->setSql("
		SELECT ger_id, cgp_id, rub_id,
		cgcu_id, cgsc_id, cgsc_nombre,
		SUM( pdx_debe ) - SUM( pdx_haber ) AS pdx_importe
		FROM cat_Genero
		INNER JOIN cat_CgGrupo USING( ger_id )
		INNER JOIN cat_Rubro USING( ger_id, cgp_id )
		INNER JOIN cat_CgCuenta USING( ger_id, cgp_id, rub_id )
		INNER JOIN cat_CgSubcuenta USING( ger_id, cgp_id, rub_id, cgcu_id )
		INNER JOIN cg_PolizaDetalleAuxiliar USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
		INNER JOIN cg_PolizaMaestro USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
		INNER JOIN cg_PlanCuentas USING( cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id )
		".( $tipoReporte == 2 ? ( $_SESSION[_S]['nvj_id'] < 6 ? " INNER JOIN cat_CargaTrabajo t USING(gpo_id,ur_id)
		":"")." WHERE ":(( $tipoReporte == 3) ? $otroWhere :"
		WHERE gpo_id = $gpo_id AND ur_id = $ur_id ".( $ue!='' ? " AND ue_id = ".$ue."":"")."
		AND " ))."
		ger_id = 1 AND cgp_id = 2 AND rub_id = 4
		AND epl_id NOT IN ( 'C', 'H' )
		".( $mesHasta < 0 ? " AND msc_id < $mesDesde":( $mesDesde == $mesHasta ? " AND msc_id = $mesDesde":" AND ( msc_id BETWEEN $mesDesde AND $mesHasta )" ) )."
		".( $tipoReporte == 2 && $_SESSION[_S]['nvj_id'] < 6 ? "AND t.usr_id = ".$_SESSION[_S]['s24-id']:"")."
		GROUP BY ger_id, cgp_id, rub_id, cgcu_id, cgsc_id
		;
	");
	$conexion->consultar();
	$conexion->desconectar();

	echo'<tr>';
		echo'<td align="center" style="font-size: 12.5; background-color: #807853; color: white;"><b>'.u8d('CÓDIGO').'</b></td>';
		echo'<td colspan=8 align="center" style="font-size: 12.5; background-color: #807853; color: white;"><b>'.u8d('DESCRIPCIÓN DEL BIEN').'</b></td>';
		echo'<td align="center" style="font-size: 12.5; background-color: #807853; color: white;"><b>Valor en Libros</b></td>';
	echo'</tr>';

	$total = 0;
	while($row = $conexion->extraer()){
		$total+=$row['pdx_importe'];
		$cuenta = $row['ger_id'].$row['cgp_id'].$row['rub_id'].$row['cgcu_id'].$row['cgsc_id'];
		$importe = $row['pdx_importe'];
		$importe =  (isset($importe) ? number_format(round($importe/$chkEnMiles,0),0):"");

		echo'<tr>';
			echo'<td align="center" style="font-size: 10.5;"><b>'.$cuenta.'</b></td>';
			echo'<td colspan=8 align="left" style="font-size: 10.5;">'.u8d($row['cgsc_nombre']).'</td>';
			echo'<td align="right" style="font-size: 10.5;">'."$".$importe.'</td>';
		echo'</tr>';

	}

	echo'<tr>';
		echo'<td align="center" style="font-size: 10.5;"></td>';
		echo'<td colspan=8 align="left" style="font-size: 10.5;">TOTAL</td>';
		echo'<td align="right" style="font-size: 10.5;">'."$".number_format(round($total/$chkEnMiles,0),0).'</td>';
	echo'</tr>';

	echo'</table>';

// Fin del código fuente
