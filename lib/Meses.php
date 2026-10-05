<?php
    require_once dirname(__FILE__)."/constantes.php";
    include_once dirname(__FILE__).'/Atributo.php';

    class Meses {	
	/******************************************************
	             DECLARACION DE VARIABLES                  
	******************************************************/	
	private $me; //Reflexion
	private $prefix; //clave presupuestal
	private $sufix; //tipo de clave
	private $tipo; //tipo de dato
	
	protected $ene;
	protected $feb;
	protected $mar;
	protected $abr;
	protected $may;
	protected $jun;
	protected $jul;
	protected $ago;
	protected $sep;
	protected $oct;
	protected $nov;
	protected $dic;
	
	
	/******************************************************        
	           DECLARACION FUNCIONES GET               
	******************************************************/
	public function getEne() { return $this->ene->getVal(); }
	public function getFeb() { return $this->feb->getVal(); }
	public function getMar() { return $this->mar->getVal(); }
	public function getAbr() { return $this->abr->getVal(); }
	public function getMay() { return $this->may->getVal(); }
	public function getJun() { return $this->jun->getVal(); }
	public function getJul() { return $this->jul->getVal(); }
	public function getAgo() { return $this->ago->getVal(); }
	public function getSep() { return $this->sep->getVal(); }
	public function getOct() { return $this->oct->getVal(); }
	public function getNov() { return $this->nov->getVal(); }
	public function getDic() { return $this->dic->getVal(); }
	public function getSumaMeses() {
		$tot = 0;
		$mes = array("ene","feb", "mar", "abr","may","jun","jul","ago","sep","oct","nov","dic");
		foreach( $mes as $val){ $tot += $this->$val->getVal(); }
		return $tot; 
	}
	public function getArrayMeses(){
		$a = array();
		$mes = array("ene","feb", "mar", "abr","may","jun","jul","ago","sep","oct","nov","dic");
		foreach( $mes as $val){ 
			if($this->$val->getVal()=="0.00" || empty($this->$val->getVal()) || $this->$val->getVal()==null ){
				array_push($a, "0"); 
			} else
			array_push($a,$this->$val->getVal()); 
		}
		return $a; 
	}
	protected function getMe() { return $this->me; }
	public function __get($name){ if( isset($this->$name) ) return $this->$name; }
	/******************************************************        
	           DECLARACION FUNCIONES SET               
	******************************************************/
	protected function setMe($val) { $this->me = $val; }
	public function __set($name, $val){
            if( isset($this->$name) ){
                $this->$name->setVal($val);  
                $this->$name->setChange(true);
            }           
        }
	
	public function setEne($val) { $this->ene->setVal($val); }
	public function setFeb($val) { $this->feb->setVal($val); }
	public function setMar($val) { $this->mar->setVal($val); }
	public function setAbr($val) { $this->abr->setVal($val); }
	public function setMay($val) { $this->may->setVal($val); }
	public function setJun($val) { $this->jun->setVal($val); }
	public function setJul($val) { $this->jul->setVal($val); }
	public function setAgo($val) { $this->ago->setVal($val); }
	public function setSep($val) { $this->sep->setVal($val); }
	public function setOct($val) { $this->oct->setVal($val); }
	public function setNov($val) { $this->nov->setVal($val); }
	public function setDic($val) { $this->dic->setVal($val); }
        
	public function setArrayMeses( $meses ){
            $mes = array("ene","feb", "mar", "abr","may","jun","jul","ago","sep","oct","nov","dic");
            foreach( $mes as $i=>$val){ $this->__set($val,$meses[$i]); }
	}
        
        public function setValMeses( $val ){
            $mes = array("ene","feb", "mar", "abr","may","jun","jul","ago","sep","oct","nov","dic");
            foreach( $mes as $key){ $this->__set($key,$val); }
	}
	/******************************************************        
	CONSTRUCTOR
	******************************************************/	
    public function  __construct(){
        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object)func_get_arg(0) : null) : null);
        
        $this->sufix = (!isset($prm->sufix)) ? '' : $prm->sufix;
        $this->prefix = (!isset($prm->prefix)) ? '' : $prm->prefix;
        $this->tipo = (!isset($prm->tipo)) ? 'numero' : $prm->tipo;
	
        //instanciando un espejo
        $this->setMe( new ReflectionClass($this) );
	
        /*se definen los atributos meses*/
        $mes = array("ene","feb", "mar", "abr","may","jun","jul","ago","sep","oct","nov","dic");
        foreach( $mes as $val){
            $this->$val = new Atributo($this->tipo,'YES','',$this->prefix.$val.$this->sufix); 
            $this->$val->staVal(0);
        }
	
        /*si trae la información de los meses*/
        foreach( $mes as $val) {
            if( isset( $prm->$val ) )
                $this->$val->setVal( $prm->$val );
        }
    }
}
