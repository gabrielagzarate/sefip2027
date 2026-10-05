<?php

require_once dirname(__FILE__) . "/Entidad.php";
require_once dirname(__FILE__) . "/UnidadResponsableFirmas.php";
require_once dirname(__FILE__) . "/AuxiliarUnidadResponsable.php";

class UnidadResponsable extends Entidad
{

    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $gpo_id;           //cat_Grupo - Llave foranea Clave identificadora del Grupo
    protected $ur_id;            //Clave identificadora de la UR
    protected $sad_id;           // Identificador del Sector Administrativo Depende de cat_SectorAdministrativo - Llave Foranea
    protected $spd_id;           //Identificador del Sector Productivo Depende de cat_SectorProductivo - Llave Foranea
    protected $eur_id;           //cat_EstadoUR - Llave foranea Clave identificadora del estado en el que se encuentra la UR
    protected $eco_id;           //enc_Conac - Llave Foranea
    protected $sei_id;           //
    protected $sts_id;           //
    protected $clave;            //concatenacion del grupo y la ur TRIGGER AFTER CONCAT( gpo_id, ur_id )
    protected $nombre;           //Nombre de la UR
    protected $compraDirecta;    //1 - solo compras directas 2 - compras directas y consolidadas
    protected $division;         //casos especiales
    protected $ueContable;       //UE responsable de la contabilidad
    protected $sector;           //C - Central P - Paraestatal
    protected $rh;               //controlado por Recursos Humanos 1 - Si 2 - No
    protected $poa;              //Utilizara costeo 1 - Si 2 - No
    protected $cadenaProductiva; //Modalidad de Pago Cadena Productiva 1 - Es de cadena produsctiva 2 - NO es de cadena productiva
    protected $tipoFirma;        //
    protected $tipoCierre;       //Especifica el tipo de Cierre de Costeo de la Unidad Responsable N = Normal por Criterio de Gasto G= Por Gran fuente Estatal/Federal
    protected $actualizarPOA;    //opcion de egresos para ajustar aunque la UR no este cerrada N = Normal A = Ajustada
    protected $usr_id;           //Clave identificadora del usuario que inserto el registro
    protected $ur_estadoCosteo;  // estado de la unidad responsable 1 = Abierta; 2= Cerrado
    protected $ur_cierreForzado; //Cierre forzado = 2 cuando la diferencia entre techo financiero y Presupuesto no es cero.
    public $sql;
    public $params;
    /*tablas auxiliares particionadas verticalmente de UnidadResponsable*/
    protected $firmas;
    protected $auxiliar;

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
    public function getSad_id()
    {
        return $this->sad_id->getVal();
    }
    public function getSpd_id()
    {
        return $this->spd_id->getVal();
    }
    public function getEur_id()
    {
        return $this->eur_id->getVal();
    }
    public function getEco_id()
    {
        return $this->eco_id->getVal();
    }
    public function getSei_id()
    {
        return $this->sei_id->getVal();
    }
    public function getSts_id()
    {
        return $this->sts_id->getVal();
    }
    public function getClave()
    {
        return $this->clave->getVal();
    }
    public function getNombre()
    {
        return $this->nombre->getVal();
    }
    public function getCompraDirecta()
    {
        return $this->compraDirecta->getVal();
    }
    public function getDivision()
    {
        return $this->division->getVal();
    }
    public function getUeContable()
    {
        return $this->ueContable->getVal();
    }
    public function getSector()
    {
        return $this->sector->getVal();
    }
    public function getRh()
    {
        return $this->rh->getVal();
    }
    public function getPoa()
    {
        return $this->poa->getVal();
    }
    public function getCadenaProductiva()
    {
        return $this->cadenaProductiva->getVal();
    }
    public function getTipoFirma()
    {
        return $this->tipoFirma->getVal();
    }
    public function getTipoCierre()
    {
        return $this->tipoCierre->getVal();
    }
    public function getActualizarPOA()
    {
        return $this->actualizarPOA->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    public function getUr_estadoCosteo()
    {
        return $this->ur_estadoCosteo->getVal();
    }
    public function getUr_cierreForzado()
    {
        return $this->ur_cierreForzado->getVal();
    }

    //DEL ATRIBUTO AUXILIAR, SE GENERAN LOS MÉTODOS PARA QUE EL USO DE UNIDADRESPONSABLE, SEA TRANSPARENTE AL USUARIO.
    public function getTituloTitular()
    {
        return $this->auxiliar->getTituloTitular();
    }
    public function getNombreTitular()
    {
        return $this->auxiliar->getNombreTitular();
    }
    public function getCargoTitular()
    {
        return $this->auxiliar->getCargoTitular();
    }
    public function getTituloAdministrativo()
    {
        return $this->auxiliar->getTituloAdministrativo();
    }
    public function getNombreAdministrativo()
    {
        return $this->auxiliar->getNombreAdministrativo();
    }
    public function getCargoAdministrativo()
    {
        return $this->auxiliar->getCargoAdministrativo();
    }
    public function getTituloPlaneacion()
    {
        return $this->auxiliar->getTituloPlaneacion();
    }
    public function getNombrePlaneacion()
    {
        return $this->auxiliar->getNombrePlaneacion();
    }
    public function getCargoPlaneacion()
    {
        return $this->auxiliar->getCargoPlaneacion();
    }
    public function getTituloEvaluacion()
    {
        return $this->auxiliar->getTituloEvaluacion();
    }
    public function getNombreEvaluacion()
    {
        return $this->auxiliar->nombreEvaluacion->getVal();
    }
    public function getCargoEvaluacion()
    {
        return $this->auxiliar->cargoEvaluacion->getVal();
    }
    public function getNombreFinancieros()
    {
        return $this->auxiliar->getNombreFinancieros();
    }
    public function getCargoFinancieros()
    {
        return $this->auxiliar->getCargoFinancieros();
    }
    public function getInicioPeriodicoOficial()
    {
        return $this->auxiliar->getInicioPeriodicoOficial();
    }
    public function getInicioFecha()
    {
        return $this->auxiliar->getInicioFecha();
    }
    public function getInicioDecretoFecha()
    {
        return $this->auxiliar->getInicioDecretoFecha();
    }
    public function getModificacionPeriodico()
    {
        return $this->auxiliar->getModificacionPeriodico();
    }
    public function getModificacionFecha()
    {
        return $this->auxiliar->getModificacionFecha();
    }
    public function getInternoPeriodico()
    {
        return $this->auxiliar->getInternoPeriodico();
    }
    public function getInternoFecha()
    {
        return $this->auxiliar->getInternoFecha();
    }
    public function getInternoArchivo()
    {
        return $this->auxiliar->getInternoArchivo();
    }
    public function getPlaneacionFecha()
    {
        return $this->auxiliar->getPlaneacionFecha();
    }
    public function getOrganigramaArchivo()
    {
        return $this->auxiliar->getOrganigramaArchivo();
    }
    public function getValidacionFecha()
    {
        return $this->auxiliar->getValidacionFecha();
    }
    public function getOrganigramaFecha()
    {
        return $this->auxiliar->getOrganigramaFecha();
    }
    public function getObjetivoGeneral()
    {
        return $this->auxiliar->getObjetivoGeneral();
    }
    public function getObjetivoEstrategico()
    {
        return $this->auxiliar->getObjetivoEstrategico();
    }
    public function getProductoInstitucional()
    {
        return $this->auxiliar->getProductoInstitucional();
    }
    public function getMision()
    {
        return $this->auxiliar->getMision();
    }
    public function getVision()
    {
        return $this->auxiliar->getVision();
    }
    public function getDiagnostico()
    {
        return $this->auxiliar->getDiagnostico();
    }
    public function getSiglas()
    {
        return $this->auxiliar->getSiglas();
    }
    public function getDecretoArchivo()
    {
        return $this->auxiliar->getDecretoArchivo();
    }
    public function getFirmaNombramientoArchivo()
    {
        return $this->auxiliar->getFirmaNombramientoArchivo();
    }
    public function getFirmaIdentificacionArchivo()
    {
        return $this->auxiliar->getFirmaIdentificacionArchivo();
    }
    public function getFirmaLegalizacionArchivo()
    {
        return $this->auxiliar->getFirmaLegalizacionArchivo();
    }
    public function getFirmaDelegartorioArchivo()
    {
        return $this->auxiliar->getFirmaDelegartorioArchivo();
    }
    public function getCorreoElectronicoTitular()
    {
        return $this->auxiliar->getCorreoElectronicoTitular();
    }
    public function getTelefonoTitular()
    {
        return $this->auxiliar->getTelefonoTitular();
    }
    public function getCelularTitular()
    {
        return $this->auxiliar->getCelularTitular();
    }
    public function getExtensionTitular()
    {
        return $this->auxiliar->getExtensionTitular();
    }
    public function getCorreoElectronicoAdministrativo()
    {
        return $this->auxiliar->getCorreoElectronicoAdministrativo();
    }
    public function getTelefonoAdministrativo()
    {
        return $this->auxiliar->getTelefonoAdministrativo();
    }
    public function getCelularAdministrativo()
    {
        return $this->auxiliar->getCelularAdministrativo();
    }
    public function getExtensionAdministrativo()
    {
        return $this->auxiliar->getExtensionAdministrativo();
    }
    public function getCorreoElectronicoPlaneacion()
    {
        return $this->auxiliar->getCorreoElectronicoPlaneacion();
    }
    public function getTelefonoPlaneacion()
    {
        return $this->auxiliar->getTelefonoPlaneacion();
    }
    public function getCelularPlaneacion()
    {
        return $this->auxiliar->getCelularPlaneacion();
    }
    public function getExtensionPlaneacion()
    {
        return $this->auxiliar->getExtensionPlaneacion();
    }
    public function getCorreoElectronicoEvaluacion()
    {
        return $this->auxiliar->correoElectronicoEvaluacion->getVal();
    }
    public function getTelefonoEvaluacion()
    {
        return $this->auxiliar->telefonoEvaluacion->getVal();
    }
    public function getCelularEvaluacion()
    {
        return $this->auxiliar->getCelularEvaluacion();
    }
    public function getExtensionEvaluacion()
    {
        return $this->auxiliar->getExtensionEvaluacion();
    }
    public function getDomicilio()
    {
        return $this->auxiliar->getDomicilio();
    }
    public function getTelefono()
    {
        return $this->auxiliar->getTelefono();
    }
    public function getPaginaWeb()
    {
        return $this->auxiliar->getPaginaWeb();
    }
    public function getCorreoElectronicoContacto()
    {
        return $this->auxiliar->getCorreoElectronicoContacto();
    }
    public function getMun_id()
    {
        return $this->auxiliar->getMun_id();
    }
    public function getCodigoPostal()
    {
        return $this->auxiliar->getCodigoPostal();
    }

    //Del atributo firmas, se generan los métodos para que el uso de UnidadResponsable, sea transparente al usuario.
    public function getNombramientoTitular()
    {
        return $this->firmas->getNombramientoTitular();
    }
    public function getFirmaTitular()
    {
        return $this->firmas->getFirmaTitular();
    }
    public function getUrf_identificacionTitular()
    {
        return $this->firmas->getUrf_identificacionTitular();
    }
    public function getNombramientoDelegatorioTitular()
    {
        return $this->firmas->getNombramientoTitular();
    }
    public function getUrf_identificacionDelegatorioTitular()
    {
        return $this->firmas->getUrf_identificacionDelegatorioTitular();
    }
    public function getFirmaDelegatorioTitular()
    {
        return $this->firmas->getFirmaDelegatorioTitular();
    }
    public function getAcuerdoDelegatorioTitular()
    {
        return $this->firmas->getAcuerdoDelegatorioTitular();
    }
    public function getNombramientoAdministrativo()
    {
        return $this->firmas->getNombramientoAdministrativo();
    }
    public function getUrf_identificacionAdministrativo()
    {
        return $this->firmas->getUrf_identificacionAdministrativo();
    }
    public function getFirmaAdministrativo()
    {
        return $this->firmas->getFirmaAdministrativo();
    }
    public function getNombramientoDelegatorioAdministrativo()
    {
        return $this->firmas->getNombramientoDelegatorioAdministrativo();
    }
    public function getUrf_identificacionDelegatorioAdministrativo()
    {
        return $this->firmas->getUrf_identificacionDelegatorioAdministrativo();
    }
    public function getFirmaDelegatorioAdministrativo()
    {
        return $this->firmas->getFirmaDelegatorioAdministrativo();
    }
    public function getAcuerdoDelegatorioAdministrativo()
    {
        return $this->firmas->getAcuerdoDelegatorioAdministrativo();
    }
    public function getNombramientoPlaneacion()
    {
        return $this->firmas->getNombramientoPlaneacion();
    }
    public function getUrf_identificacionPlaneacion()
    {
        return $this->firmas->getUrf_identificacionPlaneacion();
    }
    public function getFirmaPlaneacion()
    {
        return $this->firmas->getfirmaPlaneacion();
    }
    public function getNombramientoEvaluacion()
    {
        return $this->firmas->nombramientoEvaluacion->getVal();
    }
    public function getUrf_identificacionEvaluacion()
    {
        return $this->firmas->urf_identificacionEvaluacion->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setGpo_id($val)
    {
        $this->gpo_id->setVal($val);
        $this->firmas->setGpo_id($val);
        $this->auxiliar->setGpo_id($val);
    }
    public function setUr_id($val)
    {
        $this->ur_id->setVal($val);
        $this->firmas->setUr_id($val);
        $this->auxiliar->setUr_id($val);
    }
    public function setSad_id($val)
    {
        $this->sad_id->setVal($val);
    }
    public function setSpd_id($val)
    {
        $this->spd_id->setVal($val);
    }
    public function setEur_id($val)
    {
        $this->eur_id->setVal($val);
    }
    public function setEco_id($val)
    {
        $this->eco_id->setVal($val);
    }
    public function setSei_id($val)
    {
        $this->sei_id->setVal($val);
    }
    public function setSts_id($val)
    {
        $this->sts_id->setVal($val);
    }
    public function setClave($val)
    {
        $this->clave->setVal($val);
    }
    public function setNombre($val)
    {
        $this->nombre->setVal($val);
    }
    public function setCompraDirecta($val)
    {
        $this->compraDirecta->setVal($val);
    }
    public function setDivision($val)
    {
        $this->division->setVal($val);
    }
    public function setUeContable($val)
    {
        $this->ueContable->setVal($val);
    }
    public function setSector($val)
    {
        $this->sector->setVal($val);
    }
    public function setRh($val)
    {
        $this->rh->setVal($val);
    }
    public function setPoa($val)
    {
        $this->poa->setVal($val);
    }
    public function setCadenaProductiva($val)
    {
        $this->cadenaProductiva->setVal($val);
    }
    public function setTipoFirma($val)
    {
        $this->tipoFirma->setVal($val);
    }
    public function setTipoCierre($val)
    {
        $this->tipoCierre->setVal($val);
    }
    public function setActualizarPOA($val)
    {
        $this->actualizarPOA->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    public function setEstadoCosteo($val)
    {
        $this->ur_estadoCosteo->setVal($val);
    }
    public function setCierreForzado($val)
    {
        $this->ur_cierreForzado->setVal($val);
    }

    //Del atributo auxiliar, se generan los métodos para que el uso de UnidadResponsable, sea transparente al usuario.
    public function setTituloTitular($val)
    {
        $this->auxiliar->setTituloTitular($val);
    }
    public function setNombreTitular($val)
    {
        $this->auxiliar->setNombreTitular($val);
    }
    public function setCargoTitular($val)
    {
        $this->auxiliar->setCargoTitular($val);
    }
    public function setTituloAdministrativo($val)
    {
        $this->auxiliar->setTituloAdministrativo($val);
    }
    public function setNombreAdministrativo($val)
    {
        $this->auxiliar->setNombreAdministrativo($val);
    }
    public function setCargoAdministrativo($val)
    {
        $this->auxiliar->setCargoAdministrativo($val);
    }
    public function setTituloPlaneacion($val)
    {
        $this->auxiliar->setTituloPlaneacion($val);
    }
    public function setNombrePlaneacion($val)
    {
        $this->auxiliar->setNombrePlaneacion($val);
    }
    public function setCargoPlaneacion($val)
    {
        $this->auxiliar->setCargoPlaneacion($val);
    }
    public function setTituloEvaluacion($val)
    {
        $this->auxiliar->setTituloEvaluacion($val);
    }
    public function setNombreEvaluacion($val)
    {
        $this->auxiliar->nombreEvaluacion->setVal($val);
    }
    public function setCargoEvaluacion($val)
    {
        $this->auxiliar->cargoEvaluacion->setVal($val);
    }
    public function setNombreFinancieros($val)
    {
        $this->auxiliar->setNombreFinancieros($val);
    }
    public function setCargoFinancieros($val)
    {
        $this->auxiliar->setCargoFinancieros($val);
    }
    public function setInicioPeriodicoOficial($val)
    {
        $this->auxiliar->setInicioPeriodicoOficial($val);
    }
    public function setInicioFecha($val)
    {
        $this->auxiliar->setInicioFecha($val);
    }
    public function setInicioDecretoFecha($val)
    {
        $this->auxiliar->setInicioDecretoFecha($val);
    }
    public function setModificacionPeriodico($val)
    {
        $this->auxiliar->setModificacionPeriodico($val);
    }
    public function setModificacionFecha($val)
    {
        $this->auxiliar->setModificacionFecha($val);
    }
    public function setInternoPeriodico($val)
    {
        $this->auxiliar->setInternoPeriodico($val);
    }
    public function setInternoFecha($val)
    {
        $this->auxiliar->setInternoFecha($val);
    }
    public function setInternoArchivo($val)
    {
        $this->auxiliar->setInternoArchivo($val);
    }
    public function setPlaneacionFecha($val)
    {
        $this->auxiliar->setPlaneacionFecha($val);
    }
    public function setOrganigramaArchivo($val)
    {
        $this->auxiliar->setOrganigramaArchivo($val);
    }
    public function setValidacionFecha($val)
    {
        $this->auxiliar->setValidacionFecha($val);
    }
    public function setOrganigramaFecha($val)
    {
        $this->auxiliar->setOrganigramaFecha($val);
    }
    public function setObjetivoGeneral($val)
    {
        $this->auxiliar->setObjetivoGeneral($val);
    }
    public function setObjetivoEstrategico($val)
    {
        $this->auxiliar->setObjetivoEstrategico($val);
    }
    public function setProductoInstitucional($val)
    {
        $this->auxiliar->setProductoInstitucional($val);
    }
    public function setMision($val)
    {
        $this->auxiliar->setMision($val);
    }
    public function setVision($val)
    {
        $this->auxiliar->setVision($val);
    }
    public function setDiagnostico($val)
    {
        $this->auxiliar->setDiagnostico($val);
    }
    public function setSiglas($val)
    {
        $this->auxiliar->setSiglas($val);
    }
    public function setDecretoArchivo($val)
    {
        $this->auxiliar->setDecretoArchivo($val);
    }
    public function setFirmaNombramientoArchivo($val)
    {
        $this->auxiliar->setFirmaNombramientoArchivo($val);
    }
    public function setFirmaIdentificacionArchivo($val)
    {
        $this->auxiliar->setFirmaIdentificacionArchivo($val);
    }
    public function setFirmaLegalizacionArchivo($val)
    {
        $this->auxiliar->setFirmaLegalizacionArchivo($val);
    }
    public function setFirmaDelegartorioArchivo($val)
    {
        $this->auxiliar->setFirmaDelegartorioArchivo($val);
    }
    public function setCorreoElectronicoTitular($val)
    {
        $this->auxiliar->setCorreoElectronicoTitular($val);
    }
    public function setTelefonoTitular($val)
    {
        $this->auxiliar->setTelefonoTitular($val);
    }
    public function setCelularTitular($val)
    {
        $this->auxiliar->setCelularTitular($val);
    }
    public function setExtensionTitular($val)
    {
        $this->auxiliar->setExtensionTitular($val);
    }
    public function setCorreoElectronicoAdministrativo($val)
    {
        $this->auxiliar->setCorreoElectronicoAdministrativo($val);
    }
    public function setTelefonoAdministrativo($val)
    {
        $this->auxiliar->setTelefonoAdministrativo($val);
    }
    public function setCelularAdministrativo($val)
    {
        $this->auxiliar->setCelularAdministrativo($val);
    }
    public function setExtensionAdministrativo($val)
    {
        $this->auxiliar->setExtensionAdministrativo($val);
    }
    public function setCorreoElectronicoPlaneacion($val)
    {
        $this->auxiliar->setCorreoElectronicoPlaneacion($val);
    }
    public function setTelefonoPlaneacion($val)
    {
        $this->auxiliar->setTelefonoPlaneacion($val);
    }
    public function setCelularPlaneacion($val)
    {
        $this->auxiliar->setCelularPlaneacion($val);
    }
    public function setExtensionPlaneacion($val)
    {
        $this->auxiliar->setExtensionPlaneacion($val);
    }
    public function setDomicilio($val)
    {
        $this->auxiliar->setDomicilio($val);
    }
    public function setTelefono($val)
    {
        $this->auxiliar->setTelefono($val);
    }
    public function setCorreoElectronicoEvaluacion($val)
    {
        $this->auxiliar->correoElectronicoEvaluacion->setVal($val);
    }
    public function setTelefonoEvaluacion($val)
    {
        $this->auxiliar->telefonoEvaluacion->setVal($val);
    }
    public function setCelularEvaluacion($val)
    {
        $this->auxiliar->celularEvaluacion->setVal($val);
    }
    public function setExtensionEvaluacion($val)
    {
        $this->auxiliar->setExtensionEvaluacion($val);
    }
    public function setPaginaWeb($val)
    {
        $this->auxiliar->setPaginaWeb($val);
    }
    public function setCorreoElectronicoContacto($val)
    {
        $this->auxiliar->setCorreoElectronicoContacto($val);
    }
    public function setMun_id($val)
    {
        $this->auxiliar->setMun_id($val);
    }
    public function setCodigoPostal($val)
    {
        $this->auxiliar->setCodigoPostal($val);
    }

    //DEL ATRIBUTO FIRMAS, SE GENERAN LOS MÉTODOS PARA QUE EL USO DE UNIDADRESPONSABLE, SEA TRANSPARENTE AL USUARIO.
    public function setNombramientoTitular($val)
    {
        $this->firmas->setNombramientoTitular($val);
    }
    public function setFirmaTitular($val)
    {
        $this->firmas->setFirmaTitular($val);
    }
    public function setUrf_identificacionTitular($val)
    {
        $this->firmas->setUrf_identificacionTitular($val);
    }
    public function setNombramientoDelegatorioTitular($val)
    {
        $this->firmas->setNombramientoTitular($val);
    }
    public function setUrf_identificacionDelegatorioTitular($val)
    {
        $this->firmas->setUrf_identificacionDelegatorioTitular($val);
    }
    public function setFirmaDelegatorioTitular($val)
    {
        $this->firmas->setFirmaDelegatorioTitular($val);
    }
    public function setAcuerdoDelegatorioTitular($val)
    {
        $this->firmas->setAcuerdoDelegatorioTitular($val);
    }
    public function setNombramientoAdministrativo($val)
    {
        $this->firmas->setNombramientoAdministrativo($val);
    }
    public function setUrf_identificacionAdministrativo($val)
    {
        $this->firmas->setUrf_identificacionAdministrativo($val);
    }
    public function setFirmaAdministrativo($val)
    {
        $this->firmas->setFirmaAdministrativo($val);
    }
    public function setNombramientoDelegatorioAdministrativo($val)
    {
        $this->firmas->setNombramientoDelegatorioAdministrativo($val);
    }
    public function setUrf_identificacionDelegatorioAdministrativo($val)
    {
        $this->firmas->setUrf_identificacionDelegatorioAdministrativo($val);
    }
    public function setFirmaDelegatorioAdministrativo($val)
    {
        $this->firmas->setFirmaDelegatorioAdministrativo($val);
    }
    public function setAcuerdoDelegatorioAdministrativo($val)
    {
        $this->firmas->setAcuerdoDelegatorioAdministrativo($val);
    }
    public function setNombramientoPlaneacion($val)
    {
        $this->firmas->setNombramientoPlaneacion($val);
    }
    public function setUrf_identificacionPlaneacion($val)
    {
        $this->firmas->setUrf_identificacionPlaneacion($val);
    }
    public function setFirmaPlaneacion($val)
    {
        $this->firmas->setfirmaPlaneacion($val);
    }
    public function setUrf_identificacionEvaluacion($val)
    {
        $this->firmas->urf_identificacionEvaluacion->setVal($val);
    }
    public function setFirmaEvaluacion($val)
    {
        $this->firmas->firmaEvaluacion->setVal($val);
    }
    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/

    public function staGpo_id($val)
    {
        $this->gpo_id->staVal($val);
        $this->firmas->staGpo_id($val);
        $this->auxiliar->staGpo_id($val);
    }
    public function staUr_id($val)
    {
        $this->ur_id->staVal($val);
        $this->firmas->staUr_id($val);
        $this->auxiliar->staUr_id($val);
    }
    public function staSad_id($val)
    {
        $this->sad_id->staVal($val);
    }
    public function staSpd_id($val)
    {
        $this->spd_id->staVal($val);
    }
    public function staEur_id($val)
    {
        $this->eur_id->staVal($val);
    }
    public function staEco_id($val)
    {
        $this->eco_id->staVal($val);
    }
    public function staSei_id($val)
    {
        $this->sei_id->staVal($val);
    }
    public function staSts_id($val)
    {
        $this->sts_id->staVal($val);
    }
    public function staClave($val)
    {
        $this->clave->staVal($val);
    }
    public function staNombre($val)
    {
        $this->nombre->staVal($val);
    }
    public function staCompraDirecta($val)
    {
        $this->compraDirecta->staVal($val);
    }
    public function staDivision($val)
    {
        $this->division->staVal($val);
    }
    public function staUeContable($val)
    {
        $this->ueContable->staVal($val);
    }
    public function staSector($val)
    {
        $this->sector->staVal($val);
    }
    public function staRh($val)
    {
        $this->rh->staVal($val);
    }
    public function staPoa($val)
    {
        $this->poa->staVal($val);
    }
    public function staCadenaProductiva($val)
    {
        $this->cadenaProductiva->staVal($val);
    }
    public function staTipoFirma($val)
    {
        $this->tipoFirma->staVal($val);
    }
    public function staTipoCierre($val)
    {
        $this->tipoCierre->staVal($val);
    }
    public function staActualizarPOA($val)
    {
        $this->actualizarPOA->staVal($val);
    }
    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }

    //Del atributo auxiliar, se generan los métodos para que el uso de UnidadResponsable, sea transparente al usuario.
    public function staTituloTitular($val)
    {
        $this->auxiliar->staTituloTitular($val);
    }
    public function staNombreTitular($val)
    {
        $this->auxiliar->staNombreTitular($val);
    }
    public function staCargoTitular($val)
    {
        $this->auxiliar->staCargoTitular($val);
    }
    public function staTituloAdministrativo($val)
    {
        $this->auxiliar->staTituloAdministrativo($val);
    }
    public function staNombreAdministrativo($val)
    {
        $this->auxiliar->staNombreAdministrativo($val);
    }
    public function staCargoAdministrativo($val)
    {
        $this->auxiliar->staCargoAdministrativo($val);
    }
    public function staTituloPlaneacion($val)
    {
        $this->auxiliar->staTituloPlaneacion($val);
    }
    public function staNombrePlaneacion($val)
    {
        $this->auxiliar->staNombrePlaneacion($val);
    }
    public function staCargoPlaneacion($val)
    {
        $this->auxiliar->staCargoPlaneacion($val);
    }
    public function staTituloEvaluacion($val)
    {
        $this->auxiliar->staTituloEvaluacion($val);
    }
    public function staNombreEvaluacion($val)
    {
        $this->auxiliar->nombreEvaluacion->staVal($val);
    }
    public function staCargoEvaluacion($val)
    {
        $this->auxiliar->cargoEvaluacion->staVal($val);
    }
    public function staNombreFinancieros($val)
    {
        $this->auxiliar->staNombreFinancieros($val);
    }
    public function staCargoFinancieros($val)
    {
        $this->auxiliar->staCargoFinancieros($val);
    }
    public function staInicioPeriodicoOficial($val)
    {
        $this->auxiliar->staInicioPeriodicoOficial($val);
    }
    public function staInicioFecha($val)
    {
        $this->auxiliar->staInicioFecha($val);
    }
    public function staInicioDecretoFecha($val)
    {
        $this->auxiliar->staInicioDecretoFecha($val);
    }
    public function staModificacionPeriodico($val)
    {
        $this->auxiliar->staModificacionPeriodico($val);
    }
    public function staModificacionFecha($val)
    {
        $this->auxiliar->staModificacionFecha($val);
    }
    public function staInternoPeriodico($val)
    {
        $this->auxiliar->staInternoPeriodico($val);
    }
    public function staInternoFecha($val)
    {
        $this->auxiliar->staInternoFecha($val);
    }
    public function staInternoArchivo($val)
    {
        $this->auxiliar->staInternoArchivo($val);
    }
    public function staPlaneacionFecha($val)
    {
        $this->auxiliar->staPlaneacionFecha($val);
    }
    public function staOrganigramaArchivo($val)
    {
        $this->auxiliar->staOrganigramaArchivo($val);
    }
    public function staValidacionFecha($val)
    {
        $this->auxiliar->staValidacionFecha($val);
    }
    public function staOrganigramaFecha($val)
    {
        $this->auxiliar->staOrganigramaFecha($val);
    }
    public function staObjetivoGeneral($val)
    {
        $this->auxiliar->staObjetivoGeneral($val);
    }
    public function staObjetivoEstrategico($val)
    {
        $this->auxiliar->staObjetivoEstrategico($val);
    }
    public function staProductoInstitucional($val)
    {
        $this->auxiliar->staProductoInstitucional($val);
    }
    public function staMision($val)
    {
        $this->auxiliar->staMision($val);
    }
    public function staVision($val)
    {
        $this->auxiliar->staVision($val);
    }
    public function staDiagnostico($val)
    {
        $this->auxiliar->staDiagnostico($val);
    }
    public function staSiglas($val)
    {
        $this->auxiliar->staSiglas($val);
    }
    public function staDecretoArchivo($val)
    {
        $this->auxiliar->staDecretoArchivo($val);
    }
    public function staFirmaNombramientoArchivo($val)
    {
        $this->auxiliar->staFirmaNombramientoArchivo($val);
    }
    public function staFirmaIdentificacionArchivo($val)
    {
        $this->auxiliar->staFirmaIdentificacionArchivo($val);
    }
    public function staFirmaLegalizacionArchivo($val)
    {
        $this->auxiliar->staFirmaLegalizacionArchivo($val);
    }
    public function staFirmaDelegartorioArchivo($val)
    {
        $this->auxiliar->staFirmaDelegartorioArchivo($val);
    }
    public function staCorreoElectronicoTitular($val)
    {
        $this->auxiliar->staCorreoElectronicoTitular($val);
    }
    public function staTelefonoTitular($val)
    {
        $this->auxiliar->staTelefonoTitular($val);
    }
    public function staCelularTitular($val)
    {
        $this->auxiliar->staCelularTitular($val);
    }
    public function staExtensionTitular($val)
    {
        $this->auxiliar->staExtensionTitular($val);
    }
    public function staCorreoElectronicoAdministrativo($val)
    {
        $this->auxiliar->staCorreoElectronicoAdministrativo($val);
    }
    public function staTelefonoAdministrativo($val)
    {
        $this->auxiliar->staTelefonoAdministrativo($val);
    }
    public function staCelularAdministrativo($val)
    {
        $this->auxiliar->staCelularAdministrativo($val);
    }
    public function staExtensionAdministrativo($val)
    {
        $this->auxiliar->staExtensionAdministrativo($val);
    }
    public function staCorreoElectronicoPlaneacion($val)
    {
        $this->auxiliar->staCorreoElectronicoPlaneacion($val);
    }
    public function staTelefonoPlaneacion($val)
    {
        $this->auxiliar->staTelefonoPlaneacion($val);
    }
    public function staCelularPlaneacion($val)
    {
        $this->auxiliar->staCelularPlaneacion($val);
    }
    public function staExtensionPlaneacion($val)
    {
        $this->auxiliar->staExtensionPlaneacion($val);
    }
    public function staDomicilio($val)
    {
        $this->auxiliar->staDomicilio($val);
    }
    public function staTelefono($val)
    {
        $this->auxiliar->staTelefono($val);
    }
    public function staCorreoElectronicoEvaluacion($val)
    {
        $this->auxiliar->correoElectronicoEvaluacion->staVal($val);
    }
    public function staTelefonoEvaluacion($val)
    {
        $this->auxiliar->telefonoEvaluacion->staVal($val);
    }
    public function staCelularEvaluacion($val)
    {
        $this->auxiliar->staCelularEvaluacion($val);
    }
    public function staExtensionEvaluacion($val)
    {
        $this->auxiliar->staExtensionEvaluacion($val);
    }
    public function staPaginaWeb($val)
    {
        $this->auxiliar->staPaginaWeb($val);
    }
    public function staCorreoElectronicoContacto($val)
    {
        $this->auxiliar->staCorreoElectronicoContacto($val);
    }
    public function staMun_id($val)
    {
        $this->auxiliar->staMun_id($val);
    }
    public function staCodigoPostal($val)
    {
        $this->auxiliar->staCodigoPostal($val);
    }

    //Del atributo firmas, se generan los métodos para que el uso de UnidadResponsable, sea transparente al usuario.
    public function staNombramientoTitular($val)
    {
        $this->firmas->staNombramientoTitular($val);
    }
    public function staFirmaTitular($val)
    {
        $this->firmas->staFirmaTitular($val);
    }
    public function staUrf_identificacionTitular($val)
    {
        $this->firmas->staUrf_identificacionTitular($val);
    }
    public function staNombramientoDelegatorioTitular($val)
    {
        $this->firmas->staNombramientoTitular($val);
    }
    public function staUrf_identificacionDelegatorioTitular($val)
    {
        $this->firmas->staUrf_identificacionDelegatorioTitular($val);
    }
    public function staFirmaDelegatorioTitular($val)
    {
        $this->firmas->staFirmaDelegatorioTitular($val);
    }
    public function staAcuerdoDelegatorioTitular($val)
    {
        $this->firmas->staAcuerdoDelegatorioTitular($val);
    }
    public function staNombramientoAdministrativo($val)
    {
        $this->firmas->staNombramientoAdministrativo($val);
    }
    public function staUrf_identificacionAdministrativo($val)
    {
        $this->firmas->staUrf_identificacionAdministrativo($val);
    }
    public function staFirmaAdministrativo($val)
    {
        $this->firmas->staFirmaAdministrativo($val);
    }
    public function staNombramientoDelegatorioAdministrativo($val)
    {
        $this->firmas->staNombramientoDelegatorioAdministrativo($val);
    }
    public function staUrf_identificacionDelegatorioAdministrativo($val)
    {
        $this->firmas->staUrf_identificacionDelegatorioAdministrativo($val);
    }
    public function staFirmaDelegatorioAdministrativo($val)
    {
        $this->firmas->staFirmaDelegatorioAdministrativo($val);
    }
    public function staAcuerdgetUr_estadoCosteooDelegatorioAdministrativo($val)
    {
        $this->firmas->staAcuerdoDelegatorioAdministrativo($val);
    }
    public function staNombramientoPlaneacion($val)
    {
        $this->firmas->staNombramientoPlaneacion($val);
    }
    public function staUrf_identificacionPlaneacion($val)
    {
        $this->firmas->staUrf_identificacionPlaneacion($val);
    }
    public function staFirmaPlaneacion($val)
    {
        $this->firmas->stafirmaPlaneacion($val);
    }
    public function staUrf_identificacionEvaluacion($val)
    {
        $this->firmas->urf_identificacionEvaluacion->staVal($val);
    }
    public function staFirmaEvaluacion($val)
    {
        $this->firmas->firmaEvaluacion->staVal($val);
    }

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {
        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        parent::__construct(["tabla" => "cat_UnidadResponsable"]);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->gpo_id           = new Atributo("numero", "NO", "PRI", "gpo_id");
        $this->ur_id            = new Atributo("numero", "NO", "PRI", "ur_id");
        $this->eur_id           = new Atributo("numero", "NO", "MUL", "eur_id");
        $this->eco_id           = new Atributo("numero", "NO", "MUL", "eco_id");
        $this->sei_id           = new Atributo("numero", "YES", "MUL", "sei_id");
        $this->sts_id           = new Atributo("numero", "NO", "MUL", "sts_id");
        $this->clave            = new Atributo("numero", "NO", "UNI", "ur_clave");
        $this->nombre           = new Atributo("cadena", "NO", "", "ur_nombre");
        $this->compraDirecta    = new Atributo("numero", "NO", "", "ur_compraDirecta");
        $this->division         = new Atributo("numero", "NO", "", "ur_division");
        $this->ueContable       = new Atributo("numero", "NO", "", "ur_ueContable");
        $this->sector           = new Atributo("cadena", "NO", "", "ur_sector");
        $this->rh               = new Atributo("numero", "NO", "", "ur_rh");
        $this->poa              = new Atributo("numero", "NO", "", "ur_poa");
        $this->cadenaProductiva = new Atributo("numero", "NO", "", "ur_cadenaProductiva");
        $this->tipoFirma        = new Atributo("cadena", "NO", "", "ur_tipoFirma");
        $this->tipoCierre       = new Atributo("cadena", "NO", "", "ur_tipoCierre");
        $this->actualizarPOA    = new Atributo("numero", "NO", "", "ur_actualizarPOA");
        $this->usr_id           = new Atributo("numero", "NO", "", "usr_id");
        $this->ur_estadoCosteo  = new Atributo("numero", "NO", "", "ur_estadoCosteo");
        $this->ur_cierreForzado = new Atributo("numero", "NO", "", "ur_cierreForzado");

        $this->firmas   = new UnidadResponsableFirma($prm);
        $this->auxiliar = new AuxiliarUnidadResponsable($prm);

        $gpo_id = (! isset($prm->gpo_id)) ? '' : $prm->gpo_id;
        $ur_id  = (! isset($prm->ur_id)) ? '' : $prm->ur_id;

        if (intval($gpo_id) > 0 && intval($ur_id) > 0) {
            $this->setGpo_id($gpo_id);
            $this->setUr_id($ur_id);
            $this->select();
        }
    }

    /*Aquí se reescriben los metodos de Entidad, para poder hacer los select, update y delete acorde al
    particionamiento - vertical de la tabla*/
    public function select($servidor = "")
    {
        parent::select();
        $this->firmas->select();
        $this->auxiliar->select();
    }

    public function update($conectar = true, $servidor = "")
    {
        parent::update($conectar);
        $this->firmas->update();
        $this->auxiliar->update();
    }

    public function delete($conectar = true, $servidor = "")
    {
        $this->firmas->delete();
        $this->auxiliar->delete();
        parent::delete();
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/

    public static function abc($p)
    {
        $ur  = new UnidadResponsable();
        $urf = new UnidadResponsableFirma();
        if (isset($p->gpo_id)) {
            $ur->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $ur->setUr_id($p->ur_id);
        }

        if (isset($p->sad_id)) {
            $ur->setSad_id($p->sad_id);
        }

        if (isset($p->spd_id)) {
            $ur->setSpd_id($p->spd_id);
        }

        if (isset($p->eur_id)) {
            $ur->setEur_id($p->eur_id);
        }

        if (isset($p->eco_id)) {
            $ur->setEco_id($p->eco_id);
        }

        if (isset($p->sei_id)) {
            $ur->setSei_id($p->sei_id);
        }

        if (isset($p->sts_id)) {
            $ur->setSts_id($p->sts_id);
        }

        if (isset($p->clave)) {
            $ur->setClave($p->clave);
        }

        if (isset($p->nombre)) {
            $ur->setNombre($p->nombre);
        }

        if (isset($p->compraDirecta)) {
            $ur->setCompraDirecta($p->compraDirecta);
        }

        if (isset($p->division)) {
            $ur->setDivision($p->division);
        }

        if (isset($p->ueContable)) {
            $ur->setUeContable($p->ueContable);
        }

        if (isset($p->sector)) {
            $ur->setSector($p->sector);
        }

        if (isset($p->rh)) {
            $ur->setRh($p->rh);
        }

        if (isset($p->poa)) {
            $ur->setPoa($p->poa);
        }

        if (isset($p->cadenaProductiva)) {
            $ur->setCadenaProductiva($p->cadenaProductiva);
        }

        if (isset($p->tipoFirma)) {
            $ur->setTipoFirma($p->tipoFirma);
        }

        if (isset($p->tipoCierre)) {
            $ur->setTipoCierre($p->tipoCierre);
        }

        if (isset($p->actualizarPOA)) {
            $ur->setActualizarPOA($p->actualizarPOA);
        }

        if (isset($p->usr_id)) {
            $ur->setUsr_id($p->usr_id);
        }

        if (isset($p->ur_cierreForzado)) {
            $ur->setCierreForzado($p->ur_cierreForzado);
        }

        if (isset($p->nombramientoTitular)) {
            $ur->setNombramientoTitular($p->nombramientoTitular);
        }

        if (isset($p->urf_identificacionTitular)) {
            $ur->setUrf_identificacionTitular($p->urf_identificacionTitular);
        }

        if (isset($p->firmaTitular)) {
            $ur->setFirmaTitular($p->firmaTitular);
        }

        if (isset($p->nombramientoDelegatorioTitular)) {
            $ur->setNombramientoDelegatorioTitular($p->nombramientoDelegatorioTitular);
        }

        if (isset($p->urf_identificacionDelegatorioTitular)) {
            $ur->setUrf_identificacionDelegatorioTitular($p->urf_identificacionDelegatorioTitular);
        }

        if (isset($p->firmaDelegatorioTitular)) {
            $ur->setFirmaDelegatorioTitular($p->firmaDelegatorioTitular);
        }

        if (isset($p->acuerdoDelegatorioTitular)) {
            $ur->setAcuerdoDelegatorioTitular($p->acuerdoDelegatorioTitular);
        }

        if (isset($p->nombramientoAdministrativo)) {
            $ur->setNombramientoAdministrativo($p->nombramientoAdministrativo);
        }

        if (isset($p->urf_identificacionAdministrativo)) {
            $ur->setUrf_identificacionAdministrativo($p->urf_identificacionAdministrativo);
        }

        if (isset($p->firmaAdministrativo)) {
            $ur->setFirmaAdministrativo($p->firmaAdministrativo);
        }

        if (isset($p->nombramientoDelegatorioAdministrativo)) {
            $ur->setNombramientoDelegatorioAdministrativo($p->nombramientoDelegatorioAdministrativo);
        }

        if (isset($p->urf_identificacionDelegatorioAdministrativo)) {
            $ur->setUrf_identificacionDelegatorioAdministrativo($p->urf_identificacionDelegatorioAdministrativo);
        }

        if (isset($p->firmaDelegatorioAdministrativo)) {
            $ur->setFirmaDelegatorioAdministrativo($p->firmaDelegatorioAdministrativo);
        }

        if (isset($p->acuerdoDelegatorioAdministrativo)) {
            $ur->setAcuerdoDelegatorioAdministrativo($p->acuerdoDelegatorioAdministrativo);
        }

        if (isset($p->sts_id)) {
            $ur->setSts_id($p->sts_id);
        }

        if (isset($p->usr_id)) {
            $ur->setUsr_id($p->usr_id);
        }

        if (isset($p->nombramientoPlaneacion)) {
            $ur->setNombramientoPlaneacion($p->nombramientoPlaneacion);
        }

        if (isset($p->urf_identificacionPlaneacion)) {
            $ur->setUrf_identificacionPlaneacion($p->urf_identificacionPlaneacion);
        }

        if (isset($p->firmaPlaneacion)) {
            $ur->setFirmaPlaneacion($p->firmaPlaneacion);
        }

        if (isset($p->urf_identificacionEvaluacion)) {
            $urf->setUrf_identificacionEvaluacion($p->urf_identificacionEvaluacion);
        }

        if (isset($p->firmaEvaluacion)) {
            $urf->setFirmaEvaluacion($p->firmaEvaluacion);
        }

        if (isset($p->tituloTitular)) {
            $ur->setTituloTitular($p->tituloTitular);
        }

        if (isset($p->nombreTitular)) {
            $ur->setNombreTitular($p->nombreTitular);
        }

        if (isset($p->cargoTitular)) {
            $ur->setCargoTitular($p->cargoTitular);
        }

        if (isset($p->tituloAdministrativo)) {
            $ur->setTituloAdministrativo($p->tituloAdministrativo);
        }

        if (isset($p->nombreAdministrativo)) {
            $ur->setNombreAdministrativo($p->nombreAdministrativo);
        }

        if (isset($p->cargoAdministrativo)) {
            $ur->setCargoAdministrativo($p->cargoAdministrativo);
        }

        if (isset($p->tituloPlaneacion)) {
            $ur->setTituloPlaneacion($p->tituloPlaneacion);
        }

        if (isset($p->nombrePlaneacion)) {
            $ur->setNombrePlaneacion($p->nombrePlaneacion);
        }

        if (isset($p->cargoPlaneacion)) {
            $ur->setCargoPlaneacion($p->cargoPlaneacion);
        }

        if (isset($p->tituloEvaluacion)) {
            $ur->setTituloEvaluacion($p->tituloEvaluacion);
        }

        if (isset($p->nombreEvaluacion)) {
            $ur->setNombreEvaluacion($p->nombreEvaluacion);
        }

        if (isset($p->cargoEvaluacion)) {
            $ur->setCargoEvaluacion($p->cargoEvaluacion);
        }

        if (isset($p->nombreFinancieros)) {
            $ur->setNombreFinancieros($p->nombreFinancieros);
        }

        if (isset($p->cargoFinancieros)) {
            $ur->setCargoFinancieros($p->cargoFinancieros);
        }

        if (isset($p->inicioPeriodicoOficial)) {
            $ur->setInicioPeriodicoOficial($p->inicioPeriodicoOficial);
        }

        if (isset($p->inicioFecha)) {
            $ur->setInicioFecha($p->inicioFecha);
        }

        if (isset($p->inicioDecretoFecha)) {
            $ur->setInicioDecretoFecha($p->inicioDecretoFecha);
        }

        if (isset($p->modificacionPeriodico)) {
            $ur->setModificacionPeriodico($p->modificacionPeriodico);
        }

        if (isset($p->modificacionFecha)) {
            $ur->setModificacionFecha($p->modificacionFecha);
        }

        if (isset($p->internoPeriodico)) {
            $ur->setInternoPeriodico($p->internoPeriodico);
        }

        if (isset($p->internoFecha)) {
            $ur->setInternoFecha($p->internoFecha);
        }

        if (isset($p->internoArchivo)) {
            $ur->setInternoArchivo($p->internoArchivo);
        }

        if (isset($p->planeacionFecha)) {
            $ur->setPlaneacionFecha($p->planeacionFecha);
        }

        if (isset($p->organigramaArchivo)) {
            $ur->setOrganigramaArchivo($p->organigramaArchivo);
        }

        if (isset($p->validacionFecha)) {
            $ur->setValidacionFecha($p->validacionFecha);
        }

        if (isset($p->organigramaFecha)) {
            $ur->setOrganigramaFecha($p->organigramaFecha);
        }

        if (isset($p->objetivoGeneral)) {
            $ur->setObjetivoGeneral($p->objetivoGeneral);
        }

        if (isset($p->objetivoEstrategico)) {
            $ur->setObjetivoEstrategico($p->objetivoEstrategico);
        }

        if (isset($p->productoInstitucional)) {
            $ur->setProductoInstitucional($p->productoInstitucional);
        }

        if (isset($p->mision)) {
            $ur->setMision($p->mision);
        }

        if (isset($p->vision)) {
            $ur->setVision($p->vision);
        }

        if (isset($p->diagnostico)) {
            $ur->setDiagnostico($p->diagnostico);
        }

        if (isset($p->siglas)) {
            $ur->setSiglas($p->siglas);
        }

        if (isset($p->decretoArchivo)) {
            $ur->setDecretoArchivo($p->decretoArchivo);
        }

        if (isset($p->firmaNombramientoArchivo)) {
            $ur->setFirmaNombramientoArchivo($p->firmaNombramientoArchivo);
        }

        if (isset($p->firmaIdentificacionArchivo)) {
            $ur->setFirmaIdentificacionArchivo($p->firmaIdentificacionArchivo);
        }

        if (isset($p->firmaLegalizacionArchivo)) {
            $ur->setFirmaLegalizacionArchivo($p->firmaLegalizacionArchivo);
        }

        if (isset($p->firmaDelegartorioArchivo)) {
            $ur->setFirmaDelegartorioArchivo($p->firmaDelegartorioArchivo);
        }

        if (isset($p->usr_id)) {
            $ur->setUsr_id($p->usr_id);
        }

        if (isset($p->correoElectronicoTitular)) {
            $ur->setCorreoElectronicoTitular($p->correoElectronicoTitular);
        }

        if (isset($p->telefonoTitular)) {
            $ur->setTelefonoTitular($p->telefonoTitular);
        }

        if (isset($p->celularTitular)) {
            $ur->setCelularTitular($p->celularTitular);
        }

        if (isset($p->extensionTitular)) {
            $ur->setExtensionTitular($p->extensionTitular);
        }

        if (isset($p->correoElectronicoAdministrativo)) {
            $ur->setCorreoElectronicoAdministrativo($p->correoElectronicoAdministrativo);
        }

        if (isset($p->telefonoAdministrativo)) {
            $ur->setTelefonoAdministrativo($p->telefonoAdministrativo);
        }

        if (isset($p->celularAdministrativo)) {
            $ur->setCelularAdministrativo($p->celularAdministrativo);
        }

        if (isset($p->extensionAdministrativo)) {
            $ur->setExtensionAdministrativo($p->extensionAdministrativo);
        }

        if (isset($p->correoElectronicoPlaneacion)) {
            $ur->setCorreoElectronicoPlaneacion($p->correoElectronicoPlaneacion);
        }

        if (isset($p->telefonoPlaneacion)) {
            $ur->setTelefonoPlaneacion($p->telefonoPlaneacion);
        }

        if (isset($p->celularPlaneacion)) {
            $ur->setCelularPlaneacion($p->celularPlaneacion);
        }

        if (isset($p->extensionPlaneacion)) {
            $ur->setExtensionPlaneacion($p->extensionPlaneacion);
        }

        if (isset($p->domicilio)) {
            $ur->setDomicilio($p->domicilio);
        }

        if (isset($p->telefono)) {
            $ur->setTelefono($p->telefono);
        }

        if (isset($p->correoElectronicoEvaluacion)) {
            $ur->setCorreoElectronicoEvaluacion($p->correoElectronicoEvaluacion);
        }

        if (isset($p->telefonoEvaluacion)) {
            $ur->setTelefonoEvaluacion($p->telefonoEvaluacion);
        }

        if (isset($p->celularEvaluacion)) {
            $ur->setCelularEvaluacion($p->celularEvaluacion);
        }

        if (isset($p->extensionEvaluacion)) {
            $ur->setExtensionEvaluacion($p->extensionEvaluacion);
        }

        if (isset($p->paginaWeb)) {
            $ur->setPaginaWeb($p->paginaWeb);
        }

        if (isset($p->correoElectronicoContacto)) {
            $ur->setCorreoElectronicoContacto($p->correoElectronicoContacto);
        }

        if (isset($p->municipio)) {
            $ur->setMun_id($p->municipio);
        }

        if (isset($p->codigoPostal)) {
            $ur->setCodigoPostal($p->codigoPostal);
        }

        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != "::1") ?
        $_SESSION[_S]['ses-ip'] :
        '127.0.0.1';
        $bit_usr_id = $_SESSION[_S]['usr_id'];

        $ur->conectar();

        switch ($p->abc) {
            case "a":
                $bit_observacion = 'REGISTRO INSERTADO DESDE PANTALLA';
                $sql             = ' SET @bit_observacion = ?, @usr_ip =INET_ATON( ? ), @usr_id= ? ;';
                $params          = [$bit_observacion, $ip, $bit_usr_id];
                $ur->setSql($sql);
                $ur->consultar($params);

                $ur->insert(false);
                return $ur->getUltimoId();
                break;
            case "b":
                $ur->delete();
                break;
            case "c":
                $bit_observacion = isset($p->bit_observacion) ? $p->bit_observacion : 'SE ACTUALIZA DESDE PANTALLA';
                $sql             = ' SET @bit_observacion = ?, @usr_ip =INET_ATON( ? ), @usr_id= ? ;';
                $params          = [$bit_observacion, $ip, $bit_usr_id];
                $ur->setSql($sql);
                $ur->consultar($params);

                $ur->update(false);
                break;
        }
    }

    /*funcion de que determina, si una Ur esta excenta o no de realizar compras consolidadas
    mediante Compras y Adquisiciones */
    public static function excentaComprasConsolidadas()
    {
        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        $prm->gpo_id = (! isset($prm->gpo_id)) ? '' : $prm->gpo_id;
        $prm->ur_id  = (! isset($prm->ur_id)) ? '' : $prm->ur_id;
        $prm->pe_id  = (! isset($prm->pe_id)) ? '' : $prm->pe_id;
        $prm->ecf_id = (! isset($prm->ecf_id)) ? '' : $prm->ecf_id;

        $sql = "
            SELECT ur_compraDirecta
            FROM " . BD_CTRL . ".presupuesto p
            INNER JOIN " . BD_CTRL . ".cat_UnidadResponsable USING( gpo_id, ur_id )
            INNER JOIN " . BD_CTRL . ".cat_PartidaEspecifica USING( pg_id, pe_id )
            INNER JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento USING( ecf_id, cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
            INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            WHERE ur_compraDirecta = 2
            AND pe_comprasDirectas != 1
            AND ecf_division = 1
            AND (gpo_id, ur_id, pe_id) NOT IN (SELECT gpo_id, ur_id, pe_id FROM " . BD_CTRL . ".cat_ExcepcionCompra)
            AND ecf_id = ?
            AND gpo_id= ? and ur_id= ? and pe_id= ?
            GROUP BY gpo_id,ur_id;
        ";
        $params = [$prm->ecf_id, $prm->gpo_id, $prm->ur_id, $prm->pe_id];
        $con    = new AccesoDatos();
        $con->setSql($sql);
        $con->conectar();
        $con->consultar($params);
        $con->desconectar();
        $row = $con->extraer();
        $registros = $con->getNumeroRegistros();
        if ($registros > 0) {
            if ($row['ur_compraDirecta'] == 1) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 1;
        }
    }
}
