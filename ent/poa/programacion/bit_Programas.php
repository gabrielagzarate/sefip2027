<?php

require_once dirname(__FILE__) . '../../../Entidad.php';

class bit_Programas extends Entidad
{
    /******************************************************

        ATRIBUTOS DE LA CLASE

    ******************************************************/

    protected $bit_id;	//
    protected $prgs_id;	//
    protected $epr_id; //
    protected $bit_observacion;	//
    protected $usr_ip;	//
    protected $usr_id;	//

    /******************************************************

        METODOS GET

    ******************************************************/

    public function getBit_id()
    {
        return $this->bit_id->getVal();
    }
    public function getPrgs_id()
    {
        return $this->prgs_id->getVal();
    }
    public function getEpr_id()
    {
        return $this->epr_id->getVal();
    }
    public function getBit_observacion()
    {
        return $this->bit_observacion->getVal();
    }
    public function getUsr_ip()
    {
        return $this->usr_ip->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

        METODOS SET

    ******************************************************/

    public function setBit_id($val)
    {
        $this->bit_id->setVal($val);
    }
    public function setPrgs_id($val)
    {
        $this->prgs_id->setVal($val);
    }
    public function setEpr_id($val)
    {
        $this->epr_id->setVal($val);
    }
    public function setBit_observacion($val)
    {
        $this->bit_observacion->setVal($val);
    }
    public function setUsr_ip($val)
    {
        $this->usr_ip->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

        METODOS STA (SET sin persistencia)

    ******************************************************/

    public function staBit_id($val)
    {
        $this->bit_id->staVal($val);
    }
    public function staPrgs_id($val)
    {
        $this->prgs_id->staVal($val);
    }
    public function staEpr_id($val)
    {
        $this->epr_id->staVal($val);
    }
    public function staBit_observacion($val)
    {
        $this->bit_observacion->staVal($val);
    }
    public function staUsr_ip($val)
    {
        $this->usr_ip->staVal($val);
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

        parent::__construct(['tabla' => '' . BD_BITACORA . '.bit_Programas']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->bit_id = new Atributo('numero', 'NO', 'PRI', 'bit_id');
        $this->prgs_id = new Atributo('numero', 'NO', '', 'prgs_id');
        $this->epr_id = new Atributo('numero', 'NO', '', 'epr_id');
        $this->bit_observacion = new Atributo('cadena', 'NO', '', 'bit_observacion');
        $this->usr_ip = new Atributo('numero', 'NO', '', 'usr_ip');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object)func_get_arg(0) : null) : null);
        $prg_id = (!isset($prm->prgs_id)) ? '' : $prm->prgs_id;

        if ($prg_id != '') {
            $this->setPrgs_id($prg_id);
            $this->select();
        }
    }

    /******************************************************

        FUNCIONES ESTÁTICAS

    ******************************************************/

    public static function abc($p)
    {
        $bit = new bit_Programas();
        if (isset($p->bit_id)) {
            $bit->setBit_id($p->bit_id);
        }
        if (isset($p->prgs_id)) {
            $bit->setPrgs_id($p->prgs_id);
        }
        if (isset($p->epr_id)) {
            $bit->setEpr_id($p->epr_id);
        }
        if (isset($p->bit_observacion)) {
            $bit->setBit_observacion($p->bit_observacion);
        }
        if (isset($p->usr_ip)) {
            $bit->setUsr_ip($p->usr_ip);
        }
        if (isset($p->usr_id)) {
            $bit->setUsr_id($p->usr_id);
        }
        switch ($p->abc) {
            case 'a': $bit->insert();
                return $bit->getUltimoId();
                break;
            case 'b': $bit->delete();
                break;
            case 'c': $bit->update();
                break;
        }
    }
}
