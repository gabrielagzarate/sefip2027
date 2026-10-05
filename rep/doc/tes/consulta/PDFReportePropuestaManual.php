<?php
    
    session_start();
    ini_set('memory_limit','20M');
    
    set_time_limit(0);
    //librerias
    require_once dirname(__FILE__).'/../../lib/constantes.php';    
    require_once dirname(__FILE__).'/../../../../lib/helpers.php';
    require_once('../../lib/reportetabla.1.0.0.php');	
    require_once('../../../lib/AccesoDatos.php');
    require_once('../../../ent/UnidadResponsable.php');

    
    $lot_id = (isset($_POST['lot_id']) && $_POST['lot_id'] !='') ? $_POST['lot_id'] : '';
    $fpa_id = (isset($_POST['fpa_id']) && $_POST['fpa_id'] !='') ? $_POST['fpa_id'] : '';
    $msc_id = (isset($_POST['msc_id']) && $_POST['msc_id'] !='') ? $_POST['msc_id'] : '';
    $lot_ejercicio = (isset($_POST['lot_ejercicio']) && $_POST['lot_ejercicio'] !='') ? $_POST['lot_ejercicio'] : '';
    $lot_tipo = (isset($_POST['lot_tipo']) && $_POST['lot_tipo'] !='') ? $_POST['lot_tipo'] : '';
    class reportetabla extends PDF {

        public $gpo_id='';
        public $ur_id='';
        
        
        public function  __construct(){			
            parent::__construct();

        }

        function Header()//Encabezado del documento
        {
            $this->SetMargins(10,'','','');
            $this->SetTextColor(0,0,0);
            $this->SetBorders(0);
            $this->SetY(2);
            $this->ln(2);
            $row = $this->getDatos();
            
            $this->SetMargins(10,'','','');
            $this->SetTextColor(0,0,0);
            $this->SetFillColor(255,255,255);
            $this->SetBorders(0);
            $this->SetY(2);
            $this->ln(2);
            $ix=$this->GetX();
            $iy=$this->GetY();
            //logos
            $this->Image('../../img/logosefin-color-fblanco.jpg', $ix+98,$iy+1, '95' , '20','JPG', '');
            
            $this->SetFont('UniviaPro-Bold','B',16);
            $this->SetTextColor(200,200,200);
            $this->RotatedText($ix+4,$iy+22,'2 0 1 8',90);
            $this->SetTextColor(0,0,0); 
            
            $this->SetFont('UniviaPro-Bold','B',8); 		
            $this->SetXY($ix+4,$iy+4);
            $ytmp=$this->GetY();
            $this->Cell(195,5,'GOBIERNO DEL ESTADO DE OAXACA',0,1,'L');		
            $this->SetXY($ix+4,$ytmp+4);
            $ytmp=$this->GetY();
            
            $this->SetFont('UniviaPro-Bold','B',8);
            $this->Cell(180,5,'SECRETARIA DE FINANZAS TESORERIA',0,1,'L');	
            $this->SetXY($ix+4,$ytmp+4);
            $ytmp=$this->GetY();
            $this->SetFont('UniviaPro-Bold','B',8);
            $this->Cell(180,5,'COORDINACION DE CONTROL FINANCIERO',0,1,'L');	
            $this->SetXY($ix+4,$ytmp+4);
            $ytmp=$this->GetY();
            $this->Cell(180,5,'DEPARTAMENTO DE PROGRAMACION DE MINISTRACIONES Y PAGOS',0,1,'L');	
            $this->SetXY($ix+4,$ytmp+4);
            $this->SetFont('UniviaPro-Bold','B',7);
            $ytmp=$this->GetY();
            
            $this->Cell(5,5,'',0,0,'L');
            $this->SetFont('UniviaPro-Bold','B',7);
            $this->Cell(170,5,'',0,1,'R');
            
            $ytmp=$this->GetY();
            $this->setX(10);
            $this->setDrawColor(200,200,200);
            $this->Line(10,$ytmp,203,$ytmp); 						
            $this->ln(4);
            $this->setDrawColor(170,170,170);
            $this->SetFillColor(199,209,219);
            
            $this->setX(10);
            $this->SetWidths(array(30,124,39));
            $this->SetAligns(array('L','L','R'));
            $this->SetFont('UniviaPro-Bold','B',7);
            $this->setBorders('');
            $this->setAltoLinea(4);
            $this->SetRelleno("si");					
            $this->ln(1);
            $this->setBorders(0);
            $this->SetFillColor(255,255,255);

            $this->SetXY($ix+4,$ytmp+4);
            $ytmp=$this->GetY();

            //generamos los titulos de la tabla
            $this->setDrawColor(170,170,170);
            $this->SetFillColor(199,209,219);
            
            $this->setX(15);
            $this->SetWidths(array(5,15,20,10,15,25,25,20,20,28,20,28,20,20));
            $this->SetAligns(array('L','C','C','C','C','C','C','C','C','C','C','C','C','C'));
            $this->SetFont('UniviaPro-Bold','B',7);
            $this->setBorders('');
            $this->setAltoLinea(4);			
            $this->Row(array('#','FE.PAGO','LOTE EGR','CLC','ORDEN','UR RESPONSABLE','BENEFICIARIO','IMPORTE', 'BANCO', 'CTA CARGO', 'CTA NOMBRE', 'CTA DESTINO', 'CVE. FIN.', 'PROP PAGO'));
           
        }
        
        function Footer( $lastPage = true)
        {
            $row = $this->getDatos();
            $tmpb=$this->GetBorders();
            $this->SetY(-45);
                session_start();
                $sql="SELECT concat(usr_nombre,' ',usr_paterno,' ',usr_materno) nombre ,usr_titulo FROM cat_Usuario where usr_id= ? ";
                $parametro = [$_POST['usr_id']];
                $conexion3 = new AccesoDatos();
                $conexion3->conectar();
                $conexion3->setSql($sql);
                $conexion3->consultar($parametro);
                $conexion3->desconectar();
                $row = $conexion3->extraer();
            
                
                
            
                $this->SetFont('UniviaPro-Bold','B',6.5);
                $this->Cell(90,2,'ELABORADO Y REVISADO',0,0,'C');
                $this->Cell(90,2,'AUTORIZADO',0,1,'C');
                                    
                $this->Cell(130,2,'',0,1,'C');
                $this->ln(1);
                $this->SetFont('UniviaPro-Bold','B',6.5);
                $this->Cell(90,2,utf8Decode($row['nombre']),0,0,'C');
                $this->Cell(90,2,utf8Decode('	IVÁN ELADIO GÓMEZ SANTIAGO'),0,1,'C');
                
                $this->Cell(130,2,'',0,1,'C');
                $this->SetFont('UniviaPro-Bold','B',6.5);
                $this->Cell(90,2,utf8Decode($row['usr_titulo']),0,0,'C');
                $this->Cell(90,2,utf8Decode('	DEPARTAMENTO DE PLANEACION Y EVALUACION FINANCIERA'),0,1,'C');
            
            $this->ln();
            $this->Cell(0,10,'Página '.$this->PageNo().'/{nb}',0,0,'C');
            $this->Cell(0,10,date("d/m/y").' - '.date("H:i:s").'        ',1,0,'R');
        }
    }
    
    
    $pdf = new reportetabla('P','mm','Letter');
    $pdf->setOrientation('L');
    $pdf->AliasNbPages();
    $pdf->SetAutoPageBreak(true , 40);
    $pdf->AddFont('UniviaPro-Regular','','UniviaPro-Regular.php');
    $pdf->AddFont('UniviaPro-Bold','B','UniviaPro-Bold.php');
    
        
    $sql= "SELECT 
                LO.fpa_id,
                LO.msc_id,
                LO.lot_id,
                LO.lot_ejercicio,
                CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) AS lote_id,
                LO.lom_importe,
                LO.cbs_id,
                LO.cbb_id,
                LO.lom_concepto,
                LO.lom_beneficiario,
                LO.clc_id,
                CB.cbs_nombre,
                IB.iba_abreviatura,
                LT.lot_fechaPago,
                CF.ecf_clave,
                CONCAT(gpo_id,ur_id) as ur,
                clc_id
                from ".BD_TESORERIA.".lot_PropuestaManual LO
                inner join ".BD_TESORERIA.".lot_Lote as LT using(fpa_id,msc_id,lot_id,lot_ejercicio)
                inner join ".BD_TESORERIA.".ban_CuentaBancariaSefin as CB using(cbs_id)
                inner join ".BD_TESORERIA.".cat_InstitucionBancaria as IB using(iba_id)
                inner join ".BD_TESORERIA.".vin_PropuestaManualClaveFinanciamiento as VM using(lom_id)
                inner join ctrl". $_POST['lot_ejercicio']."enc_ClaveFinanciamiento as CF using(ecf_id)
            WHERE
                LO.lot_id = ?
                AND LO.fpa_id= ?
                AND LO.msc_id= ?
                AND LO.lot_ejercicio= ? "; 
    
    $parametros = [$_POST['lot_id'],$_POST['fpa_id'],$_POST['msc_id'],$_POST['lot_ejercicio']];
    $datos = new AccesoDatos();			
    $datos->conectar();
    $datos->setSql($sql);
    $datos->consultar($parametros);
    $datos->desconectar();
    $row = $datos->extraer();
    $pdf->setDatos($row);
    $pdf->setAltoLinea(1.5);
    $pdf->AddPage();	
    $celda=1;
    $unidad='';
    $bolunidad=false;
    $gasto='';
    $bolgasto=false;
    $identificador='';
    $bolidentificador=false;
    $pdf->SetFont('UniviaPro-Bold','B',7); ///tipo de letra para cada titulo
    $pdf->SetFillColor(255,255,255);
    $contador=1;
    $cadena="";
    $fecha="";
    $suma=0.0;
    do{
        
        $pdf->CheckPageBreak(30);
        $pdf->ln(4);
        $pdf->setAltoLinea(3);
        $pdf->SetX(15);
        $pdf->SetFont('UniviaPro-Bold','B',6); ///tipo de letra para cada titulo
        $pdf->SetWidths(array(5,15,20,10,15,25,25,20,20,28,20,28,20,20));
        $pdf->SetAligns(array('L','C','C','C','C','C','C','C','C','C','C','C','C','C'));

        $pdf->Row(array($contador++,$row['lot_fechaPago'],'-',$row['clc_id'],'-','-',utf8Decode($row['lom_beneficiario']),number_format($row['lom_importe'],2),utf8Decode($row['iba_abreviatura']),$row['cbs_id'],$row['cbs_nombre'],$row['cbb_id'],$row['ecf_clave'],$row['lote_id']));

        $bolprograma=true;
        $suma += $row['lom_importe'];
        $cadena.=$row['lote_egreso']."|".$row['clc_id']."|".$row['lom_importe']."|".$row['lote_id'];
        $pdf->SetRelleno("no"); 

        $fecha=$row['lot_fechaPago'];
        $pdf->setAltoLinea(1.5);	
    }while($row=$datos->extraer());

    $pdf->Row(array("","","","","","","","","","","","","",""));
    $pdf->Row(array("","","","","","","TOTAL",number_format($suma,2),"","","","","",""));
    $pdf->setAltoLinea(1.5);

    $pdf->SetFont('UniviaPro-Bold','B',8);
    $pdf->CheckPageBreak(30);
    $pdf->ln(8);
    $pdf->setAltoLinea(3);
    $pdf->SetX(15);
    $pdf->MultiCell(180,4,'CADENA ORIGINAL: '.md5($cadena),0,'J');
    $arrFecha = explode("-", $fecha);
    $mes="";
    switch ($arrFecha[1]) {
        case 1:
            $mes="Enero";
            break;
        case 2:
            $mes="Febrero";
            break;
        case 3:
            $mes="Marzo";
            break;
        case 4:
            $mes="Abril";
            break;
        case 5:
            $mes="Mayo";
            break;
        case 6:
            $mes="Junio";
            break;
        case 7:
            $mes="Julio";
            break;
        case 8:
            $mes="Agosto";
            break;
        case 9:
            $mes="Septiembre";
            break;
        case 10:
            $mes="Octubre";
            break;
        case 11:
            $mes="Noviembre";
            break;
        case 12:
        default:
            $mes="Diciembre";
            break;
    }
    $pdf->ln(8);
    $pdf->setAltoLinea(3);
    $pdf->SetX(15);
    $dia=date('d');
    $anio=date('Y');
    $pdf->MultiCell(180,4,utf8Decode('Reyes Mantecón, Oaxaca a '.$arrFecha[2].' de '.$mes.' del '.$arrFecha[0]),0,'C');
        

    
    $pdf->Output();	
 
?>