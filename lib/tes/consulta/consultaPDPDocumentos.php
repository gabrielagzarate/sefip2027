<?php

const CONSULTA_PDP_INNER_JOIN = ' INNER JOIN ';

function consultaPDPSqlInnerJoin($tabla)
{
    return CONSULTA_PDP_INNER_JOIN . $tabla;
}

function consultaPDPSelectModoTipoLote()
{
    return '
                SELECT
                    CASE WHEN
                        l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                    THEN
                        "FEDERAL"
                    ELSE
                        "ESTATAL"
                    END AS modo, 
                    tlo.tlo_nombre AS tipo,
                    CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", "L")),
                        LPAD(l.msc_id, 2, "0"),
                        LPAD(l.lot_id, 4, "0"),
                        SUBSTR(l.lot_ejercicio, 3)
                    ) AS lote';
}

function consultaPDPFromLoteYTipo()
{
    return ' FROM ' . BD_TESORERIA . '.lot_Lote AS l'
        . consultaPDPSqlInnerJoin(BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)')
        . consultaPDPSqlInnerJoin(BD_TESORERIA . '.lot_LoteClc USING ( fpa_id, msc_id, lot_ejercicio, lot_id )')
        . consultaPDPSqlInnerJoin(BD_TESORERIA . '.lot_LoteDocumento AS ld USING ( fpa_id, msc_id, lot_ejercicio, lot_id, gpo_id, ur_id, clc_id )');
}

function consultaPDPSelectColumnasEstandar()
{
    return consultaPDPSelectModoTipoLote() . ',
                    CONCAT(c.gpo_id, c.ur_id) AS ur, c.clc_id,
                    l.fpa_id, l.msc_id, l.lot_ejercicio, l.lot_id,
                    l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo, cx.clc_cereCo,
                    CONCAT(c.gpo_id, c.ur_id) ur_clave, c.clc_id AS clc, cx.clc_cereCo AS orden,
                    CURDATE() AS fecha, SUBSTR(l.lot_ejercicio, 3) AS anio,
                    ld.bdo_id AS documento_id, doc.bdo_referencia AS documento, bsf.cbs_id AS ctaCargo,
                    doc.rfc_id AS rfc, c.clc_beneficiario AS benef, c.bur_id AS ctaAbono,
                    ( doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable - doc.bdo_retencionContable ) importe,
                    "" AS lote_egreso, doc.bdo_concepto AS concepto, tlo.tlo_nombre, e.ecl_nombre AS estado,
                    ecf_clave, iba.iba_abreviatura AS banco, cbs.cbs_nombre AS nombre_cta';
}

function consultaPDPJoinsTesoreriaBancos()
{
    return consultaPDPSqlInnerJoin(BD_TESORERIA . '.vin_SefinClaveFinanciamiento bsf USING (ecf_id)')
        . consultaPDPSqlInnerJoin(BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING (cbs_id)')
        . consultaPDPSqlInnerJoin(BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)');
}

function consultaPDPJoinsDocumentosClc($bdCtrl)
{
    return consultaPDPSqlInnerJoin($bdCtrl . '.cat_BancoDocumentos AS doc USING (gpo_id, ur_id, bdo_id)')
        . consultaPDPSqlInnerJoin($bdCtrl . '.scp_Clc AS c USING (gpo_id, ur_id, clc_id)');
}

function consultaPDPJoinsAuxiliarUR($bdCtrl)
{
    return consultaPDPSqlInnerJoin($bdCtrl . '.scp_ClcAuxiliar AS cx USING (gpo_id, ur_id, clc_id)')
        . consultaPDPSqlInnerJoin($bdCtrl . '.cat_UnidadResponsable USING (gpo_id, ur_id)');
}

function consultaPDPJoinsProveedorFin($bdCtrl)
{
    return consultaPDPSqlInnerJoin($bdCtrl . '.vin_BancosUrProveedor AS vpr ON (c.bur_id = vpr.bur_id AND doc.rfc_id = vpr.rfc_id)')
        . consultaPDPSqlInnerJoin($bdCtrl . '.enc_ClaveFinanciamiento USING (ecf_id)');
}

function consultaPDPJoinAntClc()
{
    return consultaPDPSqlInnerJoin(BD_CTRLANT . '.scp_Clc AS ant USING (gpo_id, ur_id, clc_id)');
}

function consultaPDPJoinEstadoClc($bdCtrl, $aliasClc)
{
    return consultaPDPSqlInnerJoin($bdCtrl . '.cat_EstadoClc AS e ON (' . $aliasClc . '.ecl_id = e.ecl_id)');
}

function consultaPDPSqlActual()
{
    return consultaPDPSelectColumnasEstandar()
        . consultaPDPFromLoteYTipo()
        . consultaPDPJoinsDocumentosClc(BD_CTRL)
        . consultaPDPJoinEstadoClc(BD_CTRL, 'c')
        . consultaPDPJoinsAuxiliarUR(BD_CTRL)
        . consultaPDPJoinsTesoreriaBancos()
        . consultaPDPJoinsProveedorFin(BD_CTRL) . '
            ';
}

function consultaPDPSqlHistorico($bdCtrl)
{
    return consultaPDPSelectColumnasEstandar()
        . consultaPDPFromLoteYTipo()
        . consultaPDPJoinsDocumentosClc($bdCtrl)
        . consultaPDPJoinAntClc()
        . consultaPDPJoinEstadoClc($bdCtrl, 'ant')
        . consultaPDPJoinsAuxiliarUR($bdCtrl)
        . consultaPDPJoinsTesoreriaBancos()
        . consultaPDPJoinsProveedorFin($bdCtrl) . '
            ';
}

function consultaPDPDocumentos($lot_ejercicio = '', $fpa_id = '', $msc_id = '', $lot_id = '')
{
    $sql = '';
    $sqlAnio = '';
    switch ($lot_ejercicio) {
        case 2026:
            $sql = consultaPDPSqlActual();
            break;
        case 2025:
            $sql = consultaPDPSqlHistorico(BD_CTRL_2025);
            $sqlAnio = " AND ant.ejr_id_clc = 2025";
            break;
        case 2024:
            $sql = consultaPDPSqlHistorico(BD_CTRL_2024);
            $sqlAnio = " AND ant.ejr_id_clc = 2024";
            break;
        case 2023:
            $sql = consultaPDPSqlHistorico(BD_CTRL_2023);
            $sqlAnio = " AND ant.ejr_id_clc = 2023";
            break;
        case 2022:
            $sql = consultaPDPSqlHistorico(BD_CTRL_2022);
            $sqlAnio = " AND ant.ejr_id_clc = 2022";
            break;
        case 2021:
        case 2020:
        case 2019:
            $sql = consultaPDPSqlHistorico(BD_CTRL_2019);
            $sqlAnio = " AND ant.ejr_id_clc = 2019";
            break;
        case 2018:
            $sql = consultaPDPSqlHistorico(BD_CTRL_2018) . '
                LEFT JOIN ' . BD_CTRL_2018 . '.scp_DetalleLoteEgresos AS de ON (c.gpo_id = de.gpo_id AND c.ur_id = de.ur_id AND c.clc_id = de.clc_id)
                LEFT JOIN ' . BD_CTRL_2018 . '.scp_LoteEgresos AS le USING(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
            ';
            $sqlAnio = "AND ant.ejr_id_clc = 2018";
            break;
        case 2017:
            $sql = consultaPDPSelectModoTipoLote() . ',
                    CONCAT(c.gpo_id, c.ur_id) AS ur, c.clc_id,
                    l.fpa_id, l.msc_id, l.lot_ejercicio, l.lot_id,
                    l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo, cx.clc_cereCo,
                    CONCAT(c.gpo_id, c.ur_id) ur_clave, c.clc_id AS clc, cx.clc_cereCo AS orden,
                    CURDATE() AS fecha, SUBSTR(l.lot_ejercicio, 3) AS anio,
                    ld.bdo_id AS documento_id, doc.bdo_referencia AS documento, bsf.bsf_id AS ctaCargo,
                    doc.rfc_id AS rfc, c.clc_beneficiario AS benef, c.bur_id AS ctaAbono,
                    ( doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable - doc.bdo_retencionContable ) importe,
                    le.loe_clave AS lote_egreso, doc.bdo_concepto AS concepto, tlo.tlo_nombre, e.ecl_nombre AS estado,
                    ecf_clave, iba.iba_nombre AS banco, cbs.bsf_nombre AS nombre_cta'
                . consultaPDPFromLoteYTipo()
                . consultaPDPJoinsDocumentosClc(BD_CTRL_2017)
                . consultaPDPJoinAntClc()
                . consultaPDPJoinEstadoClc(BD_CTRL_2017, 'ant')
                . consultaPDPJoinsAuxiliarUR(BD_CTRL_2017)
                . consultaPDPSqlInnerJoin(BD_CTRL_2017 . '.enc_BancosSefinCveFin bsf USING (ecf_id)')
                . consultaPDPSqlInnerJoin(BD_CTRL_2017 . '.scp_BancosSefin AS cbs USING (bsf_id)')
                . consultaPDPSqlInnerJoin(BD_CTRL_2017 . '.cat_InstitucionesBancarias AS iba USING (iba_id)')
                . consultaPDPJoinsProveedorFin(BD_CTRL_2017) . '
                LEFT JOIN ' . BD_CTRL_2017 . '.scp_DetalleLoteEgresos AS de ON (c.gpo_id = de.gpo_id AND c.ur_id = de.ur_id AND c.clc_id = de.clc_id)
                LEFT JOIN ' . BD_CTRL_2017 . '.scp_LoteEgresos AS le USING(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
            ';
            $sqlAnio = " AND ant.ejr_id_clc = :lotEjer";
            break;
        default:
            break;
    }

    $sqlExtra = ' 
        WHERE l.lot_ejr = :ejer AND l.fpa_id = :fpa
        AND l.msc_id = :msc
        AND l.lot_ejercicio = :lotEjer
        AND l.lot_id = :lotId
        '.$sqlAnio.'
        ORDER BY l.fpa_id, l.msc_id, l.lot_ejercicio, l.lot_id';

    $params = [
        'ejer' => APP_EJERCICIO,
        'fpa' => $fpa_id,
        'msc' => $msc_id,
        'lotEjer' => $lot_ejercicio,
        'lotId' => $lot_id
    ];

    $con = new AccesoDatos();
    $con->conectar();
    $con->setSql($sql . $sqlExtra);
    $con->consultar($params);
    $con->desconectar();
    $contador=1;
    $result = array();

    while ( $fila = $con->extraer() ) {
        $dato = array(
            'contador' => $contador++,
            'modo' => utf8Decode($fila['modo']),
            'tipo' => utf8Decode($fila['tipo']),
            'lot_ejercicio' => $fila['lot_ejercicio'],
            'lot_fechaGeneracion' => $fila['lot_fechaGeneracion'],
            'lot_fechaPago' => $fila['lot_fechaPago'],
            'clc_generacionCereCo' => $fila['clc_generacionCereCo'],
            'lote_egreso' => $fila['lote_egreso'],
            'ur' => $fila['ur'],
            'clc_id' => $fila['clc_id'],
            'clc_cereCo' => $fila['clc_cereCo'],
            'benef' => utf8Encode($fila['benef']),
            'concepto' => utf8Encode($fila['concepto']),
            'importe' => number_format($fila['importe'], 2, '.', ','),
            'banco' => utf8Decode($fila['banco']),
            'ctaCargo' => $fila['ctaCargo'],
            'nombre_cta' => utf8Decode($fila['nombre_cta']),
            'ctaAbono' => $fila['ctaAbono'],
            'ecf_clave' => $fila['ecf_clave'],
            'lote' => $fila['lote'],
            'estado' => ($fila['estado']),
        );

        array_push( $result, $dato );
    }
    return $result;
}
