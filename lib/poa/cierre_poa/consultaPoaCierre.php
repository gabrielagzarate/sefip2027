<?php

/**
 * Description of function consultaPoaCierre
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 31-ago-2018
 * Comment: -----
 * Objective: Consultar datos en la base de datos de acuerdo al modulo que se requiera, cierre poa.
 * Regla de Negocio: -----
 *
 * */
require_once __DIR__ . '/../../../lib/constantes.php';

function consultaPoaCierre($gpo_id, $ur_id, $epg_id = '', $ecf_id = '')
{
    $sql = '
        SELECT
            gpo_id,ur.ur_clave AS ur_id,ur.ur_nombre,ter.ter_id,ter.ter_nombre,crc.crc_id,crc.crc_nombre,
            IFNULL(pre_original, 0) as pre_original,tec_techoPresupuestal,ur_tipoCierre,eur.eur_id,eur.eur_nombre,ur_estadoCosteo,9 AS nivel
        FROM cat_CriterioCierre crc
        LEFT JOIN(
            SELECT tec.gpo_id,tec.ur_id,ter_id,crc_id,sum(tec.tec_techoPresupuestal) AS tec_techoPresupuestal
            FROM cat_Techos tec
                INNER JOIN cat_ClaseGasto clg USING(clg_id)
            WHERE   tec.gpo_id = ? and tec.ur_id = ?
            GROUP BY ter_id,crc_id
                 )C USING(crc_id)
        LEFT JOIN(
            SELECT  ter_id,crc_id,sum(ppo.pre_original) as pre_original
            FROM presupuesto pre
            INNER JOIN presupuestoOriginal ppo USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            INNER JOIN cat_PartidaEspecifica pe USING(pg_id,pe_id)
            INNER JOIN cat_ClaseGasto clg USING(clg_id)
            INNER JOIN sinpres.cat_SuborigenRecurso' . APP_EJERCICIO . ' sub USING(cff_id,fte_id,sft_id,ori_id,sor_id)
            INNER JOIN sinpres.cat_TipoRecurso ter USING(ter_id)
            WHERE   pre.gpo_id = ? and pre.ur_id = ?
            GROUP BY ter_id,crc_id
                 ) B USING(ter_id,crc_id)
        INNER JOIN cat_UnidadResponsable ur USING(gpo_id,ur_id)
        INNER JOIN sinpres.cat_TipoRecurso ter USING(ter_id)
        INNER JOIN cat_EstadoUr eur USING(eur_id)
        HAVING ter.ter_id IS NOT NULL AND (pre_original IS NOT NULL  OR tec_techoPresupuestal >0)
        ORDER BY ter.ter_id,crc.crc_id
    ';
    $params = [$gpo_id, $ur_id, $gpo_id, $ur_id];
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($params);
    $conexion->desconectar();

    $datos = [];
    while ($row = $conexion->extraer()) {
        $dato = ['nivel' => $row['nivel']];
        $dato['gpo_id'] = $row['gpo_id'];
        $dato['ur_id'] = $row['ur_id'];
        $dato['ur_nombre'] = $row['ur_nombre'];
        $dato['ter_id'] = $row['ter_id'];
        $dato['ter_nombre'] = $row['ter_nombre'];
        $dato['gran_fuente'] = $row['ter_id'] . '-' . $row['ter_nombre'];
        $dato['crc_id'] = $row['crc_id'];
        $dato['crc_nombre'] = $row['crc_nombre'];
        $dato['clase_gasto'] = $row['crc_id'] . '-' . $row['crc_nombre'];
        $dato['pre_original'] = $row['pre_original'];
        $dato['tec_techoPresupuestal'] = $row['tec_techoPresupuestal'];
        $dato['diferencia'] = $row['tec_techoPresupuestal'] - $row['pre_original'];
        $dato['ur_tipoCierre'] = $row['ur_tipoCierre'];
        $dato['eur_id'] = $row['eur_id'];
        $dato['eur_nombre'] = $row['eur_nombre'];
        $dato['ur_estadoCosteo'] = $row['ur_estadoCosteo'];

        array_push($datos, $dato);
    }

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
}

function consultaEncuadreNoVinculacionMir($limit)
{
    $limite = ($limit != 0) ? 'LIMIT ' . $limit : ' ';
    $sql = '
        SELECT
            CONCAT(gpo_id,ur_id,"-",prg_id,"-",spr_id,"-",py_id,"-",oa_id) AS encuadre,
            IF(oa_nombre IS NULL, "SIN DESCRIPCIÓN", oa_nombre) AS concepto
        FROM (
            SELECT
                epg_id,gpo_id,ur_id,
                prg_id,spr_id,py_id,
                oa_id,oa_nombre,
                (
                SELECT evi_id
                        FROM cat_MetaActividad
                        WHERE epg_id = e.epg_id
                        LIMIT 1) AS evi_id
                FROM cat_ObraActividad
                    INNER JOIN enc_Programatico e USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                WHERE oa_id != 0
                    AND gpo_id = ?
                    AND ur_id = ?
                    AND ((eoa_id = 5 AND toa_id = 2) OR (eoa_id = 4 AND toa_id = 1))
                    AND prg_id <= 899
                    ) AS t
            LEFT JOIN cat_EstadoVinculacion USING (evi_id)
        WHERE evi_id in (1,2,3,4,5,6,7)
            ' . $limite . '
        ;
    ';
    return $sql;
}
