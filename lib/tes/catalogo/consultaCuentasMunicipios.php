<?php    
/**
 * Description of function consultaCuentasMunicipios
 *
 * @author: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 10-Dic-2019
 * Comment: -----
 * Objective: Consultar datos en la base de datos de los municipios que tienen cuentas de tipos de fondo.
 * Regla de Negocio: -----
 * 
 * */

function consultaCuentasMunicipios()
{
    $sql = '
        SELECT "A" AS agrupador, mun_id as NUMERO_MUNICIPIO, mun_nombre as MUNICIPIO,tfn.tfn_nombre AS TIPO_FONDO,eba.eba_nombre AS ESTATUS, CONCAT(cbb.cbb_id) as CLABE,cbb.cbb_cuenta AS NO_CTA,iba.iba_abreviatura as INSTFIN, vbm.tfn_id,vbm.mun_id,vbm.cbb_id
        FROM '. BD_TESORERIA .'.vin_BeneficiarioMunicipio vbm
        RIGHT OUTER JOIN '. BD_TESORERIA .'.cat_MunicipioProveedor mun USING (mun_id)
        LEFT JOIN '. BD_TESORERIA .'.cat_TipoFondo tfn using (tfn_id)
        LEFT JOIN '. BD_TESORERIA .'.ban_CuentaBancariaBeneficiario cbb using (cbb_id)
        LEFT JOIN '. BD_TESORERIA .'.cat_EstadoCuenta AS eba USING (eba_id)
        LEFT JOIN '. BD_TESORERIA .'.cat_InstitucionBancaria AS iba USING (iba_id)
        WHERE cbb.tbe_id = "MUN"
            AND tfn.tfn_id IN (2, 3, 4) AND cbb.cbb_anio = '. APP_EJERCICIO .'
        UNION ALL
        SELECT "B" AS agrupador, mun.mun_id as NUMERO_MUNICIPIO, mun_nombre as MUNICIPIO,"" AS TIPO_FONDO,"" AS ESTATUS, CONCAT("") as CLABE,"" AS NO_CTA,"" as INSTFIN, "" tfn_id,"" mun_id,"" cbb_id
        FROM '. BD_TESORERIA .'.vin_BeneficiarioMunicipio vbm
        RIGHT OUTER JOIN '. BD_TESORERIA .'.cat_MunicipioProveedor mun USING (mun_id)
        LEFT JOIN '. BD_TESORERIA .'.ban_CuentaBancariaBeneficiario cbb USING (cbb_id)
        WHERE mun.mun_id NOT IN(
            SELECT mun.mun_id
            FROM '. BD_TESORERIA .'.vin_BeneficiarioMunicipio vbm
            RIGHT OUTER JOIN '. BD_TESORERIA .'.cat_MunicipioProveedor mun USING (mun_id)
            LEFT JOIN '. BD_TESORERIA .'.ban_CuentaBancariaBeneficiario cbb USING (cbb_id)
            WHERE vbm.mun_id IS NOT NULL AND cbb.cbb_anio = '. APP_EJERCICIO .'
            AND tfn_id IN (2, 3, 4)
        )
        GROUP BY mun.mun_nombre
        UNION ALL
        SELECT "C" AS agrupador, "" AS NUMERO_MUNICIPIO, "" as MUNICIPIO, "" AS TIPO_FONDO, eba.eba_nombre AS ESTATUS, CONCAT(cbb.cbb_id) as CLABE,cbb.cbb_cuenta AS NO_CTA,iba.iba_abreviatura as INSTFIN, "" AS tfn_id,"" AS mun_id,"" AS cbb_id
        FROM '. BD_TESORERIA .'.ban_CuentaBancariaBeneficiario cbb
        LEFT JOIN '. BD_TESORERIA .'.cat_EstadoCuenta AS eba USING (eba_id)
        LEFT JOIN '. BD_TESORERIA .'.cat_InstitucionBancaria AS iba USING (iba_id)
        WHERE cbb_id NOT IN (SELECT cbb_id FROM '. BD_TESORERIA .'.vin_BeneficiarioMunicipio) 
            AND cbb.cbb_anio = '. APP_EJERCICIO .'
        ORDER BY agrupador,MUNICIPIO,tfn_id 
    ';
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar();
    $conexion->desconectar();
    
    $datos = array();
    while ($row = $conexion->extraer()) {
        $dato['agrupador'] = $row['agrupador'];
        $dato['NUMERO_MUNICIPIO'] = $row['NUMERO_MUNICIPIO'];
        $dato['MUNICIPIO'] = $row['MUNICIPIO'];
        $dato['TIPO_FONDO'] = $row['TIPO_FONDO'];
        $dato['ESTATUS'] = $row['ESTATUS'];
        $dato['CLABE'] = $row['CLABE'];
        $dato['NO_CTA'] = $row['NO_CTA'];
        $dato['INSTFIN'] = $row['INSTFIN'];
        $dato['tfn_id'] = $row['tfn_id'];
        $dato['mun_id'] = $row['mun_id'];
        $dato['cbb_id'] = $row['cbb_id'];
        array_push($datos, $dato);
    }
    if (count($datos) == 0) {
        $dato = array( 
            'agrupador' => '',
            'NUMERO_MUNICIPIO' => '',
            'MUNICIPIO' => '',
            'TIPO_FONDO' => '',
            'ESTATUS' => '',
            'CLABE' => '',
            'NO_CTA' => '',
            'INSTFIN' => '',
            'tfn_id' => '',
            'mun_id' => '',
            'cbb_id' => ''
        );
        array_push($datos, $dato);
    }
    return $datos;
}