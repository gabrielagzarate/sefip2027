<?php
session_start();
require_once dirname(__FILE__).'/../../lib/constantes.php';
$fichero = "";
$tipo = "";
$disposition = "";

if(isset($_GET['file']) ){
    if (isset($_GET['tmp']) && $_GET['tmp']==0) 
        $fichero = $_GET['file'];
    else
        $fichero = '../tmpdoctos/'.$_GET['file'];
	$tipo ="x-pdf";
}else{
	$fichero = '../tmpdoctos/acceso_negado.txt';
	$tipo ="plain-text";
}


if( isset($_GET['t']) AND $_GET['t'] =="v" ){
	$disposition = 'inline;filename='.basename($fichero);
}else{
	$disposition = 'attachment; filename='.basename($fichero);
}


if(isset($_SESSION[_S])){
		if( isset($_GET['t']) AND $_GET['t'] =="d" ){
			header('Content-Description: File Transfer');
			header('Content-Type: application/'.$tipo );
			header('Content-Disposition:'.$disposition);
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($fichero));
		}else{
			header('Content-Type: application/pdf');
		}
		echo file_get_contents($fichero);
		if (isset($_GET['file']) && !isset($_GET['tmp'])){
			unlink($fichero);
		}
		exit;
}else{

	echo("Acceso Negado");
	unlink($fichero);
}

?>

