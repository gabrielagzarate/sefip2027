<?php

require_once dirname(__FILE__) . "/../../Entidad.php";
class PlanCuentas extends Entidad
{
    /******************************************************
      ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $cta_gpo_id; //cat_UnidadEjecutora - Llave foranea Clave identificadora del Grupo
    protected $cta_ur_id; //cat_UnidadEjecutora - Llave foranea Clave identificadora de la UR
    protected $cta_ue_id; //cat_UnidadEjecutora - Llave foranea Clave identificadora de la UE
    protected $ger_id; //cat_CgSubcuenta - Llave foranea Clave identificadora de la cat_CgSubcuenta
    protected $cgp_id; //cat_CgSubcuenta - Llave foranea Clave identificadora de la cat_CgSubcuenta
    protected $rub_id; //cat_CgSubcuenta - Llave foranea Clave identificadora de la cat_CgSubcuenta
    protected $cgcu_id; //cat_CgSubcuenta - Llave foranea Clave identificadora de la cat_CgSubcuenta
    protected $cgsc_id; //cat_CgSubcuenta - Llave foranea Clave identificadora de la cat_CgSubcuenta
    protected $cgc_id; //cg_CuentaCuarto - Llave foranea Clave identificadora del Cuarto Nivel
    protected $cta_id; //Clave Identificadora del nivel de registro de la Cuenta Contable
    protected $cta_cuenta; //Cadena de la Cuenta Contable
    protected $cta_nombre; //Nombre de la cuenta contable
    protected $agc_id; //
    protected $usr_id; //

    /******************************************************
      METODOS GET
     ******************************************************/

    public function getCta_gpo_id()
    {
        return $this->cta_gpo_id->getVal();
    }

    public function getCta_ur_id()
    {
        return $this->cta_ur_id->getVal();
    }

    public function getCta_ue_id()
    {
        return $this->cta_ue_id->getVal();
    }

    public function getGer_id()
    {
        return $this->ger_id->getVal();
    }

    public function getCgp_id()
    {
        return $this->cgp_id->getVal();
    }

    public function getRub_id()
    {
        return $this->rub_id->getVal();
    }

    public function getCgcu_id()
    {
        return $this->cgcu_id->getVal();
    }

    public function getCgsc_id()
    {
        return $this->cgsc_id->getVal();
    }

    public function getCgc_id()
    {
        return $this->cgc_id->getVal();
    }

    public function getCta_id()
    {
        return $this->cta_id->getVal();
    }

    public function getCuenta()
    {
        return $this->cta_cuenta->getVal();
    }

    public function getNombre()
    {
        return $this->cta_nombre->getVal();
    }

    public function getAgc_id()
    {
        return $this->agc_id->getVal();
    }

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    /******************************************************
      METODOS SET
     ******************************************************/

    public function setCta_gpo_id()
    {
        $this->cta_gpo_id->setVal($_SESSION[_S]['clasAdm-gpo']);
    }
    //Nunca recibira valor, cualquier nivel debe estar amarrado a la ClasAdmin

    public function setCta_ur_id()
    {
        $this->cta_ur_id->setVal($_SESSION[_S]['clasAdm-ur']);
    }
    //Nunca recibira valor, cualquier nivel debe estar amarrado a la ClasAdmin

    public function setCta_ue_id($val)
    {
        $this->cta_ue_id->setVal($val);
    }
    //Si es de nivel UE, se asigna la de session, en caso contrario lo que se recibe

    public function setGer_id($val)
    {
        $this->ger_id->setVal($val);
    }

    public function setCgp_id($val)
    {
        $this->cgp_id->setVal($val);
    }

    public function setRub_id($val)
    {
        $this->rub_id->setVal($val);
    }

    public function setCgcu_id($val)
    {
        $this->cgcu_id->setVal($val);
    }

    public function setCgsc_id($val)
    {
        $this->cgsc_id->setVal($val);
    }

    public function setCgc_id($val)
    {
        $this->cgc_id->setVal($val);
    }

    public function setCta_id($val)
    {
        $this->cta_id->setVal($val);
    }

    public function setCuenta($val)
    {
        $this->cta_cuenta->setVal($val);
    }

    public function setNombre($val)
    {
        $this->cta_nombre->setVal($val);
    }

    public function setAgc_id($val)
    {
        $this->agc_id->setVal($val);
    }

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    /******************************************************
      METODOS STA (SET sin persistencia)
     ******************************************************/

    public function staCta_gpo_id($val)
    {
        $this->cta_gpo_id->staVal($val);
    }

    public function staCta_ur_id($val)
    {
        $this->cta_ur_id->staVal($val);
    }

    public function staCta_ue_id($val)
    {
        $this->cta_ue_id->staVal($val);
    }

    public function staGer_id($val)
    {
        $this->ger_id->staVal($val);
    }

    public function staCgp_id($val)
    {
        $this->cgp_id->staVal($val);
    }

    public function staRub_id($val)
    {
        $this->rub_id->staVal($val);
    }

    public function staCgcu_id($val)
    {
        $this->cgcu_id->staVal($val);
    }

    public function staCgsc_id($val)
    {
        $this->cgsc_id->staVal($val);
    }

    public function staCgc_id($val)
    {
        $this->cgc_id->staVal($val);
    }

    public function staCta_id($val)
    {
        $this->cta_id->staVal($val);
    }

    public function staCuenta($val)
    {
        $this->cta_cuenta->staVal($val);
    }

    public function staNombre($val)
    {
        $this->cta_nombre->staVal($val);
    }

    public function staAgc_id($val)
    {
        $this->agc_id->staVal($val);
    }

    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }
    /******************************************************
      CONSTRUCTOR
     ******************************************************/

    public function __construct()
    {
        parent::__construct(array("tabla" => BD_CTRL.".cg_PlanCuentas"));

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */
        $this->cta_gpo_id = new Atributo("numero", "NO", "PRI", "cta_gpo_id");
        $this->cta_ur_id = new Atributo("numero", "NO", "PRI", "cta_ur_id");
        $this->cta_ue_id = new Atributo("numero", "NO", "PRI", "cta_ue_id");
        $this->ger_id = new Atributo("numero", "NO", "PRI", "ger_id");
        $this->cgp_id = new Atributo("numero", "NO", "PRI", "cgp_id");
        $this->rub_id = new Atributo("numero", "NO", "PRI", "rub_id");
        $this->cgcu_id = new Atributo("numero", "NO", "PRI", "cgcu_id");
        $this->cgsc_id = new Atributo("numero", "NO", "PRI", "cgsc_id");
        $this->cgc_id = new Atributo("cadena", "NO", "PRI", "cgc_id");
        $this->cta_id = new Atributo("cadena", "NO", "PRI", "cta_id");
        $this->cta_cuenta = new Atributo("cadena", "NO", "", "cta_cuenta");
        $this->cta_nombre = new Atributo("cadena", "NO", "", "cta_nombre");
        $this->agc_id = new Atributo("cadena", "NO", "MUL", "agc_id");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0)
            : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null)
            : null);
        $ger_id = (!isset($prm->ger_id)) ? '' : $prm->ger_id;

        if ($ger_id != '') {
            $this->setCta_gpo_id($prm->cta_gpo_id);
            $this->setCta_ur_id($prm->cta_ur_id);
            $this->setCta_ue_id($prm->cta_ue_id);
            $this->setGer_id($ger_id);
            $this->setCgp_id($prm->cgp_id);
            $this->setRub_id($prm->rub_id);
            $this->setCgcu_id($prm->cgcu_id);
            $this->setCgsc_id($prm->cgsc_id);
            $this->setCgc_id($prm->cgc_id);
            $this->setCta_id($prm->cta_id);
            $this->select();
        }
    }
    /******************************************************
      FUNCIONES ESTÁTICAS
     ******************************************************/

    public static function abc($p)
    {
        $cta = new PlanCuentas();
        if (isset($p->cta_gpo_id)) {
            $cta->setCta_gpo_id($p->cta_gpo_id);
        }

        if (isset($p->cta_ur_id)) {
            $cta->setCta_ur_id($p->cta_ur_id);
        }

        if (isset($p->cta_ue_id)) {
            $cta->setCta_ue_id($p->cta_ue_id);
        }

        if (isset($p->ger_id)) {
            $cta->setGer_id($p->ger_id);
        }

        if (isset($p->cgp_id)) {
            $cta->setCgp_id($p->cgp_id);
        }

        if (isset($p->rub_id)) {
            $cta->setRub_id($p->rub_id);
        }

        if (isset($p->cgcu_id)) {
            $cta->setCgcu_id($p->cgcu_id);
        }

        if (isset($p->cgsc_id)) {
            $cta->setCgsc_id($p->cgsc_id);
        }

        if (isset($p->cgc_id)) {
            $cta->setCgc_id($p->cgc_id);
        }

        if (isset($p->cta_id)) {
            $cta->setCta_id($p->cta_id);
        }

        if (isset($p->cta_nombre)) {
            $cta->setNombre($p->cta_nombre);
        }

        if (isset($p->agc_id)) {
            $cta->setAgc_id($p->agc_id);
        }

        if (isset($p->usr_id)) {
            $cta->setUsr_id($p->usr_id);
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1")
            ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $usr_id = (isset($_SESSION[_S]['usr_id']) && $_SESSION[_S]['usr_id'] != "")
            ? $_SESSION[_S]['usr_id'] : '1';

        $cta->conectar();
        $cta->setSql('SET @usr_ip =INET_ATON( ? ), @usr_id = ?;');
        $params = [$ip, $usr_id];
        $cta->consultar($params);

        switch ($p->abc) {
            case "a":
                $cta->setSql('SET @bit_observacion = ?;');
                $params = ["REGISTRO DE CAPTURA MANUAL"];
                $cta->consultar($params);
                $cta->insert(false);
                return $cta->getUltimoId();
                break;
            case "b":
                $cta->setSql('SET @bit_observacion = ?;');
                $params = ["BAJA"];
                $cta->consultar($params);
                $cta->delete(false);
                break;
            case "c":
                $cta->setSql('SET @bit_observacion = ?;');
                $params = ["MODIFICACIÓN DEL NOMBRE POR EL USUARIO"];
                $cta->consultar($params);
                $cta->update(false);
                break;
        }
    }
}
