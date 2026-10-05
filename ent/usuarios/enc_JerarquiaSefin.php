<?php

require_once dirname(__FILE__) . '/../Entidad.php';

class EncJerarquiaSefin extends Entidad
{
    /******************************************************

        ATRIBUTOS DE LA CLASE

    ******************************************************/

    protected $ejs_id;
    protected $ssc_id;
    protected $dir_id;
    protected $uni_id;
    protected $dep_id;

    /******************************************************

        METODOS GET

    ******************************************************/

    public function getEjs_id()
    {
        return $this->ejs_id->getVal();
    }
    public function getSsc_id()
    {
        return $this->ssc_id->getVal();
    }
    public function getDir_id()
    {
        return $this->dir_id->getVal();
    }
    public function getUni_id()
    {
        return $this->uni_id->getVal();
    }
    public function getDep_id()
    {
        return $this->dep_id->getVal();
    }

    /******************************************************

        METODOS SET

    ******************************************************/

    public function setEjs_id($val)
    {
        $this->ejs_id->setVal($val);
    }
    public function setSsc_id($val)
    {
        $this->ssc_id->setVal($val);
    }
    public function setDir_id($val)
    {
        $this->dir_id->setVal($val);
    }
    public function setUni_id($val)
    {
        $this->uni_id->setVal($val);
    }
    public function setDep_id($val)
    {
        $this->dep_id->setVal($val);
    }

    /******************************************************

        METODOS STA (SET sin persistencia)

    ******************************************************/

    public function staEjs_id($val)
    {
        $this->ejs_id->staVal($val);
    }
    public function staSsc_id($val)
    {
        $this->ssc_id->staVal($val);
    }
    public function staDir_id($val)
    {
        $this->dir_id->staVal($val);
    }
    public function staUni_id($val)
    {
        $this->uni_id->staVal($val);
    }
    public function staDep_id($val)
    {
        $this->dep_id->staVal($val);
    }

    /******************************************************

        CONSTRUCTOR

    ******************************************************/

    public function __construct()
    {

        parent::__construct(['tabla' => BD_CTRL . '.enc_JerarquiaSefin']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->ejs_id = new Atributo('numero', 'NO', 'PRI', 'ejs_id');
        $this->ssc_id = new Atributo('numero', 'NO', 'MUL', 'ssc_id');
        $this->dir_id = new Atributo('numero', 'YES', '', 'dir_id');
        $this->uni_id = new Atributo('numero', 'YES', '', 'uni_id');
        $this->dep_id = new Atributo('numero', 'NO', '', 'dep_id');

    }


    /******************************************************

        FUNCIONES ESTÁTICAS

    ******************************************************/
    // Como -en teoría- no haremos ABC en ésta tabla... no pondremos las funciones para eso

}


// Fin del código
