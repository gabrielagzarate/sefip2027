<?php

function consultaPropuesta($lot_tipo = '', $fpa_id = '', $msc_id = '', $lot_ejercicio = '', $lot_id = '')
{

    //buscamos los datos segun el ejercicio
    $contador = 1;
    $params = [];
    switch ($lot_tipo) {
        case 6: //manuales
        case 7: //manuales federales
            $sql = "SELECT
                LO.fpa_id as lotDet_fpa,
                LO.msc_id as lotDet_msc,
                LO.lot_id as lotDet,
                LO.lot_ejercicio as lotDet_ejercicio,
                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'),
                LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                LO.lom_importe as lotDet_importe,
                LO.cbs_id as cargo,
                LO.cbb_id as destino,
                LO.lom_concepto as lotDet_concepto,
                LO.lom_beneficiario as lotDet_beneficiario,
                CB.cbs_nombre as nombre_cuenta,
                IB.iba_abreviatura as nombre_banco,
                LT.lot_fechaPago as fecha_Pago
                from " . BD_TESORERIA . ".lot_PropuestaManual LO
                inner join " . BD_TESORERIA . ".lot_Lote as LT using(fpa_id,msc_id,lot_id,lot_ejercicio)
                inner join " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                inner join " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
            WHERE
                    LT.lot_ejr = :ejer
                and LO.fpa_id =  :fpa
                and LO.msc_id= :msc
                and LO.lot_ejercicio = :lotEjer
                and LO.lot_id= :lotId ;";
            $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
            break;
        case 1: //normales
        default:
            //separamos por año
            switch ($lot_ejercicio) {
                case 2017:
                    $sql = "SELECT
                            LO.fpa_id AS lotDet_fpa,
                            LO.msc_id AS lotDet_msc,
                            LO.lot_ejercicio AS lotDet_ejercicio,
                            LO.lot_id AS lotDet,
                            LC.gpo_id AS lotDet_gpo,
                            LC.ur_id AS lotDet_ur,
                            LC.clc_id AS lotDet_clc,
                            LC.lot_importeClc AS lotDet_importe,
                            CL.clc_beneficiario AS lotDet_beneficiario,
                            CL.clc_concepto AS lotDet_concepto,
                            concat(CL.gpo_id,CL.ur_id) ur,
                            IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                            CX.clc_cereCo as Folio,
                            CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                            LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                            CL.bur_id as destino,
                            BN.bsf_id as cargo,
                            CV.ecf_clave as cve_fin,
                            UR.ur_nombre as responsable,
                            LO.lot_fechaGeneracion AS lotDet_fecha,
                            LE.loe_clave as lote_egreso,
                            LO.lot_fechaPago as fecha_Pago,
                            CB.cbs_nombre as nombre_cuenta,
                            IB.iba_abreviatura as nombre_banco,
                            LIC.lca_lineaCaptura,
                            LIC.lca_clabeAbono,
                            LIC.lca_importe
                        FROM
                            " . BD_TESORERIA . ".lot_Lote AS LO
                        INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                        LEFT JOIN " . BD_TESORERIA . ".vin_LoteDocumentoLineaCaptura VL
                        using (fpa_id,msc_id,lot_ejercicio,lot_id,gpo_id,ur_id,clc_id)
                        LEFT JOIN " . BD_TESORERIA . ".lca_LineaCaptura LIC using(lca_id)
                        INNER JOIN " . BD_CTRL_2017 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                        INNER JOIN " . BD_CTRL_2017 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                        left join " . BD_CTRL_2017 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                        left join " . BD_CTRL_2017 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                        INNER JOIN " . BD_CTRL_2017 . ".enc_BancosSefinCveFin BN USING (ecf_id)
                        INNER JOIN " . BD_CTRL_2017 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                        inner join " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB on (BN.bsf_id = CB.cbs_id)
                        inner join " . BD_TESORERIA . ".cat_InstitucionBancaria IB on (CB.iba_id=IB.iba_id)
                        INNER JOIN " . BD_CTRL_2017 . ".cat_UnidadResponsable UR
                        ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                        WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id =  :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio =  :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                    $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    break;
                case 2018:
                    if ($lot_tipo == 5) {
                        $params = [];
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                                LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                LE.loe_clave as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco,
                                CL.clc_lineaCaptura,
                                (CL.clc_percepcion-CL.clc_retencion) as importe
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL_2018 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2018 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            LEFT JOIN " . BD_CTRL_2018 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                            LEFT JOIN " . BD_CTRL_2018 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL_2018 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL_2018 . ".cat_UnidadResponsable UR
                            ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id = :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio = :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                        $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    } else {
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                                LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                LE.loe_clave as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL_2018 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2018 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            LEFT JOIN " . BD_CTRL_2018 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                            LEFT JOIN " . BD_CTRL_2018 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL_2018 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL_2018 . ".cat_UnidadResponsable UR
                            ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id = :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio = :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                        $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    }
                    break;
                case 2019:
                    $params = [];
                    if ($lot_tipo == 5) {
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                                LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                    '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco,
                                CL.clc_lineaCaptura,
                                (CL.clc_percepcion-CL.clc_retencion) as importe
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL_2019 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2019 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            #LEFT JOIN " . BD_CTRL_2019 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                            #LEFT JOIN " . BD_CTRL_2019 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL_2019 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL_2019 . ".cat_UnidadResponsable UR
                            ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id = :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio = :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                            $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    } else {
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                                LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                #LE.loe_clave as lote_egreso,
                                    '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL_2019 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2019 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            #LEFT JOIN " . BD_CTRL_2019 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                            #LEFT JOIN " . BD_CTRL_2019 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL_2019 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL_2019 . ".cat_UnidadResponsable UR
                            ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id = :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio = :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                            $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    }
                    break;
                case 2020:
                    $params = [];
                    if ($lot_tipo == 5) {
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                                LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco,
                                CL.clc_lineaCaptura,
                                (CL.clc_percepcion-CL.clc_retencion) as importe
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL_2020 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2020 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            #LEFT JOIN " . BD_CTRL_2020 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                            #LEFT JOIN " . BD_CTRL_2020 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL_2020 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL_2020 . ".cat_UnidadResponsable UR
                            ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id = :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio = :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                            $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    } else {
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                                LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                #LE.loe_clave as lote_egreso,
                                '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL_2020 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2020 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            #LEFT JOIN " . BD_CTRL_2020 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                            #LEFT JOIN " . BD_CTRL_2020 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL_2020 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL_2020 . ".cat_UnidadResponsable UR
                            ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id = :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio = :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                            $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    }
                    break;
                case 2021:
                    $params = [];
                    if ($lot_tipo == 5) {
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                                LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco,
                                CL.clc_lineaCaptura,
                                (CL.clc_percepcion-CL.clc_retencion) as importe
                                FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL_2021 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2021 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL_2021 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL_2021 . ".cat_UnidadResponsable UR
                            ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id = :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio = :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                            $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    } else {
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                                LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL_2021 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2021 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL_2021 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL_2021 . ".cat_UnidadResponsable UR
                            ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id = :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio = :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                            $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    }
                    break;
                case 2022:
                    $params = [];
                    if ($lot_tipo == 5) {
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                                LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco,
                                CL.clc_lineaCaptura,
                                (CL.clc_percepcion-CL.clc_retencion) as importe
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL_2022 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2022 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL_2022 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL_2022 . ".cat_UnidadResponsable UR
                            ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id = :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio = :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                            $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    } else {
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                                LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL_2022 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2022 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL_2022 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL_2022 . ".cat_UnidadResponsable UR
                            ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id = :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio = :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                            $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    }
                    break;
                case 2023:
                    $params = [];
                    if ($lot_tipo == 5) {
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                                LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco,
                                CL.clc_lineaCaptura,
                                (CL.clc_percepcion-CL.clc_retencion) as importe
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL_2023 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL_2023 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL_2023 . ".cat_UnidadResponsable UR
                            ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id = :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio = :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                            $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    } else {
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                                LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL_2023 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL_2023 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL_2023 . ".cat_UnidadResponsable UR
                            ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id = :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio = :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                            $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    }
                    break;
                case 2024:
                    $params = [];
                    if ($lot_tipo == 5) {
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                                LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco,
                                CL.clc_lineaCaptura,
                                (CL.clc_percepcion-CL.clc_retencion) as importe
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL_2024 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2024 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL_2024 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL_2024 . ".cat_UnidadResponsable UR
                            ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id = :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio = :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                            $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    } else {
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                                LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL_2024 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2024 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL_2024 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL_2024 . ".cat_UnidadResponsable UR
                            ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id = :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio = :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                            $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    }
                    break;
                case 2025:
                    $params = [];
                    if ($lot_tipo == 5) {
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                                LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco,
                                CL.clc_lineaCaptura,
                                (CL.clc_percepcion-CL.clc_retencion) as importe
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL_2025 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2025 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL_2025 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL_2025 . ".cat_UnidadResponsable UR
                            ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id = :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio = :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                            $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    } else {
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                                LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL_2025 . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL_2025 . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL_2025 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL_2025 . ".cat_UnidadResponsable UR
                            ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id = :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio = :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                            $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    }
                    break;
                case 2026:
                default:
                    if ($lot_tipo == 5) {
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                                LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco,
                                CL.clc_lineaCaptura,
                                (CL.clc_percepcion-CL.clc_retencion) as importe
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL . ".cat_UnidadResponsable UR
                            ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id = :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio = :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                            $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    } else {
                        $sql = "SELECT
                                LO.fpa_id AS lotDet_fpa,
                                LO.msc_id AS lotDet_msc,
                                LO.lot_ejercicio AS lotDet_ejercicio,
                                LO.lot_id AS lotDet,
                                LC.gpo_id AS lotDet_gpo,
                                LC.ur_id AS lotDet_ur,
                                LC.clc_id AS lotDet_clc,
                                LC.lot_importeClc AS lotDet_importe,
                                CL.clc_beneficiario AS lotDet_beneficiario,
                                CL.clc_concepto AS lotDet_concepto,
                                concat(CL.gpo_id,CL.ur_id) ur,
                                IF(CL.clc_tipoCereCo = 1,'CE','CO') as cere,
                                CX.clc_cereCo as Folio,
                                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ),
                                LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                                CL.bur_id as destino,
                                CF.cbs_id as cargo,
                                CV.ecf_clave as cve_fin,
                                UR.ur_nombre as responsable,
                                LO.lot_fechaGeneracion AS lotDet_fecha,
                                LO.lot_fechaPago AS fecha_Pago,
                                '' as lote_egreso,
                                CB.cbs_nombre as nombre_cuenta,
                                IB.iba_abreviatura as nombre_banco
                            FROM
                                " . BD_TESORERIA . ".lot_Lote AS LO
                            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc AS LC USING (lot_id, fpa_id, msc_id, lot_ejercicio)
                            INNER JOIN " . BD_CTRL . ".scp_Clc AS CL USING (gpo_id, ur_id, clc_id)
                            INNER JOIN " . BD_CTRL . ".scp_ClcAuxiliar as CX using(gpo_id,ur_id,clc_id)
                            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento CF USING (ecf_id)
                            INNER JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento CV using (ecf_id)
                            INNER JOIN " . BD_CTRL . ".cat_UnidadResponsable UR
                            ON (concat(CL.gpo_id, CL.ur_id) = concat(UR.gpo_id, UR.ur_id))
                            INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin as CB using(cbs_id)
                            INNER JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria as IB using(iba_id)
                            WHERE
                                LO.lot_ejr = :ejer
                            and LO.fpa_id = :fpa
                            and LO.msc_id= :msc
                            and LO.lot_ejercicio = :lotEjer
                            and LO.lot_id= :lotId order by LC.lot_importeClc";
                            $params = [":ejer" => APP_EJERCICIO, ":fpa" => $fpa_id, ":msc" => $msc_id, ":lotEjer" => $lot_ejercicio, ":lotId" => $lot_id];
                    }
            }
            break;
    } //FIN switch

    $datos = new AccesoDatos();
    $datos->conectar();
    $datos->setSql($sql);
    $datos->consultar($params);
    $datos->desconectar();

    $result = array();

    while ($row = $datos->extraer()) {
        $dato = array(
            'contador' => $contador++,
            'fecha_Pago' => $row['fecha_Pago'],
            'lote_egreso' => isset($row['lote_egreso'])? $row['lote_egreso'] : '',
            'lotDet_clc' => isset($row['lotDet_clc'])? $row['lotDet_clc'] : '',
            'Folio' => isset($row['Folio'])? $row['Folio'] : '',
            'responsable' => isset($row['responsable'])? $row['responsable'] : '',
            'lotDet_beneficiario' => $row['lotDet_beneficiario'],
            'importe' => isset($row['importe'])?number_format($row['importe'], 2, '.', ','):'',
            'nombre_banco' => utf8Decode($row['nombre_banco']),
            'cargo' => $row['cargo'],
            'nombre_cuenta' => $row['nombre_cuenta'],
            'cve_fin' => isset($row['cve_fin'])? $row['cve_fin'] : '',
            'lote_id' => $row['lote_id'],
            'lca_importe' => isset($row['lca_importe'])? $row['lca_importe'] : '',
            'clc_lineaCaptura' => isset($row['clc_lineaCaptura'])? $row['clc_lineaCaptura'] : '',
            'lca_importe' => isset($row['lca_importe'])? $row['lca_importe'] : '',
            'lotDet_importe' => $row['lotDet_importe'],
            'destino' => $row['destino'],
        );

        array_push($result, $dato);
    }
    return $result;
}
