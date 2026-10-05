<?php

// En algunas pantallas tengo que llamar a una función, pero pasándole los parámetros estilo comunica
// entonces creé éste controlador genérico que llamará a funciones genéricas, pero que podrá ser llamado con el método
// comunica() estándar.

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__)."/constantes.php";
require_once dirname(__FILE__).'/funciones.php';
require_once dirname(__FILE__).'/WebResponse.php';


class ControladorFunciones extends WebResponse
{
    public function __construct()
    {
        parent::__construct();
        switch ($this->metodo) {
            // POA: Mostrar el techo disponible para una cédula de costeo
            case 'muestraDisponible': 
                $this->muestraDisponible();
                break;
            default:
                break;
        }
        $this->returnJson();
    }
    
    // Mostramos el techo presupuestal disponible de el encuadre + pg + pe actual
    private function muestraDisponible()
    {
        // Verificamos que existan las variables necesarias
        // Si un valor necesario está vacío, vaciamos también el saldo disponible
        
        if (!isset($this->prm->pg) || $this->prm->pg == "") {
            $this->mixScript(' $("#saldoDisponible").val(""); ');
            return false;
        }
        if (!isset( $this->prm->pe ) || $this->prm->pe == "") {
            $this->mixScript(' $("#saldoDisponible").val(""); ');
            return false;
        }
        
        // Obtenemos el saldo disponible
        if (isset( $_SESSION[_S]['cvefin']->ecf)) {
            $textoAMostrar = DameSaldoDisponibleCosteo( $_SESSION[_S]['cvefin']->ecf, $this->prm->pg, $this->prm->pe);
            
            // Lo mostramos
            $this->mixScript(' $("#saldoDisponible").val("' . $textoAMostrar . '"); ');
        }
    }
}

new ControladorFunciones( );
