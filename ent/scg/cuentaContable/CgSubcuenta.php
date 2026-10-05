<?php
require_once dirname(__FILE__) . "/../../Entidad.php";

class CgSubcuenta extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $ger_id; //cat_GeneroConac - Llave foranea - Identificador del Genero CONAC
    protected $cgp_id; //cat_GrupoConac - Llave foranea - Identificador del Grupo CONAC
    protected $rub_id; //cat_RubroConac - Llave foranea - Identificador del Rubro CONAC
    protected $cgcu_id; //cat_CuentaConac - Llave foranea - Identificador de la Cuenta CONAC
    protected $cgsc_id; //Identificador de la Subcuenta CONAC
    protected $nombre; //Nombre de la Subcuenta de la Cuenta Contable
    protected $cuenta; //Formato de la Cuenta Contable
    protected $descripcion; //Descripción de la Cuenta Contable
    protected $naturalezaFija; //Determina si la Cuenta Contable podrá ser usada con otra naturaleza
    protected $positivoFijo; //Determina si la Cuenta Contable solo podra ser usada en positivo
    protected $nat_id; //cat_NaturalezaCuenta - Llave foranea - Identificador de la Naturaleza
    protected $ecu_id; //cat_EstructuraCuenta - Llave foranea - Identificador de la Estructura de la Cuenta Contable
    protected $tcu_id; //cat_TipoCuenta - Llave foranea - Identificador del Tipo de la Cuenta Contable
    protected $pcu_id; //cat_PosicionCuenta - Llave foranea - Identificador de la Posición de la Cuenta Contable
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
    public function getNombre()
    {return $this->nombre->getVal();}
    public function getCuenta()
    {return $this->cuenta->getVal();}
    public function getDescripcion()
    {return $this->descripcion->getVal();}
    public function getNaturalezaFija()
    {return $this->naturalezaFija->getVal();}
    public function getPositivoFijo()
    {return $this->positivoFijo->getVal();}
    public function getNat_id()
    {return $this->nat_id->getVal();}
    public function getEcu_id()
    {return $this->ecu_id->getVal();}
    public function getTcu_id()
    {return $this->tcu_id->getVal();}
    public function getPcu_id()
    {return $this->pcu_id->getVal();}
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
    public function setNombre($val)
    {$this->nombre->setVal($val);}
    public function setCuenta($val)
    {$this->cuenta->setVal($val);}
    public function setDescripcion($val)
    {$this->descripcion->setVal($val);}
    public function setNaturalezaFija($val)
    {$this->naturalezaFija->setVal($val);}
    public function setPositivoFijo($val)
    {$this->positivoFijo->setVal($val);}
    public function setNat_id($val)
    {$this->nat_id->setVal($val);}
    public function setEcu_id($val)
    {$this->ecu_id->setVal($val);}
    public function setTcu_id($val)
    {$this->tcu_id->setVal($val);}
    public function setPcu_id($val)
    {$this->pcu_id->setVal($val);}
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
    public function staNombre($val)
    {$this->nombre->staVal($val);}
    public function staCuenta($val)
    {$this->cuenta->staVal($val);}
    public function staDescripcion($val)
    {$this->descripcion->staVal($val);}
    public function staNaturalezaFija($val)
    {$this->naturalezaFija->staVal($val);}
    public function staPositivoFijo($val)
    {$this->positivoFijo->staVal($val);}
    public function staNat_id($val)
    {$this->nat_id->staVal($val);}
    public function staEcu_id($val)
    {$this->ecu_id->staVal($val);}
    public function staTcu_id($val)
    {$this->tcu_id->staVal($val);}
    public function staPcu_id($val)
    {$this->pcu_id->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(array("tabla" => "cat_CgSubcuenta"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->ger_id = new Atributo("numero", "NO", "PRI", "ger_id");
        $this->cgp_id = new Atributo("numero", "NO", "PRI", "cgp_id");
        $this->rub_id = new Atributo("numero", "NO", "PRI", "rub_id");
        $this->cgcu_id = new Atributo("numero", "NO", "PRI", "cgcu_id");
        $this->cgsc_id = new Atributo("numero", "NO", "PRI", "cgsc_id");
        $this->nombre = new Atributo("cadena", "NO", "", "cgsc_nombre");
        $this->cuenta = new Atributo("cadena", "NO", "", "cgsc_cuenta");
        $this->descripcion = new Atributo("cadena", "NO", "", "cgsc_descripcion");
        $this->naturalezaFija = new Atributo("numero", "NO", "", "cgsc_naturalezaFija");
        $this->positivoFijo = new Atributo("numero", "NO", "", "cgsc_positivoFijo");
        $this->nat_id = new Atributo("cadena", "NO", "MUL", "nat_id");
        $this->ecu_id = new Atributo("numero", "NO", "MUL", "ecu_id");
        $this->tcu_id = new Atributo("cadena", "NO", "MUL", "tcu_id");
        $this->pcu_id = new Atributo("cadena", "NO", "MUL", "pcu_id");
        $this->usr_id = new Atributo("numero", "NO", "MUL", "usr_id");

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $cgsc = new CgSubcuenta();
        if (isset($p->ger_id)) {
            $cgsc->setGer_id($p->ger_id);
        }

        if (isset($p->cgp_id)) {
            $cgsc->setCgp_id($p->cgp_id);
        }

        if (isset($p->rub_id)) {
            $cgsc->setRub_id($p->rub_id);
        }

        if (isset($p->cgcu_id)) {
            $cgsc->setCgcu_id($p->cgcu_id);
        }

        if (isset($p->cgsc_id)) {
            $cgsc->setCgsc_id($p->cgsc_id);
        }

        if (isset($p->nombre)) {
            $cgsc->setNombre($p->nombre);
        }

        if (isset($p->cuenta)) {
            $cgsc->setCuenta($p->cuenta);
        }

        if (isset($p->descripcion)) {
            $cgsc->setDescripcion($p->descripcion);
        }

        if (isset($p->naturalezaFija)) {
            $cgsc->setNaturalezaFija($p->naturalezaFija);
        }

        if (isset($p->positivoFijo)) {
            $cgsc->setPositivoFijo($p->positivoFijo);
        }

        if (isset($p->nat_id)) {
            $cgsc->setNat_id($p->nat_id);
        }

        if (isset($p->ecu_id)) {
            $cgsc->setEcu_id($p->ecu_id);
        }

        if (isset($p->tcu_id)) {
            $cgsc->setTcu_id($p->tcu_id);
        }

        if (isset($p->pcu_id)) {
            $cgsc->setPcu_id($p->pcu_id);
        }

        if (isset($p->usr_id)) {
            $cgsc->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case "a":$cgsc->insert();return $cgsc->getUltimoId();
                break;
            case "b":$cgsc->delete();
                break;
            case "c":$cgsc->update();
                break;
        }
    }

}
