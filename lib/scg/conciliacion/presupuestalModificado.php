<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/../../MesAlias.php';

function presupuestalModificado($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array())
{

    $ordenar = "d.gpo_id, d.ur_id";
    $key = "d.gpo_id, d.ur_id";
    if ($niveles[2] > 0) {
        $ordenar .= ", d.ue_id";
        $key .= ", '.', d.ue_id";
    }
    if ($niveles[3] > 0) {
        $ordenar .= ", ep.epg_clave";
        $key .= ", '.', ep.epg_clave";
    }
    if ($niveles[4] > 0) {
        $ordenar .= ", g.tg_id";
        $key .= ", '.', g.tg_id";
    }
    if ($niveles[5] > 0) {
        $ordenar .= ", i.idp_id";
        $key .= ", '.', i.idp_id";
    }
    if ($niveles[6] > 0) {
        $ordenar .= ", pg.pg_id";
        $key .= ", '.', pg.pg_id";
    }
    if ($niveles[7] > 0) {
        $ordenar .= ", pe.pe_id";
        $key .= ", '.', pe.pe_id";
    }
    if ($niveles[8] > 0) {
        $ordenar .= ", tr.ter_id";
        $key .= ", '.', tr.ter_id";
    }
    if ($niveles[9] > 0) {
        $ordenar .= ", ef.ecf_clave";
        $key .= ", '.', ef.ecf_clave";
    }

    $periodoSql = ( $periodo['mesDesde'] == $periodo['mesHasta'] ? "AND msc_id_autorizacion = " . $periodo['mesDesde'] : "AND ( msc_id_autorizacion BETWEEN " . $periodo['mesDesde'] . " AND " . $periodo['mesHasta'] . " )" );
    $programaticoSql = ( $programatico['epg_id'] != '' ? "AND ep.epg_id = " . $programatico['epg_id'] : '' );
    $cvefinSql = ( $claveFinanciamiento['ecf_id'] != '' ? "AND ef.ecf_id = " . $claveFinanciamiento['ecf_id'] : ( $claveFinanciamiento['ter_id'] != '' ? "AND tr.ter_id = '" . $claveFinanciamiento['ter_id'] . "'" : '' ) );
    // Armamos la consulta

    $sql = "
        SELECT d.gpo_id, d.ur_id, ur.ur_clave, ur.ur_nombre, MAX(d.ue_id) AS ue_id,
        MAX(d.epg_id) AS epg_id, MAX(oa.prg_id) AS prg_id, MAX(oa.spr_id) AS spr_id, MAX(oa.py_id) AS py_id,
            MAX(oa.oa_id) AS oa_id, MAX(ep.epg_clave) AS epg_clave, MAX(oa.oa_nombre) AS oa_nombre,
            MAX(g.tg_id) AS tg_id, MAX(g.tg_nombre) AS tg_nombre,
            MAX(i.idp_id) AS idp_id, MAX(i.idp_nombre) AS idp_nombre,
            pg.pg_id, MAX(pg.pg_nombre) AS pg_nombre,
            MAX(pe.pe_id) AS pe_id, MAX(pe.pe_nombre) AS pe_nombre,
            MAX(tr.ter_id) AS ter_id, MAX(tr.ter_nombre) ter_nombre,
            MAX(ef.ecf_id) AS ecf_id, ef.ecf_clave, MAX(cf.cfi_nombre) AS cfi_nombre, 
            SUM( CASE mov_id WHEN 'R' THEN d.dmo_ene + d.dmo_feb + d.dmo_mar + d.dmo_abr + d.dmo_may + d.dmo_jun + d.dmo_jul + d.dmo_ago + d.dmo_sep + d.dmo_oct + d.dmo_nov + d.dmo_dic ELSE 0 END ) AS pre_reduccion, 
            SUM( CASE mov_id WHEN 'A' THEN d.dmo_ene + d.dmo_feb + d.dmo_mar + d.dmo_abr + d.dmo_may + d.dmo_jun + d.dmo_jul + d.dmo_ago + d.dmo_sep + d.dmo_oct + d.dmo_nov + d.dmo_dic ELSE 0 END ) AS pre_ampliacion, 
            GROUP_CONCAT( DISTINCT m.mod_clave ) AS mod_clave, 
            CONCAT( $key ) AS id 
        FROM scp_MaestroModificacion m 
            INNER JOIN scp_DetalleModificacion d USING( omo_id, mod_gpo_id, mod_ur_id, mod_id )
            INNER JOIN cat_PartidaGenerica pg USING( pg_id ) 
            INNER JOIN cat_PartidaEspecifica pe USING( pg_id, pe_id ) 
            INNER JOIN cat_IdentificadorPartida i USING( idp_id ) 
            INNER JOIN cat_TipoGasto g ON pe.tg_id = g.tg_id 
            INNER JOIN enc_ClaveFinanciamiento ef ON d.ecf_id = ef.ecf_id 
            INNER JOIN ".BD_SINPRES.".cat_ClaveFinanciamiento".APP_EJERCICIO." cf ON ef.cff_id = cf.cff_id AND ef.fte_id = cf.fte_id AND ef.sft_id = cf.sft_id AND ef.ori_id = cf.ori_id AND ef.sor_id = cf.sor_id AND ef.cfi_id = cf.cfi_id AND ef.cfi_anio = cf.cfi_anio
            INNER JOIN ".BD_SINPRES.".cat_SuborigenRecurso".APP_EJERCICIO." cs ON ef.cff_id = cf.cff_id AND ef.fte_id = cs.fte_id AND ef.sft_id = cs.sft_id AND ef.ori_id = cs.ori_id AND ef.sor_id = cs.sor_id
            INNER JOIN ".BD_SINPRES.".cat_TipoRecurso tr USING( ter_id ) 
            INNER JOIN enc_Programatico ep USING( epg_id ) 
            INNER JOIN cat_ObraActividad oa ON ep.gpo_id = oa.gpo_id AND ep.ur_id = oa.ur_id AND ep.prg_id = oa.prg_id AND ep.spr_id = oa.spr_id AND ep.py_id = oa.py_id AND ep.oa_id = oa.oa_id 
            INNER JOIN cat_UnidadResponsable ur ON d.gpo_id = ur.gpo_id AND d.ur_id = ur.ur_id 
        " . ( ( $reporte['tipo'] == 2 && $_SESSION[_S]['nvj_id'] < 6 ) ? "   INNER JOIN cat_CargaTrabajo t ON t.gpo_id = d.gpo_id AND t.ur_id = d.ur_id
        WHERE t.usr_id = " . $_SESSION[_S]['usr_id'] . "
            " : "WHERE d.gpo_id = $gpo_id AND d.ur_id = $ur_id " ) . "
            AND emo_id IN ( 14, 15, 16 ) 
            AND d.mov_id != 'C' 
            $periodoSql 
            " . ( ($ue_id != '') ? "AND d.ue_id = $ue_id" : "" ) . " 
            $programaticoSql 
            " . ( $claveFinanciamiento['ter_id'] == 'C' ? 'AND tr.ter_id IS NOT NULL' : $cvefinSql ) . "
        GROUP BY $ordenar 
        HAVING !( pre_reduccion = 0 AND pre_ampliacion = 0 ) 
        ORDER BY $ordenar 
    ";

    //variable de conexion para los saldos iniciales
    $presupuestal = new AccesoDatos();
    $presupuestal->conectar();
    $presupuestal->setSql($sql);
    $presupuestal->consultar();
    $presupuestal->desconectar();

    $datos = array();

    while ($rowPresupuestal = $presupuestal->extraer()) {

        $mod_clave = explode(',', $rowPresupuestal['mod_clave']);
        $mod_id = '( ';
        foreach ($mod_clave as $key => $value) {
            $mod_id .= substr($value, 0, 1) . ', ' . (substr($value, 4, 6) * 1) . ' ), ( ';
        }

        $mod_id = substr($mod_id, 0, -4);

        $datos[$rowPresupuestal['id']] = array(
            'id' => $rowPresupuestal['id'],
            'gpo_id' => $rowPresupuestal['gpo_id'],
            'ur_id' => $rowPresupuestal['ur_id'],
            'ur_clave' => $rowPresupuestal['ur_clave'],
            'ur_nombre' => $rowPresupuestal['ur_nombre'],
            'ue_id' => $rowPresupuestal['ue_id'],
            'epg_id' => $rowPresupuestal['epg_id'],
            'oa_clave' => $rowPresupuestal['epg_clave'],
            'oa_nombre' => $rowPresupuestal['oa_nombre'],
            'tg_id' => $rowPresupuestal['tg_id'],
            'tg_nombre' => $rowPresupuestal['tg_nombre'],
            'idp_id' => $rowPresupuestal['idp_id'],
            'idp_nombre' => $rowPresupuestal['idp_nombre'],
            'pg_id' => $rowPresupuestal['pg_id'],
            'pg_nombre' => $rowPresupuestal['pg_nombre'],
            'pe_id' => $rowPresupuestal['pe_id'],
            'pe_nombre' => $rowPresupuestal['pe_nombre'],
            'ter_id' => $rowPresupuestal['ter_id'],
            'ter_nombre' => $rowPresupuestal['ter_nombre'],
            'ecf_id' => $rowPresupuestal['ecf_id'],
            'cfi_clave' => $rowPresupuestal['ecf_clave'],
            'cfi_nombre' => $rowPresupuestal['cfi_nombre'],
            'pre_reduccion' => $rowPresupuestal['pre_reduccion'],
            'pre_ampliacion' => $rowPresupuestal['pre_ampliacion'],
            'mod_clave' => $rowPresupuestal['mod_clave'],
            'mod_id' => $mod_id
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
            'epg_id' => '',
            'oa_clave' => '',
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
            'cfi_clave' => '',
            'cfi_nombre' => '',
            'pre_reduccion' => 0,
            'pre_ampliacion' => 0,
            'mod_clave' => '',
            'mod_id' => ''
        );
    }
    return $datos;
}