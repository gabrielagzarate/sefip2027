<?php

ini_set('memory_limit', '500M');
set_time_limit(0);
session_start();

require_once dirname(__FILE__) . '/../lib/WebResponse.php';
/*SE LA VARIABLE ACCION ESTA VACIA ENTONCES SE DESTRUYE.*/
if (!isset($_POST['accion'])) {
    unset($_SESSION[_S]['archivo']);
}
/**********************************************************************/
/*ESTA PANTALLA EXPORTA LA TABLA SELECCIONADA A XML.*/
/**********************************************************************/
/*se pregunta si la variable accion tiene asignado el valor de upload.*/
if ($_POST['accion'] == 'upload') {
    $param = [];
    /*se asigna a la variable opcion en valor qeu trae la variable "envia"*/
    $opcion = $_POST['enviar'];
    $prg_id = $_POST['prg_id']; //Se obtiene número de programa para reporte Seguimiento Productos Intermedios con metas
    /**************************************************************************************************/
    /*EN ESTA AREA SE ASIGNAN LAS CONSULTAS A UNAS VARIABLES, PARA DESPUES IDENTIFICADA QUE VARIABLE PERTENCE A QUE CONSULTA,
    SE MANDA A LLAMAR.*/
    /**************************************************************************************************/
    $sql1 = '
        SELECT gpo_id as Grupo,ur_id as UR,clc_id AS Clc,ue_id as Unidad_Ejecutora,tcp_id as Tipo_Compra,clc_concepto AS  Concepto,
            DATE_FORMAT( clc_fechaElaboracion,"%d/%m/%Y")  Fecha_Elaboracion,
            DATE_FORMAT( clc_fechaElaboracion,"%d/%m/%Y")  Fecha_Envio ,
            if(clc_tipocereCo=1  ,"CERE",if( clc_tipocereCo=2, "CERECO","SIN CERE(CO)"))CereCo,
            clc_cereCo AS Num_Cereco,bur_id as Clabe_Interbancaria,cfi_clave as Clave_Financiamiento,
            clc_beneficiario as Beneficiario,
            clc_percepcion as Percepciones,clc_retencion as Retenciones,
            clc_percepcion-clc_retencion as Importe
        FROM scp_Clc
        INNER JOIN scp_ClcAuxiliar USING(gpo_id,ur_id,clc_id,ecl_id)
        JOIN cat_ModoPagoClc USING(mpc_id)
        JOIN enc_ClaveFinanciamiento USING (ecf_id)
        JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
        WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . '
        AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . '
        AND clc_id!=0 ';

    $sql2 = '
        SELECT gpo_id as Grupo,ur_id as UR,clc_id AS Clc,afe_id as Afectacion,
        concat(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)as Enc_Programatico,
        cfi_clave AS Clave_Financiamiento,tcp_id as Tipo_Compra,
        DATE_FORMAT( afe_fechaElaboracion,"%d/%m/%Y") afe_fechaElaboracion,
        DATE_FORMAT( afe_fechaInicio,"%d/%m/%Y") afe_fechaInicio,
        DATE_FORMAT( afe_fechaFin,"%d/%m/%Y") afe_fechaFin,
        afe_percepcion as Percepciones,afe_retencion as Retenciones,
        TRUNCATE(afe_percepcion,2) - TRUNCATE(afe_retencion,2) as Importe
        FROM scp_Afectacion
        JOIN enc_Programatico USING(epg_id,gpo_id,ur_id)
        JOIN enc_ClaveFinanciamiento USING(ecf_id)
        JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
        WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . '
        AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . '
        AND clc_id!=0
        order by clc_id,afe_id';

    $sql3 = '
        SELECT gpo_id as Grupo,ur_id as UR,clc_id AS Clc,afe_id as afectacion,numcontrol,tipo_movimiento,if(clc_tipocereCo=1  ,"CERE",if( clc_tipocereCo=2, "CERECO","SIN CERE(CO)"))cereco,clc_cereCo,
        clave,clave_nombre,referencia,fecha_referencia,concepto,rfc_id,nombre,
        percepcion_bruto,percepcion_iva,percepcion_neto,retencion_neto
        FROM scp_Clc
        LEFT JOIN scp_ClcAuxiliar USING(gpo_id,ur_id,clc_id,ecl_id)
        LEFT JOIN scp_Afectacion USING(gpo_id,ur_id,clc_id,ecf_id)
        LEFT JOIN(
            SELECT detalle.gpo_id,detalle.ur_id, clc_id, afe_id,
            ncp_id numcontrol, CONCAT(pre_claveCompleta,tcp_id) clave,pe_nombre clave_nombre,ncp_concepto as concepto,
            ncp_referencia referencia,ncp_fechaReferencia fecha_referencia,
            ncp_percepcionBruto percepcion_bruto,ncp_percepcionIva percepcion_iva,
            ncp_percepcionNeto percepcion_neto, 0 retencion_neto,
            "+" tipo_movimiento,
            rfc_id,CONCAT(rfc_nombre," ",COALESCE(rfc_paterno,"")," ",COALESCE(rfc_materno,"")) AS nombre,
            "P" tipo_numcontrol
            FROM scp_Presupuestal detalle
            JOIN presupuesto USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
            JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
            LEFT JOIN (
                SELECT rfc_id,rfc_nombre,rfc_paterno,rfc_materno
                FROM cat_Proveedor
                INNER JOIN sinpres.cat_Proveedores USING(rfc_id)
            )p USING(rfc_id)
            WHERE detalle.gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' and detalle.ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . ' and clc_id!=0
            UNION
            SELECT detalle.gpo_id,detalle.ur_id, clc_id, afe_id,
                ncc_id numcontrol, CONCAT( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, ".", cta_gpo_id, cta_ur_id,".", cta_ue_id, ".", cgc_id, ".", cta_id ) clave,
                cta_nombre clave_nombre,ncc_concepto as concepto,
                ncc_referencia referencia,ncc_fechaReferencia fecha_referencia,
                0 percepcion_bruto, 0 percepcion_iva,
                ncc_percepcion percepcion_neto,ncc_retencion retencion_neto,
                ncc_tipoMovimiento tipo_movimiento,
                rfc_id,CONCAT(rfc_nombre," ",COALESCE(rfc_paterno,"")," ",COALESCE(rfc_materno,"")) AS nombre,
                "C" tipo_numcontrol
            FROM scp_Contable detalle
            JOIN cg_PlanCuentas USING(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id)
            LEFT JOIN (
                SELECT rfc_id,rfc_nombre,rfc_paterno,rfc_materno
                FROM cat_Proveedor
                INNER JOIN sinpres.cat_Proveedores USING(rfc_id)
            )p USING(rfc_id)
            WHERE detalle.gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' and detalle.ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . ' and clc_id != 0
        )nc USING(gpo_id, ur_id, clc_id, afe_id)
        WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' and ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . ' and clc_id!=0
        ORDER BY gpo_id,ur_id,clc_id,afe_id,numcontrol';

    $sql4 = '
        SELECT cap_id AS Capitulo,cap_nombre as Nombre,cto_id as Concepto,cto_nombre as Nombre,
        pg_id as Partida_Generica,pg_nombre as Nombre,pe_id as Partida_Especifica,pe_nombre as Nombre,
        idp_id as Identificador_Partida ,idp_nombre as Nombre,clg_id as Clase_Gasto,clg_nombre as Nombre
        FROM cat_PartidaGenerica
        INNER JOIN cat_Capitulo USING (cap_id)
        INNER JOIN cat_Concepto USING (cap_id,cto_id)
        INNER JOIN cat_PartidaEspecifica USING (pg_id)
        INNER JOIN cat_IdentificadorPartida USING (idp_id)
        INNER JOIN cat_ClaseGasto USING (clg_id) ;
    ';

    $sql5 = '
        SELECT cfi_clave as Clave_Financiamiento,cfi_nombre as Nombre
        FROM enc_ClaveFinanciamiento
        INNER JOIN cat_ClaveFinanciamiento USING( fte_id, sft_id, ori_id, sor_id,cso_id,cfi_id,cfi_anio)
        WHERE sts_id=1
        order by cfi_clave;
    ';

    $sql6 = '
        select  clc_gpo_id, clc_ur_id, clc_id, op_id, op_concepto, op_importe, op_fechaGeneracion,op_beneficiario,concat("*",bansf_id) as ctaban,
        ececo_id, lotco_id, ing_referenciaPago, ing_tipo, ing_lote, ing_fecha, ing_fechaPago
        from scp_Cerecos
        WHERE clc_gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and clc_ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '"
    ';

    $sql7 = '
        SELECT ncc_id, op_id, ncc_percepcionImporteNeto, ncc_retencionImporteNeto,ncc_tipoMovimiento,concat("*",ncc_clave) as clave
        from scp_ControlContableCereco a
        inner join scp_Cerecos using (op_id)
        WHERE a.clc_gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and a.clc_ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '"
    ';

    $sql8 = '
        select mod_gpo_id,mod_ur_id,mod_id,mod_fechaFolio,mod_fechaAutorizacion,mod_oficioSolicitudSefin,mod_concepto,emo_nombre,mod_oficioSefin,omo_id
        from scp_MaestroModificacion
        INNER JOIN enc_OrigenOperacion using(omo_id,enc_oop)
        INNER JOIN cat_OrigenModificacion using(omo_id)
        INNER JOIN cat_EstadoModificacion using(emo_id)
        INNER JOIN enc_ClaveFinanciamiento using(ecf_id)
        JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cf USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
        WHERE mod_gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and mod_ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '"
        order by omo_id,mod_gpo_id,mod_ur_id,mod_id
    ';

    $sql9 = '
        select mod_gpo_id,mod_ur_id,mod_id,gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,
        pg_id,pe_id,cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,tcp_id,pre_claveCompleta,mov_id,omo_id,
        dmo_importe,dmo_ene,dmo_feb,dmo_mar,dmo_abr,dmo_may,dmo_jun,dmo_jul,dmo_ago,dmo_sep,dmo_oct,dmo_nov,dmo_dic
        from scp_MaestroModificacion
        INNER JOIN scp_DetalleModificacion d using(omo_id,mod_gpo_id,mod_ur_id,mod_id,ecf_id)
        INNER JOIN presupuesto using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        WHERE mod_gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and mod_ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '"
        order by omo_id,mod_gpo_id,mod_ur_id,mod_id;
    ';

    $sql10 = '
        select gpo_id ,ur_id,ue_id,tcp_id,pre_claveCompleta as Clave,
        pre_aprobado as Aprobado,pre_ampliacion as ampliacion,pre_reduccion as reduccion,
        pre_vigente as vigente,pre_comprometido as comprometido,pre_devengado as devengado,pre_ejercido as ejercido,
        ori_ene,ori_feb,ori_mar,ori_abr,ori_may,ori_jun,ori_jul,ori_ago,ori_sep,ori_oct,ori_nov,ori_dic,
        apr_ene,apr_feb,apr_mar,apr_abr,apr_may,apr_jun,apr_jul,apr_ago,apr_sep,apr_oct,apr_nov,apr_dic,
        vig_ene,vig_feb,vig_mar,vig_abr,vig_may,vig_jun,vig_jul,vig_ago,vig_sep,vig_oct,vig_nov,vig_dic,
        com_ene,com_feb,com_mar,com_abr,com_may,com_jun,com_jul,com_ago,com_sep,com_oct,com_nov,com_dic,
        dev_ene,dev_feb,dev_mar,dev_abr,dev_may,dev_jun,dev_jul,dev_ago,dev_sep,dev_oct,dev_nov,dev_dic,
        eje_ene,eje_feb,eje_mar,eje_abr,eje_may,eje_jun,eje_jul,eje_ago,eje_sep,eje_oct,eje_nov,eje_dic,
        pag_ene,pag_feb,pag_mar,pag_abr,pag_may,pag_jun,pag_jul,pag_ago,pag_sep,pag_oct,pag_nov,pag_dic
        FROM presupuesto
        JOIN presupuestoAprobado USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        JOIN presupuestoVigente USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        JOIN presupuestoComprometido USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        JOIN presupuestoDevengado USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        JOIN presupuestoEjercido USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        JOIN presupuestoOriginal USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        JOIN presupuestoPagado USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' and ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . ' and tcp_id!="P"
        ORDER BY pre_claveCompleta
    ';

    $sql11 = '
        select banur_id, gpo_id, ur_id, iban_id, ucb_id, tcb_id, banur_ctaban, banur_alias, banur_beneficiario,
        banur_plaza, banur_sucursal, banur_sp, banur_go, banur_od, banur_oc, banur_pm, banur_ot, banur_descripcion,
        banur_fechaAlta, banur_titular1, banur_cargo1, banur_titular2, banur_cargo2, eban_id, ger_id, cgp_id, rub_id,
        cgcu_id, cgsc_id, ue_id, cgc_cuarto, cta_id, cta_cuenta
        from cat_BancosUr
        WHERE gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '"
        ORDER BY banur_id
    ';

    $sql12 = '
        SELECT 	s.cff_id, s.fte_id, s.sft_id, s.ori_id, s.sor_id, s.cfi_id, s.cfi_anio, s.cfi_nombre, s.cfi_ejercicio, s.cfi_clave
        FROM sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' AS s INNER JOIN enc_ClaveFinanciamiento AS fce ON s.cff_id = fce.cff_id AND s.fte_id = fce.fte_id
        AND s.sft_id = fce.sft_id AND s.ori_id = fce.ori_id AND s.sor_id = fce.sor_id AND s.cfi_id = fce.cfi_id AND s.cfi_anio = fce.cfi_anio
        AND s.cfi_clave = fce.ecf_clave;
    ';

    $sql13 = '
        SELECT gpo_id,ur_id,ue_id,ue_clave,ue_nombre,ue_nombreTitular,ue_cargoTitular,ue_usuarioValidadorUr,ue_validacionMensajeUr,
        ue_validacionMensajeSefin,usu_fecha,eue_id,ue_usuarioValidadorSefin,ue_objetivo
        FROM cat_UnidadEjecutora c
        WHERE gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '"
        order by gpo_id,ur_id,ue_id;
    ';

    $sql14 = '
        SELECT gpo_id,ur_id,py_id,eje_id,sp_id,sp_id,prg_id,tpy_id,py_nombre,py_objetivo,py_fechaIngreso,usu_id,usu_fecha,py_usuarioValidador,
        py_validacionMensajeUr,py_validacionMensajeSefin,epy_id,ie_id,py_proyecto2011,py_origen
        FROM cat_Proyecto c
        WHERE gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '"
        order by gpo_id,ur_id,py_id ;
    ';

    $sql15 = '
        SELECT ur_id, gpo_id, py_id, oa_id, eje_id, ue_id, ubi_id, zon_id, gpv_id, oa_nombre,oa_objetivo,oa_hombres
        oa_mujeres, oa_tipoObra,pol_id, ep_id,oa_mensajeSefin, shcp_haySolicitud, shcp_oficioSolicitud, shcp_fechaSolicitud,
        shcp_fondo, shcp_importeCartera, shcp_oficioAutorizacion, shcp_fechaAutorizacion, shcp_importeAutorizado,
        cop_oficioAutorizacion,cop_fechaAutorizacion, cop_oficioLiberacion,cop_fechaLiberacion,cop_fechaLimiteEjecucion,
        cop_anioAutorizacion, cop_accionOrigen, cop_numObra, shcp_numProy, oa_2012, oa_origen,cop_jornales, etn_id,
        reg_id, dto_id,mun_id, loc_id, mrg_id, cob_id, oa_anticipo2012, oa_anticipo, oa_amortiza
        from cat_ObraAccion
        WHERE gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '"
        order by gpo_id,ur_id,py_id,oa_id,ue_id
    ';

    $sql16 = '
        SELECT 	pe_id, 	pg_id, 	idp_id, pe_nombre, 	pe_descripcion, pe_comprasDirectas, tg_id, og_id, pe_cedula, tno_id, pe_clave, pe_reductible
        FROM cat_PartidaEspecifica
        ORDER BY pe_id, pg_id;
    ';

    $sql17 = '
        SELECT pg_id,cto_id,pg_nombre,pg_clave,pg_descripcion,pg_tipo
        from cat_PartidaGenerica
        order by pg_id,cto_id;
    ';

    $sql18 = '
        SELECT ger_id,cgp_id,rub_id, cgcu_id, cgsc_id, gpo_id, ur_id, ue_id, cgc_cuarto,cta_id,concat("*", cta_cuenta ) as cuenta ,cta_nombre,
        cta_fijarNaturaleza, agc_id
        from cg_PlanCuentas
        WHERE gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '"
        order by cuenta
    ';

    $sql19 = '
        SELECT rect.gpo_id,rect.ur_id,rct_id,clc_id,clave,numcontrol,rfc_id,concat(rfc_nombre," ",coalesce(rfc_paterno,"")," ",coalesce(rfc_materno,"")) as nombre,
        rct_justificacion,rct_fechaComprobante,rct_numeroComprobante,rct_TipoReintegro,ecl_id,
        sum(coalesce(percepcion, 0)) as percepcion,
        sum(coalesce(retenciones, 0)) as retenciones,
        sum(coalesce(percepcion, 0)) - sum(coalesce(retenciones, 0)) as total
        from scp_MaestroRectificacion
        join
        (
            SELECT r.gpo_id,r.ur_id,rct_id,r.afe_id,ncp_id as numcontrol,r.ncp_referencia as factura,r.rfc_id,ncp_clave as clave,
            coalesce(r.ncp_percepcionImporteNeto, 0) as percepcion,0 retenciones,r.iva_id as iva
            FROM scp_RectificacionPresupuestal r
            join scp_NumControlPresupuestal using(ncp_id)
            where r.gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and r.ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '"
            union
            SELECT rec.gpo_id,rec.ur_id,rec.rct_id,rec.afe_id,ncc_id as numcontrol,rec.ncc_referencia as factura,rec.rfc_id,ncc_clave as clave,
                coalesce(rec.ncc_percepcionesImporteNeto, 0) as percepcion,coalesce(rec.ncc_retencionesImporteNeto, 0) retenciones,0 iva
            FROM scp_RectificacionContable rec
            join scp_NumControlContable using(ncc_id)
            where rec.gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and rec.ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '"

        )rect USING(gpo_id,ur_id,rct_id)
        JOIN cat_Proveedores USING(rfc_id)
        group by gpo_id,ur_id,rct_id,numcontrol
        order by rct_id;
    ';

    $sql20 = '
        SELECT gpo_id,ur_id,rct_id,clc_id,rct_justificacion,rct_fechaComprobante,rct_numeroComprobante,
        rct_tipoReintegro,ecl_id,rct_fechaElaboracion
        from scp_MaestroRectificacion
        where gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '"
        order by rct_id;
    ';

    $sql21 = '
        SELECT ram_id, ram_nombre from cat_Ramo
        order by ram_id
    ';

    $sql22 = '
        SELECT sec_id, ram_id, sec_nombre, sec_urCabeza
        from cat_SectorProductivo
        order by sec_id;
    ';

    $sql23 = '
        SELECT sad_id,sad_nombre,sad_urCabeza,usu_id,usu_fecha
        FROM cat_SectorAdministrativo c
        order by sad_id;
    ';

    $sql24 = '
        SELECT eje_id,eje_nombre,usu_id,usu_fecha,eje_descripcion
        FROM cat_Eje c
        order by eje_id;
    ';

    $sql25 = '
        SELECT prg_id,eje_id,prg_nombre,usu_id,usu_fecha,prg_descripcion
        FROM cat_Programa c
        order by prg_id,eje_id;
    ';

    $sql26 = '
        SELECT sp_id,eje_id,prg_id,sp_nombre,sp_descripcion
        FROM cat_Subprograma c
        order by sp_id,eje_id,prg_id;
    ';

    $sql27 = '
        SELECT fin_id,fin_nombre,fin_descripcion
        FROM cat_Finalidad c
        order by fin_id;
    ';

    $sql28 = '
        SELECT fun_id,fin_id,fun_nombre,fun_descripcion
        FROM cat_Funcion c
        order by fun_id,fin_id;
    ';

    $sql29 = '
        SELECT sfc_id,fin_id,fun_id,sfc_nombre,sfc_descripcion
        FROM cat_Subfuncion c
        order by sfc_id,fin_id,fun_id;';

    $sql30 = '
        SELECT cap_id,cap_nombre,cap_clave,cap_descripcion
        FROM cat_Capitulo c
        order by cap_id;
    ';

    $sql31 = '
        SELECT cto_id,cap_id,cto_nombre,cto_clave,cto_descripcion
        FROM cat_Concepto c
        order by cto_id;
    ';

    $sql32 = '
        SELECT tg_id,tg_nombre
        FROM cat_TipoGasto c
        order by tg_id;
    ';

    $sql33 = '
        SELECT tg_id,og_id,og_nombre
        FROM cat_ObjetoGasto c
        order by tg_id,og_id;
    ';

    $sql34 = '
        SELECT ecl_id, ecl_nombre, ecl_descripcion, ecl_color
        from cat_EstadoClc
        order by ecl_id
    ';

    $sql35 = '
        SELECT tclc_id, tclc_nombre, tclc_alias
        from cat_TipoCedula
    ';

    $sql36 = '
        SELECT tfm_id,tfm_nombre
        FROM cat_TipoFolioModificacion
        order by tfm_id;
    ';

    $sql37 = '
        SELECT tmd_id,om_id,tmd_nombre
        FROM cat_TipoModificacion
        order by om_id
    ';

    $sql38 = '
        SELECT om_id,om_nombre
        FROM cat_OrigenModificacion
        order by om_id;
    ';

    $sql39 = '
        SELECT tiponomina,descr
        FROM cat_tiponomina
        order by tiponomina;
    ';

    $sql40 = '
        SELECT soc_id, soc_nombre, soc_descripcion
        from cat_SociedadProveedores
        order by soc_id;
    ';

    $sql41 = '
        SELECT rfc_id, gpo_id, ur_id, tcy_id, rfc_nombre, rfc_paterno, rfc_materno, soc_id, rfc_curp,
        rfc_calle, rfc_numeroExterno, rfc_numeroInterno, rfc_colonia, rfc_localidad,edo_id, mpio_id, rfc_referencia,
        rfc_cp, rfc_telefono, rfc_email
        from cat_Proveedores
        where gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '"
        ORDER BY rfc_id
    ';

    $sql42 = '
        SELECT iban_id,iban_nombre
        from cat_InstitucionesBancarias
        order by iban_id
    ';

    $sql43 = '
        SELECT fte_id, sft_id,ori_id,sor_id,sor_nombre
        FROM cat_SuborigenRecurso
        order by fte_id;
    ';

    $sql44 = '
        SELECT fte_id, sft_id,ori_id,ori_nombre
        FROM cat_OrigenRecurso
        order by fte_id;
    ';

    $sql45 = '
        SELECT fte_id,sft_id,sft_nombre
        FROM cat_Subfuente
        order by fte_id;
    ';

    $sql46 = '
        SELECT fte_id,fte_nombre
        FROM cat_Fuente
        order by fte_id;
    ';

    $sql47 = '
        SELECT idp_id, idp_nombre
        FROM cat_IdentificadorPartida
        order by idp_id;
    ';

    $sql48 = '
        SELECT um_id, um_descripcion
        FROM cat_UnidadMedida
        order by um_id;
    ';

    $sql49 = '
        SELECT um_id,tm_id,tm_nombre
        FROM cat_TipoMedida
        order by tm_id;
    ';

    $sql50 = '
        SELECT ap_id,ap_nombre
        FROM cat_AccionProgramada
        ORDER BY ap_id;
    ';

    $sql51 = '
        SELECT zon_id,zon_nombre
        FROM cat_ZonaAplicacion
        order by zon_id;
    ';

    $sql52 = '
        SELECT gpv_id,gpv_nombre
        FROM cat_GrupoVulnerable
        order by gpv_id;
    ';

    $sql53 = '
        SELECT pt_id,pt_nombre,pt_descripcion
        FROM cat_PoliticasTransversales
        order by pt_id;
    ';

    $sql54 = '
        SELECT ie_id,eje_id,prg_id,sp_id,ie_nombre,ie_metodoCalculo,ie_fuenteInformacion,ie_resultadoExpresado,
        ie_unidadMedida,ie_dimension,ie_tipoIndicador,ie_frecuenciaMedicion,ie_sentidoEsperado
        FROM sed_IndicadorEstrategico
        order by ie_id;
    ';

    $sql55 = '
        SELECT ig_id, gpo_id, ur_id, py_id, oa_id, em_id, tm_id,um_id, ap_id, am_id, ae_id, ig_nombre,ig_resultadoEsperado,
        ig_anio,ig_responsable, ig_cargoResponsable,ig_eneOriginal, ig_febOriginal, ig_marOriginal, ig_abrOriginal,
        ig_mayOriginal, ig_junOriginal, ig_julOriginal,ig_agoOriginal,ig_sepOriginal, ig_octOriginal, ig_novOriginal,
        ig_dicOriginal,ig_eneModificado,ig_febModificado, ig_marModificado,ig_abrModificado,ig_mayModificado,ig_junModificado,	ig_julModificado,ig_agoModificado,ig_sepModificado,ig_octModificado,ig_novModificado,ig_dicModificado,ig_eneAlcanzado,ig_febAlcanzado,ig_marAlcanzado,ig_abrAlcanzado,ig_mayAlcanzado,ig_junAlcanzado,ig_julAlcanzado,ig_agoAlcanzado,ig_sepAlcanzado,ig_octAlcanzado,ig_novAlcanzado,ig_dicAlcanzado,ig_eneAvanceFisico,ig_febAvanceFisico,ig_marAvanceFisico,ig_abrAvanceFisico,ig_mayAvanceFisico,ig_junAvanceFisico,ig_julAvanceFisico,ig_agoAvanceFisico,ig_sepAvanceFisico,ig_octAvanceFisico,ig_novAvanceFisico,ig_dicAvanceFisico,ig_descripcionMeta, ig_totalOriginal,ig_trimestre,ig_statusTrimestreUno,ig_statusTrimestreDos,ig_statusTrimestreTres,ig_statusTrimestreCuatro,ig_status
        from sed_IndicadorGestion
        where gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '"
    ';

    $sql56 = '
        SELECT rfc_id, concat(rfc_nombre," ",coalesce(rfc_paterno,"")," ",coalesce(rfc_materno,"")) as nombre,clc_id,afe_id,numcontrol,referencia,fecharefe,concepto,
        clave, tipo,iva,percepciones,retenciones,otb_id,op_id
        FROM scp_Cedula
        JOIN scp_Afectacion
        USING(clc_gpo_id, clc_ur_id,clc_id)
        JOIN(
        SELECT clc_gpo_id,clc_ur_id,rfc_id, ncp_id numcontrol, afe_id, ncp_clave clave,
        ncp_concepto concepto,ncp_referencia referencia, ncp_fechaReferencia fecharefe, TIPO tipo, iva_id iva,
        ncp_percepcionImporteNeto as percepciones, 0 retenciones
        from scp_NumControlPresupuestal
        where clc_gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and clc_ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '"
        union
        SELECT clc_gpo_id, clc_ur_id, rfc_id, ncc_id numcontrol, afe_id, ncc_clave clave,
        ncc_concepto concepto, ncc_referencia referencia, ncc_fechaReferencia fecharefe,"" tipo,0.00 iva,
        ncc_percepcionesImporteNeto as percepciones,ncc_retencionesImporteNeto retenciones
        from scp_NumControlContable
        where clc_gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and clc_ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '"
        )nc
        USING(clc_gpo_id, clc_ur_id,afe_id)
        JOIN cat_Proveedores
        USING(rfc_id)
        where clc_gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and clc_ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '"
        order by clc_id,afe_id asc
    ';

    //Consulta para el catálogo cat_Estrategia.
    $sql57 = '
        SELECT est_id,prg_id,est_nombre,eje_id,tem_id,obj_id
        from cat_Programa
        JOIN cat_Eje using(eje_id)
        JOIN cat_Tema b USING(eje_id,tem_id)
        JOIN cat_Objetivo c USING(eje_id,tem_id,obj_id)
        join cat_Estrategia using(eje_id,tem_id,obj_id)
        where gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '";
    ';
    //Consulta para el catálogo de linea de acción.
    $sql58 = '
        SELECT ep.lac_id, e.lac_nombre, ep.epe_id,ep.eje_id,ep.tem_id,ep.obj_id,ep.est_id
        FROM cat_Programa p
        JOIN cat_Eje using(eje_id)
        JOIN cat_Tema b USING(eje_id,tem_id)
        JOIN cat_Objetivo c USING(eje_id,tem_id,obj_id)
        JOIN cat_Estrategia d USING(eje_id,tem_id,obj_id)
        JOIN cat_LineaAccion e USING(eje_id,tem_id,obj_id,est_id)
        JOIN enc_Ped ep USING(eje_id,tem_id,obj_id,est_id,lac_id)
        where gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '"
        GROUP BY lac_id;
    ';
    //consulta para el catálogo de eje transversal
    $sql59 = '
        SELECT tet_id,tet_nombre FROM cat_TemaTransversal;
    ';

    $sql60 = '
        SELECT obt_id,obt_nombre FROM cat_ObjetivoTransversal;
    ';

    $sql61 = '
        SELECT tes_id,tes_nombre from cat_EstrategiaTransversal;
    ';

    $sql62 = '
        select gpo_id ,ur_id,presupuesto.ue_id,ue_nombre,prg_id as Programa ,prg_nombre,spr_id as Subprograma,spr_nombre,
        py_id as Proyecto,py_nombre,oa_id AS Obra_Actividad,oa_nombre,if(toa_id=2 and oa_origen!="S",oa_' . APP_EJERCICIO_ANTERIOR . ',"") NumObra,cfi_clave as Clave_Financiamiento,cfi_Nombre as nombre,pg_id as Partida_Generica,pg_nombre,
        pe_id AS Partida_Especifica,pe_nombre,tcp_id as Tipo_Compra,idp_id Identificador_Partida,idp_nombre,clg_id as Clase_Gasto,clg_nombre,pre_claveCompleta as Clave_Presupuestal,
        pre_aprobado as Aprobado,pre_ampliacion as Ampliacion,pre_reduccion as Reduccion,
        pre_vigente as Vigente,pre_comprometido as Comprometido,pre_devengado as Devengado,pre_ejercido as Ejercido,
        ori_ene,ori_feb,ori_mar,ori_abr,ori_may,ori_jun,ori_jul,ori_ago,ori_sep,ori_oct,ori_nov,ori_dic,
        apr_ene,apr_feb,apr_mar,apr_abr,apr_may,apr_jun,apr_jul,apr_ago,apr_sep,apr_oct,apr_nov,apr_dic,
        vig_ene,vig_feb,vig_mar,vig_abr,vig_may,vig_jun,vig_jul,vig_ago,vig_sep,vig_oct,vig_nov,vig_dic,
        com_ene,com_feb,com_mar,com_abr,com_may,com_jun,com_jul,com_ago,com_sep,com_oct,com_nov,com_dic,
        dev_ene,dev_feb,dev_mar,dev_abr,dev_may,dev_jun,dev_jul,dev_ago,dev_sep,dev_oct,dev_nov,dev_dic,
        eje_ene,eje_feb,eje_mar,eje_abr,eje_may,eje_jun,eje_jul,eje_ago,eje_sep,eje_oct,eje_nov,eje_dic,
        pag_ene,pag_feb,pag_mar,pag_abr,pag_may,pag_jun,pag_jul,pag_ago,pag_sep,pag_oct,pag_nov,pag_dic
        FROM presupuesto
        JOIN presupuestoAprobado USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        JOIN presupuestoVigente USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        JOIN presupuestoComprometido USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        JOIN presupuestoDevengado USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        JOIN presupuestoEjercido USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        JOIN presupuestoOriginal USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        JOIN presupuestoPagado USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        INNER JOIN cat_UnidadEjecutora using(gpo_id,ur_id,ue_id)
        INNER JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
        INNER JOIN cat_Subprograma USING(gpo_id,ur_id,prg_id,spr_id)
        INNER JOIN cat_Proyecto USING(gpo_id,ur_id,prg_id,spr_id,py_id)
        INNER JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
        INNER JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
        INNER JOIN cat_PartidaGenerica USING(pg_id)
        INNER JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
        INNER JOIN cat_ClaseGasto USING ( clg_id )
        INNER JOIN cat_IdentificadorPartida using(idp_id)
        where gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" and ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '" and tcp_id!="P"
        ORDER BY pre_claveCompleta;
    ';

    //INDICADORES
    $sql63 = '
        SELECT nim_id AS ID_NIVEL_INDICADOR, nim_nombre AS NIVEL_DE_INDICADOR,
        ind_id AS ID_INDICADOR, ind_nombre AS NOMBRE_INDICADOR,
        ind_definicion AS DEFINICION_INDICADOR, ind_formulaCalculo AS METODO_CALCULO,
        ind_objetivo AS OBJETIVO, tpi_id AS ID_TIPO_INDICADOR,
        tpi_nombre AS TIPO_INDICADOR, dim_id AS ID_DIMENSION,
        dim_nombre AS DIMENSION, frm_id AS ID_FRECUNCIA_MEDICION,
        frm_nombre AS FRECUENCIA_MEDICION, ses_id AS ID_SENTIDO,
        ses_nombre AS SENTIDO, tiv_id AS ID_TIPO_CALCULO, tiv_nombre AS TIPO_CALCULO,
        i.umi_id AS ID_UNIDAD_MEDIDA,     /*columna r*/
        u.umi_nombre AS UNIDAD_MEDIDA,   /*columna s*/ ind_nombreMIR AS NOMBRE_INDICADOR_MIR,
        ind_definicionMIR AS DEFINICION_MIR, umv.umv_id AS ID_UNIDAD_MEDIDA_VARIABLE,     /*columna v*/
        umv.umv_nombre AS UNIDAD_MEDIDA_VARIABLE, /*columna w*/
        ind_limInfVer AS LIMITE_INF_VERDE, ind_limInfAmar AS LIM_INF_AMARILLO,
        ind_limSupAmar AS LIM_SUP_AMARILLO, ind_limSupRojo AS LIMITE_SUP_ROJO,
        IF (
            sts_id = 1,
            "HABILITADO",
            "INHABILITADO"
        ) AS ESTADO, DATE(i.usr_Fecha) AS FEHA_CREACION
        FROM
            cat_Indicador i
        INNER JOIN cat_NivelMir USING (nim_id)
        INNER JOIN cat_TipoIndicador USING (tpi_id)
        INNER JOIN cat_Dimension USING (dim_id)
        INNER JOIN cat_FrecuenciaMedicion USING (frm_id)
        INNER JOIN cat_SentidoEsperado USING (ses_id)
        INNER JOIN cat_TipoValor USING (tiv_id)
        INNER JOIN cat_UnidadMedidaIndicador u USING (umi_id)
        INNER JOIN cat_UnidadMedidaVariable umv USING (umv_id) /*ON umv.umv_id = i.umi_id*/
        ORDER BY
        nim_id;
    ';
    //Productos Finales indicador
    $sql64 = '
        SELECT concat(gpo_id,ur_id) as GPO, ur_nombre as DENOMINACION_UR, prgs_id as ID_PRG, prg_nombre as DENOMINACION_PROGRAMA, spr_id as ID_SPR, spr_nombre AS DENOMINACION_SUBPROGRAMA,
        pdf_id as ID_FINAL, pdf_nombre as PF_NOMBRE, nim_id AS ID_NIVEL, ind_id as ID_INDICADOR, ind_nombre AS NOMBRE_INDICADOR, i.umi_id as ID_UNIDAD_MEDIDA, u.umi_nombre AS UNIDAD_MEDIDA, i.umv_id as ID_UNIDAD_MEDIDA_VARIABLE, umir.umv_nombre as UNIDAD_MEDIDA_VARIABLE
        FROM
        vin_UrProgramaMir
        INNER JOIN(
            SELECT gpo_id,ur_id, prg_id AS prgs_id, prg_nombre
            FROM cat_Programa
        )pro USING(gpo_id,ur_id,prgs_id)
        INNER JOIN cat_Programas USING(prgs_id)
        INNER JOIN(
            SELECT gpo_id,ur_id, prg_id AS prgs_id, pdf_id, spr_id,spr_nombre
            FROM cat_Subprograma
        )sub USING(gpo_id,ur_id,prgs_id)
        INNER JOIN enc_ProductoFinalIndicador enc USING(pdf_id)
        INNER JOIN cat_ProductoFinal USING(pdf_id)
        INNER JOIN cat_Indicador i USING(nim_id,ind_id)
        INNER JOIN cat_UnidadMedidaIndicador u USING(umi_id)
        INNER JOIN cat_UnidadMedidaVariable umir ON umir.umv_id=i.umv_id
        INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
        WHERE nim_id=3 AND pdf_id!=0 AND epr_id=4
        AND enc.sts_id=1
        ORDER BY prgs_id;
    ';
    //Productos intermedios indicador
    $sql65 = '
        SELECT concat(gpo_id,ur_id) as GPO, ur_nombre as DENOMINACION_UR, prgs_id as ID_PRG, prg_nombre as DENOMINACION_PROGRAMA, spr_id as ID_SPR, spr_nombre AS DENOMINACION_SUBPROGRAMA,
        pdf_id as ID_FINAL, pdf_nombre as PF_NOMBRE, nim_id AS ID_NIVEL, pdi_id as ID_PI, pdi_nombre as PDI_NOMBRE, nim_id AS ID_NIVEL,
        ind_id as ID_INDICADOR, ind_nombre AS NOMBRE_INDICADOR,
        i.umi_id as ID_UNIDAD_MEDIDA, u.umi_nombre AS UNIDAD_MEDIDA, i.umv_id as ID_UNIDAD_MEDIDA_VARIABLE, umir.umv_nombre as UNIDAD_MEDIDA_VARIABLE
        FROM vin_UrProgramaMir
        INNER JOIN(
            SELECT gpo_id,ur_id, prg_id AS prgs_id, prg_nombre
            FROM cat_Programa
        )pro USING(gpo_id,ur_id,prgs_id)
        INNER JOIN cat_Programas USING(prgs_id)
        INNER JOIN(
            SELECT gpo_id,ur_id, prg_id AS prgs_id, pdf_id, spr_id,spr_nombre
            FROM cat_Subprograma
        )sub USING(gpo_id,ur_id,prgs_id)
        INNER JOIN vin_ProductoFinalProductoIntermedio pi USING(pdf_id)
        INNER JOIN vin_ProductoIntermedioIndicador  vin USING(pdf_id,pdi_id)
        INNER JOIN cat_ProductoFinal USING(pdf_id)
        INNER JOIN cat_ProductoIntermedio USING(pdi_id)
        INNER JOIN cat_Indicador i USING(nim_id,ind_id)
        INNER JOIN cat_UnidadMedidaIndicador u USING(umi_id)
        INNER JOIN cat_UnidadMedidaVariable umir ON umir.umv_id=i.umv_id
        INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
        WHERE pdi_id!=0 AND nim_id=4 AND prgs_id!=0 AND epr_id=4
        AND vin.sts_id=1 and pi.sts_id=1
        ORDER BY prgs_id;
    ';

    //REPORTE MIR
    $sql66 = '
        select prgs_id ID_PRG, prgs_nombre NOMBRE_DEL_PROGRAMA, concat(gpo_id,ur_id) UR_ID, ur_nombre NOMBRE, emi_nombre as ESTATUS_UR
        from vin_UrProgramaMir
        inner join cat_Programas using(prgs_id)
        inner join cat_UnidadResponsable using(gpo_id, ur_id)
        inner join cat_EstadoMatrizIndicador using(emi_id)
        where prgs_id between 100 and 899
        order by prgs_id, gpo_id, ur_id;
    ';
    //CATALOGO PRODUCTOS FINALES
    $sql67 = '
        SELECT pdf_id AS ID_PDF, pdf_nombre AS NOMBRE_SINGULAR, pdf_abreviatura AS ABREVIATURA,
        pdf_plural AS NOMBRE_PLURAL, pdf_mir AS NOMBRE_MIR, IF(sts_id=1, "HABILITADO", "INHABILITADO") AS ESTADO, DATE(usr_fecha) AS FECHA_CREACION
        FROM cat_ProductoFinal
        WHERE pdf_id!=0;
    ';

    //CATALOGO PRODUCTOS INTERMEDIOS
    $sql68 = '
        SELECT pdi_id AS ID_PDI, pdi_nombre AS NOMBRE_SINGULAR, pdi_abreviatura AS ABREVIATURA,
        pdi_plural AS NOMBRE_PLURAL, pdi_mir AS NOMBRE_MIR, IF(sts_id=1, "HABILITADO", "INHABILITADO") AS ESTADO, DATE(usr_fecha) AS FECHA_CREACION
        FROM cat_ProductoIntermedio;
    ';

    $sql69 = '
        SELECT  CONCAT(gpo_id,ur_id) as UR,CONCAT("\'",lpad(ue_id,3,"0")) as UE,ue_nombre as NOMBRE,ue_nombreTitular as NOMBRE_TITULAR,
        ue_cargoTitular AS CARGO_TITULAR,ue_objetivo AS OBJETIVO
        FROM  cat_UnidadEjecutora
        WHERE gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" AND ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '";
    ';

    $sql70 = '
        SELECT CONCAT("\'",lpad(prg_id,3,"0")) as PROGRAMA,prg_nombre as NOMBRE
        FROM cat_Programa
        WHERE gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" AND ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '";
    ';

    $sql71 = '
        SELECT  CONCAT("\'",lpad(prg_id,3,"0")) as PROGRAMA,CONCAT("\'",lpad(spr_id,2,"0")) as SUBPROGRAMA,spr_nombre as NOMBRE
        FROM cat_Subprograma
        WHERE gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" AND ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '";
    ';

    $sql72 = '
        SELECT  CONCAT("\'",lpad(prg_id,3,"0")) as PROGRAMA,CONCAT("\'",lpad(spr_id,2,"0")) as SUBPROGRAMA,CONCAT("\'",lpad(py_id,3,"0")) as PROYECTO,py_nombre as NOMBRE
        FROM cat_Proyecto
        WHERE gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" AND ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '";
    ';

    $sql73 = '
        SELECT  CONCAT("\'",lpad(prg_id,3,"0")) as PROGRAMA,CONCAT("\'",lpad(spr_id,2,"0")) as SUBPROGRAMA,CONCAT("\'",lpad(py_id,3,"0")) as PROYECTO,CONCAT("\'",lpad(ue_id,3,"0")) as UE,CONCAT("\'",lpad(oa_id,3,"0")) as OBRA,oa_nombre as NOMBRE
        FROM cat_ObraActividad
        WHERE gpo_id="' . $_SESSION[_S]['clasAdm-gpo'] . '" AND ur_id="' . $_SESSION[_S]['clasAdm-ur'] . '" AND oa_id!=0;
    ';

    //Reporte Productos finales con información MIR
    $sql74 = '
        SELECT  ur_clave AS ID_UR, ur_nombre AS UNIDAD_RESPONSABLE, prgs_id AS ID_PRG, prg_nombre AS PROGRAMA, spr_id AS ID_SPR, spr_nombre AS SUBPROGRAMA,
        pdf_id AS ID_PF, pdf_plural AS PRODUCTO_FINAL, ind_id AS ID_IND, ind_nombreMIR AS INDICADOR_NOMBRE_MIR, ind_definicionMIR AS INDICADOR_DEFINICION_MIR,
        -- i.umi_id as ID_UNIDAD_MEDIDA, u.umi_nombre AS UNIDAD_MEDIDA,
        umir.umv_id AS ID_UNIDAD_MEDIDA, umir.umv_nombre AS UNIDAD_MEDIDA,
        CASE WHEN umi_id=5 THEN mco_anterior ELSE mco_lb END AS AÑO_LINEA_BASE,
        CASE WHEN umi_id=5 THEN mco_lbIndicador ELSE mco_lbValor END AS VALOR_LINEA_BASE,
        CASE WHEN umi_id=5 THEN mco_anteriorIndicador ELSE mco_original END AS META,
        mvcm_nombre AS MEDIO_VERIFICACION, spc_nombre AS SUPUESTOS
        FROM  cat_MetaComponente
        -- inner JOIN cat_MetaComponenteTrimestre using (gpo_id,ur_id,prgs_id,sprs_id,epfi_id)
        INNER JOIN enc_ProductoFinalIndicador epf USING(epfi_id)
        INNER JOIN(
            SELECT vpm_id, gpo_id, ur_id, prgs_id
            FROM vin_UrProgramaMir
        )v USING( gpo_id, ur_id, prgs_id)
        INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN(
            SELECT gpo_id,ur_id, prg_id AS prgs_id, prg_nombre
            FROM cat_Programa
        )pro USING(gpo_id,ur_id,prgs_id)
        INNER JOIN cat_Programas USING(prgs_id)
        INNER JOIN(
            SELECT gpo_id,ur_id, prg_id AS prgs_id, pdf_id, spr_id,spr_nombre
            FROM cat_Subprograma
        )sub USING(gpo_id,ur_id,prgs_id,pdf_id)
        INNER JOIN cat_ProductoFinal USING(pdf_id)
        INNER JOIN cat_Indicador i USING(nim_id,ind_id)
        INNER JOIN cat_UnidadMedidaIndicador u USING(umi_id)
        INNER JOIN cat_UnidadMedidaVariable umir ON umir.umv_id=i.umv_id
        LEFT JOIN(
            SELECT gpo_id, ur_id, prgs_id, sprs_id, mvcm_nombre
            FROM cat_MedioVerificacionComponente
        )medios USING(gpo_id, ur_id, prgs_id, sprs_id)
        LEFT JOIN(
            SELECT gpo_id, ur_id, prgs_id, sprs_id, spc_nombre
            FROM cat_SupuestoComponente
        )sup USING(gpo_id, ur_id, prgs_id, sprs_id)
        WHERE  epf.sts_id=1;
    ';

    //Reporte Productos intermedios con informacion MIR
    $sql75 = '
        SELECT  pii_id,ur_clave AS ID_UR, ur_nombre AS UNIDAD_RESPONSABLE, sb.prgs_id AS ID_PRG, prg_nombre AS PRG_NOMBRE, spr_id AS ID_SPR, spr_nombre AS SUBPROGRAMA,
        pdf_id AS ID_PF,pdf_plural AS PRODUCTO_FINAL, pdi_id AS ID_PI, pdi_plural AS PRODUCTO_INTERMEDIO, ind_id AS ID_IND, ind_nombre AS NOMBRE_INDICADOR,
        ind_definicion AS DEFINICION_INDICADOR,
        -- i.umi_id as ID_UNIDAD_MEDIDA, u.umi_nombre AS UNIDAD_MEDIDA,
        umir.umv_id AS ID_UNIDAD_MEDIDA, umir.umv_nombre AS UNIDAD_MEDIDA,
        MAX(macm_anio) AS AÑO_LINEA_BASE,
        MAX(macm_valor) AS VALOR_LINEA_BASE,
        MAX(macm_original) AS META,
        mvac_nombre AS MEDIO_VERIFICACION,
        spa_nombre AS SUPUESTOS,
        pi.sts_id AS ESTADO_VIN_PF_PI,
        vin.sts_id AS ESTADO_VIN_PII
        FROM cat_ProductoIntermedio cpi
        INNER JOIN vin_ProductoFinalProductoIntermedio pi USING (pdi_id)
        LEFT  JOIN cat_ProductoFinal cpf USING(pdf_id)
        INNER JOIN(
            SELECT gpo_id,ur_id, prg_id AS prgs_id, pdf_id, spr_id,spr_nombre
            FROM cat_Subprograma
        )sb USING(pdf_id)
        LEFT JOIN  vin_ProductoIntermedioIndicador  vin USING(pdf_id,pdi_id)
        INNER JOIN (
            SELECT epg_id, gpo_id, ur_id, prg_id AS prgs_id, spr_id, py_id, oa_id, epg_clave
            FROM enc_Programatico)a USING(gpo_id,ur_id, prgs_id,spr_id)
        # LEFT JOIN cat_MetaActividad USING (pii_id,epg_id)
        LEFT JOIN cat_MetaActividadMir USING ( gpo_id, ur_id, pii_id )
        INNER JOIN(
            SELECT vpm_id, gpo_id, ur_id, prgs_id,upm_id
            FROM vin_UrProgramaMir
        )v USING( gpo_id, ur_id, prgs_id)
        INNER JOIN cat_UnidadResponsable ur USING(gpo_id,ur_id)
        INNER JOIN(
            SELECT gpo_id,ur_id, prg_id AS prgs_id, prg_nombre
            FROM cat_Programa
                )p USING(gpo_id,ur_id,prgs_id)
        LEFT JOIN cat_Indicador i USING(nim_id,ind_id)
        INNER JOIN cat_UnidadMedidaIndicador u USING(umi_id)
        INNER JOIN cat_UnidadMedidaVariable umir USING(umv_id)
        LEFT JOIN(
            SELECT gpo_id,ur_id, pdf_id, pdi_id, GROUP_CONCAT( mvac_nombre ,";")mvac_nombre
            FROM cat_MedioVerificacionActividad
            GROUP BY pdf_id, pdi_id,gpo_id,ur_id
        )medios USING(pdf_id, pdi_id,gpo_id,ur_id)
        LEFT JOIN(
            SELECT gpo_id,ur_id,pdf_id, pdi_id, GROUP_CONCAT(spa_nombre,";") AS spa_nombre
            FROM cat_SupuestoActividad
            GROUP BY pdf_id, pdi_id,gpo_id,ur_id
        )sup USING(pdf_id, pdi_id,gpo_id,ur_id)
        WHERE  pdi_id!=0 and
        cpi.sts_id=1 and cpf.sts_id=1 and pi.sts_id=1 and vin.sts_id=1
        GROUP BY
        -- Se agrego pii_id y spr_id
        pii_id, pdf_id, pdi_id, ind_id, vpm_id, upm_id, spr_id
        ORDER BY ur_clave,pii_id;
    ';

    $sql76 = '
        SELECT
        ur_clave AS UR, ur_nombre AS UNIDAD_RESPONSABLE, prgs_id AS PRG,
        prg_nombre AS PROGRAMA, mir_resumenNarrativo RESUMEN_NARRATIVO, ID_EJE, EJE, ID_TEMA, TEMA, ID_OBJ,OBJETIVO,
        ID_ODS, ODS, ID_SECTOR, SECTOR, ID_OBJPES, OBJETIVO_PES, ID_PLANESP, PLAN_ESPECIAL, ID_OBJESP,
        OBJETIVO_PLAN_ESPECIAL, ind_id AS IND, ind_nombreMIR AS INDICADOR, mtf_anio AS AÑO_LINEA_BASE,
        -- Se agrego el SUM, ya que si se agrupa se deben sumar los campos que son cantidades
        SUM(mtf_valor) AS VALOR_LINEA_BASE,
        SUM(mtf_original) AS META, MEDIO_DE_VERIFICACION, SUPUESTO,
        IF(i.sts_id = 1,"HABILITADO","INHABILITADO") AS ESTADO
        FROM cat_Mir
        INNER JOIN vin_ProgramasMir USING(mir_id)
        -- Se comenta el siguiente INNER ya que no se utiliza el sprs_id y solo hace que se dupliquen registros
        --      INNER JOIN enc_UrSubprogramas USING(gpo_id,ur_id,prgs_id)
        INNER JOIN (
            SELECT prgs_id, po.epo_id,
                eje_id AS ID_EJE,eje_nombre AS EJE, tem_id ID_TEMA,tem_nombre TEMA, obj_id AS ID_OBJ,
                obj_nombre AS OBJETIVO, ods_id AS ID_ODS, ods_nombre AS ODS, sp.spe_id AS ID_SECTOR,
                spe_nombre AS SECTOR, ps.obp_id AS ID_OBJPES, obp_nombre AS OBJETIVO_PES,
                ple_id AS ID_PLANESP,ple_nombre AS PLAN_ESPECIAL,ope_id AS ID_OBJESP,
                ope_nombre AS OBJETIVO_PLAN_ESPECIAL
            FROM vin_Programa v
            INNER JOIN enc_PedObjetivo po USING(epo_id)
            INNER JOIN cat_Eje USING(eje_id)
            INNER JOIN cat_Tema USING(eje_id,tem_id)
            INNER JOIN cat_Objetivo USING(eje_id,tem_id,obj_id)
            INNER JOIN cat_SectorPes sp ON sp.spe_id = v.spe_id
            INNER JOIN cat_ObjetivoPes ps ON ps.spe_id = v.spe_id AND ps.obp_id = v.obp_id
            INNER JOIN cat_PlanEspecial USING (ple_id)
            INNER JOIN cat_ObjetivoPe USING (ple_id,ope_id)
            INNER JOIN enc_PedODS epo USING (epods_id)
            INNER JOIN cat_ODS using(ods_id)
        )e USING(prgs_id)
        INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN (
            SELECT gpo_id, ur_id, prg_id AS prgs_id, prg_nombre
            FROM cat_Programa
        )p USING(gpo_id, ur_id, prgs_id)
        INNER JOIN vin_ProgramaMirIndicador v USING(vpm_id)
        INNER JOIN cat_Indicador i USING(nim_id, ind_id)
        -- Duda, se mantiene como left? ya que apareceran registros vacios, por eso lo maneje como INNER
        INNER JOIN cat_MetaFin USING(pmi_id)
        LEFT JOIN (
            SELECT group_concat(" ",spm_nombre) AS SUPUESTO, vpm_id
            FROM cat_SupuestoProgramaMir
            WHERE nim_id = 1
            GROUP BY vpm_id
        )SupFin USING(vpm_id)
        LEFT JOIN (
            SELECT group_concat(" ",mvpm_nombre) AS MEDIO_DE_VERIFICACION, vpm_id
            FROM cat_MedioVerificacionProgramaMir
            WHERE nim_id = 1
            GROUP BY vpm_id
        )MevFin USING(vpm_id)
        WHERE nim_id=1 AND v.sts_id = 1
                -- Se agrego el mtf_anio
        GROUP BY  pmi_id, vpm_id,mtf_anio
        ORDER BY gpo_id, ur_id;
    ';

    $sql77 = '
        SELECT ur_clave AS UR, ur_nombre AS UNIDAD_RESPONSABLE,
        prgs_id AS PRG, prg_nombre AS PROGRAMA, vpm_resumenNarrativo RESUMEN_NARRATIVO,
        ID_EJE, EJE, ID_TEMA, TEMA, ID_OBJ,OBJETIVO, ID_PLANESP,
        PLAN_ESPECIAL, ID_OBJESP, OBJETIVO_PLAN_ESPECIAL,ID_ODS, ODS, ID_SECTOR,
        SECTOR, ID_OBJPES, OBJETIVO_PES, ind_id AS IND,
        ind_nombreMIR AS INDICADOR,
        CASE
            WHEN umi_id = 5 THEN
            mpr_anterior ELSE mpr_lb
        END AS AÑO_LINEA_BASE,
        CASE
            WHEN umi_id = 2
            OR umi_id = 4
            OR umi_id = 5 THEN
            -- Se hace la suma, ya que si se agrupa se deben sumar los montos
                SUM(mpr_lbIndicador) ELSE SUM(mpr_lbValor)
            END AS VALOR_LINEA_BASE,
        CASE
            WHEN umi_id = 2
            OR umi_id = 4
            OR umi_id = 5 THEN
            -- Se hace la suma, ya que si se agrupa se deben sumar los montos
                SUM(mpr_anteriorIndicador) ELSE SUM(mpr_original)
            END AS META,
            SUM(mpr_modificado) AS META_ACTUALIZADA,
            SUM(mpr_alcanzado) AS META_REPORTADA_UR,
            umi_id,
            MEDIO_DE_VERIFICACION,
            SUPUESTO,
            IF(i.sts_id = 1,"HABILITADO","INHABILITADO") AS ESTADO
        FROM cat_Mir
        INNER JOIN vin_ProgramasMir USING ( mir_id )
        -- Se comenta el siguiente INNER ya que no se utiliza el sprs_id y solo hace que se dupliquen registros
        -- INNER JOIN enc_UrSubprogramas USING ( gpo_id, ur_id, prgs_id )
        INNER JOIN (
            SELECT prgs_id, sp.spe_id ID_SECTOR, spe_nombre SECTOR,
            eje_id AS ID_EJE, eje_nombre AS EJE, tem_id ID_TEMA, tem_nombre TEMA, obj_id AS ID_OBJ,
                obj_nombre AS OBJETIVO, ps.obp_id AS ID_OBJPES, obp_nombre AS OBJETIVO_PES,
                ple_id AS ID_PLANESP, ple_nombre AS PLAN_ESPECIAL, ope_id AS ID_OBJESP,
                ope_nombre AS OBJETIVO_PLAN_ESPECIAL, ods_id AS ID_ODS, ods_nombre AS ODS
            FROM vin_Programa v
            INNER JOIN cat_SectorPes sp USING ( spe_id )
            INNER JOIN enc_PedObjetivo USING(epo_id)
            INNER JOIN cat_Eje USING(eje_id)
            INNER JOIN cat_Tema USING(eje_id,tem_id)
            INNER JOIN cat_Objetivo USING(eje_id,tem_id,obj_id)
            INNER JOIN cat_ObjetivoPes ps ON ps.spe_id = v.spe_id AND ps.obp_id = v.obp_id
            INNER JOIN cat_PlanEspecial USING (ple_id)
            INNER JOIN cat_ObjetivoPe USING (ple_id,ope_id)
            INNER JOIN enc_PedODS epo USING (epods_id)
            INNER JOIN cat_ODS using(ods_id)
        ) e USING ( prgs_id )
        INNER JOIN cat_UnidadResponsable USING ( gpo_id, ur_id )
        INNER JOIN ( SELECT gpo_id, ur_id, prg_id AS prgs_id, prg_nombre FROM cat_Programa ) p USING ( gpo_id, ur_id, prgs_id )
        INNER JOIN vin_ProgramaMirIndicador v USING ( vpm_id )
        INNER JOIN cat_Indicador i USING ( nim_id, ind_id )
        -- Se cambio a inner
        INNER JOIN cat_MetaProposito USING ( pmi_id )
        LEFT JOIN ( SELECT group_concat( spm_nombre, "- " ) AS SUPUESTO, vpm_id FROM cat_SupuestoProgramaMir WHERE nim_id = 2 GROUP BY vpm_id ) SupFin USING ( vpm_id )
        LEFT JOIN ( SELECT group_concat( mvpm_nombre, "- " ) AS MEDIO_DE_VERIFICACION, vpm_id FROM cat_MedioVerificacionProgramaMir WHERE nim_id = 2 GROUP BY vpm_id ) MevFin USING ( vpm_id )
        WHERE nim_id = 2 AND v.sts_id = 1
        GROUP BY
        -- Se agrupa por mpr_lb
        pmi_id,mpr_anterior,mpr_lb;
    ';

    $sql78 = '
        SELECT ur_clave AS ID_UR, ur_nombre AS UNIDAD_RESPONSABLE, prg.prgs_id AS ID_PRG,
        prgs_nombre AS PROGRAMA, spr.sprs_id AS ID_SPR, sprs_nombre AS SUBPROGRAMA,
        pdf_id AS ID_PF, pdf_plural AS PRODUCTO_FINAL, ind_id AS ID_IND,
        ind_nombreMIR AS INDICADOR_NOMBRE_MIR, ind_definicionMIR AS INDICADOR_DEFINICION_MIR,
        ind_nombre AS NOMBRE_INDICADOR, u.umv_id AS ID_UNIDAD_MEDIDA, u.umv_nombre AS UNIDAD_MEDIDA,
        mco_lb AS AÑO_LINEA_BASE, SUM( mco_lbValor ) AS VALOR_LINEA_BASE, SUM( mco_modificado ) AS META,
        mvcm_nombre AS MEDIO_VERIFICACION, spc_nombre AS SUPUESTOS,
        SUM( mco_original ) AS META_PROGRAMADA,
        SUM( mco_modificado ) AS META_ACTUALIZADA,
        SUM( alc_trimestre2 ) AS META_ALCANZADA_S1,
        SUM( alc_trimestre4 ) AS META_ALCANZADA_S2
        FROM cat_Programas prg
        INNER JOIN cat_Subprogramas spr USING ( prgs_id )
        INNER JOIN enc_UrSubprogramas s USING ( prgs_id, sprs_id )
        INNER JOIN cat_ProductoFinal USING ( pdf_id )
        INNER JOIN enc_ProductoFinalIndicador USING ( pdf_id )
        INNER JOIN cat_MetaComponente m USING ( gpo_id, ur_id, prgs_id, sprs_id, epfi_id )
        INNER JOIN cat_UnidadResponsable USING ( gpo_id, ur_id )
        INNER JOIN cat_Indicador i USING ( nim_id, ind_id )
        INNER JOIN cat_UnidadMedidaVariable u USING ( umv_id )
        INNER JOIN (
            SELECT gpo_id, ur_id, prgs_id, sprs_id,
            epfi_id,-- Se actualizo el nombre de de moct a mcot
            SUM( IF ( tri_id = 2, mcot_alcanzado, 0 ) ) AS alc_trimestre2,
            SUM( IF ( tri_id = 4, mcot_alcanzado, 0 ) ) AS alc_trimestre4
            FROM cat_MetaComponenteTrimestre
            GROUP BY gpo_id, ur_id, prgs_id, sprs_id, epfi_id
        ) mt USING ( gpo_id, ur_id, prgs_id, sprs_id, epfi_id )
        INNER JOIN (
            SELECT gpo_id, ur_id, prg_id, spr_id, SUM( pre_vigente ) AS pre_vigente
            FROM presupuesto
            INNER JOIN presupuestoVigente AS vig USING ( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
            WHERE pre_vigente > 0
            GROUP BY gpo_id, ur_id, prg_id, spr_id
        ) p ON s.gpo_id = p.gpo_id
        AND s.ur_id = p.ur_id
        AND s.prgs_id = p.prg_id
        AND s.sprs_id = p.spr_id
        LEFT JOIN (
            SELECT gpo_id, ur_id, prgs_id, sprs_id,
            GROUP_CONCAT( mvcm_nombre, ";" ) AS mvcm_nombre
            FROM cat_MedioVerificacionComponente
            GROUP BY gpo_id, ur_id, prgs_id, sprs_id
        ) medios ON s.gpo_id = medios.gpo_id
        AND s.ur_id = medios.ur_id
        AND s.prgs_id = medios.prgs_id
        AND s.sprs_id = medios.sprs_id
        LEFT JOIN (
            SELECT gpo_id, ur_id, prgs_id, sprs_id,
            GROUP_CONCAT( spc_nombre, ";" ) AS spc_nombre
            FROM cat_SupuestoComponente
            GROUP BY gpo_id, ur_id, prgs_id, sprs_id
        ) sup ON s.gpo_id = sup.gpo_id
        AND s.ur_id = sup.ur_id
        AND s.prgs_id = sup.prgs_id
        AND s.sprs_id = sup.sprs_id
        WHERE s.gpo_id is not null
        GROUP BY s.gpo_id, s.ur_id, s.prgs_id,-- Se agregaron los siguientes group by
        s.sprs_id, ind_id, ind_nombre, ind_nombreMIR, ind_definicionMIR, umv_id, mco_lb,
        pdf_id;
    ';

    if ($opcion == 'P-10') {
        $sql79 = '
            SELECT ur_clave AS ID_UR, ur_nombre AS UNIDAD_RESPONSABLE, prg_id AS ID_PRG,
            prg_nombre AS PRG_NOMBRE, s.spr_id AS ID_SPR, spr_nombre AS SUBPROGRAMA, pdf_id AS ID_PF,
            pdf_plural AS PRODUCTO_FINAL, pdi_id AS ID_PI, pdi_plural AS PRODUCTO_INTERMEDIO,
            ind_id AS ID_IND, ind_nombre AS NOMBRE_INDICADOR, ind_nombreMIR AS NOMBRE_INDICADOR_MIR,
            ind_definicion AS DEFINICION_INDICADOR, u.umv_id AS ID_UNIDAD_MEDIDA, u.umv_nombre AS UNIDAD_MEDIDA,
            macm_anio AS AÑO_LINEA_BASE, SUM( macm_valor ) AS VALOR_LINEA_BASE, mvac_nombre AS MEDIO_VERIFICACION,
            spa_nombre AS SUPUESTOS,
            SUM( mac_original ) AS META_PROGRAMADA,
            SUM( mac_modificado ) AS META_ACTUALIZADA,
            SUM( alc_trimestre1 ) AS META_ALCANZADA_T1,
            SUM( alc_trimestre2 ) AS META_ALCANZADA_T2,
            SUM( alc_trimestre3 ) AS META_ALCANZADA_T3,
            SUM( alc_trimestre4 ) AS META_ALCANZADA_T4,
            SUM( pre_aprobado ) AS PRE_APROBADO,
            SUM( pre_vigente ) AS PRE_VIGENTE,
            SUM( pre_comprometido ) AS PRE_COMPROMETIDO,
            SUM( pre_devengado ) AS PRE_DEVENGADO,
            SUM( pre_ejercido ) AS PRE_EJERCIDO,
            pre_claveCompleta, tcp_id, ecf_clave
            FROM cat_ProductoIntermedio
            INNER JOIN vin_ProductoFinalProductoIntermedio USING ( pdi_id )
            INNER JOIN vin_ProductoIntermedioIndicador USING ( pdf_id, pdi_id )
            INNER JOIN cat_MetaActividad USING ( pii_id )
            INNER JOIN enc_Programatico USING ( epg_id )
            INNER JOIN (
                SELECT
                    pii_id,
                    epg_id,
                    SUM( IF ( tri_id = 1, mact_alcanzado, 0 ) ) AS alc_trimestre1,
                    SUM( IF ( tri_id = 2, mact_alcanzado, 0 ) ) AS alc_trimestre2,
                    SUM( IF ( tri_id = 3, mact_alcanzado, 0 ) ) AS alc_trimestre3,
                    SUM( IF ( tri_id = 4, mact_alcanzado, 0 ) ) AS alc_trimestre4
                FROM
                    cat_MetaActividadTrimestre
                GROUP BY
                    pii_id,
                    epg_id
            ) mt USING ( pii_id, epg_id )
            INNER JOIN (
                SELECT epg_id, ecf_id, tcp_id, pre_claveCompleta, ecf_clave,
                SUM( pre_aprobado ) AS pre_aprobado,
                SUM( pre_vigente ) AS pre_vigente,
                SUM( pre_comprometido ) AS pre_comprometido,
                SUM( pre_devengado ) AS pre_devengado,
                SUM( pre_ejercido ) AS pre_ejercido
                FROM presupuesto
                INNER JOIN presupuestoAprobado USING ( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
                INNER JOIN presupuestoVigente USING ( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
                INNER JOIN presupuestoComprometido USING ( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
                INNER JOIN presupuestoDevengado USING ( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
                INNER JOIN presupuestoEjercido USING ( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
                INNER JOIN enc_ClaveFinanciamiento USING ( ecf_id )
                WHERE prg_id != 0
                GROUP BY epg_id, ecf_id, tcp_id, pre_claveCompleta
            ) p USING ( epg_id )
            INNER JOIN cat_UnidadResponsable USING ( gpo_id, ur_id )
            INNER JOIN cat_Programa USING ( gpo_id, ur_id, prg_id )
            INNER JOIN cat_Subprograma s USING ( gpo_id, ur_id, prg_id, spr_id, pdf_id )
            INNER JOIN cat_ProductoFinal USING ( pdf_id )
            INNER JOIN cat_Indicador i USING ( nim_id, ind_id )
            INNER JOIN cat_UnidadMedidaVariable u USING ( umv_id )
            LEFT JOIN cat_MetaActividadMir USING (pii_id,gpo_id,ur_id)
            LEFT JOIN ( SELECT pdf_id, pdi_id, GROUP_CONCAT( mvac_nombre, ";" ) AS mvac_nombre, gpo_id,ur_id
                FROM cat_MedioVerificacionActividad
                GROUP BY pdf_id, pdi_id,gpo_id,ur_id
            ) medios USING ( pdf_id, pdi_id,gpo_id,ur_id)
            LEFT JOIN ( SELECT pdf_id, pdi_id, GROUP_CONCAT( spa_nombre, ";" ) AS spa_nombre,gpo_id,ur_id
                FROM cat_SupuestoActividad
                GROUP BY pdf_id, pdi_id,gpo_id,ur_id
            ) sup USING ( pdf_id, pdi_id,gpo_id,ur_id)
            WHERE
                prg_id = :prg_id
                AND pdi_id != :pdi_id
                AND mac_agrupadorPresupuestal = :mac
            GROUP BY ur_clave, s.spr_id, pdf_id, pdi_id, ind_id, ind_nombre, ind_nombreMIR, ind_definicion,
                umv_id, macm_anio, tcp_id, pre_claveCompleta
            ORDER BY ur_clave,pre_claveCompleta,tcp_id;
        ';
        $param = [
            ':prg_id' => $prg_id,
            ':pdi_id' => 0,
            ':mac'    => 1,
        ];
    }

    //avance Programatico Financiero
    $sql80 = '
        SELECT ur_clave AS UR_ID, ur_nombre,prg_id as Programa ,prg_nombre,spr_id as Subprograma,spr_nombre,
            py_id as Proyecto,py_nombre,
            oa_id AS Obra_Actividad,oa_nombre,if(toa_id=2 and oa_origen!="S",oa_' . APP_EJERCICIO_ANTERIOR . ',"") NumObra,
            cfi_clave as Clave_Financiamiento,cfi_Nombre as nombre,
            idp_id Identificador_Partida,idp_nombre,clg_id as Clase_Gasto,clg_nombre,
            concat(SUBSTRING(pre_claveCompleta FROM 1 FOR 19), SUBSTRING(pre_claveCompleta FROM 26)) as Clave_Presupuestal,
            sum(ori_ene+ori_feb+ori_mar) as ori_t1,sum(apr_ene+apr_feb+apr_mar) as apr_t1,sum(vig_ene+vig_feb+vig_mar) as vig_t1,sum(com_ene+com_feb+com_mar) as com_t1,sum(dev_ene+dev_feb+dev_mar) as dev_t1,sum(eje_ene+eje_feb+eje_mar) as eje_t1, sum(pag_ene+pag_feb+pag_mar) as pag_t1,
            sum(ori_abr+ori_may+ori_jun) as ori_t2, sum(apr_abr+apr_may+apr_jun) as apr_t2,sum(vig_abr+vig_may+vig_jun) as vig_t2, sum(com_abr+com_may+com_jun) as com_t2, sum(dev_abr+dev_may+dev_jun) as dev_t2, sum(eje_abr+eje_may+eje_jun) as eje_t2,sum(pag_abr+pag_may+pag_jun) as pag_t2,
            sum(ori_jul+ori_ago+ori_sep) as ori_t3, sum(apr_jul+apr_ago+apr_sep) as apr_t3,sum(vig_jul+vig_ago+vig_sep) as vig_t3,sum(com_jul+com_ago+com_sep) as com_t3,sum(dev_jul+dev_ago+dev_sep) as dev_t3,sum(eje_jul+eje_ago+eje_sep) as eje_t3,sum(pag_jul+pag_ago+pag_sep) as pag_t3,
            sum(ori_oct+ori_nov+ori_dic) as ori_t4,sum(apr_oct+apr_nov+apr_dic) as apr_t4,sum(vig_oct+vig_nov+vig_dic) as vig_t4, sum(com_oct+com_nov+com_dic) as com_t4, sum(dev_oct+dev_nov+dev_dic) as dev_t4, sum(eje_oct+eje_nov+eje_dic) as eje_t4,sum(pag_oct+pag_nov+pag_dic) as pag_t4
        FROM presupuesto
            JOIN presupuestoAprobado USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            JOIN presupuestoVigente USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            JOIN presupuestoComprometido USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            JOIN presupuestoDevengado USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            JOIN presupuestoEjercido USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            JOIN presupuestoOriginal USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            JOIN presupuestoPagado USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
            INNER JOIN cat_UnidadResponsable using(gpo_id,ur_id)
            INNER JOIN cat_UnidadEjecutora using(gpo_id,ur_id,ue_id)
            INNER JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
            INNER JOIN cat_Subprograma USING(gpo_id,ur_id,prg_id,spr_id)
            INNER JOIN cat_Proyecto USING(gpo_id,ur_id,prg_id,spr_id,py_id)
            INNER JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            INNER JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            INNER JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
            INNER JOIN cat_ClaseGasto USING ( clg_id )
            INNER JOIN cat_IdentificadorPartida using(idp_id)
        WHERE tcp_id!="P"
        GROUP BY gpo_id, ur_id, epg_id, ecf_id, idp_id, clg_id
        ORDER BY gpo_id, ur_id, epg_id, ecf_id, idp_id, clg_id;
    ';

    $sql81 = '
        SELECT
            ur_clave AS ID_UR,
            ur_nombre AS UNIDAD_RESPONSABLE,
            prg_id AS ID_PRG,
            prg_nombre AS PRG_NOMBRE,
            s.spr_id AS ID_SPR,
            spr_nombre AS SUBPROGRAMA,
            pdf_id AS ID_PF,
            pdf_plural AS PRODUCTO_FINAL,
            pdi_id AS ID_PI,
            pdi_plural AS PRODUCTO_INTERMEDIO,
            ind_id AS ID_IND,
            ind_nombre AS NOMBRE_INDICADOR,
            ind_nombreMIR AS NOMBRE_INDICADOR_MIR,
            ind_definicion AS DEFINICION_INDICADOR,
            u.umv_id AS ID_UNIDAD_MEDIDA,
            u.umv_nombre AS UNIDAD_MEDIDA,
            SUM(mac_anio) AS AÑO_LINEA_BASE,
            SUM(mac_valor) AS VALOR_LINEA_BASE,
            SUM( mac_original ) AS META_PROGRAMADA_ANUAL,
            SUM( mac_modificado ) AS META_ACTUALIZADA,
            SUM( mac_alcanzado ) AS META_ALCANZADA,
            SUM( ori_trimestre1 ) AS META_PROGRAMADA_T1,
            SUM( mod_trimestre1 ) AS META_ACTUALIZADA_T1,
            SUM( alc_trimestre1 ) AS META_ALCANZADA_T1,
            SUM( ori_trimestre2 ) AS META_PROGRAMADA_T2,
            SUM( mod_trimestre2 ) AS META_ACTUALIZADA_T2,
            SUM( alc_trimestre2 ) AS META_ALCANZADA_T2,
            SUM( ori_trimestre3 ) AS META_PROGRAMADA_T3,
            SUM( mod_trimestre3 ) AS META_ACTUALIZADA_T3,
            SUM( alc_trimestre3 ) AS META_ALCANZADA_T3,
            SUM( ori_trimestre4 ) AS META_PROGRAMADA_T4,
            SUM( mod_trimestre4 ) AS META_ACTUALIZADA_T4,
            SUM( alc_trimestre4 ) AS META_ALCANZADA_T4,
            concat(ur_clave,LPAD(ue_id,3,0),"-",epg_clave) as CLAVE_PROGRAMATICA
        FROM cat_ProductoIntermedio
            INNER JOIN vin_ProductoFinalProductoIntermedio USING ( pdi_id )
            INNER JOIN vin_ProductoIntermedioIndicador USING ( pdf_id, pdi_id )
            INNER JOIN cat_MetaActividad USING ( pii_id )
            INNER JOIN enc_Programatico USING ( epg_id )
            INNER JOIN (
                SELECT
                    pii_id,
                    epg_id,
                    SUM( IF ( tri_id = 1, mact_original, 0 ) ) AS ori_trimestre1,
                    SUM( IF ( tri_id = 2, mact_original, 0 ) ) AS ori_trimestre2,
                    SUM( IF ( tri_id = 3, mact_original, 0 ) ) AS ori_trimestre3,
                    SUM( IF ( tri_id = 4, mact_original, 0 ) ) AS ori_trimestre4,
                    SUM( IF ( tri_id = 1, mact_modificado, 0 ) ) AS mod_trimestre1,
                    SUM( IF ( tri_id = 2, mact_modificado, 0 ) ) AS mod_trimestre2,
                    SUM( IF ( tri_id = 3, mact_modificado, 0 ) ) AS mod_trimestre3,
                    SUM( IF ( tri_id = 4, mact_modificado, 0 ) ) AS mod_trimestre4,
                    SUM( IF ( tri_id = 1, mact_alcanzado, 0 ) ) AS alc_trimestre1,
                    SUM( IF ( tri_id = 2, mact_alcanzado, 0 ) ) AS alc_trimestre2,
                    SUM( IF ( tri_id = 3, mact_alcanzado, 0 ) ) AS alc_trimestre3,
                    SUM( IF ( tri_id = 4, mact_alcanzado, 0 ) ) AS alc_trimestre4
                FROM
                    cat_MetaActividadTrimestre
                GROUP BY
                    pii_id,
                    epg_id
            ) mt USING ( pii_id, epg_id )
            INNER JOIN cat_UnidadResponsable USING ( gpo_id, ur_id )
            INNER JOIN cat_ObraActividad USING ( gpo_id, ur_id, prg_id, spr_id,py_id, oa_id)
            INNER JOIN cat_UnidadEjecutora USING(gpo_id,ur_id,ue_id)
            INNER JOIN cat_Programa USING ( gpo_id, ur_id, prg_id )
            INNER JOIN cat_Subprograma s USING ( gpo_id, ur_id, prg_id, spr_id, pdf_id )
            INNER JOIN cat_ProductoFinal USING ( pdf_id )
            INNER JOIN cat_Indicador i USING ( nim_id, ind_id )
            INNER JOIN cat_UnidadMedidaVariable u USING ( umv_id )
            LEFT JOIN cat_MetaActividadMir USING (pii_id,gpo_id,ur_id)
        WHERE pdi_id != 0
        GROUP BY
            ur_clave,
            s.spr_id,
            pdf_id,
            pdi_id,
            ind_id,
            ind_nombre,
            ind_nombreMIR,
            ind_definicion,
            umv_id,
            epg_id
        ORDER BY ur_clave,epg_clave;
    ';

    $sql82 = '
        SELECT  pii_id,ur_clave AS ID_UR, ur_nombre AS UNIDAD_RESPONSABLE, sb.prgs_id AS ID_PRG, prg_nombre AS PRG_NOMBRE, spr_id AS ID_SPR, spr_nombre AS SUBPROGRAMA,
        pdf_id AS ID_PF,pdf_plural AS PRODUCTO_FINAL, pdi_id AS ID_PI, pdi_plural AS PRODUCTO_INTERMEDIO, ind_id AS ID_IND, ind_nombre AS NOMBRE_INDICADOR,
        ind_definicion AS DEFINICION_INDICADOR,
        umir.umv_id AS ID_UNIDAD_MEDIDA, umir.umv_nombre AS UNIDAD_MEDIDA,
        MAX(macm_anio) AS AÑO_LINEA_BASE,
        MAX(macm_valor) AS VALOR_LINEA_BASE,
        MAX(macm_original) AS META,
        mvac_nombre AS MEDIO_VERIFICACION,
        spa_nombre AS SUPUESTOS,
        pi.sts_id AS ESTADO_VIN_PF_PI,
        vin.sts_id AS ESTADO_VIN_PII
        FROM cat_ProductoIntermedio cpi
        INNER JOIN vin_ProductoFinalProductoIntermedio pi USING (pdi_id)
        LEFT  JOIN cat_ProductoFinal cpf USING(pdf_id)
        INNER JOIN(
            SELECT gpo_id,ur_id, prg_id AS prgs_id, pdf_id, spr_id,spr_nombre
            FROM cat_Subprograma
        )sb USING(pdf_id)
        LEFT JOIN  vin_ProductoIntermedioIndicador  vin USING(pdf_id,pdi_id)
        INNER JOIN (
            SELECT epg_id, gpo_id, ur_id, prg_id AS prgs_id, spr_id, py_id, oa_id, epg_clave
            FROM enc_Programatico)a USING(gpo_id,ur_id, prgs_id,spr_id)
        LEFT JOIN cat_MetaActividadMir USING ( gpo_id, ur_id, pii_id )
        INNER JOIN(
            SELECT vpm_id, gpo_id, ur_id, prgs_id,upm_id
            FROM vin_UrProgramaMir
        )v USING( gpo_id, ur_id, prgs_id)
        INNER JOIN cat_UnidadResponsable ur USING(gpo_id,ur_id)
        INNER JOIN(
            SELECT gpo_id,ur_id, prg_id AS prgs_id, prg_nombre
            FROM cat_Programa
                )p USING(gpo_id,ur_id,prgs_id)
        LEFT JOIN cat_Indicador i USING(nim_id,ind_id)
        INNER JOIN cat_UnidadMedidaIndicador u USING(umi_id)
        INNER JOIN cat_UnidadMedidaVariable umir USING(umv_id)
        LEFT JOIN(
            SELECT gpo_id,ur_id, pdf_id, pdi_id, GROUP_CONCAT( mvac_nombre ,";")mvac_nombre
            FROM cat_MedioVerificacionActividad
            GROUP BY pdf_id, pdi_id,gpo_id,ur_id
        )medios USING(pdf_id, pdi_id,gpo_id,ur_id)
        LEFT JOIN(
            SELECT gpo_id,ur_id,pdf_id, pdi_id, GROUP_CONCAT(spa_nombre,";") AS spa_nombre
            FROM cat_SupuestoActividad
            GROUP BY pdf_id, pdi_id,gpo_id,ur_id
        )sup USING(pdf_id, pdi_id,gpo_id,ur_id)
        WHERE  pdi_id!=0 and
        cpi.sts_id=2 and cpf.sts_id=2 and pi.sts_id=2 or vin.sts_id=2
        GROUP BY
        -- Se agrego pii_id y spr_id
        pii_id, pdf_id, pdi_id, ind_id, vpm_id, upm_id, spr_id
        ORDER BY ur_clave,pii_id;
    ';

    $sql83 = '
        SELECT  pii_id,ur_clave AS ID_UR, ur_nombre AS UNIDAD_RESPONSABLE, sb.prgs_id AS ID_PRG, prg_nombre AS PRG_NOMBRE, spr_id AS ID_SPR, spr_nombre AS SUBPROGRAMA,
        pdf_id AS ID_PF,pdf_plural AS PRODUCTO_FINAL, pdi_id AS ID_PI, pdi_plural AS PRODUCTO_INTERMEDIO, ind_id AS ID_IND, ind_nombre AS NOMBRE_INDICADOR,
        ind_definicion AS DEFINICION_INDICADOR,
        umir.umv_id AS ID_UNIDAD_MEDIDA, umir.umv_nombre AS UNIDAD_MEDIDA,
        MAX(macm_anio) AS AÑO_LINEA_BASE,
        MAX(macm_valor) AS VALOR_LINEA_BASE,
        MAX(macm_original) AS META,
        mvac_nombre AS MEDIO_VERIFICACION,
        spa_nombre AS SUPUESTOS,
        pi.sts_id AS ESTADO_VIN_PF_PI,
        vin.sts_id AS ESTADO_VIN_PII
        FROM cat_ProductoIntermedio cpi
        INNER JOIN vin_ProductoFinalProductoIntermedio pi USING (pdi_id)
        LEFT  JOIN cat_ProductoFinal cpf USING(pdf_id)
        INNER JOIN(
            SELECT gpo_id,ur_id, prg_id AS prgs_id, pdf_id, spr_id,spr_nombre
            FROM cat_Subprograma
        )sb USING(pdf_id)
        LEFT JOIN  vin_ProductoIntermedioIndicador  vin USING(pdf_id,pdi_id)
        INNER JOIN (
            SELECT epg_id, gpo_id, ur_id, prg_id AS prgs_id, spr_id, py_id, oa_id, epg_clave
            FROM enc_Programatico)a USING(gpo_id,ur_id, prgs_id,spr_id)
        LEFT JOIN cat_MetaActividadMir USING ( gpo_id, ur_id, pii_id )
        INNER JOIN(
            SELECT vpm_id, gpo_id, ur_id, prgs_id,upm_id
            FROM vin_UrProgramaMir
        )v USING( gpo_id, ur_id, prgs_id)
        INNER JOIN cat_UnidadResponsable ur USING(gpo_id,ur_id)
        INNER JOIN(
            SELECT gpo_id,ur_id, prg_id AS prgs_id, prg_nombre
            FROM cat_Programa
                )p USING(gpo_id,ur_id,prgs_id)
        LEFT JOIN cat_Indicador i USING(nim_id,ind_id)
        INNER JOIN cat_UnidadMedidaIndicador u USING(umi_id)
        INNER JOIN cat_UnidadMedidaVariable umir USING(umv_id)
        LEFT JOIN(
            SELECT gpo_id,ur_id, pdf_id, pdi_id, GROUP_CONCAT( mvac_nombre ,";")mvac_nombre
            FROM cat_MedioVerificacionActividad
            GROUP BY pdf_id, pdi_id,gpo_id,ur_id
        )medios USING(pdf_id, pdi_id,gpo_id,ur_id)
        LEFT JOIN(
            SELECT gpo_id,ur_id,pdf_id, pdi_id, GROUP_CONCAT(spa_nombre,";") AS spa_nombre
            FROM cat_SupuestoActividad
            GROUP BY pdf_id, pdi_id,gpo_id,ur_id
        )sup USING(pdf_id, pdi_id,gpo_id,ur_id)
        WHERE  pdi_id!=0
        GROUP BY
        -- Se agrego pii_id y spr_id
        pii_id, pdf_id, pdi_id, ind_id, vpm_id, upm_id, spr_id
        ORDER BY ur_clave,pii_id;
    ';

    $sql84 = '
        SELECT pii_id,ur_clave AS ID_UR, ur_nombre AS UNIDAD_RESPONSABLE, sb.prgs_id AS ID_PRG, prg_nombre AS PRG_NOMBRE, spr_id AS ID_SPR,spr_nombre AS SUBPROGRAMA, 
        pdf_id AS ID_PF,pdf_plural AS PRODUCTO_FINAL, pdi_id AS ID_PI, pdi_plural AS PRODUCTO_INTERMEDIO, ind_id AS ID_IND, 
        ind_nombreMIR AS INDICADOR_NOMBRE_MIR, ind_definicionMIR AS INDICADOR_DEFINICION_MIR,ind_nombre AS NOMBRE_INDICADOR,
        ind_definicion AS DEFINICION_INDICADOR,
        u.umi_nombre AS UNIDAD_MEDIDA_INDICADOR,ind_formulaCalculo AS FÓRMULA_CALCULO,
        -- i.umi_id as ID_UNIDAD_MEDIDA, u.umi_nombre AS UNIDAD_MEDIDA,
        umir.umv_id AS ID_UNIDAD_MEDIDA, umir.umv_nombre AS UNIDAD_MEDIDA,
        aux.macm_anio AS AÑO_LINEA_BASE,
        aux.macm_valor AS VALOR_LINEA_BASE,
        ma.macm_original AS META,
        aux.macm_original AS META_PROGRAMADA_ANUAL,
        mac_modificado AS META_ACTUALIZADA_ANUAL,
        mac_alcanzado AS META_ALCANZADA_ANUAL,
        IF(tri_id=0,aux.macm_numeradorT1,"") AS META_PROGRAMADA_T1,
        mod_trimestre1 AS META_ACTUALIZADA_T1,
        alc_trimestre1 AS META_ALCANZADA_T1,
        IF(tri_id=0,aux.macm_numeradorT2,"") AS META_PROGRAMADA_T2,
        mod_trimestre2 AS META_ACTUALIZADA_T2,
        alc_trimestre2 AS META_ALCANZADA_T2,
        IF(tri_id=0,aux.macm_numeradorT3,"") AS META_PROGRAMADA_T3,
        mod_trimestre3 AS META_ACTUALIZADA_T3,
        alc_trimestre3 AS META_ALCANZADA_T3,
        IF(tri_id=0,aux.macm_numeradorT4,"") AS META_PROGRAMADA_T4,
        mod_trimestre4 AS META_ACTUALIZADA_T4,
        alc_trimestre4 AS META_ALCANZADA_T4,
        mvac_nombre AS MEDIO_VERIFICACION, 
        spa_nombre AS SUPUESTOS,
        IF (pi.sts_id =1, "HABILITADO", "INHABILITADO") AS ESTADO_VIN_PF_PI,
        IF (vin.sts_id =1, "HABILITADO", "INHABILITADO") AS ESTADO_VIN_PII
        FROM cat_ProductoIntermedio cpi
        INNER JOIN vin_ProductoFinalProductoIntermedio pi USING (pdi_id)
        LEFT  JOIN cat_ProductoFinal cpf USING(pdf_id)
        INNER JOIN( 
            SELECT gpo_id,ur_id, prg_id AS prgs_id, pdf_id, spr_id,spr_nombre
            FROM cat_Subprograma
        )sb USING(pdf_id)
        LEFT JOIN  vin_ProductoIntermedioIndicador  vin USING(pdf_id,pdi_id)
        INNER JOIN (
            SELECT epg_id, gpo_id, ur_id, prg_id AS prgs_id, spr_id, py_id, oa_id, epg_clave
            FROM enc_Programatico)a USING(gpo_id,ur_id, prgs_id,spr_id)
            INNER JOIN (
            SELECT
                pii_id,
                epg_id,
                SUM( IF ( tri_id = 1, mact_modificado, 0 ) ) AS mod_trimestre1,
                SUM( IF ( tri_id = 2, mact_modificado, 0 ) ) AS mod_trimestre2,
                SUM( IF ( tri_id = 3, mact_modificado, 0 ) ) AS mod_trimestre3,
                SUM( IF ( tri_id = 4, mact_modificado, 0 ) ) AS mod_trimestre4,
                SUM( IF ( tri_id = 1, mact_alcanzado, 0 ) ) AS alc_trimestre1,
                SUM( IF ( tri_id = 2, mact_alcanzado, 0 ) ) AS alc_trimestre2,
                SUM( IF ( tri_id = 3, mact_alcanzado, 0 ) ) AS alc_trimestre3,
                SUM( IF ( tri_id = 4, mact_alcanzado, 0 ) ) AS alc_trimestre4
            FROM
                cat_MetaActividadTrimestre 
            GROUP BY
                pii_id,
                epg_id 
        ) mt USING ( pii_id, epg_id )
        LEFT JOIN cat_MetaActividad USING (pii_id,epg_id)
        LEFT JOIN cat_MetaActividadMir ma USING ( gpo_id, ur_id, pii_id )
        INNER JOIN( 
            SELECT vpm_id, gpo_id, ur_id, prgs_id,upm_id
            FROM vin_UrProgramaMir  
        )v USING( gpo_id, ur_id, prgs_id)
        INNER JOIN cat_UnidadResponsable ur USING(gpo_id,ur_id)
        INNER JOIN( 
            SELECT gpo_id,ur_id, prg_id AS prgs_id, prg_nombre
            FROM cat_Programa
                )p USING(gpo_id,ur_id,prgs_id)	
        LEFT JOIN cat_Indicador i USING(nim_id,ind_id)
        INNER JOIN cat_UnidadMedidaIndicador u USING(umi_id)
        INNER JOIN cat_UnidadMedidaVariable umir USING(umv_id)
        LEFT JOIN aux_HistoricoMetaActividadMir aux USING(gpo_id, ur_id, pii_id)
        LEFT JOIN( 
            SELECT gpo_id,ur_id, pdf_id, pdi_id, GROUP_CONCAT( mvac_nombre ,";")mvac_nombre
            FROM cat_MedioVerificacionActividad 
            GROUP BY pdf_id, pdi_id,gpo_id,ur_id
        )medios USING(pdf_id, pdi_id,gpo_id,ur_id)
        LEFT JOIN( 
            SELECT gpo_id,ur_id,pdf_id, pdi_id, GROUP_CONCAT(spa_nombre,";") AS spa_nombre
            FROM cat_SupuestoActividad
            GROUP BY pdf_id, pdi_id,gpo_id,ur_id
        )sup USING(pdf_id, pdi_id,gpo_id,ur_id)
        WHERE  pdi_id!=0
        GROUP BY 
        -- Se agrego pii_id y spr_id
        pii_id, pdf_id, pdi_id, ind_id, vpm_id, upm_id, spr_id	
        ORDER BY ur_clave,pii_id;
    ';

    $sql85 = '
        SELECT ur_clave AS ID_UR, ur_nombre AS UNIDAD_RESPONSABLE, prg.prgs_id AS ID_PRG,
        prgs_nombre AS PROGRAMA, spr.sprs_id AS ID_SPR, sprs_nombre AS SUBPROGRAMA,
        pdf_id AS ID_PF, pdf_plural AS PRODUCTO_FINAL, ind_id AS ID_IND,
        ind_nombreMIR AS INDICADOR_NOMBRE_MIR, ind_definicionMIR AS INDICADOR_DEFINICION_MIR,
        ind_nombre AS NOMBRE_INDICADOR, u.umv_id AS ID_UNIDAD_MEDIDA, u.umv_nombre AS UNIDAD_MEDIDA_VARIABLE,
        CASE
        WHEN umi_id IN(2,3,4) THEN "(A/B)*100"
        WHEN umi_id = 5 THEN "((A/B)-1)*100"
        END AS FÓRMULA_CALCULO,
        u.umv_nombre AS UNIDAD_MEDIDA,
        m.mco_lb AS AÑO_LINEA_BASE, SUM( m.mco_lbValor ) AS VALOR_LINEA_BASE, SUM( m.mco_modificado ) AS META,
        mvcm_nombre AS MEDIO_VERIFICACION, spc_nombre AS SUPUESTOS,
        umi_nombre as UNIDAD_MEDIDA, ind_formulaCalculo AS FORMULA_INDICADOR, IF(pf.sts_id = 1,"HABILITADO","INHABILITADO") AS ESTADO,
        SUM(IF(tri_id = 0, aux.mcot_numeradorT2, 0)) + SUM(IF(tri_id = 0, aux.mcot_numeradorT4, 0)) AS NUMERADOR_PROGRAMADO_ANUAL,
        SUM(IF(tri_id = 0, aux.mcot_denominadorT2, 0)) + SUM(IF(tri_id = 0, aux.mcot_denominadorT4, 0)) AS DENOMINADOR_PROGRAMADO_ANUAL,
        SUM(IF(tri_id = 2, aux.mcot_numeradorT2, 0)) + SUM(IF(tri_id = 2, aux.mcot_numeradorT4, 0)) AS NUMERADOR_ACTUALIZADO_ANUAL,
        SUM(IF(tri_id = 2, aux.mcot_denominadorT2, 0)) + SUM(IF(tri_id = 2, aux.mcot_denominadorT4, 0)) AS DENOMINADOR_ACTUALIZADO_ANUAL,
        (alc_trimestre2 + alc_trimestre4) META_ALCANZADA_ANUAL,
        SUM(IF(tri_id = 0, aux.mcot_numeradorT2, NULL)) NUMERADOR_PROGRAMADO_S1,
        SUM(IF(tri_id = 0, aux.mcot_denominadorT2, NULL)) AS DENOMINADOR_PROGRAMADO_S1,
        SUM(IF(tri_id = 0, aux.mcot_numeradorT4, NULL)) NUMERADOR_PROGRAMADO_S2,
        SUM(IF(tri_id = 0, aux.mcot_denominadorT4, NULL)) AS DENOMINADOR_PROGRAMADO_S2,
        SUM(IF(tri_id = 2, aux.mcot_numeradorT2, NULL)) NUMERADOR_ACTUALIZADO_S1,
        SUM(IF(tri_id = 2, aux.mcot_numeradorT2, NULL)) DENOMINADOR_ACTUALIZADO_S1,
        SUM(IF(tri_id = 2, aux.mcot_numeradorT4, NULL)) NUMERADOR_ACTUALIZADO_S2,
        SUM(IF(tri_id = 2, aux.mcot_numeradorT4, NULL)) DENOMINADOR_ACTUALIZADO_S2,
        alc_trimestre2 AS META_ALCANZADA_S1,
        alc_trimestre4 AS META_ALCANZADA_S2
        FROM cat_Programas prg
        INNER JOIN cat_Subprogramas spr USING ( prgs_id )
        INNER JOIN enc_UrSubprogramas s USING ( prgs_id, sprs_id )
        INNER JOIN cat_ProductoFinal USING ( pdf_id )
        INNER JOIN enc_ProductoFinalIndicador pf USING ( pdf_id )
        INNER JOIN cat_MetaComponente m USING ( gpo_id, ur_id, prgs_id, sprs_id, epfi_id )
        INNER JOIN aux_HistoricoModificacionMetaComponente aux USING ( gpo_id, ur_id, prgs_id, sprs_id, epfi_id )
        INNER JOIN cat_UnidadResponsable USING ( gpo_id, ur_id )
        INNER JOIN cat_Indicador i USING ( nim_id, ind_id )
        INNER JOIN cat_UnidadMedidaVariable u USING ( umv_id )
        INNER JOIN cat_UnidadMedidaIndicador USING (umi_id)
        INNER JOIN (
            SELECT gpo_id, ur_id, prgs_id, sprs_id,
            epfi_id,-- Se actualizo el nombre de de moct a mcot
            SUM( IF ( tri_id = 2, mcot_alcanzado, 0 ) ) AS alc_trimestre2,
            SUM( IF ( tri_id = 4, mcot_alcanzado, 0 ) ) AS alc_trimestre4
            FROM cat_MetaComponenteTrimestre
            GROUP BY gpo_id, ur_id, prgs_id, sprs_id, epfi_id 
        ) mt USING ( gpo_id, ur_id, prgs_id, sprs_id, epfi_id )
        LEFT JOIN (
            SELECT gpo_id, ur_id, prgs_id, sprs_id,
            GROUP_CONCAT( mvcm_nombre, ";" ) AS mvcm_nombre
            FROM cat_MedioVerificacionComponente
            GROUP BY gpo_id, ur_id, prgs_id, sprs_id
        ) medios ON s.gpo_id = medios.gpo_id
        AND s.ur_id = medios.ur_id
        AND s.prgs_id = medios.prgs_id
        AND s.sprs_id = medios.sprs_id
        LEFT JOIN (
            SELECT gpo_id, ur_id, prgs_id, sprs_id,
            GROUP_CONCAT( spc_nombre, ";" ) AS spc_nombre
            FROM cat_SupuestoComponente
            GROUP BY gpo_id, ur_id, prgs_id, sprs_id 
        ) sup ON s.gpo_id = sup.gpo_id
        AND s.ur_id = sup.ur_id
        AND s.prgs_id = sup.prgs_id
        AND s.sprs_id = sup.sprs_id
        WHERE s.gpo_id is not null
        GROUP BY s.gpo_id, s.ur_id, s.prgs_id,-- Se agregaron los siguientes group by
        s.sprs_id, ind_id, ind_nombre, ind_nombreMIR, ind_definicionMIR, umv_id, m.mco_lb,
        pdf_id ORDER BY s.gpo_id, s.ur_id;
    ';

    $hoy = date('Y/m/d');

    if ($hoy >= APP_EJERCICIO . '/01/01' && $hoy <= APP_EJERCICIO . '/03/31') {
        $trimestre = 1;
    } elseif ($hoy >= APP_EJERCICIO . '/03/31' && $hoy <= APP_EJERCICIO . '/06/30') {
        $trimestre = 2;
    } elseif ($hoy >= APP_EJERCICIO . '/06/30' && $hoy <= APP_EJERCICIO . '/09/30') {
        $trimestre = 3;
    } elseif ($hoy >= APP_EJERCICIO . '/09/30' && $hoy <= APP_EJERCICIO . '/12/31') {
        $trimestre = 4;
    } else {
        $trimestre = 0;
    }

    $sql86 = '
        SELECT ur_clave AS UR, ur_nombre AS UNIDAD_RESPONSABLE,
            prgs_id AS PRG, prg_nombre AS PROGRAMA, vpm_resumenNarrativo RESUMEN_NARRATIVO,
            ID_EJE, EJE, ID_TEMA, TEMA, ID_OBJ,OBJETIVO, ID_SECTOR,
            SECTOR, ind_id AS IND, ind_nombre AS NOMBRE_INDICADOR,
            ind_nombreMIR AS NOMBRE_INDICADOR_MIR,
            ind_definicionMIR AS INDICADOR_DEFINICION_MIR,
            ind_formulaCalculo AS METODO_CALCULO,
            umi_nombre AS UNIDAD_MEDIDA,
            CASE
                WHEN umi_id = 5 THEN
                aux.mpr_anterior ELSE aux.mpr_lb
            END AS AÑO_LINEA_BASE,
            CASE
                WHEN umi_id = 2
                OR umi_id = 4
                OR umi_id = 5 THEN
                -- Se hace la suma, ya que si se agrupa se deben sumar los montos
                    SUM(aux.mpr_lbIndicador) ELSE SUM(aux.mpr_lbValor)
                END AS VALOR_LINEA_BASE,
            CASE
                WHEN umi_id IN(2,3,4) THEN "(A/B)*100"
                WHEN umi_id = 5 THEN "((A/B)-1)*100"
                END AS FÓRMULA_CALCULO,
                SUM(IF(tri_id = 0, aux.mpr_numeradorT1, 0)) AS NUMERADOR_PROGRAMADO,
                SUM(IF(tri_id = 0, aux.mpr_denominadorT1, 0)) AS DENOMINADOR_PROGRAMADO,
                SUM(IF(tri_id = ' . $trimestre . ', aux.mpr_numeradorT1, 0)) AS NUMERADOR_ACTUALIZADO,
                SUM(IF(tri_id = ' . $trimestre . ', aux.mpr_denominadorT1, 0)) AS DENOMINADOR_ACTUALIZADO,
                mp.mpr_alcanzado AS META_ALCANZADA,
                MEDIO_DE_VERIFICACION,
                SUPUESTO
        FROM cat_Mir
        INNER JOIN vin_ProgramasMir USING ( mir_id )
        INNER JOIN (
            SELECT prgs_id, sp.spe_id ID_SECTOR, spe_nombre SECTOR, eje_id AS ID_EJE, eje_nombre AS EJE,
                tem_id ID_TEMA,tem_nombre TEMA, obj_id AS ID_OBJ, obj_nombre AS OBJETIVO
            FROM vin_Programa
            INNER JOIN cat_SectorPes sp USING ( spe_id )
            INNER JOIN enc_PedObjetivo USING(epo_id)
            INNER JOIN cat_Eje USING(eje_id)
            INNER JOIN cat_Tema USING(eje_id,tem_id)
            INNER JOIN cat_Objetivo USING(eje_id,tem_id,obj_id)
        ) e USING ( prgs_id )
        INNER JOIN cat_UnidadResponsable USING ( gpo_id, ur_id )
        INNER JOIN ( SELECT gpo_id, ur_id, prg_id AS prgs_id, prg_nombre FROM cat_Programa ) p USING ( gpo_id, ur_id, prgs_id )
        INNER JOIN vin_ProgramaMirIndicador v USING ( vpm_id )
        INNER JOIN cat_Indicador i USING ( nim_id, ind_id )
        INNER JOIN cat_UnidadMedidaVariable u USING ( umv_id )
        INNER JOIN cat_UnidadMedidaIndicador USING (umi_id)
        -- Se cambio a inner
        INNER JOIN cat_MetaProposito mp USING ( pmi_id )
        INNER JOIN aux_HistoricoModificacionMetaProposito aux USING (pmi_id)
        LEFT JOIN ( SELECT group_concat( spm_nombre, "- " ) AS SUPUESTO, vpm_id FROM cat_SupuestoProgramaMir WHERE nim_id = 2 GROUP BY vpm_id ) SupFin USING ( vpm_id )
        LEFT JOIN ( SELECT group_concat( mvpm_nombre, "- " ) AS MEDIO_DE_VERIFICACION, vpm_id FROM cat_MedioVerificacionProgramaMir WHERE nim_id = 2 GROUP BY vpm_id ) MevFin USING ( vpm_id )
        WHERE nim_id = 2 AND v.sts_id = 1
        GROUP BY
        -- Se agrupa por mpr_lb
        pmi_id,mp.mpr_lb,mp.mpr_id
        ORDER BY gpo_id, ur_id;;
    ';

    $sql87 = '
        SELECT mme_gpo_id,mme_ur_id,mme_id,
            mme_fechaFolio,mme_fechaAutorizacion,
            mme_oficioSolicitudSefin,mme_concepto,
            emo_nombre,mme_oficioSefin,omo_id
        FROM scp_ModificacionMeta
        INNER JOIN cat_OrigenModificacion using(omo_id)
        INNER JOIN cat_EstadoModificacion using(emo_id)
        WHERE tfm_id = "C"
        ORDER BY omo_id,mme_gpo_id,mme_ur_id,mme_id;
    ';

    $sql88 = '
        SELECT omo_id, mme_gpo_id, mme_ur_id, mme_id, gpo_id, ur_id, prgs_id, sprs_id, epfi_id,
            tri_id, mco_lb, mco_lbValor, mco_lbIndicador, mco_anio, mco_original, mco_numerador,
            mco_denominador, mco_modificado, mco_alcanzado, mco_trimestreCierre, mcot_numeradorT2,
            mcot_numeradorT4, mcot_denominadorT2, mcot_denominadorT4, tmf_id
        FROM scp_ModificacionMeta m
        INNER JOIN scp_DetalleModificacionMetaComponente d using(omo_id,mme_gpo_id,mme_ur_id,mme_id)
        WHERE m.tfm_id = "C";
    ';
    $sql89 = '
        SELECT gpo_id, ur_id, ur_cargoTitular AS cargoTitular,  CONCAT (ur_tituloTitular," " , ur_nombreTitular) AS nombreTitular, 
        ur_correoElectronicoTitular AS correoElectronicoTitular, ur_celularTitular AS celularTitular, ur_telefonoTitular AS telefonoTitular, 
        ur_extensionTitular AS extensionTitular,
        ur_cargoAdministrativo AS cargoAdministrativo, CONCAT(ur_tituloAdministrativo," ", ur_nombreAdministrativo) AS nombreAdministrativo, 
        ur_correoElectronicoAdministrativo AS correoElectronicoAdministrativo, ur_celularAdministrativo AS celularAdministrativo, 
        ur_telefonoAdministrativo AS telefonoAdministrativo, ur_extensionAdministrativo AS extensionAdministrativo,
        ur_cargoPlaneacion AS cargoPlaneacion, CONCAT(ur_tituloPlaneacion, " ", ur_nombrePlaneacion) AS nombrePlaneacion, 
        ur_correoElectronicoPlaneacion AS correoElectronicoPlaneacion, ur_celularPlaneacion AS celularPlaneacion, 
        ur_telefonoPlaneacion AS telefonoPlaneacion, ur_extensionPlaneacion AS extensionPlaneacion, 
        ur_cargoEvaluacion AS cargoSeguimiento, CONCAT(ur_tituloEvaluacion, " ", ur_nombreEvaluacion) AS nombreSeguimiento, 
        ur_correoElectronicoEvaluacion AS correoElectronicoSeguimiento, ur_celularEvaluacion AS celularSeguimiento, 
        ur_telefonoEvaluacion AS telefonoSeguimiento, 
        ur_extensionEvaluacion AS extensionSeguimiento
        FROM aux_UnidadResponsable
        WHERE gpo_id !=0 and ur_id !=0
        ORDER BY gpo_id, ur_id'
    ;

    $gpoId = (int) $_SESSION[_S]['clasAdm-gpo'];
    $urId = (int) $_SESSION[_S]['clasAdm-ur'];

    $sqlVinculacionSelect = '
        SELECT
            #PROGRAMA PRESUPUESTARIO
            p.prg_id AS ID_PRG,
            p.prg_nombre AS PROGRAMA,
            #PLAN ESTATAL DE DESARRO (PED)
            cat_Eje.eje_id AS ID_EJE,
            cat_Eje.eje_nombre AS EJE,
            ct.tem_id AS ID_TEMA,
            ct.tem_nombre AS TEMA,
            co.obj_id AS ID_OBJ,
            co.obj_nombre AS OBJETIVO,
            ce.est_id AS ID_ESTRATEGIA,
            ce.est_nombre AS ESTRATEGIA,
            #PLAN ESTRATÉGICO SECTORIAL (PES)
            csp.spe_id AS ID_SECTOR,
            csp.spe_nombre AS SECTOR,
            cop.obp_id AS ID_OBJPES,
            cop.obp_nombre AS OBJETIVO_PES,
            cep.esp_id AS ID_ESTRAT_PES,
            cep.esp_nombre AS ESTRAT_PES,
            #PLAN ESPECIAL (PE)
            IF(IFNULL(cpe.ple_id, 0) = 0, NULL, cpe.ple_id) AS ID_PLANESP,
            IF(IFNULL(cpe.ple_id, 0) = 0, "NO APLICA", cpe.ple_nombre) AS PLAN_ESPECIAL,
            IF(IFNULL(cpe.ple_id, 0) = 0, NULL, cope.ope_id) AS ID_OBJESP,
            IF(IFNULL(cpe.ple_id, 0) = 0, NULL, cope.ope_nombre) AS OBJETIVO_PLAN_ESPECIAL,
            IF(IFNULL(cpe.ple_id, 0) = 0, NULL, cepe.espe_id) AS ID_ESTRESP,
            IF(IFNULL(cpe.ple_id, 0) = 0, NULL, cepe.espe_nombre) AS ESTRATEGI_PLAN_ESPECIAL,
            #ODS
            cods.ods_id AS ID_ODS,
            cods.ods_nombre AS ODS,
            #INDICADOR fin y propósito
            IF(indicadores.fin_ind_id IS NULL, NULL, "FIN") AS NIVEL_INDICADOR,
            indicadores.fin_ind_id AS IND,
            indicadores.fin_nombre AS INDICADOR,
            IF(indicadores.pro_ind_id IS NULL, NULL, "PROPÓSITO") AS NIVEL_INDICADOR2,
            indicadores.pro_ind_id AS IND2,
            indicadores.pro_nombre AS INDICADOR4,
            #PRESUPUESTO APROBADO (no ejercido)
            pre.APROBADO AS APROBADO
    ';

    $sqlVinculacionJoins = '
        LEFT JOIN enc_Ped pe ON pe.epe_id = vin.epe_id
        LEFT JOIN cat_Eje ON cat_Eje.eje_id = pe.eje_id
        LEFT JOIN cat_Tema ct ON ct.eje_id = pe.eje_id AND ct.tem_id = pe.tem_id
        LEFT JOIN cat_Objetivo co ON co.eje_id = pe.eje_id AND co.tem_id = pe.tem_id AND co.obj_id = pe.obj_id
        LEFT JOIN cat_Estrategia ce ON ce.eje_id = pe.eje_id AND ce.tem_id = pe.tem_id AND ce.obj_id = pe.obj_id AND ce.est_id = pe.est_id
        LEFT JOIN enc_Pes ep ON ep.eps_id = vin.eps_id
        LEFT JOIN cat_SectorPes csp ON csp.spe_id = ep.spe_id
        LEFT JOIN cat_ObjetivoPes cop ON cop.spe_id = ep.spe_id AND cop.obp_id = ep.obp_id
        LEFT JOIN cat_EstrategiaPes cep ON cep.spe_id = ep.spe_id AND cep.obp_id = ep.obp_id
            AND cep.esp_id = COALESCE(
                NULLIF(ep.esp_id, 0),
                (SELECT MIN(e2.esp_id) FROM enc_Pes e2 WHERE e2.spe_id = ep.spe_id AND e2.obp_id = ep.obp_id AND e2.esp_id > 0),
                (SELECT MIN(c2.esp_id) FROM cat_EstrategiaPes c2 WHERE c2.spe_id = ep.spe_id AND c2.obp_id = ep.obp_id)
            )
        LEFT JOIN enc_Pep pep ON pep.epc_id = vin.epc_id
        LEFT JOIN cat_PlanEspecial cpe ON cpe.ple_id = pep.ple_id AND pep.ple_id <> 0
        LEFT JOIN cat_ObjetivoPe cope ON cope.ple_id = pep.ple_id AND cope.ope_id = pep.ope_id
        LEFT JOIN cat_EstrategiaPe cepe ON cepe.ple_id = pep.ple_id AND cepe.ope_id = pep.ope_id AND cepe.espe_id = pep.espe_id
        LEFT JOIN enc_PedODS ods ON ods.epods_id = vin.epods_id
        LEFT JOIN cat_ODS cods ON cods.ods_id = ods.ods_id
        LEFT JOIN (
            SELECT
                prgs_id,
                rn,
                MAX(CASE WHEN nim_nombre = "FIN" THEN ind_id END) AS fin_ind_id,
                MAX(CASE WHEN nim_nombre = "FIN" THEN ind_nombreMIR END) AS fin_nombre,
                MAX(CASE WHEN nim_nombre = "PROPÓSITO" THEN ind_id END) AS pro_ind_id,
                MAX(CASE WHEN nim_nombre = "PROPÓSITO" THEN ind_nombreMIR END) AS pro_nombre
            FROM (
                SELECT
                    vpm.prgs_id,
                    nm.nim_nombre,
                    ind.ind_id,
                    ind.ind_nombreMIR,
                    ROW_NUMBER() OVER (PARTITION BY vpm.prgs_id, nm.nim_nombre ORDER BY ind.ind_id) AS rn
                FROM vin_ProgramasMir vpm
                INNER JOIN vin_ProgramaMirIndicador vinm ON vinm.vpm_id = vpm.vpm_id AND vinm.trim_0 = 1
                INNER JOIN cat_NivelMir nm ON nm.nim_id = vinm.nim_id AND nm.nim_nombre IN ("FIN", "PROPÓSITO")
                INNER JOIN cat_Indicador ind ON ind.ind_id = vinm.ind_id AND ind.nim_id = vinm.nim_id
                GROUP BY vpm.prgs_id, nm.nim_nombre, ind.ind_id, ind.ind_nombreMIR
            ) indNiv
            GROUP BY prgs_id, rn
        ) indicadores ON indicadores.prgs_id = p.prg_id
    ';

    $sql90 = $sqlVinculacionSelect . '
        FROM (
            SELECT pa.gpo_id, pa.ur_id, enc.prg_id, SUM(pa.pre_aprobado) AS APROBADO
            FROM presupuestoAprobado pa
            JOIN enc_Programatico enc USING (epg_id, gpo_id, ur_id)
            WHERE enc.prg_id <> 0 AND enc.prg_id NOT IN (997, 998, 999)
            GROUP BY pa.gpo_id, pa.ur_id, enc.prg_id
        ) pre
        JOIN cat_Programa p ON p.prg_id = pre.prg_id AND p.gpo_id = pre.gpo_id AND p.ur_id = pre.ur_id
        LEFT JOIN (
            SELECT enc.gpo_id, enc.ur_id, enc.prg_id, vi.epe_id, vi.eps_id, vi.epc_id, vi.epods_id
            FROM enc_Programatico enc
            JOIN vin_Programatico vi USING (epg_id)
            WHERE enc.prg_id <> 0 AND enc.prg_id NOT IN (997, 998, 999)
            GROUP BY enc.gpo_id, enc.ur_id, enc.prg_id, vi.epe_id, vi.eps_id, vi.epc_id, vi.epods_id
        ) vin ON vin.gpo_id = pre.gpo_id AND vin.ur_id = pre.ur_id AND vin.prg_id = pre.prg_id
        ' . $sqlVinculacionJoins . '
        WHERE pre.gpo_id = ' . $gpoId . ' AND pre.ur_id = ' . $urId . '
        GROUP BY
            p.prg_id, p.prg_nombre, pre.APROBADO,
            cat_Eje.eje_id, ct.tem_id, co.obj_id, ce.est_id,
            csp.spe_id, cop.obp_id, cep.esp_id,
            cpe.ple_id, cope.ope_id, cepe.espe_id,
            cods.ods_id,
            indicadores.rn, indicadores.fin_ind_id, indicadores.pro_ind_id
        ORDER BY p.prg_id, ce.est_id, cep.esp_id, cepe.espe_id, indicadores.rn
    ';

    $sql91 = $sqlVinculacionSelect . '
        FROM (
            SELECT enc.prg_id, SUM(pa.pre_aprobado) AS APROBADO
            FROM presupuestoAprobado pa
            JOIN enc_Programatico enc USING (epg_id, gpo_id, ur_id)
            WHERE enc.prg_id <> 0 AND enc.prg_id NOT IN (997, 998, 999)
            GROUP BY enc.prg_id
        ) pre
        JOIN (
            SELECT prg_id, MIN(prg_nombre) AS prg_nombre
            FROM cat_Programa
            WHERE prg_id <> 0 AND prg_id NOT IN (997, 998, 999)
            GROUP BY prg_id
        ) p ON p.prg_id = pre.prg_id
        LEFT JOIN (
            SELECT enc.prg_id, vi.epe_id, vi.eps_id, vi.epc_id, vi.epods_id
            FROM enc_Programatico enc
            JOIN vin_Programatico vi USING (epg_id)
            WHERE enc.prg_id <> 0 AND enc.prg_id NOT IN (997, 998, 999)
            GROUP BY enc.prg_id, vi.epe_id, vi.eps_id, vi.epc_id, vi.epods_id
        ) vin ON vin.prg_id = pre.prg_id
        ' . $sqlVinculacionJoins . '
        GROUP BY
            p.prg_id, p.prg_nombre, pre.APROBADO,
            cat_Eje.eje_id, ct.tem_id, co.obj_id, ce.est_id,
            csp.spe_id, cop.obp_id, cep.esp_id,
            cpe.ple_id, cope.ope_id, cepe.espe_id,
            cods.ods_id,
            indicadores.rn, indicadores.fin_ind_id, indicadores.pro_ind_id
        ORDER BY p.prg_id, ce.est_id, cep.esp_id, cepe.espe_id, indicadores.rn
    ';

    /***********************************/
    /*FIN SE ASIGNACION DE CONSULTAS*/
    /**********************************/

    /*SE ASIGNA EL GRUPO Y UR A UNA VARIABLE.*/
    $fn = $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'];

    /***********************************************************************************/
    /*EN ESTE SWITCH IDENTIFICAMOS QUE CONSULTA ES LA QUE SE VA A MANDAR A LLAMAR, ES DECIR DE QUE
    TABLA, Y ESTO MEDIANTE EL VALOR QUE RECIBIMOS.*/
    /*PRIMERO MANDA A LLAMAR A HEADER QUE PERMITIRA GENERAR EL ARCHIVO DE XML DONDE LE DECIMOS EL NOMBRE QUE
    LLEVARA EL ARCHIVO, DESPUES EL TIPO DE ARCHIVO QUE SERA (EN ESTE CASO XML),
    E MANDAMOS A IMPRIMIR LO QUE NOS DEVUELVA LA FUNCION DE mysql_XML DONDE DE PARAMETRO LE ENVIAMOS LA VARIABLE DE LA CONSULTA.(EJEMPLO $sql)*/
    /**********************************************************************************/
    switch ($opcion) {
        case 'R-C':
            header('Content-Disposition: attachment; filename="' . $fn . 'scp_Clc.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql1, $param);
            break;
        case 'M-C':
            header('Content-Disposition: attachment; filename="' . $fn . 'scp_Afectacion.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql2, $param);
            break;
        case 'M-HA':
            header('Content-Disposition: attachment; filename="' . $fn . 'UnidadesEjecutoras.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql69, $param);
            break;
        case 'M-HB':
            header('Content-Disposition: attachment; filename="' . $fn . 'Programas.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql70, $param);
            break;
        case 'M-HC':
            header('Content-Disposition: attachment; filename="' . $fn . 'SubProgramas.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql71, $param);
            break;
        case 'M-HD':
            header('Content-Disposition: attachment; filename="' . $fn . 'Proyectos.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql72, $param);
            break;
        case 'M-HE':
            header('Content-Disposition: attachment; filename="' . $fn . 'Obras.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql73, $param);
            break;
        case 'M-UR':
            header('Content-Disposition: attachment; filename="DirectorioUR.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql89, $param);
            break;
        case 'D-C':
            header('Content-Disposition: attachment; filename="' . $fn . 'scp_Numeros de control.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql3, $param);
            break;
        case 'D-H':
            header('Content-Disposition: attachment; filename="' . $fn . 'ObjetoGasto.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql4, $param);
            break;
        case 'M-H':
            header('Content-Disposition: attachment; filename="' . $fn . 'scp_Numeros de control Cere.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql5, $param);
            break;
        case 'M-O':
            header('Content-Disposition: attachment; filename="' . $fn . 'scp_Cerecos.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql6, $param);
            break;
        case 'D-O':
            header('Content-Disposition: attachment; filename="' . $fn . 'scp_ControlContableCereco.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql7, $param);
            break;
        case 'M-T':
            header('Content-Disposition: attachment; filename="' . $fn . 'MaestroModificacion.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql8, $param);
            break;
        case 'D-T':
            header('Content-Disposition: attachment; filename="' . $fn . 'DetalleModificacion.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql9, $param);
            break;
        case 'P':
            header('Content-Disposition: attachment; filename="' . $fn . 'Presupuesto.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql10, $param);
            break;
        case 'C-B':
            header('Content-Disposition: attachment; filename="' . $fn . 'cta_bancos.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql11, $param);
            break;
        case 'C-CF':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_ClaveFinanciamiento.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql12, $param);
            break;
        case 'C-UE':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_UnidadEjecutora.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql13, $param);
            break;
        case 'C-P':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_Proyecto.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql14, $param);
            break;
        case 'C-OA':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_ObraAccion.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql15, $param);
            break;
        case 'C-PE':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_PartidaEspecifica.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql16, $param);
            break;
        case 'C-PG':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_PartidaGenerica.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql17, $param);
            break;
        case 'C-PC':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_PlanCuentas.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql18, $param);
            break;
        case 'D-CC':
            header('Content-Disposition: attachment; filename="' . $fn . 'scp_d_corced.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql19, $param);
            break;
        case 'M-CC':
            header('Content-Disposition: attachment; filename="' . $fn . 'scp_m_concerd.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql20, $param);
            break;
        case 'C-R':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_Ramo.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql21, $param);
            break;
        case 'C-SP':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_SectorProductivo.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql22, $param);
            break;
        case 'C-SA':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_SectorAdministrativo.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql23, $param);
            break;
        case 'C-E':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_Eje.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql24, $param);
            break;
        case 'C-PRG':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_Programa.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql25, $param);
            break;
        case 'C-S':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_Subprograma.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql26, $param);
            break;
        case 'C-F':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_Finalidad.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql27, $param);
            break;
        case 'C-FUN':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_Funcion.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql28, $param);
            break;
        case 'C-SFUN':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_Subfuncion.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql29, $param);
            break;
        case 'C-CAP':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_Capitulo.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql30, $param);
            break;
        case 'C-CON':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_Concepto.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql31, $param);
            break;
        case 'C-TG':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_TipoGasto.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql32, $param);
            break;
        case 'C-OG':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_ObjetoGasto.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql33, $param);
            break;
        case 'C-EC':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_EstadoClc.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql34, $param);
            break;
        case 'C-TC':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_TipoClc.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql35, $param);
            break;
        case 'C-TFM':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_TipoFolioModificacion.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql36, $param);
            break;
        case 'C-TM':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_TipoModificacion.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql37, $param);
            break;
        case 'C-OM':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_OrigenModificacion.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql38, $param);
            break;
        case 'C-TN':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_TipoNomina.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql39, $param);
            break;
        case 'C-SOC':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_Sociedad.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql40, $param);
            break;
        case 'C-PROV':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_Proveedor.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql41, $param);
            break;
        case 'C-IB':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_InstitucionesBancarias.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql42, $param);
            break;
        case 'C-SOR':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_SuborigenRecurso.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql43, $param);
            break;
        case 'C-OR':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_OrigenRecurso.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql44, $param);
            break;
        case 'C-SF':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_Subfuente.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql45, $param);
            break;
        case 'C-FTE':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_Fuente.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql46, $param);
            break;
        case 'C-IP':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_IdentificadorPartida.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql47, $param);
            break;
        case 'C-UM':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_UnidadMedida.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql48, $param);
            break;
        case 'C-TM':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_TipoMedida.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql49, $param);
            break;
        case 'C-AP':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_AccionProgramada.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql50, $param);
            break;
        case 'C-ZA':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_ZonaAplicacion.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql51, $param);
            break;
        case 'C-GV':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_GrupoVulnerable.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql52, $param);
            break;
        case 'C-PT':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_PoliticasTransversales.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql53, $param);
            break;
        case 'C-IE':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_IndiacdorEstrategico.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql54, $param);
            break;
        case 'C-IG':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_IndiacdorGestion.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql55, $param);
            break;
        case 'MP':
            header('Content-Disposition: attachment; filename="' . $fn . 'Moviemiento Proveedor.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql56, $param);
            break;
        case 'C-Est':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_Estrategia.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql57, $param);
            break;
        case 'C-LinAc':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_LineaAccion.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql58, $param);
            break;
        case 'C-Tema':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_LineaAccion.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql59, $param);
            break;
        case 'C-Obj':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_LineaAccion.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql60, $param);
            break;
        case 'C-EstTrans':
            header('Content-Disposition: attachment; filename="' . $fn . 'cat_LineaAccion.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql61, $param);
            break;
        case 'P-D':
            header('Content-Disposition: attachment; filename="' . $fn . 'PresupuestoDetalle.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql62, $param);
            break;
        case 'P-1':
            header('Content-Disposition: attachment; filename="' . $fn . 'Indicadores.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql63, $param);
            break;
        case 'P-2':
            header('Content-Disposition: attachment; filename="' . $fn . 'Productos_finales.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql64, $param);
            break;
        case 'P-3':
            header('Content-Disposition: attachment; filename="' . $fn . 'Productos_intermedios.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql65, $param);
            break;
        case 'R-M':
            header('Content-Disposition: attachment; filename="' . $fn . 'ReporteControlMIR.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql66, $param);
            break;
        case 'C-PF':
            header('Content-Disposition: attachment; filename="' . $fn . 'Cat_Productos_finales.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql67, $param);
            break;
        case 'C-PI':
            header('Content-Disposition: attachment; filename="' . $fn . 'Cat_Productos_intermedios.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql68, $param);
            break;
        case 'P-4':
            header('Content-Disposition: attachment; filename="' . $fn . 'Cat_Productos_Finales_Metas.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql74, $param);
            break;
        case 'P-5':
            header('Content-Disposition: attachment; filename="' . $fn . 'Cat_Productos_Intermedios_Metas.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql75, $param);
            break;
        case 'P-51':
            header('Content-Disposition: attachment; filename="' . $fn . 'Cat_Productos_Intermedios_Metas_Inhabilitados.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql82, $param);
            break;
        case 'P-52':
            header('Content-Disposition: attachment; filename="' . $fn . 'Cat_Productos_Intermedios_Metas_HabilitadosInhabilitados.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql83, $param);
            break;
        case 'P-6':
            header('Content-Disposition: attachment; filename="' . $fn . 'Cat_Nivel_Fin_Metas.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql76, $param);
            break;
        case 'P-7':
            header('Content-Disposition: attachment; filename="' . $fn . 'Cat_Nivel_Proposito_Metas.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql77, $param);
            break;
        case 'P-8':
            header('Content-Disposition: attachment; filename="' . $fn . 'Seguimiento_Productos_Finales_Metas.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql78, $param);
            break;
        case 'P-10':
            header('Content-Disposition: attachment; filename="' . $fn . 'Seguimiento_Productos_Intermedios_Metas.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql79, $param);
            break;
        case 'R-PFN':
            header('Content-Disposition: attachment; filename="' . $fn . 'Avance_Programatico_Financiero.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql80, $param);
            break;
        case 'R-PFS':
            header('Content-Disposition: attachment; filename="' . $fn . 'Avance_Programatico_Fisico.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql81, $param);
            break;
        case 'P-84':
            header('Content-Disposition: attachment; filename="' . $fn . 'SeguimientoActividades(PI).xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql84, $param);
            break;
        case 'P-85':
            header('Content-Disposition: attachment; filename="' . $fn . 'SeguimientoComponentes(PF).xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql85, $param);
            break;
        case 'P-86':
            header('Content-Disposition: attachment; filename="' . $fn . 'SeguimientoProposito.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql86, $param);
            break;
        case 'T-FMC':
            header('Content-Disposition: attachment; filename="ModificacionMetaComponente.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql87, $param);
            break;
        case 'T-DFMC':
            header('Content-Disposition: attachment; filename="DetalleModificacionMetaComponente.xml' . '";');
            header('Content-type: text/xml');
            echo mysql_XML($sql88, $param);
            break;
        case 'C-CH':
            header('Content-Disposition: attachment; filename="vinculacionprogramaticapresupuestal.xml";');
            header('Content-type: text/xml');
            echo mysql_XML($sql90, []);
            break;
        case 'C-ALL':
            header('Content-Disposition: attachment; filename="vinculacionprogramaticapresupuestalall.xml";');
            header('Content-type: text/xml');
            echo mysql_XML($sql91, []);
            break;
    }
    /************************************************/
    /*FIN DE SWITCH*/
    /***********************************************/
}

function mysql_XML($sql, $param)
{
    /*SE REALIZA LA CONSULTA, QUE SE MANDA MEDIANTE EL PARAMETRO.*/
    $webResp = new WebResponse();
    $webResp->conectar();
    $webResp->setSql($sql);
    $webResp->consultar($param);
    $webResp->desconectar();

    /*TIPO DE ARCHIVO*/
    //Ahora se genera con UTF-8 para mostrar acentos
    $dom = new DOMDocument('1.0', 'UTF-8');
    /*CREAMOS TR, QUE LLEVAN EL NOMBRE DE RESULTADOS.*/
    $doc = $dom->appendChild($dom->createElement('Resultados'));

    $campo = [];
    $valor = [];

    //llenamos el array de nombres de campos
    $i = 0;

    /*AQUI SE EXTRA LOS REGISTROS*/
    while ($row = $webResp->extraer()) {

        // creamos el item
        $nodo = $doc->appendChild($dom->createElement('item'));

        foreach ($row as $nomCampo => $valorCampo) {
            $campo[$i] = $nomCampo;
            $valor[$i] = $valorCampo;

            $campoTexto = $nodo->appendChild($dom->createElement($campo[$i]));
            $campoTexto->appendChild($dom->createTextNode((string)($valor[$i] ?? '')));
            $i++;
        }
    }

    $webResp->getNumeroRegistros() === 1 ? $doc->appendChild($dom->createElement('item')) : ''; // se agrega esta linea por que cuando se devuelve un solo registro no se muestra el encabezado en excel.
    // retornamos el archivo XML como cadena de texto
    $dom->formatOutput = true;
    return $dom->saveXML();
}
