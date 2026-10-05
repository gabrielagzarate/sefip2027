<?php
require_once dirname(__FILE__) . "/Entidad.php";

class Minuta extends Entidad
{
    /****************************************************** 
        ATRIBUTOS DE LA CLASE
     ******************************************************/

    protected $min_id;
    protected $gpo_id;
    protected $ur_id;
    protected $min_entrega;
    protected $min_recibe;
    protected $min_fecha;
    protected $min_hora;
    protected $min_formato;
    protected $min_observacion;
    protected $usr_id;

    /****************************************************** 
        METODOS GET
     ******************************************************/

    public function getMin_id()
    {
        return $this->min_id->getVal();
    }
    public function getGpo_id()
    {
        return $this->gpo_id->getVal();
    }
    public function getUr_id()
    {
        return $this->ur_id->getVal();
    }
    public function getEntrega()
    {
        return $this->min_entrega->getVal();
    }
    public function getRecibe()
    {
        return $this->min_recibe->getVal();
    }
    public function getFecha()
    {
        return $this->min_fecha->getVal();
    }
    public function getHora()
    {
        return $this->min_hora->getVal();
    }
    public function getFormato()
    {
        return $this->min_formato->getVal();
    }
    public function getObservacion()
    {
        return $this->min_observacion->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /****************************************************** 
        METODOS SET
     ******************************************************/

    public function setMin_id($val)
    {
        $this->min_id->setVal($val);
    }
    public function setGpo_id($val)
    {
        $this->gpo_id->setVal($val);
    }
    public function setUr_id($val)
    {
        $this->ur_id->setVal($val);
    }
    public function setEntrega($val)
    {
        $this->min_entrega->setVal($val);
    }
    public function setRecibe($val)
    {
        $this->min_recibe->setVal($val);
    }
    public function setFecha($val)
    {
        $this->min_fecha->setVal($val);
    }
    public function setHora($val)
    {
        $this->min_hora->setVal($val);
    }
    public function setFormato($val)
    {
        $this->min_formato->setVal($val);
    }
    public function setObservacion($val)
    {
        $this->min_observacion->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /****************************************************** 
        METODOS STA (SET sin persistencia)
     ******************************************************/

    public function staMin_id($val)
    {
        $this->min_id->staVal($val);
    }
    public function staGpo_id($val)
    {
        $this->gpo_id->staVal($val);
    }
    public function staUr_id($val)
    {
        $this->ur_id->staVal($val);
    }
    public function staEntrega($val)
    {
        $this->min_entrega->staVal($val);
    }
    public function staRecibe($val)
    {
        $this->min_recibe->staVal($val);
    }
    public function staFecha($val)
    {
        $this->min_fecha->staVal($val);
    }
    public function staHora($val)
    {
        $this->min_hora->staVal($val);
    }
    public function staFormato($val)
    {
        $this->min_formato->staVal($val);
    }
    public function staObservacion($val)
    {
        $this->min_observacion->staVal($val);
    }
    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }

    /****************************************************** 
        CONSTRUCTOR
     ******************************************************/
    public function  __construct()
    {
        parent::__construct(array("tabla" => "poa_Minuta"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->min_id = new Atributo("numero", "NO", "PRI", "min_id");
        $this->gpo_id = new Atributo("numero", "NO", "MUL", "gpo_id");
        $this->ur_id = new Atributo("numero", "NO", "", "ur_id");
        $this->min_entrega = new Atributo("cadena", "NO", "", "min_entrega");
        $this->min_recibe = new Atributo("cadena", "NO", "", "min_recibe");
        $this->min_fecha = new Atributo("fecha", "NO", "", "min_fecha");
        $this->min_hora = new Atributo("fecha", "NO", "", "min_hora");
        $this->min_formato = new Atributo("cadena", "NO", "", "min_formato");
        $this->min_observacion = new Atributo("cadena", "NO", "", "min_observacion");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");
    }

    /****************************************************** 
        FUNCIONES ESTÁTICAS
     ******************************************************/

    public static function abc($p)
    {
        $min =  new Minuta();
        if (isset($p->min_id)) {
            $min->setMin_id($p->min_id);
        }
        if (isset($p->gpo_id)) {
            $min->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $min->setUr_id($p->ur_id);
        }
        if (isset($p->min_entrega)) {
            $min->setEntrega($p->min_entrega);
        }
        if (isset($p->min_recibe)) {
            $min->setRecibe($p->min_recibe);
        }
        if (isset($p->min_fecha)) {
            $min->setFecha($p->min_fecha);
        }
        if (isset($p->min_hora)) {
            $min->setHora($p->min_hora);
        }
        if (isset($p->min_formato)) {
            $min->setFormato($p->min_formato);
        }
        if (isset($p->min_observacion)) {
            $min->setObservacion($p->min_observacion);
        }
        if (isset($p->usr_id)) {
            $min->setUsr_id($p->usr_id);
        }
        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != "::1") ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
        $bit_observacion = '';
        $bit_usr_id = $p->usr_id;

        switch ($p->abc) {
            case "a":
                $bit_observacion = 'REGISTRO INSERTADO DESDE PANTALLA';
                $min->setSql(' SET @usr_ip =INET_ATON( ? ), @usr_id= ? , @bit_observacion = ?, @min_observacion = ?;');
                $params = [$ip, $bit_usr_id, $bit_observacion, $bit_observacion];
                $min->consultar($params);
                $min->insert(false);
                return $min->getUltimoId();
                break;
            case "b":
                $min->delete();
                break;
            case "c":
                $min->update();
                break;
        }
    }
}
