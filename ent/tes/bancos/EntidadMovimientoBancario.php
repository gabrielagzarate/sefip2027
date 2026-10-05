<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class EntidadMovimientoBancario extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $mbn_id; //
    protected $mbn_ejercicio; //Ejercicio del Movimiento Bancario
    protected $mbn_importe; //Importe del Movimiento Bancario
    protected $mbn_concepto; //Concepto del Movimiento Bancario
    protected $mbn_observacion; //Observación del Movimiento Bancario
    protected $mbn_fechaMovimiento; //Fecha del Movimiento Bancario
    protected $mbn_fechaValidacion; //Fecha de Validación
    protected $mbn_fechaAutorizacion; //Fecha de Autorización
    protected $cbs_id_cargo; //ban_CuentaBancariaSefin - Llave foranea - Identificador de la CLABE de cargo
    protected $cbs_id_abono; //ban_CuentaBancariaSefin - Llave foranea - Identificador de la CLABE de abono
    protected $emb_id; //cat_EstadoMovimientoBancario - Llave foranea - Identificador de la CLABE
    protected $tmb_id; //
    protected $msc_id; //
    protected $usr_id; //Identificador de usuario que captura el registro

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getMbn_id()
    {
        return $this->mbn_id->getVal();
    }
    public function getEjercicio()
    {
        return $this->mbn_ejercicio->getVal();
    }
    public function getImporte()
    {
        return $this->mbn_importe->getVal();
    }
    public function getConcepto()
    {
        return $this->mbn_concepto->getVal();
    }
    public function getObservacion()
    {
        return $this->mbn_observacion->getVal();
    }
    public function getFechaMovimiento()
    {
        return $this->mbn_fechaMovimiento->getVal();
    }
    public function getFechaValidacion()
    {
        return $this->mbn_fechaValidacion->getVal();
    }
    public function getFechaAutorizacion()
    {
        return $this->mbn_fechaAutorizacion->getVal();
    }
    public function getCbs_id_cargo()
    {
        return $this->cbs_id_cargo->getVal();
    }
    public function getCbs_id_abono()
    {
        return $this->cbs_id_abono->getVal();
    }
    public function getEmb_id()
    {
        return $this->emb_id->getVal();
    }
    public function getTmb_id()
    {
        return $this->tmb_id->getVal();
    }
    public function getMsc_id()
    {
        return $this->msc_id->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setMbn_id($val)
    {
        $this->mbn_id->setVal($val);
    }
    public function setEjercicio($val)
    {
        $this->mbn_ejercicio->setVal($val);
    }
    public function setImporte($val)
    {
        $this->mbn_importe->setVal($val);
    }
    public function setConcepto($val)
    {
        $this->mbn_concepto->setVal($val);
    }
    public function setObservacion($val)
    {
        $this->mbn_observacion->setVal($val);
    }
    public function setFechaMovimiento($val)
    {
        $this->mbn_fechaMovimiento->setVal($val);
    }
    public function setFechaValidacion($val)
    {
        $this->mbn_fechaValidacion->setVal($val);
    }
    public function setFechaAutorizacion($val)
    {
        $this->mbn_fechaAutorizacion->setVal($val);
    }
    public function setCbs_id_cargo($val)
    {
        $this->cbs_id_cargo->setVal($val);
    }
    public function setCbs_id_abono($val)
    {
        $this->cbs_id_abono->setVal($val);
    }
    public function setEmb_id($val)
    {
        $this->emb_id->setVal($val);
    }
    public function setTmb_id($val)
    {
        $this->tmb_id->setVal($val);
    }
    public function setMsc_id($val)
    {
        $this->msc_id->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staMbn_id($val)
    {
        $this->mbn_id->staVal($val);
    }
    public function staEjercicio($val)
    {
        $this->mbn_ejercicio->staVal($val);
    }
    public function staImporte($val)
    {
        $this->mbn_importe->staVal($val);
    }
    public function staConcepto($val)
    {
        $this->mbn_concepto->staVal($val);
    }
    public function staObservacion($val)
    {
        $this->mbn_observacion->staVal($val);
    }
    public function staFechaMovimiento($val)
    {
        $this->mbn_fechaMovimiento->staVal($val);
    }
    public function staFechaValidacion($val)
    {
        $this->mbn_fechaValidacion->staVal($val);
    }
    public function staFechaAutorizacion($val)
    {
        $this->mbn_fechaAutorizacion->staVal($val);
    }
    public function staCbs_id_cargo($val)
    {
        $this->cbs_id_cargo->staVal($val);
    }
    public function staCbs_id_abono($val)
    {
        $this->cbs_id_abono->staVal($val);
    }
    public function staEmb_id($val)
    {
        $this->emb_id->staVal($val);
    }
    public function staTmb_id($val)
    {
        $this->tmb_id->staVal($val);
    }
    public function staMsc_id($val)
    {
        $this->msc_id->staVal($val);
    }
    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(array("tabla" => BD_TESORERIA.".ban_MovimientoBancario"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->mbn_id = new Atributo("numero", "NO", "PRI", "mbn_id");
        $this->mbn_ejercicio = new Atributo("numero", "NO", "MUL", "mbn_ejercicio");
        $this->mbn_importe = new Atributo("numero", "NO", "", "mbn_importe");
        $this->mbn_concepto = new Atributo("cadena", "NO", "", "mbn_concepto");
        $this->mbn_observacion = new Atributo("cadena", "NO", "", "mbn_observacion");
        $this->mbn_fechaMovimiento = new Atributo("fecha", "NO", "", "mbn_fechaMovimiento");
        $this->mbn_fechaValidacion = new Atributo("fecha", "YES", "", "mbn_fechaValidacion");
        $this->mbn_fechaAutorizacion = new Atributo("fecha", "YES", "", "mbn_fechaAutorizacion");
        $this->cbs_id_cargo = new Atributo("numero", "NO", "MUL", "cbs_id_cargo");
        $this->cbs_id_abono = new Atributo("numero", "NO", "MUL", "cbs_id_abono");
        $this->emb_id = new Atributo("numero", "NO", "MUL", "emb_id");
        $this->tmb_id = new Atributo("numero", "NO", "MUL", "tmb_id");
        $this->msc_id = new Atributo("numero", "NO", "MUL", "msc_id");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $mbn = new EntidadMovimientoBancario();
        $bit_observacion = '';

        if (isset($p->mbn_id)) {
            $mbn->setMbn_id($p->mbn_id);
            $bit_observacion .= 'mbn_id: ' . $p->mbn_id;
        }
        if (isset($p->ejercicio)) {
            $mbn->setejercicio($p->ejercicio);
            $bit_observacion .= ' ejercicio: ' . $p->ejercicio;
        }
        if (isset($p->importe)) {
            $mbn->setImporte($p->importe);
            $bit_observacion .= ' importe: ' . $p->importe;
        }
        if (isset($p->concepto)) {
            $mbn->setConcepto($p->concepto);
            $bit_observacion .= ' concepto: ' . $p->concepto;
        }
        if (isset($p->observacion)) {
            $mbn->setObservacion($p->observacion);
            $bit_observacion .= ' observacion: ' . $p->observacion;
        }
        if (isset($p->fechaMovimiento)) {
            $mbn->setFechaMovimiento($p->fechaMovimiento);
            $bit_observacion .= ' fechaMovimiento: ' . $p->fechaMovimiento;
        }
        if (isset($p->fechaValidacion)) {
            $mbn->setFechaValidacion($p->fechaValidacion);
            $bit_observacion .= ' fechaValidacion: ' . $p->fechaValidacion;
        }
        if (isset($p->fechaAutorizacion)) {
            $mbn->setFechaAutorizacion($p->fechaAutorizacion);
            $bit_observacion .= ' fechaAutorizacion: ' . $p->fechaAutorizacion;
        }
        if (isset($p->cbs_id_cargo)) {
            $mbn->setCbs_id_cargo($p->cbs_id_cargo);
            $bit_observacion .= ' cbs_id_cargo: ' . $p->cbs_id_cargo;
        }
        if (isset($p->cbs_id_abono)) {
            $mbn->setCbs_id_abono($p->cbs_id_abono);
            $bit_observacion .= ' cbs_id_abono: ' . $p->cbs_id_abono;
        }
        if (isset($p->emb_id)) {
            $mbn->setEmb_id($p->emb_id);
            $bit_observacion .= ' emb_id: ' . $p->emb_id;
        }
        if (isset($p->tmb_id)) {
            $mbn->setTmb_id($p->tmb_id);
            $bit_observacion .= ' tmb_id: ' . $p->tmb_id;
        }
        if (isset($p->msc_id)) {
            $mbn->setMsc_id($p->msc_id);
            $bit_observacion .= ' msc_id: ' . $p->msc_id;
        }
        if (isset($p->usr_id)) {
            $mbn->setUsr_id($p->usr_id);
            $bit_observacion .= ' usr_id: ' . $p->usr_id;
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a":
                $mbn->conectar();
                $bit_observacion = ' REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $mbn->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $mbn->consultar($params);
                $mbn->insert(false);
                return $mbn->getUltimoId();
                break;
            case "b":
                $mbn->conectar();
                $bit_observacion = ' REGISTRO BORRADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $mbn->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $mbn->consultar($params);
                $mbn->delete(false);
                break;
            case "c":
                $mbn->conectar();
                $bit_observacion = ' REGISTRO ACTUALIZADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $mbn->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $mbn->consultar($params);
                $mbn->update(false);
                break;
        }
    }
}
