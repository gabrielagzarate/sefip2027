<?php
/*
  Función donde se obtiene información para la formación de los estados financieros y cuenta publica.
  Devuelve un array.

  // PARÁMETROS
  gpo_id, ur_id, ue_id : La clasificación administrativa que limita la información a mostrar


 */
/* ---------------------------------------------------  CONSOLIDACION  ---------------------------------------------------------------------  */

function sql_situacionFinanciera($ent_id, $msc_trimestre, $ejercicio, $consolidado, $ent_idC = '', $sec_idC = '', $gpo_idC = '', $con_idC = '', $parametroExtra = array())
{
    $nivel = (isset($parametroExtra['nivel']) && $parametroExtra['nivel'] != '' ? $parametroExtra['nivel'] : 3);
    $ger_id = (isset($parametroExtra['ger_id']) && $parametroExtra['ger_id'] != '' ? $parametroExtra['ger_id'] : -1);
    $grupos = (isset($parametroExtra['grupos']) && $parametroExtra['grupos'] != '' ? $parametroExtra['grupos'] : '');
    $excluir = (isset($parametroExtra['excluir']) && $parametroExtra['excluir'] != '' ? $parametroExtra['excluir'] : '');
    $ue = (isset($parametroExtra['ue']) ? $parametroExtra['ue'] : "");
    $tipoReporte = (isset($parametroExtra['tipoReporte']) ? $parametroExtra['tipoReporte'] : '');
    $gpo_id = (isset($parametroExtra['gpo_id']) ? $parametroExtra['gpo_id'] : '');
    $ur_id = (isset($parametroExtra['ur_id']) ? $parametroExtra['ur_id'] : '');

    $ejercicio = ($ejercicio != "") ? "ctrl" . $ejercicio . "." : "";
    $ejercicioActual = $_SESSION[_S]['ejercicioActual'];
    $ejercicioAnterior = $ejercicioActual - 1;

    $ejerAnterior = "ctrl" . $ejercicioAnterior . ".";


    switch ($msc_trimestre) {
        case 1:
            $msc_trimestre = 'msc_id IN (0, 1, 2, 3) AND';
            break;
        case 2:
            $msc_trimestre = 'msc_id IN (0, 1, 2, 3, 4, 5, 6) AND';
            break;
        case 3:
            $msc_trimestre = 'msc_id IN (0, 1, 2, 3, 4, 5, 6, 7, 8, 9) AND';
            break;
        case 4:
            $msc_trimestre = 'msc_id IN (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12) AND';
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
            $niveles = "g.ger_cuenta, p.cgp_cuenta, r.rub_cuenta, c.cgcu_cuenta, sc.cgsc_cuenta";
            $nombreNiveles = "g.ger_nombre, p.cgp_nombre, r.rub_nombre, c.cgcu_nombre, sc.cgsc_nombre";
            $sqlGroup = "g.ger_id, p.cgp_id, r.rub_id, c.cgcu_id, sc.cgsc_id";
            break;
        default:
            $niveles = "";
            $nombreNiveles = "";
            $sqlGroup = "";
            break;
    }

    switch ($ger_id) {
        case 1:
            $importe = 'SUM(IFNULL(debe, 0)) - SUM(IFNULL(haber, 0)) AS debe, 0 AS haber,';
            $nat_id = "'D'";
            break;
        case 2:
            $importe = '0 AS debe, SUM(IFNULL(haber, 0)) - SUM(IFNULL(debe, 0)) AS haber,';
            $nat_id = "'H'";
            break;
        case 3:
            $importe = '0 AS debe, SUM(IFNULL(haber, 0)) - SUM(IFNULL(debe, 0)) AS haber,';
            $nat_id = "'H'";
            break;
        case 4:
            $importe = '0 AS debe, SUM(IFNULL(haber, 0)) - SUM(IFNULL(debe, 0)) AS haber,';
            $nat_id = "'H'";
            break;
        case 5:
            $importe = 'SUM(IFNULL(debe, 0)) - SUM(IFNULL(haber, 0)) AS debe, 0 AS haber,';
            $nat_id = "'D'";
            break;
    }

    // Si limitaron por grupo o excluyeron alguna UR
    $otroWhere = "";
    if ($tipoReporte == 3 && $_SESSION[_S]['nvj_id'] >= 5) {
        // Si limitaron por grupo o excluyeron alguna UR
        if ($grupos != "") {
            $otroWhere .= "  WHERE " . ($ejercicio == $ejerAnterior ? "v.gpo_id_" . $ejercicioAnterior : "gpo_id") . " IN ( " . rtrim($grupos) . " ) AND ";

            // Si excluyeron a alguna UR
            if ($excluir != "") {
                $otroWhere .= " " . ($grupos != "" ? "" : " WHERE ") . " CONCAT( " . ($ejercicio == "ctrl" . $ejercicioAnterior . "." ? "v.gpo_id_" . $ejercicioAnterior . "" : "gpo_id") . "," . ($ejercicio != "" ? "v.ur_id_" . $ejercicioAnterior : "ur_id") . " ) NOT IN ( " . rtrim($excluir) . " ) AND ";
            }
            // Si no usaron las campos especiales, es un where estandar
        }
    }


    $orden = "ASC";
    $tablas = '
            RIGHT JOIN ' . $ejercicio . 'cat_CgSubcuenta sc USING( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
            RIGHT JOIN ' . $ejercicio . 'cat_CgCuenta c USING( ger_id, cgp_id, rub_id, cgcu_id )
            RIGHT JOIN ' . $ejercicio . 'cat_Rubro r USING( ger_id, cgp_id, rub_id )
            RIGHT JOIN ' . $ejercicio . 'cat_CgGrupo p USING( ger_id, cgp_id )
            RIGHT JOIN ' . $ejercicio . 'cat_Genero g USING( ger_id )
        ';
 
 
    $parametro = [
        ':ger' => $ger_id
    ];

    if ($gpo_id != '' && $ur_id != '') {
        $sql = '';
        switch ($ejercicio) {            
            case $ejerAnterior:
                $sql = "
                    SELECT
                    $niveles AS id,
                    $nombreNiveles,
                    $importe
                    $nat_id AS nat_id,
                    $niveles AS cuenta, $nombreNiveles AS nombre
                    FROM (
                        SELECT ger_id, cgp_id, rub_id, cgcu_id, cgsc_id , SUM( pdx_debe ) AS debe, SUM( pdx_haber ) AS haber,  MAX(nat_id) AS nat_id
                        FROM " . $ejerAnterior . "cg_PolizaDetalleAuxiliar d
                        INNER JOIN " . $ejerAnterior . "cg_PolizaMaestro USING(gpo_id,ur_id,ue_id,msc_id,pot_id,prf_id,pma_id)
                            LEFT JOIN ".BD_SINPRES.".vin_UnidadResponsable_" . $ejercicioAnterior . "_" . $ejercicioActual . " v ON v.gpo_id_" . $ejercicioAnterior . " = d.gpo_id AND v.ur_id_" . $ejercicioAnterior . " = d.ur_id
                            " . ($ue != '' ? "LEFT JOIN ".BD_SINPRES.".vin_UnidadEjecutora_" . $ejercicioAnterior . "_" . $ejercicioActual . " u ON u.gpo_id_" . $ejercicioAnterior . " = d.gpo_id AND u.ur_id_" . $ejercicioAnterior . " = d.ur_id AND u.ue_id_" . $ejercicioAnterior . " = d.ue_id " : "") . "
                            " . ($tipoReporte == 2 ? ($_SESSION[_S]['nvj_id'] < 6 ? " INNER JOIN ctrl" . $ejercicioAnterior . ".cat_CargaTrabajo t ON t.gpo_id = v.gpo_id_" . $ejercicioActual . " AND t.ur_id = v.ur_id_" . $ejercicioActual
                        : "") . " WHERE " : (($tipoReporte == 3) ? ($_SESSION[_S]['nvj_id'] < 5 ?
                        " WHERE " : $otroWhere) : "
                        WHERE v.gpo_id_" . $ejercicioAnterior . " = $gpo_id AND v.ur_id_" . $ejercicioAnterior . " = $ur_id " . ($ue != '' ? " AND u.ue_id_" . $ejercicioAnterior . " = " . $ue : "") . "
                            AND ")) . "
                            " . " msc_id < 13 " . "
                            AND ger_id = :ger
                            AND epl_id NOT IN ( 'C', 'H' )
                        GROUP BY ger_id, cgp_id, rub_id, cgcu_id, cgsc_id
                    )o
                    " . $tablas . "
                    WHERE g.ger_id = :ger 
                    GROUP BY $sqlGroup
                    ORDER BY sc.cgsc_cuenta " . $orden . "
                ";                
                break;
            default:
                $sql = " SELECT 
                    $niveles AS id,
                    $nombreNiveles,
                    $importe
                    $nat_id AS nat_id,
                    $niveles AS cuenta, $nombreNiveles AS nombre
                    FROM (
                        SELECT ger_id, cgp_id, rub_id, cgcu_id, cgsc_id , SUM( pdx_debe ) AS debe, SUM( pdx_haber ) AS haber, MAX(nat_id) AS nat_id
                        FROM " . $ejercicio  . "cg_PolizaDetalleAuxiliar d
                        INNER JOIN " . $ejercicio . "cg_PolizaMaestro USING(gpo_id,ur_id,ue_id,msc_id,pot_id,prf_id,pma_id)
                        " . ($tipoReporte == 2 ? ($_SESSION[_S]['nvj_id'] < 6 ? " INNER JOIN " . $ejercicio . "cat_CargaTrabajo t USING(gpo_id,ur_id)
                        " : "") . " WHERE " : (($tipoReporte == 3) ? ($_SESSION[_S]['nvj_id'] < 5 ?
                        " WHERE " : $otroWhere) : "
                        WHERE gpo_id = $gpo_id AND ur_id = $ur_id  ".($ue != '' ? " AND d.ue_id = " . $ue . "" : "") . "
                            AND ")) . "
                            $msc_trimestre
                            ger_id = :ger
                            AND epl_id NOT IN ( 'C', 'H' )
                        GROUP BY ger_id, cgp_id, rub_id, cgcu_id, cgsc_id
                    )o
                    " . $tablas . "
                    WHERE g.ger_id= :ger
                    GROUP BY $sqlGroup
                    ORDER BY sc.cgsc_cuenta " . $orden . "";
                break;
        }

        $contabilidad = new AccesoDatos();
        $contabilidad->conectar();
        $contabilidad->setSql($sql);
        $contabilidad->consultar($parametro);
        $contabilidad->desconectar();

        $datos = array();

        while ($rowContabilidad = $contabilidad->extraer()) {
            $datos['C.' . $rowContabilidad['id']] = array(
                'id' => $rowContabilidad['id'],
                'ger_cuenta' => $nivel == 1 ? $rowContabilidad['cuenta'] : $rowContabilidad['ger_cuenta'],
                'ger_nombre' => $rowContabilidad['ger_nombre'],
                'cgp_cuenta' => $nivel == 2 ? $rowContabilidad['cuenta'] : $rowContabilidad['cgp_cuenta'],
                'cgp_nombre' => $rowContabilidad['cgp_nombre'],
                'rub_cuenta' => $nivel == 3 ? $rowContabilidad['cuenta'] : $rowContabilidad['rub_cuenta'],
                'rub_nombre' => $rowContabilidad['rub_nombre'],
                'cgcu_cuenta' => isset($nivel) == 4 ? $rowContabilidad['cuenta'] :  $rowContabilidad['cgcu_cuenta'],
                'cgcu_nombre' => isset($rowContabilidad['cgcu_nombre']),
                'cgsc_cuenta' => isset($nivel) == 5 ? $rowContabilidad['cuenta'] : $rowContabilidad['cgsc_cuenta'],
                'cgsc_nombre' => isset($rowContabilidad['cgsc_nombre']),
                'cuenta' => $rowContabilidad['cuenta'],
                'nombre' => $rowContabilidad['nombre'],
                'debe' => $rowContabilidad['debe'],
                'haber' => $rowContabilidad['haber'],
                'nat_id' => $rowContabilidad['nat_id'],
                'nivel' => $nivel
            );
        }

        return isset($datos) ? $datos : false;
    }
}
