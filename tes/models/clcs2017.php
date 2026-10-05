<?php

class clcs2017
{
    public function __construct()
    {
        $ejercicio=2017;
    }

    public function consultaFolios17($linea)
    {
        $sql = 'SELECT MAX(lot_ejercicio) AS lot_ejercicio,c.clc_tipoCereCo,cx.clc_cereCo,c.ecl_id 
            FROM ' . BD_TESORERIA . '.vin_LoteDocumentoLineaCaptura
            INNER JOIN ' . BD_CTRL_2017 . '.scp_Clc as c using (gpo_id,ur_id,clc_id) 
            INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ant using (gpo_id,ur_id,clc_id) 
            INNER JOIN ' . BD_CTRL_2017 . '.scp_ClcAuxiliar as cx using (gpo_id,ur_id,clc_id)
            WHERE lca_id=' . $linea . ' AND ant.ejr_id_clc = 2017
            group by c.gpo_id,c.ur_id,c.clc_id,cx.clc_cereco
        ;';
        return $sql;
    }

    public function getDetLayoutLc2017($laiD, $tipo, $ejercicio)
    {
        $sql='select 
            CONCAT( IF ( vl.fpa_id = 1, "T", IF (vl.fpa_id = 2, "C", "L") ), LPAD(vl.msc_id, 2, "0"), LPAD(vl.lot_id, 4, "0"), SUBSTR(vl.lot_ejercicio, 3) ) AS layDet_lote, 
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
            ' . BD_TESORERIA . '.vin_LoteLayout as vl
            inner join ' . BD_TESORERIA . '.lot_LoteClc as lc using (fpa_id,msc_id,lot_ejercicio,lot_id)
            inner join ' . BD_TESORERIA . '.lot_Layout using (lay_id)
            inner join ' . BD_TESORERIA . '.lot_LayoutArchivo using (lay_id)
            inner join ' . BD_TESORERIA . '.lot_LayoutArchivoDetalle ld using (lay_id)
            inner join ' . BD_TESORERIA . '.lca_LineaCaptura as lcp on lcp.lca_id=ld.lad_referenciaAPagar
            inner join ' . BD_CTRL_2017 . '.scp_Clc as c using (gpo_id,ur_id,clc_id)
            inner join ' . BD_CTRL_2017 . '.cat_UnidadResponsable as ur on lc.gpo_id=ur.gpo_id and lc.ur_id=ur.ur_id

            where lay_id='.$laiD.' and tla_id='.$tipo.' and lot_ejercicio='.$ejercicio.';
            ';
        return $sql;
    }

    public function getDetalleLoteLc2017($lotes)
    {
        $sql="SELECT 
            CONCAT(lc.gpo_id, lc.ur_id) AS ur,
            lc.clc_id,
            ecf_id,
            cv.cov_cuentaReferencia AS ctaAbono,
            tlo_id,
            bsf_id AS ctaCargo,
            lcap.lca_importe AS importe,
            SUBSTR(lot_ejercicio, 3) AS anio,
            5 AS tipo,
            lcap.lca_id AS orden,
            lcap.lca_lineaCaptura AS linea_captura,
            CURDATE() AS fecha,
            c.clc_concepto AS concepto,
            c.clc_beneficiario AS benef,
            elo_id,
            lcap.elc_id as estadolc,
            c.ecl_id
        FROM
            " . BD_TESORERIA . ".lot_Lote AS l
                INNER JOIN
            " . BD_TESORERIA . ".lot_LoteClc AS lc 
            USING (fpa_id , msc_id , lot_ejercicio , lot_id)
                INNER JOIN
            " . BD_TESORERIA . ".lot_LoteDocumento ld 
            USING (fpa_id , msc_id , lot_ejercicio , lot_id , gpo_id , ur_id , clc_id)
                INNER JOIN
            " . BD_TESORERIA . ".vin_LoteDocumentoLineaCaptura vl 
            USING (fpa_id , msc_id , lot_ejercicio , lot_id , gpo_id , ur_id , clc_id)
                INNER JOIN
            " . BD_TESORERIA . ".lca_LineaCaptura AS lcap ON vl.lca_id = lcap.lca_id
                INNER JOIN
            " . BD_TESORERIA . ".cat_Convenio as cv on cv.cov_id= lcap.cov_id
                INNER JOIN
            " . BD_CTRL_2017 . ".scp_Clc AS c USING (gpo_id , ur_id , clc_id)
                INNER JOIN
            " . BD_CTRL_2017 . ".scp_ClcAuxiliar lca USING (gpo_id , ur_id , clc_id)
                INNER JOIN 
            " . BD_CTRLANT . ".scp_Clc as ca using (gpo_id,ur_id,clc_id)    
                INNER JOIN
            " . BD_CTRL_2017 . ".enc_ClaveFinanciamiento USING (ecf_id)
                INNER JOIN
            " . BD_CTRL_2017 . ".enc_BancosSefinCveFin USING (ecf_id)
        WHERE
            (l.fpa_id , l.msc_id, l.lot_ejercicio, l.lot_id) IN (
            ".$lotes.") AND ca.ecl_id=15 AND ca.ejr_id_clc=2017
        GROUP BY lcap.lca_id order by lca.clc_CereCo
        ;";
        return $sql;
    }
    function getDetLayoutPdp2017($laiD,$tipo,$ejercicio){
        $sql=' select 
                    CONCAT( IF ( vl.fpa_id = 1, "T", IF (vl.fpa_id = 2, "C", "L") ), LPAD(vl.msc_id, 2, "0"), LPAD(vl.lot_id, 4, "0"), SUBSTR(vl.lot_ejercicio, 3) ) AS layDet_lote, 
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
             inner join ' . BD_CTRL_2017 . '.cat_BancoDocumentos as ax on ax.bdo_id=ld.lad_referenciaAPagar
             inner join ' . BD_TESORERIA . '.lot_LoteClc as lc on lc.fpa_id=vl.fpa_id and lc.msc_id=vl.msc_id and vl.lot_ejercicio=lc.lot_ejercicio and vl.lot_id=lc.lot_id
             inner join ' . BD_CTRL_2017 . '.scp_Clc as c on lc.gpo_id=c.gpo_id and lc.ur_id=c.ur_id and lc.clc_id=c.clc_id
             inner join ' . BD_CTRL_2017 . '.cat_UnidadResponsable as ur on c.gpo_id=ur.gpo_id and c.ur_id=ur.ur_id
             where lay_id='.$laiD.' and tla_id='.$tipo.' and vl.lot_ejercicio='.$ejercicio.';
             ';
        return $sql;
    }

    public function getDetLayout2017($laiD, $tipo, $ejercicio)
    {
        $sql='   select 
            CONCAT( IF ( vl.fpa_id = 1, "T", IF (vl.fpa_id = 2, "C", "L") ), LPAD(vl.msc_id, 2, "0"), LPAD(vl.lot_id, 4, "0"), SUBSTR(vl.lot_ejercicio, 3) ) AS layDet_lote, 
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
             inner join ' . BD_TESORERIA . '.lot_Layout using (lay_id  )
             inner join ' . BD_TESORERIA . '.lot_LayoutArchivo using (lay_id)
             inner join ' . BD_TESORERIA . '.lot_LayoutArchivoDetalle ld using (lay_id)
             inner join ' . BD_CTRL_2017 . '.scp_ClcAuxiliar as ax on ax.clc_cereco=ld.lad_referenciaAPagar
             inner join ' . BD_CTRL_2017 . '.scp_Clc as c on ax.gpo_id=c.gpo_id and ax.ur_id=c.ur_id and ax.clc_id=c.clc_id 
             inner join ' . BD_CTRL_2017 . '.cat_UnidadResponsable as ur on c.gpo_id=ur.gpo_id and c.ur_id=ur.ur_id
             where lay_id='.$laiD.' and tla_id='.$tipo.' and lot_ejercicio='.$ejercicio.';';
        return $sql;
    }

    public function DetalleLoteLayout2017($lot_id, $fpa_id, $msc_id, $lot_ejercicio)
    {
        $sql = 'SELECT CONCAT( IF ( l.fpa_id = 1, "T", IF (l.fpa_id = 2, "C", "L") ), LPAD(l.msc_id, 2, "0"), LPAD(l.lot_id, 4, "0"), SUBSTR(l.lot_ejercicio, 3) ) AS lotDet,
                    concat(dl.gpo_id,dl.ur_id) AS lotDet_ur,
                    LE.loe_clave as lote_egreso,
                    dl.clc_id AS lotDet_clc,
                    IF(clc.clc_tipoCereCo=1,"OTB","OP") as lotDet_tipo,
                    x.clc_cereCo as lotDet_folio,
                    format(dl.lot_importeClc,2) AS lotDet_importe,
                    ur_nombre as lotDet_responsable,
                    clc.clc_beneficiario AS lotDet_beneficiario,
                    iba_nombre as banco,
                    cbs.bsf_id as lotDet_CtaCargo,
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
            INNER JOIN ' . BD_CTRL_2017 . '.scp_Clc AS clc USING (gpo_id, ur_id, clc_id)
            INNER JOIN ' . BD_CTRL_2017 . '.scp_ClcAuxiliar as x using (gpo_id,ur_id,clc_id)
            INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ca using (gpo_id,ur_id,clc_id)
            INNER JOIN ' . BD_CTRL_2017 . '.scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
            INNER JOIN ' . BD_CTRL_2017 . '.scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
            INNER JOIN ' . BD_CTRL_2017 . '.cat_UnidadResponsable using (gpo_id,ur_id)
            INNER JOIN ' . BD_CTRL_2017 . '.enc_ClaveFinanciamiento as cvf using (ecf_id)
            INNER JOIN ' . BD_CTRL_2017 . '.enc_BancosSefinCveFin as cbs using (ecf_id)
            INNER JOIN ' . BD_CTRL_2017 . '.scp_BancosSefin using (bsf_id)
            INNER JOIN ' . BD_CTRL_2017 . '.cat_InstitucionesBancarias USING (iba_id)
            WHERE
                    dl.lot_id =' . $lot_id . "
                    AND dl.fpa_id=" . $fpa_id . "
                    AND dl.msc_id=" . $msc_id . "
                    AND dl.lot_ejercicio=" . $lot_ejercicio ." 
                    AND ca.ecl_id=11 
                    AND ca.ejr_id_clc = 2017 
                    AND l.lot_ejr = " . APP_EJERCICIO . ";";
        return $sql;
    }    
    function  getMovimientosAutorizados($prm)
    {
        $lotePago = (isset($prm->lotePago)) ? strtoupper($prm->lotePago): '';
        $fechaMinPago = (isset($prm->fechaMinPago)) ? $prm->fechaMinPago: '';
        $fechaMaxPago = (isset($prm->fechaMaxPago)) ? $prm->fechaMaxPago: '';
        $fechaMinAut = (isset($prm->fechaMinAut)) ? $prm->fechaMinAut: '';
        $fechaMaxAut = (isset($prm->fechaMaxAut)) ? $prm->fechaMaxAut: '';
        $tipo=(isset($prm->tipo))?$prm->tipo:"";
        $ejercicio=(isset($prm->ejercicio))?$prm->ejercicio:'';
        
        if($tipo==6 || $tipo==7){
             $sql = '
                    SELECT
                            l.lot_id,
                            tl.tlo_nombre AS lot_tipo,
                            l.elo_id,
                            l.fpa_id,
                            l.msc_id,
                            l.lot_ejercicio,
                            fpa_nombre AS lot_modo,
                            l.lot_fechaAutorizacion,
                            l.lot_fechaPago,
                            l.lot_clave AS lot,
                            IFNULL(SUM(ld.lom_importe), 0) AS lot_total,
                            el.elo_nombre AS lot_edo,
                            tl.tlo_id as tip

                    FROM
                            ' . BD_TESORERIA . '.lot_Lote AS l
                    INNER JOIN ' . BD_TESORERIA . '.lot_PropuestaManual AS ld USING (fpa_id, msc_id, lot_ejercicio, lot_id,elo_id)
                    INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tl USING (tlo_id)
                    INNER JOIN ' . BD_TESORERIA . '.cat_EstadoLote AS el USING (elo_id)
                    INNER JOIN ' . BD_TESORERIA . '.cat_FormaPago using (fpa_id)
                    
                    WHERE el.elo_id=7  ';             
       }else{
            
            $sql = '
                    SELECT
                            l.lot_id,
                            tl.tlo_nombre AS lot_tipo,
                            l.elo_id,
                            l.fpa_id,
                            l.msc_id,
                            l.lot_ejercicio,
                            fpa_nombre  AS lot_modo,
                            l.lot_fechaAutorizacion,
                            l.lot_fechaPago,
                            l.lot_clave AS lot,
                            IFNULL(SUM(ld.lot_importeClc), 0) AS lot_total,
                            el.elo_nombre AS lot_edo,
                            tl.tlo_id as tip

                    FROM
                    ' . BD_TESORERIA . '.lot_Lote AS l
                    INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS ld USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                    INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tl USING (tlo_id)
                    INNER JOIN ' . BD_TESORERIA . '.cat_EstadoLote AS el USING (elo_id)
                    INNER JOIN ' . BD_TESORERIA . '.cat_FormaPago using (fpa_id)
                  
                    WHERE el.elo_id=7 ';

        }
            if (!empty($lotePago)){
                $lotePago = strtoupper($lotePago);
                $nlotes = explode(",", $lotePago);
                $findLotes = '';
                $lote = '';
                $fpa = 0;
                foreach ($nlotes as $value) {
                    $fpa_id = substr($value, 0, 1);
                    switch ($fpa_id) {
                        case 'T':
                            $fpa = 1;
                            break;
                        case 'C':
                            $fpa = 2;
                            break;
                        case 'L':
                            $fpa = 3;
                        default:
                            //sdf
                            break;
                    }
                    $mes = substr($value, 1, 2);
                    $lot = substr($value, 3, 4);
                    $ejercicio = substr($value, 7, 2);
                    $ejercicio = "20" . $ejercicio;
                    $lote .= "(" . $fpa . "," . $mes . "," . $ejercicio . "," . $lot . "),";
                }
                
                $findLotes = substr($lote, 0, -1);

                $sql .= "  AND (l.fpa_id,l.msc_id,l.lot_ejercicio,l.lot_id) "
                    . "in ( " . $findLotes . ")";
                       
            }
            if(!empty($fechaMinAut)){
                $fechaMinAut = DateTime::createFromFormat('d/m/Y', $fechaMinAut);
                    $sql .= ' AND l.lot_fechaAutorizacion >= "' . $fechaMinAut->format('Y-m-d'). '"';
            }
            if(!empty($tipo)){
                $sql.='AND l.tlo_id='.$tipo;
            } else {
                $sql.='AND l.tlo_id=1';
            } 
            if(!empty($fechaMaxAut)){
                $fechaMaxAut = DateTime::createFromFormat('d/m/Y', $fechaMaxAut);
                    $sql .= '
                            AND l.lot_fechaAutorizacion <= "' . $fechaMaxAut->format('Y-m-d') . '"';
            }
            if(!empty($fechaMinPago)){
                $fechaMinPago = DateTime::createFromFormat('d/m/Y', $fechaMinPago);
                    $sql .= '
                            AND l.lot_fechaPago >= "' . $fechaMinPago->format('Y-m-d') . '"';
            }
            if(!empty($fechaMaxPago)){
                  $fechaMaxPago = DateTime::createFromFormat('d/m/Y', $fechaMaxPago);
                    $sql .= '
                            AND l.lot_fechaPago <= "' . $fechaMaxPago->format('Y-m-d') . '"';
            }
            if(!empty($ejercicio)){
                $anioEjercicio = [2026,2025,2024,2023,2022,2021,2020,2019,2018,2017];
                $ejercicio = $anioEjercicio[$prm->ejercicio-1];
                $sql.=" and l.lot_ejercicio =".$ejercicio;
            }
            
            $sql .= '
                    AND l.lot_ejr = ' . APP_EJERCICIO . '
                    GROUP BY
                            l.lot_id, l.fpa_id, l.msc_id, l.lot_ejercicio
            ';
            
    /* Se arma la consulta /*/
        return $sql;
    }
    /*
    *   funcion getMovimientosAutorizadosParaAutorizarLotesEspeciales()
    *   Obtiene todos los movimientos autorizados para autorizar los lotes especiales
    */
    function  getMovimientosAutorizadosALE($prm)
    {
        $lotePago = (isset($prm->lotePago)) ? strtoupper($prm->lotePago): '';
        $fechaMinPago = (isset($prm->fechaMinPago)) ? $prm->fechaMinPago: '';
        $fechaMaxPago = (isset($prm->fechaMaxPago)) ? $prm->fechaMaxPago: '';
        $fechaMinAut = (isset($prm->fechaMinAut)) ? $prm->fechaMinAut: '';
        $fechaMaxAut = (isset($prm->fechaMaxAut)) ? $prm->fechaMaxAut: '';
        $tipo=(isset($prm->tipo))?$prm->tipo:"";
        $ejercicio=(isset($prm->ejercicio))?$prm->ejercicio:'';
        $banco=(isset($prm->banco))?$prm->banco:'';
        $sqlExtra = '';
       
        $anioEjercicio = [2026,2025,2024,2023,2022,2021,2020,2019,2018,2017];
        $ejercicio = $anioEjercicio[$prm->ejercicio-1];

        if($tipo==6 || $tipo==7){
             $sql = '
                    SELECT
                            l.lot_id,
                            tl.tlo_nombre AS lot_tipo,
                            l.elo_id,
                            l.fpa_id,
                            l.msc_id,
                            l.lot_ejercicio,
                            fpa_nombre AS lot_modo,
                            l.lot_fechaAutorizacion,
                            l.lot_fechaPago,
                            l.lot_clave AS lot,
                            IFNULL(SUM(ld.lom_importe), 0) AS lot_total,
                            el.elo_nombre AS lot_edo,
                            tl.tlo_id as tip

                    FROM
                            ' . BD_TESORERIA . '.lot_Lote AS l
                    INNER JOIN ' . BD_TESORERIA . '.lot_PropuestaManual AS ld USING (fpa_id, msc_id, lot_ejercicio, lot_id,elo_id)
                    INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tl USING (tlo_id)
                    INNER JOIN ' . BD_TESORERIA . '.cat_EstadoLote AS el USING (elo_id)
                    INNER JOIN ' . BD_TESORERIA . '.cat_FormaPago using (fpa_id)
                    
                    WHERE el.elo_id=7  '; 
            if(!empty($banco)){
                $sql.=' AND ld.cbs_id LIKE "'.$banco.'%" ';
            }else{
                $sql.=' AND (ld.cbs_id LIKE "132%" OR ld.cbs_id LIKE "102%" OR ld.cbs_id LIKE "042%") ';
            }
        }else{
            $sql = '
                    SELECT
                            l.lot_id,
                            tl.tlo_nombre AS lot_tipo,
                            l.elo_id,
                            l.fpa_id,
                            l.msc_id,
                            l.lot_ejercicio,
                            fpa_nombre  AS lot_modo,
                            l.lot_fechaAutorizacion,
                            l.lot_fechaPago,
                            l.lot_clave AS lot,
                            IFNULL(SUM(ld.lot_importeClc), 0) AS lot_total,
                            el.elo_nombre AS lot_edo,
                            tl.tlo_id as tip
                    FROM
                    ' . BD_TESORERIA . '.lot_Lote AS l
                    INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc AS ld USING (fpa_id, msc_id, lot_ejercicio, lot_id)
                    {scp}
                    INNER JOIN ' . BD_TESORERIA . '.cat_TipoLote AS tl USING (tlo_id)
                    INNER JOIN ' . BD_TESORERIA . '.cat_EstadoLote AS el USING (elo_id)
                    INNER JOIN ' . BD_TESORERIA . '.cat_FormaPago using (fpa_id)
                  
                    WHERE el.elo_id=7 ';
            if ($ejercicio == 2017) {
                $sqlExtra='
                    INNER JOIN (
                        SELECT v.cbs_id,s.gpo_id, s.ur_id, s.clc_id FROM ' . BD_CTRL_2017 . '.scp_Clc s
                            INNER JOIN ' . BD_CTRL_2017 . '.scp_ClcAuxiliar USING(gpo_id,ur_id, clc_id)
                            INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ca USING (gpo_id,ur_id,clc_id)
                            INNER JOIN ' . BD_CTRL_2017 . '.enc_BancosSefinCveFin as cbs using (ecf_id)
                        WHERE ca.ejr_id_clc = 2017 AND ca.ecl_id=15
                    ) AS scp ON ld.gpo_id=scp.gpo_id AND ld.ur_id=scp.ur_id AND ld.clc_id=scp.clc_id
                ';
            } elseif ($ejercicio == 2018) {
                $sqlExtra='
                    INNER JOIN (
                        SELECT v.cbs_id,s.gpo_id, s.ur_id, s.clc_id FROM ' . BD_CTRL_2018 . '.scp_Clc s
                            INNER JOIN ' . BD_CTRL_2018 . '.scp_ClcAuxiliar USING(gpo_id,ur_id, clc_id)
                            INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ca USING (gpo_id,ur_id,clc_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento as v USING (ecf_id)
                        WHERE ca.ejr_id_clc = 2018 AND ca.ecl_id=15
                    ) AS scp ON ld.gpo_id=scp.gpo_id AND ld.ur_id=scp.ur_id AND ld.clc_id=scp.clc_id
                ';
            } elseif ($ejercicio == 2019) {
                $sqlExtra='
                    INNER JOIN (
                        SELECT v.cbs_id,s.gpo_id, s.ur_id, s.clc_id FROM ' . BD_CTRL_2019 . '.scp_Clc s
                            INNER JOIN ' . BD_CTRL_2019 . '.scp_ClcAuxiliar USING(gpo_id,ur_id, clc_id)
                            INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ca USING (gpo_id,ur_id,clc_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento as v USING (ecf_id)
                        WHERE ca.ejr_id_clc = 2019 AND ca.ecl_id=15
                    ) AS scp ON ld.gpo_id=scp.gpo_id AND ld.ur_id=scp.ur_id AND ld.clc_id=scp.clc_id
                ';
            } elseif ($ejercicio == 2020) {
                $sqlExtra='
                    INNER JOIN (
                        SELECT v.cbs_id,s.gpo_id, s.ur_id, s.clc_id FROM ' . BD_CTRL_2020 . '.scp_Clc s
                            INNER JOIN ' . BD_CTRL_2020 . '.scp_ClcAuxiliar USING(gpo_id,ur_id, clc_id)
                            INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ca USING (gpo_id,ur_id,clc_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento as v USING (ecf_id)
                        WHERE ca.ejr_id_clc = 2020 AND ca.ecl_id=15
                    ) AS scp ON ld.gpo_id=scp.gpo_id AND ld.ur_id=scp.ur_id AND ld.clc_id=scp.clc_id
                ';
            } elseif ($ejercicio == 2021) {
                $sqlExtra='
                    INNER JOIN (
                        SELECT v.cbs_id,s.gpo_id, s.ur_id, s.clc_id FROM ' . BD_CTRL_2021 . '.scp_Clc s
                            INNER JOIN ' . BD_CTRL_2021 . '.scp_ClcAuxiliar USING(gpo_id,ur_id, clc_id)
                            INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ca USING (gpo_id,ur_id,clc_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento as v USING (ecf_id)
                        WHERE ca.ejr_id_clc = 2021 AND ca.ecl_id=15
                    ) AS scp ON ld.gpo_id=scp.gpo_id AND ld.ur_id=scp.ur_id AND ld.clc_id=scp.clc_id
                ';
            } elseif ($ejercicio == 2022) {
                $sqlExtra='
                    INNER JOIN (
                        SELECT v.cbs_id,s.gpo_id, s.ur_id, s.clc_id FROM ' . BD_CTRL_2022 . '.scp_Clc s
                            INNER JOIN ' . BD_CTRL_2022 . '.scp_ClcAuxiliar USING(gpo_id,ur_id, clc_id)
                            INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ca USING (gpo_id,ur_id,clc_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento as v USING (ecf_id)
                        WHERE ca.ejr_id_clc = 2022 AND ca.ecl_id=15
                    ) AS scp ON ld.gpo_id=scp.gpo_id AND ld.ur_id=scp.ur_id AND ld.clc_id=scp.clc_id
                ';
            } elseif ($ejercicio == 2023) {
                $sqlExtra='
                    INNER JOIN (
                        SELECT v.cbs_id,s.gpo_id, s.ur_id, s.clc_id FROM ' . BD_CTRL_2023 . '.scp_Clc s
                            INNER JOIN ' . BD_CTRL_2023 . '.scp_ClcAuxiliar USING(gpo_id,ur_id, clc_id)
                            INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ca USING (gpo_id,ur_id,clc_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento as v USING (ecf_id)
                        WHERE ca.ejr_id_clc = 2023 AND ca.ecl_id=15
                    ) AS scp ON ld.gpo_id=scp.gpo_id AND ld.ur_id=scp.ur_id AND ld.clc_id=scp.clc_id
                ';
            } elseif ($ejercicio == 2024) {
                $sqlExtra='
                    INNER JOIN (
                        SELECT v.cbs_id,s.gpo_id, s.ur_id, s.clc_id FROM ' . BD_CTRL_2024 . '.scp_Clc s
                            INNER JOIN ' . BD_CTRL_2024 . '.scp_ClcAuxiliar USING(gpo_id,ur_id, clc_id)
                            INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ca USING (gpo_id,ur_id,clc_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento as v USING (ecf_id)
                        WHERE ca.ejr_id_clc = 2024 AND ca.ecl_id=15
                    ) AS scp ON ld.gpo_id=scp.gpo_id AND ld.ur_id=scp.ur_id AND ld.clc_id=scp.clc_id
                ';
            } elseif ($ejercicio == 2025) {
                $sqlExtra='
                    INNER JOIN (
                        SELECT v.cbs_id,s.gpo_id, s.ur_id, s.clc_id FROM ' . BD_CTRL_2025 . '.scp_Clc s
                            INNER JOIN ' . BD_CTRL_2025 . '.scp_ClcAuxiliar USING(gpo_id,ur_id, clc_id)
                            INNER JOIN ' . BD_CTRLANT . '.scp_Clc as ca USING (gpo_id,ur_id,clc_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento as v USING (ecf_id)
                        WHERE ca.ejr_id_clc = 2025 AND ca.ecl_id=15
                    ) AS scp ON ld.gpo_id=scp.gpo_id AND ld.ur_id=scp.ur_id AND ld.clc_id=scp.clc_id
                ';
            } elseif ($ejercicio == 2026) { // Ejercicio actual
                $sqlExtra='
                    INNER JOIN (
                        SELECT v.cbs_id,s.gpo_id, s.ur_id, s.clc_id FROM ' . BD_CTRL . '.scp_Clc s
                            INNER JOIN ' . BD_CTRL . '.scp_ClcAuxiliar aux USING(gpo_id,ur_id, clc_id)
                            INNER JOIN ' . BD_TESORERIA . '.vin_SefinClaveFinanciamiento as v USING (ecf_id)
                        WHERE aux.ecl_id=15
                    ) AS scp ON ld.gpo_id=scp.gpo_id AND ld.ur_id=scp.ur_id AND ld.clc_id=scp.clc_id
                ';
            }
            $sql=str_replace('{scp}', $sqlExtra, $sql);

            if(!empty($banco)){
                $sql.=' AND scp.cbs_id LIKE "'.$banco.'%" ';
            }else{
                $sql.=' AND (scp.cbs_id LIKE "132%" OR scp.cbs_id LIKE "102%" OR scp.cbs_id LIKE "042%") ';
            }
        }
        if (!empty($lotePago)){
            $lotePago = strtoupper($lotePago);
            $nlotes = explode(",", $lotePago);
            $findLotes = '';
            $lote = '';
            $fpa = 0;
            foreach ($nlotes as $value) {
                $fpa_id = substr($value, 0, 1);
                switch ($fpa_id) {
                    case 'T':
                        $fpa = 1;
                        break;
                    case 'C':
                        $fpa = 2;
                        break;
                    case 'L':
                        $fpa = 3;
                    default:
                        //sdf
                        break;
                }
                $mes = substr($value, 1, 2);
                $lot = substr($value, 3, 4);
                $ejercicio = substr($value, 7, 2);
                $ejercicio = "20" . $ejercicio;
                $lote .= "(" . $fpa . "," . $mes . "," . $ejercicio . "," . $lot . "),";
            }
            
            $findLotes = substr($lote, 0, -1);

            $sql .= "  AND (l.fpa_id,l.msc_id,l.lot_ejercicio,l.lot_id) "
                . "in ( " . $findLotes . ")";
                   
        }
        if(!empty($fechaMinAut)){
            $fechaMinAut = DateTime::createFromFormat('d/m/Y', $fechaMinAut);
                $sql .= ' AND l.lot_fechaAutorizacion >= "' . $fechaMinAut->format('Y-m-d'). '"';
        }
        if(!empty($tipo)){
            $sql.='AND l.tlo_id='.$tipo;
        } else {
            $sql.='AND l.tlo_id=1';
        } 
        if(!empty($fechaMaxAut)){
            $fechaMaxAut = DateTime::createFromFormat('d/m/Y', $fechaMaxAut);
                $sql .= '
                        AND l.lot_fechaAutorizacion <= "' . $fechaMaxAut->format('Y-m-d') . '"';
        }
        if(!empty($fechaMinPago)){
            $fechaMinPago = DateTime::createFromFormat('d/m/Y', $fechaMinPago);
                $sql .= '
                        AND l.lot_fechaPago >= "' . $fechaMinPago->format('Y-m-d') . '"';
        }
        if(!empty($fechaMaxPago)){
              $fechaMaxPago = DateTime::createFromFormat('d/m/Y', $fechaMaxPago);
                $sql .= '
                        AND l.lot_fechaPago <= "' . $fechaMaxPago->format('Y-m-d') . '"';
        }
        if(!empty($ejercicio)){
            $sql.=" and l.lot_ejercicio =".$ejercicio;
            
        }
        
        $sql .= '
                AND l.lot_ejr = ' . APP_EJERCICIO . '
                GROUP BY
                        l.lot_id, l.fpa_id, l.msc_id, l.lot_ejercicio
        ';
        return $sql;
    }
    function getDetLotePdp2017($lotes){
        $sql="SELECT
            CONCAT(c.gpo_id, c.ur_id) ur_clave,
            c.clc_id AS clc,
            cx.clc_cereCo AS orden,
            CURDATE() AS fecha,
            SUBSTR(l.lot_ejercicio,3)as anio,
            3 as tipo,
            ld.bdo_id as documento_id,
            doc.bdo_referencia as documento,
            bsf.bsf_id AS ctaCargo,
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
        INNER JOIN " . BD_CTRL_2017 . ".cat_BancoDocumentos AS doc USING (gpo_id,ur_id, bdo_id)
        INNER JOIN " . BD_CTRL_2017 . ".scp_Clc AS c USING (gpo_id, ur_id, clc_id)
        inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id)
        INNER JOIN " . BD_CTRL_2017 . ".scp_ClcAuxiliar AS cx USING (gpo_id, ur_id, clc_id)
        INNER JOIN " . BD_CTRL_2017 . ".cat_UnidadResponsable USING (gpo_id, ur_id)
        INNER JOIN " . BD_CTRL_2017 . ".enc_BancosSefinCveFin bsf USING (ecf_id)
        INNER JOIN " . BD_CTRL_2017 . ".vin_BancosUrProveedor AS vpr ON c.bur_id = vpr.bur_id
        AND doc.rfc_id = vpr.rfc_id
        WHERE
            (
                l.fpa_id,
                l.msc_id,
                l.lot_ejercicio,
                l.lot_id
            ) IN (".$lotes.") and ant.ecl_id=11
            AND ant.ejr_id_clc=2017 AND l.lot_ejr = " . APP_EJERCICIO . "
        GROUP BY
            doc.bdo_id;
        ";
     return $sql;
    }
    function getDetalleLote2017($lotes){
        $sql="SELECT concat(lc.gpo_id,lc.ur_id) as ur,lc.clc_id ,
            ecf_id,c.bur_id as ctaAbono,tlo_id,bsf_id as ctaCargo,
                lot_importeClc as importe ,
                SUBSTR(lot_ejercicio,3)as anio,
                c.clc_tipoCereCo AS tipo,
                lca.clc_cereCo as orden,
                CURDATE() AS fecha,
                c.clc_concepto as concepto,
                c.clc_beneficiario as benef,
                elo_id
                FROM " . BD_TESORERIA . ".lot_Lote  as l
                inner join " . BD_TESORERIA . ".lot_LoteClc as lc using (fpa_id,msc_id,lot_ejercicio,lot_id)
                inner join " . BD_CTRL_2017 . ".scp_Clc as c using (gpo_id,ur_id,clc_id)
                inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id)
                inner join " . BD_CTRL_2017 . ".scp_ClcAuxiliar as lca on lca.gpo_id=c.gpo_id and lca.ur_id=c.ur_id and lca.clc_id=c.clc_id
                inner join " . BD_CTRL_2017 . ".enc_ClaveFinanciamiento using (ecf_id)
                inner join " . BD_CTRL_2017 . ".enc_BancosSefinCveFin using (ecf_id)
                where (l.fpa_id,l.msc_id,l.lot_ejercicio,l.lot_id) in (
                ".$lotes.")  
                AND ant.ecl_id=11
                AND ant.ejr_id_clc = 2017
                AND l.lot_ejr = " . APP_EJERCICIO . "
                ;";
        return $sql;
    }
    function sqlLlenaTablaClc($prm)
    {
      $sql = '';
      $anio =         isset($prm->anio) ? $prm->anio:"";
      $estado =       isset($prm->estado) ? $prm->estado:"";
      $modo =         isset($prm->modo) ? $prm->modo:"";
      $tipo =         isset($prm->documento) ? $prm->documento:"";
      $tipo_clc =     isset($prm->tipo) ? $prm->tipo:"";
      $subtipo_clc =  isset($prm->subtipo) ? $prm->subtipo:"";
      $impuesto_op =  isset($prm->impuesto) ? $prm->impuesto:"";
      $clc =          isset($prm->clc) ? $prm->clc:"";
      $numero =       isset($prm->orden) ? $prm->orden:"";
      $ur =           isset($prm->numerour) ? $prm->numerour:"";
      $ur_nombre =    isset($prm->nombreur) ? $prm->nombreur:"";
      $rfc =          isset($prm->rfc) ? $prm->rfc:"";
      $proveedor =    isset($prm->proveedor) ? $prm->proveedor:"";
      $beneficiario = isset($prm->beneficiario) ? $prm->beneficiario:"";
      $concepto =     isset($prm->concepto) ? $prm->concepto:"";
      $importe_min =  isset($prm->importeMin) ? str_replace("$ ","",$prm->importeMin):"";
      $importe_max =  isset($prm->importeMax) ? str_replace("$ ","",$prm->importeMax):"";
      $fecha_min =    isset($prm->fechaMin) ? $prm->fechaMin:"";
      $fecha_max =    isset($prm->fechaMax) ? $prm->fechaMax:"";

      //Se restringe en la consulta la UR 408
    //$sqlBloqueaUR =  " AND CONCAT(CD.gpo_id,CD.ur_id) != 408 ";
    $sqlBloqueaUR =  " ";
    $condicion_cuenta = " (" .
        "BN.bsf_id = '012610001096046934'  " .
        " OR BN.bsf_id = '002640700617974494'  " .
        " OR BN.bsf_id = '014610655059164236' " .
        //                " OR BN.bsf_id = '014610655057026648' " .
        //                " OR BN.bsf_id = '014610655058399141' " .
      ")";
      $sql_extra = '';
      $extra_sql_clc_cereco = '';
      // MODO   => 1.TRADICIONAL, 2.-LINEA CAPTURA , 4.-CADENA PRODUCTIVA 5.-DEUDA PUBLICA 6.-2.5% //CAMBIO 2016-09-14[IMPUESTO 2.5%]
      $sql_modo = ' AND CD.mpc_id <> 3'; //MODO DE PAGO DE CLC DISTINTO A SIN FLUJO DE EFECTIVO
      if ($modo == '1')
          $sql_modo = ' AND CD.mpc_id = 1'; // TRADICIONAL
      if ($modo == '2')
          $sql_modo = ' AND CD.mpc_id = 2'; // LINEA DE CAPTURA
      if ($modo == '4')
          $sql_modo = ' AND CD.mpc_id = 4'; // CADENAS PRODUCTIVAS
      if ($modo == '5') {
          $sql_modo = " AND CD.mpc_id <> 3 AND CV.fte_id = 'B' "; // DEUDA PUBLICA
          $condicion_cuenta = " 1=1 ";
      }
      if ($modo == '6') {//CAMBIO 2016-09-14[IMPUESTO 2.5%]
          $sql_extra .= " AND CONCAT(CD.gpo_id, CD.ur_id) = 116";
          $sql_extra .= " AND CDX.clc_cereCo IN (0)";
          $extra_sql_clc_cereco = ' ';
      }
      if ($tipo == '2')//op
        if ($impuesto_op != '0')
            $sql_extra ="";
      if ($tipo == '1')//OTB
            $sql_extra .= ' AND CD.clc_tipoCereCo = 1';
      if ($tipo == '2')//OP
          $sql_extra .= ' AND CD.clc_tipoCereCo = 2';

      $sql_tipo_clc = "";
      if ($tipo_clc != '')
          $sql_tipo_clc = " AND CD.tcl_id = '$tipo_clc' ";
      if ($subtipo_clc != '')
          $sql_tipo_clc .= " AND CD.stc_id = '$subtipo_clc' ";

      if ($numero != '')
          $sql_extra .= " AND CDX.clc_cereCo IN ($numero)";
      if ($ur != '')
          $sql_extra .= " AND CONCAT(CD.gpo_id, CD.ur_id) = $ur";

      //if($ur_nombre!='')  $sql_extra .= " AND UR.ur_nombre LIKE '%$ur_nombre%'";
      if ($ur_nombre != '')
          $sql_extra .= " AND CD.gpo_id = " . substr($ur_nombre, 0, 1) . " AND CD.ur_id = " . substr($ur_nombre, 1, 2);
      if ($clc != '')
          $sql_extra .= " AND CD.clc_id IN ($clc)";
      //
      if ($beneficiario != '')
          $sql_extra .= " AND CD.clc_beneficiario = '$beneficiario'";

      if ($concepto != '')
          $sql_extra .= " AND CD.clc_concepto LIKE '%$concepto%'";

      if ($importe_min != '' && $importe_max == '')
          $sql_extra .= " AND (CD.clc_percepcion - CD.clc_retencion) >= $importe_min";
      if ($importe_min == '' && $importe_max != '')
          $sql_extra .= " AND (CD.clc_percepcion - CD.clc_retencion) <= $importe_max";
      if ($importe_min != '' && $importe_max != '')
          $sql_extra .= " AND ((CD.clc_percepcion - CD.clc_retencion) BETWEEN $importe_min AND $importe_max) ";
      if ($fecha_min != '' && $fecha_max == '')
          $sql_extra .= " AND CDX.clc_generacionCereCo >= '$fecha_min'";
      if ($fecha_min == '' && $fecha_max != '')
          $sql_extra .= " AND CDX.clc_generacionCereCo <= '$fecha_max'";
      if ($fecha_min != '' && $fecha_max != '')
          $sql_extra .= " AND (CDX.clc_generacionCereCo BETWEEN '$fecha_min 00:00:00' AND '$fecha_max 23:59:59') ";

      $ordena = ' ORDER BY (CD.clc_percepcion - CD.clc_retencion)';
      $ordena = ' ORDER BY CDX.clc_generacionCereCo';

      $sql="SELECT 
        concat(CD.gpo_id,CD.ur_id,CD.clc_id)  IDCLC,
        SUBSTR(CDX.clc_generacionCereCo,1,10) FECHA_ELABORACION,
        DATEDIFF(CDX.clc_generacionCereCo,NOW()) DIFERENCIA,
        MP.mpc_nombre MODO,
        CD.mpc_id TLO_ID,
        concat(CD.gpo_id,CD.ur_id) UR,  
        CD.ur_id  UR_ID,
        CD.gpo_id GPO_ID,
        CD.clc_id       CLC, 
        CDX.clc_cereCo    FOLIO,
        IF (CD.clc_tipoCereCo = 1, 'CE', 'CO') TIPO,        
        TC.tcl_nombre     NOMBRE_TIPO,
        STC.stc_nombre    NOMBRE_SUBTIPO,
        UR.ur_nombre    RESPONSABLE,
        CD.clc_beneficiario BENEFICIARIO, 
        CD.clc_concepto   CONCEPTO,
        BN.bsf_id       CARGO,
        CD.bur_id       DESTINO,
        (CD.clc_percepcion - CD.clc_retencion) IMPORTE,
        concat(CV.fte_id,CV.sft_id,CV.ori_id,CV.sor_id,CV.cso_id,CV.cfi_id,CV.cfi_anio) CVE_FIN,
        CF.cfi_nombre CVE_NOMBRE
        FROM
          " . BD_CTRL_2017 . ".scp_Clc CD
          -- LEFT JOIN tesoreriaBitacora.tem_PropuestaCLC TMP USING (gpo_id,ur_id,clc_id)
          LEFT JOIN (
              SELECT gpo_id, ur_id, clc_id 
              FROM " . BD_TESORERITEM . ".tem_PropuestaClc as T
              WHERE T.pro_ejercicio = 2017 AND T.usr_id = ".$_SESSION[_S]['usr_id']."
          ) AS TMP USING (gpo_id,ur_id,clc_id)
          inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id)
          INNER JOIN " . BD_CTRL_2017 . ".scp_ClcAuxiliar CDX USING(gpo_id, ur_id, clc_id)
          LEFT JOIN " . BD_CTRL_2017 . ".tes_ClcLoteTesoreria CLT USING(gpo_id, ur_id, clc_id)
          INNER JOIN " . BD_CTRL_2017 . ".enc_BancosSefinCveFin BN USING (ecf_id)
          INNER JOIN " . BD_CTRL_2017 . ".enc_ClaveFinanciamiento CV using(ecf_id)
          INNER JOIN " . BD_CTRL_2017 . ".cat_ClaveFinanciamiento CF using(fte_id, sft_id, ori_id, sor_id, cso_id, cfi_id, cfi_anio)
          INNER JOIN " . BD_CTRL_2017 . ".cat_UnidadResponsable UR ON (concat(CD.gpo_id, CD.ur_id) = concat(UR.gpo_id, UR.ur_id))
          LEFT JOIN " . BD_CTRL_2017 . ".cat_TipoClc TC USING (tcl_id)
          LEFT JOIN " . BD_CTRL_2017 . ".cat_SubtipoClc STC USING (tcl_id,stc_id)
          INNER JOIN " . BD_CTRL_2017 . ".cat_ModoPagoClc MP on (CD.mpc_id = MP.mpc_id)
            WHERE 
              $condicion_cuenta 
              AND ( (CLT.lot_id IS NULL AND ant.ecl_id = 10 AND ant.ejr_id_clc = 2017) OR (CLT.lot_id LIKE 'P%' AND ant.ecl_id = 11 AND ant.ejr_id_clc = 2017) )
              AND CONCAT(CD.gpo_id, CD.ur_id) <> '901' 
              AND (CD.clc_percepcion - CD.clc_retencion)>0 
              AND TMP.clc_id IS NULL ".
              $sql_tipo_clc .
              $sql_modo .
              $sqlBloqueaUR .
              $sql_extra .
              $extra_sql_clc_cereco . //CAMBIO 2016-09-14[IMPUESTO 2.5%]
              $ordena;
      
        return $sql;
    }
    
    function getInfoImpuestos($impuesto_op){
      $condicion = "";
        if ($impuesto_op == 'isr')
            $condicion = " AND concat(CONT.ger_id,CONT.cgp_id,CONT.rub_id,CONT.cgcu_id,CONT.cgsc_id) = '21171' AND CONT.cgc_id IN (002, 003, 004, 005) ";
        if ($impuesto_op == 'imss')
            $condicion = " AND concat(CONT.ger_id,CONT.cgp_id,CONT.rub_id,CONT.cgcu_id,CONT.cgsc_id) = '21172' AND CONT.cgc_id IN (001,011) ";
        if ($impuesto_op == 'issste')
            $condicion = " AND concat(CONT.ger_id,CONT.cgp_id,CONT.rub_id,CONT.cgcu_id,CONT.cgsc_id) = '21172' AND CONT.cgc_id IN (012,013,023,024,025,026) ";
        if ($impuesto_op == 'fonacot')
            $condicion = " AND concat(CONT.ger_id,CONT.cgp_id,CONT.rub_id,CONT.cgcu_id,CONT.cgsc_id) = '21179' AND CONT.cgc_id IN (007,014) ";
        if ($impuesto_op == 'infonavit')
            $condicion = " AND concat(CONT.ger_id,CONT.cgp_id,CONT.rub_id,CONT.cgcu_id,CONT.cgsc_id) = '21179' AND CONT.cgc_id IN (006) ";
        $sql = "SELECT
          CONCAT(CD.gpo_id, CD.ur_id, CD.clc_id) LISTA
        FROM
          scp_Clc CD
        inner join " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id)
        INNER JOIN scp_ClcAuxiliar CDX USING(gpo_id, ur_id, clc_id)
        LEFT JOIN tes_ClcLoteTesoreria CLT USING(gpo_id, ur_id, clc_id)
        INNER JOIN scp_Contable CONT USING (gpo_id, ur_id, clc_id)
        WHERE
          clc_cereCo > 0 AND clc_tipocereCo = 2
        AND CLT.lot_id IS NULL
        AND ant.ecl_id = 10 AND CDX.clc_cereCo >0" .
                $condicion .
                " AND CONT.ncc_tipoMovimiento = '+'
        GROUP BY CONT.gpo_id,CONT.ur_id,CONT.clc_id";
        $data = $this->simpres_2017->query($sql);

        $listado = '';
        foreach ($data->result() as $key => $object) {
            $listado .= $object->LISTA . ',';
        }

        if ($listado == '')
            return ' AND CONCAT(CD.gpo_id,CD.ur_id,CD.clc_id) IN(-1) ';
        else {
            $listado = substr($listado, 0, -1);
            $sql_extra = " AND CONCAT(CD.gpo_id,CD.ur_id,CD.clc_id) IN($listado) ";
            return $sql_extra;
        }
    }

    function sqlLlenaTablaClcProveedor($prm)
    {
      //Se restringe en la consulta la UR 408
      //$sqlBloqueaUR =  " AND CONCAT(a.gpo_id,a.ur_id) != 408 ";
      $sqlBloqueaUR =  " ";
      $tipo_clc ='E';
      $sql_extra="";
      $sql_extraproveedor="";
      $condicion_cuenta = " AND(BN.bsf_id = '002640700617974494' OR BN.bsf_id = '044610092026743860' OR BN.bsf_id = '002640700278540500' OR BN.bsf_id = '014610655059164236')";
      $tipo=$prm->documento;
        if ($tipo == 'otb')
            $sql_extra .= ' AND a.clc_tipoCereCo = 1';
        if ($tipo == 'op')
            $sql_extra .= ' AND a.clc_tipoCereCo = 2';

        $sql_tipo_clc = "";
        if ($tipo_clc != '')
            $sql_tipo_clc = " AND a.tcl_id <> '$tipo_clc' ";
        $subtipo_clc="";
        if ($subtipo_clc != '')
            $sql_tipo_clc .= " AND a.stc_id = '$subtipo_clc' ";

        $numero=$prm->orden;
        if ($numero != '')
            $sql_extra .= " AND CDX.clc_cereCo IN ($numero)";
        $ur=$prm->numerour;
        if ($ur != '')
            $sql_extra .= " AND CONCAT(a.gpo_id, a.ur_id) = $ur";
        $ur_nombre=$prm->nombreur;
        if ($ur_nombre != '')
            $sql_extra .= " AND CD.gpo_id = " . substr($ur_nombre, 0, 1) . " AND CD.ur_id = " . substr($ur_nombre, 1, 2);
        $clc=$prm->clc;
        if ($clc != '')
            $sql_extra .= " AND a.clc_id IN ($clc)";
        $rfc=$prm->rfc;
        if ($rfc != '')
            $sql_extraproveedor .= " AND a.rfc_id = '$rfc'";
        $rfc_nombre=$prm->proveedor;
        if ($rfc_nombre != '')
            $sql_extraproveedor .= " AND CONCAT(cp.rfc_nombre,'',cp.rfc_paterno,'',cp.rfc_materno) like'$rfc_nombre%'";
        $beneficiario=$prm->beneficiario;
        if ($beneficiario != '')
            $sql_extra .= " AND a.clc_beneficiario = '$beneficiario'";
        $concepto=$prm->concepto;
        if ($concepto != '')
            $sql_extra .= " AND a.clc_concepto LIKE '%$concepto%'";

        $fecha_min=$prm->fechaMin;
        $fecha_max=$prm->fechaMax;
        if ($fecha_min != '' && $fecha_max == '')
            $sql_extra .= " AND a.clc_fechaElaboracion >= '$fecha_min'";
        if ($fecha_min == '' && $fecha_max != '')
            $sql_extra .= " AND a.clc_fechaElaboracion <= '$fecha_max'";
        if ($fecha_min != '' && $fecha_max != '')
            $sql_extra .= " AND (a.clc_fechaElaboracion BETWEEN '$fecha_min 00:00:00' AND '$fecha_max 23:59:59') ";
        
        $sql="SELECT
            concat(a.gpo_id,a.ur_id) UR,
            a.gpo_id GPO_ID,
            a.ur_id  UR_ID,  
            a.ur URCOMPL,
            5 TLO_ID,
            a.clc_id CLC,
            a.otb FOLIO,
            a.ecl_id EDO_CLC,
            MIN(TC.tcl_nombre) NOMBRE_TIPO,
            MIN(STC.stc_nombre) NOMBRE_SUBTIPO,
            UR.ur_nombre RESPONSABLE,
            MIN(a.clc_beneficiario) BENEFICIARIO,
            MIN(a.clc_concepto) CONCEPTO,
            if(a.clc_tipoCereCo=1,'CE','CO') TIPO,
            a.importeclc IMPORTE,
            a.fecha_elaboracion FECHA_ELABORACION,
            BN.bsf_id CARGO,
            a.bur_id DESTINO,
            concat(CV.fte_id,CV.sft_id,CV.ori_id,CV.sor_id,CV.cso_id,CV.cfi_id,CV.cfi_anio)     CVE_FIN,
            CF.cfi_nombre CVE_NOMBRE,
            DATEDIFF(a.fecha_elaboracion,NOW()) DIFERENCIA,
            'PDP' MODO,
            a.rfc_id RFC,
            CONCAT(cp.rfc_nombre,'',cp.rfc_paterno,'',cp.rfc_materno) NOMBREPROVEEDOR,
            a.rfc_id,
            X.ucb_id USO_CUENTA,
            MAX(a.ncp) NCP_ID,
            -- '100000' IMPORTEPR
            sum(a.importepr) IMPORTEPR
          FROM (
                SELECT concat(a.gpo_id,a.ur_id) ur,a.gpo_id,a.ur_id,a.clc_id ,
                a.bur_id,
                a.mpc_id,
                ant.ecl_id,
                a.ecf_id,
                a.tcl_id,
                a.stc_id,
                a.clc_beneficiario,
                a.clc_concepto,
                a.clc_tipoCereco,
                CDX.clc_cereCo as otb,
                SUBSTR(CDX.clc_generacionCereCo,1,10)fecha_elaboracion,
                (a.clc_percepcion-a.clc_retencion) importeclc,
                p.rfc_id,
                MIN(pre.ncp_id) ncp,
                sum(ncp_percepcionNeto) importepr
                FROM
                " . BD_CTRL_2017 . ".scp_Clc a
                LEFT JOIN (
                  SELECT gpo_id, ur_id, clc_id 
                  FROM " . BD_TESORERITEM . ".tem_PropuestaClc as T
                  WHERE T.pro_ejercicio = 2017 AND T.usr_id = ".$_SESSION[_S]['usr_id']."
                ) AS TMP USING (gpo_id,ur_id,clc_id)
                INNER JOIN " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id)
                INNER JOIN " . BD_CTRL_2017 . ".scp_ClcAuxiliar CDX USING(gpo_id, ur_id, clc_id)
                INNER JOIN  " . BD_CTRL_2017 . ".scp_Presupuestal pre USING (gpo_id,ur_id,clc_id)
                INNER JOIN " . BD_CTRL_2017 . ".vin_BancosUrProveedor as p using (bur_id)
                   WHERE a.mpc_id=5 AND a.tcl_id<>'E'
                    AND TMP.clc_id IS NULL
                    AND ( ant.ecl_id = 10) 
                    AND ant.ejr_id_clc = 2017
                    AND (a.clc_percepcion - a.clc_retencion)>0 
                    $sqlBloqueaUR
                    $sql_extra
                group by a.gpo_id,a.ur_id,a.clc_id,p.rfc_id
               
               UNION
            
                SELECT concat(a.gpo_id,a.ur_id)ur,a.gpo_id,a.ur_id ,a.clc_id,
                a.bur_id,
                a.mpc_id,
                ant.ecl_id,
                a.ecf_id,
                a.tcl_id,
                a.stc_id,
                a.clc_beneficiario,
                a.clc_concepto,
                a.clc_tipoCereco,
                CDX.clc_cereCo as otb,
                SUBSTR(CDX.clc_generacionCereCo,1,10)fecha_elaboracion,
                (a.clc_percepcion-a.clc_retencion) importeclc,
                p.rfc_id,
                ''ncp,
                sum(ncc_percepcion)-sum(ncc_retencion) importepr
                FROM " . BD_CTRL_2017 . ".scp_Clc a
                LEFT JOIN (
                  SELECT gpo_id, ur_id, clc_id 
                  FROM " . BD_TESORERITEM . ".tem_PropuestaClc as T
                  WHERE T.pro_ejercicio = 2017 AND T.usr_id = ".$_SESSION[_S]['usr_id']."
                ) AS TMP USING (gpo_id,ur_id,clc_id)
                INNER JOIN " . BD_CTRLANT . ".scp_Clc as ant using (gpo_id,ur_id,clc_id)
                INNER JOIN " . BD_CTRL_2017 . ".scp_ClcAuxiliar CDX using (gpo_id,ur_id,clc_id)
                INNER JOIN " . BD_CTRL_2017 . ".scp_Contable using (gpo_id,ur_id,clc_id)
                INNER JOIN " . BD_CTRL_2017 . ".vin_BancosUrProveedor as p using (bur_id)
                WHERE a.mpc_id=5  AND a.tcl_id<>'E'
                    AND TMP.clc_id IS NULL
                    AND (( ant.ecl_id = 10) OR (ant.ecl_id = 11))
                    AND ant.ejr_id_clc = 2017 
                    AND (a.clc_percepcion - a.clc_retencion)>0 
                    $sqlBloqueaUR 
                    $sql_extra
                group by a.gpo_id,a.ur_id,a.clc_id,p.rfc_id
            )a 
               
               INNER JOIN " . BD_SINPRES . ".cat_Proveedores as cp using(rfc_id)
               LEFT JOIN " . BD_CTRL_2017 . ".tes_ClcLoteTesoreria USING (gpo_id,ur_id,clc_id)
               INNER JOIN " . BD_CTRL_2017 . ".enc_BancosSefinCveFin BN USING (ecf_id)
               INNER JOIN " . BD_CTRL_2017 . ".enc_ClaveFinanciamiento CV using(ecf_id)
               INNER JOIN " . BD_CTRL_2017 . ".cat_ClaveFinanciamiento CF using(fte_id, sft_id, ori_id, sor_id, cso_id, cfi_id, cfi_anio)
               INNER JOIN " . BD_CTRL_2017 . ".cat_UnidadResponsable UR using (gpo_id,ur_id)
               INNER JOIN " . BD_CTRL_2017 . ".cat_TipoClc TC USING (tcl_id)
               INNER JOIN " . BD_CTRL_2017 . ".cat_SubtipoClc STC USING (tcl_id,stc_id)
               INNER JOIN " . BD_CTRL_2017 . ".cat_BancosUr X USING (bur_id)
               WHERE  lot_id is null".$condicion_cuenta .$sql_extraproveedor.
               "GROUP BY UR, a.gpo_id,a.ur_id,a.clc_id,a.ecl_id,a.otb,
               a.clc_tipoCereCo,a.importeclc,a.fecha_elaboracion,BN.bsf_id,
               a.bur_id, CVE_FIN, a.rfc_id";
      
      return $sql;
    }
    
    function sqlDocumentosPDP($prm)
    {
        $sql = 
            "SELECT 
                doc.bdo_id, b.rfc_id, (doc.bdo_percepcionPresupuestal + doc.bdo_percepcionContable 
                - doc.bdo_retencionContable) AS importeBdo, b.bur_id AS bur_id, sum(b.importeBdo) AS importeDocClc
            FROM 
                (
                    SELECT 
                        c.gpo_id, c.ur_id, c.bdo_id, 
                        c.rfc_id, VBP.bur_id,
                        (c.ncc_percepcion-c.ncc_retencion) AS importeBdo
                    FROM 
                        " . BD_CTRL_2017 . ".scp_Contable c 
                        INNER JOIN " . BD_CTRL_2017 . ".vin_BancosUrProveedor as VBP USING(rfc_id)
                    WHERE 
                        gpo_id = {$prm->gpo_id}
                        AND ur_id = {$prm->ur_id}
                        AND clc_id = {$prm->clc_id}
                    GROUP BY bdo_id 

                    UNION ALL 

                    SELECT 
                        p.gpo_id, p.ur_id, p.bdo_id,
                        p.rfc_id, VBP.bur_id,
                        ncp_percepcionNeto AS importeBdo
                    FROM 
                        " . BD_CTRL_2017 . ".scp_Presupuestal AS p
                        INNER JOIN " . BD_CTRL_2017 . ".vin_BancosUrProveedor as VBP USING(rfc_id)
                    WHERE 
                        gpo_id = {$prm->gpo_id}
                        AND ur_id = {$prm->ur_id}
                        AND clc_id = {$prm->clc_id}
                    GROUP BY bdo_id
                ) AS b
                INNER JOIN " . BD_CTRL_2017 . ".cat_BancoDocumentos AS doc USING(gpo_id, ur_id, bdo_id)
            GROUP BY gpo_id, ur_id, bdo_id";

        return $sql;
    }
}