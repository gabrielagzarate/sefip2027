<?php

require_once dirname(__FILE__) . '/Entidad.php';
class BancosUr extends Entidad
{
    /******************************************************
        ATRIBUTOS DE LA CLASE
     ******************************************************/
    protected $bur_id; //CLABE interbancaria identificadora de la Cuenta Bancaria
    protected $gpo_id; //Grupo al cual pertenece la UR y por tanto la Cuenta Bancaria
    protected $ur_id; //Unidad Responsable al cual pertenece Cuenta Bancaria
    protected $ue_id; //Unidad Ejecutora de la Cuenta Contable
    protected $ucb_id; //Indica el Uso de la Cuenta Bancaria  Depende de la Tabla cat_Usobur_cuentaBancaria
    protected $tcb_id; //Indica el tipo de Cuenta Bancaria, y depende de la Tabla cat_Tipobur_cuentaBancaria
    protected $iba_id; //Indica la InstituciÃ³n Bancaria, depende de la tabla cat_InstitucionesBancarias
    protected $bur_cuentaBancaria; //En si la cuenta  Bancaria a 11 digitos
    protected $bur_alias; //Sobre nombre que se le asigna a la cuenta Bancaria
    protected $bur_beneficiario; //Indica quien es el beneficiario de esta Cuenta Bancaria
    protected $bur_plaza; //Debe contener la plaza Bancaria donde se abrio la Cuenta Bancaria
    protected $bur_sucursal; //Debe contener la Sucursal donde se abrio la Cuenta Bancaria
    protected $bur_descripcion; //da una descripciÃ³n general de la Cuenta Bancaria
    protected $bur_fechaAlta; //Fecha en la que se registra la Cuenta Bancaria
    protected $bur_titular1; //nombre del primer  titular de la cuenta
    protected $bur_cargo1; //cargo del primer  titular de la cuenta
    protected $bur_titular_2; //nombre del segundo titular de la cuenta
    protected $bur_cargo2; //Cargo del segundo titular de la cuenta

    protected $ger_id; //Cuenta Contable vinculada a esta cuenta Bancaria Depende de cg_PlanCuentas
    protected $cgp_id; //Cuenta Contable vinculada a esta cuenta Bancaria Depende de cg_PlanCuentas
    protected $rub_id; //Cuenta Contable vinculada a esta cuenta Bancaria Depende de cg_PlanCuentas
    protected $cgcu_id; //Cuenta Contable vinculada a esta cuenta Bancaria Depende de cg_PlanCuentas
    protected $cgsc_id; //Cuenta Contable vinculada a esta cuenta Bancaria Depende de cg_PlanCuentas
    protected $cta_gpo_id; //Cuenta Contable vinculada a esta cuenta Bancaria Depende de cg_PlanCuentas
    protected $cta_ur_id; //Cuenta Contable vinculada a esta cuenta Bancaria Depende de cg_PlanCuentas
    protected $cta_ue_id; //Cuenta Contable vinculada a esta cuenta Bancaria Depende de cg_PlanCuentas
    protected $cgc_id; //Cuenta Contable vinculada a esta cuenta Bancaria Depende de cg_PlanCuentas
    protected $cta_id; //Cuenta Contable vinculada a esta cuenta Bancaria Depende de cg_PlanCuentas

    protected $eba_id; //Estado de la cuenta Bancaria  depende de cat_EstadoBancos
    protected $bur_gpo_id;
    protected $bur_ur_id;
    protected $bur_fondoRotatorio;
    protected $usr_id;
    /* DECLARAMOS ESTO PARA EL RFC, PERO NO SERA UNA INSTANCIA DE ATRIBUTO  */
    protected $rfc_id;

    /******************************************************
        METODOS GET
     ******************************************************/

    public function getBur_id()
    {
        return $this->bur_id->getVal();
    }
    public function getGpo_id()
    {
        return $this->gpo_id->getVal();
    }
    public function getUr_id()
    {
        return  $this->ur_id->getVal();
    }
    public function getUe_id()
    {
        return $this->ue_id->getVal();
    }
    public function getUcb_id()
    {
        return $this->ucb_id->getVal();
    }
    public function getTcb_id()
    {
        return $this->tcb_id->getVal();
    }
    public function getIba_id()
    {
        return $this->iba_id->getVal();
    }
    public function getCuentaBancaria()
    {
        return $this->bur_cuentaBancaria->getVal();
    }
    public function getAlias()
    {
        return $this->bur_alias->getVal();
    }
    public function getBeneficiario()
    {
        return $this->bur_beneficiario->getVal();
    }
    public function getPlaza()
    {
        return $this->bur_plaza->getVal();
    }
    public function getSucursal()
    {
        return $this->bur_sucursal->getVal();
    }
    public function getDescripcion()
    {
        return $this->bur_descripcion->getVal();
    }
    public function getFechaAlta()
    {
        return $this->bur_fechaAlta->getVal();
    }
    public function getTitular1()
    {
        return $this->bur_titular1->getVal();
    }
    public function getCargo1()
    {
        return $this->bur_cargo1->getVal();
    }
    public function getTitular_2()
    {
        return $this->bur_titular_2->getVal();
    }
    public function getCargo2()
    {
        return $this->bur_cargo2->getVal();
    }

    public function getGer_id()
    {
        return $this->ger_id;
    }
    public function getCgp_id()
    {
        return $this->cgp_id;
    }
    public function getRub_id()
    {
        return $this->rub_id;
    }
    public function getCgcu_id()
    {
        return $this->cgcu_id;
    }
    public function getCgsc_id()
    {
        return $this->cgsc_id;
    }
    public function getCta_gpo_id()
    {
        return $this->cta_gpo_id;
    }
    public function getCta_ur_id()
    {
        return $this->cta_ur_id;
    }
    public function getCta_ue_id()
    {
        return $this->cta_ue_id;
    }
    public function getCgc_id()
    {
        return $this->cgc_id;
    }
    public function getCta_id()
    {
        return $this->cta_id;
    }

    public function getEba_id()
    {
        return $this->eba_id->getVal();
    }
    public function getBur_gpo_id()
    {
        return $this->bur_gpo_id->getVal();
    }
    public function getBur_ur_id()
    {
        return $this->bur_ur_id->getVal();
    }
    public function getBur_fondoRotatorio()
    {
        return $this->bur_fondoRotatorio->getVal();
    }
    public function getUsr_id()
    {
        return $this->usr_id->getVal();
    }

    /*     DECLARAMOS ESTA FUNCION QUE NO ES PARTE DE LA TABLA CAT_BANCOSUR
           PERO DAREMOS UN TRATAMIENTO PARECIDO                          */
    public function getRfc_id()
    {
        return $this->rfc_id;
    }

    /******************************************************
        METODOS SET
     ******************************************************/

    public function setBur_id($val)
    {
        $this->bur_id->setVal($val);
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
    public function setUcb_id($val)
    {
        $this->ucb_id->setVal($val);
    }
    public function setTcb_id($val)
    {
        $this->tcb_id->setVal($val);
    }
    public function setIba_id($val)
    {
        $this->iba_id->setVal($val);
    }
    public function setCuentaBancaria($val)
    {
        $this->bur_cuentaBancaria->setVal($val);
    }
    public function setAlias($val)
    {
        $this->bur_alias->setVal($val);
    }
    public function setBeneficiario($val)
    {
        $this->bur_beneficiario->setVal($val);
    }
    public function setPlaza($val)
    {
        $this->bur_plaza->setVal($val);
    }
    public function setSucursal($val)
    {
        $this->bur_sucursal->setVal($val);
    }
    public function setDescripcion($val)
    {
        $this->bur_descripcion->setVal($val);
    }
    public function setFechaAlta($val)
    {
        $this->bur_fechaAlta->setVal($val);
    }
    public function setTitular1($val)
    {
        $this->bur_titular1->setVal($val);
    }
    public function setCargo1($val)
    {
        $this->bur_cargo1->setVal($val);
    }
    public function setTitular_2($val)
    {
        $this->bur_titular_2->setVal($val);
    }
    public function setCargo2($val)
    {
        $this->bur_cargo2->setVal($val);
    }

    public function setGer_id($val)
    {
        $this->ger_id = $val;
    }
    public function setCgp_id($val)
    {
        $this->cgp_id = $val;
    }
    public function setRub_id($val)
    {
        $this->rub_id = $val;
    }
    public function setCgcu_id($val)
    {
        $this->cgcu_id = $val;
    }
    public function setCgsc_id($val)
    {
        $this->cgsc_id = $val;
    }
    public function setCta_gpo_id($val)
    {
        $this->cta_gpo_id = $val;
    }
    public function setCta_ur_id($val)
    {
        $this->cta_ur_id = $val;
    }
    public function setCta_ue_id($val)
    {
        $this->cta_ue_id = $val;
    }
    public function setCgc_id($val)
    {
        $this->cgc_id = $val;
    }
    public function setCta_id($val)
    {
        $this->cta_id = $val;
    }

    public function setEba_id($val)
    {
        $this->eba_id->setVal($val);
    }
    public function setBur_gpo_id($val)
    {
        $this->bur_gpo_id->setVal($val);
    }
    public function setBur_ur_id($val)
    {
        $this->bur_ur_id->setVal($val);
    }
    public function setBur_fondoRotatorio($val)
    {
        $this->bur_fondoRotatorio->setVal($val);
    }
    public function setUsr_id($val)
    {
        $this->usr_id->setVal($val);
    }

    /*     DECLARAMOS ESTA FUNCION QUE NO ES PARTE DE LA TABLA CAT_BANCOSUR
           PERO DAREMOS UN TRATAMIENTO PARECIDO                          */
    public function setRfc_id($val)
    {
        $this->rfc_id = $val;
    }

    /******************************************************
        METODOS STA (SET sin persistencia)
     ******************************************************/

    public function staBur_id($val)
    {
        $this->bur_id->staVal($val);
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
    public function staUcb_id($val)
    {
        $this->ucb_id->staVal($val);
    }
    public function staTcb_id($val)
    {
        $this->tcb_id->staVal($val);
    }
    public function staIba_id($val)
    {
        $this->iba_id->staVal($val);
    }
    public function staCuentaBancaria($val)
    {
        $this->bur_cuentaBancaria->staVal($val);
    }
    public function staAlias($val)
    {
        $this->alias->staVal($val);
    }
    public function staBeneficiario($val)
    {
        $this->bur_beneficiario->staVal($val);
    }
    public function staPlaza($val)
    {
        $this->bur_plaza->staVal($val);
    }
    public function staSucursal($val)
    {
        $this->bur_sucursal->staVal($val);
    }
    public function staDescripcion($val)
    {
        $this->bur_descripcion->staVal($val);
    }
    public function staFechaAlta($val)
    {
        $this->bur_fechaAlta->staVal($val);
    }
    public function staTitular1($val)
    {
        $this->bur_titular1->staVal($val);
    }
    public function staCargo1($val)
    {
        $this->bur_cargo1->staVal($val);
    }
    public function staTitular_2($val)
    {
        $this->bur_titular_2->staVal($val);
    }
    public function staCargo2($val)
    {
        $this->bur_cargo2->staVal($val);
    }
    public function staGer_id($val)
    {
        $this->ger_id->staVal($val);
    }
    public function staCgp_id($val)
    {
        $this->cgp_id->staVal($val);
    }
    public function staRub_id($val)
    {
        $this->rub_id->staVal($val);
    }
    public function staCgcu_id($val)
    {
        $this->cgcu_id->staVal($val);
    }
    public function staCgsc_id($val)
    {
        $this->cgsc_id->staVal($val);
    }
    public function staCta_gpo_id($val)
    {
        $this->cta_gpo_id->staVal($val);
    }
    public function staCta_ur_id($val)
    {
        $this->cta_ur_id->staVal($val);
    }
    public function staCta_ue_id($val)
    {
        $this->cta_ue_id->staVal($val);
    }
    public function staCgc_id($val)
    {
        $this->cgc_id->staVal($val);
    }
    public function staCta_id($val)
    {
        $this->cta_id->staVal($val);
    }
    public function staEba_id($val)
    {
        $this->eba_id->staVal($val);
    }
    public function staBur_gpo_id($val)
    {
        $this->bur_gpo_id->staVal($val);
    }
    public function staBur_ur_id($val)
    {
        $this->bur_ur_id->staVal($val);
    }
    public function staBur_fondoRotatorio($val)
    {
        $this->bur_fondoRotatorio->staVal($val);
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
        parent::__construct(['tabla' => 'cat_BancosUr']);

        /*SE DEFINEN LOS TIPOS DE VARIABLES O ATRIBUTOS DE LA CLASE*/

        $this->bur_id = new Atributo('numero', 'NO', 'PRI', 'bur_id');
        $this->gpo_id = new Atributo('numero', 'NO', 'MUL', 'gpo_id');
        $this->ur_id = new Atributo('numero', 'NO', '', 'ur_id');
        $this->ue_id = new Atributo('numero', 'YES', '', 'ue_id');
        $this->ucb_id = new Atributo('numero', 'NO', 'MUL', 'ucb_id');
        $this->tcb_id = new Atributo('numero', 'NO', 'MUL', 'tcb_id');
        $this->iba_id = new Atributo('numero', 'NO', 'MUL', 'iba_id');
        $this->bur_cuentaBancaria = new Atributo('cadena', 'NO', '', 'bur_cuentaBancaria');
        $this->bur_alias = new Atributo('cadena', 'NO', '', 'bur_alias');
        $this->bur_beneficiario = new Atributo('cadena', 'NO', '', 'bur_beneficiario');
        $this->bur_plaza = new Atributo('cadena', 'NO', '', 'bur_plaza');
        $this->bur_sucursal = new Atributo('cadena', 'NO', '', 'bur_sucursal');
        $this->bur_descripcion = new Atributo('cadena', 'NO', '', 'bur_descripcion');
        $this->bur_fechaAlta = new Atributo('fecha', 'NO', '', 'bur_fechaAlta');
        $this->bur_titular1 = new Atributo('cadena', 'NO', '', 'bur_titular1');
        $this->bur_cargo1 = new Atributo('cadena', 'NO', '', 'bur_cargo1');
        $this->bur_titular_2 = new Atributo('cadena', 'NO', '', 'bur_titular_2');
        $this->bur_cargo2 = new Atributo('cadena', 'NO', '', 'bur_cargo2');
        $this->eba_id = new Atributo('numero', 'NO', 'MUL', 'eba_id');
        $this->bur_gpo_id = new Atributo('numero', 'YES', '', 'bur_gpo_id');
        $this->bur_ur_id = new Atributo('numero', 'YES', '', 'bur_ur_id');
        $this->bur_fondoRotatorio = new Atributo('cadena', 'YES', '', 'bur_fondoRotatorio');
        $this->usr_id = new Atributo('numero', 'NO', '', 'usr_id');

        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object)func_get_arg(0) : null) : null);

        $bur_id = (!isset($prm->bur_id)) ? '' : $prm->bur_id;
        $gpo_id = (!isset($prm->gpo_id)) ? '' : $prm->gpo_id;
        $ur_id = (!isset($prm->ur_id)) ? '' : $prm->ur_id;

        if ($bur_id != '') {
            $this->setBur_id($bur_id);
            $this->setGpo_id($gpo_id);
            $this->setUr_id($ur_id);
            $this->select();

            /* PARTE DEL RFC QUE LLENARA EL $THIS->RFC  COMO SI FUERA PARTE DE LA ENTIDAD
                nota: aqui podriamos preguntar sobre la naturaleza de la cuenta bancaria
                si es 4 ( pago directo a proveedor) deberia tener RFC en vin_BancosUrProveedor
                 - si no tiene algo anda mal
                 - si no es 4 y tiene algo anda mal
            */
            $con = new AccesoDatos();
            $con->conectar();
            $con->setSql('SELECT rfc_id  FROM vin_BancosUrProveedor WHERE bur_id = ?');
            $con->consultar([$bur_id]);
            if ($con->getNumeroRegistros() > 0) {
                $datos = $con->extraer();
                $this->setRfc_id($datos['rfc_id']);
            }
            $con->desconectar();
            unset($con);
            /*  FIN DEL RFC */
            if ($this->getUcb_id() != 4) {
                if ($_SESSION[_S]['nvj_id'] > 2) {
                    $params = [$bur_id, $this->getGpo_id(), $this->getUr_id()];
                    $sql = 'SELECT * FROM vin_BancosUrPlanCuentas WHERE bur_id = ? AND cta_gpo_id = ? AND cta_ur_id = ? order by usr_fecha desc';
                } else {
                    $params = [$bur_id, $_SESSION[_S]['clasAdm-gpo'], $this->getUr_id()];
                    $sql = 'SELECT * FROM vin_BancosUrPlanCuentas WHERE bur_id = ? AND cta_gpo_id = ? AND cta_ur_id = ? order by usr_fecha desc';
                }
                $c = new AccesoDatos();
                $c->setSql($sql);
                $c->conectar();
                $c->consultar($params);
                if ($c->getNumeroRegistros() > 0) {
                    $cuenta = $c->extraer();
                    if ($cuenta['ger_id'] != '') {
                        $this->setGer_id($cuenta['ger_id']);
                    }
                    if ($cuenta['cgp_id'] != '') {
                        $this->setCgp_id($cuenta['cgp_id']);
                    }
                    if ($cuenta['rub_id'] != '') {
                        $this->setRub_id($cuenta['rub_id']);
                    }
                    if ($cuenta['cgcu_id'] != '') {
                        $this->setCgcu_id($cuenta['cgcu_id']);
                    }
                    if ($cuenta['cgsc_id'] != '') {
                        $this->setCgsc_id($cuenta['cgsc_id']);
                    }
                    if ($cuenta['cta_gpo_id'] != '') {
                        $this->setCta_gpo_id($cuenta['cta_gpo_id']);
                    }
                    if ($cuenta['cta_ur_id'] != '') {
                        $this->setCta_ur_id($cuenta['cta_ur_id']);
                    }
                    if ($cuenta['cta_ue_id'] != '') {
                        $this->setCta_ue_id($cuenta['cta_ue_id']);
                    }
                    if ($cuenta['cgc_id'] != '') {
                        $this->setCgc_id($cuenta['cgc_id']);
                    }
                    if ($cuenta['cta_id'] != '') {
                        $this->setCta_id($cuenta['cta_id']);
                    }
                }
                $c->desconectar();
            }
        }
    }
    /******************************************************
        FUNCIONES ESTÁTICAS
     ******************************************************/
    public static function abc($p)
    {
        $bur = new BancosUr();
        if (isset($p->bur_id)) {
            $bur->setBur_id($p->bur_id);
        }
        if (isset($p->gpo_id)) {
            $bur->setGpo_id($p->gpo_id);
        }
        if (isset($p->ur_id)) {
            $bur->setUr_id($p->ur_id);
        }
        if (isset($p->ue_id)) {
            $bur->setUe_id($p->ue_id);
        }
        if (isset($p->ucb_id)) {
            $bur->setUcb_id($p->ucb_id);
        }
        if (isset($p->tcb_id)) {
            $bur->setTcb_id($p->tcb_id);
        }
        if (isset($p->iba_id)) {
            $bur->setIba_id($p->iba_id);
        }
        if (isset($p->cuentaBancaria)) {
            $bur->setcuentaBancaria($p->cuentaBancaria);
        }
        if (isset($p->alias)) {
            $bur->setAlias($p->alias);
        }
        if (isset($p->beneficiario)) {
            $bur->setBeneficiario($p->beneficiario);
        }
        if (isset($p->plaza)) {
            $bur->setPlaza($p->plaza);
        }
        if (isset($p->sucursal)) {
            $bur->setSucursal($p->sucursal);
        }
        if (isset($p->descripcion)) {
            $bur->setDescripcion($p->descripcion);
        }
        if (isset($p->fechaAlta)) {
            $bur->setFechaAlta($p->fechaAlta);
        }
        if (isset($p->titular1)) {
            $bur->setTitular1($p->titular1);
        }
        if (isset($p->cargo1)) {
            $bur->setCargo1($p->cargo1);
        }
        if (isset($p->titular_2)) {
            $bur->setTitular_2($p->titular_2);
        }
        if (isset($p->cargo2)) {
            $bur->setCargo2($p->cargo2);
        }

        if (isset($p->ger_id)) {
            $bur->setGer_id($p->ger_id);
        }
        if (isset($p->cgp_id)) {
            $bur->setCgp_id($p->cgp_id);
        }
        if (isset($p->rub_id)) {
            $bur->setRub_id($p->rub_id);
        }
        if (isset($p->cgcu_id)) {
            $bur->setCgcu_id($p->cgcu_id);
        }
        if (isset($p->cgsc_id)) {
            $bur->setCgsc_id($p->cgsc_id);
        }
        if (isset($p->cta_gpo_id)) {
            $bur->setCta_gpo_id($p->cta_gpo_id);
        }
        if (isset($p->cta_ur_id)) {
            $bur->setCta_ur_id($p->cta_ur_id);
        }
        if (isset($p->cta_ue_id)) {
            $bur->setCta_ue_id($p->cta_ue_id);
        }
        if (isset($p->cgc_id)) {
            $bur->setCgc_id($p->cgc_id);
        }
        if (isset($p->cta_id)) {
            $bur->setCta_id($p->cta_id);
        }

        if (isset($p->eba_id)) {
            $bur->setEba_id($p->eba_id);
        }
        if (isset($p->bur_gpo_id)) {
            $bur->setBur_gpo_id($p->bur_gpo_id);
        }
        if (isset($p->bur_ur_id)) {
            $bur->setBur_ur_id($p->bur_ur_id);
        }
        if (isset($p->bur_fondoRotatorio)) {
            $bur->setBur_fondoRotatorio($p->bur_fondoRotatorio);
        }
        if (isset($p->usr_id)) {
            $bur->setUsr_id($p->usr_id);
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ?
            $_SESSION[_S]['ses_ip'] :
            '127.0.0.1';
        $bit_usr_id = $_SESSION[_S]['usr_id'];

        switch ($p->abc) {
            case 'a':
                $bur->conectar();
                $bit_observacion = 'REGISTRO INSERTADO DESDE SCP->CATÁLOGOS->BANCOS UR';
                $params = [$ip, $bit_usr_id, $bit_observacion];
                $bur->setSql('SET @usr_ip =INET_ATON( ? ), @usr_id = ? , @bit_observacion = ?;');
                $bur->consultar($params);
                $bur->insert(false);
                return true;
                break;
            case 'b':
                $bur->delete();
                break;
            case 'c':
                $b = new BancosUr();
                $b->setBur_id($p->bur_id);
                $b->select();
                $bur->conectar();
                $bit_observacion = 'ACTUALIZAR REGISTRO ';
                $params = [$ip, $bit_usr_id, $bit_observacion];
                $bur->setSql(' SET @usr_ip = INET_ATON( ? ), @usr_id = ? , @bit_observacion = ?');
                $bur->consultar($params);

                if (
                    isset($p->eba_id) && $p->eba_id == 3 && ($b->getEba_id() == 2 || isset($p->guardaCuenta)) || (isset($_SESSION[_S]['prm']['C']['8']['1']['2']) && $b->getEba_id() == 6)
                ) {
                    /* ----  SI ES A VINCULACION CONTABLE  ENTONCES INSERTAMOS EN vin_BancosUrPlanCuentas----  */
                    if (
                        isset($p->ger_id) && $p->ger_id != '' && isset($p->cgp_id) && $p->cgp_id != '' && isset($p->rub_id) && $p->rub_id != '' &&
                        isset($p->cgcu_id) && $p->cgcu_id != '' && isset($p->cgsc_id) && $p->cgsc_id != '' && isset($p->cta_gpo_id) && $p->cta_gpo_id != '' &&
                        isset($p->cta_ur_id) && $p->cta_ur_id != '' && isset($p->cta_ue_id) && $p->cta_ue_id != '' && isset($p->cgc_id) && $p->cgc_id != '' &&
                        isset($p->cta_id) && $p->cta_id != ''
                    ) {

                        $c = new AccesoDatos();
                        $c->conectar();

                        switch ($b->getUcb_id()) {
                            case 1:
                                $sql = 'SELECT * FROM vin_BancosUrPlanCuentas WHERE bur_id = ?';
                                $c = new AccesoDatos();
                                $c->setSql($sql);
                                $c->conectar();
                                $c->consultar([$p->bur_id]);
                                if ($c->getNumeroRegistros() > 0) {
                                    $sql = 'DELETE FROM `vin_BancosUrPlanCuentas` WHERE bur_id  = ? ';
                                }
                                $c->setSql($sql);
                                $c->consultar([$p->bur_id]);
                                $c->desconectar();
                                unset($c);

                                $bur->update(false);
                                break;
                            case 2:
                            case 3:
                                $sql = 'SELECT * FROM vin_BancosUrPlanCuentas WHERE bur_id= ? AND cta_gpo_id = ? AND cta_ur_id = ?';
                                $c = new AccesoDatos();
                                $c->setSql($sql);
                                $c->conectar();
                                $c->consultar([$p->bur_id, $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']]);
                                if ($c->getNumeroRegistros() > 0) {

                                    $sql = "DELETE FROM `vin_BancosUrPlanCuentas` WHERE bur_id     = '" . $p->bur_id . "' AND cta_gpo_id     = '" . $_SESSION[_S]['clasAdm-gpo'] . "' AND cta_ur_id     = '" . $_SESSION[_S]['clasAdm-ur'] . "'";
                                }
                                $c->setSql($sql);
                                $c->consultar();
                                $c->desconectar();
                                unset($c);
                                $bur->update(false);
                                break;
                        }

                        $c = new AccesoDatos();
                        $c->conectar();

                        $sql = '
                            INSERT INTO `vin_BancosUrPlanCuentas` 
                            (`bur_id`, `ger_id`, `cgp_id`, `rub_id`, `cgcu_id`, `cgsc_id`, `cta_gpo_id`, `cta_ur_id`, `cta_ue_id`, `cgc_id`, `cta_id`, `usr_id`) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
                            ';
                        $params = [$p->bur_id, $p->ger_id, $p->cgp_id, $p->rub_id, $p->cgcu_id, $p->cgsc_id, $p->cta_gpo_id, $p->cta_ur_id, $p->cta_ue_id, $p->cgc_id, $p->cta_id, $_SESSION[_S]['nvj_id']];
                        $c->setSql($sql);
                        $c->consultar($params);
                        unset($c);
                        $bur->update(false);
                    } else {
                        if (isset($p->eba_id) && $p->eba_id == 3 && ($b->getEba_id() == 5 || isset($p->guardaCuenta) && $b->getUcb_id() == 4)) {

                            $c = new AccesoDatos();
                            $c->conectar();

                            $sql = 'DELETE FROM `vin_BancosUrPlanCuentas` WHERE bur_id  = ? ';
                            $c->setSql($sql);
                            $c->consultar([$p->bur_id]);
                            $c->desconectar();
                        }

                        $bur->update(false);
                    }

                    $bur->update(false);
                }
                if (isset($p->eba_id) && $p->eba_id == 3 && $b->getEba_id() == 5 && $p->ucb_id == 4) {

                    $c = new AccesoDatos();
                    $c->conectar();
                    $sql = 'DELETE FROM `vin_BancosUrPlanCuentas` WHERE bur_id = ? ';

                    $c->setSql($sql);
                    $c->consultar([$p->bur_id]);
                    $c->desconectar();

                    $bur->update(false);
                }
                $bur->update(false);
                $bur->desconectar();
                break;
        }
    }
}
