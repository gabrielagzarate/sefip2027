<?php
require_once dirname(__FILE__)."/../Entidad.php";
require_once dirname(__FILE__)."/PresupuestoComprometido.php";
require_once dirname(__FILE__)."/PresupuestoDevengado.php";
require_once dirname(__FILE__)."/PresupuestoVigente.php";

class Presupuesto extends Entidad
{
    /*     * ****************************************************

        ATRIBUTOS DE LA CLASE

     * **************************************************** */
    protected $gpo_id; //Identificador del Grupo de la Unidad Responsable. Depende de cat_UnidadEjecutora
    protected $ur_id; //Identificador de la Unidad Responsable. Depende de cat_UnidadEjecutora
    protected $ue_id; //Identificador de la Unidad Ejecutora. Depende de cat_UnidadEjecutora
    protected $epg_id; //Identificador del encuadre progamatico. Depende de enc_Programatico
    protected $pg_id; //Identificador de la Partida Genérica. Depende de cat_PartidaEspecifica
    protected $pe_id; //Identificador de la Partida Específica. Depende de cat_PartidaEspecifica
    protected $ecf_id; //Identificador del encuadre de Clave de Fianciamiento. Depende de enc_ClaveFinaciamiento.
    protected $tcp_id; //Identificador del Tipo de Compra. Depende de cat_TipoCompra.
    protected $clave; //Clave de la Primary Key se forma de gpo_id, ur_id, ue_id, "-", epg_id, "-", pg_id, pe_id, ecf_id
    protected $prg_id; //Identificador del Programa cat_ObraActividad - Llave Foranea
    protected $spr_id; //Identificador del Subprograma cat_ObraActividad - Llave Foranea
    protected $py_id; //Identificador del Proyecto cat_ObraActividad - Llave Foranea
    protected $oa_id; //Identificador de la Obra/Actividad cat_ObraActividad - Llave Foranea
    protected $cff_id; //
    protected $fte_id; //Identificador de la fuente de Financiamiento. enc_ClaveFinanciamiento - Llave Foranea
    protected $sft_id; //Identificador de la sub-fuente de Financiamiento. enc_ClaveFinanciamiento - Llave Foranea
    protected $ori_id; //Identificador de la Origen de Financiamiento. enc_ClaveFinanciamiento - Llave Foranea
    protected $sor_id; //Identificador de la Suborigen de Financiamiento. enc_ClaveFinanciamiento - Llave Foranea
    protected $cfi_id; //Identificador de la Clave de Financiamiento. enc_ClaveFinanciamiento - Llave Foranea
    protected $anio; //Identificador del  año de Financiamiento. enc_ClaveFinanciamiento - Llave Foranea
    protected $claveCompleta; //Clave de la llave completa, sustituyendo los encuadres.
    protected $fechaIngreso; //
    protected $exenta; //
    protected $vigente;
    protected $mesVigente;
    protected $comprometido;
    protected $mesComprometido;
    protected $devengado;
    protected $mesDevengado;

    //Tablas Auxiliare particionadas verticalmente de Presupuesto


    /*     * ****************************************************

        METODOS GET

     * **************************************************** */

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

    public function getTcp_id()
    {
        return $this->tcp_id->getVal();
    }

    public function getClave()
    {
        return $this->clave->getVal();
    }

    public function getPrg_id()
    {
        return $this->prg_id->getVal();
    }

    public function getSpr_id()
    {
        return $this->spr_id->getVal();
    }

    public function getPy_id()
    {
        return $this->py_id->getVal();
    }

    public function getOa_id()
    {
        return $this->oa_id->getVal();
    }

    public function getCff_id()
    {
        return $this->cff_id->getVal();
    }

    public function getFte_id()
    {
        return $this->fte_id->getVal();
    }

    public function getSft_id()
    {
        return $this->sft_id->getVal();
    }

    public function getOri_id()
    {
        return $this->ori_id->getVal();
    }

    public function getSor_id()
    {
        return $this->sor_id->getVal();
    }

    public function getCfi_id()
    {
        return $this->cfi_id->getVal();
    }

    public function getAnio()
    {
        return $this->anio->getVal();
    }

    public function getClaveCompleta()
    {
        return $this->claveCompleta->getVal();
    }

    public function getFechaIngreso()
    {
        return $this->fechaIngreso->getVal();
    }

    public function getVigente()
    {
        return $this->vigente->getVigente();
    }

    public function getMesVigente()
    {
        return $this->mesVigente;
    }

    public function getComprometido()
    {
        return $this->comprometido->getComprometido();
    }

    public function getMesComprometido()
    {
        return $this->mesComprometido;
    }

    public function getDevengado()
    {
        return $this->devengado->getDevengado();
    }

    public function getMesDevengado()
    {
        return $this->mesDevengado;
    }

    public function getExenta()
    {
        return $this->vigente->getExenta();
    }
    /*     * ****************************************************

        METODOS SET

     * **************************************************** */

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

    public function setTcp_id($val)
    {
        $this->tcp_id->setVal($val);
    }

    public function setClave($val)
    {
        $this->clave->setVal($val);
    }

    public function setPrg_id($val)
    {
        $this->prg_id->setVal($val);
    }

    public function setSpr_id($val)
    {
        $this->spr_id->setVal($val);
    }

    public function setPy_id($val)
    {
        $this->py_id->setVal($val);
    }

    public function setOa_id($val)
    {
        $this->oa_id->setVal($val);
    }

    public function setCff_id($val)
    {
        $this->cff_id->setVal($val);
    }

    public function setFte_id($val)
    {
        $this->fte_id->setVal($val);
    }

    public function setSft_id($val)
    {
        $this->sft_id->setVal($val);
    }

    public function setOri_id($val)
    {
        $this->ori_id->setVal($val);
    }

    public function setSor_id($val)
    {
        $this->sor_id->setVal($val);
    }

    public function setCfi_id($val)
    {
        $this->cfi_id->setVal($val);
    }

    public function setAnio($val)
    {
        $this->anio->setVal($val);
    }

    public function setClaveCompleta($val)
    {
        $this->claveCompleta->setVal($val);
    }

    public function setFechaIngreso($val)
    {
        $this->fechaIngreso->setVal($val);
    }
    /*     * ****************************************************

        METODOS STA (SET sin persistencia)

     * **************************************************** */

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

    public function staTcp_id($val)
    {
        $this->tcp_id->staVal($val);
    }

    public function staClave($val)
    {
        $this->clave->staVal($val);
    }

    public function staPrg_id($val)
    {
        $this->prg_id->staVal($val);
    }

    public function staSpr_id($val)
    {
        $this->spr_id->staVal($val);
    }

    public function staPy_id($val)
    {
        $this->py_id->staVal($val);
    }

    public function staOa_id($val)
    {
        $this->oa_id->staVal($val);
    }

    public function staCff_id($val)
    {
        $this->cff_id->staVal($val);
    }

    public function staFte_id($val)
    {
        $this->fte_id->staVal($val);
    }

    public function staSft_id($val)
    {
        $this->sft_id->staVal($val);
    }

    public function staOri_id($val)
    {
        $this->ori_id->staVal($val);
    }

    public function staSor_id($val)
    {
        $this->sor_id->staVal($val);
    }

    public function staCfi_id($val)
    {
        $this->cfi_id->staVal($val);
    }

    public function staAnio($val)
    {
        $this->anio->staVal($val);
    }

    public function staClaveCompleta($val)
    {
        $this->claveCompleta->staVal($val);
    }

    public function staFechaIngreso($val)
    {
        $this->fechaIngreso->staVal($val);
    }
    /*     * ****************************************************

        CONSTRUCTOR

     * **************************************************** */

    public function __construct()
    {

        parent::__construct(array("tabla" => "presupuesto"));
        $prm = ( func_num_args() > 0 ) ? ((( is_object(func_get_arg(0)) ) ? func_get_arg(0)
                :
            ( is_array(func_get_arg(0)) ) ) ? (object) func_get_arg(0) : null  ) : null;

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->gpo_id = new Atributo("numero", "NO", "PRI", "gpo_id");
        $this->ur_id = new Atributo("numero", "NO", "PRI", "ur_id");
        $this->ue_id = new Atributo("numero", "NO", "PRI", "ue_id");
        $this->epg_id = new Atributo("numero", "NO", "PRI", "epg_id");
        $this->pg_id = new Atributo("numero", "NO", "PRI", "pg_id");
        $this->pe_id = new Atributo("numero", "NO", "PRI", "pe_id");
        $this->ecf_id = new Atributo("numero", "NO", "PRI", "ecf_id");
        $this->tcp_id = new Atributo("cadena", "NO", "PRI", "tcp_id");
        $this->clave = new Atributo("cadena", "NO", "MUL", "pre_clave");
        $this->prg_id = new Atributo("numero", "NO", "MUL", "prg_id");
        $this->spr_id = new Atributo("numero", "NO", "", "spr_id");
        $this->py_id = new Atributo("numero", "NO", "MUL", "py_id");
        $this->oa_id = new Atributo("numero", "NO", "", "oa_id");
        $this->cff_id = new Atributo("cadena", "NO", "", "cff_id");
        $this->fte_id = new Atributo("cadena", "NO", "MUL", "fte_id");
        $this->sft_id = new Atributo("cadena", "NO", "", "sft_id");
        $this->ori_id = new Atributo("cadena", "NO", "MUL", "ori_id");
        $this->sor_id = new Atributo("cadena", "NO", "", "sor_id");
        $this->cfi_id = new Atributo("numero", "NO", "MUL", "cfi_id");
        $this->anio = new Atributo("numero", "NO", "", "cfi_anio");
        $this->claveCompleta = new Atributo("cadena", "NO", "MUL",
            "pre_claveCompleta");
        $this->fechaIngreso = new Atributo("fecha", "YES", "",
            "pre_fechaIngreso");

        // $this->aprobado = new PresupuestoAprobado($prm);
        $this->vigente = new PresupuestoVigente($prm);
        $this->comprometido = new PresupuestoComprometido($prm);
        $this->devengado = new PresupuestoDevengado($prm);

        $gpo_id = (!isset($prm->gpo_id)) ? '' : $prm->gpo_id;
        $ur_id = (!isset($prm->ur_id)) ? '' : $prm->ur_id;
        $ue_id = (!isset($prm->ue_id)) ? '' : $prm->ue_id;
        $epg_id = (!isset($prm->epg_id)) ? '' : $prm->epg_id;
        $pg_id = (!isset($prm->pg_id)) ? '' : $prm->pg_id;
        $pe_id = (!isset($prm->pe_id)) ? '' : $prm->pe_id;
        $ecf_id = (!isset($prm->ecf_id)) ? '' : $prm->ecf_id;
        $tcp_id = (!isset($prm->tcp_id)) ? '' : $prm->tcp_id;

        if ($gpo_id != '' && $ur_id != '' && $ue_id != '' && $epg_id != '' && $pg_id
            != '' && $pe_id != '' && $ecf_id != '') {

            // $this->mesAprobado = $this->aprobado->getMesAprobado();
            $this->mesVigente = $this->vigente->getMesVigente();
            $this->mesComprometido = $this->comprometido->getMesComprometido();
            $this->mesDevengado = $this->devengado->getMesDevengado();

            $this->setGpo_id($gpo_id);
            $this->setUr_id($ur_id);
            $this->setUe_id($ue_id);
            $this->setEpg_id($epg_id);
            $this->setPg_id($pg_id);
            $this->setPe_id($pe_id);
            $this->setEcf_id($ecf_id);
            $this->setTcp_id($tcp_id);
            $this->select();
        }
    }

    public function select($servidor = "")
    {
        $sql = $from = $where = '';
        if ($this->getGpo_id() != '' && $this->getUr_id() != '' && $this->getUe_id()
            != '' && $this->getEpg_id() != '' && $this->getPg_id() != '' && $this->getPe_id()
            != '' && $this->getEcf_id() != '') {
            if ($this->getTcp_id() != '') {
                /* OBTENEMOS LOS CALENDARIOS DE UNA CLAVE PRESUPUESTAL SEGÚN EL TIPO DE COMPRA ENVIADO */
                $sql = "SELECT ";
                $from = "
                    FROM presupuesto
                    INNER JOIN presupuestoDevengado USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN presupuestoVigente USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN presupuestoComprometido USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                ";
                $where = "
                    WHERE gpo_id = ".$this->getGpo_id()."
                    AND ur_id = ".$this->getUr_id()."
                    AND ue_id = '".$this->getUe_id()."'
                    AND epg_id = '".$this->getEpg_id()."'
                    AND pg_id = ".$this->getPg_id()."
                    AND pe_id = ".$this->getPe_id()."
                    AND ecf_id = '".$this->getEcf_id()."'
                    AND tcp_id = '".$this->getTcp_id()."'
                    GROUP BY gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,
                    ecf_id,tcp_id
                ";

                $properties = $this->me->getDefaultProperties();
                $coma = false;
                foreach ($properties as $key => $atributo) {
                    if ($this->$key instanceof Atributo) { //si es atributo
                        if ($this->$key->getKeys() != "PRI") { //si no es llave primaria, se integra en la sentencia
                            $sql .= $this->$key->getName() . ", ";
                            $coma = true;
                        }
                    } elseif ($this->$key instanceof Meses) {
                        /* cargando elementos de tipo Meses */
                        $meses = $this->$key->__get("me")->getDefaultProperties();
                        foreach ($meses as $idMes => $mes) {
                            if ($this->$key->__get($idMes) instanceof Atributo) {
                                $sql .= $this->$key->$idMes->getName() . ", ";
                                $coma = true;
                            }
                        }
                    } elseif ($this->$key instanceof presupuestoVigente || $this->$key instanceof presupuestoComprometido
                        || $this->$key instanceof presupuestoDevengado) {
                        $propertiesPresupuesto = $this->$key->__get("me")->getDefaultProperties();
                        foreach ($propertiesPresupuesto as $keyPresupuesto => $attr) {
                            if ($this->$key->__get($keyPresupuesto) instanceof Atributo) {
                                $sql .= $this->$key->$keyPresupuesto->getName() . ", ";
                                $coma = true;
                            }
                        }
                    }
                }

                if ($coma) {
                    $sql = substr($sql, 0, -2); //si hay, se quita la ', '
                }
            } else {
                /* select  modificado para poder hacer el sum de una clave presupuestal sin especificar el tipo no devuelve previsiones */
                /* select con sum */
                /* OBTENEMOS LA SUMA DE LOS DOS TIPOS DE COMPRA, ESTO NORMALMENTE SE HACE PARA AMPLIACIONES */
                $sql = "SELECT ";
                $from = "
                    FROM presupuesto
                    INNER JOIN presupuestoDevengado USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN presupuestoVigente USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN presupuestoComprometido USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                ";
                $where = "
                    WHERE gpo_id = ".$this->getGpo_id()."
                    AND ur_id = ".$this->getUr_id()."
                    AND ue_id = '".$this->getUe_id()."'
                    AND epg_id = '".$this->getEpg_id()."'
                    AND pg_id = ".$this->getPg_id()."
                    AND pe_id = ".$this->getPe_id()."
                    AND ecf_id = '".$this->getEcf_id()."'
                    AND tcp_id != 'P' 
                    GROUP BY gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,
                    ecf_id,tcp_id
                ";

                $properties = $this->me->getDefaultProperties();
                $coma = false;
                foreach ($properties as $key => $atributo) {
                    if ($this->$key instanceof Atributo) { //si es atributo
                        if ($this->$key->getKeys() != "PRI") { //si no es llave primaria, se integra en la sentencia
                            switch ($this->$key->getTipo()) {
                                case 'moneda':
                                    $sql .= 'sum(' . $this->$key->getName() . ') ' . $this->$key->getName() . ' , ';
                                    $coma = true;
                                    break;
                                default:
                                    $sql .= $this->$key->getName() . ", ";
                                    $coma = true;
                                    break;
                            }
                        }
                    } elseif ($this->$key instanceof Meses) {
                        /* cargando elementos de tipo Meses */
                        $meses = $this->$key->__get("me")->getDefaultProperties();
                        foreach ($meses as $idMes => $mes) {
                            if ($this->$key->__get($idMes) instanceof Atributo) {
                                switch ($this->$key->$idMes->getTipo()) {

                                    case 'moneda':
                                        $sql .= 'sum(' . $this->$key->$idMes->getName() . ') ' . $this->$key->$idMes->getName() . ' , ';
                                        $coma = true;
                                        break;
                                    default:
                                        $sql .= $this->$key->$idMes->getName() . ", ";
                                        $coma = true;
                                        break;
                                }
                            }
                        }
                    } elseif ($this->$key instanceof presupuestoVigente || $this->$key instanceof presupuestoComprometido
                        || $this->$key instanceof presupuestoDevengado) {
                        $propertiesPresupuesto = $this->$key->__get("me")->getDefaultProperties();
                        foreach ($propertiesPresupuesto as $keyPresupuesto => $attr) {
                            if ($this->$key->__get($keyPresupuesto) instanceof Atributo) {
                                switch ($this->$key->$keyPresupuesto->getTipo()) {
                                    case 'moneda':
                                        $sql .= 'sum(' . $this->$key->$keyPresupuesto->getName() . ') ' . $this->$key->$keyPresupuesto->getName() . ' , ';
                                        $coma = true;
                                        break;
                                    default:
                                        $sql .= $this->$key->$keyPresupuesto->getName() . ", ";
                                        $coma = true;
                                        break;
                                }
                            }
                        }
                    }
                }

                if ($coma) {
                    $sql = substr($sql, 0, -2); //si hay, se quita la ', '
                }
            }

            $this->conectar();
            $this->setSql($sql . $from . $where);
            $this->consultar();
            $row = $this->extraer();
            $this->desconectar();

            $properties = $this->me->getDefaultProperties();
            foreach ($properties as $key => $atributo) {
                if ($this->$key instanceof Atributo) {
                    if ($this->$key->getKeys() != "PRI") { //si no es llave primaria
                        switch ($this->$key->getTipo()) {

                            case 'date':
                            case 'fecha':
                                $this->$key->staVal($this->mysqldate_to_date($row[$this->$key->getName()]));
                                break;
                            default:
                                $this->$key->staVal($row[$this->$key->getName()]);
                                break;
                        }
                    }
                } elseif ($this->$key instanceof Meses) {
                    $meses = $this->$key->__get("me")->getDefaultProperties();
                    foreach ($meses as $idMes => $mes) {
                        if ($this->$key->__get($idMes) instanceof Atributo) {
                            switch ($this->$key->$idMes->getTipo()) {
                                default:
                                    $this->$key->$idMes->staVal($row[$this->$key->$idMes->getName()]);
                                    break;
                            }
                        }
                    }
                } elseif ($this->$key instanceof presupuestoVigente || $this->$key instanceof presupuestoComprometido
                    || $this->$key instanceof presupuestoDevengado) {
                    $propertiesPresupuesto = $this->$key->__get("me")->getDefaultProperties();
                    foreach ($propertiesPresupuesto as $keyPresupuesto => $val) {
                        if ($this->$key->__get($keyPresupuesto) instanceof Atributo) {
                            switch ($this->$key->$keyPresupuesto->getTipo()) {
                                default:
                                    $this->$key->$keyPresupuesto->staVal($row[$this->$key->$keyPresupuesto->getName()]);
                                    break;
                            }
                        }
                    }
                }
            }
        }
    }

    //Funcion que valida la existencia de una clave preupuestal, con o sin tipo
    public function validaClavePresupuestal()
    {

        if ($this->getGpo_id() != '' && $this->getUr_id() != '' && $this->getUe_id()
            != '' && $this->getEpg_id() != '' && $this->getPg_id() != '' && $this->getPe_id()
            != '' && $this->getEcf_id() != '') {
            $sql = " SELECT pre_claveCompleta, tcp_id ";
            $sql .= " FROM presupuesto
                JOIN presupuestoAprobado USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoDevengado USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoVigente USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoComprometido USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id) ";
            $sql .= " WHERE gpo_id = '{$this->getGpo_id()}' ".
                " AND ur_id = '{$this->getUr_id()}' ".
                " AND ue_id = '{$this->getUe_id()}' ".
                " AND epg_id = '{$this->getEpg_id()}' ".
                " AND pg_id = '{$this->getPg_id()}' ".
                " AND pe_id = '{$this->getPe_id()}' ".
                " AND ecf_id = '{$this->getEcf_id()}' ";

            if ($this->getTcp_id() != '') {
                $sql .= " AND tcp_id = '{$this->getTcp_id()}' ";
            }

            $sql .= " GROUP BY gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,".
                " ecf_id,pre_claveCompleta, tcp_id  ";

            $this->setSql($sql);
            $this->conectar();
            $this->consultar();
            $this->desconectar();
            return $this->getNumeroRegistros();
        }
        return 0;
    }
    /*     * ****************************************************

        FUNCIONES ESTÁTICAS

     * **************************************************** */

    public static function abc($p)
    {
        $pre = new Presupuesto();
        if (isset($p->gpo_id)) {
            $pre->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $pre->setUr_id($p->ur_id);
        }
        if (isset($p->ue_id)) {
            $pre->setUe_id($p->ue_id);
        }
        if (isset($p->epg_id)) {
            $pre->setEpg_id($p->epg_id);
        }
        if (isset($p->pg_id)) {
            $pre->setPg_id($p->pg_id);
        }
        if (isset($p->pe_id)) {
            $pre->setPe_id($p->pe_id);
        }
        if (isset($p->ecf_id)) {
            $pre->setEcf_id($p->ecf_id);
        }
        if (isset($p->tcp_id)) {
            $pre->setTcp_id($p->tcp_id);
        }
        if (isset($p->clave)) {
            $pre->setClave($p->clave);
        }
        if (isset($p->prg_id)) {
            $pre->setPrg_id($p->prg_id);
        }
        if (isset($p->spr_id)) {
            $pre->setSpr_id($p->spr_id);
        }
        if (isset($p->py_id)) {
            $pre->setPy_id($p->py_id);
        }
        if (isset($p->oa_id)) {
            $pre->setOa_id($p->oa_id);
        }
        if (isset($p->cff_id)) {
            $pre->setCff_id($p->cff_id);
        }
        if (isset($p->fte_id)) {
            $pre->setFte_id($p->fte_id);
        }
        if (isset($p->sft_id)) {
            $pre->setSft_id($p->sft_id);
        }
        if (isset($p->ori_id)) {
            $pre->setOri_id($p->ori_id);
        }
        if (isset($p->sor_id)) {
            $pre->setSor_id($p->sor_id);
        }
        if (isset($p->cfi_id)) {
            $pre->setCfi_id($p->cfi_id);
        }
        if (isset($p->anio)) {
            $pre->setAnio($p->anio);
        }
        if (isset($p->claveCompleta)) {
            $pre->setClaveCompleta($p->claveCompleta);
        }
        if (isset($p->fechaIngreso)) {
            $pre->setFechaIngreso($p->fechaIngreso);
        }
        switch ($p->abc) {
            case "a":
                $pre->insert();
                return $pre->getUltimoId();
                break;
            case "b":
                $pre->delete();
                break;
            case "c":
                $pre->update();
                break;
        }
    }
}
