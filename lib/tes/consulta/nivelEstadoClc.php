<?php
/**
 * Description of function nivelFolio
 *
 * @author ?
 * Collaborators: Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 12-Dic-2019
 * Comment: -----
 * Objective: obtener los datos para los estados de clcs
 * Regla de Negocio: Obtener las clcs de acuerdo al filtro de la pantalla  a partir del ejercicio 2017 segun sea el caso
 *
 **/
require_once dirname(__FILE__) . '/consultaEstadoClc.php';

function nivelEstadoClc($ejercicio, $sqlExtra = '', $estado = '', $sqlCtrlAnterior = '', $estado2018 = '', $estadoCtrl2018 = '')
{
    $sql = consultaEstadoClc($ejercicio, $sqlExtra, $estado, $sqlCtrlAnterior, $estado2018, $estadoCtrl2018);

    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar();
    $conexion->desconectar();

    $result = array();
    $i = 1;
    while ($row = $conexion->extraer()) {
        $dato = array(
            'id' => $i++,
            'modo' => $row['mpc_nombre'],
            'tipo' => $row['tcl_id'],
            'ejercicio' => $ejercicio,
            'FECHA_ELABORACION' => $row['clc_fechaElaboracion'],
            'FECHA_PAGO' => $row['lot_fechaPago'],
            'FECHA_CERE' => $row['clc_generacionCereCo'],
            'loe_clave' => $row['loe_clave'],
            'ur' => $row['ur'],
            'clc_id' => $row['clc_id'],
            'capitulo_gasto' => $row['capitulo_gasto'],
            'clc_cereCo' => $row['clc_cereCo'],
            'clc_beneficiario' => $row['clc_beneficiario'],
            'clc_concepto' => $row['clc_concepto'],
            'importe' => $row['importe'],
            'iba_nombre' => $row['iba_abreviatura'],
            'cbs_id' => "'" . $row['cbs_id'],
            'cbs_nombre' => $row['cbs_nombre'],
            'bur_id' => "'" . $row['bur_id'],
            'ecf_clave' => $row['ecf_clave'],
            'lote_id' => $row['lote_id'],
            'ur_nombre' => $row['ur_nombre'],
            'estado' => $row['estado'],
            'clc_tipoCereCo' => $row['clc_tipoCereCo'],
            'tcl_nombre' => $row['tcl_nombre'],
            'stc_nombre' => $row['stc_nombre'],
        );
        array_push($result, $dato);
    }
    return $result;
} //Fin function nivelEstadoClc
