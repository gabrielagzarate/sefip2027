<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class CuentaCuarto extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $ger_id; //cat_GeneroConac - Llave foranea - Identificador del Genero CONAC
    protected $cgp_id; //cat_GrupoConac - Llave foranea - Identificador del Grupo CONAC
    protected $rub_id; //cat_RubroConac - Llave foranea - Identificador del Rubro CONAC
    protected $cgcu_id; //cat_CuentaConac - Llave foranea - Identificador de la Cuenta CONAC
    protected $cgsc_id; //cat_SubcuentaConac - Llave foranea - Identificador de la Subcuenta CONAC
    protected $cgc_id; //Identificador del sexto nivel de la Cuenta Contable
    protected $cgc_cuenta; //Formato de la Cuenta Contable
    protected $cgc_nombre; //Nombre de la Cuenta Contable
    protected $descripcion; //Descripción de la Cuenta Contable
    protected $usr_id; //Identificador de usuario que captura/actualiza el registro

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getGer_id()
    {return $this->ger_id->getVal();}
    public function getCgp_id()
    {return $this->cgp_id->getVal();}
    public function getRub_id()
    {return $this->rub_id->getVal();}
    public function getCgcu_id()
    {return $this->cgcu_id->getVal();}
    public function getCgsc_id()
    {return $this->cgsc_id->getVal();}
    public function getCgc_id()
    {return $this->cgc_id->getVal();}
    public function getCuenta()
    {return $this->cgc_cuenta->getVal();}
    public function getNombre()
    {return $this->cgc_nombre->getVal();}
    public function getDescripcion()
    {return $this->descripcion->getVal();}
    public function getUsr_id()
    {return $this->usr_id->getVal();}

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setGer_id($val)
    {$this->ger_id->setVal($val);}
    public function setCgp_id($val)
    {$this->cgp_id->setVal($val);}
    public function setRub_id($val)
    {$this->rub_id->setVal($val);}
    public function setCgcu_id($val)
    {$this->cgcu_id->setVal($val);}
    public function setCgsc_id($val)
    {$this->cgsc_id->setVal($val);}
    public function setCgc_id($val)
    {$this->cgc_id->setVal($val);}
    public function setCuenta($val)
    {$this->cgc_cuenta->setVal($val);}
    public function setNombre($val)
    {$this->cgc_nombre->setVal($val);}
    public function setDescripcion($val)
    {$this->descripcion->setVal($val);}
    public function setUsr_id($val)
    {$this->usr_id->setVal($val);}

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staGer_id($val)
    {$this->ger_id->staVal($val);}
    public function staCgp_id($val)
    {$this->cgp_id->staVal($val);}
    public function staRub_id($val)
    {$this->rub_id->staVal($val);}
    public function staCgcu_id($val)
    {$this->cgcu_id->staVal($val);}
    public function staCgsc_id($val)
    {$this->cgsc_id->staVal($val);}
    public function staCgc_id($val)
    {$this->cgc_id->staVal($val);}
    public function staCuenta($val)
    {$this->cuenta->staVal($val);}
    public function staNombre($val)
    {$this->nombre->staVal($val);}
    public function staDescripcion($val)
    {$this->descripcion->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(array("tabla" => "cg_CuentaCuarto"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->ger_id = new Atributo("numero", "NO", "PRI", "ger_id");
        $this->cgp_id = new Atributo("numero", "NO", "PRI", "cgp_id");
        $this->rub_id = new Atributo("numero", "NO", "PRI", "rub_id");
        $this->cgcu_id = new Atributo("numero", "NO", "PRI", "cgcu_id");
        $this->cgsc_id = new Atributo("numero", "NO", "PRI", "cgsc_id");
        $this->cgc_id = new Atributo("cadena", "NO", "PRI", "cgc_id");
        $this->cgc_cuenta = new Atributo("cadena", "NO", "UNI", "cgc_cuenta");
        $this->cgc_nombre = new Atributo("cadena", "NO", "", "cgc_nombre");
        $this->descripcion = new Atributo("cadena", "NO", "", "cgc_descripcion");
        $this->usr_id = new Atributo("numero", "NO", "MUL", "usr_id");

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $cgc = new CuentaCuarto();
        if (isset($p->ger_id)) {
            $cgc->setGer_id($p->ger_id);
        }

        if (isset($p->cgp_id)) {
            $cgc->setCgp_id($p->cgp_id);
        }

        if (isset($p->rub_id)) {
            $cgc->setRub_id($p->rub_id);
        }

        if (isset($p->cgcu_id)) {
            $cgc->setCgcu_id($p->cgcu_id);
        }

        if (isset($p->cgsc_id)) {
            $cgc->setCgsc_id($p->cgsc_id);
        }

        if (isset($p->cgc_id)) {
            $cgc->setCgc_id($p->cgc_id);
        }

        if (isset($p->cuenta)) {
            $cgc->setCuenta($p->cuenta);
        }

        if (isset($p->nombre)) {
            $cgc->setNombre($p->nombre);
        }

        if (isset($p->descripcion)) {
            $cgc->setDescripcion($p->descripcion);
        }

        if (isset($p->usr_id)) {
            $cgc->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case "a":$cgc->insert();return $cgc->getUltimoId();
                break;
            case "b":$cgc->delete();
                break;
            case "c":$cgc->update();
                break;
        }
    }

}
