<?php

/**
 * Author: 
 * Created: December 3, 2018
 * Comment: 
 * Example: 
 * Class Parameters: 
 *     
 * Considerations: 
*/

require_once dirname(__FILE__)."/../../StoreProcedure.php";

class sp_GenerarLoteFederal extends StoreProcedure
{

    /******************************************************
                ATRIBUTOS DE LA CLASE
    ******************************************************/
    protected $modo_pago;
    protected $fecha_pago;
    protected $pro_ejercicio;
    protected $documento;
    protected $sis_id;
    protected $bit_observacion;
    protected $ejercicio;

    /******************************************************
                METODOS GET
     ******************************************************/
    public function getModo_pago()
    {
        return $this->modo_pago->getVal();
    }

    public function getFecha_pago()
    {
        return $this->fecha_pago->getVal();
    }

    public function getPro_ejercicio()
    {
        return $this->pro_ejercicio->getVal();
    }

    public function getDocumento()
    {
        return $this->documento->getVal();
    }

    public function getSis_id()
    {
        return $this->sis_id->getVal();
    }

    public function getBit_observacion()
    {
        return $this->bit_observacion;
    }

    public function getEjercicio()
    {
        return $this->ejercicio;
    }

    /******************************************************
                METODOS SET
     ******************************************************/
    public function setModo_pago($val)
    {
        $this->modo_pago->setVal($val);
    }

    public function setFecha_pago($val)
    {
        $this->fecha_pago->setVal($val);
    }

    public function setPro_ejercicio($val)
    {
        $this->pro_ejercicio->setVal($val);
    }

    public function setDocumento($val)
    {
        $this->documento->setVal($val);
    }

    public function setSis_id($val)
    {
        $this->sis_id = $val;
    }

    public function setBit_observacion($val)
    {
        $this->bit_observacion = $val;
    }

    public function setEjercicio($val)
    {
        $this->ejercicio = $val;
    }


    /******************************************************
                METODOS STA (SET sin persistencia)
    ******************************************************/
    public function staModo_pago($val)
    {
        $this->modo_pago->staVal($val);
    }

    public function staFecha_pago($val)
    {
        $this->fecha_pago->staVal($val);
    }

    public function staPro_ejercicio($val)
    {
        $this->pro_ejercicio->staVal($val);
    }

    public function staDocumento($val)
    {
        $this->documento->staVal($val);
    }

    public function staSis_id($val)
    {
        $this->sis_id = $val;
    }

    public function staBit_observacion($val)
    {
        $this->bit_observacion = $val;
    }

    public function staEjercicio($val)
    {
        $this->ejercicio = $val;
    }


    /******************************************************
                CONSTRUCTOR
    ******************************************************/
    public function  __construct()
    {
    
        parent::__construct(array("procedimientoAlmacenado"=> BD_TESORERIA.".sp_GenerarLoteFederal"));

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/
        $this->modo_pago = new Atributo( "numero", "NO", "PRI", "in_modo_pago", 4 );
        $this->fecha_pago = new Atributo( "fecha", "NO", "PRI", "in_fecha_pago", "date" );
        $this->pro_ejercicio = new Atributo( "numero", "NO", "PRI", "in_pro_ejercicio", 4 );
        $this->documento = new Atributo( "numero", "NO", "PRI", "in_documento", 1 );
        $this->sis_id = '';
        $this->bit_observacion = '';
        $this->ejercicio = '';
    }

    /******************************************************
                FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function call($p)
    {
    
        $generarLoteFederal = new sp_GenerarLoteFederal();
        if (isset($p->modo_pago))
            $generarLoteFederal->setModo_pago($p->modo_pago);
        if (isset($p->fecha_pago))
            $generarLoteFederal->setFecha_pago($p->fecha_pago);
        if (isset($p->pro_ejercicio))
            $generarLoteFederal->setPro_ejercicio($p->pro_ejercicio);
        if (isset($p->documento))
            $generarLoteFederal->setDocumento($p->documento);
        if (isset($p->bit_observacion))
            $generarLoteFederal->setBit_observacion($p->bit_observacion);

        if (isset($p->sis_id)) {
            $generarLoteFederal->setSis_id($p->sis_id);
        } else {
            $generarLoteFederal->setSis_id('');
        }//Agregar el sistema del mensaje que necesitas
        if (isset($p->ejercicio)) {
            $generarLoteFederal->setEjercicio($p->ejercicio);
        } else {
            $generarLoteFederal->setEjercicio(APP_EJERCICIO);
        }

        $generarLoteFederal->execute();

        $return = new stdClass();
        $return->bandera = $generarLoteFederal->getBandera();
        $return->mensaje = $generarLoteFederal->getMensaje();

        return $return;
    }

    /******************************************************
            FUNCIONES SOBRECARGA
     ******************************************************/
    public function execute($conectar = true, $error = false)
    {
    
        parent::execute();

        $return = new stdClass();
        $return->bandera = $this->getBandera();
        $return->mensaje = $this->getMensaje();

        return $return;
    }
}
