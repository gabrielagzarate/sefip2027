<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function pintaClc($gpo_id, $ur_id, $clc_id, $parametroExtra = array()) {
    
    if($gpo_id == '' || $ur_id == '' || $clc_id == '')
        return false;
    
    $bdo_id = (isset($parametroExtra['bdo_id']) ? $parametroExtra['bdo_id'] : '');
    $todo = (isset($parametroExtra['todo']) ? $parametroExtra['todo'] : false);
    $ejercicio = (isset($parametroExtra['ejercicio']) ? $parametroExtra['ejercicio'] : APP_EJERCICIO);
    $parametroExtra['gpo_id_origen'] = (isset($parametroExtra['gpo_id_origen']) ? $parametroExtra['gpo_id_origen'] : $gpo_id);
    $parametroExtra['ur_id_origen'] = (isset($parametroExtra['ur_id_origen']) ? $parametroExtra['ur_id_origen'] : $ur_id);
    
    $parameotrosSQL = [
        ':gpo' => $gpo_id,
        ':ur' => $ur_id,
        ':clc' => $clc_id,
    ];
    
    $filtroContable = " AND gpo_id = :gpo AND ur_id = :ur AND clc_id = :clc ";
    if ($gpo_id . $ur_id == 118 && $parametroExtra['gpo_id_origen'] . $parametroExtra['ur_id_origen'] != 118) {
        $filtroContable .= " AND cta_gpo_id = :ugpoo AND cta_ur_id = :uuro ";
        $parameotrosSQL[':ugpoo'] = $parametroExtra['gpo_id_origen'];
        $parameotrosSQL[':uuro'] = $parametroExtra['ur_id_origen'];
    }
    
    $html = '';
    $return = new stdClass();
    $union = '';

    if ($todo) {
        $union = " UNION ALL";
        $union .= 
        "   SELECT gpo_id, ur_id, clc_id, afe_id, ncc_id, 'N/A', bdo_id, 
                rfc_id, ncc_referencia, ncc_percepcion AS percepcion, ncc_retencion AS retencion, 
                CONCAT(cta_cuenta, '-', cta_nombre) AS Cuenta, 'C' AS tipo, 
                ncc_tipoMovimiento 
            FROM ctrl{$ejercicio}.scp_Contable sc
            INNER JOIN ctrl{$ejercicio}.cg_PlanCuentas USING(cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
            WHERE sc.ncc_id IS NOT NULL {$filtroContable}
            ORDER BY ncc_tipoMovimiento, retencion";
    }
    
    $sql = "SELECT gpo_id, ur_id, clc_id, afe_id, ncp_id, cmp_id, 
            rfc_id, ncp_referencia, percepcion, retencion, 
            Cuenta, tcl_nombre, clc_concepto, clc_percepcion, clc_retencion, 
            DATE(clc_fechaEnvio) AS clc_fechaEnvio, DATE(clc_fechaCancelacion) AS clc_fechaRechazo, 
            bur_id, ecf_clave, bdo_id, tipo, 
            tcl_id, stc_id, tcl_nombre, stc_nombre, mpc_nombre, mpc_id 
        FROM ctrl{$ejercicio}.scp_Clc c 
            INNER JOIN ctrl{$ejercicio}.cat_ModoPagoClc USING(mpc_id) 
            INNER JOIN ctrl{$ejercicio}.cat_SubtipoClc USING(tcl_id, stc_id) 
            INNER JOIN ctrl{$ejercicio}.cat_TipoClc USING(tcl_id) 
            INNER JOIN ctrl{$ejercicio}.enc_ClaveFinanciamiento USING(ecf_id) 
            INNER JOIN (
                SELECT gpo_id, ur_id, clc_id, afe_id, ncp_id, cmp_id, bdo_id, 
                    rfc_id, ncp_referencia, ncp_percepcionNeto AS percepcion, 0 AS retencion, 
                    CONCAT(pg_id, '-', pg_nombre) AS Cuenta, 'P' AS tipo, 
                    '+' AS ncc_tipoMovimiento 
                FROM ctrl{$ejercicio}.scp_Presupuestal 
                    INNER JOIN ctrl{$ejercicio}.cat_PartidaGenerica USING(pg_id) 
                WHERE gpo_id = :gpo AND ur_id = :ur
                    AND clc_id = :clc
                {$union}
        )n USING(gpo_id, ur_id, clc_id) 
        WHERE gpo_id = :gpo AND ur_id = :ur
            AND clc_id = :clc
        ORDER BY tipo DESC 
    ";
    
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($parameotrosSQL);
    $dataRow = $conexion->extraer();
    
    $date = new stdClass;
    $date->gpo_id = $dataRow['gpo_id'];
    $date->ur_id = $dataRow['ur_id'];
    $date->fecha = $dataRow['clc_fechaEnvio'];
    $fechaEnvio = new stdClass;
    $fechaEnvio = validaFecha($date);
    $date = new stdClass;
    $date->gpo_id = $dataRow['gpo_id'];
    $date->ur_id = $dataRow['ur_id'];
    $date->fecha = $dataRow['clc_fechaRechazo'];
    $fechaRechazo = new stdClass;
    $fechaRechazo = validaFecha($date);
    
    $return->tcl_id = $dataRow['tcl_id'];
    $return->stc_id = $dataRow['stc_id'];
    $return->mpc_id = $dataRow['mpc_id'];
    
    $html .= '
        <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
            <table class="table table-bordered danger" style="margin-bottom:0px !important;">
                <tr class="warning">
                    <th>UR-CLC</th>
                    <th>Tipo-Subtipo</th>
                    <th style="width:120px;">Percepciones</th>
                    <th style="width:120px;">Retenciones</th>
                    <th>Envio</th>
                    <th>Rechazo</th>
                    <th>CLABE</th>
                    <th>Cve Fin.</th>
                    <th rowspan="2" style="vertical-align: middle;text-align: center;">
                        <span id="btn_clc_' . $dataRow['clc_id'] . '" dest="clc_' . $dataRow['clc_id'] . '" class="clc_detail_btn"> Detalle</span>
                        <script type="text/javascript" languaje="javascript">
                            $("#btn_clc_' . $dataRow['clc_id'] . '").click(function(){
                                $(".clc_detail").css("height","0px");
                                if (document.getElementById($(this).attr("dest")).offsetHeight > 0) {
                                    $("#" + $(this).attr("dest")).css("height","0px")
                                    $("#menclc").html("");
                                } else {
                                    $("#" + $(this).attr("dest")).css("height",document.getElementById("table_"+$(this).attr("dest")).offsetHeight);
                                    $("#menclc").html("Resaltado donde esta el documento actual");
                                }
                            });
                        </script>
                    </th>
                </tr>
                <tr>
                    <td><div class="div_ajustable" style="overflow:hidden">' . $dataRow['gpo_id'] . $dataRow['ur_id'] . ' - ' . $dataRow['clc_id'] . '</div></td>	
                    <td><div class="div_ajustable" style="overflow:hidden">' . $dataRow['tcl_id'] . " - " . $dataRow['tcl_nombre'] . ' : ' . $dataRow['stc_id'] . " - " . $dataRow['stc_nombre'] . '</div></td>	
                    <td align="right"> percepcion </td>
                    <td align="right"> retencion </td>
                    <td>' . ($fechaEnvio->valido ? $fechaEnvio->fecha : '') . '</td>
                    <td>' . ($fechaRechazo->valido ? $fechaRechazo->fecha : '') . '</td>
                    <td>' . $dataRow['bur_id'] . '</td><td>' . $dataRow['ecf_clave'] . '</td>
                </tr>
                <tr>
                    <td class="warning">Concepto</td>
                    <td colspan="5"><div class="div_ajustable" style="overflow:hidden">' . $dataRow['clc_concepto'] . '</div></td>
                    <td class="warning">Modo Pago</td>
                    <td >' . $dataRow['mpc_nombre'] . '</td>
                </tr>
            </table>
            <div id="clc_' . $dataRow['clc_id'] . '" class="clc_detail">
                <table id="table_clc_' . $dataRow['clc_id'] . '" class="table table-bordered">
                    <tr class="warning">
                        <th>Afectacion</th>
                        <th>Tipo</th>
                        <th># Control</th>
                        <th>Compromiso</th>
                        <th>RFC</th>
                        <th>Referencia</th>
                        <th>Importe/Percepción</th>
                        <th>Retención</th>
                        <th>Cuenta / Partida Genérica</th>
                    </tr>
    ';
    $cell = 0;
    $style = '';
    if ($cell == 0) { 
        $cell = 1;
        $style = '';
    } else { 
        $cell = 0;
        $style = "  background-color:#EAEAEA;"; 
    }
    
    if( $dataRow['bdo_id'] == $bdo_id )
        $html.='<tr style="border:1px solid #090; background-color: #dff0d8 !important;" class="success" >';
    else
        $html.='<tr>';
    

    $html.= '	
                    <td>' . $dataRow['afe_id'] . '</td>
                    <td>' . $dataRow['tipo'] . '</td>
                    <td>' . $dataRow['ncp_id'] . '</td>
                    <td>' . $dataRow['cmp_id'] . '</td>
                    <td>' . $dataRow['rfc_id'] . '</td>
                    <td>' . $dataRow['ncp_referencia'] . '</td>
                    <td align="right"><span style="float:left">$</span>' . number_format($dataRow['percepcion'], 2) . '</td>
                    <td align="right"><span style="float:left">$</span>' . number_format($dataRow['retencion'], 2) . '</td>
                    <td>' . $dataRow['Cuenta'] . '</td>
                </tr>
    ';
    
    if ($gpo_id . $ur_id == 118 && $parametroExtra['gpo_id_origen'] . $parametroExtra['ur_id_origen'] != 118) {
        $percepcion = $dataRow['percepcion'];
        $retencion = $dataRow['retencion'];
    } else {
        $percepcion = $dataRow['clc_percepcion'];
        $retencion = $dataRow['clc_retencion'];
    }
    
    while ($dataRow = $conexion->extraer()) {
        $return->tcl_id = $dataRow['tcl_id'];
        $return->stc_id = $dataRow['stc_id'];
        $return->mpc_id = $dataRow['mpc_id'];
        
        if ($cell == 0) {
            $cell = 1;
            $style = "";
        } else {
            $cell = 0;
            $style = "  background-color:#EAEAEA;";
        }
        
        if( $dataRow['bdo_id'] == $bdo_id )
            $html.='<tr style="border:1px solid #090; background-color: #dff0d8 !important;" class="success" >';
        else
            $html.='<tr style="'.$style.'">';
        
        $html.= '
                        <td>'.$dataRow['afe_id'].'</td>
                        <td>'.$dataRow['tipo'].'</td>
                        <td>'.$dataRow['ncp_id'].'</td>
                        <td>'.$dataRow['cmp_id'].'</td>
                        <td>'.$dataRow['rfc_id'].'</td>
                        <td>'.$dataRow['ncp_referencia'].'</td>
                        <td align="right"><span style="float:left">$</span>'.number_format($dataRow['percepcion'],2).'</td>
                        <td align="right"><span style="float:left">$</span>'.number_format($dataRow['retencion'],2).'</td>
                        <td>'.$dataRow['Cuenta'].'</td>
                    </tr>
        ';
        
        if ($gpo_id . $ur_id == 118 && $parametroExtra['gpo_id_origen'] . $parametroExtra['ur_id_origen'] != 118) {
            $percepcion += $dataRow['percepcion'];
            $retencion += $dataRow['retencion'];
        }
    }

    $html .= '
                </table>
            </div>
        </fieldset></br>
    ';
    $conexion->desconectar();
    unset($conexion);
    
    $return->html = str_replace("percepcion", '<span style="float:left">$</span>' . number_format($percepcion, 2), $html);
    $return->html = str_replace("retencion", '<span style="float:left">$</span>' . number_format($retencion, 2), $return->html);
    $return->valido = true;
    
    return $return;
}
