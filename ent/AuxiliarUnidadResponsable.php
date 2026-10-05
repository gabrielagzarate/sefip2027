<?php

require_once dirname(__FILE__) . "/Entidad.php";

class  AuxiliarUnidadResponsable extends Entidad
{
	/*******************************************************
	 * ATRIBUTOS DE LA CLASE
	 ******************************************************/
	protected $gpo_id; //
	protected $ur_id; //
	protected $tituloTitular; // Título del Titular
	protected $nombreTitular; // Nombre completo del titular
	protected $cargoTitular; // Cargo del Titular	
	protected $tituloAdministrativo; // Título del Administrativo
	protected $nombreAdministrativo; // Nombre completo del responsable administrativo
	protected $cargoAdministrativo; // Cargo del administrativo
	protected $tituloPlaneacion; // Título del responsable de Planeacion
	protected $nombrePlaneacion; // Nombre completo del responsable de Planeacion
	protected $cargoPlaneacion; // Cargo del responsable de Planeacion
	protected $tituloEvaluacion; // Título de la persona encargada de la evaluacion y seguimiento del desempeño
	protected $nombreEvaluacion; // nombre de la persona encargada de la evaluacion y seguimiento del desempeño
	protected $cargoEvaluacion; // Cargo o puesto de la persona encargada de la evaluación y seguimiento del desempeño.
	protected $nombreFinancieros; // Nombre del responsable financiero
	protected $cargoFinancieros; // Cargo del financiero
	protected $inicioPeriodicoOficial; // Numero del periodico oficial del inicio de operaciones
	protected $inicioFecha; // Fecha de publicacion del periodico oficial
	protected $inicioDecretoFecha; // Fecha de inicio de actividades segun decreto
	protected $modificacionPeriodico; // Numero del periodico oficial de la ultima moificacion
	protected $modificacionFecha; // Fecha de publicacion del periodico modificado
	protected $internoPeriodico; // Numero del periodico oficial del reglamento interno
	protected $internoFecha; // Fecha del periodico oficial del reglamento interno
	protected $organigramaArchivo; // Archivo del organigrama 
	protected $organigramaFecha; // Fecha de la ultima actualizacion del organigrama
	protected $objetivoGeneral; // Objetivo general de la UR
	protected $objetivoEstrategico; // Objetivo estrategico de la UR
	protected $productoInstitucional; //
	protected $mision; // Mision de la UR
	protected $vision; // Vision de la UR
	protected $diagnostico; // Diagnostico de la UR
	protected $siglas; // Siglas de la UR
	protected $decretoArchivo; // Archivo del decreto
	protected $firmaNombramientoArchivo; //
	protected $firmaIdentificacionArchivo; //
	protected $firmaLegalizacionArchivo; //
	protected $firmaDelegartorioArchivo; //
	protected $domicilio; //
	protected $telefono; //
	protected $internoArchivo; //
	protected $correoElectronicoTitular; //
	protected $telefonoTitular; //
	protected $celularTitular; //
	protected $extensionTitular; //
	protected $correoElectronicoAdministrativo; //
	protected $telefonoAdministrativo; //
	protected $celularAdministrativo; //
	protected $extensionAdministrativo; //
	protected $correoElectronicoPlaneacion; //
	protected $telefonoPlaneacion; //
	protected $celularPlaneacion; //
	protected $extensionPlaneacion; //
	protected $correoElectronicoEvaluacion; //
	protected $telefonoEvaluacion; //
	protected $celularEvaluacion; //
	protected $extensionEvaluacion; //
	protected $paginaWeb; // Inidica la direccion electronica o portal de la Dependendcia, organismo o entidad.
	protected $correoElectronicoContacto; // guarda el correo electrónico del contacto de la dependecia, organismo o entidad.
	protected $planeacionFecha; //Fecha de publicación del reglamento interno
	protected $validacionFecha; //Fecha de validación de la estructura orgánica
	protected $mun_id; //
	protected $codigoPostal; //
	protected $usr_id; // Clave identificadora del usuario que inserto el registro

	/*******************************************************
	 * METODOS GET
	 ******************************************************/
	public function getGpo_id()
	{
		return $this->gpo_id->getVal();
	}

	public function getUr_id()
	{
		return $this->ur_id->getVal();
	}

	public function getTituloTitular()
	{
		return $this->tituloTitular->getVal();
	}

	public function getNombreTitular()
	{
		return $this->nombreTitular->getVal();
	}

	public function getCargoTitular()
	{
		return $this->cargoTitular->getVal();
	}

	public function getTituloAdministrativo()
	{
		return $this->tituloAdministrativo->getVal();
	}

	public function getNombreAdministrativo()
	{
		return $this->nombreAdministrativo->getVal();
	}

	public function getCargoAdministrativo()
	{
		return $this->cargoAdministrativo->getVal();
	}

	public function getTituloPlaneacion()
	{
		return $this->tituloPlaneacion->getVal();
	}

	public function getNombrePlaneacion()
	{
		return $this->nombrePlaneacion->getVal();
	}

	public function getCargoPlaneacion()
	{
		return $this->cargoPlaneacion->getVal();
	}

	public function getTituloEvaluacion()
	{
		return $this->tituloEvaluacion->getVal();
	}

	public function getNombreEvaluacion()
	{
		return $this->nombreEvaluacion->getVal();
	}

	public function getCargoEvaluacion()
	{
		return $this->cargoEvaluacion->getVal();
	}

	public function getNombreFinancieros()
	{
		return $this->nombreFinancieros->getVal();
	}

	public function getCargoFinancieros()
	{
		return $this->cargoFinancieros->getVal();
	}

	public function getInicioPeriodicoOficial()
	{
		return $this->inicioPeriodicoOficial->getVal();
	}

	public function getInicioFecha()
	{
		return $this->inicioFecha->getVal();
	}

	public function getInicioDecretoFecha()
	{
		return $this->inicioDecretoFecha->getVal();
	}

	public function getModificacionPeriodico()
	{
		return $this->modificacionPeriodico->getVal();
	}

	public function getModificacionFecha()
	{
		return $this->modificacionFecha->getVal();
	}

	public function getInternoPeriodico()
	{
		return $this->internoPeriodico->getVal();
	}

	public function getInternoFecha()
	{
		return $this->internoFecha->getVal();
	}

	public function getOrganigramaArchivo()
	{
		return $this->organigramaArchivo->getVal();
	}

	public function getOrganigramaFecha()
	{
		return $this->organigramaFecha->getVal();
	}

	public function getObjetivoGeneral()
	{
		return $this->objetivoGeneral->getVal();
	}

	public function getObjetivoEstrategico()
	{
		return $this->objetivoEstrategico->getVal();
	}

	public function getProductoInstitucional()
	{
		return $this->productoInstitucional->getVal();
	}

	public function getMision()
	{
		return $this->mision->getVal();
	}

	public function getVision()
	{
		return $this->vision->getVal();
	}

	public function getDiagnostico()
	{
		return $this->diagnostico->getVal();
	}

	public function getSiglas()
	{
		return $this->siglas->getVal();
	}

	public function getDecretoArchivo()
	{
		return $this->decretoArchivo->getVal();
	}

	public function getFirmaNombramientoArchivo()
	{
		return $this->firmaNombramientoArchivo->getVal();
	}

	public function getFirmaIdentificacionArchivo()
	{
		return $this->firmaIdentificacionArchivo->getVal();
	}

	public function getFirmaLegalizacionArchivo()
	{
		return $this->firmaLegalizacionArchivo->getVal();
	}

	public function getFirmaDelegartorioArchivo()
	{
		return $this->firmaDelegartorioArchivo->getVal();
	}

	public function getDomicilio()
	{
		return $this->domicilio->getVal();
	}

	public function getTelefono()
	{
		return $this->telefono->getVal();
	}

	public function getInternoArchivo()
	{
		return $this->internoArchivo->getVal();
	}

	public function getCorreoElectronicoTitular()
	{
		return $this->correoElectronicoTitular->getVal();
	}

	public function getTelefonoTitular()
	{
		return $this->telefonoTitular->getVal();
	}

	public function getCelularTitular()
	{
		return $this->celularTitular->getVal();
	}

	public function getExtensionTitular()
	{
		return $this->extensionTitular->getVal();
	}

	public function getCorreoElectronicoAdministrativo()
	{
		return $this->correoElectronicoAdministrativo->getVal();
	}

	public function getTelefonoAdministrativo()
	{
		return $this->telefonoAdministrativo->getVal();
	}

	public function getCelularAdministrativo()
	{
		return $this->celularAdministrativo->getVal();
	}

	public function getExtensionAdministrativo()
	{
		return $this->extensionAdministrativo->getVal();
	}

	public function getCorreoElectronicoPlaneacion()
	{
		return $this->correoElectronicoPlaneacion->getVal();
	}

	public function getTelefonoPlaneacion()
	{
		return $this->telefonoPlaneacion->getVal();
	}

	public function getCelularPlaneacion()
	{
		return $this->celularPlaneacion->getVal();
	}

	public function getExtensionPlaneacion()
	{
		return $this->extensionPlaneacion->getVal();
	}

	public function getCorreoElectronicoEvaluacion()
	{
		return $this->correoElectronicoEvaluacion->getVal();
	}

	public function getTelefonoEvaluacion()
	{
		return $this->telefonoEvaluacion->getVal();
	}

	public function getCelularEvaluacion()
	{
		return $this->celularEvaluacion->getVal();
	}

	public function getExtensionEvaluacion()
	{
		return $this->extensionEvaluacion->getVal();
	}

	public function getPaginaWeb()
	{
		return $this->paginaWeb->getVal();
	}

	public function getCorreoElectronicoContacto()
	{
		return $this->correoElectronicoContacto->getVal();
	}

	public function getPlaneacionFecha()
	{
		return $this->planeacionFecha->getVal();
	}

	public function getValidacionFecha()
	{
		return $this->validacionFecha->getVal();
	}

	public function getMun_id()
	{
		return $this->mun_id->getVal();
	}

	public function getCodigoPostal()
	{
		return $this->codigoPostal->getVal();
	}

	public function getUsr_id()
	{
		return $this->usr_id->getVal();
	}

	/*******************************************************
	 * METODOS SET
	 ******************************************************/
	public function setGpo_id($val)
	{
		$this->gpo_id->setVal($val);
	}

	public function setUr_id($val)
	{
		$this->ur_id->setVal($val);
	}

	public function setTituloTitular($val)
	{
		$this->tituloTitular->setVal($val);
	}

	public function setNombreTitular($val)
	{
		$this->nombreTitular->setVal($val);
	}

	public function setCargoTitular($val)
	{
		$this->cargoTitular->setVal($val);
	}

	public function setTituloAdministrativo($val)
	{
		$this->tituloAdministrativo->setVal($val);
	}

	public function setNombreAdministrativo($val)
	{
		$this->nombreAdministrativo->setVal($val);
	}

	public function setCargoAdministrativo($val)
	{
		$this->cargoAdministrativo->setVal($val);
	}

	public function setTituloPlaneacion($val)
	{
		$this->tituloPlaneacion->setVal($val);
	}

	public function setNombrePlaneacion($val)
	{
		$this->nombrePlaneacion->setVal($val);
	}

	public function setCargoPlaneacion($val)
	{
		$this->cargoPlaneacion->setVal($val);
	}

	public function setTituloEvaluacion($val)
	{
		$this->tituloEvaluacion->setVal($val);
	}

	public function setNombreEvaluacion($val)
	{
		$this->nombreEvaluacion->setVal($val);
	}

	public function setCargoEvaluacion($val)
	{
		$this->cargoEvaluacion->setVal($val);
	}

	public function setNombreFinancieros($val)
	{
		$this->nombreFinancieros->setVal($val);
	}

	public function setCargoFinancieros($val)
	{
		$this->cargoFinancieros->setVal($val);
	}

	public function setInicioPeriodicoOficial($val)
	{
		$this->inicioPeriodicoOficial->setVal($val);
	}

	public function setInicioFecha($val)
	{
		$this->inicioFecha->setVal($val);
	}

	public function setInicioDecretoFecha($val)
	{
		$this->inicioDecretoFecha->setVal($val);
	}

	public function setModificacionPeriodico($val)
	{
		$this->modificacionPeriodico->setVal($val);
	}

	public function setModificacionFecha($val)
	{
		$this->modificacionFecha->setVal($val);
	}

	public function setInternoPeriodico($val)
	{
		$this->internoPeriodico->setVal($val);
	}

	public function setInternoFecha($val)
	{
		$this->internoFecha->setVal($val);
	}

	public function setOrganigramaArchivo($val)
	{
		$this->organigramaArchivo->setVal($val);
	}

	public function setOrganigramaFecha($val)
	{
		$this->organigramaFecha->setVal($val);
	}

	public function setObjetivoGeneral($val)
	{
		$this->objetivoGeneral->setVal($val);
	}

	public function setObjetivoEstrategico($val)
	{
		$this->objetivoEstrategico->setVal($val);
	}

	public function setProductoInstitucional($val)
	{
		$this->productoInstitucional->setVal($val);
	}

	public function setMision($val)
	{
		$this->mision->setVal($val);
	}

	public function setVision($val)
	{
		$this->vision->setVal($val);
	}

	public function setDiagnostico($val)
	{
		$this->diagnostico->setVal($val);
	}

	public function setSiglas($val)
	{
		$this->siglas->setVal($val);
	}

	public function setDecretoArchivo($val)
	{
		$this->decretoArchivo->setVal($val);
	}

	public function setFirmaNombramientoArchivo($val)
	{
		$this->firmaNombramientoArchivo->setVal($val);
	}

	public function setFirmaIdentificacionArchivo($val)
	{
		$this->firmaIdentificacionArchivo->setVal($val);
	}

	public function setFirmaLegalizacionArchivo($val)
	{
		$this->firmaLegalizacionArchivo->setVal($val);
	}

	public function setFirmaDelegartorioArchivo($val)
	{
		$this->firmaDelegartorioArchivo->setVal($val);
	}

	public function setDomicilio($val)
	{
		$this->domicilio->setVal($val);
	}

	public function setTelefono($val)
	{
		$this->telefono->setVal($val);
	}

	public function setInternoArchivo($val)
	{
		$this->internoArchivo->setVal($val);
	}

	public function setCorreoElectronicoTitular($val)
	{
		$this->correoElectronicoTitular->setVal($val);
	}

	public function setTelefonoTitular($val)
	{
		$this->telefonoTitular->setVal($val);
	}

	public function setCelularTitular($val)
	{
		$this->celularTitular->setVal($val);
	}

	public function setExtensionTitular($val)
	{
		$this->extensionTitular->setVal($val);
	}

	public function setCorreoElectronicoAdministrativo($val)
	{
		$this->correoElectronicoAdministrativo->setVal($val);
	}

	public function setTelefonoAdministrativo($val)
	{
		$this->telefonoAdministrativo->setVal($val);
	}

	public function setCelularAdministrativo($val)
	{
		$this->celularAdministrativo->setVal($val);
	}

	public function setExtensionAdministrativo($val)
	{
		$this->extensionAdministrativo->setVal($val);
	}

	public function setCorreoElectronicoPlaneacion($val)
	{
		$this->correoElectronicoPlaneacion->setVal($val);
	}

	public function setTelefonoPlaneacion($val)
	{
		$this->telefonoPlaneacion->setVal($val);
	}

	public function setCelularPlaneacion($val)
	{
		$this->celularPlaneacion->setVal($val);
	}

	public function setExtensionPlaneacion($val)
	{
		$this->extensionPlaneacion->setVal($val);
	}

	public function setCorreoElectronicoEvaluacion($val)
	{
		$this->correoElectronicoEvaluacion->setVal($val);
	}

	public function setTelefonoEvaluacion($val)
	{
		$this->telefonoEvaluacion->setVal($val);
	}

	public function setCelularEvaluacion($val)
	{
		$this->celularEvaluacion->setVal($val);
	}

	public function setExtensionEvaluacion($val)
	{
		$this->extensionEvaluacion->setVal($val);
	}

	public function setPaginaWeb($val)
	{
		$this->paginaWeb->setVal($val);
	}

	public function setCorreoElectronicoContacto($val)
	{
		$this->correoElectronicoContacto->setVal($val);
	}

	public function setPlaneacionFecha($val)
	{
		$this->planeacionFecha->setVal($val);
	}

	public function setValidacionFecha($val)
	{
		$this->validacionFecha->setVal($val);
	}

	public function setMun_id($val)
	{
		$this->mun_id->setVal($val);
	}

	public function setCodigoPostal($val)
	{
		$this->codigoPostal->setVal($val);
	}

	public function setUsr_id($val)
	{
		$this->usr_id->setVal($val);
	}

	/*******************************************************
	 * METODOS STA (SET sin persistencia)
	 ******************************************************/
	public function staGpo_id($val)
	{
		$this->gpo_id->staVal($val);
	}

	public function staUr_id($val)
	{
		$this->ur_id->staVal($val);
	}

	public function staTituloTitular($val)
	{
		$this->tituloTitular->staVal($val);
	}

	public function staNombreTitular($val)
	{
		$this->nombreTitular->staVal($val);
	}

	public function staCargoTitular($val)
	{
		$this->cargoTitular->staVal($val);
	}

	public function staTituloAdministrativo($val)
	{
		$this->tituloAdministrativo->staVal($val);
	}

	public function staNombreAdministrativo($val)
	{
		$this->nombreAdministrativo->staVal($val);
	}

	public function staCargoAdministrativo($val)
	{
		$this->cargoAdministrativo->staVal($val);
	}

	public function staTituloPlaneacion($val)
	{
		$this->tituloPlaneacion->staVal($val);
	}

	public function staNombrePlaneacion($val)
	{
		$this->nombrePlaneacion->staVal($val);
	}

	public function staCargoPlaneacion($val)
	{
		$this->cargoPlaneacion->staVal($val);
	}

	public function staTituloEvaluacion($val)
	{
		$this->tituloEvaluacion->staVal($val);
	}

	public function staNombreEvaluacion($val)
	{
		$this->nombreEvaluacion->staVal($val);
	}

	public function staCargoEvaluacion($val)
	{
		$this->cargoEvaluacion->staVal($val);
	}

	public function staNombreFinancieros($val)
	{
		$this->nombreFinancieros->staVal($val);
	}

	public function staCargoFinancieros($val)
	{
		$this->cargoFinancieros->staVal($val);
	}

	public function staInicioPeriodicoOficial($val)
	{
		$this->inicioPeriodicoOficial->staVal($val);
	}

	public function staInicioFecha($val)
	{
		$this->inicioFecha->staVal($val);
	}

	public function staInicioDecretoFecha($val)
	{
		$this->inicioDecretoFecha->staVal($val);
	}

	public function staModificacionPeriodico($val)
	{
		$this->modificacionPeriodico->staVal($val);
	}

	public function staModificacionFecha($val)
	{
		$this->modificacionFecha->staVal($val);
	}

	public function staInternoPeriodico($val)
	{
		$this->internoPeriodico->staVal($val);
	}

	public function staInternoFecha($val)
	{
		$this->internoFecha->staVal($val);
	}

	public function staOrganigramaArchivo($val)
	{
		$this->organigramaArchivo->staVal($val);
	}

	public function staOrganigramaFecha($val)
	{
		$this->organigramaFecha->staVal($val);
	}

	public function staObjetivoGeneral($val)
	{
		$this->objetivoGeneral->staVal($val);
	}

	public function staObjetivoEstrategico($val)
	{
		$this->objetivoEstrategico->staVal($val);
	}

	public function staProductoInstitucional($val)
	{
		$this->productoInstitucional->staVal($val);
	}

	public function staMision($val)
	{
		$this->mision->staVal($val);
	}

	public function staVision($val)
	{
		$this->vision->staVal($val);
	}

	public function staDiagnostico($val)
	{
		$this->diagnostico->staVal($val);
	}

	public function staSiglas($val)
	{
		$this->siglas->staVal($val);
	}

	public function staDecretoArchivo($val)
	{
		$this->decretoArchivo->staVal($val);
	}

	public function staFirmaNombramientoArchivo($val)
	{
		$this->firmaNombramientoArchivo->staVal($val);
	}

	public function staFirmaIdentificacionArchivo($val)
	{
		$this->firmaIdentificacionArchivo->staVal($val);
	}

	public function staFirmaLegalizacionArchivo($val)
	{
		$this->firmaLegalizacionArchivo->staVal($val);
	}

	public function staFirmaDelegartorioArchivo($val)
	{
		$this->firmaDelegartorioArchivo->staVal($val);
	}

	public function staDomicilio($val)
	{
		$this->domicilio->staVal($val);
	}

	public function staTelefono($val)
	{
		$this->telefono->staVal($val);
	}

	public function staInternoArchivo($val)
	{
		$this->internoArchivo->staVal($val);
	}

	public function staCorreoElectronicoTitular($val)
	{
		$this->correoElectronicoTitular->staVal($val);
	}

	public function staTelefonoTitular($val)
	{
		$this->telefonoTitular->staVal($val);
	}

	public function staCelularTitular($val)
	{
		$this->celularTitular->staVal($val);
	}

	public function staExtensionTitular($val)
	{
		$this->extensionTitular->staVal($val);
	}

	public function staCorreoElectronicoAdministrativo($val)
	{
		$this->correoElectronicoAdministrativo->staVal($val);
	}

	public function staTelefonoAdministrativo($val)
	{
		$this->telefonoAdministrativo->staVal($val);
	}

	public function staCelularAdministrativo($val)
	{
		$this->celularAdministrativo->staVal($val);
	}

	public function staExtensionAdministrativo($val)
	{
		$this->extensionAdministrativo->staVal($val);
	}

	public function staCorreoElectronicoPlaneacion($val)
	{
		$this->correoElectronicoPlaneacion->staVal($val);
	}

	public function staTelefonoPlaneacion($val)
	{
		$this->telefonoPlaneacion->staVal($val);
	}

	public function staCelularPlaneacion($val)
	{
		$this->celularPlaneacion->staVal($val);
	}

	public function staExtensionPlaneacion($val)
	{
		$this->extensionPlaneacion->staVal($val);
	}

	public function staCorreoElectronicoEvaluacion($val)
	{
		$this->correoElectronicoEvaluacion->staVal($val);
	}

	public function staTelefonoEvaluacion($val)
	{
		$this->telefonoEvaluacion->staVal($val);
	}

	public function staCelularEvaluacion($val)
	{
		$this->celularEvaluacion->staVal($val);
	}

	public function staExtensionEvaluacion($val)
	{
		$this->extensionEvaluacion->staVal($val);
	}

	public function staPaginaWeb($val)
	{
		$this->paginaWeb->staVal($val);
	}

	public function staCorreoElectronicoContacto($val)
	{
		$this->correoElectronicoContacto->staVal($val);
	}

	public function staPlaneacionFecha($val)
	{
		$this->planeacionFecha->staVal($val);
	}

	public function staValidacionFecha($val)
	{
		$this->validacionFecha->staVal($val);
	}

	public function staMun_id($val)
	{
		$this->mun_id->staVal($val);
	}

	public function staCodigoPostal($val)
	{
		$this->codigoPostal->staVal($val);
	}

	public function staUsr_id($val)
	{
		$this->usr_id->staVal($val);
	}

	/*******************************************************
	 * CONSTRUCTOR
	 ******************************************************/
	public function __construct()
	{
		parent::__construct(array("tabla" => "aux_UnidadResponsable"));

		/*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
		$this->gpo_id = new Atributo("numero", "NO", "PRI", "gpo_id");
		$this->ur_id = new Atributo("numero", "NO", "PRI", "ur_id");
		$this->tituloTitular = new Atributo("cadena", "NO", "", "ur_tituloTitular");
		$this->nombreTitular = new Atributo("cadena", "NO", "", "ur_nombreTitular");
		$this->cargoTitular = new Atributo("cadena", "NO", "", "ur_cargoTitular");
		$this->tituloAdministrativo = new Atributo("cadena", "NO", "", "ur_tituloAdministrativo");
		$this->nombreAdministrativo = new Atributo("cadena", "NO", "", "ur_nombreAdministrativo");
		$this->cargoAdministrativo = new Atributo("cadena", "NO", "", "ur_cargoAdministrativo");
		$this->tituloPlaneacion = new Atributo("cadena", "NO", "", "ur_tituloPlaneacion");
		$this->nombrePlaneacion = new Atributo("cadena", "NO", "", "ur_nombrePlaneacion");
		$this->cargoPlaneacion = new Atributo("cadena", "NO", "", "ur_cargoPlaneacion");
		$this->tituloEvaluacion = new Atributo("cadena", "NO", "", "ur_tituloEvaluacion");
		$this->nombreEvaluacion = new Atributo("cadena", "NO", "", "ur_nombreEvaluacion");
		$this->cargoEvaluacion = new Atributo("cadena", "NO", "", "ur_cargoEvaluacion");
		$this->nombreFinancieros = new Atributo("cadena", "NO", "", "ur_nombreFinancieros");
		$this->cargoFinancieros = new Atributo("cadena", "NO", "", "ur_cargoFinancieros");
		$this->inicioPeriodicoOficial = new Atributo("cadena", "NO", "", "ur_inicioPeriodicoOficial");
		$this->inicioFecha = new Atributo("fecha", "YES", "", "ur_inicioFecha");
		$this->inicioDecretoFecha = new Atributo("fecha", "YES", "", "ur_inicioDecretoFecha");
		$this->modificacionPeriodico = new Atributo("cadena", "NO", "", "ur_modificacionPeriodico");
		$this->modificacionFecha = new Atributo("fecha", "YES", "", "ur_modificacionFecha");
		$this->internoPeriodico = new Atributo("cadena", "NO", "", "ur_internoPeriodico");
		$this->internoFecha = new Atributo("fecha", "YES", "", "ur_internoFecha");
		$this->organigramaArchivo = new Atributo("cadena", "NO", "", "ur_organigramaArchivo");
		$this->organigramaFecha = new Atributo("fecha", "YES", "", "ur_organigramaFecha");
		$this->objetivoGeneral = new Atributo("cadena", "NO", "", "ur_objetivoGeneral");
		$this->objetivoEstrategico = new Atributo("cadena", "NO", "", "ur_objetivoEstrategico");
		$this->productoInstitucional = new Atributo("cadena", "NO", "", "ur_productoInstitucional");
		$this->mision = new Atributo("cadena", "NO", "", "ur_mision");
		$this->vision = new Atributo("cadena", "NO", "", "ur_vision");
		$this->diagnostico = new Atributo("cadena", "NO", "", "ur_diagnostico");
		$this->siglas = new Atributo("cadena", "NO", "", "ur_siglas");
		$this->decretoArchivo = new Atributo("cadena", "NO", "", "ur_decretoArchivo");
		$this->firmaNombramientoArchivo = new Atributo("cadena", "NO", "", "ur_firmaNombramientoArchivo");
		$this->firmaIdentificacionArchivo = new Atributo("cadena", "NO", "", "ur_firmaIdentificacionArchivo");
		$this->firmaLegalizacionArchivo = new Atributo("cadena", "NO", "", "ur_firmaLegalizacionArchivo");
		$this->firmaDelegartorioArchivo = new Atributo("cadena", "NO", "", "ur_firmaDelegatorioArchivo");
		$this->domicilio = new Atributo("cadena", "NO", "", "ur_domicilio");
		$this->telefono = new Atributo("cadena", "NO", "", "ur_telefono");
		$this->internoArchivo = new Atributo("cadena", "NO", "", "ur_internoArchivo");
		$this->correoElectronicoTitular = new Atributo("cadena", "NO", "", "ur_correoElectronicoTitular");
		$this->telefonoTitular = new Atributo("cadena", "NO", "", "ur_telefonoTitular");
		$this->celularTitular = new Atributo("cadena", "NO", "", "ur_celularTitular");
		$this->extensionTitular = new Atributo("cadena", "NO", "", "ur_extensionTitular");
		$this->correoElectronicoAdministrativo = new Atributo("cadena", "NO", "", "ur_correoElectronicoAdministrativo");
		$this->telefonoAdministrativo = new Atributo("cadena", "NO", "", "ur_telefonoAdministrativo");
		$this->celularAdministrativo = new Atributo("cadena", "NO", "", "ur_celularAdministrativo");
		$this->extensionAdministrativo = new Atributo("cadena", "NO", "", "ur_extensionAdministrativo");
		$this->correoElectronicoPlaneacion = new Atributo("cadena", "NO", "", "ur_correoElectronicoPlaneacion");
		$this->telefonoPlaneacion = new Atributo("cadena", "NO", "", "ur_telefonoPlaneacion");
		$this->celularPlaneacion = new Atributo("cadena", "NO", "", "ur_celularPlaneacion");
		$this->extensionPlaneacion = new Atributo("cadena", "NO", "", "ur_extensionPlaneacion");
		$this->correoElectronicoEvaluacion = new Atributo("cadena", "NO", "", "ur_correoElectronicoEvaluacion");
		$this->telefonoEvaluacion = new Atributo("cadena", "NO", "", "ur_telefonoEvaluacion");
		$this->celularEvaluacion = new Atributo("cadena", "NO", "", "ur_celularEvaluacion");
		$this->extensionEvaluacion = new Atributo("cadena", "NO", "", "ur_extensionEvaluacion");
		$this->paginaWeb = new Atributo("cadena", "NO", "", "ur_paginaWeb");
		$this->correoElectronicoContacto = new Atributo("cadena", "NO", "", "ur_correoElectronicoContacto");
		$this->planeacionFecha = new Atributo("fecha", "YES", "", "ur_planeacionFecha");
		$this->validacionFecha = new Atributo("fecha", "YES", "", "ur_validacionFecha");
		$this->mun_id = new Atributo("numero", "NO", "", "mun_id");
		$this->codigoPostal = new Atributo("numero", "NO", "", "ur_codigoPostal");
		$this->usr_id = new Atributo("numero", "NO", "", "usr_id");
	}

	/*******************************************************
	 * FUNCIONES ESTÁTICAS
	 ******************************************************/
	public static function abc($p)
	{
		$ur = new AuxiliarUnidadResponsable();
		if (isset($p->gpo_id)) {
			$ur->setGpo_id($p->gpo_id);
		}
		if (isset($p->ur_id)) {
			$ur->setUr_id($p->ur_id);
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
		if (isset($p->organigramaArchivo)) {
			$ur->setOrganigramaArchivo($p->organigramaArchivo);
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
		if (isset($p->domicilio)) {
			$ur->setDomicilio($p->domicilio);
		}
		if (isset($p->telefono)) {
			$ur->setTelefono($p->telefono);
		}
		if (isset($p->internoArchivo)) {
			$ur->setInternoArchivo($p->internoArchivo);
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
		if (isset($p->planeacionFecha)) {
			$ur->setPlaneacionFecha($p->planeacionFecha);
		}
		if (isset($p->validacionFecha)) {
			$ur->setValidacionFecha($p->validacionFecha);
		}
		if (isset($p->mun_id)) {
			$ur->setMun_id($p->mun_id);
		}
		if (isset($p->codigoPostal)) {
			$ur->setCodigoPostal($p->codigoPostal);
		}
		if (isset($p->usr_id)) {
			$ur->setUsr_id($p->usr_id);
		}

		switch ($p->abc) {
			case "a":
				$ur->insert();
				return $ur->getUltimoId();
				break;
			case "b":
				$ur->delete();
				break;
			case "c":
				$ur->update();
				break;
		}
	}
}
