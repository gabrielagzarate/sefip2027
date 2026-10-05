<?php

/**
 * Description of reporte Excepciones Recurso Federal
 *
 * @author ?
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 16-ene-2020
 * Comment:
 * Se actualiza la consulta a 2019
 * Regla de Negocio:
 * Esta pantalla no pregunta por permisos
 *
 * */

session_start();
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';
require_once('../../../../lib/AccesoDatos.php');

$ruta = utf8decode('Resumen_ExcepcionesRecursoFederal.xls');
header('Content-type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename=' . $ruta);
header('Pragma: no-cache');
header('Expires: 0');

$hoy = date('Y-m-d H:i:s');
echo '<table align="center" width="100%">';
echo '<tr>';
echo '<td colspan="10" align="CENTER"><b>GOBIERNO DEL ESTADO DE OAXACA</b></td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="10" align="CENTER" ><b>' . utf8decode('SECRETARÍA') . ' DE FINANZAS DEL ESTADO DE OAXACA</td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="10" align="CENTER" ><b>RESUMEN DE EXCEPCIONES AGREGADAS  </td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="10" align="CENTER" ></td>';
echo '</tr>';

echo '<tr>';
echo '<td align="left"  style="font-size: 12.5; background-color: #003366; color: white;">UR</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">UR NOMBRE</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">TIPO DE CLC</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">NOMBRE </td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">SUBTIPO CLC</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">NOMBRE</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">CLAVE DE FINANCIAMIENTO</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">NOMBRE</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">FECHA DE ALTA</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">FECHA DE BAJA</td>';
echo '</tr>'; //FIN DEL ENCABEZADO DE LA TABLA

$wf = $where = '';
$filtro = '';
$paramswf = [];
if ($_POST['depto'] != '') {
    $wf .= ($wf == '') ? 'WHERE dep_id = ? ' : ' AND dep_id = ? ';
    array_push($paramswf, $_POST['depto']);
}
$_POST['tipo'] = is_array($_POST['tipo']) ? $_POST['tipo'] : [$_POST['tipo']];
if ($_POST['tipo'] != '' && $_POST['stc_id'] != '') {

    if ($_POST['stc_id'][0] == 'X') {

        if (count((array)($_POST['tipo'])) > 0) {

            if ($_POST['tipo'][0] == 'X') {
                $filtro = '';
            } else {
                $bind = '';
                foreach ((array) $_POST['tipo'] as $key => $value) {
                    $bind .= '?, ';
                    array_push($paramswf, $value);
                }
                $bind = substr($bind, 0, -2);
                $wf .= ($wf == '') ? 'where tcl_id in (' . $bind . ') ' : 'and tcl_id in (' . $bind . ') ';
            }
        }
    } else {
        $bind = '';
        foreach ((array) $_POST['stc_id'] as $dato) {
            $d = explode('_', $dato);
            $bind .= '(? , ?), ';
            array_push($paramswf, $d[0], $d[1]);
        }
        $bind = substr($bind, 0, -2);
        $wf .= ($wf == '') ? '  AND (tcl_id,stc_id) in (' . $bind . ')  ' : '  AND (tcl_id,stc_id) in (' . $bind . ')  ';
    }
}

if ($_POST['ur_id'] != '') {
    if (count((array)($_POST['ur_id'])) > 0) {
        if ($_POST['ur_id'][0] == '0') {
            $wf .= '';
        } else {

            $bind = '';
            foreach ((array)$_POST['ur_id'] as $key => $value) {
                $bind .= '?, ';
                array_push($paramswf, $value);
            }
            $bind = substr($bind, 0, -2);
            $wf .= ($wf == '') ? 'WHERE  CONCAT(gpo_id,ur_id) in (' . $bind . ') ' : 'AND CONCAT(gpo_id,ur_id) in (' . $bind . ') ';
        }
    }
}

if ($_POST['cvefin'] != '') {
    if (count((array)($_POST['cvefin'])) > 0) {
        if ($_POST['cvefin'][0] == '0') {
            $wf .= '';
        } else {
            $bind = '';
            $d = explode(',', $_POST['cvefin']);
            foreach ($d as $key => $value) {
                $bind .= '?, ';
                array_push($paramswf, $value);
            }
            $bind = substr($bind, 0, -2);
            $wf .= ($wf == '') ? 'WHERE  ecf_clave  in (' . $bind . ') ' : ' AND ecf_clave in (' . $bind . ') ';
        }
    }
}

if ($_POST['depto'] == '46') {
    $esp = ' AND CONCAT(gpo_id,ur_id) NOT IN ("116","128","129") ';
}

$sql = '
    SELECT 
        gpo_id,ur_id,ur_nombre,
        tcl_id,tcl_nombre,stc_id,
        stc_nombre,cfi_clave,cfi_nombre,
        ecf_id,dep_id,
        MAX(IF(bit_observacion="EXCEPCIÓN AGREGADA"
            , DATE_FORMAT(b.usr_fecha,"%Y/%m/%d %H:%i:%S")
            ,"")) AS FechaAlta, 
        IF(MAX(IF(bit_observacion = "EXCEPCIÓN AGREGADA"
            , DATE_FORMAT(b.usr_fecha,"%Y/%m/%d %H:%i:%S"),"")) < MAX(IF(bit_observacion="EXCEPCIÓN ELIMINADA"
                ,DATE_FORMAT(b.usr_fecha,"%Y/%m/%d %H:%i:%S")
                ,"")
            ),
        MAX(IF(bit_observacion = "EXCEPCIÓN ELIMINADA"
            ,DATE_FORMAT(b.usr_fecha,"%Y/%m/%d %H:%i:%S"),""))
            , "") AS FechaBaja
    FROM enc_ExcepcionesRecursoFederal
        RIGHT JOIN ' . BD_BITACORA . '.bit_ExcepcionRecursoFederal b using(gpo_id,ur_id,tcl_id,stc_id,ecf_id,dep_id)
        INNER JOIN cat_UnidadResponsable using(gpo_id,ur_id)
        INNER JOIN cat_TipoClc using(tcl_id)
        INNER JOIN cat_SubtipoClc using(tcl_id,stc_id)
        INNER JOIN enc_ClaveFinanciamiento using(ecf_id)
        INNER JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id,cfi_id,cfi_anio)
        ' . $wf . ' 
        ' . $where . '
    GROUP BY gpo_id,ur_id,tcl_id,stc_id,ecf_id,dep_id,cfi_clave,cfi_nombre
    ORDER BY gpo_id,ur_id,tcl_id,stc_id,ecf_id,dep_id;';


$miconexion = new AccesoDatos();
$miconexion->conectar();
$miconexion->setSql($sql);
$miconexion->consultar($paramswf);
$miconexion->desconectar();

$ur = $programa = $idp = $pe = '';
$banur = $banprograma = $banidp = $banpe = false;
$filas = 0;
while ($row = $miconexion->extraer()) { //SE EMPIEZA A ARMAR EL CUERPO

    if ($filas % 2 == 0) {
        echo ' <tr>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['gpo_id'] . '' . $row['ur_id'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . utf8decode($row['ur_nombre']) . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['tcl_id'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . utf8decode($row['tcl_nombre']) . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['stc_id'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . utf8decode($row['stc_nombre']) . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['cfi_clave'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . utf8decode($row['cfi_nombre']) . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['FechaAlta'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['FechaBaja'] . '</b></td>';
        echo '</tr>';
    } else {

        echo ' <tr>';
        echo '<td align="left" style="font-size: 10.5;"><b>' . $row['gpo_id'] . '' . $row['ur_id'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5;"><b>' . utf8decode($row['ur_nombre']) . '</b></td>';
        echo '<td align="left" style="font-size: 10.5;"><b>' . $row['tcl_id'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5;"><b>' . utf8decode($row['tcl_nombre']) . '</b></td>';
        echo '<td align="left" style="font-size: 10.5;"><b>' . $row['stc_id'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5;"><b>' . utf8decode($row['stc_nombre']) . '</b></td>';
        echo '<td align="left" style="font-size: 10.5;"><b>' . $row['cfi_clave'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5;"><b>' . utf8decode($row['cfi_nombre']) . '</b></td>';
        echo '<td align="left" style="font-size: 10.5;"><b>' . $row['FechaAlta'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5;"><b>' . $row['FechaBaja'] . '</b></td>';
        echo '</tr>';
    }
    $filas++;
}
echo '<tr>';
echo '<td colspan="10" align="left" style="font-size: 10px;"></td>';
echo '<td colspan="10" align="left" style="font-size: 10px;"></td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="10" align="left" style="font-size: 14px;"><b>Fecha ' . $hoy . ' </b></td>';
echo '</tr>';
echo '</table>';
