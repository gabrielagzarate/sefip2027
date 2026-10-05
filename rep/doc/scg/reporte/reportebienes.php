<?php
session_start();
//librerias
// Obligatorio para que el reporteador funcione
require_once dirname(__FILE__) . '/../../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../lib/TruncateCadena.php';
require_once dirname(__FILE__) . '/../../../../lib/MesNombre.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/consulta/relacionBienes.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/UrNombre.php';
class reportetabla extends PDF
{
    public $nombreUR;
    public $ur;
    public $tipoBien;
    public $trimestre;
    public $ckmiles;
    public $ue;
    public $bien;
    public $pesos;
    public $avance;
    public $hasta;
    public $mesHasta;
    public $longitud;
    public $nombreEspecial;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();
        $this->tipoBien = $_POST['radcta'];
        $this->trimestre = $_POST['trimestre'];
        $this->tipoReporte = $_POST['tipoReporte'];
        $this->ue = (isset($_POST['ue']) ? $_POST['ue'] : "");
        $this->grupos = (isset($_POST['grupos']) ? $_POST['grupos'] : '');
        $this->excluir = (isset($_POST['excluir']) ? $_POST['excluir'] : '');
        $this->nombreEspecial = (isset($_POST['nombreEspecial']) ? $_POST['nombreEspecial'] : "ERROR DE PROGRAMA. NO EXISTE ENCABEZADO");
        $this->ckmiles = (empty($_POST['ckmiles']))? 0 : $_POST['ckmiles'];
        $this->bien = $this->tipoBien == 'A' ? 'Muebles' : 'Inmuebles';
    }

    public function Footer($lastPage = true)
    {
        switch ($this->trimestre) {
            case 1:
                $this->avance = "Primer Informe de Avance de Gestión Financiera Enero-Marzo " .APP_EJERCICIO;
                break;
            case 2:
                $this->avance = "Segundo Informe de Avance de Gestión Financiera Enero-Junio " .APP_EJERCICIO;
                break;
            case 3:
                $this->avance = "Tercer Informe de Avance de Gestión Financiera Enero-Septiembre  " .APP_EJERCICIO;
                break;
            case 4:
                $this->avance = "Cuarto Informe de Avance de Gestión Financiera Enero-Diciembre " .APP_EJERCICIO;
                break;
            case 5:
                $this->avance = "Cuenta Pública del Estado de Oaxaca del Ejercicio Fiscal  " .APP_EJERCICIO;
                break;
        }

        $this->SetY(-20);
        $this->SetFont('Arial', '', 6.5);
        $this->Cell(0, 10, $this->PageNo() . '/{nb}', 0, 0, 'C');
        $this->SetY(-15);
        $this->Cell(0, 10, utf8decode('"'.$this->avance.'"') , 0, 0, 'C');
    }
    public function Header()
    { //Encabezado del documento
        switch ($this->getOrientation()) {
            case 'P':
                $this->longitud = 90;
                $this->ancho = 175;
                $this->imagen = 195;
                $this->logo = 153;
                break;
            case 'L':
                $this->longitud = 95;
                $this->ancho = 239;
                $this->imagen = 259;
                $this->logo = 189;
                break;
        }

        if($this->ue!=''){
            $ejecutora = new AccesoDatos();
            $ejecutora->conectar();
            $ejecutora->setSql('
                SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = :gpo AND ur_id = :ur  AND ue_id = :ue ;
            ');
            $ejecutora->consultar(array(':gpo'=>$this->ur->getGpo_id(),':ur'=>$this->ur->getUr_id(),':ue'=>$this->ue));
            $ejecutora->desconectar();
            $ue_id = $ejecutora->extraer();
            $linea3 = utf8Decode('UE: '.$ue_id['ue_id'].' - '.$ue_id['ue_nombre'].'');
        } 

        /************************* */
        if ($_SESSION[_S]['nvj_id'] > 2) {
            switch ($_POST['tipoReporte']) {
                case 2: // Todas las URs de la carga de trabajo
                    $linea1 = "";
                    $linea2 = utf8Decode("Dirección de Contabilidad Gubernamental");
                    break;
                case 3: // Determinar qué incluir y qué excluir
                    $linea1 = utf8Decode("Dirección de Contabilidad Gubernamental");
                    $linea2 = utf8Decode($this->nombreEspecial);
                    break;
                default:
                    $linea1 = utf8Decode("Dirección de Contabilidad Gubernamental");
                    $linea2 = $this->ur->getGpo_id().$this->ur->getUr_id()." - ".utf8Decode(UrNombre::titleCase($this->ur->getNombre()));
                    break;
            }
        }else{           
           $linea1 = utf8Decode("Gobierno del Estado de Oaxaca");
           $linea2 = utf8Decode("UR: " . $this->ur->getClave() . " - " . utf8Decode(UrNombre::titleCase($this->ur->getNombre())));
        }
        /************************* */
        
        switch ($this->trimestre) {
            case 1:
                $this->hasta = 3;
                $this->mesHasta = "Marzo";
                break;
            case 2:
                $this->hasta = 6;
                $this->mesHasta = "Junio";
                break;
            case 3:
                $this->hasta = 9;
                $this->mesHasta = "Septiembre";
                break;
            case 4:
            case 5:
                $this->hasta = 12;
                $this->mesHasta = "Diciembre";
                break;
        }

        if ($this->ckmiles== 0) {
            $this->pesos = '(Cifras en Pesos)';
        } else {            
            $this->pesos = '(Cifras en Miles de Pesos)';
        }
        $dataMes = $this->GetDiasMes($this->hasta);
        $this->SetMargins(20, 10, 20);
        $this->SetXY(20, 10);
        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->Image('../../../../img/escudocolor_derecha.png', 122, $iy, 74, 22, 'png');
        $iyt = 0;

        $this->SetFont('univiapro', 'B', 8);
        $this->SetY($iy + $iyt);
        $this->Cell(150, 5, $linea1, 0, 1, 'C');

        $iyt += 4;
        $this->SetY($iy + $iyt);
        $linea2 = TruncateCadena(trim($linea2),$this->longitud);
        $this->Cell(150, 5, substr($linea2, 0, $this->longitud), 0, 1, 'C');

        if (strlen($linea2) > $this->longitud) {
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->Cell(150, 5,substr($linea2, $this->longitud, $this->longitud), 0, 1,'C');
        }            
        if (strlen($linea2) > $this->longitud * 2) {
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->Cell(150, 5,substr($linea2, $this->longitud * 2, $this->longitud),0, 1, 'C');
        }

        $iyt += 4;
        $this->SetY($iy + $iyt);
        $this->Cell(150, 5, utf8Decode('Relación de Bienes ' . $this->bien . ' que Componen el Patrimonio'), 0, 1, 'C');

        $iyt += 4;
        $this->SetY($iy + $iyt);

        $this->Cell(150, 5, 'Al ' . $dataMes['msc_dias'] . ' de ' .  $this->mesHasta . ' de ' . APP_EJERCICIO, 0, 0, 'C');
        $iyt += 4;
        $this->SetY($iy + $iyt);

        $this->Cell(150, 5, $this->pesos, 0, 0, 'C');
    }
    public function GetDiasMes($mes)
    {
        $sqlMes2 = 'SELECT msc_nombre, msc_dias FROM cat_MesContable WHERE msc_id= ? ;';
        $con2 = new AccesoDatos();
        $con2->conectar();
        $con2->setSql($sqlMes2);
        $con2->consultar([$this->hasta]);
        $con2->desconectar();
        $rowMes2 = $con2->extraer();
        return $rowMes2;
    }
}


$pdf = new reportetabla('P', 'mm', 'letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$iyt = 8;
$iy = $pdf->GetY();
$pdf->SetBorders(true);

$pdf->SetFont('Arial', 'B', 8);;
$pdf->SetWidths(array(20, 120, 40));
$pdf->SetAligns(array('C', 'C', 'C'));
$pdf->setY($iy + $iyt);
$pdf->setAltoLinea(4);
$pdf->Row(array(utf8Decode('CÓDIGO'), utf8Decode('DESCRIPCIÓN DEL BIEN'), 'IMPORTE'));


$datos = relacionMuebleInmueble::getBalanza($pdf->ur->getGpo_id(), $pdf->ur->getUr_id(), $pdf->tipoBien, $pdf->trimestre, $parametrosExtra=array("tipoReporte" => $pdf->tipoReporte, "ue" => $pdf->ue, "grupos" => $pdf->grupos, "excluir" => $pdf->excluir, "ckmiles" => $pdf->ckmiles));

$pdf->SetAligns(array('C', 'L', 'R'));
$pdf->SetFont('univiapro', '', 8);
$suma = 0;
foreach ($datos as $key => $value) {
    if($value['nombre'] == "TOTAL"){
        $pdf->SetFont('univiapro', 'B', 8);;
        $pdf->SetWidths(array(140, 40));
        $pdf->SetAligns(array('C', 'R'));
       $pdf->Row(array(utf8Decode('TOTAL'), ' ' . number_format(round($value['saldofinal_debe']), 0, '.', ','),)); 
    }else{
        $pdf->Row(array(utf8Decode($value['cuenta']), utf8Decode($value['nombre']), number_format((int)$value['saldofinal_debe'], 0, '.', ',')));        
    }
}

$pdf->SetProtection(array("print", "annot-forms"), "", PDF_PASSWORD_OWNER_SCG);
$pdf->Output();
