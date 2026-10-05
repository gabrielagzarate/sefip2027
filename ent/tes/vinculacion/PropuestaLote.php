<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class PropuestaLote extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $pro_id; //pro_Propuesta - Llave foranea - Identificador de la Propuesta
    protected $fpa_id; //cat_FormaPago - Llave foranea - Identificador de la Forma de Pago
    protected $msc_id; //cat_Mes - Llave foranea - Identificador del Mes
    protected $lot_ejercicio; //lot_Lote - Llave Foranea - Identificador del ejercicio del Documento
    protected $lot_id; //lot_Lote - Llave foranea - Identificador del Lote

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getPro_id()
    {
        return $this->pro_id->getVal();
    }
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

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setPro_id($val)
    {
        $this->pro_id->setVal($val);
    }
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

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staPro_id($val)
    {
        $this->pro_id->staVal($val);
    }
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

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(array("tabla" => BD_TESORERIA.".vin_PropuestaLote"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->pro_id = new Atributo("numero", "NO", "PRI", "pro_id");
        $this->fpa_id = new Atributo("numero", "NO", "PRI", "fpa_id");
        $this->msc_id = new Atributo("numero", "NO", "PRI", "msc_id");
        $this->lot_ejercicio = new Atributo("numero", "NO", "PRI", "lot_ejercicio");
        $this->lot_id = new Atributo("numero", "NO", "PRI", "lot_id");

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $lot = new PropuestaLote();
        $bit_observacion = '';
        if (isset($p->pro_id)) {
            $lot->setPro_id($p->pro_id);
            $bit_observacion .= " pro_id: " . $p->pro_id;
        }
        if (isset($p->fpa_id)) {
            $lot->setFpa_id($p->fpa_id);
            $bit_observacion .= " fpa_id: " . $p->fpa_id;
        }
        if (isset($p->msc_id)) {
            $lot->setMsc_id($p->msc_id);
            $bit_observacion .= " msc_id: " . $p->msc_id;
        }
        if (isset($p->ejercicio)) {
            $lot->setEjercicio($p->ejercicio);
            $bit_observacion .= " ejercicio: " . $p->ejercicio;
        }
        if (isset($p->lot_id)) {
            $lot->setLot_id($p->lot_id);
            $bit_observacion .= " lot_id: " . $p->lot_id;
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a":
                $lot->conectar();
                $bit_observacion = ' REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->insert(false);
                return $lot->getUltimoId();
                break;
            case "b":
                $lot->conectar();
                $bit_observacion = ' REGISTRO ELIMINADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->delete(false);
                break;
            case "c":
                $lot->conectar();
                $bit_observacion = ' REGISTRO MODIFICADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->update(false);
                break;
        }
    }
}
