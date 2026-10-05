<?php

	require_once dirname(__FILE__)."/../../Entidad.php";

	class EntePublicoLDF extends Entidad{

	/****************************************************** 

		ATRIBUTOS DE LA CLASE

	******************************************************/ 

	protected $ent_id;	//Identificador del Ente Público
	protected $nombre;	//Describe nombre del Ente Publico
	protected $encabezado;	//Nombre del encabezado Sector del Reporte
	protected $usr_id;	//Identificador de usuario que captura/actualiza el registro

	/****************************************************** 

		METODOS GET

	******************************************************/ 

	public function getEnt_id() { return $this->ent_id->getVal(); }
	public function getNombre() { return $this->nombre->getVal(); }
	public function getEncabezado() { return $this->encabezado->getVal(); }
	public function getUsr_id() { return $this->usr_id->getVal(); }

	/****************************************************** 

		METODOS SET

	******************************************************/ 

	public function setEnt_id($val) { $this->ent_id->setVal($val); }
	public function setNombre($val) { $this->nombre->setVal($val); }
	public function setEncabezado($val) { $this->encabezado->setVal($val); }
	public function setUsr_id($val) { $this->usr_id->setVal($val); }

	/****************************************************** 

		METODOS STA (SET sin persistencia)

	******************************************************/ 

	public function staEnt_id($val) { $this->ent_id->staVal($val); }
	public function staNombre($val) { $this->nombre->staVal($val); }
	public function staEncabezado($val) { $this->encabezado->staVal($val); }
	public function staUsr_id($val) { $this->usr_id->staVal($val); }

	/****************************************************** 

		CONSTRUCTOR

	******************************************************/ 

	public function  __construct(){

		parent::__construct(array("tabla"=>"ldf.cat_EntePublicoLDF"));

		/*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

		$this->ent_id = new Atributo("numero","NO","PRI","ent_id");
		$this->nombre = new Atributo("cadena","NO","","ent_nombre");
		$this->encabezado = new Atributo("cadena","NO","","ent_encabezado");
		$this->usr_id = new Atributo("numero","NO","","usr_id");

	}


	/****************************************************** 

		FUNCIONES ESTÁTICAS

	******************************************************/ 

	public static function abc($p){
		$ent =  new EntePublicoLDF();
		if( isset($p->ent_id) ) $ent->setEnt_id($p->ent_id);
		if( isset($p->nombre) ) $ent->setNombre($p->nombre);
		if( isset($p->encabezado) ) $ent->setEncabezado($p->encabezado);
		if( isset($p->usr_id) ) $ent->setUsr_id($p->usr_id);
		switch($p->abc){
			case "a": $ent->insert(); return $ent->getUltimoId(); break;
			case "b": $ent->delete(); break;
			case "c": $ent->update(); break;
		}
	}

}

?>