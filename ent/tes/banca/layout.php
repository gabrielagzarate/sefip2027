<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class Layout extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $lay_id; //Identificador del Layout
    protected $lay_fechaSistema; //Fecha de generación del Layout
    protected $lay_anioSinpres; //Ejercicio del sinpres
    protected $lay_propuesta; //
    protected $lay_archivo; //
    protected $lay_password; //
    protected $lay_observacion; //Comentarios del Layout generado
    protected $tla_id; //cat_TipoLayout - Llave foranea - Identificador del Tipo de Layout
    protected $ela_id; //cat_EstadoLayout - Llave foranea - Identificador del Estado del Layout
    protected $usr_id; //Identificador de usuario que captura/actualiza el registro
    protected $iba_id; //Identificaor del banco llave foranea institucion bancaria

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getLay_id()
    {
        return $this->lay_id->getVal();
    }
    public function getFechaSistema()
    {
        return $this->lay_fechaSistema->getVal();
    }
    public function getAnioSinpres()
    {
        return $this->lay_anioSinpres->getVal();
    }
    public function getPropuesta()
    {
        return $this->lay_propuesta->getVal();
    }
    public function getArchivo()
    {
        return $this->lay_archivo->getVal();
    }
    public function getPassword()
    {
        return $this->lay_password->getVal();
    }
    public function getObservacion()
    {
        return $this->lay_observacion->getVal();
    }
    public function getTla_id()
    {
        return $this->tla_id->getVal();
    }
    public function getEla_id()
    {
        return $this->ela_id->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    public function getIba_id()
    {
        return $this->iba_id->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setLay_id($val)
    {
        $this->lay_id->setVal($val);
    }
    public function setFechaSistema($val)
    {
        $this->lay_fechaSistema->setVal($val);
    }
    public function setAnioSinpres($val)
    {
        $this->lay_anioSinpres->setVal($val);
    }
    public function setPropuesta($val)
    {
        $this->lay_propuesta->setVal($val);
    }
    public function setArchivo($val)
    {
        $this->lay_archivo->setVal($val);
    }
    public function setPassword($val)
    {
        $this->lay_password->setVal($val);
    }
    public function setObservacion($val)
    {
        $this->lay_observacion->setVal($val);
    }
    public function setTla_id($val)
    {
        $this->tla_id->setVal($val);
    }
    public function setEla_id($val)
    {
        $this->ela_id->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    public function setIba_id($val)
    {
        $this->iba_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staLay_id($val)
    {
        $this->lay_id->staVal($val);
    }
    public function staFechaSistema($val)
    {
        $this->lay_fechaSistema->staVal($val);
    }
    public function staAnioSinpres($val)
    {
        $this->lay_anioSinpres->staVal($val);
    }
    public function staPropuesta($val)
    {
        $this->lay_propuesta->staVal($val);
    }
    public function staArchivo($val)
    {
        $this->lay_archivo->staVal($val);
    }
    public function staPassword($val)
    {
        $this->lay_password->staVal($val);
    }
    public function staObservacion($val)
    {
        $this->lay_observacion->staVal($val);
    }
    public function staTla_id($val)
    {
        $this->tla_id->staVal($val);
    }
    public function staEla_id($val)
    {
        $this->ela_id->staVal($val);
    }
    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }
    public function staIba_id($val)
    {
        $this->iba_id->staVal($val);
    }

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(array("tabla" => BD_TESORERIA.".lot_Layout"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->lay_id = new Atributo("numero", "NO", "PRI", "lay_id");
        $this->lay_fechaSistema = new Atributo("fecha", "NO", "", "lay_fechaSistema");
        $this->lay_anioSinpres = new Atributo("numero", "NO", "", "lay_anioSinpres");
        $this->lay_propuesta = new Atributo("cadena", "NO", "", "lay_propuesta");
        $this->lay_archivo = new Atributo("cadena", "NO", "", "lay_archivo");
        $this->lay_password = new Atributo("cadena", "NO", "", "lay_password");
        $this->lay_observacion = new Atributo("cadena", "NO", "", "lay_observacion");
        $this->tla_id = new Atributo("numero", "NO", "MUL", "tla_id");
        $this->ela_id = new Atributo("numero", "NO", "MUL", "ela_id");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");
        $this->iba_id = new Atributo("numero", "NO", "", "iba_id");

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $lay = new Layout();
        if (isset($p->lay_id)) {
            $lay->setLay_id($p->lay_id);
        }

        if (isset($p->fechaSistema)) {
            $lay->setFechaSistema($p->fechaSistema);
        }

        if (isset($p->anioSinpres)) {
            $lay->setAnioSinpres($p->anioSinpres);
        }

        if (isset($p->propuesta)) {
            $lay->setPropuesta($p->propuesta);
        }

        if (isset($p->archivo)) {
            $lay->setArchivo($p->archivo);
        }

        if (isset($p->password)) {
            $lay->setPassword($p->password);
        }

        if (isset($p->observacion)) {
            $lay->setObservacion($p->observacion);
        }

        if (isset($p->tla_id)) {
            $lay->setTla_id($p->tla_id);
        }

        if (isset($p->ela_id)) {
            $lay->setEla_id($p->ela_id);
        }

        if (isset($p->usr_id)) {
            $lay->setUsr_id($p->usr_id);
        }

        if (isset($p->iba_id)) {
            $lay->setIba_id($p->iba_id);
        }

        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $bit_observacion = 'fechaSistema:' . $p->fechaSistema . ' anio:' . $p->anioSinpres . 'propuestas:' . $p->propuesta . ' archivo:' . $p->archivo . 'password:' . $p->password . 'tipo:' . $p->tla_id . 'estado:' . $p->ela_id . 'iba_id' . $p->iba_id;
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a":
                $lay->conectar();
                $bit_observacion = 'REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lay->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lay->consultar($params);
                $lay->insert(false);
                return $lay->getUltimoId();
                break;
            case "b":$lay->delete();
                break;
            case "c":$lay->update();
                break;
        }
    }

}
