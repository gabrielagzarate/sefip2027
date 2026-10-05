<?php

/**
 * Description of function consultaClcFondo
 *
 * author: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 18-Feb-2019
 * Migrated: 24-Feb-2019
 * Comment: -----
 * Objective: Consulta datos de la base de datos las CLC's pendientes.
 * Regla de Negocio: -----
 * 
 * */
ini_set('memory_limit', 0);
ini_set('max_execution_time', 0);
set_time_limit(0);

function consultaClcFondo($documento, $ejercicio, $clc, $folio, $numerour, $importeMax, $importeMin, $fechaMin, $fechaMax, $paginador, $otrosParametros)
{

    $sqlCtrlAnterior = "";
    $estado2018 = "";
    $estado = ($ejercicio >= 2026) ? " WHERE C.ecl_id > 9 " : " WHERE ant.ecl_id > 9";

    $importeMax = trim($importeMax, " $");
    $importeMin = trim($importeMin, " $");
    $importeMax = str_replace(',', '', $importeMax);
    $importeMin = str_replace(',', '', $importeMin);

    $importeMin = (float)$importeMin;
    $importeMax = (float)$importeMax;
    $importeMin = $importeMin == 0 ? '' : "$importeMin";
    $importeMax = $importeMax == 0 ? '' : "$importeMax";

    if ($fechaMin != "") {
        $arrFecha = explode("/", $fechaMin);
        $fechaMin = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
    }
    if ($fechaMax != "") {
        $arrFecha = explode("/", $fechaMax);
        $fechaMax = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
    }
    $sqlExtra = "";

    if ($otrosParametros->beneficiario != '') {
        $sqlExtra .= " AND C.clc_beneficiario like '%" . $otrosParametros->beneficiario . "%' ";
    }

    if ($otrosParametros->concepto != '') {
        $sqlExtra .= " AND C.clc_concepto like '%" . $otrosParametros->concepto . "%' ";
    }
    if ($otrosParametros->lote != '') {
        $nuevoLote = "";
        $tok = strtok($otrosParametros->lote, ",");
        while ($tok !== false) {
            $nuevoLote .= "'" . $tok . "',";
            $tok = strtok(",");
        }
        $nuevoLote = substr($nuevoLote, 0, -1);
        $sqlExtra .= " AND CONCAT( IF ( m.fpa_id = 1, 'T', IF (m.fpa_id = 2, 'C', 'L') ), LPAD(m.msc_id, 2, '0'), LPAD(m.lot_id, 4, '0'), SUBSTR(m.lot_ejercicio, 3) ) in (" . $nuevoLote . ") ";
    }
    if ($otrosParametros->claveBancaria != '') {
        $sqlExtra .= " AND CB.cbs_id ='" . $otrosParametros->claveBancaria . "' ";
    }
    if ($otrosParametros->cuentaDestino != '') {
        $sqlExtra .= " AND C.bur_id ='" . $otrosParametros->cuentaDestino . "' ";
    }
    if ($otrosParametros->claveFinanciamiento != '') {
        if ($ejercicio == 2017) {
            $sqlExtra .= " AND CV.ecf_clave ='" . $otrosParametros->claveFinanciamiento . "' ";
        } else {
            $sqlExtra .= " AND CF.ecf_clave ='" . $otrosParametros->claveFinanciamiento . "' ";
        }
    }
    if ($otrosParametros->loteEgreso != '') {
        $sqlExtra .= " AND LE.loe_clave ='" . $otrosParametros->loteEgreso . "' ";
    }
    if ($folio != '') {
        $sqlExtra .= " AND CX.clc_cereCo IN (" . $folio . ") ";
    }
    if ($importeMin != '' && $importeMax == '')
        $sqlExtra .= " AND (C.clc_percepcion - C.clc_retencion) >= $importeMin ";
    if ($importeMin == '' && $importeMax != '')
        $sqlExtra .= " AND (C.clc_percepcion - C.clc_retencion) <= $importeMax ";
    if ($importeMin != '' && $importeMax != '')
        $sqlExtra .= " AND ((C.clc_percepcion - C.clc_retencion) BETWEEN $importeMin AND $importeMax) ";

    if ($fechaMin != '' && $fechaMax == '')
        $sqlExtra .= " AND C.clc_fechaElaboracion >= '$fechaMin' ";
    if ($fechaMin == '' && $fechaMax != '')
        $sqlExtra .= " AND C.clc_fechaElaboracion <= '$fechaMax' ";
    if ($fechaMin != '' && $fechaMax != '')
        $sqlExtra .= " AND (C.clc_fechaElaboracion BETWEEN '$fechaMin 00:00:00' AND '$fechaMax 23:59:59') ";


    if ($clc != "") {
        $sqlExtra .= " AND C.clc_id in (" . $clc . ") ";
    }
    if ($numerour != "") {
        $sqlExtra .= " AND CONCAT(C.gpo_id, LPAD( C.ur_id,2,0)) =" . $numerour;
    }
    if ($documento != 0) {
        $sqlExtra .= " AND clc_tipoCereCo= " . $documento;
    }
    if ($otrosParametros->nombreUR != "") {
        $sqlExtra .= " AND  CONCAT(C.gpo_id, LPAD( C.ur_id,2,0)) =" . $otrosParametros->nombreUR;
    }
    if ($otrosParametros->estadoClc != "") {
        if ($ejercicio >= 2026) {
            $sqlExtra .= " AND  C.ecl_id =" . $otrosParametros->estadoClc;
            $estado = 'WHERE C.ecl_id =' . $otrosParametros->estadoClc;
        } else {
            $sqlCtrlAnterior = " AND IF(ant.ecl_id IS NOT NULL, ant.ecl_id, C.ecl_id) =" . $otrosParametros->estadoClc;
            $estado = 'WHERE ant.ecl_id =' . $otrosParametros->estadoClc;
            $estado2018 .= (strlen($estado2018) == 0) ? 'WHERE ' : ' AND ';
            $estado2018 .= 'C.ecl_id =' . $otrosParametros->estadoClc;
            if ($numerour != "") {
                $estado2018 .= (strlen($estado2018) == 0) ? 'WHERE ' : ' AND ';
                $estado2018 .= '  CONCAT(C.gpo_id, LPAD( C.ur_id,2,0)) =' . $numerour;
            }
        }
    }
    if ($otrosParametros->rfc != "") {
        $sqlExtra .= " AND  n.rfc_id='" . $otrosParametros->rfc . "'";
    }

    switch ($otrosParametros->estatal) {
        case 1:
            $sqlExtra .= " AND  CB.ter_id ='A'";
            break;
        case 2:
            $sqlExtra .= " AND  CB.ter_id ='B'";
            break;
    }

    $parametro = [
        ':ejercicio' => $ejercicio
    ];

    $BD_CTRL = "ctrl" . $ejercicio;
    switch ($ejercicio) {
        case 2017:
            $sql = "
                SELECT 
                    C.gpo_id, C.ur_id, C.clc_id, C.bur_id,
                    CASE
                        WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                        WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                        WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                    END AS importe,C.clc_concepto, CONCAT(C.gpo_id,C.ur_id) AS ur, C.clc_fechaElaboracion,
                    if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,    C.clc_beneficiario,
                    C.mpc_nombre,
                    IF(ant.ecl_id IS NULL,
                        (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2017 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                        (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2017 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                    ) AS estado,
                    m.lote_id,m.lot_fechaPago,
                    CB.cbs_id,CB.cbs_nombre,
                    CV.ecf_clave,
                    LE.loe_clave,
                    UR.ur_nombre,
                    CX.clc_generacionCereCo,
                    CX.clc_cereCo,
                    n.afe_id, n.bdo_id,n.rfc_id,
                    m.bdm_referencia AS referencia,
                    m.bdm_fechaAutorizado
                FROM (
                    SELECT scp.gpo_id, scp.ur_id, scp.clc_id, scp.bur_id,scp.mpc_id,ecf_id,
                        clc_percepcion, clc_retencion, clc_concepto, clc_fechaElaboracion, clc_tipoCereCo, clc_beneficiario,
                         
                        ecl_id,
                        MP.mpc_nombre
                    FROM " . BD_CTRL_2017 . ".scp_Clc scp         
                    INNER JOIN " . BD_CTRL_2017 . ".cat_ModoPagoClc MP using(mpc_id)
                    GROUP BY gpo_id, ur_id, clc_id
                )C
                LEFT JOIN " . BD_CTRL_2017 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                INNER JOIN (
                    SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, rfc_id FROM " . BD_CTRL_2017 . ".scp_Presupuestal
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    " . $estado . "
                    AND ant.ejr_id_clc = :ejercicio
                    UNION ALL
                    SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, rfc_id FROM " . BD_CTRL_2017 . ".scp_Contable
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    " . $estado . "
                    AND ant.ejr_id_clc = :ejercicio
                )n USING(gpo_id, ur_id, clc_id)
                    LEFT JOIN (
                        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                            CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, 
                                bdm_referencia,bdm_fechaAutorizado
                        FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            LEFT JOIN " . BD_CTRLANT . ".min_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                            LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . $ejercicio . " )
                        WHERE bdm_estado = 'A'
                            AND LO.lot_ejercicio = :ejercicio
                    UNION ALL
                        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, lot_id AS lote_id, 
                            lot_fechaPago,'' AS fpa_id, msc_id, lot_id, '' AS lot_ejercicio, 
                            bdm_referencia
                        FROM " . BD_CTRL_2017 . ".tes_ClcLoteTesoreria
                            LEFT JOIN " . BD_CTRL_2017 . ".tes_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                        WHERE bdm_estado = 'A'
                    )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                LEFT JOIN " . BD_CTRL_2017 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                LEFT JOIN " . BD_CTRL_2017 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                INNER JOIN " . BD_CTRL_2017 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                LEFT JOIN " . BD_CTRL_2017 . ".enc_BancosSefinCveFin BN using (ecf_id)
                LEFT JOIN " . BD_CTRL_2017 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB on (BN.bsf_id = CB.cbs_id)
                LEFT JOIN " . BD_CTRLANT . ".scp_Clc ant on (ant.gpo_id=C.gpo_id and ant.ur_id=C.ur_id and ant.clc_id = C.clc_id)
                WHERE C.clc_id > 0 AND ant.ejr_id_clc = :ejercicio 
                " . $sqlExtra . " 
                " . $sqlCtrlAnterior . " 
                GROUP BY C.gpo_id, C.ur_id, C.clc_id";
            break;
        case 2018:
            $sql = "
                SELECT 
                    C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                    CASE
                        WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                        WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                        WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                    END AS importe,C.clc_concepto,
                    CX.clc_cereCo ,
                    CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                    C.clc_fechaElaboracion,
                    MP.mpc_nombre ,
                    if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                    C.clc_beneficiario,
                    CONCAT(C.ecl_id,'-',EC.ecl_nombre) as estado,
                    MAX(IF (m.fpa_id is not null, CONCAT( IF ( m.fpa_id = 1, 'T', IF (m.fpa_id = 2, 'C', 'L') ), LPAD(m.msc_id, 2, '0'), LPAD(m.lot_id, 4, '0'), SUBSTR(m.lot_ejercicio, 3) ),'')) AS lote_id,
                    MAX(CB.cbs_id) AS cbs_id,
                    CF.ecf_clave,
                    LE.loe_clave,
                    MAX(IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                    UR.ur_nombre,
                    MAX(m.lot_fechaPago) AS lot_fechaPago,
                    MAX(n.rfc_id) AS rfc_id,
                    YEAR(C.clc_fechaElaboracion) AS ejercicio,
                    CX.clc_generacionCereCo,
                    MAX(iba_abreviatura) AS iba_abreviatura,
                    m.bdm_referencia AS referencia,
                    m.bdm_fechaAutorizado
                FROM  " . BD_CTRL_2018 . ".scp_Clc C 
                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                INNER JOIN " . BD_CTRL_2018 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                INNER JOIN (
                    SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, rfc_id FROM " . BD_CTRL_2018 . ".scp_Presupuestal
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    " . $estado . "
                    AND ant.ejr_id_clc = :ejercicio
                    UNION ALL
                    SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, rfc_id FROM " . BD_CTRL_2018 . ".scp_Contable
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    " . $estado . "
                    AND ant.ejr_id_clc = :ejercicio
                )n USING(gpo_id, ur_id, clc_id)
                LEFT JOIN " . BD_CTRL_2018 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    LEFT JOIN (
                        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                            CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, bdm_referencia,
                                bdm_fechaAutorizado
                        FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            LEFT JOIN " . BD_CTRLANT . ".min_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                            LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . $ejercicio . " )
                        WHERE bdm_estado = 'A'
                            AND LO.lot_ejercicio = :ejercicio AND ejr_id_clc= :ejercicio
                    )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                INNER JOIN " . BD_CTRL_2018 . ".cat_ModoPagoClc MP using(mpc_id)
                LEFT JOIN " . BD_CTRL_2018 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                LEFT JOIN " . BD_CTRL_2018 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                INNER JOIN " . BD_CTRL_2018 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                INNER JOIN " . BD_CTRL_2018 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                WHERE C.clc_id > 0 AND ant.ejr_id_clc = :ejercicio               
                " . $sqlExtra . " 
                " . $sqlCtrlAnterior . "
                GROUP BY C.gpo_id, C.ur_id, C.clc_id, m.fpa_id, m.msc_id, m.lot_ejercicio, m.lot_id, m.bdm_referencia,n.rfc_id, CB.cbs_id, m.bdm_fechaAutorizado";
            break;
        case 2019:
            $ctrlAnterior = "SELECT 
                    C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                    CASE
                        WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                        WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                        WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                    END AS importe,C.clc_concepto,
                    CX.clc_cereCo ,
                    CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                    DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                    MP.mpc_nombre ,
                    if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                    C.clc_beneficiario,
                    IF(ant.ecl_id IS NULL,
                        (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2019 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                        (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2019 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                    ) AS estado,
                    MAX(IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'')) AS lote_id,
                    MAX(CB.cbs_id) AS cbs_id,
                    CF.ecf_clave,
                    '' AS loe_clave,
                    MAX(IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                    UR.ur_nombre,
                    MAX(DATE_FORMAT(LOT.lot_fechaPago,'%d-%m-%Y')) AS lot_fechaPago,
                    MAX(doc.rfc_id) AS rfc_id,
                    YEAR(C.clc_fechaElaboracion) AS ejercicio,
                    DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                    MAX(iba_abreviatura) AS iba_abreviatura,
                    m.bdm_referencia AS referencia,
                    m.bdm_fechaAutorizado
                    FROM " . BD_CTRL_2019 . ".scp_Clc C 
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2019 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                    INNER join (
                        select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from " . BD_CTRL_2019 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = :ejercicio
                                 union all 
                        select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from " . BD_CTRL_2019 . ".scp_Contable 
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = :ejercicio
                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2019 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    LEFT JOIN (
                        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                            CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, bdm_referencia, bdm_fechaAutorizado
                        FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            LEFT JOIN " . BD_CTRLANT . ".min_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                            LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . $ejercicio . " )
                        WHERE bdm_estado = 'A'
                            AND LO.lot_ejercicio = :ejercicio AND ejr_id_clc= :ejercicio
                    )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                    INNER JOIN " . BD_CTRL_2019 . ".cat_ModoPagoClc MP using(mpc_id)
                    INNER JOIN " . BD_CTRL_2019 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2019 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT on LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio and LOT.lot_ejr = " . $ejercicio . "
                    WHERE C.clc_id > 0 AND ant.ejr_id_clc = :ejercicio
                    " . $sqlExtra . "
                    " . $sqlCtrlAnterior . "
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id, m.bdm_referencia, m.bdm_fechaAutorizado";
            $sql = "                        
                " . $ctrlAnterior . "                   
            ";
            break;
        case 2020:
            $ctrlAnterior = "SELECT 
                    C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                    CASE
                        WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                        WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                        WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                    END AS importe,C.clc_concepto,
                    CX.clc_cereCo ,
                    CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                    DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                    MP.mpc_nombre ,
                    if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                    C.clc_beneficiario,
                    IF(ant.ecl_id IS NULL,
                        (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2020 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                        (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2020 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                    ) AS estado,
                    MAX(IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'')) AS lote_id,
                    MAX(CB.cbs_id) AS cbs_id,
                    CF.ecf_clave,
                    '' AS loe_clave,
                    MAX(IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                    UR.ur_nombre,
                    MAX(DATE_FORMAT(LOT.lot_fechaPago,'%d-%m-%Y')) AS lot_fechaPago,
                    MAX(doc.rfc_id) AS rfc_id,
                    YEAR(C.clc_fechaElaboracion) AS ejercicio,
                    DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                    MAX(iba_abreviatura) AS iba_abreviatura,
                    m.bdm_referencia AS referencia,
                    m.bdm_fechaAutorizado
                    FROM " . BD_CTRL_2020 . ".scp_Clc C 
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2020 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                    INNER join (
                        select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from " . BD_CTRL_2020 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = :ejercicio
                                 union all 
                        select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from " . BD_CTRL_2020 . ".scp_Contable 
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = :ejercicio
                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2020 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    LEFT JOIN (
                        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                            CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, bdm_referencia, bdm_fechaAutorizado
                        FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            LEFT JOIN " . BD_CTRLANT . ".min_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                            LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . APP_EJERCICIO . " )
                        WHERE bdm_estado = 'A'
                            AND LO.lot_ejercicio = :ejercicio AND ejr_id_clc= :ejercicio
                    )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                    INNER JOIN " . BD_CTRL_2020 . ".cat_ModoPagoClc MP using(mpc_id)
                    INNER JOIN " . BD_CTRL_2020 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2020 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT on LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio and LOT.lot_ejr = " . APP_EJERCICIO . "
                    WHERE C.clc_id > 0 AND ant.ejr_id_clc = :ejercicio
                    " . $sqlExtra . "
                    " . $sqlCtrlAnterior . "
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id, m.bdm_referencia, m.bdm_fechaAutorizado";
            $sql = "                        
                " . $ctrlAnterior . "                   
            ";
            break;
        case 2021:
            $ctrlAnterior = "SELECT 
                    C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                    CASE
                        WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                        WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                        WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                    END AS importe,C.clc_concepto,
                    CX.clc_cereCo ,
                    CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                    DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                    MP.mpc_nombre ,
                    if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                    C.clc_beneficiario,
                    IF(ant.ecl_id IS NULL,
                        (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2021 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                        (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2021 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                    ) AS estado,
                    MAX(IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'')) AS lote_id,
                    MAX(CB.cbs_id) AS cbs_id,
                    CF.ecf_clave,
                    '' AS loe_clave,
                    MAX(IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                    UR.ur_nombre,
                    MAX(DATE_FORMAT(LOT.lot_fechaPago,'%d-%m-%Y')) AS lot_fechaPago,
                    MAX(doc.rfc_id) AS rfc_id,
                    YEAR(C.clc_fechaElaboracion) AS ejercicio,
                    DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                    MAX(iba_abreviatura) AS iba_abreviatura,
                    m.bdm_referencia AS referencia,
                    m.bdm_fechaAutorizado
                    FROM " . BD_CTRL_2021 . ".scp_Clc C 
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2021 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                    INNER join (
                        select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from " . BD_CTRL_2021 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = :ejercicio
                                 union all 
                        select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from " . BD_CTRL_2021 . ".scp_Contable 
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = :ejercicio
                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2021 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    LEFT JOIN (
                        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                            CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, bdm_referencia, bdm_fechaAutorizado
                        FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            LEFT JOIN " . BD_CTRLANT . ".min_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                            LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . APP_EJERCICIO . " )
                        WHERE bdm_estado = 'A'
                            AND LO.lot_ejercicio = :ejercicio AND ejr_id_clc= :ejercicio
                    )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                    INNER JOIN " . BD_CTRL_2021 . ".cat_ModoPagoClc MP using(mpc_id)
                    INNER JOIN " . BD_CTRL_2021 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2021 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT on LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio and LOT.lot_ejr = " . APP_EJERCICIO . "
                    WHERE C.clc_id > 0 AND ant.ejr_id_clc = :ejercicio
                    " . $sqlExtra . "
                    " . $sqlCtrlAnterior . "
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id, m.bdm_referencia, m.bdm_fechaAutorizado";
            $sql = "                        
                " . $ctrlAnterior . "                   
            ";
            break;
        case 2022:
            $ctrlAnterior = "SELECT 
                    C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                    CASE
                        WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                        WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                        WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                    END AS importe,C.clc_concepto,
                    CX.clc_cereCo ,
                    CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                    DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                    MP.mpc_nombre ,
                    if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                    C.clc_beneficiario,
                    IF(ant.ecl_id IS NULL,
                        (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2022 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                        (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2022 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                    ) AS estado,
                    MAX(IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'')) AS lote_id,
                    MAX(CB.cbs_id) AS cbs_id,
                    CF.ecf_clave,
                    '' AS loe_clave,
                    MAX(IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                    UR.ur_nombre,
                    MAX(DATE_FORMAT(LOT.lot_fechaPago,'%d-%m-%Y')) AS lot_fechaPago,
                    MAX(doc.rfc_id) AS rfc_id,
                    YEAR(C.clc_fechaElaboracion) AS ejercicio,
                    DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                    MAX(iba_abreviatura) AS iba_abreviatura,
                    m.bdm_referencia AS referencia,
                    m.bdm_fechaAutorizado
                    FROM " . BD_CTRL_2022 . ".scp_Clc C 
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2022 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                    INNER join (
                        select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from " . BD_CTRL_2022 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = :ejercicio
                                 union all 
                        select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from " . BD_CTRL_2022 . ".scp_Contable 
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = :ejercicio
                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2022 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    LEFT JOIN (
                        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                            CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, bdm_referencia, bdm_fechaAutorizado
                        FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            LEFT JOIN " . BD_CTRLANT . ".min_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                            LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . APP_EJERCICIO . " )
                        WHERE bdm_estado = 'A'
                            AND LO.lot_ejercicio = :ejercicio AND ejr_id_clc= :ejercicio
                    )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                    INNER JOIN " . BD_CTRL_2022 . ".cat_ModoPagoClc MP using(mpc_id)
                    INNER JOIN " . BD_CTRL_2022 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2022 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT on LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio and LOT.lot_ejr = " . APP_EJERCICIO . "
                    WHERE C.clc_id > 0 AND ant.ejr_id_clc = :ejercicio
                    " . $sqlExtra . "
                    " . $sqlCtrlAnterior . "
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id, m.bdm_referencia, m.bdm_fechaAutorizado";
            $sql = "                        
                " . $ctrlAnterior . "                   
            ";
            break;
        case 2023:
            $ctrlAnterior = "SELECT 
                    C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                    CASE
                        WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                        WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                        WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                    END AS importe,C.clc_concepto,
                    CX.clc_cereCo ,
                    CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                    DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                    MP.mpc_nombre ,
                    if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                    C.clc_beneficiario,
                    IF(ant.ecl_id IS NULL,
                        (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2023 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                        (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2023 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                    ) AS estado,
                    MAX(IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'')) AS lote_id,
                    MAX(CB.cbs_id) AS cbs_id,
                    CF.ecf_clave,
                    '' AS loe_clave,
                    MAX(IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                    UR.ur_nombre,
                    MAX(DATE_FORMAT(LOT.lot_fechaPago,'%d-%m-%Y')) AS lot_fechaPago,
                    MAX(doc.rfc_id) AS rfc_id,
                    YEAR(C.clc_fechaElaboracion) AS ejercicio,
                    DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                    MAX(iba_abreviatura) AS iba_abreviatura,
                    m.bdm_referencia AS referencia,
                    m.bdm_fechaAutorizado
                    FROM " . BD_CTRL_2023 . ".scp_Clc C 
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2023 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                    INNER join (
                        select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from " . BD_CTRL_2023 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = :ejercicio
                                 union all 
                        select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from " . BD_CTRL_2023 . ".scp_Contable 
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = :ejercicio
                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    LEFT JOIN (
                        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                            CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, bdm_referencia, bdm_fechaAutorizado
                        FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            LEFT JOIN " . BD_CTRLANT . ".min_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                            LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . APP_EJERCICIO . " )
                        WHERE bdm_estado = 'A'
                            AND LO.lot_ejercicio = :ejercicio AND ejr_id_clc= :ejercicio
                    )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                    INNER JOIN " . BD_CTRL_2023 . ".cat_ModoPagoClc MP using(mpc_id)
                    INNER JOIN " . BD_CTRL_2023 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2023 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT on LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio and LOT.lot_ejr = " . APP_EJERCICIO . "
                    WHERE C.clc_id > 0 AND ant.ejr_id_clc = :ejercicio
                    " . $sqlExtra . "
                    " . $sqlCtrlAnterior . "
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id, m.bdm_referencia, m.bdm_fechaAutorizado";
            $sql = "                        
                " . $ctrlAnterior . "                   
            ";
            break;
        case 2024:
            $ctrlAnterior = "SELECT 
                    C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                    CASE
                        WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                        WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                        WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                    END AS importe,C.clc_concepto,
                    CX.clc_cereCo ,
                    CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                    DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                    MP.mpc_nombre ,
                    if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                    C.clc_beneficiario,
                    IF(ant.ecl_id IS NULL,
                        (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2024 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                        (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2024 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                    ) AS estado,
                    MAX(IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'')) AS lote_id,
                    MAX(CB.cbs_id) AS cbs_id,
                    CF.ecf_clave,
                    '' AS loe_clave,
                    MAX(IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                    UR.ur_nombre,
                    MAX(DATE_FORMAT(LOT.lot_fechaPago,'%d-%m-%Y')) AS lot_fechaPago,
                    MAX(doc.rfc_id) AS rfc_id,
                    YEAR(C.clc_fechaElaboracion) AS ejercicio,
                    DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                    MAX(iba_abreviatura) AS iba_abreviatura,
                    m.bdm_referencia AS referencia,
                    m.bdm_fechaAutorizado
                    FROM " . BD_CTRL_2024 . ".scp_Clc C 
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2024 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                    INNER join (
                        select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from " . BD_CTRL_2024 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = :ejercicio
                                 union all 
                        select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from " . BD_CTRL_2024 . ".scp_Contable 
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = :ejercicio
                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2024 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    LEFT JOIN (
                        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                            CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, bdm_referencia, bdm_fechaAutorizado
                        FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            LEFT JOIN " . BD_CTRLANT . ".min_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                            LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . APP_EJERCICIO . " )
                        WHERE bdm_estado = 'A'
                            AND LO.lot_ejercicio = :ejercicio AND ejr_id_clc= :ejercicio
                    )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                    INNER JOIN " . BD_CTRL_2024 . ".cat_ModoPagoClc MP using(mpc_id)
                    INNER JOIN " . BD_CTRL_2024 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2024 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT on LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio and LOT.lot_ejr = " . APP_EJERCICIO . "
                    WHERE C.clc_id > 0 AND ant.ejr_id_clc = :ejercicio
                    " . $sqlExtra . "
                    " . $sqlCtrlAnterior . "
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id, m.bdm_referencia, m.bdm_fechaAutorizado";
            $sql = "                        
                " . $ctrlAnterior . "                   
            ";
            break;
        case 2025:
            $ctrlAnterior = "SELECT 
                    C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                    CASE
                        WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                        WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                        WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                    END AS importe,C.clc_concepto,
                    CX.clc_cereCo ,
                    CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                    DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                    MP.mpc_nombre ,
                    if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                    C.clc_beneficiario,
                    IF(ant.ecl_id IS NULL,
                        (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2025 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                        (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2025 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                    ) AS estado,
                    MAX(IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'')) AS lote_id,
                    MAX(CB.cbs_id) AS cbs_id,
                    CF.ecf_clave,
                    '' AS loe_clave,
                    MAX(IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                    UR.ur_nombre,
                    MAX(DATE_FORMAT(LOT.lot_fechaPago,'%d-%m-%Y')) AS lot_fechaPago,
                    MAX(doc.rfc_id) AS rfc_id,
                    YEAR(C.clc_fechaElaboracion) AS ejercicio,
                    DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                    MAX(iba_abreviatura) AS iba_abreviatura,
                    m.bdm_referencia AS referencia,
                    m.bdm_fechaAutorizado
                    FROM " . BD_CTRL_2025 . ".scp_Clc C 
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2025 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                    INNER join (
                        select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from " . BD_CTRL_2025 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = :ejercicio
                                 union all 
                        select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from " . BD_CTRL_2025 . ".scp_Contable 
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = :ejercicio
                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2025 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    LEFT JOIN (
                        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                            CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, bdm_referencia, bdm_fechaAutorizado
                        FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            LEFT JOIN " . BD_CTRLANT . ".min_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                            LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . APP_EJERCICIO . " )
                        WHERE bdm_estado = 'A'
                            AND LO.lot_ejercicio = :ejercicio AND ejr_id_clc= :ejercicio
                    )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                    INNER JOIN " . BD_CTRL_2025 . ".cat_ModoPagoClc MP using(mpc_id)
                    INNER JOIN " . BD_CTRL_2025 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2025 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT on LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio and LOT.lot_ejr = " . APP_EJERCICIO . "
                    WHERE C.clc_id > 0 AND ant.ejr_id_clc = :ejercicio
                    " . $sqlExtra . "
                    " . $sqlCtrlAnterior . "
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id, m.bdm_referencia, m.bdm_fechaAutorizado";
            $sql = "                        
                " . $ctrlAnterior . "                   
            ";
            break;
        case 2026:
        default:
            $sql = "SELECT 
                    C.gpo_id,C.ur_id,C.clc_id,C.bur_id,                    
                    CASE
                        WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                        WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                        WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                    END AS importe,C.clc_concepto,
                    CX.clc_cereCo ,
                    CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                    DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                    MP.mpc_nombre ,
                    if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                    C.clc_beneficiario,                       
                    CONCAT(EC.ecl_id,'-',EC.ecl_nombre) as estado,
                    MAX(IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'')) AS lote_id,
                    MAX(CB.cbs_id) AS cbs_id,
                    CF.ecf_clave,
                    '' AS loe_clave,
                    MAX(IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                    UR.ur_nombre,
                    MAX(LOT.lot_fechaPago) AS lot_fechaPago,
                    MAX(doc.rfc_id) AS rfc_id,
                    YEAR(C.clc_fechaElaboracion) AS ejercicio,
                    DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                    MAX(iba_abreviatura) AS iba_abreviatura,
                    m.bdm_referencia AS referencia,
                    m.bdm_fechaAutorizado
                    FROM " . $BD_CTRL . ".scp_Clc C 
                    INNER JOIN " . $BD_CTRL . ".cat_EstadoClc EC on C.ecl_id = EC.ecl_id
                    LEFT join (
                        select gpo_id,ur_id,clc_id,afe_id, bdo_id, rfc_id from " . $BD_CTRL . ".scp_Presupuestal
                        INNER JOIN " . $BD_CTRL . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                                 union all 
                        select gpo_id,ur_id,clc_id,afe_id, bdo_id, rfc_id from " . $BD_CTRL . ".scp_Contable 
                        INNER JOIN " . $BD_CTRL . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . $BD_CTRL . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    LEFT JOIN (
                        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                            CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, bdm_referencia,bdm_fechaAutorizado
                        FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            LEFT JOIN " . BD_TESORERIA . ".min_BancoDocumentoMinistrado_" . $ejercicio . " USING(gpo_id, ur_id, clc_id)
                            LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . APP_EJERCICIO . ")
                        WHERE bdm_estado = 'A' AND LO.lot_ejercicio = :ejercicio) m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                    INNER JOIN " . $BD_CTRL . ".cat_ModoPagoClc MP using(mpc_id)
                    INNER JOIN " . $BD_CTRL . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    left join " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . $BD_CTRL . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    left join " . BD_TESORERIA . ".lot_Lote LOT on LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio and LOT.lot_ejr = " . APP_EJERCICIO . "
                    WHERE C.clc_id > 0 
                    " . $sqlExtra . "
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id, m.bdm_referencia, m.bdm_fechaAutorizado";
            break;
    }
    $con = new AccesoDatos();
    $con->conectar();
    $con->setSql($sql);
    $con->consultar($parametro);
    $con->desconectar();

    $dato = array();
    $contador = 1;

    while ($row = $con->extraer()) {
        $dato[] = array(
            'contador' => $contador,
            'mpc_nombre' => $row['mpc_nombre'],
            'ejercicio' => $row['ejercicio'],
            'clc_fechaElaboracion' => $row['clc_fechaElaboracion'],
            'lot_fechaPago' => ($row['lot_fechaPago'] != NULL) ? date('d/m/Y', strtotime($row['lot_fechaPago'])) : '',
            'referencia' => $row['referencia'],
            'bdm_fechaAutorizado' => $row['bdm_fechaAutorizado'],
            'clc_generacionCereCo' => $row['clc_generacionCereCo'],
            'loe_clave' => $row['loe_clave'],
            'ur' => $row['ur'],
            'clc_id' => $row['clc_id'],
            'clc_cereCo' => $row['clc_cereCo'],
            'clc_beneficiario' => $row['clc_beneficiario'],
            'clc_concepto' => $row['clc_concepto'],
            'importe' => $row['importe'],
            'iba_abreviatura' => $row['iba_abreviatura'],
            'rfc_id' => $row['rfc_id'],
            'cbs_id' => "'" . $row['cbs_id'],
            'cbs_nombre' => $row['cbs_nombre'],
            'bur_id' => "'" . $row['bur_id'],
            'ecf_clave' => $row['ecf_clave'],
            'lote_id' => $row['lote_id'],
            'ur_nombre' => $row['ur_nombre'],
            'estado' => $row['estado'],
            'clc_tipoCereCo' => $row['clc_tipoCereCo'],
        );
        $contador++;
    }
    return $dato;
}
