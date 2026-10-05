<?php

declare(strict_types=1);
/**
 * Description of pdf_Minuta
 *
 * @author ?
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 20-ago-2018
 * Comment:
 * Se cambia el nombre de la clase segun el nombre del archivo.
 * Objective:  Generar Reporte de : Minuta
 * Regla de Negocio:
 * Esta pantalla no pregunta por permisos de escritura ó lectura
 *
 * */

session_start();
ini_set('memory_limit', '20M');
set_time_limit(0);
require_once dirname(__FILE__) . '/../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../lib/TruncateCadena.php';
require_once dirname(__FILE__) . "/../../../lib/constantes.php";
require_once dirname(__FILE__) . "/../../../lib/helpers.php";

$gpo = $_SESSION[_S]['clasAdm-gpo'];
$ur = $_SESSION[_S]['clasAdm-ur'];
$fe = $_POST['fe'];
$observaciones = mb_strtoupper($_POST['observaciones'], 'UTF-8');

$entrega = mb_strtoupper($_POST['trato1'] . ' C. ' . $_POST['entrega'], 'UTF-8');
$recibe  = mb_strtoupper($_POST['trato2'] . ' C. ' . $_POST['recibe'], 'UTF-8');

//Extendiendo la clase pdf para escribir el encabezado y pie de página
class PdfMinuta extends PDF
{
    public $ur;
    public function  __construct()
    {
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();
        parent::__construct();
    }

    public $ultBorde = 0;
    var $widths = '';

    function Footer()
    {
        $this->SetY(-22);
        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
        $this->SetBorders(1);

        $nomUr = '';
        $nomUr2 = '';
        $nombre = explode(" ", strtoupper($this->ur->getNombre()));
        $linea2 = false;
        foreach ($nombre as $palabras) {
            if (strlen($nomUr . $palabras) < 55 and !$linea2) {
                $nomUr .= $palabras . ' ';
            } else {
                $linea2 = true;
                $nomUr2 .= $palabras . ' ';
            }
        }

        $this->Cell(92.5, 2, utf8decode('ENTREGA POR ' . $nomUr . ''), 0, 0, 'C');
        $this->Cell(92.5, 2, utf8decode('RECIBE POR LA DIRECCIÓN DE PRESUPUESTO DE LA SEFIN'), 0, 1, 'C');

        $this->Cell(92.5, 2, utf8decode($nomUr2), 0, 0, 'C');
        $this->Cell(92.5, 2, '', 0, 1, 'C');

        $this->Cell(92.5, 2, '', 0, 0, 'C');
        $this->Cell(92.5, 2, '', 0, 1, 'C');
        $this->SetFont('UniviaPro-Regular', '', 6.5);
        $entrega = mb_strtoupper($_POST['trato1'] . ' C. ' . $_POST['entrega'], 'UTF-8');
        $recibe = mb_strtoupper($_POST['trato2'] . ' C. ' . $_POST['recibe'], 'UTF-8');
        $this->Cell(92.5, 2, utf8decode($entrega), 0, 0, 'C');
        $this->Cell(92.5, 2, utf8decode($recibe), 0, 1, 'C');

        $this->ln();
        $this->SetFont('univiapro', '', 5);
        $y = -7;
        $x = 0;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(57, 2, utf8decode("" . " - " . "" . " "), 0, 'L');

        $this->Cell(0, 0, utf8decode('Página ' . $this->PageNo() . '/{nb}'), 0, 0, 'C');
        $this->Cell(0, 0, date("d/m/y") . ' - ' . date("H:i:s") . '        ', 1, 0, 'R');
    }

    function Header()
    {
        $this->SetMargins(10, 1, 1, 1);
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        //Encabezado del documento
        $ix = $this->GetX();
        $iy = $this->GetY();

        $this->setRouteImg(" ");
        $this->Image($this->getRouteImg(), $ix + 96, $iy + 1, '95', '20', 'PNG', '');

        $this->SetFont('UniviaPro-Bold', 'B', 16);
        $this->SetTextColor(200, 200, 200);
        $this->RotatedText($ix + 5, $iy + 18, APP_EJERCICIO, 90);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->SetXY($ix + 7, $iy + 4);
        $ytmp = $this->GetY();
        $this->Cell(195, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(180, 5, utf8decode('SECRETARÍA DE FINANZAS'), 0, 1, 'L');

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->Cell(180, 5, utf8decode('PROCESO DE PLANEACIÓN - PRESUPUESTACIÓN '. APP_EJERCICIO), 0, 1, 'L');

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->Cell(180, 6, utf8decode('MINUTA DE ENTREGA-RECEPCIÓN DEL PROGRAMA'), 0, 1, 'L');

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->Cell(180, 6, utf8decode('OPERATIVO ANUAL '.APP_EJERCICIO), 0, 1, 'L');

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->Cell(5, 6, 'UR: ', 0, 0, 'L');
        $this->Cell(5, 6, ' ' . $this->ur->getClave(), 0, 0, 'L');
        $this->Cell(143, 6, '  -' . ' ' . utf8decode($this->ur->getNombre()), 0, 0, 'L');
        $this->Cell(15, 6, '', 0, 1, 'R');
        $this->SetBorders(0);
        $this->setDrawColor(200, 200, 200);
        $this->Line(10, 34, 199, 33);

        //Encabezado de la tabla
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $tmp = $this->GetBorders(0);
        $this->ln(2);
        $this->setY(35);
    }
}

$pdf = new PdfMinuta('P', 'mm', 'Letter');
$pdf->SetProtection(array('print', 'annot-forms'), "", PDF_PASSWORD_OWNER_POA);
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');

$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(false, 35);
$pdf->setAltoLinea(1.5);
//cuerpo de la tabla
$mes = array('ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE');
$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql('
    SELECT
        sum(pre_original) AS importe
    FROM presupuesto
        INNER JOIN presupuestoOriginal USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
    WHERE gpo_id = "' . $gpo . '"
        AND ur_id="' . $ur . '"
    GROUP BY gpo_id,ur_id;
');
$conexion->consultar();
$row = $conexion->extraer();

$longitud = 153;

/** 07-10-2019 - Se separaron por partes el texto del encabezado para poder realizar la justificación del texto*/
#$inicio = 'EN LA LOCALIDAD DE REYES MANTECON, SIENDO LAS ' . date("H:i") . ' HORAS DEL DÍA ' . date("d") . ' DEL MES DE ' . $mes[date("m") - 1] . ' DE DOS MIL DIECINUEVE, EN LA DIRECCIÓN DE PRESUPUESTO, DEPENDIENTE DE LA SUBSECRETARÍA DE EGRESOS,  CONTABILIDAD Y TESORERIA  DE LA SECRETARÍA DE FINANZAS, SITA EN EL EDIFICIO "D" SAÚL MARTÍNEZ, 3ER. PISO, UBICADO EN EL CENTRO ADMINISTRATIVO DEL PODER EJECUTIVO Y JUDICIAL "GENERAL PORFIRIO DÍAZ, SOLDADO DE LA PATRIA", AVENIDA GERARDO PANDAL GRAFF, NÚMERO 1 EN LA LOCALIDAD DE REYES MANTECON, MUNICIPIO DE SAN BARTOLO COYOTEPEC, OAXACA; SE ENCUENTRAN REUNIDOS ' . $entrega . ', REPRESENTANTE DE LA UNIDAD RESPONSABLE DENOMINADA "' . $pdf->ur->getNombre() . '"  Y ' . $recibe . ', REPRESENTANTE DE LA DIRECCION DE PRESUPUESTO, CON EL PROPÓSITO DE REALIZAR LA ENTREGA-RECEPCIÓN DEL PROGRAMA OPERATIVO ANUAL QUE SE INTEGRA AL ANTEPROYECTO DE PRESUPUESTO DE EGRESOS PARA EL EJERCICIO FISCAL 2020, POR UN IMPORTE TOTAL DE $ ' . number_format((float)$row['importe'], 2) . ' (' . num2letras($row['importe']) . '/100 M.N.), CONFORMADO POR LOS SIGUIENTES REPORTES:';
$parte1Encabezado = 'EN LA LOCALIDAD DE REYES MANTECON, SIENDO LAS ' . date("H:i") . ' HORAS DEL DÍA ' . date("d") . ' DEL MES DE ' . $mes[date("m") - 1] . ' DE DOS MIL VEINTICINCO, EN LA DIRECCIÓN DE PRESUPUESTO,';
$parte2Encabezado = 'DEPENDIENTE DE LA SUBSECRETARÍA DE EGRESOS,  CONTABILIDAD Y TESORERÍA  DE LA SECRETARÍA DE FINANZAS, SITA EN EL EDIFICIO "D" SAÚL MARTÍNEZ, 3ER. PISO,';
$parte3Encabezado = 'UBICADO EN EL CENTRO ADMINISTRATIVO DEL PODER EJECUTIVO Y JUDICIAL "GENERAL PORFIRIO DÍAZ, SOLDADO DE LA PATRIA", AVENIDA GERARDO';
$parte4Encabezado = 'PANDAL GRAFF, NÚMERO 1 EN LA LOCALIDAD DE REYES MANTECON, MUNICIPIO DE SAN BARTOLO COYOTEPEC, OAXACA; SE ENCUENTRAN REUNIDOS ';
$parte5Encabezado = '' . $entrega . ', REPRESENTANTE DE LA UNIDAD RESPONSABLE DENOMINADA';
$parte6Encabezado = ' "' . $pdf->ur->getNombre() . '" Y ' . $recibe . ', REPRESENTANTE DE LA ';
$parte7Encabezado = 'DIRECCION DE PRESUPUESTO, CON EL PROPÓSITO DE REALIZAR LA ENTREGA-RECEPCIÓN DEL PROGRAMA OPERATIVO ANUAL QUE SE INTEGRA AL';
$parte8Encabezado = 'ANTEPROYECTO DE PRESUPUESTO DE EGRESOS PARA EL EJERCICIO FISCAL '.APP_EJERCICIO.', POR UN IMPORTE TOTAL';
if(isset($row['importe'])){    
    $parte9Encabezado =  'DE $ ' . number_format((float)$row['importe'] ?? 0, 2) . ' (' . num2letras((float)$row['importe'] ?? 0) . '/100 M.N.), ';
}else{
    $parte9Encabezado =  'DE $ ' . number_format(0 ?? 0, 2) . ' (Cero /100 M.N.), ';
}
$parte10Encabezado = 'CONFORMADO  POR  LOS  SIGUIENTES  REPORTES:';

$textoEncabezado1 = TruncateCadena(utf8decode(trim($parte1Encabezado)), $longitud);
$textoEncabezado2 = TruncateCadena(utf8decode(trim($parte2Encabezado)), $longitud);
$textoEncabezado3 = TruncateCadena(utf8decode(trim($parte3Encabezado)), $longitud);
$textoEncabezado4 = TruncateCadena(utf8decode(trim($parte4Encabezado)), $longitud);
$textoEncabezado5 = TruncateCadena(utf8decode(trim($parte5Encabezado)), $longitud);
$textoEncabezado6 = TruncateCadena(utf8decode(trim($parte6Encabezado)), $longitud);
$textoEncabezado7 = TruncateCadena(utf8decode(trim($parte7Encabezado)), $longitud);
$textoEncabezado8 = TruncateCadena(utf8decode(trim($parte8Encabezado)), $longitud);
$textoEncabezado9 = TruncateCadena(utf8decode(trim($parte9Encabezado)), $longitud);
$textoEncabezado10 = TruncateCadena(utf8decode(trim($parte10Encabezado)), $longitud);

$textoEncabezado = array(
    1 => $textoEncabezado1,
    2 => $textoEncabezado2,
    3 => $textoEncabezado3,
    4 => $textoEncabezado4,
    5 => $textoEncabezado5,
    6 => $textoEncabezado6,
    7 => $textoEncabezado7,
    8 => $textoEncabezado8,
    9 => $textoEncabezado9,
    10 => $textoEncabezado10
);

$disposiciones = 'SE CONSIDERARÁ COMO NO PRESENTADO EL PROGRAMA OPERATIVO ANUAL (POA) QUE NO CUMPLA CON LAS NORMAS ESTABLECIDAS EN EL MANUAL PARA LA PLANEACIÓN-PROGRAMACIÓN-PRESUPUESTACIÓN  HACIA UN ENFOQUE DE RESULTADOS.';
// EL PROGRAMA OPERATIVO ANUAL SE RECIBE SIN METAS, EN VIRTUD DE QUE LAS MISMAS SE DEFINIRÁN Y VALIDARAN EN EL MÓDULO DE LA MIR EN LOS MESES SUBSECUENTES, CONSIDERANDO COMO FECHA LÍMITE EL 31 DE DICIEMBRE DE 2015, COMPROMETIÉNDOSE LA UNIDAD RESPONSABLE DENOMINADA  "'.$pdf->ur->getNombre().'"  A DAR SEGUIMIENTO HASTA SU CONCLUSIÓN, EN COORDINACIÓN CON LA SUBSECRETARÍA DE PLANEACIÓN Y LA JEFATURA DE LA GUBERNATURA.

/** 07-10-2019 - Se separaron por partes el texto de acuerdos para poder realizar la justificación del texto*/
$acuerdos = 'SI DE LA REVISIÓN A LA INFORMACIÓN PRESENTADA, LA SECRETARÍA DE FINANZAS DETERMINA REQUERIMIENTOS ADICIONALES DE INFORMACIÓN, LA UR DENOMINADA "' . $pdf->ur->getNombre() . '" , SE OBLIGA A PROPORCIONARLA DE MANERA INMEDIATA, DE NO HACERLO SE EXCLUIRÁN DEL ANTEPROYECTO DE PRESUPUESTO DE EGRESOS LOS MONTOS Y PARTIDAS QUE DEN MOTIVO AL REQUERIMIENTO DE INFORMACIÓN. EN LOS CASOS EN QUE EL SERVIDOR PÚBLICO QUE ENTREGA LA INFORMACIÓN NO SEA EL TITULAR DEL ÁREA ADMINISTRATIVA, ÉSTE SE OBLIGA A INFORMAR DEL PRESENTE DOCUMENTO AL TITULAR DEL ÁREA ADMINISTRATIVA QUE CORRESPONDA. LAS OBSERVACIONES QUE SE DETERMINEN EN EL PROCESO DE RECEPCIÓN DEBERÁN ATENDERSE DE MANERA INMEDIATA.';
$parte1acuerdos = 'SI DE LA REVISIÓN A LA INFORMACIÓN PRESENTADA, LA SECRETARÍA DE FINANZAS DETERMINA REQUERIMIENTOS ADICIONALES DE INFORMACIÓN, LA UR DENOMINADA';
$parte2acuerdos = '"' . $pdf->ur->getNombre() . '" , SE OBLIGA A PROPORCIONARLA DE MANERA INMEDIATA, DE NO HACERLO SE EXCLUIRÁN DEL ANTEPROYECTO DE PRESUPUESTO DE EGRESOS LOS MONTOS ';
$parte3acuerdos = 'Y PARTIDAS QUE DEN MOTIVO AL REQUERIMIENTO DE INFORMACIÓN. EN LOS CASOS EN QUE EL SERVIDOR PÚBLICO QUE ENTREGA LA INFORMACIÓN NO SEA EL TITULAR DEL ÁREA';
$parte4acuerdos = 'ADMINISTRATIVA, ÉSTE SE OBLIGA A INFORMAR DEL PRESENTE DOCUMENTO AL TITULAR DEL ÁREA ADMINISTRATIVA';
$parte5acuerdos = 'QUE CORRESPONDA. LAS OBSERVACIONES QUE SE DETERMINEN EN EL PROCESO DE RECEPCIÓN DEBERÁN ATENDERSE DE MANERA INMEDIATA.';

$textoArcuerdos = array(
    1 => utf8decode(trim($parte1acuerdos)),
    2 => utf8decode(trim($parte2acuerdos)),
    3 => utf8decode(trim($parte3acuerdos)),
    4 => utf8decode(trim($parte4acuerdos)),
    5 => utf8decode(trim($parte5acuerdos))

);;
$pdf->SetFont('UniviaPro-Bold', 'B', 6);
$pdf->SetWidths(array(185));
$pdf->SetAligns(array('J'));

$ytmp = $pdf->GetY();

/**07-10-2019 - APARTADO DEL ENCABEZADO -------------------------------------------------------*/
$lengs = 154;
$inc   = 2;

for ($i = 1; $i <= 10; $i++) {
    if ($i == 10) {
        $pdf->SetXY(10.5, $ytmp + $inc);
        $pdf->Cell(155, 1, substr($textoEncabezado[$i], 0, $longitud), 0, 0, 'L');
    } else {
        $pdf->SetXY(10.5, $ytmp + $inc);
        $pdf->Cell(188, 1, substr($textoEncabezado[$i], 0, $longitud), 0, 0, 'LFJ');
        $inc = $inc + 4;
    }
}
$pdf->Cell(190, 5, '', 0, 1, 'LFJ');

/**SE FINALIZA EL APARTADO DEL ENCABEZADO -----------*/

$pdf->ln(2);
$formato = array('NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO', 'NO');
foreach (explode(",", $fe) as $val) {
    if ($val != '') {
        $formato[$val] = 'SI';
    }
}
$pdf->SetFont('UniviaPro-Bold', 'B', 7);
$pdf->cell(0, 5, 'REPORTES  DEL PROGRAMA OPERATIVO ANUAL '.APP_EJERCICIO.' ENTREGADOS:', 0, 1, 'L');
$pdf->SetFont('UniviaPro-Regular', '', 6);

if (isset($_POST['poa1'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[0], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 01 - IDENTIFICACIÓN INSTITUCIONAL'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa2']) && !empty($_POST['poa2'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[0], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 02 - PLANEACIÓN INSTITUCIONAL'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa3']) && !empty($_POST['poa3'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[1], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 03 - MATRIZ DE INDICADORES PARA RESULTADOS'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa4']) && !empty($_POST['poa4'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[2], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 04 - ESTRUCTURA PROGRAMÁTICA'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa5']) && !empty($_POST['poa5'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[3], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 05 - VINCULACIÓN MATRIZ DE INDICADORES PARA RESULTADOS - ESTRUCTURA PROGRAMÁTICA'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa6']) && !empty($_POST['poa6'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[4], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 06 - CLASIFICACIÓN FUNCIONAL'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa7']) && !empty($_POST['poa7'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[5], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 07 - CLASIFICACIÓN PROGRAMÁTICA'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa8']) && !empty($_POST['poa8'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[6], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 08 - RESUMEN PRESUPUESTAL POR EJE - TEMA - OBJETIVO'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa9']) && !empty($_POST['poa9'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[7], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 09 - PROGRAMA - SUBPROGRAMA - UE - PROYECTO - ACTIVIDAD'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa10']) && !empty($_POST['poa10'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[9], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 10 - PROGRAMA - FUENTE DE FINANCIAMIENTO'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa11']) && !empty($_POST['poa11'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[10], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 11 - RESUMEN PRESUPUESTAL POR IDENTIFICADOR DE PARTIDA'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa12']) && !empty($_POST['poa12'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[11], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 12 - UR - UE - PROGRAMA - SUBPROGRAMA - PROYECTO - OBRA ACTIVIDAD - PARTIDA'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa13']) && !empty($_POST['poa13'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[12], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 13 - INVENTARIO DE PLAZAS'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa14']) && !empty($_POST['poa14'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[13], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 14 - DESPLIEGUE POR RELACIÓN LABORAL'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa15']) && !empty($_POST['poa15'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[14], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 15 - MODALIDAD DE CONTRATACIÓN - PARTIDA ESPECÍFICA'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa16']) && !empty($_POST['poa16'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[15], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 16 - SERVICIOS DE ALIMENTACIÓN (COMEDOR Y VÍVERES)'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa17']) && !empty($_POST['poa17'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[16], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 17 - SERVICIOS DE TELEFONÍA (CELULAR Y FIJA)'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa18']) && !empty($_POST['poa18'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[17], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 18 - INVENTARIO DE VEHÍCULOS POR UR'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa19']) && !empty($_POST['poa19'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[18], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 19 - SERVICIOS DE ARRENDAMIENTO (TERRENOS Y EDIFICIOS)'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa20']) && !empty($_POST['poa20'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[18], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 20 - SEGUROS Y FIANZAS DE BIENES INMUEBLES'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa21']) && !empty($_POST['poa21'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[18], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 21 - SERVICIOS DE VIGILANCIA'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa22']) && !empty($_POST['poa22'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[18], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 22 - OTRAS PARTIDAS'), 0, 1, 'L');
    $pdf->ln(2);
}

if (isset($_POST['poa23']) && !empty($_POST['poa23'])) {
    $pdf->cell(1, 3, '|___|', 0, 0, 'L');
    $pdf->cell(8, 3, $formato[18], 0, 0, 'L');
    $pdf->cell(0, 3, utf8decode('POA 23 - UR - CAPÍTULO - IDENTIFICADOR DE PARTIDA - PARTIDA'), 0, 1, 'L');
    $pdf->ln(2);
}

$pdf->ln(4);

$where = "WHERE gpo_id = ? AND ur_id = ?";
$sql = "
    SELECT
        lpad(prg_id,3,0) AS prg_id,
        lpad(spr_id,2,'0') AS spr_id,
        lpad(ue_id,3,'0') AS ue_id,
        lpad(py_id,3,0) AS py_id,
        lpad(oa_id,3,'0') AS oa_id,
        nombre, pre_original, nivel
    FROM (SELECT
            prg_id,spr_id,ue_id,
            py_id,lpad(oa_id,3,'0') AS oa_id,
            oa_nombre AS nombre ,
            SUM(pre_original) AS pre_original,
            5 AS nivel
        FROM presupuesto
            INNER JOIN cat_ObraActividad USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,ue_id)
            INNER JOIN presupuestoOriginal USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        WHERE gpo_id = ? AND ur_id = ?
        GROUP BY prg_id,spr_id,ue_id,py_id,oa_id, oa_nombre
        UNION ALL
        SELECT
            prg_id,spr_id,ue_id,py_id,
            '' AS oa_id,
            py_nombre AS nombre ,
            SUM(pre_original) AS pre_original,
            4 AS nivel
        FROM presupuesto
            INNER JOIN cat_Proyecto USING (gpo_id,ur_id,prg_id,spr_id,py_id)
            INNER JOIN presupuestoOriginal USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        WHERE gpo_id = ? AND ur_id = ?
        GROUP BY prg_id,spr_id,ue_id,py_id
        UNION ALL
        SELECT
            prg_id,spr_id,ue_id,
            '' AS py_id,
            '' AS oa_id,
            ue_nombre AS nombre ,
            SUM(pre_original) AS pre_original,
            3 AS nivel
        FROM presupuesto
            INNER JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
            INNER JOIN presupuestoOriginal USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        WHERE gpo_id = ? AND ur_id = ?
        GROUP BY prg_id,spr_id,ue_id
        UNION ALL
        SELECT
            prg_id,spr_id,
            '' AS ue_id,
            '' AS py_id,
            '' AS oa_id,
            spr_nombre AS nombre ,
            SUM(pre_original) AS pre_original,
            2 AS nivel
        FROM presupuesto
            INNER JOIN cat_Subprograma USING (gpo_id,ur_id,prg_id,spr_id)
            INNER JOIN presupuestoOriginal USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        WHERE gpo_id = ? AND ur_id = ?
        GROUP BY prg_id,spr_id
        UNION ALL
        SELECT
            prg_id,
            '' AS spr_id,
            '' AS ue_id,
            '' AS py_id,
            '' AS oa_id,prg_nombre AS nombre,
            SUM(pre_original) AS pre_original,
            1 AS nivel
        FROM presupuesto INNER JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
            INNER JOIN presupuestoOriginal using(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
        WHERE gpo_id = ? AND ur_id = ?
        GROUP BY prg_id
    ) AS presupuesto
    ORDER BY lpad(prg_id,3,0),lpad(spr_id,2,'0'),lpad(ue_id,3,'0'),lpad(py_id,3,0),lpad(oa_id,3,'0');";
$params = [$gpo, $ur, $gpo, $ur,$gpo, $ur, $gpo, $ur, $gpo, $ur];
$datos = new AccesoDatos();
$datos->conectar();
$datos->setSql($sql);
$datos->consultar($params);
$numReg = $datos->getNumeroRegistros();
$datos->desconectar();
$dat = $datos->extraer();

$cantidad = 0;
$total = 0;
$paso = false;
$totPrograma = 0;
$totRelacion = 0;
$totGeneral = 0;
$prg_id = "";
$celda = 1;

$pdf->setX(15);
$pdf->SetFont('UniviaPro-Bold', 'B', 8);
$pdf->Cell(100, 2, "", 0, 1, 'L');
$pdf->setX(15);
$pdf->ln(3);
$pdf->SetFillColor(199, 209, 219);
$pdf->RoundedRect(15, $pdf->GetY(), 185, 5, 1, 'DF', '1234');
$pdf->SetWidths(array(20, 25, 12, 20, 20, 55, 33));
$pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'L', 'R'));
$pdf->SetFont('UniviaPro-Bold', 'B', 6.5);
$pdf->SetRelleno("no");
$pdf->setX(15);
$pdf->setAltoLinea(4);
$pdf->Row(array('PROGRAMA', 'SUBPROGRAMA', 'UE', 'PROYECTO', 'ACT / OBRA', 'NOMBRE', 'TOTAL'));
$pdf->ln(1);

if($numReg > 0){
do {
    if ($prg_id <> $dat['prg_id']) {
        if ($paso) {
            $pdf->ln(8);
        }
    }

    if ($pdf->getY() >= 235) {
        $pdf->AddPage();
        $pdf->ln(2);
        $pdf->SetFillColor(199, 209, 219);
        $pdf->RoundedRect(15, $pdf->GetY(), 185, 5, 1, 'DF', '1234');
        $pdf->SetWidths(array(20, 25, 12, 20, 20, 55, 33));
        $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'L', 'R'));
        $pdf->SetFont('UniviaPro-Bold', 'B', 6.5);
        $pdf->SetRelleno("no");
        $pdf->setX(15);
        $pdf->setAltoLinea(4);
        $pdf->Row(array('PROGRAMA', 'SUBPROGRAMA', 'UE', 'PROYECTO', 'ACT / OBRA', 'NOMBRE', 'TOTAL'));
        $pdf->SetBorders(0);
        $pdf->ln(2);
    }

    $prg_id  = "";
    $spr_id  = "";
    $ue_id   = "";
    $py_id   = "";
    $oa_id   = "";
    $imprime = true;

    switch ($dat['nivel']) {
        case 5:
            $prg_id = str_pad($dat['prg_id'], 3, "0", STR_PAD_LEFT);
            $spr_id = str_pad($dat['spr_id'], 2, "0", STR_PAD_LEFT);
            $ue_id = str_pad($dat['ue_id'], 3, "0", STR_PAD_LEFT);
            $py_id = str_pad($dat['py_id'], 3, "0", STR_PAD_LEFT);
            $oa_id = str_pad($dat['oa_id'], 3, "0", STR_PAD_LEFT);

            if ($oa_id == '000') $imprime = false;
            break;

        case 4:
            $prg_id = str_pad($dat['prg_id'], 3, "0", STR_PAD_LEFT);
            $spr_id = str_pad($dat['spr_id'], 2, "0", STR_PAD_LEFT);
            $ue_id = str_pad($dat['ue_id'], 3, "0", STR_PAD_LEFT);
            $py_id = str_pad($dat['py_id'], 3, "0", STR_PAD_LEFT);

            if ($py_id == '00') $imprime = false;

            break;

        case 3:
            $prg_id = str_pad($dat['prg_id'], 3, "0", STR_PAD_LEFT);
            $spr_id = str_pad($dat['spr_id'], 2, "0", STR_PAD_LEFT);
            $ue_id = str_pad($dat['ue_id'], 3, "0", STR_PAD_LEFT);
            if ($ue_id == '000') $imprime = false;
            break;

        case 2:
            $prg_id = str_pad($dat['prg_id'], 3, "0", STR_PAD_LEFT);
            $spr_id = str_pad($dat['spr_id'], 2, "0", STR_PAD_LEFT);
            if ($spr_id == '00') $imprime = false;
            break;

        case 1:
            $prg_id = str_pad($dat['prg_id'], 3, "0", STR_PAD_LEFT);
            break;
    }

    if ($oa_id == '000') {
        $oa_id = '';
    }
    if ($py_id == '000') {
        $py_id = '';
    }
    if ($ue_id == '000') {
        $ue_id = '';
    }
    if ($spr_id == '00') {
        $spr_id = '';
    }

    if (!($dat['nivel'] == 5 and $dat['oa_id'] == '000')  and  $imprime == true) {
        $pdf->SetFillColor(230, 230, 230);
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->SetWidths(array(20, 25, 12, 20, 20, 55, 33));
        $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'L', 'R'));
        $pdf->SetFont('UniviaPro-Regular', '', 7);
        $tmp = $pdf->GetBorders();
        $pdf->SetBorders(0);
        $pdf->setX(15);
        $pdf->Row(array($prg_id, $spr_id, $ue_id, $py_id, $oa_id, mb_convert_encoding((string)$dat['nombre'], 'ISO-8859-1', 'UTF-8'), '$ ' . number_format((float)(isset($dat['pre_original']) && $dat['pre_original'] != '' ? $dat['pre_original'] : 0), 2)));
    }
    if ($dat['nivel'] == 5) {
        $totPrograma += $dat['pre_original'];
        $totGeneral  += $dat['pre_original'];
    }
    $prg_id = $dat['prg_id'];
    $paso = true;
    $cantidad = 0;
    $total = 0;
} while ($dat = $datos->extraer());
}

$pdf->ln(5);

$pos = $pdf->getY();
$pdf->SetFillColor(199, 209, 219);
$pdf->RoundedRect(115, $pos, 85, 5, 1, 'DF', '1234');
$pdf->SetFillColor(255, 255, 255);

$pdf->setX(60);
$pdf->Cell(90, 5, 'TOTAL GENERAL  ', 0, 0, 'R');
if(isset($totGeneral)){    
    $pdf->Cell(50, 5, '$ ' . number_format($totGeneral, 2), 0, 0, 'R');
}else{
    $pdf->Cell(50, 5, '$ ' . number_format((float)0, 2), 0, 0, 'R');
}
$pdf->ln(8);
$totPrograma = 0;

/* EL FINAL DE LA MINUTA DONDE SE IMPRIMEN LAS DISPOSICIONES */

$pdf->CheckPageBreak(15);
$pdf->ln(10);
$pdf->SetFont('UniviaPro-Bold', 'B', 7);
$pdf->cell(0, 5, 'DISPOSICIONES GENERALES:', 0, 2, 'L');
$pdf->SetFont('UniviaPro-Regular', '', 7);

$pdf->ln(1);
$pdf->SetFont('UniviaPro-Regular', '', 6);
$pdf->SetWidths(array(185));
$pdf->SetAligns(array('J'));

$ytmp = $pdf->GetY();
$ix = $pdf->GetX();
$disposiciones_txt = TruncateCadena(utf8decode(trim($disposiciones)), $longitud);

$pdf->SetXY(10.5, $ytmp + 1);
$pdf->Cell(224, 5, substr($disposiciones_txt, 0, $longitud), 0, 0, 'LFJ');
$pdf->Cell(10, 5, '', 0, 1, 'J');

if (strlen($disposiciones_txt) > $longitud) {
    $pdf->SetXY($ix + 10.5, $ytmp + 4);
    $pdf->Cell(155, 5, substr($disposiciones_txt, $longitud, $longitud), 0, 0, 'L');
    $pdf->Cell(5, 5, '', 0, 1, 'J');
}
if (strlen($disposiciones_txt) > $longitud * 2) {
    $pdf->SetXY($ix + 10.5, $ytmp + 8);
    $pdf->Cell(155, 5, substr($disposiciones_txt, $longitud * 2, $longitud), 0, 0, 'L');
    $pdf->Cell(5, 5, '', 0, 1, 'J');
}

if ($pdf->getY() >= 235) {
    $pdf->AddPage();
}

$pdf->ln(5);
$pdf->SetFont('UniviaPro-Bold', 'B', 7);
$pdf->cell(0, 5, 'OBSERVACIONES:', 0, 2, 'L');
$pdf->SetFont('UniviaPro-Regular', '', 7);

$pdf->ln(1);
$pdf->SetFont('UniviaPro-Regular', '', 6);
$pdf->SetWidths(array(185));
$pdf->SetAligns(array('J'));

$ytmp = $pdf->GetY();
$observaciones_txt = TruncateCadena(utf8decode(trim($observaciones)), $longitud);

$pdf->Cell(5, 5, substr($observaciones_txt, 0, $longitud), 0, 0, 'L');
$pdf->Cell(10, 5, '', 0, 1, 'J');

$inc2   = 4;
$lengs2 = 154;
for ($i = 1; $lengs2 <= 1000; $i++) {
    if (strlen($observaciones_txt) > $longitud * $i) {
        $pdf->SetXY($ix + 10, $ytmp + $inc2);
        $pdf->Cell(5, 5, substr($observaciones_txt, $longitud * $i, $longitud), 0, 0, 'L');
        $pdf->Cell(5, 5, '', 0, 1, 'J');
    }
    $inc2   = $inc2 + 4;
    $lengs2 = $lengs2 + 154;
}

if ($pdf->getY() >= 235) {
    $pdf->AddPage();
}

$pdf->ln(2);
$pdf->SetFont('UniviaPro-Bold', 'B', 7);
$pdf->cell(0, 5, 'ACUERDOS:', 0, 2, 'L');
$pdf->SetFont('UniviaPro-Regular', '', 7);

$pdf->ln(1);
$pdf->SetFont('UniviaPro-Regular', '', 6);
$pdf->SetWidths(array(185));
$pdf->SetAligns(array('J'));

$ytmp = $pdf->GetY();

$pdf->Cell(10, 1, '', 0, 1, 'J');

$inc3   = 1;
$lengs3 = 154;



    $pdf->SetXY(10.5, $ytmp + $inc3);
    $pdf->MultiCell(188, 5, utf8Decode($acuerdos), 0, 'LFJ');
   


$pdf->CheckPageBreak(25);

$pdf->Output();

function num2letras($num, $fem = false, $dec = true)
{
    $matuni[2]  = "dos";
    $matuni[3]  = "tres";
    $matuni[4]  = "cuatro";
    $matuni[5]  = "cinco";
    $matuni[6]  = "seis";
    $matuni[7]  = "siete";
    $matuni[8]  = "ocho";
    $matuni[9]  = "nueve";
    $matuni[10] = "diez";
    $matuni[11] = "once";
    $matuni[12] = "doce";
    $matuni[13] = "trece";
    $matuni[14] = "catorce";
    $matuni[15] = "quince";
    $matuni[16] = "dieciseis";
    $matuni[17] = "diecisiete";
    $matuni[18] = "dieciocho";
    $matuni[19] = "diecinueve";
    $matuni[20] = "veinte";
    $matunisub[2] = "dos";
    $matunisub[3] = "tres";
    $matunisub[4] = "cuatro";
    $matunisub[5] = "quin";
    $matunisub[6] = "seis";
    $matunisub[7] = "sete";
    $matunisub[8] = "ocho";
    $matunisub[9] = "nove";

    $matdec[2] = "veint";
    $matdec[3] = "treinta";
    $matdec[4] = "cuarenta";
    $matdec[5] = "cincuenta";
    $matdec[6] = "sesenta";
    $matdec[7] = "setenta";
    $matdec[8] = "ochenta";
    $matdec[9] = "noventa";
    $matsub[3]  = 'mill';
    $matsub[5]  = 'bill';
    $matsub[7]  = 'mill';
    $matsub[9]  = 'trill';
    $matsub[11] = 'mill';
    $matsub[13] = 'bill';
    $matsub[15] = 'mill';
    $matmil[4]  = 'millones';
    $matmil[6]  = 'billones';
    $matmil[7]  = 'de billones';
    $matmil[8]  = 'millones de billones';
    $matmil[10] = 'trillones';
    $matmil[11] = 'de trillones';
    $matmil[12] = 'millones de trillones';
    $matmil[13] = 'de trillones';
    $matmil[14] = 'billones de trillones';
    $matmil[15] = 'de billones de trillones';
    $matmil[16] = 'millones de billones de trillones';

    $num = trim((string) @$num);
    if (isset($num[0]) == '-') {
        $neg = 'menos ';
        $num = substr($num, 1);
    } else
        $neg = '';
    while (isset($num[0]) == '0') $num = substr($num, 1);
    if (isset($num[0]) < '1' or isset($num[0]) > 9) $num = '0' . $num;
    $zeros = true;
    $punt = false;
    $ent = '';
    $fra = '';
    for ($c = 0; $c < strlen($num); $c++) {
        $n = $num[$c];

        if (!(strpos(".,'''", $n) === false)) {
            if ($punt) break;
            else {
                $punt = true;
                continue;
            }
        } elseif (!(strpos('0123456789', $n) === false)) {
            if ($punt) {
                if ($n != '0') $zeros = false;
                $fra .= $n;
            } else

                $ent .= $n;
        } else

            break;
    }
    $ent = '     ' . $ent;
    $fin = $fra;

    if ((int) $ent === 0) return 'Cero ' . $fin;
    $tex = '';
    $sub = 0;
    $mils = 0;
    $neutro = false;
    while (($num = substr($ent, -3)) != '   ') {
        $ent = substr($ent, 0, -3);
        if (++$sub < 3 and $fem) {
            $matuni[1] = 'una';
            $subcent = 'as';
        } else {
            $matuni[1] = $neutro ? 'un' : 'uno';
            $subcent = 'os';
        }
        $t = '';
        $n2 = substr($num, 1);
        if ($n2 == '00') {
        } elseif ($n2 < 21)
            $t = ' ' . $matuni[(int) $n2];
        elseif ($n2 < 30) {
            $n3 = $num[2];
            if ($n3 != 0) $t = 'i' . $matuni[$n3];
            $n2 = $num[1];
            $t = ' ' . $matdec[$n2] . $t;
        } else {
            $n3 = $num[2];
            if ($n3 != 0) $t = ' y ' . $matuni[$n3];
            $n2 = $num[1];
            $t = ' ' . $matdec[$n2] . $t;
        }
        $n = $num[0];
        if ($n == 1) {
            $t = ' ciento' . $t;
        } elseif ($n == 5) {
            $t = ' ' . $matunisub[$n] . 'ient' . $subcent . $t;
        } elseif ($n != 0 && $n != ' ') {
            $t = ' ' . $matunisub[$n] . 'cient' . $subcent . $t;
        }
        if ($sub == 1) {
        } elseif (!isset($matsub[$sub])) {
            if ($num == 1) {
                $t = ' mil';
            } elseif ($num > 1) {
                $t .= ' mil';
            }
        } elseif ($num == 1) {
            $t .= ' ' . $matsub[$sub] . 'ón';
        } elseif ($num > 1) {
            $t .= ' ' . $matsub[$sub] . 'ones';
        }
        if ($num == '000') $mils++;
        elseif ($mils != 0) {
            if (isset($matmil[$sub])) $t .= ' ' . $matmil[$sub];
            $mils = 0;
        }
        $neutro = true;
        $tex = $t . $tex;
    }

    /** Validacion para la validacion de la palabra 'de pesos' o en su defeto el uso de pesos*/
    $arregloTemporal =  str_split($tex);
    $arregloTemporalVerficarDe = array();
    $arregloPalabrasVerificar = array(0 => 'mill', 1 => 'trill', 2 => 'bill');
    $palabraDe = '';
    $textoTemporal = '';

    for ($i = 0; $i < count($arregloTemporal); $i++) {
        if (count($arregloTemporal) - $i == 1) {
            $textoTemporal .= $arregloTemporal[$i];
            array_push($arregloTemporalVerficarDe, $textoTemporal);
        } elseif ($arregloTemporal[$i] != ' ') {
            $textoTemporal .= $arregloTemporal[$i];
        } else {
            array_push($arregloTemporalVerficarDe, $textoTemporal);
            $textoTemporal = '';
        }
    }

    for ($i = 0; $i < count($arregloPalabrasVerificar); $i++) {
        if (strpos($arregloTemporalVerficarDe[count($arregloTemporalVerficarDe) - 1], $arregloPalabrasVerificar[$i]) !== false) {
            $palabraDe = 'de ';
        }
    }
    $tex = $neg . substr($tex, 1) . ' ' . $palabraDe . 'pesos ' . $fin;
    return ucfirst($tex);
}
