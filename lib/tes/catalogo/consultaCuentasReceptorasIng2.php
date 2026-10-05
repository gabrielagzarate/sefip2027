<?php    
/**
 * Description of function consultaCuentasReceptorasIng2
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 19-Sep-2018
 * Comment: -----
 * Objective: Consultar datos en la base de datos de la direccion de ingresos.
 * Regla de Negocio: -----
 * 
 * */

function consultaCuentasReceptorasIng2()
{
    date_default_timezone_set('America/Mexico_City');
    $hoy = date("Y-m-d");
    $sql ="                    
        SELECT 
            iba_abreviatura, 
            ter_nombre,
            CONCAT(SUBSTRING(concat(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id),1,9),'1') as sapImproductivas,
            cbs_cuenta, 
            cbs_nombre,
            cbs_inversion,
            b.usr_fecha,
            b.cbs_id
        FROM " . BD_TESORERIA . ".ban_CuentaBancariaSefin b
        LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria USING (iba_id)
        LEFT JOIN " . BD_TESORERIA . ".cat_TipoRecurso USING (ter_id)
        LEFT JOIN " . BD_TESORERIA . ".vin_CuentaSap USING (cbs_id)
        WHERE tcs_id = 2
            AND cbs_inversion = 2
            AND CAST(b.usr_fecha AS DATE) = ?
        ORDER BY iba_id;
    ";        
    
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);        
    $conexion->consultar([$hoy]);
    $conexion->desconectar();
    
    $datos = array();
    while ($row = $conexion->extraer()) {
        $dato['iba_abreviatura'] = $row['iba_abreviatura'];
        $dato['ter_nombre'] = $row['ter_nombre'];
        $dato['sapImproductivas'] = $row['sapImproductivas'];
        // Ojo: Del ticket #518893, para que muestre correctamente la cuenta bancaria si pertenece 
        //      a "Banco Azteca, S.A."
        $dato['cbs_cuenta'] = (substr($row['cbs_id'], 0, 3) == 127) ?
            $row['cbs_cuenta'] :
            substr($row['cbs_cuenta'], 3);
        $dato['cbs_nombre'] = $row['cbs_nombre'];
        $dato['cbs_inversion'] = $row['cbs_inversion'];
        $dato['usr_fecha'] = $row['usr_fecha'];
        $dato['cbs_id'] = $row['cbs_id'];
        array_push($datos, $dato);
    }        
    if (count($datos) == 0 ) {
        $dato = array( 
            'iba_abreviatura' => '',
            'ter_nombre' => '',
            'sapImproductivas' => '',
            'cbs_cuenta' => '',
            'cbs_nombre' => '',
            'cbs_inversion' => '',
            'usr_fecha' => '',
            'cbs_id' => ''
        );
        array_push($datos, $dato);
    }
    return $datos;
}
