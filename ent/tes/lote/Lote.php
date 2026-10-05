<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class Lote extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $fpa_id; //cat_FormaPago - Llave Foranea - Identificador de la Forma de Pago
    protected $msc_id; //cat_Mes - Llave Foranea - Identificador del Mes
    protected $lot_ejercicio; //Ejercicio de Sinpres o del documento a ministrar
    protected $lot_id; //Identificador de Lote
    protected $lot_ejr; //Identificador del año del ejercicio
    protected $lot_fechaGeneracion; //Fecha de Generación del Lote
    protected $lot_fechaAutorizacion; //
    protected $lot_fechaCancelacion; //Fecha de Cancelación del Lote
    protected $lot_fechaPago; //Fecha de Pago del Lote
    protected $lot_importe; //Importe total del Lote
    protected $lot_tipoCereCo; //1.- CERE|2.- CERECO
    protected $lot_flujoEfectivo; //1.- CON|2.- SIN
    protected $elo_id; //cat_EstadoLote - Llave Foranea - Identificador del Estado del Lote
    protected $tlo_id; //cat_TipoLote - Llave Foranea - Identificador del Tipo de Lote
    protected $usr_id; //Identificador de usuario que captura el registro

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getFpa_id()
    {
        return $this->fpa_id->getVal();
    }
    public function getMsc_id()
    {
        return $this->msc_id->getVal();
    }
    public function getEjercicio()
    {
        return $this->lot_ejercicio->getVal();
    }
    public function getLot_id()
    {
        return $this->lot_id->getVal();
    }
    public function getLot_ejr()
    {
        return $this->lot_ejr->getVal();
    }
    public function getFechaGeneracion()
    {
        return $this->lot_fechaGeneracion->getVal();
    }
    public function getFechaAutorizacion()
    {
        return $this->lot_fechaAutorizacion->getVal();
    }
    public function getFechaCancelacion()
    {
        return $this->lot_fechaCancelacion->getVal();
    }
    public function getFechaPago()
    {
        return $this->lot_fechaPago->getVal();
    }
    public function getImporte()
    {
        return $this->lot_importe->getVal();
    }
    public function getTipoCereCo()
    {
        return $this->lot_tipoCereCo->getVal();
    }
    public function getFlujoEfectivo()
    {
        return $this->lot_flujoEfectivo->getVal();
    }
    public function getElo_id()
    {
        return $this->elo_id->getVal();
    }
    public function getTlo_id()
    {
        return $this->tlo_id->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setFpa_id($val)
    {
        $this->fpa_id->setVal($val);
    }
    public function setMsc_id($val)
    {
        $this->msc_id->setVal($val);
    }
    public function setEjercicio($val)
    {
        $this->lot_ejercicio->setVal($val);
    }
    public function setLot_id($val)
    {
        $this->lot_id->setVal($val);
    }
    public function setLot_ejr($val)
    {
        $this->lot_ejr->setVal($val);
    }
    public function setFechaGeneracion($val)
    {
        $this->lot_fechaGeneracion->setVal($val);
    }
    public function setFechaAutorizacion($val)
    {
        $this->lot_fechaAutorizacion->setVal($val);
    }
    public function setFechaCancelacion($val)
    {
        $this->lot_fechaCancelacion->setVal($val);
    }
    public function setFechaPago($val)
    {
        $this->lot_fechaPago->setVal($val);
    }
    public function setImporte($val)
    {
        $this->lot_importe->setVal($val);
    }
    public function setTipoCereCo($val)
    {
        $this->lot_tipoCereCo->setVal($val);
    }
    public function setFlujoEfectivo($val)
    {
        $this->lot_flujoEfectivo->setVal($val);
    }
    public function setElo_id($val)
    {
        $this->elo_id->setVal($val);
    }
    public function setTlo_id($val)
    {
        $this->tlo_id->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staFpa_id($val)
    {
        $this->fpa_id->staVal($val);
    }
    public function staMsc_id($val)
    {
        $this->msc_id->staVal($val);
    }
    public function staEjercicio($val)
    {
        $this->lot_ejercicio->staVal($val);
    }
    public function staLot_id($val)
    {
        $this->lot_id->staVal($val);
    }
    public function staLot_ejr($val)
    {
        $this->lot_ejr->staVal($val);
    }
    public function staFechaGeneracion($val)
    {
        $this->lot_fechaGeneracion->staVal($val);
    }
    public function staFechaAutorizacion($val)
    {
        $this->lot_fechaAutorizacion->staVal($val);
    }
    public function staFechaCancelacion($val)
    {
        $this->lot_fechaCancelacion->staVal($val);
    }
    public function staFechaPago($val)
    {
        $this->lot_fechaPago->staVal($val);
    }
    public function staImporte($val)
    {
        $this->lot_importe->staVal($val);
    }
    public function staTipoCereCo($val)
    {
        $this->lot_tipoCereCo->staVal($val);
    }
    public function staFlujoEfectivo($val)
    {
        $this->lot_flujoEfectivo->staVal($val);
    }
    public function staElo_id($val)
    {
        $this->elo_id->staVal($val);
    }
    public function staTlo_id($val)
    {
        $this->tlo_id->staVal($val);
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

        parent::__construct(array("tabla" => BD_TESORERIA.".lot_Lote"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->fpa_id = new Atributo("numero", "NO", "PRI", "fpa_id");
        $this->msc_id = new Atributo("numero", "NO", "PRI", "msc_id");
        $this->lot_ejercicio = new Atributo("numero", "NO", "PRI", "lot_ejercicio");
        $this->lot_id = new Atributo("numero", "NO", "PRI", "lot_id");
        $this->lot_ejr = new Atributo("numero", "NO", "", "lot_ejr");
        $this->lot_fechaGeneracion = new Atributo("fecha", "NO", "", "lot_fechaGeneracion");
        $this->lot_fechaAutorizacion = new Atributo("fecha", "YES", "", "lot_fechaAutorizacion");
        $this->lot_fechaCancelacion = new Atributo("fecha", "YES", "", "lot_fechaCancelacion");
        $this->lot_fechaPago = new Atributo("fecha", "YES", "", "lot_fechaPago");
        $this->lot_importe = new Atributo("numero", "NO", "", "lot_importe");
        $this->lot_tipoCereCo = new Atributo("numero", "NO", "", "lot_tipoCereCo");
        $this->lot_flujoEfectivo = new Atributo("numero", "NO", "", "lot_flujoEfectivo");
        $this->elo_id = new Atributo("numero", "NO", "MUL", "elo_id");
        $this->tlo_id = new Atributo("numero", "NO", "MUL", "tlo_id");
        $this->usr_id = new Atributo("numero", "NO", "MUL", "usr_id");
    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $bit_observacion = "";
        $lot = new Lote();
        if (isset($p->fpa_id)) {
            $lot->setFpa_id($p->fpa_id);
            $bit_observacion .= ' fpa_id: ' . $p->fpa_id;
        }
        if (isset($p->msc_id)) {
            $lot->setMsc_id($p->msc_id);
            $bit_observacion .= ' msc_id: ' . $p->msc_id;
        }
        if (isset($p->ejercicio)) {
            $lot->setEjercicio($p->ejercicio);
            $bit_observacion .= ' ejercicio: ' . $p->ejercicio;
        }
        if (isset($p->lot_id)) {
            $lot->setLot_id($p->lot_id);
            $bit_observacion .= ' lot_id: ' . $p->lot_id;
        }
        if (isset($p->lot_ejr)) {
            $lot->setLot_ejr($p->lot_ejr);
            $bit_observacion .= ' lot_ejr: ' . $p->lot_ejr;
        }
        if (isset($p->fechaGeneracion)) {
            $lot->setFechaGeneracion($p->fechaGeneracion);
            $bit_observacion .= ' fechaGeneracion: ' . $p->fechaGeneracion;
        }
        if (isset($p->fechaAutorizacion)) {
            $lot->setFechaAutorizacion($p->fechaAutorizacion);
            $bit_observacion .= ' fechaAutorizacion: ' . $p->fechaAutorizacion;
        }
        if (isset($p->fechaCancelacion)) {
            $lot->setFechaCancelacion($p->fechaCancelacion);
            $bit_observacion .= ' fechaCancelacion: ' . $p->fechaCancelacion;
        }
        if (isset($p->fechaPago)) {
            $lot->setFechaPago($p->fechaPago);
            $bit_observacion .= ' fechaPago: ' . $p->fechaPago;
        }
        if (isset($p->importe)) {
            $lot->setImporte($p->importe);
            $bit_observacion .= ' importe: ' . $p->importe;
        }
        if (isset($p->tipoCereCo)) {
            $lot->setTipoCereCo($p->tipoCereCo);
            $bit_observacion .= ' tipoCereCo: ' . $p->tipoCereCo;
        }
        if (isset($p->flujoEfectivo)) {
            $lot->setFlujoEfectivo($p->flujoEfectivo);
            $bit_observacion .= ' flujoEfectivo: ' . $p->flujoEfectivo;
        }
        if (isset($p->elo_id)) {
            $lot->setElo_id($p->elo_id);
            $bit_observacion .= ' elo_id: ' . $p->elo_id;
        }
        if (isset($p->tlo_id)) {
            $lot->setTlo_id($p->tlo_id);
            $bit_observacion .= ' tlo_id: ' . $p->tlo_id;
        }
        if (isset($p->usr_id)) {
            $lot->setUsr_id($p->usr_id);
            $bit_observacion .= ' usr_id: ' . $p->usr_id;
        }

        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a":
                $lot->conectar();
                $bit_observacion = 'REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->insert(false);
                return $lot->getUltimoId();
                break;
            case "b":
                $lot->conectar();
                $bit_observacion = 'REGISTRO ELIMINADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->delete(false);
                break;
            case "c":
                $lot->conectar();
                $bit_observacion = 'REGISTRO MODIFICADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->update(false);
                break;
        }
    }
}
