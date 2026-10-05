<?php
/**
 * Description of function nivelFolio
 *
 * @author ?
 * Collaborators: Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 12-Dic-2019
 * Comment: -----
 * Objective: Mostrar en un Excel datos detallados de lotes
 * Regla de Negocio: Obtener el detalle del lote de acuerdo al filtro de la pantalla  a partir del ejercicio 2017 segun sea el caso
 *
 **/
require_once dirname(__FILE__) . '/../../../lib/helpers.php';
function consultaLoteDetallado($lot_ejercicio = '', $fpa_id = '', $lot_tipoCereCo = '', $tlo_id = '', $fechaMin = '', $fechaMax = '', $lote = '', $pro_id = '')
{
    $contador = 1;

    $sql = '
            SELECT
                l.fpa_id, l.msc_id, l.lot_ejercicio, l.lot_id,
                l.lot_fechaGeneracion, l.lot_fechaPago,
                CASE WHEN
                    l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
                THEN
                    "FEDERAL"
                ELSE
                    "ESTATAL"
                END AS modo,
                tlo.tlo_nombre AS tipo,
                l.tlo_id, l.lot_importe, elo_nombre,
                CONCAT( IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", IF (l.fpa_id = 4, "I", "L")) ),
                    LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"),
                    SUBSTR(l.lot_ejercicio, 3)
                ) AS lote
            FROM ' . BD_TESORERIA . '.lot_Lote AS l
            INNER JOIN ' . BD_TESORERIA . '.cat_EstadoLote AS elo USING (elo_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
            LEFT JOIN ' . BD_TESORERIA . '.vin_PropuestaLote AS pl USING (fpa_id, msc_id, lot_ejercicio, lot_id)
            WHERE l.lot_ejr = ' . APP_EJERCICIO . '
            ';

    $sqlExtra = '';

    $i = (int) $lot_ejercicio;
    $ejercicios = [APP_EJERCICIO, 2025, 2024, 2023, 2022, 2021, 2020, 2019, 2018, 2017];
    if (array_key_exists($i, $ejercicios)) {
        $lot_ejercicio = $ejercicios[$i];
        $sqlExtra .= ' AND l.lot_ejercicio = ' . $lot_ejercicio;
    }

    if (!empty($fpa_id)) {
        $sqlExtra .= ' AND l.fpa_id = ' . $fpa_id . '';
    }

    if (!empty($lot_tipoCereCo)) {
        $sqlExtra .= ' AND l.lot_tipoCereCo = ' . $lot_tipoCereCo . '';
    }

    if (!empty($tlo_id)) {
        $sqlExtra .= ' AND l.tlo_id = ' . $tlo_id . '';
    }

    if (!empty($fechaMin)) {
        $fecha = DateTime::createFromFormat('d/m/Y', $fechaMin);
        $sqlExtra .= ' AND l.lot_fechaGeneracion >= "' . $fecha->format('Y-m-d') . '"';
    }

    if (!empty($fechaMax)) {
        $fecha = DateTime::createFromFormat('d/m/Y', $fechaMax);
        $sqlExtra .= ' AND l.lot_fechaGeneracion <= "' . $fecha->format('Y-m-d') . '"';
    }

    if (!empty($lote)) {
        $lotesArr = array();
        foreach (explode(',', $lote) as $lot) {
            array_push($lotesArr, '"' . trim($lot) . '"');
        }
        $lotes = implode(',', $lotesArr);

        $sqlExtra .= ' AND (CONCAT( IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", "L") ),
            LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"),
            SUBSTR(l.lot_ejercicio, 3) )) IN (' . $lotes . ')';
    }

    if (!empty($pro_id)) {
        $sqlExtra .= ' AND pl.pro_id IN (' . $pro_id . ')';
    }

    $sqlExtra .= ' AND l.elo_id <> 9';

    $sqlExtra .= ' GROUP BY l.fpa_id, l.msc_id, l.lot_ejercicio, l.lot_id
                    ORDER BY l.fpa_id, l.msc_id, l.lot_ejercicio, l.lot_id';

    $con = new AccesoDatos();
    $con->conectar();
    $con->setSql($sql . $sqlExtra);
    $con->consultar();
    $con->desconectar();
    $result = array();

    while ($fila = $con->extraer()) {
        $sql2 = '';
        $sqlExtra2 = '';
        $sqlGroupBy = '';

        switch ($fila['tlo_id']) {
            case 6:
            case 7:
                $sql2 = '
                        SELECT l.lot_fechaGeneracion, l.lot_fechaPago, "" AS clc_generacionCereCo,
                            CONCAT(lc.gpo_id, LPAD(lc.ur_id,2,"0")) AS ur, lc.clc_id AS clc,
                            lc.lom_beneficiario AS beneficiario, lc.lom_concepto AS concepto, lc.lom_importe AS importe,
                            CONCAT(lc.cbs_id," ") AS origen, iba.iba_abreviatura AS banco, e.elo_nombre AS estado,
                            CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", "L")),
                                LPAD(l.msc_id, 2, "0"),
                                LPAD(l.lot_id, 4, "0"),
                                SUBSTR(l.lot_ejercicio, 3)
                            ) AS lote,
                            CONCAT(lc.cbb_id," ") AS destino, ecf.ecf_clave, "" AS lote_egreso, pc.cta_cuenta AS contable,
                            "" AS clc_cereCo, cbs.cbs_nombre AS nombre_cta
                        FROM ' . BD_TESORERIA . '.lot_Lote AS l
                        INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
                        INNER JOIN ' . BD_TESORERIA . '.lot_PropuestaManual AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                        INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING (cbs_id)
                        INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                        INNER JOIN ' . BD_TESORERIA . '.cat_EstadoLote AS e ON(e.elo_id = l.elo_id)
                        INNER JOIN ' . BD_TESORERIA . '.vin_PropuestaManualClaveFinanciamiento AS vpcf USING (lom_id)
                        INNER JOIN ' . BD_CTRL . '.enc_ClaveFinanciamiento AS ecf USING(ecf_id)
                        INNER JOIN ' . BD_TESORERIA . '.vin_PropuestaManualPlanCuenta AS vlom USING (lom_id)
                        INNER JOIN ' . BD_CTRL . '.cg_PlanCuentas AS pc USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                        ';

                $sqlGroupBy = 'GROUP BY lc.lom_id';
                break;
            default:
                if ($lot_ejercicio == APP_EJERCICIO or $lot_ejercicio == 2025 or $lot_ejercicio == 2024 or $lot_ejercicio == 2023 or $lot_ejercicio == 2022 or $lot_ejercicio == 2021 or $lot_ejercicio == 2020 or $lot_ejercicio == 2019) {
                    $base = "ctrl" . $lot_ejercicio;
                    $sql2 .= '
                            SELECT cx.clc_generacionCereCo,
                                CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                                c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                                c.clc_lineaCaptura AS linea, MAX(lc.lot_importeClc) AS importe, CONCAT(MAX(cbs.cbs_id)," ") AS origen,
                                MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta,
                                e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave,
                                "" lote_egreso, cx.clc_cereCo, "" AS contable
                            FROM ' . BD_TESORERIA . '.lot_Lote AS l
                            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
                            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN ' . $base . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . $base . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . $base . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                            INNER JOIN ' . $base . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)
                            ';
                } elseif ($lot_ejercicio == 2018) {
                    $sql2 .= '
                            SELECT cx.clc_generacionCereCo,
                                CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                                c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                                c.clc_lineaCaptura AS linea, MAX(lc.lot_importeClc) AS importe,CONCAT(MAX(cbs.cbs_id)," ") AS origen,
                                MAX(iba.iba_nombre) as banco, MAX(cbs.cbs_nombre) AS nombre_cta,
                                e.ecl_nombre AS estado, c.bur_id AS destino, ecf.ecf_clave, le.loe_clave AS lote_egreso,
                                cx.clc_cereCo, "" AS contable
                            FROM ' . BD_TESORERIA . '.lot_Lote AS l
                            INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
                            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS lc USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                            INNER JOIN ' . BD_CTRL_2018 . '.scp_Clc AS c USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2018 . '.scp_ClcAuxiliar AS cx USING(gpo_id, ur_id, clc_id)
                            INNER JOIN ' . BD_CTRL_2018 . '.enc_ClaveFinanciamiento AS ecf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento AS vscf USING (ecf_id)
                            INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin AS cbs USING(cbs_id)
                            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria AS iba USING (iba_id)
                            INNER JOIN ' . BD_CTRL_2018 . '.cat_EstadoClc AS e ON (c.ecl_id = e.ecl_id)
                            LEFT JOIN ' . BD_CTRL_2018 . '.scp_DetalleLoteEgresos AS de USING(gpo_id,ur_id,clc_id)
                            LEFT JOIN ' . BD_CTRL_2018 . '.scp_LoteEgresos AS le USING(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)

                            ';

                } elseif ($lot_ejercicio == 2017) {
                    $sql2 .= '
                            SELECT cx.clc_generacionCereCo,
                                CONCAT(c.gpo_id, LPAD(c.ur_id,2,"0")) AS ur, c.clc_id AS clc,
                                c.clc_beneficiario AS beneficiario, c.clc_concepto AS concepto,
                                c.clc_lineaCaptura AS linea, MAX(lc.lot_importeClc) AS importe, cbs.bsf_id AS origen,
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
                            LEFT JOIN ' . BD_CTRL_2017 . '.scp_LoteEgresos AS le USING(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                            ';
                } //fin elseif

                $sqlGroupBy = 'GROUP BY c.gpo_id, c.ur_id, c.clc_id';
                break;
        } //fin switch
        if (!empty($sql2)) {
            $sqlExtra2 .= (strlen($sqlExtra2) == 0) ? 'WHERE ' : ' AND ';
            $sqlExtra2 .= 'l.lot_ejr = ' . APP_EJERCICIO . ' and l.fpa_id = ' . $fila['fpa_id'] . '
                    AND l.msc_id = ' . $fila['msc_id'] . '
                    AND l.lot_ejercicio = ' . $fila['lot_ejercicio'] . '
                    AND l.lot_id = ' . $fila['lot_id'] . '
                    ' . $sqlGroupBy . '
                    ORDER BY importe ASC';

            $con2 = new AccesoDatos();
            $con2->conectar();
            $con2->setSql($sql2 . $sqlExtra2);
            $con2->consultar();

            $con2->desconectar();

            $linea_dest = '';
            $result2 = array();

            while ($fila2 = $con2->extraer()) {
                if ($fila['tlo_id'] == 5 && $fila['fpa_id'] == 3) { //Lote federal de linea de captura
                    $linea_dest = utf8Decode($fila2['linea']);
                } else {
                    $linea_dest = utf8Decode($fila2['destino']);
                }

                $dato = array(
                    'contador' => $contador++,
                    'modo' => utf8Decode($fila['modo']),
                    'tipo' => utf8Decode($fila['tipo']),
                    'lot_ejercicio' => $fila['lot_ejercicio'],
                    'lot_fechaGeneracion' => $fila['lot_fechaGeneracion'],
                    'lot_fechaPago' => $fila['lot_fechaPago'],
                    'clc_generacionCereCo' => $fila2['clc_generacionCereCo'],
                    'lote_egreso' => $fila2['lote_egreso'],
                    'ur' => $fila2['ur'],
                    'clc' => $fila2['clc'],
                    'clc_cereCo' => $fila2['clc_cereCo'],
                    'beneficiario' => utf8Encode(($fila2['beneficiario'])),
                    'concepto' => ($fila2['concepto']),
                    'importe' => $fila2['importe'],
                    'banco' => ($fila2['banco']),
                    'origen' => $fila2['origen'],
                    'nombre_cta' => ($fila2['nombre_cta']),
                    'linea' => $linea_dest,
                    'destino' => $linea_dest,
                    'ecf_clave' => $fila2['ecf_clave'],
                    'lote' => $fila['lote'],
                    'estado' => ($fila2['estado']),
                    'contable' => $fila2['contable'],

                );

                array_push($result, $dato);
            } //fin while
        }
    } // fin while
    return $result;

}
