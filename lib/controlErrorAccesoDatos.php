<?php
require_once dirname(__FILE__)."/constantes.php";

function creaXmlError($errno,$error,$consulta){
    $xml='<?xml version="1.0" encoding="utf8"?><mySqlError>';
    $xml.='<error>'.$error.'</error>';
    $xml.='<errno>'.$errno.'</errno>';
    $xml.='<consulta>'.$consulta.'</consulta>';
    
    if(strpos($error, 'mensual') == true) $xml.='<mensaje>El importe excede el presupuesto Mensual</mensaje>';
    else if(strpos($error, 'vigente') == true) $xml.='<mensaje>El importe excede el presupuesto Vigente</mensaje>';
    
    $xml.='</mySqlError>';
    return $xml;
}