<?php

/**
 * Description of function consultaTesPendientesMin2024LCT
 * Objective: Consultar datos en la base de datos de los pendiente de ministrar 2024, de las lineas de 
        captura procesadas 
    como transferencias
 * Regla de Negocio:----
 * 
 * */

function consultaTesPendientesMin2024LCT($tipo, $fmin, $fmax, $return_sql = "No")
{
    $regresar = null;

    $sql = "           
            SELECT 
                CONCAT(c.gpo_id, c.ur_id) AS ur,
                c.clc_id AS clc,
                x.clc_cereCo AS cereCo,
                (
                    c.clc_percepcion - c.clc_retencion
                ) AS importe,
                c.clc_concepto AS concepto,               
                sf.cbs_id AS cta_Cargo,
                c.bur_id AS cta_Abono,
                CONCAT(
                IF(l.fpa_id = 1,
                            'T',
                            IF(l.fpa_id = 2, 'C', 'L')),
                        LPAD(l.msc_id, 2, '0'),
                        LPAD(l.lot_id, 4, '0'),
                        SUBSTR(l.lot_ejercicio, 3)
                ) AS lote,l.tlo_id,
                l.lot_FechaGeneracion,
                ''as fechaPago,
                '' as  spei,
                IF (c.bur_id <> 0,'SPEI',
                IF (c.mpc_id = 2, 'SPEI', 'CH')
                ) AS tdf_id,
                ".BD_TESORERIA.".fn_getTipoMinistracion (
                        @gpo := c.gpo_id ,@ur := c.ur_id ,@clc := c.clc_id,
                        2024
                ) AS tmi_id 
            
            FROM
                ".BD_TESORERIA.".lot_Lote AS l
                    INNER JOIN
                ".BD_TESORERIA.".lot_LoteClc AS dl USING (lot_id , fpa_id , msc_id , lot_ejercicio)
                    INNER JOIN
                ".BD_CTRL_2024.".scp_Clc AS c USING (gpo_id , ur_id , clc_id)
                    INNER JOIN 
                ".BD_CTRLANT.".scp_Clc as ant using (gpo_id,ur_id,clc_id)
                    INNER JOIN
                ".BD_CTRL_2024.".scp_ClcAuxiliar AS x USING (gpo_id , ur_id , clc_id)
                    INNER JOIN
                ".BD_CTRL_2024.".cat_TipoClc AS tc ON tc.tcl_id = c.tcl_id
                    INNER JOIN
                ".BD_CTRL_2024.".cat_SubtipoClc AS stc ON stc.stc_id = c.stc_id
                    INNER JOIN
                ".BD_CTRL_2024.".cat_UnidadResponsable as ur on ur.gpo_id=c.gpo_id and ur.ur_id=c.ur_id
                    INNER JOIN
                ".BD_CTRL_2024.".enc_ClaveFinanciamiento AS cvf USING (ecf_id)
                    INNER JOIN
                ".BD_TESORERIA.".vin_SefinClaveFinanciamiento AS v USING (ecf_id)
                    INNER JOIN
                ".BD_TESORERIA.".ban_CuentaBancariaSefin AS sf on v.cbs_id=sf.cbs_id
                    INNER JOIN
                ".BD_TESORERIA.".cat_InstitucionBancaria AS ib USING (iba_id)
                    INNER JOIN
                ".BD_CTRL_2024.".cat_BancosUr AS br ON br.bur_id = c.bur_id
            WHERE
                l.lot_ejercicio = 2024 AND l.elo_id = 8
                    AND ant.ecl_id = 16 AND ant.ejr_id_clc=2024
                    AND c.mpc_id=2 AND l.fpa_id<>3
                    AND (lot_fechaGeneracion BETWEEN :fmin  AND :fmax )
                    AND (c.mpc_id , l.tlo_id) NOT IN ((2 , 5))
                    AND l.lot_ejr= :ejer
            GROUP BY c.gpo_id , c.ur_id , c.clc_id, sf.cbs_id, lote, tlo_id, l.lot_FechaGeneracion;";

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
