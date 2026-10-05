<?php
/**
 * Description of function consultaExtractoBancarioDetalle
 *
 * @author Adriana Santiago Morales <adriana.morales@finanzasoaxaca.gob.mx>
 * Created: 11-Feb-2019
 * Comment: -----
 * Objective: Consulta el detalle del Extracto Bancario de acuerdo al rango de fecha establecido.
 * Regla de Negocio: -----
 *
 **/

function consultaExtractoBancarioDetalle($consultaDatos, $fechaMin, $fechaMax)
{
    $sql = "
        SELECT
        cbs_id,
        exb_referencia,
        exb_cargo,
        exb_abono,
        exb_importe,
        exb_fechaTransaccion,
        exb_descripcion,
        exb_spei
    FROM
        " . BD_TESORERIA . ".ban_ExtractoBancario
    WHERE
        cbs_id = '002640700617974494'
    AND (exb_fechaTransaccion) BETWEEN :fmin
    AND :fmax ;
    ";

    if ($consultaDatos) {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar(array(':fmin'=>$fechaMin,':fmax'=>$fechaMax));
        $conexion->desconectar();

        $contador = 1;
        $datos = array();
        while ($row = $conexion->extraer()) {
            $dato = array();

            $dato['contador'] = $contador;
            $dato['cbs_id'] = $row['cbs_id'];
            $dato['exb_referencia'] = $row['exb_referencia'];
            $dato['exb_cargo'] = $row['exb_cargo'];
            $dato['exb_abono'] = $row['exb_abono'];
            $dato['exb_importe'] = $row['exb_importe'];
            $dato['exb_fechaTransaccion'] = $row['exb_fechaTransaccion'];
            $dato['exb_descripcion'] = $row['exb_descripcion'];
            $dato['exb_spei'] = $row['exb_spei'];

            array_push($datos, $dato);

            $contador++;
        }
        if (count($datos) == 0) {
            $dato = array();

            $dato['contador'] = '';
            $dato['cbs_id'] = '';
            $dato['exb_referencia'] = '';
            $dato['exb_cargo'] = '';
            $dato['exb_abono'] = '';
            $dato['exb_importe'] = '';
            $dato['exb_fechaTransaccion'] = '';
            $dato['exb_descripcion'] = '';
            $dato['exb_spei'] = '';

            array_push($datos, $dato);
        }
        return $datos;
    } else {
        return $sql;
    }
}
