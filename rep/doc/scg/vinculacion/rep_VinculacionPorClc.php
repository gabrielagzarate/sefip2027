<?php
session_start();

ini_set('error_reporting', E_ALL);
ini_set('memory_limit', '200M');
set_time_limit(0);

//librerias
define('FPDF_FONTPATH_CG', '../../fnt/');
require_once('../../../../lib/helpers.php');
require_once('../../../../lib/AccesoDatos.php');
require_once('../../../../lib/TruncateCadena.php');
require_once('../../../../lib/MesNombre.php');
require_once('../../../../ent/UnidadResponsable.php');
require_once('../../../lib/reportetabla.1.8.php');
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';

//Extendiendo la clase pdf para escribir el encabezado y pie de pagina
class reportetabla extends PDF
{

    private $longitud = 100;

    public $gpo_id = null;
    public $ur_id = null;
    public $ue_id = null;
    public $ur;
    public $clcs;
    public $lineas;
    public $mes;
    public function  __construct()
    {
        parent::__construct();

        // Marcos: 04/abr/2018
        // Para que la 116 pueda ver de otras URs
        if ($_SESSION[_S]['gpo_id'] == 1 and $_SESSION[_S]['ur_id'] == 16 and isset($_POST['ur']) && $_POST['ur'] != "") {
            $this->gpo_id = substr($_POST['ur'], 0, 1);
            $this->ur_id  = substr($_POST['ur'], 1, 2);
        } else {
            //todos los usuario de nivel 8 deberan obedecer a la clasificacion administrativa
            $this->gpo_id = $_SESSION[_S]['nvj_id'] >= 3 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
            $this->ur_id = $_SESSION[_S]['nvj_id'] >= 3 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];
        }

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($this->gpo_id);
        $this->ur->setUr_id($this->ur_id);
        $this->ur->select();

        $this->clcs = isset($_POST['clc']) ?  $_POST['clc'] : 'NINGUNA';
        $this->mes  = isset($_POST['mes']) ?  $_POST['mes'] : 0;
    }


    function Footer()
    {
        $this->SetTextColor(0);
        $this->SetMargins(15, '', '', '');
        date_default_timezone_set('America/Mexico_City');
        $tmpb = $this->GetBorders();
        $this->SetY(-20);
        $this->Line(12, 192, 291, 192);

        $current = getdate();

        if ($current["minutes"] < 10)
            $minutes = '0' . $current["minutes"];
        else
            $minutes = $current["minutes"];

        if ($current["seconds"] < 10)
            $seconds = '0' . $current["seconds"];
        else
            $seconds = $current["seconds"];

        $hora = $current["hours"] . ":" . $minutes . ":" . $seconds;
        $this->Cell(10, 10, $hora, 0, -2, "R");

        $this->Cell(253, 10, utf8Decode('Página') . $this->PageNo() . '/{nb}', 0, 0, 'C');

        if ($current["mday"] < 10)
            $mday = '0' . $current["mday"];
        else
            $mday = $current["mday"];

        if ($current["mon"] < 10)
            $mon = '0' . $current["mon"];
        else
            $mon = $current["mon"];

        $fecha = $mday . "/" . $mon . "/" . $current["year"] . '';
        $this->Cell(10, 10, $fecha, 0, 2, "L");

        $this->SetBorders(0);
        $this->lineas = 0;
    }

    function Header()
    {

        $nivelusu = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : ''; //obtenemos el nivel

        // NOMBRE DE LA UNIDAD RESPONSABLE 
        if ($nivelusu >= 3)
            $texto = "DIRECCION DE CONTABILIDAD GUBERNAMENTAL: " . $this->ur->getClave() . " - " . utf8Decode($this->ur->getNombre());
        else
            $texto = "UR: " . $this->ur->getClave() . " - " . utf8Decode($this->ur->getNombre());

        $texto = TruncateCadena(trim($texto), $this->longitud);

        $this->SetMargins(15, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);

        //Encabezado del documento
        $ix = $this->GetX();
        $iy = $this->GetY();
        $iyt = 0;
        $this->Image('../../../../img/'.LOGO_GRIS, $ix + 206, $iy - 3, 74, 22, 'png');

        $this->SetFont('univiapro', 'B', 8);
        $this->SetY($iy + $iyt);
        $this->Cell(195, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
        $iyt += 4;
        $this->SetY($iy + $iyt);
        $this->Cell(195, 5, 'CONTABILIDAD GUBERNAMENTAL '.APP_EJERCICIO, 0, 1, 'L');
        $this->SetFont('univiapro', '', 8);
        $this->Cell(195, 5, $texto, 0, 1, 'L');
        $this->Cell(195, 5, utf8Decode("Relación de CLCs del mes de " . strtolower(mesNombre($this->mes)) . ", con sus pólizas vinculadas"), 0, 1, 'L');


        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', '', 9);

        $this->ln(4);
        //Encabezado de la tabla
        $this->SetAltoLinea(2.5);
        $this->SetWidths(array(8, 14, 100, 30, 12, 25, 25, 10, 30, 8, 10));
        $this->SetAligns(array('L', 'L', 'L', 'L', 'L', 'L', 'L', 'L', 'R', 'L', 'L'));
        $this->SetFont('Arial', 'B', 7);
        $tmp = $this->GetBorders();
        $this->SetBorders(0);

        $this->Row(array('T', '# CLC', 'Concepto', 'CLABE', 'Tipo', 'CveFin', 'Medio Pago', '', '', '', ''));
        $this->Row(array('', '', '', '', 'Recu', utf8Decode('Póliza'), 'Fecha', 'Edo', 'Importe', 'MC', 'APG'));
        $this->Line(15, 31, 292, 31);
        $this->ln(3);
        $this->lineas = 0;
    }
}

$pdf = new reportetabla('L', 'mm', 'Letter');
$pdf->SetProtection(array("print", "annot-forms"), "", PDF_PASSWORD_OWNER_SCG);
$pdf->setOrientation('L');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->lineas = 0;
$pdf->SetFont('Arial', '', 7);

// Obtenemos todo lo que vamos a imprimir
// Incluimos aquí también en TODOS los registros los datos de la CLC
// Aunque es tremendamente ineficiente repetir tanto caracter por línea, sería una sola 
// llamada a la base de datos, y no cientos por cada CLC
// ( Esperemos que el Dios de SQL me perdone )
$consultar = new AccesoDatos();
$consultar->conectar();
$consultar->setSql("
        SELECT scp.*, scg.* 
        FROM 
        ( 
        SELECT gpo_id, ur_id, tcl_id, clc_id, LEFT( MAX(ter_nombre), 1 ) AS ori, MAX(cfi_clave) AS cf, bur_id AS ci,
                        clc_concepto AS con, ecl_id AS edo, mpc_nombre AS mp, ( clc_percepcion - clc_retencion ) AS imp
                    FROM scp_Clc 
                    JOIN enc_ClaveFinanciamiento USING(ecf_id)
                    JOIN ".BD_SINPRES.".cat_ClaveFinanciamiento".APP_EJERCICIO." USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                    INNER JOIN ".BD_SINPRES.".cat_SuborigenRecurso".APP_EJERCICIO." USING( cff_id, fte_id, sft_id, ori_id, sor_id)
                    JOIN ".BD_SINPRES.".cat_TipoRecurso USING ( ter_id )
                    JOIN cat_ModoPagoClc USING( mpc_id )
                    LEFT JOIN scp_Presupuestal p USING( gpo_id, ur_id, clc_id ) 
                    WHERE  ( gpo_id = :gpo AND ur_id = :ur
                        AND ecl_id >= :ecl AND MONTH( clc_fechaEnvio ) = :mes)  
                    GROUP BY gpo_id, ur_id, clc_id
                    ORDER BY clc_id, gpo_id, ur_id 
        ) AS scp
        JOIN 
        ( 
            SELECT gpo_id, ur_id, MIN(pdx_clc_id_origen) AS clc_id, CONCAT( pot_id, '-', prf_id, pma_id ) AS poliza,
                pma_debe AS importe, pma_fecha, epl_id, mmc_id, tmc_id, apg_id
            FROM cg_PolizaMaestro 
                INNER JOIN cg_PolizaDetalleAuxiliar USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
            WHERE gpo_id = :gpo AND ur_id = :ur
                AND pdx_clc_id_origen IN ($pdf->clcs) 
            GROUP BY gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id 
        UNION ALL 
            SELECT gpo_id, ur_id, MIN(da.pdx_clc_id_origen) AS clc_id, CONCAT( pot_id, '-', prf_id, pma_id ) AS poliza,
                pma_debe AS importe, pma_fecha, epl_id, mmc_id, tmc_id, apg_id
                
            FROM cg_PolizaMaestro 
                INNER JOIN cg_PolizaDetalleAuxiliar USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
                INNER JOIN ( 
                    SELECT pdx_gpo_id_origen, pdx_ur_id_origen, pdx_cmp_id_origen, pdx_clc_id_origen 
                    FROM cg_PolizaMaestro 
                        INNER JOIN cg_PolizaDetalleAuxiliar USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
                    WHERE gpo_id = :gpo AND ur_id = :ur
                        AND pdx_clc_id_origen IN ($pdf->clcs) 
                        AND mmc_id = :mmc 
                        AND tmc_id = :tmc 
                ) da USING( pdx_gpo_id_origen, pdx_ur_id_origen, pdx_cmp_id_origen ) 
            WHERE gpo_id = :gpo AND ur_id = :ur 
                AND mmc_id = :mmc_id 
            GROUP BY gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id 
        ) AS scg USING( gpo_id, ur_id, clc_id )
        ORDER BY scp.clc_id, pma_fecha, poliza;
    ");
$parametros= [':gpo'=>$pdf->gpo_id,':ur'=> $pdf->ur_id,':ecl'=>2,':mes'=>$pdf->mes,':mmc'=>'E',':tmc'=>'A','mmc_id'=>'C'];
$consultar->consultar($parametros);
$consultar->desconectar();
$clc = "";
$lineas = 0;

while ($datos = $consultar->extraer()) {

    $pdf->lineas++;

    if ($pdf->lineas > 31) {
        $pdf->AddPage();
        $pdf->lineas = 0;
    }

    // Imprimimos los datos de la CLC actual
    if ($clc != $datos['clc_id']) {

        $pdf->SetFont('Arial', 'B', 7.5);

        $pdf->SetWidths(array(8, 14, 100, 30, 12, 25, 25, 10, 30, 8, 10));
        $pdf->SetAligns(array('L', 'L', 'L', 'L', 'L', 'L', 'L', 'L', 'R', 'L', 'L'));
        $pdf->Row(array(
            $datos['tcl_id'],
            $datos['clc_id'],
            utf8Decode($datos['con']),
            $datos['ci'],
            $datos['ori'],
            $datos['cf'],
            $datos['mp'],
            $datos['edo'],
            number_format($datos['imp'], 2),
            '',
            ''
        ));

        $pdf->lineas++;

        if ($pdf->lineas > 30) {
            $pdf->lineas = 0;
            $pdf->AddPage();
        }
    }

    $pdf->SetBorders(0);
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetWidths(array(8, 14, 100, 30, 12, 25, 25, 10, 30, 8, 10));
    $pdf->SetAligns(array('L', 'L', 'L', 'L', 'L', 'L', 'L', 'L', 'R', 'L', 'L'));
    $pdf->Row(array(
        '',
        '',
        '',
        '',
        '',
        $datos['poliza'],
        substr($datos['pma_fecha'], 8, 2) . "/" . substr($datos['pma_fecha'], 5, 2) . "/" . substr($datos['pma_fecha'], 0, 4),
        $datos['epl_id'],
        number_format($datos['importe'], 2),
        $datos['mmc_id'] . (($datos['tmc_id'] == "C") ? "-C" : ""),
        (($datos['apg_id'] == "ORD") ? $datos['apg_id'] : "")
    ));

    $clc = $datos['clc_id'];
}

$pdf->Output();
