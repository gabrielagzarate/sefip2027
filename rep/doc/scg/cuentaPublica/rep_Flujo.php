<?php

session_start();
ini_set('memory_limit', '200M');
ini_set('max_execution_time', 300);
set_time_limit(0);

//librerias
require_once('../../../../lib/AccesoDatos.php');
require_once('../../../../ent/UnidadResponsable.php');
require_once('../../../../lib/TruncateCadena.php');
require_once('../../../../lib/helpers.php');
require_once('../../../lib/reportetabla.1.8.php');
require_once dirname(__FILE__) . '/../../../../lib/MesNombre.php';
require_once dirname(__FILE__) . '/../../../../lib/redondear.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/UrNombre.php';
require_once dirname(__FILE__) . '/../../../../lib/TruncateCadena.php';
require_once '../../../../lib/scg/reporte/fnc_Flujo.php';

// =====================================================================================
// Extendiendo la clase pdf para escribir el encabezado y pie de página
// =====================================================================================
class reportetabla extends PDF
{
    private $longitud = 80;
    public $longitud2 = 75;
    public $ultBorde = 0;
    public $widths = '';
    public $chkEnMiles = 1;
    public $firmante1 = '';
    public $firmante2 = '';
    public $firmante3 = '';
    public $cargo1 = '';
    public $cargo2 = '';
    public $cargo3 = '';
    public $leyenda = 0;
    public $ue = '';
    public $zebra = false;
    public $interlineado_estrecho = false;
    public $font_size = '';
    public $nlineas = 0;
    public $mesDesde = 0;
    public $linea1 = '';
    public $linea2 = '';
    public $lineas = 0;
    public $ejercicio;
    public $ur;
    public $tipoReporte;
    public $mesHasta;
    public $gpo_id;
    public $ur_id;
    public $importeActual;
    public $logo;
    public $numUe;
    public $intLin;
    public $ancho;
    public $anchoImagen;
    public $altoImagen;
    public function __construct()
    {
        parent::__construct();

        $this->gpo_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['clasAdm-ur'];

        $this->mesDesde = isset($_POST['comboMes']) ? $_POST['comboMes'] : -1;//Informacion de acuerdo al mes
        $this->mesHasta = isset($_POST['comboMesHasta']) ? $_POST['comboMesHasta'] : -1;//Informacion de acuerdo al mes
        $this->chkEnMiles = isset($_POST['chkEnMiles']) ? 1000 : 1;
        $this->firmante1 = isset($_POST['firmante1']) ? $_POST['firmante1'] : '';
        $this->firmante2 = isset($_POST['firmante2']) ? $_POST['firmante2'] : '';
        $this->firmante3 = isset($_POST['firmante3']) ? $_POST['firmante3'] : '';
        $this->cargo1 = isset($_POST['cargo1']) ? $_POST['cargo1'] : '';
        $this->cargo2 = isset($_POST['cargo2']) ? $_POST['cargo2'] : '';
        $this->cargo3 = isset($_POST['cargo3']) ? $_POST['cargo3'] : '';
        $this->leyenda = isset($_POST['leyenda']) ? $_POST['leyenda'] : '';
        $this->ue = isset($_POST['ue']) ? $_POST['ue'] : '';
        $this->numUe = isset($_POST['numUe']) ? $_POST['numUe'] : '';
        $this->tipoReporte = ((isset($_POST['tipoReporte'])) ? $_POST['tipoReporte'] : 1);

        // Para validación del reporte
        $this->zebra = isset($_POST['zebra']) ? true : false;
        $this->interlineado_estrecho = isset($_POST['interlinea']) ? true : false;
        $this->font_size = isset($_POST['font_size']) ? $_POST['font_size'] : '';

        //  interlineado
        $this->intLin = 3;

        $this->ejercicio = intval(substr($_SESSION[_S]['ejercicioActual'], 0, 4));

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($this->gpo_id);
        $this->ur->setUr_id($this->ur_id);
        $this->ur->select();
    }

    public function strtMayus($variable)
    {
        $variable = strtr(strtoupper($variable), 'àèìòùáéíóúçñäëïöü', 'ÀÈÌÒÙÁÉÍÓÚÇÑÄËÏÖÜ');
        return $variable;
    }

    public function Footer($lastPage = true)
    {
        /********************* */
        $this->AddFont('Montserrat-b', '', 'Montserrat-Bold.php');
        $this->AddFont('Montserrat', '', 'Montserrat-Regular.php');
        /************************* */
        $this->SetTextColor(0);
        $this->SetMargins('5', '5', '5');
        date_default_timezone_set('America/Mexico_City');
        $tmpb = $this->GetBorders();

        if ($this->leyenda == 1 || ($this->cargo1 != '' && $this->cargo2 != '' && $this->firmante1 != '' && $this->firmante2 != '')) {
            //$this->SetY(-40);
            //$this->Cell(190,2,utf8Decode(''),0,0,'L');
            //$this->Ln(5);
        } else {
            $this->SetY(-25);
        }

        if ($lastPage) {

            if ($this->leyenda == 1) {
                $this->SetFont('Montserrat', '', 7);
                if ($_SESSION[_S]['reportes'] == 'C96' && $this->mesDesde >= 1 && $this->mesHasta <= 3 && ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 122 && $_SESSION[_S]['nvj_id'] == 2)) {
                    $this->Cell(186, 2, utf8Decode('Lugar y fecha de generación: Reyes Mantecón, Oaxaca'), 0, 0, 'C');
                } else {
                    $this->SetFont('Montserrat-b', '', 7);
                }
                $this->Ln(3);
                $this->SetX(10);
                $this->Cell(193, 2, utf8Decode('Bajo protesta de decir verdad declaramos que los estados financieros y sus Notas son razonablemente correctos y responsabilidad del emisor.'), 0, 0, 'L');
                $this->Ln(7);
            }
            $this->SetMargins('7', '7', '7');
            $this->SetFont('Montserrat-b', '', 7);
            $y = $this->GetY();

            $this->Ln(2);
            $cargos = array_values(array_filter([$this->cargo1, $this->cargo2, $this->cargo3], 'strlen'));
            $nombres = array_values(array_filter([$this->firmante1, $this->firmante2, $this->firmante3], 'strlen'));
            $ancho = $this->GetPageWidth() - 20;
            $anchoCelda = $ancho / count($cargos);
            $this->SetFont('Montserrat-b', '', 7);
            for ($i = 0; $i < count($cargos); $i++) {
                $this->MultiAlignCell($anchoCelda, 3, utf8Decode($this->strtMayus($cargos[$i])), 0, 0, 'C');
            }
            $this->Ln(28);
            $this->SetFont('Montserrat', '', 7);
            for ($i = 0; $i < count($cargos); $i++) {
                $this->MultiAlignCell($anchoCelda, 3, utf8Decode($this-> strtMayus($nombres[$i])), 0, 0, 'C');
            }
        }

        $current = getdate();

        if ($current['minutes'] < 10) {
            $minutes = '0' . $current['minutes'];
        } else {
            $minutes = $current['minutes'];
        }

        if ($current['seconds'] < 10) {
            $seconds = '0' . $current['seconds'];
        } else {
            $seconds = $current['seconds'];
        }

        $this->SetY(-11);
        $this->SetFont('Montserrat', '', 5);
        $hora = $current['hours'] . ':' . $minutes . ':' . $seconds;

        $this->Cell(186, 10, utf8Decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');

        if ($current['mday'] < 10) {
            $mday = '0' . $current['mday'];
        } else {
            $mday = $current['mday'];
        }

        if ($current['mon'] < 10) {
            $mon = '0' . $current['mon'];
        } else {
            $mon = $current['mon'];
        }

        $fecha = $mday . '/' . $mon . '/' . $current['year'] . '';

        $this->SetBorders(0);
    }



    // Marcos: 22/mar/2018
    // Intentaremos estandarizar la impresión del logo y las líneas de encabezado, para no estar inventando
    // el hilo negro en cada reporte.php
    // $orientacion: P para portrait, L para landscape
    public function drawHeadersAndLogo($orientacion, $linea1 = '', $linea2 = '', $linea3 = '', $linea4 = '', $linea5 = '', $linea6 = '', $linea7 = '')
    {

        $this->ancho = 287; // 297 que es el ancho del tamaño carta apaisada - 10

        $this->anchoImagen = 74;
        $this->altoImagen = 22;
        $this->logo = 122;

        $ix = $this->GetX();
        $iy = $this->GetY();


        $iy = ($iy == '') ? 10 : $iy;

        $this->SetMargins(20, 10, 20);
        $this->SetXY(20, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);

        $nLineas = 0;

        // Dibujamos el logo
        $this->Image('../../../../img/escudocolor_derecha.png', $this->logo, $iy, 74, 22, 'png');

        // Dibujamos las líneas de encabezado
        $this->SetFont('Montserrat-b', '', 7);

        if (trim($linea1) != '') {
            $this->SetX(10);
            $this->Cell(190, 5, $linea1, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea2) != '') {
            $linea2 = TruncateCadena(trim($linea2), $this->longitud);
            $this->SetX(10);
            $this->Cell(190, 5, substr($linea2, 0, $this->longitud), 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            if (strlen($linea2) > $this->longitud) {
                $this->SetX(10);
                $this->Cell(190, 5, substr($linea2, $this->longitud, $this->longitud), 0, 0, 'C');
                $this->SetY($this->GetY() + 4);
            }
            if (strlen($linea2) > $this->longitud * 2) {
                $this->SetX(10);
                $this->Cell(190, 5, substr($linea2, $this->longitud * 2, $this->longitud), 0, 0, 'C');
                $this->SetY($this->GetY() + 4);
            }
        }
        if (trim($linea3) != '') {
            $this->SetX(10);
            $this->Cell(190, 5, $linea3, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea4) != '') {
            $this->SetX(10);
            $this->Cell(190, 5, $linea4, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea5) != '') {
            $this->SetX(10);
            $this->Cell(190, 5, $linea5, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea6) != '') {
            $this->SetX(10);
            $this->Cell(190, 5, $linea6, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }

        // Dejamos un espacio para la siguiente sección
        if ($nLineas <= 4) {
            $this->SetY($this->GetY() + 2);
            $this->ln(2);
        }

        // Límite para asegurar la buena visilibidad del reporte y el correcto aprovechamiento de pantalla
        // Si sale sobreencimado con los encabezados de columna, tal vez haya que revisar las líneas del encabezado
        $this->setY(40);

    }

    public function Header()
    {

        $this->longitud = 100;

        $this->AddFont('Montserrat-b', '', 'Montserrat-Bold.php');
        $this->AddFont('Montserrat', '', 'Montserrat-Regular.php');

        $linea1 = utf8Decode('Gobierno del Estado de Oaxaca');

        if ($_SESSION[_S]['nvj_id'] > 2) {
            switch ($this->tipoReporte) {
                case 2:   // Todas las URs de la carga de trabajo
                    $linea2 = utf8Decode('Dirección de Contabilidad Gubernamental');
                    break;
                case 3:   // Determinar qué incluir y qué excluir
                    $ix = $this->GetX();
                    $iy = $this->GetY();
                    $this->SetXY($ix + 15, $iy + 6);
                    $ytmp = $this->GetY();
                    $linea2 = utf8Decode(isset($_POST['nombreEspecial']) ? $_POST['nombreEspecial'] : 'ERROR DE PROGRAMA. NO EXISTE ENCABEZADO');
                    break;
                default:
                    if ($this->ejercicio == '') {
                        $linea2 = $this->ur->getClave() . ' - ' . utf8Decode(UrNombre::titleCase($this->ur->getNombre()));
                    } else {
                        $linea2 = $this->ur->getGpo_id() . $this->ur->getUr_id() . ' - ' . utf8Decode(UrNombre::titleCase($this->ur->getNombre()));
                    }
                    break;

            }

        } else {

            if (mb_detect_encoding($this->ur->getNombre(), 'auto') == 'UTF-8') {
                $linea2 = 'UR: ' . $this->ur->getClave() . ' - ' . utf8Decode(UrNombre::titleCase($this->ur->getNombre()));
            } else {
                $linea2 = 'UR: ' . $this->ur->getClave() . ' - ' . utf8Decode(UrNombre::titleCase($this->ur->getNombre()));
            }

        }

        $linea3 = '';
        if ($this->ue != '' && $this->numUe > 1) {
            $ejecutora = new AccesoDatos();
            $ejecutora->conectar();
            $ejecutora->setSql('
                    SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = :gpo AND ur_id = :ur  AND ue_id = :ue ;
                ');
            $ejecutora->consultar([':gpo' => $this->gpo_id,':ur' => $this->ur_id,':ue' => $this->ue]);
            $ejecutora->desconectar();
            $ue_id = $ejecutora->extraer();
            $linea3 = 'UE: ' . $ue_id['ue_id'] . ' - ' . utf8Decode(UrNombre::titleCase($ue_id['ue_nombre'])) . '';
        }

        if (isset($_POST['tipoReporte']) != 3 && $_SESSION[_S]['nvj_id'] > 2) {
            $linea3 = utf8Decode($linea3);
        }

        $linea4 = utf8Decode('Estado de Flujos de Efectivo');

        $mesDesde = (isset($_POST['comboMes']) && $_POST['comboMes'] != '') ? $_POST['comboMes'] : '';

        $sqlMes = ' select msc_nombre, msc_dias from cat_MesContable where msc_id= ? ;';
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sqlMes);
        $con->consultar([$mesDesde]);
        $con->desconectar();
        $rowMes = $con->extraer();

        $mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta'] != '') ? $_POST['comboMesHasta'] : '';

        $sqlMes2 = ' select msc_nombre, msc_dias from cat_MesContable where msc_id=' . $mesHasta . ';';
        $con2 = new AccesoDatos();
        $con2->conectar();
        $con2->setSql($sqlMes2);
        $con2->consultar();
        $con2->desconectar();
        $rowMes2 = $con2->extraer();

        if ($this->mesDesde == 0 and $this->mesHasta == 0) {
            $linea5 = utf8Decode('Del mes de ' . ucwords(strtolower($rowMes['msc_nombre'])) . ' de ' . $this->ejercicio . '');
        } elseif ($this->mesHasta == $this->mesDesde) {
            $linea5 = utf8Decode('Del 01 al ' . $rowMes['msc_dias'] . ' de ' . ucwords(strtolower($rowMes['msc_nombre'])) . ' de ' . $this->ejercicio . '');
        } else {
            if ($this->mesDesde == 0) {
                $linea5 = utf8Decode('Del mes de ' . ucwords(strtolower($rowMes['msc_nombre'])) . ' al ' . $rowMes2['msc_dias'] . ' de ' . ucwords(strtolower($rowMes2['msc_nombre'])) . ' de ' . $this->ejercicio . '');
            } else {
                $linea5 = utf8Decode('Del 01 de ' . ucwords(strtolower($rowMes['msc_nombre'])) . ' al ' . $rowMes2['msc_dias'] . ' de ' . ucwords(strtolower($rowMes2['msc_nombre'])) . ' de ' . $this->ejercicio . '');
            }
        }


        if ($this->chkEnMiles == 1000) {
            $linea6 = '(Cifras en Miles de Pesos)';
        } else {
            $linea6 = '(Cifras en Pesos)';
        }

        // Dibujamos el logo y las líneas del encabezado
        if (!empty($linea1)) {
            $this->drawHeadersAndLogo('P', $linea1, $linea2, $linea3, $linea4, $linea5, $linea6);
        } else {
            $linea1 = ''; // Define $linea1 before using it
            $this->drawHeadersAndLogo('P', $linea1, $linea2, $linea3, $linea4, $linea5, $linea6);
        }

        //Encabezado de la tabla
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(199, 209, 219);
        $this->SetBorders(1);


        $this->SetFont('Montserrat-b', '', 7);
        $this->SetY($this->GetY() - 8);
        $this->SetX(10);
        $this->Cell(141, 6, 'CONCEPTO', 'LRTB', 0, 'C', true);
        $this->Cell(27, 6, strtoupper($rowMes2['msc_nombre']) . ' ' . $_SESSION[_S]['ejercicioActual'], 'LRTB', 0, 'C', true);
        $this->Cell(27, 6, 'DICIEMBRE ' . $_SESSION[_S]['ejercicioActual'] - 1, 'LRTB', 0, 'C', true);
        $this->setAltoLinea(3);
        $this->SetY($this->GetY() + 6);

    }

}



// =====================================================================================
// FLUJO DE EFECTIVO
// =====================================================================================

$pdf = new reportetabla('P', 'mm', 'Letter');
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_SCG);
$pdf->setOrientation('P');
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->setDrawColor(170, 170, 170);
$pdf->SetFillColor(224, 224, 224);

$fill = false;

$datos = get_machote_estado_flujo_efectivo();

rellena_flujo_efectivo($datos, $pdf->mesDesde, $pdf->mesHasta);

$pdf->SetAutoPageBreak(true, 30);
$lineas = 0;

foreach ($datos as $indice => $valor) {
    $pdf->SetX(10);

    $concepto = '';

    $font_size = 6.4;
    if ($pdf->font_size != '' && ((float)$pdf->font_size > 5 && (float)$pdf->font_size <= 12)) {
        $font_size = $pdf->font_size;
    }

    $es_linea_separadora = false;
    switch ($indice) {
        /***PRIMER NIVEL*****/
        case '1':
            //ACTIVO
        case 'suma_FEAO':
            //BIENES
        case '31':
        case '32':
            //FINANCIAMIENTO
        case 'suma_FNEAI':
        case '42':
        case '43':
            //NETO
        case 'suma_FNEAF':
        case '54':
        case 'suma_IDNEEE':
        case '56':
        case 'importe_EEEIE':
        case '58':
        case 'suma_EEEFE':
            /************* *SEGUNDO NIVEL **************/
            //ACTIVO
        case 'suma_FEAO_O':
            //PASIVO
        case 'suma_FEAO_A':
            //OPERACION
        case 'suma_FEAI_O':
        case 'suma_FEAI_A':
            //FINANCIAMIENTO
        case 'suma_FEAF_O':
        case 'suma_FEAF_A':
            /************* *TERCER NIVEL **************/
            //CTAS DEL ACTIVO
        case 'importe_411':
        case 'importe_412':
        case 'importe_413':
        case 'importe_414':
        case 'importe_415':
        case 'importe_416':
        case 'importe_417':
        case 'importe_421':
        case 'importe_422':
        case 'importe_OOO':
            //CTAS DEL PASIVO
        case 'importe_511':
        case 'importe_512':
        case 'importe_513':
        case 'importe_521':
        case 'importe_522':
        case 'importe_523':
        case 'importe_524':
        case 'importe_525':
        case 'importe_526':
        case 'importe_527':
        case 'importe_528':
        case 'importe_529':
        case 'importe_531':
        case 'importe_532':
        case 'importe_533':
        case 'importe_OAO':
            //BIENES
        case 'importe_123_O':
        case 'importe_124_O':
        case 'importe_OOI':
        case 'importe_123_A':
        case 'importe_124_A':
        case 'importe_OOA':
            //FINANCIAMIENTO
        case 'suma_OEN':
        case 'importe_OEF':
        case 'suma_ASD':
        case 'importe_OAF':
            /********** CUARTO NIVEL */
            //FINANCIAMIENTO
        case 'importe_OEN_I':
        case 'importe_OEN_E':
        case 'importe_ASD_I':
        case 'importe_ASD_E':
            if (trim(isset($valor['niv1']) == '') && trim(isset($valor['niv2']) == '') && trim(isset($valor['niv3']) == '') && trim(isset($valor['niv4']) == '')) {
                $es_linea_separadora = true;
                $concepto = '';
                $importeActual = '';
                $importeAnterior = '';
            }
            break;
    }
    // Por si piden en miles de pesos
    // SIEMPRE Y CUANDO NO SEA UNA LÍNEA SEPARADORA
    if (!$es_linea_separadora) {

        // Usamos el chr(160) porque en UTF8 se "ve" como espacio pero no es un espacio, y así
        // el FPDF no lo quita (quita todos los espacios al principio de la cadena)
        switch ($indice) {
            case '1':
                //BIENES
            case '31':
            case '32':
                //FINANCIAMIENTO
            case '42':
            case '43':
                //NETO
            case '54':
            case '56':
            case '58':
                if (isset($valor['niv1']) != '') {
                    $pdf->SetFont('Montserrat-b', '', $font_size);
                    $concepto = utf8Decode($valor['niv1']);
                }
                break;
                //ACTIVO
            case 'suma_FEAO':
                //FINANCIAMIENTO
            case 'suma_FNEAI':
                //NETO
            case 'suma_FNEAF':
            case 'suma_IDNEEE':
            case 'importe_EEEIE':
            case 'suma_EEEFE':
                if (isset($valor['niv1']) != '') {
                    $pdf->SetFont('Montserrat-b', '', $font_size);
                    $concepto = utf8Decode($valor['niv1']);
                }
                if ($pdf->chkEnMiles == 1000) {
                    $importesFormateadosActuales = isset($valor['sAct']) ? number_format((int)as_number($valor['sAct']) / $pdf->chkEnMiles, 0) : 0;
                    $importesFormateadosAnteriores = isset($valor['sAnt']) ? number_format((int)as_number($valor['sAnt']) / $pdf->chkEnMiles, 0) : 0;
                } else {
                    $importesFormateadosActuales = isset($valor['sAct']) ? number_format((int)as_number($valor['sAct']), 0) : 0;
                    $importesFormateadosAnteriores = isset($valor['sAnt']) ? number_format((int)as_number($valor['sAnt']), 0) : 0;
                }
                break;
                //ACTIVO
            case 'suma_FEAO_O':
                //PASIVO
            case 'suma_FEAO_A':
                //OPERACION
            case 'suma_FEAI_O':
            case 'suma_FEAI_A':
                //FINANCIAMIENTO
            case 'suma_FEAF_O':
            case 'suma_FEAF_A':
                if ($valor['niv2'] != '') {
                    $pdf->SetFont('Montserrat-b', '', $font_size);
                    $concepto = chr(160) . '   ' . utf8Decode($valor['niv2']);
                }
                if ($pdf->chkEnMiles == 1000) {
                    $importesFormateadosActuales = isset($valor['sAct']) ? number_format((int)as_number($valor['sAct']) / $pdf->chkEnMiles, 0) : 0;
                    $importesFormateadosAnteriores = isset($valor['sAnt']) ? number_format((int)as_number($valor['sAnt']) / $pdf->chkEnMiles, 0) : 0;
                } else {
                    $importesFormateadosActuales = isset($valor['sAct']) ? number_format((int)as_number($valor['sAct']), 0) : 0;
                    $importesFormateadosAnteriores = isset($valor['sAnt']) ? number_format((int)as_number($valor['sAnt']), 0) : 0;
                }
                break;
                //CTAS DEL ACTIVO
            case 'importe_411':
            case 'importe_412':
            case 'importe_413':
            case 'importe_414':
            case 'importe_415':
            case 'importe_416':
            case 'importe_417':
            case 'importe_421':
            case 'importe_422':
            case 'importe_OOO':
                //CTAS DEL PASIVO
            case 'importe_511':
            case 'importe_512':
            case 'importe_513':
            case 'importe_521':
            case 'importe_522':
            case 'importe_523':
            case 'importe_524':
            case 'importe_525':
            case 'importe_526':
            case 'importe_527':
            case 'importe_528':
            case 'importe_529':
            case 'importe_531':
            case 'importe_532':
            case 'importe_533':
            case 'importe_OAO':
                //BIENES
            case 'importe_123_O':
            case 'importe_124_O':
            case 'importe_OOI':
            case 'importe_123_A':
            case 'importe_124_A':
            case 'importe_OOA':
                //FINANCIAMIENTO
            case 'importe_OEF':
            case 'importe_OAF':
                if ($valor['niv3'] != '') {
                    $pdf->SetFont('Montserrat', '', 6.1);
                    $concepto = chr(160) . '       ' . utf8Decode($valor['niv3']);
                }
                if ($pdf->chkEnMiles == 1000) {
                    $importesFormateadosActuales = !empty($valor['sAct']) ? number_format(round(as_number($valor['sAct']), 2) / $pdf->chkEnMiles, 0, '.', ',') : 0;
                    $importesFormateadosAnteriores = !empty($valor['sAnt']) ? number_format(round(as_number($valor['sAnt']), 2) / $pdf->chkEnMiles, 0, '.', ',') : 0;
                } else {
                    $importesFormateadosActuales = !empty($valor['sAct']) ? number_format(round(as_number($valor['sAct']), 2), 0, '.', ',') : 0;
                    $importesFormateadosAnteriores = !empty($valor['sAnt']) ? number_format(round(as_number($valor['sAnt']), 2), 0, '.', ',') : 0;
                }
                break;
                //FINANCIAMIENTO
            case 'suma_ASD':
            case 'suma_OEN':
                if ($valor['niv3'] != '') {
                    $pdf->SetFont('Montserrat', '', $font_size);
                    $concepto = chr(160) . '       ' . utf8Decode($valor['niv3']);
                }
                if ($pdf->chkEnMiles == 1000) {
                    $importesFormateadosActuales = isset($valor['sAct']) ? number_format((int)as_number($valor['sAct']) / $pdf->chkEnMiles, 0) : 0;
                    $importesFormateadosAnteriores = isset($valor['sAnt']) ? number_format((int)as_number($valor['sAnt']) / $pdf->chkEnMiles, 0) : 0;
                } else {
                    $importesFormateadosActuales = isset($valor['sAct']) ? number_format((int)as_number($valor['sAct']), 0) : 0;
                    $importesFormateadosAnteriores = isset($valor['sAnt']) ? number_format((int)as_number($valor['sAnt']), 0) : 0;
                }
                break;
                //FINANCIAMIENTO
            case 'importe_OEN_I':
            case 'importe_OEN_E':
            case 'importe_ASD_I':
            case 'importe_ASD_E':
                if ($valor['niv4'] != '') {
                    $pdf->SetFont('Montserrat', '', $font_size);
                    $concepto = chr(160) . '           ' . utf8Decode($valor['niv4']);
                }
                if ($pdf->chkEnMiles == 1000) {
                    $importesFormateadosActuales = isset($valor['sAct']) ? number_format(round(as_number($valor['sAct'], 2)) / $pdf->chkEnMiles, 0, '.', ',') : 0;
                    $importesFormateadosAnteriores = isset($valor['sAnt']) ? number_format(round(as_number($valor['sAnt']), 2) / $pdf->chkEnMiles, 0, '.', ',') : 0;
                } else {
                    $importesFormateadosActuales = isset($valor['sAct']) ? number_format(round(as_number($valor['sAct']), 2), 0, '.', ',') : 0;
                    $importesFormateadosAnteriores = isset($valor['sAnt']) ? number_format(round(as_number($valor['sAnt']), 2), 0, '.', ',') : 0;
                }
                break;
        }

        switch ($indice) {
            case '1':
            case '31':
            case '32':
            case '42':
            case '43':
            case '54':
            case '56':
            case '58':
                $importeActual = '';
                $importeAnterior = '';
                break;
            default:
                $importeActual = $importesFormateadosActuales;
                $importeAnterior = $importesFormateadosAnteriores;
                break;
        }
    }


    $pdf->SetRelleno($fill);

    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetWidths([141, 27, 27]);
    $pdf->SetAligns(['L', 'R', 'R']);
    $pdf->SetBorders('LR');

    switch ($indice) {
        case '1':
        case '32':
        case '43':
            $pdf->Row([ $concepto, '', '' ]);
            break;
        case 'suma_EEEFE':
            $pdf->SetBorders('LRB');
            $pdf->Row([ $concepto, $importeActual, $importeAnterior]);
            break;
        default:
            $pdf->Row([ $concepto, $importeActual, $importeAnterior ]);
            break;
    }

    if ($pdf->interlineado_estrecho) {
        $pdf->Ln(-1.4);
    } else {
        $pdf->Ln(1);
    }

    $lineas++;

    if ($pdf->zebra) {
        $fill = !$fill;
    }

}

$pdf->Output();

// Fin del código fuente
