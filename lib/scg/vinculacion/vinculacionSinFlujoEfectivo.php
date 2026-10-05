<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__).'/../../compararCadenas.php';
require_once dirname(__FILE__).'/../../redondear.php';

function vinculacionSinFlujoEfectivo($gpo_id, $ur_id, $ue_id, $pot_id, $periodo,
                               $programatico, $claveFinanciamiento, $reporte,
                               $detalle, $mmc_id, $niveles,
                               $parametroExtra = array(), $paginado = false,
                               $nInicial = 0, $numreg = 0, $ejecsql2 = false)
{
    
    $innerjoinSubOrigen=" ";
    $paginadosql = "";
    if ($paginado == true) {

        $paginadosql = " LIMIT ".$nInicial.", ".$numreg." ";
    }

    $sqlFiltro = '';
    if ($parametroExtra['gur'] != ''){
        $sqlFiltro = "c.gpo_id = ".substr($parametroExtra['gur'], 0, 1)." AND c.ur_id = ".substr($parametroExtra['gur'],
                1, 2)." ";
    }
    
    if ($parametroExtra['clc_id'] != ''){
        $sqlFiltro .= ( ($sqlFiltro != '' ? "AND " : "")."c.clc_id = ".$parametroExtra['clc_id']." " );
    }
        
    $var_MAX=" MAX";
    if ($periodo['mesDesde'] != '') {
        $sqlFiltro .= ($sqlFiltro != '' ? "AND " : "")."c.msc_id = ".$periodo['mesDesde']." ";
        $var_MAX="";
    }

    if($claveFinanciamiento['ecf_id'] !=""){
        $sqlFiltro .= ($sqlFiltro != '' ? "AND " : "")."  ecf_clave = '".$claveFinanciamiento['ecf_id']."' ";
    }

    if($claveFinanciamiento['ter_id'] != ""){
        if($claveFinanciamiento['ter_id'] != "C"){
            $innerjoinSubOrigen=" INNER JOIN ".BD_SINPRES.".cat_SuborigenRecurso".APP_EJERCICIO_ANTERIOR." USING(cff_id,fte_id, sft_id, ori_id, sor_id) ";
            $sqlFiltro .= ($sqlFiltro != '' ? "AND " : "")." ter_id = '".$claveFinanciamiento['ter_id']."' ";
        }
    }
    
    $sql = "
            SELECT
                c.gpo_id, c.ur_id,CONCAT(c.gpo_id,c.ur_id)AS gur, c.clc_id,
                DATE(clc_fechaEnvio) AS clc_fechaEnvio, mpc_nombre, clc_concepto, ecf_clave, clc_beneficiario, clc_percepcion, clc_retencion, clc_recursoFederal,
                (c.msc_id),
                bdg_agrupador,
                bdb_agrupador,
                pg.polizaAutorizado AS polizaAutorizadog,
                pb.polizaAutorizado AS polizaAutorizadob,
                CONCAT( c.gpo_id, c.ur_id, '.', LPAD( (c.msc_id), 2, 000 ), '.', LPAD( c.clc_id, 6, 0000000 ) ) AS id
            FROM ".BD_CTRL_2025.".scp_Clc c
                INNER JOIN ".BD_CTRL_2025.".cat_ModoPagoClc USING(mpc_id)
                INNER JOIN ".BD_CTRL_2025.".enc_ClaveFinanciamiento USING(ecf_id)
                $innerjoinSubOrigen
                LEFT JOIN(
                    SELECT m.gpo_id AS gpo_id_polizaAutorizado, m.ur_id AS ur_id_polizaAutorizado, MAX(m.ue_id) AS ue_id_polizaAutorizado,
                        MAX(m.msc_id) AS msc_id_polizaAutorizado,
                        MAX(m.pma_fecha) AS fechaAutorizado,
                        GROUP_CONCAT(DISTINCT CONCAT( m.pot_id, '-', m.prf_id, m.pma_id)) AS polizaAutorizado,
                        MAX(x.pdx_gpo_id_vinculacion) AS gpo_id, MAX(x.pdx_ur_id_vinculacion) AS ur_id,
                        MAX(x.pdx_clc_id_vinculacion) AS clc_id,
                        (x.pdx_agrupador) AS bdg_agrupador
                    FROM cg_PolizaMaestro m
                        INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = 1 AND m.ur_id = 16
                        AND m.mmc_id = 'G'
                        AND m.tmc_id = 'A'
                        AND x.pdx_ejercicio = ".APP_EJERCICIO_ANTERIOR."
                    GROUP BY m.gpo_id, m.ur_id, pdx_gpo_id_vinculacion, pdx_ur_id_vinculacion, pdx_clc_id_vinculacion, pdx_agrupador
                )pg USING( gpo_id, ur_id, clc_id)
                LEFT JOIN (
                    SELECT m.gpo_id AS gpo_id_polizaCancelado, m.ur_id AS ur_id_polizaCancelado, MAX(m.ue_id) AS ue_id_polizaCancelado,
                        MAX(m.msc_id) AS msc_id_polizaAutorizado,
                        MAX(m.pma_fecha) AS fechaAutorizado,
                        GROUP_CONCAT(DISTINCT CONCAT( m.pot_id, '-', m.prf_id, m.pma_id)) AS polizaAutorizado,
                        MAX(x.pdx_gpo_id_vinculacion) AS gpo_id, MAX(x.pdx_ur_id_vinculacion) AS ur_id,
                        MAX(x.pdx_clc_id_vinculacion) AS clc_id,
                        (x.pdx_agrupador) AS bdb_agrupador
                    FROM cg_PolizaMaestro m
                        INNER JOIN cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    WHERE m.gpo_id = 1 AND m.ur_id = 16
                        AND m.mmc_id = 'B'
                        AND m.tmc_id = 'A'
                        AND x.pdx_ejercicio = ".APP_EJERCICIO_ANTERIOR."
                    GROUP BY m.gpo_id, m.ur_id, pdx_gpo_id_vinculacion, pdx_ur_id_vinculacion, pdx_clc_id_vinculacion, pdx_agrupador
                )pb USING( gpo_id, ur_id, clc_id)
            WHERE mpc_id = 3
                AND ecl_id > 6
                AND ecl_id NOT IN (20, 21)
                AND ".$sqlFiltro."
            GROUP BY gpo_id, ur_id, clc_id, bdg_agrupador,
            bdb_agrupador, pg.polizaAutorizado,
            pb.polizaAutorizado
            ORDER BY gpo_id, ur_id, clc_id, clc_fechaEnvio
;
        ";

    $conexion = new AccesoDatos(); //variable de konexion para los saldos iniciales
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar();
    $conexion->desconectar();
    
    $numerrows = 0;
    $datos = array();
    if ($paginado == true) {
        $datos = array(array(
                'id' => 'num',
                'gpo_id' => $numerrows, 
                'ur_id' => '',
                'gur' => '',
                'clc_id' => '',
                'clc_fechaEnvio' => '',
                'clc_concepto' => '',
                'clc_percepcion' => '',
                'clc_retencion' => '',
                'polizaAutorizadog' => '',
                'polizaAutorizadob' => '',
                'bdg_agrupador' => '',
                'bdb_agrupador' => ''));
    }
    while ($rowData = $conexion->extraer()) {
        $datos[$rowData['id']] = array(
            'id' => $rowData['id'],
            'gpo_id' => $rowData['gpo_id'],
            'ur_id' => $rowData['ur_id'],
            'gur' => $rowData['gpo_id'].$rowData['ur_id'],
            'clc_id' => $rowData['clc_id'],
            'clc_fechaEnvio' => $rowData['clc_fechaEnvio'],
            'clc_concepto' => $rowData['clc_concepto'],
            'clc_percepcion' => $rowData['clc_percepcion'],
            'clc_retencion' => $rowData['clc_retencion'],
            'polizaAutorizadog' => $rowData['polizaAutorizadog'],
            'polizaAutorizadob' => $rowData['polizaAutorizadob'],
            'bdg_agrupador' => $rowData['bdg_agrupador'],
            'bdb_agrupador' => $rowData['bdb_agrupador'],
            'ecf_clave' => $rowData['ecf_clave'],
            'clc_beneficiario' => $rowData['clc_beneficiario']
        );
    }
    return $datos;
}
?>
