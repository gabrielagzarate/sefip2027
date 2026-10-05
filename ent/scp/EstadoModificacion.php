<?php

require_once dirname(__FILE__) . '/../Entidad.php';

class EstadoModificacion extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $emo_id; //Clave identificadora del estado del folio de modificacion presupuestal
    protected $emo_nombre; //Descripcion del estado del folio de modificacion presupuestal
    protected $emo_color; //Codigo del color del estado
    protected $usr_id; //Clave identificadora del usuario que inserto el registro

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getEmo_id()
    {
        return $this->emo_id->getVal();
    }
    public function getNombre()
    {
        return $this->emo_nombre->getVal();
    }
    public function getColor()
    {
        return $this->emo_color->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setEmo_id($val)
    {
        $this->emo_id->setVal($val);
    }
    public function setNombre($val)
    {
        $this->emo_nombre->setVal($val);
    }
    public function setColor($val)
    {
        $this->emo_color->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staEmo_id($val)
    {
        $this->emo_id->staVal($val);
    }
    public function staNombre($val)
    {
        $this->emo_nombre->staVal($val);
    }
    public function staColor($val)
    {
        $this->emo_color->staVal($val);
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

        parent::__construct(['tabla' => 'cat_EstadoModificacion']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->emo_id = new Atributo('numero', 'NO', 'PRI', 'emo_id');
        $this->emo_nombre = new Atributo('cadena', 'NO', '', 'emo_nombre');
        $this->emo_color = new Atributo('cadena', 'NO', '', 'emo_color');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) :
            (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        $emo_id = (!isset($prm->emo_id)) ? '' : $prm->emo_id;
        if ($emo_id != '') {
            $this->setEmo_id($emo_id);
            $this->select();
        }

    }

    /******************************************************

    FUNCIONES EST�TICAS

     ******************************************************/

    public static function abc($p)
    {
        $emo = new EstadoModificacion();
        if (isset($p->emo_id)) {
            $emo->setEmo_id($p->emo_id);
        }
        if (isset($p->nombre)) {
            $emo->setNombre($p->nombre);
        }
        if (isset($p->color)) {
            $emo->setColor($p->color);
        }
        if (isset($p->usr_id)) {
            $emo->setUsr_id($p->usr_id);
        }
        switch ($p->abc) {
            case 'a':
                $emo->insert();
                return $emo->getUltimoId();
                break;
            case 'b':
                $emo->delete();
                break;
            case 'c':
                $emo->update();
                break;
        }
    }

}
