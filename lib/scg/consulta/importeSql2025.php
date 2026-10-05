<?php

function importeSql2025($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array())
{
    $baseEjercicio = ' ctrl2025.';
    $ejercicio = '2025';
    //el periodo inicial, son los saldos acumulados antes del periodo solicitado
    //del periodo solicitado, si los meses son iguales, solo pidieron 1 mes; en caso contrario, solicitaron mas de 1 mes
    if ($parametroExtra['I']) {
        $periodoSql = "msc_id < " . $periodo['mesDesde'] . " ";
        $importeIgualSql = "WHERE pdx_debe != pdx_haber ";
    } else {
        $periodoSql = ($periodo['mesDesde'] == $periodo['mesHasta']) ? "msc_id = " . $periodo['mesDesde']
            : "( msc_id BETWEEN " . $periodo['mesDesde'] . " AND " . $periodo['mesHasta'] . " ) ";
        $importeIgualSql = "WHERE pdx_debe != 0 OR pdx_haber != 0 ";
    }
    $var_obtenernivel = $parametroExtra['busqueda']['cuentaDesde'];
    $numerodeniveles = explode(".", $parametroExtra['busqueda']['cuentaDesde']);
    $numerodeniveleshasta = explode(".", $parametroExtra['busqueda']['cuentaHasta']);
    $sqlhavingregistro = "";
    if (count($numerodeniveles) == 7) {
        $sqlhavingregistro = "having cuenta BETWEEN '" . $parametroExtra['busqueda']['cuentaDesde'] . "' AND '" . $parametroExtra['busqueda']['cuentaHasta'] . "' ;";
        $parametroExtra['busqueda']['cuentaDesde'] = $numerodeniveles[0] . "." . $numerodeniveles[1] . "." . $numerodeniveles[2] . "." . $numerodeniveles[3] . "." . $numerodeniveles[4];
        $parametroExtra['busqueda']['cuentaHasta'] = $numerodeniveleshasta[0] . "." . $numerodeniveleshasta[1] . "." . $numerodeniveleshasta[2] . "." . $numerodeniveleshasta[3] . "." . $numerodeniveleshasta[4];
    }
    $sqlsubcuentas = "";
    switch ($parametroExtra['busqueda']['tipo']) {
        case 1: //cuando elijen la busqueda normal
            // Marcos: 17/05/2016
            // Para que funcione la balanza de una cuenta a todos los niveles en pantalla
            // cambiaremos el = por LIKE
            $sqlsubcuentas = " INNER JOIN (
                            select cgsc_cuenta,ger_id, cgp_id, rub_id, cgcu_id, cgsc_id
                            FROM {$baseEjercicio}cat_CgSubcuenta ) cuenta USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id ) ";
            if (!$detalle) {
                $busquedaSql = ($parametroExtra['busqueda']['cuentaDesde'] != $parametroExtra['busqueda']['cuentaHasta'])
                    ? (
                        ($parametroExtra['busqueda']['cuentaDesde'] != 10000 && $parametroExtra['busqueda']['cuentaHasta']
                            != 99999) ? ("AND ( cgsc_cuenta BETWEEN '" . $parametroExtra['busqueda']['cuentaDesde'] . "' AND '" . $parametroExtra['busqueda']['cuentaHasta'] . "' ) ")
                        : (
                            (($parametroExtra['busqueda']['cuentaDesde'] == 10000) ? ""
                                : "AND cgsc_cuenta >= '" . $parametroExtra['busqueda']['cuentaDesde'] . "' ") . (
                                ($parametroExtra['busqueda']['cuentaHasta'] == 99999) ? ""
                                : "AND cgsc_cuenta <= '" . $parametroExtra['busqueda']['cuentaHasta'] . "' "
                            )
                        )
                    ) : ("AND cgsc_cuenta = '" . $parametroExtra['busqueda']['cuentaDesde'] . "' ");
            } else {
                $sqlsubcuentas = "";
                // y además la cuenta inicial SIEMPRE será igual a la final, así
                // que omitimos algunas validaciones.   Sólo mostramos el detalle de ger+cgp+rub
                $busquedaSql = "AND CONCAT( ger_id,'.', cgp_id,'.', rub_id ) = '" . $parametroExtra['busqueda']['cuentaDesde'] . "' ";
            }
            break;
        case 2: //busqueda por CLC
            if ($parametroExtra['busqueda']['clc_id'] != '')
                $busquedaSql = "AND pdx_clc_id_origen = " . $parametroExtra['busqueda']['clc_id'] . " ";
            break;
        case 3: //busqueda por Bdo
            if ($parametroExtra['busqueda']['bdo_id'] != '')
                $busquedaSql = "AND pdx_bdo_id_origen = " . $parametroExtra['busqueda']['bdo_id'] . " ";
            break;
        case 4: //busqueda por Gur
            if ($parametroExtra['busqueda']['gur'] != '') {
                $busquedaSql = "AND pdx_gpo_id_origen = " . substr(
                    $parametroExtra['busqueda']['gur'],
                    0,
                    1
                ) . " ";
                $busquedaSql .= "AND pdx_ur_id_origen = " . substr(
                    $parametroExtra['busqueda']['gur'],
                    1,
                    2
                ) . " ";
            }
            break;
        default:
            echo 'kym';
            break;
    }

    $cvefinSql = ($claveFinanciamiento['ecf_id'] != '') ? "AND ecf_id = " . $claveFinanciamiento['ecf_id']
        : ($claveFinanciamiento['ter_id'] != '' ? "AND ter_id = '" . $claveFinanciamiento['ter_id'] . "'"
            : '');
    $cvefinJoinSql = (($claveFinanciamiento['ecf_id'] == '' && $claveFinanciamiento['ter_id']
        != '') ? 'LEFT JOIN sinpres.enc_ClaveFinanciamiento e USING(ecf_id)
                    LEFT JOIN sinpres.cat_SuborigenRecurso'.$ejercicio.' c USING( fte_id, sft_id, ori_id, sor_id ) '
        : '');

    /* Sólo los jefes de unidad (plinio y huitron) y la directora (evangelina) y Juan Martín tendran OPCIONALMENTE un WHERE personalizado para imprimir un rango de URs  */
    if ($reporte['tipo'] == 1) {
        $urSql = "m.gpo_id = $gpo_id AND m.ur_id = $ur_id
                AND ";
    } elseif ($reporte['tipo'] == 3 && $_SESSION[_S]['nvj_id'] >= 5) {
        // Si limitaron por grupo o excluyeron alguna UR
        if ($reporte['grupos'] != '') {
            $urSql = "m.gpo_id IN ( " . rtrim($reporte['grupos']) . " )
                    AND ";

            // Si excluyeron a alguna UR
            if ($reporte['excluir'] != '')
                $urSql .= "CONCAT( m.gpo_id, m.ur_id ) NOT IN ( " . rtrim($reporte['excluir']) . " )
                        AND ";

            // Si no usaron las campos especiales, es un where estandar
        } else {
            $urSql = "m.gpo_id = $gpo_id AND m.ur_id = $ur_id
                    AND ";
        }
    } else {
        $urSql = '';
    }

    $ordenar = '';
    $key = '';
    $key2 = '';
    $selecquinto = 'NULL AS cgq_cuenta, NULL AS cgq_nombre,';

    $leftquinto = '';
    if ($reporte['agrupador'] == 1) {
        $ordenar = "cta_gpo_id, cta_ur_id";
        $key = "cta_gpo_id, cta_ur_id";
        $key2 = "cta_gpo_id, cta_ur_id";
    }

    $ordenar .= ($ordenar != '' ? ", " : '') . "ger_id";
    $key .= ($key != '' ? ", '.'," : '') . " ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', cgsc_id ";
    $key2 .= ($key2 != '' ? ", '.'," : '') . "ger_id";
    $cuenta = "ger_cuenta";
    $nombre = "ger_nombre";

    $consultaSelect = " '' cta_ue_id,x.ger_id, x.cgp_id, x.rub_id, x.cgcu_id, x.cgsc_id,
                    '' cgc_id, '' cta_id, ";

    $consultaselect2 = " '' cgc_cuenta, '' cgc_nombre,
                '' cgcu_cuenta, '' cgcu_nombre,
                '' cgsc_cuenta, '' cgsc_nombre,
                ''  rub_cuenta, '' rub_nombre,
                '' cgp_cuenta, '' cgp_nombre,
                ger_cuenta, ger_nombre,  ";

    $var_nat_id = " '' ";
    $consultaselect3 = " '' as cta_cuenta, '' as  cta_nombre,  ";
    $innerjoinUnidadEjecutora = " ";
    $innerjoinCompleto = "";
    if ($niveles[0] > 1) {
        $ordenar .= ", cgp_id";

        $key2 .= ", '.', cgp_id";
        $cuenta = "cgp_cuenta";
        $nombre = "cgp_nombre";
        $consultaSelect = " '' cta_ue_id,x.ger_id, x.cgp_id, x.rub_id, x.cgcu_id, x.cgsc_id,
                    '' cgc_id, '' cta_id, ";

        $consultaselect2 = " '' cgc_cuenta, '' cgc_nombre,
                '' cgcu_cuenta, '' cgcu_nombre,
                '' cgsc_cuenta, '' cgsc_nombre,
                ''  rub_cuenta, '' rub_nombre,
                cgp_cuenta, cgp_nombre,
                ger_cuenta, ger_nombre,  ";
    }
    if ($niveles[0] > 2) {
        $ordenar .= ", rub_id";
        $key .
            $key2 .= ", '.', rub_id";
        $cuenta = "rub_cuenta";
        $nombre = "rub_nombre";
        $consultaSelect = " '' cta_ue_id,x.ger_id, x.cgp_id, x.rub_id, x.cgcu_id, x.cgsc_id,
                    '' cgc_id, '' cta_id, ";

        $consultaselect2 = " '' cgc_cuenta, '' cgc_nombre,
                '' cgcu_cuenta, '' cgcu_nombre,
                '' cgsc_cuenta, '' cgsc_nombre,
                rub_cuenta, rub_nombre,
                cgp_cuenta, cgp_nombre,
                ger_cuenta, ger_nombre,  ";
    }
    if ($niveles[0] > 3) {
        $ordenar .= ", cgcu_id";

        $key2 .= ", '.', cgcu_id";
        $cuenta = "cgcu_cuenta";
        $nombre = "cgcu_nombre";
        $consultaSelect = " '' cta_ue_id,x.ger_id, x.cgp_id, x.rub_id, x.cgcu_id, x.cgsc_id, '' cgc_id, '' cta_id, ";

        $consultaselect2 = " '' cgc_cuenta, '' cgc_nombre,
                '' cgcu_cuenta, '' cgcu_nombre,
                cgsc_cuenta, cgsc_nombre,
                rub_cuenta, rub_nombre,
                cgp_cuenta, cgp_nombre,
                ger_cuenta, ger_nombre,  ";
    }
    if ($niveles[0] > 4) {
        $ordenar .= ", cgsc_id";

        $key2 .= ", '.', cgsc_id";
        $cuenta = "cgsc_cuenta";
        $nombre = "cgsc_nombre";
        $consultaSelect = " '' cta_ue_id,x.ger_id, x.cgp_id, x.rub_id, x.cgcu_id,x.cgsc_id,
                    '' cgc_id, '' cta_id, ";
        $consultaselect3 = "'' cta_cuenta, '' cta_nombre, ";
        $consultaselect2 = " '' cgc_cuenta, '' cgc_nombre,
                cgcu_cuenta, cgcu_nombre,
                cgsc_cuenta, cgsc_nombre,
                rub_cuenta, rub_nombre,
                cgp_cuenta, cgp_nombre,
                ger_cuenta, ger_nombre,  ";
        $var_nat_id = "nat_id ";
    }

    if ($niveles[0] > 5) {
        $ordenar .= ", cgc_id";
        $key .= ", '.', cgc_id";
        $key2 .= ", '.', cgc_id";
        $cuenta = "cgc_cuenta";
        $nombre = "cgc_nombre";
        $consultaSelect = "  x.cta_ue_id,x.ger_id, x.cgp_id, x.rub_id, x.cgcu_id, x.cgsc_id,
                    x.cgc_id, x.cta_id, ";
        $innerjoinCompleto = "    INNER JOIN {$baseEjercicio}cg_PlanCuentas USING( cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id )
                                INNER JOIN {$baseEjercicio}cg_CuentaCuarto USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id ) ";
        $consultaselect2 = " cgc_cuenta, cgc_nombre,
                cgcu_cuenta, cgcu_nombre,
                cgsc_cuenta, cgsc_nombre,
                rub_cuenta, rub_nombre,
                cgp_cuenta, cgp_nombre,
                ger_cuenta, ger_nombre,  ";
        $consultaselect3 = "cta_cuenta, cta_nombre, ";
        $var_nat_id = "nat_id ";
        $innerjoinUnidadEjecutora = " INNER JOIN {$baseEjercicio}cat_UnidadEjecutora ue ON ue.gpo_id = cta_gpo_id AND ue.ur_id = cta_ur_id  AND ue.ue_id = cta_ue_id ";
    }
    $cgq_id = "''";
    $groupcgq_id = "";
    if ($niveles[0] == 7) {

        $selecquinto = 'cgq_cuenta,cgq_nombre,';
        $cuenta = "cgq_cuenta";
        $nombre = "cgq_nombre";
        $leftquinto = "LEFT JOIN {$baseEjercicio}cg_CuentaQuinto q ON pdx.ger_id = q.ger_id AND pdx.cgp_id = q.cgp_id AND pdx.rub_id = q.rub_id AND pdx.cgcu_id = q.cgcu_id AND pdx.cgsc_id = q.cgsc_id AND pdx.cgc_id = q.cgc_id AND SUBSTRING(cta_id,1,2) = q.cgq_id";
        $consultaSelect = " x.cta_ue_id, x.ger_id, x.cgp_id, x.rub_id, x.cgcu_id, x.cgsc_id,
                    x.cgc_id, x.cta_id, ";
        $innerjoinCompleto = "    INNER JOIN {$baseEjercicio}cg_PlanCuentas USING( cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id )
                                INNER JOIN {$baseEjercicio}cg_CuentaCuarto USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id ) ";
        $consultaselect2 = " cgc_cuenta, cgc_nombre,
                cgcu_cuenta, cgcu_nombre,
                cgsc_cuenta, cgsc_nombre,
                rub_cuenta, rub_nombre,
                cgp_cuenta, cgp_nombre,
                ger_cuenta, ger_nombre,  ";
        $consultaselect3 = "cta_cuenta, cta_nombre, ";
        $var_nat_id = "nat_id ";
        $innerjoinUnidadEjecutora = " INNER JOIN {$baseEjercicio}cat_UnidadEjecutora ue ON ue.gpo_id = cta_gpo_id AND ue.ur_id = cta_ur_id  AND ue.ue_id = cta_ue_id ";
    }
    if ($niveles[0] > 7) {

        $ordenar .= ", cta_id";
        $key .= ", '.', cta_id";
        $cgq_id = " CASE  LENGTH(cta_id) WHEN 6 THEN SUBSTRING(cta_id,1,2) ELSE '' END  ";
        $key2 .= ", '.',
                    CASE  LENGTH(cta_id) WHEN 6 THEN
                      CONCAT(SUBSTRING(cta_id,1,2),'.',SUBSTRING(cta_id,3,6))
                    ELSE cta_id END ";
        $selecquinto = 'cgq_cuenta,cgq_nombre,';
        $cuenta = "cta_cuenta";
        $nombre = "cta_nombre";
        $leftquinto = "LEFT JOIN {$baseEjercicio}cg_CuentaQuinto q ON pdx.ger_id = q.ger_id AND pdx.cgp_id = q.cgp_id AND pdx.rub_id = q.rub_id AND pdx.cgcu_id = q.cgcu_id AND pdx.cgsc_id = q.cgsc_id AND pdx.cgc_id = q.cgc_id AND SUBSTRING(cta_id,1,2) = q.cgq_id ";
        $consultaSelect = " x.cta_ue_id, x.ger_id, x.cgp_id, x.rub_id, x.cgcu_id, x.cgsc_id,
                    x.cgc_id, x.cta_id, ";
        $innerjoinCompleto = "    INNER JOIN {$baseEjercicio}cg_PlanCuentas USING( cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id )
                                INNER JOIN {$baseEjercicio}cg_CuentaCuarto USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id ) ";
        $consultaselect2 = " cgc_cuenta, cgc_nombre,
                cgcu_cuenta, cgcu_nombre,
                cgsc_cuenta, cgsc_nombre,
                rub_cuenta, rub_nombre,
                cgp_cuenta, cgp_nombre,
                ger_cuenta, ger_nombre,  ";
        $consultaselect3 = "cta_cuenta, cta_nombre, ";
        $var_nat_id = "nat_id ";
        $innerjoinUnidadEjecutora = " INNER JOIN {$baseEjercicio}cat_UnidadEjecutora ue ON ue.gpo_id = cta_gpo_id AND ue.ur_id = cta_ur_id  AND ue.ue_id = cta_ue_id ";
    }
    if ($reporte['tipo'] == 1 && $niveles[0] > 7) {
        $key .= ($key != '' ? ", '.'," : '') . " cta_ue_id";
        $key2 .= ($key2 != '' ? ", '.'," : '') . " cta_ue_id";
        $ordenar .= ", cta_ue_id";

        $consultaSelect = " x.cta_ue_id,x.ger_id, x.cgp_id, x.rub_id, x.cgcu_id, x.cgsc_id,
                    x.cgc_id, x.cta_id, ";
        $innerjoinCompleto = "  INNER JOIN {$baseEjercicio}cg_PlanCuentas USING( cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id )
                                INNER JOIN {$baseEjercicio}cg_CuentaCuarto USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id ) ";
        $consultaselect2 = " cgc_cuenta, cgc_nombre,
                cgcu_cuenta, cgcu_nombre,
                cgsc_cuenta, cgsc_nombre,
                rub_cuenta, rub_nombre,
                cgp_cuenta, cgp_nombre,
                ger_cuenta, ger_nombre,  ";
        $var_nat_id = "nat_id ";
        $consultaselect3 = "cta_cuenta, cta_nombre, ";
        $innerjoinUnidadEjecutora = " INNER JOIN {$baseEjercicio}cat_UnidadEjecutora ue ON ue.gpo_id = cta_gpo_id AND ue.ur_id = cta_ur_id  AND ue.ue_id = cta_ue_id ";
    }
    $sql = "


            SELECT min(id) id,
                min(cta_gpo_id) cta_gpo_id, min(cta_ur_id) cta_ur_id, min(cta_ue_id) cta_ue_id,
                min(ger_id) ger_id, min(cgp_id) cgp_id, min(rub_id) rub_id, min(cgcu_id) cgcu_id, min(cgsc_id) cgsc_id,
                min(cgc_id) cgc_id, min(cgq_id)cgq_id,  min(cta_id) cta_id,
                SUM(pdx_debe) pdx_debe, SUM(pdx_haber) pdx_haber, MIN(cgm_naturaleza) as cgm_naturaleza,
		min(cuenta) cuenta, min(nombre) nombre,
                min(cta_cuenta) cta_cuenta , min(cta_nombre) cta_nombre,
                min(cgq_cuenta) cgq_cuenta,min(cgq_nombre) cgq_nombre,
                min(cgc_cuenta) cgc_cuenta, min(cgc_nombre) cgc_nombre,

                min(cgcu_cuenta) cgcu_cuenta, min(cgcu_nombre) cgcu_nombre,
                min(cgsc_cuenta) cgsc_cuenta, min(cgsc_nombre) cgsc_nombre,
                min(rub_cuenta) rub_cuenta, min(rub_nombre) rub_nombre,
                min(cgp_cuenta) cgp_cuenta, min(cgp_nombre) cgp_nombre,
                min(ger_cuenta) ger_cuenta, min(ger_nombre) ger_nombre,
                min(ur_clave) ur_clave, min(ur_nombre) ur_nombre,
                min(nivel) nivel  FROM(
            SELECT id,
                cta_gpo_id, cta_ur_id, cta_ue_id,
                pdx.ger_id, pdx.cgp_id, pdx.rub_id, pdx.cgcu_id, pdx.cgsc_id,
                pdx.cgc_id,$cgq_id as cgq_id, cta_id,
                pdx_debe, pdx_haber, nat_id as cgm_naturaleza,
                $cuenta AS cuenta, $nombre AS nombre,
                $consultaselect3
                $selecquinto
                $consultaselect2
                ur_clave, ur_nombre,
                nivel
            FROM (
                SELECT
                    x.cta_gpo_id, x.cta_ur_id, $consultaSelect
                    SUM( x.pdx_debe ) AS pdx_debe, SUM( x.pdx_haber ) AS pdx_haber,
                    " . $niveles[0] . " AS nivel,
                    CONCAT( $key2 ) AS id
              FROM {$baseEjercicio}cg_PolizaMaestro m
                INNER JOIN {$baseEjercicio}cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) " .
        $sqlsubcuentas . "
                " . (($reporte['tipo'] == 2 && $_SESSION[_S]['nvj_id'] < 6) ? "    INNER JOIN cat_CargaTrabajo t ON t.gpo_id = m.gpo_id AND t.ur_id = m.ur_id
                    $cvefinJoinSql
                WHERE t.usr_id = " . $_SESSION[_S]['s24-id'] . "
                    AND " : "    $cvefinJoinSql
                WHERE $urSql") . (($ue_id) ? "m.ue_id = $ue_id
                    AND " : '') . "
                    $periodoSql
                    AND m.epl_id != 'C' AND m.epl_id != 'H'
                    $busquedaSql
                    $cvefinSql
                GROUP BY cta_gpo_id, cta_ur_id,cta_ue_id, '.',ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', cgsc_id, '.',  cgc_id , '.', cta_id
            )pdx
                $innerjoinCompleto
                INNER JOIN {$baseEjercicio}cat_CgSubcuenta USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
                INNER JOIN {$baseEjercicio}cat_CgCuenta USING( ger_id, cgp_id, rub_id, cgcu_id )
                INNER JOIN {$baseEjercicio}cat_Rubro USING( ger_id, cgp_id, rub_id )
                INNER JOIN {$baseEjercicio}cat_CgGrupo USING( ger_id, cgp_id )
                INNER JOIN {$baseEjercicio}cat_Genero USING( ger_id )
                INNER JOIN {$baseEjercicio}cat_UnidadResponsable ur ON ur.gpo_id = cta_gpo_id AND ur.ur_id = cta_ur_id
                $innerjoinUnidadEjecutora
                $leftquinto
            $importeIgualSql
            ORDER BY $ordenar
            )B
            GROUP BY " . (($reporte['tipo'] == 2 && $_SESSION[_S]['nvj_id'] < 6)
            ? "  cta_gpo_id, cta_ur_id, " : "  ") . " $key2$groupcgq_id
        $sqlhavingregistro";

    $contable = new AccesoDatos(); //variable de konexion para los saldos iniciales

    $contable->conectar();
    $contable->setSql($sql);
    $contable->consultar();
    $contable->desconectar();

    $datos = array();

    while ($rowContable = $contable->extraer()) {
        $datos['C.' . $rowContable['id']] = array(
            'id' => 'C.' . $rowContable['id'],
            'ur_clave' => $rowContable['ur_clave'],
            'ur_nombre' => $rowContable['ur_nombre'],
            'cta_gpo_id' => $rowContable['cta_gpo_id'],
            'cta_ur_id' => str_pad($rowContable['cta_ur_id'], 2, "0", STR_PAD_LEFT),
            'cta_ue_id' => $rowContable['cta_ue_id'],
            'ger_cuenta' => $rowContable['ger_cuenta'],
            'ger_nombre' => $rowContable['ger_nombre'],
            'cgp_cuenta' => $rowContable['cgp_cuenta'],
            'cgp_nombre' => $rowContable['cgp_nombre'],
            'rub_cuenta' => $rowContable['rub_cuenta'],
            'rub_nombre' => $rowContable['rub_nombre'],
            'cgcu_cuenta' => $rowContable['cgcu_cuenta'],
            'cgcu_nombre' => $rowContable['cgcu_nombre'],
            'cgsc_cuenta' => $rowContable['cgsc_cuenta'],
            'cgsc_nombre' => $rowContable['cgsc_nombre'],
            'cgc_cuenta' => $rowContable['cgc_cuenta'],
            'cgc_nombre' => $rowContable['cgc_nombre'],
            'cgq_cuenta' => $rowContable['cgq_cuenta'],
            'cgq_nombre' => $rowContable['cgq_nombre'],
            'cta_cuenta' => $rowContable['cta_cuenta'],
            'cta_nombre' => $rowContable['cta_nombre'],
            'cuenta' => $rowContable['cuenta'],
            'nombre' => $rowContable['nombre'],
            'pdx_debe' => $rowContable['pdx_debe'],
            'pdx_haber' => $rowContable['pdx_haber'],
            'cgm_naturaleza' => $rowContable['cgm_naturaleza'],
            'nivel' => $rowContable['nivel']
        );
    };
    return isset($datos) ? $datos : false;
}
