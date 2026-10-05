<?php
session_start();
ini_set('memory_limit','20M');
set_time_limit(500);
//librerias

require_once('../../../lib/reportetabla.1.8.php');	
require_once('../../../lib/conversionNumLetras.php');	
require_once('../../../../lib/AccesoDatos.php');
require_once('../../../../ent/UnidadResponsable.php');
require_once('../../../../lib/MesNombre.php');
require_once('../../../../lib/helpers.php');

class reportetabla extends PDF 
{
    public function __construct()
    {
        parent::__construct();
    }
    //Pie del documento
    function Footer( $lastPage = true)
    {
        $this->SetY(-45);

        $this->SetY(-15);
        $this->SetFont('Arial','',6.5);
        $this->Cell(0,10,utf8Decode('Página ') . $this->PageNo() . '/{nb}',0,0,'C');
        $this->Cell(0,10,date("d/m/y") . ' - ' . date("H:i:s") . '        ',1,0,'R');
    }
    //Encabezado del documento
    function Header()
    {
        $row = $this->getDatos();
        $ix=$this->GetX();
        $iy=$this->GetY();
        $this->Image('../../../../img/'.LOGO_GRIS, $ix,$iy-7, '85' , '30','PNG', '');

        $this->SetFont('Arial','',8);
        $this->Cell(270,5,'GOBIERNO DEL ESTADO DE OAXACA ' . $this->GetYear('y'),0,1,'C');
        $this->SetFont('Arial','',8);
        $this->Cell(270,5,utf8Decode('SUBSECRETARÍA DE EGRESOS, CONTABILIDAD Y TESORERÍA'),0,1,'C');
        $this->Cell(270,5,'PRESUPUESTO DE EGRESOS',0,1,'C');
        $this->Cell(270,5,'RESUMEN DE RECTIFICACIONES',0,1,'C');
        $this->ln();

        $this->setX(5);
        $this->SetFont('Arial','B',6.5);

        $this->Cell(40,5,'',0,0,'L');
        $this->Cell(15,5,'',0,0,'L');
        if (($row) && $row['lrct_reintegro'] == 'CR') {
            $this->Cell(223,5,'LISTA DE RECTIFICACIONES CON REINTEGRO DEL MES DE ' . MesNombre($_POST['meses']),0,0,'R');
        } else {
            $this->Cell(223,5,'LISTA DE RECTIFICACIONES SIN REINTEGRO DEL MES DE ' . MesNombre($_POST['meses']),0,0,'R');	
        }	
        $this->ln(6);

        /*CABECERA DEL LISTADO*/
        $this->setRelleno("no");
        $this->SetFont('Arial','B',7);
        $this->SetTextColor(0,0,0);
        $this->setX(5);

        $this->SetWidths(array(15,15,34,25,35,25,20,98,20));
        $this->SetDrawColor(180,180,180);
        $this->SetBorders("TB");

        $this->SetAligns(array('C','C','C','C','C','C','C','C','C'));
        $this->Row(array(utf8Decode('No. PROG.'),
            utf8Decode('No. FOLIO'),
            utf8Decode('FECHA DE ELABORACIÓN'),
            utf8Decode('STATUS'),
            utf8Decode('FECHA DE AUTORIZACIÓN'),
            utf8Decode('TIPO DE RECTIF.'),
            utf8Decode('No. CEDULA'),
            utf8Decode('JUSTIFICACIÓN'),
            utf8Decode('IMPORTE'),
        ));
        $this->ln(.2);
        $this->SetTextColor(0,0,0);
    }
}
$pdf = new reportetabla('L', 'mm', 'Letter');
$pdf->setOrientation('L');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 20);

$params = [];
$wf = '';
if (isset($_POST['tipoRct']) && $_POST['tipoRct'] != '') {
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
    array_push($params, $_POST['gpo_id'] . $_POST['ur_id']);
    $wf .= ' AND  concat(gpo_id,ur_id) = (?) ';
}

$wh = '';
if (isset($_POST['meses'])) {
    array_push($params, $_POST['meses']);
    $wh .= 'AND lrct.lrct_mes = ? ';
}

if (isset($_POST['listado'])) {
    array_push($params, $_POST['listado']);
    $wh .= ' AND lrct.lrct_listado_id = ? ';
}

array_push($params, $_SESSION[_S]['dep_id']);

$conexion = new AccesoDatos();	
$conexion->conectar();

$sql = '
    SELECT gpo_id, ur_id, clc_id, tcl_id, tcl_nombre,
        rct_id, rct_justificacion, rct_reintegro,
        DATE_FORMAT(rct_fechaAutorizacion,"%d/%m/%Y") AS rct_fechaAutorizacion,
        ecl_nombre,
        (rct_percepcion-rct_retencion) AS rct_importe,
        lrct_dia, lrct_mes, lrct_anio, lrct_reintegro,
        DATE_FORMAT( lrct_fechaListado,"%d/%m/%Y") AS rct_fechaListado,
        DATE_FORMAT( rct_fechaElaboracion,"%d/%m/%Y") AS rct_fechaElaboracion,
        #(cfi_clave) AS cfi_clave,
        #(cfi_nombre) AS cfi_nombre,
        ur_nombre,
        lrct_listado_id
    FROM scp_MaestroRectificacion a
        INNER JOIN scp_ListadoRectificacion lrct USING (gpo_id, ur_id, rct_id)
        INNER JOIN scp_Clc b USING (gpo_id,ur_id,clc_id)
        INNER JOIN enc_FiltroRectificacion f USING (gpo_id,ur_id,tcl_id,stc_id)
        INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
        INNER JOIN '.BD_SINPRES.'.cat_ClaveFinanciamiento'.APP_EJERCICIO.' USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
        INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
        INNER JOIN cat_TipoClc USING (tcl_id)
        INNER JOIN cat_EstadoClc e ON a.ecl_id = e.ecl_id
    WHERE ' . $wf . '
        ' . $wh . ' 
        AND dep_id = ? 
        AND (a.ecl_id = 6 AND b.ecl_id = 18 AND clc_recursoFederal = 2)
    GROUP BY gpo_id,ur_id,rct_id,clc_id
    ORDER BY gpo_id,ur_id,rct_id,clc_id
';

$conexion->setSql($sql);
$conexion->consultar($params);
$conexion->desconectar();
$row = $conexion->extraer();

$pdf->setDatos($row);
$pdf->AddPage();
$pdf->ln(2);

$importeUr = $importe = 0;
$colorfondo = 0;
$contador = 1;
$ur = $urnombre = $unidad = $leyenda = '';
$bandera = false;
if ($row) {
    if ($row['lrct_reintegro'] == 'CR') {
        $leyenda = 'CON REINTEGRO';
    } else {
        $leyenda = 'SIN REINTEGRO';
    }

    $unidad = '';
    array_push($params, $row['gpo_id'], $row['ur_id']);

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
                FROM(
                    SELECT 
                        gpo_id, 
                        ur_id, 
                        rct_percepcion - rct_retencion AS rct_importe 
                    FROM scp_MaestroRectificacion a
                        INNER JOIN scp_ListadoRectificacion lrct USING (gpo_id, ur_id, rct_id)
                        INNER JOIN scp_Clc b USING(gpo_id,ur_id,clc_id)
                        INNER JOIN enc_FiltroRectificacion f USING (gpo_id,ur_id,tcl_id,stc_id)
                        INNER JOIN cat_TipoClc using (tcl_id)
                    WHERE ' . $wf . ' 
                        ' . $wh . '
                        AND dep_id = ? 
                        AND gpo_id = ? 
                        AND ur_id = ?
                        AND (a.ecl_id = 6 AND b.ecl_id = 18 and clc_recursoFederal = 2)
                    GROUP BY gpo_id,ur_id,rct_id,clc_id
                ) AS t
            ';
            $conexionTotal->setSql($sql);
            $conexionTotal->consultar($params);
            $conexionTotal->desconectar();
            $rowTotal = $conexionTotal->extraer();

            $pdf->ln(2);
            $pdf->setX(5);
            $pdf->SetFont('Arial','B',6.5);
            $pdf->Cell(257,5,'' . ($row['gpo_id'] . $row['ur_id']) . ' .- ' . utf8Decode($row['ur_nombre']),0,0,'L');
            $pdf->Cell(30,5,'$' . number_format($rowTotal["total"],2),0,0,'R');
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

        $pdf->SetFont('Arial','',6.5);
        $pdf->SetFillColor(245,245,245);
        $pdf->SetAltoLinea(3);
        $pdf->setX(5);

        /* @JORDY 13-05-2019 rango de la version del 2018 
        $pdf->SetWidths(array(15,15,27,40,27,30,20,93,20));*/

        $pdf->SetWidths(array(15,15,34,25,35,25,20,93,20));
        $pdf->SetDrawColor(235,235,235);
        $pdf->SetBorders("B");
        $pdf->SetAligns(array('C','C','C','C','C','C','C','L','R'));
        $pdf->Row(array($contador,
            utf8Decode($row['rct_id']),
            utf8Decode($row['rct_fechaElaboracion']),
            utf8Decode($row['ecl_nombre']),
            utf8Decode($row['rct_fechaAutorizacion']),
            utf8Decode($row['rct_reintegro'] == 1 ? "CON REINTEGRO" : "SIN REINTEGRO"),
            utf8Decode($row['clc_id']),
            utf8Decode($row['rct_justificacion']),
            utf8Decode("$".number_format($row['rct_importe'],2)),
        ));
        $contador++;
        $importeUr += $row['rct_importe'];
        $importe += $row['rct_importe'];
        $urnombre = $row['ur_nombre'];
        $ur = $row['gpo_id'].$row['ur_id'];
            
    } while ($row = $conexion->extraer());
}
$pdf->ln(2);
$pdf->setx(5);
$pdf->SetFont('Arial','B',6.5);
$pdf->Cell(257,5,'TOTAL GENERAL',0,0,'R');
$pdf->Cell(30,5,'$'.number_format($importe,2),1,0,'R');
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
$dec = explode(".",$totaldetotales);
$pdf->CheckPageBreak(45);
$pdf->setWidths(array(287));
$pdf->setAligns(array('L'));
$pdf->setx(5);
$tipo = '';
$tipo = 'con reintegro';

$pdf->CheckPageBreak(34);
$pdf->setX(5);
$pdf->ln(4);
$pdf->SetFont('Arial','B',6.5);
$pdf->SetDrawColor(190,190,190);
$pdf->SetBorders("T");
$pdf->SetAltoLinea(4);
$pdf->setWidths(array(280));

$pdf->SetProtection(array('print', 'annot-forms'),"",PDF_PASSWORD_OWNER_SCP);
$pdf->Output();