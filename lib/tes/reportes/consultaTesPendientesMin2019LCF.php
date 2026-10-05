<?php

/**
 * Description of function consultaTesPendientesMin2019LCF
 *
 * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 01-Feb-2019
 * Comment: -----
 * Objective: Consultar datos en la base de datos de los pendientes por ministrar 2019 linea de captura federal
 * Regla de Negocio:----
 * 
 * */

function consultaTesPendientesMin2019LCF($tipo, $fmin, $fmax, $return_sql = "No")
{
    $regresar = null;

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
                IF (l.fpa_id = 1,'T',
                        IF (l.fpa_id = 2, 'C', 'L')
                        ),LPAD(l.msc_id, 2, '0'),
                        LPAD(l.lot_id, 4, '0'),
                        SUBSTR(l.lot_ejercicio, 3)
                ) AS lote,
                l.lot_FechaGeneracion,
                ''as fechaPago,
                '' as  spei,
                IF (c.bur_id <> 0,'SPEI',
                IF (c.mpc_id = 2, 'SPEI', 'CH')
                ) AS tdf_id,
                ".BD_TESORERIA.".fn_getTipoMinistracion (
                        @gpo := c.gpo_id ,@ur := c.ur_id ,@clc := c.clc_id,
                        2019
                ) AS tmi_id 
            FROM
                ".BD_TESORERIA.".lot_Lote AS l
                    INNER JOIN
                ".BD_TESORERIA.".lot_LoteClc AS dl USING (lot_id , fpa_id , msc_id , lot_ejercicio)
                    INNER JOIN
                ".BD_CTRL_2019.".scp_Clc AS c USING (gpo_id , ur_id , clc_id)
                    INNER JOIN 
                ".BD_CTRLANT.".scp_Clc as ant using (gpo_id,ur_id,clc_id) 
                    INNER JOIN
                ".BD_CTRL_2019.".scp_ClcAuxiliar AS x USING (gpo_id , ur_id , clc_id)
                    INNER JOIN
                ".BD_TESORERIA.".vin_SefinClaveFinanciamiento AS v USING (ecf_id)
                    INNER JOIN
                ".BD_TESORERIA.".ban_CuentaBancariaSefin AS sf on v.cbs_id=sf.cbs_id
            WHERE             
                l.lot_ejercicio = 2019 AND l.elo_id in (8) and tlo_id=5 and fpa_id=3
                    AND ant.ecl_id in(15,16) AND ant.ejr_id_clc=2019
                    and (lot_fechaGeneracion) BETWEEN :fmin AND :fmax
                AND l.lot_ejr= :ejer
            GROUP BY c.gpo_id , c.ur_id , c.clc_id, sf.cbs_id, lote,lot_FechaGeneracion;";

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
