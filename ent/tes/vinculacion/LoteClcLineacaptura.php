<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class LoteClcLineacaptura extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $fpa_id; //
    protected $msc_id; //
    protected $lot_ejercicio; //
    protected $lot_id; //
    protected $gpo_id; //
    protected $ur_id; //
    protected $clc_id; //
    protected $lca_id; //
    protected $lot_importe; //
    protected $usr_id; //

    /******************************************************

    METODOS GET

     ******************************************************/

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

    public function getGpo_id()
    {
        return $this->gpo_id->getVal();
    }

    public function getUr_id()
    {
        return $this->ur_id->getVal();
    }

    public function getClc_id()
    {
        return $this->clc_id->getVal();
    }

    public function getLca_id()
    {
        return $this->lca_id->getVal();
    }

    public function getImporte()
    {
        return $this->lot_importe->getVal();
    }

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

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

    public function setGpo_id($val)
    {
        $this->gpo_id->setVal($val);
    }

    public function setUr_id($val)
    {
        $this->ur_id->setVal($val);
    }

    public function setClc_id($val)
    {
        $this->clc_id->setVal($val);
    }

    public function setLca_id($val)
    {
        $this->lca_id->setVal($val);
    }

    public function setImporte($val)
    {
        $this->lot_importe->setVal($val);
    }

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

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
    public function staGpo_id($val)
    {
        $this->gpo_id->staVal($val);
    }
    public function staUr_id($val)
    {
        $this->ur_id->staVal($val);
    }
    public function staClc_id($val)
    {
        $this->clc_id->staVal($val);
    }
    public function staLca_id($val)
    {
        $this->lca_id->staVal($val);
    }
    public function staImporte($val)
    {
        $this->lot_importe->staVal($val);
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

        parent::__construct(array("tabla" => BD_TESORERIA.".vin_LoteClcLineaCaptura"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->fpa_id = new Atributo("numero", "NO", "PRI", "fpa_id");
        $this->msc_id = new Atributo("numero", "NO", "PRI", "msc_id");
        $this->lot_ejercicio = new Atributo("numero", "NO", "PRI", "lot_ejercicio");
        $this->lot_id = new Atributo("numero", "NO", "PRI", "lot_id");
        $this->gpo_id = new Atributo("numero", "NO", "PRI", "gpo_id");
        $this->ur_id = new Atributo("numero", "NO", "PRI", "ur_id");
        $this->clc_id = new Atributo("numero", "NO", "PRI", "clc_id");
        $this->lca_id = new Atributo("numero", "NO", "MUL", "lca_id");
        $this->lot_importe = new Atributo("numero", "NO", "", "lot_importe");
        $this->usr_id = new Atributo("numero", "NO", "MUL", "usr_id");

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $lot = new LoteClcLineaCaptura();
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

        if (isset($p->gpo_id)) {
            $lot->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $lot->setUr_id($p->ur_id);
        }

        if (isset($p->clc_id)) {
            $lot->setClc_id($p->clc_id);
        }

        if (isset($p->lca_id)) {
            $lot->setLca_id($p->lca_id);
        }

        if (isset($p->importe)) {
            $lot->setImporte($p->importe);
        }

        if (isset($p->usr_id)) {
            $lot->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case "a":
                $lot->insert();
                return $lot->getUltimoId();
                break;
            case "b":
                $lot->delete();
                break;
            case "c":
                $lot->update();
                break;
        }
    }
}
