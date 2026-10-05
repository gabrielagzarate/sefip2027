<?php
/**Devuelve la leyenda para los reportes del catálogo de clave de financiamiento */
function getModuloClaveFinanciamiento()
{
    if (tienePermiso("T.6.3.1")) {
        return "TESORERÍA " . APP_EJERCICIO;
    } elseif (tienePermiso("S.6.4.1")) {
        return "CONTROL PRESUPUESTAL " . APP_EJERCICIO;
    } elseif (tienePermiso("C.2.11.1")) {
        return "CONTABILIDAD GUBERNAMENTAL " . APP_EJERCICIO;
    }
    return "";
}

function claveFinanciamientoAfectadaPorClc($params)
{
    $conexion = new AccesoDatos();
    $where = '';
    if (array_key_exists('cff_id', $params)) {
        $where .= (empty($where)) ? 'WHERE ' : ' AND ';
        $where .= 'ccf.cff_id = :cff_id';
    }
    if (array_key_exists('fte_id', $params)) {
        $where .= (empty($where)) ? 'WHERE ' : ' AND ';
        $where .= 'ccf.fte_id = :fte_id';
    }
    if (array_key_exists('sft_id', $params)) {
        $where .= (empty($where)) ? 'WHERE ' : ' AND ';
        $where .= 'ccf.sft_id = :sft_id';
    }
    if (array_key_exists('ori_id', $params)) {
        $where .= (empty($where)) ? 'WHERE ' : ' AND ';
        $where .= 'ccf.ori_id = :ori_id';
    }
    if (array_key_exists('sor_id', $params)) {
        $where .= (empty($where)) ? 'WHERE ' : ' AND ';
        $where .= 'ccf.sor_id = :sor_id';
    }
    if (array_key_exists('cfi_id', $params)) {
        $where .= (empty($where)) ? 'WHERE ' : ' AND ';
        $where .= 'ccf.cfi_id = :cfi_id';
    }
    if (array_key_exists('cfi_anio', $params)) {
        $where .= (empty($where)) ? 'WHERE ' : ' AND ';
        $where .= 'ccf.cfi_anio = :cfi_anio';
    }
    if (array_key_exists('cfi_ejercicio2', $params)) {
        $where .= (empty($where)) ? 'WHERE ' : ' AND ';
        $where .= 'ccf.cfi_ejercicio2 = :cfi_ejercicio2';
    }
    $conexion->conectar();
    $conexion->setSql("SELECT ecf.ecf_id, cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,
            ncp.gpo_id, ncp.ur_id, ncp.clc_id
        FROM " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " ccf
        INNER JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento ecf USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
        INNER JOIN " . BD_CTRL . ".scp_Presupuestal ncp USING(ecf_id)
        " . $where . "
    ");

    $conexion->consultar($params);
    return $conexion->getNumeroRegistros();
}

function claveFinanciamientoAfectadaPorAdecuacion($params)
{
    $conexion = new AccesoDatos();
    $where = '';
    if (array_key_exists('cff_id', $params)) {
        $where .= (empty($where)) ? 'WHERE ' : ' AND ';
        $where .= 'ccf.cff_id = :cff_id';
    }
    if (array_key_exists('fte_id', $params)) {
        $where .= (empty($where)) ? 'WHERE ' : ' AND ';
        $where .= 'ccf.fte_id = :fte_id';
    }
    if (array_key_exists('sft_id', $params)) {
        $where .= (empty($where)) ? 'WHERE ' : ' AND ';
        $where .= 'ccf.sft_id = :sft_id';
    }
    if (array_key_exists('ori_id', $params)) {
        $where .= (empty($where)) ? 'WHERE ' : ' AND ';
        $where .= 'ccf.ori_id = :ori_id';
    }
    if (array_key_exists('sor_id', $params)) {
        $where .= (empty($where)) ? 'WHERE ' : ' AND ';
        $where .= 'ccf.sor_id = :sor_id';
    }
    if (array_key_exists('cfi_id', $params)) {
        $where .= (empty($where)) ? 'WHERE ' : ' AND ';
        $where .= 'ccf.cfi_id = :cfi_id';
    }
    if (array_key_exists('cfi_anio', $params)) {
        $where .= (empty($where)) ? 'WHERE ' : ' AND ';
        $where .= 'ccf.cfi_anio = :cfi_anio';
    }
    if (array_key_exists('cfi_ejercicio2', $params)) {
        $where .= (empty($where)) ? 'WHERE ' : ' AND ';
        $where .= 'ccf.cfi_ejercicio2 = :cfi_ejercicio2';
    }
    $conexion->conectar();
    $conexion->setSql("SELECT ecf.ecf_id, cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,
            dmo.gpo_id, dmo.ur_id
        FROM " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " ccf
        INNER JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento ecf USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
        INNER JOIN " . BD_CTRL . ".scp_DetalleModificacion dmo USING(ecf_id)
        " . $where . "
    ");

    $conexion->consultar($params);
    return $conexion->getNumeroRegistros();
}
