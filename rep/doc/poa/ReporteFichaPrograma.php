<?php

/**
 * ReporteFichaPrograma
 *
 * Permite generar Reporte de la FIha del programa
 * en Formato PDF
 *
 * PHP version 7
 *
 * @category  Consulta
 * @package   Package
 * @author    Yuridia Esmeralda Santiago Martínez <yuridia.santiago@finanzasoaxaca.gob.mx>
 * @copyright 2018 Secretaria de Finanzas del Gobierno del Estado de Oaxaca
 */
session_start();
ini_set('memory_limit', '20M');
error_reporting(0);
set_time_limit(0);

//  LIBRERIAS
require_once dirname(__FILE__) . "/../../../lib/constantes.php";
require_once('../../lib/reportetabla.1.8.php');
require_once('../../../lib/AccesoDatos.php');
require_once dirname(__FILE__) . '/../../../ent/UnidadResponsable.php';
require_once('../../../lib/helpers.php');

class reportetabla extends PDF
{

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);
    }

    function Footer($lastPage = true)
    {
        $this->setX(5);
        $this->SetY(-15);
    }

    function Header() //Encabezado del documento
    {
        $this->SetMargins(30, 55, 25);
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(1);

        $ix = $this->GetX();
        $iy = $this->GetY();

        $this->Image('../../../img/logoEjecutivo.jpg', $ix, $iy - 40, '32', '33', 'jpg', '');

        $this->SetFont('Montserrat-Bold', 'B', 12);
        $this->SetXY($ix, $iy);
        $ytmp = $this->GetY();
        $this->Cell(161, 0, ' ' . APP_EJERCICIO . ' FICHA DEL PROGRAMA', 0, 1, 'C');
        $this->SetXY($this->lMargin, $ytmp);
    }

    function CheckPageBreak($h)
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
            $this->SetX($this->lMargin);
            $this->Ln(5);
            return true;
        }
    }
}

//CONSULTA
$sql = '
    SELECT prgs_id,	prg_nombre,	CONCAT( gpo_id, ur_id ) ur_responsable,	ur_nombre,
    eje_nombre,	tem_nombre,	obj_nombre,fin_nombre,
    spe_nombre,	tpg_nombre,	stp_nombre,	ctp_nombre,  ods_nombre, ods_url,cs.spe_nombre,
	co.obp_nombre, pe.ple_nombre, ope.ope_nombre
    FROM
    ( SELECT gpo_id, ur_id, prg_id AS prgs_id, prg_nombre, usr_id, usr_fecha FROM cat_Programa ) p
    INNER JOIN cat_Programas USING ( prgs_id )
    INNER JOIN cat_EstadoPrograma USING ( epr_id )
    INNER JOIN vin_ProgramasMir vpm USING ( prgs_id, gpo_id, ur_id )
    INNER JOIN cat_UnidadResponsable USING ( gpo_id, ur_id )
    INNER JOIN vin_Programa vp USING ( prgs_id )
    INNER JOIN enc_PedObjetivo USING ( epo_id )
    INNER JOIN cat_Eje USING ( eje_id )
    INNER JOIN cat_Tema t USING ( eje_id, tem_id )
    INNER JOIN cat_Objetivo USING ( eje_id, tem_id, obj_id )
    INNER JOIN cat_Subprogramas USING (prgs_id)
    INNER JOIN enc_PedODS USING (epods_id)
    INNEr JOIN cat_ODS  USING (ods_id)
    INNER JOIN cat_Finalidad using(fin_id)
    INNER JOIN cat_SectorPes cs ON vp.spe_id = cs.spe_id 
    INNER JOIN cat_ObjetivoPes co on vp.obp_id = co.obp_id and vp.spe_id = co.spe_id
    INNER JOIN enc_ProgramaPresupuestario USING ( epp_id )
    INNER JOIN cat_Tipologia USING ( tpg_id )
    INNER JOIN cat_SubTipologia USING ( tpg_id, stp_id ) 
    INNER JOIN cat_ClasificacionTipologia USING ( tpg_id, stp_id, ctp_id ) 
    INNER JOIN cat_PlanEspecial pe USING (ple_id) 
    INNER JOIN cat_ObjetivoPe ope USING (ple_id,ope_id)
    WHERE
    epr_id = 4
    GROUP BY gpo_id,ur_id,prgs_id
    ORDER BY prgs_id ASC;
';

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar();
$conexion->desconectar();


$pdf = new Reportetabla();
$pdf->SetProtection(array("print"), "", PDF_PASSWORD_OWNER_POA);
/* Se agrega la paginacion */
$pdf->AliasNbPages();
$pdf->AddFont('Montserrat-Regular', '', 'Montserrat-Regular.php');
$pdf->AddFont('Montserrat-Bold', 'B', 'Montserrat-Bold.php');
// Margenes
$pdf->SetMargins(30, 55, 25);
$pdf->SetAutoPageBreak(true, 25);
while ($row = $conexion->extraer()) {
    $pdf->setDatos($row);
    $datos = $pdf->getDatos();
    $url = $datos['ods_url'];
    /* Se debe controlar cuando se inserta una nueva pagina */
    $pdf->AddPage();

    /* Contenido del reporte */

    //DATOS DEL PROGRAMA
    $pdf->SetRelleno("si");
    $pdf->CheckPageBreak(10);
    $pdf->Cell(0, 5, utf8Decode(''), 0, 1, 'L');
    $pdf->SetArrayBorders(array('LT', 'T', 'RT'));
    $pdf->SetFillColor(199, 209, 219);
    $pdf->SetWidths(array(22, 7, 132));
    $pdf->Row(array(' ', ' ', ' '));

    $pdf->SetAligns(array('L'));
    $pdf->setAltoLinea(4);
    $longitud = strlen(utf8Decode($datos['prg_nombre']));


    $pdf->SetFont('Montserrat-Regular', '', 7);


    $pdf->Row(array('PROGRAMA:', str_pad($datos['prgs_id'], 2, "0", STR_PAD_LEFT), utf8Decode(mb_strtoupper($datos['prg_nombre']))), 'LR');

    $pdf->Row(array('RESPONSABLE:', str_pad($datos['ur_responsable'], 2, "0", STR_PAD_LEFT), utf8Decode($datos['ur_nombre'])), 'LR');
    $pdf->SetArrayBorders(array('LB', 'B', 'RB'));
    $pdf->Row(array('', '', ''), '', 0);
    $pdf->Cell(0, 5, utf8Decode(''), 0, 1, 'L');

    //VINCULACIÓN PED
    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->SetArrayBorders(array('TL', 'T', 'TR'));
    $pdf->setAltoLinea(4);
    $pdf->SetAligns(array('L'));
    $pdf->SetFont('Montserrat-Regular', '', 7);
    $pdf->SetWidths(array(30, 0));
    $pdf->Row(array(utf8Decode('VINCULACIÓN  PED'), utf8Decode(''), ''), 'TR', 'TR');
    $pdf->SetWidths(array(18, 0));
    $pdf->Row(array('EJE:', utf8Decode(mb_strtoupper($datos['eje_nombre']))), 'LR');
    $pdf->Row(array('TEMA:', utf8Decode(mb_strtoupper($datos['tem_nombre']))), 'LR');
    $pdf->Row(array('OBJETIVO:', utf8Decode(mb_strtoupper($datos['obj_nombre']))), 'LR');
    $pdf->Row(array('', ''), 'LR');
    $pdf->SetArrayBorders(array('T', 'T', '', 'T', 'T'));
    $pdf->Row(array("", "", '', "", ""));
    // CLASIFICACIÓN FUNCIONAL Y SECTORIAL    
    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setAltoLinea(4);
    $pdf->SetAligns(array('L'));
    $pdf->SetFont('Montserrat-Regular', '', 7);

    $pdf->SetArrayBorders(array('TLR', '', 'TLR'));
    $pdf->SetWidths(array(77, 7, 77));
    $pdf->Row(array(utf8Decode('PLAN ESTRATÉGICO SECTORIAL'), '', utf8Decode('ODS')));

    $pdf->SetWidths(array(20, 57, 7, 77));
    $pdf->SetArrayBorders(array('L', 'R', '', 'LR'));
    $pdf->Row(array(utf8Decode('SECTOR :'), utf8Decode(mb_strtoupper($datos['spe_nombre'])), '', utf8Decode(mb_strtoupper($datos['ods_nombre']))));

    $pdf->SetWidths(array(20, 57, 7, 77));
    $pdf->SetArrayBorders(array('L', 'R', '', 'LR'));
    $pdf->Row(array(utf8Decode('OBJETIVO :'), utf8Decode(mb_strtoupper($datos['obp_nombre'])), '', ''));
    if ($datos['obp_nombre'] == 'N/A' && $datos['spe_nombre'] == 'N/A') {
        $pdf->SetWidths(array(20, 57, 7, 77));
        $pdf->SetArrayBorders(array('L', 'R', '', 'LR'));
        $pdf->Row(array('', '', '', ''));
    }
    $pdf->SetWidths(array(20, 57, 7, 77));
    $pdf->SetArrayBorders(array('L', 'R', '', 'LR'));
    $pdf->Row(array('', '', $url ? $pdf->Image($url, $pdf->GetX() + 110, $pdf->GetY() - 10, '15', '15', 'jpg', '') : '', ''));


    $pdf->SetArrayBorders(array('T', 'T', '', 'T'));
    $pdf->Row(array("", "", '', ""));

    $pdf->SetArrayBorders(array('LTR', '', 'LRT'));
    $pdf->SetWidths(array(77, 7, 77));
    $pdf->Row(array(utf8Decode("PLAN ESPECIAL"),  '', utf8Decode("CLASIFICACIÓN FUNCIONAL")));

    $pdf->SetWidths(array(20, 57, 7, 20, 57));
    $pdf->SetArrayBorders(array('L', 'R', '', 'L', 'R'));
    $pdf->Row(array(utf8Decode('PLAN: '), utf8Decode(mb_strtoupper($datos['ple_nombre'])), '', 'FINALIDAD : ', utf8Decode(mb_strtoupper($datos['fin_nombre']))));

    $pdf->SetWidths(array(20, 57, 7, 20, 57));
    $pdf->SetArrayBorders(array('BL', 'RB', '', 'LB', 'RB'));
    $pdf->Row(array(utf8Decode('OBJETIVO: '), utf8Decode(mb_strtoupper($datos['ope_nombre'])), '', '', ''));

    $sql2 = "
        SELECT sprs_id,spr_nombre,CONCAT(gpo_id,  ur_id)UR,ur_nombre, tpg_nombre,stp_nombre,ctp_nombre, fun_nombre, sfu_nombre
        FROM (SELECT gpo_id, ur_id, prg_id AS prgs_id, spr_id AS sprs_id, pdf_id, spr_nombre, usr_id, usr_fecha
                FROM cat_Subprograma) sp
        INNER JOIN cat_Subprogramas USING(prgs_id,sprs_id,pdf_id)
        INNER JOIN cat_UnidadResponsable ur USING(gpo_id,ur_id)
        INNER JOIN enc_ProgramaPresupuestario USING ( epp_id )
        INNER JOIN cat_Tipologia USING ( tpg_id )
        INNER JOIN cat_SubTipologia USING ( tpg_id, stp_id ) 
        INNER JOIN cat_ClasificacionTipologia USING ( tpg_id, stp_id, ctp_id ) 
        INNER JOIN enc_Funcional ef USING ( efn_id )
        INNER JOIN cat_Funcion cfun ON cfun.fin_id=ef.fin_id AND cfun.fun_id=ef.fun_id
        INNER JOIN cat_Subfuncion sfun ON sfun.fin_id=ef.fin_id AND sfun.fun_id=ef.fun_id  AND sfun.sfu_id = ef.sfu_id
        WHERE prgs_id=" . $datos['prgs_id'] . "
        AND sprs_id!=0
        and  esr_id = 4
        GROUP by UR,sprs_id
        ORDER BY sprs_id ASC
    ";

    $conexion2 = new AccesoDatos();
    $conexion2->conectar();
    $conexion2->setSql($sql2);
    $conexion2->consultar();
    if ($conexion2->getNumeroRegistros() > 0) {
        $pdf->SetMargins(30, 55, 25);
        $pdf->AddPage();

        $pdf->ln(5);
        $array = [];
        $anchoUrNombre = 151;

        while ($rowspr = $conexion2->extraer()) {

            if (!in_array($rowspr['sprs_id'], $array)) {
                // Altura mínima del bloque SubPr + clasificación + encabezado UR + 1 fila
                $pdf->CheckPageBreak(55);

                $pdf->setAltoLinea(4);
                $pdf->SetRelleno("no");
                $pdf->SetAligns(array('L'));
                $pdf->SetFont('Montserrat-Bold', 'B', 6);
                $pdf->SetWidths(array(18, 143));
                $pdf->SetArrayBorders(array('TL', 'TR'));
                $pdf->Row(array('SubPr: ' . $rowspr['sprs_id'], utf8Decode($rowspr['spr_nombre'])));
                $pdf->SetArrayBorders(array('L', 'R'));

                $pdf->SetRelleno("no");
                $pdf->SetAligns(array('L'));
                $pdf->SetFont('Montserrat-Regular', '', 7);
                $pdf->SetArrayBorders(array('TLR', '', 'TLR'));
                $pdf->SetWidths(array(77, 7, 0));
                $pdf->Row(array(utf8Decode('CLASIFICACIÓN FUNCIONAL'), '', utf8Decode('CLASIFICACIÓN PROGRAMÁTICA')));
                $pdf->SetWidths(array(20, 57, 7, 20, 57));
                $pdf->SetArrayBorders(array('L', 'R', '', 'L', 'R'));
                $pdf->Row(array(utf8Decode('Función:'), utf8Decode(mb_strtoupper($rowspr['fun_nombre'])), '', utf8Decode('Tipología:'), utf8Decode(mb_strtoupper($rowspr['tpg_nombre']))));
                $pdf->Row(array(utf8Decode('Subfunción:'), utf8Decode(mb_strtoupper($rowspr['sfu_nombre'])), '', utf8Decode('Subtipología:'), utf8Decode(mb_strtoupper($rowspr['stp_nombre']))));
                $pdf->Row(array(utf8Decode(''), utf8Decode(mb_strtoupper('')), '', utf8Decode('Clasificación Tipología:'), utf8Decode(mb_strtoupper($rowspr['ctp_nombre']))));

                $array[$rowspr['sprs_id']] = $rowspr['sprs_id'];
                $pdf->SetWidths(array(10, $anchoUrNombre));
                $pdf->SetArrayBorders(array('LT', 'TR'));
                $pdf->Row(array('', utf8Decode('UR´s PARTICIPANTES')));
                $pdf->SetWidths(array(10, $anchoUrNombre));
                $pdf->SetArrayBorders(array('LB', 'RB'));
                $pdf->Row(array($rowspr['UR'], utf8Decode($rowspr['ur_nombre'])));
            } else {
                $pdf->CheckPageBreak(8);
                $pdf->SetArrayBorders(array('TLB', 'TRB'));
                $pdf->SetWidths(array(10, $anchoUrNombre));
                $pdf->Row(array($rowspr['UR'], utf8Decode($rowspr['ur_nombre'])));
            }
        }
    }
    $conexion2->desconectar();
}

$pdf->Output();
