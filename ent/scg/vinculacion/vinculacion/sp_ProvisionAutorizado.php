<?php

/**
 * Author: 
 * Created: March 14, 2018
 * Comment: 
 * Example: 
 * Class Parameters: 
 *     
 * Considerations: 
*/

require_once dirname(__FILE__)."/../../../StoreProcedure.php";

class sp_ProvisionAutorizado extends StoreProcedure{

    /******************************************************
                 ATRIBUTOS DE LA CLASE
    ******************************************************/
    protected $gpo_id;	//
    protected $ur_id;	//
    protected $agrupador;	//
    protected $pma_fecha;	//
    protected $tmc_id;	//
    protected $sis_id;	//
    protected $bit_observacion;	//
    protected $ejercicio;	//

    /******************************************************
                      METODOS GET
     ******************************************************/
    public function getGpo_id() { return $this->gpo_id->getVal(); }
    public function getUr_id() { return $this->ur_id->getVal(); }
    public function getAgrupador() { return $this->agrupador->getVal(); }
    public function getPma_fecha() { return $this->pma_fecha->getVal(); }
    public function getTmc_id() { return $this->tmc_id->getVal(); }
    public function getSis_id() { return $this->sis_id->getVal(); }
    public function getBit_observacion() { return $this->bit_observacion; }
    public function getEjercicio() { return $this->ejercicio; }

    /******************************************************
                      METODOS SET
     ******************************************************/
    public function setGpo_id($val) { $this->gpo_id->setVal($val); }
    public function setUr_id($val) { $this->ur_id->setVal($val); }
    public function setAgrupador($val) { $this->agrupador->setVal($val); }
    public function setPma_fecha($val) { $this->pma_fecha->setVal($val); }
    public function setTmc_id($val) { $this->tmc_id->setVal($val); }
    public function setSis_id($val) { $this->sis_id = $val; }
    public function setBit_observacion($val) { $this->bit_observacion = $val; }
    public function setEjercicio($val) { $this->ejercicio = $val; }

    /******************************************************
                METODOS STA (SET sin persistencia)
    ******************************************************/
    public function staGpo_id($val) { $this->gpo_id->staVal($val); }
    public function staUr_id($val) { $this->ur_id->staVal($val); }
    public function staAgrupador($val) { $this->agrupador->staVal($val); }
    public function staPma_fecha($val) { $this->pma_fecha->staVal($val); }
    public function staTmc_id($val) { $this->tmc_id->staVal($val); }
    public function staSis_id($val) { $this->sis_id = $val; }
    public function staBit_observacion($val) { $this->bit_observacion = $val; }
    public function staEjercicio($val) { $this->ejercicio = $val; }

    /******************************************************
                        CONSTRUCTOR
    ******************************************************/
    public function  __construct(){
    
        parent::__construct(array("procedimientoAlmacenado"=>"sp_ProvisionAutorizado"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->gpo_id = new Atributo( "numero", "NO", "PRI", "in_gpo_id", 1 );
        $this->ur_id = new Atributo( "numero", "NO", "PRI", "in_ur_id", 2 );
        $this->agrupador = new Atributo( "cadena", "NO", "PRI", "in_agrupador", 75 );
        $this->pma_fecha = new Atributo( "fecha", "NO", "PRI", "in_pma_fecha", "date" );
        $this->tmc_id = new Atributo( "caracter", "NO", "PRI", "in_tmc_id", 1 );
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
                   FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p){
    
        $provisionAutorizado =  new sp_ProvisionAutorizado();
        if( isset($p->gpo_id) ) $provisionAutorizado->setGpo_id($p->gpo_id);
        if( isset($p->ur_id) ) $provisionAutorizado->setUr_id($p->ur_id);
        if( isset($p->agrupador) ) $provisionAutorizado->setAgrupador($p->agrupador);
        if( isset($p->pma_fecha) ) $provisionAutorizado->setPma_fecha($p->pma_fecha);
        if( isset($p->tmc_id) ) $provisionAutorizado->setTmc_id($p->tmc_id);
        if( isset($p->bit_observacion) ) $provisionAutorizado->setBit_observacion($p->bit_observacion);
        if( isset($p->sis_id) ){ $provisionAutorizado->setSis_id($p->sis_id); }else{ $provisionAutorizado->setSis_id(''); }//Agregar el sistema del mensaje que necesitas
        if( isset($p->ejercicio) ){ $provisionAutorizado->setEjercicio($p->ejercicio); }else{ $provisionAutorizado->setEjercicio(2024); }

        $provisionAutorizado->execute();

        $return = new stdClass();
        $return->bandera = $provisionAutorizado->getBandera();
        $return->mensaje = $provisionAutorizado->getMensaje();

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