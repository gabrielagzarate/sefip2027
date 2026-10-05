<?php

ini_set('display_errors', 1);
session_start();
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';

$omo_id = $_POST['repomo_id'];
$mod_gpo_id = $_POST['repmod_gpo_id'];
$mod_ur_id = $_POST['repmod_ur_id'];
$mod_id = $_POST['repmod_id'];
$tipoMov = $_POST['mod_tipoMovimiento'];

//variables de sesión
$nivel = $_SESSION[_S]['nvj_id'];

$ruta = "Folio" . $omo_id . $mod_gpo_id . $mod_ur_id . $mod_id . ".xls";
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=" . $ruta);
header("Pragma: no-cache");
header("Expires: 0");

$tipo = '';

switch ($nivel) {
    case 1:
    case 2: //UR y Ue
        $tipo = "and tcp_id!='P'";
        break;
    default:
        $tipo = "";
        break;
}

$conexion = new AccesoDatos();
$conexion->conectar();

$sql = "
        SELECT
            modificaciones.*, d.pe_nombre, pv.pre_vigente,pv.vig_ene, pv.vig_feb, pv.vig_mar,
             pv.vig_abr, pv.vig_may,pv.vig_jun, pv.vig_jul, pv.vig_ago, pv.vig_sep, pv.vig_oct,
             pv.vig_nov, pv.vig_dic,e.pre_claveCompleta,f.tmd_nombre, o.tmd_id,o.tfm_id
        FROM
        (
            SELECT
                b.omo_id, b.mod_id, b.mod_concepto,b.emo_id,a.mov_id, a.dmo_importe,b.enc_oop,
                    a.dmo_ene, a.dmo_feb, a.dmo_mar, a.dmo_abr, a.dmo_may, a.dmo_jun, a.dmo_jul,
                    a.dmo_ago, a.dmo_sep, a.dmo_oct, a.dmo_nov, a.dmo_dic,
                    a.gpo_id, a.ur_id, a.ue_id, a.epg_id, a.pg_id, a.pe_id, a.ecf_id,a.tcp_id,b.ecf_id as clavefin,
                    a.vig_ene pa_ene, a.vig_feb pa_feb, a.vig_mar pa_mar, a.vig_abr pa_abr, a.vig_may pa_may,
                a.vig_jun pa_jun, a.vig_jul pa_jul, a.vig_ago pa_ago, a.vig_sep pa_sep, a.vig_oct pa_oct,
                a.vig_nov pa_nov, a.vig_dic pa_dic, a.pre_vigente pa_importe,b.mod_ue_id
            FROM scp_MaestroModificacion b
                INNER JOIN scp_DetalleModificacion a using (omo_id,mod_gpo_id,mod_ur_id,mod_id)
            WHERE omo_id = :omo_id and mod_gpo_id= :mod_gpo_id and mod_ur_id= :mod_ur_id and mod_id= :mod_id $tipo
        ) modificaciones
        INNER JOIN cat_PartidaEspecifica d using(pe_id,pg_id)
        INNER JOIN presupuesto e using (gpo_id,ur_id,ue_id,epg_id, pg_id, pe_id,ecf_id,tcp_id)
        JOIN presupuestoVigente pv USING(gpo_id,ur_id,ue_id,epg_id, pg_id, pe_id,ecf_id,tcp_id)
        INNER JOIN enc_OrigenOperacion o using(omo_id,enc_oop)
        INNER JOIN cat_TipoModificacion f using(tmd_id)
";
$parametros = [
    'omo_id' => $omo_id,
    'mod_gpo_id' => $mod_gpo_id,
    'mod_ur_id' => $mod_ur_id,
    'mod_id' => $mod_id,
];
$conexion->setSql($sql);
$conexion->consultar($parametros);
$conexion->desconectar();

$objUnidadResponsable = new UnidadResponsable();
$objUnidadResponsable->setGpo_id($mod_gpo_id);
$objUnidadResponsable->setUr_id($mod_ur_id);
$objUnidadResponsable->select();

$ban = 0;
$sumA = 0;
$sumD = 0;
$sumC = 0;
echo '<table>';

    while ($row = $conexion->extraer()) {
        $datosCveFin = new AccesoDatos();
        $datosCveFin->conectar();
        $datosCveFin->setSql('select cfi_clave,cfi_nombre from enc_ClaveFinanciamiento
                        join ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' using(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                        where ecf_id= :ecf_id');
        $parametros = [
            'ecf_id' => $row['clavefin'],
        ];
        $datosCveFin->consultar($parametros);
        $datosCveFin->desconectar();
        $cvefin = $datosCveFin->extraer();
        $cve = utf8Decode($cvefin['cfi_clave'] . ' - ' . $cvefin['cfi_nombre']);
        $cvepres = $row['pre_claveCompleta'];
        if ($ban == 0) {
            echo '<tr >';
            echo '<td COLSPAN="6" align="center"  style="font-size: 18; background-color: #003366; color: white;"><b>UNIDAD RESPONSABLE: </b>' . $objUnidadResponsable->getGpo_id() . $objUnidadResponsable->getUr_id() . '-' . utf8Decode($objUnidadResponsable->getNombre()) . '</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td COLSPAN="6" align="center" style="font-size: 18; background-color: #003366; color: white;"><b>No. FOLIO: </b> ' . $row['mod_id'] . '</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td COLSPAN="6" align="center" style="font-size: 18; background-color: #003366; color: white;"><b>' . utf8Decode('TIPO DE ADECUACIÓN:') . '</b> ' . utf8Decode($row['tmd_nombre']) . '</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td COLSPAN="6" align="center" style="font-size: 18; background-color: #003366; color: white;"><b>CLAVE DE FINANCIAMIENTO:</b> ' . $cve . '</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td COLSPAN="6" align="center" style="font-size: 18; background-color: #003366; color: white;"> </td>';
            echo '</tr>';
            $ban = 1;
            echo '<tr>';
            echo '<td ALIGN="center" style="border-color: #000000; border-width:1px; border-style: solid;"><b>CLAVE PRESUPUESTAL</b></td>';
            echo '<td ALIGN="center" style="border-color: #000000; border-width:1px; border-style: solid;"></td>'; //TIPO
            echo '<td ALIGN="center" style="border-color: #000000; border-width:1px; border-style: solid;"><b>CONCEPTO</b></td>';
            echo '<td ALIGN="center" style="border-color: #000000; border-width:1px; border-style: solid;"><b>AMPLIACION</b></td>';
            echo '<td ALIGN="center" style="border-color: #000000; border-width:1px; border-style: solid;"><b>REDUCCION</b></td>';
            echo '<td ALIGN="center" style="border-color: #000000; border-width:1px; border-style: solid;"><b>CALENDARIZACION</b></td>';
            echo '</tr>';
        }
        echo '<tr>';
        echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $cvepres . '</td>';
        echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . $row['tcp_id'] . '</td>';
        echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"> ' . utf8Decode($row['pe_nombre']) . '</td>';
        if ($row['mov_id'] == 'A') {
            echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . "$" . number_format($row['dmo_importe'], 2) . '</td>';
            echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"> </td>';
            $sumA = $sumA + $row['dmo_importe'];
        } else if ($row['mov_id'] == 'R') {
            echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"> </td>';
            echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . "$" . number_format($row['dmo_importe'], 2) . '</td>';
            $sumD = $sumD + $row['dmo_importe'];
        } else {
            echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"> </td>';
            echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"> </td>';
            echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none">' . "$" . number_format($row['dmo_importe'], 2) . '</td>';
            $sumC = $sumC + $row['dmo_importe'];
        }
        echo '</tr>';
    }
    if ($sumA ==0 && $sumD ==0 && $sumC ==0){
        echo '<tr >';
    echo '<td COLSPAN="6" align="center"  style="font-size: 18; background-color: #003366; color: white;"><b>UNIDAD RESPONSABLE: </b>' . $objUnidadResponsable->getGpo_id() . $objUnidadResponsable->getUr_id() . '-' . utf8Decode($objUnidadResponsable->getNombre()) . '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td COLSPAN="6" align="center" style="font-size: 18; background-color: #003366; color: white;"><b>No. FOLIO: ' . $omo_id . ' </b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td COLSPAN="6" align="center" style="font-size: 18; background-color: #003366; color: white;"><b>' . utf8Decode('TIPO DE ADECUACIÓN:') . '</b> </td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td COLSPAN="6" align="center" style="font-size: 18; background-color: #003366; color: white;"><b>CLAVE DE FINANCIAMIENTO:</b> </td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td COLSPAN="6" align="center" style="font-size: 18; background-color: #003366; color: white;"> </td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td ALIGN="center" style="border-color: #000000; border-width:1px; border-style: solid;"><b>CLAVE PRESUPUESTAL</b></td>';
    echo '<td ALIGN="center" style="border-color: #000000; border-width:1px; border-style: solid;"></td>'; //TIPO
    echo '<td ALIGN="center" style="border-color: #000000; border-width:1px; border-style: solid;"><b>CONCEPTO</b></td>';
    echo '<td ALIGN="center" style="border-color: #000000; border-width:1px; border-style: solid;"><b>AMPLIACION</b></td>';
    echo '<td ALIGN="center" style="border-color: #000000; border-width:1px; border-style: solid;"><b>REDUCCION</b></td>';
    echo '<td ALIGN="center" style="border-color: #000000; border-width:1px; border-style: solid;"><b>CALENDARIZACION</b></td>';
    echo '</tr>';
    }
echo '<tr>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"></td>';
echo '<td style="border-color: #FFFFFF; border-width:1px; border-style: none"></td>';
echo '<td style="border-color: #000000; border-width:1px; border-style: solid"> <b>TOTAL</b> </td>';
echo '<td style="border-color: #000000; border-width:1px; border-style: solid">' . "$" . number_format($sumA, 2) . '</td>';
echo '<td style="border-color: #000000; border-width:1px; border-style: solid">' . "$" . number_format($sumD, 2) . '</td>';
echo '<td style="border-color: #000000; border-width:1px; border-style: solid">' . "$" . number_format($sumC, 2) . '</td>';
echo '</tr>';
echo '</table>';
