<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Automatico
 *
 * @author Faustino Cruz, Israel <israel.faustino@finanzasoaxaca.gob.mx>
 * Created: 09-may-2019
 * Comment: 
 * 
 * */
require_once dirname(__FILE__) . '/pdf_Automatico.php';

$pdf = new pdf_Automatico('L', 'mm', 'letter');
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->SetProtection(array('print', 'annot-forms'), "", PDF_PASSWORD_OWNER_SCG);

$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 40);
$pdf->setAltoLinea(1.5);
$pdf->AddPage();

switch ($pdf->men_id) {
    case 14:
        switch ($pdf->rep_id) {
            case 1:
                $datos = lib_situacionFinanciera(
                    $pdf->ent_id,
                    $pdf->msc_trimestre,
                    $pdf->consolidado,
                    $pdf->ent_idC,
                    $pdf->sec_idC,
                    $pdf->gpo_idC,
                    $pdf->con_idC,
                    $pdf->noAplica
                );
                break;
            case 6:
                $datos = lib_analiticoPresupuesto6A(
                    $pdf->ent_id,
                    $pdf->msc_trimestre,
                    $pdf->consolidado,
                    $pdf->ent_idC,
                    $pdf->sec_idC,
                    $pdf->gpo_idC,
                    $pdf->con_idC,
                    $pdf->noAplica
                );
                break; //*/
            case 7:
                $datos = lib_analiticoPresupuesto6B(
                    $pdf->ent_id,
                    $pdf->msc_trimestre,
                    $pdf->consolidado,
                    $pdf->ent_idC,
                    $pdf->sec_idC,
                    $pdf->gpo_idC,
                    $pdf->con_idC,
                    $pdf->noAplica
                );
                break;
            case 8:
                $datos = lib_analiticoPresupuesto6C(
                    $pdf->ent_id,
                    $pdf->msc_trimestre,
                    $pdf->consolidado,
                    $pdf->ent_idC,
                    $pdf->sec_idC,
                    $pdf->gpo_idC,
                    $pdf->con_idC,
                    $pdf->noAplica
                );
                break;
            case 9:
                $datos = lib_analiticoPresupuesto6D(
                    $pdf->ent_id,
                    $pdf->msc_trimestre,
                    $pdf->consolidado,
                    $pdf->ent_idC,
                    $pdf->sec_idC,
                    $pdf->gpo_idC,
                    $pdf->con_idC,
                    $pdf->noAplica
                );
                break;
            default:
                break;
        }
        break;
    default:
        break;
}
/* echo 'D<pre>';
print_r($datos);
echo '</pre>';
die();
 */
$pdf->Content($datos);
/*
$ruta = "ldf/";
$ur_clave = $_SESSION[_S]['s19-gpo'].str_pad($_SESSION[_S]['s19-ur'], 2, "0", STR_PAD_LEFT);
switch($pdf->rep_id){
    case 1:
        $nombre_archivo = 'EstadoSituacionFinancieraE'.$pdf->ent_id.'T'.$pdf->msc_trimestre.'.pdf';
        break;
    case 6:
        $nombre_archivo = 'EstadoAnalitico6AE'.$pdf->ent_id.'T'.$pdf->msc_trimestre.'.pdf';
        break;
    case 7:
        $nombre_archivo = 'EstadoAnalitico6BE'.$pdf->ent_id.'T'.$pdf->msc_trimestre.'.pdf';
        break;
    case 8:
        $nombre_archivo = 'EstadoAnalitico6CE'.$pdf->ent_id.'T'.$pdf->msc_trimestre.'.pdf';
        break;
}

if($pdf->consolidado){
    $nombre_Comp = "";
    if ($pdf->ent_idC != "") {
        $nombre_Comp = "N2E".$pdf->ent_idC;
    }
    if ($pdf->gpo_idC != "") {
        $nombre_Comp = "N1G".$pdf->gpo_idC;
    }
    if ($pdf->sec_idC != "") {
        $nombre_Comp = "N3S".$pdf->sec_idC;
    }
    if ($pdf->con_idC != "") {
        $nombre_Comp = "N4G".$pdf->con_idC;
    }
    
    $ruta = "ldf/consolidados/";
    $ur_clave = 115;
    switch($pdf->rep_id){
        case 1:
            $nombre_archivo = 'ConsolidadoEstadoSituacionFinanciera'.$nombre_Comp.'T'.$pdf->msc_trimestre.'.pdf';
            break;
        case 6:
            $nombre_archivo = 'ConsolidadoEstadoAnalitico6A'.$nombre_Comp.'T'.$pdf->msc_trimestre.'.pdf';
            break;
        case 7:
            $nombre_archivo = 'ConsolidadoEstadoAnalitico6B'.$nombre_Comp.'T'.$pdf->msc_trimestre.'.pdf';
            break;
        case 8:
            $nombre_archivo = 'ConsolidadoEstadoAnalitico6C'.$nombre_Comp.'T'.$pdf->msc_trimestre.'.pdf';
            break;
    }
}

/*$rutatem = dirname(__FILE__)."/../../../../archivostemporalesPHPpreadsheet/";
$exists = is_dir($rutatem);
if (!$exists) {
    mkdir($rutatem, 0777, true);
}

$pdf->Output($rutatem.$nombre_archivo, 'F');

$up = new FicheroSefip();
$up->setTipos(array("pdf"));
$up->setModulo("scg");
$up->setfile("$nombre_archivo");
$up->setRuta($ruta."T".$pdf->msc_trimestre);
$up->setUrFichero($ur_clave);
$up->setAnioFichero(2019);
$file = array("name" => $rutatem.$nombre_archivo, "tmp_name" => $rutatem.$nombre_archivo, "eslocal" => true);

if(is_file($rutatem.$nombre_archivo)){
    $up->setFichero($file);
    
    if($up->upload()){
        unlink($rutatem.$nombre_archivo);
    }
}
*/
$pdf->Output();
