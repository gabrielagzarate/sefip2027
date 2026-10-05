<?php

require_once dirname(__FILE__) . '/../../compararCadenas.php';
require_once dirname(__FILE__) . '/../../redondear.php';

function vinculacionMinistrado($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array(), $paginado = false, $nInicial = 0, $numreg = 0, $ejecsql2 = false)
{
    $ejercicioActual = $_SESSION[_S]['ejercicioActual'];
    $paginadosql = "";
    if ($paginado) {
        $paginadosql = " LIMIT " . $nInicial . ", " . $numreg . " ";
    }

    $parametrosSQL = [
        ':gpo' => $gpo_id,
        ':ur' => $ur_id,
        ':mmc' => $mmc_id,
        ':ejercicio' => $ejercicioActual
    ];

    $sqlFiltro = '';
    if ($parametroExtra['gur'] != '') {
        $sqlFiltro = " AND c.gpo_id = ".substr($parametroExtra['gur'], 0, 1)." AND c.ur_id = ".substr($parametroExtra['gur'],1,2)." ";
        $parametrosSQL[':cgpo'] = substr($parametroExtra['gur'], 0, 1);
        $parametrosSQL[':cur'] = substr($parametroExtra['gur'],1,2);
    }
    if ($parametroExtra['clc_id'] != '') {
        $sqlFiltro .= " AND v.clc_id = ".$parametroExtra['clc_id'] ." ";
        $parametrosSQL[':vclc'] = $parametroExtra['clc_id'];
    }
    if ($parametroExtra['bdo_id'] != '') {
        $sqlFiltro .= " AND v.bdo_id = ".$parametroExtra['bdo_id']." ";
        $parametrosSQL[':vbdo'] = $parametroExtra['bdo_id'];
    }
    if ($parametroExtra['rfc_id'] != '') {
        $sqlFiltro .= " AND b.rfc_id = '".$parametroExtra['rfc_id']."' ";
        $parametrosSQL[':brfc'] = $parametroExtra['rfc_id'];
    }
    if ($parametroExtra['bdo_referencia'] != '') {
        $sqlFiltro .= " AND b.bdo_referencia IN (" . $parametroExtra['bdo_referencia'] . ") ";
    }
    if (array_key_exists('soloSinPolizaAutorizacion', $parametroExtra) && $parametroExtra['soloSinPolizaAutorizacion']) {
        $sqlFiltro .= " AND polizaAutorizado IS NULL AND v.tmi_id IN ('1', '4', '5', '6', '7', '8', '9') AND polizaCancelado IS NULL ";
    } elseif (array_key_exists('soloSinPolizaCancelacion', $parametroExtra) && $parametroExtra['soloSinPolizaCancelacion']) {
        $sqlFiltro .= " AND polizaCancelado IS NULL AND polizaAutorizado IS NOT NULL ";
    }

    $pensiones = (isset($parametroExtra['pensiones']) && $parametroExtra['pensiones']) ? " AND v.tmi_id = 7 " : "";
    $var_MAX = " MAX";

    switch ($mmc_id) {
        case 'B':
            $tablaAuxiliar = ''.BD_TESORERIA.'.min_BancoDocumentoMinistrado_' . $ejercicioActual;
            $letraAuxiliar = 'm';
            $polizaSql = 'CASE ' . $var_MAX . '(tmi_id) WHEN 2 THEN  ' . $var_MAX . '(tmi_nombre) ELSE  ' . $var_MAX . '(pa.polizaAutorizado) END AS polizaAutorizado, MAX(pa.fechaAutorizado)AS fechaAutorizado, ';
            if ($periodo['mesDesde'] != '') {
                $sqlFiltro .= ($sqlFiltro != '' ? "AND v.msc_id = " . $periodo['mesDesde'] . " " : "");
                $var_MAX = "";
            }
            break;
        case 'T':
            $tablaAuxiliar = ''.BD_TESORERIA.'.min_BancoDocumentoMinistrado_' . $ejercicioActual;
            $letraAuxiliar = 'm';
            $polizaSql = 'CASE  ' . $var_MAX . '(tmi_id) WHEN 1 THEN  ' . $var_MAX . '(pa.polizaAutorizado) ELSE  ' . $var_MAX . '(tmi_nombre) END AS polizaAutorizado, MAX(pa.fechaAutorizado) AS fechaAutorizado, ';
            if (isset($parametroExtra['repacumulado']) && $parametroExtra['repacumulado']) {
                $periodo['mesDesde'] = '';
            }
            if (isset($parametroExtra['repacumuladomes']) && $parametroExtra['repacumuladomes']) {
                if ($periodo['mesDesde'] != '') {
                    $sqlFiltro .= ($sqlFiltro != '' ? "AND v.msc_id <= " . $periodo['mesDesde'] . " " : "");
                }
            } else {
                if ($periodo['mesDesde'] != '') {
                    $sqlFiltro .= ($sqlFiltro != '' ? "AND v.msc_id = " . $periodo['mesDesde'] . "" : "");
                    $var_MAX = "";
                }
            }
            break;
        default:
            break;
    }

    $sql = "SELECT SQL_CALC_FOUND_ROWS
                v.gpo_id, v.ur_id, v.clc_id, v.afe_id,
                v.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia, b.bdo_concepto,
                $var_MAX(v.tdf_id) AS tdf_id_ministrado, $var_MAX(v.bdm_referencia) AS bdm_referencia,
                b.bdo_percepcionPresupuestal, b.bdo_percepcionContable, b.bdo_retencionContable,
                $var_MAX(v.msc_id),
                $var_MAX(v.bd{$letraAuxiliar}_estado) AS estado, 
                DATE( {$var_MAX}(v.bd{$letraAuxiliar}_fechaAutorizado) ) AS fechaAutorizacion, 
                DATE( {$var_MAX}(v.bd{$letraAuxiliar}_fechaCancelado) ) AS fechaCancelacion,
                {$var_MAX}(v.bd{$letraAuxiliar}_agrupador) AS agrupador,
                {$polizaSql}
                MAX(pa.gpo_id_polizaAutorizado) AS gpo_id_polizaAutorizado,
                MAX(pa.ur_id_polizaAutorizado) AS ur_id_polizaAutorizado,
                MAX(pa.ue_id_polizaAutorizado) AS ue_id_polizaAutorizado,
                MAX(pa.msc_id_polizaAutorizado) AS msc_id_polizaAutorizado,
                MAX(pa.pot_id_polizaAutorizado) AS pot_id_polizaAutorizado,
                MAX(pa.prf_id_polizaAutorizado ) AS prf_id_polizaAutorizado,
                MAX(pa.pma_id_polizaAutorizado) AS pma_id_polizaAutorizado,
                MAX(pc.polizaCancelado)as polizaCancelado, 
                MAX(pc.fechaCancelado) as fechaCancelado,
                MAX(pc.gpo_id_polizaCancelado) AS gpo_id_polizaCancelado,
                MAX(pc.ur_id_polizaCancelado) AS ur_id_polizaCancelado,
                MAX(pc.ue_id_polizaCancelado) AS ue_id_polizaCancelado,
                MAX(pc.msc_id_polizaCancelado) AS msc_id_polizaCancelado,
                MAX(pc.pot_id_polizaCancelado) AS pot_id_polizaCancelado,
                MAX(pc.prf_id_polizaCancelado) AS prf_id_polizaCancelado,
                MAX(pc.pma_id_polizaCancelado) AS pma_id_polizaCancelado,
                CONCAT( v.gpo_id, v.ur_id, '.', LPAD( $var_MAX(v.msc_id), 2, 000 ), '.', LPAD( v.afe_id, 6, 0000000 ), '.', LPAD( v.bdo_id, 6, 000000 ) ) AS id,ecf_clave
            FROM cat_BancoDocumentos b
                INNER JOIN {$tablaAuxiliar} v USING( gpo_id, ur_id, bdo_id )
                INNER JOIN cg_MesContable ms USING( gpo_id, ur_id, msc_id )
                INNER JOIN ".BD_TESORERIA.".cat_TipoMinistracion USING( tmi_id )
                INNER JOIN scp_Clc c USING( gpo_id, ur_id, clc_id )
                INNER JOIN enc_ClaveFinanciamiento using(ecf_id)
                LEFT JOIN(
                    SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado, MAX(m.ue_id) AS ue_id_polizaAutorizado,
                        MAX(m.msc_id) AS msc_id_polizaAutorizado, m.pot_id AS pot_id_polizaAutorizado, m.prf_id AS prf_id_polizaAutorizado,
                        m.pma_id AS pma_id_polizaAutorizado, MAX(m.pma_fecha) AS fechaAutorizado,
                        CONCAT( m.pot_id, '-', m.prf_id, m.pma_id ,' ',MIN(m.apg_id)) AS polizaAutorizado,
                        (x.pdx_gpo_id_vinculacion) AS gpo_id, (x.pdx_ur_id_vinculacion) AS ur_id,
                        (x.pdx_afe_id_vinculacion) AS afe_id, (x.pdx_bdo_id_vinculacion) AS bdo_id,
                        (x.pdx_agrupador) AS agrupador
                    FROM cg_PolizaMaestro m
                        INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                        AND m.mmc_id = :mmc
                        AND m.tmc_id = 'A'
                        AND x.pdx_ejercicio = :ejercicio
                    GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_vinculacion, x.pdx_bdo_id_vinculacion,  m.pot_id,m.prf_id, m.pma_id,gpo_id, ur_id, afe_id, bdo_id,agrupador
                )pa USING( gpo_id, ur_id, afe_id, bdo_id )
                LEFT JOIN (
                    SELECT m.gpo_id AS gpo_id_polizaCancelado, m.ur_id AS ur_id_polizaCancelado, MAX(m.ue_id) AS ue_id_polizaCancelado,
                        MAX(m.msc_id) AS msc_id_polizaCancelado, m.pot_id AS pot_id_polizaCancelado, m.prf_id AS prf_id_polizaCancelado,
                        m.pma_id AS pma_id_polizaCancelado, MAX(m.pma_fecha) AS fechaCancelado,
                        CONCAT( m.pot_id, '-', m.prf_id, m.pma_id,' ',MIN(m.apg_id) ) AS polizaCancelado,
                        (x.pdx_gpo_id_vinculacion) AS gpo_id, (x.pdx_ur_id_vinculacion) AS ur_id,
                        (x.pdx_afe_id_vinculacion) AS afe_id, (x.pdx_bdo_id_vinculacion) AS bdo_id,
                        (x.pdx_agrupador) AS agrupador
                    FROM cg_PolizaMaestro m
                        INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                        AND m.mmc_id = :mmc
                        AND m.tmc_id = 'C'
                        AND x.pdx_ejercicio = :ejercicio
                    GROUP BY m.gpo_id, m.ur_id, x.pdx_afe_id_vinculacion, x.pdx_bdo_id_vinculacion,m.pot_id,m.prf_id, m.pma_id,gpo_id, ur_id, afe_id, bdo_id,agrupador
                )pc USING( gpo_id, ur_id, afe_id, bdo_id )
            WHERE b.bdo_id IS NOT NULL {$sqlFiltro}
                {$pensiones}
            GROUP BY gpo_id, ur_id, clc_id, bdo_id, afe_id,pa.polizaAutorizado
            ORDER BY gpo_id, ur_id, clc_id, bdo_id, afe_id,pa.polizaAutorizado {$paginadosql}";

    $conexion = new AccesoDatos(); //variable de konexion para los saldos iniciales
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($parametrosSQL);
    $datos = array();
    while ($rowData = $conexion->extraer()) {
        $datos[$rowData['id']] = array(
            'id' => $rowData['id'],
            'gpo_id' => $rowData['gpo_id'],
            'ur_id' => $rowData['ur_id'],
            'gur' => $rowData['gpo_id'] . $rowData['ur_id'],
            'clc_id' => $rowData['clc_id'],
            'afe_id' => $rowData['afe_id'],
            'bdo_id' => $rowData['bdo_id'],
            'tdf_id' => $rowData['tdf_id'],
            'rfc_id' => $rowData['rfc_id'],
            'bdo_referencia' => $rowData['bdo_referencia'],
            'bdo_concepto' => $rowData['bdo_concepto'],
            'bdo_importe' => $rowData['bdo_percepcionPresupuestal'] + $rowData['bdo_percepcionContable'] - $rowData['bdo_retencionContable'],
            'estado' => $rowData['estado'],
            'tdf_id_ministrado' => $rowData['tdf_id_ministrado'],
            'bdm_referencia' => $rowData['bdm_referencia'],
            'fechaAutorizacion' => $rowData['fechaAutorizacion'],
            'fechaCancelacion' => $rowData['fechaCancelacion'],
            'polizaAutorizado' => $rowData['polizaAutorizado'],
            'fechaAutorizado' => $rowData['fechaAutorizado'],
            'polizaCancelado' => $rowData['polizaCancelado'],
            'fechaCancelado' => $rowData['fechaCancelado'],
            'agrupador' => $rowData['agrupador'],
            'ecf_clave' => $rowData['ecf_clave']
        );
    }

    $sqlParams = [
        ':gpo' => $gpo_id,
        ':ur' => $ur_id,
        ':mmc' => $mmc_id,
        ':ejercicio' => $ejercicioActual
    ];
    if (!empty($datos) && array_key_exists('soloSinPolizaCancelacion', $parametroExtra) && $parametroExtra['soloSinPolizaCancelacion']) {
        $lista = join(',', array_column($datos,'agrupador'));
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql(
            "SELECT DISTINCT pdx_agrupador AS agrupador
            FROM cg_PolizaMaestro m
            INNER JOIN cg_PolizaDetalleAuxiliar x USING(gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id)
            WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                AND m.mmc_id = :mmc
                AND m.tmc_id = 'C'
                AND x.pdx_ejercicio = :ejercicio
                AND x.pdx_agrupador IN ($lista);
        ");
        $conexion->consultar($sqlParams);

        $agrupadoresInvalidos = array();

        while ($agrupador = $conexion->extraer()) {
            array_push($agrupadoresInvalidos, $agrupador['agrupador']);
        }

        $datosCorrectos = array_filter($datos, function ($dato) use ($agrupadoresInvalidos) {
            return !in_array($dato['agrupador'], $agrupadoresInvalidos);
        });
        $datos = $datosCorrectos;
    } elseif (!empty($datos) && array_key_exists('soloSinPolizaAutorizacion', $parametroExtra) && $parametroExtra['soloSinPolizaAutorizacion']) {
        $lista = '';
        foreach ($datos as $dato) {
            $lista .= (empty($lista) ? '(' : ', ') . "'" . $dato['agrupador'] . "'";
        }
        $lista .= ')';
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("
            SELECT DISTINCT pdx_agrupador AS agrupador
            FROM cg_PolizaMaestro m
            INNER JOIN cg_PolizaDetalleAuxiliar x USING(gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id)
            WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                AND m.mmc_id = :mmc
                AND (m.tmc_id = 'A' OR m.tmc_id = 'C')
                AND x.pdx_ejercicio = :ejercicio
                AND x.pdx_agrupador IN $lista;
        ");
        $conexion->consultar($sqlParams);

        $agrupadoresInvalidos = array();

        while ($agrupador = $conexion->extraer()) {
            array_push($agrupadoresInvalidos, $agrupador['agrupador']);
        }

        $datosCorrectos = array_filter($datos, function ($dato) use ($agrupadoresInvalidos) {
            return !in_array($dato['agrupador'], $agrupadoresInvalidos);
        });
        $datos = $datosCorrectos;
    }

    $nTotalPages = 0;
    if ($paginado == true) {
        $conexion->setSql("SELECT FOUND_ROWS() numReg");
        $conexion->consultar();
        $totalRegistros = $conexion->sacar();

        $numregConsul = $totalRegistros[0];
        $nTotalPages = ceil($numregConsul / $numreg);
    }
    $conexion->desconectar();

    if ($paginado == true) {
        $datos[0] = array(
            'id' => 'num', 'gpo_id' => $nTotalPages, 'ur_id' => '',
            'gur' => '',
            'clc_id' => '',
            'afe_id' => '',
            'bdo_id' => '',
            'tdf_id' => '',
            'rfc_id' => '',
            'bdo_referencia' => '',
            'bdo_concepto' => '',
            'bdo_importe' => '',
            'estado' => '',
            'tdf_id_ministrado' => '',
            'bdm_referencia' => '',
            'fechaAutorizacion' => '',
            'fechaCancelacion' => '', 
            'polizaAutorizado' => '',
            'polizaCancelado' => '', 
            'fechaCancelado' => '',
            'agrupador' => '', 'ecf_clave' => ''
        );
    }

    return $datos;
}
