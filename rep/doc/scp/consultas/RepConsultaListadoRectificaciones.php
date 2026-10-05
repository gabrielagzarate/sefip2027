<?php
session_start();
ini_set('memory_limit', '20M');
set_time_limit(0);
require_once dirname(__FILE__) . '/../../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../lib/conversionNumLetras.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../lib/MesNombre.php';

if (!function_exists('u8d')) {
	function u8d($value)
	{
		return mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
	}
}

class reportetabla extends PDF
{
	public function  __construct()
	{
		parent::__construct();
	}
	function Footer($lastPage = true)
	{
		$row = $this->getDatos();
		$tmpb = $this->GetBorders();
		$this->SetY(-45);
		$nombre = '';
		$puesto = '';
		$deptmp = 0;

		$this->SetY(-15);
		$this->SetFont('Arial', '', 6.5);
		$this->Cell(0, 10, u8d('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
		$this->Cell(0, 10, date("d/m/y") . ' - ' . date("H:i:s") . '        ', 1, 0, 'R');
	}
	function Header()
	{
		//Encabezado del documento

		$row = $this->getDatos();
		$ix = $this->GetX();
		$iy = $this->GetY();
		$this->Image('../../../../img/escudo.jpg', $ix, $iy , '18', '25', 'JPG', '');

		$this->SetFont('Arial', 'B', 9);
		$this->Cell(270, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'C');
		$this->Cell(270, 5, '2022', 0, 1, 'C');
		$this->Cell(270, 5, u8d('SUBSECRETARÍA DE EGRESOS, CONTABILIDAD Y TESORERÍA'), 0, 1, 'C');
		$this->Cell(270, 5, 'PRESUPUESTO DE EGRESOS', 0, 1, 'C');
		$this->Cell(270, 5, 'RESUMEN DE RECTIFICACIONES', 0, 1, 'C');
		$this->ln();

		$this->setX(5);
		$this->SetFont('Arial', 'B', 6.5);
		$this->Cell(40, 5, '', 0, 0, 'L');
		$this->Cell(15, 5, '', 0, 0, 'L');
		if ($row['rct_reintegroLote'] == 'CR') {
			$this->Cell(228, 5, 'LISTA DE RECTIFICACIONES CON REINTEGRO DEL MES DE ' . MesNombre($_POST['meses']), 0, 0, 'R');
		} else {
			$this->Cell(234, 5, 'LISTA DE RECTIFICACIONES SIN REINTEGRO DEL MES DE ' . MesNombre($_POST['meses']), 0, 0, 'R');
		}
		$this->ln(6);

		/*CABECERA DEL LISTADO DE RECTIFICACION*/

		$this->setRelleno("no");
		$this->SetFont('Arial', 'B', 7);
		$this->SetTextColor(0, 0, 0);
		$this->setX(5);

		$this->SetWidths(array(15, 15, 34, 28, 35, 25, 23, 93, 20));
		$this->SetDrawColor(180, 180, 180);
		$this->SetBorders("TB");
		$this->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
		$this->Row(array(
			u8d('No. PROG.'),
			u8d('No. FOLIO'),
			u8d('FECHA DE ELABORACIÓN'),
			u8d('STATUS'),
			u8d('FECHA DE AUTORIZACIÓN'),
			u8d('TIPO DE RECTIF.'),
			u8d('No. CEDULA'),
			u8d('JUSTIFICACIÓN'),
			u8d('IMPORTE'),
		));
		$this->ln(.2);
		$this->SetTextColor(0, 0, 0);
	}
}

$pdf = new reportetabla('L', 'mm', 'Letter');
$pdf->setOrientation('L');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 20);
$wh = '';
//print_r($_POST);
if (isset($_POST['meses'])) {
	$wh .= 'AND lr.lrct_mes = ' . $_POST['meses'] . '';
}
if (isset($_POST['listado'])) {
	$wh .= ' AND lr.lrct_listado_id = ' . $_POST['listado'] . '';
}

$wf = '';
if (isset($_POST['tipoRct']) and $_POST['tipoRct'] != '') {
	switch ($_POST['tipoRct']) {
		case 1:
			$wf .= ($wf == '') ? ' rct_reintegro = 1' : '';
			break;
		case 2:
			$wf .= ($wf == '') ? ' rct_reintegro IN (2,3)' : '';
			break;
	}
}

if (isset($_POST['ur_id'])) {
	$wf .= ' AND  concat(gpo_id,ur_id) = ("' . $_POST['ur_id'] . '") ';
}

$conexion = new AccesoDatos();
$conexion->conectar();
$sql = '
	SELECT 
		gpo_id,
		ur_id,
		clc_id,
		tcl_id,
		tcl_nombre,
		rct_id,
		rct_justificacion,
		rct_reintegro,
		DATE_FORMAT(rct_fechaAutorizacion,"%d/%m/%Y") AS rct_fechaAutorizacion,
		ecl_nombre,
		(rct_percepcion-rct_retencion) AS rct_importe,
		lr.lrct_dia,
		lr.lrct_mes,
		lr.lrct_anio,
		#rct_reintegroLote,
		lr.lrct_reintegro,
		DATE_FORMAT(lr.lrct_fechaListado,"%d/%m/%Y") AS rct_fechaListado, 
		DATE_FORMAT(rct_fechaElaboracion,"%d/%m/%Y") AS rct_fechaElaboracion,
		cfi_clave,
		cfi_nombre,
		ur_nombre,
		lr.lrct_listado_id
	FROM scp_MaestroRectificacion a
		INNER JOIN scp_ListadoRectificacion lr USING (gpo_id, ur_id, rct_id)
		INNER JOIN scp_Clc b USING (gpo_id,ur_id,clc_id)
		INNER JOIN enc_FiltroRectificacion f USING (gpo_id,ur_id,tcl_id,stc_id)
		INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
		INNER JOIN sinpres.cat_ClaveFinanciamiento2022 USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
		INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
		INNER JOIN cat_TipoClc USING (tcl_id)
		INNER JOIN cat_EstadoClc e ON a.ecl_id = e.ecl_id
	WHERE ' . $wf . ' 
		AND (a.ecl_id = 6 AND b.ecl_id = 18 AND clc_recursoFederal = 2)
		' . $wh . '  
	GROUP BY gpo_id,ur_id,rct_id,clc_id,cfi_clave,cfi_nombre
	ORDER BY gpo_id,ur_id,rct_id,clc_id;
	';

/*echo "<pre>" . $sql . "</pre>";
die();*/

$conexion->setSql($sql);
$conexion->consultar();
$conexion->desconectar();
$row = $conexion->extraer();

$pdf->setDatos($row);
$pdf->AddPage();

$pdf->ln(2);
$importeUr = $importe = 0;
$colorfondo = 0;
$contador = 1;
$ur = $urnombre = $unidad = $leyenda = $lote = '';
$bandera = false;
$lote = str_pad($row['lrct_dia'], 2, "0", STR_PAD_LEFT) . str_pad($row['lrct_mes'], 2, "0", STR_PAD_LEFT) . $row['lrct_anio'] . $row['lrct_reintegro'] . str_pad($row['lrct_listado_id'], 3, "0", STR_PAD_LEFT);
if ($row['lrct_reintegro'] == 'CR') {
	$leyenda = 'CON REINTEGRO';
} else {
	$leyenda = 'SIN REINTEGRO';
}
//$unidad=$row['gpo_id'].$row['ur_id'];	
$unidad = '';
do {
	if ($pdf->CheckPageBreak(20)) {
		$pdf->setX(5);
	}

	if ($unidad != $row['gpo_id'] . $row['ur_id']) {
		$unidad = $row['gpo_id'] . $row['ur_id'];
		//OBTENER TOTAL DE LA UNIDAD RESPOSABLE
		$conexionTotal = new AccesoDatos();
		$conexionTotal->conectar();
		$sql = '
			SELECT 
			    SUM(rct_importe) AS total
			FROM( SELECT 
					gpo_id, 
					ur_id, 
					(rct_percepcion - rct_retencion) AS rct_importe 
				FROM scp_MaestroRectificacion a
				    INNER JOIN scp_ListadoRectificacion lr USING (gpo_id, ur_id, rct_id)
					INNER JOIN scp_Clc b USING (gpo_id,ur_id,clc_id)
					INNER JOIN enc_FiltroRectificacion f USING (gpo_id,ur_id,tcl_id,stc_id)
					INNER JOIN cat_TipoClc using (tcl_id)
				WHERE ' . $wf . '
				    AND gpo_id = ' . $row['gpo_id'] . ' 
					AND ur_id = ' . $row['ur_id'] . '
					AND (a.ecl_id = 6 AND b.ecl_id = 18 and clc_recursoFederal = 2)
				    ' . $wh . ' 
				GROUP BY gpo_id,ur_id,rct_id,clc_id
			) AS t ';
		/*echo "<pre>" . $sql . "</pre>";
		die();*/
		$conexionTotal->setSql($sql);
		$conexionTotal->consultar();
		$conexionTotal->desconectar();
		$rowTotal = $conexionTotal->extraer();

		$pdf->ln(2);
		$pdf->setX(5);
		$pdf->SetFont('Arial', 'B', 6.5);
		$pdf->Cell(257, 5, '' . ($row['gpo_id'] . $row['ur_id']) . ' .- ' . u8d($row['ur_nombre']), 0, 0, 'L');
		$pdf->Cell(30, 5, '$' . number_format($rowTotal["total"], 2), 0, 0, 'R');
		$pdf->ln(8);
		$importeUr = 0;
		$urnombre = '';
		$ur = '';
		$unidad = $row['gpo_id'] . $row['ur_id'];
	}

	if ($colorfondo == 0) {
		$pdf->SetRelleno("no");
		$colorfondo = 1;
	} else {
		$pdf->SetRelleno("si");
		$colorfondo = 0;
	}
	$pdf->SetFont('Arial', '', 6.5);
	$pdf->SetFillColor(245, 245, 245);
	$pdf->SetAltoLinea(3);
	$pdf->setX(5);

	$pdf->SetWidths(array(15, 15, 27, 40, 27, 30, 20, 93, 20));
	$pdf->SetDrawColor(235, 235, 235);
	$pdf->SetBorders("B");
	$pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'L', 'R'));
	$pdf->Row(array(
		$contador,
		u8d($row['rct_id']),
		u8d($row['rct_fechaElaboracion']),
		u8d($row['ecl_nombre']),
		u8d($row['rct_fechaAutorizacion']),
		u8d($row['rct_reintegro'] == 1 ? "CON REINTEGRO" : "SIN REINTEGRO"),
		u8d($row['clc_id']),
		u8d($row['rct_justificacion']),
		u8d("$" . number_format($row['rct_importe'], 2)),
	));
	$contador++;
	$importeUr += $row['rct_importe'];
	$importe += $row['rct_importe'];
	$urnombre = $row['ur_nombre'];
	$ur = $row['gpo_id'] . $row['ur_id'];
} while ($row = $conexion->extraer());

$pdf->ln(2);
$pdf->setx(5);
$pdf->SetFont('Arial', 'B', 6.5);
$pdf->Cell(257, 5, 'TOTAL GENERAL', 0, 0, 'R');
$pdf->Cell(30, 5, '$' . number_format($importe, 2), 1, 0, 'R');
$pdf->ln(2);
if ($pdf->CheckPageBreak(40)) {
	$pdf->setX(5);
}

$pdf->setRelleno("no");
$pdf->setBorders(0);
$pdf->setWidths(array(287));
$pdf->setx(5);
$pdf->setAligns(array('L'));
$totaldetotales = $importe;
$totaldetotales = number_format($totaldetotales, 2, ".", "");
$dec = explode(".", $totaldetotales);
$pdf->CheckPageBreak(45);
$pdf->setWidths(array(287));
$pdf->setAligns(array('L'));
$pdf->setx(5);
$tipo = '';
$tipo = 'con reintegro';


$pdf->CheckPageBreak(34);
$pdf->setX(5);
$pdf->ln(4);
$pdf->SetFont('Arial', 'B', 6.5);
$pdf->SetDrawColor(190, 190, 190);
$pdf->SetBorders("T");
$pdf->SetAltoLinea(4);
$pdf->setWidths(array(280));

$pdf->SetProtection(array('print', 'annot-forms'),"",PDF_PASSWORD_OWNER_SCP);
$pdf->Output();
