<?php
/*
Función donde se obtiene información para la formación de los estados financieros y cuenta publica.
Devuelve un array.

  // PARÁMETROS
gpo_id, ur_id, ue_id : La clasificación administrativa que limita la información a mostrar


 */
/* ---------------------------------------------------  CONSOLIDACION  ---------------------------------------------------------------------  */

function SQLReportesLDF($ent_id, $reportes)
{
    /*  echo $ent_id;
    echo " ----- ";
    echo $reportes; */

    $sql = '
        SELECT rep_id, rep_observacion,
            IFNULL(MAX(Reporte1),1) AS Reporte1, IFNULL(MAX(edo_trimestre1),"C") AS edo_trimestre1,
            IFNULL(MAX(edo_trimestreC1),"C") AS edo_trimestreC1,
            IFNULL(MAX(Reporte2),1) AS Reporte2, IFNULL(MAX(edo_trimestre2),"C") AS edo_trimestre2,
            IFNULL(MAX(edo_trimestreC2),"C") AS edo_trimestreC2,
            IFNULL(MAX(Reporte3),1) AS Reporte3, IFNULL(MAX(edo_trimestre3),"C") AS edo_trimestre3,
            IFNULL(MAX(edo_trimestreC3),"C") AS edo_trimestreC3,
            IFNULL(MAX(Reporte4),1) AS Reporte4, IFNULL(MAX(edo_trimestre4),"C") AS edo_trimestre4,
            IFNULL(MAX(edo_trimestreC4),"C") AS edo_trimestreC4
        FROM (
            SELECT er.rep_id,
                CASE msc_trimestre WHEN  1 THEN er.ere_id END AS Reporte1,
                CASE msc_trimestre WHEN  1 THEN (edoTtri.ems_id) END AS edo_trimestre1,
                CASE msc_trimestre WHEN  1 THEN (msc.ems_id) END AS edo_trimestreC1,
                CASE msc_trimestre WHEN  2 THEN er.ere_id END AS Reporte2,
                CASE msc_trimestre WHEN  2 THEN (edoTtri.ems_id) END AS edo_trimestre2,
                CASE msc_trimestre WHEN  2 THEN (msc.ems_id) END AS edo_trimestreC2,
                CASE msc_trimestre WHEN  3 THEN er.ere_id END AS Reporte3,
                CASE msc_trimestre WHEN  3 THEN (edoTtri.ems_id) END AS edo_trimestre3,
                CASE msc_trimestre WHEN  3 THEN (msc.ems_id) END AS edo_trimestreC3,
                CASE msc_trimestre WHEN  4 THEN er.ere_id END AS Reporte4,
                CASE msc_trimestre WHEN  4 THEN (edoTtri.ems_id ) END AS edo_trimestre4,
                CASE msc_trimestre WHEN  4 THEN (msc.ems_id) END AS edo_trimestreC4
            FROM ldf.ldf_EstadoReporte er
                INNER JOIN ldf.vin_EntePublicoLDF vin using(ejr_id, ent_id)
                LEFT JOIN ldf.ldf_EstadoTrimestre edoTtri USING(ejr_id, msc_trimestre)
                LEFT JOIN(
                    SELECT m.ems_id, v.ent_id, c.msc_trimestre
                    FROM ctrl2023.cg_MesContable m
                        INNER JOIN ctrl2023.cat_MesContable c USING(msc_id)
                        INNER JOIN ldf.vin_EntePublicoLDF v USING(gpo_id, ur_id)
                    WHERE v.ejr_id = 2022
                        AND m.ems_id = "A"
                    GROUP BY msc_trimestre, ent_id
                )msc USING(ent_id, msc_trimestre)
                WHERE vin.ejr_id = 2022
                    AND er.ent_id = ' . $ent_id . '
                    AND rep_id IN (' . $reportes . ')
        )r
            INNER JOIN ldf.cat_TipoReporte t USING(rep_id)
        GROUP BY rep_id
    ';
    /*  echo '<pre>';
    echo $sql;
    echo '</pre>';
    die(); */

    return $sql;
}
