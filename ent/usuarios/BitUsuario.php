<?php

require_once dirname(__FILE__) . "/../../lib/constantes.php";
require_once dirname(__FILE__) . "/../Entidad.php";

class BitUsuario extends Entidad
{
    /*     * ****************************************************

      ATRIBUTOS DE LA CLASE

     * **************************************************** */

    protected $usu; //
    protected $usr_id; //Usuario Modificado
    protected $bit_observacion; //Accion realizada

    /*     * ****************************************************

      METODOS GET

     * **************************************************** */

    public function getUsu()
    {
        return $this->usu->getVal();
    }

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    public function getObservacion()
    {
        return $this->bit_observacion->getVal();
    }

    /*     * ****************************************************

      METODOS SET

     * **************************************************** */

    public function setUsu($val)
    {
        $this->usu->setVal($val);
    }

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    public function setObservacion($val)
    {
        $this->bit_observacion->setVal($val);
    }

    /*     * ****************************************************

      METODOS STA (SET sin persistencia)

     * **************************************************** */

    public function staUsu($val)
    {
        $this->usu->staVal($val);
    }

    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }

    public function staObservacion($val)
    {
        $this->bit_observacion->staVal($val);
    }

    /*     * ****************************************************

      CONSTRUCTOR

     * **************************************************** */

    public function __construct()
    {

        parent::__construct(array("tabla" => BD_BITACORA . ".bit_Usuario"));

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->usu = new Atributo("numero", "NO", "PRI", "bit_usu");
        $this->usr_id = new Atributo("numero", "NO", "", "usr_id");
        $this->bit_observacion = new Atributo("cadena", "NO", "", "bit_observacion");
    }

    /*     * ****************************************************

      FUNCIONES ESTÁTICAS

     * **************************************************** */

    public static function abc($p)
    {
        $bit = new BitUsuario();
        if (isset($p->usu))
            $bit->setUsu($p->usu);
        if (isset($p->usr_id))
            $bit->setUsr_id($p->usr_id);
        if (isset($p->bit_observacion))
            $bit->setObservacion($p->bit_observacion);
        switch ($p->abc) {
            case "a":
                $bit->insert();
                return
                $bit->getUltimoId();
                break;
            case "b":
                $bit->delete();
                break;
            case "c":
                $bit->update();
                break;
        }
    }

}

?>