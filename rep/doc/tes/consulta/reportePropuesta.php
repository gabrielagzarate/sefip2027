<?php
session_start();
ini_set('memory_limit','20M');

set_time_limit(0);
//librerias
require_once('../../lib/reportetabla.1.0.0.php');	
require_once('../../../lib/AccesoDatos.php');
require_once('../../../ent/UnidadResponsable.php');

$det_prop=(isset($_POST['det_prop']) && $_POST['det_prop'] !='') ? $_POST['det_prop'] : '';

class reportetabla extends PDF {
    public $gpo_id = '';
    public $ur_id = '';
    
    public function  __construct() {
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
        $ix = $this->GetX();
        $iy = $this->GetY();
        //logos
        $this->Image('../../img/logosefin-color-fblanco.jpg', $ix+186,$iy+1, '90' , '20','JPG');

        $this->SetFont('UniviaPro-Bold','B',8);
        $this->SetXY($ix+4,$iy+4);
        $ytmp=$this->GetY();
        $this->Cell(195,5,'GOBIERNO DEL ESTADO DE OAXACA',0,1,'L');
        $this->SetXY($ix+4,$ytmp+4);
        $ytmp=$this->GetY();
        
        $this->SetFont('UniviaPro-Bold','B',8);
        $this->Cell(180,5,'SECRETARIA DE FINANZAS ".BD_TESORERIA."',0,1,'L');
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
        
            $this->SetFont('UniviaPro-Bold','B',6.5);
            $this->Cell(90,2,'ELABORADO Y REVISADO',0,0,'C');
            $this->Cell(90,2,'AUTORIZADO',0,1,'C');
                                
            $this->Cell(130,2,'',0,1,'C');
            $this->ln(1);
            $this->SetFont('UniviaPro-Bold','B',6.5);
            $this->Cell(90,2,utf8Decode("C.P. OLEGARIO GERARDO SILVA CRUZ"),0,0,'C');
            $this->Cell(90,2,utf8Decode('	IVÁN ELADIO GÓMEZ SANTIAGO'),0,1,'C');
            
            $this->Cell(130,2,'',0,1,'C');
            $this->SetFont('UniviaPro-Bold','B',6.5);
            $this->Cell(90,2,utf8Decode(""),0,0,'C');
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

$sql='SELECT pro_ejercicio FROM ".BD_TESORERIA.".pro_Propuesta where pro_id= ? ;';
$conexion= new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar(array($det_prop));
$anio=APP_EJERCICIO;

if ($conexion->getNumeroRegistros() > 0) {
    $row = $conexion->extraer();
    $anio = $row['pro_ejercicio'];
}
$params = [];
//buscamos los datos segun el ejercicio
switch ($anio) {
    case 2017:
        $params = [];
            $sql ="SELECT 
                    p.pro_id AS proDet_propuesta,
                    p.pro_ejercicio,
                    p.pro_fechaGeneracion,
                    dp.gpo_id ,
                    dp.ur_id ,
                    dp.clc_id ,
                    dp.pro_importeClc AS importe,
                    clc.clc_beneficiario ,
                    clc.clc_concepto ,
                    clc.clc_fechaAutorizacion AS proDet_fecha,
                    clc.ecf_id,
                    BN.bsf_id as cbs_id,
                    clc.bur_id,
                    CV.ecf_clave as cve_fin,
                    concat(clc.gpo_id,clc.ur_id) ur,
                    IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
                    CX.clc_cereCo as folio,
                    UR.ur_nombre as responsable,
                    LE.loe_clave as lote_egreso,
                    CB.cbs_nombre as nombre_cuenta,
                    IB.iba_abreviatura as nombre_banco,
                    if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
                FROM
                    ".BD_TESORERIA.".pro_Propuesta AS p
                INNER JOIN ".BD_TESORERIA.".pro_PropuestaClc AS dp USING (pro_id)
                INNER JOIN ".BD_CTRL_2017.".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
                INNER JOIN ".BD_CTRL_2017.".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                LEFT JOIN ".BD_CTRL_2017.".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                LEFT JOIN ".BD_CTRL_2017.".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                INNER JOIN ".BD_CTRL_2017.".enc_BancosSefinCveFin BN using (ecf_id)
                INNER JOIN ".BD_CTRL_2017.".enc_ClaveFinanciamiento CV using (ecf_id)
                INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin CB on (BN.bsf_id = CB.cbs_id)
                INNER JOIN ".BD_TESORERIA.".cat_InstitucionBancaria IB using (iba_id)
                INNER JOIN ".BD_CTRL_2017.".cat_UnidadResponsable UR USING(gpo_id,ur_id)
                LEFT JOIN ".BD_TESORERIA.".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
                WHERE
                    pro_id= ? ;";
        $params = [$det_prop];
        break;
    case 2018:
        $prams = [];
        $sql ="SELECT
            p.pro_id AS proDet_propuesta,
            p.pro_ejercicio,
            p.pro_fechaGeneracion,
            dp.gpo_id ,
            dp.ur_id ,
            dp.clc_id ,
            dp.pro_importeClc AS importe,
            clc.clc_beneficiario ,
            clc.clc_concepto ,
            clc.clc_fechaAutorizacion AS proDet_fecha,
            clc.ecf_id,
            BS.cbs_id,
            clc.bur_id,
            CV.ecf_clave as cve_fin,
            concat(clc.gpo_id,clc.ur_id) ur,
            IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
            CX.clc_cereCo as folio,
            UR.ur_nombre as responsable,
            LE.loe_clave as lote_egreso,
            BS.cbs_nombre as nombre_cuenta,
            IB.iba_abreviatura as nombre_banco,
            if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
        FROM
            ".BD_TESORERIA.".pro_Propuesta AS p
            INNER JOIN ".BD_TESORERIA.".pro_PropuestaClc AS dp USING (pro_id)
            INNER JOIN ".BD_CTRL_2018.".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
            INNER JOIN ".BD_CTRL_2018.".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
            left JOIN ".BD_CTRL_2018.".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
            left JOIN ".BD_CTRL_2018.".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
            INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento CF using(ecf_id)
            INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS using(cbs_id)
            INNER JOIN ".BD_CTRL_2018.".cat_UnidadResponsable UR using(gpo_id,ur_id)
            INNER JOIN ".BD_TESORERIA.".cat_InstitucionBancaria as IB using(iba_id)
            INNER JOIN ".BD_CTRL_2018.".enc_ClaveFinanciamiento CV using (ecf_id)
            LEFT JOIN ".BD_TESORERIA.".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
            WHERE
            p.pro_id = ? ;";
        $params = [$det_prop];
        break;
    case 2019:
        $params = [];
        $sql ="SELECT
            p.pro_id AS proDet_propuesta,
            p.pro_ejercicio,
            p.pro_fechaGeneracion,
            dp.gpo_id ,
            dp.ur_id ,
            dp.clc_id ,
            dp.pro_importeClc AS importe,
            clc.clc_beneficiario ,
            clc.clc_concepto ,
            clc.clc_fechaAutorizacion AS proDet_fecha,
            clc.ecf_id,
            BS.cbs_id,
            clc.bur_id,
            CV.ecf_clave as cve_fin,
            concat(clc.gpo_id,clc.ur_id) ur,
            IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
            CX.clc_cereCo as folio,
            UR.ur_nombre as responsable,
            '' as lote_egreso,
            BS.cbs_nombre as nombre_cuenta,
            IB.iba_abreviatura as nombre_banco,
            if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
        FROM
            ".BD_TESORERIA.".pro_Propuesta AS p
        INNER JOIN ".BD_TESORERIA.".pro_PropuestaClc AS dp USING (pro_id)
        INNER JOIN ".BD_CTRL_2019.".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
        INNER JOIN ".BD_CTRL_2019.".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
        INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento CF using(ecf_id)
        INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS using(cbs_id)
        INNER JOIN ".BD_CTRL_2019.".cat_UnidadResponsable UR using(gpo_id,ur_id)
        INNER JOIN ".BD_TESORERIA.".cat_InstitucionBancaria as IB using(iba_id)
        INNER JOIN ".BD_CTRL_2019.".enc_ClaveFinanciamiento CV using (ecf_id)
        LEFT JOIN ".BD_TESORERIA.".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
        WHERE
            p.pro_id = ? ;";
            $params = [$det_prop];
        break;
    case 2020:
        $params = [];
        $sql ="SELECT
            p.pro_id AS proDet_propuesta,
            p.pro_ejercicio,
            p.pro_fechaGeneracion,
            dp.gpo_id ,
            dp.ur_id ,
            dp.clc_id ,
            dp.pro_importeClc AS importe,
            clc.clc_beneficiario ,
            clc.clc_concepto ,
            clc.clc_fechaAutorizacion AS proDet_fecha,
            clc.ecf_id,
            BS.cbs_id,
            clc.bur_id,
            CV.ecf_clave as cve_fin,
            concat(clc.gpo_id,clc.ur_id) ur,
            IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
            CX.clc_cereCo as folio,
            UR.ur_nombre as responsable,
            '' as lote_egreso,
            BS.cbs_nombre as nombre_cuenta,
            IB.iba_abreviatura as nombre_banco,
            if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
        FROM
            ".BD_TESORERIA.".pro_Propuesta AS p
            INNER JOIN ".BD_TESORERIA.".pro_PropuestaClc AS dp USING (pro_id)
            INNER JOIN ".BD_CTRL_2020.".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
            INNER JOIN ".BD_CTRL_2020.".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
            INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento CF using(ecf_id)
            INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS using(cbs_id)
            INNER JOIN ".BD_CTRL_2020.".cat_UnidadResponsable UR using(gpo_id,ur_id)
            INNER JOIN ".BD_TESORERIA.".cat_InstitucionBancaria as IB using(iba_id)
            INNER JOIN ".BD_CTRL_2020.".enc_ClaveFinanciamiento CV using (ecf_id)
            LEFT JOIN ".BD_TESORERIA.".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
            WHERE
                p.pro_id = ? ;";
        $params = [$det_prop];
        break;
    case 2021:
        $params = [];
        $sql ="SELECT
            p.pro_id AS proDet_propuesta,
            p.pro_ejercicio,
            p.pro_fechaGeneracion,
            dp.gpo_id ,
            dp.ur_id ,
            dp.clc_id ,
            dp.pro_importeClc AS importe,
            clc.clc_beneficiario ,
            clc.clc_concepto ,
            clc.clc_fechaAutorizacion AS proDet_fecha,
            clc.ecf_id,
            BS.cbs_id,
            clc.bur_id,
            CV.ecf_clave as cve_fin,
            concat(clc.gpo_id,clc.ur_id) ur,
            IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
            CX.clc_cereCo as folio,
            UR.ur_nombre as responsable,
            '' as lote_egreso,
            BS.cbs_nombre as nombre_cuenta,
            IB.iba_abreviatura as nombre_banco,
            if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
        FROM
            ".BD_TESORERIA.".pro_Propuesta AS p
        INNER JOIN ".BD_TESORERIA.".pro_PropuestaClc AS dp USING (pro_id)
        INNER JOIN ".BD_CTRL_2021.".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
        INNER JOIN ".BD_CTRL_2021.".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
        INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento CF using(ecf_id)
        INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS using(cbs_id)
        INNER JOIN ".BD_CTRL_2021.".cat_UnidadResponsable UR using(gpo_id,ur_id)
        INNER JOIN ".BD_TESORERIA.".cat_InstitucionBancaria as IB using(iba_id)
        INNER JOIN ".BD_CTRL_2021.".enc_ClaveFinanciamiento CV using (ecf_id)
        LEFT JOIN ".BD_TESORERIA.".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
        WHERE
            p.pro_id = ? ;";
        $params = [$det_prop];
        break;
    case 2022:
        $params = [];
        $sql ="SELECT
            p.pro_id AS proDet_propuesta,
            p.pro_ejercicio,
            p.pro_fechaGeneracion,
            dp.gpo_id ,
            dp.ur_id ,
            dp.clc_id ,
            dp.pro_importeClc AS importe,
            clc.clc_beneficiario ,
            clc.clc_concepto ,
            clc.clc_fechaAutorizacion AS proDet_fecha,
            clc.ecf_id,
            BS.cbs_id,
            clc.bur_id,
            CV.ecf_clave as cve_fin,
            concat(clc.gpo_id,clc.ur_id) ur,
            IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
            CX.clc_cereCo as folio,
            UR.ur_nombre as responsable,
            '' as lote_egreso,
            BS.cbs_nombre as nombre_cuenta,
            IB.iba_abreviatura as nombre_banco,
            if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
        FROM
            ".BD_TESORERIA.".pro_Propuesta AS p
            INNER JOIN ".BD_TESORERIA.".pro_PropuestaClc AS dp USING (pro_id)
            INNER JOIN ".BD_CTRL_2022.".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
            INNER JOIN ".BD_CTRL_2022.".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
            INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento CF using(ecf_id)
            INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS using(cbs_id)
            INNER JOIN ".BD_CTRL_2022.".cat_UnidadResponsable UR using(gpo_id,ur_id)
            INNER JOIN ".BD_TESORERIA.".cat_InstitucionBancaria as IB using(iba_id)
            INNER JOIN ".BD_CTRL_2022.".enc_ClaveFinanciamiento CV using (ecf_id)
            LEFT JOIN ".BD_TESORERIA.".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
            WHERE
                p.pro_id = ? ;";
        $params = [$det_prop];
        break;

    case 2023:
        $params = [];
        $sql ="SELECT
            p.pro_id AS proDet_propuesta,
            p.pro_ejercicio,
            p.pro_fechaGeneracion,
            dp.gpo_id ,
            dp.ur_id ,
            dp.clc_id ,
            dp.pro_importeClc AS importe,
            clc.clc_beneficiario ,
            clc.clc_concepto ,
            clc.clc_fechaAutorizacion AS proDet_fecha,
            clc.ecf_id,
            BS.cbs_id,
            clc.bur_id,
            CV.ecf_clave as cve_fin,
            concat(clc.gpo_id,clc.ur_id) ur,
            IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
            CX.clc_cereCo as folio,
            UR.ur_nombre as responsable,
            '' as lote_egreso,
            BS.cbs_nombre as nombre_cuenta,
            IB.iba_abreviatura as nombre_banco,
            if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
        FROM
            ".BD_TESORERIA.".pro_Propuesta AS p
        INNER JOIN ".BD_TESORERIA.".pro_PropuestaClc AS dp USING (pro_id)
        INNER JOIN ".BD_CTRL_2023.".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
        INNER JOIN ".BD_CTRL_2023.".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
        INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento CF using(ecf_id)
        INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS using(cbs_id)
        INNER JOIN ".BD_CTRL_2023.".cat_UnidadResponsable UR using(gpo_id,ur_id)
        INNER JOIN ".BD_TESORERIA.".cat_InstitucionBancaria as IB using(iba_id)
        INNER JOIN ".BD_CTRL_2023.".enc_ClaveFinanciamiento CV using (ecf_id)
        LEFT JOIN ".BD_TESORERIA.".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
        WHERE
            p.pro_id = ? ;";
        $params = [$det_prop];
        break;
    case 2024:
        $params = [];
        $sql ="SELECT
            p.pro_id AS proDet_propuesta,
            p.pro_ejercicio,
            p.pro_fechaGeneracion,
            dp.gpo_id ,
            dp.ur_id ,
            dp.clc_id ,
            dp.pro_importeClc AS importe,
            clc.clc_beneficiario ,
            clc.clc_concepto ,
            clc.clc_fechaAutorizacion AS proDet_fecha,
            clc.ecf_id,
            BS.cbs_id,
            clc.bur_id,	
            CV.ecf_clave as cve_fin,
            concat(clc.gpo_id,clc.ur_id) ur,
            IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
            CX.clc_cereCo as folio,
            UR.ur_nombre as responsable,
            '' as lote_egreso,
            BS.cbs_nombre as nombre_cuenta,
            IB.iba_abreviatura as nombre_banco,
            if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
        FROM
            ".BD_TESORERIA.".pro_Propuesta AS p
        INNER JOIN ".BD_TESORERIA.".pro_PropuestaClc AS dp USING (pro_id)
        INNER JOIN ".BD_CTRL_2024.".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
        INNER JOIN ".BD_CTRL_2024.".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
        INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento CF using(ecf_id)
        INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS using(cbs_id)
        INNER JOIN ".BD_CTRL_2024.".cat_UnidadResponsable UR using(gpo_id,ur_id)
        INNER JOIN ".BD_TESORERIA.".cat_InstitucionBancaria as IB using(iba_id)
        INNER JOIN ".BD_CTRL_2024.".enc_ClaveFinanciamiento CV using (ecf_id)
        LEFT JOIN ".BD_TESORERIA.".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
        WHERE
            p.pro_id = ? ;";
        $params = [$det_prop];
        break;
    case 2025:
        $params = [];
        $sql ="SELECT
            p.pro_id AS proDet_propuesta,
            p.pro_ejercicio,
            p.pro_fechaGeneracion,
            dp.gpo_id ,
            dp.ur_id ,
            dp.clc_id ,
            dp.pro_importeClc AS importe,
            clc.clc_beneficiario ,
            clc.clc_concepto ,
            clc.clc_fechaAutorizacion AS proDet_fecha,
            clc.ecf_id,
            BS.cbs_id,
            clc.bur_id,	
            CV.ecf_clave as cve_fin,
            concat(clc.gpo_id,clc.ur_id) ur,
            IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
            CX.clc_cereCo as folio,
            UR.ur_nombre as responsable,
            '' as lote_egreso,
            BS.cbs_nombre as nombre_cuenta,
            IB.iba_abreviatura as nombre_banco,
            if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
        FROM
            ".BD_TESORERIA.".pro_Propuesta AS p
        INNER JOIN ".BD_TESORERIA.".pro_PropuestaClc AS dp USING (pro_id)
        INNER JOIN ".BD_CTRL_2025.".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
        INNER JOIN ".BD_CTRL_2025.".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
        INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento CF using(ecf_id)
        INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS using(cbs_id)
        INNER JOIN ".BD_CTRL_2025.".cat_UnidadResponsable UR using(gpo_id,ur_id)
        INNER JOIN ".BD_TESORERIA.".cat_InstitucionBancaria as IB using(iba_id)
        INNER JOIN ".BD_CTRL_2025.".enc_ClaveFinanciamiento CV using (ecf_id)
        LEFT JOIN ".BD_TESORERIA.".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
        WHERE
            p.pro_id = ? ;";
        $params = [$det_prop];
        break;
    case 2026:
    default:
        $params = [];
        $sql ="SELECT
            p.pro_id AS proDet_propuesta,
            p.pro_ejercicio,
            p.pro_fechaGeneracion,
            dp.gpo_id ,
            dp.ur_id ,
            dp.clc_id ,
            dp.pro_importeClc AS importe,
            clc.clc_beneficiario ,
            clc.clc_concepto ,
            clc.clc_fechaAutorizacion AS proDet_fecha,
            clc.ecf_id,
            BS.cbs_id,
            clc.bur_id,	
            CV.ecf_clave as cve_fin,
            concat(clc.gpo_id,clc.ur_id) ur,
            IF(clc.clc_tipoCereCo = 1,'CE','CO') as cere,
            CX.clc_cereCo as folio,
            UR.ur_nombre as responsable,
            '' as lote_egreso,
            BS.cbs_nombre as nombre_cuenta,
            IB.iba_abreviatura as nombre_banco,
            if(LC.lot_id is null ,'',CONCAT( IF ( LC.fpa_id = 1, 'T', IF (LC.fpa_id = 2, 'C', 'L') ), LPAD(LC.msc_id, 2, '0'), LPAD(LC.lot_id, 4, '0'), SUBSTR(LC.lot_ejercicio, 3) )) as lote
        FROM
            ".BD_TESORERIA.".pro_Propuesta AS p
        INNER JOIN ".BD_TESORERIA.".pro_PropuestaClc AS dp USING (pro_id)
        INNER JOIN ".BD_CTRL.".scp_Clc as clc USING (gpo_id, ur_id, clc_id)
        INNER JOIN ".BD_CTRL.".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
        INNER JOIN ".BD_TESORERIA.".vin_SefinClaveFinanciamiento CF using(ecf_id)
        INNER JOIN ".BD_TESORERIA.".ban_CuentaBancariaSefin BS using(cbs_id)
        INNER JOIN ".BD_CTRL.".cat_UnidadResponsable UR using(gpo_id,ur_id)
        INNER JOIN ".BD_TESORERIA.".cat_InstitucionBancaria as IB using(iba_id)
        INNER JOIN ".BD_CTRL.".enc_ClaveFinanciamiento CV using (ecf_id)
        LEFT JOIN ".BD_TESORERIA.".lot_LoteClc as LC using(gpo_id,ur_id,clc_id)
        WHERE
            p.pro_id = ? ;";
        $params = [$det_prop];
        break;
}

$datos = new AccesoDatos();
$datos->conectar();
$datos->setSql($sql);
$datos->consultar($params);
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
do {
    
    $pdf->CheckPageBreak(30);
    $pdf->ln(4);
    $pdf->setAltoLinea(3);
    $pdf->SetX(15);
    $pdf->SetFont('UniviaPro-Bold','B',6); ///tipo de letra para cada titulo
    $pdf->SetWidths(array(5,15,20,10,15,25,25,20,20,28,20,28,20,20));
    $pdf->SetAligns(array('L','C','C','C','C','C','C','C','C','C','C','C','C','C'));

    $pdf->Row(array($contador++,$row['pro_fechaGeneracion'],$row['lote_egreso'],$row['clc_id'],$row['folio'],utf8Decode($row['responsable']),utf8Decode($row['clc_beneficiario']),number_format($row['importe'],2),$row['nombre_banco'],$row['cbs_id'],$row['nombre_cuenta'],$row['bur_id'],$row['cve_fin'],$row['lote']));

    $bolprograma=true;
    $suma += $row['importe'];
    $cadena .= $row['lote_egreso']."|".$row['clc_id']."|".$row['importe']."|".$row['lote'];
    $pdf->SetRelleno("no"); 

    $fecha=$row['pro_fechaGeneracion'];
    $pdf->setAltoLinea(1.5);
} while ($row=$datos->extraer());

$pdf->Row(array("","","","","","","","","","","","","",""));
$pdf->Row(array("","","","","","","TOTAL",number_format($suma,2),"","","","","",""));
$pdf->setAltoLinea(1.5);

$pdf->SetFont('UniviaPro-Bold','B',8);
$pdf->CheckPageBreak(30);
$pdf->ln(8);
$pdf->setAltoLinea(3);
$pdf->SetX(15);
$pdf->MultiCell(180, 4, 'CADENA ORIGINAL: ' . md5($cadena), 0, 'J');
$arrFecha = explode("-", $fecha);
$mes = "";
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
$dia = date('d');
$anio = date('Y');
$pdf->MultiCell(180, 4, utf8Decode('Reyes Mantecón, Oaxaca a ' . $arrFecha[2] . ' de ' . $mes . ' del ' . $arrFecha[0]), 0, 'C');

$pdf->Output();