<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class ProductoFinanciero extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $cbs_id; //ban_CuentaBancariaSefin - Llave foranea - Identificador de la Cuenta Bancaria de SeFin
    protected $pfi_ejercicio; //Identificador del Año del Producto Financiero
    protected $msc_id; //
    protected $pfi_dia; //Identificador del Dia del Producto Financiero
    protected $pfi_id; //
    protected $pfi_capital; //
    protected $pfi_productoFinanciero; //Producto Financiero de la cuenta bancaria
    protected $pfi_egreso; //Egreso de la cuenta bancaria
    protected $pfi_observacion; //
    protected $usr_id; //Identificador de usuario que captura/actualiza el registro

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getCbs_id()
    {return $this->cbs_id->getVal();}
    public function getEjercicio()
    {return $this->pfi_ejercicio->getVal();}
    public function getMsc_id()
    {return $this->msc_id->getVal();}
    public function getDia()
    {return $this->pfi_dia->getVal();}
    public function getPfi_id()
    {return $this->pfi_id->getVal();}
    public function getCapital()
    {return $this->pfi_capital->getVal();}
    public function getProductoFinanciero()
    {return $this->pfi_productoFinanciero->getVal();}
    public function getEgreso()
    {return $this->pfi_egreso->getVal();}
    public function getObservacion()
    {return $this->pfi_observacion->getVal();}
    public function getUsr_id()
    {return $this->usr_id->getVal();}

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setCbs_id($val)
    {$this->cbs_id->setVal($val);}
    public function setEjercicio($val)
    {$this->pfi_ejercicio->setVal($val);}
    public function setMsc_id($val)
    {$this->msc_id->setVal($val);}
    public function setDia($val)
    {$this->pfi_dia->setVal($val);}
    public function setPfi_id($val)
    {$this->pfi_id->setVal($val);}
    public function setCapital($val)
    {$this->pfi_capital->setVal($val);}
    public function setProductoFinanciero($val)
    {$this->pfi_productoFinanciero->setVal($val);}
    public function setEgreso($val)
    {$this->pfi_egreso->setVal($val);}
    public function setObservacion($val)
    {$this->pfi_observacion->setVal($val);}
    public function setUsr_id($val)
    {$this->usr_id->setVal($val);}

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staCbs_id($val)
    {$this->cbs_id->staVal($val);}
    public function staEjercicio($val)
    {$this->pfi_ejercicio->staVal($val);}
    public function staMsc_id($val)
    {$this->msc_id->staVal($val);}
    public function staDia($val)
    {$this->pfi_dia->staVal($val);}
    public function staPfi_id($val)
    {$this->pfi_id->staVal($val);}
    public function staCapital($val)
    {$this->pfi_capital->staVal($val);}
    public function staProductoFinanciero($val)
    {$this->pfi_productoFinanciero->staVal($val);}
    public function staEgreso($val)
    {$this->pfi_egreso->staVal($val);}
    public function staObservacion($val)
    {$this->pfi_observacion->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(array("tabla" => BD_TESORERIA.".ban_ProductoFinanciero"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->cbs_id = new Atributo("numero", "NO", "PRI", "cbs_id");
        $this->pfi_ejercicio = new Atributo("numero", "NO", "PRI", "pfi_ejercicio");
        $this->msc_id = new Atributo("numero", "NO", "PRI", "msc_id");
        $this->pfi_dia = new Atributo("numero", "NO", "PRI", "pfi_dia");
        $this->pfi_id = new Atributo("numero", "NO", "PRI", "pfi_id");
        $this->pfi_capital = new Atributo("numero", "NO", "", "pfi_capital");
        $this->pfi_productoFinanciero = new Atributo("numero", "NO", "", "pfi_productoFinanciero");
        $this->pfi_egreso = new Atributo("numero", "NO", "", "pfi_egreso");
        $this->pfi_observacion = new Atributo("cadena", "NO", "", "pfi_observacion");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $pfi = new ProductoFinanciero();
        $bit_observacion = " ";
        if (isset($p->cbs_id)) {
            $pfi->setCbs_id($p->cbs_id);
            $bit_observacion .= " cbs_id:" . $p->cbs_id;
        }
        if (isset($p->ejercicio)) {
            $pfi->setEjercicio($p->ejercicio);
            $bit_observacion .= " ejercicio:" . $p->ejercicio;
        }
        if (isset($p->msc_id)) {
            $pfi->setMsc_id($p->msc_id);
            $bit_observacion .= " msc_id:" . $p->msc_id;
        }
        if (isset($p->dia)) {
            $pfi->setDia($p->dia);
            $bit_observacion .= " dia:" . $p->dia;
        }
        if (isset($p->pfi_id)) {
            $pfi->setPfi_id($p->pfi_id);
            $bit_observacion .= " pfi_id:" . $p->pfi_id;
        }
        if (isset($p->capital)) {
            $pfi->setCapital($p->capital);
            $bit_observacion .= " capital:" . $p->capital;
        }
        if (isset($p->productoFinanciero)) {
            $pfi->setProductoFinanciero($p->productoFinanciero);
            $bit_observacion .= " productoFinanciero:" . $p->productoFinanciero;
        }
        if (isset($p->egreso)) {
            $pfi->setEgreso($p->egreso);
            $bit_observacion .= " egreso:" . $p->egreso;
        }
        if (isset($p->observacion)) {
            $pfi->setObservacion($p->observacion);
            $bit_observacion .= " observacion:" . $p->observacion;
        }
        if (isset($p->usr_id)) {
            $pfi->setUsr_id($p->usr_id);
            $bit_observacion .= " usr_id:" . $p->usr_id;
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a":
                $pfi->conectar();
                $bit_observacion = 'REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $pfi->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $pfi->consultar($params);
                $pfi->insert(false);
                return $pfi->getUltimoId();
                break;
            case "b":
                $pfi->conectar();
                $bit_observacion = 'REGISTRO BORRADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $pfi->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $pfi->consultar($params);
                $pfi->delete(false);
                break;
            case "c":
                $pfi->conectar();
                $bit_observacion = 'REGISTRO ACTUALIZADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $pfi->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $pfi->consultar($params);
                $pfi->update(false);
                break;
        }
    }

}
