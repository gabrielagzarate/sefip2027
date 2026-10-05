<?php
require_once dirname(__FILE__) . "/Entidad.php";

class Mensajes extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/
    protected $sme_id; //Identificador del mensaje
    protected $asunto; //Asunto del que trata el mensaje
    protected $mensaje; //Contenido del mensaje
    protected $fechaInicial; //Fecha y hora en la que se empezara a mostrarse en sistema
    protected $fechaFinal; //Fecha y hora en la que se terminara de mostrarse en sistema
    protected $tipo; //Indica el tipo de mensaje: N: normal M:Modal
    protected $usr_id; //
    protected $color; //color con el que se mostrara el mensaje1 - azul2 - azul cielo3 - verde4 - amarillo5 - rojo
    protected $sistema; //sistema SINPRES en el que aparecera el mensaje 0 - 2014,2015 1 - 2014 2 - 2015

    /******************************************************
    METODOS GET
     ******************************************************/
    public function getSme_id()
    {return $this->sme_id->getVal();}
    public function getAsunto()
    {return $this->asunto->getVal();}
    public function getMensaje()
    {return $this->mensaje->getVal();}
    public function getFechaInicial()
    {return $this->fechaInicial->getVal();}
    public function getFechaFinal()
    {return $this->fechaFinal->getVal();}
    public function getTipo()
    {return $this->tipo->getVal();}
    public function getUsr_id()
    {return $this->usr_id->getVal();}
    public function getColor()
    {return $this->color->getVal();}
    public function getSistema()
    {return $this->sistema->getVal();}

    /******************************************************
    METODOS SET
     ******************************************************/
    public function setSme_id($val)
    {$this->sme_id->setVal($val);}
    public function setAsunto($val)
    {$this->asunto->setVal($val);}
    public function setMensaje($val)
    {$this->mensaje->setVal($val);}
    public function setFechaInicial($val)
    {$this->fechaInicial->setVal($val);}
    public function setFechaFinal($val)
    {$this->fechaFinal->setVal($val);}
    public function setTipo($val)
    {$this->tipo->setVal($val);}
    public function setUsr_id($val)
    {$this->usr_id->setVal($val);}
    public function setColor($val)
    {$this->color->setVal($val);}
    public function setSistema($val)
    {$this->sistema->setVal($val);}

    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/
    public function staSme_id($val)
    {$this->sme_id->staVal($val);}
    public function staAsunto($val)
    {$this->asunto->staVal($val);}
    public function staMensaje($val)
    {$this->mensaje->staVal($val);}
    public function staFechaInicial($val)
    {$this->fechaInicial->staVal($val);}
    public function staFechaFinal($val)
    {$this->fechaFinal->staVal($val);}
    public function staTipo($val)
    {$this->tipo->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}
    public function staColor($val)
    {$this->color->staVal($val);}
    public function staSistema($val)
    {$this->sistema->staVal($val);}

    /******************************************************
    CONSTRUCTOR
     ******************************************************/
    public function __construct()
    {

        parent::__construct(array("tabla" => BD_SINPRES.".sis_Mensaje"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->sme_id = new Atributo("numero", "NO", "PRI", "sme_id");
        $this->asunto = new Atributo("cadena", "NO", "", "sme_asunto");
        $this->mensaje = new Atributo("cadena", "NO", "", "sme_Mensaje");
        $this->fechaInicial = new Atributo("fecha", "NO", "", "sme_fechaInicial");
        $this->fechaFinal = new Atributo("fecha", "NO", "", "sme_fechaFinal");
        $this->tipo = new Atributo("cadena", "NO", "", "sme_tipo");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");
        $this->color = new Atributo("numero", "NO", "", "sme_color");
        $this->sistema = new Atributo("numero", "NO", "", "sme_sistema");
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function abc($p)
    {
        $sme = new Mensajes();
        if (isset($p->sme_id)) {
            $sme->setSme_id($p->sme_id);
        }

        if (isset($p->asunto)) {
            $sme->setAsunto($p->asunto);
        }

        if (isset($p->mensaje)) {
            $sme->setMensaje($p->mensaje);
        }

        if (isset($p->fechaInicial)) {
            $sme->setFechaInicial($p->fechaInicial);
        }

        if (isset($p->fechaFinal)) {
            $sme->setFechaFinal($p->fechaFinal);
        }

        if (isset($p->tipo)) {
            $sme->setTipo($p->tipo);
        }

        if (isset($p->usr_id)) {
            $sme->setUsr_id($p->usr_id);
        }

        if (isset($p->color)) {
            $sme->setColor($p->color);
        }

        if (isset($p->sistema)) {
            $sme->setSistema($p->sistema);
        }

        switch ($p->abc) {
            case "a":$sme->insert();return $sme->getUltimoId();
                break;
            case "b":$sme->delete();
                break;
            case "c":$sme->update();
                break;
        }
    }

} // fin class
