<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . "/soap/nusoap.php";

function ws_lineaC($linea) {
    $ur_web_service = 'https://wssiox.finanzasoaxaca.gob.mx/services/Tesoreria?wsdl';
    $cliente = new nusoap_client($ur_web_service);
    $resultado = $cliente->call('consulta_linea_captura', array("linea_captura" => $linea));
    return $resultado;
}

function ws_Folio($linea) {
  $ur_web_service = 'https://wssiox.finanzasoaxaca.gob.mx/services/Tesoreria?wsdl';
		$cliente = new nusoap_client($ur_web_service);
        $resultado = $cliente->call('consulta_linea', array("no_documento"=>$linea));
		return $resultado;
}

function ws_Vh($linea) {
    $ur_web_service = 'https://wssiox.finanzasoaxaca.gob.mx/services/Tesoreria?wsdl';
    $cliente = new nusoap_client($ur_web_service);
    $resultado = $cliente->call('consulta_linea_captura', array("linea_captura" => $linea));
    return $resultado;
}

function ws_Vh_folio($linea) {
    $ur_web_service = 'https://wssiox.finanzasoaxaca.gob.mx/services/Tesoreria?wsdl';
    $cliente = new nusoap_client($ur_web_service);
    $resultado = $cliente->call('consulta_linea_captura', array("linea_captura" => $linea));
    return $resultado;
}

function ws_Consulta_Linea($linea,$tipo) {
    $ur_web_service = 'https://wssiox.finanzasoaxaca.gob.mx/services/Tesoreria?wsdl';
    $cliente = new nusoap_client($ur_web_service);
    $resultado = $cliente->call('consulta_linea_captura',  array("linea_captura" => $linea,"tipo_consulta"=>$tipo,"id_banco"=>4));
    return $resultado;
}
