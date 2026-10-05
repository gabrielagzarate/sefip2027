<?php
require_once dirname(__FILE__) . "/../Entidad.php";

class Contable extends Entidad
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/

    protected $gpo_id; //Identificador del Grupo de la UR. Foránea dependiente de scp_Afectacion
    protected $ur_id; //Identificador de la UR. Foránea dependiente de scp_Afectacion
    protected $clc_id; //Numero identificador de la CLC a la cual pertenece la Afectacion y Numero de Control. Foránea dependiete de scp_Afectacion
    protected $afe_id; //Identificador numerico de la Afectación, Foránea dependiente de scp_Afectacion
    protected $ncc_id; //Identificador del numero de control
    protected $ger_id; //cg_PlanCuentas - Llave foranea cg_PlanCuentas - Llave foranea
    protected $cgp_id; //cg_PlanCuentas - Llave foranea cg_PlanCuentas - Llave foranea
    protected $rub_id; //cg_PlanCuentas - Llave foranea cg_PlanCuentas - Llave foranea
    protected $cgcu_id; //cg_PlanCuentas - Llave foranea cg_PlanCuentas - Llave foranea
    protected $cgsc_id; //cg_PlanCuentas - Llave foranea cg_PlanCuentas - Llave foranea
    protected $cta_gpo_id; //cg_PlanCuentas - Llave foranea cg_PlanCuentas - Llave foranea
    protected $cta_ur_id; //cg_PlanCuentas - Llave foranea cg_PlanCuentas - Llave foranea
    protected $cta_ue_id; //cg_PlanCuentas - Llave foranea cg_PlanCuentas - Llave foranea
    protected $cgc_id; //cg_PlanCuentas - Llave foranea cg_PlanCuentas - Llave foranea
    protected $cta_id; //cg_PlanCuentas - Llave foranea cg_PlanCuentas - Llave foranea
    protected $rfc_id; //RFC del Proveedor. Foránea dependiente de cat_Proveedores.
    protected $ncc_concepto; //Concepto del numero de Control
    protected $ncc_referencia; //Referencia del Numero de control
    protected $ncc_fechaReferencia; //Fecha de la referencia solo acepta hasta dos años antes del ejercicio actual
    protected $ncc_tipoMovimiento; //Indica el tipo de movimiento + Percepciones - Retenciones
    protected $ncc_percepcion; //Importe Total de las percepciones
    protected $ncc_retencion; //Importe Total de las retenciones
    protected $ncc_amortizado; //Indica si el numero de control ha sido amortizado o no 1: amortizado 2: NO AMORTIZADO
    protected $ncc_gpo; //SI el numero de control es Amortizador o pagador, aqui se guarda el grupo de la unidad responsable  a la cual se le amortiza o paga
    protected $ncc_ur; //SI el numero de control es Amortizador o pagador, aqui se guarda la unidad responsable  a la cual se le amortiza o paga
    protected $ncc_clc; //SI el numero de control es Amortizador o pagador, aqui se guarda  el numero de CLC   a la cual se le amortiza o paga
    protected $ncc_nccamortiza; //SI el numero de control es Amortizador o pagador, aqui se guarda  el numero de Ccontrol   a la cual se le amortiza o paga
    protected $tem_carga; //Guarda el numero de carga masiva si es que el numero de control proviene de una carga masiva, si esta vacio indica que es capturado por sistema
    protected $bdo_id; //
    protected $usr_id; //

    /******************************************************
    METODOS GET
     ******************************************************/

    public function getGpo_id()
    {return $this->gpo_id->getVal();}
    public function getUr_id()
    {return $this->ur_id->getVal();}
    public function getClc_id()
    {return $this->clc_id->getVal();}
    public function getAfe_id()
    {return $this->afe_id->getVal();}
    public function getNcc_id()
    {return $this->ncc_id->getVal();}
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
    public function getCta_gpo_id()
    {return $this->cta_gpo_id->getVal();}
    public function getCta_ur_id()
    {return $this->cta_ur_id->getVal();}
    public function getCta_ue_id()
    {return $this->cta_ue_id->getVal();}
    public function getCgc_id()
    {return $this->cgc_id->getVal();}
    public function getCta_id()
    {return $this->cta_id->getVal();}
    public function getRfc_id()
    {return $this->rfc_id->getVal();}
    public function getConcepto()
    {return $this->ncc_concepto->getVal();}
    public function getReferencia()
    {return $this->ncc_referencia->getVal();}
    public function getFechaReferencia()
    {return $this->ncc_fechaReferencia->getVal();}
    public function getNumeroOrden()
    {return $this->numeroOrden->getVal();}
    public function getFechaOrden()
    {return $this->fechaOrden->getVal();}
    public function getTipoMovimiento()
    {return $this->ncc_tipoMovimiento->getVal();}
    public function getPercepcion()
    {return $this->ncc_percepcion->getVal();}
    public function getRetencion()
    {return $this->ncc_retencion->getVal();}
    public function getAmortizado()
    {return $this->ncc_amortizado->getVal();}
    public function getGpo()
    {return $this->ncc_gpo->getVal();}
    public function getUr()
    {return $this->ncc_ur->getVal();}
    public function getClc()
    {return $this->ncc_clc->getVal();}
    public function getNccamortiza()
    {return $this->ncc_nccamortiza->getVal();}
    public function getCarga()
    {return $this->tem_carga->getVal();}
    public function getBdo_id()
    {return $this->bdo_id->getVal();}
    public function getUsr_id()
    {return $this->usr_id->getVal();}

    /******************************************************
    METODOS SET
     ******************************************************/

    public function setGpo_id($val)
    {$this->gpo_id->setVal($val);}
    public function setUr_id($val)
    {$this->ur_id->setVal($val);}
    public function setClc_id($val)
    {$this->clc_id->setVal($val);}
    public function setAfe_id($val)
    {$this->afe_id->setVal($val);}
    public function setNcc_id($val)
    {$this->ncc_id->setVal($val);}
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
    public function setCta_gpo_id($val)
    {$this->cta_gpo_id->setVal($val);}
    public function setCta_ur_id($val)
    {$this->cta_ur_id->setVal($val);}
    public function setCta_ue_id($val)
    {$this->cta_ue_id->setVal($val);}
    public function setCgc_id($val)
    {$this->cgc_id->setVal($val);}
    public function setCta_id($val)
    {$this->cta_id->setVal($val);}
    public function setRfc_id($val)
    {$this->rfc_id->setVal($val);}
    public function setConcepto($val)
    {$this->ncc_concepto->setVal($val);}
    public function setReferencia($val)
    {$this->ncc_referencia->setVal($val);}
    public function setFechaReferencia($val)
    {$this->ncc_fechaReferencia->setVal($val);}
    public function setNumeroOrden($val)
    {$this->numeroOrden->setVal($val);}
    public function setFechaOrden($val)
    {$this->fechaOrden->setVal($val);}
    public function setTipoMovimiento($val)
    {$this->ncc_tipoMovimiento->setVal($val);}
    public function setPercepcion($val)
    {$this->ncc_percepcion->setVal($val);}
    public function setRetencion($val)
    {$this->ncc_retencion->setVal($val);}
    public function setAmortizado($val)
    {$this->ncc_amortizado->setVal($val);}
    public function setGpo($val)
    {$this->ncc_gpo->setVal($val);}
    public function setUr($val)
    {$this->ncc_ur->setVal($val);}
    public function setClc($val)
    {$this->ncc_clc->setVal($val);}
    public function setNccamortiza($val)
    {$this->ncc_nccamortiza->setVal($val);}
    public function setCarga($val)
    {$this->tem_carga->setVal($val);}
    public function setBdo_id($val)
    {$this->bdo_id->setVal($val);}
    public function setUsr_id($val)
    {$this->usr_id->setVal($val);}

    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/

    public function staGpo_id($val)
    {$this->gpo_id->staVal($val);}
    public function staUr_id($val)
    {$this->ur_id->staVal($val);}
    public function staClc_id($val)
    {$this->clc_id->staVal($val);}
    public function staAfe_id($val)
    {$this->afe_id->staVal($val);}
    public function staNcc_id($val)
    {$this->ncc_id->staVal($val);}
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
    public function staCta_gpo_id($val)
    {$this->cta_gpo_id->staVal($val);}
    public function staCta_ur_id($val)
    {$this->cta_ur_id->staVal($val);}
    public function staCta_ue_id($val)
    {$this->cta_ue_id->staVal($val);}
    public function staCgc_id($val)
    {$this->cgc_id->staVal($val);}
    public function staCta_id($val)
    {$this->cta_id->staVal($val);}
    public function staRfc_id($val)
    {$this->rfc_id->staVal($val);}
    public function staConcepto($val)
    {$this->ncc_concepto->staVal($val);}
    public function staReferencia($val)
    {$this->ncc_referencia->staVal($val);}
    public function staFechaReferencia($val)
    {$this->ncc_fechaReferencia->staVal($val);}
    public function staTipoMovimiento($val)
    {$this->ncc_tipoMovimiento->staVal($val);}
    public function staPercepcion($val)
    {$this->ncc_percepcion->staVal($val);}
    public function staRetencion($val)
    {$this->ncc_retencion->staVal($val);}
    public function staAmortizado($val)
    {$this->ncc_amortizado->staVal($val);}
    public function staGpo($val)
    {$this->ncc_gpo->staVal($val);}
    public function staUr($val)
    {$this->ncc_ur->staVal($val);}
    public function staClc($val)
    {$this->ncc_clc->staVal($val);}
    public function staNccamortiza($val)
    {$this->ncc_nccamortiza->staVal($val);}
    public function staCarga($val)
    {$this->tem_carga->staVal($val);}
    public function staBdo_id($val)
    {$this->bdo_id->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}

    /******************************************************
    CONSTRUCTOR
     ******************************************************/

    public function __construct()
    {
        parent::__construct(array("tabla" => "scp_Contable"));
        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->gpo_id = new Atributo("numero", "NO", "PRI", "gpo_id");
        $this->ur_id = new Atributo("numero", "NO", "PRI", "ur_id");
        $this->clc_id = new Atributo("numero", "NO", "PRI", "clc_id");
        $this->afe_id = new Atributo("numero", "NO", "PRI", "afe_id");
        $this->ncc_id = new Atributo("numero", "NO", "PRI", "ncc_id");
        $this->ger_id = new Atributo("numero", "NO", "MUL", "ger_id");
        $this->cgp_id = new Atributo("numero", "NO", "", "cgp_id");
        $this->rub_id = new Atributo("numero", "NO", "", "rub_id");
        $this->cgcu_id = new Atributo("numero", "NO", "", "cgcu_id");
        $this->cgsc_id = new Atributo("numero", "NO", "", "cgsc_id");
        $this->cta_gpo_id = new Atributo("numero", "NO", "", "cta_gpo_id");
        $this->cta_ur_id = new Atributo("numero", "NO", "", "cta_ur_id");
        $this->cta_ue_id = new Atributo("numero", "NO", "", "cta_ue_id");
        $this->cgc_id = new Atributo("cadena", "NO", "", "cgc_id");
        $this->cta_id = new Atributo("cadena", "NO", "", "cta_id");
        $this->rfc_id = new Atributo("cadena", "NO", "MUL", "rfc_id");
        $this->ncc_concepto = new Atributo("cadena", "NO", "", "ncc_concepto");
        $this->ncc_referencia = new Atributo("cadena", "NO", "MUL", "ncc_referencia");
        $this->ncc_fechaReferencia = new Atributo("fecha", "NO", "", "ncc_fechaReferencia");
        $this->ncc_tipoMovimiento = new Atributo("cadena", "NO", "", "ncc_tipoMovimiento");
        $this->ncc_percepcion = new Atributo("numero", "NO", "", "ncc_percepcion");
        $this->ncc_retencion = new Atributo("numero", "NO", "", "ncc_retencion");
        $this->ncc_amortizado = new Atributo("numero", "NO", "", "ncc_amortizado");
        $this->ncc_gpo = new Atributo("numero", "YES", "MUL", "ncc_gpo");
        $this->ncc_ur = new Atributo("numero", "YES", "", "ncc_ur");
        $this->ncc_clc = new Atributo("numero", "YES", "", "ncc_clc");
        $this->ncc_nccamortiza = new Atributo("numero", "YES", "MUL", "ncc_nccamortiza");
        $this->tem_carga = new Atributo("cadena", "NO", "", "tem_carga");
        $this->bdo_id = new Atributo("numero", "NO", "MUL", "bdo_id");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        $gpo_id = (!isset($prm->gpo_id)) ? '' : $prm->gpo_id;
        $ur_id = (!isset($prm->ur_id)) ? '' : $prm->ur_id;
        $clc_id = (!isset($prm->clc_id)) ? '' : $prm->clc_id;
        $afe_id = (!isset($prm->afe_id)) ? '' : $prm->afe_id;
        $ncc_id = (!isset($prm->ncc_id)) ? '' : $prm->ncc_id;

        if ($ncc_id != '') {
            $this->setGpo_id($gpo_id);
            $this->setClc_id($clc_id);
            $this->setUr_id($ur_id);
            $this->setAfe_id($afe_id);
            $this->setNcc_id($ncc_id);
            $this->select();
        }
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/

    public static function abc($p)
    {
        $ncc = new Contable();
        if (isset($p->gpo_id)) {
            $ncc->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $ncc->setUr_id($p->ur_id);
        }

        if (isset($p->clc_id)) {
            $ncc->setClc_id($p->clc_id);
        }

        if (isset($p->afe_id)) {
            $ncc->setAfe_id($p->afe_id);
        }

        if (isset($p->ncc_id)) {
            $ncc->setNcc_id($p->ncc_id);
        }

        if (isset($p->ger_id)) {
            $ncc->setGer_id($p->ger_id);
        }

        if (isset($p->cgp_id)) {
            $ncc->setCgp_id($p->cgp_id);
        }

        if (isset($p->rub_id)) {
            $ncc->setRub_id($p->rub_id);
        }

        if (isset($p->cgcu_id)) {
            $ncc->setCgcu_id($p->cgcu_id);
        }

        if (isset($p->cgsc_id)) {
            $ncc->setCgsc_id($p->cgsc_id);
        }

        if (isset($p->cta_gpo_id)) {
            $ncc->setCta_gpo_id($p->cta_gpo_id);
        }

        if (isset($p->cta_ur_id)) {
            $ncc->setCta_ur_id($p->cta_ur_id);
        }

        if (isset($p->cta_ue_id)) {
            $ncc->setCta_ue_id($p->cta_ue_id);
        }

        if (isset($p->cgc_id)) {
            $ncc->setCgc_id($p->cgc_id);
        }

        if (isset($p->cta_id)) {
            $ncc->setCta_id($p->cta_id);
        }

        if (isset($p->rfc_id)) {
            $ncc->setRfc_id($p->rfc_id);
        }

        if (isset($p->concepto)) {
            $ncc->setConcepto(htmlspecialchars($p->concepto));
        }

        if (isset($p->referencia)) {
            $ncc->setReferencia($p->referencia);
        }

        if (isset($p->fechaReferencia)) {
            $ncc->setFechaReferencia($p->fechaReferencia);
        }

        if (isset($p->tipoMovimiento)) {
            $ncc->setTipoMovimiento($p->tipoMovimiento);
        }

        if (isset($p->percepcion)) {
            $ncc->setPercepcion($p->percepcion);
        }

        if (isset($p->retencion)) {
            $ncc->setRetencion($p->retencion);
        }

        if (isset($p->amortizado)) {
            $ncc->setAmortizado($p->amortizado);
        }

        if (isset($p->gpo)) {
            $ncc->setGpo($p->gpo);
        }

        if (isset($p->ur)) {
            $ncc->setUr($p->ur);
        }

        if (isset($p->clc)) {
            $ncc->setClc($p->clc);
        }

        if (isset($p->nccamortiza)) {
            $ncc->setNccamortiza($p->nccamortiza);
        }

        if (isset($p->carga)) {
            $ncc->setCarga($p->carga);
        }

        if (isset($p->bdo_id)) {
            $ncc->setBdo_id($p->bdo_id);
        }

        if (isset($p->usr_id)) {
            $ncc->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case "a":
                $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
                $user = $_SESSION[_S]['usr_id'];
                $ncc->conectar();
                $ncc->setSql('
                    SET @usr_id = ?,
                    @bit_observacion="CAPTURA MANUAL DE UN NUMERO DE CONTROL CONTABLE",
                    @usr_ip=INET_ATON(?)
                ');
                $params = [$user,$ip];
                $ncc->consultar($params);
                $ncc->insert(false);
                $ncc->desconectar();
                return $ncc->getUltimoId();
                break;
            case "b":$ncc->delete();
                break;
            case "c":$ncc->update();
                break;
        }
    }
}
