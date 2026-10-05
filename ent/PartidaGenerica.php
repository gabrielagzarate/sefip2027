<?php

require_once dirname(__FILE__) . "/Entidad.php";

class PartidaGenerica extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $pg_id; //Identificador de la Partida Genérica del gasto
    protected $pg_nombre; //Nombre de la partida genérica de gasto
    protected $pg_descripcion; //Descripción del partida genérica.
    protected $pg_clave; //Clave de la partida genérica de gasto
    protected $cap_id; //Identificador del capitulo de gasto cat_Concepto - Llave foranea
    protected $cto_id; //Identificador del capitulo de gasto cat_Concepto - Llave foranea
    protected $usr_id; //Identificador de usuario que captura/actualiza el registro

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getPg_id()
    {return $this->pg_id->getVal();}
    public function getNombre()
    {return $this->pg_nombre->getVal();}
    public function getDescripcion()
    {return $this->pg_descripcion->getVal();}
    public function getClave()
    {return $this->pg_clave->getVal();}
    public function getCap_id()
    {return $this->cap_id->getVal();}
    public function getCto_id()
    {return $this->cto_id->getVal();}
    public function getUsr_id()
    {return $this->usr_id->getVal();}

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setPg_id($val)
    {$this->pg_id->setVal($val);}
    public function setNombre($val)
    {$this->pg_nombre->setVal($val);}
    public function setDescripcion($val)
    {$this->pg_descripcion->setVal($val);}
    public function setClave($val)
    {$this->pg_clave->setVal($val);}
    public function setCap_id($val)
    {$this->cap_id->setVal($val);}
    public function setCto_id($val)
    {$this->cto_id->setVal($val);}
    public function setUsr_id($val)
    {$this->usr_id->setVal($val);}

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staPg_id($val)
    {$this->pg_id->staVal($val);}
    public function staNombre($val)
    {$this->pg_nombre->staVal($val);}
    public function staDescripcion($val)
    {$this->pg_descripcion->staVal($val);}
    public function staClave($val)
    {$this->pg_clave->staVal($val);}
    public function staCap_id($val)
    {$this->cap_id->staVal($val);}
    public function staCto_id($val)
    {$this->cto_id->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(array("tabla" => "cat_PartidaGenerica"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->pg_id = new Atributo("numero", "NO", "PRI", "pg_id");
        $this->pg_nombre = new Atributo("cadena", "NO", "", "pg_nombre");
        $this->pg_descripcion = new Atributo("cadena", "NO", "", "pg_descripcion");
        $this->pg_clave = new Atributo("cadena", "NO", "", "pg_clave");
        $this->cap_id = new Atributo("numero", "NO", "MUL", "cap_id");
        $this->cto_id = new Atributo("numero", "NO", "", "cto_id");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $pg = new PartidaGenerica();
        if (isset($p->pg_id)) {
            $pg->setPg_id($p->pg_id);
        }

        if (isset($p->pg_nombre)) {
            $pg->setNombre($p->pg_nombre);
        }

        if (isset($p->pg_descripcion)) {
            $pg->setDescripcion($p->pg_descripcion);
        }

        if (isset($p->pg_clave)) {
            $pg->setClave($p->pg_clave);
        }

        if (isset($p->cap_id)) {
            $pg->setCap_id($p->cap_id);
        }

        if (isset($p->cto_id)) {
            $pg->setCto_id($p->cto_id);
        }

        if (isset($p->usr_id)) {
            $pg->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case "a":$pg->insert();return $pg->getUltimoId();
                break;
            case "b":$pg->delete();
                break;
            case "c":$pg->update();
                break;
        }
    }

}
