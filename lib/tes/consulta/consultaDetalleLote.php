<?php
function consultaDetalleLote($lot_ejercicio = '', $fpa_id = '', $msc_id = '', $tlo_id = '', $lot_id = '')
{
    $contador = 1;

        switch ($tlo_id) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 8:
                $sql = '';
                if($lot_ejercicio == APP_EJERCICIO){
                    $sql .= '
                        SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, CONCAT(MAX(cbs.cbs_id)," ") AS origen,
                            MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta, l.lot_ejercicio,
                            CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", "L")),
                                LPAD(l.msc_id, 2, "0"),
                                LPAD(l.lot_id, 4, "0"),
                                SUBSTR(l.lot_ejercicio, 3)
                            ) AS lote,
                            e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,
                            -- le.loe_clave AS lote_egreso,
                            "" AS lote_egreso,
                            cx.clc_cereCo, "" AS contable,
                            CASE WHEN
                                l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                            THEN
                                "FEDERAL"
                            ELSE
                                "ESTATAL"
                            END AS modo,
                            tlo.tlo_nombre AS tipo
                        FROM '.BD_TESORERIA.'.lot_Lote AS l
                        INNER JOIN '.BD_TESORERIA.'.cat_TipoLote AS tlo USING (tlo_id)
                        INNER JOIN '.BD_TESORERIA.'.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                        INNER JOIN '.BD_CTRL.'.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                        INNER JOIN '.BD_CTRL.'.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                        INNER JOIN '.BD_CTRL.'.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                        INNER JOIN '.BD_TESORERIA.'.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                        INNER JOIN '.BD_TESORERIA.'.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                        INNER JOIN '.BD_TESORERIA.'.cat_InstitucionBancaria AS iba USING (iba_id)
                        INNER JOIN '.BD_CTRL.'.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)';
                } else if ($lot_ejercicio == 2025) {
                    $sql .= '
                        SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, CONCAT(MAX(cbs.cbs_id)," ") AS origen,
                            MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta, l.lot_ejercicio,
                            CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", "L")),
                                LPAD(l.msc_id, 2, "0"),
                                LPAD(l.lot_id, 4, "0"),
                                SUBSTR(l.lot_ejercicio, 3)
                            ) AS lote,
                            e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave, 
                            -- le.loe_clave AS lote_egreso,
                            "" AS lote_egreso,
                            cx.clc_cereCo, "" AS contable,
                            CASE WHEN
                                l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                            THEN
                                "FEDERAL"
                            ELSE
                                "ESTATAL"
                            END AS modo, 
                            tlo.tlo_nombre AS tipo
                        FROM '.BD_TESORERIA.'.lot_Lote AS l
                        INNER JOIN '.BD_TESORERIA.'.cat_TipoLote AS tlo USING (tlo_id)
                        INNER JOIN '.BD_TESORERIA.'.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                        INNER JOIN '.BD_CTRL_2025.'.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                        INNER JOIN '.BD_CTRL_2025.'.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                        INNER JOIN '.BD_CTRL_2025.'.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                        INNER JOIN '.BD_TESORERIA.'.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                        INNER JOIN '.BD_TESORERIA.'.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                        INNER JOIN '.BD_TESORERIA.'.cat_InstitucionBancaria AS iba USING (iba_id)
                        INNER JOIN '.BD_CTRL_2025.'.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)';
                } else if ($lot_ejercicio == 2024) {
                    $sql .= '
                        SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, CONCAT(MAX(cbs.cbs_id)," ") AS origen,
                            MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta, l.lot_ejercicio,
                            CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", "L")),
                                LPAD(l.msc_id, 2, "0"),
                                LPAD(l.lot_id, 4, "0"),
                                SUBSTR(l.lot_ejercicio, 3)
                            ) AS lote,
                            e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave, 
                            -- le.loe_clave AS lote_egreso,
                            "" AS lote_egreso,
                            cx.clc_cereCo, "" AS contable,
                            CASE WHEN
                                l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                            THEN
                                "FEDERAL"
                            ELSE
                                "ESTATAL"
                            END AS modo, 
                            tlo.tlo_nombre AS tipo
                        FROM '.BD_TESORERIA.'.lot_Lote AS l
                        INNER JOIN '.BD_TESORERIA.'.cat_TipoLote AS tlo USING (tlo_id)
                        INNER JOIN '.BD_TESORERIA.'.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                        INNER JOIN '.BD_CTRL_2024.'.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                        INNER JOIN '.BD_CTRL_2024.'.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                        INNER JOIN '.BD_CTRL_2024.'.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                        INNER JOIN '.BD_TESORERIA.'.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                        INNER JOIN '.BD_TESORERIA.'.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                        INNER JOIN '.BD_TESORERIA.'.cat_InstitucionBancaria AS iba USING (iba_id)
                        INNER JOIN '.BD_CTRL_2024.'.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)';
                }else if($lot_ejercicio == 2023) {
                    $sql .= '
                        SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, CONCAT(MAX(cbs.cbs_id)," ") AS origen,
                            MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta, l.lot_ejercicio,
                            CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", "L")),
                                LPAD(l.msc_id, 2, "0"),
                                LPAD(l.lot_id, 4, "0"),
                                SUBSTR(l.lot_ejercicio, 3)
                            ) AS lote,
                            e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,
                            -- le.loe_clave AS lote_egreso,
                            "" AS lote_egreso,
                            cx.clc_cereCo, "" AS contable,
                            CASE WHEN
                                l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                            THEN
                                "FEDERAL"
                            ELSE
                                "ESTATAL"
                            END AS modo,
                            tlo.tlo_nombre AS tipo
                        FROM '.BD_TESORERIA.'.lot_Lote AS l
                        INNER JOIN '.BD_TESORERIA.'.cat_TipoLote AS tlo USING (tlo_id)
                        INNER JOIN '.BD_TESORERIA.'.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                        INNER JOIN '.BD_CTRL_2023.'.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                        INNER JOIN '.BD_CTRL_2023.'.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                        INNER JOIN '.BD_CTRL_2023.'.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                        INNER JOIN '.BD_TESORERIA.'.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                        INNER JOIN '.BD_TESORERIA.'.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                        INNER JOIN '.BD_TESORERIA.'.cat_InstitucionBancaria AS iba USING (iba_id)
                        INNER JOIN '.BD_CTRL_2023.'.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)';
                }else if ($lot_ejercicio == 2022) {
                    $sql .= '
                    SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                        CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                        c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                        c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, CONCAT(MAX(cbs.cbs_id)," ") AS origen,
                        MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta, l.lot_ejercicio,
                        CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", "L")),
                            LPAD(l.msc_id, 2, "0"),
                            LPAD(l.lot_id, 4, "0"),
                            SUBSTR(l.lot_ejercicio, 3)
                        ) AS lote,
                        e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,
                        -- le.loe_clave AS lote_egreso,
                        "" AS lote_egreso,
                        cx.clc_cereCo, "" AS contable,
                        CASE WHEN
                            l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                        THEN
                            "FEDERAL"
                        ELSE
                            "ESTATAL"
                        END AS modo,
                        tlo.tlo_nombre AS tipo
                    FROM '.BD_TESORERIA.'.lot_Lote AS l
                    INNER JOIN '.BD_TESORERIA.'.cat_TipoLote AS tlo USING (tlo_id)
                    INNER JOIN '.BD_TESORERIA.'.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                    INNER JOIN '.BD_CTRL_2022.'.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                    INNER JOIN '.BD_CTRL_2022.'.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                    INNER JOIN '.BD_CTRL_2022.'.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                    INNER JOIN '.BD_TESORERIA.'.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                    INNER JOIN '.BD_TESORERIA.'.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                    INNER JOIN '.BD_TESORERIA.'.cat_InstitucionBancaria AS iba USING (iba_id)
                    INNER JOIN '.BD_CTRL_2022.'.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)';
                } elseif($lot_ejercicio == 2021) {
                    $sql .= '
                    SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                        CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                        c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                        c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, CONCAT(MAX(cbs.cbs_id)," ") AS origen,
                        MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta, l.lot_ejercicio,
                        CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", "L")),
                            LPAD(l.msc_id, 2, "0"),
                            LPAD(l.lot_id, 4, "0"),
                            SUBSTR(l.lot_ejercicio, 3)
                        ) AS lote,
                        e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,
                        -- le.loe_clave AS lote_egreso,
                        "" AS lote_egreso,
                        cx.clc_cereCo, "" AS contable,
                        CASE WHEN
                            l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                        THEN
                            "FEDERAL"
                        ELSE
                            "ESTATAL"
                        END AS modo,
                        tlo.tlo_nombre AS tipo
                    FROM '.BD_TESORERIA.'.lot_Lote AS l
                    INNER JOIN '.BD_TESORERIA.'.cat_TipoLote AS tlo USING (tlo_id)
                    INNER JOIN '.BD_TESORERIA.'.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                    INNER JOIN '.BD_CTRL_2021.'.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                    INNER JOIN '.BD_CTRL_2021.'.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                    INNER JOIN '.BD_CTRL_2021.'.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                    INNER JOIN '.BD_TESORERIA.'.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                    INNER JOIN '.BD_TESORERIA.'.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                    INNER JOIN '.BD_TESORERIA.'.cat_InstitucionBancaria AS iba USING (iba_id)
                    INNER JOIN '.BD_CTRL_2021.'.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)';
                } elseif ($lot_ejercicio == 2020) {
                    $sql .= '
                    SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                        CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                        c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                        c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, CONCAT(MAX(cbs.cbs_id)," ") AS origen,
                        MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta, l.lot_ejercicio,
                        CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", "L")),
                            LPAD(l.msc_id, 2, "0"),
                            LPAD(l.lot_id, 4, "0"),
                            SUBSTR(l.lot_ejercicio, 3)
                        ) AS lote,
                        e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,
                        -- le.loe_clave AS lote_egreso,
                        "" AS lote_egreso,
                        cx.clc_cereCo, "" AS contable,
                        CASE WHEN
                            l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                        THEN
                            "FEDERAL"
                        ELSE
                            "ESTATAL"
                        END AS modo,
                        tlo.tlo_nombre AS tipo
                    FROM '.BD_TESORERIA.'.lot_Lote AS l
                    INNER JOIN '.BD_TESORERIA.'.cat_TipoLote AS tlo USING (tlo_id)
                    INNER JOIN '.BD_TESORERIA.'.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                    INNER JOIN '.BD_CTRL_2020.'.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                    INNER JOIN '.BD_CTRL_2020.'.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                    INNER JOIN '.BD_CTRL_2020.'.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                    INNER JOIN '.BD_TESORERIA.'.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                    INNER JOIN '.BD_TESORERIA.'.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                    INNER JOIN '.BD_TESORERIA.'.cat_InstitucionBancaria AS iba USING (iba_id)
                    INNER JOIN '.BD_CTRL_2020.'.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)';
                } elseif($lot_ejercicio == 2019) {
                    $sql .= '
                    SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                        CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                        c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                        c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, CONCAT(MAX(cbs.cbs_id)," ") AS origen,
                        MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta, l.lot_ejercicio,
                        CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", "L")),
                            LPAD(l.msc_id, 2, "0"),
                            LPAD(l.lot_id, 4, "0"),
                            SUBSTR(l.lot_ejercicio, 3)
                        ) AS lote,
                        e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave, "" AS lote_egreso,
                        cx.clc_cereCo, "" AS contable,
                        CASE WHEN
                            l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                        THEN
                            "FEDERAL"
                        ELSE
                            "ESTATAL"
                        END AS modo,
                        tlo.tlo_nombre AS tipo
                    FROM '.BD_TESORERIA.'.lot_Lote AS l
                    INNER JOIN '.BD_TESORERIA.'.cat_TipoLote AS tlo USING (tlo_id)
                    INNER JOIN '.BD_TESORERIA.'.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                    INNER JOIN '.BD_CTRL_2019.'.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                    INNER JOIN '.BD_CTRL_2019.'.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                    INNER JOIN '.BD_CTRL_2019.'.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                    INNER JOIN '.BD_TESORERIA.'.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                    INNER JOIN '.BD_TESORERIA.'.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                    INNER JOIN '.BD_TESORERIA.'.cat_InstitucionBancaria AS iba USING (iba_id)
                    INNER JOIN '.BD_CTRL_2019.'.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)
                    #LEFT JOIN '.BD_CTRL_2019.'.scp_DetalleLoteEgresos AS de USING(gpo_id,ur_id,clc_id)
                    #LEFT JOIN '.BD_CTRL_2019.'.scp_LoteEgresos AS le USING(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                    ';
            } elseif ($lot_ejercicio == 2018) {
                $sql .= '
                    SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                        CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                        c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                        c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, CONCAT(MAX(cbs.cbs_id)," ") AS origen,
                        MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta, l.lot_ejercicio,
                        CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", "L")),
                            LPAD(l.msc_id, 2, "0"),
                            LPAD(l.lot_id, 4, "0"),
                            SUBSTR(l.lot_ejercicio, 3)
                        ) AS lote,
                        e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave, le.loe_clave AS lote_egreso,
                        cx.clc_cereCo, "" AS contable,
                        CASE WHEN
                            l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                        THEN
                            "FEDERAL"
                        ELSE
                            "ESTATAL"
                        END AS modo,
                        tlo.tlo_nombre AS tipo
                    FROM '.BD_TESORERIA.'.lot_Lote AS l
                    INNER JOIN '.BD_TESORERIA.'.cat_TipoLote AS tlo USING (tlo_id)
                    INNER JOIN '.BD_TESORERIA.'.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                    INNER JOIN '.BD_CTRL_2018.'.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                    INNER JOIN '.BD_CTRL_2018.'.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                    INNER JOIN '.BD_CTRL_2018.'.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                    INNER JOIN '.BD_TESORERIA.'.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                    INNER JOIN '.BD_TESORERIA.'.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                    INNER JOIN '.BD_TESORERIA.'.cat_InstitucionBancaria AS iba USING (iba_id)
                    INNER JOIN '.BD_CTRL_2018.'.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)
                    LEFT JOIN '.BD_CTRL_2018.'.scp_DetalleLoteEgresos AS de USING(gpo_id,ur_id,clc_id)
                    LEFT JOIN '.BD_CTRL_2018.'.scp_LoteEgresos AS le USING(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)';
                } elseif ($lot_ejercicio == 2017) {
                    $sql .= '
                    SELECT l.lot_fechaGeneracion, l.lot_fechaPago, cx.clc_generacionCereCo,
                        CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                        c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                        c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, cbs.bsf_id AS origen,
                        iba.iba_nombre as banco, l.lot_ejercicio, cbs.bsf_nombre AS nombre_cta,
                        CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", "L")),
                            LPAD(l.msc_id, 2, "0"),
                            LPAD(l.lot_id, 4, "0"),
                            SUBSTR(l.lot_ejercicio, 3)
                        ) AS lote,
                        e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave, le.loe_clave AS lote_egreso,
                        cx.clc_cereCo, "" AS contable,
                        CASE WHEN
                            l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                        THEN
                            "FEDERAL"
                        ELSE
                            "ESTATAL"
                        END AS modo,
                        tlo.tlo_nombre AS tipo
                    FROM '.BD_TESORERIA.'.lot_Lote AS l
                    INNER JOIN '.BD_TESORERIA.'.cat_TipoLote AS tlo USING (tlo_id)
                    INNER JOIN '.BD_TESORERIA.'.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                    INNER JOIN '.BD_CTRL_2017.'.scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                    INNER JOIN '.BD_CTRLANT.'.scp_Clc AS ca USING (gpo_id, ur_id, clc_id)
                    INNER JOIN '.BD_CTRL_2017.'.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                    INNER JOIN '.BD_CTRL_2017.'.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                    INNER JOIN '.BD_CTRL_2017.'.enc_BancosSefinCveFin AS vscf USING (ecf_id)
                    INNER JOIN '.BD_CTRL_2017.'.scp_BancosSefin AS cbs USING (bsf_id)
                    INNER JOIN '.BD_CTRL_2017.'.cat_InstitucionesBancarias AS iba USING (iba_id)
                    INNER JOIN '.BD_CTRL_2017.'.cat_EstadoClc AS e ON (ca.ecl_id = e.ecl_id)
                    LEFT JOIN '.BD_CTRL_2017.'.scp_DetalleLoteEgresos AS de USING(gpo_id,ur_id,clc_id)
                    LEFT JOIN '.BD_CTRL_2017.'.scp_LoteEgresos AS le USING(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)';
                }
                $sqlGroupBy = 'GROUP BY c.gpo_id, c.ur_id, c.clc_id';
            break;
        case 6:
        case 7:
            $sql = '
            SELECT l.lot_fechaGeneracion, l.lot_fechaPago, "" AS clc_generacionCereCo,
                CONCAT(lc.gpo_id, LPAD(lc.ur_id,2,"0")) AS ur, lc.clc_id AS clc,
                lc.lom_beneficiario AS beneficiario, lc.lom_concepto AS concepto, lc.lom_importe AS importe,
                lc.cbs_id AS origen, iba.iba_abreviatura AS banco, e.elo_nombre AS estado,
                CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", "L")),
                    LPAD(l.msc_id, 2, "0"),
                    LPAD(l.lot_id, 4, "0"),
                    SUBSTR(l.lot_ejercicio, 3)
                ) AS lote,
                lc.cbb_id AS destino, ecf.ecf_clave, "" AS lote_egreso, pc.cta_cuenta AS contable,
                "" AS clc_cereCo, cbs.cbs_nombre AS nombre_cta,l.lot_ejercicio,
                CASE WHEN
                    l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                THEN
                    "FEDERAL"
                ELSE
                    "ESTATAL"
                END AS modo,
                tlo.tlo_nombre AS tipo
            FROM '.BD_TESORERIA.'.lot_Lote AS l
            INNER JOIN '.BD_TESORERIA.'.cat_TipoLote AS tlo USING (tlo_id)
            INNER JOIN '.BD_TESORERIA.'.lot_PropuestaManual AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
            INNER JOIN '.BD_TESORERIA.'.ban_CuentaBancariaSefin AS cbs USING (cbs_id)
            INNER JOIN '.BD_TESORERIA.'.cat_InstitucionBancaria AS iba USING (iba_id)
            INNER JOIN '.BD_TESORERIA.'.cat_EstadoLote AS e ON(e.elo_id = l.elo_id)
            INNER JOIN '.BD_TESORERIA.'.vin_PropuestaManualClaveFinanciamiento AS vpcf USING (lom_id)
            INNER JOIN '.BD_CTRL.'.enc_ClaveFinanciamiento AS ecf USING(ecf_id)
            INNER JOIN '.BD_TESORERIA.'.vin_PropuestaManualPlanCuenta AS vlom USING (lom_id)
            INNER JOIN '.BD_CTRL.'.cg_PlanCuentas AS pc USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)';
            $sqlGroupBy = 'GROUP BY lc.lom_id';
            break;
    }
    if (!empty($sql)) {
        $sql .= '
            WHERE l.lot_ejr = :appEjer and lc.fpa_id = :fpa
            AND lc.msc_id = :msc
            AND lc.lot_ejercicio = :lot
            AND lc.lot_id = :lotId
            ' . $sqlGroupBy . '
            ORDER BY importe ASC
        ';
  
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sql);
        $con->consultar(array(':appEjer'=>APP_EJERCICIO,':fpa'=>$fpa_id,':msc'=>$msc_id,':lot'=>$lot_ejercicio,':lotId'=>$lot_id));
        $con->desconectar();

        $linea = "";
        $destino = "";
        $contable = "";
        $result = array();

        while ($fila = $con->extraer()) {
            if ($tlo_id == 5 && $fpa_id == 3) { //Lote federal de linea de captura
                $linea = $fila['linea'];
            } else {
                $destino = $fila['destino'];
            }
            if ($tlo_id == 6 || $tlo_id == 7) {
                $contable = $fila['contable'];
            }

            $dato = array(
                'contador' => $contador++,
                'modo' => $fila['modo'],
                'tipo' => isset($fila['tipo'])?$fila['tipo']:'',
                'lot_ejercicio' => $fila['lot_ejercicio'],
                'lot_fechaGeneracion' => $fila['lot_fechaGeneracion'],
                'lot_fechaPago' => $fila['lot_fechaPago'],
                'clc_generacionCereCo' => $fila['clc_generacionCereCo'],
                'lote_egreso' => $fila['lote_egreso'],
                'ur' => $fila['ur'],
                'clc' => $fila['clc'],
                'clc_cereCo' => $fila['clc_cereCo'],
                'beneficiario' => str_replace("   ", " ", $fila['beneficiario']), //quitar espacios en blanco
                'concepto' => $fila['concepto'],
                'importe' => $fila['importe'],
                'banco' => $fila['banco'],
                'origen' => $fila['origen'],
                'nombre_cta' => $fila['nombre_cta'],
                'linea' => $fila['linea'],
                'destino' => $fila['destino'],
                'ecf_clave' => $fila['ecf_clave'],
                'lote' => $fila['lote'],
                'estado' => $fila['estado'],
                'contable' => $contable,
            );
            array_push($result, $dato);
        }
        return $result;
    }
}
