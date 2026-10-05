<?php

/**
 * Description of function consultaCadenas
 *
 * @author: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Collaborators: Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Created: 06-Ene-2019
 * Update: 09-Dic-2019
 * Comment: -----
 * Objective: Consulta datos de acuerdo a la opcion requerida, cadenas productivas.
 * Regla de Negocio: -----
 *
 * */

function consultaCadenas($ccd_id, $lot_ejercicio, $fpa_id, $lot_tipoCereCo, $tlo_id, $fechaMin, $fechaMax, $lote, $pro_id)
{
    $sqlExtra = '';
    $params = [];

    if (!empty($lot_ejercicio)) {
        $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
        $sqlExtra .= 'l.lot_ejercicio = :ejercicio ';
        $params['ejercicio'] = $lot_ejercicio;
    }

    if (!empty($fpa_id)) {
        $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
        $sqlExtra .= 'l.fpa_id = :fpa ';
        $params['fpa'] = $fpa_id;
    }

    if (!empty($lot_tipoCereCo)) {
        $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
        $sqlExtra .= 'l.lot_tipoCereCo = :tipoCereCo ';
        $params['tipoCereCo'] = $lot_tipoCereCo;
    }

    if (!empty($tlo_id)) {
        $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
        $sqlExtra .= 'l.tlo_id = :tlo ';
        $params['tlo'] = $tlo_id;
    }

    if (!empty($fechaMin)) {
        $fecha = DateTime::createFromFormat('d/m/Y', $fechaMin);
        $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
        $sqlExtra .= 'l.lot_fechaGeneracion >= :fechaMin ';
        $params['fechaMin'] = $fecha->format('Y-m-d');
    }

    if (!empty($fechaMax)) {
        $fecha = DateTime::createFromFormat('d/m/Y', $fechaMax);
        $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
        $sqlExtra .= 'l.lot_fechaGeneracion <= :fechaMax ';
        $params['fechaMax'] = $fecha->format('Y-m-d');
    }

    switch ($ccd_id) {
        case 1:
            if (!empty($lote)) {
                $lotesArr = array();
                foreach (explode(',', $lote) as $lot) {
                    array_push($lotesArr, '"' . trim($lot) . '"');
                }
                $lotes = implode(',', $lotesArr);

                $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
                $sqlExtra .= '(CONCAT( IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", "L") ),
                    LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"),
                    SUBSTR(l.lot_ejercicio, 3) )) IN (' . $lotes . ')
                ';
            }
            break;
        case 2:
            if (!empty($lote)) {
                $lotesArr = array();
                foreach (explode(',', $lote) as $lot) {
                    array_push($lotesArr, '"' . trim($lot) . '"');
                }
                $lotes = implode(',', $lotesArr);

                $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
                $sqlExtra .= '(CONCAT( IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", F (l.fpa_id = 4, "I", "L")) ),
                    LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"),
                    SUBSTR(l.lot_ejercicio, 3) )) IN (' . $lotes . ')
                ';
            }
            break;
    }

    if (!empty($pro_id)) {
        $sqlExtra .= (strlen($sqlExtra) == 0) ? 'WHERE ' : 'AND ';
        $sqlExtra .= 'pl.pro_id IN (:id) ';
        $params['id'] = $this->prm->pro_id;
    }
    $sql = '
        SELECT
            l.lot_fechaGeneracion,
            CASE WHEN
                l.tlo_id = 4 OR l.tlo_id = 5 OR tlo_id = 7
            THEN
                "FEDERAL"
            ELSE
                "ESTATAL"
            END AS modo,
            tlo.tlo_nombre AS tipo,
            l.fpa_id, l.msc_id, l.lot_ejercicio, l.lot_id,
            l.tlo_id, l.lot_fechaPago, elo_nombre,
            CONCAT( IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", IF (l.fpa_id = 4, "I", "L")) ),
                LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"),
                SUBSTR(l.lot_ejercicio, 3) ) AS lote,
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
            END AS lot_importe,
            pl.pro_id
        FROM ' . BD_TESORERIA . '.lot_Lote AS l
        INNER JOIN ' . BD_TESORERIA . '.cat_EstadoLote AS elo USING (elo_id)
        INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tlo USING (tlo_id)
        LEFT JOIN ' . BD_TESORERIA . '.vin_PropuestaLote AS pl USING (fpa_id, msc_id, lot_ejercicio, lot_id)
    ';

    $sqlExtra .= (strlen($sqlExtra) == 0) ? ' WHERE l.lot_ejr = :ejr ' : ' AND l.lot_ejr = :ejr ';
    $params['ejr'] = APP_EJERCICIO;

    $sqlExtra .= 'GROUP BY l.fpa_id, l.msc_id, l.lot_ejercicio, l.lot_id, pl.pro_id
                  ORDER BY l.fpa_id, l.msc_id, l.lot_ejercicio, l.lot_id';

    $sql = $sql . $sqlExtra;

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
        $dato['modo'] = $row['modo'];
        $dato['tipo'] = $row['tipo'];
        $dato['fpa_id'] = $row['fpa_id'];
        $dato['msc_id'] = $row['msc_id'];
        $dato['lot_ejercicio'] = $row['lot_ejercicio'];
        $dato['lot_id'] = $row['lot_id'];
        $dato['tlo_id'] = $row['tlo_id'];
        $dato['lot_fechaPago'] = $row['lot_fechaPago'];
        $dato['elo_nombre'] = $row['elo_nombre'];
        $dato['lote'] = $row['lote'];
        $dato['lot_importe'] = $row['lot_importe'];
        $dato['pro_id'] = $row['pro_id'];
        $dato['lot_fechaGeneracion'] = $row['lot_fechaGeneracion'];

        array_push($datos, $dato);

        $contador++;
    }
    if (count($datos) == 0) {
        $dato = array();

        $dato['contador'] = '';
        $dato['modo'] = '';
        $dato['tipo'] = '';
        $dato['fpa_id'] = '';
        $dato['msc_id'] = '';
        $dato['lot_ejercicio'] = '';
        $dato['lot_id'] = '';
        $dato['tlo_id'] = '';
        $dato['lot_fechaPago'] = '';
        $dato['elo_nombre'] = '';
        $dato['lote'] = '';
        $dato['lot_importe'] = '';
        $dato['pro_id'] = '';
        $dato['lot_fechaGeneracion'] = '';

        array_push($datos, $dato);
    }
    return $datos;
}
