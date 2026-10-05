<?php
session_start();
ini_set('memory_limit','20M');

set_time_limit(0);

//librerias
require_once dirname(__FILE__) . '/../../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';
include_once dirname(__FILE__) . '/../../../lib/conversionNumLetras.php';

$RepUr = (isset($_POST['inputUr']) && $_POST['inputUr'] != '') ? $_POST['inputUr']:'';	
        
class reportetabla extends PDF 
{
    public $gpo_id='';
    public $ur_id='';
    public $ultBorde="TB";
    
    public function  __construct($orientation='L', $unit='mm', $format='letter', $obj = null)
    {			
        parent::__construct($orientation, $unit, $format, $obj);				
    }
    
    function Footer( $lastPage = true)
    {
        $row = $this->getDatos();
        $this->SetY(-45);
        $this->SetX(5);

        /*--------------------------------LEYENDA PARA TODOS LOS REINTEGROS -----------------------------*/
        $ur_id  = $row['gpo_id'].$row['ur_id'];

        if ($ur_id =="802") {
            $this->setX(10);
            $this->SetFont('Arial','B',5.5);
            $this->MultiCell(260,2.5,utf8Decode('LOS SERVIDORES PÃšBLICOS QUE SUSCRIBIMOS EL PRESENTE REEMBOLSO DE ANTICIPOS DE OBRA OTORGADOS EN EJERCICIOS ANTERIORES, DECLARAMOS QUE LOS DATOS CONTENIDOS EN EL MISMO SE ENCUENTRAN RESPALDADOS CON LA DOCUMENTACIÃ“N QUE LOS JUSTIFICA, MISMA QUE EN ORIGINAL SE ENCUENTRA EN RESGUARDO Y CUSTODIA DEL EJECUTOR DE GASTO BENEFICIARIO DE LA TRANSFERENCIA.'),0,'J');
            $this->ln(1);
        } else {
            $this->setX(10);
            $this->SetFont('Arial','B',5.5);
            $this->MultiCell(260,2.5,utf8Decode('LOS SERVIDORES PÃšBLICOS DEL EJECUTOR DE GASTO QUE AUTORIZAMOS Y SUSCRIBIMOS EL PRESENTE REEMBOLSO DE ANTICIPOS DE OBRA OTORGADOS EN EJERCICIOS ANTERIORES, DECLARAMOS QUE SE ENCUENTRAN RESPALDADOS CON LA DOCUMENTACIÃ“N QUE LOS JUSTIFICA, RECONOCIENDO QUE HEMOS CUMPLIDO CON LOS REQUISITOS FISCALES Y ADMINISTRATIVOS VIGENTES.'),0,'J');
            $this->ln(1);
        }
        /*--------------------------------FIRMAS-----------------------------*/
        $this->SetFont('Arial','B',6.5);
        $this->SetX(5);
        $this->Cell(130,2,utf8Decode('JEFE ADMINISTRATIVO'),0,0,'C');
        $this->Cell(130,2,utf8Decode('TITULAR DEL EJECUTOR DEL GASTO'),0,0,'C');
        $this->Cell(130,2,'',0,1,'C');
        $this->ln(10);
        
        $this->SetFont('Arial','B',6.5);
        $this->SetX(5);
        $this->Cell(130,2,utf8Decode($row['rao_nombreAdministrativo']),0,0,'C');
        $this->Cell(130,2,utf8Decode($row['rao_nombreTitular']),0,1,'C');
        // $this->ln(1);
        
        $this->SetFont('Arial','B',6.5);
        $this->SetX(5);
        $this->Cell(130,2,utf8Decode($row['rao_cargoAdministrativo']),0,0,'C');
        $this->Cell(130,2,utf8Decode($row['rao_cargoTitular']),0,0,'C');
        $this->ln(10);
    }
    
    function Header()
    { //ENCABEZADO DEL DOCUMENTO
        $row = $this->getDatos();
        
        if ($row['era_id'] < "3") {
            $ix=$this->GetX();
            $iy=$this->GetY();	
            $this->SetFont('Arial','B',50);
            $this->SetTextColor(200,200,200);
            $this->RotatedText($ix+50,$iy+160,'COPIA',45);
            $this->RotatedText($ix+150,$iy+160,'COPIA',45);
            $this->RotatedText($ix+50,$iy+80,'COPIA',45);
            $this->RotatedText($ix+150,$iy+80,'COPIA',45);
        } else {
            $ix=$this->GetX();
        }
            
        if ($row['era_id'] == "7") {
            $ix=$this->GetX();
            $iy=$this->GetY();	
            $this->SetFont('Arial','B',50);
            $this->SetTextColor(200,200,200);
            $this->RotatedText($ix+50,$iy+180,'CANCELADO',45);
            $this->RotatedText($ix+150,$iy+165,'CANCELADO',45);
            $this->RotatedText($ix+50,$iy+85,'CANCELADO',45);
            $this->RotatedText($ix+150,$iy+85,'CANCELADO',45);
        }

        /*--------------------------------1RA LINEA.-FECHA Y HORA-----------------------------*/		
        
        $this->setX(10);
        $this->setY(10);
        $this->SetFont('Arial','B',6);	
        $this->setBorders(0);
        
        $this->Cell(40,2,utf8Decode(date("d/m/y").' - '.date("H:i:s").'        '),0,0,'L');	
        /*---------------------------------1RA LINEA.-FOLIO----------------------------------*/
        $this->SetFont('Arial','',6);	
        $this->setX(250);
        $this->setBorders(0);
        $this->Cell(10,2,'Folio...:',0,0,'R');
        $this->SetFont('Arial','B',6);	
        $this->Cell(15,2,$row['rao_id'],0,1,'L');
        /*---------------------------------2DA LINEA.-PÃ?GINA---------------------------------*/
        $this->setX(10);
        $this->SetFont('Arial','B',6);	
        $this->setBorders(0);
        $this->Cell(40,2,utf8Decode('PÃ¡gina '.$this->PageNo().'/{nb}'),0,0,'L');
        $y = $this->getY();
        /*---------------------------------2DA LINEA.-FECHA---------------------------------*/
        $this->SetFont('Arial','B',6);	
        $this->setX(250);
        $this->setBorders(0);
        $this->Cell(10,2,'Fecha...:',0,0,'R');
        $this->SetFont('Arial','B',6);	
        $this->Cell(15,2,utf8Decode(date("d/m/y")),0,1,'L');
        /*---------------------------------ENCABEZADO CENTRAL---------------------------------*/						
        $this->SetBorders(2);
        $this->SetFont('Arial','B',8);
        $ytmp = $this->GetY();
        $this->SetXY($ix+42,$ytmp - 6);
        $this->Cell(195,5,'GOBIERNO DEL ESTADO DE OAXACA',0,1,'C');

        $this->SetBorders(1);
        $this->ln(0);
        $this->setX($ix+40);
        $this->SetFont('Arial','B',8);
        $this->SetXY($ix+40,$ytmp-3);
        $this->Cell(195,5,$row['clave'].' - '.utf8Decode($row['ur_nombre']),0,0,'C');	
        $this->SetXY($ix,$ytmp);
        $ytmp=$this->GetY();
        
        $this->SetFont('Arial','B',8);
        $this->ln(3);
        $this->SetXY($ix+40,$ytmp);
        $this->Cell(195,5,'REEMBOLSO DE ANTICIPO DE OBRAS DE EJERCICIOS ANTERIORES',0,0,'C');
        $ytmp=$this->GetY();
        
        //Etiqueta de C. Secretario de Finanzas
        
        $this->SetXY($ix,$ytmp+15);
        $this->SetX(10);
        $ytmp=$this->GetY();
        $this->SetFont('Arial','B',7);
        $this->Cell(50,5,'C.SECRETARIO DE FINANZAS',0,0,'L');
                    
        //Etiqueta presente
        $this->SetXY($ix,$ytmp+3);
        $this->SetX(10);
        $ytmp=$this->GetY();
        $this->SetFont('Arial','B',7);
        $this->Cell(60,5,'P R E S E N T E',0,1,'L');	
    }
}

$gpo_id = '';
$ur_id = '';
$rao_id = $_POST['rao_id'];

if (isset($_POST['origen']) && $_POST['origen'] =="consulta") {
    $gpo_id = substr($_POST['gpour'],0,1);
    $ur_id  = substr($_POST['gpour'],1,2);
} else {
    $gpo_id = $_SESSION[_S]['clasAdm-gpo'];
    $ur_id  = $_SESSION[_S]['clasAdm-ur'];
}

$anyValue = "";
$params = [];
if ($_SERVER['SERVER_NAME'] == "sefip.finanzasoaxaca.gob.mx") {
    $anyValue = "";
} elseif ($_SERVER['SERVER_NAME'] == "uat-sefip.finanzasoaxaca.gob.mx") {
    $anyValue = "";
} else {
    $anyValue = "ANY_VALUE";
}

$sql = '
    SELECT 	
        gpo_id,
        ur_id,
        CONCAT(gpo_id,ur_id) AS clave,
        rao_id,
        ur_nombre,
        oa_id,
        oa_nombre,
        oa_numObra,
        oa_origen,
        toa_id,
        epg_id,
        ore_oficioAut,
        era_id,
        ' . $anyValue . '(coo_anticipo) AS oa_anticipo, 
        ' . $anyValue . '(sft_nombre) AS sft_nombre, 
        ' . $anyValue . '(ter_nombre) AS ter_nombre, 
        cfi_anio,
        cfi_id,
        rao_importe,
        ' . $anyValue . '(cfi_clave) AS cfi_clave,
        ' . $anyValue . '(cfi_nombre) AS cfi_nombre,
        rao_archivoOficio,
        CONCAT(tdf_id," - ",rao_documentoPago) AS Referencia,
        rao_fechaReintegro,
        rao_archivoDocumentoPago,
        rao_nombreTitular,
        rao_cargoTitular,
        rao_nombreAdministrativo,
        rao_cargoAdministrativo,
        ' . $anyValue . '(coo_anticipo) AS coo_anticipo,
        Amortizacion, 
        SUM(IF(fecha2 < ( IF (rao_fechaAplicacion IS NULL,rao_fechaEnvio,rao_fechaAplicacion) ) ,Apli,0)) AS TApli,
        ' . $anyValue . '(coo_anticipo - (IF(Amortizacion IS NULL,0,Amortizacion) + IF( Apli IS NULL ,0,Apli))) AS SALDOXREEM,
        rao_importe, 
        ' . $anyValue . '(coo_anticipo - (IF(rao_importe IS NULL,0,rao_importe) + IF(Amortizacion IS NULL,0,Amortizacion) + IF( Apli IS NULL ,0,Apli))) AS TOTALREINTEGRO 
    FROM scp_ReintegroAnticipoObra 
        INNER JOIN cat_ComplementoObra USING(gpo_id,ur_id,epg_id,ecf_id)
        INNER JOIN cat_UnidadResponsable USING( gpo_id,ur_id )
        INNER JOIN enc_Programatico USING(gpo_id, ur_id,epg_id,prg_id,spr_id,py_id,oa_id )
        INNER JOIN cat_ObraActividad OA USING( gpo_id,ur_id,prg_id,spr_id,py_id,oa_id )
        INNER JOIN enc_ClaveFinanciamiento USING( ecf_id,cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio )
        INNER JOIN sinpres.cat_ClaveFinanciamiento'.APP_EJERCICIO.' USING( cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id, cfi_anio)
        INNER JOIN poa_OrigenRecurso USING( gpo_id,ur_id,epg_id,ecf_id )
        INNER JOIN sinpres.cat_SuborigenRecurso'.APP_EJERCICIO.' USING ( cff_id,fte_id,sft_id,ori_id,sor_id)
        INNER JOIN sinpres.cat_TipoRecurso USING( ter_id )
        INNER JOIN sinpres.cat_Subfuente'.APP_EJERCICIO.' USING( fte_id,sft_id)
        LEFT JOIN(
            SELECT 
                gpo_id,ur_id,epg_id,ecf_id,rao_importe AS Apli,rao_fechaAplicacion AS fecha2
            FROM scp_ReintegroAnticipoObra
            WHERE era_id = 6 
                AND gpo_id = :gpo_id
                AND ur_id = :ur_id 
                AND rao_id != :rao_id
            )AP USING(gpo_id,ur_id,epg_id,ecf_id)
        LEFT JOIN(
            SELECT 
                gpo_id,ur_id,epg_id,ecf_id, SUM(ncc_retencion) AS Amortizacion
            FROM scp_Contable
                INNER JOIN scp_Afectacion USING(gpo_id, ur_id,clc_id,afe_id)
                INNER JOIN scp_Clc USING(gpo_id,ur_id,clc_id,ecf_id,tcl_id)
            WHERE gpo_id = :gpo_id
                AND ur_id = :ur_id
                AND ecl_id IN(10,11,12,13,16) 
                AND concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id) = "21194005" 
                AND tcl_id IN ("F","G","H")
            GROUP BY gpo_id,ur_id,epg_id,ecf_id
            )AM USING(gpo_id,ur_id,epg_id,ecf_id)
    WHERE gpo_id = :gpo_id
        AND ur_id = :ur_id
        AND rao_id = :rao_id
';
$params = [
    ':gpo_id' => $gpo_id,
    ':ur_id' => $ur_id,
    ':rao_id' => $rao_id
];

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar($params);
$conexion->desconectar();

$pdf = new Reportetabla('L','mm','letter');

$pdf->AliasNbPages();
$row = $conexion->extraer();
$pdf->setDatos($row);

$pdf->AddPage();

/* Numero y Nombre de la Obra */
$alto = 2;
$pdf->setX(10);
$pdf->SetFont('Arial','B',6.5);	
$yt= $pdf->gety();
$pdf->Cell(40,3,'OBRA:',0,0);
$xt= $pdf->getx();
$pdf->SetFont('Arial','B',6.5);	
$pdf->SetAltoLinea(1.5);
$pdf->setBorders(0);
$pdf->SetWidths(array(220)); 
$pdf->SetAligns(array('L'));
$pdf->setY($yt);
$pdf->setX($xt);
$pdf->Row(array($row['oa_id'].'-'.utf8Decode($row['oa_nombre'])));					
$pdf->ln(0);
/* OFICIO DE AUTORIZACIÃ“N DEL RECURSO DE LA OBRA y NÂ° DE SSPP DE LA OBRA */ 
$pdf->setX(10);
$pdf->SetFont('Arial','B',6.5);	
$pdf->Cell(40,3,utf8Decode('OFICIO DE AUTORIZACIÓN:'),0,0);	
$pdf->Cell(75,3,utf8Decode($row['ore_oficioAut']),0,0);

$pdf->SetFont('Arial','B',6.5);	
$pdf->Cell(29,3,utf8Decode('N° DE OBRA SPPP/SPIP:'),0,0);	
$pdf->Cell(55,3,$row['oa_numObra'],0,1);
$pdf->ln(0);
/* OFICIO DE AUTORIZACIÃ“N DEL RECURSO DE LA OBRA y NÂ° DE SSPP DE LA OBRA */ 
$pdf->setX(10);
$pdf->SetFont('Arial','B',6.5);	
$pdf->Cell(40,3,'SUBFUENTE :',0,0);
$pdf->Cell(75,3,utf8Decode($row['sft_nombre']),0,0);

            
$pdf->SetFont('Arial','B',6.5);	
$pdf->Cell(29,3,'EJERCICIO :',0,0);
$pdf->Cell(55,3,utf8Decode($row['cfi_anio']),0,0);

$pdf->SetFont('Arial','B',6.5);	
$pdf->Cell(20,3,'REFERENCIA :',0,0);	
$pdf->Cell(25,3,$row['cfi_id'],0,1);
$pdf->ln(0);

$pdf->setX(10);
$pdf->SetFont('Arial','B',6.5);	
$pdf->Cell(40,3,utf8Decode('APORTACIÓN :'),0,0);	
$pdf->Cell(30,3,utf8Decode($row['ter_nombre']),0,1);
/* CLAVE DE FINANCIAMIENTO */ 
$pdf->setX(10);
$pdf->SetFont('Arial','B',6.5);	
$pdf->Cell(40,3,'CLAVE DE FINANCIAMIENO :',0,0);
$pdf->Cell(80,3,utf8Decode($row['cfi_clave'].' - '.utf8Decode($row['cfi_nombre'])),0,1);	
$pdf->ln(1);
/* DOCUMENTACIÃ“N ANEXA */ 


/* CONCEPTO */ 
$pdf->setX(10);
$pdf->SetFont('Arial','B',6.5);	
$pdf->Cell(40,3,'CONCEPTO :',0,1);
$pdf->setX(50);
$pdf->Cell(80,3,utf8Decode('REEMBOLSO'),0,1);	
$pdf->ln(3.5);
/*------------------------TABLA CABECERA-------------------------------------------*/
$pdf->setRelleno("no");
$pdf->SetFont('Arial','B',5.5);
$pdf->SetTextColor(0,0,0);
$pdf->setX(10);
$pdf->SetBorders("TB");
// $pdf->SetBorders("ultBorde");
$pdf->SetWidths(array(30,+60,30,35,50,30,15));
$pdf->SetDrawColor(0,0,0);
$pdf->SetAligns(array('C','L','C','C','R','R','R'));
$pdf->Row(array(utf8Decode('N°'),
                utf8Decode('DESCRIPCIÓN'),
                utf8Decode('REFERENCIA'),
                utf8Decode('FECHA REFERENCIA'),
                utf8Decode(''),
                utf8Decode('IMPORTE NETO'),
                utf8Decode('+/-'),
            ));
$pdf->ln(5);
$pdf->SetTextColor(0,0,0);
/*----------------------Cuerpo de la tabla (PRIMERA FILA)------------------------*/
$pdf->setRelleno("no");
$pdf->SetFont('Arial','B',5.5);
$pdf->SetTextColor(0,0,0);
$pdf->setX(10);
$pdf->SetBorders(0);
$pdf->SetWidths(array(30,+60,30,35,50,30,15));
$pdf->SetDrawColor(180,180,180);
$pdf->SetAligns(array('C','L','C','C','R','R','R'));
$pdf->Row(array(utf8Decode(''),
                utf8Decode('ANTICIPO DE OBRA POR AMORTIZAR'),
                utf8Decode(''),
                utf8Decode(''),
                utf8Decode(''),
                number_format($row['coo_anticipo'],2),
                utf8Decode(''),
            ));
$pdf->ln(1.5);
$pdf->SetTextColor(0,0,0);
/*----------------------Cuerpo de la tabla (SEGUNDA FILA)------------------------*/
$pdf->setRelleno("no");
$pdf->SetFont('Arial','B',5.5);
$pdf->SetTextColor(0,0,0);
$pdf->setX(10);
$pdf->SetBorders(0);
$pdf->SetWidths(array(30,+60,30,35,50,30,15));
$pdf->SetDrawColor(180,180,180);
$pdf->SetAligns(array('C','L','C','C','R','R','R'));
$pdf->Row(array(utf8Decode(''),
                utf8Decode('AMORTIZACIONES'),
                utf8Decode(''),
                utf8Decode(''),
                utf8Decode(''),
                number_format($row['Amortizacion'],2),
                utf8Decode('-'),
            ));
$pdf->ln(1.5);
$pdf->SetTextColor(0,0,0);
/*----------------------Cuerpo de la tabla (TERCER FILA)------------------------*/
$pdf->setRelleno("no");
$pdf->SetFont('Arial','B',5.5);
$pdf->SetTextColor(0,0,0);
$pdf->setX(10);
$pdf->SetBorders(0);
$pdf->SetWidths(array(30,+60,30,35,50,30,15));
$pdf->SetDrawColor(180,180,180);
$pdf->SetAligns(array('C','L','C','C','R','R','R'));
$pdf->Row(array(utf8Decode(''),
                utf8Decode('APLICACIÓN DE REEMBOLSOS ANTERIORES'),
                utf8Decode(''),
                utf8Decode(''),
                utf8Decode(''),
                number_format($row['TApli'],2),
                utf8Decode('-'),
            ));
$pdf->ln(1.5);
$pdf->SetTextColor(0,0,0);
/*----------------------Cuerpo de la tabla (CUARTA FILA)------------------------*/
$pdf->setRelleno("no");
$pdf->SetFont('Arial','B',5.5);
$pdf->SetTextColor(0,0,0);
$pdf->setX(10);
$pdf->SetBorders(0);
$pdf->SetWidths(array(30,+60,30,35,50,30,15));
$pdf->SetDrawColor(180,180,180);
$pdf->SetAligns(array('C','C','C','C','R','R','R'));
$pdf->Row(array(utf8Decode(''),
                utf8Decode(''),
                utf8Decode(''),
                utf8Decode(''),
                utf8Decode('TOTAL DE PERCEPCIONES'),
                number_format($row['coo_anticipo']-($row['Amortizacion'] + $row['TApli']) ,2),
                utf8Decode('='),
            ));
$pdf->ln(1.5);
$pdf->SetTextColor(0,0,0);
/*----------------------Cuerpo de la tabla (QUINTA FILA)------------------------*/
$pdf->setRelleno("no");
$pdf->SetFont('Arial','B',5.5);
$pdf->SetTextColor(0,0,0);
$pdf->setX(10);
$pdf->SetBorders(0);
$pdf->SetWidths(array(30,+60,30,35,50,30,15));
$pdf->SetDrawColor(180,180,180);
$pdf->SetAligns(array('C','L','C','C','R','R','R'));
$pdf->Row(array(utf8Decode($row['rao_id']),
                utf8Decode('REEMBOLSO DE ANTICIPO DE OBRA'),
                utf8Decode($row['Referencia']),
                utf8Decode($row['rao_fechaReintegro']),
                utf8Decode(''),
                number_format($row['rao_importe'],2),
                utf8Decode(''),
                ));
$pdf->ln(1);
$pdf->SetTextColor(0,0,0);
/*----------------------Cuerpo de la tabla (SEXTA FILA)------------------------*/

/*----------------------Cuerpo de la tabla (SEPTIMA FILA)------------------------*/
$pdf->setRelleno("no");
$pdf->SetFont('Arial','B',5.5);
$pdf->SetTextColor(0,0,0);
$pdf->setX(10);
$pdf->SetBorders(0);
$pdf->SetWidths(array(30,+60,30,35,50,30,15));
$pdf->SetDrawColor(180,180,180);
$pdf->SetAligns(array('C','C','C','C','R','R','R'));
$pdf->Row(array(	utf8Decode(''),
                    utf8Decode(''),
                    utf8Decode(''),
                    utf8Decode(''),
                    utf8Decode('TOTAL ANTICIPO DE OBRA POR AMORTIZAR'),
                    number_format($row['coo_anticipo']-($row['Amortizacion'] + $row['TApli']+ $row['rao_importe'] ),2),
                    utf8Decode('='),
                ));
$pdf->ln(8);
$pdf->SetTextColor(0,0,0);
/*----------------------LEYENDA DEL REINTEGRO------------------------*/

// $alto = 2.5;
// $pdf->setX(7);
// $yt= $pdf->gety();
// $xt= $pdf->getx();
// $pdf->SetFont('Arial','B',6);	
// $pdf->SetAltoLinea(2.5);
// $pdf->setBorders(0);
// $pdf->SetWidths(array(260)); 
// $pdf->SetAligns(array('L'));
// $pdf->setY($yt);
// $pdf->setX($xt);
// $pdf->Row(array(utf8Decode(
    // 'LOS SERVIDORES PÃšBLICOS QUE SUSCRIBIMOS EL PRESENTE REINTEGRO DE ANTICIPOS DE OBRA DE EJERCICIOS ANTERIORES, SE ENCUENTRAN RESPALDADOS CON LA DOCUMENTACIÓN QUE LOS COMPRUEBA Y JUSTIFICA, MISMA QUE EN ORIGINAL SE QUEDA EN NUESTROS RESGUARDO Y CUSTODIA, RECONOCIENDO QUE HEMOS CUMPLIDO CON LOS REQUISITOS FISCALES Y ADMINISTRATIVOS VIGENTES, LO ANTERIOR CON FUNDAMENTO EN LOS ARTÃ?CULOS 4 Y 48 DE LA LEY ESTATAL DE PRESUPUESTO Y RESPONSABILIDAD HACENDARIA Y 58, 61, DEL 186 AL 193 DEL REGLAMENTO DE LA LEY ESTATAL DE PRESUPUESTO Y RESPONSABILIDAD HACENDARIA.
    // '))
// );
// $pdf->ln(5);	
// die();
$pdf->SetProtection(array('print', 'annot-forms'), "",PDF_PASSWORD_OWNER_SCP);
$pdf->Output();	