<?php

require_once dirname(__FILE__) . '/../../lib/sessionSafeStart.php';
sessionSafeStart();
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/RutaArchivoSefip.php';

function getMonthFromString($str)
{
    // Dividir por guiones
    $parts = explode('-', $str);
    if (count($parts) < 2) {
        return null; // Validación
    }

    $datePart = $parts[1]; // "250924"

    // Extraer el mes (posición 3 y 4 → índice 2 y 3)
    $month = substr($datePart, 2, 2);

    return $month;
}

if (isset($_SESSION[_S]) && isset($_POST['carga'])) {
    $month = getMonthFromString($_POST['carga']);
    $carga = $_POST['carga'];
    $fichero = '';
    $base = '';
    $lnk = 'afectaciones/' . $month . '/Carga_Masiva/';

    if (isset($lnk)) {
        $tmp = explode('/', $lnk);
        $base = $tmp[0] . '/' . $tmp[1] . '/' . $tmp[2];
        $fichero = $tmp[3];
    }

    $r = new RutaArchivoSefip();
    $r->setBase('SCP');
    $r->setPath($base);
    $r->setAnioFichero(APP_EJERCICIO);
    $r->setUrFichero($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur']);
    $ruta = $r->getRutaMod();

    $fichero = $ruta . $carga;
    $rutatmp = './../../archivos/tmpm/';
    $tipo = '';
    $contenttype = 'application/octet-stream';

    if (file_exists($fichero . '.xlsx')) {
        $tipo = 'xlsx';
        $contenttype = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    } elseif (file_exists($fichero . '.xls')) {
        $tipo = 'xls';
        $contenttype = 'application/vnd.ms-excel';
    } else {
        echo 'Error';
        die();
    }

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $contenttype . ' ');
    header('Content-Disposition:file-transfer;filename="' . $carga . '.' . $tipo . '"');
    header('Cache-Control: public');
    header('Pragma: public');
    header('Content-Length: ' . filesize($fichero . '.' . $tipo));
    readfile($fichero . '.' . $tipo);

    die();
} else {
    echo 'Acceso Negado';
}
