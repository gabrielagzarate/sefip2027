<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class PropuestaManual extends Entidad
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
    protected $elo_id; //cat_EstadoLote - Llave Foranea - Identificador del Estado del Lote
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
    public function getElo_id()
    {return $this->elo_id->getVal();}
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
    public function setElo_id($val)
    {$this->elo_id->setVal($val);}
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
    public function staElo_id($val)
    {$this->elo_id->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(array("tabla" => BD_TESORERIA.".lot_PropuestaManual"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->lom_id = new Atributo("numero", "NO", "PRI", "lom_id");
        $this->fpa_id = new Atributo("numero", "NO", "MUL", "fpa_id");
        $this->msc_id = new Atributo("numero", "NO", "", "msc_id");
        $this->lot_ejercicio = new Atributo("numero", "NO", "", "lot_ejercicio");
        $this->lot_id = new Atributo("numero", "NO", "", "lot_id");
        $this->gpo_id = new Atributo("numero", "YES", "MUL", "gpo_id");
        $this->ur_id = new Atributo("numero", "YES", "", "ur_id");
        $this->clc_id = new Atributo("numero", "YES", "", "clc_id");
        $this->lom_beneficiario = new Atributo("cadena", "NO", "", "lom_beneficiario");
        $this->lom_concepto = new Atributo("cadena", "NO", "", "lom_concepto");
        $this->lom_importe = new Atributo("numero", "NO", "", "lom_importe");
        $this->cbs_id = new Atributo("numero", "NO", "MUL", "cbs_id");
        $this->cbb_id = new Atributo("numero", "NO", "MUL", "cbb_id");
        $this->elo_id = new Atributo("numero", "NO", "MUL", "elo_id");
        $this->usr_id = new Atributo("numero", "NO", "MUL", "usr_id");

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $observacion = "";
        $lot = new PropuestaManual();
        if (isset($p->lom_id)) {
            $lot->setLom_id($p->lom_id);
            $observacion .= 'lom_id: ' . $p->lom_id;
        }
        if (isset($p->fpa_id)) {
            $lot->setFpa_id($p->fpa_id);
            $observacion .= 'fpa_id: ' . $p->fpa_id;
        }
        if (isset($p->msc_id)) {
            $lot->setMsc_id($p->msc_id);
            $observacion .= 'msc_id: ' . $p->msc_id;
        }
        if (isset($p->ejercicio)) {
            $lot->setEjercicio($p->ejercicio);
            $observacion .= 'ejercicio: ' . $p->ejercicio;
        }
        if (isset($p->lot_id)) {
            $lot->setLot_id($p->lot_id);
            $observacion .= 'lot_id: ' . $p->lot_id;
        }
        if (isset($p->gpo_id)) {
            $lot->setGpo_id($p->gpo_id);
            $observacion .= 'gpo_id: ' . $p->gpo_id;
        }
        if (isset($p->ur_id)) {
            $lot->setUr_id($p->ur_id);
            $observacion .= 'ur_id: ' . $p->ur_id;
        }
        if (isset($p->clc_id)) {
            $lot->setClc_id($p->clc_id);
            $observacion .= 'clc_id: ' . $p->clc_id;
        }
        if (isset($p->beneficiario)) {
            $lot->setBeneficiario($p->beneficiario);
            $observacion .= 'beneficiario: ' . $p->beneficiario;
        }
        if (isset($p->concepto)) {
            $lot->setConcepto($p->concepto);
            $observacion .= 'concepto: ' . $p->concepto;
        }
        if (isset($p->importe)) {
            $lot->setImporte($p->importe);
            $observacion .= 'importe: ' . $p->importe;
        }
        if (isset($p->cbs_id)) {
            $lot->setCbs_id($p->cbs_id);
            $observacion .= 'cbs_id: ' . $p->cbs_id;
        }
        if (isset($p->cbb_id)) {
            $lot->setCbb_id($p->cbb_id);
            $observacion .= 'cbb_id: ' . $p->cbb_id;
        }
        if (isset($p->elo_id)) {
            $lot->setElo_id($p->elo_id);
            $observacion .= 'elo_id: ' . $p->elo_id;
        }
        if (isset($p->usr_id)) {
            $lot->setUsr_id($p->usr_id);
            $observacion .= 'usr_id: ' . $p->usr_id;
        }
        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a":
                $lot->conectar();
                $bit_observacion = 'REGISTRO INSERTADO ' . $observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->insert(false);
                return $lot->getUltimoId();
                break;
            case "b":
                $lot->conectar();
                $bit_observacion = 'REGISTRO ELIMINADO ' . $observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->delete(false);
                break;
            case "c":
                $lot->conectar();
                $bit_observacion = 'REGISTRO MODIFICADO ' . $observacion;
                array_push($params, $bit_observacion);
                $lot->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lot->consultar($params);
                $lot->update(false);
                break;
        }
    }

}
