<?php

/**
 * Objective: Consulta datos de la estructura programatica de actividad/obra.
 * Author : -----
 * Created: 16-Agt-2018
 * Update for SEFIP2020: 10-Mayo-2019
 * Migrated: 08-Jul-2020
 * Collaborators : Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Permits: Esta pantalla no pregunta por permisos de escritura.
 **/

function consultapoaProgramacion($gpo_id, $ur_id, $ccd_id, $prg_id, $spr_id, $py_id, $eoa_id)
{

    $prg_sql = "";
    $spr_sql = "";
    $py_sql = "";
    $eoa_sql = "";
    $params = [];
    if ($prg_id != "" && $prg_id != 'null') {
        $prg_sql = "AND oa.prg_id = :prg ";
        $params['prg'] = $prg_id;
    }
    if ($spr_id != "" && $spr_id != 'null') {
        $spr_sql = "AND oa.spr_id = :spr ";
        $params['spr'] = $spr_id;
    }
    if ($py_id != "" && $py_id != 'null') {
        $py_sql = "AND oa.py_id = :py";
        $params['py'] = $py_id;
    }
    if ($eoa_id != "" && $eoa_id != 'null') {
        $eoa_sql = "AND eoa.eoa_id= :eoa ";
        $params['eoa'] = $eoa_id;
    }

    switch ($ccd_id) {
        case 2:
            $atributo = "";
            $atributoSubconsulta = '
                oa.gpo_id,
                oa.ur_id,
                oa.prg_id,
                oa.spr_id,
                oa.py_id,
                oa.oa_id,
                oa.oa_nombre,
                oa.sts_id,
                eoa.eoa_id,
                eoa.eoa_nombre,
                eoa.eoa_color,
                IF(toa.toa_id = 1,
                    "SUB. PRESUPUESTO",
                    "SUB. PLANEACIÓN") toa_nombre,
                toa.toa_id,
                "" vacio,
            ';
            $from = 'cat_ObraActividad oa';
            $join = '
                    JOIN
                cat_TipoObraActividad toa USING (toa_id)
                    JOIN
                cat_EstadoObraActividad eoa USING (eoa_id)
            ';
            $where_subquery = "
            WHERE
                oa.gpo_id = $gpo_id AND oa.ur_id =$ur_id
                $prg_sql
                $spr_sql
                $py_sql
                $eoa_sql

                AND oa.oa_id != 0
            ";
            break;
    }
    switch ($ccd_id) {
        case 2:
            $sql = "
                SELECT
                    gpo_id, ur.ur_clave, ur_id,
                    ur.ur_nombre,
                    prg_id, prg.prg_nombre,
                    spr_id, spr_nombre,
                    py_id, py.py_nombre,
                    oa_id, oa_nombre,
                    $atributo
                    eoa_id, eoa_nombre,
                    nivel
                FROM (
                    SELECT $atributoSubconsulta
                        9 AS nivel
                    FROM $from
                    $join
                    $where_subquery
                    ORDER BY oa_id
                )cedula
                    INNER JOIN cat_Proyecto py USING( gpo_id, ur_id, prg_id, spr_id, py_id )
                    INNER JOIN cat_Subprograma spr USING( gpo_id, ur_id, prg_id, spr_id )
                    INNER JOIN cat_Programa prg USING( gpo_id, ur_id, prg_id )
                    INNER JOIN cat_UnidadResponsable ur USING( gpo_id, ur_id )
                ORDER BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, nivel ;
            ";
            break;
    }

    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($params);
    $conexion->desconectar();

    $datos = array();
    while ($row = $conexion->extraer()) {

        $dato = array(
            'gpo_id' => $row['gpo_id'],
            'ur_id' => $row['ur_id'],
            'ur_clave' => $row['ur_clave'],
            'ur_nombre' => $row['ur_nombre'],
            'prg_id' => $row['prg_id'],
            'prg_nombre' => $row['prg_nombre'],
            'spr_id' => $row['spr_id'],
            'spr_nombre' => $row['spr_nombre'],
            'py_id' => $row['py_id'],
            'py_nombre' => $row['py_nombre'],
            'oa_id' => $row['oa_id'],
            'oa_nombre' => $row['oa_nombre'],
            'nivel' => $row['nivel'],
        );

        switch ($ccd_id) {
            case 2:
                $dato['eoa_nombre'] = $row['eoa_nombre'];
                $dato['total'] = 0;
                break;
        }

        array_push($datos, $dato);
    }
    if (count($datos) == 0) {
        $dato = array(
            'gpo_id' => '',
            'ur_id' => '',
            'ur_clave' => '',
            'ur_nombre' => '',
            'prg_id' => '',
            'prg_nombre' => '',
            'spr_id' => '',
            'spr_nombre' => '',
            'py_id' => '',
            'py_nombre' => '',
            'oa_id' => '',
            'oa_nombre' => '',
            'nivel' => '',
            'eoa_nombre' => '',
            'total' => '',
        );
        array_push($datos, $dato);
    }
    return $datos;
}
