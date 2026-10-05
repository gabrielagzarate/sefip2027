<?php
/**
 * Description of ControladorObservarSeguimientoGastos
 *
 * @author  Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 23-Ene-2020
 * Comment: -----
 * Objective: Consulta bd para mostrar los gastos por mes.
 * Regla de Negocio: -----
 **/
function consultaGastosOperacion($mes = '', $trimestre = '', $gpo_id = '', $ur_id = '', $filtro = '', $estado = '')
{
    $and = '';

    if ($mes) {
        $mesSeleccionado = $mes;
        $ultimoDia = "";
        //Declaración de arreglo de meses
        $meses = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        //Validación ultimo día del mes
        $ultimoDia = cal_days_in_month(CAL_GREGORIAN, $mesSeleccionado, date("Y"));
        //Inicialización de parámetros de consulta de acuerdo al mes seleccionado
        $and = "p.usr_fecha <= '" . date("Y") . "-" . $mesSeleccionado . "-" . $ultimoDia . "' ";
        $presupuesto = "
            (SUM(apr_" . $meses[$mesSeleccionado] . ")) AS aprobado,
            (SUM(vig_" . $meses[$mesSeleccionado] . ")) AS vigente,
            (SUM(com_" . $meses[$mesSeleccionado] . ")) AS comprometido,
            (SUM(dev_" . $meses[$mesSeleccionado] . ")) AS devengado,
            (SUM(eje_" . $meses[$mesSeleccionado] . ")) AS ejercido ";
        $porcentajePresupuesto = " IFNULL(round(((100*(SUM(eje_" . $meses[$mesSeleccionado] . ")))/(SUM(vig_" . $meses[$mesSeleccionado] . "))),2),0) porcentajePresupuesto ";

        $sql = "
            SELECT
                gpo_id, ur_id, prg_id,spr_id,py_id,oa_id,prg_nombre,spr_nombre,oa_nombre," . $presupuesto . ",
                    ep.epg_id, py_nombre, toa_id, oa_origen,cf.cfi_clave,MAX(cf.cfi_nombre)cfi_nombre,
                    pdf_id,o.usr_fecha," . $porcentajePresupuesto . ",MAX(sft_id)sft_id,MAX(sor_id)sor_id,MAX(cfi_id)cfi_id,MAX(cfi_anio)cfi_anio,
                (
                    SELECT eme_id from cat_MensajeSeguimiento ms
                    LEFT JOIN  cat_HistorialMensajeSeguimiento hms on ms.msj_id=hms.msj_id and tim_id=2
                    WHERE " . $estado . " AND hms_mensual=" . $mes . "
                    AND ms.epg_id = ep.epg_id
                    ORDER BY ms.usr_fecha DESC
                    LIMIT 1
                ) AS eme_id
            FROM cat_ObraActividad o
                INNER JOIN enc_Programatico ep USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN cat_Subprograma using(gpo_id,ur_id,prg_id,spr_id)
                INNER JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
                INNER JOIN cat_Proyecto USING(gpo_id, ur_id, prg_id, spr_id, py_id)
                INNER JOIN presupuesto p USING(gpo_id, ur_id, ue_id, epg_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN presupuestoAprobado pa using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoVigente pv using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoComprometido pc using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoDevengado pd using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoEjercido pe using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                LEFT JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento".APP_EJERCICIO." AS cf USING (fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            WHERE gpo_id=" . $gpo_id . " and ur_id=" . $ur_id . "
                    " . $filtro . " AND eoa_id = 4
            GROUP BY prg_id,spr_id,py_id,oa_id,cf.cfi_clave
        ";
    } elseif ($trimestre) {
        switch ($trimestre) {
            case 1:
                $and = "p.usr_fecha <= '".APP_EJERCICIO."-03-31' ";
                $presupuesto = "
                    (SUM(apr_ene)+ SUM(apr_feb)+ SUM(apr_mar)) AS aprobado,
                    (SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar)) AS vigente,
                    (SUM(com_ene) + SUM(com_feb) + SUM(com_mar)) AS comprometido,
                    (SUM(dev_ene) + SUM(dev_feb) + SUM(dev_mar)) AS devengado,
                    (SUM(eje_ene) + SUM(eje_feb) + SUM(eje_mar)) AS ejercido ";
                $porcentajePresupuesto = " IFNULL(round(((100*(SUM(eje_ene) + SUM(eje_feb) + SUM(eje_mar)))/(SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar))),2),0) porcentajePresupuesto ";
                break;
            case 2:
                $and = "p.usr_fecha <= '".APP_EJERCICIO."-06-30' ";
                $presupuesto = "
                    (SUM(apr_abr)+SUM(apr_may)+ SUM(apr_jun)) AS aprobado,
                    (SUM(vig_abr) + SUM(vig_may) + SUM(vig_jun)) AS vigente,
                    (SUM(com_abr) + SUM(com_may) + SUM(com_jun)) AS comprometido,
                    (SUM(dev_abr) + SUM(dev_may) + SUM(dev_jun)) AS devengado,
                    (SUM(eje_abr) + SUM(eje_may) + SUM(eje_jun)) AS ejercido ";
                $porcentajePresupuesto = " IFNULL(round(((100*(SUM(eje_abr) + SUM(eje_may) + SUM(eje_jun))/(SUM(vig_abr) + SUM(vig_may) + SUM(vig_jun)))),2),0) porcentajePresupuesto ";
                break;
            case 3:
                $and = "p.usr_fecha <= '".APP_EJERCICIO."-09-30' ";
                $presupuesto = "
                    (SUM(apr_jul)+SUM(apr_ago)+ SUM(apr_sep)) AS aprobado,
                    (SUM(vig_jul) + SUM(vig_ago) + SUM(vig_sep)) AS vigente,
                    (SUM(com_jul) + SUM(com_ago) + SUM(com_sep)) AS comprometido,
                    (SUM(dev_jul) + SUM(dev_ago) + SUM(dev_sep)) AS devengado,
                    (SUM(eje_jul) + SUM(eje_ago) + SUM(eje_sep)) AS ejercido ";
                $porcentajePresupuesto = " IFNULL(round(((100*(SUM(eje_jul) + SUM(eje_ago) + SUM(eje_sep)))/(SUM(vig_jul) + SUM(vig_ago) + SUM(vig_sep))),2),0) porcentajePresupuesto ";
                break;
            case 4:
                $and = "p.usr_fecha <= '".APP_EJERCICIO."-12-31' ";
                $presupuesto = "
                    (SUM(apr_oct) + SUM(apr_nov) + SUM(apr_dic)) AS aprobado,
                    (SUM(vig_oct) + SUM(vig_nov) + SUM(vig_dic)) AS vigente,
                    (SUM(com_oct) + SUM(com_nov) + SUM(com_dic)) AS comprometido,
                    (SUM(dev_oct) + SUM(dev_nov) + SUM(dev_dic )) AS devengado,
                    (SUM(eje_oct) + SUM(eje_nov) + SUM(eje_dic)) AS ejercido ";
                $porcentajePresupuesto = " IFNULL(round(((100*(SUM(eje_oct) + SUM(eje_nov) + SUM(eje_dic))/(SUM(vig_oct) + SUM(vig_nov) + SUM(vig_dic)))),2),0) porcentajePresupuesto ";
                break;
        }

        $sql = "
            SELECT * FROM (SELECT
                gpo_id, ur_id, prg_id,spr_id,py_id,oa_id,prg_nombre,spr_nombre,oa_nombre," . $presupuesto . ",
                ep.epg_id, py_nombre, toa_id, oa_origen,cf.cfi_clave,MAX(cf.cfi_nombre)cfi_nombre,
                pdf_id,o.usr_fecha," . $porcentajePresupuesto . ",MAX(sft_id)sft_id,MAX(sor_id)sor_id,MAX(cfi_id)cfi_id,MAX(cfi_anio)cfi_anio
            FROM cat_ObraActividad o
                INNER JOIN enc_Programatico ep USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN cat_Subprograma using(gpo_id,ur_id,prg_id,spr_id)
                INNER JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
                INNER JOIN cat_Proyecto USING(gpo_id, ur_id, prg_id, spr_id, py_id)
                INNER JOIN presupuesto p USING(gpo_id, ur_id, ue_id, epg_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN presupuestoAprobado pa using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoVigente pv using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoComprometido pc using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoDevengado pd using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoEjercido pe using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                LEFT JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento".APP_EJERCICIO." AS cf USING (fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            WHERE gpo_id=" . $gpo_id . " and ur_id=" . $ur_id . "
                    " . $filtro . " AND eoa_id = 4
            GROUP BY prg_id,spr_id,py_id,oa_id,cf.cfi_clave
            ) C
            LEFT JOIN (
                #se modifica ejercicio 2022 para no mostrar los reg. metas y presupuesto en cero, mostrar el estado del ultimo msj dependiendo del epg
                SELECT   m.epg_id, MAX(m.pii_id) pii_id, max(hms.eme_id) eme_id
                FROM cat_MetaActividad m
                INNER JOIN cat_MetaActividadTrimestre mt using(pii_id, epg_id)
                INNER JOIN vin_ProductoIntermedioIndicador pii USING(pii_id)
                INNER JOIN cat_Indicador ci USING(nim_id,ind_id)
                INNER JOIN enc_Programatico epg  USING(epg_id)
                LEFT JOIN cat_MensajeSeguimiento ms USING(epg_id)
                LEFT JOIN (
                    SELECT *
                    FROM cat_HistorialMensajeSeguimiento hms
                    INNER JOIN (
                        SELECT ms.epg_id, MAX(hms.usr_fecha) fecha_msj
                        FROM  ".BD_CTRL.".cat_MensajeSeguimiento ms
                        LEFT  JOIN ".BD_CTRL.".cat_HistorialMensajeSeguimiento hms using(msj_id)
                        WHERE " . $estado . "
                        AND tim_id=2
                        AND hms_trimestre = " . $trimestre . "
                        GROUP BY epg_id
                    ) AS msj ON (msj.fecha_msj = hms.usr_fecha)
                ) hms on (ms.msj_id = hms.msj_id AND hms.hms_trimestre = " . $trimestre . " AND tim_id = 2 AND  " . $estado . " )
                WHERE mt.tri_id = " . $trimestre . "
                AND !(mact_original=0 AND mact_modificado=0)
                GROUP BY m.epg_id
            ) asd using (epg_id)
            # GROUP BY C.prg_id,C.spr_id,C.py_id,C.oa_id,C.cfi_clave,hms_id //2019
            GROUP BY C.prg_id,C.spr_id,C.py_id,C.oa_id,C.cfi_clave
            HAVING pii_id IS NOT NULL AND !(aprobado = 0 AND  vigente = 0) #se agrega validación
            ORDER BY C.cfi_clave,prg_id,spr_id,py_id,oa_id;
        ";
    } // fin else trimestre
    $conexion = new AccesoDatos();
    $conexion->conectar();
    $conexion->setSql($sql);
    $conexion->consultar();
    $conexion->desconectar();

    $result = array();

    while ($row = $conexion->extraer()) {
        $dato = array(
            'cfi_clave' => $row['cfi_clave'],
            'cfi_nombre' => $row['cfi_nombre'],
            'prg_id' => $row['prg_id'],
            'prg_nombre' => $row['prg_nombre'],
            'spr_id' => $row['spr_id'],
            'spr_nombre' => $row['spr_nombre'],
            'py_id' => $row['py_id'],
            'py_nombre' => $row['py_nombre'],
            'eme_id' => $row['eme_id'],
            'oa_id' => $row['oa_id'],
            'epg_id' => $row['epg_id'],
            'oa_nombre' => $row['oa_nombre'],
            'aprobado' => $row['aprobado'],
            'vigente' => $row['vigente'],
            'comprometido' => $row['comprometido'],
            'devengado' => $row['devengado'],
            'ejercido' => $row['ejercido'],
            'porcentajePresupuesto' => $row['porcentajePresupuesto'],
        );
        array_push($result, $dato);
    }
    return array_reverse($result);
}