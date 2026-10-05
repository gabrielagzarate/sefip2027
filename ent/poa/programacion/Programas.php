<?php

require_once dirname(__FILE__) . '/../../Entidad.php';
class Programas extends Entidad
{
    /******************************************************

        ATRIBUTOS DE LA CLASE

     ******************************************************/

    protected $prgs_id; //
    protected $prgs_nombre; //Nombre del programa
    protected $epr_id; //
    protected $pbf_id; //
    protected $prgs_hombres; //
    protected $prgs_mujeres; //
    protected $prgs_total; //
    protected $prgs_hombresO;
    protected $prgs_mujeresO;
    protected $prgs_totalO;
    protected $pbf_idO;
    protected $prgs_MedioVerificacion;

    protected $epo_id; //
    protected $epods_id;
    protected $spe_id; //
    protected $fin_id; //
    protected $epc_id; //
    protected $ueres; // UR responsable
    protected $vpi_id;
    protected $epi_id;
    protected $obp_id;

    /******************************************************

        METODOS GET

     ******************************************************/

    public function getPrgs_id()
    {
        return $this->prgs_id->getVal();
    }
    public function getNombre()
    {
        return $this->prgs_nombre->getVal();
    }
    public function getEpr_id()
    {
        return $this->epr_id->getVal();
    }
    public function getPbf_id()
    {
        return $this->pbf_id->getVal();
    }
    public function getHombres()
    {
        return $this->prgs_hombres->getVal();
    }
    public function getMujeres()
    {
        return $this->prgs_mujeres->getVal();
    }
    public function getTotal()
    {
        return $this->prgs_total->getVal();
    }
    public function getHombresO()
    {
        return $this->prgs_hombresO->getVal();
    }
    public function getPrgsMujerO()
    {
        return $this->prgs_mujeresO->getVal();
    }
    public function getTotalO()
    {
        return $this->prgs_totalO->getVal();
    }
    public function getPbf_idO()
    {
        return $this->pbf_idO->getVal();
    }
    public function getMedioVerificacion()
    {
        return $this->prgs_MedioVerificacion->getVal();
    }

    public function getEpo_id()
    {
        return $this->epo_id;
    }
    public function getEpods_id()
    {
        return $this->epods_id;
    }
    public function getSpe_id()
    {
        return $this->spe_id;
    }
    public function getObp_id()
    {
        return $this->obp_id;
    }

    public function getFin_id()
    {
        return $this->fin_id;
    }
    public function getEpc_id()
    {
        return $this->epc_id;
    }
    public function getEpi_id()
    {
        return $this->epi_id;
    }
    public function getVpi_id()
    {
        return $this->vpi_id;
    }


    public function getUeres()
    {
        return $this->ueres;
    }

    /******************************************************

        METODOS SET

     ******************************************************/

    public function setPrgs_id($val)
    {
        $this->prgs_id->setVal($val);
    }
    public function setNombre($val)
    {
        $this->prgs_nombre->setVal($val);
    }
    public function setEpr_id($val)
    {
        $this->epr_id->setVal($val);
    }
    public function setPbf_id($val)
    {
        $this->pbf_id->setVal($val);
    }
    public function setHombres($val)
    {
        $this->prgs_hombres->setVal($val);
    }
    public function setMujeres($val)
    {
        $this->prgs_mujeres->setVal($val);
    }
    public function setTotal($val)
    {
        $this->prgs_total->setVal($val);
    }
    public function setUeres($val)
    {
        $this->ueres = $val;
    }
    public function setHombresO($val)
    {
        $this->prgs_hombresO->setVal($val);
    }
    public function setMujerO($val)
    {
        $this->prgs_mujeresO->setVal($val);
    }
    public function setTotalO($val)
    {
        $this->prgs_totalO->setVal($val);
    }
    public function setPbf_idO($val)
    {
        $this->pbf_idO->setVal($val);
    }
    public function setMedioVerificacion($val)
    {
        $this->prgs_MedioVerificacion->setVal($val);
    }


    /******************************************************

        METODOS STA (SET sin persistencia)

     ******************************************************/

    public function staPrgs_id($val)
    {
        $this->prgs_id->staVal($val);
    }
    public function staNombre($val)
    {
        $this->prgs_nombre->staVal($val);
    }
    public function staEpr_id($val)
    {
        $this->epr_id->staVal($val);
    }
    public function staUeres($val)
    {
        $this->ueres = $val;
    }


    /******************************************************

        CONSTRUCTOR

     ******************************************************/

    public function __construct()
    {

        parent::__construct(['tabla' => 'cat_Programas']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->prgs_id = new Atributo('numero', 'NO', 'PRI', 'prgs_id');
        $this->prgs_nombre = new Atributo('cadena', 'NO', '', 'prgs_nombre');
        $this->epr_id = new Atributo('numero', 'NO', 'MUL', 'epr_id');
        $this->pbf_id = new Atributo('numero', 'NO', '', 'pbf_id');
        $this->prgs_hombres = new Atributo('numero', 'NO', '', 'prgs_hombres');
        $this->prgs_mujeres = new Atributo('numero', 'NO', '', 'prgs_mujeres');
        $this->prgs_total = new Atributo('numero', 'NO', '', 'prgs_total');
        $this->prgs_hombresO = new Atributo('numero', 'NO', '', 'prgs_hombresO');
        $this->prgs_mujeresO = new Atributo('numero', 'NO', '', 'prgs_mujeresO');
        $this->prgs_totalO = new Atributo('numero', 'NO', '', 'prgs_totalO');
        $this->pbf_idO = new Atributo('numero', 'NO', '', 'pbf_idO');
        $this->prgs_MedioVerificacion = new Atributo('cadena', 'NO', '', 'prgs_MedioVerificacion');

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object)func_get_arg(0) : null) : null);

        $prg_id = (!isset($prm->prgs_id)) ? '' : $prm->prgs_id;
        if (!empty($prg_id)) {
            $this->setPrgs_id($prg_id);
            $this->select();
            $this->setSql('
                SELECT epo_id,spe_id,fin_id, epods_id, obp_id
                FROM vin_Programa 
                WHERE prgs_id = ?;
            ');
            $this->conectar();
            $this->consultar([$prm->prgs_id]);
            $datos = $this->extraer();
            $this->desconectar();
            if ($this->getNumeroRegistros() > 0) {
                $this->epo_id = $datos['epo_id'];
                $this->spe_id = $datos['spe_id'];
                $this->fin_id = $datos['fin_id'];
                $this->obp_id = $datos['obp_id'];
                $this->epods_id = $datos['epods_id'];
            }

            $this->setPrgs_id($prg_id);
            $this->select();
            $this->setSql('
                SELECT vpi_id,prgs_id,epi_id
                FROM vin_ProgramaPI 
                WHERE prgs_id = ?;
            ');
            $this->conectar();
            $this->consultar([$prm->prgs_id]);
            $datos = $this->extraer();
            $this->desconectar();
            if ($this->getNumeroRegistros() > 0) {
                $this->vpi_id = $datos['vpi_id'];
                $this->epi_id = $datos['epi_id'];
            }
        }
    }

    /******************************************************

        FUNCIONES ESTÁTICAS

     ******************************************************/

    public static function abc($p)
    {
        $prgs = new Programas();
        if (isset($p->prgs_id)) {
            $prgs->setPrgs_id($p->prgs_id);
        }
        if (isset($p->prgs_nombre)) {
            $prgs->setNombre($p->prgs_nombre);
        }
        if (isset($p->epr_id)) {
            $prgs->setEpr_id($p->epr_id);
        }
        if (isset($p->pbf_id)) {
            $prgs->setPbf_id($p->pbf_id);
        } elseif ($p->abc == 'a') {
            $prgs->setPbf_id(1);
        }
        if (isset($p->prgs_hombres)) {
            $prgs->setHombres($p->prgs_hombres);
        } elseif ($p->abc == 'a') {
            $prgs->setHombres(0);
        }
        if (isset($p->prgs_mujeres)) {
            $prgs->setMujeres($p->prgs_mujeres);
        } elseif ($p->abc == 'a') {
            $prgs->setMujeres(0);
        }
        if (isset($p->prgs_total)) {
            $prgs->setTotal($p->prgs_total);
        } elseif ($p->abc == 'a') {
            $prgs->setTotal(0);
        }
        if (isset($p->prgs_hombresO)) {
            $prgs->setHombresO($p->prgs_hombresO);
        }
        if (isset($p->prgs_mujeresO)) {
            $prgs->setMujerO($p->prgs_mujeresO);
        }
        if (isset($p->prgs_MedioVerificacion)) {
            $prgs->setMedioVerificacion($p->prgs_MedioVerificacion);
        }
        if (isset($p->prgs_totalO)) {
            $prgs->setTotalO($p->prgs_totalO);
        }
        if (isset($p->pbf_idO)) {
            $prgs->setPbf_idO($p->pbf_idO);
        }
        if (isset($p->ueres)) {
            $prgs->setUeres($p->ueres);
        }
        switch ($p->abc) {

            case 'a':
                $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
                $prgs->conectar();
                if ($p->prgs_id >= 100 && $p->prgs_id <= 899) {
                    $prgs->setSql('
                        SET 
                        @gpo_id = ?, 
                        @ur_id = ?, 
                        @bit_observacion = "REGISTRO DE PROGRAMA DESDE SISTEMA A_2_3", 
                        @usr_id = ?,
                        @usr_ip = INET_ATON(?);
                    ');
                    $params = [substr($p->ueres, 0, 1), substr($p->ueres, 1, 2), $_SESSION[_S]['usr_id'], $ip];
                    $prgs->consultar($params);
                } else {
                    $prgs->setSql('
                        SET 
                        @bit_observacion="REGISTRO DE PROGRAMA DESDE SISTEMA A_2_3",
                        @usr_id = ?,
                        @usr_ip = INET_ATON(?);
                    ');
                    $params = [$_SESSION[_S]['usr_id'], $ip];
                    $prgs->consultar($params);
                }
                $prgs->insert(false);
                if ($p->prgs_id >= 100 && $p->prgs_id <= 899) {
                    $prgs->conectar();
                    $sql = '
                        INSERT INTO vin_Programa (prgs_id, epo_id, spe_id, fin_id, usr_id, epods_id, obp_id, ple_id, ope_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);
                    ';
                    $params = [$p->prgs_id, $p->epo_id, $p->spe_id, $p->fin_id, $_SESSION[_S]['usr_id'], $p->epods_id, $p->obp_id, $p->ple_id, $p->ope_id];
                    $prgs->setSql($sql);
                    $prgs->consultar($params);
                    $prgs->desconectar();
                    if ($p->arrayPI) {
                        if (strpos($p->arrayPI, ',')) {
                            $prgs->conectar();
                            $insertPi = explode(',', $p->arrayPI);
                            foreach ($insertPi as $key => $value) {
                                $sql = 'INSERT INTO vin_ProgramaPI (prgs_id,epi_id, usr_id) VALUES (?, ?, ?);';
                                $params = [$p->prgs_id, $value, $_SESSION[_S]['usr_id']];
                                $prgs->setSql($sql);
                                $prgs->consultar($params);
                            }
                            $prgs->desconectar();
                        } else {
                            $prgs->conectar();
                            $sql = 'INSERT INTO vin_ProgramaPI (prgs_id,epi_id, usr_id) VALUES (?, ?, ?);';
                            $params = [$p->prgs_id, $p->arrayPI, $_SESSION[_S]['usr_id']];
                            $prgs->setSql($sql);
                            $prgs->consultar($params);
                            $prgs->desconectar();
                        }
                    }
                }
                return $prgs->getUltimoId();
                break;
            case 'b':
                $prgs->delete();
                break;
            case 'c':
                $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
                $prgs->conectar();
                if ($p->prgs_id >= 100 && $p->prgs_id <= 899) {
                    if (isset($p->ueres)) {
                        $prgs->setSql('SET  @gpo_id = ' . substr($p->ueres, 0, 1) . ', @ur_id = ' . substr($p->ueres, 1, 2)
                            . ',@bit_observacion="ACTUALIZACION DE PROGRAMA DESDE SISTEMA A_2_3",@usr_id=' . $_SESSION[_S]['usr_id']
                            . ',@usr_ip=INET_ATON("' . $ip . '");');
                        $prgs->consultar();
                    } else {
                        //Busca la UR responsable
                        $prgs->setSql('SELECT gpo_id,ur_id FROM vin_ProgramasMir WHERE prgs_id= ?');
                        $prgs->consultar([$p->prgs_id]);
                        $datoUr = $prgs->extraer();
                        $prgs->setSql('SET  @gpo_id = ' . $datoUr['gpo_id'] . ', @ur_id = ' . $datoUr['ur_id']
                            . ',@bit_observacion="ACTUALIZACION DE PROGRAMA DESDE SISTEMA A_2_3",@usr_id=' . $_SESSION[_S]['usr_id']
                            . ',@usr_ip=INET_ATON("' . $ip . '");');
                        $prgs->consultar();
                    }
                } else {
                    $prgs->setSql('SET @bit_observacion="ACTUALIZACION DE PROGRAMA DESDE SISTEMA A_2_3",@usr_id=' . $_SESSION[_S]['usr_id']
                        . ',@usr_ip=INET_ATON("' . $ip . '");');
                    $prgs->consultar();
                }
                $prgs->update(false);
                //VERIFICAMOS SI EXISTE EL REGISTRO DE LA VINCULACIÓN EN vin_Programa
                if ($p->prgs_id >= 100 && $p->prgs_id <= 899) {
                    $prgs->conectar();
                    $sqlVin = 'SELECT prgs_id FROM vin_Programa WHERE prgs_id = ?;';
                    $prgs->setSql($sqlVin);
                    $prgs->consultar([$p->prgs_id]);
                    if ($prgs->getNumeroRegistros() > 0) {

                        //$p->epp_id = (isset($p->epp_id) ? )

                        $sql = 'UPDATE vin_Programa SET epo_id = ?, spe_id = ?, fin_id = ?, epods_id = ?, ple_id = ?, ope_id = ?, obp_id = ?
                                WHERE prgs_id = ?;';
                        $params = [$p->epo_id, $p->spe_id, $p->fin_id, $p->epods_id, $p->ple_id, $p->ope_id, $p->obp_id,$p->prgs_id];
                        $prgs->setSql($sql);
                        //Se actualiza vin_Programa,
                        //enc_Programatico, tiene un trigger after insert donde inserta en vin_programatico
                        //efn_id y epp_id ahora son datos de subprograma, se envían como 1 temporalmente
                        $prgs->consultar($params);

                    } else {
                        $sqlnserta = 'INSERT INTO vin_Programa (prgs_id, epo_id, spe_id, fin_id, ple_id, ope_id, obp_id, epods_id, usr_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);';
                        $params = [$p->prgs_id, $p->epo_id, $p->spe_id, $p->fin_id, $p->ple_id, $p->ope_id, $p->obp_id, $p->epods_id, $_SESSION[_S]['usr_id']];
                        $prgs->setSql($sqlnserta);
                        $prgs->consultar($params);
                    }
                    $prgs->desconectar();
                }
                break;
        }
    }

    public function TieneSubProgramasValidados(): bool
    {
        if ($this->getPrgs_id()) {
            if ($this->getPrgs_id() < 100 || $this->getPrgs_id() > 899) {
                return false;
            }

            $this->conectar();
            $this->setSql('SELECT * FROM cat_Subprogramas WHERE prgs_id = ? AND esr_id = 4;');
            $this->consultar([$this->getPrgs_id()]);
            $this->desconectar();
            if ($this->getNumeroRegistros() > 0) {
                return false;
            } else {
                return true;
            }
        }

        return true;
    }

    public function sinActualiacion()
    {
        if ($this->getPrgs_id()) {
            $sql = 'SELECT prgs_id FROM ' . BD_TEMPORAL . ' .tem_ActualizacionPrograma WHERE prgs_id = ? AND tap_estado IN(0,1,2);';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar([$this->getPrgs_id()]);
            $this->desconectar();
            if ($this->getNumeroRegistros() > 0) {
                return false;
            } else {
                return true;
            }
        }
        return true;
    }
    public function validaActualizacioPrograma()
    {
        if ($this->getPrgs_id()) {
            $sql = 'SELECT tap_estado FROM ' . BD_TEMPORAL . ' .tem_ActualizacionPrograma WHERE prgs_id = ? AND tap_estado IN(0,1,2);';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar([$this->getPrgs_id()]);
            $this->desconectar();

            if ($this->getNumeroRegistros() > 0) {
                $val = $this->extraer();
                return $val['tap_estado'];
            }
        }
        return 0;
    }

    public function sinRegistros()
    {
        if ($this->getPrgs_id()) {
            //UR´s vinculadas
            $sql = 'SELECT prg_id FROM cat_Programa WHERE prg_id = ?
                    UNION ALL
                    SELECT sprs_id FROM enc_UrSubprogramas WHERE prgs_id = ? AND sprs_id != 0;';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar([$this->getPrgs_id(), $this->getPrgs_id()]);
            $this->desconectar();
            $ursVinculadas = $this->getNumeroRegistros();

            //Consulta para verificar que el programa no tenga obras validadas
            $sql = 'SELECT prg_id FROM cat_ObraActividad WHERE prg_id = ? AND oa_id !=0 AND eoa_id=4;';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar([$this->getPrgs_id()]);
            $this->desconectar();
            $obrasValidadas = $this->getNumeroRegistros();

            //Para los programas 100 - 899 se valida que no tenga mir validada
            $sql = 'SELECT prgs_id FROM vin_ProgramasMir JOIN cat_Mir USING(mir_id) WHERE prgs_id = ? AND emi_id=9 OR emi_id=10;';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar([$this->getPrgs_id()]);
            $this->desconectar();
            $mirValidada = $this->getNumeroRegistros();
            $registrosMIR = 0;
            //Obtiene vinculacion programa MIR
            $sql = 'SELECT vpm_id  FROM vin_ProgramasMir WHERE prgs_id = ?;';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar([$this->getPrgs_id()]);
            $this->desconectar();
            $vinculacionMir = $this->extraer();
            $vpm_id = $vinculacionMir['vpm_id'];

            if ($this->getNumeroRegistros() > 0) {
                // Para los programas 100 - 899 se valida que no tenga registros en mir
                $sql = '
                    SELECT count(vpm_id) AS reg
                    FROM (
                        
                        SELECT vpm_id FROM cat_SupuestoProgramaMir
                        WHERE vpm_id= :vpm_id
                        UNION ALL
                        SELECT vpm_id FROM cat_MedioVerificacionProgramaMir
                        WHERE vpm_id= :vpm_id
                        UNION ALL
                        SELECT vpm_id FROM vin_ProgramaMirIndicador
                        JOIN cat_MetaFin USING(pmi_id)
                        WHERE vpm_id= :vpm_id
                        UNION ALL
                        SELECT vpm_id FROM vin_ProgramaMirIndicador
                        JOIN cat_MetaProposito USING(pmi_id)
                        WHERE vpm_id= :vpm_id
                        UNION ALL
                        
                        SELECT vpm_id FROM vin_ProgramasMir
                        INNER JOIN cat_SupuestoComponente USING(prgs_id)
                        WHERE vpm_id= :vpm_id
                        UNION ALL
                        SELECT vpm_id FROM vin_ProgramasMir
                        INNER JOIN cat_MedioVerificacionComponente USING(prgs_id)
                        WHERE vpm_id= :vpm_id
                        UNION ALL
                        SELECT vpm_id FROM vin_ProgramasMir
                        INNER JOIN cat_MetaComponente USING(prgs_id)
                        WHERE vpm_id= :vpm_id
                        UNION ALL
                        
                        SELECT vpm_id FROM vin_ProgramasMir
                        INNER JOIN cat_SupuestoActividad USING(prgs_id)
                        WHERE vpm_id= :vpm_id
                        UNION ALL
                        SELECT vpm_id FROM vin_ProgramasMir
                        INNER JOIN cat_MedioVerificacionActividad USING(prgs_id)
                        WHERE vpm_id= :vpm_id
                        UNION ALL
                        SELECT vpm_id FROM enc_Programatico ep
                        INNER JOIN vin_ProgramasMir vp ON ep.prg_id=vp.prgs_id
                        -- INNER JOIN cat_MetaActividad USING(epg_id)
                        WHERE vpm_id= :vpm_id
                    )Res
                    ;
                ';

                $this->conectar();
                $this->setSql($sql);
                $this->consultar([':vpm_id' => $vpm_id]);
                $this->desconectar();
                $registros = $this->extraer();
                $registrosMIR = $registros['reg'];
            }
            if ($ursVinculadas > 0 || $obrasValidadas > 0 || $mirValidada > 0 || $registrosMIR > 0) {
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

    public function vinculacionModificable()
    {

        if ($this->getPrgs_id() && $this->getPrgs_id() >= 100 && $this->getPrgs_id() <= 899) {
            $sql = 'SELECT prg_id FROM enc_Programatico WHERE prg_id = ? AND oa_id >0;';
            $this->conectar();
            $this->setSql($sql);
            $this->consultar([$this->getPrgs_id()]);
            $this->desconectar();
            if ($this->getNumeroRegistros() > 0) {
                return false;
            } else {
                return true;
            }
        }
        return true;
    }
}
