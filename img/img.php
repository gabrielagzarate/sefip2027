<?php
session_start();
require_once dirname(__FILE__).'/../lib/constantes.php';
$file = $_GET['img'];
$dst = '';

if($file =="")
    $dst = "./scp/delete.png";
else
    $dst = "./scp/".$file.".png";

if(isset($_SESSION[_S])){ 
    header('Content-Type: image/png');
    if(file_exists($dst)){
        echo file_get_contents($dst);
    } else
        echo file_get_contents("./scp/delete.png");
}	
?>

