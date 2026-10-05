<?php
/**
 * Description of function nivelFolio
 *
 * @author ?
 * Collaborators: Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 11-Dic-2019
 * Comment: -----
 * Objective: obtener los datos para los estados de clcs
 * Regla de Negocio: Obtener las clcs de acuerdo al filtro de la pantalla  a partir del ejercicio 2017 segun sea el caso
 *
 * */

function consultaEstadoClc($ejercicio, $sqlExtra = '', $estado = '', $sqlCtrlAnterior = '', $estado2018 = '', $estadoCtrl2018 = '')
{
    //separamos la consulta en propuestas, lotes
    switch ($ejercicio) {
        case 2017:
            $sql = "
                    SELECT
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        (C.clc_percepcion - C.clc_retencion) AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,C.ur_id) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%Y/%m/%d')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        IF(ant.ecl_id IS NULL,
                            (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2017 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                            (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2017 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                        ) AS estado,
                        IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),IF (LT.lot_id is not null,LT.lot_id,'')) AS lote_id,
                        CB.cbs_id,
                        CF.ecf_clave,
                        LE.loe_clave,
                        CB.cbs_nombre,
                        (IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                        UR.ur_nombre,
                        DATE_FORMAT(LOT.lot_fechaPago,'%Y/%m/%d')lot_fechaPago,
                        doc.rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%Y/%m/%d')clc_generacionCereCo,
                        tipo.tcl_id,tcl_nombre,subtipo.stc_id,stc_nombre
                    FROM " . BD_CTRL_2017 . ".scp_Clc C
                    LEFT JOIN " . BD_CTRL_2017 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    LEFT JOIN (
                        SELECT
                            gpo_id,ur_id,clc_id,rfc_id
                        FROM " . BD_CTRL_2017 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = 2017
                                union all
                        select gpo_id,ur_id,clc_id,rfc_id from " . BD_CTRL_2017 . ".scp_Contable
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = 2017
                    )as doc using (gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2017 . ".cat_ModoPagoClc MP using(mpc_id)
                    LEFT JOIN " . BD_CTRL_2017 . ".tes_ClcLoteTesoreria as LT using(gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2017 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2017 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                    INNER JOIN " . BD_CTRL_2017 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_CTRL_2017 . ".enc_BancosSefinCveFin BN using (ecf_id)
                    LEFT JOIN " . BD_CTRL_2017 . ".enc_ClaveFinanciamiento CF using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB on (BN.bsf_id = CB.cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO on (LO.gpo_id=C.gpo_id and LO.ur_id= C.ur_id and LO.clc_id=C.clc_id and LO.lot_ejercicio=2017)
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT on (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = 2023 )
                    LEFT JOIN " . BD_CTRLANT . ".scp_Clc ant on (ant.gpo_id=C.gpo_id and ant.ur_id=C.ur_id and ant.clc_id = C.clc_id)
                    INNER JOIN " . BD_CTRL_2017 . ".cat_TipoClc tipo using(tcl_id)
                    INNER JOIN " . BD_CTRL_2017 . ".cat_SubtipoClc subtipo using(stc_id)
                    WHERE C.clc_percepcion - C.clc_retencion > 0
                        AND C.clc_id > 0
                        AND ant.ejr_id_clc = 2017
                        " . $sqlExtra . "
                        " . $sqlCtrlAnterior . "
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id";
            break;
        case 2018:
            $ctrlAnterior = "
                    SELECT
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        (C.clc_percepcion - C.clc_retencion) AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%Y/%m/%d')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        IF(ant.ecl_id IS NULL,
                            (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2018 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                            (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2018 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                        ) AS estado,
                        MAX(IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'')) AS lote_id,
                        MAX(CB.cbs_id) AS cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        MAX(IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                        UR.ur_nombre,
                        MAX(DATE_FORMAT(LOT.lot_fechaPago,'%Y/%m/%d')) AS lot_fechaPago,
                        MAX(doc.rfc_id) AS rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%Y/%m/%d')clc_generacionCereCo,
                        MAX(iba_abreviatura) AS iba_abreviatura,
                        tipo.tcl_id,tcl_nombre,subtipo.stc_id,stc_nombre
                    FROM " . BD_CTRL_2018 . ".scp_Clc C
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2018 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                    INNER JOIN (
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2018 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = 2018
                        union all
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2018 . ".scp_Contable
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = 2018
                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2018 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2018 . ".cat_ModoPagoClc MP using(mpc_id)
                    #LEFT JOIN " . BD_CTRL_2018 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                    #LEFT JOIN " . BD_CTRL_2018 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                    INNER JOIN " . BD_CTRL_2018 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2018 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT on LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id
                        and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio
                        and LOT.lot_ejr in(2019, 2020, 2021, 2022,2023)
                    INNER JOIN " . BD_CTRL_2018 . ".cat_TipoClc tipo using(tcl_id)
                    INNER JOIN " . BD_CTRL_2018 . ".cat_SubtipoClc subtipo using(stc_id)
                    WHERE C.clc_percepcion - C.clc_retencion > 0
                        AND C.clc_id > 0
                        AND ant.ejr_id_clc = 2018
                        " . $sqlExtra . "
                        " . $sqlCtrlAnterior . "
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id";

            $sqlCtrl2018 = "
                    SELECT
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        (C.clc_percepcion - C.clc_retencion) AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%Y/%m/%d')clc_fechaElaboracion,
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
                        DATE_FORMAT(MAX(LOT.lot_fechaPago),'%Y/%m/%d') AS lot_fechaPago,
                        MAX(doc.rfc_id) AS rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%Y/%m/%d')clc_generacionCereCo,
                        MAX(iba_abreviatura) AS iba_abreviatura,
                        tipo.tcl_id,tcl_nombre,subtipo.stc_id,stc_nombre
                    FROM " . BD_CTRL_2018 . ".scp_Clc C
                    INNER JOIN " . BD_CTRL_2018 . ".cat_EstadoClc EC ON C.ecl_id = EC.ecl_id
                    INNER JOIN (
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2018 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRL_2018 . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                        " . $estado2018 . "
                        union all
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2018 . ".scp_Contable
                        INNER JOIN " . BD_CTRL_2018 . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                        " . $estado2018 . "

                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2018 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2018 . ".cat_ModoPagoClc MP using(mpc_id)
                    #LEFT JOIN " . BD_CTRL_2018 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                    #LEFT JOIN " . BD_CTRL_2018 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                    INNER JOIN " . BD_CTRL_2018 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2018 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON LO.fpa_id=LOT.fpa_id
                        and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id
                        and LO.lot_ejercicio=LOT.lot_ejercicio
                        and LOT.lot_ejr = YEAR(C.clc_fechaElaboracion)
                    INNER JOIN " . BD_CTRL_2018 . ".cat_TipoClc tipo using(tcl_id)
                    INNER JOIN " . BD_CTRL_2018 . ".cat_SubtipoClc subtipo using(stc_id)
                    WHERE C.clc_percepcion - C.clc_retencion > 0
                        AND C.clc_id > 0
                        " . $estadoCtrl2018 . "
                        " . $sqlExtra . "
                        GROUP BY C.gpo_id, C.ur_id, C.clc_id";

            $sql = "
                    " . $ctrlAnterior . "
                    UNION ALL
                    " . $sqlCtrl2018 . "
                ";
            break;
        case 2019:
            $ctrlAnterior = "
                    SELECT
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        (C.clc_percepcion - C.clc_retencion) AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%Y/%m/%d')clc_fechaElaboracion,
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
                        MAX(DATE_FORMAT(LOT.lot_fechaPago,'%Y/%m/%d')) AS lot_fechaPago,
                        MAX(doc.rfc_id) AS rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%Y/%m/%d')clc_generacionCereCo,
                        MAX(iba_abreviatura) AS iba_abreviatura,
                        tipo.tcl_id,tcl_nombre,subtipo.stc_id,stc_nombre
                    FROM " . BD_CTRL_2019 . ".scp_Clc C
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2019 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                    INNER JOIN (
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2019 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = 2019
                        union all
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2019 . ".scp_Contable
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = 2019
                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2019 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2019 . ".cat_ModoPagoClc MP using(mpc_id)
                    #LEFT JOIN " . BD_CTRL_2019 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                    #LEFT JOIN " . BD_CTRL_2019 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                    INNER JOIN " . BD_CTRL_2019 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2019 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT on LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id
                        and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio
                        and LOT.lot_ejr in (2020, 2021, 2022, 2023)
                    INNER JOIN " . BD_CTRL_2019 . ".cat_TipoClc tipo using(tcl_id)
                    INNER JOIN " . BD_CTRL_2019 . ".cat_SubtipoClc subtipo using(stc_id)
                    WHERE C.clc_percepcion - C.clc_retencion > 0
                        AND C.clc_id > 0
                        AND ant.ejr_id_clc = 2019
                        " . $sqlExtra . "
                        " . $sqlCtrlAnterior . "
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id";

            $sqlCtrl2018 = "
                    SELECT
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        (C.clc_percepcion - C.clc_retencion) AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%Y/%m/%d')clc_fechaElaboracion,
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
                        DATE_FORMAT(MAX(LOT.lot_fechaPago),'%Y/%m/%d') AS lot_fechaPago,
                        MAX(doc.rfc_id) AS rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%Y/%m/%d')clc_generacionCereCo,
                        MAX(iba_abreviatura) AS iba_abreviatura,
                        tipo.tcl_id,tcl_nombre,subtipo.stc_id,stc_nombre
                    FROM " . BD_CTRL_2019 . ".scp_Clc C
                    INNER JOIN " . BD_CTRL_2019 . ".cat_EstadoClc EC ON C.ecl_id = EC.ecl_id
                    INNER JOIN (
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2019 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRL_2019 . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                        " . $estado2018 . "
                        union all
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2019 . ".scp_Contable
                        INNER JOIN " . BD_CTRL_2019 . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                        " . $estado2018 . "

                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2019 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2019 . ".cat_ModoPagoClc MP using(mpc_id)
                    #LEFT JOIN " . BD_CTRL_2019 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                    #LEFT JOIN " . BD_CTRL_2019 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                    INNER JOIN " . BD_CTRL_2019 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2019 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id
                        and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio
                        and LOT.lot_ejr = YEAR(C.clc_fechaElaboracion)
                    INNER JOIN " . BD_CTRL_2019 . ".cat_TipoClc tipo using(tcl_id)
                    INNER JOIN " . BD_CTRL_2019 . ".cat_SubtipoClc subtipo using(stc_id)
                    WHERE C.clc_percepcion - C.clc_retencion > 0
                        AND C.clc_id > 0
                        " . $estadoCtrl2018 . "
                        " . $sqlExtra . "
                        GROUP BY C.gpo_id, C.ur_id, C.clc_id";
            $sql = "
                        " . $ctrlAnterior . "
                        UNION ALL
                        " . $sqlCtrl2018 . "
                    ";
            break;
        case 2020:
            $ctrlAnterior = "
                    SELECT
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        (C.clc_percepcion - C.clc_retencion) AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%Y/%m/%d')clc_fechaElaboracion,
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
                        MAX(DATE_FORMAT(LOT.lot_fechaPago,'%Y/%m/%d')) AS lot_fechaPago,
                        MAX(doc.rfc_id) AS rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%Y/%m/%d')clc_generacionCereCo,
                        MAX(iba_abreviatura) AS iba_abreviatura,
                        tipo.tcl_id,tcl_nombre,subtipo.stc_id,stc_nombre
                    FROM " . BD_CTRL_2020 . ".scp_Clc C
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2020 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                    INNER JOIN (
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2020 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = 2020
                        union all
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2020 . ".scp_Contable
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = 2020
                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2020 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2020 . ".cat_ModoPagoClc MP using(mpc_id)
                    #LEFT JOIN " . BD_CTRL_2020 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                    #LEFT JOIN " . BD_CTRL_2020 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                    INNER JOIN " . BD_CTRL_2020 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2020 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT on LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id
                        and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio
                        and LOT.lot_ejr in(2021, 2022, 2023)
                    INNER JOIN " . BD_CTRL_2020 . ".cat_TipoClc tipo using(tcl_id)
                    INNER JOIN " . BD_CTRL_2020 . ".cat_SubtipoClc subtipo using(stc_id)
                    WHERE C.clc_percepcion - C.clc_retencion > 0
                        AND C.clc_id > 0
                        AND ant.ejr_id_clc = 2020
                        " . $sqlExtra . "
                        " . $sqlCtrlAnterior . "
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id";

            $sqlCtrl2018 = "
                    SELECT
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        (C.clc_percepcion - C.clc_retencion) AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%Y/%m/%d')clc_fechaElaboracion,
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
                        DATE_FORMAT(MAX(LOT.lot_fechaPago),'%Y/%m/%d') AS lot_fechaPago,
                        MAX(doc.rfc_id) AS rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%Y/%m/%d')clc_generacionCereCo,
                        MAX(iba_abreviatura) AS iba_abreviatura,
                        tipo.tcl_id,tcl_nombre,subtipo.stc_id,stc_nombre
                    FROM " . BD_CTRL_2020 . ".scp_Clc C
                    INNER JOIN " . BD_CTRL_2020 . ".cat_EstadoClc EC ON C.ecl_id = EC.ecl_id
                    INNER JOIN (
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2020 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRL_2020 . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                        " . $estado2018 . "
                        union all
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2020 . ".scp_Contable
                        INNER JOIN " . BD_CTRL_2020 . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                        " . $estado2018 . "

                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2020 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2020 . ".cat_ModoPagoClc MP using(mpc_id)
                    #LEFT JOIN " . BD_CTRL_2020 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                    #LEFT JOIN " . BD_CTRL_2020 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                    INNER JOIN " . BD_CTRL_2020 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2020 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON LO.fpa_id=LOT.fpa_id
                        and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id
                        and LO.lot_ejercicio=LOT.lot_ejercicio
                        and LOT.lot_ejr = YEAR(C.clc_fechaElaboracion)
                    INNER JOIN " . BD_CTRL_2020 . ".cat_TipoClc tipo using(tcl_id)
                    INNER JOIN " . BD_CTRL_2020 . ".cat_SubtipoClc subtipo using(stc_id)
                    WHERE C.clc_percepcion - C.clc_retencion > 0
                        AND C.clc_id > 0
                        " . $estadoCtrl2018 . "
                        " . $sqlExtra . "
                        GROUP BY C.gpo_id, C.ur_id, C.clc_id";
            $sql = "
                            " . $ctrlAnterior . "
                            UNION ALL
                            " . $sqlCtrl2018 . "
                    ";
            break;
        case 2021:
            $ctrlAnterior = "
                    SELECT
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        (C.clc_percepcion - C.clc_retencion) AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%Y/%m/%d')clc_fechaElaboracion,
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
                        MAX(DATE_FORMAT(LOT.lot_fechaPago,'%Y/%m/%d')) AS lot_fechaPago,
                        MAX(doc.rfc_id) AS rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%Y/%m/%d')clc_generacionCereCo,
                        MAX(iba_abreviatura) AS iba_abreviatura,
                        tipo.tcl_id,tcl_nombre,subtipo.stc_id,stc_nombre,
                        cap.capitulo_gasto
                    FROM " . BD_CTRL_2021 . ".scp_Clc C
                    INNER JOIN ctrlAnterior.scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2021 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                    INNER JOIN (
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2021 . ".scp_Presupuestal
                        INNER JOIN ctrlAnterior.scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = 2021
                        union all
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2021 . ".scp_Contable
                        INNER JOIN ctrlAnterior.scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = 2021
                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2021 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2021 . ".cat_ModoPagoClc MP using(mpc_id)
                    #LEFT JOIN " . BD_CTRL_2021 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                    #LEFT JOIN " . BD_CTRL_2021 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                    INNER JOIN " . BD_CTRL_2021 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2021 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT on LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id
                        and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio
                        and LOT.lot_ejr = 2023
                    INNER JOIN " . BD_CTRL_2021 . ".cat_TipoClc tipo using(tcl_id)
                    INNER JOIN " . BD_CTRL_2021 . ".cat_SubtipoClc subtipo using(stc_id)
                      LEFT JOIN  ( 	SELECT gpo_id, ur_id, clc_id, GROUP_CONCAT(DISTINCT cat.cap_id SEPARATOR ', ') AS capitulo_gasto
                        FROM " . BD_CTRL_2021 . ".scp_Presupuestal AS sp
                        JOIN " . BD_CTRL_2021 . ".presupuesto AS p USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        JOIN " . BD_CTRL_2021 . ".cat_PartidaGenerica AS cpg USING(pg_id)
                        JOIN " . BD_CTRL_2021 . ".cat_Capitulo AS cat USING(cap_id)   
                        GROUP BY gpo_id, ur_id, clc_id
                    ) as cap on C.gpo_id = cap.gpo_id  and  C.ur_id = cap.ur_id and C.clc_id = cap.clc_id
                    WHERE C.clc_percepcion - C.clc_retencion > 0
                        AND C.clc_id > 0
                        AND ant.ejr_id_clc = 2021
                        " . $sqlExtra . "
                        " . $sqlCtrlAnterior . "
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id";

            $sqlCtrl2018 = "
                    SELECT
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        (C.clc_percepcion - C.clc_retencion) AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%Y/%m/%d')clc_fechaElaboracion,
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
                        DATE_FORMAT(MAX(LOT.lot_fechaPago),'%Y/%m/%d') AS lot_fechaPago,
                        MAX(doc.rfc_id) AS rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%Y/%m/%d')clc_generacionCereCo,
                        MAX(iba_abreviatura) AS iba_abreviatura,
                        tipo.tcl_id,tcl_nombre,subtipo.stc_id,stc_nombre,
                        cap.capitulo_gasto
                    FROM " . BD_CTRL_2021 . ".scp_Clc C
                    INNER JOIN " . BD_CTRL_2021 . ".cat_EstadoClc EC ON C.ecl_id = EC.ecl_id
                    INNER JOIN (
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2021 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRL_2021 . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                        " . $estado2018 . "
                        union all
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2021 . ".scp_Contable
                        INNER JOIN " . BD_CTRL_2021 . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                        " . $estado2018 . "

                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2021 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2021 . ".cat_ModoPagoClc MP using(mpc_id)
                    #LEFT JOIN " . BD_CTRL_2021 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                    #LEFT JOIN " . BD_CTRL_2021 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                    INNER JOIN " . BD_CTRL_2021 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2021 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id
                        AND C.clc_id=LO.clc_id
                        AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT
                        ON LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id
                        and LO.lot_ejercicio=LOT.lot_ejercicio
                        and LOT.lot_ejr = YEAR(C.clc_fechaElaboracion)
                    INNER JOIN " . BD_CTRL_2021 . ".cat_TipoClc tipo using(tcl_id)
                    INNER JOIN " . BD_CTRL_2021 . ".cat_SubtipoClc subtipo using(stc_id)
                      LEFT JOIN  ( 	SELECT gpo_id, ur_id, clc_id, GROUP_CONCAT(DISTINCT cat.cap_id SEPARATOR ', ') AS capitulo_gasto
                        FROM " . BD_CTRL_2021 . ".scp_Presupuestal AS sp
                        JOIN " . BD_CTRL_2021 . ".presupuesto AS p USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        JOIN " . BD_CTRL_2021 . ".cat_PartidaGenerica AS cpg USING(pg_id)
                        JOIN " . BD_CTRL_2021 . ".cat_Capitulo AS cat USING(cap_id)   
                        GROUP BY gpo_id, ur_id, clc_id
                    ) as cap on C.gpo_id = cap.gpo_id  and  C.ur_id = cap.ur_id and C.clc_id = cap.clc_id
                    WHERE C.clc_percepcion - C.clc_retencion > 0
                        AND C.clc_id > 0
                        " . $estadoCtrl2018 . "
                        " . $sqlExtra . "
                        GROUP BY C.gpo_id, C.ur_id, C.clc_id";

            $sql = "
                    " . $ctrlAnterior . "
                    UNION ALL
                    " . $sqlCtrl2018 . "
                ";
            break;
        case 2022:
            $ctrlAnterior = "
                    SELECT
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        (C.clc_percepcion - C.clc_retencion) AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%Y/%m/%d')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        IF(ant.ecl_id IS NULL,
                            (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2022 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                            (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2022 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                        ) AS estado,
                        IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'') AS lote_id,
                        CB.cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre) AS cbs_nombre,
                        UR.ur_nombre,
                        DATE_FORMAT(LOT.lot_fechaPago,'%Y/%m/%d') AS lot_fechaPago,
                        doc.rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%Y/%m/%d')clc_generacionCereCo,
                        iba_abreviatura,
                        tipo.tcl_id,tcl_nombre,subtipo.stc_id,stc_nombre,
                        cap.capitulo_gasto
                    FROM " . BD_CTRL_2022 . ".scp_Clc C
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2022 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                    INNER JOIN (
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2022 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = 2022
                        union all
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2022 . ".scp_Contable
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = 2022
                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2022 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2022 . ".cat_ModoPagoClc MP using(mpc_id)
                    #LEFT JOIN " . BD_CTRL_2022 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                    #LEFT JOIN " . BD_CTRL_2022 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                    INNER JOIN " . BD_CTRL_2022 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2022 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT on LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id
                        and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio
                        and LOT.lot_ejr = 2023
                    INNER JOIN " . BD_CTRL_2022 . ".cat_TipoClc tipo using(tcl_id)
                    INNER JOIN " . BD_CTRL_2022 . ".cat_SubtipoClc subtipo using(stc_id)
                    LEFT JOIN  ( 	SELECT gpo_id, ur_id, clc_id, GROUP_CONCAT(DISTINCT cat.cap_id SEPARATOR ', ') AS capitulo_gasto
                    FROM " . BD_CTRL_2022 . ".scp_Presupuestal AS sp
                    JOIN " . BD_CTRL_2022 . ".presupuesto AS p USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                    JOIN " . BD_CTRL_2022 . ".cat_PartidaGenerica AS cpg USING(pg_id)
                    JOIN " . BD_CTRL_2022 . ".cat_Capitulo AS cat USING(cap_id)   
                    GROUP BY gpo_id, ur_id, clc_id
                              ) as cap on C.gpo_id = cap.gpo_id  and  C.ur_id = cap.ur_id and C.clc_id = cap.clc_id
                    
                    WHERE TRUNCATE((C.clc_percepcion - C.clc_retencion),2) > 0
                        AND C.clc_id > 0
                        AND ant.ejr_id_clc = 2022
                        " . $sqlExtra . "
                        " . $sqlCtrlAnterior . "
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id";

            $sqlCtrl2018 = "
                    SELECT
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        (C.clc_percepcion - C.clc_retencion) AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%Y/%m/%d')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        CONCAT(EC.ecl_id,'-',EC.ecl_nombre) as estado,
                        IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'') AS lote_id,
                        CB.cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre) AS cbs_nombre,
                        UR.ur_nombre,
                        DATE_FORMAT(LOT.lot_fechaPago,'%Y/%m/%d') AS lot_fechaPago,
                        doc.rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%Y/%m/%d')clc_generacionCereCo,
                        iba_abreviatura,
                        tipo.tcl_id,tcl_nombre,subtipo.stc_id,stc_nombre,
                        cap.capitulo_gasto
                    FROM " . BD_CTRL_2022 . ".scp_Clc C
                    INNER JOIN " . BD_CTRL_2022 . ".cat_EstadoClc EC ON C.ecl_id = EC.ecl_id
                    INNER JOIN (
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2022 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRL_2022 . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                        " . $estado2018 . "
                        union all
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2022 . ".scp_Contable
                        INNER JOIN " . BD_CTRL_2022 . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                        " . $estado2018 . "

                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2022 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2022 . ".cat_ModoPagoClc MP using(mpc_id)
                    #LEFT JOIN " . BD_CTRL_2022 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                    #LEFT JOIN " . BD_CTRL_2022 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                    INNER JOIN " . BD_CTRL_2022 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2022 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id
                        AND C.clc_id=LO.clc_id
                        AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT
                        ON LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id
                        and LO.lot_ejercicio=LOT.lot_ejercicio
                        and LOT.lot_ejr = YEAR(C.clc_fechaElaboracion)
                    INNER JOIN " . BD_CTRL_2022 . ".cat_TipoClc tipo using(tcl_id)
                    INNER JOIN " . BD_CTRL_2022 . ".cat_SubtipoClc subtipo using(stc_id)
                    LEFT JOIN  ( 	SELECT gpo_id, ur_id, clc_id, GROUP_CONCAT(DISTINCT cat.cap_id SEPARATOR ', ') AS capitulo_gasto
                        FROM " . BD_CTRL_2022 . ".scp_Presupuestal AS sp
                        JOIN " . BD_CTRL_2022 . ".presupuesto AS p USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        JOIN " . BD_CTRL_2022 . ".cat_PartidaGenerica AS cpg USING(pg_id)
                        JOIN " . BD_CTRL_2022 . ".cat_Capitulo AS cat USING(cap_id)   
                        GROUP BY gpo_id, ur_id, clc_id
                    ) as cap on C.gpo_id = cap.gpo_id  and  C.ur_id = cap.ur_id and C.clc_id = cap.clc_id
                    WHERE TRUNCATE((C.clc_percepcion - C.clc_retencion),2) > 0
                        AND C.clc_id > 0
                        " . $estadoCtrl2018 . "
                        " . $sqlExtra . "
                        GROUP BY C.gpo_id, C.ur_id, C.clc_id";

            $sql = "
                    " . $ctrlAnterior . "
                    UNION ALL
                    " . $sqlCtrl2018 . "
                ";
            break;
        case 2023:
            $ctrlAnterior = "
                    SELECT
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        (C.clc_percepcion - C.clc_retencion) AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%Y/%m/%d')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        IF(ant.ecl_id IS NULL,
                            (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2023 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                            (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2023 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                        ) AS estado,
                        IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'') AS lote_id,
                        CB.cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre) AS cbs_nombre,
                        UR.ur_nombre,
                        DATE_FORMAT(LOT.lot_fechaPago,'%Y/%m/%d') AS lot_fechaPago,
                        doc.rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%Y/%m/%d')clc_generacionCereCo,
                        iba_abreviatura,
                        tipo.tcl_id,tcl_nombre,subtipo.stc_id,stc_nombre,
                        cap.capitulo_gasto
                    FROM " . BD_CTRL_2023 . ".scp_Clc C
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2023 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                    INNER JOIN (
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2023 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = 2023
                        union all
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2023 . ".scp_Contable
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = 2023
                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2023 . ".cat_ModoPagoClc MP using(mpc_id)
                    INNER JOIN " . BD_CTRL_2023 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2023 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO
                        ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT
                        ON LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id
                        and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio
                        and LOT.lot_ejr = " . APP_EJERCICIO . "
                    INNER JOIN " . BD_CTRL_2022 . ".cat_TipoClc tipo using(tcl_id)
                    INNER JOIN " . BD_CTRL_2022 . ".cat_SubtipoClc subtipo using(stc_id)
                    LEFT JOIN  ( 	SELECT gpo_id, ur_id, clc_id, GROUP_CONCAT(DISTINCT cat.cap_id SEPARATOR ', ') AS capitulo_gasto
                            FROM " . BD_CTRL_2023 . ".scp_Presupuestal AS sp
                            JOIN " . BD_CTRL_2023 . ".presupuesto AS p USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                            JOIN " . BD_CTRL_2023 . ".cat_PartidaGenerica AS cpg USING(pg_id)
                            JOIN " . BD_CTRL_2023 . ".cat_Capitulo AS cat USING(cap_id)   
                            GROUP BY gpo_id, ur_id, clc_id
                        ) as cap on C.gpo_id = cap.gpo_id  and  C.ur_id = cap.ur_id and C.clc_id = cap.clc_id
                    WHERE C.clc_percepcion - C.clc_retencion > 0
                        AND C.clc_id > 0
                        AND ant.ejr_id_clc = 2023
                        " . $sqlExtra . "
                        " . $sqlCtrlAnterior . "
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id";

            $sqlCtrl2018 = "
                    SELECT
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        (C.clc_percepcion - C.clc_retencion) AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%Y/%m/%d')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        CONCAT(EC.ecl_id,'-',EC.ecl_nombre) as estado,
                        IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'') AS lote_id,
                        CB.cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre) AS cbs_nombre,
                        UR.ur_nombre,
                        DATE_FORMAT(LOT.lot_fechaPago,'%Y/%m/%d') AS lot_fechaPago,
                        doc.rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%Y/%m/%d')clc_generacionCereCo,
                        iba_abreviatura,
                        tipo.tcl_id,tcl_nombre,subtipo.stc_id,stc_nombre,
                       cap.capitulo_gasto
                    FROM " . BD_CTRL_2023 . ".scp_Clc C
                    INNER JOIN " . BD_CTRL_2023 . ".cat_EstadoClc EC ON C.ecl_id = EC.ecl_id
                    INNER JOIN (
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2023 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRL_2023 . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                        " . $estado2018 . "
                        union all
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2023 . ".scp_Contable
                        INNER JOIN " . BD_CTRL_2023 . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                        " . $estado2018 . "

                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2023 . ".cat_ModoPagoClc MP using(mpc_id)
                    INNER JOIN " . BD_CTRL_2023 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2023 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO
                        ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id
                        AND C.clc_id=LO.clc_id
                        AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT
                        ON LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id
                        and LO.lot_ejercicio=LOT.lot_ejercicio
                        and LOT.lot_ejr = YEAR(C.clc_fechaElaboracion)
                    INNER JOIN " . BD_CTRL_2023 . ".cat_TipoClc tipo using(tcl_id)
                    INNER JOIN " . BD_CTRL_2023 . ".cat_SubtipoClc subtipo using(stc_id)
                    LEFT JOIN  ( 	SELECT gpo_id, ur_id, clc_id, GROUP_CONCAT(DISTINCT cat.cap_id SEPARATOR ', ') AS capitulo_gasto
                                FROM " . BD_CTRL_2023 . ".scp_Presupuestal AS sp
                                JOIN " . BD_CTRL_2023 . ".presupuesto AS p USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                                JOIN " . BD_CTRL_2023 . ".cat_PartidaGenerica AS cpg USING(pg_id)
                                JOIN " . BD_CTRL_2023 . ".cat_Capitulo AS cat USING(cap_id)   
                                GROUP BY gpo_id, ur_id, clc_id
                            ) as cap on C.gpo_id = cap.gpo_id  and  C.ur_id = cap.ur_id and C.clc_id = cap.clc_id
                    WHERE C.clc_percepcion - C.clc_retencion > 0
                        AND C.clc_id > 0
                        " . $estadoCtrl2018 . "
                        " . $sqlExtra . "
                        GROUP BY C.gpo_id, C.ur_id, C.clc_id";

            $sql = "
                    " . $ctrlAnterior . "
                    UNION ALL
                    " . $sqlCtrl2018 . "
                ";
            break;
        case 2024:
            $ctrlAnterior = "
                    SELECT
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        (C.clc_percepcion - C.clc_retencion) AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%Y/%m/%d')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        IF(ant.ecl_id IS NULL,
                            (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2024 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                            (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2024 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                        ) AS estado,
                        IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'') AS lote_id,
                        CB.cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre) AS cbs_nombre,
                        UR.ur_nombre,
                        DATE_FORMAT(LOT.lot_fechaPago,'%Y/%m/%d') AS lot_fechaPago,
                        doc.rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%Y/%m/%d')clc_generacionCereCo,
                        iba_abreviatura,
                        tipo.tcl_id,tcl_nombre,subtipo.stc_id,stc_nombre,
                        cap.capitulo_gasto
                    FROM " . BD_CTRL_2024 . ".scp_Clc C
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2024 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                    INNER JOIN (
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2024 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = 2024
                        union all
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2024 . ".scp_Contable
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = 2024
                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2024 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2024 . ".cat_ModoPagoClc MP using(mpc_id)
                    INNER JOIN " . BD_CTRL_2024 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2024 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO
                        ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT
                        ON LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id
                        and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio
                        and LOT.lot_ejr = " . APP_EJERCICIO . "
                    INNER JOIN " . BD_CTRL_2023 . ".cat_TipoClc tipo using(tcl_id)
                    INNER JOIN " . BD_CTRL_2023 . ".cat_SubtipoClc subtipo using(stc_id)
                    LEFT JOIN  ( 	SELECT gpo_id, ur_id, clc_id, GROUP_CONCAT(DISTINCT cat.cap_id SEPARATOR ', ') AS capitulo_gasto
                                    FROM " . BD_CTRL_2024 . ".scp_Presupuestal AS sp
                                    JOIN " . BD_CTRL_2024 . ".presupuesto AS p USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                                    JOIN " . BD_CTRL_2024 . ".cat_PartidaGenerica AS cpg USING(pg_id)
                                    JOIN " . BD_CTRL_2024 . ".cat_Capitulo AS cat USING(cap_id)   
                                    GROUP BY gpo_id, ur_id, clc_id
                                ) as cap on C.gpo_id = cap.gpo_id  and  C.ur_id = cap.ur_id and C.clc_id = cap.clc_id
                    WHERE TRUNCATE(C.clc_percepcion, 2) - TRUNCATE(C.clc_retencion, 2) > 0
                        AND C.clc_id > 0
                        AND ant.ejr_id_clc = 2024
                        " . $sqlExtra . "
                        " . $sqlCtrlAnterior . "
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id";

            $sqlCtrl2018 = "
                    SELECT
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        (C.clc_percepcion - C.clc_retencion) AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%Y/%m/%d')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        CONCAT(EC.ecl_id,'-',EC.ecl_nombre) as estado,
                        IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'') AS lote_id,
                        CB.cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre) AS cbs_nombre,
                        UR.ur_nombre,
                        DATE_FORMAT(LOT.lot_fechaPago,'%Y/%m/%d') AS lot_fechaPago,
                        doc.rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%Y/%m/%d')clc_generacionCereCo,
                        iba_abreviatura,
                        tipo.tcl_id,tcl_nombre,subtipo.stc_id,stc_nombre,
                        cap.capitulo_gasto
                    FROM " . BD_CTRL_2024 . ".scp_Clc C
                    INNER JOIN " . BD_CTRL_2024 . ".cat_EstadoClc EC ON C.ecl_id = EC.ecl_id
                    INNER JOIN (
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2024 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRL_2024 . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                        " . $estado2018 . "
                        union all
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2024 . ".scp_Contable
                        INNER JOIN " . BD_CTRL_2024 . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                        " . $estado2018 . "

                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2024 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2024 . ".cat_ModoPagoClc MP using(mpc_id)
                    INNER JOIN " . BD_CTRL_2024 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2024 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO
                        ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id
                        AND C.clc_id=LO.clc_id
                        AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT
                        ON LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id
                        and LO.lot_ejercicio=LOT.lot_ejercicio
                        and LOT.lot_ejr = YEAR(C.clc_fechaElaboracion)
                    INNER JOIN " . BD_CTRL_2024 . ".cat_TipoClc tipo using(tcl_id)
                    INNER JOIN " . BD_CTRL_2024 . ".cat_SubtipoClc subtipo using(stc_id)
                    LEFT JOIN  ( 	SELECT gpo_id, ur_id, clc_id, GROUP_CONCAT(DISTINCT cat.cap_id SEPARATOR ', ') AS capitulo_gasto
                                    FROM " . BD_CTRL_2024 . ".scp_Presupuestal AS sp
                                    JOIN " . BD_CTRL_2024 . ".presupuesto AS p USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                                    JOIN " . BD_CTRL_2024 . ".cat_PartidaGenerica AS cpg USING(pg_id)
                                    JOIN " . BD_CTRL_2024 . ".cat_Capitulo AS cat USING(cap_id)   
                                    GROUP BY gpo_id, ur_id, clc_id
                                ) as cap on C.gpo_id = cap.gpo_id  and  C.ur_id = cap.ur_id and C.clc_id = cap.clc_id
                    WHERE TRUNCATE(C.clc_percepcion, 2) - TRUNCATE(C.clc_retencion, 2) > 0
                        AND C.clc_id > 0
                        " . $estadoCtrl2018 . "
                        " . $sqlExtra . "
                        GROUP BY C.gpo_id, C.ur_id, C.clc_id";

            $sql = "
                    " . $ctrlAnterior . "
                    UNION ALL
                    " . $sqlCtrl2018 . "
                ";
            break;
        case 2025:
            $ctrlAnterior = "
                    SELECT
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        (C.clc_percepcion - C.clc_retencion) AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%Y/%m/%d')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        IF(ant.ecl_id IS NULL,
                            (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2025 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                            (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2025 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                        ) AS estado,
                        IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'') AS lote_id,
                        CB.cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre) AS cbs_nombre,
                        UR.ur_nombre,
                        DATE_FORMAT(LOT.lot_fechaPago,'%Y/%m/%d') AS lot_fechaPago,
                        doc.rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%Y/%m/%d')clc_generacionCereCo,
                        iba_abreviatura,
                        tipo.tcl_id,tcl_nombre,subtipo.stc_id,stc_nombre,
                        cap.capitulo_gasto
                    FROM " . BD_CTRL_2025 . ".scp_Clc C
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2025 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                    INNER JOIN (
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2025 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = 2025
                        union all
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2025 . ".scp_Contable
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        " . $estado . "
                        AND ant.ejr_id_clc = 2025
                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2025 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2025 . ".cat_ModoPagoClc MP using(mpc_id)
                    INNER JOIN " . BD_CTRL_2025 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2025 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO
                        ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT
                        ON LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id
                        and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio
                        and LOT.lot_ejr = " . APP_EJERCICIO . "
                    INNER JOIN " . BD_CTRL_2025 . ".cat_TipoClc tipo using(tcl_id)
                    INNER JOIN " . BD_CTRL_2025 . ".cat_SubtipoClc subtipo using(stc_id)
                    LEFT JOIN  ( 	SELECT gpo_id, ur_id, clc_id, GROUP_CONCAT(DISTINCT cat.cap_id SEPARATOR ', ') AS capitulo_gasto
                                    FROM " . BD_CTRL_2025 . ".scp_Presupuestal AS sp
                                    JOIN " . BD_CTRL_2025 . ".presupuesto AS p USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                                    JOIN " . BD_CTRL_2025 . ".cat_PartidaGenerica AS cpg USING(pg_id)
                                    JOIN " . BD_CTRL_2025 . ".cat_Capitulo AS cat USING(cap_id)   
                                    GROUP BY gpo_id, ur_id, clc_id
                                ) as cap on C.gpo_id = cap.gpo_id  and  C.ur_id = cap.ur_id and C.clc_id = cap.clc_id
                    WHERE TRUNCATE(C.clc_percepcion, 2) - TRUNCATE(C.clc_retencion, 2) > 0
                        AND C.clc_id > 0
                        AND ant.ejr_id_clc = 2025
                        " . $sqlExtra . "
                        " . $sqlCtrlAnterior . "
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id";

            $sqlCtrl2018 = "
                    SELECT
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        (C.clc_percepcion - C.clc_retencion) AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%Y/%m/%d')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        CONCAT(EC.ecl_id,'-',EC.ecl_nombre) as estado,
                        IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'') AS lote_id,
                        CB.cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre) AS cbs_nombre,
                        UR.ur_nombre,
                        DATE_FORMAT(LOT.lot_fechaPago,'%Y/%m/%d') AS lot_fechaPago,
                        doc.rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%Y/%m/%d')clc_generacionCereCo,
                        iba_abreviatura,
                        tipo.tcl_id,tcl_nombre,subtipo.stc_id,stc_nombre,
                        cap.capitulo_gasto
                    FROM " . BD_CTRL_2025 . ".scp_Clc C
                    INNER JOIN " . BD_CTRL_2025 . ".cat_EstadoClc EC ON C.ecl_id = EC.ecl_id
                    INNER JOIN (
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2025 . ".scp_Presupuestal
                        INNER JOIN " . BD_CTRL_2025 . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                        " . $estado2018 . "
                        union all
                        SELECT gpo_id,ur_id,clc_id,rfc_id FROM " . BD_CTRL_2025 . ".scp_Contable
                        INNER JOIN " . BD_CTRL_2025 . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                        " . $estado2018 . "

                    )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2025 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2025 . ".cat_ModoPagoClc MP using(mpc_id)
                    INNER JOIN " . BD_CTRL_2025 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL_2025 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO
                        ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id
                        AND C.clc_id=LO.clc_id
                        AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT
                        ON LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id
                        and LO.lot_ejercicio=LOT.lot_ejercicio
                        and LOT.lot_ejr = YEAR(C.clc_fechaElaboracion)
                    INNER JOIN " . BD_CTRL_2025 . ".cat_TipoClc tipo using(tcl_id)
                    INNER JOIN " . BD_CTRL_2025 . ".cat_SubtipoClc subtipo using(stc_id)
                    LEFT JOIN  ( 	SELECT gpo_id, ur_id, clc_id, GROUP_CONCAT(DISTINCT cat.cap_id SEPARATOR ', ') AS capitulo_gasto
                                    FROM " . BD_CTRL_2025 . ".scp_Presupuestal AS sp
                                    JOIN " . BD_CTRL_2025 . ".presupuesto AS p USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                                    JOIN " . BD_CTRL_2025 . ".cat_PartidaGenerica AS cpg USING(pg_id)
                                    JOIN " . BD_CTRL_2025 . ".cat_Capitulo AS cat USING(cap_id)   
                                    GROUP BY gpo_id, ur_id, clc_id
                                ) as cap on C.gpo_id = cap.gpo_id  and  C.ur_id = cap.ur_id and C.clc_id = cap.clc_id
                    WHERE TRUNCATE(C.clc_percepcion, 2) - TRUNCATE(C.clc_retencion, 2) > 0
                        AND C.clc_id > 0
                        " . $estadoCtrl2018 . "
                        " . $sqlExtra . "
                        GROUP BY C.gpo_id, C.ur_id, C.clc_id";

            $sql = "
                    " . $ctrlAnterior . "
                    UNION ALL
                    " . $sqlCtrl2018 . "
                ";
            break;
        case 2026:
        default:
            $sql = "

                        SELECT
                            C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                            (C.clc_percepcion - C.clc_retencion) AS importe,C.clc_concepto,
                            CX.clc_cereCo ,
                            CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                            DATE_FORMAT(C.clc_fechaElaboracion,'%Y/%m/%d')clc_fechaElaboracion,
                            MP.mpc_nombre ,
                            if(C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                            C.clc_beneficiario,
                            CONCAT(EC.ecl_id,'-',EC.ecl_nombre) as estado,
                            IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'') AS lote_id,
                            CB.cbs_id,
                            CF.ecf_clave,
                            '' AS loe_clave,
                            IF(CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre) AS cbs_nombre,
                            UR.ur_nombre,
                            DATE_FORMAT(LOT.lot_fechaPago,'%Y/%m/%d')AS lot_fechaPago,
                            doc.rfc_id,
                            YEAR(C.clc_fechaElaboracion) AS ejercicio,
                            DATE_FORMAT(CX.clc_generacionCereCo,'%Y/%m/%d')clc_generacionCereCo,
                            iba_abreviatura,
                            tipo.tcl_id,tcl_nombre,subtipo.stc_id,subtipo.stc_nombre,
                            cap.capitulo_gasto
                        FROM " . BD_CTRL . ".scp_Clc C
                        INNER JOIN " . BD_CTRL . ".cat_EstadoClc EC on C.ecl_id = EC.ecl_id
                        LEFT JOIN (
                                        select gpo_id,ur_id,clc_id,rfc_id from " . BD_CTRL . ".scp_Presupuestal
                                        INNER JOIN " . BD_CTRL . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                                        " . $estado . "
                                            union all
                                        select gpo_id,ur_id,clc_id,rfc_id from " . BD_CTRL . ".scp_Contable
                                        INNER JOIN " . BD_CTRL . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                                        " . $estado . "
                                    )as doc using (gpo_id,ur_id,clc_id)
                        LEFT JOIN " . BD_CTRL . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        INNER JOIN " . BD_CTRL . ".cat_ModoPagoClc MP using(mpc_id)
                        INNER JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento CF using(ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                        LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                        INNER JOIN " . BD_CTRL . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                        LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO
                            ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                        LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT
                            ON LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id
                            and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio
                            and LOT.lot_ejr = " . APP_EJERCICIO . "
                        INNER JOIN " . BD_CTRL . ".cat_TipoClc tipo using(tcl_id)
                        INNER JOIN " . BD_CTRL . ".cat_SubtipoClc subtipo using(stc_id)
                        LEFT JOIN  (   SELECT gpo_id, ur_id, clc_id, GROUP_CONCAT(DISTINCT cat.cap_id SEPARATOR ', ') AS capitulo_gasto
                                        FROM " . BD_CTRL . ".scp_Presupuestal AS sp
                                        JOIN " . BD_CTRL . ".presupuesto AS p USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                                        JOIN " . BD_CTRL . ".cat_PartidaGenerica AS cpg USING(pg_id)
                                        JOIN " . BD_CTRL . ".cat_Capitulo AS cat USING(cap_id)   
                                        GROUP BY gpo_id, ur_id, clc_id
                                    ) as cap on C.gpo_id = cap.gpo_id  and  C.ur_id = cap.ur_id and C.clc_id = cap.clc_id
                        WHERE TRUNCATE(C.clc_percepcion, 2) - TRUNCATE(C.clc_retencion, 2) > 0
                            AND C.clc_id > 0
                            " . $sqlExtra . "

                        GROUP BY C.gpo_id, C.ur_id, C.clc_id";
            break;
    }

    return $sql;
} // Fin function consultaEstadoClc
