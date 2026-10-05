<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/../../MesAlias.php';

function presupuestalRectificado($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array())
{
    $params = [];
    $programaticoSql = '';
    $sqlTipoReporte = '';
    $sqlTipoReporteP = '';
    $sqlUe = '';
    $sqlUeP = '';
    $agrupador = "r.gpo_id, r.ur_id";
    $ordenar = "gpo_id, ur_id";
    $key = "r.gpo_id, r.ur_id";
    $key2 = "gpo_id, ur_id";
    if ($niveles[2] > 0) {
        $agrupador .= ", r.ue_id";
        $ordenar .= ", ue_id";
        $key .= ", '.', r.ue_id";
        $key2 .= ", '.', ue_id";
    }
    if ($niveles[3] > 0) {
        $agrupador .= ", ep.epg_clave";
        $ordenar .= ", epg_clave";
        $key .= ", '.', ep.epg_clave";
        $key2 .= ", '.', epg_clave";
    }
    if ($niveles[4] > 0) {
        $agrupador .= ", g.tg_id";
        $ordenar .= ", tg_id";
        $key .= ", '.', g.tg_id";
        $key2 .= ", '.', tg_id";
    }
    if ($niveles[5] > 0) {
        $agrupador .= ", i.idp_id";
        $ordenar .= ", idp_id";
        $key .= ", '.', i.idp_id";
        $key2 .= ", '.', idp_id";
    }
    if ($niveles[6] > 0) {
        $agrupador .= ", n.pg_id";
        $ordenar .= ", pg_id";
        $key .= ", '.', n.pg_id";
        $key2 .= ", '.', pg_id";
    }
    if ($niveles[7] > 0) {
        $agrupador .= ", n.pe_id";
        $ordenar .= ", pe_id";
        $key .= ", '.', n.pe_id";
        $key2 .= ", '.', pe_id";
    }
    if ($niveles[8] > 0) {
        $agrupador .= ", tr.ter_id";
        $ordenar .= ", ter_id";
        $key .= ", '.', tr.ter_id";
        $key2 .= ", '.', ter_id";
    }
    if ($niveles[9] > 0) {
        $agrupador .= ", ef.ecf_clave";
        $ordenar .= ", ecf_clave";
        $key .= ", '.', ef.ecf_clave";
        $key2 .= ", '.', ecf_clave";
    }

    if ($periodo['mesDesde'] == $periodo['mesHasta']) {
        $periodoSql = 'AND msc_id_autorizacion = :desde ';
        $params[':desde'] = $periodo['mesDesde'];
    } else {
        $periodoSql = 'AND ( msc_id_autorizacion BETWEEN :desde AND :hasta ) ';
        $params[':desde'] = $periodo['mesDesde'];
        $params[':hasta'] = $periodo['mesHasta'];

    }
    if($programatico['epg_id'] != ''){
        $programaticoSql = "AND epg_id = :prog";
        $params[':prog'] = $programatico['epg_id'];
    }

    if ($reporte['tipo'] == 2 && $_SESSION[_S]['nvj_id'] < 6) {
        $sqlTipoReporte .= " 
            INNER JOIN cat_CargaTrabajo t ON t.gpo_id = r.gpo_id AND t.ur_id = r.ur_id
            WHERE t.usr_id = :usr_id
        ";
        $sqlTipoReporteP .= "   INNER JOIN cat_CargaTrabajo t ON t.gpo_id = rp.gpo_id AND t.ur_id = rp.ur_id
                WHERE t.usr_id = :usr_id";
        $params[':usr_id'] = $_SESSION[_S]['usr_id'];
    } else {
        $sqlTipoReporte .= " WHERE r.gpo_id = :gpo_id AND r.ur_id = :ur_id ";
        $sqlTipoReporteP .= " WHERE rp.gpo_id = :gpo_id AND rp.ur_id = :ur_id " ;
        $params[':gpo_id'] = $gpo_id;
        $params[':ur_id'] = $ur_id;
    }

    if ($ue_id != '') {
        $sqlUe .= " AND r.ue_id = :ue_id ";
        $sqlUeP .= " AND rp.ue_id = :ue_id ";
        $params[':ue_id'] = $ue_id;
    }

    $cvefinSql = ($claveFinanciamiento['ecf_id'] != '' ? "AND ef.ecf_id = " . $claveFinanciamiento['ecf_id'] : ($claveFinanciamiento['ter_id'] != '' ? "AND tr.ter_id = '" . $claveFinanciamiento['ter_id'] . "'" : ''));
    if (isset($parametroExtra['origenReporte'])) {
        $sqlPrefijo = "
                SELECT gpo_id, ur_id, ur_clave, ur_nombre, MAX(ue_id) AS ue_id,
                    MAX(epg_id) as epg_id, MAX(prg_id) AS prg_id, max(spr_id) AS spr_id,
                    MAX(py_id) AS py_id, MAX(oa_id) AS oa_id, MAX(epg_clave) AS epg_clave, MAX(oa_nombre) AS oa_nombre,
                    MAX(tg_id) AS tg_id, MAX(tg_nombre) AS tg_nombre,
                    MAX(idp_id) AS idp_id, MAX(idp_nombre) AS idp_nombre ,
                    MAX(pg_id) AS pg_id , MAX(pg_nombre) AS pg_nombre,
                    MAX(pe_id) AS pe_id, MAX(pe_nombre) AS pe_nombre,
                    MAX(ter_id) AS ter_id, MAX(ter_nombre) AS ter_nombre,
                    MAX(ecf_id) AS ecf_id, MAX(ecf_clave) AS ecf_clave, MAX(cfi_nombre) AS cfi_nombre,
                    SUM( com_ene ) AS com_ene, SUM( com_feb ) AS com_feb, SUM( com_mar ) AS com_mar, SUM( com_abr ) AS com_abr, SUM( com_may ) AS com_may, SUM( com_jun ) AS com_jun, SUM( com_jul ) AS com_jul, SUM( com_ago ) AS com_ago, SUM( com_sep ) AS com_sep, SUM( com_oct ) AS com_oct, SUM( com_nov ) AS com_nov, SUM( com_dic ) AS com_dic, 
                    SUM( dev_ene ) AS dev_ene, SUM( dev_feb ) AS dev_feb, SUM( dev_mar ) AS dev_mar, SUM( dev_abr ) AS dev_abr, SUM( dev_may ) AS dev_may, SUM( dev_jun ) AS dev_jun, SUM( dev_jul ) AS dev_jul, SUM( dev_ago ) AS dev_ago, SUM( dev_sep ) AS dev_sep, SUM( dev_oct ) AS dev_oct, SUM( dev_nov ) AS dev_nov, SUM( dev_dic ) AS dev_dic, 
                    SUM( eje_ene ) AS eje_ene, SUM( eje_feb ) AS eje_feb, SUM( eje_mar ) AS eje_mar, SUM( eje_abr ) AS eje_abr, SUM( eje_may ) AS eje_may, SUM( eje_jun ) AS eje_jun, SUM( eje_jul ) AS eje_jul, SUM( eje_ago ) AS eje_ago, SUM( eje_sep ) AS eje_sep, SUM( eje_oct ) AS eje_oct, SUM( eje_nov ) AS eje_nov, SUM( eje_dic ) AS eje_dic, 
                    SUM( min_ene ) AS min_ene, SUM( min_feb ) AS min_feb, SUM( min_mar ) AS min_mar, SUM( min_abr ) AS min_abr, SUM( min_may ) AS min_may, SUM( min_jun ) AS min_jun, SUM( min_jul ) AS min_jul, SUM( min_ago ) AS min_ago, SUM( min_sep ) AS min_sep, SUM( min_oct ) AS min_oct, SUM( min_nov ) AS min_nov, SUM( min_dic ) AS min_dic, 
                    SUM( pag_ene ) AS pag_ene, SUM( pag_feb ) AS pag_feb, SUM( pag_mar ) AS pag_mar, SUM( pag_abr ) AS pag_abr, SUM( pag_may ) AS pag_may, SUM( pag_jun ) AS pag_jun, SUM( pag_jul ) AS pag_jul, SUM( pag_ago ) AS pag_ago, SUM( pag_sep ) AS pag_sep, SUM( pag_oct ) AS pag_oct, SUM( pag_nov ) AS pag_nov, SUM( pag_dic ) AS pag_dic, 
                    MAX(mesComprometido) AS mesComprometido,
                    MAX(mesDevengado) AS mesDevengado,
                    MAX(mesEjercido) AS mesEjercido,
                    MAX(mesMinistrado) AS mesMinistrado,
                    MAX(mesPagado) AS mesPagado,
                    SUM( pre_importe ) AS pre_importe,
                    MAX(cmp_id) AS cmp_id,
                    MAX(afe_id) AS afe_id,
                    MAX(bdo_id) AS bdo_id,
                    MAX(clc_id) AS clc_id,
                    MAX(rct_id) AS rct_id, 
                    CONCAT( $key2 ) AS id 
                FROM ( 
                    ";
        $sqlSufijo = "
                )r 
                GROUP BY $ordenar 
                ORDER BY gpo_id, ur_id, pg_id, ecf_clave
                    ";
    } else {
        $sqlPrefijo = "";
        $sqlSufijo = "
                ORDER BY $ordenar 
            ";
    }
    // Armamos la consulta
    // -----------------------------------------------------------------------------------------------------------------------
    $sql = $sqlPrefijo . "
            SELECT r.gpo_id, r.ur_id, ur.ur_clave,ur.ur_nombre, MAX(r.ue_id) AS ue_id,
                MAX(ep.epg_id) AS epg_id, MAX(oa.prg_id) AS prg_id, MAX(oa.spr_id) AS spr_id, MAX(oa.py_id) AS py_id,
                MAX(oa.oa_id) AS oa_id, MAX(ep.epg_clave) AS epg_clave, MAX(oa.oa_nombre) AS oa_nombre,
                MAX(g.tg_id) AS tg_id, MAX(g.tg_nombre) AS tg_nombre,
                MAX(i.idp_id) AS idp_id, MAX(i.idp_nombre) AS idp_nombre,
                MAX(n.pg_id) AS pg_id, MAX(pg.pg_nombre) AS pg_nombre,
                MAX(n.pe_id) AS pe_id, MAX(pe.pe_nombre) AS pe_nombre,
                MAX(tr.ter_id) AS ter_id, MAX(tr.ter_nombre) ter_nombre,
                MAX(n.ecf_id) AS ecf_id, ef.ecf_clave, MAX(cf.cfi_nombre) AS cfi_nombre,
                SUM( CASE c.msc_id_autorizado WHEN 1 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS com_ene, 
                SUM( CASE c.msc_id_autorizado WHEN 2 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS com_feb, 
                SUM( CASE c.msc_id_autorizado WHEN 3 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS com_mar, 
                SUM( CASE c.msc_id_autorizado WHEN 4 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS com_abr, 
                SUM( CASE c.msc_id_autorizado WHEN 5 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS com_may, 
                SUM( CASE c.msc_id_autorizado WHEN 6 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS com_jun, 
                SUM( CASE c.msc_id_autorizado WHEN 7 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS com_jul, 
                SUM( CASE c.msc_id_autorizado WHEN 8 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS com_ago, 
                SUM( CASE c.msc_id_autorizado WHEN 9 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS com_sep, 
                SUM( CASE c.msc_id_autorizado WHEN 10 THEN r.ncp_percepcionNeto ELSE 0 END )*-1  AS com_oct, 
                SUM( CASE c.msc_id_autorizado WHEN 11 THEN r.ncp_percepcionNeto ELSE 0 END )*-1  AS com_nov, 
                SUM( CASE c.msc_id_autorizado WHEN 12 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS com_dic, 
                SUM( CASE MONTH( af.afe_fechaAutorizado ) WHEN 1 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS dev_ene, 
                SUM( CASE MONTH( af.afe_fechaAutorizado ) WHEN 2 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS dev_feb, 
                SUM( CASE MONTH( af.afe_fechaAutorizado ) WHEN 3 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS dev_mar, 
                SUM( CASE MONTH( af.afe_fechaAutorizado ) WHEN 4 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS dev_abr, 
                SUM( CASE MONTH( af.afe_fechaAutorizado ) WHEN 5 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS dev_may, 
                SUM( CASE MONTH( af.afe_fechaAutorizado ) WHEN 6 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS dev_jun, 
                SUM( CASE MONTH( af.afe_fechaAutorizado ) WHEN 7 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS dev_jul, 
                SUM( CASE MONTH( af.afe_fechaAutorizado ) WHEN 8 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS dev_ago, 
                SUM( CASE MONTH( af.afe_fechaAutorizado ) WHEN 9 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS dev_sep, 
                SUM( CASE MONTH( af.afe_fechaAutorizado ) WHEN 10 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS dev_oct, 
                SUM( CASE MONTH( af.afe_fechaAutorizado ) WHEN 11 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS dev_nov, 
                SUM( CASE MONTH( af.afe_fechaAutorizado ) WHEN 12 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS dev_dic, 
                SUM( CASE MONTH( cl.clc_fechaEnvio ) WHEN 1 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS eje_ene, 
                SUM( CASE MONTH( cl.clc_fechaEnvio ) WHEN 2 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS eje_feb, 
                SUM( CASE MONTH( cl.clc_fechaEnvio ) WHEN 3 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS eje_mar, 
                SUM( CASE MONTH( cl.clc_fechaEnvio ) WHEN 4 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS eje_abr, 
                SUM( CASE MONTH( cl.clc_fechaEnvio ) WHEN 5 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS eje_may, 
                SUM( CASE MONTH( cl.clc_fechaEnvio ) WHEN 6 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS eje_jun, 
                SUM( CASE MONTH( cl.clc_fechaEnvio ) WHEN 7 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS eje_jul, 
                SUM( CASE MONTH( cl.clc_fechaEnvio ) WHEN 8 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS eje_ago, 
                SUM( CASE MONTH( cl.clc_fechaEnvio ) WHEN 9 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS eje_sep, 
                SUM( CASE MONTH( cl.clc_fechaEnvio ) WHEN 10 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS eje_oct, 
                SUM( CASE MONTH( cl.clc_fechaEnvio ) WHEN 11 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS eje_nov, 
                SUM( CASE MONTH( cl.clc_fechaEnvio ) WHEN 12 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS eje_dic, 
                SUM( CASE bdm.msc_id WHEN 1 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS min_ene, 
                SUM( CASE bdm.msc_id WHEN 2 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS min_feb, 
                SUM( CASE bdm.msc_id WHEN 3 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS min_mar, 
                SUM( CASE bdm.msc_id WHEN 4 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS min_abr, 
                SUM( CASE bdm.msc_id WHEN 5 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS min_may, 
                SUM( CASE bdm.msc_id WHEN 6 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS min_jun, 
                SUM( CASE bdm.msc_id WHEN 7 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS min_jul, 
                SUM( CASE bdm.msc_id WHEN 8 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS min_ago, 
                SUM( CASE bdm.msc_id WHEN 9 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS min_sep, 
                SUM( CASE bdm.msc_id WHEN 10 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS min_oct, 
                SUM( CASE bdm.msc_id WHEN 11 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS min_nov, 
                SUM( CASE bdm.msc_id WHEN 12 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS min_dic, 
                SUM( CASE bdp.msc_id WHEN 1 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS pag_ene, 
                SUM( CASE bdp.msc_id WHEN 2 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS pag_feb, 
                SUM( CASE bdp.msc_id WHEN 3 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS pag_mar, 
                SUM( CASE bdp.msc_id WHEN 4 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS pag_abr, 
                SUM( CASE bdp.msc_id WHEN 5 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS pag_may, 
                SUM( CASE bdp.msc_id WHEN 6 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS pag_jun, 
                SUM( CASE bdp.msc_id WHEN 7 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS pag_jul, 
                SUM( CASE bdp.msc_id WHEN 8 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS pag_ago, 
                SUM( CASE bdp.msc_id WHEN 9 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS pag_sep, 
                SUM( CASE bdp.msc_id WHEN 10 THEN r.ncp_percepcionNeto ELSE 0 END )*-1  AS pag_oct, 
                SUM( CASE bdp.msc_id WHEN 11 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS pag_nov, 
                SUM( CASE bdp.msc_id WHEN 12 THEN r.ncp_percepcionNeto ELSE 0 END )*-1 AS pag_dic, 
                c.msc_id_autorizado AS mesComprometido, 
                MONTH( af.afe_fechaAutorizado ) AS mesDevengado, 
                MONTH( cl.clc_fechaEnvio ) AS mesEjercido, 
                bdm.msc_id AS mesMinistrado, 
                bdp.msc_id AS mesPagado, 
                SUM( r.ncp_percepcionNeto )*-1 AS pre_importe, 
                GROUP_CONCAT( DISTINCT c.cmp_id ORDER BY c.cmp_id ) AS cmp_id, 
                GROUP_CONCAT( DISTINCT af.afe_id ORDER BY af.afe_id ) AS afe_id, 
                GROUP_CONCAT( DISTINCT n.bdo_id ORDER BY n.bdo_id ) AS bdo_id, 
                GROUP_CONCAT( DISTINCT n.clc_id ORDER BY n.clc_id ) AS clc_id, 
                GROUP_CONCAT( DISTINCT rct_id ORDER BY rct_id ) AS rct_id, 
                CONCAT( $key, '.', c.msc_id_autorizado, '.', MAX(MONTH( af.afe_fechaAutorizado )), '.', MAX(MONTH( cl.clc_fechaEnvio )), '.', bdm.msc_id, '.', bdp.msc_id ) AS id
            FROM ( 
                SELECT rp.gpo_id, rp.ur_id, rp.ue_id, rct_id, 
                    ncp_id, 
                    ncp_percepcionNeto 
                FROM scp_MaestroRectificacion m 
                    INNER JOIN scp_RectificacionPresupuestal rp USING( gpo_id, ur_id, rct_id ) 
                    INNER JOIN cat_UnidadResponsable ur ON rp.gpo_id = ur.gpo_id AND rp.ur_id = ur.ur_id 
                " . $sqlTipoReporteP . $sqlUeP . " 
                    AND m.ecl_id = 6 
                    $periodoSql 
            )r 
                INNER JOIN scp_Presupuestal n USING( gpo_id, ur_id, ncp_id ) 
                INNER JOIN scp_Afectacion af USING( gpo_id, ur_id, clc_id, afe_id ) 
                INNER JOIN scp_Clc cl USING( gpo_id, ur_id, clc_id ) 
                INNER JOIN scp_Compromiso c USING( gpo_id, ur_id, cmp_id ) 
                INNER JOIN " . BD_TESORERIA . ".min_BancoDocumentoMinistrado_" . APP_EJERCICIO . " bdm USING( gpo_id, ur_id, clc_id, afe_id, bdo_id )
                INNER JOIN cg_BancoDocumentoPagado bdp USING( gpo_id, ur_id, clc_id, afe_id, bdo_id ) 
                INNER JOIN cat_PartidaGenerica pg ON pg.pg_id = n.pg_id 
                INNER JOIN cat_PartidaEspecifica pe ON pe.pg_id = n.pg_id AND pe.pe_id = n.pe_id 
                INNER JOIN cat_IdentificadorPartida i USING( idp_id ) 
                INNER JOIN cat_TipoGasto g USING( tg_id ) 
                INNER JOIN enc_ClaveFinanciamiento ef ON n.ecf_id = ef.ecf_id
                INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf ON ef.cff_id = cf.cff_id AND ef.fte_id = cf.fte_id AND ef.sft_id = cf.sft_id AND ef.ori_id = cf.ori_id AND ef.sor_id = cf.sor_id AND ef.cfi_id = cf.cfi_id AND ef.cfi_anio = cf.cfi_anio
                INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " cs ON ef.cff_id = cf.cff_id AND ef.fte_id = cs.fte_id AND ef.sft_id = cs.sft_id AND ef.ori_id = cs.ori_id AND ef.sor_id = cs.sor_id
                INNER JOIN " . BD_SINPRES . ".cat_TipoRecurso tr USING( ter_id ) 
                INNER JOIN enc_Programatico ep ON ep.epg_id = n.epg_id 
                INNER JOIN cat_ObraActividad oa ON ep.gpo_id = oa.gpo_id AND ep.ur_id = oa.ur_id AND ep.prg_id = oa.prg_id AND ep.spr_id = oa.spr_id AND ep.py_id = oa.py_id AND ep.oa_id = oa.oa_id 
                INNER JOIN cat_UnidadResponsable ur ON r.gpo_id = ur.gpo_id AND r.ur_id = ur.ur_id 
            " . $sqlTipoReporte . $sqlUe . " 
                $programaticoSql 
                " . ($claveFinanciamiento['ter_id'] == 'C' ? 'AND tr.ter_id IS NOT NULL' : $cvefinSql) . "
            GROUP BY $agrupador, mesComprometido, mesDevengado, mesEjercido, mesMinistrado, mesPagado 
            HAVING pre_importe != 0 
        " . $sqlSufijo;

    //variable de conexion para los saldos iniciales
    $presupuestal = new AccesoDatos();
    $presupuestal->conectar();
    $presupuestal->setSql($sql);
    $presupuestal->consultar($params);
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
            'cmp_id' => $rowPresupuestal['cmp_id'],
            'bdo_id' => $rowPresupuestal['bdo_id'],
            'afe_id' => $rowPresupuestal['afe_id'],
            'clc_id' => $rowPresupuestal['clc_id'],
            'rct_id' => $rowPresupuestal['rct_id'],
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
            'com_ene' => $rowPresupuestal['com_ene'],
            'com_feb' => $rowPresupuestal['com_feb'],
            'com_mar' => $rowPresupuestal['com_mar'],
            'com_abr' => $rowPresupuestal['com_abr'],
            'com_may' => $rowPresupuestal['com_may'],
            'com_jun' => $rowPresupuestal['com_jun'],
            'com_jul' => $rowPresupuestal['com_jul'],
            'com_ago' => $rowPresupuestal['com_ago'],
            'com_sep' => $rowPresupuestal['com_sep'],
            'com_oct' => $rowPresupuestal['com_oct'],
            'com_nov' => $rowPresupuestal['com_nov'],
            'com_dic' => $rowPresupuestal['com_dic'],
            'dev_ene' => $rowPresupuestal['dev_ene'],
            'dev_feb' => $rowPresupuestal['dev_feb'],
            'dev_mar' => $rowPresupuestal['dev_mar'],
            'dev_abr' => $rowPresupuestal['dev_abr'],
            'dev_may' => $rowPresupuestal['dev_may'],
            'dev_jun' => $rowPresupuestal['dev_jun'],
            'dev_jul' => $rowPresupuestal['dev_jul'],
            'dev_ago' => $rowPresupuestal['dev_ago'],
            'dev_sep' => $rowPresupuestal['dev_sep'],
            'dev_oct' => $rowPresupuestal['dev_oct'],
            'dev_nov' => $rowPresupuestal['dev_nov'],
            'dev_dic' => $rowPresupuestal['dev_dic'],
            'eje_ene' => $rowPresupuestal['eje_ene'],
            'eje_feb' => $rowPresupuestal['eje_feb'],
            'eje_mar' => $rowPresupuestal['eje_mar'],
            'eje_abr' => $rowPresupuestal['eje_abr'],
            'eje_may' => $rowPresupuestal['eje_may'],
            'eje_jun' => $rowPresupuestal['eje_jun'],
            'eje_jul' => $rowPresupuestal['eje_jul'],
            'eje_ago' => $rowPresupuestal['eje_ago'],
            'eje_sep' => $rowPresupuestal['eje_sep'],
            'eje_oct' => $rowPresupuestal['eje_oct'],
            'eje_nov' => $rowPresupuestal['eje_nov'],
            'eje_dic' => $rowPresupuestal['eje_dic'],
            'min_ene' => $rowPresupuestal['min_ene'],
            'min_feb' => $rowPresupuestal['min_feb'],
            'min_mar' => $rowPresupuestal['min_mar'],
            'min_abr' => $rowPresupuestal['min_abr'],
            'min_may' => $rowPresupuestal['min_may'],
            'min_jun' => $rowPresupuestal['min_jun'],
            'min_jul' => $rowPresupuestal['min_jul'],
            'min_ago' => $rowPresupuestal['min_ago'],
            'min_sep' => $rowPresupuestal['min_sep'],
            'min_oct' => $rowPresupuestal['min_oct'],
            'min_nov' => $rowPresupuestal['min_nov'],
            'min_dic' => $rowPresupuestal['min_dic'],
            'pag_ene' => $rowPresupuestal['pag_ene'],
            'pag_feb' => $rowPresupuestal['pag_feb'],
            'pag_mar' => $rowPresupuestal['pag_mar'],
            'pag_abr' => $rowPresupuestal['pag_abr'],
            'pag_may' => $rowPresupuestal['pag_may'],
            'pag_jun' => $rowPresupuestal['pag_jun'],
            'pag_jul' => $rowPresupuestal['pag_jul'],
            'pag_ago' => $rowPresupuestal['pag_ago'],
            'pag_sep' => $rowPresupuestal['pag_sep'],
            'pag_oct' => $rowPresupuestal['pag_oct'],
            'pag_nov' => $rowPresupuestal['pag_nov'],
            'pag_dic' => $rowPresupuestal['pag_dic'],
            'mesComprometido' => $rowPresupuestal['mesComprometido'],
            'mesDevengado' => $rowPresupuestal['mesDevengado'],
            'mesEjercido' => $rowPresupuestal['mesEjercido'],
            'mesMinistrado' => $rowPresupuestal['mesMinistrado'],
            'mesPagado' => $rowPresupuestal['mesPagado'],
            'pre_importe' => $rowPresupuestal['pre_importe']
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
            'cmp_id' => '',
            'bdo_id' => '',
            'afe_id' => '',
            'clc_id' => '',
            'rct_id' => '',
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
            'com_ene' => '',
            'com_feb' => '',
            'com_mar' => '',
            'com_abr' => '',
            'com_may' => '',
            'com_jun' => '',
            'com_jul' => '',
            'com_ago' => '',
            'com_sep' => '',
            'com_oct' => '',
            'com_nov' => '',
            'com_dic' => '',
            'dev_ene' => '',
            'dev_feb' => '',
            'dev_mar' => '',
            'dev_abr' => '',
            'dev_may' => '',
            'dev_jun' => '',
            'dev_jul' => '',
            'dev_ago' => '',
            'dev_sep' => '',
            'dev_oct' => '',
            'dev_nov' => '',
            'dev_dic' => '',
            'eje_ene' => '',
            'eje_feb' => '',
            'eje_mar' => '',
            'eje_abr' => '',
            'eje_may' => '',
            'eje_jun' => '',
            'eje_jul' => '',
            'eje_ago' => '',
            'eje_sep' => '',
            'eje_oct' => '',
            'eje_nov' => '',
            'eje_dic' => '',
            'min_ene' => '',
            'min_feb' => '',
            'min_mar' => '',
            'min_abr' => '',
            'min_may' => '',
            'min_jun' => '',
            'min_jul' => '',
            'min_ago' => '',
            'min_sep' => '',
            'min_oct' => '',
            'min_nov' => '',
            'min_dic' => '',
            'pag_ene' => '',
            'pag_feb' => '',
            'pag_mar' => '',
            'pag_abr' => '',
            'pag_may' => '',
            'pag_jun' => '',
            'pag_jul' => '',
            'pag_ago' => '',
            'pag_sep' => '',
            'pag_oct' => '',
            'pag_nov' => '',
            'pag_dic' => '',
            'mesComprometido' => '',
            'mesDevengado' => '',
            'mesEjercido' => '',
            'mesMinistrado' => '',
            'mesPagado' => '',
            'pre_importe' => ''
        );
    }

    return $datos;
}
