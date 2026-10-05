<?php

require_once dirname(__FILE__) . '/../Entidad.php';

class SolicitudAcceso extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $sac_id; //Identificador de cat_SolicitudAcceso
    protected $sac_fechaAlta; //Fecha de alta de la solicitud
    protected $sac_fechaFormalizada; //Fecha en que se formalizó el permiso
    protected $sac_ejercicio; //Ejercicio de la solicitud de acceso.
    protected $sai; //Define si la solicitud es para el sistema SAI
    protected $sinpres; //Define si la solicitud es para el sistema SINPRES
    protected $sigda; //Define si la solicitud es para el sistema SIGDA
    protected $fichas; //Define si la solicitud es para el sistema de FICHAS
    protected $sac_sefip; //Define si la solicitud es para el sistema de SEFIP
    protected $siox; //Define si la solicitud es para el sistema SIOX
    protected $sac_requerimiento; //Requerimientos de la solicitud
    protected $sac_comentario; //Comentarios de la solicitud
    protected $sac_responsable; //Comentarios de la solicitud
    protected $sac_jefe; //Nombre del director del departamento o nombre del titular administrativo que autoriza la solicitud
    protected $eso_id; //cat_EstadoSolicitud - Llave foránea - Identificador del estado de la solicitud
    protected $sac_tipo;
    protected $sac_ticket;
    protected $usr_id; //Usuario que ingresa el registro

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getSac_id()
    {
        return $this->sac_id->getVal();
    }
    public function getFechaAlta()
    {
        return $this->sac_fechaAlta->getVal();
    }
    public function getFechaFormalizada()
    {
        return $this->sac_fechaFormalizada->getVal();
    }
    public function getEjercicio()
    {
        return $this->sac_ejercicio->getVal();
    }
    public function getSai()
    {
        return $this->sai->getVal();
    }
    public function getSinpres()
    {
        return $this->sinpres->getVal();
    }
    public function getSigda()
    {
        return $this->sigda->getVal();
    }
    public function getFichas()
    {
        return $this->fichas->getVal();
    }
    public function getSefip()
    {
        return $this->sac_sefip->getVal();
    }
    public function getRequerimiento()
    {
        return $this->sac_requerimiento->getVal();
    }
    public function getComentario()
    {
        return $this->sac_comentario->getVal();
    }
    public function getResponsable()
    {
        return $this->sac_responsable->getVal();
    }
    public function getJefe()
    {
        return $this->sac_jefe->getVal();
    }
    public function getEso_id()
    {
        return $this->eso_id->getVal();
    }
    public function getSac_tipo()
    {
        return $this->sac_tipo->getVal();
    }
    public function getSac_ticket()
    {
        return $this->sac_ticket->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    public function getSiox()
    {
        return $this->siox->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setSac_id($val)
    {
        $this->sac_id->setVal($val);
    }
    public function setFechaAlta($val)
    {
        $this->sac_fechaAlta->setVal($val);
    }
    public function setFechaFormalizada($val)
    {
        $this->sac_fechaFormalizada->setVal($val);
    }
    public function setEjercicio($val)
    {
        $this->sac_ejercicio->setVal($val);
    }
    public function setSai($val)
    {
        $this->sai->setVal($val);
    }
    public function setSinpres($val)
    {
        $this->sinpres->setVal($val);
    }
    public function setSigda($val)
    {
        $this->sigda->setVal($val);
    }
    public function setFichas($val)
    {
        $this->fichas->setVal($val);
    }
    public function setSefip($val)
    {
        $this->sac_sefip->setVal($val);
    }
    public function setRequerimiento($val)
    {
        $this->sac_requerimiento->setVal($val);
    }
    public function setComentario($val)
    {
        $this->sac_comentario->setVal($val);
    }
    public function setResponsable($val)
    {
        $this->sac_responsable->setVal($val);
    }
    public function setJefe($val)
    {
        $this->sac_jefe->setVal($val);
    }
    public function setEso_id($val)
    {
        $this->eso_id->setVal($val);
    }
    public function setSac_tipo($val)
    {
        $this->sac_tipo->setVal($val);
    }
    public function setSac_ticket($val)
    {
        $this->sac_ticket->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    public function setSiox($val)
    {
        $this->siox->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staSac_id($val)
    {
        $this->sac_id->staVal($val);
    }
    public function staFechaAlta($val)
    {
        $this->sac_fechaAlta->staVal($val);
    }
    public function staFechaFormalizada($val)
    {
        $this->sac_fechaFormalizada->staVal($val);
    }
    public function staEjercicio($val)
    {
        $this->sac_ejercicio->staVal($val);
    }
    public function staSai($val)
    {
        $this->sai->staVal($val);
    }
    public function staSinpres($val)
    {
        $this->sinpres->staVal($val);
    }
    public function staSigda($val)
    {
        $this->sigda->staVal($val);
    }
    public function staFichas($val)
    {
        $this->fichas->staVal($val);
    }
    public function staSefip($val)
    {
        $this->sac_sefip->staVal($val);
    }
    public function staRequerimiento($val)
    {
        $this->sac_requerimiento->staVal($val);
    }
    public function staComentario($val)
    {
        $this->sac_comentario->staVal($val);
    }
    public function staResponsable($val)
    {
        $this->sac_responsable->staVal($val);
    }
    public function staJefe($val)
    {
        $this->sac_jefe->staVal($val);
    }
    public function staEso_id($val)
    {
        $this->eso_id->staVal($val);
    }
    public function staSac_tipo($val)
    {
        $this->sac_tipo->setVal($val);
    }
    public function staSac_ticket($val)
    {
        $this->sac_ticket->setVal($val);
    }
    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }
    public function staSiox($val)
    {
        $this->siox->staVal($val);
    }

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(['tabla' => BD_USUARIO . '.cat_SolicitudAcceso']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->sac_id = new Atributo('numero', 'NO', 'PRI', 'sac_id');
        $this->sac_fechaAlta = new Atributo('fecha', 'NO', '', 'sac_fechaAlta');
        $this->sac_fechaFormalizada = new Atributo('fecha', 'YES', '', 'sac_fechaFormalizada');
        $this->sac_ejercicio = new Atributo('numero', 'NO', '', 'sac_ejercicio');
        $this->sai = new Atributo('numero', 'NO', '', 'sac_sai');
        $this->sinpres = new Atributo('numero', 'NO', '', 'sac_sinpres');
        $this->sigda = new Atributo('numero', 'NO', '', 'sac_sigda');
        $this->fichas = new Atributo('numero', 'NO', '', 'sac_fichas');
        $this->sac_sefip = new Atributo('numero', 'NO', '', 'sac_sefip');
        $this->sac_requerimiento = new Atributo('cadena', 'NO', '', 'sac_requerimiento');
        $this->sac_comentario = new Atributo('cadena', 'NO', '', 'sac_comentario');
        $this->sac_responsable = new Atributo('cadena', 'NO', '', 'sac_responsable');
        $this->sac_jefe = new Atributo('cadena', 'NO', '', 'sac_jefe');
        $this->eso_id = new Atributo('numero', 'NO', 'MUL', 'eso_id');
        $this->sac_tipo = new Atributo('cadena', 'NO', '', 'sac_tipo');
        $this->sac_ticket = new Atributo('cadena', 'NO', '', 'sac_ticket');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');
        $this->siox = new Atributo('numero', 'NO', '', 'sac_siox');

    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {

        $sac = new SolicitudAcceso();
        if (isset($p->sac_id)) {
            $sac->setSac_id($p->sac_id);
        }

        if (isset($p->sac_fechaAlta)) {
            $sac->setFechaAlta($p->sac_fechaAlta);
        }

        if (isset($p->sac_fechaFormalizada)) {
            $sac->setFechaFormalizada($p->sac_fechaFormalizada);
        }

        if (isset($p->sac_ejercicio)) {
            $sac->setEjercicio($p->sac_ejercicio);
        }

        if (isset($p->sai)) {
            $sac->setSai($p->sai);
        }

        if (isset($p->sinpres)) {
            $sac->setSinpres($p->sinpres);
        }

        if (isset($p->sigda)) {
            $sac->setSigda($p->sigda);
        }

        if (isset($p->fichas)) {
            $sac->setFichas($p->fichas);
        }

        if (isset($p->sac_sefip)) {
            $sac->setSefip($p->sac_sefip);
        }

        if (isset($p->requerimiento)) {
            $sac->setRequerimiento($p->requerimiento);
        }

        if (isset($p->comentario)) {
            $sac->setComentario($p->comentario);
        }

        if (isset($p->responsable)) {
            $sac->setResponsable($p->responsable);
        }

        if (isset($p->jefe)) {
            $sac->setJefe($p->jefe);
        }

        if (isset($p->eso_id)) {
            $sac->setEso_id($p->eso_id);
        }

        if (isset($p->sac_tipo)) {
            $sac->setSac_tipo($p->sac_tipo);
        }

        if (isset($p->sac_ticket)) {
            $sac->setSac_ticket($p->sac_ticket);
        }

        if (isset($p->usr_id)) {
            $sac->setUsr_id($p->usr_id);
        }

        if (isset($p->siox)) {
            $sac->setSiox($p->siox);
        }

        // Para la bitàcora...
        switch ($p->abc) {
            case 'a':$bitacora = 'NUEVA ';
                break;
            case 'b':$bitacora = 'ELIMINAR ';
                break;
            case 'c':$bitacora = 'MODIFICAR ';
                break;
        }

        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
        $sql = '
            SET 
            @usr_id = :usr_id,
            @bit_observacion = :observacion,
            @usr_ip = INET_ATON( :ip );
        ';
        $parametros = [
            ':usr_id'      => $_SESSION[_S]['usr_id'],
            ':observacion' => $bitacora . 'SOLICITUD',
            ':ip'          => $ip,
        ];
        $sac->setSql($sql);
        $sac->conectar();
        $sac->consultar($parametros);

        switch ($p->abc) {
            case 'a':$sac->insert(false);
                return $sac->getUltimoId();
                break;
            case 'b':$sac->delete(false);
                break;
            case 'c':$sac->update(false);
                break;
        }
    }

}
