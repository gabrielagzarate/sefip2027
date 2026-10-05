<?php
/**
 * Description of function consultaClaveFinanciamiento
 *
 * author: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 05-Sep-2019
 * Comment: -----
 * Objective: Consulta datos de acuerdo a la opcion requerida, clave financiamiento.
 * Regla de Negocio: -----
 *
 * */
    
function consultaClaveFinanciamiento($ccd_id, $primeraParteClave, $segundaParteClave, $tipoRecurso = false)
{
    $params = [];
    switch ($ccd_id) {
        case 0:
            $sqlExtra = "";
            if (!empty($primeraParteClave) && !empty($segundaParteClave)) {
                array_push($params, '%' . $primeraParteClave . $segundaParteClave . '%');
                $sqlExtra = "HAVING llave LIKE ? ";
            } elseif (!empty($primeraParteClave) && empty($segundaParteClave)) {
                array_push($params, '%' . $primeraParteClave . '%');
                $sqlExtra = "HAVING clasificadorFF LIKE ? ";
            } elseif (empty($primeraParteClave) && !empty($segundaParteClave)) {
                array_push($params, '%' . $segundaParteClave . '%');
                $sqlExtra="HAVING fte.fte_id LIKE ? ";
            }
            array_unshift($params, APP_EJERCICIO);
            $sql = "SELECT CONCAT(fte.cff_id) AS clasificadorFF, CONCAT(fte.cff_id, fte.fte_id) AS llave,
                    fte.cff_id, fte.fte_id, fte.fte_nombre,
                    cff.cff_nombre
                FROM " . BD_SINPRES . ".cat_Fuente" . APP_EJERCICIO . " fte
                RIGHT JOIN " . BD_SINPRES . ".cat_ClasificadorFuenteFinanciamiento cff USING(cff_id)
                WHERE fte.fte_ejercicio = ?
                GROUP BY fte.cff_id, fte.fte_id, fte.fte_nombre, cff.cff_nombre
                $sqlExtra
                ORDER BY llave ASC";
            break;
        case 1:
            $sqlExtra="";
            if (!empty($primeraParteClave) && !empty($segundaParteClave)) {
                array_push($params, '%' . $primeraParteClave . $segundaParteClave . '%');
                $sqlExtra = "HAVING llave LIKE ? ";
            } elseif (!empty($primeraParteClave) && empty($segundaParteClave)) {
                array_push($params, '%' . $primeraParteClave . '%');
                $sqlExtra = "HAVING fuente LIKE ? ";
            } elseif (empty($primeraParteClave) && !empty($segundaParteClave)) {
                array_push($params, '%' . $segundaParteClave . '%');
                $sqlExtra = "HAVING sft.sft_id LIKE ? ";
            }
            array_unshift($params, APP_EJERCICIO);
            array_unshift($params, APP_EJERCICIO);
            $sql ="SELECT CONCAT(sft.cff_id, sft.fte_id) AS fuente, CONCAT(sft.cff_id, sft.fte_id, sft.sft_id) AS llave,
                    sft.cff_id, sft.fte_id, sft.sft_id, sft.sft_nombre,
                    fte.fte_nombre
                FROM " . BD_SINPRES . ".cat_Subfuente" . APP_EJERCICIO . " sft
                RIGHT JOIN " . BD_SINPRES . ".cat_Fuente" . APP_EJERCICIO . " fte USING(cff_id,fte_id)
                WHERE fte.fte_ejercicio = ?  AND sft.sft_ejercicio = ?
                GROUP BY fte.cff_id, fte.fte_id, sft.sft_id,sft.sft_nombre, fte.fte_nombre
                $sqlExtra
                ORDER BY llave ASC";
            break;
        case 2:
            $sqlExtra="";
            if (!empty($primeraParteClave) && !empty($segundaParteClave)) {
                array_push($params, '%' . $primeraParteClave . $segundaParteClave . '%');
                $sqlExtra = "HAVING llave LIKE ? ";
            } elseif (!empty($primeraParteClave) && empty($segundaParteClave)) {
                array_push($params, '%' . $primeraParteClave . '%');
                $sqlExtra = "HAVING subfuente LIKE ? ";
            } elseif (empty($primeraParteClave) && !empty($segundaParteClave)) {
                array_push($params, '%' . $segundaParteClave . '%');
                $sqlExtra = "HAVING ori.ori_id LIKE ? ";
            }
            array_unshift($params, APP_EJERCICIO);
            array_unshift($params, APP_EJERCICIO);
            $sql = "SELECT CONCAT(ori.cff_id, ori.fte_id, ori.sft_id) AS subfuente,
                    CONCAT(ori.cff_id, ori.fte_id, ori.sft_id, ori.ori_id) AS llave,
                    ori.cff_id, ori.fte_id, ori.sft_id, ori.ori_id, ori.ori_nombre ,
                    sft.sft_nombre
                FROM " . BD_SINPRES . ".cat_OrigenRecurso" . APP_EJERCICIO . " ori
                RIGHT JOIN " . BD_SINPRES . ".cat_Subfuente" . APP_EJERCICIO . " sft USING(cff_id,fte_id,sft_id)
                WHERE ori.ori_ejercicio = ? AND sft.sft_ejercicio = ?
                GROUP BY sft.cff_id, sft.fte_id, sft.sft_id, ori.ori_id,ori.ori_nombre, sft.sft_nombre
                $sqlExtra
                ORDER BY llave ASC";
            break;
        case 3:
            $sqlExtra = "";
            if (!empty($primeraParteClave) && !empty($segundaParteClave)) {
                array_push($params, '%' . $primeraParteClave . $segundaParteClave . '%');
                $sqlExtra = "HAVING llave LIKE ? ";
            } elseif (!empty($primeraParteClave) && empty($segundaParteClave)) {
                array_push($params, '%' . $primeraParteClave . '%');
                $sqlExtra = "HAVING origen LIKE ? ";
            } elseif (empty($primeraParteClave) && !empty($segundaParteClave)) {
                array_push($params, '%' . $segundaParteClave . '%');
                $sqlExtra = "HAVING sor.sor_id LIKE ? ";
            }
            if (!empty($tipoRecurso)) {
                $sqlExtra .= !empty($sqlExtra) ? ' AND ' : ' HAVING ';
                $sqlExtra .= ' ter_id = ? ';
                if ($tipoRecurso == "A") {
                    array_push($params, 'ESTATAL');
                } else {
                    array_push($params, 'FEDERAL');
                }
            }
            array_unshift($params, APP_EJERCICIO);
            array_unshift($params, APP_EJERCICIO);
            $sql ="SELECT CONCAT(sor.cff_id, sor.fte_id, sor.sft_id, sor.ori_id) AS origen,
                CONCAT(sor.cff_id, sor.fte_id, sor.sft_id, sor.ori_id, sor.sor_id) AS llave,
                sor.cff_id, sor.fte_id, sor.sft_id, sor.ori_id, sor.sor_id,
                IF(sor.ter_id = 'A','ESTATAL','FEDERAL') AS ter_id, sor.sor_nombre,
                    ori.ori_nombre
                FROM " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " sor
                RIGHT JOIN " . BD_SINPRES . ".cat_OrigenRecurso" . APP_EJERCICIO . " ori USING(cff_id,fte_id,sft_id, ori_id)
                WHERE sor.sor_ejercicio = ? AND ori.ori_ejercicio = ?
                GROUP BY sor.cff_id, sor.fte_id, sor.sft_id, sor.ori_id,sor.sor_id,sor.sor_nombre,
                    ori.ori_nombre
                $sqlExtra
                ORDER BY llave ASC";
            break;
        case 4:
            $sqlExtra = "";
            if (!empty($primeraParteClave) && !empty($segundaParteClave)) {
                array_push($params, '%' . $primeraParteClave . $segundaParteClave . '%');
                $sqlExtra="HAVING llave LIKE ? ";
            } elseif (!empty($primeraParteClave) && empty($segundaParteClave)) {
                array_push($params, '%' . $primeraParteClave . '%');
                $sqlExtra="HAVING suborigen LIKE ? ";
            } elseif (empty($primeraParteClave) && !empty($segundaParteClave)) {
                array_push($params, '%' . $segundaParteClave . '%');
                $sqlExtra="HAVING clave LIKE ? ";
            }
            array_unshift($params, APP_EJERCICIO);
            array_unshift($params, APP_EJERCICIO);
            $sql ="SELECT CONCAT(cfi.cff_id, cfi.fte_id, cfi.sft_id, cfi.ori_id, cfi.sor_id) AS suborigen,
                CONCAT(cfi.cff_id, cfi.fte_id, cfi.sft_id, cfi.ori_id, cfi.sor_id,cfi.cfi_id, cfi.cfi_anio) AS llave,
                cfi.cff_id, cfi.fte_id, cfi.sft_id, cfi.ori_id, cfi.sor_id, cfi.cfi_id, cfi.cfi_anio, cfi.cfi_ejercicio2, cfi.cfi_ejercicio, cfi.cfi_nombre,
                cfi.cfi_descripcion, cfi.cfi_abreviatura,
                CONCAT(cfi.cfi_id, cfi.cfi_anio) AS clave,
                ecf.ecf_poa,ecf.ecf_division, ecf.pdp_id,
                cff.cff_nombre, fte.fte_nombre,sft.sft_nombre,ori.ori_nombre, sor.sor_nombre
                FROM " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cfi
                RIGHT JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " sor USING(cff_id,fte_id,sft_id, ori_id, sor_id)
                RIGHT JOIN " . BD_SINPRES . ".cat_OrigenRecurso" . APP_EJERCICIO . " ori USING(cff_id,fte_id,sft_id, ori_id)
                RIGHT JOIN " . BD_SINPRES . ".cat_Subfuente" . APP_EJERCICIO . " sft USING(cff_id,fte_id,sft_id)
                RIGHT JOIN " . BD_SINPRES . ".cat_Fuente" . APP_EJERCICIO . " fte USING(cff_id,fte_id)
                RIGHT JOIN " . BD_SINPRES . ".cat_ClasificadorFuenteFinanciamiento cff USING(cff_id)
                INNER JOIN " . BD_CTRL. ".enc_ClaveFinanciamiento ecf USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id, cfi_anio)
                WHERE sor.sor_ejercicio = ? AND cfi.cfi_ejercicio2 = ?
                GROUP BY cff.cff_id, fte.fte_id, sft.sft_id, ori.ori_id,sor.sor_id, cfi.cfi_id,cfi.cfi_anio,cff.cff_nombre,
                    fte.fte_nombre,sft.sft_nombre, ori.ori_nombre, sor.sor_nombre,
                    cfi.cfi_nombre
                $sqlExtra
                ORDER BY llave ASC";
            break;
        default:
            break;
    }
    
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($params);
    $conexion->desconectar();
    
    $datos = array();
    while ($row = $conexion->extraer()) {
        $dato = array(
            'cff_id'=>$row['cff_id'],
            'fte_id'=>$row['fte_id']
        );
        
        switch ($ccd_id) {
            case 0:
                $dato['fte_nombre'] = $row['fte_nombre'];
                $dato['cff_nombre'] = $row['cff_nombre'];
                break;
            case 1:
                $dato['sft_id'] = $row['sft_id'];
                $dato['sft_nombre'] = $row['sft_nombre'];
                $dato['fuente'] = $row['fuente'];
                $dato['fte_nombre'] = $row['fte_nombre'];
                break;
            case 2:
                $dato['sft_id'] = $row['sft_id'];
                $dato['ori_id'] = $row['ori_id'];
                $dato['ori_nombre'] = $row['ori_nombre'];
                $dato['subfuente'] = $row['subfuente'];
                $dato['sft_nombre'] = $row['sft_nombre'];
                break;
            case 3:
                $dato['sft_id'] = $row['sft_id'];
                $dato['ori_id'] = $row['ori_id'];
                $dato['sor_id'] = $row['sor_id'];
                $dato['ter_id'] = $row['ter_id'];
                $dato['sor_nombre'] = $row['sor_nombre'];
                $dato['origen'] = $row['origen'];
                $dato['ori_nombre'] = $row['ori_nombre'];
                break;
            case 4:
                $dato['sft_id'] = $row['sft_id'];
                $dato['ori_id'] = $row['ori_id'];
                $dato['sor_id'] = $row['sor_id'];
                $dato['cfi_id'] = $row['cfi_id'];
                $dato['cfi_anio'] = $row['cfi_anio'];
                $dato['cfi_nombre'] = $row['cfi_nombre'];
                $dato['cfi_abreviatura'] = $row['cfi_abreviatura'];
                $dato['ecf_poa'] = ($row['ecf_poa'] == '1')?'Habilitado': ' ';
                $dato['ecf_division'] = ($row['ecf_division'] == '1')?'Habilitado': ' ';
                $dato['pdp_id'] = ($row['pdp_id'] == '1')?'Habilitado': ' ';
                $dato['cfi_descripcion'] = $row['cfi_descripcion'];
                $dato['suborigen'] = $row['suborigen'];
                $dato['sor_nombre'] = $row['sor_nombre'];
                $dato['cff_nombre'] = $row['cff_nombre'];
                $dato['fte_nombre'] = $row['fte_nombre'];
                $dato['sft_nombre'] = $row['sft_nombre'];
                $dato['ori_nombre'] = $row['ori_nombre'];
                break;
            default: 
                break;
        }
        array_push( $datos, $dato );
    }
    if (count($datos) == 0) {
        $dato = array(
            'cff_id'=> '',
            'fte_id'=> '',
            'nivel'=> 1,
        );
        switch ($ccd_id ) {
            case 0:
                $dato['fte_nombre'] = '';
                $dato['cff_nombre'] = '';
                $dato['ponerFuente'] = '';
                break;
            case 1:
                $dato['sft_id'] = '';
                $dato['sft_nombre'] = '';
                $dato['fuente'] = '';
                $dato['fte_nombre'] = '';
                $dato['ponerFuente'] = '';
                break;
            case 2:
                $dato['sft_id'] = '';
                $dato['ori_id'] = '';
                $dato['ori_nombre'] = '';
                $dato['subfuente'] = '';
                $dato['sft_nombre'] = '';
                $dato['ponerFuente'] = '';
                break;
            case 3:
                $dato['sft_id'] = '';
                $dato['ori_id'] = '';
                $dato['sor_id'] = '';
                $dato['sor_nombre'] = '';
                break;
            case 4:
                $dato['sft_id'] = '';
                $dato['ori_id'] = '';
                $dato['sor_id'] = '';
                $dato['cfi_id'] = '';
                $dato['cfi_anio'] = '';
                $dato['sor_nombre'] = '';
                $dato['ecf_poa'] = '';
                $dato['ecf_division'] = '';
                $dato['pdp_id'] = '';
                $dato['suborigen'] ='';
                $dato['sor_nombre'] ='';
                $dato['ponerFuente'] ='';
                $dato['cff_nombre'] = '';
                $dato['fte_nombre'] = '';
                $dato['sft_nombre'] = '';
                $dato['ori_nombre'] = '';
                break;
            default:
                break;
        }
        array_push( $datos, $dato );
    }
    return $datos;
}
