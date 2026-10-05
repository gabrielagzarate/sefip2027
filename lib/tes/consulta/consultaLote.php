<?php
/**
 * Description of function nivelFolio
 *
 * @author ?
 * Collaborators: Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 12-Dic-2019
 * Comment: -----
 * Objective: obtener la consulta  para lotes
 * Regla de Negocio: Obtener los lotes de acuerdo al filtro de la pantalla  a partir del ejercicio 2017 segun sea el caso
 *
 **/
function consultaLote($lot_ejercicio = '', $fpa_id = '', $lot_tipoCereCo = '', $tlo_id = '', $fechaMin = '', $fechaMax = '', $lote = '', $pro_id = '')
{
    $i = (int) $lot_ejercicio;
    $ejercicios = [APP_EJERCICIO, 2025, 2024, 2023, 2022, 2021, 2020, 2019, 2018, 2017];
    if (array_key_exists($i, $ejercicios)) {
        $lot_ejercicio = $ejercicios[$i];
    }

    $sql = '
                SELECT
                    CASE WHEN l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7 THEN
                        "FEDERAL"
                    ELSE
                        "ESTATAL"
                    END AS modo,
                    tlo.tlo_nombre AS tipo,
                    CASE WHEN l.tlo_id = 4 OR l.tlo_id = 5 OR l.tlo_id = 6 OR l.tlo_id = 7 OR l.elo_id = 9 THEN
                        2
                    ELSE
                        1
                    END AS propuesta,
                    CASE WHEN l.tlo_id = 4 OR l.tlo_id = 5 OR l.tlo_id = 6 OR l.tlo_id = 7 OR l.elo_id = 9 THEN
                        ""
                    ELSE
                        min(pl.pro_id)
                    END AS propuesta_id,
                    CASE WHEN l.tlo_id = 6 OR l.tlo_id = 7 THEN
                        (CASE WHEN l.elo_id <> 9 THEN
                            (SELECT COUNT(*)
                            FROM ' . BD_TESORERIA . '.lot_PropuestaManual
                            WHERE fpa_id = l.fpa_id
                            AND msc_id = l.msc_id
                            AND lot_id = l.lot_id
                            AND lot_ejercicio = l.lot_ejercicio)
                        ELSE
                            0
                        END
                        )
                    ELSE
                        (SELECT COUNT(*)
                        FROM ' . BD_TESORERIA . '.lot_LoteClc
                        WHERE fpa_id = l.fpa_id
                        AND msc_id = l.msc_id
                        AND lot_ejercicio = l.lot_ejercicio
                        AND lot_id = l.lot_id)
                    END AS elementos,
                    l.fpa_id, l.msc_id, l.lot_ejercicio, l.lot_id, l.tlo_id,
                    l.lot_fechaGeneracion, l.lot_fechaPago, elo_nombre, l.elo_id,
                    CONCAT( IF ( l.fpa_id = 1,  "T", IF (l.fpa_id = 2, "C", IF (l.fpa_id = 4, "I", "L")) ),
                        LPAD(l.msc_id, 2, "0"),
                        LPAD(l.lot_id, 4, "0"),
                        SUBSTR(l.lot_ejercicio, 3)
                    ) AS lote,
                    CASE WHEN l.lot_importe = 0 AND l.elo_id <> 9 THEN
                        (CASE WHEN l.tlo_id = 6 OR l.tlo_id = 7 THEN
                            (SELECT SUM(lom_importe)
                            FROM ' . BD_TESORERIA . '.lot_PropuestaManual
                            WHERE fpa_id = l.fpa_id AND msc_id = l.msc_id
                            AND lot_ejercicio = l.lot_ejercicio AND lot_id = l.lot_id)
                        ELSE
                            (SELECT SUM(lot_importeClc)
                            FROM ' . BD_TESORERIA . '.lot_LoteClc
                            WHERE fpa_id = l.fpa_id AND msc_id = l.msc_id
                            AND lot_ejercicio = l.lot_ejercicio AND lot_id = l.lot_id)
                        END)
                    WHEN l.lot_importe <> 0 AND l.elo_id = 9 THEN
                        0.00
                    ELSE
                        l.lot_importe
                    END AS lot_importe
                FROM
                    ' . BD_TESORERIA . '.lot_Lote AS l
                INNER JOIN ' . BD_TESORERIA . '.cat_EstadoLote AS elo USING (elo_id)
                INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
                LEFT JOIN ' . BD_TESORERIA . '.vin_PropuestaLote AS pl USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                WHERE l.lot_ejr = ' . APP_EJERCICIO . '
            ';

    $sqlExtra = '';

    if (!empty($lot_ejercicio)) {
        $sqlExtra .= ' AND l.lot_ejercicio = ' . $lot_ejercicio;
    }

    if (!empty($fpa_id)) {
        $sqlExtra .= ' AND l.fpa_id = ' . $fpa_id;
    }

    if (!empty($lot_tipoCereCo)) {
        $sqlExtra .= ' AND l.lot_tipoCereCo = ' . $lot_tipoCereCo;
    }

    if (!empty($tlo_id)) {
        $sqlExtra .= ' AND l.tlo_id = ' . $tlo_id;
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
                SUBSTR(l.lot_ejercicio, 3) )) IN (' . $lotes . ')
            ';
    }

    if (!empty($pro_id)) {
        $sqlExtra .= ' AND pl.pro_id IN (' . $pro_id . ')';
    }

    $sqlExtra .= ' GROUP BY l.fpa_id, l.msc_id, l.lot_ejercicio, l.lot_id
                    ORDER BY l.fpa_id, l.msc_id, l.lot_ejercicio, l.lot_id';
    return $sql . $sqlExtra;
}
