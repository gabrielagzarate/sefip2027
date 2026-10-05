<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class PropuestaManualTmp extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $lom_id; //
    protected $fpa_id; //cat_FormaPago - Llave foranea - Identificador de la Forma de Pago
    protected $msc_id; //cat_Mes - Llave foranea - Identificador del Mes
    protected $lot_ejercicio; //lot_Lote - Llave Foranea - Identificador del ejercicio del Documento
    protected $lot_id; //lot_Lote - Llave foranea - Identificador del Lote
    protected $gpo_id; //
    protected $ur_id; //
    protected $clc_id; //
    protected $lom_beneficiario; //
    protected $lom_concepto; //
    protected $lom_importe; //
    protected $cbs_id; //ban_CuentaBancariaSefin - Llave foranea - Identificador de la Cuenta Bancaria de SeFin
    protected $cbb_id; //ban_CuentaBancariaBeneficiario - Llave foranea - Identificador de la Cuenta Bancaria del Beneficiario
    protected $ecf_id; //
    protected $cta_gpo_id; //
    protected $cta_ur_id; //
    protected $cta_ue_id; //
    protected $ger_id; //
    protected $cgp_id; //
    protected $rub_id; //
    protected $cgcu_id; //
    protected $cgsc_id; //
    protected $cgc_id; //
    protected $cta_id; //
    protected $usr_id; //Identificador de usuario que captura/actualiza el registro

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getLom_id()
    {return $this->lom_id->getVal();}
    public function getFpa_id()
    {return $this->fpa_id->getVal();}
    public function getMsc_id()
    {return $this->msc_id->getVal();}
    public function getEjercicio()
    {return $this->lot_ejercicio->getVal();}
    public function getLot_id()
    {return $this->lot_id->getVal();}
    public function getGpo_id()
    {return $this->gpo_id->getVal();}
    public function getUr_id()
    {return $this->ur_id->getVal();}
    public function getClc_id()
    {return $this->clc_id->getVal();}
    public function getBeneficiario()
    {return $this->lom_beneficiario->getVal();}
    public function getConcepto()
    {return $this->lom_concepto->getVal();}
    public function getImporte()
    {return $this->lom_importe->getVal();}
    public function getCbs_id()
    {return $this->cbs_id->getVal();}
    public function getCbb_id()
    {return $this->cbb_id->getVal();}
    public function getEcf_id()
    {return $this->ecf_id->getVal();}
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
    public function setFpa_id($val)
    {$this->fpa_id->setVal($val);}
    public function setMsc_id($val)
    {$this->msc_id->setVal($val);}
    public function setEjercicio($val)
    {$this->lot_ejercicio->setVal($val);}
    public function setLot_id($val)
    {$this->lot_id->setVal($val);}
    public function setGpo_id($val)
    {$this->gpo_id->setVal($val);}
    public function setUr_id($val)
    {$this->ur_id->setVal($val);}
    public function setClc_id($val)
    {$this->clc_id->setVal($val);}
    public function setBeneficiario($val)
    {$this->lom_beneficiario->setVal($val);}
    public function setConcepto($val)
    {$this->lom_concepto->setVal($val);}
    public function setImporte($val)
    {$this->lom_importe->setVal($val);}
    public function setCbs_id($val)
    {$this->cbs_id->setVal($val);}
    public function setCbb_id($val)
    {$this->cbb_id->setVal($val);}
    public function setEcf_id($val)
    {$this->ecf_id->setVal($val);}
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
    public function staFpa_id($val)
    {$this->fpa_id->staVal($val);}
    public function staMsc_id($val)
    {$this->msc_id->staVal($val);}
    public function staEjercicio($val)
    {$this->lot_ejercicio->staVal($val);}
    public function staLot_id($val)
    {$this->lot_id->staVal($val);}
    public function staGpo_id($val)
    {$this->gpo_id->staVal($val);}
    public function staUr_id($val)
    {$this->ur_id->staVal($val);}
    public function staClc_id($val)
    {$this->clc_id->staVal($val);}
    public function staBeneficiario($val)
    {$this->lom_beneficiario->staVal($val);}
    public function staConcepto($val)
    {$this->lom_concepto->staVal($val);}
    public function staImporte($val)
    {$this->lom_importe->staVal($val);}
    public function staCbs_id($val)
    {$this->cbs_id->staVal($val);}
    public function staCbb_id($val)
    {$this->cbb_id->staVal($val);}
    public function staEcf_id($val)
    {$this->ecf_id->staVal($val);}
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

        parent::__construct(array("tabla" => BD_TESORERITEM.".tem_PropuestaManual"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->lom_id = new Atributo("numero", "NO", "PRI", "lom_id");
        $this->fpa_id = new Atributo("numero", "YES", "", "fpa_id");
        $this->msc_id = new Atributo("numero", "YES", "", "msc_id");
        $this->lot_ejercicio = new Atributo("numero", "YES", "", "lot_ejercicio");
        $this->lot_id = new Atributo("numero", "YES", "", "lot_id");
        $this->gpo_id = new Atributo("numero", "YES", "MUL", "gpo_id");
        $this->ur_id = new Atributo("numero", "YES", "", "ur_id");
        $this->clc_id = new Atributo("numero", "YES", "", "clc_id");
        $this->lom_beneficiario = new Atributo("cadena", "NO", "", "lom_beneficiario");
        $this->lom_concepto = new Atributo("cadena", "NO", "", "lom_concepto");
        $this->lom_importe = new Atributo("numero", "NO", "", "lom_importe");
        $this->cbs_id = new Atributo("numero", "NO", "MUL", "cbs_id");
        $this->cbb_id = new Atributo("numero", "NO", "MUL", "cbb_id");
        $this->ecf_id = new Atributo("numero", "NO", "", "ecf_id");
        $this->cta_gpo_id = new Atributo("numero", "NO", "", "cta_gpo_id");
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
        $lot = new PropuestaManualTmp();
        $bit_observacion = '';

        if (isset($p->lom_id)) {
            $lot->setLom_id($p->lom_id);
            $bit_observacion .= 'lom_id: ' . $p->lom_id;
        }
        if (isset($p->fpa_id)) {
            $lot->setFpa_id($p->fpa_id);
            $bit_observacion .= 'fpa_id: ' . $p->fpa_id;
        }
        if (isset($p->msc_id)) {
            $lot->setMsc_id($p->msc_id);
            $bit_observacion .= 'msc_id: ' . $p->msc_id;
        }
        if (isset($p->ejercicio)) {
            $lot->setEjercicio($p->ejercicio);
            $bit_observacion .= 'lot_ejercicio: ' . $p->ejercicio;
        }
        if (isset($p->lot_id)) {
            $lot->setLot_id($p->lot_id);
            $bit_observacion .= 'lot_id: ' . $p->lot_id;
        }
        if (isset($p->gpo_id)) {
            $lot->setGpo_id($p->gpo_id);
            $bit_observacion .= 'gpo_id: ' . $p->gpo_id;
        }
        if (isset($p->ur_id)) {
            $lot->setUr_id($p->ur_id);
            $bit_observacion .= 'ur_id: ' . $p->ur_id;
        }
        if (isset($p->clc_id)) {
            $lot->setClc_id($p->clc_id);
            $bit_observacion .= 'clc_id: ' . $p->clc_id;
        }
        if (isset($p->beneficiario)) {
            $lot->setBeneficiario($p->beneficiario);
            $bit_observacion .= 'lom_beneficiario: ' . $p->beneficiario;
        }
        if (isset($p->concepto)) {
            $lot->setConcepto($p->concepto);
            $bit_observacion .= 'lom_concepto: ' . $p->concepto;
        }
        if (isset($p->importe)) {
            $lot->setImporte($p->importe);
            $bit_observacion .= 'lom_importe: ' . $p->importe;
        }
        if (isset($p->cbs_id)) {
            $lot->setCbs_id($p->cbs_id);
            $bit_observacion .= 'cbs_id: ' . $p->cbs_id;
        }
        if (isset($p->cbb_id)) {
            $lot->setCbb_id($p->cbb_id);
            $bit_observacion .= 'cbb_id: ' . $p->cbb_id;
        }
        if (isset($p->ecf_id)) {
            $lot->setEcf_id($p->ecf_id);
            $bit_observacion .= ' ecf_id: ' . $p->ecf_id;
        }
        if (isset($p->cta_gpo_id)) {
            $lot->setCta_gpo_id($p->cta_gpo_id);
            $bit_observacion .= ' cta_gpo_id: ' . $p->cta_gpo_id;
        }
        if (isset($p->cta_ur_id)) {
            $lot->setCta_ur_id($p->cta_ur_id);
            $bit_observacion .= ' cta_ur_id: ' . $p->cta_ur_id;
        }
        if (isset($p->cta_ue_id)) {
            $lot->setCta_ue_id($p->cta_ue_id);
            $bit_observacion .= ' cta_ue_id: ' . $p->cta_ue_id;
        }
        if (isset($p->ger_id)) {
            $lot->setGer_id($p->ger_id);
            $bit_observacion .= ' ger_id: ' . $p->ger_id;
        }
        if (isset($p->cgp_id)) {
            $lot->setCgp_id($p->cgp_id);
            $bit_observacion .= ' cgp_id: ' . $p->cgp_id;
        }
        if (isset($p->rub_id)) {
            $lot->setRub_id($p->rub_id);
            $bit_observacion .= ' rub_id: ' . $p->rub_id;
        }
        if (isset($p->cgcu_id)) {
            $lot->setCgcu_id($p->cgcu_id);
            $bit_observacion .= ' cgcu_id: ' . $p->cgcu_id;
        }
        if (isset($p->cgsc_id)) {
            $lot->setCgsc_id($p->cgsc_id);
            $bit_observacion .= ' cgsc_id: ' . $p->cgsc_id;
        }
        if (isset($p->cgc_id)) {
            $lot->setCgc_id($p->cgc_id);
            $bit_observacion .= ' cgc_id: ' . $p->cgc_id;
        }
        if (isset($p->cta_id)) {
            $lot->setCta_id($p->cta_id);
            $bit_observacion .= ' cta_id: ' . $p->cta_id;
        }
        if (isset($p->usr_id)) {
            $lot->setUsr_id($p->usr_id);
            $bit_observacion .= 'usr_id: ' . $p->usr_id;
        }

        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a":
                $lot->conectar();
                $bit_observacion = 'REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->insert(false);
                return $lot->getUltimoId();
                break;
            case "b":
                $lot->conectar();
                $bit_observacion = 'REGISTRO ELIMINADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->delete(false);
                break;
            case "c":
                $lot->conectar();
                $bit_observacion = 'REGISTRO MODIFICADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->update(false);
                break;
        }
    }

}
