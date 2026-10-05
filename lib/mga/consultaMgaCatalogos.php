<?php
    /**
     * Description of function consultaMgaCatalogos
     *
     * @author ?
     * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
     * Created: 20-ago-2018
     * Comment: -----
     * Objective: Consultar datos en la base de datos de acuerdo al modulo que se requiera, asignacion pg-ur.
     * Regla de Negocio: -----
     * 
     * */
    
    function consultaMgaCatalogos($gpo_id, $ur_id, $ue_id, $epg_id, $ecf_id, $ccd_id)
    {
        switch ($ccd_id) {
            case 2:
                $gpo = substr($gpo_id, 0, 1);
                $where_subquery = " p.gpo_id = $gpo AND p.ur_id = $ur_id ";
                if ($_POST['buscar'] != "") {
                    $where_subquery.= 'AND (pg.pg_id LIKE "%' . $_POST['buscar'] . '%" OR pg.pg_nombre LIKE "%' . $_POST['buscar'] . '%")';
                }
            break;
        }
                
        switch ($ccd_id) {
            case 2:
                $sql = "
                    SELECT CONCAT(pg.pg_id, ' ') AS PARTIDA, 
                        pg.pg_nombre AS DESCRIPCION , 
                        CASE WHEN ori.ccd_id IS NULL #AND pe.ccd_id IS NULL 
                        THEN '' ELSE 'Si' END AS USADA,
                        9 AS nivel
                    FROM cat_PartidaGenerica pg
                    INNER JOIN enc_PartGenUr p USING(pg_id)
                    LEFT JOIN presupuesto pre USING(gpo_id, ur_id, pg_id)
                    LEFT JOIN presupuestoOriginal ori USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                    LEFT JOIN enc_PartEspCed pe USING(pg_id)
                    WHERE ". $where_subquery ."
                    GROUP BY pg.pg_id,usada
                    ORDER BY pg.pg_id;
                ";
                break;
        }
        
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar();
        $conexion->desconectar();
        
        $datos = array();
        while ($row = $conexion->extraer()) {
            $dato = array('nivel'=>$row['nivel']);
            switch ($ccd_id) {
                case 2:
                    $dato['partida'] = $row['PARTIDA'];
                    $dato['descripcion'] = $row['DESCRIPCION'];
                    $dato['usada'] = $row['USADA'];
                    break;
                default:
                    echo 'kym';
                break;
            }
            array_push($datos, $dato);
        }

        if (count($datos) == 0) {
            $dato = array(
                'gpo_id'=>$gpo_id,
                'ur_id'=>$ur_id,
                'ur_clave'=>$gpo_id.$ur_id,
                'ur_nombre'=>'',
                'ue_id'=>'',
                'ue_nombre'=>'',
                'epg_id'=>$epg_id,
                'prg_id'=>'',
                'prg_nombre'=>'',
                'spr_id'=>'',
                'spr_nombre'=>'',
                'py_id'=>'',
                'py_nombre'=>'',
                'oa_id'=>'',
                'oa_nombre'=>'',
                'pg_id'=>'',
                'pg_nombre'=>'', 
                'pe_id'=>'',
                'pe_nombre'=>'',
                'ecf_id'=>$ecf_id,
                'ter_id'=>'',
                'ter_nombre'=>'',
                'cfi_clave'=>'',
                'cfi_nombre'=>',,',
                'ced_id'=>'',
                'total'=>0.00,
                'nivel'=>1
            );
            array_push($datos, $dato);
        }
        return $datos;
    }
?>