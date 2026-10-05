<?php

require_once dirname(__FILE__) . '/../../Entidad.php';

class MensajeMir extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     *************************************************** */

    protected $mjm_id; //
    protected $mir_id; //
    protected $emj_id; //
    protected $mjm_asunto; //asunto del mensaje (motivo)
    protected $mjm_mensaje; //guarda el mensaje general
    protected $mjm_remitente; //identificador del usuario que envia el mensaje
    protected $mjm_destinatario; //identificador del usuario a quien es dirigido el mensaje
    protected $mjm_fechaEnvio; //
    protected $usr_id; //

    /******************************************************

    METODOS GET

     ***************************************************** */

    public function getMjm_id()
    {
        return $this->mjm_id->getVal();
    }

    public function getMir_id()
    {
        return $this->mir_id->getVal();
    }

    public function getEmj_id()
    {
        return $this->emj_id->getVal();
    }

    public function getAsunto()
    {
        return $this->mjm_asunto->getVal();
    }

    public function getMensaje()
    {
        return $this->mjm_mensaje->getVal();
    }

    public function getRemitente()
    {
        return $this->mjm_remitente->getVal();
    }

    public function getDestinatario()
    {
        return $this->mjm_destinatario->getVal();
    }

    public function getFechaEnvio()
    {
        return $this->mjm_fechaEnvio->getVal();
    }

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

    METODOS SET

     ***************************************************** */

    public function setMjm_id($val)
    {
        $this->mjm_id->setVal($val);
    }

    public function setMir_id($val)
    {
        $this->mir_id->setVal($val);
    }

    public function setEmj_id($val)
    {
        $this->emj_id->setVal($val);
    }

    public function setAsunto($val)
    {
        $this->mjm_asunto->setVal($val);
    }

    public function setMensaje($val)
    {
        $this->mjm_mensaje->setVal($val);
    }

    public function setRemitente($val)
    {
        $this->mjm_remitente->setVal($val);
    }

    public function setDestinatario($val)
    {
        $this->mjm_destinatario->setVal($val);
    }

    public function setFechaEnvio($val)
    {
        $this->mjm_fechaEnvio->setVal($val);
    }

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ***************************************************** */

    public function staMjm_id($val)
    {
        $this->mjm_id->staVal($val);
    }

    public function staMir_id($val)
    {
        $this->mir_id->staVal($val);
    }

    public function staEmj_id($val)
    {
        $this->emj_id->staVal($val);
    }

    public function staAsunto($val)
    {
        $this->mjm_asunto->staVal($val);
    }

    public function staMensaje($val)
    {
        $this->mjm_mensaje->staVal($val);
    }

    public function staRemitente($val)
    {
        $this->mjm_remitente->staVal($val);
    }

    public function staDestinatario($val)
    {
        $this->mjm_destinatario->staVal($val);
    }

    public function staFechaEnvio($val)
    {
        $this->mjm_fechaEnvio->staVal($val);
    }

    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }

    /******************************************************

    CONSTRUCTOR

     ***************************************************** */

    public function __construct()
    {

        parent::__construct(['tabla' => 'cat_MensajeMir']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->mjm_id = new Atributo('numero', 'NO', 'PRI', 'mjm_id');
        $this->mir_id = new Atributo('numero', 'NO', 'MUL', 'mir_id');
        $this->emj_id = new Atributo('numero', 'NO', 'MUL', 'emj_id');
        $this->mjm_asunto = new Atributo('cadena', 'NO', '', 'mjm_asunto');
        $this->mjm_mensaje = new Atributo('cadena', 'NO', '', 'mjm_mensaje');
        $this->mjm_remitente = new Atributo('numero', 'NO', '', 'mjm_remitente');
        $this->mjm_destinatario = new Atributo('numero', 'NO', '', 'mjm_destinatario');
        $this->mjm_fechaEnvio = new Atributo('fecha', 'NO', '', 'mjm_fechaEnvio');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');
    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ***************************************************** */

    public static function abc($p)
    {
        $mjm = new MensajeMir();
        if (isset($p->mjm_id)) {
            $mjm->setMjm_id($p->mjm_id);
        }

        if (isset($p->mir_id)) {
            $mjm->setMir_id($p->mir_id);
        }

        if (isset($p->emj_id)) {
            $mjm->setEmj_id($p->emj_id);
        }

        if (isset($p->asunto)) {
            $mjm->setAsunto($p->asunto);
        }

        if (isset($p->mensaje)) {
            $mjm->setMensaje($p->mensaje);
        }

        if (isset($p->remitente)) {
            $mjm->setRemitente($p->remitente);
        }

        if (isset($p->destinatario)) {
            $mjm->setDestinatario($p->destinatario);
        }

        if (isset($p->fechaEnvio)) {
            $mjm->setFechaEnvio($p->fechaEnvio);
        }

        switch ($p->abc) {
            case 'a':
                $mjm->insert();
                return $mjm->getUltimoId();
                break;
            case 'b':
                $mjm->delete();
                break;
            case 'c':
                $mjm->update();
                break;
        }
    }

}
