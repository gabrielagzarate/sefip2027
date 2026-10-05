<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function contableModificado($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array())
{

    $params = [];
    $sqlTipoReporte = '';
    $sqlUe = '';
    $periodoSql = ( $periodo['mesDesde'] == $periodo['mesHasta'] ? "AND msc_id = " . $periodo['mesDesde'] : "AND ( msc_id BETWEEN " . $periodo['mesDesde'] . " AND " . $periodo['mesHasta'] . " )" );
    $programaticoSql = ( $programatico['epg_id'] != '' ? "AND epg_id = " . $programatico['epg_id'] : '' );
    $cvefinSql = ( $claveFinanciamiento['ecf_id'] != '' ? "AND ecf_id = " . $claveFinanciamiento['ecf_id'] : ( $claveFinanciamiento['ter_id'] != '' ? "AND ter_id = '" . $claveFinanciamiento['ter_id'] . "'" : '' ) );

    $ordenar = "m.gpo_id, m.ur_id";
    $ordenart = "gpo_id, ur_id";
    $key = "m.gpo_id, m.ur_id";
    $keyt = "gpo_id, ur_id";
    if ($niveles[2] > 0) {
        $ordenar .= ", x.cta_ue_id";
        $ordenart .= ", cta_ue_id";
        $key .= ", '.', x.cta_ue_id";
        $keyt .= ", '.', cta_ue_id";
    }
    if ($niveles[3] > 0) {
        $ordenar .= ", ep.epg_clave";
        $ordenart .= ", epg_clave";
        $key .= ", '.', ep.epg_clave";
        $keyt .= ", '.', epg_clave";
    }
    if ($niveles[4] > 0) {
        $ordenar .= ", g.tg_id";
        $ordenart .= ", tg_id";
        $key .= ", '.', g.tg_id";
        $keyt .= ", '.', tg_id";
    }
    if ($niveles[5] > 0) {
        $ordenar .= ", i.idp_id";
        $ordenart .= ", idp_id";
        $key .= ", '.', i.idp_id";
        $keyt .= ", '.', idp_id";
    }
    if ($niveles[6] > 0) {
        $ordenar .= ", pg.pg_id";
        $ordenart .= ", pg_id";
        $key .= ", '.', pg.pg_id";
        $keyt .= ", '.', pg_id";
    }
    if ($niveles[7] > 0) {
        $ordenar .= ", pe.pe_id";
        $ordenart .= ", pe_id";
        $key .= ", '.', pe.pe_id";
        $keyt .= ", '.', pe_id";
    }
    if ($niveles[8] > 0) {
        $ordenar .= ", tr.ter_id";
        $ordenart .= ", ter_id";
        $key .= ", '.', tr.ter_id";
        $keyt .= ", '.', ter_id";
    }
    if ($niveles[9] > 0) {
        $ordenar .= ", ef.ecf_clave";
        $ordenart .= ", ecf_clave";
        $key .= ", '.', ef.ecf_clave";
        $keyt .= ", '.', ecf_clave";
    }

    if ($reporte['tipo'] == 2 && $_SESSION[_S]['nvj_id'] < 6) {
        $sqlTipoReporte .= " 
            INNER JOIN cat_CargaTrabajo t ON t.gpo_id = m.gpo_id AND t.ur_id = m.ur_id
            WHERE t.usr_id = :usr_id
        ";
        $params[':usr_id'] = $_SESSION[_S]['usr_id'];
    } else {
        $sqlTipoReporte .= " WHERE m.gpo_id = :gpo_id AND m.ur_id = :ur_id ";
        $params[':gpo_id'] = $gpo_id;
        $params[':ur_id'] = $ur_id;
    }

    if ($ue_id != '') {
        $sqlUe .= " AND x.cta_ue_id = :ue_id ";
       
        $params[':ue_id'] = $ue_id;
    }

    // Armamos la consulta
    // -----------------------------------------------------------------------------------------------------------------------
    $sql = "
            SELECT m.gpo_id, m.ur_id, ur.ur_clave, ur.ur_nombre, MAX(x.cta_ue_id) AS ue_id,
                MAX(x.epg_id) AS epg_id, MAX(oa.prg_id) AS prg_id, MAX(oa.spr_id) AS spr_id,
                MAX(oa.py_id) AS py_id, MAX(oa.oa_id) AS oa_id, MAX(ep.epg_clave) AS epg_clave, MAX(oa.oa_nombre) AS oa_nombre,
                MAX(g.tg_id) AS tg_id, MAX(g.tg_nombre) AS tg_nombre,
                MAX(i.idp_id) AS idp_id, MAX(i.idp_nombre) AS idp_nombre,
                pg.pg_id, pg.pg_nombre,
                MAX(pe.pe_id) AS pe_id, MAX(pe.pe_nombre) AS pe_nombre,
                MAX(tr.ter_id) AS ter_id, MAX(tr.ter_nombre) AS ter_nombre,
                MAX(ef.ecf_id) AS ecf_id, ef.ecf_clave, MAX(cf.cfi_nombre) AS cfi_nombre, 
                SUM( x.pdx_debe ) AS pdx_reduccion, 
                SUM( x.pdx_haber ) AS pdx_ampliacion, 
                GROUP_CONCAT( DISTINCT CONCAT( x.pdx_tipoPresupuestal, m.gpo_id, m.ur_id, LPAD( x.pdx_mod_id_vinculacion, 6, 0 ) ) ) AS mod_clave, 
                GROUP_CONCAT( DISTINCT CONCAT( pot_id, '-', prf_id, pma_id ) ) AS poliza, 
                CONCAT( $key ) AS id 
            FROM cg_PolizaMaestro m 
                INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
                INNER JOIN cat_PartidaGenerica pg USING( pg_id ) 
                INNER JOIN cat_PartidaEspecifica pe USING( pg_id, pe_id ) 
                INNER JOIN cat_IdentificadorPartida i USING( idp_id ) 
                INNER JOIN cat_TipoGasto g ON pe.tg_id = g.tg_id 
                INNER JOIN enc_ClaveFinanciamiento ef USING( ecf_id ) 
                INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO ." cf USING( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio ) 
                INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO ." cs USING( cff_id, fte_id, sft_id, ori_id, sor_id  ) 
                INNER JOIN " . BD_SINPRES . ".cat_TipoRecurso tr USING( ter_id ) 
                LEFT JOIN enc_Programatico ep USING( epg_id ) 
                LEFT JOIN cat_ObraActividad oa ON ep.gpo_id = oa.gpo_id AND ep.ur_id = oa.ur_id AND ep.prg_id = oa.prg_id AND ep.spr_id = oa.spr_id AND ep.py_id = oa.py_id AND ep.oa_id = oa.oa_id 
                INNER JOIN cat_UnidadResponsable ur ON m.gpo_id = ur.gpo_id AND m.ur_id = ur.ur_id 
              $sqlTipoReporte  $sqlUe
                $periodoSql
                AND m.mmc_id = 'M' 
                AND x.ger_id = 8 AND x.cgp_id = 2 AND x.rub_id = 3 AND x.cgcu_id = 0 AND x.cgsc_id = 0 
                AND m.epl_id NOT IN ( 'C', 'H' ) 
                $programaticoSql
                " . ( $claveFinanciamiento['ter_id'] == 'C' ? 'AND tr.ter_id IS NOT NULL' : $cvefinSql ) . "
            GROUP BY $ordenar
            ORDER BY $ordenar
        ";

    //variable de conexion para los saldos iniciales
    $contable = new AccesoDatos();
    $contable->conectar();
    $contable->setSql($sql);
    $contable->consultar($params);
    $contable->desconectar();

    $datos = array();

    while ($rowContable = $contable->extraer()) {
        $mod_clave = explode(',', $rowContable['mod_clave']);

        $mod_id = '( ';
        foreach ($mod_clave as $key => $value) {
            $mod_id .= substr($value, 0, 1) . ', ' . (substr($value, 4, 6) * 1) . ' ), ( ';
        }

        $mod_id = substr($mod_id, 0, -4);

        $datos[$rowContable['id']] = array(
            'id' => $rowContable['id'],
            'gpo_id' => $rowContable['gpo_id'],
            'ur_id' => $rowContable['ur_id'],
            'ur_clave' => $rowContable['ur_clave'],
            'ur_nombre' => $rowContable['ur_nombre'],
            'ue_id' => $rowContable['ue_id'],
            'epg_id' => $rowContable['epg_id'],
            'oa_clave' => $rowContable['epg_clave'],
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
            'cfi_clave' => $rowContable['ecf_clave'],
            'cfi_nombre' => $rowContable['cfi_nombre'],
            'pdx_reduccion' => $rowContable['pdx_reduccion'],
            'pdx_ampliacion' => $rowContable['pdx_ampliacion'],
            'mod_clave' => $rowContable['mod_clave'],
            'mod_id' => $mod_id,
            'poliza' => $rowContable['poliza']
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
            'pdx_reduccion' => 0,
            'pdx_ampliacion' => 0,
            'mod_clave' => '',
            'mod_id' => '',
            'poliza' => ''
                )
        ;
    }

    return $datos;
}

?>