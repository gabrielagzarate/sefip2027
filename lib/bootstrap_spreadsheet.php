<?php

/**
 * Carga centralizada de PhpSpreadsheet.
 * Permite cambiar de versión sin editar cada controlador.
 */
$spreadsheetAutoloadCandidates = [
    __DIR__ . '/../rep/lib/PhpSpreadsheet-1.29.12/vendor/autoload.php',
    __DIR__ . '/../rep/lib/vendor/autoload.php',
];

$spreadsheetAutoloadLoaded = false;
foreach ($spreadsheetAutoloadCandidates as $spreadsheetAutoloadFile) {
    if (is_file($spreadsheetAutoloadFile)) {
        require_once $spreadsheetAutoloadFile;
        $spreadsheetAutoloadLoaded = true;
        break;
    }
}

if (!$spreadsheetAutoloadLoaded) {
    throw new RuntimeException('No se encontro autoload de PhpSpreadsheet en rutas conocidas.');
}
