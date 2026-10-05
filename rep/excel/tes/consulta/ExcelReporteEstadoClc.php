<?php
/**
 * Description of function nivelFolio
 *
 * @author ?
 * Collaborators: Adriana Pacheco <adriana.pacheco@finanzasoaxaca.gob.mx>
 * Created: 12-Dic-2019
 * Comment: -----
 * Objective: Mostrar en un Excel datos de la Clc
 * Regla de Negocio: Obtener las clcs de acuerdo al filtro de la pantalla  a partir del ejercicio 2017 segun sea el caso
 *
 **/
session_start();
ini_set('memory_limit', '54000M');
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once '../../../../lib/tes/consulta/nivelEstadoClc.php';
require_once '../../../../lib/AccesoDatos.php';

class ExcelReporteEstadoClc extends generaPhpspreadsheet
{
    public string | null $encabLin1;
    public string | null $encabLin2;
    public string | null $encabLin3;
    public  $encabLin4;
    public  $encabLin5;
    public  $encabLin6;
    public  $encabLin7;
    public $datos;
    public $nombreArchivoReporte;
    public $nombreColumnas;
    public $edoClc;

    public function __construct()
    {

        parent::__construct();
        $sqlCtrlAnterior = "";
        $estado2018 = "";
        $documento = (isset($_POST['documento']) && $_POST['documento'] != '') ? $_POST['documento'] : '';
        $ejercicio = (isset($_POST['ejercicio']) && $_POST['ejercicio'] != '') ? $_POST['ejercicio'] : '';
        $estado = ($ejercicio == APP_EJERCICIO) ? " WHERE ant.ecl_id > 9 " : " WHERE ant.ecl_id > 9";
        $estadoCtrl2018 = 'AND ((C.ecl_id  BETWEEN 1 AND 9) OR (C.ecl_id  BETWEEN 16 AND 21))';
        $clc = (isset($_POST['clc']) && $_POST['clc'] != '') ? $_POST['clc'] : '';
        $folio = (isset($_POST['folio']) && $_POST['folio'] != '') ? $_POST['folio'] : '';
        $numerour = (isset($_POST['numerour']) && $_POST['numerour'] != '') ? $_POST['numerour'] : '';
        $importeMax = (isset($_POST['importeMax']) && $_POST['importeMax'] != '') ? $_POST['importeMax'] : '';
        $importeMin = (isset($_POST['importeMin']) && $_POST['importeMin'] != '') ? $_POST['importeMin'] : '';
        $fechaMin = (isset($_POST['fechaMin']) && $_POST['fechaMin'] != '') ? $_POST['fechaMin'] : '';
        $fechaMax = (isset($_POST['fechaMax']) && $_POST['fechaMax'] != '') ? $_POST['fechaMax'] : '';

        $beneficiario = (isset($_POST['beneficiario']) && $_POST['beneficiario'] != '') ? $_POST['beneficiario'] : '';
        $concepto = (isset($_POST['concepto']) && $_POST['concepto'] != '') ? $_POST['concepto'] : '';
        $lote = (isset($_POST['lote']) && $_POST['lote'] != '') ? $_POST['lote'] : '';
        $claveBancaria = (isset($_POST['claveBancaria']) && $_POST['claveBancaria'] != '') ? $_POST['claveBancaria'] : '';
        $claveFinanciamiento = (isset($_POST['claveFinanciamiento']) && $_POST['claveFinanciamiento'] != '') ? $_POST['claveFinanciamiento'] : '';
        $loteEgreso = (isset($_POST['loteEgreso']) && $_POST['loteEgreso'] != '') ? $_POST['loteEgreso'] : '';
        $cuentaDestino = (isset($_POST['cuentaDestino']) && $_POST['cuentaDestino'] != '') ? $_POST['cuentaDestino'] : '';

        $nombreUR = (isset($_POST['nombreUR']) && $_POST['nombreUR'] != '') ? $_POST['nombreUR'] : '';
        $estadoClc = (isset($_POST['estadoClc']) && $_POST['estadoClc'] != '') ? $_POST['estadoClc'] : '';
        $estatal = (isset($_POST['estatal']) && $_POST['estatal'] != '') ? $_POST['estatal'] : '';
        $rfc = (isset($_POST['rfc']) && $_POST['rfc'] != '') ? $_POST['rfc'] : '';

        $importeMax = trim($importeMax, " $");
        $importeMin = trim($importeMin, " $");
        $importeMax = str_replace(',', '', $importeMax);
        $importeMin = str_replace(',', '', $importeMin);

        if ($fechaMin != "") {
            $arrFecha = explode("/", $fechaMin);
            $fechaMin = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
        }
        if ($fechaMax != "") {
            $arrFecha = explode("/", $fechaMax);
            $fechaMax = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
        }
        $sqlExtra = "";

        if ($beneficiario != '') {
            $sqlExtra .= " AND C.clc_beneficiario like '%" . $beneficiario . "%' ";
        }

        if ($concepto != '') {
            $sqlExtra .= " AND C.clc_concepto like '%" . $concepto . "%' ";
        }
        if ($lote != '') {
            $nuevoLote = "";
            $tok = strtok($lote, ",");
            while ($tok !== false) {
                $nuevoLote .= "'" . $tok . "',";
                $tok = strtok(",");
            }
            $nuevoLote = substr($nuevoLote, 0, -1);
            $sqlExtra .= " AND CONCAT( IF ( LO.fpa_id = 1, 'T', IF (LO.fpa_id = 2, 'C', 'L') ), LPAD(LO.msc_id, 2, '0'), LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3) ) in (" . $nuevoLote . ") ";
        }
        if ($claveBancaria != '') {
            $sqlExtra .= " AND CB.cbs_id ='" . $claveBancaria . "' ";
        }
        if ($cuentaDestino != '') {
            $sqlExtra .= " AND C.bur_id ='" . $cuentaDestino . "' ";
        }
        if ($claveFinanciamiento != '') {
            $sqlExtra .= " AND CF.ecf_clave ='" . $claveFinanciamiento . "' ";
        }
        if ($loteEgreso != '') {
            $sqlExtra .= " AND LE.loe_clave ='" . $loteEgreso . "' ";
        }
        if ($folio != '') {
            $sqlExtra .= " AND CX.clc_cereCo IN (" . $folio . ") ";
        }
        if ($importeMin != '' && $importeMax == '') {
            $sqlExtra .= " AND (C.clc_percepcion - C.clc_retencion) >= $importeMin ";
        }

        if ($importeMin == '' && $importeMax != '') {
            $sqlExtra .= " AND (C.clc_percepcion - C.clc_retencion) <= $importeMax ";
        }

        if ($importeMin != '' && $importeMax != '') {
            $sqlExtra .= " AND ((C.clc_percepcion - C.clc_retencion) BETWEEN $importeMin AND $importeMax) ";
        }

        if ($fechaMin != '' && $fechaMax == '') {
            $sqlExtra .= " AND C.clc_fechaElaboracion >= '$fechaMin' ";
        }

        if ($fechaMin == '' && $fechaMax != '') {
            $sqlExtra .= " AND C.clc_fechaElaboracion <= '$fechaMax' ";
        }

        if ($fechaMin != '' && $fechaMax != '') {
            $sqlExtra .= " AND (C.clc_fechaElaboracion BETWEEN '$fechaMin 00:00:00' AND '$fechaMax 23:59:59') ";
            if ($ejercicio != APP_EJERCICIO) {
                $sqlExtra .= " AND CF.ecf_clave ='" . $claveFinanciamiento . "' ";
                $estado2018 .= (strlen($estado2018) == 0) ? 'WHERE ' : ' AND ';
                $estado2018 .= " (C.clc_fechaElaboracion BETWEEN '$fechaMin 00:00:00' AND '$fechaMax 23:59:59')";
            }
        }

        if ($clc != "") {
            $sqlExtra .= " AND C.clc_id in (" . $clc . ") ";
            if ($ejercicio != APP_EJERCICIO) {
                $estado2018 .= (strlen($estado2018) == 0) ? 'WHERE ' : ' AND ';
                $estado2018 .= " C.clc_id in (" . $clc . ") ";
            }
        }
        if ($numerour != "") {
            $sqlExtra .= " AND CONCAT(C.gpo_id, LPAD( C.ur_id,2,0)) =" . $numerour;
        }
        if ($documento != 0) {
            $sqlExtra .= " AND clc_tipoCereCo= " . $documento;
        }
        if ($nombreUR != "") {
            $sqlExtra .= " AND  CONCAT(C.gpo_id, LPAD( C.ur_id,2,0)) =" . $nombreUR;
            if ($ejercicio != APP_EJERCICIO) {
                $estado2018 .= (strlen($estado2018) == 0) ? 'WHERE ' : ' AND ';
                $estado2018 .= ' CONCAT(C.gpo_id, LPAD( C.ur_id,2,0)) =' . $nombreUR;
            }
        }
        if ($estadoClc != "") {
            if ($ejercicio == APP_EJERCICIO) {
                $sqlExtra .= " AND  C.ecl_id =" . $estadoClc;
                $estado = 'WHERE ant.ecl_id =' . $estadoClc;
            } else {
                $sqlCtrlAnterior = " AND IF(ant.ecl_id IS NOT NULL, ant.ecl_id, C.ecl_id) =" . $estadoClc;
                $estado = 'WHERE ant.ecl_id =' . $estadoClc;
                $estado2018 = 'WHERE C.ecl_id =' . $estadoClc;
            }
        }
        if ($rfc != "") {
            $sqlExtra .= " AND  doc.rfc_id='" . $rfc . "'";
        }

        switch ($estatal) {
            case 1:
                $sqlExtra .= " AND  CB.ter_id ='A'";
                break;
            case 2:
                $sqlExtra .= " AND  CB.ter_id ='B'";
                break;
        }

        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'SECRETARÍA DE FINANZAS TESORERÍA ' . APP_EJERCICIO;
        $this->encabLin3 = 'TESORERÍA';
        $this->encabLin4 = 'LISTADO DE CLCs';
        $this->encabLin6 = '';

        $this->encabLin5 = '';
        $this->datos = nivelEstadoClc($ejercicio, $sqlExtra, $estado, $sqlCtrlAnterior, $estado2018, $estadoCtrl2018);
        $this->encabLin7 = '';
        $this->nombreArchivoReporte = 'ReporteCLC';
        $this->leyenda = "Las cuentas por liquidar certificadas señaladas en este listado, fueron tramitadas por las Unidades Responsables, de conformidad con lo establecido en los artículos 48 de la Ley Estatal de Presupuesto y Responsabilidad Hacendaria y 58 de su Reglamento; de igual manera fueron revisadas, validadas y autorizadas de acuerdo a la disponibilidad presupuestaria y/o suficiencia presupuestaria y remitidas en el sistema electrónico a la Tesorería por parte de la Dirección de Presupuesto de la Secretaría de Finanzas, de acuerdo a lo señalado en los artículos 32, 36, 37 y 38 del Reglamento Interno de la Secretaría de Finanzas del Poder Ejecutivo del Estado de Oaxaca, por tal motivo se puede proceder para su pago por parte de la Tesorería en atención a la disponibilidad financiera con la que cuente el Estado, de acuerdo a lo establecido en los artículos 43, 48 y 50 del Reglamento en cita.\nEl ejercicio del presupuesto, resguardo y custodia de la documentación justificativa y comprobatoria de los recursos públicos, son responsabilidad de los ejecutores de gasto, de conformidad con lo establecido en los artículos 4 de la Ley Estatal de Presupuesto y responsabilidad Hacendaria y 55 y 61 de su Reglamento.";
        $this->edoClc = $estadoClc;

        $this->contenido(); //llamdo a la function contenido
    }

    public function contenido()
    {
        if ($this->edoClc == "10") {
            $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7' => $this->encabLin7, 'alineacionenc' => 'I','leyenda' => $this->leyenda,'lineaInicio'=>6,'columna'=>'L', 'lineaFin'=>11);
        } else {
            $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7' => $this->encabLin7, 'alineacionenc' => 'I');
        }

        $this->nombreColumnas = array("#", "MODO", "EJERCICIO", "FECHA GENERACIÓN", "FECHA PAGO", "FECHA GENERACIÓN CERE", "UR", "CLC","CAPITULO GASTO", "CERE/CERECO", "TIPO", "SUBTIPO", "BENEFICIARIO", "CONCEPTO", "IMPORTE", "BANCO", "CUENTA CARGO", "NOMBRE CUENTA", "CTA DESTINO", "CVE_FIN", "LOTE", "RESPONSABLE", "ESTADO", "TIPO");
        $incluirColumnas = array("id", "modo", "ejercicio", "FECHA_ELABORACION", "FECHA_PAGO", "FECHA_CERE", "ur", "clc_id", "capitulo_gasto", "clc_cereCo", "tcl_nombre", "stc_nombre", "clc_beneficiario", "clc_concepto", "importe", "iba_nombre", "cbs_id", "cbs_nombre", "bur_id", "ecf_clave", "lote_id", "ur_nombre", "estado", "clc_tipoCereCo");
        $tipoColumnas = array("cC", "cI", "cC", "f", "f", "f", "cC", "cC", "cC", "cC", "cI", "cI", "cI", "cI", "n", "cI", "n", "cI", "n", "cI", "cI", "cI", "cI", "cC");

        $table = array();

        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }

        $path_image = dirname(__FILE__) . "/../../../../img/LOGO.jpg";
        $tablaImagen = array("imagen" => 1);
        $tablaImagen[0] = array("ruta" => $path_image, "nombre" => "XXX", "colImg" => "F1", "altura" => 100, "alingXimg" => 100);
        //llamado a generaExcel de generaPhpspreadsheet
        $this->generaExcel($this->datos, $encabezado, $table, array("nombre" => "ReporteCLC", "tablaimg" => $tablaImagen));
    }

} // fin clase

$exp_ajustes = new ExcelReporteEstadoClc();
