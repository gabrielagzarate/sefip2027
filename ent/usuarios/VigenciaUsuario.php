<?php

require_once dirname(__FILE__) . '/../Entidad.php';

class VigenciaUsuario extends Entidad
{
    /******************************************************

        ATRIBUTOS DE LA CLASE

    ******************************************************/

    protected $vgu_id;
    protected $usr_id;
    protected $uur_id;
    protected $vgu_ejercicio;
    protected $vgu_clave;
    protected $vgu_claveAcceso;
    protected $sts_id;
    protected $vgu_fechaInicio;
    protected $vgu_fechaFin;
    protected $vgu_vigenciaFechaContrasenia;
    protected $usr_usu;

    /******************************************************

        METODOS GET

    ******************************************************/

    public function getVgu_id()
    {
        return $this->vgu_id->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    public function getUur_id()
    {
        return $this->uur_id->getVal();
    }
    public function getVgu_ejercicio()
    {
        return $this->vgu_ejercicio->getVal();
    }
    public function getVgu_clave()
    {
        return $this->vgu_clave->getVal();
    }
    public function getVgu_claveAcceso()
    {
        return $this->vgu_claveAcceso->getVal();
    }
    public function getSts_id()
    {
        return $this->sts_id->getVal();
    }
    public function getVgu_fechaInicio()
    {
        return $this->vgu_fechaInicio->getVal();
    }
    public function getVgu_fechaFin()
    {
        return $this->vgu_fechaFin->getVal();
    }
    public function getVgu_vigenciaFechaContrasenia()
    {
        return $this->vgu_vigenciaFechaContrasenia->getVal();
    }
    public function getUsr_usu()
    {
        return $this->usr_usu->getVal();
    }

    /******************************************************

        METODOS SET

    ******************************************************/

    public function setVgu_id($val)
    {
        $this->vgu_id->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    public function setUur_id($val)
    {
        $this->uur_id->setVal($val);
    }
    public function setVgu_ejercicio($val)
    {
        $this->vgu_ejercicio->setVal($val);
    }
    public function setVgu_clave($val)
    {
        $this->vgu_clave->setVal($val);
    }
    public function setVgu_claveAcceso($val)
    {
        $this->vgu_claveAcceso->setVal($val);
    }
    public function setSts_id($val)
    {
        $this->sts_id->setVal($val);
    }
    public function setVgu_fechaInicio($val)
    {
        $this->vgu_fechaInicio->setVal($val);
    }
    public function setVgu_fechaFin($val)
    {
        $this->vgu_fechaFin->setVal($val);
    }
    public function setVgu_vigenciaFechaContrasenia($val)
    {
        $this->vgu_vigenciaFechaContrasenia->setVal($val);
    }
    public function setUsr_usu($val)
    {
        $this->usr_usu->setVal($val);
    }

    /******************************************************

        METODOS STA (SET sin persistencia)

    ******************************************************/

    public function staVgu_id($val)
    {
        $this->vgu_id->staVal($val);
    }
    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }
    public function staUur_id($val)
    {
        $this->uur_id->staVal($val);
    }
    public function staVgu_ejercicio($val)
    {
        $this->vgu_ejercicio->staVal($val);
    }
    public function staVgu_clave($val)
    {
        $this->vgu_clave->staVal($val);
    }
    public function staVgu_claveAcceso($val)
    {
        $this->vgu_claveAcceso->staVal($val);
    }
    public function staSts_id($val)
    {
        $this->sts_id->staVal($val);
    }
    public function staVgu_fechaInicio($val)
    {
        $this->vgu_fechaInicio->staVal($val);
    }
    public function staVgu_fechaFin($val)
    {
        $this->vgu_fechaFin->staVal($val);
    }
    public function staVgu_vigenciaFechaContrasenia($val)
    {
        $this->vgu_vigenciaFechaContrasenia->staVal($val);
    }
    public function staUsr_usu($val)
    {
        $this->usr_usu->staVal($val);
    }

    /******************************************************

        CONSTRUCTOR

    ******************************************************/

    public function __construct()
    {

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object)func_get_arg(0) : null) : null);

        parent::__construct(['tabla' => BD_USUARIO . '.cat_VigenciaUsuario']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->vgu_id = new Atributo('numero', 'NO', 'PRI', 'vgu_id');
        $this->usr_id = new Atributo('numero', 'NO', 'PRI', 'usr_id');
        $this->uur_id = new Atributo('numero', 'NO', 'NO', 'uur_id');
        $this->vgu_ejercicio = new Atributo('numero', 'NO', 'PRI', 'vgu_ejercicio');
        $this->vgu_clave = new Atributo('cadena', 'NO', '', 'vgu_clave');
        $this->vgu_claveAcceso = new Atributo('blob', 'NO', '', 'vgu_claveAcceso');
        $this->sts_id = new Atributo('numero', 'NO', 'MUL', 'sts_id');
        $this->vgu_fechaInicio = new Atributo('fecha', 'NO', '', 'vgu_fechaInicio');
        $this->vgu_fechaFin = new Atributo('fecha', 'NO', '', 'vgu_fechaFin');
        $this->vgu_vigenciaFechaContrasenia = new Atributo('fecha', 'NO', '', 'vgu_vigenciaFechaContrasenia');
        $this->usr_usu = new Atributo('numero', 'NO', '', 'usr_usu');

    }

    public static function abc($p)
    {
        $usr = new VigenciaUsuario();

        if (isset($p->vgu_id)) {
            $usr->setVgu_id($p->vgu_id);
        }
        if (isset($p->usr_id)) {
            $usr->setUsr_id($p->usr_id);
        }
        if (isset($p->uur_id)) {
            $usr->setUur_id($p->uur_id);
        }
        if (isset($p->vgu_ejercicio)) {
            $usr->setVgu_ejercicio($p->vgu_ejercicio);
        }
        if (isset($p->vgu_clave) && $p->vgu_clave != '') {
            $usr->setVgu_clave($p->vgu_clave);
        }
        if (isset($p->vgu_claveAcceso) && $p->vgu_claveAcceso != '') {
            $usr->setVgu_claveAcceso($p->vgu_claveAcceso);
        }
        if (isset($p->sts_id)) {
            $usr->setSts_id($p->sts_id);
        }
        if (isset($p->vgu_fechaInicio)) {
            $usr->staVgu_fechaInicio($p->vgu_fechaInicio);
        }
        if (isset($p->vgu_fechaFin)) {
            $usr->setVgu_fechaFin($p->vgu_fechaFin);
        }
        if (isset($p->vgu_vigenciaFechaContrasenia)) {
            $usr->setVgu_vigenciaFechaContrasenia($p->vgu_vigenciaFechaContrasenia);
        }
        if (isset($p->usr_usu)) {
            $usr->setUsr_usu($p->usr_usu);
        }
        // Para la bitàcora...
        switch ($p->abc) {
            case 'a': $bitacora = 'NUEVA ';
                break;
            case 'b': $bitacora = 'ELIMINAR ';
                break;
            case 'c': $bitacora = 'MODIFICAR ';
                break;
        }

        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
        $sql =
            'SET            
            @usr_id = :usrid,
            @bit_observacion = "SE MODIFICO CONTRASEÑA POR EL USUARIO",
            @usr_ip = INET_ATON( :ip )    
        ';
        $parametros = [
            ':usrid' => $_SESSION[_S]['usr_id'],
            ':ip'    => $ip,
        ];
        $usr->setSql($sql);
        $usr->conectar();
        $usr->consultar($parametros);
        switch ($p->abc) {
            case 'a': $usr->insert(false);
                return $usr->getUltimoId();
                break;
            case 'b': $usr->delete(false);
                break;
            case 'c': $usr->update(false);
                break;
        }
    }

}
