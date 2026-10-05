<?php

session_start();
ini_set('memory_limit', '150M');
set_time_limit(0);
require_once '../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../lib/helpers.php';

$extension = '.xls';
$ruta = utf8Decode('REPORTE DE AVANCE DE GESTION');
header('Content-type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename=' . $ruta);
header('Pragma: no-cache');
header('Expires: 0');

if ($_SESSION[_S]['nvj_id'] > 2) {
    $ur = 'gpo_id = ' . $_POST['gpo_id'] . ' AND ur_id = ' . $_POST['ur_id'];
    $trim = $_POST['capturandoTri2'];
} else {
    $ur = 'gpo_id = ' . $_SESSION[_S]['gpo_id'] . ' AND ur_id = ' . $_SESSION[_S]['ur_id'];
    $trim = $_POST['capturandoTri'];
}
$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql('
    SELECT gpo_id,ur_id,ue_id,prg_id,spr_id,oa_id,epg_id,
        ur_clave, ur_nombre,pdf_id,pdi_id,
        ur_nombreAdministrativo, ur_nombreTitular, ur_cargoAdministrativo,
        ur_cargoTitular, ur_objetivoGeneral, prg_nombre,
        fin, vpm_resumenNarrativo AS proposito,CONCAT(pf.pdf_id,"-",pf.pdf_nombre) AS componente,
        CONCAT(ue_id,"-",ue_nombre) AS ues,
        CONCAT(pi.pdi_id,"-",pi.pdi_nombre) AS actividad,
        CONCAT(prog.prg_id,"-",prog.prg_nombre) AS programa,
        CONCAT(prog.spr_id,"-",prog.spr_nombre) AS subprograma,
        CONCAT(prog.py_id,"-",prog.py_nombre) AS proyecto,
        CONCAT(prog.oa_id,"-",prog.oa_nombre) AS oa,
        CONCAT(eje_id,"-",eje_nombre) AS eje_nombre,
        CONCAT(tem_id,"-",tem_nombre) AS tem_nombre,
        CONCAT(obj_id,"-",obj_nombre) AS obj_nombre,
        CONCAT(est_id,"-",est_nombre) AS est_nombre,
        CONCAT(vin.lac_id,"-",lac_nombre) AS lac_nombre,
        aprobado, ampliaciones,reducciones,modificado,devengado,
        apr_trimestre1,apr_trimestre2,apr_trimestre3,apr_trimestre4,
        vig_trimestre1,vig_trimestre2,vig_trimestre3,vig_trimestre4,
        comprometido1,comprometido2,comprometido3,comprometido4,
        ejercido1,ejercido2,ejercido3,ejercido4,
        devTrimestre1, devTrimestre2, devTrimestre3, devTrimestre4
    FROM(
        SELECT epg_id, gpo_id, ur_id,ue_id, prg_id, spr_id, py_id, oa_id, prg_nombre,
            pdf_id, spr_nombre, py_nombre, oa_nombre
        FROM cat_Programa prg
        INNER JOIN cat_Subprograma spr USING (gpo_id,ur_id,prg_id)
        INNER JOIN cat_Proyecto py USING (gpo_id,ur_id,prg_id,spr_id)
        INNER JOIN cat_ObraActividad oa USING (gpo_id,ur_id,prg_id,spr_id,py_id)
        INNER JOIN enc_Programatico eprg USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
        WHERE ' . $ur . '
    )prog
    INNER JOIN (
        SELECT pii_id,epg_id,aprobado, ampliaciones,reducciones,modificado,devengado,
        apr_trimestre1,apr_trimestre2,apr_trimestre3,apr_trimestre4,
        vig_trimestre1,vig_trimestre2,vig_trimestre3,vig_trimestre4,
        comprometido1,comprometido2,comprometido3,comprometido4,
        ejercido1,ejercido2,ejercido3,ejercido4,
        devTrimestre1, devTrimestre2, devTrimestre3, devTrimestre4
        FROM (
            SELECT epg_id, gpo_id, ur_id, prg_id, spr_id, py_id, oa_id
            FROM enc_Programatico
            WHERE ' . $ur . '
        ) eprg
        LEFT JOIN cat_MetaActividad mac USING (epg_id)
        LEFT JOIN (
            SELECT pii_id, epg_id, SUM(IF(tri_id=1,mact_modificado,NULL)) mod_trimestre1,
                SUM(IF(tri_id=2,mact_modificado,NULL)) mod_trimestre2, SUM(IF(tri_id=3,mact_modificado,NULL)) mod_trimestre3,
                SUM(IF(tri_id=4,mact_modificado,NULL)) mod_trimestre4
            FROM cat_MetaActividadTrimestre
            GROUP BY pii_id,epg_id
        )mat USING (pii_id,epg_id)
        LEFT JOIN (
            SELECT gpo_id,ur_id,spr_id, prg_id, py_id, oa_id,epg_id,
                SUM(pre_aprobado) AS aprobado, SUM(pre_vigente) AS modificado, SUM(pre_devengado) devengado,
                SUM(pre_ampliacion) ampliaciones, SUM(pre_reduccion) reducciones,
                SUM(apr_ene+apr_feb+apr_mar) apr_trimestre1, SUM(apr_abr+apr_may+apr_jun) apr_trimestre2,
                SUM(apr_jul+apr_ago+apr_sep) apr_trimestre3, SUM(apr_oct+apr_nov+apr_dic)apr_trimestre4,
                SUM(vig_ene+vig_feb+vig_mar) vig_trimestre1, SUM(vig_abr+vig_may+vig_jun) vig_trimestre2,
                SUM(vig_jul+vig_ago+vig_sep) vig_trimestre3, SUM(vig_oct+vig_nov+vig_dic)vig_trimestre4,
                SUM(com_ene+com_feb+com_mar) comprometido1,SUM(dev_ene+dev_feb+dev_mar) devTrimestre1,
                SUM(eje_ene+eje_feb+eje_mar) ejercido1,SUM(com_abr+com_may+com_jun) comprometido2,
                SUM(dev_abr+dev_may+dev_jun) devTrimestre2,SUM(eje_abr+eje_may+eje_jun) ejercido2,
                SUM(com_jul+com_ago+com_sep) comprometido3,SUM(dev_jul+dev_ago+dev_sep) devTrimestre3,
                SUM(eje_jul+eje_ago+eje_sep) ejercido3,SUM(com_oct+com_nov+com_dic) comprometido4,
                SUM(dev_oct+dev_nov+dev_dic) devTrimestre4,SUM(eje_oct+eje_nov+eje_dic) ejercido4
            FROM presupuesto
                INNER JOIN presupuestoVigente USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoAprobado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoComprometido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoDevengado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                INNER JOIN presupuestoEjercido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
            WHERE ' . $ur . '
            GROUP BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, epg_id
        ) AS temp USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,epg_id)
        WHERE ' . $ur . '
        AND(vig_trimestre' . $trim . '>0 || mod_trimestre' . $trim . ' >0)
        GROUP BY gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,epg_id,pii_id
    ) meta USING(epg_id)
    INNER JOIN (
        SELECT epg_id,eje_id,eje_nombre,tem_id,tem_nombre,obj_id,obj_nombre,
            est_id,est_nombre,lac_id,lac_nombre,fin,vpm_resumenNarrativo
        FROM (
            SELECT epg_id,MAX(epe_id) epe_id, MAX(eps_id) eps_id,prg_id
            FROM enc_Programatico ep
            INNER JOIN vin_Programatico USING(epg_id)
            WHERE ' . $ur . '
            GROUP BY epg_id
        ) vp
        INNER JOIN enc_Ped USING(epe_id)
        INNER JOIN cat_Eje USING(eje_id)
        INNER JOIN cat_Tema t USING(eje_id, tem_id)
        INNER JOIN cat_Objetivo o USING(eje_id, tem_id, obj_id)
        INNER JOIN cat_Estrategia USING(eje_id,obj_id,est_id,tem_id)
        INNER JOIN cat_LineaAccion lac USING (eje_id,obj_id,est_id,tem_id,lac_id)
        LEFT JOIN (
            SELECT prgs_id AS prg_id, obj_nombre AS fin, obj_descripcion
            FROM vin_Programa
            INNER JOIN enc_PedObjetivo USING (epo_id)
            INNER JOIN cat_Objetivo USING(eje_id,tem_id,obj_id)
        ) F USING (prg_id)
        INNER JOIN (
            SELECT vpm_id, prgs_id AS prg_id, mir_id, vpm_resumenNarrativo
            FROM vin_ProgramasMir
        ) P USING (prg_id)
    ) vin USING (epg_id)
    INNER JOIN vin_ProductoIntermedioIndicador pfpi USING(pii_id,pdf_id)
    INNER JOIN cat_ProductoFinal pf USING(pdf_id)
    INNER JOIN cat_ProductoIntermedio pi USING(pdi_id)
    INNER JOIN cat_UnidadResponsable ur USING(gpo_id,ur_id)
    INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
    INNER JOIN cat_UnidadEjecutora USING(gpo_id,ur_id,ue_id)
    WHERE ' . $ur . '
    GROUP BY prg_id,spr_id,epg_id,pdi_id,vpm_resumenNarrativo
    ORDER BY gpo_id,ur_id,prg_id,spr_id,oa_id,ue_id;
');

$conexion->consultar();
echo '<table align="center" width="100%">';
$encabezado = true;
$prg_id_anterior = $act_anterior = '';
while ($row = $conexion->extraer()) {
    $prg_id = $row['prg_id'];
    $mactividad = $row['actividad'];
    if ($encabezado) {
        $encabezado = false;
        echo '<tr>';
        echo '<td colspan="10" align="LEFT"><b>REPORTE DE AVANCE DE GESTION</b></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td colspan="10" align="LEFT"><b>UR ' . $row['ur_clave'] . ' - ' . utf8Decode($row['ur_nombre']) . '</b></td>';
        echo '</tr>';
        $tri = [' - ', 'PRIMER', 'SEGUNDO', 'TERCER', 'CUARTO', 'CUARTO'];
        echo '<tr>';
        echo '<td colspan="10" align="LEFT" ><b> ' . $tri[$trim] . ' TRIMESTRE ' . APP_EJERCICIO . '</b></td>';
        echo '</tr>';
        echo '<tr >
                <td style="background-color: rgb(217, 217, 217);" align="LEFT"><b>OBJETIVO INSTITUCIONAL:</b></td>
                <td colspan="8">' . utf8Decode($row['ur_objetivoGeneral']) . '</td>
            </tr>
            <tr >
            </tr >';
    }

    if ($row['prg_id'] < 100) {
        $fin = $pro = ' - ';
        $com = $row['componente'];
        $act = $row['actividad'];
        $titulo = 'DEL INDICADOR';
    } else {
        $fin = $row['fin'];
        $pro = $row['proposito'];
        $com = $row['componente'];
        $act = $row['actividad'];

        $titulo = 'DE LA ACTIVIDAD';
    }
    if ($prg_id != $prg_id_anterior) {
        echo '<tr style="background-color: rgb(217, 217, 217);">
            <td align="LEFT"><b>' . utf8Decode('ALINEACIÓN PED:') . '</b></td>
            <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            <td align="CENTER" colspan="10" ><b>' . utf8Decode('MATRÍZ DE INDICADORES PARA RESULTADOS:') . '</b></td>
            </tr>';
        echo '<tr>
                <td align="LEFT"><b>EJE:</b></td>
                <td colspan="8">' . utf8Decode($row['eje_nombre']) . '</td>
                <td align="LEFT"><b>FIN:</b></td>
                <td colspan="8">' . utf8Decode($fin) . '</td>
            </tr>';
        echo '<tr style="background-color: rgb(217, 217, 217);">
                <td align="LEFT"><b>TEMA:</b></td>
                <td colspan="8">' . utf8Decode($row['tem_nombre']) . '</td>
                <td></td>
                <td></td>
            </tr>';
        echo '<tr style="background-color: rgb(217, 217, 217);">
                <td align="LEFT"><b>OBJETIVO:</b></td>
                <td colspan="8">' . utf8Decode($row['obj_nombre']) . '</td>
                <td align="LEFT"><b>' . utf8Decode('PROPÓSITO: ') . '</b></td>
                <td colspan="8">' . strtoupper(utf8Decode($pro)) . '</td>
            </tr>';
        echo '<tr style="background-color: rgb(217, 217, 217);">
                <td align="LEFT"><b>ESTRATEGIA: </b></td>
                <td colspan="8">' . utf8Decode($row['est_nombre']) . '</td>
                <td align="LEFT"><b>COMPONENTE:</b></td>
                <td colspan="8">' . utf8Decode($row['componente']) . '</td>
            </tr>';
        echo '<tr style="background-color: rgb(217, 217, 217);">
                <td align="LEFT"><b>' . utf8Decode('LÍNEAS DE ACCIÓN: ') . '</b></td>
                <td colspan="8">' . utf8Decode($row['lac_nombre']) . '</td>
                <td align="LEFT"><b>ACTIVIDAD:</b></td>
                <td colspan="8">' . utf8Decode($row['actividad']) . '</td>
            </tr>';

        echo '<tr style="background-color: rgb(217, 217, 217);">
                <td align="LEFT"><b>UE:</b></td>
                <td colspan="8">' . utf8Decode($row['ues']) . '</td>
                <td></td>
                <td></td>
            </tr>';
    } else {
        if ($mactividad != $act_anterior) {
            echo '<tr style="background-color: rgb(217, 217, 217);">
                    <td align="LEFT"><b>' . utf8Decode('ALINEACIÓN PED:') . '</b></td>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    <td align="CENTER" colspan="10" ><b>' . utf8Decode('MATRÍZ DE INDICADORES PARA RESULTADOS:') . '</b></td>
                </tr>';
            echo '<tr style="background-color: rgb(217, 217, 217);">
                    <td align="LEFT"><b>ESTRATEGIA: </b></td>
                    <td colspan="8">' . utf8Decode($row['est_nombre']) . '</td>
                    <td align="LEFT"><b>COMPONENTE:</b></td>
                    <td colspan="8">' . utf8Decode($row['componente']) . '</td>
                </tr>';
            echo '<tr style="background-color: rgb(217, 217, 217);">
                    <td align="LEFT"><b>' . utf8Decode('LÍNEAS DE ACCIÓN: ') . '</b></td>
                    <td colspan="8">' . utf8Decode($row['lac_nombre']) . '</td>
                    <td align="LEFT"><b>ACTIVIDAD:</b></td>
                    <td colspan="8">' . utf8Decode($row['actividad']) . '</td>
                </tr>';

            echo '<tr style="background-color: rgb(217, 217, 217);">
                    <td align="LEFT"><b>UE:</b></td>
                    <td colspan="8">' . utf8Decode($row['ues']) . '</td>
                    <td></td>
                    <td></td>
                </tr>';
        } else {
            echo '<tr style="background-color: rgb(217, 217, 217);">
                    <td align="LEFT"><b>UE:</b></td>
                    <td colspan="8">' . utf8Decode($row['ues']) . '</td>
                    <td></td>
                    <td></td>
                </tr>';
        }
    }
    $prg_id_anterior = $prg_id;
    echo '<tr>
            <td colspan="11"><b>ESTRUCTURA PROGRAMATICA PRESUPUESTAL</b></td>
        </tr>';
    echo '<tr>
            <td><b>PROGRAMA</b></td>
            <td><b>SUBPROGRAMA</b></td>
            <td><b>PROYECTO</b></td>
            <td><b>OBRA/ACTIVIDAD</b></td>
        </tr>';
    echo '<tr>
            <td>' . utf8Decode($row['programa']) . '</td>
            <td>' . utf8Decode($row['subprograma']) . '</td>
            <td>' . utf8Decode($row['proyecto']) . '</td>
            <td>' . utf8Decode($row['oa']) . '</td>
        </tr>';

    if ($_POST['capturandoTri'] == 0) {
        $_POST['capturandoTri'] = $_POST['capturandoTri2'];
    }

    //para pintar el trimestre del devengado correspondiente
    $DevengadoT1 = $DevengadoT2 = $DevengadoT3 = $DevengadoT4 = $totalEjercido = 0;
    switch ($_POST['capturandoTri']) {
        case 1:
            $DevengadoT1 = $row['devTrimestre1'];
            $totalEjercido = $row['ejercido1'];
            break;
        case 2:
            $DevengadoT1 = $row['devTrimestre1'];
            $DevengadoT2 = $row['devTrimestre2'];
            $totalEjercido = $row['ejercido1'] + $row['ejercido2'];
            break;
        case 3:
            $DevengadoT1 = $row['devTrimestre1'];
            $DevengadoT2 = $row['devTrimestre2'];
            $DevengadoT3 = $row['devTrimestre3'];
            $totalEjercido = $row['ejercido1'] + $row['ejercido2'] + $row['ejercido3'];
            break;
        case 4:
            $DevengadoT1 = $row['devTrimestre1'];
            $DevengadoT2 = $row['devTrimestre2'];
            $DevengadoT3 = $row['devTrimestre3'];
            $DevengadoT4 = $row['devTrimestre4'];
            $totalEjercido = $row['ejercido1'] + $row['ejercido2'] + $row['ejercido3'] + $row['ejercido4'];
            break;
    }

    echo '
        <tr></tr>
        <tr>
            <td style="background-color: rgb(217, 217, 217);" colspan="11" align="center"><b>PRESUPUESTO ANUAL</b></td>
        </tr>
        <tr>
            <td align="center"><b>Aprobado</b></td>
            <td align="center"><b>' . utf8Decode('Ampliación') . '</b></td>
            <td align="center"><b>' . utf8Decode('Reducción') . '</b></td>
            <td align="center"><b>Modificado</b></td>
            <td align="center"><b>EJERCIDO T1</b></td>
            <td align="center"><b>EJERCIDO T2</b></td>
            <td align="center"><b>EJERCIDO T3</b></td>
            <td align="center"><b>EJERCIDO T4</b></td>
        </tr>
        <tr>
            <td align="center"><b>' . number_format($row['aprobado'], 2) . '</b></td>
            <td align="center"><b>' . number_format($row['ampliaciones'], 2) . '</b></td>
            <td align="center"><b>' . number_format($row['reducciones'], 2) . '</b></td>
            <td align="center"><b>' . number_format($row['modificado'], 2) . '</b></td>
            <td align="center"><b>' . number_format($DevengadoT1, 2) . '</b></td>
            <td align="center"><b>' . number_format($DevengadoT2, 2) . '</b></td>
            <td align="center"><b>' . number_format($DevengadoT3, 2) . '</b></td>
            <td align="center"><b>' . number_format($DevengadoT4, 2) . '</b></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td align="right"><b>TOTAL EJERCIDO AL TRIMESTRE VIGENTE</b></td>
            <td align="center"><b>' . number_format($totalEjercido, 2) . '</b></td>
        </tr>
        <tr>
            <td style="background-color: rgb(217, 217, 217);" colspan="11" align="center"><b>PRESUPUESTO TRIMESTRAL DE LA ACTIVIDAD</b></td>
        </tr>
        <tr>
            <td align="center"><b>Aprobado</b></td>
            <td align="center"><b>Modificado</b></td>
            <td align="center"><b>Comprometido</b></td>
            <td align="center"><b>Devengado</b></td>
            <td align="center" colspan="2"><b>Ejercido</b></td>
            <td align="center"><b>% de Ejercido Vs. Modificado</b></td>
        </tr>
        <tr>
            <td align="center"><b>' . number_format($row['apr_trimestre' . $_POST['capturandoTri']], 2) . '</b></td>
            <td align="center"><b>' . number_format($row['vig_trimestre' . $_POST['capturandoTri']], 2) . '</b></td>
            <td align="center"><b>' . number_format($row['comprometido' . $_POST['capturandoTri']], 2) . '</b></td>
            <td align="center"><b>' . number_format($row['devTrimestre' . $_POST['capturandoTri']], 2) . '</b></td>
            <td align="center" colspan="2"><b>' . number_format($row['ejercido' . $_POST['capturandoTri']], 2) . '</b></td>
            <td align="center"><b>' . ($row['vig_trimestre' . $_POST['capturandoTri']] != 0 ? number_format(($row['ejercido' . $_POST['capturandoTri']] / $row['vig_trimestre' . $_POST['capturandoTri']]) * 100, 2) : 0) . '</b></td>
        </tr>
    ';
    $act_anterior = $mactividad;

    $conexionIndicador = new AccesoDatos();
    $conexionIndicador->setSql('
        SELECT pdf_id, pdi_id, ind_id, ind_nombre, ind_definicion, ind_nombreMIR, ind_definicionMIR, ind_objetivo, ind_formulaCalculo,
            tpi_id, tpi_nombre, umi_id ,umi_nombre,umv_id, umv_nombre,
            dim_id, dim_nombre, frm_id, frm_nombre, ses_id, tiv_id, tiv_nombre,
            ses_nombre, nim_id, MAX(mvac_nombre) mvac_nombre, MAX(spa_nombre) spa_nombre,
            gpo_id, ur_id, prg_id, mac_anio, mac_valor, mac_original, epg_id
            FROM vin_ProductoIntermedioIndicador pii
            JOIN cat_Indicador i USING (nim_id , ind_id)
            JOIN cat_Dimension USING (dim_id)
            JOIN cat_FrecuenciaMedicion USING (frm_id)
            JOIN cat_TipoIndicador USING (tpi_id)
            JOIN cat_UnidadMedidaIndicador u USING (umi_id)
            JOIN cat_UnidadMedidaVariable umv USING(umv_id)
            JOIN cat_TipoValor USING (tiv_id)
            JOIN cat_SentidoEsperado USING (ses_id)
            INNER JOIN cat_MetaActividad USING (pii_id)
            INNER JOIN enc_Programatico ep USING (epg_id)
            LEFT JOIN cat_MedioVerificacionActividad mva USING (pdf_id,pdi_id,gpo_id, ur_id)
            LEFT JOIN cat_SupuestoActividad sa USING (pdf_id,pdi_id,gpo_id, ur_id)
        WHERE
            nim_id = 4
            AND pdf_id = ?
            AND pdi_id = ?
            AND gpo_id = ? AND ur_id = ?
            AND prg_id= ? AND epg_id= ?
        GROUP BY gpo_id, ur_id, prg_id, pdf_id,pdi_id,epg_id,pii_id;
    ');

    $params = [
        $row['pdf_id'],
        $row['pdi_id'],
        $row['gpo_id'],
        $row['ur_id'],
        $row['prg_id'],
        $row['epg_id'],
    ];

    $conexionIndicador->conectar();
    $conexionIndicador->consultar($params);
    $numMetas = 0;
    $primer = true;
    while ($rowind = $conexionIndicador->extraer()) {
        echo '<tr></tr>';
        echo '<tr></tr>';
        echo '<tr>';
        echo '<td></td>
            <td align="CENTER" colspan="8"><b>DATOS DEL INDICADOR</b>
            </td>';
        echo '</tr>';
        echo '<tr>
            <td></td>
            <td align="CENTER" ><b>Nombre:</b></td><td colspan="4">' . utf8Decode($rowind['ind_nombreMIR']) . '</b></td>
            <td align="RIGHT" ><b>' . utf8Decode('Número de indicador :') . '</b></td><td >' . utf8Decode($rowind['ind_id']) . '</b></td>';
        echo '</tr>';
        echo '<tr>
            <td></td>
            <td align="CENTER" ><b>' . utf8Decode('Definición:') . '</b></td><td colspan="7">' . utf8Decode($rowind['ind_definicionMIR']) . '</b></td>';
        echo '</tr>';
        echo '<tr>
            <td></td>
            <td align="CENTER" ><b>' . utf8Decode('Método de Cálculo:') . '</b></td>
            <td colspan="7"> ' . utf8Decode($rowind['ind_formulaCalculo']) . '</b></td>';
        echo '</tr>';
        echo '<tr>
            <td></td>
            </tr>
            <tr>
            <td></td>
            <td><b>Tipo</b></td>
            <td><b>' . utf8Decode('Dimensión') . '</b></td>
            <td><b>' . utf8Decode('Frecuencia de Medición') . '</b></td>
            <td><b>' . utf8Decode('Forma de cálculo del indicador') . '</b></td><td><b>Tipo de Valor</b></td>
            <td><b>Sentido Esperado</b></td>';
        echo '</tr>';
        echo '<tr>
            <td></td>
            <td>' . utf8Decode($rowind['tpi_nombre']) . '</td><td>' . utf8Decode($rowind['dim_nombre']) . '</td>
            <td>' . utf8Decode($rowind['frm_nombre']) . '</td><td>' . utf8Decode($rowind['umi_nombre']) . '</td>
            <td>' . utf8Decode($rowind['tiv_nombre']) . '</td><td>' . utf8Decode($rowind['ses_nombre']) . '</td>';
        echo '</tr>';

        echo '<tr>
            <td></td></tr><tr>
            <td></td><td colspan="4" align="center"><b>' . utf8Decode('MEDIOS DE VERIFICACIÓN / FUENTE INFORMACIÓN') . '</b></td>
            <td align="CENTER" colspan="4"><b>SUPUESTO</b></td>';
        echo '</tr>';
        echo '<tr>
            <td></td>
            <td colspan="4">' . utf8Decode($rowind['mvac_nombre']) . '</td>
            <td align="CENTER" colspan="4">' . utf8Decode($rowind['spa_nombre']) . '</td>';
        echo '</tr>';

        $conexionmeta = new AccesoDatos();
        $conexionmeta->setSql('
            SELECT
                mac_descripcion,epg_id,
                mac_anio,
                mac_valor,
                mac_original,
                SUM(IF(tri_id = 1, mact_original, NULL)) ori_trimestre1,
                SUM(IF(tri_id = 2, mact_original, NULL)) ori_trimestre2,
                SUM(IF(tri_id = 3, mact_original, NULL)) ori_trimestre3,
                SUM(IF(tri_id = 4, mact_original, NULL)) ori_trimestre4,
                mac_modificado,
                SUM(IF(tri_id = 1, mact_modificado, NULL)) mod_trimestre1,
                SUM(IF(tri_id = 2, mact_modificado, NULL)) mod_trimestre2,
                SUM(IF(tri_id = 3, mact_modificado, NULL)) mod_trimestre3,
                SUM(IF(tri_id = 4, mact_modificado, NULL)) mod_trimestre4,
                mac_alcanzado,
                SUM(IF(tri_id = 1, mact_alcanzado, NULL)) alc_trimestre1,
                SUM(IF(tri_id = 2, mact_alcanzado, NULL)) alc_trimestre2,
                SUM(IF(tri_id = 3, mact_alcanzado, NULL)) alc_trimestre3,
                SUM(IF(tri_id = 4, mact_alcanzado, NULL)) alc_trimestre4,
                IF(mac_tipoCalendario IS NULL,"S",mac_tipoCalendario) AS mac_tipoCalendario
            FROM cat_MetaActividad
            INNER JOIN cat_MetaActividadTrimestre USING(pii_id, epg_id)
            INNER JOIN vin_ProductoIntermedioIndicador USING(pii_id)
            INNER JOIN enc_Programatico USING(epg_id)
            WHERE gpo_id = ? AND ur_id = ? AND ind_id IN(?)
                AND pdi_id IN (?) AND epg_id=?
            GROUP BY epg_id,pii_id;
        ');

        $params = [
            $rowind['gpo_id'],
            $rowind['ur_id'],
            $rowind['ind_id'],
            $rowind['pdi_id'],
            $rowind['epg_id'],
        ];

        $conexionmeta->conectar();
        $conexionmeta->consultar($params);
        $numMetas = 0;
        $primer = true;
        $numeroMeta = 0;
        $epg_id = 0;
        while ($rowmeta = $conexionmeta->extraer()) {
            $numMetas++;
            $numeroMeta++;

            echo '<tr></tr>';
            echo '<tr><td></td>';
            echo '<td colspan="8" align="center"><b>DATOS DE LA META</b></td>';
            echo '</tr>';

            echo '<tr><td></td><td></td>';
            echo '<td><b>' . utf8Decode('Definición de la variable Meta:') . '</b></td>';
            echo '<td colspan="6">' . $numeroMeta . '-' . utf8Decode($rowmeta['mac_descripcion']) . '</td>';
            echo '</tr>';
            echo '<tr><td></td><td></td>';
            echo '<td><b>' . utf8Decode('Unidad de medida:') . '</b></td>';
            echo '<td colspan="6">' . $rowind['umv_id'] . ' - ' . utf8Decode($rowind['umv_nombre']) . '</td>';

            echo '<tr></tr>';
            echo '<tr>';
            echo '<td></td> <td colspan="2" align="center"><b>' . utf8Decode('LÍNEA BASE DE LA META DEL INDICADOR') . '</b></td>'
            . '<td colspan="3" align="center"><b>' . utf8Decode('VALOR PROGRAMADO ANUAL') . '</b></td>';
            echo '</tr>';
            echo '<tr><td></td>';
            echo '<td align="center">' . utf8Decode('AÑO: ' . $rowind['mac_anio']) . '</td><td align="left">VALOR: ' . $rowind['mac_valor'] . '</td>'
            . '<td align="center">' . utf8Decode('AÑO:' . APP_EJERCICIO) . '</td> <td align="center">VALOR</td><td align="center">' . number_format($rowind['mac_original'], 2) . '</td>';

            echo '</tr>';

            if ($rowmeta['mac_tipoCalendario'] == 'C') {
                $sumoriginalanual = $rowmeta['ori_trimestre1'];
                $summodificadoanual = $rowmeta['mod_trimestre1'];
                //mientras el 1 pero este trimestre se vera reflejado dependiendo el trimestre activo.
                $sumAvanceFisicoanual = $rowmeta['alc_trimestre1'];
            } else {
                $sumoriginalanual = $rowmeta['mac_original'];
                $summodificadoanual = $rowmeta['mac_modificado'];
                $sumAvanceFisicoanual = $rowmeta['mac_alcanzado'];
            }
            $sumametasanual = $summodificadoanual;

            if ($sumametasanual == 0) {
                $unoporcavreportado = number_format(0, 2);
                $dosporcavreportado = number_format(0, 2);
                $tresporcavreportado = number_format(0, 2);
                $cuatroporcavreportado = number_format(0, 2);
                $porcentajeacumulado = number_format(0, 2);
            } else {
                $unoporcavreportado = number_format(($rowmeta['alc_trimestre1'] / $sumametasanual) * 100, 2);
                $dosporcavreportado = number_format(($rowmeta['alc_trimestre2'] / $sumametasanual) * 100, 2);
                $tresporcavreportado = number_format(($rowmeta['alc_trimestre3'] / $sumametasanual) * 100, 2);
                $cuatroporcavreportado = number_format(($rowmeta['alc_trimestre4'] / $sumametasanual) * 100, 2);

                $porcentajeacumulado = ($rowmeta['mac_tipoCalendario'] == 'C') ? ($rowmeta['alc_trimestre1'] * 25) / $sumametasanual
                // se comenta mientras se ve como recibira el trimestre acutual
                 : ($rowmeta['alc_trimestre1'] / $sumametasanual) * 100 +
                    ($rowmeta['alc_trimestre2'] / $sumametasanual) * 100 +
                    ($rowmeta['alc_trimestre3'] / $sumametasanual) * 100 +
                    ($rowmeta['alc_trimestre4'] / $sumametasanual) * 100;
                $porcentajeacumulado = number_format($porcentajeacumulado * 100, 2);
            }

            echo '<tr><td></td><td></td>';
            echo '<tr><td></td><td colspan="8" align="center"><b>AVANCE TRIMESTRAL</b></td><td></td>';
            echo '</tr>';
            echo '<tr><td></td>';
            echo '<td align="CENTER"></td>';
            echo '<td align="CENTER"><b>1er. TRIMESTRE</b></td>';
            echo '<td align="CENTER"><b>2o. TRIMESTRE</b></td>';
            echo '<td align="CENTER"><b>3er. TRIMESTRE</b></td>';
            echo '<td align="CENTER"><b>4o. TRIMESTRE</b></td>';
            echo '<td align="CENTER" colspan="2"><b>CUMPLIMIENTO DE METAS</b></td>';
            echo '</tr>';

            $trim1alc = 0;
            $trim2alc = 0;
            $trim3alc = 0;
            $trim4alc = 0;
            $trim1ori = $rowmeta['ori_trimestre1'];
            $trim1mod = ($_POST['capturandoTri'] >= 1 ? $rowmeta['mod_trimestre1'] : 0);
            $trim2ori = $rowmeta['ori_trimestre2'];
            $trim2mod = ($_POST['capturandoTri'] >= 2 ? $rowmeta['mod_trimestre2'] : 0);
            $trim3ori = $rowmeta['ori_trimestre3'];
            $trim3mod = ($_POST['capturandoTri'] >= 3 ? $rowmeta['mod_trimestre3'] : 0);
            $trim4ori = $rowmeta['ori_trimestre4'];
            $trim4mod = ($_POST['capturandoTri'] >= 4 ? $rowmeta['mod_trimestre4'] : 0);

            /* % ALCANZADO TRIMESTRAL */
            $porcentajealcanzadoTrim1 = ($rowmeta['mod_trimestre1'] != 0 ? number_format(($rowmeta['alc_trimestre1'] / $rowmeta['mod_trimestre1']) * 100, 2) : 0);
            $porcentajealcanzadoTrim2 = ($rowmeta['mod_trimestre2'] != 0 ? number_format(($rowmeta['alc_trimestre2'] / $rowmeta['mod_trimestre2']) * 100, 2) : 0);
            $porcentajealcanzadoTrim3 = ($rowmeta['mod_trimestre3'] != 0 ? number_format(($rowmeta['alc_trimestre3'] / $rowmeta['mod_trimestre3']) * 100, 2) : 0);
            $porcentajealcanzadoTrim4 = ($rowmeta['mod_trimestre4'] != 0 ? number_format(($rowmeta['alc_trimestre4'] / $rowmeta['mod_trimestre4']) * 100, 2) : 0);

            $acumuladoMod = $rowmeta['mod_trimestre1'] + $rowmeta['mod_trimestre2'] + $rowmeta['mod_trimestre3'] + $rowmeta['mod_trimestre4'];
            switch ($_POST['capturandoTri']) {
                case 1:
                    $trim1alc = $rowmeta['alc_trimestre1'];
                    $sumaalcanzados = $trim1alc;
                    break;
                case 2:
                    $trim1alc = $rowmeta['alc_trimestre1'];
                    $trim2alc = $rowmeta['alc_trimestre2'];
                    $sumaalcanzados = $trim1alc + $trim2alc;
                    break;
                case 3:
                    $trim1alc = $rowmeta['alc_trimestre1'];
                    $trim2alc = $rowmeta['alc_trimestre2'];
                    $trim3alc = $rowmeta['alc_trimestre3'];
                    $sumaalcanzados = $trim1alc + $trim2alc + $trim3alc;
                    break;
                case 4:
                    $trim1alc = $rowmeta['alc_trimestre1'];
                    $trim2alc = $rowmeta['alc_trimestre2'];
                    $trim3alc = $rowmeta['alc_trimestre3'];
                    $trim4alc = $rowmeta['alc_trimestre4'];
                    $sumaalcanzados = $trim1alc + $trim2alc + $trim3alc + $trim4alc;
                    break;
            }

            if ($sumametasanual == 0) {
                $porcentajeanual = number_format(0, 2);
            } else {
                $porcentajeanual = number_format(($sumaalcanzados / $sumametasanual) * 100, 2);
                $porcentajeanual = ($rowmeta['mac_tipoCalendario'] == 'C') ? number_format((($rowmeta['alc_trimestre1'] * 25) / $sumametasanual) + (($rowmeta['alc_trimestre2'] * 25) / $sumametasanual) + (($rowmeta['alc_trimestre3'] * 25) / $sumametasanual) + (($rowmeta['alc_trimestre4'] * 25) / $sumametasanual), 2) : number_format(($rowmeta['alc_trimestre' . $_POST['capturandoTri'] . ''] / $sumametasanual) * 100, 2);
            }

            echo '<tr><td></td>';
            echo '<td><b>PROGRAMADA</b></td>';
            echo '<td align="center">' . number_format($trim1ori, 2) . '</td>';
            echo '<td align="center">' . number_format($trim2ori, 2) . '</td>';
            echo '<td align="center">' . number_format($trim3ori, 2) . '</td>';
            echo '<td align="center">' . number_format($trim4ori, 2) . '</td>';
            echo '<td align="center"><b>T1+T2+T3+T4</b></td>';
            echo '<td align="center">' . number_format($sumoriginalanual, 2) . '</td>';
            echo '</tr>';
            echo '<tr><td></td>';
            echo '<td><b>' . utf8Decode('PROGRAMACIÓN ACTUALIZADA') . '</b></td>';
            echo '<td align="center">' . number_format($trim1mod, 2) . '</td>';
            echo '<td align="center">' . number_format($trim2mod, 2) . '</td>';
            echo '<td align="center">' . number_format($trim3mod, 2) . '</td>';
            echo '<td align="center">' . number_format($trim4mod, 2) . '</td>';
            echo '<td align="center"><b>T1+T2+T3+T4</b></td>';
            echo '<td align="center">' . number_format($acumuladoMod, 2) . '</td>';
            echo '</tr>';
            echo '<tr> <td></td>';
            echo '<td><b>ALCANZADA</b></td>';
            echo '<td align="center">' . number_format($trim1alc, 2) . '</td>';
            echo '<td align="center">' . number_format($trim2alc, 2) . '</td>';
            echo '<td align="center">' . number_format($trim3alc, 2) . '</td>';
            echo '<td align="center">' . number_format($trim4alc, 2) . '</td>';
            echo '<td align="center"><b>T1+T2+T3+T4</b></td>';
            echo '<td align="center">' . number_format($sumaalcanzados, 2) . '</td>';
            echo '</tr>';
            echo '<tr> <td></td>';
            echo '<td><b>PORCENTAJE ALCANZADO TRIMESTRAL</b></td>';
            echo '<td align="center">' . $porcentajealcanzadoTrim1 . '</td>';
            echo '<td align="center">' . $porcentajealcanzadoTrim2 . '</td>';
            echo '<td align="center">' . $porcentajealcanzadoTrim3 . '</td>';
            echo '<td align="center">' . $porcentajealcanzadoTrim4 . '</td>';
            echo '<td align="center"><b>PORCENTAJE ALCANZADO ANUAL</b></td>';
            echo '<td align="center">' . $porcentajeanual . '</td>';
            echo '</tr>';
            echo '<tr><td></td><td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
            echo '<td>PORCENTAJE ALCANZADO EN EL TRIMESTRE:</td>';
            echo '<td>' . $porcentajeanual . '</td>';
            echo '</tr>';
            $primer = false;

            $epg_id = $rowmeta['epg_id'];
            if ($epg_id != $rowmeta['epg_id']) {
                $numeroMeta = 0;
            }

        }
        $conexionmeta->desconectar();
    } // fin de metas de una accion
}
echo '</table>';
