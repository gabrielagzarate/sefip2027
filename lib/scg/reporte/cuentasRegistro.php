<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function cuentasRegistro($gpo_id, $ur_id, $ue_id, $ctainicial, $ctafinal)
{
    $ca = new AccesoDatos();
    // Si en cat_CargaTrabajo tiene ue_id == null entonces NO está limitado por UE
    // Pero si sí está definido un valor en ése campo, quiere decir que sí debemos limitarlo a dichas UEs
    $ca->setSql("
    SELECT  concat(ger_id,'.', cgp_id,'.', rub_id,'.', cgcu_id,'.', cgsc_id,'.', cgc_id,'.', cta_id) as idcta
    FROM  cg_PlanCuentas p
    where cta_gpo_id = $gpo_id and cta_ur_id = $ur_id
        ".( $ue_id != '%' ? "AND cta_ue_id = $ue_id":"" )." 
    AND ( cta_cuenta BETWEEN '$ctainicial' AND '$ctafinal' ) "
        . " GROUP BY cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id
	ORDER BY ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id, cta_ur_id, cta_gpo_id, cta_ue_id
        ");

    $ca->conectar();
    $ca->consultar();
    $numRegistros = $ca->getNumeroRegistros();
    $ca->desconectar();
    $contador = 0;
    if ($numRegistros > 0) {
        while ($rowAuxiliar = $ca->extraer()) {
            $datos[$contador] = $rowAuxiliar['idcta'];  
            $contador++;
        }
    } else {
        $datos[0] = '';
        
    }

    return $datos;
}
?>