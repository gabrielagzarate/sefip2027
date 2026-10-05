<?php

/**
 * Description of function consultaTesPendientesMin2019Clc
 *
 * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Migrated: 30-01-2020
 * Comment: -----
 * Objective: Consultar datos en la base de datos de los pendientes por ministrar 2019
 * Regla de Negocio:----
 * 
 * */

function consultaTesPendientesMin2019Clc($tipo, $fmin, $fmax, $return_sql = "No")
{
    $regresar = null;

    $sqlExtra = "";
    $sqlInner = "";
    $sql = "
            SELECT
                CONCAT(c.gpo_id, c.ur_id) AS ur,
                c.clc_id AS clc,
                x.clc_cereCo AS cereCo,
                IF(c.clc_tipoCereCo = 1,'CE','CO') as cere,
                (
                    c.clc_percepcion - c.clc_retencion
                ) AS importe,
                c.clc_concepto AS concepto,
                sf.cbs_id AS cta_Cargo,
                c.bur_id AS cta_Abono,
                CONCAT(
                    IF (
                        l.fpa_id = 1,
                        'T',
                    IF (l.fpa_id = 2, 'C', 'L')
                    ),
                    LPAD(l.msc_id, 2, '0'),
                    LPAD(l.lot_id, 4, '0'),
                    SUBSTR(l.lot_ejercicio, 3)
                ) AS lote,
                l.lot_FechaGeneracion,
            IF (
                c.bur_id <> 0,
                'SPEI',
            IF (c.mpc_id = 2, 'SPEI', 'CH')
            ) AS tdf_id,
            ".BD_TESORERIA.".fn_getTipoMinistracion (
                @gpo := c.gpo_id ,@ur := c.ur_id ,@clc := c.clc_id,
                2019
            ) AS tmi_id 
            FROM
                ".BD_TESORERIA.".lot_Lote AS l
            INNER JOIN ".BD_TESORERIA.".lot_LoteClc AS dl USING (
                lot_id,
                fpa_id,
                msc_id,
                lot_ejercicio
            )
            INNER JOIN ".BD_CTRL_2019.".scp_Clc AS c USING (gpo_id, ur_id, clc_id)
            INNER JOIN ".BD_CTRLANT.".scp_Clc as ant using (gpo_id,ur_id,clc_id) 
            INNER JOIN ".BD_CTRL_2019.".scp_ClcAuxiliar AS x USING (gpo_id, ur_id, clc_id) 
            " . $sqlInner . "
            INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento AS v USING (ecf_id)
            INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin AS sf ON v.cbs_id = sf.cbs_id
            WHERE
                l.lot_ejercicio = 2019
            AND l.elo_id = 8
            AND ant.ecl_id = 16
            AND l.fpa_id <> 3
            AND (lot_fechaGeneracion) BETWEEN :fmin
            AND :fmax
            AND tlo_id != 4
            AND (c.mpc_id, l.tlo_id) NOT IN ((2, 1),(5, 2))" . $sqlExtra . "
            AND l.lot_ejr= :ejer
            AND ant.ejr_id_clc=2019
            GROUP BY
                c.gpo_id,
                c.ur_id,
                c.clc_id, sf.cbs_id, lote, l.lot_FechaGeneracion;
            ";

    if ($return_sql == "No") {
        $fmin = DateTime::createFromFormat('d/m/Y', $fmin);
        $fmin = $fmin->format('Y-m-d');
        $fmax = DateTime::createFromFormat('d/m/Y', $fmax);
        $fmax = $fmax->format('Y-m-d');
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar(array(':fmin' => $fmin,':fmax' => $fmax,':ejer' => APP_EJERCICIO));
        $conexion->desconectar();

        $datos = array();
        while ($row = $conexion->extraer()) {
            $dato = array(
                'ur' => $row['ur'],
                'clc' => $row['clc'],
                'cereCo' => $row['cereCo'],
                'cere' => $row['cere'],
                'importe' => $row['importe'],
                'concepto' => $row['concepto'],
                'cta_Cargo' => "'" . $row['cta_Cargo'],
                'cta_Abono' => "'" . $row['cta_Abono'],
                'lote' => $row['lote'],
                'lot_FechaGeneracion' => $row['lot_FechaGeneracion'],
                'fechaPago' => $row['fechaPago'],
                'spei' => $row['spei'],
                'tdf_id' => $row['tdf_id'],
                'tmi_id' => $row['tmi_id']
            );
            array_push($datos, $dato);
        }

        if (count($datos) == 0) {
            $dato = array(
                'ue_id'    => '',
                'ue_nombre' => '',
                'nivel'    => 1
            );
            array_push($datos, $dato);
        }
        $regresar = $datos;
    } else {
        $regresar = $sql;
    }
    return $regresar;
}
