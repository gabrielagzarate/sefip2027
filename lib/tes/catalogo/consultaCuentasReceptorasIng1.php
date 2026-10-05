<?php    
/**
 * Description of function consultaCuentasReceptorasIng1
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 19-Sep-2018
 * Comment: -----
 * Objective: Consultar datos en la base de datos de la relacion de cuentas para configuracion.
 * Regla de Negocio: -----
 * 
 * */

function consultaCuentasReceptorasIng1()
{
    date_default_timezone_set('America/Mexico_City');
    $hoy = date("Y-m-d");
    $sql ="                    
        SELECT 
            cbs_id clabe,
            cbs_cuenta, 
            (
                SELECT cbs_cuentaBanco 
                FROM " . BD_TESORERIA . ".vin_CuentaSap 
                WHERE tcs_id = 1 AND cbs_id = clabe
            ) inversion,
            (
                SELECT concat(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id) 
                FROM " . BD_TESORERIA . ".vin_CuentaSap 
                WHERE tcs_id = 2 AND cbs_id = clabe
            ) sap_cheque,
            (
                SELECT concat(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id) 
                FROM " . BD_TESORERIA . ".vin_CuentaSap 
                WHERE tcs_id = 1 AND cbs_id = clabe
            ) sap_inversion,
            cbs_nombre,
            (
                SELECT concat(substring(concat(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id),1,9),1) 
                FROM " . BD_TESORERIA . ".vin_CuentaSap 
                WHERE tcs_id = 2 AND cbs_id = clabe
            ) as ing,
            (
                SELECT concat(substring(concat(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id),1,9),2) 
                FROM " . BD_TESORERIA . ".vin_CuentaSap 
                WHERE tcs_id = 2 AND cbs_id = clabe
            ) as egr,
            b.usr_fecha,
            iba_abreviatura,
            iba_id
        FROM " . BD_TESORERIA . ".ban_CuentaBancariaSefin 
            LEFT JOIN " . BD_TESORERIA . ".vin_CuentaSap b USING (cbs_id)
            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria USING (iba_id)
        WHERE
            tcs_id = 1 AND 
            CAST(b.usr_fecha AS DATE) = ?
            AND cbs_inversion = 1
        ORDER BY iba_id;
    ";        
    
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);        
    $conexion->consultar([$hoy . '%']);
    $conexion->desconectar();
    
    $datos = array();
    while( $row = $conexion->extraer() ){
        $dato['clabe'] = $row['clabe'];
        // Ojo: Del ticket #518893, para que muestre correctamente la cuenta bancaria si pertenece 
        //      a "Banco Azteca, S.A."
        $dato['cbs_cuenta'] = (substr($row['clabe'],0,3) == 127) ?
            $row['cbs_cuenta'] :
            substr($row['cbs_cuenta'],3);
        // Ojo: Del ticket #518893, para que muestre correctamente la cuenta bancaria si pertenece 
        //      a "Banco Azteca, S.A.", ademas inversion no se muestran los 3 digitos, ya que al establecer el rellenado
        //      a la izquierda hay 3 ceros de mas que antes no se mostraban
        $dato['inversion'] = $row['inversion'];
        $dato['sap_cheque'] = $row['sap_cheque'];
        $dato['sap_inversion'] = $row['sap_inversion'];
        $dato['cbs_nombre'] = $row['cbs_nombre'];
        $dato['ing'] = $row['ing'];
        $dato['egr'] = $row['egr'];
        $dato['iba_abreviatura'] = $row['iba_abreviatura'];
        $dato['iba_id'] = $row['iba_id'];
        array_push($datos, $dato);                                    
    }        
    if (count($datos) == 0) {
        $dato = array( 
            'clabe' => '',
            'cbs_cuenta' => '',
            'inversion' => '',
            'sap_cheque' => '',
            'sap_inversion' => '',
            'cbs_nombre' => '',
            'ing' => '',
            'egr' => '',
            'iba_abreviatura' => '',
            'iba_id' => ''
        );
        array_push($datos,$dato);
    }
    return $datos;
}
