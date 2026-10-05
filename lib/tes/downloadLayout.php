<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once dirname(__FILE__) . '/../WebResponse.php';
require_once dirname(__FILE__) . '/../tes/layout.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2017.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2018.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2019.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2020.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2021.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2022.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2023.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2024.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2025.php';
require_once dirname(__FILE__) . '/../../tes/models/clcs2026.php';

class downloadLayout extends WebResponse
{
    public function generalayoutdet($fpa_id, $msc_id, $lot_ejercicio, $lote_id, $lai)
    {
        error_reporting(E_ALL);
        $conexion = new AccesoDatos();
        $conexion->conectar();

        $ruta = __DIR__ . "/../tmps/";
        $lot_id = '';
        $sqllay = " SELECT
            fpa_id,
            vl.msc_id,
            lot_ejercicio,
            lot_id,
            tlo_id,
            CONCAT('Layout/',
                    lar_ejercicio,
                    '/',
                    a.msc_id,
                    '/',
                    a.lar_dia,
                    '/',
                    a.usr_id,
                    '/') AS ruta,
            lar_archivo
        FROM
            ".BD_TESORERIA.".vin_LoteLayout AS vl
                INNER JOIN
            ".BD_TESORERIA.".lot_Lote USING (fpa_id , msc_id , lot_ejercicio , lot_id)
                INNER JOIN
            ".BD_TESORERIA.".lot_LayoutArchivo AS a ON vl.lay_id = a.lay_id
        WHERE
            a.lay_id = ?";
        $parametros = [$lai];

        $conexion->setSql($sqllay);
        $conexion->consultar($parametros);
        while ($l = $conexion->extraer()) {
            $lot_id .= "(" . $l['fpa_id'] . "," . $l['msc_id'] . "," . $l['lot_ejercicio'] . "," . $l['lot_id'] . "),";
            $tipoLote = $l['tlo_id'];
            $file = $l['ruta'];
            $name = $l['lar_archivo'];
        }

        $name = str_replace(".TXT", ".txt", $name);
        $name = str_replace(".CSV", ".csv", $name);

        $fichero = $name;

        $r = new RutaArchivoSefip();
        $r->setBase("tes");
        $r->setPath($file);
        $r->setAnioFichero(APP_EJERCICIO);
        $r->setUrFichero($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id']);

        $ruta = $r->getRutaMod();
        $ruta .= $fichero;

        $dest = __DIR__ . "/../../tmps/" . $fichero;
        copy($ruta, $dest);

        $regresar = new stdClass();
        $regresar->{"fichero"} = $fichero;
        echo json_encode($regresar);
    }

}
if (isset($_POST['lai_id'])) {
    $fpa_id = (isset($_POST['fpa_id']) && $_POST['fpa_id'] != '') ? $_POST['fpa_id'] : '1';
    $msc_id = (isset($_POST['msc_id']) && $_POST['msc_id'] != '') ? $_POST['msc_id'] : '1';
    $lot_ejercicio = (isset($_POST['lot_ejercicio']) && $_POST['lot_ejercicio'] != '') ? $_POST['lot_ejercicio'] : '2017';
    $lote_id = (isset($_POST['lot_id']) && $_POST['lot_id'] != '') ? $_POST['lot_id'] : '1';
    $lai_id = (isset($_POST['lai_id']) && $_POST['lai_id'] != '') ? $_POST['lai_id'] : '1';

    $prueba = new downloadLayout;
    $prueba->generalayoutdet($fpa_id, $msc_id, $lot_ejercicio, $lote_id, $lai_id);
}
