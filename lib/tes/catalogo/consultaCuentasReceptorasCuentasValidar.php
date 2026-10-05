<?php    
/**
 * Description of function consultaCuentasReceptorasCuentasValidar
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 16-Oct-2018
 * Comment: -----
 * Objective: Consultar en la base de datos las cuentas a validar.
 * Regla de Negocio: Esta pantalla no pregunta por permisos.
 * 
 * */

function consultaCuentasReceptorasCuentasValidar()
{
    $sql ="                    
        SELECT 
            cbs_fechaApertura F_Apertura, 
            cbs_id CLABE, 
            ter_nombre TIPO, 
            eba_nombre E_Cuenta,
            cbs_nombre N_Cuenta 
        FROM 
            " . BD_TESORERIA . ".ban_CuentaBancariaSefin
        INNER JOIN " . BD_TESORERIA . ".cat_EstadoCuenta USING (eba_id)
        INNER JOIN " . BD_TESORERIA . ".cat_TipoRecurso USING (ter_id) 
        WHERE eba_id = 4
    ";        
        
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);        
    $conexion->consultar();
    $conexion->desconectar();
    
    $datos = array();
    while ($row = $conexion->extraer()) {
        $dato['F_Apertura'] = $row['F_Apertura'];
        $dato['CLABE'] = "'" . $row['CLABE'];
        $dato['TIPO'] = $row['TIPO'];
        $dato['E_Cuenta'] = $row['E_Cuenta'];
        $dato['N_Cuenta'] = $row['N_Cuenta'];
        array_push( $datos, $dato );                                    
    }        
    if (count($datos) == 0) {
        $dato = array( 
            'F_Apertura' => '',
            'CLABE' => '',
            'TIPO' => '',
            'E_Cuenta' => '',
            'N_Cuenta' => ''
        );
        array_push($datos,$dato);
    }
    return $datos;
}
