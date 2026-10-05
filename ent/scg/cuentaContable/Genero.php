<?php

	require_once dirname(__FILE__)."/../../Entidad.php";

	class Genero extends Entidad{

	/****************************************************** 

		ATRIBUTOS DE LA CLASE

	******************************************************/ 

	protected $ger_id;	//Identificador del Genero de la Cuenta Contable
	protected $nombre;	//Nombre del Genero de la Cuenta Contable
	protected $cuenta;	//Formato de la Cuenta Contable
	protected $descripcion;	//Descripción de la Cuenta Contable
	protected $usr_id;	//Identificador de usuario que captura/actualiza el registro

	/****************************************************** 

		METODOS GET

	******************************************************/ 

	public function getGer_id() { return $this->ger_id->getVal(); }
	public function getNombre() { return $this->nombre->getVal(); }
	public function getCuenta() { return $this->cuenta->getVal(); }
	public function getDescripcion() { return $this->descripcion->getVal(); }
	public function getUsr_id() { return $this->usr_id->getVal(); }

	/****************************************************** 

		METODOS SET

	******************************************************/ 

	public function setGer_id($val) { $this->ger_id->setVal($val); }
	public function setNombre($val) { $this->nombre->setVal($val); }
	public function setCuenta($val) { $this->cuenta->setVal($val); }
	public function setDescripcion($val) { $this->descripcion->setVal($val); }
	public function setUsr_id($val) { $this->usr_id->setVal($val); }

	/****************************************************** 

		METODOS STA (SET sin persistencia)

	******************************************************/ 

	public function staGer_id($val) { $this->ger_id->staVal($val); }
	public function staNombre($val) { $this->nombre->staVal($val); }
	public function staCuenta($val) { $this->cuenta->staVal($val); }
	public function staDescripcion($val) { $this->descripcion->staVal($val); }
	public function staUsr_id($val) { $this->usr_id->staVal($val); }

	/****************************************************** 

		CONSTRUCTOR

	******************************************************/ 

	public function  __construct(){

		parent::__construct(array("tabla"=>"cat_Genero"));

		/*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

		$this->ger_id = new Atributo("numero","NO","PRI","ger_id");
		$this->nombre = new Atributo("cadena","NO","","ger_nombre");
		$this->cuenta = new Atributo("cadena","NO","","ger_cuenta");
		$this->descripcion = new Atributo("cadena","NO","","ger_descripcion");
		$this->usr_id = new Atributo("numero","NO","MUL","usr_id");

	}


	/****************************************************** 

		FUNCIONES ESTÁTICAS

	******************************************************/ 

	public static function abc($p){
		$ger =  new Genero();
		if( isset($p->ger_id) ) $ger->setGer_id($p->ger_id);
		if( isset($p->nombre) ) $ger->setNombre($p->nombre);
		if( isset($p->cuenta) ) $ger->setCuenta($p->cuenta);
		if( isset($p->descripcion) ) $ger->setDescripcion($p->descripcion);
		if( isset($p->usr_id) ) $ger->setUsr_id($p->usr_id);
		switch($p->abc){
			case "a": $ger->insert(); return $ger->getUltimoId(); break;
			case "b": $ger->delete(); break;
			case "c": $ger->update(); break;
		}
	}

}

?>