<?php
/**
 * Description of function consultaPlaneacionUr
 *
 * author: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 06-May-2019
 * Migrate: 02-Julio-2020
 * Comment: -----
 * Objective: Consulta datos de la planeacion UR en particular X
 * Regla de Negocio: -----
 *
 * */

function consultaPlaneacionUr($clasAdm_gpo, $clasAdm_ur)
{

    $sql = '
        SELECT
        u.gpo_id,u.ur_id,u.ur_nombre,a.ur_nombreTitular, a.ur_cargoTitular,a.ur_nombreAdministrativo,a.ur_cargoAdministrativo
        ,a.ur_inicioPeriodicoOficial,IFNULL( a.ur_inicioFecha, 0 ) AS ur_inicioFecha, IFNULL( a.ur_inicioDecretoFecha, 0 ) AS ur_inicioDecretoFecha,a.ur_modificacionPeriodico,a.ur_nombrePlaneacion,a.ur_cargoPlaneacion,
            IFNULL( a.ur_modificacionFecha, 0 ) AS ur_modificacionFecha, a.ur_internoPeriodico, IFNULL( a.ur_internoFecha, 0 ) AS ur_internoFecha,a.ur_organigramaArchivo, IFNULL( a.ur_organigramaFecha, 0 ) AS ur_organigramaFecha,a.ur_internoArchivo,a.ur_mision,a.ur_vision,
            a.ur_objetivoGeneral,a.ur_objetivoEstrategico,a.ur_productoInstitucional,u.ur_poa, u.eur_id
        FROM ' . BD_CTRL . '.cat_UnidadResponsable u
        JOIN ' . BD_CTRL . '.aux_UnidadResponsable a USING( gpo_id, ur_id )
        WHERE u.gpo_id= ? AND u.ur_id= ? ;';
    $params = [$clasAdm_gpo, $clasAdm_ur];
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($params);
    $conexion->desconectar();

    $datos = array();
    while ($row = $conexion->extraer()) {
        $dato = array(
            'gpo_id' => $row['gpo_id'],
            'ur_id' => $row['ur_id'],
            'ur_nombre' => $row['ur_nombre'],
            'ur_nombreTitular' => $row['ur_nombreTitular'],
            'ur_cargoTitular' => $row['ur_cargoTitular'],
            'ur_nombreAdministrativo' => $row['ur_nombreAdministrativo'],
            'ur_cargoAdministrativo' => $row['ur_cargoAdministrativo'],
            'ur_inicioPeriodicoOficial' => $row['ur_inicioPeriodicoOficial'],
            'ur_inicioFecha' => $row['ur_inicioFecha'],
            'ur_inicioDecretoFecha' => $row['ur_inicioDecretoFecha'],
            'ur_modificacionPeriodico' => $row['ur_modificacionPeriodico'],
            'ur_nombrePlaneacion' => $row['ur_nombrePlaneacion'],
            'ur_cargoPlaneacion' => $row['ur_cargoPlaneacion'],
            'ur_modificacionFecha' => $row['ur_modificacionFecha'],
            'ur_internoArchivo' => $row['ur_internoArchivo'],
            'ur_internoPeriodico' => $row['ur_internoPeriodico'],
            'ur_internoFecha' => $row['ur_internoFecha'],
            'ur_organigramaArchivo' => $row['ur_organigramaArchivo'],
            'ur_organigramaFecha' => $row['ur_organigramaFecha'],
            'ur_mision' => $row['ur_mision'],
            'ur_vision' => $row['ur_vision'],
            'ur_objetivoGeneral' => $row['ur_objetivoGeneral'],
            'ur_objetivoEstrategico' => $row['ur_objetivoEstrategico'],
            'ur_productoInstitucional' => $row['ur_productoInstitucional'],
            'ur_poa' => $row['ur_poa'],
            'eur_id' => $row['eur_id'],
        );

        array_push($datos, $dato);
    }
    if (count($datos) == 0) {
        $dato = array(
            'gpo_id' => '',
            'ur_id' => '',
            'ur_nombre' => '',
            'ur_nombreTitular' => '',
            'ur_cargoTitular' => '',
            'ur_nombreAdministrativo' => '',
            'ur_cargoAdministrativo' => '',
            'ur_inicioPeriodicoOficial' => '',
            'ur_inicioFecha' => '',
            'ur_inicioDecretoFecha' => '',
            'ur_modificacionPeriodico' => '',
            'ur_nombrePlaneacion' => '',
            'ur_cargoPlaneacion' => '',
            'ur_modificacionFecha' => '',
            'ur_internoPeriodico' => '',
            'ur_internoFecha' => '',
            'ur_internoArchivo' => '',
            'ur_organigramaArchivo' => '',
            'ur_organigramaFecha' => '',
            'ur_mision' => '',
            'ur_vision' => '',
            'ur_objetivoGeneral' => '',
            'ur_objetivoEstrategico' => '',
            'ur_productoInstitucional' => '',
            'ur_poa' => '',
            'eur_id' => '',
        );

        array_push($datos, $dato);
    }
    return $datos;
}
