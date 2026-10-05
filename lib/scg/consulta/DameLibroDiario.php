<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Regresamos un array con el libro diario, para su impresión o exportación a excel
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
function DameLibroDiario($msc_id, $orden, $tipo, $numero, $vinculacion, $ue,
                             $tipoCLC, $usuario, $textoBuscar, $clc_v, $afe_v,
                             $cmp_v, $bdo_v, $clc_o, $afe_o, $cmp_o, $bdo_o,
                             $ref_v, $andOr, $bolPidieronVinculacion,
                             $filtroTexto)
{

    // El orden que pueden escoger es: or_N: por numero de póliza   or_F: por fecha   or_I: por importe
    // -------------------------------------------------------------------------------------------
    switch ($orden) {
        case 'or_F':
            $order = ' ORDER BY mva_fecha';
            break;
        case 'or_I':
            $order = ' ORDER BY m.pma_debe';
            break;
        case 'or_T':
            $order = ' ORDER BY m.pot_id, m.pma_id';
            break;
        default:
            $order = '';
            break;
    }

    $where = "";

    // Si estàn pidiendo ver sólo una ue ... filtramos
    // -------------------------------------------------------------------------------------------
    if ($ue != "" && $ue != "%") {
        $where = " AND m.ue_id = ".$ue." ";
    }

    // Si estàn pidiendo ver sólo un momento contable, un tipo de póliza o un estatus ...
    // -------------------------------------------------------------------------------------------
    $opcion = substr($tipo, 0, 2);
    $valor = substr($tipo, 3, 1);
    switch ($opcion) {
        case "tp":
            // Si filtran por vinculacion presupuestal, no pelamos éste filtro
            if ($bolPidieronVinculacion == false) {
                $where .= " AND m.pot_id = '".$valor."' ";
            }
            break;
        case "mc":
            // Si es de pagos a terceros, hay una validación extra
            if ($valor == "O") {
                $where .= " AND ( m.mmc_id = 'P' AND m.m.pol_tipo = 'C' ) ";
                // Y por lo tanto el Pagado Pagado normal también
            } else if ($valor == "P") {
                $where .= " AND ( mmc_id = '".$valor."' AND m.pol_tipo <> 'C' ) ";
            } else {
                $where .= " AND m.mmc_id = '".$valor."' ";
            }
            break;
        case "st":
            $where .= " AND m.epl_id = '".$valor."' ";
            break;
    }

    // Para el filtro por nùmero de pòliza, mostramos las que sean iguales o mayores al numero pedido
    // -------------------------------------------------------------------------------------------
    if ($numero != 0) {
        $where .= ' AND m.pma_id >= "'.$numero.'" ';
    }

    // Si desean mostrar sólo las pólizas que incluyan determinado texto en el concepto ...
    // -------------------------------------------------------------------------------------------
    if (trim($textoBuscar) != "") {
        $where .= " AND m.pma_concepto like '%".$textoBuscar."%' ";
    }

    // Para filtrar si un usuario en especial registro o modifico una póliza
    // -------------------------------------------------------------------------------------------
    if (trim($usuario) != "") {
        $where .= " AND u.usr_id = ".$usuario." ";
    }

    // Marcos: 21/marzo/2017
    // Adicionamos el filtro por texto en el cuadro de busqueda
    if ($filtroTexto != "") {
        $where = " AND m.pma_concepto LIKE '%".$filtroTexto."%' ";
    }

    // Marcos: 05/abr/2016
    // Adicionamos filtros para los campos *_origen y *_vinculacion de la tabla
    // cg_PolizaDetalleAuxiliar

    if ($bolPidieronVinculacion == true) $where .= " AND (";
    if ($clc_v != "") $where .= " $andOr d.pdx_clc_id_vinculacion = '$clc_v' ";
    if ($afe_v != "") $where .= " $andOr d.pdx_clc_id_vinculacion = '$afe_v' ";
    if ($cmp_v != "") $where .= " $andOr d.pdx_clc_id_vinculacion = '$cmp_v' ";
    if ($bdo_v != "") $where .= " $andOr d.pdx_clc_id_vinculacion = '$bdo_v' ";

    if ($clc_o != "") $where .= " $andOr d.pdx_clc_id_origen = '$clc_o' ";
    if ($afe_o != "") $where .= " $andOr d.pdx_clc_id_origen = '$afe_o' ";
    if ($cmp_o != "") $where .= " $andOr d.pdx_clc_id_origen = '$cmp_o' ";
    if ($bdo_o != "") $where .= " $andOr d.pdx_clc_id_origen = '$bdo_o' ";

    // El campo de REFERENCIA, si lo escriben tal y como se imprime... es tdf_id-tdf_referencia
    // así que lo separamos
    if ($ref_v != "") {
        $arrDocto = explode("-", $ref_v);
        // DEBE ser un array de 2 posiciones.   En caso contrario dejamos vacío
        if (count($arrDocto) == 2) {
            $where .= " $andOr d.tdf_id = '".$arrDocto[0]."' $andOr d.pdx_referencia = '".$arrDocto[1]."' ";
        }
    }

    if ($bolPidieronVinculacion == true) $where .= " )";


    // Aunque hasta abril del 2016 el mes era campo fijo, si pedimos filtros por
    // vinculación presupuestal, no pelaremos el mes
    if ($bolPidieronVinculacion == false) {
        $where .= ' AND m.msc_id = "'.$msc_id.'" ';
    }

    // Para corregir un error en la lógica del armado
    $where = str_replace("AND ( AND ", "AND ( ", $where);
    $where = str_replace("AND ( OR ", "AND ( ", $where);

    // Marcos: 18/abr/2017
    // Si es la UR 116, se regresa para excel la UR origen y el #CereCo
    // Para todas, se adiciona el ejercicio, la clc y la clave de financiamiento
    if ($_SESSION[_S]['clasAdm-gpo'] == 1 and $_SESSION[_S]['clasAdm-ur'] == 16) {
        $masDatos = ", CONCAT( MAX(da.pdx_gpo_id_origen), MAX(da.pdx_ur_id_origen) ) AS urOri, MAX(da.pdx_clc_cereCo_vinculacion) AS cereCo";
    } else {
        $masDatos = "";
    }


    // Armamos la consulta
    // -----------------------------------------------------------------------------------------------------------------------
    $sql = "SELECT  CONCAT( m.pot_id, '-', m.prf_id, m.pma_id ) AS poliza,
                    DAY( m.pma_fecha ) AS dia,
                    m.pma_concepto AS concepto,
                    m.pma_debe AS debe, m.pma_haber AS haber,


                    m.mmc_id AS mc,
                    m.tmc_id AS tmc,
                    m.epl_id AS edo,
                    m.ue_id AS ue,
                    da.pdx_ejercicio AS eje,
                    GROUP_CONCAT( DISTINCT CONCAT(da.pdx_clc_id_origen)) AS clc,
                    GROUP_CONCAT( DISTINCT CONCAT(ecf.ecf_clave)) AS ecf,
                    
                    u.usr_usuario AS u,
                    MONTH( m.pma_fecha ) as mesPol
			$masDatos
			FROM cg_PolizaMaestro m
			JOIN cg_PolizaDetalleAuxiliar da USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
			LEFT JOIN enc_ClaveFinanciamiento ecf USING( ecf_id )
			LEFT JOIN cat_Usuario u ON ( m.usr_id = u.usr_id )
			WHERE m.gpo_id = ".$_SESSION[_S]['clasAdm-gpo']." AND m.ur_id = ".$_SESSION[_S]['clasAdm-ur'].
        $where.
        " GROUP BY m.msc_id, m.ue_id, m.pot_id, m.prf_id, m.pma_id,da.pdx_ejercicio ".
        $order.";";
//echo '<br><pre>C-';print_r($sql);echo '</pre>';die();//*/
    $contable = new AccesoDatos(); //variable de konexion para los saldos iniciales
    $contable->conectar();
    $contable->setSql($sql);
    $contable->consultar();
    $contable->desconectar();
//echo '<br><pre>C-';print_r($datos);echo '</pre>';die();//*/
    $datos = array();
    $reginum=0;
    while ($rowContable = $contable->extraer()) {
        /* echo '<br><pre>C-';
          print_r($rowContable);
          echo '</pre>';// */
        //$datos[$rowContable['gpo_id'].$rowContable['ur_id'].'.'.$rowContable['tg_id'].'.'.$rowContable['idp_id'].'.'.$rowContable['pg_id'].'.'.$rowContable['pe_id'].'.'.$rowContable['ter_id'].'.'.$rowContable['cfi_clave']] = array(
        $datos[$reginum] = array(
            'poliza' => $rowContable['poliza'],
            'ue' => $rowContable['ue'],
            'mesPol' => $rowContable['mesPol'],
            'dia' => $rowContable['dia'],
            'concepto' => $rowContable['concepto'],
            'debe' => $rowContable['debe'],
            'haber' => $rowContable['haber'],
            'mc' => $rowContable['mc'],
            'tmc' => $rowContable['tmc'],
            'edo' => $rowContable['edo'],
            'eje' => $rowContable['eje'],
            'clc' => $rowContable['clc'],
            'ecf' => $rowContable['ecf'],
            'u' => $rowContable['u']
        );

        $reginum++;
    }

    if (count($datos) == 0) {
        $datos['vacio'] = array(
            'poliza' => '',
            'ue' => '',
            'mesPol' => '',
            'dia' => '',
            'concepto' => '',
            'debe' => '',
            'haber' => '',
            'mc' => '',
            'tmc' => '',
            'edo' => '',
            'eje' => '',
            'clc' => '',
            'ecf' => '',
            'u' => '')
        ;
    }

    //echo '<br><pre>C-';print_r($datos);echo '</pre>';die();//*/
    return $datos;
}
?>