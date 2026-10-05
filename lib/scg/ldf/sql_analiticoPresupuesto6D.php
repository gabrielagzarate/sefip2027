<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sql_analiticoPresupuesto6D
 *
 * @author Faustino Cruz, Israel <israel.faustino@finanzasoaxaca.gob.mx>
 * Created: 24-abr-2019
 * Comment: 
 * 
 * */
function sql_analiticoPresupuesto6D(
    $ent_id, $msc_trimestre, $ejercicio,
    $consolidado, $ent_idC = '',
    $sec_idC = '', $gpo_idC = '', $con_idC = '',
    $noAplica = false, $parametroExtra = array()
){
    //put your code here
    $nivel = (isset($parametroExtra['nivel']) && $parametroExtra['nivel'] != '' ? $parametroExtra['nivel']:3);
    $innerjoinConsolidado = "";
    
    switch($ejercicio){
        case 2022:
            $db = 'ctrl2023';
            $tabla = '';
            break;
        case 2020:
            $db = 'ctrl2020';
            $tabla = '';
            break;
        case 2019:
            $db = 'ctrl2019';
            $tabla = '';
            break;
        case 2018:
            $db = 'ctrl2018';
            $msc_trimestre = 4;
            $tabla = '';
            break;
        case 2017:
            $db = 'ctrl2018';
            $msc_trimestre = 4;
            $tabla = '';
            break;
        default :
            $db = '';
            $msc_trimestre = -1;
            $tabla = '';
            break;
    }
    
    switch($nivel){
        case 1:
            $id = "f.cff_id";
            $nombre = "f.cff_nombre";
            $sqlGroup = "cff_id";
            break;
        case 2:
            $id = "cff_id, '.', IFNULL(pg_id, 'XXX')";
            $nombre = "IFNULL(p.pg_nombre, 'UNDEFINED')";
            $sqlGroup = "cff_id, p.pg_id";
            break;
        case 3:
            $id = "cff_id, '.', IFNULL(pg_id, 'XXX'), '.', IFNULL(pe_id, 'XXX')";
            $nombre = "IFNULL(p.pe_nombre, 'UNDEFINED')";
            $sqlGroup = "cff_id, p.pg_id, e.pe_id";
            break;
        default:
            $id = "";
            $nombre = "";
            $sqlGroup = "";
            break;
    }
    
    if($consolidado){
        if($ent_idC != ''){
            $gurwhere = "AND ent_id = $ent_idC";
        }else if($sec_idC != ''){
            $innerjoinConsolidado = "INNER JOIN ldf.cat_SectorLDF sec USING(sec_id)";
            $gurwhere = "AND sec.tsc_id = '$sec_idC'";
        }else if($gpo_idC != ''){
            $innerjoinConsolidado = "INNER JOIN ldf.cat_SectorLDF sec USING(sec_id)";
            $gurwhere = "AND sec.gpo_id = $gpo_idC";
        }else if($con_idC != ''){
            $innerjoinConsolidado = "INNER JOIN ldf.cat_SectorLDF sec USING(sec_id)";
            //$gurwhere = "AND sec.tsc_id = '$con_idC'";
            $gurwhere = "";
        }else{
            $gurwhere = "AND ent_id = $ent_idC";
        }
    }else{
        $gurwhere = "AND ent_id = $ent_id";
    }
    
    $sql = "
        SELECT CONCAT($id) AS id,
            f.cff_id AS cff_cuenta, f.cff_nombre,
            CONCAT(f.cff_id, '.', IFNULL(p.pg_id, 'XXX')) AS pg_cuenta, IFNULL(p.pg_nombre, 'UNDEFINED') AS pg_nombre,
            CONCAT(f.cff_id, '.', IFNULL(p.pg_id, 'XXX'), '.', IFNULL(p.pe_id, 'XXX')) AS pe_cuenta, IFNULL(p.pe_nombre, 'UNDEFINED') AS pe_nombre,
            IFNULL(p.aprobado, 0) AS aprobado,
            IFNULL(p.ampred, 0) AS ampred,
            IFNULL(p.modificado, 0) AS modificado,
            IFNULL(p.devengado, 0) AS devengado,
            IFNULL(p.pagado, 0) AS pagado,
            IFNULL(p.subejercicio, 0) AS subejercicio,
            CONCAT($id) AS cuenta, $nombre AS nombre
        FROM sinpres.cat_ClasificadorFuenteFinanciamiento f
            LEFT JOIN (
                SELECT p.cff_id,
                    MAX(g.pg_id) AS pg_id, MAX(g.pg_nombre) AS pg_nombre,
                    MAX(e.pe_id) AS pe_id, MAX(e.pe_nombre) AS pe_nombre,
                    SUM(a.pre_aprobado) AS aprobado,
                    SUM(v.pre_ampliacion) - SUM(v.pre_reduccion) AS ampred,
                    SUM(v.pre_vigente) AS modificado,
                    SUM(d.pre_devengado) AS devengado,
                    SUM(s.pre_pagado) AS pagado,
                    SUM(v.pre_vigente) - SUM(d.pre_devengado) AS subejercicio
                FROM ldf.ldf_presupuesto p
                    INNER JOIN ctrl2020.cat_PartidaGenerica g USING(pg_id)
                    INNER JOIN ctrl2020.cat_PartidaEspecifica e USING(pg_id, pe_id)
                    INNER JOIN ldf.vin_EntePublicoLDF USING(gpo_id, ur_id, ejr_id)
                    LEFT JOIN ldf.ldf_presupuestoAprobado a USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id, ejr_id, msc_trimestre)
                    LEFT JOIN ldf.ldf_presupuestoVigente v USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id, ejr_id, msc_trimestre)
                    LEFT JOIN ldf.ldf_presupuestoDevengado d USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id, ejr_id, msc_trimestre)
                    LEFT JOIN ldf.ldf_presupuestoPagado s USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id, ejr_id, msc_trimestre)
                    $innerjoinConsolidado
                WHERE ejr_id = $ejercicio
                    AND msc_trimestre = $msc_trimestre
                    $gurwhere
                    AND clg_id = 1
                GROUP BY $sqlGroup
            )p USING(cff_id)
        ORDER BY f.cff_id, p.pg_id, p.pe_id
    ";
    //echo "<pre>";print_r($sql);echo "</pre>";die();
    
    $presupuesto = new AccesoDatos(); //variable de konexion para los saldos iniciales
    
    $presupuesto->conectar();
    $presupuesto->setSql($sql);
    $presupuesto->consultar();
    $presupuesto->desconectar();
    
    $datos = array();
    
    while($rowPresupuesto = $presupuesto->extraer()){
        $datos['P.'.$rowPresupuesto['id']] = array(
            'id' => $rowPresupuesto['id'],
            'cff_cuenta' => $rowPresupuesto['cff_cuenta'],
            'cff_nombre' => $rowPresupuesto['cff_nombre'],
            'pg_cuenta' => $rowPresupuesto['pg_cuenta'],
            'pg_nombre' => $rowPresupuesto['pg_nombre'],
            'pe_cuenta' => $rowPresupuesto['pe_cuenta'],
            'pe_nombre' => $rowPresupuesto['pe_nombre'],
            'aprobado' => $rowPresupuesto['aprobado'],
            'ampred' => $rowPresupuesto['ampred'],
            'modificado' => $rowPresupuesto['modificado'],
            'devengado' => $rowPresupuesto['devengado'],
            'pagado' => $rowPresupuesto['pagado'],
            'subejercicio' => $rowPresupuesto['subejercicio'],
            'cuenta' => $rowPresupuesto['cuenta'],
            'nombre' => $rowPresupuesto['nombre'],
            'nivel' => $nivel
        );
        //echo $rowPresupuesto['id']."</br>";
    }
    
    /*if(count($datos) == 0){
        $datos['vacio'] = array(
            'id' => '',
            'ur_clave' => '',
            'ur_nombre' => '',
            'cff_id' => '',
            'cff_nombre' => '',
            'clg_id' => '',
            'clg_nombre' => '',
            'pg_id' => '',
            'pg_nombre' => '',
            'aprobado' => '',
            'ampred' => '',
            'modificado' => '',
            'devengado' => '',
            'pagado' => '',
            'subejercicio' => ''
        );
    }// */
    
    //echo "<pre>";print_r($datos);echo "</pre>";die();
    return isset($datos) ? $datos : FALSE;
}

?>