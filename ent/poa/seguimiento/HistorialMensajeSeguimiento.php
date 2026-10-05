<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class HistorialMensajeSeguimiento extends Entidad
{
    /*****************************************************/
    /**************** ATRIBUTOS DE LA CLASE *************/
    /***************************************************/

    protected $hms_id; //
    protected $hms_mensaje; //
    protected $eme_id; //
    protected $hms_mensajeRespuesta; //
    protected $hms_trimestre;
    protected $hms_mensual;
    protected $tim_id;
    protected $msj_id; //
    protected $ese_id;
    protected $hms_respuesta; //
    protected $hms_fechaRespuesta;
    protected $hms_fechaSolicitudEvidencia;
    protected $usr_id;
    protected $usr_fecha;

    /***************************************/
    /*********** GETTERS ******************/
    /*************************************/
    public function getMsj_id()
    {
        $this->msj_id->getVal();
    }
    public function getHms_id()
    {
        $this->hms_id->getVal();
    }
    public function getHms_mensaje()
    {
        $this->hms_mensaje->getVal();
    }
    public function getEme_id()
    {
        $this->eme_id->getVal();
    }
    public function getHms_mensaje_respuesta()
    {
        $this->hms_mensajeRespuesta->getVal();
    }
    public function getHms_respuesta()
    {
        $this->hms_respuesta->getVal();
    }
    public function getHms_fecha_respuesta()
    {
        $this->hms_fechaRespuesta->getVal();
    }
    public function getHms_trimestre()
    {
        $this->hms_trimestre->getVal();
    }
    public function getHms_mensual()
    {
        $this->hms_mensual->getVal();
    }
    public function getTim_id()
    {
        $this->tim_id->getVal();
    }
    public function getEse_id()
    {
        $this->ese_id->getVal();
    }
    public function getHms_fecha_solicitud_evidencia()
    {
        $this->hms_fechaSolicitudEvidencia->getVal();
    }
    public function getUsr_id()
    {
        $this->usr_id->getVal();
    }
    public function getUsr_fecha()
    {
        $this->usr_fecha->getVal();
    }

    /***************************************/
    /************ SETTERS *****************/
    /***************************************/
    public function setMsj_id($msj_id)
    {
        $this->msj_id->setVal($msj_id);
    }
    public function setHms_id($hms_id)
    {
        $this->hms_id->setVal($hms_id);
    }
    public function setHms_mensaje($hms_mensaje)
    {
        $this->hms_mensaje->setVal($hms_mensaje);
    }
    public function setEme_id($eme_id)
    {
        $this->eme_id->setVal($eme_id);
    }
    public function setHms_mensaje_respuesta($hms_mensajeRespuesta)
    {
        $this->hms_mensajeRespuesta->setVal($hms_mensajeRespuesta);
    }
    public function setHms_respuesta($hms_respuesta)
    {
        $this->hms_respuesta->setVal($hms_respuesta);
    }
    public function setHms_fecha_respuesta($hms_fechaRespuesta)
    {
        $this->hms_fechaRespuesta->setVal($hms_fechaRespuesta);
    }
    public function setHms_trimestre($hms_trimestre)
    {
        $this->hms_trimestre->setVal($hms_trimestre);
    }
    public function setHms_mensual($hms_mensual)
    {
        $this->hms_mensual->setVal($hms_mensual);
    }
    public function setTim_id($tim_id)
    {
        $this->tim_id->setVal($tim_id);
    }
    public function setEse_id($ese_id)
    {
        $this->ese_id->setVal($ese_id);
    }
    public function setHms_fecha_solicitud_evidencia($hms_fechaSolicitudEvidencia)
    {
        $this->hms_fechaSolicitudEvidencia->setVal($hms_fechaSolicitudEvidencia);
    }
    public function setUsr_id($usr_id)
    {
        $this->usr_id->setVal($usr_id);
    }
    public function setUsr_fecha($usr_fecha)
    {
        $this->usr_fecha->setVal($usr_fecha);
    }

    /* *************************************************
    CONSTRUCTOR
     ************************************************* */
    public function __construct()
    {
        parent::__construct(array("tabla" => "cat_HistorialMensajeSeguimiento"));

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */
        $this->msj_id = new Atributo("numero", "NO", "", "msj_id");
        $this->hms_id = new Atributo("numero", "NO", "PRI", "hms_id");
        $this->hms_mensaje = new Atributo("cadena", "NO", "", "hms_mensaje");
        $this->eme_id = new Atributo("numero", "NO", "", "eme_id");
        $this->hms_mensajeRespuesta = new Atributo("cadena", "SI", "", "hms_mensajeRespuesta");
        $this->hms_respuesta = new Atributo("numero", "SI", "", "hms_respuesta");
        $this->hms_fechaRespuesta = new Atributo("fecha", "SI", "", "hms_fechaRespuesta");
        $this->hms_trimestre = new Atributo("numero", "NO", "", "hms_trimestre");
        $this->hms_mensual = new Atributo("numero", "NO", "", "hms_mensual");
        $this->tim_id = new Atributo("numero", "NO", "", "tim_id");
        $this->ese_id = new Atributo("numero", "NO", "", "ese_id");
        $this->hms_fechaSolicitudEvidencia = new Atributo("fecha", "SI", "", "hms_fechaSolicitudEvidencia");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");
        $this->usr_fecha = new Atributo("fecha", "NO", "", "usr_fecha");
    }

    /* ****************************************************
    FUNCIONES ESTÁTICAS
     *************************************************** */

    public static function abc($p)
    {
        $hms = new HistorialMensajeSeguimiento();

        if (isset($p->msj_id)) {
            $hms->setMsj_id($p->msj_id);
        }

        if (isset($p->hms_id)) {
            $hms->setHms_id($p->hms_id);
        }

        if (isset($p->hms_mensaje)) {
            $hms->setHms_mensaje($p->hms_mensaje);
        }

        if (isset($p->eme_id)) {
            $hms->setEme_id($p->eme_id);
        }

        if (isset($p->hms_mensajeRespuesta)) {
            $hms->setHms_mensaje_respuesta($p->hms_mensajeRespuesta);
        }

        if (isset($p->hms_respuesta)) {
            $hms->setHms_respuesta($p->hms_respuesta);
        }

        if (isset($p->hms_fechaRespuesta)) {
            $hms->setHms_fecha_respuesta($p->hms_fechaRespuesta);
        }

        if (isset($p->hms_trimestre)) {
            $hms->setHms_trimestre($p->hms_trimestre);
        }

        if (isset($p->hms_mensual)) {
            $hms->setHms_mensual($p->hms_mensual);
        }

        if (isset($p->tim_id)) {
            $hms->setTim_id($p->tim_id);
        }

        if (isset($p->ese_id)) {
            $hms->setEse_id($p->ese_id);
        }

        if (isset($p->hms_fechaSolicitudEvidencia)) {
            $hms->setHms_fecha_solicitud_evidencia($p->hms_fechaSolicitudEvidencia);
        }

        if (isset($p->usr_id)) {
            $hms->setUsr_id($p->usr_id);
        }

        if (isset($p->usr_fecha)) {
            $hms->setUsr_fecha($p->usr_fecha);
        }

        switch ($p->abc) {
            case "a":
                $hms->insert();
                return $hms->getUltimoId();
                break;
            case "b":
                $hms->delete();
                break;
            case "c":
                $hms->update();
                break;
        }
    }
} // Fin class
