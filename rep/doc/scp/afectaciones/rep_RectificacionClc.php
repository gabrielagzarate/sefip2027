<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';
ini_set('memory_limit', '200M');
set_time_limit(500);

require_once '../../../lib/reportetabla.1.8.php';
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../ent/Entidad.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../lib/Math.php';

//Extendiendo la clase pdf para escribir el encabezado y pie de página
class reportetabla extends PDF
{
    public $datosAnt;
    public $ultBorde = 0;
    public $seccion = '{nb}';
    public $importe = '{importe}';
    public $ur_clave = '';
    public $clc_id = '';
    public $pagina = 0;
    public $temEcl = 0;

    public function setDatosAnt($tmp)
    {
        $this->datosAnt = $tmp;
    }
    public function getDatosAnt()
    {
        return $this->datosAnt;
    }

    public function __construct($orientation = 'L', $unit = 'mm', $format = 'A4', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);
    }

    public function Footer($lastPage = true)
    {

        $datos = $this->getDatos();
        $this->SetY(-40);

        /* Leyenda Rectificaciones  */
        $urs = ['116', '901', '902'];
        if (in_array($datos['gpo_id'] . $datos['ur_id'], $urs)) {

            $this->SetFont('Arial', 'B', 5.5);
            $this->MultiCell(260, 2.5, (utf8Decode('LOS SERVIDORES PÚBLICOS QUE SUSCRIBIMOS LA PRESENTE RECTIFICACIÓN DE CUENTA POR LIQUIDAR CERTIFICADA, DECLARAMOS QUE HEMOS CUMPLIDO CON LO SEÑALADO EN EL ARTÍCULO 62 DEL REGLAMENTO DE LA LEY ESTATAL DE PRESUPUESTO Y RESPONSABILIDAD HACENDARIA, Y QUE LOS DATOS CONTENIDOS EN LA MISMA CORRESPONDEN A LA DOCUMENTACIÓN FISCAL SOPORTE DEL GASTO, MISMA QUE EN ORIGINAL SE QUEDA EN RESGUARDO Y CUSTODIA DEL EJECUTOR DE GASTO  BENEFICIARIO DE LA TRANSFERENCIA. LO ANTERIOR, CON FUNDAMENTO EN LOS ARTÍCULOS 70 DE LA LEY ESTATAL DE PRESUPUESTO Y RESPONSABILIDAD HACENDARIA; 23A DE LA LEY DE COORDINACIÓN FISCAL PARA EL ESTADO DE OAXACA; 163 DEL REGLAMENTO DE LA LEY ESTATAL DE PRESUPUESTO Y RESPONSABILIDAD HACENDARIA, Y DEMAS DISPOSICIONES APLICABLES. ')), 0, 'J');
            $this->ln(1);
        } else {
            $this->SetFont('Arial', 'B', 5.5);
            $this->MultiCell(260, 2.5, (utf8Decode('LOS SERVIDORES PÚBLICOS QUE SUSCRIBIMOS LA PRESENTE RECTIFICACIÓN DE CUENTA POR LIQUIDAR CERTIFICADA, DECLARAMOS QUE HEMOS CUMPLIDO CON LO SEÑALADO EN EL ARTÍCULO 62 DEL REGLAMENTO DE LA LEY ESTATAL  DE  PRESUPUESTO Y RESPONSABILIDAD HACENDARIA, Y QUE LOS DATOS CONTENIDOS EN LA MISMA CORRESPONDEN A LA DOCUMENTACIÓN FISCAL SOPORTE DEL GASTO, MISMA QUE EN ORIGINAL SE QUEDA EN NUESTRO RESGUARDO Y CUSTODIA. LO ANTERIOR, CON FUNDAMENTO EN LOS ARTÍCULOS 4 Y 48 DE LA LEY ESTATAL DE PRESUPUESTO Y RESPONSABILIDAD HACENDARIA, Y DEL 186 AL 193 DEL REGLAMENTO DE LA LEY ESTATAL DE PRESUPUESTO Y RESPONSABILIDAD HACENDARIA.')), 0, 'J');
            $this->ln(1);
        }

        $this->SetBorders(0);

        if (
            isset($_POST['rct_gpo']) and ($_POST['rct_gpo'] != '') and isset($_POST['rct_ur'])
            and ($_POST['rct_ur'] != '')
        ) {

            $ur = new UnidadResponsable();
            $ur->setGpo_id($_POST['rct_gpo']);
            $ur->setUr_id($_POST['rct_ur']);
            $ur->select();

            $this->SetFont('Arial', '', 6.5);
            $this->Cell(130, 2, utf8Decode('JEFE ADMINISTRATIVO'), 0, 0, 'C');
            $this->Cell(130, 2, utf8Decode('TITULAR DEL EJECUTOR DEL GASTO'), 0, 0, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->ln(5);
            $this->SetFont('Arial', '', 6.5);
            $this->Cell(130, 2, utf8Decode($ur->getNombreAdministrativo()), 0, 0, 'C');
            $this->Cell(130, 2, utf8Decode($ur->getNombreTitular()), 0, 1, 'C');
            $this->ln(.5);
            $this->SetFont('Arial', '', 6.5);
            $this->Cell(130, 2, utf8Decode($ur->getCargoAdministrativo()), 0, 0, 'C');
            $this->Cell(130, 2, utf8Decode($ur->getCargoTitular()), 0, 0, 'C');
        } else {

            if ($datos['gpo_id'] == '9' and $datos['ur_id'] == '02') {

                $this->SetFont('Arial', '', 6.5);
                $this->Cell(130, 2, utf8Decode($datos['ur_cargoTitular']), 0, 0, 'C');
                $this->Cell(130, 2, utf8Decode($datos['ur_cargoAdministrativo']), 0, 0, 'C');
                $this->ln(5);
                $this->SetFont('Arial', '', 6.5);
                $this->Cell(130, 2, utf8Decode($datos['ur_nombreTitular']), 0, 0, 'C');
                $this->Cell(130, 2, utf8Decode($datos['ur_nombreAdministrativo']), 0, 0, 'C');
            } else {

                $this->SetFont('Arial', '', 6.5);
                $this->Cell(130, 2, utf8Decode('JEFE ADMINISTRATIVO'), 0, 0, 'C');
                $this->Cell(130, 2, utf8Decode('TITULAR DEL EJECUTOR DEL GASTO'), 0, 0, 'C');

                $this->Cell(130, 2, '', 0, 1, 'C');
                $this->ln(5);
                $this->SetFont('Arial', '', 6.5);
                $this->Cell(130, 2, utf8Decode($datos['ur_nombreAdministrativo']), 0, 0, 'C');
                $this->Cell(130, 2, utf8Decode($datos['ur_nombreTitular']), 0, 1, 'C');
                $this->ln(.5);
                $this->SetFont('Arial', '', 6.5);
                $this->Cell(130, 2, utf8Decode($datos['ur_cargoAdministrativo']), 0, 0, 'C');
                $this->Cell(130, 2, utf8Decode($datos['ur_cargoTitular']), 0, 0, 'C');
            }
        }
    } //fin de la funcion footer

    public function Header()
    {

        //Encabezado del documento
        $datos = $this->getDatos();

        // 05-02-2019 @Jordy se agrega impresion de la etiqueta PRUEBAS para los ambientes de prueba
        if ((isset($_SESSION[_S]['ejercicio']) && $_SESSION[_S]['ejercicio'] == APP_EJERCICIO . '_localhost')
            || (isset($_SESSION[_S]['ejercicio']) && $_SESSION[_S]['ejercicio'] == APP_EJERCICIO . 'Calidad')
        ) {
            $iy = $this->GetY();
            $ix = $this->GetX();
            $this->SetFont('Arial', 'B', 50);
            $this->SetTextColor(234, 41, 64);
            $this->RotatedText($ix + 35, $iy + 180, 'PRUEBAS', 40);
            $this->RotatedText($ix + 130, $iy + 190, 'PRUEBAS', 40);
            $this->RotatedText($ix + 25, $iy + 105, 'PRUEBAS', 40);
            $this->RotatedText($ix + 130, $iy + 110, 'PRUEBAS', 40);
        }

        if ((isset($_POST['tipoRep']) && $_POST['tipoRep'] == 'CONTABILIDAD')) {

            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('Arial', 'B', 50);
            $this->SetTextColor(200, 200, 200);
            $this->RotatedText((float)$ix + 45, (float)$iy + 165, 'CONTABILIDAD', 45);
            $this->RotatedText((float)$ix + 150, (float)$iy + 160, 'CONTABILIDAD', 45);
            $this->RotatedText((float)$ix + 45, (float)$iy + 85, 'CONTABILIDAD', 45);
            $this->RotatedText((float)$ix + 150, (float)$iy + 80, 'CONTABILIDAD', 45);
        } else {
            if ($datos['ecl_id'] == 1) {
                $ix = $this->GetX();
                $iy = $this->GetY();
                $this->SetFont('Arial', 'B', 50);
                $this->SetTextColor(200, 200, 200);
                $this->RotatedText((float)$ix + 30, (float)$iy + 160, 'SIN VALIDEZ', 45);
                $this->RotatedText((float)$ix + 150, (float)$iy + 160, 'SIN VALIDEZ', 45);
                $this->RotatedText((float)$ix, (float)$iy + 80, 'SIN VALIDEZ', 45);
                $this->RotatedText((float)$ix + 150, (float)$iy + 80, 'SIN VALIDEZ', 45);
            } elseif (($datos['ecl_id'] > 2)) {

                if (empty($_POST['reimprimir'])) {
                    $ix = $this->GetX();
                    $iy = $this->GetY();
                    $this->SetFont('Arial', 'B', 50);
                    $this->SetTextColor(200, 200, 200);
                    $this->RotatedText((float)$ix + 50, (float)$iy + 160, 'COPIA', 45);
                    $this->RotatedText((float)$ix + 150, (float)$iy + 160, 'COPIA', 45);
                    $this->RotatedText((float)$ix + 50, (float)$iy + 80, 'COPIA', 45);
                    $this->RotatedText((float)$ix + 150, (float)$iy + 80, 'COPIA', 45);
                }
                /*Este se aumento por la consulta de las UR'S */
            } elseif (isset($_POST['reimprimir'])) {
                $ix = $this->GetX();
                $iy = $this->GetY();
                $this->SetFont('Arial', 'B', 50);
                $this->SetTextColor(200, 200, 200);
                $this->RotatedText((float)$ix + 50, (float)$iy + 160, 'COPIA', 45);
                $this->RotatedText((float)$ix + 150, (float)$iy + 160, 'COPIA', 45);
                $this->RotatedText((float)$ix + 50, (float)$iy + 80, 'COPIA', 45);
                $this->RotatedText((float)$ix + 150, (float)$iy + 80, 'COPIA', 45);
            }
        }

        $this->SetMargins(15, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();

        if (empty($_POST['reimprimir'])) {
            $this->SetFont('Arial', 'B', 6);
            $this->SetXY($ix, $iy + 4);
            $this->Cell(20, 5, utf8Decode(date('d/m/y') . ' - ' . date('H:i:s') . '        '), 0, 1, 'L');
            $this->SetXY($ix, $iy + 6);
        } else {
            $this->SetFont('Arial', 'B', 6);
            $this->SetXY($ix, $iy + 4);
            $this->Cell(20, 5, utf8Decode($_POST['reimprimir'] . '        '), 0, 1, 'L');
            $this->SetXY($ix, $iy + 6);
        }

        $this->Cell(20, 5, (utf8Decode('Página ')) . $this->PageNo() . '/{nb}', 0, 0, 'L');
        $textotipoclc = $datos['tcl_nombre'];
        $conceptoclc = $datos['rct_justificacion'];

        $this->SetFont('Arial', 'B', 8);
        $this->SetXY($ix, $iy + 4);
        $ytmp = $this->GetY();

        $this->Cell(264, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 0, 'C');

        $this->SetXY($ix - 65, $ytmp - 3);
        $this->SetFont('Arial', '', 6.5);
        $this->Cell(20, 5, utf8Decode('Rectificación...'), 0, 0, 'R');
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(40, 5, $datos['rct_id'], 0, 1, 'L');

        if ($datos['rct_reintegro'] == 1) {

            $this->SetXY($ix - 65, $ytmp);
            $this->SetFont('Arial', '', 6);
            $this->Cell(20, 5, 'No. de Comprobante...:', 0, 0, 'R');
            $this->SetFont('Arial', 'B', 6);
            $this->Cell(40, 5, $datos['rct_numeroComprobante'], 0, 1, 'L');

            $this->SetXY($ix - 65, $ytmp + 2);
            $this->SetFont('Arial', '', 6);
            $this->Cell(20, 5, 'Fecha Comprobante :', 0, 0, 'R');
            $this->SetFont('Arial', 'B', 6);
            $this->Cell(40, 5, $datos['rct_fechaComprobante'], 0, 1, 'L');
        }

        $this->SetXY($ix, $ytmp + 3);
        $ytmp = $this->GetY();
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(264, 5, $datos['gpo_id'] . $datos['ur_id'] . '  -' . ' ' . utf8Decode($datos['ur_nombre']), 0, 0, 'C');

        $this->SetXY($ix, $ytmp + 3);
        $this->SetFont('Arial', 'B', 8);
        $ytmp = $this->GetY();
        $this->Cell(264, 5, 'RECTIFICACION DE CUENTA POR LIQUIDAR CERTIFICADA (CLC) DE EROGACIONES PRESUPUESTALES PARA PAGO', 0, 0, 'C');

        $this->SetFont('Arial', 'B', 8);
        $this->SetXY($ix, $ytmp + 3);
        $ytmp = $this->GetY();
        $this->Cell(264, 5, utf8Decode($textotipoclc), 0, 0, 'C');

        $this->SetFont('Arial', 'B', 8);
        $this->SetXY($ix, $ytmp + 3);
        $ytmp = $this->GetY();
        if ($datos['rct_reintegro'] == 1) {
            $this->Cell(264, 5, 'CON REINTEGRO DE EFECTIVO', 0, 0, 'C');
        } else {
            $this->Cell(264, 5, 'SIN REINTEGRO DE EFECTIVO', 0, 1, 'C');
        }

        $this->SetXY($ix - 60, $ytmp - 3);
        $this->SetFont('Arial', '', 6);
        $this->Cell(15, 5, 'Clc...:', 0, 0, 'R');
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(10, 5, $datos['clc_id'], 0, 0, 'L');

        $this->SetXY($ix - 60, $ytmp);
        $this->SetFont('Arial', '', 6);
        $this->Cell(15, 5, 'Fecha...:', 0, 0, 'R');
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(40, 5, $datos['clc_fechaElaboracion'], 0, 1, 'L');

        $this->SetXY($ix - 60, $ytmp + 3);
        $this->SetFont('Arial', '', 6);
        $this->Cell(15, 5, 'Clabe Interbancaria...:', 0, 0, 'R');
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(40, 5, $datos['bur_id'], 0, 1, 'L');
        $this->SetFont('Arial', 'B', 8);
        $this->SetXY($ix, $ytmp + 3);
        $ytmp = $this->GetY();

        $this->SetXY($ix - 60, $ytmp + 3);
        $this->SetFont('Arial', '', 6);
        $this->Cell(13, 5, 'Banco...:', 0, 0, 'R');
        $this->SetFont('Arial', 'B', 6);

        $dx = $this->GetX();
        $dy = $this->GetY();
        $this->setXY($dx, $dy + 1);
        $this->MultiCell(32, 2.5, utf8Decode($datos['iba_nombre']), 0, 'L');

        //Etiqueta de C. Secretario de Finanzas
        $this->SetXY($ix, $ytmp + 5);
        $ytmp = $this->GetY();
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(60, 5, 'C.SECRETARIO DE FINANZAS', 0, 0, 'L');

        //Etiqueta presente
        $this->SetXY($ix, $ytmp + 3);
        $ytmp = $this->GetY();
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(60, 5, 'P R E S E N T E', 0, 1, 'L');

        //Datos clave de financiamientoa
        //documentacion anexa
        //Nombre de la obra
        if ($datos['tcl_id'] == 'F' || $datos['tcl_id'] == 'G' || $datos['tcl_id'] == 'H') {
            $this->SetXY($ix, $ytmp + 3);
            $ytmp = $this->GetY();
            $this->SetFont('Arial', 'B', 6.5);
            $this->Cell(34, 5, 'OBRA:', 0, 0, 'L');
            $this->Cell(166, 5, $datos['oa_id'] . '-' . utf8Decode($datos['oa_nombre']), 0, 0, 'L');
        }

        $this->SetXY($ix, $ytmp + 3);
        $ytmp = $this->GetY();
        $this->SetFont('Arial', 'B', 6.5);
        $this->Cell(34, 5, 'CLAVE DE FINANCIAMIENTO:', 0, 0, 'L');
        $this->Cell(166, 5, $datos['cfi_clave'] . '-' . utf8Decode($datos['cfi_nombre']) . '  EJERCICIO 20' . $datos['cfi_anio'], 0, 0, 'L');

        //Datos Concepto
        $this->SetXY($ix, $ytmp + 3);
        $ytmp = $this->GetY();
        $this->SetFont('Arial', 'B', 6.5);
        $this->Cell(34, 5, 'JUSTIFICACION:', 0, 0, 'L');
        $yconcepto = $this->GetY();
        $xconcepto = $this->GetX();
        $this->ln(1);
        $this->SetX($xconcepto);
        $this->MultiCell(228, 2.5, utf8Decode($conceptoclc), 0, 'L');
        $this->ln(2);
        $this->SetXY($ix, $ytmp + 8);
        $ytmp = $this->GetY();
        $this->SetFont('Arial', 'B', 6.5);
        $this->Cell(40, 5, utf8Decode('NÚMERO DE RECTIFICACIONES:'), 0, 0, 'L');
        $this->Cell(166, 5, $datos['clc_rectificaciones'], 0, 0, 'L');
        $this->ln(4);

        //Encabezado de la tabla
        $this->SetFont('Arial', 'B', 9);
        $tmp = $this->GetBorders(0);

        $ytmp = $this->GetY();
        $this->Line(15, $ytmp + 1, 274, $ytmp + 1);
        $this->SetFont('Arial', '', 5.5);
        $this->ln(1.5);

        if (!is_null($_POST['tcl_id']) && isset($_POST['tcl_id']) && $_POST['tcl_id'] == 'A' || $_POST['tcl_id'] == 'C') {

            $this->Cell(8, 2, utf8Decode('No.AFEC'), 0, 0, 'L');
            $this->Cell(11, 2, utf8Decode('No.CTRL'), 0, 0, 'L');
            $this->Cell(39, 2, utf8Decode('CVE. PRESUP./CTA. CONTAB.'), 0, 0, 'L');
            $this->Cell(48, 2, utf8Decode('DESCRIPCIÓN'), 0, 0, 'L');
            $this->Cell(17, 2, utf8Decode('REFERENCIA'), 0, 0, 'L');
            $this->Cell(20, 2, utf8Decode('FECHA INI.'), 0, 0, 'C');
            $this->Cell(20, 2, utf8Decode('FECHA FIN.'), 0, 0, 'C');
            $this->Cell(45, 2, utf8Decode('PROVEEDOR'), 0, 0, 'L');
            $this->Cell(23, 2, utf8Decode('R. F. C.'), 0, 0, 'L');
            $this->Cell(24, 2, utf8Decode('IMPORTE NETO'), 0, 0, 'R');
            $this->Cell(5, 2, utf8Decode('+/-'), 0, 0, 'C');
        } else {

            $this->Cell(8, 2, utf8Decode('No.AFEC'), 0, 0, 'L');
            $this->Cell(11, 2, utf8Decode('No.CTRL'), 0, 0, 'L');
            $this->Cell(39, 2, utf8Decode('CVE. PRESUP./CTA. CONTAB.'), 0, 0, 'L');
            $this->Cell(38, 2, utf8Decode('DESCRIPCIÓN'), 0, 0, 'L');
            $this->Cell(16, 2, utf8Decode('REFERENCIA'), 0, 0, 'L');
            $this->Cell(20, 2, utf8Decode('FECHA REF.'), 0, 0, 'C');
            $this->Cell(36, 2, utf8Decode('PROVEEDOR'), 0, 0, 'L');
            $this->Cell(22, 2, utf8Decode('R. F. C.'), 0, 0, 'L');
            $this->Cell(22, 2, utf8Decode('IMPORTE'), 0, 0, 'R');
            $this->Cell(22, 2, utf8Decode('IMPORTE IVA'), 0, 0, 'R');
            $this->Cell(22, 2, utf8Decode('IMPORTE NETO'), 0, 0, 'R');
            $this->Cell(4, 2, utf8Decode('+/-'), 0, 0, 'C');
        }
        $this->Line(15, $ytmp + 3.8, 274, $ytmp + 3.8);

        $this->SetBorders($this->ultBorde);
        $ix = $this->GetX();
        $this->SetBorders(0);
        $this->SetFont('Arial', 'B', 10);
        $this->ln(2);
    }
}
$pselect = [];
$reintegro = $np = $nc = $whsub = $wf = $wh = '';
if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == '118' || $_SESSION[_S]['nvj_id'] > 2) {
    $wh .= 'WHERE gpo_id = :gpo_id
            AND ur_id = :ur_id
            AND clc_id = :clc_id ';

    $whsub .= 'WHERE detalle.gpo_id = :gpo_id
            AND detalle.ur_id = :ur_id
            AND clc_id = :clc_id ';

    $wf .= 'WHERE gpo_id = :gpo_id
            AND ur_id = :ur_id
            AND rct_id = :rct_id ';

    $pselect[':gpo_id'] = $_POST['gpo_id'];
    $pselect[':ur_id'] = $_POST['ur_id'];
    $pselect[':clc_id'] = $_POST['clc_id'];
    $pselect[':rct_id'] = $_POST['rct_id'];

} else {
    $wh .= 'WHERE gpo_id = :gpo_id
            AND ur_id = :ur_id
            AND clc_id = :clc_id ';

    $whsub .= 'WHERE detalle.gpo_id = :gpo_id
            AND detalle.ur_id = :ur_id
            AND clc_id = :clc_id ';

    $wf .= 'WHERE gpo_id = :gpo_id
            AND ur_id = :ur_id
            AND rct_id = :rct_id ';

    $pselect[':gpo_id'] = $_SESSION[_S]['gpo_id'];
    $pselect[':ur_id'] = $_SESSION[_S]['ur_id'];
    $pselect[':clc_id'] = $_POST['clc_id'];
    $pselect[':rct_id'] = $_POST['rct_id'];
}

$sql = '
        SELECT
            /*DATOS DE LA CLC ORIGINAL*/
            gpo_id,ur_id,ur_nombre,
            oa_numobra ,ore_oficioAut,oa_nombre,oa_' . APP_EJERCICIO_ANTERIOR . ' ,sft_nombre,cfi_anio,obr_contrato,obr_avanceFisico,oa_id,
            obr_avanceFinanciero,obr_cumplimiento,obr_vicios,obr_documentacion,obr_anticipo,obr_fianza,
            C.clc_id,ur_nombreAdministrativo,ur_cargoAdministrativo,ur_nombreTitular, ur_cargoTitular,
            clc_nombreAdministracion,clc_nombreRecursos,clc_fechaElaboracion,
            C.ecl_id, ecl_nombre,mpc_id, mpc_nombre,bur_id,iba_nombre,fte_id,sft_id,ori_id,ori_nombre,cfi_clave,cfi_nombre, cfi_ejercicio,
            ter_id,ter_nombre,tcl_id,tcl_nombre,stc_id,stc_nombre,tno_id,tno_nombre,mno_id,mno_nombre,smn_id,smn_nombre,C.clc_rectificaciones,

            C.afe_id as afe_idClc,afe_fechaInicio,afe_fechaFin,C.fecha_referencia AS fecha_referenciaClc,C.clave,clave_nombre,C.referencia AS referenciaClc,
            C.percepcion_bruto AS percepcion_brutoClc,C.percepcion_iva AS percepcion_ivaClc,C.percepcion_neto AS percepcion_netoClc,
            C.retencion_neto AS retencion_netoClc,C.tipo_movimiento,C.tipo_numcontrol,numcontrol AS numcontrolClc,rfc_id,rfc_nombre,

            /*DATOS DE LA RECTIFICACION*/
            R.afe_id as afe_idR,R.rct_id,R.numero_rectifiacacion,R.referencia AS referenciaR,DATE_FORMAT(R.fechaReferencia,"%d/%m/%Y") AS fechaReferenciaR,
            R.percepcionImporteBruto,R.iva_id,R.importeIva,R.percepciones,R.retenciones,R.tipo_Movimiento,
            R.tipo_numcontrol as tipo,concepto,R.ue_id,rct_justificacion,DATE_FORMAT(rct_fechaComprobante,"%d/%m/%Y")rct_fechaComprobante,rct_numeroComprobante,rct_reintegro,
            rct_fechaElaboracion,rct_fechaAutorizacion,R.ecl_id,neto,R.inicio,R.final
            
        FROM (
            SELECT gpo_id,ur_id,oa_id, ur_nombre,oa_numobra ,ore_oficioAut,oa_nombre,oa_' . APP_EJERCICIO_ANTERIOR . ',sft_nombre,cfi_anio,obr_contrato,obr_avanceFisico,
                obr_avanceFinanciero,obr_cumplimiento,obr_vicios,obr_documentacion,obr_anticipo,obr_fianza,clc_id, scp_Clc.ocl_id ,
                DATE_FORMAT(clc_fechaElaboracion,"%d/%m/%Y") clc_fechaElaboracion,clc_concepto,clc_firmas,clc_beneficiario,clc_lineaCaptura,scp_Clc.tem_carga,
                ur_nombreAdministrativo, ur_cargoAdministrativo, ur_nombreTitular, ur_cargoTitular, clc_nombreAdministracion,clc_nombreRecursos,
                ecl_id, ecl_nombre,mpc_id, mpc_nombre,bur_id,iba_nombre,fte_id,sft_id,ori_id,ori_nombre,cfi_clave,cfi_nombre, cfi_ejercicio,
                t.ter_id,ter_nombre,scp_Clc.tcl_id,tcl_nombre,scp_Clc.stc_id, stc_nombre,scp_Clc.tno_id,tno_nombre,scp_Clc.mno_id, mno_nombre, scp_Clc.smn_id, smn_nombre,
                afe_id,DATE_FORMAT(afe_fechaInicio,"%d/%m/%Y") afe_fechaInicio,DATE_FORMAT(afe_fechaFin,"%d/%m/%Y") afe_fechaFin,
                numcontrol,clave,clave_nombre,referencia,DATE_FORMAT(fecha_referencia,"%d/%m/%Y") fecha_referencia,
                percepcion_bruto,percepcion_iva,percepcion_neto, retencion_neto, tipo_movimiento, tipo_numcontrol,
                rfc_id,CONCAT(rfc_nombre," ",COALESCE(rfc_paterno,"")," ",COALESCE(rfc_materno,"")) AS rfc_nombre,IF(clc_rectificaciones IS NULL,1,clc_rectificaciones+1) AS clc_rectificaciones
            FROM cat_UnidadResponsable
                INNER JOIN aux_UnidadResponsable USING(gpo_id, ur_id)
                INNER JOIN scp_Clc USING(gpo_id, ur_id)
                INNER JOIN scp_ClcAuxiliar USING(gpo_id, ur_id,clc_id,ecl_id)
                INNER JOIN (
                    SELECT
                        bur_id,iba_nombre
                    FROM cat_BancosUr
                        INNER JOIN cat_InstitucionesBancarias USING(iba_id)
                ) bancosUr USING(bur_id)
                INNER JOIN cat_EstadoClc USING(ecl_id)
                INNER JOIN cat_ModoPagoClc USING(mpc_id)
                INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
                INNER JOIN sinpres.cat_OrigenRecurso' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id)
                INNER JOIN sinpres.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id)
                INNER JOIN sinpres.cat_Subfuente' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id)
                INNER JOIN sinpres.cat_TipoRecurso t USING(ter_id)
                INNER JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                INNER JOIN cat_TipoClc USING(tcl_id)
                INNER JOIN cat_SubtipoClc USING(tcl_id,stc_id)
                LEFT JOIN cat_TipoNomina USING(tno_id)
                LEFT JOIN cat_ModalidadNomina USING(mno_id)
                LEFT JOIN cat_SubmodalidadNomina USING(mno_id, smn_id)
                LEFT JOIN scp_Afectacion USING(gpo_id,ur_id,clc_id,ecf_id)
                LEFT JOIN(
                    SELECT
                        detalle.gpo_id, detalle.ur_id, clc_id, afe_id,
                        ncp_id numcontrol, CONCAT(pre_claveCompleta,tcp_id) clave,pe_nombre clave_nombre,
                        ncp_referencia referencia,ncp_fechaReferencia fecha_referencia,
                        ncp_percepcionBruto percepcion_bruto,ncp_percepcionIva percepcion_iva,
                        ncp_percepcionNeto percepcion_neto, 0 retencion_neto,
                        "+" tipo_movimiento,
                        rfc_id,rfc_nombre, rfc_paterno, rfc_materno,
                        "P" tipo_numcontrol
                    FROM scp_Presupuestal detalle
                        INNER JOIN presupuesto USING(gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                        INNER JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
                        INNER JOIN sinpres.cat_Proveedores USING(rfc_id)
                        INNER JOIN cat_Proveedor USING(rfc_id)
                    ' . $whsub . '
                    UNION
                    SELECT detalle.gpo_id, detalle.ur_id, clc_id, afe_id,
                        ncc_id numcontrol, CONCAT( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, ".", cta_gpo_id, cta_ur_id,".", cta_ue_id, ".", cgc_id, ".", cta_id ) clave, cta_nombre clave_nombre,
                        ncc_referencia referencia,ncc_fechaReferencia fecha_referencia,
                        0 percepcion_bruto, 0 percepcion_iva,
                        ncc_percepcion percepcion_neto,ncc_retencion retencion_neto,
                        ncc_tipoMovimiento tipo_movimiento,
                        rfc_id,rfc_nombre, rfc_paterno, rfc_materno,
                        "C" tipo_numcontrol
                    FROM scp_Contable detalle
                        INNER JOIN cg_PlanCuentas USING(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id)
                        INNER JOIN sinpres.cat_Proveedores USING(rfc_id)
                        INNER JOIN cat_Proveedor USING(rfc_id)
                    ' . $whsub . '
                ) nc USING(gpo_id, ur_id, clc_id, afe_id)
                LEFT JOIN `enc_Programatico` USING(gpo_id,ur_id,epg_id)
                LEFT JOIN cat_ObraClc USING(gpo_id,ur_id,clc_id,epg_id)
                LEFT JOIN `cat_ObraActividad` USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                LEFT JOIN `poa_OrigenRecurso` USING(gpo_id,ur_id,epg_id,ecf_id)
                ' . $wh . '
                ORDER BY tipo_movimiento, afe_id, numcontrol
        )C
        INNER JOIN
        (
                SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,numero_rectifiacacion,numcontrol,referencia,fechaReferencia,
                    percepcionImporteBruto,iva_id,importeIva,percepciones,retenciones,importeOtras,tipo_Movimiento,
                    tipo_numcontrol,clave,concepto,
                    ue_id,rct_justificacion,rct_fechaComprobante,rct_numeroComprobante,rct_reintegro,
                    DATE_FORMAT( rct_fechaElaboracion,"%d/%m/%Y") rct_fechaElaboracion,
                    DATE_FORMAT( rct_fechaAutorizacion,"%d/%m/%Y") rct_fechaAutorizacion,ecl_id,neto,inicio,final
            FROM scp_MaestroRectificacion
            JOIN(
                SELECT
                    gpo_id,ur_id,clc_id,afe_id,rct_id,numero_rectifiacacion,numcontrol,referencia,fechaReferencia,
                    percepcionImporteBruto,iva_id,importeIva,percepciones,retenciones,importeOtras,tipo_Movimiento,
                    tipo_numcontrol,clave,concepto,ABS(percepciones - retenciones) as neto,
                    DATE_FORMAT( inicio,"%d/%m/%Y") as inicio,DATE_FORMAT( final,"%d/%m/%Y") as  final
                FROM
                (
                    SELECT  gpo_id,ur_id,rct_id,rcc_id  AS numero_rectifiacacion,clc_id,afe_id,ncc_id AS numcontrol,
                        ncc_referencia  AS referencia,ncc_fechaReferencia  AS fechaReferencia,0 AS percepcionImporteBruto,
                        0 AS iva_id,0 AS importeIva,ncc_percepcion AS percepciones,ncc_retencion AS retenciones,rcc_importeOtras AS importeOtras,
                        ncc_tipoMovimiento AS tipo_Movimiento,"CONTABLE" AS tipo_numcontrol,ncc_clave AS clave,ncc_concepto AS concepto,
                        ncc_fechaInicio as inicio,ncc_fechaFin as final
                    FROM scp_RectificacionContable
                    ' . $wf . ' ' . $nc . '
                    UNION ALL
                    SELECT gpo_id,ur_id,rct_id,rcp_id AS numero_rectifiacacion,clc_id,afe_id,ncp_id AS numcontrol,
                        ncp_referencia AS referencia,ncp_fechaReferencia AS fechaReferencia,ncp_percepcionImporteBruto AS percepcionImporteBruto,
                        iva_id AS iva_id,ncp_importeIva AS importeIva,ncp_percepcionNeto AS percepciones,0 retenciones,rcp_importeOtras AS importeOtras,
                        "+" AS tipo_Movimiento,"PRESUPUESTAL" AS tipo_numcontrol,ncp_clave AS clave,ncp_concepto AS concepto,
                        ncp_fechaInicio as inicio,ncp_fechaFin as final
                    FROM scp_RectificacionPresupuestal
                    ' . $wf . ' ' . $np . '
                )NUMRECTIFICACIONES
                ' . $wf . '
            )MAESTRO USING(gpo_id,ur_id,clc_id,rct_id)
            ' . $wf . ' ' . $reintegro . '
        )R USING(gpo_id,ur_id,numcontrol,tipo_Movimiento)
        LEFT JOIN (
            SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,numero_rectifiacacion,numcontrol,
                percepciones,retenciones,tipo_Movimiento
            FROM scp_MaestroRectificacion
            JOIN(
            SELECT
                gpo_id,ur_id,clc_id,afe_id,rct_id,numero_rectifiacacion,numcontrol,
                percepciones, retenciones,tipo_Movimiento
            FROM
            (
                SELECT  gpo_id,ur_id,rct_id,rcc_id  AS numero_rectifiacacion,clc_id,afe_id,ncc_id AS numcontrol,
                    ncc_percepcion AS percepciones,ncc_retencion AS retenciones,ncc_tipoMovimiento AS tipo_Movimiento
                        FROM scp_RectificacionContable
                        ' . $wh . ' and rct_id != :rct_id
                        UNION ALL
                        SELECT gpo_id,ur_id,rct_id,rcp_id AS numero_rectifiacacion,clc_id,afe_id,ncp_id AS numcontrol,
                            ncp_percepcionNeto AS percepciones,0 retenciones,"+" AS tipo_Movimiento
                        FROM scp_RectificacionPresupuestal
                        ' . $wh . ' and rct_id != :rct_id
                    )NUMRECTIFICACIONES
                    ' . $wh . ' and rct_id != :rct_id
                )MAESTRO USING(gpo_id,ur_id,clc_id,rct_id)
                ' . $wh . ' and rct_id != :rct_id and ecl_id=6
        )RA USING (gpo_id,ur_id,numcontrol,tipo_Movimiento)
        order by C.tipo_movimiento, C.afe_id, C.numcontrol
    ';
$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar($pselect);

$pdf = new Reportetabla('L', 'mm', 'Letter');
$tipomovanterior = '';
$totalrectificacion = 0;
$sumapercepciones = 0;
$sumaretenciones = 0;

$isMostrar = $conexion->getNumeroRegistros() != 0 ? true : false;

if ($row = $conexion->extraer()) {

    $sqlValidarRectificacionesAnteriores = '
        SELECT gpo_id, ur_id, clc_id, rct_id,
            percepcionesOtras, retencionesOtras
            FROM scp_MaestroRectificacion
                INNER JOIN ( SELECT gpo_id, ur_id, clc_id, rct_id,
                        sum(percepciones) AS percepcionesOtras,
                        sum(retenciones) AS retencionesOtras
                    FROM ( SELECT gpo_id, ur_id, rct_id, clc_id,
                            ncc_percepcion AS percepciones,
                            ncc_retencion AS retenciones
                        FROM scp_RectificacionContable
                        ' . $wh . '
                            AND rct_id != :rct_id
                        UNION ALL
                        SELECT gpo_id, ur_id, rct_id, clc_id, ncp_percepcionNeto AS percepciones,
                            0 AS retenciones
                        FROM scp_RectificacionPresupuestal
                        ' . $wh . '
                            AND rct_id != :rct_id
                        )NUMRECTIFICACIONES
                    ' . $wh . '
                        AND rct_id != :rct_id
                    GROUP BY gpo_id,ur_id,clc_id,rct_id
                )MAESTRO USING(gpo_id,ur_id,clc_id,rct_id)
            ' . $wh . '
                AND rct_id != :rct_id
                AND ecl_id = 6 ';
    $conexion2 = new AccesoDatos();
    $conexion2->conectar();
    $conexion2->setSql($sqlValidarRectificacionesAnteriores);
    $conexion2->consultar($pselect);
    if ($conexion2->getNumeroRegistros() > 0) {
        $rectifiacionAnterior = $conexion2->extraer();
    } else {
        $rectifiacionAnterior = ['percepcionesOtras' => 0, 'retencionesOtras' => 0];
    }
    $conexion2->desconectar();

    $pdf->setDatos($row);
    $pdf->AliasNbPages();
    $pdf->AddPage();

    do {
        if (mb_strlen($tipomovanterior) && $tipomovanterior != $row['tipo_movimiento']) {
            //TOTAL DE PERCEPCIONES
            $pdf->SetFont('Arial', 'B', 5.5);
            $pdf->SetFont('Arial', 'B', 5.5);
            $pdf->SetWidths([233, 22, 5]);
            $pdf->Row(['TOTAL PERCEPCIONES', number_format($sumapercepciones, 2, '.', ','), '']);
            $pdf->ln(6);

            $yreporte = $pdf->GetY();
            if ($yreporte > 148) {
                $pdf->AddPage();
            }
        }

        $yreporte = $pdf->GetY();
        if ($yreporte > 148) {
            $pdf->AddPage();
        }

        $bruto = 0;
        $iva = 0;
        $neto = 0;
        //si es un numero de control presupuestal
        if ($row['tipo_numcontrol'] == 'P') {
            $bruto = $row['percepcion_brutoClc'];
            $iva = $row['percepcion_ivaClc'];
            $neto = $row['percepcion_netoClc'];
        } elseif ($row['tipo_numcontrol'] == 'C') { // si es un numero de control contable

            if ($row['tipo_movimiento'] == '+') {
                $bruto = $row['percepcion_netoClc'];
                $neto = $row['percepcion_netoClc'];
            } else {
                $bruto = $row['retencion_netoClc'];
                $neto = $row['retencion_netoClc'];
            }
            $iva = $row['percepcion_ivaClc'];
        }

        if (isset($_POST['tcl_id']) && $_POST['tcl_id'] == 'A' || $_POST['tcl_id'] == 'C') {

            $pdf->ln(1);
            $pdf->SetFont('Arial', 'B', 5.5);
            $pdf->SetWidths([8, 11, 39, 48, 17, 20, 20, 45, 23, 24, 5]);
            $pdf->SetAligns(['L', 'L', 'L', 'L', 'C', 'C', 'C', 'L', 'R', 'R', 'C']);
            $pdf->SetAltoLinea(1.8);
            $pdf->Row([$row['afe_idClc'], $row['numcontrolClc'], $row['clave'], utf8Decode($row['clave_nombre']), $row['referenciaClc'], $row['inicio'], $row['final'], utf8Decode($row['rfc_nombre']), utf8Decode($row['rfc_id']), number_format($neto, 2), $row['tipo_movimiento']]);
        } else {

            $pdf->ln(1);
            $pdf->SetFont('Arial', 'B', 5.5);
            $pdf->SetWidths([8, 11, 39, 38, 16, 20, 36, 22, 22, 22, 22, 4]);
            $pdf->SetAligns(['L', 'L', 'L', 'L', 'L', 'C', 'L', 'R', 'R', 'R', 'R', 'C']);
            $pdf->SetAltoLinea(1.8);
            $pdf->Row([$row['afe_idClc'], $row['numcontrolClc'], $row['clave'], utf8Decode($row['clave_nombre']), $row['referenciaClc'], $row['fecha_referenciaClc'], utf8Decode($row['rfc_nombre']), $row['rfc_id'], number_format($bruto, 2, '.', ','), number_format($iva, 2, '.', ','), number_format($neto, 2, '.', ','), $row['tipo_movimiento']]);
        }

        if ($row['tipo_movimiento'] == '+') {
            $tipmovrct = '-';
        } else {
            $tipmovrct = '+';
        }

        $brutoR = 0;
        $ivaR = 0;
        $netoR = 0;

        if ($row['tipo'] == 'PRESUPUESTAL') {

            if ($row['importeIva'] != 0) {
                //Esta comparacion se hizo porque cuando se hacia la resta del importe neto de la clc con el importe de la rectificacion los importes del calculo del
                //importe iva y del bruto no coincidian por el calculo de los decimales
                if ($row['percepcion_netoClc'] - $row['neto'] == 0) {
                    $netoR = $neto;
                    $brutoR = $bruto;
                    $ivaR = $iva;
                } else {
                    $netoR = Math::redondear($row['neto'], 2);
                    $brutoR = Math::redondear(($netoR / 1.16), 2);
                    $ivaR = Math::redondear(($brutoR * .16), 2);
                }
            } else {
                $netoR = $row['neto'];
                $brutoR = $row['neto'];
                $ivaR = 0;
            }
        } elseif ($row['tipo'] == 'CONTABLE') { // si es un numero de control contable
            if ($row['tipo_Movimiento'] == '+') {
                $brutoR = $row['percepciones'];
                $netoR = $row['percepciones'];
            } else {
                $brutoR = $row['retenciones'];
                $netoR = $row['retenciones'];
            }
            $ivaR = $row['importeIva'];
        }

        if (isset($_POST['tcl_id']) && $_POST['tcl_id'] == 'A' || $_POST['tcl_id'] == 'C') {
            $pdf->ln(1);
            $pdf->SetFont('Arial', 'B', 5.5);
            $pdf->SetWidths([8, 11, 39, 48, 17, 20, 20, 45, 23, 24, 5]);
            $pdf->SetAligns(['L', 'L', 'L', 'L', 'C', 'C', 'C', 'L', 'R', 'R', 'C']);
            $pdf->SetAltoLinea(1.8);
            $pdf->Row(['', '', $row['clave'], utf8Decode($row['clave_nombre']), $row['referenciaR'], $row['inicio'], $row['final'], utf8Decode($row['rfc_nombre']), utf8Decode($row['rfc_id']), number_format($netoR, 2), $tipmovrct]);
        } else {
            $pdf->SetFont('Arial', 'B', 5.5);
            $pdf->SetWidths([8, 11, 39, 38, 16, 20, 36, 22, 22, 22, 22, 4]);
            $pdf->SetAligns(['L', 'L', 'L', 'L', 'L', 'C', 'L', 'R', 'R', 'R', 'R', 'C']);
            $pdf->SetAltoLinea(1.8);
            $pdf->Row(['', '', $row['clave'], utf8Decode($row['clave_nombre']), $row['referenciaR'], $row['fechaReferenciaR'], utf8Decode($row['rfc_nombre']), $row['rfc_id'], number_format($brutoR, 2, '.', ','), number_format($ivaR, 2, '.', ','), number_format($netoR, 2, '.', ','), $tipmovrct]);
        }

        $recAnterior = $row['tipo_numcontrol'] == 'P' ?
            $rectifiacionAnterior['percepcionesOtras'] :
                ($row['tipo_movimiento'] == '-' ?
                    $rectifiacionAnterior['retencionesOtras'] :
                    $rectifiacionAnterior['percepcionesOtras']);
        $pdf->SetFont('Arial', 'B', 5.5);
        $pdf->SetWidths([233, 22, 5]);
        $pdf->SetAligns(['R', 'R', 'C']);
        $pdf->SetAltoLinea(1);
        $pdf->Row(['Otras Rectificaciones', number_format($recAnterior, 2, '.', ','), $tipmovrct]);

        $resta = round((float) ($neto - ($recAnterior + $row['neto'])), 2);
        $pdf->SetFont('Arial', 'B', 5.5);
        $pdf->SetWidths([233, 22, 5]);
        $pdf->SetAligns(['R', 'R', 'R']);
        $pdf->Row(['Disponible a Rectificar...:', number_format($resta, 2, '.', ','), '']);
        $tipomovanterior = $row['tipo_movimiento'];
        $pdf->ln(2);

        if ($row['tipo_movimiento'] == '+') {
            $sumapercepciones += $row['neto'];
        } else {
            $sumaretenciones += $row['neto'];
        }
        $temEcl = $row['ecl_id'];
    } while ($row = $conexion->extraer());
}

//al final de la rectificacion se pone el total de las retenciones y el total de la rectificacion
if ($sumaretenciones != 0) {
    $pdf->SetFont('Arial', 'B', 5.5);
    $pdf->SetWidths([233, 22, 5]);
    $pdf->Row(['TOTAL RETENCIONES', number_format($sumaretenciones, 2, '.', ','), '']);
    $pdf->ln(2);
}

if ($isMostrar) {
    $totalrectificacion += ABS($sumapercepciones - $sumaretenciones);
    $pdf->SetFont('Arial', 'B', 5.5);
    $pdf->SetWidths([233, 22, 5]);
    $pdf->SetAligns(['R', 'R', 'R']);
    $pdf->Row(['TOTAL RECTIFICACION DE C.L.C.', number_format($totalrectificacion, 2, '.', ','), '']);
}

//REIMPRIMIR LA RECTIFICACIÓN
if (isset($_POST['reimprimir']) && $_SESSION[_S]['nvj_id'] > 2) {
    $acceso = new AccesoDatos();
    $acceso->setSql('
        INSERT INTO ' . BD_BITACORA . '.bit_Rectificacion (gpo_id,ur_id,rct_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
        VALUES (?, ?, ?, ?, ?,"SE REIMPRIMIO LA RECTIFICACION SIN MARCA DE AGUA PANTALLA S_5_17",INET_ATON(?), ?,now());
    ');
    $params = [$_POST['gpo_id'], $_POST['ur_id'], $_POST['rct_id'], $_POST['clc_id'], $temEcl, $_SESSION[_S]['ses_ip'], $_SESSION[_S]['usr_id']];
    $acceso->conectar();
    $acceso->consultar($params);
    $acceso->desconectar();
}
$pdf->SetProtection(['print', 'annot-forms'], '', 'PDF_PASSWORD_OWNER_SCP');
$pdf->Output();
