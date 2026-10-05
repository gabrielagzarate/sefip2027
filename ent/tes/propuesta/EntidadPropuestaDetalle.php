<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class EntidadPropuestaDetalle extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $pro_id; //
    protected $gpo_id; //
    protected $ur_id; //
    protected $clc_id; //
    protected $pro_importeClc; //
    protected $usr_id; //
    protected $bur_id; //
    protected $usr_fecha; //

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getpro_id()
    {
        return $this->pro_id->getVal();
    }
    public function getgpo_id()
    {
        return $this->gpo_id->getVal();
    }
    public function getur_id()
    {
        return $this->ur_id->getVal();
    }
    public function getclc_id()
    {
        return $this->clc_id->getVal();
    }
    public function getpro_importeClc()
    {
        return $this->pro_importeClc->getVal();
    }
    public function getbur_id()
    {
        return $this->bur_id->getVal();
    }
    public function getusr_id()
    {
        return $this->usr_id->getVal();
    }
    public function getusr_fecha()
    {
        return $this->usr_fecha->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setpro_id($val)
    {
        $this->pro_id->setVal($val);
    }
    public function setgpo_id($val)
    {
        $this->gpo_id->setVal($val);
    }
    public function setur_id($val)
    {
        $this->ur_id->setVal($val);
    }
    public function setclc_id($val)
    {
        $this->clc_id->setVal($val);
    }
    public function setpro_importeClc($val)
    {
        $this->pro_importeClc->setVal($val);
    }
    public function setbur_id($val)
    {
        $this->bur_id->setVal($val);
    }
    public function setusr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    public function setusr_fecha($val)
    {
        $this->usr_fecha->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function stapro_id($val)
    {
        $this->pro_id->staVal($val);
    }
    public function stagpo_id($val)
    {
        $this->gpo_id->staVal($val);
    }
    public function staur_id($val)
    {
        $this->ur_id->staVal($val);
    }
    public function staclc_id($val)
    {
        $this->clc_id->staVal($val);
    }
    public function stapro_importeClc($val)
    {
        $this->pro_importeClc->staVal($val);
    }
    public function stabur_id($val)
    {
        $this->bur_id->staVal($val);
    }
    public function stausr_id($val)
    {
        $this->usr_id->staVal($val);
    }
    public function stausr_fecha($val)
    {
        $this->usr_fecha->staVal($val);
    }

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(array("tabla" => BD_TESORERIA.".pro_PropuestaClc"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->pro_id = new Atributo("numero", "NO", "PRI", "pro_id");
        $this->gpo_id = new Atributo("numero", "NO", "PRI", "gpo_id");
        $this->ur_id = new Atributo("numero", "NO", "PRI", "ur_id");
        $this->clc_id = new Atributo("numero", "NO", "PRI", "clc_id");
        $this->pro_importeClc = new Atributo("cadena", "NO", "", "pro_importeClc");
        $this->bur_id = new Atributo("cadena", "NO", "", "bur_id");
        $this->usr_id = new Atributo("cadena", "NO", "", "usr_id");
        $this->usr_fecha = new Atributo("cadena", "NO", "", "usr_fecha");

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {

        $epd = new EntidadPropuestaDetalle();
        $bit_observacion = '';

        if (isset($p->pro_id)) {
            $epd->setpro_id($p->pro_id);
            $bit_observacion .= 'pro_id: ' . $p->pro_id;
        }
        if (isset($p->gpo_id)) {
            $epd->setgpo_id($p->gpo_id);
            $bit_observacion .= ' gpo_id: ' . $p->gpo_id;
        }
        if (isset($p->ur_id)) {
            $epd->setur_id($p->ur_id);
            $bit_observacion .= ' ur_id: ' . $p->ur_id;
        }
        if (isset($p->clc_id)) {
            $epd->setclc_id($p->clc_id);
            $bit_observacion .= ' clc_id: ' . $p->clc_id;
        }
        if (isset($p->pro_importeClc)) {
            $epd->setpro_importeClc($p->pro_importeClc);
            $bit_observacion .= ' pro_importeClc: ' . $p->pro_importeClc;
        }
        if (isset($p->bur_id)) {
            $epd->setbur_id($p->bur_id);
            $bit_observacion .= ' bur_id: ' . $p->bur_id;
        }
        if (isset($p->usr_id)) {
            $epd->setusr_id($p->usr_id);
            $bit_observacion .= ' usr_id: ' . $p->usr_id;
        }
        if (isset($p->usr_fecha)) {
            $epd->setusr_fecha($p->usr_fecha);
            $bit_observacion .= ' usr_fecha: ' . $p->usr_fecha;
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a":
                $epd->conectar();
                $bit_observacion = 'REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $epd->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $epd->consultar($params);
                $epd->insert(false);
                return $epd->getUltimoId();
                break;
            case "b":
                $epd->conectar();
                $bit_observacion = 'REGISTRO BORRADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $epd->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $epd->consultar($params);
                $epd->delete(false);
                break;
            case "c":
                $epd->conectar();
                $bit_observacion = 'REGISTRO ACTUALIZADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $epd->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $epd->consultar($params);
                $epd->update(false);
                break;
        }
    }
}
