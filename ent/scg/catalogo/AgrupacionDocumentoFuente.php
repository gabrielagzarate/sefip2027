<?php
require_once dirname(__FILE__) . "/../../Entidad.php";

class AgrupacionDocumentoFuente extends Entidad
{

    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/

    protected $adf_id; //Clave Identificadora de la agrupación
    protected $adf_nombre; //Nombre de la agrupación
    protected $adf_alias; //Alias de la agrupación
    protected $usr_id; //Clave del usuario que insertó el registro

    /******************************************************
    METODOS GET
     ******************************************************/

    public function getAdf_id()
    {return $this->adf_id->getVal();}
    public function getNombre()
    {return $this->adf_nombre->getVal();}
    public function getAlias()
    {return $this->adf_alias->getVal();}
    public function getUsr_id()
    {return $this->usr_id->getVal();}

    /******************************************************
    METODOS SET
     ******************************************************/

    public function setAdf_id($val)
    {$this->adf_id->setVal($val);}
    public function setNombre($val)
    {$this->adf_nombre->setVal($val);}
    public function setAlias($val)
    {$this->adf_alias->setVal($val);}
    public function setUsr_id($val)
    {$this->usr_id->setVal($val);}

    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/

    public function staAdf_id($val)
    {$this->adf_id->staVal($val);}
    public function staNombre($val)
    {$this->adf_nombre->staVal($val);}
    public function staAlias($val)
    {$this->adf_alias->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}

    /******************************************************
    CONSTRUCTOR
     ******************************************************/

    public function __construct()
    {
        parent::__construct(array("tabla" => "cat_AgrupacionDocumentoFuente"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->adf_id = new Atributo("cadena", "NO", "PRI", "adf_id");
        $this->adf_nombre = new Atributo("cadena", "NO", "", "adf_nombre");
        $this->adf_alias = new Atributo("cadena", "NO", "", "adf_alias");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        $adf_id = (!isset($prm->adf_id)) ? '' : $prm->adf_id;
        if ($adf_id != '') {
            $this->setAdf_id($adf_id);
            $this->select();
        }
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/

    public static function abc($p)
    {
        $adf = new AgrupacionDocumentoFuente();
        if (isset($p->adf_id)) {
            $adf->setAdf_id($p->adf_id);
        }

        if (isset($p->adf_nombre)) {
            $adf->setNombre($p->adf_nombre);
        }

        if (isset($p->adf_alias)) {
            $adf->setAlias($p->adf_alias);
        }

        if (isset($p->usr_id)) {
            $adf->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case "a":
                $adf->insert();
                return $adf->getUltimoId();
                break;
            case "b":
                $adf->delete();
                break;
            case "c":
                $adf->update();
                break;
        }
    }

}
