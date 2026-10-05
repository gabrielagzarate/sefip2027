<?php

require_once dirname(__FILE__) . '/Entidad.php';

class VinUnidadResponsable extends Entidad
{
    /******************************************************

        ATRIBUTOS DE LA CLASE

    ******************************************************/

    protected $vur_id;
    protected $uur_id;
    protected $gpo_id;
    protected $ur_id;
    protected $vur_ejercicio;
    protected $vur_nombre;
    protected $vur_urClave;
    protected $usr_id;

    /******************************************************

        METODOS GET

    ******************************************************/

    public function getVur_id()
    {
        return $this->vur_id->getVal();
    }
    public function getUur_id()
    {
        return $this->uur_id->getVal();
    }
    public function getGpo_id()
    {
        return $this->gpo_id->getVal();
    }
    public function getUr_id()
    {
        return $this->ur_id->getVal();
    }
    public function getVur_ejercicio()
    {
        return $this->vur_ejercicio->getVal();
    }
    public function getVur_nombre()
    {
        return $this->vur_nombre->getVal();
    }
    public function getVur_urClave()
    {
        return $this->vur_urClave->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

        METODOS SET

    ******************************************************/

    public function setVur_id($val)
    {
        $this->vur_id->setVal($val);
    }
    public function setUur_id($val)
    {
        $this->uur_id->setVal($val);
    }
    public function setGpo_id($val)
    {
        $this->gpo_id->setVal($val);
    }
    public function setUr_id($val)
    {
        $this->ur_id->setVal($val);
    }
    public function setVur_ejercicio($val)
    {
        $this->vur_ejercicio->setVal($val);
    }
    public function setVur_nombre($val)
    {
        $this->vur_nombre->setVal($val);
    }
    public function setVur_urClave($val)
    {
        $this->vur_urClave->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

        METODOS STA (SET sin persistencia)

    ******************************************************/

    public function staVur_id($val)
    {
        $this->vur_id->staVal($val);
    }
    public function staUur_id($val)
    {
        $this->uur_id->staVal($val);
    }
    public function staGpo_id($val)
    {
        $this->gpo_id->staVal($val);
    }
    public function staUr_id($val)
    {
        $this->ur_id->staVal($val);
    }
    public function staVur_ejercicio($val)
    {
        $this->vur_ejercicio->staVal($val);
    }
    public function staVur_nombre($val)
    {
        $this->vur_nombre->staVal($val);
    }
    public function staVur_urClave($val)
    {
        $this->vur_urClave->staVal($val);
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

        parent::__construct(['tabla' => BD_SINPRES . '.vin_UnidadResponsable']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        // ¿NO DEBERÍA SER VUR_ID, Y NO UUR_ID, EL CAMPO QUE SE GUARDE EN TABLAS COMO ctrlUsuario.cat_UsuarioPrevio

        // $this->vur_id = new Atributo("numero","NO","PRI","vur_id");
        $this->uur_id = new Atributo('numero', 'NO', 'PRI', 'uur_id');
        $this->gpo_id = new Atributo('numero', 'YES', 'MUL', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'YES', '', 'ur_id');
        $this->vur_ejercicio = new Atributo('numero', 'NO', 'PRI', 'vur_ejercicio');   // Le ponemos PRIMARY para que la incluya en el WHERE
        $this->vur_nombre = new Atributo('cadena', 'NO', '', 'vur_nombre');
        $this->vur_urClave = new Atributo('numero', 'YES', '', 'vur_urClave');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

    }


    /******************************************************

        FUNCIONES ESTÁTICAS

    ******************************************************/
    // Como -en teoría- no haremos ABC en ésta tabla... no pondremos las funciones para eso

}


// Fin del código
