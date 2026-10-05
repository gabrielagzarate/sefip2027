<?php

require_once dirname(__FILE__)."/Entidad.php";

class UnidadResponsableFirma extends Entidad
{
	/*******************************************************
	 * ATRIBUTOS DE LA CLASE
	 ******************************************************/
	protected $gpo_id; //
	protected $ur_id; //
	protected $nombramientoTitular; //
	protected $urf_identificacionTitular; //
	protected $firmaTitular; //
	protected $nombramientoDelegatorioTitular; //
	protected $urf_identificacionDelegatorioTitular; //
	protected $firmaDelegatorioTitular; //
	protected $acuerdoDelegatorioTitular; //
	protected $nombramientoAdministrativo; //
	protected $urf_identificacionAdministrativo; //
	protected $firmaAdministrativo; //
	protected $nombramientoDelegatorioAdministrativo; //
	protected $urf_identificacionDelegatorioAdministrativo; //
	protected $firmaDelegatorioAdministrativo; //
	protected $acuerdoDelegatorioAdministrativo; //
	protected $nombramientoPlaneacion; // Nombramiento de la persona de planeacion
	protected $urf_identificacionPlaneacion; // IFE INE de la persona de planeacion
	protected $firmaPlaneacion; // IFE INE de la persona de planeacion
	protected $nombramientoEvaluacion; // Nombreamiento de la persona encargada d ela evaluaciòn  y seguimiento del desempeño ( ruta pdf)
	protected $urf_identificacionEvaluacion; // identificacion de la persona encargada d ela evaluaciòn  y seguimiento del desempeño ( ruta pdf)
	protected $firmaEvaluacion; // Firma de la persona encargada d ela evaluaciòn  y seguimiento del desempeño ( ruta pdf)
	protected $usr_id; //

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

	public function getNombramientoTitular()
	{
		return $this->nombramientoTitular->getVal();
	}

	public function getUrf_identificacionTitular()
	{
		return $this->urf_identificacionTitular->getVal();
	}

	public function getFirmaTitular()
	{
		return $this->firmaTitular->getVal();
	}

	public function getNombramientoDelegatorioTitular()
	{
		return $this->nombramientoDelegatorioTitular->getVal();
	}

	public function getUrf_identificacionDelegatorioTitular()
	{
		return $this->urf_identificacionDelegatorioTitular->getVal();
	}

	public function getFirmaDelegatorioTitular()
	{
		return $this->firmaDelegatorioTitular->getVal();
	}

	public function getAcuerdoDelegatorioTitular()
	{
		return $this->acuerdoDelegatorioTitular->getVal();
	}

	public function getNombramientoAdministrativo()
	{
		return $this->nombramientoAdministrativo->getVal();
	}

	public function getUrf_identificacionAdministrativo()
	{
		return $this->urf_identificacionAdministrativo->getVal();
	}

	public function getFirmaAdministrativo()
	{
		return $this->firmaAdministrativo->getVal();
	}

	public function getNombramientoDelegatorioAdministrativo()
	{
		return $this->nombramientoDelegatorioAdministrativo->getVal();
	}

	public function getUrf_identificacionDelegatorioAdministrativo()
	{
		return $this->urf_identificacionDelegatorioAdministrativo->getVal();
	}

	public function getFirmaDelegatorioAdministrativo()
	{
		return $this->firmaDelegatorioAdministrativo->getVal();
	}

	public function getAcuerdoDelegatorioAdministrativo()
	{
		return $this->acuerdoDelegatorioAdministrativo->getVal();
	}

	public function getNombramientoPlaneacion()
	{
		return $this->nombramientoPlaneacion->getVal();
	}

	public function getUrf_identificacionPlaneacion()
	{
		return $this->urf_identificacionPlaneacion->getVal();
	}

	public function getFirmaPlaneacion()
	{
		return $this->firmaPlaneacion->getVal();
	}

	public function getNombramientoEvaluacion()
	{
		return $this->nombramientoEvaluacion->getVal();
	}

	public function getUrf_identificacionEvaluacion()
	{
		return $this->urf_identificacionEvaluacion->getVal();
	}

	public function getFirmaEvaluacion()
	{
		return $this->firmaEvaluacion->getVal();
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

	public function setNombramientoTitular($val)
	{
		$this->nombramientoTitular->setVal($val);
	}

	public function setUrf_identificacionTitular($val)
	{
		$this->urf_identificacionTitular->setVal($val);
	}

	public function setFirmaTitular($val)
	{
		$this->firmaTitular->setVal($val);
	}

	public function setNombramientoDelegatorioTitular($val)
	{
		$this->nombramientoDelegatorioTitular->setVal($val);
	}

	public function setUrf_identificacionDelegatorioTitular($val)
	{
		$this->urf_identificacionDelegatorioTitular->setVal($val);
	}

	public function setFirmaDelegatorioTitular($val)
	{
		$this->firmaDelegatorioTitular->setVal($val);
	}

	public function setAcuerdoDelegatorioTitular($val)
	{
		$this->acuerdoDelegatorioTitular->setVal($val);
	}

	public function setNombramientoAdministrativo($val)
	{
		$this->nombramientoAdministrativo->setVal($val);
	}

	public function setUrf_identificacionAdministrativo($val)
	{
		$this->urf_identificacionAdministrativo->setVal($val);
	}

	public function setFirmaAdministrativo($val)
	{
		$this->firmaAdministrativo->setVal($val);
	}

	public function setNombramientoDelegatorioAdministrativo($val)
	{
		$this->nombramientoDelegatorioAdministrativo->setVal($val);
	}

	public function setUrf_identificacionDelegatorioAdministrativo($val)
	{
		$this->urf_identificacionDelegatorioAdministrativo->setVal($val);
	}

	public function setFirmaDelegatorioAdministrativo($val)
	{
		$this->firmaDelegatorioAdministrativo->setVal($val);
	}

	public function setAcuerdoDelegatorioAdministrativo($val)
	{
		$this->acuerdoDelegatorioAdministrativo->setVal($val);
	}

	public function setNombramientoPlaneacion($val)
	{
		$this->nombramientoPlaneacion->setVal($val);
	}

	public function setUrf_identificacionPlaneacion($val)
	{
		$this->urf_identificacionPlaneacion->setVal($val);
	}

	public function setFirmaPlaneacion($val)
	{
		$this->firmaPlaneacion->setVal($val);
	}

	public function setNombramientoEvaluacion($val)
	{
		$this->nombramientoEvaluacion->setVal($val);
	}

	public function setUrf_identificacionEvaluacion($val)
	{
		$this->urf_identificacionEvaluacion->setVal($val);
	}

	public function setFirmaEvaluacion($val)
	{
		$this->firmaEvaluacion->setVal($val);
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

	public function staNombramientoTitular($val)
	{
		$this->nombramientoTitular->staVal($val);
	}

	public function staUrf_identificacionTitular($val)
	{
		$this->urf_identificacionTitular->staVal($val);
	}

	public function staFirmaTitular($val)
	{
		$this->firmaTitular->staVal($val);
	}

	public function staNombramientoDelegatorioTitular($val)
	{
		$this->nombramientoDelegatorioTitular->staVal($val);
	}

	public function staUrf_identificacionDelegatorioTitular($val)
	{
		$this->urf_identificacionDelegatorioTitular->staVal($val);
	}

	public function staFirmaDelegatorioTitular($val)
	{
		$this->firmaDelegatorioTitular->staVal($val);
	}

	public function staAcuerdoDelegatorioTitular($val)
	{
		$this->acuerdoDelegatorioTitular->staVal($val);
	}

	public function staNombramientoAdministrativo($val)
	{
		$this->nombramientoAdministrativo->staVal($val);
	}

	public function staUrf_identificacionAdministrativo($val)
	{
		$this->urf_identificacionAdministrativo->staVal($val);
	}

	public function staFirmaAdministrativo($val)
	{
		$this->firmaAdministrativo->staVal($val);
	}

	public function staNombramientoDelegatorioAdministrativo($val)
	{
		$this->nombramientoDelegatorioAdministrativo->staVal($val);
	}

	public function staUrf_identificacionDelegatorioAdministrativo($val)
	{
		$this->urf_identificacionDelegatorioAdministrativo->staVal($val);
	}

	public function staFirmaDelegatorioAdministrativo($val)
	{
		$this->firmaDelegatorioAdministrativo->staVal($val);
	}

	public function staAcuerdoDelegatorioAdministrativo($val)
	{
		$this->acuerdoDelegatorioAdministrativo->staVal($val);
	}

	public function staNombramientoPlaneacion($val)
	{
		$this->nombramientoPlaneacion->staVal($val);
	}

	public function staUrf_identificacionPlaneacion($val)
	{
		$this->urf_identificacionPlaneacion->staVal($val);
	}

	public function staFirmaPlaneacion($val)
	{
		$this->firmaPlaneacion->staVal($val);
	}

	public function staNombramientoEvaluacion($val)
	{
		$this->nombramientoEvaluacion->staVal($val);
	}

	public function staUrf_identificacionEvaluacion($val)
	{
		$this->urf_identificacionEvaluacion->staVal($val);
	}

	public function staFirmaEvaluacion($val)
	{
		$this->firmaEvaluacion->staVal($val);
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
		parent::__construct(array("tabla" => "cat_UnidadResponsableFirma"));

		/*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
		$this->gpo_id = new Atributo("numero", "NO", "PRI", "gpo_id");
		$this->ur_id = new Atributo("numero", "NO", "PRI", "ur_id");
		$this->nombramientoTitular = new Atributo("cadena", "NO", "", "urf_nombramientoTitular");
		$this->urf_identificacionTitular = new Atributo("cadena", "NO", "", "urf_identificacionTitular");
		$this->firmaTitular = new Atributo("cadena", "NO", "", "urf_firmaTitular");
		$this->nombramientoDelegatorioTitular = new Atributo("cadena", "NO", "", "urf_nombramientoDelegatorioTitular");
		$this->urf_identificacionDelegatorioTitular = new Atributo("cadena", "NO", "", "urf_identificacionDelegatorioTitular");
		$this->firmaDelegatorioTitular = new Atributo("cadena", "NO", "", "urf_firmaDelegatorioTitular");
		$this->acuerdoDelegatorioTitular = new Atributo("cadena", "NO", "", "urf_acuerdoDelegatorioTitular");
		$this->nombramientoAdministrativo = new Atributo("cadena", "NO", "", "urf_nombramientoAdministrativo");
		$this->urf_identificacionAdministrativo = new Atributo("cadena", "NO", "", "urf_identificacionAdministrativo");
		$this->firmaAdministrativo = new Atributo("cadena", "NO", "", "urf_firmaAdministrativo");
		$this->nombramientoDelegatorioAdministrativo = new Atributo("cadena", "NO", "", "urf_nombramientoDelegatorioAdministrativo");
		$this->urf_identificacionDelegatorioAdministrativo = new Atributo("cadena", "NO", "", "urf_identificacionDelegatorioAdministrativo");
		$this->firmaDelegatorioAdministrativo = new Atributo("cadena", "NO", "", "urf_firmaDelegatorioAdministrativo");
		$this->acuerdoDelegatorioAdministrativo = new Atributo("cadena", "NO", "", "urf_acuerdoDelegatorioAdministrativo");
		$this->nombramientoPlaneacion = new Atributo("cadena", "YES", "", "urf_nombramientoPlaneacion");
		$this->urf_identificacionPlaneacion = new Atributo("cadena", "YES", "", "urf_identificacionPlaneacion");
		$this->firmaPlaneacion = new Atributo("cadena", "YES", "", "urf_firmaPlaneacion");
		$this->nombramientoEvaluacion = new Atributo("cadena", "YES", "", "urf_nombramientoEvaluacion");
		$this->urf_identificacionEvaluacion = new Atributo("cadena", "YES", "", "urf_identificacionEvaluacion");
		$this->firmaEvaluacion = new Atributo("cadena", "YES", "", "urf_firmaEvaluacion");
		$this->usr_id = new Atributo("numero", "NO", "", "usr_id");
	}

	/*******************************************************
	 * FUNCIONES ESTÁTICAS
	 ******************************************************/
	public static function abc($p)
	{
		$urf = new UnidadResponsableFirma();

		if (isset($p->gpo_id)) {
			$urf->setGpo_id($p->gpo_id);
		}
		if (isset($p->ur_id)) {
			$urf->setUr_id($p->ur_id);
		}
		if (isset($p->nombramientoTitular)) {
			$urf->setNombramientoTitular($p->nombramientoTitular);
		}
		if (isset($p->urf_identificacionTitular)) {
			$urf->setUrf_identificacionTitular($p->urf_identificacionTitular);
		}
		if (isset($p->firmaTitular)) {
			$urf->setFirmaTitular($p->firmaTitular);
		}
		if (isset($p->nombramientoDelegatorioTitular)) {
			$urf->setNombramientoDelegatorioTitular($p->nombramientoDelegatorioTitular);
		}
		if (isset($p->urf_identificacionDelegatorioTitular)) {
			$urf->setUrf_identificacionDelegatorioTitular($p->urf_identificacionDelegatorioTitular);
		}
		if (isset($p->firmaDelegatorioTitular)) {
			$urf->setFirmaDelegatorioTitular($p->firmaDelegatorioTitular);
		}
		if (isset($p->acuerdoDelegatorioTitular)) {
			$urf->setAcuerdoDelegatorioTitular($p->acuerdoDelegatorioTitular);
		}
		if (isset($p->nombramientoAdministrativo)) {
			$urf->setNombramientoAdministrativo($p->nombramientoAdministrativo);
		}
		if (isset($p->urf_identificacionAdministrativo)) {
			$urf->setUrf_identificacionAdministrativo($p->urf_identificacionAdministrativo);
		}
		if (isset($p->firmaAdministrativo)) {
			$urf->setFirmaAdministrativo($p->firmaAdministrativo);
		}
		if (isset($p->nombramientoDelegatorioAdministrativo)) {
			$urf->setNombramientoDelegatorioAdministrativo($p->nombramientoDelegatorioAdministrativo);
		}
		if (isset($p->urf_identificacionDelegatorioAdministrativo)) {
			$urf->setUrf_identificacionDelegatorioAdministrativo($p->urf_identificacionDelegatorioAdministrativo);
		}
		if (isset($p->firmaDelegatorioAdministrativo)) {
			$urf->setFirmaDelegatorioAdministrativo($p->firmaDelegatorioAdministrativo);
		}
		if (isset($p->acuerdoDelegatorioAdministrativo)) {
			$urf->setAcuerdoDelegatorioAdministrativo($p->acuerdoDelegatorioAdministrativo);
		}
		if (isset($p->nombramientoPlaneacion)) {
			$urf->setNombramientoPlaneacion($p->nombramientoPlaneacion);
		}
		if (isset($p->urf_identificacionPlaneacion)) {
			$urf->setUrf_identificacionPlaneacion($p->urf_identificacionPlaneacion);
		}
		if (isset($p->firmaPlaneacion)) {
			$urf->setFirmaPlaneacion($p->firmaPlaneacion);
		}
		if (isset($p->nombramientoEvaluacion)) {
			$urf->setNombramientoEvaluacion($p->nombramientoEvaluacion);
		}
		if (isset($p->urf_identificacionEvaluacion)) {
			$urf->setUrf_identificacionEvaluacion($p->urf_identificacionEvaluacion);
		}
		if (isset($p->firmaEvaluacion)) {
			$urf->setFirmaEvaluacion($p->firmaEvaluacion);
		}
		if (isset($p->usr_id)) {
			$urf->setUsr_id($p->usr_id);
		}

		switch ($p->abc) {
			case "a":
				$urf->insert();
				return $urf->getUltimoId();
				break;
			case "b":
				$urf->delete();
				break;
			case "c":
				$urf->update();
				break;
		}
	}
}
