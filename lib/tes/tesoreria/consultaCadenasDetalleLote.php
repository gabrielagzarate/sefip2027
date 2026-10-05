<?php

/**
 * Description of function consultaCadenasDetalleLote
 *
 * @author Adriana Santiago Morales <adriana.morales@finanzasoaxaca.gob.mx>
 * Created: 06-Ene-2019
 * Update: 10-Nov-2022
 * Comment: -----
 * Objective: Consulta datos de acuerdo a la opcion requerida, cadenas productivas.
 * Regla de Negocio: -----
 *
 * */
function consultaCadenasDetalleLote($fpa_id, $msc_id, $lot_ejercicio, $lot_id, $tlo_id)
{
    $sql = '';
    $andSql = '';
    switch ($lot_ejercicio) {
        case 2017:
            $sql .= '
                SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                    CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                    c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                    c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.bsf_id) AS origen,
                    MAX(iba.iba_nombre) as banco, l.lot_ejercicio, MAX(cbs.bsf_nombre) AS nombre_cta,
                    l.lot_clave AS lote,
                    MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, le.loe_clave AS lote_egreso,
                    cx.clc_cereCo, "" AS contable,
                    CASE WHEN
                        l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                    THEN
                        "FEDERAL"
                    ELSE
                        "ESTATAL"
                    END AS modo,
                    tlo.tlo_nombre AS tipo
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                INNER JOIN ' . BD_CTRL_2017 . '.scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2017 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2017 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                INNER JOIN ' . BD_CTRL_2017 . '.enc_BancosSefinCveFin AS vscf USING (ecf_id)
                INNER JOIN ' . BD_CTRL_2017 . '.scp_BancosSefin AS cbs USING (bsf_id)
                INNER JOIN ' . BD_CTRL_2017 . '.cat_InstitucionesBancarias AS iba USING (iba_id)
                INNER JOIN ' . BD_CTRL_2017 . '.cat_EstadoClc AS e ON (ca.ecl_id = e.ecl_id)
                LEFT JOIN ' . BD_CTRL_2017 . '.scp_DetalleLoteEgresos AS de USING(gpo_id,ur_id,clc_id)
                LEFT JOIN ' . BD_CTRL_2017 . '.scp_LoteEgresos AS le USING(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)';
            $andSql = ' AND ca.ejr_id_clc = 2017 ';
            break;
        case 2018:
            $sql .= '
                SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                    CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                    c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                    c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.cbs_id) AS origen,
                    MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta, l.lot_ejercicio,
                    l.lot_clave AS lote,
                    MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, le.loe_clave AS lote_egreso,
                    cx.clc_cereCo, "" AS contable,
                    CASE WHEN
                        l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                    THEN
                        "FEDERAL"
                    ELSE
                        "ESTATAL"
                    END AS modo,
                    tlo.tlo_nombre AS tipo
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                INNER JOIN ' . BD_CTRL_2018 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca USING (gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2018 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2018 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                INNER JOIN ' . BD_CTRL_2018 . '.cat_EstadoClc AS e ON (ca.ecl_id = e.ecl_id)
                LEFT JOIN ' . BD_CTRL_2018 . '.scp_DetalleLoteEgresos AS de USING(gpo_id,ur_id,clc_id)
                LEFT JOIN ' . BD_CTRL_2018 . '.scp_LoteEgresos AS le USING(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)';
            $andSql = ' AND ca.ejr_id_clc = 2018 ';
            break;
        case 2019:
            $sql .= '
                SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                    CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                    c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                    c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.cbs_id) AS origen,
                    MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta, l.lot_ejercicio,
                    l.lot_clave AS lote,
                    MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, "" AS lote_egreso,
                    cx.clc_cereCo, "" AS contable,
                    CASE WHEN
                        l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                    THEN
                        "FEDERAL"
                    ELSE
                        "ESTATAL"
                    END AS modo,
                    tlo.tlo_nombre AS tipo
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                INNER JOIN ' . BD_CTRL_2019 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2019 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2019 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                INNER JOIN ' . BD_CTRL_2019 . '.cat_EstadoClc AS e ON (ca.ecl_id = e.ecl_id)
            ';
            $andSql = ' AND ca.ejr_id_clc = 2019 ';
            break;
        case 2020:
            $sql .= '
                SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                    CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                    c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                    c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.cbs_id) AS origen,
                    MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta, l.lot_ejercicio,
                    l.lot_clave AS lote,
                    MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, "" AS lote_egreso,
                    cx.clc_cereCo, "" AS contable,
                    CASE WHEN
                        l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                    THEN
                        "FEDERAL"
                    ELSE
                        "ESTATAL"
                    END AS modo,
                    tlo.tlo_nombre AS tipo
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                INNER JOIN ' . BD_CTRL_2020 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2020 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2020 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                INNER JOIN ' . BD_CTRL_2020 . '.cat_EstadoClc AS e ON (ca.ecl_id = e.ecl_id)
            ';
            $andSql = ' AND ca.ejr_id_clc = 2020 ';
            break;
        case 2021:
            $sql .= '
                SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                    CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                    c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                    c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.cbs_id) AS origen,
                    MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta, l.lot_ejercicio,
                    l.lot_clave AS lote,
                    MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, "" AS lote_egreso,
                    cx.clc_cereCo, "" AS contable,
                    CASE WHEN
                        l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                    THEN
                        "FEDERAL"
                    ELSE
                        "ESTATAL"
                    END AS modo,
                    tlo.tlo_nombre AS tipo
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                INNER JOIN ' . BD_CTRL_2021 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2021 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2021 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                INNER JOIN ' . BD_CTRL_2021 . '.cat_EstadoClc AS e ON (ca.ecl_id = e.ecl_id)
            ';
            $andSql = ' AND ca.ejr_id_clc = 2021 ';
            break;
        case 2022:
            $sql .= '
                SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                    CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                    c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                    c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.cbs_id) AS origen,
                    MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta, l.lot_ejercicio,
                    l.lot_clave AS lote,
                    MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, "" AS lote_egreso,
                    cx.clc_cereCo, "" AS contable,
                    CASE WHEN
                        l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                    THEN
                        "FEDERAL"
                    ELSE
                        "ESTATAL"
                    END AS modo,
                    tlo.tlo_nombre AS tipo
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                INNER JOIN ' . BD_CTRL_2022 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2022 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2022 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                INNER JOIN ' . BD_CTRL_2022 . '.cat_EstadoClc AS e ON (ca.ecl_id = e.ecl_id)
            ';
            $andSql = ' AND ca.ejr_id_clc = 2022 ';
            break;
        case 2023:
            $sql .= '
                SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                    CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                    c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                    c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.cbs_id) AS origen,
                    MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta, l.lot_ejercicio,
                    l.lot_clave AS lote,
                    MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, "" AS lote_egreso,
                    cx.clc_cereCo, "" AS contable,
                    CASE WHEN
                        l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                    THEN
                        "FEDERAL"
                    ELSE
                        "ESTATAL"
                    END AS modo,
                    tlo.tlo_nombre AS tipo
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                INNER JOIN ' . BD_CTRL_2023 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2023 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2023 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                INNER JOIN ' . BD_CTRL_2023 . '.cat_EstadoClc AS e ON (ca.ecl_id = e.ecl_id)
            ';
            $andSql = ' AND ca.ejr_id_clc = 2023 ';
            break;
        case 2024:
            $sql .= '
                SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                    CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                    c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                    c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.cbs_id) AS origen,
                    MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta, l.lot_ejercicio,
                    l.lot_clave AS lote,
                    MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, "" AS lote_egreso,
                    cx.clc_cereCo, "" AS contable,
                    CASE WHEN
                        l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                    THEN
                        "FEDERAL"
                    ELSE
                        "ESTATAL"
                    END AS modo,
                    tlo.tlo_nombre AS tipo
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                INNER JOIN ' . BD_CTRL_2024 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2024 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2024 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                INNER JOIN ' . BD_CTRL_2024 . '.cat_EstadoClc AS e ON (ca.ecl_id = e.ecl_id)
            ';
            $andSql = ' AND ca.ejr_id_clc = 2024 ';
            break;
        case 2025:
            $sql .= '
                SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                    CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                    c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                    c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.cbs_id) AS origen,
                    MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta, l.lot_ejercicio,
                    l.lot_clave AS lote,
                    MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, "" AS lote_egreso,
                    cx.clc_cereCo, "" AS contable,
                    CASE WHEN
                        l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                    THEN
                        "FEDERAL"
                    ELSE
                        "ESTATAL"
                    END AS modo,
                    tlo.tlo_nombre AS tipo
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                INNER JOIN ' . BD_CTRL_2025 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2025 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL_2025 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                INNER JOIN ' . BD_CTRL_2025 . '.cat_EstadoClc AS e ON (ca.ecl_id = e.ecl_id)
            ';
            $andSql = ' AND ca.ejr_id_clc = 2025 ';
            break;
        case 2026:
            $sql .= '
                SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                    CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                    c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                    c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, MAX(cbs.cbs_id) AS origen,
                    MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta, l.lot_ejercicio,
                    l.lot_clave AS lote,
                    MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, "" AS lote_egreso,
                    cx.clc_cereCo, "" AS contable,
                    CASE WHEN
                        l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                    THEN
                        "FEDERAL"
                    ELSE
                        "ESTATAL"
                    END AS modo,
                    tlo.tlo_nombre AS tipo
                FROM ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                INNER JOIN ' . BD_CTRL . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                INNER JOIN ' . BD_CTRL . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                INNER JOIN ' . BD_CTRL . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)
                ';
            break;
    }

    $sqlGroupBy = 'GROUP BY c.gpo_id, c.ur_id, c.clc_id';
    $params = [$fpa_id, $msc_id, $lot_ejercicio, $lot_id];
    $sql .= '
        WHERE lc.fpa_id = ?
        AND lc.msc_id = ?
        AND lc.lot_ejercicio = ?
        AND lc.lot_id = ?
        ' . $andSql . '
        ' . $sqlGroupBy . '
        ORDER BY importe ASC
    ';

    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($params);
    $conexion->desconectar();

    $contador = 1;
    $datos = array();
    while ($row = $conexion->extraer()) {
        $dato = array();

        $dato['contador'] = $contador;
        $dato['lot_fechaGeneracion'] = $row['lot_fechaGeneracion'];
        $dato['lot_fechaPago'] = $row['lot_fechaPago'];
        $dato['clc_generacionCereCo'] = $row['clc_generacionCereCo'];
        $dato['ur'] = $row['ur'];
        $dato['clc'] = $row['clc'];
        $dato['beneficiario'] = $row['beneficiario'];
        $dato['concepto'] = $row['concepto'];
        $dato['linea'] = $row['linea'];
        $dato['importe'] = $row['importe'];
        $dato['origen'] = $row['origen'];
        $dato['banco'] = $row['banco'];
        $dato['nombre_cta'] = $row['nombre_cta'];
        $dato['lot_ejercicio'] = $row['lot_ejercicio'];
        $dato['lote'] = $row['lote'];
        $dato['estado'] = $row['estado'];
        $dato['destino'] = $row['destino'];
        $dato['ecf_clave'] = $row['ecf_clave'];
        $dato['lote_egreso'] = $row['lote_egreso'];
        $dato['clc_cereCo'] = $row['clc_cereCo'];
        $dato['contable'] = $row['contable'];
        $dato['modo'] = $row['modo'];
        $dato['tipo'] = $row['tipo'];

        array_push($datos, $dato);

        $contador++;
    }
    if (count($datos) == 0) {
        $dato = array();

        $dato['contador'] = '';
        $dato['lot_fechaGeneracion'] = '';
        $dato['lot_fechaPago'] = '';
        $dato['clc_generacionCereCo'] = '';
        $dato['ur'] = '';
        $dato['clc'] = '';
        $dato['beneficiario'] = '';
        $dato['concepto'] = '';
        $dato['linea'] = '';
        $dato['importe'] = '';
        $dato['origen'] = '';
        $dato['banco'] = '';
        $dato['nombre_cta'] = '';
        $dato['lot_ejercicio'] = '';
        $dato['lote'] = '';
        $dato['estado'] = '';
        $dato['destino'] = '';
        $dato['ecf_clave'] = '';
        $dato['lote_egreso'] = '';
        $dato['clc_cereCo'] = '';
        $dato['contable'] = '';
        $dato['modo'] = '';
        $dato['tipo'] = '';

        array_push($datos, $dato);
    }

    return $datos;
}
