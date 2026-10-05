<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function validacionPoliza($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array())
{
    $sqlNivel = false;
    $limitSql = '';
    $periodoSql = '';
    $params = array();
    $limitarConsultaUe = "";
    if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "") {
        $limitarConsultaUe = "WHERE ue_id =  :ue_id ";
        $params[':ue_id'] = $_SESSION[_S]['clasAdm-ue'];
    }

    if ($_SESSION[_S]['nvj_id'] > 2) {
        if (isset($parametroExtra['origen']) == 'controlador') {
            $sqlNivel = true;
        }
    }

    if (isset($parametroExtra['origen']) == 'controlador') {
        $limitSql = 'LIMIT 1';
    }

    if ($periodo['mesDesde'] == $periodo['mesHasta']){
        $periodoSql.= " AND msc_id =  :mesDesde ";
        $params[':mesDesde'] = $periodo['mesDesde'];
    } else{
        $periodoSql.= "AND (msc_id BETWEEN :mesDesde AND :mesHasta) ";
        $params[':mesDesde'] = $periodo['mesDesde'];
        $params[':mesHasta'] = $periodo['mesHasta'];
    } 

    $sql = "
        SELECT gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id,
            a, b, c, d,
            error, mensaje
        FROM(
            " . ($sqlNivel ? "" : "
            SELECT 1 AS error, 'Detalles insuficientes' AS mensaje,
                gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id,
                COUNT( x.pdx_id ) AS a, '' AS b, '' AS c, '' AS d
            FROM cg_PolizaMaestro m
                LEFT JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
            WHERE gpo_id = :gpo AND ur_id = :ur_id
                $periodoSql
                AND m.epl_id NOT IN ( 'C', 'H' )
            GROUP BY gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id
            HAVING a < 2
        UNION ALL
            SELECT 6 AS error, 'El nivel de registro 9999 esta reservado' AS mensaje,
                gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id,
                cta_cuenta AS a, '' AS b, '' AS c, '' AS d
            FROM cg_PolizaMaestro m
                INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                INNER JOIN cg_PlanCuentas USING( cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id )
            WHERE gpo_id = :gpo AND ur_id = :ur_id
                $periodoSql
                AND x.cta_id = '9999'
                AND m.epl_id != 'C'
            GROUP BY gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id,cta_cuenta
        UNION ALL ") . "
            (SELECT 2 AS error, 'Detalles insuficientes' AS mensaje,
                gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id,
                COUNT( x.pdx_id ) AS a, '' AS b, '' AS c, '' AS d
            FROM cg_PolizaMaestro m
                LEFT JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
            WHERE gpo_id = :gpo AND ur_id = :ur_id
                $periodoSql
                AND m.epl_id = 'H'
            GROUP BY gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id
            HAVING a < 1
            $limitSql )
        UNION ALL
            (SELECT 3 AS error, 'La poliza NO cuadra' AS mensaje,
                gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id,
                SUM( debe ) AS a, SUM( haber ) AS b, '' AS c, '' AS d
            FROM (
                SELECT gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id,
                    d.pdt_debe AS debe, d.pdt_haber AS haber
                FROM cg_PolizaMaestro m
                    LEFT OUTER JOIN cg_PolizaDetalle d USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                WHERE gpo_id = :gpo AND ur_id = :ur_id
                    $periodoSql
                    AND epl_id NOT IN ( 'C', 'H' )
            UNION ALL
                SELECT gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id,
                    x.pdx_debe AS debe , x.pdx_haber AS haber
                FROM  cg_PolizaMaestro m
                    LEFT OUTER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                WHERE gpo_id = :gpo AND ur_id = :ur_id
                    $periodoSql
                AND m.epl_id NOT IN ( 'C', 'H' )
            )d
            GROUP BY gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id
            HAVING a != b
            $limitSql )
        UNION ALL
            ( SELECT 4 AS error, 'El maestro y detalle no cuadra' AS mensaje,
                gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id,
                debe AS a, haber AS b, pma_debe AS c, pma_haber AS d
            FROM cg_PolizaMaestro m
                INNER JOIN(
                    SELECT gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id,
                        SUM( x.pdx_debe ) AS debe, SUM( x.pdx_haber ) AS haber
                    FROM cg_PolizaMaestro m
                        INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE gpo_id = :gpo AND ur_id = :ur_id
                        $periodoSql
                        AND m.epl_id NOT IN ( 'C', 'H' )
                    GROUP BY gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id
                )d USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
            GROUP BY gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id
            HAVING m.pma_debe != a OR m.pma_haber != b
            $limitSql )
        UNION ALL
            (SELECT 5 AS error, 'Cuenta Contable Incorrecta' AS mensaje,
                gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id,
                CONCAT( d.ger_id, d.cgp_id, d.rub_id, d.cgcu_id, d.cgsc_id, d.nat_id ) AS a,
                CONCAT( x.ger_id, x.cgp_id, x.rub_id, x.cgcu_id, x.cgsc_id, x.nat_id ) AS b, '' AS c, '' AS d
            FROM cg_PolizaMaestro m
                LEFT OUTER JOIN cg_PolizaDetalle d USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                LEFT OUTER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id, pdt_id )
            WHERE gpo_id = :gpo AND ur_id = :ur_id
                $periodoSql
                AND m.epl_id != 'C'
                AND ( d.ger_id, d.cgp_id, d.rub_id, d.cgcu_id, d.cgsc_id, d.cgc_id, d.cta_id, d.nat_id ) != ( x.ger_id, x.cgp_id, x.rub_id, x.cgcu_id, x.cgsc_id, x.cgc_id, x.cta_id, x.nat_id )
            $limitSql )
        UNION ALL
            (SELECT 7 AS error, 'El mes de la poliza es incorrecto' AS mensaje,
                gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id,
                MONTH( pma_fecha ) AS a, msc_id AS b, '' AS c, '' AS d
            FROM cg_PolizaMaestro
            WHERE gpo_id = :gpo AND ur_id = :ur_id
                $periodoSql
                AND msc_id NOT IN(0,13)
                AND epl_id != 'C'
                AND MONTH( pma_fecha ) != msc_id
            GROUP BY gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id
            $limitSql)
        UNION ALL
            (SELECT 8 AS error, 'El año de la poliza es incorrecto' AS mensaje,
                gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id,
                YEAR( pma_fecha ) AS a, '' AS b, '' AS c, '' AS d
            FROM cg_PolizaMaestro
            WHERE gpo_id = :gpo AND ur_id = :ur_id
                $periodoSql
                AND epl_id != 'C'
                AND YEAR( pma_fecha ) != " . APP_EJERCICIO . "
            GROUP BY gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id
            $limitSql )
        )validacion
        $limitarConsultaUe
    ";
    $params[':gpo'] = $gpo_id;
    $params[':ur_id'] = $ur_id;

    $contable = new AccesoDatos(); //variable de konexion para los saldos iniciales
    $contable->conectar();
    $contable->setSql($sql);
    $contable->consultar($params);
    $contable->desconectar();

    $datos = array();

    while ($rowContable = $contable->extraer()) {

        array_push($datos, array(
            'id' => $rowContable['gpo_id'] . $rowContable['ur_id'] . '.' . $rowContable['error'],
            'gpo_id' => $rowContable['gpo_id'],
            'ur_id' => $rowContable['ur_id'],
            'ur_clave' => $rowContable['gpo_id'] . $rowContable['ur_id'],
            'ue_id' => $rowContable['ue_id'],
            'msc_id' => $rowContable['msc_id'],
            'pot_id' => $rowContable['pot_id'],
            'prf_id' => $rowContable['prf_id'],
            'pma_id' => $rowContable['pma_id'],
            'a' => $rowContable['a'],
            'b' => $rowContable['b'],
            'c' => $rowContable['c'],
            'd' => $rowContable['d'],
            'error' => $rowContable['error'],
            'mensaje' => $rowContable['mensaje'],
        ));

    }

    if (!isset($datos)) {
        $datos = array(
            'id' => '',
            'gpo_id' => '',
            'ur_id' => '',
            'ur_clave' => '',
            'ue_id' => '',
            'msc_id' => '',
            'pot_id' => '',
            'prf_id' => '',
            'pma_id' => '',
            'a' => '',
            'b' => '',
            'c' => '',
            'd' => '',
            'error' => '',
            'mensaje' => '',
        );
    }
    return $datos;
}
