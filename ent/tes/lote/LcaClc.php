<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class LcaClc extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $msc_id; //Identificador del Mes - cat_Mes
    protected $lca_ejercicio; //Ejercicio de la Clc y Linea de Captura - Llave Foranea-cat_Ejercicio
    protected $lca_ejr; //Ejercicio actual del sistema
    protected $gpo_id; //Identificador Grupo de la Clc
    protected $ur_id; //Identificador de la Ur de la Clc
    protected $clc_id; //Identificador de la  CLC
    protected $lca_folio; //
    protected $tdf_id; //Identificador Tipo Documento Fuente
    protected $lca_referencia; //
    protected $lca_fecha; //
    protected $lca_importeClc; //
    protected $lca_procesado; //Si tiene el valor 2 significa que esta deshabilitado
    protected $sts_id; //Estado del registro
    protected $usr_id; //Identificador de usuario que captura/actualiza el registro

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getMsc_id()
    {
        return $this->msc_id->getVal();
    }
    public function getEjercicio()
    {
        return $this->lca_ejercicio->getVal();
    }
    public function getEjr()
    {
        return $this->lca_ejr->getVal();
    }
    public function getGpo_id()
    {
        return $this->gpo_id->getVal();
    }
    public function getUr_id()
    {
        return $this->ur_id->getVal();
    }
    public function getClc_id()
    {
        return $this->clc_id->getVal();
    }
    public function getFolio()
    {
        return $this->lca_folio->getVal();
    }
    public function getTdf_id()
    {
        return $this->tdf_id->getVal();
    }
    public function getReferencia()
    {
        return $this->lca_referencia->getVal();
    }
    public function getFecha()
    {
        return $this->lca_fecha->getVal();
    }
    public function getImporteClc()
    {
        return $this->lca_importeClc->getVal();
    }
    public function getProcesado()
    {
        return $this->lca_procesado->getVal();
    }
    public function getSts_id()
    {
        return $this->sts_id->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setMsc_id($val)
    {
        $this->msc_id->setVal($val);
    }
    public function setEjercicio($val)
    {
        $this->lca_ejercicio->setVal($val);
    }
    public function setEjr($val)
    {
        $this->lca_ejr->setVal($val);
    }
    public function setGpo_id($val)
    {
        $this->gpo_id->setVal($val);
    }
    public function setUr_id($val)
    {
        $this->ur_id->setVal($val);
    }
    public function setClc_id($val)
    {
        $this->clc_id->setVal($val);
    }
    public function setFolio($val)
    {
        $this->lca_folio->setVal($val);
    }
    public function setTdf_id($val)
    {
        $this->tdf_id->setVal($val);
    }
    public function setReferencia($val)
    {
        $this->lca_referencia->setVal($val);
    }
    public function setFecha($val)
    {
        $this->lca_fecha->setVal($val);
    }
    public function setImporteClc($val)
    {
        $this->lca_importeClc->setVal($val);
    }
    public function setProcesado($val)
    {
        $this->lca_procesado->setVal($val);
    }
    public function setSts_id($val)
    {
        $this->sts_id->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staMsc_id($val)
    {
        $this->msc_id->staVal($val);
    }
    public function staEjercicio($val)
    {
        $this->lca_ejercicio->staVal($val);
    }
    public function staEjr($val)
    {
        $this->lca_ejr->staVal($val);
    }
    public function staGpo_id($val)
    {
        $this->gpo_id->staVal($val);
    }
    public function staUr_id($val)
    {
        $this->ur_id->staVal($val);
    }
    public function staClc_id($val)
    {
        $this->clc_id->staVal($val);
    }
    public function staFolio($val)
    {
        $this->lca_folio->staVal($val);
    }
    public function staTdf_id($val)
    {
        $this->tdf_id->staVal($val);
    }
    public function staReferencia($val)
    {
        $this->lca_referencia->staVal($val);
    }
    public function staFecha($val)
    {
        $this->lca_fecha->staVal($val);
    }
    public function staImporteClc($val)
    {
        $this->lca_importeClc->staVal($val);
    }
    public function staProcesado($val)
    {
        $this->lca_procesado->staVal($val);
    }
    public function staSts_id($val)
    {
        $this->sts_id->staVal($val);
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

        parent::__construct(array("tabla" => BD_TESORERIA.".lca_Clc"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->msc_id = new Atributo("numero", "NO", "PRI", "msc_id");
        $this->lca_ejercicio = new Atributo("numero", "NO", "PRI", "lca_ejercicio");
        $this->lca_ejr = new Atributo("numero", "NO", "PRI", "lca_ejr");
        $this->gpo_id = new Atributo("numero", "NO", "PRI", "gpo_id");
        $this->ur_id = new Atributo("numero", "NO", "PRI", "ur_id");
        $this->clc_id = new Atributo("numero", "NO", "PRI", "clc_id");
        $this->lca_folio = new Atributo("cadena", "NO", "PRI", "lca_folio");
        $this->tdf_id = new Atributo("cadena", "NO", "MUL", "tdf_id");
        $this->lca_referencia = new Atributo("cadena", "NO", "", "lca_referencia");
        $this->lca_fecha = new Atributo("fecha", "NO", "", "lca_fecha");
        $this->lca_importeClc = new Atributo("numero", "NO", "", "lca_importeClc");
        $this->lca_procesado = new Atributo("numero", "NO", "", "lca_procesado");
        $this->sts_id = new Atributo("numero", "NO", "MUL", "sts_id");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $lca = new LcaClc();
        if (isset($p->msc_id)) {
            $lca->setMsc_id($p->msc_id);
        }
        if (isset($p->ejercicio)) {
            $lca->setEjercicio($p->ejercicio);
        }
        if (isset($p->ejr)) {
            $lca->setEjr($p->ejr);
        }
        if (isset($p->gpo_id)) {
            $lca->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $lca->setUr_id($p->ur_id);
        }
        if (isset($p->clc_id)) {
            $lca->setClc_id($p->clc_id);
        }
        if (isset($p->folio)) {
            $lca->setFolio($p->folio);
        }
        if (isset($p->tdf_id)) {
            $lca->setTdf_id($p->tdf_id);
        }
        if (isset($p->referencia)) {
            $lca->setReferencia($p->referencia);
        }
        if (isset($p->fecha)) {
            $lca->setFecha($p->fecha);
        }
        if (isset($p->importeClc)) {
            $lca->setImporteClc($p->importeClc);
        }
        if (isset($p->procesado)) {
            $lca->setProcesado($p->procesado);
        }
        if (isset($p->sts_id)) {
            $lca->setSts_id($p->sts_id);
        }
        if (isset($p->usr_id)) {
            $lca->setUsr_id($p->usr_id);
        }

        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a":
                $lca->conectar();
                $bit_observacion = "DESDE EL MÓDULO DE LOTES";
                $bit_observacion = 'REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lca->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lca->consultar($params);
                $lca->insert(false);
                return $lca->getUltimoId();
                break;
            case "b":
                $lca->conectar();
                $bit_observacion = "DESDE EL MÓDULO DE LOTES";
                $bit_observacion = 'REGISTRO ELIMINADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lca->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lca->consultar($params);
                $lca->delete(false);
                break;
            case "c":
                $lca->conectar();
                $bit_observacion = "DESDE EL MÓDULO DE LOTES";
                $bit_observacion = 'REGISTRO MODIFICADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lca->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lca->consultar($params);
                $lca->update(false);
                break;
        }
    }
}
