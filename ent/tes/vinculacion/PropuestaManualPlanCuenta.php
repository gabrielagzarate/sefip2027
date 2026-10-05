<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class PropuestaManualPlanCuenta extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $lom_id; //
    protected $cta_gpo_id; //cat_Grupo - Llave foranea - Identificador de Grupo de la Clasificación Administrativa
    protected $cta_ur_id; //cat_UnidadResponsable - Llave foranea - Identificador de la Unidad Responsable de la Clasificación Administrativa
    protected $cta_ue_id; //cat_UnidadEjecutora - Llave foranea - Identificador de la Unidad Ejecutora de la Clasificación Administrativa
    protected $ger_id; //cat_GeneroConac - Llave foranea - Identificador del Genero CONAC
    protected $cgp_id; //cat_GrupoConac - Llave foranea - Identificador del Grupo CONAC
    protected $rub_id; //cat_RubroConac - Llave foranea - Identificador del Rubro CONAC
    protected $cgcu_id; //cat_CuentaConac - Llave foranea - Identificador de la Cuenta CONAC
    protected $cgsc_id; //cat_SubcuentaConac - Llave foranea - Identificador de la Subcuenta CONAC
    protected $cgc_id; //cgt_CuentaCuarto - Llave foranea - Identificador de la Cuenta Maestro
    protected $cta_id; //Identificador del Nivel de Registro de la Cuenta Contable
    protected $usr_id; //Identificador de usuario que captura/actualiza el registro

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getLom_id()
    {return $this->lom_id->getVal();}
    public function getCta_gpo_id()
    {return $this->cta_gpo_id->getVal();}
    public function getCta_ur_id()
    {return $this->cta_ur_id->getVal();}
    public function getCta_ue_id()
    {return $this->cta_ue_id->getVal();}
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
    public function getCta_id()
    {return $this->cta_id->getVal();}
    public function getUsr_id()
    {return $this->usr_id->getVal();}

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setLom_id($val)
    {$this->lom_id->setVal($val);}
    public function setCta_gpo_id($val)
    {$this->cta_gpo_id->setVal($val);}
    public function setCta_ur_id($val)
    {$this->cta_ur_id->setVal($val);}
    public function setCta_ue_id($val)
    {$this->cta_ue_id->setVal($val);}
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
    public function setCta_id($val)
    {$this->cta_id->setVal($val);}
    public function setUsr_id($val)
    {$this->usr_id->setVal($val);}

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staLom_id($val)
    {$this->lom_id->staVal($val);}
    public function staCta_gpo_id($val)
    {$this->cta_gpo_id->staVal($val);}
    public function staCta_ur_id($val)
    {$this->cta_ur_id->staVal($val);}
    public function staCta_ue_id($val)
    {$this->cta_ue_id->staVal($val);}
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
    public function staCta_id($val)
    {$this->cta_id->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(array("tabla" => BD_TESORERIA.".vin_PropuestaManualPlanCuenta"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->lom_id = new Atributo("numero", "NO", "PRI", "lom_id");
        $this->cta_gpo_id = new Atributo("numero", "NO", "MUL", "cta_gpo_id");
        $this->cta_ur_id = new Atributo("numero", "NO", "", "cta_ur_id");
        $this->cta_ue_id = new Atributo("numero", "NO", "", "cta_ue_id");
        $this->ger_id = new Atributo("numero", "NO", "", "ger_id");
        $this->cgp_id = new Atributo("numero", "NO", "", "cgp_id");
        $this->rub_id = new Atributo("numero", "NO", "", "rub_id");
        $this->cgcu_id = new Atributo("numero", "NO", "", "cgcu_id");
        $this->cgsc_id = new Atributo("numero", "NO", "", "cgsc_id");
        $this->cgc_id = new Atributo("numero", "NO", "", "cgc_id");
        $this->cta_id = new Atributo("cadena", "NO", "", "cta_id");
        $this->usr_id = new Atributo("numero", "NO", "MUL", "usr_id");

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $vPmpc = new PropuestaManualPlanCuenta();

        $bit_observacion = '';

        if (isset($p->lom_id)) {
            $vPmpc->setLom_id($p->lom_id);
            $bit_observacion .= ' lom_id: ' . $p->lom_id;
        }
        if (isset($p->cta_gpo_id)) {
            $vPmpc->setCta_gpo_id($p->cta_gpo_id);
            $bit_observacion .= ' cta_gpo_id: ' . $p->cta_gpo_id;
        }
        if (isset($p->cta_ur_id)) {
            $vPmpc->setCta_ur_id($p->cta_ur_id);
            $bit_observacion .= ' cta_ur_id: ' . $p->cta_ur_id;
        }
        if (isset($p->cta_ue_id)) {
            $vPmpc->setCta_ue_id($p->cta_ue_id);
            $bit_observacion .= ' cta_ue_id: ' . $p->cta_ue_id;
        }
        if (isset($p->ger_id)) {
            $vPmpc->setGer_id($p->ger_id);
            $bit_observacion .= ' ger_id: ' . $p->ger_id;
        }
        if (isset($p->cgp_id)) {
            $vPmpc->setCgp_id($p->cgp_id);
            $bit_observacion .= ' cgp_id: ' . $p->cgp_id;
        }
        if (isset($p->rub_id)) {
            $vPmpc->setRub_id($p->rub_id);
            $bit_observacion .= ' rub_id: ' . $p->rub_id;
        }
        if (isset($p->cgcu_id)) {
            $vPmpc->setCgcu_id($p->cgcu_id);
            $bit_observacion .= ' cgcu_id: ' . $p->cgcu_id;
        }
        if (isset($p->cgsc_id)) {
            $vPmpc->setCgsc_id($p->cgsc_id);
            $bit_observacion .= ' cgsc_id: ' . $p->cgsc_id;
        }
        if (isset($p->cgc_id)) {
            $vPmpc->setCgc_id($p->cgc_id);
            $bit_observacion .= ' cgc_id: ' . $p->cgc_id;
        }
        if (isset($p->cta_id)) {
            $vPmpc->setCta_id($p->cta_id);
            $bit_observacion .= ' cta_id: ' . $p->cta_id;
        }
        if (isset($p->usr_id)) {
            $vPmpc->setUsr_id($p->usr_id);
            $bit_observacion .= ' usr_id: ' . $p->usr_id;
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a":
                $vPmpc->conectar();
                $bit_observacion = ' REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $vPmpc->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $vPmpc->consultar($params);
                $vPmpc->insert(false);
                return $vPmpc->getUltimoId();
                break;
            case "b":
                $vPmpc->conectar();
                $bit_observacion = ' REGISTRO BORRADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $vPmpc->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $vPmpc->consultar($params);
                $vPmpc->delete(false);
                break;
            case "c":
                $vPmpc->conectar();
                $bit_observacion = ' REGISTRO ACTUALIZADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $vPmpc->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $vPmpc->consultar($params);
                $vPmpc->update(false);
                break;
        }
    }

}
