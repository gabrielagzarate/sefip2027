<?php

declare(strict_types=1);

session_start();

require_once dirname(__FILE__) . "../../../../../lib/constantes.php";
require_once dirname(__FILE__) . "../../../../../lib/helpers.php";
require_once dirname(__FILE__) . '../../../../../lib/AccesoDatos.php';

if (!isset($_SESSION[_S])) {
    echo "ACCESO NEGADO";
}

$gpo_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
$ur_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
$nvj = $_SESSION[_S]['nvj_id'];

$ruta = utf8decode('Resumen_Compromisos.xls');
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=" . $ruta);
header("Pragma: no-cache");
header("Expires: 0");

$estadoCompromiso = "";
$nombreUR = "";
$tamano = 14.43;

$hoy = date("Y-m-d H:i:s");

$mes = '';

switch ($_POST['estado']) {
    case 'P':
        $estadoCompromiso = "ESTADO DEL COMPROMISO - PARCIALMENTE PAGADO";
        break;
    case 'G':
        $estadoCompromiso = "ESTADO DEL COMPROMISO - GENERADO";
        break;
    case 'A':
        $estadoCompromiso = "ESTADO DEL COMPROMISO - AUTORIZADO";
        break;
    case 'D':
        $estadoCompromiso = "ESTADO DEL COMPROMISO - DEVENGADO";
        break;
    case 'C':
        $estadoCompromiso = "ESTADO DEL COMPROMISO - CANCELADO";
        break;
    default:
        $estadoCompromiso = "";
        break;
}

switch ($_POST['mes']) {
    case 1:
        $mes = 'ENERO';
        break;
    case 2:
        $mes = 'FEBRERO';
        break;
    case 3:
        $mes = 'MARZO';
        break;
    case 4:
        $mes = 'ABRIL';
        break;
    case 5:
        $mes = 'MAYO';
        break;
    case 6:
        $mes = 'JUNIO';
        break;
    case 7:
        $mes = 'JULIO';
        break;
    case 8:
        $mes = 'AGOSTO';
        break;
    case 9:
        $mes = 'SEPTIEMBRE';
        break;
    case 10:
        $mes = 'OCTUBRE';
        break;
    case 11:
        $mes = 'NOVIEMBRE';
        break;
    case 12:
        $mes = 'DICIEMBRE';
        break;
}

$busqueda = new AccesoDatos();
$busqueda->conectar();
$busqueda->setSql('SELECT ur_nombre FROM cat_UnidadResponsable WHERE gpo_id = ? AND ur_id = ? ;');
$params = [$gpo_id, $ur_id];
$busqueda->consultar($params);
if ($busqueda->getNumeroRegistros() > 0) {
    $nombreUR = $busqueda->extraer();
}

$busqueda->desconectar();

echo '<table align="center" width="100%" cellspacing="0" cellpadding="3" style="font-family:Calibri"; style="font-size: ' . $tamano . ';>';
echo '<tr>';
echo '<td colspan="22" align="CENTER"></td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="22" align="CENTER"><b>GOBIERNO DEL ESTADO DE OAXACA</b></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="22" align="CENTER"><b>CONTROL PRESUPUESTAL '.APP_EJERCICIO.'</b></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="22" align="CENTER"><b>' . $gpo_id . $ur_id . ' - ' . utf8decode($nombreUR['ur_nombre']) . '</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="22" align="CENTER"><b>RESUMEN DE COMPROMISOS DEL MES DE ' . $mes . ' </td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="22" align="CENTER"><b>' . $estadoCompromiso . '</b></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="22" align="CENTER"></td>';
echo '</tr>';

$color = '#545555;';

echo ' <tr>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color:' . $color . ' color: white;">COMPROMISO</td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: white;">' . utf8decode("FECHA DE CREACIÓN") . '</td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: white;">FECHA DE AUTORIZADO</td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: white;">FECHA DE CANCELADO</td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: white;">ESTADO COMPROMISO </td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: white;">TIPO </td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: white;">IMPORTE</td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: white;">ORIGINAL </td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: white;">CLAVE PRESUPUESTAL</td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: #000000; color: white;">NUMERO CONTROL</td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: #000000; color: white;">TIPO</td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: #000000; color: white;">RFC</td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: #000000; color: white;">REFERENCIA</td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: #000000; color: white;">NETO</td>';

echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: white;">AFECTACION</td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: white;">ESTADO </td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: white;">PERCEPCION</td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: white;">RETENCION</td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: white;">IMPORTE</td>';

echo '<td align="left" style="font-size: ' . $tamano . '; background-color: #000000; color: white;">CLC</td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: #000000; color: white;"><b>PERCEPCION</b></td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: #000000; color: white;"><b>RETENCION</b></td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: #000000; color: white;"><b>IMPORTE</b></b></td>';
echo '</tr>'; //FIN DEL ENCABEZADO DE LA TABLA

$estado = '';
$filtro = '';
$nivel = '';
$paramsFiltro = [];
if ($_POST['mes'] != "") {
    $filtro = ' AND MONTH(a.usr_fecha) = ? ' . PHP_EOL;
    array_push($paramsFiltro, $_POST['mes']);
}
$paramsEstado = [];
if ($_POST['estado'] != "") {
    $estado = ' AND ecm_id = ?' . PHP_EOL;
    array_push($paramsEstado, $_POST['estado']);
}
$paramsNivel = [];
if ($nvj == 1) {
    $nivel = ' AND a.usr_id = ?' . PHP_EOL;
    array_push($paramsNivel, $_SESSION[_S]['usr_id']);
}
$sql = '
    SELECT
        cmp_id,
        if(cmp_cmp_id is NULL,"",cmp_cmp_id) AS original,
        cmp_importe, ecm_id, afe_id,
        afe_percepcion, afe_retencion,
        TRUNCATE(afe_percepcion,2) - TRUNCATE(afe_retencion,2) AS importeAfe,
        if(clc_id = 0,"",clc_id) AS clc_id,
        if(clc_id=0,0,clc_percepcion) AS clc_percepcion,
        if(clc_id=0,0,clc_retencion) AS clc_retencion,
        if(clc_id=0,0,clc_percepcion-clc_retencion) AS importe,
        if(cmp_tipoCompromiso = "N","NORMAL","AJUSTE") AS cmp_tipoCompromiso,
        date(a.usr_fecha) AS fecha,
        date(a.cmp_fechaAutorizado) AS fechaA,
        date(a.cmp_fechaCancelado) AS fechaC,
        ecm_nombre,
        eaf_nombre,
        if(ncp_id is NULL,"","PRESUPUESTAL") AS es,
        ncp_id AS numcontrol, b.rfc_id,
        ncp_referencia AS referencia,
        ncp_percepcionNeto AS percepcion,
        pre_ClaveCompleta
    FROM  scp_Compromiso a
    INNER JOIN presupuesto USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
    LEFT JOIN scp_Presupuestal b USING (gpo_id,ur_id,cmp_id)
    LEFT JOIN scp_Afectacion USING (gpo_id,ur_id,afe_id,clc_id)
    LEFT JOIN scp_Clc USING (gpo_id,ur_id,clc_id)
    LEFT JOIN cat_EstadoCompromiso USING (ecm_id)
    LEFT JOIN cat_EstadoAfectacion USING (eaf_id)
    WHERE gpo_id = ?
    AND ur_id = ?
    ' . $filtro
    . $estado
    . $nivel . '
    ORDER BY cmp_id
';
$params = [$gpo_id, $ur_id];
$params_merge = array_merge($params, $paramsFiltro, $paramsEstado, $paramsNivel);

$miconexion = new AccesoDatos();
$miconexion->conectar();
$miconexion->setSql($sql);
$miconexion->consultar($params_merge);
$miconexion->desconectar();

$ur = $programa = $idp = $pe = '';
$banur = $banprograma = $banidp = $banpe = false;
$filas = 0;

$importe = 0;
$cmp_importe = 0;

while ($row = $miconexion->extraer()) {
    if ($filas % 2 == 0) {
        $color = '#F1F1F1;';
    } else {
        $color = '#FFFFFF;';
    }

    echo ' <tr>';
    echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>' . $row['cmp_id'] . '</b></td>';
    echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>' . $row['fecha'] . '</b></td>';
    echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>' . $row['fechaA'] . '</b></td>';
    echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>' . $row['fechaC'] . '</b></td>';
    echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>' . $row['ecm_nombre'] . '</b></td>';
    echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>' . $row['cmp_tipoCompromiso'] . '</b></td>';
    echo '<td align="right" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>$' . number_format((float) $row['cmp_importe'], 2) . '</b></td>';
    echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>' . $row['original'] . '</b></td>';
    echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>' . $row['pre_ClaveCompleta'] . '</b></td>';

    echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>' . $row['numcontrol'] . '</b></td>';
    echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>' . $row['es'] . '</b></td>';
    echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>' . $row['rfc_id'] . '</b></td>';
    echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>' . $row['referencia'] . '</b></td>';
    echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>' . $row['percepcion'] . '</b></td>';

    echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>' . $row['afe_id'] . '</b></td>';
    echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>' . $row['eaf_nombre'] . '</b></td>';
    echo '<td align="right" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>$' . number_format((float) $row['afe_percepcion'], 2) . '</b></td>';
    echo '<td align="right" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>$' . number_format((float) $row['afe_retencion'], 2) . '</b></td>';
    echo '<td align="right" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>$' . number_format((float) $row['importeAfe'], 2) . '</b></td>';
    echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>' . $row['clc_id'] . '</b></td>';
    echo '<td align="right" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>$' . number_format((float) $row['clc_percepcion'], 2) . '</b></td>';
    echo '<td align="right" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>$' . number_format((float) $row['clc_retencion'], 2) . '</b></td>';
    echo '<td align="right" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: BLACK;"><b>$' . number_format((float) $row['importe'], 2) . '</b></td>';
    echo '</tr>';
    $filas++;
    $importe += $row['importe'];
    $cmp_importe += $row['cmp_importe'];
}

$color = "#858585";

echo ' <tr>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"><b></b></td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"><b></b></td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"><b></b></td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"><b></b></td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"><b></b></td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"><b></b></td>';
echo '<td align="right" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"><b>$' . number_format((float) $cmp_importe, 2) . '</b></td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"><b></b></td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"><b></b></td>';

echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"><b></td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"><b></td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"><b></td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"></td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"></td>';

echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"></td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"></td>';
echo '<td align="right" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"></td>';
echo '<td align="right" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"></td>';
echo '<td align="right" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"></td>';
echo '<td align="left" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"></td>';
echo '<td align="right" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"></td>';
echo '<td align="right" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"></td>';
echo '<td align="right" style="font-size: ' . $tamano . '; background-color: ' . $color . ' color: WHITE;"><b>$' . number_format((float) $importe, 2) . '</b></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2" align="left" style="font-size: 10px;"></td>';
echo '<td colspan="2" align="left" style="font-size: 10px;"></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="21" align="left" style="font-size: 14px;"><b></b></td>';
echo '<td colspan="2" align="left" style="font-size: 14px;"><b>Fecha ' . $hoy . ' </b></td>';
echo '</tr>';

echo '</table>';
