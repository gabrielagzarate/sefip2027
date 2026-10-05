<?php

require_once dirname(__FILE__) . '/Entidad.php';
class Proveedores extends Entidad
{
    /******************************************************
        ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $rfc_id;
    protected $tcy_id;
    protected $soc_id;
    protected $edo_id;
    protected $mun_id;
    protected $rfc_ejercicio;
    protected $rfc_nombre;
    protected $rfc_paterno;
    protected $rfc_materno;
    protected $rfc_curp;
    protected $rfc_calle;
    protected $rfc_exterior;
    protected $rfc_interior;
    protected $rfc_colonia;
    protected $rfc_localidad;
    protected $rfc_referencia;
    protected $rfc_color;
    protected $rfc_cp;
    protected $rfc_telefono;
    protected $rfc_email;
    protected $rfc_imss;
    protected $rfc_numero;
    protected $rfc_cta_id;
    protected $usr_id;
    /******************************************************
            METODOS GET
     ******************************************************/
    public function getRfc_id()
    {
        return $this->rfc_id->getVal();
    }
    public function getTcy_id()
    {
        return $this->tcy_id->getVal();
    }
    public function getSoc_id()
    {
        return $this->soc_id->getVal();
    }
    public function getEdo_id()
    {
        return $this->edo_id->getVal();
    }
    public function getMun_id()
    {
        return $this->mun_id->getVal();
    }
    public function getEjercicio()
    {
        return $this->rfc_ejercicio->getVal();
    }
    public function getNombre()
    {
        return $this->rfc_nombre->getVal();
    }
    public function getPaterno()
    {
        return $this->rfc_paterno->getVal();
    }
    public function getMaterno()
    {
        return $this->rfc_materno->getVal();
    }
    public function getCurp()
    {
        return $this->rfc_curp->getVal();
    }
    public function getCalle()
    {
        return $this->rfc_calle->getVal();
    }
    public function getExterior()
    {
        return $this->rfc_exterior->getVal();
    }
    public function getInterior()
    {
        return $this->rfc_interior->getVal();
    }
    public function getColonia()
    {
        return $this->rfc_colonia->getVal();
    }
    public function getLocalidad()
    {
        return $this->rfc_localidad->getVal();
    }
    public function getReferencia()
    {
        return $this->rfc_referencia->getVal();
    }
    public function getColor()
    {
        return $this->rfc_color->getVal();
    }
    public function getCp()
    {
        return $this->rfc_cp->getVal();
    }
    public function getTelefono()
    {
        return $this->rfc_telefono->getVal();
    }
    public function getEmail()
    {
        return $this->rfc_email->getVal();
    }
    public function getImss()
    {
        return $this->rfc_imss->getVal();
    }
    public function getNumero()
    {
        return $this->rfc_numero->getVal();
    }
    public function getRfc_cta_id()
    {
        return $this->rfc_cta_id->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    /******************************************************
            METODOS SET
     ******************************************************/
    public function setRfc_id($val)
    {
        $this->rfc_id->setVal($val);
    }
    public function setTcy_id($val)
    {
        $this->tcy_id->setVal($val);
    }
    public function setSoc_id($val)
    {
        $this->soc_id->setVal($val);
    }
    public function setEdo_id($val)
    {
        $this->edo_id->setVal($val);
    }
    public function setMun_id($val)
    {
        $this->mun_id->setVal($val);
    }
    public function setEjercicio($val)
    {
        $this->rfc_ejercicio->setVal($val);
    }
    public function setNombre($val)
    {
        $this->rfc_nombre->setVal($val);
    }
    public function setPaterno($val)
    {
        $this->rfc_paterno->setVal($val);
    }
    public function setMaterno($val)
    {
        $this->rfc_materno->setVal($val);
    }
    public function setCurp($val)
    {
        $this->rfc_curp->setVal($val);
    }
    public function setCalle($val)
    {
        $this->rfc_calle->setVal($val);
    }
    public function setExterior($val)
    {
        $this->rfc_exterior->setVal($val);
    }
    public function setInterior($val)
    {
        $this->rfc_interior->setVal($val);
    }
    public function setColonia($val)
    {
        $this->rfc_colonia->setVal($val);
    }
    public function setLocalidad($val)
    {
        $this->rfc_localidad->setVal($val);
    }
    public function setReferencia($val)
    {
        $this->rfc_referencia->setVal($val);
    }
    public function setColor($val)
    {
        $this->rfc_color->setVal($val);
    }
    public function setCp($val)
    {
        $this->rfc_cp->setVal($val);
    }
    public function setTelefono($val)
    {
        $this->rfc_telefono->setVal($val);
    }
    public function setEmail($val)
    {
        $this->rfc_email->setVal($val);
    }
    public function setImss($val)
    {
        $this->rfc_imss->setVal($val);
    }
    public function setNumero($val)
    {
        $this->rfc_numero->setVal($val);
    }
    public function setRfc_cta_id($val)
    {
        $this->rfc_cta_id->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    /******************************************************
            METODOS STA (SET sin persistencia)
     ******************************************************/
    public function staRfc_id($val)
    {
        $this->rfc_id->staVal($val);
    }
    public function staTcy_id($val)
    {
        $this->tcy_id->staVal($val);
    }
    public function staSoc_id($val)
    {
        $this->soc_id->staVal($val);
    }
    public function staEdo_id($val)
    {
        $this->edo_id->staVal($val);
    }
    public function staMun_id($val)
    {
        $this->mun_id->staVal($val);
    }
    public function staEjercicio($val)
    {
        $this->rfc_ejercicio->staVal($val);
    }
    public function staNombre($val)
    {
        $this->rfc_nombre->staVal($val);
    }
    public function staPaterno($val)
    {
        $this->rfc_paterno->staVal($val);
    }
    public function staMaterno($val)
    {
        $this->rfc_materno->staVal($val);
    }
    public function staCurp($val)
    {
        $this->rfc_curp->staVal($val);
    }
    public function staCalle($val)
    {
        $this->rfc_calle->staVal($val);
    }
    public function staExterior($val)
    {
        $this->rfc_exterior->staVal($val);
    }
    public function staInterior($val)
    {
        $this->rfc_interior->staVal($val);
    }
    public function staColonia($val)
    {
        $this->rfc_colonia->staVal($val);
    }
    public function staLocalidad($val)
    {
        $this->rfc_localidad->staVal($val);
    }
    public function staReferencia($val)
    {
        $this->rfc_referencia->staVal($val);
    }
    public function staColor($val)
    {
        $this->rfc_color->staVal($val);
    }
    public function staCp($val)
    {
        $this->rfc_cp->staVal($val);
    }
    public function staTelefono($val)
    {
        $this->rfc_telefono->staVal($val);
    }
    public function staEmail($val)
    {
        $this->rfc_email->staVal($val);
    }
    public function staImss($val)
    {
        $this->rfc_imss->staVal($val);
    }
    public function staNumero($val)
    {
        $this->rfc_numero->staVal($val);
    }
    public function staRfc_cta_id($val)
    {
        $this->rfc_cta_id->staVal($val);
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
        parent::__construct(['tabla' => 'sinpres.cat_Proveedores']);
        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->rfc_id = new Atributo('cadena', 'NO', 'PRI', 'rfc_id');
        $this->tcy_id = new Atributo('numero', 'NO', 'MUL', 'tcy_id');
        $this->soc_id = new Atributo('numero', 'NO', 'MUL', 'soc_id');
        $this->edo_id = new Atributo('numero', 'NO', 'MUL', 'edo_id');
        $this->mun_id = new Atributo('numero', 'NO', '', 'mun_id');
        $this->rfc_ejercicio = new Atributo('numero', 'NO', '', 'rfc_ejercicio');
        $this->rfc_nombre = new Atributo('cadena', 'YES', '', 'rfc_nombre');
        $this->rfc_paterno = new Atributo('cadena', 'NO', '', 'rfc_paterno');
        $this->rfc_materno = new Atributo('cadena', 'NO', '', 'rfc_materno');
        $this->rfc_curp = new Atributo('cadena', 'NO', '', 'rfc_curp');
        $this->rfc_calle = new Atributo('cadena', 'NO', '', 'rfc_calle');
        $this->rfc_exterior = new Atributo('cadena', 'NO', '', 'rfc_exterior');
        $this->rfc_interior = new Atributo('cadena', 'NO', '', 'rfc_interior');
        $this->rfc_colonia = new Atributo('cadena', 'NO', '', 'rfc_colonia');
        $this->rfc_localidad = new Atributo('cadena', 'NO', '', 'rfc_localidad');
        $this->rfc_referencia = new Atributo('cadena', 'NO', '', 'rfc_referencia');
        $this->rfc_color = new Atributo('cadena', 'NO', '', 'rfc_color');
        $this->rfc_cp = new Atributo('cadena', 'NO', '', 'rfc_cp');
        $this->rfc_telefono = new Atributo('cadena', 'NO', '', 'rfc_telefono');
        $this->rfc_email = new Atributo('cadena', 'NO', '', 'rfc_email');
        $this->rfc_imss = new Atributo('cadena', 'NO', '', 'rfc_imss');
        $this->rfc_numero = new Atributo('numero', 'NO', '', 'rfc_numero');
        $this->rfc_cta_id = new Atributo('cadena', 'NO', '', 'rfc_cta_id');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');
    }
    /******************************************************
            FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function abc($p)
    {
        $rfc = new Proveedores();

        if (isset($p->rfc_id)) {
            $rfc->setRfc_id($p->rfc_id);
        }
        if (isset($p->tcy_id)) {
            $rfc->setTcy_id($p->tcy_id);
        }
        if (isset($p->soc_id)) {
            $rfc->setSoc_id($p->soc_id);
        }
        if (isset($p->edo_id)) {
            $rfc->setEdo_id($p->edo_id);
        }
        if (isset($p->mun_id)) {
            $rfc->setMun_id($p->mun_id);
        }
        if (isset($p->ejercicio)) {
            $rfc->setEjercicio($p->ejercicio);
        }
        if (isset($p->nombre)) {
            $rfc->setNombre($p->nombre);
        }
        if (isset($p->paterno)) {
            $rfc->setPaterno($p->paterno);
        }
        if (isset($p->materno)) {
            $rfc->setMaterno($p->materno);
        }
        if (isset($p->curp)) {
            $rfc->setCurp($p->curp);
        }
        if (isset($p->calle)) {
            $rfc->setCalle($p->calle);
        }
        if (isset($p->exterior)) {
            $rfc->setExterior($p->exterior);
        }
        if (isset($p->interior)) {
            $rfc->setInterior($p->interior);
        }
        if (isset($p->colonia)) {
            $rfc->setColonia($p->colonia);
        }
        if (isset($p->localidad)) {
            $rfc->setLocalidad($p->localidad);
        }
        if (isset($p->referencia)) {
            $rfc->setReferencia($p->referencia);
        }
        if (isset($p->color)) {
            $rfc->setColor($p->color);
        }
        if (isset($p->cp)) {
            $rfc->setCp($p->cp);
        }
        if (isset($p->telefono)) {
            $rfc->setTelefono($p->telefono);
        }
        if (isset($p->email)) {
            $rfc->setEmail($p->email);
        }
        if (isset($p->imss)) {
            $rfc->setImss($p->imss);
        }
        if (isset($p->numero)) {
            $rfc->setNumero($p->numero);
        }
        if (isset($p->rfc_cta_id)) {
            $rfc->setRfc_cta_id($p->rfc_cta_id);
        }
        if (isset($p->usr_id)) {
            $rfc->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case 'a':
                $rfc->conectar();
                $rfc->setSql(' SET @inserta=' . APP_EJERCICIO . ';');
                $rfc->consultar();
                $rfc->insert(false);
                return $rfc->getUltimoId();
                break;
                break;
            case 'b':
                $rfc->conectar();
                $rfc->setSql('SET @inserta=' . APP_EJERCICIO . '; ');
                $rfc->consultar();
                $rfc->delete(false);
                break;
            case 'c':
                $rfc->conectar();
                $rfc->setSql('SET @inserta=' . APP_EJERCICIO . '; ');
                $rfc->consultar();
                $rfc->update(false);
                break;
        }
    }
}
