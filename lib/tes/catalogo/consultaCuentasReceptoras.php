<?php
/**
 * Description of function consultaCuentasReceptoras
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 19-Sep-2018
 * Comment: -----
 * Objective: Consultar en la base de datos el catálogos de claves de financiamiento.
 * Regla de Negocio: -----
 *
 * */

function consultaCuentasReceptoras($clabe,$estadocuenta,$fechaAperturaBMin,$fechaAperturaBMax,$prm)
{
    $params = [];
    $extraSql = "";
    $sql ="
        SELECT
            cff_id, cff_nombre,
            fte_id, fte_nombre,
            sft_id, sft_nombre,
            ori_id, ori_nombre,
            sor_id, sor_nombre,
            cfi_id, cfi_anio, cfi_nombre, cfi_clave,
            ecf_ejercicio,
            iba_abreviatura as abreviatura,
            cbs_cuenta as CUENTA,
            cbs_id as CLABE,
            CONCAT(d.ger_id, '.', d.cgp_id, '.', d.rub_id, '.', d.cgcu_id, '.', d.cgsc_id, '.', d.cgc_id, '.',d.cta_id) as ARMONI,
            SUBSTR(cbs_id,4,3) as PLAZA,
            cbs_sucursal AS SUCURSAL
        FROM
            " . BD_SINPRES . ".enc_ClaveFinanciamiento as e
            LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento as v USING(ecf_id)
            INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " as c 
                USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as b 
                USING (cbs_id)
            LEFT JOIN (
                SELECT cbs_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id 
                FROM " . BD_TESORERIA . ".vin_SefinPlanCuenta 
                WHERE tcs_id = 2
            ) as d 
                USING (cbs_id)
            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria 
                USING (iba_id)
            INNER JOIN " . BD_SINPRES . ".cat_ClasificadorFuenteFinanciamiento 
                USING (cff_id)
            INNER JOIN " . BD_SINPRES . ".cat_Fuente" . APP_EJERCICIO . " 
                USING (cff_id, fte_id)
            INNER JOIN " . BD_SINPRES . ".cat_Subfuente" . APP_EJERCICIO . " 
                USING (cff_id, fte_id, sft_id)
            INNER JOIN " . BD_SINPRES . ".cat_OrigenRecurso" . APP_EJERCICIO . " 
                USING (cff_id, fte_id, sft_id, ori_id)
            INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " 
                USING (cff_id, fte_id, sft_id, ori_id, sor_id)
            INNER JOIN " . BD_TESORERIA . ".cat_EstadoCuenta as ec 
                USING (eba_id)
            INNER JOIN " . BD_TESORERIA . ".cat_TipoRecurso as tr 
                ON tr.ter_id = b.ter_id
        WHERE c.cfi_ejercicio2 = " . APP_EJERCICIO . " AND e.ecf_ejercicio = " . APP_EJERCICIO . "
    ";
    if ($clabe != "") {
        $extraSql .= "AND b.cbs_id like ? ";
        array_push($params, '%' . $clabe . '%');
    }
    if ($fechaAperturaBMin != "" && $fechaAperturaBMax != "") {
        $fechaMin = DateTime::createFromFormat('d/m/Y', $fechaAperturaBMin);
        $fechaMax = DateTime::createFromFormat('d/m/Y', $fechaAperturaBMax);
        $extraSql .= "AND b.cbs_fechaApertura BETWEEN ? AND ? ";
        array_push($params, $fechaMin->format('Y-m-d'), $fechaMax->format('Y-m-d'));
    } else if ($fechaAperturaBMin != "" && $fechaAperturaBMax == "") {
        $fechaMin = DateTime::createFromFormat('d/m/Y', $fechaAperturaBMin);
        $extraSql .= "AND b.cbs_fechaApertura = ? ";
        array_push($params, $fechaMin->format('Y-m-d'));
    } else if ($fechaAperturaBMin == "" && $fechaAperturaBMax != "") {
        $fechaMax = DateTime::createFromFormat('d/m/Y', $fechaAperturaBMax);
        $extraSql .= "AND b.cbs_fechaApertura = ? ";
        array_push($params, $fechaMax->format('Y-m-d'));
    }

    if ($estadocuenta != "") {
        $extraSql .= "AND ec.eba_nombre = ? ";
        array_push($params, $estadocuenta);
    }    
    if ($prm->tipo != ""){
        $extraSql .= "AND tr.ter_nombre = ? ";
        array_push($params, $prm->tipo);
    }
    if ($prm->aliasCuenta != "") {
        $extraSql .= "AND b.cbs_alias like ? ";
        array_push($params, '%' . $prm->aliasCuenta . '%');
    }
    if ($prm->nombreCuenta != ""){
        $extraSql .= "AND b.cbs_nombre like ? ";
        array_push($params, '%' . $prm->nombreCuenta . '%');
    }
    if ($prm->nombreBanco != ""){
        $extraSql .= "AND iba_abreviatura = ? ";
        array_push($params, $prm->nombreBanco);
    }

    $sql .= ' ' . $extraSql . ' ORDER BY cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio;';
    
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($params);
    $conexion->desconectar();
    
    $datos = array();
    while( $row = $conexion->extraer() ) {
        $dato['cff_id'] = $row['cff_id'];
        $dato['cff_nombre'] = $row['cff_nombre'];
        $dato['fte_id'] = $row['fte_id'];
        $dato['fte_nombre'] = $row['fte_nombre'];
        $dato['sft_id'] = $row['sft_id'];
        $dato['sft_nombre'] = $row['sft_nombre'];
        $dato['ori_id'] = $row['ori_id'];
        $dato['ori_nombre'] = $row['ori_nombre'];
        $dato['sor_id'] = $row['sor_id'];
        $dato['sor_nombre'] = $row['sor_nombre'];
        $dato['cfi_id'] = $row['cfi_id'];
        $dato['cfi_anio'] = $row['cfi_anio'];
        $dato['cfi_nombre'] = $row['cfi_nombre'];
        $dato['cfi_clave'] = $row['cfi_clave'];
        $dato['ecf_ejercicio'] = $row['ecf_ejercicio'];
        $dato['abreviatura'] = $row['abreviatura'];
        $dato['CUENTA'] = $row['CUENTA'];
        $dato['CLABE'] = $row['CLABE'];
        $dato['ARMONI'] = $row['ARMONI'];
        $dato['PLAZA'] = $row['PLAZA'];
        $dato['SUCURSAL'] = $row['SUCURSAL'];
        array_push( $datos, $dato );
    }
    if (count($datos) == 0) {
        $dato = array(
            'cff_id' => '',
            'cff_nombre' => '',
            'fte_id' => '',
            'fte_nombre' => '',
            'sft_id' => '',
            'sft_nombre' => '',
            'ori_id' => '',
            'ori_nombre' => '',
            'sor_id' => '',
            'sor_nombre' => '',
            'cfi_id' => '',
            'cfi_anio' => '',
            'cfi_nombre' => '',
            'cfi_clave' => '',
            'ecf_ejercicio' => '',
            'abreviatura' => '',
            'CUENTA' => '',
            'CLABE' => '',
            'ARMONI' => '',
            'PLAZA' => '',
            'SUCURSAL' => ''
        );
        array_push($datos,$dato);
    }
    return $datos;
}