<?php
/**
 * Description of function nivelFolio
 *
 * @author ?
 * Collaborators: Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 12-Dic-2019
 * Comment: -----
 * Objective: obtener los datos para lotes
 * Regla de Negocio: Obtener los lotes de acuerdo al filtro de la pantalla  a partir del ejercicio 2017 segun sea el caso
 *
 **/
require_once dirname(__FILE__) . '/consultaLote.php';
require_once dirname(__FILE__) . '/../../../lib/helpers.php';

function nivelConsultaLote($lot_ejercicio = '', $fpa_id = '', $lot_tipoCereCo = '', $tlo_id = '', $fechaMin = '', $fechaMax = '', $lote = '', $pro_id = '')
{
    $contador = 1;
    $sql = consultaLote($lot_ejercicio, $fpa_id, $lot_tipoCereCo, $tlo_id, $fechaMin, $fechaMax, $lote, $pro_id);

    $con = new AccesoDatos();
    $con->conectar();
    $con->setSql($sql);
    $con->consultar();
    $con->desconectar();

    $result = array();

    while ($row = $con->extraer()) {
        $dato = array(
            'contador' => $contador++,
            'modo' => $row['modo'],
            'tipo' => $row['tipo'],
            'lot_ejercicio' => $row['lot_ejercicio'],
            'lote' => $row['lote'],
            'pro_id' => isset($row['propuesta_id']) ? $row['propuesta_id'] : '',
            'elo_nombre' => utf8Decode($row['elo_nombre']),
            'lot_fechaPago' => isset($row['lot_fechaPago']) ? $row['lot_fechaPago'] : '',
            'lot_importe' => $row['lot_importe'],
        );

        array_push($result, $dato);
    }

    return $result;
} //Fin function nivelConsultaLote
