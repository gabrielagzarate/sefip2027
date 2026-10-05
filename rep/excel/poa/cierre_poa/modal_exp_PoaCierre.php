<?php

/**
 * DESCRIPTION OF modal_exp_PoaCierre
 *
 * @author Jordy Jesus Castro Avendaño <jordy.castro@finanzasoaxaca.gob.mx>
 * Create: 15-10-2019
 * Comment: ---
 * Objetive: Muestra en excel los encuadres programaticos que no estan vinculados en EP-MIR
 * Regla de Negocio: ver el ticket 693891
 */

session_start();

require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../../../lib/poa/cierre_poa/dame_Poa_Cierre.php';
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
error_reporting(0);


class Modal_exp_PoaCierre extends generaPhpspreadsheet
{
    private $ur;
    private $encabezadoLin1;
    private $encabezadoLin2;
    private $encabezadoLin3;
    private $encabezadoLin4;
    private $encabezadoLin5;
    private $encabezadoLin6;
    private $encabezadoLin7;

    function __construct()
    {
        parent::__construct();
        $this->header();
        $this->content();
    }

    private function header()
    {
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();
        $this->encabezadoLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
        $this->encabezadoLin2 = "PROGRAMA OPERATIVO ANUAL " . APP_EJERCICIO;
        $this->encabezadoLin3 = "CIERRE POA";
        $this->encabezadoLin4 = "UR: " . $this->ur->getClave() . " - " . $this->ur->getNombre();
        $this->encabezadoLin5 = "";
        $this->encabezadoLin6 = "";
        $this->encabezadoLin7 = "";
    }

    private function content()
    {
        // Estructura del encabezado del excel
        $encabezado = array(
            'encabLin1' => $this->encabezadoLin1,
            'encabLin2' => $this->encabezadoLin2,
            'encabLin3' => $this->encabezadoLin3,
            'encabLin4' => $this->encabezadoLin4,
            'encabLin5' => $this->encabezadoLin5,
            'encabLin6' => $this->encabezadoLin6,
            'encabLin7' => $this->encabezadoLin7
        );
        //Estructura de las columnas de la información
        $tabla = array();
        $nombreColumnas = array(
            "Encuadre Programático",
            "Concepto"
        );
        $incluirColumnas = array(
            "encuadre",
            "concepto"
        );
        $tipoColumnas = array("c", "c");

        foreach ($incluirColumnas as $contador => $valor) {
            $tabla['nombre'][$valor] = $nombreColumnas[$contador];
            $tabla['campo'][$valor] = $contador;
            $tabla['tipo'][$valor] = $tipoColumnas[$contador];
            $tabla['tipoTabla'] = "A";
            $tabla['ancho'][$valor] = 60;
        }

        //INFORMACIÓN PARA RELLENAR LAS COLUMNAS
        $encuadresNoVinculadosMir = new damePoaCierre();
        $datosEncuadresNoVinculadosMir = $encuadresNoVinculadosMir->obtenerEncuadresNoVinculadosMirModal($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']);

        //SE MANDA A GENERAR EL EXCELL
        $this->generaExcel($datosEncuadresNoVinculadosMir, $encabezado, $tabla, array("nombre" => $this->encabezadoLin3));
    }
}
$exp_ajustes = new Modal_exp_PoaCierre();
