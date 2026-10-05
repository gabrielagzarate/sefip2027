<?php

/**
 * Description of Exp_ProgramacionClcsPendientes
 *
 * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Collaborators: Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Created: 31-Oct-2018
 * Comment: -----
 * Objective: Generar archivo Excel de las Clcs pendientes.
 * Regla de Negocio: -----
 * Permits: Esta pantalla no pregunta por permisos de escritura.
 * */
session_start();
require_once dirname(__FILE__) . "/../../../../lib/AccesoDatos.php";
require_once dirname(__FILE__) . "/../../../../lib/Conexion.php";
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2017.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2018.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2019.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2020.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2021.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2022.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2023.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2024.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2025.php';
require_once dirname(__FILE__) . '/../../../../tes/models/clcs2026.php';
//Funcion para obtener los datos desde la base de datos
require_once dirname(__FILE__) . '/../../../../lib/tes/tesoreria/consultaProgramacionClcsPendientes.php';

class Exp_ProgramacionClcsPendientes extends generaPhpspreadsheet
{
    public $encabLin4;
    public $encabLin5;
    public $encabLin6;
    public $encabLin7;
    public $datos;
    public $nombreArchivoReporte;
    public $nombreColumnas;

    public function __construct()
    {
        parent::__construct();

        $prm = new StdClass();
        $prm->anio = (isset($_POST['anio']) && $_POST['anio'] != '') ? $_POST['anio'] : '';
        $prm->estado = (isset($_POST['estado']) && $_POST['estado'] != '') ? $_POST['estado'] : '';
        $prm->modo = (isset($_POST['modo']) && $_POST['modo'] != '') ? $_POST['modo'] : '';
        $prm->documento = (isset($_POST['documento']) && $_POST['documento'] != '') ? $_POST['documento'] : '';

        $prm->tipo = (isset($_POST['tipo']) && $_POST['tipo'] != '') ? $_POST['tipo'] : '';
        $prm->subtipo = (isset($_POST['subtipo']) && $_POST['subtipo'] != '') ? $_POST['subtipo'] : '';
        $prm->impuesto = (isset($_POST['impuesto']) && $_POST['impuesto'] != '') ? $_POST['impuesto'] : '';
        $prm->clc = (isset($_POST['clc']) && $_POST['clc'] != '') ? $_POST['clc'] : '';
        $prm->orden = (isset($_POST['orden']) && $_POST['orden'] != '') ? $_POST['orden'] : '';
        $prm->numerour = (isset($_POST['numerour']) && $_POST['numerour'] != '') ? $_POST['numerour'] : '';
        $prm->nombreur = (isset($_POST['nombreur']) && $_POST['nombreur'] != '') ? $_POST['nombreur'] : '';
        $prm->rfc = (isset($_POST['rfc']) && $_POST['rfc'] != '') ? $_POST['rfc'] : '';
        $prm->proveedor = (isset($_POST['proveedor']) && $_POST['proveedor'] != '') ? $_POST['proveedor'] : '';
        $prm->beneficiario = (isset($_POST['beneficiario']) && $_POST['beneficiario'] != '') ? $_POST['beneficiario'] : '';
        $prm->concepto = (isset($_POST['concepto']) && $_POST['concepto'] != '') ? $_POST['concepto'] : '';
        $prm->importeMin = (isset($_POST['importeMin']) && $_POST['importeMin'] != '') ? $_POST['importeMin'] : '';
        $prm->importeMax = (isset($_POST['importeMax']) && $_POST['importeMax'] != '') ? $_POST['importeMax'] : '';
        $prm->fechaMin = (isset($_POST['fechaMin']) && $_POST['fechaMin'] != '') ? $_POST['fechaMin'] : '';
        $prm->fechaMax = (isset($_POST['fechaMax']) && $_POST['fechaMax'] != '') ? $_POST['fechaMax'] : '';

        $prm->importeMax = trim($prm->importeMax, " $");
        $prm->importeMin = trim($prm->importeMin, " $");
        $prm->importeMax = str_replace(',', '', $prm->importeMax);
        $prm->importeMin = str_replace(',', '', $prm->importeMin);

        if ($prm->fechaMin != "") {
            $arrFecha = explode("/", $prm->fechaMin);
            $prm->fechaMin = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
        }
        if ($prm->fechaMax != "") {
            $arrFecha = explode("/", $prm->fechaMax);
            $prm->fechaMax = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
        }

        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'SECRETARÍA DE FINANZAS DE OAXACA ';
        $this->encabLin3 = 'TESORERÍA ' . APP_EJERCICIO;
        $this->encabLin4 = 'LISTADOS DE CLCs PENDIENTES';
        $this->encabLin6 = '';

        $this->encabLin5 = '';
        $this->datos = consultaProgramacionClcsPendientes($prm);
        $this->encabLin7 = '';
        $this->nombreArchivoReporte = 'ReporteCLC';

        $this->contenido();
    }
    public function contenido()
    {
        $encabezado = array(
            'encabLin1' => $this->encabLin1,
            'encabLin2' => $this->encabLin2,
            'encabLin3' => $this->encabLin3,
            'encabLin4' => $this->encabLin4,
            'encabLin5' => $this->encabLin5,
            'encabLin6' => $this->encabLin6,
            'encabLin7' => $this->encabLin7,
            'alineacionenc' => 'I'
        );

        $this->nombreColumnas = array('FECHA', 'MODO', 'UR', 'CLC', 'FOLIO', 'TIPO', 'NOMBRE_TIPO', 'RESPONSABLE', 'BENEFICIARIO', 'CONCEPTO', 'CARGO', 'DESTINO', 'IMPORTE', 'CVE_FIN', 'SUBTIPO', 'CVE_NOMBRE');
        $incluirColumnas = array(
            'FECHA_ELABORACION',
            'MODO',
            'UR',
            'CLC',
            'FOLIO',
            'TIPO',
            'NOMBRE_TIPO',
            'RESPONSABLE',
            'BENEFICIARIO',
            'CONCEPTO',
            'CARGO',
            'DESTINO',
            'IMPORTE',
            'CVE_FIN',
            'NOMBRE_SUBTIPO',
            'CVE_NOMBRE'
        );
        $tipoColumnas = array('cI', 'cI', 'cD', 'cD', 'cD', 'cI', 'cI', 'cI', 'cI', 'cI', 'cD', 'cD', 'n', 'cI', 'cI', 'cI');
        $anchoColumnas = array(15, 20, 6, 6, 10, 7, 30, 30, 50, 100, 30, 30, 25, 20, 30, 30);

        $table = array();
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['ancho'][$val] = $anchoColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];

            $table['tipoTabla'] = 'M';
        }

        $name = 114;
        $path_image = dirname(__FILE__) . '/../../../../img/Logo Institucional/' . $name . '.jpg';
        $tablaImagen = array('imagen' => 1);
        $tablaImagen[0] = array('ruta' => $path_image, 'nombre' => 'XXX', 'colImg' => 'H1', 'altura' => 100, 'alingXimg' => 50);
        $this->generaExcel($this->datos, $encabezado, $table, array('nombre' => $this->nombreArchivoReporte, 'tablaimg' => $tablaImagen));
    }
}
$exp_ajustes = new Exp_ProgramacionClcsPendientes();
