<?php

/**
 * Description of Exp_reporteDetalladoClcDeudaPublica
 *
 * @author Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Collaborators:
 * Created: 06-03-2025
 * Comment: -----
 * Objective: Generar un archivo Excel del detalle de las CLCs de Deuda Publica.
 * Regla de Negocio: -----
 * Permits: Esta pantalla no pregunta por permisos de lectura ni de escritura.
 * */

session_start();
require_once dirname(__FILE__) . '/../../../../rep/lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/scp/reportes/consultaDetalleClcDeudaPublica.php';

class Exp_reporteDetalladoClcDeudaPublica extends generaPhpspreadsheet
{
    public $tipo;
    public $fechaMin;
    public $fechaMax;
    public string |	null $encabLin1;
    public string | null $encabLin2;
    public string | null $encabLin3;
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
        $this->tipo = (isset($_POST['tipo']) && $_POST['tipo'] != '') ? $_POST['tipo'] : 'J';
        $this->fechaMin = (isset($_POST['inicio']) && $_POST['inicio'] != '') ? $_POST['inicio'] : '';
        $this->fechaMax = (isset($_POST['termino']) && $_POST['termino'] != '') ? $_POST['termino'] : '';

        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'TESORERÍA ' . APP_EJERCICIO;
        $this->encabLin3 = 'SECRETARÍA DE FINANZAS';
        $this->encabLin4 = 'REPORTE DETALLADO DE CLCS DE DEUDA PÚBLICA DE LA FECHA ' . $this->fechaMin . ' A LA ' . $this->fechaMax;
        $this->encabLin5 = '';
        $this->encabLin6 = '';

        $this->nombreArchivoReporte = 'DEUDA PUBLICA ' . APP_EJERCICIO . ' DE LA FECHA ' . $this->fechaMin . ' A LA ' . $this->fechaMax;

        $this->datos = consultaDetalleClcDeudaPublica($this->tipo, $this->fechaMin, $this->fechaMax);

        $this->contenido();
    }

    public function contenido()
    {
        $encabezado = [
            'encabLin1'     => $this->encabLin1,
            'encabLin2'     => $this->encabLin2,
            'encabLin3'     => $this->encabLin3,
            'encabLin4'     => $this->encabLin4,
            'encabLin5'     => $this->encabLin5,
            'encabLin6'     => $this->encabLin6,
            'encabLin7'     => $this->encabLin7,
            'alineacionenc' => 'I',
        ];

        $this->nombreColumnas = ['UR', 'UR NOMBRE', 'CLC', 'AFECTACION', 'NUMERO DE CONTROL', 'CERECO', 'CLC CERECO', 'MODO DE PAGO', 'CLC CONCEPTO', 'PARTIDA', 'PARTIDA GENERICA', 'PARTIDA ESPECIFICA', 'CLAVE FINANCIAMIENTO', 'REFERENCIA', 'CLC FECHA DE ELABORACION', 'RFC', 'NOMBRE', 'RECEPTORA', 'ORDENANTE', 'PERCEPCION BRUTO', 'PERCEPCION IVA', 'PERCEPCION NETO', 'RETENCION NETO'];
        $incluirColumnas = ['ur', 'ur_nombre', 'clc', 'afectacion', 'numcontrol', 'cereco', 'clc_cereCo', 'modo_pago', 'clc_concepto', 'partida', 'partida_generica', 'partida_especifica', 'clave_financiamiento', 'referencia', 'clc_fecha_elaboracion', 'rfc_id', 'nombre', 'receptora', 'ordenante', 'percepcion_bruto', 'percepcion_iva', 'percepcion_neto', 'retencion_neto'];
        $tipoColumnas = ['cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'cC', 'f', 'cC', 'cC', 'cC', 'cC', 'n', 'n', 'n', 'n'];
        $anchoColumnas = [5, 35, 20, 20, 20, 20, 20, 20, 60, 10, 35, 35, 20, 50, 20, 20, 40, 20, 20, 20, 20, 20, 20];

        $table = [];
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $this->nombreColumnas[$key];
            $table['ancho'][$val] = $anchoColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }
        $name = 114;
        $path_image = dirname(__FILE__) . '/../../../../img/Logo Institucional/' . $name . '.jpg';
        $tablaImagen = ['imagen' => 1];
        $tablaImagen[0] = ['ruta' => $path_image, 'nombre' => 'XXX', 'colImg' => 'H1', 'altura' => 100, 'alingXimg' => 0];
        $this->generaExcel($this->datos, $encabezado, $table, ['nombre' => $this->nombreArchivoReporte, 'tablaimg' => $tablaImagen]);
    }
}
$exp = new Exp_reporteDetalladoClcDeudaPublica();
