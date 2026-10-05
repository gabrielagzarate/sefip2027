<?php
require_once dirname(__FILE__) . "/../../Entidad.php";

class AgrupacionCuentas extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $agc_id; //Clave identificadora de la agrupación
    protected $agc_nombre; //Nombre de la agrupación
    protected $agc_descripcion; //Nombre de la agrupación
    protected $usr_id; //Clave del usuario que insertó el registro

    /******************************************************
    METODOS GET
     ******************************************************/

    public function getAgc_id()
    {return $this->agc_id->getVal();}
    public function getNombre()
    {return $this->agc_nombre->getVal();}
    public function getDescripcion()
    {return $this->agc_descripcion->getVal();}
    public function getUsr_id()
    {return $this->usr_id->getVal();}

    /******************************************************
    METODOS SET
     ******************************************************/

    public function setAgc_id($val)
    {$this->agc_id->setVal($val);}
    public function setNombre($val)
    {$this->agc_nombre->setVal($val);}
    public function setDescripcion($val)
    {$this->agc_descripcion->setVal($val);}
    public function setUsr_id($val)
    {$this->usr_id->setVal($val);}

    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/

    public function staAgc_id($val)
    {$this->agc_id->staVal($val);}
    public function staNombre($val)
    {$this->agc_nombre->staVal($val);}
    public function staDescripcion($val)
    {$this->agc_descripcion->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}

    /******************************************************
    CONSTRUCTOR
     ******************************************************/

    public function __construct()
    {
        parent::__construct(array("tabla" => "cat_AgrupacionCuentas"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->agc_id = new Atributo("cadena", "NO", "PRI", "agc_id");
        $this->agc_nombre = new Atributo("cadena", "NO", "", "agc_nombre");
        $this->agc_descripcion = new Atributo("cadena", "NO", "", "agc_descripcion");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        $agc_id = (!isset($prm->agc_id)) ? '' : $prm->agc_id;
        if ($agc_id != '') {
            $this->setAgc_id($agc_id);
            $this->select();
        }
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/

    public static function abc($p)
    {
        $agc = new AgrupacionCuentas();
        if (isset($p->agc_id)) {
            $agc->setAgc_id($p->agc_id);
        }

        if (isset($p->nombre)) {
            $agc->setNombre($p->nombre);
        }

        if (isset($p->descripcion)) {
            $agc->setDescripcion($p->descripcion);
        }

        if (isset($p->usr_id)) {
            $agc->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case "a":
                $agc->insert();
                return $agc->getUltimoId();
                break;
            case "b":
                $agc->delete();
                break;
            case "c":
                $agc->update();
                break;
        }
    }
}
