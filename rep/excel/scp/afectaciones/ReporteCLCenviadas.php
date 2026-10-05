<?php
session_start();
ini_set('memory_limit', '30M');

require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';

$ruta = utf8Decode('reportelote.xls');
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=" . $ruta);
header("Pragma: no-cache");
header("Expires: 0");
$c = new AccesoDatos();
$in = explode("/", $_POST['inicio']);
$inicio = $in[2] . "-" . $in[1] . "-" . $in[0];
$dep = $_SESSION[_S]['uni_id'] == 17 ? '44,45,46' : '48,49';
$params = [];
$sql = '
    SELECT gpo_id, ur_id, bclc.ecl_id, clc_id, CONCAT( gpo_id, ur_id ) AS UR,
        ( TRUNCATE(clc_percepcion,2) - TRUNCATE(clc_retencion,2) ) AS Importe, clc_concepto, clc_cereCo,
        date_format( bclc.usr_fecha, "%e/%m/%Y %H:%i:%s" ) AS FechaCLC,
        clc_cereCo, mpc_nombre, tcl_nombre, ecf_clave, clc_percepcion, clc_retencion,
        clc_beneficiario, ter_id TipoRecurso, dep_id departamento
    FROM scp_Clc C
        INNER JOIN scp_ClcAuxiliar b USING ( gpo_id, ur_id, clc_id )
        INNER JOIN
            (
            SELECT MAX(bit_clc), gpo_id, ur_id, clc_id, ecl_id, usr_fecha
            FROM ' . BD_BITACORA . '.bit_Clc
            WHERE ecl_id IN (10)
            AND DATE( usr_fecha ) = DATE(:inicio)
            AND usr_id = :usr_id
            GROUP BY gpo_id, ur_id, clc_id, ecl_id, usr_fecha
            ) bclc USING (gpo_id, ur_id, clc_id)
        INNER JOIN cat_ModoPagoClc USING ( mpc_id )
        INNER JOIN cat_TipoClc USING ( tcl_id )
        INNER JOIN enc_FiltroClc USING ( gpo_id, ur_id, tcl_id, stc_id, mno_id )
        INNER JOIN enc_ClaveFinanciamiento USING ( ecf_id )
        INNER JOIN sinpres.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING ( cff_id, fte_id, sft_id, ori_id, sor_id )
    WHERE C.clc_id != 0
        AND SUBSTRING( C.tem_carga, 1, 5 ) != "116-A"
';
$params[':inicio'] = $inicio;
$params[':usr_id'] = $_SESSION[_S]['usr_id'];

if (isset($_POST['tipo']) && $_POST['tipo'] != "") {
    $sql .= ' AND clc_tipoCereCo = :tipo';
    $params[':tipo'] = $_POST['tipo'];
}

if (isset($_POST['mpc_id']) && $_POST['mpc_id'] != "") {
    $sql .= ' AND mpc_id = :mpc_id';
    $params[':mpc_id'] = $_POST['mpc_id'];
}
if (isset($_POST['ter_id']) && $_POST['ter_id'] != "") {
    if ($_POST['ter_id'] == '0') {
        $sql .= ' AND ter_id IN ("A", "B")';
    } else {
        $sql .= ' AND ter_id = :ter_id';
        $params[':ter_id'] = $_POST['ter_id'];
    }

}
if (isset($_POST['dep_id']) && $_POST['dep_id'] != "") {
    $sql .= ' AND dep_id   = :dep_id';
    $params[':dep_id'] = $_POST['dep_id'];
} else {
    if ($_SESSION[_S]['uni_id'] == 17) {
        $sql .= ' AND dep_id IN (' . $dep . ') ';
    } else {
        $sql .= ' AND dep_id IN (' . $dep . ') ';
    }
}

if (isset($_POST['ur']) && $_POST['ur'] != "") {
    $gpo = substr($_POST['ur'], 0, 1);
    $ur = substr($_POST['ur'], 1, 2);
    $sql .= ' AND gpo_id = :gpo_id AND ur_id = :ur_id';
    $params[':gpo_id'] = $gpo;
    $params[':ur_id'] = $ur;
}

if (isset($_POST['clave']) && $_POST['clave'] != "") {
    $sql .= ' AND ecf_clave = :clave';
    $params[':clave'] = $_POST['clave'];
}

if (isset($_POST['horaInicio']) && $_POST['horaInicio'] != "" && isset($_POST['horaFin']) && $_POST['horaFin'] != "") {
    $sql .= ' AND TIME(bclc.usr_fecha) BETWEEN TIME(:horaInicio) AND TIME(:horaFin)';
    $params[':horaInicio'] = $_POST['horaInicio'];
    $params[':horaFin'] = $_POST['horaFin'];
} elseif (isset($_POST['horaInicio']) && $_POST['horaInicio'] != "") {
    $sql .= ' AND TIME(bclc.usr_fecha) > TIME(:horaInicio)';
    $params[':horaInicio'] = $_POST['horaInicio'];
} elseif (isset($_POST['horaFin']) && $_POST['horaFin'] != "") {
    $sql .= ' AND TIME(bclc.usr_fecha) < TIME(:horaFin)';
    $params[':horaFin'] = $_POST['horaFin'];
}

$sql .= '
    GROUP BY gpo_id,ur_id,clc_id
    ORDER BY gpo_id,ur_id,clc_id';

$c->conectar();
$c->setSql($sql);
$c->consultar($params);
$c->desconectar();

$TipoRecurso = $departamento = 'NO ESPECIFICADO';
$cuerpo = '';
while ($data = $c->extraer()) {
    $TipoRecurso = ($TipoRecurso == 'NO ESPECIFICADO' ? $data["TipoRecurso"] : ($TipoRecurso == $data["TipoRecurso"] && $TipoRecurso != 'TODOS' ? $data["TipoRecurso"] : "TODOS"));
    $departamento = ($departamento == 'NO ESPECIFICADO' ? $data["departamento"] : ($departamento == $data["departamento"] && $departamento != 'TODOS' ? $data["departamento"] : "TODOS"));
    $cuerpo .= '
            <tr>
                <td>' . $data['clc_id'] . '</td>
                <td>' . $data['clc_cereCo'] . '</td>
                <td>' . $data['UR'] . '</td>
                <td>' . $data['mpc_nombre'] . '</td>
                <td>' . utf8Decode($data['tcl_nombre']) . '</td>
                <td>' . utf8Decode($data['ecf_clave']) . '</td>
                <td>' . utf8Decode($data['clc_concepto']) . '</td>
                <td>' . utf8Decode($data['clc_beneficiario']) . '</td>
                <td>' . $data['clc_percepcion'] . '</td>
                <td>' . $data['clc_retencion'] . '</td>
                <td align="right"><span style="float:left">$</span>' . number_format(
        $data['Importe'],
        2
    ) . '</td>
                <td align="center">' . $data['FechaCLC'] . '</td>
            </tr>';
}

$encabezado = utf8Decode('
    <tr>
        <td colspan="12" style="font-size: 16px;" align="center"><b>GOBIERNO DEL ESTADO DE OAXACA</b></td>
    </tr>
    <td colspan="12" style="font-size: 16px;" align="center"><b>SECRETARIA DE FINANZAS - DIRECCIÓN DE PRESUPUESTO ' . APP_EJERCICIO . '</b></td>
    </tr>
    <tr>
        <td colspan="12" style="font-size: 16px;" align="center"><b>RELACIÓN DE CLC PROCESADAS</b></td>
    </tr>
    <tr>
        <td colspan="12" style="font-size: 16px;" align="center"><b>
        ' . (isset($_POST['tipo']) && $_POST['tipo'] == "1" ? 'CÉDULA DE REGISTRO (CERE)' : "") . '
        ' . (isset($_POST['tipo']) && $_POST['tipo'] == "2" ? 'CÉDULA DE REGISTRO CUENTA CONTABLE (CERECO)' : "") .
    '</b></td>
    </tr>
    <tr>
        <td colspan="12" style="font-size: 16px; heigth: 10px;" align="center"></td>
    </tr>
    <tr>
        <td colspan="4" style="font-size: 14px;" align="left">TIPO DE RECURSO: </td>
        <td colspan="8" style="font-size: 14px;" align="left">' . $TipoRecurso . '</td>
    </tr>
    <tr>
        <td colspan="4" style="font-size: 14px;" align="left">DEPARTAMENTO DE APLICACIÓN DE CLC: </td>
        <td colspan="8" style="font-size: 14px;" align="left">' . $departamento . '</td>
    </tr>
    <tr>
        <td colspan="12" style="font-size: 16px; heigth: 10px;" align="center"></td>
    </tr>
');

$html = '
        <style type="text/css">
            .tabla{
                width:100%;
            }
            .tabla td {
                font-size:10.5px; font-family:Calibri; border-bottom:1px solid #AAAAAA; border-right:1px dashed #E0E0E0;
            }
        </style>

        <table cellpadding="3" class="table table-bordered" width="100%" style="border:1px solid #333;">'
    . $encabezado . '
            <tr>
                <th colspan="4" align="center" style="border: 1px solid #AAA; background-color:#EAEAEA;">
                    <br>
                    RELACI&Oacute;N DE CLC ENVIADAS
                </th>
            </tr>
            <tr>
                <td colspan="12">
                    <table align="center" width="100%" border="0" cellspacing="0" cellpadding="1" class="tabla">
                        <tr>
                            <th style="border:1px solid #333;" width="25px" class="warning">No. CLC</th>
                            <th style="border:1px solid #333;" width="30px" class="warning">CERE-CO</th>
                            <th style="border:1px solid #333;" width="15px" class="warning">UR</th>
                            <th style="border:1px solid #333;" width="50px" class="warning">MODALIDAD DE PAGO</th>
                            <th style="border:1px solid #333;" width="25px" class="warning">TIPO DE CLC</th>
                            <th style="border:1px solid #333;" width="25px" class="warning">CLAVE DE FINANCIAMIENTO</th>
                            <th style="border:1px solid #333;" width="70px" class="warning">CONCEPTO</th>
                            <th style="border:1px solid #333;" width="50px" class="warning">BENEFICIARIO</th>
                            <th style="border:1px solid #333;" width="25px" class="warning">PERCEPCIONES</th>
                            <th style="border:1px solid #333;" width="25px" class="warning">RETENCIONES</th>
                            <th style="border:1px solid #333;" width="25px" class="warning">IMPORTE</th>
                            <th style="border:1px solid #333;" width="30px" class="warning">FECHA Y HORA DE ENVI&Oacute;</th>
                        </tr>' .
    $cuerpo . '
                    </table>
                </td>
            </tr>';
$html .= '</table>';

echo '<style type="text/css">
            .tabla{
                width:100%;
            }
            .tabla td {
                font-size:10.5px; font-family:Calibri; border-bottom:1px solid #AAAAAA; border-right:1px dashed #E0E0E0;
            }
        </style>';
echo $html;
