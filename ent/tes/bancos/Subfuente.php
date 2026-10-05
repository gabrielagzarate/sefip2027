<?php
require_once dirname(__FILE__) . "/../../Entidad.php";
class Subfuente extends Entidad
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $cff_id; //
    protected $fte_id; //
    protected $sft_id; //
    protected $sft_ejercicio; //AÑo del registro
    protected $sft_nombre; //Nombre de la Subfuente
    protected $sft_descripcion; //Descripcion de la Subfuente
    protected $sft_clave; //
    protected $usr_id; //Identificador del usuario que hace el registro.

    /******************************************************
    METODOS GET
     ******************************************************/

    public function getCff_id()
    {return $this->cff_id->getVal();}
    public function getFte_id()
    {return $this->fte_id->getVal();}
    public function getSft_id()
    {return $this->sft_id->getVal();}
    public function getEjercicio()
    {return $this->sft_ejercicio->getVal();}
    public function getNombre()
    {return $this->sft_nombre->getVal();}
    public function getDescripcion()
    {return $this->sft_descripcion->getVal();}
    public function getClave()
    {return $this->sft_clave->getVal();}
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
    public function setEjercicio($val)
    {$this->sft_ejercicio->setVal($val);}
    public function setNombre($val)
    {$this->sft_nombre->setVal($val);}
    public function setDescripcion($val)
    {$this->sft_descripcion->setVal($val);}
    public function setClave($val)
    {$this->sft_clave->setVal($val);}
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
    public function staEjercicio($val)
    {$this->sft_ejercicio->staVal($val);}
    public function staNombre($val)
    {$this->sft_nombre->staVal($val);}
    public function staDescripcion($val)
    {$this->sft_descripcion->staVal($val);}
    public function staClave($val)
    {$this->sft_clave->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}

    /******************************************************
    CONSTRUCTOR
     ******************************************************/

    public function __construct()
    {
        parent::__construct(array("tabla" => BD_SINPRES . ".cat_Subfuente" . APP_EJERCICIO));
        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->cff_id = new Atributo("cadena", "NO", "PRI", "cff_id");
        $this->fte_id = new Atributo("cadena", "NO", "PRI", "fte_id");
        $this->sft_id = new Atributo("cadena", "NO", "PRI", "sft_id");
        $this->sft_ejercicio = new Atributo("numero", "NO", "", "sft_ejercicio");
        $this->sft_nombre = new Atributo("cadena", "NO", "", "sft_nombre");
        $this->sft_descripcion = new Atributo("cadena", "NO", "", "sft_descripcion");
        $this->sft_clave = new Atributo("cadena", "NO", "", "sft_clave");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/

    public static function abc($p)
    {
        $sft = new Subfuente();
        $bit_observacion = '';
        if (isset($p->cff_id)) {
            $sft->setCff_id($p->cff_id);
        }
        if (isset($p->fte_id)) {
            $sft->setFte_id($p->fte_id);
        }
        if (isset($p->sft_id)) {
            $sft->setSft_id($p->sft_id);
        }
        if (isset($p->nombre)) {
            $sft->setNombre($p->nombre);
            $bit_observacion = "Nombre: " . $p->nombre;
        }
        if (isset($p->descripcion)) {
            $sft->setDescripcion($p->descripcion);
        }
        if (isset($p->ejercicio)) {
            $sft->setEjercicio($p->ejercicio);
            $bit_observacion = "Ejercicio: " . $p->ejercicio;
        }
        if (isset($p->clave)) {
            $sft->setClave($p->clave);
        }
        if (isset($p->usr_id)) {
            $sft->setUsr_id($p->usr_id);
            $bit_observacion .= ' usr_id: ' . $p->usr_id;
        }

        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a":
                $sft->conectar();
                $bit_observacion = 'REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $sft->setSql(' SET @usr_ip =INET_ATON(?), @usr_id = ? , @bit_observacion = ?;');
                $sft->consultar($params);
                $sft->insert(false);
                return $sft->getUltimoId();
                break;
            case "b":
                $sft->delete();
                break;
            case "c":
                $sft->update();
                break;
        }
    }
}
