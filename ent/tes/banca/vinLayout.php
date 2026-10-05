<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class LoteLayout extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $lay_id; //lot_Layout - Llave foranea - Identificador del Layout
    protected $fpa_id; //cat_FormaPago - Llave foranea - Identificador de la Forma de Pago
    protected $msc_id; //cat_Mes - Llave foranea - Identificador del Mes
    protected $lot_ejercicio; //lot_Lote - Llave Foranea - Identificador del ejercicio del Documento
    protected $lot_id; //lot_Lote - Llave foranea - Identificador del Lote
    protected $usr_id; //Identificador de usuario que captura/actualiza el registro

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getLay_id()
    {
        return $this->lay_id->getVal();
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

    public function setLay_id($val)
    {
        $this->lay_id->setVal($val);
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

    public function staLay_id($val)
    {
        $this->lay_id->staVal($val);
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

        parent::__construct(array("tabla" => BD_TESORERIA.".vin_LoteLayout"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->lay_id = new Atributo("numero", "NO", "PRI", "lay_id");
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
        $lot = new LoteLayout();
        if (isset($p->lay_id)) {
            $lot->setLay_id($p->lay_id);
        }

        if (isset($p->fpa_id)) {
            $lot->setFpa_id($p->fpa_id);
        }

        if (isset($p->msc_id)) {
            $lot->setMsc_id($p->msc_id);
        }

        if (isset($p->ejercicio)) {
            $lot->setEjercicio($p->ejercicio);
        }

        if (isset($p->lot_id)) {
            $lot->setLot_id($p->lot_id);
        }

        if (isset($p->usr_id)) {
            $lot->setUsr_id($p->usr_id);
        }

        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $bit_observacion = 'lay_id:' . $p->lay_id . ' fpa:' . $p->fpa_id . 'msc_id:' . $p->msc_id . ' ejercicio:' . $p->ejercicio . 'lot:' . $p->lot_id;
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a":

                $lot->conectar();
                $bit_observacion = 'REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->insert(false);
                return $lot->getUltimoId();
                break;
            case "b":$lot->delete();
                break;
            case "c":$lot->update();
                break;
        }
    }

}
