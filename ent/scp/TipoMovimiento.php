<?php

require_once dirname(__FILE__) . '/../Entidad.php';

class TipoMovimiento extends Entidad
{
    /*     * ****************************************************
        ATRIBUTOS DE LA CLASE
     * **************************************************** */
    protected $mov_id; //Clave identificadora del tipo de movimiento de modificacion presupuestal
    protected $nombre; //Descripci�n del movimiento presupuestal
    protected $usr_id; //Clave identificadora del usuario que inserto el registro
    protected $altaSefin;

    /*     * ****************************************************
        METODOS GET
     * **************************************************** */

    public function getMov_id()
    {
        return $this->mov_id->getVal();
    }

    public function getNombre()
    {
        return $this->nombre->getVal();
    }

    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    public function getAltaSefin()
    {
        return $this->altaSefin->getVal();
    }
    /*     * ****************************************************
        METODOS SET
     * **************************************************** */

    public function setMov_id($val)
    {
        $this->mov_id->setVal($val);
    }

    public function setNombre($val)
    {
        $this->nombre->setVal($val);
    }

    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    public function setAltaSefin($val)
    {
        $this->altaSefin->setVal($val);
    }
    /*     * ****************************************************
        METODOS STA (SET sin persistencia)
     * **************************************************** */

    public function staMov_id($val)
    {
        $this->mov_id->staVal($val);
    }

    public function staNombre($val)
    {
        $this->nombre->staVal($val);
    }

    public function staUsr_id($val)
    {
        $this->usr_id->staVal($val);
    }

    public function staAltaSefin($val)
    {
        $this->altaSefin->staVal($val);
    }
    /*     * ****************************************************
        CONSTRUCTOR
     * **************************************************** */

    public function __construct()
    {

        parent::__construct(['tabla' => 'cat_TipoMovimiento']);

        /* SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE */

        $this->mov_id = new Atributo('cadena', 'NO', 'PRI', 'mov_id');
        $this->nombre = new Atributo('cadena', 'NO', '', 'mov_nombre');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');


        $prm = (func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0)
                :
            (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null;
        $mov_id = (!isset($prm->mov_id)) ? '' : $prm->mov_id;
        $altaSefin = (!isset($prm->altaSefin)) ? '' : $prm->altaSefin;
        $this->setAltaSefin($altaSefin);

        if ($mov_id != '') {
            $this->setMov_id($mov_id);
            $this->select();
        }
    }
    /*     * ****************************************************
        FUNCIONES EST�TICAS
     * **************************************************** */

    public static function abc($p)
    {
        $mov = new TipoMovimiento();
        if (isset($p->mov_id)) {
            $mov->setMov_id($p->mov_id);
        }
        if (isset($p->nombre)) {
            $mov->setNombre($p->nombre);
        }
        if (isset($p->usr_id)) {
            $mov->setUsr_id($p->usr_id);
        }
        switch ($p->abc) {
            case 'a':
                $mov->insert();
                return $mov->getUltimoId();
                break;
            case 'b':
                $mov->delete();
                break;
            case 'c':
                $mov->update();
                break;
        }
    }

    public static function objMovimiento()
    {

        $prm = (func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0)
                : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : new stdClass();
        $tcp_id = (!isset($prm->tcp_id) || $prm->tcp_id == '') ? 'D' : $prm->tcp_id;
        $readOnly = (!isset($prm->readOnly)) ? false : $prm->readOnly;
        $prm->omo_id = (isset($prm->omo_id)) ? $prm->omo_id : '';
        $prm->mod_gpo_id = (isset($prm->mod_gpo_id)) ? $prm->mod_gpo_id : '';
        $prm->mod_ur_id = (isset($prm->mod_ur_id)) ? $prm->mod_ur_id : '';
        $prm->mod_id = (isset($prm->mod_id)) ? $prm->mod_id : '';

        if ($prm->omo_id != '' && $prm->mod_gpo_id != '' && $prm->mod_ur_id != ''
            && $prm->mod_id != '') {
            $mod = new MaestroModificacion($prm);
            $enc_oop = new OrigenOperacion(['enc_oop' => $mod->getOop()]);

            $estados = '';
            switch ($_SESSION[_S]['ssc_id']) {

                case 2: //planeacion
                    $estados = $mod->getEmo_id() == 6 || $mod->getEmo_Id() == 11
                        || $mod->getEmo_id() == 17;
                    break;
                case 3: //egresos
                    $estados = $mod->getEmo_id() == 8 || $mod->getEmo_Id() == 11
                        || $mod->getEmo_id() == 17;
                    break;
            }

            //Eligiendo el tipo de Movimiento por defecto
            switch ($enc_oop->getTmd_id()) {

                case 'A':
                    $mov_id = (!isset($prm->mov_id) || $prm->mov_id == '') ? 'A'
                            : $prm->mov_id;
                    break;
                case 'B':
                    switch ($mod->getTipoMovimiento()) {
                        case 'A':
                            $mov_id = $mod->getTipoMovimiento();
                            if ((isset($prm->altaSefin) && mb_strlen($prm->altaSefin))
                                || $tcp_id != 'P') {
                                $mov_id = 'A';
                            } elseif (($tcp_id == 'P' && $estados) && $_SESSION[_S]['nvj_id']
                                >= 3) {
                                $mov_id = 'R';
                            }
                            break;
                        case 'R':
                            $mov_id = $mod->getTipoMovimiento();
                            if ((isset($prm->altaSefin) && mb_strlen($prm->altaSefin))
                                || $tcp_id != 'P') {
                                $mov_id = 'R';
                            } elseif (($tcp_id == 'P' || $estados) && $_SESSION[_S]['nvj_id']
                                >= 3) {
                                $mov_id = 'A';
                            }
                            break;
                    }
                    break;
                case 'D':
                    $mov_id = 'A';
                    break; //liquida amp
                case 'E':
                    $mov_id = 'R';
                    break; //liquida red
                default:
                    $mov_id = 'C';
                    break;
            }
        }

        $rdTmov = new stdClass();
        $rdTmov->sql = ' SELECT mov_id, mov_nombre FROM cat_TipoMovimiento ';
        $rdTmov->cols = 1;
        $rdTmov->enDiv = true;
        $rdTmov->name = $rdTmov->id = 'mov_id';
        $rdTmov->value = 'mov_id';
        $rdTmov->label = 'mov_nombre';
        $rdTmov->selecciona = $mov_id;
        if ($readOnly) {
            $rdTmov->readOnly = $readOnly;
        }

        switch ($mod->getOmo_id() . $enc_oop->getTmd_id()) {
            case 'AA': //traspaso
            case 'BA':
            case 'CA':
            case 'DA':
            case 'EA':
                $rdTmov->sql .= " where mov_id in ('A', 'R')";
                break;
            default:
                $rdTmov->sql .= " where mov_id = '$mov_id' ";
                break;
        }
        return $rdTmov;
    }
}
