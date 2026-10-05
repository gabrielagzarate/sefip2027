<?php
require_once dirname(__FILE__) . "/../../../lib/constantes.php";
require_once dirname(__FILE__) . "/../../Entidad.php";

class SeguroFianza extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $ccd_id; //
    protected $ced_id; //
    protected $gpo_id; //cat_UnidadEjecutora - Llave foranea
    protected $ur_id; //cat_UnidadEjecutora - Llave Foranea
    protected $ue_id; //cat_UnidadEjecutora - Llave Foranea
    protected $epg_id; //enc_Programatico - Llave Foranea
    protected $pg_id; //cat_PartidaEspecifica - Llave Foranea
    protected $pe_id; //cat_PartidaEspecifica - Llave Foranea
    protected $ecf_id; //enc_ClaveFinanciamiento - Llave Foranea
    protected $sef_concepto; //
    protected $sef_inicioPeriodo; //
    protected $sef_terminoPeriodo; //
    protected $sef_mensualidad; //
    protected $sef_tipoPago;
    protected $sef_tipo;
    protected $unidades; //calendario de unidades por partida
    protected $montos; //calandario de costos por partida
    protected $usr_id; //


    /******************************************************

    METODOS GET

     ******************************************************/

    public function getCcd_id()
    {return $this->ccd_id->getVal();}
    public function getCed_id()
    {return $this->ced_id->getVal();}
    public function getGpo_id()
    {return $this->gpo_id->getVal();}
    public function getUr_id()
    {return $this->ur_id->getVal();}
    public function getUe_id()
    {return $this->ue_id->getVal();}
    public function getEpg_id()
    {return $this->epg_id->getVal();}
    public function getPg_id()
    {return $this->pg_id->getVal();}
    public function getPe_id()
    {return $this->pe_id->getVal();}
    public function getEcf_id()
    {return $this->ecf_id->getVal();}
    public function getConcepto()
    {return $this->sef_concepto->getVal();}
    public function getInicioPeriodo()
    {return $this->sef_inicioPeriodo->getVal();}
    public function getTerminoPeriodo()
    {return $this->sef_terminoPeriodo->getVal();}
    public function getMensualidad()
    {return $this->sef_mensualidad->getVal();}
    public function getUnidades()
    {return $this->unidades;}
    public function getMontos()
    {return $this->montos;}
    public function getUsr_id()
    {return $this->usr_id->getVal();}
    public function getTipoPago()
    {return $this->sef_tipoPago->getVal();}
    public function getTipo()
    {return $this->sef_tipo->getVal();}

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setCcd_id($val)
    {$this->ccd_id->setVal($val);}
    public function setCed_id($val)
    {$this->ced_id->setVal($val);}
    public function setGpo_id($val)
    {$this->gpo_id->setVal($val);}
    public function setUr_id($val)
    {$this->ur_id->setVal($val);}
    public function setUe_id($val)
    {$this->ue_id->setVal($val);}
    public function setEpg_id($val)
    {$this->epg_id->setVal($val);}
    public function setPg_id($val)
    {$this->pg_id->setVal($val);}
    public function setPe_id($val)
    {$this->pe_id->setVal($val);}
    public function setEcf_id($val)
    {$this->ecf_id->setVal($val);}
    public function setConcepto($val)
    {$this->sef_concepto->setVal($val);}
    public function setInicioPeriodo($val)
    {$this->sef_inicioPeriodo->setVal($val);}
    public function setTerminoPeriodo($val)
    {$this->sef_terminoPeriodo->setVal($val);}
    public function setMensualidad($val)
    {$this->sef_mensualidad->setVal($val);}
    public function setUsr_id($val)
    {$this->usr_id->setVal($val);}
    public function setTipoPago($val)
    {$this->sef_tipoPago->setVal($val);}
    public function setTipo($val)
    {$this->sef_tipo->setVal($val);}

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staCcd_id($val)
    {$this->ccd_id->staVal($val);}
    public function staCed_id($val)
    {$this->ced_id->staVal($val);}
    public function staGpo_id($val)
    {$this->gpo_id->staVal($val);}
    public function staUr_id($val)
    {$this->ur_id->staVal($val);}
    public function staUe_id($val)
    {$this->ue_id->staVal($val);}
    public function staEpg_id($val)
    {$this->epg_id->staVal($val);}
    public function staPg_id($val)
    {$this->pg_id->staVal($val);}
    public function staPe_id($val)
    {$this->pe_id->staVal($val);}
    public function staEcf_id($val)
    {$this->ecf_id->staVal($val);}
    public function staConcepto($val)
    {$this->sef_concepto->staVal($val);}
    public function staInicioPeriodo($val)
    {$this->sef_inicioPeriodo->staVal($val);}
    public function staTerminoPeriodo($val)
    {$this->sef_terminoPeriodo->staVal($val);}
    public function staMensualidad($val)
    {$this->sef_mensualidad->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}
    public function staTipoPago($val)
    {$this->sef_tipoPago->staVal($val);}
    public function staTipo($val)
    {$this->sef_tipo->staVal($val);}

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(array("tabla" => "poa_SeguroFianza"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->ccd_id = new Atributo("numero", "NO", "PRI", "ccd_id");
        $this->ced_id = new Atributo("numero", "NO", "PRI", "ced_id");
        $this->gpo_id = new Atributo("numero", "NO", "MUL", "gpo_id");
        $this->ur_id = new Atributo("numero", "NO", "", "ur_id");
        $this->ue_id = new Atributo("numero", "NO", "", "ue_id");
        $this->epg_id = new Atributo("numero", "NO", "", "epg_id");
        $this->pg_id = new Atributo("numero", "NO", "MUL", "pg_id");
        $this->pe_id = new Atributo("numero", "NO", "", "pe_id");
        $this->ecf_id = new Atributo("numero", "NO", "", "ecf_id");
        $this->sef_concepto = new Atributo("cadena", "NO", "", "sef_concepto");
        $this->sef_inicioPeriodo = new Atributo("fecha", "NO", "", "sef_inicioPeriodo");
        $this->sef_terminoPeriodo = new Atributo("fecha", "NO", "", "sef_terminoPeriodo");
        $this->sef_mensualidad = new Atributo("numero", "NO", "", "sef_mensualidad");

        $this->unidades = new Meses(array("prefix" => "uni_"));
        $this->montos = new Meses(array("prefix" => "mon_"));
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");
        $this->sef_tipoPago = new Atributo("cadena", "NO", "", "sef_tipoPago");
        $this->sef_tipo = new Atributo("cadena", "NO", "", "sef_tipo");

        /*Aqui se predefinen algunos atributos, en función de la Cedula*/
        $this->setCcd_id(6); //El Identificador de la Cedula de Seguros y Fianzas es el 6
        $this->setPe_id(341); //La partida específica para Seguros y Fianzas es 341

    }
    //se aumento un campo

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $sef = new SeguroFianza();

        // print_r($sef);
        if (isset($p->ccd_id)) {
            $sef->setCcd_id($p->ccd_id);
        }

        if (isset($p->ced_id)) {
            $sef->setCed_id($p->ced_id);
        }

        if (isset($p->gpo_id)) {
            $sef->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $sef->setUr_id($p->ur_id);
        }

        if (isset($p->ue_id)) {
            $sef->setUe_id($p->ue_id);
        }

        if (isset($p->epg_id)) {
            $sef->setEpg_id($p->epg_id);
        }

        if (isset($p->pg_id)) {
            $sef->setPg_id($p->pg_id);
        }

        if (isset($p->pe_id)) {
            $sef->setPe_id($p->pe_id);
        }

        if (isset($p->ecf_id)) {
            $sef->setEcf_id($p->ecf_id);
        }

        if (isset($p->concepto)) {
            $sef->setConcepto($p->concepto);
        }

        if (isset($p->inicioPeriodo)) {
            $sef->setInicioPeriodo($p->inicioPeriodo);
        }

        if (isset($p->terminoPeriodo)) {
            $sef->setTerminoPeriodo($p->terminoPeriodo);
        }

        if (isset($p->mensualidad)) {
            $sef->setMensualidad($p->mensualidad);
        }

        if (isset($p->unidades)) {
            $sef->getUnidades()->setArrayMeses($p->unidades);
        }

        if (isset($p->montos)) {
            $sef->getMontos()->setArrayMeses($p->montos);
        }

        if (isset($p->usr_id)) {
            $sef->setUsr_id($p->usr_id);
        }

        if (isset($p->tipoPago)) {
            $sef->setTipoPago($p->tipoPago);
        }

        if (isset($p->tipo)) {
            $sef->setTipo($p->tipo);
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        $sef->conectar();
        $params = [$ip, $_SESSION[_S]['usr_id'], $p->bit_observacion];
        $sef->setSql('SET @usr_ip  =INET_ATON(?), @usr_id = ?, @bit_observacion = ? ;');
        $sef->consultar($params);

        switch ($p->abc) {
            case "a":
                $sef->insert(false);
                return $sef->getUltimoId();
                $sef->desconectar();
                break;
            case "b":
                $sef->delete(false);
                break;
            case "c":
                $sef->update(false);
                break;
        }
    }
}