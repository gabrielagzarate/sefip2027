<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__).'/../../lib/validaFecha.php';

function pintaPagadoContable( $gpo_id, $ur_id, $bdo_id, $parametroExtra = array(),$ejercicioActual = 2022 ){
    //echo $gpo_id.$ur_id.$bdo_id;die();
    //echo "<pre>";print_r($parametroExtra);echo "</pre>";die();//*
    if( $gpo_id == '' || $ur_id == '' || $bdo_id == '' )
        return false;
    
    $clc_id = ( isset( $parametroExtra['clc_id'] ) ? $parametroExtra['clc_id']:'' );
    $ejercicio = ( isset( $parametroExtra['ejercicio'] ) ? $parametroExtra['ejercicio']:'' );
    $agrupadorSql = ( isset( $parametroExtra['agrupador'] ) ? "AND p.bdp_agrupador = '".$parametroExtra['agrupador']."' ":"AND p.bdo_id = $bdo_id " );
    
    $html = '';
    $return = new stdClass();

    $sql = "
        SELECT p.gpo_id, p.ur_id, p.clc_id, p.afe_id, p.bdo_id, 
            p.bdp_poliza, CASE p.bdp_poliza WHEN 1 THEN 'ORDEN Y GASTO' WHEN 2 THEN 'SOLO ORDEN' WHEN 3 THEN 'NO GENERAR PÓLIZAS' WHEN 4 THEN 'SOLO GASTO' END AS bdp_polizaNombre, 
            p.pot_id, pot_nombre, 
            p.tdf_id, p.bdp_referencia, p.bdp_concepto, p.bdp_fechaAutorizado, p.bdp_fechaCancelado, 
            p.bdp_estado, p.bdp_percepcionPresupuestal, p.bdp_percepcionContable, p.bdp_retencionContable, 
            p.bdp_percepcionPresupuestal + p.bdp_percepcionContable - p.bdp_retencionContable AS importe, 
            i.iba_id, i.iba_nombre, b.bur_cuentaBancaria, b.bur_alias, b.bur_beneficiario 
        FROM ".($ejercicio<$ejercicioActual?" ctrlAnterior.cg_BancoDocumentoPagadoContable p ":"ctrl".$ejercicio.".cg_BancoDocumentoPagadoContable p")."
            INNER JOIN ctrl".$ejercicio.".scp_Clc c USING( gpo_id, ur_id, clc_id ) 
            INNER JOIN ctrl".$ejercicio.".cat_BancosUr b USING( bur_id ) 
            INNER JOIN ctrl".$ejercicio.".cat_InstitucionesBancarias i USING( iba_id ) 
            INNER JOIN ".($ejercicio<$ejercicioActual?" ctrlAnterior.cat_TipoPoliza ":"ctrl".$ejercicio.".cat_TipoPoliza ")."USING( pot_id )
        WHERE p.gpo_id = $gpo_id AND p.ur_id = $ur_id ".($ejercicio<$ejercicioActual ? " AND p.ejr_id_autorizado = $ejercicioActual ":"")."
            $agrupadorSql 
    ";
    //echo '<pre>';echo $sql;echo '</pre>';die();
    
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar();
    $dataRow = $conexion->extraer();
    
    $date = new stdClass;
    $date->gpo_id = $dataRow['gpo_id'];
    $date->ur_id = $dataRow['ur_id'];
    $date->fecha = $dataRow['bdp_fechaAutorizado'];
    $fechaAutorizado = new stdClass;
    $fechaAutorizado = validaFecha($date);
    $date = new stdClass;
    $date->gpo_id = $dataRow['gpo_id'];
    $date->ur_id = $dataRow['ur_id'];
    $date->fecha = $dataRow['bdp_fechaCancelado'];
    $fechaCancelado = new stdClass;
    $fechaCancelado = validaFecha($date);
    
    $html .= '
        <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
            <table class="table table-bordered danger" style="margin-bottom:0px !important;">
                <tr class="warning">
                    <th>Tipo de póliza</th>
                    <th>Referencia</th>
                    <th style="width:120px;">Importe</th>
                    <th>Concepto</th>
                    <th>Fecha Pago</th>
                    <th>Fecha Cancelado</th>
                    <th>Seleccion de póliza</th>
                    <th rowspan="2" style="vertical-align: middle;text-align: center;">
                        <span id="btn_pag_'.$dataRow['bdo_id'].'" dest="clc_'.$dataRow['bdo_id'].'" class="clc_detail_btn  pintaPagado"> Detalle</span>
						<script type="text/javascript" languaje="javascript">
                            $("#btn_pag_'.$dataRow['bdo_id'].'").click(function(){
								$(".clc_detail").css("height","0px");
                                h = $("#"+$(this).attr("dest")).outerHeight();
                                if( h > 0 ){
									$("#" + $(this).attr("dest")).css("height","0px")
									$("#ministrado").html("");
								}else{
                                    h2 = $("#table_"+$(this).attr("dest")).outerHeight();
                                    h2 = parseInt(h2);
									$("#" + $(this).attr("dest")).css("height",h2);
									$("#ministrado").html("Resaltado donde esta el documento actual en el registro de Pago");
								}
							});
						</script>
                    </th>
                </tr>
                <tr>
                    <td>'.$dataRow['pot_id'].' - '.$dataRow['pot_nombre'].'</td>	
                    <td>'.$dataRow['tdf_id'].' - '.$dataRow['bdp_referencia'].'</td>	
                    <td align="right"> importePagado </td>
                    <td><div class="div_ajustable" style="overflow:hidden">'.$dataRow['bdp_concepto'].'</div></td>	
                    <td>'.( $fechaAutorizado->valido ? $fechaAutorizado->fecha:'' ).'</td>
                    <td>'.( $fechaCancelado->valido ? $fechaCancelado->fecha:'' ).'</td>
                    <td>'.$dataRow['bdp_poliza'].' - '.$dataRow['bdp_polizaNombre'].'</td>	
                </tr>
                <tr>
                    <td class="warning">Banco</td>
                    <td>'.$dataRow['iba_id'].' - '.$dataRow['iba_nombre'].'</td>
                    <td class="warning">Cuenta</td>
                    <td colspan="2">'.$dataRow['bur_cuentaBancaria'].' - '.$dataRow['bur_alias'].'</td>
                    <td class="warning">Beneficiario</td>
                    <td>'.$dataRow['bur_beneficiario'].'</td>
                </tr>
            </table>
            <div id="clc_'.$dataRow['bdo_id'].'"  class="clc_detail">
                <table id="table_clc_'.$dataRow['bdo_id'].'" class="table table-bordered">
                    <tr class="warning">
                        <th>CLC</th>
                        <th>Afectacion</th>
                        <th>Percepción Presupuestal</th>
                        <th>Percepción Contable</th>
                        <th>Retención Contable</th>
                    </tr>
    ';
                        //<td align="right"><span style="float:left">$</span>'.number_format($dataRow['importe'],2).'</td>
    $cell = 0;
    $style = '';
    if($cell == 0 ){ 
        $cell = 1;
        $style = '';
    }else{ 
        $cell = 0;
        $style = "  background-color:#EAEAEA;"; 
    }
    
    if( $dataRow['clc_id'] == $clc_id )
        $html.='<tr style="border:1px solid #090;" class="success" >';
    else
        $html.='<tr>';
    
    $html.= '	
                        <td>'.$dataRow['clc_id'].'</td>
                        <td>'.$dataRow['afe_id'].'</td>
                        <td align="right"><span style="float:left">$</span>'.number_format($dataRow['bdp_percepcionPresupuestal'],2).'</td>
                        <td align="right"><span style="float:left">$</span>'.number_format($dataRow['bdp_percepcionContable'],2).'</td>
                        <td align="right"><span style="float:left">$</span>'.number_format($dataRow['bdp_retencionContable'],2).'</td>
                    </tr>
    ';
    
    $importePagado = ( $dataRow['bdp_percepcionPresupuestal'] + $dataRow['bdp_percepcionContable'] - $dataRow['bdp_retencionContable'] );
    
    while( $dataRow = $conexion->extraer() ){
        
        if( $cell == 0 ){ $cell =1;  $style ="";}else{ $cell = 0; $style="  background-color:#EAEAEA;"; }
        
        if( $dataRow['clc_id'] == $clc_id )
            $html.='<tr style="border:1px solid #090;" class="success" >';
        else
            $html.='<tr style="'.$style.'">';
        
        $html.= '
                        <td>'.$dataRow['clc_id'].'</td>
                        <td>'.$dataRow['afe_id'].'</td>
                        <td align="right"><span style="float:left">$</span>'.number_format($dataRow['bdp_percepcionPresupuestal'],2).'</td>
                        <td align="right"><span style="float:left">$</span>'.number_format($dataRow['bdp_percepcionContable'],2).'</td>
                        <td align="right"><span style="float:left">$</span>'.number_format($dataRow['bdp_retencionContable'],2).'</td>
                    </tr>
        ';
        
        $importePagado += ( $dataRow['bdp_percepcionPresupuestal'] + $dataRow['bdp_percepcionContable'] - $dataRow['bdp_retencionContable'] );
    }
    $html .= '
                </table>
            </div>
        </fieldset></br>
    ';
    $conexion->desconectar();
    unset($conexion);
    
    $return->html = str_replace( "importePagado", '<span style="float:left">$</span>'.number_format($importePagado,2), $html );
    //str_repla
    $return->valido = true;
    
    return $return;
}
