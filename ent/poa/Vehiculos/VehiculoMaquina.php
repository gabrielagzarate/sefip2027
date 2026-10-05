<?php

require_once __DIR__ . '/../../Entidad.php';
require_once __DIR__ . '/../../../lib/Meses.php';

class VehiculoMaquina extends Entidad
{
    /******************************************************
    ATRIBUTOS DE LA CLASE
     ******************************************************/

    protected $pqv_id; //cat_ParqueVehicular2017 - Llave Foranea
    protected $cve_id; //cat_ConceptoVehiculo - Llave Foranea
    protected $pve_id; //cat_PartidaVehiculo - Llave Foranea
    protected $ced_id; //
    protected $ccd_id; //
    protected $gpo_id; //cat_UnidadEjecutora - Llave foranea poa_OrigenRecurso - Llave Foranea enc_PartGenUR - Llave Foranea
    protected $ur_id; //cat_UnidadEjecutora - Llave foranea poa_OrigenRecurso - Llave Foranea enc_PartGenUR - Llave Foranea
    protected $ue_id; //cat_UnidadEjecutora - Llave foranea
    protected $epg_id; //enc_Programatico - Llave Foranea poa_OrigenRecurso - Llave Foranea
    protected $pg_id; //cat_PartidaEspecifica - Llave Foranea enc_PartGenUR - Llave Foranea
    protected $pe_id; //cat_PartidaEspecifica - Llave Foranea
    protected $ecf_id; //enc_ClaveFinanciamiento - Llave Foranea poa_OrigenRecurso - Llave Foranea
    protected $vmq_costoUnitario; //almacena el costo unitario
    protected $vmq_importe; //engomado
    protected $vmq_otrosImportes; //Otros impuestos
    protected $vmq_aseguradora; //Nombre de la Aseguradora
    protected $vmq_poliza; //Numero de poliza o seguro
    protected $tco_id; //cat_TipoCombustible - Llave Foranea
    protected $unidades; //calendario de unidades por partida
    protected $montos; //calandario de costos por partida
    protected $usr_id; //Guarda el identificador del usuario que esta haciendo la presupuestación

    /******************************************************
    METODOS GET
     ******************************************************/

    public function getPqv_id()
    {
        return $this->pqv_id->getVal();
    }
    public function getCve_id()
    {
        return $this->cve_id->getVal();
    }
    public function getPve_id()
    {
        return $this->pve_id->getVal();
    }
    public function getCed_id()
    {
        return $this->ced_id->getVal();
    }
    public function getCcd_id()
    {
        return $this->ccd_id->getVal();
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
    public function getCostoUnitario()
    {
        return $this->vmq_costoUnitario->getVal();
    }
    public function getImporte()
    {
        return $this->vmq_importe->getVal();
    }
    public function getOtrosImportes()
    {
        return $this->vmq_otrosImportes->getVal();
    }
    public function getAseguradora()
    {
        return $this->vmq_aseguradora->getVal();
    }
    public function getPoliza()
    {
        return $this->vmq_poliza->getVal();
    }
    public function getTco_id()
    {
        return $this->tco_id->getVal();
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

    public function setPqv_id($val)
    {
        $this->pqv_id->setVal($val);
    }
    public function setCve_id($val)
    {
        $this->cve_id->setVal($val);
    }
    public function setPve_id($val)
    {
        $this->pve_id->setVal($val);
    }
    public function setCed_id($val)
    {
        $this->ced_id->setVal($val);
    }
    public function setCcd_id($val)
    {
        $this->ccd_id->setVal($val);
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
    public function setCostoUnitario($val)
    {
        $this->vmq_costoUnitario->setVal($val);
    }
    public function setImporte($val)
    {
        $this->vmq_importe->setVal($val);
    }
    public function setOtrosImportes($val)
    {
        $this->vmq_otrosImportes->setVal($val);
    }
    public function setAseguradora($val)
    {
        $this->vmq_aseguradora->setVal($val);
    }
    public function setPoliza($val)
    {
        $this->vmq_poliza->setVal($val);
    }
    public function setTco_id($val)
    {
        $this->tco_id->setVal($val);
    }
    public function setUnidades($val)
    {
        $this->unidades->setArrayMeses($val);
    }
    public function setMontos($val)
    {
        $this->montos->setArrayMeses($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /******************************************************
    METODOS STA (SET sin persistencia)
     ******************************************************/

    public function staPqv_id($val)
    {
        $this->pqv_id->staVal($val);
    }
    public function staCve_id($val)
    {
        $this->cve_id->staVal($val);
    }
    public function staPve_id($val)
    {
        $this->pve_id->staVal($val);
    }
    public function staCed_id($val)
    {
        $this->ced_id->staVal($val);
    }
    public function staCcd_id($val)
    {
        $this->ccd_id->staVal($val);
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
    public function staCostoUnitario($val)
    {
        $this->vmq_costoUnitario->staVal($val);
    }
    public function staImporte($val)
    {
        $this->vmq_importe->staVal($val);
    }
    public function staOtrosImportes($val)
    {
        $this->vmq_otrosImportes->staVal($val);
    }
    public function staAseguradora($val)
    {
        $this->vmq_aseguradora->staVal($val);
    }
    public function staPoliza($val)
    {
        $this->vmq_poliza->staVal($val);
    }
    public function staTco_id($val)
    {
        $this->tco_id->staVal($val);
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

        parent::__construct(['tabla' => 'poa_VehiculoMaquina']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->pqv_id = new Atributo('cadena', 'NO', 'PRI', 'pqv_id');
        $this->cve_id = new Atributo('numero', 'NO', 'PRI', 'cve_id');
        $this->pve_id = new Atributo('numero', 'NO', 'PRI', 'pve_id');
        $this->ced_id = new Atributo('numero', 'NO', '', 'ced_id');
        $this->ccd_id = new Atributo('numero', 'NO', '', 'ccd_id');
        $this->gpo_id = new Atributo('numero', 'NO', 'MUL', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', '', 'ur_id');
        $this->ue_id = new Atributo('numero', 'NO', '', 'ue_id');
        $this->epg_id = new Atributo('numero', 'NO', '', 'epg_id');
        $this->pg_id = new Atributo('numero', 'NO', 'MUL', 'pg_id');
        $this->pe_id = new Atributo('numero', 'NO', '', 'pe_id');
        $this->ecf_id = new Atributo('numero', 'NO', '', 'ecf_id');
        $this->vmq_costoUnitario = new Atributo('numero', 'NO', '', 'vmq_costoUnitario');
        $this->vmq_importe = new Atributo('numero', 'NO', '', 'vmq_importe');
        $this->vmq_otrosImportes = new Atributo('numero', 'NO', '', 'vmq_otrosImportes');
        $this->vmq_aseguradora = new Atributo('cadena', 'NO', '', 'vmq_aseguradora');
        $this->vmq_poliza = new Atributo('cadena', 'NO', '', 'vmq_poliza');
        $this->tco_id = new Atributo('numero', 'NO', 'MUL', 'tco_id');
        $this->unidades = new Meses(['prefix' => 'uni_']);
        $this->montos = new Meses(['prefix' => 'mon_']);
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        /*Aqui se predefinen algunos atributos, en función de la Cedula*/
        $this->setCcd_id(4); //El Identificador de la Cedula de Vehiculo es el 4
        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        $pqv_id = (!isset($prm->pqv_id)) ? '' : $prm->pqv_id;
        $cve_id = (!isset($prm->cve_id)) ? 0 : $prm->cve_id;
        $pve_id = (!isset($prm->pve_id)) ? 0 : $prm->pve_id;

        if ($pqv_id != '' && $pve_id != 0 && $cve_id != 0) {
            $this->setPqv_id($pqv_id);
            $this->setPve_id($pve_id);
            $this->setCve_id($cve_id);
            $this->select();
        } else {
            $this->unidades->setValMeses(0);
        }
    }

    public static function busqueda()
    {
        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        $gpo_id = (isset($prm->gpo_id)) ? $prm->gpo_id : 0;
        $ur_id = (isset($prm->ur_id)) ? $prm->ur_id : 0;
        $pqv_id = (isset($prm->pqv_id)) ? $prm->pqv_id : '';
        $pve_id = (isset($prm->pve_id)) ? $prm->pve_id : 0;
        $cve_id = (isset($prm->cve_id)) ? $prm->cve_id : 0;

        $ced_id = 0;
        if ($pqv_id != '' && $pve_id != 0 && $cve_id != 0) {
            //instanciando la cedula, ya sea para editar o grabar nuevo
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql("
                SELECT
                    ced_id
                FROM poa_VehiculoMaquina
                WHERE gpo_id = :gpo AND ur_id = :ur
                    AND pqv_id = ':pqv'
                    AND pve_id = :pve
                    AND cve_id = :cve
                ");
            $parametros = [
                'gpo' => $gpo_id,
                'ur'  => $ur_id,
                'pqv' => $pqv_id,
                'pve' => $pve_id,
                'cve' => $cve_id,
            ];
            $conexion->consultar($parametros);
            $conexion->desconectar();

            if ($row = $conexion->extraer()) {
                $ced_id = $row['ced_id'];
            }
        }
        return $ced_id;
    }

    /******************************************************
    FUNCIONES ESTÁTICAS
     ******************************************************/

    public static function abc($p)
    {

        $vmq = new VehiculoMaquina();
        if (isset($p->pqv_id)) {
            $vmq->setPqv_id($p->pqv_id);
        }
        if (isset($p->cve_id)) {
            $vmq->setCve_id($p->cve_id);
        }
        if (isset($p->pve_id)) {
            $vmq->setPve_id($p->pve_id);
        }
        if (isset($p->ced_id)) {
            $vmq->setCed_id($p->ced_id);
        }

        $vmq->setCcd_id(4);
        if (isset($p->gpo_id)) {
            $vmq->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $vmq->setUr_id($p->ur_id);
        }
        if (isset($p->ue_id)) {
            $vmq->setUe_id($p->ue_id);
        }
        if (isset($p->epg_id)) {
            $vmq->setEpg_id($p->epg_id);
        }
        if (isset($p->pg_id)) {
            $vmq->setPg_id($p->pg_id);
        }
        if (isset($p->pe_id)) {
            $vmq->setPe_id($p->pe_id);
        }
        if (isset($p->ecf_id)) {
            $vmq->setEcf_id($p->ecf_id);
        }
        if (isset($p->costoUnitario)) {
            $vmq->setCostoUnitario($p->costoUnitario);
        }
        if (isset($p->importe)) {
            $vmq->setImporte($p->importe);
        }
        if (isset($p->otrosImportes)) {
            $vmq->setOtrosImportes($p->otrosImportes);
        }
        if (isset($p->aseguradora)) {
            $vmq->setAseguradora($p->aseguradora);
        }
        if (isset($p->poliza)) {
            $vmq->setPoliza($p->poliza);
        }
        if (isset($p->tco_id)) {
            $vmq->setTco_id($p->tco_id);
        }
        if (isset($p->unidades)) {
            $vmq->setUnidades($p->unidades);
        }
        if (isset($p->montos)) {
            $vmq->getMontos()->setArrayMeses($p->montos);
        }

        if (isset($p->usr_id)) {
            $vmq->setUsr_id($p->usr_id);
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
                $vmq->conectar();
                $bit_observacion = 'REGISTRO INSERTADO DESDE PANTALLA';
                $vmq->setSql(' SET @usr_ip =INET_ATON(:ip),  @usr_id=:usr , @bit_observacion =:obs;');
                $parametros += [
                    'obs' => $bit_observacion,
                ];
                $vmq->consultar($parametros);
                $vmq->insert(false);
                return $vmq->getUltimoId();
                break;
            case 'b':
                $vmq->conectar();
                $bit_observacion = 'REGISTRO BORRADO DESDE PANTALLA';
                $parametros += [
                    'obs' => $bit_observacion,
                ];
                $vmq->setSql(' SET @usr_ip =INET_ATON(:ip),  @usr_id=:usr , @bit_observacion =:obs;');
                $vmq->consultar($parametros);
                $vmq->delete(false);
                break;
            case 'c':
                $vmq->conectar();
                $bit_observacion = 'REGISTRO ACTUALIZADO DESDE PANTALLA';
                $parametros += [
                    'obs' => $bit_observacion,
                ];
                $vmq->setSql(' SET @usr_ip =INET_ATON(:ip),  @usr_id=:usr , @bit_observacion =:obs;');
                $vmq->consultar($parametros);
                $vmq->update(false);
                break;
        }
    }
}
