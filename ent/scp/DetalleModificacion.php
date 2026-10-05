<?php

require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../Entidad.php';
require_once dirname(__FILE__) . '/Presupuesto.php';
require_once dirname(__FILE__) . '/../UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../lib/Meses.php';

class DetalleModificacion extends Entidad
{
    /*     * ****************************************************
    ATRIBUTOS DE LA CLASE
     * **************************************************** */
    protected $omo_id; //enc_OrigenOperacion - Llave foranea Clave del origen de la modificacion
    protected $mod_gpo_id; //cat_UnidadResponsable - Llave foranea Clave del grupo de la clasificacion administrativa
    protected $mod_ur_id; //cat_UnidadResponsable - Llave foranea
    protected $mod_id; //Clave identificadora del numero de modificacion. Foranea dependiente de scp_MaestroModificacion
    protected $gpo_id; //Indentificador del Grupo de la UR. Foranea dependiente de presupuesto
    protected $ur_id; //Indentificador de la UR. Foranea dependiente de presupuesto
    protected $ue_id; //Identificador de la Unida dEjecutora, Foranea dependiente de presupuesto
    protected $epg_id; //Identificador del Encuadre programatico. Foranea dependiente de presupuesto
    protected $pg_id; //Identificador de la Partida Generica, Foranea dependiente de presupuesto
    protected $pe_id; //Identificador de la Partida Especifica. ForÃ¡nea dependiente de presupuesto
    protected $ecf_id; //Identificador del Encuadre de Clave de Financiamiento. ForÃ¡nea dependiente de presupuesto
    protected $tcp_id; //Identificador del Tipo de Compra. ForÃ¡nea dependiente de presupuesto
    protected $mov_id; //Identificador del tipo de movimiento. forÃ¡nea dependiente de cat_TipoMovimiento
    protected $dmo_importe; //Importe total de la modificacion, por clave presupuestal
    protected $mes; //presupuesto vigente de la clave presupuestal antes de la modificaciÃ³n
    protected $importeVig; //importe del vigente al momento de hacer la autorizacion
    protected $mesVigente; // calendario del vigente al momento del hacer la autorizacion
    protected $usr_id; //

    /*     * ****************************************************
    METODOS GET
     * **************************************************** */

    public function getOmo_id()
    {
        return $this->omo_id->getVal();
    }

    public function getMod_gpo_id()
    {
        return $this->mod_gpo_id->getVal();
    }

    public function getMod_ur_id()
    {
        return $this->mod_ur_id->getVal();
    }

    public function getMod_id()
    {
        return $this->mod_id->getVal();
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

    public function getTcp_id()
    {
        return $this->tcp_id->getVal();
    }

    public function getMov_id()
    {
        return $this->mov_id->getVal();
    }

    public function getImporte()
    {
        return $this->dmo_importe->getVal();
    }

    public function getMes()
    {
        return $this->mes;
    }

    public function getImporteVigente()
    {
        return $this->importeVig->getVal();
    }

    public function getMesVigente()
    {
        return $this->mesVigente;
    }

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }
    /*     * ****************************************************

    METODOS SET

     * **************************************************** */

    public function setOmo_id($val)
    {
        $this->omo_id->setVal($val);
    }

    public function setMod_gpo_id($val)
    {
        $this->mod_gpo_id->setVal($val);
    }

    public function setMod_ur_id($val)
    {
        $this->mod_ur_id->setVal($val);
    }

    public function setMod_id($val)
    {
        $this->mod_id->setVal($val);
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

    public function setTcp_id($val)
    {
        $this->tcp_id->setVal($val);
    }

    public function setMov_id($val)
    {
        $this->mov_id->setVal($val);
    }

    public function setImporte($val)
    {
        $this->dmo_importe->setVal($val);
    }

    public function setImporteVigente($val)
    {
        $this->vigente->setVal($val);
    }

    public function setVigente($val)
    {
        $this->vigente->setVal($val);
    }

    public function setMes($val)
    {
        $this->mes = $val;
    }

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /** ****************************************************
    METODOS STA (SET sin persistencia)
     * **************************************************** */

    public function staOmo_id($val)
    {
        $this->omo_id->staVal($val);
    }

    public function staMod_gpo_id($val)
    {
        $this->mod_gpo_id->staVal($val);
    }

    public function staMod_ur_id($val)
    {
        $this->mod_ur_id->staVal($val);
    }

    public function staMod_id($val)
    {
        $this->mod_id->staVal($val);
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

    public function staTcp_id($val)
    {
        $this->tcp_id->staVal($val);
    }

    public function staMov_id($val)
    {
        $this->mov_id->staVal($val);
    }

    public function staImporte($val)
    {
        $this->dmo_importe->staVal($val);
    }

    public function staImporteVigente($val)
    {
        $this->importeVig->staVal($val);
    }

    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }

    /** ****************************************************

    CONSTRUCTOR

     * **************************************************** */

    public function __construct()
    {

        parent::__construct(['tabla' => 'scp_DetalleModificacion']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */
        $this->omo_id = new Atributo('cadena', 'NO', 'PRI', 'omo_id');
        $this->mod_gpo_id = new Atributo('numero', 'NO', 'PRI', 'mod_gpo_id');
        $this->mod_ur_id = new Atributo('numero', 'NO', 'PRI', 'mod_ur_id');
        $this->mod_id = new Atributo('numero', 'NO', 'PRI', 'mod_id');
        $this->gpo_id = new Atributo('numero', 'NO', 'PRI', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', 'PRI', 'ur_id');
        $this->ue_id = new Atributo('numero', 'NO', 'PRI', 'ue_id');
        $this->epg_id = new Atributo('numero', 'NO', 'PRI', 'epg_id');
        $this->pg_id = new Atributo('numero', 'NO', 'PRI', 'pg_id');
        $this->pe_id = new Atributo('numero', 'NO', 'PRI', 'pe_id');
        $this->ecf_id = new Atributo('numero', 'NO', 'PRI', 'ecf_id');
        $this->tcp_id = new Atributo('cadena', 'NO', 'PRI', 'tcp_id');
        $this->mov_id = new Atributo('cadena', 'NO', 'MUL', 'mov_id');
        $this->dmo_importe = new Atributo('moneda', 'NO', '', 'dmo_importe');
        $this->vigente = new Atributo('moneda', 'NO', '', 'pre_vigente');
        $this->mes = new Meses(['prefix' => 'dmo_', 'tipo' => 'moneda']);
        $this->mesVigente = new Meses(['prefix' => 'vig_', 'tipo' => 'moneda']);
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        $prm = (func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0)
            : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null;

        $omo_id = (!isset($prm->omo_id)) ? '' : $prm->omo_id;
        $mod_gpo_id = (!isset($prm->mod_gpo_id)) ? '' : $prm->mod_gpo_id;
        $mod_ur_id = (!isset($prm->mod_ur_id)) ? '' : $prm->mod_ur_id;
        $mod_id = (!isset($prm->mod_id)) ? '' : $prm->mod_id;
        $gpo_id = (!isset($prm->gpo_id)) ? '' : $prm->gpo_id;
        $ur_id = (!isset($prm->ur_id)) ? '' : $prm->ur_id;
        $ue_id = (!isset($prm->ue_id)) ? '' : $prm->ue_id;
        $epg_id = (!isset($prm->epg_id)) ? '' : $prm->epg_id;
        $pg_id = (!isset($prm->pg_id)) ? '' : $prm->pg_id;
        $pe_id = (!isset($prm->pe_id)) ? '' : $prm->pe_id;
        $ecf_id = (!isset($prm->ecf_id)) ? '' : $prm->ecf_id;
        $tcp_id = (!isset($prm->tcp_id)) ? '' : $prm->tcp_id;

        if (
            $omo_id != '' && $mod_gpo_id != '' && $mod_ur_id != '' && $mod_id != ''
            && $gpo_id != '' && $ur_id != '' && $ue_id != '' && $epg_id != '' && $pg_id
            != '' && $pe_id != '' && $ecf_id != ''
        ) {
            $this->setOmo_id($omo_id);
            $this->setMod_gpo_id($mod_gpo_id);
            $this->setMod_ur_id($mod_ur_id);
            $this->setMod_id($mod_id);
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
    } //Fin __construct

    public function select($servidor = '')
    {
        $parametros = [];
        if (
            $this->getOmo_id() != '' && $this->getMod_gpo_id() != '' && $this->getMod_ur_id()
            != '' && $this->getMod_id() != ''
        ) {
            $mod = new MaestroModificacion(
                [
                    'omo_id'     => $this->getOmo_id(),
                    'mod_gpo_id' => $this->getMod_gpo_id(),
                    'mod_ur_id'  => $this->getMod_ur_id(),
                    'mod_id'     => $this->getMod_id(),
                ]
            );
            $enc_oop = new OrigenOperacion(
                [
                    'enc_oop' => $mod->getOop(),
                ]
            );
            /* si es una prevision */
            if ($this->getTcp_id() == 'P') {
                parent::select();
            } else {
                switch ($enc_oop->getTmd_id()) {
                    /* si es un traspaso interinstitucional con tipo de movimiento ampliacion,
                    se procede a tratarla de manera especial */
                    case 'B':
                        switch ($mod->getTipoMovimiento()) {
                            case 'A':
                                if (UnidadResponsable::excentaComprasConsolidadas(
                                    [
                                        'gpo_id' => $this->getMod_gpo_id(),
                                        'ur_id'  => $this->getMod_ur_id(),
                                        'pe_id'  => $this->getPe_id(),
                                        'ecf_id' => $this->getEcf_id(),
                                    ]
                                )) {
                                    parent::select();
                                } else {
                                    /* select con sum */
                                    $sql = 'select ';
                                    $from = ' from ' . $this->getTable();
                                    $where = '
                                        where omo_id = :omo_id ' .
                                        ' and mod_gpo_id = :m_gpo_id ' .
                                        ' and mod_ur_id = :m_ur_id ' .
                                        ' and mod_id = :mod_id ' .
                                        ' and gpo_id = :gpo_id ' .
                                        ' and ur_id = :ur_id ' .
                                        ' and ue_id = :ue_id ' .
                                        ' and epg_id = :epg_id ' .
                                        ' and pg_id = :pg_id ' .
                                        ' and pe_id = :pe_id ' .
                                        ' and ecf_id = :ecf_id ';

                                    $parametros = [
                                        ':omo_id'   => $this->getOmo_id(),
                                        ':m_gpo_id' => $this->getMod_gpo_id(),
                                        ':m_ur_id'  => $this->getMod_ur_id(),
                                        ':mod_id'   => $this->getMod_id(),
                                        ':gpo_id'   => $this->getGpo_id(),
                                        ':ur_id'    => $this->getUr_id(),
                                        ':ue_id'    => $this->getUe_id(),
                                        ':epg_id'   => $this->getEpg_id(),
                                        ':pg_id'    => $this->getPg_id(),
                                        ':pe_id'    => $this->getPe_id(),
                                        ':ecf_id'   => $this->getEcf_id(),
                                    ];

                                    if ($_SESSION[_S]['nvj_id'] >= 3) {
                                        $where .=
                                            ' and tcp_id = :tcp_id ' .
                                            ' group by omo_id,mod_gpo_id,mod_ur_id,mod_id,gpo_id,
                                                ur_id,ue_id,epg_id,pg_id, pe_id, ' .
                                            ' ecf_id,tcp_id';
                                        $parametros[':tcp_id'] = $this->getTcp_id();
                                    } else {
                                        $where .= '
                                            group by omo_id,mod_gpo_id,mod_ur_id,mod_id,
                                                gpo_id,ur_id,ue_id,epg_id,pg_id, pe_id,ecf_id,tcp_id';
                                    }

                                    $properties = $this->me->getDefaultProperties();
                                    $coma = false;

                                    foreach ($properties as $key => $atributo) {
                                        //si es atributo
                                        if ($this->$key instanceof Atributo) {
                                            //si no es llave primaria, se integra en la sentencia
                                            if ($this->$key->getKeys() != 'PRI') {
                                                switch ($this->$key->getTipo()) {
                                                    case 'moneda':
                                                        $sql .= 'sum(' . $this->$key->getName() . ') ' .
                                                        $this->$key->getName() . ' , ';
                                                        $coma = true;
                                                        break;
                                                    default:
                                                        $sql .= $this->$key->getName() . ', ';
                                                        $coma = true;
                                                        break;
                                                }
                                            }
                                        } elseif ($this->$key instanceof Meses) { /* cargando elementos de tipo Meses */
                                            $meses = $this->$key->__get('me')->getDefaultProperties();
                                            foreach ($meses as $idMes => $mes) {
                                                if ($this->$key->__get($idMes) instanceof Atributo) {
                                                    switch ($this->$key->$idMes->getTipo()) {
                                                        case 'moneda':
                                                            $sql .= 'sum(' . $this->$key->$idMes->getName() . ') ' .
                                                            $this->$key->$idMes->getName() . ' , ';
                                                            $coma = true;
                                                            break;
                                                        default:
                                                            $sql .= $this->$key->getName() . ', ';
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
                                    $this->conectar();
                                    $this->setSql($sql . $from . $where);
                                    $this->consultar($parametros);
                                    $row = $this->extraer();
                                    $this->desconectar();
                                    if ($row != '') {
                                        foreach ($properties as $key => $atributo) {
                                            if ($this->$key instanceof Atributo) {
                                                //si no es llave primaria
                                                if ($this->$key->getKeys() != 'PRI') {
                                                    switch ($this->$key->getTipo()) {
                                                        case 'date':
                                                        case 'fecha':
                                                            $this->$key->staVal($this->mysqldate_to_date(
                                                                $row[$this->$key->getName()]
                                                            ));
                                                            break;
                                                        default:
                                                            $this->$key->staVal(
                                                                $row[$this->$key->getName()]
                                                            );
                                                            break;
                                                    }
                                                }
                                            } elseif ($this->$key instanceof Meses) {
                                                foreach ($meses as $idMes => $mes) {
                                                    if ($this->$key->__get($idMes) instanceof Atributo) {
                                                        switch ($this->$key->$idMes->getTipo()) {
                                                            default:
                                                                $this->$key->$idMes->staVal(
                                                                    $row[$this->$key->$idMes->getName()]
                                                                );
                                                                break;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                break;

                            case 'R':
                                parent::select();
                                break;
                        }
                        break;
                    default:
                        parent::select();
                        break;
                }
            }
        }
    } //fin select

    public function delete($conectar = true, $servidor = '')
    {
        $parametros = [];
        $parametrosb = [];
        $observacion = 'ACTUALIZADO DESDE PANTALLA S_1_1';
        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1')
        ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
        $id = $_SESSION[_S]['usr_id'];

        if (
            $this->getOmo_id() != '' && $this->getMod_gpo_id() != '' && $this->getMod_ur_id()
            != '' && $this->getMod_id() != '' && $this->getGpo_id() != '' && $this->getUr_id()
            != '' && $this->getUe_id() != '' && $this->getEpg_id() != '' && $this->getPg_id()
            != '' && $this->getPe_id() != '' && $this->getEcf_id() != ''
        ) {
            $mod = new MaestroModificacion(
                [
                    'omo_id'     => $this->getOmo_id(),
                    'mod_gpo_id' => $this->getMod_gpo_id(),
                    'mod_ur_id'  => $this->getMod_ur_id(),
                    'mod_id'     => $this->getMod_id(),
                ]
            );

            //obtenemos el tipo de modificacion
            $enc_oop = new OrigenOperacion();
            $enc_oop->setOop($mod->getOop());
            $enc_oop->select();

            if (($enc_oop->getTmd_id() == 'B' && $mod->getTipoMovimiento() == 'A')
                || $enc_oop->getTmd_id() == 'D'
            ) {
                if (UnidadResponsable::excentaComprasConsolidadas(
                    [
                        'gpo_id' => $this->getMod_gpo_id(),
                        'ur_id'  => $this->getMod_ur_id(),
                        'pe_id'  => $this->getPe_id(),
                        'ecf_id' => $this->getEcf_id(),
                    ]
                )) {
                    if ($this->getTcp_id() != 'P') {
                        $this->setTcp_id('D'); /* debido a que está exenta y debe ser directa */
                        $this->conectar();
                        //ENVIAMOS LAS VARIABLES DE SESION
                        $sql = '
                            SET @bit_observacion = ?,
                            @usr_ip = INET_ATON(?),
                            @usr_id = ?
                        ';
                        $parametrosb = [
                            $observacion,
                            $ip,
                            $id,
                        ];
                        $this->setSql($sql);
                        $this->consultar($parametrosb);
                        $sql = "
                                UPDATE presupuestoVigente
                                INNER JOIN presupuesto USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                                SET pre_mov = ''
                            ";
                        $where = ' where gpo_id = ? ' .
                            ' and ur_id = ? ' .
                            ' and ue_id = ? ' .
                            ' and epg_id = ? ' .
                            ' and pg_id = ? ' .
                            ' and pe_id = ? ' .
                            ' and ecf_id = ? ' .
                            ' and tcp_id = ?
                        ';
                        $parametros = [
                            $this->getGpo_id(),
                            $this->getUr_id(),
                            $this->getUe_id(),
                            $this->getEpg_id(),
                            $this->getPg_id(),
                            $this->getPe_id(),
                            $this->getEcf_id(),
                            $this->getTcp_id(),
                        ];
                        $this->setSql($sql . $where);
                        $this->consultar($parametros);
                        $this->desconectar();
                    }

                    $this->conectar();
                    //ENVIAMOS LAS VARIABLES DE SESION
                    $sql = '
                        SET @bit_observacion = ?,
                        @usr_ip = INET_ATON(?),
                        @usr_id = ?
                    ';
                    $parametrosb = [
                        $observacion,
                        $ip,
                        $id,
                    ];
                    $this->setSql($sql);
                    $this->consultar($parametrosb);
                    parent::delete(false);
                } else {

                    $this->conectar();
                    //ENVIAMOS LAS VARIABLES DE SESION
                    $sql = '
                        SET @bit_observacion = ?,
                        @usr_ip = INET_ATON(?),
                        @usr_id = ?
                    ';
                    $parametrosb = [
                        $observacion,
                        $ip,
                        $id,
                    ];
                    $this->setSql($sql);
                    $this->consultar($parametrosb);

                    $sql = "
                            UPDATE presupuestoVigente
                            INNER JOIN presupuesto USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        SET pre_mov = ''
                    ";
                    $where = ' 
                        WHERE gpo_id = ? ' .
                        ' AND ur_id =? ' .
                        ' AND ue_id =? ' .
                        ' AND epg_id = ? ' .
                        ' AND pg_id =? ' .
                        ' AND pe_id =? ' .
                        ' AND ecf_id = ? ' .
                        ' AND tcp_id != ?
                    ';

                    $parametros = [
                        $this->getGpo_id(),
                        $this->getUr_id(),
                        $this->getUe_id(),
                        $this->getEpg_id(),
                        $this->getPg_id(),
                        $this->getPe_id(),
                        $this->getEcf_id(),
                        'P',
                    ];

                    switch ($_SESSION[_S]['nvj_id']) {
                        case 1:
                        case 2:
                            break;
                        default:
                            $where .= " AND tcp_id = '{$this->getTcp_id()}' ";
                            break;
                    }
                    $this->setSql($sql . $where);
                    $this->consultar($parametros);

                    $sql = 'DELETE FROM ' . $this->getTable();
                    $where = ' WHERE omo_id= ? ' .
                        ' AND mod_gpo_id = ? ' .
                        ' AND mod_ur_id = ? ' .
                        ' AND mod_id = ? ' .
                        ' AND gpo_id = ? ' .
                        ' AND ur_id = ? ' .
                        ' AND ue_id = ? ' .
                        ' AND epg_id = ? ' .
                        ' AND pg_id = ? ' .
                        ' AND pe_id = ? ' .
                        ' AND ecf_id = ?
                    ';

                    $parametros = [
                        $this->getOmo_id(),
                        $this->getMod_gpo_id(),
                        $this->getMod_ur_id(),
                        $this->getMod_id(),
                        $this->getGpo_id(),
                        $this->getUr_id(),
                        $this->getUe_id(),
                        $this->getEpg_id(),
                        $this->getPg_id(),
                        $this->getPe_id(),
                        $this->getEcf_id(),
                    ];

                    switch ($_SESSION[_S]['nvj_id']) {
                        case 1:
                        case 2:
                            break;
                        default:
                            $where .= " AND tcp_id = '{$this->getTcp_id()}' ";
                            break;
                    }

                    $this->setSql($sql . $where);
                    $this->consultar($parametros);
                    $this->desconectar();
                }
            } elseif ($this->getTcp_id() != '') {
                if ($this->getTcp_id() != 'P') {
                    $this->conectar();
                    //ENVIAMOS LAS VARIABLES DE SESION
                    $sql = '
                        SET @bit_observacion = ?,
                        @usr_ip = INET_ATON(?),
                        @usr_id = ?
                    ';
                    $parametrosb = [
                        $observacion,
                        $ip,
                        $id,
                    ];
                    $this->setSql($sql);
                    $this->consultar($parametrosb);

                    $sql = "
                            UPDATE presupuestoVigente
                            INNER JOIN presupuesto USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                            SET pre_mov = ''
                        ";
                    $where = ' WHERE gpo_id = ? ' .
                        ' AND ur_id = ? ' .
                        ' AND ue_id = ? ' .
                        ' AND epg_id = ? ' .
                        ' AND pg_id = ? ' .
                        ' AND pe_id = ? ' .
                        ' AND ecf_id = ? ' .
                        ' AND tcp_id = ?
                    ';

                    $parametros = [
                        $this->getGpo_id(),
                        $this->getUr_id(),
                        $this->getUe_id(),
                        $this->getEpg_id(),
                        $this->getPg_id(),
                        $this->getPe_id(),
                        $this->getEcf_id(),
                        $this->getTcp_id(),
                    ];

                    $this->setSql($sql . $where);
                    $this->consultar($parametros);
                    $this->desconectar();
                }

                $this->conectar();
                //ENVIAMOS LAS VARIABLES DE SESION
                $sql = '
                    SET @bit_observacion = ?,
                    @usr_ip = INET_ATON(?),
                    @usr_id = ?
                ';
                $parametrosb = [
                    $observacion,
                    $ip,
                    $id,
                ];
                $this->setSql($sql);
                $this->consultar($parametrosb);
                parent::delete(false);
            }
        }
    } //fin delete

    public function validaModificacion()
    {
        $prm = (func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0)
            : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null;

        $importe = (isset($prm->importe) ? $prm->importe : 0);
        $datos = [0, ''];
        $error = [];
        $noErrores = 0;
        $pre = new Presupuesto();
        $pre->setGpo_id($this->getGpo_id());
        $pre->setUr_id($this->getUr_id());
        $pre->setUe_id($this->getUe_id());
        $pre->setEpg_id($this->getEpg_id());
        $pre->setPg_id($this->getPg_id());
        $pre->setPe_id($this->getPe_id());
        $pre->setEcf_id($this->getEcf_id());
        $pre->setTcp_id($this->getTcp_id());
        $pre->select();

        if (!$pre->validaClavePresupuestal()) {
            $noErrores++;
            $error[$noErrores] = ['numero' => 45, 'mensaje' => 'La clave presupuestal no existe. '];
        }

        return $error;
    } // fin validaModificacion

    /*     * ****************************************************
    FUNCIONES ESTÁTICAS
     * **************************************************** */

    public static function abc($p)
    {
        $parametros = [];
        $parametrosb = [];
        $dmo = new DetalleModificacion();
        if (isset($p->omo_id)) {
            $dmo->setOmo_id($p->omo_id);
        }
        if (isset($p->mod_gpo_id)) {
            $dmo->setMod_gpo_id($p->mod_gpo_id);
        }
        if (isset($p->mod_ur_id)) {
            $dmo->setMod_ur_id($p->mod_ur_id);
        }
        if (isset($p->mod_id)) {
            $dmo->setMod_id($p->mod_id);
        }
        if (isset($p->gpo_id)) {
            $dmo->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $dmo->setUr_id($p->ur_id);
        }
        if (isset($p->ue_id)) {
            $dmo->setUe_id($p->ue_id);
        }
        if (isset($p->epg_id)) {
            $dmo->setEpg_id($p->epg_id);
        }
        if (isset($p->pg_id)) {
            $dmo->setPg_id($p->pg_id);
        }
        if (isset($p->pe_id)) {
            $dmo->setPe_id($p->pe_id);
        }
        if (isset($p->ecf_id)) {
            $dmo->setEcf_id($p->ecf_id);
        }
        if (isset($p->tcp_id)) {
            $dmo->setTcp_id($p->tcp_id);
        }
        if (isset($p->mov_id)) {
            $dmo->setMov_id($p->mov_id);
        }
        if (isset($p->importe)) {
            $dmo->setImporte($p->importe);
        }
        if (isset($p->vigente)) {
            $dmo->setVigente($p->vigente);
        }
        if (isset($p->importeVig)) {
            $dmo->setImporteVigente($p->importeVig);
        }
        if (isset($p->mes)) {
            $dmo->setMes($p->mes);
        }
        if (isset($p->usr_id)) {
            $dmo->setUsr_id($p->usr_id);
        }

        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1')
        ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
        //se modifica en 2020 por cambio a bitacora triggers
        switch ($p->abc) {
            case 'a':
                $observacion = 'INSERTADO DESDE PANTALLA S_1_1';
                $dmo->conectar();
                $dmo->setSql('
                    SET @bit_observacion = ?,
                    @usr_ip =INET_ATON(?),
                    @usr_id = ? ;
                ');
                $parametrosb = [
                    $observacion,
                    $ip,
                    $_SESSION[_S]['usr_id'],
                ];
                $dmo->consultar($parametrosb);
                $update = "
                    UPDATE presupuestoVigente
                        INNER JOIN presupuesto USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        SET pre_mov='" . $p->mov_id . "'
                    WHERE gpo_id = ?
                    AND ur_id =  ?
                    AND ue_id =  ?
                    AND epg_id = ?
                    AND pg_id =  ?
                    AND pe_id =  ?
                    AND ecf_id = ?
                    AND tcp_id = ?
                ";
                $parametros = [
                    $p->gpo_id,
                    $p->ur_id,
                    $p->ue_id ,
                    $p->epg_id ,
                    $p->pg_id,
                    $p->pe_id,
                    $p->ecf_id ,
                    $p->tcp_id,
                ];
                $dmo->setSql($update);
                $dmo->consultar($parametros);
                $dmo->insert(false);
                $dmo->desconectar();
                return $dmo->getUltimoId();
                break;
            case 'b':
                $dmo->delete();
                break;
            case 'c':
                $observacion = 'ACTUALIZADO DESDE PANTALLA S_1_1';
                $dmo->conectar();
                $dmo->setSql('
                    SET @bit_observacion = ?,
                    @usr_ip =INET_ATON(?),
                    @usr_id = ?;');
                $parametrosb = [
                    $observacion,
                    $ip,
                    $_SESSION[_S]['usr_id'],
                ];
                $dmo->consultar($parametrosb);
                $dmo->update(false);
                $dmo->desconectar();
                break;
        }
    } //fin abc
} //FIN class DetalleModificacion
