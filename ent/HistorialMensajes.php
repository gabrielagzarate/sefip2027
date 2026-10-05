<?php

require_once dirname(__FILE__) . '/../lib/constantes.php';
require_once dirname(__FILE__) . '/Entidad.php';

class HistorialMensajes extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $mje_id; //
    protected $gpo_id; //
    protected $ur_id; //
    protected $mje_destinatario; //
    protected $mje_remitente; //
    protected $mje_asunto; //
    protected $mje_descripcion; //
    protected $mje_respuesta; //
    protected $mje_fechaEnvio; //
    protected $mje_fechaRecibido; //
    protected $eme_id; //
    protected $usr_id; //Clave identificadora del usuario que inserto el registro

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getMje_id()
    {
        return $this->mje_id->getVal();
    }
    public function getGpo_id()
    {
        return $this->gpo_id->getVal();
    }
    public function getUr_id()
    {
        return $this->ur_id->getVal();
    }
    public function getMje_destinatario()
    {
        return $this->mje_destinatario->getVal();
    }
    public function getMje_remitente()
    {
        return $this->mje_remitente->getVal();
    }
    public function getMje_asunto()
    {
        return $this->mje_asunto->getVal();
    }
    public function getMje_descripcion()
    {
        return $this->mje_descripcion->getVal();
    }
    public function getMje_respuesta()
    {
        return $this->mje_respuesta->getVal();
    }
    public function getMje_fechaEnvio()
    {
        return $this->mje_fechaEnvio->getVal();
    }
    public function getMje_fechaRecibido()
    {
        return $this->mje_fechaRecibido->getVal();
    }
    public function getEme_id()
    {
        return $this->eme_id->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    public function getNombreRemitente()
    {
        $params = [$this->getMje_remitente() * 1];
        $this->setSql(' SELECT usr_usuario FROM cat_Usuario WHERE usr_id= ?');
        $this->conectar();
        $this->consultar($params);
        $this->desconectar();
        $nombre = $this->extraer();
        if ($this->getNumeroRegistros() == 1) {
            return $nombre['usr_usuario'];
        } else {
            return '';
        }
    }

    public function getIdRespuesta()
    {
        $params = [$this->getMje_id()];
        $this->setSql(' SELECT mje_id FROM poa_HistorialMensajes WHERE mje_respuesta = ?');
        $this->conectar();
        $this->consultar($params);
        $this->desconectar();
        $mjeAnterior = $this->extraer();
        if ($this->getNumeroRegistros() == 1) {
            return $mjeAnterior['mje_id'];
        } else {
            return 0;
        }
    }

    public function getHistorial()
    {
        if ($this->getMje_id() != 0) {
            $historial = [$this];
            $mensaje = $this;
            $mje = $mensaje->getMje_respuesta();
            while ($mje != 0) {
                $mensaje = new HistorialMensajes();
                $mensaje->setMje_id($mje);
                $mensaje->select();
                array_push($historial, $mensaje);
                $mje = $mensaje->getMje_respuesta();
            }
            krsort($historial);
            $mensaje = $this;
            do {
                $mje = $mensaje->getIdRespuesta();
                if ($mje != 0) {
                    $mensaje = new HistorialMensajes();
                    $mensaje->setMje_id($mje);
                    $mensaje->select();
                    array_push($historial, $mensaje);
                }
            } while ($mje != 0);
            return $historial;
        } else {
            return [];
        }
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setMje_id($val)
    {
        $this->mje_id->setVal($val);
    }
    public function setGpo_id($val)
    {
        $this->gpo_id->setVal($val);
    }
    public function setUr_id($val)
    {
        $this->ur_id->setVal($val);
    }
    public function setMje_destinatario($val)
    {
        $this->mje_destinatario->setVal($val);
    }
    public function setMje_remitente($val)
    {
        $this->mje_remitente->setVal($val);
    }
    public function setMje_asunto($val)
    {
        $this->mje_asunto->setVal($val);
    }
    public function setMje_descripcion($val)
    {
        $this->mje_descripcion->setVal($val);
    }
    public function setMje_respuesta($val)
    {
        $this->mje_respuesta->setVal($val);
    }
    public function setMje_fechaEnvio($val)
    {
        $this->mje_fechaEnvio->setVal($val);
    }
    public function setMje_fechaRecibido($val)
    {
        $this->mje_fechaRecibido->setVal($val);
    }
    public function setEme_id($val)
    {
        $this->eme_id->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staMje_id($val)
    {
        $this->mje_id->staVal($val);
    }
    public function staGpo_id($val)
    {
        $this->gpo_id->staVal($val);
    }
    public function staUr_id($val)
    {
        $this->ur_id->staVal($val);
    }
    public function staMje_destinatario($val)
    {
        $this->mje_destinatario->staVal($val);
    }
    public function staMje_remitente($val)
    {
        $this->mje_remitente->staVal($val);
    }
    public function staMje_asunto($val)
    {
        $this->mje_asunto->staVal($val);
    }
    public function staMje_descripcion($val)
    {
        $this->mje_descripcion->staVal($val);
    }
    public function staMje_respuesta($val)
    {
        $this->mje_respuesta->staVal($val);
    }
    public function staMje_fechaEnvio($val)
    {
        $this->mje_fechaEnvio->staVal($val);
    }
    public function staMje_fechaRecibido($val)
    {
        $this->mje_fechaRecibido->staVal($val);
    }
    public function staEme_id($val)
    {
        $this->eme_id->staVal($val);
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

        parent::__construct(['tabla' => 'poa_HistorialMensajes']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->mje_id = new Atributo('numero', 'NO', 'PRI', 'mje_id');
        $this->gpo_id = new Atributo('numero', 'NO', 'MUL', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', '', 'ur_id');
        $this->mje_destinatario = new Atributo('numero', 'NO', '', 'mje_destinatario');
        $this->mje_remitente = new Atributo('numero', 'NO', '', 'mje_remitente');
        $this->mje_asunto = new Atributo('cadena', 'NO', '', 'mje_asunto');
        $this->mje_descripcion = new Atributo('cadena', 'NO', '', 'mje_descripcion');
        $this->mje_respuesta = new Atributo('numero', 'NO', '', 'mje_respuesta');
        $this->mje_fechaEnvio = new Atributo('fecha', 'NO', '', 'mje_fechaEnvio');
        $this->mje_fechaRecibido = new Atributo('fecha', 'YES', '', 'mje_fechaRecibido');
        $this->eme_id = new Atributo('numero', 'NO', 'MUL', 'eme_id');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');
    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $mje = new HistorialMensajes();
        if (isset($p->mje_id)) {
            $mje->setMje_id($p->mje_id);
        }

        if (isset($p->gpo_id)) {
            $mje->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $mje->setUr_id($p->ur_id);
        }

        if (isset($p->destinatario)) {
            $mje->setMje_destinatario($p->destinatario);
        } else {
            $mje->setMje_destinatario('0');
        }

        if (isset($p->remitente)) {
            $mje->setMje_remitente($p->remitente);
        }

        if (isset($p->asunto)) {
            $mje->setMje_asunto($p->asunto);
        }

        if (isset($p->descripcion)) {
            $mje->setMje_descripcion($p->descripcion);
        }

        if (isset($p->respuesta)) {
            $mje->setMje_respuesta($p->respuesta);
        } else {
            $mje->setMje_respuesta('0');
        }

        if (isset($p->fechaEnvio)) {
            $mje->setMje_fechaEnvio($p->fechaEnvio);
        }

        if (isset($p->fechaRecibido)) {
            $mje->setMje_fechaRecibido($p->fechaRecibido);
        }

        if (isset($p->eme_id)) {
            $mje->setEme_id($p->eme_id);
        }

        if (isset($p->usr_id)) {
            $mje->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case 'a':
                $mje->insert();
                return $mje->getUltimoId();
                break;
            case 'b':
                $mje->delete();
                break;
            case 'c':
                $mje->update();
                break;
        }
    }
}
