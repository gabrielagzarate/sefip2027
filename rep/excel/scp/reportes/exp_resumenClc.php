<?php

/**
 * Description of ExportaResumenClc
 *
 * @author ?
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 14-ene-2019
 * Comment:
 * Se cambia encabezado
 * Objective:  Generar Reporte de : RESUMEN DE CLC'S
 * Regla de Negocio:
 * Esta pantalla no pregunta por permisos de escritura ó lectura
 *
 * */

session_start();
set_time_limit(0);
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';

$ruta = utf8Decode('exp_resumenClc.xls');
header('Content-type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename=' . $ruta);
header('Pragma: no-cache');
header('Expires: 0');

$ur_ent = '';
$nivel = $_SESSION[_S]['nvj_id'];
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
$todo = isset($_POST['SelectAll']) ? $_POST['SelectAll'] : '';
$parametrosC = [];

if ($nivel <= 2) {
    $gpo = $_SESSION[_S]['gpo_id'];
    $ur = $_SESSION[_S]['ur_id'];
    $ur_ent = $gpo . $ur;
} else {
    $ur = $_SESSION[_S]['clasAdm-ur'];
    $gpo = $_SESSION[_S]['clasAdm-gpo'];
    $ur_ent = $gpo . $ur;
}
$inicio = DateToQuotedMySQLDate($_POST['inicio']);
$termino = DateToQuotedMySQLDate($_POST['termino']);

$tcl_id = '';
$join = '';
$campos = '';

if ($tipo !== '' && $tipo !== '0') {
    $tcl_id = 'and tcl_id="' . $tipo . '"';
} elseif ($todo !== '') {
    $tcl_id = '';
}

$fechas = '';

if ($inicio != '' && $termino != '') {
    $fechas = ' and date(clc_fechaElaboracion) BETWEEN ' . $inicio . ' and ' . $termino . '';
} else {
    $fechas = '';
}

$miconexion = new AccesoDatos();
$miconexion->conectar();
$miconexion->setSql('select ur_nombre from cat_UnidadResponsable where ur_clave= :ur_ent');
$parametros = [
    'ur_ent' => $ur_ent,
];
$miconexion->consultar($parametros);
$miconexion->desconectar();
while ($row = $miconexion->extraer()) {
    $nom_ur = $row['ur_nombre'];
}

echo '<table>';

if ($nivel > 2) {
    echo '<tr>'; //SE EMPIEZA A FORMAR EL ENCABEZADO
    echo '<td COLSPAN="15" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:18px;" ALIGN="center"><b>GOBIERNO DEL ESTADO DE OAXACA</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td COLSPAN="15" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:16px;" ALIGN="center"><b>' . utf8Decode('SECRETARÍA DE FINANZAS - DIRECCIÓN DE EGRESOS ' . APP_EJERCICIO) . '</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td COLSPAN="15" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:14px;" ALIGN="center"><b>LISTADO DE CLCS DE LA FECHA  ' . $inicio . ' A LA  ' . $termino . '</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td COLSPAN="15" style="border-color: #FFFFFF; border-width:1px; border-style: none" ALIGN="center"><b>' . $gpo . $ur . ' -' . utf8Decode($nom_ur) . '</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td ALIGN="center" style="border-color: #FFFFFF; border-width:0px; border-style: solid"></td>';
    echo '</tr>';
} else {

    echo '<tr>'; //SE EMPIEZA A FORMAR EL ENCABEZADO
    echo '<td COLSPAN="12" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:18px;" ALIGN="center"><b>GOBIERNO DEL ESTADO DE OAXACA</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td COLSPAN="12" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:16px;" ALIGN="center"><b>' . utf8Decode('SECRETARÍA DE FINANZAS - DIRECCIÓN DE EGRESOS ' . APP_EJERCICIO) . '</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td COLSPAN="12" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:14px;" ALIGN="center"><b>LISTADO DE CLCS DE LA FECHA ' . $inicio . ' A LA  ' . $termino . ' </b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td COLSPAN="12" style="border-color: #FFFFFF; border-width:1px; border-style: none" ALIGN="center"><b>' . $gpo . $ur . ' -' . utf8Decode($nom_ur) . '</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td ALIGN="center" style="border-color: #FFFFFF; border-width:0px; border-style: solid"></td>';
    echo '</tr>';

}
//ESTE EXPORTAR SOLO ES PARA LOS DE NIVEL 8

if ($nivel > 2) {
    echo '<tr>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>CLC</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>CERE(CO)</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>NUMERO</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>CVE FIN</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>FECHA</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>CLAB. INTERB.</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>TIPO</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>SUBTIPO</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>ESTADO</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>CONCEPTO DE LA CLC</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>FECHA PAGO</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>' . utf8Decode('AFE. PRESUPUESTAL') . '</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>' . utf8Decode('PERCEPCIONES') . '</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>' . utf8Decode('RETENCIONES') . '</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>' . utf8Decode('IMPORTE') . '</b></td>';
    echo '</tr>';
} else {
    echo '<tr>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>CLC</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>CVE FIN</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>FECHA</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>CLAB. INTERB.</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>TIPO</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>CONCEPTO DE LA CLC</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>ESTADO</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>' . utf8Decode('AFE. PRESUPUESTAL') . '</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>' . utf8Decode('PERCEPCIONES') . '</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>' . utf8Decode('RETENCIONES') . '</b></td>';
    echo '<td align="center" style="font-size: 13px; background-color: #003366; color: white;"><b>' . utf8Decode('IMPORTE') . '</b></td>';
    echo '</tr>';
}

//SE EMPIEZA A ARMAR EL CUERPO
$miconexion = new AccesoDatos();
$miconexion->conectar();
$miconexion->setSql('
    SELECT
        gpo_id,
        ur_id,clc_id,
        MAX(scf.cfi_clave) AS cfi_clave,
        bur_id,
        tcl_nombre,
        clc_concepto,
        DATE_FORMAT(clc_fechaElaboracion,"%Y/%m/%d") AS clc_fechaElaboracion,
        TRUNCATE(clc_percepcion,2) - TRUNCATE(clc_retencion,2) AS importe,
        MAX(tes.lot_fechaPago) AS lot_fechaPago,
        stc_nombre,
        clc_percepcion AS percepcion,
        clc_retencion AS retencion,
        IF(!ISNULL(MAX(sp.ncp_percepcionNeto)),
            SUM(sp.ncp_percepcionNeto),
            0.00) AS afe_presupuestal,
        ecl_nombre,
        if(clc_tipocereCo = 1 AND clc_generacionCereCo IS NOT NULL,
            "CERE",
            if( clc_tipocereCo = 2 AND clc_generacionCereCo IS NOT NULL,
                "CERECO",
                "")) AS cereco,
        if(clc_generacionCereCo IS  NOT  NULL,
            clc_cereCo,
            "") AS clc_cereCo,
        if(ecl_id = 18 OR ecl_id = 17,
            "PAGADA",
            if(ecl_id = 21,
                "CANCELADA",
                IF(ecl_id = 20,
                    "INHABILITADA",
                    ""))) AS pagada
    FROM scp_Clc
        LEFT JOIN scp_ClcAuxiliar b USING (gpo_id,ur_id,clc_id,ecl_id)
        LEFT JOIN scp_Presupuestal sp USING(gpo_id, ur_id, clc_id,ecf_id)
        LEFT JOIN (
            SELECT
                MAX(l.lot_fechaPago) AS lot_fechaPago,
                gpo_id, ur_id, clc_id
            FROM ' . BD_TESORERIA . '.lot_Lote l
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc lc USING ( fpa_id, msc_id, lot_ejercicio, lot_id )
                WHERE l.lot_ejercicio = ' . APP_EJERCICIO . '
            GROUP BY gpo_id, ur_id, clc_id
        )tes USING (gpo_id,ur_id,clc_id)
        INNER JOIN cat_TipoClc USING(tcl_id)
        INNER JOIN cat_SubtipoClc USING(tcl_id,stc_id)
        INNER JOIN cat_EstadoClc USING (ecl_id)
        INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
        INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' scf USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
        LEFT JOIN cat_BancosUr USING(gpo_id,ur_id,bur_id)
    WHERE gpo_id = :gpo AND ur_id = :ur
        ' . $tcl_id . '
        ' . $fechas . '
        AND clc_id != 0
    #group by clc_id,scf.cfi_clave,tes.lot_fechaPago,sp.ncp_percepcionNeto
    group by clc_id
    order by clc_id;
    ');

$parametrosC += [
    'gpo' => $gpo,
    'ur'  => $ur,
];
$miconexion->consultar($parametrosC);
$miconexion->desconectar();

$filas = 0;
while ($row = $miconexion->extraer()) {

    if ($nivel > 2) {
        if ($filas % 2 == 0) {
            echo '<tr>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['clc_id'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['cereco'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['clc_cereCo'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['cfi_clave'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . fechan($row['clc_fechaElaboracion']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>"' . $row['bur_id'] . '"</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['tcl_nombre']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['stc_nombre']) . '</b></td>';
            echo '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['ecl_nombre']) . '</b></td>';
            echo '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['clc_concepto']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['lot_fechaPago'] . '</td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($row['afe_presupuestal'], 2) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($row['percepcion'], 2) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($row['retencion'], 2) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($row['importe'], 2) . '</b></td>';
            echo '</tr>';
        } else {
            echo '<tr>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['clc_id'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['cereco'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['clc_cereCo'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['cfi_clave'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . fechan($row['clc_fechaElaboracion']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>"' . $row['bur_id'] . '"</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['tcl_nombre']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['stc_nombre']) . '</b></td>';
            echo '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['ecl_nombre']) . '</b></td>';
            echo '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['clc_concepto']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['lot_fechaPago'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($row['afe_presupuestal'], 2) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($row['percepcion'], 2) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($row['retencion'], 2) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($row['importe'], 2) . '</b></td>';
            echo '</tr>';
        }
    } else {
        if ($filas % 2 == 0) {
            echo '<tr>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['clc_id'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . $row['cfi_clave'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . fechan($row['clc_fechaElaboracion']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>"' . $row['bur_id'] . '"</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['tcl_nombre']) . '</b></td>';
            echo '<td align="left" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['clc_concepto']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . utf8Decode($row['pagada']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($row['afe_presupuestal'], 2) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($row['percepcion'], 2) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($row['retencion'], 2) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #99CC99; color: black;"><b>' . number_format($row['importe'], 2) . '</b></td>';
            echo '</tr>';
        } else {
            echo '<tr>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['clc_id'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . $row['cfi_clave'] . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . fechan($row['clc_fechaElaboracion']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>"' . $row['bur_id'] . '"</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['tcl_nombre']) . '</b></td>';
            echo '<td align="left" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['clc_concepto']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . utf8Decode($row['pagada']) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($row['afe_presupuestal'], 2) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($row['percepcion'], 2) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($row['retencion'], 2) . '</b></td>';
            echo '<td align="center" style="font-size: 11px; background-color: #C6E7CE; color: black;"><b>' . number_format($row['importe'], 2) . '</b></td>';
            echo '</tr>';
        }
    }

    $filas++;
}

//FIN DEL CUERPO DE LA TABLA
echo '</table>';

function fechan($fechavieja)
{
    $d = substr($fechavieja, 8, 2);
    $m = substr($fechavieja, 5, 2);
    $a = substr($fechavieja, 0, 4);
    return $d . '/' . $m . '/' . $a;
}

function MySQLDateToDate($MySQLFecha)
{
    if (($MySQLFecha == '') or ($MySQLFecha == '0000-00-00')) {
        return '';
    } else {
        return date('d/m/Y', strtotime($MySQLFecha));
    }
}

function DateToQuotedMySQLDate($Fecha)
{
    if ($Fecha != '') {
        $trozos = explode('/', $Fecha, 3);
        return "'" . $trozos[2] . '-' . $trozos[1] . '-' . $trozos[0] . "'";
    } else {
        return 'NULL';
    }
}
