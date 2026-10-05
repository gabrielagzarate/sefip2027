<?php
require_once dirname(__FILE__) . "/../Entidad.php";
class Presupuestal extends Entidad
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $gpo_id; //Identificador del Grupo de la Unidad Responsable. Foránea dependiente de scp_Afectacion y presupuesto
    protected $ur_id; //Identificador de la Unidad Responsable. Foránea dependiente de scp_Afectacion y presupuesto
    protected $clc_id; //Identificador de la CLC a lacual pertenece la Afectacion, de la cual esta colgado el numero de control. Depende de spc_Afectacion.
    protected $afe_id; //Identificador de la Afectacion de la cual depende el numero de control. Depende de scp_Afectacion
    protected $ncp_id; //Identificador numerico para el numero de control
    protected $ue_id; //Identificador de la Unidad Ejectuora de la UR depende de presupuesto
    protected $cmp_id; //
    protected $epg_id; //Identificador del encuaadre programatico. depende de presupuesto
    protected $pg_id; //Identificador de la Partida Generica a la cual se le carga el numero de control. depende de presupuesto.
    protected $pe_id; //Identificador de la Partida espécifica. Depende de presupuesto.
    protected $ecf_id; //Identificador del encuandre de Clave de financiamiento. Depende de presupuesto.
    protected $tcp_id; //Identificador del Tipo de Compra. depende de presupuesto
    protected $rfc_id; //Identificador del Proveedor. Depende de cat_Proveedores
    protected $ncp_concepto; //Concepto para el numero de Control.
    protected $ncp_referencia; //Referencia para el numero de control.
    protected $ncp_fechaReferencia; //Fecha que corresponde a la referencia.
    protected $ncp_percepcionBruto; //importe bruto de la percepcion del presupuesto
    protected $iva_id; //Identificador del IVA que se manejara para este numero de control
    protected $ncp_percepcionIva; //Importe del iva segun el porcentaje de IVA que le corresponde
    protected $ncp_percepcionNeto; //Importe total de la percepcio bruta mas el IVA que le corresponde.
    protected $tem_carga; //Indica la carga masiva de la cual proviene el numero de control. si esta vacia indica que sfue capturado en el SINPRES
    protected $ncp_otroDocumento;
    protected $bdo_id; //Identificador del Documento al cual se hace referencia. depende de cat_BancoDocumentos
    protected $tpd_id; //Identificador del tipo de documento que genera el devengado. Depende de cat_TipoDocumento
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
    public function getNcp_id()
    {return $this->ncp_id->getVal();}
    public function getUe_id()
    {return $this->ue_id->getVal();}
    public function getCmp_id()
    {return $this->cmp_id->getVal();}
    public function getEpg_id()
    {return $this->epg_id->getVal();}
    public function getPg_id()
    {return $this->pg_id->getVal();}
    public function getPe_id()
    {return $this->pe_id->getVal();}
    public function getEcf_id()
    {return $this->ecf_id->getVal();}
    public function getTcp_id()
    {return $this->tcp_id->getVal();}
    public function getRfc_id()
    {return $this->rfc_id->getVal();}
    public function getConcepto()
    {return $this->ncp_concepto->getVal();}
    public function getReferencia()
    {return $this->ncp_referencia->getVal();}
    public function getFechaReferencia()
    {return $this->ncp_fechaReferencia->getVal();}
    public function getPercepcionBruto()
    {return $this->ncp_percepcionBruto->getVal();}
    public function getIva_id()
    {return $this->iva_id->getVal();}
    public function getPercepcionIva()
    {return $this->ncp_percepcionIva->getVal();}
    public function getPercepcionNeto()
    {return $this->ncp_percepcionNeto->getVal();}
    public function getCarga()
    {return $this->tem_carga->getVal();}
    public function getBdo_id()
    {return $this->bdo_id->getVal();}
    public function getTpd_id()
    {return $this->tpd_id->getVal();}
    public function getOtroDocumento()
    {return $this->ncp_otroDocumento->getVal();}
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
    public function setNcp_id($val)
    {$this->ncp_id->setVal($val);}
    public function setUe_id($val)
    {$this->ue_id->setVal($val);}
    public function setCmp_id($val)
    {$this->cmp_id->setVal($val);}
    public function setEpg_id($val)
    {$this->epg_id->setVal($val);}
    public function setPg_id($val)
    {$this->pg_id->setVal($val);}
    public function setPe_id($val)
    {$this->pe_id->setVal($val);}
    public function setEcf_id($val)
    {$this->ecf_id->setVal($val);}
    public function setTcp_id($val)
    {$this->tcp_id->setVal($val);}
    public function setRfc_id($val)
    {$this->rfc_id->setVal($val);}
    public function setConcepto($val)
    {$this->ncp_concepto->setVal($val);}
    public function setReferencia($val)
    {$this->ncp_referencia->setVal($val);}
    public function setFechaReferencia($val)
    {$this->ncp_fechaReferencia->setVal($val);}
    public function setPercepcionBruto($val)
    {$this->ncp_percepcionBruto->setVal($val);}
    public function setIva_id($val)
    {$this->iva_id->setVal($val);}
    public function setPercepcionIva($val)
    {$this->ncp_percepcionIva->setVal($val);}
    public function setPercepcionNeto($val)
    {$this->ncp_percepcionNeto->setVal($val);}
    public function setCarga($val)
    {$this->tem_carga->setVal($val);}
    public function setBdo_id($val)
    {$this->bdo_id->setVal($val);}
    public function setTpd_id($val)
    {$this->tpd_id->setVal($val);}
    public function setOtroDocumento($val)
    {$this->ncp_otroDocumento->setVal($val);}
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
    public function staNcp_id($val)
    {$this->ncp_id->staVal($val);}
    public function staUe_id($val)
    {$this->ue_id->staVal($val);}
    public function staCmp_id($val)
    {$this->cmp_id->staVal($val);}
    public function staEpg_id($val)
    {$this->epg_id->staVal($val);}
    public function staPg_id($val)
    {$this->pg_id->staVal($val);}
    public function staPe_id($val)
    {$this->pe_id->staVal($val);}
    public function staEcf_id($val)
    {$this->ecf_id->staVal($val);}
    public function staTcp_id($val)
    {$this->tcp_id->staVal($val);}
    public function staRfc_id($val)
    {$this->rfc_id->staVal($val);}
    public function staConcepto($val)
    {$this->ncp_concepto->staVal($val);}
    public function staReferencia($val)
    {$this->ncp_referencia->staVal($val);}
    public function staFechaReferencia($val)
    {$this->ncp_fechaReferencia->staVal($val);}
    public function staPercepcionBruto($val)
    {$this->ncp_percepcionBruto->staVal($val);}
    public function staIva_id($val)
    {$this->iva_id->staVal($val);}
    public function staPercepcionIva($val)
    {$this->ncp_percepcionIva->staVal($val);}
    public function staPercepcionNeto($val)
    {$this->ncp_percepcionNeto->staVal($val);}
    public function staCarga($val)
    {$this->tem_carga->staVal($val);}
    public function staBdo_id($val)
    {$this->bdo_id->staVal($val);}
    public function staTpd_id($val)
    {$this->tpd_id->staVal($val);}
    public function staOtroDocumento($val)
    {$this->ncp_otroDocumento->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}
    /******************************************************
    CONSTRUCTOR
     ******************************************************/
    public function __construct()
    {
        parent::__construct(array("tabla" => "scp_Presupuestal"));
        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->gpo_id = new Atributo("numero", "NO", "PRI", "gpo_id");
        $this->ur_id = new Atributo("numero", "NO", "PRI", "ur_id");
        $this->clc_id = new Atributo("numero", "NO", "PRI", "clc_id");
        $this->afe_id = new Atributo("numero", "NO", "PRI", "afe_id");
        $this->ncp_id = new Atributo("numero", "NO", "PRI", "ncp_id");
        $this->ue_id = new Atributo("numero", "NO", "", "ue_id");
        $this->cmp_id = new Atributo("numero", "NO", "MUL", "cmp_id");
        $this->epg_id = new Atributo("numero", "NO", "", "epg_id");
        $this->pg_id = new Atributo("numero", "NO", "", "pg_id");
        $this->pe_id = new Atributo("numero", "NO", "", "pe_id");
        $this->ecf_id = new Atributo("numero", "NO", "", "ecf_id");
        $this->tcp_id = new Atributo("cadena", "NO", "", "tcp_id");
        $this->rfc_id = new Atributo("cadena", "NO", "MUL", "rfc_id");
        $this->ncp_concepto = new Atributo("cadena", "NO", "", "ncp_concepto");
        $this->ncp_referencia = new Atributo("cadena", "NO", "MUL", "ncp_referencia");
        $this->ncp_fechaReferencia = new Atributo("fecha", "NO", "", "ncp_fechaReferencia");
        $this->ncp_percepcionBruto = new Atributo("numero", "NO", "", "ncp_percepcionBruto");
        $this->iva_id = new Atributo("numero", "NO", "MUL", "iva_id");
        $this->ncp_percepcionIva = new Atributo("numero", "NO", "", "ncp_percepcionIva");
        $this->ncp_percepcionNeto = new Atributo("numero", "NO", "", "ncp_percepcionNeto");
        $this->tem_carga = new Atributo("cadena", "NO", "", "tem_carga");
        $this->bdo_id = new Atributo("numero", "NO", "MUL", "bdo_id");
        $this->tpd_id = new Atributo("numero", "NO", "MUL", "tpd_id");
        $this->ncp_otroDocumento = new Atributo("cadena", "YES", "", "ncp_otroDocumento");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        $ncp_id = (!isset($prm->ncp_id)) ? '' : $prm->ncp_id;
        $gpo_id = (!isset($prm->gpo_id)) ? '' : $prm->gpo_id;
        $ur_id = (!isset($prm->ur_id)) ? '' : $prm->ur_id;
        $clc_id = (!isset($prm->ur_id)) ? '' : $prm->clc_id;
        $afe_id = (!isset($prm->afe_id)) ? '' : $prm->afe_id;
        if ($ncp_id != '') {
            $this->setNcp_id($ncp_id);
            $this->setGpo_id($gpo_id);
            $this->setUr_id($ur_id);
            $this->setClc_id($clc_id);
            $this->setAfe_id($afe_id);
            $this->select();
        }
    }
    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function abc($p)
    {
        $ncp = new Presupuestal();
        if (isset($p->gpo_id)) {
            $ncp->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $ncp->setUr_id($p->ur_id);
        }

        if (isset($p->clc_id)) {
            $ncp->setClc_id($p->clc_id);
        }

        if (isset($p->afe_id)) {
            $ncp->setAfe_id($p->afe_id);
        }

        if (isset($p->ncp_id)) {
            $ncp->setNcp_id($p->ncp_id);
        }

        if (isset($p->ue_id)) {
            $ncp->setUe_id($p->ue_id);
        }

        if (isset($p->cmp_id)) {
            $ncp->setCmp_id($p->cmp_id);
        }

        if (isset($p->epg_id)) {
            $ncp->setEpg_id($p->epg_id);
        }

        if (isset($p->pg_id)) {
            $ncp->setPg_id($p->pg_id);
        }

        if (isset($p->pe_id)) {
            $ncp->setPe_id($p->pe_id);
        }

        if (isset($p->ecf_id)) {
            $ncp->setEcf_id($p->ecf_id);
        }

        if (isset($p->tcp_id)) {
            $ncp->setTcp_id($p->tcp_id);
        }

        if (isset($p->rfc_id)) {
            $ncp->setRfc_id($p->rfc_id);
        }

        if (isset($p->concepto)) {
            $ncp->setConcepto(htmlspecialchars($p->concepto));
        }

        if (isset($p->referencia)) {
            $ncp->setReferencia($p->referencia);
        }

        if (isset($p->fechaReferencia)) {
            $ncp->setFechaReferencia($p->fechaReferencia);
        }

        if (isset($p->percepcionBruto)) {
            $ncp->setPercepcionBruto($p->percepcionBruto);
        }

        if (isset($p->iva_id)) {
            $ncp->setIva_id($p->iva_id);
        }

        if (isset($p->percepcionIva)) {
            $ncp->setPercepcionIva($p->percepcionIva);
        }

        if (isset($p->percepcionNeto)) {
            $ncp->setPercepcionNeto($p->percepcionNeto);
        }

        if (isset($p->comprobado)) {
            $ncp->setComprobado($p->comprobado);
        }

        if (isset($p->carga)) {
            $ncp->setCarga($p->carga);
        }

        if (isset($p->fechaDevengo)) {
            $ncp->setFechaDevengo($p->fechaDevengo);
        }

        if (isset($p->bdo_id)) {
            $ncp->setBdo_id($p->bdo_id);
        }

        if (isset($p->tpd_id)) {
            $ncp->setTpd_id($p->tpd_id);
        }

        if (isset($p->otroDocumento)) {
            $ncp->setOtroDocumento($p->otroDocumento);
        }

        if (isset($p->usr_id)) {
            $ncp->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case "a":$ncp->insert();return $ncp->getUltimoId();
                break;
            case "b":$ncp->delete();
                break;
            case "c":$ncp->update();
                break;
        }
    }
}
