<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/../../MesAlias.php';

function presupuestal($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array())
{

    $ordenar = "p.gpo_id, p.ur_id";
    $key = "p.gpo_id, p.ur_id";
    $maxpg_id = "MAX";
    $maxecf_clave = "MAX";
    if ($niveles[2] > 0) {
        $ordenar .= ", p.ue_id";
        $key .= ", '.', p.ue_id";
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
        $maxpg_id = "";
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
        $maxecf_clave = "";
    }

    switch ($mmc_id) {
        case 'C':
            $momentoSql = 'pc.com';
            $rectificadoSql = 'r.com';
            break;
        case 'D':
            $momentoSql = 'pd.dev';
            $rectificadoSql = 'r.dev';
            break;
        case 'E':
            $momentoSql = 'pej.eje';
            $rectificadoSql = 'r.eje';
            break;
        case 'T':
            $momentoSql = 'pm.min';
            $rectificadoSql = 'r.min';
            break;
        case 'P':
            $momentoSql = 'pp.pag';
            $rectificadoSql = 'r.pag';
            break;
        default:
            echo 'kym';
            break;
    }

    $periodoMomentoSql = $momentoSql . "_" . MesAlias($periodo['mesDesde']);
    $periodoRectificadoSql = $rectificadoSql . "_" . MesAlias($periodo['mesDesde']);

    for ($i = $periodo['mesDesde'] + 1; $i <= $periodo['mesHasta']; $i++) {
        $periodoMomentoSql .= " + " . $momentoSql . "_" . MesAlias($i);
        $periodoRectificadoSql .= " + " . $rectificadoSql . "_" . MesAlias($i);
    }
    $parametros = [];
    $programaticoSql = ($programatico['epg_id'] != '' ? "AND epg_id = :epg_id" : '');
    $cvefinSql = ($claveFinanciamiento['ecf_id'] != '' ? "AND ecf_id = :ecf_id" : ($claveFinanciamiento['ter_id'] != '' ? "AND tr.ter_id = :ter_id" : ''));

    // Armamos la consulta
    // -----------------------------------------------------------------------------------------------------------------------
    if ($programaticoSql !=''){
        $parametros += [
            'epg_id' => $programatico['epg_id']
        ];
    }
    if ($claveFinanciamiento['ecf_id'] != ''){
        $parametros += [
            'ecf_id' => $claveFinanciamiento['ecf_id'],
        ];
    }
    else{
        if($claveFinanciamiento['ter_id'] != ''){
            $parametros += ['ter_id' => $claveFinanciamiento['ter_id'],];
        }
    }
    if ($_SESSION[_S]['nvj_id'] > 5 && $reporte['tipo'] != 1){
        $where = 'WHERE 1';
    }
    else {
        if ($ue_id != ''){
            $parametros += ['ue_id' => $ue_id,];
        }
    }
    $sql = "
        SELECT p.gpo_id, p.ur_id, ur.ur_clave, ur.ur_nombre, MAX(p.ue_id) AS ue_id,
            MAX(p.epg_id) AS epg_id, MAX(oa.prg_id) AS prg_id, MAX(oa.spr_id) AS spr_id, MAX(oa.py_id) AS py_id,
            MAX(oa.oa_id) AS oa_id, MAX(ep.epg_clave) AS epg_clave, MAX(oa.oa_nombre) AS oa_nombre,
            MAX(g.tg_id) AS tg_id, MAX(g.tg_nombre) AS tg_nombre,
            MAX(i.idp_id) AS idp_id, MAX(i.idp_nombre) AS idp_nombre,
            $maxpg_id(pg.pg_id) AS pg_id, MAX(pg.pg_nombre) AS pg_nombre,
            MAX(pe.pe_id) AS pe_id, MAX(pe.pe_nombre) AS pe_nombre,
            MAX(tr.ter_id) AS ter_id, MAX(tr.ter_nombre) ter_nombre,
            MAX(ef.ecf_id) AS ecf_id, $maxecf_clave(ef.ecf_clave) AS ecf_clave, MAX(cf.cfi_nombre) AS cfi_nombre,
            SUM( $periodoMomentoSql ) AS pre_importe,
            COALESCE( SUM( $periodoRectificadoSql ), 0 ) AS rec_importe,
            CONCAT( $key ) AS id
        FROM presupuesto p
            LEFT JOIN presupuestoRectificado r USING( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
            LEFT JOIN presupuestoComprometido pc USING( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
            LEFT JOIN presupuestoDevengado pd USING( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
            LEFT JOIN presupuestoEjercido pej USING( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
            LEFT JOIN presupuestoPagado pp USING( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
            LEFT JOIN presupuestoMinistrado pm USING( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
            INNER JOIN cat_UnidadResponsable ur USING( gpo_id, ur_id )
            INNER JOIN cat_PartidaGenerica pg USING( pg_id )
            INNER JOIN cat_PartidaEspecifica pe USING( pg_id, pe_id )
            INNER JOIN cat_IdentificadorPartida i USING( idp_id )
            INNER JOIN cat_TipoGasto g USING( tg_id )
            INNER JOIN enc_ClaveFinanciamiento ef USING( ecf_id )
            INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf ON ef.cff_id = cf.cff_id AND ef.fte_id = cf.fte_id AND ef.sft_id = cf.sft_id AND ef.ori_id = cf.ori_id AND ef.sor_id = cf.sor_id AND ef.cfi_id = cf.cfi_id AND ef.cfi_anio = cf.cfi_anio
            INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " cs ON ef.cff_id = cs.cff_id AND ef.fte_id = cs.fte_id AND ef.sft_id = cs.sft_id AND ef.ori_id = cs.ori_id AND ef.sor_id = cs.sor_id
            INNER JOIN " . BD_SINPRES . ".cat_TipoRecurso tr USING( ter_id )
            INNER JOIN enc_Programatico ep USING( epg_id )
            INNER JOIN cat_ObraActividad oa ON ep.gpo_id = oa.gpo_id AND ep.ur_id = oa.ur_id AND ep.prg_id = oa.prg_id AND ep.spr_id = oa.spr_id AND ep.py_id = oa.py_id AND ep.oa_id = oa.oa_id
        " . (($reporte['tipo'] == 2 && $_SESSION[_S]['nvj_id'] < 6) ? "    INNER JOIN cat_CargaTrabajo t ON t.gpo_id = p.gpo_id AND t.ur_id = p.ur_id
        WHERE t.usr_id = :usr_id " : ($reporte['tipo'] == 1 ? "WHERE p.gpo_id = :gpo_id AND p.ur_id = :ur_id " : "")) . "
            " . ($_SESSION[_S]['nvj_id'] > 5 && $reporte['tipo'] != 1 ? $where : ($ue_id != '' ? "AND p.ue_id = :ue_id " : "")) . "
            $programaticoSql
            " . ($claveFinanciamiento['ter_id'] == 'C' ? 'AND tr.ter_id IS NOT NULL' : $cvefinSql) . "
        GROUP BY $ordenar
        HAVING pre_importe != 0
        ORDER BY $ordenar
    ";

    if ($reporte['tipo'] == 2 && $_SESSION[_S]['nvj_id'] < 6){
        $parametros += ['usr_id' => $_SESSION[_S]['usr_id']];
    }
    else {
        if ($reporte['tipo'] == 1){
            $parametros += [
                'gpo_id' => $gpo_id,
                'ur_id' => $ur_id,
            ];
        }
    }

    $presupuestal = new AccesoDatos(); //variable de conexion para los saldos iniciales
    $presupuestal->conectar();
    $presupuestal->setSql($sql);
    $presupuestal->consultar($parametros);
    $presupuestal->desconectar();

    $datos = array();

    while ($rowPresupuestal = $presupuestal->extraer()) {

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
            'pre_importe' => $rowPresupuestal['pre_importe'],
            'rec_importe' => $rowPresupuestal['rec_importe'],
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
            'pre_importe' => '',
            'rec_importe' => '',
        );
    }

    return $datos;
}
