<?php    
/**
 * Description of function consultaCuentasReceptorasTabla
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 19-Sep-2018
 * Comment: -----
 * Objective: Consultar en la base de datos las cuentas bancarias.
 * Regla de Negocio: -----
 * 
 * */

function consultaCuentasReceptorasTabla($clabe,$estadocuenta,$fechaAperturaB,$prm)
{
    $params = [];
    $extraSql = "";
    $sql ="                    
        SELECT 
            iba_abreviatura, 
            cbs_nombre, 
            cbs_id clabe, 
            cbs_contrato,
            cbs_cuenta cta_cheque, 
            (
                SELECT 
                cbs_cuentaBanco 
                FROM " . BD_TESORERIA . ".vin_CuentaSap 
                WHERE tcs_id = 1 and cbs_id = clabe
            ) cta_inversion, 
            (
                SELECT 
                concat(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id) 
                FROM " . BD_TESORERIA . ".vin_CuentaSap 
                WHERE tcs_id = 2 and cbs_id = clabe
            ) cargo_cheque,
            (
                SELECT 
                CONCAT(ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', cgsc_id, '.', cgc_id, '.',cta_id) 
                FROM " . BD_TESORERIA . ".vin_SefinPlanCuenta 
                WHERE tcs_id = 2 and cbs_id = clabe
            ) as armoni_cheque,
            (
                SELECT 
                concat(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id) 
                FROM " . BD_TESORERIA . ".vin_CuentaSap 
                WHERE tcs_id = 1 and cbs_id = clabe
            ) cargo_inversion,
            (
                SELECT 
                CONCAT(ger_id, '.', cgp_id, '.', rub_id, '.', cgcu_id, '.', cgsc_id, '.', cgc_id, '.', cta_id) 
                FROM " . BD_TESORERIA . ".vin_SefinPlanCuenta 
                WHERE tcs_id = 1 and cbs_id = clabe
            ) armoni_inversion,
            ter_nombre,
            cbs_fechaApertura,
            cbs_fechaCancelacion,
            (
                SELECT 
                cbs_cuentaBanco 
                FROM " . BD_TESORERIA . ".vin_CuentaSap 
                WHERE tcs_id = 3 and cbs_id = clabe
            ) cta_interes,
            (
                SELECT 
                concat(ibs_propio, cbs_consecutivo) 
                FROM " . BD_TESORERIA . ".vin_CuentaSap 
                INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancariaSap as c using (ibs_id) 
                WHERE cbs_id = clabe and tcs_id = 2
            ) BAN_PROP
        FROM " . BD_TESORERIA . ".ban_CuentaBancariaSefin
        INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria using (iba_id)
        INNER JOIN " . BD_TESORERIA . ".cat_TipoRecurso using (ter_id)
        INNER JOIN " . BD_TESORERIA . ".cat_EstadoCuenta USING (eba_id)
        WHERE 1 = 1 
    ";        
    if ($clabe != "") {
        $extraSql .= "AND cbs_id like ? ";
        array_push($params, '%' . $clabe . '%');
    }
    if ($fechaAperturaB != "") {
        $fechaMin = DateTime::createFromFormat('d/m/Y', $fechaAperturaB);
        $extraSql .= "AND cbs_fechaApertura = ? ";
        array_push($params, $fechaMin->format('Y-m-d'));
    }
    if ($estadocuenta != "") {
        $extraSql .= "AND eba_nombre = ? ";
        array_push($params, $estadocuenta);
    }
    if ($prm->tipo != "") {
        $extraSql .= "AND ter_nombre = ? ";
        array_push($params, $prm->tipo);
    }
    if ($prm->aliasCuenta != "") {
        $extraSql .= "AND cbs_alias like ? ";
        array_push($params, '%' . $prm->aliasCuenta . '%');
    }
    if ($prm->nombreCuenta != "") {
        $extraSql .= "AND cbs_nombre like ? ";
        array_push($params, '%' . $prm->nombreCuenta . '%');
    }
    if ($prm->nombreBanco != "") {
        $extraSql .= "AND iba_abreviatura = ? ";
        array_push($params, $prm->nombreBanco);
    }

    $sql .= ' '.$extraSql.' ORDER BY cargo_cheque desc;';
    
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);        
    $conexion->consultar($params);
    $conexion->desconectar();
    
    $datos = array();
    while ($row = $conexion->extraer()) {
        $dato['iba_abreviatura'] = $row['iba_abreviatura'];
        $dato['cbs_nombre'] = $row['cbs_nombre'];
        $dato['clabe'] = $row['clabe'];
        $dato['cbs_contrato'] = $row['cbs_contrato'];
        // Ojo: Del ticket #518893, para que muestre correctamente la cuenta bancaria si pertenece 
        //      a "Banco Azteca, S.A."
        $dato['cta_cheque'] = (substr($row['clabe'],0,3)==127)?$row['cta_cheque']:substr($row['cta_cheque'],3);
        $dato['cta_inversion'] = $row['cta_inversion'];
        $dato['cargo_cheque'] = $row['cargo_cheque'];
        $dato['armoni_cheque'] = $row['armoni_cheque'];
        $dato['cargo_inversion'] = $row['cargo_inversion'];
        $dato['armoni_inversion'] = $row['armoni_inversion'];
        $dato['ter_nombre'] = $row['ter_nombre'];
        $dato['cbs_fechaApertura'] = $row['cbs_fechaApertura'];
        $dato['cbs_fechaCancelacion'] = $row['cbs_fechaCancelacion'];
        $dato['cta_interes'] = $row['cta_interes'];
        $dato['BAN_PROP'] = $row['BAN_PROP'];
        array_push( $datos, $dato );                                    
    }        
    if (count($datos) == 0) {
        $dato = array( 
            'iba_abreviatura' => '',
            'cbs_nombre' => '',
            'clabe' => '',
            'cbs_contrato' => '',
            'cta_cheque' => '',
            'cta_inversion' => '',
            'cargo_cheque' => '',
            'armoni_cheque' => '',
            'cargo_inversion' => '',
            'armoni_inversion' => '',
            'ter_nombre' => '',
            'cbs_fechaApertura' => '',
            'cbs_fechaCancelacion' => '',
            'cta_interes' => '',
            'BAN_PROP' => ''
        );
        array_push($datos,$dato);
    }
    return $datos;
}
