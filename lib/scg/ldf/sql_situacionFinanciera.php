<?php
/*
  Función donde se obtiene información para la formación de los estados financieros y cuenta publica.
  Devuelve un array.

  // PARÁMETROS
  gpo_id, ur_id, ue_id : La clasificación administrativa que limita la información a mostrar


 */
/* ---------------------------------------------------  CONSOLIDACION  ---------------------------------------------------------------------  */

function sql_situacionFinanciera(
    $ent_id,
    $msc_trimestre,
    $ejercicio,
    $consolidado,
    $ent_idC = '',
    $sec_idC = '',
    $gpo_idC = '',
    $con_idC = '',
    $noAplica = false,
    $parametroExtra = array()
) {
    $nivel = (isset($parametroExtra['nivel']) && $parametroExtra['nivel'] != '' ? $parametroExtra['nivel'] : 3);
    $ger_id = (isset($parametroExtra['ger_id']) && $parametroExtra['ger_id'] != '' ? $parametroExtra['ger_id'] : -1);
    $innerjoinConsolidado = "";


    switch($ejercicio){
 
        case 2022:
            $db = '2022';
            $tabla = "Mensual_2022";
            $prefijo = "msl";
            switch ($msc_trimestre) {
                case 1:
                    $msc_trimestre = "AND a.msc_id IN (0, 1, 2, 3)";
                    break;
                case 2:
                    $msc_trimestre = "AND a.msc_id IN (0, 1, 2, 3, 4, 5, 6)";
                    break;
                case 3:
                    $msc_trimestre = "AND a.msc_id IN (0, 1, 2, 3, 4, 5, 6, 7, 8, 9)";
                    break;
                case 4:
                    $msc_trimestre = "AND a.msc_id IN (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12)";
                    break;
            }
            break;
        case 2020:
            $db = '2020';
            $tabla = "Anual_2020";
            $prefijo = "anl";
            $msc_trimestre = "";
            break;
        case 2019:
            $db = '2019';
            $tabla = "Anual_2019";
            $prefijo = "anl";
            $msc_trimestre = "";
            break;
        default:
            $db = '';
            $tabla = "";
            $prefijo = "";
            $msc_trimestre = "";
            break;
    }

    switch ($nivel) {
        case 1:
            $niveles = "g.ger_cuenta";
            $nombreNiveles = "g.ger_nombre";
            $sqlGroup = "g.ger_id";
            break;
        case 2:
            $niveles = "g.ger_cuenta, p.cgp_cuenta";
            $nombreNiveles = "g.ger_nombre, p.cgp_nombre";
            $sqlGroup = "g.ger_id, p.cgp_id";
            break;
        case 3:
            $niveles = "g.ger_cuenta, p.cgp_cuenta, r.rub_cuenta";
            $nombreNiveles = "g.ger_nombre, p.cgp_nombre, r.rub_nombre";
            $sqlGroup = "g.ger_id, p.cgp_id, r.rub_id";
            break;
        case 4:
            $niveles = "g.ger_cuenta, p.cgp_cuenta, r.rub_cuenta, c.cgcu_cuenta";
            $nombreNiveles = "g.ger_nombre, p.cgp_nombre, r.rub_nombre, c.cgcu_nombre";
            $sqlGroup = "g.ger_id, p.cgp_id, r.rub_id, c.cgcu_id";
            break;
        case 5:
            $niveles = "g.ger_cuenta, p.cgp_cuenta, r.rub_cuenta, c.cgcu_cuenta, s.cgsc_cuenta";
            $nombreNiveles = "g.ger_nombre, p.cgp_nombre, r.rub_nombre, c.cgcu_nombre, s.cgsc_nombre";
            $sqlGroup = "g.ger_id, p.cgp_id, r.rub_id, c.cgcu_id, s.cgsc_id";
            break;
        default:
            $niveles = "";
            $nombreNiveles = "";
            $sqlGroup = "";
            break;
    }
    switch ($ger_id) {
        case 1:
            $importe = 'SUM(IFNULL(a.debe, 0)) - SUM(IFNULL(a.haber, 0)) AS debe, 0 AS haber,';
            $nat_id = "'D'";
            break;
        case 2:
            $importe = '0 AS debe, SUM(IFNULL(a.haber, 0)) - SUM(IFNULL(a.debe, 0)) AS haber,';
            $nat_id = "'H'";
            break;
        case 3:
            $importe = '0 AS debe, SUM(IFNULL(a.haber, 0)) - SUM(IFNULL(a.debe, 0)) AS haber,';
            $nat_id = "'H'";
            break;
        case 4:
            $importe = '0 AS debe, SUM(IFNULL(a.haber, 0)) - SUM(IFNULL(a.debe, 0)) AS haber,';
            $nat_id = "'H'";
            break;
        case 5:
            $importe = 'SUM(IFNULL(a.debe, 0)) - SUM(IFNULL(a.haber, 0)) AS debe, 0 AS haber,';
            $nat_id = "'D'";
            break;
    }

    if ($consolidado) {
        if ($ent_idC != '') {
            $gurwhere = "AND e.ent_id = $ent_idC";
        } else if ($sec_idC != '') {
            $innerjoinConsolidado = "INNER JOIN ldf.cat_SectorLDF sec USING(sec_id, gpo_id)";
            $gurwhere = "AND sec.tsc_id = '$sec_idC'";
        } else if ($gpo_idC != '') {
            $innerjoinConsolidado = "INNER JOIN ldf.cat_SectorLDF sec USING(sec_id, gpo_id)";
            $gurwhere = "AND sec.gpo_id = $gpo_idC";
        } else if ($con_idC != '') {
            $innerjoinConsolidado = "INNER JOIN ldf.cat_SectorLDF sec USING(sec_id, gpo_id)";
            $gurwhere = "";
        } else {
            $gurwhere = "AND e.ent_id = $ent_idC";
        }
    } else {
        $gurwhere = "AND e.ent_id = $ent_id";
    }

    $sql = "
        SELECT $niveles AS id,
            $nombreNiveles,
            $importe
            $nat_id AS nat_id,
            $niveles AS cuenta, $nombreNiveles AS nombre
        FROM ctrl" . $db . ".cat_Genero g
            INNER JOIN ctrl" . $db . ".cat_CgGrupo p USING(ger_id)
            INNER JOIN ctrl" . $db . ".cat_Rubro r USING(ger_id, cgp_id)
            INNER JOIN ctrl" . $db . ".cat_CgCuenta c USING(ger_id, cgp_id, rub_id)
            INNER JOIN ctrl" . $db . ".cat_CgSubcuenta s USING(ger_id, cgp_id, rub_id, cgcu_id)
            LEFT JOIN (
                SELECT a.ger_id, a.grp_id, a.rub_id, a.cta_id, a.sct_id,
                    SUM(IFNULL(a." . $prefijo . "_debe, 0)) AS debe, SUM(IFNULL(a." . $prefijo . "_haber, 0)) AS haber
                FROM ldf.vin_EntePublicoLDF e
                    $innerjoinConsolidado
                    INNER JOIN ldf.blz_" . $tabla . " a USING(gpo_id, ur_id)
                WHERE e.ejr_id = " . $db . "
                    AND a.ger_id = $ger_id
                    $gurwhere
                    $msc_trimestre
                GROUP BY a.ger_id, a.grp_id, a.rub_id, a.cta_id, a.sct_id
            )a ON s.ger_id = a.ger_id AND s.cgp_id = a.grp_id AND s.rub_id = a.rub_id AND s.cgcu_id = a.cta_id AND s.cgsc_id = a.sct_id
        WHERE g.ger_id = $ger_id
        GROUP BY $sqlGroup
    ";
    //echo "<pre>";print_r($sql);echo "</pre>";//die();

    $contabilidad = new AccesoDatos(); //variable de konexion para los saldos iniciales

    $contabilidad->conectar();
    $contabilidad->setSql($sql);
    $contabilidad->consultar();
    $contabilidad->desconectar();

    $datos = array();

    while ($rowContabilidad = $contabilidad->extraer()) {
        $datos['C.' . $rowContabilidad['id']] = array(
            'id' => $rowContabilidad['id'],
            'ger_cuenta' => $rowContabilidad['ger_cuenta'],
            'ger_nombre' => $rowContabilidad['ger_nombre'],
            'cgp_cuenta' => $rowContabilidad['cgp_cuenta'],
            'cgp_nombre' => $rowContabilidad['cgp_nombre'],
            'rub_cuenta' => $rowContabilidad['rub_cuenta'],
            'rub_nombre' => $rowContabilidad['rub_nombre'],
            'cgcu_cuenta' => $rowContabilidad['cgcu_cuenta'],
            'cgcu_nombre' => $rowContabilidad['cgcu_nombre'],
            'cgsc_cuenta' => $rowContabilidad['cgsc_cuenta'],
            'cgsc_nombre' => $rowContabilidad['cgsc_nombre'],
            'cuenta' => $rowContabilidad['cuenta'],
            'nombre' => $rowContabilidad['nombre'],
            'debe' => $rowContabilidad['debe'],
            'haber' => $rowContabilidad['haber'],
            'nat_id' => $rowContabilidad['nat_id'],
            'nivel' => $nivel
        );
        //echo $rowContabilidad['id']."</br>";
    }

    /*if(count($datos) == 0){
        $datos['vacio'] = array(
            'id' => '',
            'ger_id' => '',
            'ger_nombre' => '',
            'cgp_id' => '',
            'cgp_nombre' => '',
            'rub_id' => '',
            'rub_nombre' => '',
            'cgcu_id' => '',
            'cgcu_nombre' => '',
            'cgsc_id' => '',
            'cgsc_nombre' => '',
            'debe' => '',
            'haber' => '',
            'nat_id' => ''
        );
    }// */

    //echo "<pre>C-";print_r($datos);echo "</pre>";die();
    return isset($datos) ? $datos : FALSE;
}
