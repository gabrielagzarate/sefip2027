<?php
    require_once dirname(__FILE__)."/../../Entidad.php";

    class CgCuenta extends Entidad{

    /******************************************************

            ATRIBUTOS DE LA CLASE

    ******************************************************/

    protected $ger_id;	//cat_GeneroConac - Llave foranea - Identificador del Genero CONAC
    protected $cgp_id;	//cat_GrupoConac - Llave foranea - Identificador del Grupo CONAC
    protected $rub_id;	//cat_RubroConac - Llave foranea - Identificador del Rubro CONAC
    protected $cgcu_id;	//Identificador de Cuenta de la Cuenta Contable
    protected $nombre;	//Nombre de la Cuenta de la Cuenta Contable
    protected $cuenta;	//Formato de la Cuenta Contable
    protected $descripcion;	//Descripción de la Cuenta Contable
    protected $usr_id;	//Identificador de usuario que captura/actualiza el registro

    /******************************************************

            METODOS GET

    ******************************************************/

    public function getGer_id() { return $this->ger_id->getVal(); }
    public function getCgp_id() { return $this->cgp_id->getVal(); }
    public function getRub_id() { return $this->rub_id->getVal(); }
    public function getCgcu_id() { return $this->cgcu_id->getVal(); }
    public function getNombre() { return $this->nombre->getVal(); }
    public function getCuenta() { return $this->cuenta->getVal(); }
    public function getDescripcion() { return $this->descripcion->getVal(); }
    public function getUsr_id() { return $this->usr_id->getVal(); }

    /******************************************************

            METODOS SET

    ******************************************************/

    public function setGer_id($val) { $this->ger_id->setVal($val); }
    public function setCgp_id($val) { $this->cgp_id->setVal($val); }
    public function setRub_id($val) { $this->rub_id->setVal($val); }
    public function setCgcu_id($val) { $this->cgcu_id->setVal($val); }
    public function setNombre($val) { $this->nombre->setVal($val); }
    public function setCuenta($val) { $this->cuenta->setVal($val); }
    public function setDescripcion($val) { $this->descripcion->setVal($val); }
    public function setUsr_id($val) { $this->usr_id->setVal($val); }

    /******************************************************

            METODOS STA (SET sin persistencia)

    ******************************************************/

    public function staGer_id($val) { $this->ger_id->staVal($val); }
    public function staCgp_id($val) { $this->cgp_id->staVal($val); }
    public function staRub_id($val) { $this->rub_id->staVal($val); }
    public function staCgcu_id($val) { $this->cgcu_id->staVal($val); }
    public function staNombre($val) { $this->nombre->staVal($val); }
    public function staCuenta($val) { $this->cuenta->staVal($val); }
    public function staDescripcion($val) { $this->descripcion->staVal($val); }
    public function staUsr_id($val) { $this->usr_id->staVal($val); }

    /******************************************************

            CONSTRUCTOR

    ******************************************************/

    public function  __construct(){

            parent::__construct(array("tabla"=>"cat_CgCuenta"));

            /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

            $this->ger_id = new Atributo("numero","NO","PRI","ger_id");
            $this->cgp_id = new Atributo("numero","NO","PRI","cgp_id");
            $this->rub_id = new Atributo("numero","NO","PRI","rub_id");
            $this->cgcu_id = new Atributo("numero","NO","PRI","cgcu_id");
            $this->nombre = new Atributo("cadena","NO","","cgcu_nombre");
            $this->cuenta = new Atributo("cadena","NO","","cgcu_cuenta");
            $this->descripcion = new Atributo("cadena","NO","","cgcu_descripcion");
            $this->usr_id = new Atributo("numero","NO","MUL","usr_id");

    }


    /******************************************************

            FUNCIONES ESTÁTICAS

    ******************************************************/

    public static function abc($p){
            $cgcu =  new CgCuenta();
            if( isset($p->ger_id) ) $cgcu->setGer_id($p->ger_id);
            if( isset($p->cgp_id) ) $cgcu->setCgp_id($p->cgp_id);
            if( isset($p->rub_id) ) $cgcu->setRub_id($p->rub_id);
            if( isset($p->cgcu_id) ) $cgcu->setCgcu_id($p->cgcu_id);
            if( isset($p->nombre) ) $cgcu->setNombre($p->nombre);
            if( isset($p->cuenta) ) $cgcu->setCuenta($p->cuenta);
            if( isset($p->descripcion) ) $cgcu->setDescripcion($p->descripcion);
            if( isset($p->usr_id) ) $cgcu->setUsr_id($p->usr_id);
            switch($p->abc){
                    case "a": $cgcu->insert(); return $cgcu->getUltimoId(); break;
                    case "b": $cgcu->delete(); break;
                    case "c": $cgcu->update(); break;
            }
    }

}

?>