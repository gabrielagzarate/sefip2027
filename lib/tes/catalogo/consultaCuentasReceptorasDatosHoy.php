<?php    
/**
 * Description of function consultaCuentasReceptorasDatosHoy
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 19-Sep-2018
 * Comment: -----
 * Objective: Consultar datos en la base de datos de la relacion de cuenta para dar de alta para conciliar.
 * Regla de Negocio: -----
 * 
 * */

function consultaCuentasReceptorasDatosHoy()
{
    date_default_timezone_set('America/Mexico_City');
    $hoy = date("Y-m-d");
    $sql ="                    
        SELECT 
            iba_abreviatura, 
            cbs_cuenta, 
            concat(substring(cbs_id,1,3), substring(cbs_cuenta,-5)) claveBanco, 
            cbs_nombre, 
            b.usr_fecha,
            b.cbs_id
        FROM " . BD_TESORERIA . ".ban_CuentaBancariaSefin b
        INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria c USING (iba_id)
        WHERE CAST(b.usr_fecha AS DATE) = '".$hoy."'
        ORDER BY iba_id; 
    ";        
    
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);        
    $conexion->consultar();
    $conexion->desconectar();
    
    $datos = array();
    while ($row = $conexion->extraer()) {
        $dato['iba_abreviatura'] = $row['iba_abreviatura'];
        $dato['cbs_cuenta'] = $row['cbs_cuenta'];
        $dato['claveBanco'] = $row['claveBanco'];
        $dato['cbs_nombre'] = $row['cbs_nombre'];
        $dato['usr_fecha'] = $row['usr_fecha'];
        $dato['cbs_id'] = $row['cbs_id'];
        array_push( $datos, $dato );                                    
    }        
    if (count($datos) == 0) {
        $dato = array( 
            'iba_abreviatura' => '',
            'cbs_cuenta' => '',
            'claveBanco' => '',
            'cbs_nombre' => '',
            'usr_fecha' => '',
            'cbs_id' => ''
        );
        array_push($datos, $dato);
    }
    return $datos;
}
