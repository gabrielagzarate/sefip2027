<?php

require_once dirname(__FILE__) . '/../../Entidad.php';

class Subprogramas extends Entidad
{
    /******************************************************

        ATRIBUTOS DE LA CLASE

    ******************************************************/

    protected $prgs_id;	//
    protected $sprs_id;	//
    protected $pdf_id;	//
    protected $esr_id;	//
    protected $efn_id;	//
    protected $epp_id;	//
    protected $sprs_nombre;	//
    protected $usr_id;	//

    /******************************************************

        METODOS GET

    ******************************************************/

    public function getPrgs_id()
    {
        return $this->prgs_id->getVal();
    }
    public function getSprs_id()
    {
        return $this->sprs_id->getVal();
    }
    public function getPdf_id()
    {
        return $this->pdf_id->getVal();
    }
    public function getEsr_id()
    {
        return $this->esr_id->getVal();
    }
    public function getEfn_id()
    {
        return $this->efn_id->getVal();
    }
    public function getEpp_id()
    {
        return $this->epp_id->getVal();
    }
    public function getSprs_nombre()
    {
        return $this->sprs_nombre->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

        METODOS SET

    ******************************************************/

    public function setPrgs_id($val)
    {
        $this->prgs_id->setVal($val);
    }
    public function setSprs_id($val)
    {
        $this->sprs_id->setVal($val);
    }
    public function setPdf_id($val)
    {
        $this->pdf_id->setVal($val);
    }
    public function setEsr_id($val)
    {
        $this->esr_id->setVal($val);
    }
    public function setEfn_id($val)
    {
        $this->efn_id->setVal($val);
    }
    public function setEpp_id($val)
    {
        $this->epp_id->setVal($val);
    }
    public function setSprs_nombre($val)
    {
        $this->sprs_nombre->setVal($val);
    }

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

        METODOS STA (SET sin persistencia)

    ******************************************************/

    public function staPrgs_id($val)
    {
        $this->prgs_id->staVal($val);
    }
    public function staSprs_id($val)
    {
        $this->sprs_id->staVal($val);
    }
    public function staPdf_id($val)
    {
        $this->pdf_id->staVal($val);
    }
    public function staEsr_id($val)
    {
        $this->esr_id->staVal($val);
    }
    public function staEfn_id($val)
    {
        $this->efn_id->staVal($val);
    }
    public function staEpp_id($val)
    {
        $this->epp_id->staVal($val);
    }
    public function staSprs_nombre($val)
    {
        $this->sprs_nombre->staVal($val);
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

        parent::__construct(['tabla' => 'cat_Subprogramas']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->prgs_id = new Atributo('numero', 'NO', 'PRI', 'prgs_id');
        $this->sprs_id = new Atributo('numero', 'NO', 'PRI', 'sprs_id');
        $this->pdf_id = new Atributo('numero', 'NO', 'MUL', 'pdf_id');
        $this->esr_id = new Atributo('numero', 'NO', 'MUL', 'esr_id');
        $this->efn_id = new Atributo('numero', 'NO', 'MUL', 'efn_id');
        $this->epp_id = new Atributo('numero', 'NO', 'MUL', 'epp_id');
        $this->sprs_nombre = new Atributo('cadena', 'NO', '', 'sprs_nombre');
        ;
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object)func_get_arg(0) : null) : null);

        $prg_id = (!isset($prm->prgs_id)) ? '' : $prm->prgs_id;
        $spr_id = (!isset($prm->sprs_id)) ? '' : $prm->sprs_id;

        if ($prg_id != '' && $spr_id != '') {
            $this->setPrgs_id($prg_id);
            $this->setSprs_id($spr_id);
            $this->select();
        }
    }

    /******************************************************

        FUNCIONES ESTÁTICAS

    ******************************************************/

    public static function abc($p)
    {
        $sprs = new Subprogramas();
        if (isset($p->prgs_id)) {
            $sprs->setPrgs_id($p->prgs_id);
        }
        if (isset($p->sprs_id)) {
            $sprs->setSprs_id($p->sprs_id);
        }
        if (isset($p->sprs_nombre)) {
            $sprs->setSprs_nombre($p->sprs_nombre);
        }
        if (isset($p->pdf_id)) {
            $sprs->setPdf_id($p->pdf_id);
        } elseif ($p->abc == 'a' && $p->prgs_id >= 900) {
            $sprs->setPdf_id('0');
        }
        if (isset($p->esr_id)) {
            $sprs->setEsr_id($p->esr_id);
        }
        if (isset($p->efn_id)) {
            $sprs->setEfn_id($p->efn_id);
        }
        if (isset($p->epp_id)) {
            $sprs->setEpp_id($p->epp_id);
        }
        if (isset($p->usr_id)) {
            $sprs->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case 'a':
                $sprs->conectar();
                $sprs->insert(false);
                $sprs->setSql('SELECT @sprs_id as sprs_id');
                $sprs->consultar();
                $sprs->desconectar();
                $spr = $sprs->extraer();
                return $spr['sprs_id'];
                break;
            case 'b': $sprs->delete();
                break;
            case 'c': $sprs->update();
                break;
        }
    }

    public function sinRegistros()
    {
        if ($this->getPrgs_id() && $this->getSprs_id()) {

            $sql = 'SELECT sprs_id FROM enc_UrSubprogramas WHERE prgs_id = :prgs and sprs_id = :sprs';
            $parametros = [':prgs' => $this->getPrgs_id(), ':sprs' => $this->getSprs_id()];
            $this->setSql($sql);
            $this->conectar();
            $this->consultar($parametros);
            $this->desconectar();
            if ($this->getNumeroRegistros() > 0) {
                return false;
            } else {
                return true;
            }
        }
        return true;
    }
}
