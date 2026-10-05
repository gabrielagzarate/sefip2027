<?php

require_once __DIR__ . '/../../../lib/constantes.php';
require_once __DIR__ . '/../../Entidad.php';
require_once __DIR__ . '/../../../lib/Meses.php';

class ServiciosPersonales extends Entidad
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $ccd_id; //
    protected $ced_id; //
    protected $gpo_id; //
    protected $ur_id; //
    protected $ue_id; //
    protected $epg_id; //
    protected $pg_id; //
    protected $pe_id; //
    protected $ecf_id; //
    protected $rla_id; //
    protected $epz_id; //
    protected $montos; //calandario de costos por partida
    protected $usr_id; //Clave identificadora del usuario que inserto el registro

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
    public function getRla_id()
    {
        return $this->rla_id->getVal();
    }
    public function getEpz_id()
    {
        return $this->epz_id->getVal();
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
    public function setRla_id($val)
    {
        $this->rla_id->setVal($val);
    }
    public function setEpz_id($val)
    {
        $this->epz_id->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }
    public function setPlazas($val)
    {
        $this->plazas->setVal($val);
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
    public function staRla_id($val)
    {
        $this->rla_id->staVal($val);
    }
    public function staEpz_id($val)
    {
        $this->epz_id->staVal($val);
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

        parent::__construct(['tabla' => 'poa_ServiciosPersonales']);

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
        $this->rla_id = new Atributo('numero', 'NO', 'MUL', 'rla_id');
        $this->epz_id = new Atributo('numero', 'NO', 'MUL', 'epz_id');
        $this->montos = new Meses(['prefix' => 'mon_']);
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        $ccd_id = (!isset($prm->ccd_id)) ? '' : $prm->ccd_id;
        $ced_id = (!isset($prm->ced_id)) ? '' : $prm->ced_id;

        if ($ccd_id != '' && $ced_id != '') {
            $this->setCcd_id($ccd_id);
            $this->setCed_id($ced_id);
            $this->select();
        }
    } // fin __construct

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function abc($p)
    {
        $spe = new ServiciosPersonales();
        if (isset($p->ccd_id)) {
            $spe->setCcd_id($p->ccd_id);
        }

        if (isset($p->ced_id)) {
            $spe->setCed_id($p->ced_id);
        }

        if (isset($p->gpo_id)) {
            $spe->setGpo_id($p->gpo_id);
        }

        if (isset($p->ur_id)) {
            $spe->setUr_id($p->ur_id);
        }

        if (isset($p->ue_id)) {
            $spe->setUe_id($p->ue_id);
        }

        if (isset($p->epg_id)) {
            $spe->setEpg_id($p->epg_id);
        }

        if (isset($p->pg_id)) {
            $spe->setPg_id($p->pg_id);
        }

        if (isset($p->pe_id)) {
            $spe->setPe_id($p->pe_id);
        }

        if (isset($p->ecf_id)) {
            $spe->setEcf_id($p->ecf_id);
        }

        if (isset($p->rla_id)) {
            $spe->setRla_id($p->rla_id);
        }

        if (isset($p->epz_id)) {
            $spe->setEpz_id($p->epz_id);
        }

        if (isset($p->plazas)) {
            $spe->setPlazas($p->plazas);
        }

        if (isset($p->montos)) {
            $spe->getMontos()->setArrayMeses($p->montos);
        }

        if (isset($p->usr_id)) {
            $spe->setUsr_id($p->usr_id);
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $bit_observacion = '';
        $bit_usr_id = $_SESSION[_S]['usr_id'];
        $parametros = [
            'ip'  => $ip,
            'usr' => $bit_usr_id,
        ];
        switch ($p->abc) {
            case 'a':
                $spe->conectar();
                $bit_observacion = 'REGISTRO INSERTADO DESDE PANTALLA';
                $parametros += [
                    'obs' => $bit_observacion,
                ];
                $spe->setSql(' SET  @usr_ip =INET_ATON(:ip), @bit_observacion = :obs ,@usr_id=:usr;');
                $spe->consultar($parametros);
                $spe->insert(false);
                return $spe->getUltimoId();
                break;
            case 'b':
                $spe->conectar();
                $bit_observacion = 'REGISTRO ELIMINADO DESDE PANTALLA';
                $parametros += [
                    'obs' => $bit_observacion,
                ];
                $spe->setSql(' SET @usr_ip =INET_ATON(:ip),@bit_observacion =:obs ,@usr_id=:usr;');
                $spe->consultar($parametros);
                $spe->delete(false);
                break;
            case 'c':
                $spe->conectar();
                $bit_observacion = 'REGISTRO ACTUALIZADO DESDE PANTALLA';
                $parametros += [
                    'obs' => $bit_observacion,
                ];
                $spe->setSql(' SET @usr_ip =INET_ATON(:ip),@bit_observacion =:obs, @usr_id=:usr;');
                $spe->consultar($parametros);
                $spe->update(false);
                break;
        } // fin switch
    } // fin abc

} // fin class
