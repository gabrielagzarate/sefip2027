<?php
/**
 * Description of Exp_ConsultaClcFondo
 *
 * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Collaborators: 
 * Created: 18-Feb-2019
 * Migrated: 24-Feb-2019
 * Comment: -----
 * Objective: Generar un archivo Excel del listado de las CLC's pendientes.
 * Regla de Negocio: -----
 * Permits: -----
 * */          
ini_set('display_errors', 1);      
session_start();
ini_set('memory_limit','600M');
ini_set('max_execution_time',300);
set_time_limit(0);

require_once dirname(__FILE__).'/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__).'/../../../../lib/AccesoDatos.php';    
require_once dirname(__FILE__).'/../../../../lib/scg/fondos/consultaClcFondo.php';
require_once dirname(__FILE__).'/../../../../lib/calculaPeso.php';

class Exp_ConsultaClcFondo extends generaPhpspreadsheet
{
    public $documento;
    public $ejercicio;
    public $clc;
    public $folio;
    public $numerour;
    public $importeMax;
    public $importeMin;
    public $fechaMin;
    public $fechaMax;
    public $paginador;
    public $rfc;
    public $beneficiario;
    public $concepto;
    public $lote;
    public $claveBancaria;
    public $claveFinanciamiento;
    public $loteEgreso;
    public $cuentaDestino;
    public $nombreUR;
    public $estadoClc;
    public $estatal;
    public string|null $encabLin1;
    public string|null $encabLin2;
    public string|null $encabLin3;
    public  $encabLin4;
    public  $encabLin5;
    public  $encabLin6;
    public  $encabLin7;
    public $datos;
    public $nombreArchivoReporte;
    public $nombreColumnas;
    function __construct() {
        parent::__construct();      
                
        $this->documento = (isset($_POST['documento']) && $_POST['documento'] !='') ? $_POST['documento'] : '';
        $this->ejercicio = (isset($_POST['ejercicio']) && $_POST['ejercicio'] !='') ? $_POST['ejercicio'] : '';
        $this->clc = (isset($_POST['clc']) && $_POST['clc'] !='') ? $_POST['clc'] : '';
        $this->folio = (isset($_POST['folio']) && $_POST['folio'] !='') ? $_POST['folio'] : '';
        $this->numerour = (isset($_POST['numerour']) && $_POST['numerour'] !='') ? $_POST['numerour'] : '';
        $this->importeMax = (isset($_POST['importeMax']) && $_POST['importeMax'] !='') ? $_POST['importeMax'] : '';
        $this->importeMin = (isset($_POST['importeMin']) && $_POST['importeMin'] !='') ? $_POST['importeMin'] : '';
        $this->fechaMin = (isset($_POST['fechaMin']) && $_POST['fechaMin'] !='') ? $_POST['fechaMin'] : '';
        $this->fechaMax = (isset($_POST['fechaMax']) && $_POST['fechaMax'] !='') ? $_POST['fechaMax'] : '';
        $this->paginador = (isset($_POST['paginador']) && $_POST['paginador'] !='') ? $_POST['paginador'] : '';

        $this->rfc = (isset($_POST['rfc']) && $_POST['rfc'] !='') ? $_POST['rfc'] : '';

        $this->beneficiario = (isset($_POST['beneficiario']) && $_POST['beneficiario'] !='') ? $_POST['beneficiario'] : '';
        $this->concepto = (isset($_POST['concepto']) && $_POST['concepto'] !='') ? $_POST['concepto'] : '';
        $this->lote = (isset($_POST['lote']) && $_POST['lote'] !='') ? $_POST['lote'] : '';
        $this->claveBancaria = (isset($_POST['claveBancaria']) && $_POST['claveBancaria'] !='') ? $_POST['claveBancaria'] : '';
        $this->claveFinanciamiento = (isset($_POST['claveFinanciamiento']) && $_POST['claveFinanciamiento'] !='') ? $_POST['claveFinanciamiento'] : '';
        $this->loteEgreso = (isset($_POST['loteEgreso']) && $_POST['loteEgreso'] !='') ? $_POST['loteEgreso'] : '';
        $this->cuentaDestino = (isset($_POST['cuentaDestino']) && $_POST['cuentaDestino'] !='') ? $_POST['cuentaDestino'] : '';

        $this->nombreUR = (isset($_POST['nombreUR']) && $_POST['nombreUR'] !='') ? $_POST['nombreUR'] : '';
        $this->estadoClc = (isset($_POST['estadoClc']) && $_POST['estadoClc'] !='') ? $_POST['estadoClc'] : '';
        $this->estatal = (isset($_POST['estatal']) && $_POST['estatal'] !='') ? $_POST['estatal'] : ''; 

        $otrosParametros = new stdClass();
        $otrosParametros->rfc = $this->rfc;
        $otrosParametros->beneficiario = $this->beneficiario;
        $otrosParametros->concepto = $this->concepto;
        $otrosParametros->lote = $this->lote;
        $otrosParametros->claveBancaria = $this->claveBancaria;
        $otrosParametros->claveFinanciamiento = $this->claveFinanciamiento;
        $otrosParametros->loteEgreso = $this->loteEgreso;
        $otrosParametros->cuentaDestino = $this->cuentaDestino;
        $otrosParametros->nombreUR = $this->nombreUR;
        $otrosParametros->estadoClc = $this->estadoClc;
        $otrosParametros->estatal = $this->estatal;

        $this->datos = consultaClcFondo(
            $this->documento, 
            $this->ejercicio, 
            $this->clc, 
            $this->folio, 
            $this->numerour, 
            $this->importeMax, 
            $this->importeMin, 
            $this->fechaMin, 
            $this->fechaMax, 
            $this->paginador,
            $otrosParametros
        );
        
        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'SECRETARÍA DE FINANZAS TESORERÍA '.$this->ejercicio;
        $this->encabLin3 = 'TESORERÍA';
        $this->encabLin4 = 'LISTADOS DE CLCs '.$this->ejercicio;
        $this->encabLin6 = '';        
        $this->encabLin5 = '';
        $this->encabLin7 = '';

        $this->nombreArchivoReporte = 'LISTADOS DE CLCs '.$this->ejercicio;
        $this->contenido();
    }
                
    public function contenido() {
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
        
        $this->nombreColumnas = array(
            '#',
            'MODO',
            'EJERCICIO',
            'FECHA GENERACIÓN',
            'FECHA PAGO',
            'REFERENCIA DE PAGO',
            'FECHA DE AUTORIZACIÓN',
            'FECHA GENERACIÓN CERE',
            'LOTE EGRESO',
            'UR',
            'CLC',
            'CERE/CERECO',
            'BENEFICIARIO',
            'CONCEPTO',
            'IMPORTE',
            'BANCO',
            'RFC',
            'CUENTA CARGO',
            'NOMBRE CUENTA',
            'CUENTA DESTINO',
            'CLAVE FINANCIAMIENTO',
            'LOTE',
            'RESPONSABLE',
            'ESTADO',
            'TIPO'
        );
        $incluirColumnas = array(
            'contador',
            'mpc_nombre',
            'ejercicio',
            'clc_fechaElaboracion',
            'lot_fechaPago',
            'referencia',
            'bdm_fechaAutorizado',
            'clc_generacionCereCo',
            'loe_clave',
            'ur',
            'clc_id',
            'clc_cereCo',
            'clc_beneficiario',
            'clc_concepto',
            'importe',
            'iba_abreviatura',
            'rfc_id',
            'cbs_id',
            'cbs_nombre',
            'bur_id',
            'ecf_clave',
            'lote_id',
            'ur_nombre',
            'estado',
            'clc_tipoCereCo'
        );
        $tipoColumnas = array(
            'cC',
            'cI',
            'cC',
            'cC',
            'cC',
            'cC',
            'cC',
            'cC',
            'cC',
            'cC',
            'cC',
            'cC',
            'cI',
            'cI',
            'n',
            'cC',
            'cC',
            'cC',
            'cC',
            'cC',
            'cC',
            'cC',
            'cC',
            'cC',
            'cC'
        );
        $anchoColumnas = array(
            2,
            13,
            11,
            20,
            13,
            22,
            15,
            20,
            14,
            4,
            8,
            14,
            50,
            150,
            15,
            16,
            16,
            23,
            25,
            24,
            24,
            12,
            14,
            53,
            6
        );

        $table = array();
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['ancho'][$val] = $anchoColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }
        $tablaimg['imagen'] = 1;
        $tablaimg[0]['nombre'] = "Logo";
        $tablaimg[0]['ruta'] = "../../../../img/escudocolor_derecha.png";
        $tablaimg[0]['colImg'] = "K1";
        $tablaimg[0]['alingXimg'] = "10";
        $tablaimg[0]['altura'] = "75";
        $tablaimg[0]['rotacion'] = "0";
        if (!is_file($tablaimg[0]['ruta'])) {
            $tablaimg['imagen'] = 0;
            unset($tablaimg[0]);
        }
        $this->generaExcel(
            $this->datos,
            $encabezado,
            $table,
            array('nombre' => $this->nombreArchivoReporte, 'tablaimg' => $tablaimg)
        );
    }
}
$exp =new Exp_ConsultaClcFondo();