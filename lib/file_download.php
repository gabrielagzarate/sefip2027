<?php
class filedownload
{
    public function __construct($r,$type_Disposition)
    {
        $ruta = __DIR__."/../tmps/".$r;
        echo $this->download($ruta, $type_Disposition);
        if ( file_exists($ruta)) {
            unlink($ruta);
        }
    }
    public function download($fichero, $type_Disposition)
    {
        if ( file_exists($fichero)) {
            $tipo = mime_content_type  ($fichero);

            $disposition = $type_Disposition . ';filename='.basename($fichero);            
            
            header('Content-Description: File Transfer');
            header('Content-Type:'.$tipo );
            header('Content-Disposition:'.$disposition);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($fichero));
            return  file_get_contents($fichero);
            
        }else {
            return  "fichero no existe";
        };
    }
}
/* LLAMADA PRINCIPAL */
if (isset($_GET['r'])){
    $r = $_GET['r'];
    $type_Disposition = !isset($_GET['type_Disposition'])?'inline':$_GET['type_Disposition'];
    $m = new filedownload($r,$type_Disposition);
} else {
    echo "vacio";
}
?>