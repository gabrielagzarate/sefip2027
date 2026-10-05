<?php

require_once dirname(__FILE__) . "/../../Entidad.php";

class LayoutArchivo extends Entidad
{

    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $lay_id; //
    protected $lar_ejercicio; //
    protected $msc_id; //
    protected $lar_dia; //
    protected $lar_nombre; //
    protected $usr_id; //
    protected $lar_archivo; //
    protected $lar_numeroCopia; //

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getLay_id()
    {
        return $this->lay_id->getVal();
    }
    public function getEjercicio()
    {
        return $this->lar_ejercicio->getVal();
    }
    public function getMsc_id()
    {
        return $this->msc_id->getVal();
    }
    public function getDia()
    {
        return $this->lar_dia->getVal();
    }
    public function getNombre()
    {
        return $this->lar_nombre->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    public function getArchivo()
    {
        return $this->lar_archivo->getVal();
    }
    public function getNumeroCopia()
    {
        return $this->lar_numeroCopia->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setLay_id($val)
    {
        $this->lay_id->setVal($val);
    }
    public function setEjercicio($val)
    {
        $this->lar_ejercicio->setVal($val);
    }
    public function setMsc_id($val)
    {
        $this->msc_id->setVal($val);
    }
    public function setDia($val)
    {
        $this->lar_dia->setVal($val);
    }
    public function setNombre($val)
    {
        $this->lar_nombre->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    public function setArchivo($val)
    {
        $this->lar_archivo->setVal($val);
    }
    public function setNumeroCopia($val)
    {
        $this->lar_numeroCopia->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staLay_id($val)
    {
        $this->lay_id->staVal($val);
    }
    public function staEjercicio($val)
    {
        $this->lar_ejercicio->staVal($val);
    }
    public function staMsc_id($val)
    {
        $this->msc_id->staVal($val);
    }
    public function staDia($val)
    {
        $this->lar_dia->staVal($val);
    }
    public function staNombre($val)
    {
        $this->lar_nombre->staVal($val);
    }
    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }
    public function staArchivo($val)
    {
        $this->lar_archivo->staVal($val);
    }
    public function staNumeroCopia($val)
    {
        $this->lar_numeroCopia->staVal($val);
    }

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(array("tabla" => BD_TESORERIA.".lot_LayoutArchivo"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->lay_id = new Atributo("numero", "NO", "PRI", "lay_id");
        $this->lar_ejercicio = new Atributo("numero", "NO", "PRI", "lar_ejercicio");
        $this->msc_id = new Atributo("numero", "NO", "PRI", "msc_id");
        $this->lar_dia = new Atributo("numero", "NO", "PRI", "lar_dia");
        $this->lar_nombre = new Atributo("cadena", "NO", "PRI", "lar_nombre");
        //$this->usr_id = new Atributo("numero","NO","PRI","usr_id");
        $this->lar_archivo = new Atributo("cadena", "NO", "", "lar_archivo");
        $this->lar_numeroCopia = new Atributo("numero", "NO", "", "lar_numeroCopia");

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $lar = new LayoutArchivo();
        if (isset($p->lay_id)) {
            $lar->setLay_id($p->lay_id);
        }

        if (isset($p->ejercicio)) {
            $lar->setEjercicio($p->ejercicio);
        }

        if (isset($p->msc_id)) {
            $lar->setMsc_id($p->msc_id);
        }

        if (isset($p->dia)) {
            $lar->setDia($p->dia);
        }

        if (isset($p->nombre)) {
            $lar->setNombre($p->nombre);
        }

        if (isset($p->archivo)) {
            $lar->setArchivo($p->archivo);
        }

        if (isset($p->numeroCopia)) {
            $lar->setNumeroCopia($p->numeroCopia);
        }

        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $bit_observacion = 'lay_id:' . $p->lay_id . ' anio:' . $p->ejercicio . 'mes:' . $p->msc_id . 'dia:' . $p->dia . 'nombre:' . (isset($p->nombre) ? $p->nombre : '') . 'usuario:' . $bit_usr_id . 'archivo:' . $p->archivo . 'copias:' . $p->numeroCopia;
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $params = [$ip, $bit_usr_id];
        switch ($p->abc) {
            case "a":
                $lar->conectar();
                $bit_observacion = 'REGISTRO INSERTADO ' . $bit_observacion;
                array_push($params, $bit_observacion);
                $lar->setSql(' SET @usr_ip =INET_ATON(?), @usr_id=? , @bit_observacion =?;');
                $lar->consultar($params);
                $lar->insert(false);
                return $lar->getUltimoId();
                break;
            case "b":$lar->delete();
                break;
            case "c":$lar->update();
                break;
        }
    }

}
