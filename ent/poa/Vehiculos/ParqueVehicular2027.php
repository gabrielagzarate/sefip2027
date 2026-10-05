<?php

require_once __DIR__ . '/../../Entidad.php';

class ParqueVehicular2027 extends Entidad
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $pqv_id; //Clave Identificadora del numero de serie vehicular
    protected $gpo_id; //cat_UnidadEjecutora - Llave Foranea
    protected $ur_id; //cat_UnidadEjecutora - Llave Foranea
    protected $ue_id; //cat_UnidadEjecutora - Llave Foranea
    protected $epg_id; //enc_Programatico - Llave Foranea
    protected $ecf_id; //enc_ClaveFinanciamiento - Llave Foranea
    protected $pqv_numeroPlacas; //Número de placa del vehículo
    protected $pqv_modelo; //Modelo del vehículo
    protected $pqv_tipo; //Descripción del vehículo, mediante el cual la dependencia lo reconoce
    protected $civ_id; //cat_CilindrosVehiculo - Llave Foranea
    protected $ubv_id; //cat_UbicacionVehiculo - Llave Foranea
    protected $usv_id; //cat_UsoVehiculo - Llave Foranea
    protected $sts_id; //cat_Estado - Llave Foranea
    protected $tfv_id; //Tipo Financiamiento vehiculo 1.- Patrimonio del estado, 2.- Arrendado
    protected $usr_id; //Identificador de usuario que captura/actualiza el registro
    /******************************************************
    METODOS GET
     ******************************************************/
    public function getPqv_id()
    {
        return $this->pqv_id->getVal();
    }
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
    public function getEcf_id()
    {
        return $this->ecf_id->getVal();
    }
    public function getNumeroPlacas()
    {
        return $this->pqv_numeroPlacas->getVal();
    }
    public function getModelo()
    {
        return $this->pqv_modelo->getVal();
    }
    public function getTipo()
    {
        return $this->pqv_tipo->getVal();
    }
    public function getCiv_id()
    {
        return $this->civ_id->getVal();
    }
    public function getUbv_id()
    {
        return $this->ubv_id->getVal();
    }
    public function getUsv_id()
    {
        return $this->usv_id->getVal();
    }
    public function getSts_id()
    {
        return $this->sts_id->getVal();
    }
    public function getTfv_id()
    {
        return $this->tfv_id->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    /******************************************************
    METODOS SET
     ******************************************************/
    public function setPqv_id($val)
    {
        $this->pqv_id->setVal(trim($val));
    }
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
    public function setEcf_id($val)
    {
        $this->ecf_id->setVal($val);
    }
    public function setNumeroPlacas($val)
    {
        $this->pqv_numeroPlacas->setVal(trim($val));
    }
    public function setModelo($val)
    {
        $this->pqv_modelo->setVal($val);
    }
    public function setTipo($val)
    {
        $this->pqv_tipo->setVal(trim($val));
    }
    public function setCiv_id($val)
    {
        $this->civ_id->setVal($val);
    }
    public function setUbv_id($val)
    {
        $this->ubv_id->setVal($val);
    }
    public function setUsv_id($val)
    {
        $this->usv_id->setVal($val);
    }
    public function setSts_id($val)
    {
        $this->sts_id->setVal($val);
    }
    public function setTfv_id($val)
    {
        $this->tfv_id->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/
    public function staPqv_id($val)
    {
        $this->pqv_id->staVal($val);
    }
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
    public function staEcf_id($val)
    {
        $this->ecf_id->staVal($val);
    }
    public function staNumeroPlacas($val)
    {
        $this->pqv_numeroPlacas->staVal($val);
    }
    public function staModelo($val)
    {
        $this->pqv_modelo->staVal($val);
    }
    public function staTipo($val)
    {
        $this->pqv_tipo->staVal($val);
    }
    public function staCiv_id($val)
    {
        $this->civ_id->staVal($val);
    }
    public function staUbv_id($val)
    {
        $this->ubv_id->staVal($val);
    }
    public function staUsv_id($val)
    {
        $this->usv_id->staVal($val);
    }
    public function staSts_id($val)
    {
        $this->sts_id->staVal($val);
    }
    public function staTfv_id($val)
    {
        $this->tfv_id->staVal($val);
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
        parent::__construct(['tabla' => 'cat_ParqueVehicular']);
        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->pqv_id = new Atributo('cadena', 'NO', 'PRI', 'pqv_id');
        $this->gpo_id = new Atributo('numero', 'NO', 'MUL', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', '', 'ur_id');
        $this->ue_id = new Atributo('numero', 'NO', '', 'ue_id');
        $this->epg_id = new Atributo('numero', 'NO', '', 'epg_id');
        $this->ecf_id = new Atributo('numero', 'NO', '', 'ecf_id');
        $this->pqv_numeroPlacas = new Atributo('cadena', 'NO', '', 'pqv_numeroPlacas');
        $this->pqv_modelo = new Atributo('numero', 'NO', '', 'pqv_modelo');
        $this->pqv_tipo = new Atributo('cadena', 'NO', '', 'pqv_tipo');
        $this->civ_id = new Atributo('numero', 'NO', 'MUL', 'civ_id');
        $this->ubv_id = new Atributo('numero', 'NO', 'MUL', 'ubv_id');
        $this->usv_id = new Atributo('numero', 'NO', 'MUL', 'usv_id');
        $this->sts_id = new Atributo('numero', 'NO', 'MUL', 'sts_id');
        $this->tfv_id = new Atributo('numero', 'NO', 'MUL', 'tfv_id');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function abc($p)
    {
        $pqv = new ParqueVehicular2027();
        if (isset($p->pqv_id)) {
            $pqv->setPqv_id($p->pqv_id);
        }
        if (isset($p->gpo_id)) {
            $pqv->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $pqv->setUr_id($p->ur_id);
        }
        if (isset($p->ue_id)) {
            $pqv->setUe_id($p->ue_id);
        }
        if (isset($p->epg_id)) {
            $pqv->setEpg_id($p->epg_id);
        }
        if (isset($p->ecf_id)) {
            $pqv->setEcf_id($p->ecf_id);
        }
        if (isset($p->numeroPlacas)) {
            $pqv->setNumeroPlacas($p->numeroPlacas);
        }
        if (isset($p->modelo)) {
            $pqv->setModelo($p->modelo);
        }
        if (isset($p->tipo)) {
            $pqv->setTipo($p->tipo);
        }
        if (isset($p->civ_id)) {
            $pqv->setCiv_id($p->civ_id);
        }
        if (isset($p->ubv_id)) {
            $pqv->setUbv_id($p->ubv_id);
        }
        if (isset($p->usv_id)) {
            $pqv->setUsv_id($p->usv_id);
        }
        if (isset($p->sts_id)) {
            $pqv->setSts_id($p->sts_id);
        }
        if (isset($p->tfv_id)) {
            $pqv->setTfv_id($p->tfv_id);
        }
        if (isset($p->usr_id)) {
            $pqv->setUsr_id($p->usr_id);
        }

        switch ($p->abc) {
            case 'a':
                $pqv->insert();
                return $pqv->getUltimoId();
                break;
            case 'b':
                $pqv->delete();
                break;
            case 'c':
                $pqv->update();
                break;
        }
    }
}
