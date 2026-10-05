<?php

/**
 * Description of consultaMgaComplementos
 *
 * Objective: Consultar datos en la base de datos de acuerdo al modulo que se requiera, modificar techos,
 *            altas de previsiones o ajustes.
 * Author : -----
 * Collaborators : Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Update: 26-Jul-2019
 * Permits: Esta pantalla no pregunta por permisos de escritura.
 **/

function consultaMgaComplementos($gpoId, $urId, $ueId, $epgId, $ecfId, $ccdId)
{
    if ($ueId != '') {
        $ueSql = "AND ue_id = " . $ueId;
    } else {
        $ueSql = "";
    }

    if ($epgId != '') {
        $epgSql = "AND epg_id = " . $epgId;
    } else {
        $epgSql = "";
    }

    if ($ecfId != '') {
        $ecfSql = "AND ecf_id = " . $ecfId;
    } else {
        $ecfSql = "";
    }

    $whereAdicional = '';
    $parametros = [];

    switch ($ccdId) {
        case 1:
            $whereAdicional = 'WHERE gpo_id = :gpoId AND ur.ur_id = :urId';
            $parametros[':gpoId'] = !is_null($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : 'null';
            $parametros[':urId'] = !is_null($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : 'null';

            if (!empty($_POST['ter_id']) && empty($_POST['clg_id'])) {
                $whereAdicional .= ' AND ter.ter_id = :terId';
                $parametros[':terId'] = $_POST['ter_id'];
            } else if (empty($_POST['ter_id']) && !empty($_POST['clg_id'])) {
                $whereAdicional .= ' AND clg.clg_id = :clgId';
                $parametros[':clgId'] = $_POST['clg_id'];
            } else if (!empty($_POST['ter_id']) && !empty($_POST['clg_id'])) {
                $whereAdicional .= ' AND ter.ter_id = :terId AND clg.clg_id = :clgId';
                $parametros[':terId'] = $_POST['ter_id'];
                $parametros[':clgId'] = $_POST['clg_id'];
            }

            break;
        case 2:
            if ($_POST['prg_id'] != '' && $_POST['spr_id'] == 'Seleccione una opcion de la lista') {
                $whereAdicional = 'WHERE prg.prg_id = :prgId';
                $parametros[':prgId'] = $_POST['prg_id'];
            } else if ($_POST['prg_id'] != '' && empty($_POST['spr_id'])) {
                $whereAdicional = 'WHERE prg.prg_id = :prgId';
                $parametros[':prgId'] = $_POST['prg_id'];
            } else if (!empty($_POST['prg_id']) && !empty($_POST['spr_id'])) {
                $whereAdicional = 'WHERE prg.prg_id = :prgId AND spr.spr_id = :sprId';
                $parametros[':prgId'] = $_POST['prg_id'];
                $parametros[':sprId'] = $_POST['spr_id'];
            }

            break;
        case 3:
            $whereAdicional = '';
            $join_subquery = "";
            $filtro = "";

            if (!empty($_POST['pg_id'])) {
                $filtro .= "AND pg_id = :pgId ";
                $parametros[':pgId'] = $_POST['pg_id'];
            }

            if (!empty($_POST['pe_id'])) {
                $filtro .= "AND pe_id = :peId ";
                $parametros[':peId'] = $_POST['pe_id'];
            }

            if (!empty($_SESSION[_S]['cvefin']->ecf)) {
                $filtro .= "AND ecf_id = " . $_SESSION[_S]['cvefin']->ecf . " ";
            }

            break;
    }

    switch ($ccdId) {
        case 1:
            $atributo = "";
            $atributoSubconsulta = 't.tec_id, ter.ter_id, clg.clg_id, t.tec_techoPresupuestal, t.tec_techoPresupuestal AS total,';
            $from = 'cat_Techos t';
            $join = '
                JOIN ' . BD_SINPRES . '.cat_TipoRecurso ter USING(ter_id)
                JOIN ' . BD_CTRL . '.cat_ClaseGasto clg USING(clg_id)
                ';
            $agrupador = ", t.tec_id";
            $whereSubquery = 'WHERE t.gpo_id = :clasAdmGpoId AND t.ur_id = :clasAdmUrId ';
            $parametros[':clasAdmGpoId'] = !is_null($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : 'null';
            $parametros[':clasAdmUrId'] = !is_null($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : 'null';
            break;
        case 2:
            $atributo = "";
            $atributoSubconsulta = 'CONCAT(a.oa_id, " - ", a.oa_nombre) AS oa,
                IFNULL(CONCAT(f.cff_id, "-", f.fte_id, f.sft_id, f.ori_id, f.sor_id, "-", f.cfi_id, f.cfi_anio), "") AS cveF,
                CASE WHEN pp.pg_id IS NULL THEN 0 ELSE 1 END AS tieneOG,
                SUM(pp.mon_ene + pp.mon_feb + pp.mon_mar + pp.mon_abr + pp.mon_may + pp.mon_jun + pp.mon_jul + pp.mon_ago + pp.mon_sep + pp.mon_oct + pp.mon_nov + pp.mon_dic) AS total,';
            $from = 'cat_ObraActividad a';
            $join = "
                INNER JOIN " . BD_CTRL . ".enc_Programatico epg USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                LEFT JOIN " . BD_CTRL . ".poa_OrigenRecurso ore USING(gpo_id, ur_id, epg_id)
                LEFT JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento f USING(ecf_id)
                LEFT JOIN " . BD_CTRL . ".poa_Prevision pp USING(gpo_id, ur_id, ue_id, epg_id, ecf_id)
                LEFT JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cfi USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                LEFT JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " so USING(cff_id, fte_id, sft_id, ori_id, sor_id)
                ";
            $agrupador = ", pp.pg_id, pp.pe_id";
            $whereSubquery = "WHERE a.gpo_id = :subGpoId AND a.ur_id = :subUrId " . $ueSql . " AND a.oa_id != 0
                $epgSql $ecfSql";
            $parametros[':subGpoId'] = $gpoId;
            $parametros[':subUrId'] = $urId;
            break;
        case 3:
            $atributo = "tcp_id,";
            $atributoSubconsulta = "
                p.ecf_id,
                p.gpo_id,p.ur_id,p.ue_id,
                p.prg_id,p.spr_id,p.py_id,p.oa_id,
                p.pg_id,p.pe_id,
                p.tcp_id,
                p.epg_id,
                p.pre_claveCompleta,
                apr.apr_ene, apr.apr_feb, apr.apr_mar, apr.apr_abr, apr.apr_may, apr.apr_jun,
                apr.apr_jul, apr.apr_ago, apr.apr_sep, apr.apr_oct, apr.apr_nov, apr.apr_dic,
                (ori.ori_ene+ori.ori_feb+ori.ori_mar+ori.ori_abr+ori.ori_may+ori.ori_jun+ori.ori_jul+ori.ori_ago+ori.ori_sep+ori.ori_oct+ori.ori_nov+ori.ori_dic) total,";
            $from = 'presupuesto p';
            $join = "
                INNER JOIN " . BD_CTRL . ".cat_UnidadResponsable ur USING (gpo_id, ur_id)
                INNER JOIN " . BD_CTRL . ".presupuestoOriginal ori USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                $join_subquery
                LEFT JOIN " . BD_CTRL . ".presupuestoAprobado apr USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                ";
            $agrupador = "";
            $whereSubquery = "WHERE p.gpo_id = :clasAdmGpoId AND p.ur_id = :clasAdmUrId";
            $parametros[':clasAdmGpoId'] = !is_null($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : 'null';
            $parametros[':clasAdmUrId'] = !is_null($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : 'null';

            if (!empty($_SESSION[_S]['clasAdm-prg'])) {
                $whereSubquery .= " AND p.prg_id = :clasAdmPrgId";
                $parametros[':clasAdmPrgId'] = $_SESSION[_S]['clasAdm-prg'];
            }

            if (!empty($_SESSION[_S]['clasAdm-spr'])) {
                $whereSubquery .= " AND p.spr_id = :clasAdmSprId";
                $parametros[':clasAdmSprId'] = $_SESSION[_S]['clasAdm-spr'];
            }

            if (!empty($_SESSION[_S]['clasAdm-py'])) {
                $whereSubquery .= " AND p.py_id = :clasAdmPyId";
                $parametros[':clasAdmPyId'] = $_SESSION[_S]['clasAdm-py'];
            }

            if (!empty($_SESSION[_S]['clasAdm-oa'])) {
                $whereSubquery .= " AND p.oa_id = :clasAdmOaId";
                $parametros[':clasAdmOaId'] = $_SESSION[_S]['clasAdm-oa'];
            }
            $whereSubquery .= " AND ur_poa = 1 AND p.tcp_id != 'P'";
            break;
        default:
            break;
    }
    switch ($ccdId) {
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
                    $whereSubquery
                    GROUP BY t.gpo_id, t.ur_id $agrupador
                    ORDER BY ter.ter_id, clg.clg_id
                )cedula
                    INNER JOIN " . BD_SINPRES . ".cat_TipoRecurso ter USING ( ter_id )
                    JOIN " . BD_CTRL . ".cat_ClaseGasto clg USING(clg_id)
                    INNER JOIN " . BD_CTRL . ".cat_UnidadResponsable ur USING( gpo_id, ur_id )
                    $whereAdicional
                ORDER BY gpo_id, ur.ur_id, ter.ter_id, clg.clg_id, nivel
            ";
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
                    $whereSubquery
                    GROUP BY a.gpo_id, a.ur_id, a.prg_id, a.spr_id, a.ue_id, a.py_id, a.oa_id, f.ecf_id $agrupador
                )cedula
                    INNER JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento ecf USING ( ecf_id )
                    INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cfi USING( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                    INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " cs USING( cff_id, fte_id, sft_id, ori_id, sor_id )
                    INNER JOIN " . BD_SINPRES . ".cat_TipoRecurso ter USING ( ter_id )
                    INNER JOIN " . BD_CTRL . ".cat_ObraActividad oa USING( gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id )
                    INNER JOIN " . BD_CTRL . ".cat_Proyecto py USING( gpo_id, ur_id, prg_id, spr_id, py_id )
                    INNER JOIN " . BD_CTRL . ".cat_Subprograma spr USING( gpo_id, ur_id, prg_id, spr_id )
                    INNER JOIN " . BD_CTRL . ".cat_Programa prg USING( gpo_id, ur_id, prg_id )
                    INNER JOIN " . BD_CTRL . ".cat_UnidadEjecutora ue USING( gpo_id, ur_id, ue_id )
                    INNER JOIN " . BD_CTRL . ".cat_UnidadResponsable ur USING( gpo_id, ur_id )
                    $whereAdicional
                ORDER BY gpo_id, ur_id, prg_id, spr_id, ue_id, py_id, oa_id, cfi.cfi_clave, pg_id, pe_id, nivel
            ";
            break;
        case 3:
            $sql = "
                SELECT gpo_id, ur.ur_clave, ur_id, ur.ur_nombre,
                    ue_id, ue.ue_nombre,
                    prg_id, prg.prg_nombre,
                    spr_id, spr.spr_nombre,
                    py_id, py.py_nombre,
                    oa_id, oa.oa_nombre,
                    pg.pg_id, pg.pg_nombre,
                    pe.pe_id, pe.pe_nombre,
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
                    $whereSubquery
                    $agrupador
                    ORDER BY p.pre_claveCompleta
                )cedula
                    INNER JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento ecf USING ( ecf_id )
                    INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cfi USING( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                    INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " cs USING( cff_id, fte_id, sft_id, ori_id, sor_id )
                    INNER JOIN " . BD_SINPRES . ".cat_TipoRecurso ter USING ( ter_id )

                    INNER JOIN " . BD_CTRL . ".cat_PartidaGenerica pg USING( pg_id )
                    INNER JOIN " . BD_CTRL . ".cat_PartidaEspecifica pe USING( pg_id, pe_id )
                    INNER JOIN " . BD_CTRL . ".cat_ObraActividad oa USING( gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id )
                    INNER JOIN " . BD_CTRL . ".cat_Proyecto py USING( gpo_id, ur_id, prg_id, spr_id, py_id )
                    INNER JOIN " . BD_CTRL . ".cat_Subprograma spr USING( gpo_id, ur_id, prg_id, spr_id )
                    INNER JOIN " . BD_CTRL . ".cat_Programa prg USING( gpo_id, ur_id, prg_id )
                    INNER JOIN " . BD_CTRL . ".cat_UnidadEjecutora ue USING( gpo_id, ur_id, ue_id )
                    INNER JOIN " . BD_CTRL . ".cat_UnidadResponsable ur USING( gpo_id, ur_id )
                    $whereAdicional
                GROUP BY gpo_id, ur_id, prg_id, spr_id, ue_id, py_id, oa_id, cfi.cfi_clave,cfi.cfi_nombre,ter.ter_id, pg_id, pe_id, tcp_id, nivel, total
            ";
    }
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($parametros);
    $conexion->desconectar();

    $datos = array();
    while ($row = $conexion->extraer()) {
        $dato = array(
            'gpo_id' => $row['gpo_id'] ?? '',
            'ur_id' => $row['ur_id'] ?? '',
            'ur_clave' => $row['ur_clave'] ?? '',
            'ur_nombre' => $row['ur_nombre'] ?? '',
            'ue_id' => $row['ue_id'] ?? '',
            'ue_nombre' => $row['ue_nombre'] ?? '',
            'epg_id' => $row['epg_id'] ?? '',
            'prg_id' => $row['prg_id'] ?? '',
            'prg_nombre' => $row['prg_nombre'] ?? '',
            'spr_id' => $row['spr_id'] ?? '',
            'spr_nombre' => $row['spr_nombre'] ?? '',
            'py_id' => $row['py_id'] ?? '',
            'py_nombre' => $row['py_nombre'] ?? '',
            'oa_id' => $row['oa_id'] ?? '',
            'oa_nombre' => $row['oa_nombre'] ?? '',
            'pg_id' => $row['pg_id'] ?? '',
            'pg_nombre' => $row['pg_nombre'] ?? '',
            'pe_id' => $row['pe_id'] ?? '',
            'pe_nombre' => $row['pe_nombre'] ?? '',
            'ecf_id' => $row['ecf_id'] ?? '',
            'ter_id' => $row['ter_id'] ?? '',
            'ter_nombre' => $row['ter_nombre'] ?? '',
            'cfi_clave' => $row['cfi_clave'] ?? '',
            'cfi_nombre' => !empty($row['cfi_nombre']) ? utf8Decode($row['cfi_nombre']) : '',
            'tcp_id' => $row['tcp_id'] ?? '',
            'total' => $row['total'] ?? '',
            'nivel' => $row['nivel'] ?? '',
        );

        switch ($ccdId) {
            case 1:
                $dato['gbf_nombre'] = !empty($row['clg_id']) && !empty($row['clg_nombre']) ? $row['clg_id'] . '    ' . $row['clg_nombre'] : '';
                $dato['ali_beneficiarios'] = $row['tec_techoPresupuestal'] ?? '';
                $dato['gff'] = $row['gff'] ?? '';
                $dato['clg'] = $row['clg'] ?? '';
                break;
            case 2:
                $dato['asr_nombre'] = $row['asr_nombre'] ?? '';
                $dato['gbf_nombre'] = $row['gbf_nombre'] ?? '';
                $dato['ali_beneficiarios'] = $row['ali_beneficiarios'] ?? '';
                $dato['ali_cuotaDiaria'] = $row['ali_cuotaDiaria'] ?? '';
                $dato['ali_diasOtorgados'] = $row['ali_diasOtorgados'] ?? '';
                break;
            case 3:
                $dato['cff_id'] = $row['cff_id'] ?? '';
                $dato['fte_id'] = $row['fte_id'] ?? '';
                $dato['sft_id'] = $row['sft_id'] ?? '';
                $dato['ori_id'] = $row['ori_id'] ?? '';
                $dato['sor_id'] = $row['sor_id'] ?? '';
                $dato['cfi_id'] = $row['cfi_id'] ?? '';
                $dato['cfi_anio'] = $row['cfi_anio'] ?? '';
                $dato['tcp_id'] = $row['tcp_id'] ?? '';
                break;
            default:
                break;
        }
        array_push($datos, $dato);
    }
    if (count($datos) == 0) {
        $dato = array(
            'gpo_id' => $gpoId,
            'ur_id' => $urId,
            'ur_clave' => $gpoId . $urId,
            'ur_nombre' => '',
            'ue_id' => '',
            'ue_nombre' => '',
            'epg_id' => $epgId,
            'prg_id' => '',
            'prg_nombre' => '',
            'spr_id' => '',
            'spr_nombre' => '',
            'py_id' => '',
            'py_nombre' => '',
            'oa_id' => '',
            'oa_nombre' => '',
            'pg_id' => '',
            'pg_nombre' => '',
            'pe_id' => '',
            'pe_nombre' => '',
            'ecf_id' => $ecfId,
            'ter_id' => '',
            'ter_nombre' => '',
            'cfi_clave' => '',
            'cfi_nombre' => ',,',
            'ced_id' => '',
            'tcp_id' => '',
            'total' => 0.00,
            'nivel' => 1,
        );
        array_push($datos, $dato);
    }
    return $datos;
}
