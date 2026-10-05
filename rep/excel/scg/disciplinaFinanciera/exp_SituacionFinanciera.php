<?php

session_start();

//librerias
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../../../ent/scg/ldf/EntePublicoLDF.php';
require_once dirname(__FILE__) . '/../../../../lib/redondear.php';
require_once dirname(__FILE__) . '/../../../../lib/MesNombre.php';
require_once dirname(__FILE__) . '/../../../../lib/compararCadenas.php';
require_once dirname(__FILE__) . '/../../../../lib/TruncateCadena.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/disciplinaFinanciera/lib_situacionFinanciera.php';
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';

// Armamos los parámetros para pedir correctamente la balanza
$ur = new UnidadResponsable();
$ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
$ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
$ur->select();

$ejercicioActual = $_SESSION[_S]['ejercicioActual'];
$ejercicioAnterior = $ejercicioActual - 1;

if (isset($_SESSION[_S]['clasAdm-ue'])) {
    $ue = new UnidadEjecutora();
    $ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
    $ue->setUr_id($_SESSION[_S]['clasAdm-ur']);
    $ue->setUe_id($_SESSION[_S]['clasAdm-ue']);
    $ue->select();
} elseif (isset($_POST['ue']) && trim($_POST['ue']) != '') {
    $ue = new UnidadEjecutora();
    $ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
    $ue->setUr_id($_SESSION[_S]['clasAdm-ur']);
    $ue->setUe_id(trim($_POST['ue']));
    $ue->select();
} else {
    $ue = false;
}

$msc_trimestre = (isset($_POST['msc_trimestre']) ? $_POST['msc_trimestre'] : '');
$gpo_id = (isset($_POST['gpo_id']) ? $_POST['gpo_id'] : '');
$ur_id = (isset($_POST['ur_id']) ? $_POST['ur_id'] : '');
$consolidado = (isset($_POST['consolidado']) ? $_POST['consolidado'] : false);
$ent_id = (isset($_POST['ent_id']) ? $_POST['ent_id'] : 0);
$ent_idC = (isset($_POST['ent_id']) ? $_POST['ent_id'] : '');
$sec_idC = (isset($_POST['sec_id']) ? $_POST['sec_id'] : '');
$gpo_idC = (isset($_POST['gpo_id']) ? $_POST['gpo_id'] : '');
$con_idC = (isset($_POST['con_id']) ? $_POST['con_id'] : '');

if ($ent_id == 0 && !$consolidado) {
    echo "NO TIENE ENTIDAD ASIGNADO";
    die();
}

$encabLin2 = '';
if ($consolidado) {
    $encabLin1 = "GOBIERNO ESTATAL CONSOLIDADO";
    if ($ent_idC <> '') {
        $entePub = new EntePublicoLDF();
        $entePub->setEnt_id($ent_idC);
        $entePub->select();
        $sql = "
            SELECT gpo_id, ur_id
            FROM ldf.vin_EntePublicoLDF
            WHERE ejr_id = $ejercicioActual
                AND ent_id = $ent_idC
                AND vin_generaReporte = 1
            LIMIT 1;
        ";

        $consultaEnt = new AccesoDatos();
        $consultaEnt->conectar();
        $consultaEnt->setSql($sql);
        $consultaEnt->consultar();
        $consultaEnt->desconectar();
        if ($consultaEnt->getNumeroRegistros() > 0) {
            $row = $consultaEnt->extraer();
            $ur_claveC = $row['gpo_id'] . $row['ur_id'];
        }
        $encabLin2 = $entePub->getEncabezado();
    }
    if ($sec_idC <> '') {
        if ($sec_idC == 'P') {
            $encabLin2 = "SECTOR PARAESTATAL";
        } elseif ($sec_idC == 'C') {
            $encabLin2 = "SECTOR CENTRAL";
        }
    }
    if ($gpo_idC <> '') {
        switch ($gpo_idC) {
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
} else {
    $entePub = new EntePublicoLDF();
    $entePub->setEnt_id($ent_id);
    $entePub->select();
    $encabLin1 = $entePub->getEncabezado();
}

$encabLin3 = "Estado de Situación Financiera Detallado - LDF";
switch ($msc_trimestre) {
    case 1:
        $encabLin4 = "Al 31 de diciembre de $ejercicioAnterior y al 31 de marzo de $ejercicioActual";
        $corte = "Al 31 de Marzo de " .$ejercicioActual;
        break;
    case 2:
        $encabLin4 = "Al 31 de diciembre de $ejercicioAnterior y al 30 de junio de $ejercicioActual";
        $corte = "Al 30 de Junio de " .$ejercicioActual;
        break;
    case 3:
        $encabLin4 = "Al 31 de diciembre de $ejercicioAnterior y al 30 de septiembre de $ejercicioActual";
        $corte = "Al 30 de Septiembre de " .$ejercicioActual;
        break;
    case 4:
        $encabLin4 = "Al 31 de diciembre de $ejercicioAnterior y al 31 de diciembre de $ejercicioActual";
        $corte = "Al 31 de Diciembre de " .$ejercicioActual;
        break;
}

if (empty($_POST['ckmiles'])) {
    $encabLin5 = '(Cifras en Pesos)';
} else {            
    $encabLin5 = '(Cifras en Miles de Pesos)';
}

$datos = lib_situacionFinanciera($ent_id, $msc_trimestre, $consolidado, $ent_idC, $sec_idC, $gpo_idC, $con_idC,$parametrosExtra=array("tipoReporte" => $_POST['tipoReporte'], "ue" => $_POST['ue'], "grupos" => $_POST['grupos'], "excluir" => $_POST['excluir']));
$guardararchivo = false;

// Valores para el archivo de exportación
$nombreColumnas = array("Concepto", $corte, "31 de diciembre de $ejercicioAnterior", "Concepto", $corte, "31 de diciembre de $ejercicioAnterior");
$incluirColumnas = array("nombreIzquierda", "importeActualIzquierda", "importeAnteriorIzquierda", "nombreDerecha", "importeActualDerecha", "importeAnteriorDerecha");
$tipoColumnas = array("c", "n", "n", "c", "n", "n");
$anchoColumnas = array(50, 23, 28, 50, 23, 28);

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

if ($consolidado) {
    if ($ent_idC !== '') {
        if ($ent_id === 1) {
            $tablaimg[0]['ruta'] = "../../../../img/logosefin-color-fblancoOriginal.jpg";
            $tablaimg[0]['colImg'] = "D1";
        } else {
            $tablaimg[0]['ruta'] = "../../../../img/Logos Institucionales/" . $ur_claveC . ".jpg";
            $tablaimg[0]['colImg'] = "D1";
        }
        $tablaimg[0]['altura'] = "75";
        $tablaimg[0]['alingXimg'] = "290";
        $tablaimg[0]['rotacion'] = "0";
    } else {
        $tablaimg[0]['ruta'] = "../../../../img/logosefin-color-fblancoOriginal.jpg";
        $tablaimg[0]['colImg'] = "D1";
        $tablaimg[0]['altura'] = "75";
        $tablaimg[0]['alingXimg'] = "290";
        $tablaimg[0]['rotacion'] = "0";
    }
} else {
    if ($ent_id === 1) {
        $tablaimg[0]['ruta'] = "../../../../img/logosefin-color-fblancoOriginal.jpg";
    } else {
        $tablaimg[0]['ruta'] = "../../../../img/Logos Institucionales/" . $ur->getClave() . ".jpg";
    }
    $tablaimg[0]['colImg'] = "D1";
    $tablaimg[0]['altura'] = "75";
    $tablaimg[0]['alingXimg'] = "290";
    $tablaimg[0]['rotacion'] = "0";
}

if (!is_file($tablaimg[0]['ruta'])) {
    unset($tablaimg[0]);
}

// Armamos los parametros opcionales
$rutalog = '/../../scg/formatosLDF/LDF/' . $gpo_id . str_pad($ur_id, 2, "0", STR_PAD_LEFT) . '/TRIMESTRE' . $msc_trimestre . '/';

$encabezado = array('encabLin1' => $encabLin1, 'encabLin2' => $encabLin2, 'encabLin3' => $encabLin3, 'encabLin4' => $encabLin4, 'encabLin5' => $encabLin5);

$grupoUR = $ur->getGpo_id() . str_pad($ur->getUr_id(), 2, "0", STR_PAD_LEFT);

if ($consolidado) {
    $grupoUR = 115;
    $guardararchivo = true;
    $nombre_Comp = "";

    if ($ent_idC != "") {
        $nombre_Comp = "N2E" . $ent_idC;
    }
    if ($gpo_idC != "") {
        $nombre_Comp = "N1G" . $gpo_idC;
    }
    if ($sec_idC != "") {
        $nombre_Comp = "N3S" . $sec_idC;
    }
    if ($con_idC != "") {
        $nombre_Comp = "N4G" . $con_idC;
    }
    $otrosParametros = array(
        'comprimir' => false,
        'nombre' => "ConsolidadoEstadoSituacionFinanciera",
        'sufijoNombre' => $nombre_Comp . "T" . $msc_trimestre,
        'color' => 0,
        'tablaimg' => $tablaimg
    );
    $guardar = array(
        'guardar' => $guardararchivo,
        'modulo' => "scg",
        'claveunica' => "ldf/consolidados",
        'claveUr' => $grupoUR,
        '$claveunica2' => "T" . $msc_trimestre,
        'tipoguardado' => 2
    );
} else {
    $guardararchivo = true;
    $otrosParametros = array(
        'comprimir' => false,
        'nombre' => "EstadoSituacionFinanciera",
        'sufijoNombre' => "E" . $ent_id . "T" . $msc_trimestre,
        'color' => 0,
        'tablaimg' => $tablaimg,
        'libro' => true
    );
    $guardar = array(
        'guardar' => $guardararchivo,
        'modulo' => "scg",
        'claveunica' => "ldf",
        'claveUr' => $grupoUR,
        '$claveunica2' => "T" . $msc_trimestre,
        'tipoguardado' => 2
    );
}

$excel = new generaPhpspreadsheet();
$excel->generaExcel($datos, $encabezado, $table, $otrosParametros, false, false); //guardar
