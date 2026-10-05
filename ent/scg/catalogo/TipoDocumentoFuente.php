<?php
require_once dirname(__FILE__) . "/../../Entidad.php";

class TipoDocumentoFuente extends Entidad
{

    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/

    protected $tdf_id;
    protected $tdf_nombre;
    protected $tdf_alias;
    protected $usr_id;

    /******************************************************
    METODOS GET
     ******************************************************/

    public function getTdf_id()
    {return $this->tdf_id->getVal();}
    public function getNombre()
    {return $this->tdf_nombre->getVal();}
    public function getAlias()
    {return $this->tdf_alias->getVal();}
    public function getUsr_id()
    {return $this->usr_id->getVal();}

    /******************************************************
    METODOS SET
     ******************************************************/

    public function setTdf_id($val)
    {$this->tdf_id->setVal($val);}
    public function setNombre($val)
    {$this->tdf_nombre->setVal($val);}
    public function setAlias($val)
    {$this->tdf_alias->setVal($val);}
    public function setUsr_id($val)
    {$this->usr_id->setVal($val);}

    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/

    public function staTdf_id($val)
    {$this->tdf_id->staVal($val);}
    public function staNombre($val)
    {$this->tdf_nombre->staVal($val);}
    public function staAlias($val)
    {$this->tdf_alias->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}

    /******************************************************
    CONSTRUCTOR
     ******************************************************/

    public function __construct()
    {
        parent::__construct(array("tabla" => "cat_TipoDocumentoFuente"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->tdf_id = new Atributo("cadena", "NO", "PRI", "tdf_id");
        $this->tdf_nombre = new Atributo("cadena", "NO", "", "tdf_nombre");
        $this->tdf_alias = new Atributo("cadena", "NO", "", "tdf_alias");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        $tdf_id = (!isset($prm->tdf_id)) ? '' : $prm->tdf_id;
        if ($tdf_id != '') {
            $this->setTdf_id($tdf_id);
            $this->select();
        }
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/

    public static function abc($p)
    {
        $tdf = new TipoDocumentoFuente();
        if (isset($p->tdf_id)) {
            $tdf->setTdf_id($p->tdf_id);
        }

        if (isset($p->tdf_nombre)) {
            $tdf->setNombre($p->tdf_nombre);
        }

        if (isset($p->tdf_alias)) {
            $tdf->setAlias($p->tdf_alias);
        }

        if (isset($p->usr_id)) {
            $tdf->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case "a":
                $tdf->insert();
                return $tdf->getUltimoId();
                break;
            case "b":
                $tdf->delete();
                break;
            case "c":
                $tdf->update();
                break;
        }
    }

}
