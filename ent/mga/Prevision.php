<?php

require_once dirname(__FILE__) . '/../Entidad.php';

class Prevision extends Entidad
{
    /******************************************************

        ATRIBUTOS DE LA CLASE

    ******************************************************/

    protected $gpo_id;	//identificador del Grupo de la Unidad Responsable. Depende de cat_UnidadEjecutora, poa_OrigenRecurso
    protected $ur_id;	//identificador de la Unidad Responsable. Depende de cat_UnidadEjecutora, poa_OrigenRecurso
    protected $ue_id;	//identificador de la Unidad Ejecutora. Depende de cat_UnidadEjecutora
    protected $epg_id;	//Identificador del encuadre programatico. Depende de enc_Programatico,poa_OrigenRecurso
    protected $pg_id;	//Identificador de la Partida Generica. Depende de cat_PartidaEspecifica
    protected $pe_id;	//Identificador de la Partida Espécifica. Depende de cat_PartidaEspecifica
    protected $ecf_id;	//Identificador del Encuadre de Clave de Financiamiento. Depende de enc_ClaveFinanciamiento, poa_OrigenRecurso.
    protected $tcp_id;	//Identificador del Tipo de Compra. Depende de cat_TipoCompra
    protected $montos;	//calandario de costos por partida
    protected $usr_id;	//

    /******************************************************

        METODOS GET

    ******************************************************/

    public function getGpo_id()
    {
        return $this->gpo_id->getVal();
    }
    public function getUr_id()
    {
        return $this->ur_id->getVal();
    }
    public function getUe_id()
    {
        return $this->ue_id->getVal();
    }
    public function getEpg_id()
    {
        return $this->epg_id->getVal();
    }
    public function getPg_id()
    {
        return $this->pg_id->getVal();
    }
    public function getPe_id()
    {
        return $this->pe_id->getVal();
    }
    public function getEcf_id()
    {
        return $this->ecf_id->getVal();
    }
    public function getTcp_id()
    {
        return $this->tcp_id->getVal();
    }
    public function getMontos()
    {
        return $this->montos;
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

        METODOS SET

    ******************************************************/

    public function setGpo_id($val)
    {
        $this->gpo_id->setVal($val);
    }
    public function setUr_id($val)
    {
        $this->ur_id->setVal($val);
    }
    public function setUe_id($val)
    {
        $this->ue_id->setVal($val);
    }
    public function setEpg_id($val)
    {
        $this->epg_id->setVal($val);
    }
    public function setPg_id($val)
    {
        $this->pg_id->setVal($val);
    }
    public function setPe_id($val)
    {
        $this->pe_id->setVal($val);
    }
    public function setEcf_id($val)
    {
        $this->ecf_id->setVal($val);
    }
    public function setTcp_id($val)
    {
        $this->tcp_id->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

        METODOS STA (SET sin persistencia)

    ******************************************************/

    public function staGpo_id($val)
    {
        $this->gpo_id->staVal($val);
    }
    public function staUr_id($val)
    {
        $this->ur_id->staVal($val);
    }
    public function staUe_id($val)
    {
        $this->ue_id->staVal($val);
    }
    public function staEpg_id($val)
    {
        $this->epg_id->staVal($val);
    }
    public function staPg_id($val)
    {
        $this->pg_id->staVal($val);
    }
    public function staPe_id($val)
    {
        $this->pe_id->staVal($val);
    }
    public function staEcf_id($val)
    {
        $this->ecf_id->staVal($val);
    }
    public function staTcp_id($val)
    {
        $this->tcp_id->staVal($val);
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

        parent::__construct(['tabla' => 'poa_Prevision']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'ur_id');
        $this->ue_id = new Atributo('numero', 'NO', 'PRI', 'ue_id');
        $this->epg_id = new Atributo('numero', 'NO', 'PRI', 'epg_id');
        $this->pg_id = new Atributo('numero', 'NO', 'PRI', 'pg_id');
        $this->pe_id = new Atributo('numero', 'NO', 'PRI', 'pe_id');
        $this->ecf_id = new Atributo('numero', 'NO', 'PRI', 'ecf_id');
        $this->tcp_id = new Atributo('cadena', 'NO', 'PRI', 'tcp_id');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        $this->montos = new Meses(['prefix' => 'mon_']);
    }


    /******************************************************

        FUNCIONES ESTÁTICAS

    ******************************************************/

    public static function abc($p)
    {
        $mon = new Prevision();
        if (isset($p->gpo_id)) {
            $mon->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $mon->setUr_id($p->ur_id);
        }
        if (isset($p->ue_id)) {
            $mon->setUe_id($p->ue_id);
        }
        if (isset($p->epg_id)) {
            $mon->setEpg_id($p->epg_id);
        }
        if (isset($p->pg_id)) {
            $mon->setPg_id($p->pg_id);
        }
        if (isset($p->pe_id)) {
            $mon->setPe_id($p->pe_id);
        }
        if (isset($p->ecf_id)) {
            $mon->setEcf_id($p->ecf_id);
        }
        if (isset($p->tcp_id)) {
            $mon->setTcp_id($p->tcp_id);
        }

        if (isset($p->montos)) {
            $mon->getMontos()->setArrayMeses($p->montos);
        }

        if (isset($p->usr_id)) {
            $mon->setUsr_id($p->usr_id);
        }

        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';

        $mon->conectar();
        $mon->setSql('
            SET
            @usr_ip =INET_ATON(?),
            @usr_id = ?,
            @bit_observacion = ?;
        ');
        $mon->consultar([$ip, $_SESSION[_S]['usr_id'], $p->bit_observacion]);

        switch ($p->abc) {
            case 'a':
                $mon->insert(false);
                return $mon->getUltimoId();
                break;
            case 'b':
                $mon->delete(false);
                break;
            case 'c':
                $mon->update(false);
                break;
        }
    }
}
