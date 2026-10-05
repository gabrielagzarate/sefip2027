<?php

/**
 * Description of function consultaTesPendientesMin2017Clc
 *
 * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 07-Sep-2018
 * Comment: -----
 * Objective: Consultar datos en la base de datos de acuerdo a la opcion solicitada
 * Regla de Negocio:----
 * 
 * */

function consultaTesPendientesMin2017Clc($tipo, $fmin, $fmax, $return_sql = "No")
{
    $regresar = null;

    $sqlExtra = "";
    $sqlInner = "";
    $sql = "            
            SELECT
                CONCAT(c.gpo_id, c.ur_id) AS ur,                
                x.clc_cereCo AS cereCo,
                IF(c.clc_tipoCereCo = 1,'CE','CO') as cere,
                c.clc_id AS clc,
                stc.stc_nombre AS subTipoClc,
                c.clc_concepto AS concepto,
                (
                    c.clc_percepcion - c.clc_retencion
                ) AS importe,
                ex.exb_importe,
                c.clc_fechaElaboracion,
                c.bur_id AS cta_Abono,
                bsf_id AS cta_Cargo,
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
                '' fecha,
                '' AS SPEI,
                ex.exb_fechaSap AS Fecha,
                ex.exb_spei AS SPEI,
                l.lot_ejercicio,
                IF (
                    c.bur_id <> 0,
                    'SPEI',
                IF (c.mpc_id = 2, 'SPEI', 'CH')
                ) AS tdf_id,
                ".BD_TESORERIA.".fn_getTipoMinistracion (
                    @gpo := c.gpo_id ,@ur := c.ur_id ,@clc := c.clc_id,
                    2017
                ) AS tmi_id 
            FROM
                ".BD_TESORERIA.".lot_Lote AS l
            INNER JOIN ".BD_TESORERIA.".lot_LoteClc AS dl USING (
                lot_id,
                fpa_id,
                msc_id,
                lot_ejercicio
            )
            INNER JOIN ".BD_TESORERIA.".scp_Clc AS ca USING (gpo_id, ur_id, clc_id)
            INNER JOIN ".BD_CTRL_2017.".scp_Clc AS c USING (gpo_id, ur_id, clc_id)
            INNER JOIN ".BD_CTRL_2017.".scp_ClcAuxiliar AS x USING (gpo_id, ur_id, clc_id)
            LEFT JOIN ".BD_TESORERIA."Temporal.tem_ExtractoBancario AS ex ON ex.gpo_id = x.gpo_id
            #LEFT JOIN ".BD_TESORERIA.".vin_ExtractoBancario AS ex ON ex.gpo_id = x.gpo_id
            AND ex.ur_id = x.ur_id
            AND ex.clc_cereCo = x.clc_cereCo
            AND ex.clc_tipoCereCo = c.clc_tipoCereCo
            AND ex.exb_importe = (
                c.clc_percepcion - c.clc_retencion
            )
            INNER JOIN ".BD_CTRL_2017.".cat_TipoClc AS tc ON tc.tcl_id = c.tcl_id
            INNER JOIN ".BD_CTRL_2017.".cat_SubtipoClc AS stc ON stc.stc_id = c.stc_id
            INNER JOIN ".BD_CTRL_2017.".cat_UnidadResponsable AS ur ON ur.gpo_id = c.gpo_id
            AND ur.ur_id = c.ur_id
            LEFT JOIN ".BD_CTRL_2017.".enc_ClaveFinanciamiento AS cvf USING (ecf_id)
            LEFT JOIN ".BD_CTRL_2017.".enc_BancosSefinCveFin AS v USING (ecf_id)
            INNER JOIN ".BD_CTRL_2017.".scp_BancosSefin USING (bsf_id)
            INNER JOIN ".BD_CTRL_2017.".cat_InstitucionesBancarias AS ib USING (iba_id)
            INNER JOIN ".BD_CTRL_2017.".cat_BancosUr AS br ON br.bur_id = c.bur_id
            WHERE
                ca.ecl_id IN (12)
            AND (lot_fechaPago) BETWEEN :fmin 
            AND :fmax 
            #and ca.gpo_id=1 and ca.ur_id=18
            AND l.lot_ejercicio = 2017
            AND l.lot_ejr= :ejer
            AND ca.ejr_id_clc=2017
            GROUP BY
                c.gpo_id,
                c.ur_id,
                c.clc_id, stc.tcl_id, ex.exb_importe,lote, l.lot_FechaGeneracion, ex.tem_exb;
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
