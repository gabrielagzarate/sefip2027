<?php


session_start();

//librerias
require_once('../../../../lib/AccesoDatos.php');
require_once('../../../../ent/UnidadResponsable.php');
require_once('../../../../lib/scg/conciliacion/consolidacion.php');
require_once('../../../../lib/TruncateCadena.php');
require_once('../../../../lib/compararCadenas.php');
require_once('../../../../lib/MesNombre.php');
require_once('../../../../lib/helpers.php');
require_once('../../../lib/reportetabla.1.8.php');

//Extendiendo la clase pdf para escribir el encabezado y pie de página
class reportetabla extends PDF
{
    public $balanza = '';
    public $anio = '';
    public $anchoImagen = 0;
    public $altoImagen = 0;
    public $nlineas = 0;
    private $longitud = 100;
    public $longitud2 = 75;
    public $ultBorde = 0;
    public $widths = '';

    public function __construct()
    {
        parent::__construct();
        //print_r($_POST);
        $this->gpo_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['s24-gpo'];
        $this->ur_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['s24-ur'];

        $this->mesDesde = isset($_POST['comboMes']) ? $_POST['comboMes'] : -1;//Informacion de acuerdo al mes
        $this->mesHasta = isset($_POST['comboMesHasta']) ? $_POST['comboMesHasta'] : -1;//Informacion de acuerdo al mes
        $this->genero = isset($_POST['comboGenero']) ? ($_POST['comboGenero'] != '' ? $_POST['comboGenero'] : 9) : 9;
        $this->tipoReporte = isset($_POST['tipoReporte']) ? $_POST['tipoReporte'] : 1;
        $this->chkEnMiles = isset($_POST['chkEnMiles']) ? 1000 : 1;
        $this->grupos = isset($_POST['grupos']) ? $_POST['grupos'] : '';
        $this->excluir = isset($_POST['excluir']) ? $_POST['excluir'] : '';
        $this->firmante1 = isset($_POST['firmante1']) ? $_POST['firmante1'] : '';
        $this->firmante2 = isset($_POST['firmante2']) ? $_POST['firmante2'] : '';
        $this->firmante3 = isset($_POST['firmante3']) ? $_POST['firmante3'] : '';
        $this->cargo1 = isset($_POST['cargo1']) ? $_POST['cargo1'] : '';
        $this->cargo2 = isset($_POST['cargo2']) ? $_POST['cargo2'] : '';
        $this->cargo3 = isset($_POST['cargo3']) ? $_POST['cargo3'] : '';
        $this->leyenda = isset($_POST['leyenda']) ? $_POST['leyenda'] : '';
        $this->ceros = isset($_POST['ceros']) ? $_POST['ceros'] : '';
        $this->incCta = isset($_POST['incCta']) ? $_POST['incCta'] : '';
        $this->intLin = isset($_POST['intLin']) ? $_POST['intLin'] : '';
        $this->ue = isset($_POST['ue']) ? $_POST['ue'] : '';
        $this->numUe = isset($_POST['numUe']) ? $_POST['numUe'] : '';

        // Si no viene un número en el interlineado, ponemos el default
        if (!is_numeric($this->intLin)) {
            $this->intLin = 2.5;
        }
        $this->intLin = 5;

        $_POST['tipoReporte'] = ((isset($_POST['tipoReporte'])) ? $_POST['tipoReporte'] : 1);

        $this->nivel[1] = isset($_POST['checkNivel1']) ? $_POST['checkNivel1'] : -1;
        $this->nivel[2] = isset($_POST['checkNivel2']) ? $_POST['checkNivel2'] : -1;
        $this->nivel[3] = isset($_POST['checkNivel3']) ? $_POST['checkNivel3'] : -1;
        $this->nivel[4] = isset($_POST['checkNivel4']) ? $_POST['checkNivel4'] : -1;
        $this->nivel[5] = isset($_POST['checkNivel5']) ? $_POST['checkNivel5'] : -1;
        //de alguna manera aki se le asignara 'ctrl2012.' o 'ctrl2013.', segun sea el caso. para el 2014 se deja en blanco
        $this->ejercicio = '';
        //si el ejercicio es diferente al 2014, entonces se le agregara el sufijo 'Balanza' a las consultas
        if ($this->ejercicio != '') {
            $this->balanza = 'Balanza';
            $this->anio = 'xxx';
        } else {//en caso contrario no lleva nada
            $this->balanza = '';
            $this->anio = substr($_SESSION[_S]['ejercicio'], 0, 4);
        }

        if ($this->ejercicio == '') {
            $this->ur = new UnidadResponsable();
            $this->ur->setGpo_id($this->gpo_id);
            $this->ur->setUr_id($this->ur_id);
            $this->ur->select();
        } else {
            //obtenemos los datos de la UR del ejercicio solicitado
        }

    }

    public function Footer($lastPage = true)
    {

        $this->AddFont('univiapro', '');
        $this->AddFont('univiapro', 'B');
        $this->SetTextColor(0);
        $this->SetMargins(15, '', '', '');
        date_default_timezone_set('America/Mexico_City');
        $tmpb = $this->GetBorders();
        // $this->SetY(-20);
        if ($this->leyenda == 1 || ($this->cargo1 != '' && $this->cargo2 != '' && $this->firmante1 != '' && $this->firmante2 != '')) {
            $this->SetY(-30);
            $this->Cell(190, 2, utf8Decode(''), 0, 0, 'C');
            $this->Ln(5);
        } else {
            $this->SetY(-25);
        }

        if ($lastPage) {
            // $this->Cell(190,2,utf8Decode(''),0,0,'C');
            // $this->Ln(5);
            if ($this->leyenda == 1) {
                $this->SetFont('univiapro', '', 7);
                if ($_SESSION[_S]['reportes'] == 'C96' && $this->mesDesde >= 1 && $this->mesHasta <= 3 && ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 122 && $_SESSION[_S]['nvj_id'] == 2)) {
                    $this->Cell(270, 2, utf8Decode('Lugar y fecha de generación: Reyes Mantecón, Oaxaca'), 0, 0, 'C');
                } else {
                    $this->Cell(270, 2, utf8Decode('Lugar y fecha de generación: Reyes Mantecón, Oaxaca; a ' . date('d') . ' de ' . MesNombre(date('m')) . ' de ' . date('Y')), 0, 0, 'C');
                }
                $this->Ln(2);
                $this->Cell(270, 2, utf8Decode('Bajo protesta de decir verdad declaramos que los estados financieros y sus Notas son razonablemente correctos y responsabilidad del emisor.'), 0, 0, 'C');
                $this->Ln(1);
            }
            $this->SetFont('univiapro', 'B', 7);
            $y = $this->GetY();

            $this->Ln(2);
            $this->MultiAlignCell(97, 3, utf8Decode($this->cargo1), 0, 0, 'C');
            $this->MultiAlignCell(77, 3, utf8Decode($this->cargo2), 0, 0, 'C');
            $this->MultiAlignCell(97, 3, utf8Decode($this->cargo3), 0, 1, 'C');
            $this->Ln(4);
            $this->SetFont('univiapro', '', 7);
            $this->MultiAlignCell(97, 3, utf8Decode($this->firmante1), 0, 0, 'C');
            $this->MultiAlignCell(77, 3, utf8Decode($this->firmante2), 0, 0, 'C');
            $this->MultiAlignCell(97, 3, utf8Decode($this->firmante3), 0, 1, 'C');
        }

        // $this->Line(12,190,280,190);

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
        $this->SetFont('univiapro', '', 5);
        $hora = $current['hours'] . ':' . $minutes . ':' . $seconds;
        // $this->Cell(10,10,$hora,0,-2,"R");
        $this->Cell(275, 10, utf8Decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');

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
        // $this->Cell(10,10,$fecha,0,2,"L");

        $this->SetBorders(0);

    }



    // Marcos: 22/mar/2018
    // Intentaremos estandarizar la impresión del logo y las líneas de encabezado, para no estar inventando
    // el hilo negro en cada reporte.php
    // $orientacion: P para portrait, L para landscape
    public function drawHeadersAndLogo($orientacion, $linea1 = '', $linea2 = '', $linea3 = '', $linea4 = '', $linea5 = '', $linea6 = '', $linea7 = '')
    {

        $this->ancho = 283; // 297 que es el ancho del tamaño carta apaisada - 10

        $this->anchoImagen = 74;
        $this->altoImagen = 22;
        $this->logo = ($this->ancho - $this->anchoImagen); //213;

        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->SetMargins(20, 10, 20);
        $this->SetXY(20, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);

        $nLineas = 0;


        // Dibujamos el logo
        //$this->Image('../../../../img/logosefin-color-fblanco.jpg', $this->logo, $iy, $this->anchoImagen, $this->altoImagen, 'jpg');
        $this->Image('../../../../img/logoOaxaca-color-fblanco.png', $this->logo, $iy, $this->anchoImagen, $this->altoImagen, 'png');
        // Dibujamos las líneas de encabezado
        $this->SetFont('univiapro', 'B', 7);

        if (trim($linea1) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea1, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea2) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea2, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea3) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea3, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea4) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea4, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea5) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea5, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea6) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea6, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea7) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea7, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }

        // Dejamos un espacio para la siguiente sección
        if ($nLineas <= 4) {
            $this->SetY($this->GetY() + 2);
            $this->ln(5);
        }

        // Límite para asegurar la buena visilibidad del reporte y el correcto aprovechamiento de pantalla
        // Si sale sobreencimado con los encabezados de columna, tal vez haya que revisar las líneas del encabezado
        $this->setY(35);

    }







    public function Header()
    {

        $this->AddFont('univiapro', '');
        $this->AddFont('univiapro', 'B');

        if ($_SESSION[_S]['reportes'] != 'C96') {
            $linea1 = utf8Decode('Cuenta de la Hacienda Pública Estatal ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
        }
        if ($_SESSION[_S]['nvj_id'] > 2) {
            $linea2 = utf8Decode('Secretaría de Finanzas del Gobierno del Estado de Oaxaca');
        } else {
            $linea2 = utf8Decode('Gobierno del Estado de Oaxaca');
        }

        if ($_SESSION[_S]['nvj_id'] > 2) {
            switch ($this->tipoReporte) {
                case 2:   // Todas las URs de la carga de trabajo
                    $texto = utf8Decode('Dirección de Contabilidad Gubernamental');
                    break;
                case 3:   // Determinar qué incluir y qué excluir
                    $this->SetXY($ix + 15, $iy + 6);
                    $ytmp = $this->GetY();
                    $this->Cell(235, 3, utf8Decode('Dirección de Contabilidad Gubernamental'), 0, 0, 'C');
                    $texto = utf8Decode(isset($_POST['nombreEspecial']) ? $_POST['nombreEspecial'] : 'ERROR DE PROGRAMA. NO EXISTE ENCABEZADO');
                    break;
                default:
                    if ($this->ejercicio == '') {
                        $texto = utf8Decode('Dirección de Contabilidad Gubernamental: ' . $this->ur->getClave() . ' - ' . utf8_encode($this->ur->getNombre()));
                    } else {
                        $texto = utf8Decode('Dirección de Contabilidad Gubernamental: ') . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . utf8_encode($this->ur['ur_nombre']);
                    }
                    break;

            }

        } else {

            if ($this->ejercicio == '') {
                if (mb_detect_encoding($this->ur->getNombre(), 'auto') == 'UTF-8') {
                    $texto = ('UR: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre());
                } else {
                    $texto = utf8Decode('UR: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre());
                }
            } else {
                $texto = utf8Decode('UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . ($this->ur['ur_nombre']));
            }

        }

        $texto2 = '';
        if ($this->ue != '' && $this->numUe > 1) {
            $ejecutora = new AccesoDatos();
            $ejecutora->conectar();
            $ejecutora->setSql('
					SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = ? AND ur_id = ? AND ue_id = ? ;
				');
            $ejecutora->consultar([$this->gpo_id,$this->ur_id,$this->ue]);
            $ejecutora->desconectar();
            $ue_id = $ejecutora->extraer();
            $texto2 = 'UE: ' . $ue_id['ue_id'] . ' - ' . $ue_id['ue_nombre'] . '';
        }

        $texto = TruncateCadena(trim($texto), $this->longitud);

        $gen = '';
        switch ($this->genero) {
            case 1:
                $gen = 'del Activo';
                break;
            case 2:
                $gen = 'del Pasivo';
                break;
            case 3:
                $gen = 'de la Hacienda Pública/Patrimonio';
                break;
            case 4:
                $gen = 'de Ingresos y Otros beneficios';
                break;
            case 5:
                $gen = 'de Gastos y Otras Perdidas';
                break;
        }

        if ($_POST['tipoReporte'] != 3 && $_SESSION[_S]['nvj_id'] > 2) {
            $linea3 = $texto;
        }

        if ($this->ue != '' && $_SESSION[_S]['nvj_id'] > 2) {
            $linea4 = utf8Decode($texto2);
        }

        $linea5 = utf8Decode('Estado Analítico ' . $gen . '');

        $mesDesde = (isset($_POST['comboMes']) && $_POST['comboMes'] != '') ? $_POST['comboMes'] : '';

        $sqlMes = ' select msc_nombre, msc_dias from cat_MesContable where msc_id= ? ;';
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sqlMes);
        $con->consultar([$mesDesde]);
        $con->desconectar();
        $rowMes = $con->extraer();

        $mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta'] != '') ? $_POST['comboMesHasta'] : '';

        $sqlMes2 = ' select msc_nombre, msc_dias from cat_MesContable where msc_id= ? ;';
        $con2 = new AccesoDatos();
        $con2->conectar();
        $con2->setSql($sqlMes2);
        $con2->consultar([$mesHasta]);
        $con2->desconectar();
        $rowMes2 = $con2->extraer();

        if ($this->mesDesde == 0 and $this->mesHasta == 0) {
            $linea6 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' de ' . $this->anio . '');
        } elseif ($this->mesHasta == $this->mesDesde) {
            $linea6 = utf8Decode('Del 1° al ' . $rowMes['msc_dias'] . ' de ' . strtolower($rowMes['msc_nombre']) . ' de ' . $this->anio . '');
        } else {
            if ($this->mesDesde == 0) {
                $linea6 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . $this->anio . '');
            } else {
                $linea6 = utf8Decode('Del 1° de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . $this->anio . '');
            }
        }


        if ($this->chkEnMiles == 1000) {
            $linea7 = '(Miles)';
        } else {
            $linea7 = '(Pesos)';
        }

        if ($_POST['tipoReporte'] == 3 || $_SESSION[_S]['nvj_id'] <= 2) {
            $linea3 = utf8Decode($texto);
            if ($this->ue != '' && $_SESSION[_S]['nvj_id'] <= 2) {
                $linea4 = utf8Decode($texto2);
            }
        }

        // Dibujamos el logo y las líneas del encabezado
        $this->drawHeadersAndLogo('L', $linea1, $linea2, $linea3, $linea4, $linea5, $linea6, $linea7);

        //Encabezado de la tabla
        // $this->SetFillColor(192,192,192);
        $this->setDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->setAltoLinea(3);

        $this->SetFont('univiapro', 'B', 9);
        $this->SetX(10);
        // $this->Cell(25,5,'CUENTA','LRT',0,'C',true);
        $this->Cell(121, 5, 'DESCRIPCION', 'LRT', 0, 'C', true);
        $this->Cell(30, 5, 'SALDO INICIAL', 'LRT', 0, 'C', true);
        $this->Cell(60, 5, 'PERIODO', 'LRT', 0, 'C', true);
        $this->Cell(30, 5, 'SALDO FINAL', 'LRT', 0, 'C', true);
        $this->Cell(30, 5, 'FLUJO', 'LRT', 1, 'C', true);

        // $this->Cell(25,5,'','LRB',0,'C',true);
        $this->SetX(10);
        $this->Cell(121, 5, '', 'LRB', 0, 'C', true);
        $this->Cell(30, 5, '', 1, 0, 'C', true);
        $this->Cell(30, 5, 'CARGOS', 1, 0, 'C', true);
        $this->Cell(30, 5, 'ABONOS', 1, 0, 'C', true);
        $this->Cell(30, 5, '', 1, 0, 'C', true);
        $this->Cell(30, 5, '', 'LRB', 1, 'C', true);

    }

}


$pdf = new reportetabla('L', 'mm', 'Letter');
$pdf->SetProtection(['print','annot-forms'], '', PDF_PASSWORD_OWNER_SCG);
$pdf->setOrientation('L');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->AddFont('univiapro', '');
$pdf->AddFont('univiapro', 'B');
$pdf->SetFont('univiapro', '', 9);
$pdf->SetFillColor(230, 230, 230);

$pdf->setBorders(true);

$fill = false;

$lineas = 1;
list($datos) = balanzaConsolidacion($pdf->gpo_id, $pdf->ur_id, $pdf->ue, $pdf->mesDesde, $pdf->mesHasta, $pdf->genero, $pdf->tipoReporte, $pdf->ejercicio, '', $pdf->grupos, $pdf->excluir, $pdf->ceros, $pdf->incCta);
// var_dump($datos);
$datos[$pdf->genero . '999']['cuenta'] = '';
$datos[$pdf->genero . '999']['nombre'] = 'TOTAL ' . $datos[$pdf->genero]['nombre'];
$datos[$pdf->genero . '999']['naturalezaCuenta'] = $datos[$pdf->genero]['naturalezaCuenta'];
$datos[$pdf->genero . '999']['debeI'] = $datos[$pdf->genero]['debeI'];
$datos[$pdf->genero . '999']['haberI'] = $datos[$pdf->genero]['haberI'];
$datos[$pdf->genero . '999']['debeM'] = $datos[$pdf->genero]['debeM'];
$datos[$pdf->genero . '999']['haberM'] = $datos[$pdf->genero]['haberM'];
$datos[$pdf->genero . '999']['nivel'] = $datos[$pdf->genero]['nivel'];

$naturalezaGenero = $datos[$pdf->genero]['naturalezaCuenta'];

$sort = [];
foreach ($datos as $i => $val) {
    $sort['indice'][$i] = str_pad($i, 5, '0', STR_PAD_RIGHT);
}
array_multisort($sort['indice'], SORT_ASC, $datos);
// var_dump($datos);
// die();
if ($datos) {

    foreach ($datos as $indice => $valor) {
        if ($pdf->nivel[$valor['nivel']] == 1) {
            // var_dump($indice);
            // if( $indice != 'total' ){

            //$valor['nombre'] = TruncateCadena( trim( $valor['nombre'] ), $pdf->longitud2 );
            $valor['nombre'] = TruncateCadena(utf8Decode(trim($valor['nombre'])), $pdf->longitud2);
            // if($valor['nivel']==2 )
            // $pdf->SetFont('univiapro','B',6);
            // else
            // $pdf->SetFont('univiapro','',6);
            $espacio = '';
            switch ($valor['nivel']) {
                case 2:
                    $pdf->SetFont('univiapro', 'B', 5.4);
                    $espacio = '   ';
                    break;
                case 3:
                    $pdf->SetFont('univiapro', '', 6);
                    $espacio = '      ';
                    break;
                case 4:
                    $pdf->SetFont('univiapro', '', 6);
                    $espacio = '         ';
                    break;
                case 5:
                    $pdf->SetFont('univiapro', '', 6);
                    $espacio = '            ';
                    break;
                default:
                    $pdf->SetFont('univiapro', '', 6);
                    break;
            }

            $pdf->SetX(10);
            // $pdf->Cell(25,5, $valor['cuenta'],'LR',0,'L',$fill);
            if ($valor['cuenta'] != '') {
                $pdf->Cell(121, $pdf->intLin, $espacio . substr($valor['nombre'], 0, $pdf->longitud2), 'LR', 0, 'L', $fill);
            }

            if ($valor['naturalezaCuenta'] == 'D') {
                $inicial = ($valor['debeI'] - $valor['haberI']) / $pdf->chkEnMiles;
                $final = $inicial + ($valor['debeM'] - $valor['haberM']) / $pdf->chkEnMiles;
                $flujo = $final - $inicial;
            } else {
                $inicial = ($valor['haberI'] - $valor['debeI']) / $pdf->chkEnMiles;
                $final = $inicial + ($valor['haberM'] - $valor['debeM']) / $pdf->chkEnMiles;
                $flujo = $final - $inicial;
            }

            if ($valor['nivel'] >= 3) {
                $inicial = (($valor['naturalezaCuenta'] != $naturalezaGenero) ? $inicial * -1 : $inicial);
                $final = (($valor['naturalezaCuenta'] != $naturalezaGenero) ? $final * -1 : $final);
            }

            if ($valor['nivel'] != 1) {

                if ($valor['nivel'] == 2) {
                    $pdf->SetFont('univiapro', 'B', 6);
                } else {
                    $pdf->SetFont('univiapro', '', 6);
                }

                if ($_SESSION[_S]['reportes'] == 'C116') {
                    $pdf->Cell(30, $pdf->intLin, ((bccomp($inicial, 0, 2)) ? number_format(round($inicial, 0), 0) : '---'), 'LR', 0, 'R', $fill);
                    $pdf->Cell(30, $pdf->intLin, ((bccomp($valor['debeM'], 0, 2)) ? number_format(round($valor['debeM'] / $pdf->chkEnMiles, 0), 0) : '---'), 'LR', 0, 'R', $fill);
                    $pdf->Cell(30, $pdf->intLin, ((bccomp($valor['haberM'], 0, 2)) ? number_format(round($valor['haberM'] / $pdf->chkEnMiles, 0), 0) : '---'), 'LR', 0, 'R', $fill);
                    $pdf->Cell(30, $pdf->intLin, ((bccomp($final, 0, 2)) ? number_format(round($final, 0), 0) : '---'), 'LR', 0, 'R', $fill);
                    $pdf->Cell(30, $pdf->intLin, ((bccomp($flujo, 0, 2)) ? number_format(round($flujo, 0), 0) : '---'), 'LR', 1, 'R', $fill);
                } else {
                    $pdf->Cell(30, $pdf->intLin, ((bccomp($inicial, 0, 2)) ? number_format($inicial, 2) : '---'), 'LR', 0, 'R', $fill);
                    $pdf->Cell(30, $pdf->intLin, ((bccomp($valor['debeM'], 0, 2)) ? number_format($valor['debeM'] / $pdf->chkEnMiles, 2) : '---'), 'LR', 0, 'R', $fill);
                    $pdf->Cell(30, $pdf->intLin, ((bccomp($valor['haberM'], 0, 2)) ? number_format($valor['haberM'] / $pdf->chkEnMiles, 2) : '---'), 'LR', 0, 'R', $fill);
                    $pdf->Cell(30, $pdf->intLin, ((bccomp($final, 0, 2)) ? number_format($final, 2) : '---'), 'LR', 0, 'R', $fill);
                    $pdf->Cell(30, $pdf->intLin, ((bccomp($flujo, 0, 2)) ? number_format($flujo, 2) : '---'), 'LR', 1, 'R', $fill);
                }
            } else {
                if ($valor['cuenta'] != '') {
                    $pdf->Cell(30, $pdf->intLin, '', 'LR', 0, 'R', $fill);
                    $pdf->Cell(30, $pdf->intLin, '', 'LR', 0, 'R', $fill);
                    $pdf->Cell(30, $pdf->intLin, '', 'LR', 0, 'R', $fill);
                    $pdf->Cell(30, $pdf->intLin, '', 'LR', 0, 'R', $fill);
                    $pdf->Cell(30, $pdf->intLin, '', 'LR', 1, 'R', $fill);
                }
            }
            $lineas++;
            for ($con = 1; strlen($valor['nombre']) > $pdf->longitud2 * $con; $con++) {
                if ($lineas > 29) {
                    $pdf->Cell(262, 1, '', 'T', 1);
                    $pdf->SetFillColor(230, 230, 230);
                    $pdf->SetTextColor(0);
                    $pdf->AddPage();
                    $lineas = 1;
                }

                // if($valor['nivel']==2 )
                // $pdf->SetFont('univiapro','B',6);
                // else
                // $pdf->SetFont('univiapro','',6);

                switch ($valor['nivel']) {
                    case 2:
                        $pdf->SetFont('univiapro', 'B', 5.4);
                        $espacio = '   ';
                        break;
                    case 3:
                        $pdf->SetFont('univiapro', '', 6);
                        $espacio = '      ';
                        break;
                    case 4:
                        $pdf->SetFont('univiapro', '', 6);
                        $espacio = '         ';
                        break;
                    case 5:
                        $pdf->SetFont('univiapro', '', 6);
                        $espacio = '            ';
                        break;
                    default:
                        $pdf->SetFont('univiapro', '', 6);
                        break;
                }

                // $pdf->Cell(25,5, '','LR',0,'L',$fill);
                $pdf->SetX(10);
                $pdf->Cell(121, $pdf->intLin, $espacio . substr($valor['nombre'], $pdf->longitud2 * $con, $pdf->longitud2), 'LR', 0, 'L', $fill);
                $pdf->Cell(30, $pdf->intLin, '', 'LR', 0, 'R', $fill);
                $pdf->Cell(30, $pdf->intLin, '', 'LR', 0, 'R', $fill);
                $pdf->Cell(30, $pdf->intLin, '', 'LR', 0, 'R', $fill);
                $pdf->Cell(30, $pdf->intLin, '', 'LR', 0, 'R', $fill);
                $pdf->Cell(30, $pdf->intLin, '', 'LR', 1, 'C', $fill);
                $lineas++;
            }

            if ($lineas > 29) {
                $pdf->Cell(262, 1, '', 'T', 1);
                $pdf->SetFillColor(230, 230, 230);
                $pdf->SetTextColor(0);
                $pdf->AddPage();
                $lineas = 1;
            }

            $fill = !$fill;
            // }
        }
    }
    // die();
} else {
    $pdf->SetX(10);
    $pdf->Cell(262, 5, 'NO HAY REGISTROS', 1, 1, 'C');
}

$pdf->SetX(10);
$pdf->Cell(271, $pdf->intLin, '', 'T', 1, 'C');
$pdf->SetFont('univiapro', 'B', 6);
// $pdf->SetFillColor(105,105,105);
$pdf->setDrawColor(170, 170, 170);
$pdf->SetFillColor(199, 209, 219);
$pdf->setAltoLinea(3);
$pdf->SetBorders(true);

// $pdf->SetTextColor(255);
$pdf->SetX(10);
$pdf->Cell(87, $pdf->intLin, '');
$pdf->Cell(34, $pdf->intLin, $valor['nombre'], 1, 0, 'C', true);

if ($_SESSION[_S]['reportes'] == 'C116') {
    $pdf->Cell(30, $pdf->intLin, ((bccomp($inicial, 0, 2)) ? number_format(round($inicial, 0), 0) : '---'), 1, 0, 'R', true);
    $pdf->Cell(30, $pdf->intLin, ((bccomp($valor['debeM'], 0, 2)) ? number_format(round($valor['debeM'], 0), 0) : '---'), 1, 0, 'R', true);
    $pdf->Cell(30, $pdf->intLin, ((bccomp($valor['haberM'], 0, 2)) ? number_format(round($valor['haberM'], 0), 0) : '---'), 1, 0, 'R', true);
    $pdf->Cell(30, $pdf->intLin, ((bccomp($final, 0, 2)) ? number_format(round($final, 0), 0) : '---'), 1, 0, 'R', true);
    $pdf->Cell(30, $pdf->intLin, ((bccomp($flujo, 0, 2)) ? number_format(round($flujo,0), 0) : '---'), 1, 0, 'R', true);
} else {
    $pdf->Cell(30,$pdf->intLin , ((bccomp($inicial, 0, 2)) ? number_format($inicial,2) : '---'),1,0,'R',true);
    $pdf->Cell(30,$pdf->intLin , ((bccomp($valor['debeM'], 0, 2)) ? number_format($valor['debeM'],2) : '---'),1,0,'R',true);
    $pdf->Cell(30,$pdf->intLin , ((bccomp($valor['haberM'], 0, 2)) ? number_format($valor['haberM'],2) : '---'),1,0,'R',true);
    $pdf->Cell(30,$pdf->intLin , ((bccomp($final, 0, 2)) ? number_format($final,2) : '---'),1,0,'R',true);
    $pdf->Cell(30,$pdf->intLin , ((bccomp($flujo, 0, 2)) ? number_format($flujo,2) : '---'),1,0,'R',true);
}
//*/
$pdf->Output();

// Fin del código fuente
