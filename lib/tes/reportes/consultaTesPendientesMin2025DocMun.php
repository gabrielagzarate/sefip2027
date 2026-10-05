<?php

/**
 * Description of function consultaTesPendientesMin2025DocMun
 * Objective: Consultar datos en la base de datos de los pendiente de ministrar 2025, de los documentos de municipio.
 * Regla de Negocio:----
 * */

function consultaTesPendientesMin2025DocMun($tipo, $fmin, $fmax, $return_sql = "No")
{
    $regresar = null;

    $sql = "
            SELECT 
                CONCAT(c.gpo_id, c.ur_id) AS ur,
                c.ue_id,
                x.clc_cereCo AS cereCo,
                IF(c.clc_tipoCereCo = 1,'CE','CO') as cere,
                c.clc_id AS clc,
                    doc.bdo_id,
                    doc.afe_id,
                doc.importe AS importe,
                c.clc_concepto AS concepto,
                cbs_id AS cta_Cargo,
                c.bur_id AS cta_Abono,
                CONCAT(IF(l.fpa_id = 1,
                            'T',
                            IF(l.fpa_id = 2, 'C', 'L')),
                        LPAD(l.msc_id, 2, '0'),
                        LPAD(l.lot_id, 4, '0'),
                        SUBSTR(l.lot_ejercicio, 3)) AS lote,
                l.tlo_id,
                l.lot_FechaGeneracion,
                '' AS fechaPago,
                '' AS  spei,
                IF (c.bur_id <> 0,'SPEI',
                IF (c.mpc_id = 2, 'SPEI', 'CH')
                ) AS tdf_id,
                ".BD_TESORERIA.".fn_getTipoMinistracion (
                        @gpo := c.gpo_id ,@ur := c.ur_id ,@clc := c.clc_id,
                        2025
                ) AS tmi_id 
            FROM
                ".BD_TESORERIA.".lot_Lote AS l
                    INNER JOIN
                ".BD_TESORERIA.".lot_LoteClc AS dl USING ( fpa_id , msc_id , lot_ejercicio,lot_id)
                    INNER JOIN 
                ".BD_CTRL_2025.".scp_Clc AS c ON c.gpo_id=dl.gpo_id and c.ur_id=dl.ur_id and c.clc_id =dl.clc_id
                    INNER JOIN 
                ".BD_CTRLANT.".scp_Clc as ant ON c.gpo_id=ant.gpo_id and c.ur_id=ant.ur_id and c.clc_id =ant.clc_id
                    INNER JOIN
                ".BD_CTRL_2025.".scp_ClcAuxiliar AS x ON c.gpo_id=x.gpo_id and c.ur_id=x.ur_id and c.clc_id=x.clc_id
                INNER JOIN 
                ".BD_TESORERIA.".lot_LoteDocumento AS ldc ON ldc.fpa_id=dl.fpa_id and ldc.msc_id=dl.msc_id and ldc.lot_ejercicio=dl.lot_ejercicio and ldc.lot_id=dl.lot_id
            INNER JOIN(
                SELECT gpo_id,ur_id,clc_id,bdo_id,afe_id,bdo_estadoMinistrado AS edoDoc,ncp_percepcionNeto AS importe
                FROM ".BD_CTRL_2025.".scp_Clc AS c 
                INNER JOIN ".BD_CTRLANT.".scp_Clc as ant using (gpo_id,ur_id,clc_id)
                INNER JOIN ".BD_CTRL_2025.".scp_Presupuestal using (gpo_id,ur_id,clc_id)
                INNER JOIN ".BD_CTRL_2025.".cat_BancoDocumentos using (gpo_id,ur_id,bdo_id)
                WHERE ant.ecl_id=16 
            AND bdo_estadoMinistrado !=3
                UNION ALL
            SELECT gpo_id,ur_id,clc_id,bdo_id ,afe_id,bdo_estadoMinistrado AS edoDoc, (ncc_percepcion-ncc_retencion) AS importe
                FROM ".BD_CTRL_2025.".scp_Clc AS c
                INNER JOIN ".BD_CTRLANT.".scp_Clc as ant using (gpo_id,ur_id,clc_id)
                INNER JOIN ".BD_CTRL_2025.".scp_Contable using (gpo_id,ur_id,clc_id)
            INNER JOIN ".BD_CTRL_2025.".cat_BancoDocumentos using (gpo_id,ur_id,bdo_id)
                WHERE ant.ecl_id=16 
            AND bdo_estadoMinistrado != 3 
            ) AS doc ON doc.gpo_id =ldc.gpo_id and doc.ur_id=ldc.ur_id and doc.clc_id=ldc.clc_id and doc.bdo_id=ldc.bdo_id
            INNER JOIN 
                ".BD_CTRL_2025.".cat_TipoClc AS tc ON tc.tcl_id = c.tcl_id
                    INNER JOIN
                ".BD_CTRL_2025.".cat_SubtipoClc AS stc ON stc.stc_id = c.stc_id
                    INNER JOIN
                ".BD_CTRL_2025.".cat_UnidadResponsable AS ur on ur.gpo_id=c.gpo_id and ur.ur_id=c.ur_id
                    INNER JOIN
                ".BD_CTRL_2025.".enc_ClaveFinanciamiento AS cvf USING (ecf_id)
                    INNER JOIN
                ".BD_TESORERIA.".vin_SefinClaveFinanciamiento AS v USING (ecf_id)
                    INNER JOIN
                ".BD_TESORERIA.".ban_CuentaBancariaSefin AS sf USING (cbs_id)
                    INNER JOIN
                ".BD_TESORERIA.".cat_InstitucionBancaria AS ib USING (iba_id)
                    INNER JOIN
                ".BD_CTRL_2025.".cat_BancosUr AS br ON br.bur_id = c.bur_id
            WHERE
                l.lot_ejercicio = 2025 AND l.elo_id = 8
                    AND c.gpo_id=9 AND c.ur_id=01 #AND ib.iba_id=2
                    AND ant.ecl_id = 16 AND ant.ejr_id_clc=2025
                    AND tlo_id=4 AND ldc.bur_id!=0
                    AND (lot_fechaPago BETWEEN :fmin AND :fmax  )
                    AND l.lot_ejr= :ejer
            GROUP BY c.gpo_id , c.ur_id , c.clc_id,doc.bdo_id,doc.afe_id, doc.importe, v.cbs_id, lote, l.lot_FechaGeneracion;";

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
                'afe_id' => $row['afe_id'],
                'bdo_id' => $row['bdo_id'],
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
