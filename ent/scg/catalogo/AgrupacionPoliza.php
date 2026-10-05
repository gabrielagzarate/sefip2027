<?php
require_once dirname(__FILE__) . "/../../Entidad.php"; //require_once dirname(__FILE__)."/Entidad.php";

class AgrupacionPoliza extends Entidad
{

    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/

    protected $apg_id; //Clave identificadora de la agrupación
    protected $apg_nombre; //Nombre de la agrupación
    protected $apg_alias; //Alias de la agrupación
    protected $usr_id; //Clave del usuario que insertó el registro

    /******************************************************
    METODOS GET
     ******************************************************/

    public function getApg_id()
    {return $this->apg_id->getVal();}
    public function getNombre()
    {return $this->apg_nombre->getVal();}
    public function getAlias()
    {return $this->apg_alias->getVal();}
    public function getUsr_id()
    {return $this->usr_id->getVal();}

    /******************************************************
    METODOS SET
     ******************************************************/

    public function setApg_id($val)
    {$this->apg_id->setVal($val);}
    public function setNombre($val)
    {$this->apg_nombre->setVal($val);}
    public function setAlias($val)
    {$this->apg_alias->setVal($val);}
    public function setUsr_id($val)
    {$this->usr_id->setVal($val);}

    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/

    public function staApg_id($val)
    {$this->apg_id->staVal($val);}
    public function staNombre($val)
    {$this->apg_nombre->staVal($val);}
    public function staAlias($val)
    {$this->apg_alias->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}

    /******************************************************
    CONSTRUCTOR
     ******************************************************/

    public function __construct()
    {
        parent::__construct(array("tabla" => "cat_AgrupacionPoliza"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->apg_id = new Atributo("cadena", "NO", "PRI", "apg_id");
        $this->apg_nombre = new Atributo("cadena", "NO", "", "apg_nombre");
        $this->apg_alias = new Atributo("cadena", "NO", "", "apg_alias");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object)func_get_arg(0) : null) : null);
        $apg_id = (!isset($prm->apg_id)) ? '' : $prm->apg_id;
        if ($apg_id != '') {
            $this->setApg_id($apg_id);
            $this->select();
        }
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/

    public static function abc($p)
    {

        $apg = new AgrupacionPoliza();
        if (isset($p->apg_id)) {
            $apg->setApg_id($p->apg_id);
        }

        if (isset($p->apg_nombre)) {
            $apg->setNombre($p->apg_nombre);
        }

        if (isset($p->apg_alias)) {
            $apg->setAlias($p->apg_alias);
        }

        if (isset($p->usr_id)) {
            $apg->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case "a":$apg->insert();return $apg->getUltimoId();
                break;
            case "b":$apg->delete();
                break;
            case "c":$apg->update();
                break;
        }
    }

}
