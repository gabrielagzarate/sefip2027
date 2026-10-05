<?php

session_start();
//error_reporting("E_ERROR");
ini_set('memory_limit', '20M');

set_time_limit(0);

define('Ingresos', 4);
define('Gastos', 5);

/*ESTE REPORTE CORRESPONDE A LA OPCION DE ESTADO DE ACTIVIDADES.*/
require_once('../../../../ent/UnidadResponsable.php');
require_once('../../../../lib/scg/conciliacion/consolidacion.php');
require_once('../../../../lib/compararCadenas.php');
require_once('../../../../lib/MesNombre.php');
require_once('../../../lib/reportetabla.1.8.php');
require_once('../../../../lib/helpers.php');

//Extendiendo la clase pdf para escribir el encabezado y pie de página
class reportetabla extends PDF
{
    public $mesDesde;
    public $mesHasta;
    public $firmante1;
    public $firmante2;
    public $firmante3;
    public $cargo1;
    public $cargo2;
    public $cargo3;
    public $leyenda;
    public $ceros;
    public $incCta;
    public $intLin;
    public $ue;
    public $numUe;
    public $nivel;
    public $anchoImagen;
    public $altoImagen;
    public $nlineas;
    public $ur;
    public $ejercicio;
    public $ur_id;
    public $gpo_id;
    public function __construct()
    {
        parent::__construct();

        $this->mesDesde = isset($_POST['comboMesDesde']) ? $_POST['comboMesDesde'] : -1;//Informacion de acuerdo al mes
        $this->mesHasta = isset($_POST['comboMesHasta']) ? $_POST['comboMesHasta'] : -1;//Informacion de acuerdo al mes
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

        $_POST['tipoReporte'] = ((isset($_POST['tipoReporte'])) ? $_POST['tipoReporte'] : 1);

        $this->nivel[1] = isset($_POST['checkNivel1']) ? $_POST['checkNivel1'] : -1;
        $this->nivel[2] = isset($_POST['checkNivel2']) ? $_POST['checkNivel2'] : -1;
        $this->nivel[3] = isset($_POST['checkNivel3']) ? $_POST['checkNivel3'] : -1;
        $this->nivel[4] = isset($_POST['checkNivel4']) ? $_POST['checkNivel4'] : -1;
        $this->nivel[5] = isset($_POST['checkNivel5']) ? $_POST['checkNivel5'] : -1;
    }

    // FIRMAS
    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    public function Footer($lastPage = true)
    {
        $this->AddFont('univiapro', '');
        $this->AddFont('univiapro', 'B');
        $row = $this->getDatos();
        $tmpb = $this->GetBorders();
        // $this->SetY(-45);
        if ($this->leyenda == 1 || ($this->cargo1 != '' && $this->cargo2 != '' && $this->firmante1 != '' && $this->firmante2 != '')) {
            $this->SetY(-30);
            $this->Cell(190, 2, utf8Decode(''), 0, 0, 'C');
            $this->Ln(5);
        } else {
            $this->SetY(-25);
        }

        if ($lastPage) {
            if ($this->leyenda == 1) {
                $this->SetFont('univiapro', '', 7);
                if ($_SESSION[_S]['reportes'] == 'C93' && $this->mesDesde >= 1 && $this->mesHasta <= 3 && ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 122 && $_SESSION[_S]['nvj_id'] == 2)) {
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
        $this->SetFont('univiapro', '', 7);

        //pie de página
        $this->SetY(-11);
        $this->SetFont('univiapro', '', 5);
        // $this->Cell(10,10,date("H:i:s"),0,-2,"R");
        $this->Cell(275, 10, utf8Decode('Página') . $this->PageNo() . '/{nb}', 0, 0, 'C');
        // $this->Cell(240,10,date("d/m/y"),0,2,"L");
    }

    // Marcos: 22/mar/2020
    // Intentaremos estandarizar la impresión del logo y las líneas de encabezado, para no estar inventando
    // el hilo negro en cada reporte.php
    // $orientacion: P para portrait, L para landscape
    public function drawHeadersAndLogo($orientacion, $linea1 = '', $linea2 = '', $linea3 = '', $linea4 = '', $linea5 = '', $linea6 = '', $linea7 = '')
    {

        $this->ancho = 282; // 297 que es el ancho del tamaño carta apaisada - 10

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
        if ($nlineas <= 4) {
            $this->SetY($this->GetY() + 2);
            $this->ln(5);
        }

        // Límite para asegurar la buena visilibidad del reporte y el correcto aprovechamiento de pantalla
        // Si sale sobreencimado con los encabezados de columna, tal vez haya que revisar las líneas del encabezado
        $this->setY(35);

    }






    // ENCABEZADO
    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    public function Header()
    {
        /*SE IMPRIME EL ENCABEZADO DEL REPORTE.*/
        $this->AddFont('univiapro', '');
        $this->AddFont('univiapro', 'B');
        $this->ejercicio = '';
        $this->gpo_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['s24-gpo'];
        $this->ur_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['s24-ur'];

        if ($this->ejercicio == '') {
            $this->ur = new UnidadResponsable();
            $this->ur->setGpo_id($this->gpo_id);
            $this->ur->setUr_id($this->ur_id);
            $this->ur->select();
        }

        $row = $this->getDatos();

        if ($_SESSION[_S]['reportes'] != 'C93') {
            $linea1 = utf8Decode('Cuenta de la Hacienda Pública Estatal ' . substr($_SESSION[_S]['ejercicio'], 0, 4));

        }

        if ($_SESSION[_S]['nvj_id'] > 2) {
            $linea2 = utf8Decode('Secretaría de Finanzas del Gobierno del Estado de Oaxaca');
        } else {
            $linea2 = utf8Decode('Gobierno del Estado de Oaxaca');
        }

        if ($_SESSION[_S]['nvj_id'] > 2) {
            switch ($_POST['tipoReporte']) {
                case 2:   // Todas las URs de la carga de trabajo
                    $texto = 'Dirección de Contabilidad Gubernamental';
                    break;
                case 3:   // Determinar qué incluir y qué excluir
                    $texto = isset($_POST['nombreEspecial']) ? $_POST['nombreEspecial'] : 'ERROR DE PROGRAMA. NO EXISTE ENCABEZADO';
                    break;
                default:
                    if ($this->ejercicio == '') {
                        $texto = 'Dirección de Contabilidad Gubernamental: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre();
                    } else {
                        $texto = 'Dirección de Contabilidad Gubernamental: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . $this->ur['ur_nombre'];
                    }
                    break;

            }

        } else {

            if ($this->ejercicio == '') {
                $texto = 'UR: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre();
            } else {
                $texto = 'UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . $this->ur['ur_nombre'];
            }

        }

        $texto2 = '';
        if ($this->ue != '' && $this->numUe > 1) {
            $ejecutora = new AccesoDatos();
            $ejecutora->conectar();
            $ejecutora->setSql('
					SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = ' . $this->gpo_id . ' AND ur_id = ' . $this->ur_id . ' AND ue_id = ' . $this->ue . ';
				');
            $ejecutora->consultar();
            $ejecutora->desconectar();
            $ue_id = $ejecutora->extraer();
            $texto2 = 'UE: ' . $ue_id['ue_id'] . ' - ' . $ue_id['ue_nombre'] . '';
        }

        if ($_POST['tipoReporte'] != 3 && $_SESSION[_S]['nvj_id'] > 2) {
            $linea3 = utf8Decode($texto);
        }

        if ($this->ue != '' && $_SESSION[_S]['nvj_id'] > 2) {
            $linea4 = utf8Decode($texto2);
        }

        $linea5 = utf8Decode('Estado de Variación de la Hacienda Pública');

        $mesDesde = (isset($_POST['comboMesDesde']) && $_POST['comboMesDesde'] != '') ? $_POST['comboMesDesde'] : '';

        $sqlMes = ' select msc_nombre, msc_dias from cat_MesContable where msc_id=' . $mesDesde . ';';
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sqlMes);
        $con->consultar();
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

        if ($_SESSION[_S]['reportes'] == 'C93') {
            if ($this->mesDesde == 0 and $this->mesHasta == 0) {
                $linea6 = utf8Decode('Periodo: Del mes de ' . $rowMes['msc_nombre'] . ' ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
            } elseif ($this->mesHasta == $this->mesDesde) {
                $linea6 = utf8Decode('Periodo: Del 1° al ' . $rowMes['msc_dias'] . ' de ' . $rowMes['msc_nombre'] . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
            } else {
                if ($this->mesDesde == 0) {
                    $linea6 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                } else {
                    $linea6 = utf8Decode('Del 1° de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
                }
            }
        } else {
            if ($this->mesDesde == 0 and $this->mesHasta == 0) {
                $linea6 = utf8Decode('Periodo: Del mes de ' . $rowMes['msc_nombre'] . ' ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
            } elseif ($this->mesHasta == $this->mesDesde) {
                $linea6 = utf8Decode('Periodo: Del 1° al ' . $rowMes['msc_dias'] . ' de ' . $rowMes['msc_nombre'] . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
            } else {
                if ($this->mesDesde == 0) {
                    $linea6 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                } else {
                    $linea6 = utf8Decode('Del 1° de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
                }
            }
            // $this->Cell(145,3,utf8Decode('Del 1° de enero al 31 de diciembre de '.substr( $_SESSION[_S]['ejercicio'], 0, 4 ).' y '.substr( $_SESSION[_S]['ejercicio']-1, 0, 4 )),0,1,'C');
        }

        if (isset($_POST['MilPes'])) {
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

        $this->setRelleno('si');
        $this->SetFont('univiapro', 'B', 7);
        $this->SetTextColor(0, 0, 0);
        $this->setX(10);
        $this->setDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->setAltoLinea(3);

        $this->SetBorders(1);
        $this->setAltoLinea($this->intLin);

        if (isset($_POST['cc']) && $_POST['cc'] == 1) {
            $this->Cell(20, 5, '', 'LRT', 0, 'C', true);
            $this->Cell(50, 5, '', 'LRT', 0, 'C', true);
            $this->Cell(120, 5, utf8Decode('Hacienda Pública/ Patrimonio'), 'LRT', 0, 'C', true);
            $this->Cell(40, 5, '', 'LRT', 0, 'C', true);
            $this->Cell(40, 5, '', 'LRT', 1, 'C', true);

            $this->setX(10);
            $this->Cell(20, 5, 'Cuenta', 'LR', 0, 'C', true);
            $this->Cell(50, 5, 'Concepto', 'LR', 0, 'C', true);
            $this->Cell(40, 5, 'Contribuido', 1, 0, 'C', true);
            $this->Cell(80, 5, 'Generado', 1, 0, 'C', true);
            $this->Cell(40, 5, 'Ajustes por Cambios de Valor', 'LR', 0, 'C', true);
            $this->Cell(40, 5, 'Total', 'LR', 1, 'C', true);

            $this->setX(10);
            $this->Cell(20, 5, '', 'LRB', 0, 'C', true);
            $this->Cell(50, 5, '', 'LRB', 0, 'C', true);
            $this->Cell(40, 5, '', 1, 0, 'C', true);
            $this->Cell(40, 5, 'Ejerc. Ant.', 1, 0, 'C', true);
            $this->Cell(40, 5, 'Del Ejercicio', 1, 0, 'C', true);
            $this->Cell(40, 5, '', 'LRB', 0, 'C', true);
            $this->Cell(40, 5, '', 'LRB', 1, 'C', true);
        } else {
            // $this->Cell(20,5,'Cuenta','LRT',0,'C',true);
            $this->Cell(50, 5, '', 'LRT', 0, 'C', true);
            $this->Cell(132, 5, utf8Decode('Hacienda Pública/ Patrimonio'), 'LRT', 0, 'C', true);
            $this->Cell(44, 5, '', 'LRT', 0, 'C', true);
            $this->Cell(44, 5, '', 'LRT', 1, 'C', true);

            // $this->Cell(20,5,'','LRB',0,'C',true);
            $this->setX(10);
            $this->Cell(50, 5, 'Concepto', 'LR', 0, 'C', true);
            $this->Cell(44, 5, 'Contribuido', 1, 0, 'C', true);
            $this->Cell(88, 5, 'Generado', 1, 0, 'C', true);
            $this->Cell(44, 5, 'Ajustes por Cambios de Valor', 'LR', 0, 'C', true);
            $this->Cell(44, 5, 'Total', 'LR', 1, 'C', true);

            // $this->Cell(20,5,'','LRB',0,'C',true);
            $this->setX(10);
            $this->Cell(50, 5, '', 'LRB', 0, 'C', true);
            $this->Cell(44, 5, '', 1, 0, 'C', true);
            $this->Cell(44, 5, 'Ejerc. Ant.', 1, 0, 'C', true);
            $this->Cell(44, 5, 'Del Ejercicio', 1, 0, 'C', true);
            $this->Cell(44, 5, '', 'LRB', 0, 'C', true);
            $this->Cell(44, 5, '', 'LRB', 1, 'C', true);



        }

        // $this->setX(10);
        $this->setRelleno('no');
        $this->SetBorders('LR');

    }

}


// AQUI EMPIEZA LA LOGICA DEL REPORTE
// -----------------------------------------------------------------------------

$mesDesde = (isset($_POST['comboMesDesde']) != '') ? $_POST['comboMesDesde'] : -1;
$mesHasta = (isset($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta'] : -1;
$tipoReporte = (isset($_POST['tipoReporte']) != '') ? $_POST['tipoReporte'] : '';
$MilPesos = (isset($_POST['MilPes']) != '') ? 1000 : 1;
$grupos = isset($_POST['grupos']) ? $_POST['grupos'] : '';
$excluir = isset($_POST['excluir']) ? $_POST['excluir'] : '';
$cc = isset($_POST['cc']) && $_POST['cc'] == 1 ? $_POST['cc'] : '';
$ue = isset($_POST['ue']) ? $_POST['ue'] : '';

$pdf = new Reportetabla('L', 'mm', 'Letter');
$pdf->SetProtection(['print','annot-forms'], '', PDF_PASSWORD_OWNER_SCG);
// function consolidacion( $gpo_id, $ur_id, $mesInicial, $mesFinal, $genero, $tipoReporte, $ejercicio, $chkEnMiles = 0 )
list($vhApeDesde2022) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 0, $mesDesde - 1, 3, $tipoReporte, '', $MilPesos, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
list($vhDesdeHasta2022) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesDesde, $mesHasta, 3, $tipoReporte, '', $MilPesos, $grupos, $excluir, $pdf->ceros, $pdf->incCta);

list($vhApeDesde2020) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 0, $mesDesde - 1, 3, $tipoReporte, 2020, $MilPesos, $grupos, $excluir, $pdf->ceros, $pdf->incCta);
list($vhDesdeHasta2020) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesDesde, $mesHasta, 3, $tipoReporte, 2020, $MilPesos, $grupos, $excluir, $pdf->ceros, $pdf->incCta);


//cuerpo de la tabla
$pdf->setOrientation('L');
$pdf->AddFont('univiapro', '');
$pdf->AddFont('univiapro', 'B');
$pdf->SetFont('univiapro', '', 7);
$pdf->AliasNbPages();
$pdf->AddPage();
// 50,44,44,44,44,44
if ($cc != '') {
    $pdf->SetWidths([20,50,40,40,40,40,40]);
    $pdf->SetAligns(['L','L','R','R','R','R','R']);
} else {
    $pdf->SetWidths([50,44,44,44,44,44]);
    $pdf->SetAligns(['L','R','R','R','R','R']);
}

$keyApeDesde2022 = key($vhApeDesde2022);
$keyDesdeHasta2022 = key($vhDesdeHasta2022);
$final2022 = [];

while ($keyApeDesde2022 || $keyDesdeHasta2022) {

    if (CompararCadenas(( string )$keyApeDesde2022, ( string )$keyDesdeHasta2022) == 0) {


        $final2022[$keyApeDesde2022]['cuenta'] = $vhApeDesde2022[$keyApeDesde2022]['cuenta'];
        $final2022[$keyApeDesde2022]['nombre'] = $vhApeDesde2022[$keyApeDesde2022]['nombre'];
        $final2022[$keyApeDesde2022]['nivelCuenta'] = $vhApeDesde2022[$keyApeDesde2022]['nivel'];
        $final2022[$keyApeDesde2022]['naturalezaCuenta'] = $vhApeDesde2022[$keyApeDesde2022]['naturalezaCuenta'];
        $final2022[$keyApeDesde2022]['debe'] = $vhApeDesde2022[$keyApeDesde2022]['debe'] + $vhDesdeHasta2022[$keyApeDesde2022]['debe'];
        $final2022[$keyApeDesde2022]['haber'] = $vhApeDesde2022[$keyApeDesde2022]['haber'] + $vhDesdeHasta2022[$keyApeDesde2022]['haber'];
        $final2022[$keyApeDesde2022]['resultado'] = (($final2022[$keyApeDesde2022]['naturalezaCuenta'] == 'H') ? $final2022[$keyApeDesde2022]['haber'] - $final2022[$keyApeDesde2022]['debe'] : $final2022[$keyApeDesde2022]['debe'] - $final2022[$keyApeDesde2022]['haber']);

        next($vhApeDesde2022);
        $keyApeDesde2022 = key($vhApeDesde2022);

        next($vhDesdeHasta2022);
        $keyDesdeHasta2022 = key($vhDesdeHasta2022);

        // Marcos: 28/abr/2016
        // Se adicionó éste ELSE ya que si la consulta anterior no regresa nada nunca,
        // entonces nunca se ajecuta un NEXT y se queda en un ciclo
    } else {

        // Marcos: 28/abr/2016
        // Como el propósito era asignar valores, se valida que exista la variable y
        // si existe se asignan los valores correspondients
        if (isset($keyApeDesde2022) || (string)$keyApeDesde2022 != '') {

            $final2022[$keyApeDesde2022]['cuenta'] = $vhApeDesde2022[$keyApeDesde2022]['cuenta'];
            $final2022[$keyApeDesde2022]['nombre'] = $vhApeDesde2022[$keyApeDesde2022]['nombre'];
            $final2022[$keyApeDesde2022]['nivelCuenta'] = $vhApeDesde2022[$keyApeDesde2022]['nivel'];
            $final2022[$keyApeDesde2022]['naturalezaCuenta'] = $vhApeDesde2022[$keyApeDesde2022]['naturalezaCuenta'];
            $final2022[$keyApeDesde2022]['debe'] = $vhApeDesde2022[$keyApeDesde2022]['debe'];
            $final2022[$keyApeDesde2022]['haber'] = $vhApeDesde2022[$keyApeDesde2022]['haber'];
            $final2022[$keyApeDesde2022]['resultado'] = (($final2022[$keyApeDesde2022]['naturalezaCuenta'] == 'H') ? $final2022[$keyApeDesde2022]['haber'] - $final2022[$keyApeDesde2022]['debe'] : $final2022[$keyApeDesde2022]['debe'] - $final2022[$keyApeDesde2022]['haber']);

            next($vhApeDesde2022);
            $keyApeDesde2022 = key($vhApeDesde2022);
        }
        if (isset($keyDesdeHasta2022) || (string)$keyDesdeHasta2022 != '') {

            $final2022[$keyDesdeHasta2022]['cuenta'] = $vhDesdeHasta2022[$keyDesdeHasta2022]['cuenta'];
            $final2022[$keyDesdeHasta2022]['nombre'] = $vhDesdeHasta2022[$keyDesdeHasta2022]['nombre'];
            $final2022[$keyDesdeHasta2022]['nivelCuenta'] = $vhDesdeHasta2022[$keyDesdeHasta2022]['nivel'];
            $final2022[$keyDesdeHasta2022]['naturalezaCuenta'] = $vhDesdeHasta2022[$keyDesdeHasta2022]['naturalezaCuenta'];
            $final2022[$keyDesdeHasta2022]['debe'] = $vhDesdeHasta2022[$keyDesdeHasta2022]['debe'];
            $final2022[$keyDesdeHasta2022]['haber'] = $vhDesdeHasta2022[$keyDesdeHasta2022]['haber'];
            $final2022[$keyDesdeHasta2022]['resultado'] = (($final2022[$keyDesdeHasta2022]['naturalezaCuenta'] == 'H') ? $final2022[$keyDesdeHasta2022]['haber'] - $final2022[$keyDesdeHasta2022]['debe'] : $final2022[$keyDesdeHasta2022]['debe'] - $final2022[$keyDesdeHasta2022]['haber']);

            next($vhDesdeHasta2022);
            $keyDesdeHasta2022 = key($vhDesdeHasta2022);
        }

    }
}


$keyApeDesde2020 = key($vhApeDesde2020);
$keyDesdeHasta2020 = key($vhDesdeHasta2020);
$final2020 = [];

while ($keyApeDesde2020 || $keyDesdeHasta2020) {

    // echo "-> " . ( string )$keyApeDesde2020 . " >>> " . ( string )$keyDesdeHasta2020 . " == " . ( CompararCadenas ( ( string )$keyApeDesde2020, ( string )$keyDesdeHasta2020 ) == 0 ) . "     <br /><br />";

    if (CompararCadenas(( string )$keyApeDesde2020, ( string )$keyDesdeHasta2020) == 0) {

        $final2020[$keyApeDesde2020]['cuenta'] = $vhApeDesde2020[$keyApeDesde2020]['cuenta'];
        $final2020[$keyApeDesde2020]['nombre'] = $vhApeDesde2020[$keyApeDesde2020]['nombre'];
        $final2020[$keyApeDesde2020]['nivelCuenta'] = $vhApeDesde2020[$keyApeDesde2020]['nivel'];
        $final2020[$keyApeDesde2020]['naturalezaCuenta'] = $vhApeDesde2020[$keyApeDesde2020]['naturalezaCuenta'];
        $final2020[$keyApeDesde2020]['debe'] = $vhApeDesde2020[$keyApeDesde2020]['debe'] + $vhDesdeHasta2020[$keyApeDesde2020]['debe'];
        $final2020[$keyApeDesde2020]['haber'] = $vhApeDesde2020[$keyApeDesde2020]['haber'] + $vhDesdeHasta2020[$keyApeDesde2020]['haber'];
        $final2020[$keyApeDesde2020]['resultado'] = (($final2020[$keyApeDesde2020]['naturalezaCuenta'] == 'H') ? $final2020[$keyApeDesde2020]['haber'] - $final2020[$keyApeDesde2020]['debe'] : $final2020[$keyApeDesde2020]['debe'] - $final2020[$keyApeDesde2020]['haber']);

        next($vhApeDesde2020);
        $keyApeDesde2020 = key($vhApeDesde2020);

        next($vhDesdeHasta2020);
        $keyDesdeHasta2020 = key($vhDesdeHasta2020);

        // Marcos: 28/abr/2016
        // Se adicionó éste ELSE ya que si la consulta anterior no regresa nada nunca,
        // entonces nunca se ajecuta un NEXT y se queda en un ciclo
    } else {

        // Marcos: 28/abr/2016
        // Como el propósito era asignar valores, se valida que exista la variable y
        // si existe se asignan los valores correspondients
        if (isset($keyApeDesde2020) || (string)$keyApeDesde2020 != '') {

            $final2020[$keyApeDesde2020]['cuenta'] = $vhApeDesde2020[$keyApeDesde2020]['cuenta'];
            $final2020[$keyApeDesde2020]['nombre'] = $vhApeDesde2020[$keyApeDesde2020]['nombre'];
            $final2020[$keyApeDesde2020]['nivelCuenta'] = $vhApeDesde2020[$keyApeDesde2020]['nivel'];
            $final2020[$keyApeDesde2020]['naturalezaCuenta'] = $vhApeDesde2020[$keyApeDesde2020]['naturalezaCuenta'];
            $final2020[$keyApeDesde2020]['debe'] = $vhApeDesde2020[$keyApeDesde2020]['debe'];
            $final2020[$keyApeDesde2020]['haber'] = $vhApeDesde2020[$keyApeDesde2020]['haber'];
            $final2020[$keyApeDesde2020]['resultado'] = (($final2020[$keyApeDesde2020]['naturalezaCuenta'] == 'H') ? $final2020[$keyApeDesde2020]['haber'] - $final2020[$keyApeDesde2020]['debe'] : $final2020[$keyApeDesde2020]['debe'] - $final2020[$keyApeDesde2020]['haber']);

            next($vhApeDesde2020);
            $keyApeDesde2020 = key($vhApeDesde2020);
        }
        if (isset($keyDesdeHasta2020) || (string)$keyDesdeHasta2020 != '') {

            $final2020[$keyDesdeHasta2020]['cuenta'] = $vhDesdeHasta2020[$keyDesdeHasta2020]['cuenta'];
            $final2020[$keyDesdeHasta2020]['nombre'] = $vhDesdeHasta2020[$keyDesdeHasta2020]['nombre'];
            $final2020[$keyDesdeHasta2020]['nivelCuenta'] = $vhDesdeHasta2020[$keyDesdeHasta2020]['nivel'];
            $final2020[$keyDesdeHasta2020]['naturalezaCuenta'] = $vhDesdeHasta2020[$keyDesdeHasta2020]['naturalezaCuenta'];
            $final2020[$keyDesdeHasta2020]['debe'] = $vhDesdeHasta2020[$keyDesdeHasta2020]['debe'];
            $final2020[$keyDesdeHasta2020]['haber'] = $vhDesdeHasta2020[$keyDesdeHasta2020]['haber'];
            $final2020[$keyDesdeHasta2020]['resultado'] = (($final2020[$keyDesdeHasta2020]['naturalezaCuenta'] == 'H') ? $final2020[$keyDesdeHasta2020]['haber'] - $final2020[$keyDesdeHasta2020]['debe'] : $final2020[$keyDesdeHasta2020]['debe'] - $final2020[$keyDesdeHasta2020]['haber']);

            next($vhDesdeHasta2020);
            $keyDesdeHasta2020 = key($vhDesdeHasta2020);
        }

    }
}

// 	echo var_dump( $final2020 );
// 	echo "<br />";echo "<br />";echo "<br />";echo "<br />";echo "<br />";echo "<br />";
// 	echo var_dump( $final2022 );
// die();

$c1 = [31100,31200,31300];
$c2 = [32510,32520];
$c3 = [32100,32200,323,324];

$arrayColum11 = [32500 => ['resultado11' => 0,'resultado44' => 0]];
$arrayColum22 = [31000 => ['resultado22' => 0,'resultado44' => 0]];
$arrayColum33 = [32000 => ['resultado33' => 0,'resultado44' => 0]];

foreach ($final2020 as $i => $v) {

    if (in_array($i, $c2)) {
        // if( $v['cuenta']=='3.2.5.1.0'){
        $arrayColum11[32500]['cuenta'] = '';
        $arrayColum11[32500]['nombre'] = 'Rectificaciones de Resultados de Ejercicios Anteriores';
        $arrayColum11[32500]['nivelCuenta'] = 1;
        $arrayColum11[32500]['resultado11'] += $v['resultado'];
        $arrayColum11[32500]['resultado44'] += $v['resultado'];
        $arrayColum11[32500]['anio'] = 2020;
        // }

        $arrayColum11[$i]['cuenta'] = $v['cuenta'];
        $arrayColum11[$i]['nombre'] = $v['nombre'];
        $arrayColum11[$i]['nivelCuenta'] = $v['nivelCuenta'];
        $arrayColum11[$i]['resultado11'] = $v['resultado'];
        $arrayColum11[$i]['resultado44'] = $v['resultado'];
        $arrayColum11[$i]['anio'] = 2020;

    } elseif (in_array($i, $c1)) {
        // if($i==31100){
        $arrayColum22[31000]['cuenta'] = '';
        $arrayColum22[31000]['nombre'] = 'Patrimonio Neto Inicial Ajustado del Ejercicio';
        $arrayColum22[31000]['nivelCuenta'] = 1;
        $arrayColum22[31000]['resultado22'] += $v['resultado'];
        $arrayColum22[31000]['resultado44'] += $v['resultado'];
        $arrayColum22[31000]['anio'] = 2020;
        // }
        $arrayColum22[$i]['cuenta'] = $v['cuenta'];
        $arrayColum22[$i]['nombre'] = $v['nombre'];
        $arrayColum22[$i]['nivelCuenta'] = $v['nivelCuenta'];
        $arrayColum22[$i]['resultado22'] = $v['resultado'];
        $arrayColum22[$i]['resultado44'] = $v['resultado'];
        $arrayColum22[$i]['anio'] = 2020;
    } elseif (in_array($i, $c3)) {

        // if($i==32100){
        $arrayColum33[32000]['cuenta'] = '';
        $arrayColum33[32000]['nombre'] = 'Variaciones de la Hacienda Pública / Patrimonio Neto del Ejercicio';
        $arrayColum33[32000]['nivelCuenta'] = 1;
        $arrayColum33[32000]['resultado33'] += $v['resultado'];
        $arrayColum33[32000]['resultado44'] += $v['resultado'];
        $arrayColum33[32000]['anio'] = 2020;
        // }
        $arrayColum33[$i]['cuenta'] = $v['cuenta'];
        $arrayColum33[$i]['nombre'] = $v['nombre'];
        $arrayColum33[$i]['nivelCuenta'] = $v['nivelCuenta'];
        $arrayColum33[$i]['resultado33'] = $v['resultado'];
        $arrayColum33[$i]['resultado44'] = $v['resultado'];
        $arrayColum33[$i]['anio'] = 2020;

    }


}

$arrayColum11[32600]['cuenta'] = '';
$arrayColum11[32600]['nombre'] = '';
$arrayColum11[32600]['nivelCuenta'] = 1;
$arrayColum11[32600]['resultado11'] = '';
$arrayColum11[32600]['resultado44'] = '';
$arrayColum11[32600]['anio'] = 2020;

$arrayColum22[31400]['cuenta'] = '';
$arrayColum22[31400]['nombre'] = '';
$arrayColum22[31400]['nivelCuenta'] = 1;
$arrayColum22[31400]['resultado22'] = '';
$arrayColum22[31400]['resultado44'] = '';
$arrayColum22[31400]['anio'] = 2020;

$arrayColum33[32500]['cuenta'] = '';
$arrayColum33[32500]['nombre'] = '';
$arrayColum33[32500]['nivelCuenta'] = 1;
$arrayColum33[32500]['resultado33'] = '';
$arrayColum33[32500]['resultado44'] = '';
$arrayColum33[32500]['anio'] = 2020;

$arrayColum33[33000]['cuenta'] = '';
$arrayColum33[33000]['nombre'] = 'Hacienda Pública / Patrimonio Neto Final del Ejercicio ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4);
$arrayColum33[33000]['nivelCuenta'] = 1;
$arrayColum33[33000]['resultado11'] = $arrayColum11[32500]['resultado11'];
$arrayColum33[33000]['resultado22'] = $arrayColum22[31000]['resultado22'];
$arrayColum33[33000]['resultado33'] = $arrayColum33[32000]['resultado33'];
$arrayColum33[33000]['resultado44'] = $arrayColum22[31000]['resultado22'] + $arrayColum11[32500]['resultado11'] + $arrayColum33[32000]['resultado33'];
$arrayColum33[33000]['anio'] = 2020;

$arrayColum33[33100]['cuenta'] = '';
$arrayColum33[33100]['nombre'] = '';
$arrayColum33[33100]['nivelCuenta'] = 1;
$arrayColum33[33100]['resultado11'] = '';
$arrayColum33[33100]['resultado22'] = '';
$arrayColum33[33100]['resultado33'] = '';
$arrayColum33[33100]['resultado44'] = '';
$arrayColum33[33100]['anio'] = 2020;


$total_array2020 = array_merge($arrayColum11, $arrayColum22, $arrayColum33);

$ajustes2022 = 0;

// Marcos: 28/abr/2016
// Como no existía la variable, se adicionó un isset() y un else para asignarle un valor default
if (isset($final2022[3251])) {
    $cuenta3251 = (($final2022[3251]['naturalezaCuenta'] == 'D') ? $final2022[3251]['debe'] - $final2022[3251]['haber'] : $final2022[3251]['haber'] - $final2022[3251]['debe']);
} else {
    $cuenta3251 = 0;
}

// Marcos: 28/abr/2016
// Como no existía la variable, se adicionó un isset() y un else para asignarle un valor default
if (isset($final2022[3252])) {
    $cuenta3252 = (($final2022[3252]['naturalezaCuenta'] == 'D') ? $final2022[3252]['debe'] - $final2022[3252]['haber'] : $final2022[3252]['haber'] - $final2022[3252]['debe']);
} else {
    $cuenta3252 = 0;
}

$ajustes2022 = ($cuenta3251 + $cuenta3252);


$arrayColum1 = [31000 => ['resultado22' => 0,'resultado44' => 0]];

$arrayColum3 = [32000 => ['resultado33' => 0,'resultado44' => 0]];

// Marcos: 28/abr/2016
// Como no existía la variable, se adicionó un isset() y un else para asignarle un valor default
if (!isset($arrayColum3[323])) {
    $arrayColum3[323]['resultado44'] = 0;
}

foreach ($final2022 as $i => $v) {

    if (in_array($i, $c1)) {

        $arrayColum1[31000]['cuenta'] = '';
        $arrayColum1[31000]['nombre'] = 'Cambios en la Hacienda Pública / Patrimonio Neto del Ejercicio ' . substr($_SESSION[_S]['ejercicio'], 0, 4);
        $arrayColum1[31000]['nivelCuenta'] = 1;
        $arrayColum1[31000]['resultado22'] += $v['resultado'];
        $arrayColum1[31000]['resultado44'] += $v['resultado'];
        $arrayColum1[31000]['anio'] = substr($_SESSION[_S]['ejercicio'] - 1, 0, 4);

        $arrayColum1[$i]['cuenta'] = $v['cuenta'];
        $arrayColum1[$i]['nombre'] = $v['nombre'];
        $arrayColum1[$i]['nivelCuenta'] = $v['nivelCuenta'];
        $arrayColum1[$i]['resultado22'] = $v['resultado'];
        $arrayColum1[$i]['resultado44'] = $v['resultado'];
        $arrayColum1[$i]['anio'] = substr($_SESSION[_S]['ejercicio'] - 1, 0, 4);

    } elseif (in_array($i, $c3)) {

        $arrayColum3[32000]['cuenta'] = '3.2.0.0.0';
        $arrayColum3[32000]['nombre'] = 'Variaciones de la Hacienda Pública / Patrimonio Neto del Ejercicio';
        $arrayColum3[32000]['nivelCuenta'] = 1;
        $arrayColum3[32000]['resultado33'] += $v['resultado'];
        $arrayColum3[32000]['resultado44'] += $v['resultado'];
        $arrayColum3[32000]['anio'] = substr($_SESSION[_S]['ejercicio'] - 1, 0, 4);

        $arrayColum3[$i]['cuenta'] = $v['cuenta'];
        $arrayColum3[$i]['nombre'] = $v['nombre'];
        $arrayColum3[$i]['nivelCuenta'] = $v['nivelCuenta'];
        $arrayColum3[$i]['resultado33'] = $v['resultado'];
        $arrayColum3[$i]['resultado44'] = $v['resultado'];
        $arrayColum3[$i]['anio'] = substr($_SESSION[_S]['ejercicio'] - 1, 0, 4);

    }

}

$arrayColum3[32000]['resultado44'] += $ajustes2022;
$arrayColum3[323]['resultado44'] += $ajustes2022;

$arrayColum1[31400]['cuenta'] = '';
$arrayColum1[31400]['nombre'] = '';
$arrayColum1[31400]['nivelCuenta'] = 1;
$arrayColum1[31400]['resultado22'] = '';
$arrayColum1[31400]['resultado44'] = '';
$arrayColum1[31400]['anio'] = substr($_SESSION[_S]['ejercicio'] - 1, 0, 4);

$arrayColum3[32500]['cuenta'] = '';
$arrayColum3[32500]['nombre'] = '';
$arrayColum3[32500]['nivelCuenta'] = 1;
$arrayColum3[32500]['resultado22'] = '';
$arrayColum3[32500]['resultado44'] = '';
$arrayColum3[32500]['anio'] = substr($_SESSION[_S]['ejercicio'] - 1, 0, 4);

$arrayColum3[33000]['cuenta'] = '3.3.0.0.0';
$arrayColum3[33000]['nombre'] = 'Saldo Neto en la Hacienda Pública / Patrimonio ' . substr($_SESSION[_S]['ejercicio'], 0, 4);
$arrayColum3[33000]['nivelCuenta'] = 1;
$arrayColum3[33000]['resultado22'] = $arrayColum1[31000]['resultado22'];
$arrayColum3[33000]['resultado11'] = 0;
$arrayColum3[33000]['resultado33'] = $arrayColum3[32000]['resultado33'];
$arrayColum3[33000]['resultado44'] = $arrayColum3[33000]['resultado11'] + $arrayColum3[33000]['resultado22'] + $arrayColum3[33000]['resultado33'] + $ajustes2022;
// $arrayColum3[33000]['resultado44'] = $arrayColum3[33000]['resultado11'] + $arrayColum3[33000]['resultado22'] + $arrayColum3[33000]['resultado33'] + $ajustes2022;
$arrayColum3[33000]['anio'] = substr($_SESSION[_S]['ejercicio'] - 1, 0, 4);


$total_array2022 = array_merge($arrayColum1, $arrayColum3);
if ($_SESSION[_S]['reportes'] != 'C93') {
    $total_array = array_merge($total_array2020, $total_array2022);
} else {
    $total_array = $total_array2022;
}
$max = count($total_array);

$cuentasTotales = [32500,31000,32000,33000];
for ($x = 0;$x <= $max;$x++) {

    $cuenta = '';
    $concepto = '';
    $columna1 = '';
    $columna2 = '';
    $columna3 = '';
    $columna4 = '';
    $columna5 = '';

    if (isset($total_array[$x])) {

        // Marcos: 28/abr/2016
        // Como no existía la variable, se adicionó un isset() para asignarle un valor default
        if (!isset($total_array[$x]['cuenta'])) {
            $total_array[$x]['cuenta'] = '';
        }

        if ($total_array[$x]['cuenta'] == '' || $total_array[$x]['cuenta'] == '3.2.0.0.0' || $total_array[$x]['cuenta'] == '3.3.0.0.0') {
            $pdf->SetFont('univiapro', 'B', 7);
        } else {
            $pdf->SetFont('univiapro', '', 7);
        }

        if ($_SESSION[_S]['reportes'] == 'C93') {

            if ($total_array[$x]['anio'] == substr($_SESSION[_S]['ejercicio'] - 1, 0, 4)) {
                $cuenta = $total_array[$x]['cuenta'];
                $concepto = utf8Decode($total_array[$x]['nombre']);

                if ($total_array[$x]['nombre'] != '') {
                    if ($_SESSION[_S]['reportes'] == 'C113') {
                        $columna1 = ((isset($total_array[$x]['resultado22'])) ? number_format(round($total_array[$x]['resultado22'] / $MilPesos, 0), 0) : number_format(0, 0));
                        $columna2 = ((isset($total_array[$x]['resultado11'])) ? number_format(round($total_array[$x]['resultado11'] / $MilPesos, 0), 0) : number_format(0, 0));
                        $columna3 = ((isset($total_array[$x]['resultado33'])) ? number_format(round($total_array[$x]['resultado33'] / $MilPesos, 0), 0) : number_format(0, 0));
                        $columna4 = ((($total_array[$x]['cuenta'] == '3.2.3' || $total_array[$x]['cuenta'] == '3.2.0.0.0' || $total_array[$x]['cuenta'] == '3.3.0.0.0') && $total_array[$x]['anio'] == substr($_SESSION[_S]['ejercicio'] - 1, 0, 4)) ? number_format(round($ajustes2022 / $MilPesos, 0), 0) : number_format(0, 0));
                        $columna5 = ((isset($total_array[$x]['resultado44'])) ? number_format(round($total_array[$x]['resultado44'] / $MilPesos, 0), 0) : number_format(0, 0));
                    } else {
                        $columna1 = ((isset($total_array[$x]['resultado22'])) ? number_format($total_array[$x]['resultado22'] / $MilPesos, 2) : number_format(0, 0));
                        $columna2 = ((isset($total_array[$x]['resultado11'])) ? number_format($total_array[$x]['resultado11'] / $MilPesos, 2) : number_format(0, 0));
                        $columna3 = ((isset($total_array[$x]['resultado33'])) ? number_format($total_array[$x]['resultado33'] / $MilPesos, 2) : number_format(0, 0));
                        $columna4 = ((($total_array[$x]['cuenta'] == '3.2.3' || $total_array[$x]['cuenta'] == '3.2.0.0.0' || $total_array[$x]['cuenta'] == '3.3.0.0.0') && $total_array[$x]['anio'] == substr($_SESSION[_S]['ejercicio'] - 1, 0, 4)) ? number_format($ajustes2022 / $MilPesos, 2) : number_format(0, 0));
                        $columna5 = ((isset($total_array[$x]['resultado44'])) ? number_format($total_array[$x]['resultado44'] / $MilPesos, 2) : number_format(0, 0));
                    }
                } else {
                    $columna1 = '';
                    $columna2 = '';
                    $columna3 = '';
                    $columna4 = '';
                    $columna5 = '';

                }

            }

        } else {

            $cuenta = $total_array[$x]['cuenta'];

            // Marcos: 28/abr/2016
            // Como no existía la variable, se adicionó un isset() para asignarle un valor default
            if (!isset($total_array[$x]['nombre'])) {
                $total_array[$x]['nombre'] = '';
            }

            $concepto = utf8Decode($total_array[$x]['nombre']);

            if ($total_array[$x]['nombre'] != '') {
                if ($_SESSION[_S]['reportes'] == 'C113') {
                    $columna1 = ((isset($total_array[$x]['resultado22'])) ? number_format(round($total_array[$x]['resultado22'] / $MilPesos, 0), 0) : number_format(0, 0));
                    $columna2 = ((isset($total_array[$x]['resultado11'])) ? number_format(round($total_array[$x]['resultado11'] / $MilPesos, 0), 0) : number_format(0, 0));
                    $columna3 = ((isset($total_array[$x]['resultado33'])) ? number_format(round($total_array[$x]['resultado33'] / $MilPesos, 0), 0) : number_format(0, 0));
                    $columna4 = ((($total_array[$x]['cuenta'] == '3.2.3' || $total_array[$x]['cuenta'] == '3.2.0.0.0' || $total_array[$x]['cuenta'] == '3.3.0.0.0') && $total_array[$x]['anio'] == substr($_SESSION[_S]['ejercicio'] - 1, 0, 4)) ? number_format(round($ajustes2022 / $MilPesos, 0), 0) : number_format(0, 0));
                    $columna5 = ((isset($total_array[$x]['resultado44'])) ? number_format(round($total_array[$x]['resultado44'] / $MilPesos, 0), 0) : number_format(0, 0));
                } else {
                    $columna1 = ((isset($total_array[$x]['resultado22'])) ? number_format($total_array[$x]['resultado22'] / $MilPesos, 2) : number_format(0, 0));
                    $columna2 = ((isset($total_array[$x]['resultado11'])) ? number_format($total_array[$x]['resultado11'] / $MilPesos, 2) : number_format(0, 0));
                    $columna3 = ((isset($total_array[$x]['resultado33'])) ? number_format($total_array[$x]['resultado33'] / $MilPesos, 2) : number_format(0, 0));
                    $columna4 = ((($total_array[$x]['cuenta'] == '3.2.3' || $total_array[$x]['cuenta'] == '3.2.0.0.0' || $total_array[$x]['cuenta'] == '3.3.0.0.0') && $total_array[$x]['anio'] == substr($_SESSION[_S]['ejercicio'] - 1, 0, 4)) ? number_format($ajustes2022 / $MilPesos,2) : number_format(0,0));
                    $columna5 = ((isset($total_array[$x]['resultado44'])) ? number_format($total_array[$x]['resultado44'] / $MilPesos,2) : number_format(0,0));
                }
            } else {
                $columna1 = '';
                $columna2 = '';
                $columna3 = '';
                $columna4 = '';
                $columna5 = '';

            }
        }

    }
    if ($pdf->getY() > 175) {
        $xtm = $pdf->getX();
        $ytm = $pdf->getY();
        $pdf->Line($xtm,$ytm,285,$ytm);
        $pdf->addPage();
    }

    $pdf->setAltoLinea($pdf->intLin);
    $pdf->SetFillColor(192,192,192);

    $pdf->setX(10);

    if ($cc != '') {
        $pdf->Row([ $cuenta, $concepto, $columna1, $columna2, $columna3, $columna4, $columna5 ],'LR');
    } else {
        $pdf->Row([ $concepto, $columna1, $columna2, $columna3, $columna4, $columna5 ],'LR');
    }

}

$pdf->setX(10);

$xtm = $pdf->getX();
$ytm = $pdf->getY();
$pdf->Line($xtm,$ytm,280,$ytm);


$pdf->Output();

// Fin del código fuente
