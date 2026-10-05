<?php

require_once dirname(__FILE__) . '/../Entidad.php';

class TipoModificacion extends Entidad
{
    /*     * ****************************************************
        ATRIBUTOS DE LA CLASE
     * **************************************************** */
    protected $tmd_id; //Clave identificadora del tipo de modificacion presupuestal
    protected $nombre; //Descripcion del tipo de modificacion presupuestal
    protected $usr_id; //Clave identificadora del usuario que inserto el registro

    /*     * ****************************************************
        METODOS GET
     * **************************************************** */

    public function getTmd_id()
    {
        return $this->tmd_id->getVal();
    }

    public function getNombre()
    {
        return $this->nombre->getVal();
    }

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    /*     * ****************************************************
        METODOS SET
     * **************************************************** */

    public function setTmd_id($val)
    {
        $this->tmd_id->setVal($val);
    }

    public function setNombre($val)
    {
        $this->nombre->setVal($val);
    }

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    /*     * ****************************************************
        METODOS STA (SET sin persistencia)
     * **************************************************** */

    public function staTmd_id($val)
    {
        $this->tmd_id->staVal($val);
    }

    public function staNombre($val)
    {
        $this->nombre->staVal($val);
    }

    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }
    /*     * ****************************************************
        CONSTRUCTOR
     * **************************************************** */

    public function __construct()
    {

        parent::__construct(['tabla' => 'cat_TipoModificacion']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->tmd_id = new Atributo('cadena', 'NO', 'PRI', 'tmd_id');
        $this->nombre = new Atributo('cadena', 'NO', '', 'tmd_nombre');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        $prm = (func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0)
                :
            (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null;
        $tmd_id = (!isset($prm->tmd_id)) ? '' : $prm->tmd_id;

        if ($tmd_id != '') {
            $this->setTmd_id($tmd_id);
            $this->select();
        }
    }
    /*     * ****************************************************
        FUNCIONES EST�TICAS
     * **************************************************** */

    public static function abc($p)
    {
        $tmd = new TipoModificacion();
        if (isset($p->tmd_id)) {
            $tmd->setTmd_id($p->tmd_id);
        }
        if (isset($p->nombre)) {
            $tmd->setNombre($p->nombre);
        }
        if (isset($p->usr_id)) {
            $tmd->setUsr_id($p->usr_id);
        }
        switch ($p->abc) {
            case 'a':
                $tmd->insert();
                return $tmd->getUltimoId();
                break;
            case 'b':
                $tmd->delete();
                break;
            case 'c':
                $tmd->update();
                break;
        }
    }
}
