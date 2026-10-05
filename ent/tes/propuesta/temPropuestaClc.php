<?php
require_once dirname(__FILE__) . "/../../Entidad.php";

class TemporalPropuestaClc extends Entidad
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $pro_id;
    protected $pro_ejercicio; //pro_Propuesta - Llave foranea - Identificador de la Propuesta
    protected $gpo_id; //cat_Grupo - Llave foranea - Identificador de Grupo de la Clasificación Administrativa
    protected $ur_id; //cat_UnidadResponsable - Llave foranea - Identificador de la Unidad Responsable de la Clasificación Administrativa
    protected $clc_id; //scp_Clc - Llave foranea - Identificador de la CLC
    protected $pro_importeClc; //Importe de la CLC
    protected $bur_id; //cat_BancosUr - Llave foranea - Identificador de la Cuenta Bancaria donde se depositara el importe de la CLC
    protected $pro_lineaCaptura; //
    protected $usr_id; //Identificador de usuario que captura el registro
    /******************************************************
    METODOS GET
     ******************************************************/
    public function getPro_id()
    {
        return $this->pro_id->getVal();
    }
    public function getEjercicio()
    {
        return $this->pro_ejercicio->getVal();
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
    public function getImporteClc()
    {
        return $this->pro_importeClc->getVal();
    }
    public function getBur_id()
    {
        return $this->bur_id->getVal();
    }
    public function getLineaCaptura()
    {
        return $this->pro_lineaCaptura->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    /******************************************************
    METODOS SET
     ******************************************************/
    public function setPro_id($val)
    {
        $this->pro_id->setVal($val);
    }
    public function setEjercicio($val)
    {
        $this->pro_ejercicio->setVal($val);
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
    public function setImporteClc($val)
    {
        $this->pro_importeClc->setVal($val);
    }
    public function setBur_id($val)
    {
        $this->bur_id->setVal($val);
    }
    public function setLineaCaptura($val)
    {
        $this->pro_lineaCaptura->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/
    public function staPro_id($val)
    {
        $this->pro_id->staVal($val);
    }
    public function staEjercicio($val)
    {
        $this->pro_ejercicio->staVal($val);
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
    public function staImporteClc($val)
    {
        $this->pro_importeClc->staVal($val);
    }
    public function staBur_id($val)
    {
        $this->bur_id->staVal($val);
    }
    public function staLineaCaptura($val)
    {
        $this->pro_lineaCaptura->staVal($val);
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
        parent::__construct(array("tabla" => BD_TESORERITEM . ".tem_PropuestaClc"));
        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->pro_id = new Atributo("numero", "NO", "", "pro_id");
        $this->pro_ejercicio = new Atributo("numero", "NO", "PRI", "pro_ejercicio");
        $this->gpo_id = new Atributo("numero", "NO", "PRI", "gpo_id");
        $this->ur_id = new Atributo("numero", "NO", "PRI", "ur_id");
        $this->clc_id = new Atributo("numero", "NO", "PRI", "clc_id");
        $this->pro_importeClc = new Atributo("numero", "NO", "", "pro_importeClc");
        $this->bur_id = new Atributo("numero", "NO", "MUL", "bur_id");
        $this->pro_lineaCaptura = new Atributo("cadena", "YES", "MUL", "pro_lineaCaptura");

        $this->usr_id = new Atributo("numero", "NO", "MUL", "usr_id");
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function abc($p)
    {
        $pro = new TemporalPropuestaClc();
        if (isset($p->pro_id)) {
            $pro->setPro_id($p->pro_id);
        }
        if (isset($p->ejercicio)) {
            $pro->setEjercicio($p->ejercicio);
        }
        if (isset($p->gpo_id)) {
            $pro->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $pro->setUr_id($p->ur_id);
        }
        if (isset($p->clc_id)) {
            $pro->setClc_id($p->clc_id);
        }
        if (isset($p->importeClc)) {
            $pro->setImporteClc($p->importeClc);
        }
        if (isset($p->bur_id)) {
            $pro->setBur_id($p->bur_id);
        }
        if (isset($p->lineaCaptura)) {
            $pro->setLineaCaptura($p->lineaCaptura);
        } else {
            $pro->setLineaCaptura('0');
        }

        if (isset($p->usr_id)) {
            $pro->setUsr_id($p->usr_id);
        }
        switch ($p->abc) {
            case "a":
                $pro->insert();
                return $pro->getUltimoId();
                break;
            case "b":
                $pro->delete();
                break;
            case "c":
                $pro->update();
                break;
        }
    }
}
