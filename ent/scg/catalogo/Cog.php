<?php
require_once dirname(__FILE__) . "/../../Entidad.php";
require_once dirname(__FILE__) . "/../../PartidaGenerica.php";

class Cog extends Entidad
{

    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $pg_id; //Partida Generica
    protected $ger_id; //Genero - Cuenta CONAC
    protected $cgp_id; //Grupo - Cuenta CONAC
    protected $rub_id; //Rubro - Cuenta CONAC
    protected $cgcu_id; //Cuenta - Cuenta CONAC
    protected $cgsc_id; //Subcuenta - Cuenta CONAC
    protected $cog_tipo; //R->Resultados - B->Balance
    protected $cog_cuenta; //Cuenta contable
    protected $usr_id; //Usuario

    /******************************************************
    METODOS GET
     ******************************************************/
    public function getPg_id()
    {return $this->pg_id->getVal();}

    public function getPg_nombre()
    {
        $generica = new PartidaGenerica();
        $generica->setPg_id($this->getPg_id());
        $generica->select();

        return $generica->getNombre();
    }
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
    /*NO EXISTE ENTIDAD, NO TIENE SENTIDO //REVISAR */
    /*public function getCgm_nombre()
    {
    $mayor = new CuentaMayor();
    $mayor->setGer_id($this->getGer_id());
    $mayor->setCgp_id($this->getCgp_id());
    $mayor->setRub_id($this->getRub_id());
    $mayor->setCgcu_id($this->getCgcu_id());
    $mayor->setCgsc_id($this->getCgsc_id());
    $mayor->select();

    return $mayor->getNombre();
    }*/
    public function getTipo()
    {return $this->cog_tipo->getVal();}
    public function getCuenta()
    {return $this->cog_cuenta->getVal();}
    public function getUsr_id()
    {return $this->usr_id->getVal();}

    /******************************************************
    METODOS SET
     ******************************************************/

    public function setPg_id($val)
    {$this->pg_id->setVal($val);}
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
    public function setTipo($val)
    {$this->cog_tipo->setVal($val);}
    public function setUsr_id($val)
    {$this->usr_id->setVal($val);}

    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/

    public function staPg_id($val)
    {$this->pg_id->staVal($val);}
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
    public function staTipo($val)
    {$this->cog_tipo->staVal($val);}
    public function staCuenta($val)
    {$this->cog_cuenta->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}

    /******************************************************
    CONSTRUCTOR
     ******************************************************/

    public function __construct()
    {
        parent::__construct(array("tabla" => "vin_Cog"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->pg_id = new Atributo("numero", "NO", "PRI", "pg_id");
        $this->ger_id = new Atributo("numero", "NO", "PRI", "ger_id");
        $this->cgp_id = new Atributo("numero", "NO", "PRI", "cgp_id");
        $this->rub_id = new Atributo("numero", "NO", "PRI", "rub_id");
        $this->cgcu_id = new Atributo("numero", "NO", "PRI", "cgcu_id");
        $this->cgsc_id = new Atributo("numero", "NO", "PRI", "cgsc_id");
        $this->cog_tipo = new Atributo("cadena", "NO", "", "cog_tipo");
        $this->cog_cuenta = new Atributo("cadena", "NO", "", "cog_cuenta");
        $this->usr_id = new Atributo("numero", "NO", "MUL", "usr_id");

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        $pg_id = (!isset($prm->pg_id)) ? '' : $prm->pg_id;
        $ger_id = (!isset($prm->ger_id)) ? '' : $prm->ger_id;
        $cgp_id = (!isset($prm->cgp_id)) ? '' : $prm->cgp_id;
        $rub_id = (!isset($prm->rub_id)) ? '' : $prm->rub_id;
        $cgcu_id = (!isset($prm->cgcu_id)) ? '' : $prm->cgcu_id;
        $cgsc_id = (!isset($prm->cgsc_id)) ? '' : $prm->cgsc_id;

        if ($pg_id != '' && $ger_id != '' && $cgp_id != '' && $rub_id != '' && $cgcu_id != '' && $cgsc_id != '') {
            $this->setPg_id($pg_id);
            $this->setGer_id($ger_id);
            $this->setCgp_id($cgp_id);
            $this->setRub_id($rub_id);
            $this->setCgcu_id($cgcu_id);
            $this->setCgsc_id($cgsc_id);
            $this->select();

        } elseif ($pg_id != '') {
            $this->setPg_id($pg_id);
        }
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/

    public static function abc($p)
    {
        $cog = new Cog();
        if (isset($p->pg_id)) {
            $cog->setPg_id($p->pg_id);
        }

        if (isset($p->ger_id)) {
            $cog->setGer_id($p->ger_id);
        }

        if (isset($p->cgp_id)) {
            $cog->setCgp_id($p->cgp_id);
        }

        if (isset($p->rub_id)) {
            $cog->setRub_id($p->rub_id);
        }

        if (isset($p->cgcu_id)) {
            $cog->setCgcu_id($p->cgcu_id);
        }

        if (isset($p->cgsc_id)) {
            $cog->setCgsc_id($p->cgsc_id);
        }

        if (isset($p->cog_tipo)) {
            $cog->setTipo($p->cog_tipo);
        }

        if (isset($p->usr_id)) {
            $cog->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case "a":$cog->insert();return $cog->getUltimoId();
                break;
            case "b":$cog->delete();
                break;
            case "c":$cog->update();
                break;
        }
    }
}
