<?php

if (!function_exists('u8d')) {
    function u8d($value)
    {
        return mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
    }
}

session_start();
//librerias
// Obligatorio para que el reporteador funcione
require_once dirname(__FILE__) . '/../../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/TruncateCadena.php';
require_once dirname(__FILE__) . '/../../../../lib/compararCadenas.php';
require_once dirname(__FILE__) . '/../../../../ent/Usuario.php';
require_once dirname(__FILE__) . '/../../../../ent/scg/ldf/EntePublicoLDF.php';
require_once dirname(__FILE__) . '/../../../../lib/Math.php';
require_once dirname(__FILE__) . '/../../../../lib/FicheroSefip.php';
// Se incluyen los PHP necesarios en función de lo que se imprime
require_once dirname(__FILE__) . '/../../../../lib/MesNombre.php';
require_once dirname(__FILE__) . '/../../../../lib/redondear.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
// Por ejemplo, estas son librerías propias del reporta a imprimir
// Librerias LDF
require_once dirname(__FILE__) . '/../../../../lib/scg/ldf/lib_situacionFinanciera.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/ldf/lib_analiticoPresupuesto6A.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/ldf/lib_analiticoPresupuesto6B.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/ldf/lib_analiticoPresupuesto6C.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/ldf/lib_analiticoPresupuesto6D.php';

class pdf_Automatico extends PDF
{
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

        $this->format = $format;
        //echo "<pre>";print_r($_POST);echo "</pre>";die();
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();
        /* echo "Clas ".$_SESSION[_S]['clasAdm-ue']."-";
        echo "Post".$_POST['ue'];
          die();// */

        $this->ur->setUsr_id($_SESSION[_S]['s24-id']);
        $this->nivel = $_SESSION[_S]['nvj_id'];
        $this->usr_id = $_SESSION[_S]['s24-id'];

        if ($this->usr_id != '' && $this->usr_id != 0) {
            $this->usuario = new Usuario();
            $this->usuario->setUsr_id($this->usr_id);
            $this->usuario->select();
        }

        /*if (isset($_SESSION[_S]['clasAdm-ue'])) {
            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $this->ue->setUr_id($_SESSION[_S]['clasAdm-ur']);
            $this->ue->setUe_id($_SESSION[_S]['clasAdm-ue']);
            $this->ue->select();
        } elseif (isset($_POST['ue']) && trim($_POST['ue']) != '') {
            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $this->ue->setUr_id($_SESSION[_S]['clasAdm-ur']);
            $this->ue->setUe_id(trim($_POST['ue']));
            $this->ue->select();
        } else {*/
        $this->ue = FALSE;
        //}

        $this->ue_id = ($this->ue ? $this->ue->getUe_id() : FALSE);

        if (isset($_POST['firma']) && $_POST['firma'] != 0) {
            $this->firmar = TRUE;
        } else {
            $this->firmar = FALSE;
        }
        $this->firmar = false;
        //echo $_SESSION[_S]['clasAdm-gpo'].$_SESSION[_S]['clasAdm-ur']."//////".$_SESSION[_S]['s19-gpo'].$_SESSION[_S]['s19-ur'];
        //die();
        if ($this->firmar) {
            $this->firma = new AuxiliarUnidadResponsable();
            $this->firma->setGpo_id($_SESSION[_S]['s24-gpo']);
            $this->firma->setUr_id($_SESSION[_S]['s24-ur']);
            $this->firma->select();
        }

        $this->sis_id = (isset($_POST['sis_id']) ? $_POST['sis_id'] : '');
        $this->men_id = 14; //(isset($_POST['men_id']) ? $_POST['men_id'] : 14);
        $this->opc_id = 1; //(isset($_POST['opc_id']) ? $_POST['opc_id'] : 1);
        $this->rep_id = (isset($_POST['rep_id']) ? $_POST['rep_id'] : 0);
        // -----------------------------------------------------------------------------------------
        // Paso 1: Aquí preparamos las opciones globales y variables que usaremos en la sección CONTENT()
        // -----------------------------------------------------------------------------------------
        switch ($this->men_id) {
                //menu Reporte
            case 14:
                $this->msc_trimestre = (isset($_POST['msc_trimestre']) ? $_POST['msc_trimestre'] : '');

                $this->consolidado = (isset($_POST['consolidado']) ? $_POST['consolidado'] : false);
                $this->ent_id = (isset($_POST['ent_id']) ? $_POST['ent_id'] : '');
                $this->ent_idC = (isset($_POST['ent_id']) ? $_POST['ent_id'] : '');
                $this->sec_idC = (isset($_POST['sec_id']) ? $_POST['sec_id'] : '');
                $this->gpo_idC = (isset($_POST['gpo_id']) ? $_POST['gpo_id'] : '');
                $this->con_idC = (isset($_POST['con_id']) ? $_POST['con_id'] : '');
                $this->ejercicio = "2022";

                $this->noAplica = (isset($_POST['noAplica']) ? $_POST['noAplica'] : false);

                if ($this->ent_id == 0 && !$this->consolidado) {
                    echo "NO TIENE ENTIDAD ASIGNADO";
                    die();
                } //*
                //$this->setOrientation('L');
                break;
            default:
                break;
        } //*/

        /*if($this->rep_id == 6 || $this->rep_id == 7 || $this->rep_id == 8 || $this->rep_id == 9){
            $this->setOrientation('L');
        }*/
    }

    public function Header()
    { //Encabezado del documento
        switch ($this->format) {
            case 'letter':
                switch ($this->getOrientation()) {
                    case 'P':
                        //$this->longitud = 45;
                        $this->longitud = 60;
                        $this->ancho = 175;
                        $this->largo = 300;
                        $this->anchoImagen = 74;
                        $this->altoImagen = 22;
                        $this->imagen = 195;
                        //$this->logo = 97;
                        $this->logo = 122;
                        break;
                    case 'L':
                        //$this->longitud = 80;
                        $this->longitud = 95;
                        $this->ancho = 239;
                        $this->largo = 200;
                        $this->imagen = 259;
                        $this->anchoImagen = 74;
                        $this->altoImagen = 22;
                        //$this->logo = 161;
                        $this->logo = 186;
                        break;
                }
                break;
        }

        if (!$this->firmar) {
            //$this->ImagenFondo("../../../../../sefip2019/img/pruebaimagen.png", 424, 371, 3);
            //$this->TextoFondo("EN REVISIÓN", 0, 3); //Funcion indefinida 
        }

        // Paso 2: Configuramos el texto del encabezado
        switch ($this->men_id) {
            case 14:
                if ($this->consolidado) {
                    $encabLin1 = "GOBIERNO ESTATAL CONSOLIDADO";
                    $encabLin2 = '';

                    if ($this->ent_idC <> '') {
                        $entePub = new EntePublicoLDF();
                        $entePub->setEnt_id($this->ent_idC);
                        $entePub->select();

                        $sql = "
                            SELECT gpo_id,ur_id
                            FROM ldf. vin_EntePublicoLDF
                            WHERE ejr_id = 2022
                                AND ent_id= " . $this->ent_idC . " AND vin_generaReporte = 1
                            LIMIT 1
                        ";

                        $consultaEnt = new AccesoDatos();
                        $consultaEnt->conectar();
                        $consultaEnt->setSql($sql);
                        $consultaEnt->consultar();
                        $consultaEnt->desconectar();
                        if ($consultaEnt->getNumeroRegistros() > 0) {
                            $row = $consultaEnt->extraer();
                            $this->ur_clave = $row['gpo_id'] . $row['ur_id'];
                        }
                        $encabLin2 = $entePub->getEncabezado();
                    }

                    switch ($this->sec_idC) {
                        case 'P':
                            $encabLin2 = "SECTOR PARAESTATAL";
                            break;
                        case 'C':
                            $encabLin2 = "SECTOR CENTRAL";
                            break;
                    }

                    switch ($this->gpo_idC) {
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
                } else {
                    $entePub = new EntePublicoLDF();
                    $entePub->setEnt_id($this->ent_id);
                    $entePub->select();
                    $encabLin1 = $entePub->getEncabezado();
                    //echo "<pre>";print_r($entePub);echo "</pre>";die();
                    $encabLin2 = '';
                }

                switch ($this->rep_id) {
                    case 1:
                        $this->tiponombreReporte = u8d('Estado de Situación Financiera Detallado - LDF');
                        $this->nombreEncabezado = u8d($encabLin1);
                        $this->nombreEnte = u8d($encabLin2);

                        $this->peridodo = "";

                        switch ($this->msc_trimestre) {
                            case 1:
                                $this->peridodo = "Del 31 de Diciembre de 2020 al 31 de Marzo de 2022";
                                $this->encabmes = "31 de Marzo de 2022";
                                break;
                            case 2:
                                $this->peridodo = "Del 31 de Diciembre de 2020 al 30 de Junio de 2022";
                                $this->encabmes = "30 de Junio de 2022";
                                break;
                            case 3:
                                $this->peridodo = "Del 31 de Diciembre de 2020 al30 de Septiembre de 2022";
                                $this->encabmes = "30 de Septiembre de 2022";
                                break;
                            case 4:
                                $this->peridodo = "Del 31 de Diciembre de 2020 al 31 de Diciembre de 2022";
                                $this->encabmes = "31 de Diciembre de 2022";
                                break;
                        }

                        //$longitud = strlen($this->nombreReporte);
                        //$this->nombreReporte = TruncateCadena(u8d(trim($this->nombreReporte)),            $this->longitud);
                        break;
                    case 6:
                    case 7:
                    case 8:
                    case 9:
                        $this->tiponombreReporte = u8d('Estado Analítico del Ejercicio del Presupuesto de Egresos Detallado - LDF');

                        switch ($this->rep_id) {
                            case 6:
                                $this->tiponombreSubtitulo = u8d('Clasificación por Objeto del Gasto (Capítulo y Concepto) ');
                                break;
                            case 7:
                                $this->tiponombreSubtitulo = u8d('Clasificación Administrativa');
                                break;
                            case 8:
                                $this->tiponombreSubtitulo = u8d('Clasificación Funcional (Finalidad y Función)');
                                break;
                            case 9:
                                $this->tiponombreSubtitulo = u8d('Clasificación de Servicios Personales');
                                break;
                        }

                        $this->nombreEncabezado = u8d($encabLin1);
                        $this->nombreEnte = u8d($encabLin2);
                        $this->peridodo = "";

                        switch ($this->msc_trimestre) {
                            case 1:
                                $this->peridodo = "Del 1 de enero al 31 de Marzo de 2022";
                                break;
                            case 2:
                                $this->peridodo = "Del 1 de enero al 30 de Junio de 2022";
                                break;
                            case 3:
                                $this->peridodo = "Del 1 de enero al 30 de Septiembre de 2022";
                                break;
                            case 4:
                                $this->peridodo = "Del 1 de enero al 31 de Diciembre de 2022";
                                break;
                        }

                        //$longitud = strlen($this->nombreReporte);
                        //$this->nombreReporte = TruncateCadena(u8d(trim($this->nombreReporte)),            $this->longitud);
                        break;
                    default:
                        break;
                } //*/
                break;
            default:
                break;
        }
        /* if(!$this->firmar){
          $ix=$this->GetX();
          $iy=$this->GetY();
          $this->SetFont('univiapro','B',50);
          $this->SetTextColor(200,200,200);
          $this->RotatedText($ix+30,$iy+160,'HOJA DE TRABAJO',45);
          $this->RotatedText($ix+150,$iy+160,'HOJA DE TRABAJO',45);
          $this->RotatedText($ix,$iy+80,'HOJA DE TRABAJO',45);
          $this->RotatedText($ix+150,$iy+80,'HOJA DE TRABAJO',45);
          }// */

        $this->SetMargins(20, 10, 20);
        $this->SetXY(20, 10);
        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);

        //$this->Image('../../../img/logosefin-color-fblanco.png', $this->logo, $iy, 99, 22, 'png');
        //$this->Image('../../../../img/logoOaxaca-color-fblanco.png', $this->logo, $iy, $this->anchoImagen, $this->altoImagen, 'png');
        /* $this->Image('../../../../img/logoOaxaca-color-fblanco.png', $this->logo, $iy,
          74, 22, 'png'); */

        if($this->noAplica){
            if($this->getOrientation() == "L"){
                $this->Image('../../../../img/NoAplica.png', 100, 20, 100, 200, 'png');
            } else {
                $this->Image('../../../../img/NoAplica.png', 50, 10, 100, 300, 'png');
            }
        }

        $iyt = 0;
        //echo (!$this->consolidado) ? 'N':'Y';
        /*if(!$this->consolidado){
            //echo (!$this->consolidado) ? 'N':'Y';
            if($this->ent_id == 1){
                if(is_file('../../../../img/logosefin-color-fblancoOriginal.jpg')){
                    $this->Image('../../../../img/logosefin-color-fblancoOriginal.jpg', $this->logo, $iy, 74, 16, 'jpg');
                }
            }else{
                if(is_file('../../../../img/Logo Institucional/'.$this->ur->getClave().'.jpg')){
                    $this->Image('../../../../img/Logo Institucional/'.$this->ur->getClave().'.jpg', $this->logo, $iy, 74, 16, 'jpg');
                }
            }
        }else{
            if($this->ent_idC <> ''){
                if($this->ent_idC == 1){
                    if (is_file('../../../../img/logosefin-color-fblancoOriginal.jpg')){
                        $this->Image('../../../../img/logosefin-color-fblancoOriginal.jpg', $this->logo, $iy, 74, 16, 'jpg');
                    }
                }else{
                    if(is_file('../../../../img/Logo Institucional/'.$this->ur_clave.'.jpg')){
                        $this->Image('../../../../img/Logo Institucional/'.$this->ur_clave.'.jpg', $this->logo, $iy, 74, 16, 'jpg');
                    }
                }
            }else{
                if(is_file('../../../../img/logosefin-color-fblancoOriginal.jpg')){
                    $this->Image('../../../../img/logosefin-color-fblancoOriginal.jpg', $this->logo, $iy, 74, 16, 'jpg');
                }
            }
            
            $this->SetFont('univiapro', 'B', 8);
            
            $this->SetY($iy + $iyt);
            $this->Cell(195, 5, $this->nombreEncabezado, 0, 1, 'L');
            $iyt += 4;
        }*/
        $this->Image('../../../../img/logoOaxaca-color-fblanco.png', $this->logo, $iy, $this->anchoImagen, $this->altoImagen, 'png');
        //$this->Image('../../../img/escudo2.jpg', $this->logo, $iy, 72, 21, 'jpg');
        //$this->ImagePngWithAlpha('../../../img/logosefin-color-fblancox.png', $this->logo, $iy, 99, 22, 'png');

        $lenconcepto = 0;
        if (!$this->consolidado) {
            $this->SetFont('univiapro', 'B', 8);
            $this->SetY($iy + $iyt);
            //$this->Cell(195, 5, $this->nombreEncabezado, 0, 1, 'L');
            $lenconcepto = strlen($this->nombreEncabezado);
            $this->MultiCell(100, 5, ($this->nombreEncabezado), 0, 1, "L");
        } else {
            $this->SetFont('univiapro', 'B', 8);
            $this->SetY($iy + $iyt);
            // $this->Cell(195, 5, $this->nombreEnte, 0, 1, 'L');
            $lenconcepto = strlen($this->nombreEnte);
            $this->MultiCell(100, 5, ($this->nombreEnte), 0, 1, "L");
        }

        if ($lenconcepto > 60) {
            $lenconcepto = $lenconcepto / 60;
            $lenconcepto = $lenconcepto * 4;
        } else {
            $lenconcepto = 1;
        }
        $iyt += 4 + ($lenconcepto);
        $this->SetY($iy + $iyt);
        $this->Cell(180, 5, $this->tiponombreReporte, 0, 1, 'L');
        $this->SetFont('univiapro', '', 8);
        if (isset($this->tiponombreSubtitulo)) {
            $iyt += 4 + ($lenconcepto);
            $this->SetY($iy + $iyt);
            $this->Cell(180, 5, $this->tiponombreSubtitulo, 0, 1, 'L');
            $this->SetFont('univiapro', '', 8);
        }
        $iyt += 4;
        $this->SetY($iy + $iyt);
        //$this->MultiCell($this->ancho - 80, 1, $this->peridodo, 0, 1, 'L');
        $this->Cell(195, 5, $this->peridodo, 0, 1, 'L');
        $this->SetFont('univiapro', '', 7);
        $iyt += 4;
        $this->SetY($iy + $iyt);
        $this->Cell(195, 5, "(PESOS)", 0, 1, 'L');

        $this->SetFont('univiapro', '', 7);

        //echo $iyt;die();
        if ($iyt < 20) {
            $iyt = 24;
        } else {
            $iyt += 4;
        }
        $this->Line($ix, $iy + $iyt, $this->imagen, $iy + $iyt);

        $iyt += 2;
        $this->setY($iy + $iyt);
        $this->SetBorders(TRUE);
        $this->SetRelleno(TRUE);
        $relleno = TRUE;
        $this->SetFont('univiapro', 'B', 8);
        $this->setDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->setAltoLinea(3);

        // -----------------------------------------------------------------------------------------
        // Paso 3: Configuramos el nombre y ancho de cada columna del reporte
        // El ancho total para PDF en modo "portrait" es 175
        // -----------------------------------------------------------------------------------------
        switch ($this->men_id) {
            case 14:
                switch ($this->rep_id) {
                    case 1:
                        $ancho = ($this->ancho - (24 + 24 + 24 + 24)) / 2;
                        $this->SetFont('univiapro', '', 6);
                        $this->SetWidths(array($ancho, 24, 28, $ancho, 24, 28));
                        $this->SetAligns(array('L', 'R', 'R', 'L', 'R', 'R'));
                        $this->Row(array("Concepto", $this->encabmes, "31 de Diciembre de 2020", "Concepto", $this->encabmes, "31 de Diciembre de 2020"));
                        $this->SetBorders(0);
                        $this->SetAligns(array('L', 'R', 'R', 'L', 'R', 'R'));
                        $this->SetRelleno($relleno);
                        break;

                    case 6:
                        $ancho = $this->ancho - (24 + 24 + 24 + 24 + 24 + 24);
                        $this->SetFont('univiapro', '', 6);
                        $this->SetWidths(array($ancho, (24 + 24 + 24 + 24 + 24), 24));
                        $this->SetAligns(array('C', 'C', 'C'));
                        $this->Row(array("Concepto", "Egresos", "Subejercicio"));
                        $this->SetBorders(0);
                        // $this->SetRelleno(true);

                        $this->SetFont('univiapro', '', 6);
                        $this->SetWidths(array($ancho, 24, 24, 24, 24, 24, 24));
                        $this->SetAligns(array('L', 'R', 'R', 'R', 'R', 'R', 'R'));
                        $this->Row(array("", "Aprobado", "Ampliaciones/ (Reducciones)", "Modificado", "Devengado", "Pagado", ""));
                        $this->SetBorders(1);
                        $this->SetAligns(array('L', 'R', 'R', 'R', 'R', 'R', 'R'));
                        $this->SetRelleno($relleno);
                        break;

                    case 7:
                        $ancho = $this->ancho - (24 + 24 + 24 + 24 + 24 + 24);
                        $this->SetFont('univiapro', '', 6);
                        $this->SetWidths(array($ancho, (24 + 24 + 24 + 24 + 24), 24));
                        $this->SetAligns(array('C', 'C', 'C'));
                        $this->Row(array("Concepto", "Egresos", "Subejercicio"));
                        $this->SetBorders(0);
                        // $this->SetRelleno(true);

                        $this->SetFont('univiapro', '', 6);
                        $this->SetWidths(array($ancho, 24, 24, 24, 24, 24, 24));
                        $this->SetAligns(array('L', 'R', 'R', 'R', 'R', 'R', 'R'));
                        $this->Row(array("", "Aprobado", "Ampliaciones/ (Reducciones)", "Modificado", "Devengado", "Pagado", ""));
                        $this->SetBorders(1);
                        $this->SetAligns(array('L', 'R', 'R', 'R', 'R', 'R', 'R'));
                        $this->SetRelleno($relleno);
                        break;
                    case 8:
                        $ancho = $this->ancho - (24 + 24 + 24 + 24 + 24 + 24);
                        $this->SetFont('univiapro', '', 6);
                        $this->SetWidths(array($ancho, (24 + 24 + 24 + 24 + 24), 24));
                        $this->SetAligns(array('C', 'C', 'C'));
                        $this->Row(array("Concepto", "Egresos", "Subejercicio"));
                        $this->SetBorders(0);
                        // $this->SetRelleno(true);

                        $this->SetFont('univiapro', '', 6);
                        $this->SetWidths(array($ancho, 24, 24, 24, 24, 24, 24));
                        $this->SetAligns(array('L', 'R', 'R', 'R', 'R', 'R', 'R'));
                        $this->Row(array("", "Aprobado", "Ampliaciones/ (Reducciones)", "Modificado", "Devengado", "Pagado", ""));
                        $this->SetBorders(1);
                        $this->SetAligns(array('L', 'R', 'R', 'R', 'R', 'R', 'R'));
                        $this->SetRelleno($relleno);
                        break;
                    case 9:
                        $ancho = $this->ancho - (24 + 24 + 24 + 24 + 24 + 24);
                        $this->SetFont('univiapro', '', 6);
                        $this->SetWidths(array($ancho, (24 + 24 + 24 + 24 + 24), 24));
                        $this->SetAligns(array('C', 'C', 'C'));
                        $this->Row(array("Concepto", "Egresos", "Subejercicio"));
                        $this->SetBorders(0);
                        // $this->SetRelleno(true);

                        $this->SetFont('univiapro', '', 6);
                        $this->SetWidths(array($ancho, 24, 24, 24, 24, 24, 24));
                        $this->SetAligns(array('L', 'R', 'R', 'R', 'R', 'R', 'R'));
                        $this->Row(array("", "Aprobado", "Ampliaciones/ (Reducciones)", "Modificado", "Devengado", "Pagado", ""));
                        $this->SetBorders(1);
                        $this->SetAligns(array('L', 'R', 'R', 'R', 'R', 'R', 'R'));
                        $this->SetRelleno($relleno);
                        break;
                    default:
                        break;
                }
                break;
            default:
                break;
        }
        $this->ln(1);
    }

    public function Content($datos)
    {
        //realizamos la impresion de acuerdo al nivel(aqui no se procesa la informacion, el array debe estar procesado y ordenado
        $this->SetFont('univiapro', '', 7); ///tipo de letra para cada titulo
        $this->setDrawColor(170, 170, 170);
        $this->SetFillColor(224, 224, 224);
        $relleno = TRUE;
        // -----------------------------------------------------------------------------------------
        // Paso 4: Imprimirmos los datos obtenidos en el paso 5... ésta es la impresión del reporte en sí
        // -----------------------------------------------------------------------------------------
        switch ($this->men_id) {
            case 14:
                switch ($this->rep_id) {
                    case 1:
                        //echo '<pre>';print_r($datos);echo '</pre>';die();
                        foreach ($datos as $key => $dato) {
                            /*echo "<pre>";print_r($dato);echo "</pre>"; */
                            $this->SetRelleno($relleno);
                            //if($dato['nivel'] == 1){
                            //$this->SetFont('univiapro', 'B', 6);
                            //}else{
                            $this->SetFont('univiapro', '', 6);
                            //}
                            $this->Row(array(
                                //u8d($dato['nombreIzquierda']),
                                u8d($dato['cuentaIzquierda']) . '-' . u8d($dato['nombreIzquierda']),
                                (is_numeric($dato['importe2022Izquierda']) ? number_format($dato['importe2022Izquierda'], 2) : '0.00'),
                                (is_numeric($dato['importe2020Izquierda']) ? number_format($dato['importe2020Izquierda'], 2) : '0.00'),
                                //u8d($dato['nombreDerecha']),
                                u8d($dato['cuentaDerecha']) . '-' . u8d($dato['nombreDerecha']),
                                (is_numeric($dato['importe2022Derecha']) ? number_format($dato['importe2022Derecha'], 2) : '0.00'),
                                (is_numeric($dato['importe2020Derecha']) ? number_format($dato['importe2020Derecha'], 2) : '0.00')
                            ));
                            /*$this->Row( array(
                                //u8d($dato['nombreIzquierda']),
                                u8d($dato['cuentaIzquierda']).'-'.u8d($dato['nombreIzquierda']),
                                $dato['importe2019Izquierda'], $dato['importe2018Izquierda'],
                                //u8d($dato['nombreDerecha']),
                                u8d($dato['cuentaDerecha']).'-'.u8d($dato['nombreDerecha']),
                                $dato['importe2019Derecha'], $dato['importe2018Derecha']
                            ));*/
                            $relleno = !$relleno;
                        }
                        //die();
                        break;

                    case 6:
                        //echo '<pre>';print_r($datos);echo '</pre>';die();
                        foreach ($datos as $key => $dato) {
                            /*echo "<pre>";print_r($dato);echo "</pre>"; */
                            $this->SetRelleno($relleno);
                            //echo '<pre>';print_r($dato);echo '</pre>';die();
                            /*if(!isset($dato['nivel'])){
                                echo"XXX";
                                echo '<pre>';print_r($dato);echo '</pre>';die();
                            }*/
                            if ($dato['nivel'] == 1) {
                                $this->SetFont('univiapro', 'B', 6);
                            } else {
                                $this->SetFont('univiapro', '', 6);
                            }
                            $this->Row(array(
                                //u8d($dato['nombre']),
                                u8d($dato['cuenta']) . '-' . u8d($dato['nombre']),
                                number_format($dato['aprobado'], 2), number_format($dato['ampred'], 2),
                                number_format($dato['modificado'], 2), number_format($dato['devengado'], 2),
                                number_format($dato['pagado'], 2), number_format($dato['subejercicio'], 2)
                            ));
                            /*$this->Row(
                                    array(u8d($dato['cgcu_nombre2019']),
                                (empty($dato['saldo2019']) ? $dato['saldo2019'] : number_format($dato['saldo2019'],2)),
                                (empty($dato['saldo2018']) ? $dato['saldo2018'] : number_format($dato['saldo2018'],2)),
                                u8d($dato['Pcgcu_nombre2019']),
                                (empty($dato['Psaldo2019']) ? $dato['Psaldo2019']
                                        : number_format($dato['Psaldo2019'],2)),
                                (empty($dato['Psaldo2018']) ? $dato['Psaldo2018']
                                        : number_format($dato['Psaldo2018'],2))
                            ));*/
                            $relleno = !$relleno;
                        }
                        //die();
                        break;
                    case 7:
                        //echo '<pre>';print_r($datos);echo '</pre>';die();
                        foreach ($datos as $key => $dato) {
                            /*echo "<pre>";print_r($dato);echo "</pre>"; */
                            $this->SetRelleno($relleno);
                            if ($dato['nivel'] == 1) {
                                $this->SetFont('univiapro', 'B', 6);
                            } else {
                                $this->SetFont('univiapro', '', 6);
                            }
                            $this->Row(array(
                                //u8d($dato['nombre']),
                                u8d($dato['cuenta']) . '-' . u8d($dato['nombre']),
                                number_format($dato['aprobado'], 2), number_format($dato['ampred'], 2),
                                number_format($dato['modificado'], 2), number_format($dato['devengado'], 2),
                                number_format($dato['pagado'], 2), number_format($dato['subejercicio'], 2)
                            ));
                            /*$this->Row(
                                    array(u8d($dato['cgcu_nombre2019']),
                                (empty($dato['saldo2019']) ? $dato['saldo2019'] : number_format($dato['saldo2019'],2)),
                                (empty($dato['saldo2018']) ? $dato['saldo2018'] : number_format($dato['saldo2018'],2)),
                                u8d($dato['Pcgcu_nombre2019']),
                                (empty($dato['Psaldo2019']) ? $dato['Psaldo2019']
                                        : number_format($dato['Psaldo2019'],2)),
                                (empty($dato['Psaldo2018']) ? $dato['Psaldo2018']
                                        : number_format($dato['Psaldo2018'],2))
                            ));*/
                            $relleno = !$relleno;
                        }
                        //die();
                        break;
                    case 8:
                        //echo '<pre>';print_r($datos);echo '</pre>';die();
                        foreach ($datos as $key => $dato) {
                            /*echo "<pre>";print_r($dato);echo "</pre>"; */
                            $this->SetRelleno($relleno);
                            if ($dato['nivel'] == 1) {
                                $this->SetFont('univiapro', 'B', 6);
                            } else {
                                $this->SetFont('univiapro', '', 6);
                            }
                            $this->Row(array(
                                //u8d($dato['nombre']),
                                u8d($dato['cuenta']) . '-' . u8d($dato['nombre']),
                                number_format($dato['aprobado'], 2), number_format($dato['ampred'], 2),
                                number_format($dato['modificado'], 2), number_format($dato['devengado'], 2),
                                number_format($dato['pagado'], 2), number_format($dato['subejercicio'], 2)
                            ));
                            /*$this->Row(
                                    array(u8d($dato['cgcu_nombre2019']),
                                (empty($dato['saldo2019']) ? $dato['saldo2019'] : number_format($dato['saldo2019'],2)),
                                (empty($dato['saldo2018']) ? $dato['saldo2018'] : number_format($dato['saldo2018'],2)),
                                u8d($dato['Pcgcu_nombre2019']),
                                (empty($dato['Psaldo2019']) ? $dato['Psaldo2019']
                                        : number_format($dato['Psaldo2019'],2)),
                                (empty($dato['Psaldo2018']) ? $dato['Psaldo2018']
                                        : number_format($dato['Psaldo2018'],2))
                            ));*/
                            $relleno = !$relleno;
                        }
                        //die();
                        break;
                    case 9:
                        //echo '<pre>';print_r($datos);echo '</pre>';die();
                        foreach ($datos as $key => $dato) {
                            /*echo "<pre>";print_r($dato);echo "</pre>"; */
                            $this->SetRelleno($relleno);
                            if ($dato['nivel'] == 1) {
                                $this->SetFont('univiapro', 'B', 6);
                            } else {
                                $this->SetFont('univiapro', '', 6);
                            }
                            $this->Row(array(
                                //u8d($dato['nombre']),
                                u8d($dato['cuenta']) . '-' . u8d($dato['nombre']),
                                number_format($dato['aprobado'], 2), number_format($dato['ampred'], 2),
                                number_format($dato['modificado'], 2), number_format($dato['devengado'], 2),
                                number_format($dato['pagado'], 2), number_format($dato['subejercicio'], 2)
                            ));
                            /*$this->Row(
                                    array(u8d($dato['cgcu_nombre2019']),
                                (empty($dato['saldo2019']) ? $dato['saldo2019'] : number_format($dato['saldo2019'],2)),
                                (empty($dato['saldo2018']) ? $dato['saldo2018'] : number_format($dato['saldo2018'],2)),
                                u8d($dato['Pcgcu_nombre2019']),
                                (empty($dato['Psaldo2019']) ? $dato['Psaldo2019']
                                        : number_format($dato['Psaldo2019'],2)),
                                (empty($dato['Psaldo2018']) ? $dato['Psaldo2018']
                                        : number_format($dato['Psaldo2018'],2))
                            ));*/
                            $relleno = !$relleno;
                        }
                        //die();
                        break;
                    default:
                        break;
                }
                break;
            default:
                break;
        }
    }

    public function Footer($lastPage = true)
    {
        //Usuarios de la Direccion de Contabilidad
        $usr_Direccion = 326; #VHUITRONG 
        $usr_Coord1 = 99999; #VHUITRONG  
        $usr_Coord2 = 347; #PACOSTAB  
        switch ($this->getOrientation()) {
            case 'P':
                $x = 15;
                $distancia = 5;
                break;
            case 'L':
                $x = 30;
                $distancia = 25;
                break;
        }
        //$this->firmar=false;

        $distancia2 = 0;
        if ($lastPage) {
            if ($this->firmar) {
                $this->SetFont('univiapro', 'B', 6.5);
                if ($this->ent_id != 1 && !$this->consolidado) {
                    $y = -40;
                    $distancia1 = 30;
                    $distancia2 = 87;

                    if ($this->firma->getNombreFinancieros() != "") {
                        $this->SetY($y);
                        $this->setX($x);
                        $this->MultiCell(
                            57,
                            3,
                            u8d('REVISÓ'),
                            0,
                            'C',
                            FALSE
                        );
                        $distancia1 = 57;
                        $distancia2 = 114;
                    }

                    if ($this->firma->getNombreAdministrativo() != "") {
                        $this->SetY($y);
                        $this->setX($x + $distancia1);
                        $this->MultiCell($distancia, 3, '', 0, 'C', FALSE);

                        $this->SetY($y);
                        $this->setX($x + $distancia1 + $distancia);
                        $this->MultiCell(
                            57,
                            3,
                            u8d('REVISÓ'),
                            0,
                            'C',
                            FALSE
                        );
                    }
                    if ($this->firma->getNombreTitular() != "") {
                        $this->SetY($y);
                        $this->setX($x + $distancia2 + $distancia);
                        $this->MultiCell($distancia, 3, '', 0, 'C', FALSE);

                        $this->SetY($y);
                        $this->setX($x + $distancia2 + 2 * $distancia);
                        $this->MultiCell(
                            57,
                            3,
                            u8d('AUTORIZÓ'),
                            0,
                            'C',
                            FALSE
                        );
                    }
                    $y = -20;
                    if ($this->firma->getNombreFinancieros() != "") {
                        $this->SetY($y);
                        $this->setX($x);
                        $this->MultiCell(
                            57,
                            4,
                            u8d($this->firma->getNombreFinancieros()),
                            'T',
                            'C',
                            FALSE
                        );

                        $this->SetY($y);
                        $this->setX($x + 57);
                        $this->MultiCell($distancia, 4, '', 0, 'C', FALSE);
                    }
                    if ($this->firma->getNombreAdministrativo() != "") {
                        $this->SetY($y);
                        $this->setX($x + $distancia1 + $distancia);
                        $this->MultiCell(
                            57,
                            4,
                            u8d($this->firma->getNombreAdministrativo()),
                            'T',
                            'C',
                            FALSE
                        );

                        $this->SetY($y);
                        $this->setX($x + $distancia2 + $distancia);
                        $this->MultiCell($distancia, 4, '', 0, 'C', false);
                    }
                    if ($this->firma->getNombreTitular() != "") {
                        $this->SetY($y);
                        $this->setX($x + $distancia2 + 2 * $distancia);
                        $this->MultiCell(
                            57,
                            4,
                            u8d($this->firma->getNombreTitular()),
                            'T',
                            'C',
                            FALSE
                        );
                    }
                    $y = -15;
                    $this->SetY($y);
                    $this->setX($x);
                    $this->MultiCell(
                        57,
                        3,
                        u8d($this->firma->getCargoFinancieros()),
                        0,
                        'C'
                    );

                    $this->SetY($y);
                    $this->setX($x + $distancia1);
                    $this->MultiCell($distancia, 3, '', 0, 'C', FALSE);

                    $this->SetY($y);
                    $this->setX($x + $distancia1 + $distancia);
                    $this->MultiCell(
                        57,
                        3,
                        u8d($this->firma->getCargoAdministrativo()),
                        0,
                        'C'
                    );

                    $this->SetY($y);
                    $this->setX($x + $distancia2 + $distancia);
                    $this->MultiCell($distancia, 3, '', 0, 'C', FALSE);

                    $this->SetY($y);
                    $this->setX($x + $distancia2 + 2 * $distancia);
                    $this->MultiCell(
                        57,
                        3,
                        u8d($this->firma->getCargoTitular()),
                        0,
                        'C'
                    );
                } else {
                    if ($this->consolidado) {
                        if (($this->ent_idC >= 1 && $this->ent_idC <= 11)
                            || $this->sec_idC == "C"
                            || ($this->gpo_idC >= 1 && $this->gpo_idC <= 4)
                        ) {
                            $userCon = $usr_Coord1;
                        } else {
                            $userCon = $usr_Coord2;
                        }
                        if ($userCon != $this->usuario->getUsr_id()) {

                            $this->usuarioU = new Usuario();
                            $this->usuarioU->setUsr_id($userCon); //usario evangelina
                            $this->usuarioU->select();
                        }
                    } else {
                        $this->usuarioU = $this->usuario;
                    }
                    $this->usuarioD = new Usuario();
                    $this->usuarioD->setUsr_id($usr_Direccion); //usario evangelina
                    $this->usuarioD->select();

                    $y = -40;
                    $distacia3 = 55;
                    if ($this->usuarioU->getNombre() != "" && $this->con_idC == "") {
                        $distancia2 = 30;
                        $this->SetY($y);
                        $this->setX($x + $distancia2);
                        $this->MultiCell($distancia, 3, '', 0, 'C', FALSE);

                        $this->SetY($y);
                        $this->setX($x + $distancia2 + $distancia);
                        $this->MultiCell(
                            57,
                            3,
                            u8d('REVISÓ'),
                            0,
                            'C',
                            FALSE
                        );
                        $distacia3 = 87;
                    }

                    if ($this->usuarioD->getNombre() != "") {
                        $this->SetY($y);
                        $this->setX($x + $distacia3 + $distancia);
                        $this->MultiCell($distancia, 3, '', 0, 'C', FALSE);

                        $this->SetY($y);
                        $this->setX($x + $distacia3 + 2 * $distancia);
                        $this->MultiCell(57, 3, u8d('AUTORIZÓ'), 0, 'C', FALSE);
                    }
                    $y = -20;

                    if ($this->usuarioU->getNombre() != "" && $this->con_idC == "") {
                        $this->SetY($y);
                        $this->setX($x + $distancia2 + $distancia);
                        $this->MultiCell(57, 4, u8d($this->usuarioU->getNombre() . " " . $this->usuarioU->getPaterno() . " " . $this->usuarioU->getMaterno()), 'T', 'C', FALSE);

                        $this->SetY($y);
                        $this->setX($x + $distacia3 + $distancia);
                        $this->MultiCell($distancia, 4, '', 0, 'C', false);
                    }
                    if ($this->usuarioD->getNombre() != "") {
                        $this->SetY($y);
                        $this->setX($x + $distacia3 + 2 * $distancia);
                        $this->MultiCell(57, 4, u8d($this->usuarioD->getNombre() . " " . $this->usuarioD->getPaterno() . " " . $this->usuarioD->getMaterno()), 'T', 'C', FALSE);
                    }
                    $y = -15;
                    /*                  $this->SetY($y);
                      $this->setX($x);
                      $this->MultiCell(57, 3,
                      u8d($this->usuarioU->getNombre()." ".$this->usuarioU->getPaterno()." ".$this->usuarioU->getMaterno()), 0, 'C');
                     */
                    $this->SetY($y);
                    $this->setX($x + $distancia2);
                    $this->MultiCell($distancia, 3, '', 0, 'C', FALSE);
                    if ($this->usuarioU->getNombre() != "" && $this->con_idC == "") {
                        $this->SetY($y);
                        $this->setX($x + $distancia2 + $distancia);
                        $this->MultiCell(57, 3, u8d($this->usuarioU->getPuesto()), 0, 'C');
                    }
                    $this->SetY($y);
                    $this->setX($x + $distacia3 + $distancia);
                    $this->MultiCell(57, 3, '', 0, 'C', FALSE);

                    $this->SetY($y);
                    $this->setX($x + $distacia3 + 2 * $distancia);
                    $this->MultiCell(57, 3, u8d($this->usuarioD->getPuesto()), 0, 'C');
                }
            }
        }
        $this->SetFont('univiapro', '', 5);
        $y = -7;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(57, 2, u8d($this->usuario->getUsr_id() . " - " . $this->usuario->getUsuario() . " "), 0, 'L');

        $this->SetY($y);
        $this->setX($x + 57);
        $this->MultiCell($distancia, 2, '', 0, 'C', FALSE);

        $this->SetY($y);
        $this->setX($x + 57 + $distancia);
        $this->MultiCell(57, 2, u8d('Página ' . $this->PageNo() . '/{nb}'), 0, 'C');

        $this->SetY($y);
        $this->setX($x + 114 + $distancia);
        $this->MultiCell($distancia, 2, '', 0, 'C', FALSE);

        $this->SetY($y);
        $this->setX($x + 104 + 2 * $distancia);
        $this->MultiCell(63, 2, date("d/m/Y") . ' - ' . date("H:i:s"), 0, 'R');
    }
}
