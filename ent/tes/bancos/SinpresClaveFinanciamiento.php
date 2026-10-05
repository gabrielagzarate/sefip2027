<?php
require_once dirname(__FILE__) . "/../../Entidad.php";

class SinpresClaveFinanciamiento extends Entidad
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $cff_id; //
    protected $fte_id; //
    protected $sft_id; //
    protected $ori_id; //
    protected $sor_id; //
    protected $cfi_id; //
    protected $cfi_anio; //
    protected $cfi_clave; //
    protected $cfi_nombre; //
    protected $cfi_ejercicio; //
    protected $cfi_descripcion; //
    protected $cfi_abreviatura; //
    protected $trp_id; //
    protected $trp_id_distribucion; //
    protected $cfi_ejercicio2; //Controla por año de los  registros insertados
    protected $usr_id; //
    /******************************************************
    METODOS GET
     ******************************************************/
    public function getCff_id()
    {return $this->cff_id->getVal();}
    public function getFte_id()
    {return $this->fte_id->getVal();}
    public function getSft_id()
    {return $this->sft_id->getVal();}
    public function getOri_id()
    {return $this->ori_id->getVal();}
    public function getSor_id()
    {return $this->sor_id->getVal();}
    public function getCfi_id()
    {return $this->cfi_id->getVal();}
    public function getAnio()
    {return $this->cfi_anio->getVal();}
    public function getClave()
    {return $this->cfi_clave->getVal();}
    public function getNombre()
    {return $this->cfi_nombre->getVal();}
    public function getEjercicio()
    {return $this->cfi_ejercicio->getVal();}
    public function getDescripcion()
    {return $this->cfi_descripcion->getVal();}
    public function getAbreviatura()
    {return $this->cfi_abreviatura->getVal();}
    public function getTrp_id()
    {return $this->trp_id->getVal();}
    public function getTrp_id_distribucion()
    {return $this->trp_id_distribucion->getVal();}
    public function getEjercicio2()
    {return $this->cfi_ejercicio2->getVal();}
    public function getUsr_id()
    {return $this->usr_id->getVal();}
    /******************************************************
    METODOS SET
     ******************************************************/
    public function setCff_id($val)
    {$this->cff_id->setVal($val);}
    public function setFte_id($val)
    {$this->fte_id->setVal($val);}
    public function setSft_id($val)
    {$this->sft_id->setVal($val);}
    public function setOri_id($val)
    {$this->ori_id->setVal($val);}
    public function setSor_id($val)
    {$this->sor_id->setVal($val);}
    public function setCfi_id($val)
    {$this->cfi_id->setVal($val);}
    public function setAnio($val)
    {$this->cfi_anio->setVal($val);}
    public function setClave($val)
    {$this->cfi_clave->setVal($val);}
    public function setNombre($val)
    {$this->cfi_nombre->setVal($val);}
    public function setEjercicio($val)
    {$this->cfi_ejercicio->setVal($val);}
    public function setDescripcion($val)
    {$this->cfi_descripcion->setVal($val);}
    public function setAbreviatura($val)
    {$this->cfi_abreviatura->setVal($val);}
    public function setTrp_id($val)
    {$this->trp_id->setVal($val);}
    public function setTrp_id_distribucion($val)
    {$this->trp_id_distribucion->setVal($val);}
    public function setEjercicio2($val)
    {$this->cfi_ejercicio2->setVal($val);}
    public function setUsr_id($val)
    {$this->usr_id->setVal($val);}
    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/
    public function staCff_id($val)
    {$this->cff_id->staVal($val);}
    public function staFte_id($val)
    {$this->fte_id->staVal($val);}
    public function staSft_id($val)
    {$this->sft_id->staVal($val);}
    public function staOri_id($val)
    {$this->ori_id->staVal($val);}
    public function staSor_id($val)
    {$this->sor_id->staVal($val);}
    public function staCfi_id($val)
    {$this->cfi_id->staVal($val);}
    public function staAnio($val)
    {$this->cfi_anio->staVal($val);}
    public function staClave($val)
    {$this->cfi_clave->staVal($val);}
    public function staNombre($val)
    {$this->cfi_nombre->staVal($val);}
    public function staEjercicio($val)
    {$this->cfi_ejercicio->staVal($val);}
    public function staDescripcion($val)
    {$this->cfi_descripcion->staVal($val);}
    public function staAbreviatura($val)
    {$this->cfi_abreviatura->staVal($val);}
    public function staTrp_id($val)
    {$this->trp_id->staVal($val);}
    public function staTrp_id_distribucion($val)
    {$this->trp_id_distribucion->staVal($val);}
    public function staEjercicio2($val)
    {$this->cfi_ejercicio2->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}

    /******************************************************
    CONSTRUCTOR
     ******************************************************/

    public function __construct()
    {
        parent::__construct(array("tabla" => BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO));
        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->cff_id = new Atributo("cadena", "NO", "PRI", "cff_id");
        $this->fte_id = new Atributo("cadena", "NO", "PRI", "fte_id");
        $this->sft_id = new Atributo("cadena", "NO", "PRI", "sft_id");
        $this->ori_id = new Atributo("cadena", "NO", "PRI", "ori_id");
        $this->sor_id = new Atributo("cadena", "NO", "PRI", "sor_id");
        $this->cfi_id = new Atributo("numero", "NO", "PRI", "cfi_id");
        $this->cfi_anio = new Atributo("numero", "NO", "PRI", "cfi_anio");
        $this->cfi_clave = new Atributo("cadena", "NO", "", "cfi_clave");
        $this->cfi_nombre = new Atributo("cadena", "NO", "", "cfi_nombre");
        $this->cfi_ejercicio = new Atributo("numero", "NO", "", "cfi_ejercicio");
        $this->cfi_descripcion = new Atributo("cadena", "NO", "", "cfi_descripcion");
        $this->cfi_abreviatura = new Atributo("cadena", "NO", "", "cfi_abreviatura");
        $this->trp_id = new Atributo("cadena", "YES", "MUL", "trp_id");
        $this->trp_id_distribucion = new Atributo("cadena", "YES", "MUL", "trp_id_distribucion");
        $this->cfi_ejercicio2 = new Atributo("numero", "NO", "", "cfi_ejercicio2");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/

    public static function abc($p)
    {
        $cfi = new SinpresClaveFinanciamiento();
        $bit_observacion = '';
        if (isset($p->cff_id)) {
            $cfi->setCff_id($p->cff_id);
        }
        if (isset($p->fte_id)) {
            $cfi->setFte_id($p->fte_id);
        }
        if (isset($p->sft_id)) {
            $cfi->setSft_id($p->sft_id);
        }
        if (isset($p->ori_id)) {
            $cfi->setOri_id($p->ori_id);
        }
        if (isset($p->sor_id)) {
            $cfi->setSor_id($p->sor_id);
        }
        if (isset($p->cfi_id)) {
            $cfi->setCfi_id($p->cfi_id);
        }
        if (isset($p->anio)) {
            $cfi->setAnio($p->anio);
        }
        if (isset($p->clave)) {
            $cfi->setClave($p->clave);
            $bit_observacion = "CLAVE: " . $p->clave;
        }
        if (isset($p->nombre)) {
            $cfi->setNombre($p->nombre);
            $bit_observacion = "NOMBRE: " . $p->nombre;
        }
        if (isset($p->ejercicio)) {
            $cfi->setEjercicio($p->ejercicio);
            $bit_observacion = "EJERCICIO: " . $p->ejercicio;
        }
        if (isset($p->descripcion)) {
            $cfi->setDescripcion($p->descripcion);
        }
        if (isset($p->abreviatura)) {
            $cfi->setAbreviatura($p->abreviatura);
        }
        if (isset($p->trp_id)) {
            $cfi->setTrp_id($p->trp_id);
        }
        if (isset($p->trp_id_distribucion)) {
            $cfi->setTrp_id_distribucion($p->trp_id_distribucion);
        }
        if (isset($p->ejercicio2)) {
            $cfi->setEjercicio2($p->ejercicio2);
            $bit_observacion = "EJERCICIO2: " . $p->ejercicio2;
        }
        if (isset($p->usr_id)) {
            $cfi->setUsr_id($p->usr_id);
            $bit_observacion .= ' usr_id: ' . $p->usr_id;
        }

        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a":
                $cfi->conectar();
                $bit_observacion = 'REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $cfi->setSql(' SET @usr_ip = INET_ATON(?), @usr_id=? , @bit_observacion = ?;');
                $cfi->consultar($params);
                $cfi->insert(false);
                return $cfi->getUltimoId();
                break;
            case "b":
                $cfi->conectar();
                $bit_observacion = 'ELIMINAR REGISTRO CLAVE-FINANCIAMIENTO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $cfi->setSql(' SET @usr_ip = INET_ATON(?), @usr_id=? , @bit_observacion = ?;');
                $cfi->consultar($params);
                $cfi->delete(false);
                break;
            case "c":
                $cfi->conectar();
                $bit_observacion = 'ACTUALIZAR REGISTRO CLAVE-FINANCIAMIENTO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $cfi->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion = ?;');
                $cfi->consultar($params);
                $cfi->update(false);
                break;
        }
    }
}
