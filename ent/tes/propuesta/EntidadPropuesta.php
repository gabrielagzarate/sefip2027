<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class EntidadPropuesta extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/
    protected $pro_id;
    protected $pro_ejercicio;
    protected $pro_fechaGeneracion;
    protected $pro_fechaCancelacion;
    protected $pro_fechaAutorizacion;
    protected $pro_flujoEfectivo;
    protected $tlo_id;
    protected $msc_id;
    protected $elo_id;
    protected $usr_id;
    protected $usr_fecha;

    /******************************************************

    MÉTODOS GET

     ******************************************************/
    public function getPro_id()
    {
        return $this->pro_id->getVal();
    }
    public function getPro_ejercicio()
    {
        return $this->pro_ejercicio->getVal();
    }
    public function getPro_fechaGeneracion()
    {
        return $this->pro_fechaGeneracion->getVal();
    }
    public function getPro_fechaCancelacion()
    {
        return $this->pro_fechaCancelacion->getVal();
    }
    public function getPro_fechaAutorizacion()
    {
        return $this->pro_fechaAutorizacion->getVal();
    }
    public function getPro_flujoEfectivo()
    {
        return $this->pro_flujoEfectivo->getVal();
    }
    public function getTlo_ip()
    {
        return $this->tlo_id->getVal();
    }
    public function getMsc_id()
    {
        return $this->msc_id->getVal();
    }
    public function getElo_id()
    {
        return $this->elo_id->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    public function getUsr_fecha()
    {
        return $this->usr_fecha->getVal();
    }

    /******************************************************

    MÉTODOS SET

     ******************************************************/
    public function setPro_id($val)
    {
        $this->pro_id->setVal($val);
    }
    public function setPro_ejercicio($val)
    {
        $this->pro_ejercicio->setVal($val);
    }
    public function setPro_fechaGeneracion($val)
    {
        $this->pro_fechaGeneracion->setVal($val);
    }
    public function setPro_fechaCancelacion($val)
    {
        $this->pro_fechaCancelacion->setVal($val);
    }
    public function setPro_fechaAutorizacion($val)
    {
        $this->pro_fechaAutorizacion->setVal($val);
    }
    public function setPro_flujoEfectivo($val)
    {
        $this->pro_flujoEfectivo->setVal($val);
    }
    public function setTlo_id($val)
    {
        $this->tlo_id->setVal($val);
    }
    public function setMsc_id($val)
    {
        $this->msc_id->setVal($val);
    }
    public function setElo_id($val)
    {
        $this->elo_id->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    public function setUsr_fecha($val)
    {
        $this->usr_fecha->setVal($val);
    }

    /******************************************************

    MÉTODOS STA (SET sin persistencia)

     ******************************************************/
    public function staPro_id($val)
    {
        $this->pro_id->staVal($val);
    }
    public function staPro_ejercicio($val)
    {
        $this->pro_ejercicio->staVal($val);
    }
    public function staPro_fechaGeneracion($val)
    {
        $this->pro_fechaGeneracion->staVal($val);
    }
    public function staPro_fechaCancelacion($val)
    {
        $this->pro_fechaCancelacion->staVal($val);
    }
    public function staPro_fechaAutorizacion($val)
    {
        $this->pro_fechaAutorizacion->staVal($val);
    }
    public function stapro_flujoEfectivo($val)
    {
        $this->pro_flujoEfectivo->staVal($val);
    }
    public function staTlo_id($val)
    {
        $this->tlo_id->staVal($val);
    }
    public function staMsc_id($val)
    {
        $this->msc_id->staVal($val);
    }
    public function staElo_id($val)
    {
        $this->elo_id->staVal($val);
    }
    public function staUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    public function staUsr_fecha($val)
    {
        $this->usr_fecha->setVal($val);
    }

    /******************************************************

    CONSTRUCTOR

     ******************************************************/
    public function __construct()
    {

        parent::__construct(array("tabla" => BD_TESORERIA.".pro_Propuesta"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->pro_id = new Atributo("numero", "NO", "PRI", "pro_id");
        $this->pro_ejercicio = new Atributo("numero", "NO", "", "pro_ejercicio");
        $this->pro_fechaGeneracion = new Atributo("fecha", "NO", "", "pro_fechaGeneracion");
        $this->pro_fechaCancelacion = new Atributo("fecha", "NO", "", "pro_fechaCancelacion");
        $this->pro_fechaAutorizacion = new Atributo("fecha", "NO", "", "pro_fechaAutorizacion");
        $this->pro_flujoEfectivo = new Atributo("numero", "NO", "", "pro_flujoEfectivo");
        $this->tlo_id = new Atributo("numero", "NO", "", "tlo_id");
        $this->msc_id = new Atributo("numero", "NO", "", "msc_id");
        $this->elo_id = new Atributo("numero", "NO", "", "elo_id");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");
        $this->usr_fecha = new Atributo("fecha", "NO", "", "usr_fecha");
    }

    public static function abc($p)
    {
        $propM = new EntidadPropuesta();

        $bit_observacion = '';

        if (isset($p->pro_id)) {
            $propM->setPro_id($p->pro_id);
            $bit_observacion .= 'pro_id: ' . $p->pro_id;
        }
        if (isset($p->pro_ejercicio)) {
            $propM->setPro_ejercicio($p->pro_ejercicio);
            $bit_observacion .= ' pro_ejercicio: ' . $p->pro_ejercicio;
        }
        if (isset($p->pro_fechaGeneracion)) {
            $propM->setPro_fechaGeneracion($p->pro_fechaGeneracion);
            $bit_observacion .= ' pro_fechaGeneracion: ' . $p->pro_fechaGeneracion;
        }
        if (isset($p->pro_fechaCancelacion)) {
            $propM->setPro_fechaCancelacion($p->pro_fechaCancelacion);
            $bit_observacion .= ' pro_fechaCancelacion: ' . $p->pro_fechaCancelacion;
        }
        if (isset($p->pro_fechaAutorizacion)) {
            $propM->setPro_fechaAutorizacion($p->pro_fechaAutorizacion);
            $bit_observacion .= ' pro_fechaAutorizacion: ' . $p->pro_fechaAutorizacion;
        }
        if (isset($p->pro_flujoEfectivo)) {
            $propM->setPro_flujoEfectivo($p->pro_flujoEfectivo);
            $bit_observacion .= ' pro_flujoEfectivo: ' . $p->pro_flujoEfectivo;
        }
        if (isset($p->tlo_id)) {
            $propM->setTlo_id($p->tlo_id);
            $bit_observacion .= ' tlo_id: ' . $p->tlo_id;
        }
        if (isset($p->msc_id)) {
            $propM->setMsc_id($p->msc_id);
            $bit_observacion .= ' msc_id: ' . $p->msc_id;
        }
        if (isset($p->elo_id)) {
            $propM->setElo_id($p->elo_id);
            $bit_observacion .= ' elo_id: ' . $p->elo_id;
        }
        if (isset($p->usr_id)) {
            $propM->setUsr_id($p->usr_id);
            $bit_observacion .= ' usr_id: ' . $p->usr_id;
        }
        if (isset($p->usr_fecha)) {
            $propM->setUsr_fecha($p->usr_fecha);
            $bit_observacion .= ' usr_fecha: ' . $p->usr_fecha;
        }
        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a":
                $propM->conectar();
                $bit_observacion = ' REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $propM->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $propM->consultar($params);
                $propM->insert(false);
                return $propM->getUltimoId();
                break;
            case "b":
                $propM->conectar();
                $bit_observacion = 'REGISTRO BORRADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $propM->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $propM->consultar($params);
                $propM->delete(false);
                break;
            case "c":
                $propM->conectar();
                $bit_observacion = 'REGISTRO ACTUALIZADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $propM->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $propM->consultar($params);
                $propM->update(false);
                break;
        }
    }
}
