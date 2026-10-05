<?php

/**
 * Description of consultaCedula
 *
 * @author Israel Faustino Cruz
 * @update Pacheco Adriana <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 20-ago-2018
 * Comment:
 * Objective: Genera la consulta de las cedulas de costeo
 * Regla de Negocio:
 *
 * */

function consultaCedula($gpo_id, $ur_id, $ue_id, $epg_id, $ecf_id, $ccd_id, $rla_id, $cve_id, $ced_id = '')
{
    $parametros =[];
    if ($ue_id != '' && $rla_id == 0) {
        $ue_sql = " AND ue_id = :ue";
        $parametros += ['ue' => $ue_id];
    } else {
        $ue_sql = "";
    }

    if ($epg_id != '') {
        $epgSql = " AND epg_id = :epg";
        $parametros += ['epg' => $epg_id];
    } else {
        $epgSql = "";
    }

    if ($ecf_id != '') {
        $ecfSql = " AND ecf_id = :ecf";
        $parametros += ['ecf' => $ecf_id];
    } else {
        $ecfSql = "";
    }

    $ue_idSQL = "";
    $clave_id = "";
    $num_ced = "";
    $atributo = "";

    switch ($ccd_id) {
        case 1: // Cedula Alimentacion
            $atributo = "asr_nombre, gbf_nombre, ali_beneficiarios, ali_cuotaDiaria, ali_diasOtorgados, ";
            $atributoSubconsulta = "ced_id, asr_nombre, if( gbf_id = 9, ali_beneficiarioOtros, gbf_nombre ) AS gbf_nombre, ali_beneficiarios, ali_cuotaDiaria, ali_diasOtorgados, ";
            $join = "
                    INNER JOIN poa_Alimentacion USING( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id )
                    INNER JOIN cat_AreaServicio USING( asr_id )
                    INNER JOIN cat_GrupoBeneficiario USING( gbf_id )
                    ";
            $agrupador = "pg_id, pe_id, ced_id ";
            break;
        case 2: // Cedula Servicios Personales
            $atributo = "epz_nombre, rla_nombre, ";
            $atributoSubconsulta = "ced_id, epz_nombre, rla_nombre, ";
            $join = "
                    INNER JOIN poa_ServiciosPersonales USING( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id )
                    INNER JOIN cat_EstadoPlaza USING( epz_id )
                    INNER JOIN cat_RelacionLaboral USING( rla_id )
                    ";
            $agrupador = "pg_id, pe_id, ced_id ";
            break;

        case 3: // Cedula Telefonia
            $atributo = "ccc_nombre, ubicacion, tcl_numeroCelular, tcl_mensualidad, ";
            $atributoSubconsulta = "ced_id, ccc_nombre, CASE ccc_id WHEN 0 THEN tcl_ubicacion ELSE utf_nombre END AS ubicacion, tcl_numeroCelular, tcl_mensualidad, ";
            $join = "
                    INNER JOIN poa_TelefoniaCelular USING( gpo_id, ur_id, ue_id, epg_id, ecf_id, pg_id, pe_id )
                    INNER JOIN cat_CargoCuotaCelular USING( ccc_id )
                    INNER JOIN cat_UbicacionTelefonia USING( utf_id )
                    ";
            $clave_id = " AND cve_id = :cve";
            $parametros += ['cve' => $cve_id];
            $agrupador = "pg_id, pe_id, ced_id ";
            break;
        case 4: // Cedula Vehiculo
            $atributo = "pqv_id, usv_nombre, pqv_numeroPlacas, pqv_tipo, ubv_nombre, cve_id, impuesto, seguro, mantenimiento, combustible, ";
            $atributoSubconsulta = "pqv_id, usv_nombre, pqv_numeroPlacas, pqv_tipo, ubv_nombre,  MAX(cve_id) as cve_id,
                    SUM( CASE cve_id WHEN 1 THEN mon_ene + mon_feb + mon_mar + mon_abr + mon_may + mon_jun + mon_jul + mon_ago + mon_sep + mon_oct + mon_nov + mon_dic ELSE 0 END ) AS impuesto,
                    SUM( CASE cve_id WHEN 2 THEN mon_ene + mon_feb + mon_mar + mon_abr + mon_may + mon_jun + mon_jul + mon_ago + mon_sep + mon_oct + mon_nov + mon_dic
                        WHEN 3 THEN mon_ene + mon_feb + mon_mar + mon_abr + mon_may + mon_jun + mon_jul + mon_ago + mon_sep + mon_oct + mon_nov + mon_dic ELSE 0 END ) AS seguro,
                    SUM( CASE cve_id WHEN 4 THEN mon_ene + mon_feb + mon_mar + mon_abr + mon_may + mon_jun + mon_jul + mon_ago + mon_sep + mon_oct + mon_nov + mon_dic
                        WHEN 6 THEN mon_ene + mon_feb + mon_mar + mon_abr + mon_may + mon_jun + mon_jul + mon_ago + mon_sep + mon_oct + mon_nov + mon_dic ELSE 0 END ) AS mantenimiento,
                    SUM( CASE cve_id WHEN 5 THEN mon_ene + mon_feb + mon_mar + mon_abr + mon_may + mon_jun + mon_jul + mon_ago + mon_sep + mon_oct + mon_nov + mon_dic ELSE 0 END ) AS combustible,
                    ";
            $join = "
                    INNER JOIN poa_VehiculoMaquina USING (gpo_id, ur_id, ue_id, epg_id, ecf_id, pg_id, pe_id)
                    INNER JOIN cat_ParqueVehicular USING (pqv_id, gpo_id, ur_id, ue_id, epg_id, ecf_id)
                    INNER JOIN cat_UsoVehiculo USING (usv_id)
                    INNER JOIN cat_UbicacionVehiculo USING (ubv_id)
                    INNER JOIN cat_ConceptoVehiculo USING (cve_id)
                    ";
            $agrupador = "pqv_id";
            break;
        case 5: // Cedula Arrendamiento
            $atributo = "arr_uso, arr_ubicacion, arr_inicioPeriodo, arr_terminoPeriodo, arr_observaciones, ";
            $atributoSubconsulta = "ced_id, arr_uso, arr_ubicacion, arr_inicioPeriodo, arr_terminoPeriodo, arr_observaciones, ";
            $join = "
                    INNER JOIN poa_Arrendamiento USING( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id )
                    JOIN enc_PartEspCed pe USING(ccd_id,pg_id,pe_id)
                    ";
            $agrupador = "pg_id, pe_id, ced_id ";
            $clave_id = " AND cve_id = :cve" ; //se modifica por que marcaba error en la consulta al no tener datos el filtro
            $parametros += ['cve' => (isset($cve_id) ? $cve_id : "''")];
            if ($ced_id) {
                $num_ced = " AND ced_id = :ced";
                $parametros += ['ced' => $ced_id];
            }

            break;
        case 6: // Cedula SeguroFianza
            $atributo = "sef_concepto, sef_tipoPago, sef_inicioPeriodo, sef_terminoPeriodo, importe, ";
            $atributoSubconsulta = "ced_id, sef_concepto, sef_tipoPago, sef_inicioPeriodo, sef_terminoPeriodo, if( sef_tipoPago = 'A', sef_mensualidad, mon_ene) AS importe, ";
            $join = "
                    INNER JOIN poa_SeguroFianza USING( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id )
                    ";
            $agrupador = "pg_id, pe_id, ced_id ";
            if ($ced_id) {
                $num_ced = " AND ced_id = :ced";
                $parametros += ['ced' => $ced_id];
            }
            break;
        case 7: // Cedula ServicioVigilancia
            $atributo = "svg_area, tct_nombre, tct_costoMensual, cantidad, ";
            $atributoSubconsulta = "ced_id, svg_area, tct_nombre, tct_costoMensual, SUM( uni_ene + uni_feb + uni_mar + uni_abr + uni_may + uni_jun + uni_jul + uni_ago + uni_sep + uni_oct + uni_nov + uni_dic ) AS cantidad, ";
            $join = "
                    INNER JOIN poa_ServicioVigilancia USING( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id )
                    INNER JOIN cat_TipoContrato USING( tct_id )
                    ";
            $agrupador = "pg_id, pe_id, ced_id ";
            break;
        case 9: // Cedula OtrasPartidas
            $atributo = "otp_unidadMedida, otp_costo, otp_archivo, ";
            $atributoSubconsulta = "ced_id, COALESCE(otp_unidadMedida, umv_nombre) AS otp_unidadMedida, otp_costo, otp_archivo, ";
            $join = "
                    INNER JOIN poa_OtrasPartidas USING( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id )
                    LEFT JOIN cat_UnidadMedidaVariable ON cat_UnidadMedidaVariable.umv_id = poa_OtrasPartidas.umv_id
                    ";
            $agrupador = "pg_id, pe_id, ced_id ";
            if ($ced_id) {
                $num_ced = " AND ced_id = :ced";
                $parametros += ['ced' => $ced_id];
            }
            break;
        case 20: // INVENTARIO DE PLAZAS POR UNIDAD EJECUTORA
            $atributoSubconsulta = "rla_id,rla_nombre ,SUM(inp_activas) AS Activas,SUM(inp_vacantes) AS Vacantes,SUM(inp_litigio) AS Litigio,SUM(inp_activas)+ SUM(inp_vacantes) + SUM(inp_litigio) as total,gpo_id,ur_id,ur_nombre,ur_nombreTitular, ur_nombreAdministrativo, ur_cargoTitular, ur_cargoAdministrativo,ur_nombrePlaneacion,ur_cargoPlaneacion,eur_id";
            $join = "
                INNER JOIN enc_NivelRelacionLaboral USING( rla_id )
                INNER JOIN enc_TabuladorUr USING( tab_id)
                INNER JOIN cat_InventarioPlazas USING(gpo_id,ur_id,enr_id)
                INNER JOIN cat_UnidadResponsable using(gpo_id,ur_id)
                INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)";
            $agrupador = "rla_id";
            break;
        case 21: // DESPLIEGUE POR RELACIÓN LABORAL
            $atributo = '"rla"es,rla_id as clave,rla_nombre nombre,gpo_id,ur_id,rla_id,""nrl_id,"" nrl_clave,"" nrl_descripcion,sum(inp_activas) as inp_activas,sum(inp_vacantes) as inp_vacantes,sum(inp_litigio) as inp_litigio,sum(inp_activas)+ sum(inp_vacantes)+sum(inp_litigio) AS total,
        ur_nombre,ur_nombreTitular, ur_nombreAdministrativo, ur_cargoTitular, ur_cargoAdministrativo,ur_nombrePlaneacion,ur_cargoPlaneacion,eur_id,1 as nivel';
            $atributoSubconsulta = '"nrl"es,nrl_id as clave,nrl_descripcion nombre,gpo_id,ur_id,rla_id, nrl_id,nrl_clave,nrl_descripcion,sum(inp_activas) as inp_activas,sum(inp_vacantes) as inp_vacantes,sum(inp_litigio) as inp_litigio,sum(inp_activas)+ sum(inp_vacantes)+sum(inp_litigio) AS total,
        ur_nombre,ur_nombreTitular, ur_nombreAdministrativo, ur_cargoTitular, ur_cargoAdministrativo,ur_nombrePlaneacion,ur_cargoPlaneacion,eur_id,2 as nivel';
            $join = "
                INNER JOIN enc_TabuladorUr USING(tab_id)
                INNER JOIN enc_NivelRelacionLaboral USING(rla_id,tab_id,nrl_id)
                INNER JOIN cat_RelacionLaboral USING(rla_id)
                INNER JOIN cat_InventarioPlazas USING(gpo_id,ur_id,enr_id)
                INNER JOIN cat_UnidadResponsable using(gpo_id,ur_id)
                INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id) ";
            $agrupador = "gpo_id,ur_id,rla_id,nrl_id,nivel, nrl_descripcion, nrl_clave";
            $ue_idSQL = " AND ue_id = :ue";
            $parametros += ['ue' => $ue_id];
            break;
        default:
            echo 'kym';
            break;
    }
    $ced_id = $ccd_id != 4 ? "ced_id," : " ";
    $selectPG_PE = $ccd_id == 4 ? "MAX(pg_id) AS pg_id, MAX(pe_id) AS pe_id" : "pg_id, pe_id";

    $sql = "
            SELECT
                gpo_id,
                ur_clave,  ur_id,
                ur_nombre, ue_id,
                ue_nombre, epg_id,
                prg_id, prg_nombre,
                spr_id, spr_nombre,
                py_id,  py_nombre,
                oa_id,  oa_nombre,
                pg_id,  pg_nombre,
                cedula.pe_id,  pe_nombre,
                ecf_id, ter_id,
                ter_nombre,
                cfi_clave,
                cfi_nombre,
                $ced_id
                $atributo
                total, nivel
            FROM (
                SELECT
                    gpo_id,
                    ur_id,
                    ue_id,
                    epg_id,
                    prg_id,
                    spr_id,
                    py_id,
                    oa_id,
                    $selectPG_PE,
                    ecf_id,
                    $atributoSubconsulta
                    SUM( mon_ene + mon_feb + mon_mar + mon_abr + mon_may + mon_jun + mon_jul + mon_ago + mon_sep + mon_oct + mon_nov + mon_dic ) AS total,
                    9 AS nivel
                FROM presupuesto $join
                WHERE gpo_id = :gpo
                    AND ur_id = :ur
                    $clave_id
                    $num_ced
                    $ue_sql
                    $epgSql
                    $ecfSql
                GROUP BY gpo_id, ur_id, prg_id, spr_id, ue_id, py_id, oa_id, ecf_id, $agrupador
            )cedula
                INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
                INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                INNER JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " cs USING (cff_id, fte_id, sft_id, ori_id, sor_id)
                INNER JOIN " . BD_SINPRES . ".cat_TipoRecurso USING (ter_id)
                INNER JOIN cat_PartidaGenerica USING (pg_id)
                INNER JOIN cat_PartidaEspecifica USING (pg_id, pe_id)
                INNER JOIN cat_ObraActividad USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, ue_id)
                INNER JOIN cat_Proyecto USING (gpo_id, ur_id, prg_id, spr_id, py_id)
                INNER JOIN cat_Subprograma USING (gpo_id, ur_id, prg_id, spr_id)
                INNER JOIN cat_Programa USING (gpo_id, ur_id, prg_id)
                INNER JOIN cat_UnidadEjecutora USING (gpo_id, ur_id, ue_id)
                INNER JOIN cat_UnidadResponsable USING (gpo_id, ur_id)
            ORDER BY gpo_id, ur_id, prg_id, spr_id, ue_id, py_id, oa_id, cfi_clave, pe_id, $ced_id nivel
        ";
    $parametros += [
        'gpo' => $gpo_id,
        'ur' => $ur_id,];
    if ($ccd_id == 20) {
        $sql = "
            SELECT
                DISTINCT $atributoSubconsulta
            FROM cat_RelacionLaboral
            $join
            WHERE gpo_id = :gpo
                AND ur_id = :ur
                $ecfSql
                $epgSql
            GROUP BY $agrupador
            HAVING total > 0 ";
    } else if ($ccd_id == 21) {
        $sql = "
            SELECT
                $atributo
            FROM cat_NivelRelacionLaboral
            $join
            WHERE gpo_id = :gpo
                AND ur_id = :ur
                AND rla_id= :rla $ecfSql $epgSql
            GROUP BY gpo_id,ur_id,rla_id
            HAVING total > 0
            UNION ALL
            SELECT
                $atributoSubconsulta
            FROM cat_NivelRelacionLaboral
            $join
            WHERE gpo_id = :gpo
                AND ur_id = :ur
                AND rla_id= :rla
                $ecfSql
                $epgSql
                $ue_idSQL
            GROUP BY $agrupador
            HAVING total > 0
            ORDER BY gpo_id,ur_id,rla_id,nrl_id,nivel";
            $parametros += ['rla' => $rla_id];
    }

    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar($parametros);
    $conexion->desconectar();

    $datos = array();
    while ($row = $conexion->extraer()) {

        $dato = array(
            'gpo_id' => $row['gpo_id'],
            'ur_id' => $row['ur_id'],
            'ur_clave' => isset($row['ur_clave']) ? $row['ur_clave'] : '',
            'ur_nombre' => $row['ur_nombre'],
            'ue_id' => isset($row['ue_id']) ? $row['ue_id'] : '',
            'ue_nombre' => isset($row['ue_nombre']) ? $row['ue_nombre'] : '',
            'epg_id' => isset($row['epg_id']) ? $row['epg_id'] : '',
            'prg_id' => isset($row['prg_id']) ? $row['prg_id'] : '',
            'prg_nombre' => isset($row['prg_nombre']) ? $row['prg_nombre'] : '',
            'spr_id' => isset($row['spr_id']) ? $row['spr_id'] : '',
            'spr_nombre' => isset($row['spr_nombre']) ? $row['spr_nombre'] : '',
            'py_id' => isset($row['py_id']) ? $row['py_id'] : '',
            'py_nombre' => isset($row['py_nombre']) ? $row['py_nombre'] : '',
            'oa_id' => isset($row['oa_id']) ? $row['oa_id'] : '',
            'oa_nombre' => isset($row['oa_nombre']) ? $row['oa_nombre'] : '',
            'pg_id' => isset($row['pg_id']) ? $row['pg_id'] : '',
            'pg_nombre' => isset($row['pg_nombre']) ? $row['pg_nombre'] : '',
            'pe_id' => isset($row['pe_id']) ? $row['pe_id'] : '',
            'pe_nombre' => isset($row['pe_nombre']) ? $row['pe_nombre'] : '',
            'ecf_id' => isset($row['ecf_id']) ? $row['ecf_id'] : '',
            'ter_id' => isset($row['ter_id']) ? $row['ter_id'] : '',
            'ter_nombre' => isset($row['ter_nombre']) ? $row['ter_nombre'] : '',
            'cfi_clave' => isset($row['cfi_clave']) ? $row['cfi_clave'] : '',
            'cfi_nombre' => isset($row['cfi_nombre']) ? $row['cfi_nombre'] : '',
            'ced_id' => isset($row['ced_id']) ? $row['ced_id'] : '',
            'total' => $row['total'],
            'nivel' => isset($row['nivel']) ? $row['nivel'] : '',
        );

        switch ($ccd_id) {
            case 1:
                $dato['asr_nombre'] = $row['asr_nombre'];
                $dato['gbf_nombre'] = $row['gbf_nombre'];
                $dato['ali_beneficiarios'] = $row['ali_beneficiarios'];
                $dato['ali_cuotaDiaria'] = $row['ali_cuotaDiaria'];
                $dato['ali_diasOtorgados'] = $row['ali_diasOtorgados'];
                break;
            case 2:
                $dato['epz_nombre'] = $row['epz_nombre'];
                $dato['rla_nombre'] = $row['rla_nombre'];
                break;
            case 3:
                $dato['ccc_nombre'] = $row['ccc_nombre'];
                $dato['ubicacion'] = $row['ubicacion'];
                $dato['tcl_numeroCelular'] = $row['tcl_numeroCelular'];
                $dato['tcl_mensualidad'] = $row['tcl_mensualidad'];
                break;
            case 4:
                $dato['pqv_id'] = $row['pqv_id'];
                $dato['usv_nombre'] = $row['usv_nombre'];
                $dato['ubicacion'] = $row['ubv_nombre'];
                $dato['pqv_tipo'] = $row['pqv_tipo'];
                $dato['pqv_numeroPlacas'] = $row['pqv_numeroPlacas'];
                $dato['impuesto'] = $row['impuesto'];
                $dato['seguro'] = $row['seguro'];
                $dato['mantenimiento'] = $row['mantenimiento'];
                $dato['combustible'] = $row['combustible'];
                break;
            case 5:
                $dato['arr_uso'] = $row['arr_uso'];
                $dato['arr_ubicacion'] = $row['arr_ubicacion'];
                $dato['arr_inicioPeriodo'] = $row['arr_inicioPeriodo'];
                $dato['arr_terminoPeriodo'] = $row['arr_terminoPeriodo'];
                $dato['arr_observaciones'] = $row['arr_observaciones'];
                break;
            case 6:
                $dato['sef_concepto'] = $row['sef_concepto'];
                $dato['sef_tipoPago'] = $row['sef_tipoPago'];
                $dato['sef_inicioPeriodo'] = $row['sef_inicioPeriodo'];
                $dato['sef_terminoPeriodo'] = $row['sef_terminoPeriodo'];
                $dato['importe'] = $row['importe'];
                break;
            case 7:
                $dato['svg_area'] = $row['svg_area'];
                $dato['tct_nombre'] = $row['tct_nombre'];
                $dato['tct_costoMensual'] = $row['tct_costoMensual'];
                $dato['cantidad'] = $row['cantidad'];
                break;
            case 9:
                $dato['otp_unidadMedida'] = $row['otp_unidadMedida'];
                $dato['otp_costo'] = $row['otp_costo'];
                $dato['otp_archivo'] = $row['otp_archivo'];
                break;
            case 20:
                $dato['rla_nombre'] = $row['rla_nombre'];
                $dato['Activas'] = $row['Activas'];
                $dato['Vacantes'] = $row['Vacantes'];
                $dato['Litigio'] = $row['Litigio'];
                $dato['total'] = $row['total'];
                break;
            case 21:
                $dato['nrl_clave'] = $row['nrl_clave'];
                $dato['nombre'] = $row['nombre'];
                $dato['inp_activas'] = $row['inp_activas'];
                $dato['inp_vacantes'] = $row['inp_vacantes'];
                $dato['inp_litigio'] = $row['inp_litigio'];
                $dato['total'] = $row['total'];
                $dato['clave'] = $row['clave'];
                $dato['nivel_relacion'] = $row['nivel'];
                break;
            default:
                echo 'kym';
                break;
        }

        array_push($datos, $dato);
    }

    if (count($datos) == 0) {
        $dato = array(
            'gpo_id' => $gpo_id,
            'ur_id' => $ur_id,
            'ur_clave' => $gpo_id . $ur_id,
            'ur_nombre' => '',
            'ue_id' => '',
            'ue_nombre' => '',
            'epg_id' => $epg_id,
            'prg_id' => '',
            'prg_nombre' => '',
            'spr_id' => '',
            'spr_nombre' => '',
            'py_id' => '',
            'py_nombre' => '',
            'oa_id' => '',
            'oa_nombre' => '',
            'pg_id' => '',
            'pg_nombre' => '',
            'pe_id' => '',
            'pe_nombre' => '',
            'ecf_id' => $ecf_id,
            'ter_id' => '',
            'ter_nombre' => '',
            'cfi_clave' => '',
            'cfi_nombre' => ',,',
            'ced_id' => '',
            'total' => 0.00,
            'nivel' => 1,
        );
        array_push($datos, $dato);
    }

    return $datos;
}
