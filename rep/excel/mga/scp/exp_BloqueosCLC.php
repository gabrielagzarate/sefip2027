<?php

session_start();
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';
error_reporting(0);

$ruta = utf8decode('Resumen_BloqueosCLC.xls');
header('Content-type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename=' . $ruta);
header('Pragma: no-cache');
header('Expires: 0');

//parametros de entrada
$depto = isset($_POST['depto']) ? $_POST['depto'] : '';
$tipoClc = isset($_POST['tipo']) ? $_POST['tipo'] : '';
$subtipoClc = isset($_POST['stc_id']) ? $_POST['stc_id'] : '';
$ur_id = isset($_POST['ur_id']) ? $_POST['ur_id'] : '';
$claveFin = isset($_POST['cvefin']) ? $_POST['cvefin'] : '';
$mmc_id = isset($_POST['mmc_id']) ? $_POST['mmc_id'] : '';

$hoy = date('Y-m-d H:i:s');

//tabla
//encabezado
echo '<table align="center" width="100%">';
echo '<tr>';
echo '<td colspan="12" align="CENTER"><b>GOBIERNO DEL ESTADO DE OAXACA</b></td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="12" align="CENTER"><b>' . utf8decode('SECRETARÍA DE FINANZAS DEL ESTADO DE OAXACA') . '</td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="12" align="CENTER"><b>RESUMEN DE BLOQUEOS DE CLC</td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="12" align="CENTER"></td>';
echo '</tr>';
echo ' <tr>';
echo '<td align="left"  style="font-size: 12.5; background-color: #003366; color: white;">UR</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">UR NOMBRE</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">TIPO DE CLC</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">NOMBRE </td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">SUBTIPO CLC</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">NOMBRE</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">CLAVE DE FINANCIAMIENTO</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">NOMBRE</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">COMPROMISO</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">' . utf8decode('AFECTACIÓN') . '</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">CLC</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">FECHA DE INICIO</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">FECHA DE TERMINO</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">TIPO DE BLOQUEO</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">FECHA DE ALTA</td>';
echo '<td align="left" style="font-size: 12.5; background-color: #003366; color: white;">FECHA DE BAJA</td>';
echo '</tr>'; //FIN DEL ENCABEZADO DE LA TABLA

//armar las condicionales de la consulta
$wf = $where = '';

if ($depto != '') {
    $wf .= (($wf == '') ? ('WHERE dep_id = ' . $depto . ' ') : (' AND dep_id = ' . $depto . ' '));
}

if ($tipoClc != '' && $subtipoClc != '') {
    if ($subtipoClc[0] == 'X') {
        if (isset($tipoClc)) {
            $tipoClc = (!is_array($tipoClc)) ? (explode(',', $tipoClc)) : ($tipoClc);

            if (count($tipoClc) > 0) {
                if ($tipoClc[0] == 'X') {
                    $filtro = '';
                } else {
                    if (!is_array($tipoClc)) {
                        $wf .= ($wf == '') ? (' WHERE tcl_id ="' . $tipoClc . '" ') : (' AND tcl_id="' . $tipoClc . '" ');
                    } else {
                        $wf .= ($wf == '') ? (' WHERE tcl_id IN ("' . implode('","', $tipoClc) . '") ') : (' AND tcl_id IN ("' . implode('","', $tipoClc) . '") ');
                    }
                }
            }
        }
    } else {
        $filtro = '';

        if (!is_array($subtipoClc) && strpos($subtipoClc, ',')) {
            foreach ($subtipoClc as $dato) {
                $d = explode('_', $dato);
                $filtro .= '("' . $d[0] . '", ' . $d[1] . '),';
            }
        } else {
            $d = explode('_', $subtipoClc);
            $filtro .= '("' . $d[0] . '", ' . $d[1] . ')';
        }

        $wf .= ($wf == '') ? (' AND (tcl_id, stc_id) IN (' . trim($filtro, ',') . ')  ') : (' AND (tcl_id, stc_id) IN (' . trim($filtro, ',') . ')  ');
    }
}

$ur_id = (!is_array($ur_id)) ? explode(',', $ur_id) : $ur_id;
if (count($ur_id) > 0 && $ur_id != '') {
    if ($ur_id[0] == '0') {
        $wf .= '';
    } else {
        if (!is_array($ur_id)) {
            $ur_id = explode(',', $ur_id);
        }

        $wf .= (($wf == '') ? ('WHERE gpo_id = in(' . implode(',', $ur_id) . ') ') : ('AND CONCAT(gpo_id, ur_id) IN (' . implode(',', $ur_id) . ') '));
    }
}
$claveFin = (!is_array($claveFin)) ? explode(',', $claveFin) : $claveFin;
if (count($claveFin) > 0 && $claveFin != '') {
    if ($claveFin[0] == '0') {
        $wf .= '';
    } else {
        $incvfin = count($claveFin) > 0 ? implode('","', $claveFin) : $claveFin;
        $wf .= ($wf == '') ? 'WHERE  ecf_clave IN ("' . $incvfin . '") ' : ' AND ecf_clave IN ("' . $incvfin . '") ';
    }
}

$cmp = $afe = $clc = '" "';
if ($mmc_id != '') {
    $mmc_id = (!is_array($mmc_id)) ? explode(',', $mmc_id) : $mmc_id;

    if (count($mmc_id) > 0) {
        $valor = "='X'";

        if ($mmc_id[0] == 'A') {
            $wf .= '';
            $cmp = $afe = $clc = '"X" ';
        } else {
            if (!is_array($mmc_id)) {
                $mmc_id = explode(',', $mmc_id);
            }
            $arrayMmc = $mmc_id;
            foreach ($arrayMmc as $key => $val) {
                switch ($arrayMmc[$key]) {
                    case 'B':
                        $cmp = '"X"';
                        $wf .= (($wf == '') ? ' WHERE a.blc_compromiso ' . $valor . '' : ' AND a.blc_compromiso ' . $valor . '');
                        break;
                    case 'C':
                        $afe = '"X"';
                        $wf .= (($wf == '') ? ' WHERE a.blc_afectacion ' . $valor . '' : ' AND a.blc_afectacion ' . $valor . '');
                        break;
                    case 'D':
                        $clc = '"X"';
                        $wf .= (($wf == '') ? ' WHERE a.blc_clc ' . $valor . '' : ' AND a.blc_clc ' . $valor . '');
                        break;
                }
            }
        }
    }
}

$sql = '
    SELECT 
        gpo_id, ur_id, ur_nombre, ur_clave,
        tcl_id, tcl_nombre, stc_id, stc_nombre,
        dep_id, blc_inicio, blc_fin,
        ter_id,
        a.ecf_id, ecf_clave,
        cfi_clave, cfi_nombre,
        ' . $cmp . ' blc_compromiso, ' . $afe . ' blc_afectacion, ' . $clc . " blc_clc,
        #a.blc_compromiso, a.blc_afectacion, a.blc_clc,
        max( if(bit_observacion = 'SE INSERTA EL BLOQUEO', DATE_FORMAT(b.usr_fecha, '%Y/%m/%d %H:%i:%S'), '') ) AS FechaAlta,
        max( if(bit_observacion = 'SE ELIMINA EL BLOQUEO', DATE_FORMAT(b.usr_fecha, '%Y/%m/%d %H:%i:%S'), '') ) AS FechaBaja,
        if(ter_id = 'A' AND a.ecf_id IS NULL, 'BLOQUEO RECURSO ESTATAL', if(ter_id = 'B' AND a.ecf_id IS NULL, 'BLOQUEO RECURSO FEDERAL', 'TODOS') ) AS tipoBloqueo
    FROM  cat_BloqueoClc a
        INNER JOIN enc_FiltroClc USING(efc_id)
        INNER JOIN cat_UnidadResponsable USING(gpo_id, ur_id)
        INNER JOIN aux_UnidadResponsable USING(gpo_id, ur_id)
        INNER JOIN cat_TipoClc USING(tcl_id)
        INNER JOIN cat_SubtipoClc USING(tcl_id, stc_id)
        LEFT JOIN enc_ClaveFinanciamiento USING(ecf_id)
        LEFT JOIN sinpres.cat_ClaveFinanciamiento" . APP_EJERCICIO_ANTERIOR . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
        RIGHT JOIN ' . BD_BITACORA . '.bit_BloqueoClc b USING(efc_id, blc_id, blc_inicio, blc_fin, ter_id)
        ' . $wf . ' 
    GROUP BY a.ecf_id, blc_id, cfi_clave, cfi_nombre
    ORDER BY gpo_id, ur_id, tcl_id, stc_id, a.ecf_id, dep_id
        ';

$miconexion = new AccesoDatos();
$miconexion->conectar();
$miconexion->setSql($sql);
$miconexion->consultar();
$miconexion->desconectar();

$filas = true;
//SE EMPIEZA A ARMAR EL CUERPO de la tabla
while ($row = $miconexion->extraer()) {
    if ($filas) {
        echo ' <tr>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['gpo_id'] . '' . $row['ur_id'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . utf8decode($row['ur_nombre']) . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['tcl_id'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . utf8decode($row['tcl_nombre']) . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['stc_id'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . utf8decode($row['stc_nombre']) . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['cfi_clave'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . utf8decode($row['cfi_nombre']) . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['blc_compromiso'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['blc_afectacion'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['blc_clc'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['blc_inicio'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['blc_fin'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5; background-color: #99CC99; color: BLACK;"><b>' . $row['tipoBloqueo'] . '</b></td>';
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
        echo '<td align="left" style="font-size: 10.5;"><b>' . $row['blc_compromiso'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5;"><b>' . $row['blc_afectacion'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5;"><b>' . $row['blc_clc'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5;"><b>' . $row['blc_inicio'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5;"><b>' . $row['blc_fin'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5;"><b>' . $row['tipoBloqueo'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5;"><b>' . $row['FechaAlta'] . '</b></td>';
        echo '<td align="left" style="font-size: 10.5;"><b>' . $row['FechaBaja'] . '</b></td>';
        echo '</tr>';
    }
    $filas = !$filas;
}

echo '<tr>';
echo '<td colspan="10" align="left" style="font-size: 10px;"></td>';
echo '<td colspan="10" align="left" style="font-size: 10px;"></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="10" align="left" style="font-size: 14px;"><b>Fecha ' . $hoy . ' </b></td>';
echo '</tr>';

echo '</table>';
