<?php
//Hazael Hernandez Febrero 2018
function asientoDetalle($gpo_id, $ur_id, $periodo, $pesoExcelMaestro = 0)
{
    $params = array();
    $sql = "
        SELECT m.gpo_id, m.ur_id, m.ue_id, pot_id, m.prf_id, msc_id, pma_id AS pol_numero, pdx_id AS dpo_numero,
            ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id,
            pdx_debe AS dpo_debe, pdx_haber AS dpo_haber, nat_id AS dpo_naturaleza,
            pdx_concepto AS dpo_concepto, rfc_id, pdx_referencia AS dpo_referencia, pdx_fechaReferencia AS dpo_fechaReferencia, pdx_clc_id_origen AS dpo_vinculacion,
            cfi_clave, tdf_id, adf_id, epl_id, mmc_id,
            '' AS usr_idModificado, '' AS usr_fechaModificado, u.usr_usuario AS usr_id, x.usr_fecha,
            CONCAT(m.gpo_id, m.ur_id, m.ue_id, pot_id, m.prf_id, msc_id, pma_id, pdx_id) AS id
        FROM " . BD_CTRL . ".cg_PolizaMaestro m
            INNER JOIN " . BD_CTRL . ".cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
            LEFT JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento USING( ecf_id )
            LEFT JOIN " . BD_SINPRES .".cat_ClaveFinanciamiento" . APP_EJERCICIO . " USING( cff_id,fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
            LEFT JOIN " . BD_CTRL . ".cat_Usuario u ON u.usr_id = x.usr_id
        WHERE m.gpo_id = :gpo_id AND m.ur_id = :ur_id $periodo
        LIMIT 0, 1000000000
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

    $pesoExcelDetalle = calcularPeso($numRegistros, $numColumnas);

    ini_set('memory_limit', ($pesoExcelMaestro + $pesoExcelDetalle) . 'M');

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
            'dpo_numero' => $rowAsiento['dpo_numero'],
            'ger_id' => $rowAsiento['ger_id'],
            'cgp_id' => $rowAsiento['cgp_id'],
            'rub_id' => $rowAsiento['rub_id'],
            'cgcu_id' => $rowAsiento['cgcu_id'],
            'cgsc_id' => $rowAsiento['cgsc_id'],
            'cta_gpo_id' => $rowAsiento['cta_gpo_id'],
            'cta_ur_id' => $rowAsiento['cta_ur_id'],
            'cta_ue_id' => $rowAsiento['cta_ue_id'],
            'cgc_id' => $rowAsiento['cgc_id'],
            'cta_id' => $rowAsiento['cta_id'],
            'dpo_debe' => $rowAsiento['dpo_debe'],
            'dpo_haber' => $rowAsiento['dpo_haber'],
            'dpo_naturaleza' => $rowAsiento['dpo_naturaleza'],
            'dpo_concepto' => $rowAsiento['dpo_concepto'],
            'rfc_id' => $rowAsiento['rfc_id'],
            'dpo_referencia' => $rowAsiento['dpo_referencia'],
            'dpo_fechaReferencia' => $rowAsiento['dpo_fechaReferencia'],
            'dpo_vinculacion' => $rowAsiento['dpo_vinculacion'],
            'cfi_clave' => $rowAsiento['cfi_clave'],
            'tdf_id' => $rowAsiento['tdf_id'],
            'adf_id' => $rowAsiento['adf_id'],
            'epl_id' => $rowAsiento['epl_id'],
            'mmc_id' => $rowAsiento['mmc_id'],
            'usr_idModificado' => $rowAsiento['usr_idModificado'],
            'usr_fechaModificado' => $rowAsiento['usr_fechaModificado'],
            'usr_id' => $rowAsiento['usr_id'],
            'usr_fecha' => $rowAsiento['usr_fecha'],
        );
    }

    return $datos;

}
