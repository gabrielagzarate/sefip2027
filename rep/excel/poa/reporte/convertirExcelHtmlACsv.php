<?php
/**
 * Description of convertirExcelHtmlACsv
 * Created: Feb-2026
 * Objective: Convertir el REPORTE DE AVANCE DE GESTION a CSV
 * El contenido del reporte es HTML
 * */
function convertirExcelHtmlACsv($inputFileName)
{
    $html = $inputFileName;

    $encoding = mb_detect_encoding($html, ['UTF-8','ISO-8859-1','Windows-1252'], true);

    if ($encoding !== 'UTF-8') {
        $html = mb_convert_encoding($html, 'UTF-8', $encoding ?: 'ISO-8859-1');
    }

    $dom = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();

    $rows = $dom->getElementsByTagName('tr');

    $numeroColumnas = 0;

    foreach ($rows as $row) {
        $cells = $row->getElementsByTagName('td');
        $numeroColumnas = max($numeroColumnas, $cells->length);
    }

    $csvContent = "\xEF\xBB\xBF";

    foreach ($rows as $row) {

        $cells = $row->getElementsByTagName('td');
        $data = [];

        foreach ($cells as $cell) {

            $texto = trim($cell->textContent);
            $texto = str_replace("\xc2\xa0", '', $texto);
            $texto = str_replace(["\r", "\n"], ' ', $texto);

            $numeroPrueba = str_replace(',', '', $texto);

            if (is_numeric($numeroPrueba)) {

                $texto = $numeroPrueba;

                $numero = floatval($texto);
                if ($numero == intval($numero)) {
                    $texto = intval($numero);
                }

            } else {

                if (strpos($texto, ',') !== false) {
                    $texto = str_replace('"', '""', $texto);
                    $texto = '"' . $texto . '"';
                }
            }

            $data[] = $texto;
        }

        if (count($data) < $numeroColumnas) {
            $data = array_pad($data, $numeroColumnas, '');
        }

        $noVacios = array_filter($data, fn($v) => $v !== '');

        if (empty($noVacios)) {
            $csvContent .= str_repeat(',', $numeroColumnas - 1) . "\r\n";
        } else {
            $csvContent .= implode(',', $data) . "\r\n";
        }
    }

    return $csvContent;
}
?>