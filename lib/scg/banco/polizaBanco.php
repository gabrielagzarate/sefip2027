<?php
require_once dirname(__FILE__) . '/../../../lib/constantes.php';;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function polizaBanco($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array())
{

    $periodoSql = ($periodo['mesDesde'] == $periodo['mesHasta'] ? "AND x.msc_id = " . $periodo['mesDesde'] : "AND ( x.msc_id BETWEEN " . $periodo['mesDesde'] . " AND " . $periodo['mesHasta'] . " )");
    $cvefinSql = ($claveFinanciamiento['ecf_id'] != '' ? "AND ecf_id = " . $claveFinanciamiento['ecf_id'] : ($claveFinanciamiento['ter_id'] != '' ? "AND ter_id = '" . $claveFinanciamiento['ter_id'] . "'" : ''));

    $cuentascontable = "";
    if ($gpo_id . $ur_id == 116) {
        $cuentascontable = "AND ger_id = 1 AND cgp_id = 1 AND rub_id = 1 AND (cgcu_id = 2 or cgcu_id = 4) AND cgsc_id = 1 ";
    } else {
        $cuentascontable = "AND ger_id = 1 AND cgp_id = 1 AND rub_id = 1 AND cgcu_id = 3 AND cgsc_id = 1 ";
    }
    switch ($pot_id) {
        case 'I':
            $referenciaSql = "MAX(t.tdf_id) AS tdf_id, MAX(t.bdm_referencia) AS referencia, MAX(c.ing_fechaPago) AS fechaReferencia ";
            $referenciaSql2 = "MAX(t.tdf_id) AS tdf_id , MAX(t.bdm_referencia) AS referencia, MAX(t.bdm_fechaAutorizado) AS fechaReferencia";
            $referenciaSql3 = "MAX(t.tdf_id) AS tdf_id , MAX(t.bdm_referencia) AS referencia, MAX(t.bdm_fechaAutorizado) AS fechaReferencia   ";

            $joinSql = "INNER JOIN ctrl2016.ing_BancoDocumentoMinistrado t USING( gpo_id, ur_id, clc_id ) ";
            $joinSql2 = "INNER JOIN ".BD_CTRLANT.".min_BancoDocumentoMinistrado t USING( gpo_id, ur_id, clc_id ) ";
            $joinSql3 = "INNER JOIN ".BD_CTRLANT.".min_BancoDocumentoMinistrado t USING( gpo_id, ur_id, clc_id ) ";
            $joinSql4 = "INNER JOIN tesoreria.min_BancoDocumentoMinistrado_" . APP_EJERCICIO_ANTERIOR . " t USING( gpo_id, ur_id, clc_id ) ";

            $importeSql = "x.pdx_debe";
            $groupBySql = "bdm_referencia, ";
            $orderBySql = "bdm_referencia, ";
            $wherefech = "AND YEAR( ing_fechaPago ) = " . APP_EJERCICIO_ANTERIOR . "";
            $wheretipo = "AND pdx_debe != 0 ";
            break;
        case 'E':
            $referenciaSql = "MAX(t.tdf_id) AS tdf_id , MAX(t.bdp_referencia) AS referencia, MAX(t.bdp_fechaAutorizado) AS fechaReferencia ";
            $referenciaSql2 = "MAX(t.tdf_id) AS tdf_id , MAX(t.bdp_referencia) AS referencia, MAX(t.bdp_fechaAutorizado) AS fechaReferencia ";
            $referenciaSql3 = "MAX(t.tdf_id) AS tdf_id , MAX(t.bdp_referencia) AS referencia, MAX(t.bdp_fechaAutorizado) AS fechaReferencia ";

            $joinSql = "INNER JOIN ctrl2016.cg_BancoDocumentoPagado t USING( gpo_id, ur_id, clc_id ) ";
            $joinSql2 = "INNER JOIN ".BD_CTRLANT.".cg_BancoDocumentoPagado t USING( gpo_id, ur_id, clc_id )  ";
            $joinSql3 = "INNER JOIN ".BD_CTRLANT.".cg_BancoDocumentoPagado t USING( gpo_id, ur_id, clc_id )  "; //esta tabla aun no se crea
            $joinSql4 = "INNER JOIN cg_BancoDocumentoPagado t USING( gpo_id, ur_id, clc_id )  "; //esta tabla aun no se crea
            $importeSql = "x.pdx_haber";
            $groupBySql = "bdp_referencia,";
            $orderBySql = "bdp_referencia,";
            $wherefech = "AND YEAR( t.bdp_fechaAutorizado ) =  " . APP_EJERCICIO_ANTERIOR . " ";
            $wheretipo = "AND pdx_haber != 0 ";
            break;
        default:
            echo 'kym';
            break;
    }

    // Armamos la consulta
    // -----------------------------------------------------------------------------------------------------------------------
    $sql = "
                SELECT x.msc_id,
                x.gpo_id, x.ur_id, u.ur_clave, u.ur_nombre,
                MAX(x.cta_ue_id) AS ue_id,
                SUM( pdx_importe ) AS pdx_importe,
                x.tdf_id_poliza, x.pdx_referencia, MAX(x.pdx_fechaReferencia) AS pdx_fechaReferencia,
                MAX(x.cgc_id) AS cgc_id, MAX(x.cgc_nombre) AS cgc_nombre, MAX(x.cta_id) AS cta_id, MAX(x.cta_nombre) AS cta_nombre,
                GROUP_CONCAT( DISTINCT x.afe_id ORDER BY x.afe_id ) AS afe_id,
                GROUP_CONCAT( DISTINCT x.clc_id ORDER BY x.clc_id ) AS clc_id,
                GROUP_CONCAT( DISTINCT x.bdo_id ORDER BY x.bdo_id ) AS bdo_id,
                MAX(x.ecf_id) AS ecf_id,
                MAX(x.ter_id) AS ter_id, MAX(x.ter_nombre) AS ter_nombre,
                MAX(x.cfi_clave) AS cfi_clave, MAX(x.cfi_nombre) AS cfi_nombre,
                MAX(c.bur_cuentaBancaria) AS bur_cuentaBancaria, MAX(c.bur_alias) AS bur_alias,
                MAX(c.iba_id) AS iba_id, MAX(c.iba_nombre) AS iba_nombre,
                MAX(c.tdf_id) AS tdf_id, MAX(c.referencia) AS referencia, MAX(c.fechaReferencia) AS fechaReferencia,
                MAX(x.pma_fecha) AS pma_fecha, GROUP_CONCAT( DISTINCT x.poliza ORDER BY x.poliza ) AS poliza,
                x.pdx_ejercicio
        FROM (
            SELECT m.msc_id, x.pdx_id,
                m.gpo_id, m.ur_id, x.cta_ue_id,
                $importeSql AS pdx_importe,
                x.tdf_id AS tdf_id_poliza, x.pdx_referencia, x.pdx_fechaReferencia,
                cc.cgc_id, cc.cgc_nombre, cp.cta_id, cp.cta_nombre,
                x.pdx_afe_id_origen AS afe_id, x.pdx_clc_id_origen AS clc_id, x.pdx_bdo_id_origen AS bdo_id,
                x.ecf_id,
                r.ter_id, r.ter_nombre,
                cf.cfi_clave, cf.cfi_nombre,
                m.pma_fecha, CONCAT( pot_id, '-', prf_id, pma_id ) AS poliza,
                x.pdx_ejercicio
            FROM cg_PolizaMaestro m
                INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                INNER JOIN cg_CuentaCuarto cc USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id )
                INNER JOIN cg_PlanCuentas cp USING( cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id )
                LEFT JOIN enc_ClaveFinanciamiento e USING( ecf_id )
                LEFT JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO_ANTERIOR . " cf USING( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                LEFT JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO_ANTERIOR . " c USING( cff_id, fte_id, sft_id, ori_id, sor_id )
                LEFT JOIN " . BD_SINPRES . ".cat_TipoRecurso r USING( ter_id )
            WHERE m.gpo_id = :gpo_id AND m.ur_id = :ur_id
                " . (($ue_id != '') ? "AND m.ue_id = $ue_id " : "") . "
                $periodoSql
                AND m.epl_id NOT IN ( 'C', 'H' )
                " . $cuentascontable . "
                " . ($claveFinanciamiento['ter_id'] == 'C' ? 'AND c.ter_id IS NOT NULL ' : $cvefinSql) . "
                " . $wheretipo . "
                AND x.pdx_ejercicio = " . APP_EJERCICIO_ANTERIOR . "
        )x
            LEFT JOIN (
                SELECT c.gpo_id, c.ur_id, c.clc_id, t.bdo_id,
                    c.bur_id, b.bur_cuentaBancaria, b.bur_alias, ib.iba_id, ib.iba_nombre,
                    $referenciaSql2
                FROM ctrl" . APP_EJERCICIO_ANTERIOR . ".scp_Clc c
                    $joinSql3
                    INNER JOIN ctrl" . APP_EJERCICIO_ANTERIOR . ".cat_BancosUr b USING( bur_id )
                    INNER JOIN ctrl" . APP_EJERCICIO_ANTERIOR . ".cat_InstitucionesBancarias ib USING( iba_id )
                    INNER JOIN " . BD_SINPRES . ".vin_UnidadResponsable_" . APP_EJERCICIO_ANTERIOR . "_" . APP_EJERCICIO . " vur" . APP_EJERCICIO . "
                    ON vur" . APP_EJERCICIO . ".gpo_id_" . APP_EJERCICIO_ANTERIOR . " = c.gpo_id AND vur" . APP_EJERCICIO . ".ur_id_" . APP_EJERCICIO_ANTERIOR . " = c.ur_id
                WHERE c.gpo_id = :gpo_id AND c.ur_id = :ur_id
                    AND c.ecl_id IN ( 13, 16 )
                    AND b.bur_id != 0
                    AND b.gpo_id = :gpo_id AND b.ur_id = :ur_id
                    AND ejr_id_clc=" . APP_EJERCICIO_ANTERIOR . "
                GROUP BY c.gpo_id, c.ur_id, clc_id, bdo_id
            )c USING( gpo_id, ur_id, clc_id, bdo_id )
            INNER JOIN ctrl" . APP_EJERCICIO_ANTERIOR . ".cat_UnidadResponsable u USING( gpo_id, ur_id )
        GROUP BY gpo_id, ur_id, x.msc_id, c.tdf_id, c.referencia, x.tdf_id_poliza, x.pdx_referencia, x.cta_id
    UNION ALL 
        SELECT x.msc_id,
                x.gpo_id, x.ur_id, u.ur_clave, u.ur_nombre,
                MAX(x.cta_ue_id) AS ue_id,
                SUM( pdx_importe ) AS pdx_importe,
                x.tdf_id_poliza, x.pdx_referencia, MAX(x.pdx_fechaReferencia) AS pdx_fechaReferencia,
                MAX(x.cgc_id) AS cgc_id, MAX(x.cgc_nombre) AS cgc_nombre, MAX(x.cta_id) AS cta_id, MAX(x.cta_nombre) AS cta_nombre,
                GROUP_CONCAT( DISTINCT x.afe_id ORDER BY x.afe_id ) AS afe_id,
                GROUP_CONCAT( DISTINCT x.clc_id ORDER BY x.clc_id ) AS clc_id,
                GROUP_CONCAT( DISTINCT x.bdo_id ORDER BY x.bdo_id ) AS bdo_id,
                MAX(x.ecf_id) AS ecf_id,
                MAX(x.ter_id) AS ter_id, MAX(x.ter_nombre) AS ter_nombre,
                MAX(x.cfi_clave) AS cfi_clave, MAX(x.cfi_nombre) AS cfi_nombre,
                MAX(c.bur_cuentaBancaria) AS bur_cuentaBancaria, MAX(c.bur_alias) AS bur_alias,
                MAX(c.iba_id) AS iba_id, MAX(c.iba_nombre) AS iba_nombre,
                MAX(c.tdf_id) AS tdf_id, MAX(c.referencia) AS referencia, MAX(c.fechaReferencia) AS fechaReferencia,
                MAX(x.pma_fecha) AS pma_fecha, GROUP_CONCAT( DISTINCT x.poliza ORDER BY x.poliza ) AS poliza,
                x.pdx_ejercicio
        FROM (
            SELECT m.msc_id, x.pdx_id,
                m.gpo_id, m.ur_id, x.cta_ue_id,
                $importeSql AS pdx_importe,
                x.tdf_id AS tdf_id_poliza, x.pdx_referencia, x.pdx_fechaReferencia,
                cc.cgc_id, cc.cgc_nombre, cp.cta_id, cp.cta_nombre,
                x.pdx_afe_id_origen AS afe_id, x.pdx_clc_id_origen AS clc_id, x.pdx_bdo_id_origen AS bdo_id,
                x.ecf_id,
                r.ter_id, r.ter_nombre,
                cf.cfi_clave, cf.cfi_nombre,
                m.pma_fecha, CONCAT( pot_id, '-', prf_id, pma_id ) AS poliza,
                x.pdx_ejercicio
            FROM cg_PolizaMaestro m
                INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                INNER JOIN cg_CuentaCuarto cc USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id )
                INNER JOIN cg_PlanCuentas cp USING( cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id )
                LEFT JOIN enc_ClaveFinanciamiento e USING( ecf_id )
                LEFT JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                LEFT JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " USING( cff_id, fte_id, sft_id, ori_id, sor_id )
                LEFT JOIN " . BD_SINPRES . ".cat_TipoRecurso r USING( ter_id )
            WHERE m.gpo_id = :gpo_id AND m.ur_id = :ur_id
                " . (($ue_id != '') ? "AND m.ue_id = $ue_id " : "") . "
                $periodoSql
                AND m.epl_id NOT IN ( 'C', 'H' )
                " . $cuentascontable . "
                " . ($claveFinanciamiento['ter_id'] == 'C' ? 'AND c.ter_id IS NOT NULL ' : $cvefinSql) . "
                " . $wheretipo . "
                AND x.pdx_ejercicio = " . APP_EJERCICIO . "
        )x
            LEFT JOIN (
                SELECT c.gpo_id, c.ur_id, c.clc_id, t.bdo_id,
                    c.bur_id, b.bur_cuentaBancaria, b.bur_alias, ib.iba_id, ib.iba_nombre,
                    $referenciaSql3
                FROM scp_Clc c
                    $joinSql4
                    INNER JOIN cat_BancosUr b USING( bur_id )
                    INNER JOIN cat_InstitucionesBancarias ib USING( iba_id )
                WHERE c.gpo_id = :gpo_id AND c.ur_id = :ur_id
                    AND c.ecl_id IN ( 13, 16 )
                    AND b.bur_id != 0
                    AND b.gpo_id = :gpo_id AND b.ur_id = :ur_id
                GROUP BY c.gpo_id, c.ur_id, clc_id, bdo_id
            )c USING( gpo_id, ur_id, clc_id, bdo_id )
            INNER JOIN cat_UnidadResponsable u USING( gpo_id, ur_id )
        GROUP BY gpo_id, ur_id, x.msc_id, c.tdf_id, c.referencia, x.tdf_id_poliza, x.pdx_referencia, x.cta_id
    ";
    $params = [':gpo_id' => $gpo_id, ':ur_id' => $ur_id];
    $contable = new AccesoDatos(); //variable de konexion para los saldos iniciales
    $contable->conectar();
    $contable->setSql($sql);
    $contable->consultar($params);
    $contable->desconectar();
    $datos = array();

    while ($rowContable = $contable->extraer()) {
        $datos[$rowContable['gpo_id'] . '.' . $rowContable['ur_id'] . '.' . $rowContable['msc_id'] . '.' . $rowContable['tdf_id'] . '.' . $rowContable['referencia'] . '.' . $rowContable['tdf_id_poliza'] . '.' . $rowContable['pdx_referencia'] . '.' . $rowContable['cta_id']] = array(
            'id' => $rowContable['gpo_id'] . '.' . $rowContable['ur_id'] . '.' . $rowContable['msc_id'] . '.' . $rowContable['tdf_id'] . '.' . $rowContable['referencia'] . '.' . $rowContable['tdf_id_poliza'] . '.' . $rowContable['pdx_referencia'] . '.' . $rowContable['cta_id'],
            'gpo_id' => $rowContable['gpo_id'],
            'ur_id' => $rowContable['ur_id'],
            'ur_clave' => $rowContable['ur_clave'],
            'ur_nombre' => $rowContable['ur_nombre'],
            'ue_id' => $rowContable['ue_id'],
            'pma_fecha' => $rowContable['pma_fecha'],
            'tdf_id_poliza' => $rowContable['tdf_id_poliza'],
            'pdx_referencia' => $rowContable['pdx_referencia'],
            'pdx_fechaReferencia' => $rowContable['pdx_fechaReferencia'],
            'tdf_id' => $rowContable['tdf_id'],
            'referencia' => $rowContable['referencia'],
            'fechaReferencia' => $rowContable['fechaReferencia'],
            'cgc_id' => $rowContable['cgc_id'],
            'cgc_nombre' => $rowContable['cgc_nombre'],
            'cta_id' => $rowContable['cta_id'],
            'cta_nombre' => $rowContable['cta_nombre'],
            'bur_cuentaBancaria' => $rowContable['bur_cuentaBancaria'],
            'bur_alias' => $rowContable['bur_alias'],
            'iba_id' => $rowContable['iba_id'],
            'iba_nombre' => $rowContable['iba_nombre'],
            'ter_id' => $rowContable['ter_id'],
            'ter_nombre' => $rowContable['ter_nombre'],
            'ecf_id' => $rowContable['ecf_id'],
            'cfi_clave' => $rowContable['cfi_clave'],
            'cfi_nombre' => $rowContable['cfi_nombre'],
            'pdx_importe' => $rowContable['pdx_importe'],
            'afe_id' => $rowContable['afe_id'],
            'clc_id' => $rowContable['clc_id'],
            'bdo_id' => $rowContable['bdo_id'],
            'poliza' => $rowContable['poliza'],
            'pdx_ejercicio' => $rowContable['pdx_ejercicio']
        );
    }

    if (count($datos) == 0) {

        $datos['vacio'] = array(
            'id' => '',
            'gpo_id' => '',
            'ur_id' => '',
            'ur_clave' => '',
            'ur_nombre' => '',
            'ue_id' => '',
            'pma_fecha' => '',
            'tdf_id_poliza' => '',
            'pdx_referencia' => '',
            'pdx_fechaReferencia' => '',
            'tdf_id' => '',
            'referencia' => '',
            'fechaReferencia' => '',
            'cgc_id' => '',
            'cgc_nombre' => '',
            'cta_id' => '',
            'cta_nombre' => '',
            'bur_cuentaBancaria' => '',
            'bur_alias' => '',
            'iba_id' => '',
            'iba_nombre' => '',
            'ter_id' => '',
            'ter_nombre' => '',
            'ecf_id' => '',
            'cfi_clave' => '',
            'cfi_nombre' => '',
            'pdx_importe' => '',
            'afe_id' => '',
            'clc_id' => '',
            'bdo_id' => '',
            'poliza' => '',
            'pdx_ejercicio' => ''
        );
    }
    return $datos;
}
