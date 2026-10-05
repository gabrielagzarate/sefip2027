<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function pintaRectificacion( $gpo_id, $ur_id, $rct_id, $parametroExtra = array() ){

    if( $gpo_id == '' || $ur_id == '' || $rct_id == '' )
        return false;
    
    $ejercicio = ( isset( $parametroExtra['ejercicio'] ) ? $parametroExtra['ejercicio']:'' );
    
    $html = '';
    $return = new stdClass();

    $sql = "
        SELECT m.gpo_id, m.ur_id, m.rct_id, m.clc_id, v.afe_id, 
            m.rct_justificacion, m.rct_reintegro, DATE( m.rct_fechaAutorizacion ) AS rct_fechaAutorizacion, 
            m.rct_numeroComprobante, m.rct_fechaComprobante, 
            v.bdr_percepcionPresupuestal, v.bdr_percepcionContable, v.bdr_retencionContable 
        FROM ctrl".$ejercicio.".scp_MaestroRectificacion m 
            INNER JOIN ctrl".$ejercicio.".scp_BancoDocumentoRectificado v USING( gpo_id, ur_id, rct_id ) 
        WHERE m.gpo_id = ? AND m.ur_id = ?
            AND m.rct_id = ? 
    ";
    $parametros = [$gpo_id, $ur_id, $rct_id];
    
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($parametros);
    $dataRow = $conexion->extraer();
    
    $date = new stdClass;
    $date->gpo_id = $gpo_id;
    $date->ur_id = $ur_id;
    $date->fecha = $dataRow['rct_fechaAutorizacion'];
    $fechaAutorizacion = new stdClass;
    $fechaAutorizacion = validaFecha($date);
    
    $date = new stdClass;
    $date->gpo_id = $gpo_id;
    $date->ur_id = $ur_id;
    $date->fecha = $dataRow['rct_fechaComprobante'];
    $fechaPago = new stdClass;
    $fechaPago = validaFecha($date);
    
    $html .= '
        <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
            <table class="table table-bordered danger" style="margin-bottom:0px !important;">
                <tr class="warning">
                    <th>UR-RCT</th>
                    <th>Tipo</th>
                    <th style="width:120px;">Percepciones</th>
                    <th style="width:120px;">Retenciones</th>
                    <th>Autorización</th>
                    <th rowspan="2" style="vertical-align: middle;text-align: center;">
                        <span id="btn_clc_'.$dataRow['rct_id'].'" dest="clc_'.$dataRow['rct_id'].'" class="clc_detail_btn"> Detalle</span>
						<script type="text/javascript" languaje="javascript">
							$("#btn_clc_'.$dataRow['rct_id'].'").click(function(){
								$(".clc_detail").css("height","0px");
								if(document.getElementById($(this).attr("dest")).offsetHeight > 0 ){
									$("#" + $(this).attr("dest")).css("height","0px")
									$("#menclc").html("");
								}else{
									$("#" + $(this).attr("dest")).css("height",document.getElementById("table_"+$(this).attr("dest")).offsetHeight);
									$("#menclc").html("Resaltado donde esta el documento actual");
								}
							});
						</script>
                    </th>
                </tr>
                <tr>
                    <td><div class="div_ajustable" style="overflow:hidden">'.$dataRow['gpo_id'].$dataRow['ur_id'].' - '.$dataRow['rct_id'].'</div></td>	
                    <td><div class="div_ajustable" style="overflow:hidden">'.$dataRow['rct_reintegro']." - ".( $dataRow['rct_reintegro'] == 1 ? 'CON REINTEGRO':'SIN REINTEGRO' ).'</div></td>	
                    <td align="right"><span style="float:left">$</span>'.number_format($dataRow['bdr_percepcionPresupuestal'] + $dataRow['bdr_percepcionContable'],2).'</td>
                    <td align="right"><span style="float:left">$</span>'.number_format($dataRow['bdr_retencionContable'],2).'</td>
                    <td>'.( $fechaAutorizacion->valido ? $fechaAutorizacion->fecha:'' ).'</td>
                </tr>
                <tr>
                    <td class="warning">Concepto</td>
                    <td '.( $dataRow['rct_reintegro'] != 1 ? 'colspan="4"':'').'><div class="div_ajustable" style="overflow:hidden">'.$dataRow['rct_justificacion'].'</div></td>
                    '.( $dataRow['rct_reintegro'] == 1 ? '
                    <td class="warning">Referencia Pago</td>
                    <td>'.$dataRow['rct_numeroComprobante'].'</td>
                    <td class="warning">Fecha Pago</td>
                    <td>'.( $fechaPago->valido ? $fechaPago->fecha:'' ).'</td>
                    ':'').'
                </tr>
            </table>
            <div id="clc_'.$dataRow['rct_id'].'"  class="clc_detail">
                <table id="table_clc_'.$dataRow['rct_id'].'" class="table table-bordered">
                    <tr class="warning">
                        <th>Afectacion</th>
                        <th>Percepción</th>
                        <th>Retención</th>
                    </tr>
    ';
    $cell = 0;
    $style = '';
    if($cell == 0 ){ 
        $cell = 1;
        $style = '';
    }else{ 
        $cell = 0;
        $style = "  background-color:#EAEAEA;"; 
    }
    
    $html.='<tr>';
    
    $html.= '	
                        <td>'.$dataRow['afe_id'].'</td>
                        <td align="right"><span style="float:left">$</span>'.number_format($dataRow['bdr_percepcionPresupuestal'] + $dataRow['bdr_percepcionContable'],2).'</td>
                        <td align="right"><span style="float:left">$</span>'.number_format($dataRow['bdr_retencionContable'],2).'</td>
                    </tr>
    ';
    
    while( $dataRow = $conexion->extraer() ){
        if( $cell == 0 ){ $cell =1;  $style ="";}else{ $cell = 0; $style="  background-color:#EAEAEA;"; }
        
        $html.='<tr style="'.$style.'">';
        
        $html.= '
                        <td>'.$dataRow['afe_id'].'</td>
                        <td align="right"><span style="float:left">$</span>'.number_format($dataRow['bdr_percepcionPresupuestal'] + $dataRow['bdr_percepcionContable'],2).'</td>
                        <td align="right"><span style="float:left">$</span>'.number_format($dataRow['bdr_retencionContable'],2).'</td>
                    </tr>
        ';
    }
    $html .= '
                </table>
            </div>
        </fieldset></br>
    ';
    $conexion->desconectar();
    unset($conexion);
    
    $return->html = $html;
    $return->valido = true;
    
    return $return;
}
