<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/../../compararCadenas.php';
require_once dirname(__FILE__) . '/../../redondear.php';

function vinculacionPorClc($clcs, $mes, $gpo, $ur)
{
    $ejercicioActual = $_SESSION[_S]['ejercicioActual'];
    $sql = "
        SELECT scp.*, scg.*
        FROM
        (
        SELECT gpo_id, ur_id, tcl_id, clc_id, LEFT(MAX(ter_nombre), 1) AS ori, MAX(cfi_clave) AS cf, bur_id AS ci, iba_nombre AS nb,
                        clc_concepto AS con, ecl_id AS edo, mpc_nombre AS mp, (clc_percepcion - clc_retencion) AS imp
                    FROM scp_Clc 
                        JOIN enc_ClaveFinanciamiento USING (ecf_id)
                        JOIN sinpres.cat_ClaveFinanciamiento" . $ejercicioActual . " USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                        INNER JOIN sinpres.cat_SuborigenRecurso" . $ejercicioActual . " USING (cff_id, fte_id, sft_id, ori_id, sor_id)
                        JOIN sinpres.cat_TipoRecurso USING (ter_id)
                        JOIN cat_ModoPagoClc USING (mpc_id)
                        JOIN (
                            SELECT bur_id,iba_nombre
                            FROM cat_BancosUr
                            JOIN cat_InstitucionesBancarias USING (iba_id)
                        ) bancosUr USING (bur_id)
                        LEFT JOIN scp_Presupuestal p USING(gpo_id, ur_id, clc_id)
                    WHERE (gpo_id = $gpo AND ur_id = $ur AND ecl_id >= 2 AND MONTH(clc_fechaEnvio) = $mes)
                    GROUP BY gpo_id, ur_id, clc_id
                    ORDER BY clc_id, gpo_id, ur_id
        ) AS scp
        JOIN
        (
            SELECT gpo_id, ur_id, MIN(pdx_clc_id_origen) AS clc_id, CONCAT(pot_id, '-', prf_id, pma_id) AS poliza,
                pma_debe AS importe, pma_fecha, epl_id, mmc_id, tmc_id, apg_id
            FROM cg_PolizaMaestro
                INNER JOIN cg_PolizaDetalleAuxiliar USING(gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id)
            WHERE gpo_id = $gpo AND ur_id = $ur
                AND pdx_clc_id_origen IN ($clcs)
            GROUP BY gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id
        UNION ALL
            SELECT gpo_id, ur_id, MIN(da.pdx_clc_id_origen) AS clc_id, CONCAT(pot_id, '-', prf_id, pma_id) AS poliza,
                pma_debe AS importe, pma_fecha, epl_id, mmc_id, tmc_id, apg_id
            FROM cg_PolizaMaestro
                INNER JOIN cg_PolizaDetalleAuxiliar USING(gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id)
                INNER JOIN (
                    SELECT pdx_gpo_id_origen, pdx_ur_id_origen, pdx_cmp_id_origen, pdx_clc_id_origen
                    FROM cg_PolizaMaestro
                        INNER JOIN cg_PolizaDetalleAuxiliar USING(gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id)
                    WHERE gpo_id = $gpo AND ur_id = $ur AND pdx_clc_id_origen IN ($clcs) AND mmc_id = 'E' AND tmc_id = 'A'
                ) da USING(pdx_gpo_id_origen, pdx_ur_id_origen, pdx_cmp_id_origen)
            WHERE gpo_id = $gpo AND ur_id = $ur AND mmc_id = 'C'
            GROUP BY gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id
        ) AS scg USING(gpo_id, ur_id, clc_id)
        ORDER BY scp.clc_id, pma_fecha, poliza;
    ";
    $consultar = new AccesoDatos();
    $consultar->conectar();
    $consultar->setSql($sql);

    $consultar->consultar();

    $result = array();
    $datos = array();
    $x = 0;

    while ($rowData = $consultar->extraer()) {
        $result[$x] = array(
            'id' => $rowData['id'],
            'gpo_id' => $rowData['gpo_id'],
            'ur_id' => $rowData['ur_id'],
            'tcl_id' => $rowData['tcl_id'],
            'clc_id' => $rowData['clc_id'],
            'ori' => $rowData['ori'],
            'cf' => $rowData['cf'],
            'ci' => $rowData['ci'],
            'nb' => $rowData['nb'],
            'con' => $rowData['con'],
            'edo' => $rowData['edo'],
            'mp' => $rowData['mp'],
            'imp' => $rowData['imp'],
            'poliza' => $rowData['poliza'],
            'pma_fecha' => $rowData['pma_fecha'],
            'importe' => number_format($rowData['importe'], 2),
            'epl_id' => $rowData['epl_id'],
            'mmc_id' => $rowData['mmc_id'],
            'tmc_id' => $rowData['tmc_id'],
            'apg_id' => $rowData['apg_id'],

        );
        $x++;
    }

    $consultar->desconectar();
    $total_reg = count($result);
    for ($i = 0; $i < $total_reg; $i++) {
        if ($i == 0) {
            $datos[$i] = array(
                'id' => $result[$i]['id'],
                'gpo_id' => $result[$i]['gpo_id'],
                'ur_id' => $result[$i]['ur_id'],
                'tcl_id' => $result[$i]['tcl_id'],
                'clc_id' => $result[$i]['clc_id'],
                'ori' => $result[$i]['ori'],
                'cf' => $result[$i]['cf'],
                'ci' => $result[$i]['ci'],
                'nb' => $result[$i]['nb'],
                'con' => $result[$i]['con'],
                'edo' => $result[$i]['edo'],
                'mp' => $result[$i]['mp'],
                'imp' => $result[$i]['imp'],
                'poliza' => $result[$i]['poliza'],
                'pma_fecha' => $result[$i]['pma_fecha'],
                'importe' => $result[$i]['importe'],
                'epl_id' => $result[$i]['epl_id'],
                'mmc_id' => $result[$i]['mmc_id'],
                'tmc_id' => $result[$i]['tmc_id'],
                'apg_id' => $result[$i]['apg_id'],

            );
        } else {
            $datos[$i] = array(
                'id' => $result[$i]['id'],
                'gpo_id' => $result[$i]['gpo_id'],
                'ur_id' => $result[$i]['ur_id'],
                'tcl_id' => $result[$i]['tcl_id'],
                'clc_id' => $result[$i]['clc_id'],
                'ori' => $result[$i]['ori'],
                'cf' => $result[$i]['cf'],
                'ci' => $result[$i]['ci'],
                'nb' => $result[$i]['nb'],
                'con' => $result[$i]['con'],
                'edo' => $result[$i]['edo'],
                'mp' => $result[$i]['mp'],
                'imp' => $result[$i]['imp'],
                'poliza' => $result[$i]['poliza'],
                'pma_fecha' => $result[$i]['pma_fecha'],
                'importe' => $result[$i]['importe'],
                'epl_id' => $result[$i]['epl_id'],
                'mmc_id' => $result[$i]['mmc_id'],
                'tmc_id' => $result[$i]['tmc_id'],
                'apg_id' => $result[$i]['apg_id'],

            );
        }
    }

    return $datos;
}
