<?php

require_once __DIR__ . '/../../../lib/constantes.php';
require_once __DIR__ . '/../../Entidad.php';
require_once __DIR__ . '/../../../lib/Meses.php';

class Alimentacion extends Entidad
{
    /******************************************************

    ATRIBUTOS DE LA CLASE

     ******************************************************/

    public $ccd_id;
    public $ced_id;
    public $gpo_id;
    public $ur_id;
    public $ue_id;
    public $epg_id; //Encuadre de Estructura programática
    public $pg_id;
    public $pe_id;
    public $ecf_id; //Encuadre Clasificación de Financiamiento
    public $gbf_id;
    public $asr_id;
    public $ali_beneficiarios;
    public $ali_cuotaDiaria;
    public $ali_diasOtorgados;
    public $ali_beneficiarioOtros;
    public $unidades; //calendario de unidades por partida
    public $montos; //calandario de costos por partida
    public $usr_id;

    /******************************************************

    METODOS GET

     ******************************************************/

    public function getCcd_id()
    {
        return $this->ccd_id->getVal();
    }
    public function getCed_id()
    {
        return $this->ced_id->getVal();
    }
    public function getGpo_id()
    {
        return $this->gpo_id->getVal();
    }
    public function getUr_id()
    {
        return $this->ur_id->getVal();
    }
    public function getUe_id()
    {
        return $this->ue_id->getVal();
    }
    public function getEpg_id()
    {
        return $this->epg_id->getVal();
    }
    public function getPg_id()
    {
        return $this->pg_id->getVal();
    }
    public function getPe_id()
    {
        return $this->pe_id->getVal();
    }
    public function getEcf_id()
    {
        return $this->ecf_id->getVal();
    }
    public function getGbf_id()
    {
        return $this->gbf_id->getVal();
    }
    public function getAsr_id()
    {
        return $this->asr_id->getVal();
    }
    public function getBeneficiarios()
    {
        return $this->ali_beneficiarios->getVal();
    }
    public function getCuotaDiaria()
    {
        return $this->ali_cuotaDiaria->getVal();
    }
    public function getDiasOtorgados()
    {
        return $this->ali_diasOtorgados->getVal();
    }
    public function getBeneficiarioOtros()
    {
        return $this->ali_beneficiarioOtros->getVal();
    }
    public function getUnidades()
    {
        return $this->unidades;
    }
    public function getMontos()
    {
        return $this->montos;
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /******************************************************

    METODOS SET

     ******************************************************/

    public function setCcd_id($val)
    {
        $this->ccd_id->setVal($val);
    }
    public function setCed_id($val)
    {
        $this->ced_id->setVal($val);
    }
    public function setGpo_id($val)
    {
        $this->gpo_id->setVal($val);
    }
    public function setUr_id($val)
    {
        $this->ur_id->setVal($val);
    }
    public function setUe_id($val)
    {
        $this->ue_id->setVal($val);
    }
    public function setEpg_id($val)
    {
        $this->epg_id->setVal($val);
    }
    public function setPg_id($val)
    {
        $this->pg_id->setVal($val);
    }
    public function setPe_id($val)
    {
        $this->pe_id->setVal($val);
    }
    public function setEcf_id($val)
    {
        $this->ecf_id->setVal($val);
    }
    public function setGbf_id($val)
    {
        $this->gbf_id->setVal($val);
    }
    public function setAsr_id($val)
    {
        $this->asr_id->setVal($val);
    }
    public function setBeneficiarios($val)
    {
        $this->ali_beneficiarios->setVal($val);
    }
    public function setCuotaDiaria($val)
    {
        $this->ali_cuotaDiaria->setVal($val);
    }
    public function setDiasOtorgados($val)
    {
        $this->ali_diasOtorgados->setVal($val);
    }
    public function setBeneficiarioOtros($val)
    {
        $this->ali_beneficiarioOtros->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************

    METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staCcd_id($val)
    {
        $this->ccd_id->staVal($val);
    }
    public function staCed_id($val)
    {
        $this->ced_id->staVal($val);
    }
    public function staGpo_id($val)
    {
        $this->gpo_id->staVal($val);
    }
    public function staUr_id($val)
    {
        $this->ur_id->staVal($val);
    }
    public function staUe_id($val)
    {
        $this->ue_id->staVal($val);
    }
    public function staEpg_id($val)
    {
        $this->epg_id->staVal($val);
    }
    public function staPg_id($val)
    {
        $this->pg_id->staVal($val);
    }
    public function staPe_id($val)
    {
        $this->pe_id->staVal($val);
    }
    public function staEcf_id($val)
    {
        $this->ecf_id->staVal($val);
    }
    public function staGbf_id($val)
    {
        $this->gbf_id->staVal($val);
    }
    public function staAsr_id($val)
    {
        $this->asr_id->staVal($val);
    }
    public function staBeneficiarios($val)
    {
        $this->ali_beneficiarios->staVal($val);
    }
    public function staCuotaDiaria($val)
    {
        $this->ali_cuotaDiaria->staVal($val);
    }
    public function staDiasOtorgados($val)
    {
        $this->ali_diasOtorgados->staVal($val);
    }
    public function staBeneficiarioOtros($val)
    {
        $this->ali_beneficiarioOtros->staVal($val);
    }
    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }

    /******************************************************

    CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {
        parent::__construct(['tabla' => 'poa_Alimentacion']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->ccd_id = new Atributo('numero', 'NO', 'PRI', 'ccd_id');
        $this->ced_id = new Atributo('numero', 'NO', 'PRI', 'ced_id');
        $this->gpo_id = new Atributo('numero', 'NO', 'MUL', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', '', 'ur_id');
        $this->ue_id = new Atributo('numero', 'NO', '', 'ue_id');
        $this->epg_id = new Atributo('numero', 'NO', '', 'epg_id');
        $this->pg_id = new Atributo('numero', 'NO', 'MUL', 'pg_id');
        $this->pe_id = new Atributo('numero', 'NO', '', 'pe_id');
        $this->ecf_id = new Atributo('numero', 'NO', '', 'ecf_id');
        $this->gbf_id = new Atributo('numero', 'NO', 'MUL', 'gbf_id');
        $this->asr_id = new Atributo('numero', 'NO', 'MUL', 'asr_id');
        $this->ali_beneficiarios = new Atributo('numero', 'NO', '', 'ali_beneficiarios');
        $this->ali_cuotaDiaria = new Atributo('numero', 'NO', '', 'ali_cuotaDiaria');
        $this->ali_diasOtorgados = new Atributo('numero', 'NO', '', 'ali_diasOtorgados');
        $this->ali_beneficiarioOtros = new Atributo('cadena', 'NO', '', 'ali_beneficiarioOtros');

        /*instanciando los meses*/
        $this->unidades = new Meses(['prefix' => 'uni_']);
        $this->montos = new Meses(['prefix' => 'mon_']);

        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        /*Aqui se predefinen algunos atributos, en función de la Cedula*/
        /*
    //Marcos 22/julio/2015
    //No sé porqué estan predefinidos éstos valores aqui, y no los toma del combo
    //para 2016 los comento para que sí los tome del combo
    $this->setCcd_id(1); //El Identificador de la Cedula de Alimentación es el 1
    $this->setPg_id(221); //La partida específica para alimentación es 213
    $this->setPe_id(213); //La partida específica para alimentación es 213
     */
    }

    /******************************************************

    FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $ali = new Alimentacion();
        if (isset($p->ccd_id)) {
            $ali->setCcd_id($p->ccd_id);
        }
        if (isset($p->ced_id)) {
            $ali->setCed_id($p->ced_id);
        }
        if (isset($p->gpo_id)) {
            $ali->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $ali->setUr_id($p->ur_id);
        }
        if (isset($p->ue_id)) {
            $ali->setUe_id($p->ue_id);
        }
        if (isset($p->epg_id)) {
            $ali->setEpg_id($p->epg_id);
        }
        if (isset($p->pg_id)) {
            $ali->setPg_id($p->pg_id);
        }
        if (isset($p->pe_id)) {
            $ali->setPe_id($p->pe_id);
        }
        if (isset($p->ecf_id)) {
            $ali->setEcf_id($p->ecf_id);
        }
        if (isset($p->gbf_id)) {
            $ali->setGbf_id($p->gbf_id);
        }
        if (isset($p->asr_id)) {
            $ali->setAsr_id($p->asr_id);
        }
        if (isset($p->beneficiarios)) {
            $ali->setBeneficiarios($p->beneficiarios);
        }
        if (isset($p->cuotaDiaria)) {
            $ali->setCuotaDiaria($p->cuotaDiaria);
        }
        if (isset($p->diasOtorgados)) {
            $ali->setDiasOtorgados($p->diasOtorgados);
        }
        if (isset($p->beneficiarioOtros)) {
            $ali->setBeneficiarioOtros($p->beneficiarioOtros);
        }

        if (isset($p->unidades)) {
            $ali->getUnidades()->setArrayMeses($p->unidades);
        }
        if (isset($p->montos)) {
            $ali->getMontos()->setArrayMeses($p->montos);
        }

        if (isset($p->usr_id)) {
            $ali->setUsr_id($p->usr_id);
        }
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $bit_observacion = '';
        $bit_usr_id = $_SESSION[_S]['usr_id'];
        switch ($p->abc) {
            case 'a':
                $ali->conectar();
                $bit_observacion = 'REGISTRO INSERTADO DESDE PANTALLA';
                $sql = ' SET @usr_ip =INET_ATON(?), @usr_id=? ,@bit_observacion =?;';
                $params = [$ip, $bit_usr_id, $bit_observacion];
                $ali->setSql($sql);
                $ali->consultar($params);
                $ali->insert(false);
                return $ali->getUltimoId();
                break;
            case 'b':
                $ali->conectar();
                $bit_observacion = 'REGISTRO ELIMINADO DESDE PANTALLA';
                $sql = ' SET @usr_ip =INET_ATON(?), @usr_id=? ,@bit_observacion =?;';
                $params = [$ip, $bit_usr_id, $bit_observacion];
                $ali->setSql($sql);
                $ali->consultar($params);
                $ali->delete(false);
                break;
            case 'c':
                $ali->conectar();
                $bit_observacion = 'REGISTRO ACTUALIZADO DESDE PANTALLA';
                $sql = ' SET @usr_ip =INET_ATON(?), @usr_id=? ,@bit_observacion =?;';
                $params = [$ip, $bit_usr_id, $bit_observacion];
                $ali->setSql($sql);
                $ali->consultar($params);
                $ali->update(false);
                break;
        }
    }
}
