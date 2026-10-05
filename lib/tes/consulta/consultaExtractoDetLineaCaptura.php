<?php
/**
 * Description of function consultaExtractoDetLineaCaptura
 *
 * @author Adriana Santiago Morales <adriana.morales@finanzasoaxaca.gob.mx>
 * Created: 12-Feb-2019
 * Comment: -----
 * Objective: Consulta el detalle del Extracto Bancario de acuerdo al rango de fecha establecido.
 * Regla de Negocio: -----
 *
 **/

function consultaExtractoDetLineaCaptura($consultaDatos, $fechaMin, $fechaMax)
{
    $sql = "
        SELECT
            ex.cbs_id,
            MIN(ex.exb_referencia) AS exb_referencia,
            MIN(ex.exb_cargo) AS exb_cargo,
            MIN(ex.exb_abono) AS exb_abono,
            MIN(ex.exb_importe) AS exb_importe,
            MIN(ex.exb_fechaTransaccion) AS exb_fechaTransaccion,
            MIN(ex.exb_descripcion) AS exb_descripcion,
            MIN(ex.exb_spei) AS exb_spei,
            CONCAT(v.gpo_id,v.ur_id) as ur,
            v.clc_id,
            x.clc_cereCo,
            lc.lca_lineaCaptura AS lineaCaptura
        FROM
            " . BD_TESORERIA . ".ban_ExtractoBancario AS ex
        INNER JOIN " . BD_TESORERIA . ".vin_ExtractoBancarioLineaCaptura USING (exb_id)
        INNER JOIN " . BD_TESORERIA . ".vin_LoteDocumentoLineaCaptura AS v USING (lca_id)
        INNER JOIN " . BD_TESORERIA . ".lca_LineaCaptura AS lc USING (lca_id)
        INNER JOIN " . BD_CTRL . ".scp_ClcAuxiliar x USING (gpo_id, ur_id, clc_id)
        WHERE
            cbs_id = '002640700617974494'
        AND (exb_fechaTransaccion) BETWEEN :fmin
        AND :fmax
        GROUP BY
            x.gpo_id,
            x.ur_id,
            x.clc_id,
            x.clc_cereCo,lc.lca_id;
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
            $dato['ur'] = $row['ur'];
            $dato['clc_id'] = $row['clc_id'];
            $dato['clc_cereCo'] = $row['clc_cereCo'];
            $dato['lineaCaptura'] = "'" . $row['lineaCaptura'];

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
            $dato['ur'] = '';
            $dato['clc_id'] = '';
            $dato['clc_cereCo'] = '';
            $dato['lineaCaptura'] = '';

            array_push($datos, $dato);
        }
        return $datos;
    } else {
        return $sql;
    }
}
