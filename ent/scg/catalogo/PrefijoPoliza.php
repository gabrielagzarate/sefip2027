<?php
require_once dirname(__FILE__) . "/../../Entidad.php";

class PrefijoPoliza extends Entidad
{

    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/

    protected $prf_id; //Clave identificadora del prefijo
    protected $prf_nombre; //Nombre del prefijo
    protected $prf_alias; //
    protected $usr_id; //Clave del usuario que insertÃ³ el registro

    /******************************************************
    METODOS GET
     ******************************************************/

    public function getPrf_id()
    {return $this->prf_id->getVal();}
    public function getNombre()
    {return $this->prf_nombre->getVal();}
    public function getAlias()
    {return $this->prf_alias->getVal();}
    public function getUsr_id()
    {return $this->usr_id->getVal();}

    /******************************************************
    METODOS SET
     ******************************************************/

    public function setPrf_id($val)
    {$this->prf_id->setVal($val);}
    public function setNombre($val)
    {$this->prf_nombre->setVal($val);}
    public function setAlias($val)
    {$this->prf_alias->setVal($val);}
    public function setUsr_id($val)
    {$this->usr_id->setVal($val);}

    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/

    public function staPrf_id($val)
    {$this->prf_id->staVal($val);}
    public function staNombre($val)
    {$this->prf_nombre->staVal($val);}
    public function staAlias($val)
    {$this->prf_alias->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}

    /******************************************************
    CONSTRUCTOR
     ******************************************************/

    public function __construct()
    {
        parent::__construct(array("tabla" => "cat_PrefijoPoliza"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->prf_id = new Atributo("cadena", "NO", "PRI", "prf_id");
        $this->prf_nombre = new Atributo("cadena", "NO", "", "prf_nombre");
        $this->prf_alias = new Atributo("cadena", "NO", "", "prf_alias");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        $prf_id = (!isset($prm->prf_id)) ? '' : $prm->prf_id;
        if ($prf_id != '') {
            $this->setPrf_id($prf_id);
            $this->select();
        }
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/

    public static function abc($p)
    {
        $prf = new PrefijoPoliza();
        if (isset($p->prf_id)) {
            $prf->setPrf_id($p->prf_id);
        }

        if (isset($p->prf_nombre)) {
            $prf->setNombre($p->prf_nombre);
        }

        if (isset($p->prf_alias)) {
            $prf->setAlias($p->prf_alias);
        }

        if (isset($p->usr_id)) {
            $prf->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case "a":
                $prf->insert();
                return $prf->getUltimoId();
                break;
            case "b":
                $prf->delete();
                break;
            case "c":
                $prf->update();
                break;
        }
    }

}
//
