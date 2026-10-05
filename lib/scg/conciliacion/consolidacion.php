<?php

/*
Función donde se obtiene información para la formación de los estados financieros y cuenta publica.
Devuelve un array.

// PARÁMETROS
gpo_id, ur_id, ue_id : La clasificación administrativa que limita la información a mostrar

 */
/* ---------------------------------------------------  CONSOLIDACION  ---------------------------------------------------------------------  */
function consolidacion($gpo_id, $ur_id, $ue, $mesInicial, $mesFinal, $genero, $tipoReporte, $ejercicio, $chkEnMiles, $grupos, $excluir, $right, $incCta = 0)
{

    if ($tipoReporte == '') {
        $tipoReporte = 1;
    }
    if (is_null($chkEnMiles)) {
        $chkEnMiles = 0;
    }

    $params = [];

    $ejercicio = ($ejercicio != '') ? 'ctrl' . $ejercicio . '.' : '';
    $ejercicioActual = $_SESSION[_S]['ejercicioActual'];
    $ejercicioAnterior = $ejercicioActual - 1;

    $ejerAnterior = 'ctrl' . $ejercicioAnterior . '.';

    $tablas = '';

    if ($right == 1) {
        $orden = 'DESC';
        // Marcos: 26/ene/2018
        // En el 2018 se quitó ésta tabla... pero para consultar 2017 la necesitamos
        if ($ejercicio == 'ctrl2017.') {
            $tablas .= 'RIGHT JOIN ' . $ejercicio . 'cg_CuentaMayor cm USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id ) ';
        }

        $tablas .= '
            RIGHT JOIN ' . $ejercicio . 'cat_CgSubcuenta sc USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
            RIGHT JOIN ' . $ejercicio . 'cat_CgCuenta USING( ger_id, cgp_id, rub_id, cgcu_id )
            RIGHT JOIN ' . $ejercicio . 'cat_Rubro USING( ger_id, cgp_id, rub_id )
            RIGHT JOIN ' . $ejercicio . 'cat_CgGrupo USING( ger_id, cgp_id )
            RIGHT JOIN ' . $ejercicio . 'cat_Genero USING( ger_id )
        ';
    } else {
        $orden = 'ASC';
        // Marcos: 26/ene/2018
        // En el 2018 se quitó ésta tabla... pero para consultar 2017 la necesitamos
        $tablas .= '
            INNER JOIN ' . $ejercicio . 'cat_CgSubcuenta sc USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
            INNER JOIN ' . $ejercicio . 'cat_CgCuenta USING( ger_id, cgp_id, rub_id, cgcu_id )
            INNER JOIN ' . $ejercicio . 'cat_Rubro USING( ger_id, cgp_id, rub_id )
            INNER JOIN ' . $ejercicio . 'cat_CgGrupo USING( ger_id, cgp_id )
            INNER JOIN ' . $ejercicio . 'cat_Genero USING( ger_id )
        ';
    }

    $otroWhere = '';

    if ($tipoReporte == 3 && $_SESSION[_S]['nvj_id'] >= 5) {
        // Si limitaron por grupo o excluyeron alguna UR
        if ($grupos != '') {
            $otroWhere .= '  WHERE ' . ($ejercicio == $ejerAnterior ? 'v.gpo_id_' . $ejercicioAnterior : 'gpo_id') . ' IN ( ' . rtrim($grupos) . ' ) AND ';

            // Si excluyeron a alguna UR
            if ($excluir != '') {
                $otroWhere .= ' ' . ($grupos != '' ? '' : ' WHERE ') . ' CONCAT( ' . ($ejercicio == 'ctrl' . $ejercicioAnterior . '.' ? 'v.gpo_id_' . $ejercicioAnterior . '' : 'gpo_id') . ',' . ($ejercicio != '' ? 'v.ur_id_' . $ejercicioAnterior : 'ur_id') . ' ) NOT IN ( ' . rtrim($excluir) . ' ) AND ';
            }

            // Si no usaron las campos especiales, es un where estandar
        }
    }

    // Si piden las cantidades en miles de pesos
    if ($chkEnMiles == 1) {
        $divisor = 1000;
    } else {
        $divisor = 1;
    }

    $tipoRep = '';
    if ($tipoReporte == 2 && $_SESSION[_S]['nvj_id'] < 6) {
        $tipoRep = ' AND t.usr_id = :usr';
        $params[':usr'] = $_SESSION[_S]['usr_id'];
    }

    $saldo = new AccesoDatos(); //variable de konexion para los saldos iniciales

    $saldo->conectar();

    $registros = [];
    if ($gpo_id != '' && $ur_id != '') {
        $sql = '';
        switch ($ejercicio) {

            case $ejerAnterior:
                $sql = '
                    SELECT ger_id, cgp_id, rub_id, cgcu_id, cgsc_id,
                    ger_id, ger_nombre, cgp_cuenta, cgp_nombre, rub_cuenta, rub_nombre, cgcu_cuenta, cgcu_nombre, cgsc_cuenta, cgsc_nombre, dpo_debe, dpo_haber, dpo_naturaleza, sc.nat_id AS cgm_naturaleza, 5
                    FROM (
                        SELECT ger_id, cgp_id, rub_id, cgcu_id, cgsc_id , SUM( pdx_debe ) AS dpo_debe, SUM( pdx_haber ) AS dpo_haber,  MAX(nat_id) AS dpo_naturaleza
                        FROM ctrl' . $ejercicioAnterior . '.cg_PolizaDetalleAuxiliar d
                        INNER JOIN ctrl' . $ejercicioAnterior . '.cg_PolizaMaestro USING(gpo_id,ur_id,ue_id,msc_id,pot_id,prf_id,pma_id)
                            LEFT JOIN ' . BD_SINPRES . '.vin_UnidadResponsable_' . $ejercicioAnterior . '_' . $ejercicioActual . ' v ON v.gpo_id_' . $ejercicioAnterior . ' = d.gpo_id AND v.ur_id_' . $ejercicioAnterior . ' = d.ur_id
                            ' . ($ue != '' ? 'LEFT JOIN ' . BD_SINPRES . '.vin_UnidadEjecutora_' . $ejercicioAnterior . '_' . $ejercicioActual . ' u ON u.gpo_id_' . $ejercicioAnterior . ' = d.gpo_id AND u.ur_id_' . $ejercicioAnterior . ' = d.ur_id AND u.ue_id_' . $ejercicioAnterior . ' = d.ue_id ' : '') . '
                            ' . ($tipoReporte == 2 ? ($_SESSION[_S]['nvj_id'] < 6 ? ' INNER JOIN ctrl' . $ejercicioAnterior . '.cat_CargaTrabajo t ON t.gpo_id = v.gpo_id_' . $ejercicioActual . ' AND t.ur_id = v.ur_id_' . $ejercicioActual
                        : '') . ' WHERE ' : (($tipoReporte == 3) ? ($_SESSION[_S]['nvj_id'] < 5 ?
                        ' WHERE ' : $otroWhere) : '
                        WHERE v.gpo_id_' . $ejercicioAnterior . " = $gpo_id AND v.ur_id_" . $ejercicioAnterior . " = $ur_id " . ($ue != '' ? ' AND u.ue_id_' . $ejercicioAnterior . ' = ' . $ue : '') . '
                            AND ')) . '
                            ' . ' msc_id < 13 ' . "
                            AND ger_id = $genero
                            AND epl_id NOT IN ( 'C', 'H' )
                            $tipoRep
                        GROUP BY ger_id, cgp_id, rub_id, cgcu_id, cgsc_id
                    )p
                    " . $tablas . "
                    WHERE ger_id=$genero
                    ORDER BY cgsc_cuenta " . $orden . '
                ';

                break;
            default:
                $sql = '
                    SELECT ger_id, cgp_id, rub_id, cgcu_id, cgsc_id,
                    ger_id, ger_nombre, cgp_cuenta, cgp_nombre, rub_cuenta, rub_nombre, cgcu_cuenta, cgcu_nombre, cgsc_cuenta, cgsc_nombre, dpo_debe, dpo_haber, dpo_naturaleza , sc.nat_id AS cgm_naturaleza, 5
                    FROM (
                        SELECT ger_id, cgp_id, rub_id, cgcu_id, cgsc_id , SUM( pdx_debe ) AS dpo_debe, SUM( pdx_haber ) AS dpo_haber, MAX(nat_id) AS dpo_naturaleza
                        FROM ' . $ejercicio . 'cg_PolizaDetalleAuxiliar d
                            INNER JOIN ' . $ejercicio . 'cg_PolizaMaestro USING(gpo_id,ur_id,ue_id,msc_id,pot_id,prf_id,pma_id)
                            ' . ($tipoReporte == 2 ? ($_SESSION[_S]['nvj_id'] < 6 ? ' INNER JOIN ' . $ejercicio . 'cat_CargaTrabajo t USING(gpo_id,ur_id)
                        ' : '') . ' WHERE ' : (($tipoReporte == 3) ? ($_SESSION[_S]['nvj_id'] < 5 ?
                        ' WHERE ' : $otroWhere) : "
                        WHERE gpo_id = $gpo_id AND ur_id = $ur_id " . ($ue != '' ? ' AND d.ue_id = ' . $ue . '' : '') . '
                            AND ')) . '
                            ' . ($mesFinal < 0 ? "msc_id < $mesInicial" : ($mesInicial == $mesFinal ? "msc_id = $mesInicial" : "( msc_id BETWEEN $mesInicial AND $mesFinal )")) . "
                            AND ger_id = $genero
                            AND epl_id NOT IN ( 'C', 'H' )
                            $tipoRep
                        GROUP BY ger_id, cgp_id, rub_id, cgcu_id, cgsc_id
                    )p
                    " . $tablas . "
                    WHERE ger_id=$genero
                    ORDER BY cgsc_cuenta " . $orden . '
                ';
                break;
        }

        $saldo->setSql($sql);

        $saldo->consultar($params);

        $saldo->desconectar();

        $cuentaAnterior = '';
        $naturalezaAnterior = '';
        $sumaDebe = 0;
        $sumaHaber = 0;
        $registro = '';

        if ($saldo->getNumeroRegistros() != 0) {
            while ($rowRegistro = $saldo->extraer()) {

                $key = $rowRegistro['ger_id'] . $rowRegistro['cgp_id'] . $rowRegistro['rub_id'] . $rowRegistro['cgcu_id'] . $rowRegistro['cgsc_id'];
                $key4 = $rowRegistro['ger_id'] . $rowRegistro['cgp_id'] . $rowRegistro['rub_id'] . $rowRegistro['cgcu_id'];
                $key3 = $rowRegistro['ger_id'] . $rowRegistro['cgp_id'] . $rowRegistro['rub_id'];
                $key2 = $rowRegistro['ger_id'] . $rowRegistro['cgp_id'];
                $key1 = $rowRegistro['ger_id'];

                // DETALLE DEL QUINTO NIVEL

                $registros[$key]['cuenta'] = $rowRegistro['cgsc_cuenta'];
                // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                if ($incCta == '1') {
                    $registros[$key]['nombre'] = $rowRegistro['cgsc_cuenta'] . ' ' . $rowRegistro['cgsc_nombre'];
                } else {
                    $registros[$key]['nombre'] = $rowRegistro['cgsc_nombre'];
                }
                $registros[$key]['debe'] = $rowRegistro['dpo_debe'];
                $registros[$key]['haber'] = $rowRegistro['dpo_haber'];
                $registros[$key]['naturalezaDetalle'] = $rowRegistro['dpo_naturaleza'];
                $registros[$key]['naturalezaCuenta'] = $rowRegistro['cgm_naturaleza'];
                $registros[$key]['ger_id'] = $rowRegistro['ger_id'];
                $registros[$key]['ger_cuenta'] = $rowRegistro['ger_id'];
                // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                if ($incCta == '1') {
                    $registros[$key]['ger_nombre'] = $rowRegistro['ger_id'] . ' ' . $rowRegistro['ger_nombre'];
                } else {
                    $registros[$key]['ger_nombre'] = $rowRegistro['ger_nombre'];
                }
                $registros[$key]['cgp_id'] = $rowRegistro['cgp_id'];
                $registros[$key]['cgp_cuenta'] = $rowRegistro['cgp_cuenta'];
                // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                if ($incCta == '1') {
                    $registros[$key]['cgp_nombre'] = $rowRegistro['cgp_cuenta'] . ' ' . $rowRegistro['cgp_nombre'];
                } else {
                    $registros[$key]['cgp_nombre'] = $rowRegistro['cgp_nombre'];
                }
                $registros[$key]['rub_id'] = $rowRegistro['rub_id'];
                $registros[$key]['rub_cuenta'] = $rowRegistro['rub_cuenta'];
                // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                if ($incCta == '1') {
                    $registros[$key]['rub_nombre'] = $rowRegistro['rub_cuenta'] . ' ' . $rowRegistro['rub_nombre'];
                } else {
                    $registros[$key]['rub_nombre'] = $rowRegistro['rub_nombre'];
                }
                $registros[$key]['cgcu_id'] = $rowRegistro['cgcu_id'];
                $registros[$key]['cgcu_cuenta'] = $rowRegistro['cgcu_cuenta'];
                // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                if ($incCta == '1') {
                    $registros[$key]['cgcu_nombre'] = $rowRegistro['cgcu_cuenta'] . ' ' . $rowRegistro['cgcu_nombre'];
                } else {
                    $registros[$key]['cgcu_nombre'] = $rowRegistro['cgcu_nombre'];
                }
                $registros[$key]['cgsc_id'] = $rowRegistro['cgsc_id'];
                $registros[$key]['cgsc_cuenta'] = $rowRegistro['cgsc_cuenta'];
                // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                if ($incCta == '1') {
                    $registros[$key]['cgsc_nombre'] = $rowRegistro['cgsc_cuenta'] . ' ' . $rowRegistro['cgsc_nombre'];
                } else {
                    $registros[$key]['cgsc_nombre'] = $rowRegistro['cgsc_nombre'];
                }
                $registros[$key]['nivel'] = 5;

                //SI NO EXISTE EL CUARTO NIVEL DEL DETALLE ACTUAL LO CREAMOS
                if (!isset($registros[$key4])) {
                    $registros[$key4]['cuenta'] = $rowRegistro['cgcu_cuenta'];
                    // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                    if ($incCta == '1') {
                        $registros[$key4]['nombre'] = $rowRegistro['cgcu_cuenta'] . ' ' . $rowRegistro['cgcu_nombre'];
                    } else {
                        $registros[$key4]['nombre'] = $rowRegistro['cgcu_nombre'];
                    }
                    $registros[$key4]['debe'] = 0;
                    $registros[$key4]['haber'] = 0;
                    $registros[$key4]['naturalezaDetalle'] = (($rowRegistro['dpo_naturaleza'] != '') ? $rowRegistro['dpo_naturaleza'] : $registros[$key]['naturalezaDetalle']);
                    $registros[$key4]['naturalezaCuenta'] = (($rowRegistro['cgm_naturaleza'] != '') ? $rowRegistro['cgm_naturaleza'] : $registros[$key]['naturalezaCuenta']);
                    $registros[$key4]['ger_id'] = $rowRegistro['ger_id'];
                    $registros[$key4]['ger_cuenta'] = $rowRegistro['ger_id'];
                    // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                    if ($incCta == '1') {
                        $registros[$key4]['ger_nombre'] = $rowRegistro['ger_id'] . ' ' . $rowRegistro['ger_nombre'];
                    } else {
                        $registros[$key4]['ger_nombre'] = $rowRegistro['ger_nombre'];
                    }
                    $registros[$key4]['cgp_id'] = $rowRegistro['cgp_id'];
                    $registros[$key4]['cgp_cuenta'] = $rowRegistro['cgp_cuenta'];
                    // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                    if ($incCta == '1') {
                        $registros[$key4]['cgp_nombre'] = $rowRegistro['cgp_cuenta'] . ' ' . $rowRegistro['cgp_nombre'];
                    } else {
                        $registros[$key4]['cgp_nombre'] = $rowRegistro['cgp_nombre'];
                    }
                    $registros[$key4]['rub_id'] = $rowRegistro['rub_id'];
                    $registros[$key4]['rub_cuenta'] = $rowRegistro['rub_cuenta'];
                    // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                    if ($incCta == '1') {
                        $registros[$key4]['rub_nombre'] = $rowRegistro['rub_cuenta'] . ' ' . $rowRegistro['rub_nombre'];
                    } else {
                        $registros[$key4]['rub_nombre'] = $rowRegistro['rub_nombre'];
                    }
                    $registros[$key4]['cgcu_id'] = $rowRegistro['cgcu_id'];
                    $registros[$key4]['cgcu_cuenta'] = $rowRegistro['cgcu_cuenta'];
                    // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                    if ($incCta == '1') {
                        $registros[$key4]['cgcu_nombre'] = $rowRegistro['cgcu_cuenta'] . ' ' . $rowRegistro['cgcu_nombre'];
                    } else {
                        $registros[$key4]['cgcu_nombre'] = $rowRegistro['cgcu_nombre'];
                    }
                    $registros[$key4]['nivel'] = 4;

                }
                //SI NO EXISTE EL TERCER NIVEL DEL DETALLE ACTUAL LO CREAMOS
                if (!isset($registros[$key3])) {
                    $registros[$key3]['cuenta'] = $rowRegistro['rub_cuenta'];
                    // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                    if ($incCta == '1') {
                        $registros[$key3]['nombre'] = $rowRegistro['rub_cuenta'] . ' ' . $rowRegistro['rub_nombre'];
                    } else {
                        $registros[$key3]['nombre'] = $rowRegistro['rub_nombre'];
                    }
                    $registros[$key3]['debe'] = 0;
                    $registros[$key3]['haber'] = 0;
                    $registros[$key3]['naturalezaDetalle'] = (($rowRegistro['dpo_naturaleza'] != '') ? $rowRegistro['dpo_naturaleza'] : $registros[$key4]['naturalezaDetalle']);
                    $registros[$key3]['naturalezaCuenta'] = (($rowRegistro['cgm_naturaleza'] != '') ? $rowRegistro['cgm_naturaleza'] : $registros[$key4]['naturalezaCuenta']);
                    $registros[$key3]['ger_id'] = $rowRegistro['ger_id'];
                    $registros[$key3]['ger_cuenta'] = $rowRegistro['ger_id'];
                    // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                    if ($incCta == '1') {
                        $registros[$key3]['ger_nombre'] = $rowRegistro['ger_id'] . ' ' . $rowRegistro['ger_nombre'];
                    } else {
                        $registros[$key3]['ger_nombre'] = $rowRegistro['ger_nombre'];
                    }
                    $registros[$key3]['cgp_id'] = $rowRegistro['cgp_id'];
                    $registros[$key3]['cgp_cuenta'] = $rowRegistro['cgp_cuenta'];
                    // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                    if ($incCta == '1') {
                        $registros[$key3]['cgp_nombre'] = $rowRegistro['cgp_cuenta'] . ' ' . $rowRegistro['cgp_nombre'];
                    } else {
                        $registros[$key3]['cgp_nombre'] = $rowRegistro['cgp_nombre'];
                    }
                    $registros[$key3]['rub_id'] = $rowRegistro['rub_id'];
                    $registros[$key3]['rub_cuenta'] = $rowRegistro['rub_cuenta'];
                    // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                    if ($incCta == '1') {
                        $registros[$key3]['rub_nombre'] = $rowRegistro['rub_cuenta'] . ' ' . $rowRegistro['rub_nombre'];
                    } else {
                        $registros[$key3]['rub_nombre'] = $rowRegistro['rub_nombre'];
                    }
                    $registros[$key3]['nivel'] = 3;

                }
                //SI NO EXISTE EL SEGUNDO NIVEL DEL DETALLE ACTUAL LO CREAMOS
                if (!isset($registros[$key2])) {
                    $registros[$key2]['cuenta'] = $rowRegistro['cgp_cuenta'];
                    // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                    if ($incCta == '1') {
                        $registros[$key2]['nombre'] = $rowRegistro['cgp_cuenta'] . ' ' . $rowRegistro['cgp_nombre'];
                    } else {
                        $registros[$key2]['nombre'] = $rowRegistro['cgp_nombre'];
                    }
                    $registros[$key2]['debe'] = 0;
                    $registros[$key2]['haber'] = 0;
                    $registros[$key2]['naturalezaDetalle'] = (($rowRegistro['dpo_naturaleza'] != '') ? $rowRegistro['dpo_naturaleza'] : $registros[$key3]['naturalezaDetalle']);
                    $registros[$key2]['naturalezaCuenta'] = (($rowRegistro['cgm_naturaleza'] != '') ? $rowRegistro['cgm_naturaleza'] : $registros[$key3]['naturalezaCuenta']);
                    $registros[$key2]['ger_id'] = $rowRegistro['ger_id'];
                    $registros[$key2]['ger_cuenta'] = $rowRegistro['ger_id'];
                    // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                    if ($incCta == '1') {
                        $registros[$key2]['ger_nombre'] = $rowRegistro['ger_id'] . ' ' . $rowRegistro['ger_nombre'];
                    } else {
                        $registros[$key2]['ger_nombre'] = $rowRegistro['ger_nombre'];
                    }
                    $registros[$key2]['cgp_id'] = $rowRegistro['cgp_id'];
                    $registros[$key2]['cgp_cuenta'] = $rowRegistro['cgp_cuenta'];
                    // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                    if ($incCta == '1') {
                        $registros[$key2]['cgp_nombre'] = $rowRegistro['cgp_cuenta'] . ' ' . $rowRegistro['cgp_nombre'];
                    } else {
                        $registros[$key2]['cgp_nombre'] = $rowRegistro['cgp_nombre'];
                    }
                    $registros[$key2]['nivel'] = 2;
                }
                //SI NO EXISTE EL PRIMER NIVEL DEL DETALLE ACTUAL LO CREAMOS
                if (!isset($registros[$key1])) {
                    $registros[$key1]['cuenta'] = $rowRegistro['ger_id'];
                    // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                    if ($incCta == '1') {
                        $registros[$key1]['nombre'] = $rowRegistro['ger_id'] . ' ' . $rowRegistro['ger_nombre'];
                    } else {
                        $registros[$key1]['nombre'] = $rowRegistro['ger_nombre'];
                    }
                    $registros[$key1]['debe'] = 0;
                    $registros[$key1]['haber'] = 0;
                    $registros[$key1]['naturalezaDetalle'] = (($rowRegistro['dpo_naturaleza'] != '') ? $rowRegistro['dpo_naturaleza'] : $registros[$key2]['naturalezaDetalle']);
                    $registros[$key1]['naturalezaCuenta'] = (($rowRegistro['cgm_naturaleza'] != '') ? $rowRegistro['cgm_naturaleza'] : $registros[$key2]['naturalezaCuenta']);
                    $registros[$key1]['ger_id'] = $rowRegistro['ger_id'];
                    $registros[$key1]['ger_cuenta'] = $rowRegistro['ger_id'];
                    // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
                    if ($incCta == '1') {
                        $registros[$key1]['ger_nombre'] = $rowRegistro['ger_id'] . ' ' . $rowRegistro['ger_nombre'];
                    } else {
                        $registros[$key1]['ger_nombre'] = $rowRegistro['ger_nombre'];
                    }
                    $registros[$key1]['nivel'] = 1;
                }

                //ACUMULAMOS LOS IMPORTES EN SUS RESPÉCTIVOS NIVELES SUPERIORES

                $registros[$key4]['debe'] += $rowRegistro['dpo_debe'];
                $registros[$key4]['haber'] += $rowRegistro['dpo_haber'];

                $registros[$key3]['debe'] += $rowRegistro['dpo_debe'];
                $registros[$key3]['haber'] += $rowRegistro['dpo_haber'];

                $registros[$key2]['debe'] += $rowRegistro['dpo_debe'];
                $registros[$key2]['haber'] += $rowRegistro['dpo_haber'];

                $registros[$key1]['debe'] += $rowRegistro['dpo_debe'];
                $registros[$key1]['haber'] += $rowRegistro['dpo_haber'];
            }
        }

    } else {
        $registros[0]['cuenta'] = '';
        $registros[0]['nombre'] = '';
        $registros[0]['debe'] = 0;
        $registros[0]['haber'] = 0;
        $registros[0]['naturalezaDetalle'] = '';
        $registros[0]['naturalezaCuenta'] = '';
        $registros[0]['ger_id'] = 0;
        $registros[0]['ger_cuenta'] = '';
        $registros[0]['ger_nombre'] = '';
        $registros[0]['cgp_id'] = 0;
        $registros[0]['cgp_cuenta'] = '';
        $registros[0]['cgp_nombre'] = '';
        $registros[0]['rub_id'] = 0;
        $registros[0]['rub_cuenta'] = '';
        $registros[0]['rub_nombre'] = '';
        $registros[0]['cgcu_id'] = 0;
        $registros[0]['cgcu_cuenta'] = '';
        $registros[0]['cgcu_nombre'] = '';
        $registros[0]['cgsc_id'] = 0;
        $registros[0]['cgsc_cuenta'] = '';
        $registros[0]['cgsc_nombre'] = '';
        $registros[0]['nivel'] = 0;
    }

    $datos = array_reverse($registros, true);

    return isset($datos) ? [$datos] : false;
}
/* ------------------------------------------------------------------------------------------------------------------------  */
//retorna un array de la balanza con flujos de movimientos
function balanzaConsolidacion($gpo_id, $ur_id, $ue, $mesInicial, $mesFinal, $genero, $tipoReporte, $ejercicio, $chkEnMiles, $grupos, $excluir, $ceros, $incCta = 0)
{
    if (is_null($chkEnMiles)) {
        $chkEnMiles = 0;
    }
    list($inicial) = consolidacion($gpo_id, $ur_id, $ue, $mesInicial, -1, $genero, $tipoReporte, $ejercicio, $chkEnMiles, $grupos, $excluir, $ceros);
    list($periodo) = consolidacion($gpo_id, $ur_id, $ue, $mesInicial, $mesFinal, $genero, $tipoReporte, $ejercicio, $chkEnMiles, $grupos, $excluir, $ceros);

    $keyInicio = key($inicial);
    $keyPeriodo = key($periodo);
    $final = [];

    while ($keyInicio || $keyPeriodo) {

        if (CompararCadenas((string) $keyInicio, (string) $keyPeriodo) == 0) {

            $final[$keyInicio]['cuenta'] = $inicial[$keyInicio]['cuenta'];
            // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
            if ($incCta == '1') {
                $final[$keyInicio]['nombre'] = $inicial[$keyInicio]['cuenta'] . ' ' . $inicial[$keyInicio]['nombre'];
            } else {
                $final[$keyInicio]['nombre'] = $inicial[$keyInicio]['nombre'];
            }
            $final[$keyInicio]['naturalezaCuenta'] = $inicial[$keyInicio]['naturalezaCuenta'];
            $final[$keyInicio]['debeI'] = $inicial[$keyInicio]['debe'];
            $final[$keyInicio]['haberI'] = $inicial[$keyInicio]['haber'];
            $final[$keyInicio]['debeM'] = $periodo[$keyInicio]['debe'];
            $final[$keyInicio]['haberM'] = $periodo[$keyInicio]['haber'];
            $final[$keyInicio]['nivel'] = $periodo[$keyInicio]['nivel'];

            next($inicial);
            $keyInicio = key($inicial);

            next($periodo);
            $keyPeriodo = key($periodo);

        } elseif ((!$keyPeriodo || CompararCadenas((string) $keyInicio, (string) $keyPeriodo) < 0) && $keyInicio) { //cuentas anteriores

            $final[$keyInicio]['cuenta'] = $inicial[$keyInicio]['cuenta'];
            // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
            if ($incCta == '1') {
                $final[$keyInicio]['nombre'] = $inicial[$keyInicio]['cuenta'] . ' ' . $inicial[$keyInicio]['nombre'];
            } else {
                $final[$keyInicio]['nombre'] = $inicial[$keyInicio]['nombre'];
            }
            $final[$keyInicio]['naturalezaCuenta'] = $inicial[$keyInicio]['naturalezaCuenta'];
            $final[$keyInicio]['debeI'] = $inicial[$keyInicio]['debe'];
            $final[$keyInicio]['haberI'] = $inicial[$keyInicio]['haber'];
            $final[$keyInicio]['debeM'] = (isset($final[$keyInicio]['debeM']) ? $final[$keyInicio]['debeM'] : 0.00);
            $final[$keyInicio]['haberM'] = (isset($final[$keyInicio]['haberM']) ? $final[$keyInicio]['haberM'] : 0.00);
            $final[$keyInicio]['nivel'] = $inicial[$keyInicio]['nivel'];

            next($inicial);
            $keyInicio = key($inicial);

        } elseif ($keyPeriodo) {

            $final[$keyPeriodo]['cuenta'] = $periodo[$keyPeriodo]['cuenta'];
            // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
            if ($incCta == '1') {
                $final[$keyPeriodo]['nombre'] = $periodo[$keyPeriodo]['cuenta'] . ' ' . $periodo[$keyPeriodo]['nombre'];
            } else {
                $final[$keyPeriodo]['nombre'] = $periodo[$keyPeriodo]['nombre'];
            }
            $final[$keyPeriodo]['naturalezaCuenta'] = $periodo[$keyPeriodo]['naturalezaCuenta'];
            $final[$keyPeriodo]['debeI'] = (isset($final[$keyPeriodo]['debeI']) ? $final[$keyPeriodo]['debeI'] : 0.00);
            $final[$keyPeriodo]['haberI'] = (isset($final[$keyPeriodo]['haberI']) ? $final[$keyPeriodo]['haberI'] : 0.00);
            $final[$keyPeriodo]['debeM'] = $periodo[$keyPeriodo]['debe'];
            $final[$keyPeriodo]['haberM'] = $periodo[$keyPeriodo]['haber'];
            $final[$keyPeriodo]['nivel'] = $periodo[$keyPeriodo]['nivel'];

            next($periodo);
            $keyPeriodo = key($periodo);

        } else {

            $final[0]['cuenta'] = '';
            $final[0]['nombre'] = 'SIN REGISTROS';
            $final[0]['naturalezaCuenta'] = '';
            $final[0]['debeI'] = 0;
            $final[0]['haberI'] = 0;
            $final[0]['debeM'] = 0;
            $final[0]['haberM'] = 0;
            $final[0]['nivel'] = 0;
        }

    }

    return isset($final) ? [$final] : false;

}

// Fin del código fuente
