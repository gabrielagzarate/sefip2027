<?php
//Hazael Hernandez Febrero 2018
function asientoMayor($gpo_id, $ur_id, $periodo)
{
    $params = array();
    $sql = "
        SELECT m.gpo_id, m.ur_id, m.ue_id, pot_id, m.prf_id, msc_id, pma_id AS pol_numero,
            pma_debe AS pol_debe, pma_haber AS pol_haber,
            pma_concepto AS pol_concepto, pma_fecha AS pol_fecha, '' AS pol_vinculacion, '' AS pol_tipo, '' AS pol_clc,
            mmc_id, epl_id, apg_id, MAX( cfi_clave) AS cfi_clave, '' AS ore_idObra,
            '' AS usr_idModificado, '' AS usr_fechaModificado, u.usr_usuario AS usr_id, m.usr_fecha,
            CONCAT(m.gpo_id, m.ur_id, m.ue_id, pot_id, m.prf_id, msc_id, pma_id) AS id
        FROM " . BD_CTRL . ".cg_PolizaMaestro m
            INNER JOIN " . BD_CTRL . ".cg_PolizaDetalleAuxiliar USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
            LEFT JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento USING( ecf_id )
            LEFT JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " USING( cff_id,fte_id, sft_id, ori_id, sor_id,  cfi_id, cfi_anio )
            LEFT JOIN " . BD_CTRL . ".cat_Usuario u ON u.usr_id = m.usr_id
        WHERE m.gpo_id = :gpo_id AND m.ur_id = :ur_id $periodo
        GROUP BY gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id
        ORDER BY gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id
        LIMIT 0, 1000000000;
        ";
    $params[':gpo_id'] = $gpo_id;
    $params[':ur_id'] = $ur_id;

    $asiento = new AccesoDatos();
    $asiento->conectar();
    $asiento->setSql($sql);
    $asiento->consultar($params);
    $asiento->desconectar();
    $numRegistros = $asiento->getNumeroRegistros();
    $numColumnas = $asiento->getNumeroCampos();

    while ($rowAsiento = $asiento->extraer()) {
        $datos[$rowAsiento['id']] = array(
            'id' => $rowAsiento['id'],
            'gpo_id' => $rowAsiento['gpo_id'],
            'ur_id' => $rowAsiento['ur_id'],
            'ue_id' => $rowAsiento['ue_id'],
            'pot_id' => $rowAsiento['pot_id'],
            'prf_id' => $rowAsiento['prf_id'],
            'msc_id' => $rowAsiento['msc_id'],
            'pol_numero' => $rowAsiento['pol_numero'],
            'pol_fecha' => $rowAsiento['pol_fecha'],
            'pol_debe' => $rowAsiento['pol_debe'],
            'pol_haber' => $rowAsiento['pol_haber'],
            'pol_concepto' => $rowAsiento['pol_concepto'],
            'pol_vinculacion' => $rowAsiento['pol_vinculacion'],
            'pol_tipo' => $rowAsiento['pol_tipo'],
            'pol_clc' => $rowAsiento['pol_clc'],
            'mmc_id' => $rowAsiento['mmc_id'],
            'epl_id' => $rowAsiento['epl_id'],
            'apg_id' => $rowAsiento['apg_id'],
            'cfi_clave' => $rowAsiento['cfi_clave'],
            'ore_idObra' => $rowAsiento['ore_idObra'],
            'usr_idModificado' => $rowAsiento['usr_idModificado'],
            'usr_fechaModificado' => $rowAsiento['usr_fechaModificado'],
            'usr_id' => $rowAsiento['usr_id'],
            'usr_fecha' => $rowAsiento['usr_fecha'],
        );
    }

    return $datos;

}
