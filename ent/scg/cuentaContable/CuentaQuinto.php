<?php

	require_once dirname(__FILE__)."/../../Entidad.php";

	class CuentaQuinto extends Entidad{

	/****************************************************** 

		ATRIBUTOS DE LA CLASE

	******************************************************/ 

	protected $ger_id;	//
	protected $cgp_id;	//
	protected $rub_id;	//
	protected $cgcu_id;	//
	protected $cgsc_id;	//
	protected $cgc_id;	//
	protected $cgq_id;	//Identificador del septimo nivel de la Cuenta Contable
	protected $cuenta;	//Formato de la Cuenta Contable
	protected $nombre;	//Nombre de la Cuenta Contable
	protected $descripcion;	//Descripción de la Cuenta Maestro de Ingresos de la Cuenta Contable
	protected $usr_id;	//Identificador de usuario que captura/actualiza el registro

	/****************************************************** 

		METODOS GET

	******************************************************/ 

	public function getGer_id() { return $this->ger_id->getVal(); }
	public function getCgp_id() { return $this->cgp_id->getVal(); }
	public function getRub_id() { return $this->rub_id->getVal(); }
	public function getCgcu_id() { return $this->cgcu_id->getVal(); }
	public function getCgsc_id() { return $this->cgsc_id->getVal(); }
	public function getCgc_id() { return $this->cgc_id->getVal(); }
	public function getCgq_id() { return $this->cgq_id->getVal(); }
	public function getCuenta() { return $this->cuenta->getVal(); }
	public function getNombre() { return $this->nombre->getVal(); }
	public function getDescripcion() { return $this->descripcion->getVal(); }
	public function getUsr_id() { return $this->usr_id->getVal(); }

	/****************************************************** 

		METODOS SET

	******************************************************/ 

	public function setGer_id($val) { $this->ger_id->setVal($val); }
	public function setCgp_id($val) { $this->cgp_id->setVal($val); }
	public function setRub_id($val) { $this->rub_id->setVal($val); }
	public function setCgcu_id($val) { $this->cgcu_id->setVal($val); }
	public function setCgsc_id($val) { $this->cgsc_id->setVal($val); }
	public function setCgc_id($val) { $this->cgc_id->setVal($val); }
	public function setCgq_id($val) { $this->cgq_id->setVal($val); }
	public function setCuenta($val) { $this->cuenta->setVal($val); }
	public function setNombre($val) { $this->nombre->setVal($val); }
	public function setDescripcion($val) { $this->descripcion->setVal($val); }
	public function setUsr_id($val) { $this->usr_id->setVal($val); }

	/****************************************************** 

		METODOS STA (SET sin persistencia)

	******************************************************/ 

	public function staGer_id($val) { $this->ger_id->staVal($val); }
	public function staCgp_id($val) { $this->cgp_id->staVal($val); }
	public function staRub_id($val) { $this->rub_id->staVal($val); }
	public function staCgcu_id($val) { $this->cgcu_id->staVal($val); }
	public function staCgsc_id($val) { $this->cgsc_id->staVal($val); }
	public function staCgc_id($val) { $this->cgc_id->staVal($val); }
	public function staCgq_id($val) { $this->cgq_id->staVal($val); }
	public function staCuenta($val) { $this->cuenta->staVal($val); }
	public function staNombre($val) { $this->nombre->staVal($val); }
	public function staDescripcion($val) { $this->descripcion->staVal($val); }
	public function staUsr_id($val) { $this->usr_id->staVal($val); }

	/****************************************************** 

		CONSTRUCTOR

	******************************************************/ 

	public function  __construct(){

		parent::__construct(array("tabla"=>"cg_CuentaQuinto"));

		/*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

		$this->ger_id = new Atributo("numero","NO","PRI","ger_id");
		$this->cgp_id = new Atributo("numero","NO","PRI","cgp_id");
		$this->rub_id = new Atributo("numero","NO","PRI","rub_id");
		$this->cgcu_id = new Atributo("numero","NO","PRI","cgcu_id");
		$this->cgsc_id = new Atributo("numero","NO","PRI","cgsc_id");
		$this->cgc_id = new Atributo("numero","NO","PRI","cgc_id");
		$this->cgq_id = new Atributo("numero","NO","PRI","cgq_id");
		$this->cuenta = new Atributo("cadena","NO","UNI","cgq_cuenta");
		$this->nombre = new Atributo("cadena","NO","","cgq_nombre");
		$this->descripcion = new Atributo("cadena","NO","","cgq_descripcion");
		$this->usr_id = new Atributo("numero","NO","MUL","usr_id");

	}


	/****************************************************** 

		FUNCIONES ESTÁTICAS

	******************************************************/ 

	public static function abc($p){
		$cgq =  new CuentaQuinto();
		if( isset($p->ger_id) ) $cgq->setGer_id($p->ger_id);
		if( isset($p->cgp_id) ) $cgq->setCgp_id($p->cgp_id);
		if( isset($p->rub_id) ) $cgq->setRub_id($p->rub_id);
		if( isset($p->cgcu_id) ) $cgq->setCgcu_id($p->cgcu_id);
		if( isset($p->cgsc_id) ) $cgq->setCgsc_id($p->cgsc_id);
		if( isset($p->cgc_id) ) $cgq->setCgc_id($p->cgc_id);
		if( isset($p->cgq_id) ) $cgq->setCgq_id($p->cgq_id);
		if( isset($p->cuenta) ) $cgq->setCuenta($p->cuenta);
		if( isset($p->nombre) ) $cgq->setNombre($p->nombre);
		if( isset($p->descripcion) ) $cgq->setDescripcion($p->descripcion);
		if( isset($p->usr_id) ) $cgq->setUsr_id($p->usr_id);
		switch($p->abc){
			case "a": $cgq->insert(); return $cgq->getUltimoId(); break;
			case "b": $cgq->delete(); break;
			case "c": $cgq->update(); break;
		}
	}

}

?>