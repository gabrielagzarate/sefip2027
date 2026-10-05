<?php
session_start();

//librerias
require_once dirname(__FILE__).'/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__).'/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__).'/../../../../ent/scg/ldf/EntePublicoLDF.php';
require_once dirname(__FILE__).'/../../../../lib/redondear.php';
require_once dirname(__FILE__).'/../../../../lib/MesNombre.php';
require_once dirname(__FILE__).'/../../../../lib/compararCadenas.php';
require_once dirname(__FILE__).'/../../../../lib/TruncateCadena.php';
//require_once dirname(__FILE__).'/../../../../lib/scg/ldf/lib_analiticoDeuda.php';
require_once dirname(__FILE__).'/../../../lib/generaPhpspreadsheet.php';

//echo "<pre>";print_r($_POST);echo "</pre>";//die();//*
//echo "<pre>";print_r($_SESSION);echo "</pre>";die();//*
// Armamos los parámetros para pedir correctamente la balanza
$ur = new UnidadResponsable();
$ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
$ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
$ur->select();

if(isset($_SESSION[_S]['clasAdm-ue'])){
    $ue = new UnidadEjecutora( );
    $ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
    $ue->setUr_id($_SESSION[_S]['clasAdm-ur']);
    $ue->setUe_id($_SESSION[_S]['clasAdm-ue']);
    $ue->select();
}elseif(isset($_POST['ue']) && trim($_POST['ue']) != ''){
    $ue = new UnidadEjecutora( );
    $ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
    $ue->setUr_id($_SESSION[_S]['clasAdm-ur']);
    $ue->setUe_id(trim($_POST['ue']));
    $ue->select();
}else{
    $ue = FALSE;
}

$ue_id = ($ue ? $ue->getUe_id():FALSE);

$sis_id = (isset($_POST['sis_id']) ? $_POST['sis_id']:'');
$men_id = (isset($_POST['men_id']) ? $_POST['men_id']:'');
$opc_id = (isset($_POST['opc_id']) ? $_POST['opc_id']:'');
$msc_trimestre = (isset($_POST['msc_trimestre']) ? $_POST['msc_trimestre']:'');
$gpo_id = (isset($_POST['gpo_id']) ? $_POST['gpo_id']:'');
$ur_id = (isset($_POST['ur_id']) ? $_POST['ur_id']:'');
$consolidado = (isset($_POST['consolidado']) ? $_POST['consolidado']:false);
$noAplica = (isset($_POST['noAplica']) ? $_POST['noAplica']:false);
$ent_id = (isset($_POST['ent_id']) ? $_POST['ent_id']:0);
$ent_idC = (isset($_POST['ent_id']) ? $_POST['ent_id']:'');
$sec_idC = (isset($_POST['sec_id']) ? $_POST['sec_id']:'');
$gpo_idC = (isset($_POST['gpo_id']) ? $_POST['gpo_id']:'');
$con_idC = (isset($_POST['con_id']) ? $_POST['con_id']:'');

if($ent_id == 0 && !$consolidado){
    echo "NO TIENE ENTIDAD ASIGNADO";
    DIE();
}

$encabLin2 = '';
if($consolidado){
    $encabLin1 = "GOBIERNO ESTATAL CONSOLIDADO";
    if($ent_idC <> ''){
        $entePub = new EntePublicoLDF();
        $entePub->setEnt_id($ent_idC);
        $entePub->select();
        $sql = "
            SELECT gpo_id, ur_id
            FROM ldf.vin_EntePublicoLDF
            WHERE ejr_id = 2020
                AND ent_id = ".$ent_idC."
                AND vin_generaReporte = 1
            LIMIT 1
        ";

        $consultaEnt = new AccesoDatos();
        $consultaEnt->conectar();
        $consultaEnt->setSql($sql);
        $consultaEnt->consultar();
        $consultaEnt->desconectar();
        if($consultaEnt->getNumeroRegistros() > 0){
            $row = $consultaEnt->extraer();
            $ur_claveC = $row['gpo_id'].$row['ur_id'];
        }
        $encabLin2 = $entePub->getEncabezado();
    }
    if($sec_idC <> ''){
        if($sec_idC == 'P'){
            $encabLin2 = "SECTOR PARAESTATAL";
        }elseif($sec_idC == 'C'){
            $encabLin2 = "SECTOR CENTRAL";
        }
    }
    if($gpo_idC <> ''){
        switch($gpo_idC){
            case 1:
                $encabLin2 = "CONSOLIDADO PODER EJECUTIVO SIN INCLUIR PARAESTATALES";
                break;
            case 2:
                $encabLin2 = "CONSOLIDADO PODER LEGISLATIVO";
                break;
            case 3:
                $encabLin2 = "CONSOLIDADO PODER JUDICIAL";
                break;
            case 4:
                $encabLin2 = "CONSOLIDADO ÓRGANOS AUTÓNOMOS";
                break;
            case 5:
                $encabLin2 = "CONSOLIDADO ORGANISMOS PÚBLICOS DESCENTRALIZADOS";
                break;
            case 6:
                $encabLin2 = "CONSOLIDADO FIDEICOMISOS PÚBLICOS";
                break;
            case 7:
                $encabLin2 = "CONSOLIDADO EMPRESAS DE PARTICIPACIÓN ESTATAL";
                break;
            case 8:
                $encabLin2 = "CONSOLIDADO EMPRESAS DE PARTICIPACIÓN ESTATAL";
                break;
        }
    }
}else{
    $entePub = new EntePublicoLDF();
    $entePub->setEnt_id($ent_id);
    $entePub->select();
    $encabLin1 = $entePub->getEncabezado();
}

$ejercicio = "2020";
$encabLin3 = "Nombre del ente público";




$encabLin5 = "(PESOS) ";
$datos = lib_situacionFinanciera($ent_id, $msc_trimestre, $consolidado, $ent_idC, $sec_idC, $gpo_idC, $con_idC, $noAplica);
$guardararchivo = false;
//}
//break;
//}
//echo '<pre>';print_r($datos);echo '</pre>';die();
// Valores para el archivo de exportación
//$anchoColumnas = array(50, 20, 20, 50, 20, 20);
/*
$nombreColumnas = array("Concepto", $encabmes, "31 de Diciembre de 2018", "Concepto", $encabmes, "31 de Diciembre de 2018");
$incluirColumnas = array("nombreIzquierda", "importe2019Izquierda", "importe2018Izquierda", "nombreDerecha", "importe2019Derecha", "importe2018Derecha");
*/
$nombreColumnas = array("Concepto", $encabmes, "1 de enero de 2020", "Concepto", $encabmes, "1 de enero de 2020");
$incluirColumnas = array("nombreIzquierda", "importe2020Izquierda", "importe2019Izquierda", "nombreDerecha", "importe2020Derecha", "importe2019Derecha");
$tipoColumnas = array("c", "n", "n", "c", "n", "n");
$anchoColumnas = array(50, 23, 23, 50, 23, 23);

$table = array();
foreach ($incluirColumnas as $key => $val) {
    $table['nombre'][$val] = $nombreColumnas[$key];
    $table['ancho'][$val] = $anchoColumnas[$key];
    $table['campo'][$val] = $key;
    $table['tipo'][$val] = $tipoColumnas[$key];
}
$table['tipoTabla'] = "M";
$table['encabezado'] = 'S';
$table['color'] = 0;

$tablaimg['imagen'] = 1;
$tablaimg[0]['nombre'] = "Logo";
if($consolidado){
    if($ent_idC <> ''){
        if($ent_id == 1){
            $tablaimg[0]['ruta'] = "../../../../img/logosefin-color-fblancoOriginal.jpg";
            $tablaimg[0]['colImg'] = "D1";
        }else{
            $tablaimg[0]['ruta'] = "../../../../img/Logos Institucionales/".$ur_claveC.".jpg";
            $tablaimg[0]['colImg'] = "D1";
        }

        $tablaimg[0]['altura'] = "75";
        $tablaimg[0]['alingXimg'] = "290";

        $tablaimg[0]['rotacion'] = "0";
    }else{
        $tablaimg[0]['ruta'] = "../../../../img/logosefin-color-fblancoOriginal.jpg";
        $tablaimg[0]['colImg'] = "D1";
        $tablaimg[0]['altura'] = "75";
        $tablaimg[0]['alingXimg'] = "290";
        $tablaimg[0]['rotacion'] = "0";
    }
}else{
    if($ent_id == 1){
        $tablaimg[0]['ruta'] = "../../../../img/logosefin-color-fblancoOriginal.jpg";
    }else{
        $tablaimg[0]['ruta'] = "../../../../img/Logos Institucionales/".$ur->getClave().".jpg";
    }
    $tablaimg[0]['colImg'] = "D1";
    $tablaimg[0]['altura'] = "75";
    $tablaimg[0]['alingXimg'] = "290";
    $tablaimg[0]['rotacion'] = "0";
}

if (!is_file($tablaimg[0]['ruta'])) {
    //$tablaimg['imagen'] = 0;
    unset($tablaimg[0]);
    // die();
}

// Armamos los parametros opcionales
$rutalog = '/../../scg/formatosLDF/LDF/'.$gpo_id.str_pad($ur_id, 2, "0", STR_PAD_LEFT).'/TRIMESTRE'.$msc_trimestre.'/';

$encabezado = array('encabLin1' => $encabLin1, 'encabLin2' => $encabLin2, 'encabLin3' => $encabLin3, 'encabLin4' => $encabLin4, 'encabLin5' => $encabLin5
   /* , 'alineacionenc' => 'I' */);

$grupoUR = $ur->getGpo_id().str_pad($ur->getUr_id(), 2, "0", STR_PAD_LEFT);

if($consolidado){
    $grupoUR = 115;
    $guardararchivo = true;
    $nombre_Comp = "";

    if($ent_idC != ""){
        $nombre_Comp = "N2E".$ent_idC;
    }
    if($gpo_idC != ""){
        $nombre_Comp = "N1G".$gpo_idC;
    }
    if($sec_idC != ""){
        $nombre_Comp = "N3S".$sec_idC;
    }
    if($con_idC != ""){
        $nombre_Comp = "N4G".$con_idC;
    }
    $otrosParametros = array('comprimir' => false, 'nombre' => "ConsolidadoEstadoSituacionFinanciera", 'sufijoNombre' => $nombre_Comp."T".$msc_trimestre, 'color' => 0, 'tablaimg' => $tablaimg
        //'img'=>"../../../../img/Logos Institucionales/".$ur->getClave().".jpg",'colImg'=>"D1",'alingXimg'=>"470"
        // 'imgagua'=>"../../../../img/Logos Institucionales/".$ur->getClave().".jpg",'colImgagua'=>"A33",'alingXimgagua'=>"470","imgaguarota"=>-25,"imgaguaTamaño"=>300
    );
    //$rutalog = '../../../../scg/formatosLDF/LDF/consolidado'.$gpo_id.str_pad($ur_id,
    //      2, "0", STR_PAD_LEFT).'/TRIMESTRE'.$msc_trimestre.'/';
    $guardar = array('guardar' => $guardararchivo, 'modulo' => "scg", 'claveunica' => "ldf/consolidados", 'claveUr' => $grupoUR, '$claveunica2' => "T".$msc_trimestre, 'tipoguardado' => 2,
        //'ruta' => $rutalog
    );
}else{
    if($noAplica){
        $tablaimg[1]['nombre'] = "no aplica";
        $tablaimg[1]['ruta'] = "../../../../img/NoAplica.png";
        $tablaimg[1]['colImg'] = "A15";
        $tablaimg[1]['altura'] = "1900";
        $tablaimg[1]['alingXimg'] = "0";
        $tablaimg[1]['rotacion'] = "0";
    }
    $guardararchivo = true;
    $otrosParametros = array('comprimir' => false, 'nombre' => "EstadoSituacionFinanciera", 'sufijoNombre' => "E".$ent_id."T".$msc_trimestre, 'color' => 0, 'tablaimg' => $tablaimg, 'libro' => true
        // 'imgagua'=>"../../../../img/Logos Institucionales/".$ur->getClave().".jpg",'colImgagua'=>"A33",'alingXimgagua'=>"470","imgaguarota"=>-25,"imgaguaTamaño"=>300
    );
    $guardar = array('guardar' => $guardararchivo, 'modulo' => "scg", 'claveunica' => "ldf", 'claveUr' => $grupoUR, '$claveunica2' => "T".$msc_trimestre, 'tipoguardado' => 2,
        //'ruta' => $rutalog
    );
}

//die();
//echo '<pre>';print_r($datos);echo '</pre>';die();
$excel = new generaPhpspreadsheet();
//$reporte =$excel->generaExcel($datos, $encabezado, true, $table, $otrosParametros);
$excel->generaExcel($datos, $encabezado, $table, $otrosParametros, false, false); //$guardar

?>


