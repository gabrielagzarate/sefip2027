<?php
session_start();
set_time_limit(0);
ini_set("display_errors", 1);

require_once('../../../lib/reportetabla.1.8.php');
require_once('../../../../lib/helpers.php');
require_once('../../../../lib/AccesoDatos.php');
class reportetabla extends PDF
{

    var $margenAnterior = array(15, '', '', '', '');

    public function  __construct()
    {
        parent::__construct();
    }
    function Footer($lastPage = true)
    {
        $row = $this->getDatos();
        $tmpb = $this->GetBorders();
        $this->SetY(-20);
        $this->SetY(-20);
        $this->Cell(0, 10, utf8Decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
        $this->Cell(0, 10, date("d/m/y") . ' - ' . date("H:i:s") . '        ', 1, 0, 'R');
    }
    function Header()
    { //Encabezado del documento

        $row = $this->getDatos();
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);

        $row = $this->getDatos();
        $this->SetMargins(15, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();
        //$this->Image('../../img/logo2small.png', 160,4, '38' , '10','PNG', '');
        $this->SetFont('Arial', 'B', 8);
        $this->SetXY(10, $iy + 5);
        $ytmp = $this->GetY();

        $this->SetTextColor(26, 64, 62);
        $this->SetFontSize(14);
        $this->Cell(195, 4, 'INCONSISTENCIAS DE LA CARGA', 0, 1, 'L');
        $this->SetFontSize(8);
        $this->SetXY(10, $ytmp + 5);
        $this->Cell(180, 4, utf8Decode('Id de carga :' . $_POST['tem_Carga']), 0, 1, 'L');
        $this->ln(3);
        $this->SetXY(10, $ytmp + 10);
        if (isset($_POST['archivo']))
            $this->Cell(180, 4, utf8Decode('Archivo: ' . $_POST['archivo']), 0, 1, 'L');
        $this->ln(3);
        $this->Line(10, 25, 200, 25);
        $this->ln(4);
        $this->SetMargins($this->margenAnterior[0], $this->margenAnterior[1], $this->margenAnterior[2], $this->margenAnterior[3]);
    }
}

$pdf = new reportetabla('L', 'mm', 'Letter');
$pdf->setOrientation('P');
$pdf->AliasNbPages();
$pdf->SetProtection(array('print', 'annot-forms'), "", PDF_PASSWORD_OWNER_SCP);
$pdf->AddPage();
$conexion = new AccesoDatos();
$conexion->conectar();

//VALIDACION DE CUENTA BANCARIA
$conexion->setSql('
    SELECT DISTINCT clc.gpo_id, clc.ur_id, bur_id
    FROM '.BD_TEMPORAL.'.tem_Clc clc
    LEFT JOIN cat_BancosUr bur USING(bur_id)
    LEFT JOIN(
        SELECT bur_id,GROUP_CONCAT(tcl_id) tcl_id
        FROM vin_BancosUrTipoClc 
        GROUP BY bur_id
    ) ebn USING(bur_id)
    WHERE bur_id != 0 AND( FIND_IN_SET(clc.tcl_id,ebn.tcl_id) = 0 OR 
    ISNULL(eba_id) OR eba_id != 6 OR (ucb_id not IN(3,4)
    AND (bur.gpo_id != clc.gpo_id OR bur.ur_id != clc.ur_id) )) 
    AND tem_carga = ?
    ORDER BY bur_id,clc.gpo_id, clc.ur_id;
');
$conexion->consultar([$_POST['tem_Carga']]);
if ($conexion->getNumeroRegistros() > 0) {
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetRelleno("si");
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetBorders(1);
    $pdf->SetWidths(array(162));
    $pdf->SetAltoLinea(3);
    $pdf->SetAligns(array('C'));
    $pdf->Row(array(utf8Decode('Cuentas Bancarias no registradas y/o no validas')));
    $pdf->SetFillColor(30, 30, 30);
    $celda = 0;
    $datos = array();
    $pdf->SetFillColor(210, 210, 210);
    $pdf->SetTextColor(0, 0, 0);
    $bur_id = '';
    while ($row = $conexion->extraer()) {
        if ($row['bur_id'] != $bur_id) {
            $pdf->Row(array('Cuenta Bancaria:', $row['bur_id']));
            $pdf->SetWidths(array(40, 40));
            $pdf->SetWidths(array(40, 40));
            $pdf->SetAligns(array('C', 'L'));
            $bur_id = $row['bur_id'];
            $w = array();
            $a = array();
            $r = array();
            for ($x = 0; $x < 11; $x++) {
                array_push($w, 14);
                array_push($a, 'C');
                array_push($r, 'UR');
            }
            $pdf->SetWidths($w);
            $pdf->SetAligns($a);
            $pdf->Row($r);
        }
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetBorders(1);
        if (count($datos) == 11) {
            if ($celda == 0) {
                $celda = 1;
                $pdf->SetRelleno("si");
            } else {
                $celda = 0;
                $pdf->SetRelleno("no");
            }
            $pdf->Row($datos);
            $datos = array();
        }
        array_push($datos, $row['gpo_id']);
        array_push($datos, $row['ur_id']);
        array_push($datos, $row['bur_id']);
    }
    if (count($datos) != 11) {
        do {
            array_push($datos, '-');
            array_push($datos, '-');
            array_push($datos, '-');
        } while (count($datos) != 9);
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->Row($datos);
    } else {
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->Row($datos);
        $datos = array();
    }
    $pdf->ln(5);
}
//VALIDACION DE CLAVE DE FINANCIAMIENTO QUE ESTE PRESUPUESTADO
$conexion->setSql('
    SELECT DISTINCT gpo_id, ur_id, concat(t.fte_id,t.sft_id,t.ori_id,t.sor_id,t.cfi_id,t.cfi_anio) clave
    FROM '.BD_TEMPORAL.'.tem_Clc t
    LEFT JOIN presupuesto USING (gpo_id , ur_id , ecf_id)
    WHERE ISNULL(pre_claveCompleta)  AND tem_carga = ?;
');
$conexion->consultar([$_POST['tem_Carga']]);
if ($conexion->getNumeroRegistros() > 0) {
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetRelleno("si");
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetBorders(1);
    $pdf->SetWidths(array(162));
    $pdf->SetAltoLinea(3);
    $pdf->SetAligns(array('C'));
    $pdf->Row(array(utf8Decode('CLAVE DE FINANCIAMINETO NO PRESUPUESTADA')));
    $pdf->SetFillColor(30, 30, 30);
    $pdf->SetWidths(array(7, 7, 40, 7, 7, 40, 7, 7, 40));
    $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
    $pdf->Row(array(utf8Decode('Gpo'), utf8Decode('Ur'), utf8Decode('Cuenta'), utf8Decode('Gpo'), utf8Decode('Ur'), utf8Decode('Cuenta'), utf8Decode('Gpo'), utf8Decode('Ur'), utf8Decode('Cuenta')));
    $celda = 0;
    $datos = array();
    $pdf->SetFillColor(210, 210, 210);
    $pdf->SetTextColor(0, 0, 0);
    while ($row = $conexion->extraer()) {

        $pdf->SetFont('Arial', '', 6);
        $pdf->SetBorders(1);
        if (count($datos) == 9) {
            if ($celda == 0) {
                $celda = 1;
                $pdf->SetRelleno("si");
            } else {
                $celda = 0;
                $pdf->SetRelleno("no");
            }
            $pdf->Row($datos);
            $datos = array();
        }
        array_push($datos, $row['gpo_id']);
        array_push($datos, $row['ur_id']);
        array_push($datos, $row['clave']);
    }
    if (count($datos) != 9) {
        do {
            array_push($datos, '-');
            array_push($datos, '-');
            array_push($datos, '-');
        } while (count($datos) != 9);
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->Row($datos);
    } else {
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->Row($datos);
        $datos = array();
    }
    $pdf->ln(5);
}
//VALIDACION DE CUENTA CONTABLE
$conexion->setSql('
    SELECT DISTINCT CONCAT(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id) cuenta,cta_gpo_id,cta_ur_id
    FROM '.BD_TEMPORAL.'.tem_Contable t
    LEFT JOIN cg_PlanCuentas USING(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id)
    WHERE isnull(cta_cuenta) AND t.tem_carga = ?;
');
$conexion->consultar([$_POST['tem_Carga']]);
if ($conexion->getNumeroRegistros() > 0) {
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetRelleno("si");
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetBorders(1);
    $pdf->SetWidths(array(162));
    $pdf->SetAltoLinea(3);
    $pdf->SetAligns(array('C'));
    $pdf->Row(array(utf8Decode('CUENTA CONTABLE NO REGISTRADA EN EL SISTEMA')));
    $pdf->SetFillColor(30, 30, 30);
    $pdf->SetWidths(array(7, 7, 40, 7, 7, 40, 7, 7, 40));
    $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
    $pdf->Row(array(utf8Decode('Gpo'), utf8Decode('Ur'), utf8Decode('cuenta'), utf8Decode('Gpo'), utf8Decode('Ur'), utf8Decode('cuenta'), utf8Decode('Gpo'), utf8Decode('Ur'), utf8Decode('cuenta')));
    $celda = 0;
    $datos = array();
    $pdf->SetFillColor(210, 210, 210);
    $pdf->SetTextColor(0, 0, 0);
    while ($row = $conexion->extraer()) {
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetBorders(1);
        if (count($datos) == 9) {
            if ($celda == 0) {
                $celda = 1;
                $pdf->SetRelleno("si");
            } else {
                $celda = 0;
                $pdf->SetRelleno("no");
            }
            $pdf->Row($datos);
            $datos = array();
        }
        array_push($datos, $row['cta_gpo_id']);
        array_push($datos, $row['cta_ur_id']);
        array_push($datos, $row['cuenta']);
    }
    if (count($datos) != 9) {
        do {
            array_push($datos, '-');
            array_push($datos, '-');
            array_push($datos, '-');
        } while (count($datos) != 9);
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->Row($datos);
    } else {
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->Row($datos);
        $datos = array();
    }
    $pdf->ln(5);
}
//VALIDACION DE LA MODALIDAD DE NOMINDA CORRESPONDIENTE AL TIPO DE CLC

$conexion->setSql('
    SELECT DISTINCT tcl_id,stc_id, mno_id
    FROM '.BD_TEMPORAL.'.tem_Clc t
    LEFT JOIN enc_ModalidadSubtipoClc m
    USING( tcl_id,stc_id,mno_id )
    WHERE ISNULL(m.mno_id) AND tem_carga = ?
    AND tcl_id NOT IN ("B","C")
');
$conexion->consultar([$_POST['tem_Carga']]);
if ($conexion->getNumeroRegistros() > 0) {
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetRelleno("si");
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetBorders(1);
    $pdf->SetWidths(array(148));
    $pdf->SetAltoLinea(3);
    $pdf->SetAligns(array('C'));
    $pdf->Row(array(utf8Decode('MODALIDAD QUE NO CORRESPONDE AL TIPO DE CLC')));
    $pdf->SetFillColor(30, 30, 30);
    $pdf->SetWidths(array(7, 15, 15, 7, 15, 15, 7, 15, 15, 7, 15, 15));
    $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
    $pdf->Row(array(utf8Decode('tipo'), utf8Decode('concepto Pago'), utf8Decode('modalidad'), utf8Decode('tipo'), utf8Decode('concepto Pago'), utf8Decode('modalidad'), utf8Decode('tipo'), utf8Decode('concepto Pago'), utf8Decode('modalidad'), utf8Decode('tipo'), utf8Decode('concepto Pago'), utf8Decode('modalidad')));
    $celda = 0;
    $datos = array();
    $pdf->SetFillColor(210, 210, 210);
    $pdf->SetTextColor(0, 0, 0);
    while ($row = $conexion->extraer()) {

        $pdf->SetFont('Arial', '', 6);
        $pdf->SetBorders(1);
        if (count($datos) == 12) {
            if ($celda == 0) {
                $celda = 1;
                $pdf->SetRelleno("si");
            } else {
                $celda = 0;
                $pdf->SetRelleno("no");
            }
            $pdf->Row($datos);
            $datos = array();
        }
        array_push($datos, $row['tcl_id']);
        array_push($datos, $row['stc_id']);
        array_push($datos, $row['mno_id']);
    }
    if (count($datos) != 12) {
        do {
            array_push($datos, '-');
            array_push($datos, '-');
            array_push($datos, '-');
        } while (count($datos) != 12);
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->Row($datos);
    } else {
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->Row($datos);
        $datos = array();
    }
    $pdf->ln(5);
}
//VALIDACION EXISTENCIA DE RFC
$conexion->setSql('
    SELECT DISTINCT rfc_id from (
        SELECT DISTINCT rfc_id 
        FROM '.BD_TEMPORAL.'.tem_Presupuestal
        LEFT JOIN cat_Proveedor p USING(rfc_id)
        WHERE ISNULL( p.rfc_id )
        AND tem_carga = :tem_carga
        UNION ALL
        SELECT DISTINCT rfc_id
        FROM '.BD_TEMPORAL.'.tem_Contable
        LEFT JOIN cat_Proveedor p USING(rfc_id)
        WHERE ISNULL( p.rfc_id )
        AND tem_carga = :tem_carga
    ) rfcs;
');

$conexion->consultar([':tem_carga' => $_POST['tem_Carga']]);
if ($conexion->getNumeroRegistros() > 0) {
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetRelleno("si");
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetBorders(1);
    $pdf->SetWidths(array(162));
    $pdf->SetAltoLinea(3);
    $pdf->SetAligns(array('C'));
    $pdf->Row(array(utf8Decode('RFC\'S NO REGISTRADOS EN EL SISTEMA')));
    $pdf->SetFillColor(30, 30, 30);
    $pdf->SetWidths(array(27, 27, 27, 27, 27, 27, 27, 27, 27));
    $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
    $pdf->Row(array(utf8Decode('RFC'), utf8Decode('RFC'), utf8Decode('RFC'), utf8Decode('RFC'), utf8Decode('RFC'), utf8Decode('RFC')));
    $celda = 0;
    $datos = array();
    $pdf->SetFillColor(210, 210, 210);
    $pdf->SetTextColor(0, 0, 0);
    while ($row = $conexion->extraer()) {
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetBorders(1);
        if (count($datos) == 6) {
            if ($celda == 0) {
                $celda = 1;
                $pdf->SetRelleno("si");
            } else {
                $celda = 0;
                $pdf->SetRelleno("no");
            }
            $pdf->Row($datos);
            $datos = array();
        }
        array_push($datos, $row['rfc_id']);
    }
    if (count($datos) != 6) {
        do {
            array_push($datos, '-');
        } while (count($datos) != 6);
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->Row($datos);
    } else {
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->Row($datos);
        $datos = array();
    }
    $pdf->ln(5);
}
//VALIDACION EXISTENCIA DE CLAVE PRESUPUESTAL
$conexion->setSql('
    SELECT DISTINCT concat(gpo_id,ur_id,ue_id,"-",tp.prg_id,tp.spr_id,tp.py_id,tp.oa_id,"-",pg_id,pe_id,tp.fte_id,tp.sft_id,tp.ori_id,tp.sor_id,tp.cfi_id,tp.cfi_anio,tcp_id) clave
    FROM '.BD_TEMPORAL.'.tem_Presupuestal tp
    LEFT JOIN presupuesto USING (gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id)
    WHERE ISNULL(pre_claveCompleta) AND tem_carga = ?
');
$conexion->consultar([$_POST['tem_Carga']]);
if ($conexion->getNumeroRegistros() > 0) {
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetRelleno("si");
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetBorders(1);
    $pdf->SetWidths(array(165));
    $pdf->SetAltoLinea(3);
    $pdf->SetAligns(array('C'));
    $pdf->Row(array(utf8Decode('CLAVES PRESUPUESTALES NO REGISTRADAS EN EL SISTEMA')));
    $pdf->SetFillColor(30, 30, 30);
    $pdf->SetWidths(array(55, 55, 55));
    $pdf->SetAligns(array('C', 'C', 'C'));
    $pdf->Row(array(utf8Decode('clave'), utf8Decode('clave'), utf8Decode('clave')));
    $celda = 0;
    $datos = array();
    $pdf->SetFillColor(210, 210, 210);
    $pdf->SetTextColor(0, 0, 0);
    while ($row = $conexion->extraer()) {

        $pdf->SetFont('Arial', '', 6);
        $pdf->SetBorders(1);
        if (count($datos) == 3) {
            if ($celda == 0) {
                $celda = 1;
                $pdf->SetRelleno("si");
            } else {
                $celda = 0;
                $pdf->SetRelleno("no");
            }
            $pdf->Row($datos);
            $datos = array();
        }
        array_push($datos, $row['clave']);
    }
    if (count($datos) != 3) {
        do {
            array_push($datos, '-');
        } while (count($datos) != 3);
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->Row($datos);
    } else {
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->Row($datos);
        $datos = array();
    }
    $pdf->ln(5);
}
//VALIDACION CLAVE PRESUPUESTAL CALENDARIZACION CREADA
$conexion->setSql('
    SELECT DISTINCT pre_claveCompleta, pre_mov,mod_id,omo_nombre
    FROM '.BD_TEMPORAL.'.tem_Presupuestal
    JOIN presupuesto USING (gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id)
    JOIN presupuestoVigente USING (gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id)
    JOIN (
        SELECT DISTINCT gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id,mod_id,mod_origen, 
        IF(omo_id = "C" AND tmd_id = "D" AND tfm_id = "B", "DIRECCION DE RECURSOS MATERIALES", omo_nombre ) omo_nombre
        FROM scp_MaestroModificacion 
        JOIN scp_DetalleModificacion using(omo_id,mod_gpo_id,mod_ur_id,mod_id,ecf_id)
        JOIN enc_OrigenOperacion USING(omo_id,enc_oop)
        JOIN cat_OrigenModificacion USING(omo_id)
        WHERE emo_id not IN(12, 13) AND mod_origen = "A"
        GROUP BY gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id,mod_id,omo_id,tmd_id,tfm_id
    )fol USING (gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id)
    WHERE pre_mov IN ("C","R") AND tem_carga = "' . $_POST['tem_Carga'] . '"
    GROUP BY gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id,mod_id,omo_nombre
    ORDER BY mod_id,pre_claveCompleta;
');
$conexion->consultar();
if ($conexion->getNumeroRegistros() > 0) {
    $pdf->SetMargins(10, '', '', '');
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetRelleno("si");
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetBorders(1);
    $pdf->SetWidths(array(200));
    $pdf->SetAltoLinea(3);
    $pdf->SetAligns(array('C'));
    $pdf->Row(array(utf8Decode('FOLIOS GENERADOS MEDIANTE CARGA MASIVA DE CLC')));
    $pdf->SetFillColor(30, 30, 30);
    $pdf->SetWidths(array(50, 8, 13, 25, 50, 8, 13, 25));
    $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
    $pdf->Row(array('MOV'), utf8Decode('FOLIO'), utf8Decode('ORIGEN'), utf8Decode('CLAVE'), utf8Decode('MOV'), utf8Decode('FOLIO'), utf8Decode('ORIGEN'));
    $celda = 0;
    $datos = array();
    $mod_id = '';
    $mod_idFin = '';
    while ($row = $conexion->extraer()) {
        if ($mod_id != $row['mod_id']) {
            $pdf->SetWidths(array(20, 8, 13, 13, 13, 140));
            $pdf->SetAligns(array('R', 'L', 'R', 'L', 'R', 'L'));
            $pdf->SetFillColor(0, 0, 0);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Row(array('MOVIMIENTO:', $row['pre_mov'], 'FOLIO', $row['mod_id'], 'ORIGEN', $row['omo_nombre']));
            $mod_id = $row['mod_id'];
        }
        $pdf->SetFillColor(210, 210, 210);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetWidths(array(49, 49, 49, 49));
        $pdf->SetAligns(array('C', 'C', 'C', 'C'));
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetBorders(1);
        if (count($datos) == 4) {
            if ($celda == 0) {
                $celda = 1;
                $pdf->SetRelleno("si");
            } else {
                $celda = 0;
                $pdf->SetRelleno("no");
            }
            $pdf->Row($datos);
            $datos = array();
        }
        array_push($datos, $row['pre_claveCompleta']);
        $mod_idFin = $row;
    }
    if ($mod_id != $mod_idFin['mod_id']) {
        $pdf->SetWidths(array(8, 8, 13, 13, 13, 145));
        $pdf->SetAligns(array('R', 'L', 'R', 'L', 'R', 'L'));
        $pdf->Row(array('MOVIMIENTO:', $mod_idFin['pre_mov'], 'FOLIO', $mod_idFin['mod_id'], 'ORIGEN', $mod_idFin['omo_nombre']));
    }
    $pdf->SetWidths(array(50, 50, 50, 50));
    $pdf->SetAligns(array('C', 'C', 'C', 'C'));
    if (count($datos) != 4) {
        do {
            array_push($datos, '-');
        } while (count($datos) != 4);
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->Row($datos);
    } else {
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->Row($datos);
        $datos = array();
    }
    $pdf->ln(5);
}
$pdf->SetMargins('', '', '', '');
//VALIDACION CLAVE PRESUPUESTAL SIN MOVIMIENTOS PRESUPUESTALES
$conexion->setSql('
    SELECT DISTINCT pre_claveCompleta, pre_mov,mod_id,omo_nombre
    FROM '.BD_TEMPORAL.'.tem_Presupuestal
    JOIN presupuesto USING (gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id)
    JOIN presupuestoVigente USING (gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id)
    JOIN (
        SELECT DISTINCT gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id,mod_id,mod_origen, 
            IF(omo_id = "C" AND tmd_id = "D" AND tfm_id = "B", "DIRECCION DE RECURSOS MATERIALES", omo_nombre ) omo_nombre
            FROM scp_MaestroModificacion 
            JOIN  scp_DetalleModificacion using(omo_id,mod_gpo_id,mod_ur_id,mod_id,ecf_id)
            JOIN enc_OrigenOperacion USING(omo_id,enc_oop)
            JOIN cat_OrigenModificacion USING(omo_id)
            WHERE emo_id not IN(12, 13) AND mod_origen != "A"
            GROUP BY gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id,mod_id,omo_id,tmd_id,tfm_id,mod_origen
        )fol USING (gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id)
        WHERE pre_mov IN ("C","R") AND tem_carga = ?
        GROUP BY gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id,mod_id,omo_nombre ;');

$carga = $_POST['tem_Carga'];
$conexion->consultar([$carga]);

if ($conexion->getNumeroRegistros() > 0) {
    $pdf->SetMargins(10, '', '', '');
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetRelleno("si");
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetBorders(1);
    $pdf->SetWidths(array(192));
    $pdf->SetAltoLinea(3);
    $pdf->SetAligns(array('C'));
    $pdf->Row(array(utf8Decode('CLAVES PRESUPUESTALES CON FOLIOS PENDIENTES')));
    $pdf->SetFillColor(30, 30, 30);
    $pdf->SetWidths(array(50, 8, 13, 25, 50, 8, 13, 25));
    $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
    $pdf->Row(array(utf8Decode('CLAVE'), utf8Decode('MOV'), utf8Decode('FOLIO'), utf8Decode('ORIGEN'), utf8Decode('CLAVE'), utf8Decode('MOV'), utf8Decode('FOLIO'), utf8Decode('ORIGEN')));
    $celda = 0;
    $datos = array();
    $pdf->SetFillColor(210, 210, 210);
    $pdf->SetTextColor(0, 0, 0);
    while ($row = $conexion->extraer()) {
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetBorders(1);
        if (count($datos) == 8) {
            if ($celda == 0) {
                $celda = 1;
                $pdf->SetRelleno("si");
            } else {
                $celda = 0;
                $pdf->SetRelleno("no");
            }
            $pdf->Row($datos);
            $datos = array();
        }
        array_push($datos, $row['pre_claveCompleta']);
        array_push($datos, $row['pre_mov']);
        array_push($datos, $row['mod_id']);
        array_push($datos, $row['omo_nombre']);
    }
    if (count($datos) != 8) {
        do {
            array_push($datos, '-');
        } while (count($datos) != 8);
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->Row($datos);
    } else {
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->Row($datos);
        $datos = array();
    }
    $pdf->ln(5);
}
$pdf->SetMargins('', '', '', '');

//VALIDACION PRESUPUESTO DISPONIBLE CLAVE PRESUPUESTAL
$mes = array('ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC');

$conexion->setSql('
    SELECT DISTINCT 
        pre_claveCompleta,
        vig_' . strtolower($mes[date('n') - 1]) . ' vigente,
        com_' . strtolower($mes[date('n') - 1]) . ' comprometido,
        vig_' . strtolower($mes[date('n') - 1]) . ' - com_' . $mes[date('n') - 1] . ' disponible,
        sum(ncp_percepcionNeto) importe,
        sum(ncp_percepcionNeto) - ( vig_' . strtolower($mes[date('n') - 1]) . ' - com_' . strtolower($mes[date('n') - 1]) . ' ) dif,
        omo_id,dmo_importe
    FROM presupuesto
    INNER JOIN presupuestoComprometido USING (gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
    INNER JOIN  presupuestoVigente USING ( gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id )
    JOIN '.BD_TEMPORAL.'.tem_Presupuestal USING (gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id)
    LEFT JOIN (
        SELECT DISTINCT gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id,dmo_importe,omo_id
        FROM scp_MaestroModificacion 
        JOIN  scp_DetalleModificacion using(omo_id,mod_gpo_id,mod_ur_id,mod_id,ecf_id)
        JOIN enc_OrigenOperacion USING(omo_id,enc_oop)
        JOIN cat_OrigenModificacion USING(omo_id)
        WHERE emo_id not IN(12, 13)
        GROUP BY gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id,mod_id,omo_id,dmo_importe
    )fol  USING (gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id)
    WHERE tem_carga = "' . $_POST['tem_Carga'] . '"
    GROUP BY gpo_id , ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id,tcp_id,omo_id,dmo_importe
    HAVING disponible < importe;
');

$conexion->consultar();
$pdf->margenAnterior = array(5, '', '', '');
if ($conexion->getNumeroRegistros() > 0) {
    $pdf->SetMargins('', '', '', '');
    $pdf->SetFillColor(0, 0, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetRelleno("si");
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetBorders(1);
    $pdf->SetWidths(array(175));
    $pdf->SetAltoLinea(3);
    $pdf->SetAligns(array('C'));
    $pdf->Row(array(utf8Decode('CLAVE PRESUPUESTAL SIN COBERTURA EN EL MES')));
    $pdf->SetFillColor(30, 30, 30);
    $pdf->SetWidths(array(50, 24, 24, 24, 24, 24, 8, 24));
    $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
    $pdf->Row(array(utf8Decode('CLAVE'), utf8Decode('Vigente'), utf8Decode('Comprometido'), utf8Decode('Disponible'), utf8Decode('Importe'), utf8Decode('Diferencia'), utf8Decode('mov'), utf8Decode('Importe Folio')));
    $pdf->SetAligns(array('C', 'R', 'R', 'R', 'R', 'R', 'C', 'R'));
    $pdf->SetFillColor(210, 210, 210);
    $pdf->SetTextColor(0, 0, 0);
    $celda = 0;
    $datos = array();
    while ($row = $conexion->extraer()) {

        $pdf->SetMargins('', '', '', '');
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetBorders(1);
        if (count($datos) == 8) {
            if ($celda == 0) {
                $celda = 1;
                $pdf->SetRelleno("si");
            } else {
                $celda = 0;
                $pdf->SetRelleno("no");
            }
            if (empty($datos[6])) {
                $datos[6] = '';
                $datos[7] = 0;
                $pdf->SetFillColor(240, 64, 40);
                $pdf->SetRelleno("si");
                $celda = 1;
            } else $pdf->SetFillColor(210, 210, 210);
            $pdf->Row($datos);
            $datos = array();
        }
        array_push($datos, $row['pre_claveCompleta']);
        array_push($datos, '$ ' . number_format($row['vigente'], 2));
        array_push($datos, '$ ' . number_format($row['comprometido'], 2));
        array_push($datos, '$ ' . number_format($row['disponible'], 2));
        array_push($datos, '$ ' . number_format($row['importe'], 2));
        array_push($datos, '$ ' . number_format($row['dif'], 2));
        array_push($datos, $row['omo_id']);
        array_push($datos, '$ ' . number_format($row['dmo_importe'], 2));
    }
    if (count($datos) != 8) {
        do {
            array_push($datos, '-');
        } while (count($datos) != 8);
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->Row($datos);
    } else {
        if ($celda == 0) {
            $celda = 1;
            $pdf->SetRelleno("si");
        } else {
            $celda = 0;
            $pdf->SetRelleno("no");
        }
        $pdf->Row($datos);
        $datos = array();
    }
    $pdf->ln(5);
}
$conexion->desconectar();
ob_end_clean();
$pdf->Output();
