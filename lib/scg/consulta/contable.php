<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function contable($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico,
    $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles,
    $parametroExtra = array()) {
    $periodoSql = "AND msc_id = " . $periodo['mesDesde'];
    $cuentamayor = $parametroExtra['busqueda']['cuentaMayor'];

    $arrCta = explode(".", $cuentamayor);
    $elementos = count($arrCta);
    if ($elementos != 5) {
        return false;
    }
    $cuentaMayorSql = "AND x.ger_id = ? AND x.cgp_id = ? AND x.rub_id = ? AND x.cgcu_id = ? AND x.cgsc_id = ? ";
    $cuentaSefinSql = $parametroExtra['busqueda']['cuentaSefin'] != '' ? "AND x.cgc_id = '" . $parametroExtra['busqueda']['cuentaSefin'] . "' "
    : '';
    $cuentaRegistroSql = $parametroExtra['busqueda']['cuentaRegistro'] ? "AND x.cta_id = '" . $parametroExtra['busqueda']['cuentaRegistro'] . "' "
    : '';
    $gurSql = '';
    $clc_idSql = '';
    $cvefinSql = (($claveFinanciamiento != '') ? " AND ecf_id = " . $claveFinanciamiento . " " : "");
    $parametros = [$arrCta[0], $arrCta[1], $arrCta[2], $arrCta[3], $arrCta[4]];

    if ($parametroExtra['gur'] != '') {
        $gurSql = "AND pdx_gpo_id_vinculacion = " . substr($parametroExtra['gur'],
            0, 1) . " ";
        $gurSql .= "AND pdx_ur_id_vinculacion = " . substr($parametroExtra['gur'],
            1, 2) . " ";
    }
    if ($parametroExtra['clc_id'] != '') {
        $clc_idSql = "AND pdx_clc_id_vinculacion = " . $parametroExtra['clc_id'] . " ";
    }

    // Armamos la consulta
    $sql = "
            SELECT m.gpo_id, m.ur_id, ur.ur_clave, ur.ur_nombre,
                x.cta_gpo_id, x.cta_ur_id, x.cta_ue_id,
                pc.cta_cuenta,
                x.epg_id, oa.prg_id, oa.spr_id, oa.py_id, oa.oa_id, CONCAT(ep.epg_clave,' ') AS epg_clave, oa.oa_nombre,
                tg.tg_id, tg.tg_nombre,
                i.idp_id, i.idp_nombre,
                pg.pg_id, pg.pg_nombre,
                pe.pe_id, pe.pe_nombre,
                tr.ter_id, tr.ter_nombre,
                ef.ecf_id, ef.ecf_clave, cf.cfi_nombre,
                pdx_clc_id_vinculacion AS clc_id,
                pdx_afe_id_vinculacion AS afe_id,
                pdx_cmp_id_vinculacion AS cmp_id,
                pdx_bdo_id_vinculacion AS bdo_id,
                CONCAT( pdx_gpo_id_vinculacion, pdx_ur_id_vinculacion ) AS gur,
                pdx_ejercicio,
                CONCAT( pot_id, '-', prf_id, pma_id ) AS poliza, pma_fecha,
                rfc_id, tdf_id, pdx_referencia, pdx_fechaReferencia,
                pdx_debe, pdx_haber,
                pdx_id AS id
            FROM " . BD_CTRL . ".cg_PolizaMaestro m
                INNER JOIN " . BD_CTRL . ".cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                INNER JOIN " . BD_CTRL . ".cg_PlanCuentas pc USING( cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id )
                LEFT JOIN " . BD_CTRL . ".cat_UnidadResponsable ur USING( gpo_id, ur_id )
                LEFT JOIN " . BD_CTRL . ".cat_PartidaGenerica pg USING( pg_id )
                LEFT JOIN " . BD_CTRL . ".cat_PartidaEspecifica pe USING( pg_id, pe_id )
                LEFT JOIN " . BD_CTRL . ".cat_IdentificadorPartida i USING( idp_id )
                LEFT JOIN " . BD_CTRL . ".cat_TipoGasto tg ON pe.tg_id = tg.tg_id
                LEFT JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento ef USING( ecf_id )
                LEFT JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING( cff_id,fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                LEFT JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " cs USING( cff_id,fte_id, sft_id, ori_id, sor_id )
                LEFT JOIN " . BD_SINPRES . ".cat_TipoRecurso tr USING( ter_id )
                LEFT JOIN " . BD_CTRL . ".enc_Programatico ep USING( epg_id )
                LEFT JOIN " . BD_CTRL . ".cat_ObraActividad oa ON ep.gpo_id = oa.gpo_id AND ep.ur_id = oa.ur_id AND ep.prg_id = oa.prg_id AND ep.spr_id = oa.spr_id AND ep.py_id = oa.py_id AND ep.oa_id = oa.oa_id
            " . (($reporte['tipo'] == 2 && $_SESSION[_S]['nvj_id'] < 6) ? "   INNER JOIN " . BD_CTRL . ".cat_CargaTrabajo t ON t.gpo_id = m.gpo_id AND t.ur_id = m.ur_id
            WHERE t.usr_id = " . $_SESSION[_S]['usr_id'] . "
                " : "WHERE m.gpo_id = $gpo_id AND m.ur_id = $ur_id ") . "
                " . (($ue_id != '') ? "AND x.cta_ue_id = $ue_id " : "") . "
                $periodoSql
                $cuentaMayorSql
                $cuentaSefinSql
                $cuentaRegistroSql
                $gurSql
                $clc_idSql
                $cvefinSql
                AND m.epl_id NOT IN ( 'C', 'H' )
            ORDER BY cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id, pma_fecha, nat_id
        ";

    $contable = new AccesoDatos(); //variable de konexion para los saldos iniciales
    $contable->conectar();
    $contable->setSql($sql);
    $contable->consultar($parametros);
    $contable->desconectar();

    $datos = array();

    while ($rowContable = $contable->extraer()) {

        $datos[$rowContable['id']] = array(
            'id' => $rowContable['id'],
            'gpo_id' => $rowContable['gpo_id'],
            'ur_id' => $rowContable['ur_id'],
            'ur_clave' => $rowContable['ur_clave'],
            'ur_nombre' => $rowContable['ur_nombre'],
            'cta_ue_id' => $rowContable['cta_ue_id'],
            'cta_cuenta' => $rowContable['cta_cuenta'],
            'pdx_ejercicio' => $rowContable['pdx_ejercicio'],
            'cmp_id' => $rowContable['cmp_id'],
            'gur' => $rowContable['gur'],
            'clc_id' => $rowContable['clc_id'],
            'afe_id' => $rowContable['afe_id'],
            'bdo_id' => $rowContable['bdo_id'],
            'poliza' => $rowContable['poliza'],
            'pma_fecha' => $rowContable['pma_fecha'],
            'rfc_id' => $rowContable['rfc_id'],
            'tdf_id' => $rowContable['tdf_id'],
            'pdx_referencia' => $rowContable['pdx_referencia'],
            'pdx_fechaReferencia' => $rowContable['pdx_fechaReferencia'],
            'epg_id' => $rowContable['epg_id'],
            'epg_clave' => $rowContable['epg_clave'],
            'oa_nombre' => $rowContable['oa_nombre'],
            'tg_id' => $rowContable['tg_id'],
            'tg_nombre' => $rowContable['tg_nombre'],
            'idp_id' => $rowContable['idp_id'],
            'idp_nombre' => $rowContable['idp_nombre'],
            'pg_id' => $rowContable['pg_id'],
            'pg_nombre' => $rowContable['pg_nombre'],
            'pe_id' => $rowContable['pe_id'],
            'pe_nombre' => $rowContable['pe_nombre'],
            'ter_id' => $rowContable['ter_id'],
            'ter_nombre' => $rowContable['ter_nombre'],
            'ecf_id' => $rowContable['ecf_id'],
            'ecf_clave' => $rowContable['ecf_clave'],
            'cfi_nombre' => $rowContable['cfi_nombre'],
            'pdx_debe' => $rowContable['pdx_debe'],
            'pdx_haber' => $rowContable['pdx_haber'],
        );
    }

    if (count($datos) == 0) {
        $datos['vacio'] = array(
            'id' => '',
            'gpo_id' => '',
            'ur_id' => '',
            'ur_clave' => '',
            'ur_nombre' => '',
            'cta_ue_id' => '',
            'cta_cuenta' => '',
            'pdx_ejercicio' => '',
            'gur' => '',
            'clc_id' => '',
            'afe_id' => '',
            'bdo_id' => '',
            'cmp_id' => '',
            'poliza' => '',
            'pma_fecha' => '',
            'rfc_id' => '',
            'tdf_id' => '',
            'pdx_referencia' => '',
            'pdx_fechaReferencia' => '',
            'epg_id' => '',
            'epg_clave' => '',
            'oa_nombre' => '',
            'tg_id' => '',
            'tg_nombre' => '',
            'idp_id' => '',
            'idp_nombre' => '',
            'pg_id' => '',
            'pg_nombre' => '',
            'pe_id' => '',
            'pe_nombre' => '',
            'ter_id' => '',
            'ter_nombre' => '',
            'ecf_id' => '',
            'ecf_clave' => '',
            'cfi_nombre' => '',
            'pdx_debe' => '',
            'pdx_haber' => '',
        )
        ;
    }
    return $datos;
}
