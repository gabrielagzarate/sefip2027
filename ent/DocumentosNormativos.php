<?php

require_once __DIR__ . '/Entidad.php';

class DocumentosNormativos extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/
    protected $dn_id; //Identificador
    protected $ur_id; //UR
    protected $tituloCatalogo; //Titulo
    protected $nombreOriginal; //Nombre archivo original
    protected $nombreGuardado; //Nombre archivo guardado
    protected $descripcion; //
    protected $dn_tipo;
    protected $dn_ruta;
    protected $dn_ejercicio;
    protected $sts_id; //

    /******************************************************
    METODOS GET
     ******************************************************/
    public function getDn_id()
    {
        return $this->dn_id->getVal();
    }
    public function getUr()
    {
        return $this->ur_id->getVal();
    }
    public function getTituloCatalogo()
    {
        return $this->tituloCatalogo->getVal();
    }
    public function getNombreOriginal()
    {
        return $this->nombreOriginal->getVal();
    }
    public function getNombreGuardado()
    {
        return $this->nombreGuardado->getVal();
    }
    public function getDescripcion()
    {
        return $this->descripcion->getVal();
    }
    public function getTipo()
    {
        return $this->dn_tipo->getVal();
    }
    public function getRuta()
    {
        return $this->dn_ruta->getVal();
    }
    public function getEjercicio()
    {
        return $this->dn_ejercicio->getVal();
    }
    public function getSts_id()
    {
        return $this->sts_id->getVal();
    }


    /******************************************************
    METODOS SET
     ******************************************************/
    public function setDn_id($val)
    {
        $this->dn_id->setVal($val);
    }
    public function setUr($val)
    {
        $this->ur_id->setVal($val);
    }
    public function setTituloCatalogo($val)
    {
        $this->tituloCatalogo->setVal($val);
    }
    public function setNombreOriginal($val)
    {
        $this->nombreOriginal->setVal($val);
    }
    public function setNombreGuardado($val)
    {
        $this->nombreGuardado->setVal($val);
    }
    public function setDescripcion($val)
    {
        $this->descripcion->setVal($val);
    }
    public function setTipo($val)
    {
        $this->dn_tipo->setVal($val);
    }
    public function setRuta($val)
    {
        $this->dn_ruta->setVal($val);
    }
    public function setEjercicio($val)
    {
        $this->dn_ejercicio->setVal($val);
    }
    public function setSts_id($val)
    {
        $this->sts_id->setVal($val);
    }
    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/
    public function staDn_id($val)
    {
        $this->dn_id->staVal($val);
    }
    public function staUr($val)
    {
        $this->ur_id->staVal($val);
    }
    public function staTituloCatalogo($val)
    {
        $this->tituloCatalogo->staVal($val);
    }
    public function staNombreOriginal($val)
    {
        $this->nombreOriginal->staVal($val);
    }
    public function staNombreGuardado($val)
    {
        $this->nombreGuardado->staVal($val);
    }
    public function staDescripcion($val)
    {
        $this->descripcion->staVal($val);
    }
    public function staTipo($val)
    {
        $this->dn_tipo->staVal($val);
    }
    public function staRuta($val)
    {
        $this->dn_ruta->staVal($val);
    }
    public function staEjercicio($val)
    {
        $this->dn_ejercicio->staVal($val);
    }
    public function staSts_id($val)
    {
        $this->sts_id->staVal($val);
    }

    /******************************************************
    CONSTRUCTOR
     ******************************************************/
    public function __construct()
    {

        parent::__construct(['tabla' => 'cat_DocumentosNormativos']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->dn_id = new Atributo('numero', 'NO', 'PRI', 'dn_id');
        $this->ur_id = new Atributo('numero', '', '', 'ur_id');
        $this->tituloCatalogo = new Atributo('cadena', '', '', 'dn_tituloCatalogo');
        $this->nombreOriginal = new Atributo('cadena', '', '', 'dn_nombreOriginal');
        $this->nombreGuardado = new Atributo('cadena', '', '', 'dn_nombreGuardado');
        $this->descripcion = new Atributo('cadena', '', '', 'dn_descripcion');
        $this->tipo = new Atributo('cadena', '', '', 'dn_tipo');
        $this->ruta = new Atributo('cadena', '', '', 'dn_ruta');
        $this->ejercicio = new Atributo('cadena', '', '', 'dn_ejercicio');
        $this->sts_id = new Atributo('numero', '', '', 'sts_id');
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function abc($p)
    {
        $dc = new DocumentosNormativos();
        if (isset($p->dn_id)) {
            $dc->setDn_id($p->dn_id);
        }

        if (isset($p->ur_id)) {
            $dc->setUr($p->ur_id);
        }

        if (isset($p->tituloCatalogo)) {
            $dc->setTituloCatalogo($p->tituloCatalogo);
        }

        if (isset($p->nombreOriginal)) {
            $dc->setNombreOriginal($p->nombreOriginal);
        }

        if (isset($p->nombreGuardado)) {
            $dc->setNombreGuardado($p->nombreGuardado);
        }

        if (isset($p->descripcion)) {
            $dc->setDescripcion($p->descripcion);
        }

        if (isset($p->tipo)) {
            $dc->setTipo($p->tipo);
        }

        if (isset($p->ruta)) {
            $dc->setRuta($p->ruta);
        }

        if (isset($p->ejercicio)) {
            $dc->setEjercicio($p->ejercicio);
        }

        if (isset($p->sts_id)) {
            $dc->setSts_id($p->sts_id);
        }

        switch ($p->abc) {
            case 'a':
                $dc->insert();
                return $dc->getUltimoId();
                break;
            case 'c':
                $dc->update();
                break;
        }
    }

} // fin class
