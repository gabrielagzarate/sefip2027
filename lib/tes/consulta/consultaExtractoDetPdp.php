<?php
/**
 * Description of function consultaExtractoDetPdp
 *
 * @author Adriana Santiago Morales <adriana.morales@finanzasoaxaca.gob.mx>
 * Created: 12-Feb-2019
 * Comment: -----
 * Objective: Consulta el detalle del Extracto Bancario PDP de acuerdo al rango de fecha establecido.
 * Regla de Negocio: -----
 *
 **/

function consultaExtractoDetPdp($consultaDatos, $fechaMin, $fechaMax)
{
    $sql = "
            SELECT
            ex.cbs_id,
            ex.exb_referencia,
            ex.exb_cargo,
            ex.exb_abono,
            ex.exb_importe,
            ex.exb_fechaTransaccion,
            ex.exb_descripcion,
            ex.exb_spei,
            cl.ur AS ur,
            cl.clc_id,
            cl.clc_cereCo
        FROM
            " . BD_TESORERIA . ".ban_ExtractoBancario AS ex
        INNER JOIN " . BD_TESORERIA . ".vin_ExtractoBancarioDocumento USING (exb_id)
        INNER JOIN ".BD_CTRL.".cat_BancoDocumentos USING (bdo_id)
        INNER JOIN (
            SELECT
                concat(c.gpo_id,c.ur_id) as ur,
                c.clc_id,
                x.clc_cereCo,
                bdo_id,
                afe_id
            FROM
                ".BD_CTRL.".scp_Presupuestal
            INNER JOIN ".BD_CTRL.".scp_Clc AS c USING (gpo_id, ur_id, clc_id)
            INNER JOIN ".BD_CTRL.".scp_ClcAuxiliar AS x USING (gpo_id, ur_id, clc_id)
            WHERE
                c.mpc_id = 5
            UNION ALL
                SELECT
                    concat(c.gpo_id,c.ur_id) as ur,
                    c.clc_id,
                    x.clc_cereCo,
                    bdo_id,
                    afe_id
                FROM
                    ".BD_CTRL.".scp_Contable
                INNER JOIN ".BD_CTRL.".scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                INNER JOIN ".BD_CTRL.".scp_ClcAuxiliar AS x USING (gpo_id, ur_id, clc_id)
                WHERE
                    c.mpc_id = 5
        ) AS cl USING (bdo_id)
        WHERE
            cbs_id = '002640700617974494'
            AND (exb_fechaTransaccion) BETWEEN :fmin
            AND :fmax
            AND ejr_id = ".APP_EJERCICIO."
            UNION ALL
            SELECT
            ex.cbs_id,
            ex.exb_referencia,
            ex.exb_cargo,
            ex.exb_abono,
            ex.exb_importe,
            ex.exb_fechaTransaccion,
            ex.exb_descripcion,
            ex.exb_spei,
            cl.ur AS ur,
            cl.clc_id,
            cl.clc_cereCo
        FROM
            " . BD_TESORERIA . ".ban_ExtractoBancario AS ex
        INNER JOIN " . BD_TESORERIA . ".vin_ExtractoBancarioDocumento USING (exb_id)
        INNER JOIN ".BD_CTRL_2025.".cat_BancoDocumentos USING (bdo_id)
        INNER JOIN (
            SELECT
                concat(c.gpo_id,c.ur_id) as ur,
                c.clc_id,
                x.clc_cereCo,
                bdo_id,
                afe_id
            FROM
                ".BD_CTRL_2025.".scp_Presupuestal
            INNER JOIN ".BD_CTRL_2025.".scp_Clc AS c USING (gpo_id, ur_id, clc_id)
            INNER JOIN ".BD_CTRL_2025.".scp_ClcAuxiliar AS x USING (gpo_id, ur_id, clc_id)
            WHERE
                c.mpc_id = 5
            UNION ALL
                SELECT
                    concat(c.gpo_id,c.ur_id) as ur,
                    c.clc_id,
                    x.clc_cereCo,
                    bdo_id,
                    afe_id
                FROM
                    ".BD_CTRL_2025.".scp_Contable
                INNER JOIN ".BD_CTRL_2025.".scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                INNER JOIN ".BD_CTRL_2025.".scp_ClcAuxiliar AS x USING (gpo_id, ur_id, clc_id)
                WHERE
                    c.mpc_id = 5
        ) AS cl USING (bdo_id)
        WHERE
            cbs_id = '002640700617974494'
            AND (exb_fechaTransaccion) BETWEEN :fmin
            AND :fmax
            AND ejr_id = 2025
        UNION ALL
            SELECT
            ex.cbs_id,
            ex.exb_referencia,
            ex.exb_cargo,
            ex.exb_abono,
            ex.exb_importe,
            ex.exb_fechaTransaccion,
            ex.exb_descripcion,
            ex.exb_spei,
            cl.ur AS ur,
            cl.clc_id,
            cl.clc_cereCo
        FROM
            " . BD_TESORERIA . ".ban_ExtractoBancario AS ex
        INNER JOIN " . BD_TESORERIA . ".vin_ExtractoBancarioDocumento USING (exb_id)
        INNER JOIN ".BD_CTRL_2024.".cat_BancoDocumentos USING (bdo_id)
        INNER JOIN (
            SELECT
                concat(c.gpo_id,c.ur_id) as ur,
                c.clc_id,
                x.clc_cereCo,
                bdo_id,
                afe_id
            FROM
                ".BD_CTRL_2024.".scp_Presupuestal
            INNER JOIN ".BD_CTRL_2024.".scp_Clc AS c USING (gpo_id, ur_id, clc_id)
            INNER JOIN ".BD_CTRL_2024.".scp_ClcAuxiliar AS x USING (gpo_id, ur_id, clc_id)
            WHERE
                c.mpc_id = 5
            UNION ALL
                SELECT
                    concat(c.gpo_id,c.ur_id) as ur,
                    c.clc_id,
                    x.clc_cereCo,
                    bdo_id,
                    afe_id
                FROM
                    ".BD_CTRL_2024.".scp_Contable
                INNER JOIN ".BD_CTRL_2024.".scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                INNER JOIN ".BD_CTRL_2024.".scp_ClcAuxiliar AS x USING (gpo_id, ur_id, clc_id)
                WHERE
                    c.mpc_id = 5
        ) AS cl USING (bdo_id)
        WHERE
            cbs_id = '002640700617974494'
            AND (exb_fechaTransaccion) BETWEEN :fmin
            AND :fmax
            AND ejr_id = 2024
            UNION ALL
            SELECT
            ex.cbs_id,
            ex.exb_referencia,
            ex.exb_cargo,
            ex.exb_abono,
            ex.exb_importe,
            ex.exb_fechaTransaccion,
            ex.exb_descripcion,
            ex.exb_spei,
            cl.ur AS ur,
            cl.clc_id,
            cl.clc_cereCo
        FROM
            " . BD_TESORERIA . ".ban_ExtractoBancario AS ex
        INNER JOIN " . BD_TESORERIA . ".vin_ExtractoBancarioDocumento USING (exb_id)
        INNER JOIN ".BD_CTRL_2023.".cat_BancoDocumentos USING (bdo_id)
        INNER JOIN (
            SELECT
                concat(c.gpo_id,c.ur_id) as ur,
                c.clc_id,
                x.clc_cereCo,
                bdo_id,
                afe_id
            FROM
                ".BD_CTRL_2023.".scp_Presupuestal
            INNER JOIN ".BD_CTRL_2023.".scp_Clc AS c USING (gpo_id, ur_id, clc_id)
            INNER JOIN ".BD_CTRL_2023.".scp_ClcAuxiliar AS x USING (gpo_id, ur_id, clc_id)
            WHERE
                c.mpc_id = 5
            UNION ALL
                SELECT
                    concat(c.gpo_id,c.ur_id) as ur,
                    c.clc_id,
                    x.clc_cereCo,
                    bdo_id,
                    afe_id
                FROM
                    ".BD_CTRL_2023.".scp_Contable
                INNER JOIN ".BD_CTRL_2023.".scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                INNER JOIN ".BD_CTRL_2023.".scp_ClcAuxiliar AS x USING (gpo_id, ur_id, clc_id)
                WHERE
                    c.mpc_id = 5
        ) AS cl USING (bdo_id)
        WHERE
            cbs_id = '002640700617974494'
            AND (exb_fechaTransaccion) BETWEEN :fmin
            AND :fmax
            AND ejr_id = 2023
            UNION ALL
        SELECT
            ex.cbs_id,
            ex.exb_referencia,
            ex.exb_cargo,
            ex.exb_abono,
            ex.exb_importe,
            ex.exb_fechaTransaccion,
            ex.exb_descripcion,
            ex.exb_spei,
            cl.ur AS ur,
            cl.clc_id,
            cl.clc_cereCo
        FROM
            " . BD_TESORERIA . ".ban_ExtractoBancario AS ex
        INNER JOIN " . BD_TESORERIA . ".vin_ExtractoBancarioDocumento USING (exb_id)
        INNER JOIN ".BD_CTRL_2023.".cat_BancoDocumentos USING (bdo_id)
        INNER JOIN (
            SELECT
                concat(c.gpo_id,c.ur_id) as ur,
                c.clc_id,
                x.clc_cereCo,
                bdo_id,
                afe_id
            FROM
                ".BD_CTRL_2023.".scp_Presupuestal
            INNER JOIN ".BD_CTRL_2023.".scp_Clc AS c USING (gpo_id, ur_id, clc_id)
            INNER JOIN ".BD_CTRL_2023.".scp_ClcAuxiliar AS x USING (gpo_id, ur_id, clc_id)
            WHERE
                c.mpc_id = 5
            UNION ALL
                SELECT
                    concat(c.gpo_id,c.ur_id) as ur,
                    c.clc_id,
                    x.clc_cereCo,
                    bdo_id,
                    afe_id
                FROM
                    ".BD_CTRL_2022.".scp_Contable
                INNER JOIN ".BD_CTRL_2022.".scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                INNER JOIN ".BD_CTRL_2022.".scp_ClcAuxiliar AS x USING (gpo_id, ur_id, clc_id)
                WHERE
                    c.mpc_id = 5
        ) AS cl USING (bdo_id)
        WHERE
            cbs_id = '002640700617974494'
            AND (exb_fechaTransaccion) BETWEEN :fmin
            AND :fmax
            AND ejr_id = 2022
        UNION ALL
        SELECT
            ex.cbs_id,
            ex.exb_referencia,
            ex.exb_cargo,
            ex.exb_abono,
            ex.exb_importe,
            ex.exb_fechaTransaccion,
            ex.exb_descripcion,
            ex.exb_spei,
            cl.ur AS ur,
            cl.clc_id,
            cl.clc_cereCo
        FROM
            " . BD_TESORERIA . ".ban_ExtractoBancario AS ex
        INNER JOIN " . BD_TESORERIA . ".vin_ExtractoBancarioDocumento USING (exb_id)
        INNER JOIN ".BD_CTRL_2021.".cat_BancoDocumentos USING (bdo_id)
        INNER JOIN (
            SELECT
                concat(c.gpo_id,c.ur_id) as ur,
                c.clc_id,
                x.clc_cereCo,
                bdo_id,
                afe_id
            FROM
                ".BD_CTRL_2021.".scp_Presupuestal
            INNER JOIN ".BD_CTRL_2021.".scp_Clc AS c USING (gpo_id, ur_id, clc_id)
            INNER JOIN ".BD_CTRL_2021.".scp_ClcAuxiliar AS x USING (gpo_id, ur_id, clc_id)
            WHERE
                c.mpc_id = 5
            UNION ALL
                SELECT
                    concat(c.gpo_id,c.ur_id) as ur,
                    c.clc_id,
                    x.clc_cereCo,
                    bdo_id,
                    afe_id
                FROM
                    ".BD_CTRL_2021.".scp_Contable
                INNER JOIN ".BD_CTRL_2021.".scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                INNER JOIN ".BD_CTRL_2021.".scp_ClcAuxiliar AS x USING (gpo_id, ur_id, clc_id)
                WHERE
                    c.mpc_id = 5
        ) AS cl USING (bdo_id)
        WHERE
            cbs_id = '002640700617974494'
            AND (exb_fechaTransaccion) BETWEEN :fmin
            AND :fmax
            AND ejr_id = 2021
        UNION ALL
        SELECT
            ex.cbs_id,
            ex.exb_referencia,
            ex.exb_cargo,
            ex.exb_abono,
            ex.exb_importe,
            ex.exb_fechaTransaccion,
            ex.exb_descripcion,
            ex.exb_spei,
            cl.ur AS ur,
            cl.clc_id,
            cl.clc_cereCo
        FROM
            " . BD_TESORERIA . ".ban_ExtractoBancario AS ex
        INNER JOIN " . BD_TESORERIA . ".vin_ExtractoBancarioDocumento USING (exb_id)
        INNER JOIN ".BD_CTRL_2020.".cat_BancoDocumentos USING (bdo_id)
        INNER JOIN (
            SELECT
                concat(c.gpo_id,c.ur_id) as ur,
                c.clc_id,
                x.clc_cereCo,
                bdo_id,
                afe_id
            FROM
                ".BD_CTRL_2020.".scp_Presupuestal
            INNER JOIN ".BD_CTRL_2020.".scp_Clc AS c USING (gpo_id, ur_id, clc_id)
            INNER JOIN ".BD_CTRL_2020.".scp_ClcAuxiliar AS x USING (gpo_id, ur_id, clc_id)
            WHERE
                c.mpc_id = 5
            UNION ALL
                SELECT
                    concat(c.gpo_id,c.ur_id) as ur,
                    c.clc_id,
                    x.clc_cereCo,
                    bdo_id,
                    afe_id
                FROM
                    ".BD_CTRL_2020.".scp_Contable
                INNER JOIN ".BD_CTRL_2020.".scp_Clc AS c USING (gpo_id, ur_id, clc_id)
                INNER JOIN ".BD_CTRL_2020.".scp_ClcAuxiliar AS x USING (gpo_id, ur_id, clc_id)
                WHERE
                    c.mpc_id = 5
        ) AS cl USING (bdo_id)
        WHERE
            cbs_id = '002640700617974494'
            AND (exb_fechaTransaccion) BETWEEN :fmin
            AND :fmax
            AND ejr_id = 2020
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
            $dato['cbs_id'] = "'" . $row['cbs_id'];
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

            array_push($datos, $dato);
        }
        return $datos;
    } else {
        return $sql;
    }
}