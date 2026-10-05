<?php

/* --------------------------------------------------------------------------------------------------------
Objective: Consultar datos en la base de datos de acuerdo al modulo que se requiera, modificar techos,
            altas de previsiones o ajustes.
Author : -----
Created: 13-Agt-2018
Updated: may-2024
Collaborators : Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx> ABN
Permits: Esta pantalla no pregunta por permisos de escritura.
-------------------------------------------------------------------------------------------------------- */

function consultaMgaComplementos($gpo_id, $ur_id, $ue_id, $epg_id, $ecf_id, $ccd_id)
{
    $ue_sql = '';
    $ue_prm = [];
    $epgSql = '';
    $epg_prm = [];
    $ecfSql = '';
    $ecf_prm = [];
    if ($ue_id != '') {
        $ue_sql = 'AND ue_id = ? ';
        $ue_prm = [$ue_id];
    }

    if ($epg_id != '') {
        $epgSql = 'AND epg_id = ? ';
        $epg_prm = [$epg_id];
    }

    if ($ecf_id != '') {
        $ecfSql = 'AND ecf_id = ? ';
        $ecf_prm = [$ecf_id];
    }

    $where_additional = '';
    $where_add_prm = [];

    //Para el caso $ccd_id == 1
    $gpo = !is_null($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : 'null';
    $ur = !is_null($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : 'null';

    switch ($ccd_id) {
        case 1:
            $where_add_prm = [$gpo, $ur];
            $where_additional = 'WHERE gpo_id = ? AND ur.ur_id = ? ';

            if (!empty($_POST['ter_id']) && empty($_POST['clg_id'])) {
                array_push($where_add_prm, $_POST['ter_id']);
                $where_additional .= ' AND ter.ter_id = ? ';
            } elseif (empty($_POST['ter_id']) && !empty($_POST['clg_id'])) {
                array_push($where_add_prm, $_POST['clg_id']);
                $where_additional .= ' AND clg.clg_id = ? ';
            } elseif (!empty($_POST['ter_id']) && !empty($_POST['clg_id'])) {
                array_push($where_add_prm, $_POST['ter_id']);
                array_push($where_add_prm, $_POST['clg_id']);
                $where_additional .= ' AND ter.ter_id = ? AND clg.clg_id = ? ';
            }
            break;
        case 2:
            if ($_POST['prg_id'] != '' && ($_POST['spr_id'] == 'Seleccione una opcion de la lista' || empty($_POST['spr_id']))) {
                array_push($where_add_prm, $_POST['prg_id']);
                $where_additional = 'WHERE prg.prg_id = ?';
            } elseif (!empty($_POST['prg_id']) && !empty($_POST['spr_id'])) {
                array_push($where_add_prm, $_POST['prg_id']);
                array_push($where_add_prm, $_POST['spr_id']);
                $where_additional = 'WHERE prg.prg_id = ? AND spr.spr_id= ? ';
            }
            break;
        case 3:
            break;
    }

    switch ($ccd_id) {
        case 1:
            $atributo = '';
            $atributoSubconsulta = 't.tec_id,ter.ter_id,clg.clg_id,t.tec_techoPresupuestal,
                    t.tec_techoPresupuestal  AS total,';
            $from = 'cat_Techos t';
            $join = '
                JOIN ' . BD_SINPRES . '.cat_TipoRecurso ter USING(ter_id)
                JOIN cat_ClaseGasto clg USING(clg_id)
                ';
            $agrupador = ', t.tec_id';
            $where_subquery = 'WHERE t.gpo_id = ? AND t.ur_id = ? ';
            $where_sub_prm = [$gpo, $ur];
            break;
        case 2:
            $atributo = '';
            $atributoSubconsulta = 'CONCAT(a.oa_id, " - ", a.oa_nombre) AS oa,
                IFNULL(CONCAT(f.cff_id, "-", f.fte_id, f.sft_id, f.ori_id, f.sor_id, "-", f.cfi_id, f.cfi_anio), "")
                    AS cveF, CASE WHEN pp.pg_id IS NULL THEN 0 ELSE 1 END AS tieneOG,                    
                SUM(pp.mon_ene + pp.mon_feb + pp.mon_mar + pp.mon_abr + pp.mon_may + pp.mon_jun + pp.mon_jul + pp.mon_ago + pp.mon_sep + pp.mon_oct + pp.mon_nov + pp.mon_dic) AS total,';
            $from = 'cat_ObraActividad a';
            $join = '
                INNER JOIN enc_Programatico epg USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                LEFT JOIN poa_OrigenRecurso ore USING(gpo_id, ur_id, epg_id)
                LEFT JOIN enc_ClaveFinanciamiento f USING(ecf_id)
                LEFT JOIN poa_Prevision pp USING(gpo_id, ur_id, ue_id, epg_id, ecf_id)
                LEFT JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cfi USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                LEFT JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' so USING(cff_id, fte_id, sft_id, ori_id, sor_id)
                ';
            $agrupador = ', pp.pg_id, pp.pe_id';
            $where_subquery = 'WHERE a.gpo_id = ? AND a.ur_id = ? ' . $ue_sql . " AND a.oa_id != 0
                $epgSql $ecfSql";
            $where_sub_prm = array_merge([$gpo_id, $ur_id], $ue_prm, $epg_prm, $ecf_prm);
            break;
        case 3:
            $atributo = 'tcp_id,';
            $atributoSubconsulta = '
                p.ecf_id,
                p.gpo_id,p.ur_id,p.ue_id,
                p.prg_id,p.spr_id,p.py_id,p.oa_id,
                p.pg_id,p.pe_id,
                p.tcp_id,
                p.epg_id,
                p.pre_claveCompleta,
                apr.apr_ene, apr.apr_feb, apr.apr_mar, apr.apr_abr, apr.apr_may, apr.apr_jun, 
                apr.apr_jul, apr.apr_ago, apr.apr_sep, apr.apr_oct, apr.apr_nov, apr.apr_dic, 
                (ori.ori_ene+ori.ori_feb+ori.ori_mar+ori.ori_abr+ori.ori_may+ori.ori_jun+ori.ori_jul+ori.ori_ago+ori.ori_sep+ori.ori_oct+ori.ori_nov+ori.ori_dic) total,';
            $from = 'presupuesto p';
            $join = '
                INNER JOIN cat_UnidadResponsable ur USING (gpo_id, ur_id)
                INNER JOIN presupuestoOriginal ori USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                LEFT JOIN presupuestoAprobado apr USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                ';
            $agrupador = '';
            $where_subquery = 'WHERE p.gpo_id = ? AND p.ur_id = ? ';
            $where_sub_prm = [$gpo, $ur];

            if (!is_null($_SESSION[_S]['clasAdm-prg'])) {
                $where_sub_prm = array_merge($where_sub_prm, $_SESSION[_S]['clasAdm-prg']);
                $where_subquery .= 'AND p.prg_id = ? ';
            }
            if (!is_null($_SESSION[_S]['clasAdm-spr'])) {
                $where_sub_prm = array_merge($where_sub_prm, $_SESSION[_S]['clasAdm-spr']);
                $where_subquery .= 'AND p.spr_id = ? ';
            }
            if (!is_null($_SESSION[_S]['clasAdm-py'])) {
                $where_sub_prm = array_merge($where_sub_prm, $_SESSION[_S]['clasAdm-py']);
                $where_subquery .= 'AND p.py_id = ? ';
            }
            if (!is_null($_SESSION[_S]['clasAdm-oa'])) {
                $where_sub_prm = array_merge($where_sub_prm, $_SESSION[_S]['clasAdm-oa']);
                $where_subquery .= 'AND p.oa_id = ? ';
            }
            $where_subquery .= "AND ur_poa = 1 AND p.tcp_id != 'P'";
            break;
        default:
            echo 'kym';
            break;
    }
    switch ($ccd_id) {
        case 1:
            $sql = "
                SELECT gpo_id, ur.ur_clave, ur.ur_id, ur.ur_nombre, 
                    ter.ter_id, ter.ter_nombre, 
                    clg.clg_id, clg.clg_nombre, 
                    tec_techoPresupuestal,
                    CONCAT(ter.ter_id,'-', ter.ter_nombre) AS gff,
                    CONCAT(clg.clg_id,'-' ,clg.clg_nombre) AS clg,
                    $atributo 
                    tec_techoPresupuestal AS total,
                    nivel 
                FROM (
                    SELECT t.gpo_id, t.ur_id,
                        $atributoSubconsulta
                        9 AS nivel 
                    FROM $from $join 
                    $where_subquery
                    GROUP BY t.gpo_id, t.ur_id $agrupador
                    ORDER BY ter.ter_id, clg.clg_id
                )cedula 
                    INNER JOIN " . BD_SINPRES . ".cat_TipoRecurso ter USING (ter_id)
                    JOIN cat_ClaseGasto clg USING(clg_id)
                    INNER JOIN cat_UnidadResponsable ur USING(gpo_id, ur_id)
                    $where_additional
                ORDER BY gpo_id, ur.ur_id, ter.ter_id, clg.clg_id, nivel 
            ";
            $sql_prm = array_merge($where_sub_prm, $where_add_prm);
            break;
        case 2:
            $sql = "
                SELECT gpo_id, ur.ur_clave, ur_id, ur.ur_nombre, 
                    ue_id, ue.ue_nombre, 
                    epg_id, 
                    prg_id, prg.prg_nombre, 
                    spr_id, spr_nombre, 
                    py_id, py.py_nombre, 
                    oa_id, oa.oa_nombre, 
                    pg_id,
                    pe_id, 
                    ecf_id, 
                    ter.ter_id, ter.ter_nombre, 
                    cfi.cfi_clave, cfi.cfi_nombre, 
                    $atributo 
                    total, 
                    nivel 
                FROM (
                    SELECT a.gpo_id, a.ur_id, a.ue_id, epg.epg_id, a.prg_id, a.spr_id, a.py_id, a.oa_id, pp.pg_id, pp.pe_id, f.ecf_id, 
                        $atributoSubconsulta
                        9 AS nivel 
                    FROM $from $join 
                    $where_subquery
                    GROUP BY a.gpo_id, a.ur_id, a.prg_id, a.spr_id, a.ue_id, a.py_id, a.oa_id, f.ecf_id $agrupador
                )cedula 
                    INNER JOIN enc_ClaveFinanciamiento ecf USING (ecf_id) 
                    INNER JOIN " . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cfi USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio) 
                    INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' cs USING(cff_id, fte_id, sft_id, ori_id, sor_id) 
                    INNER JOIN ' . BD_SINPRES . ".cat_TipoRecurso ter USING (ter_id) 
                    INNER JOIN cat_ObraActividad oa USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id) 
                    INNER JOIN cat_Proyecto py USING(gpo_id, ur_id, prg_id, spr_id, py_id) 
                    INNER JOIN cat_Subprograma spr USING(gpo_id, ur_id, prg_id, spr_id) 
                    INNER JOIN cat_Programa prg USING(gpo_id, ur_id, prg_id) 
                    INNER JOIN cat_UnidadEjecutora ue USING(gpo_id, ur_id, ue_id) 
                    INNER JOIN cat_UnidadResponsable ur USING(gpo_id, ur_id) 
                    $where_additional
                ORDER BY gpo_id, ur_id, prg_id, spr_id, ue_id, py_id, oa_id, cfi.cfi_clave, pg_id, pe_id, nivel 
            ";
            $sql_prm = array_merge($where_sub_prm, $where_add_prm);
            break;
        case 3:
            $sql = "
                SELECT gpo_id, ur.ur_clave, ur_id, ur.ur_nombre, 
                    ue_id, ue.ue_nombre,
                    prg_id, prg.prg_nombre
                    spr_id, spr.spr_nombre, 
                    py_id, py.py_nombre, 
                    oa_id, oa.oa_nombre, 
                    pg_id, pg.pg_nombre, 
                    pe_id, pe.pe_nombre, 
                    ter.ter_id, ter.ter_nombre, 
                    cfi.cfi_clave, cfi.cfi_nombre, 
                    $atributo 
                    total, 
                    nivel 
                FROM (
                    SELECT 
                        $atributoSubconsulta
                        9 AS nivel 
                    FROM $from $join 
                    $where_subquery
                    $agrupador
                    ORDER BY p.pre_claveCompleta
                )cedula 
                    INNER JOIN enc_ClaveFinanciamiento ecf USING (ecf_id) 
                    INNER JOIN " . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cfi USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio) 
                    INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' cs USING(cff_id, fte_id, sft_id, ori_id, sor_id) 
                    INNER JOIN ' . BD_SINPRES . ".cat_TipoRecurso ter USING (ter_id) 
                    INNER JOIN cat_PartidaGenerica pg USING(pg_id) 
                    INNER JOIN cat_PartidaEspecifica pe USING(pg_id, pe_id) 
                    INNER JOIN cat_ObraActividad oa USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id) 
                    INNER JOIN cat_Proyecto py USING(gpo_id, ur_id, prg_id, spr_id, py_id) 
                    INNER JOIN cat_Subprograma spr USING(gpo_id, ur_id, prg_id, spr_id) 
                    INNER JOIN cat_Programa prg USING(gpo_id, ur_id, prg_id) 
                    INNER JOIN cat_UnidadEjecutora ue USING(gpo_id, ur_id, ue_id) 
                    INNER JOIN cat_UnidadResponsable ur USING(gpo_id, ur_id) 
                    $where_additional
                GROUP BY gpo_id, ur_id, prg_id, spr_id, ue_id, py_id, oa_id, cfi.cfi_clave,cfi.cfi_nombre,ter.ter_id, pg_id, pe_id, tcp_id, nivel, total 
            ";
            $sql_prm = array_merge($where_sub_prm, $where_add_prm);
            break;
    }

    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($sql_prm);
    $conexion->desconectar();
    $datos = [];

    while ($row = $conexion->extraer()) {
        $dato = [
            'gpo_id'     => $row['gpo_id'],
            'ur_id'      => $row['ur_id'],
            'ur_clave'   => $row['ur_clave'],
            'ur_nombre'  => $row['ur_nombre'],
            'ue_id'      => array_key_exists('ue_id', $row) ? $row['ue_id'] : null,
            'ue_nombre'  => array_key_exists('ue_nombre', $row) ? $row['ue_nombre'] : null,
            'epg_id'     => array_key_exists('epg_id', $row) ? $row['epg_id'] : null,
            'prg_id'     => array_key_exists('prg_id', $row) ? $row['prg_id'] : null,
            'prg_nombre' => array_key_exists('prg_nombre', $row) ? $row['prg_nombre'] : null,
            'spr_id'     => array_key_exists('spr_id', $row) ? $row['spr_id'] : null,
            'spr_nombre' => array_key_exists('spr_nombre', $row) ? $row['spr_nombre'] : null,
            'py_id'      => array_key_exists('py_id', $row) ? $row['py_id'] : null,
            'py_nombre'  => array_key_exists('py_nombre', $row) ? $row['py_nombre'] : null,
            'oa_id'      => array_key_exists('oa_id', $row) ? $row['oa_id'] : null,
            'oa_nombre'  => array_key_exists('oa_nombre', $row) ? $row['oa_nombre'] : null,
            'pg_id'      => array_key_exists('pg_id', $row) ? $row['pg_id'] : null,
            'pg_nombre'  => array_key_exists('pg_nombre', $row) ? $row['pg_nombre'] : null,
            'pe_id'      => array_key_exists('pe_id', $row) ? $row['pe_id'] : null,
            'pe_nombre'  => array_key_exists('pe_nombre', $row) ? $row['pe_nombre'] : null,
            'ecf_id'     => array_key_exists('ecf_id', $row) ? $row['ecf_id'] : null,
            'ter_id'     => array_key_exists('ter_id', $row) ? $row['ter_id'] : null,
            'ter_nombre' => array_key_exists('ter_nombre', $row) ? $row['ter_nombre'] : null,
            'cfi_clave'  => array_key_exists('cfi_clave', $row) ? $row['cfi_clave'] : null,
            'cfi_nombre' => array_key_exists('cfi_nombre', $row) ? $row['cfi_nombre'] : null,
            'total'      => $row['total'],
            'nivel'      => $row['nivel'],
        ];

        switch ($ccd_id) {
            case 1:
                $dato['gbf_nombre'] = $row['clg_id'] . '    ' . $row['clg_nombre'];
                $dato['ali_beneficiarios'] = $row['tec_techoPresupuestal'];
                $dato['gff'] = array_key_exists('gff', $row) ? $row['gff'] : null;
                $dato['clg'] = array_key_exists('clg', $row) ? $row['clg'] : null;
                break;
            case 2:
                $dato['asr_nombre'] = $row['asr_nombre'];
                $dato['gbf_nombre'] = $row['gbf_nombre'];
                $dato['ali_beneficiarios'] = $row['ali_beneficiarios'];
                $dato['ali_cuotaDiaria'] = $row['ali_cuotaDiaria'];
                $dato['ali_diasOtorgados'] = $row['ali_diasOtorgados'];
                break;
            case 3:
                $dato['cff_id'] = $row['cff_id'];
                $dato['fte_id'] = $row['fte_id'];
                $dato['sft_id'] = $row['sft_id'];
                $dato['ori_id'] = $row['ori_id'];
                $dato['sor_id'] = $row['sor_id'];
                $dato['cfi_id'] = $row['cfi_id'];
                $dato['cfi_anio'] = $row['cfi_anio'];
                $dato['tcp_id'] = $row['tcp_id'];
                break;
            default:
                echo 'kym';
                break;
        }
        array_push($datos, $dato);
    }  // fin while

    if (count($datos) == 0) {
        $dato = [
            'gpo_id'     => $gpo_id,
            'ur_id'      => $ur_id,
            'ur_clave'   => $gpo_id . $ur_id,
            'ur_nombre'  => '',
            'ue_id'      => '',
            'ue_nombre'  => '',
            'epg_id'     => $epg_id,
            'prg_id'     => '',
            'prg_nombre' => '',
            'spr_id'     => '',
            'spr_nombre' => '',
            'py_id'      => '',
            'py_nombre'  => '',
            'oa_id'      => '',
            'oa_nombre'  => '',
            'pg_id'      => '',
            'pg_nombre'  => '',
            'pe_id'      => '',
            'pe_nombre'  => '',
            'gff'        => '',
            'clg'        => '',
            'ecf_id'     => $ecf_id,
            'ter_id'     => '',
            'ter_nombre' => '',
            'cfi_clave'  => '',
            'cfi_nombre' => ',,',
            'ced_id'     => '',
            'total'      => 0.00,
            'nivel'      => 1,
        ];

        array_push($datos, $dato);
    }
    return $datos;
}//fin consultaMgaComplementos
