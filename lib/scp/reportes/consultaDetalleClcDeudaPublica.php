<?php

/**
 * Description of function consultaDetalleClcDeudaPublica
 *
 * @author Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Created: 05-03-2025
 * Comment: -----
 * Objective: Consultar datos en la base de datos el detalle de las CLC de tipo J
 * Regla de Negocio:----
 *
 * */

function consultaDetalleClcDeudaPublica($tipo, $fmin, $fmax, $return_sql = "No")
{
    $regresar = null;
    $filtroFecha = "";
    $fmin = DateTime::createFromFormat('d/m/Y', $fmin);
    $fmin = $fmin->format('Y-m-d');
    $fmax = DateTime::createFromFormat('d/m/Y', $fmax);
    $fmax = $fmax->format('Y-m-d');
    $params[':tipo'] = $tipo;
    if ($fmin == $fmax) {
        $filtroFecha = " AND clc_fechaElaboracion LIKE '".$fmin."%'";
    } else {
        $filtroFecha = "
            AND (clc_fechaElaboracion) BETWEEN :fmin
            AND :fmax
        ";
        $params[':fmin'] = $fmin;
        $params[':fmax'] = $fmax;
    }

    $sql = "
            SELECT
                ur_clave as ur,
                ur_nombre,
                clc_id AS clc,
                c.tcl_id,
                afe_id AS afectacion,
                numcontrol,
            IF
                ( clc_tipocereCo = 1, 'CERE', IF ( clc_tipocereCo = 2, 'CERECO', 'SIN CERE(CO)' ) ) cereco,
                clc_cereCo,
                mpc_nombre AS modo_pago,
                clc_concepto,
                CONCAT( pg_id, pe_id ) AS partida,
                CONCAT( pg_id, '-', pg_nombre ) AS partida_generica,
                CONCAT( pe_id, '-', pe_nombre ) AS partida_especifica,
                ecf_clave AS clave_financiamiento,
                referencia,
                DATE_FORMAT(clc_fechaElaboracion,'%d/%m/%Y') AS clc_fecha_elaboracion,
                rfc_id,
                nombre,
                CAST( bur_id AS CHAR ) AS receptora,
                CAST( scf.cbs_id AS CHAR ) AS ordenante,
                percepcion_bruto,
                percepcion_iva,
                percepcion_neto,
                retencion_neto
            FROM
                ".BD_CTRL.".scp_Clc c
                LEFT JOIN ".BD_CTRL.".scp_ClcAuxiliar USING ( gpo_id, ur_id, clc_id, ecl_id )
                LEFT JOIN ".BD_CTRL.".scp_Afectacion USING ( gpo_id, ur_id, clc_id, ecf_id )
                INNER JOIN ".BD_CTRL.".cat_UnidadResponsable USING ( gpo_id, ur_id )
                INNER JOIN ".BD_CTRL.".cat_ModoPagoClc mpc USING ( mpc_id )
                INNER JOIN ".BD_CTRL.".enc_ClaveFinanciamiento ecf USING ( ecf_id )
                INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento scf USING (ecf_id)
                INNER JOIN ".BD_SINPRES.".cat_ClaveFinanciamiento".APP_EJERCICIO." cf USING ( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                LEFT JOIN (
                SELECT
                    detalle.gpo_id,
                    detalle.ur_id,
                    clc_id,
                    afe_id,
                    ncp_id numcontrol,
                    pg_id,
                    pg_nombre,
                    pe_id,
                    pe_nombre,
                    ncp_concepto AS concepto,
                    ncp_referencia referencia,
                    ncp_fechaReferencia fecha_referencia,
                    ncp_percepcionBruto percepcion_bruto,
                    ncp_percepcionIva percepcion_iva,
                    ncp_percepcionNeto percepcion_neto,
                    0 retencion_neto,
                    rfc_id,
                    CONCAT( rfc_nombre, ' ', COALESCE ( rfc_paterno, ' ), ' ', COALESCE ( rfc_materno, ' ) ) AS nombre,
                    'P' tipo_numcontrol
                FROM
                    ".BD_CTRL.".scp_Presupuestal detalle
                    JOIN ".BD_CTRL.".presupuesto USING ( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
                    JOIN ".BD_CTRL.".cat_PartidaGenerica USING ( pg_id )
                    JOIN ".BD_CTRL.".cat_PartidaEspecifica USING ( pg_id, pe_id )
                    LEFT JOIN ( SELECT rfc_id, rfc_nombre, rfc_paterno, rfc_materno
                        FROM ".BD_CTRL.".cat_Proveedor
                        INNER JOIN ".BD_SINPRES.".cat_Proveedores USING ( rfc_id )
                    ) p USING ( rfc_id )
                WHERE
                    detalle.gpo_id = 1
                    AND detalle.ur_id = 16
                    AND clc_id != 0
                ) nc USING ( gpo_id, ur_id, clc_id, afe_id )
            WHERE
                gpo_id = 1
                AND ur_id = 16
                AND clc_id != 0 
                AND c.tcl_id = :tipo
                $filtroFecha                
            ORDER BY
                gpo_id,
                ur_id,
                clc_id,
                afe_id,
                numcontrol;
            ";
    if ($return_sql == "No") {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($params);
        $conexion->desconectar();

        $datos = array();
        while ($row = $conexion->extraer()) {
            $dato = array(
                'ur' => $row['ur'],
                'ur_nombre' => $row['ur_nombre'],
                'clc' => $row['clc'],
                'afectacion' => $row['afectacion'],
                'numcontrol' => $row['numcontrol'],
                'cereco' => $row['cereco'],
                'clc_cereCo' => $row['clc_cereCo'],
                'modo_pago' => $row['modo_pago'],
                'clc_concepto' => $row['clc_concepto'],
                'partida' => $row['partida'],
                'partida_generica' => $row['partida_generica'],
                'partida_especifica' => $row['partida_especifica'],
                'clave_financiamiento' => $row['clave_financiamiento'],
                'referencia' => $row['referencia'],
                'clc_fecha_elaboracion' => $row['clc_fecha_elaboracion'],
                'rfc_id' => $row['rfc_id'],
                'nombre' => $row['nombre'],
                'receptora' => "'" . $row['receptora'],
                'ordenante' => "'" . $row['ordenante'],
                'percepcion_bruto' => $row['percepcion_bruto'],
                'percepcion_iva' => $row['percepcion_iva'],
                'percepcion_neto' => $row['percepcion_neto'],
                'retencion_neto' => $row['retencion_neto']
            );
            array_push($datos, $dato);
        }

        if (count($datos) == 0) {
            $dato = array(
                'UR'    => '',
                'ur_nombre' => '',
                'Clc'    => ''
            );
            array_push($datos, $dato);
        }
        $regresar = $datos;
    } else {
        $regresar = $sql;
    }
    return $regresar;
}
