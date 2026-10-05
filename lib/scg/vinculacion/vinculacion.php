<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/../../compararCadenas.php';
require_once dirname(__FILE__) . '/../../redondear.php';
require_once dirname(__FILE__) . '/../getPolizaAgrupador.php';

function vinculacion($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles, $parametroExtra = array(), $paginado = false, $nInicial = 0, $numreg = 0, $ejecsql2 = false)
{
    set_time_limit(0);

    $ejercicioActual = $_SESSION[_S]['ejercicioActual'];

    switch ($mmc_id) {
        case 'D':
            $tablaAuxiliar = 'scp_BancoDocumentoDevengado';
            $letraAuxiliar = 'd';
            break;
        case 'E':
            $tablaAuxiliar = 'scp_BancoDocumentoEjercido';
            $letraAuxiliar = 'e';
            break;
        case 'G':
            $tablaAuxiliar = 'scp_BancoDocumentoCereCo';
            $letraAuxiliar = 'g';
            break;
        case 'R':
            $tablaAuxiliar = 'scp_BancoDocumentoRectificado';
            $letraAuxiliar = 'r';
            break;
        default:
            break;
    }

    $parametrosSQL = [
        ':gpo' => $gpo_id,
        ':ur' => $ur_id,
        ':mmc' => $mmc_id,
        ':ejercicio' => $ejercicioActual,
    ];
    $sqlFiltro = '';
    if ($parametroExtra['gur'] != '') {
        $sqlFiltro = " AND v.gpo_id = :vgpo AND v.ur_id = :vur ";
        $parametrosSQL[':vgpo'] = substr($parametroExtra['gur'], 0, 1);
        $parametrosSQL[':vur'] = substr($parametroExtra['gur'], 1, 2);
    }
    if ($parametroExtra['clc_id'] != '') {
        $sqlFiltro .=  " AND v.clc_id = :vclc ";
        $parametrosSQL[':vclc'] = $parametroExtra['clc_id'];
    }
    if ($parametroExtra['bdo_id'] != '') {
        $sqlFiltro .= " AND v.bdo_id = :vbdo ";
        $parametrosSQL[':vbdo'] = $parametroExtra['bdo_id'];
    }
    if ($parametroExtra['rfc_id'] != '') {
        $sqlFiltro .= " AND b.rfc_id = :brfc ";
        $parametrosSQL[':brfc'] = $parametroExtra['rfc_id'];
    }
    if ($parametroExtra['bdo_referencia'] != '') {
        $ref = $parametroExtra['bdo_referencia'];
        $sqlFiltro .=  " AND b.bdo_referencia IN ( {$ref} ) ";
    }
    if (array_key_exists('soloSinPolizaAutorizacion', $parametroExtra) && $parametroExtra['soloSinPolizaAutorizacion']) {
        $sqlFiltro .= " AND polizaAutorizado IS NULL AND polizaCancelado IS NULL ";
    } elseif (array_key_exists('soloSinPolizaCancelacion', $parametroExtra) && $parametroExtra['soloSinPolizaCancelacion']) {
        $sqlFiltro .= " AND polizaCancelado IS NULL AND polizaAutorizado IS NOT NULL ";
    }

    $var_MAX = " MAX";
    if ($periodo['mesDesde'] != '') {
        $sqlFiltro .= " AND v.msc_id = :mesdesde ";
        $parametrosSQL[':mesdesde'] = $periodo['mesDesde'];
        $var_MAX = "";
    }
    $paginadosql = "";
    if ($paginado) {
        $paginadosql = " LIMIT " . $nInicial . ", " . $numreg . " ";
    }

    $sql = 
        "SELECT SQL_CALC_FOUND_ROWS
            v.gpo_id, v.ur_id, v.clc_id, v.afe_id,
            (bdo_percepcionPresupuestal + bdo_percepcionContable) as bdo_importe, 
            concat(gpo_id, ur_id) as gur, v.bdo_id, b.tdf_id, b.rfc_id, b.bdo_referencia, 
            b.bdo_fechaDocumento, b.bdo_concepto, b.bdo_fondoRotatorio, b.bdo_origen,
            b.bdo_percepcionPresupuestal, b.bdo_percepcionContable, b.bdo_retencionContable,
            $var_MAX(v.msc_id) AS msc_id,
            $var_MAX(v.bd{$letraAuxiliar}_estado) AS estado, 
            DATE( $var_MAX(v.bd{$letraAuxiliar}_fechaAutorizado) ) AS fechaAutorizacion, 
            DATE( $var_MAX(v.bd{$letraAuxiliar}_fechaCancelado) ) AS fechaCancelacion,
            $var_MAX(v.bd{$letraAuxiliar}_agrupador) AS agrupador,
            MAX(pa.polizaAutorizado) AS polizaAutorizado, 
            MAX(pa.fechaAutorizado) AS fechaAutorizado,
            MAX(pa.gpo_id_polizaAutorizado) AS gpo_id_polizaAutorizado,
            MAX(pa.ur_id_polizaAutorizado) AS ur_id_polizaAutorizado, 
            MAX(pa.ue_id_polizaAutorizado) AS ue_id_polizaAutorizado,
            MAX(pa.msc_id_polizaAutorizado) AS msc_id_polizaAutorizado, 
            MAX(pa.pot_id_polizaAutorizado) AS pot_id_polizaAutorizado, 
            MAX(pa.prf_id_polizaAutorizado) AS prf_id_polizaAutorizado,
            MAX(pa.pma_id_polizaAutorizado) AS pma_id_polizaAutorizado,
            MAX(pc.polizaCancelado) AS polizaCancelado, 
            MAX(pc.fechaCancelado) AS fechaCancelado,
            MAX(pc.gpo_id_polizaCancelado) AS gpo_id_polizaCancelado,
            MAX(pc.ur_id_polizaCancelado) AS ur_id_polizaCancelado,
            MAX(pc.ue_id_polizaCancelado) AS ue_id_polizaCancelado,
            MAX(pc.msc_id_polizaCancelado) AS msc_id_polizaCancelado, 
            MAX(pc.pot_id_polizaCancelado) AS pot_id_polizaCancelado, 
            MAX(pc.prf_id_polizaCancelado) AS prf_id_polizaCancelado,
            MAX(pc.pma_id_polizaCancelado) AS pma_id_polizaCancelado,
            CONCAT( v.gpo_id, v.ur_id, '.', LPAD( {$var_MAX}(v.msc_id), 2, 000 ), '.', LPAD( v.afe_id, 6, 0000000 ), '.', LPAD( v.bdo_id, 6, 000000 ), '.', COALESCE( MAX(pa.pma_id_polizaAutorizado), 000000 ) ) AS id
        FROM cat_BancoDocumentos b
            INNER JOIN {$tablaAuxiliar} v USING(gpo_id, ur_id, bdo_id)
            INNER JOIN cg_MesContable ms USING(gpo_id, ur_id, msc_id)
            LEFT JOIN(
                SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado, MAX(m.ue_id) AS ue_id_polizaAutorizado,
                    MAX(m.msc_id) AS msc_id_polizaAutorizado, (m.pot_id) AS pot_id_polizaAutorizado, (m.prf_id) AS prf_id_polizaAutorizado,
                    m.pma_id AS pma_id_polizaAutorizado, MAX(m.pma_fecha) AS fechaAutorizado,
                    CONCAT( (m.pot_id), '-', (m.prf_id), (m.pma_id),' ',apg_id  ) AS polizaAutorizado,
                    (x.pdx_gpo_id_vinculacion) AS gpo_id, (x.pdx_ur_id_vinculacion) AS ur_id,
                    x.pdx_afe_id_vinculacion AS afe_id, x.pdx_bdo_id_vinculacion AS bdo_id,
                    (x.pdx_agrupador) AS agrupador
                FROM cg_PolizaMaestro m
                    INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                    AND m.mmc_id = :mmc
                    AND m.tmc_id = 'A'
                    AND x.pdx_ejercicio = :ejercicio
                GROUP BY m.gpo_id, m.ur_id, x.pdx_gpo_id_vinculacion,pdx_ur_id_vinculacion,x.pdx_afe_id_vinculacion, x.pdx_bdo_id_vinculacion,
                    m.pot_id,m.prf_id, m.pma_id,m.apg_id,x.pdx_agrupador
            )pa USING(gpo_id, ur_id, afe_id, bdo_id)
            LEFT JOIN (
                SELECT m.gpo_id AS gpo_id_polizaCancelado, m.ur_id AS ur_id_polizaCancelado, MAX(m.ue_id) AS ue_id_polizaCancelado,
                    MAX(m.msc_id) AS msc_id_polizaCancelado,( m.pot_id) AS pot_id_polizaCancelado, (m.prf_id) AS prf_id_polizaCancelado,
                    m.pma_id AS pma_id_polizaCancelado, MAX(m.pma_fecha) AS fechaCancelado,
                    CONCAT( (m.pot_id), '-', (m.prf_id), (m.pma_id),' ',apg_id ) AS polizaCancelado,
                    (x.pdx_gpo_id_vinculacion) AS gpo_id, (x.pdx_ur_id_vinculacion) AS ur_id,
                    x.pdx_afe_id_vinculacion AS afe_id, x.pdx_bdo_id_vinculacion AS bdo_id,
                    (x.pdx_agrupador) AS agrupador
                FROM cg_PolizaMaestro m
                    INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                    AND m.mmc_id = :mmc
                    AND m.tmc_id = 'C'
                    AND x.pdx_ejercicio = :ejercicio
                GROUP BY m.gpo_id, m.ur_id, x.pdx_gpo_id_vinculacion,pdx_ur_id_vinculacion,x.pdx_afe_id_vinculacion, x.pdx_bdo_id_vinculacion,
                    m.pot_id,m.prf_id, m.pma_id,m.apg_id,x.pdx_agrupador
            )pc USING(gpo_id, ur_id, afe_id, bdo_id)
        WHERE b.bdo_id IS NOT NULL {$sqlFiltro}
            AND v.bd{$letraAuxiliar}_percepcionpresupuestal > 0
        GROUP BY gpo_id, ur_id, clc_id, bdo_id, afe_id, pa.polizaAutorizado,pc.polizaCancelado
        ORDER BY gpo_id, ur_id, clc_id, bdo_id, afe_id {$paginadosql};";

    $conexion = new AccesoDatos(); //variable de konexion para los saldos iniciales
    $conexion->conectar();
    $conexion->bindParams($parametrosSQL);
    $conexion->setSql($sql);
    $conexion->consultar();

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
            'bdo_fechaDocumento' => $rowData['bdo_fechaDocumento'],
            'bdo_concepto' => $rowData['bdo_concepto'],
            'bdo_fondoRotatorio' => ($rowData['bdo_fondoRotatorio'] == 'N' ? 'NO' : 'SI'),
            'bdo_origen' => ($rowData['bdo_origen'] == 1 ? 'CAPTURA MANUAL' : ($rowData['bdo_origen'] == 2 ? 'XML' : 'CARGA MASIVA')),
            'bdo_importe' => $rowData['bdo_percepcionPresupuestal'] + $rowData['bdo_percepcionContable'],
            'bdo_percepcionPresupuestal' => $rowData['bdo_percepcionPresupuestal'],
            'estado' => $rowData['estado'],
            'fechaAutorizacion' => $rowData['fechaAutorizacion'],
            'fechaCancelacion' => $rowData['fechaCancelacion'],
            'polizaAutorizado' => $rowData['polizaAutorizado'],
            'fechaAutorizado' => $rowData['fechaAutorizado'],
            'polizaCancelado' => $rowData['polizaCancelado'],
            'fechaCancelado' => $rowData['fechaCancelado'],
            'agrupador' => $rowData['agrupador']
        );
    }

    if (
        !empty($datos) && array_key_exists('soloSinPolizaCancelacion', $parametroExtra) 
        && $parametroExtra['soloSinPolizaCancelacion']
    ) {
        $lista ='"' . join('","',array_column($datos, 'agrupador')) . '"';
        $sql = "SELECT DISTINCT pdx_agrupador AS agrupador
            FROM cg_PolizaMaestro m
            INNER JOIN cg_PolizaDetalleAuxiliar x USING(gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id)
            WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                AND m.mmc_id = :mmc
                AND m.tmc_id = 'C'
                AND x.pdx_ejercicio = :ejercicio
                AND x.pdx_agrupador IN ($lista);
        ";
        $parametros = [':gpo' =>$gpo_id, ':ur' => $ur_id, ':mmc' => $mmc_id, ':ejercicio' => $ejercicioActual];
        
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);

        $agrupadoresInvalidos = array();

        while ($agrupador = $conexion->extraer()) {
            array_push($agrupadoresInvalidos, $agrupador['agrupador']);
        }

        $datosCorrectos = array_filter($datos, function ($dato) use ($agrupadoresInvalidos) {
            return !in_array($dato['agrupador'], $agrupadoresInvalidos);
        });
        $datos = $datosCorrectos;
    } elseif (!empty($datos) && array_key_exists('soloSinPolizaAutorizacion', $parametroExtra) && $parametroExtra['soloSinPolizaAutorizacion']) {
        $lista =  '"' . join('","', array_column($datos, 'agrupador')) . '"';
        $sql = "SELECT DISTINCT pdx_agrupador AS agrupador
            FROM cg_PolizaMaestro m
            INNER JOIN cg_PolizaDetalleAuxiliar x USING(gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id)
            WHERE m.gpo_id = :gpo AND m.ur_id = :ur
                AND m.mmc_id = :mmc
                AND (m.tmc_id = 'A' OR m.tmc_id = 'C')
                AND x.pdx_ejercicio = :ejercicio
                AND x.pdx_agrupador IN ($lista);
        ";
        $parametosSQL = [
            ':gpo' => $gpo_id,
            ':ur' => $ur_id,
            ':mmc' => $mmc_id,
            ':ejercicio' => $ejercicioActual,

        ];
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametosSQL);

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
    if ($paginado) {
        $conexion->setSql("SELECT FOUND_ROWS() numReg");
        $conexion->consultar();
        $totalRegistros = $conexion->sacar();

        $numregConsul = $totalRegistros[0];
        $nTotalPages = ceil($numregConsul / $numreg);
    }
    $conexion->desconectar();

    if ($paginado) {
        $datos[0] = array(
            'id' => 'num',
            'gpo_id' => $nTotalPages,
            'ur_id' => '',
            'gur' => '',
            'clc_id' => '',
            'afe_id' => '',
            'bdo_id' => '',
            'tdf_id' => '',
            'rfc_id' => '',
            'bdo_referencia' => '',
            'bdo_fechaDocumento' => '',
            'bdo_concepto' => '',
            'bdo_fondoRotatorio' => '',
            'bdo_origen' => '',
            'bdo_importe' => '',
            'bdo_percepcionPresupuestal' => '',
            'estado' => '',
            'fechaAutorizacion' => '',
            'fechaCancelacion' => '',
            'polizaAutorizado' => '',
            'fechaAutorizado' => '',
            'polizaCancelado' => '',
            'fechaCancelado' => '',
            'agrupador' => ''
        );
    }
    return $datos;
}
