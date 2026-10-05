<?php

session_start();
ini_set('memory_limit', '150M');
set_time_limit(0);
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';
require_once '../../../../lib/AccesoDatos.php';

function validaFecha($fecha)
{
    $mesEnCurso = true;
    if (count(explode('-', $fecha)) == 3) {
        list($año, $mes, $dia) = explode('-', $fecha);
        if (checkdate($mes, $dia, $año)) {
            return '"' . $año . '-' . $mes . '-' . $dia . '"';
        } else {
            return '"' . $dia . '-' . $mes . '-' . $año . '"';
        }

    } elseif (count(explode('/', $fecha)) == 3) {
        list($año, $mes, $dia) = explode('/', $fecha);
        if (checkdate($mes, $dia, $año)) {
            return '"' . $año . '-' . $mes . '-' . $dia . '"';
        } else {
            return '"' . $dia . '-' . $mes . '-' . $año . '"';
        }

    } else {
        return 'NULL';
    }

}
$ur_ent = '';
$nivel = $_SESSION[_S]['nvj_id'];
if ($nivel <= 2) {
    $gpo = $_SESSION[_S]['gpo_id'];
    $ur = $_SESSION[_S]['ur_id'];
    $ur_ent = $gpo . $ur;
} else {
    $ur = $_SESSION[_S]['clasAdm-ur'];
    $gpo = $_SESSION[_S]['clasAdm-gpo'];
    $ur_ent = $gpo . $ur;
}

$miconexion = new AccesoDatos();
$miconexion->setSql('
    SELECT ur_nombre
    FROM cat_UnidadResponsable
    WHERE ur_clave=?
');
$miconexion->conectar();
$miconexion->consultar([$ur_ent]);
$miconexion->desconectar();
while ($row = $miconexion->extraer()) {
    $nom_ur = $row['ur_nombre'];
}

$ruta = utf8Decode('NumeroDeControl.xls');
header('Content-type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename=' . $ruta);
header('Pragma: no-cache');
header('Expires: 0');
/* FIN */
//se comienza a  formar el encabezado
echo '<table class="table">';

echo '<tr>'; //SE EMPIEZA A FORMAR EL ENCABEZADO
echo '<td COLSPAN="8" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:18px;" ALIGN="center"><b>GOBIERNO DEL ESTADO DE OAXACA</b></td>';
echo '</tr>';

echo '<tr>';
echo '<td COLSPAN="8" style="border-color: #FFFFFF; border-width:1px; border-style: none;font-size:14px;" ALIGN="center"><b>' . utf8Decode('LISTADO DE NÚMERO DE CONTROL DE LA FECHA') . ' ' . $_POST['fechaIni'] . ' A LA  ' . $_POST['fechaFin'] . ' </b></td>';
echo '</tr>';
echo '<tr>';
echo '<td COLSPAN="8" style="border-color: #FFFFFF; border-width:1px; border-style: none" ALIGN="center"><b>' . $gpo . $ur . ' -' . utf8Decode($nom_ur) . '</b></td>';
echo '</tr>';
echo '<tr>';
echo '<td ALIGN="center" style="border-color: #FFFFFF; border-width:0px; border-style: solid"></td>';
echo '</tr>';

echo '<thead>';
echo '<tr>';
echo '<th>UR</th>';
echo '<th>Num. Control</th>';
echo '<th>CLC</th>';
echo '<th colspan="2">Cuenta Contable</th>';
echo '<th>Percepciones</th>';
echo '<th>Retenciones</th>';
echo '<th>Carga</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';
$filtro = '';
if ($_SESSION[_S]['nvj_id'] > 2 || $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 118) {
    $filtro .= (!empty($_POST['gpo_id'])) ? ' AND gpo_id = "' . $_POST['gpo_id'] . '"' : '';
    $filtro .= (!empty($_POST['ur_id'])) ? ' AND ur_id = "' . $_POST['ur_id'] . '"' : '';
} else {
    $filtro .= ' AND gpo_id = "' . $_SESSION[_S]['gpo_id'] . '"';
    $filtro .= ' AND ur_id = "' . $_SESSION[_S]['ur_id'] . '"';
    $filtro .= (!empty($_POST['tem_carga'])) ? ' AND c.tem_carga = "' . $_POST['tem_carga'] . '"'
    : '';
}
if (!empty($_POST['cg_primer'])) {
    $campo = ['ger_id', 'cgp_id', 'rub_id', 'cgcu_id', 'cgsc_id'];
    foreach (str_split($_POST['cg_primer']) as $pos => $valor) {
        $filtro .= ' AND ' . $campo[$pos] . ' = ' . $valor;
    }
}
$filtro .= (!empty($_POST['cg_cuarto'])) ? ' AND cgc_id = "' . str_pad(
    $_POST['cg_cuarto'],
    3,
    '0',
    STR_PAD_LEFT
) . '"' : '';
$filtro .= (!empty($_POST['registro'])) ? ' AND cta_id = "' . str_pad(
    $_POST['registro'],
    3,
    '0',
    STR_PAD_LEFT
) . '"' : '';
if (!empty($_POST['fechaIni'])) {
    $filtro .= ' AND afe_fechaInicio = ' . validaFecha($_POST['fechaIni']);
}

if (!empty($_POST['fechaFin'])) {
    $filtro .= ' AND afe_fechaFin = ' . validaFecha($_POST['fechaFin']);
}

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql('
    SELECT
        CONCAT(gpo_id,ur_id) ur_clave,ncc_id,clc_id,
        CONCAT(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,".",cta_gpo_id,cta_ur_id,".",cta_ue_id,".",cgc_id,".",cta_id) cuenta,
        cgc_nombre,
        afe_fechaInicio,afe_fechaFin,
        ncc_percepcion,ncc_retencion,
        c.tem_carga
    FROM scp_Contable
    INNER JOIN scp_Afectacion a USING(gpo_id,ur_id,afe_id,clc_id)
    INNER JOIN scp_Clc c USING(gpo_id,ur_id,clc_id)
    INNER JOIN cat_UrAdministracion USING ( gpo_id,ur_id )
    JOIN cg_CuentaCuarto USING(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id)
    WHERE clc_id != 0 AND ( ecl_id BETWEEN 3 AND 18 ) ' . $filtro . '
    ORDER BY cuenta
');

$conexion->consultar();
$conexion->desconectar();

while ($row = $conexion->extraer()) {
    echo '<tr>';
    echo '<th>' . $row['ur_clave'] . '</th>';
    echo '<th>' . $row['ncc_id'] . '</th>';
    echo '<th>' . $row['clc_id'] . '</th>';
    echo '<th>' . $row['cuenta'] . '</th><th>' . utf8Decode($row['cgc_nombre']) . '</th>';
    echo '<th>$ ' . number_format($row['ncc_percepcion'], 3) . '</th>';
    echo '<th>$ ' . number_format($row['ncc_retencion'], 3) . '</th>';
    echo '<th> ' . $row['tem_carga'] . '</th>';
    echo '</tr>';
}
echo '</tbody>';
echo '</table>';
