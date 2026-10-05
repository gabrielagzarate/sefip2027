<?php

/**
 * Author: 
 * Created: February 28, 2018
 * Comment: 
 * Example: 
 * Class Parameters: 
 *     
 * Considerations: 
*/

require_once dirname(__FILE__)."/../../StoreProcedure.php";

class sp_CancelarMinistrarClc2019 extends StoreProcedure{

    /******************************************************
                 ATRIBUTOS DE LA CLASE
    ******************************************************/
    protected $gpo_id;	//
    protected $ur_id;	//
    protected $clc_id;	//
    protected $sis_id;	//
    protected $bit_observacion;	//
    protected $ejercicio;	//

    /******************************************************
                      METODOS GET
     ******************************************************/
    public function getGpo_id() { return $this->gpo_id->getVal(); }
    public function getUr_id() { return $this->ur_id->getVal(); }
    public function getClc_id() { return $this->clc_id->getVal(); }
    public function getSis_id() { return $this->sis_id->getVal(); }
    public function getBit_observacion() { return $this->bit_observacion; }
    public function getEjercicio() { return $this->ejercicio; }

    /******************************************************
                      METODOS SET
     ******************************************************/
    public function setGpo_id($val) { $this->gpo_id->setVal($val); }
    public function setUr_id($val) { $this->ur_id->setVal($val); }
    public function setClc_id($val) { $this->clc_id->setVal($val); }
    public function setSis_id($val) { $this->sis_id = $val; }
    public function setBit_observacion($val) { $this->bit_observacion = $val; }
    public function setEjercicio($val) { $this->ejercicio = $val; }

    /******************************************************
                METODOS STA (SET sin persistencia)
    ******************************************************/
    public function staGpo_id($val) { $this->gpo_id->staVal($val); }
    public function staUr_id($val) { $this->ur_id->staVal($val); }
    public function staClc_id($val) { $this->clc_id->staVal($val); }
    public function staSis_id($val) { $this->sis_id = $val; }
    public function staBit_observacion($val) { $this->bit_observacion = $val; }
    public function staEjercicio($val) { $this->ejercicio = $val; }

    /******************************************************
                        CONSTRUCTOR
    ******************************************************/
    public function  __construct(){
    
        parent::__construct(array("procedimientoAlmacenado"=>"tesoreria.sp_CancelarMinistrarClc2019_"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->gpo_id = new Atributo( "numero", "NO", "PRI", "in_gpo_id", 1 );
        $this->ur_id = new Atributo( "numero", "NO", "PRI", "in_ur_id", 2 );
        $this->clc_id = new Atributo( "numero", "NO", "PRI", "in_clc_id", 10 );
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
                   FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p){
    
        $cancelarMinistrarClc =  new sp_CancelarMinistrarClc2019();
        if( isset($p->gpo_id) ) $cancelarMinistrarClc->setGpo_id($p->gpo_id);
        if( isset($p->ur_id) ) $cancelarMinistrarClc->setUr_id($p->ur_id);
        if( isset($p->clc_id) ) $cancelarMinistrarClc->setClc_id($p->clc_id);
        if( isset($p->bit_observacion) ) $cancelarMinistrarClc->setBit_observacion($p->bit_observacion);
        if( isset($p->sis_id) ){ $cancelarMinistrarClc->setSis_id($p->sis_id); }else{ $cancelarMinistrarClc->setSis_id(''); }//Agregar el sistema del mensaje que necesitas
        if( isset($p->ejercicio) ){ $cancelarMinistrarClc->setEjercicio($p->ejercicio); }else{ $cancelarMinistrarClc->setEjercicio(2022); }

        $cancelarMinistrarClc->execute();

        $return = new stdClass();
        $return->bandera = $cancelarMinistrarClc->getBandera();
        $return->mensaje = $cancelarMinistrarClc->getMensaje();

        return $return;
    }

    /******************************************************
                   FUNCIONES SOBRECARGA
     ******************************************************/
    public function execute($conectar = true, $error = false){
    
        parent::execute();

        $return = new stdClass();
        $return->bandera = $this->getBandera();
        $return->mensaje = $this->getMensaje();

        return $return;
    }

}
?>