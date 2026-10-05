<?php
// Si no llegaron aquí por AJAX ...
if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
    echo "Poor Cyrus ...";
    return;
}
session_start();

require_once dirname(__FILE__) . '/../lib/AccesoDatos.php';

// Esta muestra TODO cg_CuentaMayor
//$sql = "select cgm_mayor AS c, cgm_nombre AS n from cg_CuentaMayor order by ger_id, cgp_id, rub_id, cgcu_id, cgsc_id;";

// Y esta muestra solo las usadas por la UR
$sql = "SELECT c.cgsc_cuenta AS c,c.cgsc_nombre AS n,c.ecu_id AS e,nat_id AS t
    FROM cat_CgSubcuenta  c
    GROUP BY ger_id, cgp_id, rub_id, cgcu_id, cgsc_id;";

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar();
$conexion->desconectar();

$nRegistros = $conexion->getNumeroRegistros();

if ($nRegistros <= 0) {
    echo json_encode(array('c' => '', 'n' => ''));
} else {
    $data = array();
    while ($row = $conexion->extraer()) {
        array_push($data, array(
            'c' => $row['c'],
            'n' => $row['n'],
            'e' => $row['e'],
            't' => $row['t'],
        ));
    }

    echo json_encode($data);
}
