<?php

class clcs2023
{
    public function __construct()
    {
        $ejercicio = 2023;
    }

    public function consultaFolios23($linea)
    {
        $sql = 'SELECT MAX(lot_ejercicio) AS lot_ejercicio,c.clc_tipoCereCo,cx.clc_cereCo,c.ecl_id 
        from ' . BD_TESORERIA . '.vin_LoteDocumentoLineaCaptura
        INNER JOIN ' . BD_CTRL_2023 . '.scp_Clc as c using (gpo_id,ur_id,clc_id)
        INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id)
        inner join ' . BD_CTRL_2023 . '.scp_ClcAuxiliar as cx using (gpo_id,ur_id,clc_id)
        where lca_id=' . $linea . ' AND ant.ejr_id_clc = 2023
        group by c.gpo_id,c.ur_id,c.clc_id,cx.clc_cereco
        ;';
        return $sql;
    }

    public function consultaLcVencida($linea)
    {
        $sql = "SELECT 
            lp.lca_id AS orden, 
            lp.lca_lineaCaptura AS linea_captura,
            lp.lca_importe as importe
        FROM
            " . BD_TESORERIA . ".vin_LineaCaptura lv
                INNER JOIN
            " . BD_TESORERIA . ".lca_LineaCaptura AS lp ON lp.lca_id = lv.lca_id_pago
        WHERE
            lca_id_siox =" . $linea . " and elc_id=1;";
        return $sql;
    }

    public function getDetalleLoteLinea2023($lot_id, $fpa_id, $msc_id, $lot_ejercicio)
    {
        $sql="SELECT 
        CONCAT(IF(l.fpa_id = 1,
                'T',
                IF(l.fpa_id = 2, 'C', 'L')),
            LPAD(l.msc_id, 2, '0'),
            LPAD(l.lot_id, 4, '0'),
            SUBSTR(l.lot_ejercicio, 3)) AS lotDet,
        CONCAT(dl.gpo_id, dl.ur_id) AS lotDet_ur,
        ' ' AS lote_egreso,
        dl.clc_id AS lotDet_clc,
        IF(clc.clc_tipoCereCo = 1, 'OTB', 'OP') AS lotDet_tipo,
        x.clc_cereCo AS lotDet_folio,
        FORMAT(lca.lca_importe, 2) AS lotDet_importe,
        ur_nombre AS lotDet_responsable,
        clc.clc_beneficiario AS lotDet_beneficiario,
        iba_abreviatura AS banco,
        sf.cbs_id AS lotDet_CtaCargo,
        cv.cov_cuentaReferencia AS lotDet_CtaAbono,
        clc.clc_concepto AS lotDet_concepto,
        l.lot_fechaPago AS lotDet_fechaPago,
        l.lot_fechaGeneracion AS lotDet_fecha,
        l.lot_ejercicio AS lotDet_ejercicio,
        dl.fpa_id AS lotDet_fpa,
        cvf.ecf_clave AS cvefin,
        dl.msc_id AS lotDet_msc,lca.lca_lineaCaptura as lotDet_lineaC,
        lca.lca_folio as lotDet_folioL
        FROM
        " . BD_TESORERIA . ".lot_Lote AS l
            INNER JOIN
        " . BD_TESORERIA . ".lot_LoteClc AS dl 
        USING (lot_id , fpa_id , msc_id , lot_ejercicio)
            INNER JOIN
        " . BD_TESORERIA . ".lot_LoteDocumento ldc 
        USING (fpa_id , msc_id , lot_ejercicio , lot_id , gpo_id , ur_id , clc_id)
            INNER JOIN
        " . BD_TESORERIA . ".vin_LoteDocumentoLineaCaptura vlca 
        USING (fpa_id , msc_id , lot_ejercicio , lot_id , gpo_id , ur_id , clc_id)
            INNER JOIN
        " . BD_TESORERIA . ".lca_LineaCaptura AS lca ON lca.lca_id = vlca.lca_id
            INNER JOIN
        " . BD_TESORERIA . ".cat_Convenio AS cv ON lca.cov_id = cv.cov_id
            INNER JOIN
        " . BD_CTRL_2023 . ".scp_Clc AS clc USING (gpo_id , ur_id , clc_id)
            INNER JOIN
        " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id , ur_id , clc_id)
            INNER JOIN
        " . BD_CTRL_2023 . ".scp_ClcAuxiliar AS x USING (gpo_id , ur_id , clc_id)
            INNER JOIN
        " . BD_CTRL_2023 . ".cat_UnidadResponsable USING (gpo_id , ur_id)
            INNER JOIN
        " . BD_CTRL_2023 . ".enc_ClaveFinanciamiento AS cvf USING (ecf_id)
            INNER JOIN
        " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento AS v USING (ecf_id)
            INNER JOIN
        " . BD_TESORERIA . ".ban_CuentaBancariaSefin AS sf USING (cbs_id)
            INNER JOIN
        " . BD_TESORERIA . ".cat_InstitucionBancaria AS ib on sf.iba_id=ib.iba_id
            WHERE
        l.lot_ejr = " . APP_EJERCICIO . " AND dl.lot_id =".$lot_id." AND dl.fpa_id = ".$fpa_id." AND dl.msc_id = ".$msc_id.
        " AND dl.lot_ejercicio = ".$lot_ejercicio." AND ant.ecl_id=15 AND ant.ejr_id_clc = 2023 order by x.clc_cereCo";
        return $sql;
    }

    public function getDetalleLoteLc2023($lotes)
    {
        $sql = "SELECT  
                CONCAT(a.gpo_id, a.ur_id) AS ur,
                a.clc_id,
                ecf.ecf_id,
                a.ctaAbono,
                a.tlo_id,
                vscf.cbs_id AS ctaCargo,
                a.importe,
                a.anio,
                5 AS tipo,
                a.orden,
                a.linea_captura,
                CURDATE() AS fecha,
                c.clc_concepto AS concepto,
                c.clc_beneficiario AS benef,
                a.elo_id,
                a.estadolc,
                c.ecl_id      
        FROM (SELECT 
                lc.gpo_id, 
                lc.ur_id,        
                lc.clc_id,
                l.tlo_id,
                l.elo_id,
                SUBSTR(l.lot_ejercicio, 3) AS anio,
                lcap.lca_importe AS importe,
                cv.cov_cuentaReferencia AS ctaAbono,
                lcap.lca_id AS orden,
                lcap.lca_lineaCaptura AS linea_captura,
                lcap.elc_id as estadolc
        FROM " . BD_TESORERIA . ".lot_Lote AS l
            INNER JOIN
        " . BD_TESORERIA . ".lot_LoteClc AS lc USING (fpa_id , msc_id , lot_ejercicio , lot_id)
            INNER JOIN
        " . BD_TESORERIA . ".lot_LoteDocumento ld USING (fpa_id , msc_id , lot_ejercicio , lot_id , gpo_id , ur_id , clc_id)
            INNER JOIN
        " . BD_TESORERIA . ".vin_LoteDocumentoLineaCaptura vl USING (fpa_id,msc_id,lot_ejercicio,lot_id,gpo_id,ur_id,clc_id)
            INNER JOIN
        " . BD_TESORERIA . ".lca_LineaCaptura AS lcap ON vl.lca_id = lcap.lca_id
            INNER JOIN
        " . BD_TESORERIA . ".cat_Convenio as cv on cv.cov_id= lcap.cov_id
        WHERE  (l.fpa_id , l.msc_id, l.lot_ejercicio, l.lot_id) IN (" . $lotes . ")
            AND l.lot_ejr = " . APP_EJERCICIO . "
        GROUP BY lcap.lca_id
        ) a
            INNER JOIN
        " . BD_CTRL_2023 . ".scp_Clc AS c USING (gpo_id , ur_id , clc_id)
            INNER JOIN
        " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id , ur_id , clc_id)
            INNER JOIN
        " . BD_CTRL_2023 . ".scp_ClcAuxiliar lca USING (gpo_id , ur_id , clc_id)
            INNER JOIN
        " . BD_CTRL_2023 . ".enc_ClaveFinanciamiento ecf USING (ecf_id)
            INNER JOIN
        " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento vscf USING (ecf_id)
        WHERE ant.ecl_id=15 AND ant.ejr_id_clc = 2023
        ORDER BY lca.clc_CereCo;
        ";
        return $sql;
    }
   
    public function getDetLayoutManual2023($laiD, $tipo, $ejercicio)
    {
        $sql = 'SELECT
            CONCAT( IF ( vl.fpa_id = 1, "T", IF (vl.fpa_id = 2, "C", "L") ), 
            LPAD(vl.msc_id, 2, "0"), LPAD(vl.lot_id, 4, "0"), SUBSTR(vl.lot_ejercicio, 3) ) AS layDet_lote,
            concat(m.gpo_id,m.ur_id) as layDet_ur,
            m.clc_id as layDet_clc,
            "Manual" as layDet_tipo,
            ldt.lad_referenciaAPagar as layDet_folio,
            format(lad_importe,2) as layDet_importe,
            ur.ur_nombre as layDet_responsable,
            m.lom_beneficiario as layDet_beneficiario,
            ldt.cbs_id as layDet_CtaCargo,
            ldt.cbb_id as layDet_CtaAbono,
            m.lom_concepto as  layDet_concepto,
            lay_fechaSistema as layDet_fecha
            from ' . BD_TESORERIA . '.vin_LoteLayout as vl 
            inner join ' . BD_TESORERIA . '.lot_Layout la using (lay_id)
            inner join ' . BD_TESORERIA . '.lot_PropuestaManual as m using (fpa_id,msc_id,lot_ejercicio,lot_id)
            left join ' . BD_CTRL_2023 . '.scp_Clc as c using (gpo_id,ur_id,clc_id)
            left join ' . BD_CTRL_2023 . '.scp_ClcAuxiliar as x using (gpo_id,ur_id,clc_id,ecl_id)
            left join ' . BD_CTRL_2023 . '.cat_UnidadResponsable as ur using (gpo_id,ur_id)
            left join ' . BD_CTRL_2023 . '.enc_ClaveFinanciamiento using (ecf_id)
            left join ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento as cbs using (ecf_id)
            inner join ' . BD_TESORERIA . '.lot_LayoutArchivoDetalle as ldt on ldt.lay_id=la.lay_id
            where la.lay_id=' . $laiD . ' and tla_id=' . $tipo . ' and vl.lot_ejercicio=' . $ejercicio . ';';

        return $sql;
    }

    public function getDetLayoutLc2023($laiD, $tipo, $ejercicio)
    {
        $sql = 'select 
            CONCAT( IF ( vl.fpa_id = 1, "T", IF (vl.fpa_id = 2, "C", "L") ), 
            LPAD(vl.msc_id, 2, "0"), LPAD(vl.lot_id, 4, "0"), SUBSTR(vl.lot_ejercicio, 3) ) AS layDet_lote, 
            CONCAT(c.gpo_id,c.ur_id) as layDet_ur,
            lc.clc_id as layDet_clc,
            if(c.clc_tipoCereCo=1,"OTB","OP") as layDet_tipo,
            lcp.lca_lineaCaptura as layDet_folio,
            ld.lad_importe as layDet_importe,
            ur.ur_nombre as layDet_responsable,
            c.clc_beneficiario as layDet_beneficiario,
            ld.cbs_id as layDet_CtaCargo,
            ld.cbb_id as layDet_CtaAbono,
            c.clc_concepto as  layDet_concepto,
             lay_fechaSistema as layDet_fecha
            from 
            " . BD_TESORERIA . ".vin_LoteLayout as vl
            inner join ' . BD_TESORERIA . '.lot_LoteClc as lc using (fpa_id,msc_id,lot_ejercicio,lot_id)
            inner join ' . BD_TESORERIA . '.lot_Layout using (lay_id)
            inner join ' . BD_TESORERIA . '.lot_LayoutArchivo using (lay_id)
            inner join ' . BD_TESORERIA . '.lot_LayoutArchivoDetalle ld using (lay_id)
            inner join ' . BD_TESORERIA . '.lca_LineaCaptura as lcp on lcp.lca_id=ld.lad_referenciaAPagar
            inner join ' . BD_CTRL_2023 . '.scp_Clc as c using (gpo_id,ur_id,clc_id)
            inner join ' . BD_CTRL_2023 . '.cat_UnidadResponsable as ur on lc.gpo_id=ur.gpo_id and lc.ur_id=ur.ur_id

            where lay_id=' . $laiD . ' and tla_id=' . $tipo . ' and lot_ejercicio=' . $ejercicio . ';
            ';
        return $sql;
    }

    public function getDetLayoutPdp2023($laiD, $tipo, $ejercicio)
    {
        $sql = ' select 
            CONCAT( IF ( vl.fpa_id = 1, "T", IF (vl.fpa_id = 2, "C", "L") ), 
            LPAD(vl.msc_id, 2, "0"), LPAD(vl.lot_id, 4, "0"), SUBSTR(vl.lot_ejercicio, 3) ) AS layDet_lote, 
             CONCAT(c.gpo_id,c.ur_id) as layDet_ur,
             c.clc_id as layDet_clc,
             if(c.clc_tipoCereCo=1,"OTB","OP") as layDet_tipo,
             ax.bdo_referencia as layDet_folio,
             ld.lad_importe as layDet_importe,
             ur.ur_nombre as layDet_responsable,
             c.clc_beneficiario as layDet_beneficiario,
             ld.cbs_id as layDet_CtaCargo,
             ld.cbb_id as layDet_CtaAbono,
             c.clc_concepto as  layDet_concepto,
              lay_fechaSistema as layDet_fecha
             from 
             ' . BD_TESORERIA . '.vin_LoteLayout as vl
             inner join ' . BD_TESORERIA . '.lot_Layout using (lay_id  )
             inner join ' . BD_TESORERIA . '.lot_LayoutArchivo using (lay_id)
             inner join ' . BD_TESORERIA . '.lot_LayoutArchivoDetalle ld using (lay_id)
             inner join ' . BD_CTRL_2023 . '.cat_BancoDocumentos as ax on ax.bdo_id=ld.lad_referenciaAPagar
             inner join ' . BD_TESORERIA . '.lot_LoteClc as lc on lc.fpa_id=vl.fpa_id 
             and lc.msc_id=vl.msc_id and vl.lot_ejercicio=lc.lot_ejercicio and vl.lot_id=lc.lot_id
             inner join ' . BD_CTRL_2023 . '.scp_Clc as c on lc.gpo_id=c.gpo_id and lc.ur_id=c.ur_id and lc.clc_id=c.clc_id
             inner join ' . BD_CTRL_2023 . '.cat_UnidadResponsable as ur on c.gpo_id=ur.gpo_id and c.ur_id=ur.ur_id
             where lay_id=' . $laiD . ' and tla_id=' . $tipo . ' and vl.lot_ejercicio=' . $ejercicio . ';
             ';
        return $sql;
    }

    public function getDetLayout2023($laiD, $tipo, $ejercicio)
    {
        $sql = ' select 
            CONCAT( IF ( vl.fpa_id = 1, "T", IF (vl.fpa_id = 2, "C", "L") ),
             LPAD(vl.msc_id, 2, "0"), LPAD(vl.lot_id, 4, "0"), SUBSTR(vl.lot_ejercicio, 3) ) AS layDet_lote, 
            CONCAT(c.gpo_id,c.ur_id) as layDet_ur,
            c.clc_id as layDet_clc,
            if(c.clc_tipoCereCo=1,"OTB","OP") as layDet_tipo,
            ax.clc_cereCo as layDet_folio,
            ld.lad_importe as layDet_importe,
            ur.ur_nombre as layDet_responsable,
            c.clc_beneficiario as layDet_beneficiario,
            ld.cbs_id as layDet_CtaCargo,
            ld.cbb_id as layDet_CtaAbono,
            c.clc_concepto as  layDet_concepto,
            lay_fechaSistema as layDet_fecha
            from 
            ' . BD_TESORERIA . '.vin_LoteLayout as vl
            inner join ' . BD_TESORERIA . '.lot_Layout using (lay_id   )
            inner join ' . BD_TESORERIA . '.lot_LayoutArchivo using (lay_id)
            inner join ' . BD_TESORERIA . '.lot_LayoutArchivoDetalle ld using (lay_id)
            inner join ' . BD_CTRL_2023 . '.scp_ClcAuxiliar as ax on ax.clc_cereco=ld.lad_referenciaAPagar
            inner join ' . BD_CTRL_2023 . '.scp_Clc as c on ax.gpo_id=c.gpo_id and ax.ur_id=c.ur_id and ax.clc_id=c.clc_id 
            inner join ' . BD_CTRL_2023 . '.cat_UnidadResponsable as ur on c.gpo_id=ur.gpo_id and c.ur_id=ur.ur_id
            where lay_id=' . $laiD . ' and tla_id=' . $tipo . ' and lot_ejercicio=' . $ejercicio . ';';

        return $sql;
    }

    public function DetalleManual($lot_id, $fpa_id, $msc_id, $lot_ejercicio)
    {
        $sql = 'SELECT
            CONCAT( IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", "L") ), 
            LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"), SUBSTR(l.lot_ejercicio, 3) ) as lotDet,
            concat(m.gpo_id,m.ur_id )lotDet_ur,
                        l.lot_fechaPago as lotDet_fechaPago,
            m.clc_id as lotDet_clc,
                        m.lom_id as lotDet_folio,
            format(m.lom_importe,2) as lotDet_importe,
            m.lom_beneficiario as lotDet_beneficiario,
                        m.cbs_id as lotDet_CtaCargo,
            m.cbb_id as lotDet_CtaAbono,
            m.lom_concepto as lotDet_concepto,
            l.lot_fechaGeneracion AS lotDet_fecha,
            l.lot_ejercicio AS lotDet_ejercicio,
            m.fpa_id as lotDet_fpa,
            m.msc_id as lotDet_msc
            FROM
            ' . BD_TESORERIA . '.lot_Lote AS l
            inner join ' . BD_TESORERIA . '.lot_PropuestaManual as m using (fpa_id,msc_id,lot_ejercicio,lot_id)
            WHERE  
                l.lot_id =' . $lot_id . "
                AND l.fpa_id=" . $fpa_id . "
                AND l.msc_id=" . $msc_id . "
                AND l.lot_ejercicio=" . $lot_ejercicio."
                AND l.lot_ejr = " . APP_EJERCICIO . "";
        return $sql;
    }

    public function DetalleLoteLayout2023($lot_id, $fpa_id, $msc_id, $lot_ejercicio)
    {
        $sql = '
              SELECT CONCAT( IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", "L") ), 
              LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"), SUBSTR(l.lot_ejercicio, 3) ) AS lotDet,
                    concat(dl.gpo_id,dl.ur_id) AS lotDet_ur,
                    " " as lote_egreso,
                    dl.clc_id AS lotDet_clc,
                    IF(clc.clc_tipoCereCo=1,"OTB","OP") as lotDet_tipo,
                    x.clc_cereCo as lotDet_folio,
                    format(dl.lot_importeClc,2) AS lotDet_importe,
                    ur_nombre as lotDet_responsable,
                    clc.clc_beneficiario AS lotDet_beneficiario,
                    iba_abreviatura as banco,
                    sf.cbs_id as lotDet_CtaCargo,
                    clc.bur_id as lotDet_CtaAbono,
                    clc.clc_concepto AS lotDet_concepto,
                    l.lot_fechaPago as lotDet_fechaPago,
                    l.lot_fechaGeneracion AS lotDet_fecha,
                    l.lot_ejercicio AS lotDet_ejercicio,
                    dl.fpa_id AS lotDet_fpa,
                    cvf.ecf_clave as cvefin,
                    dl.msc_id AS lotDet_msc
            FROM
                    ' . BD_TESORERIA . '.lot_Lote AS l
            INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS dl USING (lot_id, fpa_id, msc_id, lot_ejercicio)
            INNER JOIN ' . BD_CTRL_2023 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
            INNER JOIN ' . BD_CTRLANT . '.scp_Clc AS ant USING (gpo_id, ur_id, clc_id)
            INNER JOIN ' . BD_CTRL_2023 . '.scp_ClcAuxiliar as x using (gpo_id,ur_id,clc_id)
            #LEFT JOIN ' . BD_CTRL_2023 . '.scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
            #LEFT JOIN ' . BD_CTRL_2023 . '.scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
            INNER JOIN ' . BD_CTRL_2023 . '.cat_UnidadResponsable using (gpo_id,ur_id)
            INNER JOIN ' . BD_CTRL_2023 . '.enc_ClaveFinanciamiento as cvf using (ecf_id)
            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento as v using (ecf_id)
            INNER JOIN ' . BD_TESORERIA . '.ban_CuentaBancariaSefin as sf using (cbs_id)
            INNER JOIN ' . BD_TESORERIA . '.cat_InstitucionBancaria as ib using (iba_id)
            WHERE
                    dl.lot_id =' . $lot_id . "
                    AND dl.fpa_id=" . $fpa_id . "
                    AND dl.msc_id=" . $msc_id . "
                    AND dl.lot_ejercicio=" . $lot_ejercicio . " 
                    AND ant.ecl_id=15
                    AND ant.ejr_id_clc = 2023
                    AND l.lot_ejr = " . APP_EJERCICIO . " "
        ;

        return $sql;
    }

    public function getDetalleMunicipio2023($fpa_id, $msc_id, $lot_ejercicio, $lot_id)
    {
        $sql='
            SELECT CONCAT( IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", "L") ), 
            LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"), SUBSTR(l.lot_ejercicio, 3) ) AS lotDet,
            concat(lc.gpo_id,lc.ur_id) AS lotDet_ur,
            lc.clc_id AS lotDet_clc,
            IF(c.clc_tipoCereCo=1,"OTB","OP") as lotDet_tipo,
            x.clc_cereCo as lotDet_folio,
            format((d.bdo_percepcionPresupuestal+d.bdo_percepcionContable)-d.bdo_retencionContable,2) AS lotDet_importe,
            ur.ur_nombre as lotDet_responsable,
            pr.mun_nombre  AS lotDet_beneficiario,
            sf.cbs_id as lotDet_CtaCargo,
            m.cbb_id as lotDet_CtaAbono,
            c.clc_concepto AS lotDet_concepto,
            l.lot_fechaPago as lotDet_fechaPago,
            l.lot_fechaGeneracion AS lotDet_fecha,
            l.lot_ejercicio AS lotDet_ejercicio,
            lc.fpa_id AS lotDet_fpa,
            cvf.ecf_clave as cvefin,
            lc.msc_id AS lotDet_msc,c.ecl_id
            FROM ' . BD_TESORERIA . '.lot_Lote  as l
            inner join ' . BD_TESORERIA . '.lot_LoteClc as lc using (fpa_id,msc_id,lot_ejercicio,lot_id)
            inner join ' . BD_CTRL_2023 . '.scp_Clc as c on lc.gpo_id=c.gpo_id and lc.ur_id=c.ur_id and lc.clc_id=c.clc_id
            inner join ' . BD_CTRL_2023 . '.scp_ClcAuxiliar as x on x.gpo_id=c.gpo_id 
            and x.ur_id=c.ur_id and x.clc_id=c.clc_id
            inner join ' . BD_CTRL_2023 . '.cat_UnidadResponsable as ur on ur.gpo_id= c.gpo_id and ur.ur_id=c.ur_id
            inner join (
                select gpo_id,ur_id,clc_id,bdo_id,pr.mun_id as ue,rfc_id 
                from  ' . BD_CTRL_2023 . '.scp_Presupuestal as p 
                join ' . BD_TESORERIA . '.cat_MunicipioProveedor as pr using(rfc_id)
                where p.gpo_id=9 and p.ur_id=01 and p.clc_id>0
                        union
                select gpo_id,ur_id,clc_id,bdo_id,pr.mun_id as ue,rfc_id 
                from  ' . BD_CTRL_2023 . '.scp_Contable as c 
                join ' . BD_TESORERIA . '.cat_MunicipioProveedor as pr using(rfc_id)
                where c.gpo_id=9 and c.ur_id=01 and c.clc_id>0
            ) as doc on doc.gpo_id=lc.gpo_id and doc.ur_id=lc.ur_id and doc.clc_id=lc.clc_id
            inner join ' . BD_CTRL_2023 . '.enc_ClaveFinanciamiento using (ecf_id)
            inner join ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento as sf using (ecf_id)
            inner join (
                    select if(tfn_id>4 && tfn_id<9,2,tfn_id) as tipoF,cbs_id
                    from ' . BD_TESORERIA . '.ban_CuentaBancariaSefin
                    where tfn_id != 1
            )as cb using(cbs_id) 
            inner join ' . BD_CTRL_2023 . '.enc_ClaveFinanciamiento as cvf using (ecf_id)
            inner join ' . BD_CTRL_2023 . '.cat_BancoDocumentos as d on doc.gpo_id=d.gpo_id 
            and doc.ur_id=d.ur_id and doc.bdo_id=d.bdo_id
            INNER join ' . BD_TESORERIA . '.vin_BeneficiarioMunicipio as m on m.mun_id =doc.ue and m.tfn_id=cb.tipoF
            INNER JOIN ' . BD_TESORERIA . '.cat_MunicipioProveedor as pr on pr.rfc_id=doc.rfc_id
            where l.fpa_id='.$fpa_id.'
            and l.msc_id='.$msc_id.' 
            and l.lot_ejercicio='.$lot_ejercicio.' 
            and lot_id='.$lot_id.' 
            and c.ecl_id=15
            and l.lot_ejr = ' . APP_EJERCICIO . ';
           ';
        return $sql;
    }

    public function getDetalleLote2023($lotes)
    {
        $sql = "SELECT 
                concat(lc.gpo_id,lc.ur_id) as ur,
                lc.clc_id ,
                ecf_id,
                c.bur_id as ctaAbono,
                tlo_id,
                sf.cbs_id as ctaCargo,
                lot_importeClc as importe ,
                SUBSTR(lot_ejercicio,3)as anio,            
                c.clc_tipoCereCo AS tipo,
                lca.clc_cereCo as orden,
                CURDATE() AS fecha,
                c.clc_concepto as concepto,
                c.clc_beneficiario as benef,
                elo_id
                FROM " . BD_TESORERIA . ".lot_Lote  as l
                INNER JOIN " . BD_TESORERIA . ".lot_LoteClc as lc using (fpa_id,msc_id,lot_ejercicio,lot_id)
                INNER JOIN " . BD_CTRL_2023 . ".scp_Clc as c on lc.gpo_id=c.gpo_id AND lc.ur_id=c.ur_id AND lc.clc_id=c.clc_id
                INNER JOIN " . BD_CTRLANT . ".scp_Clc as ant on ant.gpo_id=c.gpo_id and ant.ur_id=c.ur_id and ant.clc_id=c.clc_id
                INNER JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar as lca on lca.gpo_id=c.gpo_id 
                AND lca.ur_id=c.ur_id AND lca.clc_id=c.clc_id
                INNER JOIN " . BD_CTRL_2023 . ".enc_ClaveFinanciamiento using (ecf_id)
                INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento as sf using (ecf_id)
                where (l.fpa_id,l.msc_id,l.lot_ejercicio,l.lot_id) in (
                  " . $lotes . "
                )AND ant.ecl_id=15
                AND ant.ejr_id_clc = 2023
                AND l.lot_ejr = " . APP_EJERCICIO . " ;";
        return $sql;
    }

    public function getDetalleLoteManual($lotes)
    {
        $sql = "SELECT
                concat(m.gpo_id,m.ur_id) as ur,
                m.clc_id ,
                ecf_id,
                m.cbb_id as ctaAbono,
                tlo_id,
                m.cbs_id as ctaCargo,
                m.lom_importe as importe ,
                SUBSTR(lot_ejercicio,3)as anio,            
                4 AS tipo,
                m.lom_id as orden,
                CURDATE() AS fecha,
                m.lom_concepto as concepto,
                m.lom_beneficiario as benef,
                l.elo_id
            FROM
            " . BD_TESORERIA . ".lot_Lote AS l
            INNER JOIN " . BD_TESORERIA . ".lot_PropuestaManual as m using (fpa_id,msc_id,lot_ejercicio,lot_id)
            LEFT JOIN " . BD_CTRL_2023 . ".scp_Clc as c on m.gpo_id=c.gpo_id AND m.ur_id=c.ur_id AND m.clc_id=c.clc_id
            LEFT JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar as lca on lca.gpo_id=c.gpo_id 
            AND lca.ur_id=c.ur_id AND lca.clc_id=c.clc_id
            WHERE  (l.fpa_id,l.msc_id,l.lot_ejercicio,l.lot_id) in (
                " . $lotes . "
                )
                -- AND c.ecl_id =15 
                AND l.lot_ejr = " . APP_EJERCICIO . "
                ";
        return $sql;
    }

    public function getDetLotePdp2023($lotes)
    {
        $sql = "SELECT
            CONCAT(c.gpo_id, c.ur_id) ur_clave,
            c.clc_id AS clc,
            cx.clc_cereCo AS orden,
            CURDATE() AS fecha,
            SUBSTR(l.lot_ejercicio,3)as anio,
            3 as tipo,
            ld.bdo_id as documento_id,
            doc.bdo_referencia as documento,
            bsf.cbs_id AS ctaCargo,
            doc.rfc_id as rfc,
            c.clc_beneficiario as benef,
            c.bur_id AS ctaAbono,
            (
                doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable - doc.bdo_retencionContable
            ) importe
        FROM
            " . BD_TESORERIA . ".lot_Lote AS l
        INNER JOIN " . BD_TESORERIA . ".lot_LoteClc using (
            fpa_id,
            msc_id,
            lot_ejercicio,
            lot_id
        )
        INNER JOIN " . BD_TESORERIA . ".lot_LoteDocumento as ld using (fpa_id,msc_id,lot_ejercicio,lot_id,gpo_id,ur_id,clc_id)
        INNER JOIN " . BD_CTRL_2023 . ".cat_BancoDocumentos AS doc USING (gpo_id,ur_id, bdo_id)
        INNER JOIN " . BD_CTRL_2023 . ".scp_Clc AS c USING (gpo_id, ur_id, clc_id)
        INNER JOIN " . BD_CTRLANT . ".scp_Clc AS ant USING (gpo_id, ur_id, clc_id)
        INNER JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar AS cx USING (gpo_id, ur_id, clc_id)
        INNER JOIN " . BD_CTRL_2023 . ".cat_UnidadResponsable USING (gpo_id, ur_id)
        INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento bsf USING (ecf_id)
        INNER JOIN " . BD_CTRL_2023 . ".vin_BancosUrProveedor AS vpr ON c.bur_id = vpr.bur_id
        AND doc.rfc_id = vpr.rfc_id
        WHERE
            (
                l.fpa_id,
                l.msc_id,
                l.lot_ejercicio,
                l.lot_id
            ) IN (" . $lotes . ") AND ant.ecl_id=15
            AND ant.ejr_id_clc=2023
            AND l.lot_ejr = " . APP_EJERCICIO . "
        GROUP BY c.gpo_id, c.ur_id, c.clc_id,l.fpa_id, l.msc_id,l.lot_ejercicio, bsf.cbs_id, doc.bdo_id;
            ";
        return $sql;
    }

    public function getDetalleLoteMunicipio($lotes)
    {
        $sql = "SELECT concat(lc.gpo_id,lc.ur_id) as ur,
                lc.clc_id ,
                ecf_id,
                m.cbb_id as ctaAbono,
                tlo_id,
                sf.cbs_id as ctaCargo,
                d.lot_importeBdo as importe,
                SUBSTR(l.lot_ejercicio,3)as anio,
                3 AS tipo,
                doc.bdo_id as orden,
                CURDATE() AS fecha,
                cd.bdo_referencia as concepto,
                pr.mun_nombre as benef,
                elo_id,doc.ue as ue,
                cb.tipoF
            FROM " . BD_TESORERIA . ".lot_Lote  as l
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc as lc using (fpa_id,msc_id,lot_ejercicio,lot_id)
            INNER JOIN " . BD_CTRL_2023 . ".scp_Clc as c on lc.gpo_id=c.gpo_id AND lc.ur_id=c.ur_id AND lc.clc_id=c.clc_id
            INNER JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar as lca on lca.gpo_id=c.gpo_id 
            AND lca.ur_id=c.ur_id AND lca.clc_id=c.clc_id
            INNER JOIN (
                    SELECT gpo_id,ur_id,clc_id,bdo_id,pr.mun_id as ue,rfc_id 
                    FROM  " . BD_CTRL_2023 . ".scp_Presupuestal as p 
                    JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor as pr using(rfc_id)
                    WHERE p.gpo_id=9 AND p.ur_id=01 AND p.clc_id>0
                            UNION
                    SELECT gpo_id,ur_id,clc_id,bdo_id,pr.mun_id as ue,rfc_id 
                    FROM  " . BD_CTRL_2023 . ".scp_Contable as c 
                    JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor as pr using(rfc_id)
                    WHERE c.gpo_id=9 AND c.ur_id=01 AND c.clc_id>0
            ) as doc on doc.gpo_id=lc.gpo_id AND doc.ur_id=lc.ur_id AND doc.clc_id=lc.clc_id
            INNER JOIN " . BD_CTRL_2023 . ".enc_ClaveFinanciamiento using (ecf_id)
            INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento as sf using (ecf_id)
            INNER JOIN (
                    SELECT if(tfn_id>4 && tfn_id<9,2,tfn_id) as tipoF,cbs_id
                    FROM " . BD_TESORERIA . ".ban_CuentaBancariaSefin
                    WHERE tfn_id != 1
            )as cb using(cbs_id) 
            INNER JOIN " . BD_TESORERIA . ".lot_LoteDocumento as d on doc.gpo_id=d.gpo_id 
             AND doc.ur_id=d.ur_id AND doc.bdo_id=d.bdo_id AND l.lot_ejercicio=d.lot_ejercicio
            INNER JOIN " . BD_CTRL_2023 . ".cat_BancoDocumentos as cd on doc.gpo_id=cd.gpo_id 
            AND doc.ur_id=cd.ur_id AND doc.bdo_id=cd.bdo_id
            INNER JOIN " . BD_TESORERIA . ".vin_BeneficiarioMunicipio as m on m.mun_id =doc.ue AND m.tfn_id=cb.tipoF
            INNER JOIN " . BD_TESORERIA . ".cat_MunicipioProveedor as pr on pr.rfc_id=doc.rfc_id
            WHERE (l.fpa_id,l.msc_id,l.lot_ejercicio,l.lot_id) in (
                " . $lotes . "
            ) AND c.ecl_id=15 AND l.lot_ejr = " . APP_EJERCICIO . "
            AND m.sts_id = 1;
            ";
        return $sql;
    }

    function sqlLlenaTablaClc($prm) {
        $sql = '';
        $anio = isset($prm->anio) ? $prm->anio : "";
        $estado = isset($prm->estado) ? $prm->estado : "";
        $modo = isset($prm->modo) ? $prm->modo : "";
        $tipo = isset($prm->documento) ? $prm->documento : "";
        $tipo_clc = isset($prm->tipo) ? $prm->tipo : "";
        $subtipo_clc = isset($prm->subtipo) ? $prm->subtipo : "";
        $impuesto_op = isset($prm->impuesto) ? $prm->impuesto : "";
        $clc = isset($prm->clc) ? $prm->clc : "";
        $numero = isset($prm->orden) ? $prm->orden : "";
        $ur = isset($prm->numerour) ? $prm->numerour : "";
        $ur_nombre = isset($prm->nombreur) ? $prm->nombreur : "";
        $rfc = isset($prm->rfc) ? $prm->rfc : "";
        $proveedor = isset($prm->proveedor) ? $prm->proveedor : "";
        $beneficiario = isset($prm->beneficiario) ? $prm->beneficiario : "";
        $concepto = isset($prm->concepto) ? $prm->concepto : "";
        $importe_min = isset($prm->importeMin) ? $prm->importeMin : "";
        $importe_max = isset($prm->importeMax) ? $prm->importeMax : "";
        $fecha_min = isset($prm->fechaMin) ? $prm->fechaMin : "";
        $fecha_max = isset($prm->fechaMax) ? $prm->fechaMax : "";

        $importe_min = (double) $importe_min;
        $importe_max = (double) $importe_max;

        $sql_extra = '';
        if (!empty($ur)) {
            $sql_extra .= " AND CD.gpo_id = " . substr($ur, 0, 1) . " AND CD.ur_id = " . substr($ur, 1, 2);
        }

        // MODO   => 1.TRADICIONAL, 2.-LINEA CAPTURA , 4.-CADENA PRODUCTIVA 5.-DEUDA PUBLICA 6.-2.5% //CAMBIO 2016-09-14[IMPUESTO 2.5%]
        $sql_modo = ' AND CD.mpc_id <> 3'; //MODO DE PAGO DE CLC DISTINTO A SIN FLUJO DE EFECTIVO
        switch ($modo) {
            case 1: // TRADICIONAL
            case 2: // LINEA DE CAPTURA
            case 4: // CADENAS PRODUCTIVAS
                $sql_modo = ' AND CD.mpc_id = ' . $modo;
                break;
            case 5:
                $sql_modo = " AND CD.mpc_id <> 3 AND ECF.fte_id = 'B' "; // DEUDA PUBLICA
                break;
            case 6: //CAMBIO 2016-09-14[IMPUESTO 2.5%]
                if (!empty($ur)) {
                    $sql_extra = " AND CD.gpo_id = 1 AND CD.ur_id = 16";
                }
                $sql_extra .= " AND CDX.clc_cereCo IN (0)";
                break;
        }

        if ($tipo == '2')//op
            if ($impuesto_op != '0')
            //PENDIENTE DE ANALISIS
            //$sql_extra = $this->getInfoImpuestos($impuesto_op);
                $sql_extra = " ";

        switch ($tipo) { //clc_tipoCereCo
            case 1: //CERE -- OTB
                $sql_extra .= ' AND CD.clc_tipoCereCo = 1';
                break;
            case 2: //CERECO -- OP
                $sql_extra .= ' AND CD.clc_tipoCereCo = 2';
                break;
        }


        $sql_tipo_clc = "";
        if (!empty($tipo_clc)) {
            $sql_tipo_clc = " AND CD.tcl_id = '$tipo_clc'";
        }
        if (!empty($subtipo_clc)) {
            $sql_tipo_clc .= " AND CD.stc_id = '$subtipo_clc'";
        }

        if (!empty($numero)) {
            $sql_extra .= " AND CDX.clc_cereCo IN ($numero)";
        }

        if (!empty($ur_nombre)) {
            $sql_extra .= " AND CD.gpo_id = " . substr($ur_nombre, 0, 1) . " AND CD.ur_id = " . substr($ur_nombre, 1, 2);
        }

        if (!empty($clc)) {
            $sql_extra .= " AND CD.clc_id IN ($clc)";
        }

        if (!empty($beneficiario)) {
            $sql_extra .= " AND CD.clc_beneficiario = '$beneficiario'";
        }

        if (!empty($concepto)) {
            $sql_extra .= " AND CD.clc_concepto LIKE '%$concepto%'";
        }

        if ($importe_min > 0) {
            if ($importe_max == 0) {
                $sql_extra .= " AND (CD.clc_percepcion - CD.clc_retencion) >= $importe_min";
            } else {
                $sql_extra .= " AND ((CD.clc_percepcion - CD.clc_retencion) BETWEEN $importe_min AND $importe_max) ";
            }
        } else if ($importe_max > 0) {
            $sql_extra .= " AND (CD.clc_percepcion - CD.clc_retencion) <= $importe_max";
        }


        if (!empty($fecha_min)) {
            if (empty($fecha_max)) {
                $sql_extra .= " AND CDX.clc_generacionCereCo >= '$fecha_min'";
            } else {
                $sql_extra .= " AND (CDX.clc_generacionCereCo BETWEEN '$fecha_min 00:00:00' AND '$fecha_max 23:59:59') ";
            }
        } else if (!empty($fecha_max)) {
            $sql_extra .= " AND CDX.clc_generacionCereCo <= '$fecha_max'";
        }

        $ordena = ' ORDER BY CDX.clc_generacionCereCo';

        $sql = "SELECT 
                concat(CD.gpo_id,CD.ur_id,CD.clc_id)  IDCLC,
                SUBSTR(CDX.clc_generacionCereCo,1,10) FECHA_ELABORACION,
                DATEDIFF(CDX.clc_generacionCereCo,NOW()) DIFERENCIA,
                MP.mpc_nombre MODO,
                concat(CD.gpo_id,CD.ur_id) UR,  
                CD.ur_id UR_ID,
                CD.gpo_id GPO_ID,
                CD.clc_id CLC, 
                CDX.clc_cereCo FOLIO,
                IF (CD.clc_tipoCereCo = 1, 'CE', 'CO') TIPO,        
                TC.tcl_nombre NOMBRE_TIPO,
                STC.stc_nombre NOMBRE_SUBTIPO,
                UR.ur_nombre RESPONSABLE,
                CD.clc_beneficiario BENEFICIARIO, 
                CD.clc_concepto CONCEPTO,
                VCB.cbs_id CARGO,
                CD.bur_id DESTINO,
                (CD.clc_percepcion - CD.clc_retencion) IMPORTE,
                ECF.ecf_clave CVE_FIN,
                CF.cfi_nombre CVE_NOMBRE
            FROM
                " . BD_CTRL_2023 . ".scp_Clc CD
                INNER JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar CDX USING(gpo_id, ur_id, clc_id)
                LEFT JOIN (
                    SELECT gpo_id, ur_id, clc_id
                    FROM " . BD_TESORERITEM . ".tem_PropuestaClc as T
                    WHERE T.pro_ejercicio = 2023 and T.usr_id = " .$_SESSION[_S]['usr_id']. "
                ) AS TMP  USING(gpo_id, ur_id, clc_id)
                INNER JOIN " . BD_CTRLANT . ".scp_Clc cda USING(gpo_id, ur_id, clc_id)
                INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento VCB USING (ecf_id)
                INNER JOIN " . BD_CTRL_2023 . ".enc_ClaveFinanciamiento ECF using(ecf_id)
                INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento2023 as CF USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                INNER JOIN " . BD_CTRL_2023 . ".cat_UnidadResponsable UR 
                    ON CD.gpo_id = UR.gpo_id AND CD.ur_id = UR.ur_id
                INNER JOIN " . BD_CTRL_2023 . ".cat_TipoClc TC USING (tcl_id)
                INNER JOIN " . BD_CTRL_2023 . ".cat_SubtipoClc STC USING (tcl_id,stc_id)
                INNER JOIN " . BD_CTRL_2023 . ".cat_ModoPagoClc MP ON (CD.mpc_id = MP.mpc_id)
                INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CBS USING (cbs_id)
                INNER JOIN " . BD_TESORERIA . ".cat_TipoRecurso TR ON CBS.ter_id = TR.ter_id
            WHERE 
                cda.ecl_id = 10 AND cda.ejr_id_clc = 2023 AND TR.ter_id = 'A' AND TMP.clc_id IS NULL
                #AND (CD.gpo_id, CD.ur_id) != ((9,1))
                AND TRUNCATE(CD.clc_percepcion, 2) - TRUNCATE(CD.clc_retencion, 2) > 0 " .
                $sql_tipo_clc .
                $sql_modo .
                $sql_extra .
                $ordena;
        return $sql;
    }

    function sqlLlenaTablaClcProveedor($prm) {
        $sql = "";
        $tipo_clc = 'E'; //Anticipos - Fondo Rotatorio
        $sql_extra = "";

        $anio = isset($this->ejercicio) ? $this->ejercicio : "";
        $estado = isset($prm->estado) ? $prm->estado : "";
        $modo = isset($prm->modo) ? $prm->modo : "";
        $tipo = isset($prm->documento) ? $prm->documento : "";
        $tipo_clc = isset($prm->tipo) ? $prm->tipo : "";
        $subtipo_clc = isset($prm->subtipo) ? $prm->subtipo : "";
        $impuesto_op = isset($prm->impuesto) ? $prm->impuesto : "";
        $clc = isset($prm->clc) ? $prm->clc : "";
        $numero = isset($prm->orden) ? $prm->orden : "";
        $ur = isset($prm->numerour) ? $prm->numerour : "";
        $ur_nombre = isset($prm->nombreur) ? $prm->nombreur : "";
        $rfc = isset($prm->rfc) ? $prm->rfc : "";
        $proveedor = isset($prm->proveedor) ? $prm->proveedor : "";
        $beneficiario = isset($prm->beneficiario) ? $prm->beneficiario : "";
        $concepto = isset($prm->concepto) ? $prm->concepto : "";
        $importe_min = isset($prm->importeMin) ? $prm->importeMin : "";
        $importe_max = isset($prm->importeMax) ? $prm->importeMax : "";
        $fecha_min = isset($prm->fechaMin) ? $prm->fechaMin : "";
        $fecha_max = isset($prm->fechaMax) ? $prm->fechaMax : "";

        switch ($tipo) { //clc_tipoCereCo
            case 1: //CERE -- OTB
                $sql_extra .= ' AND CD.clc_tipoCereCo = 1';
                break;
            case 2: //CERECO -- OP
                $sql_extra .= ' AND CD.clc_tipoCereCo = 2';
                break;
        }

        $sql_extra .= " AND CD.tcl_id != 'E' ";
        if (!empty($tipo_clc) && $tipo_clc != 'E') {
            $sql_extra .= " AND CD.tcl_id = '$tipo_clc'";
        }

        if ($subtipo_clc != '') {
            $sql_extra .= " AND CD.stc_id = $subtipo_clc";
        }

        if (!empty($numero)) {
            $sql_extra .= " AND CDX.clc_cereCo IN ($numero)";
        }

        if (!empty($ur)) {
            $sql_extra .= " AND CD.gpo_id = " . substr($ur, 0, 1) .
                    " AND CD.ur_id = " . substr($ur, 1, 2);
        }

        if (!empty($ur_nombre)) {
            $sql_extra .= " AND CD.gpo_id = " . substr($ur_nombre, 0, 1) . " AND CD.ur_id = " . substr($ur_nombre, 1, 2);
        }

        if (!empty($clc)) {
            $sql_extra .= " AND CD.clc_id IN ($clc)";
        }

        if (!empty($rfc)) {
            $sql_extra .= " AND P.rfc_id = '$rfc'";
        }

        if (!empty($beneficiario)) {
            $sql_extra .= " AND CD.clc_beneficiario = '$beneficiario'";
        }

        if (!empty($concepto)) {
            $sql_extra .= " AND CD.clc_concepto LIKE '%$concepto%'";
        }

        if (!empty($importe_min)) {
            if (empty($importe_max)) {
                $sql_extra .= " AND (CD.clc_percepcion - CD.clc_retencion) >= $importe_min";
            } else {
                $sql_extra .= " AND ((CD.clc_percepcion - CD.clc_retencion) BETWEEN $importe_min AND $importe_max) ";
            }
        } else if (!empty($importe_max)) {
            $sql_extra .= " AND (CD.clc_percepcion - CD.clc_retencion) <= $importe_max";
        }

        if (!empty($fecha_min)) {
            if (empty($fecha_max)) {
                $sql_extra .= " AND CDX.clc_generacionCereCo >= '$fecha_min'";
            } else {
                $sql_extra .= " AND (CDX.clc_generacionCereCo BETWEEN '$fecha_min 00:00:00' AND '$fecha_max 23:59:59') ";
            }
        } else if (!empty($fecha_max)) {
            $sql_extra .= " AND CDX.clc_generacionCereCo <= '$fecha_max'";
        }

        $sql_extraproveedor = "";
        if (!empty($proveedor)) {
            $sql_extraproveedor .= " WHERE CONCAT(PRO.rfc_nombre, '', PRO.rfc_paterno," .
                    " '', PRO.rfc_materno) like'$proveedor%'";
        }

        $sql = "
            SELECT 
                a.ur UR,
                a.gpo_id GPO_ID,
                a.ur_id  UR_ID,
                5 TLO_ID, #PDP
                a.clc_id CLC,
                a.clc_cereCo FOLIO,
                a.ecl_id EDO_CLC,
                MIN(TC.tcl_nombre) NOMBRE_TIPO,
                MIN(STC.stc_nombre) NOMBRE_SUBTIPO,
                UR.ur_nombre RESPONSABLE,
                MIN(a.clc_beneficiario) BENEFICIARIO,
                MIN(a.clc_concepto) CONCEPTO,
                IF(a.clc_tipoCereCo=1,'CE','CO') TIPO,
                a.importeclc IMPORTE,
                a.fecha_elaboracion FECHA_ELABORACION,
                a.cbs_id AS CARGO,
                a.bur_id DESTINO,
                ECF.ecf_clave CVE_FIN,
                CF.cfi_nombre CVE_NOMBRE,
                DATEDIFF(a.fecha_elaboracion,NOW()) DIFERENCIA,
                'PDP' MODO,
                a.rfc_id RFC,
                CONCAT(PRO.rfc_nombre,'',PRO.rfc_paterno,'',PRO.rfc_materno) NOMBREPROVEEDOR,
                a.rfc_id,
                X.ucb_id USO_CUENTA,
                MAX(a.ncp) NCP_ID,                
                sum(a.importepr) IMPORTEPR
            FROM (
                SELECT concat(CD.gpo_id,CD.ur_id) ur,
                    CD.gpo_id,CD.ur_id,CD.clc_id ,
                    CD.bur_id,
                    CD.mpc_id,
                    CD.ecl_id,
                    CD.ecf_id,
                    CD.tcl_id,
                    CD.stc_id,
                    CD.clc_beneficiario,
                    CD.clc_concepto,
                    CD.clc_tipoCereco,
                    CDX.clc_cereCo,
                    date(CDX.clc_generacionCereCo) fecha_elaboracion,
                    (CD.clc_percepcion-CD.clc_retencion) importeclc,
                    P.rfc_id,
                    MIN(PRE.ncp_id) ncp,
                    sum(ncp_percepcionNeto) importepr,
                    CBS.cbs_id
                FROM
                    " . BD_CTRL_2023 . ".scp_Clc CD
                    LEFT JOIN (
                        SELECT gpo_id, ur_id, clc_id
                        FROM " . BD_TESORERITEM . ".tem_PropuestaClc as T
                        WHERE T.pro_ejercicio = 2023 and T.usr_id =  " .$_SESSION[_S]['usr_id']. "
                    ) AS TMP  USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento VCB USING(ecf_id)
                    INNER JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar AS CDX USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_CTRL_2023 . ".scp_Presupuestal AS PRE USING(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2023 . ".vin_BancosUrProveedor AS P USING(bur_id)                    
                    INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CBS USING (cbs_id)
                    INNER JOIN " . BD_TESORERIA . ".cat_TipoRecurso TR ON CBS.ter_id = TR.ter_id
                WHERE 
                    TMP.clc_id IS NULL
                    AND CD.mpc_id = 5 
                    AND CD.tcl_id != 'E'
                    AND ant.ecl_id = 10
                    AND ant.ejr_id_clc = 2023
                    AND (TR.ter_id = 'A')
                    AND TRUNCATE(CD.clc_percepcion, 2) - TRUNCATE(CD.clc_retencion, 2) > 0
                    $sql_extra
                GROUP BY CD.gpo_id, CD.ur_id, CD.clc_id,
                 P.rfc_id, CBS.cbs_id
               
                UNION
            
                SELECT 
                    concat(CD.gpo_id,CD.ur_id)ur,
                    CD.gpo_id,CD.ur_id ,CD.clc_id,
                    CD.bur_id,
                    CD.mpc_id,
                    CD.ecl_id,
                    CD.ecf_id,
                    CD.tcl_id,
                    CD.stc_id,
                    CD.clc_beneficiario,
                    CD.clc_concepto,
                    CD.clc_tipoCereco,
                    CDX.clc_cereCo,
                    date(CDX.clc_generacionCereCo) fecha_elaboracion,
                    (CD.clc_percepcion-CD.clc_retencion) importeclc,
                    P.rfc_id,
                    '' ncp,
                    sum(ncc_percepcion)- sum(ncc_retencion) importepr,
                    CBS.cbs_id
                FROM 
                    " . BD_CTRL_2023 . ".scp_Clc CD
                    LEFT JOIN (
                        SELECT gpo_id, ur_id, clc_id
                        FROM " . BD_TESORERITEM . ".tem_PropuestaClc as T
                        WHERE T.pro_ejercicio = 2023 and T.usr_id =  " .$_SESSION[_S]['usr_id']. "
                    ) AS TMP  USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento VCB USING(ecf_id)
                    INNER JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar AS CDX USING (gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2023 . ".scp_Contable USING (gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2023 . ".vin_BancosUrProveedor AS P USING (bur_id)
                    INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CBS USING (cbs_id)
                    INNER JOIN " . BD_TESORERIA . ".cat_TipoRecurso TR ON CBS.ter_id = TR.ter_id
                WHERE 
                    TMP.clc_id IS NULL
                    AND CD.mpc_id = 5
                    AND CD.tcl_id != 'E'
                    AND ant.ecl_id = 10
                    AND ant.ejr_id_clc = 2023
                    AND  (TR.ter_id = 'A')
                    AND (CD.clc_percepcion - CD.clc_retencion)>0
                    $sql_extra
                GROUP BY CD.gpo_id, CD.ur_id, CD.clc_id,
                 P.rfc_id, CBS.cbs_id
            ) AS a 
                INNER JOIN " . BD_CTRL_2023 . ".enc_ClaveFinanciamiento AS ECF USING(ecf_id)
                INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento2023 as CF USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                INNER JOIN " . BD_SINPRES . ".cat_Proveedores as PRO using(rfc_id)
                INNER JOIN " . BD_CTRL_2023 . ".cat_UnidadResponsable AS UR USING (gpo_id,ur_id)
                INNER JOIN " . BD_CTRL_2023 . ".cat_TipoClc AS TC USING (tcl_id)
                INNER JOIN " . BD_CTRL_2023 . ".cat_SubtipoClc AS STC USING (tcl_id,stc_id)
                INNER JOIN " . BD_CTRL_2023 . ".cat_BancosUr AS X USING (bur_id)
                 " .
                (empty($sql_extraproveedor) ? "" : $sql_extraproveedor) .
                "
                GROUP BY a.gpo_id, a.ur_id, a.clc_id, a.ur,a.clc_cereCo, a.ecl_id, 
                a.clc_tipoCereco, a.importeclc, a.fecha_elaboracion,
                a.cbs_id,a.bur_id,ECF.ecf_clave,a.rfc_id

        ";
        return $sql;
    }

    function sqlDocumentosPDP($prm) {
        // @consultaMinistracionDetalleAutorizadaPdp
        $sql = "
            SELECT bdo_id,rfc_id,importeBdo,bur_id,SUM(importeDocClc) as importeDocClc
            FROM
            (
                SELECT 
                doc.bdo_id,
                b.rfc_id, (doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable - doc.bdo_retencionContable) AS importeBdo,
                MIN(b.bur_id) AS bur_id,
                SUM(b.importeBdo) AS importeDocClc,
                referencia
                    FROM             
                        (
                            SELECT 
                                c.gpo_id, c.ur_id, c.bdo_id,
                                c.rfc_id, VBP.bur_id,
                                c.ncc_referencia as referencia,
                                SUM(c.ncc_percepcion)-SUM(c.ncc_retencion) AS importeBdo
                            FROM
                                " . BD_CTRL_2023 . ".scp_Contable AS c
                                INNER JOIN " . BD_CTRL_2023 . ".vin_BancosUrProveedor as VBP USING(rfc_id)                    
                            WHERE 
                                c.gpo_id = {$prm->gpo_id} AND VBP.sts_id = 1 #1-activo,2-inactivo
                                AND c.ur_id = {$prm->ur_id}
                                AND c.clc_id = {$prm->clc_id}
                            GROUP BY gpo_id,ur_id,bdo_id,rfc_id,bur_id,referencia
                        
                            UNION ALL
                            
                            SELECT 
                                p.gpo_id, p.ur_id, p.bdo_id,
                                p.rfc_id, VBP.bur_id,
                                p.ncp_referencia as referencia,
                                ncp_percepcionNeto AS importeBdo
                            FROM 
                                " . BD_CTRL_2023 . ".scp_Presupuestal p
                                INNER JOIN " . BD_CTRL_2023 . ".vin_BancosUrProveedor as VBP USING(rfc_id)                    
                            WHERE 
                                p.gpo_id = {$prm->gpo_id} AND VBP.sts_id = 1 #1-activo,2-inactivo
                                AND p.ur_id = {$prm->ur_id}
                                AND p.clc_id = {$prm->clc_id}
                            GROUP BY gpo_id,ur_id,bdo_id,rfc_id,bur_id,referencia
                        ) AS b
                        INNER JOIN " . BD_CTRL_2023 . ".cat_BancoDocumentos AS doc USING(gpo_id, ur_id, bdo_id)
                    GROUP BY gpo_id,ur_id,bdo_id,rfc_id,referencia,importeBdo
            ) AS d
            GROUP BY bdo_id,rfc_id;
        ";

        return $sql;
    }
    
    function sqlLlenaTablaClcCadenaProductiva($prm) {
        $sql = "";
        $tipo_clc = 'E'; //Anticipos - Fondo Rotatorio
        $sql_extra = "";

        $anio = isset($this->ejercicio) ? $this->ejercicio : "";
        $estado = isset($prm->estado) ? $prm->estado : "";
        $modo = isset($prm->modo) ? $prm->modo : "";
        $tipo = isset($prm->documento) ? $prm->documento : "";
        $tipo_clc = isset($prm->tipo) ? $prm->tipo : "";
        $subtipo_clc = isset($prm->subtipo) ? $prm->subtipo : "";
        $impuesto_op = isset($prm->impuesto) ? $prm->impuesto : "";
        $clc = isset($prm->clc) ? $prm->clc : "";
        $numero = isset($prm->orden) ? $prm->orden : "";
        $ur = isset($prm->numerour) ? $prm->numerour : "";
        $ur_nombre = isset($prm->nombreur) ? $prm->nombreur : "";
        $rfc = isset($prm->rfc) ? $prm->rfc : "";
        $proveedor = isset($prm->proveedor) ? $prm->proveedor : "";
        $beneficiario = isset($prm->beneficiario) ? $prm->beneficiario : "";
        $concepto = isset($prm->concepto) ? $prm->concepto : "";
        $importe_min = isset($prm->importeMin) ? $prm->importeMin : "";
        $importe_max = isset($prm->importeMax) ? $prm->importeMax : "";
        $fecha_min = isset($prm->fechaMin) ? $prm->fechaMin : "";
        $fecha_max = isset($prm->fechaMax) ? $prm->fechaMax : "";

        switch ($tipo) { //clc_tipoCereCo
            case 1: //CERE -- OTB
                $sql_extra .= ' AND CD.clc_tipoCereCo = 1';
                break;
            case 2: //CERECO -- OP
                $sql_extra .= ' AND CD.clc_tipoCereCo = 2';
                break;
        }

        $sql_extra .= " AND CD.tcl_id != 'E' ";
        if (!empty($tipo_clc) && $tipo_clc != 'E') {
            $sql_extra .= " AND CD.tcl_id = '$tipo_clc'";
        }

        if ($subtipo_clc != '') {
            $sql_extra .= " AND CD.stc_id = $subtipo_clc";
        }

        if (!empty($numero)) {
            $sql_extra .= " AND CDX.clc_cereCo IN ($numero)";
        }

        if (!empty($ur)) {
            $sql_extra .= " AND CD.gpo_id = " . substr($ur, 0, 1) .
                    " AND CD.ur_id = " . substr($ur, 1, 2);
        }

        if (!empty($ur_nombre)) {
            $sql_extra .= " AND CD.gpo_id = " . substr($ur_nombre, 0, 1) . " AND CD.ur_id = " . substr($ur_nombre, 1, 2);
        }

        if (!empty($clc)) {
            $sql_extra .= " AND CD.clc_id IN ($clc)";
        }

        if (!empty($rfc)) {
            $sql_extra .= " AND P.rfc_id = '$rfc'";
        }

        if (!empty($beneficiario)) {
            $sql_extra .= " AND CD.clc_beneficiario = '$beneficiario'";
        }

        if (!empty($concepto)) {
            $sql_extra .= " AND CD.clc_concepto LIKE '%$concepto%'";
        }

        if (!empty($fecha_min)) {
            if (empty($fecha_max)) {
                $sql_extra .= " AND CDX.clc_generacionCereCo >= '$fecha_min'";
            } else {
                $sql_extra .= " AND (CDX.clc_generacionCereCo BETWEEN '$fecha_min 00:00:00' AND '$fecha_max 23:59:59') ";
            }
        } else if (!empty($fecha_max)) {
            $sql_extra .= " AND CDX.clc_generacionCereCo <= '$fecha_max'";
        }

        $sql_extraproveedor = "";
        if (!empty($proveedor)) {
            $sql_extraproveedor .= " WHERE CONCAT(PRO.rfc_nombre, '', PRO.rfc_paterno," .
                    " '', PRO.rfc_materno) like'$proveedor%'";
        }

        $sql = "
            SELECT 
                a.ur UR,
                a.gpo_id GPO_ID,
                a.ur_id  UR_ID,
                5 TLO_ID, #PDP
                a.clc_id CLC,
                a.clc_cereCo FOLIO,
                a.ecl_id EDO_CLC,
                MIN(TC.tcl_nombre) NOMBRE_TIPO,
                MIN(STC.stc_nombre) NOMBRE_SUBTIPO,
                UR.ur_nombre RESPONSABLE,
                MIN(a.clc_beneficiario) BENEFICIARIO,
                MIN(a.clc_concepto) CONCEPTO,
                IF(a.clc_tipoCereCo=1,'CE','CO') TIPO,
                a.importeclc IMPORTE,
                a.fecha_elaboracion FECHA_ELABORACION,
                a.cbs_id AS CARGO,
                a.bur_id DESTINO,
                ECF.ecf_clave CVE_FIN,
                CF.cfi_nombre CVE_NOMBRE,
                DATEDIFF(a.fecha_elaboracion,NOW()) DIFERENCIA,
                'CADENAS PRODUCTIVAS' MODO,
                a.rfc_id RFC,
                CONCAT(PRO.rfc_nombre,'',PRO.rfc_paterno,'',PRO.rfc_materno) NOMBREPROVEEDOR,
                a.rfc_id,
                X.ucb_id USO_CUENTA,
                MAX(a.ncp) NCP_ID,                
                sum(a.importepr) IMPORTEPR
            FROM (
                SELECT concat(CD.gpo_id,CD.ur_id) ur,
                    CD.gpo_id,CD.ur_id,CD.clc_id ,
                    CD.bur_id,
                    CD.mpc_id,
                    CD.ecl_id,
                    CD.ecf_id,
                    CD.tcl_id,
                    CD.stc_id,
                    CD.clc_beneficiario,
                    CD.clc_concepto,
                    CD.clc_tipoCereco,
                    CDX.clc_cereCo,
                    date(CDX.clc_generacionCereCo) fecha_elaboracion,
                    (CD.clc_percepcion-CD.clc_retencion) importeclc,
                    P.rfc_id,
                    MIN(PRE.ncp_id) ncp,
                    sum(ncp_percepcionNeto) importepr,
                    CBS.cbs_id
                FROM
                    " . BD_CTRL_2023 . ".scp_Clc CD
                    LEFT JOIN (
                        SELECT gpo_id, ur_id, clc_id
                        FROM " . BD_TESORERITEM . ".tem_PropuestaClc as T
                        WHERE T.pro_ejercicio = 2023 and T.usr_id =  " .$_SESSION[_S]['usr_id']. "
                    ) AS TMP  USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento VCB USING(ecf_id)
                    INNER JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar AS CDX USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_CTRL_2023 . ".scp_Presupuestal AS PRE USING(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2023 . ".vin_BancosUrProveedor AS P USING(bur_id)                    
                    INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CBS USING (cbs_id)
                    INNER JOIN " . BD_TESORERIA . ".cat_TipoRecurso TR ON CBS.ter_id = TR.ter_id
                WHERE 
                    TMP.clc_id IS NULL
                    AND CD.mpc_id = 4
                    AND CD.tcl_id != 'E'
                    AND ant.ecl_id = 10
                    AND ant.ejr_id_clc = 2023
                    AND (TR.ter_id = 'A')
                    AND TRUNCATE(CD.clc_percepcion, 2) - TRUNCATE(CD.clc_retencion, 2) > 0
                    $sql_extra
                GROUP BY CD.gpo_id, CD.ur_id, CD.clc_id,
                 P.rfc_id, CBS.cbs_id
               
                UNION
            
                SELECT 
                    concat(CD.gpo_id,CD.ur_id)ur,
                    CD.gpo_id,CD.ur_id ,CD.clc_id,
                    CD.bur_id,
                    CD.mpc_id,
                    CD.ecl_id,
                    CD.ecf_id,
                    CD.tcl_id,
                    CD.stc_id,
                    CD.clc_beneficiario,
                    CD.clc_concepto,
                    CD.clc_tipoCereco,
                    CDX.clc_cereCo,
                    date(CDX.clc_generacionCereCo) fecha_elaboracion,
                    (CD.clc_percepcion-CD.clc_retencion) importeclc,
                    P.rfc_id,
                    '' ncp,
                    sum(ncc_percepcion)-sum(ncc_retencion) importepr,
                    CBS.cbs_id
                FROM 
                    " . BD_CTRL_2023 . ".scp_Clc CD
                    LEFT JOIN (
                        SELECT gpo_id, ur_id, clc_id
                        FROM " . BD_TESORERITEM . ".tem_PropuestaClc as T
                        WHERE T.pro_ejercicio = 2023 and T.usr_id =  " .$_SESSION[_S]['usr_id']. "
                    ) AS TMP  USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento VCB USING(ecf_id)
                    INNER JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar AS CDX USING (gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2023 . ".scp_Contable USING (gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2023 . ".vin_BancosUrProveedor AS P USING (bur_id)
                    INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CBS USING (cbs_id)
                    INNER JOIN " . BD_TESORERIA . ".cat_TipoRecurso TR ON CBS.ter_id = TR.ter_id
                WHERE 
                    TMP.clc_id IS NULL
                    AND CD.mpc_id = 4
                    AND CD.tcl_id != 'E'
                    AND ant.ecl_id = 10 
                    AND ant.ejr_id_clc = 2023
                    AND (TR.ter_id = 'A')
                    AND TRUNCATE(CD.clc_percepcion, 2) - TRUNCATE(CD.clc_retencion, 2) > 0
                    $sql_extra
                GROUP BY CD.gpo_id, CD.ur_id, CD.clc_id,
                 P.rfc_id, CBS.cbs_id
            ) AS a 
                
                INNER JOIN " . BD_CTRL_2023 . ".enc_ClaveFinanciamiento AS ECF USING(ecf_id)
                INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento2023 as CF USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                INNER JOIN " . BD_SINPRES . ".cat_Proveedores as PRO using(rfc_id)
                INNER JOIN " . BD_CTRL_2023 . ".cat_UnidadResponsable AS UR USING (gpo_id,ur_id)
                INNER JOIN " . BD_CTRL_2023 . ".cat_TipoClc AS TC USING (tcl_id)
                INNER JOIN " . BD_CTRL_2023 . ".cat_SubtipoClc AS STC USING (tcl_id,stc_id)
                INNER JOIN " . BD_CTRL_2023 . ".cat_BancosUr AS X USING (bur_id)
                 " .
                (empty($sql_extraproveedor) ? "" : $sql_extraproveedor) .
                "
                GROUP BY a.gpo_id, a.ur_id, a.clc_id, a.ur,a.clc_cereCo, a.ecl_id, 
                a.clc_tipoCereco, a.importeclc, a.fecha_elaboracion,
                a.cbs_id,a.bur_id,ECF.ecf_clave,a.rfc_id
        ";
        return $sql;
    }

    function sqlDocumentosCadenaProductiva($prm) {
        $sql = "SELECT 
                doc.bdo_id, b.rfc_id, (doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable 
                - doc.bdo_retencionContable) AS importeBdo, MIN(b.bur_id) AS bur_id, sum(b.importeBdo) AS importeDocClc,
                referencia
            FROM             
                (
                    SELECT 
                        c.gpo_id, c.ur_id, c.bdo_id,
                        c.rfc_id, VBP.bur_id,
                        c.ncc_referencia as referencia,
                        (c.ncc_percepcion-c.ncc_retencion) AS importeBdo
                    FROM
                        " . BD_CTRL_2023 . ".scp_Contable AS c
                        INNER JOIN " . BD_CTRL_2023 . ".vin_BancosUrProveedor as VBP USING(rfc_id)                    
                    WHERE 
                        c.gpo_id = {$prm->gpo_id} AND VBP.sts_id = 1 #1-activo,2-inactivo
                        AND c.ur_id = {$prm->ur_id}
                        AND c.clc_id = {$prm->clc_id}
                    GROUP BY gpo_id,ur_id,bdo_id,rfc_id,bur_id,referencia,importeBdo
                
                    UNION ALL
                    
                    SELECT 
                        p.gpo_id, p.ur_id, p.bdo_id,
                        p.rfc_id, VBP.bur_id,
                        p.ncp_referencia as referencia,
                        ncp_percepcionNeto AS importeBdo
                    FROM 
                        " . BD_CTRL_2023 . ".scp_Presupuestal p
                        INNER JOIN " . BD_CTRL_2023 . ".vin_BancosUrProveedor as VBP USING(rfc_id)                    
                    WHERE 
                        p.gpo_id = {$prm->gpo_id} AND VBP.sts_id = 1 #1-activo,2-inactivo
                        AND p.ur_id = {$prm->ur_id}
                        AND p.clc_id = {$prm->clc_id}
                    GROUP BY gpo_id,ur_id,bdo_id,rfc_id,bur_id,referencia,importeBdo
                ) AS b
                INNER JOIN " . BD_CTRL_2023 . ".cat_BancoDocumentos AS doc USING(gpo_id, ur_id, bdo_id)
            GROUP BY gpo_id, ur_id, bdo_id,rfc_id,referencia
            ";

        return $sql;
    }

    function sqlLlenaTablaLineas($prm) {
        $sql = '';
        $anio = isset($prm->anio) ? $prm->anio : "";
        $estado = isset($prm->estado) ? $prm->estado : "";
        $modo = isset($prm->modo) ? $prm->modo : "";
        $tipo = isset($prm->documento) ? $prm->documento : "";
        $tipo_clc = isset($prm->tipo) ? $prm->tipo : "";
        $subtipo_clc = isset($prm->subtipo) ? $prm->subtipo : "";
        $impuesto_op = isset($prm->impuesto) ? $prm->impuesto : "";
        $clc = isset($prm->clc) ? $prm->clc : "";
        $numero = isset($prm->orden) ? $prm->orden : "";
        $ur = isset($prm->numerour) ? $prm->numerour : "";
        $ur_nombre = isset($prm->nombreur) ? $prm->nombreur : "";
        $rfc = isset($prm->rfc) ? $prm->rfc : "";
        $proveedor = isset($prm->proveedor) ? $prm->proveedor : "";
        $beneficiario = isset($prm->beneficiario) ? $prm->beneficiario : "";
        $concepto = isset($prm->concepto) ? $prm->concepto : "";
        $importe_min = isset($prm->importeMin) ? str_replace("$ ","",$prm->importeMin) : "";
        $importe_max = isset($prm->importeMax) ? str_replace("$ ","",$prm->importeMax) : "";
        $fecha_min = isset($prm->fechaMin) ? $prm->fechaMin : "";
        $fecha_max = isset($prm->fechaMax) ? $prm->fechaMax : "";

        $sql_extra = '';
        if (!empty($ur)) {
            $sql_extra .= " AND CD.gpo_id = " . substr($ur, 0, 1) . " AND CD.ur_id = " . substr($ur, 1, 2);
        }

        // MODO   => 1.TRADICIONAL, 2.-LINEA CAPTURA , 4.-CADENA PRODUCTIVA 5.-DEUDA PUBLICA 6.-2.5% //CAMBIO 2016-09-14[IMPUESTO 2.5%]
        $sql_modo = ' AND CD.mpc_id <> 3'; //MODO DE PAGO DE CLC DISTINTO A SIN FLUJO DE EFECTIVO
        switch ($modo) {
            case 1: // TRADICIONAL
            case 2: // LINEA DE CAPTURA
            case 4: // CADENAS PRODUCTIVAS
                $sql_modo = ' AND CD.mpc_id = ' . $modo;
                break;
            case 5:
                $sql_modo = " AND CD.mpc_id <> 3 AND ECF.fte_id = 'B' "; // DEUDA PUBLICA
                break;
            case 6: //CAMBIO 2016-09-14[IMPUESTO 2.5%]
                if (!empty($ur)) {
                    $sql_extra = " AND CD.gpo_id = 1 AND CD.ur_id = 16";
                }
                $sql_extra .= " AND CDX.clc_cereCo IN (0)";
                break;
        }

        if ($tipo == '2')//op
            if ($impuesto_op != '0')
            //PENDIENTE DE ANALISIS
            //$sql_extra = $this->getInfoImpuestos($impuesto_op);
                $sql_extra = " ";

        switch ($tipo) { //clc_tipoCereCo
            case 1: //CERE -- OTB
                $sql_extra .= ' AND CD.clc_tipoCereCo = 1';
                break;
            case 2: //CERECO -- OP
                $sql_extra .= ' AND CD.clc_tipoCereCo = 2';
                break;
        }

        $sql_tipo_clc = "";
        if (!empty($tipo_clc)) {
            $sql_tipo_clc = " AND CD.tcl_id = '$tipo_clc'";
        }
        if (!empty($subtipo_clc)) {
            $sql_tipo_clc .= " AND CD.stc_id = '$subtipo_clc'";
        }

        if (!empty($numero)) {
            $sql_extra .= " AND CDX.clc_cereCo IN ($numero)";
        }

        if (!empty($ur_nombre)) {
            $sql_extra .= " AND CD.gpo_id = " . substr($ur_nombre, 0, 1) . " AND CD.ur_id = " . substr($ur_nombre, 1, 2);
        }

        if (!empty($clc)) {
            $sql_extra .= " AND CD.clc_id IN ($clc)";
        }

        if (!empty($beneficiario)) {
            $sql_extra .= " AND CD.clc_beneficiario = '$beneficiario'";
        }

        if (!empty($concepto)) {
            $sql_extra .= " AND CD.clc_concepto LIKE '%$concepto%'";
        }

        if (!empty($importe_min)) {
            if (empty($importe_max)) {
                $sql_extra .= " AND (CD.clc_percepcion - CD.clc_retencion) >= $importe_min";
            } else {
                $sql_extra .= " AND ((CD.clc_percepcion - CD.clc_retencion) BETWEEN $importe_min AND $importe_max) ";
            }
        } else if (!empty($importe_max)) {
            $sql_extra .= " AND (CD.clc_percepcion - CD.clc_retencion) <= $importe_max";
        }


        if (!empty($fecha_min)) {
            if (empty($fecha_max)) {
                $sql_extra .= " AND CDX.clc_generacionCereCo >= '$fecha_min'";
            } else {
                $sql_extra .= " AND (CDX.clc_generacionCereCo BETWEEN '$fecha_min 00:00:00' AND '$fecha_max 23:59:59') ";
            }
        } else if (!empty($fecha_max)) {
            $sql_extra .= " AND CDX.clc_generacionCereCo <= '$fecha_max'";
        }

        $ordena = ' GROUP BY IDCLC,CDX.clc_generacionCereCo, FECHA_ELABORACION, DIFERENCIA, MODO, UR, GPO_ID,
            UR_ID, CLC, FOLIO, diferenciaDias, pintaDiff, TIPO, RESPONSABLE, VCB.cbs_id, DESTINO,
            CVE_FIN, lca_fechaVencimiento  ORDER BY lca_fechaVencimiento';

        $sql = "
                SELECT 
                    IDCLC,
                    FECHA_ELABORACION,
                    DIFERENCIA,
                    MODO,
                    UR, 
                    GPO_ID,
                    UR_ID,
                    CLC,
                    FOLIO,
                    bdo_id,
                    lca_folio,
                    lca_lineaCaptura,
                    lca_fechaVencimiento,
                    importeLineas,
                    diferenciaDias,
                    pintaDiff,
                    TIPO,
                    NOMBRE_TIPO,
                    NOMBRE_SUBTIPO,
                    RESPONSABLE,
                    BENEFICIARIO,
                    CONCEPTO,
                    CARGO,
                    DESTINO,
                    IMPORTE,
                    CVE_FIN
                FROM (SELECT 
                        concat(CD.gpo_id,CD.ur_id,CD.clc_id)  IDCLC,
                        SUBSTR(CDX.clc_generacionCereCo,1,10) FECHA_ELABORACION,
                        DATEDIFF(CDX.clc_generacionCereCo,NOW()) DIFERENCIA,
                        MP.mpc_nombre MODO,
                        concat(CD.gpo_id,CD.ur_id) UR,  
                        CD.gpo_id GPO_ID,
                        CD.ur_id UR_ID,
                        CD.clc_id CLC, 
                        CDX.clc_cereCo FOLIO,
                        MAX(bdo_id) bdo_id,
                        MAX(lca_folio) lca_folio,
                        MAX(lca_lineaCaptura) lca_lineaCaptura,
                        lca_fechaVencimiento,
                        sum(lca_importe) AS importeLineas,
                        DATEDIFF(lca_fechaVencimiento, date(now())) as diferenciaDias,
                        if( lca_fechaVencimiento is null,1, if (DATEDIFF(lca_fechaVencimiento, date(now())) < 6,1,0 ))as pintaDiff,
                        IF (CD.clc_tipoCereCo = 1, 'CE', 'CO') TIPO,        
                        MIN(TC.tcl_nombre) NOMBRE_TIPO,
                        MIN(STC.stc_nombre) NOMBRE_SUBTIPO,
                        UR.ur_nombre RESPONSABLE,
                        MIN(CD.clc_beneficiario) BENEFICIARIO, 
                        MIN(CD.clc_concepto) CONCEPTO,
                        VCB.cbs_id CARGO,
                        CD.bur_id DESTINO,
                        (CD.clc_percepcion - CD.clc_retencion) IMPORTE,
                        ECF.ecf_clave CVE_FIN
                    FROM
                        " . BD_CTRL_2023 . ".scp_Clc CD
                        left JOIN 
                        ( SELECT MIN(gpo_id) gpo_id,MIN(ur_id) ur_id,MIN(clc_id) clc_id,bdo_id,MIN(lca_folio) lca_folio FROM
                            ( SELECT MIN(gpo_id) gpo_id,MIN(ur_id) ur_id,MIN(clc_id) clc_id,bdo_id,MIN(ncp_referencia) as lca_folio
                                FROM " . BD_CTRL_2023 . ".scp_Presupuestal AS pro
                                GROUP BY  bdo_id,gpo_id,ur_id,clc_id
                                UNION ALL
                                SELECT MIN(gpo_id) gpo_id,MIN(ur_id) ur_id,MIN(clc_id) clc_id,bdo_id,MIN(ncc_referencia) as lca_folio
                                FROM " . BD_CTRL_2023 . ".scp_Contable AS pro
                                GROUP BY  bdo_id,gpo_id,ur_id,clc_id
                            ) AS doc GROUP BY bdo_id
                        ) as doc using (gpo_id,ur_id,clc_id)
                        INNER JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar CDX USING(gpo_id, ur_id, clc_id)
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id
                            FROM " . BD_TESORERITEM . ".tem_PropuestaClc as T
                            WHERE T.pro_ejercicio = 2023 and T.usr_id = " .$_SESSION[_S]['usr_id']. "
                        ) AS TMP  USING(gpo_id, ur_id, clc_id)
                        LEFT JOIN " . BD_TESORERIA . ".lca_LineaCaptura using(lca_folio)
                        INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento VCB USING (ecf_id)
                        INNER JOIN " . BD_CTRL_2023 . ".enc_ClaveFinanciamiento ECF using(ecf_id)
                        INNER JOIN " . BD_CTRL_2023 . ".cat_UnidadResponsable UR 
                            ON CD.gpo_id = UR.gpo_id AND CD.ur_id = UR.ur_id
                        INNER JOIN " . BD_CTRL_2023 . ".cat_TipoClc TC USING (tcl_id)
                        INNER JOIN " . BD_CTRL_2023 . ".cat_SubtipoClc STC USING (tcl_id,stc_id)
                        INNER JOIN " . BD_CTRL_2023 . ".cat_ModoPagoClc MP ON (CD.mpc_id = MP.mpc_id)
                        INNER JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CBS USING (cbs_id)
                        INNER JOIN " . BD_TESORERIA . ".cat_TipoRecurso TR ON CBS.ter_id = TR.ter_id
                    WHERE 
                        CD.ecl_id = 10 AND TR.ter_id = 'A' AND TMP.clc_id IS NULL
                        #AND (CD.gpo_id, CD.ur_id) != ((9,1))
                        AND TRUNCATE(CD.clc_percepcion, 2) - TRUNCATE(CD.clc_retencion, 2) > 0 " .
                        $sql_tipo_clc .
                        $sql_modo .
                        $sql_extra .
                        $ordena."
                ) as a
                GROUP BY IDCLC, FECHA_ELABORACION, DIFERENCIA, MODO, UR, GPO_ID,
                    UR_ID, CLC, FOLIO, diferenciaDias, pintaDiff, TIPO, RESPONSABLE, 
                    CARGO, DESTINO,CVE_FIN, lca_fechaVencimiento
                ORDER BY lca_fechaVencimiento";
        return $sql;
    }
}