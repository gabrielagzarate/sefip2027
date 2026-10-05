<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function importeSqlAuxiliar($cuenta, $gpo_id, $ur_id, $tipo, $periodoArreglo,
                            $cajaArreglo, $busqueda, $cuentaArreglo, $deue,
                            $cfi, $datosExtra)
{
    $params = [];
    $ct = '';
    $nivel = 0;
    $claveFinanciamiento = $cfi;
    $ue_id = '';
    if ($_SESSION[_S]['nvj_id'] == 1) $ue_id = $_SESSION[_S]['ue_id'];
    else
            $ue_id = ( ( $_SESSION[_S]['clasAdm-gpo'].$_SESSION[_S]['clasAdm-ur']
            == 116 && !is_null($_SESSION[_S]['clasAdm-ue']) ) ? $_SESSION[_S]['clasAdm-ue']
                : '%');
   
    if ($busqueda == "R" && $cuentaArreglo["cuentaDesde"] == $cuentaArreglo["cuentaHasta"]) {
        $cuenta = $cuentaArreglo["cuentaDesde"];
    }
    $arrayCuenta = explode('.', $cuenta);
    $arrayCuenta = array_pad($arrayCuenta, 5, '0');
    $cgm = $arrayCuenta[0].$arrayCuenta[1].$arrayCuenta[2].$arrayCuenta[3].$arrayCuenta[4];

    $ger = substr($cgm, 0, 1);
    $cgp = substr($cgm, 1, 1);
    $rub = substr($cgm, 2, 1);
    $cgcu = substr($cgm, 3, 1);
    $cgsc = substr($cgm, 4);

    $cgc = '';
    $cgq = '';
    $cta = '';
    $sql = '';
    if (isset($arrayCuenta[5])) $cgc = substr($arrayCuenta[5], 0);
    if (isset($arrayCuenta[6])) $cgq = substr($arrayCuenta[6], 0);
    if (strlen($cgq) > 2) $cta = $cgq;
    if (isset($arrayCuenta[7])) $cta = substr($arrayCuenta[7], 0);

    // para que encuentre las cuentas de quinto nivel
    if (strlen($cgq.$cta) == 6) $cta = $cgq.$cta;

    if (strlen($cta) >= 4) {
        $nivel = 8;
    } elseif (strlen($cgq) == 2) {
        $nivel = 7;
    } elseif (strlen($cgc) == 3) {
        $nivel = 6;
    } elseif (strlen($cgm) == 5) {
        $nivel = 5;
    }

    $ca = new AccesoDatos();
    // Si en cat_CargaTrabajo tiene ue_id == null entonces NO está limitado por UE
    // Pero si sí está definido un valor en ése campo, quiere decir que sí debemos limitarlo a dichas UEs
    $ca->setSql("SELECT ue_id FROM cat_CargaTrabajo WHERE usr_id = ?");
    $ca->conectar();
    $ca->consultar(array($_SESSION[_S]['usr_id']));
    $numRegistros = $ca->getNumeroRegistros();
    $row = $ca->extraer();
    $ca->desconectar();

    if ($numRegistros > 0) {
        if (is_null($row['ue_id'])) {
            $ct = '';
        } else {
            $ct = 'INNER JOIN ( SELECT ue_id FROM cat_CargaTrabajo WHERE usr_id = :usr ) ct USING( ue_id )';
            $params[':usr'] = $_SESSION[_S]['usr_id'];
        }
    }

    $sqloriwhere = '';
    if (isset($_POST['ori_ur'])) {
        if (isset($_POST['ori_ur_id']) && $_POST['ori_ur_id'] != '') {
            $sqloriwhere .= " AND concat(pdx_gpo_id_origen,pdx_ur_id_origen)=".$_POST['ori_ur_id']." ";
        }
    }

    if (isset($_POST['ori_clc'])) {

        if (isset($_POST['oriclc_id']) && $_POST['oriclc_id'] != '') {
            $sqloriwhere .= " AND pdx_clc_id_origen = :ori ";
            $params[':ori'] = $_POST['oriclc_id'];
        }
    }

    //EN ESTA PARTE SOLO VERIFICAMOS SI ELIGIERON UN TIPO DE POLIZA PARA REALIZAR EL FILTRO SI NO SE MUESTRAN TODOS LOS TIPOS DE POLIZAS
    $poliza = ( $tipo != '0' && $tipo != '') ? "AND pot_id = '".$tipo."'" : "";
    //SE ELIGE EL PERIOO DE MESES
    //este es para la primer consulta
    $periodo = "AND msc_id <= ".$periodoArreglo["mesHasta"];
    //esto es para la segunda
    $periodo2 = ( $periodoArreglo["mesDesde"] == $periodoArreglo["mesHasta"] ) ? "AND msc_id = ".$periodoArreglo["mesDesde"]
            :
        "AND ( msc_id BETWEEN ".$periodoArreglo["mesDesde"]." AND ".$periodoArreglo["mesHasta"]." )";

    // Ordenado por poliza (default) o por fecha
    $ordenar = '';
    if ($cajaArreglo["cjaOrden"] == 'on') $ordenar = "ORDER BY pma_fecha";
    else $ordenar = "ORDER BY pot_id, prf_id, pma_id";

    //Búsqueda de las pólizas marcadas como Flujo de Efectivo
    $fe = '';
    if($cajaArreglo["cjaFe"] == 'on') { 
        $fe = ' AND m.apg_id LIKE "%FE%"';
    }

    //Búsqueda de sólo pólizas canceladas
    $pci = '';
    $pct = '';
    if($cajaArreglo["cjaPc"] == 'on') { 
        $pci = ' AND m.epl_id IN ("C","H")';  
        $pct = ' AND m.epl_id IN ("C","H")';
    }else{
        $pci = ' AND m.epl_id NOT IN ("C","H")';
    }

    // es para saber que tipo de busqueda viene  si es R es por rango si es C es por una cuenta....
    $cuentas = '';
    //echo $busqueda;
    if ($busqueda == "R") {

        if ($cuentaArreglo["cuentaDesde"] == $cuentaArreglo["cuentaHasta"]) {
            $cuentas = ' AND ger_id='.$ger.' AND cgp_id='.$cgp.' AND rub_id='.$rub.' AND cgcu_id='.$cgcu.' AND cgsc_id='.$cgsc.$cuentas;
        } else {
            $cuentas = " AND ( cgsc_cuenta BETWEEN '".$cuentaArreglo["cuentaDesde"]."' AND '".$cuentaArreglo["cuentaHasta"]."' )";
        }
        if ($cuentaArreglo["cuentaDesde"] == "N"){
            $cuentas = " AND ( cgsc_cuenta BETWEEN '".$cuentaArreglo["cuentaDesde"]."' AND '".$cuentaArreglo["cuentaHasta"]."' )";
        }


    } else if ($busqueda == "C" && isset($cuentaArreglo["cuenta"]) && $cuentaArreglo["cuenta"]
        != '') {

        //echo $pdf->nivel;
        switch ($nivel) {
            case 8:
                $cuentas = 'AND ger_id='.$ger.' AND cgp_id='.$cgp.' AND rub_id='.$rub.' AND cgcu_id='.$cgcu.' AND cgsc_id='.$cgsc.'
						AND cgc_id = "'.$cgc.'"
						AND cta_id = "'.$cta.'"';
                break;
            case 7:
                $cuentas = 'AND ger_id='.$ger.' AND cgp_id='.$cgp.' AND rub_id='.$rub.' AND cgcu_id='.$cgcu.' AND cgsc_id='.$cgsc.'
						AND cgc_id = "'.$cgc.'"
						AND cta_id like "'.$cgq.'%" AND LENGTH(cta_id) = 6 ';
                break;
            case 6:
                $cuentas = 'AND ger_id='.$ger.' AND cgp_id='.$cgp.' AND rub_id='.$rub.' AND cgcu_id='.$cgcu.' AND cgsc_id='.$cgsc.'
						AND cgc_id = "'.$cgc.'"';
                break;
            case 5:
                $cuentas = 'AND ger_id='.$ger.' AND cgp_id='.$cgp.' AND rub_id='.$rub.' AND cgcu_id='.$cgcu.' AND cgsc_id='.$cgsc;
                break;
        }
    }

    $mmc_id = '';
    if (count($cajaArreglo["cjaMmc"])) {
        if (count($cajaArreglo["cjaMmc"]) > 1) {
            $mmc_id = "AND mmc_id in(";
            foreach ($cajaArreglo["cjaMmc"] as $key => $value) {
                $mmc_id .= "'$value',";
            }
            $mmc_id = substr($mmc_id, 0, -1);
            $mmc_id .= ")";
        } else $mmc_id = "AND mmc_id = '{$cajaArreglo["cjaMmc"][0]}'";
    }

    if ($deue != '' && $ue_id == '%') $ue_id = $deue;

    $consultar = new AccesoDatos();
    $consultar->conectar();
    $datos = array();
    if ($datosExtra["inicial"]) {
        $consultar->setSql("
				SELECT 
					MAX(gpo_id) AS gpo_id, MAX(ur_id) AS ur_id, cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id,
					MAX(d.msc_id) AS msc_id, MAX(pot_id) AS pot_id, MAX(c.nat_id) AS nat_id, -- d.nat_id,
					sum(if(ISNULL(msc_id), 0, if(msc_id < ".$periodoArreglo["mesDesde"].", pdx_debe, 0)) ) InicialDebe,
					sum(if(ISNULL(msc_id), 0, if(msc_id < ".$periodoArreglo["mesDesde"].", pdx_haber, 0)) ) InicialHaber, 
					MAX(mmc_id) AS mmc_id, cta_cuenta, cta_nombre, cgsc_naturalezaFija, cgsc_cuenta,
					CONCAT(MAX(gpo_id), MAX(ur_id), cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id) AS id
				FROM cg_PolizaDetalleAuxiliar d
					INNER JOIN cg_PolizaMaestro m USING(gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id)
					INNER JOIN cg_PlanCuentas p USING(cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
					INNER JOIN cat_CgSubcuenta AS c USING(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id)
					$ct
				WHERE cta_gpo_id = :gpo_id and cta_ur_id = :ur_id ".
            ( $ue_id != '%' ? "AND cta_ue_id = $ue_id" : "" )."
					$cuentas 
					$periodo $poliza $mmc_id $fe
                    $pci
					".$claveFinanciamiento.$sqloriwhere." 
				GROUP BY cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id 
			ORDER BY ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id, cta_ur_id, cta_gpo_id, cta_ue_id
				;
			");

        $params[':gpo_id'] = $gpo_id;
        $params[':ur_id'] = $ur_id;
        $consultar->consultar($params);

        $consultar->desconectar();
        while ($rowAuxiliar = $consultar->extraer()) {
            $datos[$rowAuxiliar['id']] = array(
                'id' => $rowAuxiliar['id'],
                'gpo_id' => $rowAuxiliar['gpo_id'],
                'ur_id' => $rowAuxiliar['ur_id'],
                'ue_id' => '',
                'pot_id' => $rowAuxiliar['pot_id'],
                'prf_id' => '',
                'msc_id' => $rowAuxiliar['msc_id'],
                'pma_id' => '',
                'pdt_id' => '',
                'pdx_id' => '',
                'cta_gpo_id' => $rowAuxiliar['cta_gpo_id'],
                'cta_ur_id' => $rowAuxiliar['cta_ur_id'],
                'cta_ue_id' => $rowAuxiliar['cta_ue_id'],
                'ger_id' => $rowAuxiliar['ger_id'],
                'cgp_id' => $rowAuxiliar['cgp_id'],
                'rub_id' => $rowAuxiliar['rub_id'],
                'cgcu_id' => $rowAuxiliar['cgcu_id'],
                'cgsc_id' => $rowAuxiliar['cgsc_id'],
                'cgc_id' => $rowAuxiliar['cgc_id'],
                'cta_id' => $rowAuxiliar['cta_id'],
                'pdx_debe' => $rowAuxiliar['InicialDebe'],
                'pdx_haber' => $rowAuxiliar['InicialHaber'],
                'nat_id' => $rowAuxiliar['nat_id'],
                'pdx_concepto' => '',
                'tdf_id' => '',
                'pdx_referencia' => '',
                'pdx_fechaReferencia' => '',
                'ecf_id' => '',
                'pdx_bdo_id_origen' => '',
                'mmc_id' => $rowAuxiliar['mmc_id'],
                'epl_id' => '',
                'pma_concepto' => '',
                'concepto' => '',
                'pma_fecha' => '',
                'cta_cuenta' => $rowAuxiliar['cta_cuenta'],
                'cta_nombre' => $rowAuxiliar['cta_nombre'],
                'cgsc_naturalezaFija' => $rowAuxiliar['cgsc_naturalezaFija'],
                'cgsc_cuenta' => $rowAuxiliar['cgsc_cuenta'],
                'cgsc_nombre' => '',
                'ecf_clave' => ''
            );
            
        }
    } else {
        $gpo = $datosExtra["gpo"];
        $ur = $datosExtra["ur"];
        $ue = $datosExtra["ue"];
        $ger_id = $datosExtra['ger_id'];
        $cgp_id = $datosExtra['cgp_id'];
        $rub_id = $datosExtra['rub_id'];
        $cgcu_id = $datosExtra['cgcu_id'];
        $cgsc_id = $datosExtra['cgsc_id'];
        $cgc = $datosExtra['cgc_id'];
        $cta = $datosExtra['cta_id'];

        $cuentas = 'AND ger_id='.$ger_id.' AND cgp_id='.$cgp_id.' AND rub_id='.$rub_id.' AND cgcu_id='.$cgcu_id.' AND cgsc_id='.$cgsc_id
            .' AND cgc_id = "'.$cgc.'" AND cta_id = "'.$cta.'"';

        $consultar->setSql("
				SELECT 
					gpo_id ,ur_id ,cta_ue_id ,pot_id ,prf_id ,d.msc_id ,pma_id ,pdt_id ,pdx_id ,
					cta_gpo_id ,cta_ur_id ,cta_ue_id ,ger_id ,cgp_id ,rub_id ,cgcu_id ,cgsc_id ,cgc_id ,cta_id,
					pdx_debe ,pdx_haber, d.nat_id ,pdx_concepto ,tdf_id,pdx_referencia ,pdx_fechaReferencia,
					ecf_id, pdx_bdo_id_origen,
					mmc_id ,epl_id, pma_concepto, if( pdx_concepto = '',pma_concepto, pdx_concepto ) concepto ,pma_fecha,
					cta_cuenta, cta_nombre ,cgsc_naturalezaFija ,cgsc_cuenta ,cgsc_nombre , ecf_clave
				FROM cg_PolizaDetalleAuxiliar d 
					INNER JOIN cg_PolizaMaestro m USING(gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id)
					INNER JOIN cg_PlanCuentas p USING(cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
					INNER JOIN cat_CgSubcuenta  USING(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id)
					LEFT JOIN enc_ClaveFinanciamiento using(ecf_id)
				WHERE cta_gpo_id = :gpo and cta_ur_id = :ur AND cta_ue_id = :ue 
					$cuentas 
					$periodo2 $poliza $mmc_id $fe
                    $pct
					".$claveFinanciamiento.$sqloriwhere." 
					$ordenar
				;
			");

        $params[':gpo'] = $gpo;
        $params[':ur'] = $ur;
        $params[':ue'] = $ue;
        $consultar->consultar($params);
        $consultar->desconectar();
        while ($rowAuxiliar = $consultar->extraer()) {
            $datos[] = array(
                'gpo_id' => $rowAuxiliar['gpo_id'],
                'ur_id' => $rowAuxiliar['ur_id'],
                'ue_id' => $rowAuxiliar['cta_ue_id'],
                'pot_id' => $rowAuxiliar['pot_id'],
                'prf_id' => $rowAuxiliar['prf_id'],
                'msc_id' => $rowAuxiliar['msc_id'],
                'pma_id' => $rowAuxiliar['pma_id'],
                'pdt_id' => $rowAuxiliar['pdt_id'],
                'pdx_id' => $rowAuxiliar['pdx_id'],
                'cta_gpo_id' => $rowAuxiliar['cta_gpo_id'],
                'cta_ur_id' => $rowAuxiliar['cta_ur_id'],
                'cta_ue_id' => $rowAuxiliar['cta_ue_id'],
                'ger_id' => $rowAuxiliar['ger_id'],
                'cgp_id' => $rowAuxiliar['cgp_id'],
                'rub_id' => $rowAuxiliar['rub_id'],
                'cgcu_id' => $rowAuxiliar['cgcu_id'],
                'cgsc_id' => $rowAuxiliar['cgsc_id'],
                'cgc_id' => $rowAuxiliar['cgc_id'],
                'cta_id' => $rowAuxiliar['cta_id'],
                'pdx_debe' => $rowAuxiliar['pdx_debe'],
                'pdx_haber' => $rowAuxiliar['pdx_haber'],
                'nat_id' => $rowAuxiliar['nat_id'],
                'pdx_concepto' => $rowAuxiliar['pdx_concepto'],
                'tdf_id' => $rowAuxiliar['tdf_id'],
                'pdx_referencia' => $rowAuxiliar['pdx_referencia'],
                'pdx_fechaReferencia' => $rowAuxiliar['pdx_fechaReferencia'],
                'ecf_id' => $rowAuxiliar['ecf_id'],
                'pdx_bdo_id_origen' => $rowAuxiliar['pdx_bdo_id_origen'],
                'mmc_id' => $rowAuxiliar['mmc_id'],
                'epl_id' => $rowAuxiliar['epl_id'],
                'pma_concepto' => $rowAuxiliar['pma_concepto'],
                'concepto' => $rowAuxiliar['concepto'],
                'pma_fecha' => $rowAuxiliar['pma_fecha'],
                'cta_cuenta' => $rowAuxiliar['cta_cuenta'],
                'cta_nombre' => $rowAuxiliar['cta_nombre'],
                'cgsc_naturalezaFija' => $rowAuxiliar['cgsc_naturalezaFija'],
                'cgsc_cuenta' => $rowAuxiliar['cgsc_cuenta'],
                'cgsc_nombre' => $rowAuxiliar['cgsc_nombre'],
                'ecf_clave' => $rowAuxiliar['ecf_clave']
            );
        }
    }

    return $datos;
}
?>