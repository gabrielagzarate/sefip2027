<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function pintaPoliza($poliza = NULL)
{
    $return = new stdClass();
    $html   = '';

    if (!$poliza->valido) {
        $return->html = '<div class="alert alert-danger">Error en los parametros de la póliza</div>';
        return $return;
    }

    if ($poliza->gpo_id == '' || $poliza->ur_id == '' || $poliza->ue_id == '' || $poliza->msc_id == '' || $poliza->pot_id == '' || $poliza->prf_id == '' || $poliza->pma_id == '') {
        $return->html = '<div class="alert alert-danger">Error en los parametros de la póliza<br>';
        if ($poliza->gpo_id == '')
            $return->html .= 'Falta el grupo<br>';
        if ($poliza->ur_id == '')
            $return->html .= 'Falta la UR<br>';
        if ($poliza->ue_id == '')
            $return->html .= 'Falta la UE<br>';
        if ($poliza->msc_id == '')
            $return->html .= 'Falta el mes ( msc_id )<br>';
        if ($poliza->pot_id == '')
            $return->html .= 'Falta el tipo de póliza ( pot_id )<br>';
        if ($poliza->prf_id == '')
            $return->html .= 'Falta el perfil (prf_id )<br>';
        if ($poliza->pma_id == '')
            $return->html .= 'Falta el número de póliza <br>';

        $return->html .= '</div>';
        return $return;
    }

    // Marcos: 15/jun/2018
    // Para el rectificado de la vinculación de Fondos, necesitamos gpo + ur de la Entidad, no de la 116
    // que es la clasifiación administrativa que tiene permisos para la opción
    // asi que agregaremos un parámetro    
    $btnImprimePoliza = '<button id="print_' . $poliza->pma_id . '" rg="' . $poliza->gpo_id . '" ru="' . $poliza->ur_id . '" ue="' . $poliza->ue_id . '" msc="' . $poliza->msc_id . '" pot="' . $poliza->pot_id . '" prf="' . $poliza->prf_id . '" pma="' . $poliza->pma_id . '" class="btn btn-outline-danger btn-xs" data-style="slide-left"><i class="fa-solid fa-file-pdf me-2"></i>PDF</button>';

    $maestro = ' 
            <table class="table table-bordered" style="margin-bottom:0px !important;">
                <tr class="warning">
                    <th>Póliza</th>	 
                    <th>Fecha</th> 
                    <th>Concepto</th> 
                    <th style="width:120px;">DEBE</th>	
                    <th style="width:120px;">HABER</th> 
                    <th>Tipo</th>
                    <th rowspan="2" style="vertical-align:middle; text-align:center;">
							<span id="btn_pol_' . $poliza->pma_id . '" dest="pol_' . $poliza->pma_id . '" class="clc_detail_btn">
							Detalle
							</span>
							<br>
                            <br>
						' . $btnImprimePoliza . '
						<script type="text/javascript" languaje="javascript">
							$("#btn_pol_' . $poliza->pma_id . '").click(function(){
								console.log("clickeando..." + $(this).attr("id"));
								$(".clc_detail").css("height","0px");
								
								if(document.getElementById($(this).attr("dest")).offsetHeight > 0 ){
									$("#" + $(this).attr("dest")).css("height","0px")
									$("#menclc").html("");
								}else{
									$("#" + $(this).attr("dest")).css("height",document.getElementById("table_"+$(this).attr("dest")).offsetHeight);
									$("#menclc").html("Resaltado donde esta el documento actual");
								}
							});
							$("#print_' . $poliza->pma_id . '").click(function(){
								var form = $("<form>")
								.attr("action", "rep/doc/scg/poliza/cgPoliza.php")
								.attr("method", "post")
								.attr("target", "_blank")
								.append($("<input>").attr("type", "hidden").attr("name", "tipo").attr("value", "actual"))
								.append($("<input>").attr("type", "hidden").attr("name", "rg").attr("value" ,  $(this).attr("rg") ))
                                .append($("<input>").attr("type", "hidden").attr("name", "ru").attr("value" ,  $(this).attr("ru") ))
                                .append($("<input>").attr("type", "hidden").attr("name", "ue").attr("value" ,  $(this).attr("ue") ))
								.append($("<input>").attr("type", "hidden").attr("name", "msc").attr("value",  $(this).attr("msc") ))
								.append($("<input>").attr("type", "hidden").attr("name", "pot").attr("value",  $(this).attr("pot") ))
								.append($("<input>").attr("type", "hidden").attr("name", "prf").attr("value",  $(this).attr("prf") ))
								.append($("<input>").attr("type", "hidden").attr("name", "num").attr("value",  $(this).attr("pma") ))
								.append($("<input>").attr("type", "hidden").attr("name", "rev").attr("value",  1 ))
								$("body").append(form);
								form.submit();
								form.remove();
							});
						</script>
					</th>
                </tr>
                <tr>
                    <td>' . $poliza->pot_id . '-' . $poliza->prf_id . $poliza->pma_id . '</td>
                    <td>' . $poliza->fecha . '</td>
                    <td><div class="div_ajustable">' . $poliza->concepto . '</div></td> 
                    <td align="right"><span style="float:left;">$</span>' . number_format($poliza->debe, 2) . '</td>
                    <td align="right"><span style="float:left;">$</span>' . number_format($poliza->haber, 2) . '</td>
                    <td>' . $poliza->agrupador . '</td>
                </tr>
            </table>
    ';

    $detalle = '
        <div id="pol_' . $poliza->pma_id . '"  class="clc_detail">
            <table id="table_pol_' . $poliza->pma_id . '" class="table table-bordered">
                <tr class="warning">
                    <th>Cuenta</th>
                    <th>Nombre Cuenta</th>
                    <th>Concepto</th>
                    <th style="width:120px;">DEBE</th>
                    <th style="width:120px;">HABER</th>
                </tr> 
    ';
    foreach ($poliza->detalleMayor as $key => $value) {
        $detalle .= '
                    <tr>
                        <td>' . $value->cuentaMayor . '</td>
                        <td><div class="div_ajustable">' . $value->cuentaMayorNombre . '</div></td>
                        <td><div class="div_ajustable">' . $value->concepto . '</div></td>
                        <td align = "right"><span style="float:left;">$</span>' . number_format($value->debe, 2) . '</td>
                        <td align = "right"><span style="float:left;">$</span>' . number_format($value->haber, 2) . '</td>
                    </tr>
        ';
    }

    $detalle .= '
            </table>
        </div> 
    ';

    $html .= '
    <fieldset class="col-md-12 col-xs-12 ejercido_bloque" >
        <legend>Póliza contable de ' . ($poliza->tipoMomento == 'A' ? "Autorización" : ($poliza->tipoMomento == 'C' ? "Cancelación" : '')) . '
            ' . ($poliza->estado == 'C' ? ' <span style="color:#900">PÓLIZA CANCELADA</span>' : '') . '
            <span id="boton' . $poliza->tipoMomento . '"></span>
        </legend>
        ' . $maestro . '
        ' . $detalle . '
    </fieldset><br/>
    ';

    $return->html = $html;

    return $return;
}
