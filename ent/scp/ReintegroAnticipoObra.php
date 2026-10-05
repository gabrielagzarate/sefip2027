<?php

require_once dirname(__FILE__) . '/../Entidad.php';

class ReintegroAnticipoObra extends Entidad
{
    /******************************************************

        ATRIBUTOS DE LA CLASE

    ******************************************************/

    protected $gpo_id;	//
    protected $ur_id;	//
    protected $rao_id;	//
    protected $ue_id;	//
    protected $epg_id;	//IDENTIFICADOR DEL ENCUADRE PROGRAMÁTICO
    protected $ecf_id;	//IDENTIFICADOR DEL ENCUADRE DE LA CLAVE DE FINANCIAMIENTO.
    protected $anioReintegro;	//AÑO DEL QUE SE EJECUTA EL REINTEGRO
    protected $oficio;	//NÚMERO DE OFICIO DEL REINTEGRO
    protected $archivoOficio;	//RUTA DEL ARCHIVO PDF DEL OFICIO DEL REINTEGRO
    protected $fechaReintegro;	//FECHA EN QUE SE EFECTÚA EL REINTEGRO
    protected $tdf_id;	//TIPO DE DOCUMENTO CON EL QUE SE PAGA EL REINTEGRO
    protected $documentoPago;	//REFERENCIA DEL TIPO DE DOCUMENTO DEL PAGO
    protected $archivoDocumentoPago;	//RUTA DEL ARCHIVO PDF DEL TIPO DE DOCUMENTO DE PAGO.
    protected $importe;	//IMPORTE DEL REINTEGRO
    protected $era_id;	//ESTADO DEL REINTEGRO
    protected $fechaElaboracion;	//FECHA EN LA QUE SE ELABORA EL REGISTRO DEL REINTEGRO
    protected $fechaAutorizacion;	//FECHA EN LA QUE SE AUTORIZA EL  REINTEGRO POR LA UR
    protected $fechaEnvio;	//FECHA EN LA QUE SE ENVÍA EL  REINTEGRO A SEFIN
    protected $fechaAplicacion;	//FECHA EN LA QUE SE APLICA EL REINTEGRO POR SEFIN
    protected $nombreAdministrativo;	//GUARDA EL NOMBRE DEL TITULAR ADMINISTRATIVO
    protected $cargoAdministrativo;	//GUARDA EL  CARGO DEL JEFE ADMINISTRATIVO
    protected $nombreTitular;	//GUARDA EL NOMBRE DEL TITULAR DE LA DEPENDENCIA
    protected $cargoTitular;	//GUARDA EL  CARGO DEL TITULAR DE LA DEPENDENCIA
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
    public function getRao_id()
    {
        return $this->rao_id->getVal();
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
    public function getAnioReintegro()
    {
        return $this->anioReintegro->getVal();
    }
    public function getOficio()
    {
        return $this->oficio->getVal();
    }
    public function getArchivoOficio()
    {
        return $this->archivoOficio->getVal();
    }
    public function getFechaReintegro()
    {
        return $this->fechaReintegro->getVal();
    }
    public function getTdf_id()
    {
        return $this->tdf_id->getVal();
    }
    public function getDocumentoPago()
    {
        return $this->documentoPago->getVal();
    }
    public function getArchivoDocumentoPago()
    {
        return $this->archivoDocumentoPago->getVal();
    }
    public function getImporte()
    {
        return $this->importe->getVal();
    }
    public function getEra_id()
    {
        return $this->era_id->getVal();
    }
    public function getFechaElaboracion()
    {
        return $this->fechaElaboracion->getVal();
    }
    public function getFechaAutorizacion()
    {
        return $this->fechaAutorizacion->getVal();
    }
    public function getFechaEnvio()
    {
        return $this->fechaEnvio->getVal();
    }
    public function getFechaAplicacion()
    {
        return $this->fechaAplicacion->getVal();
    }
    public function getNombreAdministrativo()
    {
        return $this->nombreAdministrativo->getVal();
    }
    public function getCargoAdministrativo()
    {
        return $this->cargoAdministrativo->getVal();
    }
    public function getNombreTitular()
    {
        return $this->nombreTitular->getVal();
    }
    public function getCargoTitular()
    {
        return $this->cargoTitular->getVal();
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
    public function setRao_id($val)
    {
        $this->rao_id->setVal($val);
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
    public function setAnioReintegro($val)
    {
        $this->anioReintegro->setVal($val);
    }
    public function setOficio($val)
    {
        $this->oficio->setVal($val);
    }
    public function setArchivoOficio($val)
    {
        $this->archivoOficio->setVal($val);
    }
    public function setFechaReintegro($val)
    {
        $this->fechaReintegro->setVal($val);
    }
    public function setTdf_id($val)
    {
        $this->tdf_id->setVal($val);
    }
    public function setDocumentoPago($val)
    {
        $this->documentoPago->setVal($val);
    }
    public function setArchivoDocumentoPago($val)
    {
        $this->archivoDocumentoPago->setVal($val);
    }
    public function setImporte($val)
    {
        $this->importe->setVal($val);
    }
    public function setEra_id($val)
    {
        $this->era_id->setVal($val);
    }
    public function setFechaElaboracion($val)
    {
        $this->fechaElaboracion->setVal($val);
    }
    public function setFechaAutorizacion($val)
    {
        $this->fechaAutorizacion->setVal($val);
    }
    public function setFechaEnvio($val)
    {
        $this->fechaEnvio->setVal($val);
    }
    public function setFechaAplicacion($val)
    {
        $this->fechaAplicacion->setVal($val);
    }
    public function setNombreAdministrativo($val)
    {
        $this->nombreAdministrativo->setVal($val);
    }
    public function setCargoAdministrativo($val)
    {
        $this->cargoAdministrativo->setVal($val);
    }
    public function setNombreTitular($val)
    {
        $this->nombreTitular->setVal($val);
    }
    public function setCargoTitular($val)
    {
        $this->cargoTitular->setVal($val);
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
    public function staRao_id($val)
    {
        $this->rao_id->staVal($val);
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
    public function staAnioReintegro($val)
    {
        $this->anioReintegro->staVal($val);
    }
    public function staOficio($val)
    {
        $this->oficio->staVal($val);
    }
    public function staArchivoOficio($val)
    {
        $this->archivoOficio->staVal($val);
    }
    public function staFechaReintegro($val)
    {
        $this->fechaReintegro->staVal($val);
    }
    public function staTdf_id($val)
    {
        $this->tdf_id->staVal($val);
    }
    public function staDocumentoPago($val)
    {
        $this->documentoPago->staVal($val);
    }
    public function staArchivoDocumentoPago($val)
    {
        $this->archivoDocumentoPago->staVal($val);
    }
    public function staImporte($val)
    {
        $this->importe->staVal($val);
    }
    public function staEra_id($val)
    {
        $this->era_id->staVal($val);
    }
    public function staFechaElaboracion($val)
    {
        $this->fechaElaboracion->staVal($val);
    }
    public function staFechaAutorizacion($val)
    {
        $this->fechaAutorizacion->staVal($val);
    }
    public function staFechaEnvio($val)
    {
        $this->fechaEnvio->staVal($val);
    }
    public function staFechaAplicacion($val)
    {
        $this->fechaAplicacion->staVal($val);
    }
    public function staNombreAdministrativo($val)
    {
        $this->nombreAdministrativo->staVal($val);
    }
    public function staCargoAdministrativo($val)
    {
        $this->cargoAdministrativo->staVal($val);
    }
    public function staNombreTitular($val)
    {
        $this->nombreTitular->staVal($val);
    }
    public function staCargoTitular($val)
    {
        $this->cargoTitular->staVal($val);
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

        parent::__construct(['tabla' => 'scp_ReintegroAnticipoObra']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'ur_id');
        $this->rao_id = new Atributo('numero', 'NO', 'PRI', 'rao_id');
        $this->ue_id = new Atributo('numero', 'YES', '', 'ue_id');
        $this->epg_id = new Atributo('numero', 'NO', '', 'epg_id');
        $this->ecf_id = new Atributo('numero', 'NO', '', 'ecf_id');
        $this->anioReintegro = new Atributo('numero', 'NO', '', 'rao_anioReintegro');
        $this->oficio = new Atributo('cadena', 'NO', '', 'rao_oficio');
        $this->archivoOficio = new Atributo('cadena', 'YES', '', 'rao_archivoOficio');
        $this->fechaReintegro = new Atributo('fecha', 'NO', '', 'rao_fechaReintegro');
        $this->tdf_id = new Atributo('cadena', 'NO', 'MUL', 'tdf_id');
        $this->documentoPago = new Atributo('cadena', 'NO', '', 'rao_documentoPago');
        $this->archivoDocumentoPago = new Atributo('cadena', 'YES', '', 'rao_archivoDocumentoPago');
        $this->importe = new Atributo('numero', 'NO', '', 'rao_importe');
        $this->era_id = new Atributo('numero', 'NO', 'MUL', 'era_id');
        $this->fechaElaboracion = new Atributo('fecha', 'NO', '', 'rao_fechaElaboracion');
        $this->fechaAutorizacion = new Atributo('fecha', 'YES', '', 'rao_fechaAutorizacion');
        $this->fechaEnvio = new Atributo('fecha', 'YES', '', 'rao_fechaEnvio');
        $this->fechaAplicacion = new Atributo('fecha', 'YES', '', 'rao_fechaAplicacion');
        $this->nombreAdministrativo = new Atributo('cadena', 'NO', '', 'rao_nombreAdministrativo');
        $this->cargoAdministrativo = new Atributo('cadena', 'NO', '', 'rao_cargoAdministrativo');
        $this->nombreTitular = new Atributo('cadena', 'NO', '', 'rao_nombreTitular');
        $this->cargoTitular = new Atributo('cadena', 'NO', '', 'rao_cargoTitular');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object)func_get_arg(0) : null) : null);

        $rao_id = (!isset($prm->rao_id)) ? '' : $prm->rao_id;
        $gpo_id = (!isset($prm->gpo_id)) ? '' : $prm->gpo_id;
        $ur_id = (!isset($prm->ur_id)) ? '' : $prm->ur_id;
        if ($rao_id != '') {
            $this->setRao_id($rao_id);
            $this->setGpo_id($gpo_id);
            $this->setUr_id($ur_id);
            $this->select();
        }


    }


    /******************************************************

        FUNCIONES ESTÁTICAS

    ******************************************************/

    public static function abc($p)
    {
        $rao = new ReintegroAnticipoObra();
        if (isset($p->gpo_id)) {
            $rao->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $rao->setUr_id($p->ur_id);
        }
        if (isset($p->rao_id)) {
            $rao->setRao_id($p->rao_id);
        }
        if (isset($p->ue_id)) {
            $rao->setUe_id($p->ue_id);
        }
        if (isset($p->epg_id)) {
            $rao->setEpg_id($p->epg_id);
        }
        if (isset($p->ecf_id)) {
            $rao->setEcf_id($p->ecf_id);
        }
        if (isset($p->anioReintegro)) {
            $rao->setAnioReintegro($p->anioReintegro);
        }
        if (isset($p->oficio)) {
            $rao->setOficio($p->oficio);
        }
        if (isset($p->archivoOficio)) {
            $rao->setArchivoOficio($p->archivoOficio);
        }
        if (isset($p->fechaReintegro)) {
            $rao->setFechaReintegro($p->fechaReintegro);
        }
        if (isset($p->tdf_id)) {
            $rao->setTdf_id($p->tdf_id);
        }
        if (isset($p->documentoPago)) {
            $rao->setDocumentoPago($p->documentoPago);
        }
        if (isset($p->archivoDocumentoPago)) {
            $rao->setArchivoDocumentoPago($p->archivoDocumentoPago);
        }
        if (isset($p->importe)) {
            $rao->setImporte($p->importe);
        }
        if (isset($p->era_id)) {
            $rao->setEra_id($p->era_id);
        }
        if (isset($p->fechaElaboracion)) {
            $rao->setFechaElaboracion($p->fechaElaboracion);
        }
        if (isset($p->fechaAutorizacion)) {
            $rao->setFechaAutorizacion($p->fechaAutorizacion);
        }
        if (isset($p->fechaEnvio)) {
            $rao->setFechaEnvio($p->fechaEnvio);
        }
        if (isset($p->fechaAplicacion)) {
            $rao->setFechaAplicacion($p->fechaAplicacion);
        }
        if (isset($p->nombreAdministrativo)) {
            $rao->setNombreAdministrativo($p->nombreAdministrativo);
        }
        if (isset($p->cargoAdministrativo)) {
            $rao->setCargoAdministrativo($p->cargoAdministrativo);
        }
        if (isset($p->nombreTitular)) {
            $rao->setNombreTitular($p->nombreTitular);
        }
        if (isset($p->cargoTitular)) {
            $rao->setCargoTitular($p->cargoTitular);
        }
        if (isset($p->usr_id)) {
            $rao->setUsr_id($p->usr_id);
        }
        switch ($p->abc) {
            case 'a': $rao->insert();
                return $rao->getUltimoId();
                break;
            case 'b': $rao->delete();
                break;
            case 'c': $rao->update();
                break;
        }
    }

}
