<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function pintaSinFlujoEfectivo($gpo_id, $ur_id, $clc_id, $parametroExtra = array())
{
    if ($gpo_id == '' || $ur_id == '' || $clc_id == '') return false;

    
    $ejercicio = ( isset($parametroExtra['ejercicio']) ? $parametroExtra['ejercicio']
            : '' );

    switch ($ejercicio) {
        case 2016:
            $campo = "t.tmi_id, t.tmi_nombre, m.tdf_id, m.bdm_referencia, m.bdm_concepto, ing_fechaPago AS bdm_fechaAutorizado, '' AS bdm_fechaCancelado, ";
            $tabla = "FROM ctrl".$ejercicio.".ing_BancoDocumentoMinistrado m ";
            break;
        case 2019:

            $campo = "t.tmi_id, t.tmi_nombre, m.tdf_id, m.bdm_referencia,
                            case when bdm_tipoAgrupador= 'C' Then c.clc_concepto
				 when bdm_tipoAgrupador= 'D' Then bd.bdo_concepto
                 else ''
            END AS bdm_concepto, m.bdm_fechaAutorizado, m.bdm_fechaCancelado, ";
            $tabla = "FROM ".BD_TESORERIA.".min_BancoDocumentoMinistrado_2019 m
                        INNER JOIN ctrl".$ejercicio.".cat_BancoDocumentos bd USING( gpo_id, ur_id, bdo_id ) ";
            break;
        case 2020:

            $campo = "t.tmi_id, t.tmi_nombre, m.tdf_id, m.bdm_referencia,
                            case when bdm_tipoAgrupador= 'C' Then c.clc_concepto
				 when bdm_tipoAgrupador= 'D' Then bd.bdo_concepto
                 else ''
            END AS bdm_concepto, m.bdm_fechaAutorizado, m.bdm_fechaCancelado, ";
            $tabla = "FROM ".BD_TESORERIA.".min_BancoDocumentoMinistrado_2020 m
                        INNER JOIN ctrl".$ejercicio.".cat_BancoDocumentos bd USING( gpo_id, ur_id, bdo_id ) ";
            break;
             case 2022:

            $campo = "t.tmi_id, t.tmi_nombre, m.tdf_id, m.bdm_referencia,
                            case when bdm_tipoAgrupador= 'C' Then c.clc_concepto
                 when bdm_tipoAgrupador= 'D' Then bd.bdo_concepto
                 else ''
            END AS bdm_concepto, m.bdm_fechaAutorizado, m.bdm_fechaCancelado, ";
            $tabla = "FROM ".BD_TESORERIA.".min_BancoDocumentoMinistrado_2022 m
                        INNER JOIN ctrl".$ejercicio.".cat_BancoDocumentos bd USING( gpo_id, ur_id, bdo_id ) ";
            break;

        default:
            $campo = "t.tmi_id, t.tmi_nombre, m.tdf_id, m.bdm_referencia,
                            case when bdm_tipoAgrupador= 'C' Then c.clc_concepto
				 when bdm_tipoAgrupador= 'D' Then bd.bdo_concepto
                 else ''
            END AS bdm_concepto, m.bdm_fechaAutorizado, m.bdm_fechaCancelado, ";
            $tabla = "FROM ".BD_CTRLANT.".min_BancoDocumentoMinistrado m "
                ."INNER JOIN ctrl".$ejercicio.".cat_BancoDocumentos bd USING( gpo_id, ur_id, bdo_id ) ";

            break;
    }
    
    $parametros = [':gpo'=>$gpo_id,':ur'=>$ur_id,':clc'=>$clc_id];
    $html = '';
    $return = new stdClass();
    $sql = "
        SELECT m.gpo_id, m.ur_id, m.clc_id, m.afe_id, m.bdo_id, 
            $campo 
            m.bdm_estado, m.bdm_percepcionPresupuestal, m.bdm_percepcionContable, m.bdm_retencionContable, 
            m.bdm_percepcionPresupuestal + m.bdm_percepcionContable - m.bdm_retencionContable AS importe, 
            i.iba_id, i.iba_nombre, b.bur_cuentaBancaria, b.bur_alias, b.bur_beneficiario 
        $tabla 
            INNER JOIN ctrl".$ejercicio.".scp_Clc c USING( gpo_id, ur_id, clc_id ) 
            INNER JOIN ctrl".$ejercicio.".cat_BancosUr b USING( bur_id ) 
            INNER JOIN ctrl".$ejercicio.".cat_InstitucionesBancarias i USING( iba_id ) 
            ".(($ejercicio == 2022) ? "INNER JOIN tesoreria.cat_TipoMinistracion t USING( tmi_id )"
            : (($ejercicio >= 2017 && $ejercicio < 2022) ? "INNER JOIN ctrlAnterior.cat_TipoMinistracion t USING( tmi_id )"
            :
        "INNER JOIN ctrl".$ejercicio.".cat_TipoMinistracion t USING( tmi_id )")).
        "
        WHERE m.gpo_id = :gpo AND m.ur_id = :ur 
            AND m.clc_id = :clc ";
    if ($ejercicio == 2015) {
        $sql = "
        SELECT n.gpo_id, n.ur_id, n.clc_id, n.afe_id, n.bdo_id, 
            1 AS tmi_id, '' AS tmi_nombre, '' AS tdf_id, bdm_referencia, bdm_concepto, bdm_fechaAutorizado, '' AS bdm_fechaCancelado, 
            'A' AS bdm_estado, n.bdm_percepcionPresupuestal, n.bdm_percepcionContable, n.bdm_retencionContable, 
            n.bdm_percepcionPresupuestal + n.bdm_percepcionContable - n.bdm_retencionContable AS importe, 
            i.iba_id, i.iba_nombre, b.bur_cuentaBancaria, b.bur_alias, b.bur_beneficiario 
        FROM ( 
                SELECT n.gpo_id, n.ur_id, n.clc_id, n.afe_id, n.bdo_id, c.bur_id, 
                    c.ing_referenciaPago AS bdm_referencia, c.clc_concepto AS bdm_concepto, c.ing_fechaPago AS bdm_fechaAutorizado, 
                    SUM(n.ncp_percepcionNeto) AS bdm_percepcionPresupuestal, 0 AS bdm_percepcionContable, 0 AS bdm_retencionContable 
                FROM ".BD_CTRL_2015.".scp_Clc c 
                    INNER JOIN ".BD_CTRL_2015.".scp_Presupuestal n USING( gpo_id, ur_id, clc_id ) 
                WHERE n.gpo_id = :gpo AND n.ur_id = :ur
                    AND n.clc_id = :clc 
                    AND c.ecl_id IN ( 13, 16 ) 
            UNION ALL 
                SELECT n.gpo_id, n.ur_id, n.clc_id, n.afe_id, n.bdo_id, c.bur_id, 
                    c.ing_referenciaPago AS bdm_referencia, c.clc_concepto AS bdm_concepto, c.ing_fechaPago AS bdm_fechaAutorizado, 
                    0 AS bdm_percepcionPresupuestal, SUM(n.ncc_percepcion) AS bdm_percepcionContable, SUM(n.ncc_retencion) AS bdm_retencionContable 
                FROM ".BD_CTRL_2015.".scp_Clc c 
                    INNER JOIN ".BD_CTRL_2015.".scp_Contable n USING( gpo_id, ur_id, clc_id ) 
                WHERE n.gpo_id = :gpo AND n.ur_id = :ur 
                    AND n.clc_id = :clc 
                    AND c.ecl_id IN ( 13, 16 ) 
            )n 
            INNER JOIN ".BD_CTRL_2015.".cat_BancosUr b USING( bur_id ) 
            INNER JOIN ".BD_CTRL_2015.".cat_InstitucionesBancarias i USING( iba_id ) 
    ";
    }

    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($parametros);
    $dataRow = $conexion->extraer();

    $date = new stdClass;
    $date->gpo_id = $dataRow['gpo_id'];
    $date->ur_id = $dataRow['ur_id'];
    $date->fecha = $dataRow['bdm_fechaAutorizado'];
    $fechaAutorizado = new stdClass;
    $fechaAutorizado = validaFecha($date);
    $date = new stdClass;
    $date->gpo_id = $dataRow['gpo_id'];
    $date->ur_id = $dataRow['ur_id'];
    $date->fecha = $dataRow['bdm_fechaCancelado'];
    $fechaCancelado = new stdClass;
    $fechaCancelado = validaFecha($date);
    $bdmReferencia = str_replace(" ", "", $dataRow['bdm_referencia']);
    $html .= '
        <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
            <table class="table table-bordered danger" style="margin-bottom:0px !important;">
                <tr class="warning">
                    <th>Tipo Ministración</th>
                    <th>Referencia</th>
                    <th style="width:120px;">Importe</th>
                    <th>Concepto</th>
                    <th>Fecha Ministrado</th>
                    <th>Fecha Cancelado</th>
                    <th rowspan="2" style="vertical-align: middle;text-align: center;">
                        <span id="btn_clc_'.$bdmReferencia.'" dest="clc_'.$bdmReferencia.'" class="clc_detail_btn"> Detalle</span>
						<script type="text/javascript" languaje="javascript">
							$("#btn_clc_'.$bdmReferencia.'").click(function(){
								$(".clc_detail").css("height","0px");
								if(document.getElementById($(this).attr("dest")).offsetHeight > 0 ){
									$("#" + $(this).attr("dest")).css("height","0px")
									$("#ministrado").html("");                                    
								}else{
									$("#" + $(this).attr("dest")).css("height",document.getElementById("table_"+$(this).attr("dest")).offsetHeight);
									$("#ministrado").html("Resaltado donde esta el documento actual");                                    
								}
							});
						</script>
                    </th>
                </tr>
                <tr>
                    <td>'.$dataRow['tmi_id'].' - '.$dataRow['tmi_nombre'].'</td>	
                    <td>'.$dataRow['tdf_id'].' - '.$dataRow['bdm_referencia'].'</td>	
                    <td align="right"> importeMinistrado </td>
                    <td><div class="div_ajustable" style="overflow:hidden">'.$dataRow['bdm_concepto'].'</div></td>	
                    <td>'.( $fechaAutorizado->valido ? $fechaAutorizado->fecha : '' ).'</td>
                    <td>'.( $fechaCancelado->valido ? $fechaCancelado->fecha : '' ).'</td>
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
            <div id="clc_'.$bdmReferencia.'"  class="clc_detail">
                <table id="table_clc_'.$bdmReferencia.'" class="table table-bordered">
                    <tr class="warning">
                        <th>CLC</th>
                        <th>Afectacion</th>
                        <th>Percepción Presupuestal</th>
                        <th>Percepción Contable</th>
                        <th>Retención Contable</th>
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

    if ($dataRow['clc_id'] == $clc_id)
            $html .= '<tr style="border:1px solid #090;" class="success" >';
    else $html .= '<tr>';

    $html .= '
                        <td>'.$dataRow['clc_id'].'</td>
                        <td>'.$dataRow['afe_id'].'</td>
                        <td align="right"><span style="float:left">$</span>'.number_format($dataRow['bdm_percepcionPresupuestal'],
            2).'</td>
                        <td align="right"><span style="float:left">$</span>'.number_format($dataRow['bdm_percepcionContable'],
            2).'</td>
                        <td align="right"><span style="float:left">$</span>'.number_format($dataRow['bdm_retencionContable'],
            2).'</td>
                    </tr>
    ';

    $importeMinistrado = ( $dataRow['bdm_percepcionPresupuestal'] + $dataRow['bdm_percepcionContable']
        - $dataRow['bdm_retencionContable'] );

    while ($dataRow = $conexion->extraer()) {

        if ($cell == 0) {
            $cell = 1;
            $style = "";
        } else {
            $cell = 0;
            $style = "  background-color:#EAEAEA;";
        }

        if ($dataRow['clc_id'] == $clc_id)
                $html .= '<tr style="border:1px solid #090;" class="success" >';
        else $html .= '<tr style="'.$style.'">';

        $html .= '
                        <td>'.$dataRow['clc_id'].'</td>
                        <td>'.$dataRow['afe_id'].'</td>
                        <td align="right"><span style="float:left">$</span>'.number_format($dataRow['bdm_percepcionPresupuestal'],
                2).'</td>
                        <td align="right"><span style="float:left">$</span>'.number_format($dataRow['bdm_percepcionContable'],
                2).'</td>
                        <td align="right"><span style="float:left">$</span>'.number_format($dataRow['bdm_retencionContable'],
                2).'</td>
                    </tr>
        ';

        $importeMinistrado += ( $dataRow['bdm_percepcionPresupuestal'] + $dataRow['bdm_percepcionContable']
            - $dataRow['bdm_retencionContable'] );
    }
    $html .= '
                </table>
            </div>
        </fieldset></br>
    ';
    $conexion->desconectar();
    unset($conexion);

    $return->html = str_replace("importeMinistrado",
        '<span style="float:left">$</span>'.number_format($importeMinistrado, 2),
        $html);
    $return->valido = true;

    return $return;
}
