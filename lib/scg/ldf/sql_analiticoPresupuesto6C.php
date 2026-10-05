<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sql_analiticoPresupuesto6C
 *
 * @author Faustino Cruz, Israel <israel.faustino@finanzasoaxaca.gob.mx>
 * Created: 24-abr-2019
 * Comment: 
 * 
 * */
function sql_analiticoPresupuesto6C(
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
            $id = "cff_id";
            $nombre = "cff_nombre";
            $sqlGroup = "cff_id";
            break;
        case 2:
            $id = "cff_id, '.', fin_id";
            $nombre = "fin_nombre";
            $sqlGroup = "cff_id, fin_id";
            break;
        case 3:
            $id = "cff_id, '.', fin_id, '.', fun_id";
            $nombre = "fun_nombre";
            $sqlGroup = "cff_id, fin_id, fun_id";
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
            cff_id AS cff_cuenta, cff_nombre,
            CONCAT(cff_id, '.', fin_id) AS fin_cuenta, fin_nombre,
            CONCAT(cff_id, '.', fin_id, '.', fun_id) AS fun_cuenta, fun_nombre,
            SUM(IFNULL(aprobado, 0)) AS aprobado,
            SUM(IFNULL(ampred, 0)) AS ampred,
            SUM(IFNULL(modificado, 0)) AS modificado,
            SUM(IFNULL(devengado, 0)) AS devengado,
            SUM(IFNULL(pagado, 0)) AS pagado,
            SUM(IFNULL(subejercicio, 0)) AS subejercicio,
            CONCAT($id) AS cuenta, $nombre AS nombre
        FROM (
            SELECT 
                c.cff_id, c.cff_nombre,
                f.fin_id, f.fin_nombre,
                n.fun_id, n.fun_nombre,
                IFNULL(p.aprobado, 0) AS aprobado,
                IFNULL(p.ampred, 0) AS ampred,
                IFNULL(p.modificado, 0) AS modificado,
                IFNULL(p.devengado, 0) AS devengado,
                IFNULL(p.pagado, 0) AS pagado,
                IFNULL(p.subejercicio, 0) AS subejercicio
            FROM cat_Finalidad f
                INNER JOIN cat_Funcion n USING(fin_id)
                INNER JOIN enc_Funcional l USING(fin_id, fun_id)
                INNER JOIN sinpres.cat_ClasificadorFuenteFinanciamiento c
                LEFT JOIN vin_Programatico v USING(efn_id)
                LEFT JOIN enc_Programatico g USING(epg_id)
                LEFT JOIN (
                    SELECT p.cff_id, p.epg_id,
                        SUM(a.pre_aprobado) AS aprobado,
                        SUM(v.pre_ampliacion) - SUM(v.pre_reduccion) AS ampred,
                        SUM(v.pre_vigente) AS modificado,
                        SUM(d.pre_devengado) AS devengado,
                        SUM(s.pre_pagado) AS pagado,
                        SUM(v.pre_vigente) - SUM(d.pre_devengado) AS subejercicio
                    FROM ldf.ldf_presupuesto p
                        INNER JOIN ldf.vin_EntePublicoLDF USING(gpo_id, ur_id, ejr_id)
                        LEFT JOIN ldf.ldf_presupuestoAprobado a USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id, ejr_id, msc_trimestre)
                        LEFT JOIN ldf.ldf_presupuestoVigente v USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id, ejr_id, msc_trimestre)
                        LEFT JOIN ldf.ldf_presupuestoDevengado d USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id, ejr_id, msc_trimestre)
                        LEFT JOIN ldf.ldf_presupuestoPagado s USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id, ejr_id, msc_trimestre)
                    $innerjoinConsolidado
                WHERE ejr_id = $ejercicio
                    AND msc_trimestre = $msc_trimestre
                    $gurwhere
                    GROUP BY p.cff_id, p.epg_id
                )p USING(cff_id, epg_id)
        )f 
        GROUP BY $sqlGroup
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
            'fin_cuenta' => $rowPresupuesto['fin_cuenta'],
            'fin_nombre' => $rowPresupuesto['fin_nombre'],
            'fun_cuenta' => $rowPresupuesto['fun_cuenta'],
            'fun_nombre' => $rowPresupuesto['fun_nombre'],
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
            'cap_id' => '',
            'cap_nombre' => '',
            'cto_id' => '',
            'cto_nombre' => '',
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