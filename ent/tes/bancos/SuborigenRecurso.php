<?php
require_once dirname(__FILE__) . "/../../Entidad.php";
class SuborigenRecurso extends Entidad
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $cff_id; //
    protected $fte_id; //
    protected $sft_id; //
    protected $ori_id; //
    protected $sor_id; //Identificador del SubOrigen
    protected $sor_nombre; //Nombre del SubOrigen
    protected $sor_descripcion; //Descripcion del SubOrigen del recurso
    protected $sor_ejercicio; //AÑo del registro
    protected $ter_id;
    protected $sor_clave; //
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
    public function getOri_id()
    {return $this->ori_id->getVal();}
    public function getSor_id()
    {return $this->sor_id->getVal();}
    public function getNombre()
    {return $this->sor_nombre->getVal();}
    public function getDescripcion()
    {return $this->sor_descripcion->getVal();}
    public function getEjercicio()
    {return $this->sor_ejercicio->getVal();}
    public function getRecurso()
    {return $this->ter_id->getVal();}
    public function getClave()
    {return $this->sor_clave->getVal();}
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
    public function setNombre($val)
    {$this->sor_nombre->setVal($val);}
    public function setDescripcion($val)
    {$this->sor_descripcion->setVal($val);}
    public function setEjercicio($val)
    {$this->sor_ejercicio->setVal($val);}
    public function setRecurso($val)
    {$this->ter_id->setVal($val);}
    public function setClave($val)
    {$this->sor_clave->setVal($val);}
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
    public function staNombre($val)
    {$this->sor_nombre->staVal($val);}
    public function staDescripcion($val)
    {$this->sor_descripcion->staVal($val);}
    public function staEjercicio($val)
    {$this->sor_ejercicio->staVal($val);}
    public function staRecurso($val)
    {$this->ter_id->staVal($val);}
    public function staClave($val)
    {$this->sor_clave->staVal($val);}
    public function staUsr_id($val)
    {$this->usr_id->staVal($val);}

    /******************************************************
    CONSTRUCTOR
     ******************************************************/

    public function __construct()
    {
        parent::__construct(array("tabla" => BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO));
        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->cff_id = new Atributo("cadena", "NO", "PRI", "cff_id");
        $this->fte_id = new Atributo("cadena", "NO", "PRI", "fte_id");
        $this->sft_id = new Atributo("cadena", "NO", "PRI", "sft_id");
        $this->ori_id = new Atributo("cadena", "NO", "PRI", "ori_id");
        $this->sor_id = new Atributo("cadena", "NO", "PRI", "sor_id");
        $this->sor_nombre = new Atributo("cadena", "NO", "", "sor_nombre");
        $this->sor_descripcion = new Atributo("cadena", "NO", "", "sor_descripcion");
        $this->sor_ejercicio = new Atributo("numero", "NO", "", "sor_ejercicio");
        $this->ter_id = new Atributo("cadena", "NO", "MUL", "ter_id");
        $this->sor_clave = new Atributo("cadena", "NO", "", "sor_clave");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");

    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/

    public static function abc($p)
    {
        $sor = new SuborigenRecurso();
        $bit_observacion = '';
        if (isset($p->cff_id)) {
            $sor->setCff_id($p->cff_id);
        }
        if (isset($p->fte_id)) {
            $sor->setFte_id($p->fte_id);
        }
        if (isset($p->sft_id)) {
            $sor->setSft_id($p->sft_id);
        }
        if (isset($p->ori_id)) {
            $sor->setOri_id($p->ori_id);
        }
        if (isset($p->sor_id)) {
            $sor->setSor_id($p->sor_id);
        }
        if (isset($p->nombre)) {
            $sor->setNombre($p->nombre);
            $bit_observacion .= "Nombre: " . $p->nombre;
        }
        if (isset($p->descripcion)) {
            $sor->setDescripcion($p->descripcion);
        }
        if (isset($p->ejercicio)) {
            $sor->setEjercicio($p->ejercicio);
            $bit_observacion .= "Ejercicio: " . $p->ejercicio;
        }
        if (isset($p->recurso)) {
            $sor->setRecurso($p->recurso);
            $bit_observacion .= "Recurso: " . $p->recurso;
        }
        if (isset($p->clave)) {
            $sor->setClave($p->clave);
            $bit_observacion .= "Clave: " . $p->clave;
        }
        if (isset($p->usr_id)) {
            $sor->setUsr_id($p->usr_id);
            $bit_observacion .= ' usr_id: ' . $p->usr_id;
        }

        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case 'a':
                $sor->conectar();
                $bit_observacion = 'REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $sor->setSql(' SET @usr_ip = INET_ATON(?), @usr_id = ? , @bit_observacion = ?;');
                $sor->consultar($params);
                $sor->insert(false);
                return $sor->getUltimoId();
                break;
            case 'b':
                $sor->delete();
                break;
            case 'c':
                $sor->update();
                break;
        }
    }
}
