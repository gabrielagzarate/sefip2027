<?php
ini_set('display_errors', 1);
session_start();
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../lib/MesNombre.php';
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';

$mes = isset($_POST['mes']) ? $_POST['mes'] : "";
$edo = (isset($_POST['edo']) && $_POST['edo'] != "" && $_POST['edo'] != "0") ? $_POST['edo'] : "";

$nivelusu = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : '';

$estadoClc = " clc.ecl_id >= 2 ";
if ($edo != "") {
    switch ($edo) {
        case "3":
            $estadoClc = "  ( ( clc.ecl_id BETWEEN 3 AND 16 ) OR clc.ecl_id = 19 )  ";
            break;
        default:
            $estadoClc = "  clc.ecl_id = " . $edo . " ";
            break;
    }
}
$nivelusu = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : '';

$ur = new UnidadResponsable();
$ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
$ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
$ur->select();
$encabLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
if ($nivelusu > 2) {
    $encabLin2 = "CONTABILIDAD GUBERNAMENTAL " . APP_EJERCICIO;
    $encabLin3 = 'UR: ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '-' . ($ur->getNombre());
} else {
    $encabLin3 = 'UR: ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '-' . ($ur->getNombre());
}

$encabLin4 = ('VINCULACIÓN DE CLC - ') . ' ' . MesNombre($mes);

$ue = '';
if ($nivelusu < 2) {
    $ue = ' AND c.ue_id=' . $_SESSION[_S]['clasAdm-ue'];
}

$parametros = [
    ':gpo' => $_SESSION[_S]['clasAdm-gpo'],
    ':ur' => $_SESSION[_S]['clasAdm-ur'],
    ':mes' => $mes
];

$sql = " 
    SELECT MAX(tcl_id) AS tcl, clc_id AS clc, MAX(clc_concepto) AS con, MAX(clc_cereCo) AS cco,
        replace(GROUP_CONCAT(DISTINCT CONCAT(cmp_id) ORDER BY cmp_id ), '0,', '') AS cmp,
        replace(GROUP_CONCAT(DISTINCT CONCAT(bdo_id) ORDER BY bdo_id ), '0,', '') AS bdo,
        replace(GROUP_CONCAT(DISTINCT CONCAT(cereCo_Terceros) ORDER BY cereCo_Terceros ), '0,', '') AS pt,
        imp, case when ((ecl_id BETWEEN 3 AND 16) OR ecl_id = 19) then 3 else ecl_id end as edo
    FROM (
        SELECT DISTINCT clc.clc_id, 0 AS cmp_id, bdo_id, clc_cereCo, clc.tcl_id, clc_concepto, 0 AS cereCo_Terceros,
            (clc_percepcion - clc_retencion) as  imp, clc.ecl_id
        FROM scp_Clc clc
        JOIN scp_ClcAuxiliar aux USING( gpo_id, ur_id, clc_id )
        JOIN scp_Afectacion afe USING( gpo_id, ur_id, clc_id )
        JOIN scp_Contable c USING( gpo_id, ur_id, afe_id )
        WHERE clc.gpo_id = :gpo AND clc.ur_id = :ur
        AND $estadoClc AND MONTH( clc.clc_fechaAutorizacion ) = :mes

        UNION ALL

        SELECT DISTINCT clc.clc_id, cmp_id, bdo_id , clc_cereCo, clc.tcl_id, clc_concepto, 0 AS cereCo_Terceros,
            (clc_percepcion - clc_retencion) as  imp, clc.ecl_id
        FROM scp_Clc clc
        JOIN scp_ClcAuxiliar aux USING( gpo_id, ur_id, clc_id )
        JOIN scp_Afectacion afe USING( gpo_id, ur_id, clc_id )
        JOIN scp_Presupuestal p USING( gpo_id, ur_id, afe_id )
        WHERE clc.gpo_id = :gpo AND clc.ur_id = :ur
        AND $estadoClc AND MONTH( clc.clc_fechaAutorizacion ) = :mes

        UNION ALL

        SELECT MIN(ncc_clc) AS ncc_clc, 0 AS cmp_id, 0 AS bdo_id, 0 AS clc_cereCo, tcl_id, clc_concepto, clc_cereCo AS cereCo_Terceros,
            (clc_percepcion - clc_retencion) as  imp, clc.ecl_id
        FROM scp_Contable con
            INNER JOIN scp_Clc clc USING( gpo_id, ur_id, clc_id )
            JOIN scp_ClcAuxiliar aux USING( gpo_id, ur_id, clc_id )
            INNER JOIN cat_EstadoClc ec ON( clc.ecl_id = ec.ecl_id )
        WHERE ncc_gpo = :gpo AND ncc_ur = :ur
            AND clc.ecl_id IN (13, 16)
        GROUP BY gpo_id, ur_id, clc_id

        ORDER BY clc_id
    ) AS datos
    GROUP BY tcl_id, clc_id, ecl_id
    HAVING cmp <> '0'; ";

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar($parametros);
$conexion->desconectar();

$datos = array();
$x = 0;
while ($row = $conexion->extraer()) {
    $datos[$x]['tcl'] = $row['tcl'];
    $datos[$x]['clc'] = $row['clc'];
    $datos[$x]['con'] = $row['con'];
    $datos[$x]['cmp'] = $row['cmp'];
    $datos[$x]['bdo'] = $row['bdo'];
    $datos[$x]['clc1'] = $row['clc'];
    $datos[$x]['cco'] = $row['cco'];
    $datos[$x]['clc2'] = $row['clc'];
    $datos[$x]['bdo1'] = $row['bdo'];
    $datos[$x]['pt'] = $row['pt'];
    $datos[$x]['edo'] = $row['edo'];
    $datos[$x]['imp'] = $row['imp'];

    $x++;
}

$anchoColumnas = array();
$nombreColumnas = array();
$incluirColumnas = array();
$tipoColumnas = array();

array_push($anchoColumnas, 15);
array_push($nombreColumnas, "TIPO");
array_push($incluirColumnas, "tcl");
array_push($tipoColumnas, "cC");

array_push($anchoColumnas, 15);
array_push($nombreColumnas, "CLC");
array_push($incluirColumnas, "clc");
array_push($tipoColumnas, "cC");

array_push($anchoColumnas, 50);
array_push($nombreColumnas, "CONCEPTO");
array_push($incluirColumnas, "con");
array_push($tipoColumnas, "cC");

array_push($anchoColumnas, 20);
array_push($nombreColumnas, "COMPROMETIDO");
array_push($incluirColumnas, "cmp");
array_push($tipoColumnas, "cC");

array_push($anchoColumnas, 20);
array_push($nombreColumnas, "DEVENGADO");
array_push($incluirColumnas, "bdo");
array_push($tipoColumnas, "cC");

array_push($anchoColumnas, 20);
array_push($nombreColumnas, "EJERCIDO");
array_push($incluirColumnas, "clc1");
array_push($tipoColumnas, "cC");

array_push($anchoColumnas, 20);
array_push($nombreColumnas, "Cere/Cereco");
array_push($incluirColumnas, "cco");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 20);
array_push($nombreColumnas, "TRANSFERIDO");
array_push($incluirColumnas, "clc2");
array_push($tipoColumnas, "cC");

array_push($anchoColumnas, 20);
array_push($nombreColumnas, "PAGADO");
array_push($incluirColumnas, "bdo1");
array_push($tipoColumnas, "cC");

array_push($anchoColumnas, 20);
array_push($nombreColumnas, "PAGOS A TERCEROS");
array_push($incluirColumnas, "pt");
array_push($tipoColumnas, "cC");

array_push($anchoColumnas, 20);
array_push($nombreColumnas, "ESTADO CLC");
array_push($incluirColumnas, "edo");
array_push($tipoColumnas, "cD");

array_push($anchoColumnas, 20);
array_push($nombreColumnas, "IMPORTE");
array_push($incluirColumnas, "imp");
array_push($tipoColumnas, "n");

$table = array();
foreach ($incluirColumnas as $key => $val) {
    $table['nombre'][$val] = $nombreColumnas[$key];
    $table['ancho'][$val] = $anchoColumnas[$key];
    $table['campo'][$val] = $key;
    $table['tipo'][$val] = $tipoColumnas[$key];
}
$table['textoCompleto'] = false;

$tablaimg['imagen'] = 1;
$tablaimg[0]['nombre'] = "Logo";
$tablaimg[0]['ruta'] = "../../../../img/escudocolor_derecha.png";

$tablaimg[0]['colImg'] = "H1";
$tablaimg[0]['alingXimg'] = "10";

$tablaimg[0]['altura'] = "75";
$tablaimg[0]['rotacion'] = "0";
if (!is_file($tablaimg[0]['ruta'])) {
    $tablaimg['imagen'] = 0;
    unset($tablaimg[0]);
}

$encabezado = array(
    'encabLin1' => $encabLin1, 'encabLin2' => $encabLin2, 'encabLin3' => $encabLin3,
    'encabLin4' => $encabLin4, 'ancho' => $anchoColumnas, 'alineacionenc' => 'I',
);

$otrosParametros = array(
    'comprimir' => false, 'nombre' => "VinculacionCLCFONDOS", 'sufijoNombre' => "-" . MesNombre($mes),
    'tablaimg' => $tablaimg
);
$excel = new generaPhpspreadsheet();
$excel->generaExcel($datos, $encabezado, $table, $otrosParametros);
