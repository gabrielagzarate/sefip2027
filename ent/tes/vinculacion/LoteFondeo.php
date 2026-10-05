<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class LoteFondeo extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $mbn_id; //
    protected $fpa_id; //
    protected $msc_id; //
    protected $lot_ejercicio; //
    protected $lot_id; //
    protected $usr_id; //Identificador de usuario que captura/actualiza el registro

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getMbn_id()
    {
        return $this->mbn_id->getVal();
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
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setMbn_id($val)
    {
        $this->mbn_id->setVal($val);
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
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staMbn_id($val)
    {
        $this->mbn_id->staVal($val);
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
    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(array("tabla" => BD_TESORERIA.".vin_LoteFondeo"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->mbn_id = new Atributo("numero", "NO", "PRI", "mbn_id");
        $this->fpa_id = new Atributo("numero", "NO", "PRI", "fpa_id");
        $this->msc_id = new Atributo("numero", "NO", "PRI", "msc_id");
        $this->lot_ejercicio = new Atributo("numero", "NO", "PRI", "lot_ejercicio");
        $this->lot_id = new Atributo("numero", "NO", "PRI", "lot_id");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $lot = new LoteFondeo();
        $bit_observacion = '';

        if (isset($p->mbn_id)) {
            $lot->setMbn_id($p->mbn_id);
            $bit_observacion .= 'mbn_id: ' . $p->mbn_id;
        }
        if (isset($p->fpa_id)) {
            $lot->setFpa_id($p->fpa_id);
            $bit_observacion .= 'fpa_id: ' . $p->fpa_id;
        }
        if (isset($p->msc_id)) {
            $lot->setMsc_id($p->msc_id);
            $bit_observacion .= 'msc_id: ' . $p->msc_id;
        }
        if (isset($p->ejercicio)) {
            $lot->setEjercicio($p->ejercicio);
            $bit_observacion .= 'ejercicio: ' . $p->ejercicio;
        }
        if (isset($p->lot_id)) {
            $lot->setLot_id($p->lot_id);
            $bit_observacion .= 'lot_id: ' . $p->lot_id;
        }
        if (isset($p->usr_id)) {
            $lot->setUsr_id($p->usr_id);
            $bit_observacion .= 'usr_id: ' . $p->usr_id;
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
                $bit_observacion = ' REGISTRO BORRADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->delete(false);
                break;
            case "c":
                $lot->conectar();
                $bit_observacion = ' REGISTRO ACTUALIZADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->update(false);
                break;
        }
    }

}
