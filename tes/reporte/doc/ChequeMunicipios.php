<?php

date_default_timezone_set('America/Mexico_City');
define('FPDF_FONTPATH', '../../../rep/fnt/');
require '../../../rep/lib/fpdf.1.8.php';
include '../../../lib/tes/cheques/fechas_importes.php';
include "../../config/cheques.php";
include "../../../lib/helpers.php";
header("Content-Type: text/html; charset=utf-8 ");
$orden = $_POST["orden"];
$consec = $_POST["consec"];
$importe = $_POST["importe"];
$concepto = substr($_POST["concepto"], 0, 50);
$cta_cargo = $_POST["cuenta"];

$benef = $_POST["benef"];

$lineas = array();
$l_benef = array();

$fecha = strtoupper(mi_fecha($config["fecha_sis_uno"]));
$fecha_c = strtoupper(mi_fecha_com($config["fecha_sis_dos"]));

$bancos["072"] = "banorte";
$bancos["044"] = "scotia";
$bancos["021"] = "hsbc";
$bancos["002"] = "banamex";
$bancos["132"] = "multiva";
$bancos["037"] = "interacciones";
$bancos["012"] = "bancomer";
$bancos["014"] = "santander";
$bancos["127"] = "azteca";

$banc = substr($cta_cargo, 0, 3);
$cuenta = substr($cta_cargo, 6, 11);

$lineas = separarConcepto($concepto, 75);

$l_benef = separarConcepto($benef, 48);

$pdf = new FPDF();

$pdf->AddPage("P", "cheque");
$pdf->SetFont('Arial', 'B', 8);
//*********************** POLIZA ***********************************
if ($banc == "012") {
    $pdf->Text(140, 24, $fecha);
} else {
    $pdf->Text(130, 24, $fecha);
}

$l_benef = separarConcepto2($benef, 48);
$posicionY = 46;
foreach ($l_benef as $key => $value) {
    $posicionY -= 5;
}
foreach ($l_benef as $key => $value) {
    $pdf->Text(38, $posicionY, utf8Decode($value));
    $posicionY += 5;
}
$pdf->SetFont('Arial', 'B', 9);
$pdf->Text(160, 43, number_format($importe, 2));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Text(25, 50, strtoupper(num2letras(str_replace(",", "", $importe))));

//CONCEPTO POLIZA
$pos_y = 105;
foreach ($lineas as $key => $value) {
    $pdf->Text(21, $pos_y, utf8Decode($value));
    $pos_y = $pos_y + 5;
}

//FILA
$orden = str_pad($orden, 30, ' ', STR_PAD_LEFT);
$pdf->Text(16, 127, $orden);

$pos_y = 127;
foreach ($l_benef as $key => $value) {
    $pdf->Text(64, $pos_y, utf8Decode($value));
    $pos_y = $pos_y + 5;
}

$pdf->Text(160, 127, number_format($importe, 2));

//DATOS BANCO
$pdf->Text(70, 166, strtoupper($bancos[$banc]) . " " . $cuenta);
$pdf->Text(70, 171, "NUMERO DE CHEQUE: " . $consec);
$pdf->Text(70, 176, $fecha_c);

$pdf->Output();

function separarConcepto($concepto, $lmax)
{
    $largo = strlen($concepto);
    $linea = array();
    if ($largo > $lmax) {
        $renglones = 0;
        $renglones = ceil($largo / $lmax);
        $tope = $lmax;
        $inicio = 0;
        for ($i = 0; $i < $renglones; $i++) {
            $atras = $largo - $tope;
            $limite = substr($concepto, $tope, 1);
            if ($limite == " ") {
                $linea[$i] = substr($concepto, $inicio, $lmax);
                $inicio = $tope + 1;
            } else {
                $last_esp = strrpos($concepto, " ", -$atras);
                $long = $last_esp - $inicio;
                $linea[$i] = substr($concepto, $inicio, $long);
                $inicio = $last_esp + 1;
            }

            $tope = $inicio + $lmax;
        }
    } else {
        $linea[0] = $concepto;
    }
    return $linea;
}
function separarConcepto2($concepto, $lmax)
{
    $linea = array();
    $tok = strtok($concepto, " ");
    $lineaBack = "";
    $lineaUp = "";
    while ($tok !== false) {
        $lineaBack .= $tok . " ";
        $tok = strtok(" ");
        $lineaUp = isset($tok) ? $tok : "";
        if (strlen($lineaBack . $lineaUp) > $lmax) {
            array_push($linea, $lineaBack);
            $lineaBack = "";
            $lineaUp = "";
        }
    }
    if (strlen($lineaBack) > 0) {
        array_push($linea, $lineaBack);
    }
    return $linea;
}
