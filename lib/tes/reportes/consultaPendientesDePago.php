<?php

/**
 * Description of function consultaPendientesDePago
 *
 * @author 
 * Collaborators: Adriana Santiago Morales <adriana.morales@finanzasoaxaca.gob.mx>
 * Created: 12-Jun-2019
 * Comment: -----
 * Objective: Consultar datos en la base de datos de los pendientes de pago
 * Regla de Negocio:----
 * 
 **/

function consultaPendientesDePago($anio, $return_sql = "No")
{
    switch ($anio) {
        case 2016:
            $sql = "
                    SELECT
                    mpc_id,
                    CONCAT(A.gpo_id, A.ur_id) AS UR,
                    B.ur_nombre AS NOMBRE,
                    A.ue_id AS UE,
                    A.clc_tipocereCo AS tipodoc,
                    A.clc_cereCo AS OTB,
                    G.ecl_nombre AS ESTADO_CLC,
                    A.clc_id AS CLC,
                    E.tcl_nombre AS tipo_Clc,
                    F.stc_nombre AS SubtipoClc,
                    A.clc_concepto AS CONCEPTO,
                    (A.clc_percepcion - A.clc_retencion) AS Neto,
                    DATE( A.clc_fechaElaboracion) AS elab_CLC,
                    DATE(A.clc_fechaEnvio)AS envio_Finanzas,
                    DATE(A.clc_fechaAutorizacion )as Autorizacion_Finanzas,
                    DATE(A.clc_generacionCereCo )AS FECHA_OTB,
                    CONCAT(C.fte_id,C.sft_id,C.ori_id,C.sor_id,C.cso_id, C.cfi_id,C.cfi_anio) AS CVEFIN,
                    CONCAT(D.bsf_id) AS CTABAN_CARGO,
                    CONCAT(A.bur_id) AS CTABAN_ABONO,
                    '' AS ucb_nombre,
                    CONCAT(A.loe_dia,A.loe_mes,A.loe_anio,A.loe_cereCo,A.loe_id) AS LOTE_EGRESOS,
                    A.loi_id AS LOTE_PAGO,
                    A.ing_fecha AS FECHA_PAGO,
                    A.ing_fechaPago as Fecha,
                    A.ing_referenciaPago AS SPEI
                    FROM
                    ctrl2016.scp_Clc A
                    LEFT JOIN
                    ctrl2016.cat_UnidadResponsable B USING (gpo_id , ur_id)
                    LEFT JOIN
                    ctrl2016.cat_BancosUr X USING (bur_id)
                    LEFT JOIN
                    cat_UsoCuentaBancaria USING(ucb_id)
                    LEFT JOIN
                    ctrl2016.enc_ClaveFinanciamiento C USING (ecf_id)
                    LEFT JOIN
                    ctrl2016.enc_BancosSefinCveFin D USING (ecf_id)
                    LEFT JOIN
                    ctrl2016.cat_TipoClc E USING (tcl_id)
                    LEFT JOIN
                    ctrl2016.cat_SubtipoClc F USING (tcl_id , stc_id)
                    INNER JOIN
                    ctrl2016.cat_EstadoClc G USING (ecl_id)
                    where ecl_id in (10,11,12) and clc_RecursoFederal=2
                    ORDER BY UR,CLC;
                ";
            break;
        case 2015:
            $sql = "
                    SELECT
                    mpc_id,
                    CONCAT(A.gpo_id, A.ur_id) AS UR,
                    B.ur_nombre AS NOMBRE,
                    A.ue_id AS UE,
                    A.clc_tipocereCo AS tipodoc,
                    A.clc_cereCo AS OTB,
                    G.ecl_nombre AS ESTADO_CLC,
                    A.clc_id AS CLC,
                    E.tcl_nombre AS tipo_Clc,
                    F.stc_nombre AS SubtipoClc,
                    A.clc_concepto AS CONCEPTO,
                    (A.clc_percepcion - A.clc_retencion) AS Neto,
                    DATE( A.clc_fechaElaboracion) AS elab_CLC,
                    DATE(A.clc_fechaEnvio)AS envio_Finanzas,
                    DATE(A.clc_fechaAutorizacion )AS Autorizacion_Finanzas,
                    DATE(A.clc_generacionCereCo )AS FECHA_OTB,
                    CONCAT(C.fte_id,C.sft_id,C.ori_id,C.sor_id,C.cso_id, C.cfi_id,C.cfi_anio) AS CVEFIN,
                    CONCAT(D.bsf_id) AS CTABAN_CARGO,
                    CONCAT(A.bur_id) AS CTABAN_ABONO,
                    '' AS ucb_nombre,
                    CONCAT(A.loe_dia,A.loe_mes,A.loe_anio,A.loe_cereCo,A.loe_id) AS LOTE_EGRESOS,
                    A.loi_id AS LOTE_PAGO,
                    A.ing_fecha AS FECHA_PAGO,
                    A.ing_fechaPago AS Fecha,
                    A.ing_referenciaPago AS SPEI
                    FROM
                    ctrl2015.scp_Clc A
                    LEFT JOIN
                    ctrl2015.cat_UnidadResponsable B USING (gpo_id , ur_id)
                    LEFT JOIN
                    ctrl2015.cat_BancosUr X USING (bur_id)
                    LEFT JOIN
                    cat_UsoCuentaBancaria USING(ucb_id)
                    LEFT JOIN
                    ctrl2015.enc_ClaveFinanciamiento C USING (ecf_id)
                    LEFT JOIN
                    ctrl2015.enc_BancosSefinCveFin D USING (ecf_id)
                    LEFT JOIN
                    ctrl2015.cat_TipoClc E USING (tcl_id)
                    LEFT JOIN
                    ctrl2015.cat_SubtipoClc F USING (tcl_id , stc_id)
                    INNER JOIN
                    ctrl2015.cat_EstadoClc G USING (ecl_id)
                    WHERE ecl_id IN (10,11,12) #and clc_RecursoFederal=2
                    AND CONCAT( A.gpo_id, '.', A.ur_id, '.', A.clc_id ) NOT IN
                    (
                    # determinamos las que SI son de recurso federal
                    # sacado del reporte de CLC (rep_Clc) del sinpres2015
                    SELECT CONCAT( gpo_id, '.', ur_id, '.', clc_id ) FROM scp_Clc
                    WHERE
                    ecf_id IN (
                    SELECT ecf_id FROM
                    ctrl2015.enc_ClaveFinanciamiento
                    INNER JOIN ctrl2015.cat_ClasificacionSuborigen USING( fte_id, sft_id, ori_id, sor_id,cso_id)
                    WHERE ter_id='B' AND sts_id=1
                    AND ecf_id NOT IN(
                    000272, 002292, 002067, 000289, 001575,
                    000297, 002122, 000308, 002121, 000324,
                    000325, 000330, 001351, 000332, 002290,
                    001369, 000893, 000894, 000897, 000898,
                    001822, 001823, 000904, 002181, 002182,
                    002183, 002184, 002114, 001824, 000918,
                    001825, 001826, 001827, 001828, 000939,
                    001829, 000944, 001830, 001831, 000955,
                    001832, 001833, 001835, 001350, 001836,
                    002125, 001949, 001991, 001883, 001884,
                    001433, 001838, 001839, 000998, 001840,
                    001536, 002240, 001581, 002238, 002236,
                    002237, 002241, 001498, 001535, 001383,
                    001394, 001497, 001404, 001552, 001584,
                    001878, 002239, 001876, 002258, 001966,
                    002247, 002243, 001855, 002007, 001872,
                    001867, 001877, 001882, 002244,002285,
                    002299,000950,000973,001525,001563,001885,002288,
                    000961,002312,002310,002313,002246,001857,
                    001857,002321,002322,002309,002270,
                    001856,002284,002287,002343,002357,002385,002389
                    )
                    )
                    AND CONCAT( gpo_id, ur_id ) NOT IN ( '315', '801', '802' )
                    AND NOT (
                    CONCAT( gpo_id, ur_id ) IN ( '202','302','303','304','306','307','308','309','310','324','333','334','471' ) AND
                    tcl_id = 'A' AND stc_id = '02'
                    )
                    AND ecl_id = 16
                    AND NOT (
                    ( CONCAT( gpo_id, ur_id ) = '325' AND tcl_id = 'A' AND ecf_id = 001739 ) OR
                    ( CONCAT( gpo_id, ur_id ) = '318' AND tcl_id = 'C' AND ecf_id = 001739 )
                    )
                    )
                    ORDER BY UR,CLC;
                ";
            break;
        case 2014:
            $sql = "
                    SELECT  c.mpc_id,
                    concat(c.gpo_id,c.ur_id)AS UR ,
                    ur_nombre AS NOMBRE,
                    c.ue_id AS UE,
                    c.clc_tipoCereCo AS tipodoc,
                    c.clc_cereCo AS OTB,
                    E.ecl_nombre as ESTADO_CLC,
                    c.clc_id AS CLC,
                    T.tcl_nombre AS tipo_Clc,
                    S.stc_nombre AS SubtipoClc,
                    c.clc_concepto AS CONCEPTO,
                    (c.clc_percepcion-clc_retencion) AS Neto,
                    DATE(c.clc_fechaElaboracion) AS elab_CLC,
                    DATE(c.clc_fechaEnvio) AS envio_Finanzas,
                    DATE(c.clc_fechaAutorizacion) AS Autorizacion_Finanzas,
                    DATE(c.clc_generacionCereCo) AS FECHA_OTB,
                    CONCAT(F.fte_id,F.sft_id,F.ori_id,F.sor_id,F.cso_id,F.cfi_id,F.cfi_anio) AS CVEFIN,
                    CONCAT(B.bsf_id) AS CTABAN_CARGO,
                    CONCAT(c.bur_id) AS CTABAN_ABONO,
                    '' AS ucb_nombre,
                    CONCAT(c.loe_dia,c.loe_mes,c.loe_anio,c.loe_cereCo,c.loe_id) AS LOTE_EGRESOS,
                    c.loi_id AS LOTE_PAGO,
                    c.ing_fecha AS FECHA_PAGO,
                    c.ing_fechaPago AS Fecha,
                    c.ing_referenciaPago AS SPEI
                    FROM ctrl2014.scp_Clc AS c
                    INNER JOIN ctrl2014.cat_UnidadResponsable USING (gpo_id,ur_id)
                    INNER JOIN ctrl2014.cat_EstadoClc AS E using (ecl_id)
                    INNER JOIN ctrl2014.cat_TipoClc AS T USING (tcl_id)
                    INNER JOIN ctrl2014.cat_SubtipoClc AS S using (stc_id)
                    INNER JOIN ctrl2014.enc_ClaveFinanciamiento AS F using(ecf_id)
                    INNER JOIN ctrl2014.enc_BancosSefinCveFin AS B using(ecf_id)
                    WHERE ecl_id IN (10,11,12,14)  AND c.loi_id IS NOT NULL AND c.ing_referenciaPago =''
                    GROUP BY c.gpo_id,c.ur_id,c.clc_id
                    ORDER BY B.bsf_id;
                ";
            break;
        case 2013:
            $sql = "
                    SELECT
                    '' AS mpc_id,
                    concat(A.clc_gpo_id,A.clc_ur_id)AS UR,
                    B.ur_nombre AS NOMBRE,
                    A.clc_ue_id AS UE,
                    F.ucb_nombre,
                    IF(A.otb_id!='',1,2)AS tipodoc,
                    IF(A.otb_id !='',A.otb_id,A.op_id) AS OTB,
                    A.clc_id AS CLC,
                    C.ecl_nombre AS ESTADO_CLC,
                    D.tclc_nombre AS tipo_Clc,
                    '' AS SubtipoClc,
                    A.clc_concepto AS CONCEPTO,
                    IF(A.otb_id !='',X.otb_importe,Y.op_importe) AS Neto,
                    DATE(A.clc_fechaElaboracion) AS elab_CLC,
                    DATE(A.clc_fechaAutorizacion) AS Autorizacion_Finanzas,
                    IF(A.otb_id !='',DATE(X.otb_fechaGeneracion),DATE(Y.op_fechaGeneracion)) AS FECHA_OTB,
                    A.clc_cvefin AS CVEFIN,
                    IF(A.otb_id !='',X.bansf_id,Y.bansf_id) AS CTABAN_CARGO,
                    A.banur_id AS CTABAN_ABONO,
                    IF(A.otb_id !='',X.lotce_id,Y.lotco_id) AS LOTE_EGRESOS,
                    IF(A.otb_id!='',X.ing_lote,Y.ing_lote)AS LOTE_PAGO,
                    IF(A.otb_id!='',X.ing_fecha,Y.ing_fecha) AS FECHA_PAGO,
                    IF(A.otb_id!='',X.ing_fechaPago,Y.ing_fechaPago) AS Fecha,
                    IF(A.otb_id !='',X.ing_referenciaPago,Y.ing_referenciaPago)AS SPEI
                    FROM ctrl2013.scp_Cedula A
                    INNER JOIN ctrl2013.cat_EstadoClc AS C using (ecl_id)
                    INNER JOIN ctrl2013.cat_TipoCedula AS D using (tclc_id)
                    INNER JOIN ctrl2013.cat_BancosUr AS E using(banur_id)
                    INNER JOIN ctrl2013.cat_UsoCuentaBancaria  F using(ucb_id)
                    LEFT JOIN ctrl2013.cat_UnidadResponsable AS B ON B.gpo_id=A.clc_gpo_id
                    AND B.ur_id=A.clc_ur_id
                    LEFT JOIN ctrl2013.scp_Ceres AS X ON A.clc_gpo_id = X.clc_gpo_id
                    AND A.clc_ur_id = X.clc_ur_id AND A.clc_id = X.clc_id
                    LEFT JOIN ctrl2013.scp_Cerecos Y ON A.clc_gpo_id = Y.clc_gpo_id
                    AND A.clc_ur_id = Y.clc_ur_id AND A.clc_id = Y.clc_id
                    WHERE ecl_id = 7 OR X.ececo_id IN (4,6)
                    HAVING LOTE_PAGO !='' AND SPEI IS NULL;
                ";
            break;
        case 2012:
            $sql = "
                    SELECT
                    '' AS mpc_id,
                    CONCAT(a.gpo_id,a.ur_id) UR,
                    UR_OP.ur_nombre AS NOMBRE,
                    '' AS UE,
                    a.cedula AS CLC,
                    a.folio AS OTB,
                    c.ecl_nombre AS ESTADO_CLC,
                    '' AS elab_CLC,
                    '' AS envio_Finanzas,
                    '' AS Autorizacion_Finanzas,
                    '' AS ucb_nombre,
                    d.descr AS tipo_Clc,
                    '' AS SubtipoClc,
                    '2' AS tipodoc,
                    a.concepto CONCEPTO,
                    a.cvefin AS CVEFIN,
                    a.lotePropuestaPago AS LOTE_EGRESOS,
                    a.ctaban_cargo AS CTABAN_CARGO,
                    a.ctaban AS CTABAN_ABONO,
                    a.importe AS Neto,
                    DATE(a.fechaord) AS FECHA_OTB,
                    DATE(a.tra_fecha) AS FECHA_PAGO,
                    a.tra_lote AS LOTE_PAGO,
                    DATE(a.tra_fechapago) AS Fecha,
                    a.tra_numero AS SPEI
                    FROM ctrl2012.scp_m_orden AS a
                    LEFT JOIN ctrl2012.scp_r_cedula AS b ON b.gpo_id=a.gpo_id AND b.ur_id=a.ur_id AND b.cedula=a.cedula
                    LEFT JOIN ctrl2012.cat_EstadoClc AS c ON c.status=b.status
                    LEFT JOIN ctrl2012.cat_tipoclc AS d ON d.tipoclc=b.tipoced
                    LEFT JOIN ctrl2012.cat_UnidadResponsable UR_OP ON UR_OP.gpo_id=a.gpo_id AND UR_OP.ur_id=a.ur_id
                    WHERE a.tra_lote <>'' AND a.tra_fecha <>'' AND b.status =8
                    AND a.tra_numero ='' OR a.tra_fechapago='';
                ";
            break;
        case 2011:
            $sql = "
                    SELECT
                    '' AS mpc_id,
                    A.UR,
                    B.descr AS NOMBRE,
                    C.ue AS UE,
                    '' AS tipodoc,
                    '' AS ESTADO_CLC,
                    A.CEDULA AS CLC ,
                    D.descr AS tipo_Clc,
                    '' AS SubtipoClc,
                    '' AS elab_CLC,
                    '' AS FECHA_OTB,
                    '' AS CVEFIN,
                    '' AS ucb_nombre,
                    '' AS LOTE_EGRESOS,
                    '' AS LOTE_PAGO,
                    A.ORDEN AS OTB,
                    A.CONCEP AS CONCEPTO,
                    A.IMPORTE AS Neto,
                    date(C.fecenvio) AS envio_Finanzas,
                    date(C.fecautorizacion) AS Autorizacion_Finanzas,
                    A.CTABAN_CARGO AS CTABAN_CARGO,
                    A.CTABAN AS CTABAN_ABONO,
                    A.tra_fecha AS FECHA_PAGO,
                    C.fecprogpag AS Fecha,
                    A.tra_numero AS SPEI
                    FROM ctrl2011.scp_m_hismin AS A
                    LEFT JOIN ctrl2011.cat_unidadresponsable AS B ON B.ur=A.UR
                    LEFT JOIN ctrl2011.scp_r_cedula AS C ON C.ur=A.UR AND C.cedula=A.CEDULA
                    LEFT JOIN ctrl2011.cat_tipoclc AS D ON D.tipoclc=C.tipoced
                    WHERE A.tra_numero ='';
                ";
            break;
    }

    if ($return_sql == "No") {
        $conexion = new AccesoDatos();
        $conexion->conectar('sefipAnterior');
        $conexion->setSql($sql);
        $conexion->consultar();
        $conexion->desconectar();

        $datos = array();
        while ($row = $conexion->extraer()) {
            $dato = array(
                'mpc_id' => $row['mpc_id'],
                'ur' => $row['UR'],
                'ur_nombre' => $row['NOMBRE'],
                'ue' => $row['UE'],
                'tipodoc' => $row['tipodoc'],
                'otb' => $row['OTB'],
                'edo_clc' => $row['ESTADO_CLC'],
                'clc' => $row['CLC'],
                'tcl_nombre' => $row['tipo_Clc'],
                'stc_nombre' => $row['SubtipoClc'],
                'concepto' => $row['CONCEPTO'],
                'neto' => $row['Neto'],
                'fecha_elaboracion' => $row['elab_CLC'],
                'fecha_envio' => $row['envio_Finanzas'],
                'autorizacion' => $row['Autorizacion_Finanzas'],
                'fecha_otb' => $row['FECHA_OTB'],
                'cvefin' => $row['CVEFIN'],
                'cat_banco_cargo' => "'" . $row['CTABAN_CARGO'],
                'cat_banco_abono' => "'" . $row['CTABAN_ABONO'],
                'ucb_nombre' => $row['ucb_nombre'],
                'lote_egresos' => $row['LOTE_EGRESOS'],
                'lote_pago' => $row['LOTE_PAGO'],
                'fecha_pago' => $row['FECHA_PAGO'],
                'fecha' => $row['Fecha'],
                'spei' => $row['SPEI']
            );
            array_push($datos, $dato);
        }

        if (count($datos) == 0) {
            $dato = array(
                'mpc_id' => '',
                'ur' => '',
                'ur_nombre' => '',
                'ue' => '',
                'tipodoc' => '',
                'otb' => '',
                'edo_clc' => '',
                'clc' => '',
                'tcl_nombre' => '',
                'stc_nombre' => '',
                'concepto' => '',
                'neto' => '',
                'fecha_elaboracion' => '',
                'fecha_envio' => '',
                'autorizacion' => '',
                'fecha_otb' => '',
                'cvefin' => '',
                'cat_banco_cargo' => '',
                'cat_banco_abono' => '',
                'ucb_nombre' => '',
                'lote_egresos' => '',
                'lote_pago' => '',
                'fecha_pago' => '',
                'fecha' => '',
                'spei' => '',
            );
            array_push($datos, $dato);
        }
        $regresar = $datos;
    } else {
        $regresar = $sql;
    }
    return $regresar;
}
