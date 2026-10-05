<?php
require_once 'zip.php';

//html, nombreArchivo, extensionArchivo, datoExtraNombre
function comprimir($cadena, $archivo, $extension, $extra = "", $descargar = true)
{
    $zip = new zip();

    $zip->add_fileFromString($cadena, $archivo . $extra . $extension);

    if ($descargar) {
        header("Content-type: application/force-download");
        header("Content-Transfer-Encoding: Binary");
        header("Content-length: " . strlen($zip->file()) . strlen($extension) . strlen($extra));
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=" . $archivo . $extra . ".zip");
        echo $zip->file();
    } else {
        return $zip;
    }
}
