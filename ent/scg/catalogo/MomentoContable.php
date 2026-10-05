<?php
require_once dirname(__FILE__) . "/../../Entidad.php";

class MomentoContable extends Entidad
{

    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/

    protected $mmc_id; //Identificador del momento Contable
    protected $mmc_nombre; //Nombre del momento Contable
    protected $mmc_alias; //Alias del momento contable
    protected $mmc_orden; //
    protected $usr_id; //Identificador de usuario que captura/actualiza el registro

    /******************************************************
    METODOS GET
     ******************************************************/

    public function getMmc_id()
    {
        return $this->mmc_id->getVal();
    }
    public function getNombre()
    {
        return $this->mmc_nombre->getVal();
    }
    public function getAlias()
    {
        return $this->mmc_alias->getVal();
    }
    public function getOrden()
    {
        return $this->mmc_orden->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************
    METODOS SET
     ******************************************************/

    public function setMmc_id($val)
    {
        $this->mmc_id->setVal($val);
    }
    public function setNombre($val)
    {
        $this->mmc_nombre->setVal($val);
    }
    public function setAlias($val)
    {
        $this->mmc_alias->setVal($val);
    }
    public function setOrden($val)
    {
        $this->mmc_orden->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/

    public function staMmc_id($val)
    {
        $this->mmc_id->staVal($val);
    }
    public function staNombre($val)
    {
        $this->mmc_nombre->staVal($val);
    }
    public function staAlias($val)
    {
        $this->mmc_alias->staVal($val);
    }
    public function staOrden($val)
    {
        $this->mmc_orden->staVal($val);
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
        parent::__construct(array("tabla" => "cat_MomentoContable"));
        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->mmc_id = new Atributo("cadena", "NO", "PRI", "mmc_id");
        $this->mmc_nombre = new Atributo("cadena", "NO", "", "mmc_nombre");
        $this->mmc_alias = new Atributo("cadena", "NO", "", "mmc_alias");
        $this->mmc_orden = new Atributo("numero", "NO", "", "mmc_orden");
        $this->usr_id = new Atributo("numero", "NO", "MUL", "usr_id");

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object)func_get_arg(0) : null) : null);
        $mmc_id = (!isset($prm->mmc_id)) ? '' : $prm->mmc_id;
        if ($mmc_id != '') {
            $this->setMmc_id($mmc_id);
            $this->select();
        }
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/

    public static function abc($p)
    {
        $mmc = new MomentoContable();
        if (isset($p->mmc_id)) {
            $mmc->setMmc_id($p->mmc_id);
        }

        if (isset($p->mmc_nombre)) {
            $mmc->setNombre($p->mmc_nombre);
        }

        if (isset($p->mmc_alias)) {
            $mmc->setAlias($p->mmc_alias);
        }

        if (isset($p->mmc_orden)) {
            $mmc->setOrden($p->mmc_orden);
        }

        if (isset($p->usr_id)) {
            $mmc->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case "a":
                $mmc->insert();
                return $mmc->getUltimoId();
                break;
            case "b":
                $mmc->delete();
                break;
            case "c":
                $mmc->update();
                break;
        }
    }
}
