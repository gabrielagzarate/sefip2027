<?php
require_once dirname(__FILE__)."/../../Entidad.php";

class CuentaBancariaSefin extends Entidad
{
    /****************************************************** 

        ATRIBUTOS DE LA CLASE

    ******************************************************/ 
    protected $cbs_id;	//CLABE interbancaria
    protected $cbs_cuenta;	//Cuenta Bancaria
    protected $cbs_fechaApertura;	//Fecha de Apertura de la Cuenta Bancaria
    protected $cbs_fechaCancelacion; //Fecha de Cancelación de la Cuenta Bancaria
    protected $cbs_anio;	//Ejercicio de uso de la Cuenta Bancaria
    protected $cbs_sucursal;	//Sucursal Bancaria
    protected $cbs_nombre;	//Nombre de la Cuenta Bancaria
    protected $cbs_alias;	//Alias de la Cuenta Bancaria
    protected $cbs_cliente;	//Número de Cliente de la Cuenta Bancaria
    protected $cbs_contrato;	//Número de Contrato de la Cuenta Bancaria
    protected $cbs_cuentaInversion;	//Cuenta de Inversión
    protected $cbs_inversion;	//Define si la Cuenta Bancaria es de Inversión
    protected $cbs_recaudadora;	//Define si la Cuenta Bancaria es Recaudadora
    protected $cbs_concentradora;	//Define si la Cuenta Bancaria es Concentradora
    protected $cbs_pagadora;	//Define si la Cuenta Bancaria es Pagadora
    protected $cbs_receptora;	//Recibe Recurso Federal
    protected $cbs_observacion;	//Observaciones de la Cuenta Bancaria
    protected $iba_id;	//cat_InstitucionBancaria - Llave foranea - Identificador de la Institución Bancaria
    protected $ter_id;	//cat_TipoRecurso - Llave foranea - Identificador del Tipo de Recurso
    protected $pbn_id;	//cat_PlazaBancaria - Llave foranea - Identificador de la Plaza Bancaria
    protected $eba_id;	//cat_EstadoCuenta - Llave foranea - Identificador del Estado Cuenta
    protected $tfn_id;	//cat_TipoFondo - Llave foranea - Identificador del Tipo de Fondo
    protected $usr_id;	//Identificador de usuario que captura el registro

    /****************************************************** 

        METODOS GET

    ******************************************************/ 

    public function getCbs_id() { return $this->cbs_id->getVal(); }
    public function getCuenta() { return $this->cbs_cuenta->getVal(); }
    public function getFechaApertura() { return $this->cbs_fechaApertura->getVal(); }
    public function getFechaCancelacion() { return $this->cbs_fechaCancelacion->getVal(); }
    public function getAnio() { return $this->cbs_anio->getVal(); }
    public function getSucursal() { return $this->cbs_sucursal->getVal(); }
    public function getNombre() { return $this->cbs_nombre->getVal(); }
    public function getAlias() { return $this->cbs_alias->getVal(); }
    public function getCliente() { return $this->cbs_cliente->getVal(); }
    public function getContrato() { return $this->cbs_contrato->getVal(); }
    public function getCuentaInversion() { return $this->cbs_cuentaInversion->getVal(); }
    public function getInversion() { return $this->cbs_inversion->getVal(); }
    public function getRecaudadora() { return $this->cbs_recaudadora->getVal(); }
    public function getConcentradora() { return $this->cbs_concentradora->getVal(); }
    public function getPagadora() { return $this->cbs_pagadora->getVal(); }
    public function getReceptora() { return $this->cbs_receptora->getVal(); }
    public function getObservacion() { return $this->cbs_observacion->getVal(); }
    public function getIba_id() { return $this->iba_id->getVal(); }
    public function getTer_id() { return $this->ter_id->getVal(); }
    public function getPbn_id() { return $this->pbn_id->getVal(); }
    public function getEba_id() { return $this->eba_id->getVal(); }
    public function getTfn_id() { return $this->tfn_id->getVal(); }
    public function getUsr_id() { return $this->usr_id->getVal(); }

    /****************************************************** 

        METODOS SET

    ******************************************************/ 

    public function setCbs_id($val) { $this->cbs_id->setVal($val); }
    public function setCuenta($val) { $this->cbs_cuenta->setVal($val); }
    public function setFechaApertura($val) { $this->cbs_fechaApertura->setVal($val); }
    public function setFechaCancelacion($val) { $this->cbs_fechaCancelacion->setVal($val); }
    public function setAnio($val) { $this->cbs_anio->setVal($val); }
    public function setSucursal($val) { $this->cbs_sucursal->setVal($val); }
    public function setNombre($val) { $this->cbs_nombre->setVal($val); }
    public function setAlias($val) { $this->cbs_alias->setVal($val); }
    public function setCliente($val) { $this->cbs_cliente->setVal($val); }
    public function setContrato($val) { $this->cbs_contrato->setVal($val); }
    public function setCuentaInversion($val) { $this->cbs_cuentaInversion->setVal($val); }
    public function setInversion($val) { $this->cbs_inversion->setVal($val); }
    public function setRecaudadora($val) { $this->cbs_recaudadora->setVal($val); }
    public function setConcentradora($val) { $this->cbs_concentradora->setVal($val); }
    public function setPagadora($val) { $this->cbs_pagadora->setVal($val); }
    public function setReceptora($val) { $this->cbs_receptora->setVal($val); }
    public function setObservacion($val) { $this->cbs_observacion->setVal($val); }
    public function setIba_id($val) { $this->iba_id->setVal($val); }
    public function setTer_id($val) { $this->ter_id->setVal($val); }
    public function setPbn_id($val) { $this->pbn_id->setVal($val); }
    public function setEba_id($val) { $this->eba_id->setVal($val); }
    public function setTfn_id($val) { $this->tfn_id->setVal($val); }
    public function setUsr_id($val) { $this->usr_id->setVal($val); }

    /****************************************************** 

        METODOS STA (SET sin persistencia)

    ******************************************************/ 

    public function staCbs_id($val) { $this->cbs_id->staVal($val); }
    public function staCuenta($val) { $this->cbs_cuenta->staVal($val); }
    public function staFechaApertura($val) { $this->cbs_fechaApertura->staVal($val); }
    public function staFechaCancelacion($val) { $this->cbs_fechaCancelacion->staVal($val); }
    public function staAnio($val) { $this->cbs_anio->staVal($val); }
    public function staSucursal($val) { $this->cbs_sucursal->staVal($val); }
    public function staNombre($val) { $this->cbs_nombre->staVal($val); }
    public function staAlias($val) { $this->cbs_alias->staVal($val); }
    public function staCliente($val) { $this->cbs_cliente->staVal($val); }
    public function staContrato($val) { $this->cbs_contrato->staVal($val); }
    public function staCuentaInversion($val) { $this->cbs_cuentaInversion->staVal($val); }
    public function staInversion($val) { $this->cbs_inversion->staVal($val); }
    public function staRecaudadora($val) { $this->cbs_recaudadora->staVal($val); }
    public function staConcentradora($val) { $this->cbs_concentradora->staVal($val); }
    public function staPagadora($val) { $this->cbs_pagadora->staVal($val); }
    public function staReceptora($val) { $this->cbs_receptora->staVal($val); }
    public function staObservacion($val) { $this->cbs_observacion->staVal($val); }
    public function staIba_id($val) { $this->iba_id->staVal($val); }
    public function staTer_id($val) { $this->ter_id->staVal($val); }
    public function staPbn_id($val) { $this->pbn_id->staVal($val); }
    public function staEba_id($val) { $this->eba_id->staVal($val); }
    public function staTfn_id($val) { $this->tfn_id->staVal($val); }
    public function staUsr_id($val) { $this->usr_id->staVal($val); }

    /****************************************************** 

        CONSTRUCTOR

    ******************************************************/ 

    public function  __construct()
    {
        parent::__construct(array("tabla" => BD_TESORERIA . ".ban_CuentaBancariaSefin"));
        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->cbs_id = new Atributo("numero","NO","PRI","cbs_id");
        $this->cbs_cuenta = new Atributo("numero","NO","MUL","cbs_cuenta");
        $this->cbs_fechaApertura = new Atributo("fecha","NO","","cbs_fechaApertura");
        $this->cbs_fechaCancelacion = new Atributo("fecha","NO","","cbs_fechaCancelacion");
        $this->cbs_anio = new Atributo("numero","NO","MUL","cbs_anio");
        $this->cbs_sucursal = new Atributo("numero","NO","","cbs_sucursal");
        $this->cbs_nombre = new Atributo("cadena","NO","","cbs_nombre");
        $this->cbs_alias = new Atributo("cadena","NO","","cbs_alias");
        $this->cbs_cliente = new Atributo("cadena","NO","","cbs_cliente");
        $this->cbs_contrato = new Atributo("cadena","YES","","cbs_contrato");
        $this->cbs_cuentaInversion = new Atributo("numero","YES","","cbs_cuentaInversion");
        $this->cbs_inversion = new Atributo("numero","NO","MUL","cbs_inversion");
        $this->cbs_recaudadora = new Atributo("numero","NO","MUL","cbs_recaudadora");
        $this->cbs_concentradora = new Atributo("numero","NO","MUL","cbs_concentradora");
        $this->cbs_pagadora = new Atributo("numero","NO","MUL","cbs_pagadora");
        $this->cbs_receptora = new Atributo("numero","NO","","cbs_receptora");
        $this->cbs_observacion = new Atributo("cadena","NO","","cbs_observacion");
        $this->iba_id = new Atributo("numero","NO","MUL","iba_id");
        $this->ter_id = new Atributo("cadena","NO","MUL","ter_id");
        $this->pbn_id = new Atributo("numero","NO","MUL","pbn_id");
        $this->eba_id = new Atributo("numero","NO","MUL","eba_id");
        $this->tfn_id = new Atributo("numero","NO","MUL","tfn_id");
        $this->usr_id = new Atributo("numero","NO","","usr_id");
    }

    /****************************************************** 

        FUNCIONES ESTÁTICAS

    ******************************************************/ 

    public static function abc($p)
    {
        $cbs =  new CuentaBancariaSefin();
        $bit_observacion = '';
        if (isset($p->bit_complementario)) {
            $bit_observacion .= ' observaciones:'.$p->bit_complementario;
        } 
        if (isset($p->cbs_id)) {
            $cbs->setCbs_id($p->cbs_id);
            $bit_observacion .= ' cbs:'.$p->cbs_id;
        }
        if (isset($p->cuenta)) {
            $cbs->setCuenta($p->cuenta);
        } 
        if (isset($p->fechaApertura)) {
            $cbs->setFechaApertura($p->fechaApertura);
        } 
        if (isset($p->fechaCancelacion)) {
            $cbs->setFechaCancelacion($p->fechaCancelacion);
        } 
        if (isset($p->anio)) {
            $cbs->setAnio($p->anio);
        } 
        if (isset($p->sucursal)) {
            $cbs->setSucursal($p->sucursal);
        } 
        if (isset($p->nombre)) {
            $cbs->setNombre($p->nombre);
        } 
        if (isset($p->alias)) {
            $cbs->setAlias($p->alias);
        } 
        if (isset($p->cliente)) {
            $cbs->setCliente($p->cliente);
        } 
        if (isset($p->contrato)) {
            $cbs->setContrato($p->contrato);
        } 
        if (isset($p->cuentaInversion)) {
            $cbs->setCuentaInversion($p->cuentaInversion);
        } 
        if (isset($p->inversion)) {
            $cbs->setInversion($p->inversion);
        } 
        if (isset($p->recaudadora)) {
            $cbs->setRecaudadora($p->recaudadora);
        } 
        if (isset($p->concentradora)) {
            $cbs->setConcentradora($p->concentradora);
        } 
        if (isset($p->pagadora)) {
            $cbs->setPagadora($p->pagadora);
        } 
        if (isset($p->receptora)) {
            $cbs->setReceptora($p->receptora);
        } 
        if (isset($p->observacion)){
            $cbs->setObservacion($p->observacion);
            $bit_observacion .= ' observacion:'.$p->observacion;
        } 
        if (isset($p->iba_id)) {
            $cbs->setIba_id($p->iba_id);
            $bit_observacion .= ' iba:'.$p->iba_id;
        } 
        if (isset($p->ter_id)) {
            $cbs->setTer_id($p->ter_id);
            $bit_observacion .= ' ter:'.$p->ter_id;
        }
        if (isset($p->pbn_id)) {
            $cbs->setPbn_id($p->pbn_id);
            $bit_observacion .= ' pbn:'.$p->pbn_id;
        }
        if (isset($p->eba_id)) {
            $cbs->setEba_id($p->eba_id);
            $bit_observacion .= ' eba:'.$p->eba_id;
        } 
        if (isset($p->tfn_id)) {
            $cbs->setTfn_id($p->tfn_id);
            $bit_observacion .= ' tfn:'.$p->tfn_id;
        }
        if (isset($p->usr_id)) {
            $cbs->setUsr_id($p->usr_id);
        }
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? 
            $_SESSION[_S]['ses_ip']:
            '127.0.0.1';
        $bit_usr_id =  $_SESSION[_S]['usr_id'];
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a": 
                $cbs->conectar();
                $bit_observacion = 'REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $cbs->setSql(' SET @usr_ip = INET_ATON(?), @usr_id = ?, @bit_observacion = ?;');
                $cbs->consultar($params);
                $cbs->insert(false); 
                return $cbs->getUltimoId(); 
                break;
            case "b": 
                $cbs->delete(); 
                break;
            case "c":
                $cbs->conectar();
                $bit_observacion = 'REGISTRO ACTUALIZADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $cbs->setSql(' SET @usr_ip = INET_ATON(?), @usr_id = ?, @bit_observacion = ?;');
                $cbs->consultar($params);
                $cbs->update(false);
                break;
        }
    }
}
