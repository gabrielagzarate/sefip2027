<?php

require_once dirname(__FILE__)."../../../Entidad.php";

class EstadoVinculacion extends Entidad
{

    /****************************************************** 

        ATRIBUTOS DE LA CLASE

    ******************************************************/ 

    protected $evi_id;    //Clave identificadora del estado de la vinculacion
    protected $nombre;    //Nombre del estado de la vinculacion
    protected $descripcion;    //Descripcion del estado de la vinculacion
    protected $color;    //Codigo del color del estado
    protected $usr_id;    //Clave identificadora del usuario que inserto el registro

    /****************************************************** 

        METODOS GET

    ******************************************************/ 

    public function getEvi_id() 
    { 
        return $this->evi_id->getVal(); 
    }
    public function getNombre() 
    { 
        return $this->nombre->getVal(); 
    }
    public function getDescripcion() 
    { 
        return $this->descripcion->getVal(); 
    }
    public function getColor() 
    { 
        return $this->color->getVal(); 
    }
    public function getUsr_id() 
    { 
        return $this->usr_id->getVal(); 
    }

    /****************************************************** 

        METODOS SET

    ******************************************************/ 

    public function setEvi_id($val) 
    { 
        $this->evi_id->setVal($val); 
    }
    public function setNombre($val) 
    { 
        $this->nombre->setVal($val); 
    }
    public function setDescripcion($val) 
    { 
        $this->descripcion->setVal($val); 
    }
    public function setColor($val) 
    { 
        $this->color->setVal($val); 
    }
    public function setUsr_id($val) 
    { 
        $this->usr_id->setVal($val); 
    }

    /****************************************************** 

        METODOS STA (SET sin persistencia)

    ******************************************************/ 

    public function staEvi_id($val) 
    { 
        $this->evi_id->staVal($val); 
    }
    public function staNombre($val) 
    { 
        $this->nombre->staVal($val); 
    }
    public function staDescripcion($val)
    {
        $this->descripcion->staVal($val);
    }
    public function staColor($val) 
    {
        $this->color->staVal($val); 
    }
    public function staUsr_id($val) 
    { 
        $this->usr_id->staVal($val); 
    }

    /****************************************************** 

        CONSTRUCTOR

    ******************************************************/ 

    public function  __construct()
    {

        parent::__construct(array("tabla"=>"cat_EstadoVinculacion"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->evi_id = new Atributo("numero","NO","PRI","evi_id");
        $this->nombre = new Atributo("cadena","NO","","evi_nombre");
        $this->descripcion = new Atributo("cadena","NO","","evi_descripcion");
        $this->color = new Atributo("cadena","NO","","evi_color");
        $this->usr_id = new Atributo("numero","NO","","usr_id");
        
        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object)func_get_arg(0) : null) : null);
        
        $evi_id = (!isset($prm->evi_id)) ? '' : $prm->evi_id;
        if ($evi_id != '') {
            $this->setEvi_id($evi_id);
            $this->select();
        }

    }


    /****************************************************** 

        FUNCIONES ESTÁTICAS

    ******************************************************/ 

    public static function abc($p)
    {
        $evi =  new EstadoVinculacion();
        if (isset($p->evi_id)) {
            $evi->setEvi_id($p->evi_id);
        }
        if (isset($p->nombre)) {
            $evi->setNombre($p->nombre);
        }
        if (isset($p->color)) {
            $evi->setColor($p->color);
        }
        if (isset($p->usr_id)) {
            $evi->setUsr_id($p->usr_id);
        }
        switch ($p->abc) {
            case "a": 
                $evi->insert(); 
                return $evi->getUltimoId(); 
                break;
            case "b": 
                $evi->delete(); 
                break;
            case "c": 
                $evi->update(); 
                break;
        }
    }

}

?>