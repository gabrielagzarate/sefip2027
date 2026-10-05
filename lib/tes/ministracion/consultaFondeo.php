<?php
/**
 * Description of function consultaFondeo
 *
 * author: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 12-Dic-2019
 * Comment: -----
 * Objective: Consulta datos del catálogo de los movimietos bancarios en estado autorizado y de tipo traspaso, inversion y fondeo.
 * Regla de Negocio: -----
 *
 * */

function consultaFondeo($tipo, $fecha, $fechaMax, $mbn_id)
{

    $sql_extra = '';
    $parametros = [
        'tipo' => $tipo,
    ];
    if (!empty($mbn_id)) {
        $parametros += [
            'mbn_id' => $mbn_id,
        ];
        $sql_extra = 'AND mbn_id = :mbn_id';
    }

    if (!empty($fecha)) {
        $fecha = explode("/", $fecha);
        $fecha = $fecha[2] . "-" . $fecha[1] . "-" . $fecha[0];
        $parametros += [
            'mbn_fechaMovimiento' => $fecha,
        ];
        $sqlFechaMomiviento = ' AND mbn_fechaMovimiento >= DATE(:mbn_fechaMovimiento) ';
    }
    if (!empty($fechaMax)) {
        $fechaMax = explode("/", $fechaMax);
        $fechaMax = $fechaMax[2] . "-" . $fechaMax[1] . "-" . $fechaMax[0];
        $parametros += [
            'mbn_fechaMovimientoMax' => $fechaMax,
        ];
        $sqlFechaMomiviento .= ' AND mbn_fechaMovimiento <= DATE(:mbn_fechaMovimientoMax) ';
    }

    $sql = '
            SELECT
                (SELECT iba_abreviatura FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria USING(iba_id) WHERE cbs_id=cbs_id_cargo )cargo,
                cbs_id_cargo,
                (SELECT iba_abreviatura FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria USING(iba_id) WHERE cbs_id=cbs_id_abono )abono,
                cbs_id_abono,
                FORMAT(mbn_importe, 2) as importe,
                mbn_observacion, mbn_fechaAutorizacion, mbn_fechaMovimiento,
                mbn_id
            FROM ' . BD_TESORERIA . '.ban_MovimientoBancario
            INNER JOIN ' . BD_TESORERIA . '.cat_EstadoMovimientoBancario using(emb_id)
            WHERE  emb_id=3 AND tmb_id= :tipo' . $sqlFechaMomiviento . '
            ' . $sql_extra . '
    ';

    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);

    $conexion->consultar($parametros);
    $conexion->desconectar();

    $datos = array();
    while ($row = $conexion->extraer()) {
        $dato = array(
            'cargo' => $row['cargo'],
            'cbs_id_cargo' => $row['cbs_id_cargo'],
            'abono' => $row['abono'],
            'cbs_id_abono' => $row['cbs_id_abono'],
            'importe' => $row['importe'],
            'mbn_observacion' => $row['mbn_observacion'],
            'mbn_fechaAutorizacion' => $row['mbn_fechaAutorizacion'],
            'mbn_fechaMovimiento' => $row['mbn_fechaMovimiento'],
            'mbn_id' => $row['mbn_id'],
        );

        array_push($datos, $dato);
    }
    if (count($datos) == 0) {
        $dato = array(
            'cargo' => '',
            'cbs_id_cargo' => '',
            'abono' => '',
            'cbs_id_abono' => '',
            'importe' => '',
            'mbn_observacion' => '',
            'mbn_fechaAutorizacion' => '',
            'mbn_fechaMovimiento' => '',
            'mbn_id' => '',
        );

        array_push($datos, $dato);
    }
    return $datos;
}
