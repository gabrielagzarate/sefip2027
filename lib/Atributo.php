<?php
//require_once dirname(__FILE__)."/constantes.php";

class Atributo {
	/******************************************************
	             DECLARACION DE VARIABLES
	******************************************************/
	private $val;
	private $tipo;
	private $change = false;
	private $null;
	private $keys;
	private $name;
	private $longitud;
    
	/******************************************************
	           DECLARACION FUNCIONES GET
	******************************************************/
	public function getVal() { return $this->val; }
	public function getTipo() { return $this->tipo; }
	public function getChange() { return $this->change; }
	public function getNull() { return $this->null; }
	public function getKeys() { return $this->keys; }
	public function getName() { return $this->name; }
	public function getLongitud() { return $this->longitud; }
    
	/******************************************************
	           DECLARACION FUNCIONES SET
	******************************************************/
	public function setVal($val) { $this->val = $val; $this->change = true; }
	public function setTipo($val) { $this->tipo = $val; }
	public function setChange($val) { $this->change = $val; }
	public function setNull($val) { $this->null = $val; }
	public function setKeys($val) { $this->keys = $val; }
	public function setName($val) { $this->name = $val; }
	public function setLongitud($val) { $this->longitud = $val; }
    
    /******************************************************
                METODOS STA (SET sin persistencia)
    ******************************************************/
	public function staVal($val) { $this->val = $val; }
    
	/******************************************************
	           CONSTRUCTOR
	******************************************************/
    public function  __construct( $tipo, $null, $keys, $name, $longitud = 0 ){ 
		$this->tipo = $tipo;
		$this->null = $null;
		$this->keys = $keys;
		$this->name = $name;
		$this->longitud = $longitud;
	}
}
?>