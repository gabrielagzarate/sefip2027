<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class LineaCaptura extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $lca_id; //
    protected $lca_lineaCaptura; //
    protected $lca_fechaGeneracion; //
    protected $lca_fechaVencimiento; //
    protected $lca_importe; //
    protected $lca_clabeAbono; //
    protected $ejr_id; //
    protected $tlc_id; //
    protected $elc_id; //
    protected $usr_id; //
    protected $lca_folio;
    protected $cov_id;

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getLca_id()
    {
        return $this->lca_id->getVal();
    }
    public function getlca_folio()
    {
        return $this->lca_folio->getVal();
    }
    public function getLineaCaptura()
    {
        return $this->lca_lineaCaptura->getVal();
    }
    public function getFechaGeneracion()
    {
        return $this->lca_fechaGeneracion->getVal();
    }
    public function getFechaVencimiento()
    {
        return $this->lca_fechaVencimiento->getVal();
    }
    public function getImporte()
    {
        return $this->lca_importe->getVal();
    }
    public function getCuentaAbono()
    {
        return $this->lca_clabeAbono->getVal();
    }
    public function getEjr_id()
    {
        return $this->ejr_id->getVal();
    }
    public function getTlc_id()
    {
        return $this->tlc_id->getVal();
    }
    public function getElc_id()
    {
        return $this->elc_id->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    public function getidConvenioBanco()
    {
        return $this->cov_id->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setLca_id($val)
    {
        $this->lca_id->setVal($val);
    }
    public function setlca_folio($val)
    {
        $this->lca_folio->setVal($val);
    }
    public function setLineaCaptura($val)
    {
        $this->lca_lineaCaptura->setVal($val);
    }
    public function setFechaGeneracion($val)
    {
        $this->lca_fechaGeneracion->setVal($val);
    }
    public function setFechaVencimiento($val)
    {
        $this->lca_fechaVencimiento->setVal($val);
    }
    public function setImporte($val)
    {
        $this->lca_importe->setVal($val);
    }
    public function setCuentaAbono($val)
    {
        $this->lca_clabeAbono->setVal($val);
    }
    public function setEjr_id($val)
    {
        $this->ejr_id->setVal($val);
    }
    public function setTlc_id($val)
    {
        $this->tlc_id->setVal($val);
    }
    public function setElc_id($val)
    {
        $this->elc_id->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    public function setidConvenioBanco($val)
    {
        $this->cov_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staLca_id($val)
    {
        $this->lca_id->staVal($val);
    }
    public function stalca_folio($val)
    {
        $this->lca_folio->staVal($val);
    }
    public function staLineaCaptura($val)
    {
        $this->lca_lineaCaptura->staVal($val);
    }
    public function staFechaGeneracion($val)
    {
        $this->lca_fechaGeneracion->staVal($val);
    }
    public function staFechaVencimiento($val)
    {
        $this->lca_fechaVencimiento->staVal($val);
    }
    public function staImporte($val)
    {
        $this->lca_importe->staVal($val);
    }
    public function staCuentaAbono($val)
    {
        $this->lca_clabeAbono->staVal($val);
    }
    public function staEjr_id($val)
    {
        $this->ejr_id->staVal($val);
    }
    public function staTlc_id($val)
    {
        $this->tlc_id->staVal($val);
    }
    public function staElc_id($val)
    {
        $this->elc_id->staVal($val);
    }
    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }
    public function staidConvenioBanco($val)
    {
        $this->cov_id->staVal($val);
    }

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(array("tabla" => BD_TESORERIA.".lca_LineaCaptura"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->lca_id = new Atributo("numero", "NO", "PRI", "lca_id");
        $this->lca_folio = new Atributo("cadena", "NO", "MUL", "lca_folio");
        $this->lca_lineaCaptura = new Atributo("cadena", "NO", "MUL", "lca_lineaCaptura");
        $this->lca_fechaGeneracion = new Atributo("fecha", "NO", "", "lca_fechaGeneracion");
        $this->lca_fechaVencimiento = new Atributo("fecha", "NO", "", "lca_fechaVencimiento");
        $this->lca_importe = new Atributo("numero", "NO", "", "lca_importe");
        $this->lca_clabeAbono = new Atributo("numero", "NO", "MUL", "lca_clabeAbono");
        $this->ejr_id = new Atributo("numero", "NO", "MUL", "ejr_id");
        $this->tlc_id = new Atributo("numero", "NO", "MUL", "tlc_id");
        $this->elc_id = new Atributo("numero", "NO", "MUL", "elc_id");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");
        $this->cov_id = new Atributo("numero", "NO", "", "cov_id");
    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $lca = new LineaCaptura();
        $bit_observacion = "";
        if (isset($p->lca_folio)) {
            $lca->setlca_folio($p->lca_folio);
            $bit_observacion .= 'lca_folio: ' . $p->lca_folio;
        }
        if (isset($p->lca_id)) {
            $lca->setLca_id($p->lca_id);
            $bit_observacion .= 'lca_id: ' . $p->lca_id;
        }
        if (isset($p->lineaCaptura)) {
            $lca->setLineaCaptura($p->lineaCaptura);
            $bit_observacion .= 'lineaCaptura: ' . $p->lineaCaptura;
        }
        if (isset($p->fechaGeneracion)) {
            $lca->setFechaGeneracion($p->fechaGeneracion);
            $bit_observacion .= 'fechaGeneracion: ' . $p->fechaGeneracion;
        }
        if (isset($p->fechaVencimiento)) {
            $lca->setFechaVencimiento($p->fechaVencimiento);
            $bit_observacion .= 'fechaVencimiento: ' . $p->fechaVencimiento;
        }
        if (isset($p->importe)) {
            $lca->setImporte($p->importe);
            $bit_observacion .= 'importe: ' . $p->importe;
        }
        if (isset($p->cuentaAbono)) {
            $lca->setCuentaAbono($p->cuentaAbono);
            $bit_observacion .= 'cuentaAbono: ' . $p->cuentaAbono;
        }
        if (isset($p->ejr_id)) {
            $lca->setEjr_id($p->ejr_id);
            $bit_observacion .= 'ejr_id: ' . $p->ejr_id;
        }
        if (isset($p->tlc_id)) {
            $lca->setTlc_id($p->tlc_id);
            $bit_observacion .= 'tlc_id: ' . $p->tlc_id;
        }
        if (isset($p->elc_id)) {
            $lca->setElc_id($p->elc_id);
            $bit_observacion .= 'elc_id: ' . $p->elc_id;
        }
        if (isset($p->usr_id)) {
            $lca->setUsr_id($p->usr_id);
            $bit_observacion .= 'usr_id: ' . $p->usr_id;
        }

        if (isset($p->idConvenioBanco)) {
            $lca->setidConvenioBanco($p->idConvenioBanco);
            $bit_observacion .= 'idConvenioBanco: ' . $p->idConvenioBanco;
        }
        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != "::1") ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
        $params = [$ip,$bit_usr_id];
        switch ($p->abc) {
            case "a":
                $lca->conectar();
                $bit_observacion = 'REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lca->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lca->consultar($params);
                $lca->insert(false);
                return $lca->getUltimoId();
                break;
            case "b":
                $lca->conectar();
                $bit_observacion = 'REGISTRO BORRADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lca->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lca->consultar($params);
                $lca->delete(false);
                break;
            case "c":
                $lca->conectar();
                $bit_observacion = 'REGISTRO ACTUALIZADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lca->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lca->consultar($params);
                $lca->update(false);
                break;
        }
    }
}
