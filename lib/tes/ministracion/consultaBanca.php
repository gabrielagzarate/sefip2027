<?php
/**
 * Description of function consultaBanca
 *
 * author: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 09-Ene-2019
 * Comment: -----
 * Objective: Consulta datos del catálogo de los .
 * Regla de Negocio: -----
 *
 * */

function consultaBanca($lot_id, $fpa_id, $msc_id, $lot_ejercicio)
{
    $e2026 = new clcs2026;
    $e2025 = new clcs2025;
    $e2024 = new clcs2024;
    $e2023 = new clcs2023;
    $e2022 = new clcs2022;
    $e2021 = new clcs2021;
    $e2020 = new clcs2020;
    $e2019 = new clcs2019;
    $e2018 = new clcs2018;
    $e2017 = new clcs2017;

    $sql = 'SELECT
                tlo_id
            FROM
                '.BD_TESORERIA.'.lot_Lote AS l
                WHERE
                l.lot_id = :lot_id
                AND l.fpa_id= :fpa_id
                AND l.msc_id= :msc_id
                AND l.lot_ejercicio= :lot_ejercicio';
    
    $parametros = [
        'lot_id' => $lot_id,
        'fpa_id' => $fpa_id,
        'msc_id' => $msc_id,
        'lot_ejercicio' => $lot_ejercicio,
    ];

    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($parametros);

    $row2 = $conexion->extraer();
    $tipoLote = $row2['tlo_id'];
    $sql = "";

    switch ($tipoLote) {
        case 6:
            $sql = $e2026->DetalleManual($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            break;
        case 4:
            $sql = $e2026->getDetalleMunicipio2026($fpa_id, $msc_id, $lot_ejercicio, $lot_id);
            break;
        default:
            if ($fpa_id == 3) {
                $sql = $e2026->getDetalleLoteLinea2026($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2017) {
                $sql = $e2017->DetalleLoteLayout2017($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2018) {
                $sql = $e2018->DetalleLoteLayout2018($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2019) {
                $sql = $e2019->DetalleLoteLayout2019($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2020) {
                $sql = $e2020->DetalleLoteLayout2020($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2021) {
                $sql = $e2021->DetalleLoteLayout2021($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2022) {
                $sql = $e2022->DetalleLoteLayout2022($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2023) {
                $sql = $e2023->DetalleLoteLayout2023($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2024) {
                $sql = $e2024->DetalleLoteLayout2024($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2025) {
                $sql = $e2025->DetalleLoteLayout2025($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            } elseif ($lot_ejercicio == 2026) {
                $sql = $e2026->DetalleLoteLayout2026($lot_id, $fpa_id, $msc_id, $lot_ejercicio);
            }
            break;
    }

    $conexion->setSql($sql);
    $conexion->consultar();
    $conexion->desconectar();

    $datos = array();
    while ($row = $conexion->extraer()) {
        $dato = array(
            'nivel' => isset($row['nivel']) ? $row['nivel'] : '',
            'lotDet_fechaPago' => $row['lotDet_fechaPago'],
            'lote_egreso' => $row['lote_egreso'],
            'lotDet_ur' => $row['lotDet_ur'],
            'lotDet_clc' => $row['lotDet_clc'],
            'lotDet_tipo' => $row['lotDet_tipo'],
            'lotDet_folio' => $row['lotDet_folio'],
            'lotDet_folioL' => isset($row['lotDet_folioL']) ? $row['lotDet_folioL'] : '',
            'lotDet_lineaC' => isset ($row['lotDet_lineaC']) ? $row['lotDet_lineaC'] : '',
            'lotDet_importe' => $row['lotDet_importe'],
            'lotDet_responsable' => $row['lotDet_responsable'],
            'lotDet_beneficiario' => $row['lotDet_beneficiario'],
            'lotDet_concepto' => $row['lotDet_concepto'],
            'banco' => $row['banco'],
            'lotDet_CtaCargo' => $row['lotDet_CtaCargo'],
            'lotDet_CtaAbono' => $row['lotDet_CtaAbono'],
            'cvefin' => $row['cvefin'],
            'lotDet' => $row['lotDet'],
            'lotDet_fecha' => $row['lotDet_fecha'],
        );

        array_push($datos, $dato);
    }

    if (count($datos) == 0) {
        $dato = array(
            'lotDet_fechaPago' => '',
            'lote_egreso' => '',
            'lotDet_ur' => '',
            'lotDet_clc' => '',
            'lotDet_tipo' => '',
            'lotDet_folio' => '',
            'lotDet_folioL' => '',
            'lotDet_lineaC' => '',
            'lotDet_importe' => '',
            'lotDet_responsable' => '',
            'lotDet_beneficiario' => '',
            'lotDet_concepto' => '',
            'banco' => '',
            'lotDet_CtaCargo' => '',
            'lotDet_CtaAbono' => '',
            'cvefin' => '',
            'lotDet' => '',
            'lotDet_fecha' => $row['lotDet_fecha'],
        );

        array_push($datos, $dato);
    }

    return $datos;
}
