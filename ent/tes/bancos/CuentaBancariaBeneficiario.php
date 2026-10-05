<?php

require_once __DIR__ . '/../../Entidad.php';

class CuentaBancariaBeneficiario extends Entidad
{
    protected $cbb_id;              //CLABE interbancaria
    protected $cbb_cuenta;          //Cuenta Bancaria
    protected $cbb_fechaApertura;   //Fecha de Apertura de la Cuenta Bancaria
    protected $cbb_anio;            //Ejercicio de uso de la Cuenta Bancaria
    protected $cbb_sucursal;        //Sucursal Bancaria
    protected $cbb_nombre;          //Nombre de la Cuenta Bancaria
    protected $cbb_alias;           //Alias de la Cuenta Bancaria
    protected $cbb_cliente;         //Número de Cliente de la Cuenta Bancaria
    protected $cbb_plazaBancaria;   //Plaza Bancaria
    protected $iba_id;              //cat_InstitucionBancaria - FK Institucion Bancaria
    protected $pbn_id;              //cat_PlazaBancaria - FK Plaza Bancaria
    protected $eba_id;              //cat_EstadoCuenta - FK Estado de la Cuenta
    protected $tbe_id;              //cat_TipoBeneficiario - FK Tipo de Beneficiario
    protected $usr_id;              //Usuario que captura el registro

    public function __construct()
    {
        $prm = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        parent::__construct(['tabla' => BD_TESORERIA . '.ban_CuentaBancariaBeneficiario']);

        $this->cbb_id = new Atributo('numero', 'NO', 'PRI', 'cbb_id');
        $this->cbb_cuenta = new Atributo('numero', 'NO', '', 'cbb_cuenta');
        $this->cbb_fechaApertura = new Atributo('fecha', 'NO', '', 'cbb_fechaApertura');
        $this->cbb_anio = new Atributo('numero', 'NO', 'MUL', 'cbb_anio');
        $this->cbb_sucursal = new Atributo('numero', 'NO', '', 'cbb_sucursal');
        $this->cbb_nombre = new Atributo('cadena', 'NO', '', 'cbb_nombre');
        $this->cbb_alias = new Atributo('cadena', 'NO', '', 'cbb_alias');
        $this->cbb_cliente = new Atributo('numero', 'NO', '', 'cbb_cliente');
        $this->cbb_plazaBancaria = new Atributo('numero', 'NO', '', 'cbb_plazaBancaria');
        $this->iba_id = new Atributo('numero', 'NO', 'MUL', 'iba_id');
        $this->pbn_id = new Atributo('numero', 'NO', 'MUL', 'pbn_id');
        $this->eba_id = new Atributo('numero', 'NO', 'MUL', 'eba_id');
        $this->tbe_id = new Atributo('cadena', 'NO', 'MUL', 'tbe_id');
        $this->usr_id = new Atributo('numero', 'NO', 'MUL', 'usr_id');

        if ($prm) {
            $this->fill($prm, true);
            if ($this->getCbb_id() != '') {
                $this->select();
            }
        }
    }

    public static function abc($p)
    {
        $p = (object) $p;

        // El módulo de municipios envía llaves "cortas"; se normalizan al nombre de
        // columna (cbb_<campo>) para que fill() las mapee (propiedad = columna).
        $camposCortos = ['cuenta', 'fechaApertura', 'anio', 'sucursal', 'nombre', 'alias', 'cliente', 'plazaBancaria'];
        foreach ($camposCortos as $corto) {
            if (isset($p->$corto)) {
                $p->{'cbb_' . $corto} = $p->$corto;
            }
        }

        $cbb = new CuentaBancariaBeneficiario();
        $cbb->fill($p, false);
        $cbb->setObservacion($p->observacion ?? 'OPCION DE BANCOS UR');

        switch ($p->abc) {
            case 'a':
                $cbb->insert();
                return $cbb->getUltimoId();
            case 'b':
                $cbb->delete();
                break;
            case 'c':
                $cbb->update();
                break;
        }
    }
}
