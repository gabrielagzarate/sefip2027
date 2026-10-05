<?php

ini_set('display_errors', 1);

session_start();
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
require_once '../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';

$gpo = $_POST['gpo_id'];
$ur = $_POST['ur_id'];
$clc = $_POST['clc_id'];

$ruta = utf8Decode('ReporteClc.xls');
header('Content-type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename=' . $ruta);
header('Pragma: no-cache');
header('Expires: 0');

$cadenaproductiva = '';

$rowExtraServPers = '';
$rowencabezado = '';
$cols = 0;

$whsub = '';
$whsub .= 'where gpo_id = :gpo_id  and ur_id = :ur_id  ';
$parametros = [
    'gpo_id' => $_POST['gpo_id'],
    'ur_id'  => $_POST['ur_id'],
];
$conexion = new AccesoDatos();
$conexion->conectar();

$sql = '
        SELECT ur_clave,oa_id, ur_nombre,oa_numobra ,ore_oficioAut,oa_nombre,oa_' . APP_EJERCICIO_ANTERIOR . ',sft_clave,sft_nombre,cfi_anio,ecf_id,
        obr_contrato,obr_avanceFisico,obr_avanceFinanciero,obr_cumplimiento,obr_vicios,obr_documentacion,obr_anticipo,obr_fianza,
        clc_id, scp_Clc.ocl_id ,DATE_FORMAT(clc_fechaElaboracion,"%d/%m/%Y") clc_fechaElaboracion,clc_concepto,
        clc_firmas,clc_beneficiario,clc_lineaCaptura, scp_Clc.tem_carga,
        clc_nombreAdministrativo, clc_cargoAdministrativo, clc_nombreTitular, clc_cargoTitular, clc_nombreAdministracion,clc_nombreRecursos,
        if(ecl_id <= 3,scp_Clc.ecl_id,scp_ClcAuxiliar.ecl_id) as ecl_id, ecl_nombre,soc_nombre,clc_rectificacion,
        mpc_id, mpc_nombre,
        bur_id,iba_nombre,
        fte_id,sft_id,ori_id,ori_nombre,cfi_clave,cfi_nombre, cfi_ejercicio,
        t.ter_id,ter_nombre,
        scp_Clc.tcl_id,tcl_nombre,scp_Clc.stc_id, stc_nombre,clc_recursoFederal,
        scp_Clc.tno_id,tno_nombre,scp_Clc.mno_id, mno_nombre, scp_Clc.smn_id, smn_nombre,
        afe_id,DATE_FORMAT(afe_fechaInicio,"%d/%m/%Y") afe_fechaInicio,DATE_FORMAT(afe_fechaFin,"%d/%m/%Y") afe_fechaFin,
        numcontrol,clave,clave_nombre,referencia,DATE_FORMAT(fecha_referencia,"%d/%m/%Y") fecha_referencia,
        percepcion_bruto,percepcion_iva,percepcion_neto, retencion_neto, tipo_movimiento, tipo_numcontrol,
        rfc_id,rfc_nombre, rfc_paterno, rfc_materno ,if(tdf_id="SDI",1,0) as automatica
    FROM scp_Clc
    LEFT JOIN scp_ClcAuxiliar USING (gpo_id,ur_id,clc_id,ecl_id)
    INNER JOIN cat_UnidadResponsable USING(gpo_id, ur_id)
    LEFT JOIN cat_ObraClc using(gpo_id,ur_id,clc_id)
    JOIN (
        select bur_id,iba_nombre from cat_BancosUr
        join cat_InstitucionesBancarias using(iba_id)
    ) bancosUr using(bur_id)
    INNER JOIN cat_EstadoClc using(ecl_id)
    INNER JOIN cat_ModoPagoClc using(mpc_id)
    INNER JOIN enc_ClaveFinanciamiento using(ecf_id)
    INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' using(cff_id,fte_id,sft_id,ori_id,sor_id/*, cso_id*/ ,cfi_id,cfi_anio)
    INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' using(cff_id,fte_id,sft_id,ori_id,sor_id)
    INNER JOIN ' . BD_SINPRES . '.cat_OrigenRecurso' . APP_EJERCICIO . ' using(cff_id,fte_id,sft_id,ori_id)
    INNER JOIN ' . BD_SINPRES . '.cat_Subfuente' . APP_EJERCICIO . ' using (cff_id,fte_id,sft_id)
    INNER JOIN ' . BD_SINPRES . '.cat_TipoRecurso t using(ter_id)
    INNER JOIN cat_SubtipoClc using(tcl_id,stc_id)
    INNER JOIN cat_TipoClc using(tcl_id)
        LEFT JOIN cat_TipoNomina using(tno_id)
        LEFT JOIN cat_ModalidadNomina using(mno_id)
        LEFT JOIN cat_SubmodalidadNomina using(mno_id, smn_id)
        /*para que salga el encabezado y el pie de pagina aunque no tenga afectaciones*/
    /*  AFECTACIONES Y SUS DETALLES SI ES QUE TIENE    --------*/
    LEFT JOIN(
        SELECT gpo_id,ur_id,clc_id,afe_id,afe_fechaInicio,afe_fechaFin,
                numcontrol,clave,clave_nombre,referencia,fecha_referencia,percepcion_bruto,percepcion_iva,
                percepcion_neto,retencion_neto,tipo_movimiento,rfc_id,rfc_nombre,rfc_paterno,rfc_materno,tipo_numcontrol,soc_nombre,bdo_id

        FROM  scp_Afectacion
        LEFT JOIN (
            SELECT gpo_id, ur_id, clc_id, afe_id,
                ncp_id numcontrol, concat(pre_claveCompleta,tcp_id) clave,pe_nombre clave_nombre,
                ncp_referencia referencia,ncp_fechaReferencia fecha_referencia,ncp_percepcionBruto percepcion_bruto,ncp_percepcionIva percepcion_iva,
                ncp_percepcionNeto percepcion_neto, 0 retencion_neto,"+" tipo_movimiento,rfc_id,rfc_nombre, rfc_paterno, rfc_materno,"P" tipo_numcontrol,soc_nombre,bdo_id
            FROM scp_Presupuestal detalle
                JOIN presupuesto USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
                LEFT JOIN (
                    SELECT rfc_id,rfc_nombre,rfc_paterno,rfc_materno,soc_nombre
                    FROM ' . BD_SINPRES . '.cat_Proveedores
                    INNER  JOIN cat_Proveedor USING(rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_SociedadProveedor using(soc_id)
                    )Prov USING(rfc_id)
            ' . $whsub . '
            UNION
            SELECT  detalle.gpo_id, detalle.ur_id, clc_id, afe_id,
                ncc_id numcontrol,
                CONCAT( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, ".", cta_gpo_id, cta_ur_id,".", cta_ue_id, ".", cgc_id, ".", cta_id ) clave,
                cta_nombre clave_nombre,ncc_referencia referencia,ncc_fechaReferencia fecha_referencia,
                0 percepcion_bruto, 0 percepcion_iva,ncc_percepcion percepcion_neto,ncc_retencion retencion_neto,
                ncc_tipoMovimiento tipo_movimiento,rfc_id,rfc_nombre, rfc_paterno, rfc_materno,"C" tipo_numcontrol,soc_nombre,bdo_id
            FROM scp_Contable detalle
                join cg_PlanCuentas using(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id)
                LEFT JOIN (
                    SELECT rfc_id,rfc_nombre,rfc_paterno,rfc_materno,soc_nombre
                    FROM ' . BD_SINPRES . '.cat_Proveedores
                    INNER  JOIN cat_Proveedor USING(rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_SociedadProveedor using(soc_id)
                )Prov USING(rfc_id)
            ' . $whsub . '
        ) detalles  USING(gpo_id,ur_id,clc_id,afe_id)
        ' . $whsub . '
    ) nc using(gpo_id, ur_id, clc_id)
      /* ------------------------------------------------------  */
    LEFT JOIN `enc_Programatico` USING(gpo_id,ur_id,epg_id)
    LEFT JOIN `cat_ObraActividad` USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
    LEFT JOIN `poa_OrigenRecurso` USING(gpo_id,ur_id,epg_id,ecf_id)
    INNER JOIN cat_BancoDocumentos using(gpo_id,ur_id,bdo_id,rfc_id)
    #LEFT JOIN `pla_ObrasImportadas` USING(gpo_id,ur_id,epg_id,ecf_id)

    ';

$wh = ' where ';
$wh .= ' gpo_id = :gpo_id  and ur_id = :ur_id and clc_id = :clc_id ';
$parametros += [
    'clc_id' => $_POST['clc_id'],
];
$sql .= $wh;
$sql .= 'order by tipo_movimiento, afe_id, numcontrol';
$conexion->setSql($sql);
$conexion->consultar($parametros);
$conexion->desconectar();

$percepciones = 0;
$retenciones = 0;
$tipomovanterior = '';
$clc_id = '';
$ur_clave = '';
$filas = 0;
$hoy = date('Y-m-d H:i:s');

if ($row = $conexion->extraer()) {
    switch ($row['tcl_id']) {
        //Clc de servicios personales***
        case 'A':
            if ($row['mno_id'] != '' or $row['mno_id'] != null) {
                $rowExtraServPers .= ' - ' . $row['mno_nombre'];
            }

            if ($row['smn_id'] != '' or $row['smn_id'] != null) {
                $rowExtraServPers .= ' - ' . $row['smn_nombre'];
            }

            break;

            //Clc de gastos de operacion***
        case 'B':
            break;
    }

    echo '<table align="center" width="1000px">';
    echo '<tr>';
    echo '<td colspan="2" align="left" style="font-size: 10px;"></td>';
    echo '<td colspan="7" style="font-size: 16px;" align="center"><b>GOBIERNO DEL ESTADO DE OAXACA</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td colspan="2" align="center" style="font-size: 16px;"></td>';
    echo '<td colspan="7" align="center" style="font-size: 16px;"><b>' . $row['ur_clave'] . ' ' . utf8Decode($row['ur_nombre']) . '</b></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="2" align="center" style="font-size: 16px;"></td>';
    echo '<td colspan="7" align="center" style="font-size: 16px;"><b>CUENTA POR LIQUIDAR CERTIFICADA (CLC) DE EROGACIONES PRESUPUESTALES PARA PAGO </b> </td>';
    echo '</tr>';

    $rowExtraServPers = '';
    $datosencabezado = '';
    $subtipo = '';
    if ($row['stc_id'] != 0) {
        $subtipo = ' - ' . $row['stc_nombre'];
    }

    echo '<tr>';
    echo '<td colspan="2" align="center" style="font-size: 16px;"></td>';
    echo '<td colspan="7" align="center" style="font-size: 16px;"><b>' . utf8Decode($row['tcl_nombre'] . $subtipo . $rowExtraServPers) . '</b></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="2" align="center" style="font-size: 16px;"></td>';
    echo '<td colspan="7" align="center" style="font-size: 16px;"></td>';
    echo '</tr>';

    // //row Linea de Captura
    if ($row['clc_lineaCaptura'] != '') {
        echo '<tr>';
        echo '<td colspan="2" align="center" style="font-size: 16px;"></td>';
        echo '<td colspan="7" align="center" style="font-size: 16px;"><td>';
        echo '<td colspan="2" align="right" style="font-size: 12px;"><b>Linea de Captura: ' . $row['clc_lineaCaptura'] . '</b>  </td>';
        echo '</tr>';

    }

    $impresion = false;
    if ($row['tcl_id'] == 'F' || $row['tcl_id'] == 'G' || $row['tcl_id'] == 'H') {
        $impresion = true;
    }

    if ($impresion == true) {
        echo '<tr>';
        echo '<td colspan="11"  align="left" style="font-size: 12px;"><b>POR MEDIO DEL PRESENTE, SIRVA MINISTRAR RECURSOS FINANCIEROS POR LA CANTIDAD DE $  </b></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td colspan="11" align="left"style="font-size: 12px;"><b> OBRA: ' . $row['oa_id'] . '-' . utf8Decode($row['oa_nombre']) . '</b></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td colspan="11" align="left"style="font-size: 12px;"><b> CLAVE PRESUPUESTAL:   ' . $row['clave'] . '</b></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td colspan="2" align="left"style="font-size: 12px;"><b> ' . utf8Decode('OFICIO DE AUTORIZACIÓN') . ':   ' . $row['ore_oficioAut'] . '</b></td>';
        echo '<td colspan="2" align="left"style="font-size: 12px;"><b> No.OBRA SSPP:   ' . $row['oa_numobra'] . '</b></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td colspan="2" align="left"style="font-size: 12px;"><b> SUBFUENTE:   ' . utf8Decode($row['sft_nombre']) . '</b></td>';
        echo '<td colspan="2" align="left"style="font-size: 12px;"><b> EJERCICIO:   ' . $row['cfi_anio'] . '</b></td>';
        echo '<td colspan="2" align="left"style="font-size: 12px;"><b> REFERENCIA:   ' . $row['oa_' . APP_EJERCICIO_ANTERIOR ] . '</b></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td colspan="2" align="left"style="font-size: 12px;"><b> APORTACION:   ' . $row['ter_nombre'] . '</b></td>';
        echo '<td colspan="2" align="left"style="font-size: 12px;"><b> AVANCE FISICO:   ' . $row['obr_avanceFisico'] . '</b></td>';
        echo '<td colspan="2" align="left"style="font-size: 12px;"><b> AVANCE FINANCIERO:   ' . $row['obr_avanceFinanciero'] . '</b></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td colspan="11" align="left"style="font-size: 12px;"><b> CLAVE DE FINANCIAMIENTO:   ' . $row['cfi_clave'] . '-' . utf8Decode($row['cfi_nombre']) . '   EJERCICIO 20' . $row['cfi_anio'] . '</b></td>';
        echo '</tr>';

    }

    //row origen recurso
    /* ESTA CLAVE DE FINANCIMIENTO ES PARA LOS DISTINTOS DE TIPO DE CLC QUE NOS OSON DE OBRA PUBLICA */

    if ($impresion == false) {

        echo '<tr>';
        echo '<td colspan="10" align="left"style="font-size: 12px;"><b> ORIGEN DEL RECURSO: ' . $row['fte_id'] . $row['sft_id'] . $row['ori_id'] . '-' . utf8Decode($row['ori_nombre']) . '';
        echo '<td colspan="1" style="font-size: 12px;" align="right"><b>Folio:  ' . $row['clc_id'] . '</b></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td colspan="10" align="left"style="font-size: 12px;"><b> CLAVE DE FINANCIAMIENTO:   ' . $row['cfi_clave'] . '-' . utf8Decode($row['cfi_nombre']) . '   EJERCICIO 20' . $row['cfi_anio'] . '</b></td>';
        echo '<td colspan="1" align="right" style="font-size: 12px;"><b>Fecha:  ' . $row['clc_fechaElaboracion'] . '  </b></td>';
        echo '</tr>';

        if ($row['oa_id'] != '') {
            echo '<tr>';
            echo '<td colspan="11" align="left"style="font-size: 12px;"><b> OBRA: ' . $row['oa_id'] . '-' . utf8Decode($row['oa_nombre']) . '</b></td>';
            echo '</tr>';
        }

        if ($row['oa_numobra'] != '' && $row['ore_oficioAut'] != '') {
            echo '<tr>';
            echo '<td colspan="2" align="left"style="font-size: 12px;"><b> ' . utf8Decode('OFICIO DE AUTORIZACIÓN') . ':   ' . $row['ore_oficioAut'] . '</b></td>';
            echo '<td colspan="2" align="left"style="font-size: 12px;"><b> No.OBRA SSPP:   ' . $row['oa_numobra'] . '</b></td>';
            echo '</tr>';
        }
        echo '<tr>';
        echo '<td colspan="9" align="left"style="font-size: 12px;"><b> SUBFUENTE:   ' . $row['sft_clave'] . '-' . utf8Decode($row['sft_nombre']) . '</b></td>';
        echo '<td colspan="2" align="right" style="font-size: 12px;"><b>Modalidad de Pago: ' . utf8Decode($row['mpc_nombre']) . '</b>  </td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td colspan="9" align="left"style="font-size: 12px;"><b> APORTACION:   ' . $row['ter_nombre'] . '</b></td>';
        echo '<td colspan="2" align="right" style="font-size: 12px;"><b>Banco: ' . utf8Decode($row['iba_nombre']) . '</b>  </td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td colspan="9" align="left"style="font-size: 12px;"><b> DOCUMENTACION ANEXA:   ' . $row['obr_documentacion'] . '</b></td>';
        echo '<td colspan="2" align="right" style="font-size: 12px;"><b>Clabe Interbancaria: ' . $row['bur_id'] . '</b>  </td>';
        echo '</tr>';
    }

    if ($impresion == true) {

        switch ($row['tcl_id']) {
            case 'F':
                echo '<tr>';
                echo '<td colspan="11" align="left"style="font-size: 12px;"><b> OBRA POR CONTRATO No.:   ' . $row['obr_contrato'] . '</b></td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td colspan="2" align="left"style="font-size: 12px;"><b>FIANZAS</b></td>';
                echo '<td colspan="2" align="left"style="font-size: 12px;"><b> CUMPLIMIENTO:   ' . $row['obr_cumplimiento'] . '</b></td>';
                echo '<td colspan="2" align="left"style="font-size: 12px;"><b> VICIOS OCULTOS:   ' . $row['obr_vicios'] . '</b></td>';
                echo '</tr>';
                break;
            case 'G':
                break;
            case 'H':
                echo '<tr>';
                echo '<td colspan="11" align="left"style="font-size: 12px;"><b> OBRA POR CONTRATO No.:   ' . $row['obr_contrato'] . '</b></td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td colspan="2" align="left"style="font-size: 12px;"><b>FIANZAS</b></td>';
                echo '<td colspan="2" align="left"style="font-size: 12px;"><b> ANTICIPO:   ' . $row['obr_anticipo'] . '</b></td>';
                echo '<td colspan="2" align="left"style="font-size: 12px;"><b> CUMPLIMIENTO:  ' . $row['obr_cumplimiento'] . '</b></td>';
                echo '</tr>';
                break;

        }

        echo '<tr>';
        echo '<td colspan="11" align="left"style="font-size: 12px;"><b> DOCUMENTACION ANEXA:   ' . utf8Decode($row['obr_documentacion']) . '</b></td>';
        echo '</tr>';

    }
    echo '<tr>';
    echo '<td colspan="11" align="left"style="font-size: 12px;"><b> CONCEPTO:   ' . utf8Decode($row['clc_concepto']) . '</b></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="11" align="left"style="font-size: 12px;"><b> BENEFICIARIO:   ' . utf8Decode($row['clc_beneficiario']) . '</b></td>';
    echo '</tr>';

    switch ($row['tcl_id']) {
        //Clc de servicios personales***
        case 'A':
            echo '<tr>';
            echo '<td colspan="11" align="left"style="font-size: 12px;"><b> TIPO NOMINA:   ' . $row['tno_nombre'] . '</b></td>';
            echo '</tr>';
            break;

            //Clc de gastos de operacion***
        case 'B':
            break;
    }

    echo '<tr>';
    echo '<td colspan="11" align="left"style="font-size: 12px;"></td>';
    echo '</tr>';

    switch ($row['tcl_id']) {
        //SERVICIOS PERSONALES
        case 'A':
            echo '<tr>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>No. AFEC<br></br></b></b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>No. CTRL</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>CVE. PRESUP./CTA. CONTAB.</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>DESCRIPCION</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>RERENCIA</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>FECHA INICIAL</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>FECHA FINAL</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>PROVEEDOR</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>R.F.C.</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>IMPORTE</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>+/-</b></td>';
            echo '</tr>';
            break;
            //GASTOS DE OPERACION
        case 'B':
            echo '<tr>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>No. AFEC<br></br></b></b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>No. CTRL</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>CVE. PRESUP./CTA. CONTAB.</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>DESCRIPCION</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>RERENCIA</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>FECHA REFERENCIA</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>PROVEEDOR</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>R.F.C.</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>IMPORTE</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>IMPORTE IVA</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>IMPORTE NETO</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>+/-</b></td>';
            echo '</tr>';
            break;
        case 'F':case 'G':case 'H':
            echo '<tr>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>No. AFEC<br></br></b></b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>No. CTRL</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>CVE. PRESUP./CTA. CONTAB.</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>DESCRIPCION</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>RERENCIA</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>FECHA REFERENCIA</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>PROVEEDOR</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>R.F.C.</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>IMPORTE</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>IMPORTE IVA</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>TOTAL</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>+/-</b></td>';
            echo '</tr>';
            break;

        case 'D':case 'E':case 'J':case 'K':case 'L':case 'M':case 'I':
            echo '<tr>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>No. AFEC<br></br></b></b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>No. CTRL</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>CVE. PRESUP./CTA. CONTAB.</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>DESCRIPCION</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>RERENCIA</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>FECHA REFERENCIA</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>PROVEEDOR</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>R.F.C.</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>IMPORTE NETO</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>+/-</b></td>';
            echo '</tr>';
            break;

        case 'C':
            switch ($row['stc_id']) {
                //tiene periodo y no tiene iva
                case '01':
                    echo '<tr>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>No. AFEC<br></br></b></b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>No. CTRL</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>CVE. PRESUP./CTA. CONTAB.</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>DESCRIPCION</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>REFERENCIA</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>FECHA INICIAL</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>FECHA FINAL</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>PROVEEDOR</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>R.F.C.</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>IMPORTE NETO</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>+/-</b></td>';
                    echo '</tr>';
                    break;
                    //no tiene periodo y tiene desglose de IVA
                case '02':case '03':case '04':
                    echo '<tr>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>No. AFEC<br></br></b></b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>No. CTRL</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>CVE. PRESUP./CTA. CONTAB.</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>DESCRIPCION</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>REFERENCIA</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>FECHA REFERENCIA</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>PROVEEDOR</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>R.F.C.</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>IMPORTE </b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>IMPORTE IVA</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>IMPORTE NETO</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #003366; color: white;"><b>+/-</b></td>';
                    echo '</tr>';
                    break;
            }
            break;

    }

    do {
        //cuerpo de la tabla

        if ($clc_id != $row['clc_id'] || $ur_clave != $row['ur_clave']) {

            if (mb_strlen($clc_id)) {
                if ($tipomovanterior == '+') {
                    echo '<tr>';
                    echo '<td colspan="10" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b>TOTAL PERCEPCIONES <b></td>';
                    echo '<td colspan="2" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b> ' . number_format($percepciones, 2) . ' <b></td>';
                    echo '</tr>';
                }
                echo '<tr>';
                echo '<td colspan="10" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b>TOTAL RETENCIONES: <b></td>';
                echo '<td colspan="2" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b> ' . number_format($retenciones, 2) . ' <b></td>';
                echo '</tr>';

                if ($percepciones > 0) {
                    $sumatotal = $percepciones - $retenciones;
                } else {
                    $sumatotal = $retenciones;
                }

                echo '<tr>';
                echo '<td colspan="10" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b>TOTAL C.L.C. <b></td>';
                echo '<td colspan="2" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b> ' . number_format($sumatotal, 2) . ' <b></td>';
                echo '</tr>';

                $percepciones = 0;
                $retenciones = 0;
                $tipomovanterior = '';
            }

            $clc_id = $row['clc_id'];
            $ur_clave = $row['ur_clave'];
        }

        //PARA CLCS DE ANTICIPO DE OBRA,PAGOS A TERCEROS Y PAGOS A TERCEROS OTROS
        if (mb_strlen($tipomovanterior) && $tipomovanterior != $row['tipo_movimiento']) {
            //TOTAL DE PERCEPCIONES
            echo '<tr>';

            echo '<td colspan="10" align="right" style="font-size: 10px;  background-color: #336699; color: white;"><b>TOTAL PERCEPCIONES <b></td>';
            echo '<td colspan="1" align="right" style="font-size: 10px;  background-color: #336699; color: white;"><b> ' . number_format($percepciones, 2) . ' <b></td>';

            echo '</tr>';
        }

        $widths = '';
        $aligns = '';
        $contenido = '';
        $bruto = 0;
        $iva = 0;
        $neto = 0;
        //si es un numero de control presupuestal
        if ($row['tipo_numcontrol'] == 'P') {
            $bruto = $row['percepcion_bruto'];
            $iva = $row['percepcion_iva'];
            $neto = $row['percepcion_neto'];

        }

        // si es un numero de control contable
        elseif ($row['tipo_numcontrol'] == 'C') {
            if ($row['tipo_movimiento'] == '+') {
                $bruto = $row['percepcion_neto'];
                $neto = $row['percepcion_neto'];
            } else {
                $bruto = $row['retencion_neto'];
                $neto = $row['retencion_neto'];
            }
            $iva = $row['percepcion_iva'];
        }

        $cols = 0;
        switch ($row['tcl_id']) {
            //SERVICIOS PERSONALES
            case 'A':
                if ($filas % 2 == 0) {
                    echo '<tr>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['afe_id'] . '<br></br></b></b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['numcontrol'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['clave'] . '</b></td>';
                    echo '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['clave_nombre']) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['referencia'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['afe_fechaInicio'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['afe_fechaFin'] . '</b></td>';
                    echo '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['rfc_nombre'] . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno']) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['rfc_id'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($neto, 2) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['tipo_movimiento'] . '</b></td>';
                    echo '</tr>';
                } else {
                    echo '<tr>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['afe_id'] . '<br></br></b></b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['numcontrol'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['clave'] . '</b></td>';
                    echo '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['clave_nombre']) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['referencia'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['afe_fechaInicio'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['afe_fechaFin'] . '</b></td>';
                    echo '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['rfc_nombre'] . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno']) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['rfc_id'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($neto, 2) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['tipo_movimiento'] . '</b></td>';
                    echo '</tr>';

                }
                $cols = 11;
                break;
                //GASTOS DE OPERACION
            case 'B':case 'F':case 'G':case 'H':
                if ($filas % 2 == 0) {
                    echo '<tr>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['afe_id'] . '</br></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['numcontrol'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['clave'] . '</b></td>';
                    echo '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['clave_nombre']) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['referencia'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['fecha_referencia'] . '</b></td>';
                    echo '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['rfc_nombre'] . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno']) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['rfc_id'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($bruto, 2) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($iva, 2) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($neto, 2) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['tipo_movimiento'] . '</b></td>';
                    echo '</tr>';
                } else {
                    echo '<tr>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['afe_id'] . '</br></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['numcontrol'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['clave'] . '</b></td>';
                    echo '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['clave_nombre']) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['referencia'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['fecha_referencia'] . '</b></td>';
                    echo '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['rfc_nombre'] . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno']) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['rfc_id'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($bruto, 2) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($iva, 2) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($neto, 2) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['tipo_movimiento'] . '</b></td>';
                    echo '</tr>';

                }
                $cols = 12;
                break;
            case 'D':case 'E':case 'J':case 'K':case 'L':case 'M':case 'I':
                if ($filas % 2 == 0) {
                    echo '<tr>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['afe_id'] . '<br></br></b></b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['numcontrol'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['clave'] . '</b></td>';
                    echo '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['clave_nombre']) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['referencia'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['fecha_referencia'] . '</b></td>';
                    echo '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['rfc_nombre'] . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno']) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['rfc_id'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($neto, 2) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['tipo_movimiento'] . '</b></td>';
                    echo '</tr>';
                } else {
                    echo '<tr>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['afe_id'] . '<br></br></b></b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['numcontrol'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['clave'] . '</b></td>';
                    echo '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['clave_nombre']) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['referencia'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['fecha_referencia'] . '</b></td>';
                    echo '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['rfc_nombre'] . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno']) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['rfc_id'] . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($neto, 2) . '</b></td>';
                    echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['tipo_movimiento'] . '</b></td>';
                    echo '</tr>';
                }
                $cols = 10;
                break;

            case 'C':
                switch ($row['stc_id']) {

                    //tiene periodo y no tiene iva
                    case '01':
                        if ($filas % 2 == 0) {
                            echo '<tr>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['afe_id'] . '</br></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['numcontrol'] . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['clave'] . '</b></td>';
                            echo '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['clave_nombre']) . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['referencia'] . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['afe_fechaInicio'] . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['afe_fechaFin'] . '</b></td>';
                            echo '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['rfc_nombre'] . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno']) . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['rfc_id'] . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($neto, 2) . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['tipo_movimiento'] . '</b></td>';
                            echo '</tr>';
                        } else {
                            echo '<tr>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['afe_id'] . '</br></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['numcontrol'] . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['clave'] . '</b></td>';
                            echo '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['clave_nombre']) . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['referencia'] . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['afe_fechaInicio'] . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['afe_fechaFin'] . '</b></td>';
                            echo '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['rfc_nombre'] . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno']) . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['rfc_id'] . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($neto, 2) . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['tipo_movimiento'] . '</b></td>';
                            echo '</tr>';

                        }
                        $cols = 11;
                        break;
                        //no tiene periodo y tiene desglose de IVA
                    case '02':case '03':case '04':case '05':
                        if ($filas % 2 == 0) {
                            echo '<tr>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['afe_id'] . '</br></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['numcontrol'] . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['clave'] . '</b></td>';
                            echo '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['clave_nombre']) . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['referencia'] . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['fecha_referencia'] . '</b></td>';
                            echo '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['rfc_nombre'] . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno']) . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['rfc_id'] . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($bruto, 2) . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($iva, 2) . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($neto, 2) . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['tipo_movimiento'] . '</b></td>';
                            echo '</tr>';
                        } else {
                            echo '<tr>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['afe_id'] . '</br></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['numcontrol'] . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['clave'] . '</b></td>';
                            echo '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['clave_nombre']) . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['referencia'] . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['fecha_referencia'] . '</b></td>';
                            echo '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['rfc_nombre'] . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno']) . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['rfc_id'] . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($bruto, 2) . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($iva, 2) . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($neto, 2) . '</b></td>';
                            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['tipo_movimiento'] . '</b></td>';
                            echo '</tr>';

                        }
                        $cols = 12;
                        break;
                }
                break;

        }
        $filas++;
        $tipomovanterior = $row['tipo_movimiento'];
        if ($row['tipo_movimiento'] == '+') {
            $percepciones = $percepciones + $row['percepcion_neto'];
        } else {
            $retenciones = $retenciones + $row['retencion_neto'];
        }

    } while ($row = $conexion->extraer());
}

if ($percepciones > 0 and $retenciones == 0) {

    switch ($cols) {

        case 10:
            echo '<tr>';
            echo '<td colspan="9" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b>TOTAL PERCEPCIONES <b></td>';
            echo '<td colspan="1" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b> ' . number_format($percepciones, 2) . ' <b></td>';
            echo '</tr>';
            break;
        case 11:
            echo '<tr>';
            echo '<td colspan="10" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b>TOTAL PERCEPCIONES <b></td>';
            echo '<td colspan="1" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b> ' . number_format($percepciones, 2) . ' <b></td>';
            echo '</tr>';
            break;
        case 12:
            echo '<tr>';
            echo '<td colspan="10" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b>TOTAL PERCEPCIONES <b></td>';
            echo '<td colspan="2" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b> ' . number_format($percepciones, 2) . ' <b></td>';
            echo '</tr>';
            break;
    }

}

//TOTAL DE RETENCIONES
switch ($cols) {
    case 10:
        echo '<tr>';
        echo '<td colspan="9" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b>TOTAL RETENCIONES<b></td>';
        echo '<td colspan="1" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b> ' . number_format($retenciones, 2) . ' <b></td>';
        echo '</tr>';
        break;
    case 11:
        echo '<tr>';
        echo '<td colspan="10" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b>TOTAL RETENCIONES<b></td>';
        echo '<td colspan="1" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b> ' . number_format($retenciones, 2) . ' <b></td>';
        echo '</tr>';
        break;
    case 12:
        echo '<tr>';
        echo '<td colspan="10" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b>TOTAL RETENCIONES<b></td>';
        echo '<td colspan="2" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b> ' . number_format($retenciones, 2) . ' <b></td>';
        echo '</tr>';
        break;
}

//FIN TOTAL DE RETENCIONES

if ($percepciones > 0) {
    $sumatotal = $percepciones - $retenciones;
} else {
    $sumatotal = $retenciones;
}

switch ($cols) {

    case 10:
        echo '<tr>';
        echo '<td colspan="9" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b>TOTAL C.L.C.<b></td>';
        echo '<td colspan="1" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b> ' . number_format($sumatotal, 2) . ' <b></td>';
        echo '</tr>';
        break;
    case 11:
        echo '<tr>';
        echo '<td colspan="10" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b>TOTAL C.L.C.<b></td>';
        echo '<td colspan="1" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b> ' . number_format($sumatotal, 2) . ' <b></td>';
        echo '</tr>';
        break;
    case 12:
        echo '<tr>';
        echo '<td colspan="10" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b>TOTAL C.L.C.<b></td>';
        echo '<td colspan="2" align="right" style="font-size: 10px;  background-color: #000000; color: white;"><b> ' . number_format($sumatotal, 2) . ' <b></td>';
        echo '</tr>';
        break;
}

echo '<tr>';
echo '<td colspan="11" align="right" style="font-size: 10px;"><b>Fecha ' . $hoy . '</b></td>';
echo '</tr>';

echo ($row = $conexion->extraer()) ? '</table>' : '';
