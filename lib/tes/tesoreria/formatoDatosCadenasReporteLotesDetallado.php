<?php

/**
 * Description of function formatoDatosCadenasReporteLotesDetallado
 *
 * @author ?
 * Collaborators: Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Created: 06-Ene-2019
 * Update: 10-Nov-2022
 * Comment: -----
 * Objective: Ordena los datos consultados de los lotes de tipo cadenas para elaborar el reporte detallado.
 * Regla de Negocio: -----
 * 
 * */
require_once dirname(__FILE__) . '/consultaCadenas.php';

function formatoDatosCadenasReporteLotesDetallado($lot_ejercicio, $fpa_id, $lot_tipoCereCo, $tlo_id, $fechaMin, $fechaMax, $lote, $pro_id)
{
    $cdatos = consultaCadenas(2, $lot_ejercicio, $fpa_id, $lot_tipoCereCo, $tlo_id, $fechaMin, $fechaMax, $lote, $pro_id);

    $contador = 1;
    $datos = array();
    foreach ($cdatos as $key => $value) {
        //Verificar que por lo menos trae un registro
        if ($cdatos[$key]['contador'] != '') {
            $sql2 = '';
            $andSql = '';
            $sqlExtra2 = '';
            $sqlGroupBy = '';

            switch ($lot_ejercicio) {
                case 2017:
                    $sql2 .= '
                            SELECT cx.clc_generacionCereCo,
                                CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                                c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                                c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, cbs.bsf_id AS origen,
                                iba.iba_nombre as banco, cbs.bsf_nombre AS nombre_cta,
                                e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave, le.loe_clave AS lote_egreso,
                                cx.clc_cereCo, "" AS contable
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
                            LEFT JOIN ' . BD_CTRL_2017. '.scp_LoteEgresos AS le USING(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                            ';
                    $andSql = ' AND ca.ejr_id_clc = 2017 ';
                    break;
                case 2018:
                    $sql2 .= '
                        SELECT cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, cbs.cbs_id AS origen,
                            iba.iba_nombre as banco, cbs.cbs_nombre AS nombre_cta,
                            MAX(e.ecl_nombre) AS estado, c.bur_id AS destino, ecf.ecf_clave, le.loe_clave AS lote_egreso,
                            cx.clc_cereCo, "" AS contable
                        FROM ' . BD_TESORERIA . '.lot_Lote AS l
                        INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
                        INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                        INNER JOIN ' . BD_CTRL_2018 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ca USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2018 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL_2018 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                        INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                        INNER JOIN ' . BD_CTRL_2018 . '.cat_EstadoClc AS e ON (ca.ecl_id = e.ecl_id)
                        LEFT JOIN ' . BD_CTRL_2018 . '.scp_DetalleLoteEgresos AS de USING(gpo_id,ur_id,clc_id)
                        LEFT JOIN ' . BD_CTRL_2018 . '.scp_LoteEgresos AS le USING(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                    ';
                    $andSql = ' AND ca.ejr_id_clc = 2018 ';
                    break;
                case 2019:
                    $sql2 .= '
                        SELECT cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, cbs.cbs_id AS origen,
                            iba.iba_nombre as banco, cbs.cbs_nombre AS nombre_cta,
                            e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,
                            "" AS lote_egreso,
                            cx.clc_cereCo, "" AS contable
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
                        INNER JOIN ' . BD_CTRL_2019 . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)
                    ';
                    $andSql = ' AND ca.ejr_id_clc = 2019 ';
                    break;
                case 2020:
                    $sql2 .= '
                        SELECT cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, cbs.cbs_id AS origen,
                            iba.iba_nombre as banco, cbs.cbs_nombre AS nombre_cta,
                            e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,
                            "" AS lote_egreso,
                            cx.clc_cereCo, "" AS contable
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
                        INNER JOIN ' . BD_CTRL_2020 . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)
                    ';
                    $andSql = ' AND ca.ejr_id_clc = 2020 ';
                    break;
                case 2021:
                    $sql2 .= '
                        SELECT cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, cbs.cbs_id AS origen,
                            iba.iba_nombre as banco, cbs.cbs_nombre AS nombre_cta,
                            e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,
                            "" AS lote_egreso,
                            cx.clc_cereCo, "" AS contable
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
                        INNER JOIN ' . BD_CTRL_2021 . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)
                    ';
                    $andSql = ' AND ca.ejr_id_clc = 2021 ';
                    break;
                case 2022:
                    $sql2 .= '
                        SELECT cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, cbs.cbs_id AS origen,
                            iba.iba_nombre as banco, cbs.cbs_nombre AS nombre_cta,
                            e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,
                            "" AS lote_egreso,
                            cx.clc_cereCo, "" AS contable
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
                        INNER JOIN ' . BD_CTRL_2022 . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)
                    ';
                    $andSql = ' AND ca.ejr_id_clc = 2022 ';
                    break;
                case 2023:
                    $sql2 .= '
                        SELECT cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, cbs.cbs_id AS origen,
                            iba.iba_nombre as banco, cbs.cbs_nombre AS nombre_cta,
                            e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,
                            "" AS lote_egreso,
                            cx.clc_cereCo, "" AS contable
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
                        INNER JOIN ' . BD_CTRL_2023 . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)
                    ';
                    $andSql = ' AND ca.ejr_id_clc = 2023 ';
                    break;
                case 2024:
                    $sql2 .= '
                        SELECT cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, cbs.cbs_id AS origen,
                            iba.iba_nombre as banco, cbs.cbs_nombre AS nombre_cta,
                            e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,
                            "" AS lote_egreso,
                            cx.clc_cereCo, "" AS contable
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
                        INNER JOIN ' . BD_CTRL_2024 . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)
                    ';
                    $andSql = ' AND ca.ejr_id_clc = 2024 ';
                    break;
                case 2025:
                    $sql2 .= '
                        SELECT cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, cbs.cbs_id AS origen,
                            iba.iba_nombre as banco, cbs.cbs_nombre AS nombre_cta,
                            e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,
                            "" AS lote_egreso,
                            cx.clc_cereCo, "" AS contable
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
                        INNER JOIN ' . BD_CTRL_2025 . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)
                    ';
                    $andSql = ' AND ca.ejr_id_clc = 2025 ';
                    break;
                case 2026:
                    $sql2 .= '
                        SELECT cx.clc_generacionCereCo,
                            CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                            c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                            c.clc_lineaCaptura AS linea, lc.lot_importeClc AS importe, cbs.cbs_id AS origen,
                            iba.iba_nombre as banco, cbs.cbs_nombre AS nombre_cta,
                            e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,
                            "" AS lote_egreso,
                            cx.clc_cereCo, "" AS contable
                        FROM ' . BD_TESORERIA . '.lot_Lote AS l
                        INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
                        INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                        INNER JOIN ' . BD_CTRL . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                        INNER JOIN ' . BD_CTRL . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                        INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                        INNER JOIN ' . BD_CTRL . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)';
                    break;
            }
            $sqlGroupBy = 'GROUP BY c.gpo_id, c.ur_id, c.clc_id, cbs.cbs_id';

            if (!empty($sql2)) {
                $sqlExtra2 .= (strlen($sqlExtra2) == 0) ? ' WHERE ' : ' AND ';
                $sqlExtra2 .= 'l.fpa_id = ' . $cdatos[$key]['fpa_id'] . '
                    AND l.msc_id = ' . $cdatos[$key]['msc_id'] . '
                    AND l.lot_ejercicio = ' . $cdatos[$key]['lot_ejercicio'] . '
                    AND l.lot_id = ' . $cdatos[$key]['lot_id'] . '
                    AND l.lot_ejr= ' . APP_EJERCICIO . '
                    ' . $andSql . '
                    ' . $sqlGroupBy . '
                    ORDER BY importe ASC';

                $con2 = new AccesoDatos();
                $con2->conectar();
                $con2->setSql($sql2 . $sqlExtra2);
                $con2->consultar();
                $con2->desconectar();

                while ($fila2 = $con2->extraer()) {
                    $dato = array();

                    $dato['contador'] = $contador;
                    $dato['modo'] = $cdatos[$key]['modo'];
                    $dato['tipo'] = $cdatos[$key]['tipo'];
                    $dato['lot_ejercicio'] = $cdatos[$key]['lot_ejercicio'];
                    $dato['lot_fechaGeneracion'] = $cdatos[$key]['lot_fechaGeneracion'];
                    $dato['lot_fechaPago'] = $cdatos[$key]['lot_fechaPago'];
                    $dato['clc_generacionCereCo'] = $fila2['clc_generacionCereCo'];
                    $dato['lote_egreso'] = $fila2['lote_egreso'];
                    $dato['ur'] = $fila2['ur'];
                    $dato['clc'] = $fila2['clc'];
                    $dato['clc_cereCo'] = $fila2['clc_cereCo'];
                    $dato['beneficiario'] = $fila2['beneficiario'];
                    $dato['concepto'] = $fila2['concepto'];
                    $dato['importe'] = $fila2['importe'];
                    $dato['banco'] = $fila2['banco'];
                    $dato['origen'] = $fila2['origen'];
                    $dato['nombre_cta'] = $fila2['nombre_cta'];

                    if ($tlo_id == 5 && $fpa_id == 3) { //Lote federal de linea de captura
                        $dato['linea'] = $fila2['linea'];
                    } else {
                        $dato['destino'] = $fila2['destino'];
                    }
                    $contenido .= '
                            <td align="center">' . $fila2['ecf_clave'] . '</td>
                            <td align="center">' . $fila['lote'] . '</td>
                            <td align="center">' . utf8Decode($fila2['estado']) . '</td>
                            <td align="center">' . $fila2['contable'] . '</td>
                        </tr>';
                    $dato['ecf_clave'] = $fila2['destino'];
                    $dato['lote'] = $cdatos[$key]['lote'];
                    $dato['estado'] = $fila2['estado'];
                    $dato['contable'] = $fila2['destino'];

                    array_push($datos, $dato);

                    $contador++;
                }
            }
        }
    }

    return $datos;
}
?>