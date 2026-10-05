<?php

/**
 * Carga de TCPDF compatible con PHP 8.x.
 *
 * Prioriza el paquete Composer tecnickcom/tcpdf (autoload del proyecto).
 * Si no está instalado, no define la clase; el caller puede usar el renderer legacy.
 */
if (class_exists('TCPDF', false)) {
    return;
}

$projectRoot = dirname(__DIR__);

$autoloadCandidates = [
    $projectRoot . '/vendor/autoload.php',
    $projectRoot . '/rep/lib/vendor/autoload.php',
];

foreach ($autoloadCandidates as $autoloadFile) {
    if (is_file($autoloadFile)) {
        require_once $autoloadFile;
        if (class_exists('TCPDF', false)) {
            return;
        }
    }
}
