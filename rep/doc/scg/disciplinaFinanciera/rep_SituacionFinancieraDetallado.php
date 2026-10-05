<?php

session_start();
error_reporting(E_ALL);
set_time_limit(0);

require_once dirname(__FILE__) . '/../../ReporteBase.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/disciplinaFinanciera/lib_situacionFinanciera.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/UrNombre.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';
require_once dirname(__FILE__) . '/../../../../lib/TruncateCadena.php';
require_once '../../../../lib/constantes.php';

DEFINE("TAMANO_FUENTE_MINUSCULAS", 5.5);
DEFINE("TAMANO_FUENTE_MAYUSCULAS", 7.5);

class ReporteSituacionFinancieraDetalladoLdf extends ReporteBase
{
    public $longitud;
    public $mesHasta = null;
    public $enteId = null;
    public $enteEncabezado = '';
    public $pesos;
    public $corte;
    public $especial;
    public $gpo_id;
    public $ur_id;
    public $ur;
    public $ue;
    public $ejercicio;
    public $logoUr;
    public $linea1;
    public $texto;
    public $texto2;

    public function __construct()
    {
        parent::__construct();
        $this->AddFont('Montserrat-b', '', 'Montserrat-Bold.php');
        $this->AddFont('Montserrat', '', 'Montserrat-Regular.php');
        $this->gpo_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
        $this->ur_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();
        
        $this->ue = (isset($_POST['ue']) ? $_POST['ue'] : "");

        $this->ejercicio = intval( substr( $this->getEjercicioActual(), 0, 4 ) );
    }

    function drawHeadersAndLogo( $orientacion, $linea1 = "", $linea2 = "", $linea3 = "", $linea4 = "", $linea5 = "", $linea6 = "", $linea7 = "" )
    {            
        $iy = $this->GetY();

           
        $iy = ( $iy == "" ) ? 10 : $iy;
            
        $this->SetMargins(20, 10, 20);
        $this->SetXY(20, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255); 
        $this->SetBorders(0);

        $nLineas = 0;
        //LOGO OAXACA
        $this->Image('../../../../img/'.LOGO_COLOR, 10, 10, 75, 18, 'png');
        //LOGO UR
        $logoUr = $this->gpo_id.$this->ur_id; 
        if (file_exists("../../../../img/Logo Institucional/".$logoUr.".png")) {
            $this->Image('../../../../img/Logo Institucional/'.$logoUr.'.png', 215, 10, 75, 19, 'png');
        } 

        // Dibujamos las líneas de encabezado
        $this->SetFont('Montserrat-b','',7);          

        if( trim( $linea1 ) != "" ) {
            $linea1 = TruncateCadena(trim($linea1),$this->longitud);
            $this->SetX( 10 );
            $this->Cell( 280, 5,  substr($linea1, 0, $this->longitud), 0, 0, 'C' );  
            $this->SetY( $this->GetY() + 4 );
            if (strlen($linea1) > $this->longitud) {
                $this->SetX( 10 );
                $this->Cell(280, 5,substr($linea1, $this->longitud, $this->longitud), 0, 0,'C');
                $this->SetY( $this->GetY() + 4 );
            }            
            if (strlen($linea1) > $this->longitud * 2) {
                $this->SetX( 10 );
                $this->Cell(280, 5,substr($linea1, $this->longitud * 2, $this->longitud),0, 0, 'C');
                $this->SetY( $this->GetY() + 4 );
            }

            $nLineas++;
        }
        if( trim( $linea2 ) != "" ) {
            $this->SetX( 10 );
            $this->Cell( 280, 5, $linea2, 0, 0, 'C' );  
            $this->SetY( $this->GetY() + 4 );
            $nLineas++;
        }
        if( trim( $linea3 ) != "" ) {
            $this->SetX( 10 );
            $this->Cell( 280, 5, $linea3, 0, 0, 'C' );  
            $this->SetY( $this->GetY() + 4 );
            $nLineas++;
        }
        if( trim( $linea4 ) != "" ) {
            $this->SetX( 10 );
            $this->Cell( 280, 5, $linea4, 0, 0, 'C' );  
            $this->SetY( $this->GetY() + 4 );
            $nLineas++;
        }
        if( trim( $linea5 ) != "" ) {
            $this->SetX( 10 );
            $this->Cell( 280, 5, $linea5, 0, 0, 'C' );  
            $this->SetY( $this->GetY() + 4 );
            $nLineas++;
        }
        if( trim( $linea6 ) != "" ) {
            $this->SetX( 10 );
            $this->Cell( 280, 5, $linea6, 0, 0, 'C' );  
            $this->SetY( $this->GetY() + 4 );
            $nLineas++;
        }
        if( trim( $linea7 ) != "" ) {
            $this->SetX( 10 );
            $this->Cell( 280, 5, $linea7, 0, 0, 'C' );  
            $this->SetY( $this->GetY() + 4 );
            $nLineas++;
        }

        // Dejamos un espacio para la siguiente sección
        if ( $nLineas <= 4 ) { 
            $this->SetY( $this->GetY() + 2 );
            $this->ln(5);
        }

        // Límite para asegurar la buena visilibidad del reporte y el correcto aprovechamiento de pantalla
        // Si sale sobreencimado con los encabezados de columna, tal vez haya que revisar las líneas del encabezado
        $this->setY(34);

    }

    function Header(){
        $this->longitud = 80;
        $this->AddFont('Montserrat-b', '', 'Montserrat-Bold.php');
        $this->AddFont('Montserrat', '', 'Montserrat-Regular.php');

        /************************* */
        if ($_SESSION[_S]['nvj_id'] > 2) {

            switch ($_POST['tipoReporte']) {
                case 2: // Todas las URs de la carga de trabajo
                    $linea1 = "";
                    break;
                case 3: // Determinar qué incluir y qué excluir
                    $linea1 = isset($_POST['nombrEspecial']) ?  utf8Decode($_POST['nombrEspecial']) : "ERROR DE PROGRAMA. NO EXISTE ENCABEZADO";
                    break;
                default:
                    if( $this->ejercicio == "" ){
                        $linea1 = utf8Decode($this->ur->getClave()." - ".$this->ur->getNombre());
                    }else{
                        $linea1 = utf8Decode($this->ur->getGpo_id().$this->ur->getUr_id()." - ".$this->ur->getNombre());
                    }
                    break;
            }
        }else{
            if (mb_detect_encoding($this->ur->getNombre(), "auto")=="UTF-8"){
                $linea1 = utf8Decode("UR: ".$this->ur->getClave()." - ".$this->ur->getNombre());
            }else{
                $linea1 = utf8Decode("UR: ".$this->ur->getClave()." - ". $this->ur->getNombre());
            }
        }

        if($this->ue!=''){
            $ejecutora = new AccesoDatos();
            $ejecutora->conectar();
            $ejecutora->setSql('
                SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = :gpo AND ur_id = :ur  AND ue_id = :ue ;
            ');
            $ejecutora->consultar(array(':gpo'=>$this->gpo_id,':ur'=>$this->ur_id,':ue'=>$this->ue));
            $ejecutora->desconectar();
            $ue_id = $ejecutora->extraer();
            $texto2 = 'UE: '.$ue_id['ue_id'].' - '.$ue_id['ue_nombre'].'';
        } 

        $texto = wordwrap( trim( $this->texto ), $this->longitud, "|" );
        $texto_hdr = explode( "|", $texto );

        if ( count( $texto_hdr ) > 1 ) {
            $linea3 = $texto_hdr[0];
            $linea4 = $texto_hdr[1];
        } else {
            if(isset($_POST['tipoReporte'])!=3 && $_SESSION[_S]['nvj_id']>2) {
                $linea3 = $texto;
            }

            if($this->ue!='' && $_SESSION[_S]['nvj_id']>2){ 
                $linea4 = utf8Decode($texto2);
            }   
        }

        $linea5 = utf8Decode('Estado de Situación Financiera Detallado - LDF');

        switch ($_POST['msc_trimestre']) {
            case 1:
                $corte = "Al 31 de Marzo de " .$this->getEjercicioActual();
                $mesHasta = 3;
                $alto1 = 9;
                $alto2 = 4.5;
                $alto3 = 3;
                break;
            case 2:
                $corte = "Al 30 de Junio de " .$this->getEjercicioActual();
                $mesHasta = 6;
                $alto1 = 9;
                $alto2 = 4.5;
                $alto3 = 3;
                break;
            case 3:
                $corte = "Al 30 de Septiembre de " .$this->getEjercicioActual();
                $mesHasta = 9;
                $alto1 = 9;
                $alto2 = 3;
                $alto3 = 3;
                break;
            case 4:
                $corte = "Al 31 de Diciembre de " .$this->getEjercicioActual();
                $mesHasta = 12;
                $alto1 = 9;
                $alto2 = 3;
                $alto3 = 3;
                break;
        }

        $sqlMes = 'SELECT msc_nombre, msc_dias FROM cat_MesContable WHERE msc_id = ' . $mesHasta . ';';
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sqlMes);
        $con->consultar();
        $con->desconectar();
        $rowMes = $con->extraer();
        
        $linea6 =utf8Decode('Al ' . $rowMes['msc_dias'] . ' de ' . strtolower($rowMes['msc_nombre']) . ' de ' . $this->getEjercicioActual(). ' y al 31 de diciembre de '. $this->getEjercicioAnterior());
        
        if (empty($_POST['ckmiles'])) {
            $linea7 = '(Cifras en Pesos)';
        } else {            
            $linea7 = '(Cifras en Miles de Pesos)';
        }

        $linea4 = "";
        if(isset($_POST['tipoReporte']) == 3 || $_SESSION[_S]['nvj_id'] <= 2){
            $linea3 = utf8Decode($texto);
            if($this->ue!='' && $_SESSION[_S]['nvj_id']<=2){
                $linea4 = utf8Decode($this->texto2);
            }
        }
        
        $linea1 = TruncateCadena(trim($linea1),$this->longitud);
        $linea2 = "";
        $this->drawHeadersAndLogo( 'L', $linea1, $linea2, $linea3, $linea4, $linea5, $linea6, $linea7 );
                    
        //Encabezado de la tabla
        $this->setDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);

        $this->SetFont('Montserrat-b','',7);
        $this->SetX( 10 );
        $this->MultiAlignCell(95, $alto1, 'CONCEPTO', 1, 0,'C',true);
        $this->MultiAlignCell(25, $alto2, $corte, 1, 0,'C',true);
        $this->MultiAlignCell(25, $alto3, "Al 31 de Diciembre de ".$_SESSION[_S]['ejercicioActual']-1, 1, 0,'C',true);
        $this->MultiAlignCell(87, $alto1, 'CONCEPTO', 1, 0,'C',true);
        $this->MultiAlignCell(25, $alto2, $corte, 1, 0,'C',true);
        $this->MultiAlignCell(25, $alto3, "Al 31 de Diciembre de ".$_SESSION[_S]['ejercicioActual']-1, 1, 0,'C',true);
        $this->SetY( $this->GetY() + $alto1 );
            
    }
}

/* ---------- REPORTE DE SITUACION FINANCIERA DETALLADO ------------- */
/**
 * A partir de aquí se obtinen los datos para el cuerpo del reporte y se comienza a rellenar.
 */

if (empty($_POST['msc_trimestre']) || !in_array($_POST['msc_trimestre'], array(1, 2, 3, 4))) {
    echo "<html><head><script>alert('No se ha seleccionado un trimestre válido.'); window.close();</script></head></html>";
    exit();
}

$limiteCuerpo = 185;

$pdf = new ReporteSituacionFinancieraDetalladoLdf('L', 'mm', 'A5');
$pdf->SetProtection(array("print", "annot-forms"), '', PDF_PASSWORD_OWNER_SCG);

$pdf->setOrientation('L');
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->setRelleno('no');
$pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MAYUSCULAS);
$pdf->SetWidths(array(103, 34, 103, 34));
$pdf->SetAligns(array('L', 'R', 'L', 'R'));

$datos = lib_situacionFinanciera($pdf->enteId, $_POST['msc_trimestre'],false,'','','','',$parametrosExtra=array("tipoReporte" => $_POST['tipoReporte'], "ue" => $_POST['ue'], "grupos" => $_POST['grupos'], "excluir" => $_POST['excluir'], "ckmiles" => $_POST['ckmiles']));

$altoLinea = 4;
$pdf->SetFillColor(192, 192, 192);

foreach ($datos as $value) {
    $pdf->longitud = 100;

    switch ($value['nivelIzquierda']) {
        case 1:
        case 2:
            $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MINUSCULAS);
            $espacio = chr(160) . "    ";
            break;
        case 3:
            $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
            $espacio = chr(160) . "        ";
            break;
        case 4:
            $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
            $espacio = chr(160) . "            ";
            break;
        default:
            $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
            break;
    }

    // Aquí se imprime el contenido de la línea:
    $pdf->setX(10);
    $pdf->setDrawColor(170, 170, 170);

    $pdf->Cell(95, $altoLinea, $espacio . substr($value['nombreIzquierda'], 0, $pdf->longitud), 'LR', 0, 'L', false);
    $pdf->Cell(25, $altoLinea, $value['importeActualIzquierda'] == '' || in_array($value['nombreIzquierda'], ['ACTIVO', 'Activo Circulante', 'Activo No Circulante']) ? '' : number_format(round(floatval($value['importeActualIzquierda']), 0), 0), 'LR', 0, 'R', false);
    $pdf->Cell(25, $altoLinea, $value['importeAnteriorIzquierda'] == '' || in_array($value['nombreIzquierda'], ['ACTIVO', 'Activo Circulante', 'Activo No Circulante']) ? '' : number_format(round(floatval($value['importeAnteriorIzquierda']), 0), 0), 'LR', 0, 'R', false);

    switch ($value['nivelDerecha']) {
        case 1:
        case 2:
            $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MINUSCULAS);
            $espacio = chr(160) . "    ";
            break;
        case 3:
            $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
            $espacio = chr(160) . "        ";
            break;
        case 4:
            $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
            $espacio = chr(160) . "            ";
            break;
        default:
            $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
            break;
    }

    $pdf->Cell(87, $altoLinea, $espacio . substr($value['nombreDerecha'], 0, $pdf->longitud), 'LR', 0, 'L', false);
    $pdf->Cell(25, $altoLinea, $value['importeActualDerecha'] == '' || in_array($value['nombreDerecha'], ['PASIVO', 'Pasivo Circulante', 'Pasivo No Circulante', utf8Decode('HACIENDA PÚBLICA/PATRIMONIO')]) ? '' : number_format(round(floatval($value['importeActualDerecha']), 0), 0), 'LR', 0, 'R', false);
    $pdf->Cell(25, $altoLinea, $value['importeAnteriorDerecha'] == '' || in_array($value['nombreDerecha'], ['PASIVO', 'Pasivo Circulante', 'Pasivo No Circulante', utf8Decode('HACIENDA PÚBLICA/PATRIMONIO')]) ? '' : number_format(round(floatval($value['importeAnteriorDerecha']), 0), 0), 'LR', 1, 'R', false);

    $con2 = $con = 1;

    if (strlen($value['nombreIzquierda']) > $pdf->longitud * $con2 || strlen($value['nombreDerecha']) > $pdf->longitud * $con) {
        switch ($value['nivelIzquierda']) {
            case 1:
            case 2:
                $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MINUSCULAS);
                $espacio = chr(160) . "    ";
                break;
            case 3:
                $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                $espacio = chr(160) . "        ";
                break;
            case 4:
                $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                $espacio = chr(160) . "            ";
                break;
            default:
                $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                break;
        }

        $pdf->setX(10);
        $pdf->Cell(95, $altoLinea, $espacio . substr($value['nombreIzquierda'], $pdf->longitud * $con2, $pdf->longitud), 'LR', 0, 'L', false);
        $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'R', false);
        $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'R', false);

        switch ($value['nivelDerecha']) {
            case 1:
            case 2:
                $pdf->SetFont('Montserrat-b', '', TAMANO_FUENTE_MINUSCULAS);
                $espacio = chr(160) . "    ";
                break;
            case 3:
                $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                $espacio = chr(160) . "        ";
                break;
            case 4:
                $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                $espacio = chr(160) . "            ";
                break;
            default:
                $pdf->SetFont('Montserrat', '', TAMANO_FUENTE_MINUSCULAS);
                break;
        }

        $pdf->Cell(87, $altoLinea, $espacio . substr($value['nombreDerecha'], $pdf->longitud * $con, $pdf->longitud), 'LR', 0, 'L', false);
        $pdf->Cell(25, $altoLinea, '', 'LR', 0, 'R', false);
        $pdf->Cell(25, $altoLinea, '', 'LR', 1, 'C', false);

        $con2++;
        $con++;
    }

    if ($pdf->getY() > $limiteCuerpo) {
        $pdf->Line(10, $pdf->GetY(), 292, $pdf->GetY());
        $pdf->addPage();
    }
}
$pdf->Line(10, $pdf->GetY(), 292, $pdf->GetY());

$pdf->Output();